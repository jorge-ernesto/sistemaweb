<?php
class ExistenciasController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
	}

	function Run() {
		include "reportes/m_existencias.php";
		include "reportes/t_existencias.php";

		$this->Init();

		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;

		switch ($this->action) {
			case "PDF":
				$resultado = ExistenciasModel::search($_REQUEST['fecha']/*, $_REQUEST['tipo'], 5*/);
				ExistenciasTemplate::reportePDF($resultado, $_REQUEST['fecha'], ""/*, $_REQUEST['tipo']*/);
			return;
			case "search":
				$listado = true;
			break;
			default:
				$form_search = true;
			break;
		}

		if($form_search) {
			$result = ExistenciasTemplate::formSearch();
		}

		if($listado) {
			$resultado = ExistenciasModel::search($_REQUEST['fecha']/*, $_REQUEST['tipo_reporte'], 5*/);
			$a = ExistenciasModel::obtenerNombreBreve();
			$result_f = ExistenciasTemplate::listado($resultado, $_REQUEST['fecha'],$a );
		}

		$this->visor->addComponent("ContentT", "content_title", ExistenciasTemplate::titulo());
		if($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
