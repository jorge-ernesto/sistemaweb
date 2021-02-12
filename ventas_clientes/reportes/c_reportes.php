<?php

class ReportesController extends Controller {

    function Init() {
        $this->visor = new Visor();
        $this->task = @$_REQUEST["task"];
    }

    function Run() {
        $this->Init();
        $Controlador = null;
        switch ($this->request) {
            case "INTERFACESAP":
                include "reportes/c_interface_sap.php";
                $Controlador = new InterfaceSAPController("INTERFACESAP");
                break;

            case "RESUMENDIARIOVALESXEMPRESA":
                include "reportes/c_resumen_diario_vales_x_empresa.php";
                $Controlador = new ResumenDiarioValesXEmpresaController("RESUMENDIARIOVALESXEMPRESA");
                break;

            case "REGITROVENTASCLIENTES":
                include "reportes/c_registro_ventas_clientes.php";
                $Controlador = new RegistroVentasClientesController("REGITROVENTASCLIENTES");
                break;

            case "DIAVALES":
                include "reportes/c_consistenciavales.php";
                $Controlador = new ConsistenciaValesController("DIAVALES");
                break;

            case "ESPECIALES":
                include "reportes/c_especiales.php";
                $Controlador = new EspecialesController("ESPECIALES");
                break;

            case "CONSUMO_VALES":
                include "reportes/c_cons_vales.php";
                $Controlador = new ConsvalesController($this->task);
                break;

            case "VENTASCONTARJETA":
                include "reportes/c_ventastarjeta.php";
                $Controlador = new VentasTarjetaController("VENTASCONTARJETA");
                break;

            case "VENTASCONVALE":
                include "reportes/c_ventasvale.php";
                $Controlador = new VentasValeController("VENTASCONVALE");
                break;

            case "VENTASXHORAS":
                include "reportes/c_ventasxhoras.php";
                $Controlador = new VentasxHorasController("VENTASXHORAS");
                break;

            case "VALESXDIAS":
                include "reportes/c_consumo_vales_dias.php";
                $Controlador = new ConsumoValesDiasController("VALESXDIAS");
                break;

            case "VENTASDIARIAS":
                include "reportes/c_ventasdiarias.php";
                $Controlador = new VentasDiariasController("VENTASDIARIAS");
                break;

            case "VENTASDIARIAS2":
                include "reportes/c_ventasdiarias2.php";
                $Controlador = new VentasDiariasController("VENTASDIARIAS");
                break;

             case "VENTASMENSUALES":
                include "reportes/c_ventas_mensuales.php";
                $Controlador = new VentasMensualesController("VENTASMENSUALES");
                break;

            case "VENTASOFICIAL":
                include "reportes/c_ventasoficial.php";
                $Controlador = new VentasOficialController("VENTASOFICIAL");
                break;

            case "VENTASXPROVEEDOR":
                include "reportes/c_ventasxproveedor.php";
                $Controlador = new VentasxProveedorController("VENTASXPROVEEDOR");
                break;

            case "VENTASESPECIALES":
                include "reportes/c_ventas_especiales.php";
                $Controlador = new VentasEspecialesController("VENTASESPECIALES");
                break;

            case "CONSUMO":
                include "reportes/c_consumo.php";
                $Controlador = new ConsumoController($this->task);
                break;

            case "CARTAS":
                include "reportes/c_cartas.php";
                $Controlador = new CartasController($this->task);
                break;

            case "DAOT":
                include "reportes/c_daot.php";
                $Controlador = new DaotController("DAOT");
                break;

            case "GRAFICOVENTASHORAS":
                include "reportes/c_graficoventashoras.php";
                $Controlador = new GraficoVentasHorasController("GRAFICOVENTASHORAS");
                break;

            case "GRAFICOVENTASDIARIAS":
                include "reportes/c_graficoventasdiarias.php";
                $Controlador = new GraficoVentasDiariasController("GRAFICOVENTASDIARIAS");
                break;

            case "GRAFICOVENTASMENSUALES":
                include "reportes/c_graficoventasmensuales.php";
                $Controlador = new GraficoVentasMensualesController("GRAFICOVENTASMENSUALES");
                break;

            case "LISTACOMPRAS":
                include "reportes/c_lista_compras.php";
                $Controlador = new ListaComprasController("LISTACOMPRAS");
                break;

            case "TIPOCAMBIO":
                include "reportes/c_tipo_cambio.php";
                $Controlador = new TipoCambioController("TIPOCAMBIO");
                break;

            case "REGISTROVENTAS":
                include "reportes/c_registro_ventas.php";
                $Controlador = new RegistroVentasController("REGISTROVENTAS");
                break;

            case "UTILIDADBRUTA":
                include "reportes/c_utilidad_bruta.php";
                $Controlador = new UtilidadBrutaController("UTILIDADBRUTA");
                break;

            case "PEDIDOCOMPRAS":
                include "reportes/c_pedido_compras.php";
                $Controlador = new PedidoComprasController("PEDIDOCOMPRAS");
                break;

            case "TRANSTRABAJADOR":
                include "reportes/c_trans_trabajador.php";
                $Controlador = new TransTrabajadorController("TRANSTRABAJADOR");
                break;

            case "VTADIARIA":
                include "reportes/c_vtadiaria.php";
                $Controlador = new VtaDiariaController("VTADIARIA");
                break;

            case "ESTADISTICA":
                include "reportes/c_estadistica.php";
                $Controlador = new EstadisticaController("ESTADISTICA");
                break;

            case 'CONSULTA':
                include('reportes/c_consulta_cuentasxcobrar.php');
                $Controlador = new ConsultaCuentaxCobrarController($this->task);
                break;

            case 'CONSULTAPAGAR':
                include('reportes/c_consulta_cuentasxpagar.php');
                $Controlador = new ConsultaCuentaxPagarController($this->task);
                break;

            case "REPORTETURNO":
                include "reportes/c_reporte_turno.php";
                $Controlador = new ReporteTurnoController($this->visor);
                break;
            case "AUDITORVENTA":
                include "reportes/c_auditor_venta.php";
                $Controlador = new AuditorVentaController($this->visor);
                break;

            default:
                $this->visor->addComponent("ContentB", "content_body", "<h2><b>Funcion de reportes no conocida</b></h2>");
                break;
        }

        if ($Controlador != null) {
            $Controlador->Run();
            $this->visor = $Controlador->visor;
        }
    }

}

