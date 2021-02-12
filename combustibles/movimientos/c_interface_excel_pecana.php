<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

class InterfaceMovControllerCE extends Controller {
    function Init() {
        $this->visor = new Visor();
        $this->task = @$_REQUEST["task"];
        $this->action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
        $this->datos = isset($_REQUEST["datos"]) ? $_REQUEST["datos"] : '';
    }

    function Run() {
        $this->Init();
        $result = '';
        include('movimientos/m_interface_excel_pecana.php');
        include('movimientos/t_interface_excel_pecana.php');
        include('/sistemaweb/include/m_sisvarios.php');
        include_once('../include/Classes/PHPExcel.php');
        //$this->visor->addComponent('ContentT', 'content_title', InterfaceMovTemplateCE::titulo());

        switch ($this->task) {
            case 'INTERFAZPECANA':

                $CbSucursales = VariosModel::sucursalCBArray();
                switch ($this->action) {
                    case 'Generar':
                        $cod_sucursal = $_REQUEST['datos']['sucursal'];
                        $cod_pecana = $_REQUEST['datos']['cod_pecana'];
                        $sYearMonth = $_REQUEST['year'].'-'.$_REQUEST['month'];

                        $info_are_facturas_boletas = InterfaceMovModelCE::ActualizarDatosFacturas($sYearMonth, $cod_sucursal, $cod_pecana);
                        $tickes_anu = InterfaceMovModelCE::getTickesAnulados($sYearMonth, $cod_sucursal);
                        $info_are_post = InterfaceMovModelCE::ActualizarDatosPostrans($sYearMonth, $tickes_anu, $cod_sucursal, $cod_pecana);
                        $array_unior = array_merge($info_are_facturas_boletas, $info_are_post);
                        $fecha = str_replace("-", "", $sYearMonth);
                        /*echo "<pre>";
                        var_dump($array_unior);
                        echo "</pre>";*/
                        InterfaceMovTemplateCE:: reporteExcelPersonalizado($array_unior);
                        break;

                    default:
                        $result = InterfaceMovTemplateCE::formInterfaceMov();
                        $this->visor->addComponent("ContentB", "content_body", $result);
                        break;
                }

                break;

            case 'SUNATDET':
                break;

            default:
                $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "' . $this->request . '" NO CONOCIDA EN REGISTROS</h2>');
                break;
        }
    }
}

