<?php

class AnularTickesController extends Controller {

    function Init() {
        $this->visor = new Visor();
        isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
    }

    function Run() {

        include 'movimientos/m_anular_tickes.php';
        include 'movimientos/t_anular_tickes.php';

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
                $result = AnularTickesTemplate::formSearchRegistro(trim($_REQUEST['fecha']), trim($_REQUEST['fecha2']), $_REQUEST['estacion']);
                echo "Ingrese al sistema de web .";
                break;

            case "num_recibo":
                $_almacen = $_REQUEST['almacen'];
                $num_recibo = AnularTickesModel::ObtenerNroDocuemnto_Recibo($_almacen);
                $resultado='{"dato":"'.$num_recibo.'"}';
                $ajax = true;
                break;

            default:
                $form_search = true;
                break;
        }

        if ($form_search) {
            $result = AnularTickesTemplate::formSearch(date(d . "/" . m . "/" . Y), date(d . "/" . m . "/" . Y), $_REQUEST['estacion'], $_REQUEST['turno']);
        }

        if ($listado) {
            $result = AnularTickesTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion'], $_REQUEST['turno']);

            //$result_f = RegistroCajasTemplate::listado($resultado, $_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['turno']);
        }
        echo $result;
        
    }

}

