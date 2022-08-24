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

include('/sistemaweb/include/mvc_sistemaweb.php');
include('t_libro_diario.php');
include('m_libro_diario.php');

$objLibroDiarioModel 	= new LibroDiarioModel();
$objLibroDiarioTemplate = new LibroDiarioTemplate();

$accion = $_POST['accion'];

try {
	if ($accion == "listAll") {
		$response = $objLibroDiarioModel->getListAll($_POST['data'], $objjqGridModel);
		echo "<script>console.log('response')</script>";
		echo "<script>console.log('" . json_encode($response) . "')</script>";
		$objLibroDiarioTemplate->gridView(json_encode($response));
	} else if($accion == "listAllPDF") {
		$response = $objLibroDiarioModel->getListAllExcel($_POST["data"]);
		$objLibroDiarioTemplate->gridViewPDF(json_encode($response));
	} else if ($accion == "listAllExcel") {
		$response = $objLibroDiarioModel->getListAllExcel($_POST["data"]);
		$objLibroDiarioTemplate->gridViewExcel(json_encode($response));
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

