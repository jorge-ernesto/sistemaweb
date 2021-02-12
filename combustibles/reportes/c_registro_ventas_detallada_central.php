<?php

date_default_timezone_set('UTC');

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

include('/sistemaweb/include/mvc_sistemaweb.php');
include('t_registro_ventas_detallada_central.php');
include('m_registro_ventas_detallada_central.php');

/* Get Class Template y Model */

$objtem 	= new TemplateRegistroVentasCental();
$objmodel 	= new ModelRegistroVentasCental();

/* Get Variables de Request */

$accion					= $_REQUEST['accion'];
$_SESSION['data_excel']	= null;

try {

	if ($accion == "Search") {

		$data = ModelRegistroVentasCental::ReporteRegisroVentasCentral($_POST);

		TemplateRegistroVentasCental::RegistroVentasCentral($data);

	} else if ($accion == "SearchExcel") {

		$data		= ModelRegistroVentasCental::ReporteRegisroVentasCentral($_REQUEST);
		$razsocial	= ModelRegistroVentasCental::GetEmpresa($_REQUEST['cmbnualmacen']);

		TemplateRegistroVentasCental::RegistroVentasCentral($data, $_REQUEST['rdnotipo']);

		if(!empty($data)){
			$_SESSION['data']			= $data;
			$_SESSION['razsocial']		= $razsocial;
			$_SESSION['txtnofechaini']	= $_REQUEST['txtnofechaini'];
			$_SESSION['txtnofechafin']	= $_REQUEST['txtnofechafin'];
			$_SESSION['rdnotipo']		= $_REQUEST['rdnotipo'];
		}

	} else if($accion == "SearchVC") {
		//var_dump($_REQUEST);
		$data = ModelRegistroVentasCental::ReporteRegisroVentasCentralizado($_REQUEST);
		TemplateRegistroVentasCental::renderTablaVentasCentralizadas($data);
	}

} catch (Exception $r) {
	echo $r->getMessage();
}

