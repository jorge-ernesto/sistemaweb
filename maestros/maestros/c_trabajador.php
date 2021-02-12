<?php
  // Controlador del Modulo Generales

  Class TrabajadorController extends Controller{
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
      include('maestros/m_trabajador.php');
      include('maestros/t_trabajador.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', TrabajadorTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      switch ($this->request){//task
      case 'TRABAJADOR':
      //echo "ENTRO";
	$tablaNombre = 'TRABAJADOR';
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action){
	    
	   	 case 'Agregar':
			$result = TrabajadorTemplate::formTrabajador(array());
			$this->visor->addComponent("ContentB", "content_body", $result);
			break;

	  	case 'Modificar':
			$record = TrabajadorModel::recuperarRegistroArray($_REQUEST["registroid"]);
			//print_r($record["razsocial"]);		    
			$result = TrabajadorTemplate::formTrabajador($record);
			$this->visor->addComponent("ContentB", "content_body", $result);
		break;
		
		case 'Reporte':
			$result = TrabajadorModel::ModelReportePDF($_REQUEST["busqueda"]);
			$record .= TrabajadorTemplate::TemplateReportePDF($result);
			$this->visor->addComponent("ContentB", "content_body", $record);
		break;
		
	   	 case 'Eliminar':
                    
		   	 $result = TrabajadorModel::eliminarRegistro($_REQUEST["registroid"]);
		    if ($result == OK){
				$listado= true;
		    } else {
				$result = TrabajadorTemplate::errorResultado($result);
				$this->visor->addComponent("ContentB", "content_body", $result);
		    }
		    break;
    
		case 'TEXTO':
			$listado=false;
			break;
			
		case 'Guardar':
			$listado = false;
			if($_REQUEST['accion']=='actualizar'){
				$result = TrabajadorModel::actualizarRegistro(
					strtoupper($_REQUEST['trab']['codigo']),
					strtoupper($_REQUEST['trab']['nombre']),
					strtoupper($_REQUEST['trab']['nombre2']),
					strtoupper($_REQUEST['trab']['apepat']),
					strtoupper($_REQUEST['trab']['apemat']),
					strtoupper($_REQUEST['trab']['sexo']),
					strtoupper($_REQUEST['trab']['direccion']),
					strtoupper($_REQUEST['trab']['telefono']),
					strtoupper($_REQUEST['trab']['dni']),
					strtoupper($_REQUEST['fechaNac']),
					strtoupper($_REQUEST['trab']['s_estado_trabajador'])
				);
			} else {
		    	$result = TrabajadorModel::guardarRegistro(
		    		strtoupper($_REQUEST['trab']['codigo']),
					strtoupper($_REQUEST['trab']['nombre']),
					strtoupper($_REQUEST['trab']['nombre2']),
					strtoupper($_REQUEST['trab']['apepat']),
					strtoupper($_REQUEST['trab']['apemat']),
					strtoupper($_REQUEST['trab']['sexo']),
					strtoupper($_REQUEST['trab']['direccion']),
					strtoupper($_REQUEST['trab']['telefono']),
					strtoupper($_REQUEST['trab']['dni']),
					strtoupper($_REQUEST['fechaNac']),
					strtoupper($_REQUEST['trab']['s_estado_trabajador'])
				);
			}

			if ($result!=''){
				$result = TrabajadorTemplate::errorResultado('ERROR: TRABAJADOR YA EXISTENTE');
				$this->visor->addComponent("error", "error_body", $result);
			}else{
				$result = TrabajadorTemplate::formTrabajador(array());
				$this->visor->addComponent("ContentB", "content_body", $result);
				$result = TrabajadorTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS 					DATOS '.$_REQUEST['trab']['codigo'].' !!!');
				$this->visor->addComponent("error", "error_body", $result);
			}
			break;
    
	    case 'Buscar':
		    //Listo
		    $busqueda = TrabajadorModel::tmListado($_REQUEST['busqueda'],$_REQUEST['rxp'],$_REQUEST[ 
			'pagina']);
		    //print_r($_REQUEST["busqueda"]["parametro"]);	
		    $result = TrabajadorTemplate::listado($busqueda['datos']);
		    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
		    break;
    
	    default:
		//echo 'prueba de report';
		   //listado
		   $listado = true;
		   break;
	}

		if ($listado) {
		    $listado   = TrabajadorModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
		    $result    = TrabajadorTemplate::formBuscar($listado['paginacion']);
		    $result   .= TrabajadorTemplate::listado($listado['datos']);
		    $this->visor->addComponent("ContentB", "content_body", $result);
		}
      //break;
  
      }
    }
  }

?>
