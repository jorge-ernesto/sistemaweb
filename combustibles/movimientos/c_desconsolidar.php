<?php

class DesconsolidarController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'movimientos/m_desconsolidar.php';
		include 'movimientos/t_desconsolidar.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';

		$ip = "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		switch ($this->action) {

			case 'Desconsolidar':
				// echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";
				// die();

				$almacen	= trim($_REQUEST['almacen']);
				$fecha   = trim($_REQUEST['fecha']);
				$turno 	= trim($_REQUEST['turno']);

				$validar = DesconsolidarModel::FechaInicio($almacen);
				
				if ($validar == 1){
					echo '<script name="accion">alert("No se puede desconsolidar la fecha de inicio del Sistema") </script>';	
				} else {
					$result = DesconsolidarModel::desconsolidar($almacen, $fecha, $turno, $_SESSION['auth_usuario'], $ip);
					if($result === false){
						echo '<script name="accion">alert("Dia y turno no fueron consolidados") </script>';	
					}else{
						echo '<script name="accion">alert("Se desconsolido el dia '.$fecha.' ,  turno '.$turno.'.") </script>';
					}
					$almacenes 	= DesconsolidarModel::GetAlmacenes();
					$siguiente 	= DesconsolidarModel::obtenerSiguiente($almacen);
					$result 	= DesconsolidarTemplate::formDesconsolidar($siguiente, $almacenes, $almacen);		    		
			    		$this->visor->addComponent("ContentT", "content_title", DesconsolidarTemplate::titulo());				
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", "");			
				}

			break;

			case 'Desconsolidar dia y turno':
				// echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";
				// die();

				$almacen	= trim($_REQUEST['almacen_']);
				$fecha   = trim($_REQUEST['fecha_']);
				$turno 	= trim($_REQUEST['turno_']);

				$validar = DesconsolidarModel::FechaInicio($almacen);
				
				if ($validar == 1){
					echo '<script name="accion">alert("No se puede desconsolidar la fecha de inicio del Sistema") </script>';	
				} else {
					$result = DesconsolidarModel::desconsolidar($almacen, $fecha, $turno, $_SESSION['auth_usuario'], $ip);
					if($result === false){
						echo '<script name="accion">alert("Dia y turno no fueron consolidados") </script>';	
					}else{
						echo '<script name="accion">alert("Se desconsolido el dia '.$fecha.' ,  turno '.$turno.'.") </script>';
					}
					$almacenes 	= DesconsolidarModel::GetAlmacenes();
					$siguiente 	= DesconsolidarModel::obtenerSiguiente($almacen);
					$result 	= DesconsolidarTemplate::formDesconsolidar($siguiente, $almacenes, $almacen);		    		
			    		$this->visor->addComponent("ContentT", "content_title", DesconsolidarTemplate::titulo());				
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", "");			
				}
			break;

		   default:

				$almacenes 	= DesconsolidarModel::GetAlmacenes();
		    		$siguiente	= DesconsolidarModel::obtenerSiguiente($_REQUEST['almacen']);
				$result		= DesconsolidarTemplate::formDesconsolidar($siguiente, $almacenes, $_REQUEST['almacen']);

		    		$this->visor->addComponent("ContentT", "content_title", DesconsolidarTemplate::titulo());				
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");

			break;

		}		
	}
}
