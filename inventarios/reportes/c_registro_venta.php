<?php

class RegistroVentaController extends Controller {

    	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    	}
    
    	function Run() {
		include 'reportes/m_registro_venta.php';
		include 'reportes/t_registro_venta.php';
	
		$this->Init();	
		$result 	= "";
		$result_f 	= "";
		$form_search 	= false;
		$reporte 	= false;

		switch ($this->action) {
		
		    	case "Buscar":
				$reporte = true;
				break;
								
			case "PDF":
                            
				$resultado = RegistroVentaModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);			
				$result    = RegistroVentaTemplate::reportePDF($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['tipo_reporte']);
				$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/Registro_venta.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'."Registro_venta.pdf".'"');
				readfile($mi_pdf);
				break;
				
				
		    	default:
				$form_search = true;
		}

		if ($form_search) {
		    	$result = RegistroVentaTemplate::formSearch();
		}

		if ($reporte) {
                   
		    	$resultado = RegistroVentaModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);
		    	$result_f  = RegistroVentaTemplate::listado($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['tipo_reporte'], $_REQUEST['art_linea']);
		}

		$this->visor->addComponent("ContentT", "content_title", RegistroVentaTemplate::Titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
               
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    	}
}
