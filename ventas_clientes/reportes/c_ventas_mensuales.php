<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_ventas_mensuales.php');
include('m_ventas_mensuales.php');

/* Get Class Template y Model */

$template 	= new TemplateVentasMensuales();
$model 	= new ModelVentasMensuales();

/* Get Variables de Request */

$accion			= $_REQUEST['accion'];
$_SESSION['data_excel']	= null;

try {

	if ($accion == "search") {

		$data = $model->search($_REQUEST);
		$template->VentasMensuales($data, $_REQUEST);

	} else if ($accion == "exportExcel") {

		$data		= $model->search($_REQUEST);
		$nualmacen	= $model->GetAlmacen($_REQUEST['cod_almacen']);

		$template->VentasMensuales($data, $_REQUEST);

		if(!empty($data)){
			$_SESSION['data_1010']	= $data;
			$_SESSION['cod_almacen']	= $nualmacen[0]['cod_almacen'];
			$_SESSION['cod_linea']	= $_REQUEST['cod_linea'];
			$_SESSION['periodo']	= $_REQUEST['periodo'];
			$_SESSION['modo']	= $_REQUEST['modo'];
		}

		//var_dump($_SESSION['data_1010']);

	} 

} catch (Exception $r) {
	echo $r->getMessage();
}

