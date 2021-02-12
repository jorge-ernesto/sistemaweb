<?php

class SistemasController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include 'permisos/m_sistemas.php';
	include 'permisos/t_sistemas.php';
	
	$result = "";
	$result_f = "";
	$listado = false;

	$this->Init();
	
	switch ($this->action) {
	    case "DoAgregar":
		SistemasModel::agregarAcceso($_REQUEST['addoption'], $_REQUEST['gid'], $_REQUEST['uid'], $_REQUEST['sistema']);
		$listado = true;
		break;
	    case "Agregar":
		$result_f = SistemasTemplate::agregar();
		break;
	    case "Borrar":
		SistemasModel::borrarAcceso($_REQUEST['keys']);
		$listado = true;
		break;
	    default:
		$listado = true;
		$result_f = " ";
		break;
	}

	if ($listado) {
	    $result = SistemasTemplate::listado();
	}

	$this->visor->addComponent("ContentT", "content_title", SistemasTemplate::titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}


