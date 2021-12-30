<?php
require 'facturacion/t_facturas_venta.php';
require 'facturacion/m_facturas_venta.php';
require '/sistemaweb/assets/jgridpaginador.php';

class controllerSalesInvoice {
	function __construct() {
	}

	public function index($arrDataHelper) {
		$jqGridModel = new jqGridModel();//Clase de paginador

		$templateSalesInvoice = new templateSalesInvoice();
		$modelSalesInvoice = new modelSalesInvoice();
		$arrData = $modelSalesInvoice->get_all_sales_invoice($arrDataHelper, $jqGridModel);
		$templateSalesInvoice->index($arrDataHelper, $arrData);
	}

	public function searchSalesInvoice($arrPost) {
		$jqGridModel = new jqGridModel();//Clase de paginador

		$modelSalesInvoice = new modelSalesInvoice();
		$templateSalesInvoice = new templateSalesInvoice();
		$arrData = $modelSalesInvoice->get_all_sales_invoice($arrPost, $jqGridModel);
		$templateSalesInvoice->table_sales_invoice($arrData);
	}

	public function page_add_sales_invoice($arrDataHelper, $sTitle, $arrGet) {
		$templateSalesInvoice = new templateSalesInvoice();
		$arrDataEdit = array();
		if ($sTitle == 'Editar') {
			$modelSalesInvoice = new modelSalesInvoice();
			$arrDataEdit = $modelSalesInvoice->edit_sales_invoice($arrGet);
		}		
		// echo "<script>console.log('" . json_encode($arrDataHelper) . "')</script>";
		// echo "<script>console.log('" . json_encode($sTitle) . "')</script>";
		// echo "<script>console.log('" . json_encode($arrDataEdit) . "')</script>";
		$templateSalesInvoice->page_add_sales_invoice($arrDataHelper, $sTitle, $arrDataEdit);
	}

	public function verify_reference_sales_invoice_document($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->verify_reference_sales_invoice_document($arrPost));
	}

	// FV Agregar - Obtener Datos
	public function search_sales_serial($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->get_sales_serial($arrPost));
	}

	public function search_number_by_sale_serial($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->get_number_by_sale_serial($arrPost));
	}

	public function search_customer_price_list($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->get_customer_price_list($arrPost));
	}

	public function search_customer_credit_days($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->get_customer_credit_days($arrPost));
	}

	public function search_other_customer_fields($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->get_other_customer_fields($arrPost));
	}

	public function search_item_sale_price($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->get_item_sale_price($arrPost));
	}

	public function search_other_item_fields($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->get_other_item_fields($arrPost));
	}

	public function verify_register($arrPost) {
		$modelSalesInvoice = new modelSalesInvoice();
		echo json_encode($modelSalesInvoice->verify_register($arrPost));
	}
	// /. FV Agregar - Obtener Datos

	//Save
	public function save_sales_invoice($arrPost, $sendUserIp, $dHoraActual) {
		$modelSalesInvoice = new modelSalesInvoice();
	    echo json_encode($modelSalesInvoice->add_sales_invoice($arrPost, $sendUserIp, $dHoraActual));
	}

	//Save complementary
	public function save_sales_invoice_complementary($arrPost, $sendUserIp) {
		$modelSalesInvoice = new modelSalesInvoice();
	    echo json_encode($modelSalesInvoice->save_sales_invoice_complementary($arrPost, $sendUserIp));
	}

	//Modify
	public function modify_sales_invoice($arrPost, $sendUserIp, $dHoraActual) {
		$modelSalesInvoice = new modelSalesInvoice();
	    echo json_encode($modelSalesInvoice->modify_sales_invoice($arrPost, $sendUserIp, $dHoraActual));
	}

	//Cancel / Delete
	public function cancel_or_delete_sales_invoice($arrPost, $arrDataHelper) {
		/*
		0 = Registrado
		1 = Completado
		2 = Anulado
		3 = Completado Enviado
		4 = Completado Error (No se envió el documento a EBI -> SUNAT)
		5 = Anulado enviado
		6 = Anulado Error
		*/
		if (
			strlen(trim($arrPost["sSerieDocumento"])) == 3
			&& ($arrPost["iEstadoDocumento"] == 0 || $arrPost["iEstadoDocumento"] == 1)
		) {
			$modelSalesInvoice = new modelSalesInvoice();
		    echo json_encode($modelSalesInvoice->cancel_or_delete_sales_invoice($arrPost));
		} else if (
			(
				strlen(trim($arrPost["sSerieDocumento"])) == 4
				&& (($arrPost["iEstadoDocumento"] == 3 || $arrPost["iEstadoDocumento"] == 6)
				&& $arrPost['sAction'] == 'anular')
			)
			||
			(
				strlen(trim($arrPost["sSerieDocumento"])) == 4
				&& (($arrPost["iEstadoDocumento"] == 0 || $arrPost["iEstadoDocumento"] == 1 || $arrPost["iEstadoDocumento"] == 4)
				&& $arrPost['sAction'] == 'eliminar')
			)
		) {
			if( $arrPost['sAction'] == 'anular' ) {
				$sAnulado = ($arrPost['sAction'] == 'anular' ? 'S' : 'N');
				$arrValidacionFecha = array(
					"sDataType" => "PHP",
					"sNombreValidacion" => "fecha",
					"sSerieDocumento" => $arrPost["sSerieDocumento"],
					"dFechaEmision" => $arrPost["dFechaEmision"],
					"no_anulado" => $sAnulado,
				);
				$arrFecha = $this->verify_validations_FE($arrValidacionFecha, $arrDataHelper);
				if ( $arrFecha['sStatus'] != "success" ) {
					echo json_encode($arrFecha);
				} else {
					$modelSalesInvoice = new modelSalesInvoice();
				    $arrResponseModel = $modelSalesInvoice->cancel_or_delete_sales_invoice($arrPost);
				    if ( $arrResponseModel['sStatus'] != 'success' ){
				    	echo $arrResponseModel;
				    } else {
				    	$this->generate_printed_representation_pdf_FE_sunat($arrPost, $arrDataHelper);
				    }
				}
			} else {
				$modelSalesInvoice = new modelSalesInvoice();
				$arrResponseModel = $modelSalesInvoice->cancel_or_delete_sales_invoice($arrPost);
				echo json_encode($arrResponseModel);
			}
		}
	}

	/***************************************************************
	* Datos para Facturación Electrónica y Representación Impresa  *
	****************************************************************/
	private function check_get_plates_generated_settlement_vouchers($arrGet) {
		$modelSalesInvoice = new modelSalesInvoice();
		return $modelSalesInvoice->check_get_plates_generated_settlement_vouchers($arrGet);
	}

	public function generate_printed_representation_pdf_FE_sunat($arrGet, $arrDataHelper){ //METODO PARA REPRESENTACION PDF / ENVIO A SUNAT
		$bStatusFESunat = true;

		error_log('Obtener compania');
		$arrCompany = $this->get_company($arrGet);
		if ( ($bStatusFESunat) && $arrCompany['sStatus'] == "danger" ) {
			if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
				echo '<script type="text/javascript">alert("' . $arrCompany['sMessage'] . '");window.close();</script>';
			} else {
				echo json_encode($arrCompany);
			}
			$bStatusFESunat = false;
		}

		error_log('Obtener cabecera');
		$arrHeader = $this->get_header($arrGet);
		if ( ($bStatusFESunat) && $arrHeader['sStatus'] == "danger" ) {
			if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
				echo '<script type="text/javascript">alert("' . $arrHeader['sMessage'] . '");window.close();</script>';
			} else {
				echo json_encode($arrHeader);
			}
			$bStatusFESunat = false;
		}

		error_log('Validacion de fecha');
		$arrPost = array(
			"sDataType" => "PHP",
			"sNombreValidacion" => "fecha",
			"sSerieDocumento" => $arrHeader["arrRow"]["no_serie_documento"],
			"dFechaEmision" => $arrHeader["arrRow"]["fe_emision"],
			"no_anulado" => $arrHeader["arrRow"]["no_anulado"],
		);
		$arrFecha = $this->verify_validations_FE($arrPost, $arrDataHelper);
		if ( $arrFecha['sStatus'] != "success" ) {
			if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
				if ( $arrHeader["arrRow"]["nu_estado_documento_sunat"] == "0" ) { //0=Registrado
					echo '<script type="text/javascript">alert("' . $arrFecha['sMessage'] . '");window.close();</script>';
					$bStatusFESunat = false;
				}
			} else {
				$bStatusFESunat = false;
				echo json_encode($arrFecha);
			}
		}

		error_log('Obtener detalle');
		$arrDetail = array();
		if ( $arrHeader['arrRow']['no_anulado'] != "S" ) {//Solo si no está anulado el documento, buscamos el detalle
			$arrDetail = $this->get_detail($arrGet);
			if ( ($bStatusFESunat) && $arrDetail['sStatus'] == "danger" ) {
				if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
					echo '<script type="text/javascript">alert("' . $arrDetail['sMessage'] . '");window.close();</script>';
				} else {
					echo json_encode($arrDetail);
				}
				$bStatusFESunat = false;
			}
		}

		error_log('Validacion de documento de identidad del cliente');
		$arrPost = array(
			"sDataType" => "PHP",
			"sNombreValidacion" => "numero_documento_identidad_nombres",
			"iTipoDocumento" => $arrHeader['arrRow']["_nu_tipo_documento"],
			"iTipoDocumentoReferencia" => $arrHeader['arrRow']["nu_tipo_documento_referencia"],
			"iNumeroDocumentoIdentidadCliente" => $arrHeader['arrRow']["nu_documento_identidad_cliente"],
		);
		$arrValidaciones = $this->verify_several_types_validations($arrPost, $arrDataHelper);
		if ( ($bStatusFESunat) && $arrValidaciones['sStatus'] != "success" ) {
			if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
				echo '<script type="text/javascript">alert("' . $arrValidaciones['sMessage'] . '");window.close();</script>';
			} else {
				echo json_encode($arrValidaciones);
			}
			$bStatusFESunat = false;
		}

		error_log('Validacion del tipo de impuesto');
		$arrPost = array(
			"sDataType" => "PHP",
			"sNombreValidacion" => "tipos_impuesto",
			"no_codigo_impuesto" => $arrHeader["arrRow"]["no_codigo_impuesto"],
			"no_anulado" => $arrHeader["arrRow"]["no_anulado"],
		);
		$arrTaxCode = $this->verify_validations_FE($arrPost, $arrDataHelper);
		if ( ($bStatusFESunat) && $arrTaxCode['sStatus'] != "success" ) {
			if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
				echo '<script type="text/javascript">alert("' . $arrTaxCode['sMessage'] . '");window.close();</script>';
			} else {
				echo json_encode($arrTaxCode);
			}
			$bStatusFESunat = false;
		}

		error_log('Obtener montos totales');
		$arrMontos = $this->get_totals($arrHeader, $arrDataHelper);//$arrPost = Arreglo de impuestos tributarios
		if ( ($bStatusFESunat) && $arrMontos['sStatus'] != "success" ) {
			if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
				echo '<script type="text/javascript">alert("' . $arrMontos['sMessage'] . '");window.close();</script>';
			} else {
				echo json_encode($arrMontos);
			}
			$bStatusFESunat = false;
		}

		error_log('Obtener montos totales en letras');
		$arrMontoLetras = $this->get_total_legend($arrHeader);//$arrPost = Arreglo de impuestos tributarios
		if ( ($bStatusFESunat) && $arrMontoLetras['sStatus'] != "success" ) {
			if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
				echo '<script type="text/javascript">alert("' . $arrMontoLetras['sMessage'] . '");window.close();</script>';
			} else {
				echo json_encode($arrMontoLetras);
			}
			$bStatusFESunat = false;
		}

		error_log('Entrando a verificacion de placas válidas');
		// Solo si existe número de liquidación
		// Verificamos y obtenemos placas por Liquidación de vales generando un documento de venta oficina (boleta / factura)
		$arrPlates = array();
		if ( $arrHeader['arrRow']['no_anulado'] != "S" && !empty($arrGet['iNumeroLiquidacion']) ) {
			$arrPlates = $this->check_get_plates_generated_settlement_vouchers($arrGet);
			if ( ($bStatusFESunat) && $arrPlates['sStatus'] != "success" ) {
				if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
					echo '<script charset="utf8" type="text/javascript">alert("' . $arrPlates['sMessage'] . '");window.close();</script>';
				} else {
					echo json_encode($arrPlates);
				}
				$bStatusFESunat = false;
			}
		}

		error_log('Entrando a generar documento PDF o JSON');
		if ( $bStatusFESunat ) { // Solo si es TRUE generamos PDF ó FE Sunat
			error_log('Paso por todas las validaciones y comenzamos a generar documento PDF o JSON');
			// Generando array para PDF y Facturación Electrónica
			$_arrHeader = array( "sTipoDocumentoIdentidad" => $arrValidaciones["sTipoDocumentoIdentidad"], "iTipoDocumentoIdentidad" => $arrValidaciones["iTipoDocumentoIdentidad"] );
			$arrData = array(
				'arrCompany' => $arrCompany["arrRow"],
				'arrHeader' => array_merge($arrHeader["arrRow"], $_arrHeader),
				'arrDetail' => ($arrHeader['arrRow']['no_anulado'] != "S" ? $arrDetail["arrRow"] : ""),
				'arrTaxCode' => $arrTaxCode["arrRow"],
				'arrMontos' => $arrMontos["arrRow"],
				'arrMontoLetras' => $arrMontoLetras["arrRow"],
				'arrPlates' => ((isset($arrPlates['sStatus']) && $arrPlates['sStatus'] == "success") ? $arrPlates["arrRow"] : ""),
			);
			
			/*** Verificar envio de decimales para precio unitario y cantidad en el detalle de las facturas ***/			
			error_log('*** Etapa 2 ***');
			error_log( json_encode($arrData) );
			// die();
			/***/

			//PDF - Representación Impresa
			if (strip_tags(stripslashes($arrGet['sAction'])) == "representacion_interna_pdf_sunat") {
				$templateSalesInvoice = new templateSalesInvoice();
				$templateSalesInvoice->generate_printed_representation_pdf_sunat($arrData);
			} else {//FE - SUNAT
				$modelSalesInvoice = new modelSalesInvoice();

				// Parámetros adicionales
				// fac_ta_factura_cabecera PRIMARY KEY (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
				$primary_key_factura_cabecera = $arrData['arrHeader']['_nu_tipo_documento'] . trim($arrData['arrHeader']['no_serie_documento']) . $arrData['arrHeader']['nu_numero_documento'];
				error_log('*** Etapa 2.1 ***');
				error_log( $primary_key_factura_cabecera );
				//die();

				$arrVerifyFE = array(
					//'nu_fac_recargo3' => ($arrData['arrHeader']['no_anulado'] != "S" ? 1 : 2),// 1 = COMPLETADO y 2 = ANULADO
					'nu_fac_recargo3' => 1,// 1 = COMPLETADO
					'ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento' => $primary_key_factura_cabecera,
				);

				// verifiy if exist sale invoice in SUNAT
				$arrVerifyResponseFE = $modelSalesInvoice->verify_register_SUNAT($arrVerifyFE);
				if ( ($bStatusFESunat) && $arrVerifyResponseFE["sStatus"] != "success" ){
					echo json_encode($arrVerifyResponseFE);
					$bStatusFESunat = false;
				}

				/*
				 Name: Prepare data
				 Actions: INSERT INTO and UPDATE
				 Tables: ebi_queue and fac_ta_factura_cabecera
				*/

				// taxid
				$taxid = $arrData["arrCompany"]["iEmpresaRuc"];
				if ( ($bStatusFESunat) && empty($taxid) ) {
					echo json_encode(array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener RUC de la Empresa', 'ruc' => $taxid));
					$bStatusFESunat = false;
				}

				// optype
				$optype = ($arrData['arrHeader']['no_anulado'] != "S" ? 0 : 1);

				// status
				$status = 0;// Registrado = Listo para enviar

				// callback				
				if ($optype == 0) { //EMITIR
					$callback = <<<EOT
{
	"1":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 3 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento = '''||'{$primary_key_factura_cabecera}'||'''",
	"2":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 4 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento = '''||'{$primary_key_factura_cabecera}'||'''"
}
EOT;
				} else if ($optype == 1) {//ANULAR
			    	$callback = <<<EOT
{
	"1":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 5 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento = '''||'{$primary_key_factura_cabecera}'||'''",
	"2":"UPDATE fac_ta_factura_cabecera SET nu_fac_recargo3 = 6 WHERE ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento = '''||'{$primary_key_factura_cabecera}'||'''"
}
EOT;
				}

				/*** Verificar envio de decimales para precio unitario y cantidad en el detalle de las facturas ***/			
				error_log('*** Etapa 3 ***');
				error_log( json_encode( array($arrData, $arrDataHelper) ) );
				// die();
				/***/

				// content
				$arrContentResponseFE = $this->generate_document_content_SUNAT_format_OCS($arrData, $arrDataHelper);
				if ( ($bStatusFESunat) && $arrContentResponseFE["sStatus"] != "success" ){
					echo json_encode($arrContentResponseFE);
					$bStatusFESunat = false;
				}

				/*** Verificar envio de decimales para precio unitario y cantidad en el detalle de las facturas ***/			
				error_log('*** Etapa 4 ***');
				error_log( json_encode($arrContentResponseFE) );
				// die();
				/***/

				// Preparando los valores de SQL para el INSERT a la TABLA ebi_queue
				$arrData = array(
					'_id' => "nextval('seq_ebi_queue_id')",
					'created' => "now()",
					'taxid' => $taxid,
					'optype' => $optype,
					'status' => $status,
					'callback' => $callback,
					'content' => $modelSalesInvoice->text_clean_fe($arrContentResponseFE["sContent"]),
					//Parámetros adicionales
					'primary_key_factura_cabecera' => $primary_key_factura_cabecera,
					//Estado de documento de BD integrado - campo nu_fac_recargo3
					'iEstadoDocumento' => $arrGet['iEstadoDocumento'],
					'content_like_tipo_serie_numero' => $arrData['arrHeader']['nu_tipo_documento'] . '|' . $arrData['arrHeader']['no_serie_documento'] . '|0' . $arrData['arrHeader']['nu_numero_documento'],
				);

				/*** Verificar envio de decimales para precio unitario y cantidad en el detalle de las facturas ***/			
				error_log('*** Etapa 5 ***');
				error_log( json_encode($arrData) );
				//die();
				/***/

				if ( $bStatusFESunat ) {
					// Generando INSERT a la TABLA ebi_queue y UPDATE fac_ta_factura_cabecera
					echo json_encode($modelSalesInvoice->save_document_sunat($arrData)); //Acá te responde el mensaje "Documento enviado satisfactoriamente"
				}
			}
		}
	}

	public function get_company($arrGet){
		$modelSalesInvoice = new modelSalesInvoice();
		return $modelSalesInvoice->get_company($arrGet);
	}

	public function get_header($arrGet){
		$modelSalesInvoice = new modelSalesInvoice();
		return $modelSalesInvoice->get_header($arrGet);
	}

	public function get_detail($arrGet){
		$modelSalesInvoice = new modelSalesInvoice();
		return $modelSalesInvoice->get_detail($arrGet);
	}

	// FUNCION DE NUMEROS A LETRAS
	private function MontoMonetarioEnLetras($fTotal, $sNombreMoneda){
		$monto = str_replace(',', '', $fTotal);
		$pos = strpos($monto, '.');
		        
		if ($pos == false) {
		        $monto_entero = $monto;
		        $monto_decimal = '00';
		} else {
		        $monto_entero = substr($monto, 0, $pos);
		        $monto_decimal = substr($monto, $pos, strlen($monto)-$pos);
		        $monto_decimal = $monto_decimal * 100;
		}
		$monto = (int)($monto_entero);
		$texto_con = " CON " . $monto_decimal . "/100 " . $sNombreMoneda;

		return ($monto > 0 ? $this->NumerosALetras($monto) : "CERO") . $texto_con;
	}

	private function NumerosALetras($monto){
		$maximo                 = pow(10,9);
		$unidad                 = array(1=>"UNO", 2=>"DOS", 3=>"TRES", 4=>"CUATRO", 5=>"CINCO", 6=>"SEIS", 7=>"SIETE", 8=>"OCHO", 9=>"NUEVE" );
		$decena                 = array(10=>"DIEZ", 11=>"ONCE", 12=>"DOCE", 13=>"TRECE", 14=>"CATORCE", 15=>"QUINCE", 20=>"VEINTE", 30=>"TREINTA", 40=>"CUARENTA", 50=>"CINCUENTA", 60=>"SESENTA", 70=>"SETENTA", 80=>"OCHENTA", 90=>"NOVENTA");
		$prefijoDecena			= array(10=>"DIECI", 20=>"VEINTI", 30=>"TREINTA Y ", 40=>"CUARENTA Y ", 50=>"CINCUENTA Y ", 60=>"SESENTA Y ", 70=>"SETENTA Y ", 80=>"OCHENTA Y ", 90=>"NOVENTA Y ");
		$centena				= array(100=>"CIEN", 200=>"DOSCIENTOS", 300=>"TRESCIENTOS", 400=>"CUATROCIENTOS", 500=>"QUINIENTOS", 600=>"SEISCIENTOS", 700=>"SETECIENTOS", 800=>"OCHOCIENTOS", 900=>"NOVECIENTOS");        
		$prefijoCentena			= array(100=>"CIENTO ", 200=>"DOSCIENTOS ", 300=>"TRESCIENTOS ", 400=>"CUATROCIENTOS ", 500=>"QUINIENTOS ", 600=>"SEISCIENTOS ", 700=>"SETECIENTOS ", 800=>"OCHOCIENTOS ", 900=>"NOVECIENTOS ");
		$sufijoMiles			= "MIL";
		$sufijoMillon			= "UN MILLON";
		$sufijoMillones			= "MILLONES";
		$base					= strlen(strval($monto));
		$pren					= intval(floor($monto/pow(10,$base-1)));
		$prencentena			= intval(floor($monto/pow(10,3)));
		$prenmillar				= intval(floor($monto/pow(10,6)));
		$resto					= $monto%pow(10,$base-1);
		$restocentena			= $monto%pow(10,3);
		$restomillar			= $monto%pow(10,6);
		
		if (!$monto) return "";
		
		if (is_int($monto) && $monto > 0 && $monto < abs($maximo)) {
			switch ($base) {
	            case 1: return $unidad[$monto];
	            case 2: return array_key_exists($monto, $decena)  ? $decena[$monto]  : $prefijoDecena[$pren*10]   . $this->NumerosALetras($resto);
	            case 3: return array_key_exists($monto, $centena) ? $centena[$monto] : $prefijoCentena[$pren*100] . $this->NumerosALetras($resto);
	            case 4: case 5: case 6: return ($prencentena>1) ? $this->NumerosALetras($prencentena). " ". $sufijoMiles . " " . $this->NumerosALetras($restocentena) : $sufijoMiles. " " . $this->NumerosALetras($restocentena);
	            case 7: case 8: case 9: return ($prenmillar>1)  ? $this->NumerosALetras($prenmillar). " ". $sufijoMillones . " " . $this->NumerosALetras($restomillar)  : $sufijoMillon. " " . $this->NumerosALetras($restomillar);
		    }
		} else return false;
	}
	//. FUNCION DE NUMEROS A LETRAS

	// Validaciones FE
	public function verify_validations_FE($arrPost, $arrDataHelper) {
		$arrResponse = array('sStatus' => 'danger', 'sMessage' => 'error SQL - function verify_validations_FE()');
		if ($arrPost['sNombreValidacion'] == 'fecha') {
			$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Se puede registrar');
			if ( strlen(trim($arrPost['sSerieDocumento'])) == 4 ){//Solo si son series electronicas y contingencia realizar verificación de días
				$dFechaValida = date_create($arrDataHelper["date_ymd_today"]);
				if ( isset($arrPost['no_anulado']) && $arrPost['no_anulado'] == 'S' ) {
					date_add($dFechaValida, date_interval_create_from_date_string('-5 days'));
					$sMessageStatus = 'anular';
				} else {
					date_add($dFechaValida, date_interval_create_from_date_string('-5 days'));
					$sMessageStatus = 'registrar';
				}
				$dFechaValida = date_format($dFechaValida, 'Y-m-d');
				$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'Solo se pueden ' . $sMessageStatus . ' documentos electrónicos hasta 5 días');
				if($arrPost['dFechaEmision'] >= $dFechaValida)
					$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Se puede registrar');
			}
		} else if ($arrPost['sNombreValidacion'] == 'tipos_impuesto') {
			/*
			- Reglas SUNAT (FE):
				* Códigos de Tipo de Afectación del IGV - Cat. 07
					1. 10 Gravado - Operación Onerosa
					2. 20 Exonerado - Operación Onerosa
					3. 15 Gravado – Bonificaciones
					4. 21 Exonerado – Transferencia Gratuita
					5. 30 Inafecto - Operación Onerosa
					6. 31 Inafecto – Retiro por Bonificación
			- Valores (OCS) de tipos de impuesto:
				      Impuesto                  | ch_fac_tiporecargo2    |    Valor de impuesto (S / N)
				----------------------------------------------------------------------------------------
				- Op. Gravadas                  =   vacío 				 =		S
				- Op. Exoneradas                =   S 				 	 =		N
				- Op. Gratuitas                 =   T 				 	 =		S
				- Op. Gratuitas + Exoneradas    =   U  				 	 =		N
				- Op. Inafectas                 =   V  	 				 =		N
				- Op. Gratuitas + Inafectas     =   W  	 				 =		N
			*/

			$iImpuesto = (((double)$arrDataHelper["fImpuesto"] - 1) * 100);

			if ( empty($arrPost["no_codigo_impuesto"]) ){
				$fImpuesto = (double)$arrDataHelper["fImpuesto"];
				$arrImpuesto[] = array(
					'fImpuesto' => $fImpuesto,
					'iCodigoImpuesto' => '10',
					'sDescripcionImpuesto' => 'Gravado - Operación Onerosa',
					'sTotalImpuesto' => 'Gravadas',
				);
			}

			if ( $arrPost["no_codigo_impuesto"] == "S" ){
				$arrImpuesto[] = array(
					'fImpuesto' => 1,
					'iCodigoImpuesto' => '20',
					'sDescripcionImpuesto' => 'Exonerado - Operación Onerosa',
					'sTotalImpuesto' => 'Exoneradas'
				);
			}

			if ( $arrPost["no_codigo_impuesto"] == "T" ){
				$arrImpuesto[] = array(
					'fImpuesto' => 1,
					'iCodigoImpuesto' => '15',
					'sDescripcionImpuesto' => 'Gravado – Bonificaciones',
					'sTotalImpuesto' => 'Gratuitas'
				);
			}

			if ( $arrPost["no_codigo_impuesto"] == "U" ){
				$arrImpuesto[] = array(
					'fImpuesto' => 1,
					'iCodigoImpuesto' => '21',
					'sDescripcionImpuesto' => 'Exonerado – Transferencia Gratuita',
					'sTotalImpuesto' => 'Gratuitas + Exoneradas'
				);
			}

			if ( $arrPost["no_codigo_impuesto"] == "V" ){
				$arrImpuesto[] = array(
					'fImpuesto' => 1,
					'iCodigoImpuesto' => '30',
					'sDescripcionImpuesto' => 'Inafecto - Operación Onerosa',
					'sTotalImpuesto' => 'Inafectas'
				);
			}

			if ( $arrPost["no_codigo_impuesto"] == "W" ){
				$arrImpuesto[] = array(
					'fImpuesto' => 1,
					'iCodigoImpuesto' => '31',
					'sDescripcionImpuesto' => 'Inafecto – Retiro por Bonificación',
					'sTotalImpuesto' => 'Inafectas'
				);
			}

			$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'No se encontró ningún impuestos tributario', 'arrRow' => 0);
			if (count($arrImpuesto) > 0)
				$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Impuestos tributarios', 'arrRow' => $arrImpuesto);
		}
		if ($arrPost["sDataType"] == "JSON")
			echo json_encode($arrResponse);
		else
			return $arrResponse;
	}

	// Validaciones
	function verify_several_types_validations($arrPost, $arrDataHelper){
		$arrResponse = array('sStatus' => 'danger', 'sMessage' => 'error SQL - function verify_several_types_validations()');
		if ($arrPost['sNombreValidacion'] == 'numero_documento_identidad_nombres') {
			/*
			El monto mínimo de (350 || 700) (obtener de int_parametros -> max_unidentified)
			- Regla SUNAT (FE):
				* Documento de Identidad del Cliente - Cat. 06
					1. Si Numero documento de identidad = 8 then 1 - DNI (EXACTO)
					2. Si Numero documento de identidad = 11 then 6 - RUC (EXACTO)
					3. ELSE 0 - OTROS (NO EXACTO)
			- Validación:
				* Si es factura / NC - FACTURA / ND - FACTURA deben contener el mismo cliente y RUC - 11
				* Si es boleta / NC - BOLETA / ND - BOLETA debe contener DNI / OTROS
			- Parámetros:
				* Tipo: Tipo de documento de venta
				* CLI: Codigo de cliente (RUC, DNI y OTROS) OCS -> Integrado -> int_clientes -> cli_ruc
			*/
			$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Documento válido', 'sTipoDocumentoIdentidad' => '-', 'iTipoDocumentoIdentidad' => 0);
			if (
				$arrPost['iTipoDocumento'] == '35' ||
				($arrPost['iTipoDocumento'] == '20' && $arrPost['iTipoDocumentoReferencia'] == '35') ||
				($arrPost['iTipoDocumento'] == '11' && $arrPost['iTipoDocumentoReferencia'] == '35')
			){ //Boleta de Venta || (N/C y Boleta de Venta)
				$iTipoDocumentoIdentidad = (strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 8 ? 1 : 0);
				$sTipoDocumentoIdentidad = (strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 8 ? 'DNI' : 'OTROS');

				$arrResponse = array('sStatus' => 'warning', 'sMessage' => $sTipoDocumentoIdentidad . ' inválido', 'sTipoDocumentoIdentidad' => $sTipoDocumentoIdentidad, 'iTipoDocumentoIdentidad' => $iTipoDocumentoIdentidad);
				//Castear variables
				$fMontoMinimo = (double)$arrDataHelper["fMontoMinimo"];
				$arrPost['fTotTotal'] = (double)$arrPost['fTotTotal'];

				//if ( $arrPost['iNumeroDocumentoIdentidadCliente'] == "99999999999" || strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 8 || strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 15 || (strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 11 && substr($arrPost['iNumeroDocumentoIdentidadCliente'], 0, 2)=="10") ) { // Entra si es persona natural con RUC
				if ( trim($arrPost['iNumeroDocumentoIdentidadCliente']) == "99999999999" || strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 8 || strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 15 || strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 11 ) { // Entra si es persona natural con RUC
					//Validacion por documento boleta monto mínimo 350 || 700 soles
					if ( $arrPost['fTotTotal'] >= $fMontoMinimo ){
						//Verificar que Numero documento de identidad = 8 THEN (1 - DNI) ó (0 - OTROS) -> Datos SUNAT
						if ( strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 11 || strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 8 || strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 15 )
							$arrResponse = array('sStatus' => 'success', 'sMessage' => $sTipoDocumentoIdentidad . ' válido', 'sTipoDocumentoIdentidad' => $sTipoDocumentoIdentidad, 'iTipoDocumentoIdentidad' => $iTipoDocumentoIdentidad);
					} else if ( $arrPost['fTotTotal'] < $fMontoMinimo ){
						$arrResponse = array('sStatus' => 'success', 'sMessage' => $sTipoDocumentoIdentidad . ' válido', 'sTipoDocumentoIdentidad' => $sTipoDocumentoIdentidad, 'iTipoDocumentoIdentidad' => $iTipoDocumentoIdentidad);
					}
				} else {
					$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'Cliente no válido. Debe considerar las siguientes validaciones para el campo ruc: - Boleta: DNI - 8 caracteres o RUC - 11 caracteres - Cliente Varios: 99999999999 - Otros: OTROS - 15 caracteres', 'sTipoDocumentoIdentidad' => $sTipoDocumentoIdentidad, 'iTipoDocumentoIdentidad' => $iTipoDocumentoIdentidad);
				}
			} else if (
				$arrPost['iTipoDocumento'] == '10' ||
				($arrPost['iTipoDocumento'] == '20' && $arrPost['iTipoDocumentoReferencia'] == '10') ||
				($arrPost['iTipoDocumento'] == '11' && $arrPost['iTipoDocumentoReferencia'] == '10')
			){ //Factura || (N/C y Fatura) || (N/D y Factura)
				//Verificar que Numero documento de identidad = 11 THEN (6 - RUC) -> Datos SUNAT
				$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'RUC inválido');
				if (strlen($arrPost['iNumeroDocumentoIdentidadCliente']) == 11)
					$arrResponse = array('sStatus' => 'success', 'sMessage' => 'RUC válido', 'sTipoDocumentoIdentidad' => 'RUC', 'iTipoDocumentoIdentidad' => 6);
			}
		}
		if ($arrPost["sDataType"] == "JSON")
			echo json_encode($arrResponse);
		else
			return $arrResponse;
	}

	public function get_totals($arrHeader, $arrDataHelper){
		/*
		- Reglas SUNAT (FE):
			* Códigos de Tipo de Afectación del IGV - Cat. 14
				1000 - Total valor de venta – operaciones exportada
				1001 - Total valor de venta - operaciones gravadas
				1002 - Total valor de venta - operaciones inafectas
				1003 - Total valor de venta - operaciones exoneradas
				1004 - Total valor de venta – Operaciones gratuitas
				1005 - Sub total de venta
				2001 - Percepciones
				2002 - Retenciones
				2003 - Detracciones
				2004 - Bonificaciones
				2005 - Total descuentos
				3001 - FISE (Ley 29852) Fondo Inclusión Social Energético
		- Valores (OCS) de tipos de impuesto:
			      Impuesto                  | ch_fac_tiporecargo2    |    Valor de impuesto (S / N)
			----------------------------------------------------------------------------------------
			- Op. Gravadas                  =   vacío 				 =		S
			- Op. Exoneradas                =   S 				 	 =		N
			- Op. Gratuitas                 =   T 				 	 =		S
			- Op. Gratuitas + Exoneradas    =   U  				 	 =		N
			- Op. Inafectas                 =   V  	 				 =		N
			- Op. Gratuitas + Inafectas     =   W  	 				 =		N
		*/

		$iImpuesto = (((double)$arrDataHelper["fImpuesto"] - 1) * 100);

		$arrMontos = array();
		if ( empty($arrHeader["arrRow"]["no_codigo_impuesto"]) ) {
			$arrMontos[] = array(
				"fSubTotal" => $arrHeader["arrRow"]["ss_valor_venta"],
				"sDescripcionImpuesto" => 'Gravadas',
				"sValorImpuesto" => "I.G.V. (" . $iImpuesto . " %)",
				"iTipoAfectacionIGV" => 1001,
			);
		}

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "S" ) {
			$arrMontos[] = array(
				"fSubTotal" => $arrHeader["arrRow"]["ss_valor_venta"],
				"sDescripcionImpuesto" => 'Exoneradas',
				"sValorImpuesto" => "I.G.V. (0 %)",
				"iTipoAfectacionIGV" => 1003,
			);
		}

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "T" ) {
			$arrMontos[] = array(
				"fSubTotal" => $arrHeader["arrRow"]["ss_gratuita"],
				"sDescripcionImpuesto" => 'Gratuitas',
				"sValorImpuesto" => "I.G.V. (" . $iImpuesto . " %)",
				"iTipoAfectacionIGV" => 1004,
			);
		}

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "U" ) {
			$arrMontos[] = array(
				"fSubTotal" => 0.00,
				"sDescripcionImpuesto" => 'Exoneradas',
				"sValorImpuesto" => "I.G.V. (0 %)",
				"iTipoAfectacionIGV" => 1003,
			);

			$arrMontos[] = array(
				"fSubTotal" => $arrHeader["arrRow"]["ss_gratuita"],
				"sDescripcionImpuesto" => 'Gratuitas',
				"sValorImpuesto" => "I.G.V. (0 %)",
				"iTipoAfectacionIGV" => 1004,
			);
		}

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "V" ) {
			$arrMontos[] = array(
				"fSubTotal" => $arrHeader["arrRow"]["ss_valor_venta"],
				"sDescripcionImpuesto" => 'Inafectas',
				"sValorImpuesto" => "I.G.V. (0 %)",
				"iTipoAfectacionIGV" => 1002,
			);
		}

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "W" ) {
			$arrMontos[] = array(
				"fSubTotal" => 0.00,
				"sDescripcionImpuesto" => 'Inafectas',
				"sValorImpuesto" => "I.G.V. (0 %)",
				"iTipoAfectacionIGV" => 1002,
			);

			$arrMontos[] = array(
				"fSubTotal" => $arrHeader["arrRow"]["ss_gratuita"],
				"sDescripcionImpuesto" => 'Gratuitas',
				"sValorImpuesto" => "I.G.V. (" . $iImpuesto . " %)",
				"iTipoAfectacionIGV" => 1004,
			);
		}

		// Adicionar 2005 - Total descuentos
		if ( (float)$arrHeader["arrRow"]["ss_descuento"] > 0.00 ) {
			$arrMontos[] = array(
				"fSubTotal" => $arrHeader["arrRow"]["ss_descuento"],
				"sDescripcionImpuesto" => 'Descuentos',
				"sValorImpuesto" => "I.G.V. (" . $iImpuesto . " %)",
				"iTipoAfectacionIGV" => 2005,
			);			
		}

		// Adicionar 2003 - Detracciones
		if (
			!empty($arrHeader["arrRow"]["nu_numero_cuenta_detraccion"]) &&
			!empty($arrHeader["arrRow"]["ss_importe_detraccion"]) &&
			!empty($arrHeader["arrRow"]["nu_porcentaje_detraccion"]) &&
			!empty($arrHeader["arrRow"]["nu_codigo_bienes_servicio_detraccion"])
		) {
			$arrMontos[] = array(
				"fSubTotal" => $arrHeader["arrRow"]["ss_importe_detraccion"],
				"sDescripcionImpuesto" => 'Detracciones',
				"sValorImpuesto" => "I.G.V. (" . $iImpuesto . " %)",
				"iTipoAfectacionIGV" => 2003,
			);			
		}

		$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'No se encontró totales', 'sNameFunction' => 'get_totals()');
		if (count($arrMontos) > 0)
			$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Totales', 'arrRow' => $arrMontos);
		return $arrResponse;		
	}

	public function get_total_legend($arrHeader){
		/*
		- Regla SUNAT (FE):
			* Leyenda / Propiedad importes - Cat. 15
				1. 1000 - Monto en Letras
				2. 1002 - TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE
				3. 2001 - BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA
				Si tiene detracción adicionar:
					1. 2006 - Operación sujeta a detracción
					2. 3000 - CÓDIGO DE BB Y SS SUJETOS A DETRACCION - Obtener valor DB (integrado)
					3. 3001 - NÚMERO DE CTA EN EL BN - Obtener valor DB (integrado)
		- Reglas SUNAT (FE):
			* Códigos de Tipo de Afectación del IGV - Cat. 07
				1. 10 Gravado - Operación Onerosa
				2. 20 Exonerado - Operación Onerosa
				3. 15 Gravado – Bonificaciones
				4. 21 Exonerado – Transferencia Gratuita
				5. 30 Inafecto - Operación Onerosa
				6. 31 Inafecto – Retiro por Bonificación
		- Valores (OCS) de tipos de impuesto:
			      Impuesto                  | ch_fac_tiporecargo2    |    Valor de impuesto (S / N)
			----------------------------------------------------------------------------------------
			- Op. Gravadas                  =   vacío 				 =		S
			- Op. Exoneradas                =   S 				 	 =		N
			- Op. Gratuitas                 =   T 				 	 =		S
			- Op. Gratuitas + Exoneradas    =   U  				 	 =		N
			- Op. Inafectas                 =   V  	 				 =		N
			- Op. Gratuitas + Inafectas     =   W  	 				 =		N
		*/
		$fTotal = ( (empty($arrHeader["arrRow"]["no_codigo_impuesto"]) || $arrHeader["arrRow"]["no_codigo_impuesto"] == "S" || $arrHeader["arrRow"]["no_codigo_impuesto"] == "V") ? $arrHeader["arrRow"]["ss_total"] : 0.00);
		$sMontoEnLetras = $this->MontoMonetarioEnLetras($fTotal, $arrHeader["arrRow"]["no_nombre_moneda"]);

		$arrMontoLetras[] = array(
			"sCodigoLeyena" => "1000",
			"sValorLeyena" => $sMontoEnLetras,
			"sExtraValorLeyendaPDF" => "SON: ",
		);

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "S" ) {
			$arrMontoLetras[] = array(
				"sCodigoLeyena" => "2001",
				"sValorLeyena" => "BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA",
				"sExtraValorLeyendaPDF" => "",
			);
		}

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "T" ) {
			$arrMontoLetras[] = array(
				"sCodigoLeyena" => "1002",
				"sValorLeyena" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE",
				"sExtraValorLeyendaPDF" => "",
			);
		}

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "U" ) {
			$arrMontoLetras[] = array(
				"sCodigoLeyena" => "2001",
				"sValorLeyena" => "BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA",
				"sExtraValorLeyendaPDF" => "",
			);

			$arrMontoLetras[] = array(
				"sCodigoLeyena" => "1002",
				"sValorLeyena" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE",
				"sExtraValorLeyendaPDF" => "",
			);
		}

		if ( $arrHeader["arrRow"]["no_codigo_impuesto"] == "W" ) {
			$arrMontoLetras[] = array(
				"sCodigoLeyena" => "1002",
				"sValorLeyena" => "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE",
				"sExtraValorLeyendaPDF" => "",
			);
		}

		// Adicionar Detracciones
		if (
			!empty($arrHeader["arrRow"]["nu_numero_cuenta_detraccion"]) &&
			!empty($arrHeader["arrRow"]["ss_importe_detraccion"]) &&
			!empty($arrHeader["arrRow"]["nu_porcentaje_detraccion"]) &&
			!empty($arrHeader["arrRow"]["nu_codigo_bienes_servicio_detraccion"])
		) {
			$arrMontoLetras[] = array(
				"sCodigoLeyena" => "2006",
				"sValorLeyena" => "OPERACION SUJETA A DETRACCION",
				"sExtraValorLeyendaPDF" => "",
			);
			$arrMontoLetras[] = array(
				"sCodigoLeyena" => "3000",
				"sValorLeyena" => $arrHeader["arrRow"]["nu_codigo_bienes_servicio_detraccion"],
				"sExtraValorLeyendaPDF" => "CÓDIGO DE BB Y SS SUJETOS A DETRACCION: ",
			);
			$arrMontoLetras[] = array(
				"sCodigoLeyena" => "3001",
				"sValorLeyena" => $arrHeader["arrRow"]["nu_numero_cuenta_detraccion"],
				"sExtraValorLeyendaPDF" => "NÚMERO DE CTA EN EL BN: ",
			);			
		}

		$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'No se encontró leyenda totales', 'arrRow' => 0);
		if (count($arrMontoLetras) > 0)
			$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Leyenda de totales', 'arrRow' => $arrMontoLetras);
		return $arrResponse;
	}

	// Generar estructura para FE
	private function schemaLine($params) {
		$line = '';
		$return = array();
		$count = count($params);
		for ($i = 0; $i < $count; $i++) { 
			$line .= $params[$i];
			if ($i != ($count -1)) {
				$line .= '|';
			}
		}
		return array(
			'valid' => $this->checkValidLineFe($line),
			'line' => $line,
		);
	}

	private function checkValidLineFe($data) {
		if ($data == '') {
			return false;
		}
		$data = explode('|', $data);
		for ($i = 0; $i < count($data); $i++) { 
			if (TRIM($data[$i]) == '') {
				return false;
			}
		}
		return true;
	}

	private function generate_document_content_SUNAT_format_OCS($arrData, $arrDataHelper) { //METODO PARA OBTENER CAMPO CONTENT EN EBI_QUEUE
		$arrCadenaFESUNAT = "";

		if ( $arrData["arrHeader"]["no_anulado"] == "S" ) {
			$arrDataAnulado = array(
				$arrData["arrHeader"]["fe_emision"],
				$arrData["arrHeader"]["nu_tipo_documento"],
				$arrData["arrHeader"]["no_serie_documento"],
				'0' . $arrData["arrHeader"]["nu_numero_documento"]
			);

			$arrLineAnulado = $this->schemaLine($arrDataAnulado);
			if ( !($arrLineAnulado["valid"]) )
				return $arrLineAnulado = array('sStatus' => 'danger', 'sMessage' => 'Problemas al generar contenido de anulación', 'sContent' => $arrLineAnulado["line"]);
			$arrCadenaFESUNAT .= "\n" . $arrLineAnulado["line"];
			$sTipoAccionDocumento = "Anulado";
		} else {
			/**
			 * Desarrollo: Estructura de Facturación Electrónica OCS - SUNAT
			 * Tipo Dato: JSON
			 * Envío: Tabla de Almacenamiento
			 * Validaciones SUNAT(OCS):
			 	1. Fecha:
			 		- Documento Normal: Solo se pueden registrar documentos con el día actual ó máximo hasta 5 días antes.
					- Documento Anulado: Solo se pueden registrar documentos con el día actual ó máximo hasta 5 día antes.
				2. Documento de Identidad del Cliente:
					2.1. Tipo de documento:
						- Factura / NC /ND:
							El cliente no puede ser un DNI ú OTROS.
							Recordar que si es (NC / ND) ambos documentos deben de tener el mismo cliente.
							El RUC debe de contener 11 dígitos (EXACTO)
						- Boleta / NC / ND:
							Solo puede ser DNI 8 dígitos (EXACTO) ú Otros (NO EXACTO).
							Recordar que si es (NC / ND) ambos documentos deben de tener el mismo cliente.
						- NC / ND:
							Glosa mayor a 7 dígitos
							Ambas series deben de ser iguales, EJEMPLO: NC(F001) A F(F001) ó NB(B001) A B(B001)
					2.2. Importe:
						- Boleta: Si es IGV y es mayor a 700 soles, deben de colocar DNI 8 dígitos (EXACTO) y Nombres completos OBLIGATORIO
						- Boleta: Si es Exonerada y es mayor a 350 soles, deben de colocar DNI 8 dígitos (EXACTO) y Nombres completos OBLIGATORIO
			*/

			/**
			 * Primera Línea del documento contiene los datos principales (Cabecera):
			 * Parámetros:
				- Tipo de documento: 01 para Factura, 03 para Boleta, 07 para Nota de Crédito, 08 para Nota de Débito (Catálogo Número 01).
				- Serie: Debe comenzar con la letra “B” o ”F” (Mayúsculas), en concordancia con el tipo de documento y referencia en caso de Nota de Crédito y Débito (si existiera). Los siguientes tres caracteres son numéricos. Ejemplo: F001.
				- Número: Solo caracteres númericos, 8 digitos exactos, con ceros (0) a la izquierda. Ejemplo: 00000033.
				- Moneda: Moneda de la Factura, codigo ISO, 3 caracteres (Catálogo Número 02). Ejemplo: “PEN” para Moneda Soles.
				- Fecha: Fecha del documento, formato ISO YYYY-MM-DD. Ejemplo: 2017-01-01.
				- Gran Total: Dos digitos decimales, sin separador de miles, se usa punto decimal(.). Ejemplo: 22500.20
				- Tipo de Documento de Identidad del Cliente: Catálogo Número 06. Ejemplo: 6 para REG. UNICO DE CONTRIBUYENTES (RUC).
				- Documento de Identidad del Cliente: Si es no domiciliado se usa “-”.
				- Nombre del Cliente. Si no lleva nombre se usa “-”.
				- Documento de Referencia: En el caso de una Nota de Crédito o Débito se incluye el documento a referenciar. Si no hay se usa “-”. Ejemplo: F001-00000032 (FNNN-NNNNNNNN).
				- Motivo de Referencia: Si no hay se usa “-”, si es 01 Nota de Credito (Catálogo Número 09) y 03 es Noda de Débito (Catálogo Número 10).
				- Descripción de Referencia: Si no hay se usa “-”.
				- Total de descuentos globales: Si no hay se usa “0” (Cero).
			*/

			$sTipoDocumentoReferencia = '-';
			$sSerieNumeroDocumentoReferencia = '-';
			$sGlosaDocumentoReferencia = '-';
			if (
				!empty($arrData['arrHeader']['no_serie_documento_referencia']) &&
				!empty($arrData['arrHeader']['nu_numero_documento_referencia']) &&
				!empty($arrData['arrHeader']['nu_tipo_documento_referencia']) &&
				!empty($arrData['arrHeader']['txt_observaciones_referencia'])
			) {
				//$sTipoDocumentoReferencia = $arrData['arrHeader']['nu_tipo_documento_referencia_sunat'];				
				$sTipoDocumentoReferencia = (trim($arrData['arrHeader']['ch_cat_sunat']) == '' || $arrData['arrHeader']['ch_cat_sunat'] == NULL) ? $arrData['arrHeader']['nu_tipo_documento_referencia_sunat'] : $arrData['arrHeader']['ch_cat_sunat'];
				$sSerieNumeroDocumentoReferencia = $arrData['arrHeader']['no_serie_documento_referencia'] . "-" . $arrData['arrHeader']['nu_numero_documento_referencia'];
			}

			if ( strlen($arrData['arrHeader']['txt_observaciones_referencia']) > 3 )
				$sGlosaDocumentoReferencia = $arrData['arrHeader']['txt_observaciones_referencia'];

			$arrDataFirstLine = array(
				$arrData['arrHeader']['nu_tipo_documento'],
				trim($arrData['arrHeader']['no_serie_documento']),
				"0" . $arrData['arrHeader']['nu_numero_documento'],
				$arrData['arrHeader']['nu_codigo_moneda_sunat'],
				$arrData['arrHeader']['fe_emision'],
				(float)$arrData['arrHeader']['ss_total'],
				$arrData['arrHeader']['iTipoDocumentoIdentidad'],
				$arrData['arrHeader']['nu_documento_identidad_cliente'],
				$arrData['arrHeader']['no_razsocial_cliente'],
				$sSerieNumeroDocumentoReferencia,
				$sTipoDocumentoReferencia,
				$sGlosaDocumentoReferencia,
				(float)$arrData['arrHeader']['ss_descuento']
			);

			$arrFirstLine = $this->schemaLine($arrDataFirstLine);
			if ( !($arrFirstLine["valid"]) )
				return $arrFirstLine = array('sStatus' => 'danger', 'sMessage' => 'Problemas al generar contenido Primera Línea', 'sContent' => $arrFirstLine["line"]);
			$arrCadenaFESUNAT .= $arrFirstLine["line"];

			/*
			* Línea tipo “L” - Detalle de factura, productos y/o servicios del documento.
			* Parámetros:
				- Codigo: SKU o identificador interno del artículo.
				- Cantidad: 4 digitos decimales (con punto), sin separador de miles.
				- Unidad de Medida: Codigo ISO de la unidad de medida (Catálogo Número 03).
				- Costo Unitario: Correspondiente al "01" del Catálogo Número 16. Aquí va el precio real, cobrado; en operaciones no onerosas, "0".
				- Costo Auxiliar: correspondiente al "02" del Catálogo Número 16. Aquí va el precio referencial en operaciones no onerosas. En los demás casos usar "-".
				- Descripción: Nombre del Articulo o Servicio.
				- Subtotal: Valor sin impuestos.
				- IGV: Precedido por los dos digitos del tipo de afectación (Catálogo Número 07). Si no hay se usa "-".
				- ISC: Precedido por los dos digitos del sistema de cálculo (Catálogo Número 08). Si no hay se usa "-".
				- OTH: Sumatoria de otros impuestos. Si no hay se usa "-".
				- Descuento: Si no hay se usa "0".
				- Total: Con impuestos.
			*/
			foreach ($arrData["arrDetail"] as $row) {
				$fCostoUnitario = $row["ss_precio_venta_item"];
				foreach ($arrData["arrTaxCode"] as $row2) {
					$iCodigoImpuesto = $row2["iCodigoImpuesto"];
					if ( $iCodigoImpuesto == "10" ) {//10=Op. Gravadas
						// $fCostoUnitario = round($row["ss_precio_venta_item"] / $row2["fImpuesto"], 4, PHP_ROUND_HALF_UP);
						$fCostoUnitario = round($row["ss_subtotal"] / $row["qt_cantidad"], 6, PHP_ROUND_HALF_UP);
					}
				}

				$fCostoAuxiliar = '-';
				if (
					$row["no_codigo_impuesto_item"] == "T" ||
					$row["no_codigo_impuesto_item"] == "U" ||
					$row["no_codigo_impuesto_item"] == "W"
				) {
					$fCostoUnitario = 0;
					$fCostoAuxiliar = $row["ss_precio_venta_item"];
				}

				$arrDataLineL = array(
					"L",
					trim($row["nu_codigo_item"]),
					$row["qt_cantidad"],
					$row["nu_codigo_unidad_medida_sunat"],
					$fCostoUnitario,
					$fCostoAuxiliar,
					$row["no_nombre_item"],
					$row["ss_subtotal"],
					$iCodigoImpuesto . $row["ss_impuesto"],
					"-",
					"-",
					0,//(float)$row["ss_descuento"],
					$row["ss_total"]
				);

				$arrLineL = $this->schemaLine($arrDataLineL);
				if ( !($arrLineL["valid"]) )
					return $arrLineL = array('sStatus' => 'danger', 'sMessage' => 'Problemas al generar contenido Linea L', 'sContent' => $arrLineL["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineL["line"];
			}

			/**
			* Línea Tipo "T" – Impuesto del documento.
				- Código: Catálogo Número 05 (UN/ECE 5153)
				- Importe.
				- Porcentaje del impuesto: Dos decimales. Ejemplo: 18.00.
			* Nota: Solo se considerara VAT / EXC / OTH, lo demás se trara en el XML
			*/

			$iPorcentajeImpuesto = 0;
			if ( (float)$arrData["arrHeader"]["ss_impuesto"] > 0.00 )
				$iPorcentajeImpuesto = (((double)$arrDataHelper["fImpuesto"] - 1) * 100);

			$arrDataLineT = array(
				"T",
				"VAT",
				$arrData["arrHeader"]["ss_impuesto"],
				number_format($iPorcentajeImpuesto, 2, '.', '')
			);

			$arrLineT = $this->schemaLine($arrDataLineT);
			if ( !($arrLineT["valid"]) )
				return $arrLineT = array('sStatus' => 'danger', 'sMessage' => 'Problemas al generar contenido Linea T', 'sContent' => $arrLineT["line"]);
			$arrCadenaFESUNAT .= "\n" . $arrLineT["line"];

			/**
			* Línea Tipo "O" – Valor Total del documento.
				- Código: Catálogo Número 14.
				- Importe.
			*/
			foreach ($arrData["arrMontos"] as $row) {
				$arrDataLineO = array(
					"O",
					$row["iTipoAfectacionIGV"],
					$row["fSubTotal"],
				);

				$arrLineO = $this->schemaLine($arrDataLineO);
				if ( !($arrLineO["valid"]) )
					return $arrLineO = array('sStatus' => 'danger', 'sMessage' => 'Problemas al generar contenido Linea O', 'sContent' => $arrLineO["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineO["line"];
			}

			/**
			* Línea Tipo "X" – Extensión
				- Código de la extensión
				- Uno o más campos correspondientes a la extensión.
			* Casos de extensiones - Las extenciones permitidas en la Línea X son las siguientes:
			  Código | Valor
			  ---------------
				X0001 Placa de Veiculo - solo si es FACTURA
				X0002 Autorización de transporte terrestre (MTC)
				X0003 Marca del vehículo
				X0004 Licencia de conducir
				X0005 Transportista (Contiene RUC y luego Razón Social)
				X0006 Código de tipo de transporte
				X0007 Peso neto (Primero peso, luego unidad de medida)
				X0008 Hora de emisión (sólo hora en formato HH24:MM:SS) Ej: 20:10:30
				X0009 Glosa u observaciones del documento
				X0010 Consultar, Referencia de pago anticipado. Son 3 argumentos: Importe descontado del anticipo, tipo de documento y número de documento, No se usa, es para otros tipos de anticipos
				X0011 Consultar, Tipo de operación SUNAT
				X0012 Referencia a documento de traslado. Lleva primero tipo y después número
				X0013 Dirección de entrega, argumentos en este orden: código ubigeo, vía, zona, distrito, provincia, departamento y país
				X0014 Dirección de origen. Igual a la de entrega
				X0015 Referencia al número de orden
				X0016 Forma de pago del documento
				X0017 Fecha de vencimiento del documento
				X0018 Fecha del documento de referencia (Sólo Nota de Crédito y Nota de Débito)
				X0019 Instrucción de Pago. Suele ser el detalle del medio de pago(X0016). Por ejemplo, el tipo de tarjeta(Visa, Mastercard, etc) para pago con tarjeta(48), o el nombre de banco y número de cuenta para el pago bancarizado(42). El avlor es libre.
				X0020 Glosa del Pago. Libre.
				X0021 Dirección del establecimiento. Argumentos: CODIGO_ESTABLECIMIENTO|NOMBRE_ESTABLECIMIENTO|UBIGEO|DIRECCION|ZONA|DISTRITO|PROVINCIA|DEPARTAMENTO
				X0022 Medio de contacto. Lleva dos argumentos: Tipo de medio de contacto, y medio de contacto. Lee mas abajo para entenderlo.
				X0023 Cuenta de Detracciones (Sólo UBL 2.1)
				X0024 Código de Bienes y Servicios afectos a detracción (Sólo UBL 2.1)
				X0025 Porcentaje de detracción (Sólo UBL 2.1)
				X0026 Importe de detracción (Sólo UBL 2.1)

				En cuanto a medio de contacto, estos son los tipos:
				AH Página Web
				AI Código de País
				AJ Teléfono Alternativo
				AL Teléfono Móvil
				EM Correo Electrónico
				FX Fax
				MA Correo
				PB Apartado Postal
				TE Teléfono
			*/

			if ( $arrData['arrHeader']['_nu_tipo_documento'] == "10" ){//Solo si es 10 = FACTURA
				foreach ($arrData["arrPlates"] as $key => $sPlaca) {
					$arrPlates = array(
						'X',
						'X0001',
						$sPlaca
					);

					$arrLineXPlates = $this->schemaLine($arrPlates);
					if ( !($arrLineXPlates["valid"]) )
						return $arrLineXPlates = array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener placas - Linea X', 'sContent' => $arrLineXPlates["line"]);
					$arrCadenaFESUNAT .= "\n" . $arrLineXPlates["line"];
				}
			}

			if ( strlen($sGlosaDocumentoReferencia) > 2 ) {
				$arrGlosaDocumentoReferencia = array(
					'X',
					'X0009',
					$sGlosaDocumentoReferencia
				);

				$arrLineXGlosaDocumentoReferencia = $this->schemaLine($arrGlosaDocumentoReferencia);
				if ( !($arrLineXGlosaDocumentoReferencia["valid"]) )
					return $arrLineXGlosaDocumentoReferencia = array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener glosa - Linea X', 'sContent' => $arrLineXGlosaDocumentoReferencia["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineXGlosaDocumentoReferencia["line"];
			}

			// SOLO PARA (FACTURA Y BOLETA DE VENTA)
			if ( $arrData["arrHeader"]["_nu_tipo_documento"] == "10" || $arrData["arrHeader"]["_nu_tipo_documento"] == "35" ) {
				$arrFormaPago = array(
					'X',
					'X0016',
					$arrData["arrHeader"]["nu_codigo_forma_pago_sunat"]
				);
				
				$arrLineXFormaPago = $this->schemaLine($arrFormaPago);
				if ( !($arrLineXFormaPago["valid"]) )
					return $arrLineXFormaPago = array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener forma pago - Linea X', 'sContent' => $arrLineXFormaPago["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineXFormaPago["line"];
			}

			// SOLO PARA (FACTURA Y BOLETA DE VENTA)
			if ( $arrData["arrHeader"]["_nu_tipo_documento"] == "10" || $arrData["arrHeader"]["_nu_tipo_documento"] == "35" ) {
				$arrFechaVencimiento = array(
					'X',
					'X0017',
					$arrData["arrHeader"]["fe_vencimiento"]
				);

				$arrLineXFechaVencimiento = $this->schemaLine($arrFechaVencimiento);
				if ( !($arrLineXFechaVencimiento["valid"]) )
					return $arrLineXFechaVencimiento = array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener fecha de vencimiento - Linea X', 'sContent' => $arrLineXFechaVencimiento["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineXFechaVencimiento["line"];
			}

			// SOLO PARA (NOTA DE CRÉDITO Y DÉBITO)
			if ( $arrData["arrHeader"]["_nu_tipo_documento"] == "20" || $arrData["arrHeader"]["_nu_tipo_documento"] == "11" ) {
				$arrFechaReferencia = array(
					'X',
					'X0018',
					$arrData["arrHeader"]["fe_emision_referencia"]
				);

				$arrLineXFechaReferencia = $this->schemaLine($arrFechaReferencia);
				if ( !($arrLineXFechaReferencia["valid"]) )
					return $arrLineXFechaReferencia = array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener fecha de refencia - Linea X', 'sContent' => $arrLineXFechaReferencia["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineXFechaReferencia["line"];
			}

			// CODIGO_ESTABLECIMIENTO|NOMBRE_ESTABLECIMIENTO|UBIGEO|DIRECCION|ZONA|DISTRITO|PROVINCIA|DEPARTAMENTO
			// La lógica es que si en inv_ta_almacenes el almacen tiene ch_dirección distinto de NULL y distinto de vacío o un espacio, pones la dirección como argumento de X0021
			if (
		    	!empty($arrData["arrCompany"]["sEstablecimientoDireccion"]) &&
		    	!empty($arrData["arrCompany"]["sEstablecimientoDistrito"]) &&
		    	!empty($arrData["arrCompany"]["sEstablecimientoProvincia"]) &&
		    	!empty($arrData["arrCompany"]["sEstablecimientoDepartamento"])
			) {
				$arrFechaReferencia = array(
					'X',
					'X0021',
					$arrData["arrHeader"]["nu_codigo_almacen"] . "|" . $arrData["arrCompany"]["sEstablecimientoNombre"] . "|" . $arrData["arrCompany"]["iEstablecimientoCodigo"] . "|" . $arrData["arrCompany"]["sEstablecimientoDireccion"] . "|" . $arrData["arrCompany"]["sEstablecimientoZona"] . "|" . $arrData["arrCompany"]["sEstablecimientoDistrito"] . "|" . $arrData["arrCompany"]["sEstablecimientoProvincia"] . "|" . $arrData["arrCompany"]["sEstablecimientoDepartamento"],
				);

				$arrLineXEstablecimiento = $this->schemaLine($arrFechaReferencia);
				if ( !($arrLineXEstablecimiento["valid"]) )
					return $arrLineXEstablecimiento = array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener dirección del establecimiento - Linea X', 'sContent' => $arrLineXEstablecimiento["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineXEstablecimiento["line"];
			}

			// OPENSOFT-99: Mejoras en gestión de importes del SPOT en facturas de venta
			// Descontaremos el importe SPOT del importe de la unica cuota
			$importe_spot = 0;
			if (!empty($arrData["arrHeader"]["ss_importe_spot"])){
				$importe_spot = $arrData["arrHeader"]["ss_importe_spot"]; //Aqui esta o bien la Detraccion o Retencion
			}

			// OPENSOFT-59: R.S. 193-2020/SUNAT 
			// X|X0038|ID_CUOTA|MONTO|FECHA_PAGO
			// ID_CUOTA será siempre 001, pues sólo se calcularán cronogramas con una cuota; MONTO es el importe total del comprobante; y FECHA_PAGO es la fecha calculada a partir de la fecha de emisión + los días de crédito.
			if( TRIM($arrData["arrHeader"]["nu_tipo_pago"]) == "06" && TRIM($arrData["arrHeader"]["no_nombre_forma_pago"]) == "CREDITO" ){ //SI ES DOCUMENTO CON FORMA DE PAGO CREDITO
				$arrCuotaDePago = array(
					'X',
					'X0038',
					'001',
					(float)$arrData['arrHeader']['ss_total'] - (float)$importe_spot, //float, si se la pasa un string lo convierte a 0
					$arrData["arrHeader"]["fe_vencimiento"]
				);

				$arrLineXCuotaDePago = $this->schemaLine($arrCuotaDePago);
				if ( !($arrLineXCuotaDePago["valid"]) )
					return $arrLineXCuotaDePago = array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener cuotas de pago - Linea X', 'sContent' => $arrLineXCuotaDePago["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineXCuotaDePago["line"];
			}

			// OPENSOFT-103: Direccion de clientes en FE Sunat
			// X|X0046|DIRECCION
			if( $arrData["arrHeader"]["txt_linea_direccion_cliente"] != NULL ){ //SI LA DIRECCION ESTA DEFINIDA Y NO ES NULL
				if( TRIM($arrData["arrHeader"]["txt_linea_direccion_cliente"]) != "" ){ //SI LA DIRECCION ES DIFERENTE DE VACIO
					$arrDireccion = array(
						'X',
						'X0046',
						$arrData["arrHeader"]["txt_linea_direccion_cliente"]
					);

					$arrLineXDireccion = $this->schemaLine($arrDireccion);
					if ( !($arrLineXDireccion["valid"]) )
						return $arrLineXDireccion = array('sStatus' => 'danger', 'sMessage' => 'Problemas al obtener direccion - Linea X', 'sContent' => $arrLineXDireccion["line"]);
					$arrCadenaFESUNAT .= "\n" . $arrLineXDireccion["line"];
				}
			}

			/**
			* Línea Tipo "E" – Leyenda/Propiedad
				- Código: Catálogo Número 15.
				- Contenido.
			*/
			foreach ($arrData["arrMontoLetras"] as $row) {
				$arrDataLineE = array(
					"E",
					$row["sCodigoLeyena"],
					$row["sValorLeyena"],
				);

				$arrLineE = $this->schemaLine($arrDataLineE);
				if ( !($arrLineE["valid"]) )
					return $arrLineE = array('sStatus' => 'danger', 'sMessage' => 'Problemas al generar contenido Linea E', 'sContent' => $arrLineE["line"]);
				$arrCadenaFESUNAT .= "\n" . $arrLineE["line"];
			}

			$sTipoAccionDocumento = "Registrado";
		}
		$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Contenido del documento para SUNAT, ' . $sTipoAccionDocumento . ' correctamente', 'sContent' => $arrCadenaFESUNAT);
		return $arrResponse;
	}
	/* /. Facturación Electrónica y Representación Impresa */
}
