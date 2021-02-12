<?php
class DespachoLineaController extends Controller {
	
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'movimientos/m_despacholinea.php';
		include 'movimientos/t_despacholinea.php';

		$this->Init();

		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;

		switch($this->action) {
			case "Buscar":
			$listado = true;
			break;

			default:
			$form_search = true;
			break;
		}

		if($form_search) {
			$result = DespachoLineaTemplate::formSearch();
		}

		if($listado) {
			$resultados = DespachoLineaModel::busqueda($_REQUEST['rb_tipoconsulta']);
			$result_f = DespachoLineaTemplate::listado($resultados);
		}

		$this->visor->addComponent("ContentT", "content_title", DespachoLineaTemplate::titulo());

		if ($result != "") {
			$this->visor->addComponent("ContentB", "content_body", $result);
		}

		if ($result_f != "") {
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
		}
	}
}
