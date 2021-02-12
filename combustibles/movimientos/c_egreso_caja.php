<?php

class RegistroCajaController extends Controller {

    function Init() {
        $this->visor = new Visor();
        isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
    }

    function Run() {

        include 'movimientos/m_ingreso_caja.php';
        include 'movimientos/t_ingreso_caja.php';

        $this->Init();

        $result = "";
        $result_f = "";

        $form_search = false;
        $listado = false;
        $ajax = false;

        switch ($this->action) {

            case "Buscar":
                $listado = true;
                break;


            case "Registro":
                $result = RegistroCajasTemplate::formSearchRegistro(trim($_REQUEST['fecha']), trim($_REQUEST['fecha2']), $_REQUEST['estacion']);
                echo "Ingrese al sistema de web .";
                break;

            case "num_recibo":
                $_almacen = $_REQUEST['almacen'];
                $num_recibo = RegistroCajasModel::ObtenerNroDocuemnto_Recibo($_almacen);
                $resultado='{"dato":"'.$num_recibo.'"}';
                $ajax = true;
                break;

            default:
                $form_search = true;
                break;
        }

        if ($form_search) {
            $result = RegistroCajasTemplate::formSearch(date(d . "/" . m . "/" . Y), date(d . "/" . m . "/" . Y), $_REQUEST['estacion'], $_REQUEST['turno']);
        }

        if ($listado) {
            $result = RegistroCajasTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion'], $_REQUEST['turno']);

            //$result_f = RegistroCajasTemplate::listado($resultado, $_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['turno']);
        }
        echo $result;
        /*if (!$ajax) {
            $this->visor->addComponent("ContentT", "content_title", RegistroCajasTemplate::titulo());
            if ($result != "")
                $this->visor->addComponent("ContentB", "content_body", $result);
            if ($result_f != "")
                $this->visor->addComponent("ContentF", "content_footer", $result_f);
        }else {


            echo $resultado;
        }*/
    }

}

