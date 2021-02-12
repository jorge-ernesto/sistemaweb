<?php

class ReimpresionController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	$this->Init();
	
	include('facturacion/m_reimpresion.php');
	include('facturacion/t_reimpresion.php');
	
	$form_search = false;
	$result = '';
	$result_f = '';

	switch ($this->action)
	{
	    case 'Procesar':
		ReimpresionModel::procesarDocumento($_REQUEST['ch_tipodocumento'], $_REQUEST['ch_seriedocumento'], $_REQUEST['ch_numerodocumento']);
		break;
	    default:
		$form_search = true;
		break;
	}

	if ($form_search) {
	    $result = ReimpresionTemplate::formSearch();
	}
	
	if ($result!='') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result!='') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

