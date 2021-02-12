<?php

class AuditorVentaController extends Controller {

    function Init() {
        $this->visor = new Visor();
        isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
    }

    function Run() {
        include 'reportes/m_auditor_venta.php';
        include 'reportes/t_auditor_venta.php';

        $this->Init();
        $result = "";
        $result_f = "";
        $form_search = false;
        $reporte = false;

        switch ($this->action) {

            case "Buscar":
                $reporte = true;
                break;

            default:
                $form_search = true;
        }

        if ($form_search) {
            $result = AuditorVentaTemplate::formSearch();
        }

        if ($reporte) {
            $ventas_contrometros = AuditorVentaModel::venta_combustible_contrometros($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $ventas_tickes_combustible = AuditorVentaModel::venta_combustible_tickes($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $ventas_tickes_market = AuditorVentaModel::venta_market_tickes($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $monto_registro_detallado = AuditorVentaModel::venta_refleja_registro_venta_detallado($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $monto_vales_generados = AuditorVentaModel::venta_vales_tickes($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $monto_facturado = AuditorVentaModel::venta_facturadas_liquidadas_anticipadas_normales($_REQUEST['ano'], $_REQUEST['mes'], $_REQUEST['estacion']);
            $ano_mes=$_REQUEST['ano']."-". $_REQUEST['mes'];
            $result_f = AuditorVentaTemplate::listado($_REQUEST['estacion'], $ventas_contrometros, $ventas_tickes_combustible, $ventas_tickes_market, $monto_registro_detallado, $monto_vales_generados,$monto_facturado,$ano_mes);
        }

        $this->visor->addComponent("ContentT", "content_title", AuditorVentaTemplate::Titulo());
        if ($result != "")
            $this->visor->addComponent("ContentB", "content_body", $result);
        if ($result_f != "")
            $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }

}

