<?php

class ContometrosController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_contometros.php';
		include 'reportes/t_contometros.php';
		include('../include/paginador_new.php');
	
		$this->Init();
	
		$result = '';
		$result_f = '';
		$search_form = false;

		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
			$_REQUEST['rxp'] = 16;
			$_REQUEST['pagina'] = 1;
		}

		switch ($this->action) {
			case "Reporte":
				echo 'Entro al Reporte';
				$busqueda = ContometrosModel::Paginacion($_REQUEST['desde'], $_REQUEST['hasta'],$_REQUEST['rxp'],$_REQUEST['pagina']);
				$result_f = ContometrosTemplate::reporte($busqueda['datos'],$_REQUEST['desde'], $_REQUEST['hasta']);
				$result = ContometrosTemplate::search_form($_REQUEST['desde'], $_REQUEST['hasta'],$busqueda['paginacion']);
				break;

			default:
				$busqueda = ContometrosModel::Paginacion('', '',$_REQUEST['rxp'],$_REQUEST['pagina']);
				$result_f = ContometrosTemplate::reporte($busqueda['datos'],date(d."/".m."/".Y), date(d."/".m."/".Y));
				$result = ContometrosTemplate::search_form(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$search_form = true;
				break;
		}
	
		if($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
		if($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
