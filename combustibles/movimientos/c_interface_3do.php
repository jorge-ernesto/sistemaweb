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

class Interface3DOController extends Controller {
	function Init(){
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {
		$this->Init();
		$result = '';
		include('movimientos/m_interface_3do.php');
		include('movimientos/t_interface_3do.php');
		include('/sistemaweb/include/m_sisvarios.php');
		$this->visor->addComponent('ContentT', 'content_title', Interface3DOTemplate::titulo());

		switch ($this->task) {
			case 'INTERFAZ3DO':
				switch ($this->action) {
					case 'Procesar':
						Interface3DOModel::actualizarParametros($_REQUEST['par_3doserver'],$_REQUEST['par_3douser'],$_REQUEST['par_3dopass'],$_REQUEST['par_3dodb']);
						$Parametros = Interface3DOModel::obtenerParametros();
						$res = Interface3DOModel::ActualizarInterfaces($Parametros,$_REQUEST['datos']['fechaini'],$_REQUEST['datos']['fechafin'],$_REQUEST['datos']['sucursal'], $_REQUEST['agrupado']);
						
						/*Mensaje del confirmacion*/
						$result = Interface3DOTemplate::imprimeResultado($res);
						$this->visor->addComponent("ContentE", "content_error", $result);
						
						if($res === TRUE){
							echo "<script>alert('Se ha copiado la información al 3DO')</script>";
						}else{
							echo "<script>alert('No se pudo copiar la información al 3DO')</script>";							
						}
						/*Fin de mensaje de confirmacion*/

						break;

					case 'Consultar':
						$result_1 = Interface3DOModel::ConsultaProcesos($_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'],$_REQUEST['datos']['sucursal']);
						$result = Interface3DOTemplate::imprimeConsulta($result_1,$_REQUEST['datos']['fechaini'], $_REQUEST['datos']['fechafin'], $_REQUEST['datos']['sucursal']);
						$this->visor->addComponent("ContentF", "content_footer", $result);
						break;

					case 'Eliminar':
						$Parametros = Interface3DOModel::obtenerParametros();
						$result_0 = Interface3DOModel::Eliminar($_POST['radio_eliminar'],$Parametros);
						if (is_string($result_0))
							echo "<script>alert(\"Error al eliminar informacion: " . addslashes($result_0) . "\");</script>";
						$result_1 = Interface3DOModel::ConsultaProcesos($_REQUEST['f_inicio'], $_REQUEST['f_fin'],$_REQUEST['f_sucursal']);
						$result = Interface3DOTemplate::imprimeConsulta($result_1,$_REQUEST['f_inicio'], $_REQUEST['f_fin'],$_REQUEST['f_sucursal']);
						$this->visor->addComponent("ContentF", "content_footer", $result);
						break;

					default:
						$Almacenes = Interface3DOModel::ListadoAlmacenes();
						$Parametros = Interface3DOModel::obtenerParametros();
						$result = Interface3DOTemplate::formInterface3DO(Array(),$Almacenes,$Parametros);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;
			}
			break;

		default:
			$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN INTERFACE 3DO</h2>');
			break;
		}
	}
}
