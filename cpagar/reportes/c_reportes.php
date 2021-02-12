<?php

class ReportesController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();

	// JCH	
    $this->task = @$_REQUEST["task"];
    isset($_REQUEST["action"])?$this->action = $_REQUEST["action"]:$this->action = '';
	
    }
    
    function Run()
    {
	$this->Init();
	$Controlador = null;
	switch ($this->request) {
	    case 'REGISTRO':
		include('reportes/c_registro.php');
		$Controlador = new RegistroController($this->request);
	    break;
		
	    case 'REIMPRESION':
		include('reportes/c_reimpresion.php');
		$Controlador = new ReimpresionController($this->task);
	    break;
	    case 'REGISTROOFICIAL':
		include('reportes/c_registrooficial.php');
		$Controlador = new RegistroOficialController($this->request);
	    break;
	    default:
		$this->visor->AddComponent("ContentB", "content_body", "<h2>Reporte no conocido</h2>");
		break;
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
}

?>