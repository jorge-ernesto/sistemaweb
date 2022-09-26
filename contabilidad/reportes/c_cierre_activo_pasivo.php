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
include('t_cierre_activo_pasivo.php');
include('m_cierre_activo_pasivo.php');

$objCierreActivoPasivoModel    = new CierreActivoPasivoModel();
$objCierreActivoPasivoTemplate = new CierreActivoPasivoTemplate();

$accion = $_POST['accion'];

try {
	if ($accion == "previewEntry") {
		$response = $objCierreActivoPasivoModel->getAsientoCierreActivoPasivo($_POST['data'], $objHelper);
		echo "<script>console.log('response')</script>";
		echo "<script>console.log('" . json_encode($response) . "')</script>";
		$objCierreActivoPasivoTemplate->gridView(json_encode($response)); //AQUI RENDERIZA LA INFORMACION DE LISTADO O SI HUBO UN ERROR LO MUESTRA
	} else if($accion == "generateEntry") {
		$response = $objCierreActivoPasivoModel->getAsientoCierreActivoPasivo($_POST["data"], $objHelper);
		$objCierreActivoPasivoModel->saveAsientoCierreActivoPasivo($response, $objHelper);
	} else if($accion == "deleteEntry") {
		$objCierreActivoPasivoModel->deleteAsientoCierreActivoPasivo($_POST["data"], $objHelper);
	} else if($accion == "regenerateBalance") {
		$objCierreActivoPasivoModel->regenerateBalance($_POST["data"], $objHelper);
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

