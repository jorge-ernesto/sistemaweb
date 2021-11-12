<?php
date_default_timezone_set('America/Lima');

class VentasDiariasController extends Controller{

	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}
    
	function Run(){
		ob_start();
		include 'reportes/m_ventasdiarias.php';
		include 'reportes/t_ventasdiarias.php';
	
		$this->Init();

		//get Model y Template
		$objModel = new VentasDiariasModel();
		$objTemplate = new VentasDiariasTemplate();
	
		$result = '';
		$result_f = '';
		$search_form = false;

		switch ($this->action) {
			case "Reporte":
				if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
				$results = $objModel->obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['unidad_medida'], $bResumido);
				$result_f = $objTemplate->reporte($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['modo'], $_REQUEST['estacion']);
			break;

	    	case "pdf":
				if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
				$results = $objModel->obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['unidad_medida'], $bResumido);
				$result_f = $objTemplate->reporte($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['modo'], $_REQUEST['estacion']);				
				$result_f .= $objTemplate->reportePDF($results, $_REQUEST['desde'], $_REQUEST['hasta']);
			break;

			case "Excel":
				$res = $objModel->obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['unidad_medida'], false);				
				$resultt = $objTemplate->reporteExcel($res, $_REQUEST['estacion'], $_REQUEST['desde'], $_REQUEST['hasta'] ) ;
				echo "<script>console.log('" . json_encode($res) . "')</script>";
			break;

			case "AsientosContablesSiigo":
				$res = $objModel->obtieneVentasSiigo($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], false);								
				echo "<script>console.log('" . json_encode($res) . "')</script>";
			break;

			default:
				$search_form = true;
			break;
		}

		if ($search_form) {
			$estaciones = $objModel->obtieneListaEstaciones();
			$result = $objTemplate->search_form($estaciones);
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->titulo());
		if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}

