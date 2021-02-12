<?php

date_default_timezone_set('UTC');

class InterfaceQuipuController extends Controller {

	function Init(){
		$this->visor 	= new Visor();
		$this->task 	= @$_REQUEST["task"];
		$this->action 	= isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		$this->datos 	= isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {

		$this->Init();
		
		$result = '';

		include('movimientos/m_interface_quipu.php');
		include('movimientos/t_interface_quipu.php');

		$this->visor->addComponent('ContentT', 'content_title', InterfaceQuipuTemplate::titulo());

		switch ($this->task) {

			case 'INTERFAZQUIPU':

				switch ($this->action) {

					case 'Procesar':

						$Parametros	= InterfaceQuipuModel::obtenerParametros($_REQUEST['datos']['sucursal']);
						$res		= InterfaceQuipuModel::ActualizarInterfaces($Parametros, $_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'], $_REQUEST['datos']['sucursal'], $_REQUEST['agrupado']);
						$result		= InterfaceQuipuTemplate::imprimeResultado($res, $_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin']);

						$this->visor->addComponent("ContentF", "content_footer", $result);

					break;

					case 'Buscar':

						$Parametros	= InterfaceQuipuModel::obtenerParametros($_REQUEST['datos']['sucursal']);
						$res		= InterfaceQuipuModel::BuscarData($Parametros, $_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin']);
						$result		= InterfaceQuipuTemplate::ResultadosBusqueda($res);

						$this->visor->addComponent("ContentF", "content_footer", $result);

					break;

					default:

						$Almacenes 	= InterfaceQuipuModel::ListadoAlmacenes();
						$result 	= InterfaceQuipuTemplate::formInterface3DO($Almacenes, date("d/m/Y"));

						$this->visor->addComponent("ContentB", "content_body", $result);

					break;

				}

			break;

			default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN INTERFACE QUIPU</h2>');
			break;

		}

	}

}
