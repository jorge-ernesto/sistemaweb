<?php
  // Controlador del Modulo x

  Class PreciosController extends Controller{
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
      include('maestros/m_promedio.php');
      include('maestros/t_promedio.php');
      $this->visor->addComponent('ContentT', 'content_title', PreciosTemplate::titulo());
            
      switch ($this->request)
      {//task
      case 'PROMEDIO':
      	switch ($this->action)
		{

			case "Agregar":
				$result = PreciosTemplate::formAgregar();
				$listado = false;
				$this->visor->addComponent("ListadoB", "content_body", $result);
				break;
			case "Buscar":
				$datos = PreciosModel::tmListado($_REQUEST['busqueda']);
				$result = PreciosTemplate::formBuscar();
				$result .= PreciosTemplate::listado($datos['datos']);
				$listado = false;
				$this->visor->addComponent("ListadoB", "content_body", $result);
				break;
				
			case 'setRegistroCli':
				$result = PreciosTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
            	$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
          		break;
          		
          	case 'setRegistroArt':
				$result = PreciosTemplate::setRegistrosArticulos($_REQUEST["codigoart"]);
            	$this->visor->addComponent("desc_articulo", "desc_articulo", $result);
          		break;
          	
          	case "Regresar":
          		$datos = PreciosModel::tmListado($_REQUEST['busqueda']);
				$result = PreciosTemplate::formBuscar();
				$result .= PreciosTemplate::listado($datos['datos']);
				$listado = false;
				$this->visor->addComponent("ListadoB", "content_body", $result);
          		break;
          	
          	case "Modificar":
          		$result = PreciosTemplate::formAgregar();
				$listado = false;
				$this->visor->addComponent("ListadoB", "content_body", $result);
				break;      		
          		break;
          		
          	case "Grabar":
          		$grabar = PreciosModel::grabarRegistro($_REQUEST['pre_lista_precio'],$_REQUEST['cli_codigo'],$_REQUEST['art_codigo'],$_REQUEST['precio'],$_SESSION['auth_usuario']);
          		$_REQUEST['busqueda']['codigo']=$_REQUEST['art_codigo'];
          		$_REQUEST['busqueda']['radio']=$_REQUEST['pre_lista_precio'];
          		$datos = PreciosModel::tmListado($_REQUEST['busqueda']);
				$result = PreciosTemplate::formBuscar();
				$result .= PreciosTemplate::listado($datos['datos']);
				$listado = false;
				$this->visor->addComponent("ListadoB", "content_body", $result);
          		break;
            default:
		       $listado = true;
		    break;
		}
		if ($listado) 
		{	$result = PreciosTemplate::formBuscar();
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
