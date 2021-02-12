<?php

class FormAcuController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}
    
	function Run() {
	    	ob_start();
		include "reportes/m_formacu.php";
		include "reportes/m_formproces.php";
		include "reportes/t_formacu.php";
	
		$this->Init();

		$result 	= '';
		$result_t 	= '';
		$form_search 	= false;
		$listado 	= false;

		switch ($this->action) {

		    	case "Buscar":
					$listado = true;
					break;

			case "pdf":
					$resultado = FormAcuModel::Search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estaciones'], $_REQUEST['formulario'], $_REQUEST['modo']);
					FormAcuTemplate::reportePDF($resultado, $_REQUEST['desde'], $_REQUEST['hasta']);
					return;
			
			case "Excel":
					$resultado = FormAcuModel::search($_REQUEST['ch_almacen'], $_REQUEST['desde'], $_REQUEST['hasta']);
					$resultt   = FormAcuTemplate::reporteExcel($resultado, $_REQUEST['ch_almacen'], $_REQUEST['desde'], $_REQUEST['hasta']) ;
					break;

			default:
					$form_search = true;
					break;
		}
	
		if ($form_search) {
		    	$result = FormAcuTemplate::FormSearch();
		}

		if ($listado) {
		    	$resultado = FormAcuModel::Search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estaciones'], $_REQUEST['formulario'], $_REQUEST['modo']);
		    	$result_f  = FormAcuTemplate::Search($resultado, $_REQUEST['estaciones'], $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['formulario'], $_REQUEST['modo']);
		}
		
		$this->visor->addComponent("ContentT", "content_title", FormAcuTemplate::Titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
    	}
}
