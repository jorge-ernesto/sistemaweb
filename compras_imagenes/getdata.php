<?php

// getdata.php3 - by Florian Dittmer <dittmer@gmx.net>
// Example php script to demonstrate the direct passing of binary data
// to the user. More infos at http://www.phpbuilder.com
// Syntax: getdata.php3?id=<id>

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");



if($id) {

    // you may have to modify login information for your database server:

    $query = "select bin_data,filetype from binary_data where id=$id";
    $result = pg_QUERY($query);

    $data = pg_RESULT($result,0,"bin_data");
    $type = pg_RESULT($result,0,"filetype");

	echo strlen($data);
	echo $type;

    Header( "Content-type: $type");
    echo $data;

};


// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();

