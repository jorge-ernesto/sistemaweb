<?php

class MovimientosController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	$this->task = @$_REQUEST["task"];
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }
    
    function Run()
    {
	$this->Init();
	$Controlador = null;
	switch ($this->request) {
	    case 'APLICACIONES':
	      include('movimientos/c_aplicaciones.php');
	      $Controlador = new AplicacionesController($this->task);
	    break;

	    case 'RECCOMPDEV':
	      include('movimientos/c_reccompdev.php');
	      $Controlador = new RecCompDevController($this->task);
	    break;

	    default:
	      $this->visor->AddComponent("ContentB", "content_body", "<h2>Movimiento no conocido</h2>");
	    break;
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
}

?>