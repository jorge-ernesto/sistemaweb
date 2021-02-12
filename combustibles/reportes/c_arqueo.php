<?php

class ArqueoController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    	}
    
    	function Run() {

	    	ob_start();
		include 'reportes/m_arqueo.php';
		include 'reportes/t_arqueo.php';

		$this->Init();
	
		$result   	= "";
		$result_f 	= "";
		$search 	= false;
		$listado     	= false;

		switch ($this->action) { 

		    	case "Buscar":
				$listado = true;
			break;

		    	default:
				$form_search = true;
			break;
		}
	
		if ($form_search)
			$result = ArqueoTemplate::formSearch($_REQUEST['almacen'], date("d/m/Y"));

		if ($listado) {
			$result 	= ArqueoTemplate::formSearch($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['type']);
			$turnosEfectivo	= ArqueoModel::SearchTurnosEfectivo($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['type']);
			$turnosCredito	= ArqueoModel::SearchTurnosTarjetaCreditos($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['type']);
		    	$tickets	= ArqueoModel::SearchTickets($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['type']);
		    	$documentos	= ArqueoModel::SearchDocumentos($_REQUEST['almacen'], $_REQUEST['fecha'], $_REQUEST['type']);
		    	$result_f	= ArqueoTemplate::ListadoRegistros($turnosEfectivo, $turnosCredito, $tickets, $documentos);
		}

		$this->visor->addComponent("ContentT", "content_title", ArqueoTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);	
    	}
}
