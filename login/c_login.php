<?php

class LoginController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	$this->Init();

	$result = '';
	$Controlador = null;
	
	switch($this->request) {
	    case "MAIN":
		include "login/c_main.php";
		$Controlador = new MainController("MAIN");
		break;
	    default:
		$this->visor->addComponent("ContentB", "content_body", "<h2><b>Funcion de login no conocida</b></h2>");
		break;
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
}

?>
