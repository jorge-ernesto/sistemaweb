<?php

class TarjetasMagneticasController extends Controller {

	function Init() {
		$this->visor = new Visor();
		$this->task	= @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

	function Run() {
		include('reportes/m_mov_analitico.php');
		include('reportes/t_mov_analitico.php');

		$this->Init();
		$result = '';
	    $this->visor->addComponent('ContentT', 'content_title', TarjetasMagneticasTemplate::titulo());
	     
		switch ($this->request) {
			case 'ANALITICO':
				$listado = false;
				switch ($this->action) {
		  			case 'HTML':
						$record  = TarjetasMagneticasTemplate::formBuscar($_REQUEST['desde'], $_REQUEST['hasta'], "", "", "", "");
						$arrResult = TarjetasMagneticasModel::ModelReportePDF($_REQUEST['busqueda'], $_REQUEST['desde'], $_REQUEST['hasta']);
						$sTipoVista = $_REQUEST['busqueda']['modo'];
						$record .= TarjetasMagneticasTemplate::gridViewHTML($arrResult, $sTipoVista);
		 				$this->visor->addComponent("ContentB", "content_body", $record);
				    	break;

		  			case 'PDF':
						$record  = TarjetasMagneticasTemplate::formBuscar($_REQUEST['desde'], $_REQUEST['hasta']);
					    $arrResult  = TarjetasMagneticasModel::ModelReportePDF($_REQUEST['busqueda'], $_REQUEST['desde'], $_REQUEST['hasta']);
						$record .= TarjetasMagneticasTemplate::ReportePDF($arrResult, $_REQUEST['desde'], $_REQUEST['hasta']);
		 				$this->visor->addComponent("ContentB", "content_body", $record);
				    	break;
			    
					default:
					    $listado = true;
						break;
				}

				if ($listado) {
					$record = TarjetasMagneticasTemplate::formBuscar(date("d/m/Y"), date("d/m/Y"), "", "", "", "");
					$this->visor->addComponent("ContentB", "content_body", $record);
				}
			break;
	
      		default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
	      		break;
	    }
    }
}

