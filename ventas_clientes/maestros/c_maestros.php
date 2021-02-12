<?php

class MaestrosController extends Controller {

    	function Init(){
      		$this->visor = new Visor();
      		$this->task = @$_REQUEST["task"];
      		isset($_REQUEST["action"])?$this->action = $_REQUEST["action"]:$this->action = '';
    	}

    	function Run() {    	
      		$this->Init();
      		$result = '';
      		$Controlador = null;
      		
      		switch ($this->request) {
      		
			case "CLIENTES":
		   			include "maestros/c_clientes.php";
		    			$Controlador = new ClientesController("CLIENTES");
					break;
					
			case "CLIENTE":
		    			include "maestros/c_cliente.php";
		    			$Controlador = new ClienteController($this->task);
					break; 
					
        		case 'TARJMAG':		
					include('maestros/c_tarjetas_mag.php');
          				$Controlador = new TarjetasMagneticasController($this->task);
        				break;
        
        		case "FORMPRUEBA":
          				include ('maestros/c_form_prueba.php');
          				$Controlador = new FormPruebaController($this->task);
        				break;
        
        		case "PRECIOS":
        				include('maestros/c_promedio.php');
        				$Controlador = new PreciosController($this->task);
        				break;
	
       			case "RUC":
        				include('maestros/c_ruc.php');
       					$Controlador = new RucController($this->task);
        				break;

			case 'MANTENIMIENTOCLIENTE':
					include('maestros/c_mantenimiento_cliente.php');
					$Controlador = new MantenimientoClienteController("MANTENIMIENTOCLIENTE");
					break;

			case 'TIPODECAMBIO':
					include('maestros/c_tipodecambio.php');
					$Controlador = new TipodeCambioController("");
					break;

			case 'SERIEDOCUMENTO':
					include('maestros/c_serie_documento.php');
					$Controlador = new SerieDocumentoController('');
					break;

        		default:
          				$this->visor->addComponent("ContentB", "content_body", "<h2>MAESTRO NO CONOCIDO</h2>");
        				break;
      		}
      		if ($Controlador != null){
        		$Controlador->Run();
        		$this->visor = $Controlador->visor;
      		}
    	}
}
