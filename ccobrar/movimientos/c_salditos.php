<?php

class SalditosController extends Controller{

    	function Init(){
      		$this->visor = new Visor();
      		$this->task = @$_REQUEST["task"];
      		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
    	}

    	function Run() {
      		$this->Init();
      		$result = '';
      		include('movimientos/m_salditos.php');
      		include('movimientos/t_salditos.php');
      		if ($_REQUEST['action']=='Ingresar')
     			$_SESSION['fec_aplicacion']=$_REQUEST['fecha'];    
     
     		if ($_REQUEST['action']=='Interfaz')
       			$this->visor->addComponent('ContentT', 'content_title', '<div align="center"><h2>Fecha de Cancelacion de Saldos</h2></div><hr>');
     		else	
       			$this->visor->addComponent('ContentT', 'content_title', SalditosTemplate::titulo());
     
      		$montosArray = array();
      		$montosArray = substr($_REQUEST['montos'],1,-1);
      		$montosArray = explode('}{',$montosArray);

      		switch ($this->request) {
      		
      			case 'SALDITOS':
      				$listado = false;

				switch ($this->action) {	   
	    
					case 'Cancelacion':
						$result = SalditosTemplate::formSalditos();
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
		
	    				case 'setRegistroCli':
	    					$result = SalditosTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
            					$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
          					break;
	    
	    				case 'Buscar':
	        				$busqueda = SalditosModel::tmListadoDocumentos(@$_REQUEST["busqueda"]);
	    					$result = SalditosTemplate::listadoDocumentos($busqueda['datos']);
	   					$this->visor->addComponent("ListadoB", "resultados_grid", $result);
	    					break;
   
	    				case 'Interfaz':
	    					$result = SalditosTemplate::formInterfaz();
	    					$this->visor->addComponent("ContentB", "content_body", $result);
	    					break;
	    
	    				case 'Ingresar':
	    					$listado=true;
	    					break;	
	    
	    				case 'Cancelar Saldos':
	    					$procesado = SalditosModel::cancelarSalditos($_REQUEST['registroid'],$_REQUEST['monto'],$_REQUEST['tipo_doc'],$_REQUEST['num_doc'],$_REQUEST['caja'],$_REQUEST['glosa']);
					    	$datos = SalditosModel::obtenerDocumento($_REQUEST['registroid']);
					    	$_REQUEST['saldo'] 	  = $datos['datos'][0]['dt_fechasaldo'];
					    	$_REQUEST['fechaemision'] = $datos['datos'][0]['dt_fechaemision'];
					    	$_REQUEST['tipo'] 	  = $datos['datos'][0]['ch_tipdocumento'];
					    	$_REQUEST['serie'] 	  = $datos['datos'][0]['ch_seriedocumento'];
					    	$_REQUEST['numero'] 	  = $datos['datos'][0]['ch_numdocumento'];
					    	$_REQUEST['saldo'] 	  = $datos['datos'][0]['nu_importesaldo'];
					    	$_REQUEST['importe'] 	  = $datos['datos'][0]['nu_importetotal'];
					    	$_REQUEST['codigo'] 	  = $datos['datos'][0]['cli_codigo'];
					    	$_REQUEST['razsocial'] 	  = $datos['datos'][0]['cli_razsocial'];
					    	$result = SalditosTemplate::formSalditos();
						$this->visor->addComponent("ContentB", "content_body", $result);
					    	$listado = false;
					    	break;	
	    	
	    				default:
	       					$listado = true;
		   				break;
				}
				
				if ($listado) {		
					if ($_REQUEST['busqueda'])
						$listado = SalditosModel::tmListadoDocumentos($_REQUEST['busqueda']);			
					$result      =  SalditosTemplate::formBuscar();
	    				$result     .= SalditosTemplate::listadoDocumentos($listado['datos']);
	    				$this->visor->addComponent("ContentB", "content_body", $result);
				}
      				break;
     
      			default:
        			$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      				break;
		}
	}
}
?>
