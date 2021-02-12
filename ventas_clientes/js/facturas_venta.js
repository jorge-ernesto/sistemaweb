var url;
var url_cancel = '/sistemaweb/ventas_clientes/facturas_venta.php';
var block_loding_modal = $('<div class="block-loading" />');

$(document).ready(function() {
	/*
	Name function: searchSalesInvoice
	Parameters: int = number page - example = 1;
	*/
	$( document ).on('click', '.pagination-previous', function(event) {
		var iPagePrev = $( '#pageActual' ).val();
		if(parseInt(iPagePrev) > 1) {
			iPagePrev--;
			searchSalesInvoice(iPagePrev);
		}
	})

	$( document ).on('click', '.pagination-link', function(event) {
		var iPage = $(this).attr( "data-page" );
		searchSalesInvoice(iPage);
	})

	$( document ).on('click', '.pagination-next', function(event) {
		var iPageNext = $( '#pageActual' ).val();
		var iPageQuantity = $( '#cantidadPage' ).val();

		if(parseInt(iPageNext) < parseInt(iPageQuantity)) {
			iPageNext++;
			searchSalesInvoice(iPageNext);
		}
	})
	// /. Paginador

	// Obtener parametros de la url
	var getUrlParameter = function getUrlParameter(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;

	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');

	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};

	$( document ).keyup(function(event){
		// Invocamos function getUrlParameter para verificar el parámetro que necesitamos
		if ( getUrlParameter('action') !== undefined && getUrlParameter('action') == 'add' ) {
			if(event.which == 13){// ENTER = Guardar
				if ( !$( '#btn-save-sale_invoice' ).is(':disabled') ){ //Solo si el botón esta activo ingresa a guardar
					saveSalesInvoice();	
				}
			}
		}

		if ( getUrlParameter('action') !== undefined && (getUrlParameter('action') == 'add' || getUrlParameter('action') == 'edit') ) {
			if(event.which == 27){// ESC = Cancelar
				var arrCancel = {
					sAction : getUrlParameter('action'),
					sURL : url_cancel,
				};
				cancelInvoiceSale(arrCancel);
			}
		}
	});

	$( "#txt-fe_emision" ).datepicker({
		changeMonth: true,
		changeYear: true,
		autoclose : true,
		maxDate: $("#txt-fe_vencimiento").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_vencimiento").datepicker("option", "minDate", selectedDate);
		},
		todayHighlight: true
	});

	//Ocultar elementos al cargar Facturas de Venta
	$( '.div-referencia_documento' ).hide();
	$( '.div-fecha_credito' ).hide();
	$( '.div-complementarios' ).hide();
	$( '.div-message-sale_invoice_referencia' ).hide();
	$( '.div-detraccion' ).hide();
	$( '#div-sales_invoice_detail' ).hide();
	$( '#btn-save-sale_invoice' ).prop( "disabled", true );

	$( '#btn-html-sales_invoice' ).click(function() {
		searchSalesInvoice(1);
	});

  	$( document ).on('change', '.cbo-acciones', function(){
		// elemento = $(this);
		// console.log(elemento.val());
		// return;

		var row = $(this).parents("tr");
		var iEstadoDocumento = $(this).val();
		var sAction = row.find('.cbo-acciones option:selected').data('action');
		var iTipoFormaVales = row.find('.cbo-acciones option:selected').data('nu_tipo_forma_vales');//1 = Sin soltar vales y 2 = Soltando vales
	    var iCodigoAlmacen = $.trim(row.find('.cbo-acciones option:selected').data('nu_codigo_almacen'));
	    var iTipoDocumento = $.trim(row.find('.cbo-acciones option:selected').data('nu_tipo_documento'));
	    var sSerieDocumento = $.trim(row.find('.cbo-acciones option:selected').data('no_serie_documento'));
	    var iNumeroDocumento = $.trim(row.find('.cbo-acciones option:selected').data('nu_numero_documento'));
	    var dFechaEmision = $.trim(row.find('.cbo-acciones option:selected').data('fe_emision'));
	    var iNumeroLiquidacion = $.trim(row.find('.cbo-acciones option:selected').data('nu_liquidacion'));
	    var iIdCliente = $.trim(row.find('.cbo-acciones option:selected').data('nu_codigo_cliente'));

	    var sMessage = '';
	    var sTipoFormaVales = (iTipoFormaVales == '1' ? 'Sin soltar vales' : 'Soltando vales');
	    if ( sAction == 'anular' ) {
			sMessage = "¿Deseas " + sAction + " el documento (" + sTipoFormaVales + ")?";
	    	if (confirm(sMessage)) {
				// Verificar consolidación
				// Parametros (día, turno y fecha)
				var params = {
					action: 'search-verify_consolidation',
					dFecha: dFechaEmision,
					iTurno: 0,
					iAlmacen: iCodigoAlmacen,
				}

				url = '/sistemaweb/ventas_clientes/facturas_venta.php';

				$.post( url, params, function( response ) {
					if (response.sStatus == 'success') {
						var params = {
							action: 'cancel',
							iCodigoAlmacen: iCodigoAlmacen,
							iTipoDocumento: iTipoDocumento,
							sSerieDocumento: sSerieDocumento,
							iNumeroDocumento: iNumeroDocumento,
							dFechaEmision: dFechaEmision,
							iNumeroLiquidacion: iNumeroLiquidacion,
							iTipoFormaVales: parseInt(iTipoFormaVales),
							iIdCliente: iIdCliente,
							sAction : sAction,
							iEstadoDocumento : iEstadoDocumento,
						}

						url = '/sistemaweb/ventas_clientes/facturas_venta.php';

						$.post( url, params, function( response ) {
							if (response.sStatus == 'success') {
								alert(response.sMessage);
								searchSalesInvoice(1);
							} else if(response.sStatus=='warning') {
								alert(response.sMessage);
								location.reload();
							} else {
								alert(response.sMessage);
							}
						}, "json");
					} else if (response.sStatus == 'warning') {
						alert(response.sMessage);
					} else {
						alert(response.sMessage);
					}
				}, "json");
			}// /. Preguntar al anular
		} else if ( sAction == 'eliminar' ) {
			sMessage = "¿Deseas " + sAction + " el documento (" + sTipoFormaVales + ")?";
	    	if (confirm(sMessage)) {
				// Verificar consolidación
				// Parametros (día, turno y fecha)
				var params = {
					action: 'search-verify_consolidation',
					dFecha: dFechaEmision,
					iTurno: 0,
					iAlmacen: iCodigoAlmacen,
				}

				url = '/sistemaweb/ventas_clientes/facturas_venta.php';

				$.post( url, params, function( response ) {
					if (response.sStatus == 'success') {
						var params = {
							action: 'delete',
							iCodigoAlmacen: iCodigoAlmacen,
							iTipoDocumento: iTipoDocumento,
							sSerieDocumento: sSerieDocumento,
							iNumeroDocumento: iNumeroDocumento,
							dFechaEmision: dFechaEmision,
							iNumeroLiquidacion: iNumeroLiquidacion,
							iTipoFormaVales: parseInt(iTipoFormaVales),
							iIdCliente: iIdCliente,
							sAction : sAction,
							iEstadoDocumento : iEstadoDocumento,
						}

						url = '/sistemaweb/ventas_clientes/facturas_venta.php';

						$.post( url, params, function( response ) {
							if (response.sStatus == 'success') {
								alert(response.sMessage);
								searchSalesInvoice(1);
							} else {
								alert(response.sMessage);
							}
						}, "json");
					} else if (response.sStatus == 'warning') {
						alert(response.sMessage);
					} else {
						alert(response.sMessage);
					}
				}, "json");
			}// /. Preguntar al eliminar
		} else if ( sAction == 'editar' ) {
			window.location.href = '/sistemaweb/ventas_clientes/facturas_venta.php?action=edit&iCodigoAlmacen=' + iCodigoAlmacen + '&iTipoDocumento=' + iTipoDocumento + '&sSerieDocumento=' + sSerieDocumento + '&iNumeroDocumento=' + iNumeroDocumento + '&dFechaEmision=' + dFechaEmision + '&iIdCliente=' + iIdCliente;
		} else if ( sAction == 'representacion_interna_pdf_sunat' ) {
			window.open('/sistemaweb/ventas_clientes/facturas_venta.php?action=pdf_representacion_interna_fe_sunat&iCodigoAlmacen=' + iCodigoAlmacen + '&iTipoDocumento=' + iTipoDocumento + '&sSerieDocumento=' + sSerieDocumento + '&iNumeroDocumento=' + iNumeroDocumento + '&dFechaEmision=' + dFechaEmision + '&iIdCliente=' + iIdCliente + '&iNumeroLiquidacion=' + iNumeroLiquidacion + '&sAction=' + sAction, '_blank');
		} else if ( sAction == 'enviar_sunat' ) {
			url = '/sistemaweb/ventas_clientes/facturas_venta.php';
			sMessage = "¿Deseas enviar el documento a SUNAT?";
	    	if (confirm(sMessage)) {
   			
				var params = {
					action: 'verify-ocs_ebi_provider',
					sEBIProvider: 'ebiProvider',				
				}
				$.post( url, params, function( response ) {
					if (response.sStatus == 'success') {
						var params = {
							action: 'pdf_representacion_interna_fe_sunat',
							iCodigoAlmacen: iCodigoAlmacen,
							iTipoDocumento: iTipoDocumento,
							sSerieDocumento: sSerieDocumento,
							iNumeroDocumento: iNumeroDocumento,
							dFechaEmision: dFechaEmision,
							iNumeroLiquidacion: iNumeroLiquidacion,
							iIdCliente: iIdCliente,
							sAction : sAction,
							iEstadoDocumento : iEstadoDocumento,
						}

						$.post( url, params, function( response ) {
							// console.log(response);
							if (response.sStatus=='success') {
								alert(response.sMessage);
								searchSalesInvoice(1);
							} else if(response.sStatus=='warning') {
								alert(response.sMessage);
								location.reload();
							} else {
								alert(response.sMessage);
							}
						}, "json");
					} else {
						alert(response.sMessage);
					}
				}, "json");
			}// /. Preguntar al enviar documento a SUNAT
		} else if ( sAction == 'extornar' ) { //Boton extornar
			sMessage = "¿Deseas " + sAction + " el documento?";
	    	if (confirm(sMessage)) {
				var iEstadoDocumento = $(this).val();
				var sAction = row.find('.cbo-acciones option:selected').data('action');
				var iTipoFormaVales = row.find('.cbo-acciones option:selected').data('nu_tipo_forma_vales');//1 = Sin soltar vales y 2 = Soltando vales
				var iCodigoAlmacen = $.trim(row.find('.cbo-acciones option:selected').data('nu_codigo_almacen'));
				var iTipoDocumento = $.trim(row.find('.cbo-acciones option:selected').data('nu_tipo_documento'));
				var sSerieDocumento = $.trim(row.find('.cbo-acciones option:selected').data('no_serie_documento'));
				var iNumeroDocumento = $.trim(row.find('.cbo-acciones option:selected').data('nu_numero_documento'));
				var dFechaEmision = $.trim(row.find('.cbo-acciones option:selected').data('fe_emision'));
				var iNumeroLiquidacion = $.trim(row.find('.cbo-acciones option:selected').data('nu_liquidacion'));
				var iIdCliente = $.trim(row.find('.cbo-acciones option:selected').data('nu_codigo_cliente'));

				console.log("iEstadoDocumento: ", iEstadoDocumento);
				console.log("sAction: ", sAction);
				console.log("iTipoFormaVales: ", iTipoFormaVales);
				console.log("iCodigoAlmacen: ", iCodigoAlmacen);
				console.log("iTipoDocumento: ", iTipoDocumento);
				console.log("sSerieDocumento: ", sSerieDocumento);
				console.log("iNumeroDocumento: ", iNumeroDocumento);
				console.log("dFechaEmision: ", dFechaEmision);
				console.log("iNumeroLiquidacion: ", iNumeroLiquidacion);
				console.log("iIdCliente: ", iIdCliente);				
			}
		}
	});

  	/** Agregar **/
	$( '#btn-add-sale_invoice' ).click(function() {
		window.location = '/sistemaweb/ventas_clientes/facturas_venta.php?action=add';
	});

	$( '#cbo-filtro-tipo_documento' ).change(function() {
		$( "#chk-activar_complemento" ).prop( "checked", false );
		$( '.div-complementarios' ).hide();
		$( '.div-referencia_documento' ).hide();
		if ( $(this).val() == '20' || $(this).val() == '11' ) {
			$( "#chk-activar_complemento" ).prop( "checked", true );
			$( '.div-complementarios' ).show();
			$( '.div-referencia_documento' ).show();
		}
	});

	$( '#cbo-filtro-serie_documento' ).change(function() {
		searchNumberBySaleSerial();
	});

	$( '#cbo-add-forma_pago' ).change(function() {
		var iCredito = $(this).val();
		$( '.div-fecha_credito' ).hide();

		$( "#cbo-add-anticipado" ).val("N");
		$( "#cbo-add-anticipado" ).prop( "disabled", false );

		$( "#cbo-add-credito" ).val("N");
		$( "#cbo-add-credito" ).prop( "disabled", false );

		$( "#cbo-add-transferencia_gratuita" ).prop( "disabled", false );

		if ( iCredito == '06' ) {
		 	if ( $( '#txt-lista_precio' ).val().length === 0 ) {
		 		$( "#cbo-add-forma_pago option[value='']" ).remove();
				$( "#cbo-add-forma_pago" ).append( '<option value="">- SELECCIONAR -</option>' );
				$( "#cbo-add-forma_pago" ).val("");

				alert( "Seleccionar primero el cliente y luego F. Pago" );
			} else {
				$( '.div-fecha_credito' ).show();

				$( "#cbo-add-anticipado" ).val("N");
				$( "#cbo-add-anticipado" ).prop( "disabled", true );

				$( "#cbo-add-credito" ).val("S");
				$( "#cbo-add-credito" ).prop( "disabled", true );

				// Calcular nueva fecha de vencimiento según los días configurados en el módulo de Clientes
				calculateDuoDate();

			    //Deshabilitar gratuita cuando sea crédito o anticipo
				$( "#cbo-add-transferencia_gratuita" ).val("N");
				$( "#cbo-add-transferencia_gratuita" ).prop( "disabled", true );
			}
		}
	});

	$( '#cbo-add-anticipado' ).change(function() {
		$( '.help' ).empty();
	    //Deshabilitar gratuita cuando sea crédito o anticipo
		$( "#cbo-add-transferencia_gratuita" ).prop( "disabled", false );
		if ( $(this).val() == 'S' ){//Activamos anticipo SI
			$( "#cbo-add-transferencia_gratuita" ).val("N");
			$( "#cbo-add-transferencia_gratuita" ).prop( "disabled", true );
			if ( $( '#hidden-filtro-cliente-anticipo' ).val().length > 0 ) {
				if ( $(this).val() != $( '#hidden-filtro-cliente-anticipo' ).val() ) {//Si seleccionamos ANTICIPO = SI el cliente también debe de ser de tipo ANTICIPADO
					$( "#hidden-filtro-cliente-id" ).val( '' );
					$( "#hidden-filtro-cliente-ruc" ).val( '' );
					$( "#hidden-filtro-cliente-direccion" ).val( '' );
					$( "#hidden-filtro-cliente-anticipo" ).val( '' );
					$( "#txt-filtro-cliente-nombre" ).closest('.form-group').find('.help').html('El Cliente <b>' + $.trim($( "#txt-filtro-cliente-nombre" ).val()) + '</b> no puede tener anticipos');
					$( "#txt-filtro-cliente-nombre" ).val( '' );
				}
			}
		}
	})

	$( '#cbo-add-dias_credito' ).change(function() {
		// Calcular nueva fecha de vencimiento según los días configurados en el módulo de Clientes
		calculateDuoDate();
	});

	$( '#cbo-add-transferencia_gratuita' ).change(function() {
		$( "#txt-descuento" ).prop( "disabled", false );
		if ( $( '#cbo-add-transferencia_gratuita' ).val() == 'S' )
			$( "#txt-descuento" ).prop( "disabled", true );
	});

	$( '#cbo-filtro-referencia-tipo_documento' ).change(function() {
		searchSalesSerialReference();
	});

	$( '#btn-search-reference-sales_invoice' ).click(function() {
		$( '.help' ).empty();
		if ( $( '#cbo-filtro-referencia-tipo_documento' ).val() == 0){
			$( '#cbo-filtro-referencia-tipo_documento' ).closest('.form-group').find('.help').html('Seleccionar tipo');
		} else if ( $( '#cbo-filtro-referencia-serie_documento' ).val() == 0){
			$( '#cbo-filtro-referencia-serie_documento' ).closest('.form-group').find('.help').html('Seleccionar serie');
		} else if ( $( '#cbo-filtro-serie_documento' ).val().substr(0,1) !== $( '#cbo-filtro-referencia-serie_documento' ).val().substr(0,1) ) {
			$( '#cbo-filtro-serie_documento' ).closest('.form-group').find('.help').html('La <b>(primera letra)</b> de ambas series deben coincidir ');
			$( '#cbo-filtro-referencia-serie_documento' ).closest('.form-group').find('.help').html('La <b>(primera letra)</b> de ambas series deben coincidir ');
		} else if ( $( '#txt-add-referencia-numero_documento' ).val().length == 0){
			$( '#txt-add-referencia-numero_documento' ).closest('.form-group').find('.help').html('Ingresar número');
		} else if ( $( '#hidden-filtro-cliente-ruc' ).val().length == 0 || $( '#hidden-filtro-cliente-id' ).val().length == 0 || $( '#txt-filtro-cliente-nombre' ).val().length == 0){
			$( '#txt-filtro-cliente-nombre' ).closest('.form-group').find('.help').html('Seleccionar cliente');
		} else {
			$( '#btn-search-reference-sales_invoice' ).attr('disabled', true);
			$( '#btn-search-reference-sales_invoice' ).addClass('is-loading');

			var params = {
				action: 'search-reference-sales_invoice',
				dFechaEmision: sTypeDate('fecha_dmy', $( '#txt-referencia-fe_emision' ).val(), '/'),
				iTipoDocumento: $( '#cbo-filtro-referencia-tipo_documento' ).val(),
				sSerieDocumento: $.trim($( '#cbo-filtro-referencia-serie_documento' ).val()),
				iNumeroDocumento: $.trim($( '#txt-add-referencia-numero_documento' ).val()),
				iIdCliente: $.trim($( '#hidden-filtro-cliente-id' ).val()),
				iRucCliente: $.trim($( '#hidden-filtro-cliente-ruc' ).val()),
			}

			url = '/sistemaweb/ventas_clientes/facturas_venta.php';
				
			$.post( url, params, function( response ) {
				$( '.div-message-sale_invoice_referencia' ).removeClass( 'is-info is-danger is-success is-warning' );
				$( '.div-message-sale_invoice_referencia' ).show();

				if (response.sStatus === 'success') {
					$( '.div-message-sale_invoice_referencia' ).addClass( 'is-info' );
					$( '.div-message-sale_invoice_referencia' ).html( '<b>' + response.sMessage + '</b>' );
					$( '.div-message-sale_invoice_referencia' ).delay(1200).hide(1000);
				} else {
					$( '.div-message-sale_invoice_referencia' ).addClass( 'is-' + response.sStatus );
					$( '.div-message-sale_invoice_referencia' ).html( response.sMessage );
					$( '.div-message-sale_invoice_referencia' ).delay(1200).hide(1000);
				}
				$( '#btn-search-reference-sales_invoice' ).removeClass('is-loading');
				$( '#btn-search-reference-sales_invoice' ).attr('disabled', false);
			}, "json");
		}
	});

	$( '#cbo-add-detraccion' ).change(function(){
		$( '.div-detraccion' ).hide();
		if ($(this).val() === 'S')
			$( '.div-detraccion' ).show();
	})

	$( '#btn-add-product_detail' ).click(function() {
		$( '.help' ).empty();

		var $iIdItem = $( '#hidden-add-item-id' );
		var $sNombreItem = $( '#txt-add-item-nombre' );
		var $fCantidad = $( '#txt-cantidad' );
		var $fPrecioVenta = $( '#txt-precio_venta' );
		var $fSubtotal = $( '#txt-subtotal' );
		var $fImpuesto = $( '#txt-igv' );
		var $fDescuento = $( '#txt-descuento' );
		var $fTotal = $( '#txt-total' );
		var $iCodigoTipoPlu = $( '#hidden-codigo_tipo_plu' );
		var $iCodigoImpuestoItem = $( '#hidden-codigo_impuesto_item' );

		bEstadoValidacion = validatePreviousDocumentToSave();

		if (bEstadoValidacion) {
			if ( $iIdItem.val().length == 0 || $sNombreItem.val().length == 0){
				$( $sNombreItem ).closest('.form-group').find('.help').html('Seleccionar item');
				bEstadoValidacion = false;

				scrollToError($iIdItem);
			} else if ( $fCantidad.val().length == 0 ){
				$( $fCantidad ).closest('.form-group').find('.help').html('Ingresar cantidad');
				bEstadoValidacion = false;

				scrollToError($fCantidad);
			} else if ( $fCantidad.val() <= 0 ){
				$( $fCantidad ).closest('.form-group').find('.help').html('La cantidad debe ser mayor a 0');
				bEstadoValidacion = false;

				scrollToError($fCantidad);
			} else if ( $fPrecioVenta.val().length == 0 ){
				$( $fPrecioVenta ).closest('.form-group').find('.help').html('Ingresar precio venta');
				bEstadoValidacion = false;

				scrollToError($fPrecioVenta);
			} else if ( $fPrecioVenta.val() <= 0 ){
				$( $fPrecioVenta ).closest('.form-group').find('.help').html('El precio de venta debe ser mayor a 0');
				bEstadoValidacion = false;

				scrollToError($fPrecioVenta);
			} else if ( $fTotal.val().length == 0 ){
				$( $fTotal ).closest('.form-group').find('.help').html('Ingresar total');
				bEstadoValidacion = false;

				scrollToError($fTotal);
			} else if ( $fTotal.val() <= 0 ){
				$( $fTotal ).closest('.form-group').find('.help').html('El total debe ser mayor a 0');
				bEstadoValidacion = false;

				scrollToError($fTotal);
			} else if ( $( "#cbo-add-exonerado" ).val() == 'S' && $iCodigoImpuestoItem.val().length == 0 ){// Exo = Si e Inafecta = Si
				$( $( "#cbo-add-exonerado" ) ).closest('.form-group').find('.help').html('Si el <b>item</b> es <b>INAFECTO</b>, <b>EXONERADA</b> debe ser NO');
				bEstadoValidacion = false;

				scrollToError( $( "#cbo-add-exonerado" ) );
			} else if (bEstadoValidacion) {
				//Bloquear tipos de afectación tributaria
				$( '#cbo-add-exonerado' ).prop( "disabled", true );
				$( '#cbo-add-transferencia_gratuita' ).prop( "disabled", true );

				// Verificar tipo de impuesto
				$fImpuestoItem = $fImpuesto.val();
				$fSubTotalItem = $fSubtotal.val();
				$sEstadoInafecto = "N";
				if ($iCodigoImpuestoItem.val().length == 0)
					$sEstadoInafecto = "S";

				if ( 
					$( '#cbo-add-exonerado' ).val() == 'N' &&
					$( '#cbo-add-transferencia_gratuita' ).val() == 'N' &&
					$sEstadoInafecto == "N"
				) {// Gravadas
					$fImpuestoItem = $fImpuesto.val();
					$fSubTotalItem = $fSubtotal.val();
				} else if (
					$( '#cbo-add-exonerado' ).val() == 'S' &&
					$( '#cbo-add-transferencia_gratuita' ).val() == 'N' &&
					$sEstadoInafecto == "N"
				) {// exonerada
					$fImpuestoItem = 0.00;
					$fSubTotalItem = $fTotal.val();
				} else if (
					$( '#cbo-add-exonerado' ).val() == 'N' &&
					$( '#cbo-add-transferencia_gratuita' ).val() == 'S' &&
					$sEstadoInafecto == "N"
				) {//gratuita
					$fSubTotalItem = 0.00;
				} else if (
					$( '#cbo-add-exonerado' ).val() == 'S' &&
					$( '#cbo-add-transferencia_gratuita' ).val() == 'S' &&
					$sEstadoInafecto == "N"
				) {// exonerada + gratuita
					$fImpuestoItem = 0.00;
					$fSubTotalItem = 0.00;
				} else if (
					$( '#cbo-add-exonerado' ).val() == 'N' &&
					$( '#cbo-add-transferencia_gratuita' ).val() == 'N' &&
					$sEstadoInafecto == "S"
				) { // Inafectas
					$fImpuestoItem = 0.00;
					$fSubTotalItem = $fTotal.val();
				}  else if (
					$( '#cbo-add-exonerado' ).val() == 'N' &&
					$( '#cbo-add-transferencia_gratuita' ).val() == 'S' &&
					$sEstadoInafecto == "S"
				) { // Inafectas + gratuita
					$fImpuestoItem = 0.00;
					$fSubTotalItem = $fTotal.val();
				}
				// /. Verificar tipo de impuesto

				//Consultar si valido stock, en el antiguo programa no lo hacia.
				var table_sales_invoice_detail =
				"<tr id='tr_detalle_producto" + $iIdItem.val() + "'>"
					+"<td style='display:none;' class='text-left'>" + $iIdItem.val() + "</td>"
					+"<td class='text-left'>" + $sNombreItem.val() + "</td>"
					+"<td class='text-right'>" + $fCantidad.val() + "</td>"
					+"<td class='text-right'>" + $fPrecioVenta.val() + "</td>"
					+"<td class='text-right'>" + $fSubTotalItem + "</td>"
					+"<td class='text-right'>" + $fImpuestoItem  + "</td>"
					+"<td class='text-right'>" + $fDescuento.val() + "</td>"
					+"<td class='text-right'>" + $fTotal.val() + "</td>"
					+"<td style='display:none;' class='text-right'>" + $iCodigoTipoPlu.val() + "</td>"
					+"<td style='display:none;' class='text-right'>" + $iCodigoImpuestoItem.val() + "</td>"
					+"<td class='text-center'><button type='button' id='btn-delete-sale_invoice_detail_item' class='button is-danger is-small icon-size btn-danger'><span class='icon is-small'><i class='fa fa-trash'></i></span></button></td>"
				+ "</tr>";

				if( isExistItem($iIdItem.val()) ){
					$( '#txt-add-item-nombre' ).closest('.form-group').find('.help').html('Ya existe item <b>' + $sNombreItem.val() + '</b>');
					$( '#txt-add-item-nombre' ).focus();
				} else {
					$( '#div-sales_invoice_detail' ).show();
					$( '#table-sales_invoice_detail >tbody' ).append(table_sales_invoice_detail);

					//Limpiar item
					$iIdItem.val( '' );
					$sNombreItem.val( '' );
					$fCantidad.val( '' );
					$fPrecioVenta.val( '' );
					$fSubtotal.val( '' );
					$( '#hidden-subtotal' ).val( '' );
					$fImpuesto.val( '' );
					$fDescuento.val( '' );
					$fTotal.val( '' );

					//Ubicar en nombre de item
					$( $sNombreItem ).focus();

					//Calcular totales
					calcAmountsSalesInvoice();

					//Activar button guardar factura de venta
					$( '#btn-save-sale_invoice' ).prop( "disabled", false );
				}
			} // ./ Validacion por linea de item
		} // ./ Validacion cabecera
	});

	$( '#table-sales_invoice_detail tbody' ).on('click', '#btn-delete-sale_invoice_detail_item', function(){
    	$(this).closest('tr').remove();

    	calcAmountsSalesInvoice();

		$( '#btn-save-sale_invoice' ).prop( "disabled", false );
	    if ($( '#table-sales_invoice_detail > tbody > tr' ).length == 0){
	    	//Activar Tipos de afectación tributaria
			$( '#cbo-add-exonerado' ).prop( "disabled", false );
			$( '#cbo-add-transferencia_gratuita' ).prop( "disabled", false );

			$( '#div-sales_invoice_detail' ).hide();
			$( '#btn-save-sale_invoice' ).prop( "disabled", true );
	    }
    })

	$( '#btn-save-sale_invoice' ).click(function() {
		saveSalesInvoice();
	});
	/** Fin Agregar **/

	/** Modificar complemento **/
	$( '#btn-save-sale_invoice_complementary' ).click(function() {
		$( '.help' ).empty();
		var $fImporteDetraccion = (isNaN(parseFloat($( '#txt-detraccion-importe' ).val())) ? 0 : parseFloat($( '#txt-detraccion-importe' ).val()));

		if ( ($( '#cbo-filtro-tipo_documento' ).val() == '20' || $( '#cbo-filtro-tipo_documento' ).val() == '11') && $( '#txt-observaciones' ).val().length < 7 ){//Validar si es NC ó ND, debe tener observación
			$( '#txt-observaciones' ).closest('.form-group').find('.help').html('Ingresar observación');	    	
		} else if ( ($( '#cbo-filtro-tipo_documento' ).val() == '20' || $( '#cbo-filtro-tipo_documento' ).val() == '11') && $( '#cbo-filtro-referencia-tipo_documento' ).val() == 0 ){//Validar si es NC ó ND, seleccionar tipo de documento
			$( '#cbo-filtro-referencia-tipo_documento' ).closest('.form-group').find('.help').html('Seleccionar tipo');	    	
		} else if ( ($( '#cbo-filtro-tipo_documento' ).val() == '20' || $( '#cbo-filtro-tipo_documento' ).val() == '11') && $( '#cbo-filtro-referencia-serie_documento' ).val().length == 0 ){//Validar si es NC ó ND, seleccionar serie de documento
			$( '#cbo-filtro-referencia-serie_documento' ).closest('.form-group').find('.help').html('Seleccionar serie');	    	
		} else if ( ($( '#cbo-filtro-tipo_documento' ).val() == '20' || $( '#cbo-filtro-tipo_documento' ).val() == '11') && $( '#txt-add-referencia-numero_documento' ).val().length == 0 ){//Validar si es NC ó ND, seleccionar numero de documento
			$( '#txt-add-referencia-numero_documento' ).closest('.form-group').find('.help').html('Ingresar número');	    	
		} else if ( $( '#cbo-add-detraccion' ).val() == 'S' && $( '#txt-detraccion-nu_cuenta' ).val().length == 0 ) {//Si se activa Detracción, verificar los campos (nro. cuenta, importe, porcentaje, codigo de servicio y bien)
	    	$( '#txt-detraccion-nu_cuenta' ).closest('.form-group').find('.help').html('Ingresar número cuenta');
		} else if ( $( '#cbo-add-detraccion' ).val() == 'S' && $fImporteDetraccion <= 0 ) {
	    	$( '#txt-detraccion-importe' ).closest('.form-group').find('.help').html('Importe debe ser mayor a 0');
		} else if ( $( '#cbo-add-detraccion' ).val() == 'S' && $( '#txt-detraccion-porcentaje' ).val().length == 0 ) {
	    	$( '#txt-detraccion-porcentaje' ).closest('.form-group').find('.help').html('Ingresar porcentaje');
		} else if ( $( '#cbo-add-detraccion' ).val() == 'S' && $( '#txt-detraccion-codigo_bienes_servicios' ).val().length < 3 ) {
	    	$( '#txt-detraccion-codigo_bienes_servicios' ).closest('.form-group').find('.help').html('Ingresar código de bien / servicio');
		} else {
			saveSalesInvoiceComplementary();
		}
	});
	/** Fin Modificar complemento **/

	$( '#btn-cancel-sale_invoice' ).click(function() {
		var arrCancel = {
			sAction : getUrlParameter('action'),
			sURL : url_cancel,
		};

		cancelInvoiceSale(arrCancel);
	});
});

function calcAmountsSalesInvoice(){
	var $fSumCantidad = 0.00, $fSumSubtotal = 0.00, $fSumImpuesto = 0.00, $fSumDescuento = 0.00, $fSumTotal = 0.00, $sEstadoInafecto = "N";
	var $fSubtotal=0.00, $fTotal=0.00, $fDescuento=0.00;
	$( '#table-sales_invoice_detail > tbody > tr' ).each(function(){
		var rows = $(this);
		$fSumCantidad += parseFloat(rows.find("td:eq(2)").text());
  		$fDescuento = (isNaN(parseFloat(rows.find("td:eq(6)").text())) ? 0 : parseFloat(rows.find("td:eq(6)").text()));
  		$fSubtotal = parseFloat(rows.find("td:eq(4)").text());
  		$fImpuesto = parseFloat(rows.find("td:eq(5)").text());
  		$fTotal = parseFloat(rows.find("td:eq(7)").text());

  		if (
  			$( '#cbo-add-exonerado' ).val() == 'N' &&
			$( '#cbo-add-transferencia_gratuita' ).val() == 'N' &&
			$sEstadoInafecto == "N" &&
			$fDescuento > 0.00
		) {
			$fSubtotal = parseFloat(rows.find("td:eq(4)").text()) - parseFloat($fDescuento);
  			$fTotal = $fSubtotal.toFixed(2) * (isNaN(parseFloat($( '#txt-add-igv' ).val())) ? 1 : parseFloat($( '#txt-add-igv' ).val()));
  			$fTotal = $fTotal.toFixed(2);
  			$fImpuesto = $fTotal - $fSubtotal;
  		} else {
  			$fTotal = parseFloat(rows.find("td:eq(7)").text()) - parseFloat($fDescuento);
  			$fTotal = $fTotal.toFixed(2);
  		}

  		$fSumImpuesto += $fImpuesto;
		$fSumDescuento += $fDescuento;
		$fSumSubtotal += $fSubtotal;
		$iCodigoImpuestoItem = $.trim(rows.find("td:eq(9)").text());

		$fSumTotal+=parseFloat($fTotal);

  		if ( $iCodigoImpuestoItem.length == 0)
  			$sEstadoInafecto = "S";
	});

	$fSumSubtotal = Math.round($fSumSubtotal * 100)/100;
	$fSumImpuesto = Math.round($fSumImpuesto * 100)/100;

	if ( 
		$( '#cbo-add-exonerado' ).val() == 'N' &&
		$( '#cbo-add-transferencia_gratuita' ).val() == 'N' &&
		$sEstadoInafecto == "N"
	) {// Gravadas
    	$( '#hidden-totales-exonerada' ).val( 0 );
    	$( '#hidden-totales-inafecta' ).val( 0 );
    	$( '#hidden-totales-gravada' ).val( $fSumSubtotal );
    	$( '#hidden-totales-igv' ).val( $fSumImpuesto );
    	$( '#hidden-totales-gratuita' ).val( 0 );
    	$( '#hidden-totales-descuento' ).val( $fSumDescuento );
    	$( '#hidden-totales-total' ).val( $fSumTotal );

    	$( '#label-totales-exonerada' ).text( '0' );
    	$( '#label-totales-inafecta' ).text( '0' );
    	$( '#label-totales-gravada' ).text( $fSumSubtotal );
    	$( '#label-totales-igv' ).text( $fSumImpuesto );
    	$( '#label-totales-gratuita' ).text( '0' );
    	$( '#label-totales-descuento' ).text( $fSumDescuento );
    	$( '#label-totales-total' ).text( $fSumTotal );
	} else if (
		$( '#cbo-add-exonerado' ).val() == 'S' &&
		$( '#cbo-add-transferencia_gratuita' ).val() == 'N' &&
		$sEstadoInafecto == "N"
	) {// exonerada
    	$( '#hidden-totales-exonerada' ).val( $fSumTotal );
    	$( '#hidden-totales-inafecta' ).val( 0 );
    	$( '#hidden-totales-gravada' ).val( 0 );
    	$( '#hidden-totales-igv' ).val( 0 );
    	$( '#hidden-totales-gratuita' ).val( 0 );
    	$( '#hidden-totales-descuento' ).val( $fSumDescuento );
    	$( '#hidden-totales-total' ).val( $fSumTotal );

		$( '#label-totales-exonerada' ).text( $fSumTotal );
    	$( '#label-totales-inafecta' ).text( '0' );
    	$( '#label-totales-gravada' ).text( '0' );
    	$( '#label-totales-igv' ).text( '0' );
    	$( '#label-totales-gratuita' ).text( '0' );
    	$( '#label-totales-descuento' ).text( $fSumDescuento );
    	$( '#label-totales-total' ).text( $fSumTotal );
	} else if (
		$( '#cbo-add-exonerado' ).val() == 'N' &&
		$( '#cbo-add-transferencia_gratuita' ).val() == 'S' &&
		$sEstadoInafecto == "N"
	) {//gratuita
    	$( '#hidden-totales-exonerada' ).val( 0 );
    	$( '#hidden-totales-inafecta' ).val( 0 );
    	$( '#hidden-totales-gravada' ).val( 0 );
    	$( '#hidden-totales-igv' ).val( $fSumImpuesto );
    	$( '#hidden-totales-gratuita' ).val( $fSumTotal );
    	$( '#hidden-totales-descuento' ).val( 0 );
    	$( '#hidden-totales-total' ).val( 0 );

		$( '#label-totales-exonerada' ).text( '0' );
    	$( '#label-totales-inafecta' ).text( '0' );
    	$( '#label-totales-gravada' ).text( '0' );
    	$( '#label-totales-igv' ).text( $fSumImpuesto );
    	$( '#label-totales-gratuita' ).text( $fSumTotal );
    	$( '#label-totales-descuento' ).text( '0' );
    	$( '#label-totales-total' ).text( '0' );
	} else if (
		$( '#cbo-add-exonerado' ).val() == 'S' &&
		$( '#cbo-add-transferencia_gratuita' ).val() == 'S' &&
		$sEstadoInafecto == "N"
	) {// exonerada + gratuita
    	$( '#hidden-totales-exonerada' ).val( 0 );
    	$( '#hidden-totales-inafecta' ).val( 0 );
    	$( '#hidden-totales-gravada' ).val( 0 );
    	$( '#hidden-totales-igv' ).val( 0 );
    	$( '#hidden-totales-gratuita' ).val( $fSumTotal );
    	$( '#hidden-totales-descuento' ).val( 0 );
    	$( '#hidden-totales-total' ).val( 0 );

		$( '#label-totales-exonerada' ).text( '0' );
    	$( '#label-totales-inafecta' ).text( '0' );
    	$( '#label-totales-gravada' ).text( '0' );
    	$( '#label-totales-igv' ).text( '0' );
    	$( '#label-totales-gratuita' ).text( $fSumTotal );
    	$( '#label-totales-descuento' ).text( '0' );
    	$( '#label-totales-total' ).text( '0' );
	} else if (
		$( '#cbo-add-exonerado' ).val() == 'N' &&
		$( '#cbo-add-transferencia_gratuita' ).val() == 'N' &&
		$sEstadoInafecto == "S"
	) { // Inafectas
		$( '#hidden-totales-exonerada' ).val( 0 );
    	$( '#hidden-totales-inafecta' ).val( $fSumTotal );
    	$( '#hidden-totales-gravada' ).val( 0 );
    	$( '#hidden-totales-igv' ).val( 0 );
    	$( '#hidden-totales-gratuita' ).val( 0 );
    	$( '#hidden-totales-descuento' ).val( 0 );
    	$( '#hidden-totales-total' ).val( $fSumTotal );

		$( '#label-totales-exonerada' ).text( '0' );
    	$( '#label-totales-inafecta' ).text( $fSumTotal );
    	$( '#label-totales-gravada' ).text( '0' );
    	$( '#label-totales-igv' ).text( '0' );
    	$( '#label-totales-gratuita' ).text( '0' );
    	$( '#label-totales-descuento' ).text( '0' );
    	$( '#label-totales-total' ).text( $fSumTotal );
	}  else if (
		$( '#cbo-add-exonerado' ).val() == 'N' &&
		$( '#cbo-add-transferencia_gratuita' ).val() == 'S' &&
		$sEstadoInafecto == "S"
	) { // Inafectas + gratuita
		$( '#hidden-totales-exonerada' ).val( 0 );
    	$( '#hidden-totales-inafecta' ).val( 0 );
    	$( '#hidden-totales-gravada' ).val( 0 );
    	$( '#hidden-totales-igv' ).val( 0 );
    	$( '#hidden-totales-gratuita' ).val( $fSumTotal );
    	$( '#hidden-totales-descuento' ).val( 0 );
    	$( '#hidden-totales-total' ).val( 0 );

		$( '#label-totales-exonerada' ).text( '0' );
    	$( '#label-totales-inafecta' ).text( '0' );
    	$( '#label-totales-gravada' ).text( '0' );
    	$( '#label-totales-igv' ).text( '0' );
    	$( '#label-totales-gratuita' ).text( $fSumTotal );
    	$( '#label-totales-descuento' ).text( '0' );
    	$( '#label-totales-total' ).text( '0' );
	}
}

function isExistItem($ID_Producto){
  return Array.from($('tr[id*=tr_detalle_producto]'))
    .some(element => ($('td:nth(0)',$(element)).html()===$ID_Producto));
}

function searchNumberBySaleSerial(){
	$( '#txt-add-almacen' ).val( '' );
	$( '#txt-add-numero_documento' ).val('');
	if ( $( '#cbo-filtro-serie_documento' ).val() != '') {
		var params = {
			action: 'search-number_by_sale_serial',
			iTipoDocumento: $( '#cbo-filtro-tipo_documento' ).val(),
			iSerieDocumento: $( '#cbo-filtro-serie_documento' ).val(),
		}

		url = '/sistemaweb/ventas_clientes/facturas_venta.php';

		$.post( url, params, function( response ) {
			if (response.sStatus === 'success') {
				$( '#txt-add-numero_documento' ).val(response.rowData.nu_numero_documento);
				//Mostrar nombre de almacén
				$( '#txt-add-almacen' ).val( response.rowData.no_almacen );
			} else {
				$( '#txt-add-numero_documento' ).val( '' );
				$( '#txt-add-numero_documento' ).closest('.form-group').find('.help').html( response.sMessage );
			}
		}, "json");
	}
}

function autocompleteBridge(type) {
	if (type == 0) {// Cliente agregar
		var customers = $("#txt-filtro-cliente-nombre");
		if(customers.val() !== undefined) {
			generalAutocomplete('#txt-filtro-cliente-nombre', '#hidden-filtro-cliente-id', 'getCustomers', ['getCustomerPriceList(), getCustomerCreditDays(), getOtherCustomerFields()']);
		}
	} else if (type == 1) {
		var items = $("#txt-add-item-nombre");
		if(items.val() !== undefined) {
			generalAutocomplete('#txt-add-item-nombre', '#hidden-add-item-id', 'getProductXByCodeOrName', ['getItemSalePrice(), getOtherItemFields()']);
		}
	} else if (type == 2) {// Cliente buscar
		var customers = $("#txt-filtro-cliente-nombre");
		if(customers.val() !== undefined) {
			generalAutocomplete('#txt-filtro-cliente-nombre', '#hidden-filtro-cliente-id', 'getCustomers', ['']);
		}
	}
}

function getCustomerPriceList(){
	var iIdCliente = $.trim($("#hidden-filtro-cliente-id").val());

	var params = {
		action: 'search-customer_price_list',
		iIdCliente: iIdCliente,
	}

	url = '/sistemaweb/ventas_clientes/facturas_venta.php';

	$.post( url, params, function( response ) {
		if (response.sStatus === 'success'){
			$( '#txt-lista_precio' ).val( response.rowData.id );
			$( '#label-lista_precio' ).text( response.rowData.name );
		} else {
			$( '#label-lista_precio' ).text( response.sMessage );
		}
	}, 'JSON');
}

function getCustomerCreditDays(){
	var iIdCliente = $.trim($("#hidden-filtro-cliente-id").val());

	var params = {
		action: 'search-customer_credit_days',
		iIdCliente: iIdCliente,
	}

	url = '/sistemaweb/ventas_clientes/facturas_venta.php';

	$.post( url, params, function( response ) {
		if (response.sStatus === 'success'){
			$( "#cbo-add-dias_credito" ).val( response.arrData.id );
		} else {
			alert( response.sMessage );
		}
	}, 'JSON');
}

function getOtherCustomerFields(){
	var iIdCliente = $.trim($("#hidden-filtro-cliente-id").val());

	var params = {
		action: 'search-other_customer_fields',
		iIdCliente: iIdCliente,
	}

	url = '/sistemaweb/ventas_clientes/facturas_venta.php';

	$.post( url, params, function( response ) {
		$( '.help' ).empty();
		if (response.sStatus === 'success'){
			if ( $( '#cbo-add-anticipado' ).val() == 'N' ) {
				$( "#hidden-filtro-cliente-ruc" ).val( $.trim(response.rowData.cli_ruc) );
				$( "#hidden-filtro-cliente-direccion" ).val( $.trim(response.rowData.cli_direccion) );
				$( "#hidden-filtro-cliente-anticipo" ).val( $.trim(response.rowData.cli_anticipo) );
			} else {
				if ( $.trim(response.rowData.cli_anticipo) == 'N' ) {
					$( "#hidden-filtro-cliente-id" ).val( '' );
					$( "#hidden-filtro-cliente-ruc" ).val( '' );
					$( "#hidden-filtro-cliente-direccion" ).val( '' );
					$( "#hidden-filtro-cliente-anticipo" ).val( '' );
					$( "#txt-filtro-cliente-nombre" ).val( '' );
					$( "#txt-filtro-cliente-nombre" ).closest('.form-group').find('.help').html('El Cliente <b>' + $.trim(response.rowData.cli_razsocial) + '</b> no puede tener anticipos');
				} else {
					$( "#hidden-filtro-cliente-ruc" ).val( $.trim(response.rowData.cli_ruc) );
					$( "#hidden-filtro-cliente-direccion" ).val( $.trim(response.rowData.cli_direccion) );
					$( "#hidden-filtro-cliente-anticipo" ).val( $.trim(response.rowData.cli_anticipo) );
				}
			}	
		} else {
			alert( response.sMessage );
		}
	}, 'JSON');
}

function activeComplementary(){
	if ($( "#chk-activar_complemento" ).prop("checked") )
		$( '.div-complementarios' ).show();
	else
		$( '.div-complementarios' ).hide();
}

function searchSalesSerialReference(){
	$( '#cbo-filtro-referencia-serie_documento' ).html('<option value="" selected="selected">Seleccionar</option>');

	if ( $( '#cbo-filtro-referencia-tipo_documento' ).val() > 0) {
		var params = {
			action: 'search-sales_serial',
			iTipoDocumento: $( '#cbo-filtro-referencia-tipo_documento' ).val(),
		}

		url = '/sistemaweb/ventas_clientes/facturas_venta.php';
			
		$.post( url, params, function( response ) {
			for (var i = 0; i < response.arrData.length; i++)
				$( '#cbo-filtro-referencia-serie_documento' ).append( '<option value="' + response.arrData[i].id + '">' + response.arrData[i].id + '</option>' );
		}, "json");
	}
}

function getItemSalePrice(){
	if ($( '#txt-lista_precio' ).val().length===0) {
		$("#hidden-add-item-id").val('');
		$("#txt-add-item-nombre").val('');
		alert('Seleccionar cliente');
		$("#txt-filtro-cliente-nombre").focus();
	} else {
		var iIdItem = $.trim($("#hidden-add-item-id").val());

		var params = {
			action: 'search-item_sale_price',
			iIdItem: iIdItem,
			iIdListaPrecio: $( '#txt-lista_precio' ).val(),
		}

		url = '/sistemaweb/ventas_clientes/facturas_venta.php';

		$.post( url, params, function( response ) {
			$( '.help' ).empty();
			if (response.sStatus === 'success'){
				$( '#txt-cantidad' ).val( '' );
				$( '#txt-subtotal' ).val( '' );
				$( '#txt-igv' ).val( '' );
				$( '#txt-descuento' ).val( '' );
				$( '#txt-total' ).val( '' );
				$( '#txt-precio_venta' ).val( response.arrData.ss_precio_venta_igv_item );
			} else {
				$( '#txt-precio_venta' ).closest('.form-group').find('.help').html( response.sMessage );
			}
		}, 'JSON');
	}
}

function getOtherItemFields(){
	var iIdItem = $.trim($("#hidden-add-item-id").val());

	var params = {
		action: 'search-other_item_fields',
		iIdItem: iIdItem
	}

	url = '/sistemaweb/ventas_clientes/facturas_venta.php';

	$.post( url, params, function( response ) {
		if (response.sStatus === 'success'){
			$( '#hidden-codigo_impuesto_item' ).val( response.rowData.nu_codigo_impuesto_item );
			$( '#hidden-codigo_tipo_plu' ).val( response.rowData.nu_codigo_tipo_plu );
		} else {
			alert( response.sMessage );
		}
	}, 'JSON');
}

function searchSalesInvoice(iPage){
	$( '#div-sales_invoice' ).prepend(block_loding_modal);

	$( '#btn-html-sales_invoice' ).attr('disabled', true);
	$( '#btn-html-sales_invoice' ).addClass('is-loading');

	var params = {
		action: 'search-sales_invoice',
		iAlmacen: $( '#cbo-filtro-almacen' ).val(),
		dInicial: $( '#txt-fe_inicial' ).val(),
		dFinal: $( '#txt-fe_final' ).val(),
		iTipoDocumento: $( '#cbo-filtro-tipo_documento' ).val(),
		iSerieDocumento: $( '#cbo-filtro-serie_documento' ).val(),
		iNumeroDocumento: $( '#txt-filtro-numero_documento' ).val(),
		iEstado: $( '#cbo-filtro-estado' ).val(),
		iIdCliente: $.trim($( '#hidden-filtro-cliente-id' ).val()),
		sNombreCliente: $( '#txt-filtro-cliente-nombre' ).val(),
       	page : iPage
	}

	url = '/sistemaweb/ventas_clientes/facturas_venta.php';

	$.ajax({
		url: url,
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			$( '.div-sales_invoice' ).html(data);

			$( '#btn-html-sales_invoice' ).removeClass('is-loading');
			$( '#btn-html-sales_invoice' ).attr('disabled', false);
		}
	}, "JSON");
}

function saveSalesInvoice(){
	$( '#div-add-sales_invoice' ).prepend(block_loding_modal);
	
	$( '#btn-html-sales_invoice' ).attr('disabled', true);
	$( '#btn-html-sales_invoice' ).addClass('is-loading');

	/** Validaciones **/
	
	// Verificar si el dia es consolidado (Para pasar la validación, el día no debe estar consolidado)
	// Parametros (dia format(YYYY-MM-DD), turno, almacen)
	var params = {
		action: 'search-verify_consolidation',	
		dFecha: sTypeDate('fecha_dmy', $( '#txt-fe_emision' ).val(), '/'),
		iTurno: 0,
		iAlmacen: $( '#cbo-filtro-serie_documento' ).find(':selected').data('ialmacen'),
	}

	url = '/sistemaweb/ventas_clientes/facturas_venta.php';

	$.post( url, params, function( response ) {
		if (response.sStatus == 'success') {
			// Verificar si existe registro
			// Parametros (nombre módulo, tipo, serie, numero y almacén)
			var params = {
				action: 'search-verify_register',
				sNombreModulo : 'ventas-manuales',
				iTipoDocumento : $( '#cbo-filtro-tipo_documento' ).val(),
				sSerieDocumento : $( '#cbo-filtro-serie_documento' ).val(),
				iNumeroDocumento : $( '#txt-add-numero_documento' ).val(),
				iAlmacen: $( '#cbo-filtro-serie_documento' ).find(':selected').data('ialmacen'),
			}

			url = '/sistemaweb/ventas_clientes/facturas_venta.php';

			$.post( url, params, function( response ) {
				if ( $( '#hidden-tipo_accion' ).val()=='Editar' || (response.sStatus=='success' && $( '#hidden-tipo_accion' ).val()=='Agregar') ) {
					// Verificar si es un documento electronico verificar la fecha de emision deberá ser >= 5 días (La fecha con la que se registra no puede ser menor a los 5 días del día actual)
					// parametros: nombre modulo, tipo, serie, numero y almacén
					var params = {
						sDataType: 'JSON',
						action: 'search-verify_validations_FE',
						sNombreValidacion : 'fecha',
						sSerieDocumento : $( '#cbo-filtro-serie_documento' ).val(),
						dFechaEmision : sTypeDate('fecha_dmy', $( '#txt-fe_emision' ).val(), '/')
					}

					url = '/sistemaweb/ventas_clientes/facturas_venta.php';

					$.post( url, params, function( response ) {
						if ( response.sStatus == 'success' ) {
							// Regla: (boleta de venta y el monto es mayor a 700 o 350 (solo aplica para la selva) || FACTURA / NC / ND) verificar que tenga número de documento de identidad y nombres completos
							// parametros: nombre modulo, tipo, serie, numero y almacén
							// Valor: obtenemos de la tabla int_parametros campo max_unidentified
							var params = {
								sDataType: 'JSON',
								action: 'search-verify_several_types_validations',
								sNombreValidacion : 'numero_documento_identidad_nombres',
								iNumeroDocumentoIdentidadCliente : $( '#hidden-filtro-cliente-ruc' ).val(),
								iTipoDocumento : $( '#cbo-filtro-tipo_documento' ).val(),
								iTipoDocumentoReferencia : $( '#cbo-filtro-referencia-tipo_documento' ).val(),
								sExonerado : $( '#cbo-add-exonerado' ).val(),
								fTotTotal : $( '#hidden-totales-total' ).val(),
							}

							url = '/sistemaweb/ventas_clientes/facturas_venta.php';

							$.post( url, params, function( response ) {
								if ( response.sStatus == 'success' ) {
									bEstadoValidacion = validatePreviousDocumentToSave();
									if ( bEstadoValidacion ) {
										var sMessage = "¿Deseas guardar venta?";
								    	if (confirm(sMessage)) {
											//arrPost Guardar Venta
											//Cabecera
											var arrHeaderSaleInvoice = {
												action: 'save',
												//Hidden
												iNumeroLiquidacion : $( '#hidden-numero_liquidacion' ).val(),
												//Datos de Documento
												iTipoDocumento : $( '#cbo-filtro-tipo_documento' ).val(),
												sSerieDocumento : $( '#cbo-filtro-serie_documento' ).val(),
												iNumeroDocumento : $( '#txt-add-numero_documento' ).val(),
												dFechaEmision : sTypeDate('fecha_dmy', $( '#txt-fe_emision' ).val(), '/'),
												iAlmacen : $( '#cbo-filtro-serie_documento' ).find(':selected').data('ialmacen'),
												iCodigoImpuestoItem : $( '#hidden-codigo_impuesto_item' ).val(),
												iFormaPago : $( '#cbo-add-forma_pago' ).val(),
												sAnticipado : $.trim($( '#cbo-add-anticipado' ).val()),
												iDiasVencimiento : $( '#cbo-add-dias_credito' ).find(':selected').data('id_dias_vencimiento'),
												dFechaVencimiento : ($( '#cbo-add-forma_pago' ).val() != '06' ? sTypeDate('fecha_dmy', $( '#txt-fe_emision' ).val(), '/') : sTypeDate('fecha_dmy', $( '#txt-fe_vencimiento' ).val(), '/')),
												sCredito : $.trim($( '#cbo-add-credito' ).val()),
												sMoneda : $.trim($( '#cbo-add-moneda' ).val()),
												fTipoCambio : (isNaN(parseFloat($( '#txt-tipo_cambio' ).val())) ? 0 : parseFloat($( '#txt-tipo_cambio' ).val())),
												sDescargarStock : $.trim($( '#cbo-add-descargar_stock' ).val()),
												//Cliente
												iIdCliente : $.trim($( '#hidden-filtro-cliente-id' ).val()),
												iListaPrecioCliente : $.trim($( '#txt-lista_precio' ).val()),
												sExonerado : $.trim($( '#cbo-add-exonerado' ).val()),
												sTransferenciaGratuita : $.trim($( '#cbo-add-transferencia_gratuita' ).val()),
												sDespachoPerdido : $.trim($( '#cbo-add-despacho_perdido' ).val()),
												//Total del Documento
												fTotExonerada : (isNaN(parseFloat($( '#hidden-totales-exonerada' ).val())) ? 0 : parseFloat($( '#hidden-totales-exonerada' ).val())),
												fTotInafecta : (isNaN(parseFloat($( '#hidden-totales-inafecta' ).val())) ? 0 : parseFloat($( '#hidden-totales-inafecta' ).val())),
												fTotGravada : (isNaN(parseFloat($( '#hidden-totales-gravada' ).val())) ? 0 : parseFloat($( '#hidden-totales-gravada' ).val())),
												fTotIGV : (isNaN(parseFloat($( '#hidden-totales-igv' ).val())) ? 0 : parseFloat($( '#hidden-totales-igv' ).val())),
												fTotGratuita : (isNaN(parseFloat($( '#hidden-totales-gratuita' ).val())) ? 0 : parseFloat($( '#hidden-totales-gratuita' ).val())),
												fTotDescuento : (isNaN(parseFloat($( '#hidden-totales-descuento' ).val())) ? 0 : parseFloat($( '#hidden-totales-descuento' ).val())),
												fTotTotal : (isNaN(parseFloat($( '#hidden-totales-total' ).val())) ? 0 : parseFloat($( '#hidden-totales-total' ).val())),
											}

											//Complementario
											var arrComplementarySaleInvoice = {
												//Glosa
												sObservacion : $( '#txt-observaciones' ).val(),
												//Datos para N/C ó N/D
												dFechaEmisionReferencia : sTypeDate('fecha_dmy', $( '#txt-referencia-fe_emision' ).val(), '/'),
												iTipoDocumentoReferencia : $( '#cbo-filtro-referencia-tipo_documento' ).val(),
												sSerieDocumentoReferencia : $( '#cbo-filtro-referencia-serie_documento' ).val(),
												iNumeroDocumentoReferencia : $( '#txt-add-referencia-numero_documento' ).val(),
												//Detracción
												iDetraccion : $( '#cbo-add-detraccion' ).val(),
												iNumeroCuentaDetraccion : $( '#txt-detraccion-nu_cuenta' ).val(),
												fImporteDetraccion : (isNaN(parseFloat($( '#txt-detraccion-importe' ).val())) ? '' : parseFloat($( '#txt-detraccion-importe' ).val())),
												iPorcentajeDetraccion : $( '#txt-detraccion-porcentaje' ).val(),
												iCodigoBienServicioDetraccion : $( '#txt-detraccion-codigo_bienes_servicios' ).val(),
												//Otros datos tenian anteriormente la tabla
												sNombreCliente : $.trim($( '#txt-filtro-cliente-nombre' ).val()),
												iNumeroDocumentoIdentidadCliente : $( '#hidden-filtro-cliente-ruc' ).val(),
												sDireccionCliente : $( '#hidden-filtro-cliente-direccion' ).val(),
											}

											//Detalle
											var arrDetailSaleInvoice = [];

											var $fCantidad = 0.00;
											var $fPrecioVenta = 0.00;
											var $fSubtotal = 0.00;
											var $fImpuesto = 0.00;
											var $fDescuento = 0.00;
											var $fTotal = 0.00;

											$( '#table-sales_invoice_detail > tbody > tr' ).each(function(){
												var rows = $(this);
												$iIdItem = $.trim(rows.find("td:eq(0)").text());
												$sNombreItem = $.trim(rows.find("td:eq(1)").text());
												$fCantidad = parseFloat(rows.find("td:eq(2)").text());
												$fPrecioVenta = parseFloat(rows.find("td:eq(3)").text());
												$fSubtotal = parseFloat(rows.find("td:eq(4)").text());
												$fImpuesto = parseFloat(rows.find("td:eq(5)").text());
												$fDescuento = (isNaN(parseFloat(rows.find("td:eq(6)").text())) ? 0 : parseFloat(rows.find("td:eq(6)").text()));
												$fTotal = parseFloat(rows.find("td:eq(7)").text());
												$iCodigoTipoPlu = $.trim(rows.find("td:eq(8)").text());
												$iCodigoImpuestoItem = $.trim(rows.find("td:eq(9)").text()); // INAFECTAS

												var obj = {};
												obj.iIdItem = $iIdItem;
												obj.sNombreItem = $sNombreItem;
												obj.fCantidad = $fCantidad;
												obj.fPrecioVenta = $fPrecioVenta;
												obj.fSubtotal = $fSubtotal;
												obj.fImpuesto = $fImpuesto;
												obj.fDescuento = $fDescuento;
												obj.fTotal = $fTotal;
												obj.iCodigoTipoPlu = $iCodigoTipoPlu;
												obj.iCodigoImpuestoItem = $iCodigoImpuestoItem;

												arrDetailSaleInvoice.push(obj);
											});

											action = ( $(' #hidden-tipo_accion ').val()=='Agregar' ? 'save' : 'modify' );

											var params = {
												action: action,
												arrHeaderSaleInvoice : arrHeaderSaleInvoice,
												arrComplementarySaleInvoice : arrComplementarySaleInvoice,
												arrDetailSaleInvoice : arrDetailSaleInvoice,
											}
											// console.log(params);
											// return;

											url = '/sistemaweb/ventas_clientes/facturas_venta.php';

											$.ajax({
												type 	: "POST",
												url 	: url,
												data 	: params,
												dataType: "json",
												success: function(response) {
													if (response.sStatus=='success') {
														alert(response.sMessage);
														//Buscar documento agregado
														window.location='/sistemaweb/ventas_clientes/facturas_venta.php';
													} else {
														alert(response.sMessage);
													}
													$( '#btn-html-sales_invoice' ).removeClass('is-loading');
													$( '#btn-html-sales_invoice' ).attr('disabled', false);
												},
												error: function (xhr, status, error) {
													console.log(xhr.responseText);
													$( '.block-loading' ).remove();
												}
											}); // ./ arrPost Guardar Venta
										}// ./ Deseas guardar venta
									}
								} else if ( response.sStatus == 'warning' ) {
									alert( response.sMessage );

									$( '#btn-html-sales_invoice' ).removeClass('is-loading');
									$( '#btn-html-sales_invoice' ).attr('disabled', false);

									$( '.block-loading' ).remove();
								} else {
									alert( response.sMessage );
									$( '.block-loading' ).remove();
								}
							}, "json")
							.done(function(msg){
								console.log(msg);
								$( '.block-loading' ).remove();
							})
							.fail(function(xhr, status, error) {
								console.log(xhr.responseText);
								$( '.block-loading' ).remove();
							});;
						} else {
							alert( response.sMessage );

							$( '#btn-html-sales_invoice' ).removeClass('is-loading');
							$( '#btn-html-sales_invoice' ).attr('disabled', false);

							$( '.block-loading' ).remove();
						}
					}, "json")
					.done(function(msg){
						console.log(msg);
						$( '.block-loading' ).remove();
					})
					.fail(function(xhr, status, error) {
						console.log(xhr.responseText);
						$( '.block-loading' ).remove();
					});;
				} else if ( response.sStatus == 'warning' ) {
					alert( response.sMessage );

					$( '#btn-html-sales_invoice' ).removeClass('is-loading');
					$( '#btn-html-sales_invoice' ).attr('disabled', false);

					$( '.block-loading' ).remove();
				} else {
					alert( response.sMessage );
					
					$( '#btn-html-sales_invoice' ).removeClass('is-loading');
					$( '#btn-html-sales_invoice' ).attr('disabled', false);

					$( '.block-loading' ).remove();
				}
			}, "json")
			.done(function(msg){
				console.log(msg);
				$( '.block-loading' ).remove();
			})
			.fail(function(xhr, status, error) {
				console.log(xhr.responseText);
				$( '.block-loading' ).remove();
			});;
		} else if ( response.sStatus == 'warning' ) {
			alert( response.sMessage );
									
			$( '#btn-html-sales_invoice' ).removeClass('is-loading');
			$( '#btn-html-sales_invoice' ).attr('disabled', false);

			$( '.block-loading' ).remove();
		} else {
			alert( response.sMessage );
									
			$( '#btn-html-sales_invoice' ).removeClass('is-loading');
			$( '#btn-html-sales_invoice' ).attr('disabled', false);
			
			$( '.block-loading' ).remove();
		}
	},'json')
	.done(function(msg){
		console.log(msg);
		$( '.block-loading' ).remove();
	})
	.fail(function(xhr, status, error) {
		console.log(xhr.responseText);
		$( '.block-loading' ).remove();
	});;
	// ./ Verficar consolidacion
	/** /. Validaciones **/
}


function saveSalesInvoiceComplementary(){
	/** Validaciones **/

	// Verificar consolidación
	// Parametros (día, turno y fecha)
	var params = {
		action: 'search-verify_consolidation',	
		dFecha: sTypeDate('fecha_dmy', $( '#txt-fe_emision' ).val(), '/'),
		iTurno: 0,
		iAlmacen: $( '#cbo-filtro-serie_documento' ).find(':selected').data('ialmacen'),
	}

	url = '/sistemaweb/ventas_clientes/facturas_venta.php';

	$.post( url, params, function( response ) {
		if (response.sStatus == 'success') {
			$( '#btn-save-sale_invoice_complementary' ).attr('disabled', true);
			$( '#btn-save-sale_invoice_complementary' ).addClass('is-loading');

			// arrPost Guardar Complementarios
			var arrComplementarySaleInvoice = {
				//Datos de Documento
				iTipoDocumento : $( '#cbo-filtro-tipo_documento' ).val(),
				sSerieDocumento : $( '#cbo-filtro-serie_documento' ).val(),
				iNumeroDocumento : $( '#txt-add-numero_documento' ).val(),
				dFechaEmision : sTypeDate('fecha_dmy', $( '#txt-fe_emision' ).val(), '/'),
				//Modifcar fecha y forma de pago
				iFormaPago : $( '#cbo-add-forma_pago' ).val(),
				dFechaVencimiento : sTypeDate('fecha_dmy', $( '#txt-fe_vencimiento' ).val(), '/'),
				//Glosa
				sObservacion : $( '#txt-observaciones' ).val(),
				//Datos para N/C ó N/D
				dFechaEmisionReferencia : sTypeDate('fecha_dmy', $( '#txt-referencia-fe_emision' ).val(), '/'),
				iTipoDocumentoReferencia : $( '#cbo-filtro-referencia-tipo_documento' ).val(),
				sSerieDocumentoReferencia : $( '#cbo-filtro-referencia-serie_documento' ).val(),
				iNumeroDocumentoReferencia : $( '#txt-add-referencia-numero_documento' ).val(),
				//Detracción
				iDetraccion : $( '#cbo-add-detraccion' ).val(),
				iNumeroCuentaDetraccion : $( '#txt-detraccion-nu_cuenta' ).val(),
				fImporteDetraccion : (isNaN(parseFloat($( '#txt-detraccion-importe' ).val())) ? '' : parseFloat($( '#txt-detraccion-importe' ).val())),
				iPorcentajeDetraccion : $( '#txt-detraccion-porcentaje' ).val(),
				iCodigoBienServicioDetraccion : $( '#txt-detraccion-codigo_bienes_servicios' ).val(),
			}

			var params = {
				action: 'save_complementary',
				arrComplementarySaleInvoice : arrComplementarySaleInvoice,
			}

			url = '/sistemaweb/ventas_clientes/facturas_venta.php';

			$.ajax({
				type 	: "POST",
				url 	: url,
				data 	: params,
				dataType: "json",
				success: function(response) {
					if (response.sStatus == 'success') {
						alert(response.sMessage);
					} else {
						alert(response.sMessage);
					}
					$( '#btn-save-sale_invoice_complementary' ).removeClass('is-loading');
					$( '#btn-save-sale_invoice_complementary' ).attr('disabled', false);
				}
			});
			// ./ arrPost Guardar Complementarios
		} else if ( response.sStatus == 'warning' ) {
			alert( response.sMessage );
		} else {
			alert( response.sMessage );
		}
	},'json');
	// ./ Verficar consolidacion
	/** /. Validaciones **/
}

function validatePreviousDocumentToSave(){
	var bEstadoValidacion = true;

	var $fImporteDetraccion = (isNaN(parseFloat($( '#txt-detraccion-importe' ).val())) ? 0 : parseFloat($( '#txt-detraccion-importe' ).val()));

	if ( $( '#cbo-filtro-tipo_documento' ).val() == 0){
		$( '#cbo-filtro-tipo_documento' ).closest('.form-group').find('.help').html('Seleccionar tipo');
		bEstadoValidacion = false;

	    scrollToError($( "#cbo-filtro-tipo_documento" ));
	} else if ( $( '#cbo-filtro-serie_documento' ).val() == 0){
		$( '#cbo-filtro-serie_documento' ).closest('.form-group').find('.help').html('Seleccionar serie');
		bEstadoValidacion = false;

	    scrollToError($( "#cbo-filtro-serie_documento" ));
	} else if ( $( '#txt-add-numero_documento' ).val().length == 0){
		$( '#txt-add-numero_documento' ).closest('.form-group').find('.help').html('Sin correlativo');
		bEstadoValidacion = false;

	    scrollToError($( "#txt-add-numero_documento" ));
	} else if ( $( '#hidden-filtro-cliente-ruc' ).val().length == 0 || $( '#hidden-filtro-cliente-id' ).val().length == 0 || $( '#txt-filtro-cliente-nombre' ).val().length == 0){
		$( '#txt-filtro-cliente-nombre' ).closest('.form-group').find('.help').html('Seleccionar cliente');
		bEstadoValidacion = false;

	    scrollToError($( "#hidden-filtro-cliente-id" ));
	} else if ( $( '#txt-lista_precio' ).val().length == 0){
		$( '#txt-lista_precio' ).closest('.form-group').find('.help').html('Seleccionar lista de precio');
		bEstadoValidacion = false;

	    scrollToError($( "#txt-lista_precio" ));
	} else if ( ($( '#cbo-filtro-tipo_documento' ).val() === '20' || $( '#cbo-filtro-tipo_documento' ).val() === '11') && $( '#txt-observaciones' ).val().length < 7 ){//Validar si es NC ó ND, debe tener observación
		$( '#txt-observaciones' ).closest('.form-group').find('.help').html('Ingresar observación');	    	
		bEstadoValidacion = false;

		scrollToError($( "#txt-observaciones" ));
	} else if ( ($( '#cbo-filtro-tipo_documento' ).val() === '20' || $( '#cbo-filtro-tipo_documento' ).val() === '11') && $( '#cbo-filtro-referencia-tipo_documento' ).val() == 0 ){//Validar si es NC ó ND, seleccionar tipo de documento
		$( '#cbo-filtro-referencia-tipo_documento' ).closest('.form-group').find('.help').html('Seleccionar tipo');	    	
		bEstadoValidacion = false;

		scrollToError($( "#cbo-filtro-referencia-tipo_documento" ));
	} else if ( ($( '#cbo-filtro-tipo_documento' ).val() === '20' || $( '#cbo-filtro-tipo_documento' ).val() === '11') && $( '#cbo-filtro-referencia-serie_documento' ).val().length == 0 ){//Validar si es NC ó ND, seleccionar serie de documento
		$( '#cbo-filtro-referencia-serie_documento' ).closest('.form-group').find('.help').html('Seleccionar serie');	    	
		bEstadoValidacion = false;

		scrollToError($( "#cbo-filtro-referencia-serie_documento" ));
	} else if ( ($( '#cbo-filtro-tipo_documento' ).val() === '20' || $( '#cbo-filtro-tipo_documento' ).val() === '11') && $( '#cbo-filtro-serie_documento' ).val().substr(0,1) !== $( '#cbo-filtro-referencia-serie_documento' ).val().substr(0,1) ) {
		$( '#cbo-filtro-serie_documento' ).closest('.form-group').find('.help').html('La <b>(primera letra)</b> de ambas series deben coincidir ');
		$( '#cbo-filtro-referencia-serie_documento' ).closest('.form-group').find('.help').html('La <b>(primera letra)</b> de ambas series deben coincidir ');
		bEstadoValidacion = false;

		scrollToError($( "#cbo-filtro-referencia-serie_documento" ));
	} else if ( ($( '#cbo-filtro-tipo_documento' ).val() === '20' || $( '#cbo-filtro-tipo_documento' ).val() === '11') && $( '#txt-add-referencia-numero_documento' ).val().length == 0 ){//Validar si es NC ó ND, seleccionar numero de documento
		$( '#txt-add-referencia-numero_documento' ).closest('.form-group').find('.help').html('Ingresar número');	    	
		bEstadoValidacion = false;

		scrollToError($( "#txt-add-referencia-numero_documento" ));
	} else if ( $( '#cbo-add-detraccion' ).val() === 'S' && $( '#txt-detraccion-nu_cuenta' ).val().length == 0 ) {//Si se activa Detracción, verificar los campos (nro. cuenta, importe, porcentaje, codigo de servicio y bien)
    	$( '#txt-detraccion-nu_cuenta' ).closest('.form-group').find('.help').html('Ingresar número cuenta');
    	bEstadoValidacion = false;

    	scrollToError($( '#txt-detraccion-nu_cuenta' ));
	} else if ( $( '#cbo-add-detraccion' ).val() === 'S' && $fImporteDetraccion <= 0 ) {
    	$( '#txt-detraccion-importe' ).closest('.form-group').find('.help').html('Importe debe ser mayor a 0');
    	bEstadoValidacion = false;

    	scrollToError($( '#txt-detraccion-importe' ));
	} else if ( $( '#cbo-add-detraccion' ).val() === 'S' && $( '#txt-detraccion-porcentaje' ).val().length == 0 ) {
    	$( '#txt-detraccion-porcentaje' ).closest('.form-group').find('.help').html('Ingresar porcentaje');
    	bEstadoValidacion = false;

    	scrollToError($( '#txt-detraccion-porcentaje' ));
	} else if ( $( '#cbo-add-detraccion' ).val() === 'S' && $( '#txt-detraccion-codigo_bienes_servicios' ).val().length < 3 ) {
    	$( '#txt-detraccion-codigo_bienes_servicios' ).closest('.form-group').find('.help').html('Ingresar código de bien / servicio');
    	bEstadoValidacion = false;

    	scrollToError($( '#txt-detraccion-codigo_bienes_servicios' ));
	} else if ( $( '#cbo-add-forma_pago' ).val() == '' ) {
    	$( '#cbo-add-forma_pago' ).closest('.form-group').find('.help').html('Seleccionar F. Pago');
    	bEstadoValidacion = false;

    	scrollToError($( '#cbo-add-forma_pago' ));
	}
	return bEstadoValidacion;
}

function cancelInvoiceSale(arrCancel){
	var sMessageAction = ' ';
	if (arrCancel.sAction == 'edit')
		sMessageAction = ' edición ';

	var sMessage = "¿Deseas cancelar" + sMessageAction + "venta?";
	if (confirm(sMessage)) {
		window.location = arrCancel.sURL;
	}
}

function calculateDuoDate(){
	var iDiasVencimiento = parseInt($( '#cbo-add-dias_credito' ).val());
	var dFechaVencimiento = sTypeDate('fecha_dmy', $( '#txt-fe_emision' ).val(), '/')
	dFechaVencimiento = dFechaVencimiento.split('-');
	var iMonth = parseInt(dFechaVencimiento[1]);
	iMonth--;

	// Castear fecha para aumentar día(s)
	var dFechaVencimiento = new Date( dFechaVencimiento[0], iMonth, dFechaVencimiento[2]);//(YYYY, MM, DD)
	dFechaVencimiento.setDate(dFechaVencimiento.getDate() + iDiasVencimiento);

	//Obtener formato d/m/Y con javascript
	dDay = dFechaVencimiento.getDate();
	dMonth = dFechaVencimiento.getMonth() + 1;
	dYear = dFechaVencimiento.getFullYear();

	if(dMonth.toString().length < 2)
		dMonth = "0".concat(dMonth);

	if(dDay.toString().length<2)
		dDay = "0".concat(dDay);

	$( "#txt-fe_vencimiento" ).val(dDay + '/' + dMonth + '/' + dYear);

    $( "#txt-fe_vencimiento" ).datepicker({
    	changeMonth: true,
    	changeYear: true,
		minDate: $("#txt-fe_emision").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_emision").datepicker("option", "maxDate", selectedDate);
		}
    });
}
