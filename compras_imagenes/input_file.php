<?php

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
	
// check file upload

	if ($testfile)
    	{
		if (is_uploaded_file ($testfile))
			{
			chmod ($testfile, 0777);
			//query for upload file in to database
			$sql = "INSERT INTO pic (id, name, picoid) VALUES";
			$sql .= "($v_id, '$testfile_name', lo_import('$testfile'))";
			$hasil = pg_exec($conector_id, $sql);
			if (!$hasil)
				{
				echo "Archivo no fue UPLOAD<br><br><br>";
				exit;
				}
			else
				{
				echo "<h1>Archivo <b>$testfile_name</b> fue UPLOAD</h1><br> ";
				}
			}
		else
			{
			echo "No hay archivo a UPLOAD";
			}
		}
	else{
		echo "no ahi archivo $testfile";
		}


// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();



