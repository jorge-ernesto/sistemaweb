<?php
  /*
    Sistema de Contabilidad ACOSA
    Principal
    @TBCA
  */

  include('start.php');

  $Controlador='';

  switch (substr($rqst, 0 ,strcspn($rqst,'.'))){
  case 'MAESTROS':
   //Entrada Maestros
    include('maestros/c_maestros.php');
    $Controlador = new MaestrosController(substr($rqst, strcspn($rqst,'.')+1));
  break;

  case 'MOVIMIENTOS':
   //Entrada Maestros
    include('movimientos/c_movimientos.php');
    $Controlador = new MovimientosController(substr($rqst, strcspn($rqst,'.')+1));
  break;

  case 'REPORTES':
   //Entrada Reportes
    include('reportes/c_reportes.php');
    $Controlador = new ReportesController(substr($rqst, strcspn($rqst,'.')+1));
  break;

  default:
    //Entrada de defecto o inicial
    include('main/c_main.php');
    $Controlador = new MainController(substr($rqst, strcspn($rqst,'.')+1));
    break;
  }

  $Controlador->Run();
  echo $Controlador->outputVisor();

?>