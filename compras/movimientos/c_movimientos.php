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

			case "ORDENCOMPRA":
				include "movimientos/c_ordencompra.php";
				$Controlador = new OrdenCompraController("ORDENCOMPRA");
				break;

			case "REGISTROCOMPRAS":
				include "movimientos/c_registro_compras.php";
				$Controlador = new RegistroComprasController($this->task);
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
