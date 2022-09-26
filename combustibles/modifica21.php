<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}

include("../menu_princ.php");
require("../clases/funciones.php");	
include("/sistemaweb/functions.php");

$funcion     = new class_funciones;
$clase_error = new OpensoftError;
$coneccion   = $funcion->conectar("","","","","");

//$version = explode('.', PHP_VERSION);
//$php_version = $version[0] . '.' . $version[1];
//if ( $php_version == '5.6' ) {
//}

session_start();

date_default_timezone_set('America/Lima');

include_once('/sistemaweb/include/mvc_sistemaweb.php');
include_once('/sistemaweb/include/dbsqlca.php');

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

$turno_label = "";
$resultado1  = 0;
$resultado2  = 0;
$tformu      = "";
$activa	     = 0;
$turno 		= null;

$tipoformu 		= $_POST['tipoformu'];
$c_formulario 	= $_POST['c_formulario'];
$fbuscar 		= $_POST['fbuscar'];
$boton 			= $_POST['boton'];
$c_articulo 	= $_POST['c_articulo'];
$iAllProduct 	= $_POST['iAllProduct'];

//Capturar valores para el UPDATE
$mov_fecha 		= $_POST['mov_fecha'];
$fecha_text2 	= $_POST['fecha_text2'];
$c_cantidad 	= $_POST['c_cantidad'];
$c_unitario 	= $_POST['c_unitario'];
$c_costototal 	= $_POST['c_costototal'];
$c_tipodoc 		= $_POST['c_tipodoc'];
$c_docref 		= $_POST['c_docref'];

$iValidarAnularCompra = $_POST['iValidarAnularCompra'];

$usuario = $_SESSION['auth_usuario'];
/*
$usuario = 'ADMIN';
if ( $php_version == '5.6' ) {
	$usuario = substr($_SESSION['auth_usuario'],0,10);
}
*/
	

switch ($boton) {
	case "allProduct":
		$cdia = substr($fbuscar,6,4)."-".substr($fbuscar,3,2)."-".substr($fbuscar,0,2);

		$almacen = $_SESSION['almacen'];
		$turno = 0;

		$sql = "SELECT validar_consolidacion('" . $cdia . "', " . $turno . ",'" . $almacen . "')";

		$ejecuta = pg_exec($sql);

		if (pg_numrows($ejecuta) > 0)
			$consolida	= pg_result($ejecuta,0,0);

		$status = $sqlca->query("
		SELECT
			COUNT(*) AS existe,
			SUM(mov_cantidad) AS qt_cantidad
		FROM 
			inv_movialma
		WHERE 
			tran_codigo = '" . trim($tipoformu) . "'
			AND mov_numero = '" . trim($c_formulario) . "'
			AND TO_DATE(TO_CHAR(mov_fecha, 'dd/mm/yyyy'), 'dd/mm/yyyy') = TO_DATE('" . $fbuscar . "', 'dd/mm/yyyy')
		");
		$row = $sqlca->fetchRow();
		settype($row['existe'], 'int');
		settype($row['qt_cantidad'], 'int');
		if($consolida == 1){
			print "<script> alert('Dia Consolidado!'); </script>";
		} else if ($row['existe'] > 0 && $row['qt_cantidad'] == 0) {
			print "<script> alert('Compra anulada'); </script>";
		} else{
			$tformu = trim($tipoformu);
			$query = "
			SELECT
				TD.tab_desc_breve AS no_tipo_documento,
				SUBSTR(MOVI.mov_docurefe, 1, 4) AS nu_serie_documento,
				SUBSTR(MOVI.mov_docurefe, 5, 8) AS nu_numero_documento,
				TO_CHAR(MOVI.mov_fecha,'dd/mm/yyyy hh24:mi:ss') AS fe_emision,
				PROVEE.pro_razsocial AS no_razon_social,
				PRO.art_codigo || ' ' || PRO.art_descripcion AS producto,
				MOVI.mov_cantidad AS qt_cantidad,
				MOVI.mov_costounitario AS ss_costo_unitario,
				MOVI.mov_costototal AS ss_total
			FROM 
				inv_movialma AS MOVI
				LEFT JOIN int_articulos AS PRO ON (PRO.art_codigo = MOVI.art_codigo)
				LEFT JOIN int_proveedores AS PROVEE ON(MOVI.mov_entidad = PROVEE.pro_codigo)
				LEFT JOIN int_tabla_general AS TD ON(MOVI.mov_tipdocuref = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
			WHERE 
				MOVI.tran_codigo = '" . trim($tipoformu) . "'
				AND MOVI.mov_numero	= '" . trim($c_formulario) . "'
				AND TO_DATE(TO_CHAR(MOVI.mov_fecha, 'dd/mm/yyyy'), 'dd/mm/yyyy') = TO_DATE('" . $fbuscar . "', 'dd/mm/yyyy')
			";

			if ($sqlca->query($query) < 0 ){
				print "<script>alert('Error al buscar');</script>";
			} else if ($sqlca->query($query) == 0 ){
				print "<script>alert('No existe formulario');</script>";
			} else {
				$activa = 2;
                $arrData = $sqlca->fetchAll();
			}
		}
		break;

	case "Buscar": //
		$cdia = substr($fbuscar,6,4)."-".substr($fbuscar,3,2)."-".substr($fbuscar,0,2);

		$almacen 	= $_SESSION['almacen'];
		$turno 		= 0;

		$sql = "SELECT validar_consolidacion('" . $cdia . "', " . $turno . ",'" . $almacen . "')";

		$ejecuta = pg_exec($sql);

		if (pg_numrows($ejecuta) > 0)
			$consolida	= pg_result($ejecuta,0,0);

		if($consolida == 1){
			print "<script> alert('Dia Consolidado!'); </script>";
		}else{

			$tformu = trim($tipoformu);
			$query = "
			SELECT 
				i.tran_codigo,
				i.mov_numero,
				i.mov_fecha,
				i.art_codigo,
				i.mov_costounitario,
				i.mov_cantidad,
				i.mov_costototal,
				i.mov_tipdocuref,
				i.mov_docurefe,
				to_char(i.mov_fecha,'dd/mm/yyyy') as fecha,
				i.art_codigo,
				c.turno_recepcion as turno,
				c.numero_scop scop,
				to_char(c.hora_recepcion, 'DD/MM/YYYY HH24:MI:SS') as hora_rec
			FROM 
				inv_movialma i
				LEFT JOIN inv_movialma_complemento c ON	(i.tran_codigo = c.tran_codigo AND i.mov_numero = c.mov_numero AND DATE(i.mov_fecha) = DATE(c.mov_fecha))
			WHERE 
				i.tran_codigo 		= '" . $tipoformu . "'
				AND i.mov_numero 	= '" . $c_formulario . "'
				AND i.art_codigo 	= '" . $c_articulo . "'
				AND TO_DATE(TO_CHAR(i.mov_fecha,'dd/mm/yyyy'),'dd/mm/yyyy') = TO_DATE('" . $fbuscar . "','dd/mm/yyyy')
			";
			//echo $query;
			$rs = pg_exec($query) ;

			if (pg_numrows($rs) > 0) { 
				$activa = 1;
				$costounitario	= pg_result($rs,0,"mov_costounitario");
				$cantidad		= pg_result($rs,0,"mov_cantidad");
				$mov_fecha		= pg_result($rs,0,"mov_fecha"); 
				$mov_costototal	= pg_result($rs,0,"mov_costototal");
				$mov_tipdocuref	= pg_result($rs,0,"mov_tipdocuref");
				$mov_docurefe	= pg_result($rs,0,"mov_docurefe");
				$c_fecha		= pg_result($rs,0,"fecha");
				$c_articulo		= pg_result($rs,0,"art_codigo");
				$turno_txt		= pg_result($rs,0,"turno");
				$scop_txt		= pg_result($rs,0,"scop");
				$fecha_txt		= pg_result($rs,0,"fecha_rec");
				$hora_txt		= pg_result($rs,0,"hora_rec");
			} else { 
				print "<script> alert('No existe formulario'); </script>" ;
			}
		}
		break;

	case "Grabar":

		$ndia = substr($fecha_text2,6,4)."-".substr($fecha_text2,3,2)."-".substr($fecha_text2,0,2)." 00:00:00";

		$query = "
		UPDATE 
			inv_movialma 
		SET 
			mov_fecha 				= '" . $ndia . "',
			mov_cantidad 			= " . $c_cantidad . ",
			mov_costounitario 		= " . $c_unitario . ",
			mov_costototal 			= " . $c_costototal . ",
			mov_tipdocuref 			= '" . $c_tipodoc . "',
			mov_docurefe 			= '" . $c_docref . "',
			mov_fecha_actualizacion = NOW(),
			mov_usuario = '".$usuario."'
		WHERE 
			tran_codigo 	= '" . $tipoformu . "' 
			AND mov_numero 	= '" . $c_formulario . "' 
			AND art_codigo 	= '" . $c_articulo . "' 
			AND mov_fecha 	= '" . $mov_fecha . "'
		";
		//error_log(json_encode($query));

		$resultado1 = pg_exec($query) ;

		if(trim($tipoformu) == '21'){
			$query = "
			UPDATE 
				inv_movialma_complemento 
			SET 
				mov_fecha		= '" . $fecha_text2 . "'::DATE,
				turno_recepcion	= " . $turno . ",
				numero_scop		= " . $scop . ",
				hora_recepcion	= to_timestamp(('" . $fecha_recepcion . " " . $hora_recepcion . "'), 'DD/MM/YYYY hh:mi'),
				auditoria_usuario = '".$usuario."'
			WHERE 
				tran_codigo		= '21' 
				AND mov_numero	= '" . $c_formulario ."'
			";
			//error_log(json_encode($query));
			$resultado2 = pg_exec($query);
		}

		if ($resultado1 != 0) {
			print "<script> alert('DATOS ACTUALIZADOS CORRECTAMENTE'); </script>";
		} else {
			print "<script> alert('ERROR AL ACTUALIZAR LOS DATOS'); </script>"; 
		}

		$query = " 
		UPDATE
			inv_calculo_glp
		SET
			mov_fecha = TO_DATE('" . $fecha_text2 ."','dd/mm/yyyy')
		WHERE 
			tran_codigo 	= '" . $tipoformu . "'
			AND mov_numero 	= '" . $c_formulario . "'
			AND art_codigo	= '" . $c_articulo . "'
		";
		//error_log(json_encode($query));

		if($mov_fecha != '')	
			$query .= "AND mov_fecha::DATE = '" . $mov_fecha . "'::DATE";
			
		pg_exec($query);

		break;

	case "Nuevo":
		$activa 		= 0;
		$c_formulario 	= "";
		$c_articulo 	= "";
		$tipoformu 		= "";
		break;

	case "anularCompra":
		if ($iValidarAnularCompra == '0'){
			$fe_emision = substr($fbuscar,6,4)."-".substr($fbuscar,3,2)."-".substr($fbuscar,0,2);
			$query = "
			UPDATE 
				inv_movialma 
			SET
				mov_cantidad = 0.00,
				mov_costounitario = 0.00,
				mov_costototal = 0.00,
				mov_usuario = '".$usuario."'
			WHERE 
				tran_codigo 	= '" . trim($tipoformu) . "'
				AND mov_numero 	= '" . trim($c_formulario) . "'
				AND mov_fecha::DATE	= '" . trim($fe_emision) . "'
			";

			if ($sqlca->query($query) < 0 ){
				print "<script>alert('Error al actualizar');</script>";
			} else {
				print "<script> alert('Registro anulado satisfactoriamente'); </script>";
			}
		} else {
			print "<script> alert('Aceptar mensaje confirmación'); </script>";
		}
		break;
}

for($i = 1; $i < 10; $i++) {
	$turno_label .= "<option value='".$i."'".($i == $turno_txt?" selected ":"").">".$i."</option>";
}

$sql = "SELECT tran_codigo, tran_descripcion FROM inv_tipotransa WHERE tran_valor='S' ORDER BY tran_codigo;";
$rs  = pg_exec($sql) ;
$tiposformu = "";
for ($i = 0; $i < pg_numrows($rs); $i++) { 
	$cod = pg_result($rs,$i,"tran_codigo");
	$des = pg_result($rs,$i,"tran_descripcion");
	$tiposformu .= "<option value='".$cod."'".($cod == $tipoformu?" selected ":"").">".$cod." - ".$des."</option>";
}
?>

<h2 align="center"><b>Actualizar Datos de Formulario</b></h2>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Modificacion de Formulario</title>
	<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
    <link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
    <script src="/sistemaweb/js/jquery-ui.js"></script>
	<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
	<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
	<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>
	<style type="text/css">
		.tr-producto{
			visibility: hidden;
		}
	</style>
	<script type="text/javascript">
		function cumpleReglas(simpleTexto) {
			var expresion = new RegExp("^(|([0-9]{1,8}(\\.([0-9]{1,6})?)?))$");
	    	if(expresion.test(simpleTexto))
	        	return true;
	    	return false;
		}

		function revisaCadena(textItem) {
			var textoAnterior = textItem.value;
			if(textItem.value.substring(0,1) == '.') 
		        textItem.value = '0' + textItem.value;

			if(!cumpleReglas(textItem.value))
	        	textItem.value = textItem.value.substring(0,textoAnterior.length-1);
	    	else
	        	textoAnterior = textItem.value;
		}

		function allProduct(value){
			$(' #tr-producto' ).removeClass('tr-producto');
			$(' #buscar' ).removeClass('tr-producto');
			$(' #btn-allProduct' ).addClass('tr-producto');
			if (value == 0){
				$(' #tr-producto' ).addClass('tr-producto');
				$(' #buscar' ).addClass('tr-producto');
				$(' #btn-allProduct' ).removeClass('tr-producto');
			}
		}

		$(document).ready(function(){
			var iAllProduct = '';
			iAllProduct = '<?php echo $iAllProduct; ?>';

			$(' #tr-producto' ).removeClass('tr-producto');
			$(' #buscar' ).removeClass('tr-producto');
			$(' #btn-allProduct' ).addClass('tr-producto');
			
			if (iAllProduct == 0){
				$(' #tr-producto' ).addClass('tr-producto');
				$(' #buscar' ).addClass('tr-producto');
				$(' #btn-allProduct' ).removeClass('tr-producto');
			}

			$.datepicker.regional['es'] = {
			    closeText: 'Cerrar',
			    prevText: '<Ant',
			    nextText: 'Sig>',
			    currentText: 'Hoy',
			    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
			    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
			    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
			    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
			    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
			    weekHeader: 'Sm',
			    dateFormat: 'dd/mm/yy',
			    firstDay: 1,
			    isRTL: false,
			    showMonthAfterYear: false,
			    yearSuffix: ''
			};

			$.datepicker.setDefaults($.datepicker.regional['es']);


			$( "#fbuscar" ).datepicker({
				changeMonth: true,
				changeYear: true,
				onSelect:function(fecha,obj){

					$('#cargardor').css({'display':'block'});
				    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

					var fecha = $("#fbuscar").val();
					var almacen = $("#almacen").val();

					fecha = fecha.substr(6, 4) + '-' + fecha.substr(3, 2) + '-' + fecha.substr(0, 2);

					$.ajax({
						type: "POST",
						url: "/sistemaweb/inventarios/forms/fecha.php",
						data:{
							fecha:fecha,
							almacen:almacen
						},
						success:function (response){
							$('#cargardor').css({'display':'none'});
							if(response.length > 12){//VALIDAR PARA QUE NO MUESTRE EL ECHO DE FECHA EN EL FRONT END
								$("#resultado").html(response);
								$("#buscar").prop( "disabled", true );
							}else{
								$("#resultado").html('');
								$("#buscar").prop( "disabled", false );
							}
						}
					});
				}
			});
		});

	</script>
</head>
<body>
	<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>
<form name="form1" id="form1" method="post" action="">
<br><div align="center">
<table bgcolor="#FFFFFF" cellspacing="0" cellpadding="2" border="0">
	<?php
		if($activa == 1 || $activa==2) 
			$onlyread = 'readonly="readonly" '; 
		else
			$onlyread = ''; 
	?>
	<tr>
      		<input type="hidden" name="almacen" id="almacen" value="<?php echo $_SESSION['almacen'] ?>" /></td>
    	</tr>
	<tr>
      		<td>Tipo de Formulario: </td>
      		<td><select name="tipoformu" id="tipoformu" <?php echo $onlyread; ?> ><?php echo $tiposformu; ?></select></td>
    	</tr>
	<tr> 
		<td>Formulario: </td>
		<td colspan="1"><input type="text" name="c_formulario" class="mayuscula" placeholder="Ingresar numero" autocomplete="off" required value="<?php echo $c_formulario; ?>" <?php echo $onlyread; ?> >		
	</tr>
	<tr>
		<td>Fecha Emision: </td>
		<td>
			<input maxlength="10" size="10" type='text' name ='fbuscar' id='fbuscar' class='fecha_formato' autocomplete="off" required value='<?php echo $_REQUEST['fbuscar']; ?>' <?php echo $onlyread; ?> /><span id="resultado"></span>
		</td>
    </tr>
    	<tr>
		<td>Todos los productos: </td>
		<td>
			<input type="radio" name="iAllProduct" onclick="allProduct(this.value);" value="0" <?php echo ($iAllProduct == '0' ? 'checked="checked"' : '') ?>> Si
			<input type="radio" name="iAllProduct" onclick="allProduct(this.value);" value="1" <?php echo ($iAllProduct == '1' ? 'checked="checked"' : '') ?>> No
		</td>
	</tr>
	<tr id="tr-producto" class="tr-producto">
		<td>Producto: </td>
		<td>
			<input type="hidden" id="txt-Nu_Id_Producto" name="c_articulo" placeholder="Ingresar codigo producto" value="<?php echo $c_articulo; ?>" <?php echo ($activa == 2 ? '' : $onlyread); ?> maxlength="25" size="25">
			<input type="text" id="txt-No_Producto" class="mayuscula" name="No_Producto" placeholder="Ingresar Código o Nombre" autocomplete="off" value="<?php echo $c_articulo; ?>" <?php echo ($activa == 2 ? '' : $onlyread); ?> maxlength="35" size="35">
		</td>
	</tr>
	<tr> 
		<td align="center" colspan="2">
			<button type="submit" name="boton" id="btn-allProduct" class="tr-producto" value="allProduct"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
			<button type="submit" name="boton" id="buscar" class="tr-producto" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
			<?php if($activa == 1 || $activa == 2) { ?>	
			<button type="submit" name="boton" id="Nuevo" value="Nuevo"><img src="/sistemaweb/icons/gadd.png" align="right" />Nuevo</button>
			<?php } ?>
		</td>
	</tr>
</table>

<?php if($activa == 1) { ?>

<table border="0">
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr> 
		<td>FECHA</td>
		<td><input type="text" name="fecha_text2" id="fecha_text2" value="<?php echo $c_fecha; ?>" readonly="readonly">
			<a href="javascript:show_calendar('form1.fecha_text2');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
		</td>		
	</tr>
	<tr> 		
		<td>DOCUMENTO</td>
		<td><input type="text" name="c_tipodoc" id="c_tipodoc" size="5" value="<?php echo $mov_tipdocuref; ?>">&nbsp;-&nbsp;
		    <input type="text" name="c_docref" id="c_docref" value="<?php echo $mov_docurefe; ?>"></td>
	</tr>
	<tr> 
		<td>COSTO TOTAL</td>
		<td><input type="text" name="c_costototal" id="c_costototal" onKeyUp="revisaCadena(this)" value="<?php echo $mov_costototal; ?>" onblur="parseFloat(this.value) / parseFloat(document.getElementById('c_cantidad').value);"></td>
	</tr>
	<tr> 
		<td><input type="hidden" name="mov_fecha" value="<?php echo $mov_fecha; ?>">CANTIDAD</td>
		<td><input type="text" name="c_cantidad" id="c_cantidad" onKeyUp="revisaCadena(this)" value="<?php echo $cantidad; ?>" onblur="document.getElementById('c_unitario').value = parseFloat(document.getElementById('c_costototal').value) / parseFloat(this.value);"></td>
	</tr>
	<tr> 		
      	<td>COSTO UNITARIO</td>
		<td><input type="text" name="c_unitario" id="c_unitario" onKeyUp="revisaCadena(this)" value="<?php echo $costounitario; ?>"></td>
	</tr>

<?php if($tformu == '21') { ?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr> 		
		<td colspan="2" align="center"><h4><strong>DATOS COMPLEMENTARIOS</strong></h4></td>
	</tr>
	<tr> 
      		<td>TURNO</td>
      		<td><select name="turno" id="turno"><?php echo $turno_label; ?></select></td>
    	</tr>
    	<tr> 
      		<td>NUMERO DE SCOP</td>
      		<td><input type="text" name="scop" id="scop" value="<?php echo $scop_txt; ?>"></td>
    	</tr>
    	<tr> 
      		<td>FECHA Y HORA RECEPCION</td>
      		<td>
			<input id="fecha_recepcion" name="fecha_recepcion" type="text" maxlength="10" size ="10" value="<?php echo $fecha_txt; ?>" readonly="readonly">
			<a href="javascript:show_calendar('form1.fecha_recepcion');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
			<input id="hora_recepcion" name="hora_recepcion" type="text" maxlength="5" size ="10" value="<?php echo $hora_txt; ?>">
      		</td>
    	</tr>
<?php } ?>

    	<tr><td colspan="2">&nbsp;</td></tr>
    	<tr>
      		<td colspan="2" align="center"><input type="submit" name="boton" value="Grabar"></td>
      		<td>&nbsp;</td>
    	</tr>
</table>
<?php } ?>

<?php if($activa == 2) { ?>
<table border="0">
	<thead>
		<tr><td colspan="2">&nbsp;</td></tr>
	</thead>
	<thead>
		<tr>
			<th class="grid_cabecera">Tipo</td>
			<th class="grid_cabecera">Serie</td>
			<th class="grid_cabecera">Número</td>
			<th class="grid_cabecera">F. Emisión</td>
			<th class="grid_cabecera">Proveedor</td>
			<th class="grid_cabecera">Producto</td>
			<th class="grid_cabecera">Cantidad</td>
			<th class="grid_cabecera">Costo Unitario</td>
			<th class="grid_cabecera">Total</td>
		</tr>
	</thead>
	<tbody>
		<?php
		$color = '';
		$i = 0;
		foreach ($arrData as $row) {
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$row = (object)$row;
			?>
			<tr>
				<td class="<?php echo $color; ?>"><?php echo $row->no_tipo_documento ?></td>
				<td class="<?php echo $color; ?>"><?php echo $row->nu_serie_documento ?></td>
				<td class="<?php echo $color; ?>"><?php echo $row->nu_numero_documento ?></td>
				<td class="<?php echo $color; ?>"><?php echo $row->fe_emision ?></td>
				<td class="<?php echo $color; ?>"><?php echo $row->no_razon_social ?></td>
				<td class="<?php echo $color; ?>"><?php echo $row->producto ?></td>
				<td class="<?php echo $color; ?>"><?php echo $row->qt_cantidad ?></td>
				<td class="<?php echo $color; ?>"><?php echo $row->ss_costo_unitario ?></td>
				<td class="<?php echo $color; ?>"><?php echo $row->ss_total ?></td>
			<?php
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr>
			<th class="grid_detalle_especial" colspan="9">
				<input type="checkbox" id="checkbox-msg" name="iValidarAnularCompra" value="0"> ¿Estas seguro de anular la compra?
			</th>
		</tr>
		<tr>
	  		<th class="grid_detalle_especial" colspan="9" align="center">
	  			<button type="submit" name="boton" id="btn-anularCompra" value="anularCompra"><img src="/sistemaweb/icons/anular.gif" align="right" />Anular</button>
	  		</th>
		</tr>
	</tfoot>
</table>
<?php } ?>
</div>
</form>
</body>
</html>
