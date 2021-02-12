<?php
include_once('../include/m_sisvarios.php');
class ParticipaVentaLineaController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
		isset($_REQUEST['task']) ? $this->task = $_REQUEST['task'] : $this->task = "";
	}

	function Run() {
		include "reportes/t_participaventalinea.php";
		include "reportes/m_participaventalinea.php";
		
		$this->Init();
		
		$result = "";
		$result_f = "";
		$form_search = false;
		$reporte = false;
	
		switch ($this->action) {
		case "Buscar":
			$reporte = true;
			break;
		case 'detalle':
			$detalle = ParticipaVentaLineaModel::obtenerDetalleLinea($_REQUEST['linea'],$_REQUEST['f_desde'], $_REQUEST['f_hasta'], $_REQUEST['f_estacion']);
			$this->visor->addComponent("Detalle", "div" . $_REQUEST['linea'], ParticipaVentaLineaTemplate::mostrarDetalle($detalle));
			break;
		default:
			$form_search = true;
		}
	
		if($form_search) {
			$result = ParticipaVentaLineaTemplate::formSearch();
		}
	
		if($reporte) {
			$resultado = ParticipaVentaLineaModel::busqueda($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
			$result_f = ParticipaVentaLineaTemplate::listado($resultado,$_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
		}
	
		$this->visor->addComponent("ContentT", "content_title", ParticipaVentaLineaTemplate::Titulo());
		if($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
