<?php
require '../valida_sess.php';
require '../config.php';
require("/sistemaweb/helper.php");

require("reportes/c_rep_ranking_ventas.php");

if (!isset($_REQUEST['action'])) {
	$objHelper = new HelperClass();
	$arrYearStart = $objHelper->getSystemYearStartDB();
	$arrDataHelper = array(
		'arrAlmacenes' => $objHelper->getWareHouse(),
		'arrYearStart' => $objHelper->getSystemYearStart($arrYearStart['dFechaEmision']),
		'arrMonth' => $objHelper->Months(),
		'sGeneradoPor' => 'T',
		'sYear' => date('Y'),
		'sMonth' => date('m'),
	);
	$controllerRankingVentas = new controllerRankingVentas();
	$controllerRankingVentas->index($arrDataHelper, date('Y'), date('m'));
} else {
	if (isset($_REQUEST['action'])) {
		$controllerRankingVentas = new controllerRankingVentas();
		switch ($_REQUEST['action']) {
			case 'search-ranking-ventas':
				//listar registros de ventas por trabajador
				$controllerRankingVentas->getRankingVentas($_REQUEST);
				break;
			case 'search-ranking-ventas-detalle':
				//listar registros de ventas por trabajador
				$controllerRankingVentas->getRankingVentasDetalle($_REQUEST);
				break;
		}
	}
}