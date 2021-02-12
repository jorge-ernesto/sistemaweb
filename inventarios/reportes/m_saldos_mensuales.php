<?php

class ModelSaldosMensuales extends Model {

	function GetAlmacen($nualmacen) {
		global $sqlca;

		$cond = '';
		if ($nualmacen != 'T') {
			$cond = "AND ch_almacen = '".$nualmacen."'";
		}

		try {
			$sql = "
			SELECT
				ch_almacen AS nualmacen,
				TRIM (ch_almacen) || ' - ' || TRIM (ch_nombre_almacen) AS noalmacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1' $cond
			ORDER BY
				ch_almacen;
			";

			if ($sqlca->query($sql) <= 0) {
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}
	}

	function GetLinea() {
		global $sqlca;

		try {
		$sql = "
		SELECT
			tl.tab_elemento AS nucodlinea,
			tl.tab_elemento || ' - ' || tl.tab_descripcion AS nolinea
		FROM
			int_tabla_general as tl
		WHERE
			tl.tab_tabla = '20'
			AND (tl.tab_elemento != '000000' AND tl.tab_elemento != '')
		ORDER BY
			tl.tab_descripcion;
		";

		if ($sqlca->query($sql) <= 0) {
			$registro[] = array("nucodlinea"=>"","nolinea"=>"No hay Lineas");
		}

		while ($reg = $sqlca->fetchRow()) {
			$registro[] = $reg;
		}

		return $registro;

		} catch (Exception $e) {
			throw $e;
		}

	}

	function search($data) {
		global $sqlca;
		$registro = array();

		$nualmacen 	= $data['nualmacen'];
		$nucodlinea = $data['nucodlinea'];
		$nuyear 	= $data['nuyear'];
		$numonth 	= $data['numonth'];
		$codart 	= $data['cod_art'];
		$descart 	= $data['desc_art'];

		$anod = $nuyear;
		$mesd = $numonth;
		$linea = '';
		$sqladd = '';

		if(!isset($mesd))
			$mesd = date("m");

		if(!isset($anod))
			$anod = date("Y");

		$txtdesc = strtoupper($descart);

		if(strlen(trim($codart)) > 0 && strlen(trim($descart)) > 0)
			$vwhere1 = " AND s.art_codigo = '" . $codart . "'";

		if($nucodlinea != 'T')
			$vwhere3 = " AND a.art_linea = '" . $nucodlinea. "'";

		if ($nualmacen != 'T')
			$vwhere4 = " AND s.stk_almacen='".$nualmacen."'";

		$vwhere = $vwhere1 . $vwhere2 . $vwhere3;


		$sql = "
		SELECT
			s.art_codigo,
			a.art_descripcion,
			s.stk_stock" . $mesd . ",
			s.stk_fisico" . $mesd . ",
			s.stk_costo" . $mesd . ",
			s.stk_stockinicial,
			s.stk_costoinicial,
			s.stk_almacen || ' ' || ALMA.ch_nombre_almacen AS stk_almacen,
			a.art_plutipo
		FROM
			inv_saldoalma AS s
			JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = s.stk_almacen)
			JOIN int_articulos AS a USING(art_codigo)
		WHERE
			stk_periodo = '" . $anod . "'
			" . $vwhere . "
		ORDER BY
			2
		";

		if ($sqlca->query($sql) <= 0) {
			throw new Exception("No se encontro ningun registro");
		}
		while ($reg = $sqlca->fetchRow()) {
			$registro[] = $reg;
		}
		return $registro;
	}
}