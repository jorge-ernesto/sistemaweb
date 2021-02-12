<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_mov_por_dia.php');
include('m_mov_por_dia.php');

/* Get Class Template y Model */

$template 	= new TemplateMovPorDias();
$model 	= new ModelMovPorDias();

/* Get Variables de Request */

$accion = $_REQUEST['accion'];
$_SESSION['data_excel']	= null;

try {

	if ($accion == "search") {

		$data = $model->search($_REQUEST);

		$template->ListaStockLinea($data, $_REQUEST, $fecha);

	} else if ($accion == "exportExcel") {

		$data		= $model->search($_REQUEST);
		$nualmacen	= $model->GetAlmacen($_REQUEST['nualmacen']);

		$template->ListaStockLinea($data, $_REQUEST, $fecha);

		if(!empty($data)){
			$_SESSION['data_1010']	= $data;
			$_SESSION['noalmacen']	= $nualmacen[0]['noalmacen'];
			$_SESSION['fecha_inicio']	= $_REQUEST['fecha_inicio'];
			$_SESSION['fecha_final']	= $_REQUEST['fecha_final'];
		}

		//var_dump($_SESSION['data_1010']);

	} 

} catch (Exception $r) {
	echo $r->getMessage();
}

