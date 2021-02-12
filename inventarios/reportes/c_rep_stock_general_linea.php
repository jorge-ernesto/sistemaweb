<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_rep_stock_general_linea.php');
include('m_rep_stock_general_linea.php');

/* Get Class Template y Model */

$objtem 	= new TemplateStockGeneralLinea();
$objmodel 	= new ModelStockGeneralLinea();

/* Get Variables de Request */

$accion			= $_REQUEST['accion'];
$_SESSION['data_excel']	= null;

try {

	if ($accion == "SearchLinea") {

		$data = ModelStockGeneralLinea::SearchLinea($_REQUEST);

		TemplateStockGeneralLinea::ListaStockLinea($data, $_REQUEST['notipo']);

	} else if ($accion == "SearchLineaExcel") {

		$data		= ModelStockGeneralLinea::SearchLinea($_REQUEST);
		$nualmacen	= ModelStockGeneralLinea::GetAlmacen($_REQUEST['nualmacen']);

		TemplateStockGeneralLinea::ListaStockLinea($data, $_REQUEST['notipo']);

		if(!empty($data)){
			$_SESSION['data']	= $data;
			$_SESSION['noalmacen']	= $nualmacen[0]['noalmacen'];
			$_SESSION['nuyear']	= $_REQUEST['nuyear'];
			$_SESSION['numonth']	= $_REQUEST['numonth'];
//			$_SESSION['fbuscar']	= $_REQUEST['fbuscar'];
			$_SESSION['notipo']	= $_REQUEST['notipo'];
		}

	}

} catch (Exception $r) {
	echo $r->getMessage();
}

