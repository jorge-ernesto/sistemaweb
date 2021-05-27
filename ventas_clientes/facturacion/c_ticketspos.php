<?php

class TicketsPosController extends Controller {

    function Init() {
        $this->visor = new Visor();
        isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = '';
    }

    function Run() {

        ob_start();
        include 'facturacion/m_ticketspos.php';
        include 'facturacion/t_ticketspos.php';
        include('../include/paginador_new.php');

        $this->Init();
        $result = "";
        $result_f = "";
        $result_f2 = "";
        $form_search = false;
        $listado = false;

        //Obj
        $templateTicketPos = new TicketsPosTemplate();
        $modelTicketPos = new TicketsPosModel();

        $ip = "";
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];

        switch ($this->action) {

            case "Buscar":
                $listado = true;
                break;

            case "Acumulada":
                $file = "/sistemaweb/tmp/imprimir/acumula_turno.txt";
                $fh = fopen($file, "w");
                fwrite($fh, "");
                fclose($fh);

                $resu = $modelTicketPos->acumuladoTurno($_REQUEST['ch_almacen'], $_REQUEST['ch_tipo_consulta'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['ch_caja'], $_REQUEST['ch_tipo']);
                $texto_impresion = $templateTicketPos->imprimir($resu);
                
                // $cmd = $modelTicketPos->obtenerComandoImprimir($file);
                // exec($cmd);

                $response = $modelTicketPos->obtenerComandoImprimir($texto_impresion);
                $impresion = $response['impresion'];

                if ($impresion == true){
                    ?><script>alert('Imprimiendo venta acumulada por turno' );</script><?php
                }else{
                    ?><script>alert('No se pudo imprimir venta acumulada por turno' );</script><?php
                }
                break;

            case "AcumuladaExcel":
                $resu = $modelTicketPos->acumuladoTurno($_REQUEST['ch_almacen'], $_REQUEST['ch_tipo_consulta'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['ch_caja'], $_REQUEST['ch_tipo']);
                $result_f = $templateTicketPos->repExcel($resu);
                break;

            case "Bonus":
                $resultados = $modelTicketPos->reporte_bonus($ip, $_REQUEST['ch_tipo_consulta'], $_REQUEST['tm'], $_REQUEST['td'], $_REQUEST['Bonus'], $_REQUEST['ch_almacen'], $_REQUEST['ch_lado'], $_REQUEST['ch_caja'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['art_codigo'], $_REQUEST['ruc'], $_REQUEST['cuenta'], $_REQUEST['tarjeta'], $_REQUEST['ch_tipo']);
                ob_end_clean();
                $buff = "";
                for ($i = 0; $i < count($resultados); $i++) {
                    $A = $resultados[$i];
                    $buff .= "{$A['codigo']}" . chr(13) . chr(10);
                }
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=\"reporte_bonus.txt\"");
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                die($buff);
                break;

            case "Excel": //Boton Excel
                $tfpago = null;

                if (is_array($_REQUEST['tfpago']))
                    $tfpago[] = 1;

                if (is_array($_REQUEST['fpago']))
                    $tfpago[] = 2;

                $fpago = array();
                $fpago = $tfpago;

                $bonus = "";
                if (isset($_REQUEST['Bonus']))
                    $bonus = $_REQUEST['Bonus'];
                
                $listado_excel = $modelTicketPos->tmListado_Excel($_REQUEST['rxp'], $_REQUEST['pagina'], $_REQUEST['ch_tipo_consulta'], $_REQUEST['tm'], $_REQUEST['td'], $bonus, $_REQUEST['ch_almacen'], $_REQUEST['ch_lado'], $_REQUEST['ch_caja'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['art_codigo'], $_REQUEST['ruc'], $_REQUEST['cuenta'], $_REQUEST['tarjeta'], $_REQUEST['ch_tipo'], $fpago,  $_REQUEST['txtfeserie'], $_REQUEST['txtfenumero']);
                // echo "<script>console.log('" . json_encode($listado_excel) . "')</script>";                
                $_SESSION['info']  = $listado_excel['datos'];
                $_SESSION['info_'] = array('modo' => $_REQUEST['ch_tipo_consulta'], 'iYear' => $_REQUEST['ch_periodo'], 'iMonth' => $_REQUEST['ch_mes']);

                $listado = false;
                header("Location: /sistemaweb/ventas_clientes/reporte_tickes_ventas.php");
                break;

            default:
                $form_search = true;
                break;
        }

        if ($form_search) {
            $result = $templateTicketPos->formSearch();
        }

        if ($listado) {            
            $tfpago = null;

            if (isset($_REQUEST['tfpago']) && is_array($_REQUEST['tfpago']))
                $tfpago[] = 1;

            if (isset($_REQUEST['fpago']) && is_array($_REQUEST['fpago']))
                $tfpago[] = 2;

            $fpago = array();
            $fpago = $tfpago;

            $bonus = "";
            if (isset($_REQUEST['Bonus']))
                $bonus = $_REQUEST['Bonus'];
            
            $listado = $modelTicketPos->tmListado($_REQUEST['rxp'], $_REQUEST['pagina'], $_REQUEST['ch_tipo_consulta'], $_REQUEST['tm'], $_REQUEST['td'], $bonus, $_REQUEST['ch_almacen'], $_REQUEST['ch_lado'], $_REQUEST['ch_caja'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['art_codigo'], $_REQUEST['ruc'], $_REQUEST['cuenta'], $_REQUEST['tarjeta'], $_REQUEST['ch_tipo'], $fpago,  $_REQUEST['txtfeserie'], $_REQUEST['txtfenumero']);
            // echo "<script>console.log('" . json_encode($listado) . "')</script>";
            $vec = array($_REQUEST['ch_tipo_consulta'], $_REQUEST['tm'][0], $_REQUEST['tm'][1], $_REQUEST['tm'][2], $_REQUEST['td'][0], $_REQUEST['td'][1], $_REQUEST['td'][2], $bonus, $_REQUEST['ch_almacen'], $_REQUEST['ch_lado'], $_REQUEST['ch_caja'], $_REQUEST['ch_turno'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes'], $_REQUEST['ch_dia_desde'], $_REQUEST['ch_dia_hasta'], $_REQUEST['art_codigo'], $_REQUEST['ruc'], $_REQUEST['cuenta'], $_REQUEST['tarjeta'], $_REQUEST['ch_tipo'], $tfpago[0], $tfpago[1]);

            $result_f2 = $templateTicketPos->formPag($listado['paginacion'], $vec);
            $result_f = $templateTicketPos->listado($listado['datos'], $_REQUEST['ch_tipo_consulta'], $_REQUEST['ch_periodo'], $_REQUEST['ch_mes']);
        }

        $this->visor->addComponent("ContentT", "content_title", $templateTicketPos->titulo());
        if ($result != "")
            $this->visor->addComponent("ContentB", "content_body", $result);
        if ($result_f != "")
            $this->visor->addComponent("ContentF", "content_footer", $result_f2 . $result_f);
    }

}
