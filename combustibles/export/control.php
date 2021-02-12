<?php

/*
  Sistema de Contabilidad sistemaweb
  Principal
  @TBCA
 */
include('start.php');
$Controlador = '';

switch (substr($rqst, 0, strcspn($rqst, '.'))) {   

    case "MOVIMIENTOS":
        include('c_movimientos.php');
        $Controlador = new MovimientosController(substr($rqst, strcspn($rqst, '.') + 1));
        break;
    default:
        //Entrada de defecto o inicial
        include('main/c_main.php');
        $Controlador = new MainController(substr($rqst, strcspn($rqst, '.') + 1));
        break;
}
$Controlador->Run();
echo $Controlador->outputVisor();
