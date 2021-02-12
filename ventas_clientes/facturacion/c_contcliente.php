<?php
  // Controlador del Modulo x

  Class ContClienteController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
    }

    function Run()
    {
      $this->Init();
      $result = '';
      include('facturacion/m_contcliente.php');
      include('facturacion/t_contcliente.php');
      $listado = false;     
      $this->visor->addComponent('ContentT', 'content_title', ContClienteTemplate::titulo());
      switch ($this->request)
      {//task
	      case 'CLIENTE':
	      	switch ($this->action){
	      		case 'Modificar':
	      			$result = ContClienteTemplate::formAgregar();
	      			$this->visor->addComponent("ContentB", "content_body", $result);
	      			break;
	      		case 'Guardar':
	      			$_REQUEST['datos']['fec_inicio']=$_REQUEST['fec_inicio'];
	      			$_REQUEST['datos']['fec_fin']=$_REQUEST['fec_fin'];
	      			if ($_REQUEST['datos']['lim_importe']==''){
	      				$_REQUEST['datos']['lim_importe']=0.00;
	      				$_REQUEST['datos']['sal_importe']=0.00;
	      			}
	      			if ($_REQUEST['datos']['lim_galones']==''){
	      				$_REQUEST['datos']['lim_galones']=0.00;
	      				$_REQUEST['datos']['sal_galones']=0.00;
	      			}
	      			
	      			$lh = ContClienteModel::guardar($_REQUEST['datos']);
	      			if ($lh==1)	$lh = "ERROR: EL ITEM YA HA SIDO ASIGNADO AL CLIENTE !!!";
	      			elseif ($lh==2)	$lh = "ERROR: EL ITEM NO ES UN COMBUSTIBLE !!!";
	      			elseif ($lh==3) $lh = "ERROR: NO PUEDE LLEVAR UN CONTROL DE TODOS LOS COMBUSTIBLES !!!";
	      			elseif ($lh==4) $lh ="ERROR: NO PUEDE LLEVAR UN CONTROL DE ESTE ITEM !!!";
	      			elseif ($lh==5) $lh ="ERROR: NO PUEDE LLEVAR UN CONTROL DE TODOS LOS COMBUSTIBLES !!!";
	      			elseif ($lh==6) $lh ="ERROR: NO PUEDE LLEVAR UN CONTROL DE ESTE ITEM !!!";
	      			elseif ($lh==100) $lh = "SE ACTUALIZO CON EXITO !!!";
	      			else $lh = "SE GRABO CON EXITO !!!!";
	      			if ($lh=="SE ACTUALIZO CON EXITO !!!"){
	      				$result = ContClienteTemplate::formBuscar();
	      				$lh = ContClienteModel::listado($_REQUEST['busqueda']);
	      				$result .= ContClienteTemplate::listado($lh['datos']);
	      				$this->visor->addComponent("ContentB", "content_body", $result);
	      			}else $this->visor->addComponent("error_body", "error_body", ContClienteTemplate::errorResultado($lh));
	      			break;
	      		case 'setArticulo':
	      			$result = ContClienteTemplate::setArticulo($_REQUEST["codigoart"]);
            		$this->visor->addComponent("desc_articulo", "desc_articulo", $result);
	      			break;
	      		case 'setRegistroCli':
	      			$result = ContClienteTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
            		$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
            		break;
            	case 'setRegistroCli2':
	      			$result = ContClienteTemplate::setRegistrosCliente2($_REQUEST["codigocli"]);
            		$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
            		break;
	      		case 'Buscar':
	      			$result = ContClienteTemplate::formBuscar();
	      			$lh = ContClienteModel::listado($_REQUEST['busqueda']);
	      			$result .= ContClienteTemplate::listado($lh['datos']);
	      			$this->visor->addComponent("ContentB", "content_body", $result);
	      			break;
	      		case 'Eliminar':
	      			$lh = ContClienteModel::eliminarCriterio($_REQUEST['codigo']);
	      			$result = ContClienteTemplate::formBuscar();
	      			$lh = ContClienteModel::listado($_REQUEST['busqueda']);
	      			$result .= ContClienteTemplate::listado($lh['datos']);
	      			$this->visor->addComponent("ContentB", "content_body", $result);
	      			break;
	      		case 'Agregar':
	      			$result = ContClienteTemplate::formAgregar();
	      			$this->visor->addComponent("ContentB", "content_body", $result);
	      			break;	
	      		default:
			       $listado = true;
			       break;
			}
			if ($listado) {
				$result = ContClienteTemplate::formBuscar();
				$this->visor->addComponent("ContentB", "content_body", $result);
			}
		    break;
	      
	      default:
	        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
	      break;
      }
    }
  }
