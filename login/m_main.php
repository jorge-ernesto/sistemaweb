<?php

class MainModel extends Model
{
    function obtenerAlmacenes()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_almacen,
		    ch_nombre_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		ORDER BY
		    ch_almacen;";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $result[$a[0]] = $a[1];
	}
	
	return $result;
    }
    
function obtenerAlmacenesPorUsuario($uid){
	global $sqlca;
	$sql = "SELECT ch_almacen FROM int_usuarios_almacenes WHERE uid='" . pg_escape_string($uid) . "'";
	    if (count($gid) > 0) {
		$sql .= " OR gid in (";
		for ($i = 0; $i < count($gid); $i++) {
		    if ($i > 0) $sql .= ",";
		    $sql .= "'" . pg_escape_string($gid[$i]) . "'";
		}
		$sql .= ")";
	    }
	    $sql .= ";";

	    if ($sqlca->query($sql, "auth_main") < 0) return;
	    if ($sqlca->numrows("auth_main") > 0) {
			for ($i = 0; $i < $sqlca->numrows("auth_main"); $i++) {
			    $a = $sqlca->fetchRow("auth_main");
			    $almacenes[$i] = $a[0];
			}
	    }
	return $almacenes;
}
    
}

