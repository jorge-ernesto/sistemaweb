<?php
require '../valida_sess.php';
require '../config.php';
require("/sistemaweb/helper.php");

require("facturacion/c_facturas_venta.php");

if (!isset($_REQUEST['action'])) {
	$_REQUEST['page'] = '1';//Para que funcione el paginador, siempre se inicia en la primera página 1

	$objHelper = new HelperClass();
	$arrDataHelper = array(
		'arrAlmacenes' => $objHelper->getWareHouse(),
		'dInicial' => $objHelper->getAllDateFormat('fecha_inicial_dmy'),
		'dFinal' => $objHelper->getAllDateFormat('fecha_dmy'),
		'iTipoDocumento' => '',
		'iSerieDocumento' => '',
		'iNumeroDocumento' => '',
		'iEstado' => '',
		'iIdCliente' => '',
		'sNombreCliente' => '',
		'arrDocumentos' => $objHelper->getGeneralTable('08', "AND tab_car_03!=''", "tab_descripcion", ""),
	);
	$controllerSalesInvoice = new controllerSalesInvoice();
	$controllerSalesInvoice->index($arrDataHelper);
} else {
	if (isset($_REQUEST['action'])) {
		$controllerSalesInvoice = new controllerSalesInvoice();

		$objHelper = new HelperClass();
		$arrDataHelper = array(
			'dFinal' => $objHelper->getAllDateFormat('fecha_dmy'),
			'arrDocumentos' => $objHelper->getGeneralTable('08', "AND tab_car_03 != ''", "tab_descripcion", ""),
			'arrFormaPago' => $objHelper->getGeneralTable('05', "", "tab_elemento", ""),
			'arrDiasPago' => $objHelper->getGeneralTable('96', "", "tab_elemento", "tab_num_01"),
			'arrMonedas' => $objHelper->getGeneralTable('04', "", "tab_elemento", ""),
			'fTipoCambioVenta' => $objHelper->getExchangeRate($objHelper->getAllDateFormat('fecha_ymd')),
			'fImpuesto' => $objHelper->getTax(),
			'iTipoImpuesto' => $objHelper->getParametersTable('taxoptional'),
		);

		switch ($_REQUEST['action']) {
			case 'verify-ocs_ebi_provider'://Verificar que la empresa pueda enviar documentos electrónicos a SUNAT
				echo json_encode($objHelper->getParametersTable($_REQUEST['sEBIProvider']));
				break;

			case 'search-sales_invoice':
				//listar registros de documentos de ventas manuales
				$controllerSalesInvoice->searchSalesInvoice($_REQUEST);
				break;

			case 'add':
				//Agregar documento de ventas manuales
				$controllerSalesInvoice->page_add_sales_invoice($arrDataHelper, "Agregar", '');
				break;

			case 'edit':
				//Editar documento de ventas manuales
				$controllerSalesInvoice->page_add_sales_invoice($arrDataHelper, "Editar", $_GET);
				break;

			case 'pdf_representacion_interna_fe_sunat':
				//Editar documento de ventas manuales
				$arrMontoMinimo = $objHelper->getParametersTable('max_unidentified');
				$arrImpuesto = $objHelper->getTax();
				$arrDataHelper = array(
					'fMontoMinimo' => $arrMontoMinimo["arrData"][0]["par_valor"],
					'fImpuesto' => $arrImpuesto['arrData'],
					'date_ymd_today' => $objHelper->getAllDateFormat('fecha_ymd'),
				);
				
				/*** Verificar envio de decimales para precio unitario y cantidad en el detalle de las facturass ***/
				error_log('*** Etapa 1 ***');
				error_log( json_encode( array($_REQUEST, $arrDataHelper) ) );
				// die();
				/***/

				$controllerSalesInvoice->generate_printed_representation_pdf_FE_sunat($_REQUEST, $arrDataHelper);
				break;

			case 'save':
				//Guardar documentos de ventas manuales
				// error_log("****** Analisis para guardar documentos, etapa 5 ******");
        		// error_log(json_encode(array($_POST, $objHelper->getUserIP(), $objHelper->getAllDateFormat('hora'))));
				$controllerSalesInvoice->save_sales_invoice($_POST, $objHelper->getUserIP(), $objHelper->getAllDateFormat('hora'));
				break;

			case 'save_complementary':
				//Modificar complemento de documento de venta manual
				$controllerSalesInvoice->save_sales_invoice_complementary($_POST, $objHelper->getUserIP());
				break;

			case 'modify':
				//Modificar documentos de ventas manuales
				$controllerSalesInvoice->modify_sales_invoice($_POST, $objHelper->getUserIP(), $objHelper->getAllDateFormat('hora'));
				break;

			case 'cancel':
				//Anular documento(s) de ventas manuales
				//2 (anulado) ó 5 (registrado) días máximo para envio de FE, contando desde la fecha que se emitió
				$arrMontoMinimo = $objHelper->getParametersTable('max_unidentified');
				$arrImpuesto = $objHelper->getTax();
				$arrDataHelper = array(
					'fMontoMinimo' => $arrMontoMinimo["arrData"][0]["par_valor"],
					'fImpuesto' => $arrImpuesto['arrData'],
					'date_ymd_today' => $objHelper->getAllDateFormat('fecha_ymd'),
				);
				$controllerSalesInvoice->cancel_or_delete_sales_invoice($_POST, $arrDataHelper);
				break;

			case 'delete'://Eliminar
				//Eliminar documento(s) de ventas manuales
				$controllerSalesInvoice->cancel_or_delete_sales_invoice($_POST, "");
				break;

			case 'search-reference-sales_invoice':
				//verificar si existe documento de referencia
				$controllerSalesInvoice->verify_reference_sales_invoice_document($_POST);
				break;

			// FV Agregar - Obtener Datos
			case 'search-sales_serial':
				//obtener series de documentos de ventas manuales
				$controllerSalesInvoice->search_sales_serial($_POST);
				break;

			case 'search-number_by_sale_serial':
				//obtener número correlativo de ventas manuales por serie
				$controllerSalesInvoice->search_number_by_sale_serial($_POST);
				break;

			case 'search-customer_price_list':
				//obtener lista de precio(s) de cliente
				$controllerSalesInvoice->search_customer_price_list($_POST);
				break;

			case 'search-customer_credit_days':
				//obtener días de crédito de cliente
				$controllerSalesInvoice->search_customer_credit_days($_POST);
				break;

			case 'search-other_customer_fields':
				//obtener lista de precio(s) de cliente
				$controllerSalesInvoice->search_other_customer_fields($_POST);
				break;

			case 'search-item_sale_price':
				//obtener precio de venta de item
				$controllerSalesInvoice->search_item_sale_price($_POST);
				break;

			case 'search-other_item_fields':
				//obtener lista de precio(s) de cliente
				$controllerSalesInvoice->search_other_item_fields($_POST);
				break;

			case 'search-verify_register':
				//Verificar si existe documento manual de venta
				$controllerSalesInvoice->verify_register($_POST);
				break;

			// Validaciones de FE
			case 'search-verify_validations_FE':
				//2 (anulado) ó 5 (registrado) días máximo para envio de FE, contando desde la fecha que se emitió
				$arrDataHelper = array(
					'date_ymd_today' => $objHelper->getAllDateFormat('fecha_ymd'),
				);
				// error_log("****** Analisis para guardar documentos, etapa 3 ******");
				// error_log(json_encode(array($_POST, $arrDataHelper)));
				$controllerSalesInvoice->verify_validations_FE($_POST, $arrDataHelper);
				break;
			// ./ Validaciones de FE

			// Obtener Datos de la Clase Helper
			// Validaciones
			case 'search-verify_consolidation':
				//Agregar documentos de ventas manuales
				echo json_encode($objHelper->verify_consolidation($_POST));
				break;

			case 'search-verify_several_types_validations':
				$arrMontoMinimo = $objHelper->getParametersTable('max_unidentified');
				$arrImpuesto = $objHelper->getTax();
				$arrDataHelper = array(
					'fMontoMinimo' => $arrMontoMinimo["arrData"][0]["par_valor"],
					'fImpuesto' => $arrImpuesto['arrData'],
				);
				// error_log("****** Analisis para guardar documentos, etapa 4 ******");
				// error_log(json_encode(array($_POST, $arrDataHelper)));
				$controllerSalesInvoice->verify_several_types_validations($_POST, $arrDataHelper);
				break;
			// ./ Validaciones
			// ./ Class Helper
			// /. FV Agregar - Obtener Datos
		}
	}
}
