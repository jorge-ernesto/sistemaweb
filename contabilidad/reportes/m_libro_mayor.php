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

class LibroMayorModel {

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

		$Since_Account = trim($data['Since_Account']);
		$Since_Account = strip_tags($Since_Account);

		$To_Account = trim($data['To_Account']);
		$To_Account = strip_tags($To_Account);

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
					e.ch_sucursal,
					e.act_entrytype_id,
					e.subbookcode,
					e.registerno,
					TO_CHAR(e.documentdate, 'DD/MM/YYYY') AS documentdate,
					el.description AS description_detail,
					el.amtdt,
					el.amtct,
					a.act_account_id,
					a.acctcode,
					a.name					
				FROM
					act_entryline el
					LEFT JOIN act_entry   AS e ON (el.act_entry_id   = e.act_entry_id)
					LEFT JOIN act_account AS a ON (el.act_account_id = a.act_account_id)
				WHERE
					1 = 1
					AND TRIM(e.ch_sucursal) = '" . $Nu_Almacen . "'
					AND TO_CHAR(DATE(e.documentdate),'YYYY-MM') = '" . $Fe_Fecha . "'
					AND CAST(substr(a.acctcode,1,char_length(trim(to_char(".$Since_Account.",'99999999999999999999999999999')))  ) as numeric) >= ".$Since_Account."
					AND CAST(substr(a.acctcode,1,char_length(trim(to_char(".$To_Account.",'99999999999999999999999999999')))  ) as numeric) <= ".$To_Account."
				ORDER BY 
					e.ch_sucursal, a.acctcode, e.subbookcode, e.registerno
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
			$arrResult = $this->groupByAccountArrEntry($arrEntry);

			$response = array(
				'status' => 'success',
				'message' => 'Registros encontrados satisfactoriamente',
				'data' => $arrResult,
				'param' => $data,
				'data_company' => $data_company,
			);

			return $response;

		}catch(Exception $e){
			throw $e;
		}
	}

	/**
	* Funcion para agrupar asientos por codigo de cuentas contables
	*/
	function groupByAccountArrEntry($arrEntry) {
		$arrResult = array();
		foreach ($arrEntry as $key => $value) {
			$ch_sucursal        = $value['ch_sucursal'];
			$act_entrytype_id   = $value['act_entrytype_id'];
			$subbookcode        = $value['subbookcode'];
			$registerno         = $value['registerno'];
			$documentdate       = $value['documentdate'];
			$description_detail = $value['description_detail'];
			$amtdt              = $value['amtdt'];
			$amtct              = $value['amtct'];
			$act_account_id     = $value['act_account_id'];
			$acctcode           = $value['acctcode'];
			$name               = $value['name'];
		
			/**
			* Ordenamos por cuenta contable colocando: Los 2 primeros digitos de acctcode, seguido de acctcode, y act_account_id. Ejemplo: 10*101101*9001	
			*/
			$sub_acctcode = substr($value['acctcode'], 0, 2);
			$arrResult[$sub_acctcode."*".$acctcode."*".$act_account_id]['detalle'][] = array(
				"ch_sucursal"        => $ch_sucursal,
				"act_entrytype_id"   => $act_entrytype_id,
				"subbookcode"        => $subbookcode,
				"registerno"         => $registerno,
				"documentdate"       => $documentdate,
				"description_detail" => $description_detail,
				"amtdt"              => $amtdt,
				"amtct"              => $amtct,
				"act_account_id"     => $act_account_id,
				"acctcode"           => $acctcode,
				"name"               => $name,
			);
			$arrResult[$sub_acctcode."*".$acctcode."*".$act_account_id]['total']['total_amtdt'] += $amtdt;
			$arrResult[$sub_acctcode."*".$acctcode."*".$act_account_id]['total']['total_amtct'] += $amtct;
		}
		ksort($arrResult);
		return $arrResult;
	}	
}