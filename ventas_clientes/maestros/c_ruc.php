<?php
class RucController extends Controller {
	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

	function Run() {
		$this->Init();
		$result = '';
		include('maestros/m_ruc.php');
		include('maestros/t_ruc.php');
		include('../include/paginador_new.php');
		include('../lib/zip.lib.php');
		include('../lib/unzip.lib.php');
		$this->visor->addComponent('ContentT', 'content_title', RucTemplate::titulo());
		
		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}

		var_dump($_REQUEST);
		
		switch ($this->request) {
			case 'RUC':
			$tablaNombre = 'RUC';
			$listado = false;

			switch ($this->action) {
				case 'Agregar':
				$result = RucTemplate::formRuc(array());
				$this->visor->addComponent("ContentB", "content_body", $result);
				break;

				case 'Modificar':
				$record = RucModel::recuperarRegistroArray($_REQUEST["registroid"]);		    
				$result = RucTemplate::formRuc($record);
				$this->visor->addComponent("ContentB", "content_body", $result);
				break;

				case 'Reporte':
				$result = RucModel::ModelReportePDF($_REQUEST["busqueda"]);
				$record .= RucTemplate::TemplateReportePDF($result);
				$this->visor->addComponent("ContentB", "content_body", $record);
				break;
		
				case 'Eliminar':
				$result = RucModel::eliminarRegistro($_REQUEST["registroid"]);
				if($result == OK) {
					$listado= true;
				} else {
					$result = RucTemplate::errorResultado($result);
					$this->visor->addComponent("ContentB", "content_body", $result);
				}
				break;

				case 'TEXTO':
				$listado=false;
				break;

				case 'Guardar':
				$listado = false;
				if($_REQUEST['accion']=='actualizar') {
					$result = RucModel::actualizarRegistro($_REQUEST['ruc']['ruc'],$_REQUEST['ruc']['razsocial'],$_REQUEST['fecha']);
				} else {
					$result = RucModel::guardarRegistro($_REQUEST['ruc']['ruc'],$_REQUEST['ruc']['razsocial']);
					print_r($_REQUEST['ruc']);
				}

				if($result!='') {
					$result = RucTemplate::errorResultado('ERROR: RUC YA EXISTENTE');
					$this->visor->addComponent("error", "error_body", $result);
				} else {
					$result = RucTemplate::formRuc(array());
					$this->visor->addComponent("ContentB", "content_body", $result);
					$result = RucTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS '.$_REQUEST['ruc']['ruc'].' !!!');
					$this->visor->addComponent("error", "error_body", $result);
				}		
				break;

				case 'Buscar':
				$busqueda = RucModel::tmListado($_REQUEST['busqueda'],""/*$_REQUEST['desde']*/,""/*$_REQUEST['hasta']*/,$_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['tipobusqueda']);
				$result = RucTemplate::listado($busqueda['datos'],$_REQUEST['tipobusqueda']);
				$this->visor->addComponent("ListadoB", "resultados_grid", $result);
				break;

				case 'Generar':
				$rdata = RucModel::listarRucsMuertos($_REQUEST['desde'],$_REQUEST['hasta']);
				if(is_string($rdata)) {
					echo "<script>alert(\"" . $rdata . "\");</script>";
					break;
				}

				$qfiles = count($rdata);
				if((count($rdata) % 100) > 0) {
					$qfiles += 1;
				}

				$zf = new zipFile();
				$i  = 0;
				$p  = 1;
				$fc = "";
				foreach ($rdata as $rr) {
					if($i == 100) {								
						$zf->addFile($fc,"rucs".$p.".txt");
						$fc = "";
						$i  = 0;
						$p++;
					}

					$i++;
					$fc .= $rr . "|\n";
				}

				if($fc != "") {
					$zf->addFile($fc,"rucs" . $p . ".txt");
					$fc = "";
				}

				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=rucs.zip");
				header("Content-Description: RUCs");

				die($zf->file());
				break;

				case 'Importar':
				$busqueda = RucModel::tmListado($_REQUEST['busqueda'],$_REQUEST['desde'],$_REQUEST['hasta'],$_REQUEST['rxp'],$_REQUEST['pagina'],'1');
				$result  = RucTemplate::formBuscar($listado['paginacion'], '1', '','0','0','');
				$result .= RucTemplate::listado($busqueda['datos'],'1');
				$this->visor->addComponent("ContentB", "content_body", $result);
				break;

				case 'CIF OCS':
				$cifkey = RucModel::CIFKey();
				if($cifkey === NULL) {
					echo "<script>alert('La estacion no esta integrada con servidor CIF OCS');</script>\n";
					break;
				}

				$rdata = RucModel::listarRucsMuertos($_REQUEST['desde'],$_REQUEST['hasta']);
				if(is_string($rdata)) {
					echo "<script>alert(\"" . $rdata . "\");</script>";
					break;
						}

						$stats = Array(-2 => 0,-1 => 0,0 => 0,1 => 0,2 => 0,3 => 0);
						foreach ($rdata as $rr) {
							$cifdata = RucModel::CIFGet($rr,$cifkey);
							$stats[-2] += 1;
							$stats[$cifdata[0]] += 1;
							if ($cifdata[0] == 3)
								RucModel::RUCupsert($rr,$cifdata[1]);
						}

						$msg = "Procesados: {$stats[-2]}.";
						if ($stats[3] > 0) {
							$msg .= " Guardados: {$stats[3]}.";
						}

						if ($stats[1] > 0) {
							$msg .= " No validos: {$stats[1]}.";
						}

						if ($stats[2] > 0) {
							$msg .= " En cola: {$stats[2]}.";
						}

						if ($stats[-1] > 0) {
							$msg .= " Error Interno: {$stats[-1]}.";
						}

						if ($stats[0] > 0) {
							$msg .= " Error Remoto: {$stats[0]}.";
						}

						echo "<script>alert('{$msg}');</script>\n";
						break;

						case 'Cargar':
						$tfn = $_FILES['ubicacion']['tmp_name'];
						if($tfn != "") {
							$zf = new SimpleUnzip();
							$ze = $zf->ReadFile($tfn);
							// error_log('ze');
							// error_log(json_encode($ze));
							foreach ($ze as $de) {
								// error_log('de');
								// error_log(json_encode($de));
								$ls = explode("\n",$de->Data);
								// error_log('ls');
								// error_log(json_encode($ls));
								foreach ($ls as $ln => $ll) {
									if($ln > 0) {
										$lx = explode('|', $ll);
										$rx = RucModel::RUCupsert($lx[0],$lx[1]);
									}
								}
							}

							if (count($ze) == 0) {
								$result  = RucTemplate::formBuscar('', '1', '', '0','0','');
								$result .= RucTemplate::listado($busqueda['datos'],'1');
								$this->visor->addComponent("ContentB", "content_body", $result);
								echo "<script>alert('Datos cargados correctamente');</script>\n";
								break;
							}
						}

						$result  = RucTemplate::formBuscar('', '1', '', '0','0','');
						$result .= RucTemplate::listado($busqueda['datos'],'1');
						$this->visor->addComponent("ContentB", "content_body", $result);
						echo "<script>alert('Datos cargados correctamente');</script>\n";
						break;

						case 'RENIEC':
							$arrRequest = array(
								'dIni' => $_REQUEST['desde'],
								'dFin' => $_REQUEST['hasta'],
							);
						    $arrResponseDNI = RucModel::obtenerDNIPostransYM($arrRequest);
							if ($arrResponseDNI['sStatus'] == 'success') {
								$arrDniValidados = array();
								$t=0;
								$i=0;
								$a=0;
        						$ch = curl_init();
								foreach ($arrResponseDNI['arrData'] as $row) {
									$iDNI = trim($row['ruc']);
									$arrResponseApi = RucModel::apiReniec($ch, $iDNI);
									++$t;
									if ( $arrResponseApi['sStatus'] == 'success' ){
										RucModel::RUCupsert($iDNI,$arrResponseApi['sNombresApellidos']);
										++$i;
										$arrDniValidados['validos'][$i][] = $iDNI;
									} else {
										++$a;
										$arrDniValidados['no_validos'][$a][] = $iDNI;
									}
								}
								$msg = "Procesados: {$t}.";
								if ($i > 0) {
									$msg .= " Guardados: {$i}.";
								}
								if ($a > 0) {
									$msg .= " No validos: {$a}.";
								}
                    			echo "<script charset='utf8' type='text/javascript'>alert('{$msg}');</script>\n";
							} else {
                    			echo "<script charset='utf8' type='text/javascript'>alert('{$arrResponseDNI["sMessage"]}');</script>\n";
							}
						    break;

						default:
						$listado = true;
						break;
					}

					if($listado) {
						$listado = RucModel::tmListado('','','',$_REQUEST['rxp'],$_REQUEST['pagina'],'2');
						$result	= RucTemplate::formBuscar($listado['paginacion'], '0','','0','0','');
						$result	.= RucTemplate::listado($listado['datos'],'0');
						$this->visor->addComponent("ContentB", "content_body", $result);
					}
				}
			}
		}
