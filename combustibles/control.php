<?php

/*
  Sistema de Contabilidad sistemaweb
  Principal
  @TBCA
 */
include('start.php');
$Controlador = '';

switch (substr($rqst, 0, strcspn($rqst, '.'))) {
    case "REPORTES":
        include('reportes/c_reportes.php');
        $Controlador = new ReportesController(substr($rqst, strcspn($rqst, '.') + 1));
        break;

    case "MOVIMIENTOS":
        include('movimientos/c_movimientos.php');
        $Controlador = new MovimientosController(substr($rqst, strcspn($rqst, '.') + 1));
        break;

    case "MAESTROS":
        include('maestros/c_maestros.php');
        $Controlador = new MaestrosController(substr($rqst, strcspn($rqst, '.') + 1));
        break;

    case "PROMOCIONES":
        include('promociones/c_promociones.php');
        $Controlador = new PromocionesController(substr($rqst, strcspn($rqst, '.') + 1));
        break;
	
   

    default:
        //Entrada de defecto o inicial
        include('main/c_main.php');
        $Controlador = new MainController(substr($rqst, strcspn($rqst, '.') + 1));
        break;
}
$Controlador->Run();
echo $Controlador->outputVisor();
