<?php
session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_consumo_xplaca.php');
include('m_consumo_xplaca.php');
include('consumo_xplaca_pdf.php');

$objmodel = new Consumos_Placa_Model();
$objtem = new Consumos_Placa_Template();
$pdf = new consumo_xplaca_pdf();

$accion = $_REQUEST['accion'];
$_SESSION['data_excel'] = null;

try {
	if ($accion == "buscar") {
		$almacen 	= $_REQUEST['almacen'];
		$fdesde	 	= $_REQUEST['fecha_ini'];
		$fhasta	 	= $_REQUEST['fecha_fin'];
		$cliente	= trim($_REQUEST['cliente']);
		$placa		= trim($_REQUEST['placa']);
		$arrPost = array (
		    'sMostrarReporte' => trim($_POST['sMostrarReporte']),
		    'sIdCliente' => $cliente,
		);
		$datars 	= Consumos_Placa_Model::ObtenerReporte($almacen, $fdesde, $fhasta, $cliente, $placa);
		Consumos_Placa_Template::CrearTablaReporte($datars, $arrPost);
	} else if ($accion == "pdf") {
		$almacen 	= $_REQUEST['almacen'];
		$fdesde	 	= $_REQUEST['fecha_ini'];
		$fhasta	 	= $_REQUEST['fecha_fin'];
		$cliente	= trim($_REQUEST['cliente']);
		$placa		= trim($_REQUEST['placa']);
		$arrGet = array (
		    'sMostrarReporte' => trim($_REQUEST['sMostrarReporte']),
		    'sIdCliente' => $cliente,
		);
		
		/*Buscamos datos de empresa*/
		$empresa = Consumos_Placa_Model::datosEmpresa($_SESSION['almacen']);
		error_log(json_encode($_SESSION));
		error_log(json_encode($empresa));
		/*Fin buscamos datos de empresa*/

		$resultados = Consumos_Placa_Model::ObtenerReporte($almacen, $fdesde, $fhasta, $cliente, $placa);
		consumo_xplaca_pdf::ConsumosPlacaPDF($resultados,$fdesde,$fhasta, $arrGet,$empresa);
        header("Location: /sistemaweb/ventas_clientes/reportes/pdf/consumos_x_placa.pdf");
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

