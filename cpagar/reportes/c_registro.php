<?php

class RegistroController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include 'reportes/m_registro.php';
	include 'reportes/t_registro.php';
	
	$form_search = false;
	$result = '';
	$result_f = '';

	$this->Init();
	
	switch ($this->action) {
	    case "pdf":
		$results = RegistroModel::buscar($_REQUEST['params']);
		RegistroTemplate::reportePDF($results, $_REQUEST['params']);
		break;
	    case "Buscar":
		$results = RegistroModel::buscar($_REQUEST['params']);
		$result_f = RegistroTemplate::reporte($results, $_REQUEST['params']);
		break;
	    default:
		$form_search = true;
		break;
	}
	
	if ($form_search) {
	    $result = RegistroTemplate::formSearch();
	}
	
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

?>