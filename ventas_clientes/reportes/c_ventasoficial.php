<?php

class VentasOficialController extends Controller {

    	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'reportes/m_ventasoficial.php';
		include 'reportes/t_ventasoficial.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';
		$form_search = false;
		$listado = false;

		switch ($this->action) {
	    		case 'pdf':
				$results = VentasOficialModel::reporte($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['modo'], $_REQUEST['facturas'], $_REQUEST['descontar'], $_REQUEST['estacion']);			
				$listado	=false;
				break;

	    		case 'Reporte':
				$listado = true;
				break;

	    		case "SerieDocumento":
				$this->visor->addComponent("SpaceSeries", "space", VentasOficialTemplate::listaSeries());
				return;

	    		default:
				$form_search = true;
				break;
		}
	
		if ($form_search) {
	   		$result = VentasOficialTemplate::formSearch();
		}

		if ($listado) {
				// echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";
	    		$results = VentasOficialModel::reporte($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['modo'], $_REQUEST['facturas'], $_REQUEST['descontar'], $_REQUEST['estacion'], $_REQUEST['seriesdocs']);
	    		echo VentasOficialTemplate::reportePDF($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['modo'], $_REQUEST['impresion'], $_REQUEST['estacion']); // agregado estacion
	    		$result_f = VentasOficialTemplate::listado($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['modo'], $_REQUEST['facturas'], $_REQUEST['descontar'], $_REQUEST['impresion']);
	    
		}

		if ($result!='') $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    	}    
}
