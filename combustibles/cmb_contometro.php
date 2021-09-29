<?php
header("Location: rep_venta_combustible_x_lado.php");
die("");

include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
require("/sistemaweb/clases/funciones.php");
extract($_REQUEST);

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id = $funcion->conectar("","","","","");


$boton 			= null;
$diad 			= null;
$diaa 			= null;
$mesd 			= null;
$mesa 			= null;
$anoa 			= null;
$anod 			= null;
$cod_almacen 	= null;
$codproducto 	= null;
$fecha_de 		= null;
$fecha_hasta 	= null;
$rs3 			= null;
$rs4 			= null;

$and_cont2 		= null;
$and_cont3 		= null;
$and_cont4 		= null;
/*
echo "<br />";
echo "<br />";
echo "<br />";
echo "<pre>";
print_r($_POST);
echo "</pre>";
*/

$boton 			= $_POST['boton'];
$cod_almacen 	= $_POST['cod_almacen'];
$codproducto 	= $_POST['codproducto'];

if($boton != "Nuevo_Parte" && $boton != "Eliminar_Ultimo_Parte") {
	require("../menu_princ.php");
}

//include("../functions.php");

$fecha 	= getdate();
$dia 	= $fecha['mday'];
$mes 	= $fecha['mon'];
$year 	= $fecha['year'];
$hoy 	= $dia.'-'.$mes.'-'.$year;

if($diad == "") {
	$diad = "01";
}
if($diaa == "") {
	$diaa = "29";
}
if($mesd == "") {
	$mesd = $mes;
}
if($mesa == "") {
	$mesa = $mes;
}
if($anoa == "") {
	$anoa = $year;
}
if($anod == "") {
	$anod = $year;
}

if($cod_almacen == "") {
	//$and_cont = " AND cont.ch_sucursal=trim('$cod_almacen')";
	$and_cont = "";
	//$rs6 = pg_exec("SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' AND trim(ch_almacen) = trim('$cod_almacen') ORDER BY ch_nombre_almacen");
	$rs6 = pg_exec("SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY ch_nombre_almacen");
	$R6  = pg_fetch_row($rs6,0);
	$sucursal_dis = $R6[1];
	$sucursal_val = $R6[0];
//	$cod_almacen  = $almacen;
	$cod_almacen  = "";
	$and_almacem = "";
	$and_almacen_afe = "";
	$and_sucursal = "";
} else {
	$cod_almacen = trim($cod_almacen);
	$and_cont="AND cont.ch_sucursal=trim('$cod_almacen')";
	$rs6 = pg_exec("SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen = '1' AND trim(ch_almacen) = trim('$cod_almacen') ORDER BY ch_nombre_almacen");
	$R6 = pg_fetch_row($rs6,0);
	$sucursal_dis = $R6[1];
	$sucursal_val = $R6[0];
	$and_almacem = "AND comb.ch_sucursal=trim('$cod_almacen')";
	$and_almacen_afe = "AND af.es=trim('$cod_almacen')";
	$and_sucursal = "WHERE ch_sucursal = '$cod_almacen'";
}


if($codproducto == "TODOS"){
	$and_cont2 = "";
}else{
	$and_cont2 = "AND cont.ch_codigocombustible = TRIM('$codproducto')";
	$and_cont3 = "AND comb.ch_codigocombustible = TRIM('$codproducto')";
	$and_cont4 = "AND af.codigo = TRIM('$codproducto')";
}

if ($_REQUEST['diasd'] && $_REQUEST['diasa']) {
	$nuevofechad = explode('/',$_REQUEST['diasd']);
	$diad = $nuevofechad[0];
	$mesd = $nuevofechad[1];
	$anod = $nuevofechad[2];
	$nuevofechaa = explode('/',$_REQUEST['diasa']);
	$diaa = $nuevofechaa[0];
	$mesa = $nuevofechaa[1];
	$anoa = $nuevofechaa[2];
} else {
	$diad = date('d');
	$diaa = date('d');
	$mesd = date('m');
	$mesa = date('m');
	$anoa = date('Y');
	$anod = date('Y');
}

$rs1 		= pg_exec("select c.ch_numeroparte , c.ch_numeroparte  || '---------' || c.dt_fechaparte from comb_ta_contometros c ");
$rs2 		= pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' order by ch_nombre_almacen");
$productos	= pg_exec("
				SELECT DISTINCT
					f.product,
					ch_nombrecombustible
				FROM
					f_grade f
					LEFT JOIN comb_ta_combustibles c ON (c.ch_codigocombustible = f.product)
				ORDER BY
					f.product
			");

switch ($boton) {

        case "Nuevo_Parte":
                	pg_close();
                	header("Location: cmb_add_contometro.php");
        		break;

        case "Eliminar_Ultimo_Parte":
                	header("Location: cmb_edit_contometro.php?action=eliminar_ultimo&cod_almacen=$cod_almacen");
        		break;

        case "Reporte":

        	$fecha_de = $diad.'-'.$mesd.'-'.$anod;
        	$fecha_hasta = $diaa.'-'.$mesa.'-'.$anoa;

			$postrans = "pos_trans".$anod.$mesd;

			if(("pos_trans".$anoa.$mesa) != $postrans){
				?><script>alert("<?php echo 'Fecha no valida. Ambas fechas deben coincidir en el mismo mes.' ; ?> ");</script><?php
			} else if ($fecha_de > $fecha_hasta) {
				?><script>alert("<?php echo 'La fecha inicial no puede ser mayor a la final' ; ?> ");</script><?php
			} else {

                	$q3 = "	SELECT 
					cont.ch_numeroparte as parte, 
					cont.ch_codigocombustible, 
					cont.ch_tanque as tanque, 
					cont.ch_surtidor as manguera, 
					cont.nu_contometroinicialgalon, 
					cont.nu_contometrofinalgalon, 
					cont.nu_ventagalon, 
					cont.nu_contometroinicialvalor, 
					cont.nu_contometrofinalvalor, 
					cont.nu_ventavalor, 
					cont.nu_afericionveces_x_5, 
					cont.nu_consumogalon, 
					-cont.nu_descuentos, 
					comb.ch_nombrecombustible, 
					cont.dt_fechaparte, 
					cont.ch_responsable, 
					surt.ch_numerolado as lado,
					(
					SELECT
						ROUND((SUM(precio) / COUNT(*)),2)
					FROM
						pos_contometros
					WHERE
						dia = cont.dt_fechaparte
						AND num_lado::text = surt.ch_numerolado
						AND manguera = nu_manguera
					) as precio
				FROM 
					comb_ta_contometros cont
					LEFT JOIN comb_ta_surtidores surt ON (cont.ch_sucursal= surt.ch_sucursal and cont.ch_surtidor=surt.ch_surtidor)
					LEFT JOIN comb_ta_combustibles comb ON (cont.ch_codigocombustible=comb.ch_codigocombustible)
				WHERE 				
					cont.dt_fechaparte >= to_date('$fecha_de','DD-MM-YYYY')	
					and cont.dt_fechaparte <=to_date('$fecha_hasta','DD-MM-YYYY')
					$and_cont
					$and_cont2
				ORDER BY 
					parte,
					lado,
					manguera,
					tanque;";
				// echo "<pre>";
				// echo $q3;
				// echo "</pre>";

			$q4 = "SELECT
					C.codigo as codigo,
					COMB.descripcion as descripcion,
					ROUND(COMB.total_cantidad,3) as total_cantidad,
					ROUND(COMB.total_venta,2) as total_venta,
					(CASE WHEN AFC.af_cantidad IS NULL THEN
						COMB.af_cantidad
					ELSE
						AFC.af_cantidad
					END) as af_cantidad,
					(CASE WHEN AFC.af_total IS NULL THEN
						COMB.af_soles
					ELSE
						AFC.af_total
					END) as af_total,
					'0.000' as consumo_galon,
					'0.000' as consumo_valor,
					COMB.descuentos as descuentos,
					(CASE
						WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN (COMB.total_cantidad - COMB.af_cantidad)
						WHEN AFC.af_cantidad > 0 THEN (COMB.total_cantidad - AFC.af_cantidad)
						WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN (COMB.total_cantidad)
					END) AS resumen,
					(CASE
						WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN ((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
						WHEN AFC.af_total > 0 THEN ((COMB.total_venta + COMB.descuentos) - AFC.af_total)
						WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN (COMB.total_venta + COMB.descuentos)
					END) AS neto_soles				
				FROM

					(SELECT DISTINCT( ch_codigocombustible ) AS codigo FROM comb_ta_tanques ) C

					INNER JOIN 

					(SELECT
						comb.ch_codigocombustible AS codigo,
						cmb.ch_nombrecombustible AS descripcion,
						SUM(CASE WHEN comb.nu_ventagalon > 0 THEN comb.nu_ventavalor ELSE 0 END) AS total_venta,
						SUM(CASE WHEN comb.nu_ventagalon > 0 THEN comb.nu_ventagalon ELSE 0 END) AS total_cantidad,
						SUM(CASE WHEN comb.nu_ventagalon > 0 THEN (comb.nu_afericionveces_x_5 * 5) ELSE 0 END) AS af_cantidad,
						SUM(CASE WHEN comb.nu_ventagalon > 0 THEN ((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5) ELSE 0 END) AS af_soles,
						ROUND(SUM(comb.nu_descuentos),2) AS descuentos
					 FROM 
						comb_ta_contometros comb
						LEFT JOIN comb_ta_combustibles cmb ON (comb.ch_codigocombustible = cmb.ch_codigocombustible)
					 WHERE 	
						comb.dt_fechaparte BETWEEN to_date('$fecha_de', 'DD/MM/YYYY') and to_date('$fecha_hasta', 'DD/MM/YYYY')
						$and_almacem
						$and_cont3						
					GROUP BY 
						comb.ch_codigocombustible,
						cmb.ch_nombrecombustible
					) COMB on COMB.codigo = C.codigo
	
					LEFT JOIN

					(SELECT 
						af.codigo as codigo,
						SUM(af.importe) AS af_total,
						ROUND(SUM(af.cantidad), 3) AS af_cantidad
					FROM 
						pos_ta_afericiones af
					WHERE
						af.dia BETWEEN to_date('$fecha_de', 'DD/MM/YYYY') and to_date('$fecha_hasta', 'DD/MM/YYYY')
						$and_cont4
						$and_almacen_afe
					GROUP BY
						af.codigo
					)AFC ON AFC.codigo = C.codigo;
				";
				// echo "<pre>";
				// echo $q4;
				// echo "</pre>";
			}

       		break;
}
pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Configuracion de Contometros</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<script language="JavaScript" src="miguel-funciones.js"></script>
	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript">
	
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
							accion	: "aprosys",
							dia	: diasd
						},
						type	: "POST",
						url	: "cmb_contometro_ajax.php",
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
							accion	: "aprosys",
							dia	: diasa,
						},
						type	: "POST",
						url	: "cmb_contometro_ajax.php",
				            	success:function(response){
							$("#resultado2").html(response);
				            	}

					});

				}

	        	});	

		});
		
        	function mostrarVentanaImportar(form1){
                	window.open('cmb_contometros_auto_insert.php','miwin','width=200,height=220,scrollbars=no,menubar=no,left=60,top=60');
		}
			
        	function mostrarVentanaEliminar(form1){
                	window.open('cmb_contometro_eliminar.php','miwin','width=280,height=250,scrollbars=no,menubar=no,left=580,top=400');
		}
	
</script>
</head>

<body>
<h2 align="center"><b>PARTE DE VENTA</b></h2>
<hr noshade>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<FORM id="form1" name="form1" method="post" action="cmb_contometro.php">
<center>
<table border="0">
<tr>
	<td align="right">Seleccionar Almacen: </td>

	<td>
        	<select name="cod_almacen">
        	<?php
			
			if(empty($cod_almacen))
				$select = "";
			else
				$select = "selected";

				print "<option value='' $select>Todos los Almacenes</option>"; 

               	for($i=0;$i<pg_numrows($rs2);$i++) {
					$B = pg_fetch_row($rs2,$i);
					if($B[0] == $cod_almacen)
	                   	print "<option value='$B[0]' $select>$B[0] -- $B[1]</option>";
					else
	                   	print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
                }

                ?>
        	</select>
	</td>
</tr>
<tr>
	<td align="right">Fecha Inicio: </td>
	<td>
		<input type="text" name="diasd" id="diasd" maxlength="10" size="10" class="fecha_formato" value="<?php echo (empty($_REQUEST['diasd']) ? date('d/m/Y', strtotime('-1 day')) : $_REQUEST['diasd']); ?>" />
		<span id="resultado"></span>
	</td>
</tr>
<tr>
	<td align="right">Fecha Final: </td>
	<td>
		<input type="text" name="diasa" id="diasa" maxlength="10" size="10" class="fecha_formato" value="<?php echo (empty($_REQUEST['diasa']) ? date('d/m/Y', strtotime('-1 day')) : $_REQUEST['diasa']); ?>" />
		<span id="resultado2"></span>
	</td>
</tr>
<tr>
	<td align="right">Seleccionar Productos: </td>
	<td>
        	<select name="codproducto">
        	<?php

			print "<option value='TODOS' selected>Todos los Productos</option>";

	               	for($i=0; $i<pg_numrows($productos); $i++) {

				$C = pg_fetch_row($productos,$i);

				if($_REQUEST['codproducto'] == $C[0])
					$pintar="selected";
				else
					$pintar="";

	                	print "<option value='$C[0]' $pintar>$C[0] -- $C[1]</option>";

			}

                ?>

        	</select>
	</td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>

<tr>
	<td colspan="2" align="center">
	<button type="submit" value="Reporte" name="boton"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
	&nbsp;&nbsp;&nbsp;<a href="reporte_excel_combustible.php?fechad=<?php echo $fecha_de;?>&fechaa=<?php echo $fecha_hasta;?>&cod_almacen=<?php echo $cod_almacen;?>&codproducto=<?php echo $codproducto;?>" ><button type="button" ><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button></a>
	&nbsp;&nbsp;&nbsp;<button type="submit" value="Nuevo_Parte" name="boton"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar Parte</button>
	&nbsp;&nbsp;&nbsp;<button type="button" value="Eliminar_Ultimo_Parte" name="btn_eliminar" onClick="javascript:mostrarVentanaEliminar(form1);"><img src="/sistemaweb/icons/gdelete.png" align="right" />Eliminar Parte</button>
	&nbsp;&nbsp;&nbsp;<button type="button" value="Importar" name="btn_importar" onClick="javascript:mostrarVentanaImportar(form1);"><img src="/sistemaweb/icons/gimportar.png" align="right" />Importar Parte</button>
</tr>

<tr>
	<td colspan="2">&nbsp;</td>

</table>

<table width="1000" border="1" cellpadding="0" cellspacing="0">
        <!--DWLayoutTable-->
<tr>
	<td width="40" height="59" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Nro Parte</strong></font></div></td>
	<td width="32" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Cod. Art</strong></font></div></td>
	<td width="33" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Lado-Tq</strong></font></div></td>
	<td width="44" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Manguera</strong></font></div></td>
	<td width="20" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Precio</strong></font></div></td>
	<td width="54" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Inicial (galones)</strong></font></div></td>
	<td width="54" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Final (galones)</strong></font></div></td>
	<td width="46" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Galones<br>Vendidos </strong></font></div></td>
	<td width="53" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Cont&oacute;metro<br>Inicial (Soles)</strong></font></div></td>
	<td width="54" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Final (soles)</strong></font></div></td>
	<td width="46" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Soles<br>Vendidos </strong></font></div></td>
	<td width="50" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Afericiones</strong></font></div></td>
	<!--<td width="44" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Consumo</strong></font></div></td>-->
	<td width="44" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Descuentos</strong></font></div></td>
	<td width="100" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Descripci&oacute;n</strong></font></div></td>
	<td width="200" valign="top" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong><br>Fecha</strong></font></div></td>
</tr>

<?php
	$rs3 = $sqlca->query($q3);
	for($i=0; $i < $sqlca->numrows(); $i++){
		$E = $sqlca->fetchRow();
	?>
		<tr>
			<td height="21"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[0]; ?></font></div></td>
			<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[1]; ?></font></div></td>
			<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[16].' - '.$E[2]; ?></font></div></td>
			<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[3]; ?></font></div></td>
			<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[17]; ?></font></div></td>
			<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[4]; ?></font></div></td>
			<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[5]; ?></font></div></td>
			<td><div align="right"><font size="-7" face="Arial, Helvetica, sans-serif"><?php echo $E[6]; ?></font></div></td>
			<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[7]; ?></font></div></td>
			<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[8]; ?></font></div></td>
			<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[9]; ?></font></div></td>
			<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[10]; ?></font></div></td>
			<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[12]; ?></font></div></td>
			<td width="100" ><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[13]; ?></font></div></td>
			<td width="200" valign="top"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><?php echo $E[14].' - '.$E[15]; ?></font></div></td>
		</tr>
<?php
	}
?>
</table>
<tr>
	<td height="33">&nbsp;</td>
	<td>&nbsp;</td>
      	<td>&nbsp;</td>
      	<td>&nbsp;</td>
      	<td>&nbsp;</td>
      	<td>&nbsp;</td>
</tr>
</table>
<table width="763" border="1" cellpadding="0" cellspacing="0">
<tr>
	<td width="70" bgcolor="yellow"><div align="center"></div></td>
	<td width="90" bgcolor="yellow"><div align="center"></div></td>
	<td colspan="2" bgcolor="yellow"><div align="center"></div><div align="center"><font size="-5"><strong>RESUMEN DE VENTA</strong></font></div></td>
	<td colspan="2" bgcolor="yellow"><div align="center"><font size="-5"><strong>RESUMEN DE AFERICI&Oacute;N</strong></font></div><div align="center"></div></td>
	<td width="84" bgcolor="yellow"><div align="center"><font size="-5"><strong>DESCUENTOS</strong></font></div></td>		
	<td width="84" bgcolor="yellow"><div align="center"><font size="-5"><strong>RESUMEN</strong></font></div></td>
	<td width="215" bgcolor="yellow"><div align="center"><font size="-5"><strong>NETO</strong></font></div></td>
</tr>
<tr>
  	<td bgcolor="yellow"><div align="center"><font size="-5"><em>Producto</em></font></div></td>
  	<td width="90" bgcolor="yellow"><div align="center"><font size="-5"><em>Descripci&oacute;n</em></font></div></td>
  	<td width="83" bgcolor="yellow"><div align="center"><font size="-5"><em>Galones</em></font></div></td>
  	<td width="83" bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>
  	<td width="79" bgcolor="yellow"><div align="center"><font size="-5"><em>Galones</em></font></div></td>
  	<td width="79" bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>
  	<td width="83" bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>	  
  	<td bgcolor="yellow"><div align="center"><font size="-5"><em>Galones</em></font></div></td>
  	<td bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>
</tr>

<?php
	$rs4 = $sqlca->query($q4);

	$total_res_ven_gal = 0;
	$total_res_ven_val = 0;
	$total_res_afe_gal = 0;
	$total_res_afe_val = 0;
	$total_res_descuentos = 0;
	$total_resumen_gal = 0;
	$total_neto_val = 0;

	for($i=0;$i<$sqlca->numrows();$i++){

		$Q4 					= $sqlca->fetchRow();
		$total_res_ven_gal    	= $total_res_ven_gal    + $Q4[2];
		$total_res_ven_val    	= $total_res_ven_val    + $Q4[3];
		$total_res_afe_gal    	= $total_res_afe_gal    + $Q4[4];
		$total_res_afe_val    	= $total_res_afe_val    + $Q4[5];
		$total_res_descuentos 	= $total_res_descuentos + $Q4[8];
		$total_resumen_gal    	= $total_resumen_gal    + $Q4[9];
		$total_neto_val       	= $total_neto_val       + $Q4[10];
?>
	<tr>
		<td><div align="center"><font size="-5"><?php echo $Q4[0]; ?></font></div></td>
      	<td width="120"><div align="center"><font size="-5"><?php echo $Q4[1]; ?></font></div></td>
      	<td><div align="right"><font size="-5"><?php echo $Q4[2]; ?></font></div></td>
      	<td><div align="right"><font size="-5"><?php echo $Q4[3]; ?></font></div></td>
     	<td><div align="right"><font size="-5"><?php echo htmlentities(number_format($Q4[4], 3, '.', ',')); ?></font></div></td>
      	<td><div align="right"><font size="-5"><?php echo htmlentities(number_format($Q4[5], 3, '.', ',')); ?></font></div></td>
      	<td><div align="right"><font size="-5"><?php echo htmlentities(number_format($Q4[8], 3, '.', ',')); ?></font></div></td>
      	<td><div align="right"><font size="-5"><?php echo htmlentities(number_format($Q4[9], 3, '.', ',')); ?></font></div></td>
      	<td><div align="right"><font size="-5"><?php echo htmlentities(number_format($Q4[10], 3, '.', ',')); ?></font></div></td>
	</tr>
<?php } ?>
	<tr>
		<td><div align="center"><font size="2"><font size="-5"></font></font></div></td>
		<td><div align="center" style="color:blue"><font size="-5">TOTAL</font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5"><?php echo htmlentities(number_format($total_res_ven_gal, 3, '.', ',')); ?></font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5"><?php echo htmlentities(number_format($total_res_ven_val, 3, '.', ',')); ?></font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5"><?php echo htmlentities(number_format($total_res_afe_gal, 3, '.', ',')); ?></font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5"><?php echo htmlentities(number_format($total_res_afe_val, 3, '.', ',')); ?></font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5"><?php echo htmlentities(number_format($total_res_descuentos, 3, '.', ',')); ?></font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5"><?php echo htmlentities(number_format($total_resumen_gal, 3, '.', ',')); ?></font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5"><?php echo htmlentities(number_format($total_neto_val, 3, '.', ',')); ?></font></div></td>
	</tr>

</table>
<p>&nbsp;</p>
</center>
</FORM>
</body>
</html>
