<?php

Class AnticiposController extends Controller {

	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {
		$this->Init();
		$result = '';
		include('movimientos/m_anticipos.php');
		include('movimientos/t_anticipos.php');

		if ($_REQUEST['action']=='Ingresar') {
			$_SESSION['fec_aplicacion']=$_REQUEST['fecha'];
		}

		if ($_REQUEST['action']=='Interfaz')
			$this->visor->addComponent('ContentT', 'content_title', '<div align="center"><h2>Fecha de Aplicacion de Anticipos</h2></div><hr>');
		else	
			$this->visor->addComponent('ContentT', 'content_title', AnticiposTemplate::titulo());

		$montosArray = array();
		$montosArray = substr($_REQUEST['montos'],1,-1);
		$montosArray = explode('}{',$montosArray);
      
		switch ($this->request) {

			case 'ANTICIPOS':
				$listado = false;
      		
				switch ($this->action) {
					case 'Aplicar':						  
						$DatosCargo = AnticiposModel::tmSeleccionaAnticipo(trim($_REQUEST['registroid']));						
						if ($_REQUEST['chkpormonto']=='S') {
				    			AnticiposModel::AplicarporMonto($DatosCargo['datos'][0]['cli_codigo'], 
											$_REQUEST['ch_tipdocumento'],
											$_REQUEST['ch_numdocumento'],
											$_REQUEST['monto']);
				    			$_REQUEST['TotalSaldoAbono'] = $_REQUEST['monto'];
				    		} else {
							foreach($_REQUEST['calcular'] as $llave => $valor) {
								$montosArray = substr($valor,1,-1);
								$montosArray = explode('}{',$montosArray);
								AnticiposModel::AplicarResumenes($DatosCargo['datos'][0]['cli_codigo'], 
												$_REQUEST['ch_tipdocumento'],
												$_REQUEST['ch_numdocumento'],
												$montosArray[1],
												$montosArray[2],
												$montosArray[0]);
							}
				        	}
						$anticipo = AnticiposModel::tmSeleccionaAnticipo($_REQUEST['registroid']);
						$resumenes = AnticiposModel::tmListaResumenes($_REQUEST['registroid']);
						$result = AnticiposTemplate::formAplicacionesdeAnticipo($anticipo['datos'][0],$resumenes['datos']);
						$listado=false;
						$this->visor->addComponent("ListadoB", "content_body", $result);
						break;
					
					case 'setRegistroCli':
				    		$result = AnticiposTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
			            		$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
			        		break;
			        
					case 'Aplicacion':
						$anticipo = AnticiposModel::tmSeleccionaAnticipo($_REQUEST['registroid']);
						$resumenes = AnticiposModel::tmListaResumenes($_REQUEST['registroid']);
						$result = AnticiposTemplate::formAplicacionesdeAnticipo($anticipo['datos'][0],$resumenes['datos']);
						$listado=false;
					    	$this->visor->addComponent("ListadoB", "content_body", $result);
						break;
					
		            		case 'Buscar':
						$listado = AnticiposModel::tmListaAnticipos(@$_REQUEST["busqueda"]);
						$result = AnticiposTemplate::listadoAnticipos($listado['datos']);
						$listado=false;
						$this->visor->addComponent("ListadoB", "resultados_grid", $result);
				    		break;
				    
					case 'Interfaz':
			    			$result = AnticiposTemplate::formInterfaz();
			    			$this->visor->addComponent("ContentB", "content_body", $result);
			    			break;
	    
				    	case 'Ingresar':
				       		$listado=true;
				    		break;	
				    				    
				    	default:
				       		$listado = true;
				    	break;
				}
				if ($listado) {
					if ($_REQUEST['busqueda'])
						$listado     = AnticiposModel::tmListaAnticipos($_REQUEST['busqueda']);
					$result      = AnticiposTemplate::formBuscar();
					$result     .= AnticiposTemplate::listadoAnticipos($listado['datos']);
			    		$this->visor->addComponent("ContentB", "content_body", $result);
				}
	      			break;

     			case "ANTICIPOSDET":
	  			$montos = array();
        			$montos['SALDO ABONO']  = $montosArray[0];
        			$montos['OPERACION']    = $_REQUEST['operacion'];
        			if(!empty($_REQUEST['total_saldo_abono']) && $_REQUEST['operacion']=='sumar') {
					$montos['TOTAL SALDO ABONO'] = $_REQUEST['total_saldo_abono']+$montos['SALDO ABONO'];
				} elseif(!empty($_REQUEST['total_saldo_abono']) && $_REQUEST['operacion']=='restar') {
					$montos['TOTAL SALDO ABONO'] = $_REQUEST['total_saldo_abono']-$montos['SALDO ABONO'];
				} else {
					$montos['TOTAL SALDO ABONO']  = $montos['SALDO ABONO'];
				}
				$montos['TOTAL IMPORTE SALDO'] = $_REQUEST['total_import_saldo'];
				$TotalMontos = AnticiposTemplate::verTotales($montos);
				$this->visor->addComponent("Totales","Totales",$TotalMontos);
				break;
	  	
			default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
				break;
		}
	}
}
