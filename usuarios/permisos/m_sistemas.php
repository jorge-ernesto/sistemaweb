<?php

class SistemasModel extends Model
{
    function obtenerSistemasPorUsuario()
    {
	global $sqlca;
	
	$sql = "SELECT
		    *
		FROM
		    int_usuarios_sistemas
		GROUP BY
		    gid,
		    uid,
		    ch_sistema
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$i]['uid'] = $a[0];
	    $result[$i]['gid'] = $a[1];
	    $result[$i]['ch_sistema'] = $a[2];
	}
	
	return $result;
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
    
    function obtenerSistemas()
    {
	global $sqlca;
	
	$sql = "SELECT
		    tab_elemento,
		    tab_descripcion
		FROM
		    int_tabla_general
		WHERE
			tab_tabla='SIST'
		    AND tab_elemento!='000000'
		ORDER BY
		    tab_elemento
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
    
    function agregarAcceso($opt, $gid, $uid, $sis)
    {
	global $sqlca;
	
	if ($opt == "grupo") {
	    $sql = "INSERT INTO
			int_usuarios_sistemas
		    VALUES (
			-1,
			'" . pg_escape_string($gid) . "',
			'" . pg_escape_string($sis) . "'
		    )
		    ;
		    ";
	}
	else {
	    $sql = "INSERT INTO
			int_usuarios_sistemas
		    VALUES (
			'" . pg_escape_string($uid) . "',
			-1,
			'" . pg_escape_string($sis) . "'
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
	
	foreach($keys as $i => $key) {
	    $sql = "DELETE FROM
			int_usuarios_sistemas
		    WHERE
			gid||uid||ch_sistema='" . pg_escape_string($key) . "'
		    ;
		    ";
	    $sqlca->query($sql);
	}
    }
}

