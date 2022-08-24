<?php /*Copy /sistemaweb/helper.php*/
include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");

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
}