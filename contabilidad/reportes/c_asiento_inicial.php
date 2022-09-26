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
include('t_asiento_inicial.php');
include('m_asiento_inicial.php');

$objAsientoInicialModel    = new AsientoInicialModel();
$objAsientoInicialTemplate = new AsientoInicialTemplate();

$accion = $_POST['accion'];

try {
	if ($accion == "previewEntry") {
		$response = $objAsientoInicialModel->getAsientoInicial($_POST['data'], $objHelper);
		echo "<script>console.log('response')</script>";
		echo "<script>console.log('" . json_encode($response) . "')</script>";
		$objAsientoInicialTemplate->gridView(json_encode($response)); //AQUI RENDERIZA LA INFORMACION DE LISTADO O SI HUBO UN ERROR LO MUESTRA
	} else if($accion == "generateEntry") {
		$response = $objAsientoInicialModel->getAsientoInicial($_POST["data"], $objHelper);
		$objAsientoInicialModel->saveAsientoInicial($response, $objHelper);
	} else if($accion == "deleteEntry") {
		$objAsientoInicialModel->deleteAsientoInicial($_POST["data"], $objHelper);
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

