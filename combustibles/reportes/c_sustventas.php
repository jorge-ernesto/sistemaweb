<?php

class SustentoVentasController extends Controller{
	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_sustventas.php';
		include 'reportes/t_sustventas.php';

		$this->Init();

		$result = '';
		$result_f = '';
		$search_form = false;

		switch ($this->action) {
			case "Reporte":
			echo 'Entro al Reporte';
			if($_REQUEST['tipo'] == 'GLP') {
				$results0 = SustentoVentasModel::obtieneTC($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results1 = SustentoVentasModel::obtieneValesGLP($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results2 = SustentoVentasModel::obtieneTarjetaGLP($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results3 = SustentoVentasModel::obtieneEfectivosGLP($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results4 = SustentoVentasModel::obtieneFaltantesGLP($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results5 = SustentoVentasModel::obtieneAfericionGLP($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$result_f = SustentoVentasTemplate::reporteGLP($results0,$results1,$results2,$results3,$results4,$results5,$_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
			} else {
				$results0 = SustentoVentasModel::obtieneTC($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
				$results1 = SustentoVentasModel::obtieneVales($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
				$results2 = SustentoVentasModel::obtieneTarjeta($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
				$results3 = SustentoVentasModel::obtieneEfectivos($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
				$results4 = SustentoVentasModel::obtieneFaltantes($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
				if($_REQUEST['tipo'] == 'M') {			
					$results5 = SustentoVentasModel::obtieneAfericionMarket($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
				} else {
					$results5 = SustentoVentasModel::obtieneAfericion($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
				}
				$result_f = SustentoVentasTemplate::reporte($results0,$results1,$results2,$results3,$results4,$results5,$_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
			}
			break;

			case "pdf":
			$results0 = SustentoVentasModel::obtieneTC($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);	
			$results1 = SustentoVentasModel::obtieneVales($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
			$results2 = SustentoVentasModel::obtieneTarjeta($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
			$results3 = SustentoVentasModel::obtieneEfectivos($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
			$results4 = SustentoVentasModel::obtieneFaltantes($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
			$results5 = SustentoVentasModel::obtieneAfericion($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST["tipo"]);
			SustentoVentasTemplate::reportePDF($results0,$results1,$results2,$results3,$results4,$results5, $_REQUEST['desde'], $_REQUEST['hasta']);
			break;

			default:
			$search_form = true;
			break;
		}

		if($search_form) {
			$result = SustentoVentasTemplate::search_form();
		}

		if($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
		if($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
