<?php
class ProgramaAfericionModel extends Model {
	function obtenerLados() {
		global $sqlca;

		$lados = Array();

		$sql =	"	SELECT
					lado
				FROM
					pos_cmblados
				ORDER BY
					1;";

		if ($sqlca->query($sql)<0)			return $lados;		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$r = $sqlca->fetchRow();			$lados[$r[0]] = $r[0];
		}

		return $lados;
	}

	function obtenerModos() {
		return Array(
			"L" => "Lento",
			"R" => "Rapido"
		);
	}
	function programaAfericion($lado,$modo,$lineas) {
		global $sqlca;
echo "CHECK1_$lado";
		if (strlen($lado)!=2 || !is_numeric($lado))
			return FALSE;
echo "CHECK2";
		if (!is_numeric($lineas))
			return FALSE;

		$sql = "	UPDATE pos_cmblados SET
					carga='A'
					,tmpfpago=''
					,tmpntarjc=''
					,tmptbonus=''
					,tmpruc=''
					,tmptarjac=''
					,tmpmodoa='$modo'
					,tmpodomet=null
					,tmplineasa=$lineas
					,tmpttarjc=''
					,tmpproductom=null
					,tmpmontom=null
				WHERE
					lado='$lado';";
echo "QUERY";
		if ($sqlca->query($sql)<0)			return FALSE;

		return TRUE;	}
}
