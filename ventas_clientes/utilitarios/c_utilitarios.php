<?php

class UtilitariosController extends Controller
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
	    case 'INTERFACES':
		include('utilitarios/c_interface_movi.php');
		$Controlador = new InterfaceMovController($this->task);
	    break;
	    
	    default:
		$this->visor->AddComponent("ContentB", "content_body", "<h2>Utilitario no conocido</h2>");
		break;
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
}

