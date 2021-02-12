<?php

class VtaDiariaController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}

    	function Run() {
	    	ob_start();
		include 'reportes/m_vtadiaria.php';
		include 'reportes/t_vtadiaria.php';  

		$this->Init();

		$ip 		= "";
		$result 	= "";
		$result_f 	= "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

        	switch($this->action) {
        	
			case "Buscar":
					$result    = VtaDiariaTemplate::formBuscar(@$_REQUEST['almacen'], @$_REQUEST['dia1'], @$_REQUEST['turno1'], @$_REQUEST['dia2'], @$_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);		
				    	$res       = VtaDiariaModel::busqueda($_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);	    	
				    	$result_f  = VtaDiariaTemplate::listado($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);
					break;
					
			case "Validar": 
					$res	   = VtaDiariaModel::validar($_REQUEST['conf'], $_REQUEST['vec_check'], $_REQUEST['val'],
										$_REQUEST['almacen'], $_REQUEST['dia'], $_REQUEST['turno']);	
					$result    = VtaDiariaTemplate::formBuscar($_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);		
				    	$res       = VtaDiariaModel::busqueda($_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);	    	
				    	$result_f  = VtaDiariaTemplate::listado($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);				
					break;
					
			case "PDF":
					$res       = VtaDiariaModel::busqueda($_SESSION['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);
					$result    = VtaDiariaTemplate::reportePDF($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2']) ;
					$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/VtaDiaria.pdf";
					header('Content-type: application/pdf');
					header('Content-Disposition: attachment; filename="'."VtaDiaria.pdf".'"');
					readfile($mi_pdf);
					break;
			
			case "Excel":
					$res       = VtaDiariaModel::busqueda($_SESSION['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);
					$resultt   = VtaDiariaTemplate::reporteExcel($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2']) ;
					break;

		    	default:
					$result    = VtaDiariaTemplate::formBuscar("", "");		
				    	//$res       = VtaDiariaModel::busqueda($_SESSION['almacen'], date("d/m/Y"), "", date("d/m/Y"), "", "", "");	    	
				    	//$result_f  = VtaDiariaTemplate::listado($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find']);
					break;
		}

		$this->visor->addComponent("ContentT", "content_title", VtaDiariaTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
