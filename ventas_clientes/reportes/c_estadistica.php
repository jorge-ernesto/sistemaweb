<?php

class EstadisticaController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'reportes/m_estadistica.php';
		include 'reportes/t_estadistica.php';
	
		$this->Init();	
		$result = '';
		$result_f = '';

		switch ($this->action) {

			case "Reporte":
				$busqueda = EstadisticaModel::buscar($_REQUEST['almacen'], $_REQUEST['desde1'], $_REQUEST['desde2'], $_REQUEST['hasta1'], $_REQUEST['hasta2']);
				$result_f = EstadisticaTemplate::reporte($busqueda);				
				break;

		    	default:				
				$result = EstadisticaTemplate::search_form();
				$result_f = "";
				break;
		}		
		
		$this->visor->addComponent("ContentT", "content_title", EstadisticaTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);			
	}
}
