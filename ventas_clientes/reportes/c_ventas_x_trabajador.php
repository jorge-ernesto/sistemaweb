<?php
require 'reportes/t_ventas_x_trabajador.php';
require 'reportes/m_ventas_x_trabajador.php';

class controllerSalesXEmployee {
	function __construct() {
	}

	public function index($arrDataHelper) {
		$templateSalesXEmployee = new templateSalesXEmployee();
		$modelSalesXEmployee = new modelSalesXEmployee();
		$arrResponseModel = $modelSalesXEmployee->getAllData($arrDataHelper);
		$templateSalesXEmployee->index($arrDataHelper, $arrResponseModel);
	}

	public function searchSalesInvoice($arrPost) {
		$modelSalesXEmployee = new modelSalesXEmployee();
		$templateSalesXEmployee = new templateSalesXEmployee();
		$arrResponseModel = $modelSalesXEmployee->getAllData($arrPost);
		$templateSalesXEmployee->tableSalesXEmployee($arrResponseModel);
	}
}