<?php

function unlinkRecursive($dir) {
	if(!$dh = @opendir($dir)) {
		return;
	}
	while (false !== ($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..') {
			continue;
		}

		if (!@unlink($dir . '/' . $obj)) {
		unlinkRecursive($dir.'/'.$obj);
		}
	}
	closedir($dh);

	return;
}

class InterfaceMovController extends Controller {

	function Init() {	      
	      $this->visor = new Visor();
	      $this->task = @$_REQUEST["task"];
	      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {
      		$this->Init();
      		$result = '';
      		include('movimientos/m_interface_iridium.php');
      		include('movimientos/t_interface_iridium.php');
      		include('/sistemaweb/include/m_sisvarios.php');
      		$this->visor->addComponent('ContentT', 'content_title', InterfaceMovTemplate::titulo());
               	
		switch ($this->task) {

			case 'INTERFAZIRIDIUM':
			
				$CbSucursales = VariosModel::sucursalCBArray();
              			//echo 'Entro a interfaz' ;
				
				switch ($this->action) {
				
					case 'Actualizar': {
										
						error_log("Request");
						error_log(json_encode($_REQUEST));
						$Funcion = InterfaceMovModel::ActualizarInterfaces($_REQUEST['datos']['fechaini'],$_REQUEST['datos']['sucursal'],$_REQUEST['datos']['modulos']);
						error_log("ActualizarInterfaces");
						error_log(json_encode($Funcion));

						$Resultados[$llave] = $Funcion;

						if (file_exists("/tmp/data.zip")){ //Verifica que el archivo exista
							unlink("/tmp/data.zip"); //Elimina archivo
						}

						$cmd = "zip -j -m /tmp/data.zip /home/jlachira/*";
						exec($cmd);

						list($dia, $mes, $ano) = explode('[-/]', $_REQUEST['datos']['fechaini']);
						$archivo = substr($_REQUEST['datos']['sucursal'],1).$dia.$mes.substr($ano, 2).".zip";
						header("Content-Type: application/x-zip-compressed");
						header('Content-Disposition: attachment; filename="' . $archivo . '"');
						readfile("/tmp/data.zip");
						unlinkRecursive("/home/jlachira");
			
					}
					break;
		
					default:
				   		$result = InterfaceMovTemplate::formInterfaceMov(array());
				   		$this->visor->addComponent("ContentB", "content_body", $result);				 
						break;
				}

				break;
				
			case 'SUNATDET':
				//Si hay detalles
				break;
		
			default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
				break;
		}  
	}
}
