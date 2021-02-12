<?php
require '../valida_sess.php';
require '../config.php';
require("/sistemaweb/helper.php");

require("reportes/c_ventas_x_trabajador.php");

if (!isset($_REQUEST['action'])) {
	$objHelper = new HelperClass();
	$arrDataHelper = array(
		'arrAlmacenes' => $objHelper->getWareHouse(),
		'dInicial' => $objHelper->getAllDateFormat('fecha_inicial_dmy'),
		'dFinal' => $objHelper->getAllDateFormat('fecha_dmy'),
		'iIdCliente' => '',
		'sNombreCliente' => '',
	);
	$controllerSalesXEmployee = new controllerSalesXEmployee();
	$controllerSalesXEmployee->index($arrDataHelper);
} else {
	if (isset($_REQUEST['action'])) {
		$controllerSalesXEmployee = new controllerSalesXEmployee();

		$objHelper = new HelperClass();
		$arrDataHelper = array(
			'dFinal' => $objHelper->getAllDateFormat('fecha_dmy'),
		);

		switch ($_REQUEST['action']) {
			case 'search-sales-employee':
				//listar registros de ventas por trabajador
				$controllerSalesXEmployee->searchSalesInvoice($_REQUEST);
				break;
		}
	}
}