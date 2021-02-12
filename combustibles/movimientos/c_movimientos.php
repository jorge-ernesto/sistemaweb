<?php

class MovimientosController extends Controller {

	function Init() {
		$this->visor = new Visor();
	}

	function Run() {
		$this->Init();
		$Controlador = null;
		switch ($this->request) {
			case "INTERFAZSAP" :
				include "movimientos/c_interface_sap.php";
				$Controlador = new InterfaceSAPController("INTERFAZSAP");
				break;

			case "VARILLAS":
				include "movimientos/c_varillas.php";
				$Controlador = new VarillasController("VARILLAS");
				break;

			case "CAJAYBANCO":
				include "movimientos/c_cajabanco.php";
				$Controlador = new CajaBancoController("CAJAYBANCO");
				break;

			case "STOCKTURNO":
				include "movimientos/c_stockxturno.php";
				$Controlador = new StockTurnoController("STOCKTURNO");
				break;
	
			case 'INTERFAZIRIDIUM':
				include('movimientos/c_interface_iridium.php');
				$Controlador = new InterfaceMovController($this->task);
			        break;

                        case 'INTERFAZSIGO':
				include('movimientos/c_interface_sigo.php');
				$Controlador = new InterfaceMovController($this->task);
			        break;

        		case 'INTERFAZ3DO':
				include('movimientos/c_interface_3do.php');
				$Controlador = new Interface3DOController($this->task);
			        break;

        		case 'INTERFAZQUIPU':
				include('movimientos/c_interface_quipu.php');
				$Controlador = new InterfaceQuipuController($this->task);
			        break;
			
        		case 'INTERFAZEXACTUS':
				include('movimientos/c_interface_exactus.php');
				$Controlador = new InterfaceExactusController($this->task);
			        break;

        		case 'INTERFAZCOPETROL':
				include('movimientos/c_interface_copetrol.php');
				$Controlador = new InterfaceCopetrolController($this->task);
			        break;

        		case 'INTERFAZNEVADA':
				include('movimientos/c_interface_nevada.php');
				$Controlador = new InterfaceNevadaController($this->task);
			        break;

        		case 'INTERFAZCONCAR':
				include('movimientos/c_interface_concar.php');
				$Controlador = new InterfaceConcarController($this->task);
			        break;

				case 'INTERFAZCONCARACT':
				include('movimientos/c_interface_concar_act.php');
				$Controlador = new InterfaceConcarActController($this->task);
			        break;

		       case 'INTERFAZBARRANCA':

				include('movimientos/c_interface_excel.php');
				$Controlador = new InterfaceMovController($this->task);

			break;

                       case 'INTERFAZESMERALDA':
                          
				include('movimientos/c_interface_excel_esmeralda.php');
				$Controlador = new InterfaceMovControllerCE($this->task);

			break;

                       case 'INTERFAZCE':
                          
				include('movimientos/c_interface_excel_ce.php');
				$Controlador = new InterfaceMovControllerCE($this->task);

			break;
                            
			
			case 'INTERFAZDOMINIO':
				include('movimientos/c_interface_dominio.php');
				$Controlador = new InterfaceDominioController($this->task);
			        break;

			case 'ACTFORMAPAGO':
				include('movimientos/c_actformapago.php');
				$Controlador = new ActFormaPagoController($this->task);
				break;

			case 'AVANZECONTOMETROS':
				include('movimientos/c_avanzecontometros.php');
				$Controlador = new AvanzeContometrosController($this->task);
				break;
				
			case 'DEPOSITOSPOS':
				include('movimientos/c_depositos.php');
				$Controlador = new DepositosPosController($this->task);
				break;

			case 'ACTDEPOSITOSPOS':
				include('movimientos/c_act_depositos_pos.php');
				$Controlador = new ActDepositosPosController($this->task);
				break;

			case 'DESPACHOLINEA':
				include('movimientos/c_despacholinea.php');
				$Controlador = new DespachoLineaController($this->task);
				break;

	    		case 'LIQUIDACIONGASTOS':
				include('movimientos/c_liquidacion_gastos.php');
				$Controlador = new LiquidacionGastosController($this->task);
				break;

			case 'SOBRANTESFALTANTESTRABAJADOR':
				include('movimientos/c_sobrantes_faltantes_x_trabajador.php');
				$Controlador = new SobrantesFaltantesTrabajadorController($this->task);
				break;

			case 'SOBRANTESFALTANTES':
				include('movimientos/c_sobrantes_faltantes.php');
				$Controlador = new LiquidacionGastosController($this->task);
				break;

			case 'CUADREVENTAS':
				include('movimientos/c_cuadre_ventas.php');
				$Controlador = new CuadreVentasController($this->task);
				break;
	
			case 'PROGRAMA_AFERICION':
				include('movimientos/c_programa_afericion.php');
				$Controlador = new ProgramaAfericionController($this->task);
				break;

			case 'CONSOLIDACION':
				include('movimientos/c_consolidacion.php');
				$Controlador = new ConsolidacionController($this->task);
				break;

			case 'VENTASTRABAJADOR':
				include('movimientos/c_ventas_trabajador.php');
				$Controlador = new VentasTrabajadorController($this->task);
				break;	 

			case 'DESCONSOLIDAR':
				include('movimientos/c_desconsolidar.php');
				$Controlador = new DesconsolidarController("DESCONSOLIDAR");
				break;	   

			case 'EXTORNO':
				include('movimientos/c_extorno.php');
				$Controlador = new ExtornoController("EXTORNO");
				break;	   

			case 'CODIGOSRAPIDO':
				include('movimientos/c_codigos_rapido.php');
				$Controlador = new CodigosRapidoController("CODIGOSRAPIDO");
				break;	

			case 'IMPRESIONCIERRES':
				include('movimientos/c_impresion_cierres.php');
				$Controlador = new ImpresionCierresController("IMPRESIONCIERRES");
				break;

			case 'CUADRETURNO':
				include('movimientos/c_cuadre_turno.php');
				$Controlador = new CuadreTurnoController($this->task);
				break;	
			
			case 'VENTAGRANEL':
				include('movimientos/c_venta_granel.php');
				$Controlador = new VentaGranelController($this->task);
				break;
			
			case 'PROGRAMRUTA':
				include('movimientos/c_ruta_granel.php');
				$Controlador = new VentaGranelController($this->task);
				break;

			case 'ASIENTOS':
				include('movimientos/c_asientos.php');
				$Controlador = new AsientosController($this->task);
				break;

			case 'TICKETSGNV':
				include('movimientos/c_tickets_gnv.php');
				$Controlador = new TicketsGNVController($this->task);
				break;

			case 'ELIMINACION':
				include('movimientos/c_elimina_cuentaxcobrar.php');
				$Controlador = new EliminaCuentaxCobrarController($this->task);
				break;	

			case 'CONTMECANICOS':
				include('movimientos/c_cont_mecanicos.php');
				$Controlador = new ContMecanicosController($this->task);
				break;	
				
			case 'PROBE':
				include('movimientos/c_probe.php');
				$Controlador = new ProbeController($this->task);
				break; 

			case 'INTERFAZPECANA':
				include('movimientos/c_interface_excel_pecana.php');
				$Controlador = new InterfaceMovControllerCE($this->task);
				break;

			case 'INTERFAZEUROMAX':
				include('movimientos/c_interface_excel_euromax.php');
				$Controlador = new InterfaceMovControllerEuromax($this->task);
				break;

			case 'CAJA':
				include('movimientos/c_movimientos_caja.php');
				$Controlador = new MovimientosCajaController('CAJA');
				break;

			case 'LIQUIDACIONCAJA':
				include('movimientos/c_liquidacion_caja.php');
				$Controlador = new VarillasController('LIQUIDACIONCAJA');
				break;
		
			default:
				$this->visor->addComponent("ContentB", "content_body", "<h2><b>Funcion de movimientos no conocida</b></h2>");
				break;	
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
}
