<?php

class ImpresionesController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action']) ? $this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include 'facturacion/m_impresiones.php';
	include 'facturacion/t_impresiones.php';
	
	$this->Init();
	
	$result = '';
	$result_f = '';
	$form_search = false;

	switch ($this->action)
	{
	    default:
		$form_search = true;
		break;
	}
	
	if ($form_search) $result = ImpresionesTemplate::formSearch();
	
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

