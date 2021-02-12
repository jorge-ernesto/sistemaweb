<?php

class AfericionReportModel extends Model {
   	//Obtener la fecha del ultimo del cierre dia - tabla PA = pos_aprosys
	function getLastDatePA() {
		global $sqlca;
		$sqlca->query("SELECT TO_CHAR(da_fecha - integer '1', 'DD/MM/YYYY') AS fe_sistema FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;");
		$row = $sqlca->fetchRow();
		return $row['fe_sistema'];
	}

    function listarAfericiones($fe_inicial, $fe_final) {
		global $sqlca;

		$status = $sqlca->query("
		SELECT
			TO_CHAR(dia, 'DD/MM/YYYY') AS dia,
			turno,
			caja,
			trans,
			fecha,
			pump,
			AFE.codigo || ' - ' || PRO.ch_nombrebreve AS producto,
			cantidad,
			precio,
			importe,
			veloc,
			lineas,
			responsabl
		FROM
			pos_ta_afericiones AS AFE
			JOIN comb_ta_combustibles AS PRO ON (AFE.codigo = PRO.ch_codigocombustible)
		WHERE
			dia BETWEEN '" . $fe_inicial . "' AND '" . $fe_final . "'
		ORDER BY
			dia, turno, caja, pump, producto;
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
		$arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function listarAfericiones';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}

		return $arrResult;
	}
}
