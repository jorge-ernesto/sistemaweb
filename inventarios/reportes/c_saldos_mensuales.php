<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_saldos_mensuales.php');
include('m_saldos_mensuales.php');

/* Get Class Template y Model */

$template 	= new TemplateSaldosMensuales();
$model 	= new ModelSaldosMensuales();

/* Get Variables de Request */

$accion			= $_REQUEST['accion'];
$_SESSION['data_excel']	= null;

try {

	if ($accion == "search") {

		$data = $model->search($_REQUEST);
		$template->SaldosMensuales($data, $_REQUEST);

	} else if ($accion == "exportExcel") {

		$data		= $model->search($_REQUEST);
		$nualmacen	= $model->GetAlmacen($_REQUEST['nualmacen']);

		$template->SaldosMensuales($data, $_REQUEST);

		if(!empty($data)){
			$_SESSION['data_1010']	= $data;
			$_SESSION['noalmacen']	= $nualmacen[0]['noalmacen'];
			$_SESSION['nuyear']	= $_REQUEST['nuyear'];
			$_SESSION['numonth']	= $_REQUEST['numonth'];
			$_SESSION['cod_art']	= $_REQUEST['cod_art'];
			$_SESSION['desc_art'] = $_REQUEST['desc_art'];
		}

		//var_dump($_SESSION['data_1010']);

	} 

} catch (Exception $r) {
	echo $r->getMessage();
}

