<?php

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_rep_ajuste_ubicacion.php');
include('m_rep_ajuste_ubicacion.php');

/* Get Class Template y Model */

$objtem 	= new TemplateReporteAjusteUbicacion();
$objmodel 	= new ModelReporteAjusteUbicacion();

/* Get Variables de Request */

$accion			= $_REQUEST['accion'];
$_SESSION['data_excel']	= null;

try {

	if ($accion == "GetUbicacion") {

		$data = ModelReporteAjusteUbicacion::GetUbicacion($_REQUEST['nualmacen']);

		$cadena = '<option value="T">Todos</option>';

		foreach ($data as $row)
			$cadena .= '<option value=' . $row['nuubicacion']. '>' . $row['noubicacion'] . '</option>';

		echo "{'msg':'" . $cadena . "'}";

	}else if ($accion == "BuscarUbicacion") {

		$data = ModelReporteAjusteUbicacion::BuscarUbicacion($_REQUEST);

		TemplateReporteAjusteUbicacion::ListaAjusteUbicacion($data, $_REQUEST['notipo']);

	} else if ($accion == "BuscarUbicacionExcel") {

		$data		= ModelReporteAjusteUbicacion::BuscarUbicacion($_REQUEST);
		$nualmacen	= ModelReporteAjusteUbicacion::GetAlmacen($_REQUEST['nualmacen']);

		TemplateReporteAjusteUbicacion::ListaAjusteUbicacion($data, $_REQUEST['notipo']);

		if(!empty($data)){
			$_SESSION['data']	= $data;
			$_SESSION['noalmacen']	= $nualmacen[0]['noalmacen'];
			$_SESSION['fbuscar']	= $_REQUEST['fbuscar'];
			$_SESSION['notipo']	= $_REQUEST['notipo'];
		}

	}

} catch (Exception $r) {
	echo $r->getMessage();
}

