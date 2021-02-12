<?php

class DescuentosFideModel extends Model { 

	function llamadaRemota($procedimiento, $parametros) {
		global $sqlca;

		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='master_puntos';";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		$ip  = $row[0];
		$url = "http://".$ip."/sistemaweb/puntos/index.php?action=descuentos_fide&proc=".urlencode($procedimiento);

		foreach($parametros as $parametro=>$valor) {
			$url .= "&" . $parametro . "=" . urlencode($valor);
		}

		$fh = fopen($url,"rb");
		if ($fh===FALSE)
			return FALSE;

		$res = '';
		while (!feof($fh)) {
			$res .= fread($fh, 8192);
		}
		fclose($fh);
 
		return unserialize($res);
        }

	function obtenerDatos($ruc) {
		global $sqlca;
	    
		$params = array("ruc"	=> $ruc	);
	    
		return DescuentosFideModel::llamadaRemota("obtenerDescuentos", $params);

  	}

	function ingresarDescuento($ruc, $articulo, $descuento, $inicio, $fin) {
		global $sqlca;

		$params = array(
				"ruc"		=> $ruc,
				"cod_articulo"	=> $articulo,
				"descuento"	=> $descuento,
				"inicio"	=> $inicio,
				"fin"		=> $fin
				);
	    
	    	return DescuentosFideModel::llamadaRemota("ingresarDescuento", $params);
	} 

	function editarDescuento($id, $descuento, $fin) {
		global $sqlca;

		$params = array(
				"id"		=> $id,
				"descuento"	=> $descuento,
				"fin"		=> $fin
				);
	    
	    	return DescuentosFideModel::llamadaRemota("editarDescuento", $params);
	} 

	function eliminarCodigo($id) {
		global $sqlca;

		$params = array("id"		=> $id);
	    
	    	return DescuentosFideModel::llamadaRemota("eliminarDescuento", $params);
 	}

	function validaRUC($RUC) {
		$sum = 0;
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
			$sum = 11 - $sum;
			if ($sum >= 10)
				$sum -= 10;
			if ($sum == (ord(substr($RUC,-1)) - 48))
				return TRUE;
		}
		return FALSE;
	}	
}
