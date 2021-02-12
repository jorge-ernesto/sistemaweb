<?php
class TarjetasDuplicadasController extends Controller {
	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

	function Run(){
		$this->Init();
		
		$result = '';
		$bolMensaje ='0';

		include('promociones/m_targpromocion.php');
		include('promociones/t_tarjetasduplicadas.php'); 
		require("../clases/funciones.php");	

		$result = "";
		$result_f = "";

		$fechaini = date("d/m/Y");
		$fechafin = date("d/m/Y");

		switch ($this->action) {
			case 'Buscar':
				$numerotarjeta = strtoupper(trim($_REQUEST['busquedatarjeta']));
				$fechaini = trim($_REQUEST['fecha1']);
				$fechafin = trim($_REQUEST['fecha2']);
				$busqueda = TargpromocionModel::listarCambiosTarjetas($numerotarjeta,$fechaini,$fechafin);
				$result_f = TarjetasDuplicadasTemplate::listado($busqueda);
				break;
			default:
				$result = TarjetasDuplicadasTemplate::formBuscar($fechaini,$fechafin);
				break;
		}

		$this->visor->addComponent("ContentT", "content_title", TarjetasDuplicadasTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
