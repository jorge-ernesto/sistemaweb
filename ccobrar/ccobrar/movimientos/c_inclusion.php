<?php
  // Controlador del Modulo Cuentas por Cobrar

  Class InclusionController extends Controller{
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
	echo 'entro';
      include('movimientos/m_inclusion.php');
      include('movimientos/t_inclusion.php');
      $this->visor->addComponent('ContentT', 'content_title', InclusionTemplate::titulo());
           
      switch ($this->request)
      {
      	case 'INCLUSION':
      	$listado = false;
      	$arrayCodigo = array("codigo" => trim($_REQUEST['registroid']));

	switch ($this->action)
	{
		
		case 'setRegistroCli':
			$result = InclusionTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
			$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
			break;
        	case 'Aplicacion':
            		$DatosAbonos = InclusionModel::tmListadoAbonos($_REQUEST['registroid']);
            		$DatosCargo = InclusionModel::tmListadoCargos($arrayCodigo,1,1);
	        	$result = InclusionTemplate::formInclusion($DatosCargo['datos'][0],$DatosAbonos);
	        	$this->visor->addComponent("ContentB", "content_body", $result);
            		break;
		case 'Buscar':
			$busqueda = InclusionModel::tmListadoCargos(@$_REQUEST["busqueda"]);	
			$result = InclusionTemplate::listadoCargos($busqueda['datos']);
			$this->visor->addComponent("ListadoB", "resultados_grid", $result);
			break;
		case 'Modificar':
	    	$codigo = trim($_REQUEST['registroid']).trim($_REQUEST['ch_tipdocumento']).trim($_REQUEST['ch_seriedocumento']).trim($_REQUEST['ch_numdocumento']);
	    	$modificado = InclusionModel::modificarDiasVencimiento($_REQUEST['registroid'],$_REQUEST['ch_tipdocumento'],$_REQUEST['ch_numdocumento'],$_REQUEST['dias']);
	    	$DatosAbonos = InclusionModel::tmListadoAbonos(trim($_REQUEST['registroid']).trim($_REQUEST['ch_tipdocumento']).trim($_REQUEST['ch_numdocumento']));
            //$DatosCargo = InclusionModel::tmListadoCargos(array('codigo'=>$codigo));
            $DatosCargo = InclusionModel::obtenerDocumentodeCargo($codigo);
	       	$result = InclusionTemplate::formInclusion($DatosCargo['datos'][0],$DatosAbonos);
	       	$this->visor->addComponent("ContentB", "content_body", $result);
	       	break;
	    
	    default:
	       $listado = true;
	    break;
	}
	if ($listado) 
	{
		if ($_REQUEST['busqueda'])
	    	$listado    = InclusionModel::tmListadoCargos($_REQUEST['busqueda']);
	    $result     =  InclusionTemplate::formBuscar();
	    $result     .= InclusionTemplate::listadoCargos($listado['datos']);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	}

      break;
      case 'INCLUSIONDET':
        //Si hay detalles
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
