<?php

class PrincipalController extends Controller {
	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST['task'];
		$this->action = @$_REQUEST['action'];
	}

	function Run() {
		$this->Init();
		$Controlador = null;
		switch ($this->request) {
			case 'OBTENERCANCELACION':
				include "c_obtcancelaciones.php";
				$Controlador = new CancelacionController($this->task);
				break;
		}
		if ($Controlador != null) {
				$Controlador->Run();
				$this->visor = $Controlador->visor;
		}
	}
}

