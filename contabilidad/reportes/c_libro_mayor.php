<?php
$version = explode('.', PHP_VERSION);
$php_version = $version[0] . '.' . $version[1];
if ( $php_version == '5.6' ) {
	session_start();
}

date_default_timezone_set('America/Lima');

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

include('/sistemaweb/assets/jgridpaginador.php');
$objjqGridModel = new jqGridModel();

include("/sistemaweb/contabilidad/helper/helper.php");	
$objHelper = new HelperClass();	

include('/sistemaweb/include/mvc_sistemaweb.php');
include('t_libro_mayor.php');
include('m_libro_mayor.php');

$objLibroMayorModel 	= new LibroMayorModel();
$objLibroMayorTemplate = new LibroMayorTemplate();

$accion = $_POST['accion'];

try {
	if($accion == "listPDF") {
		$response = $objLibroMayorModel->getList($_POST["data"], $objHelper);
		echo "<script>console.log('response')</script>";
		echo "<script>console.log('" . json_encode($response) . "')</script>";
		$objLibroMayorTemplate->gridViewPDF(json_encode($response));
	} else if($accion == "listExcel") {
		$response = $objLibroMayorModel->getList($_POST["data"], $objHelper);
		$objLibroMayorTemplate->gridViewExcel(json_encode($response));
	} else if ($accion == "listPLE") {
		$response = $objLibroMayorModel->getList($_POST["data"], $objHelper);
		$objLibroMayorTemplate->gridViewPLE(json_encode($response));
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

