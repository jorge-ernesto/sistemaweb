<?php
class OpcionesController extends Controller {
	public $action = '';
	function Init() {
		$this->visor = new Visor();
		$this->action = isset($_POST['action']) ? $_POST['action'] : $this->action='';
	}

	function Run() {
		require("permisos/m_opciones.php");
		require("permisos/t_opciones.php");

		$OpcionesModel = new OpcionesModel;
		$OpcionesTemplate = new OpcionesTemplate;

		$result = "";
		$result_f = "";
		$listado = false;

		$this->Init();

		switch ($this->action) {
			case "Buscar":
				$opciones = $OpcionesModel->obtenerOpcionesAsignadas($_REQUEST['sl_grupo']);
				$result_f = $OpcionesTemplate->listarOpcionesAsignadas($opciones,$_REQUEST['sl_grupo']);
				break;
			case "BorrarOpcion":
				OpcionesModel::eliminarOpciones($_REQUEST['chk']);
				$opciones = OpcionesModel::obtenerOpcionesAsignadas($_REQUEST['sl_grupo']);
				$result_f = OpcionesTemplate::listarOpcionesAsignadas($opciones,$_REQUEST['sl_grupo']);
				break;
			case "AgregarOpcion":
				$opciones = $OpcionesModel->obtenerOpcionesNoAsignadas($_REQUEST['sl_grupo']);
				$result_f = $OpcionesTemplate->listarOpcionesNoAsignadas($opciones,$_REQUEST['sl_grupo']);
				break;
			case "AgregarSeleccionado":
				$OpcionesModel->agregarOpciones($_REQUEST['chk'],$_REQUEST['sl_grupo']);
				$opciones = $OpcionesModel->obtenerOpcionesAsignadas($_REQUEST['sl_grupo']);
				$result_f = $OpcionesTemplate->listarOpcionesAsignadas($opciones,$_REQUEST['sl_grupo']);
				break;
			default:
				$listado = true;
				$result_f = " ";
				break;
		}

		if ($listado)
			$result = $OpcionesTemplate->listado($OpcionesModel->obtenerGrupos());

		$this->visor->addComponent("ContentT", "content_title", $OpcionesTemplate->titulo());
		if ($result != "") $this->visor->addComponent("ContentB","content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF","content_footer", $result_f);	
	}
}
?>
