<?php
/*
$version = explode('.', PHP_VERSION);
$php_version = $version[0] . '.' . $version[1];
if ( $php_version == '5.6' ) {
*/
	session_start();
//}

date_default_timezone_set('UTC');

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_consumo_vales.php');
include('m_consumo_vales.php');

$objmodel 	= new ConsumoValesModel();
$objtem 	= new ConsumoValesTemplate();

$accion = $_REQUEST['accion'];
$_SESSION['data_excel'] = null;

try {
	if ($accion == "buscar") {
		$almacen 	= $_REQUEST['almacen']; //ME INTERESA
		$fdesde	 	= $_REQUEST['fecha_ini']; //ME INTERESA
		$fhasta	 	= $_REQUEST['fecha_fin']; //ME INTERESA
		$Nu_Documento_Identidad	= $_POST['Nu_Documento_Identidad']; //ME INTERESA
		$liquidacion	= $_REQUEST['liquidacion'];
		$factura		= $_REQUEST['factura'];
		$orden		= $_REQUEST['orden'];
		$hora		= $_REQUEST['hora'];
		
		$arrRequest = array(
			'iTipoCliente' => $_REQUEST['iTipoCliente'],
			'sPrecioPizarra' => $_REQUEST['sPrecioPizarra'],
			'iTipoVersion' => $_REQUEST['iTipoVersion'],
		);
		$datars = ConsumoValesModel::ObtenerReporte($almacen, $fdesde, $fhasta, $Nu_Documento_Identidad, $liquidacion, $factura, '', $hora, $arrRequest);
		echo "<script>console.log('" . json_encode( array(
			"data" => $datars, 
			"myorden" => $orden, 
			"hora" => $hora, 
			"arrRequest" => $arrRequest
		) ) . "')</script>";

		ConsumoValesTemplate::CrearTablaReporte($datars, $orden, $hora, $arrRequest);

	} else if ($accion == "excel") {
		$almacen 	= $_REQUEST['almacen'];
		$fdesde	 	= $_REQUEST['fecha_ini'];
		$fhasta	 	= $_REQUEST['fecha_fin'];
		$Nu_Documento_Identidad	= $_POST['Nu_Documento_Identidad'];
		$liquidacion	= $_REQUEST['liquidacion'];
		$factura	= $_REQUEST['factura'];
		$orden		= $_REQUEST['orden'];
		$hora		= $_REQUEST['hora'];
		$arrRequest = array(
			'iTipoCliente' => $_REQUEST['iTipoCliente'],
			'sPrecioPizarra' => $_REQUEST['sPrecioPizarra'],
			'iTipoVersion' => $_REQUEST['iTipoVersion'],
		);

		$resultados = ConsumoValesModel::ObtenerReporte($almacen, $fdesde, $fhasta, $Nu_Documento_Identidad, $liquidacion, $factura, '', $hora, $arrRequest);

		if(!empty($resultados)){
			$_SESSION['data_excel'] = $resultados;
			$_SESSION['orden'] = $_REQUEST['orden'];
			$_SESSION['arrRequest'] = $arrRequest;
		}
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

