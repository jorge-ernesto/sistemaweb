<?php

class TransDetController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }
    function Run()
    {
	include ('reportes/m_transdet.php');
	include ('reportes/t_transdet.php');
	include ('reportes/m_formproces.php');

	$this->Init();

	$result = '';
	$result_f = '';
	$form_search = false;
	$listado = false;

	switch ($this->action) {
	    case "pdf":
		$this->generarPDF($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['costos']);
		return;
	    case "Generar":
		$listado = true;
		break;
	    default:
		$form_search = true;
		break;
	}

	if ($listado) {
	    $resultado = TransDetModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['costos']);
	    $result_f = TransDetTemplate::listado($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['costos']);
	}
	if ($form_search)
	    $result = TransDetTemplate::formSearch();

	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
    
    function generarPDF($desde, $hasta, $almacen, $costos)
    {
	$resultado = TransDetModel::search($desde, $hasta, $almacen, $costos);
	TransDetTemplate::reportePDF($resultado, $desde, $hasta, $almacen);
    }
}

