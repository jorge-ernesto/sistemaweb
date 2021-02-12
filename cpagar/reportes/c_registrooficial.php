<?php

class RegistroOficialController extends Controller
{
    function Init()
    {
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	$this->visor = new Visor();
    }
    
    function Run()
    {
	include 'reportes/m_registrooficial.php';
	include 'reportes/m_registro.php';
	include 'reportes/t_registrooficial.php';
	
	$this->Init();
	
	$form_search = false;
	$result = '';
	$result_f = '';

	switch ($this->action) {
	    case 'pdf':
		$results = RegistroOficialModel::reporte($_REQUEST['params']);
		RegistroOficialTemplate::reportePDF($results, $_REQUEST['params']);
		exit;
	    case 'Buscar':
		$results = RegistroOficialModel::reporte($_REQUEST['params']);
		$result_f = RegistroOficialTemplate::listado($results, $_REQUEST['params']);
		break;
	    default:
		$form_search = true;
		break;
	}
	
	if ($form_search) {
	    $result = RegistroOficialTemplate::formSearch();
	}
	
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

?>