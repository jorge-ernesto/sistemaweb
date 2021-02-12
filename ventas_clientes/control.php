<?php
  /*
    Sistema de Contabilidad 
    Principal
    @TBCA
  */

  include('start.php');

  $Controlador='';

  switch (substr($rqst, 0 ,strcspn($rqst,'.'))){
  case "REPORTES":
    include('reportes/c_reportes.php');
    $Controlador = new ReportesController(substr($rqst, strcspn($rqst,'.')+1));
  break;
  case "MOVIMIENTOS":
    include('movimientos/c_movimientos.php');
    $Controlador = new MovimientosController(substr($rqst, strcspn($rqst,'.')+1));
  break;
  
  case "MAESTROS":
    include('maestros/c_maestros.php');
    $Controlador = new MaestrosController(substr($rqst, strcspn($rqst,'.')+1));
  break;

  case "FACTURACION":
    include('facturacion/c_facturacion.php');
    $Controlador = new FacturacionController(substr($rqst, strcspn($rqst,'.')+1));
  break;

  case "CLIENTES":
    include('clientes/c_clientes.php');
    $Controlador = new ClientesController(substr($rqst, strcspn($rqst,'.')+1));
  break;
    
  case "UTILITARIOS":
    include('utilitarios/c_utilitarios.php');
    $Controlador = new UtilitariosController(substr($rqst, strcspn($rqst,'.')+1));
  break;
   case "LIQUIDACION":
       echo "Sistema cargando los vales";
    include('liquidacion_vales/c_liquidacion_vales_relacion.php');
    $Controlador = new LiquidacionValesRelacionController(substr($rqst, strcspn($rqst,'.')+1));
  break;
  
  default:
    //Entrada de defecto o inicial
    include('main/c_main.php');
    $Controlador = new MainController(substr($rqst, strcspn($rqst,'.')+1));
    break;
  }

  $Controlador->Run();
  echo $Controlador->outputVisor();

