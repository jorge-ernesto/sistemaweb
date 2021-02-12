<?php

class FormProcesController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }
    
    function Run()
    {
	include "reportes/m_formproces.php";
	include "reportes/t_formproces.php";

	$this->Init();
	$result = '';
	$form_search = false;
	$listado = false;
	
	switch($this->action) {
	    case "Buscar":
		$form_search = true;
		$listado = true;
		break;
	    default:
		$form_search=true;
		break;
	}
	
	if ($form_search) {
	    $result = FormProcesTemplate::formSearch();
	}
	if ($listado) {
	    $resultados = FormProcesModel::busqueda($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['formulario']);
	    $result_f = FormProcesTemplate::listado($resultados);
	}

	$this->visor->addComponent("ContentT", "content_title", FormProcesTemplate::Titulo());
	$this->visor->addComponent("ContentB", "content_body", $result);
	$this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

