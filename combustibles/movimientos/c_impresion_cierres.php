<?php

class ImpresionCierresController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'movimientos/m_impresion_cierres.php';
		include 'movimientos/t_impresion_cierres.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';
		$search_form = false;

		switch ($this->action) {

			case "Mostrar":
				$resultados = ImpresionCierresModel::obtenerComandoImprimir($_REQUEST['sucursal'], $_REQUEST['dia'], $_REQUEST['opcion']);
				$result_f = ImpresionCierresTemplate::reporte($resultados);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
                		break;
		
			case "Imprimir":                		
				$file = $_REQUEST['file'];
				echo "para imprimir: ".$file;		
				exec($file);
                		break;

		    	default:
				$result = ImpresionCierresTemplate::search_form(date(d."/".m."/".Y));	
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;
		}		
	}
}
