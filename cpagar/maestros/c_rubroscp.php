<?php
  // Controlador del Modulo Generales

  Class RubrosCPController extends Controller{
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
      include('maestros/m_rubroscp.php');
      include('maestros/t_rubroscp.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', RubrosCPTemplate::titulo());
      if(!$_REQUEST['rxp'] && !$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      switch ($this->request)
      {//task
      case 'RUBROSCP':
      //echo "ENTRO";
	$listado = false;
	//evaluar y ejecutar $action
	switch ($this->action)
	{
	    
	    case 'Agregar':
	    $result = RubrosCPTemplate::formRubrosCP(array());
	    $this->visor->addComponent("ContentB", "content_body", $result);
	    break;
    
	    case 'Eliminar':
	    $result = RubrosCPModel::eliminarRegistro($_REQUEST["registroid"]);
	    if ($result == OK){
		$listado= true;
	    } else {
		$result = RubrosCPTemplate::errorResultado($result);
		$this->visor->addComponent("ContentB", "content_body", $result);
	    }
	    break;
    
	    case 'Modificar':
	    $record = RubrosCPModel::recuperarRegistroArray($_REQUEST["registroid"]);
	    //print_r($registrosXml);
	    $result = RubrosCPTemplate::formRubrosCP($record);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	    break;
    
	    case 'Guardar':
	    $result = RubrosCPModel::guardarRegistro($this->datos);
	    $listado = true;
	    break;
    
	    case 'Buscar':
	    //Listo
      $busqueda = RubrosCPModel::tmListado($_REQUEST["busqueda"],$_REQUEST['rxp'],$_REQUEST['pagina']);
      
      echo "<pre>";
      print_r($busqueda);
      echo "</pre>";
      die();
      
	    $result = RubrosCPTemplate::listado($busqueda['datos']);
	    
	    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    break;
    
	    default:
	    //listado
	    $listado = true;
	    unset($_SESSION['CUENTAS']);
	    unset($_SESSION['TOTAL_CUENTAS']);
	    //$this->visor->addComponent("ContentT","content_title",TarjetasMagneticasTemplate::titulo());
	    break;
	}
	if ($listado) 
	{
	    $listado    = RubrosCPModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
	    //print_r($listado);
	    $result     =  RubrosCPTemplate::formBuscar($listado['paginacion']);
	    $result     .= RubrosCPTemplate::listado($listado['datos']);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	}
      break;
      case 'RUBROSCPDET':
       //echo "ENTRO 2";
        switch($this->action)
        {
          case 'setRegistro'://Codigo CIIU
            $result = RubrosCPTemplate::setRegistros($_REQUEST["codigo"]);
            $this->visor->addComponent("desc_ciiu", "desc_ciiu", $result);
          break;
          case 'setRegistroFP'://Forma de Pago
            $result = RubrosCPTemplate::setRegistrosFormaPago($_REQUEST["codigofp"]);
            $this->visor->addComponent("desc_forma_pago", "desc_forma_pago", $result);
          break;
          case 'setRegistroLPRE'://Lista de Precios
            $result = RubrosCPTemplate::setRegistrosListaPrecios($_REQUEST["codigolpre"]);
            $this->visor->addComponent("desc_lista_precios", "desc_lista_precios", $result);
          break;
          case 'setRegistroDist'://Distritos
            $result = RubrosCPTemplate::setRegistrosDistrito($_REQUEST["codigodist"]);
            $this->visor->addComponent("desc_distrito", "desc_distrito", $result);
          break;
          case 'setRegistroRub'://Rubros
            $result = RubrosCPTemplate::setRegistrosRubro($_REQUEST["codigorub"]);
            $this->visor->addComponent("desc_rubro", "desc_rubro", $result);
          break;
          case 'setRegistroCodCta'://Cuentas de Bancos
            //echo "FIELDS-CONTROL : ".$_REQUEST["fields"]."\n";
            $result = RubrosCPTemplate::setRegistrosCuentas($_REQUEST["codigocta"]);
            $this->visor->addComponent("desc_cta[]", "desc_cta[]", $result);
          break;
          
          case 'setRegistroTipoCtaBan'://Cuentas de Bancos
            //echo "FIELDS-CONTROL : ".$_REQUEST["fields"]."\n";
            $result = RubrosCPTemplate::setRegistrosTipoCtaBan($_REQUEST["codigotipoctaban"]);
            $this->visor->addComponent("desc_tipoctaban[]", "desc_tipoctaban[]", $result);
          break;
          
	  case 'ValidarCodigo':
	    $result = RubrosCPModel::validarCodigo($_REQUEST["Codigo"]);
	    $this->visor->addComponent("MensajeValidacion", "MensajeValidacion", $result);
	  break;
	  
	  case 'ValidarCodigoShell':
	    $result = RubrosCPModel::validarCodigoShell($_REQUEST["CodigoShell"]);
	    $this->visor->addComponent("MensajeValidacionShell", "MensajeValidacionShell", $result);
	  break;
	  
	  case 'ValidarRuc':
	    $result = RubrosCPModel::validarRuc($_REQUEST["CodigoRuc"]);
	    $this->visor->addComponent("MensajeValidacionRuc", "MensajeValidacionRuc", $result);
	  break;

          default:
            //listar ultimos movimientos
          break;
        }
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
