<?php
session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('movimientos/m_ingreso_caja.php');
include('movimientos/c_ingreso_caja.php');
include('movimientos/t_ingreso_caja.php');
	
$objmodel = new RegistroCajasModel();
$objtem = new RegistroCajasTemplate();
$objcomn = new RegistroCajaController();

//print_r($_REQUEST);
$accion = $_REQUEST['accion'];
$id_recibo = $_REQUEST['id_recibo'];
try {

	if ($accion == "tipodocumento") {

		$almacen		= $_SESSION['almacen'];
		$tipo_doc_numero	= trim($_REQUEST['documento']);
		$result			= RegistroCajasModel::obtenerSucursales("");
		$cmb_serie		= "<select id=serie_doc>";
		$cmb_serie		.= "<option ]> Seleccione ..</option>";

		foreach ($result as $value) {
			if (strcmp($almacen, $value[0]) == 0)
		        	$cmb_serie.="<option value=$value[0] selected> " . $value[1] . "</option>";
			else
				$cmb_serie.="<option value=$value[0] > " . $value[1] . "</option>";
        	}

        	$cmb_serie	.= "</select>";
        	$json_clienete	= RegistroCajasModel::obtenerClientes();

        	echo "{'dato':'$cmb_serie','cliente':$json_clienete}";

	} else if ($accion == "SearchCliente") {
		$keyword = $_REQUEST['keyword'];
		$data = RegistroCajasModel::TraerClientes($keyword);
		echo json_decode($data);
	} else if ($accion == "upadte_datos") {

		foreach ($_REQUEST['data'] as $valor) {
			$datos_update = explode("*", $valor);
			RegistroCajasModel::Actualizarpayment($datos_update[0], $datos_update[1]);
		}

	} else if ($accion == "tipo_documento_gennerar") {

		$dia = trim($_REQUEST['fecha']);
		$dia = strip_tags($dia);

		$flag = RegistroCajasModel::validaDia($dia);
		$dInicialSistema = RegistroCajasModel::obtenerDiaInicialSistema();

		if($flag == 1){
			echo $flag;
		} else if ($dia < $dInicialSistema) {
			echo 2;//La fecha es menor a la fecha de inicio del sistema
		} else {
			$result = RegistroCajasModel::obtenerTipoDocumnetos_otros();
			echo "<select id='cmbtipo_doc'>";
			foreach ($result as $key => $value)
				echo "<option value='" . $value[0] . "'>" . $value[1] . "</option>";
			echo "</select>";
		}

	} else if ($accion == "mostar_resultado_data") {

		$fecha_inicio 	= $_REQUEST['fecha_inicio'];
		$fecha_final 	= $_REQUEST['fecha_final'];
		$sucursal 	= $_REQUEST['sucursal'];
		$limit 		= $_REQUEST['limit_mostrar'];
		$ruc 		= trim($_REQUEST['ruc']);
		$operacion 		= $_REQUEST['operacion'];

		try {

			$json_data = RegistroCajasModel::MostarResultadoDetalle($fecha_inicio, $fecha_final, $sucursal, $limit, $ruc, $operacion);

			RegistroCajasTemplate::CrearTablareporte($json_data, $sucursal);

		} catch (Exception $e) {
			echo "<h2 style='color:red;'>" . $e->getMessage() . "</h2>";
		}

	} else if ($accion == "count_bank") {

        	$dat_banco = RegistroCajasModel::obtenerBanco();
       		$data_cuentas = RegistroCajasModel::ReporteCuentasBancarias();
        	RegistroCajasTemplate::FormularioCuentaBankaria($data_cuentas, $dat_banco);

	} else if ($accion == "guardar_cuenta_bancaria") {

        try {
            $data = $_REQUEST['data'];

            RegistroCajasModel::Insertarc_Cuenta_bancaria($data['numero'], $data['banco'], $data['nombre'], $data['inicuenta']);
            echo "{'estado':'bien','mes':'Se inserto Correctamente la nueva cuenta(" . $data['numero'] . ")'}";
        } catch (Exception $e) {
            echo "{'estado':'error','mes':'" . $e->getMessage() . "'}";
        }

	} else if ($accion == "nuevo_registro") {

		$almacen	= $_SESSION['almacen'];
		$estaciones 	= RegistroCajasModel::obtenerSucursales("");
		$caja 		= RegistroCajasModel::obtenerCaja("");
		$operacion 	= RegistroCajasModel::obtenerOperacion("");
		$monedas 	= RegistroCajasModel::ObtenerMoneda("");
		$fecha_actual 	= RegistroCajasModel::fecha_aprosys();
		$serie 		= RegistroCajasModel::ObtenerNroDocuemnto_Recibo($almacen);
		$tipo_cambio 	= RegistroCajasModel::tipo_cambio($fecha_actual);

		echo RegistroCajasTemplate::FormularioPrincipalSegundario($estaciones, $caja, $operacion, $almacen, $fecha_actual, $serie, $tipo_cambio, $monedas);

	} else if ($accion == "num_recibo") {

	        $alm = $_REQUEST['almacen'];
	        $serie = RegistroCajasModel::ObtenerNroDocuemnto_Recibo($alm);
	        echo "{'dato':'$serie'}";
	        exit;

	} else if ($accion == "anular_ingreso") {

		$id_transacion = $_REQUEST['id_transacion'];
		$nu_almacen = $_REQUEST['nu_almacen'];
		$dEntry = $_REQUEST['dEntry'];

		$flag = RegistroCajasModel::validaDia($dEntry);

		if($flag == 1){
			//echo "<blink style='color: red'><b>¡ Dia consolidado, seleccionar otra fecha !</blink>";
			echo "{'sStatus':'warning','sMessage':'¡ Dia consolidado, no se puede anular !'}";
			exit();
		}else{
			try {
				RegistroCajasModel::IniciarTransaccion(); //INICIAR TRANSACION 
				RegistroCajasModel::Anular_Registro_Ingreso_Caja_solo_cliente($id_transacion, 0, $nu_almacen);
				RegistroCajasModel::COMMITransaccion(); //CONFIRMAR TRANSACION
				echo "{'sStatus':'success','sMessage':'Recibo se ANULO($id_transacion) correctamente'}";
			} catch (Exception $e) {
				echo "{'sStatus':'danger','sMessage':'Error " . $e->getMessage() . "'}";
				RegistroCajasModel::ROLLBACKTransaccion();
				throw $e;
			}
		}
	} else if ($accion == "buscar_cliente") {

		$ruc_identi	= trim($_REQUEST['ruc']);
		$dia		= trim($_REQUEST['fecha']);
		$tc		= trim($_REQUEST['tc']);
		$moneda		= trim($_REQUEST['moneda']);

		$flag = RegistroCajasModel::validaDia($dia);

		if($flag == 1){
			echo "<blink style='color: red'><b>¡ Dia consolidado, seleccionar otra fecha !</blink>";
			exit();
		}else{

			try {
				$dat_cuentas_x_cobrar = RegistroCajasModel::DataCuentasCobrar($ruc_identi);
				RegistroCajasTemplate::CrearTablaSeleccionarCliente($dat_cuentas_x_cobrar, $tc, $moneda);

			} catch (Exception $e) {
				echo "ERROR_:No se encontro liquidaciones disponibles";
				exit();
			}

		}

	} else if ($accion == "buscar_cuenta_cobrar_recivo") {

        $ruc_identi = trim($_REQUEST['ruc']);
        $num_docums = $_REQUEST['num_doc'];
		$tc			= trim($_REQUEST['tc']);
		$moneda		= trim($_REQUEST['moneda']);

		try {
			if (count($num_docums) > 0) {
				$cade_num_do = "";
                foreach ($num_docums as $valor) {
                	$cade_num_do .= "'$valor',";
                }
                
				$cade_num_do = substr($cade_num_do, 0, -1);

				/* VERIFICAR SI LA FACTURA O BOLETA TIENE O NO (NC O ND) */
	        	$validar = RegistroCajasModel::Verify_FC_BV($cade_num_do);
		        if($validar){
					/* Si es una N/C o N/D verificar que se encuentre enlazada a un documento ORIGEN */
					$arrResponseVerificacion = RegistroCajasModel::verificarRelacionDocumentos($cade_num_do);
					if ( $arrResponseVerificacion["sStatus"] == "success" ) {
						$dat_cuentas_x_cobrar 	= RegistroCajasModel::DataCuentasCobrarDetalleRecivo($ruc_identi, $cade_num_do);
				        $dat_medio_pago 		= RegistroCajasModel::obtenerMedioPago();
				        $dat_banco 				= RegistroCajasModel::obtenerBanco();
				        RegistroCajasTemplate::CrearTablaSeleccionarClienteDetalleRecibo($dat_cuentas_x_cobrar, $dat_medio_pago, $dat_banco, $tc, $moneda);
				        exit();
					} else {
		        		echo "<blink style='color: red'><b>".$arrResponseVerificacion["sMessage"]."</b></blink>";
		        		exit();
					}
			    }else{
			        $dat_cuentas_x_cobrar 	= RegistroCajasModel::DataCuentasCobrarDetalleRecivo($ruc_identi, $cade_num_do);
			        $dat_medio_pago 		= RegistroCajasModel::obtenerMedioPago();
			        $dat_banco 				= RegistroCajasModel::obtenerBanco();
			        RegistroCajasTemplate::CrearTablaSeleccionarClienteDetalleRecibo($dat_cuentas_x_cobrar, $dat_medio_pago, $dat_banco, $tc, $moneda);
			    }
			} else {
				exit;
			}
		} catch (Exception $e) {
			echo "ERROR_:No se encontro liquidaciones disponibles";
			exit();
        }
	} else if ($accion == "cuentas_bancarias") {

		//formato fecha
		$id_banco	= $_REQUEST['cuenta'];
		$id_moneda	= $_REQUEST['moneda'];
		$data_rs	= RegistroCajasModel::ObtenerCuentasDeBanco($id_banco, $id_moneda);
		$caden_cmb	= "";

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
	        $dat_banco 	= RegistroCajasModel::obtenerBanco();

        	RegistroCajasTemplate::MostarMedioPago($dat_medio_pago, $dat_banco);

	} else if ($accion == "cmboperacion") {

		$almacen		= $_SESSION['almacen'];
		$tipo_doc_numero	= trim($_REQUEST['documento']);
		$result			= RegistroCajasModel::obtenerOperacion("");
		$cmb_serie2		= "<select id=id_operacion>";
		$cmb_serie2		.= "<option value = > Seleccione ..</option>";

		foreach ($result as $value) {
			if (strcmp($almacen, $value[0]) == 0)
		        	$cmb_serie2.="<option value=$value[0] selected> " . $value[1] . "</option>";
			else
				$cmb_serie2.="<option value=$value[0] > " . $value[1] . "</option>";
        	}

        	$cmb_serie2	.= "</select>";

        	echo "{'dato':'$cmb_serie2'}";

	} else if ($accion == "finalizar_proceso") {

		$rate = 0;

		$tipo_accion 		= $_REQUEST['tipo_accion'];
		$createdby 		= "0";
		$datos_generales 	= $_REQUEST['datos_generales'][0];
		$almacen 		= $datos_generales['almacen'];
		$num_recibo 		= $datos_generales['num_recibo'];
		$fecha_general 		= $datos_generales['fecha_general'];
		$caja 			= $datos_generales['caja'];
		$tipo_operacion 	= $datos_generales['tipo_operacion'];
		$observacion 		= $datos_generales['observacion'];
		$ruc_cliente 		= $datos_generales['ruc_cliente'];
		$rate 			= $datos_generales['txttipo_cambio'];
		$tipo_moneda		= $datos_generales['cmnmoneda_id'];//ES LA PARTE DE LA CABECERA RECIBO CAJA INGRESO.

		$type				= 0;
		$amount				= 0;
		$amountpay			= 0;
		$importe_global_documento 	= (float) 0;
		$importe_global_mp 		= (float) 0;

		//*******PROCESO CAPTURAR INFORAMCION NECESARIA********//

		$datos_facturas_antelacion = $_REQUEST['datos_factura'];

		/*$cantidad_factura_ant = count($datos_facturas_antelacion);

		/*if ($cantidad_factura_ant == 1) {
			$informacion = explode("*", $datos_facturas_antelacion[0]);
		    	$c_currency_id = $informacion[5];
		} else {
			$c_currency_id = '01';
		}*/

		$informacion = '';

		//ARRAY DETALLE DE RECIBO DE PAGO 

		foreach ($datos_facturas_antelacion as $valores) {

			$informacion	= explode("*", $valores);
		    $tipo_doc 		= $informacion[0];
			$montotmp		= $informacion[3];
			$monedatmp 		= $informacion[5];

			if($tipo_moneda == "01" && $monedatmp == "02"){//SI MI CAJA ES EN SOLES Y DOCUMENTO PAGO EN DOLARES
				if($tipo_doc == '20')
					$amount -= (float)($montotmp * $rate);
				else
					$amount += (float)($montotmp * $rate);
			}elseif($tipo_moneda == "02" && $monedatmp == "01"){//SI MI CAJA ES EN DOLARES Y DOCUMENTO PAGO EN SOLES
				if($tipo_doc == '20')
					$amount -= (float)($montotmp / $rate);
				else
					$amount += (float)($montotmp / $rate);
			}elseif($tipo_moneda == "02" && $monedatmp == "02"){//SI MI CAJA ES EN DOLARES Y DOCUMENTO PAGO EN DOLARES
				if($tipo_doc == '20')
					$amount -= (float)($montotmp);
				else
					$amount += (float)($montotmp);
			}else{
				if($tipo_doc == '20')
					$amount -= (float)($montotmp);
				else
					$amount += (float)($montotmp);
			}

			$importe_global_documento = (float)$amount;

		}

		// FIN ARRAY DETALLE DE RECIBO DE PAGO 

		$cantidadcount 		= 0;
		$datos_forma_pago_tmp 	= $_REQUEST['datos_medio_pago'];

		// ARRAY DETALLE DE MEDIO DE PAGO 

		foreach ($datos_forma_pago_tmp as $datos_pagos) {

		    	$importe = $datos_pagos['importeg'];

		    	$importe_global_mp +=(float)$importe;

		    	$cantidadcount++;

		}

		// FIN ARRAY DETALLE DE MEDIO DE PAGO 

		if ($cantidadcount == 0)
			throw new Exception("No se ingreso  Medio de pago");

		try {

			RegistroCajasModel::IniciarTransaccion();

		    	//calculamos la secuencia  del los documentos

		    	$estado		= RegistroCajasModel::Insertarc_cash_transaction($createdby, $type, $fecha_general, $num_recibo, $caja, $tipo_operacion, $observacion, $ruc_cliente, $tipo_moneda, $rate, $amount, $almacen);

		    	$datos_facturas = $_REQUEST['datos_factura'];

			//ARRAY DETALLE DE RECIBO

		    	foreach ($datos_facturas as $valores) {

				$informacion		= null;
				$informacion 		= explode("*", $valores);
				$tipo_doc 		= $informacion[0];
				$tipo_doc_serie 	= $informacion[1];
				$tipo_doc_numero 	= $informacion[2];
				$monto 			= (float)$informacion[3];
				$reference 		= $informacion[4];
				$moneda 		= $informacion[5];

				RegistroCajasModel::Insertarc_cash_transaction_detail($tipo_doc, $tipo_doc_serie, $tipo_doc_numero, $reference, $monto, $moneda);

			}

		    	$datos_forma_pago = $_REQUEST['datos_medio_pago'];

			//ARRAY DETALLE DE MEDIO DE PAGO

		    	foreach ($datos_forma_pago as $datos_pagos) {

				$c_cash_mpayment_id 	= $datos_pagos['medio_pago'];
				$pay_number 		= $datos_pagos['num_referencia'];
				$fecha_pay 		= $datos_pagos['fecha'];
				$c_bank_id 		= $datos_pagos['banco'];
				$c_bank_account_id 	= $datos_pagos['cuentas_cmb_mostrar'];
				$moneda 		= $datos_pagos['mostarmoneda'];
				$importe 		= $datos_pagos['importeg'];

				RegistroCajasModel::Insertarc_cash_transaction_payment($c_cash_mpayment_id, $pay_number, $c_bank_id, $c_bank_account_id, $moneda, $importe, $fecha_pay);

		    	}

		    	$data_actualizar = array();

		    	if ($tipo_accion == "sin_cliente") {
		        	//haremos otras ocsiones
				} else {

		        	$medio_pago 	= round($importe_global_mp, 2);
		        	$factura_global = round($importe_global_documento, 2);

		        	if ($factura_global < $medio_pago) {

						throw new Exception("Monto de medio de pago ingresado paso el monto apropiado.(" . $factura_global . "=" . $medio_pago . ")");

					} else if ($factura_global > $medio_pago) {

						$tipo_pago 		= "partes";
						$nu_serie 		= NULL;
						$nu_documento 	= NULL;

			            $monto_pago_total_other	= round($importe_global_mp,  2);//IMPORTE DE MEDIO DE PAGO
			            $monto_pago_total	= round($importe_global_mp,  2);//IMPORTE DE MEDIO DE PAGO
			            $datos_facturas 	= $_REQUEST['datos_factura'];
						$amount				= 0;
						$i = 0;
						$valor = null;
						$monto_factura = 0;

						$monto_pago_total_descontando = $monto_pago_total;

/*
			            	echo "<pre>";
			            	print_r($monto_pago_total);
			            	echo "<pre>";
*/
			            foreach ($datos_facturas as $valores) {
/*
			            	echo "<pre>";
			            	print_r($valores);
			            	echo "<pre>";
*/
							$informacion 		= explode("*", $valores);
							$tipo_doc 			= $informacion[0];
							$tipo_doc_serie 	= $informacion[1];
							$tipo_doc_numero 	= $informacion[2];

							$monto				= $informacion[3];

							$reference 			= $informacion[4];
							$moneda 			= $informacion[5];
							$tipo_doc_emitido 	= $informacion[6];

							$monto 	= (float)($monto);

							if($tipo_moneda == '02' && $moneda == '01')
								$monto 	= ($monto / $rate);
							elseif($tipo_moneda == '01' && $moneda == '02')
								$monto 	= ($monto * $rate);

							if($tipo_doc_emitido == '20'){//NOTA DE CREDITO
						        RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '', '', '', '', '', '');
						        $data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto);
						        $arrNC[] = array(
						        	"sTipoDocumento" => $tipo_doc_emitido,
						        	"sSerieDocumento" => $tipo_doc_serie,
						        	"sNumeroDocumento" => $tipo_doc_numero,
						        	"sIdEntidad" => $ruc_cliente,
				        			"fAmountHeader" => $monto,
						       	);
							}
/*
							else if($tipo_doc_emitido == '11'){//NOTA DE DEBITO
						        RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '', '', '', '', '', '');
						        $data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto);
						        $arrNC[] = array(
						        	"sTipoDocumento" => $tipo_doc_emitido,
						        	"sSerieDocumento" => $tipo_doc_serie,
						        	"sNumeroDocumento" => $tipo_doc_numero,
						        	"sIdEntidad" => $ruc_cliente,
				        			"fAmountHeader" => $monto,
						       	);
							}
*/

							//FACTURAS Y BOLETAS Y OTROS
							if($tipo_doc_emitido != '20'){
								$valor 						= $tipo_doc_emitido.' - '.$tipo_doc_serie.' - '.$tipo_doc_numero;
								$cadena_numero_documento 	= "'$valor',";
								$cade_num_do 				= substr($cadena_numero_documento, 0, -1);

								/* VERIFICAR SI LA FACTURA O BOLETA TIENE O NO (NC O ND) */
		        				$validar = RegistroCajasModel::Verify_FC_BV($cade_num_do);
		        				//var_dump($validar);
		        				if($validar){
									if(count($datos_facturas) > 1)
										$i++;//Para validar el documento a cancelar

									$nu_serie .= $tipo_doc_serie.",";
									$nu_documento .= $tipo_doc_numero.",";

			            	echo "<pre>";
			            	echo "pago total descontando -> "; print_r($monto_pago_total_descontando); echo "<br>";
			            	echo "pago total -> "; print_r($monto_pago_total); echo "<br>";
			            	echo "montos de F/B/NC/ND -> "; print_r($monto); echo "<br>";
			            	echo "<pre>";

						           	$monto_facturayboleta_restando_nc = RegistroCajasModel::Verify_Amount_CxB($tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero);
						           	$monto_facturayboleta_aumentando_nd = RegistroCajasModel::Verify_Amount_ND($tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero);
						           	$data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto_factura);

						        	if($monto_pago_total_descontando >= $monto){
						           		RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '', $nu_serie, $nu_documento, $i, $datos_facturas, $monto_pago_total_other);
						           		//$monto_pago_total -= $monto;
						           		$monto_pago_total -= $monto + $monto_facturayboleta_aumentando_nd;
						           		$monto_pago_total_descontando -= ($monto - abs($monto_facturayboleta_restando_nc) + $monto_facturayboleta_aumentando_nd);
						        	} else {
						        		//$monto_pago_total = abs($monto_pago_total) + $monto_facturayboleta_restando_nc;
						        		$monto_pago_total = abs($monto_pago_total) + $monto_facturayboleta_restando_nc - $monto_facturayboleta_aumentando_nd;
										RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto_pago_total, $reference, $almacen, '', '', $nu_serie, $nu_documento, $i, $datos_facturas, $monto_pago_total_other);						        		
						        	}
						        	//echo "con nota de crédito";
						        }else{
						        	//echo "sin nota de crédito";

			            	echo "<pre>";
			            	echo "pago total descontando -> "; print_r($monto_pago_total_descontando); echo "<br>";
			            	echo "pago total -> "; print_r($monto_pago_total); echo "<br>";
			            	echo "total de F/B/NC/ND -> "; print_r($monto); echo "<br>";
			            	echo "<pre>";

			            			//if( $monto >= $monto_pago_total ){
			            			//	$monto = $monto_pago_total;
			            			if( $monto >= $monto_pago_total_descontando ){
			            				$monto = $monto_pago_total_descontando;
							           	RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '', '', '', '', '', '');
							            $data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto);
							            break;
			            			}

						        	//if($monto_pago_total >= $monto){
						        	if($monto_pago_total_descontando >= $monto){
							           	RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '', '', '', '', '', '');
							            $data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto);
										//$monto_pago_total -= $monto;
										$monto_pago_total_descontando -= $monto;
									}else{
							           	RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto_pago_total, $reference, $almacen, '', '', '', '', '', '', '');
							            $data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto_pago_total);
							        }
							    }
							}
						}
					} else if ($factura_global == $medio_pago) {

						$tipo_pago = "todo";

				    	$datos_facturas = $_REQUEST['datos_factura'];

				    	foreach ($datos_facturas as $valores) {

							$informacion 		= explode("*", $valores);
							$tipo_doc 			= $informacion[0];
							$tipo_doc_serie 	= $informacion[1];
							$tipo_doc_numero 	= $informacion[2];
							$monto				= $informacion[3];
							$reference 			= $informacion[4];
							$moneda 			= $informacion[5];
							$tipo_doc_emitido 	= $informacion[6];

							$monto 				= (float)($monto);

				        	RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '', '', '', '', '', '');

				        	$data_actualizar[] = array("td" => $tipo_doc_emitido, "serie" => $tipo_doc_serie, "num" => $tipo_doc_numero, "ruc" => $ruc_cliente, "importe" => $monto);
						}
					}

			}

			// Realizar UPDATE a los documentos que hace referencia la N/C, actualmente no se está considerando
			foreach ($_REQUEST['datos_factura'] as $row) {
				$row = explode("*", $row);
				if ( $row[0] == '20' ){					
					$tipo_doc 			= $row[0];
					$tipo_doc_serie 	= $row[1];
					$tipo_doc_numero 	= $row[2];
					$monto				= $row[3];
					$reference 			= $row[4];
					$moneda 			= $row[5];
					$tipo_doc_emitido 	= $row[6];
					$monto 				= (float)($monto);
		        	RegistroCajasModel::Insertarc_cuentas_x_cobrar_detalle($ruc_cliente, $tipo_doc_emitido, $tipo_doc_serie, $tipo_doc_numero, '2', '2', $fecha_general, $moneda, $rate, $monto, $reference, $almacen, '', '', '', '', '', '', '');
				}
			}

/*
			if ( isset($arrNC) ){
				foreach ($arrNC as $row) {
					$arrResponse = RegistroCajasModel::ActualizarMontosReferenciaCabecera($row);
					if ( $arrResponse['sStatus'] != 'success') {
						RegistroCajasModel::ROLLBACKTransaccion();
						echo json_encode($arrResponse);
						exit();
					}
				}
			}
*/

			RegistroCajasModel::ActualizarNroDocuemnto_Recibo($almacen);

			RegistroCajasModel::COMMITransaccion();

			echo "{'estado':'bien','mes':'Recibo se registro correctamente'}";

			} catch (Exception $e) {
				RegistroCajasModel::ROLLBACKTransaccion();
				throw $e;
			}
		}
	} catch (Exception $r) {
		echo "{'estado':'error','mes':'" . $r->getMessage() . "'}";
	}

