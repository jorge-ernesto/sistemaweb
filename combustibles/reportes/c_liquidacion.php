<?php

class LiquidacionController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_liquidacion.php';
		include 'reportes/t_liquidacion.php';	

		$this->Init();	
		$result 	= '';
		$result_f 	= '';
		$search_form 	= false;

		switch ($this->action) {
			case "Reporte":
				echo 'Entro al Reporte'."\n";

				if ($_REQUEST['tipo'] == 'GLP') {
					$results  = LiquidacionModel::obtieneGLP($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
					$result_f = LiquidacionTemplate::reporteGLP($results,$_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				} else {	
					$results  = LiquidacionModel::obtieneLiquidacion($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
					$result_f = LiquidacionTemplate::reporte($results,$_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"], "", "", "");
				} break;
			case "pdf":
				$results  = LiquidacionModel::obtieneLiquidacion($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				LiquidacionTemplate::reportePDF($results, $_REQUEST['desde'], $_REQUEST['hasta']);
				break;
			default:
				$search_form = true;
				break;
		}

		if ($search_form) {
			$result = LiquidacionTemplate::search_form();
		}	

		if ($result != '') 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '') 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
