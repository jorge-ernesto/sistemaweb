<?php

class TransferenciasController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']: $this->action = "";
    }
    
    function Run()
    {
	include "reportes/m_transferencias.php";
	include "reportes/t_transferencias.php";
	
	$this->Init();
	
	$result = '';
	$result_f = '';

	$form_search = false;
	$listado = false;

	switch ($this->action) {
	    case "pdf":
		$resultados = TransferenciasModel::search($_REQUEST['desde'], $_REQUEST['hasta'], false);
		TransferenciasTemplate::reportePDF($resultados, $_REQUEST['desde'], $_REQUEST['hasta']);
		exit;
	    case "Buscar":
		$listado = true;
		break;
	    default:
		$form_search = true;
		break;
	}
	
	if ($form_search) {
	    $result = TransferenciasTemplate::formSearch($resultados);
	}

	if ($listado) {
	    $resultados = TransferenciasModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['bActualizar']);
	    $result_f = TransferenciasTemplate::listado($resultados, $_REQUEST['desde'], $_REQUEST['hasta']);
	}

	$this->visor->addComponent("ContentT", "content_title", TransferenciasTemplate::Titulo());
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

