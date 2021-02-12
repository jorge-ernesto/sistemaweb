<?php

/*
  Sistema de Contabilidad sistemaweb
  Principal
  @TBCA
 */
include('start.php');
$Controlador = '';


        include('c_movimientos.php');
        $Controlador = new MovimientosController(substr($rqst, strcspn($rqst, '.') + 1));


$Controlador->Run();
echo $Controlador->outputVisor();
