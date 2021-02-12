<?php

class GroupMasterModel extends Model
{
    function obtenerGrupos($desde, $hasta)
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
			AND gid > 0
		ORDER BY
		    gid
		";
	
	if ($desde > 0) $sql .= "OFFSET " . pg_escape_string($desde) . " ";
	if ($hasta > 0) $sql .= "LIMIT " . pg_escape_string($hasta) . " ";

	$sql .= "
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $result[$a[0]]['ch_grupo'] = $a[1];
	    $result[$a[0]]['ch_nombre'] = $a[2];
	}

	return $result;
    }
    
    function obtieneGrupo($gid)
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_grupo,
		    ch_nombre
		FROM
		    int_usuarios_grupos
		WHERE
			gid='" . pg_escape_string($gid) . "'
		    AND ch_activo='S'
		;
		";
	if ($sqlca->query($sql) < 0) return;
	
	$a = $sqlca->fetchRow();
	
	return $a;
    }
    
    function updateGrupo($gid, $ch_grupo, $ch_nombre)
    {
	global $sqlca;
	
	$sql = "UPDATE
		    int_usuarios_grupos
		SET
		    ch_grupo='" . pg_escape_string($ch_grupo) . "',
		    ch_nombre='" . pg_escape_string($ch_nombre) . "'
		WHERE
		    gid='" . pg_escape_string($gid) . "'
		;
		";
	echo $sql;
	if ($sqlca->query($sql) < 0) return false;
	
	return true;
    }
    
    function agregar($ch_grupo, $ch_nombre)
    {
	global $sqlca;
	
	$sql = "INSERT INTO
		    int_usuarios_grupos
			(
			    gid,
			    ch_grupo,
			    ch_nombre,
			    ch_activo
			)
		VALUES
		    (
			nextval('seq_int_usuarios_gid'),
			'" . pg_escape_string($ch_grupo) . "',
			'" . pg_escape_string($ch_nombre) . "',
			'S'
		    )
		;
		";
	echo $sql;
	if ($sqlca->query($sql) < 0) return 3;
	
	return 0;	
    }
    
    function borrarGrupos($gids)
    {
	global $sqlca;
	
	foreach($gids as $gid) {
	    $sql = "UPDATE
		        int_usuarios_grupos
		    SET
			ch_activo='N'
		    WHERE
			gid='" . pg_escape_string($gid) . "'
		    ;
		    ";
	    if ($sqlca->query($sql) < 0) return false;
    
	    /* Revocar los permisos del usuario */

	    $sql = "DELETE FROM
			int_usuarios_grupos_pertenencia
		    WHERE
			gid='" . pg_escape_string($gid) . "'
		    ;
		    ";
	    if ($sqlca->query($sql) < 0) return false;

	    $sql = "DELETE FROM
			int_usuarios_permisos
		    WHERE
			gid='" . pg_escape_string($gid) . "'
		    ;
		    ";
	    if ($sqlca->query($sql) < 0) return false;
	
	    $sql = "DELETE FROM
			int_usuarios_almacenes
		    WHERE
			gid='" . pg_escape_string($gid) . "'
		    ;
		    ";
	    if ($sqlca->query($sql) < 0) return false;	
	}
	return true;
    }    
    
}

?>
