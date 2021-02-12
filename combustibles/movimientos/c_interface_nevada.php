<?php
function unlinkRecursive($dir) {
	if(!$dh = @opendir($dir))
		return;
	while (false !== ($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..')
			continue;
		if (!@unlink($dir . '/' . $obj))
			unlinkRecursive($dir.'/'.$obj);
	}

	closedir($dh);

	return;
}

class InterfaceNevadaController extends Controller {
	function Init(){
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {
		$this->Init();
		$result = '';
		include('movimientos/m_interface_nevada.php');
		include('movimientos/t_interface_nevada.php');
		include('/sistemaweb/include/m_sisvarios.php');
		$this->visor->addComponent('ContentT', 'content_title', InterfaceNevadaTemplate::titulo());

		switch ($this->task) {
			case 'INTERFAZNEVADA':
				switch ($this->action) {
					case 'Procesar':
						$res = InterfaceNevadaModel::interface_fn_opensoft_nevada($_REQUEST['datos']['fechaini'],$_REQUEST['datos']['fechafin'],$_REQUEST['datos']['sucursal']);
//						echo "RESULT:".$res;
						if ($res==="INVALID_DATE") {
							$result = "<script language=\"javascript\">alert('Fecha no valida. Ambas fechas deben coincidir en el mismo mes.');</script>";
							$this->visor->addComponent("ContentE", "content_error", $result);
						} else if ($res==="PROCESS_EXECUTED") {
							$result = "<script language=\"javascript\">alert('Un proceso anterior ya migro informacion de esas fechas');</script>";
							$this->visor->addComponent("ContentE", "content_error", $result);
						} else {

							if (file_exists("/tmp/$res")){
								unlink("/tmp/$res");
							}

							$cmd = "zip -j -m /tmp/$res /home/data/*";							
							
							exec($cmd);

							header("Content-Type: application/x-zip-compressed");
							header('Content-Disposition: attachment; filename="' . $res . '"');
							//exec("chmod -R 777 /tmp/");
							readfile("/tmp/$res");
							//unlinkRecursive("/home/data");
							die("");
						}

						break;
					default:
						$CbSucursales = VariosModel::sucursalCBArray();
						$result = InterfaceNevadaTemplate::formInterfaceNevada(Array(),$CbSucursales);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
			}
			break;
		default:
			$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN INTERFACE NEVADA</h2>');
			break;
		}
	}
}
