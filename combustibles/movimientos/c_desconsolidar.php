<?php

class DesconsolidarController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		include 'movimientos/m_desconsolidar.php';
		include 'movimientos/t_desconsolidar.php';
		include 'movimientos/m_consolidacion.php';
		include 'movimientos/m_asientos_contables.php';
	
		//get Class Template - Model
		$objConsolidacionModel = new ConsolidacionModel();
		$objAsientosContablesModel = new AsientosContablesModel();

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

				$almacen = TRIM($_REQUEST['almacen']);
				$fecha   = TRIM($_REQUEST['fecha']);
				$turno 	 = TRIM($_REQUEST['turno']);
				$validar = DesconsolidarModel::FechaInicio($almacen);
				
				if ($validar == 1){
					echo '<script name="accion">alert("No se puede desconsolidar la fecha de inicio del Sistema") </script>'; 
				} else {
					//COMENZAMOS TRANSACCION
					DesconsolidarModel::EmpezarDesconsolidacion();

					//DESCONSOLIDAMOS DIA Y TURNO
					$result = DesconsolidarModel::desconsolidar($almacen, $fecha, $turno, $_SESSION['auth_usuario'], $ip);

					//VALIDAMOS DESCONSOLIDACION
					if ($result == FALSE) {
						echo '<script name="accion">alert("Problema al desconsolidar. Dia y turno no fueron consolidados") </script>';	
						DesconsolidarModel::RevertirDesconsolidacion();
					} else {

						//SI ACCOUTING_ENABLED ESTA HABILITADO, ELIMINA ASIENTOS
						$arrResponse = $objAsientosContablesModel->getAccoutingEnabled();
						if ($arrResponse['bStatus'] == TRUE) {
							//VALIDA QUE SEA EL ULTIMO TURNO DEL DIA PARA ELIMINAR ASIENTOS
							$arrParams   = array('sCodeWarehouse' => $almacen, 'dEntry' => $fecha);
							$arrResponse = $objConsolidacionModel->validateDateTurnLast($arrParams);
							if ($arrResponse['sTurn'] == $turno) {
								//ELIMINAMOS ASIENTOS POR ALMACEN Y FECHA DE DESCONSOLIDACION
								$arrResponse = $objAsientosContablesModel->eliminarAsientos($arrParams);
								if ($arrResponse['error']) {
									echo '<script name="accion">alert("Problema al desconsolidar. Problema al eliminar asientos contables. '.$arrResponse['message'].'") </script>';
									DesconsolidarModel::RevertirDesconsolidacion();
								} else {
									echo '<script name="accion">alert("Se desconsolido el dia '.$fecha.' ,  turno '.$turno.'.") </script>';
									echo '<script name="accion">alert("Se eliminaron asientos contables.") </script>';
									DesconsolidarModel::FinalizarDesconsolidacion();
								}
							} else {
								echo '<script name="accion">alert("Se desconsolido el dia '.$fecha.' ,  turno '.$turno.'.") </script>';
								DesconsolidarModel::FinalizarDesconsolidacion();
							}
						} else {
							echo '<script name="accion">alert("Se desconsolido el dia '.$fecha.' ,  turno '.$turno.'.") </script>';
							DesconsolidarModel::FinalizarDesconsolidacion();
						}
					}		
				}

				$almacenes 	= DesconsolidarModel::GetAlmacenes();
				$siguiente 	= DesconsolidarModel::obtenerSiguiente($almacen);
				$result 	= DesconsolidarTemplate::formDesconsolidar($siguiente, $almacenes, $almacen);		    		
				$this->visor->addComponent("ContentT", "content_title", DesconsolidarTemplate::titulo());				
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
			break;

			case 'Desconsolidar dia y turno':
				// echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";
				// die();

				$almacen = TRIM($_REQUEST['almacen_']);
				$fecha   = TRIM($_REQUEST['fecha_']);
				$turno 	 = TRIM($_REQUEST['turno_']);
				$validar = DesconsolidarModel::FechaInicio($almacen);
				
				if ($validar == 1){
					echo '<script name="accion">alert("No se puede desconsolidar la fecha de inicio del Sistema") </script>'; 
				} else {
					//COMENZAMOS TRANSACCION
					DesconsolidarModel::EmpezarDesconsolidacion();

					//DESCONSOLIDAMOS DIA Y TURNO
					$result = DesconsolidarModel::desconsolidar($almacen, $fecha, $turno, $_SESSION['auth_usuario'], $ip);

					//VALIDAMOS DESCONSOLIDACION
					if ($result == FALSE) {
						echo '<script name="accion">alert("Problema al desconsolidar. Dia y turno no fueron consolidados") </script>';	
						DesconsolidarModel::RevertirDesconsolidacion();
					} else {

						//SI ACCOUTING_ENABLED ESTA HABILITADO, ELIMINA ASIENTOS
						$arrResponse = $objAsientosContablesModel->getAccoutingEnabled();
						if ($arrResponse['bStatus'] == TRUE) {
							//VALIDA QUE SEA EL ULTIMO TURNO DEL DIA PARA ELIMINAR ASIENTOS
							$arrParams   = array('sCodeWarehouse' => $almacen, 'dEntry' => $fecha);
							$arrResponse = $objConsolidacionModel->validateDateTurnLast($arrParams);
							if ($arrResponse['sTurn'] == $turno) {
								//ELIMINAMOS ASIENTOS POR ALMACEN Y FECHA DE DESCONSOLIDACION
								$arrResponse = $objAsientosContablesModel->eliminarAsientos($arrParams);
								if ($arrResponse['error']) {
									echo '<script name="accion">alert("Problema al desconsolidar. Problema al eliminar asientos contables. '.$arrResponse['message'].'") </script>';
									DesconsolidarModel::RevertirDesconsolidacion();
								} else {
									echo '<script name="accion">alert("Se desconsolido el dia '.$fecha.' ,  turno '.$turno.'.") </script>';
									echo '<script name="accion">alert("Se eliminaron asientos contables.") </script>';
									DesconsolidarModel::FinalizarDesconsolidacion();
								}
							} else {
								echo '<script name="accion">alert("Se desconsolido el dia '.$fecha.' ,  turno '.$turno.'.") </script>';
								DesconsolidarModel::FinalizarDesconsolidacion();
							}
						} else {
							echo '<script name="accion">alert("Se desconsolido el dia '.$fecha.' ,  turno '.$turno.'.") </script>';
							DesconsolidarModel::FinalizarDesconsolidacion();
						}
					}		
				}

				$almacenes 	= DesconsolidarModel::GetAlmacenes();
				$siguiente 	= DesconsolidarModel::obtenerSiguiente($almacen);
				$result 	= DesconsolidarTemplate::formDesconsolidar($siguiente, $almacenes, $almacen);		    		
				$this->visor->addComponent("ContentT", "content_title", DesconsolidarTemplate::titulo());				
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");			
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
