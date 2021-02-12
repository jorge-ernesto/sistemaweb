<?php

class GraficoVentasHorasController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include 'reportes/m_graficoventashoras.php';
	include 'reportes/t_graficoventashoras.php';
	
	$this->Init();
	
	$result = '';
	$result_f = '';
	$search_form = false;

	switch ($this->action) {
	    case "Reporte":
		if (substr($_REQUEST['desde'],6,4) . substr($_REQUEST['desde'],3,2) != substr($_REQUEST['hasta'],6,4) . substr($_REQUEST['hasta'],3,2)){
			$result_f = "<script>alert('Debe ingresar un rango de fechas del mismo mes y anio');</script>";
		} else {
			$results = GraficoVentasHorasModel::obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['tipo'], $_REQUEST['dia'], $_REQUEST['producto'], $_REQUEST['lado']);
			$result_f = GraficoVentasHorasTemplate::reporte($results, $_REQUEST['desde'], $_REQUEST['hasta']);
		}
		break;
	    case "pdf":
		if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
		$results = GraficoVentasHorasModel::obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['estacion'], $_REQUEST['tipo']);
		GraficoVentasHorasTemplate::reportePDF($results, $_REQUEST['desde'], $_REQUEST['hasta']);
		break;
	    default:
		$search_form = true;
		break;
	}

	if ($search_form) {
	    $result = GraficoVentasHorasTemplate::search_form();
	}
	
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

