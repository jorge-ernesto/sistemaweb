<?php

class StockController extends Controller
{
    function Init()
    {
	    $this->visor = new Visor();
	    isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }

    function Run()
    {
	    include 'reportes/m_stock.php';
	    include 'reportes/t_stock.php';

	    $this->Init();

	    $result = "";
	    $result_f = "";

	    $form_search = false;
	    $listado = false;

	    switch($this->action) {
	        default:
                $form_search = true;
                $listado = true;
		        break;
	    }

        if ($form_search) {
            $result = StockTemplate::formSearch();
        }

        if ($listado) {
            $resultados = StockModel::busqueda();
            $result_f = StockTemplate::listado($resultados);
        }

	    $this->visor->addComponent("ContentT", "content_title", StockTemplate::titulo());
	    if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	    if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
    
}


