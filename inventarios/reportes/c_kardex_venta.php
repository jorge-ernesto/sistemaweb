<?php

class KardexVentaActController extends Controller {

    function Init() {
        $this->visor = new Visor();
        isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
    }

    function Run() {
        include 'reportes/m_kardex_venta.php';
        include 'reportes/t_kardex_venta.php';

        $this->Init();
        $result = "";
        $result_f = "";
        $form_search = false;
        $reporte = false;

        switch ($this->action) {

            case "Buscar":
                $reporte = true;
                break;

            case "PDF":
                $resultado = KardexVentaActModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);
                $result = KardexVentaActTemplate::reportePDF($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['tipo_reporte']);
                $mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/Kardex.pdf";
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="' . "Kardex.pdf" . '"');
                readfile($mi_pdf);
                break;

            case "Excel":
                $resultado = KardexVentaActModel::search($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['art_desde'], $_REQUEST['estacion'], $_REQUEST['art_linea']);
                KardexVentaActTemplate::reporteExcel($resultado, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['tipo_reporte']);

                break;

            default:
                $form_search = true;
        }

        if ($form_search) {
            $result = KardexVentaActTemplate::formSearch();
        }

        if ($reporte) {
            $accion = $_REQUEST['accion'];
            /*
              $listado_productos = KardexVentaActModel::lista_productos($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
              $saldos_inicial = KardexVentaActModel::saldo_inicial_mes_anterior($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
              $saldos_final = KardexVentaActModel::saldo_inicial_mes_actual($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
             */

            $ventas_inventario = KardexVentaActModel::ventas_inventario($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
             $cantidadDiasMes=KardexVentaActTemplate::getUltimoDiaMes($_REQUEST['ano'], $_REQUEST['mes']);


            $listado_productos = KardexVentaActModel::lista_productos($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $saldos_inicial = KardexVentaActModel::saldo_inicial_mes_anterior($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $saldos_final = KardexVentaActModel::saldo_inicial_mes_actual($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $mermas = KardexVentaActModel::ingreso_ajuste_inventario($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $solo_venta_producto=KardexVentaActModel::ventas_producto($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);

            $linea = KardexVentaActModel::getdescripcion_linea();

            if ($accion == "Normal" || $accion == "Agrupado") {
                $ingreso_inventario = KardexVentaActModel::ingreso_inventario($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
                $result_f = KardexVentaActTemplate::listado($accion, $listado_productos, $saldos_inicial, $ingreso_inventario, $ventas_inventario, $saldos_final, $linea,$solo_venta_producto,$cantidadDiasMes);
            }



            //
        }


        $this->visor->addComponent("ContentT", "content_title", KardexVentaActTemplate::Titulo());
        if ($result != "")
            $this->visor->addComponent("ContentB", "content_body", $result);
        if ($result_f != "")
            $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }

}

