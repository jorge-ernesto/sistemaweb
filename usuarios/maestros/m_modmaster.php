<?php

class ModMasterModel extends Model
{
    function obtenerModulos()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_modulo,
		    ch_descripcion
		FROM
		    int_modulos
		ORDER BY
		    ch_modulo
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[1];
	}
	
	return $result;
    }
    
    function agregarModulo($ch_modulo, $ch_descripcion)
    {
	global $sqlca;
	
	$sql = "INSERT INTO
		    int_modulos
		VALUES (
		    '" . pg_escape_string($ch_modulo) . "',
		    '" . pg_escape_string($ch_descripcion) . "'
		)
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	return true;
    }

    function borrarModulos($ch_modulos)
    {
	global $sqlca;
	
	foreach($ch_modulos as $i=>$ch_modulo) {
	    $sql = "DELETE FROM
			int_modulos
		    WHERE
			ch_modulo='" . pg_escape_string($ch_modulo) . "'
		    ;
		    ";
	    $sqlca->query($sql);
	}
    }   
    
    function obtenerModulo($ch_modulo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_descripcion
		FROM
		    int_modulos
		WHERE
		    ch_modulo='" . pg_escape_string($ch_modulo) . "'
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$a = $sqlca->fetchRow();
	return $a[0];
    }
    
    function modificarModulo($ch_modulo, $ch_descripcion)
    {
	global $sqlca;
	
	$sql = "UPDATE
		    int_modulos
		SET
		    ch_descripcion='" . pg_escape_string($ch_descripcion) . "'
		WHERE
		    ch_modulo='" . pg_escape_string($ch_modulo) . "'
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	return true;
    }
}

