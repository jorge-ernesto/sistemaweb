<?php

class ExtornoModel extends Model {
	function obtenerLados() {
		global $sqlca;

		$sql =	"	SELECT
					lado
				FROM
					pos_cmblados
				ORDER BY
					1;";

		if ($sqlca->query($sql)<0)
			return $sqlca->get_error();

		$ret = Array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$ret[] = $a[0];
		}

		return $ret;
	}

	function obtenerUltimoTicket($lado) {
		global $sqlca;

		$sql =	"	SELECT
					t.td,
					t.fecha AS fecha,
					t.caja,
					t.pump,
					t.codigo,
					c.ch_nombrecombustible AS descripcion,
					t.importe,
					t.soles_km,
					t.trans,
					n.nregtr,
					t.ruc,
					t.fpago,
					t.at,
					t.text1,
					t.tarjeta,
					t.tanque,
					t.balance
				FROM
					pos_transtmp t
					LEFT JOIN comb_ta_combustibles c ON (t.codigo = c.ch_codigocombustible)
					LEFT JOIN pos_nbastra n ON (t.pump = n.tralad AND t.trans = n.ntrans AND t.caja = n.ncaja)
				WHERE
					t.tipo = 'C'
					AND pump = '{$lado}'
				ORDER BY
					fecha DESC
				LIMIT
					1;";

		if ($sqlca->query($sql)<0)
			return NULL;

		if ($sqlca->numrows() == 0)
			return NULL;

		if ($sqlca->numrows() > 1)
			return FALSE;

		$r = $sqlca->fetchRow();

		if ($r['soles_km'] != $r['importe'])
			return FALSE;

		return $r;
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

	function programaLado($datos,$sim=FALSE) {
		global $sqlca;

		$td = $datos['n_td'];

		$set = "carga='{$td}',tmptbonus='',";

		if ($td == "F") {
			$ruc = $datos['ruc'];
			if (ExtornoModel::validaRUC($ruc)===FALSE)
				return 6;
			$set .= "tmpruc='{$ruc}',";
		}

		if ($td == "B" || $td == "F") {
			$fpago = $datos['fpago'];
			$ttarj = substr(addslashes($datos['ttarj']),-1);
			$voucher = addslashes($datos['voucher']);
			if ($fpago == "1")
				$set .= "tmpfpago='1',tmpttarjc='0',tmpntarjc='0',";
			else if ($fpago == "2")
				$set .= "tmpfpago='2',tmpttarjc='{$ttarj}',tmpntarjc='{$voucher}',";
			else
				return -3;
		}

		if ($td == "N") {
			$fptshe = addslashes($datos['fptshe']);
			$sql =	"	SELECT
						1
					FROM
						pos_fptshe1 f
					WHERE
						numtar='{$fptshe}'
						AND estblo='N';";
			if ($sqlca->query($sql)<0)
				return -4;

			if ($sqlca->numrows() == 0)
				return 7;

			$odometro = $datos['odometro'];
			settype($odometro,"integer");

			$set .= "tmptarjac='{$fptshe}',tmpodomet={$odometro},";
		}

		if ($td == "A") {
			$modoa = addslashes($datos['veloc']);
			$lineasa = $datos['lineas'];
			settype($lineasa,"integer");
			if ($modoa != "R" && $modoa != "L")
				return -2;
			$set .= "tmpmodoa='{$modoa}',tmplineasa={$lineasa},";
		}

		$set = substr($set,0,-1);

		$sql =	"	UPDATE
					pos_cmblados
				SET
					$set
				WHERE
					lado = '" . $datos['pump'] . "';";

		if ($sim===FALSE)
			if ($sqlca->query($sql)<0)
				return -5;

		return 0;
	}

	function desprogramaLado($lado) {
		global $sqlca;

		$sql =	"	UPDATE
					pos_cmblados
				SET
					carga=null,
					tmpruc=null,
					tmpfpago='',
					tmpttarjc='',
					tmpntarjc=null,
					tmptarjac=null,
					tmpodomet=null,
					tmptbonus='',
					tmpdespachogal=null,
					tmpdespachoimp=null
				WHERE
					lado='{$lado}';";

		if ($sqlca->query($sql)<0)
			return -1;

		return 0;
	}

	function beginTransaction() {
		global $sqlca;

		$sql =	"BEGIN;";

		if ($sqlca->query($sql)<0)
			return -100;

		return 0;
	}

	function commitTransaction() {
		global $sqlca;

		$sql =	"COMMIT;";

		if ($sqlca->query($sql)<0)
			return -100;

		return 0;
	}

	function rollbackTransaction() {
		global $sqlca;

		$sql =	"ROLLBACK;";

		if ($sqlca->query($sql)<0)
			return -100;

		return 0;
	}

	function extornar($ticket,$datos) {
		global $sqlca;

		$antiguo = Array();
		$antiguo['pump'] = $ticket['pump'];
		$antiguo['n_td'] = $ticket['td'];
		$antiguo['ruc'] = $ticket['ruc'];
		$antiguo['fpago'] = $ticket['fpago'];
		$antiguo['ttarj'] = $ticket['at'];
		$antiguo['voucher'] = $ticket['text1'];
		$antiguo['fptshe'] = $ticket['tarjeta'];
		$antiguo['veloc'] = $ticket['tanque'];
		$antiguo['lineas'] = $ticket['balance'];
		$datos['pump'] = $ticket['pump'];

		$r = ExtornoModel::programaLado($antiguo,TRUE);
		if ($r != 0)
			return $r;

		$r = ExtornoModel::programaLado($datos,TRUE);
		if ($r != 0)
			return $r;


		$r = ExtornoModel::beginTransaction();
		if ($r != 0)
			return $r;

		$r = ExtornoModel::desprogramaLado($datos['o_pump']);
		if ($r != 0) {
			ExtornoModel::rollbackTransaction();
			return $r;
		}

		$r = ExtornoModel::programaLado($antiguo);
		if ($r != 0) {
			ExtornoModel::rollbackTransaction();
			return $r;
		}

		$sql =	"	SELECT
					tralad,
					tragra,
					trapre,
					tragal,
					tratot
				FROM
					pos_nbastra
				WHERE
					nregtr = {$ticket['nregtr']};";

		if ($sqlca->query($sql)<0) {
			ExtornoModel::rollbackTransaction();
			return -6;
		}

		if ($sqlca->numrows() == 0) {
			ExtornoModel::rollbackTransaction();
			return 4;
		}

		$despacho = $sqlca->fetchRow();

		$sql = "SELECT nextval('pos_nbastra_seq');";
		if ($sqlca->query($sql)<0) {
			ExtornoModel::rollbackTransaction();
			return -7;
		}

		if ($sqlca->numrows() == 0) {
			ExtornoModel::rollbackTransaction();
			return 4;
		}

		$idr = $sqlca->fetchRow();
		$id = $idr[0];

		$sql =	"	INSERT INTO
					pos_nbastra
				(
					expoac,
					hora,
					tralad,
					tragra,
					trapre,
					tragal,
					tratot,
					traiv,
					codpos,
					nregtr,
					ncaja,
					fecha_creacion,
					ntrans
				) VALUES (
					'P',
					now(),
					'{$despacho['tralad']}',
					'{$despacho['tragra']}',
					{$despacho['trapre']},
					{$despacho['tragal']}*-1,
					{$despacho['tratot']}*-1,
					null,
					null,
					{$id},
					null,
					now(),
					null
				);";

		if ($sqlca->query($sql)<0) {
			ExtornoModel::rollbackTransaction();
			return -8;
		}

		ExtornoModel::commitTransaction();

		sleep(5);

		$sql =	"	SELECT
					expoac
				FROM
					pos_nbastra
				WHERE
					nregtr = {$id};";

		if ($sqlca->query($sql) < 0 || $sqlca->numrows() == 0)
			return -9;

		$r = $sqlca->fetchRow();

		$sql =	"	DELETE FROM
					pos_nbastra
				WHERE
					nregtr = {$id};";

		$sqlca->query($sql);

		ExtornoModel::desprogramaLado($datos['o_pump']);

		if ($r[0] != "E")
			return 5;

		$r = ExtornoModel::beginTransaction();
		if ($r != 0)
			return $r;

		$r = ExtornoModel::programaLado($datos);
		if ($r != 0) {
			ExtornoModel::rollbackTransaction();
			return $r;
		}

		$sql =	"	UPDATE
					pos_nbastra
				SET
					expoac = 'P'
				WHERE
					nregtr = {$ticket['nregtr']};";echo $sql;

		if ($sqlca->query($sql) < 0) {
			ExtornoModel::rollbackTransaction();
			return -10;
		}

		ExtornoModel::commitTransaction();
	}

	function obtenerTiposDoc() {
		return Array(
			"B"	=>	"Boleta",
			"F"	=>	"Factura",
			"N"	=>	"Nota de Despacho",
			"A"	=>	"Afericion"
		);
	}

	function obtenerFormasPago() {
		return Array(
			"1"	=>	"Efectivo",
			"2"	=>	"Tarjeta de Cr&eacute;dito"
		);
	}

	function obtenerTiposTarjeta() {
		global $sqlca;

		$sql =	"	SELECT
					substr(tab_elemento,6,1),
					tab_descripcion
				FROM
					int_tabla_general
				WHERE
					tab_tabla='95'
					AND tab_elemento!='000000'
				ORDER BY
					1;";

		if ($sqlca->query($sql)<0)
			return $sqlca->get_error();

		$ret = Array();
		for ($i=1;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$ret[$a[0]] = $a[1];
		}

		return $ret;
	}
}
