<?php

class RepGuiaController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include "reportes/m_repguia.php";
	include "reportes/m_consistencia.php";
	include "reportes/m_formproces.php";
	include "reportes/t_repguia.php";
	include "reportes/t_consistencia.php";
	
	$this->Init();
	
	$result = "";
	$result_f = "";

	$form_search = false;
	$listado = false;
	
	switch ($this->action) {
	    case "Buscar":
		$listado = true;
		break;
	    default:
		$form_search = true;
		break;
	}
	
	if ($form_search) {
	    $result = RepGuiaTemplate::formSearch();
	}
	
	if ($listado) {
	    $movimientos = RepGuiaModel::search($_REQUEST['mov_tipdocuref'], $_REQUEST['mov_docurefe']);
	    $result_f = ConsistenciaTemplate::listado($movimientos, '', '', '', 1);
	}

	$this->visor->addComponent("ContentT", "content_title", RepGuiaTemplate::Titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

