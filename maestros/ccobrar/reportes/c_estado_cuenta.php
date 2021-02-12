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

      $this->Init();
      $result = '';

      include('reportes/m_estado_cuenta.php');
      include('reportes/t_estado_cuenta.php');

      $this->visor->addComponent('ContentT', 'content_title', EstadoCuentaTemplate::titulo());

      switch ($this->request){//task
      	case 'ESTADOCUENTA':

		//$tablaNombre = 'TARJETAS MAGNETICAS';
		$listado = false;
		//evaluar y ejecutar $action

		switch ($this->action){			

			case 'Reporte':
				
				$fecha 		= 	trim($_REQUEST['busqueda']['fecha']);
				$todo  		= 	trim($_REQUEST['busqueda']['combo']);
				$codcliente	= 	trim($_REQUEST['busqueda']['codigo']);

				if($todo == '01'){			
				    	$result  = EstadoCuentaModel::ModelReportePDF($fecha);
					$record  = EstadoCuentaTemplate::formBuscar();
					$record .= EstadoCuentaTemplate::ReportePDF($result,$fecha);
					$this->visor->addComponent("ContentB", "content_body", $record);

				}else{
					$result  = EstadoCuentaModel::ModelReportePDFCLIENTE($fecha,$codcliente);
					$record  = EstadoCuentaTemplate::formBuscar();
					$record .= EstadoCuentaTemplate::ReportePDF($result,$fecha);
					$this->visor->addComponent("ContentB", "content_body", $record);
				}

		    	break;
	    
		    default:
		    $listado = true;
		    break;
		}

		if ($listado){
			$record = EstadoCuentaTemplate::formBuscar();
		    $this->visor->addComponent("ContentB", "content_body", $record);
		}
      	break;

      default:
      	$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;

     }

   }

}
