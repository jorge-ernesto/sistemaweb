<?php

//session_start();

include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
require("/sistemaweb/clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id = $funcion->conectar("","","","","");

$sql = "SELECT count(*) FROM pos_consolidacion WHERE dia = '".$_REQUEST['fecha']."';";

if ($sqlca->query($sql) < 0) 
	return false;

$a = $sqlca->fetchRow();

if($a[0]>=1){
	echo "<blink style='color: red'>¡ Dia consolidado, ingresar otra fecha !</blink>";
}else{
	echo $_REQUEST['fecha'];
}

?>

<form>
<table>
<tr>
<td>
<input type="text" size="15" maxlength="15" />
</td>
</tr>


