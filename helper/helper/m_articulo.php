<?php

class ArticuloModel extends Model
{
    function search($hint, $criterio)
    {
	global $sqlca;
	
	$sql = "SELECT
		    art_codigo,
		    art_descripcion
		FROM
		    int_articulos
		";
	
	
	if ($hint != "") {
	    if ($criterio == "CODIGO")
		$sql .= "WHERE art_codigo like '%" . pg_escape_string($hint) . "%' ";
	    else
		$sql .= "WHERE art_descripcion like '%" . pg_escape_string($hint) . "%' ";
	}

	if ($criterio == "CODIGO")	
	    $sql .= "ORDER BY
			art_codigo
		    ;";
	else
	    $sql .= "ORDER BY
			art_descripcion
		    ;";
	
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = trim($a[0]) . " - " . trim($a[1]);
	}
	
	return $result;
    }
}

?>