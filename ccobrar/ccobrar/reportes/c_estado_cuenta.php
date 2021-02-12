<?php
class EstadoCuentaController extends Controller{
    function Init(){
      //Verificar seguridad
      $_REQUEST['busqueda']['fecha'] = $_REQUEST['fecha'];
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      //otros variables de entorno
    }

    function Run(){
	  ob_start();
      $this->Init();
      $result = '';

      include('reportes/m_estado_cuenta.php');
      include('reportes/t_estado_cuenta.php');

      $this->visor->addComponent('ContentT', 'content_title', EstadoCuentaTemplate::titulo());

      switch ($this->request){//task
      	case 'ESTADOCUENTA':
		$listado = false;

		switch ($this->action){
			case 'EXCEL':
				$fecha 				= trim($_REQUEST['fecha']);
				$todo  				= trim($_REQUEST['busqueda']['combo']);
				$codcliente			= trim($_REQUEST['busqueda']['codigo']);
				$Nu_Tipo_Cliente	= trim($_REQUEST['busqueda']['Nu_Tipo_Cliente']);

				if($todo == '01'){			
				    $arrResult  = EstadoCuentaModel::ModelReportePDF($fecha, $Nu_Tipo_Cliente);
					$record  = EstadoCuentaTemplate::formBuscar($fecha);
					$record .= EstadoCuentaTemplate::gridViewEXCEL($arrResult);
					$this->visor->addComponent("ContentB", "content_body", $record);
				}else{
					$arrResult  = EstadoCuentaModel::ModelReportePDFCLIENTE($fecha, $codcliente, $Nu_Tipo_Cliente);
					$record  = EstadoCuentaTemplate::formBuscar($fecha);
					$record .= EstadoCuentaTemplate::gridViewEXCEL($arrResult);
					$this->visor->addComponent("ContentB", "content_body", $record);
				}
				break;
				
			case 'HTML':
				$fecha 				= trim($_REQUEST['fecha']);
				$todo  				= trim($_REQUEST['busqueda']['combo']);
				$codcliente			= trim($_REQUEST['busqueda']['codigo']);
				$Nu_Tipo_Cliente	= trim($_REQUEST['busqueda']['Nu_Tipo_Cliente']);

				if($todo == '01'){			
				    $arrResult  = EstadoCuentaModel::ModelReportePDF($fecha, $Nu_Tipo_Cliente);
					$record  = EstadoCuentaTemplate::formBuscar($fecha);
					$record .= EstadoCuentaTemplate::gridViewHTML($arrResult);
					$this->visor->addComponent("ContentB", "content_body", $record);
				}else{
					$arrResult  = EstadoCuentaModel::ModelReportePDFCLIENTE($fecha, $codcliente, $Nu_Tipo_Cliente);
					$record  = EstadoCuentaTemplate::formBuscar($fecha);
					$record .= EstadoCuentaTemplate::gridViewHTML($arrResult);
					$this->visor->addComponent("ContentB", "content_body", $record);
				}
				break;

			case 'PDF':
				$fecha 				= trim($_REQUEST['fecha']);
				$todo  				= trim($_REQUEST['busqueda']['combo']);
				$codcliente			= trim($_REQUEST['busqueda']['codigo']);
				$Nu_Tipo_Cliente	= trim($_REQUEST['busqueda']['Nu_Tipo_Cliente']);

				if($todo == '01'){			
				    $result  = EstadoCuentaModel::ModelReportePDF($fecha, $Nu_Tipo_Cliente);
					$record  = EstadoCuentaTemplate::formBuscar($fecha);
					$record .= EstadoCuentaTemplate::ReportePDF($result, $fecha);
					$this->visor->addComponent("ContentB", "content_body", $record);
				}else{
					$result  = EstadoCuentaModel::ModelReportePDFCLIENTE($fecha, $codcliente, $Nu_Tipo_Cliente);
					$record  = EstadoCuentaTemplate::formBuscar($fecha);
					$record .= EstadoCuentaTemplate::ReportePDF($result,$fecha);
					$this->visor->addComponent("ContentB", "content_body", $record);
				}
		    	break;
	    
		    default:
		    	$listado = true;
		    break;
		}

		if ($listado){
			$record = EstadoCuentaTemplate::formBuscar(date("d/m/Y"));
		    $this->visor->addComponent("ContentB", "content_body", $record);
		}
      	break;

      default:
      	$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;

     }

   }

}
