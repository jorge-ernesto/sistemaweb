<?php

class RegistroVentasFEController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    	}
    
    	function Run() {

	    	ob_start();
		include 'reportes/m_registro_ventas_fe.php';
		include 'reportes/t_registro_ventas_fe.php';

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

		    	$tickets	= RegistroVentasFEModel::SearchTickets($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
				$documentos	= RegistroVentasFEModel::SearchDocumentos($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
				$result 	= RegistroVentasFETemplate::reportePDF($tickets, $documentos, $_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta']) ;
				$mi_pdf 	= "/sistemaweb/ventas_clientes/reportes/pdf/RegistroVentasFESeries.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'."RegistroVentasFESeries.pdf".'"');
				readfile($mi_pdf);
				break;
			
			case "Excel":

		    	$tickets	= RegistroVentasFEModel::SearchTickets($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
		    	$documentos	= RegistroVentasFEModel::SearchDocumentos($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
				$resultt   	= RegistroVentasFETemplate::reporteExcel($tickets, $documentos, $_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta']) ;
				break;

	    	default:
				$form_search = true;
				break;
		}
	
		if ($form_search)
			$result = RegistroVentasFETemplate::formSearch($_REQUEST['almacen'], date("d/m/Y"), date("d/m/Y"));

		if ($listado) {
			$result 	= RegistroVentasFETemplate::formSearch($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
	    	$tickets	= RegistroVentasFEModel::SearchTickets($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
	    	$documentos	= RegistroVentasFEModel::SearchDocumentos($_REQUEST['almacen'], $_REQUEST['fdesde'], $_REQUEST['fhasta'], $_REQUEST['type']);
	    	$docsEbi = RegistroVentasFEModel::getDocumentStatusByDateRange($_REQUEST);
	    	$result_f	= RegistroVentasFETemplate::listado($tickets, $documentos, $docsEbi);
		}

		$this->visor->addComponent("ContentT", "content_title", RegistroVentasFETemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);	

    	}

}
