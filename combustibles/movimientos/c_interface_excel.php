<?php

function unlinkRecursive($dir) {
    if (!$dh = @opendir($dir)) {
        return;
    }
    while (false !== ($obj = readdir($dh))) {
        if ($obj == '.' || $obj == '..') {
            continue;
        }

        if (!@unlink($dir . '/' . $obj)) {
            unlinkRecursive($dir . '/' . $obj);
        }
    }
    closedir($dh);

    return;
}

class InterfaceMovController extends Controller {

    function Init() {
        $this->visor = new Visor();
        $this->task = @$_REQUEST["task"];
        $this->action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
        $this->datos = isset($_REQUEST["datos"]) ? $_REQUEST["datos"] : '';
    }

    function Run() {
        $this->Init();
        $result = '';
        include('movimientos/m_interface_excel.php');
        include('movimientos/t_interface_excel.php');
        include('/sistemaweb/include/m_sisvarios.php');
        $this->visor->addComponent('ContentT', 'content_title', InterfaceMovTemplate::titulo());

        switch ($this->task) {


            case 'INTERFAZBARRANCA':

                $CbSucursales = VariosModel::sucursalCBArray();
                //echo 'Entro a interfaz' ;

                switch ($this->action) {

                    case 'Actualizar':

//ini_set("memory_limit","20M");
                        $centro_costo = array("001" => array("004" => "ESTACION HUACHIPA"));
                        $info_are_facturas_boletas = InterfaceMovModel::ActualizarDatosFacturas($_REQUEST['datos']['fechaini']);
                        //$info_are_postrans = InterfaceMovModel::ActualizarDatosPostrans($_REQUEST['datos']['fechaini']);
                        InterfaceMovTemplate::reporteExcel($info_are_facturas_boletas,$info_are_postrans, $desde, $hasta, $tipo);
                       

                        break;

                    default:
                        $result = InterfaceMovTemplate::formInterfaceMov();
                        $this->visor->addComponent("ContentB", "content_body", $result);
                        break;
                }

                break;

            case 'SUNATDET':
                //Si hay detalles
                break;

            default:
                $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "' . $this->request . '" NO CONOCIDA EN REGISTROS</h2>');
                break;
        }
    }

}

