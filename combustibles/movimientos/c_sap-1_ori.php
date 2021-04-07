<?php
require 'v_sap-1.php';
require 'm_sap-1.php';

/* Guardar/editar Centro Costo */
if($_GET['action'] == "guardar-centro-costo"){	
	$id_tabla          = $_POST['id-tabla-centro-costo'];	
	$id                = $_POST['id-centro-costo'];		
	$nombre            = $_POST['nombre-centro-costo'];
	$consult_warehouse = $_POST['consult-warehouse-centro-costo'];
	$codigo_sap        = $_POST['codigo-sap-centro-costo'];		

	$m_sap_1 = new m_sap_1();
	if(empty($id)){
		$es_correcto = $m_sap_1->guardarCentroCosto($id_tabla, $nombre, $consult_warehouse, $codigo_sap);
		echo $es_correcto ? 'Centro costo creado con éxito' : 'No se pudo crear centro costo';		
	}else{
		$es_correcto = $m_sap_1->editarCentroCosto($id_tabla, $id, $nombre, $consult_warehouse, $codigo_sap);
		echo $es_correcto ? 'Centro costo editado con éxito' : 'No se pudo editar centro costo';
	}
}
/* Fin Guardar/editar Centro Costo */

/* Guardar/editar Almacen */
if($_GET['action'] == "guardar-almacen"){	
	$id_tabla          = $_POST['id-tabla-almacen'];	
	$id                = $_POST['id-almacen'];		
	$nombre            = $_POST['nombre-almacen'];
	$consult_warehouse = $_POST['consult-warehouse-almacen'];
	$codigo_sap        = $_POST['codigo-sap-almacen'];		

	$m_sap_1 = new m_sap_1();
	if(empty($id)){
		$es_correcto = $m_sap_1->guardarAlmacen($id_tabla, $nombre, $consult_warehouse, $codigo_sap);
		echo $es_correcto ? 'Almacen creado con éxito' : 'No se pudo crear almacen';		
	}else{
		$es_correcto = $m_sap_1->editarAlmacen($id_tabla, $id, $nombre, $consult_warehouse, $codigo_sap);
		echo $es_correcto ? 'Almacen editado con éxito' : 'No se pudo editar almacen';
	}
}
/* Fin Guardar/editar Almacen */

/* Guardar/editar Tarjeta Credito */
if($_GET['action'] == "guardar-tarjeta-credito"){	
	$id_tabla                = $_POST['id-tabla-tarjeta-credito'];	
	$id                      = $_POST['id-tarjeta-credito'];		
	$nombre                  = $_POST['nombre-tarjeta-credito'];
	$consult_tarjeta_credito = $_POST['consult-tarjeta-credito'];
	$codigo_sap              = $_POST['codigo-sap-tarjeta-credito'];		

	$m_sap_1 = new m_sap_1();
	if(empty($id)){
		$es_correcto = $m_sap_1->guardarTarjetaCredito($id_tabla, $nombre, $consult_tarjeta_credito, $codigo_sap);
		echo $es_correcto ? 'Tarjeta Credito creada con éxito' : 'No se pudo crear tarjeta credito';		
	}else{
		$es_correcto = $m_sap_1->editarTarjetaCredito($id_tabla, $id, $nombre, $consult_tarjeta_credito, $codigo_sap);
		echo $es_correcto ? 'Tarjeta Credito editada con éxito' : 'No se pudo editar tarjeta credito';
	}
}
/* Fin Guardar/editar Tarjeta Credito */

/* Guardar/editar Fondo Efectivo */
if($_GET['action'] == "guardar-fondo-efectivo"){	
	$id_tabla               = $_POST['id-tabla-fondo-efectivo'];	
	$id                     = $_POST['id-fondo-efectivo'];		
	$nombre                 = $_POST['nombre-fondo-efectivo'];
	$consult_fondo_efectivo = $_POST['consult-fondo-efectivo'];
	$codigo_sap             = $_POST['codigo-sap-fondo-efectivo'];		

	$m_sap_1 = new m_sap_1();
	if(empty($id)){
		$es_correcto = $m_sap_1->guardarFondoEfectivo($id_tabla, $nombre, $consult_fondo_efectivo, $codigo_sap);
		echo $es_correcto ? 'Fondo Efectivo creado con éxito' : 'No se pudo crear fondo efectivo';		
	}else{
		$es_correcto = $m_sap_1->editarFondoEfectivo($id_tabla, $id, $nombre, $consult_fondo_efectivo, $codigo_sap);
		echo $es_correcto ? 'Fondo Efectivo editado con éxito' : 'No se pudo editar fondo efectivo';
	}
}
/* Fin Guardar/editar Fondo Efectivo */

/*** Buscar ***/
if($_GET['action'] == "buscar"){
	$id_tipo_tabla         = $_POST['id_tipo_tabla'];
	$id_tipo_tabla_detalle = $_POST['id_tipo_tabla_detalle'];

	$m_sap_1 = new m_sap_1();
	$data = $m_sap_1->buscar($id_tipo_tabla, $id_tipo_tabla_detalle);
	echo json_encode($data);
}
/* Fin Buscar */

/*** Eliminar ***/
if($_GET['action'] == "eliminar"){	
	$value = $_POST['value'];
	$value_explode = explode(",", $value);
	$id_tipo_tabla         = $value_explode[0];
	$id_tipo_tabla_detalle = $value_explode[1];

	$m_sap_1 = new m_sap_1();
	$es_correcto = $m_sap_1->eliminar($id_tipo_tabla, $id_tipo_tabla_detalle);
    echo $es_correcto ? 'Registro eliminado con éxito' : 'No se pudo eliminar registro';
}
/***/

class c_sap_1 {
	function __construct() {
	}

	public function getLocaltime() {
		$localtime = localtime();
		$localtime['year'] = '20'.substr($localtime[5], -2);
		$localtime['day'] = $localtime[3];
		if (strlen($localtime['day']) < 2 ) {
			$localtime['day'] = '0'.$localtime['day'];
		}

		$localtime['month'] = $localtime[4] + 1;
		if (strlen($localtime['month']) < 2 ) {
			$localtime['month'] = '0'.$localtime['month'];
		}
		return $localtime;
	}

	public function delete_export_day($arrDataGET) {
		$m_sap_1 = new m_sap_1();
		echo json_encode($m_sap_1->delete_export_day($arrDataGET));
	}

	public function index() {
		$v_sap_1 = new v_sap_1();
		$m_sap_1 = new m_sap_1();
		$data['warehouse'] = $m_sap_1->getWarehouse();
		$_SESSION['warehouse'] = $m_sap_1->getWarehouse();
		$_SESSION['sucursal'] = $m_sap_1->getSucursal();
		$_SESSION['tarjetaCredito'] = $m_sap_1->getTarjetaCredito();
		$localtime = $this->getLocaltime();
		$data['consult_initial_date'] = $localtime;
		$data['exports'] = $m_sap_1->getDayExporter(0, array());
		$data['tableConfiguration'] = $m_sap_1->getTableConfiguration();
		$v_sap_1->index($data);
	}

	public function consult() {
		$v_sap_1 = new v_sap_1();
		$m_sap_1 = new m_sap_1();
		$data['exports'] = $m_sap_1->getDayExporter(1, $_POST);
		$v_sap_1->tableDayExporter($data);
	}

	public function viewDetailConfiguration() {
		$v_sap_1 = new v_sap_1();
		$m_sap_1 = new m_sap_1();
		$req['table_id'] = $_POST['table_id'];	
		$detailTable = $m_sap_1->getDetailTableConfigurationById($req);	
		$v_sap_1->viewDetailTableConfiguration($detailTable);		
	}

	public function preview($req) {
		error_log('Inicia la previsualizacion de informacion SAP, '.$this->getMemoryUsage());
		$m_sap_1 = new m_sap_1();
		$v_sap_1 = new v_sap_1();

		$m_sap_1->setIsDebug(true);
		$m_sap_1->setIsViewTableName(true);
		$user = $m_sap_1->getUserIdByChLogin();
		if ($user['error']) {
			$v_sap_1->statusRequest('Error [', array(
				'error' => true,
				'code' => -1,
				'message' => 'No se encontró sesión',
			));
			exit;
		}

		//validar que se reciba la fecha y no esté vacía
		$arr_initial_date = explode('/', $req['initial_date']);

		// $connectionData = $m_sap_1->getConnectionData();
		// echo "<script>console.log('connectionData: " . json_encode($connectionData) . "')</script>"; //Agregado 2020-01-10			

		// $hanaInstance = $m_sap_1->connectionHana($connectionData);
		// echo "<script>console.log('hanaInstance: " . json_encode($hanaInstance['message']) . "')</script>"; //Agregado 2020-01-10
		// $v_sap_1->statusRequest('Estado de conexión [', $hanaInstance);

		$req['initial_date'] = $arr_initial_date[2].'-'.$arr_initial_date[1].'-'.$arr_initial_date[0];
		$req['pos_trans'] = 'pos_trans'.$arr_initial_date[2].$arr_initial_date[1];

		$req['last_day_close'] = $m_sap_1->getLastDayClose();
		$req['tax'] = $m_sap_1->getTax();

		$req['sap_tax_code'] = $req['tax'] > 0 || $req['tax'] <= 1.18 ? 'IGV' : 'EXO';

		$req['factor_bonus'] = $m_sap_1->getFactorBonus();
		
		echo "<script>console.log('req: " . json_encode($req) . "')</script>"; //Agregado 2020-01-10


		// if($_SESSION['es_requerimiento_sap_energigas'] == true){
		// 	$req['tableName'] = 'INTOCRD';
		// 	$data['bpartner'] = $m_sap_1->getBPartnerRequerimientoEnergigas($hanaInstance, $req);
		// 	//$data['bpartner'] = $m_sap_1->getBPartner($req); cai				
		// 	$data['bpartner']['isViewTableName'] = $m_sap_1->isViewTableName;
		// 	//echo "<script>console.log('INTOCRD: " . json_encode($data['bpartner']) . "')</script>"; //Agregado 2020-01-10
		// 	$v_sap_1->tableBpartner($data['bpartner']);
		// }else{		
		// 	$req['tableName'] = 'INTOCRD';
		// 	$data['bpartner'] = $m_sap_1->getBPartner($hanaInstance, $req);
		// 	//$data['bpartner'] = $m_sap_1->getBPartner($req); cai				
		// 	$data['bpartner']['isViewTableName'] = $m_sap_1->isViewTableName;
		// 	//echo "<script>console.log('INTOCRD: " . json_encode($data['bpartner']) . "')</script>"; //Agregado 2020-01-10
		// 	$v_sap_1->tableBpartner($data['bpartner']);
		// }

		$req['tableName'] = 'INTOHEM';
		$data['employee'] = $m_sap_1->getEmployee($req);
		$data['employee']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableEmployee($data['employee']);

		/**
		 * 1. Venta contado
		 */
		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas fecha emision */
			$req['tableName'] = 'INTOINVFC'; //El numero de clientes RUC que están en la TABLA INTOINVFC no están en la tabla INTOCRD(SOCIOS DE NEGOCIO)
			$data['invoiceHeaderSaleCash'] = $m_sap_1->getInvoiceHeaderSaleCashWithFechaEmision($req);
			$data['invoiceHeaderSaleCash']['isViewTableName'] = $m_sap_1->isViewTableName;			
			$v_sap_1->tableInvoiceHeaderSaleCashWithFechaEmision($data['invoiceHeaderSaleCash']);
			/* Fin */
		}else if($_SESSION['es_requerimiento_sap_centauro'] == true){
			$req['tableName'] = 'INTOINVFC'; //El numero de clientes RUC que están en la TABLA INTOINVFC no están en la tabla INTOCRD(SOCIOS DE NEGOCIO)
			$data['invoiceHeaderSaleCash'] = $m_sap_1->getInvoiceHeaderSaleCashDesagregarDocumentosAnuladosTransferenciasGratuitas($req);
			$data['invoiceHeaderSaleCash']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableInvoiceHeaderSaleCash($data['invoiceHeaderSaleCash']);			
		}else{
			$req['tableName'] = 'INTOINVFC'; //El numero de clientes RUC que están en la TABLA INTOINVFC no están en la tabla INTOCRD(SOCIOS DE NEGOCIO)
			$data['invoiceHeaderSaleCash'] = $m_sap_1->getInvoiceHeaderSaleCash($req);
			$data['invoiceHeaderSaleCash']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableInvoiceHeaderSaleCash($data['invoiceHeaderSaleCash']);			
		}
		// die();

		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas, excluimos notas de credito */
			$req['tableName'] = 'INTINVFC1';
			$data['invoiceDetailSaleCash'] = $m_sap_1->getInvoiceDetailSaleCashExcluimosNC($req);
			$data['invoiceDetailSaleCash']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableInvoiceDetailSaleCash($data['invoiceDetailSaleCash']);
			/* Fin */
		}else{
			$req['tableName'] = 'INTINVFC1';
			$data['invoiceDetailSaleCash'] = $m_sap_1->getInvoiceDetailSaleCash($req);
			$data['invoiceDetailSaleCash']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableInvoiceDetailSaleCash($data['invoiceDetailSaleCash']);
		}
		// die();

		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas, excluimos documentos originales */
			$req['tableName'] = 'INTORCTFC';
			$data['paymentSaleCash'] = $m_sap_1->getPaymentSaleCashExcluimosDocumentosOriginales($req);
			$data['paymentSaleCash']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tablePaymentSaleCash($data['paymentSaleCash']);
			/* Fin */
		}else{
			$req['tableName'] = 'INTORCTFC';
			$data['paymentSaleCash'] = $m_sap_1->getPaymentSaleCash($req);
			$data['paymentSaleCash']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tablePaymentSaleCash($data['paymentSaleCash']);
		}

		/**
		 * 2. Venta efectivo
		 */
		$req['tableName'] = 'INTODLNPE';
		$req['client'] = "AND client.cli_ndespacho_efectivo = '1' AND client.cli_anticipo = 'N'";//efectivo
		$data['shipmentHeaderSaleEffective'] = $m_sap_1->getShipmentHeaderSaleEffective($req);
		$data['shipmentHeaderSaleEffective']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableShipmentHeaderSaleEffective($data['shipmentHeaderSaleEffective']);

		$req['tableName'] = 'INTDLNPE1';
		$data['shipmentDetailSaleEffective'] = $m_sap_1->getShipmentDetailSaleEffective($req);
		$data['shipmentDetailSaleEffective']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableShipmentDetailSaleEffective($data['shipmentDetailSaleEffective']);

		$req['tableName'] = 'INTOINVPE';
		$data['invoiceHeaderSaleEffective'] = $m_sap_1->getInvoiceHeaderSaleEffective($req);
		$data['invoiceHeaderSaleEffective']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableInvoiceHeaderSaleEffective($data['invoiceHeaderSaleEffective']);

		$req['tableName'] = 'INTINVPE1';
		$data['invoiceDetailSaleEffective'] = $m_sap_1->getInvoiceDetailSaleEffective($req);
		$data['invoiceDetailSaleEffective']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableInvoiceDetailSaleEffective($data['invoiceDetailSaleEffective']);

		$req['tableName'] = 'INTORCTPE';
		$data['paymentSaleEffective'] = $m_sap_1->getPaymentSaleEffective($req);
		$data['paymentSaleEffective']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tablePaymentSaleEffective($data['paymentSaleEffective']);

		/**
		 * . Venta credito
		 */		
		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas fecha emision */
			$req['client'] = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'";//credito
			$req['tableName'] = 'INTODLNPC';
			$data['shipmentHeaderSaleCredit'] = $m_sap_1->getShipmentHeaderSaleCreditWithFechaEmision($req);
			$data['shipmentHeaderSaleCredit']['isViewTableName'] = $m_sap_1->isViewTableName;			
			$v_sap_1->tableShipmentHeaderSaleCreditWithFechaEmision($data['shipmentHeaderSaleCredit']);
			/* Fin */
		}else{
			$req['client'] = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'";//credito
			$req['tableName'] = 'INTODLNPC';
			$data['shipmentHeaderSaleCredit'] = $m_sap_1->getShipmentHeaderSaleCredit($req);
			$data['shipmentHeaderSaleCredit']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableShipmentHeaderSaleCredit($data['shipmentHeaderSaleCredit']);		
		}

		$req['tableName'] = 'INTDLNPC1';
		$data['shipmentDetailSaleCredit'] = $m_sap_1->getShipmentDetailSaleCredit($req);
		$data['shipmentDetailSaleCredit']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableShipmentDetailSaleCredit($data['shipmentDetailSaleCredit']);

		$req['tableName'] = 'INTOINVPC';
		$data['invoiceHeaderSaleCredit'] = $m_sap_1->getInvoiceHeaderSaleCredit($req);
		$data['invoiceHeaderSaleCredit']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableInvoiceHeaderSaleCredit($data['invoiceHeaderSaleCredit']);

		/*
			$req['tableName'] = 'INTOINVPC';
			$data['invoiceHeaderSaleCredit'] = $m_sap_1->getInvoiceHeaderSaleCredit($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceHeaderSaleCredit'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res);
		*///CAI

		$req['tableName'] = 'INTINVPC1';
		$data['invoiceDetailSaleCredit'] = $m_sap_1->getInvoiceDetailSaleCredit($req);
		$data['invoiceDetailSaleCredit']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableInvoiceDetailSaleCredit($data['invoiceDetailSaleCredit']);

		$req['tableName'] = 'INTORCTPC';
		$data['paymentSaleCredit'] = $m_sap_1->getPaymentSaleCredit($req);
		$data['paymentSaleCredit']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tablePaymentSaleCredit($data['paymentSaleCredit']);

		/**
		 * . Venta anticipo
		 */
		$req['client'] = "AND client.cli_anticipo = 'S' AND client.cli_ndespacho_efectivo = '0'";//anticipo
		$req['tableName'] = 'INTODPI';//factura inicial por el anticipo
		$data['invoiceHeaderSaleAnticipationInit'] = $m_sap_1->getInvoiceHeaderSaleAnticipationInit($req);
		$data['invoiceHeaderSaleAnticipationInit']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableInvoiceHeaderSaleAnticipationInit($data['invoiceHeaderSaleAnticipationInit']);

		$req['tableName'] = 'INTDPI1';
		$data['invoiceDetailSaleAnticipationInit'] = $m_sap_1->getInvoiceDetailSaleAnticipationInit($req);
		$data['invoiceDetailSaleAnticipationInit']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableInvoiceDetailSaleAnticipationInit($data['invoiceDetailSaleAnticipationInit']);

		$req['tableName'] = 'INTORCTA';
		$data['paymentSaleAnticipation'] = $m_sap_1->getPaymentSaleAnticipation($req);
		$data['paymentSaleAnticipation']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tablePaymentSaleAnticipation($data['paymentSaleAnticipation']);

		$req['tableName'] = 'INTODLNA';
		$data['shipmentHeaderSaleAnticipation'] = $m_sap_1->getShipmentHeaderSaleAnticipation($req);
		$data['shipmentHeaderSaleAnticipation']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableShipmentHeaderSaleAnticipation($data['shipmentHeaderSaleAnticipation']);

		$req['tableName'] = 'INTDLNA1';
		$data['shipmentDetailSaleAnticipation'] = $m_sap_1->getShipmentDetailSaleAnticipation($req);
		$data['shipmentDetailSaleAnticipation']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableShipmentDetailSaleAnticipation($data['shipmentDetailSaleAnticipation']);

		/*$req['tableName'] = 'INTOINVA';//aun por confirmar en centauro
		$data['invoiceHeaderSaleAnticipation'] = $m_sap_1->getInvoiceHeaderSaleAnticipation($req);
		$data['invoiceHeaderSaleAnticipation']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableInvoiceHeaderSaleAnticipation($data['invoiceHeaderSaleAnticipation']);

		$req['tableName'] = 'INTINVA1';
		$data['invoiceDetailSaleAnticipation'] = $m_sap_1->getInvoiceDetailSaleAnticipation($req);
		$data['invoiceDetailSaleAnticipation']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableInvoiceDetailSaleAnticipation($data['invoiceDetailSaleAnticipation']);*/

		/**
		 * . Boletas
		 */
		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas distinguir documentos mayores a 700 */
			$req['tableName'] = 'INTOBOL';
			$data['documentHeadTicket'] = $m_sap_1->getDocumentHeadTicketDistinguirDocumentosMayores700($req);
			$data['documentHeadTicket']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDocumentHeadTicket($data['documentHeadTicket']);
			/* Fin */
		}else if($_SESSION['es_requerimiento_sap_centauro'] == true){
			$req['tableName'] = 'INTOBOL';
			$data['documentHeadTicket'] = $m_sap_1->getDocumentHeadTicketDesagregarDocumentosAnuladosTransferenciasGratuitas($req);
			$data['documentHeadTicket']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDocumentHeadTicket($data['documentHeadTicket']);
		}else{
			$req['tableName'] = 'INTOBOL';
			$data['documentHeadTicket'] = $m_sap_1->getDocumentHeadTicket($req);
			$data['documentHeadTicket']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDocumentHeadTicket($data['documentHeadTicket']);
		}
		// die();

		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas distinguir documentos mayores a 700 y fecha emision */
			$req['tableName'] = 'INTBOL1';
			$data['documentDetailTicket'] = $m_sap_1->getDocumentDetailTicketDistinguirDocumentosMayores700AndWithFechaEmision($req);
			$data['documentDetailTicket']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDocumentDetailTicketWithFechaEmision($data['documentDetailTicket']);
			/* Fin */
		}else{
			$req['tableName'] = 'INTBOL1';
			$data['documentDetailTicket'] = $m_sap_1->getDocumentDetailTicket($req);
			$data['documentDetailTicket']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDocumentDetailTicket($data['documentDetailTicket']);
		}
		// die();

		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas distinguir documentos mayores a 700 y agrupacion por Turno y Efectivo */
			$req['tableName'] = 'INTORCTBOL';
			$data['paymentDocumentTicket'] = $m_sap_1->getPaymentDocumentTicketDistinguirDocumentosMayores700AndGroupByTurnoAndEfectivo($req);
			$data['paymentDocumentTicket']['isViewTableName'] = $m_sap_1->isViewTableName;			
			$v_sap_1->tablePaymentDocumentTicketGroupByTurnoAndEfectivo($data['paymentDocumentTicket']);
			/* Fin */
		}else{
			$req['tableName'] = 'INTORCTBOL';
			$data['paymentDocumentTicket'] = $m_sap_1->getPaymentDocumentTicket($req);
			$data['paymentDocumentTicket']['isViewTableName'] = $m_sap_1->isViewTableName;		
			$v_sap_1->tablePaymentDocumentTicket($data['paymentDocumentTicket']);
		}

		/**
		 * . Contometros
		 */
		$req['tableName'] = 'INTCONTOM';
		$data['getContometer'] = $m_sap_1->getContometer($req);
		$data['getContometer']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableContometer($data['getContometer']);

		/**
		 * . Cambio de precio
		 */
		$req['tableName'] = 'INTCAMPREC';
		$data['changePrice'] = $m_sap_1->getChangePrice($req);
		$data['changePrice']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableChangePrice($data['changePrice']);

		/**
		 * . Bonus
		 */
		$req['tableName'] = 'INTBONUS';
		$data['bonus'] = $m_sap_1->getBonus($req);
		$data['bonus']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableBonus($data['bonus']);

		/**
		 * . Deposito
		 */
		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas fecha sistema */
			$req['tableName'] = 'INTDEPOSITOS';
			$data['deposit'] = $m_sap_1->getDepositWithFechaSistema($req);
			$data['deposit']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDepositWithFechaSistema($data['deposit']);
			/* Fin */
		}else{
			$req['tableName'] = 'INTDEPOSITOS';
			$data['deposit'] = $m_sap_1->getDeposit($req);
			$data['deposit']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDeposit($data['deposit']);
		}

		/**
		 * . Inventario
		 */
/* David Prada - Centauro indico lo siguiente:
TABLA INTAJUSTE , CODIGO 23000 , quedamos en que no se pasarian esos movimientos

		$req['tableName'] = 'INTAJUSTE';
		$data['headInventory'] = $m_sap_1->getHeadInventory($req);
		$data['headInventory']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableHeadInventory($data['headInventory']);

		$req['tableName'] = 'INTAJUSTE1';
		$data['detalleInventory'] = $m_sap_1->getDetailInventory($req);
		$data['detalleInventory']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableDetailInventory($data['detalleInventory']);
*/
		/**
		 * . Transferencias
		 */
		$req['tableName'] = 'INTOWTR';
		$data['headTransfers'] = $m_sap_1->getHeadTransfers($req);
		$data['headTransfers']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableHeadTransfers($data['headTransfers']);

		$req['tableName'] = 'INTWTR1';
		$data['detailTransfers'] = $m_sap_1->getDetailTransfers($req);
		$data['detailTransfers']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableDetailTransfers($data['detailTransfers']);

		/**
		 * . Afericiones
		 */
		$req['tableName'] = 'INTOAFE';
		$data['headTestDispatch'] = $m_sap_1->getHeadTestDispatch($req);
		$data['headTestDispatch']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableHeadTestDispatch($data['headTestDispatch']);

		$req['tableName'] = 'INTAFE1';
		$data['detailTestDispatch'] = $m_sap_1->getDetailTestDispatch($req);
		$data['detailTestDispatch']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableDetailTestDispatch($data['detailTestDispatch']);

		/**
		 * . Notas de credito
		 */
		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas fecha emision */
			$req['tableName'] = 'INTORIN';
			$data['headCreditNote'] = $m_sap_1->getHeadCreditNoteWithFechaEmision($hanaInstance, $req);
			$data['headCreditNote']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableHeadCreditNoteWithFechaEmision($data['headCreditNote']);
			/* Fin */
		}else{
			$req['tableName'] = 'INTORIN';
			$data['headCreditNote'] = $m_sap_1->getHeadCreditNote($hanaInstance, $req);
			$data['headCreditNote']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableHeadCreditNote($data['headCreditNote']);
		}

		$req['tableName'] = 'INTRIN1';
		$data['detailCreditNote'] = $m_sap_1->getDetailCreditNote($hanaInstance, $req);
		$data['detailCreditNote']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableDetailCreditNote($data['detailCreditNote']);

		/**
		 * . Notas de debito
		 */
		$req['tableName'] = 'INTODLN';
		$data['headDebitNote'] = $m_sap_1->getHeadDebitNote($hanaInstance, $req);
		$data['headDebitNote']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableHeadDebitNote($data['headDebitNote']);

		$req['tableName'] = 'INTDLN1';
		$data['detailDebitNote'] = $m_sap_1->getDetailDebitNote($hanaInstance, $req);
		$data['detailDebitNote']['isViewTableName'] = $m_sap_1->isViewTableName;
		$v_sap_1->tableDetailDebitNote($data['detailDebitNote']);

		/**
		 * . Factura de proveedores - compras
		 */
		if($_SESSION['es_requerimiento_sap_centauro'] == true){
			$req['tableName'] = 'INTOPCH';
			$data['headInvoicePurchase'] = $m_sap_1->getHeadInvoicePurchaseWithGuiasRemision($req);
			$data['headInvoicePurchase']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableHeadInvoicePurchase($data['headInvoicePurchase']);
		}else{
			$req['tableName'] = 'INTOPCH';
			$data['headInvoicePurchase'] = $m_sap_1->getHeadInvoicePurchase($req);
			$data['headInvoicePurchase']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableHeadInvoicePurchase($data['headInvoicePurchase']);
		}

		if($_SESSION['es_requerimiento_sap_centauro'] == true){
			$req['tableName'] = 'INTPCH1';
			$data['detailInvoicePurchase'] = $m_sap_1->getDetailInvoicePurchaseWithGuiasRemision($req);
			$data['detailInvoicePurchase']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDetailInvoicePurchase($data['detailInvoicePurchase']);
		}else{
			$req['tableName'] = 'INTPCH1';
			$data['detailInvoicePurchase'] = $m_sap_1->getDetailInvoicePurchase($req);
			$data['detailInvoicePurchase']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDetailInvoicePurchase($data['detailInvoicePurchase']);
		}

		/**
		 * . Varillaje
		 */
		if($_SESSION['es_requerimiento_sap_energigas'] == true){
			/* Requerimiento Energigas agregar tabla varillas */
			$req['tableName'] = 'INTVARILLAS';
			$data['detailVarillas'] = $m_sap_1->getDetailVarillas($req);
			$data['detailVarillas']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDetailVarillas($data['detailVarillas']);
			/* Fin */
		}

		//REQUERIMIENTO CENTAURO OPENSOFT-XX
		/**
		 * . Venta de combustibles por manguera/día
		 */
		if($_SESSION['es_requerimiento_sap_centauro'] == true){
			$req['tableName'] = 'INTCOMBUSTIBLECONT';
			$data['detailCombustiblePorManguera'] = $m_sap_1->getDetailCombustiblePorManguera($req);
			$data['detailCombustiblePorManguera']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDetailCombustiblePorManguera($data['detailCombustiblePorManguera']);			
		}

		/**
		 * . Tabla de stocks (sólo combustibles)
		 */
		if($_SESSION['es_requerimiento_sap_centauro'] == true){			
			$req['tableName'] = 'INTSTOCK';
			$data['detailStock'] = $m_sap_1->getDetailStock($req);
			$data['detailStock']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDetailStock($data['detailStock']);			
		}

		/**
		 * . Tabla de totales por forma de pago con notas de despacho
		 */
		if($_SESSION['es_requerimiento_sap_centauro'] == true){			
			$req['tableName'] = 'INTTOTALES';
			$data['detailTotales'] = $m_sap_1->getDetailTotales($req);
			$data['detailStock']['isViewTableName'] = $m_sap_1->isViewTableName;
			$v_sap_1->tableDetailTotales($data['detailTotales']);			
		}
		//CERRAR REQUERIMIENTO CENTAURO OPENSOFT-XX

		$m_sap_1->ticketHead = array();

		/*$exporter = $m_sap_1->addDayExporter($req, array(), $user);
		var_dump($exporter);*/

		error_log('Por finalizar previsualizacion de informacion SAP, '.$this->getMemoryUsage());

		unset($data, $req, $connectionData, $hanaInstance, $v_sap_1, $m_sap_1);
		error_log('Finaliza la previsualizacion de informacion SAP, '.$this->getMemoryUsage());
	}

	public function export($req) {
		$res = array();
		$m_sap_1 = new m_sap_1();
		$v_sap_1 = new v_sap_1();
		//validar que se reciba la fecha y no esté vacía
		$arr_initial_date = explode('/', $req['initial_date']);

		$req['initial_date'] = $arr_initial_date[2].'-'.$arr_initial_date[1].'-'.$arr_initial_date[0];

		$connectionData = $m_sap_1->getConnectionData();
		$hanaInstance = $m_sap_1->connectionHana($connectionData);

		$existDayExporter = $m_sap_1->getDayExporter(1, array('initial_systemdate' => $req['initial_date']));

		if ($existDayExporter != NULL) {
			echo json_encode(array(
				'error' => true,
				'site' => 'validationDayExporter',
				'code' => 1,
				'message' => 'El día selecionado ya fue exportado.',
			));
			exit;
		}

		if ($hanaInstance['error']) {
			echo json_encode(array(
				'error' => true,
				'site' => 'connection',
				'code' => 2,
				'message' => 'Estado de conexión ['.$hanaInstance['code'].']: '.$hanaInstance['message'].'',
			));
			exit;
		} else {
			//$req['initial_date'] = $arr_initial_date[2].'-'.$arr_initial_date[1].'-'.$arr_initial_date[0];
			$req['pos_trans'] = 'pos_trans'.$arr_initial_date[2].$arr_initial_date[1];

			$user = $m_sap_1->getUserIdByChLogin();
			if ($user['error']) {
				echo json_encode(array(
					'error' => true,
					'site' => 'session',
					'code' => 3,
					'message' => 'No se encontró sesión',
				));
				exit;
			}

			odbc_autocommit($hanaInstance['instance'], false);
			$req['last_day_close'] = $m_sap_1->getLastDayClose();
			$req['tax'] = $m_sap_1->getTax();

			$req['sap_tax_code'] = $req['tax'] > 0 || $req['tax'] <= 1.18 ? 'IGV' : 'EXO';

			$req['factor_bonus'] = $m_sap_1->getFactorBonus();

			/*$document = $m_sap_1->findDocumentReference($hanaInstance, array('head' => 'INTOINVFC', 'detail' => 'INTINVFC1'), array('foliopref' => 'F005', 'folionum' => '554'));
			var_dump($document);
			exit;*/

			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				$req['tableName'] = 'INTOCRD';
				$data['bpartner'] = $m_sap_1->getBPartnerRequerimientoEnergigas($hanaInstance, $req);
				if ( $data['bpartner']['sStatus'] == 'success' ) { 
					$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['bpartner'], array('isUnique' => false));
					$this->checkResponseInsert($req, $res); 
				}
			}else{
				$req['tableName'] = 'INTOCRD';
				$data['bpartner'] = $m_sap_1->getBPartner($hanaInstance, $req);
				if ( $data['bpartner']['sStatus'] == 'success' ) { 
					$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['bpartner'], array('isUnique' => false));
					$this->checkResponseInsert($req, $res); 
				}
			}

			$req['tableName'] = 'INTOHEM';
			$data['employee'] = $m_sap_1->getEmployee($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['employee'], array('isUnique' => false));
			$this->checkResponseInsert($req, $res); 

			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas fecha emision */
				$req['tableName'] = 'INTOINVFC';
				$data['invoiceHeaderSaleCash'] = $m_sap_1->getInvoiceHeaderSaleCashWithFechaEmision($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceHeaderSaleCash'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
				/* Fin */
			}else if($_SESSION['es_requerimiento_sap_centauro'] == true){
				$req['tableName'] = 'INTOINVFC';
				$data['invoiceHeaderSaleCash'] = $m_sap_1->getInvoiceHeaderSaleCashDesagregarDocumentosAnuladosTransferenciasGratuitas($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceHeaderSaleCash'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}else{
				$req['tableName'] = 'INTOINVFC';
				$data['invoiceHeaderSaleCash'] = $m_sap_1->getInvoiceHeaderSaleCash($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceHeaderSaleCash'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}

			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas, excluimos notas de credito */
				$req['tableName'] = 'INTINVFC1';
				$data['invoiceDetailSaleCash'] = $m_sap_1->getInvoiceDetailSaleCashExcluimosNC($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceDetailSaleCash'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);
				/* Fin */
			}else{
				$req['tableName'] = 'INTINVFC1';
				$data['invoiceDetailSaleCash'] = $m_sap_1->getInvoiceDetailSaleCash($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceDetailSaleCash'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);
			}

			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas, excluimos documentos originales */
				$req['tableName'] = 'INTORCTFC';
				$data['paymentSaleCash'] = $m_sap_1->getPaymentSaleCashExcluimosDocumentosOriginales($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['paymentSaleCash'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);
				/* Fin */
			}else{
				$req['tableName'] = 'INTORCTFC';
				$data['paymentSaleCash'] = $m_sap_1->getPaymentSaleCash($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['paymentSaleCash'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);
			}


			$req['client'] = "AND client.cli_ndespacho_efectivo = '1' AND client.cli_anticipo = 'N'";//efectivo
			$req['tableName'] = 'INTODLNPE';
			$data['shipmentHeaderSaleEffective'] = $m_sap_1->getShipmentHeaderSaleEffective($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['shipmentHeaderSaleEffective'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTDLNPE1';
			$data['shipmentDetailSaleEffective'] = $m_sap_1->getShipmentDetailSaleEffective($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['shipmentDetailSaleEffective'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTOINVPE';
			$data['invoiceHeaderSaleEffective'] = $m_sap_1->getInvoiceHeaderSaleEffective($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceHeaderSaleEffective'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTINVPE1';
			$data['invoiceDetailSaleEffective'] = $m_sap_1->getInvoiceDetailSaleEffective($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceDetailSaleEffective'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTORCTPE';//***pendiente la relacion de pago a guia
			$data['paymentSaleEffective'] = $m_sap_1->getPaymentSaleEffective($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['paymentSaleEffective'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 


			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas fecha emision */
				$req['client'] = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'";//credito
				$req['tableName'] = 'INTODLNPC';
				$data['shipmentHeaderSaleCredit'] = $m_sap_1->getShipmentHeaderSaleCreditWithFechaEmision($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['shipmentHeaderSaleCredit'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
				/* Fin */
			}else{
				$req['client'] = "AND client.cli_ndespacho_efectivo = '0' AND client.cli_anticipo = 'N'";//credito
				$req['tableName'] = 'INTODLNPC';
				$data['shipmentHeaderSaleCredit'] = $m_sap_1->getShipmentHeaderSaleCredit($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['shipmentHeaderSaleCredit'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}

			$req['tableName'] = 'INTDLNPC1';
			$data['shipmentDetailSaleCredit'] = $m_sap_1->getShipmentDetailSaleCredit($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['shipmentDetailSaleCredit'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTOINVPC';
			$data['invoiceHeaderSaleCredit'] = $m_sap_1->getInvoiceHeaderSaleCredit($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceHeaderSaleCredit'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTINVPC1';
			$data['invoiceDetailSaleCredit'] = $m_sap_1->getInvoiceDetailSaleCredit($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceDetailSaleCredit'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTORCTPC';
			$data['paymentSaleCredit'] = $m_sap_1->getPaymentSaleCredit($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['paymentSaleCredit'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 


			$req['client'] = "AND client.cli_anticipo = 'S' AND client.cli_ndespacho_efectivo = '0'";//anticipo
			$req['tableName'] = 'INTODPI';
			$data['invoiceHeaderSaleAnticipationInit'] = $m_sap_1->getInvoiceHeaderSaleAnticipationInit($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceHeaderSaleAnticipationInit'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTDPI1';
			$data['invoiceDetailSaleAnticipationInit'] = $m_sap_1->getInvoiceDetailSaleAnticipationInit($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceDetailSaleAnticipationInit'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 


			//$req['tableName'] = 'INTOINVA';
			//$data['invoiceHeaderSaleAnticipation'] = $m_sap_1->getInvoiceHeaderSaleAnticipation($req);
			//$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceHeaderSaleAnticipation'], array('isUnique' => true));
			//$this->checkResponseInsert($req, $res);

			//$req['tableName'] = 'INTINVA1';//aun por confirmar en centauro
			//$data['invoiceDetailSaleAnticipation'] = $m_sap_1->getInvoiceDetailSaleAnticipation($req);
			//$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['invoiceDetailSaleAnticipation'], array('isUnique' => true));
			//$this->checkResponseInsert($req, $res);



			$req['tableName'] = 'INTORCTA';
			$data['paymentSaleAnticipation'] = $m_sap_1->getPaymentSaleAnticipation($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['paymentSaleAnticipation'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 



			$req['tableName'] = 'INTODLNA';//SQL error: [SAP AG][LIBODBCHDB SO][HDBODBC] General error;287 cannot insert NULL or update to NULL: INDICATOR, SQL state S1000 in SQLExecDirect
			$data['shipmentHeaderSaleAnticipation'] = $m_sap_1->getShipmentHeaderSaleAnticipation($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['shipmentHeaderSaleAnticipation'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTDLNA1';
			$data['shipmentDetailSaleAnticipation'] = $m_sap_1->getShipmentDetailSaleAnticipation($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['shipmentDetailSaleAnticipation'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 



			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas distinguir documentos mayores a 700 */
				$req['tableName'] = 'INTOBOL';
				$data['documentHeadTicket'] = $m_sap_1->getDocumentHeadTicketDistinguirDocumentosMayores700($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['documentHeadTicket'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);
				/* Fin */
			}else if($_SESSION['es_requerimiento_sap_centauro'] == true){
				$req['tableName'] = 'INTOBOL';
				$data['documentHeadTicket'] = $m_sap_1->getDocumentHeadTicketDesagregarDocumentosAnuladosTransferenciasGratuitas($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['documentHeadTicket'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);
			}else{
				$req['tableName'] = 'INTOBOL';
				$data['documentHeadTicket'] = $m_sap_1->getDocumentHeadTicket($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['documentHeadTicket'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);
			}

			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas distinguir documentos mayores a 700 y fecha emision */
				$req['tableName'] = 'INTBOL1';
				$data['documentDetailTicket'] = $m_sap_1->getDocumentDetailTicketDistinguirDocumentosMayores700AndWithFechaEmision($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['documentDetailTicket'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
				/* Fin */
			}else{
				$req['tableName'] = 'INTBOL1';
				$data['documentDetailTicket'] = $m_sap_1->getDocumentDetailTicket($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['documentDetailTicket'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}

			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas distinguir documentos mayores a 700 y agrupacion por Turno y Efectivo */
				$req['tableName'] = 'INTORCTBOL';
				$data['paymentDocumentTicket'] = $m_sap_1->getPaymentDocumentTicketDistinguirDocumentosMayores700AndGroupByTurnoAndEfectivo($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['paymentDocumentTicket'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
				/* Fin */
			}else{
				$req['tableName'] = 'INTORCTBOL';
				$data['paymentDocumentTicket'] = $m_sap_1->getPaymentDocumentTicket($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['paymentDocumentTicket'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}

			

			$req['tableName'] = 'INTCONTOM';//OK
			$data['getContometer'] = $m_sap_1->getContometer($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['getContometer'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 


			$req['tableName'] = 'INTCAMPREC';
			$data['changePrice'] = $m_sap_1->getChangePrice($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['changePrice'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTBONUS';
			$data['bonus'] = $m_sap_1->getBonus($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['bonus'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas fecha sistema */
				$req['tableName'] = 'INTDEPOSITOS';//Ok
				$data['deposit'] = $m_sap_1->getDepositWithFechaSistema($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['deposit'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
				/* Fin */
			}else{
				$req['tableName'] = 'INTDEPOSITOS';//Ok
				$data['deposit'] = $m_sap_1->getDeposit($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['deposit'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);
			}

			/*
			$req['tableName'] = 'INTAJUSTE';
			$data['headInventory'] = $m_sap_1->getHeadInventory($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['headInventory'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res);

			$req['tableName'] = 'INTAJUSTE1';
			$data['detalleInventory'] = $m_sap_1->getDetailInventory($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['detalleInventory'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res);
			*/

			$req['tableName'] = 'INTOWTR';
			$data['headTransfers'] = $m_sap_1->getHeadTransfers($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['headTransfers'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTWTR1';
			$data['detailTransfers'] = $m_sap_1->getDetailTransfers($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['detailTransfers'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTOAFE';
			$data['headTestDispatch'] = $m_sap_1->getHeadTestDispatch($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['headTestDispatch'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTAFE1';
			$data['detailTestDispatch'] = $m_sap_1->getDetailTestDispatch($req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['detailTestDispatch'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 


			//Para enviar Notas de credito, es necesiario también envíar INTOINVFC e INTOBOL
			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas fecha emision */
				$req['tableName'] = 'INTORIN';
				$data['headCreditNote'] = $m_sap_1->getHeadCreditNoteWithFechaEmision($hanaInstance, $req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['headCreditNote'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
				/* Fin */
			}else{
				$req['tableName'] = 'INTORIN';
				$data['headCreditNote'] = $m_sap_1->getHeadCreditNote($hanaInstance, $req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['headCreditNote'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}

			$req['tableName'] = 'INTRIN1';
			$data['detailCreditNote'] = $m_sap_1->getDetailCreditNote($hanaInstance, $req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['detailCreditNote'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTODLN';
			$data['headDebitNote'] = $m_sap_1->getHeadDebitNote($hanaInstance, $req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['headDebitNote'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			$req['tableName'] = 'INTDLN1';
			$data['detailDebitNote'] = $m_sap_1->getDetailDebitNote($hanaInstance, $req);
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['detailDebitNote'], array('isUnique' => true));
			$this->checkResponseInsert($req, $res); 

			if($_SESSION['es_requerimiento_sap_centauro'] == true){
				$req['tableName'] = 'INTOPCH';
				$data['headInvoicePurchase'] = $m_sap_1->getHeadInvoicePurchaseWithGuiasRemision($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['headInvoicePurchase'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}else{
				$req['tableName'] = 'INTOPCH';
				$data['headInvoicePurchase'] = $m_sap_1->getHeadInvoicePurchase($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['headInvoicePurchase'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}

			if($_SESSION['es_requerimiento_sap_centauro'] == true){
				$req['tableName'] = 'INTPCH1';
				$data['detailInvoicePurchase'] = $m_sap_1->getDetailInvoicePurchaseWithGuiasRemision($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['detailInvoicePurchase'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}else{
				$req['tableName'] = 'INTPCH1';
				$data['detailInvoicePurchase'] = $m_sap_1->getDetailInvoicePurchase($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['detailInvoicePurchase'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res); 
			}

			if($_SESSION['es_requerimiento_sap_energigas'] == true){
				/* Requerimiento Energigas agregar tabla varillas */
				$req['tableName'] = 'INTVARILLAS';
				$data['detailVarillas'] = $m_sap_1->getDetailVarillas($req);
				$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['detailVarillas'], array('isUnique' => true));
				$this->checkResponseInsert($req, $res);				
				/* Fin */
			}

			$exporter = $m_sap_1->addDayExporter($req, array(), $user);
			if (!$exporter['error']) {
				//insert del día migrado
				odbc_commit($hanaInstance['instance']);
				error_log('Exportado correctamente: '.$req['initial_date']);
				echo json_encode(array(
					'error' => false,
					'message' => 'Exportado correctamente: '.$req['initial_date'],
					'tables' => $res,
				));
			} else {
				odbc_rollback($hanaInstance['instance']);
				error_log('Error al Exportar: '.$req['initial_date']);
				echo json_encode(array(
					'error' => true,
					'site' => 'insertExport',
					'code' => 2,
					'message' => 'Error al insertar registro de exportación de información.',
				));
			}
			odbc_close($hanaInstance['instance']);
			unset($data, $req, $res, $exporter, $connectionData, $hanaInstance, $v_sap_1, $m_sap_1);
			exit;
		}
	}

	public function checkResponseInsert($req, $res) {
		if ($res['tables'][$req['tableName']]['error']) {
			echo json_encode(array(
				'error' => true,
				'site' => $req['tableName'],
				'code' => 4,
				'message' => 'Error',
				'tables' => $res['tables'][$req['tableName']],
			));
			exit;
		}
	}

	public function getMemoryUsage() {
		return (memory_get_usage(true) / 1024 / 1024);
	}

	/*Test para UTF-8*/
	public function testInsertUtf8() {
		$m_sap_1 = new m_sap_1();

		$connectionData = $m_sap_1->getConnectionData();
		$hanaInstance = $m_sap_1->connectionHana($connectionData);

		//if (!$hanaInstance['error']) {
			$_res[] = array(
				'cardcode' => 'C1007474416A',
				'cardname' => utf8_decode('DUEÑAS SERNA ALEJANDRO ENRÍQUE'),
				'federaltaxid' => '10074744163',
				'phone' => '',
				'email' => '',
				'street' => '',
				'name' => '',
				'lastname' => '',
				'u_exx_tipopers' => 'TPN',
				'u_exx_tipodocu' => 6,
				'u_exx_apellpat' => 'DUEÑAS',
				'u_exx_apellmat' => 'SERNA',
				'u_exx_primerno' => 'ALEJANDRO',
				'u_exx_segundno' => 'ENRIQUE',
				'estado' => 'P',
				'errormsg' => '',
			);
			$bpartner = array(
				'error' => false,
				'tableName' => 'INTOCRD',
				'nodeData' => 'bpartner',
				'bpartner' => $_res,
				'count' => 1,
			);

			$data['bpartner'] = $bpartner;
			echo '<pre>';
			var_dump($data);
			echo '</pre>';
			$req['tableName'] = 'INTOCRD';
			$res['tables'][$req['tableName']] = $m_sap_1->setTableHana($hanaInstance, $data['bpartner'], array('isUnique' => false));
			echo '<hr><pre>';
			var_dump($res);
			echo '</pre>';
		/*} else {
			echo 'Error de conxión a HANA';
		}*/
	}
} 