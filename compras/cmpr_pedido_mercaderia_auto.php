<?php
include("../valida_sess.php");
include("inc_top_compras.php");
include("../functions.php");
//include("store_procedures.php");
require("../clases/funciones.php");

$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";
	}



?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Pedido de Mercaderia Automatica</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript">
function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
	if(consulta!="" && des_campo!=""){
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
	}else{
	alert("Falta definir un campo");
	}
}
</script>
<script>
	function alerta(){
		alert('Carajo');
	}
</script>
</head>

<body>
Pedido de Mercaderia Automatico<br>
<form name="form1" method="post" action="">
  Almacen 
  <?php $rs_combo_almacenes = combo("almacenes"); ?>
  <select name="combo_almacenes">
<?php for($i=0;$i<pg_numrows($rs_combo_almacenes);$i++){
	$CMB = pg_fetch_array($rs_combo_almacenes, $i);
	print "<option value='$CMB[0]'>$CMB[1]</option>";
}
?>
</select>
<br>
  <table width="704" border="0">
    <tr> 
      <td width="138"><div align="right">Fecha</div></td>
      <td colspan="3"><input type="text" name="fecha"  size="11" value="<?php echo $hoy;?>" readonly="true"></td>
      <td width="196">&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right"></div></td>
      <td colspan="3">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="3">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td height="21">&nbsp;</td>
      <td width="118"><input type="radio" name="condicion" value="ubicaciones" onClick="javascript:condi.value='ubicaciones<?php echo trim($almacen);?>';" >
        Por Ubicacion</td>
      <td width="102"><input type="radio" name="condicion" value="lineas" onClick="javascript:condi.value='lineas';">
        Por Linea</td>
      <td width="128"><input type="radio" name="condicion" value="proveedores" onClick="javascript:condi.value='proveedores';">
        Por Proveedor</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td height="22">&nbsp;</td>
      <td><input type="radio" name="radiobutton" value="radiobutton" checked onMouseOver="this.style.cursor='hand'" onClick="javascript:filtro.value='menos';">
        Menos</td>
      <td><input type="radio" name="radiobutton" value="radiobutton" onMouseOver="this.style.cursor='hand'" onClick="javascript:filtro.value='mas';">
        Mas</td>
      <td><input type="radio" name="radiobutton" value="radiobutton" onMouseOver="this.style.cursor='hand'" onClick="javascript:filtro.value='todos';">
        Todos</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td height="33">&nbsp;</td>
      <td>&nbsp;</td>
      <td><div align="center"> 
          <input type="button" name="Button" value="Generar Reporte" onMouseOver="this.style.cursor='hand'" onClick="javascript: mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','cod_proveedor','despro',condi.value,filtro.value);">
        </div></td>
      <td><input type="hidden" name="condi">
        <input type="hidden" name="filtro" value="menos"></td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
<?php pg_close();?>