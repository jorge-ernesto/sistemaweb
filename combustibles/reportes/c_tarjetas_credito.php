<?php

session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_tarjetas_credito.php');
include('m_tarjetas_credito.php');

$objmodel 	= new Tarjetas_Credito_Model();
$objtem 	= new Tarjetas_Credito_Template();

$accion = $_REQUEST['accion'];
$_SESSION['data_excel'] = null;

try {

	if($accion == "fecha_servidor") {

		$fecha		= $_REQUEST['fecha_inicial'];
		$turnos 	= Tarjetas_Credito_Model::ObtenerFechaDTurno($fecha);

		$cadena 	= "";

		foreach($turnos as $fila) {

			$turno = $fila['turno'];       
			$cadena.='<option value="'.$turno.'">' . $fila['turno'] . '</option>';

		}

		echo "{'msg':'" . $cadena . "'}";

	} else if ($accion == "buscar") {

		$almacen 	= $_REQUEST['almacen'];
		$fdesde	 	= $_REQUEST['fecha_ini'];
		$fhasta	 	= $_REQUEST['fecha_fin'];
		$tdesde 	= $_REQUEST['turno_ini'];
		$thasta 	= $_REQUEST['turno_fin'];
		$tipo 		= $_REQUEST['tipo'];
		$tarjeta 	= $_REQUEST['tarjeta'];
		$datars 	= Tarjetas_Credito_Model::ObtenerReporte($almacen, $fdesde, $fhasta, $tdesde, $thasta, $tipo, $tarjeta);

		Tarjetas_Credito_Template::CrearTablaReporte($datars);

	} else if ($accion == "excel") {

		$almacen 	= $_REQUEST['almacen'];
		$fdesde	 	= $_REQUEST['fecha_ini'];
		$fhasta	 	= $_REQUEST['fecha_fin'];
		$tdesde 	= $_REQUEST['turno_ini'];
		$thasta 	= $_REQUEST['turno_fin'];
		$tipo 		= $_REQUEST['tipo'];
		$tarjeta 	= $_REQUEST['tarjeta'];
		$resultados 	= Tarjetas_Credito_Model::ObtenerReporte($almacen, $fdesde, $fhasta, $tdesde, $thasta, $tipo, $tarjeta);

		if(!empty($resultados))
			$_SESSION['data_excel'] = $resultados;

	}

} catch (Exception $r) {
	echo $r->getMessage();
}

