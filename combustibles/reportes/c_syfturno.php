<?php
class SYFTurnoController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_syfturno.php';
		include 'reportes/t_syfturno.php';

		$this->Init();

		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;

		switch($this->action) {
			case "Procesar":
				$reporte = SYFTurnoModel::obtenerReporte($_REQUEST['dia1'],$_REQUEST['dia2'],$_REQUEST['tanque']);
				echo "<script>console.log('" . json_encode($reporte) . "')</script>";
				$result_f = SYFTurnoTemplate::presentarReporte($reporte);
				break;
			default:
				$tanques = SYFTurnoModel::obtenerTanques();
				$result = SYFTurnoTemplate::formReporte($tanques);
			break;
		}

		$this->visor->addComponent("ContentT", "content_title", SYFTurnoTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
