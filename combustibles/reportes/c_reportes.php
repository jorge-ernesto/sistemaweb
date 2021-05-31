<?php

class ReportesController extends Controller {

	function Init() {
		$this -> visor = new Visor();
	}

	function Run() {
		$this -> Init();
		$Controlador = null;
		switch ($this->request) {
			case "CUADROVENTALIQUIDACION" :
				include "reportes/c_cuadro_venta_liquidacion.php";
				$Controlador = new CuadroVentaLiquidacionController("CUADROVENTALIQUIDACION");
				break;

			case "VENTACOMBUSTIBLEXLADO" :
				include "reportes/c_venta_combustible_x_lado.php";
				$Controlador = new VentaCombustiblexLadoController("VENTACOMBUSTIBLEXLADO");
				break;

			case "AFERICIONESREP" :
				include "reportes/c_afericion_rep.php";
				$Controlador = new AfericionReportController("AFERICIONESREP");
				break;

			case "DIARIOINVENTARIOYCAJA" :
				include "reportes/c_diario_inventarioycaja.php";
				$Controlador = new DiarioInventarioYCajaController("DIARIOINVENTARIOYCAJA");
				break;
				
			case "CONTOMETROS" :
				include "reportes/c_contometros.php";
				$Controlador = new ContometrosController("CONTOMETROS");
				break;

			case "PARTELIQUIDACION" :
				include "reportes/c_parteliquidacion.php";
				$Controlador = new ParteLiquidacionController("PARTELIQUIDACION");
				break;

			case "PARTELIQUIDACIONNEW" :
				include "reportes/c_parteliquidacion_new.php";
				$Controlador = new ParteLiquidacionController("PARTELIQUIDACIONNEW");
				break;

			case "PARTEMARKET" :
				include "reportes/c_partemarket.php";
				$Controlador = new ParteMarketController("PARTEMARKET");
				break;

			case "LIQUIDACION" :
				include "reportes/c_liquidacion.php";
				$Controlador = new LiquidacionController("LIQUIDACION");
				break;

			case "SUSTENTOVENTAS" :
				include "reportes/c_sustventas.php";
				$Controlador = new SustentoVentasController("SUSTENTOVENTAS");
				break;

			case "EXVENTAS" :
				include "reportes/c_exventas.php";
				$Controlador = new ExVentasController("EXVENTAS");
				break;

			case "EXISTENCIAS" :
				include "reportes/c_existencias.php";
				$Controlador = new ExistenciasController("EXISTENCIAS");
				break;

			case "STOCK" :
				include "reportes/c_stock.php";
				$Controlador = new StockController("STOCK");
				break;

			case "SOBRA_FALTA" :
				include "reportes/c_sobra_falta.php";
				$Controlador = new SobraFaltaController("SOBRA_FALTA");
				break;

			case "COMPRAS" :
				include "reportes/c_compras.php";
				$Controlador = new ComprasController("COMPRAS");
				break;

			case "CIERRES_Z" :
				include "reportes/c_cierres_z.php";
				$Controlador = new CierresZController("CIERRES_Z");
				break;

			case "SYFTURNO" :
				include "reportes/c_syfturno.php";
				$Controlador = new SYFTurnoController("CIERRES_Z");
				break;

			case "AFERICIONES" :
				include "reportes/c_afericiones.php";
				$Controlador = new AfericionesController("");
				break;

			case "DESCUENTOAVENTA" :
				include "reportes/c_descuento_venta.php";
				$Controlador = new DescuentoVentaController("DESCUENTOAVENTA");
				break;

			case "CONTINUIDAD" :
				include "reportes/c_continuidad.php";
				$Controlador = new ContinuidadController("");
				break;

			case "LIQUIDACIONGNV" :
				include "reportes/c_liquidacion_gnv.php";
				$Controlador = new LiquidacionGNVController("LIQUIDACIONGNV");
				break;

			case "VARILLAS" :
				include "reportes/c_venta_gnv.php";
				$Controlador = new VarillasController("VARILLAS");
				break;

			case "DEPOSITOSBANK" :
				include "reportes/c_depositos_bank.php";
				$Controlador = new DepositosBankController("");
				break;

			case "CUENTASBANCARIAS" :
				include "reportes/c_cuentas_bancarias.php";
				$Controlador = new CuentasBancariasController("");
				break;

			case "MAN_RUBRO" :
				include "reportes/c_man_rubro.php";
				$Controlador = new man_rubro_Controller("");
				break;

			case "TARJETAS" :
				include "reportes/c_tarjetas.php";
				$Controlador = new TarjetasCreditoController();
				break;

			case "MAN_CASHOPE" :
				include "reportes/c_man_operacione_cash.php";
				$Controlador = new MAN_CASHOPEController("");
				break;

			case "VENTADIARIANEW" :
				include "reportes/c_liquidacion_ventas_diarias_new.php";
				$Controlador = new liquidacion_ventas_diariasController($this->visor);
				break;

			case "GASTOS" :
				include "reportes/c_man_gastos.php";
				$Controlador = new Gasto_Controller("");
				break;

			case "CONFIGURARCONCAR" :
				include "reportes/c_configuracion_concar.php";
				$Controlador = new ConfigurarConcarController("");
			break;

			case "CONFIGURARCONCARNEW" :
				include "reportes/c_configuracion_concarnew.php";
				$Controlador = new ConfigurarConcarNewController("");
			break;

			case "REGISTROVENTAS" :
				include "reportes/c_registro_ventas.php";
				$Controlador = new RegistroVentasController("");
			break;

			case "REGISTROVENTASFE" :
				include "reportes/c_registro_ventas_fe.php";
				$Controlador = new RegistroVentasFEController();
			break;

			case "ARQUEO" :
				include "reportes/c_arqueo.php";
				$Controlador = new ArqueoController();
			break;

			default :
				$this -> visor -> addComponent("ContentB", "content_body", "<h2><b>Funcion de reportes no conocida</b></h2>");
				break;
		}

		if ($Controlador != null) {
			$Controlador -> Run();
			$this -> visor = $Controlador -> visor;
		}
	}

}
