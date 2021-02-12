<?php
  // Controlador del Modulo Generales

  Class AgenteBuencontrController extends Controller{
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
      include('maestros/m_agente_buencontr.php');
      include('maestros/t_agente_buencontr.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', AgenteBuencontrTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }

      switch ($this->request)
      {//task
      case 'SUNAT_BUENCONTR':
      $listado = false;
	switch ($this->action)
	{
            case 'Actualizar':
            {
	       $result = AgenteBuencontrTemplate::formAgenteBuencontr();
	       $this->visor->addComponent("ContentB", "content_body", $result);
            }
            break;

	    case 'Importar':
	    $result = AgenteBuencontrModel::SubirArchivoTxt($_FILES['file_sunat']['tmp_name'],$_FILES['file_sunat']['name']);
	    if ($result == OK){
		$result = AgenteBuencontrTemplate::errorResultado($result);
		$this->visor->addComponent("error", "error_body", "La transaci&oacute;n fue un Exito.");
	    } else {
		$result = AgenteBuencontrTemplate::errorResultado($result);
		$this->visor->addComponent("error", "error_body", $result);
	    }
	    break;

	    case 'Buscar':
	    //Listo
	    $busqueda = AgenteBuencontrModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
	    $result = AgenteBuencontrTemplate::listado($busqueda['datos']);
	    
	    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    break;

	    default:
	       //$result = AgenteBuencontrTemplate::formAgenteBuencontr();
	       //$this->visor->addComponent("ContentB", "content_body", $result);
	       $listado = true;
	    break;
	}
	if ($listado) 
	{
	    $listado    = AgenteBuencontrModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
	    //print_r($listado);
	    $result     =  AgenteBuencontrTemplate::formBuscar($listado['paginacion']);
	    $result     .= AgenteBuencontrTemplate::listado($listado['datos']);
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
