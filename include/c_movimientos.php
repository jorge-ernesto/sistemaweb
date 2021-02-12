<?php

class MovimientosController extends Controller {

	function Init() {
		$this->visor = new Visor();
	}

	function Run() {
		$this->Init();
		$Controlador = null;
		switch ($this->request) {

			case 'INTERFAZ':
				include('c_interface.php');
				$Controlador = new InterfaceController("INTERFAZ");
			        break;

			default:
				$this->visor->addComponent("ContentB", "content_body", "<h2><b>Funcion de movimientos no conocida</b></h2>");
				break;	
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
}
