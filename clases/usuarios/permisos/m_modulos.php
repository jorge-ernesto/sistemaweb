<?php

class ModulosModel extends Model
{

    function obtenerAccesos()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_modulo,
		    gid,
		    uid
		FROM
		    int_usuarios_permisos
		ORDER BY
		    ch_modulo
		;
		";

	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$i]['ch_modulo'] = $a[0];
	    $result[$i]['uid'] = $a[2];
	    $result[$i]['gid'] = $a[1];
	}
	
	return $result;
    }
    
    function obtenerModulos()
    {
	global $sqlca;
	
	$sql = "SELECT
		    *
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
	    
	    $result[$a[0]] = $a[0] . "-" . $a[1];
	}
	
	return $result;
    }
    
    function agregarAcceso($opt, $gid, $uid, $ch_modulo)
    {
	global $sqlca;
	
	if($opt=="grupo") {
	    $sql = "INSERT INTO
			int_usuarios_permisos
		    VALUES (
			-1,
			'" . pg_escape_string($gid) . "',
			'" . pg_escape_string($ch_modulo) . "'
		    )
		    ;
		    ";
	}
	else {
	    $sql = "INSERT INTO
			int_usuarios_permisos
		    VALUES (
			'" . pg_escape_string($uid) . "',
			-1,
			'" . pg_escape_string($ch_modulo) . "'
		    )
		    ;
		    ";
	}

	if ($sqlca->query($sql) < 0) return false;
	
	return true;
    }
    
    function borrarAcceso($keys)
    {
	global $sqlca;
	
	foreach($keys as $i=>$key) {
	    $sql = "DELETE FROM
			int_usuarios_permisos
		    WHERE
			ch_modulo||uid||gid='" . pg_escape_string($key) . "'
		    ;
		    ";
	    echo $sql;
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

