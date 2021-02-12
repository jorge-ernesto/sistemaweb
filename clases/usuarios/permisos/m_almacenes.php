<?php

class AlmacenesModel extends Model {

	function obtenerListado() {
		global $sqlca;
	
		$sql = "SELECT * FROM int_usuarios_almacenes GROUP BY gid, uid, ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();		    
		    $result[$i]['uid'] = $a['uid'];
		    $result[$i]['gid'] = $a['gid'];
		    $result[$i]['ch_almacen'] = $a['ch_almacen'];
		}
	
		return $result;
    	}
    
    	function obtenerAlmacenes() {
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

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();		    
		    $result[$a[0]] = $a[1];
		}
	
		return $result;
    	}
    
	function agregarAcceso($opt, $gid, $uid, $almacen) {
		global $sqlca;
	
		if ($opt == "grupo") {
		    	$sql = "INSERT INTO
					int_usuarios_almacenes
			    	VALUES (
					'" . pg_escape_string($almacen) . "',
					-1,
					'" . pg_escape_string($gid) . "'
			    		);";
		} else {
		    	$sql = "INSERT INTO
					int_usuarios_almacenes
			    	VALUES (
					'" . pg_escape_string($almacen) . "',
					'" . pg_escape_string($uid) . "',
					-1
			    	);";
		}
		$sqlca->query($sql);
    	}
    
	function borrarAcceso($keys) {
		global $sqlca;

		foreach($keys as $i => $key) {
			$sql = "DELETE FROM
					int_usuarios_almacenes
		    		WHERE
					trim(to_char(gid, 'FM99')||to_char(uid, 'FM99')||ch_almacen)='".pg_escape_string($key)."';";

			$sqlca->query($sql);
		}
    	}

    function obtenerGrupos()
    {
	global $sqlca;
	
	$sql = "SELECT
		    gid,
		    ch_grupo,
		    ch_nombre
		FROM
		    int_usuarios_grupos
		WHERE
		    ch_activo='S'
		ORDER BY
		    gid
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[1] . " - " . $a[2];
	}
	
	return $result;
    }
    
    function obtenerUsuarios()
    {
	global $sqlca;
	
	$sql = "SELECT
		    uid,
		    ch_login,
		    ch_nombre
		FROM
		    int_usuarios_passwd
		WHERE
		    ch_activo='S'
		ORDER BY
		    uid
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[1] . " - " . $a[2];
	}
	
	return $result;
    }

}

