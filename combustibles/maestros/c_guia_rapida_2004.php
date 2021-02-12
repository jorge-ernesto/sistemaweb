<?php
  // Controlador del Modulo Generales

  Class GuiaRapidaController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      //otros variables de entorno
    }

    function Run(){
      $this->Init();
      $result = '';
      include('maestros/m_guia_rapida_2004.php');
      include('maestros/t_guia_rapida_2004.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', GuiaRapidaTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']){
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      $this->request="GUIARAPIDA";
      switch ($this->request)
      {//task
      case 'GUIARAPIDA':
      //echo "ENTRO";
	
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action)
	{
	
    	    case 'Buscar':
	    		$busqueda = GuiaRapidaModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
	    		$result = GuiaRapidaTemplate::listado($busqueda['datos']);
	    		$this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    		break;
    
	    default:
		    	$listado = true;
		    	break;
	}
	if ($listado) 
	{
	    $listado    = GuiaRapidaModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
	    $result     =  GuiaRapidaTemplate::formBuscar($listado['paginacion']);
	    $result     .= GuiaRapidaTemplate::listado($listado['datos']);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	}
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
