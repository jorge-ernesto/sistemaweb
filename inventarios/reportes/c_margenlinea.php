<?php
include_once('../include/m_sisvarios.php');

class MargenLineaController extends Controller {

    function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
		isset($_REQUEST['task']) ? $this->task = $_REQUEST['task'] : $this->task = "";
   	}
    
    function Run() {
		$this->Init();
		include "reportes/t_margenlinea.php";
		include "reportes/m_margenlinea.php";

		$result = '';
		$result_f = '';

		switch ($this->action) {

			case 'Reporte':
				$almacen   = trim($_REQUEST['estacion']);
				$tipolista = trim($_REQUEST['tipolista']);
				$anio      = trim($_REQUEST['anio']);
				$mes       = trim($_REQUEST['mes']); 
				$repor     = MargenLineaModel::busqueda($almacen, $tipolista, $anio, $mes);				
				$result_f  = MargenLineaTemplate::listado($repor);
				break;

			case 'detalle':
				$almacen   = trim($_REQUEST['estacion']);
				$tipolista = trim($_REQUEST['tipolista']);
				$anio      = trim($_REQUEST['anio']);
				$mes       = trim($_REQUEST['mes']); 
				$detalle = MargenLineaModel::obtenerDetalleLinea($_REQUEST['linea'], $almacen, $tipolista, $anio, $mes);
				$this->visor->addComponent("Detalle", "div" . $_REQUEST['linea'], MargenLineaTemplate::mostrarDetalle($detalle));
				break;

			default:
				$result = MargenLineaTemplate::search_form();
				$result_f = "";
				break;
		}
		
		$this->visor->addComponent("ContentT", "content_title", MargenLineaTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}

