<?php
require 't_orden_compra.php';
require 'm_orden_compra.php';
class controllerOrderPurchase {
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

	public function index() {
		$templateOrderPurchase = new templateOrderPurchase();
		$modelOrderPurchase = new modelOrderPurchase();
		$data['warehouse'] = $modelOrderPurchase->getWarehouse();
		$localtime = $this->getLocaltime();
		$data['consult_initial_date'] = $localtime;
		/*$data['exports'] = $modelOrderPurchase->getDayExporter(0, array());
		$data['tableConfiguration'] = $modelOrderPurchase->getTableConfiguration();*/
		$templateOrderPurchase->index($data);
	}

	public function searchOrders($req) {
		$templateOrderPurchase = new templateOrderPurchase();
		$modelOrderPurchase = new modelOrderPurchase();
		$arr_initial_date = explode('/', $req['initial_date']);
		$req['initial_date'] = $arr_initial_date[2].'-'.$arr_initial_date[1].'-'.$arr_initial_date[0];
		$arr_end_date = explode('/', $req['end_date']);
		$req['end_date'] = $arr_end_date[2].'-'.$arr_end_date[1].'-'.$arr_end_date[0];

		$result = $modelOrderPurchase->getOrders($req);
		$templateOrderPurchase->renderSearchOrders($result);
	}

	public function pageAddOrder($req) {
		$templateOrderPurchase = new templateOrderPurchase();
		$modelOrderPurchase = new modelOrderPurchase();
		$data = array();
		$data['warehouse'] = $_SESSION['almacen'];
		$data['warehouses'] = $modelOrderPurchase->getWarehouse();
		$data['currency'] = $modelOrderPurchase->getCurrency();
		$data['tendertype'] = $modelOrderPurchase->getGeneralTable('05');
		$data['reasonForTransfer'] = $modelOrderPurchase->getReasonForTransfer();
		$data['nextOrderId'] = $modelOrderPurchase->getNextOrderIdByWarehouse($data['warehouse']);
		$localtime = $this->getLocaltime();
		$data['date'] = $localtime;
		$templateOrderPurchase->renderPageAddOrder($data);
	}

	public function documentMerchandiseOrder($req) {
		$modelOrderPurchase = new modelOrderPurchase();
		$data['merchandiseOrder'] = $modelOrderPurchase->getMerchandiseOrder($req);
		echo json_encode($data['merchandiseOrder']);
	}

	public function saveOrderPurchase($arrData){
		$modelOrderPurchase = new modelOrderPurchase();
		$saveOrderPurchase = $modelOrderPurchase->saveOrderPurchase($arrData);
		echo json_encode($saveOrderPurchase);
	}

	public function reportResumePDF($req) {
		$modelOrderPurchase = new modelOrderPurchase();
		$result = $modelOrderPurchase->getOrders($req);
		$templateOrderPurchase = new templateOrderPurchase();
		$templateOrderPurchase->reportResumePDF($result);
	}
}
