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
include('t_vale_crud.php');
include('m_vale_crud.php');

$objValeModel 		= new ValeCRUDModel();
$objValeTemplate 	= new ValeCRUDTemplate();

$accion = $_POST['accion'];

/* Get IP and USER */

$ip = "";

if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
	$ip = getenv("HTTP_CLIENT_IP");
else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
	$ip = getenv("HTTP_X_FORWARDED_FOR");
else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	$ip = getenv("REMOTE_ADDR");
else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	$ip = $_SERVER['REMOTE_ADDR'];

$usuario = 'ADMIN';
if ( $php_version == '5.6' ) {
	$usuario = $_SESSION['auth_usuario'];
}

try {
	if ($accion == "listAll") {
		$response = $objValeModel->getListAll($_POST['data'], $objjqGridModel);
		$objValeTemplate->gridView(json_encode($response));
	} else if ($accion == "listAllExcel") {
		$response = $objValeModel->getListAllExcel($_POST["data"]);
		$objValeTemplate->gridViewExcel(json_encode($response));
	} else if ($accion == "add") {
		$response = $objValeModel->addVale($_POST["arrFormAgregarVale"], $_POST["arrDetailCreditVoucher"], $usuario, $ip);
		echo json_encode($response);
	}else if ($accion == "update") {
		$response = $objValeModel->updateVale($_POST["arrFormAgregarVale"], $_POST["arrDetailCreditVoucher"], $usuario, $ip);
		echo json_encode($response);
	}else if($accion == "editVale") {
		$response = $objValeModel->editVale($_POST["data"]);
		echo json_encode($response);
	}else if($accion == "deleteVale") {
		$response = $objValeModel->deleteVale($_POST["data"]);
		echo json_encode($response);
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

