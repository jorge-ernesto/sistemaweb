<?php

class CodigosRapidoModel extends Model { 

	function obtenerDatos($identificador, $articulo) {
		global $sqlca;
	    
		$query =	"	SELECT
						P.quickcode,
						P.art_codigo,
						art.art_descripcion
					FROM
						pos_quickcode P
						LEFT JOIN int_articulos art ON (art.art_codigo = P.art_codigo)
					WHERE
						1=1 ";
		if($identificador != '')
			$query .= "	AND P.quickcode = '".pg_escape_string($identificador)."' ";

		if($articulo != '')
			$query .= "	AND P.art_codigo = '".pg_escape_string($articulo)."' ";
		
		$query .= "		ORDER BY P.quickcode ";

		if ($sqlca->query($query) <= 0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['identificador']	= $a[0];
			$resultado[$i]['articulo'] 	= $a[1];
			$resultado[$i]['nom_articulo'] 	= $a[2];
		}

		return $resultado;
  	}

	function ingresarCodigo($identificador, $articulo) {
		global $sqlca;

		if ($identificador == "" or $articulo == "") {
			return 0;
		}

		$query = " SELECT 1 FROM pos_quickcode WHERE quickcode = '".pg_escape_string($identificador)."' ";
		$sqlca->query($query);

		if ($sqlca->numrows() == 1)
			return 2;

		if ($identificador == "" or $articulo == "") {
			return 0;
		} else {
			$sql = "	INSERT INTO pos_quickcode (
									quickcode,
									art_codigo) 
							VALUES	(
									".trim($identificador).", 
									'".trim($articulo)."') ;";
			$sqlca->query($sql);

			return 1;
		}
	} 

	function eliminarCodigo($identificador){
		global $sqlca;

		$query = "DELETE FROM pos_quickcode WHERE quickcode=".$identificador." ;";
		$sqlca->query($query);

		return 1;
 	}
}
