<?php

session_start();
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('liquidacion_vales/m_liquidacion_vales.php');
include('liquidacion_vales/c_liquidacion_vales.php');
include('liquidacion_vales/t_liquidacion_vales.php');

$objmodel = new LiquidacionValesModel();
$objtem = new LiquidacionValesTemplate();
$objcomn = new LiquidacionValesController();

$accion = $_REQUEST['accion'];

try {

    if ($accion == "ordenarchofer") {

        $fecha_inicio   = $_REQUEST['fecha_inicio'];
        $fecha_final    = $_REQUEST['fecha_final'];
        $ruc            = $_REQUEST['ruc'];
        $vales_sele     = 'NOALL';

        $orderchofer    = trim($_REQUEST['orderchofer']);

        $result         = LiquidacionValesModel::MostarValesDeUnCliente($fecha_inicio, $fecha_final, $ruc, $orderchofer);
        $datos_cliente  = LiquidacionValesModel::ObtenerdatosCliente($ruc);

        LiquidacionValesTemplate::CrearTablaVervales($result, $datos_cliente, $fecha_inicio, $fecha_final, $vales_sele);

    }else if ($accion == "tipodocumento") {
        $tipo_doc_numero = trim($_REQUEST['documento']);
        $result = LiquidacionValesModel::obtenerTiposDocumento($tipo_doc_numero);
        $cmb_serie = "<select id='serie_doc'>";
        foreach ($result as $value) {
            $cmb_serie.="<option value='$value[0]'>" . $value[0] . "#" . $value[1] . "</option>";
        }
        $cmb_serie.="</select>";
        echo $cmb_serie;
    } else if ($accion == "selecionabtn") {

        /* $fecha_inicio = explode("/", $_REQUEST['fecha_inicio']);
          $fecha_inicio = $fecha_inicio[2] . "-" . $fecha_inicio[0] . "-" . $fecha_inicio[1];
          $fecha_final = explode("/", $_REQUEST['fecha_final']);
          $fecha_final = $fecha_final[2] . "-" . $fecha_final[0] . "-" . $fecha_final[1]; */

        $fecha_inicio = $_REQUEST['fecha_inicio'];
        $fecha_final = $_REQUEST['fecha_final'];


        $result = LiquidacionValesModel::MostarClienteVales_rangoFecha($fecha_inicio, $fecha_final);
        if (count($result) == 0) {
            echo "ERROR_:Busqueda sin Resultado Verifique sus datos de Ingreso";
            return;
        }

        LiquidacionValesTemplate::CrearTablaSeleccionarCliente($result, $fecha_inicio, $fecha_final);
    } else if ($accion == "ver_vales") {

        $fecha_inicio = $_REQUEST['fecha_inicio'];
        $fecha_final = $_REQUEST['fecha_final'];
        $ruc = $_REQUEST['ruc'];
        $vales_sele = $_REQUEST['valesselecionada'];
        $vales_sele = str_replace("'", "", $vales_sele);

        $result = LiquidacionValesModel::MostarValesDeUnCliente($fecha_inicio, $fecha_final, $ruc, NULL);
        $datos_cliente = LiquidacionValesModel::ObtenerdatosCliente($ruc);
        LiquidacionValesTemplate::CrearTablaVervales($result, $datos_cliente, $fecha_inicio, $fecha_final, $vales_sele);

	} else if ($accion == "buscar_liquidacion") {

		$fecha_inicio	= $_REQUEST['fecha_inicio'];
		$fecha_final	= $_REQUEST['fecha_final'];

		try {

			$datos_liquidaciones = LiquidacionValesModel::BuscarLiquidaciones($fecha_inicio, $fecha_final);
            $taxOptional = LiquidacionValesModel::GetTaxOptional();

			if (count($datos_liquidaciones) == 0) {
		        	throw new Exception("Error");
		    	}

			LiquidacionValesTemplate::CrearTabladatosLiquidacionProducto_Busqueda($datos_liquidaciones,$taxOptional);

		} catch (Exception $e) {

			echo "ERROR_:No se encontro liquidaciones disponibles";
			exit();

		}

	} else if ($accion == "liquidar_vales") {

		//formato fecha
		$notas_depacho_efectivo	= array();
		$fecha_inicio		= $_REQUEST['fecha_inicio'];
		$fecha_final		= $_REQUEST['fecha_final'];
		$codigo_hermandad	= date("dnHis");
		$estado_nega		= $_REQUEST['estado_negativo'];
        $estado_exo         = $_REQUEST['estado_exonerado'];//cai
        $transfe_grat       = $_REQUEST['transferencia_gratuita'];//caix2

		$aregloOficialClientes	= array();

        	foreach ($_REQUEST['vales'] as $key => $value) {

            		$cod_cliente	= trim(substr($value, 0, strpos($value, "-")));
            		$vales		= trim(substr($value, strpos($value, "-") + 1));
            		$cadena_vales	= "(" . substr($value, strpos($value, "-") + 1, -1) . ")";

			if (strcmp($vales, 'NOALL') == 0) {
                		continue;
            		} else if (strcmp($vales, 'ALL') == 0) {

                		$datosrs = LiquidacionValesModel::MostarValesDeUnCliente($fecha_inicio, $fecha_final, $cod_cliente, NULL);
                		$cadena_vales = "(";

                		foreach ($datosrs as $key => $value) {
					$cadena_vales.= "'" . trim($value['ch_documento']) . "',";
				}

                		$cadena_vales = substr($cadena_vales, 0, -1) . ")";
                		$aregloOficialClientes[$cod_cliente] = $cadena_vales;

            		} else {
				$aregloOficialClientes[$cod_cliente] = $cadena_vales;
			}

		}

		if (count($aregloOficialClientes) == 0) {
            		echo "ERROR_:Debe Seleccionar Clientes o notas de Despachos.";
        	}

        	foreach ($aregloOficialClientes as $ruc_cli => $cadena_vales) {

		    	$ruc			= $ruc_cli;
		    	$tipo_operacion 	= trim($_REQUEST['tipo_opeacion']);
		    	$tipo_doc_numero 	= trim($_REQUEST['documento']);
		    	$num_seriedocumento 	= trim($_REQUEST['serie_actual']);
		    	$fecha_liqui 		= trim($_REQUEST['fecha_liqui']);

			//COMIENZA LA CREACION DE LA FACTURA

            		$falg_anticipo = false;

            		if ($tipo_operacion == "01") {//CLIENTE NORMAL

				try {
                    			LiquidacionValesModel::IniciarTransaccion(); //INICIAR TRANSACION 

				    	$rsdata 			    = LiquidacionValesModel::LiquidacionValesUnicoCliente($fecha_inicio, $fecha_final, $ruc, $cadena_vales,$estado_nega);//$estado_nega
				    	$datos_inicales_documento 	= LiquidacionValesModel::ObtenerdatosFactura($tipo_doc_numero, $num_seriedocumento);
				    	$datoscliente 			= LiquidacionValesModel::ObtenerdatosCliente($ruc_cli);
				    	$sucursal 			    = LiquidacionValesModel::ObtenerdatosSucurasles();
                    	$listo_para_facturar 	= LiquidacionValesController::AgruparRegistoFacturaNormal($rsdata, $sucursal);


					if (array_key_exists('N', $listo_para_facturar)) {

                        			$fecha_liqui = $fecha_liqui;

                                    $datosweb = LiquidacionValesModel::procesoDocumnetoLiquidarUnicoClienteXNormal($tipo_doc_numero, $num_seriedocumento, $fecha_liqui, $listo_para_facturar, $datoscliente, $datos_inicales_documento, $cadena_vales, $fecha_inicio, $fecha_final, $codigo_hermandad,$estado_exo,$transfe_grat);
				        	LiquidacionValesModel::ActualizarValesGeneral($fecha_inicio, $fecha_final, $ruc, $cadena_vales);

						$falg_anticipo	= true;
						$ArrayMontos	= array();

						foreach ($datosweb as $key => $value) {
							$num_doc_tmp				= str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
							$rs_tmp					= LiquidacionValesModel:: MostarMontoDocumetos($tipo_doc_numero, $num_seriedocumento, $num_doc_tmp, $value['cli'], $fecha_liqui, $value['num_liquidacion'], $value['cli']);
						 	$rs_tmp['tipo_doc']			= $value['tipo_doc'];
							$rs_tmp['rz']				= $value['rz'];
							$ArrayMontos[$value['num_liquidacion']] = $rs_tmp;
						}

						LiquidacionValesTemplate::CrearTabladatosLiquidacion($datosweb, $ArrayMontos, $fecha_liqui);

				    	}

                    	foreach ($datosweb as $record) {

						$cod_cliente = trim($record['cli']);
						$estado = LiquidacionValesModel::Verificar_nota_despacho_efectivo($cod_cliente);
						if ($estado['cli_ndespacho_efectivo'] == "1") {
						    $ch_tipdocumento = $record['tipo_doc'];
						    $ch_seriedocumento = $record['serie'];
						    $ch_numdocumento = str_pad($record['num_doc'], 7, "0", STR_PAD_LEFT);
						    $ch_numdocreferencia = $record['num_liquidacion'];
						    if (isset($ch_tipdocumento) && isset($ch_seriedocumento) && isset($ch_numdocumento) && isset($ch_numdocreferencia)) {
						        LiquidacionValesModel::EleminarCuentas_x_pagar_cliente_NDE($cod_cliente, $ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $ch_numdocreferencia);
						        }
                        	}
                    	}

					LiquidacionValesModel::COMMITransaccion(); //CONFIRMAR TRANSACION

                		} catch (Exception $e) {

 					LiquidacionValesModel::ROLLBACKTransaccion(); //INICIAR TRANSACION
					throw new Exception("ERROR_:" . $e->getMessage());
                		}

            		} else if ($tipo_operacion == "02") {

                	try {
                    LiquidacionValesModel::IniciarTransaccion(); //INICIAR TRANSACION 
                    $rsdata = LiquidacionValesModel::LiquidacionValesUnicoClienteSoloVales($fecha_inicio, $fecha_final, $ruc, $cadena_vales);
                    $datos_inicales_documento = LiquidacionValesModel::ObtenerdatosFactura($tipo_doc_numero, $num_seriedocumento);
                    $datoscliente = LiquidacionValesModel::ObtenerdatosCliente($ruc_cli);
                    $listo_para_facturar = LiquidacionValesController::AgruparRegistoFacturaXNotaDespacho($rsdata);
                    if (array_key_exists('N', $listo_para_facturar)) {
                        $fecha_liqui = $fecha_liqui;
                        //$fecha_liqui = $fecha_liqui[2] . "-" . $fecha_liqui[0] . "-" . $fecha_liqui[1];
                        $datosweb = LiquidacionValesModel::procesoDocumnetoLiquidarUnicoClienteXNotaDespacho($tipo_doc_numero, $num_seriedocumento, $fecha_liqui, $listo_para_facturar, $datoscliente, $datos_inicales_documento, $cadena_vales, $fecha_inicio, $fecha_final, $codigo_hermandad,$estado_exo,$transfe_grat);

                        LiquidacionValesModel::ActualizarValesGeneral($fecha_inicio, $fecha_final, $ruc, $cadena_vales);
                        $falg_anticipo = true;

                        $ArrayMontos = array();
                        foreach ($datosweb as $key => $value) {
                            $num_doc_tmp = str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                            $rs_tmp = LiquidacionValesModel:: MostarMontoDocumetos($tipo_doc_numero, $num_seriedocumento, $num_doc_tmp, $value['cli'], $fecha_liqui, $value['num_liquidacion'], $value['cli']);
                            $rs_tmp['opcion'] = $value['ND'];
                            $rs_tmp['tipo_doc'] = $value['tipo_doc'];
                            $rs_tmp['rz'] = $value['rz'];
                            $ArrayMontos[$value['num_liquidacion']] = $rs_tmp;
                        }
                        LiquidacionValesTemplate::CrearTabladatosLiquidacionND($datosweb, $ArrayMontos, $fecha_liqui);
                    }

                    foreach ($datosweb as $record) {

                        $cod_cliente = trim($record['cli']);
                        $estado = LiquidacionValesModel::Verificar_nota_despacho_efectivo($cod_cliente);
                        if ($estado['cli_ndespacho_efectivo'] == "1") {
                            $ch_tipdocumento = $record['tipo_doc'];
                            $ch_seriedocumento = $record['serie'];
                            $ch_numdocumento = str_pad($record['num_doc'], 7, "0", STR_PAD_LEFT);
                            $ch_numdocreferencia = $record['num_liquidacion'];
                            if (isset($ch_tipdocumento) && isset($ch_seriedocumento) && isset($ch_numdocumento) && isset($ch_numdocreferencia)) {
                                LiquidacionValesModel::EleminarCuentas_x_pagar_cliente_NDE($cod_cliente, $ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $ch_numdocreferencia);
                            }
                        }
                    }
                    LiquidacionValesModel::COMMITransaccion(); //CONFIRMAR TRANSACION 
                } catch (Exception $e) {
                    LiquidacionValesModel::ROLLBACKTransaccion(); //INICIAR TRANSACION
                    throw new Exception("ERROR_:" . $e->getMessage());
                }
            } else if ($tipo_operacion == "03") {
                try {
                    LiquidacionValesModel::IniciarTransaccion(); //INICIAR TRANSACION 
                    $rsdata = LiquidacionValesModel::LiquidacionValesUnicoCliente($fecha_inicio, $fecha_final, $ruc, $cadena_vales);
                    $datos_inicales_documento = LiquidacionValesModel::ObtenerdatosFactura($tipo_doc_numero, $num_seriedocumento);
                    $datoscliente = LiquidacionValesModel::ObtenerdatosCliente($ruc_cli);

                    $listo_para_facturar = LiquidacionValesController::AgruparRegistoFacturaXPlaca($rsdata);
                    if (array_key_exists('N', $listo_para_facturar)) {
                        $fecha_liqui = $fecha_liqui;
                        // $fecha_liqui = $fecha_liqui[2] . "-" . $fecha_liqui[0] . "-" . $fecha_liqui[1];
                        $datosweb = LiquidacionValesModel::procesoDocumnetoLiquidarUnicoClienteXPlaca($tipo_doc_numero, $num_seriedocumento, $fecha_liqui, $listo_para_facturar, $datoscliente, $datos_inicales_documento, $cadena_vales, $fecha_inicio, $fecha_final, $codigo_hermandad,$estado_exo,$transfe_grat);

                        LiquidacionValesModel::ActualizarValesGeneral($fecha_inicio, $fecha_final, $ruc, $cadena_vales);
                        $falg_anticipo = true;
                        $ArrayMontos = array();
                        foreach ($datosweb as $key => $value) {
                            $num_doc_tmp = str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                            $rs_tmp = LiquidacionValesModel:: MostarMontoDocumetos($tipo_doc_numero, $num_seriedocumento, $num_doc_tmp, $value['cli'], $fecha_liqui, $value['num_liquidacion'], $value['cli']);
                            $rs_tmp['opcion'] = $value['placa'];
                            $rs_tmp['tipo_doc'] = $value['tipo_doc'];
                            $rs_tmp['rz'] = $value['rz'];
                            $ArrayMontos[$value['num_liquidacion']] = $rs_tmp;
                        }
                        LiquidacionValesTemplate::CrearTabladatosLiquidacionPlaca($datosweb, $ArrayMontos, $fecha_liqui);
                    }

                    foreach ($datosweb as $record) {

                        $cod_cliente = trim($record['cli']);
                        $estado = LiquidacionValesModel::Verificar_nota_despacho_efectivo($cod_cliente);
                        if ($estado['cli_ndespacho_efectivo'] == "1") {
                            $ch_tipdocumento = $record['tipo_doc'];
                            $ch_seriedocumento = $record['serie'];
                            $ch_numdocumento = str_pad($record['num_doc'], 7, "0", STR_PAD_LEFT);
                            $ch_numdocreferencia = $record['num_liquidacion'];
                            if (isset($ch_tipdocumento) && isset($ch_seriedocumento) && isset($ch_numdocumento) && isset($ch_numdocreferencia)) {
                                LiquidacionValesModel::EleminarCuentas_x_pagar_cliente_NDE($cod_cliente, $ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $ch_numdocreferencia);
                            }
                        }
                    }
                    LiquidacionValesModel::COMMITransaccion(); //CONFIRMAR TRANSACION 
                } catch (Exception $e) {
                    echo $e->getMessage();
                    LiquidacionValesModel::ROLLBACKTransaccion(); //INICIAR TRANSACION
                    throw new Exception("ERROR_:" . $e->getMessage());
                }
            } else if ($tipo_operacion == "04") {
                try {
                    LiquidacionValesModel::IniciarTransaccion(); //INICIAR TRANSACION 
                    $rsdata = LiquidacionValesModel::LiquidacionValesUnicoCliente($fecha_inicio, $fecha_final, $ruc, $cadena_vales);
                    $datos_inicales_documento = LiquidacionValesModel::ObtenerdatosFactura($tipo_doc_numero, $num_seriedocumento);
                    $datoscliente = LiquidacionValesModel::ObtenerdatosCliente($ruc_cli);

                    $listo_para_facturar = LiquidacionValesController::AgruparRegistoFacturaXProducto($rsdata);
                    if (array_key_exists('N', $listo_para_facturar)) {
                        $fecha_liqui = $fecha_liqui;
                        //$fecha_liqui = $fecha_liqui[2] . "-" . $fecha_liqui[0] . "-" . $fecha_liqui[1];
                        $datosweb = LiquidacionValesModel::procesoDocumnetoLiquidarUnicoClienteXProducto($tipo_doc_numero, $num_seriedocumento, $fecha_liqui, $listo_para_facturar, $datoscliente, $datos_inicales_documento, $cadena_vales, $fecha_inicio, $fecha_final, $codigo_hermandad,$estado_exo,$transfe_grat);

                        LiquidacionValesModel::ActualizarValesGeneral($fecha_inicio, $fecha_final, $ruc, $cadena_vales);
                        $falg_anticipo = true;
                        $ArrayMontos = array();
                        foreach ($datosweb as $key => $value) {
                            $num_doc_tmp = str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                            $rs_tmp = LiquidacionValesModel:: MostarMontoDocumetos($tipo_doc_numero, $num_seriedocumento, $num_doc_tmp, $value['cli'], $fecha_liqui, $value['num_liquidacion'], $value['cli']);
                            $rs_tmp['opcion'] = $value['producto'];
                            $rs_tmp['tipo_doc'] = $value['tipo_doc'];
                            $rs_tmp['rz'] = $value['rz'];
                            $ArrayMontos[$value['num_liquidacion']] = $rs_tmp;
                        }
                        LiquidacionValesTemplate::CrearTabladatosLiquidacionProducto($datosweb, $ArrayMontos, $fecha_liqui);
                    }

                    foreach ($datosweb as $record) {

                        $cod_cliente = trim($record['cli']);
                        $estado = LiquidacionValesModel::Verificar_nota_despacho_efectivo($cod_cliente);
                        if ($estado['cli_ndespacho_efectivo'] == "1") {
                            $ch_tipdocumento = $record['tipo_doc'];
                            $ch_seriedocumento = $record['serie'];
                            $ch_numdocumento = str_pad($record['num_doc'], 7, "0", STR_PAD_LEFT);
                            $ch_numdocreferencia = $record['num_liquidacion'];
                            if (isset($ch_tipdocumento) && isset($ch_seriedocumento) && isset($ch_numdocumento) && isset($ch_numdocreferencia)) {
                                LiquidacionValesModel::EleminarCuentas_x_pagar_cliente_NDE($cod_cliente, $ch_tipdocumento, $ch_seriedocumento, $ch_numdocumento, $ch_numdocreferencia);
                            }
                        }
                    }
                    LiquidacionValesModel::COMMITransaccion(); //CONFIRMAR TRANSACION 
                } catch (Exception $e) {
                    LiquidacionValesModel::ROLLBACKTransaccion(); //INICIAR TRANSACION
                    throw new Exception("ERROR_:" . $e->getMessage());
                }
            }
            //si es anticipo
            if ($falg_anticipo == false) {
                try {
                    LiquidacionValesModel::IniciarTransaccion(); //INICIAR TRANSACION 
                    
                     

                       
                    
                    $rsdata = LiquidacionValesModel::LiquidacionValesUnicoCliente($fecha_inicio, $fecha_final, $ruc, $cadena_vales);
                    $datos_inicales_documento = LiquidacionValesModel::ObtenerdatosFactura($tipo_doc_numero, $num_seriedocumento);
                    $datoscliente = LiquidacionValesModel::ObtenerdatosCliente($ruc_cli);
                    
                        $cod_cliente = trim($datoscliente['cli_ruc']);
                        $estado = LiquidacionValesModel::Verificar_nota_despacho_efectivo($cod_cliente);
                        if ($estado['cli_ndespacho_efectivo'] == "1") {
                            throw new Exception("Lo siento no se pruede Procesar por el cliente con anticipo,tambien tiene  la opcion de  nota de despacho en efectivo=SI .");
                        }

                    $listo_para_facturar = LiquidacionValesController::AgruparRegistoFacturaNormal($rsdata);
                    if (array_key_exists('S', $listo_para_facturar)) {

                        $fecha_liqui = $fecha_liqui;
                        //$fecha_liqui = $fecha_liqui[2] . "-" . $fecha_liqui[0] . "-" . $fecha_liqui[1];

                        $rsdata = LiquidacionValesModel::LiquidacionValesUnicoCliente($fecha_inicio, $fecha_final, $ruc, $cadena_vales);
                        $datos_inicales_anticipo = LiquidacionValesModel::ObtenerdatosANTICIPOFactura();
                        $datos_inicales_documento = LiquidacionValesModel::ObtenerdatosFactura($tipo_doc_numero, $num_seriedocumento);
                        $datoscliente = LiquidacionValesModel::ObtenerdatosCliente($ruc_cli);
                        $listo_para_facturar = LiquidacionValesController::AgruparRegistoFacturaNormal($rsdata);

                        $tipocambio = LiquidacionValesModel::validarTipoCambio($fecha_liqui);

                        $NUMERO_DOCUMENTO_ANTI = $datos_inicales_anticipo['num_act_documneto'];
                        $num_liquidacion = ($datos_inicales_documento['num_LV'] + 1);
                        $ccob_documento = array();
                        foreach ($listo_para_facturar as $anticipo => $cliente) {
                            foreach ($cliente as $clie => $sucursal) {
                                foreach ($sucursal as $sucur => $articulos) {
                                    foreach ($articulos as $kart => $valores) {
                                        $ccob_documento[$sucur]+=$valores['importe'];
                                    }
                                }
                            }
                        }
                        $codigo_cliente = trim($datoscliente['cli_codigo']);
                        $datosweb = array();
                        foreach ($ccob_documento as $keysucursal => $totalimporte) {
                            LiquidacionValesModel::procesoDocumnetoLiquidarConAnticipo($num_seriedocumento, $NUMERO_DOCUMENTO_ANTI, $codigo_cliente, $fecha_liqui, $num_liquidacion, $totalimporte);
                            $datosweb[$keysucursal][$num_liquidacion] = array(
                                "num_docu" => $NUMERO_DOCUMENTO_ANTI,
                                "num_serie" => $num_seriedocumento,
                                "cod_cli" => $codigo_cliente,
                                "fecha_liqui" => $fecha_liqui,
                                "num_liquidacion" => $num_liquidacion,
                                "totalimporte" => $totalimporte,
                                "rz" => $datoscliente['cli_razsocial']
                            );
                        }
                        

                        foreach ($listo_para_facturar as $anticipo => $cliente) {
                            foreach ($cliente as $clie => $sucursal) {
                                foreach ($sucursal as $sucur => $articulos) {
                                    foreach ($articulos as $articulo => $valores) {
                                        $NUM_LIQUI_CADE = str_pad($num_liquidacion, 10, '0', STR_PAD_LEFT);
                                        LiquidacionValesModel::ActualizarVales_LiquidacionXcobrar($tipo_doc_numero, $fecha_inicio, $fecha_final, $ruc, $cadena_vales, $num_seriedocumento, $NUMERO_DOCUMENTO_ANTI, $articulo, $NUM_LIQUI_CADE, $fecha_liqui, $codigo_hermandad);
                                    }
                                }
                            }
                        }

                        LiquidacionValesModel::ActualizarValesGeneral($fecha_inicio, $fecha_final, $ruc, $cadena_vales);

                        $ArrayMontos = array();
                        LiquidacionValesTemplate::CrearTabladatosLiquidacionCuentasPorCobrar($datosweb, $ruc);
                    }

                    LiquidacionValesModel::COMMITransaccion(); //CONFIRMAR TRANSACION 
                } catch (Exception $e) {
                    LiquidacionValesModel::ROLLBACKTransaccion(); //INICIAR TRANSACION
                    throw new Exception("ERROR_:" . $e->getMessage());
                }
            }
        }//fin del form principal
    }
} catch (Exception $r) {
    echo $r->getMessage();
}
