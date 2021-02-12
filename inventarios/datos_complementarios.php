<?php
include("/sistemaweb/global.php");
include("../valida_sess.php");
include("../functions.php");
include("js/funciones.php");
require("../clases/funciones.php");
include("store_procedures.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

include("js/inv_addmov_support.php");
$resultado=0;
global $tipo_formularioBD;
global $numeroBD ;
global $fecha_ordenBD;
global $turnoBD;
global $scopBD;
global $observBD;
global $ipBD;
global $usuarioBD;


		   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			   $ip = getenv("HTTP_CLIENT_IP");
		   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			   $ip = getenv("HTTP_X_FORWARDED_FOR");
		   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			   $ip = getenv("REMOTE_ADDR");
		   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			   $ip = $_SERVER['REMOTE_ADDR'];
		   else
			   $ip = "";

		list($dia,$mes,$anio)=explode("/",$fecha); 
		$fecha_form=$anio."-".$mes."-".$dia;

	    $CI = pg_exec("select count(turno_recepcion) as valor from inv_movialma_complemento where tran_codigo='".trim($num)."' and mov_numero='".trim($formulario)."' and mov_fecha='".$fecha_form."';");
	    $AI = pg_fetch_array($CI,0);
	    $tamanio= $AI["valor"];

		$fecha_recepcionBD = date('d/m/Y');
		$hora_recepcionBD = date('h:i');

		if ($tamanio>= 1){
		    $CI = pg_exec("select to_char(hora_recepcion, 'DD/MM/YYYY') as fecha_recepcion,to_char(hora_recepcion, 'HH:MI') as hora_recepcion,turno_recepcion as turno,numero_scop as scop,observacion from inv_movialma_complemento where tran_codigo='".trim($num)."' and mov_numero='".trim($formulario)."' and mov_fecha='".$fecha_form."';");
		    $AI = pg_fetch_array($CI,0);
			$tipo_formularioBD = trim($num);
			$numeroBD = trim($formulario);
			$fecha_ordenBD = $fecha_form;

			if ($AI["fecha_recepcion"] != '') $fecha_recepcionBD = $AI["fecha_recepcion"];
			if ($AI["hora_recepcion"] != '') $hora_recepcionBD = $AI["hora_recepcion"];
			
			$turnoBD = $AI["turno"];
			$scopBD = $AI["scop"];
			$observBD = $AI["observacion"];
			$ipBD = $ip;
			$usuarioBD = $_SESSION['auth_usuario'];
		}

		$turno_label="";
	    for($i=1;$i<10;$i++){
	      $turno_label .= "<option value='".$i."'".($i == $turnoBD?" selected ":"").">".$i."</option>";
	    }

switch($accion){
	case "Cambiar":
		$resultado=pg_exec("delete from inv_movialma_complemento where tran_codigo='".trim($tipo_formulario)."' and mov_numero='".trim($numero)."' and mov_fecha='".$fecha_orden."';");
		$resultado=pg_exec("insert into inv_movialma_complemento (tran_codigo,mov_numero,mov_fecha,hora_recepcion,turno_recepcion,numero_scop, observacion,auditoria_ip,auditoria_usuario) values ('".trim($tipo_formulario)."','".trim($numero)."','".$fecha_orden."',to_timestamp(('$fecha_recepcion $hora_recepcion'), 'DD/MM/YYYY hh:mi'),'".$turno."','".$scop."','".$observ."','".$ip."','".$_SESSION['auth_usuario']."');");

		$tipo_formularioBD = trim($tipo_formulario);
		$numeroBD = trim($numero);
		$fecha_ordenBD = $fecha_orden;
		$turnoBD = $turno;
		$scopBD = $scop;
		$observBD = $observ;
		$ipBD = $ip;
		$usuarioBD = $_SESSION['auth_usuario'];
	break;
}
?>
<style>
#mensaje{
	font-family: Tahoma, Verdana, Arial;
	font-size: 11px;
	color: #707070;
	background-color: #FFFFFF;
	border-width:0;
}
</style>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Datos Complementarios</title>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
  <script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
  <script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<script language='JavaScript' type='text/javascript'>

function isNumberKey(evt)
{
	var charCode = (evt.which) ? evt.which : evt.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;
	return true;
}

function Guardar(){
	if(document.getElementById("scop").value == '' || document.getElementById("observ").value == '') {
		alert('Faltan ingresar datos');
	}
	else{
		document.getElementById("accion").value = 'Cambiar';
		document.getElementById("form").submit();
		//location.reload();
		document.getElementById("mensaje").value = '*** Datos almacenados correctamente ***';
	}
}
</script>
</head>
<body>
<center>
<h3 style="font-weight:bold; color:blue">DATOS COMPLEMENTARIOS</h3>
<br/>
<form action="" method="post" name="form" id="form">
<table border="1">
<tr>
	<td width="230" style="font-weight:bold; color:blue">Tipo de Formulario</td>
	<td width="230" style="font-weight:bold; color:blue" align="right"><?php echo $t_formulario;?><input type="hidden" name="tipo_formulario" value="<?php echo $num;?>"></td>
</tr>
<tr>
	<td style="font-weight:bold;">Numero de Formulario</td>
	<td style="font-weight:bold;" align="right"><?php echo $formulario;?><input type="hidden" name ="numero" id="numero" value="<?php echo $formulario;?>"></td>
</tr>
<tr>
	<td style="font-weight:bold;">Fecha</td>
	<td style="font-weight:bold;" align="right"><?php echo $fecha;?><input type="hidden" name ="fecha_orden" id="fecha_orden" value="<?php echo $fecha_form;?>"></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>

<tr>
	<td style="font-weight:bold;" align="left" colspan="2">RECEPCION</td>
</tr>
<tr>
	<td>Fecha y Hora</td>
	<td align="right">
	<input id="fecha_recepcion" name="fecha_recepcion" class="form_input" type="text" maxlength="10" size ="10" value="<?php echo $fecha_recepcionBD;?>">
	<input id="hora_recepcion" name="hora_recepcion" class="form_input" type="text" maxlength="5" size ="10" value="<?php echo $hora_recepcionBD;?>">
	<a href="javascript:show_calendar('form.fecha_recepcion');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
	</td>
</tr>
<tr>
	<td>Turno</td>
	<td align="right"><select name="turno" id="turno"><?php echo $turno_label;?></select></td>
</tr>
<tr>
	<td>Numero SCOP</td>
	<td align="right"><input type="text" name="scop" id="scop" style="text-align: right" onkeypress="return isNumberKey(event)" value="<?php echo $scopBD;?>"></td>
</tr>
<tr>
	<td>Observacion</td>
	<td align="right"><input type="text" name="observ" id="observ"  maxlength="200" size ="40" value="<?php echo $observBD;?>"></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<th><input type="button" name="boton" value="Grabar" onclick="javascript:Guardar();window.close();"><input type="hidden" name="accion" id="accion"></th>
	<th><input type="button" name="salir" value="Cancelar" onclick="window.close();"></th>
</tr>
</table>
<br/>
<input type="text" class="input" id="mensaje" name="mensaje" style="text-align: center; font-weight:bold; color:blue" size="50">
</form>
</center>
</body>
</html>
