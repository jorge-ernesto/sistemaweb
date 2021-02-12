<?php

class ValesFacturaController extends Controller {

	function Init() {
        	$this->visor = new Visor();
        	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
	}

	function Run() {

        	include 'reportes/m_vale_factura.php';
        	include 'reportes/t_vale_factura.php';

		$this->Init();
		$result		= "";
		$result_f	= "";
		$form_search	= false;
		$reporte	= false;

        	switch ($this->action) {

			case "Buscar":
				$reporte = true;
			break;

            		default:
                		$form_search = true;

		}

		if ($form_search) {
			$result = ValesFacturaTemplate::formSearch();
		}

		if ($reporte) {

			echo "cargando ";

            		$monto_vales_generados	= ValesFacturaModel::venta_vales($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
			$ano_mes		= $_REQUEST['ano'] . "-" . $_REQUEST['mes'];
			$result_f		= ValesFacturaTemplate::listado($monto_vales_generados);

		}

		$this->visor->addComponent("ContentT", "content_title", ValesFacturaTemplate::Titulo());

		if ($result != ""){
			$this->visor->addComponent("ContentB", "content_body", $result);
		}

		if ($result_f != ""){
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
		}
	}

}

