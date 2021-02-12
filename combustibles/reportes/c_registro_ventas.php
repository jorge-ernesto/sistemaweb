<?php

class RegistroVentasController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    	}
    
    	function Run() {

	    	ob_start();
		include 'reportes/m_registro_ventas.php';
		include 'reportes/t_registro_ventas.php';

		$this->Init();
	
		$result   	= "";
		$result_f 	= "";
		$search 	= false;
		$listado     	= false;

		switch ($this->action) { 

		    	case "Buscar":
					$listado = true;
					break;

			case "PDF":

				    	$tickets	= RegistroVentasModel::SearchTickets($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
				    	$documentos	= RegistroVentasModel::SearchDocumentos($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
					$result 	= RegistroVentasTemplate::reportePDF($tickets, $documentos, $_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta']) ;
					$mi_pdf 	= "/sistemaweb/ventas_clientes/reportes/pdf/RegistroVentasSeries.pdf";
					header('Content-type: application/pdf');
					header('Content-Disposition: attachment; filename="'."RegistroVentasSeries.pdf".'"');
					readfile($mi_pdf);
					break;
			
			case "Excel":

				    	$tickets	= RegistroVentasModel::SearchTickets($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
				    	$documentos	= RegistroVentasModel::SearchDocumentos($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
					$resultt   	= RegistroVentasTemplate::reporteExcel($tickets, $documentos, $_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta']) ;
					break;

		    	default:
					$form_search = true;
					break;
		}
	
		if ($form_search)
			$result = RegistroVentasTemplate::formSearch($_REQUEST['almacen'], date("d/m/Y"), date("d/m/Y"), "");

		if ($listado) {
			$result 	= RegistroVentasTemplate::formSearch($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
		    	$tickets	= RegistroVentasModel::SearchTickets($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
		    	$documentos	= RegistroVentasModel::SearchDocumentos($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
		    	$result_f	= RegistroVentasTemplate::listado($tickets, $documentos);
		}

		$this->visor->addComponent("ContentT", "content_title", RegistroVentasTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);	

    	}

}
