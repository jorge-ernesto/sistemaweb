<?php
  // Controlador del Modulo Cuentas por Cobrar

  Class AplicacionesController extends Controller{
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
      include('movimientos/m_aplicaciones.php');
      include('movimientos/t_aplicaciones.php');
      include('../include/paginador_new.php');
      $this->visor->addComponent('ContentT', 'content_title', AplicacionesTemplate::titulo());
      if(!@$_REQUEST['rxp'] && !@$_REQUEST['pagina'])
      {
         $_REQUEST['rxp'] = 100;
         $_REQUEST['pagina'] = 0;
      }
      $montosArray = array();
      $montosArray = substr($_REQUEST['montos'],1,-1);
      $montosArray = explode('}{',$montosArray);

      switch ($this->request)
      {//task
      case 'APLICACIONES':
      $listado = false;
      $arrayCodigo = array("codigo" => trim($_REQUEST['registroid']));

	switch ($this->action)
	{

            case 'Aplicacion':
            {
               
               $DatosAbonos = AplicacionesModel::tmListadoAbonos($_REQUEST['pro_codigo']);
               $DatosCargo = AplicacionesModel::tmListadoCargos($arrayCodigo,1,1);
	       $result = AplicacionesTemplate::formAplicaciones($DatosCargo['datos'][0],$DatosAbonos);
	       $this->visor->addComponent("ContentB", "content_body", $result);
            }
            break;
	    case 'Aplicar':
//	    $montosArray = array();
	    foreach($_REQUEST['calcular'] as $llave => $valor)
	    {
	       $montosArray = substr($valor,1,-1);
	       $montosArray = explode('}{',$montosArray);
	       $DatosCargo = AplicacionesModel::tmListadoCargos($arrayCodigo,1,1);
	       if(($DatosCargo['datos'][0]['pro_cab_impsaldo']) > $montosArray[0])
	       {
	           AplicacionesModel::ActualizarCargos($DatosCargo['datos'][0]['pro_codigo'], $_REQUEST['pro_cab_tipdocumento'],$_REQUEST['pro_cab_numdocumento'],$montosArray[1],$montosArray[2],$montosArray[0]);
	       }else{
	           AplicacionesModel::ActualizarCargos($DatosCargo['datos'][0]['pro_codigo'], $_REQUEST['pro_cab_tipdocumento'],$_REQUEST['pro_cab_numdocumento'],$montosArray[1],$montosArray[2],trim($DatosCargo['datos'][0]['pro_cab_impsaldo']));
	       }
	    }
	    $listado = true;
	    break;
	    
	    case 'Buscar':
	    //Listo
	    $busqueda = AplicacionesModel::tmListadoCargos(@$_REQUEST["busqueda"],@$_REQUEST['rxp'],@$_REQUEST['pagina']);
	    $result = AplicacionesTemplate::listadoCargos($busqueda['datos']);
	    
	    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    break;

	    default:
	       $listado = true;
	    break;
	}
	if ($listado) 
	{
	    $listado    = AplicacionesModel::tmListadoCargos('',@$_REQUEST['rxp'],@$_REQUEST['pagina']);
	    $result     =  AplicacionesTemplate::formBuscar($listado['paginacion']);
	    $result     .= AplicacionesTemplate::listadoCargos($listado['datos']);
	    $this->visor->addComponent("ContentB", "content_body", $result);
	}

      break;
      case 'APLICACIONESDET':
        //Si hay detalles
        $montos = array();
        $montos['SALDO ABONO']  = $montosArray[0];
        $montos['OPERACION']    = $_REQUEST['operacion'];
        if(!empty($_REQUEST['total_saldo_abono']) && $_REQUEST['operacion']=='sumar')
        {
            $montos['TOTAL SALDO ABONO'] = $_REQUEST['total_saldo_abono']+$montos['SALDO ABONO'];
        }
        elseif(!empty($_REQUEST['total_saldo_abono']) && $_REQUEST['operacion']=='restar')
        {
            $montos['TOTAL SALDO ABONO'] = $_REQUEST['total_saldo_abono']-$montos['SALDO ABONO'];
        }
        else
        {
            $montos['TOTAL SALDO ABONO']  = $montos['SALDO ABONO'];
        }
        $montos['TOTAL IMPORTE SALDO'] = $_REQUEST['total_import_saldo'];
        $TotalMontos = AplicacionesTemplate::verTotales($montos);
        $this->visor->addComponent("Totales","Totales",$TotalMontos);
      break;

      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
