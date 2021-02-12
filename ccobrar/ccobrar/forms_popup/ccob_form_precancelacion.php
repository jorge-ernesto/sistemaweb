<?php
include("../../valida_sess.php");
//include("../../menu_princ.php");
include("../../functions.php");
include("../store_procedures.php");

require("../../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}


//$items_precancelacion = $_SESSION["ITEMS_PRECANCELACION"] ;
//$items_pre = $items_precancelacion[0];
//for($i=0;$i<count($items_pre);$i++){
	
//}
$items_pre = $_SESSION["ITEMS_PRECANCELACION"];

$vars = cortarCadena2($items_pre,"#");
//CAB.ch_tipdocumento||'#'||CAB.ch_seriedocumento||'#'||CAB.ch_numdocumento||'#'||CAB.cli_codigo as CLAVE
$tip_docu 	= $vars[0];
$serie_docu = $vars[1];
$num_docu 	= $vars[2];
$cli_codigo = $vars[3];
echo "docu : ".$tip_docu."<br>";
echo "serie: ".$serie_docu."<br>";
echo "nume: ".$num_docu."<br>";
echo "clie: ".$cli_codigo."<br>";

switch($accion){

	case "Precancelar":
	
		pg_exec("update ccob_ta_cabecera set 
				dt_fecha_precancelado    = to_date('$c_fecha_precancelado','dd/mm/yyyy') ,
				ch_sucursal_precancelado = '$c_sucursal_precancelado',
				nu_importe_precancelado  = $c_importe_precancelado
				where cli_codigo='$cli_codigo'			and		ch_tipdocumento='$tip_docu'
				and	  ch_seriedocumento='$serie_docu'	and		ch_numdocumento='$num_docu' 
				");	
	
		print "<script>window.close();</script>";
	break;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script>
function precancelacion_mandarDatos(form,opcion){
	form.accion.value=opcion;
	form.submit();
}
</script>
<title>PRECANCELACIONES</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../acosa.css" rel="stylesheet" type="text/css">
</head>

<body>
<form name="form1" method="post" action="">
  DATOS DE PRECANCELACION
  <table width="91%" border="1">
    <tr>
      <td width="32%">FECHA DE PRECANCELACION</td>
      <td width="24%"><input type="text" name="c_fecha_precancelado"></td>
      <td width="30%">&nbsp;</td>
      <td width="4%">&nbsp;</td>
      <td width="10%">&nbsp;</td>
    </tr>
    <tr>
      <td height="23">SUCURSAL DE PRECANCELACION</td>
      <td><select name="c_sucursal_precancelado">
	  	<?php $rsf = combo("almacenes");
			for($i=0;$i<pg_numrows($rsf);$i++){
				$A = pg_fetch_array($rsf,$i);
				print "<option value='$A[0]'>$A[1]</option>";
			}
		?>
        </select></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>IMPORTE PRECANCELADO</td>
      <td><input type="text" name="c_importe_precancelado"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><input type="button" name="btn_aceptar" value="Precancelar" onClick="javascript:precancelacion_mandarDatos(form1,'Precancelar');">
        <input type="hidden" name="hiddenField"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
<?php pg_close($conector_id); ?>