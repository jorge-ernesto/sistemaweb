<?php
  // Controlador Principal o de defecto

 class FacturacionController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      isset($_REQUEST["action"])?$this->action = $_REQUEST["action"]:$this->action = '';
      //otros variables de entorno
    }

    function Run(){

      $this->Init();

      $result = '';
      $Controlador = null;

      switch ($this->request){

        case 'FACTURAS':
            include('facturacion/c_facturas.php');
            $Controlador = new FacturasController($this->task);
        break;

		case 'FACTURAS2':
            include('facturacion/c_facturas2.php');
            $Controlador = new Facturas2Controller($this->task);
        break;

		case 'TICKETSPOS':
		    include('facturacion/c_ticketspos.php');
		    $Controlador = new TicketsPosController($this->task);
		break;
		case 'LIQUIDACION':
		    include('facturacion/c_liquidacion.php');
		    $Controlador = new LiquidacionController($this->task);
		break;
		case 'REP_PROC_DIA':
		    include('facturacion/c_rep_proc_diarios.php');
		    $Controlador = new RepProcDiaController($this->task);
		break;
		case 'IMPRESIONES':
		    include('facturacion/c_impresiones.php');
		    $Controlador = new ImpresionesController('IMPRESIONES');
			break;
		case 'REIMPRESION':
		    include('facturacion/c_reimpresion.php');
		    $Controlador = new ReimpresionController('REIMPRESION');
		    break;
		case 'AUTORIZAR':
			include('facturacion/c_precios.php');
		    $Controlador = new PreciosController($this->task);
		   	break;
		case 'DESCUENTOS':
			include('facturacion/c_descuentos.php');
			$Controlador = new DescuentosController($this->task);
			break;
		case 'ESPECIALES':
			include('facturacion/c_facturasespeciales.php');
			$Controlador = new FacturasController($this->task);
			break;
		case 'RESUMEN':
			include('facturacion/c_resumen.php');
			$Controlador = new ResumenController($this->task);
			break;
		case 'VALES':
			include('facturacion/c_vales.php');
			$Controlador = new ValesController($this->task);
			break;
		case 'CONTROL':
			include('facturacion/c_contcliente.php');
			$Controlador = new ContClienteController($this->task);
			break;
	    default:
	        $this->visor->addComponent("ContentB", "content_body", "<h2>FACTURACI&Oacute;N NO CONOCIDA</h2>");
	        break;
      }
      if ($Controlador != null){
      	$Controlador->Run();
        $this->visor = $Controlador->visor;
      }
    }
  }
?>
