<?php

include("../menu_princ.php");
include("../functions.php");

extract($_REQUEST);

$cod_almacen = $_POST["cod_almacen"];
$buscar_por = $_POST["buscar_por"];
$cod_tanque = $_POST["cod_tanque"];
$unidadmedida = $_POST["unidadmedida"];
$detallecompras = $_POST["detallecompras"];
$boton = $_POST["boton"];

$nuevofechad = explode('/',$_POST['diasd']);
$diad = $nuevofechad[0];
$mesd = $nuevofechad[1];
$anod = $nuevofechad[2];
$nuevofechaa = explode('/',$_POST['diasa']);
$diaa = $nuevofechaa[0];
$mesa = $nuevofechaa[1];
$anoa = $nuevofechaa[2];
	
	if($boton != "Reporte") {
		$cod_tanque = "";
	}
		
	$fecha = getdate();
	$dia   = $fecha['mday'];
	$mes   = $fecha['mon'];
	$year  = $fecha['year'];
	$hoy   = $dia.'-'.$mes.'-'.$year;
	
	if($diad == "") 
		$diad = "01";
	if($diaa == "") 
		$diaa = "29";
	if($mesd == "")
		$mesd = $mes;
	if($mesa == "")
		$mesa = $mes;
	if($anoa == "")
		$anoa = $year;
	if($anod == "")
		$anod = $year;
 
		if(trim($cod_almacen) != "") {
			$rs6 = pg_exec("SELECT 
						ch_almacen ,
						ch_nombre_almacen 
					FROM 
						inv_ta_almacenes 
					WHERE 
						ch_clase_almacen='1' 
						AND  trim(ch_almacen)=trim('$cod_almacen')  
					ORDER BY 
						ch_almacen
					");
		
			$R6 = pg_fetch_row($rs6,0);
			$sucursal_dis = $R6[1];
			$sucursal_val = $R6[0];
			$almacen      = $cod_almacen;
		}

		if($cod_almacen == "") {
			$cod_almacen = $almacen;
		}

		if($cod_tanque == "") {
			$cod_tanque = "01";
		}

		if($buscar_por == "") {
			$buscar_por = "1";
		}
	
		$fechad = $diad.'-'.$mesd.'-'.$anod;
		$fechaa = $diaa.'-'.$mesa.'-'.$anoa;

		if($almacen == ''){
			$almacen = $_SESSION['almacen'];
		}

		$rs1    = pg_exec($coneccion,"
						SELECT 
							'00',
							'00 -- TODOS'
		
						UNION

						SELECT DISTINCT 
							a.ch_tanque,
							a.ch_tanque  || ' -- ' || b.ch_nombrecombustible 
						FROM 
							comb_ta_tanques a,
							comb_ta_combustibles b,
							comb_ta_tanques c
						WHERE 
							a.ch_codigocombustible=b.ch_codigocombustible
							AND a.ch_tanque=c.ch_tanque
							AND c.ch_codigocombustible=b.ch_codigocombustible
							AND c.ch_sucursal=trim('" . $almacen . "')
						ORDER BY
							1 ASC");

		$rs2 = pg_exec("	SELECT 
						ch_almacen ,
						ch_nombre_almacen 
					FROM 
						inv_ta_almacenes 
					WHERE 
						ch_clase_almacen='1' 
					ORDER BY 
						ch_almacen");
	
		$comb = pg_exec("	SELECT 
						comb.ch_nombrecombustible 
					FROM 
						comb_ta_combustibles comb, 
						comb_ta_tanques tan 
					WHERE 
						tan.ch_codigocombustible=comb.ch_codigocombustible 
						AND tan.ch_tanque='$cod_tanque'
						AND tan.ch_sucursal=trim('$cod_almacen') ");

		$procesar = false;
		$combustibles = array();
		
		if(pg_numrows($comb) > 0) { //Si existe el combustible
			$C    = pg_fetch_row($comb,0);
			$comb = $C[0];
			$procesar = true;
			
			//OBTENEMOS ARRAY COMBUSTIBLES
			$combustibles[$cod_tanque] = array(
				"codigo_tanque" => $cod_tanque,
				"nombre_combustible" => $cod_tanque . " -- " . $comb
			);
		}elseif($cod_tanque == '00'){ //Si se selecciono todos los combustibles
			$comb = "TODOS";
			$procesar = true;

			//OBTENEMOS ARRAY COMBUSTIBLES
			for($i = 0; $i < pg_numrows($rs1); $i++) {
				$A = pg_fetch_row($rs1,$i);			
				
				if($A[0] == '00'){
					continue;
				}
				
				$combustibles[$A[0]] = array(
					"codigo_tanque" => $A[0],
					"nombre_combustible" => $A[1]
				);
			}
		}else { 
			$comb = "";
		}

		if($procesar){
			if($buscar_por == 0){ //Buscar por tanque
				foreach ($combustibles as $key => $value) {
					$REPSOB[$value['nombre_combustible']] = sobrantesyfaltantesReporte($almacen,$value['codigo_tanque'],$fechad,$fechaa, $_REQUEST["unidadmedida"], $_REQUEST["detallecompras"]);
				}
			}elseif($buscar_por == 1){ //Buscar por combustible
				foreach ($combustibles as $key => $value) {
					$REPSOB[$value['nombre_combustible']] = sobrantesyfaltantesReporte($almacen,$value['codigo_tanque'],$fechad,$fechaa, $_REQUEST["unidadmedida"], $_REQUEST["detallecompras"], false);
				}
			}
			
			/***Agregado 2020-01-13***/
			echo "<script>console.log('arreglo de combustibles: " . json_encode($combustibles) . "')</script>";
			echo "<script>console.log('REPSOB: " . json_encode($REPSOB) . "')</script>";
			echo "<script>console.log('almacen: " . json_encode($almacen) . "')</script>";
			echo "<script>console.log('cod_tanque: " . json_encode($cod_tanque) . "')</script>";
			echo "<script>console.log('fechad: " . json_encode($fechad) . "')</script>";
			echo "<script>console.log('fechaa: " . json_encode($fechaa) . "')</script>";
			echo "<script>console.log('unidadmedida: " . json_encode($_REQUEST["unidadmedida"]) . "')</script>";
			echo "<script>console.log('detallecompras: " . json_encode($_REQUEST["detallecompras"]) . "')</script>";
			echo "<script>console.log('buscar_por: " . json_encode($_REQUEST["buscar_por"]) . "')</script>";			
			/***/
			
			if($_REQUEST['detallecompras'] == "Si") {
				foreach ($combustibles as $key => $value) {
					$REPDET[$value['nombre_combustible']] = DetalleComprasReporte($almacen,$value['codigo_tanque'],$fechad,$fechaa, $_REQUEST["unidadmedida"], $_REQUEST["detallecompras"]);
				}
				echo "<script>console.log('REPDET: " . json_encode($REPDET) . "')</script>";
			}
		}
pg_close();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>REPORTE SOBRANTE Y FALTANTES</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" src="miguel-funciones.js"></script>
	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript">
	
		/* Agregado para cargar Combustibles o Tanque */
		// cargarTanqueCombustible({value:0});
		
		// function cargarTanqueCombustible(event){			
		// 	if(event.value == "0"){ //Si es tanque
		// 		console.log('Tanque')
		// 	}else if(event.value == "1"){ //Si es combustible
		// 		console.log('Combustible')
		// 	}
		// }		
		/* Fin Agregado para cargar Combustibles o Tanque */

		$(document).ready(function(){

			$.datepicker.regional['es'] = {
				    closeText: 'Cerrar',
				    prevText: '<Ant',
				    nextText: 'Sig>',
				    currentText: 'Hoy',
				    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
				    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
				    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
				    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
				    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
				    weekHeader: 'Sm',
				    dateFormat: 'dd/mm/yy',
				    firstDay: 1,
				    isRTL: false,
				    showMonthAfterYear: false,
				    yearSuffix: ''
			};

	                $.datepicker.setDefaults($.datepicker.regional['es']); 

 			$( "#diasd" ).datepicker({
				changeMonth: true,
				changeYear: true,
				onSelect:function(fecha,obj){

				var diasd = $('#diasd').val();

					$.ajax({
						data:{
							accion: "aprosys",
							dia:diasd
						},
						type: "POST",
						url: "cmb_contometro_ajax.php",
				            	success:function(response){
							$("#resultado").html(response);
						}
					});
				}
	        	});


 			$( "#diasa" ).datepicker({
				changeMonth: true,
				changeYear: true,
				onSelect:function(fecha,obj){

				var diasa = $('#diasa').val();

					$.ajax({
						data:{
							accion: "aprosys",
							dia:diasa,
						},
						type: "POST",
						url: "cmb_contometro_ajax.php",
				            	success:function(response){
							$("#resultado2").html(response);
				            	}

					});

				}

	        	});	

		});

	</script>

	<script type= "text/javascript">

		$(function() {
			$('#btnImprimir').click(function() {
				$.get("../utils/impresiones.php?imprimir=lpr&archivo=/sistemaweb/combustibles/sobrantes_faltantes_comb.txt");
				alert('Imprimiendo');	
			});
		});

	</script>

</head>

<body>

<h2 align="center" style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Reporte de Sobrantes y Faltantes</b></h2>

<form name="form1" method="post" action="cmb_sobyfaltantes.php">

<table align="center" border="0"> 
	<tr> 
		<td align="right">
			Seleccionar Almacen: 
		</td>
		<td>
			<select name="cod_almacen" onChange="javascript:form1.submit()">
			<?php
			for($i = 0; $i<pg_numrows($rs2); $i++) {		
				$B = pg_fetch_row($rs2,$i);
				if(trim($cod_almacen) == trim($B[0]))		
					print "<option value='$B[0]' selected >$B[0] -- $B[1]</option>";	
				else
					print "<option value='$B[0]' >$B[0] -- $B[1]</option>";	
			}
			?>
			</select>
		</td>
	</tr>
    <tr>
		<td align="right">
			Fecha Inicial: 
		</td>
		<td>
			<input type="text" name="diasd" id="diasd" maxlength="10" size="10" class="fecha_formato" value="<?php echo (empty($_REQUEST['diasd']) ? date('01/m/Y') : $_REQUEST['diasd']); ?>" />
			<span id="resultado"></span>
		</td>
	</tr>
    <tr>
			<td align="right">
				Fecha Final: 
			</td>
			<td>
				<input type="text" name="diasa" id="diasa" maxlength="10" size="10" class="fecha_formato" value="<?php echo (empty($_REQUEST['diasa']) ? date('d/m/Y', strtotime('-1 day')) : $_REQUEST['diasa']); ?>" />
				<span id="resultado2"></span>
			</td>
	<tr> 
		<td align="right">
			Buscar por:
		</td>
		<td>
			<?php //$checked_tanque      = ($buscar_por == 0) ? "checked=true" : ""; ?>
			<?php $checked_combustible = ($buscar_por == 1) ? "checked=true" : ""; ?>

			<!-- <input type="radio" id="tanque" name="buscar_por" value="0" <?php //echo $checked_tanque ?>>
			<label for="tanque">Tanque</label><br> -->
			<input type="radio" id="combustible" name="buscar_por" value="1" <?php echo $checked_combustible ?>>
			<label for="combustible">Combustible</label><br>
		</td>
	<tr>	
		<td align="right">
			Seleccionar: 
		</td>
		<td>
			<select name="cod_tanque">
			<?php
			if($comb != ""){
				echo "<option value='" . $cod_tanque . "'>" . $cod_tanque . "--" . $comb . "</option>";
			}
			for($i = 0; $i < pg_numrows($rs1); $i++) {
				$A = pg_fetch_row($rs1,$i);
				echo "<option value='$A[0]'>$A[1]</option>";
			}
			?>
			</select>
		</td>	
	<tr>	
		<td align="right">
			Seleccionar Unidad de Medida: 
		</td>
		<td>
			<select name="unidadmedida">
				<OPTION value="-"<?php if (isset($_REQUEST["unidadmedida"]) && $_REQUEST["unidadmedida"] == "-") echo " selected=\"1\""; ?>>No convertir unidades</OPTION>
				<OPTION value="Litros_a_Galones"<?php if (isset($_REQUEST["unidadmedida"]) && $_REQUEST["unidadmedida"] == "Litros_a_Galones") echo " selected=\"1\""; ?>>Convertir de litros a galones</OPTION>
				<OPTION value="Galones_a_Litros"<?php if (isset($_REQUEST["unidadmedida"]) && $_REQUEST["unidadmedida"] == "Galones_a_Litros") echo " selected=\"1\""; ?>>Convertir de galones a litros</OPTION>
			</select>
		</td>
	<tr>
		<td align="right">
			Detalle de Compras: 
		</td>
		<td>
			<select name="detallecompras">
				<OPTION value="No">No</OPTION>
				<OPTION value="Si">Si</OPTION>
			</select>
		</td>
	<tr>
		<td colspan="2" align="center">
			<button type="submit" value="Reporte" name="boton"><img src="/sistemaweb/icons/gbuscar.png" align="right">Buscar </button>
			&nbsp;&nbsp;&nbsp;<a href="reporte_excel_sobyfal.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_tanque=<?php echo $cod_tanque;?>&cod_almacen=<?php echo $cod_almacen;?>&medida=<?php echo $_REQUEST["unidadmedida"];?>" ><button type="button" ><img src="/sistemaweb/icons/gexcel.png" align="right">Excel </button></a>
		</td>
	</tr>
</table>
</form>

<?php
foreach ($REPSOB as $key => $value) {			
	$REP  = $REPSOB[$key];	
	$REP1 = $REPDET[$key];
	// echo "<pre>";
	// print_r($REP_DET[$key]);
	// echo "<pre>";
?>
<br>
<h2 align="center" style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Combustible <?php echo $key ?></b></h2>
<table align="center" border="0" >
	<tr> 
		<!--<th colspan="6" style="font-size:0.7em; color:black;background-color: #30767F">&nbsp;</td>-->
		<th colspan="5" style="font-size:0.7em; color:black;background-color: #30767F">&nbsp;</td>
		<th colspan="2" style="font-size:0.7em; color:white;background-color: #30767F">TRANSFERENCIAS</td>
		<th colspan="2" style="font-size:0.7em; color:black;background-color: #30767F">&nbsp;</td>
		<th colspan="2" style="font-size:0.7em; color:white;background-color: #30767F">DIFERENCIA</td>
	</tr>
	<tr> 
		<th style="font-size:0.7em; color:white;background-color: #30767F">FECHA</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">SALDO<br>(+)</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">COMPRA<br>(+)</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">AFERICIÓN<br>(+)</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">VENTA<br>(-)</td>
<!--		<th style="font-size:0.7em; color:white;background-color: #30767F">PRECIO</td>-->
		<th style="font-size:0.7em; color:white;background-color: #30767F">INGRESO<br>(+)</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">SALIDA<br>(-)</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">PARTE</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">VARILLA</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">DIARIA</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">ACUMULADA</td>
	</tr>

<?php
	$PRECIO_VENTA = 0.00;

	$TOT_SALDO 		= 0.00;
	$TOT_COMPRA 	= 0.00;
	$TOT_MEDICION 	= 0.00;
	$TOT_VENTA 		= 0.00;
	$TOT_INGRESO 	= 0.00;
	$TOT_SALIDA 	= 0.00;
	$TOT_PARTE 		= 0.00;
	$TOT_VARILLA 	= 0.00;
	$TOT_DIARIA 	= 0.00;
	$TOT_ACUMULADA 	= 0.00;

	for($i = 0; $i < count($REP); $i++){

		$PRECIO_VENTA = (empty($REP[$i][12]) ? '0.00' : $REP[$i][12]);

		$TOT_SALDO 		= 	$TOT_SALDO + $REP[$i][1];
		$TOT_COMPRA 	= 	$TOT_COMPRA + $REP[$i][2];
		$TOT_MEDICION 	= 	$TOT_MEDICION + $REP[$i][3];
		$TOT_VENTA 		= 	$TOT_VENTA + $REP[$i][4];
		$TOT_INGRESO 	= 	$TOT_INGRESO + $REP[$i][5];
		$TOT_SALIDA 	= 	$TOT_SALIDA + $REP[$i][6];
		$TOT_PARTE 		= 	$TOT_PARTE + $REP[$i][7];
		$TOT_VARILLA 	= 	$TOT_VARILLA + $REP[$i][8];
		$TOT_DIARIA 	= 	$TOT_DIARIA + $REP[$i][9];
		$TOT_ACUMULADA 	= 	$TOT_ACUMULADA + $REP[$i][10];

		$color = ($i%2==0?"#C9F4D4":"#FFFFFF");

		if ($REP[$i][9] >= 100)
			$dailycolor = "#FF0000";
		else
			$dailycolor = ($i%2==0?"#C9F4D4":"#FFFFFF");

		echo "<tr>\n";
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][0]."</td>\n";
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][1]."</td>\n";
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][2]."</td>\n";
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][3]."</td>\n";
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][4]."</td>\n";//VENTA
//		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$PRECIO_VENTA."</td>\n";//PRECIO VENTA
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][5]."</td>\n";
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][6]."</td>\n";
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][7]."</td>\n";
		echo "<td style=\"background-color: $dailycolor; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][8]."</td>\n";
		echo "<td style=\"background-color: $dailycolor; color: #000000\"><div align=\"right\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".number_format($REP[$i][9],3)."</td>\n";
		echo "<td style=\"background-color: $color; color: #000000\"><div align=\"right\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".number_format($REP[$i][10],3)."</td>\n";
		echo "</tr>\n";
	}
?>

	<tr>
		<td colspan="2" align="right" style="font-size:0.7em; color:white; background-color: #30767F"><b>TOTALES: </b></td>
		<td align="center" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo $TOT_COMPRA;?></b></td>
		<td align="center" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo $TOT_MEDICION;?></b></td>
		<td align="center" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo $TOT_VENTA;?></b></td>
<!--		<td align="center" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo $PRECIO_VENTA;?></b></td>-->
		<td align="center" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo $TOT_INGRESO;?></b></td>
		<td align="center" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo $TOT_SALIDA;?></b></td>
		<td align="center" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo $TOT_PARTE;?></b></td>
		<td align="center" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo $TOT_VARILLA;?></b></td>
		<td align="right" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo number_format($TOT_DIARIA,3);?></b></td>
		<td align="right" style="font-size:0.7em; color:white; background-color: #30767F"><b>&nbsp;</b></td>
	</tr>
</table>

<?php
if($_REQUEST['detallecompras'] == "Si") {
?>
<br><br>
<table border="0" align="center">
	<tr>
		<td align="center" style="font-size:0.7em; color:white;background-color:#30767F" colspan="5"><STRONG>COMPRAS DE COMBUSTIBLE</b<</td>
	</tr>
	<tr>
		<th style="font-size:0.7em; color:white;background-color: #30767F">FECHA</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">DOCUMENTO</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">KILOS</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">G.E.</td>
		<th style="font-size:0.7em; color:white;background-color: #30767F">GALONES</td>
	</tr>

	<?php

	$TOT_KILOS = 0.00;
	$TOT_GE = 0.00;
	$TOT_GALONES = 0.00;

	for ($i = 0; $i < count($REP1); $i++) {

		$color = ($i%2==0?"#C9F4D4":"#FFFFFF");

		$TOT_KILOS += $REP1[$i][2];
		$TOT_GE += $REP1[$i][3];
		$TOT_GALONES += $REP1[$i][4];

		echo "<tr>\n";
		echo "<td align=\"center\" style=\"background-color: $color\">".$REP1[$i][0]."</td>\n";
		echo "<td align=\"left\" style=\"background-color: $color;\">".$REP1[$i][1]."</td>\n";
		echo "<td align=\"center\" style=\"background-color: $color;\">".number_format($REP1[$i][2], 2, '.', ',')."</td>\n";
		echo "<td align=\"center\" style=\"background-color: $color;\">".number_format($REP1[$i][3], 4, '.', ',')."</td>\n";
		echo "<td align=\"right\" style=\"background-color: $color;\">".number_format($REP1[$i][4], 2, '.', ',')."</td>\n";
		echo "</tr>\n";
	}

	?>
	<tr>
		<td colspan="2" align="right" style="font-size:0.7em; color:white; background-color: #30767F"><b>Total: </b></td>
		<td align="right" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo number_format($TOT_KILOS, 2, '.', ',');?></b></td>
		<td align="right" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo number_format($TOT_GE, 4, '.', ',');?></b></td>
		<td align="right" style="font-size:0.7em; color:white; background-color: #30767F"><b><?php echo number_format($TOT_GALONES, 2, '.', ',');?></b></td>
		<!--
		<td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
			TOTALES</font></font></font></font></font>></td>
		<td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
			*</font></font></font></font></font>></td>
		<td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
			<?php echo $TOT_KILOS;?></font></font></font></font></font></font><font size="-4"></font></font></font></font></font>></td>
		<td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
			**</font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font>></td>
		<td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
			<?php echo number_format($TOT_GALONES,2);?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font>></td>
		-->
	</tr>
</table>
<?php
}
echo "<br>";
}
?>
<br>
<br>
</body>
</html>
