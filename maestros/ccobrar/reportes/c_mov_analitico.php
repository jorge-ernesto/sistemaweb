<?php

class TarjetasMagneticasController extends Controller {

	function Init() {

		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

	function Run() {
		include('reportes/m_mov_analitico.php');
		include('reportes/t_mov_analitico.php');
		include('../include/paginador_new.php');

		$this->Init();
		$result = '';
	      	$this->visor->addComponent('ContentT', 'content_title', TarjetasMagneticasTemplate::titulo());
	     
		switch ($this->request) {

			case 'ANALITICO':
				$tablaNombre = 'TARJETAS MAGNETICAS';
				$listado = false;

				switch ($this->action) {
		  			case 'Reporte':				
					    	$result  = TarjetasMagneticasModel::ModelReportePDF($_REQUEST['busqueda']);
						$record  = TarjetasMagneticasTemplate::formBuscar();
						$record .= TarjetasMagneticasTemplate::ReportePDF($result);
		 				$this->visor->addComponent("ContentB", "content_body", $record);
				    		break;
			    
					default:
					    	$listado = true;
						break;
				}
			if ($listado) {
				$record = TarjetasMagneticasTemplate::formBuscar();
				$this->visor->addComponent("ContentB", "content_body", $record);
			} 
			break;
	
	      		default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
		      		break;
	      		}
    	}
}
