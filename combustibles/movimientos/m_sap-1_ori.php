<?php
class m_sap_1 {
	var $isDebug = true;//Define el modo de depuracion
	var $isViewTableName = true;//Define la visibilidad del nombre de las tablas HANA en la previsualizacion
	var $employeeMarketDefault = '';
	var $ticketHead = array();//almacena informacion cabecera de boletas consultadas en el mismo día [OPEN]
	var $ticketDetail = array();//almacena informacion detalle de boletas consultadas en el mismo día [OPEN]
	var $erroConnectionHANA = false;
	var $creditNote = array();//almacena las notas de credito con su origen para detalle [Consultado a HANA]
	var $debitNote = array();//almacena las notas de credito con su origen para detalle [Consultado a HANA]

	var $invoiceSaleHead = array();//almacena informacion cabecera de facturas consultadas en el mismo día [OPEN]
	var $invoiceSaleDetail = array();//almacena informacion detalle de facturas consultadas en el mismo día [OPEN]

	function __construct() {
	}

	public function array_debug($data){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

	public function setIsDebug($is) {
		$this->isDebug = $is;
	}

	public function setIsViewTableName($is) {
		$this->isViewTableName = $is;
	}

	public function cleanStr($str) {
		return trim($str);
	}

	public function _error_log($text) {
		if ($this->isDebug)  {
			error_log($text);
		}
	}

	public function getConnectionData() {
		global $sqlca;
		$sql = "
SELECT
 p2.par_valor AS hana_warehouse,
 p3.par_valor AS hana_dbname,
 p4.par_valor AS hana_username,
 p5.par_valor AS hana_password
FROM
 int_parametros p1
 LEFT JOIN int_parametros p2 ON p2.par_nombre = 'hana_warehouse'
 LEFT JOIN int_parametros p3 ON p3.par_nombre = 'hana_dbname'
 LEFT JOIN int_parametros p4 ON p4.par_nombre = 'hana_username'
 LEFT JOIN int_parametros p5 ON p5.par_nombre = 'hana_password'
WHERE
 p1.par_nombre = 'hana_warehouse';
 		";

		$result = $sqlca->query($sql);
		if ($result == 0) {
			return array(
				'error' => true,
				'message' => 'No existen credenciales',
			);
		}
		$row = $sqlca->fetchRow();
		$row['error'] = false;
		return $row;
	}

	public function connectionHana($param) { //Este es el problema		
		$res = array();
		if ($param['error'] == false) {
			//echo "<script>console.log('res_condicion: Entro en la condicion')</script>"; //Este es el problema			
			$conn = odbc_connect($param['hana_warehouse'], $param['hana_username'], $param['hana_password']);
			//echo "<script>console.log('conn: " . $conn . "')</script>"; //Este es el problema
			if ($conn) {				
				$res['db'] = $param['hana_dbname'];
				$res['instance'] = $conn;
				$res['error'] = false;
				$res['code'] = 0;
				$res['message'] = 'Ok';
			} else {				
				$this->erroConnectionHANA = false;
				$res['error'] = true;
				$res['code'] = 1;
				$res['message'] = 'Error de conexión - '.odbc_errormsg();				
			}			
		} else {
			$this->erroConnectionHANA = false;
			$res['error'] = true;
			$res['code'] = 2;
			$res['message'] = 'No existen credenciales para la conexión.';
		}
		return $res;
	}

	public function getUserIdByChLogin() {
		global $sqlca;

		if (isset($_SESSION['auth_usuario'])) {
			$ch_login = $_SESSION['auth_usuario'];
		} else {
			return array('error' => true, 'code' => 0, 'message' => 'No existe session');
		}

		$sql = "SELECT uid AS user_id FROM int_usuarios_passwd WHERE TRIM(ch_login) = TRIM('$ch_login');";
		$result = $sqlca->query($sql);
		if ($result == 0) {
			return array('error' => true, 'code' => 1, 'message' => 'No existe usuario');
		}
		$row = $sqlca->fetchRow();
		$row['error'] = false;
		return $row;
	}

	/**
	 * Almacenes
	 */
	public function getWarehouse() {
		global $sqlca;
		$sql = "SELECT
 ch_almacen AS id,
 ch_almacen||' - '||ch_nombre_almacen AS name
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen = '1'
ORDER BY
 ch_almacen;";
 		$result = $sqlca->query($sql);
		if ($sqlca->query($sql) > 0) {
			while ($result = $sqlca->fetchRow()) {
				$res[] = $result;
			}
			return $res;
		} else {
			return null;
		}
	}

	/**
	 * Sucursales
	 */
	public function getSucursal() {
		global $sqlca;
		$sql = "SELECT
 ch_sucursal AS id,
 ch_sucursal||' - '||ch_nombre_sucursal AS name
FROM
 int_ta_sucursales
 -- WHERE
 -- ch_clase_almacen = '1'
ORDER BY
 ch_sucursal;";
 		$result = $sqlca->query($sql);
		if ($sqlca->query($sql) > 0) {
			while ($result = $sqlca->fetchRow()) {
				$res[] = $result;
			}
			return $res;
		} else {
			return null;
		}
	}

	/**
	 * Tarjetas de Credito
	 */
	public function getTarjetaCredito() {
		global $sqlca;
		$sql = "SELECT   tab_elemento, tab_elemento||' - '||tab_descripcion AS tab_descripcion 
				FROM     int_tabla_general 
				WHERE    tab_tabla = '95' AND tab_elemento <> '000000' 
				ORDER BY tab_elemento;";
 		$result = $sqlca->query($sql);
		if ($sqlca->query($sql) > 0) {
			while ($result = $sqlca->fetchRow()) {
				$res[] = $result;
			}
			return $res;
		} else {
			return null;
		}
	}

	/**
	 * Ultimo día cerrado
	 */
	public function getLastDayClose() {
		global $sqlca;
		$sql = "SELECT TO_CHAR(da_fecha - integer '1', 'YYYY-MM-DD') AS date FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		return $row['date'];
	}

	/**
	 * Inpuesto
	 */
	public function getTax() {
		global $sqlca;
		$sql = "SELECT util_fn_igv();";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		$row['tax'] = (double)$row['util_fn_igv'];
		$row['tax'] = (1 + ($row['tax'] / 100));
		return $row['tax'];
	}

	/**
	 * Factor bonus
	 */
	public function getFactorBonus() {
		global $sqlca;
		$sql = "SELECT par_valor AS factor_bonus FROM int_parametros WHERE par_nombre = 'prom_factor_bonus';";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		$factor_bonus = $row['factor_bonus'];
		if (empty($row['factor_bonus'])) {//Verificar que valor se asignará cuando no tenga Puntos BONUS
			$factor_bonus = 1;
		}
		return $factor_bonus;
	}

	public function isValidEmail($email) { 
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * Socios(Clientes)
	*/
	public function getBPartner($hanaInstance, $param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
bpartner.Nu_Codigo_Cliente AS cardcode,
FIRST(No_Nombre_Cliente) AS CARDNAME,
FIRST(Nu_Documento_Identidad) AS FEDERALTAXID,
FIRST(Nu_Telefono1) AS PHONE,
FIRST(Txt_Email) AS EMAIL,
FIRST(Txt_Direccion) AS STREET,
FIRST(No_Contacto) AS _contact_name
FROM (
(SELECT
 CLI.cli_codigo AS Nu_Codigo_Cliente,
 FIRST(CLI.cli_razsocial) AS No_Nombre_Cliente,
 FIRST(CLI.cli_ruc) AS Nu_Documento_Identidad,
 FIRST(CLI.cli_telefono1) AS Nu_Telefono1,
 FIRST(CLI.cli_email) AS Txt_Email,
 FIRST(CLI.cli_ndespacho_efectivo),
 FIRST(CLI.cli_anticipo),
 FIRST(CLI.cli_creditosol),
 FIRST(CLI.cli_direccion) AS Txt_Direccion,
 FIRST(CLI.cli_contacto) AS No_Contacto
FROM
 fac_ta_factura_cabecera AS FC
 JOIN int_clientes AS CLI ON(FC.cli_codigo = CLI.cli_codigo)
WHERE
 FC.ch_fac_tipodocumento IN ('10', '11', '20', '35')
 AND FC.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
GROUP BY CLI.cli_codigo)

UNION

(SELECT
 CLI.cli_codigo AS Nu_Codigo_Cliente,
 FIRST(CLI.cli_razsocial) AS No_Nombre_Cliente,
 FIRST(CLI.cli_ruc) AS Nu_Documento_Identidad,
 FIRST(CLI.cli_telefono1) AS Nu_Telefono1,
 FIRST(CLI.cli_email) AS Txt_Email,
 FIRST(CLI.cli_ndespacho_efectivo),
 FIRST(CLI.cli_anticipo),
 FIRST(CLI.cli_creditosol),
 FIRST(CLI.cli_direccion) AS Txt_Direccion,
 FIRST(CLI.cli_contacto) AS No_Contacto
FROM
 val_ta_cabecera AS VC
 JOIN int_clientes AS CLI ON(VC.ch_cliente = CLI.cli_codigo)
WHERE
 VC.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
GROUP BY CLI.cli_codigo)

UNION

(SELECT
 CLI.ruc AS Nu_Codigo_Cliente,
 FIRST(CLI.razsocial) AS No_Nombre_Cliente,
 FIRST(CLI.ruc) AS Nu_Documento_Identidad,
 '' AS Nu_Telefono1,
 '' AS Txt_Email,
 NULL AS cli_ndespacho_efectivo,
 '' AS cli_anticipo,
 NULL AS cli_creditosol,
 '' AS Txt_Direccion,
 '' AS No_Contacto
FROM
 ".$param['pos_trans']." AS VC
 JOIN ruc AS CLI ON(VC.ruc = CLI.ruc)
WHERE
 VC.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
GROUP BY CLI.ruc)

) bpartner
GROUP BY 1 
ORDER BY 2;";
		
		/***Agregado 2020-01-10***/
		// echo "<script>console.log('param: " . json_encode($param) . "')</script>";				
		// echo "<script>console.log('HANA message: " . json_encode($hanaInstance['message']) . "')</script>";
		// echo "<script>console.log('HANA instance: " . json_encode($hanaInstance['instance']) . "')</script>";
		/***/

		$this->_error_log($param['tableName'].' - getBPartner: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}

		/***Agregado 2020-01-14***/
		//echo "<script>console.log('fetchAll: " . json_encode($sqlca->fetchAll()) . "')</script>";			
		/***/

		$c = 0;
		$iCounter = 0;
		while ($reg = $sqlca->fetchRow()) {
			/***Agregado 2020-01-10***/
			// echo "<script>console.log('Registro $c: " . json_encode($reg) . "')</script>";				
			/***/

			// $c++;

			$reg['cardname'] = str_replace("'", "''", $reg['cardname']);
			$u_exx_tipopers = strlen($this->cleanStr($reg['cardcode'])) <= 8 ? 'TPN' : 'TPJ';
			$u_exx_tipodocu = '0';
			if (strlen($this->cleanStr($reg['cardcode'])) == 8) {
				$u_exx_tipodocu = '1';
			} else if (strlen($this->cleanStr($reg['cardcode'])) == 11) {
				$u_exx_tipodocu = '6';
			}
			//Carnet de extranjeria, Pasaporte, Cédula Diplomática

			$u_exx_apellpat = '';
			$u_exx_apellmat = '';
			$u_exx_primerno = '';
			$u_exx_segundno = '';

			$let = $this->cleanStr($reg['cardcode']);						
			$let = substr($let, 0, 1);
			if ($u_exx_tipopers == 'TPJ' && $let != '2') {
				$u_exx_tipopers = 'TPN';
			}
			// echo "<script>console.log('$c: " . json_encode($let) . "')</script>";

			if ($u_exx_tipopers == 'TPN') {
				$arr_cardname = explode(' ',$reg['cardname']);
				/*$u_exx_apellpat = isset($arr_cardname[2]) ? $arr_cardname[2] : '';
				$u_exx_apellmat = isset($arr_cardname[3]) ? $arr_cardname[3] : '';

				$u_exx_primerno = isset($arr_cardname[0]) ? $arr_cardname[0] : '';
				$u_exx_segundno = isset($arr_cardname[1]) ? $arr_cardname[1] : '';*/

				$u_exx_primerno = isset($arr_cardname[2]) ? $arr_cardname[2] : '';
				$u_exx_segundno = isset($arr_cardname[3]) ? $arr_cardname[3] : '';

				$u_exx_apellpat = isset($arr_cardname[0]) ? $arr_cardname[0] : '';
				$u_exx_apellmat = isset($arr_cardname[1]) ? $arr_cardname[1] : '';
			}

			$reg['name'] = '';
			$reg['lastname'] = '';
			if ($this->cleanStr($reg['_contact_name']) != '' && $reg['_contact_name'] != null && $reg['_contact_name'] != 'null') {
				$arr__contact_name = explode(' ',$reg['_contact_name']);
				$reg['lastname'] = $arr__contact_name[0];
				$reg['name'] = $arr__contact_name[1];
			}

			$email = $this->isValidEmail($this->cleanStr($reg['email'])) ? $this->cleanStr($reg['email']) : '';
			$phone = $this->cleanStr($reg['phone']);
			$phone = strlen($phone) >= 6 ? $phone : '';

			//echo "<script>console.log('$c: " . json_encode($reg) . "')</script>";
			$c++;
			/*
            error_log(' ======================================== ');
            error_log('DB -> ');
            error_log($hanaInstance['db']);
            error_log('TABLE -> ');
            error_log($param['tableName']);
            */
			$sql_sap = "SELECT CARDCODE FROM ".$hanaInstance['db'].".".$param['tableName']." WHERE CARDCODE='" . $this->preLetterBPartner('C', $reg['cardcode']) . "' LIMIT 1";			
			// echo "<pre>Query $c: ";
			// echo "$sql_sap";
			// echo "</pre>";
            /*
            error_log('CLIENTE -> ');
            error_log($sql_sap);
            error_log(' ======================================== ');
			*/
			$iStatusSQLSAP = odbc_exec($hanaInstance['instance'], $sql_sap);			
			$arrRowResultSAP = odbc_fetch_array($iStatusSQLSAP);
			// echo "<pre>arrRowResultSAP: ";
			// var_dump($arrRowResultSAP);
			// echo "</pre>";            
            if ( $arrRowResultSAP === false ) { //if ( $arrRowResultSAP != false ) { //Este es el problema
				$res[] = array(
					'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
					'cardname' => $this->cleanStr($reg['cardname']),
					'federaltaxid' => $this->cleanStr($reg['federaltaxid']),
					'phone' => $phone,
					'email' => $email,
					'street' => $this->cleanStr($reg['street']),
					'name' => $this->cleanStr($reg['name']),
					'lastname' => $this->cleanStr($reg['lastname']),
					'u_exx_tipopers' => $u_exx_tipopers,
					'u_exx_tipodocu' => $u_exx_tipodocu,
					'u_exx_apellpat' => $u_exx_apellpat,
					'u_exx_apellmat' => $u_exx_apellmat,
					'u_exx_primerno' => $u_exx_primerno,
					'u_exx_segundno' => $u_exx_segundno,
					'estado' => 'P',
					'errormsg' => '',
				);
				++$iCounter;
			}
		}
		if ( $iCounter > 0 ) {// Clientes se insertaran en HANA
			return array(
				'error' => false,
				'tableName' => $param['tableName'],
				'nodeData' => 'bpartner',
				'bpartner' => $res,
				'iCountHana' => $iCounter,
				'sStatus' => 'success',
				'iCountLocal' => ($c - $iCounter),
			);
		} else {// Clientes NO se insertaran en HANA
			return array(
				'error' => false,
				'tableName' => $param['tableName'],
				'nodeData' => 'bpartner',
				'bpartner' => '',
				'sStatus' => 'danger',
				'sMessage' => 'Clientes ya fueron migrados a HANA',
			);
		}
	}

	/**
	 * Socios(Clientes)
	*/
	public function getBPartnerRequerimientoEnergigas($hanaInstance, $param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
bpartner.Nu_Codigo_Cliente AS cardcode,
FIRST(No_Nombre_Cliente) AS CARDNAME,
FIRST(Nu_Documento_Identidad) AS FEDERALTAXID,
FIRST(Nu_Telefono1) AS PHONE,
FIRST(Txt_Email) AS EMAIL,
FIRST(Txt_Direccion) AS STREET,
FIRST(No_Contacto) AS _contact_name
FROM (
(SELECT
 CLI.cli_codigo AS Nu_Codigo_Cliente,
 FIRST(CLI.cli_razsocial) AS No_Nombre_Cliente,
 FIRST(CLI.cli_ruc) AS Nu_Documento_Identidad,
 FIRST(CLI.cli_telefono1) AS Nu_Telefono1,
 FIRST(CLI.cli_email) AS Txt_Email,
 FIRST(CLI.cli_ndespacho_efectivo),
 FIRST(CLI.cli_anticipo),
 FIRST(CLI.cli_creditosol),
 FIRST(CLI.cli_direccion) AS Txt_Direccion,
 FIRST(CLI.cli_contacto) AS No_Contacto
FROM
 fac_ta_factura_cabecera AS FC
 JOIN int_clientes AS CLI ON(FC.cli_codigo = CLI.cli_codigo)
WHERE
 FC.ch_fac_tipodocumento IN ('10', '11', '20', '35')
 AND FC.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
GROUP BY CLI.cli_codigo)

UNION

(SELECT
 CLI.cli_codigo AS Nu_Codigo_Cliente,
 FIRST(CLI.cli_razsocial) AS No_Nombre_Cliente,
 FIRST(CLI.cli_ruc) AS Nu_Documento_Identidad,
 FIRST(CLI.cli_telefono1) AS Nu_Telefono1,
 FIRST(CLI.cli_email) AS Txt_Email,
 FIRST(CLI.cli_ndespacho_efectivo),
 FIRST(CLI.cli_anticipo),
 FIRST(CLI.cli_creditosol),
 FIRST(CLI.cli_direccion) AS Txt_Direccion,
 FIRST(CLI.cli_contacto) AS No_Contacto
FROM
 val_ta_cabecera AS VC
 JOIN int_clientes AS CLI ON(VC.ch_cliente = CLI.cli_codigo)
WHERE
 VC.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
GROUP BY CLI.cli_codigo)

UNION

(SELECT
 CLI.ruc AS Nu_Codigo_Cliente,
 FIRST(CLI.razsocial) AS No_Nombre_Cliente,
 FIRST(CLI.ruc) AS Nu_Documento_Identidad,
 '' AS Nu_Telefono1,
 '' AS Txt_Email,
 NULL AS cli_ndespacho_efectivo,
 '' AS cli_anticipo,
 NULL AS cli_creditosol,
 '' AS Txt_Direccion,
 '' AS No_Contacto
FROM
 ".$param['pos_trans']." AS VC
 JOIN ruc AS CLI ON(VC.ruc = CLI.ruc)
WHERE
 VC.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
GROUP BY CLI.ruc)

UNION --ESTE UNION SE AGREGO COMO PARTE DEL REQUERIMIENTO DE ENERGIGIAS PARA OBTENER CLIENTES DE LA TABLA INT_CLIENTES

(SELECT
 CLI.cli_ruc AS Nu_Codigo_Cliente,
 FIRST(CLI.cli_razsocial) AS No_Nombre_Cliente,
 FIRST(CLI.cli_ruc) AS Nu_Documento_Identidad,
 '' AS Nu_Telefono1,
 '' AS Txt_Email,
 NULL AS cli_ndespacho_efectivo,
 '' AS cli_anticipo,
 NULL AS cli_creditosol,
 '' AS Txt_Direccion,
 '' AS No_Contacto
FROM
 ".$param['pos_trans']." AS VC
 JOIN int_clientes AS CLI ON(VC.ruc = CLI.cli_ruc)
WHERE
 VC.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
GROUP BY CLI.cli_ruc)

) bpartner
GROUP BY 1 
ORDER BY 2;";
		
		/***Agregado 2020-01-10***/
		// echo "<script>console.log('param: " . json_encode($param) . "')</script>";				
		// echo "<script>console.log('HANA message: " . json_encode($hanaInstance['message']) . "')</script>";
		// echo "<script>console.log('HANA instance: " . json_encode($hanaInstance['instance']) . "')</script>";
		/***/

		$this->_error_log($param['tableName'].' - getBPartner: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}

		/***Agregado 2020-01-14***/
		//echo "<script>console.log('fetchAll: " . json_encode($sqlca->fetchAll()) . "')</script>";			
		/***/

		$c = 0;
		$iCounter = 0;
		while ($reg = $sqlca->fetchRow()) {
			/***Agregado 2020-01-10***/
			// echo "<script>console.log('Registro $c: " . json_encode($reg) . "')</script>";				
			/***/

			// $c++;

			$reg['cardname'] = str_replace("'", "''", $reg['cardname']);
			$u_exx_tipopers = strlen($this->cleanStr($reg['cardcode'])) <= 8 ? 'TPN' : 'TPJ';
			$u_exx_tipodocu = '0';
			if (strlen($this->cleanStr($reg['cardcode'])) == 8) {
				$u_exx_tipodocu = '1';
			} else if (strlen($this->cleanStr($reg['cardcode'])) == 11) {
				$u_exx_tipodocu = '6';
			}
			//Carnet de extranjeria, Pasaporte, Cédula Diplomática

			$u_exx_apellpat = '';
			$u_exx_apellmat = '';
			$u_exx_primerno = '';
			$u_exx_segundno = '';

			$let = $this->cleanStr($reg['cardcode']);						
			$let = substr($let, 0, 1);
			if ($u_exx_tipopers == 'TPJ' && $let != '2') {
				$u_exx_tipopers = 'TPN';
			}
			// echo "<script>console.log('$c: " . json_encode($let) . "')</script>";

			if ($u_exx_tipopers == 'TPN') {
				$arr_cardname = explode(' ',$reg['cardname']);
				/*$u_exx_apellpat = isset($arr_cardname[2]) ? $arr_cardname[2] : '';
				$u_exx_apellmat = isset($arr_cardname[3]) ? $arr_cardname[3] : '';

				$u_exx_primerno = isset($arr_cardname[0]) ? $arr_cardname[0] : '';
				$u_exx_segundno = isset($arr_cardname[1]) ? $arr_cardname[1] : '';*/

				$u_exx_primerno = isset($arr_cardname[2]) ? $arr_cardname[2] : '';
				$u_exx_segundno = isset($arr_cardname[3]) ? $arr_cardname[3] : '';

				$u_exx_apellpat = isset($arr_cardname[0]) ? $arr_cardname[0] : '';
				$u_exx_apellmat = isset($arr_cardname[1]) ? $arr_cardname[1] : '';
			}

			$reg['name'] = '';
			$reg['lastname'] = '';
			if ($this->cleanStr($reg['_contact_name']) != '' && $reg['_contact_name'] != null && $reg['_contact_name'] != 'null') {
				$arr__contact_name = explode(' ',$reg['_contact_name']);
				$reg['lastname'] = $arr__contact_name[0];
				$reg['name'] = $arr__contact_name[1];
			}

			$email = $this->isValidEmail($this->cleanStr($reg['email'])) ? $this->cleanStr($reg['email']) : '';
			$phone = $this->cleanStr($reg['phone']);
			$phone = strlen($phone) >= 6 ? $phone : '';

			//echo "<script>console.log('$c: " . json_encode($reg) . "')</script>";
			$c++;
			/*
            error_log(' ======================================== ');
            error_log('DB -> ');
            error_log($hanaInstance['db']);
            error_log('TABLE -> ');
            error_log($param['tableName']);
            */
			$sql_sap = "SELECT CARDCODE FROM ".$hanaInstance['db'].".".$param['tableName']." WHERE CARDCODE='" . $this->preLetterBPartner('C', $reg['cardcode']) . "' LIMIT 1";			
			// echo "<pre>Query $c: ";
			// echo "$sql_sap";
			// echo "</pre>";
            /*
            error_log('CLIENTE -> ');
            error_log($sql_sap);
            error_log(' ======================================== ');
			*/
			$iStatusSQLSAP = odbc_exec($hanaInstance['instance'], $sql_sap);			
			$arrRowResultSAP = odbc_fetch_array($iStatusSQLSAP);
			// echo "<pre>arrRowResultSAP: ";
			// var_dump($arrRowResultSAP);
			// echo "</pre>";            
            if ( $arrRowResultSAP === false ) { //if ( $arrRowResultSAP != false ) { //Este es el problema
				$res[] = array(
					'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
					'cardname' => $this->cleanStr($reg['cardname']),
					'federaltaxid' => $this->cleanStr($reg['federaltaxid']),
					'phone' => $phone,
					'email' => $email,
					'street' => $this->cleanStr($reg['street']),
					'name' => $this->cleanStr($reg['name']),
					'lastname' => $this->cleanStr($reg['lastname']),
					'u_exx_tipopers' => $u_exx_tipopers,
					'u_exx_tipodocu' => $u_exx_tipodocu,
					'u_exx_apellpat' => $u_exx_apellpat,
					'u_exx_apellmat' => $u_exx_apellmat,
					'u_exx_primerno' => $u_exx_primerno,
					'u_exx_segundno' => $u_exx_segundno,
					'estado' => 'P',
					'errormsg' => '',
				);
				++$iCounter;
			}
		}
		if ( $iCounter > 0 ) {// Clientes se insertaran en HANA
			return array(
				'error' => false,
				'tableName' => $param['tableName'],
				'nodeData' => 'bpartner',
				'bpartner' => $res,
				'iCountHana' => $iCounter,
				'sStatus' => 'success',
				'iCountLocal' => ($c - $iCounter),
			);
		} else {// Clientes NO se insertaran en HANA
			return array(
				'error' => false,
				'tableName' => $param['tableName'],
				'nodeData' => 'bpartner',
				'bpartner' => '',
				'sStatus' => 'danger',
				'sMessage' => 'Clientes ya fueron migrados a HANA',
			);
		}
	}

	/**
	 * Empleados
	 */
	public function getEmployee($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT DISTINCT
 TRA.ch_codigo_trabajador AS extempno,
 TRA.ch_nombre1 AS _name1,
 TRA.ch_nombre2 AS _name2,
 TRA.ch_apellido_paterno AS _surname1,
 TRA.ch_apellido_materno AS _surname2
FROM
 pla_ta_trabajadores AS TRA
 JOIN pos_historia_ladosxtrabajador AS PHLTRA USING (ch_codigo_trabajador)
WHERE
 PHLTRA.dt_dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 --AND PHLTRA.ch_sucursal = '".$param['warehouse']."'
 ;";
 		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}

		$c = 0;
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$reg['_name2'] = $this->cleanStr($reg['_name2']);
			$reg['_name1'] = $this->cleanStr($reg['_name1']);
			$name = $reg['_name1'].($reg['_name2'] != '' ? ' '.$reg['_name2'] : '');
			$reg['_surname2'] = $this->cleanStr($reg['_surname2']);
			$lastname = $reg['_name1'].($reg['_surname2'] != '' ? ' '.$reg['_surname2'] : '');
			$res[] = array(
				'extempno' => $this->cleanStr($reg['extempno']),
				'name' => $this->cleanStr($name),
				'lastname' => $this->cleanStr($lastname),
				'estado' => 'P',
				'errormsg' => '',
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'employee',
			'employee' => $res,
			'count' => $c,
		);
	}

	/**
	 * Venta al contado - Cabecera
	 * Se está analizando la posibilidad de incluir:
	 * Las facturas de crédito que no tengan referencia a guías, deben insertarse aqui,
	 * tanto cabecera como detalle
	 */
	public function getInvoiceHeaderSaleCash($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 PT.es || PT.caja || PT.trans AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN LPAD(FIRST(pt.caja),3,'000'::text)
 ELSE FIRST(pt.usr) END AS foliopref,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN TO_CHAR(FIRST(pt.trans),'FM9999999999')
 ELSE '' END AS folionum,

 ROUND(SUM(pt.igv), 2) AS vatsum,
 ROUND(SUM(pt.importe), 2) AS doctotal,

 CASE WHEN FIRST(employe.ch_codigo_trabajador) IS NULL OR FIRST(employe.ch_codigo_trabajador) = '' THEN
  FIRST(pt.cajero)
 ELSE
  FIRST(employe.ch_codigo_trabajador)
 END AS extempno,
 FIRST(pt.caja) AS u_exc_maqreg,
 '01' AS indicador,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN '0'
 ELSE 1 END AS _isfe,
 FIRST(pt.trans) AS transaccion
FROM
 " . $param['pos_trans'] . " AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td='F'
 AND pt.tm='V'
 --AND pt.es = '".$param['warehouse']."'
 --AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;
 		";

		$this->_error_log($param['tableName'].' - getInvoiceHeaderSaleCash: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($reg['_isfe'] == '1') {
				$arr_foliopref = explode('-', $reg['foliopref']);
				$reg['foliopref'] = $arr_foliopref[0];
				$reg['folionum'] = $arr_foliopref[1];
			}

			//No se almacena el detalle porque en el metodo de notas de credito consulta a postrans y estas deben aplicarse el mismo día
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'indicador' => $reg['indicador'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],
				'doccur' => '',

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
			);
		}

		//$client = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'"; //credito, se comenta para mejorarlo

		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 FIRST(ftfc.ch_fac_seriedocumento) AS foliopref,
 FIRST(ftfc.ch_fac_numerodocumento) AS folionum,
 ROUND(FIRST(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(ftfc.nu_fac_valortotal), 2) AS doctotal,

 FIRST(ftfc.nu_fac_impuesto1) AS tax_total,
 FIRST((util_fn_igv()/100)) AS cnf_igv_ocs,
 FIRST(ftfc.nu_fac_valorbruto) AS taxable_operations,
 FIRST(ftfc.nu_fac_valortotal) AS grand_total,
 CASE WHEN FIRST(ftfc.ch_fac_tiporecargo2) IS NULL OR FIRST(ftfc.ch_fac_tiporecargo2) = '' THEN 0 -- NORMAL
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) = 0 THEN 1 -- EXO
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(FIRST(ftfc.nu_fac_descuento1), 0) AS disc,

 '' AS extempno,
 '' AS u_exc_maqreg,
 FIRST(doctype_s.tab_car_03) AS indicador,
 CASE WHEN FIRST(ch_fac_moneda) IN('1','01') THEN '' ELSE 'USD' END AS moneda
FROM
 fac_ta_factura_cabecera ftfc
 JOIN int_tabla_general doctype_s ON(ftfc.ch_fac_tipodocumento = SUBSTRING(TRIM(doctype_s.tab_elemento) for 2 from length(TRIM(doctype_s.tab_elemento))-1) AND doctype_s.tab_tabla ='08' AND doctype_s.tab_elemento != '000000')
 LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)--client valta_complemente
 LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)
 JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND ftfc.ch_fac_tipodocumento = '10'
 AND vtcd.ch_fac_seriedocumento IS NULL
 AND ftfc.nu_fac_recargo3 IN (3, 5)--enviado o anulado
 AND ftfc.ch_liquidacion=''--cai
 AND ftfc.ch_fac_anticipo!='S' --cai 21/01/20
 --AND ftfc.ch_almacen = '".$param['warehouse']."'
 --".$client." se comenta para mejorarlo, cai
GROUP BY 1;";

		$this->_error_log($param['tableName'].' - **getInvoiceHeaderSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		//$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$data = $this->calcAmounts($reg);

			$data['tax_total'] = $this->getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2));
			$data['grand_total'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			$this->invoiceSaleHead[$reg['foliopref']][$reg['folionum']] = $param['tableName'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'indicador' => $reg['indicador'],
				'vatsum' => (float)$data['tax_total'],
				'doctotal' => (float)$data['grand_total'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => 'CREDIT',
				'doccur' => $reg['moneda'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceheadersalecash',
			'invoiceheadersalecash' => $res,
			'count' => $c,
		);
	}

	/**
	 * Venta al contado - Cabecera
	 * Se está analizando la posibilidad de incluir:
	 * Las facturas de crédito que no tengan referencia a guías, deben insertarse aqui,
	 * tanto cabecera como detalle
	 */
	public function getInvoiceHeaderSaleCashWithFechaEmision($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 PT.es || PT.caja || PT.trans AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN LPAD(FIRST(pt.caja),3,'000'::text)
 ELSE FIRST(pt.usr) END AS foliopref,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN TO_CHAR(FIRST(pt.trans),'FM9999999999')
 ELSE '' END AS folionum,

 ROUND(SUM(pt.igv), 2) AS vatsum,
 ROUND(SUM(pt.importe), 2) AS doctotal,

 CASE WHEN FIRST(employe.ch_codigo_trabajador) IS NULL OR FIRST(employe.ch_codigo_trabajador) = '' THEN
  FIRST(pt.cajero)
 ELSE
  FIRST(employe.ch_codigo_trabajador)
 END AS extempno,
 FIRST(pt.caja) AS u_exc_maqreg,
 '01' AS indicador,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN '0'
 ELSE 1 END AS _isfe,
 FIRST(pt.trans) AS transaccion,
 FIRST(pt.fecha) AS u_exc_fechaemi --Requerimiento fecha emision
FROM
 " . $param['pos_trans'] . " AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td='F'
 AND pt.tm='V'
 --AND pt.es = '".$param['warehouse']."'
 --AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;
 		";

		$this->_error_log($param['tableName'].' - getInvoiceHeaderSaleCash: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($reg['_isfe'] == '1') {
				$arr_foliopref = explode('-', $reg['foliopref']);
				$reg['foliopref'] = $arr_foliopref[0];
				$reg['folionum'] = $arr_foliopref[1];
			}

			//No se almacena el detalle porque en el metodo de notas de credito consulta a postrans y estas deben aplicarse el mismo día
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'indicador' => $reg['indicador'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],
				'doccur' => '',

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
				'u_exc_fechaemi' => substr($reg['u_exc_fechaemi'],0,19)
			);
		}

		//$client = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'"; //credito, se comenta para mejorarlo

		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 FIRST(ftfc.ch_fac_seriedocumento) AS foliopref,
 FIRST(ftfc.ch_fac_numerodocumento) AS folionum,
 ROUND(FIRST(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(ftfc.nu_fac_valortotal), 2) AS doctotal,

 FIRST(ftfc.nu_fac_impuesto1) AS tax_total,
 FIRST((util_fn_igv()/100)) AS cnf_igv_ocs,
 FIRST(ftfc.nu_fac_valorbruto) AS taxable_operations,
 FIRST(ftfc.nu_fac_valortotal) AS grand_total,
 CASE WHEN FIRST(ftfc.ch_fac_tiporecargo2) IS NULL OR FIRST(ftfc.ch_fac_tiporecargo2) = '' THEN 0 -- NORMAL
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) = 0 THEN 1 -- EXO
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(FIRST(ftfc.nu_fac_descuento1), 0) AS disc,

 '' AS extempno,
 '' AS u_exc_maqreg,
 FIRST(doctype_s.tab_car_03) AS indicador,
 CASE WHEN FIRST(ch_fac_moneda) IN('1','01') THEN '' ELSE 'USD' END AS moneda,
 FIRST(ftfc.dt_fac_fecha) AS u_exc_fechaemi --Requerimiento fecha emision
FROM
 fac_ta_factura_cabecera ftfc
 JOIN int_tabla_general doctype_s ON(ftfc.ch_fac_tipodocumento = SUBSTRING(TRIM(doctype_s.tab_elemento) for 2 from length(TRIM(doctype_s.tab_elemento))-1) AND doctype_s.tab_tabla ='08' AND doctype_s.tab_elemento != '000000')
 LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)--client valta_complemente
 LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)
 JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND ftfc.ch_fac_tipodocumento = '10'
 AND vtcd.ch_fac_seriedocumento IS NULL
 AND ftfc.nu_fac_recargo3 IN (3, 5)--enviado o anulado
 AND ftfc.ch_liquidacion=''--cai
 AND ftfc.ch_fac_anticipo!='S' --cai 21/01/20
 --AND ftfc.ch_almacen = '".$param['warehouse']."'
 --".$client." se comenta para mejorarlo, cai
GROUP BY 1;";

		$this->_error_log($param['tableName'].' - **getInvoiceHeaderSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		//$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$data = $this->calcAmounts($reg);

			$data['tax_total'] = $this->getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2));
			$data['grand_total'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			$this->invoiceSaleHead[$reg['foliopref']][$reg['folionum']] = $param['tableName'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'indicador' => $reg['indicador'],
				'vatsum' => (float)$data['tax_total'],
				'doctotal' => (float)$data['grand_total'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => 'CREDIT',
				'doccur' => $reg['moneda'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
				'u_exc_fechaemi' => substr($reg['u_exc_fechaemi'],0,19)
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceheadersalecash',
			'invoiceheadersalecash' => $res,
			'count' => $c,
		);
	}

	/**
	 * Venta al contado - Cabecera
	 * Se está analizando la posibilidad de incluir:
	 * Las facturas de crédito que no tengan referencia a guías, deben insertarse aqui,
	 * tanto cabecera como detalle
	 */
	public function getInvoiceHeaderSaleCashDesagregarDocumentosAnuladosTransferenciasGratuitas($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 PT.es || PT.caja || PT.trans AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN LPAD(FIRST(pt.caja),3,'000'::text)
 ELSE FIRST(pt.usr) END AS foliopref,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN TO_CHAR(FIRST(pt.trans),'FM9999999999')
 ELSE '' END AS folionum,

 ROUND(SUM(pt.igv), 2) AS vatsum,
 ROUND(SUM(pt.importe), 2) AS doctotal,

 CASE WHEN FIRST(employe.ch_codigo_trabajador) IS NULL OR FIRST(employe.ch_codigo_trabajador) = '' THEN
  FIRST(pt.cajero)
 ELSE
  FIRST(employe.ch_codigo_trabajador)
 END AS extempno,
 FIRST(pt.caja) AS u_exc_maqreg,
 '01' AS indicador,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN '0'
 ELSE 1 END AS _isfe,
 FIRST(pt.trans) AS transaccion
FROM
 " . $param['pos_trans'] . " AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td='F'
 AND pt.tm='V'
 --AND pt.es = '".$param['warehouse']."'
 --AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;
 		";

		$this->_error_log($param['tableName'].' - getInvoiceHeaderSaleCash: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($reg['_isfe'] == '1') {
				$arr_foliopref = explode('-', $reg['foliopref']);
				$reg['foliopref'] = $arr_foliopref[0];
				$reg['folionum'] = $arr_foliopref[1];
			}

			//No se almacena el detalle porque en el metodo de notas de credito consulta a postrans y estas deben aplicarse el mismo día
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'indicador' => $reg['indicador'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],
				'doccur' => '',

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
			);
		}

		//$client = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'"; //credito, se comenta para mejorarlo

		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 FIRST(ftfc.ch_fac_seriedocumento) AS foliopref,
 FIRST(ftfc.ch_fac_numerodocumento) AS folionum,
 ROUND(FIRST(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(ftfc.nu_fac_valortotal), 2) AS doctotal,

 FIRST(ftfc.nu_fac_impuesto1) AS tax_total,
 FIRST((util_fn_igv()/100)) AS cnf_igv_ocs,
 FIRST(ftfc.nu_fac_valorbruto) AS taxable_operations,
 FIRST(ftfc.nu_fac_valortotal) AS grand_total,
 CASE WHEN FIRST(ftfc.ch_fac_tiporecargo2) IS NULL OR FIRST(ftfc.ch_fac_tiporecargo2) = '' THEN 0 -- NORMAL
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) = 0 THEN 1 -- EXO
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(FIRST(ftfc.nu_fac_descuento1), 0) AS disc,

 '' AS extempno,
 '' AS u_exc_maqreg,
 FIRST(doctype_s.tab_car_03) AS indicador,
 CASE WHEN FIRST(ch_fac_moneda) IN('1','01') THEN '' ELSE 'USD' END AS moneda
FROM
 fac_ta_factura_cabecera ftfc
 JOIN int_tabla_general doctype_s ON(ftfc.ch_fac_tipodocumento = SUBSTRING(TRIM(doctype_s.tab_elemento) for 2 from length(TRIM(doctype_s.tab_elemento))-1) AND doctype_s.tab_tabla ='08' AND doctype_s.tab_elemento != '000000')
 LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)--client valta_complemente
 LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)
 JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND ftfc.ch_fac_tipodocumento = '10'
 AND vtcd.ch_fac_seriedocumento IS NULL
 AND ftfc.nu_fac_recargo3 IN (3, 5)--enviado o anulado
 AND ftfc.ch_liquidacion=''--cai
 AND ftfc.ch_fac_anticipo!='S' --cai 21/01/20
 --AND ftfc.ch_almacen = '".$param['warehouse']."'
 --".$client." se comenta para mejorarlo, cai
GROUP BY 1;";

		$this->_error_log($param['tableName'].' - **getInvoiceHeaderSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		//$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$data = $this->calcAmounts($reg);

			$data['tax_total'] = $this->getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2));
			$data['grand_total'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			$this->invoiceSaleHead[$reg['foliopref']][$reg['folionum']] = $param['tableName'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'indicador' => $reg['indicador'],
				'vatsum' => (float)$data['tax_total'],
				'doctotal' => (float)$data['grand_total'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => 'CREDIT',
				'doccur' => $reg['moneda'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceheadersalecash',
			'invoiceheadersalecash' => $res,
			'count' => $c,
		);
	}

	/**
	 * Venta al contado - Detalle
	 */
	public function getInvoiceDetailSaleCash($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 PT.es || PT.caja || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 SUM(PT.cantidad) AS quantity,
 (SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax']." AS price,--4??
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((NULLIF(SUM(PT.precio),0) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt,
 SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,
 --(FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) * 100) / SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS discprcnt_,
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((NULLIF(SUM(PT.precio),0) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt_,
 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,
 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,
 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 (CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE
  ''
 END) AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(SUM(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv
FROM
".$param['pos_trans']." AS PT
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento,
 ROUND(PT.importe - PT.igv, 4) AS importe_descuento_sigv,
 PT.importe AS importe_descuento_igv
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59' AND
 pt.td IN ('F')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo = 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_sin_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('F')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo != 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)

WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('F')
 --AND PT.es = '".$param['warehouse']."'
 --AND pt.tm IN ('V') --JEL, excluimos notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo
ORDER BY 1;
		";

		$this->_error_log($param['tableName'].' - getInvoiceDetailSaleCash: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			//No se almacena el detalle porque en el metodo de notas de credito consulta a postrans
			// y estas deben aplicarse el mismo día
			//$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$discprcnt = (float)$reg['discprcnt'];

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $reg['noperacion'];
		}

		//$client = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'"; //credito, se deja de utilizar
		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 ftfd.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 ftfd.nu_fac_cantidad AS quantity,
 ROUND((ftfd.nu_fac_precio / ".$param['tax']."), 4) AS price,
 ftfd.nu_fac_precio / ".$param['tax']." AS _price,
 ftfd.nu_fac_descuento1 AS _discprcnt,
 '".$param['sap_tax_code']."' AS taxcode,
 SAPLINEA.sap_codigo AS ocrcode,

 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat,

 '' AS u_exc_dispensador,
 '' AS u_exc_caja,
 '' AS u_exc_manguera,
 ftfc.ch_fac_tiporecargo3 AS u_exc_turno,
 TO_CHAR(ftfc.dt_fac_fecha, 'HH12:MI:SS') AS u_exc_hora,
 '' AS u_exc_placa,
 '' AS u_exc_km,
 0 AS u_exc_bonus,
 '' AS u_exc_nrotarjbonus,
 '' AS u_exc_nrotarjmag--pendiente

 , ftfc.ch_fac_seriedocumento AS _serie
 , ftfc.ch_fac_numerodocumento AS _number
 , ftfc.ch_fac_tiporecargo3 AS _turn,
 ROUND((ftfd.nu_fac_descuento1), 4) AS desc_sinigv,
 ROUND((ftfd.nu_fac_descuento1 * ".$param['tax']."), 4) AS desc_igv
FROM
fac_ta_factura_cabecera ftfc
JOIN fac_ta_factura_detalle ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)--client valta_complemente
LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = ftfc.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS PRO ON (PRO.art_codigo = ftfd.art_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
--AND ftfc.ch_almacen = '".$param['warehouse']."'
AND ftfc.nu_fac_recargo3 IN (3, 5)--enviado o anulado
AND ftfc.ch_fac_tipodocumento = '10'
AND vtcd.ch_fac_seriedocumento IS NULL
AND ftfc.ch_liquidacion=''
--".$client." cai, se comenta para mejorarlo
ORDER BY _serie, _number, _turn;";

		$this->_error_log($param['tableName'].' - **getInvoiceDetailSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		//$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->ticketDetail[$reg['_serie']][$reg['_number']] = $ci;
			$this->invoiceSaleDetail[$reg['_serie']][$reg['_number']][$this->cleanStr($reg['itemcode'])] = array(
				'item' => $ci,
				'noperacion' => $reg['noperacion'],
			);

			$reg['u_exc_turno'] = $reg['u_exc_turno'] == NULL ? '' : $reg['u_exc_turno'];
			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				//'noperacion' => $noperacion.' | '.$reg['_serie'].'-'.$reg['_number'],
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $reg['noperacion'];
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceDetailSaleCash',
			'invoiceDetailSaleCash' => $res,
			'count' => $c,
		);
	}

	/**
	 * Venta al contado - Detalle
	 */
	public function getInvoiceDetailSaleCashExcluimosNC($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 PT.es || PT.caja || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 SUM(PT.cantidad) AS quantity,
 (SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax']." AS price,--4??
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((NULLIF(SUM(PT.precio),0) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt,
 SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,
 --(FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) * 100) / SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS discprcnt_,
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((NULLIF(SUM(PT.precio),0) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt_,
 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,
 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,
 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 (CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE
  ''
 END) AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(SUM(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv
FROM
".$param['pos_trans']." AS PT
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento,
 ROUND(PT.importe - PT.igv, 4) AS importe_descuento_sigv,
 PT.importe AS importe_descuento_igv
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59' AND
 pt.td IN ('F')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo = 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_sin_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('F')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo != 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)

WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('F')
 --AND PT.es = '".$param['warehouse']."'
 AND pt.tm IN ('V') --JEL, excluimos notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo
ORDER BY 1;
		";

		$this->_error_log($param['tableName'].' - getInvoiceDetailSaleCash: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			//No se almacena el detalle porque en el metodo de notas de credito consulta a postrans
			// y estas deben aplicarse el mismo día
			//$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$discprcnt = (float)$reg['discprcnt'];

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $reg['noperacion'];
		}

		//$client = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'"; //credito, se deja de utilizar
		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 ftfd.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 ftfd.nu_fac_cantidad AS quantity,
 ROUND((ftfd.nu_fac_precio / ".$param['tax']."), 4) AS price,
 ftfd.nu_fac_precio / ".$param['tax']." AS _price,
 ftfd.nu_fac_descuento1 AS _discprcnt,
 '".$param['sap_tax_code']."' AS taxcode,
 SAPLINEA.sap_codigo AS ocrcode,

 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat,

 '' AS u_exc_dispensador,
 '' AS u_exc_caja,
 '' AS u_exc_manguera,
 ftfc.ch_fac_tiporecargo3 AS u_exc_turno,
 TO_CHAR(ftfc.dt_fac_fecha, 'HH12:MI:SS') AS u_exc_hora,
 '' AS u_exc_placa,
 '' AS u_exc_km,
 0 AS u_exc_bonus,
 '' AS u_exc_nrotarjbonus,
 '' AS u_exc_nrotarjmag--pendiente

 , ftfc.ch_fac_seriedocumento AS _serie
 , ftfc.ch_fac_numerodocumento AS _number
 , ftfc.ch_fac_tiporecargo3 AS _turn,
 ROUND((ftfd.nu_fac_descuento1), 4) AS desc_sinigv,
 ROUND((ftfd.nu_fac_descuento1 * ".$param['tax']."), 4) AS desc_igv
FROM
fac_ta_factura_cabecera ftfc
JOIN fac_ta_factura_detalle ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)--client valta_complemente
LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = ftfc.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS PRO ON (PRO.art_codigo = ftfd.art_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
--AND ftfc.ch_almacen = '".$param['warehouse']."'
AND ftfc.nu_fac_recargo3 IN (3, 5)--enviado o anulado
AND ftfc.ch_fac_tipodocumento = '10'
AND vtcd.ch_fac_seriedocumento IS NULL
AND ftfc.ch_liquidacion=''
--".$client." cai, se comenta para mejorarlo
ORDER BY _serie, _number, _turn;";

		$this->_error_log($param['tableName'].' - **getInvoiceDetailSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		//$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->ticketDetail[$reg['_serie']][$reg['_number']] = $ci;
			$this->invoiceSaleDetail[$reg['_serie']][$reg['_number']][$this->cleanStr($reg['itemcode'])] = array(
				'item' => $ci,
				'noperacion' => $reg['noperacion'],
			);

			$reg['u_exc_turno'] = $reg['u_exc_turno'] == NULL ? '' : $reg['u_exc_turno'];
			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				//'noperacion' => $noperacion.' | '.$reg['_serie'].'-'.$reg['_number'],
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $reg['noperacion'];
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceDetailSaleCash',
			'invoiceDetailSaleCash' => $res,
			'count' => $c,
		);
	}

	/**
	 * Pago Venta al contado
	 */
	public function getPaymentSaleCash($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 PT.es || PT.caja || PT.trans AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 ROUND(SUM(PT.importe), 2) AS doctotal,--R4
 '' AS moneda, -- DEJAR VACIO
 FIRST(sap_cash_fund.sap_codigo) AS fecuenta,
 FIRST(sap_currency.sap_codigo) AS femoneda,
 1 AS fetc,
 ROUND(SUM(PT.importe), 4) AS femonto,--confirmar si solo es cuando es efectivo
 0 AS fecuentav,
 FIRST(sap_card.sap_codigo) AS tccod,
 '' AS tccuenta,-- DEJAR VACIO
 FIRST(text1) AS tcnumero,
 FIRST(text1) AS tcid,
 TO_CHAR(FIRST(PT.dia), 'MM/YY') AS tcvalido,
 ROUND(SUM(PT.importe), 4) AS tcmonto--confirmar si solo es cuando es tarjeta

 ,FIRST(PT.at) AS _card_type
FROM
".$param['pos_trans']." AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND PT.td ='N'
 AND PT.grupo = 'D'
 --AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN int_tabla_general card ON (trim(pt.at) = substring(card.tab_elemento,6,6) AND card.tab_tabla ='95' AND card.tab_elemento != '000000')
LEFT JOIN sap_mapeo_tabla_detalle sap_card ON (sap_card.id_tipo_tabla = 4 AND sap_card.opencomb_codigo = card.tab_elemento)
LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')

WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('F')
 AND pt.tm = 'V'
 --AND pt.es = '".$param['warehouse']."'
 AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;";


		$this->_error_log($param['tableName'].' - getPaymentSaleCash: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$paymentType = 0;
			if ($reg['_card_type'] != NULL AND $reg['_card_type'] != '') {
				//tarjeta de credito
				$paymentType = 1;
			}

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacion'],
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'paymentSaleCash',
			'paymentSaleCash' => $res,
			'count' => $c,
		);
	}

	/**
	 * Pago Venta al contado
	 */
	public function getPaymentSaleCashExcluimosDocumentosOriginales($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 PT.es || PT.caja || PT.trans AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 ROUND(SUM(PT.importe), 2) AS doctotal,--R4
 '' AS moneda, -- DEJAR VACIO
 FIRST(sap_cash_fund.sap_codigo) AS fecuenta,
 FIRST(sap_currency.sap_codigo) AS femoneda,
 1 AS fetc,
 ROUND(SUM(PT.importe), 4) AS femonto,--confirmar si solo es cuando es efectivo
 0 AS fecuentav,
 FIRST(sap_card.sap_codigo) AS tccod,
 '' AS tccuenta,-- DEJAR VACIO
 FIRST(text1) AS tcnumero,
 FIRST(text1) AS tcid,
 TO_CHAR(FIRST(PT.dia), 'MM/YY') AS tcvalido,
 ROUND(SUM(PT.importe), 4) AS tcmonto--confirmar si solo es cuando es tarjeta

 ,FIRST(PT.at) AS _card_type
FROM
".$param['pos_trans']." AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND PT.td ='N'
 AND PT.grupo = 'D'
 --AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN int_tabla_general card ON (trim(pt.at) = substring(card.tab_elemento,6,6) AND card.tab_tabla ='95' AND card.tab_elemento != '000000')
LEFT JOIN sap_mapeo_tabla_detalle sap_card ON (sap_card.id_tipo_tabla = 4 AND sap_card.opencomb_codigo = card.tab_elemento)
LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')

WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('F')
 AND pt.tm = 'V'
 --AND pt.es = '".$param['warehouse']."'
 AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;";


		$this->_error_log($param['tableName'].' - getPaymentSaleCash: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$paymentType = 0;
			if ($reg['_card_type'] != NULL AND $reg['_card_type'] != '') {
				//tarjeta de credito
				$paymentType = 1;
			}

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacion'],
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'paymentSaleCash',
			'paymentSaleCash' => $res,
			'count' => $c,
		);
	}

	/**
	 * Guia efectivo - Cabecera ***pendiente
	 */
	public function getShipmentHeaderSaleEffective($param) {
		global $sqlca;

		$param['_tax'] = $param['tax'];
		if ($param['_tax'] > 0) {
			$param['_tax'] = ($param['tax'] - 1);
		}

		$res = array();
		$sql = "
SELECT
vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || --vtc.ch_documento AS noperacion, wilbert y cai
substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento)) AS noperacion,
FIRST(client.cli_codigo) AS cardcode,
FIRST(vtc.dt_fecha) AS docdate,
'00'||FIRST(vtc.ch_caja) AS foliopref,
--FIRST(vtc.ch_documento) AS folionum, --CAI
FIRST(substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento))) AS folionum,

ROUND(FIRST(vtc.nu_importe) - (FIRST(vtc.nu_importe) / (1 + ".$param['_tax'].")), 2) AS vatsum,--probar
ROUND(FIRST(vtc.nu_importe), 2) AS doctotal,

COALESCE(FIRST(employe.ch_codigo_trabajador), '0') AS extempno,
FIRST(vtc.ch_caja) AS u_exc_maqreg
FROM
val_ta_cabecera vtc
JOIN inv_ta_almacenes alm ON (vtc.ch_sucursal = alm.ch_almacen)
JOIN int_clientes client ON (vtc.ch_cliente = client.cli_codigo)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = vtc.dt_fecha AND employe.ch_posturno::CHAR = vtc.ch_turno AND employe.ch_lado = vtc.ch_lado)
WHERE
 vtc.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 ".$param['client']."
GROUP BY
 1;";

		$this->_error_log($param['tableName'].' - getShipmentHeaderSaleEffective: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'shipmentHeaderSaleEffective',
			'shipmentHeaderSaleEffective' => $res,
			'count' => $c,
		);
	}

	/**
	 * Guia efectivo - Detalle
	 */
	public function getShipmentDetailSaleEffective($param) {
		global $sqlca;

		$tmpDocument = array();
		$res = array();

		/**
		 * Obetener vales automaticos
		 */
		$sql = "
SELECT
 PT.es::INTEGER || TO_CHAR(FIRST(PT.dia), 'DDMMYY') || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 SUM(PT.cantidad) AS quantity,
 ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax'].", 4) AS price,
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt,
 SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,
 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,
 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,
 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE '' END AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(SUM(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 PT.tarjeta AS u_exc_nrotarjmag,--pendiente
 FIRST(PT.trans) AS item,
 'postrans' AS source,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv
FROM
 ".$param['pos_trans']." AS PT
 --LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
 JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
 JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
 JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
 JOIN int_clientes client ON (pt.cuenta = client.cli_codigo)
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento,
  ROUND(PT.importe - PT.igv, 4) AS importe_descuento_sigv,
  PT.importe AS importe_descuento_igv
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND pt.td IN ('N')
  AND pt.tm not IN ('A')
  --AND PT.td ='N'
  ---AND PT.tipo = 'M'
  AND PT.grupo = 'D'
  ---AND PT.es = '".$param['warehouse']."'
 ) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_sin_descuento
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND pt.td IN ('N')
  --AND PT.td ='N'
  ---AND PT.tipo = 'M'
  AND PT.grupo != 'D'
  ---AND PT.es = '".$param['warehouse']."'
 ) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)

WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('N')
 --AND PT.es = '".$param['warehouse']."'
 ".$param['client']."
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo,
 PT.tarjeta--pendiente0
ORDER BY 1;
		";

		$this->_error_log($param['tableName'].' [A] - getShipmentDetailSaleEffective'.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$tmpDocument[$reg['noperacion']][$this->cleanStr($reg['itemcode'])] = 1;

			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => 1,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nrotarjmag' => $reg['u_exc_nrotarjmag'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
		}

		/**
		 * Obetener vales manuales
		 */
		$sql = "
SELECT
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || vtc.ch_documento AS noperacion,
 vtd.ch_articulo AS itemcode,
 SAPALMA.sap_codigo AS whscode,--SAPALMA.sap_codigo AS whscode,
 vtd.nu_cantidad AS quantity,
 ROUND(vtd.nu_precio_unitario / ".$param['tax'].", 4) AS price,--sin impuesto!
 '".$param['sap_tax_code']."' AS taxcode,
 ROUND(vtd.nu_precio_unitario, 4) AS _price,
 CASE WHEN vtd.nu_precio_unitario > 0 THEN
  vtd.nu_importe - ROUND((vtd.nu_precio_unitario * vtd.nu_cantidad), 3)
 ELSE
  0
 END AS _discprcnt,
 ROUND(0.0, 4) AS discprcnt,
 SAPLINEA.sap_codigo AS ocrcode,
 SAPCC.sap_codigo AS ocrcode2,
 ROUND(PT.precio, 2) AS priceafvat,
 vtc.ch_lado AS u_exc_dispensador,
 vtc.ch_caja AS u_exc_caja,
 --SURTIDOR.nu_manguera::TEXT AS u_exc_manguera,
 CASE WHEN vtc.ch_lado != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = vtc.ch_lado::INTEGER AND SURTIDOR.ch_codigocombustible = vtd.ch_articulo)::TEXT
 ELSE
  ''
 END AS u_exc_manguera,
 vtc.ch_turno AS u_exc_turno,
 TO_CHAR(vtc.dt_fecha, 'HH12:MI:SS') AS u_exc_hora,
 vtc.ch_placa AS u_exc_placa,
 vtc.nu_odometro AS u_exc_km,
 TRUNC(vtd.nu_importe / " . $param['factor_bonus'] . ") AS u_exc_bonus,
 '' AS u_exc_nrotarjbonus,--pendiente
 '' AS u_exc_nrotarjmag,--pendiente
 vtc.ch_documento AS item,
 'val_ta' AS source,
 ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0)) AS desc_sinigv,
 ABS(COALESCE(PTDSCT.importe_descuento_igv, 0)) AS desc_igv
FROM
 val_ta_detalle vtd
 JOIN val_ta_cabecera vtc ON (vtd.ch_sucursal = vtc.ch_sucursal AND vtd.dt_fecha = vtc.dt_fecha AND vtd.ch_documento = vtc.ch_documento)
 --LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (vtc.ch_lado::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = vtd.ch_articulo)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = vtc.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
 JOIN int_clientes client ON (vtc.ch_cliente = client.cli_codigo)
 JOIN int_articulos AS art ON (art.art_codigo = vtd.ch_articulo)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = vtc.ch_sucursal AND SAPALMA.id_tipo_tabla = 2)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = vtc.dt_fecha AND employe.ch_posturno::CHAR = vtc.ch_turno AND employe.ch_lado = vtc.ch_lado)
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento,
  ROUND(PT.importe - PT.igv, 4) AS importe_descuento_sigv,
  PT.importe AS importe_descuento_igv
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND pt.td IN ('N')
  --AND PT.td ='N'
  ---AND PT.tipo = 'M'
  AND PT.grupo = 'D'
  ---AND PT.es = '".$param['warehouse']."'
 ) AS PTDSCT ON (PTDSCT.es = vtc.ch_sucursal AND PTDSCT.caja = vtc.ch_caja AND (PTDSCT.trans::CHARACTER = vtc.ch_documento OR PTDSCT.caja||'-'||PTDSCT.trans = vtc.ch_documento))
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND pt.td IN ('N')
  --AND PT.td ='N'
  ---AND PT.tipo = 'M'
  AND PT.grupo != 'D'
  ---AND PT.es = '".$param['warehouse']."'
 ) AS PT ON (PT.es = vtc.ch_sucursal AND PT.caja = vtc.ch_caja AND (PT.trans::CHARACTER = vtc.ch_documento OR PT.caja||'-'||PT.trans = vtc.ch_documento))
WHERE
 vtc.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND vtd.nu_precio_unitario >= 0
 ".$param['client'].";
";

		$this->_error_log($param['tableName'].' [M] - getShipmentDetailSaleEffective'.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			if (!isset($tmpDocument[$reg['noperacion']][$this->cleanStr($reg['itemcode'])])) {
				$res[] = array(
					'noperacion' => $reg['noperacion'],
					'item' => 1,
					'itemcode' => $this->cleanStr($reg['itemcode']),
					'whscode' => $reg['whscode'],
					'quantity' => (float)$reg['quantity'],
					'price' => (float)$reg['price'],
					'taxcode' => $reg['taxcode'],
					'discprcnt' => (float)$discprcnt,
					'ocrcode' => $reg['ocrcode'],
					'ocrcode2' => $reg['ocrcode2'],
					'priceafvat' => (float)$reg['priceafvat'],
					'u_exc_dispensador' => $reg['u_exc_dispensador'],
					'u_exc_caja' => $reg['u_exc_caja'],
					'u_exc_manguera' => $reg['u_exc_manguera'],
					'u_exc_turno' => $reg['u_exc_turno'],
					'u_exc_hora' => $reg['u_exc_hora'],
					'u_exc_placa' => $reg['u_exc_placa'],
					'u_exc_km' => $reg['u_exc_km'],
					'u_exc_bonus' => $reg['u_exc_bonus'],
					'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
					'u_exc_nrotarjmag' => $reg['u_exc_nrotarjmag'],
					'desc_sinigv' => (float)$reg['desc_sinigv'],
					'desc_igv' => (float)$reg['desc_igv'],
				);
			} else {
				$c--;
			}
		}

		unset($tmpDocument);

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'shipmentDetailSaleEffective',
			'shipmentDetailSaleEffective' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura efectivo - Cabecera
	 solo muestra las facturas liquidadas
	 */
	public function getInvoiceHeaderSaleEffective($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento||ftfc.cli_codigo AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 FIRST(ftfc.ch_fac_seriedocumento) AS foliopref,
 FIRST(ftfc.ch_fac_numerodocumento) AS folionum,
 ROUND(FIRST(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(ftfc.nu_fac_valortotal), 2) AS doctotal,

 FIRST(ftfc.nu_fac_impuesto1) AS tax_total,
 FIRST((util_fn_igv()/100)) AS cnf_igv_ocs,
 FIRST(ftfc.nu_fac_valorbruto) AS taxable_operations,
 FIRST(ftfc.nu_fac_valortotal) AS grand_total,
 CASE WHEN FIRST(ftfc.ch_fac_tiporecargo2) IS NULL OR FIRST(ftfc.ch_fac_tiporecargo2) = '' THEN 0 -- NORMAL
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) = 0 THEN 1 -- EXO
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(FIRST(ftfc.nu_fac_descuento1), 0) AS disc,

 '' AS extempno,
 '' AS u_exc_maqreg

FROM
fac_ta_factura_cabecera ftfc
LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)
JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
AND ftfc.ch_fac_tipodocumento = '10'
AND ftfc.nu_fac_recargo3 IN (3, 5)
and ftfc.ch_liquidacion != '' --cai
--AND ftfc.ch_almacen = '".$param['warehouse']."'
".$param['client']."
GROUP BY 1;";

		$this->_error_log($param['tableName'].' - getInvoiceHeaderSaleEffective: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$data = $this->calcAmounts($reg);

			$data['tax_total'] = $this->getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2));
			$data['grand_total'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			$this->invoiceSaleHead[$reg['foliopref']][$reg['folionum']] = $param['tableName'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'vatsum' => (float)$data['tax_total'],
				'doctotal' => (float)$data['grand_total'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceHeaderSaleEffective',
			'invoiceHeaderSaleEffective' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura efectivo - Detalle
	 solo muestra las facturas liquidadas
	 */
	public function getInvoiceDetailSaleEffective($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento||ftfc.cli_codigo AS noperacion,
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || vtc.ch_documento AS itemref,

 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat

 , ftfd.ch_fac_seriedocumento AS serie_
 , ftfd.ch_fac_numerodocumento AS number_
 , ftfd.art_codigo AS product_
FROM
fac_ta_factura_cabecera ftfc
JOIN fac_ta_factura_detalle ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)

JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)

JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
AND ftfc.ch_fac_tipodocumento = '10'
and ftfc.ch_liquidacion != '' --cai
--AND ftfc.ch_almacen = '".$param['warehouse']."'
".$param['client']."
ORDER BY 1;";

		$this->_error_log($param['tableName'].' - getInvoiceDetailSaleEffective: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->invoiceSaleDetail[$reg['serie_']][$reg['number_']][$this->cleanStr($reg['product_'])] = array(
				'item' => $ci,
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemref' => 1,
				'noperacionref' => $reg['itemref'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceDetailSaleEffective',
			'invoiceDetailSaleEffective' => $res,
			'count' => $c,
		);
	}

	/**
	 * Pago efectivo
	 * Verificar, para pago en efectivo debe ser el mimo valor de la factura (fac_ta_factura_cabecera)
	 * extraer todos los datos necesarios(forma de pago, etc) de ahí
	 */
	public function getPaymentSaleEffective($param) {
		global $sqlca;

		$res = array();

		$sql = "SELECT
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || --vtc.ch_documento AS noperacionpe, CAI
 substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento)) AS noperacionpe,
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || --vtc.ch_documento AS noperacion, CAI
 substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento)) AS noperacion,
 client.cli_codigo AS cardcode,
 vtd.dt_fecha AS docdate,
 vtc.nu_importe AS doctotal,
 '' AS moneda,
 ftfc.ch_fac_forma_pago AS _type_payment_id,
 sap_cash_fund.sap_codigo AS fecuenta,
 sap_currency.sap_codigo AS femoneda,
 1 AS fetc,
 vtc.nu_importe AS femonto,
 0 AS fecuentav,
 '' AS tccod,
 '' AS tccuenta,
 '' AS tcnumero,
 '' AS tcid,
 TO_CHAR(vtd.dt_fecha,'DD/MM') AS tcvalido,
 vtc.nu_importe AS tcmonto,
 '' AS bcuenta,
 '' AS breferencia,
 '' AS bfecha,
 '' AS bmonto
FROM
val_ta_detalle vtd
JOIN val_ta_cabecera vtc ON (vtd.ch_sucursal = vtc.ch_sucursal AND vtd.dt_fecha = vtc.dt_fecha AND vtd.ch_documento = vtc.ch_documento)
 INNER JOIN int_clientes client ON (vtc.ch_cliente = client.cli_codigo)
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
 JOIN val_ta_complemento_documento vtcd ON (vtcd.ch_numeval = vtc.ch_documento)
 JOIN fac_ta_factura_cabecera ftfc ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
WHERE
 vtc.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND client.cli_ndespacho_efectivo = '1' AND client.cli_anticipo = 'N'
;";


		$this->_error_log($param['tableName'].' - getPaymentSaleEffective: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			//0: efectivo, 1: tarjeta de credito, 2: banco
			$paymentType = 0;
			if ($reg['_type_payment_id'] == '02') {
				//tarjeta de credito
				$paymentType = 1;
			}

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacionpe'],
				'noperacion' => $reg['noperacion'],//debe hacer referencia con la cabecera de la guia
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				'bcuenta' => $reg['bcuenta'],
				'breferencia' => $reg['breferencia'],
				'bfecha' => $reg['bfecha'],
				'bmonto' => $reg['bmonto'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'paymentSaleEffective',
			'paymentSaleEffective' => $res,
			'count' => $c,
		);
	}






	/**
	 * Guia credito - Cabecera
	 */
	public function getShipmentHeaderSaleCredit($param) {
		global $sqlca;

		$param['_tax'] = $param['tax'];
		if ($param['_tax'] > 0) {
			$param['_tax'] = ($param['tax'] - 1);
		}

		$res = array();
		$sql = "
SELECT
vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') ||  --vtc.ch_documento AS noperacion, --cai
substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento)) AS noperacion,
FIRST(client.cli_codigo) AS cardcode,
FIRST(vtc.dt_fecha) AS docdate,
'00'||FIRST(vtc.ch_caja) AS foliopref,
--FIRST(vtc.ch_documento) AS folionum, --CAI
FIRST(substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento))) AS folionum,

ROUND(FIRST(vtc.nu_importe) - (FIRST(vtc.nu_importe) / (1 + ".$param['_tax'].")), 2) AS vatsum,--probar
ROUND(FIRST(vtc.nu_importe), 2) AS doctotal,

CASE WHEN FIRST(employe.ch_codigo_trabajador) IS NULL OR FIRST(employe.ch_codigo_trabajador) = '' THEN
 (SELECT ch_codigo_trabajador FROM pos_historia_ladosxtrabajador WHERE dt_dia = FIRST(vtc.dt_fecha) AND ch_posturno::CHAR = FIRST(vtc.ch_turno) AND ch_lado = FIRST(vtc.ch_caja))
ELSE
 FIRST(employe.ch_codigo_trabajador)
END AS extempno,--

FIRST(vtc.ch_caja) AS u_exc_maqreg
FROM
val_ta_cabecera vtc
JOIN inv_ta_almacenes alm ON (vtc.ch_sucursal = alm.ch_almacen)
JOIN int_clientes client ON (vtc.ch_cliente = client.cli_codigo)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = vtc.dt_fecha AND employe.ch_posturno::CHAR = vtc.ch_turno AND employe.ch_lado = vtc.ch_lado)
WHERE
 vtc.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 ".$param['client']."
GROUP BY 1;";

		$this->_error_log($param['tableName'].' - getShipmentHeaderSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			//$reg['noperacion'] = str_replace('-', '', $reg['noperacion']);

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'shipmentHeaderSaleCredit',
			'shipmentHeaderSaleCredit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Guia credito - Cabecera
	 */
	public function getShipmentHeaderSaleCreditWithFechaEmision($param) {
		global $sqlca;

		$param['_tax'] = $param['tax'];
		if ($param['_tax'] > 0) {
			$param['_tax'] = ($param['tax'] - 1);
		}

		$res = array();
		$sql = "
SELECT
vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') ||  --vtc.ch_documento AS noperacion, --cai
substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento)) AS noperacion,
FIRST(client.cli_codigo) AS cardcode,
FIRST(vtc.dt_fecha) AS docdate,
'00'||FIRST(vtc.ch_caja) AS foliopref,
--FIRST(vtc.ch_documento) AS folionum, --CAI
FIRST(substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento))) AS folionum,

ROUND(FIRST(vtc.nu_importe) - (FIRST(vtc.nu_importe) / (1 + ".$param['_tax'].")), 2) AS vatsum,--probar
ROUND(FIRST(vtc.nu_importe), 2) AS doctotal,

CASE WHEN FIRST(employe.ch_codigo_trabajador) IS NULL OR FIRST(employe.ch_codigo_trabajador) = '' THEN
 (SELECT ch_codigo_trabajador FROM pos_historia_ladosxtrabajador WHERE dt_dia = FIRST(vtc.dt_fecha) AND ch_posturno::CHAR = FIRST(vtc.ch_turno) AND ch_lado = FIRST(vtc.ch_caja))
ELSE
 FIRST(employe.ch_codigo_trabajador)
END AS extempno,--

FIRST(vtc.ch_caja) AS u_exc_maqreg,
(SELECT pt.fecha FROM ".$param['pos_trans']." pt WHERE CAST(pt.trans as text) = FIRST(substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento))) LIMIT 1) as u_exc_fechaemi
FROM
val_ta_cabecera vtc
JOIN inv_ta_almacenes alm ON (vtc.ch_sucursal = alm.ch_almacen)
JOIN int_clientes client ON (vtc.ch_cliente = client.cli_codigo)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = vtc.dt_fecha AND employe.ch_posturno::CHAR = vtc.ch_turno AND employe.ch_lado = vtc.ch_lado)
WHERE
 vtc.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 ".$param['client']."
GROUP BY 1;";

		$this->_error_log($param['tableName'].' - getShipmentHeaderSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			//$reg['noperacion'] = str_replace('-', '', $reg['noperacion']);

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
				'u_exc_fechaemi' => substr($reg['u_exc_fechaemi'],0,19)
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'shipmentHeaderSaleCredit',
			'shipmentHeaderSaleCredit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Guia credito - Detalle
	 */
	public function getShipmentDetailSaleCredit($param) {
		global $sqlca;
		$tmpDocument = array();

		$res = array();

		/**
		 * Obetener vales automaticos
		 */
		$sql = "
SELECT
 PT.es::INTEGER || TO_CHAR(FIRST(PT.dia), 'DDMMYY') || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 SUM(PT.cantidad) AS quantity,

 ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax'].", 4) AS price,

 CASE WHEN SUM(PT.cantidad) > 0 THEN
 	ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4)
 ELSE
 	0
 END AS discprcnt,

 SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,

 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,

 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,

 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE '' END AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(SUM(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 PT.tarjeta AS u_exc_nrotarjmag,--pendiente

 FIRST(PT.trans) AS item
 , 'postrans' AS source,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv
FROM
".$param['pos_trans']." AS PT
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
JOIN int_clientes client ON (pt.cuenta = client.cli_codigo)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento,
 ROUND(PT.importe - PT.igv, 4) AS importe_descuento_sigv,
 PT.importe AS importe_descuento_igv
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('N')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo = 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_sin_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('N')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo != 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)

WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('N')
 --AND PT.es = '".$param['warehouse']."'
 ".$param['client']."
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo,
 PT.tarjeta--pendiente1
ORDER BY 1;
		";

		$this->_error_log($param['tableName'].' [A] - getShipmentDetailSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$tmpDocument[$reg['noperacion']][$this->cleanStr($reg['itemcode'])] = 1;

			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => 1,//(int)$reg['item'],
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nrotarjmag' => $reg['u_exc_nrotarjmag'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
		}


		/**
		 * Obetener vales manuales
		 */
		$sql = "
SELECT
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || vtc.ch_documento AS noperacion,
 vtd.ch_articulo AS itemcode,
 SAPALMA.sap_codigo AS whscode,--SAPALMA.sap_codigo AS whscode,
 vtd.nu_cantidad AS quantity,
 ROUND(vtd.nu_precio_unitario / ".$param['tax'].", 4) AS price,--sin impuesto!
 '".$param['sap_tax_code']."' AS taxcode,

 ROUND(vtd.nu_precio_unitario, 4) AS _price,
 CASE WHEN vtd.nu_precio_unitario > 0 THEN
  vtd.nu_importe - ROUND((vtd.nu_precio_unitario * vtd.nu_cantidad), 3)
 ELSE 0 END AS _discprcnt,

 ROUND(0.0, 4) AS discprcnt,
 SAPLINEA.sap_codigo AS ocrcode,

 SAPCC.sap_codigo AS ocrcode2,
 --ROUND(vtd.nu_precio_unitario, 2) AS priceafvat, 10/07/2018
 ROUND(PT.precio, 2) AS priceafvat,

 vtc.ch_lado AS u_exc_dispensador,
 vtc.ch_caja AS u_exc_caja,
 --SURTIDOR.nu_manguera::TEXT AS u_exc_manguera,
 CASE WHEN vtc.ch_lado != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = vtc.ch_lado::INTEGER AND SURTIDOR.ch_codigocombustible = vtd.ch_articulo)::TEXT
 ELSE '' END AS u_exc_manguera,
 vtc.ch_turno AS u_exc_turno,
 TO_CHAR(vtc.dt_fecha, 'HH12:MI:SS') AS u_exc_hora,
 vtc.ch_placa AS u_exc_placa,
 vtc.nu_odometro AS u_exc_km,
 TRUNC(vtd.nu_importe / ".$param['factor_bonus'].") AS u_exc_bonus,
 '' AS u_exc_nrotarjbonus,--pendiente
 '' AS u_exc_nrotarjmag--pendiente

 , vtc.ch_documento AS item
 , 'val_ta' AS source,
 ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0)) AS desc_sinigv,
 ABS(COALESCE(PTDSCT.importe_descuento_igv, 0)) AS desc_igv
FROM
val_ta_detalle vtd
JOIN val_ta_cabecera vtc ON (vtd.ch_sucursal = vtc.ch_sucursal AND vtd.dt_fecha = vtc.dt_fecha AND vtd.ch_documento = vtc.ch_documento)
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (vtc.ch_lado::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = vtd.ch_articulo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = vtc.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
JOIN int_clientes client ON (vtc.ch_cliente = client.cli_codigo)

JOIN int_articulos AS art ON (art.art_codigo = vtd.ch_articulo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar

LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = vtc.ch_sucursal AND SAPALMA.id_tipo_tabla = 2)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = vtc.dt_fecha AND employe.ch_posturno::CHAR = vtc.ch_turno AND employe.ch_lado = vtc.ch_lado)

LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento,
  ROUND(PT.importe - PT.igv, 4) AS importe_descuento_sigv,
  PT.importe AS importe_descuento_igv
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND pt.td IN ('N')
  --AND PT.td ='N'
  ---AND PT.tipo = 'M'
  AND PT.grupo = 'D'
  ---AND PT.es = '".$param['warehouse']."'
 ) AS PTDSCT ON (PTDSCT.es = vtc.ch_sucursal AND PTDSCT.caja = vtc.ch_caja AND (PTDSCT.trans::CHARACTER = vtc.ch_documento OR PTDSCT.caja||'-'||PTDSCT.trans = vtc.ch_documento))
LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND pt.td IN ('N')
  --AND PT.td ='N'
  ---AND PT.tipo = 'M'
  AND PT.grupo != 'D'
  ---AND PT.es = '".$param['warehouse']."'
 ) AS PT ON (PT.es = vtc.ch_sucursal AND PT.caja = vtc.ch_caja AND (PT.trans::CHARACTER = vtc.ch_documento OR PT.caja||'-'||PT.trans = vtc.ch_documento))
WHERE
 vtc.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND vtd.nu_precio_unitario >= 0
 ".$param['client']."
;";

		$this->_error_log($param['tableName'].' [M] - getShipmentDetailSaleCredit '.$sql);
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			if (!isset($tmpDocument[$reg['noperacion']][$this->cleanStr($reg['itemcode'])])) {
				$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
				$res[] = array(
					'noperacion' => $reg['noperacion'],
					'item' => 1,//(int)$reg['item'],
					'itemcode' => $this->cleanStr($reg['itemcode']),
					'whscode' => $reg['whscode'],
					'quantity' => (float)$reg['quantity'],
					'price' => (float)$reg['price'],
					'taxcode' => $reg['taxcode'],
					'discprcnt' => (float)$discprcnt,
					'ocrcode' => $reg['ocrcode'],
					'ocrcode2' => $reg['ocrcode2'],
					'priceafvat' => (float)$reg['priceafvat'],
					'u_exc_dispensador' => $reg['u_exc_dispensador'],
					'u_exc_caja' => $reg['u_exc_caja'],
					'u_exc_manguera' => $reg['u_exc_manguera'],
					'u_exc_turno' => $reg['u_exc_turno'],
					'u_exc_hora' => $reg['u_exc_hora'],
					'u_exc_placa' => $reg['u_exc_placa'],
					'u_exc_km' => $reg['u_exc_km'],
					'u_exc_bonus' => $reg['u_exc_bonus'],
					'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
					'u_exc_nrotarjmag' => $reg['u_exc_nrotarjmag'],
					'desc_sinigv' => (float)$reg['desc_sinigv'],
					'desc_igv' => (float)$reg['desc_igv'],
				);
			} else {
				$c--;
			}
		}
		unset($tmpDocument);

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'shipmentDetailSaleCredit',
			'shipmentDetailSaleCredit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura credito - Cabecera
	 */
	public function getInvoiceHeaderSaleCredit($param) {
		global $sqlca;

		//left join para encontrar las facturas que se les soltaron los vales
		$res = array();
		$sql = "SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 FIRST(ftfc.ch_fac_seriedocumento) AS foliopref,
 FIRST(ftfc.ch_fac_numerodocumento) AS folionum,
 ROUND(FIRST(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(ftfc.nu_fac_valortotal), 2) AS doctotal,

 FIRST(ftfc.nu_fac_impuesto1) AS tax_total,
 FIRST((util_fn_igv()/100)) AS cnf_igv_ocs,
 FIRST(ftfc.nu_fac_valorbruto) AS taxable_operations,
 FIRST(ftfc.nu_fac_valortotal) AS grand_total,
 CASE WHEN FIRST(ftfc.ch_fac_tiporecargo2) IS NULL OR FIRST(ftfc.ch_fac_tiporecargo2) = '' THEN 0 -- NORMAL
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) = 0 THEN 1 -- EXO
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(FIRST(ftfc.nu_fac_descuento1), 0) AS disc,

 '' AS extempno,
 '' AS u_exc_maqreg,
 FIRST(doctype_s.tab_car_03) AS indicator
FROM
fac_ta_factura_cabecera ftfc
JOIN int_tabla_general doctype_s ON(ftfc.ch_fac_tipodocumento = SUBSTRING(TRIM(doctype_s.tab_elemento) for 2 from length(TRIM(doctype_s.tab_elemento))-1) AND doctype_s.tab_tabla ='08' AND doctype_s.tab_elemento != '000000')
JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)--client valta_complemente
JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)
JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
AND ftfc.nu_fac_recargo3 IN (3, 5)
AND ftfc.ch_fac_tipodocumento = '10'
--AND ftfc.ch_almacen = '".$param['warehouse']."'
".$param['client']."
GROUP BY 1;";

		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		$this->_error_log($param['tableName'].' - getInvoiceHeaderSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$data = $this->calcAmounts($reg);

			$data['tax_total'] = $this->getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2));
			$data['grand_total'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			$this->invoiceSaleHead[$reg['foliopref']][$reg['folionum']] = $param['tableName'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'indicator' => $reg['indicator'],
				'vatsum' => (float)$data['tax_total'],
				'doctotal' => (float)$data['grand_total'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceHeaderSaleCredit',
			'invoiceHeaderSaleCredit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura credito - Detalle
	 */
	public function getInvoiceDetailSaleCredit($param) {
		global $sqlca;

		//vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || vtc.ch_documento AS noperacion,
		$res = array();
		$sql = "
SELECT
 ftfd.ch_fac_tipodocumento||ftfd.ch_fac_seriedocumento||ftfd.ch_fac_numerodocumento AS noperacion,
 vtcd.ch_numeval AS itemref,
 vtcd.ch_sucursal::INTEGER || TO_CHAR(vtcd.dt_fecha, 'DDMMYY') || --vtcd.ch_numeval AS noperacionref, -- CAI
 substr(vtcd.ch_numeval, position('-' in vtcd.ch_numeval)+1, char_length (vtcd.ch_numeval)+1 - position('-' in vtcd.ch_numeval)) AS noperacionref,
 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat,
 ftfd.ch_fac_seriedocumento AS serie_,
 ftfd.ch_fac_numerodocumento AS number_,
 ftfd.art_codigo AS product_
FROM
 val_ta_complemento_documento AS vtcd
 JOIN fac_ta_factura_cabecera AS ftfc
  ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento AND ftfc.cli_codigo = vtcd.ch_cliente)
 JOIN fac_ta_factura_detalle AS ftfd
  ON(ftfd.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfd.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfd.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento AND ftfd.cli_codigo = vtcd.ch_cliente AND ftfd.art_codigo = vtcd.art_codigo)
 JOIN inv_ta_almacenes AS ALMA
  ON (ALMA.ch_almacen = vtcd.ch_sucursal)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC
  ON (SAPCC.opencomb_codigo = ALMA.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
 JOIN int_clientes client
  ON (vtcd.ch_cliente = client.cli_codigo)
WHERE
	vtcd.fecha_liquidacion BETWEEN '" . $param['initial_date'] . "' AND '" . $param['initial_date'] . "'
	AND ftfc.nu_fac_recargo3 IN (3, 5)
	AND ftfc.ch_fac_tipodocumento = '10'
	" . $param['client'] . "
ORDER BY 1;
		";

		
/*
		$sql = "
SELECT
 ftfd.ch_fac_tipodocumento||ftfd.ch_fac_seriedocumento||ftfd.ch_fac_numerodocumento AS noperacion,
 vtc.ch_documento AS itemref,
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || vtc.ch_documento AS noperacionref,

 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat

 , ftfd.ch_fac_seriedocumento AS serie_
 , ftfd.ch_fac_numerodocumento AS number_
 , ftfd.art_codigo AS product_
FROM
fac_ta_factura_cabecera ftfc
JOIN fac_ta_factura_detalle ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)

JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)

JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
AND ftfc.nu_fac_recargo3 IN (3, 5)
AND ftfc.ch_fac_tipodocumento = '10'
--AND ftfc.ch_almacen = '".$param['warehouse']."'
".$param['client']."
ORDER BY 1;";
*/

		$this->_error_log($param['tableName'].' - getInvoiceDetailSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->_error_log('$this->invoiceSaleDetail['.$reg['serie_'].']['.$reg['number_'].']['.$this->cleanStr($reg['product_']).'] = item: '.$ci.', noperacion: '.$reg['noperacion']);

			$this->invoiceSaleDetail[$reg['serie_']][$reg['number_']][$this->cleanStr($reg['product_'])] = array(
				'item' => $ci,
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemref' => 1,
				'noperacionref' => $reg['noperacionref'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceDetailSaleCredit',
			'invoiceDetailSaleCredit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Pago Credito
	 */
	public function getPaymentSaleCredit($param) {
		global $sqlca;

		$res = array();

 		$sql = "SELECT
 FIRST(ctd.cli_codigo)||FIRST(ctd.ch_tipdocumento)||FIRST(ctd.ch_seriedocumento)||FIRST(ctd.ch_numdocumento)||FIRST(ctd.ch_identidad) AS noperacionpe,
 FIRST(ftfc.ch_fac_tipodocumento)||FIRST(ftfc.ch_fac_seriedocumento)||FIRST(ftfc.ch_fac_numerodocumento) AS noperacion,
 FIRST(ctd.cli_codigo) AS cardcode,
 ctd.dt_fecha_actualizacion AS docdate,
 FIRST(ctd.nu_importemovimiento) AS doctotal,
 '' AS moneda,
 FIRST(ctc.ch_formapago) AS _type_payment_id,
 FIRST(sap_cash_fund.sap_codigo) AS fecuenta,
 FIRST(sap_currency.sap_codigo) AS femoneda,
 1 AS fetc,
 FIRST(ctd.nu_importemovimiento) AS femonto,
 0 AS fecuentav,
 '' AS tccod,
 '' AS tccuenta,
 '' AS tcnumero,
 '' AS tcid,
 TO_CHAR(ctd.dt_fecha_actualizacion,'DD/MM') AS tcvalido,
 FIRST(ctd.nu_importemovimiento) AS tcmonto,
 '' AS bcuenta,
 '' AS breferencia,
 '' AS bfecha,
 FIRST(ctd.nu_importemovimiento) AS bmonto

 --,FIRST(ftfcc.ch_fac_observacion1) AS data_payment
 ,FIRST(ctd.ch_glosa) AS data_payment
FROM
 ccob_ta_detalle AS ctd
 INNER JOIN ccob_ta_cabecera AS ctc ON (ctc.cli_codigo = ctd.cli_codigo AND ctc.ch_tipdocumento = ctd.ch_tipdocumento AND ctc.ch_seriedocumento = ctd.ch_seriedocumento AND ctc.ch_numdocumento = ctd.ch_numdocumento)
 INNER JOIN int_clientes client ON (ctd.cli_codigo = client.cli_codigo)
 INNER JOIN fac_ta_factura_cabecera ftfc ON (ctd.ch_seriedocumento = ftfc.ch_fac_seriedocumento AND ctd.ch_numdocumento = ftfc.ch_fac_numerodocumento AND ctd.ch_tipdocumento = ftfc.ch_fac_tipodocumento AND ctd.cli_codigo = ftfc.cli_codigo)

 LEFT JOIN fac_ta_factura_complemento ftfcc ON (ftfc.ch_fac_seriedocumento = ftfcc.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = ftfcc.ch_fac_numerodocumento AND ftfc.ch_fac_tipodocumento = ftfcc.ch_fac_tipodocumento AND ftfc.cli_codigo = ftfcc.cli_codigo)

 LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
 LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)

 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
 INNER JOIN int_tabla_general paymentType ON (
  paymentType.tab_tabla = '05'
  AND paymentType.tab_elemento != ''
  AND paymentType.tab_elemento <> '000000'
  AND substring(tab_elemento for 2 from length(tab_elemento)-1) = ctc.ch_formapago
 )
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
WHERE
 ctd.dt_fecha_actualizacion BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 ".$param['client']."
GROUP BY vtcd.ch_fac_tipodocumento, vtcd.ch_fac_seriedocumento, vtcd.ch_fac_numerodocumento, ctd.dt_fecha_actualizacion;";

		$this->_error_log($param['tableName'].' - getPaymentSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$matches = array();
			$text = $reg['data_payment'];
			preg_match("/\[[^\]]*\]/", $text, $matches);

			//0: efectivo, 1: tarjeta de credito, 2: banco
			$paymentType = 0;
			if (count($matches) < 1) {
				$paymentType = 0;//No se encontró forma y se define como efectivo
			} else {
				$str = substr($matches[0], 1, -1);
				$ex_str = explode(',', $str);
				if (isset($ex_str[0])) {
					$ex_str[0] = trim($ex_str[0]);
					if ($ex_str[0] >= 0 && $ex_str[0] <= 2) {
						$paymentType = (int)$ex_str[0];
						if (isset($ex_str[1])) {
							$reg['tccod'] = $ex_str[1];
						} else {
							$paymentType = 0;
						}
						if (isset($ex_str[2])) {
							$reg['tcid'] = $ex_str[2];
						} else {
							$paymentType = 0;
						}
						
					} else {
						$paymentType = 0;
					}
				}
			}
			//echo '<hr>$reg[noperacionpe]: '.$reg['noperacionpe'].', $paymentType: '.$paymentType.', $reg[tccod]: '.$reg['tccod'].', $reg[tcid]: '.$reg['tcid'].'<hr>';

			//0: efectivo, 1: tarjeta de credito, 2: banco
			//$paymentType = 0;
			/*if ($reg['_type_payment_id'] == '02') {
				//tarjeta de credito
				$paymentType = 1;
			}*/

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacionpe'],
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				'bcuenta' => $reg['bcuenta'],
				'breferencia' => $reg['breferencia'],
				'bfecha' => $reg['bfecha'],
				'bmonto' => $reg['bmonto'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'paymentSaleCredit',
			'paymentSaleCredit' => $res,
			'count' => $c,
		);
	}



	/**
	 * =============================================
	 * Anticipo
	 * Factura Anticipo - Cabecera
	 */

	/**
	 * Factura Inicial de Anticipation(Factura del pago)
	 */
	public function getInvoiceHeaderSaleAnticipationInit($param) {
		global $sqlca;

		$res = array();

 		$sql = "SELECT
 ftfc.cli_codigo||ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 ftfc.cli_codigo AS cardcode,
 ftfc.dt_fac_fecha AS docdate,
 ftfc.ch_fac_seriedocumento AS foliopref,
 ftfc.ch_fac_numerodocumento AS folionum,
 ROUND(ftfc.nu_fac_impuesto1, 2) AS vatsum,
 ROUND(ftfc.nu_fac_valortotal, 2) AS doctotal,
 doctype_s.tab_car_03 AS indicator,

 ftfc.nu_fac_impuesto1 AS tax_total,
 (util_fn_igv()/100) AS cnf_igv_ocs,
 ftfc.nu_fac_valorbruto AS taxable_operations,
 ftfc.nu_fac_valortotal AS grand_total,
 CASE WHEN ftfc.ch_fac_tiporecargo2 IS NULL OR ftfc.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
 WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 = 0 THEN 1 -- EXO
 WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(ftfc.nu_fac_descuento1, 0) AS disc,

 '' AS extempno,
 '' AS u_exc_maqreg
FROM
 fac_ta_factura_cabecera ftfc
 JOIN int_tabla_general doctype_s ON(ftfc.ch_fac_tipodocumento = SUBSTRING(TRIM(doctype_s.tab_elemento) for 2 from length(TRIM(doctype_s.tab_elemento))-1) AND doctype_s.tab_tabla ='08' AND doctype_s.tab_elemento != '000000')
 JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND ftfc.nu_fac_recargo3 IN (3, 5)
 AND ftfc.ch_fac_anticipo = 'S'
 ".$param['client'].";";

		$this->_error_log($param['tableName'].' - getInvoiceHeaderSaleAnticipationInit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$data = $this->calcAmounts($reg);

			$data['tax_total'] = $this->getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2));
			$data['grand_total'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			$this->invoiceSaleHead[$reg['foliopref']][$reg['folionum']] = $param['tableName'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'indicator' => $reg['indicator'],
				'vatsum' => (float)$data['tax_total'],
				'doctotal' => (float)$data['grand_total'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceHeaderSaleAnticipationInit',
			'invoiceHeaderSaleAnticipationInit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura inicial de Anticipo - Detalle
	 */
	public function getInvoiceDetailSaleAnticipationInit($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 ftfc.cli_codigo||ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 ftfd.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 ftfd.nu_fac_cantidad AS quantity,
 ROUND(ftfd.nu_fac_precio/ ".$param['tax'].", 4) AS price,
 SAPLINEA.sap_codigo AS ocrcode,
 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat,
 '".$param['sap_tax_code']."' AS taxcode,
 '' AS u_exc_turno

 , ftfd.ch_fac_seriedocumento AS serie_
 , ftfd.ch_fac_numerodocumento AS number_
 , ftfd.art_codigo AS product_,
 ROUND((ftfd.nu_fac_descuento1), 4) AS desc_sinigv,
 ROUND((ftfd.nu_fac_descuento1 * ".$param['tax']."), 4) AS desc_igv
FROM 
 fac_ta_factura_cabecera ftfc
 INNER JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
 INNER JOIN fac_ta_factura_detalle ftfd ON (ftfc.ch_fac_tipodocumento = ftfd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = ftfd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = ftfd.ch_fac_numerodocumento AND ftfc.cli_codigo = ftfd.cli_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = ftfc.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = ftfd.art_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar

JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)--puede limitar

WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND ftfc.nu_fac_recargo3 IN (3, 5)
 AND ftfc.ch_fac_anticipo = 'S'
 ".$param['client']."
;";

		$this->_error_log($param['tableName'].' - getInvoiceDetailSaleAnticipationInit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->invoiceSaleDetail[$reg['serie_']][$reg['number_']][$this->cleanStr($reg['product_'])] = array(
				'item' => $ci,
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => $reg['quantity'],
				'price' => $reg['price'],
				'taxcode' => $reg['taxcode'],
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceDetailSaleAnticipationInit',
			'invoiceDetailSaleAnticipationInit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Pago Anticipation
	 */
	public function getPaymentSaleAnticipation($param) {
		global $sqlca;

		$res = array();

		$sql = "SELECT
 FIRST(ctd.cli_codigo)||FIRST(ctd.ch_tipdocumento)||FIRST(ctd.ch_seriedocumento)||FIRST(ctd.ch_numdocumento::INTEGER)||FIRST(ctd.ch_identidad) AS noperacionpe,
 FIRST(ftfc.cli_codigo)||FIRST(ftfc.ch_fac_tipodocumento)||FIRST(ftfc.ch_fac_seriedocumento)||FIRST(ftfc.ch_fac_numerodocumento) AS noperacion,
 FIRST(ctd.cli_codigo) AS cardcode,
 ctd.dt_fecha_actualizacion AS docdate,
 FIRST(ctd.nu_importemovimiento) AS doctotal,
 '' AS moneda,
 FIRST(ctc.ch_formapago) AS _type_payment_id,
 FIRST(sap_cash_fund.sap_codigo) AS fecuenta,
 FIRST(sap_currency.sap_codigo) AS femoneda,
 1 AS fetc,
 FIRST(ctd.nu_importemovimiento) AS femonto,
 0 AS fecuentav,
 '' AS tccod,
 '' AS tccuenta,
 '' AS tcnumero,
 '' AS tcid,
 TO_CHAR(ctd.dt_fecha_actualizacion,'DD/MM') AS tcvalido,
 FIRST(ctd.nu_importemovimiento) AS tcmonto,
 '' AS bcuenta,
 '' AS breferencia,
 '' AS bfecha,
 FIRST(ctd.nu_importemovimiento) AS bmonto
 ,FIRST(ctd.ch_glosa) AS data_payment
FROM
 ccob_ta_detalle AS ctd
 INNER JOIN ccob_ta_cabecera AS ctc ON (ctc.cli_codigo = ctd.cli_codigo AND ctc.ch_tipdocumento = ctd.ch_tipdocumento AND ctc.ch_seriedocumento = ctd.ch_seriedocumento AND ctc.ch_numdocumento = ctd.ch_numdocumento)
 INNER JOIN int_clientes client ON (ctd.cli_codigo = client.cli_codigo)
 INNER JOIN fac_ta_factura_cabecera ftfc ON (ctd.ch_seriedocumento = ftfc.ch_fac_seriedocumento AND ctd.ch_numdocumento = ftfc.ch_fac_numerodocumento AND ctd.cli_codigo = ftfc.cli_codigo AND (ftfc.ch_fac_tipodocumento = '10' OR ftfc.ch_fac_tipodocumento = '35'))

 LEFT JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
 LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)

 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
 INNER JOIN int_tabla_general paymentType ON (
  paymentType.tab_tabla = '05'
  AND paymentType.tab_elemento != ''
  AND paymentType.tab_elemento <> '000000'
  AND substring(tab_elemento for 2 from length(tab_elemento)-1) = ctc.ch_formapago
 )
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
WHERE
 ctd.dt_fecha_actualizacion BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 ".$param['client']."
 AND ftfc.nu_fac_recargo3 IN (3, 5)
GROUP BY vtcd.ch_fac_tipodocumento, vtcd.ch_fac_seriedocumento, vtcd.ch_fac_numerodocumento, ctd.dt_fecha_actualizacion;";

		$this->_error_log($param['tableName'].' - getPaymentSaleAnticipation: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$matches = array();
			$text = $reg['data_payment'];
			preg_match("/\[[^\]]*\]/", $text, $matches);

			//0: efectivo, 1: tarjeta de credito, 2: banco
			$paymentType = 0;
			if (count($matches) < 1) {
				$paymentType = 0;//No se encontró forma y se define como efectivo
			} else {
				$str = substr($matches[0], 1, -1);
				$ex_str = explode(',', $str);
				if (isset($ex_str[0])) {
					$ex_str[0] = trim($ex_str[0]);
					if ($ex_str[0] >= 0 && $ex_str[0] <= 2) {
						$paymentType = (int)$ex_str[0];
						if (isset($ex_str[1])) {
							$reg['tccod'] = $ex_str[1];
						} else {
							$paymentType = 0;
						}
						if (isset($ex_str[2])) {
							$reg['tcid'] = $ex_str[2];
						} else {
							$paymentType = 0;
						}
						
					} else {
						$paymentType = 0;
					}
				}
			}
			//echo '<hr>$reg[noperacionpe]: '.$reg['noperacionpe'].', $paymentType: '.$paymentType.', $reg[tccod]: '.$reg['tccod'].', $reg[tcid]: '.$reg['tcid'].'<hr>';

			//0: efectivo, 1: tarjeta de credito, 2: banco
			//$paymentType = 0;
			/*if ($reg['_type_payment_id'] == '02') {
				//tarjeta de credito
				$paymentType = 1;
			}*/

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacionpe'],
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				'bcuenta' => $reg['bcuenta'],
				'breferencia' => $reg['breferencia'],
				'bfecha' => $reg['bfecha'],
				'bmonto' => $reg['bmonto'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'paymentSaleAnticipation',
			'paymentSaleAnticipation' => $res,
			'count' => $c,
		);
	}

	/**
	 * Guia anticipo - Cabecera
	 */
	public function getShipmentHeaderSaleAnticipation($param) {
		global $sqlca;

		$param['_tax'] = $param['tax'];
		if ($param['_tax'] > 0) {
			$param['_tax'] = ($param['tax'] - 1);
		}
		error_log('INTODLNA: '.$param['tax'].', '.$param['_tax']);

		$res = array();
		$sql = "
SELECT
vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || --vtc.ch_documento AS noperacion, --cai
substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento)) AS noperacion,
FIRST(client.cli_codigo) AS cardcode,
FIRST(vtc.dt_fecha) AS docdate,
'00'||FIRST(vtc.ch_caja) AS foliopref,
--FIRST(vtc.ch_documento) AS folionum, --CAI
FIRST(substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento))) AS folionum,

ROUND(FIRST(vtc.nu_importe) - (FIRST(vtc.nu_importe) / (1 + ".$param['_tax'].")), 2) AS vatsum,--probar
ROUND(FIRST(vtc.nu_importe), 2) AS doctotal,

CASE WHEN FIRST(employe.ch_codigo_trabajador) IS NULL OR FIRST(employe.ch_codigo_trabajador) = '' THEN
 (SELECT ch_codigo_trabajador FROM pos_historia_ladosxtrabajador WHERE dt_dia = FIRST(vtc.dt_fecha) AND ch_posturno::CHAR = FIRST(vtc.ch_turno) AND ch_lado = FIRST(vtc.ch_caja))
ELSE
 FIRST(employe.ch_codigo_trabajador)
END AS extempno,--

FIRST(vtc.ch_caja) AS u_exc_maqreg,

FIRST(vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY')) as noperacion_primero,
FIRST(substr(vtc.ch_documento, position('-' in vtc.ch_documento)+1, char_length (vtc.ch_documento)+1 - position('-' in vtc.ch_documento))) as noperacion_segundo

FROM
val_ta_cabecera vtc
JOIN inv_ta_almacenes alm ON (vtc.ch_sucursal = alm.ch_almacen)
JOIN int_clientes client ON (vtc.ch_cliente = client.cli_codigo)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = vtc.dt_fecha AND employe.ch_posturno::CHAR = vtc.ch_turno AND employe.ch_lado = vtc.ch_lado)
WHERE
 vtc.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 ".$param['client']."
GROUP BY 1;";

		$this->_error_log($param['tableName'].' - getShipmentHeaderSaleAnticipation: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			//OPENSOFT-26
			//Validacion para que no permita caracteres diferentes a numeros en el numero de vale 
			$reg['noperacion_segundo'] = preg_replace('([^0-9])', '', $reg['noperacion_segundo']);				
			$reg['folionum']           = preg_replace('([^0-9])', '', $reg['folionum']);	

			//Validacion para que el largo no sea mayor a 9 digitos, usando el operador aritmetico % 		
			$reg['noperacion_segundo'] = $reg['noperacion_segundo'] % 1000000000;
			$reg['folionum']           = $reg['folionum'] % 1000000000;

			//Obtenemos noperacion
			$reg['noperacion'] = $reg['noperacion_primero'] . $reg['noperacion_segundo'];
			//CERRAR OPENSOFT-26			

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'shipmentHeaderSaleAnticipation',
			'shipmentHeaderSaleAnticipation' => $res,
			'count' => $c,
		);
	}

	/**
	 * Guia credito - Detalle
	 */
	public function getShipmentDetailSaleAnticipation($param) {
		global $sqlca;

		$tmpDocument = array();
		$res = array();

		/**
		 * Obetener vales automaticos
		 */
		$sql = "
SELECT
 PT.es::INTEGER || TO_CHAR(FIRST(PT.dia), 'DDMMYY') || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 SUM(PT.cantidad) AS quantity,

 ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax'].", 4) AS price,
 --ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt,
 CASE WHEN ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / 1.18) > 0 THEN
ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4)
ELSE 0
END AS discprcnt,

 SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,

 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,

 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,

 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE '' END AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(SUM(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 PT.tarjeta AS u_exc_nrotarjmag,--pendiente

 FIRST(PT.trans) AS item
 , 'postrans' AS source,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv
FROM
".$param['pos_trans']." AS PT
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
JOIN int_clientes client ON (pt.cuenta = client.cli_codigo)
LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento,
 (PT.importe - PT.igv) AS importe_descuento_sigv,
 PT.importe AS importe_descuento_igv
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo = 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_sin_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo != 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td='N'
 --AND PT.es = '".$param['warehouse']."'
 ".$param['client']."
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo,
 PT.tarjeta--pendiente
ORDER BY 1;
		";

		$this->_error_log($param['tableName'].' [A] - getShipmentDetailSaleAnticipation: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$tmpDocument[$reg['noperacion']][$this->cleanStr($reg['itemcode'])] = 1;

			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => 1,//(int)$reg['item'],
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nrotarjmag' => $reg['u_exc_nrotarjmag'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
		}

		/**
		 * Obetener vales manuales
		 */
		$sql = "
SELECT
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || vtc.ch_documento AS noperacion,
 vtd.ch_articulo AS itemcode,
 SAPALMA.sap_codigo AS whscode,--SAPALMA.sap_codigo AS whscode,
 vtd.nu_cantidad AS quantity,
 ROUND(vtd.nu_precio_unitario / ".$param['tax'].", 4) AS price,--sin impuesto!
 '".$param['sap_tax_code']."' AS taxcode,

 ROUND(vtd.nu_precio_unitario, 4) AS _price,
 CASE WHEN vtd.nu_precio_unitario > 0 THEN
  vtd.nu_importe - ROUND((vtd.nu_precio_unitario * vtd.nu_cantidad), 3)
 ELSE 0 END AS _discprcnt,

 ROUND(0.0, 4) AS discprcnt,
 SAPLINEA.sap_codigo AS ocrcode,

 SAPCC.sap_codigo AS ocrcode2,
 --ROUND(vtd.nu_precio_unitario, 2) AS priceafvat,
 CASE WHEN PT.precio IS NULL THEN vtd.nu_precio_unitario ELSE ROUND(PT.precio, 2) END AS priceafvat,

 vtc.ch_lado AS u_exc_dispensador,
 vtc.ch_caja AS u_exc_caja,
 --SURTIDOR.nu_manguera::TEXT AS u_exc_manguera,
 CASE WHEN vtc.ch_lado != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = vtc.ch_lado::INTEGER AND SURTIDOR.ch_codigocombustible = vtd.ch_articulo)::TEXT
 ELSE '' END AS u_exc_manguera,
 vtc.ch_turno AS u_exc_turno,
 TO_CHAR(vtc.dt_fecha, 'HH12:MI:SS') AS u_exc_hora,
 vtc.ch_placa AS u_exc_placa,
 vtc.nu_odometro AS u_exc_km,
 TRUNC(vtd.nu_importe / ".$param['factor_bonus'].") AS u_exc_bonus,
 '' AS u_exc_nrotarjbonus,--pendiente
 '' AS u_exc_nrotarjmag--pendiente

 , vtc.ch_documento AS item
 , 'val_ta' AS source,
 ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0)) AS desc_sinigv,
 ABS(COALESCE(PTDSCT.importe_descuento_igv, 0)) AS desc_igv,
 
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') AS noperacion_primero,
 vtc.ch_documento AS noperacion_segundo

FROM
val_ta_detalle vtd
JOIN val_ta_cabecera vtc ON (vtd.ch_sucursal = vtc.ch_sucursal AND vtd.dt_fecha = vtc.dt_fecha AND vtd.ch_documento = vtc.ch_documento)
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (vtc.ch_lado::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = vtd.ch_articulo)
LEFt JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = vtc.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
JOIN int_clientes client ON (vtc.ch_cliente = client.cli_codigo)

JOIN int_articulos AS art ON (art.art_codigo = vtd.ch_articulo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar

LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = vtc.ch_sucursal AND SAPALMA.id_tipo_tabla = 2)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = vtc.dt_fecha AND employe.ch_posturno::CHAR = vtc.ch_turno AND employe.ch_lado = vtc.ch_lado)

LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento,
  ROUND(PT.importe - PT.igv, 4) AS importe_descuento_sigv,
  PT.importe AS importe_descuento_igv
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND pt.td='N'
  ---AND PT.tipo = 'M'
  AND PT.grupo = 'D'
  ---AND PT.es = '".$param['warehouse']."'
 ) AS PTDSCT ON (PTDSCT.es = vtc.ch_sucursal AND PTDSCT.caja = vtc.ch_caja AND (PTDSCT.trans::CHARACTER = vtc.ch_documento OR PTDSCT.caja||'-'||PTDSCT.trans = vtc.ch_documento))
LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND pt.td='N'
  ---AND PT.tipo = 'M'
  AND PT.grupo != 'D'
  ---AND PT.es = '".$param['warehouse']."'
 ) AS PT ON (PT.es = vtc.ch_sucursal AND PT.caja = vtc.ch_caja AND (PT.trans::CHARACTER = vtc.ch_documento OR PT.caja||'-'||PT.trans = vtc.ch_documento))

WHERE
 vtc.dt_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND vtd.nu_precio_unitario >= 0
 ".$param['client']."
 		";

		$this->_error_log($param['tableName'].' [M] - getShipmentDetailSaleAnticipation'.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			//OPENSOFT-26
			//Validacion para que no permita caracteres diferentes a numeros en el numero de vale 
			$reg['noperacion_segundo'] = preg_replace('([^0-9])', '', $reg['noperacion_segundo']);							

			//Validacion para que el largo no sea mayor a 9 digitos, usando el operador aritmetico % 		
			$reg['noperacion_segundo'] = $reg['noperacion_segundo'] % 1000000000;			

			//Obtenemos noperacion
			$reg['noperacion'] = $reg['noperacion_primero'] . $reg['noperacion_segundo'];
			//CERRAR OPENSOFT-26	

			if (!isset($tmpDocument[$reg['noperacion']][$this->cleanStr($reg['itemcode'])])) {
				$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
				$res[] = array(
					'noperacion' => $reg['noperacion'],
					'item' => 1,//(int)$reg['item'],
					'itemcode' => $this->cleanStr($reg['itemcode']),
					'whscode' => $reg['whscode'],
					'quantity' => (float)$reg['quantity'],
					'price' => (float)$reg['price'],
					'taxcode' => $reg['taxcode'],
					'discprcnt' => (float)$discprcnt,
					'ocrcode' => $reg['ocrcode'],
					'ocrcode2' => $reg['ocrcode2'],
					'priceafvat' => (float)$reg['priceafvat'],
					'u_exc_dispensador' => $reg['u_exc_dispensador'],
					'u_exc_caja' => $reg['u_exc_caja'],
					'u_exc_manguera' => $reg['u_exc_manguera'],
					'u_exc_turno' => $reg['u_exc_turno'],
					'u_exc_hora' => $reg['u_exc_hora'],
					'u_exc_placa' => $reg['u_exc_placa'],
					'u_exc_km' => $reg['u_exc_km'],
					'u_exc_bonus' => $reg['u_exc_bonus'],
					'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
					'u_exc_nrotarjmag' => $reg['u_exc_nrotarjmag'],
					'desc_sinigv' => (float)$reg['desc_sinigv'],
					'desc_igv' => (float)$reg['desc_igv'],
				);
			} else {
				$c--;
			}
		}

		unset($tmpDocument);

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'shipmentDetailSaleAnticipation',
			'shipmentDetailSaleAnticipation' => $res,
			'count' => $c,
		);
	}

	/**
	 * 
	 */
	public function getInvoiceHeaderSaleAnticipation($param) {
		global $sqlca;

		$res = array();

		$sql = "SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento||ftfc.cli_codigo AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 FIRST(ftfc.ch_fac_seriedocumento) AS foliopref,
 FIRST(ftfc.ch_fac_numerodocumento) AS folionum,
 ROUND(FIRST(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(ftfc.nu_fac_valortotal), 2) AS doctotal,

 FIRST(ftfc.nu_fac_impuesto1) AS tax_total,
 FIRST((util_fn_igv()/100)) AS cnf_igv_ocs,
 FIRST(ftfc.nu_fac_valorbruto) AS taxable_operations,
 FIRST(ftfc.nu_fac_valortotal) AS grand_total,
 CASE WHEN FIRST(ftfc.ch_fac_tiporecargo2) IS NULL OR FIRST(ftfc.ch_fac_tiporecargo2) = '' THEN 0 -- NORMAL
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) = 0 THEN 1 -- EXO
 WHEN FIRST(ftfc.ch_fac_tiporecargo2) = 'S' AND FIRST(ftfc.nu_fac_impuesto1) > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(FIRST(ftfc.nu_fac_descuento1), 0) AS disc,

 '' AS extempno,
 '' AS u_exc_maqreg
FROM
fac_ta_factura_cabecera ftfc
JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)
JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
--AND ftfc.ch_almacen = '".$param['warehouse']."'
AND ftfc.nu_fac_recargo3 IN (3, 5)
AND ftfc.ch_fac_tipodocumento = '10'
".$param['client']."
GROUP BY 1;";
		$this->_error_log($param['tableName'].' - getInvoiceHeaderSaleAnticipation: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$data = $this->calcAmounts($reg);

			$data['tax_total'] = $this->getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2));
			$data['grand_total'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			$this->invoiceSaleHead[$reg['foliopref']][$reg['folionum']] = $param['tableName'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'vatsum' => (float)$data['tax_total'],
				'doctotal' => (float)$data['grand_total'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceHeaderSaleAnticipation',
			'invoiceHeaderSaleAnticipation' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura Anticipation - Detalle
	 */
	public function getInvoiceDetailSaleAnticipation($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento||ftfc.cli_codigo AS noperacion,
 vtc.ch_sucursal::INTEGER || TO_CHAR(vtc.dt_fecha, 'DDMMYY') || vtc.ch_documento AS itemref,--'' AS itemref
 ftfd.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 ftfd.nu_fac_cantidad AS quantity,
 ROUND(ftfd.nu_fac_precio/ ".$param['tax'].", 4) AS price,
 '".$param['sap_tax_code']."' AS taxcode,
 SAPLINEA.sap_codigo AS ocrcode,

 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat,

 '' AS u_exc_turno

 , ftfd.ch_fac_seriedocumento AS serie_
 , ftfd.ch_fac_numerodocumento AS number_
 , ftfd.art_codigo AS product_
FROM
fac_ta_factura_cabecera ftfc
JOIN fac_ta_factura_detalle ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
LEFT JOIN val_ta_cabecera vtc ON (vtcd.ch_sucursal = vtc.ch_sucursal AND vtcd.dt_fecha = vtc.dt_fecha AND vtcd.ch_numeval = vtc.ch_documento)

JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)

JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = ftfc.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = ftfd.art_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
WHERE ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
AND ftfc.ch_fac_tipodocumento = '10'
--AND ftfc.ch_almacen = '".$param['warehouse']."'
".$param['client']."
ORDER BY 1;";

		$this->_error_log($param['tableName'].' - getInvoiceDetailSaleAnticipation: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->invoiceSaleDetail[$reg['serie_']][$reg['number_']][$this->cleanStr($reg['product_'])] = array(
				'item' => $ci,
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => $reg['quantity'],
				'price' => $reg['price'],
				'taxcode' => $reg['taxcode'],
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_turno' => $reg['u_exc_turno'],
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'invoiceDetailSaleAnticipation',
			'invoiceDetailSaleAnticipation' => $res,
			'count' => $c,
		);
	}






	/**
	 * Boletas - Cabecera
	 */
	public function getDocumentHeadTicket($param) {
		//20 y 45 documentos manuales
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 (string_to_array(pt.usr, '-'))[1] AS foliopref,
 pt.turno AS _turn,
 FIRST(pt.es) || FIRST(pt.caja) || FIRST(pt.trans) AS noperacion,
 --CASE WHEN FIRST(pt.ruc) = '' THEN 'C00099999999' ELSE FIRST(pt.ruc) END AS cardcode,
 'C00099999999' AS cardcode, --JEL, cliente por defecto
 FIRST(pt.dia) AS docdate,
 MIN(((string_to_array(pt.usr, '-'))[2])::INTEGER) AS u_exx_nroini,
 MAX(((string_to_array(pt.usr, '-'))[2])::INTEGER) AS u_exx_nrofin,
 ROUND(SUM(pt.igv), 2) AS vatsum,
 ROUND(SUM(pt.importe), 2) AS doctotal,
 FIRST(pt.trans) AS transaccion--considerar otro valor para hacerlo unicno (caja?)
FROM
 ".$param['pos_trans']." pt
 LEFT JOIN int_clientes AS client
  ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe
  ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
WHERE
 pt.td = 'B'
 AND pt.tm = 'V'
 AND pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 --AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 1,
 pt.turno;
		";

		$this->_error_log($param['tableName'].' - getDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$this->ticketHead[$reg['foliopref']][$reg['_turn']] = array(
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
			);
		}

		$sql = "
SELECT
 ftfc.ch_fac_seriedocumento AS foliopref,
 FIRST(ftfc.ch_fac_tiporecargo3) AS _turn,
 FIRST(ftfc.ch_fac_tipodocumento)||ftfc.ch_fac_seriedocumento||FIRST(ftfc.ch_fac_numerodocumento::INTEGER)||FIRST(ftfc.cli_codigo) AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 MIN(ftfc.ch_fac_numerodocumento::INTEGER) AS u_exx_nroini,
 MAX(ftfc.ch_fac_numerodocumento::INTEGER) AS u_exx_nrofin,
 ROUND(SUM(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(SUM(ftfc.nu_fac_valortotal), 2) AS doctotal,
 '' AS transaccion
FROM
 fac_ta_factura_cabecera AS ftfc
 JOIN int_clientes AS client
  ON(ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 --AND ftfc.ch_almacen = '".$param['warehouse']."'
 AND ftfc.ch_fac_tipodocumento = '35'
 AND ftfc.ch_fac_anticipo != 'S'
GROUP BY 1, ftfc.ch_fac_tiporecargo3;";

		$this->_error_log($param['tableName'].' - getDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$reg['transaccion'] = $reg['noperacion'];
			$this->ticketHead[$reg['foliopref']][$reg['_turn']] = array(
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'].' 00:00:00',
				'foliopref' => $reg['foliopref'],
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'documentHeadTicket',
			'documentHeadTicket' => $res,
			'count' => $c,
		);
	}	

	/**
	 * Boletas - Cabecera
	 */
	public function getDocumentHeadTicketDistinguirDocumentosMayores700($param) {
		//20 y 45 documentos manuales
		global $sqlca;

		$cantidad = 700;
		$res = array();
		$sql = "
SELECT
 (string_to_array(pt.usr, '-'))[1] AS foliopref,
 pt.turno AS _turn,
 FIRST(pt.es) || FIRST(pt.caja) || FIRST(pt.trans) AS noperacion,
 --CASE WHEN FIRST(pt.ruc) = '' THEN 'C00099999999' ELSE FIRST(pt.ruc) END AS cardcode,
 'C00099999999' AS cardcode, --JEL, cliente por defecto
 FIRST(pt.dia) AS docdate,
 MIN(((string_to_array(pt.usr, '-'))[2])::INTEGER) AS u_exx_nroini,
 MAX(((string_to_array(pt.usr, '-'))[2])::INTEGER) AS u_exx_nrofin,
 ROUND(SUM(pt.igv), 2) AS vatsum,
 ROUND(SUM(pt.importe), 2) AS doctotal,
 FIRST(pt.trans) AS transaccion--considerar otro valor para hacerlo unicno (caja?)
FROM
 ".$param['pos_trans']." pt
 LEFT JOIN int_clientes AS client
  ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe
  ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
WHERE
 pt.td = 'B'
 AND pt.tm = 'V'
 AND pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 --AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
 AND pt.importe < ".$cantidad."
GROUP BY
 1,
 pt.turno;
		";

		$this->_error_log($param['tableName'].' - getDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$this->ticketHead[$reg['foliopref']][$reg['_turn']] = array(
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
			);
		}

		$sql = "
SELECT
 FIRST((string_to_array(pt.usr, '-'))[1]) AS foliopref,
 FIRST(pt.turno) AS _turn,
 --FIRST(pt.es) || FIRST(pt.caja) || FIRST(pt.trans) AS noperacion,
 PT.es::INTEGER || PT.caja || PT.trans AS noperacion,
 CASE WHEN FIRST(pt.ruc) = '' THEN 'C00099999999' ELSE FIRST(pt.ruc) END AS cardcode,
 --'C00099999999' AS cardcode, --JEL, cliente por defecto
 FIRST(pt.dia) AS docdate,
 MIN(((string_to_array(pt.usr, '-'))[2])::INTEGER) AS u_exx_nroini,
 MAX(((string_to_array(pt.usr, '-'))[2])::INTEGER) AS u_exx_nrofin,
 ROUND(SUM(pt.igv), 2) AS vatsum,
 ROUND(SUM(pt.importe), 2) AS doctotal,
 FIRST(pt.trans) AS transaccion--considerar otro valor para hacerlo unicno (caja?)
FROM
 ".$param['pos_trans']." pt
 LEFT JOIN int_clientes AS client
  ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe
  ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
WHERE
 pt.td = 'B'
 AND pt.tm = 'V'
 AND pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 --AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
 AND pt.importe >= ".$cantidad."
GROUP BY
 --1,
 --pt.turno;
 PT.es,
 PT.caja,
 PT.trans;
		";

		$this->_error_log($param['tableName'].' - getDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		//$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$this->ticketHead[$reg['u_exx_nroini']] = array(
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
			);
		}

		$sql = "
SELECT
 ftfc.ch_fac_seriedocumento AS foliopref,
 FIRST(ftfc.ch_fac_tiporecargo3) AS _turn,
 FIRST(ftfc.ch_fac_tipodocumento)||ftfc.ch_fac_seriedocumento||FIRST(ftfc.ch_fac_numerodocumento::INTEGER)||FIRST(ftfc.cli_codigo) AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 MIN(ftfc.ch_fac_numerodocumento::INTEGER) AS u_exx_nroini,
 MAX(ftfc.ch_fac_numerodocumento::INTEGER) AS u_exx_nrofin,
 ROUND(SUM(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(SUM(ftfc.nu_fac_valortotal), 2) AS doctotal,
 '' AS transaccion
FROM
 fac_ta_factura_cabecera AS ftfc
 JOIN int_clientes AS client
  ON(ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 --AND ftfc.ch_almacen = '".$param['warehouse']."'
 AND ftfc.ch_fac_tipodocumento = '35'
 AND ftfc.ch_fac_anticipo != 'S'
GROUP BY 1, ftfc.ch_fac_tiporecargo3;";

		$this->_error_log($param['tableName'].' - getDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$reg['transaccion'] = $reg['noperacion'];
			$this->ticketHead[$reg['foliopref']][$reg['_turn']] = array(
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'noperacion' => $reg['noperacion'],
			);
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'].' 00:00:00',
				'foliopref' => $reg['foliopref'],
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
			);
		}

		error_log( json_encode( $this->ticketHead ) );
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'documentHeadTicket',
			'documentHeadTicket' => $res,
			'count' => $c,
		);
	}

	/**
	 * Boletas - Cabecera
	 */
	public function getDocumentHeadTicketDesagregarDocumentosAnuladosTransferenciasGratuitas($param) {
		//20 y 45 documentos manuales
		global $sqlca;

		$res = array();		
		$originalHead = array();
		$array_serie_turno = array();
		$sql = "
SELECT
 (string_to_array(FIRST(pt.usr), '-'))[1] AS foliopref,
 FIRST(pt.turno) AS _turn,
 FIRST(pt.es) || FIRST(pt.caja) || FIRST(pt.trans) AS noperacion,
 --CASE WHEN FIRST(pt.ruc) = '' THEN 'C00099999999' ELSE FIRST(pt.ruc) END AS cardcode,
 'C00099999999' AS cardcode, --JEL, cliente por defecto
 FIRST(pt.dia) AS docdate,
 MIN(((string_to_array(pt.usr, '-'))[2])::INTEGER) AS u_exx_nroini,
 MAX(((string_to_array(pt.usr, '-'))[2])::INTEGER) AS u_exx_nrofin,
 ROUND(SUM(pt.igv), 2) AS vatsum,
 ROUND(SUM(pt.importe), 2) AS doctotal,
 FIRST(pt.trans) AS transaccion--considerar otro valor para hacerlo unicno (caja?)
 
 , FIRST(pt.trans) AS u_exc_ticket
 , FIRST(pt.es) AS es
 , FIRST(pt.caja) AS caja
 , FIRST(pt.trans) AS trans
 , FIRST(pt.cantidad) AS cantidad
 , FIRST(pt.precio) AS precio
 , FIRST(pt.igv) AS igv
 , FIRST(pt.importe) AS importe
FROM
 ".$param['pos_trans']." pt
 LEFT JOIN int_clientes AS client
  ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe
  ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
WHERE
 pt.td = 'B'
 AND pt.tm = 'V'
 AND pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 --AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo
ORDER BY 
 foliopref, u_exx_nroini, _turn;
		";

		$this->_error_log($param['tableName'].' - getDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}	
		while ($reg = $sqlca->fetchRow()) {
			$originalHead[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
				'_turn' => $reg['_turn'],
				'es' => $reg['es'],
				'caja' => $reg['caja'],
				'trans' => $reg['trans'],
			);
			$array_serie_turno[] = trim($reg['foliopref']) . "-" . trim($reg['_turn']);
		}	
		
		//LOGICA PARA DESAGREGAR DOCUMENTOS ANULADOS MANTENIENDO CONTINUIDAD DE CORRELATIVO (1-19,20,21-69,70,71-100)
		//OBTENEMOS UN ARRAY CON SERIE Y TURNO DEL DETALLE, YA QUE LOS DATOS SON AGRUPADOS POR SERIE Y TURNO
		$array_serie_turno = array_unique($array_serie_turno);					
		$i = 0;

		//RECORREMOS ARRAY DE SERIE Y TURNO
		foreach ($array_serie_turno as $key => $value) {
			$serie_turno       = $value;
			$inicio_agrupacion = true;					
			$i++;	
			
			//DENTRO DE CADA VALOR DENTRO DEL ARRAY, RECORREMOS LOS DATOS DE LA QUERY DETALLADA
			foreach ($originalHead as $key2 => $value2) {
				
				//SI ES EL PRIMER ELEMENTO DENTRO DEL ARRAY DE SERIES Y TURNO, OBTIENE EL DOCUMENTO INICIAL
				if($inicio_agrupacion == true){
					$noperacion   = $value2['es'] . $value2['caja'] . $value2['trans'];
					$u_exx_nroini = $value2['u_exx_nroini'];
					$transaccion  = $value2['transaccion'];
				} 

				//IGUALAMOS POR SERIE Y TURNO
				if(trim($serie_turno) == trim($originalHead[$key2]['foliopref']) ."-". trim($originalHead[$key2]['_turn'])){
					$inicio_agrupacion = false;										

					/**
					 * Cuando es anulado cantidad, precio, igv, importe y soles_km se muestra en 0, y no hay documento de referencia
					 */ 
					if($value2['doctotal'] != 0){ //NO ES DOCUMENTO ANULADO
						$res[$i]['noperacion']   = $noperacion;
						$res[$i]['cardcode']     = $this->preLetterBPartner('C', $value2['cardcode']);
						$res[$i]['docdate']      = $value2['docdate'];
						$res[$i]['foliopref']    = $value2['foliopref'];
						$res[$i]['u_exx_nroini'] = (int)$u_exx_nroini;
						$res[$i]['u_exx_nrofin'] = (int)$value2['u_exx_nrofin'];
						$res[$i]['vatsum']       += (float)$value2['vatsum'];
						$res[$i]['doctotal']     += (float)$value2['doctotal'];					
						$res[$i]['estado']       = 'P';
						$res[$i]['errormsg']     = '';
						$res[$i]['transaccion']  = $transaccion;
						$res[$i]['docentry']     = NULL;
						$res[$i]['_turn']        = $value2['_turn'];													
						$res[$i]['indicator']    = "BO";													
					}else{ //DOCUMENTOS ANULADOS DIFERENCIADOS
						$i++;
						$res[$i]['noperacion']   = $value2['noperacion'];
						$res[$i]['cardcode']     = $this->preLetterBPartner('C', $value2['cardcode']);
						$res[$i]['docdate']      = $value2['docdate'];
						$res[$i]['foliopref']    = $value2['foliopref'];
						$res[$i]['u_exx_nroini'] = (int)$value2['u_exx_nroini'];
						$res[$i]['u_exx_nrofin'] = (int)$value2['u_exx_nrofin'];
						$res[$i]['vatsum']       += (float)$value2['vatsum'];
						$res[$i]['doctotal']     += (float)$value2['doctotal'];					
						$res[$i]['estado']       = 'P';
						$res[$i]['errormsg']     = '';
						$res[$i]['transaccion']  = $value2['transaccion'];
						$res[$i]['docentry']     = NULL;
						$res[$i]['_turn']        = $value2['_turn'];
						$res[$i]['indicator']    = "AB";
						$i++;
						$inicio_agrupacion = true;
					}								
				}
			}
		}
		//CERRAR LOGICA PARA DESAGREGAR DOCUMENTOS ANULADOS MANTENIENDO CONTINUIDAD DE CORRELATIVO (1-19,20,21-69,70,71-100)

		$originalHead = array();
		$array_serie_turno = array();
		$sql = "
SELECT
 FIRST(ftfc.ch_fac_seriedocumento) AS foliopref,
 FIRST(ftfc.ch_fac_tiporecargo3) AS _turn,
 FIRST(ftfc.ch_fac_tipodocumento)||FIRST(ftfc.ch_fac_seriedocumento)||FIRST(ftfc.ch_fac_numerodocumento::INTEGER)||FIRST(ftfc.cli_codigo) AS noperacion,
 FIRST(ftfc.cli_codigo) AS cardcode,
 FIRST(ftfc.dt_fac_fecha) AS docdate,
 MIN(ftfc.ch_fac_numerodocumento::INTEGER) AS u_exx_nroini,
 MAX(ftfc.ch_fac_numerodocumento::INTEGER) AS u_exx_nrofin,
 ROUND(SUM(ftfc.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(SUM(ftfc.nu_fac_valortotal), 2) AS doctotal,
 '' AS transaccion

 , FIRST(nu_fac_recargo3) AS nu_fac_recargo3
 , FIRST(ch_fac_tiporecargo2) AS ch_fac_tiporecargo2
 , FIRST(ftfc.ch_fac_tipodocumento) AS ch_fac_tipodocumento
 , FIRST(ftfc.ch_fac_seriedocumento) AS ch_fac_seriedocumento
 , FIRST(ftfc.ch_fac_numerodocumento::INTEGER) AS ch_fac_numerodocumento
 , FIRST(ftfc.cli_codigo) AS cli_codigo
FROM
 fac_ta_factura_cabecera AS ftfc
 JOIN int_clientes AS client
  ON(ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 --AND ftfc.ch_almacen = '".$param['warehouse']."'
 AND ftfc.ch_fac_tipodocumento = '35'
 AND ftfc.ch_fac_anticipo != 'S'
GROUP BY ftfc.ch_fac_seriedocumento, ftfc.ch_fac_numerodocumento, ftfc.ch_fac_tiporecargo3;";

		$this->_error_log($param['tableName'].' - getDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$reg['transaccion'] = $reg['noperacion'];
			$originalHead[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'].' 00:00:00',
				'foliopref' => $reg['foliopref'],
				'u_exx_nroini' => (int)$reg['u_exx_nroini'],
				'u_exx_nrofin' => (int)$reg['u_exx_nrofin'],
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => $reg['transaccion'],
				'docentry' => NULL,
				'_turn' => $reg['_turn'],
				'nu_fac_recargo3' => $reg['nu_fac_recargo3'],
				'ch_fac_tiporecargo2' => $reg['ch_fac_tiporecargo2'],
				'ch_fac_tipodocumento' => $reg['ch_fac_tipodocumento'],
				'ch_fac_seriedocumento' => $reg['ch_fac_seriedocumento'],
				'ch_fac_numerodocumento' => $reg['ch_fac_numerodocumento'],
				'cli_codigo' => $reg['cli_codigo'],
			);
			$array_serie_turno[] = trim($reg['foliopref']) . "-" . trim($reg['_turn']);
		}

		//DESAGREGAR TRANSFERENCIAS GRATUITAS MANTENIENDO CONTINUIDAD DE CORRELATIVO
		//OBTENEMOS UN ARRAY CON SERIE Y TURNO DEL DETALLE, YA QUE LOS DATOS SON AGRUPADOS POR SERIE Y TURNO
		$array_serie_turno = array_unique($array_serie_turno);					
		//$i = 0; //Ya no debe arrancar en 0

		//RECORREMOS ARRAY DE SERIE Y TURNO
		foreach ($array_serie_turno as $key => $value) {
			$serie_turno       = $value;
			$inicio_agrupacion = true;					
			$i++;	
			
			//DENTRO DE CADA VALOR DENTRO DEL ARRAY, RECORREMOS LOS DATOS DE LA QUERY DETALLADA
			foreach ($originalHead as $key2 => $value2) {
				
				//SI ES EL PRIMER ELEMENTO DENTRO DEL ARRAY DE SERIES Y TURNO, OBTIENE EL DOCUMENTO INICIAL
				if($inicio_agrupacion == true){
					$noperacion   = $value2['ch_fac_tipodocumento'] . $value2['ch_fac_seriedocumento'] . $value2['ch_fac_numerodocumento'] . $value2['cli_codigo'];
					$u_exx_nroini = $value2['u_exx_nroini'];
					$transaccion  = $value2['transaccion'];
				} 

				//IGUALAMOS POR SERIE Y TURNO
				if(trim($serie_turno) == trim($originalHead[$key2]['foliopref']) ."-". trim($originalHead[$key2]['_turn'])){
					$inicio_agrupacion = false;										

					/**
					 * Transferencias gratuitas solo se realizan en oficina
					 */ 
					
					/**
					 * Tabla fac_ta_factura_cabecera
					 * Campos:
						- nu_fac_recargo3:
							0 = Registrado
							1 = Completado
							2 = Anulado
							3 = Completado Enviado
							4 = Completado Error (No se envió el documento a EBI -> SUNAT)
							5 = Anulado enviado
							6 = Anulado Error
					 */
					
					/** 
					 * - Valores (OCS) de tipos de impuesto:
									Impuesto                  | ch_fac_tiporecargo2    |    Valor de impuesto (S / N)
							----------------------------------------------------------------------------------------
							- Op. Gravadas                  =   vacío 				 =		S
							- Op. Exoneradas      ,          =   S 				 	 =		N
							- Op. Gratuitas                 =   T 				 	 =		S
							- Op. Gratuitas + Exoneradas    =   U  				 	 =		N
							- Op. Inafectas                 =   V  	 				 =		N
							- Op. Gratuitas + Inafectas     =   W  	 				 =		N
					 */
					if($value2['nu_fac_recargo3'] == 2 || $value2['nu_fac_recargo3'] == 5 || $value2['nu_fac_recargo3'] == 6 || trim($value2['ch_fac_tiporecargo2']) == "T"){ //ES DOCUMENTO ANULADO O ES TRANSFERENCIA GRATUITA
						$i++;
						$res[$i]['noperacion']   = $value2['noperacion'];
						$res[$i]['cardcode']     = $this->preLetterBPartner('C', $value2['cardcode']);
						$res[$i]['docdate']      = $value2['docdate'];
						$res[$i]['foliopref']    = $value2['foliopref'];
						$res[$i]['u_exx_nroini'] = (int)$value2['u_exx_nroini'];
						$res[$i]['u_exx_nrofin'] = (int)$value2['u_exx_nrofin'];
						$res[$i]['vatsum']       += (float)$value2['vatsum'];
						$res[$i]['doctotal']     += (float)$value2['doctotal'];					
						$res[$i]['estado']       = 'P';
						$res[$i]['errormsg']     = '';
						$res[$i]['transaccion']  = $value2['transaccion'];
						$res[$i]['docentry']     = NULL;
						$res[$i]['_turn']        = $value2['_turn'];
						$res[$i]['indicator']    = ( $value2['nu_fac_recargo3'] == 2 || $value2['nu_fac_recargo3'] == 5 || $value2['nu_fac_recargo3'] == 6 ) ? "AB" : "TG"; //QUE PASA CUANDO UNA TRANSFERENCIA GRATUITA SE ANULA? PASA QUE SE MANTIENE LA "T" PERO CON EL STATUS "2", "5" O "6"
						$i++;
						$inicio_agrupacion = true;
					}else{
						$res[$i]['noperacion']   = $noperacion;
						$res[$i]['cardcode']     = $this->preLetterBPartner('C', $value2['cardcode']);
						$res[$i]['docdate']      = $value2['docdate'];
						$res[$i]['foliopref']    = $value2['foliopref'];
						$res[$i]['u_exx_nroini'] = (int)$u_exx_nroini;
						$res[$i]['u_exx_nrofin'] = (int)$value2['u_exx_nrofin'];
						$res[$i]['vatsum']       += (float)$value2['vatsum'];
						$res[$i]['doctotal']     += (float)$value2['doctotal'];					
						$res[$i]['estado']       = 'P';
						$res[$i]['errormsg']     = '';
						$res[$i]['transaccion']  = $transaccion;
						$res[$i]['docentry']     = NULL;
						$res[$i]['_turn']        = $value2['_turn'];													
						$res[$i]['indicator']    = "BO";
					}								
				}
			}
		}

		foreach ($res as $key => $value) {
			//ESTO SE CALCULARA EN EL ARRAY RESULTANTE
			$c++;
			$this->ticketHead[$value['foliopref']][$value['_turn']] = array(
				'u_exx_nroini' => (int)$value['u_exx_nroini'],
				'u_exx_nrofin' => (int)$value['u_exx_nrofin'],
				'noperacion' => $value['noperacion'],
			);
			//CERRAR ESTO SE CALCULARA EN EL ARRAY RESULTANTE
		}
		//CERRAR DESAGREGAR TRANSFERENCIAS GRATUITAS MANTENIENDO CONTINUIDAD DE CORRELATIVO

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'documentHeadTicket',
			'documentHeadTicket' => $res,
			'count' => $c,
		);
	}

	/**
	 * Boletas - Detalle
	 */
	public function getDocumentDetailTicket($param) {
		global $sqlca;

		$res = array();

		$sql = "
SELECT
 PT.es || TO_CHAR(FIRST(PT.dia), 'DDMMYY') || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 SUM(PT.cantidad) AS quantity,

 ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax'].", 2) AS price,
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt,

 SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,--ROUND
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,

 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,
 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,
 COALESCE(FIRST(employe.ch_codigo_trabajador)) AS extempno,

 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE '' END AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(SUM(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 '' AS u_exc_nrotarjmag--pendiente

 , FIRST(pt.trans) AS u_exc_ticket

 , (string_to_array(FIRST(pt.usr), '-'))[1] AS _serie
 , ((string_to_array(FIRST(pt.usr), '-'))[2]) AS _number
 , FIRST(pt.turno) AS _turn,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv
FROM
".$param['pos_trans']." AS PT
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento,
 (PT.importe - PT.igv) AS importe_descuento_sigv,
 PT.importe AS importe_descuento_igv
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo = 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_sin_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo != 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)

WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 AND pt.tm = 'V'
 --AND PT.es = '".$param['warehouse']."'
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo
ORDER BY _serie, _number, _turn;";

		$this->_error_log($param['tableName'].' - getDocumentDetailTicket: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);
			if ($tmpDoc == $noperacion) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->ticketDetail[$reg['_serie']][$reg['_number']] = $ci;
			
			// Nuevo
			$this->invoiceSaleDetail[$reg['_serie']][$reg['_number']][$this->cleanStr($reg['itemcode'])] = array(
				'item' => $ci,
				'noperacion' => $noperacion,
			);

			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				//'noperacion' => $noperacion.' | '.$reg['_serie'].'-'.$reg['_number'],
				'noperacion' => $noperacion,
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nrotarjmag' => '',//***-pendiente
				'u_exc_serie' => $reg['_serie'],
				'u_exc_numero' => $reg['_number'],
				'u_exc_ticket' => $reg['u_exc_ticket'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $noperacion;
		}

		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento::INTEGER||ftfc.cli_codigo AS noperacion,
 ftfd.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 ftfd.nu_fac_cantidad AS quantity,
 ROUND((ftfd.nu_fac_precio / ".$param['tax']."), 2) AS price,
 ftfd.nu_fac_precio / ".$param['tax']." AS _price,
 ftfd.nu_fac_descuento1 AS _discprcnt,
 '".$param['sap_tax_code']."' AS taxcode,
 SAPLINEA.sap_codigo AS ocrcode,
 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat,
 '' AS extempno,
 '' AS u_exc_dispensador,
 '' AS u_exc_caja,
 '' AS u_exc_manguera,
 ftfc.ch_fac_tiporecargo3 AS u_exc_turno,
 TO_CHAR(ftfc.dt_fac_fecha, 'HH12:MI:SS') AS u_exc_hora,
 '' AS u_exc_placa,
 '' AS u_exc_km,
 0 AS u_exc_bonus,
 '' AS u_exc_nrotarjbonus,
 '' AS u_exc_nrotarjmag--pendiente

 , ftfc.ch_fac_seriedocumento AS _serie
 , ftfc.ch_fac_numerodocumento AS _number
 , ftfc.ch_fac_tiporecargo3 AS _turn,
 ROUND((ftfd.nu_fac_descuento1), 4) AS desc_sinigv,
 ROUND((ftfd.nu_fac_descuento1 * ".$param['tax']."), 4) AS desc_igv
FROM
 fac_ta_factura_cabecera AS ftfc
 JOIN fac_ta_factura_detalle AS ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
 JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = ftfc.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
 JOIN int_articulos AS PRO ON (PRO.art_codigo = ftfd.art_codigo)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
 JOIN int_clientes AS client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 --AND ftfc.ch_almacen = '".$param['warehouse']."'
 AND ftfc.ch_fac_tipodocumento = '35'
 AND ftfc.ch_fac_anticipo != 'S'
ORDER BY _serie, _number, _turn;";

		$this->_error_log($param['tableName'].' - getDocumentDetailTicket: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);
			if ($tmpDoc == $noperacion) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->ticketDetail[$reg['_serie']][$reg['_number']] = $ci;
			
			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				//'noperacion' => $noperacion.' | '.$reg['_serie'].'-'.$reg['_number'],
				'noperacion' => $noperacion,
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nrotarjmag' => '',//***-pendiente
				'u_exc_serie' => $reg['_serie'],
				'u_exc_numero' => $reg['_number'],
				'u_exc_ticket' => 0,
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $noperacion;
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'documentDetailTicket',
			'documentDetailTicket' => $res,
			'count' => $c,
		);
	}

	/**
	 * Boletas - Detalle
	 */
	public function getDocumentDetailTicketDistinguirDocumentosMayores700AndWithFechaEmision($param) {
		global $sqlca;

		$cantidad = 700;
		$res = array();

		$sql = "
SELECT
 PT.es || TO_CHAR(FIRST(PT.dia), 'DDMMYY') || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 SUM(PT.cantidad) AS quantity,

 ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax'].", 2) AS price,
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt,

 SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,--ROUND
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,

 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,
 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,
 COALESCE(FIRST(employe.ch_codigo_trabajador)) AS extempno,

 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE '' END AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(SUM(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 '' AS u_exc_nrotarjmag--pendiente

 , FIRST(pt.trans) AS u_exc_ticket

 , (string_to_array(FIRST(pt.usr), '-'))[1] AS _serie
 , ((string_to_array(FIRST(pt.usr), '-'))[2]) AS _number
 , FIRST(pt.turno) AS _turn,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv,
 FIRST(pt.fecha) AS u_exc_fechaemi --Requerimiento fecha emision
FROM
".$param['pos_trans']." AS PT
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento,
 (PT.importe - PT.igv) AS importe_descuento_sigv,
 PT.importe AS importe_descuento_igv
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo = 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_sin_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo != 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)

WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 AND pt.tm = 'V'
 --AND PT.es = '".$param['warehouse']."'
 AND pt.importe < ".$cantidad."
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo
ORDER BY _serie, _number, _turn;";

		$this->_error_log($param['tableName'].' - getDocumentDetailTicket: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);
			$noperacion = $this->getNOperacionTicketDetail($reg, $noperacion);
			if ($tmpDoc == $noperacion) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->ticketDetail[$reg['_serie']][$reg['_number']] = $ci;
			
			// Nuevo
			$this->invoiceSaleDetail[$reg['_serie']][$reg['_number']][$this->cleanStr($reg['itemcode'])] = array(
				'item' => $ci,
				'noperacion' => $noperacion,
			);

			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				//'noperacion' => $noperacion.' | '.$reg['_serie'].'-'.$reg['_number'],
				'noperacion' => $noperacion,
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nrotarjmag' => '',//***-pendiente
				'u_exc_serie' => $reg['_serie'],
				'u_exc_numero' => $reg['_number'],
				'u_exc_ticket' => $reg['u_exc_ticket'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
				'u_exc_fechaemi' => substr($reg['u_exc_fechaemi'],0,19),
			);
			$tmpDoc = $noperacion;
		}

		$sql = "
SELECT
 PT.es || TO_CHAR(FIRST(PT.dia), 'DDMMYY') || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 SUM(PT.cantidad) AS quantity,

 ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax'].", 2) AS price,
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt,

 SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,--ROUND
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,

 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,
 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,
 COALESCE(FIRST(employe.ch_codigo_trabajador)) AS extempno,

 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE '' END AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(SUM(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 '' AS u_exc_nrotarjmag--pendiente

 , FIRST(pt.trans) AS u_exc_ticket

 , (string_to_array(FIRST(pt.usr), '-'))[1] AS _serie
 , ((string_to_array(FIRST(pt.usr), '-'))[2]) AS _number
 , FIRST(pt.turno) AS _turn,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv,
 FIRST(pt.fecha) AS u_exc_fechaemi --Requerimiento fecha emision
FROM
".$param['pos_trans']." AS PT
--LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (PT.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PT.codigo)
LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento,
 (PT.importe - PT.igv) AS importe_descuento_sigv,
 PT.importe AS importe_descuento_igv
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo = 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_sin_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 --AND PT.td ='N'
 ---AND PT.tipo = 'M'
 AND PT.grupo != 'D'
 ---AND PT.es = '".$param['warehouse']."'
) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)

WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 AND pt.tm = 'V'
 --AND PT.es = '".$param['warehouse']."'
 AND pt.importe >= ".$cantidad."
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo
ORDER BY _serie, _number, _turn;";

		$this->_error_log($param['tableName'].' - getDocumentDetailTicket: '.$sql.' [LINE: '.__LINE__.']');
		//$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);
			$noperacion = $this->getNOperacionTicketDetail($reg, $noperacion);
			if ($tmpDoc == $noperacion) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->ticketDetail[$reg['_serie']][$reg['_number']] = $ci;
			
			// Nuevo
			$this->invoiceSaleDetail[$reg['_serie']][$reg['_number']][$this->cleanStr($reg['itemcode'])] = array(
				'item' => $ci,
				'noperacion' => $noperacion,
			);

			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				//'noperacion' => $noperacion.' | '.$reg['_serie'].'-'.$reg['_number'],
				'noperacion' => $noperacion,
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nrotarjmag' => '',//***-pendiente
				'u_exc_serie' => $reg['_serie'],
				'u_exc_numero' => $reg['_number'],
				'u_exc_ticket' => $reg['u_exc_ticket'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
				'u_exc_fechaemi' => substr($reg['u_exc_fechaemi'],0,19),
			);
			$tmpDoc = $noperacion;
		}

		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento::INTEGER||ftfc.cli_codigo AS noperacion,
 ftfd.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 ftfd.nu_fac_cantidad AS quantity,
 ROUND((ftfd.nu_fac_precio / ".$param['tax']."), 2) AS price,
 ftfd.nu_fac_precio / ".$param['tax']." AS _price,
 ftfd.nu_fac_descuento1 AS _discprcnt,
 '".$param['sap_tax_code']."' AS taxcode,
 SAPLINEA.sap_codigo AS ocrcode,
 SAPCC.sap_codigo AS ocrcode2,
 ROUND(ftfd.nu_fac_precio, 2) AS priceafvat,
 '' AS extempno,
 '' AS u_exc_dispensador,
 '' AS u_exc_caja,
 '' AS u_exc_manguera,
 ftfc.ch_fac_tiporecargo3 AS u_exc_turno,
 TO_CHAR(ftfc.dt_fac_fecha, 'HH12:MI:SS') AS u_exc_hora,
 '' AS u_exc_placa,
 '' AS u_exc_km,
 0 AS u_exc_bonus,
 '' AS u_exc_nrotarjbonus,
 '' AS u_exc_nrotarjmag--pendiente

 , ftfc.ch_fac_seriedocumento AS _serie
 , ftfc.ch_fac_numerodocumento AS _number
 , ftfc.ch_fac_tiporecargo3 AS _turn,
 ROUND((ftfd.nu_fac_descuento1), 4) AS desc_sinigv,
 ROUND((ftfd.nu_fac_descuento1 * ".$param['tax']."), 4) AS desc_igv,
 ftfc.dt_fac_fecha AS u_exc_fechaemi --Requerimiento fecha emision
FROM
 fac_ta_factura_cabecera AS ftfc
 JOIN fac_ta_factura_detalle AS ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
 JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = ftfc.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
 JOIN int_articulos AS PRO ON (PRO.art_codigo = ftfd.art_codigo)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
 JOIN int_clientes AS client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 --AND ftfc.ch_almacen = '".$param['warehouse']."'
 AND ftfc.ch_fac_tipodocumento = '35'
 AND ftfc.ch_fac_anticipo != 'S'
ORDER BY _serie, _number, _turn;";

		$this->_error_log($param['tableName'].' - getDocumentDetailTicket: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$ci = 1;
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);
			if ($tmpDoc == $noperacion) {
				$ci++;
			} else {
				$ci = 1;
			}

			$this->ticketDetail[$reg['_serie']][$reg['_number']] = $ci;
			
			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				//'noperacion' => $noperacion.' | '.$reg['_serie'].'-'.$reg['_number'],
				'noperacion' => $noperacion,
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nrotarjmag' => '',//***-pendiente
				'u_exc_serie' => $reg['_serie'],
				'u_exc_numero' => $reg['_number'],
				'u_exc_ticket' => 0,
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
				'u_exc_fechaemi' => substr($reg['u_exc_fechaemi'],0,19),
			);
			$tmpDoc = $noperacion;
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'documentDetailTicket',
			'documentDetailTicket' => $res,
			'count' => $c,
		);
	}

	/**
	 * Pago Venta Boleta
	 */
	public function getPaymentDocumentTicket($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 PT.es::INTEGER || PT.caja || PT.trans AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 ROUND(SUM(PT.importe), 4) AS doctotal,
 '' AS moneda, -- DEJAR VACIO
 FIRST(sap_cash_fund.sap_codigo) AS fecuenta,
 FIRST(sap_currency.sap_codigo) AS femoneda,
 1 AS fetc,
 ROUND(SUM(PT.importe), 4) AS femonto,--confirmar si solo es cuando es efectivo
 0 AS fecuentav,
 FIRST(sap_card.sap_codigo) AS tccod,
 '' AS tccuenta,-- DEJAR VACIO
 trim(FIRST(text1)) AS tcnumero,
 trim(FIRST(text1)) AS tcid,
 TO_CHAR(FIRST(PT.dia), 'MM/YY') AS tcvalido,
 ROUND(SUM(PT.importe), 4) AS tcmonto,--confirmar si solo es cuando es tarjeta
 FIRST(PT.at) AS _card_type,
 (string_to_array(FIRST(pt.usr), '-'))[1] AS _serie,
 ((string_to_array(FIRST(pt.usr), '-'))[2]) AS _number,
 FIRST(pt.turno) AS _turn--19
FROM
 ".$param['pos_trans']." AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND PT.td ='N'
  AND PT.grupo = 'D'
  --AND PT.es = '".$param['warehouse']."'
 ) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
 LEFT JOIN int_tabla_general card ON (trim(pt.at) = substring(card.tab_elemento,6,6) AND card.tab_tabla ='95' AND card.tab_elemento != '000000')
 LEFT JOIN sap_mapeo_tabla_detalle sap_card ON (sap_card.id_tipo_tabla = 4 AND sap_card.opencomb_codigo = card.tab_elemento)
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 AND pt.tm = 'V'
 --AND pt.es = '".$param['warehouse']."'
 AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;
 		";

		$this->_error_log($param['tableName'].' - getPaymentDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);

			$paymentType = 0;
			if ($reg['_card_type'] != NULL AND $reg['_card_type'] != '') {
				//tarjeta de credito
				$paymentType = 1;
			}

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacion'],
				'noperacion' => $noperacion,
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				//27/08/2018
				'foliopref' => $reg['_serie'],
				'folionum' => $reg['_number'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		//Documentos manuales(Boletas)
		$sql = "
SELECT
 --ftfd.ch_fac_tipodocumento||ftfd.ch_fac_seriedocumento||ftfd.ch_fac_numerodocumento::INTEGER||ftfd.cli_codigo||ftfd.art_codigo AS noperacionpe,
 DISTINCT ftfd.ch_fac_tipodocumento||ftfd.ch_fac_seriedocumento||ftfd.ch_fac_numerodocumento::INTEGER||ftfd.cli_codigo||SUBSTRING(ftfd.art_codigo, LENGTH(ftfd.art_codigo), 1) AS noperacionpe,
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento::INTEGER||ftfc.cli_codigo AS noperacion,
 client.cli_codigo AS cardcode,
 ftfc.dt_fac_fecha AS docdate,
 ftfc.nu_fac_valortotal AS doctotal,
 '' AS moneda,
 ftfc.ch_fac_forma_pago AS _type_payment_id,
 sap_cash_fund.sap_codigo AS fecuenta,
 sap_currency.sap_codigo AS femoneda,
 1 AS fetc,
 ftfc.nu_fac_valortotal AS femonto,
 0 AS fecuentav,
 '' AS tccod,
 '' AS tccuenta,
 '' AS tcnumero,
 '' AS tcid,
 TO_CHAR(ftfc.dt_fac_fecha,'DD/MM') AS tcvalido,
 ftfc.nu_fac_valortotal AS tcmonto,
 '' AS bcuenta,
 '' AS breferencia,
 '' AS bfecha,
 '' AS bmonto,

 ftfc.nu_fac_impuesto1 AS tax_total,
 (util_fn_igv()/100) AS cnf_igv_ocs,
 ftfc.nu_fac_valorbruto AS taxable_operations,
 ftfc.nu_fac_valortotal AS grand_total,
 CASE WHEN ftfc.ch_fac_tiporecargo2 IS NULL OR ftfc.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
 WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 = 0 THEN 1 -- EXO
 WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(ftfc.nu_fac_descuento1, 0) AS disc

 , ftfc.ch_fac_seriedocumento AS _serie
 , ftfc.ch_fac_numerodocumento AS _number
 , ftfc.ch_fac_tiporecargo3 AS _turn
FROM
 fac_ta_factura_cabecera ftfc
 JOIN fac_ta_factura_detalle ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND ftfc.nu_fac_recargo3 IN (3, 5)
 AND ftfc.ch_fac_tipodocumento = '35'
ORDER BY _serie, _number, _turn;
";

		$this->_error_log($param['tableName'].' - getPaymentDocumentTicket M: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}

		while ($reg = $sqlca->fetchRow()) {
			$c++;
			//0: efectivo, 1: tarjeta de credito, 2: banco
			$paymentType = 0;
			if ($reg['_type_payment_id'] == '02') {
				//tarjeta de credito
				$paymentType = 1;
			}

			$data = $this->calcAmounts($reg);

			$reg['doctotal'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));
			$reg['femonto'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));
			$reg['tcmonto'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacionpe'],
				'noperacion' => $reg['noperacion'],//debe hacer referencia con la cabecera de la guia
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],
				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],
				'tccod' => $reg['tccod'],
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],//14
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],
				/*
				'bcuenta' => $reg['bcuenta'],
				'breferencia' => $reg['breferencia'],
				'bfecha' => $reg['bfecha'],
				'bmonto' => $reg['bmonto'],
				*/
				'_serie' => $reg['_serie'],
				'_number' => $reg['_number'],				
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'paymentDocumentTicket',
			'paymentDocumentTicket' => $res,
			'count' => $c,
		);
	}

	/**
	 * Pago Venta Boleta
	 */
	public function getPaymentDocumentTicketDistinguirDocumentosMayores700AndGroupByTurnoAndEfectivo($param) {
		global $sqlca;

		$cantidad = 700;
		$res = array();
		
		//Documentos pos_trans(Boletas) - Documentos AGRUPADOS POR TURNO, FORMA DE PAGO Y SERIE de pagos realizados con efectivo
		$sql = "
SELECT
 FIRST(PT.es::INTEGER) || FIRST(PT.caja) || FIRST(PT.trans) AS noperacion,
 --FIRST(pt.ruc) AS cardcode,
 'C00099999999' AS cardcode, --JEL, cliente por defecto
 FIRST(pt.dia) AS docdate,
 ROUND(SUM(PT.importe), 4) AS doctotal,
 '' AS moneda, -- DEJAR VACIO
 FIRST(sap_cash_fund.sap_codigo) AS fecuenta,
 FIRST(sap_currency.sap_codigo) AS femoneda,
 1 AS fetc,
 ROUND(SUM(PT.importe), 4) AS femonto,--confirmar si solo es cuando es efectivo
 0 AS fecuentav,
 FIRST(sap_card.sap_codigo) AS tccod,
 '' AS tccuenta,-- DEJAR VACIO
 trim(FIRST(text1)) AS tcnumero,
 trim(FIRST(text1)) AS tcid,
 TO_CHAR(FIRST(PT.dia), 'MM/YY') AS tcvalido,
 ROUND(SUM(PT.importe), 4) AS tcmonto,--confirmar si solo es cuando es tarjeta
 FIRST(PT.at) AS _card_type,
 (string_to_array(FIRST(pt.usr), '-'))[1] AS _serie,
 ((string_to_array(FIRST(pt.usr), '-'))[2]) AS _number,
 FIRST(pt.turno) AS _turn--19
FROM
 ".$param['pos_trans']." AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND PT.td ='N'
  AND PT.grupo = 'D'
  --AND PT.es = '".$param['warehouse']."'
 ) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
 LEFT JOIN int_tabla_general card ON (trim(pt.at) = substring(card.tab_elemento,6,6) AND card.tab_tabla ='95' AND card.tab_elemento != '000000')
 LEFT JOIN sap_mapeo_tabla_detalle sap_card ON (sap_card.id_tipo_tabla = 4 AND sap_card.opencomb_codigo = card.tab_elemento)
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 AND pt.tm = 'V'
 AND pt.fpago = '1' --Pago realizado con efectivo
 --AND pt.es = '".$param['warehouse']."'
 AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
 AND pt.importe < ".$cantidad."
GROUP BY
 -- PT.es,
 -- PT.caja,
 -- PT.trans,
 (string_to_array(pt.usr, '-'))[1], --JEL, cambiamos el orden
 pt.turno, --JEL, cambiamos el orden
 pt.fpago --JEL, cambiamos el orden
ORDER BY
 (string_to_array(pt.usr, '-'))[1], 
 pt.turno;
 		";

		$this->_error_log($param['tableName'].' - getPaymentDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);
			$noperacion = $this->getNOperacionTicketDetail($reg, $noperacion);

			$paymentType = 0;
			if ($reg['_card_type'] != NULL AND $reg['_card_type'] != '') {
				//tarjeta de credito
				$paymentType = 1;
			}

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacion'],
				'noperacion' => $noperacion,
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				//27/08/2018
				'foliopref' => $reg['_serie'],
				'folionum' => $reg['_number'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		$sql = "
SELECT
 PT.es::INTEGER || PT.caja || PT.trans AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 ROUND(SUM(PT.importe), 4) AS doctotal,
 '' AS moneda, -- DEJAR VACIO
 FIRST(sap_cash_fund.sap_codigo) AS fecuenta,
 FIRST(sap_currency.sap_codigo) AS femoneda,
 1 AS fetc,
 ROUND(SUM(PT.importe), 4) AS femonto,--confirmar si solo es cuando es efectivo
 0 AS fecuentav,
 FIRST(sap_card.sap_codigo) AS tccod,
 '' AS tccuenta,-- DEJAR VACIO
 trim(FIRST(text1)) AS tcnumero,
 trim(FIRST(text1)) AS tcid,
 TO_CHAR(FIRST(PT.dia), 'MM/YY') AS tcvalido,
 ROUND(SUM(PT.importe), 4) AS tcmonto,--confirmar si solo es cuando es tarjeta
 FIRST(PT.at) AS _card_type,
 (string_to_array(FIRST(pt.usr), '-'))[1] AS _serie,
 ((string_to_array(FIRST(pt.usr), '-'))[2]) AS _number,
 FIRST(pt.turno) AS _turn--19
FROM
 ".$param['pos_trans']." AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND PT.td ='N'
  AND PT.grupo = 'D'
  --AND PT.es = '".$param['warehouse']."'
 ) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
 LEFT JOIN int_tabla_general card ON (trim(pt.at) = substring(card.tab_elemento,6,6) AND card.tab_tabla ='95' AND card.tab_elemento != '000000')
 LEFT JOIN sap_mapeo_tabla_detalle sap_card ON (sap_card.id_tipo_tabla = 4 AND sap_card.opencomb_codigo = card.tab_elemento)
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 AND pt.tm = 'V'
 AND pt.fpago = '1'
 --AND pt.es = '".$param['warehouse']."'
 AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
 AND pt.importe >= ".$cantidad."
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;
 		";

		$this->_error_log($param['tableName'].' - getPaymentDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		//$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);
			$noperacion = $this->getNOperacionTicketDetail($reg, $noperacion);

			$paymentType = 0;
			if ($reg['_card_type'] != NULL AND $reg['_card_type'] != '') {
				//tarjeta de credito
				$paymentType = 1;
			}

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacion'],
				'noperacion' => $noperacion,
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				//27/08/2018
				'foliopref' => $reg['_serie'],
				'folionum' => $reg['_number'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		//Documentos pos_trans(Boletas) - Documentos NO AGRUPADOS POR TURNO, FORMA DE PAGO Y SERIE de pagos realizados con tarjetas de credito
		$sql = "
SELECT
 PT.es::INTEGER || PT.caja || PT.trans AS noperacion,
 --FIRST(pt.ruc) AS cardcode,
 'C00099999999' AS cardcode, --JEL, cliente por defecto
 FIRST(pt.dia) AS docdate,
 ROUND(SUM(PT.importe), 4) AS doctotal,
 '' AS moneda, -- DEJAR VACIO
 FIRST(sap_cash_fund.sap_codigo) AS fecuenta,
 FIRST(sap_currency.sap_codigo) AS femoneda,
 1 AS fetc,
 ROUND(SUM(PT.importe), 4) AS femonto,--confirmar si solo es cuando es efectivo
 0 AS fecuentav,
 FIRST(sap_card.sap_codigo) AS tccod,
 '' AS tccuenta,-- DEJAR VACIO
 trim(FIRST(text1)) AS tcnumero,
 trim(FIRST(text1)) AS tcid,
 TO_CHAR(FIRST(PT.dia), 'MM/YY') AS tcvalido,
 ROUND(SUM(PT.importe), 4) AS tcmonto,--confirmar si solo es cuando es tarjeta
 FIRST(PT.at) AS _card_type,
 (string_to_array(FIRST(pt.usr), '-'))[1] AS _serie,
 ((string_to_array(FIRST(pt.usr), '-'))[2]) AS _number,
 FIRST(pt.turno) AS _turn--19
FROM
 ".$param['pos_trans']." AS pt
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND PT.td ='N'
  AND PT.grupo = 'D'
  --AND PT.es = '".$param['warehouse']."'
 ) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
 LEFT JOIN int_tabla_general card ON (trim(pt.at) = substring(card.tab_elemento,6,6) AND card.tab_tabla ='95' AND card.tab_elemento != '000000')
 LEFT JOIN sap_mapeo_tabla_detalle sap_card ON (sap_card.id_tipo_tabla = 4 AND sap_card.opencomb_codigo = card.tab_elemento)
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
WHERE
 pt.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND pt.td IN ('B')
 AND pt.tm = 'V'
 AND pt.fpago = '2' --Pago realizado con tarjetas de credito
 --AND pt.es = '".$param['warehouse']."'
 AND pt.rendi_gln IS NULL --JEL, quitamos documentos originales que hacen referencia a notas de credito
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;
 		";

		$this->_error_log($param['tableName'].' - getPaymentDocumentTicket: '.$sql.' [LINE: '.__LINE__.']');
		//$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$noperacion = $this->getNOperacionTicket($reg);
			$noperacion = $this->getNOperacionTicketDetail($reg, $noperacion);

			$paymentType = 0;
			if ($reg['_card_type'] != NULL AND $reg['_card_type'] != '') {
				//tarjeta de credito
				$paymentType = 1;
			}

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacion'],
				'noperacion' => $noperacion,
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],

				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],

				'tccod' => $reg['tccod'],
				//'tccod' => NULL,//***temporalmente
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],

				//27/08/2018
				'foliopref' => $reg['_serie'],
				'folionum' => $reg['_number'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		//Documentos manuales(Boletas)
		$sql = "
SELECT
 --ftfd.ch_fac_tipodocumento||ftfd.ch_fac_seriedocumento||ftfd.ch_fac_numerodocumento::INTEGER||ftfd.cli_codigo||ftfd.art_codigo AS noperacionpe,
 DISTINCT ftfd.ch_fac_tipodocumento||ftfd.ch_fac_seriedocumento||ftfd.ch_fac_numerodocumento::INTEGER||ftfd.cli_codigo||SUBSTRING(ftfd.art_codigo, LENGTH(ftfd.art_codigo), 1) AS noperacionpe,
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento::INTEGER||ftfc.cli_codigo AS noperacion,
 client.cli_codigo AS cardcode,
 ftfc.dt_fac_fecha AS docdate,
 ftfc.nu_fac_valortotal AS doctotal,
 '' AS moneda,
 ftfc.ch_fac_forma_pago AS _type_payment_id,
 sap_cash_fund.sap_codigo AS fecuenta,
 sap_currency.sap_codigo AS femoneda,
 1 AS fetc,
 ftfc.nu_fac_valortotal AS femonto,
 0 AS fecuentav,
 '' AS tccod,
 '' AS tccuenta,
 '' AS tcnumero,
 '' AS tcid,
 TO_CHAR(ftfc.dt_fac_fecha,'DD/MM') AS tcvalido,
 ftfc.nu_fac_valortotal AS tcmonto,
 '' AS bcuenta,
 '' AS breferencia,
 '' AS bfecha,
 '' AS bmonto,

 ftfc.nu_fac_impuesto1 AS tax_total,
 (util_fn_igv()/100) AS cnf_igv_ocs,
 ftfc.nu_fac_valorbruto AS taxable_operations,
 ftfc.nu_fac_valortotal AS grand_total,
 CASE WHEN ftfc.ch_fac_tiporecargo2 IS NULL OR ftfc.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
 WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 = 0 THEN 1 -- EXO
 WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 > 0 THEN 2 -- TG
 END AS typetax,
 COALESCE(ftfc.nu_fac_descuento1, 0) AS disc

 , ftfc.ch_fac_seriedocumento AS _serie
 , ftfc.ch_fac_numerodocumento AS _number
 , ftfc.ch_fac_tiporecargo3 AS _turn
FROM
 fac_ta_factura_cabecera ftfc
 JOIN fac_ta_factura_detalle ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
 LEFT JOIN sap_mapeo_tabla_detalle sap_cash_fund ON (sap_cash_fund.id_tipo_tabla = 5 AND sap_cash_fund.opencomb_codigo = '01')
 LEFT JOIN sap_mapeo_tabla_detalle sap_currency ON (sap_currency.id_tipo_tabla = 6 AND sap_currency.opencomb_codigo = '01')
WHERE
 ftfc.dt_fac_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND ftfc.nu_fac_recargo3 IN (3, 5)
 AND ftfc.ch_fac_tipodocumento = '35'
ORDER BY _serie, _number, _turn;
";

		$this->_error_log($param['tableName'].' - getPaymentDocumentTicket M: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}

		while ($reg = $sqlca->fetchRow()) {
			$c++;
			//0: efectivo, 1: tarjeta de credito, 2: banco
			$paymentType = 0;
			if ($reg['_type_payment_id'] == '02') {
				//tarjeta de credito
				$paymentType = 1;
			}

			$data = $this->calcAmounts($reg);

			$reg['doctotal'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));
			$reg['femonto'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));
			$reg['tcmonto'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			if ($paymentType == 0) {
				//efectivo
				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 1) {
				//tarjeta de credito
				$reg['tccod'] = (int)$reg['tccod'];

				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['bcuenta'] = '';
				$reg['breferencia'] = '';
				$reg['bfecha'] = '';
				$reg['bmonto'] = NULL;

			} else if ($paymentType == 2) {
				//banco
				$reg['fecuenta'] = '';
				$reg['femoneda'] = '';
				$reg['fetc'] = 0.0;
				$reg['femonto'] = 0.0;
				$reg['fecuentav'] = '';

				$reg['tccod'] = NULL;
				$reg['tccuenta'] = '';
				$reg['tcnumero'] = '';
				$reg['tcid'] = '';
				$reg['tcvalido'] = '';
				$reg['tcmonto'] = '';
			}

			$res[] = array(
				'noperacionpe' => $reg['noperacionpe'],
				'noperacion' => $reg['noperacion'],//debe hacer referencia con la cabecera de la guia
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'doctotal' => (float)$reg['doctotal'],
				'moneda' => $reg['moneda'],
				'fecuenta' => $reg['fecuenta'],
				'femoneda' => $reg['femoneda'],
				'fetc' => (float)$reg['fetc'],
				'femonto' => (float)$reg['femonto'],
				'fecuentav' => $reg['fecuentav'],
				'tccod' => $reg['tccod'],
				'tccuenta' => $reg['tccuenta'],
				'tcnumero' => $reg['tcnumero'],//14
				'tcid' => $reg['tcid'],
				'tcvalido' => $reg['tcvalido'],
				'tcmonto' => (float)$reg['tcmonto'],
				/*
				'bcuenta' => $reg['bcuenta'],
				'breferencia' => $reg['breferencia'],
				'bfecha' => $reg['bfecha'],
				'bmonto' => $reg['bmonto'],
				*/
				'_serie' => $reg['_serie'],
				'_number' => $reg['_number'],				
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'paymentDocumentTicket',
			'paymentDocumentTicket' => $res,
			'count' => $c,
		);
	}

	/**
	 * Contometros
	 * Pendiente conversion GLP: 'LT' a 'GAL'
	 */
	public function getContometer($param) {
		global $sqlca;

		$res = array();
		//Query NO GNV
		$sql = "SELECT
 SURTIDOR.ch_sucursal || PC.cnt AS noperacion,
 PC.dia AS u_exc_fecha,
 PC.turno AS u_exc_turno,
 PARTE.ch_codigocombustible AS u_exc_articulo,
 (CASE
  WHEN PC.turno = 1 THEN PCANT.Nu_Contometro_Inicial_Soles
  WHEN PC.turno > 1 THEN
   (SELECT cnt_val FROM pos_contometros WHERE dia = PC.dia AND num_lado = PC.num_lado AND manguera = PC.manguera AND turno = PC.turno - integer '1')
 END) AS u_exc_continicial,
 PC.cnt_val AS u_exc_contfinal,
 PC.manguera AS u_exc_manguera,
 (CASE
  WHEN PC.turno = 1 THEN PCANT.Nu_Contometro_Inicial_Cantidad
  WHEN PC.turno > 1 THEN
   (SELECT cnt_vol FROM pos_contometros WHERE dia = PC.dia AND num_lado = PC.num_lado AND manguera = PC.manguera AND turno = PC.turno - integer '1')
 END) AS u_exc_continicialgal,
 PC.cnt_vol AS u_exc_contfinalgal,
 PC.num_lado AS u_exc_lado,

 '' AS u_exc_caja,
 '' AS u_exc_cont,
 '' AS u_exc_tipo,

 SAPCC.sap_codigo AS ocrcode

FROM
 pos_contometros AS PC
 LEFT JOIN comb_ta_surtidores AS SURTIDOR ON (num_lado = SURTIDOR.ch_numerolado::INTEGER AND PC.manguera = SURTIDOR.nu_manguera)
 LEFT JOIN comb_ta_contometros AS PARTE ON (dia = PARTE.dt_fechaparte AND PARTE.ch_sucursal= SURTIDOR.ch_sucursal AND PARTE.ch_surtidor = SURTIDOR.ch_surtidor)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = SURTIDOR.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
 LEFT JOIN (
  SELECT
   dia + integer '1' dia,
   turno,
   num_lado,
   manguera,
   MAX(cnt_val) AS Nu_Contometro_Inicial_Soles,
   MAX(cnt_vol) AS Nu_Contometro_Inicial_Cantidad
  FROM
   pos_contometros
  WHERE
   dia = (date '".$param['initial_date']."' - integer '1')
   AND turno = (
    SELECT
     MAX(turno)
    FROM
     pos_contometros
    WHERE
     dia = (date '".$param['initial_date']."' - integer '1')
   )
   GROUP BY
   1,2,3,4
 ) AS PCANT USING(dia,num_lado,manguera)
WHERE
 PC.dia BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."' AND PARTE.dt_fechaparte BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
ORDER BY
 PC.dia,PC.turno,PC.num_lado,PC.manguera;";

		$this->_error_log($param['tableName'].' - getContometer (NO GNV): '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'u_exc_fecha' => $reg['u_exc_fecha'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_articulo' => $reg['u_exc_articulo'],
				'u_exc_continicial' => $reg['u_exc_continicial'],
				'u_exc_contfinal' => $reg['u_exc_contfinal'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'ocrcode' => $reg['ocrcode'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_lado' => $reg['u_exc_lado'],
				'u_exc_cont' => $reg['u_exc_cont'],
				'u_exc_tipo' => '2',
				'u_exc_continicialgal' => $reg['u_exc_continicialgal'],
				'u_exc_contfinalgal' => $reg['u_exc_contfinalgal'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
			);
		}

		//Query GNV
		$sql = "SELECT
 gnv.comb_liquidaciongnv_id AS noperacion,
 gnv.dt_fecha AS u_exc_fecha,
 '' AS u_exc_continicial,--falta
 '' AS u_exc_contfinal,--falta
 SAPCC.sap_codigo AS ocrcode,
 '' AS u_exc_cont,--falta
 gnv.contometro_inicial AS u_exc_continicialgal,
 gnv.contometro_final AS u_exc_contfinalgal

 , COALESCE(gnv.tot_surtidor_soles, 0) AS _grand_total
 , COALESCE(gnv.tot_afericion, 0) AS _test_dispatch
 , gnv.tot_cantidad AS _qty_total
FROM comb_liquidaciongnv gnv
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = gnv.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
WHERE gnv.dt_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."';";

		$this->_error_log($param['tableName'].' - getContometer (GNV): '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$testDispatch = $reg['_test_dispatch'] == NULL OR $reg['_test_dispatch'] <= 0 ? 0 : $reg['_test_dispatch'];
			$qty_sale = $reg['_qty_total'];
			$total_sale = $reg['_grand_total'] - $testDispatch;
			$price_unit = $total_sale / $qty_sale;
			$price_initial = $price_unit * $reg['u_exc_continicialgal'];
			$price_final = $price_unit * $reg['u_exc_contfinalgal'];

			$u_exc_continicialgal = $this->converterUM(array('type' => 1, 'co' => $reg['u_exc_continicialgal']));
			$u_exc_contfinalgal = $this->converterUM(array('type' => 1, 'co' => $reg['u_exc_contfinalgal']));
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'u_exc_fecha' => $reg['u_exc_fecha'],
				'u_exc_turno' => '',// '$qty_sale: '.$qty_sale. '_grand_total: '.$reg['_grand_total'].' - '.$testDispatch.' $total_sale: '.$total_sale,
				'u_exc_articulo' => '11620308',
				'u_exc_continicial' => $price_initial,//soles
				'u_exc_contfinal' => $price_final,//soles
				'u_exc_manguera' => '',
				'ocrcode' => $reg['ocrcode'],
				'u_exc_caja' => '',
				'u_exc_lado' => '',
				'u_exc_cont' => $reg['u_exc_cont'],
				'u_exc_tipo' => '1',
				'u_exc_continicialgal' => $u_exc_continicialgal,//cantidad
				'u_exc_contfinalgal' => $u_exc_contfinalgal,//cantidad

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'contometer',
			'contometer' => $res,
			'count' => $c,
		);
	}

	/**
	 * Cambio de precio
	 */
	public function getChangePrice($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 es||TO_CHAR(dia::DATE,'YYMMDD')||caja||turno||codigo AS noperacion,
 dia AS u_exc_fecha,
 turno AS u_exc_turno,
 FIRST(precio) AS u_exc_precio,
 codigo AS u_exc_tipoprod,--tipo de producto, linea?
 SAPCC.sap_codigo AS ocrcode
FROM
".$param['pos_trans']." AS PT
JOIN inv_ta_almacenes AS ALMA ON (PT.es = ALMA.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
WHERE
 dia BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 AND tipo = 'C'
 AND grupo != 'D'
 --AND es = '".$param['warehouse']."'
GROUP BY
 es,
 dia,
 caja,
 turno,
 codigo,
 SAPCC.sap_codigo;";

		$this->_error_log($param['tableName'].' - getChangePrice: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'u_exc_fecha' => $reg['u_exc_fecha'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_precio' => (float)$reg['u_exc_precio'],
				'u_exc_tipoprod' => $reg['u_exc_tipoprod'],
				'ocrcode' => $reg['ocrcode'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'changePrice',
			'changePrice' => $res,
			'count' => $c,
		);
	}

	/**
	 * Bonus
	 */
	public function getBonus($param) {
		global $sqlca;

		$res = array();
// 		$sql = "SELECT
//  PT.es || PT.caja || PT.trans AS noperacion,
//  PT.dia AS u_exc_fecha,
//  PT.indexa AS u_exc_ntarjeta,
//  TO_CHAR(PT.fecha, 'HH12:MI:SS') AS u_exc_hora,
//  TRUNC(PT.importe / ".$param['factor_bonus'].") AS u_exc_cantpnts,
//  PT.trans AS u_exc_tickets,
//  (CASE WHEN PT.td = 'N' THEN PT.cuenta ELSE PT.ruc END) AS cardcode,
//  MTRABA.ch_codigo_trabajador AS extempno,
//  SAPCC.sap_codigo AS ocrcode
//  , PT.cajero AS _extempno
//  , PT.tipo AS _type
// FROM
//  ".$param['pos_trans']." AS PT
//  LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(PT.dia = MTRABA.dt_dia AND PT.turno::INTEGER = MTRABA.ch_posturno AND PT.pump = MTRABA.ch_lado AND PT.tipo = MTRABA.ch_tipo)
//  JOIN inv_ta_almacenes AS ALMA ON (PT.es = ALMA.ch_almacen)
//  JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
//  LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
// WHERE
//  PT.td IN('F','B','N')
//  AND TRIM(PT.indexa) != '' AND TRIM(PT.indexa) != '.' AND TRIM(PT.indexa) != '0'
//  AND PT.grupo != 'D'
//  AND PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
//  --AND es = '".$param['warehouse']."'
// ;";

		/*** Group by para arreglar ticket F1-0000005499 - 2020/02/03 ***/
		$sql = "SELECT
PT.es || PT.caja || PT.trans AS noperacion,
PT.dia AS u_exc_fecha,
PT.indexa AS u_exc_ntarjeta,
TO_CHAR(PT.fecha, 'HH12:MI:SS') AS u_exc_hora,
SUM(TRUNC(PT.importe / ".$param['factor_bonus'].")) AS u_exc_cantpnts,
PT.trans AS u_exc_tickets,
(CASE WHEN PT.td = 'N' THEN PT.cuenta ELSE PT.ruc END) AS cardcode,
MTRABA.ch_codigo_trabajador AS extempno,
SAPCC.sap_codigo AS ocrcode
, PT.cajero AS _extempno
, PT.tipo AS _type
FROM
".$param['pos_trans']." AS PT
LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(PT.dia = MTRABA.dt_dia AND PT.turno::INTEGER = MTRABA.ch_posturno AND PT.pump = MTRABA.ch_lado AND PT.tipo = MTRABA.ch_tipo)
JOIN inv_ta_almacenes AS ALMA ON (PT.es = ALMA.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
WHERE
PT.td IN('F','B','N')
AND TRIM(PT.indexa) != '' AND TRIM(PT.indexa) != '.' AND TRIM(PT.indexa) != '0'
AND PT.grupo != 'D'
AND PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
GROUP BY
pt.dia,pt.es,pt.caja,pt.trans,pt.indexa,pt.fecha,pt.td,pt.cuenta,pt.ruc,mtraba.ch_codigo_trabajador,sapcc.sap_codigo,pt.cajero,pt.tipo;";

		$this->_error_log($param['tableName'].' - getBonus: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$reg['extempno'] = $reg['_type'] != 'M' ? $reg['extempno'] : $reg['_extempno'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'u_exc_fecha' => $reg['u_exc_fecha'],
				'u_exc_ntarjeta' => $reg['u_exc_ntarjeta'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_cantpnts' => $reg['u_exc_cantpnts'],
				'u_exc_tickets' => $reg['u_exc_tickets'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'extempno' => $this->cleanStr($reg['extempno']),
				'ocrcode' => $reg['ocrcode'],
				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'bonus',
			'bonus' => $res,
			'count' => $c,
		);
	}

	/**
	 * Depositos
	 */
	public function getDeposit($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 DISTINCT ON (DPOS.ch_almacen || DPOS.dt_dia || DPOS.ch_posturno || DPOS.ch_codigo_trabajador || DPOS.ch_numero_documento)
 DPOS.ch_almacen || TO_CHAR(DPOS.dt_dia::DATE,'YYMMDD') || DPOS.ch_posturno || DPOS.ch_codigo_trabajador || DPOS.ch_numero_documento AS noperacion,
 DPOS.dt_fecha AS u_exc_fecha,
 DPOS.ch_posturno AS u_exc_turno,
 DPOS.nu_importe AS u_exc_monto,
 '2' AS u_exc_tipo,--GNV o Combustible
 COALESCE(TRAFB.importe,0) AS u_exc_falt,
 MTRABA.ch_codigo_trabajador AS extempno,
 LADOCAJA.s_pos_id AS u_exc_caja,
 DPOS.ch_moneda AS u_exc_moneda,
 DPOS.nu_tipo_cambio AS u_exc_tc,
 DPOS.ch_numero_documento AS u_exc_ncomprobante,
 (CASE
  WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) > 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) = 0 THEN 'Billetes'
  WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) = 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) > 0 THEN 'Monedas'
  WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) > 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) > 0 THEN 'Billetes y Monedas'
 ELSE
  'Ninguna'
 END) AS u_exc_denominacion,
 SAPCC.sap_codigo AS ocrcode
FROM
 pos_depositos_diarios AS DPOS
 LEFT JOIN comb_diferencia_trabajador AS TRAFB ON (DPOS.dt_dia = TRAFB.dia AND DPOS.ch_posturno = TRAFB.turno AND DPOS.ch_codigo_trabajador = TRAFB.ch_codigo_trabajador AND TRAFB.importe < 0)
 LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(DPOS.ch_codigo_trabajador = MTRABA.ch_codigo_trabajador AND DPOS.dt_dia = MTRABA.dt_dia AND DPOS.ch_posturno = MTRABA.ch_posturno)
 LEFT JOIN f_pump AS LADO ON (MTRABA.ch_lado = LADO.name)
 LEFT JOIN f_pump_pos AS LADOCAJA USING (f_pump_id)
 JOIN inv_ta_almacenes AS ALMA USING (ch_almacen)
 JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
 LEFt JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
WHERE
 DPOS.dt_dia BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 AND (DPOS.ch_valida = 'S' OR DPOS.ch_valida = 's')
 --AND DPOS.ch_almacen = '".$param['warehouse']."'
;";

		$this->_error_log($param['tableName'].' - getDeposit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'u_exc_fecha' => $reg['u_exc_fecha'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_monto' => (float)$reg['u_exc_monto'],
				'u_exc_tipo' => $reg['u_exc_tipo'],
				'u_exc_falt' => $reg['u_exc_falt'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'ocrcode' => $reg['ocrcode'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_moneda' => $reg['u_exc_moneda'],
				'u_exc_tc' => (float)$reg['u_exc_tc'],
				'u_exc_ncomprobante' => $this->cleanStr($reg['u_exc_ncomprobante']),
				'u_exc_denominacion' => $reg['u_exc_denominacion'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'deposit',
			'deposit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Depositos
	 */
	public function getDepositWithFechaSistema($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 DISTINCT ON (DPOS.ch_almacen || DPOS.dt_dia || DPOS.ch_posturno || DPOS.ch_codigo_trabajador || DPOS.ch_numero_documento)
 DPOS.ch_almacen || TO_CHAR(DPOS.dt_dia::DATE,'YYMMDD') || DPOS.ch_posturno || DPOS.ch_codigo_trabajador || DPOS.ch_numero_documento AS noperacion,
 DPOS.dt_fecha AS u_exc_fecha,
 DPOS.ch_posturno AS u_exc_turno,
 DPOS.nu_importe AS u_exc_monto,
 '2' AS u_exc_tipo,--GNV o Combustible
 COALESCE(TRAFB.importe,0) AS u_exc_falt,
 MTRABA.ch_codigo_trabajador AS extempno,
 LADOCAJA.s_pos_id AS u_exc_caja,
 DPOS.ch_moneda AS u_exc_moneda,
 DPOS.nu_tipo_cambio AS u_exc_tc,
 DPOS.ch_numero_documento AS u_exc_ncomprobante,
 (CASE
  WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) > 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) = 0 THEN 'Billetes'
  WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) = 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) > 0 THEN 'Monedas'
  WHEN (nu_mon200+nu_mon100+nu_mon50+nu_mon20+nu_mon10) > 0 AND (nu_mon5+nu_mon2+nu_mon1+nu_mon050+nu_mon020+nu_mon010) > 0 THEN 'Billetes y Monedas'
 ELSE
  'Ninguna'
 END) AS u_exc_denominacion,
 SAPCC.sap_codigo AS ocrcode,
 DPOS.dt_dia::TIMESTAMP AS u_exc_fechaturno
FROM
 pos_depositos_diarios AS DPOS
 LEFT JOIN comb_diferencia_trabajador AS TRAFB ON (DPOS.dt_dia = TRAFB.dia AND DPOS.ch_posturno = TRAFB.turno AND DPOS.ch_codigo_trabajador = TRAFB.ch_codigo_trabajador AND TRAFB.importe < 0)
 LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(DPOS.ch_codigo_trabajador = MTRABA.ch_codigo_trabajador AND DPOS.dt_dia = MTRABA.dt_dia AND DPOS.ch_posturno = MTRABA.ch_posturno)
 LEFT JOIN f_pump AS LADO ON (MTRABA.ch_lado = LADO.name)
 LEFT JOIN f_pump_pos AS LADOCAJA USING (f_pump_id)
 JOIN inv_ta_almacenes AS ALMA USING (ch_almacen)
 JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
 LEFt JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
WHERE
 DPOS.dt_dia BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 AND (DPOS.ch_valida = 'S' OR DPOS.ch_valida = 's')
 --AND DPOS.ch_almacen = '".$param['warehouse']."'
;";

		$this->_error_log($param['tableName'].' - getDeposit: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'u_exc_fecha' => $reg['u_exc_fecha'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_monto' => (float)$reg['u_exc_monto'],
				'u_exc_tipo' => $reg['u_exc_tipo'],
				'u_exc_falt' => $reg['u_exc_falt'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'ocrcode' => $reg['ocrcode'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_moneda' => $reg['u_exc_moneda'],
				'u_exc_tc' => (float)$reg['u_exc_tc'],
				'u_exc_ncomprobante' => $this->cleanStr($reg['u_exc_ncomprobante']),
				'u_exc_denominacion' => $reg['u_exc_denominacion'],
				'u_exc_fechaturno' => substr($reg['u_exc_fechaturno'],0,19),

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'deposit',
			'deposit' => $res,
			'count' => $c,
		);
	}

	/**
	 * Inventarios - Cabecera
	 */
	public function getHeadInventory($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 mov.mov_numero||mov.tran_codigo AS noperacion,
 to_char(mov.mov_fecha,'YYYY-MM-DD') AS docdate,
 FIRST(tipotransa.format_sunat) AS u_exx_tipooper
 , FIRST(tipotransa.tran_naturaleza) AS _type_transaction
FROM inv_movialma mov
JOIN int_articulos art ON (mov.art_codigo = art.art_codigo)
JOIN inv_tipotransa tipotransa ON (mov.tran_codigo = tipotransa.tran_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = mov.mov_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = mov.mov_almacen)
LEFt JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)--puede limitar
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
WHERE
mov.mov_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
--AND mov.mov_almacen = '001'
AND art.art_plutipo = '1'
GROUP BY
 tipotransa.format_sunat,
 mov.mov_numero,
 mov.tran_codigo,
 to_char(mov.mov_fecha, 'YYYY-MM-DD');";

		$this->_error_log($param['tableName'].' - getHeadInventory: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$u_exx_tipooper = explode('-', $reg['u_exx_tipooper']);
			$u_exx_tipooper = $u_exx_tipooper[0];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'docdate' => $reg['docdate'],
				'tipo' => $reg['_type_transaction'] == 1 || $reg['_type_transaction'] == 2 ? 'E' : 'S',
				'u_exx_tipooper' => $u_exx_tipooper,

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'headInventory',
			'headInventory' => $res,
			'count' => $c,
		);
	}

	/**
	 * Inventarios - Detalle
	 */
	public function getDetailInventory($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 mov.mov_numero||mov.tran_codigo AS noperacion,
 mov.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 mov.mov_cantidad AS quantity,
 mov.mov_costounitario AS price,
 SAPLINEA.sap_codigo AS ocrcode,
 SAPCC.sap_codigo AS ocrcode2--centro de costo
FROM inv_movialma mov
JOIN int_articulos art ON (mov.art_codigo = art.art_codigo)
JOIN inv_tipotransa tipotransa ON (mov.tran_codigo = tipotransa.tran_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = mov.mov_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = mov.mov_almacen)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)--puede limitar
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
WHERE
mov.mov_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
--AND mov.mov_almacen = '001'
AND art.art_plutipo = '1'
ORDER BY 1;";

		$this->_error_log($param['tableName'].' - getDetailInventory: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailInventory',
			'detailInventory' => $res,
			'count' => $c,
		);
	}

	/**
	 * Transferencias - Cabecera
	 */
	public function getHeadTransfers($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 mov.mov_numero AS noperacion,
 to_char(mov.mov_fecha,'YYYY-MM-DD') AS docdate,
 FIRST(tipotransa.format_sunat) AS u_exx_tipooper,
 _origen.sap_codigo AS filler,
 _destino.sap_codigo AS towhscode
FROM inv_movialma mov
JOIN int_articulos art ON (mov.art_codigo = art.art_codigo)
JOIN inv_tipotransa tipotransa ON (mov.tran_codigo = tipotransa.tran_codigo)
JOIN inv_ta_almacenes origen ON (mov.mov_almaorigen = origen.ch_almacen)
JOIN inv_ta_almacenes destino ON (mov.mov_almadestino = destino.ch_almacen)
JOIN sap_mapeo_tabla_detalle _origen ON (_origen.id_tipo_tabla = 2 AND origen.ch_almacen = _origen.opencomb_codigo)
JOIN sap_mapeo_tabla_detalle _destino ON (_destino.id_tipo_tabla = 2 AND destino.ch_almacen = _destino.opencomb_codigo)
WHERE
mov.tran_codigo IN ('07', '08', '27', '28')
AND mov.mov_fecha BETWEEN '".$param['initial_date']."  00:00:00' AND '".$param['initial_date']."  23:59:59'
AND art.art_plutipo = '1'
GROUP BY
 mov.mov_numero,
 mov.tran_codigo,
 to_char(mov.mov_fecha, 'YYYY-MM-DD'),
 filler,
 towhscode,
 mov.mov_tipdocuref,
 mov.mov_docurefe
ORDER BY
 mov.tran_codigo;";

		/*$sql = "SELECT
 mov.mov_numero AS noperacion,
 to_char(mov.mov_fecha,'YYYY-MM-DD') AS docdate,
 FIRST(tipotransa.format_sunat) AS u_exx_tipooper,
 mov.mov_almaorigen AS filler,
 mov.mov_almadestino AS towhscode
FROM inv_movialma mov
JOIN int_articulos art ON (mov.art_codigo = art.art_codigo)
JOIN inv_tipotransa tipotransa ON (mov.tran_codigo = tipotransa.tran_codigo)
WHERE
mov.tran_codigo IN ('07', '08', '27', '28')
AND mov.mov_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
AND art.art_plutipo = '1'
GROUP BY
 mov.mov_numero,
 mov.tran_codigo,
 to_char(mov.mov_fecha, 'YYYY-MM-DD'),
 mov.mov_almaorigen,
 mov.mov_almadestino,
 mov.mov_tipdocuref,
 mov.mov_docurefe
ORDER BY
 mov.tran_codigo;";*/

		$this->_error_log($param['tableName'].' - getHeadTransfers: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$u_exx_tipooper = explode('-', $reg['u_exx_tipooper']);
			$u_exx_tipooper = $u_exx_tipooper[0];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'docdate' => $reg['docdate'],
				'u_exx_tipooper' => $u_exx_tipooper,
				'filler' => $reg['filler'],
				'towhscode' => $reg['towhscode'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'headTransfers',
			'headTransfers' => $res,
			'count' => $c,
		);
	}

	/**
	 * Transferencias - Detella
	 */
	public function getDetailTransfers($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 mov.mov_numero AS noperacion,
 mov.art_codigo AS itemcode,
 mov.mov_cantidad AS quantity
FROM inv_movialma mov
JOIN int_articulos art ON (mov.art_codigo = art.art_codigo)
JOIN inv_tipotransa tipotransa ON (mov.tran_codigo = tipotransa.tran_codigo)
JOIN inv_ta_almacenes origen ON (mov.mov_almaorigen = origen.ch_almacen)
JOIN inv_ta_almacenes destino ON (mov.mov_almadestino = destino.ch_almacen)
JOIN sap_mapeo_tabla_detalle _origen ON (_origen.id_tipo_tabla = 2 AND origen.ch_almacen = _origen.opencomb_codigo)
JOIN sap_mapeo_tabla_detalle _destino ON (_destino.id_tipo_tabla = 2 AND destino.ch_almacen = _destino.opencomb_codigo)
WHERE
mov.tran_codigo IN ('07', '08', '27', '28')
AND mov.mov_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
AND art.art_plutipo = '1'
ORDER BY
mov.mov_numero;";

		$this->_error_log($param['tableName'].' - getDetailTransfers: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'quantity' => (float)$reg['quantity'],
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailTransfers',
			'detailTransfers' => $res,
			'count' => $c,
		);
	}

	/**
	 * Afericiones - Cabecera
	 */
	public function getHeadTestDispatch($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 pta.es || pta.caja || pta.trans AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pta.dia) AS docdate,
 LPAD(FIRST(pt.caja),3,'0'::TEXT) AS foliopref,
 TO_CHAR(FIRST(pt.trans),'FM9999999999') AS folionum,
 FIRST(MTRABA.ch_codigo_trabajador) AS extempno,
 pta.caja AS u_exc_maqreg
FROM
 pos_ta_afericiones AS pta
 JOIN ".$param['pos_trans']." pt ON (pta.trans = pt.trans AND pta.es = pt.es AND pta.caja = pt.caja AND pta.turno = pt.turno AND pta.pump = pt.pump)
 LEFT JOIN pos_historia_ladosxtrabajador AS MTRABA ON(pt.dia = MTRABA.dt_dia AND pt.turno::INTEGER = MTRABA.ch_posturno AND pt.pump = MTRABA.ch_lado AND pt.tipo = MTRABA.ch_tipo)
WHERE
 pta.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 --AND pta.es = '".$param['warehouse']."'
GROUP BY
 pta.es,
 pta.caja,
 pta.trans;";

		$this->_error_log($param['tableName'].' - getHeadTestDispatch: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode'] == NULL || $reg['cardcode'] == '' ? '' : $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => $reg['folionum'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'headTestDispatch',
			'headTestDispatch' => $res,
			'count' => $c,
		);
	}

	/**
	 * Afericiones - Detalle
	 */
	public function getDetailTestDispatch($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 PTA.es || PTA.caja || PTA.trans AS noperacion,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 PTA.codigo AS itemcode,
 FIRST(PTA.cantidad) AS quantity,
 ROUND((FIRST(PTA.precio) / ".$param['tax']."), 4) AS price,
 '".$param['sap_tax_code']."' AS taxcode,
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / 1) * 100) / (FIRST(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1))) / 1), 4) AS discprcnt,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,
 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 '' AS u_exc_dispensador,
 PTA.caja AS u_exc_caja,
 FIRST(SURTIDOR.nu_manguera) AS u_exc_manguera,
 FIRST(PTA.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PTA.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(pt.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(FIRST(PT.importe) / 1) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 FIRST(PTA.lineas) AS u_exc_nlineas
FROM
 pos_ta_afericiones AS PTA
 JOIN ".$param['pos_trans']." pt ON (pta.trans = pt.trans AND pta.es = pt.es AND pta.caja = pt.caja AND pta.turno = pt.turno AND pta.pump = pt.pump)
 JOIN comb_ta_surtidores AS SURTIDOR ON (PTA.pump::INTEGER = SURTIDOR.ch_numerolado::INTEGER AND SURTIDOR.ch_codigocombustible = PTA.codigo)
 JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PTA.es)
 JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PTA.es AND SAPALMA.id_tipo_tabla = 2)
 JOIN int_articulos AS PRO ON (PRO.art_codigo = PTA.codigo)
 LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = PRO.art_linea AND SAPLINEA.id_tipo_tabla = 3)
 LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND PT.td ='N'
 --AND PT.tipo = 'M'
 AND PT.grupo = 'D'
 --AND PT.es = '".$param['warehouse']."'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
WHERE
 PTA.dia BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
 --AND pta.es = '".$param['warehouse']."'
GROUP BY
 PTA.es,
 PTA.caja,
 PTA.trans,
 PTA.codigo;";

		$this->_error_log($param['tableName'].' - getDetailTestDispatch:'.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => 1,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$reg['discprcnt'],
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'u_exc_nrotarjbonus' => $reg['u_exc_nrotarjbonus'],
				'u_exc_nlineas' => $reg['u_exc_nlineas'],
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailTestDispatch',
			'detailTestDispatch' => $res,
			'count' => $c,
		);
	}


	/**
	 * Nota de credito - Cabecera
	 * El documento origen no puede existir en hana por dos motivos, 
	 1: El documento está fuera de rango en las exportaciones, 
	 2: El documento se encuentra detro del grupo a enviar el dìa de la exportaciones
	 */
	public function getHeadCreditNote($hanaInstance, $param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 Cab.ch_fac_seriedocumento || Cab.ch_fac_numerodocumento as noperacion,
 FIRST(Cab.cli_codigo) AS cardcode,
 FIRST(Cab.dt_fac_fecha) AS docdate,
 Cab.ch_fac_seriedocumento  AS foliopref,
 Cab.ch_fac_numerodocumento AS folionum,

 ROUND(FIRST(Cab.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(Cab.nu_fac_valortotal), 2) AS doctotal,

 CASE WHEN com.ch_fac_observacion2 != '' THEN
 CASE
  WHEN Cab.ch_fac_tipodocumento='20' OR Cab.ch_fac_tipodocumento='11' THEN
   CASE
    WHEN substring(com.ch_fac_observacion2, length(com.ch_fac_observacion2)-1, length(com.ch_fac_observacion2)) = '10' THEN
      substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'01'
   ELSE
    substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'03'
   END
  END
 ELSE
  ''
 END AS _refdata,
 com.ch_fac_observacion3  as u_exx_fecdocor,
 '' AS extempno,
 '' AS u_exc_maqreg

FROM
fac_ta_factura_cabecera AS Cab
JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
LEFT JOIN fac_ta_factura_complemento AS com ON (cab.cli_codigo = com.cli_codigo AND cab.ch_fac_seriedocumento=com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=com.ch_fac_numerodocumento AND cab.ch_fac_tipodocumento=com.ch_fac_tipodocumento)
LEFT JOIN int_clientes AS Cli ON (Cli.cli_codigo = Cab.cli_codigo)
LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = Cab.dt_fac_fecha)
WHERE
Cab.ch_fac_tipodocumento = '20'
AND Cab.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
GROUP BY
Cab.ch_fac_tipodocumento,
Cab.ch_fac_seriedocumento,
Cab.dt_fac_fecha,
Cab.ch_fac_numerodocumento,
Cab.ch_fac_moneda,
Cab.ch_fac_tiporecargo2,
com.ch_fac_observacion2,
com.ch_fac_observacion3
ORDER BY
Cab.ch_fac_seriedocumento,
Cab.dt_fac_fecha,
Cab.ch_fac_numerodocumento;
		";

		$this->_error_log($param['tableName'].' - getHeadCreditNote: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$table = '';
			$u_exx_serdocor = '';
			$u_exx_cordocor = '';
			$u_exx_fecdocor = '';
			$u_exx_tipdocor = '';

/*
			if (trim($reg['u_exx_fecdocor']) != '') {
				$u_exx_fecdocor = explode('/', $reg['u_exx_fecdocor']);
				$u_exx_fecdocor = $u_exx_fecdocor[2].'-'.$u_exx_fecdocor[1].'-'.$u_exx_fecdocor[0];
			}
*/
			if (trim($reg['u_exx_fecdocor']) != '') {
				$u_exx_fecdocor = $reg['u_exx_fecdocor'];
				//$u_exx_fecdocor = explode('/', $reg['u_exx_fecdocor']);
				//$u_exx_fecdocor = $u_exx_fecdocor[2].'-'.$u_exx_fecdocor[1].'-'.$u_exx_fecdocor[0];
			}

			if (trim($reg['_refdata'])) {
				$u_exx_docor = explode('*', $reg['_refdata']);
				$u_exx_serdocor = $u_exx_docor[1];
				$u_exx_cordocor = $u_exx_docor[0];
				$u_exx_tipdocor = $u_exx_docor[2];

				$let = trim($u_exx_serdocor);
				$let = substr($let, 0, 1);

				$isLoadRefNow = false;
				if (isset($this->invoiceSaleHead[$u_exx_serdocor][$u_exx_cordocor])) {
					$isLoadRefNow = true;
					$table = $this->invoiceSaleHead[$u_exx_serdocor][$u_exx_cordocor];
				}

				if (!$this->erroConnectionHANA && !$isLoadRefNow) {
					if ($let == 'F') {
						$table = 'INTOINVFC';
						$document = $this->findDocumentReference($hanaInstance, 0, array('head' => $table, 'detail' => 'INTINVFC1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
						if (count($document) > 0) {
							$this->creditNote[$reg['noperacion']] = $document;
						}
						if (count($document) <= 0) {
							$table = 'INTOINVPE';
							$document = $this->findDocumentReference($hanaInstance, 2, array('head' => $table, 'detail' => 'INTINVPE1', 'shipmentDetail' => 'INTDLNPE1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
							//$this->creditNote[$u_exx_serdocor][$u_exx_cordocor][$reg['noperacion']] = $document;
							$this->creditNote[$reg['noperacion']] = $document;
							if (count($document) <= 0) {
								$table = 'INTOINVPC';
								$document = $this->findDocumentReference($hanaInstance, 2, array('head' => $table, 'detail' => 'INTINVPC1', 'shipmentDetail' => 'INTDLNPC1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
								//$this->creditNote[$u_exx_serdocor][$u_exx_cordocor][$reg['noperacion']] = $document;
								$this->creditNote[$reg['noperacion']] = $document;
								if (count($document) <= 0) {
									$table = 'INTODPI';
									$document = $this->findDocumentReference($hanaInstance, 0, array('head' => $table, 'detail' => 'INTDPI1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
									//$this->creditNote[$u_exx_serdocor][$u_exx_cordocor][$reg['noperacion']] = $document;
									$this->creditNote[$reg['noperacion']] = $document;
									if (count($document) <= 0) {
										$table = '';
									}
								}
							}
						}
					} else if ($let == 'B') {
						$table = 'INTOBOL';
						$document = $this->findDocumentReference($hanaInstance, 1, array('head' => $table, 'detail' => 'INTBOL1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
						$this->creditNote[$reg['noperacion']] = $document;
						if (count($document) <= 0) {
							$table = '';
							error_log('Boleta no encontrada');
						}
					}
				}
			}

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'u_exx_serdocor' => $u_exx_serdocor,
				'u_exx_cordocor' => $u_exx_cordocor,
				'u_exx_fecdocor' => $u_exx_fecdocor,
				'u_exx_tipdocor' => $u_exx_tipdocor,
				'tabla' => $table,//***considerar
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		$sql = "
SELECT
 FIRST(PT.es) || FIRST(PT.caja) || FIRST(PT.trans) AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN LPAD(FIRST(pt.caja),3,'000'::text)
 ELSE FIRST(pt.usr) END AS foliopref,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN TO_CHAR(FIRST(pt.trans),'FM9999999999')
 ELSE '' END AS folionum,

 ROUND(SUM(PT.igv), 2) AS vatsum,
 ROUND(SUM(PT.importe), 2) AS doctotal,

 FIRST(PTORIGEN.no_serie_numero_doc_origen) AS _docref,
 FIRST(PTORIGEN.fe_origen) AS u_exx_fecdocor,
 FIRST(PTORIGEN.no_tipo_doc_origen) AS _documenttype,

 CASE WHEN FIRST(employe.ch_codigo_trabajador) IS NULL OR FIRST(employe.ch_codigo_trabajador) = '' THEN
  FIRST(pt.cajero)
 ELSE
  FIRST(employe.ch_codigo_trabajador)
 END AS extempno,--
 FIRST(pt.caja) AS u_exc_maqreg,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN '0'
 ELSE 1 END AS _isfe
 , FIRST(pt.trans) AS transaccion

FROM
 " . $param['pos_trans'] . " AS PT
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
 LEFT JOIN (
 SELECT
  trans AS id_trans,
  FIRST(td) AS no_tipo_doc_origen,
  FIRST(usr) AS no_serie_numero_doc_origen,
  FIRST(fecha)::DATE AS fe_origen
 FROM
  " . $param['pos_trans'] . "
 WHERE
  tm='V'
  AND td IN ('B', 'F')
  AND dia BETWEEN '" . $param['initial_date'] . " 00:00:00' AND '" . $param['initial_date'] . " 23:59:59'
 GROUP BY
  es,
  caja,
  trans
 ) AS PTORIGEN ON (PTORIGEN.id_trans = PT.rendi_gln)
WHERE
 PT.tm IN('D','A')
 AND PT.td IN ('B', 'F')
 AND PT.dia BETWEEN '" . $param['initial_date'] . " 00:00:00' AND '" . $param['initial_date'] . " 23:59:59'
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;
 		";

		$this->_error_log($param['tableName'].' - getHeadCreditNote: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$_foliopref = explode('-', $reg['foliopref']);
			$foliopref = $_foliopref[0];
			$folionum = $_foliopref[1];

			$_u_exx_serdocor = explode('-', $reg['_docref']);
			$u_exx_serdocor = $_u_exx_serdocor[0];
			$u_exx_cordocor = $_u_exx_serdocor[1];

			$u_exx_fecdocor = $reg['u_exx_fecdocor'];
            if ($reg['_documenttype'] == 'B') {
            $u_exx_tipdocor = '03';
            } else if ($reg['_documenttype'] == 'F') {
            $u_exx_tipdocor = '01';
            }//cai 20-01-2020
            
            if ($reg['_documenttype'] == 'B') {
            $table = 'INTOBOL';
            } else if ($reg['_documenttype'] == 'F') {
            $table = 'INTOINVFC';
            }

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $foliopref,
				'folionum' => (int)$folionum,
				'u_exx_serdocor' => $u_exx_serdocor,
				'u_exx_cordocor' => $u_exx_cordocor,
				'u_exx_fecdocor' => $u_exx_fecdocor,
				'u_exx_tipdocor' => $u_exx_tipdocor,//cai 
				'tabla' => $table,
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'headCreditNote',
			'headCreditNote' => $res,
			'count' => $c,
		);
	}

	/**
	 * Nota de credito - Cabecera
	 * El documento origen no puede existir en hana por dos motivos, 
	 1: El documento está fuera de rango en las exportaciones, 
	 2: El documento se encuentra detro del grupo a enviar el dìa de la exportaciones
	 */
	public function getHeadCreditNoteWithFechaEmision($hanaInstance, $param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 Cab.ch_fac_seriedocumento || Cab.ch_fac_numerodocumento as noperacion,
 FIRST(Cab.cli_codigo) AS cardcode,
 FIRST(Cab.dt_fac_fecha) AS docdate,
 Cab.ch_fac_seriedocumento  AS foliopref,
 Cab.ch_fac_numerodocumento AS folionum,

 ROUND(FIRST(Cab.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(Cab.nu_fac_valortotal), 2) AS doctotal,

 CASE WHEN com.ch_fac_observacion2 != '' THEN
 CASE
  WHEN Cab.ch_fac_tipodocumento='20' OR Cab.ch_fac_tipodocumento='11' THEN
   CASE
    WHEN substring(com.ch_fac_observacion2, length(com.ch_fac_observacion2)-1, length(com.ch_fac_observacion2)) = '10' THEN
      substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'01'
   ELSE
    substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'03'
   END
  END
 ELSE
  ''
 END AS _refdata,
 com.ch_fac_observacion3  as u_exx_fecdocor,
 '' AS extempno,
 '' AS u_exc_maqreg,
 FIRST(Cab.dt_fac_fecha) AS u_exc_fechaemi --Requerimiento fecha emision

FROM
fac_ta_factura_cabecera AS Cab
JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
LEFT JOIN fac_ta_factura_complemento AS com ON (cab.cli_codigo = com.cli_codigo AND cab.ch_fac_seriedocumento=com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=com.ch_fac_numerodocumento AND cab.ch_fac_tipodocumento=com.ch_fac_tipodocumento)
LEFT JOIN int_clientes AS Cli ON (Cli.cli_codigo = Cab.cli_codigo)
LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = Cab.dt_fac_fecha)
WHERE
Cab.ch_fac_tipodocumento = '20'
AND Cab.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
GROUP BY
Cab.ch_fac_tipodocumento,
Cab.ch_fac_seriedocumento,
Cab.dt_fac_fecha,
Cab.ch_fac_numerodocumento,
Cab.ch_fac_moneda,
Cab.ch_fac_tiporecargo2,
com.ch_fac_observacion2,
com.ch_fac_observacion3
ORDER BY
Cab.ch_fac_seriedocumento,
Cab.dt_fac_fecha,
Cab.ch_fac_numerodocumento;
		";

		$this->_error_log($param['tableName'].' - getHeadCreditNote: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$table = '';
			$u_exx_serdocor = '';
			$u_exx_cordocor = '';
			$u_exx_fecdocor = '';
			$u_exx_tipdocor = '';

/*
			if (trim($reg['u_exx_fecdocor']) != '') {
				$u_exx_fecdocor = explode('/', $reg['u_exx_fecdocor']);
				$u_exx_fecdocor = $u_exx_fecdocor[2].'-'.$u_exx_fecdocor[1].'-'.$u_exx_fecdocor[0];
			}
*/
			if (trim($reg['u_exx_fecdocor']) != '') {
				$u_exx_fecdocor = $reg['u_exx_fecdocor'];
				//$u_exx_fecdocor = explode('/', $reg['u_exx_fecdocor']);
				//$u_exx_fecdocor = $u_exx_fecdocor[2].'-'.$u_exx_fecdocor[1].'-'.$u_exx_fecdocor[0];
			}

			if (trim($reg['_refdata'])) {
				$u_exx_docor = explode('*', $reg['_refdata']);
				$u_exx_serdocor = $u_exx_docor[1];
				$u_exx_cordocor = $u_exx_docor[0];
				$u_exx_tipdocor = $u_exx_docor[2];

				$let = trim($u_exx_serdocor);
				$let = substr($let, 0, 1);

				$isLoadRefNow = false;
				if (isset($this->invoiceSaleHead[$u_exx_serdocor][$u_exx_cordocor])) {
					$isLoadRefNow = true;
					$table = $this->invoiceSaleHead[$u_exx_serdocor][$u_exx_cordocor];
				}

				if (!$this->erroConnectionHANA && !$isLoadRefNow) {
					if ($let == 'F') {
						$table = 'INTOINVFC';
						$document = $this->findDocumentReference($hanaInstance, 0, array('head' => $table, 'detail' => 'INTINVFC1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
						if (count($document) > 0) {
							$this->creditNote[$reg['noperacion']] = $document;
						}
						if (count($document) <= 0) {
							$table = 'INTOINVPE';
							$document = $this->findDocumentReference($hanaInstance, 2, array('head' => $table, 'detail' => 'INTINVPE1', 'shipmentDetail' => 'INTDLNPE1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
							//$this->creditNote[$u_exx_serdocor][$u_exx_cordocor][$reg['noperacion']] = $document;
							$this->creditNote[$reg['noperacion']] = $document;
							if (count($document) <= 0) {
								$table = 'INTOINVPC';
								$document = $this->findDocumentReference($hanaInstance, 2, array('head' => $table, 'detail' => 'INTINVPC1', 'shipmentDetail' => 'INTDLNPC1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
								//$this->creditNote[$u_exx_serdocor][$u_exx_cordocor][$reg['noperacion']] = $document;
								$this->creditNote[$reg['noperacion']] = $document;
								if (count($document) <= 0) {
									$table = 'INTODPI';
									$document = $this->findDocumentReference($hanaInstance, 0, array('head' => $table, 'detail' => 'INTDPI1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
									//$this->creditNote[$u_exx_serdocor][$u_exx_cordocor][$reg['noperacion']] = $document;
									$this->creditNote[$reg['noperacion']] = $document;
									if (count($document) <= 0) {
										$table = '';
									}
								}
							}
						}
					} else if ($let == 'B') {
						$table = 'INTOBOL';
						$document = $this->findDocumentReference($hanaInstance, 1, array('head' => $table, 'detail' => 'INTBOL1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
						$this->creditNote[$reg['noperacion']] = $document;
						if (count($document) <= 0) {
							$table = '';
							error_log('Boleta no encontrada');
						}
					}
				}
			}

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'u_exx_serdocor' => $u_exx_serdocor,
				'u_exx_cordocor' => $u_exx_cordocor,
				'u_exx_fecdocor' => $u_exx_fecdocor,
				'u_exx_tipdocor' => $u_exx_tipdocor,
				'tabla' => $table,//***considerar
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],
				'u_exc_fechaemi' => substr($reg['u_exc_fechaemi'],0,19),

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,				
			);
		}

		$sql = "
SELECT
 FIRST(PT.es) || FIRST(PT.caja) || FIRST(PT.trans) AS noperacion,
 FIRST(pt.ruc) AS cardcode,
 FIRST(pt.dia) AS docdate,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN LPAD(FIRST(pt.caja),3,'000'::text)
 ELSE FIRST(pt.usr) END AS foliopref,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN TO_CHAR(FIRST(pt.trans),'FM9999999999')
 ELSE '' END AS folionum,

 ROUND(SUM(PT.igv), 2) AS vatsum,
 ROUND(SUM(PT.importe), 2) AS doctotal,

 FIRST(PTORIGEN.no_serie_numero_doc_origen) AS _docref,
 FIRST(PTORIGEN.fe_origen) AS u_exx_fecdocor,
 FIRST(PTORIGEN.no_tipo_doc_origen) AS _documenttype,

 CASE WHEN FIRST(employe.ch_codigo_trabajador) IS NULL OR FIRST(employe.ch_codigo_trabajador) = '' THEN
  FIRST(pt.cajero)
 ELSE
  FIRST(employe.ch_codigo_trabajador)
 END AS extempno,--
 FIRST(pt.caja) AS u_exc_maqreg,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN '0'
 ELSE 1 END AS _isfe
 , FIRST(pt.trans) AS transaccion,
 FIRST(pt.fecha) AS u_exc_fechaemi --Requerimiento fecha emision

FROM
 " . $param['pos_trans'] . " AS PT
 LEFT JOIN int_clientes AS client ON(client.cli_codigo = pt.cuenta)
 LEFT JOIN pos_historia_ladosxtrabajador AS employe ON(employe.dt_dia = pt.dia AND employe.ch_posturno::CHAR = pt.turno AND employe.ch_lado = PT.pump)
 LEFT JOIN (
 SELECT
  trans AS id_trans,
  FIRST(td) AS no_tipo_doc_origen,
  FIRST(usr) AS no_serie_numero_doc_origen,
  FIRST(fecha)::DATE AS fe_origen
 FROM
  " . $param['pos_trans'] . "
 WHERE
  tm='V'
  AND td IN ('B', 'F')
  AND dia BETWEEN '" . $param['initial_date'] . " 00:00:00' AND '" . $param['initial_date'] . " 23:59:59'
 GROUP BY
  es,
  caja,
  trans
 ) AS PTORIGEN ON (PTORIGEN.id_trans = PT.rendi_gln)
WHERE
 PT.tm IN('D','A')
 AND PT.td IN ('B', 'F')
 AND PT.dia BETWEEN '" . $param['initial_date'] . " 00:00:00' AND '" . $param['initial_date'] . " 23:59:59'
GROUP BY
 PT.es,
 PT.caja,
 PT.trans;
 		";

		$this->_error_log($param['tableName'].' - getHeadCreditNote: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$_foliopref = explode('-', $reg['foliopref']);
			$foliopref = $_foliopref[0];
			$folionum = $_foliopref[1];

			$_u_exx_serdocor = explode('-', $reg['_docref']);
			$u_exx_serdocor = $_u_exx_serdocor[0];
			$u_exx_cordocor = $_u_exx_serdocor[1];

			$u_exx_fecdocor = $reg['u_exx_fecdocor'];
            if ($reg['_documenttype'] == 'B') {
            $u_exx_tipdocor = '03';
            } else if ($reg['_documenttype'] == 'F') {
            $u_exx_tipdocor = '01';
            }//cai 20-01-2020
            
            if ($reg['_documenttype'] == 'B') {
            $table = 'INTOBOL';
            } else if ($reg['_documenttype'] == 'F') {
            $table = 'INTOINVFC';
            }

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $foliopref,
				'folionum' => (int)$folionum,
				'u_exx_serdocor' => $u_exx_serdocor,
				'u_exx_cordocor' => $u_exx_cordocor,
				'u_exx_fecdocor' => $u_exx_fecdocor,
				'u_exx_tipdocor' => $u_exx_tipdocor,//cai 
				'tabla' => $table,
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],
				'u_exc_fechaemi' => substr($reg['u_exc_fechaemi'],0,19),

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,				
			);
		}

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'headCreditNote',
			'headCreditNote' => $res,
			'count' => $c,
		);
	}

	public function getItemCreditNote($noperacion, $itemcode, $arrOrigenDocumento) {
		error_log('BUSCANDO REFERENCIA');
		error_log('==================');
		error_log('$this->creditNote[' . $noperacion . ']');
		foreach ($this->creditNote[$noperacion] as $key => $value) {
			error_log('ND Precargada SQL Item -> ' . $value['itemcode'] );
			error_log('ND Precargada SQL Serie -> ' . $value['sSerieDocumentoOrigen'] );
			error_log('ND Precargada SQL Numero -> ' . $value['sNumeroDocumentoOrigen'] );
			error_log('ND DETALLE SQL Item -> ' . $itemcode );
			error_log('ND DETALLE SQL Serie -> ' . $arrOrigenDocumento['sSerieDocumentoOrigen'] );
			error_log('ND DETALLE SQL Numero -> ' . $arrOrigenDocumento['sNumeroDocumentoOrigen'] );
			if (
				trim($arrOrigenDocumento['sSerieDocumentoOrigen']) == trim($value['sSerieDocumentoOrigen'])
				&& trim($arrOrigenDocumento['sNumeroDocumentoOrigen']) == trim($value['sNumeroDocumentoOrigen'])
				&& ($value['itemcode'] == $itemcode || substr($value['itemcode'], -1) == $itemcode  || $value['itemcode'] == substr($itemcode, -1))
			) {
				return array('isFind' => true, 'item' => $value['item'], 'noperacion' => $value['noperacion']);
			}
		}
		error_log('==================');
		return array('isFind' => false);
	}

	/**
	 * Note ade credito - Detalle
	 */
	public function getDetailCreditNote($hanaInstance, $param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 Cab.ch_fac_seriedocumento || Cab.ch_fac_numerodocumento as noperacion,
 '' AS itemref,
 '' AS noperacionref,
 FD.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 FD.nu_fac_cantidad AS quantity,
 '".$param['sap_tax_code']."' AS taxcode,
 SAPLINEA.sap_codigo AS ocrcode,

 ROUND((FD.nu_fac_precio / ".$param['tax']."), 4) AS price,
 FD.nu_fac_precio / ".$param['tax']." AS _price,
 FD.nu_fac_descuento1 AS _discprcnt,

 SAPCC.sap_codigo AS ocrcode2,
 ROUND(FD.nu_fac_precio, 2) AS priceafvat,

 CASE WHEN com.ch_fac_observacion2 != '' THEN
 CASE
  WHEN Cab.ch_fac_tipodocumento='20' OR Cab.ch_fac_tipodocumento='11' THEN
   CASE
    WHEN substring(com.ch_fac_observacion2, length(com.ch_fac_observacion2)-1, length(com.ch_fac_observacion2)) = '10' THEN
      substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'01'
   ELSE
    substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'03'
   END
  END
 ELSE
  ''
 END AS _refdata,
 com.ch_fac_observacion3  as _u_exx_fecdocor,

 '' AS u_exc_dispensador,
 '' AS u_exc_caja,
 '' AS u_exc_manguera,
 '' AS u_exc_turno,
 '' AS u_exc_hora,
 '' AS u_exc_placa,
 '' AS u_exc_km,
 '' AS u_exc_bonus,
 ROUND(FD.nu_fac_descuento1, 4) AS desc_sinigv,
 ROUND((FD.nu_fac_descuento1 * ".$param['tax']."), 4) AS desc_igv
FROM
fac_ta_factura_detalle AS FD
JOIN fac_ta_factura_cabecera AS Cab USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
LEFT JOIN fac_ta_factura_complemento AS com ON (cab.cli_codigo = com.cli_codigo AND cab.ch_fac_seriedocumento=com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=com.ch_fac_numerodocumento AND cab.ch_fac_tipodocumento=com.ch_fac_tipodocumento)
LEFT JOIN int_clientes AS Cli ON (Cli.cli_codigo = Cab.cli_codigo)
LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = Cab.dt_fac_fecha)

LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = cab.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos art ON (FD.art_codigo = art.art_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar

JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = Cab.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)

WHERE
Cab.ch_fac_tipodocumento = '20'
AND Cab.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
AND (Cab.ch_fac_anulado IS NULL OR Cab.ch_fac_anulado = '' OR Cab.ch_fac_anulado != 'S')
ORDER BY 1;";

		$this->_error_log($param['tableName'].' - getDetailCreditNote: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}
			$data = array('item' => '', 'noperacion' => '');

			$item = $ci;

			if ($reg['_refdata'] != '') {
				$u_exx_docor = explode('*', $reg['_refdata']);
				$u_exx_serdocor = $u_exx_docor[1];
				$u_exx_cordocor = $u_exx_docor[0];
				$u_exx_tipdocor = $u_exx_docor[2];
				$u_exx_fecdocor = explode('/', $reg['_u_exx_fecdocor']);
				$u_exx_fecdocor = $u_exx_fecdocor[2].'-'.$u_exx_fecdocor[1].'-'.$u_exx_fecdocor[0];

				/* Debug */
				error_log('****** Debug ******');
				error_log( json_encode($this->invoiceSaleHead) );
				error_log( json_encode($this->ticketDetail) );
				error_log( json_encode($this->invoiceSaleDetail) );
				error_log( json_encode($this->invoiceSaleDetail[$u_exx_serdocor][$u_exx_cordocor]) );
				/* Fin Debug */

				$this->_error_log('POSIBLE('.$ci.'): $this->invoiceSaleDetail['.$u_exx_serdocor.']['.$u_exx_cordocor.']['.$this->cleanStr($reg['itemcode']).']'.' [LINE: '.__LINE__.']');
				if (isset($this->invoiceSaleDetail[$u_exx_serdocor][$u_exx_cordocor][$this->cleanStr($reg['itemcode'])])) {
					$data = $this->invoiceSaleDetail[$u_exx_serdocor][$u_exx_cordocor][$this->cleanStr($reg['itemcode'])];
				} else {
					if (isset($this->invoiceSaleDetail[$u_exx_serdocor][$u_exx_cordocor])) {
						$primer_elemento_array = reset($this->invoiceSaleDetail[$u_exx_serdocor][$u_exx_cordocor]);
						$data = $primer_elemento_array;
					} else {
						$this->_error_log('NO ENCONTRADO('.$ci.')'.' [LINE: '.__LINE__.']');
						$arrOrigenDocumento = array(
							'sSerieDocumentoOrigen' => $u_exx_serdocor,
							'sNumeroDocumentoOrigen' => $u_exx_cordocor,
						);
						$itemref = $this->getItemCreditNote($reg['noperacion'], $this->cleanStr($reg['itemcode']), $arrOrigenDocumento);
						if (!$itemref['isFind']) {
							$item = NULL;
							$data['item'] = NULL;
							$data['noperacion'] = NULL;
						} else {
							$data['item'] = $itemref['item'];
							$data['noperacion'] = $itemref['noperacion'];
						}
					}
				}
			}
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'itemref' => $data['item'],//no puede ser entero
				'noperacionref' => $data['noperacion'],
				'item' => $item,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$reg['_discprcnt'],
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $reg['noperacion'];
		}

		$sql = "
SELECT
 PT.es || PT.caja || PT.trans AS noperacion,
 PT.codigo AS itemcode,
 FIRST(SAPALMA.sap_codigo) AS whscode,
 FIRST(PT.cantidad) AS quantity,

 ROUND((FIRST(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / ".$param['tax'].", 4) AS price,
 ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / ".$param['tax'].") * 100) / ((FIRST(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / ".$param['tax']."), 4) AS discprcnt,

 FIRST(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _price,
 FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))) AS _discprcnt,

 '".$param['sap_tax_code']."' AS taxcode,
 FIRST(SAPLINEA.sap_codigo) AS ocrcode,

 FIRST(SAPCC.sap_codigo) AS ocrcode2,
 --ROUND(SUM(PT.precio), 2) AS priceafvat,09/07/2018
 ROUND(FIRST(PTPRECIO.precio_sin_descuento), 2) AS priceafvat,

 FIRST(PTORIGEN.td) AS _documenttype,
 (string_to_array(FIRST(PTORIGEN.usr), '-'))[1] AS _serie,
 ((string_to_array(FIRST(PTORIGEN.usr), '-'))[2]) AS _number,
 FIRST(PTORIGEN.turno) AS _turn,
 FIRST(PTORIGEN.es) || FIRST(PTORIGEN.caja) || FIRST(PTORIGEN.trans) AS _noperacionref,

 FIRST(PT.pump) AS u_exc_dispensador,
 PT.caja AS u_exc_caja,
 --FIRST(SURTIDOR.nu_manguera)::TEXT AS u_exc_manguera,
 CASE WHEN FIRST(PT.pump) != '' THEN
  (SELECT nu_manguera FROM comb_ta_surtidores SURTIDOR WHERE SURTIDOR.ch_numerolado::INTEGER = FIRST(PT.pump)::INTEGER AND SURTIDOR.ch_codigocombustible = FIRST(PT.codigo))::TEXT
 ELSE '' END AS u_exc_manguera,
 FIRST(PT.turno) AS u_exc_turno,
 TO_CHAR(FIRST(PT.fecha), 'HH12:MI:SS') AS u_exc_hora,
 FIRST(PT.placa) AS u_exc_placa,
 FIRST(PT.odometro) AS u_exc_km,
 TRUNC(FIRST(PT.importe / ".$param['factor_bonus'].")) AS u_exc_bonus,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_sigv, 0))) AS desc_sinigv,
 FIRST(ABS(COALESCE(PTDSCT.importe_descuento_igv, 0))) AS desc_igv
FROM
".$param['pos_trans']." AS PT
JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = PT.es)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = PT.es AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos AS art ON (art.art_codigo = PT.codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar
LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento,
  (PT.importe - PT.igv) AS importe_descuento_sigv,
  PT.importe AS importe_descuento_igv
 FROM
  ".$param['pos_trans']." AS PT
 WHERE
  PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
  AND PT.tm='A'
  AND PT.td IN('B', 'F')
  AND PT.grupo = 'D'
) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)

LEFT JOIN (
 SELECT
  es,
  caja,
  trans,
  FIRST(td) AS td,
  FIRST(usr) AS usr,
  FIRST(turno) AS turno
 FROM
  ".$param['pos_trans']."
 WHERE
  tm='V'
  AND td IN('B', 'F')
  AND dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 GROUP BY
  es,
  caja,
  trans
) AS PTORIGEN ON (PTORIGEN.trans = PT.rendi_gln)

LEFT JOIN (
SELECT
 PT.es,PT.caja,PT.trans,
 PT.precio AS precio_sin_descuento
FROM
".$param['pos_trans']." AS PT
WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND PT.tm='A'
 AND PT.td IN('B', 'F')
 AND PT.grupo != 'D'
) AS PTPRECIO ON (PT.es = PTPRECIO.es AND PT.caja = PTPRECIO.caja AND PT.trans = PTPRECIO.trans)

WHERE
 PT.dia BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
 AND PT.tm IN ('A','D')
 AND PT.td IN ('B', 'F')
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 PT.codigo
ORDER BY 1;";

		$this->_error_log($param['tableName'].' - getDetailCreditNote: '.$sql.' [LINE: '.__LINE__.']');
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			if ($reg['_documenttype'] == 'B') {
				$table = 'INTOBOL';
				$noperacionref = $this->getNOperacionTicket($reg);
				$noperacionref = $this->getNOperacionTicketDetail($reg, $noperacionref);
				$itemref = $this->ticketDetail[$reg['_serie']][$reg['_number']];
			} else if ($reg['_documenttype'] == 'F') {
				$table = 'INTOINVFC';
				$noperacionref = $reg['_noperacionref'];
				$itemref = 1;
			}

			$reg['quantity'] = abs((float)$reg['quantity']);

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'itemref' => $itemref,//no puede ser entero
				'noperacionref' => $noperacionref,///
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => $reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => $reg['_discprcnt'],
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $reg['noperacion'];
		}
		unset($this->ticketDetail, $this->ticketHead);

		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailCreditNote',
			'detailCreditNote' => $res,
			'count' => $c,
		);
	}


	/**
	 * Nota de debito - Cabecera
	 */
	public function getHeadDebitNote($hanaInstance, $param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 Cab.ch_fac_seriedocumento || Cab.ch_fac_numerodocumento as noperacion,
 FIRST(Cab.cli_codigo) AS cardcode,
 FIRST(Cab.dt_fac_fecha) AS docdate,
 Cab.ch_fac_seriedocumento  AS foliopref,
 Cab.ch_fac_numerodocumento AS folionum,

 ROUND(FIRST(Cab.nu_fac_impuesto1), 2) AS vatsum,
 ROUND(FIRST(Cab.nu_fac_valortotal), 2) AS doctotal,

 CASE WHEN com.ch_fac_observacion2 != '' THEN
 CASE
  WHEN Cab.ch_fac_tipodocumento='20' OR Cab.ch_fac_tipodocumento='11' THEN
   CASE
    WHEN substring(com.ch_fac_observacion2, length(com.ch_fac_observacion2)-1, length(com.ch_fac_observacion2)) = '10' THEN
      substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'01'
   ELSE
    substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'03'
   END
  END
 ELSE
  ''
 END AS _refdata,
 com.ch_fac_observacion3  as u_exx_fecdocor,
 '' AS extempno,
 '' AS u_exc_maqreg

FROM
fac_ta_factura_cabecera AS Cab
JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
LEFT JOIN fac_ta_factura_complemento AS com ON (cab.cli_codigo = com.cli_codigo AND cab.ch_fac_seriedocumento=com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=com.ch_fac_numerodocumento AND cab.ch_fac_tipodocumento=com.ch_fac_tipodocumento)
LEFT JOIN int_clientes AS Cli ON (Cli.cli_codigo = Cab.cli_codigo)
LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = Cab.dt_fac_fecha)
WHERE
Cab.ch_fac_tipodocumento = '11'
AND Cab.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
AND (Cab.ch_fac_anulado IS NULL OR Cab.ch_fac_anulado='N') --no anulados(consultar)
GROUP BY
Cab.ch_fac_tipodocumento,
Cab.ch_fac_seriedocumento,
Cab.dt_fac_fecha,
Cab.ch_fac_numerodocumento,
Cab.ch_fac_moneda,
Cab.ch_fac_tiporecargo2,
com.ch_fac_observacion2,
com.ch_fac_observacion3
ORDER BY
Cab.ch_fac_seriedocumento,
Cab.dt_fac_fecha,
Cab.ch_fac_numerodocumento;
		";

		$this->_error_log($param['tableName'].' - getHeadDebitNote: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$u_exx_docor = explode('*', $reg['_refdata']);
			$u_exx_serdocor = $u_exx_docor[1];
			$u_exx_cordocor = $u_exx_docor[0];
			$u_exx_tipdocor = $u_exx_docor[2];

			$u_exx_fecdocor = explode('/', $reg['u_exx_fecdocor']);
			$u_exx_fecdocor = $u_exx_fecdocor[2].'-'.$u_exx_fecdocor[1].'-'.$u_exx_fecdocor[0];

			$let = trim($u_exx_serdocor);
			$let = substr($let, 0, 1);
			$table = '';

			$isLoadRefNow = false;
			if (isset($this->invoiceSaleHead[$u_exx_serdocor][$u_exx_cordocor])) {
				$isLoadRefNow = true;
				$table = $this->invoiceSaleHead[$u_exx_serdocor][$u_exx_cordocor];
			}

			if (!$this->erroConnectionHANA && !$isLoadRefNow) {
				if ($let == 'F') {
					$table = 'INTOINVFC';
					$document = $this->findDocumentReference($hanaInstance, 0, array('head' => $table, 'detail' => 'INTINVFC1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
					if (count($document) > 0) {
						$this->creditNote[$reg['noperacion']] = $document;
					}
					if (count($document) <= 0) {
						$table = 'INTOINVPE';
						$document = $this->findDocumentReference($hanaInstance, 2, array('head' => $table, 'detail' => 'INTINVPE1', 'shipmentDetail' => 'INTDLNPE1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));

						$this->creditNote[$reg['noperacion']] = $document;
						if (count($document) <= 0) {
							$table = 'INTOINVPC';
							$document = $this->findDocumentReference($hanaInstance, 2, array('head' => $table, 'detail' => 'INTINVPC1', 'shipmentDetail' => 'INTDLNPC1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));

							$this->creditNote[$reg['noperacion']] = $document;
							if (count($document) <= 0) {
								$table = 'INTODPI';
								$document = $this->findDocumentReference($hanaInstance, 0, array('head' => $table, 'detail' => 'INTDPI1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));

								$this->creditNote[$reg['noperacion']] = $document;
								if (count($document) <= 0) {
									$table = '';
								}
							}
						}
					}
				} else if ($let == 'B') {
					$table = 'INTOBOL';
					$document = $this->findDocumentReference($hanaInstance, 1, array('head' => $table, 'detail' => 'INTBOL1'), array('foliopref' => $u_exx_serdocor, 'folionum' => (int)$u_exx_cordocor));
					$this->creditNote[$reg['noperacion']] = $document;
					if (count($document) <= 0) {
						$table = '';
						error_log('Boleta no encontrada');
					}
				}
			}

			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('C', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => (int)$reg['folionum'],
				'u_exx_serdocor' => $u_exx_serdocor,
				'u_exx_cordocor' => $u_exx_cordocor,
				'u_exx_fecdocor' => $u_exx_fecdocor,
				'u_exx_tipdocor' => $u_exx_tipdocor,
				'tabla' => $table,//***considerar
				'vatsum' => (float)$reg['vatsum'],
				'doctotal' => (float)$reg['doctotal'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'u_exc_maqreg' => $reg['u_exc_maqreg'],

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'headDebitNote',
			'headDebitNote' => $res,
			'count' => $c,
		);
	}

	/**
	 * Nota de debito - Detalle
	 */
	public function getDetailDebitNote($hanaInstance, $param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 Cab.ch_fac_seriedocumento || Cab.ch_fac_numerodocumento as noperacion,
 '' AS itemref,
 '' AS noperacionref,
 FD.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 FD.nu_fac_cantidad AS quantity,
 '".$param['sap_tax_code']."' AS taxcode,
 SAPLINEA.sap_codigo AS ocrcode,

 ROUND((FD.nu_fac_precio / ".$param['tax']."), 4) AS price,
 FD.nu_fac_precio / ".$param['tax']." AS _price,
 FD.nu_fac_descuento1 AS _discprcnt,

 SAPCC.sap_codigo AS ocrcode2,
 ROUND(FD.nu_fac_precio, 2) AS priceafvat,

 CASE WHEN com.ch_fac_observacion2 != '' THEN
 CASE
  WHEN Cab.ch_fac_tipodocumento='20' OR Cab.ch_fac_tipodocumento='11' THEN
   CASE
    WHEN substring(com.ch_fac_observacion2, length(com.ch_fac_observacion2)-1, length(com.ch_fac_observacion2)) = '10' THEN
      substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'01'
   ELSE
    substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'03'
   END
  END
 ELSE
  ''
 END AS _refdata,
 com.ch_fac_observacion3  as _u_exx_fecdocor,

 '' AS u_exc_dispensador,
 '' AS u_exc_caja,
 '' AS u_exc_manguera,
 '' AS u_exc_turno,
 '' AS u_exc_hora,
 '' AS u_exc_placa,
 '' AS u_exc_km,
 '' AS u_exc_bonus,
 ROUND(FD.nu_fac_descuento1, 4) AS desc_sinigv,
 ROUND((FD.nu_fac_descuento1 * ".$param['tax']."), 4) AS desc_igv

FROM
fac_ta_factura_detalle AS FD
JOIN fac_ta_factura_cabecera AS Cab USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
LEFT JOIN fac_ta_factura_complemento AS com ON (cab.cli_codigo = com.cli_codigo AND cab.ch_fac_seriedocumento=com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=com.ch_fac_numerodocumento AND cab.ch_fac_tipodocumento=com.ch_fac_tipodocumento)
LEFT JOIN int_clientes AS Cli ON (Cli.cli_codigo = Cab.cli_codigo)
LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = Cab.dt_fac_fecha)

JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = Cab.ch_almacen)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = ALMA.ch_sucursal)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)

LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = cab.ch_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_articulos art ON (FD.art_codigo = art.art_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPLINEA ON (SAPLINEA.opencomb_codigo = art.art_linea AND SAPLINEA.id_tipo_tabla = 3)--puede limitar

WHERE
Cab.ch_fac_tipodocumento = '11'
AND Cab.dt_fac_fecha BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
AND (Cab.ch_fac_anulado IS NULL OR Cab.ch_fac_anulado='N')--no anulados(consultar)
ORDER BY 1;";

		$this->_error_log($param['tableName'].' - getDetailDebitNote: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}
			$data = array('item' => '', 'noperacion' => '');

			$item = $ci;

			$u_exx_docor = explode('*', $reg['_refdata']);
			$u_exx_serdocor = $u_exx_docor[1];
			$u_exx_cordocor = $u_exx_docor[0];
			$u_exx_tipdocor = $u_exx_docor[2];
			$u_exx_fecdocor = explode('/', $reg['_u_exx_fecdocor']);
			$u_exx_fecdocor = $u_exx_fecdocor[2].'-'.$u_exx_fecdocor[1].'-'.$u_exx_fecdocor[0];

			$this->_error_log('ND DETAIL -> POSIBLE ('.$ci.'): $this->invoiceSaleDetail['.$u_exx_serdocor.']['.$u_exx_cordocor.']['.$this->cleanStr($reg['itemcode']).']'.' [LINE: '.__LINE__.']');
			if (isset($this->invoiceSaleDetail[$u_exx_serdocor][$u_exx_cordocor][$this->cleanStr($reg['itemcode'])])) {
				$data = $this->invoiceSaleDetail[$u_exx_serdocor][$u_exx_cordocor][$this->cleanStr($reg['itemcode'])];
			} else {
				$this->_error_log('NO ENCONTRADO('.$ci.')'.' [LINE: '.__LINE__.']');
				$arrOrigenDocumento = array(
					'sSerieDocumentoOrigen' => $u_exx_serdocor,
					'sNumeroDocumentoOrigen' => $u_exx_cordocor,
				);
				$itemref = $this->getItemCreditNote($reg['noperacion'], $this->cleanStr($reg['itemcode']), $arrOrigenDocumento);
				if (!$itemref['isFind']) {
					$item = NULL;
					$data['item'] = NULL;
					$data['noperacion'] = NULL;
				} else {
					$data['item'] = $itemref['item'];
					$data['noperacion'] = $itemref['noperacion'];
				}
			}
			$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'itemref' => $data['item'],//no puede ser entero
				'noperacionref' => $data['noperacion'],
				'item' => $item,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$discprcnt,
				'ocrcode' => $reg['ocrcode'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'u_exc_dispensador' => $reg['u_exc_dispensador'],
				'u_exc_caja' => $reg['u_exc_caja'],
				'u_exc_manguera' => $reg['u_exc_manguera'],
				'u_exc_turno' => $reg['u_exc_turno'],
				'u_exc_hora' => $reg['u_exc_hora'],
				'u_exc_placa' => $reg['u_exc_placa'],
				'u_exc_km' => $reg['u_exc_km'],
				'u_exc_bonus' => $reg['u_exc_bonus'],
				'desc_sinigv' => (float)$reg['desc_sinigv'],
				'desc_igv' => (float)$reg['desc_igv'],
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailDebitNote',
			'detailDebitNote' => $res,
			'count' => $c,
		);
	}


	/**
	 * Factura de proveedores - Cabecera
	 */
	public function getHeadInvoicePurchase($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 movialma.mov_numero || movialma.tran_codigo AS noperacion,
 (CASE WHEN FIRST(proveedor.pro_ruc) IS NULL OR FIRST(proveedor.pro_ruc) = '' THEN 'C00099999999' ELSE FIRST(proveedor.pro_ruc) END)  AS cardcode,
 FIRST(movialma.mov_fecha) AS docdate,
 substring(FIRST(mov_docurefe) FROM 1 for 4) AS foliopref,
 substring(FIRST(mov_docurefe) FROM 5 for 12) AS folionum,
 '' AS extempno,
 FIRST(tipodocumento.tab_car_03) AS indicator,
 ROUND(((SUM(movialma.mov_costototal) * " . $param['tax'] . ") - SUM(movialma.mov_costototal)), 2) AS vatsum,
 ROUND((SUM(movialma.mov_costototal) * " . $param['tax'] . "), 2) AS doctotal
FROM inv_movialma movialma
JOIN inv_tipotransa tipotransa ON (movialma.tran_codigo = tipotransa.tran_codigo)
LEFT JOIN int_proveedores proveedor ON (movialma.mov_entidad = proveedor.pro_codigo)
LEFT JOIN int_tabla_general AS tipodocumento ON (
 movialma.mov_tipdocuref = substring(TRIM(tipodocumento.tab_elemento) for 2 FROM length(TRIM(tipodocumento.tab_elemento))-1)
 AND tipodocumento.tab_tabla = '08'
 AND tipodocumento.tab_elemento <> '000000'
)
JOIN inv_ta_almacenes al_origen ON (movialma.mov_almaorigen = al_origen.ch_almacen)
JOIN inv_ta_almacenes al_destino ON (movialma.mov_almadestino = al_destino.ch_almacen)
WHERE
tipotransa.tran_naturaleza = '2'
AND tipodocumento.tab_car_03 = '01'
AND movialma.tran_codigo IN ('21', '01')
--AND movialma.art_codigo NOT IN('11620301','11620303','11620304','11620305','11620307')
AND movialma.mov_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
GROUP BY
movialma.mov_numero, movialma.tran_codigo;";

		$this->_error_log($param['tableName'].' - getHeadInvoicePurchase: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('P', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => $reg['folionum'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'indicator' => $reg['indicator'],

				'vatsum' => (float)$reg['vatsum'],//(Total Impuesto) actualmente vacíos
				'doctotal' => (float)$reg['doctotal'],//(Total de Documento) actualmente vacíos

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'headInvoicePurchase',
			'headInvoicePurchase' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura de proveedores - Cabecera
	 */
	public function getHeadInvoicePurchaseWithGuiasRemision($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT
 movialma.mov_numero || movialma.tran_codigo AS noperacion,
 (CASE WHEN FIRST(proveedor.pro_ruc) IS NULL OR FIRST(proveedor.pro_ruc) = '' THEN 'C00099999999' ELSE FIRST(proveedor.pro_ruc) END)  AS cardcode,
 FIRST(movialma.mov_fecha) AS docdate,
 substring(FIRST(mov_docurefe) FROM 1 for 4) AS foliopref,
 substring(FIRST(mov_docurefe) FROM 5 for 12) AS folionum,
 '' AS extempno,
 FIRST(tipodocumento.tab_car_03) AS indicator,
 ROUND(((SUM(movialma.mov_costototal) * " . $param['tax'] . ") - SUM(movialma.mov_costototal)), 2) AS vatsum,
 ROUND((SUM(movialma.mov_costototal) * " . $param['tax'] . "), 2) AS doctotal
FROM inv_movialma movialma
JOIN inv_tipotransa tipotransa ON (movialma.tran_codigo = tipotransa.tran_codigo)
LEFT JOIN int_proveedores proveedor ON (movialma.mov_entidad = proveedor.pro_codigo)
LEFT JOIN int_tabla_general AS tipodocumento ON (
 movialma.mov_tipdocuref = substring(TRIM(tipodocumento.tab_elemento) for 2 FROM length(TRIM(tipodocumento.tab_elemento))-1)
 AND tipodocumento.tab_tabla = '08'
 AND tipodocumento.tab_elemento <> '000000'
)
JOIN inv_ta_almacenes al_origen ON (movialma.mov_almaorigen = al_origen.ch_almacen)
JOIN inv_ta_almacenes al_destino ON (movialma.mov_almadestino = al_destino.ch_almacen)
WHERE
tipotransa.tran_naturaleza = '2'
AND tipodocumento.tab_car_03 IN ('01','09') --MOSTRAMOS FACTURAS Y GUIAS DE REMISION
AND movialma.tran_codigo IN ('21', '01')
--AND movialma.art_codigo NOT IN('11620301','11620303','11620304','11620305','11620307')
AND movialma.mov_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
GROUP BY
movialma.mov_numero, movialma.tran_codigo;";

		$this->_error_log($param['tableName'].' - getHeadInvoicePurchase: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'cardcode' => $this->preLetterBPartner('P', $reg['cardcode']),
				'docdate' => $reg['docdate'],
				'foliopref' => $reg['foliopref'],
				'folionum' => $reg['folionum'],
				'extempno' => $this->cleanStr($reg['extempno']),
				'indicator' => $reg['indicator'],

				'vatsum' => (float)$reg['vatsum'],//(Total Impuesto) actualmente vacíos
				'doctotal' => (float)$reg['doctotal'],//(Total de Documento) actualmente vacíos

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',
				'docentry' => NULL,
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'headInvoicePurchase',
			'headInvoicePurchase' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura de proveedores - Detalle
	 */
	public function getDetailInvoicePurchase($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 movialma.mov_numero || movialma.tran_codigo AS noperacion,
 movialma.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 movialma.mov_cantidad AS quantity,
 movialma.mov_costounitario AS price,
 '".$param['sap_tax_code']."' AS taxcode,
 0 AS discprcnt,
 SAPCC.sap_codigo AS ocrcode2--centro de costo
 , ROUND(movialma.mov_costounitario * ".$param['tax'].", 2) AS priceafvat
 
FROM inv_movialma movialma
JOIN inv_tipotransa tipotransa ON (movialma.tran_codigo = tipotransa.tran_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = movialma.mov_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = movialma.mov_almacen)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)--puede limitar
LEFT JOIN int_tabla_general AS tipodocumento ON (
 movialma.mov_tipdocuref = substring(TRIM(tipodocumento.tab_elemento) for 2 FROM length(TRIM(tipodocumento.tab_elemento))-1)
 AND tipodocumento.tab_tabla = '08'
 AND tipodocumento.tab_elemento <> '000000'
)
WHERE
tipotransa.tran_naturaleza = '2'
AND tipodocumento.tab_car_03 = '01'
AND movialma.tran_codigo IN ('21', '01') 
--AND movialma.art_codigo NOT IN('11620301','11620303','11620304','11620305','11620307')
AND movialma.mov_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
ORDER BY 1;";

		$this->_error_log($param['tableName'].' - getDetailInvoicePurchase: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$reg['discprcnt'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'desc_sinigv' => 0.00,
				'desc_igv' => 0.00,
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailInvoicePurchase',
			'detailInvoicePurchase' => $res,
			'count' => $c,
		);
	}

	/**
	 * Factura de proveedores - Detalle
	 */
	public function getDetailInvoicePurchaseWithGuiasRemision($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 movialma.mov_numero || movialma.tran_codigo AS noperacion,
 movialma.art_codigo AS itemcode,
 SAPALMA.sap_codigo AS whscode,
 movialma.mov_cantidad AS quantity,
 movialma.mov_costounitario AS price,
 '".$param['sap_tax_code']."' AS taxcode,
 0 AS discprcnt,
 SAPCC.sap_codigo AS ocrcode2--centro de costo
 , ROUND(movialma.mov_costounitario * ".$param['tax'].", 2) AS priceafvat
 
FROM inv_movialma movialma
JOIN inv_tipotransa tipotransa ON (movialma.tran_codigo = tipotransa.tran_codigo)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPALMA ON (SAPALMA.opencomb_codigo = movialma.mov_almacen AND SAPALMA.id_tipo_tabla = 2)
JOIN int_ta_sucursales AS ORG ON (ORG.ch_sucursal = movialma.mov_almacen)
LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = ORG.ch_sucursal AND SAPCC.id_tipo_tabla = 1)--puede limitar
LEFT JOIN int_tabla_general AS tipodocumento ON (
 movialma.mov_tipdocuref = substring(TRIM(tipodocumento.tab_elemento) for 2 FROM length(TRIM(tipodocumento.tab_elemento))-1)
 AND tipodocumento.tab_tabla = '08'
 AND tipodocumento.tab_elemento <> '000000'
)
WHERE
tipotransa.tran_naturaleza = '2'
AND tipodocumento.tab_car_03 IN ('01','09') --MOSTRAMOS FACTURAS Y GUIAS DE REMISION
AND movialma.tran_codigo IN ('21', '01') 
--AND movialma.art_codigo NOT IN('11620301','11620303','11620304','11620305','11620307')
AND movialma.mov_fecha BETWEEN '".$param['initial_date']." 00:00:00' AND '".$param['initial_date']." 23:59:59'
ORDER BY 1;";

		$this->_error_log($param['tableName'].' - getDetailInvoicePurchase: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		$ci = 1;
		$tmpDoc = '';
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}
			$res[] = array(
				'noperacion' => $reg['noperacion'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'whscode' => $reg['whscode'],
				'quantity' => (float)$reg['quantity'],
				'price' => (float)$reg['price'],
				'taxcode' => $reg['taxcode'],
				'discprcnt' => (float)$reg['discprcnt'],
				'ocrcode2' => $reg['ocrcode2'],
				'priceafvat' => (float)$reg['priceafvat'],
				'desc_sinigv' => 0.00,
				'desc_igv' => 0.00,
			);
			$tmpDoc = $reg['noperacion'];
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailInvoicePurchase',
			'detailInvoicePurchase' => $res,
			'count' => $c,
		);
	}

	/**
	 * Varillaje
	 */
	public function getDetailVarillas($param) {
		global $sqlca;

		$res = array();
		$sql = "
				SELECT	
					VARILLA.ch_sucursal || TO_CHAR(VARILLA.dt_fechamedicion, 'YYYYMMDD') || COMBU.ch_codigocombustible as noperacion,				
					VARILLA.dt_fechamedicion as docdate,
					--VARILLA.ch_sucursal as whscode,
					SAPCC.sap_codigo as whscode,
					COMBU.ch_codigocombustible as itemcode,
					ROUND(VARILLA.nu_medicion,2) as quantity,
				
					VARILLA.dt_fechamedicion,
					VARILLA.ch_tanque,
					COMBU.ch_codigocombustible,
					COMBU.ch_nombrecombustible,
					VARILLA.nu_medicion,
					VARILLA.ch_responsable,
					VARILLA.dt_fechactualizacion,
					VARILLA.ch_usuario,
					VARILLA.ch_auditorpc,
					VARILLA.ch_sucursal
				FROM
					comb_ta_mediciondiaria AS VARILLA
					JOIN comb_ta_tanques AS TANK
					USING (ch_sucursal,ch_tanque)
					JOIN comb_ta_combustibles AS COMBU
					USING(ch_codigocombustible)
					LEFT JOIN sap_mapeo_tabla_detalle AS SAPCC ON (SAPCC.opencomb_codigo = VARILLA.ch_sucursal AND SAPCC.id_tipo_tabla = 2)
				WHERE
					--VARILLA.ch_sucursal = '009' AND
					DATE(VARILLA.dt_fechamedicion) BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
				ORDER BY
					VARILLA.dt_fechamedicion DESC,
					--VARILLA.ch_tanque ASC
					COMBU.ch_codigocombustible ASC;";

		$this->_error_log($param['tableName'].' - getDetailVarillas: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],				
				'docdate' => $reg['docdate'],
				'whscode' => $reg['whscode'],
				'itemcode' => $reg['itemcode'],				
				'quantity' => $reg['quantity'],				

				'estado' => 'P',
				'errormsg' => '',
				'transaccion' => '',				
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailVarillas',
			'detailVarillas' => $res,
			'count' => $c,
		);
	}

	/**
	 * Varillaje
	 */
	public function getDetailCombustiblePorManguera($param) {
		global $sqlca;

		$res = array();
		$sql = "
				SELECT 
					cont.ch_sucursal || TO_CHAR(cont.dt_fechaparte, 'YYYYMMDD') || cont.ch_surtidor as noperacion,					
					cont.dt_fechaparte, 
					cont.ch_codigocombustible,
					surt.ch_numerolado as lado,
					cont.ch_surtidor as manguera, 
					(
					SELECT
						ROUND((SUM(precio) / COUNT(*)),2)
					FROM
						pos_contometros
					WHERE
						dia = cont.dt_fechaparte
						AND num_lado::text = surt.ch_numerolado
						AND manguera = nu_manguera
					) as precio,
					cont.nu_contometroinicialgalon, 
					cont.nu_contometrofinalgalon, 
					cont.nu_contometroinicialvalor, 
					cont.nu_contometrofinalvalor, 
					cont.nu_afericionveces_x_5 as afericiones, 
					-cont.nu_descuentos as descuentos	
				FROM 
					comb_ta_contometros cont
					LEFT JOIN comb_ta_surtidores surt ON (cont.ch_sucursal= surt.ch_sucursal and cont.ch_surtidor=surt.ch_surtidor)
					LEFT JOIN comb_ta_combustibles comb ON (cont.ch_codigocombustible=comb.ch_codigocombustible)
				WHERE 				
					DATE(cont.dt_fechaparte) BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
				ORDER BY 	
					dt_fechaparte, manguera;";
		
		$this->_error_log($param['tableName'].' - getDetailCombustiblePorManguera: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],				
				'docdate' => $reg['dt_fechaparte'],
				'itemcode' => $reg['ch_codigocombustible'],				
				'u_exc_lado' => $reg['lado'],				
				'u_exc_manguera' => $reg['manguera'],				
				'price' => $reg['precio'],				
				'u_exc_continigal' => $reg['nu_contometroinicialgalon'],				
				'u_exc_contfingal' => $reg['nu_contometrofinalgalon'],				
				'u_exc_continival' => $reg['nu_contometroinicialvalor'],				
				'u_exc_contfinval' => $reg['nu_contometrofinalvalor'],				
				'u_exc_afericiones' => $reg['afericiones'],				
				'u_exc_descuentos' => $reg['descuentos'],				
				'estado' => 'P',
				'errormsg' => '',							
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailCombustiblePorManguera',
			'detailCombustiblePorManguera' => $res,
			'count' => $c,
		);
	}

	/**
	 * Tabla de stocks (sólo combustibles)
	 */
	public function getDetailStock($param) {
		global $sqlca;

		$res = array();
		$sql = "
				SELECT
					VARILLA.ch_sucursal || TO_CHAR(VARILLA.dt_fechamedicion, 'YYYYMMDD') || COMBU.ch_codigocombustible as noperacion,
					VARILLA.dt_fechamedicion as fechamedicion,
					COMBU.ch_codigocombustible as codigocombustible,
					COMBU.ch_nombrecombustible as nombrecombustible,
					SUM(VARILLA.nu_medicion) as medicion
				FROM
					comb_ta_mediciondiaria    AS VARILLA
					JOIN comb_ta_tanques      AS TANK    USING (ch_sucursal,ch_tanque)
					JOIN comb_ta_combustibles AS COMBU   USING(ch_codigocombustible)
				WHERE
					--VARILLA.ch_sucursal = '003' AND
					DATE(VARILLA.dt_fechamedicion) BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
				GROUP BY
					VARILLA.dt_fechamedicion,
					COMBU.ch_codigocombustible,
					VARILLA.ch_sucursal
				ORDER BY
					VARILLA.dt_fechamedicion,
					COMBU.ch_codigocombustible;";
		
		$this->_error_log($param['tableName'].' - getDetailStock: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],				
				'docdate' => $reg['fechamedicion'],
				'itemcode' => $reg['codigocombustible'],				
				'u_exc_medicion' => $reg['medicion'],				
				'estado' => 'P',
				'errormsg' => '',							
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailStock',
			'detailStock' => $res,
			'count' => $c,
		);
	}

	/**
	 * Tabla de totales por forma de pago con notas de despacho
	 */
	public function getDetailTotales($param) {
		global $sqlca;

		$res = array();
		$sql = "
				SELECT
					t.es || TO_CHAR(DATE(t.dia), 'YYYYMMDD') || t.turno || TRIM(tl.tab_elemento) as noperacion,
					DATE(t.dia) as docdate,
					t.turno as turno,
					'TARJETA' as fpago, 
					TRIM(tl.tab_elemento)|| ' - ' ||TRIM(tl.tab_descripcion) nolinea,
					SUM(t.importe) as importetarjeta
				FROM
					".$param['pos_trans']." t
					JOIN int_articulos art ON(art.art_codigo = t.codigo)
					LEFT JOIN int_tabla_general tl ON (tl.tab_elemento = art.art_linea AND tl.tab_tabla = '20' AND (tl.tab_elemento != '000000' AND tl.tab_elemento != ''))
				WHERE
					--t.es = '" . pg_escape_string($estaciones) . "' AND
					t.fpago = '2' AND
					t.td != 'N' AND
					DATE(t.dia) BETWEEN '".$param['initial_date']."' AND '".$param['initial_date']."'
				GROUP BY 
					noperacion,docdate,turno,fpago,nolinea
				ORDER BY 
					docdate,turno,fpago,nolinea;";
		
		$this->_error_log($param['tableName'].' - getDetailTotales: '.$sql.' [LINE: '.__LINE__.']');
		$c = 0;
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			$res[] = array(
				'noperacion' => $reg['noperacion'],				
				'docdate' => $reg['docdate'],
				'u_exc_turno' => $reg['turno'],				
				'u_exc_fpago' => $reg['fpago'],	
				'u_exc_nolinea' => $reg['nolinea'],
				'u_exc_importe' => $reg['importetarjeta'],
				'estado' => 'P',
				'errormsg' => '',							
			);
		}
		return array(
			'error' => false,
			'tableName' => $param['tableName'],
			'nodeData' => 'detailTotales',
			'detailTotales' => $res,
			'count' => $c,
		);
	}




	/**
	 ****************************
	 * Export information Exxis *
	 ****************************
	 */
	public function setTableHana($hanaInstance, $data, $config) {

		if ($data['error']) {
			$this->_error_log('ERROR Y ROLLBACK! (Consulta) '.' [LINE: '.__LINE__.']');
			odbc_rollback($hanaInstance['instance']);
			$res = array(
				'errorCode' => 'QY',
				'error' => true,
				'message' => 'Error al acceder a la infomación',
				'table' => $data['tableName'],
			);
			return $res;
		}

		$res = array(
			'error' => false,
			'table' => $data['tableName'],
		);
		if (count($data[$data['nodeData']]) < 1) {
			return $res;
		}
		$static = '';
		$c = 0;
		$countNames = count($data[$data['nodeData']][0]);
		$static = "INSERT INTO ".$hanaInstance['db'].".".$data['tableName']." (";
		foreach (array_keys($data[$data['nodeData']][0]) as $key => $name) {
			$c++;
			$static .= "\n ".$name;
			$static .= ($c != $countNames ? ',' : '');
		}
		$static .= ") VALUES (";

		for ($i = 0; $i < count($data[$data['nodeData']]); $i++) {
			$c = 0;
			$countValues = count($data[$data['nodeData']][$i]);
			$insert = '';
			$insert .= $static;
			foreach (array_keys($data[$data['nodeData']][$i]) as $key => $value) {
				$c++;
				$insert .= "\n ";
				$text = $this->typeValue($data[$data['nodeData']][$i][$value]);
				//$insert .= $text == '[NULL]' ? "NULL" : $text;
				$insert .= $text;
				$insert .= ($c != $countValues ? ',' : '');
			}
			$insert .= ");\n";
			//echo '<br>'.$insert;
			$stmt = odbc_exec($hanaInstance['instance'], $insert);
			if (!$stmt) {
				$this->_error_log('$insert: '.$insert.' [LINE: '.__LINE__.']');
				//no quiero que sea unico: false; si marca el errorCode = 23000, debe saltar y no mostrar
				if (odbc_error() == '23000') {
					if ($config['isUnique']) {
						$this->_error_log('ERROR Y ROLLBACK!'.' [LINE: '.__LINE__.']');
						odbc_rollback($hanaInstance['instance']);
						$res = array(
							'errorCode' => odbc_error(),
							'error' => true,
							'message' => odbc_errormsg(),
							'line' => $insert,
							'table' => $data['tableName'],
						);
						return $res;
						break;
					}
				} else {
					$this->_error_log('ERROR Y ROLLBACK!'.' [LINE: '.__LINE__.']');
					odbc_rollback($hanaInstance['instance']);
					$res = array(
						'errorCode' => odbc_error(),
						'error' => true,
						'message' => odbc_errormsg(),
						'line' => $insert,
						'table' => $data['tableName'],
					);
					return $res;
					break;
				}
			}
		}
		return $res;
	}

	public function findDocumentReference($hanaInstance, $type, $table, $param) {
		$res = array();
		if ($type == 0) {
			error_log( 'type -> 0' );
			$sql = "SELECT head.NOPERACION AS NOPERACIONORG, detail.*, head.foliopref AS U_EXC_SERIE, head.folionum AS U_EXC_NUMERO FROM ".$hanaInstance['db'].".".$table['head']." AS head INNER JOIN ".$hanaInstance['db'].".".$table['detail']." AS detail ON(head.NOPERACION = detail.NOPERACION) WHERE head.foliopref = '".$param['foliopref']."' AND head.folionum = ".$param['folionum'].";";
			error_log('findDocumentReference 0 SQL -> '.$sql);
		} else if ($type == 1) {
			error_log( 'type -> 1' );
			$sql = "SELECT head.NOPERACION AS NOPERACIONORG, detail.* FROM ".$hanaInstance['db'].".".$table['head']." AS head INNER JOIN ".$hanaInstance['db'].".".$table['detail']." AS detail ON(head.NOPERACION = detail.NOPERACION) WHERE head.foliopref = '".$param['foliopref']."' AND head.u_exx_nrofin >= ".$param['folionum']." AND head.u_exx_nroini <= ".$param['folionum'].";";
			/*
			error_log('findDocumentReference : ');
			error_log('Head -> ' . $table['head']);
			error_log('Detail boleta-> ' . $table['detail']);
			error_log('SQL -> ' . $sql);
			*/
		} else if ($type == 2) {
			error_log( 'type -> 2' );
			//Efectivo no tiene itencode en el detalle, se debe obtener con 'item'
			$sql = "SELECT head.NOPERACION AS NOPERACIONORG, shipDetail.itemcode AS ITEMCODE, detail.item AS ITEM, head.foliopref AS U_EXC_SERIE, head.folionum AS U_EXC_NUMERO FROM ".$hanaInstance['db'].".".$table['head']." AS head INNER JOIN ".$hanaInstance['db'].".".$table['detail']." AS detail ON(head.NOPERACION = detail.NOPERACION) INNER JOIN ".$hanaInstance['db'].".".$table['shipmentDetail']." AS shipDetail ON (detail.noperacionref = shipDetail.noperacion) WHERE head.foliopref = '".$param['foliopref']."' AND head.folionum = ".$param['folionum'].";";
			error_log('findDocumentReference 2 SQL -> '.$sql);
		}

		$result = odbc_exec($hanaInstance['instance'], $sql);
		while ($rows = odbc_fetch_object($result)) {
			//$res[] = $rows;
			$res[] = array(
				'itemcode' => $rows->ITEMCODE,
				'item' => $rows->ITEM,
				'noperacion' => $rows->NOPERACIONORG,
				'sSerieDocumentoOrigen' => $rows->U_EXC_SERIE,
				'sNumeroDocumentoOrigen' => $rows->U_EXC_NUMERO,
			);
		}
		return $res;
	}

	public function getTableConfiguration() {
		global $sqlca;

		$res = array();
		$sql = "SELECT * FROM sap_mapeo_tabla ORDER BY 1;";

		$result = $sqlca->query($sql);
		if ($sqlca->query($sql) > 0) {
			while ($result = $sqlca->fetchRow()) {
				$res[] = $result;
			}
			return $res;
		} else {
			return null;
		}
	}

	public function getDetailTableConfigurationById($param) {
		global $sqlca;

		$res = array();
		$sql = "SELECT *
FROM
sap_mapeo_tabla_detalle
WHERE
id_tipo_tabla = ".$param['table_id']."
ORDER BY 2;"; //Se cambio a ORDER BY 2, tenia ORDER BY 1, se cambio para que se vea ordenado

		/*** Agregado 2020-02-14 ***/
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";
		/***/

		$this->_error_log('getDetailTableConfigurationById: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$c = 0;
		while ($reg = $sqlca->fetchRow()) {
			$res[] = $reg;
			$c++;
		}
		return array(
			'error' => false,
			'detailTableConfiguration' => $res,
			'count' => $c,
		);
	}

	/**
	 * Insertar registro de día exportado
	 */
	public function addDayExporter($req, $res, $user) {
		global $sqlca;
		$res = json_encode($res);
		$sql = "INSERT INTO sap_exporter (
 id,
 created,
 createdby,
 warehouse_code,
 systemdate,
 description
) VALUES (
 DEFAULT,
 NOW(),
 ".$user['user_id'].",
 '*',
 '".$req['initial_date']."',
 '{$res}'
);";
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		} else {
			return array('error' => false);
		}
	}

	/**
	 * Consultar días exportados
	 */
	public function getDayExporter($type, $req) {
		global $sqlca;

		$res = array();
		if ($type == 0) {
			$sql = "SELECT
 se.*,
 usr.ch_login AS username
FROM sap_exporter se
LEFT JOIN int_usuarios_passwd usr ON (se.createdby = usr.uid)
ORDER BY se.systemdate DESC, se.created DESC LIMIT 7;";
		} else {
			$sql = "SELECT
 se.*,
 usr.ch_login AS username
FROM sap_exporter se
LEFT JOIN int_usuarios_passwd usr ON (se.createdby = usr.uid)
WHERE se.systemdate = '".$req['initial_systemdate']."' ORDER BY se.systemdate;";
		}

		$result = $sqlca->query($sql);
		if ($sqlca->query($sql) > 0) {
			while ($result = $sqlca->fetchRow()) {
				$res[] = $result;
			}
			return $res;
		} else {
			return null;
		}
	}

	/**
	 * Consultar si el día a exportar ya existe en sap_exporter
	 */

	public function getNOperacionTicket($res) {
		if (isset($this->ticketHead[$res['_serie']][$res['_turn']])) {
			$val = $this->ticketHead[$res['_serie']][$res['_turn']];
			if ($res['_number'] >= $val['u_exx_nroini'] && $res['_number'] <= $val['u_exx_nrofin']) {
				return $val['noperacion'];
			} else {
				error_log('No valido, u_exx_nroini: '.$val['u_exx_nroini'].' u_exx_nrofin: '.$val['u_exx_nrofin'].' - _number: '.$res['_number']);
				return '';
			}
		} else {
			error_log('No existe en ticketHead');
			return '';
		}
	}

	public function getNOperacionTicketDetail($res, $noperacion) {
		error_log($reg['_number']);
		$res['_number'] = substr($res['_number'], 2);
		if (isset($this->ticketHead[$res['_number']])) {
			$val = $this->ticketHead[$res['_number']];
			if ($res['_number'] == $val['u_exx_nroini']) {
				return $val['noperacion'];
			} else {				
				return $noperacion;
			}			
		}else{
			return $noperacion;
		}
	}

	public function preLetterBPartner($l, $t) {
		$t = $this->cleanStr($t);
		if ($t == '') {
			return $l.'00099999999';
		}
		if (strlen($t) == 8) {
			$t = str_pad($t,11,"0",STR_PAD_LEFT);
			$t = $l.$t;
		} else if (strlen($t) == 11) {
			$t = $t != '' ? $l.$t : '';
		}

		return $t;
	}

	public function typeValue($val) {
		if (is_string($val)) {
			$val = "'".utf8_decode($val)."'";
		}
		if (is_null($val)) {
			$val = "NULL";
		}
		return $val;
	}

	/**
	 * Convertir unidad de medida
	 * @param type Int 0: ltr. a gal.; 1: M3 a gal.
	 * @param co float
	 * @return float
	 */
	function converterUM($data) {
		if($data['type'] == 0) {
			return $data['co'] / 3.785411784;//11620307 - GLP
		} else if($data['type'] == 1) {
			return $data['co'] / 3.15;//11620308 - GNV
		} else {
			return $data['co'];
		}
	}

	function getFormatNumber($data) {
		return round($data['number'], $data['decimal']);
		//return number_format($data['number'], $data['decimal'], '.', ',');
	}

	/**
	 * Devuelve el calculo de los documentos: Normal(18), descuento, exonerado y gratuito
	 * @param int $reg['typetax'] - Tipo de impuesto
	 * @param int $reg['taxable_operations'] - Monto de operaciones gravadas
	 * @param int $reg['disc'] - Descuento
	 * @param int $reg['cnf_igv_ocs'] - IGV OCS
	 * @param int $reg['tax_total'] - impuesto total del documento
	 * @param int $reg['grand_total'] - Monto total del documento
	 */
	function calcAmounts($reg) {
		if ($reg['typetax'] == 0) {
			$reg['taxable_operations'] = $reg['taxable_operations'] - $reg['disc'];
			$reg['tax_total'] = $reg['taxable_operations'] * $reg['cnf_igv_ocs'];
			$reg['grand_total'] = $reg['taxable_operations'] + $reg['tax_total'];
		} else if ($reg['typetax'] == 1) {
			$reg['taxable_operations'] = $reg['taxable_operations'] - $reg['disc'];
			$reg['tax_total'] = $reg['taxable_operations'] * 0;
			$reg['grand_total'] = $reg['taxable_operations'] + $reg['tax_total'];
		} else if ($reg['typetax'] == 2) {
			$reg['taxable_operations'] = 0;
			$reg['tax_total'] = 0;
			$reg['grand_total'] = 0;
		}
		return $reg;
	}

	function delete_export_day($arrDataGET){
		global $sqlca;

		$sql = "DELETE FROM sap_exporter WHERE id = " . strip_tags(stripslashes($arrDataGET["iID"]));
		$iStatus = $sqlca->query($sql);

		$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'danger', 'sMessage' => 'Problemas al eliminar cliente SQL - delete_export_day()');
		if ($iStatus >= 0)
			$arrResponse = array('iStatus' => $iStatus, 'sStatus' => 'success', 'sMessage' => 'Exportación eliminada satisfactoriamente');
	    return $arrResponse;
	}

	/*** Guardar/editar Centro Costo ***/
	function guardarCentroCosto($id_tabla, $nombre, $consult_warehouse, $codigo_sap){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$nombre = (trim($nombre) == "") ? "" : $nombre;
		$sql = "INSERT INTO sap_mapeo_tabla_detalle (id_tipo_tabla, opencomb_codigo, sap_codigo, name) VALUES('$id_tabla','$consult_warehouse','$codigo_sap','$nombre');";
		$query = pg_query($dbconn, $sql);
		return $query;
	}
	function editarCentroCosto($id_tabla, $id, $nombre, $consult_warehouse, $codigo_sap){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$nombre = (trim($nombre) == "") ? "" : $nombre;
		$sql = "UPDATE sap_mapeo_tabla_detalle 
				SET    opencomb_codigo = '$consult_warehouse', sap_codigo = '$codigo_sap', name = '$nombre'
				WHERE  id_tipo_tabla = '$id_tabla' 
				AND    id_tipo_tabla_detalle = '$id';";		
		$query = pg_query($dbconn, $sql);
		return $query;
	}
	/***/

	/*** Guardar/editar Almacen ***/
	function guardarAlmacen($id_tabla, $nombre, $consult_warehouse, $codigo_sap){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$nombre = (trim($nombre) == "") ? "" : $nombre;
		$sql = "INSERT INTO sap_mapeo_tabla_detalle (id_tipo_tabla, opencomb_codigo, sap_codigo, name) VALUES('$id_tabla','$consult_warehouse','$codigo_sap','$nombre');";
		$query = pg_query($dbconn, $sql);
		return $query;
	}
	function editarAlmacen($id_tabla, $id, $nombre, $consult_warehouse, $codigo_sap){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$nombre = (trim($nombre) == "") ? "" : $nombre;
		$sql = "UPDATE sap_mapeo_tabla_detalle 
				SET    opencomb_codigo = '$consult_warehouse', sap_codigo = '$codigo_sap', name = '$nombre'
				WHERE  id_tipo_tabla = '$id_tabla' 
				AND    id_tipo_tabla_detalle = '$id';";		
		$query = pg_query($dbconn, $sql);
		return $query;
	}
	/***/

	/*** Guardar/editar Tarjeta Credito ***/
	function guardarTarjetaCredito($id_tabla, $nombre, $consult_warehouse, $codigo_sap){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$nombre = (trim($nombre) == "") ? "" : $nombre;
		$sql = "INSERT INTO sap_mapeo_tabla_detalle (id_tipo_tabla, opencomb_codigo, sap_codigo, name) VALUES('$id_tabla','$consult_warehouse','$codigo_sap','$nombre');";
		$query = pg_query($dbconn, $sql);
		return $query;
	}
	function editarTarjetaCredito($id_tabla, $id, $nombre, $consult_warehouse, $codigo_sap){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$nombre = (trim($nombre) == "") ? "" : $nombre;
		$sql = "UPDATE sap_mapeo_tabla_detalle 
				SET    opencomb_codigo = '$consult_warehouse', sap_codigo = '$codigo_sap', name = '$nombre'
				WHERE  id_tipo_tabla = '$id_tabla' 
				AND    id_tipo_tabla_detalle = '$id';";		
		$query = pg_query($dbconn, $sql);
		return $query;
	}
	/***/

	/*** Guardar/editar Fondo Efectivo ***/
	function guardarFondoEfectivo($id_tabla, $nombre, $consult_fondo_efectivo, $codigo_sap){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$nombre = (trim($nombre) == "") ? "" : $nombre;
		$sql = "INSERT INTO sap_mapeo_tabla_detalle (id_tipo_tabla, opencomb_codigo, sap_codigo, name) VALUES('$id_tabla','$consult_fondo_efectivo','$codigo_sap','$nombre');";
		$query = pg_query($dbconn, $sql);
		return $query;
	}
	function editarFondoEfectivo($id_tabla, $id, $nombre, $consult_fondo_efectivo, $codigo_sap){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$nombre = (trim($nombre) == "") ? "" : $nombre;
		$sql = "UPDATE sap_mapeo_tabla_detalle 
				SET    opencomb_codigo = '$consult_fondo_efectivo', sap_codigo = '$codigo_sap', name = '$nombre'
				WHERE  id_tipo_tabla = '$id_tabla' 
				AND    id_tipo_tabla_detalle = '$id';";		
		$query = pg_query($dbconn, $sql);
		return $query;
	}
	/***/

	/*** Buscar ***/
	function buscar($id_tipo_tabla, $id_tipo_tabla_detalle){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$sql = "SELECT * 
		        FROM   sap_mapeo_tabla_detalle 
		        WHERE  id_tipo_tabla = '$id_tipo_tabla' 
				AND    id_tipo_tabla_detalle = '$id_tipo_tabla_detalle';";				
		$query = pg_query($dbconn, $sql);        
        $fila = pg_fetch_assoc($query);
        return $fila;
	}
	/***/

	/*** Eliminar ***/
	function eliminar($id_tipo_tabla, $id_tipo_tabla_detalle){
		$dbconn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
		pg_set_client_encoding($dbconn, "utf8");

		$sql = "DELETE FROM sap_mapeo_tabla_detalle 
		        WHERE id_tipo_tabla = '$id_tipo_tabla' 
				AND id_tipo_tabla_detalle = '$id_tipo_tabla_detalle';";
		$query = pg_query($dbconn, $sql);
		return $query;
	}	
	/***/
}
