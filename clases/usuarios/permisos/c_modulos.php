<?php

class ModulosController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include 'permisos/m_modulos.php';
	include 'permisos/t_modulos.php';
	
	$this->Init();
	
	$listado = false;
	$result = "";
	$result_f = "";

	switch ($this->action) {
	    case "Borrar":
		ModulosModel::borrarAcceso($_REQUEST['keys']);
		$result_f = " ";
		$listado = true;
		break;
	    case "DoAgregar":
		ModulosModel::agregarAcceso($_REQUEST['addoption'], $_REQUEST['gid'], $_REQUEST['uid'], $_REQUEST['modulo']);
		$listado = true;
		break;
	    case "Agregar":
		$result_f = ModulosTemplate::formAgregar();
		break;
	    default:
		$listado = true;
		$result_f = " ";
		break;
	}

	if ($listado) {
	    $result = ModulosTemplate::listado();
	}

	$this->visor->addComponent("ContentT", "content_title", ModulosTemplate::titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

?>
