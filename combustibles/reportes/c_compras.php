<?php

date_default_timezone_set('UTC');

class ComprasController extends Controller{

	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}
    
	function Run(){

	    	ob_start();
		include "reportes/m_compras.php";
		include "reportes/t_compras.php";
	
		$search_form = false;
		$result = '';
		$result_f = '';

		$this->Init();
	
		switch ($this->action) {

	    		case "Buscar":
				$results 	= ComprasModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estaciones'], ($_REQUEST['detallado']=="SI"?true:false));
				$result_f 	= ComprasTemplate::listado($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado']);
			break;

			case "pdf":

				$results 	= ComprasModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estaciones'], ($_REQUEST['detallado']=="SI"?true:false));
				$result_f 	= ComprasTemplate::reportePDF($results);
				$mi_pdf 	= "/sistemaweb/ventas_clientes/reportes/pdf/Compras_Combustible.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'."Compras_Combustible.pdf".'"');
				readfile($mi_pdf);
			break;

	    		case "Buscar":
				$results 	= ComprasModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estaciones'], ($_REQUEST['detallado']=="SI"?true:false));
				$result_f 	= ComprasTemplate::listado($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado']);
			break;

			case "excel":
				$results 	= ComprasModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estaciones'], ($_REQUEST['detallado']=="SI"?true:false));
				$resultt   	= ComprasTemplate::reporteExcel($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estaciones'], $_REQUEST['detallado']);
			break;

	    		default:
				$search_form = true;
			break;
		}
	
		if ($search_form)
			$result = ComprasTemplate::form_search(date(d."/".m."/".Y), date(d."/".m."/".Y), $_REQUEST['estaciones']);

		$this->visor->addComponent("ContentT", "content_title", ComprasTemplate::titulo());
		if ($result!='') $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f!='') $this->visor->addComponent("ContentF", "content_footer", $result_f);
	
	}
    
}

