<?php

class DescuentosFideModel { 

	function obtenerDatos($ruc) {
		global $sqlca;
	    
		$query =	"	SELECT
						df.id_descuento,
						df.ruc,
						df.art_codigo,
						art.art_descripcion,
						df.descuento,
						df.inicio_validez::date,
						df.fin_validez::date
					FROM
						prom_ta_descuentos df
						LEFT JOIN int_articulos art ON (df.art_codigo = art.art_codigo)
					WHERE
						1=1 ";
		if($ruc != '')
			$query .= "	AND df.ruc = '".pg_escape_string($ruc)."' ";
		
		$query .= "		ORDER BY df.ruc,
						 df.inicio_validez; ";

		if ($sqlca->query($query) <= 0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['id_descuento']	= $a[0];
			$resultado[$i]['ruc']	 	= $a[1];
			$resultado[$i]['cod_articulo'] 	= $a[2];
			$resultado[$i]['nom_articulo'] 	= $a[3];
			$resultado[$i]['importe']	= $a[4];
			$resultado[$i]['inicio'] 	= substr($a[5],8,2)."/".substr($a[5],5,2)."/".substr($a[5],0,4);
			$resultado[$i]['fin']	 	= substr($a[6],8,2)."/".substr($a[6],5,2)."/".substr($a[6],0,4);
		}

		return $resultado;
  	}

	function ingresarDescuento($ruc, $articulo, $descuento, $inicio, $fin) {
		global $sqlca;

		$valruc = DescuentosFideModel::validaRUC($ruc);
		
		if ($valruc) {
			$sql = "INSERT INTO prom_ta_descuentos 
					       (
						ruc,
						art_codigo,
						descuento,
						inicio_validez,
						fin_validez) 
					VALUES (
						".trim($ruc).", 
						'".trim($articulo)."',
						".trim($descuento).", 
						to_date('".trim($inicio)."', 'DD/MM/YYYY'),
						to_date('".trim($fin)."', 'DD/MM/YYYY')
					       )";
			$sqlca->query($sql);
			return 1;
		} else {
			return 2;
		}
	} 

	function editarDescuento($id, $descuento, $fin) {
		global $sqlca;

		$sql = "UPDATE 
				prom_ta_descuentos
			SET	
				descuento = ".trim($descuento).",
				fin_validez = to_date('".trim($fin)."', 'DD/MM/YYYY')
			WHERE
				id_descuento = ".trim($id)." ";
		$sqlca->query($sql);

		return 1;
	} 

	function eliminarCodigo($id) {
		global $sqlca;

		$query = "DELETE FROM prom_ta_descuentos WHERE id_descuento=".$id." ;";
		$sqlca->query($query);

		return 1;
 	}

	function validaRUC($RUC) {
		$sum   = 0;
		$digit = 0;
		$RUC = trim($RUC);

		if (!is_numeric($RUC))
			return FALSE;

		if (strlen($RUC) == 8) {
			for ($i = 0; $i < 7; $i++) {
				$digit = ord(substr($RUC,$i,1)) - 48;
				if (i == 0)
					$sum += $digit * 2;
				else
					$sum += $digit * (strlen($RUC) - i);
			}

			$sum %= 11;
			if ($sum == 1)
				$sum = 11;
			if ($sum + (ord(substr($RUC,-1)) - 48) == 11)
				return TRUE;

		} else if (strlen($RUC) == 11) {
			$x = 6;
			for ($i = 0; $i < 10; $i++) {
				if ($i == 4)
					$x = 8;
				$digit = ord(substr($RUC,$i,1)) - 48;
				$x--;
				if ($i == 0)
					$sum += $digit * $x;
				else
					$sum += $digit * $x;
			}

			$sum %= 11;
			$sum  = 11 - $sum;
			if ($sum >= 10)
				$sum -= 10;
			if ($sum == (ord(substr($RUC,-1)) - 48))
				return TRUE;
		}
		return FALSE;
	}	
}
