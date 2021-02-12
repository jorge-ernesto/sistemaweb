<?php
  // Controlador del Modulo Generales

  Class AgentePercepcionController extends Controller{
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
      include('maestros/m_agente_percepcion.php');
      include('maestros/t_agente_percepcion.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', AgentePercepcionTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }

      switch ($this->request)
      {//task
      case 'SUNAT_PERCEPCION':
      $listado = false;
	switch ($this->action)
	{

            case 'Actualizar':
            {
	       $result = AgentePercepcionTemplate::formAgentePercepcion();
	       $this->visor->addComponent("ContentB", "content_body", $result);
            }
            break;
            
	    case 'Importar':
	    $result = AgentePercepcionModel::SubirArchivoTxt($_FILES['file_sunat']['tmp_name'],$_FILES['file_sunat']['name']);
	    if ($result == OK){
		$this->visor->addComponent("error", "error_body", "La transaci&oacute;n fue un Exito.");
	    } else {
		$result = AgentePercepcionTemplate::errorResultado($result);
		$this->visor->addComponent("error", "error_body", $result);
	    }
	    break;
	    
	    case 'Buscar':
	    //Listo
	    $busqueda = AgentePercepcionModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
	    $result = AgentePercepcionTemplate::listado($busqueda['datos']);
	    
	    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    break;

	    default:
	       //$result = AgentePercepcionTemplate::formAgentePercepcion();
	       //$this->visor->addComponent("ContentB", "content_body", $result);
	       $listado = true;
	    break;
	}
	if ($listado) 
	{
	    $listado    = AgentePercepcionModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
	    //print_r($listado);
	    $result     =  AgentePercepcionTemplate::formBuscar($listado['paginacion']);
	    $result     .= AgentePercepcionTemplate::listado($listado['datos']);
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