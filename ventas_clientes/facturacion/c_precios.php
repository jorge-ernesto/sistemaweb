<?php
  // Controlador del Modulo Generales

  Class PreciosController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
      //otros variables de entorno
    }

    function Run(){
      $this->Init();
      $result = '';
      
      include('../maestros/maestros/m_especiales.php');
      //include('../maestros/maestros/t_especiales.php');
      include("facturacion/t_precios.php");
      
      $this->visor->addComponent('ContentT', 'content_title', PreciosTemplate::titulo());
      $listado=true;
          
      switch ($this->request){
      	  case 'PRECIOS':
      	  	
	   			switch ($this->action){
				    
	   				
	   				case 'Ingresar':
	   					$existe = EspecialesModel::ValidarUsuario($_REQUEST['login'],$_REQUEST['clave']);
	   					print_r($existe);
	   					if (!$existe) {
	   						$result = PreciosTemplate::formLogin();
	   						$result.='No Existe el usuario';
	   						$this->visor->addComponent("ContentB", "content_body", $result);
	   						$listado=false;
	   					}
	   					elseif ($_REQUEST['login']=='SISTEMAS' || $_REQUEST['login']=='YCANEVARO') $listado=true;
	   					else {
	   						$result = PreciosTemplate::formLogin();
	   						$result.='Usted no esta autorizado a ver esta opcion';
	   						$this->visor->addComponent("ContentB", "content_body", $result);
	   						$listado=false;
	   					}
	   					break;
	   					
	   							
	   				case 'Buscar':
	   					//$listado=true;
	   					break;
	   				
	   				case 'Autorizar':
	   					foreach($_REQUEST['chk'] as $k =>$v){
	   						$registro = EspecialesModel::autorizarRegistros($v);
	   					}
	   					$valor=(isset($_REQUEST["paginacion"])?$_REQUEST["paginacion"]:$_REQUEST["busqueda"]);
	   					$listado = EspecialesModel::listadoSoloPendientes($valor);
				    	$result     = PreciosTemplate::formAutorizar();
				    	$result    .= PreciosTemplate::listado($listado['datos']);
				    	$this->visor->addComponent("ContentB", "content_body", $result);
				    	$listado=false;
	   					break;	
	   					
	   				case 'Modificar':
	   					$registro=EspecialesModel::getRegistroEspecialporDetalle($_REQUEST['registroid']);
			    		$result = PreciosTemplate::formModAutorizar($registro['datos']);
			    		$this->visor->addComponent("ContentB", "content_body", $result);
			    		$listado=false;
			    		break;
	   				
	   				case 'Regresar':
	   					$valor=(isset($_REQUEST["paginacion"])?$_REQUEST["paginacion"]:$_REQUEST["busqueda"]);
	   					$listado = EspecialesModel::listadoSoloPendientes(array('codigo'=>$valor));
				    	$result     = PreciosTemplate::formAutorizar();
				    	$result    .= PreciosTemplate::listado($listado['datos']);
				    	$this->visor->addComponent("ContentB", "content_body", $result);
				    	$listado=false;
	   					break;
	   				
	   				case 'Guardar':
	   					$result = EspecialesModel::guardar($this->datos);
			 			if($result!='OK'){
					       $result = PerciosTemplate::errorResultado($result);
					       $this->visor->addComponent("error", "error_body", $result);
					    }
			 	    	$valor=(isset($_REQUEST["paginacion"]['codigo'])?$_REQUEST["paginacion"]['codigo']:$_REQUEST["busqueda"]['codigo']);
			 	    	$listado = EspecialesModel::listadoSoloPendientes(array('codigo'=>$valor));
				    	$result = PreciosTemplate::formAutorizar();
				    	$result    .= PreciosTemplate::listado($listado['datos']);
				    	$this->visor->addComponent("ContentB", "content_body", $result);
			    		$listado = false;
	   					break;	
	   				
	   				case 'Eliminar':
	   					$result = EspecialesModel::eliminar();
				    	if($result!='OK'){
					       $result = EspecialesTemplate::errorResultado($result);
					       $this->visor->addComponent("error", "error_body", $result);
					    }
				    	$listado=true;	
	   					break;
	   					
	   				default:
	   					
	   					break;
				   
				}
				
				//if ($_SESSION['auth_usuario']=='MESPINOZA'){
					if ($listado) {
						$listado = EspecialesModel::listadoSoloPendientes($_REQUEST['busqueda']);
					   	$result     = PreciosTemplate::formAutorizar();
					    $result    .= PreciosTemplate::listado($listado['datos']);
	        			$this->visor->addComponent("ContentB", "content_body", $result);
					}
				//}
				
			    break;
			    
		  case 'PRECIOSDET':
		  		switch($this->action)
		        {
		          case 'setCodigo':
		            $result = PreciosTemplate::setRegistros($_REQUEST["codigo"]);
		            $this->visor->addComponent("desc_codigo", "desc_codigo", $result);
		          break;
		        }
		  		break;
     }
    }
  }
