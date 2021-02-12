<?php

class MaestrosController extends Controller
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
	    case 'SUNAT_RETENCION':
		include('maestros/c_agente_retencion.php');
		$Controlador = new AgenteRetencionController($this->task);
	    break;
	    
	    case 'SUNAT_PERCEPCION':
		include('maestros/c_agente_percepcion.php');
		$Controlador = new AgentePercepcionController($this->task);
	    break;

	    case 'SUNAT_PERCEPCION_VI':
		include('maestros/c_agente_percepcion_vi.php');
		$Controlador = new AgentePercepcionViController($this->task);
	    break;

	    case 'SUNAT_BUENCONTR':
		include('maestros/c_agente_buencontr.php');
		$Controlador = new AgenteBuencontrController($this->task);
	    break;
	    
	    case 'SUNAT_PRINCCONTR':
		include('maestros/c_agente_princcontr.php');
		$Controlador = new AgentePrinccontrController($this->task);
	    break;

	    case 'SUNAT_CONTRNOHALL':
		include('maestros/c_contrib_no_hallado.php');
		$Controlador = new ContribNoHalladoController($this->task);
	    break;
		
	    case 'RUBROSCP':
		include('maestros/c_rubroscp.php');
		$Controlador = new RubrosCPController($this->task);
	    break;

	    default:
		$this->visor->AddComponent("ContentB", "content_body", "<h2>Maestro conocido</h2>");
		break;
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
}

?>