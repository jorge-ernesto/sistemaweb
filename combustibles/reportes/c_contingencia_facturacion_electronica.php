<?php
session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_contingencia_facturacion_electronica.php');
include('m_contingencia_facturacion_electronica.php');

// Get Class Template y Model
$objModel 		= new ModelContingEnciaFE();
$objTemplate 	= new TemplateContingEnciaFE();

// Get Variables de Request
$accion						= $_REQUEST['accion'];
$_SESSION['data_text']		= null;
$_SESSION['nuruc']			= null;
$_SESSION['txtnofechaini']	= null;
$_SESSION['txtnofechafin']	= null;
$_SESSION['iCantidadEnviado']	= null;

try {
	if ($accion == "Search") {
		$arrData = $objModel->ContingenciaFE($_REQUEST);
		$objTemplate->GridviewContingenciaFE($arrData);
	} else if ($accion == "GenerarTxt") {
		$arrData 	= $objModel->ContingenciaFE($_REQUEST);
		$nuruc 		= $objModel->GetEmpresa($_REQUEST['cboalmacen']);

		$objTemplate->GridviewContingenciaFE($arrData);

		if(!empty($arrData)){
			$_SESSION['data']			= $arrData;
			$_SESSION['nuruc']			= $nuruc;
			$_SESSION['txtnofechaini']	= $_REQUEST['txtnofechaini'];
			$_SESSION['txtnofechafin']	= $_REQUEST['txtnofechafin'];
			$_SESSION['iCantidadEnviado']	= $_REQUEST['iCantidadEnviado'];
		}
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

