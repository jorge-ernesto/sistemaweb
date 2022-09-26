<?php /*Copy /sistemaweb/helper.php*/
include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");

ini_set('memory_limit', '-1');
date_default_timezone_set('America/Lima');

class HelperClass {
    function __construct(){
    }

    /**
	* Funcion para validar existencia de tablas por information_schema de Postgres
	*/
	public function validateTableBySchema($table) {
		global $sqlca;
		$iStatusTable = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$table."'");
		return $iStatusTable;
	}

    /**
	* Funcion que obtiene tablas pos_trans del mes anterior y mes posterior
	*/
	public function getPosTransAnteriorDespues($anio, $mes){
		/* Obtenemos fecha postrans del mes anterior y mes posterior */
		$anio_ant = $anio;
		$anio_des = $anio;
		$mes_ant  = $mes-1;
		$mes_des  = $mes+1;
		$mes_ant  = strlen($mes_ant) == 1 ? "0".$mes_ant : $mes_ant;
		$mes_des  = strlen($mes_des) == 1 ? "0".$mes_des : $mes_des;
		if($mes == "01"){
			$mes_ant  = "12";
			$anio_ant = $anio-1;
		}
		if($mes == "12"){
			$mes_des  = "01";
			$anio_des = $anio+1;
		}
		$fecha_postrans_ant = $anio_ant . "" . $mes_ant;
		$fecha_postrans_des = $anio_des . "" . $mes_des;
		// echo "<script>console.log('" . json_encode( array($fecha_postrans_ant, $fecha_postrans_des) ) . "')</script>";
		
		/* Validamos que tablas pos_trans del mes anterior y posterior existan */
		$status_table_postrans_ant = $this->validateTableBySchema("pos_trans".$fecha_postrans_ant);
		$status_table_postrans_des = $this->validateTableBySchema("pos_trans".$fecha_postrans_des);
		// echo "<script>console.log('" . json_encode( array($status_table_postrans_ant, $status_table_postrans_des) ) . "')</script>";

		$response = array(
			"mes_ant"  => $mes_ant,
			"anio_ant" => $anio_ant,
			"mes_des"  => $mes_des,
			"anio_des" => $anio_des,
			"status_table_postrans_ant" => $status_table_postrans_ant,
			"status_table_postrans_des" => $status_table_postrans_des,
		);

		echo "<script>console.log('getPosTransAnteriorDespues')</script>";			
		echo "<script>console.log('" . json_encode($response, JSON_FORCE_OBJECT) . "')</script>";

		return $response;
	}

    /**
	* Funcion para obtener Cuentas Contables por ID
	* ID: Campo "act_config_id" de la tabla "act_config"
	*/
	public function getCuentaContable($data) {
		global $sqlca;

		/* Obtenemos act_config_id y value de la tabla act_config */
		$porciones      = explode("*", $data);
		$act_config_id_ = $porciones[0];

		/* Obtenemos cuentas contables */
		$sql = "
		SELECT 
			cnf.act_config_id,
			cnf.act_account_id,
			TRIM(acc.acctcode) AS acctcode,
			TRIM(cnf.value) AS value
		FROM 
			act_config cnf 
			LEFT JOIN act_account acc ON (cnf.act_account_id = acc.act_account_id) 
		WHERE 
			cnf.act_config_id = '$act_config_id_';
		";

		// echo "<pre>getCuentaContable";
		// echo $sql;
		// echo "</pre>";

		// if ($data['value']['id_trans'] == "959521") {
		// 	echo "<pre>getCuentaContable";
		// 	echo $sql;
		// 	echo "</pre>";
		// }

		if ($sqlca->query($sql) < 0) {
			return false;
		}

		$a = $sqlca->fetchRow();
		$act_config_id  = $a[0];
		$act_account_id = $a[1];
		$acctcode       = $a[2];
		$value          = $a[3];

		$data_cuenta['acctcode']       = $acctcode;
		$data_cuenta['value']          = $value;
		$data_cuenta['act_config_id']  = $act_config_id;
		$data_cuenta['act_account_id'] = $act_account_id;

		return $data_cuenta;
	}

    /**
	* Funcion para obtener Cuentas Contables para Combustible (por Codigo) y Market (por Linea)
	* Codigo: Campo "art_codigo" de la tabla "act_config"
	* Linea: Campo "art_linea" de la tabla "act_config"
	*/
	public function getCuentaContablePersonalizada($data) {
		global $sqlca;

		/* Si se busca Cuenta Contable Market (por Linea) y se envia el parametro art_linea = "ALL" */		
		if ($data["tipo"] == "M" && $data["art_linea"] == "ALL") {
			return $this->getCuentaContablePersonalizada_MarketALL($data);
		}		

		/* Obtenemos where para Combustible */
		if ($data["tipo"] == "C") {
			if ($data["art_codigo"] != NULL) {
				$where = "AND TRIM(cnf.art_codigo) = '".TRIM($data["art_codigo"])."'";
			}
		}
		/* Obtenemos where para Market */
		if ($data["tipo"] == "M") {
			if ($data["art_linea"] != NULL) {
				$where = "AND TRIM(cnf.art_linea) = '".TRIM($data["art_linea"])."'";
			}			
		}

		/* Obtenemos cuentas contables */
		$sql = "
		SELECT 
			cnf.act_config_id,
			cnf.act_account_id,
			TRIM(acc.acctcode) AS acctcode,
			TRIM(cnf.value) AS value
		FROM 
			act_config cnf 
			LEFT JOIN act_account acc ON (cnf.act_account_id = acc.act_account_id) 
		WHERE 
			1 = 1
			AND cnf.module        = '". $data["module"] ."'
			AND cnf.category      = '". $data["category"] ."'
			AND cnf.subcategory   = '". $data["subcategory"] ."'
			AND cnf.account_order = '". $data["account_order"] ."'
			$where
		LIMIT 1
		";

		// echo "<pre>getCuentaContablePersonalizada";
		// echo $sql;
		// echo "</pre>";
		
		// if ($data['value']['id_trans'] == "959521") {
		// 	echo "<pre>getCuentaContablePersonalizada 959521";
		// 	echo $sql;
		// 	echo "</pre>";
		// }

		if ($sqlca->query($sql) < 0) {
			return false;
		}
		
		$a = $sqlca->fetchRow();
		$act_config_id  = $a[0];
		$act_account_id = $a[1];
		$acctcode       = $a[2];
		$value          = $a[3];

		$data_cuenta['acctcode']       = $acctcode;
		$data_cuenta['value']          = $value;
		$data_cuenta['act_config_id']  = $act_config_id;
		$data_cuenta['act_account_id'] = $act_account_id;

		/* Si no se encontro Cuenta Contable, busca la Cuenta Contable Market (por Linea) por defecto que esta configurada por el campo art_linea = "ALL" */
		if ($data_cuenta['act_account_id'] == NULL) {
			if ($data["tipo"] == "M") {
				return $this->getCuentaContablePersonalizada_MarketALL($data);
			}
		}

		return $data_cuenta;
	}

    /**
	* Funcion para obtener Cuenta Contable Market (por Linea)
	* Linea: Campo "art_linea" de la tabla "act_config"
	*/
	public function getCuentaContablePersonalizada_MarketALL($data) {
		global $sqlca;

		$sql = "
		SELECT 
			cnf.act_config_id,
			cnf.act_account_id,
			TRIM(acc.acctcode) AS acctcode,
			TRIM(cnf.value) AS value
		FROM 
			act_config cnf 
			LEFT JOIN act_account acc ON (cnf.act_account_id = acc.act_account_id) 
		WHERE 
			1 = 1
			AND cnf.module          = '". $data["module"] ."'
			AND cnf.category        = '". $data["category"] ."'
			AND cnf.subcategory     = '". $data["subcategory"] ."'
			AND cnf.account_order   = '". $data["account_order"] ."'	
			AND TRIM(cnf.art_linea) = 'ALL'
		LIMIT 1		
		";

		// echo "<pre>getCuentaContablePersonalizada_MarketALL";
		// echo $sql;
		// echo "</pre>";

		// if ($data['value']['id_trans'] == "959521") {
		// 	echo "<pre>getCuentaContablePersonalizada_MarketALL";
		// 	echo $sql;
		// 	echo "</pre>";
		// }

		if ($sqlca->query($sql) < 0) {
			return false;
		}
		
		$a = $sqlca->fetchRow();
		$act_config_id  = $a[0];
		$act_account_id = $a[1];
		$acctcode       = $a[2];
		$value          = $a[3];

		$data_cuenta['acctcode']       = $acctcode;
		$data_cuenta['value']          = $value;
		$data_cuenta['act_config_id']  = $act_config_id;
		$data_cuenta['act_account_id'] = $act_account_id;

		return $data_cuenta;
	}

    /**
	* Funcion para obtener correlativo del subdiario
	*/
	public function getCorrelativoSubdiario($subdiario, $arrParams) {
		/**
		* Validamos que exista registro de subdiario y su correlativo
		* Se puede pasar en la funcion validateSubdiario, en el parametro 1, un array con varios arrays de subdiarios
		* @param 1 | array(array(), array())
		* @param 2 | array()
		*/
		$dataSubdiario     = array($subdiario);
		$responseSubdiario = $this->validateSubdiario($dataSubdiario, $arrParams);
		if ($responseSubdiario['error'] == TRUE) {
			return $responseSubdiario;		
		}
		
		global $sqlca;

		/* Recogemos parametros */
		$almacen   = TRIM($arrParams['sCodeWarehouse']);
		$fecha     = TRIM($arrParams['dEntry']);
		$subdiario = TRIM($subdiario['acctcode']);

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];		
		$dia       = "01";
		$fecha     = $anio."-".$mes."-".$dia;			
	
		//VERIFICAMOS EXISTENCIA DE SECUENCIA PREVIA
		$response_act_preseq = $this->verify_count_act_preseq($almacen, $fecha, $subdiario);
		if ($response_act_preseq['error']) {
			return $response_act_preseq;
		}
		$cantidad_act_preseq = $response_act_preseq['cantidad'];
	
		//OBTENEMOS SECUENCIA PREVIA
		if ($cantidad_act_preseq > 0) {
			$sql = "
				SELECT
					p.act_preseq_id,
					p.numerator
				FROM
					act_preseq p
					INNER JOIN act_registernumber r ON (p.act_registernumber_id = r.act_registernumber_id)
					INNER JOIN act_day d            ON (r.act_day_id = d.act_day_id)
				WHERE
					TRIM(d.ch_sucursal)     = '$almacen'
					AND DATE(d.dateacct)    = '$fecha'
					AND TRIM(r.subbookcode) = '$subdiario'
				ORDER BY 
					p.numerator ASC
				LIMIT 
					1;
			";
			// echo "\nOBTENER SECUENCIA PREVIA\n";
			// echo "<pre>$sql</pre>";

			if ($sqlca->query($sql) < 0) {
				return array('error' => TRUE, 'message' => 'Error en obtener correlativo en act_preseq');
			}

			$row           = $sqlca->fetchRow();
			$act_preseq_id = $row['act_preseq_id'];
			$numerator     = $row['numerator'];

			//ACTUALIZAMOS EL CORRELATIVO
			if (is_numeric($numerator) && $numerator >= 0) {
				$sql = "DELETE FROM act_preseq WHERE act_preseq_id = '$act_preseq_id' AND numerator = '$numerator'";
				
				if ($sqlca->query($sql) < 0) {
					return array('error' => TRUE, 'message' => 'Error en delete act_preseq');
				}

				return array(
					'error' => FALSE,
					'correlativo' => $numerator
				);		
			}

			return array('error' => TRUE, 'message' => 'No hay secuencia previa');
		} else if ($cantidad_act_preseq == 0) { //OBTENEMOS EL CORRELATIVO
			$sql = "
				SELECT   
					r.act_registernumber_id,
					r.numerator
				FROM
					act_registernumber r
					INNER JOIN act_day d ON (r.act_day_id = d.act_day_id)
				WHERE
					TRIM(d.ch_sucursal)     = '$almacen'
					AND DATE(d.dateacct)    = '$fecha'
					AND TRIM(r.subbookcode) = '$subdiario'
				LIMIT 1
			";
			// echo "\nOBTENER CORRELATIVO\n";
			// echo "<pre>$sql</pre>";

			if ($sqlca->query($sql) < 0) {
				return array('error' => TRUE, 'message' => 'Error en obtener correlativo en act_registernumber');
			}
			
			$row                   = $sqlca->fetchRow();
			$act_registernumber_id = $row['act_registernumber_id'];
			$numerator             = $row['numerator'];
			
			//ACTUALIZAMOS EL CORRELATIVO
			if (is_numeric($numerator) && $numerator >= 0) {
				$numerator = $numerator + 1;
				$sql = "UPDATE act_registernumber SET numerator = '$numerator' WHERE act_registernumber_id = '$act_registernumber_id'";
				
				if ($sqlca->query($sql) < 0) {
					return array('error' => TRUE, 'message' => 'Error en update act_registernumber');	
				}

				return array(
					'error' => FALSE,
					'correlativo' => $numerator
				);		
			}

			return array('error' => TRUE, 'message' => 'No hay correlativo');
		}
	}
	
	/**
	* Funcion para validar existencia de correlativos de subdiarios
	*/
	public function validateSubdiario($dataSubdiario, $arrParams) {		
		global $sqlca;

		/* Recogemos parametros */
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];		
		$dia       = "01";
		$fecha     = $anio."-".$mes."-".$dia;
		
		//RECORREMOS SUBDIARIOS
		foreach ($dataSubdiario as $key => $value) {
			//OBTENEMOS DATOS			
			$subdiario = TRIM($value['acctcode']);
			
			//OBTENEMOS CANTIDAD REGISTRO EN ACT_DAY
			$response_act_day = $this->verify_count_act_day($almacen, $fecha);
			if ($response_act_day['error']) {
				return $response_act_day;
			}
			$cantidad_act_day = $response_act_day['cantidad'];

			//SI NO EXISTE REGISTRO EN ACT_DAY
			if ($cantidad_act_day == 0) {
				$sql = "INSERT INTO act_day (act_day_id, ch_sucursal, dateacct, created) VALUES (nextval('seq_act_day_id'), '$almacen', '$fecha', NOW()) RETURNING act_day_id AS act_day_id";				
				// echo "\nINSERT ACT_DAY\n";
				// echo "<pre>$sql</pre>";				
				
				if ($sqlca->query($sql) < 0) {
					return array('error' => TRUE, 'message' => 'Error en insert act_day');
				}
				
				$row        = $sqlca->fetchRow();
				$act_day_id = $row["act_day_id"];
			} else if ($cantidad_act_day > 1) { //SI EXISTE MAS DE UNO
				return array('error' => TRUE, 'message' => 'Error en cantidad de registros en act_day');
			}
			
			//OBTENEMOS CANTIDAD REGISTRO EN ACT_REGISTERNUMBER
			$response_act_registernumber = $this->verifiy_count_act_registernumber($almacen, $fecha, $subdiario);		
			if ($response_act_registernumber['error']){
				return $response_act_registernumber;
			}
			$cantidad_act_registernumber = $response_act_registernumber['cantidad'];

			//SI NO EXISTE REGISTRO EN ACT_REGISTERNUMBER
			if ($cantidad_act_registernumber == 0) {
				if ($cantidad_act_day == 1) { //SI HAY UN REGISTRO EN ACT_DAY 
					$response_act_day_id = $this->get_act_day_id($almacen, $fecha);		
					if ($response_act_day_id['error']) {
						return $response_act_day_id;
					}
					$act_day_id = $response_act_day_id['act_day_id'];
				}

				$sql = "INSERT INTO act_registernumber (act_registernumber_id, act_day_id, subbookcode, numerator) VALUES (nextval('seq_act_registernumber_id'), '$act_day_id', '$subdiario', 0)";				
				// echo "\nINSERT ACT_REGISTERNUMBER\n";
				// echo "<pre>$sql</pre>";			
				
				if ($sqlca->query($sql) < 0) {
					return array('error' => TRUE, 'message' => 'Error en insert act_regiternumber');				
				}
			} else if ($cantidad_act_registernumber > 1) { //SI EXISTE MAS DE UNO
				return array('error' => TRUE, 'message' => 'Error en cantidad de registros en act_regiternumber');				
			}
		}

		return array(
			'error' => FALSE
		);
	}

	public function verify_count_act_day($almacen, $fecha) {
		global $sqlca;

		$sql = "
			SELECT 
				COUNT(*) AS cantidad 
			FROM 
				act_day 
			WHERE 
				TRIM(ch_sucursal)  = '$almacen'
				AND DATE(dateacct) = '$fecha'
		";			
		// echo "\nVERIFICAR ACT_DAY\n</>";
		// echo "<pre>$sql</pre>";
				
		if ($sqlca->query($sql) < 0)
			return array('error' => TRUE, 'message' => 'Error en verificar act_day');

		$row      = $sqlca->fetchRow();
		$cantidad = $row['cantidad'];
		
		return array(
			'error' => FALSE,
			'cantidad' => $cantidad,
		);
	}

	public function verifiy_count_act_registernumber($almacen, $fecha, $subdiario) {
		global $sqlca;

		$sql = "
			SELECT   
				COUNT(r.*) AS cantidad
			FROM
				act_registernumber r
				INNER JOIN act_day d ON (r.act_day_id = d.act_day_id)
			WHERE
				TRIM(d.ch_sucursal)     = '$almacen'
				AND DATE(d.dateacct)    = '$fecha'
				AND TRIM(r.subbookcode) = '$subdiario'
		";
		// echo "\nVERIFICAR ACT_REGISTERNUMBER\n";
		// echo "<pre>$sql</pre>";

		if ($sqlca->query($sql) < 0) {
			return array('error' => TRUE, 'message' => 'Error en verificar act_registernumber');
		}
		
		$row      = $sqlca->fetchRow();
		$cantidad = $row['cantidad'];

		return array(
			'error' => FALSE,
			'cantidad' => $cantidad,
		);
	}

	public function verify_count_act_preseq($almacen, $fecha, $subdiario) {
		global $sqlca;

		$sql = "
			SELECT   
				COUNT(p.*) AS cantidad
			FROM
				act_preseq p
				INNER JOIN act_registernumber r ON (p.act_registernumber_id = r.act_registernumber_id)
				INNER JOIN act_day d            ON (r.act_day_id = d.act_day_id)
			WHERE
				TRIM(d.ch_sucursal)     = '$almacen'
				AND DATE(d.dateacct)    = '$fecha'
				AND TRIM(r.subbookcode) = '$subdiario'
		";
		// echo "\nVERIFICAR ACT_PRESEQ\n";
		// echo "<pre>$sql</pre>";

		if ($sqlca->query($sql) < 0) {
			return array('error' => TRUE, 'message' => 'Error en verificar act_preseq');
		}
		
		$row      = $sqlca->fetchRow();
		$cantidad = $row['cantidad'];

		return array(
			'error' => FALSE,
			'cantidad' => $cantidad,
		);
	}

	public function get_act_day_id($almacen, $fecha) {
		global $sqlca;

		$sql = "
			SELECT 
				act_day_id 
			FROM 
				act_day 
			WHERE 
				TRIM(ch_sucursal)  = '$almacen' 
				AND DATE(dateacct) = '$fecha' 
			LIMIT 1
		";
		// echo "\nSELECT ACT_DAY_ID\n";
		// echo "<pre>$sql</pre>";	

		if ($sqlca->query($sql) < 0) {
			return array('error' => TRUE, 'message' => 'Error en select act_day');				
		}
		
		$row = $sqlca->fetchRow();
		$act_day_id = $row["act_day_id"];

		return array(
			'error' => FALSE,
			'act_day_id' => $act_day_id,
		);
	}

	public function get_act_registernumber_id($almacen, $fecha, $subdiario) {
		global $sqlca;

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];		
		$dia       = "01";
		$fecha     = $anio."-".$mes."-".$dia;

		$sql = "
			SELECT 
				r.act_registernumber_id 
			FROM 
				act_registernumber r 
				INNER JOIN act_day d ON (r.act_day_id = d.act_day_id)
			WHERE 
				TRIM(d.ch_sucursal)     = '$almacen' 
				AND DATE(d.dateacct)    = '$fecha' 
				AND TRIM(r.subbookcode) = '$subdiario'
			LIMIT 1
		";
		// echo "\nSELECT ACT_REGISTERNUMBER_ID\n";
		// echo "<pre>$sql</pre>";	

		if ($sqlca->query($sql) < 0) {
			return array('error' => TRUE, 'message' => 'Error en select act_registernumber_id');				
		}
		
		$row = $sqlca->fetchRow();
		$act_registernumber_id = $row["act_registernumber_id"];

		return array(
			'error' => FALSE,
			'act_registernumber_id' => $act_registernumber_id,
		);
	}
    
    function array_debug($text){
        echo "<pre>";
        print_r($text);
        echo "<pre>";
    }

	function str_debug($debug) {
        echo "\n$debug\n";        
        error_log($debug);
        echo "<script>console.log('".$debug."')</script>";		
	}

	/**
	* Funcion para obtener información adicional para el array de asientos, buscando por tableid y regid, es decir debe existir en el array de asientos los registros tableid y regid
	*/
	function get_data_arrayEntry($arrEntry) {
		foreach ($arrEntry as $key => $entry) {
			if (isset($entry['tableid']) && isset($entry['regid'])) {
				$response_tableid_regid = $this->get_data_by_tableid_regid($entry['tableid'], $entry['regid']);
				$documento              = $response_tableid_regid['documento'];
				$serie                  = $response_tableid_regid['serie'];
				$numero                 = $response_tableid_regid['numero'];
				$tipo_documento_sunat   = $response_tableid_regid['tipo_documento_sunat'];

				//VALIDACION DE INFORMACION A MOSTRAR PARA DOCUMENTO SUSTENTATORIO
				if (!isset($documento) || empty($documento)) {
					$documento = '-';
				}

				$arrEntry[$key]['documento_sustentatorio'] = $documento;
				$arrEntry[$key]['serie']                   = $serie;
				$arrEntry[$key]['numero']                  = $numero;
				$arrEntry[$key]['tipo_documento_sunat']    = $tipo_documento_sunat;
			}
		}
		return $arrEntry;
	}

	/**
	* Los valores para el campo "tableid"
	* Descripción breve
	* 1 = pos_transXXXXYY
	* 2 = fac_ta_factura_cabecera
	* 3 = cpag_ta_cabecera
	* 4 = c_cash_transaction
	*
	* Los valores para el campo "regid", entendiendo que viene acompañado de "tableid":
	* Descripción breve
	* 1 = es * caja * trans * tabla * usr * Tipo de Documento SUNAT
	* 2 = ch_almacen * ch_fac_tipodocumento * ch_fac_seriedocumento * ch_fac_numerodocumento * cli_codigo * Tipo de Documento SUNAT
	* 3 = pro_cab_almacen * pro_cab_tipdocumento * pro_cab_seriedocumento * pro_cab_numdocumento * pro_codigo * Tipo de Documento SUNAT
	* 4 = c_cash_transaction_id
	*/
	function get_data_by_tableid_regid($tableid, $regid, $db = FALSE, $param = array()) {
		if ($tableid == "1") {
			//OBTENEMOS DATOS
			$porciones         = explode("*", $regid);									
			$data['es']        = $porciones[0];
			$data['caja']      = $porciones[1];
			$data['trans']     = $porciones[2];
			$data['tabla']     = $porciones[3];		
			$data['usr']       = $porciones[4];	

			//Serie y Numero de Documento
			$data['documento'] = $porciones[4];
			$porciones_usr     = explode("-", $porciones[4]);
			$data['serie']     = $porciones_usr[0];
			$data['numero']    = $porciones_usr[1];

			//Tipo de Documento Sunat
			$data['tipo_documento_sunat'] = $porciones[5];

			//LOGICA PARA OBTENER DATOS ADICIONALES
			if ($db) {
				if (isset($param["tipo_documento_entidad_emisor"]) && isset($param["numero_documento_entidad_emisor"])) {
					$dataDB = $this->get_tabla($tableid, $data);
				}
			}
		} else if ($tableid == "2") {
			//OBTENEMOS DATOS
			$porciones                      = explode("*", $regid);									
			$data['ch_almacen']             = $porciones[0];
			$data['ch_fac_tipodocumento']   = $porciones[1];
			$data['ch_fac_seriedocumento']  = $porciones[2];
			$data['ch_fac_numerodocumento'] = $porciones[3];
			$data['cli_codigo']             = $porciones[4];

			//Serie y Numero de Documento
			$data['documento']              = $porciones[2]."-".$porciones[3];
			$data['serie']                  = $porciones[2];
			$data['numero']                 = $porciones[3];

			//Tipo de Documento Sunat
			$data['tipo_documento_sunat'] = $porciones[5];

			//LOGICA PARA OBTENER DATOS ADICIONALES
			if ($db) {
				if (isset($param["tipo_documento_entidad_emisor"]) && isset($param["numero_documento_entidad_emisor"])) {
					$dataDB = $this->get_tabla($tableid, $data);
				}
			}
		} else if ($tableid == "3") {
			//OBTENEMOS DATOS
			$porciones                      = explode("*", $regid);									
			$data['pro_cab_almacen']        = $porciones[0];
			$data['pro_cab_tipdocumento']   = $porciones[1];
			$data['pro_cab_seriedocumento'] = $porciones[2];
			$data['pro_cab_numdocumento']   = $porciones[3];
			$data['pro_codigo']             = $porciones[4];

			//Serie y Numero de Documento
			$data['documento']              = $porciones[2]."-".$porciones[3];
			$data['serie']                  = $porciones[2];
			$data['numero']                 = $porciones[3];

			//Tipo de Documento Sunat
			$data['tipo_documento_sunat'] = $porciones[5];

			//LOGICA PARA OBTENER DATOS ADICIONALES
			if ($db) {
				if (isset($param["tipo_documento_entidad_emisor"]) && isset($param["numero_documento_entidad_emisor"])) {
					$dataDB = $this->get_tabla($tableid, $data);
				}
			}						
		} else if ($tableid == "4") {
			$data['documento'] = "-";
		} else {
			$data['documento'] = "-";
		}
		
		//RETORNAMOS INFORMACION
		return $data;
	}

	function get_data_tabla($tabla) {
		global $sqlca;

		if ($tabla == 1) {
			//EJECUTAMOS QUERY
				$sql = "
				SELECT
					usr AS documento
				FROM 
					".$tabla."
				WHERE
					1 = 1
					AND es    = '$es'
					AND caja  = '$caja'
					AND trans = '$trans'
				LIMIT 1;
			";

			if ($sqlca->query($sql) < 0) {
				return array(
					'error' => TRUE,
					'sql' => $sql,
				);
			}
			
			$row = $sqlca->fetchRow();			
			$data['documento'] = $row['documento'];
		} else if ($tabla == 2) {
			//EJECUTAMOS QUERY
			$sql = "
			SELECT
				ch_fac_seriedocumento || '-' || ch_fac_numerodocumento AS documento
			FROM 
				fac_ta_factura_cabecera
			WHERE
				1 = 1
				AND ch_almacen             = '$ch_almacen'
				AND ch_fac_tipodocumento   = '$ch_fac_tipodocumento'
				AND ch_fac_seriedocumento  = '$ch_fac_seriedocumento'
				AND ch_fac_numerodocumento = '$ch_fac_numerodocumento'
				AND cli_codigo             = '$cli_codigo'
			LIMIT 1;
			";

			if ($sqlca->query($sql) < 0) {
				return array(
					'error' => TRUE,
					'sql' => $sql,
				);
			}

			$row = $sqlca->fetchRow();			
			$data['documento'] = $row['documento'];
		} else if ($tabla == 3) {
			//EJECUTAMOS QUERY
			$sql = "
				SELECT
					pro_cab_seriedocumento || '-' || pro_cab_numdocumento AS documento
				FROM 
					cpag_ta_cabecera
				WHERE
					1 = 1
					AND pro_cab_almacen        = '$pro_cab_almacen'
					AND pro_cab_tipdocumento   = '$pro_cab_tipdocumento'
					AND pro_cab_seriedocumento = '$pro_cab_seriedocumento'
					AND pro_cab_numdocumento   = '$pro_cab_numdocumento'
					AND pro_codigo             = '$pro_codigo'
				LIMIT 1;
			";

			if ($sqlca->query($sql) < 0) {
				return array(
					'error' => TRUE,
					'sql' => $sql,
				);
			}
			
			$row = $sqlca->fetchRow();			
			$data['documento'] = $row['documento'];
		} else if ($tabla == 4) {
			$data['documento'] = $row['documento'];
		} else {
			$data['documento'] = $row['documento'];
		}
		
		return $data;
	}

	function generarBalancePorAnioCierre($arrParams) {
		$meses = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
		foreach ($meses as $mes) {
			$params['sCodeWarehouse'] = $arrParams['Nu_Almacen'];
			$params['dEntry']         = $arrParams['Fe_Periodo'] ."-". $mes ."-". "01";
			$params['isDebug']        = FALSE;
			$this->generarBalance($params);
		}
	}

	function generarBalance($arrParams) {
		global $sqlca;

		/* Recogemos parametros */
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);
		$isDebug = $arrParams['isDebug'];

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];		

		/* Obtenemos fechas para usar en queries */
		$result        = Array();
		$fecha_mes     = $anio . "-" . $mes;
		$fecha_balance = $anio . "-" . $mes . "-" . "01";

		//ELIMINAMOS BALANCE DE TODO EL MES
		$sql_eliminar_balance = "DELETE FROM act_balance WHERE TRIM(ch_sucursal) = '$almacen' AND TO_CHAR(DATE(dateacct),'YYYY-MM') = '$fecha_mes'";

		//VERIFICAMOS QUE ELIMINACION SE REALIZO CORRECTAMENTE
		$iStatus = $sqlca->query($sql_eliminar_balance);
		
		if ((int)$iStatus >= 0) {
			//GENERAMOS BALANCE DE TODO EL MES
			$sql_generar_balance = "
				SELECT
					e.ch_sucursal, 
					a.act_account_id,
					a.acctcode,
					el.tab_currency AS tab_currency,
					SUM(el.amtdt) AS amtdt, 
					SUM(el.amtct) AS amtct, 
					SUM(el.amtsourcedt) AS amtsourcedt,
					SUM(el.amtsourcect) AS amtsourcect
				FROM
					act_entryline el
					LEFT JOIN act_entry   AS e ON (el.act_entry_id   = e.act_entry_id)
					LEFT JOIN act_account AS a ON (el.act_account_id = a.act_account_id)
				WHERE
					1 = 1
					AND TRIM(e.ch_sucursal) = '". $almacen . "'
					AND TO_CHAR(DATE(e.documentdate),'YYYY-MM') = '" . $fecha_mes . "'
					AND el.tab_currency IS NOT NULL
					AND e.act_entrytype_id NOT IN ('11')
				GROUP BY
					e.ch_sucursal, a.act_account_id, el.tab_currency
				ORDER BY 
					1,2,3;
			";

			if ($isDebug) {
				echo "<pre>sql_facturas_manuales:";
				echo "$sql_generar_balance";
				echo "</pre>";
			}

			if ($sqlca->query($sql_generar_balance) < 0) {
				return array('error' => TRUE, 'message' => 'Error en sql_generar_balance');
			}

			//RECORREMOS INFORMACION DE BALANCE
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
			
				$result[$i]['ch_sucursal']    = TRIM($a['ch_sucursal']);
				$result[$i]['act_account_id'] = $a['act_account_id'];
				$result[$i]['acctcode']       = TRIM($a['acctcode']);
				$result[$i]['tab_currency']   = TRIM($a['tab_currency']);
				$result[$i]['amtdt']          = $a['amtdt'];
				$result[$i]['amtct']          = $a['amtct'];
				$result[$i]['amtsourcedt']    = $a['amtsourcedt'];
				$result[$i]['amtsourcect']    = $a['amtsourcect'];
			}

			if ($isDebug) {
				echo "<script>console.log('result')</script>";
				echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";
			}

			//RECORREMOS INFORMACION DE BALANCE
			foreach ($result as $key => $value) {	
				$ch_sucursal    = $value['ch_sucursal'];
				$act_account_id = $value['act_account_id'];
				$acctcode       = $value['acctcode'];
				$tab_currency   = $value['tab_currency'];
				
				/**
				* Balance de Saldos Acumulados
				* El proceso de Balance de Saldos Acumulados solo guarda saldos acumulados en DOLARES de las cuentas: 12, 14, 16, 41, 42, 44, 45, 46. Solo si hubiera asientos cuya moneda original sea DOLARES y en esos asientos estuvieran esas Cuentas Contables 
				*/
				$cuentas = substr($acctcode, 0, 2);
				if ($cuentas == "12" || $cuentas == "14" || $cuentas == "16" || $cuentas == "41" || $cuentas == "42" || $cuentas == "45" || $cuentas == "46") {
					if ($tab_currency == "01") { //Si la moneda es '01' SOLES
						$amtdt = $value['amtdt'];
						$amtct = $value['amtct'];
					} else { //Si la moneda es '02' DOLARES
						$amtdt = $value['amtsourcedt'];
						$amtct = $value['amtsourcect'];
					}
				} else { //Guardamos todo convertido en SOLES y con MONEDA '01'
					$tab_currency = "01";
					$amtdt = $value['amtdt'];
					$amtct = $value['amtct'];
				}

				//INSERTAMOS BALANCE
				$sql_insertar_balance = "
					INSERT INTO act_balance (
						act_balance_id, 
						ch_sucursal, 
						dateacct, 
						act_account_id, 
						amtdt, 
						amtct, 
						tab_currency
					) VALUES (
						nextval('seq_act_balance_id'),
						'$ch_sucursal',
						'$fecha_balance',
						'$act_account_id',
						'$amtdt',
						'$amtct',
						'$tab_currency'
					);
				";
				
				$iStatus = $sqlca->query($sql_insertar_balance);

				if ((int)$iStatus < 0) {
					return array('error' => TRUE, 'message' => 'Error en insert sql_insertar_balance');
				}
			}
		} else {
			return array('error' => TRUE, 'message' => 'Error en sql_eliminar_balance');
		}

		return array(
			'error' => FALSE
		);
	}
}