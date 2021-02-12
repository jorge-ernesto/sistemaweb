<?php
  // Controlador del Modulo Generales

  Class IntelecController extends Controller{
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
      include('maestros/m_intelec.php');
      include('maestros/t_intelec.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', IntelecTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      switch ($this->request){//task
      case 'INTELEC':
      //echo "ENTRO";
	$tablaNombre = 'INTELEC';
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action){
	    
	   	 case 'Agregar':
			$result = IntelecTemplate::formTrabajador(array());
			$this->visor->addComponent("ContentB", "content_body", $result);
			break;
	  	case 'Modificar':
			$record = IntelecModel::recuperarRegistroArray($_REQUEST["registroid"]);
			//print_r($record["razsocial"]);		    
			$result = IntelecTemplate::formTrabajador($record);
			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
		
		case 'Reporte':
			$result = IntelecModel::ModelReportePDF($_REQUEST["busqueda"]);
			$record .= IntelecTemplate::TemplateReportePDF($result);
			$this->visor->addComponent("ContentB", "content_body", $record);
		break;
		
	   	 case 'Eliminar':
                    
		   	 $result = IntelecModel::eliminarRegistro($_REQUEST["registroid"]);
		    if ($result == OK){
				$listado= true;
		    } else {
				$result = IntelecTemplate::errorResultado($result);
				$this->visor->addComponent("ContentB", "content_body", $result);
		    }
		    break;
    
		case 'TEXTO':
			$listado=false;
			break;
			
		case 'Guardar':
			
			$listado = false;
			
		
			if($_REQUEST['accion']=='actualizar'){
			$result = IntelecModel::actualizarRegistro($_REQUEST['registroid'],
							       $_REQUEST['trab']['dispositivo'],
							       $_REQUEST['trab']['tipo'],
							       $_REQUEST['trab']['sleep'],
							       $_REQUEST['trab']['maxsleep']);
			}
			else{
		    	$result = IntelecModel::guardarRegistro($_REQUEST['trab']['dispositivo'],
							       $_REQUEST['trab']['tipo'],
							       $_REQUEST['trab']['sleep'],
							       $_REQUEST['trab']['maxsleep']);
			//print_r($_REQUEST['ruc']);
				
			}
			if ($result!=''){
					$result = IntelecTemplate::errorResultado('ERROR: INTERFAZ YA EXISTENTE');
					$this->visor->addComponent("error", "error_body", $result);
				}else{
					
					$result = IntelecTemplate::formTrabajador(array());
					$this->visor->addComponent("ContentB", "content_body", $result);
					$result = IntelecTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS 					DATOS ');//'.$_REQUEST['trab']['codigo'].' !!!');
					$this->visor->addComponent("error", "error_body", $result);
				}		
		
		    break;
    
	    case 'Buscar':
		    //Listo
		    $busqueda = IntelecModel::tmListado($_REQUEST['busqueda'],$_REQUEST['rxp'],$_REQUEST[ 
			'pagina']);
		    //print_r($_REQUEST["busqueda"]["parametro"]);	
		    $result = IntelecTemplate::listado($busqueda['datos']);
		    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
		    break;
    
	    default:
		//echo 'prueba de report';
		   //listado
		   $listado = true;
		   break;
	}

		if ($listado) {
		    $listado   = IntelecModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = IntelecTemplate::formBuscar($listado['paginacion']);
		    $result   .= IntelecTemplate::listado($listado['datos']);
		    $this->visor->addComponent("ContentB", "content_body", $result);
		}
      //break;
  
      }
    }
  }

