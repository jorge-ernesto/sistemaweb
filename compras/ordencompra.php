<?php
require '../valida_sess.php';
require '../config.php';

require 'movimientos/c_orden_compra.php';
if (!isset($_REQUEST['action'])) {
	$controllerOrderPurchase = new controllerOrderPurchase();
	$controllerOrderPurchase->index();
} else {
	if (isset($_REQUEST['action'])) {
		$controllerOrderPurchase = new controllerOrderPurchase();
		switch ($_REQUEST['action']) {
			case 'search-orders':
				//busqueda de orden de compra
				$controllerOrderPurchase->searchOrders($_REQUEST);
				break;
			case 'add':
				//mostrar informacion para registro de orden de compra
				$controllerOrderPurchase->pageAddOrder($_REQUEST);
				break;
			case 'search-merchandise-order':
				//obtener detalle de pedido de mercaderia
				$controllerOrderPurchase->documentMerchandiseOrder($_REQUEST);
				break;
			case 'save-order':
				//guardar la orden de compra
				$controllerOrderPurchase->saveOrderPurchase($_POST);
				break;
			case 'report-resume-pdf':
				//Exportar resumen de orden (PDF)
				$controllerOrderPurchase->reportResumePDF($_REQUEST);
				break;
			default:
				$controllerOrderPurchase->error404($_REQUEST);
				break;
		}
	}
}