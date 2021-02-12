<?php
require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
	
    $sql = "SELECT id, name, picoid, adddate, tipo FROM pic WHERE id=$id";
    $hasil = pg_exec($conector_id, $sql);
    $type = pg_result( $hasil, 0, "tipo");
    $data = pg_fetch_row($hasil, 0);
    if    (!$data)
    {
	echo "No hay Data";
    }
    else
    {
	Header ("Content-type: image/png ");
	pg_exec($conector_id, "BEGIN");
	$ofp = pg_loopen($data[2], "r");
	if  (!$ofp)	
		{
		echo "No se puede abrir el oid";	
		}
	$img = pg_loreadall($ofp);
	print $img;
	//echo $img;
	pg_loclose($ofp);
	pg_exec($conector_id, "END");
	}



// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();



