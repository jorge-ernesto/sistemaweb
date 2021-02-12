<?php

class MovimientosController extends Controller {

	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
	}
    
	function Run() {
		$this->Init();
		$Controlador = null;
		
		switch ($this->request) {

			case 'APLICACIONES':
				
				include('movimientos/c_aplicaciones.php');

				$Controlador = new AplicacionesController($this->task);
	    			break;

	    		case 'INCLUSION':
				include('movimientos/c_inclusion.php');
				$Controlador = new InclusionController($this->task);
	    			break;

	    		case 'ELIMINACION':
				include('movimientos/c_eliminacion.php');
				$Controlador = new EliminacionController($this->task);
	    			break;	  

			case 'ANTICIPOS':
				include('movimientos/c_anticipos.php');
				$Controlador = new AnticiposController($this->task);
	    			break;

			case 'PRECANCELACION':
				include('movimientos/c_precancelado.php');
				$Controlador = new PrecanceladoController($this->task);
				break; 	 

			case 'SALDITOS':
				include('movimientos/c_salditos.php');
				$Controlador = new SalditosController($this->task);
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
