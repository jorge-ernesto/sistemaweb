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

class InterfaceDominioController extends Controller {

	function Init() {	      
	      $this->visor = new Visor();
	      $this->task = @$_REQUEST["task"];
	      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

	function Run() {
      		$this->Init();
      		$result = '';
      		include('movimientos/m_interface_dominio.php');
      		include('movimientos/t_interface_dominio.php');
      		include('/sistemaweb/include/m_sisvarios.php');
      		$this->visor->addComponent('ContentT', 'content_title', InterfaceDominioTemplate::titulo());
               	
		switch ($this->task) {
			case 'INTERFAZDOMINIO':         				
				switch ($this->action) {
				
					case 'Procesar': 
					
						$almacen = $_REQUEST['almacen'];
						$desde 	 = $_REQUEST['desde'];	
						$hasta 	 = $_REQUEST['hasta'];	
						
						list($dia1, $mes1, $anio1) = explode('[-/]', $desde);
						list($dia2, $mes2, $anio2) = explode('[-/]', $hasta);		
																				
						$resus = InterfaceDominioModel::procesarInterface($almacen, $desde, $hasta);

						if (file_exists("/tmp/data.zip")) 
							unlink("/tmp/data.zip");

						$cmd = "zip -j -m /tmp/data.zip /home/data/*";
						exec($cmd);

						$archivo = "Datos_".$almacen."_del_".$dia1.$mes1.$anio1."_al_".$dia2.$mes2.$anio2.".zip";
						header("Content-Type: application/x-zip-compressed");
						header('Content-Disposition: attachment; filename="'.$archivo.'"');
						readfile("/tmp/data.zip");
						unlinkRecursive("/home/data");					
						break;
		
					default:
				   		$result = InterfaceDominioTemplate::formBuscar("", date("d/m/Y"),date("d/m/Y"));
				   		$this->visor->addComponent("ContentB", "content_body", $result);				 
						break;
				}
				break;
				
			default:
				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
				break;
		}  
	}
}
