<?php

class ParteLiquidacionController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}
    
	function Run() {
		include 'reportes/m_parteliquidacion_new.php';
		include 'reportes/t_parteliquidacion_new.php';
	
		$this->Init();	
		$result = '';
		$result_f = '';
		$search_form = false;

		switch ($this->action) {

			case "Reporte":
				echo 'Entro al Reporte'."\n";		
				$results0 = ParteLiquidacionModel::obtieneTC($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results1 = ParteLiquidacionModel::obtieneParte($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results2 = ParteLiquidacionModel::obtieneCombustible($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results3 = ParteLiquidacionModel::obtieneGLP($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				if($_REQUEST['market'] == '1' || $_REQUEST['documento'] == '2' || $_REQUEST['gnv'] == '3')
					$results4 = ParteLiquidacionModel::obtieneMarket($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
					$results5 = ParteLiquidacionModel::obtieneDocumentos($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
					$results6 = ParteLiquidacionModel::obtieneGNV($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
					$result_f = ParteLiquidacionTemplate::reporte($results0,$results1,$results2,$results3,$results4,$results5,$results6,$_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'],$_REQUEST['market'],$_REQUEST['documento'],$_REQUEST['gnv']);
				break;

			default:
				$search_form = true;
				break;
		}

		if ($search_form) {
			$result = ParteLiquidacionTemplate::search_form();
		}	
		if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
