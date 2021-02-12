<?php
class ConsolidacionController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'movimientos/t_cuadre_ventas.php';
		include 'movimientos/m_cuadre_ventas.php';
		include 'movimientos/m_consolidacion.php';
		include 'movimientos/t_consolidacion.php';

		//get Class Template - Model
		$objConsolidacionModel = new ConsolidacionModel();
		$objCuadreVentasModel = new CuadreVentasModel();

		$this->Init();

		$result = "";
		$result_f = "";
		$result_x = "";

		$ip = "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		switch($this->action) {
			case "Buscar":
				if ( $_REQUEST['almacen'] == '' && empty($_REQUEST['almacen']) ) {
					echo '<script charset="utf-8" type="text/javascript">alert("Seleccionar almacén");window.close();</script>';
				} else {
					$resultados = ConsolidacionModel::buscar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['fecha2']);
					$result_f = ConsolidacionTemplate::resultadosBusqueda($resultados);
				}
			break;

			case "Consolidar":
			case "Consolidacion":
				/*
					* Parametros
					-sCodeWarehouse string(codigo de almacen)
					-dEntry date(fecha a consolidar) format YYYY-MM-DD
				*/
				$arrParams = array(
					'sCodeWarehouse' => $_REQUEST['almacen'],
					'dEntry' => $_REQUEST['dia'],
				);
				/**
				* Obtener el ultimo turno del dia a consolidar
				*/
				$arrResponse = $objConsolidacionModel->validateDateTurnLast($arrParams);
				if ($arrResponse['bStatus']){
					$bStatusDocumentPending = true;
					if ($arrResponse['sTurn']!=$_REQUEST['turno']){
						$bStatusDocumentPending = false;
					} else {
						/**
						* Obtener documentos electronicos no enviados
						* Se buscará registros del día que se está consolidando el último turno y anterior a esa fecha
						*/
						$arrResponseDocumentPending = $objConsolidacionModel->validateDocumentPending($arrParams);
					}

					if ( $bStatusDocumentPending==false || (($bStatusDocumentPending) && ($arrResponseDocumentPending['bStatus'])) ){
						/*
						$reporte = CuadreVentasModel::obtenerReporteTurno($_REQUEST['dia'],$_REQUEST['turno'],NULL);
						Agregado segun combobox almacen jala los sobrantes/faltantes
						*/
						$reporte = $objCuadreVentasModel->obtenerReporteTurnoConsolidacion($_REQUEST['dia'],$_REQUEST['turno'],NULL,$_REQUEST['almacen']);
						if ($reporte!==FALSE) {
							$validar = ConsolidacionModel::validarConsolidaciones($_REQUEST['almacen'], $_REQUEST['dia'], $_REQUEST['turno']);
							if($validar == 0 && $validar != NULL){//ACTUALIZO SOLO EL REGISTRO PORQUE YA LO INSERTE
									$actualizar = ConsolidacionModel::ActualizarConsolidaciones($reporte, $_REQUEST['almacen'], $_REQUEST['dia'], $_REQUEST['turno'], $_SESSION['auth_usuario'], $ip);

									if($actualizar == 1){
										$result_x = "<script>alert('Se completo la consolidacion del turno.')</script>";
										ConsolidacionModel::finalizaConsolidacion();
									} else if ($res=="ERROR_CON_PRE") {
										$result_x = "<script>alert('El turno ya fue consolidado. Intente nuevamente')</script>";
										break;
									} else if ($res=="ERROR_CON_INCOMPLETE") {
										$result_x = "<script>alert('La liquidacion del turno no esta completa. Debe matricular trabajadores en todos los lados y puntos.')</script>";
										break;
									} else if ($res=="INTERNAL_ERROR_CON2") {
										$result_x = "<script>alert('Falta asignar trabajadores en los lados revisar las matriculas')</script>";
										break;
									} else if ($res=="INTERNAL_ERROR_CON3") {
										$result_x = "<script>alert('Error al Consolidar')</script>";
									} else {
										ConsolidacionModel::revierteConsolidacion();
									}

							}else{//AGREGO EN CASO DE QUE NO EXISTA EL REGISTRO EN POS_CONSOLIDACION
								$res = ConsolidacionModel::consolidar($reporte, $_REQUEST['almacen'], $_REQUEST['dia'], $_REQUEST['turno'], $_SESSION['auth_usuario'], $ip);
								if (is_numeric($res)) {
									$eess = ConsolidacionModel::obtenerDatosEESS();
									$texto = ConsolidacionTemplate::generarWinchaReporte($reporte,$eess);
									$file = "/tmp/imprimir/Consolidacion_$res";
									$fh = fopen($file, "w");
									fwrite($fh,$texto);
									fclose($fh);
									$cmd = ConsolidacionModel::obtenerComandoImprimir($file);
								if ($this->action == "Consolidar")
										exec($cmd);
								} else if ($res=="ERROR_CON_PRE") {
									$result_x = "<script>alert('El turno ya fue consolidado. Intente nuevamente')</script>";
									break;
								} else if ($res=="ERROR_CON_INCOMPLETE") {
									$result_x = "<script>alert('La liquidacion del turno no esta completa. Debe matricular trabajadores en todos los lados y puntos.')</script>";
									break;
								} else if ($res=="INTERNAL_ERROR_CON2") {
									$result_x = "<script>alert('Falta asignar trabajadores en los lados revisar las matriculas')</script>";
									break;
								} else if ($res=="INTERNAL_ERROR_CON3") {
									$result_x = "<script>alert('Error al consolidar el turno. Intente nuevamente')</script>";
									break;
								} else if ($res=="INTERNAL_ERROR_CON4") {
									$result_x = "<script>alert('Error al consolidar el turno. Intente nuevamente')</script>";
									break;
								}trigger_error("XR: {$result_x}");
								if ($result_x == "") {
									if ($this->action == "Consolidar")
										$result_x = "<script>alert('Se completo la consolidacion del turno. Se ha enviado a imprimir las winchas.')</script>";
									else
										$result_x = "<script>alert('Se completo la consolidacion del turno.')</script>";
									ConsolidacionModel::finalizaConsolidacion();
								} else {
									ConsolidacionModel::revierteConsolidacion();
								}
							}
						} else {
							$result_x = "<script>alert('Error al consolidar el turno. Verifique la matricula de trabajadores.')</script>";
							break;
						}
					} else {
						echo '<script charset="utf-8" type="text/javascript">alert("' . $arrResponseDocumentPending['sMessage'] . '");window.close();</script>';
						break;						
					}
				}
			default:

				$siguiente 	= ConsolidacionModel::obtenerSiguiente($_REQUEST['almacen']);
				$almacen 	= ConsolidacionModel::GetAlmacenes();
				$result 	= ConsolidacionTemplate::formSearch($siguiente, $almacen, $_REQUEST['almacen']);
			break;
		}

		$this->visor->addComponent("ContentT", "content_title", ConsolidacionTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
		if ($result_x != "") $this->visor->addComponent("ContentX", "content_X", $result_x);

	}

}

						

