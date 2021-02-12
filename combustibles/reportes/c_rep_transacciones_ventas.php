<?php
session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');
include('t_rep_transacciones_ventas.php');
include('m_rep_transacciones_ventas.php');

/* Get Class Template y Model */
$objtem 	= new TemplateReporteTransaccionVenta();
$objmodel 	= new ModelReporteTransaccionVenta();

/* Get Variables de Request */
$accion					= $_REQUEST['accion'];
$_SESSION['data_excel']	= null;

try {
	if($accion == "Search") {
		$data = ModelReporteTransaccionVenta::SearchTransaccionVenta($_REQUEST);
		TemplateReporteTransaccionVenta::ListaTransaccionVenta($data, $_REQUEST['rdnotipo']);
	} else if($accion == "SearchExcel") {
		$data		= ModelReporteTransaccionVenta::SearchTransaccionVenta($_REQUEST);
		$razsocial	= ModelReporteTransaccionVenta::GetEmpresa($_REQUEST['cmbnualmacen']);
		TemplateReporteTransaccionVenta::ListaTransaccionVenta($data, $_REQUEST['rdnotipo']);

		if(!empty($data)) {
			$_SESSION['data']			= $data;
			$_SESSION['razsocial']		= $razsocial;
			$_SESSION['txtnofechaini']	= $_REQUEST['txtnofechaini'];
			$_SESSION['txtnofechafin']	= $_REQUEST['txtnofechafin'];
			$_SESSION['rdnotipo']		= $_REQUEST['rdnotipo'];
		}

	}
} catch (Exception $r) {
	echo $r->getMessage();
}
