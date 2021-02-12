<?php
  /*
    Sistema de Contabilidad ACOSA
    Principal
    @TBCA
  */

  include('start.php');

  $Controlador='';

  switch (substr($rqst, 0 ,strcspn($rqst,'.'))){
  case "MAESTROS":
    include('maestros/c_maestros.php');
    $Controlador = new MaestrosController(substr($rqst, strcspn($rqst,'.')+1));
    break;
  case "PERMISOS":
    include('permisos/c_permisos.php');
    $Controlador = new PermisosController(substr($rqst, strcspn($rqst,'.')+1));
    break;
  default:
    //Entrada de defecto o inicial
    include('main/c_main.php');
    $Controlador = new MainController(substr($rqst, strcspn($rqst,'.')+1));
    break;
  }

  $Controlador->Run();
  echo $Controlador->outputVisor();

