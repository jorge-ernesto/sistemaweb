<?php
include("/sistemaweb/global.php");
include("../menu_princ.php");
include("../functions.php");
include("js/funciones.php");
require("../clases/funciones.php");
include("store_procedures.php");
include("config.php");

$accion = $_REQUEST['accion'];
$flag = 0;

switch($accion){
	case "Generar": // ejecuta actualizacion de tabla com_rec_proveedor		
		$flag = 1;
		break;
	default:
		$flag = 0;
		break;		
}

if($flag==1) {
	$arti = Array();
	$rs0 = pg_exec("SELECT DISTINCT art_codigo FROM inv_movialma WHERE tran_codigo='01' order by art_codigo;");
	$cant0 = pg_numrows($rs0);
	for ($i=0; $i<$cant0; $i++){
		$A0 = pg_fetch_array($rs0,$i);
		$arti[$i] = $A0[0];
	}

	for ($k=0; $k<$cant0; $k++){	
		$rs = pg_exec("SELECT 	mov_entidad, art_codigo, mov_costounitario, mov_fecha 
				FROM 	inv_movialma WHERE tran_codigo='01' and art_codigo='".trim($arti[$k])."' and 
					mov_fecha=(	SELECT 	max(mov_fecha) 
							FROM 	inv_movialma 
							WHERE 	tran_codigo='01' and art_codigo='".trim($arti[$k])."' 
							GROUP BY art_codigo);");
		$A = pg_fetch_array($rs,0);			

		$proveedor = $A[0];
		$articulo = $A[1];
		$costounitario = $A[2];
		$fecha = $A[3];
		$rs = pg_exec("INSERT INTO com_rec_pre_proveedor (pro_codigo, art_codigo, rec_moneda, rec_precio, rec_descuento1, rec_fecha_precio, rec_fecha_ultima_compra) VALUES ('$proveedor','$articulo', '01', '".$costounitario."' , 0.00, '".$fecha."', '".$fecha."')");					
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Para Ejecutar</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<body>
<form action="" method="post" name="form1">
  	<table width="734" border="1" cellpadding="0" cellspacing="0">
    		<tr> 
      		<td width="140" height="22">Ejecutar:  </td>
      		<td><input type="submit" name="accion" value="Generar"></td>
    		</tr>
  	</table>
</form>
</body>
</html>
