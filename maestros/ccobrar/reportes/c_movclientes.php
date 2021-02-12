<?php
  // Controlador del Modulo Generales

  Class MovClientesController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      //otros variables de entorno
    }

    function Run()
    {
      $this->Init();
      $result = '';
      include('reportes/m_movclientes.php');
      include('reportes/t_movclientes.php');
      $this->visor->addComponent('ContentT', 'content_title', MovClientesTemplate::titulo());
   
      switch ($this->request)
      {//task
      case 'CLIENTES':
		$tablaNombre = 'MOVIMIENTO DE CLIENTES';
		$listado = false;
		//evaluar y ejecutar $action
		switch ($this->action)
		{
			case 'Reporte':
				$result = MovClientesModel::ObtenerClientesSaldo($_REQUEST['cbmoneda'],$_REQUEST['fecinicio'],$_REQUEST['fecfin'],$_REQUEST['cbclientes'],$_REQUEST['txtcliente']);
				$record = MovClientesTemplate::formBuscar();
				$record .= MovClientesTemplate::tmlistadoReporte($result);
			   	$this->visor->addComponent("ContentB", "content_body", $record);
			   	$reporte = MovClientesTemplate::ReportePDF($result);
    			echo "$reporte";
		    break;
	    
		    default:
		    	$listado = true;
		    break;
		}
		if ($listado) 
		{
			$record = MovClientesTemplate::formBuscar();
		    $this->visor->addComponent("ContentB", "content_body", $record);
		}
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
