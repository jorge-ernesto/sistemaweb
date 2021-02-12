<?php

class ArticuloController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }
    
    function Run()
    {
	include 'helper/m_articulo.php';
	include 'helper/t_articulo.php';

	$this->Init();
	
	$result = "";
	$result_f = "";
	$form_search = false;
	$search = false;

	switch ($this->action)
	{
	    case "Search":
		$search = true;
		break;
	    default:
		$form_search = true;
		break;
	}
	
	if ($form_search) {
	    $result = ArticuloTemplate::formSearch($_REQUEST['dstname']);
	}

	if ($search) {
	    $articulos = ArticuloModel::search($_REQUEST['texto'], $_REQUEST['criterio']);
	    $result_f = ArticuloTemplate::listado($articulos, $_REQUEST['dstname']);
	}

	$this->visor->addComponent("ContentT", "content_title", ArticuloTemplate::titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

?>