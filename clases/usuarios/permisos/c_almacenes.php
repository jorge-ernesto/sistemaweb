<?php

class AlmacenesController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'permisos/m_almacenes.php';
		include 'permisos/t_almacenes.php';
	
		$this->Init();
	
		$listado  = false;
		$result   = "";
		$result_f = "";

		switch ($this->action) {

		    	case "Borrar":
					AlmacenesModel::borrarAcceso($_REQUEST['keys']);
					$listado = true;
					$result_f = " ";
					break;

		    	case "DoAgregar":
					AlmacenesModel::agregarAcceso($_REQUEST['addoption'], $_REQUEST['gid'], $_REQUEST['uid'], $_REQUEST['almacen']);
					$listado = true;
					break;

		    	case "Agregar":
					$result_f = AlmacenesTemplate::agregar();
					break;

		    	default:
					$listado = true;
					$result_f = " ";
					break;
		}

		if ($listado) {
	    		$result = AlmacenesTemplate::listado();
		}

		$this->visor->addComponent("ContentT", "content_title", AlmacenesTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    	}
}
