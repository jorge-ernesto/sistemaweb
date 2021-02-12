<?php

class ExVentasController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include 'reportes/m_existencias.php';
	include 'reportes/m_exventas.php';
	include 'reportes/t_exventas.php';

	$this->Init();

	$form_search = false;
	$listado = false;

	$result = "";
	$result_f = "";

	switch ($this->action) {
	    case "PDF":
		$resultados = ExVentasModel::obtenerReporte($_REQUEST['fecha'], $_REQUEST['dias']);
		ExVentasTemplate::reportePDF($resultados, $_REQUEST['fecha']);
		return;
	    case "search":
		$listado = true;
		break;
	    default:
		$form_search = true;
		break;
	}

	if ($form_search)
	    $result = ExVentasTemplate::formSearch();

	if ($listado) {
	    $resultados = ExVentasModel::obtenerReporte($_REQUEST['fecha'], $_REQUEST['dias']);
	    $result_f = ExVentasTemplate::listado($resultados, $_REQUEST['fecha'], $_REQUEST['dias']);
	}

	$this->visor->addComponent("ContentT", "content_title", ExVentasTemplate::titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

