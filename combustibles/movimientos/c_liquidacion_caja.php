<?php
class VarillasController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
		include 'movimientos/m_liquidacion_caja.php';
		include 'movimientos/t_liquidacion_caja.php';
	
		$this->Init();
	
		$result   = "";
		$result_f = "";

		$form_search = false;
		$listado     = false;

		switch ($this->action) { 
	    	case "Buscar":
				$listado = true;
				break;

		    default:
				$form_search = true;
				break;
		}
	
		if ($form_search) {
		    $result = VarillasTemplate::formSearch(date(d."/".m."/".Y, strtotime('-1 day')),$_REQUEST['estacion']);
		}

		if ($listado) {
			$result    	= VarillasTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['estacion']);
		    $resultado 	= VarillasModel::search($_REQUEST['fecha'], $_REQUEST['estacion']);
		    $movi		= VarillasModel::SearchMovement($_REQUEST['fecha'], $_REQUEST['estacion']);
		    $result_f  	= VarillasTemplate::listado($resultado, $movi);
		}

		$this->visor->addComponent("ContentT", "content_title", VarillasTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
