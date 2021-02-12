<?php

class GraficoVentasMensualesController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include 'reportes/m_graficoventasmensuales.php';
	include 'reportes/t_graficoventasmensuales.php';
	
	$this->Init();
	
	$result = '';
	$result_f = '';
	$search_form = false;

	switch ($this->action) {
	    case "Reporte":
		//if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
		$results = GraficoVentasMensualesModel::obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
		$result_f = GraficoVentasMensualesTemplate::reporte($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion']);
		break;
	    case "pdf":
		if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
		$results = GraficoVentasMensualesModel::obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $bResumido);
		GraficoVentasMensualesTemplate::reportePDF($results, $_REQUEST['desde'], $_REQUEST['hasta']);
		break;
	    default:
		$search_form = true;
		break;
	}

	if ($search_form) {
	    $result = GraficoVentasMensualesTemplate::search_form();
	}
	
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

