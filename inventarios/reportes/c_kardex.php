<?php

class KardexController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run()
    {
	include 'reportes/m_kardex.php';
	include 'reportes/m_formproces.php';
	include 'reportes/t_kardex.php';
	
	$this->Init();
	
	$result = "";
	$result_f = "";
	$form_search = false;
	$reporte = false;

	switch ($this->action) {
	    case "Buscar":
		$reporte = true;
		break;
	    case "pdf":
		$resultado = KardexModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['art_hasta'], $_REQUEST['estacion']);
		$resulta = KardexTemplate::reportePDF($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['tipo_reporte']);
		$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/Kardex.pdf";
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="' . "Kardex.pdf" . '"');
                readfile($mi_pdf);
		break;
	    default:
		$form_search = true;
	}

	if ($form_search) {
	    $result = KardexTemplate::formSearch();
	}

	if ($reporte) {
	    $resultado = KardexModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['art_hasta'], $_REQUEST['estacion']);
	    $result_f = KardexTemplate::listado($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['art_hasta'], $_REQUEST['estacion'], $_REQUEST['tipo_reporte']);
	}

	$this->visor->addComponent("ContentT", "content_title", KardexTemplate::Titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

