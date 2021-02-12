<?php
class m_innova_1 {
	var $isDebug = true;//Define el modo de depuracion
	var $isViewTableName = true;//Define la visibilidad del nombre de las tablas HANA en la previsualizacion
	var $bpartners = array();

	function __construct() {
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

	/**
	 * Almacenes
	 */
	public function getWarehouse() {
		global $sqlca;
		$sql = "
SELECT
 ch_almacen AS id,
 ch_almacen||' - '||ch_nombre_almacen AS name
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen = '1'
ORDER BY
 ch_almacen;
 		";
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
	 * Ultimo día cerrado
	 */
	public function getLastDayClose() {
		global $sqlca;
		$sql = "SELECT
 TO_CHAR(da_fecha - integer '1', 'YYYY-MM-DD') AS date
FROM
 pos_aprosys
WHERE
 ch_poscd = 'A'
ORDER BY
 da_fecha DESC
LIMIT 1;";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		return $row['date'];
	}

	/**
	 * Valor de impuesto
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
	* Lista de tablas de configuración de interfaz ERP (Proveedor)
	*/
	public function getTableConfiguration() {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 TBERP.*
FROM
 sap_mapeo_tabla AS TBERP
 JOIN tipo_interface AS TIPOERP
  USING (id_tipo_interface)
WHERE
 TIPOERP.id_tipo_interface = (SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1)
ORDER BY
 1;
		";

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
		$sql = "
SELECT
 *
FROM
 sap_mapeo_tabla_detalle
WHERE
 id_tipo_tabla = " . $param['table_id'] . "
ORDER BY
 1;
		";

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
	 * Socios(Clientes)
	 */
	public function getBPartner($param) {
		global $sqlca;

		$res = array();
		$sql = "
SELECT
 TRIM(bpartner.Nu_Codigo_Cliente) AS cardcode,
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
  AND FC.dt_fac_fecha BETWEEN '{$param['initial_date']}' AND '{$param['initial_date']}'
  --AND FC.ch_almacen = '{$param['warehouse']}'
GROUP BY
 CLI.cli_codigo)

UNION

(SELECT
 TRIM(CLI.cli_codigo) AS Nu_Codigo_Cliente,
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
 VC.dt_fecha BETWEEN '{$param['initial_date']} 00:00:00' AND '{$param['final_date']} 23:59:59'
 --AND VC.ch_sucursal = '{$param['warehouse']}'
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
 {$param['pos_trans']} AS VC
 JOIN ruc AS CLI ON(VC.ruc = CLI.ruc)
WHERE
 VC.dia BETWEEN '{$param['initial_date']} 00:00:00' AND '{$param['final_date']} 23:59:59'
 --AND VC.ch_sucursal = '{$param['warehouse']}'
GROUP BY CLI.ruc)

) bpartner
GROUP BY 1;
		";

		$this->_error_log($param['tableName'].' - getBPartner: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}

		while ($reg = $sqlca->fetchRow()) {

			$reg['cardname'] = str_replace("'", "''", $reg['cardname']);
			$reg['cardcode'] = trim($reg['cardcode']);

			$this->bpartners[$reg['cardcode']] = array(
				'cardcode' => $reg['cardcode'],
				'cardname' => $this->cleanStr($reg['cardname']),
				'federaltaxid' => $this->cleanStr($reg['federaltaxid']),
				'street' => $this->cleanStr($reg['street']),
			);
		}
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
 PT.es::INTEGER || PT.caja || PT.trans AS noperacion,
 CASE WHEN FIRST(pt.td) IS NULL OR FIRST(pt.usr) = '' THEN 'T'
 ELSE
  CASE WHEN FIRST(pt.tm) = 'A' THEN 'NC'
  ELSE
   FIRST(pt.td)
  END
 END AS documenttypeocs,
 FIRST(pt.ruc) AS cardcode,
 FIRST(to_char(pt.fecha, 'DD/MM/YYYY')) AS docdate,
 FIRST(pt.fecha) AS created,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN LPAD(FIRST(pt.caja),3,'000'::text)
 ELSE FIRST(pt.usr) END AS foliopref,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN TO_CHAR(FIRST(pt.trans),'FM9999999999')
 ELSE '' END AS folionum,
 ROUND(SUM(pt.igv), 2) AS tax_total,
 ROUND(SUM(pt.importe) - SUM(pt.igv), 2) AS subtotal,
 ROUND(SUM(pt.importe), 2) AS grand_total,
 ROUND(FIRST(ABS(COALESCE(PTDSCT.importe_descuento, 0))), 2) AS disc,
 FIRST(PT.placa) AS plate,
 FIRST(PT.turno) AS turn,
 FIRST(PT.indexa) AS u_exc_nrotarjbonus,
 FIRST(pt.caja) AS u_exc_maqreg,
 FIRST(tc.tca_venta_oficial) AS exchange_rate,
 CASE WHEN FIRST(pt.precio) = 0 AND FIRST(pt.importe) = 0 THEN 'A'
 ELSE 'D' END AS status,
 CASE WHEN FIRST(pt.usr) IS NULL OR FIRST(pt.usr) = '' THEN '0'
 ELSE 1 END AS _isfe,
 FIRST(PT.pump) AS pump,
 FIRST(ERPCC.sap_codigo) AS company_code_innova,
 FIRST(ERPALMA.sap_codigo) AS office_code_innova
FROM
 {$param['pos_trans']} AS pt
 JOIN inv_ta_almacenes AS ALMA
  ON (ALMA.ch_almacen = PT.es)
 JOIN sap_mapeo_tabla_detalle AS ERPCC
  ON (ERPCC.opencomb_codigo = ALMA.ch_sucursal AND ERPCC.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Centro Costo' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1) LIMIT 1))
 JOIN sap_mapeo_tabla_detalle AS ERPALMA
  ON (ERPALMA.opencomb_codigo = ALMA.ch_almacen AND ERPALMA.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Almacen' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1) LIMIT 1))
 LEFT JOIN int_tipo_cambio AS tc
  ON (tc.tca_fecha = pt.dia AND tc.tca_moneda = '02')
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento,
  PT.importe AS importe_descuento
 FROM
  {$param['pos_trans']} AS PT
 WHERE
  PT.dia BETWEEN '{$param['initial_date']} 00:00:00' AND '{$param['final_date']} 23:59:59'
  AND pt.td IN ('F','B')
  AND PT.grupo = 'D'
 ) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
WHERE
 pt.dia BETWEEN '{$param['initial_date']} 00:00:00' AND '{$param['final_date']} 23:59:59'
 AND pt.td IN ('F', 'B')
 AND pt.usr IS NOT NULL AND pt.usr != ''
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
				$reg['ship'] = $reg['foliopref'].$reg['folionum'];
			}

			$bpartner = $this->bpartnerInvoice($reg['cardcode']);

			//No se almacena el detalle porque en el metodo de notas de credito consulta a postrans y estas deben aplicarse el mismo día
			$res[] = array(
				'doctype' => $this->documentType($reg['documenttypeocs']),
				'documentno' => $reg['ship'],
				'status' => $reg['status'],
				'ship1' => '',
				'ship2' => '',
				'ship3' => '',
				'ship4' => '',
				'ship5' => '',
				'docdate' => $reg['docdate'],
				'cardcode' => $bpartner['taxid'],
				'cardname' => $bpartner['name'],
				'cardaddress' => $bpartner['address'],
				'currency_code' => '01',
				'exchange_rate' => $reg['exchange_rate'],
				'due_date' => $reg['docdate'],
				'pay_date' => $reg['docdate'],
				'type_invoice' => '0',//Bienes
				'is_export' => '0',//Nacional
				'observation' => '',
				'purchase_order_number' => '',
				'aff_perception' => '0',
				'perception' => 0,//No
				'item' => 1,//(Total de items que contiene el detalle)
				'subtotal' => $reg['subtotal'],
				'tax' => $reg['tax_total'],
				'disc' => $reg['disc'],
				'doctotal' => (float)$reg['grand_total'],
				'perception_total' => 0,
				'doctotal_perception_total' => (float)$reg['grand_total'],
				'company_code_innova' => $reg['company_code_innova'],//innnova
				'is_anticipation' => 0,//No
				'is_reg_anticipation' => 0,//No
				'office_code_innova' => $reg['office_code_innova'],//innnova
				'created' => $reg['created'],
				//'machine_num' => $reg['u_exc_maqreg'],
				'machine_num' => '',//No tienen maquinas de registradoras, ya que cuentan con FE SUNAT 
				'turn' => $reg['turn'],
				'card_bonus' => $reg['u_exc_nrotarjbonus'],
				'observation2' => 'VENTA',
				'is_detraction' => '0',//No
				'code_detraction' => '',
				'per_detraction' => 0,
				'total_detraction' => 0,
				'vehicle' => '',
				'num_scop' => '',
				'isle' => $reg['pump'],
				'plate' => $reg['plate'],
				'net_weight' => 0,
				'gross_weight' => 0,
				'drained weight' => 0,
				'bidding' => '',
			);
		}

		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 ftfc.ch_fac_tipodocumento AS documenttypeocs,
 CASE WHEN ftfc.nu_fac_recargo3 IN(0, 1, 3) THEN 'D' ELSE 'A' END AS status,
 client.cli_ruc AS cardcode,
 client.cli_razsocial AS cardname,
 client.cli_direccion AS cardaddress,
 ftfc.ch_fac_moneda AS currency_code,
 tc.tca_venta_oficial AS exchange_rate,
 to_char(ftfc.dt_fac_fecha, 'DD/MM/YYYY') AS docdate,
 ftfc.dt_fac_fecha AS created,
 com.ch_fac_observacion1 AS observation,
 ftfc.ch_fac_seriedocumento AS foliopref,
 ftfc.ch_fac_numerodocumento AS folionum,
 ftfc.nu_fac_impuesto1 AS tax_total,
 (util_fn_igv()/100) AS cnf_igv_ocs,
 ftfc.nu_fac_valorbruto AS taxable_operations,
 ftfc.nu_fac_valortotal AS grand_total,
 ftfc.ch_fac_tiporecargo3 AS turn,
 CASE
  WHEN ftfc.ch_fac_tiporecargo2 IS NULL OR ftfc.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
  WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 = 0 THEN 1 -- EXO
  WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 > 0 THEN 2 -- TG
 END AS typetax,
 '' AS plate,
 com.nu_fac_complemento_direccion AS data_detraction,
 COALESCE(ftfc.nu_fac_descuento1, 0) AS disc,
 (util_fn_igv()/100) AS cnf_igv_ocs,
 ftfc.ch_fac_anticipo AS is_anticipation,
 ERPCC.sap_codigo AS company_code_innova,
 ERPALMA.sap_codigo AS office_code_innova,
 COALESCE(FD.nu_cantidad_item_detalle, 0) AS nu_cantidad_item_detalle
FROM
 fac_ta_factura_cabecera ftfc
 JOIN inv_ta_almacenes AS ALMA ON (ALMA.ch_almacen = ftfc.ch_almacen)
 JOIN sap_mapeo_tabla_detalle AS ERPCC
  ON (ERPCC.opencomb_codigo = ALMA.ch_sucursal AND ERPCC.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Centro Costo' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1) LIMIT 1))
 JOIN sap_mapeo_tabla_detalle AS ERPALMA
  ON (ERPALMA.opencomb_codigo = ALMA.ch_almacen AND ERPALMA.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Almacen' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1) LIMIT 1))
 JOIN int_tabla_general doctype_s ON(ftfc.ch_fac_tipodocumento = SUBSTRING(TRIM(doctype_s.tab_elemento) for 2 from length(TRIM(doctype_s.tab_elemento))-1) AND doctype_s.tab_tabla ='08' AND doctype_s.tab_elemento != '000000')
 JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
 LEFT JOIN fac_ta_factura_complemento AS com ON (ftfc.cli_codigo = com.cli_codigo AND ftfc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND ftfc.ch_fac_tipodocumento = com.ch_fac_tipodocumento)
 LEFT JOIN int_tipo_cambio tc ON (tc.tca_fecha = ftfc.dt_fac_fecha AND tc.tca_moneda = '02')
 LEFT JOIN (
  SELECT
   FC.ch_fac_tipodocumento, FC.ch_fac_seriedocumento, FC.ch_fac_numerodocumento, FC.cli_codigo,
   COUNT(FD.*) AS nu_cantidad_item_detalle
  FROM
   fac_ta_factura_cabecera AS FC
   JOIN fac_ta_factura_detalle AS FD
    USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
  WHERE
   FC.dt_fac_fecha BETWEEN '{$param['initial_date']}' AND '{$param['final_date']}'
   AND FC.ch_fac_tipodocumento IN ('10', '35')
  GROUP BY
   ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo
 ) AS FD ON (ftfc.cli_codigo = FD.cli_codigo AND ftfc.ch_fac_seriedocumento = FD.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = FD.ch_fac_numerodocumento AND ftfc.ch_fac_tipodocumento = FD.ch_fac_tipodocumento)
WHERE
 ftfc.dt_fac_fecha BETWEEN '{$param['initial_date']}' AND '{$param['final_date']}'
 AND ftfc.ch_fac_tipodocumento IN ('10', '35');
		";
		//filtro para buscar los documentos emitido o anulados

		$this->_error_log($param['tableName'].' - getInvoiceHeaderSaleCash: '.$sql.' [LINE: '.__LINE__.']');

		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}

		while ($reg = $sqlca->fetchRow()) {
			$c++;

			$reg['folionum'] = str_pad($reg['folionum'], 8, "0", STR_PAD_LEFT);//competar los 'ceros' para numero de documentos
			$reg['ship'] = TRIM($reg['foliopref']).$reg['folionum'];//revisar en los casos que es documento manual sin facturación electrónica

			$data = $this->calcAmounts($reg);

			$is_detraction = 0;
			$code_sunat_detraction = '';
			$per_detraction = 0;
			$total_detraction = 0;

			if ($reg['data_detraction'] != NULL || $reg['data_detraction'] != '') {
				$is_detraction = 1;
				$data_detraction = explode('*', $reg['data_detraction']);
				$code_sunat_detraction = isset($data_detraction[0]) ? $data_detraction[0] : '';
				$per_detraction = isset($data_detraction[2]) ? $data_detraction[2] : 0;
				$total_detraction = isset($data_detraction[1]) ? $data_detraction[1] : 0;
			}

			$data['taxable_operations'] = $this->getFormatNumber(array('number' => $data['taxable_operations'], 'decimal' => 2));
			$data['tax_total'] = $this->getFormatNumber(array('number' => $data['tax_total'], 'decimal' => 2));
			$data['grand_total'] = $this->getFormatNumber(array('number' => $data['grand_total'], 'decimal' => 2));

			if (trim($reg['is_anticipation']) == 'S') {
				$reg['is_anticipation'] = 1;
			} else {
				$reg['is_anticipation'] = 0;
			}

			$res[] = array(
				'doctype' => $this->documentType($reg['documenttypeocs']),
				'documentno' => $reg['ship'],
				'status' => $reg['status'],
				'ship1' => '',
				'ship2' => '',
				'ship3' => '',
				'ship4' => '',
				'ship5' => '',
				'docdate' => $reg['docdate'],
				'cardcode' => $reg['cardcode'],
				'cardname' => $reg['cardname'],
				'cardaddress' => $reg['cardaddress'],
				'currency_code' => $reg['currency_code'],
				'exchange_rate' => $reg['exchange_rate'],
				'due_date' => $reg['docdate'],
				'pay_date' => $reg['docdate'],
				'type_invoice' => '0',//dinamico...
				'is_export' => '0',
				'observation' => $reg['observation'],
				'purchase_order_number' => '',
				'aff_perception' => '0',
				'perception' => 0,
				'item' => $data['nu_cantidad_item_detalle'],//(Total de items que contiene el detalle)
				'subtotal' => (float)$data['taxable_operations'],
				'tax' => (float)$data['tax_total'],
				'disc' => (float)$data['disc'],
				'doctotal' => (float)$data['grand_total'],
				'perception_total' => 0,
				'doctotal_perception_total' => (float)$data['grand_total'],
				'company_code_innova' => $reg['company_code_innova'],
				'is_anticipation' => $reg['is_anticipation'],
				'is_reg_anticipation' => 0,//falta
				'office_code_innova' => $reg['office_code_innova'],
				'created' => $reg['created'],
				'machine_num' => '',
				'turn' => $reg['turn'],
				'card_bonus' => '',
				'observation2' => 'VENTA',
				'is_detraction' => $is_detraction,
				'code_detraction' => $code_sunat_detraction,
				'per_detraction' => $per_detraction,
				'total_detraction' => $total_detraction,
				'vehicle' => '',
				'num_scop' => '',
				'isle' => '',
				'plate' => $reg['plate'],
				'net_weight' => 0,
				'gross_weight' => 0,
				'drained weight' => 0,
				'bidding' => '',
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
 PT.es::INTEGER || PT.caja || PT.trans AS noperacion,
 CASE WHEN FIRST(pt.td) IS NULL OR FIRST(pt.usr) = '' THEN 'T'
 ELSE
  CASE WHEN FIRST(pt.tm) = 'A' THEN 'NC'
  ELSE
   FIRST(pt.td)
  END
 END AS documenttypeocs,
 ERPITEM.sap_codigo AS itemcode,
 ROUND(SUM(PT.cantidad), 3) AS quantity,
 FIRST(ERPUM.sap_codigo) AS uom,
 ROUND((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)))) / {$param['tax']}, 5) AS price_no_tax,
 CASE WHEN FIRST(PT.precio) = 0 AND FIRST(PT.cantidad) = 0 AND FIRST(PT.importe) = 0 THEN--ESTA ANULADO
  0
 ELSE 
  ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / {$param['tax']}) * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / {$param['tax']}), 2)
 END AS per_discprcnt,
 --ROUND((FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0)) / {$param['tax']}) * 100) / ((SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 1)))) / {$param['tax']}), 2) AS per_discprcnt,
 ROUND(FIRST(ABS(COALESCE(PTDSCT.importe_descuento, 0))), 2) AS discprcnt,
 ROUND(SUM(PT.precio) + FIRST(ABS(COALESCE(PTDSCT.precio_descuento, 0))), 2) AS price,
 ROUND(({$param['tax']} - 1) * 100, 2) AS per_tax,
 ROUND(SUM(PT.importe) - SUM(PT.igv), 2) AS subtotal,
 FIRST(pt.usr) AS foliopref
FROM
 {$param['pos_trans']} AS PT
 JOIN int_articulos AS ITEM ON (ITEM.art_codigo = PT.codigo)
 JOIN sap_mapeo_tabla_detalle AS ERPUM
  ON (ERPUM.opencomb_codigo = ITEM.art_unidad AND ERPUM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Unidad Medida' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1) LIMIT 1))
 JOIN sap_mapeo_tabla_detalle AS ERPITEM
  ON (ERPITEM.opencomb_codigo = ITEM.art_codigo AND ERPITEM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Items' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1) LIMIT 1))
 LEFT JOIN (
 SELECT
  PT.es,PT.caja,PT.trans,
  PT.precio AS precio_descuento,
  PT.importe AS importe_descuento
 FROM
  {$param['pos_trans']} AS PT
 WHERE
  PT.dia BETWEEN '{$param['initial_date']} 00:00:00' AND '{$param['final_date']} 23:59:59' AND
  pt.td IN ('F', 'B')
  AND PT.grupo = 'D'
 ) AS PTDSCT ON (PT.es = PTDSCT.es AND PT.caja = PTDSCT.caja AND PT.trans = PTDSCT.trans)
WHERE
 PT.dia BETWEEN '{$param['initial_date']} 00:00:00' AND '{$param['final_date']} 23:59:59'
 AND pt.td IN ('F', 'B')
 AND pt.usr IS NOT NULL AND pt.usr != ''
GROUP BY
 PT.es,
 PT.caja,
 PT.trans,
 ERPITEM.sap_codigo
ORDER BY
 1;
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

			$arr_foliopref = explode('-', $reg['foliopref']);
			$reg['foliopref'] = $arr_foliopref[0];
			$reg['folionum'] = $arr_foliopref[1];
			$reg['ship'] = $reg['foliopref'].$reg['folionum'];

			//No se almacena el detalle porque en el metodo de notas de credito consulta a postrans y estas deben aplicarse el mismo día
			//$discprcnt = ((float)$reg['_discprcnt'] * 100) / (float)$reg['_price'];
			$discprcnt = (float)$reg['discprcnt'];//actualmente sin IGV

			$res[] = array(
				'doctype' => $this->documentType($reg['documenttypeocs']),
				'ship' => $reg['ship'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'quantity' => (float)$reg['quantity'],
				'uom' => $reg['uom'],//falta
				'price' => (float)$reg['price_no_tax'],//sin igv
				'price_sale' => (float)$reg['price'],//con igv
				'per_discprcnt' => (float)$reg['per_discprcnt'],
				'discprcnt' => (float)$discprcnt,
				'subtotal' => (float)$reg['subtotal'],
				'per_tax' => (float)$reg['per_tax'],
				'aff_perception' => 0,
				'per_perception' => 0,
				'observation' => '',
			);
			$tmpDoc = $reg['noperacion'];
		}

		$sql = "
SELECT
 ftfc.ch_fac_tipodocumento||ftfc.ch_fac_seriedocumento||ftfc.ch_fac_numerodocumento AS noperacion,
 ftfc.ch_fac_tipodocumento AS documenttypeocs,
 ERPITEM.sap_codigo AS itemcode,
 ftfd.nu_fac_cantidad AS quantity,
 ERPUM.sap_codigo AS uom,
 ROUND(ftfd.nu_fac_precio, 2) AS price,
 ROUND((ftfd.nu_fac_precio / {$param['tax']}), 5) AS price_no_tax,
 '' AS per_discprcnt,
 ROUND(COALESCE(ftfd.nu_fac_descuento1, 0), 2) AS discprcnt,
 ROUND(({$param['tax']} - 1) * 100, 2) AS per_tax,--verificar
 ROUND(ftfc.nu_fac_valorbruto, 2) AS subtotal,
 CASE
  WHEN ftfc.ch_fac_tiporecargo2 IS NULL OR ftfc.ch_fac_tiporecargo2 = '' THEN 0 --NORMAL
  WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 = 0 THEN 1 --EXO
  WHEN ftfc.ch_fac_tiporecargo2 = 'S' AND ftfc.nu_fac_impuesto1 > 0 THEN 2 --TG
 END AS typetax,
 ftfc.ch_fac_seriedocumento AS foliopref,
 ftfc.ch_fac_numerodocumento AS folionum,
 ftfc.ch_fac_seriedocumento AS _serie,
 ftfc.ch_fac_numerodocumento::INTEGER AS _number,
 ftfc.ch_fac_tiporecargo3 AS _turn
FROM
 fac_ta_factura_cabecera ftfc
 JOIN fac_ta_factura_detalle ftfd USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
 JOIN int_articulos AS ITEM ON (ITEM.art_codigo = ftfd.art_codigo)
 JOIN sap_mapeo_tabla_detalle AS ERPUM
  ON (ERPUM.opencomb_codigo = ITEM.art_unidad AND ERPUM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Unidad Medida' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1) LIMIT 1))
 JOIN sap_mapeo_tabla_detalle AS ERPITEM
  ON (ERPITEM.opencomb_codigo = ITEM.art_codigo AND ERPITEM.id_tipo_tabla = (SELECT id_tipo_tabla FROM sap_mapeo_tabla WHERE no_tabla='Items' AND id_tipo_interface=(SELECT id_tipo_interface FROM tipo_interface WHERE no_tipo_interface='INNOVA' LIMIT 1) LIMIT 1))
 JOIN int_clientes client ON (ftfc.cli_codigo = client.cli_codigo)
WHERE
 ftfc.dt_fac_fecha BETWEEN '{$param['initial_date']}' AND '{$param['final_date']}'
 AND ftfc.ch_fac_tipodocumento IN ('10', '35')
ORDER BY
 _serie,
 _number,
 _turn;
		";

		$this->_error_log($param['tableName'].' - **getInvoiceDetailSaleCredit: '.$sql.' [LINE: '.__LINE__.']');
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		
		$tmpDoc = '';
		while ($reg = $sqlca->fetchRow()) {
			$c++;
			if ($tmpDoc == $reg['noperacion']) {
				$ci++;
			} else {
				$ci = 1;
			}

			$reg['folionum'] = str_pad($reg['folionum'], 8, "0", STR_PAD_LEFT);//competar los 'ceros' para numero de documentos
			$reg['ship'] = $reg['foliopref'].$reg['folionum'];

			$res[] = array(
				'doctype' => $this->documentType($reg['documenttypeocs']),
				'ship' => $reg['ship'],
				'item' => $ci,
				'itemcode' => $this->cleanStr($reg['itemcode']),
				'quantity' => (float)$reg['quantity'],
				'uom' => $reg['uom'],
				'price' => (float)$reg['price_no_tax'],//sin igv
				'price_sale' => (float)$reg['price'],//con igv
				'per_discprcnt' => (float)$reg['per_discprcnt'],
				'discprcnt' => (float)$reg['discprcnt'],
				'subtotal' => (float)$reg['subtotal'],
				'per_tax' => (float)$reg['per_tax'],
				'aff_perception' => 0,
				'per_perception' => 0,
				'observation' => '',
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

	function bpartnerInvoice($taxid) {
		if ($taxid == '') {
			return array(
				'taxid' => '99999999',
				'name' => 'CLIENTES VARIOS',
				'address' => ''
			);
		} else {
			if (isset($this->bpartners[$taxid])) {
				return array(
					'taxid' => $this->bpartners[$taxid]['cardcode'],
					'name' => $this->bpartners[$taxid]['cardname'],
					'address' => $this->bpartners[$taxid]['street'],
				);
			} else {
				return array('taxid' => $taxid, 'name' => '', 'address' => '');
			}
		}
	}

	function documentType($type) {
		/**
		 * Tipo de documentos que necesita Innova
		 * 01 - FACTURA
		 * 03 - BOLETA
		 * 12 - TICKET
		 * 02 - NOTA DE CREDITO
		*/
		if (trim($type) == 'F' || trim($type) == '10') {
			return '01';
		} else if (trim($type) == 'B' || trim($type) == '35') {
			return '03';
		} else if (trim($type) == 'T') {
			return '12';
		} else if (trim($type) == 'NC' || trim($type) == '20') {
			return '02';
		} else {
			return null;
		}
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

	function getFormatNumber($data) {
		return round($data['number'], $data['decimal']);
	}

	public function updDetailTableConfigurationById($arrDataPOST){
		global $sqlca;

		$query = "
UPDATE
 sap_mapeo_tabla_detalle
SET
 sap_codigo='" . $arrDataPOST['arrData']['sap_codigo'] . "',
 name='" . $arrDataPOST['arrData']['name'] . "',
 description='" . $arrDataPOST['arrData']['description'] . "'
WHERE
 id_tipo_tabla_detalle = " . $arrDataPOST['arrData']['id_tipo_tabla_detalle'] . "
		";

		$iStatus = $sqlca->query($query);
		if( (int)$iStatus < 0 )
			return array("sStatus" => "danger", "sMessage" => "Problemas al actualizar", "sMessageBD" => $sqlca->get_error());

		$res = array();
		$query = "
SELECT
 *
FROM
 sap_mapeo_tabla_detalle
WHERE
 id_tipo_tabla = " . $arrDataPOST['arrData']['id_tipo_tabla'] . "
ORDER BY
 1;
		";

		$iStatus = $sqlca->query($query);
		if( (int)$iStatus < 0 )
			return array("sStatus" => "danger", "sMessage" => "Problemas al obtener registros", "sMessageBD" => $sqlca->get_error());
		return array("sStatus" => "success", "sMessage" => "Registro actualizado", "detailTableConfiguration" => $sqlca->fetchAll(), "count" => $iStatus);
	}

	public function saveDetailTableConfigurationById($arrDataPOST){
		global $sqlca;

		$query = "
INSERT INTO sap_mapeo_tabla_detalle (
 id_tipo_tabla,
 id_tipo_tabla_detalle,
 opencomb_codigo,
 sap_codigo,
 name,
 description
) VALUES (
 " . $arrDataPOST['arrData']['id_tipo_tabla'] . ",
 nextval('seq_id_tipo_tabla_detalle'),
 '" . $arrDataPOST['arrData']['ocs_codigo'] . "',
 '" . $arrDataPOST['arrData']['sap_codigo'] . "',
 '" . $arrDataPOST['arrData']['name'] . "',
 '" . $arrDataPOST['arrData']['description'] . "'
);
		";

		$iStatus = $sqlca->query($query);
		if( (int)$iStatus < 0 )
			return array("sStatus" => "danger", "sMessage" => "Problemas al guardar", "sMessageBD" => $sqlca->get_error());

		$res = array();
		$query = "
SELECT
 *
FROM
 sap_mapeo_tabla_detalle
WHERE
 id_tipo_tabla = " . $arrDataPOST['arrData']['id_tipo_tabla'] . "
ORDER BY
 1;
		";

		$iStatus = $sqlca->query($query);
		if( (int)$iStatus < 0 )
			return array("sStatus" => "danger", "sMessage" => "Problemas al obtener registros", "sMessageBD" => $sqlca->get_error());
		return array("sStatus" => "success", "sMessage" => "Registro agregado", "detailTableConfiguration" => $sqlca->fetchAll(), "count" => $iStatus);
	}
}