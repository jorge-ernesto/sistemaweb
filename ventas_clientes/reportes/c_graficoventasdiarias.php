<?php

class GraficoVentasDiariasController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include 'reportes/m_graficoventasdiarias.php';
	include 'reportes/t_graficoventasdiarias.php';
	
	$this->Init();
	
	$result = '';
	$result_f = '';
	$search_form = false;

	switch ($this->action) {
	    case "Reporte":
		//if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
		$results = GraficoVentasDiariasModel::obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
		$result_f = GraficoVentasDiariasTemplate::reporte($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
		break;
	    case "pdf":
		if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
		$results = GraficoVentasDiariasModel::obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $bResumido);
		GraficoVentasDiariasTemplate::reportePDF($results, $_REQUEST['desde'], $_REQUEST['hasta']);
		break;
	    default:
		$search_form = true;
		break;
	}

	if ($search_form) {
	    $result = GraficoVentasDiariasTemplate::search_form();
	}
	
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

