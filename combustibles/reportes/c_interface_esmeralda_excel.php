<?php
ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_interface_esmeralda_excel.php');
include('m_interface_esmeralda_excel.php');

$objmodel 	= new Descuentos_Especiales_Model();
$objtem 	= new Descuentos_Especiales_Template();

$accion 		= $_REQUEST['accion'];
$_SESSION['data_excel'] = null;

try {
	if ($accion == "excel") {
		$modulos 	= $_REQUEST['modulos'];
		$sucursal 	= $_REQUEST['sucursal'];
		$year 		= $_REQUEST['year'];
		$month 		= $_REQUEST['month'];

		$fecha = $year.$month;

		$info_are_facturas_boletas	= Descuentos_Especiales_Model::ActualizarDatosFacturas($sucursal, $fecha);
		$tickes_anu					= Descuentos_Especiales_Model::getTickesAnulados($sucursal, $fecha);
		$info_are_post				= Descuentos_Especiales_Model::ActualizarDatosPostrans($sucursal, $fecha, $tickes_anu);
		//$resultados				= array_merge($info_are_facturas_boletas, $info_are_post);
		$resultados					= array_merge($info_are_post, $info_are_facturas_boletas);

		if(!empty($resultados)){
			$_SESSION['data_excel'] = $resultados;
			$_SESSION['data_fecha'] = $fecha;
		}
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

