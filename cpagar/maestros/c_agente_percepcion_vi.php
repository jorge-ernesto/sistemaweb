<?php
  // Controlador del Modulo Generales

  Class AgentePercepcionViController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
      //otros variables de entorno
    }

    function Run()
    {
      $this->Init();
      $result = '';
      include('maestros/m_agente_percepcion_vi.php');
      include('maestros/t_agente_percepcion_vi.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', AgentePercepcionViTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }

      switch ($this->request)
      {//task
      case 'SUNAT_PERCEPCION_VI':
      $listado = false;
	switch ($this->action)
	{
            case 'Actualizar':
            {
	       $result = AgentePercepcionViTemplate::formAgentePercepcionVi();
	       $this->visor->addComponent("ContentB", "content_body", $result);
            }
            break;

	    case 'Importar':
	    $result = AgentePercepcionViModel::SubirArchivoTxt($_FILES['file_sunat']['tmp_name'],$_FILES['file_sunat']['name']);
	    if ($result == OK){
		$this->visor->addComponent("error", "error_body", "La transaci&oacute;n fue un Exito.");
	    } else {
		$result = AgentePercepcionViTemplate::errorResultado($result);
		$this->visor->addComponent("error", "error_body", $result);
	    }
	    break;

	    case 'Buscar':
	    //Listo
	    $busqueda = AgentePercepcionViModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
	    $result = AgentePercepcionViTemplate::listado($busqueda['datos']);
	    
	    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    break;

	    default:
	    $listado = true;
	       //$result = AgentePercepcionViTemplate::formAgentePercepcionVi();
	       //$this->visor->addComponent("ContentB", "content_body", $result);
	    break;
	}
	if ($listado) 
	{
	    $listado    = AgentePercepcionViModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
	    //print_r($listado);
	    $result     =  AgentePercepcionViTemplate::formBuscar($listado['paginacion']);
	    $result     .= AgentePercepcionViTemplate::listado($listado['datos']);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	}

      break;
      case 'SUNATDET':
        //Si hay detalles
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
?>