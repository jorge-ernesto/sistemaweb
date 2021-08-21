<?php
session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('movimientos/m_egreso_caja.php');
include('movimientos/c_egreso_caja.php');
include('movimientos/t_egreso_caja.php');

$objmodel = new RegistroCajasModel();
$objtem = new RegistroCajasTemplate();
$objcomn = new RegistroCajaController("");


$accion = $_REQUEST['accion'];

//print_r($_REQUEST);
//echo "dasd";

try {
	if($accion == "tipodocumento") {
		$almacen = $_SESSION['almacen'];
		$tipo_doc_numero = trim($_REQUEST['documento']);
		$result = RegistroCajasModel::obtenerSucursales("");
		$cmb_serie = "<select id=serie_doc>";
		$cmb_serie .= "<option ]> Seleccione ..</option>";

		foreach ($result as $value) {
			if(strcmp($almacen, $value[0]) == 0) {
				$cmb_serie.="<option value=$value[0] selected> " . $value[1] . "</option>";
			} else {
				$cmb_serie.="<option value=$value[0] > " . $value[1] . "</option>";
			}
		}

		$cmb_serie .= "</select>";
		$json_clienete = RegistroCajasModel::obtenerClientes();

		//echo "{'dato':'$cmb_serie','cliente':$json_clienete}";
		echo json_encode(array('dato' => $cmb_serie, 'cliente' => $json_clienete));
	} else if($accion == "SearchCliente") {
		$keyword = $_REQUEST['keyword'];
		$data = RegistroCajasModel::TraerClientes($keyword);
		echo json_encode($data);
	} else if($accion == "executar_reporte_excel") {
		$fecha_inicio = $_REQUEST['fecha_inicio'];
		$fecha_final = $_REQUEST['fecha_final'];
		$sucursal = $_REQUEST['sucursal'];
		$limit = $_REQUEST['limit_mostrar'];
		$ruc = trim($_REQUEST['ruc']);
		$operacion = $_REQUEST['operacion'];
		$datars = RegistroCajasModel::MostarResultadoDetalle($fecha_inicio, $fecha_final, $sucursal, $limit, $ruc, $operacion);
		$_SESSION['data_excel']=$datars;
	} else if($accion == "tipo_documento_gennerar") {
		//$fecha = trim($_REQUEST['fecha_mostar']);
		$fecha = trim($_REQUEST['fecha']);
		$flag = RegistroCajasModel::verificar_dia($fecha);
		$dInicialSistema = RegistroCajasModel::obtenerDiaInicialSistema();

		if($flag == 1) {
			echo $flag;
		} else if($fecha < $dInicialSistema) {
			echo 2;//La fecha es menor a la fecha de inicio del sistema
		} else {
			$result = RegistroCajasModel::obtenerTipoDocumnetos_otros();
			echo "<select id='cmbtipo_doc'>";
			foreach ($result as $key => $value) {
			    echo "<option value='" . $value[0] . "'>" . $value[1] . "</option>";
			}

			echo "</select>";
		}

	} else if($accion == "TipoCambio") {
		$fecha	= $_REQUEST['fecha'];
		$tc	= RegistroCajasModel::ObtenerTipoCambio($fecha);
		echo $tc[0][0];//PARA MOSTRAR EL TIPO DE CAMBIO EN FACTURAS DE VENTAS
	} else if ($accion == "mostar_resultado_data") {
		$fecha_inicio = $_REQUEST['fecha_inicio'];
		$fecha_final = $_REQUEST['fecha_final'];
		$sucursal = $_REQUEST['sucursal'];
		$limit = $_REQUEST['limit_mostrar'];
		$ruc = trim($_REQUEST['ruc']);
		$operacion = $_REQUEST['operacion'];

		try {
			$json_data = RegistroCajasModel::MostarResultadoDetalle($fecha_inicio, $fecha_final, $sucursal, $limit, $ruc, $operacion);
			RegistroCajasTemplate::CrearTablareporte($json_data,$sucursal);
		} catch (Exception $e) {
			echo "<h2 style='color:red;'>" . $e->getMessage() . "</h2>";
		}
	} else if($accion == "anular_ingreso") {
		$id_transacion = $_REQUEST['id_transacion'];
		$iWarehouse = $_REQUEST['iWarehouse'];
		$dEntry = $_REQUEST['dEntry'];
		$flag = RegistroCajasModel::verificar_dia($dEntry);
		if($flag == 1) {
			echo "{'sStatus':'warning','sMessage':'¡ Dia consolidado, no se puede anular !'}";
			exit();
		} else {
			try {
				RegistroCajasModel::IniciarTransaccion(); //INICIAR TRANSACION
				RegistroCajasModel::Anular_Registro_Egreso_Caja_solo_cliente($id_transacion, 0);
				RegistroCajasModel::COMMITransaccion(); //CONFIRMAR TRANSACION
				echo "{'sStatus':'success','sMessage':'Recibo se ANULO($id_transacion) correctamente'}";
			} catch (Exception $e) {
				echo "{'sStatus':'danger','sMessage':'Error " . $e->getMessage() . "'}";
				RegistroCajasModel::ROLLBACKTransaccion();
				throw $e;
			}
		}
	} else if($accion == "count_bank") {
		$dat_banco = RegistroCajasModel::obtenerBanco();
		$data_cuentas = RegistroCajasModel::ReporteCuentasBancarias();
		RegistroCajasTemplate::FormularioCuentaBankaria($data_cuentas, $dat_banco);
	} else if($accion == "guardar_cuenta_bancaria") {
		try {
			$data = $_REQUEST['data'];

			RegistroCajasModel::Insertarc_Cuenta_bancaria($data['numero'], $data['banco'], $data['nombre'], $data['inicuenta']);
			echo "{'estado':'bien','mes':'Se inserto Correctamente la nueva cuenta(" . $data['numero'] . ")'}";
		} catch (Exception $e) {
			echo "{'estado':'error','mes':'" . $e->getMessage() . "'}";
		}
	} else if($accion == "nuevo_registro") {
		$almacen = $_SESSION['almacen'];
		$estaciones = RegistroCajasModel::obtenerSucursales("");
		$caja = RegistroCajasModel::obtenerCaja("");
		$operacion = RegistroCajasModel::obtenerOperacion("");
		$monedas = RegistroCajasModel::ObtenerMoneda("");
		$fecha_actual = RegistroCajasModel::fecha_aprosys();
		$serie = RegistroCajasModel::ObtenerNroDocuemnto_Recibo($almacen);
		$tipo_cambio = RegistroCajasModel::ObtenerTipoCambio($fecha_actual);
		$tipo_cambio = $tipo_cambio[0][0];

		if(empty($tipo_cambio))
			$tipo_cambio = 0;

		echo RegistroCajasTemplate::FormularioPrincipalSegundario($estaciones, $caja, $operacion, $almacen, $fecha_actual, $serie, $tipo_cambio, $monedas);

	} else if($accion == "num_recibo") {
		$alm = $_REQUEST['almacen'];
		$serie = RegistroCajasModel::ObtenerNroDocuemnto_Recibo($alm);
		echo "{'dato':'$serie'}";
		exit;
	} else if ($accion == "buscar_cliente") {
		$ruc_identi = trim($_REQUEST['ruc']);
		$dia = trim($_REQUEST['fecha_mostar']);
		$tc = trim($_REQUEST['tc']);
		$moneda = trim($_REQUEST['moneda']);
		$flag = RegistroCajasModel::verificar_dia($dia);
		if($flag == 1) {
			echo "<blink style='color: red'><b>¡ Dia consolidado, seleccionar otra fecha !</blink>";
			exit();
		} else {
			try {
				$dat_cuentas_x_cobrar = RegistroCajasModel::DataCuentasCobrar($ruc_identi);
				RegistroCajasTemplate::CrearTablaSeleccionarCliente($dat_cuentas_x_cobrar, $tc, $moneda);
			} catch (Exception $e) {
				echo "<h>ERROR_:" . $e->getMessage() . "</h1>";
				exit();
			}
		}
	} else if($accion == "buscar_cuenta_cobrar_recivo") {
			$ruc_identi = trim($_REQUEST['ruc']);
        	$num_docums = $_REQUEST['num_doc'];
			$tc = trim($_REQUEST['tc']);
			$moneda = trim($_REQUEST['moneda']);

			try {
				if(count($num_docums) > 0) {
					$cade_num_do = "";
					foreach ($num_docums as $valor) {
						$cade_num_do.="'$valor',";
					}

					$cade_num_do = substr($cade_num_do, 0, -1);
					$dat_cuentas_x_cobrar = RegistroCajasModel::DataCuentasCobrarDetalleRecivo($ruc_identi, $cade_num_do);
					$dat_medio_pago = RegistroCajasModel::obtenerMedioPago();
					$dat_banco = RegistroCajasModel::obtenerBanco();

					RegistroCajasTemplate::CrearTablaSeleccionarClienteDetalleRecibo($dat_cuentas_x_cobrar, $dat_medio_pago, $dat_banco, $tc, $moneda);
				} else {
					exit;
				}
			} catch (Exception $e) {
				echo "ERROR_:No se encontro liquidaciones disponibles";
				exit();
		}
	} else if($accion == "cmboperacion") {
		$almacen = $_SESSION['almacen'];
		$tipo_doc_numero = trim($_REQUEST['documento']);
		$result = RegistroCajasModel::obtenerOperacion("");
		$cmb_serie3 = "<select id=id_operacion>";
		$cmb_serie3 .= "<option value = >Todos</option>";

		foreach ($result as $value) {
			if(strcmp($almacen, $value[0]) == 0)
				$cmb_serie3.="<option value=$value[0] selected> " . $value[1] . "</option>";
			else
				$cmb_serie3.="<option value=$value[0] > " . $value[1] . "</option>";
		}

		$cmb_serie3	.= "</select>";
		echo "{'dato':'$cmb_serie3'}";
	} else if ($accion == "cuentas_bancarias") {
		//formato fecha
		$id_banco = $_REQUEST['cuenta'];
		$id_moneda = $_REQUEST['moneda'];
		$data_rs = RegistroCajasModel::ObtenerCuentasDeBanco($id_banco, $id_moneda);
		$caden_cmb = "";
		$caden_cmb .= "<option value='-'>Seleccionar..</option>";

		foreach ($data_rs as $valor) {
			$caden_cmb .= "<option value='$valor[0]'>$valor[0] - $valor[1]</option>";
		}

		echo '{"datos":"' . $caden_cmb . '"}';
	} else if ($accion == "cuentas_bancarias_moneda") {
		//formato fecha
		$id_banco	= $_REQUEST['cuenta'];
		$data_rs	= RegistroCajasModel::ObtenerCuentasDeBancoMoneda($id_banco);
		$caden_cmb	= "";

		foreach ($data_rs as $valor) {
			$caden_cmb .= "<option value='$valor[0]'>$valor[1]</option>";
		}

		echo '{"datos":"' . $caden_cmb . '"}';
	} else if ($accion == "mostar_medio_pago") {
		$dat_medio_pago = RegistroCajasModel::obtenerMedioPago();
		$dat_banco = RegistroCajasModel::obtenerBanco();
		RegistroCajasTemplate::MostarMedioPago($dat_medio_pago, $dat_banco);
	} else if ($accion == "finalizar_proceso") {
		$tipo_accion = $_REQUEST['tipo_accion'];
		$createdby = "0";
		$datos_generales = $_REQUEST['datos_generales'][0];
		$almacen = $datos_generales['almacen'];
		$num_recibo = $datos_generales['num_recibo'];
		$fecha_general = $datos_generales['fecha_general'];
		$caja = $datos_generales['caja'];
		$tipo_operacion = $datos_generales['tipo_operacion'];
		$observacion = $datos_generales['observacion'];
		$ruc_cliente = $datos_generales['ruc_cliente'];
		$rate = $datos_generales['txttipo_cambio'];
		$tipo_moneda = $datos_generales['cmnmoneda_id'];//ES LA PARTE DE LA CABECERA RECIBO CAJA EGRESO.

		$type = 1;
		$amount = 0;
		$importe_global_documento = (float) 0;
		$importe_global_mp = (float) 0;
		$importe_global_notacredito = (float) 0;

		//*******PROCESO DE CAPTURAR INFORAMCION NECESARIA********///
		$datos_facturas_antelacion = $_REQUEST['datos_factura'];
		foreach ($datos_facturas_antelacion as $valores) {
			$informacion = explode("*", $valores);
			$montotmp = $informacion[3];
			$tipo_doc_valid = $informacion[6];
			$monedatmp 	= $informacion[5];

			if($tipo_doc_valid == "20") {
				$importe_global_notacredito+=abs($montotmp);
			}

			if($tipo_moneda == "01" && $monedatmp == "02") {//SI MI CAJA ES EN SOLES Y DOCUMENTO PAGO EN DOLARES
				$amount += (float)($montotmp * $rate);
			} else if($tipo_moneda == "02" && $monedatmp == "01") {//SI MI CAJA ES EN DOLARES Y DOCUMENTO PAGO EN SOLES
				$amount += (float)($montotmp / $rate);
			} else if($tipo_moneda == "02" && $monedatmp == "02") {//SI MI CAJA ES EN DOLARES Y DOCUMENTO PAGO EN DOLARES
				$amount += (float)($montotmp);
			} else {
				$amount += (float)($montotmp);
			}

			/*if($c_currency_id == "1" || $c_currency_id == '01') {
				if ($monedatmp == "1" || $monedatmp == "01") {
					$amount+=(float) $montotmp;
					$c_currency_id = '01';
					if($tipo_doc_valid=="20") {
						$importe_global_notacredito+=abs($montotmp);
					}
				} else {
					$amount+=(float) $montotmp * $rate;
				}
			} else {
				$amount+=(float) $montotmp;
			}*/

			$importe_global_documento = (float) $amount;
		}

		$cantidadcount = 0;
		$datos_forma_pago_tmp = $_REQUEST['datos_medio_pago'];

		foreach ($datos_forma_pago_tmp as $datos_pagos) {
			$importe = $datos_pagos['importeg'];
			$importe_global_mp +=(float) $importe;
			$cantidadcount++;
		}

		if($cantidadcount == 0) {
			throw new Exception("No se ingreso  Medio de pago");
		}

		try {
			RegistroCajasModel::IniciarTransaccion(); //INICIAR TRANSACION
			$estado = RegistroCajasModel::Insertarc_cash_transaction($createdby, $type, $fecha_general, $num_recibo, $caja, $tipo_operacion, $observacion, $ruc_cliente, $tipo_moneda, $rate, $amount, $almacen);
			$datos_facturas = $_REQUEST['datos_factura'];

			foreach ($datos_facturas as $valores) {
				$informacion = null;
				$informacion = explode("*", $valores);
				$tipo_doc = $informacion[0];
				$tipo_doc_serie = $informacion[1];
				$tipo_doc_numero = $informacion[2];
				$monto = (float)$informacion[3];
				$reference = $informacion[4];
				$moneda = $informacion[5];
				RegistroCajasModel::Insertarc_cash_transaction_detail($tipo_doc, $tipo_doc_serie, $tipo_doc_numero, $reference, $monto, $moneda);
			}

			$datos_forma_pago = $_REQUEST['datos_medio_pago'];

			foreach ($datos_forma_pago as $datos_pagos) {
				$c_cash_mpayment_id = $datos_pagos['medio_pago'];
				$pay_number = $datos_pagos['num_referencia'];
				$fecha = $datos_pagos['fecha'];
				$c_bank_id = $datos_pagos['banco'];
				$c_bank_account_id = $datos_pagos['cuentas_cmb_mostrar'];
				$moneda = $datos_pagos['mostarmoneda'];
				$importe = $datos_pagos['importeg'];

				RegistroCajasModel::Insertarc_cash_transaction_payment($c_cash_mpayment_id, $pay_number, $c_bank_id, $c_bank_account_id, $moneda, $importe, $fecha);
			}

			$data_actualizar = array();
			if($tipo_accion == "sin_cliente") {
				//haremos otras ocsiones
			} else {
				$medio_pago = round($importe_global_mp, 2);
				$factura_global = round($importe_global_documento, 2);
				if ($factura_global < $medio_pago) {
					throw new Exception("Monto de medio de pago ingresado paso el monto apropiado.(" . $factura_global . "=" . $medio_pago . ")");
				} else if($factura_global > $medio_pago) {
					$monto_decremento = $importe_global_mp;
					if($importe_global_notacredito>0) {
						$monto_decremento = ($importe_global_notacredito+$importe_global_mp);
					}

					$datos_facturas = $_REQUEST['datos_factura'];
					$estado_aplicado_nota_credito	= "0";

					foreach ($datos_facturas as $valores) {
						$informacion = explode("*", $valores);
						$tipo_doc = $informacion[0];
						$tipo_doc_serie = $informacion[1];
						$tipo_doc_numero = $informacion[2];
						$monto = $informacion[3];
						$reference = $informacion[4];
						$moneda = $informacion[5];
						$tipo_doc_emitido = $informacion[6];
						$montopay = $monto;

						if($tipo_moneda == '02' && $moneda == '01')
							$montopay = (float)($monto / $rate);
						else if($tipo_moneda == '01' && $moneda == '02')
							$montopay = (float)($monto * $rate);

						if($tipo_doc_emitido!="20") {//------inicio if
							if($monto_decremento >= $montopay) {
								RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '');
								$data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto);
								$monto_decremento -= $montopay;
							} else {
								if($tipo_moneda == '02' && $moneda == '01')
									$monto_decremento = (float)($monto_decremento * $rate);
								else if($tipo_moneda == '01' && $moneda == '02')
									$monto_decremento = (float)($monto_decremento / $rate);

								RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto_decremento, $reference, $almacen, '', '');
								$data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto_decremento);
								break;
							}
						} else {
							RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '');
							$data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto); 
						}
					}
				} else if($factura_global == $medio_pago) {
					$datos_facturas = $_REQUEST['datos_factura'];

					foreach ($datos_facturas as $valores) {
						$informacion = explode("*", $valores);
						$tipo_doc = $informacion[0];
						$tipo_doc_serie = $informacion[1];
						$tipo_doc_numero = $informacion[2];
						$monto = $informacion[3];
						$reference = $informacion[4];
						$moneda = $informacion[5];
						$tipo_doc_emitido = $informacion[6];
						$monto = (float)($monto);

						RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '');
						$data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto);
					}
				}
			}

			if(count($data_actualizar) > 0) {
				foreach ($data_actualizar as $value) {
					RegistroCajasModel::ActualizarMontosCabecera($value['ruc'], $value['serie'], $value['num'], $value['importe'], $value['td']);
				}
			}

			RegistroCajasModel::ActualizarNroDocuemnto_Recibo($almacen);
			RegistroCajasModel::COMMITransaccion(); //CONFIRMAR TRANSACION
			echo "{'estado':'bien','mes':'Recibo se registro correctamente'}";
		} catch (Exception $e) {
			RegistroCajasModel::ROLLBACKTransaccion();
			throw $e;
		}
	}
} catch (Exception $r) {
	echo "{'estado':'error','mes':'" . $r->getMessage() . "'}";
}
