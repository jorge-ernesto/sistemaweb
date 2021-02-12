<?php
  // Controlador del Modulo Generales

  Class EspecialesController extends Controller{
    function Init(){
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
    }

    function Run()
    {
      $this->Init();
      $result = '';
    
      include('maestros/m_especiales.php');
      include('maestros/t_especiales.php');
      
   
      switch ($this->request)
      {
      	case 'ESPECIALES':
    
			switch ($this->action){
			    
			    case 'Agregar':
			    	print_r($_REQUEST['busqueda']);
				    $result = EspecialesTemplate::formModificar(array('ch_codigo_cliente_grupo'=>$_REQUEST['busqueda']['codigo']));
	    			$this->visor->addComponent("ContentB", "content_body", $result);
				    break;
		    
			    case 'Modificar':
			    	$registro=EspecialesModel::getRegistroEspecialporDetalle($_REQUEST['registroid']);
			    	$result = EspecialesTemplate::formModificar($registro['datos']);
			    	$this->visor->addComponent("ContentB", "content_body", $result);
			    	break;
		    
			    case 'Buscar':
				    $busqueda = EspecialesModel::tmListado($_REQUEST["busqueda"]);
				    $result = EspecialesTemplate::listado($busqueda['datos']);
				    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
				    
				    break;
		    				    
			    case 'Guardar':
			    	$result = EspecialesModel::guardar($this->datos);
		 			if($result!='OK'){
				       $result = EspecialesTemplate::errorResultado($result);
				       $this->visor->addComponent("error", "error_body", $result);
				    }
		 	    	$valor=(isset($_REQUEST["paginacion"]['codigo'])?$_REQUEST["paginacion"]['codigo']:$_REQUEST["busqueda"]['codigo']);
		 	    	$listado = EspecialesModel::tmListado(array('codigo'=>$valor));
			    	$result = EspecialesTemplate::formRegistrar();
			    	$result    .= EspecialesTemplate::listado($listado['datos']);
			    	$this->visor->addComponent("ContentB", "content_body", $result);
		    		$listado = false;
			    	break;
			    
			    case 'Eliminar':
			    	$result = EspecialesModel::eliminar();
			    	if($result!='OK'){
				       $result = EspecialesTemplate::errorResultado($result);
				       $this->visor->addComponent("error", "error_body", $result);
				    }
				    $listado = EspecialesModel::tmListado($_REQUEST['busqueda']);
			    	$result = EspecialesTemplate::formRegistrar();
			    	$result    .= EspecialesTemplate::listado($listado['datos']);
			    	$this->visor->addComponent("ContentB", "content_body", $result);
				    $listado = false;
				   	break;
				   					   	
			    case 'Mantenimiento':
			    	$this->visor->addComponent('ContentT', 'content_title', EspecialesTemplate::titulo('INGRESAR PRECIOS ESPECIALES'));
			    	$result = EspecialesTemplate::formRegistrar();
			    	$this->visor->addComponent("ContentB", "content_body", $result);
			    	$listado=false;
			    	break;
			    
			    case 'Volver':
			    	$this->visor->addComponent('ContentT', 'content_title', EspecialesTemplate::titulo('INGRESAR PRECIOS ESPECIALES'));
			    	$valor=(isset($_REQUEST["paginacion"])?$_REQUEST["paginacion"]:$_REQUEST["busqueda"]);
			    	if ($valor['codigo']!=''){
			    		$listado = EspecialesModel::tmListado($valor);
			    	}
					$result = EspecialesTemplate::formRegistrar();
					$result    .= EspecialesTemplate::listado($listado['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
			    	$listado=false;
			    	break;	
			    	
			    case 'Regresar':
			    	$valor=(isset($_REQUEST["paginacion"])?$_REQUEST["paginacion"]:$_REQUEST["busqueda"]);
			    	if ($valor != ''){
			    		$listado = EspecialesModel::tmListado(array('codigo'=>$valor));
			    	}
			    	$result = EspecialesTemplate::formRegistrar();
			    	$result    .= EspecialesTemplate::listado($listado['datos']);
			    	$this->visor->addComponent("ContentB", "content_body", $result);
			    	$listado=false;
			    	break;
			    		
			    case 'Reporte':
			    	$this->visor->addComponent('ContentT', 'content_title', EspecialesTemplate::titulo('REPORTE DE PRECIOS ESPECIALES'));
			    	$valor=(isset($_REQUEST["paginacion"])?$_REQUEST["paginacion"]:$_REQUEST["busqueda"]);
              		$reporte = EspecialesModel::tmListado($valor);
              		$this->visor->addComponent("ListadoB", "resultados_grid",EspecialesPDFTemplate::reporte($reporte['datos']));
			    	break; 
			    	
			    case 'Detallar':
			    	$valor=(isset($_REQUEST["paginacion"])?$_REQUEST["paginacion"]:$_REQUEST["busqueda"]);
			    	if ($valor['codigo']!=''){
			    		$listado = EspecialesModel::tmListado($valor);
			    	}
					$result = EspecialesTemplate::formRegistrar();
					$result    .= EspecialesTemplate::listado($listado['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
			    	$listado=false;
			    	break;
			    		  	
			    default:
			       
			    	$this->visor->addComponent('ContentT', 'content_title', EspecialesTemplate::titulo('REPORTE DE PRECIOS ESPECIALES'));
			  	    $listado = true;
				    break;
			}
			if ($listado) 
			{
				//$this->visor->addComponent('ContentT', 'content_title', EspecialesTemplate::titulo('INGRESAR PRECIOS ESPECIALES'));
				$valor=(isset($_REQUEST["paginacion"])?$_REQUEST["paginacion"]:$_REQUEST["busqueda"]);
				$listado    = EspecialesModel::tmListado($valor);
				$result     = EspecialesTemplate::formBuscar();
	    		$result    .= EspecialesTemplate::listado($listado['datos']);
        		$this->visor->addComponent("ContentB", "content_body", $result);
			}
     	break;
     	
	      case 'ESPECIALESDET':
	      
		        switch($this->action)
		        {
		          case 'setCodigo':
		             $result = EspecialesTemplate::setRegistros($_REQUEST["codigo"]);
		            $this->visor->addComponent("desc_codigo", "desc_codigo", $result);
		          break;
		          
		          case 'setArticulo':
		          	$result = EspecialesTemplate::setArticulo($_REQUEST["codigo"]);
		            $this->visor->addComponent("desc_articulo", "desc_articulo", $result);
		          break;
		                    
			     default:
		        
		          break;
		        }
	      break;

	      default:
	        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
	      	break;
      }
    }
  }
