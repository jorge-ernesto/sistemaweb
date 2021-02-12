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

class InterfaceExactusController extends Controller {
	function Init(){
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {
		$this->Init();
		$result = '';
		include('movimientos/m_interface_exactus.php');
		include('movimientos/t_interface_exactus.php');
		include('/sistemaweb/include/m_sisvarios.php');
		$this->visor->addComponent('ContentT', 'content_title', InterfaceExactusTemplate::titulo());

		switch ($this->task) {
			case 'INTERFAZEXACTUS':
				switch ($this->action) {
					case 'Procesar':
						InterfaceExactusModel::actualizarParametros($_REQUEST['par_3doserver'],$_REQUEST['par_3douser'],$_REQUEST['par_3dopass'],$_REQUEST['par_3dodb']);
						$Parametros = InterfaceExactusModel::obtenerParametros();
						?><script>//alert("<?php echo $Parametros[0].'-'.$Parametros[1].'-'.$Parametros[2].'-'.$Parametros[3].'-'.$_REQUEST['datos']['fechaini'].'-'.$_REQUEST['datos']['fechafin'].'-'.$_REQUEST['datos']['sucursal']; ?>");</script><?php
						$res = InterfaceExactusModel::ActualizarInterfaces($Parametros,$_REQUEST['datos']['fechaini'],$_REQUEST['datos']['fechafin'],$_REQUEST['datos']['sucursal']);
						echo "RESULT:".$res;
						if ($res==="INVALID_DATE") {
							$result = "<script language=\"javascript\">alert('Fecha no valida. Ambas fechas deben coincidir en el mismo mes.');</script>";
							$this->visor->addComponent("ContentE", "content_error", $result);
						} else if ($res==="PROCESS_EXECUTED") {
							$result = "<script language=\"javascript\">alert('Un proceso anterior ya migro informacion de esas fechas');</script>";
							$this->visor->addComponent("ContentE", "content_error", $result);
						} else if ($res==="CONNECT_EXACTUS") {
							$result = "<script language=\"javascript\">alert('Error de conexion al servidor Exactus. Intente mas tarde');</script>";
							$this->visor->addComponent("ContentE", "content_error", $result);
						} else {
							$result = InterfaceExactusTemplate::imprimeResultado($res);
							$this->visor->addComponent("ContentB", "content_body", $result);
						}

						break;

					case 'Consultar':
						$result_1 = InterfaceExactusModel::ConsultaProcesos($_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'],$_REQUEST['datos']['sucursal']);
						$result = InterfaceExactusTemplate::imprimeConsulta($result_1,$_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'], $_REQUEST['datos']['sucursal']);
						$this->visor->addComponent("ContentF", "content_footer", $result);
						break;

					case 'Eliminar':
						$result_0 = InterfaceExactusModel::Eliminar($_POST['radio_eliminar']);
						$result_1 = InterfaceExactusModel::ConsultaProcesos($_REQUEST['f_inicio'], $_REQUEST['f_fin'],$_REQUEST['f_sucursal']);
						$result = InterfaceExactusTemplate::imprimeConsulta($result_1,$_REQUEST['f_inicio'], $_REQUEST['f_fin'],$_REQUEST['f_sucursal']);
						$this->visor->addComponent("ContentF", "content_footer", $result);
						break;
						?><script>//alert("<?php echo $_REQUEST['datos']['fechaini'].'-'.$_REQUEST['f_inicio']?>");</script><?php
						/*$result_1 = Interface3DOModel::ConsultaProcesos($_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'],$_REQUEST['datos']['sucursal']);
						$result = InterfaceExactusTemplate::imprimeConsulta($result_1,$_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'], $_REQUEST['datos']['sucursal']);
						$this->visor->addComponent("ContentF", "content_footer", $result);
						break;*/

					default:
						$CbSucursales = VariosModel::sucursalCBArray();
						$Parametros = InterfaceExactusModel::obtenerParametros();
						$result = InterfaceExactusTemplate::formInterfaceExactus(Array(),$CbSucursales,$Parametros);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
			}
			break;

		default:
			$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN INTERFACE EXACTUS</h2>');
			break;
		}
	}
}
