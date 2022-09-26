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

class AsientoInicialModel {

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

	function getAsientoInicial($data, $objHelper) {
		// echo "<script>console.log('data')</script>";
		// echo "<script>console.log('" . json_encode($data) . "')</script>";

		global $sqlca;

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$SubDiario = trim($data['SubDiario']);
		$SubDiario = strip_tags($SubDiario);

		$Descripcion_Subdiario = trim($data['Descripcion_Subdiario']);
		$Descripcion_Subdiario = strip_tags($Descripcion_Subdiario);

		$Fe_Anio_Pasado = trim($data['Fe_Anio_Pasado']);
		$Fe_Anio_Pasado = strip_tags($Fe_Anio_Pasado);

		$Fe_Anio_Apertura = trim($data['Fe_Anio_Apertura']);
		$Fe_Anio_Apertura = strip_tags($Fe_Anio_Apertura);

		//OBTENEMOS PARAMETROS EN ARRAY
		$arrParams['Nu_Almacen']            = $Nu_Almacen;
		$arrParams['SubDiario']             = $SubDiario;
		$arrParams['Descripcion_Subdiario'] = $Descripcion_Subdiario;
		$arrParams['Fe_Anio_Pasado']        = $Fe_Anio_Pasado;
		$arrParams['Fe_Anio_Apertura']      = $Fe_Anio_Apertura;
		$arrParams['tca_fecha']             = $Fe_Anio_Pasado . "-12-31";

		try {
			//GENERAMOS ASIENTOS DE REVERSION 
			$response = $this->generarAsientoInicial($arrParams);			
				
			//RETORNAMOS ARRAY DE RESPUESTA
			return $response;

		}catch(Exception $e){
			throw $e;
		}
	}	

	function saveAsientoInicial($response, $objHelper) {
		$this->executeInsert($response['entry']);		
	}

	function deleteAsientoInicial($data, $objHelper) {
		// echo "<script>console.log('data')</script>";
		// echo "<script>console.log('" . json_encode($data) . "')</script>";

		global $sqlca;

		$Nu_Almacen = trim($data['Nu_Almacen']);
		$Nu_Almacen = strip_tags($Nu_Almacen);

		$SubDiario = trim($data['SubDiario']);
		$SubDiario = strip_tags($SubDiario);

		$Descripcion_Subdiario = trim($data['Descripcion_Subdiario']);
		$Descripcion_Subdiario = strip_tags($Descripcion_Subdiario);

		$Fe_Anio_Pasado = trim($data['Fe_Anio_Pasado']);
		$Fe_Anio_Pasado = strip_tags($Fe_Anio_Pasado);

		$Fe_Anio_Apertura = trim($data['Fe_Anio_Apertura']);
		$Fe_Anio_Apertura = strip_tags($Fe_Anio_Apertura);

		//OBTENEMOS PARAMETROS EN ARRAY
		$arrParams['Nu_Almacen']            = $Nu_Almacen;
		$arrParams['SubDiario']             = $SubDiario;
		$arrParams['Descripcion_Subdiario'] = $Descripcion_Subdiario;
		$arrParams['Fe_Anio_Pasado']        = $Fe_Anio_Pasado;
		$arrParams['Fe_Anio_Apertura']      = $Fe_Anio_Apertura;
		$arrParams['tca_fecha']             = $Fe_Anio_Pasado . "-12-31";
		
		//ELIMINAMOS ASIENTOS DE REVERSION
		$this->eliminarAsientoInicial($arrParams);
	}

	//FUNCIONES ADICIONALES
	function generarAsientoInicial($arrParams) {
		global $sqlca;

		$Nu_Almacen            = $arrParams['Nu_Almacen'];
		$SubDiario             = $arrParams['SubDiario'];
		$Descripcion_Subdiario = $arrParams['Descripcion_Subdiario'];
		$Fe_Anio_Pasado        = $arrParams['Fe_Anio_Pasado'];
		$Fe_Anio_Apertura      = $arrParams['Fe_Anio_Apertura'];
		$tca_fecha             = $arrParams['tca_fecha'];

		//OBTENEMOS TIPO DE CAMBIO
		$sql_tipo_cambio = "
			SELECT 
				tc.tca_compra_oficial,
				tc.tca_venta_oficial
			FROM 
				int_tipo_cambio AS tc
			WHERE
				tc.tca_fecha = '$tca_fecha' 
				AND tc.tca_moneda = '02'
			LIMIT 1
		";

		// echo "<pre>sql_tipo_cambio";
		// print_r($sql_tipo_cambio);
		// echo "</pre>";

		if ($sqlca->query($sql_tipo_cambio) < 0){
			return $response = array(
				'status' => 'danger',
				'message' => 'Problemas al buscar TC'
			);
		}

		if ($sqlca->query($sql_tipo_cambio) == 0){
			return $response = array(
				'status' => 'warning',
				'message' => 'No hay registros TC'
			);
		}

		$arrTipoCambio = $sqlca->fetchRow();

		// echo "<pre>tipoCambio";
		// print_r($arrTipoCambio);
		// echo "</pre>";

		//OBTENEMOS DATA PARA EL ASIENTO DE REVERSION (SOLO CUENTAS DEL 1 AL 5)
		$sql_data_asiento = "
			SELECT
				b.act_balance_id,
				b.ch_sucursal,
				b.dateacct,
				b.act_account_id,
				a.acctcode,
				a.name,
				b.amtdt,
				b.amtct,
				b.tab_currency
			FROM
				act_balance b
				LEFT JOIN act_account AS a  ON (b.act_account_id = a.act_account_id)
			WHERE 
				1 = 1
				AND TO_CHAR(DATE(b.dateacct),'YYYY') = '" . $Fe_Anio_Pasado . "'
				AND CAST(substr(a.acctcode,1,char_length(trim(to_char(1,'99999999999999999999999999999')))  ) as numeric) >= 1
				AND CAST(substr(a.acctcode,1,char_length(trim(to_char(1,'99999999999999999999999999999')))  ) as numeric) <= 5
			ORDER BY
				a.acctcode;
		";

		// echo "<pre>sql_data_asiento";
		// print_r($sql_data_asiento);
		// echo "</pre>";
			
		if ($sqlca->query($sql_data_asiento) < 0){
			return $response = array(
				'status' => 'danger',
				'message' => 'Problemas al buscar registros'
			);
		}

		if ($sqlca->query($sql_data_asiento) == 0){
			return $response = array(
				'status' => 'warning',
				'message' => 'No hay registros'
			);
		}

		$arrData = $sqlca->fetchAll();
		$arrEntry = $this->formatoAsientoInicial($arrData, $arrTipoCambio, $arrParams);

		$response = array(
			'status' => 'success',
			'message' => 'Registros encontrados satisfactoriamente',
			// 'data' => $arrData,
			'entry' => $arrEntry,
			'param' => $arrParams,
			'data_tc' => $arrTipoCambio,
		);

		return $response;
	}

	function formatoAsientoInicial($arrData, $arrTipoCambio, $arrParams) {
		//GENERAMOS ASIENTOS DE REVERSION
		$act_entryline = array();
		
		foreach ($arrData as $key => $value) {
			//OBTENEMOS VARIABLES
			$tab_currency       = $value['tab_currency'];			
			$tca_compra_oficial = $arrTipoCambio['tca_compra_oficial'];
			$tca_venta_oficial  = $arrTipoCambio['tca_venta_oficial'];

			//OBTENEMOS CANTIDADES EN SOLES
			if ($tab_currency == '01'){ //SOLES
				$debe  = $value['amtdt'];
				$haber = $value['amtct'];	
			} else { //DOLARES				
				$debe  = $value['amtdt'] * $tca_venta_oficial;
				$haber = $value['amtct'] * $tca_venta_oficial;
			}

			//PROCESO DE EXTORNAR CANTIDADES
			if ($debe > $haber) {
				$debe = $debe - $haber;
				$haber = 0;
			}

			if ($haber > $debe) {
				$haber = $haber - $debe;
				$debe = 0;
			}

			//ASIENTOS DE REVERSION, INVIERTE CANTIDADES
			$amtdt = $debe; //amtdt normalmente es el debe, pero le ponemos haber, invertimos
			$amtct = $haber;  //amtct normalmente es el haber, pero le ponemos debe, invertimos
			
			//OBTENEMOS CANTIDADES EN DOLARES PARA TODAS LAS CUENTAS CONVERTIDAS CON EL TIPO DE CAMBIO
			$amtsourcedt = $amtdt / $tca_venta_oficial;
			$amtsourcect = $amtct / $tca_venta_oficial;

			//OBTENEMOS DATOS PARA LOS ASIENTOS
			$act_entrytype_id = "12";
			$tableid          = NULL;
			$regid            = "-";
			$tab_currency     = "01";
			$description      = "ASIENTO INICIAL";

			//INFORMACION PARA DETALLE (ACT_ENTRYLINE)
			$act_entryline[] = array(
				"act_entry_id"   => NULL,
				"act_account_id" => array("act_account_id" => $value['act_account_id']),
				"amtdt"          => $amtdt,
				"amtct"          => $amtct,
				"amtsourcedt"    => $amtsourcedt,
				"amtsourcect"    => $amtsourcect,
				"description"        => $description,
				"tableid"            => $tableid,
				"regid"              => $regid,
				"int_clientes_id"    => NULL,
				"c_cash_mpayment_id" => NULL,
				"tab_currency"       => $tab_currency,
				//DATOS ADICIONALES QUE NO NECESITA EL ASIENTO PERO SI LA PREVISUALIZACION HTML
				"acctcode"      => $value['acctcode'],
				"name"          => $value['name']
			);
		}

		//INFORMACION PARA CABECERA (ACT_ENTRY)
		$data_asientos[] = array(
			"ch_sucursal"        => $arrParams['Nu_Almacen'],
			"dateacct"           => $arrParams['Fe_Anio_Apertura']."-01-01",
			"description"        => $description,
			"act_entrytype_id"   => $act_entrytype_id,
			"subbookcode"        => array("acctcode" => $arrParams['SubDiario']),
			"registerno"         => "1",
			"documentdate"       => $arrParams['Fe_Anio_Apertura']."-01-01",
			"tableid"            => $tableid,
			"regid"              => $regid,
			"int_clientes_id"    => NULL,
			"c_cash_mpayment_id" => NULL,
			"tab_currency"       => $tab_currency,
			"act_entryline"      => $act_entryline,
		);
		
		return $data_asientos;
	}

	function eliminarAsientoInicial($arrParams) {
		global $sqlca;

		$Nu_Almacen            = $arrParams['Nu_Almacen'];
		$SubDiario             = $arrParams['SubDiario'];
		$Descripcion_Subdiario = $arrParams['Descripcion_Subdiario'];
		$Fe_Anio_Pasado        = $arrParams['Fe_Anio_Pasado'];
		$Fe_Anio_Apertura      = $arrParams['Fe_Anio_Apertura'];
		$tca_fecha             = $arrParams['tca_fecha'];

		//ELIMINAMOS DETALLE DE ASIENTOS
		$sql_act_entryline = "
			DELETE FROM act_entryline WHERE act_entry_id IN (SELECT 
																act_entry_id 
															FROM 
																act_entry 
															WHERE 
																TRIM(ch_sucursal) = '$Nu_Almacen'
																AND DATE(documentdate) = '$Fe_Anio_Apertura-01-01'
																AND act_entrytype_id IN ('12'));
		";
		$iStatus = $sqlca->query($sql_act_entryline);
		echo "<pre>";
		echo $sql_act_entryline;
		echo "</pre>";
		
		//ELIMINAMOS CABECERA DE ASIENTOS
		if ((int)$iStatus >= 0) {
			$sql_act_entry = "
				DELETE FROM 
					act_entry 
				WHERE
					TRIM(ch_sucursal) = '$Nu_Almacen'
					AND DATE(documentdate) = '$Fe_Anio_Apertura-01-01'
					AND act_entrytype_id IN ('12');
			";
			$sqlca->query($sql_act_entry);	
			echo "<pre>";
			echo $sql_act_entry;
			echo "</pre>";
		}
	}

	function executeInsert($dataAsientos) {
		global $sqlca;

		//EJECUTAMOS INSERT CABECERA (act_entry)
		foreach ($dataAsientos as $key => $value) {
			$ch_sucursal        = $value['ch_sucursal'];
			$dateacct           = $value['dateacct'];        
			$description        = $value['description'];     
			$act_entrytype_id   = $value['act_entrytype_id'];
			$subbookcode        = $value['subbookcode']['acctcode'];
			$registerno         = $value['registerno'];   
			$documentdate       = $value['documentdate'];
			$tableid            = $value['tableid'];         
			$regid              = $value['regid'];            
			$int_clientes_id    = $value['int_clientes_id'];  
			$c_cash_mpayment_id = $value['c_cash_mpayment_id'];
			$tab_currency       = $value['tab_currency'];     

			//Columns de insert
			$ch_sucursal_column        = isset($ch_sucursal)        ? ",ch_sucursal"        : NULL; 
			$dateacct_column           = isset($dateacct)           ? ",dateacct"           : NULL; 
			$description_column        = isset($description)        ? ",description"        : NULL; 
			$act_entrytype_id_column   = isset($act_entrytype_id)   ? ",act_entrytype_id"   : NULL; 
			$subbookcode_column    	   = isset($subbookcode)     	? ",subbookcode"        : NULL;
			$registerno_column    	   = isset($registerno)     	? ",registerno"         : NULL;
			$documentdate_column       = isset($documentdate)       ? ",documentdate"       : NULL; 
			$tableid_column            = isset($tableid)            ? ",tableid"            : NULL; 
			$regid_column              = isset($regid)              ? ",regid"              : NULL; 
			$int_clientes_id_column    = isset($int_clientes_id)    ? ",int_clientes_id"    : NULL; 
			$c_cash_mpayment_id_column = isset($c_cash_mpayment_id) ? ",c_cash_mpayment_id" : NULL; 
			$tab_currency_column       = isset($tab_currency)       ? ",tab_currency"       : NULL; 

			//Values de insert
			$ch_sucursal_value        = isset($ch_sucursal)        ? ",'$ch_sucursal'"        : NULL; 
			$dateacct_value           = isset($dateacct)           ? ",'$dateacct'"           : NULL; 
			$description_value        = isset($description)        ? ",'$description'"        : NULL; 
			$act_entrytype_id_value   = isset($act_entrytype_id)   ? ",'$act_entrytype_id'"   : NULL; 
			$subbookcode_value    	  = isset($subbookcode)        ? ",'$subbookcode'"        : NULL;
			$registerno_value         = isset($registerno)     	   ? ",'$registerno'"     	  : NULL; 
			$documentdate_value       = isset($documentdate)       ? ",'$documentdate'"       : NULL; 
			$tableid_value            = isset($tableid)            ? ",'$tableid'"            : NULL; 
			$regid_value              = isset($regid)              ? ",'$regid'"              : NULL; 
			$int_clientes_id_value    = isset($int_clientes_id)    ? ",'$int_clientes_id'"    : NULL; 
			$c_cash_mpayment_id_value = isset($c_cash_mpayment_id) ? ",'$c_cash_mpayment_id'" : NULL; 
			$tab_currency_value       = isset($tab_currency)       ? ",'$tab_currency'"       : NULL; 

			$insert_act_entry = "
				INSERT INTO public.act_entry (
					act_entry_id
					$ch_sucursal_column 
					$dateacct_column 
					$description_column 
					$act_entrytype_id_column 
					$subbookcode_column 
					$registerno_column 
					$documentdate_column 
					$tableid_column 
					$regid_column 
					$int_clientes_id_column 
					$c_cash_mpayment_id_column 
					$tab_currency_column
				) VALUES (
					nextval('seq_act_entry_id')
					$ch_sucursal_value 
					$dateacct_value 
					$description_value 
					$act_entrytype_id_value 
					$subbookcode_value
					$registerno_value 
					$documentdate_value
					$tableid_value 
					$regid_value 
					$int_clientes_id_value 
					$c_cash_mpayment_id_value 
					$tab_currency_value
				) RETURNING act_entry_id AS act_entry_id
			";
			$iStatus = $sqlca->query($insert_act_entry);
			// echo "<pre>";
			// echo $insert_act_entry;
			// echo "</pre>";

			if ((int)$iStatus < 0) {
				return array('error' => TRUE, 'message' => 'Error en insert act_entry');
			}

			$row = $sqlca->fetchRow();
			$act_entry_id = $row["act_entry_id"];

			//EJECUTAMOS INSERT DETALLE (act_entryline)
			$dataAsientosDetalle = $value['act_entryline'];
			foreach ($dataAsientosDetalle as $key => $value) {
				$act_entry_id       = $act_entry_id;
				$act_account_id     = $value['act_account_id']['act_account_id'];        
				$amtdt              = $value['amtdt'];     
				$amtct              = $value['amtct'];
				$amtsourcedt        = $value['amtsourcedt'];   
				$amtsourcect        = $value['amtsourcect'];         
				$description        = $value['description'];
				$tableid            = $value['tableid'];
				$regid              = $value['regid'];
				$int_clientes_id    = $value['int_clientes_id'];
				$c_cash_mpayment_id = $value['c_cash_mpayment_id'];
				$tab_currency       = $value['tab_currency'];

				//Columns de insert
				$act_entry_id_column       = isset($act_entry_id)       ? ",act_entry_id"       : NULL; 
				$act_account_id_column     = isset($act_account_id)     ? ",act_account_id"     : NULL; 
				$amtdt_column              = isset($amtdt)              ? ",amtdt"              : NULL; 
				$amtct_column              = isset($amtct)              ? ",amtct"              : NULL; 
				$amtsourcedt_column        = isset($amtsourcedt)        ? ",amtsourcedt"        : NULL; 
				$amtsourcect_column        = isset($amtsourcect)        ? ",amtsourcect"        : NULL;
				$description_column        = isset($description)        ? ",description"        : NULL;
				$tableid_column            = isset($tableid)            ? ",tableid"            : NULL;
				$regid_column              = isset($regid)              ? ",regid"              : NULL;
				$int_clientes_id_column    = isset($int_clientes_id)    ? ",int_clientes_id"    : NULL;
				$c_cash_mpayment_id_column = isset($c_cash_mpayment_id) ? ",c_cash_mpayment_id" : NULL;
				$tab_currency_column       = isset($tab_currency)       ? ",tab_currency"       : NULL;

				//Values de insert
				$act_entry_id_value    = isset($act_entry_id)       ? ",'$act_entry_id'"       : NULL; 
				$act_account_id_value  = isset($act_account_id)     ? ",'$act_account_id'"     : NULL; 
				$amtdt_value           = isset($amtdt)              ? ",'$amtdt'"              : NULL; 
				$amtct_value           = isset($amtct)              ? ",'$amtct'"              : NULL; 
				$amtsourcedt_value     = isset($amtsourcedt)        ? ",'$amtsourcedt'"        : NULL; 
				$amtsourcect_value     = isset($amtsourcect)        ? ",'$amtsourcect'"        : NULL;
				$description_value     = isset($description)        ? ",'$description'"        : NULL;
				$tableid_value         = isset($tableid)            ? ",'$tableid'"            : NULL;
				$regid_value           = isset($regid)              ? ",'$regid'"              : NULL;
				$int_clientes_id_value = isset($int_clientes_id)    ? ",'$int_clientes_id'"    : NULL;
				$c_cash_mpayment_value = isset($c_cash_mpayment_id) ? ",'$c_cash_mpayment_id'" : NULL;
				$tab_currency_value    = isset($tab_currency)       ? ",'$tab_currency'"       : NULL; 

				$insert_act_entryline = "
					INSERT INTO public.act_entryline (
						act_entryline_id
						$act_entry_id_column 
						$act_account_id_column 
						$amtdt_column 
						$amtct_column 
						$amtsourcedt_column 
						$amtsourcect_column 	
						$description_column	
						$tableid_column
						$regid_column	
						$int_clientes_id_column	
						$c_cash_mpayment_id_column
						$tab_currency_column
					) VALUES (
						nextval('seq_act_entryline_id')
						$act_entry_id_value 
						$act_account_id_value 
						$amtdt_value 
						$amtct_value 
						$amtsourcedt_value 
						$amtsourcect_value 	
						$description_value
						$tableid_value	
						$regid_value
						$int_clientes_id_value
						$c_cash_mpayment_value		
						$tab_currency_value		
					);
				";
				$iStatus = $sqlca->query($insert_act_entryline);
				// echo "<pre>";
				// echo $insert_act_entryline;
				// echo "</pre>";

				if ((int)$iStatus < 0) {
					return array('error' => TRUE, 'message' => 'Error en insert act_entryline');
				}
			}
		}

		return array(
			'error' => FALSE
		);
	}
}