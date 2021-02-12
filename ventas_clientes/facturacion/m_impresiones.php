<?php

class ImpresionesModel extends Model
{

    function obtenerDocumentos()
    {
	global $sqlca;
	
	$sql = "SELECT
		    tab_elemento,
		    tab_descripcion
		FROM
		    int_tabla_general
		WHERE
			tab_tabla='08'
		    AND tab_elemento!='000000'
		ORDER BY
		    tab_elemento
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[0] . " - " . $a[1];
	}
	
	return $result;
    }
}

?>