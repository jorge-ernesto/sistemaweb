<?php
require 'reportes/t_rep_ranking_ventas.php';
require 'reportes/m_rep_ranking_ventas.php';

class controllerRankingVentas {
	function __construct() {
	}

	public function index($arrDataHelper, $iYearNow, $iMonthNow) {
		$templateRankingVentas = new templateRankingVentas();
		$modelRankingVentas = new modelRankingVentas();
		$arrResponseModel = $modelRankingVentas->getAllData($arrDataHelper);
		$templateRankingVentas->index($arrDataHelper, $iYearNow, $iMonthNow, $arrResponseModel);
	}

	public function getRankingVentas($arrPost) {
		$modelRankingVentas = new modelRankingVentas();
		$templateRankingVentas = new templateRankingVentas();
		$arrResponseModel = $modelRankingVentas->getAllData($arrPost);
		$templateRankingVentas->tableGridViewHTML($arrResponseModel, $arrPost);
	}

	public function getRankingVentasDetalle($arrPost) {
		$modelRankingVentas = new modelRankingVentas();
		$templateRankingVentas = new templateRankingVentas();
		$arrResponseModel = $modelRankingVentas->getAllDataDetail($arrPost);
		$templateRankingVentas->tableGridViewHTMLDetail($arrResponseModel, $arrPost);
	}
}