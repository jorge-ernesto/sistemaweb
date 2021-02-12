<?php

//get the q parameter from URL
//$fecha = $_GET['fecha'];
$search = $_POST['service'];

include("../valida_sess.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

$sql = "SELECT pro_razsocial FROM int_proveedores WHERE pro_razsocial LIKE '" . $search . "%' ORDER BY pro_razsocial;";

if ($sqlca->query($sql) < 0) 
	return false;	

while ($row_services = mysql_fetch_array($sql)) {
    echo '<div class="suggest-element"><a data="'.$row_services['pro_razsocial'].'">'.utf8_encode($row_services['pro_razsocial']).'</a></div>';
}

