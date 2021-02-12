<?php

class RankingPuntosAcumuladosModel extends Model {
		
	function llamadaRemota($procedimiento, $parametros){
		ini_set("max_execution_time", "2000");

		global $sqlca;
	
		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'master_puntos';";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		$ip = $row[0];
	
		$url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=rankingpuntosacumulados&proc=" . urlencode($procedimiento);
		
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
			
	function tmListado($fechaini,$fechafin,$sucursal,$pp, $pagina, $estado) {
		$params = array(
			"fechainicio"	=> $fechaini,
			"fechafin"	=> $fechafin,
			"sucursal"	=> $sucursal,
			"pp"		=> $pp,
			"pagina"	=> $pagina,
			"estado"	=> $estado
		);
	
		return RankingPuntosAcumuladosModel::llamadaRemota("tmListado", $params);
	}
			
	function listarDetalleMovimientos($iAlmacen, $dIni, $dFin, $iCuenta, $iTarjeta) {
		$params = array(
			"iAlmacen" => $iAlmacen,
			"dIni" => $dIni,
			"dFin" => $dFin,
			"iCuenta" => $iCuenta,
			"iTarjeta" => $iTarjeta
		);
	
		return RankingPuntosAcumuladosModel::llamadaRemota("listarDetalleMovimientos", $params);
	}

	function obtenerAlmacenes() {
        global $sqlca;

		$sql = "
		SELECT
			ch_almacen,
			ch_nombre_almacen
		FROM
			inv_ta_almacenes
		WHERE
			ch_clase_almacen='1'
		ORDER BY
			ch_almacen;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}
		return $result;
	}
}
