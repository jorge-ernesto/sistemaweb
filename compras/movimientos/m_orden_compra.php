<?php
/**
 * Información de Tablas
 * com_cabecera - orden de compra CABECERA
 * com_detalle - orden de compra DETALLE
 * pedido_compra_cabecera - pedido de mercadería CABECERA
 * pedido_compra_detalle - pedido de mercadería DETALLE
 */
class modelOrderPurchase {
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
	 * Ultimo día cerrado
	 */
	public function getLastDayClose() {
		global $sqlca;
		$sql = "SELECT TO_CHAR(da_fecha - integer '1', 'YYYY-MM-DD') AS date FROM pos_aprosys WHERE ch_poscd = 'A' ORDER BY da_fecha DESC LIMIT 1;";
		$sqlca->query($sql);
		$row = $sqlca->fetchRow();
		return $row['date'];
	}

	public function getOrders($req) {
		global $sqlca;
		$res = array();

		$sql = "SELECT
 C.com_cab_numorden as id,
 A.ch_nombre_almacen warehouse_name,
 C.pro_codigo as bpartner_id,
 P.pro_razsocial as bpartner_name,
 to_char(com_cab_fechaorden, 'DD/MM/YYYY') as created,
 CASE WHEN ltrim(com_cab_moneda,'0')='1' THEN 'Soles' WHEN ltrim(com_cab_moneda,'0')='2' THEN 'Dolares' Else '-' END as currency_name,
 CASE WHEN cast(com_cab_tipcambio AS integer)!=0 THEN cast(com_cab_tipcambio AS varchar(10)) Else '-' END as exchange_rate_value,
 com_cab_imporden as base,
 CASE WHEN dev.mov_numero != '' THEN 'Recibido'  else 'Pendiente' END as status,
 CASE WHEN com_factu!= '' THEN com_factu Else '-' END as invoice_ref,
 C.num_seriedocumento as serie,
 TD.tab_desc_breve || ' ' || SUBSTR(MOVI.mov_docurefe, 1, 4) || ' ' || SUBSTR(MOVI.mov_docurefe, 5, 8) AS document
FROM
com_cabecera AS C
LEFT JOIN inv_ta_compras_devoluciones dev ON (dev.com_tipo_compra=C.num_tipdocumento AND dev.com_serie_compra=C.num_seriedocumento AND dev.com_num_compra=C.com_cab_numorden AND dev.mov_entidad=C.pro_codigo)
INNER JOIN 	int_proveedores AS P
	ON (P.pro_codigo = C.pro_codigo)
INNER JOIN 	inv_ta_almacenes AS A
	ON (A.ch_almacen = C.com_cab_almacen)
LEFT JOIN inv_movialma AS MOVI
	ON (c.num_tipdocumento = MOVI.com_tipo_compra AND c.num_seriedocumento = MOVI.com_serie_compra AND c.com_cab_numorden=MOVI.com_num_compra)
LEFT JOIN int_tabla_general AS TD
	ON (MOVI.mov_tipdocuref = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
WHERE
 C.com_cab_fechaorden BETWEEN '".$req['initial_date']."' and '".$req['end_date']."'
ORDER BY cast(C.com_cab_numorden as integer) DESC;";

		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$res[] = $reg;
		}
		return array(
			'error' => false,
			'data' => $res,
		);
	}

	public function getOrderById() {
		
	}

	public function getCurrency() {
		global $sqlca;
		$res = array();

		$sql = "SELECT
 TRIM(tab_elemento) AS id,
 TRIM(tab_elemento) || ' -- ' || TRIM(tab_descripcion) AS name
FROM int_tabla_general  
WHERE tab_tabla = '04' 
 AND tab_elemento != '000000' 
ORDER BY id;";
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$res[] = $reg;
		}
		return array(
			'error' => false,
			'data' => $res,
		);
	}

	public function getGeneralTable($value) {
		global $sqlca;
		$res = array();

		$sql = "SELECT
substr(TAB_ELEMENTO,5,2) AS id,
substr(TAB_ELEMENTO,5,2)  || ' -- ' || TAB_DESCRIPCION AS name
FROM INT_TABLA_GENERAL
WHERE TAB_TABLA = '".$value."' AND tab_elemento != '000000'
ORDER BY TAB_ELEMENTO";
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		while ($reg = $sqlca->fetchRow()) {
			$res[] = $reg;
		}
		return array(
			'error' => false,
			'data' => $res,
		);
	}

	public function getReasonForTransfer() {
		return array(
			array('id' => 0, 'name' => 'Venta'),
			array('id' => 1, 'name' => 'Venta Sujeta a Confirmacion del Comprador'),
			array('id' => 2, 'name' => 'Compra'),
			array('id' => 3, 'name' => 'Consignacion'),
			array('id' => 4, 'name' => 'Devolucion'),
			array('id' => 5, 'name' => 'Traslado entre Establecimentos de la Misma Empresa'),
			array('id' => 6, 'name' => 'Traslado de Bienes para Transformacion'),
			array('id' => 7, 'name' => 'Recojo de Bienes Transformados'),
			array('id' => 8, 'name' => 'Traslado por Emisor Itinerante de Comprobantes de Pago'),
			array('id' => 9, 'name' => 'Traslado Zona Primaria'),
			array('id' => 10, 'name' => 'Importacion'),
			array('id' => 11, 'name' => 'Exportacion'),
			array('id' => 12, 'name' => 'Venta con entrega a terceros'),
			array('id' => 13, 'name' => 'Otros'),
		);
	}

	public function getNextOrderIdByWarehouse($w) {
		global $sqlca;
		$res = array();

		$sql = "SELECT lpad(util_fn_corre_docs('01','".$w."', 'select' )::text,8,'0') as nextOrderId";

		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}
		$res = $sqlca->fetchRow();
		return array(
			'error' => false,
			'data' => $res[0],
		);
	}

	public function getMerchandiseOrder($param) {
		global $sqlca;
		$res = array();

		$sql = "SELECT
 cab.num_pedido,
 det.art_codigo,
 art.art_descripcion,
 art.art_unidad,
 det.ped_cantidad
FROM
 pedido_compra_cabecera AS cab
 JOIN pedido_compra_detalle AS det
  ON (cab.id_pedido_cabecera = det.id_pedido_cabecera)
 JOIN int_articulos AS art
  ON (det.art_codigo = art.art_codigo)
WHERE
 cab.num_pedido = " . $param['id'];

 		$status = $sqlca->query($sql);

		if ($status < 0) {
			return array(
				'error' => true,
				'erroCode' => -1,
				'alert' => 'alert-danger',
				'message' => 'Error al consultar',
				'data' => NULL
			);
		} else if ($status === 0) {
			return array(
				'error' => true,
				'alert' => 'alert-warning',
				'message' => 'Sin registros',
				'data' => NULL
			);
		} else {
			return array(
				'error' => false,
				'alert' => 'alert-info',
				'message' => 'Registros encontrados',
				'data' => $sqlca->fetchAll(),
			);
		}
	}

	public function saveOrderPurchase($data) {
		global $sqlca;

		$this->managerTransaction('BEGIN');
		$perception = array('val' => array(0, 0));
		$header = $data['arrHeaderOrderPurchase'];
		$freight = $data['arrFreightageOrderPurchase'];
		$detail = $data['arrDetailOrderPurchase'];
		$countDetail = count($detail);
		$countDetailInserts = 0;
		/*echo 'header<pre>';
		var_dump($header);
		echo '</pre>';
		echo '<hr>';
		echo 'freight<pre>';
		var_dump($freight);
		echo '</pre>';
		echo '<hr>';
		echo 'detail<pre>';
		var_dump($detail);
		echo '</pre>';
		echo '<hr>';*/

		//se debe obtener el numero real a asignar para orden
		//se guarda la percepcion cuando se marque
		$isPerception = $header['isPerception'] == 'true' ? 1 : 0;
		if ($header['isPerception'] == 'true') {
			$perception = $this->managerPerception(array(
				'number' => trim($header['serie']).trim($header['number']),
				'value' => $header['subtotal'],
				'perception' => $header['perception'],
			));
			if ($perception['error']) {
				$this->managerTransaction('ROLLBACK');
				unset($header, $freight, $detail);
				return array(
					'error' => true,
					'message' => 'Error al insertar percepción.',
				);
			}
		}

		$sql = "INSERT INTO com_cabecera(
 com_cab_numorden,
 num_seriedocumento,
 com_cab_fechaorden,
 com_cab_almacen,
 pro_codigo,
 com_cab_moneda,
 com_cab_tipcambio,
 com_cab_credito,
 com_cab_formapago,
 com_factu,
 com_cab_observacion,
 com_cab_fechaofrecida,
 com_cab_fecharecibida,
 com_cab_det_glosa,
 num_tipdocumento,
 com_cab_imporden,
 com_cab_recargo1,
 com_cab_estado,
 com_cab_transmision,
 com_ser,
 fecha_replicacion,
 percepcion,
 percepcion_i
) VALUES (
 '" . $header['number'] . "',
 '" . $header['serie'] . "',
 '" . $header['order_date'] . "',
 '" . $header['serie'] . "',
 '" . $header['bpartner_id'] . "',
 '" . $header['currency'] . "',
 " . $header['exangerate'] . ",
 '" . $header['isCredit'] . "',
 '" . $header['tendertype'] . "',
 '" . $header['invoiceText'] . "',
 '" . $header['comment'] . "',
 '" . $header['dateDelivery'] . "',
 '" . $header['dateDelivery'] . "',
 '" . $header['observation'] . "',
 '01',
 0.00,
 0.00,
 '1',
 't',
 '',--com_ser
 now(),
 '" . $perception['val'][0] . "',
 '" . $perception['val'][1] . "'
);";
		$status = $sqlca->query($sql);
		if ($status < 0) {
			unset($header, $freight, $detail);
			return array('error' => true, 'message' => 'Error SQL', 'data' => NULL);
		} else {
			//agregar flete, si se selecionó
			if ($freight['orderFreight'] == 'true') {
				$saveOrderFreight = $this->saveOrderFreight(array(
					'tran_codigo' => '00',
					'mov_numero' => "01".trim($header['serie']).trim($header['number']),
					'fe_emision' => $header['order_date'],
					'fe_flete' => $freight['orderDateTransfer'],
					'id_motivo_traslado' => $freight['orderReasonTransfer'],
					'no_placa' => $freight['orderPlate'],
					'no_licencia' => $freight['orderLicense'],
					'no_certificado_inscripcion' => $freight['orderCertificateInscription'],
					'id_transportista_proveedor' => $freight['orderCarrierId'],
				));
				if (!$saveOrderFreight) {
					$this->managerTransaction('ROLLBACK');
					unset($header, $freight, $detail);
					return array(
						'error' => true,
						'message' => 'Error al insertar el flete.',
					);
				}
			}

			//agregar detalle
			foreach ($detail as $key => $value) {
				$saveOrderDetail = $this->saveOrderDetail(array(
					'number' => $header['number'],
					'bpartner_id' => $header['bpartner_id'],
					'order_date' => $header['order_date'],
					'product_id' => $value['product_id'],
					'qty' => $value['qty'],
					'cost' => $value['cost'],
					'subtotal' => $value['subtotal'],
					'disc' => $value['disc'],
					'serie' => $header['serie'],
				));
				if ($saveOrderDetail) {
					$countDetailInserts++;
				}
			}

			error_log('$countDetail: '.$countDetail.' - $countDetailInserts: '.$countDetailInserts);
			if ($countDetail == $countDetailInserts) {
				if (!$this->saveOrderId($header)) {
					$this->managerTransaction('ROLLBACK');
					unset($header, $freight, $detail);
					return array(
						'error' => true,
						'message' => 'Error, inténtelo en otro momento.',
					);
				}

				$this->managerTransaction('COMMIT');
				unset($header, $freight, $detail);
				return array(
					'error' => false,
					'message' => 'Insertado correctamente.',
				);
			} else {
				$this->managerTransaction('ROLLBACK');
				unset($header, $freight, $detail);
				return array(
					'error' => false,
					'message' => 'Error al insertar el detalle.',
				);
			}
		}
	}

	public function saveOrderFreight($data) {
		global $sqlca;
		$sql = "INSERT INTO flete(
 tran_codigo,
 mov_numero,
 fe_emision,
 fe_flete,
 id_motivo_traslado,
 no_placa,
 no_licencia,
 no_certificado_inscripcion,
 id_transportista_proveedor
) VALUES (
 '" . $data['tran_codigo'] . "',
 '" . $data['mov_numero'] . "',
 '" . $data['fe_emision'] . "',
 '" . $data['fe_flete'] . "',
 " . $data['id_motivo_traslado'] . ",
 '" . pg_escape_string($data['no_placa']) . "',
 '" . pg_escape_string($data['no_licencia']) . "',
 '" . pg_escape_string($data['tran_codigo']) . "',
 '" . trim($data['id_transportista_proveedor']) . "'
);";
		error_log($sql);
		if ($sqlca->query($sql) < 0)
			return false;
		return true;
	}

	public function saveOrderDetail($data) {
		global $sqlca;
		$sql = "INSERT INTO com_detalle (
 com_cab_numorden,
 pro_codigo,
 com_det_fechaentrega,
 art_codigo,
 com_det_cantidadpedida,
 com_det_precio,
 com_det_imparticulo,
 com_det_descuento1,
 num_tipdocumento,
 num_seriedocumento,
 com_det_estado,
 fecha_replicacion
) VALUES (
 '" . trim($data['number']) . "',
 '" . trim($data['bpartner_id']) . "',
 '" . $data['order_date'] . "',
 '" . $data['product_id'] . "',
 " . $data['qty'] . ",
 " . $data['cost'] . ",
 " . $data['subtotal'] . ",
 " . $data['disc'] . ",
 '01',
 '" . $data['serie'] . "',
 '1', 
 now()
);";
		if ($sqlca->query($sql) < 0) {
			return false;
		}
		return true;
	}

	public function managerPerception($data) {
		global $sqlca;
		$val = array();
		$rex = '';
		//$igv=OrdenCompraModel::igv();
		$sql = "SELECT valor FROM ctrl_com_cab_imporden WHERE id = '".$data['number']."';";
		if ($sqlca->query($sql) < 0) {
			return array('error' => true);
		}

		//$rex=Array();
		for ($i = 0; $i<$sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$rex = $a[0];
		}

		if ($rex == "") {
			$sql = "INSERT INTO ctrl_com_cab_imporden (id, valor) VALUES ('".$data['number']."', ".$data['value'].");";
			if ($sqlca->query($sql) < 0) {
				return array('error' => true);
			}
			$val[0] = (($data['perception'] / 100) + 1) * $data['value'];//-$valor
			$val[1] = (($data['perception'] / 100) + 1) * $data['value'] - $data['value'];
			$val = array('error' => false, 'val' => $val);

		} else {
			//$val=$val+$valor;
			$sql = "SELECT valor FROM ctrl_com_cab_imporden WHERE id = '".$data['number']."';";
			if ($sqlca->query($sql) < 0) {
				return array('error' => true);
			}

			//$rex2=Array();
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
				$rex2 = $a[0];
			}
			
			$valor2 = $rex2 + $data['value'];
			$val[0] = (($data['perception'] / 100) + 1) * $valor2; //-$valor2
			$val[1] = (($data['perception'] / 100) + 1) * $valor2 - $valor2;
			$val = array('error' => false, 'val' => $val);
			
			$sql = "UPDATE ctrl_com_cab_imporden SET valor = $valor2 WHERE id = '".$data['number']."';";
			if ($sqlca->query($sql) < 0) {
				return array('error' => true);
			}
			//$val=1;
		}
		return $val;
	}

	public function saveOrderId($data) {
		global $sqlca;
		$data['number'] = (int)$data['number'];
		$sql = "UPDATE
 int_num_documentos
SET
 num_numactual = TO_CHAR(" . $data['number'] . ", '99999999')
WHERE
 num_tipdocumento = '01'
 AND num_seriedocumento = '" . $data['serie'] . "';";
		if ($sqlca->query($sql) < 0) {
			return false;
		}
		return true;
	}

	public function managerTransaction($type) {
		global $sqlca;
		$sql = $type.";";
		$sqlca->query($sql);
	}
}