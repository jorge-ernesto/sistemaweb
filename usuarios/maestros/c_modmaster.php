<?php

class ModMasterController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include "maestros/m_modmaster.php";
	include "maestros/t_modmaster.php";
	
	$this->Init();
	
	$result = "";
	$result_f = "";
	$listado = false;
	
	switch ($this->action) {
	    case "Modificar":
		$result_f = ModMasterTemplate::formModificar($_REQUEST['ch_modulo']);
		break;
	    case "DoModificar":
		ModMasterModel::modificarModulo($_REQUEST['ch_modulo'], $_REQUEST['ch_descripcion']);
		$result_f = " ";
		$listado = true;
		break;
	    case "DoAgregar":
		ModMasterModel::agregarModulo($_REQUEST['ch_modulo'], $_REQUEST['ch_descripcion']);
		$listado = true;
		break;
	    case "Agregar":
		$result_f = ModMasterTemplate::formAgregar();
		break;
	    case "Borrar":
		ModMasterModel::borrarModulos($_REQUEST['keys']);
	    default:
		$listado = true;
		$result_f = " ";
		break;
	}

	if ($listado) {
	    $result = ModMasterTemplate::listado();
	}

	$this->visor->addComponent("ContentT", "content_title", ModMasterTemplate::titulo());
	if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

