<?php
ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

class RegistroVentasController extends Controller {

	function Init() {
    	$this->visor = new Visor();
    	isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = '';
	}

	function Run() {

		ob_start();
		include 'reportes/m_registro_ventas.php';
		include 'reportes/t_registro_ventas.php';

		$this->Init();
		$result = '';
		$result_f = '';
		$search_form = false;

        //Obj
        $templateRegistroVentas = new RegistroVentasTemplate();
        $modelRegistroVentas = new RegistroVentasModel();

        switch ($this->action) {
            case "Reporte":
		        $almacen		= $_REQUEST['almacen'];
		        $anio 			= trim($_REQUEST['anio']);
		        $mes 			= trim($_REQUEST['mes']);
		        $desde 			= trim($_REQUEST['desde']);
		        $hasta 			= trim($_REQUEST['hasta']);
		        $tipo 			= $_REQUEST['tipo'];
		        $orden 			= $_REQUEST['orden'];
		        $nd 			= $_REQUEST['nd'];//Nota Despacho
		        $gnv 			= $_REQUEST['gnv'];
		        $seriesdocumentos 	= $_REQUEST['seriesdocs'];

		        $serie 			= $_REQUEST['serie'];
		        $nserie 		= $_REQUEST['nserie'];

		        $BI_incre 		= $_REQUEST['bi'];
		        $IGV_incre 		= $_REQUEST['igv'];
		        $TOTAL_incre 		= $_REQUEST['valor_venta'];
				$monto_sistema 		= $_REQUEST['sistema'];// ya no se usa esta varibale
		        $monto_igual 		= 0;//ya no se usa este variable

		        $results = $modelRegistroVentas->obtieneRegistros($almacen, $anio, $mes, $desde, $hasta, $tipo, $orden, $seriesdocumentos, $monto_sistema='S', $serie, $nserie, $BI_incre, $IGV_incre, $TOTAL_incre,$monto_igual='S', $nd);
		        if ($gnv == 'S') {
		            $resultgnv = $modelRegistroVentas->obtieneRegistrosGNV($anio, $mes, $desde, $hasta);
				}
				/*** Agregado 2020-02-04 ****/
				echo "<pre>";
				print_r( array($almacen, $anio, $mes, $desde, $hasta, $tipo, $orden, $seriesdocumentos, $monto_sistema='S', $serie, $nserie, $BI_incre, $IGV_incre, $TOTAL_incre,$monto_igual='S', $nd) );
				echo "</pre>";

				echo "<pre>";
				print_r($results);
				echo "</pre>";

				echo "<pre>";
				print_r($resultgnv);
				echo "</pre>";
				/***/

		        $arrParamsPOST = array(
		        	"sTablePostransYM" => 'pos_trans'.$anio.$mes,
		        	"sCodigoAlmacen" => $almacen,
		        );
		        $result_f = $templateRegistroVentas->reporte($results, $resultgnv, $BI_incre, $IGV_incre, $TOTAL_incre, $arrParamsPOST);

                break;

            case "PDF":

		        $almacen 		= $_REQUEST['almacen'];
		        $anio 			= trim($_REQUEST['anio']);
		        $mes 			= trim($_REQUEST['mes']);
		        $desde 			= trim($_REQUEST['desde']);
		        $hasta 			= trim($_REQUEST['hasta']);
		        $tipo 			= $_REQUEST['tipo'];
		        $orden 			= $_REQUEST['orden'];
		        $nd 			= $_REQUEST['nd'];//Nota Despacho
		        $gnv 			= $_REQUEST['gnv'];
		        $seriesdocumentos 	= $_REQUEST['seriesdocs'];
		        $monto_sistema 		= $_REQUEST['sistema'];
		        $serie 			= $_REQUEST['serie'];
		        $nserie 		= $_REQUEST['nserie'];
		        $BI_incre 		= $_REQUEST['bi'];
		        $IGV_incre 		= $_REQUEST['igv'];
		        $TOTAL_incre 		= $_REQUEST['valor_venta'];
		        $monto_igual 		= $_REQUEST['monto_igual'];

		        $arrParamsPOST = array(
		        	"sTablePostransYM" => 'pos_trans'.$anio.$mes,
		        	"sCodigoAlmacen" => $almacen,
		        );
		        $results = $modelRegistroVentas->obtieneRegistros($almacen, $anio, $mes, $desde, $hasta, $tipo, $orden, $seriesdocumentos, $monto_sistema, $serie, $nserie, $BI_incre, $IGV_incre, $TOTAL_incre,$monto_igual, $nd);
		        echo $templateRegistroVentas->reportePDF($results, $almacen, $anio, $mes, $tipo, $BI_incre, $IGV_incre, $TOTAL_incre, $modelRegistroVentas, $arrParamsPOST);

                break;

            case "Excel":

		        $almacen 		= $_REQUEST['almacen'];
		        $anio 			= trim($_REQUEST['anio']);
		        $mes 			= trim($_REQUEST['mes']);
		        $tipo 			= $_REQUEST['tipo'];
		        $orden 			= $_REQUEST['orden'];
		        $BI 			= $_REQUEST['bi'];
		        $difIgv 		= $_REQUEST['igv'];
		        $valor_venta 	= $_REQUEST['valor_venta'];
		        $desde 			= trim($_REQUEST['desde']);
		        $hasta 			= trim($_REQUEST['hasta']);
		        $nd 			= $_REQUEST['nd'];//Nota Despacho
		        $gnv 			= $_REQUEST['gnv'];
		        $correlativo 		= $_REQUEST['correlativo'];
		        $seriesdocumentos 	= $_REQUEST['seriesdocs'];
		        $serie 				= $_REQUEST['serie'];
		        $nserie 			= $_REQUEST['nserie'];
		        $BI_incre 			= $_REQUEST['bi'];
		        $IGV_incre 			= $_REQUEST['igv'];
		        $TOTAL_incre 		= $_REQUEST['valor_venta'];
		        $monto_igual 		= $_REQUEST['monto_igual'];

		        $resultgnv = array();

		        if ($gnv == 'S')
					$resultgnv = $modelRegistroVentas->obtieneRegistrosGNV($anio, $mes, $desde, $hasta);

		        $results = $modelRegistroVentas->obtieneRegistros($almacen, $anio, $mes, $desde, $hasta, $tipo, $orden, $seriesdocumentos, 'S', $serie, $nserie, $BI_incre, $IGV_incre, $TOTAL_incre,$monto_igual, $nd);

		        $_SESSION['info'] 	= $results;
		        $_SESSION['biincre'] 	= $BI_incre;
		        $_SESSION['igvincre'] 	= $BI_incre;
		        $_SESSION['totincre'] 	= $BI_incre;
		        $_SESSION['sTipoVistaReporte'] = $_REQUEST['tipo'];
				$arrParamsPOST = array(
		        	"sTablePostransYM" => 'pos_trans'.$anio.$mes,
		        	"sCodigoAlmacen" => $almacen,
				);
				$_SESSION['arrParamsPOST_excel'] = $arrParamsPOST;				

		        header("Location: /sistemaweb/ventas_clientes/reporte_ventas_detalla.php");

                break;

            case "Libros-Electronico":

		        $almacen		= $_REQUEST['almacen'];
		        $anio 			= trim($_REQUEST['anio']);
		        $mes 			= trim($_REQUEST['mes']);
		        $tipo 			= $_REQUEST['tipo'];
		        $orden 			= $_REQUEST['orden'];
		        $BI_incre 		= $_REQUEST['bi'];
		        $IGV_incre 		= $_REQUEST['igv'];
		        $TOTAL_incre 		= $_REQUEST['valor_venta'];
		        $desde 			= trim($_REQUEST['desde']);
		        $hasta 			= trim($_REQUEST['hasta']);
		        $nd 			= $_REQUEST['nd'];//Nota Despacho
		        $gnv 			= $_REQUEST['gnv'];
		        $correlativo 		= $_REQUEST['correlativo'];
		        $seriesdocumentos 	= $_REQUEST['seriesdocs'];
		        $tipo_ple 		= $_REQUEST['tipo_ple'];
		        $monto_sistema 		= $_REQUEST['sistema'];
		        $serie 			= $_REQUEST['serie'];
		        $nserie 		= $_REQUEST['nserie'];
		        $monto_igual 		= $_REQUEST['monto_igual'];

		        $resultgnv = array();

		        if ($gnv == 'S'){
					$resultgnv = $modelRegistroVentas->obtieneRegistrosGNV($anio, $mes, $desde, $hasta);
				}

		        $results	= $modelRegistroVentas->obtieneRegistros($almacen, $anio, $mes, $desde, $hasta, $tipo, $orden, $seriesdocumentos, $monto_sistema, $serie, $nserie,$BI_incre, $IGV_incre, $TOTAL_incre, $monto_igual, $nd);
		        $arrResponseModel = $modelRegistroVentas->obtenerAlma($almacen);

				/*** Agregado 2020-02-04 ****/
				// echo "PLE";
				
				// echo "<pre>";
				// print_r($results);
				// echo "</pre>";

				// echo "<pre>";
				// print_r( array( 
				// 	"arrData" => $arrResponseModel["arrData"], 
				// 	"anio" => $anio, 
				// 	"mes" => $mes, 
				// 	"resultgnv" => $resultgnv, 
				// 	"correlativo" => $correlativo, 
				// 	"almacen" => $almacen, 
				// 	"tipo_ple" => $tipo_ple,
				// 	"BI_incre" => $BI_incre,
				// 	"TOTAL_incre" => $TOTAL_incre,
				// 	"IGV_incre" => $IGV_incre ) );
				// echo "</pre>";
				// die();
				/***/

		        if ($arrResponseModel["sStatus"] != "success" ) {
					echo '<script type="text/javascript">alert("' . $arrResponseModel['sMessage'] . '");window.close();</script>';
		        } else {
		        	$this->GenerarLibrosElectronicos($results, $arrResponseModel["arrData"], $anio, $mes, $resultgnv, $correlativo, $almacen, $tipo_ple,$BI_incre,$TOTAL_incre,$IGV_incre);
		        }

                break;

            case "SerieDocumento":
                	$this->visor->addComponent("SpaceSeries", "space", $templateRegistroVentas->listaSeries());
                return;

            	default:
                	$search_form = true;
                break;

	}

        if ($search_form) {
            $result = $templateRegistroVentas->search_form();
        }

        if ($result != '')
            $this->visor->addComponent("ContentB", "content_body", $result);

        if ($result_f != '')
            $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }

	//$this->GenerarLibrosElectronicos($results, $arrResponseModel["arrData"], $anio, $mes, $resultgnv, $correlativo, $almacen, $tipo_ple,$BI_incre,$TOTAL_incre,$IGV_incre);
    function GenerarLibrosElectronicos($resultado, $v, $anio, $mes, $resultgnv, $correlativo, $almacen, $version,$BI,$BT,$IGV) {

		ob_clean();
		
		// echo "<script>console.log('" . json_encode( array( count($resultado['ticket']), count($resultado['manual']), count($resultgnv['gnv']) ) ) . "')</script>";
		// echo "<script>console.log('" . json_encode( array($resultado, $resultgnv) ) . "')</script>";
		// die();

        $ruc = $v["ruc"];
        $ntickets = count($resultado['ticket']);

        $accion_ejecutar = "si";
        $contador_incremento = 0;
        $PLETXTLINIA = null;
        $estado_corretivo = FALSE; //solo cuando el valor sea cero(0)

        if ($correlativo > 0) {
            $estado_corretivo = TRUE;
        }

        for ($i = 0; $i < $ntickets - 6; $i++) { //TICKETS
            $linea = $resultado['ticket'][$i];

            if ($contador_incremento == 0 ) {//&& $linea['imponible']>$BI
                if( ($BI<0 && $linea['imponible']>abs($BI) ) || ($BT<0  && $linea['importe']>abs($BT)) || ($IGV<0 && $linea['igv']>abs($IGV))){
					    error_log("Entro 1");
						if ($version == "3") {
							$PLEDATA = $this->ImprimirLiniaPLE($linea, $anio, $mes, $almacen, "si", $correlativo,$BI,20,$IGV);
						} else {
							$PLEDATA = $this->ImprimirLiniaPLE_SIMPLIFICADO($linea, $anio, $mes, $almacen, $BI,$BT,$IGV, $correlativo);
						}
						$contador_incremento++;

                }else if( ($BI>0) || ($BT>0) || ($IGV>0) ){
						error_log("Entro 2");
                      	if ($version == "3") {
                            $PLEDATA = $this->ImprimirLiniaPLE($linea, $anio, $mes, $almacen, "si", $correlativo,$BI,$BT,$IGV);
                        } else {
                            $PLEDATA = $this->ImprimirLiniaPLE_SIMPLIFICADO($linea, $anio, $mes, $almacen, $BI,$BT,$IGV, $correlativo);
                        }
                		$contador_incremento++;

                }else{
						error_log("Entro 3");
                    	if ($version == "3") {
                            $PLEDATA = $this->ImprimirLiniaPLE($linea, $anio, $mes, $almacen, "si", $correlativo,0,0,0);
                        } else {
                            $PLEDATA = $this->ImprimirLiniaPLE_SIMPLIFICADO($linea, $anio, $mes, $almacen, 0,0,0, $correlativo);
                        }

                }

              

                $PLETXTLINI = $PLEDATA['registro'];
                
            
                
            } else {

                if ($version == "3") {
					error_log("Entro 4");
                    $PLEDATA = $this->ImprimirLiniaPLE($linea, $anio, $mes, $almacen, "no", $correlativo,0,0,0);
                } else {
					error_log("Entro 5");
                    $PLEDATA = $this->ImprimirLiniaPLE_SIMPLIFICADO($linea, $anio, $mes, $almacen,0,0,0, $correlativo);
                }

                $PLETXTLINI = $PLEDATA['registro'];
            }

            echo implode("|", $PLETXTLINI) . "|" . PHP_EOL;

            


        }

        	//APARTIR DE AQUI VIENE LO DE FACTURAS Y BOLETAS MANUALES

		$nmanuales = count($resultado['manual']);

		for ($i = 0; $i < $nmanuales - 6; $i++) {

			$linea = $resultado['manual'][$i];

			if ($version == "3"){
                		$PLEDATA = $this->ImprimirLiniaPLE($linea, $anio, $mes, $almacen, "no", $correlativo,0,0,0);
            		} else {
                		$PLEDATA = $this->ImprimirLiniaPLE_SIMPLIFICADO($linea, $anio, $mes, $almacen, 0,0,0, $correlativo);
			}

			$PLETXTLINI = $PLEDATA['registro'];
			echo implode("|", $PLETXTLINI) . "|" . PHP_EOL;

			if ($estado_corretivo == TRUE) {
				$correlativo++;
			}
		}

        	//APARTIR DE AQUI VIENE LO DE GNV
        	$ngnv = count($resultgnv['gnv']); //siempre -3 x que por los importe

		for ($i = 0; $i < $ngnv - 4; $i++) {

			$linea = $resultgnv['gnv'][$i];

			if ($version == "3")
				$PLEDATA = $this->ImprimirLiniaPLE($linea, $anio, $mes, $almacen, "no", $correlativo,0,0,0);
			else
				$PLEDATA = $this->ImprimirLiniaPLE_SIMPLIFICADO($linea, $anio, $mes, $almacen, 0,0,0, $correlativo);

			$PLETXTLINI = $PLEDATA['registro'];
			echo implode("|", $PLETXTLINI) . "|" . PHP_EOL;

			if ($estado_corretivo == TRUE)
				$correlativo++;

		}

        	$estado_info = 0;

        	if ($ntickets > 1 || $nmanuales > 1)
            		$estado_info = 1;

	        //JALAR INFORMACION DE las tres estaciones
	        if ($version == "3")
                	$nombre_archivo = "LE" . $ruc . "" . $anio . "" . $mes . "00140100001" . $estado_info . "11.txt";  
		else
                	$nombre_archivo = "LE" . $ruc . "" . $anio . "" . $mes . "00140200001" . $estado_info . "11.txt";

		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");



        	$result = trim($result);

        	die($result);

	}

	function ImprimirLiniaPLE_SIMPLIFICADO($linea, $anio, $mes, $almacen, $difBi, $valor_venta, $difIgv, $accion_ejecutar, $correlativo = "") { //PROBLEMA CON CANTIDAD DE PARAMETROS PHP7

		$reg_12			= "0.00";
		$reg_14 		= "0.00";
		$reg_15 		= "0.00";
		$reg_20_otros_tributos 	= "0.00";
		$reg_19_iva5 		= "0.00";
		$reg_18_BI_iva 		= "0.00";
		$reg_16_ISC 		= "0.00";

		$rango_tickes		= $this->tickes_rango($linea['tipo'], $linea['numero']);
		//$datos_referencia_nc_nd	= $this->datos_nota_credito_debito($anio, $mes, $linea['tipo'], $linea['serie'], $linea['trans']);
		$datos_referencia_nc_nd['fecha_emision_original'] = '';
		$datos_referencia_nc_nd['tipo_docu_original'] = '';
		$datos_referencia_nc_nd['num_serie_original'] = '';
		$datos_referencia_nc_nd['num_docu_original'] = '';
		if ( $linea['rendi_gln'] != "" ) {
			$arrData = array(
	            //Datos para buscar documento origen
	            "sCaja" => $linea['caja'],
	            "sTipoDocumento" => $linea['td'],
	            "fIDTrans" => $linea['rendi_gln'],
	            "iNumeroDocumentoIdentidad" => $linea['ruc_bd_interno'],
	        );

	        $arrResponseModel = $this->datos_nota_credito_debito($anio, $mes, $linea['tipo'], $linea['serie'], $linea['trans'], $arrData);
	        if ( isset($arrResponseModel["sStatus"]) && $arrResponseModel["sStatus"] == "success") {
				$datos_referencia_nc_nd['fecha_emision_original'] = $arrResponseModel["arrDataRef"]["fecha_emision_original"];
				$datos_referencia_nc_nd['tipo_docu_original'] = $arrResponseModel["arrDataRef"]["tipo_docu_original"];
				$datos_referencia_nc_nd['num_serie_original'] = $arrResponseModel["arrDataRef"]["num_serie_original"];
				$datos_referencia_nc_nd['num_docu_original'] = $arrResponseModel["arrDataRef"]["num_docu_original"];
	        }
	    }
	    
		$estado_documento	= ($linea['estado'] == "AN" || $linea['estado'] == "an") ? '2' : '1';
		$informacion_cliente	= $this->validar_tipo_documento_identidad($linea['tipo'], $linea['ruc'], $linea['cliente'], $estado_documento, $datos_referencia_nc_nd['tipo_docu_original']);

		//SOLO ES OBLIGATORIO CUANDO ES FACTURA ,DESPUES PARA LOS DEMAS DOCUMENTOS NO TE EXIJE ,TAMPOCO CUANDO ES ANULADO(CAMPO 27=2)
		$numero_unico_registro = "";

		if ($correlativo == 0) {
			if($linea['tipo'] != '12')
				$numero_unico_registro = trim($linea['tipo']) . "-" . trim($linea['serie']) . "-" . trim($linea['trans']);
			else
				$numero_unico_registro = $almacen . "-" . trim($linea['caja']) . "-" . trim($linea['trans']);
		} else {
			if($linea['tipo'] != '12')
				$numero_unico_registro = trim($linea['tipo']) . "-" . trim($linea['serie']) . "-" . trim($linea['trans']);
			else
				$numero_unico_registro = $almacen . "-" . trim($linea['caja']) . "-" . trim($linea['trans']);
		}

		$bandera_monto 		= $this->establecer_monto_otro_tipo($linea['igv']);
		$datos_importe 		= $this->formato_moneda($linea['tipo'], $linea['imponible'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);
		$datos_igv 		= $this->formato_moneda($linea['tipo'], $linea['igv'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);
		$datos_exonerada 	= $this->formato_moneda($linea['tipo'], $linea['exonerada'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);
		$datos_inafecto 	= $this->formato_moneda($linea['tipo'], $linea['inafecto'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);
		$datos_total 		= $this->formato_moneda($linea['tipo'], $linea['importe'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);

		$PLETXT[0] 	= $anio . "" . $mes . "00";
		$PLETXT[1] 	= $numero_unico_registro; 
		$PLETXT[2] 	= 'M1'; 
		$PLETXT[3] 	= $this->formato_fecha($linea['emision']);
		$PLETXT[4] 	= $this->formato_fecha($linea['vencimiento']);
		$PLETXT[5] 	= $linea['tipo']; //es el tipo de documento 12=tickes,03=boleta,01=Factura,07=nota credito,08=nota debito
		$PLETXT[6] 	= $this->numero_serie_comprobante($linea['tipo'], trim($linea['serie']));
		$PLETXT[7] 	= $rango_tickes['inicio_tickes'];
		$PLETXT[8] 	= $rango_tickes['fin_tickes'];
		$PLETXT[9] 	= $informacion_cliente['tipo_documento_identidad'];
		$PLETXT[10] 	= $informacion_cliente['numero_documento_identidad'];
		$PLETXT[11] 	= $informacion_cliente['rz_documento_identidad'];
		$PLETXT[12] 	= ($bandera_monto['flag'] == "N") ? $datos_importe['valor'] + $difBi : "0.00";//Base imponible de la operacion gravada
		$PLETXT[13] 	= ($bandera_monto['flag'] == "N") ? $datos_igv['valor'] + $difIgv : "0.00";//Impuesto General a las Ventas y/o Impuesto de Promoción Municipal  =16
		
		$PLETXT[14]     = number_format( round($linea['balance'],2), 2, '.', '' );

		$PLETXT[15] 	= ($bandera_monto['flag'] == "V_NG") ? $datos_inafecto['valor'] + $datos_exonerada['valor'] : "0.00";//Otros conceptos, tributos y cargos que no forman parte de la base imponible
		$PLETXT[16] 	= $datos_total['valor'] + $valor_venta;
		$PLETXT[17] 	= "PEN";//Codigo de moneda
		$PLETXT[18] 	= $this->validarTipoCambio($linea['tipocambio']);
		$PLETXT[19] 	= $datos_referencia_nc_nd['fecha_emision_original'];
		$PLETXT[20] 	= $datos_referencia_nc_nd['tipo_docu_original'];
		$PLETXT[21] 	= $datos_referencia_nc_nd['num_serie_original'];
		$PLETXT[22] 	= $datos_referencia_nc_nd['num_docu_original'];
		$PLETXT[23] 	= "1";//TIPO DE INCOSISTENCIA 
		$PLETXT[24] 	= "1";//ndicador de Comprobantes de pago cancelados con medios de pago 
		$PLETXT[25] 	= $informacion_cliente['estado_documento'];

		return array("registro" => $PLETXT, "incremento_monto" => $datos_importe['ejecuto']); //si hay un  diferencia incremente en las boletas.

	}

	function ImprimirLiniaPLE($linea, $anio, $mes, $almacen, $accion_ejecutar, $correlativo,$BI,$BT,$IGV) {
		$reg_12			= "0.00";
		$reg_14 		= "0.00";
		$reg_15 		= "0.00";
		$reg_20_otros_tributos 	= "0.00";
		$reg_19_iva5 		= "0.00";
		$reg_18_BI_iva 		= "0.00";
		$reg_16_ISC 		= "0.00";

		$rango_tickes 		= $this->tickes_rango($linea['tipo'], $linea['numero']);
		//$datos_referencia_nc_nd = $this->datos_nota_credito_debito($anio, $mes, $linea['tipo'], $linea['serie'], $linea['trans'], $arrData);
		$datos_referencia_nc_nd['fecha_emision_original'] = '';
		$datos_referencia_nc_nd['tipo_docu_original'] = '';
		$datos_referencia_nc_nd['num_serie_original'] = '';
		$datos_referencia_nc_nd['num_docu_original'] = '';
		if ( $linea['rendi_gln'] != "" ) {
			$arrData = array(
	            //Datos para buscar documento origen
	            "sCaja" => $linea['caja'],
	            "sTipoDocumento" => $linea['td'],
	            "fIDTrans" => $linea['rendi_gln'],
	            "iNumeroDocumentoIdentidad" => $linea['ruc_bd_interno'],
	        );

	        $arrResponseModel = $this->datos_nota_credito_debito($anio, $mes, $linea['tipo'], $linea['serie'], $linea['trans'], $arrData);
	        if ( isset($arrResponseModel["sStatus"]) && $arrResponseModel["sStatus"] == "success") {
				$datos_referencia_nc_nd['fecha_emision_original'] = $arrResponseModel["arrDataRef"]["fecha_emision_original"];
				$datos_referencia_nc_nd['tipo_docu_original'] = $arrResponseModel["arrDataRef"]["tipo_docu_original"];
				$datos_referencia_nc_nd['num_serie_original'] = $arrResponseModel["arrDataRef"]["num_serie_original"];
				$datos_referencia_nc_nd['num_docu_original'] = $arrResponseModel["arrDataRef"]["num_docu_original"];
	        }
	    }

	    if( $linea['reffec'] != "" && $linea['reftip'] != "" && $linea['refser'] != "" && $linea['refnum'] != "" ){
			$dOrigen = explode("-", $linea['reffec']);
			$dOrigen = $dOrigen[2] . "/" . $dOrigen[1] . "/" . $dOrigen[0];
			$datos_referencia_nc_nd['fecha_emision_original'] = $dOrigen;

			$datos_referencia_nc_nd['tipo_docu_original'] = $linea['reftip'];
			$datos_referencia_nc_nd['num_serie_original'] = $linea['refser'];
			$datos_referencia_nc_nd['num_docu_original'] = $linea['refnum'];
	    }

		$estado_documento 	= ($linea['estado'] == "AN" || $linea['estado'] == "an") ? '2' : '1';
		$informacion_cliente 	= $this->validar_tipo_documento_identidad($linea['tipo'], $linea['ruc'], $linea['cliente'], $estado_documento, $datos_referencia_nc_nd['tipo_docu_original'],$linea['importe']);

		//SOLO ES OBLIGATORIO CUANDO ES FACTURA ,DESPUES PARA LOS DEMAS DOCUMENTOS NO TE EXIJE ,TAMPOCO CUANDO ES ANULADO(CAMPO 27=2)
		$numero_unico_registro = "";

		if ($correlativo == 0) {
			if($linea['tipo'] != '12')
				$numero_unico_registro = trim($linea['tipo']) . "-" . trim($linea['serie']) . "-" . trim($linea['trans']);
			else
				$numero_unico_registro = $almacen . "-" .trim($linea['caja']) . "-" . trim($linea['trans']);
		} else {
			if($linea['tipo'] != '12')
				$numero_unico_registro = trim($linea['tipo']) . "-" . trim($linea['serie']) . "-" . trim($linea['trans']);
			else
				$numero_unico_registro = $almacen . "-" .trim($linea['caja']) . "-" . trim($linea['trans']);
		}

		$bandera_monto 		= $this->establecer_monto_otro_tipo($linea['igv']);
		$datos_importe 		= $this->formato_moneda($linea['tipo'], $linea['imponible'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);
		$datos_igv 		= $this->formato_moneda($linea['tipo'], $linea['igv'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);
		$datos_exonerada 	= $this->formato_moneda($linea['tipo'], $linea['exonerada'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);
		$datos_inafecto 	= $this->formato_moneda($linea['tipo'], $linea['inafecto'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);
		$datos_total 		= $this->formato_moneda($linea['tipo'], $linea['importe'], $accion_ejecutar, $informacion_cliente['tipo_documento_identidad']);

		$PLETXT[0] 	= $anio . "" . $mes . "00";
		$PLETXT[1] 	= $numero_unico_registro; //tener presente Cuando es totalizado entonces Trans esta conformado(Z.ch_posz_pos-Z.dt_posz_fecha_sistema,'MMDD'-Z.id_posz)
		$PLETXT[2] 	= 'M1'; //el correlativo de la transaccion
		$PLETXT[3] 	= $this->formato_fecha($linea['emision']);
		$PLETXT[4] 	= $this->formato_fecha($linea['vencimiento']);
		$PLETXT[5] 	= $linea['tipo']; //es el tipo de documento 12=tickes,03=boleta,01=Factura,07=nota credito,08=nota debito
		$PLETXT[6] 	= $this->numero_serie_comprobante($linea['tipo'], trim($linea['serie']));
		$PLETXT[7] 	= $rango_tickes['inicio_tickes'];
		$PLETXT[8] 	= $rango_tickes['fin_tickes'];
		$PLETXT[9] 	= $informacion_cliente['tipo_documento_identidad'];
		$PLETXT[10] 	= $informacion_cliente['numero_documento_identidad'];
		$PLETXT[11] 	= $informacion_cliente['rz_documento_identidad'];
		$PLETXT[12] 	= $reg_12;//valor facturado de la exportacion
		$PLETXT[13] 	= ($bandera_monto['flag'] == "N") ? $datos_importe['valor'] + $BI : "0.00";//Base imponible de la operacion gravada
		$PLETXT[14] 	= "0.00";//Descuento del base imponible =15
		$PLETXT[15] 	= ($bandera_monto['flag'] == "N") ? $datos_igv['valor'] + $IGV : "0.00";//Impuesto General a las Ventas y/o Impuesto de Promoción Municipal  =16
		$PLETXT[16] 	= "0.00";
		$PLETXT[17] 	= "0.00";
		$PLETXT[18] 	= "0.00";
		$PLETXT[19] 	= "0.00";
		$PLETXT[20] 	= "0.00";
		$PLETXT[21] 	= "0.00";

		$PLETXT[22]     = number_format( round($linea['balance'],2), 2, '.', '' );

		$PLETXT[23] 	= ($bandera_monto['flag'] == "V_NG") ? $datos_inafecto['valor'] + $datos_exonerada['valor'] : "0.00";//Otros conceptos, tributos y cargos que no forman parte de la base imponible
		$PLETXT[24] 	= $datos_total['valor']+$BT;
		$PLETXT[25] 	= "PEN";//Codigo de moneda
		$PLETXT[26] 	= $this->validarTipoCambio($linea['tipocambio']);
		$PLETXT[27] 	= $datos_referencia_nc_nd['fecha_emision_original'];
		$PLETXT[28] 	= $datos_referencia_nc_nd['tipo_docu_original'];
		$PLETXT[29] 	= $datos_referencia_nc_nd['num_serie_original'];
		$PLETXT[30] 	= $datos_referencia_nc_nd['num_docu_original'];
		$PLETXT[31] 	= "" ;
		$PLETXT[32] 	= "1" ;
		$PLETXT[33] 	= "1";
		$PLETXT[34] 	= $informacion_cliente['estado_documento'];

		return array("registro" => $PLETXT, "incremento_monto" => $datos_importe['ejecuto']); //si hay un  diferencia incremente en las boletas.

	}

	function establecer_monto_otro_tipo($igv_cero) {

		if ($igv_cero == 0 || $igv_cero == 0.0)
		    return array("flag" => "V_NG");

		return array("flag" => "N");

	}

    function numero_serie_comprobante($tipo_comprobante, $serie) {
        if (strcmp($tipo_comprobante, "01") == 0 ||
                strcmp($tipo_comprobante, "03") == 0 ||
                strcmp($tipo_comprobante, "04") == 0 ||
                strcmp($tipo_comprobante, "07") == 0 ||
                strcmp($tipo_comprobante, "08") == 0) {
            return str_pad($serie, 4, "0", STR_PAD_LEFT);
        } else if (strcmp($tipo_comprobante, "00") == 0) {
            return "-";
        } else {//quiere decir que es un tickes  12
            return $serie;
        }
    }

    function tickes_rango($tipo_documento, $num_comprobante) {

        $tipo_documento = trim($tipo_documento);

        if ($tipo_documento == 12) {
            $exixte = strpos($num_comprobante, "-");
            if ($exixte == FALSE) {
                return array("inicio_tickes" => $num_comprobante, "fin_tickes" => ""); //CUANDO SE TRATA DE VENTAS SIN TOTALIZADO X TURNO
            } else {
                $rango_tickes = explode("-", $num_comprobante);
                return array("inicio_tickes" => $rango_tickes[0], "fin_tickes" => $rango_tickes[1]); //CONSOLIDADO POR TURNO
            }
        } else if ($tipo_documento == "01" || $tipo_documento == "03" || $tipo_documento == "07" || $tipo_documento == "08") {//DOCUMENTOS MANUALES
            $estado_nume = trim($num_comprobante);
            $estado_nume = strlen($estado_nume);
            if ($estado_nume == 0) {
                return array("inicio_tickes" => "000", "fin_tickes" => "");
            } else {
            	$rango_tickes = explode("-", $num_comprobante);
            	if ( $tipo_documento == "03" ) {
                return array("inicio_tickes" => $rango_tickes[0], "fin_tickes" => $rango_tickes[1]);
            	} else {
            	return array("inicio_tickes" => $rango_tickes[0], "fin_tickes" => "");
            	}
            }
        }
    }

    function datos_nota_credito_debito($anio, $mes, $tipo_docu_venta, $serie_documento_original, $num_documento_original, $arrData) {
        $modelRegistroVentas = new RegistroVentasModel();

        $serie_doc = trim($serie_documento_original);
        $num_doc = trim($num_documento_original);

        if ($tipo_docu_venta == "07") {//Nota de crédito documento de abono
            $data = $modelRegistroVentas->ObtenerDocumentoReferencia($anio, $mes, $num_doc, $serie_doc, "20", $arrData);
            return $data;
        } else if ($tipo_docu_venta == "08") {

            $data = $modelRegistroVentas->ObtenerDocumentoReferencia($anio, $mes, $num_doc, $serie_doc, "11", $arrData);
            return $data;
        } else {
            return array(
                "fecha_emision_original" => "01/01/0001",
                "tipo_docu_original" => "00",
                "num_serie_original" => "-",
                "num_docu_original" => "-"
            );
        }
    }

    function validar_tipo_documento_identidad($tipo_docu_venta, $numero_documento_identidad, $rz_documento_identidad, $estado_documento, $tipo_docu_venta_referencia, $importe = "") { //PROBLEMA CON CANTIDAD DE PARAMETROS PHP7
        //SOLO ES OBLIGATORIO CUANDO ES FACTURA ,DESPUES PARA LOS DEMAS DOCUMENTOS NO TE EXIJE ,TAMPOCO CUANDO ES ANULADO(CAMPO 27=2)

        $numero_documento_identidad = trim($numero_documento_identidad);
        $rz_documento_identidad = trim($rz_documento_identidad);


        if ($estado_documento == "2") {//si el documento esta anulada
            return array("tipo_documento_identidad" => "0", "numero_documento_identidad" => "999999999", "rz_documento_identidad" => "-", "estado_documento" => $estado_documento);
        } else {
            if ($tipo_docu_venta == "01") {//si es una factura manual
                return array("tipo_documento_identidad" => "6", "numero_documento_identidad" => $numero_documento_identidad, "rz_documento_identidad" => $rz_documento_identidad, "estado_documento" => $estado_documento);
            } else if ($tipo_docu_venta == "03" || $tipo_docu_venta == "07" || $tipo_docu_venta == "08") {

                if ($tipo_docu_venta_referencia == "01" && ($tipo_docu_venta == "07" || $tipo_docu_venta == "08")) {
                    return array("tipo_documento_identidad" => "6", "numero_documento_identidad" => $numero_documento_identidad, "rz_documento_identidad" => $rz_documento_identidad, "estado_documento" => $estado_documento);
                } else {
                    if (strlen($numero_documento_identidad) == 0) {
                    	//Validacion si supera los 700 soles indentificar cliente
                    	if ($importe > 700){
                    	return array("tipo_documento_identidad" => "0", "numero_documento_identidad" => "00000000", "rz_documento_identidad" => "CLIENTES VARIOS", "estado_documento" => $estado_documento);
                    	} else {
                        return array("tipo_documento_identidad" => "0", "numero_documento_identidad" => "999999999", "rz_documento_identidad" => "-", "estado_documento" => $estado_documento);
                    	} 
                    } else {
                    	if(strlen($numero_documento_identidad) == 8 && trim($numero_documento_identidad) != '99999999'){
                        return array("tipo_documento_identidad" => "1", "numero_documento_identidad" => $numero_documento_identidad, "rz_documento_identidad" => $rz_documento_identidad, "estado_documento" => $estado_documento);
						}else{
							 return array("tipo_documento_identidad" => "0", "numero_documento_identidad" => $numero_documento_identidad, "rz_documento_identidad" => $rz_documento_identidad, "estado_documento" => $estado_documento);
						}
				    }
                }
            } else if ($tipo_docu_venta == "12") {
                if (strlen(trim($numero_documento_identidad)) == 11) {
                    return array("tipo_documento_identidad" => "6", "numero_documento_identidad" => $numero_documento_identidad, "rz_documento_identidad" => $rz_documento_identidad, "estado_documento" => $estado_documento);
                } else if (strlen(trim($numero_documento_identidad)) == 8 && trim($numero_documento_identidad) != '99999999') {
                    return array("tipo_documento_identidad" => "1", "numero_documento_identidad" => $numero_documento_identidad, "rz_documento_identidad" => $rz_documento_identidad, "estado_documento" => $estado_documento);
                } else {
                    return array("tipo_documento_identidad" => "0", "numero_documento_identidad" => "99999999", "rz_documento_identidad" => "-", "estado_documento" => $estado_documento);
                }
            }
        }
    }

    function validarTipoCambio($tipo_cambio) {

        $tipo_cambio = trim($tipo_cambio);

        if (strlen($tipo_cambio) == 0) {
            return "0.000";
        } else {
            $tipo_cambio = round($tipo_cambio, 3);
            $index = strpos($tipo_cambio, ".");

            if ($index == false) {
                return $tipo_cambio . ".000";
            }

            $cade_entero = substr($tipo_cambio, 0, $index);
            $cade_decimal = substr($tipo_cambio, $index + 1);
            $cade_decimal = str_pad($cade_decimal, 3, "0", STR_PAD_RIGHT);

            return $cade_entero . "." . $cade_decimal;
        }

        return $tipo_cambio;
    }

    function formato_moneda($tipo_documento, $monto, $accion_ejecutar, $diferencia, $tipo_documento_identidad = "") { //PROBLEMA CON CANTIDAD DE PARAMETROS PHP7
        $tipo_documento = trim($tipo_documento);

        if ($tipo_documento == "07" && $monto > 0) {
            return array("valor" => round("-" . $monto, 2), "ejecuto" => "no"); //DEBE SER NEGATIVO CUANDO ES NOTA DE CREDITO.
        } else {
            if ($accion_ejecutar == "si" && $tipo_documento == "12" && $tipo_documento_identidad == "0") {
               // $monto = $monto + ($diferencia);
                return array("valor" => number_format($monto, 2, '.', ''), "ejecuto" => "si");

                ;
            }

            return array("valor" => round($monto, 2), "ejecuto" => "no");
        }
    }

    function formato_fecha($fecha) {
        $date = explode("-", $fecha);
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }

}

