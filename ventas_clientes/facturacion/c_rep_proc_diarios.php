<?php
  // Controlador del Modulo Cuentas por Cobrar

  Class RepProcDiaController extends Controller{
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
      include('facturacion/m_rep_proc_diarios.php');
      include('facturacion/t_rep_proc_diarios.php');
      include('../include/paginador_new.php');
      include('../include/m_sisvarios.php');
      $this->visor->addComponent('ContentT', 'content_title', RepProcDiaTemplate::titulo());
      if(!@$_REQUEST['rxp'] && !@$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      
      switch ($this->request)
      {//task
      case 'REP_PROC_DIA':
      $listado = false;
      $arrayCodigo = array("codigo" => trim($_REQUEST['registroid']));
	switch ($this->action)
	{
            /*case 'Aplicacion':
            {
               
               $DatosAbonos = RepProcDiaModel::tmListadoAbonos($_REQUEST['registroid']);
               $DatosCargo = RepProcDiaModel::tmListadoCargos($arrayCodigo,1,1);
	       $result = RepProcDiaTemplate::formRepProcDia($DatosCargo['datos'][0],$DatosAbonos);
	       $this->visor->addComponent("ContentB", "content_body", $result);
            }
            break;*/

	    case 'Buscar':
	    //Listo
	    $busqueda = RepProcDiaModel::tmListado(@$_REQUEST["busqueda"],@$_REQUEST['rxp'],@$_REQUEST['pagina']);
	    $result = RepProcDiaTemplate::listado($busqueda['datos']);
	    
	    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    //$listado = true;
	    break;

	    default:
	       $listado = true;
	    break;
	}
	if ($listado) 
	{
	    $listado    = RepProcDiaModel::tmListado(@$_REQUEST["busqueda"],@$_REQUEST['rxp'],@$_REQUEST['pagina']);
	    $result     =  RepProcDiaTemplate::formBuscar($listado['paginacion']);
	    $result     .= RepProcDiaTemplate::listado($listado['datos']);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	}

      break;
      case 'DET':
        //Si hay detalles
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
