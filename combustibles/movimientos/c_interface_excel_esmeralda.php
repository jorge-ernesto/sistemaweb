<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

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

		include('movimientos/m_interface_excel_esmeralda.php');
		include('movimientos/t_interface_excel_esmeralda.php');
		include_once('../include/Classes/PHPExcel.php');

		$this->visor->addComponent('ContentT', 'content_title', InterfaceMovTemplateCE::titulo());

		switch ($this->task) {

			case 'INTERFAZESMERALDA':

				switch ($this->action) {

					case 'Actualizar':

						/*$info_are_facturas_boletas	= InterfaceMovModelCE::ActualizarDatosFacturas($_REQUEST['datos']['fechaini']);
						$tickes_anu			= InterfaceMovModelCE::getTickesAnulados($_REQUEST['datos']['fechaini']);
						$info_are_post			= InterfaceMovModelCE::ActualizarDatosPostrans($_REQUEST['datos']['fechaini'],$tickes_anu);
						$array_unior			= array_merge($info_are_facturas_boletas, $info_are_post);
						$fecha				= str_replace("-", "", $_REQUEST['datos']['fechaini']);

						InterfaceMovTemplateCE::reporteExcel($array_unior, $desde, $hasta, $tipo, $fecha);*/
						InterfaceMovTemplateCE::reporteExcel();
						 
					break;

					default:
				        	$result = InterfaceMovTemplateCE::formInterfaceMov();
				        	$this->visor->addComponent("ContentB", "content_body", $result);
				        break;

				}

		        break;

		   	default:
		       		$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "' . $this->request . '" NO CONOCIDA EN REGISTROS</h2>');
			break;

		}

	}

}

