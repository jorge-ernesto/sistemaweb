<?php

date_default_timezone_set('America/Lima');

class FacturasController extends Controller {

    function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
		if (isset($_REQUEST["datos"]))
		    $_REQUEST['datos']['dt_fac_fecha'] = $_REQUEST['dt_fac_fecha'];
		$this->datos = isset($_REQUEST["datos"]) ? $_REQUEST["datos"] : '';
    }

	function Run() {

		$this->Init();

		$result = '';

		include('facturacion/m_facturas.php');
		include('facturacion/t_facturas.php');
		include('../include/paginador_new.php');
		include('../include/m_sisvarios.php');

		$this->visor->addComponent('ContentT', 'content_title', FacturasTemplate::titulo());
		if (!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
		    	$_REQUEST['rxp'] = 100;
		    	$_REQUEST['pagina'] = 0;
		}

		switch ($this->request) {
		
		    	case 'FACTURAS':

		        	$listado = false;

		        	switch ($this->action) {

		            	case 'Contado':

							$result = FacturasModel::pasaraContado($_REQUEST["registroid"]);

							if ($result) {
								$search = FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
								$result = FacturasTemplate::listado($search['datos']);
								$this->visor->addComponent("error", "error_body", " ");
								$this->visor->addComponent("ListadoB", "resultados_grid", $result);
							} else {
								$result = FacturasTemplate::errorResultado($result);
								$this->visor->addComponent("error", "error_body", $result);
							}

						break;

					case 'Reporte':
						$result = FacturasTemplate::formReporte();
						$this->visor->addComponent("ContentB", "content_body", $result);
					break;

					case 'Agregar':
						$result = FacturasTemplate::formFacturas(null,null,null,null,null,null,null,null);
						$this->visor->addComponent("ContentB", "content_body", $result);
					break;

		           	case 'Anular':


						if(ereg("[A-Z]+", $_REQUEST['ch_fac_seriedocumento'])){

			           		/* VALIDACION DE DOCUMENTO ELECTRONICO ENVIADO */
							$valida_estado_documento_electronico = FacturasModel::Verify_Status_Document_Electronic_Anulado($_REQUEST['_id']);

							if($valida_estado_documento_electronico){

								/* VALIDACION PARA DOCUMENTOS ANULADOS. LA FECHA DEBE DE SER HOY O MAXIMO AYER */

								//FECHA DE EMISION - SISTEMAWEB
								$fe_emision = substr($_REQUEST['dt_fac_fecha'],6,4).'-'.substr($_REQUEST['dt_fac_fecha'],3,2).'-'.substr($_REQUEST['dt_fac_fecha'],0,2);

								$localtime 	= localtime();
								$day 		= $localtime[3];

								if(strlen($day) < 2 )
									$day = '0'.$day;

								$month = $localtime[4] + 1;

								if(strlen($day) < 2 )
									$month = '0'.$month;						

								$year 			= '20'.substr($localtime[5], -2);
								$today 			= $year.'-'.$month.'-'.$day;
								$today_before 	= date_create($year.'-'.$month.'-'.$day);

								/* RESTRICCION PARA FECHA DE F.E MENOR A SOLO ANULADOS 1 DIAS */
								date_add($today_before, date_interval_create_from_date_string('-1 days'));
								$today_before = date_format($today_before, 'Y-m-d');

								if($fe_emision <= $today){
									if($fe_emision >= $today_before){
										$complete 	= FacturasModel::Action_Complete_FE($_REQUEST);//FE = FACTURACION ELECTRONICA
										if($complete){
											$result = FacturasModel::anulacionRegistro($_REQUEST["registroid"]);
											if ($result == 'OK') {
												$busqueda 	= FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
												$result = FacturasTemplate::listado($busqueda['datos'], $_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
												$this->visor->addComponent("error", "error_body", " ");
												$this->visor->addComponent("ListadoB", "resultados_grid", $result);
											} else {
												$result = FacturasTemplate::errorResultado($result);
												$this->visor->addComponent("error", "error_body", $result);
											}
										}else{
											$result = FacturasTemplate::errorResultado("Error al enviar documento");
											$this->visor->addComponent("error", "error_body", $result);
										}
									}else{
										echo "<script>alert('Solo se pueden enviar documentos electronicos anulados m\u00E1ximo hasta con la fecha de ayer');</script>";
									}
								}else{
									echo "<script>alert('La fecha de emision no puede ser mayor al d\u00EDa actual');</script>";
								}
							}else{
								$busqueda 	= FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
								$result 	= FacturasTemplate::listado($busqueda['datos'],$_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
								$this->visor->addComponent("error", "error_body", " ");
								$this->visor->addComponent("ListadoB", "resultados_grid", $result);
								echo "<script>alert('El documento ya fue enviado a SUNAT');</script>";
							}
						}else{
							$result = FacturasModel::anulacionRegistro($_REQUEST["registroid"]);
							if ($result == 'OK') {
								$busqueda 	= FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
								$result = FacturasTemplate::listado($busqueda['datos'], $_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
								$this->visor->addComponent("error", "error_body", " ");
								$this->visor->addComponent("ListadoB", "resultados_grid", $result);
							} else {
								$result = FacturasTemplate::errorResultado($result);
								$this->visor->addComponent("error", "error_body", $result);
							}
						}

					break;

					case 'Eliminar':

						$result = FacturasModel::eliminarRegistro($_REQUEST["registroid"], $_REQUEST["forma_eliminar"]);

						if ($result == 'OK') {
							$busqueda 	= FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
							$result 	= FacturasTemplate::listado($busqueda['datos'], $_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
							$this->visor->addComponent("error", "error_body", " ");
							$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						} else {
							$result = FacturasTemplate::errorResultado($result);
						    	$this->visor->addComponent("error", "error_body", $result);
						}

						break;

					case 'Modificar':

						$registroid		= FacturasModel::GenerarRegitroID();
						$record			= FacturasModel::recuperarRegistro($_REQUEST["registroid"]);
						$registrosArray 	= FacturasModel::recuperarArticulos($_REQUEST["registroid"]);
						$registrosComplemento	= FacturasModel::recuperarComplemento($_REQUEST["registroid"]);

						if ($registrosComplemento)
						    $_SESSION["ARR_COMP"] = $registrosComplemento;

						$result = FacturasTemplate::formFacturas($record, $registrosArray,$_REQUEST['codigo'],$_REQUEST['f_desde'],$_REQUEST['f_hasta'], $_REQUEST['rxp'], $_REQUEST['pagina'], $_REQUEST['buscar_tipo']);
						$this->visor->addComponent("ContentB", "content_body", $result);

					break;

					case 'Actualizar':

						$tipo			= $_REQUEST["datos"]["ch_fac_tipodocumento"];
						$serie			= $_REQUEST["datos"]["ch_fac_seriedocumento"];
						$numero			= $_REQUEST["datos"]["ch_fac_numerodocumento"];
						$cod_cliente	= $_REQUEST["datos"]["cli_codigo"];
						
						$Nu_Tipo_Pago		= $_POST["datos"]["nu_tipo_pago"];
						$sTransferenciaGratuita	= $_POST["datos"]["ch_fac_tiporecargo2"];
						$Fe_Emision			= $_POST["Fe_Emision"];
						$No_Tipo_Credito 	= $_POST["datos"]["ch_fac_credito"];
						$Nu_Dias_Pago 		= $_POST["datos"]["ch_fac_forma_pago"];
						$Fe_Vencimiento		= $_POST["fe_vencimiento"];

						$record	= FacturasModel::ActualizarFactura($tipo, $serie, $numero, $cod_cliente, $Nu_Tipo_Pago, $Fe_Emision, $No_Tipo_Credito, $Nu_Dias_Pago, $Fe_Vencimiento, $sTransferenciaGratuita);

						if($record)
							echo "<script>alert('Cliente Actualizado');</script>\n";
						else
							echo "<script>alert('Error al actualizar Cliente');</script>\n";

					break;

					case 'Guardar':
						$tipo		= $_REQUEST["datos"]["ch_fac_tipodocumento"];
						$serie		= $_REQUEST["datos"]["ch_fac_seriedocumento"];
						$numero		= $_REQUEST["datos"]["ch_fac_numerodocumento"];
						$almacen	= $_REQUEST["datos"]["ch_almacen"];

						//FECHA DE EMISION - SISTEMAWEB
						$dayopcion 	= substr($this->datos['dt_fac_fecha'],6,4).'-'.substr($this->datos['dt_fac_fecha'],3,2).'-'.substr($this->datos['dt_fac_fecha'],0,2);

						$localtime 	= localtime();
						$day 		= $localtime[3];

						if(strlen($day) < 2 )
							$day = '0'.$day;

						$month = $localtime[4] + 1;

						if(strlen($day) < 2 )
							$month = '0'.$month;						

						$year 			= '20'.substr($localtime[5], -2);
						$today 			= $year.'-'.$month.'-'.$day;
						$today_before 	= date_create($year.'-'.$month.'-'.$day);

						/* RESTRICCION PARA FECHA DE F.E MENOR A 4 DIAS */
						date_add($today_before, date_interval_create_from_date_string('-4 days'));
						$today_before = date_format($today_before, 'Y-m-d');

						if(ereg("[A-Z]+", $this->datos['ch_fac_seriedocumento'])){
							if($dayopcion <= $today){
								if($dayopcion >= $today_before){
									$result = FacturasModel::guardarRegistro($this->datos, $_REQUEST['fecha_replicacion'], $_REQUEST['fe_vencimiento']);
									
									if ($result != OK) {
									    $result = FacturasTemplate::errorResultado($result);
									    $this->visor->addComponent("error", "error_body", $result);
									} else {
										$listado = true;

										$_REQUEST['busqueda']['f_desde'] = ($_REQUEST['busqueda']['f_desde'] == '') ? date('d/m/Y') : $_REQUEST['busqueda']['f_desde'];
										$_REQUEST['busqueda']['f_hasta'] = ($_REQUEST['busqueda']['f_hasta'] == '') ? date('d/m/Y') : $_REQUEST['busqueda']['f_hasta'];
									}

								}else{
									echo "<script>alert('Solo se pueden registrar documentos con el d\u00EDa actual \u00F3 m\u00E1ximo hasta 4 d\u00EDas antes');</script>";
								}
							}else{
								echo "<script>alert('La fecha de emision no puede ser mayor al d\u00EDa actual');</script>";
							}
						}else{
							$result = FacturasModel::guardarRegistro($this->datos, $_REQUEST['fecha_replicacion'], $_REQUEST['fe_vencimiento']);

							if ($result != OK) {
							    $result = FacturasTemplate::errorResultado($result);
							    $this->visor->addComponent("error", "error_body", $result);
							}
							
							$listado = true;

							$_REQUEST['busqueda']['f_desde'] = ($_REQUEST['busqueda']['f_desde'] == '') ? date('d/m/Y') : $_REQUEST['busqueda']['f_desde'];
							$_REQUEST['busqueda']['f_hasta'] = ($_REQUEST['busqueda']['f_hasta'] == '') ? date('d/m/Y') : $_REQUEST['busqueda']['f_hasta'];

						}
					break;

					case 'excel':

						$filtro=array("codigo"=>"","f_desde"=>$_REQUEST['f_desde'],"f_hasta"=>$_REQUEST['f_hasta']);
						$listado = FacturasModel::tmListadoExcel($filtro, $_REQUEST['rxp'], $_REQUEST['pagina'],$_REQUEST['buscar_tipo'], $_REQUEST["turno"]);
						FacturasTemplate::Excel($listado);

					break;

		            case "Buscar":

						$busqueda 	= FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
						$result 	= FacturasTemplate::listado($busqueda['datos'],$_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
						$this->visor->addComponent("error", "error_body", " ");
						$this->visor->addComponent("ListadoB", "resultados_grid", $result);

					break;
			
					case "Complete":

						/* VALIDACION DE DOCUMENTO ELECTRONICO ENVIADO */

						$valida_estado_documento_electronico = FacturasModel::Verify_Status_Document_Electronic($_REQUEST['registroid']);

						if($valida_estado_documento_electronico){

							/* VALIDACION PARA DOCUMENTOS ANULADOS. LA FECHA DEBE DE SER HOY O MAXIMO AYER */

							//FECHA DE EMISION - SISTEMAWEB
							$fe_emision = substr($_REQUEST['dt_fac_fecha'],6,4).'-'.substr($_REQUEST['dt_fac_fecha'],3,2).'-'.substr($_REQUEST['dt_fac_fecha'],0,2);

							$localtime 	= localtime();
							$day 		= $localtime[3];

							if(strlen($day) < 2 )
								$day = '0'.$day;

							$month = $localtime[4] + 1;

							if(strlen($day) < 2 )
								$month = '0'.$month;						

							$year 			= '20'.substr($localtime[5], -2);
							$today 			= $year.'-'.$month.'-'.$day;
							$today_4before 	= date_create($year.'-'.$month.'-'.$day);
							$today_before 	= date_create($year.'-'.$month.'-'.$day);

							/* RESTRICCION PARA FECHA DE F.E MENOR A 4 DIAS */
							date_add($today_4before, date_interval_create_from_date_string('-4 days'));
							$today_4before = date_format($today_4before, 'Y-m-d');

							/* RESTRICCION PARA FECHA DE F.E MENOR A SOLO ANULADOS 1 DIAS */
							date_add($today_before, date_interval_create_from_date_string('-1 days'));
							$today_before = date_format($today_before, 'Y-m-d');

							if($fe_emision <= $today){
								if($_REQUEST['ch_fac_anulado'] == 'S'){
									if($fe_emision >= $today_before){
										$valida_letra_electronica 	= true;
										$valida_Observacion 		= true;
										$valida 					= true;

										if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
											$valida_letra_electronica = FacturasModel::Verify_LetraElectronica($_REQUEST['registroid']);

										if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
											$valida_Observacion = FacturasModel::Verify_NotaCredito_Observacion($_REQUEST['registroid']);

										$valida_Unidad_Medidad = FacturasModel::Verify_UnidadMedida($_REQUEST['registroid']);

										if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
											$valida = FacturasModel::Verify_NotaCredito($_REQUEST['registroid'], $_REQUEST['dt_fac_fecha']);

										if($valida_letra_electronica){
											if($valida_Observacion){
												if($valida_Unidad_Medidad){
													if($valida){
														$complete 	= FacturasModel::Action_Complete_FE($_REQUEST);//FE = FACTURACION ELECTRONICA
														if($complete){
															$busqueda 	= FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
															$result 	= FacturasTemplate::listado($busqueda['datos'],$_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
															$this->visor->addComponent("error", "error_body", " ");
															$this->visor->addComponent("ListadoB", "resultados_grid", $result);
														}else{
															$result = FacturasTemplate::errorResultado("Error al enviar documento");
															$this->visor->addComponent("error", "error_body", $result);
														}
													}else{
														?><script>alert("<?php echo 'Error: El Documento que hace Referencia no existe'; ?> ");</script><?php
														$result = FacturasTemplate::errorResultado("Error: El Documento que hace Referencia no existe");
														$this->visor->addComponent("error", "error_body", $result);
													}
												}else{
													?><script>alert("<?php echo 'Error: No existe referencia SUNAT - Tipo Unidad de Medida'; ?> ");</script><?php
													$result = FacturasTemplate::errorResultado("Error: No existe referencia SUNAT - Tipo Unidad de Medida");
													$this->visor->addComponent("error", "error_body", $result);
												}
											}else{
												?><script>alert("<?php echo 'Error: El campo Observacion esta vacio'; ?> ");</script><?php
												$result = FacturasTemplate::errorResultado("Error: El campo Observacion esta vacio");
												$this->visor->addComponent("error", "error_body", $result);
											}
										}else{
											?><script>alert("<?php echo 'Error: No coincide el tipo de documento con la referencia'; ?> ");</script><?php
											$result = FacturasTemplate::errorResultado("Error: No coincide el tipo de documento con la referencia");
											$this->visor->addComponent("error", "error_body", $result);
										}
									}else{
										echo "<script>alert('Solo se pueden enviar documentos electronicos anulados m\u00E1ximo hasta con la fecha de ayer');</script>";
									}
								}else{
									if($fe_emision >= $today_4before){
										$valida_letra_electronica 	= true;
										$valida_Observacion 		= true;
										$valida 					= true;

										if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
											$valida_letra_electronica = FacturasModel::Verify_LetraElectronica($_REQUEST['registroid']);

										if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
											$valida_Observacion = FacturasModel::Verify_NotaCredito_Observacion($_REQUEST['registroid']);

										$valida_Unidad_Medidad = FacturasModel::Verify_UnidadMedida($_REQUEST['registroid']);

										if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
											$valida = FacturasModel::Verify_NotaCredito($_REQUEST['registroid'], $_REQUEST['dt_fac_fecha']);

										if($valida_letra_electronica){
											if($valida_Observacion){
												if($valida_Unidad_Medidad){
													if($valida){
														$complete 	= FacturasModel::Action_Complete_FE($_REQUEST);//FE = FACTURACION ELECTRONICA
														if($complete){
															$busqueda 	= FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
															$result 	= FacturasTemplate::listado($busqueda['datos'],$_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
															$this->visor->addComponent("error", "error_body", " ");
															$this->visor->addComponent("ListadoB", "resultados_grid", $result);
														}else{
															$result = FacturasTemplate::errorResultado("Error al enviar documento");
															$this->visor->addComponent("error", "error_body", $result);
														}
													}else{
														?><script>alert("<?php echo 'Error: El Documento que hace Referencia no existe'; ?> ");</script><?php
														$result = FacturasTemplate::errorResultado("Error: El Documento que hace Referencia no existe");
														$this->visor->addComponent("error", "error_body", $result);
													}
												}else{
													?><script>alert("<?php echo 'Error: No existe referencia SUNAT - Tipo Unidad de Medida'; ?> ");</script><?php
													$result = FacturasTemplate::errorResultado("Error: No existe referencia SUNAT - Tipo Unidad de Medida");
													$this->visor->addComponent("error", "error_body", $result);
												}
											}else{
												?><script>alert("<?php echo 'Error: El campo Observacion esta vacio'; ?> ");</script><?php
												$result = FacturasTemplate::errorResultado("Error: El campo Observacion esta vacio");
												$this->visor->addComponent("error", "error_body", $result);
											}
										}else{
											?><script>alert("<?php echo 'Error: No coincide el tipo de documento con la referencia'; ?> ");</script><?php
											$result = FacturasTemplate::errorResultado("Error: No coincide el tipo de documento con la referencia");
											$this->visor->addComponent("error", "error_body", $result);
										}
									}else{
										echo "<script>alert('Solo se pueden registrar documentos con el d\u00EDa actual \u00F3 m\u00E1ximo hasta 4 d\u00EDas antes');</script>";
									}
								}
							}else{
								echo "<script>alert('La fecha de emision no puede ser mayor al d\u00EDa actual');</script>";
							}
						}else{
							$busqueda 	= FacturasModel::tmListado($_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
							$result 	= FacturasTemplate::listado($busqueda['datos'],$_REQUEST["f_desde"], $_REQUEST["f_hasta"], $_REQUEST["codigo"], $_REQUEST["buscar_tipo"], $_REQUEST["turno"]);
							$this->visor->addComponent("error", "error_body", " ");
							$this->visor->addComponent("ListadoB", "resultados_grid", $result);
							echo "<script>alert('El documento ya fue enviado a SUNAT');</script>";
						}

					break;

						case "_complete":
							/**
							 * Funcion de comletado (Nueva)
							 */

							$res = array();
							/* VALIDACION DE DOCUMENTO ELECTRONICO ENVIADO */

							$valida_estado_documento_electronico = FacturasModel::Verify_Status_Document_Electronic($_REQUEST['registroid']);

							if($valida_estado_documento_electronico){

								//Validacion para boletas con valor mayor a 700 soles
								$validTotalTicket = FacturasModel::validTotalTicket($_REQUEST['registroid']);
								if ($validTotalTicket['error']) {
									echo json_encode($validTotalTicket);
									exit;
								}
								/* VALIDACION PARA DOCUMENTOS ANULADOS. LA FECHA DEBE DE SER HOY O MAXIMO AYER */
								//FECHA DE EMISION - SISTEMAWEB
								$fe_emision = substr($_REQUEST['dt_fac_fecha'],6,4).'-'.substr($_REQUEST['dt_fac_fecha'],3,2).'-'.substr($_REQUEST['dt_fac_fecha'],0,2);

								$localtime 	= localtime();
								$day 		= $localtime[3];

								if(strlen($day) < 2 )
									$day = '0'.$day;

								$month = $localtime[4] + 1;

								if(strlen($day) < 2 )
									$month = '0'.$month;						

								$year 			= '20'.substr($localtime[5], -2);
								$today 			= $year.'-'.$month.'-'.$day;
								$today_4before 	= date_create($year.'-'.$month.'-'.$day);
								$today_before 	= date_create($year.'-'.$month.'-'.$day);

								/* RESTRICCION PARA FECHA DE F.E MENOR A 4 DIAS */
								date_add($today_4before, date_interval_create_from_date_string('-4 days'));
								$today_4before = date_format($today_4before, 'Y-m-d');

								/* RESTRICCION PARA FECHA DE F.E MENOR A SOLO ANULADOS 1 DIAS */
								date_add($today_before, date_interval_create_from_date_string('-1 days'));
								$today_before = date_format($today_before, 'Y-m-d');

								if($fe_emision <= $today){
									if($_REQUEST['ch_fac_anulado'] == 'S'){
										if($fe_emision >= $today_before){
											$valida_letra_electronica 	= true;
											$valida_Observacion 		= true;
											$valida 					= true;

											if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
												$valida_letra_electronica = FacturasModel::Verify_LetraElectronica($_REQUEST['registroid']);

											if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
												$valida_Observacion = FacturasModel::Verify_NotaCredito_Observacion($_REQUEST['registroid']);

											$valida_Unidad_Medidad = FacturasModel::Verify_UnidadMedida($_REQUEST['registroid']);

											if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
												$valida = FacturasModel::Verify_NotaCredito($_REQUEST['registroid'], $_REQUEST['dt_fac_fecha']);

											if($valida_letra_electronica){
												if($valida_Observacion){
													if($valida_Unidad_Medidad){
														if($valida){
															$complete 	= FacturasModel::actionCompleteFE($_REQUEST);//FE = FACTURACION ELECTRONICA
															if($complete){
																$res = array(
																	'error' => false,
																	'message' => 'Documento enviado correctamente.',
																);
																echo json_encode($res);
															}else{
																$res = array(
																	'error' => true,
																	'message' => 'Error al enviar documento',
																);
																echo json_encode($res);
															}
														}else{
															$res = array(
																'error' => true,
																'message' => 'Error: El Documento que hace Referencia no existe',
															);
															echo json_encode($res);
														}
													}else{
														$res = array(
															'error' => true,
															'message' => 'Error: No existe referencia SUNAT - Tipo Unidad de Medida',
														);
														echo json_encode($res);
													}
												}else{
													$res = array(
														'error' => true,
														'message' => 'Error: El campo Observacion esta vacio',
													);
													echo json_encode($res);
												}
											}else{
												$res = array(
													'error' => true,
													'message' => 'Error: No coincide el tipo de documento con la referencia',
												);
												echo json_encode($res);
											}
										}else{
											$res = array(
												'error' => true,
												'message' => 'Solo se pueden enviar documentos electronicos anulados m\u00E1ximo hasta con la fecha de ayer',
											);
											echo json_encode($res);
										}
									}else{
										if($fe_emision >= $today_4before){
											$valida_letra_electronica 	= true;
											$valida_Observacion 		= true;
											$valida 					= true;

											if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
												$valida_letra_electronica = FacturasModel::Verify_LetraElectronica($_REQUEST['registroid']);

											if($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20')
												$valida_Observacion = FacturasModel::Verify_NotaCredito_Observacion($_REQUEST['registroid']);

											$valida_Unidad_Medidad = FacturasModel::Verify_UnidadMedida($_REQUEST['registroid']);

											if ($_REQUEST['ch_fac_tipodocumento'] == '11' || $_REQUEST['ch_fac_tipodocumento'] == '20') {
												$valida = FacturasModel::checkOriginDocument($_REQUEST['registroid'], $_REQUEST['dt_fac_fecha']);
											}

											if($valida_letra_electronica){
												if($valida_Observacion){
													if($valida_Unidad_Medidad){
														if($valida){
															$complete = FacturasModel::actionCompleteFE($_REQUEST);//FE = FACTURACION ELECTRONICA
															if($complete){
																$res = array(
																	'error' => false,
																	'message' => 'Documento enviado correctamente.',
																);
																echo json_encode($res);
															}else{
																$res = array(
																	'error' => true,
																	'message' => 'Error al enviar documento',
																);
																echo json_encode($res);
															}
														}else{
															$res = array(
																'error' => true,
																'message' => 'Error: El Documento que hace Referencia no existe',
															);
															echo json_encode($res);
														}
													}else{
														$res = array(
															'error' => true,
															'message' => 'Error: No existe referencia SUNAT - Tipo Unidad de Medida',
														);
														echo json_encode($res);
													}
												}else{
													$res = array(
														'error' => true,
														'message' => 'Error: El campo Observacion es incorrecto.',
													);
													echo json_encode($res);
												}
											}else{
												$res = array(
													'error' => true,
													'message' => 'Error: No coincide el tipo de documento con la referencia',
												);
												echo json_encode($res);
											}
										}else{
											$res = array(
												'error' => true,
												'message' => 'Solo se pueden registrar documentos con el día actual ó máximo hasta 4 días antes.',
											);
											echo json_encode($res);
										}
									}
								}else{
									$res = array(
										'error' => true,
										'message' => 'La fecha de emision no puede ser mayor al d\u00EDa actual',
									);
									echo json_encode($res);
								}
							}else{
								$res = array(
									'error' => true,
									'message' => 'El documento ya fue enviado a SUNAT',
								);
								echo json_encode($res);
							}
							exit;

						break;

					case "DOCUMENTOINTERNO":
						//var_dump($_REQUEST);
						$data = $_REQUEST;
						//fc.ch_fac_tipodocumento||fc.ch_fac_seriedocumento||fc.ch_fac_numerodocumento||fc.cli_codigo = ''
						$registroid = $_REQUEST['registroid'];
						$fe_referencia_documento = '';
						$dt_fac_fecha = $data["dt_fac_fecha"];

						if (empty($fe_referencia_documento)) {
							$_dt_fac_fecha = explode("/", $dt_fac_fecha);
							$month = $_dt_fac_fecha[1];
							$year = $_dt_fac_fecha[2];
						} else {
							$month = substr($fe_referencia_documento,3,2);
							$year = substr($fe_referencia_documento,6,4);
						}

						$getTax = FacturasModel::getTax();

						$postrans = "pos_trans".$year.$month;
						$isExistPostrans = FacturasModel::checkExistPostrans($postrans);
						$isFreeTransfer = FacturasModel::checkFreeTransfer($registroid);

						$data['postrans'] = $postrans;
						$data['isExistPostrans'] = $isExistPostrans;
						$data['isFreeTransfer'] = $isFreeTransfer;
						$data['isfe'] = false;
						$data['ch_almacen'] = $data['codalmacen'];

						$resHead = FacturasModel::getDataHeadFE($data);
						$xAdd = FacturasModel::getDataLineXAddrFE($data);

						$getOriginDocument = array('error' => true);
						$warehouse = FacturasModel::obtenerSucursal($data['codalmacen']);
						if ($data['ch_fac_tipodocumento'] == '11' || $data['ch_fac_tipodocumento'] == '20') {
							$getOriginDocument = FacturasModel::getOriginDocument($registroid);
						}
						$getDataLineLFE = FacturasModel::getDataLineLFE($data);
						

						//$params['no_producto_impuesto'] = $no_producto_impuesto;
						$data['no_producto_impuesto'] = 'PRODUCTO_INAFECTO';
						$getDataTaxFE = FacturasModel::getDataTaxFE($data);
						$getDataLineOFE = FacturasModel::getDataLineOFE($data);
						$getInfoInvoice = FacturasModel::getInfoInvoice($registroid);

						$data['ch_liquidacion'] = $getInfoInvoice['ch_liquidacion'];

						$getPlate = FacturasModel::getPlate($data);

						$data['disc'] = $resHead['disc'];
						$data['textog'] = 'OPERACIONES GRAVADAS:';
						if ($getInfoInvoice['typetax'] == '0') {//normal
							//considerar descuento
							$data['taxable_operations'] = $resHead['taxable_operations']-$data['disc'];
							$data['tax_total'] = $data['taxable_operations'] * $resHead['cnf_igv_ocs'];
							$data['grand_total'] = $data['taxable_operations'] + $data['tax_total'];
							$data['grand_total'] = number_format((float)$data['grand_total'], 2, '.', '');
							$data['letters'] = 'SON: '.FacturasModel::MontoMonetarioEnLetras($data['grand_total'], $getInfoInvoice['currency_name']);
						} else if ($getInfoInvoice['typetax'] == '1') {//exo
							$data['textog'] = 'OPERACIONES EXONERADAS:';
							$data['taxable_operations'] = $resHead['taxable_operations']-$data['disc'];
							$data['tax_total'] = $data['taxable_operations'] * 0;
							$data['grand_total'] = $data['taxable_operations'] + $data['tax_total'];
							$data['grand_total'] = number_format((float)$data['grand_total'], 2, '.', '');
							$data['letters'] = 'SON: '.FacturasModel::MontoMonetarioEnLetras($data['grand_total'], $getInfoInvoice['currency_name']);
							$data['letters'] .= "\n".'BIENES TRANSFERIDOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA';
							$getTax[0] = 0;
						} else if ($getInfoInvoice['typetax'] == '2') {//tg
							$data['tax_total'] = 0;
							$data['taxable_operations'] = 0;
							$data['grand_total'] = 0;
							$data['letters'] = 'SON: CERO Y 00/100 '.$getInfoInvoice['currency_name'];
							$data['letters'] .= "\n".'TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE';
						}

						FacturasTemplate::pdfDocumentoInterno($data, $resHead, $xAdd, $warehouse, $getOriginDocument, $getDataLineLFE, $getDataTaxFE, $getDataLineOFE, $getInfoInvoice, $getPlate, $getTax);
						//exit;
					break;

		            default:

						$listado = true;
						$_REQUEST['busqueda']['f_desde'] = ($_REQUEST['busqueda']['f_desde'] == '') ? date('d/m/Y') : $_REQUEST['busqueda']['f_desde'];
						$_REQUEST['busqueda']['f_hasta'] = ($_REQUEST['busqueda']['f_hasta'] == '') ? date('d/m/Y') : $_REQUEST['busqueda']['f_hasta'];
						unset($_SESSION['ARTICULOS']);
						unset($_SESSION['TOTAL_ARTICULOS']);
						unset($_SESSION["ARR_COMP"]);

					break;

		       		}

		        	if ($listado) {

	            		$result = FacturasTemplate::formBuscar($listado['paginacion']);
	            		$result .= FacturasTemplate::listado($listado['datos']);
	            		$this->visor->addComponent("error", "error_body", " ");
	            		$this->visor->addComponent("ContentB", "content_body", $result);

	            		unset($_SESSION['ARTICULOS']);
	            		unset($_SESSION['TOTAL_ARTICULOS']);
	            		unset($_SESSION["ARR_COMP"]);

		        	}

		       		break;
		       		
			case 'FACTURASDET':
			
		        	switch ($this->action) {
		        	
					case 'setRegistro'://Codigo CIIU
						$result = FacturasTemplate::setRegistros($_REQUEST["codigo"], $_REQUEST["tdoc"]);
						$this->visor->addComponent("desc_series_doc", "desc_series_doc", $result);
					break;
					
					case 'setRegistroSerie'://Codigo CIIU
						$result = FacturasTemplate::setRegistroSerie($_REQUEST["codigo"], $_REQUEST["tdoc"]);
						$this->visor->addComponent("desc_series_doc", "desc_series_doc", $result);
					break;
						
					case 'setRegistroFP'://Forma de Pago
						$result = FacturasTemplate::setRegistrosFormaPago($_REQUEST["codigofp"], $_REQUEST["fcred"], $_REQUEST["nu_codigo_cliente"]);
						$this->visor->addComponent("desc_forma_pago", "desc_forma_pago", $result);
					break;
						
					case 'setRegistroLPRE'://Lista de Precios
						$result = FacturasTemplate::setRegistrosListaPrecios($_REQUEST["codigolpre"]);
						$this->visor->addComponent("desc_lista_precios", "desc_lista_precios", $result);
						break;
						
					case 'setRegistroCli'://Clientes
						$result = FacturasTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
						$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
						break;
						
					case 'setRegistroDesc'://Rubros
						$result = FacturasTemplate::setRegistrosDesc($_REQUEST["codigodesc"]);
						$this->visor->addComponent("desc_descuento", "desc_descuento", $result);
						break;
						
					case 'setRegistroArt'://Cuentas de Bancos
						$result = FacturasTemplate::setRegistrosArticulos($_REQUEST["codigoart"], $_REQUEST["lprec"]);
						$this->visor->addComponent("desc_articulo[]", "desc_articulo[]", $result);
						break;

					case 'TipoCambio':
						$tc = FacturasModel::ObtenerTipoCambio($_REQUEST["fecha"], $_REQUEST["moneda"]);
					break;

					case 'ActualizarIGV':

						$tipo		= $_REQUEST["tipo"];
						$serie		= $_REQUEST["serie"];
						$numero		= $_REQUEST["numero"];
						$cliente	= $_REQUEST["cliente"];
						$idigv		= $_REQUEST["idigv"];

						$res = FacturasModel::ActualizarIGV($tipo, $serie, $numero, $cliente, $idigv);
	
						if($res){
							echo "<script>alert('Cliente Actualizado');</script>\n";
							$busqueda = FacturasModel::tmListado($_REQUEST["desde"], $_REQUEST["hasta"], $_REQUEST["codigo"], $_REQUEST["tipo_doc"], $_REQUEST["turno"]);
							$result = FacturasTemplate::listado($busqueda['datos'],$_REQUEST["desde"], $_REQUEST["hasta"], $_REQUEST["codigo"], $_REQUEST["tipo_doc"], $_REQUEST["turno"]);
							$this->visor->addComponent("error", "error_body", " ");
							$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						}else
							echo "<script>alert('Error al actualizar Cliente');</script>\n";

					break;

				    case 'AgregaArticulo':

						$contador 				= $_SESSION["TOTAL_ARTICULOS"];
						$Datos['codigo'] 		= $_REQUEST['codigo'];
						$Datos['descripcion'] 	= urldecode($_REQUEST['descripcion']);
						$Datos['cantidad'] 		= $_REQUEST['cantidad'];
						$Datos['precio'] 		= $_REQUEST['precio'];
						$Datos['neto'] 			= $_REQUEST['neto'];
						$Datos['igv'] 			= $_REQUEST['igv'];
						$Datos['dscto'] 		= $_REQUEST['dscto'];
						$Datos['total'] 		= $_REQUEST['total'];
						$Datos['total2'] 		= $_REQUEST['total2'];
						
						$lista = FacturasModel::AgregarArticulo($Datos, $contador);
						
						if ($_REQUEST['dato_elimina']) {
							if ($_REQUEST['registroid']) {
							        $art_codigo = $lista[$_REQUEST['dato_elimina']]['cod_articulo'];
							        $registroid = FacturasModel::GenerarRegitroID();
							        FacturasModel::eliminarArticuloDet($registroid, $art_codigo);
							}
							unset($lista[trim($_REQUEST['dato_elimina'])]);
						}

						$_SESSION["ARTICULOS"] = $lista;						
						$TotalesLista = FacturasModel::CalcularTotales($Datos);

						if (!$_REQUEST['dato_elimina']) {
							if (count($_SESSION["ARTICULOS"]) > $_SESSION["TOTAL_ARTICULOS"] && $_SESSION["TOTAL_ARTICULOS"] != count($_SESSION["ARTICULOS"])) {
								$_SESSION["TOTAL_ARTICULOS"] = count($_SESSION["ARTICULOS"]);
							} elseif ($_SESSION["TOTAL_ARTICULOS"] == count($_SESSION["ARTICULOS"])) {
								$_SESSION["TOTAL_ARTICULOS"] = ($_SESSION["TOTAL_ARTICULOS"] + 1);
							} else {
								$_SESSION["TOTAL_ARTICULOS"] = ($_SESSION["TOTAL_ARTICULOS"] + 1);
							}
						} else {
						    	$_SESSION["TOTAL_ARTICULOS"] = $_SESSION["TOTAL_ARTICULOS"] - 1;
						}

						$TotalesLista['total_recargo']		= round($TotalesLista['total_neto_articulo'] - $TotalesLista['total_neto_articulo'] / (1 + $_REQUEST['recargo'] / 100), 2);
						$TotalesLista["total_impuesto2"] 	= $_REQUEST["datos[nu_fac_impuesto2]"];
						$result 							= FacturasTemplate::addArticulos($_SESSION["ARTICULOS"]);
						$resultTotales 						= FacturasTemplate::addTotales($TotalesLista, $_REQUEST["datos"]);

						$this->visor->addComponent("datos_agregados", "datos_agregados", $result);
						$this->visor->addComponent("datos_agregados_totales", "datos_agregados_totales", $resultTotales);

						break;

	            		default:
	                		//listar ultimos movimientos
                		break;
		        	}

		        	break;

		   	default:
		        	$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "' . $this->request . '" NO CONOCIDA EN REGISTROS</h2>"');
		        	break;
		}
    }
}
?>
