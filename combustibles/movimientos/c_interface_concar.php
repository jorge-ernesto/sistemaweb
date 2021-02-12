<?php

function unlinkRecursive($dir) {
    if (!$dh = @opendir($dir))
        return;
    while (false !== ($obj = readdir($dh))) {
        if ($obj == '.' || $obj == '..')
            continue;
        if (!@unlink($dir . '/' . $obj))
            unlinkRecursive($dir . '/' . $obj);
    }

    closedir($dh);

    return;
}

class InterfaceConcarController extends Controller {

    function Init() {
        $this->visor = new Visor();
        $this->task = @$_REQUEST["task"];
        $this->action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
        $this->datos = isset($_REQUEST["datos"]) ? $_REQUEST["datos"] : '';
    }

    function Run() {
        $this->Init();
        $result = '';
        include('movimientos/m_interface_concar.php');
        include('movimientos/t_interface_concar.php');
        include('/sistemaweb/include/m_sisvarios.php');
        $this->visor->addComponent('ContentT', 'content_title', InterfaceConcarTemplate::titulo());

        switch ($this->task) {
            case 'INTERFAZCONCAR':
                switch ($this->action) {
                    case 'Procesar':
                        if ($_REQUEST["comboTipo"] == '1') {
                            $res = InterfaceConcarModel::interface_fn_opensoft_concar_ventas($_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'], $_REQUEST['datos']['sucursal']);
                        } else {
                            $res = InterfaceConcarModel::interface_fn_opensoft_concar_cuentascobrar($_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'], $_REQUEST['datos']['sucursal']);
                        }

                        //$res = InterfaceConcarModel::interface_fn_opensoft_concar($_REQUEST['datos']['fechaini'],$_REQUEST['datos']['fechafin'],$_REQUEST['datos']['sucursal']);

                        if ($res === "INVALID_DATE") {
                            $result = "<script language=\"javascript\">alert('Fecha no valida. Ambas fechas deben coincidir en el mismo mes y anio.');</script>";
                            $this->visor->addComponent("ContentE", "content_error", $result);
                        } else if ($res === "PROCESS_EXECUTED") {
                            $result = "<script language=\"javascript\">alert('Un proceso anterior ya migro informacion de esas fechas');</script>";
                            $this->visor->addComponent("ContentE", "content_error", $result);
                        } else {
                            if (file_exists("/tmp/$res"))
                                unlink("/tmp/$res");

                            $cmd = "zip -j -m /tmp/$res /home/data/*";
                            exec($cmd);

                            header("Content-Type: application/x-zip-compressed");
                            header('Content-Disposition: attachment; filename="' . $res . '"');
                            readfile("/tmp/$res");
                            unlinkRecursive("/home/data");
                            die("");
                            //$result = InterfaceConcarTemplate::formResultados($res);
                            //$this->visor->addComponent("ContentB", "content_footer", $result);
                        }

                        break;
                    default:
                        $CbSucursales = VariosModel::sucursalCBArray2();
                        $result = InterfaceConcarTemplate::formInterfaceConcar(Array(), $CbSucursales);
                        $this->visor->addComponent("ContentB", "content_body", $result);
                        break;
                }
                break;
            default:
                $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "' . $this->request . '" NO CONOCIDA EN INTERFACE CONCAR</h2>');
                break;
        }
    }

}

