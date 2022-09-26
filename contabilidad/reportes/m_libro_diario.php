<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}

class LibroDiarioModel {

	function BEGINTransacction() {
       	global $sqlca;

		try {
			$sql = "BEGIN;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR BEGIN");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function COMMITTransacction() {
		global $sqlca;

        try {
			$sql = "COMMIT;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR COMMIT TRANSACION");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function ROLLBACKTransacction() {
		global $sqlca;

		try {
			$sql = "ROLLBACK;";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("ERROR ROLLBACK");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

    function ObtenerEstaciones() {
		global $sqlca;
	
		try {
			$sql = "
				SELECT
					ch_almacen as almacen,
					trim(ch_nombre_almacen) as nombre
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen='1'
				ORDER BY
					ch_almacen;
			";

			if($sqlca->query($sql) <= 0)
				throw new Exception("Error no se encontro turnos en la fecha indicada");

			while($reg = $sqlca->fetchRow())
				$registro[] = $reg;

			return $registro;
		}catch(Exception $e){
			throw $e;
		}
    }

	function getListAll($data, $jqGridModel, $objHelper) {
		// echo "<script>console.log('data')</script>";
		// echo "<script>console.log('" . json_encode($data) . "')</script>";

		global $sqlca;

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$Fe_Periodo = trim($data['Fe_Periodo']);
		$Fe_Periodo = strip_tags($Fe_Periodo);

		$Fe_Mes = trim($data['Fe_Mes']);
		$Fe_Mes = strip_tags($Fe_Mes);

		$Nu_Cantreg = trim($data['Nu_Cantreg']);
		$Nu_Cantreg = strip_tags($Nu_Cantreg);

		$Fe_Fecha = $Fe_Periodo . "-" . $Fe_Mes;

		if(!empty($Nu_Almacen))
			$cond_almacen = "AND TRIM(e.ch_sucursal) = '" . $Nu_Almacen . "'";

		$sql_total = "
			SELECT
				COUNT(el.*) total
			FROM
				act_entryline el
				LEFT JOIN act_entry e ON (el.act_entry_id = e.act_entry_id)
			WHERE
				1 = 1
				AND TRIM(e.ch_sucursal) = '" . $Nu_Almacen . "'
				AND TO_CHAR(DATE(e.documentdate),'YYYY-MM') = '" . $Fe_Fecha . "'
		";
		// echo "<pre>sql_total";
		// print_r($sql_total);
		// echo "</pre>";

		$sqlca->query($sql_total);

		$cantidad_registros = $sqlca->fetchRow();
		$paginador = $jqGridModel->Config($cantidad_registros["total"], $Nu_Cantreg, 3);	
		// echo "<script>console.log('paginador')</script>";
		// echo "<script>console.log('" . json_encode($paginador) . "')</script>";

		try {
			$sql = "
				SELECT 	
					el.act_entryline_id, 
					el.act_entry_id, 
					el.act_account_id,
					el.amtdt, 
					el.amtct, 
					el.amtsourcedt, 
					el.amtsourcect, 
					el.description AS description_detail, 
					el.tableid, 
					el.regid, 
					el.int_clientes_id, 
					el.c_cash_mpayment_id,
					el.tab_currency,
					--------
					a.acctcode, 
					a.name,
					--------
					e.ch_sucursal, 
					e.dateacct, 
					e.description AS description_head, 
					e.act_entrytype_id, 
					e.subbookcode, 
					e.registerno, 
					TO_CHAR(e.documentdate, 'DD/MM/YYYY') AS documentdate, --YYYY-MM-DD HH12:MM:SS
					e.documentdate AS documentdate_datetime,
					--e.tableid,            --Existe en detalle con información mas precisa
					--e.regid,              --Existe en detalle con información mas precisa
					--e.int_clientes_id,    --Existe en detalle con información mas precisa
					--e.c_cash_mpayment_id, --Existe en detalle con información mas precisa
					--e.tab_currency,       --Existe en detalle con información mas precisa
					-------
					et.bookcode,
					et.bookcode_order,
					-------
					mone.tab_car_04 AS isocode,
					-------
					r.ruc AS ruc,
					r.razsocial AS razsocial,
					cli.cli_codigo AS cli_codigo,
					cli.cli_razsocial AS cli_razsocial
				FROM
					act_entryline el
					LEFT JOIN act_entry         AS e    ON (el.act_entry_id      = e.act_entry_id)
					LEFT JOIN act_entrytype     AS et   ON (e.act_entrytype_id   = et.act_entrytype_id)
					LEFT JOIN act_account       AS a    ON (el.act_account_id    = a.act_account_id)
					LEFT JOIN int_tabla_general AS mone ON (el.tab_currency      = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla = '04' AND mone.tab_elemento != '000000')
					LEFT JOIN ruc               AS r    ON (TRIM(r.ruc)          = TRIM(el.int_clientes_id::VARCHAR) AND el.tableid IN (1,3))
					LEFT JOIN int_clientes      AS cli  ON (TRIM(cli.cli_codigo) = TRIM(el.int_clientes_id::VARCHAR) AND el.tableid IN (2))
				WHERE
					1 = 1 
					AND TRIM(e.ch_sucursal) = '" . $Nu_Almacen . "'
					AND TO_CHAR(DATE(e.documentdate),'YYYY-MM') = '" . $Fe_Fecha . "'
				ORDER BY
					e.ch_sucursal ASC, et.bookcode_order ASC, e.subbookcode ASC, e.registerno ASC, el.act_entryline_id ASC
				LIMIT
					" . $paginador["limit"] . "
				OFFSET
					" . $paginador["start"];
			// echo "<pre>sql";
			// print_r($sql);
			// echo "</pre>";
			
			if ($sqlca->query($sql) < 0){
				return $response = array(
					'status' => 'danger',
					'message' => 'Problemas al buscar registros'
				);
			}

			if ($sqlca->query($sql) == 0){
				return $response = array(
					'status' => 'warning',
					'message' => 'No hay registros'
				);
			}

			$arrEntry = $sqlca->fetchAll();
			$arrEntry = $objHelper->get_data_arrayEntry($arrEntry);

			$jqGridModel->DataSource($arrEntry);

			$response = array(
				'status' => 'success',
				'message' => 'Registros encontrados satisfactoriamente',
				'data' => $jqGridModel,
				'param' => $data,
			);

			return $response;

		}catch(Exception $e){
			throw $e;
		}
	}

	function getList($data, $objHelper) {
		// echo "<script>console.log('data')</script>";
    	// echo "<script>console.log('" . json_encode($data) . "')</script>";

		global $sqlca;

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$Fe_Periodo = trim($data['Fe_Periodo']);
		$Fe_Periodo = strip_tags($Fe_Periodo);

		$Fe_Mes = trim($data['Fe_Mes']);
		$Fe_Mes = strip_tags($Fe_Mes);

		$Fe_Fecha = $Fe_Periodo . "-" . $Fe_Mes;

		if(!empty($Nu_Almacen))
			$cond_almacen = "AND VC.ch_sucursal = '" . $Nu_Almacen . "'";

		$sql_company = "
			SELECT
				EMPRE.ruc,
				EMPRE.razsocial,
				EMPRE.ch_direccion,
				EMPRE.ebiurl,
				EMPRE.ebiauth,
				ALMA.ch_nombre_almacen AS no_almacen,
				ALMA.ch_direccion_almacen
			FROM
				inv_ta_almacenes AS ALMA
				JOIN int_ta_sucursales AS EMPRE
					USING ( ch_sucursal )
			WHERE
				EMPRE.ebikey IS NOT NULL AND EMPRE.ebikey != ''
				AND ALMA.ch_clase_almacen = '1'
				AND ALMA.ch_almacen = '" . $Nu_Almacen . "'
			LIMIT 1
		";
		// echo "<pre>sql_company";
		// print_r($sql_company);
		// echo "</pre>";

		$sqlca->query($sql_company);
		$data_company = $sqlca->fetchRow();

		try {
			$sql = "
				SELECT 	
					el.act_entryline_id, 
					el.act_entry_id, 
					el.act_account_id,
					el.amtdt, 
					el.amtct, 
					el.amtsourcedt, 
					el.amtsourcect, 
					el.description AS description_detail, 
					el.tableid, 
					el.regid, 
					el.int_clientes_id, 
					el.c_cash_mpayment_id,
					el.tab_currency,
					--------
					a.acctcode, 
					a.name,
					--------
					e.ch_sucursal, 
					e.dateacct, 
					e.description AS description_head, 
					e.act_entrytype_id, 
					e.subbookcode, 
					e.registerno, 
					TO_CHAR(e.documentdate, 'DD/MM/YYYY') AS documentdate, --YYYY-MM-DD HH12:MM:SS
					e.documentdate AS documentdate_datetime,
					--e.tableid,            --Existe en detalle con información mas precisa
					--e.regid,              --Existe en detalle con información mas precisa
					--e.int_clientes_id,    --Existe en detalle con información mas precisa
					--e.c_cash_mpayment_id, --Existe en detalle con información mas precisa
					--e.tab_currency,       --Existe en detalle con información mas precisa
					-------
					et.bookcode,
					et.bookcode_order,
					-------
					mone.tab_car_04 AS isocode,
					-------
					r.ruc AS ruc,
					r.razsocial AS razsocial,
					cli.cli_codigo AS cli_codigo,
					cli.cli_razsocial AS cli_razsocial
				FROM
					act_entryline el
					LEFT JOIN act_entry         AS e    ON (el.act_entry_id      = e.act_entry_id)
					LEFT JOIN act_entrytype     AS et   ON (e.act_entrytype_id   = et.act_entrytype_id)
					LEFT JOIN act_account       AS a    ON (el.act_account_id    = a.act_account_id)
					LEFT JOIN int_tabla_general AS mone ON (el.tab_currency      = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla = '04' AND mone.tab_elemento != '000000')
					LEFT JOIN ruc               AS r    ON (TRIM(r.ruc)          = TRIM(el.int_clientes_id::VARCHAR) AND el.tableid IN (1,3))
					LEFT JOIN int_clientes      AS cli  ON (TRIM(cli.cli_codigo) = TRIM(el.int_clientes_id::VARCHAR) AND el.tableid IN (2))
				WHERE
					1 = 1
					AND TRIM(e.ch_sucursal) = '" . $Nu_Almacen . "'
					AND TO_CHAR(DATE(e.documentdate),'YYYY-MM') = '" . $Fe_Fecha . "'
				ORDER BY
					e.ch_sucursal ASC, et.bookcode_order ASC, e.subbookcode ASC, e.registerno ASC, el.act_entryline_id ASC
			";
			// echo "<pre>sql";
			// print_r($sql);
			// echo "</pre>";
			
			if ($sqlca->query($sql) < 0){
				return $response = array(
					'status' => 'danger',
					'message' => 'Problemas al buscar registros'
				);
			}

			if ($sqlca->query($sql) == 0){
				return $response = array(
					'status' => 'warning',
					'message' => 'No hay registros'
				);
			}

			$arrEntry = $sqlca->fetchAll();
			$arrEntry = $objHelper->get_data_arrayEntry($arrEntry);

			$response = array(
				'status' => 'success',
				'message' => 'Registros encontrados satisfactoriamente',
				'data' => $arrEntry,
				'param' => $data,
				'data_company' => $data_company,
			);

			return $response;

		}catch(Exception $e){
			throw $e;
		}
	}
}