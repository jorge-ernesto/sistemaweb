<?php
class RetencionesModel extends Model{
	function llamadaRemota($procedimiento, $parametros){
		global $sqlca;

		$sql = "select par_valor from int_parametros where par_nombre='master_puntos';";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		$ip = $row[0];

		$url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=retenciones&proc=" . urlencode($procedimiento);

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
	
	function tmListado($fechaini,$fechafin,$pp, $pagina){
		$params = array(
			"fechainicio"	=> $fechaini,
			"fechafin"	=> $fechafin,
			"pp"		=> $pp,
			"pagina"	=> $pagina
		);
			return RetencionesModel::llamadaRemota("tmListado", $params);
	}

	function liberaRetencion($id) {
		$params = Array("id" => $id);
		return RetencionesModel::llamadaRemota("liberaRetencion",$params);
	}
}
