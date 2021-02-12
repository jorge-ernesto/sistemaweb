<?php
session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_regenerar_saldos.php');
include('m_regenerar_saldos.php');
require("/sistemaweb/clases/funciones.php");

$objModel = new Regenerar_Saldos_Model();
$objTemplate = new Regenerar_Saldos_Template();

$accion = $_REQUEST['sAction'];

try {
	if ($accion == "get_process_now") {
		echo json_encode($objModel->get_status_balance_regeneration());
	}
	if ($accion == "procesar") {
		echo json_encode($objModel->execute_balance_regeneration($_POST));
	}
	if ($accion == "verify_status_process") {
		echo json_encode($objModel->verify_status_process_balance_item());
	}
	if ($accion == "stop_process_balance") {
		echo json_encode($objModel->stop_process_balance());
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

