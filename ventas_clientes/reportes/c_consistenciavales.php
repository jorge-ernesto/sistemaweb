<?php
  // Controlador del Modulo Generales

  Class ConsistenciaValesController extends Controller{
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
      include('reportes/m_consistenciavales.php');
      include('reportes/t_consistenciavales.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', ConsistenciaValesTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      switch ($this->request)
      {//task
      case 'DIAVALES':
		$tablaNombre = '';
		$listado = false;
		//evaluar y ejecutar $action
		switch ($this->action)
		{
			case 'Reporte':
			    $result = ConsistenciaValesModel::ModelReportePDF($_REQUEST['busqueda']);
				$record = ConsistenciaValesTemplate::formBuscar();
				$record .= ConsistenciaValesTemplate::TmpReportePDF($result);
				$this->visor->addComponent("ContentB", "content_body", $record);
		    break;
	    
		    default:
		    $listado = true;
		    break;
		}
		if ($listado) 
		{
			$record = ConsistenciaValesTemplate::formBuscar();
		    $this->visor->addComponent("ContentB", "content_body", $record);
		}
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
