<?php
  // Controlador Principal o de defecto

 class ReportesController extends Controller{
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

      	case 'ESTADOCUENTA':
			include('reportes/c_estado_cuenta.php');
			$Controlador = new EstadoCuentaController($this->task);
			break;
		
	case 'MOVIMIENTOS':
      			include('reportes/c_movclientes.php');
      			$Controlador = new MovClientesController($this->task);
      			break;
      	
        case 'ANALITICO':
			include('reportes/c_mov_analitico.php');
		   	$Controlador = new TarjetasMagneticasController($this->task);
			break;

	case 'GENERALES':
			include('reportes/c_reporte_general.php');
		    	$Controlador = new ReporteGeneralController('GENERALES');
			break;

	case 'DIACIERRE':
			include('reportes/c_dia_cierre_turno.php');
		    	$Controlador = new DiaCierreTurnoController('DIACIERRE');
			break;
		   	
	    default:
	        $this->visor->addComponent("ContentB", "content_body", "<h2>REPORTE NO CONOCIDO</h2>");
	    break;
      }
      if ($Controlador != null){
        $Controlador->Run();
        $this->visor = $Controlador->visor;
      }
    }
  }
