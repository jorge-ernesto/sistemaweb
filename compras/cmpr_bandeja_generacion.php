<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

//require("../clases/funciones.php");
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
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<style type="text/css">
<!--
.Estilo1 {
	font-family: Geneva, Arial, Helvetica, sans-serif;
	font-weight: bold;
}
.Estilo3 {font-size: 18px}
-->
</style>
<head>
<title>Documento sin titulo</title>
</head>
<body>
<form>
<p align="center" class="Estilo1 Estilo3">Bandeja de Generacion de O/C  </p>
<div align="center">
    <table width="232" border="1">
      <tr>
        <tD width="104" >Fecha Proceso: </tD>
        <tD width="112"><div align="center">22/03/2004</div></tD>
      </tr>
    
</table>
</div>
<p align="center">
  <input type="submit" name="Submit" value="Genrear Pendientes">
</p>
<table width="960" border="1">
  <tr>
    <th width="120" scope="col">Codigo Articulo </th>
    <th width="287" scope="col">Nombre</th>
    <th width="116" scope="col">Cantidad</th>
    <th width="241" scope="col">Proveedor</th>
    <th width="162" scope="col">Opciones</th>
  </tr>
  <tr>
    <td>1234567890123</td>
    <td>ARTICULO PRUEBA </td>
    <td><div align="center">100.00</div></td>
    <td>00D155 - DUALNET 
      <input name="imgprov" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" ></td>
    <td>      <input type="checkbox" name="checkbox2" value="checkbox">
    Mail
      <input type="checkbox" name="checkbox" value="checkbox">
      Impreso</td>
  </tr>
  <tr>
    <td>2345678901234</td>
    <td>ARTICULO PRUEBA 2 </td>
    <td><div align="center">200.00</div></td>
    <td>00S016 - SHELL PERU 
      <input name="imgprov2" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" ></td>
    <td><input type="checkbox" name="checkbox22" value="checkbox">
Mail
  <input type="checkbox" name="checkbox3" value="checkbox">
Impreso</td>
  </tr>
  <tr>
    <td>3456789012345</td>
    <td>ARTICULO PRUEBA 3 </td>
    <td><div align="center">50.00</div></td>
    <td>00X001 - PROVEEDOR X 
      <input name="imgprov3" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" ></td>
    <td><input type="checkbox" name="checkbox222" value="checkbox">
Mail
  <input type="checkbox" name="checkbox32" value="checkbox">
Impreso</td>
  </tr>
</table>
<table width="400" border="0" align="center">
  <tr>
    <th width="194" scope="col"><input type="submit" name="Submit2" value="Generar Ordenes Compra"></th>
    <th width="190" scope="col"><input type="submit" name="Submit3" value="Regresar"></th>
  </tr>
</table>
<p align="center">&nbsp;</p>
</form>
</body>
</html>
