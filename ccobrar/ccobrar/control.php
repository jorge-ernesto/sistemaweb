<?php

include('start.php');
include_once('../include/m_sisvarios.php');
$Controlador = '';

switch (substr($rqst, 0 ,strcspn($rqst,'.'))){

	case 'MAESTROS':
		include('maestros/c_maestros.php');
		$Controlador = new MovimientosController(substr($rqst, strcspn($rqst,'.')+1));
		break;

	case 'MOVIMIENTOS':
		include('movimientos/c_movimientos.php');
		$Controlador = new MovimientosController(substr($rqst, strcspn($rqst,'.')+1));
		break;

	case 'REPORTES':
		include('reportes/c_reportes.php');
		$Controlador = new ReportesController(substr($rqst, strcspn($rqst,'.')+1));
		break;

	case 'OBTCANCELACION':
		include('obtcancelaciones/c_principal.php');
		$Controlador = new PrincipalController(substr($rqst, strcspn($rqst,'.')+1));
		break;

	default:
		include('main/c_main.php');
		$Controlador = new MainController(substr($rqst, strcspn($rqst,'.')+1));
		break;
}
$Controlador->Run();
echo $Controlador->outputVisor();

