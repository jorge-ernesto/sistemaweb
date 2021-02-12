<?php

class ConsistenciaController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }
    function Run()
    {
	include "reportes/m_consistencia.php";
	include "reportes/m_formproces.php";
	include "reportes/t_consistencia.php";
	
	$this->Init();

	$form_search = false;
	$listado = false;

	switch ($this->action) {
	    case "pdf":
		$this->generarPDF($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
		return;
	    case "Buscar":
		$listado = true;
		break;
	    default:
		$form_search = true;
		break;
	}

	if ($listado) {
	    $resultado = ConsistenciaModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
	    $result_f = ConsistenciaTemplate::listado($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
	}
	if ($form_search)
	    $result = ConsistenciaTemplate::formSearch();

	$this->visor->addComponent("ContentT", "content_title", ConsistenciaTemplate::Titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
    
    function generarPDF($desde, $hasta, $almacen)
    {
	$resultado = ConsistenciaModel::search($desde, $hasta, $almacen);
	echo ConsistenciaTemplate::OutputPDF($resultado, $desde, $hasta, $almacen);
    }
}

