<?php
session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_formato_fisico.php');
include('m_formato_fisico.php');

$objmodel = new Formato_Fisico_Model();
$objtem   = new Formato_Fisico_Template();

$accion = $_REQUEST['accion'];
$_SESSION['data_excel'] = null;

/* HORA PERUANA */
date_default_timezone_set("America/Lima");

$t		 = microtime(true);
$micro = sprintf("%06d",($t - floor($t)) * 1000000);
$hora  = date('H:i:s.'.$micro,$t);
$fecha = date(Y."-".m."-".d)." ".$hora;

try {
	if($accion == "find_ubica") {
		$almacen	= $_REQUEST['cod_almacen'];
		$ubicaciones 	= Formato_Fisico_Model::Ubicaciones($almacen);
		$cadena 	= "";

//$cadena .='<option value="T">Seleccionar..</option>';
		foreach($ubicaciones as $fila) {
			$ubica  = $fila['codigo'];
			$cadena .='<option value="'.$ubica.'">' . $fila['codigo'] . ' - ' . $fila['nombre'] . '</option>';
		}

		echo "{'msg':'" . $cadena . "'}";

	} else if ($accion == "buscar") {

		$almacen = $_REQUEST['almacen'];
		$ubica   = $_REQUEST['ubica'];
		$stk		= $_REQUEST['stk'];
		$orden	= $_REQUEST['orden'];
		$datars	= Formato_Fisico_Model::ObtenerReporte($almacen, $ubica, $stk, $orden, $hora);
		Formato_Fisico_Template::CrearTablaReporte($datars);
	} else if ($accion == "excel") {
		$almacen = $_REQUEST['almacen'];
		$ubica	= $_REQUEST['ubica'];
		$stk 		= $_REQUEST['stk'];
		$orden	= $_REQUEST['orden'];
		$datars	= Formato_Fisico_Model::ObtenerReporte($almacen, $ubica, $stk, $orden);
		Formato_Fisico_Template::CrearTablaReporte($datars);
		if(!empty($datars))
			$_SESSION['data_excel'] = $datars;
			$_SESSION['almacen'] 	= $almacen;
			$_SESSION['fecha'] 	= $fecha;
	}
} catch (Exception $r) {
	echo $r->getMessage();
}
