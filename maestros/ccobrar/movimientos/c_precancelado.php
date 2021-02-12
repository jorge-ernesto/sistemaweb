<?php
  // Controlador del Modulo Cuentas por Cobrar

  Class PrecanceladoController extends Controller{
    function Init(){
      //Verificar seguridad
      $_REQUEST['datos']['dt_fecha_precancelado']=$_REQUEST['dt_fecha_precancelado'];
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
      include('movimientos/m_precancelado.php');
      include('movimientos/t_precancelado.php');
      $this->visor->addComponent('ContentT', 'content_title', PrecanceladoTemplate::titulo());
          
      switch ($this->request)
      {//task
      case 'PRECANCELADO':
        
		switch ($this->action)
		{
			 case 'Mostrar':
			 	$record = PrecanceladoModel::recuperarRegistroporID($_REQUEST['registroid']);
			 	$result = PrecanceladoTemplate::formPrecancelar($record['datos'][0]);
			 	$this->visor->addComponent("ContentB", "content_body", $result);
			 	break;
			 	
	         case 'Buscar':
	            $listado = PrecanceladoModel::tmSeleccionaDocumento($_REQUEST['busqueda']['codigo']);
		  		$result .= PrecanceladoTemplate::listado($listado['datos']);
		  		$listado = false;
			    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
			 break;
	
	         case 'Precancelar':
	         	$resss = explode('/',$this->datos['dt_fecha_precancelado']);
	         	$this->datos['dt_fecha_precancelado'] = $resss[2].'/'.$resss[1].'/'.$resss[0];
	         	$precancelado = PrecanceladoModel::precancelarDocumento($this->datos);
				$listado = PrecanceladoModel::tmSeleccionaDocumento($_REQUEST['busqueda']['codigo']);
		  		$result  = PrecanceladoTemplate::formBuscar();
		  		$result .= PrecanceladoTemplate::listado($listado['datos']);
		  		$listado = false;
		  		$this->visor->addComponent("ContentB", "content_body", $result);
	         break;
	         
	         case 'Quitar':
	         	$quitar = PrecanceladoModel::quitarPrecancelado($_REQUEST['registroid']);
	         	$listado = PrecanceladoModel::tmSeleccionaDocumento($_REQUEST['busqueda']['codigo']);
		  		$result  = PrecanceladoTemplate::formBuscar();
		  		$result .= PrecanceladoTemplate::listado($listado['datos']);
		  		$listado = false;
		  		$this->visor->addComponent("ContentB", "content_body", $result);
	         	break;
	         
	         case 'Regresar':
	         	$listado = PrecanceladoModel::tmSeleccionaDocumento($_REQUEST['busqueda']['codigo']);
		  		$result  = PrecanceladoTemplate::formBuscar();
		  		$result .= PrecanceladoTemplate::listado($listado['datos']);
		  		$listado = false;
		  		//print_r('dfsfsd'.$_REQUEST['busqueda']['codigo']);
		    	$this->visor->addComponent("ContentB", "content_body", $result);
	         break;
	         
	         case 'setRegistroCli':
				   $result = PrecanceladoTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
			       $this->visor->addComponent("desc_cliente", "desc_cliente", $result);
			 break;
	         
		     default:
		        $listado = true;
		     break;
		}
		if ($listado) 
		{
		    $listado = PrecanceladoModel::tmSeleccionaDocumento($_REQUEST['busqueda']['codigo']);
		  	$result  = PrecanceladoTemplate::formBuscar();
		  	$result .= PrecanceladoTemplate::listado($listado['datos']);
		    $this->visor->addComponent("ContentB", "content_body", $result);
		}

      break;
      
  	  case 'PRECANCELADODET':
       //echo "ENTRO 2";
        switch($this->action)
        {
          case 'setRegistro'://Codigo CIIU
            $result = PrecanceladoTemplate::setRegistros($_REQUEST["codigo"]);
            $this->visor->addComponent("desc_series_doc", "desc_series_doc", $result);
          break;
        }
        break;
        
      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
