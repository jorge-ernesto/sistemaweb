<?php
class DespachoLineaModel extends Model {

	function busqueda($tipoconsulta) {
		global $sqlca;

		$sql = "BEGIN";
		$sqlca->query($sql);

		$sql = " SELECT
						p.expoac AS estado,
						to_char(p.hora,'dd-mm-yyyy HH24:MI:SS') AS hora,
						p.tralad AS lado_surtidor,
						p.tragra AS num_mangueras,
						p.trapre AS precio_galon,
						p.tragal AS cant_galones,
						p.tratot AS total,
						p.ncaja AS num_caja,
						p.ntrans AS num_tiket
					FROM
						pos_nbastra p " .
						(($tipoconsulta=='0')?"where p.expoac = 'P'":'') .
					" ORDER BY p.hora asc";

		echo $sql;

		if($sqlca->query($sql) < 0) return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$resultado[$i] = $sqlca->fetchRow();
		}

		$sql = "COMMIT";
		$sqlca->query($sql);

		return $resultado;
	}
}
