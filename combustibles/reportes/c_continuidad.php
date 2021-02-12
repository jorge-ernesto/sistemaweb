<?php

class ContinuidadController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'reportes/m_continuidad.php';
		include 'reportes/t_continuidad.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';

		switch ($this->action) {
			case "Reporte":
				$reporte = ContinuidadModel::reporte($_REQUEST['desde'], $_REQUEST['hasta']);
				$result_f = ContinuidadTemplate::reporte($reporte);
				break;
		    	default:
				$result = ContinuidadTemplate::search_form(date(d."/".m."/".Y), date(d."/".m."/".Y));
				break;
		}
		if ($result != '')
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '')
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
