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
include('m_mov_almacen_crud.php');
include('t_mov_almacen_crud.php');

$objMovimientoAlmacenModel 		= new MovimientoAlmacenCRUDModel();
$objMovimientoAlmacenTemplate 	= new MovimientoAlmacenCRUDTemplate();

$accion = TRIM($_POST['accion']);
$accion = strip_tags($accion);

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
		$response = $objMovimientoAlmacenModel->getListAll($_POST["data"], $objjqGridModel);
		$objMovimientoAlmacenTemplate->gridView($response);
	} else if ($accion == "listAllExcel") {
		$response = $objMovimientoAlmacenModel->getListAllExcel($_POST["data"]);
		$objMovimientoAlmacenTemplate->gridViewExcel(json_encode($response));
	} else if ($accion == "add") {
		$arrFletes = array();
		if ( isset($_POST["arrFletes"]) )
			$arrFletes = $_POST["arrFletes"];
		$response = $objMovimientoAlmacenModel->compraAdd($_POST["arrFormAgregar"], $_POST["arrTableAgregar"], $_POST["arrConversionGLP"], $_POST["arrRegistroCompras"], $_POST["arrFormAgregarDocumentoReferencia"], $_POST["arrDatosComplementarios"], $arrFletes, $usuario, $ip, $_POST["enviar_orden_compra"]);
		print_r(json_encode($response));
	} else if ($accion == "test") {
		return $objMovimientoAlmacenModel->getTest();
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

