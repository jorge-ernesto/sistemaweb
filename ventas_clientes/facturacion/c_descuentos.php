<?php
  // Controlador del Modulo x

  Class DescuentosController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
      //otras variables de entorno
    }

    function Run()
    {
      $this->Init();
      $result = '';
      include('facturacion/m_descuentos.php');
      include('../maestros/maestros/m_especiales.php');
      include('facturacion/t_descuentos.php');
      $this->visor->addComponent('ContentT', 'content_title', DescuentosTemplate::titulo());
            
      switch ($this->request)
      {//task
      case 'DESCUENTOS':
      	switch ($this->action)
		{
		  case 'setRegistroCli':
            	$result = DescuentosTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
            	$this->visor->addComponent("desc_codigo", "desc_codigo", $result);
          		break;
          		
          case 'setRegistroDesc':
            	$result = DescuentosTemplate::setRegistrosDesc($_REQUEST["codigodesc"]);
            	$this->visor->addComponent("desc_descuento", "desc_descuento", $result);
          		break;
		   	
	      case 'Autorizar':
	      		print_r($_REQUEST['busqueda']);
	   			foreach($_REQUEST['chk'] as $k =>$v){
	   				$registro = DescuentosModel::autorizarRegistros($v);
	   			}
	   			$lh = DescuentosModel::listadoSoloenEspera($_REQUEST['busqueda']);
		    	$result     = DescuentosTemplate::formAutorizar();
		    	$result .= DescuentosTemplate::listado($lh['datos']);
	        	$this->visor->addComponent("ContentB", "content_body", $result);
				$listado=false;
	   			break;
          			
		    default:
		       $listado = true;
		    break;
		    
		    case 'Modificar':
		    	$result = DescuentosTemplate::formEditar();
		    	$this->visor->addComponent("ContentB", "content_body", $result);
				$listado=false;
		    	break;
		    	
		    case 'Cambiar':
		    	$rs=DescuentosModel::cambiarDescuento($_REQUEST['codigo'],$_REQUEST['descuento']);
		    	$listado=true;
		    	break;
		}
		if ($listado) {
			$lh = DescuentosModel::listadoSoloenEspera($_REQUEST['busqueda']);
		    $result     = DescuentosTemplate::formAutorizar();
		    $result .= DescuentosTemplate::listado($lh['datos']);
	        $this->visor->addComponent("error", "error_body", " ");
		    $this->visor->addComponent("ContentB", "content_body", $result);
		}

      break;
      
      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
