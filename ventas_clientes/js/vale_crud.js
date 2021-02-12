var save_accion;
var bEstadoValidacion;

// ID's input for button add item temporal
var $iIdItem = $( '#txt-Nu_Id_Producto' );
var $sNombreItem = $( '#txt-No_Producto_Detalle' );
var $fCantidad = $( '#txt-Nu_Cantidad' );
var $fPrecioVenta = $( '#txt-Nu_Precio' );
var $fTotal = $( '#txt-Nu_Total' );

function edit_vale(Nu_Almacen, Fe_Emision, Ch_Documento, Nu_Turno, Nu_Lado){
	$( '#div-credit_detail' ).hide();
	$( '#table-credit_detail >tbody' ).empty();

	data = {
		'Nu_Almacen' 	: Nu_Almacen,
		'Fe_Emision' 	: Fe_Emision,
		'Ch_Documento' 	: Ch_Documento,
		'Nu_Turno' 		: Nu_Turno,
		'Nu_Lado' 		: Nu_Lado,
	};

   save_accion = 'update';

	$.post( "reportes/c_vale_crud.php", {
		accion 	: 'editVale',
		data  	: data,
	}, function(response){
		$( '.message-header-text' ).html('');
		$( '.message-body' ).html('');

		if(response.status == 'error'){
			$( '.MsgError' ).show();
			$( '.message' ).removeClass('is-primary');
			$( '.message' ).addClass('is-danger');
			$( '.message-header-text' ).html('Error');
			$( '.message-body' ).html(response.message);
		}else{
			$( '.MsgError' ).hide();
			$( '.message-header-text' ).html('');
			$( '.message-body' ).html('');

			/* CLEAN HIDDEN, TEXT and SELECT */
			$(".required").each(function(){	
				$($(this)).val('');
			});

			//Limpiar item
			var $iIdItem = $( '#txt-Nu_Id_Producto' );
			var $sNombreItem = $( '#txt-No_Producto_Detalle' );
			var $fCantidad = $( '#txt-Nu_Cantidad' );
			var $fPrecioVenta = $( '#txt-Nu_Precio' );
			var $fTotal = $( '#txt-Nu_Total' );

			$iIdItem.val( '' );
			$sNombreItem.val( '' );
			$fCantidad.val( '' );
			$fPrecioVenta.val( '' );
			$fTotal.val( '' );

			$iIdItem.prop( "disabled", false );
			$sNombreItem.prop( "disabled", false );
			$fCantidad.prop( "disabled", false );
			$fPrecioVenta.prop( "disabled", false );
			$fTotal.prop( "disabled", false );

			$(".help").html('');

			// Limpiar valores opcionales solo si tienen valor
			$( "#txt-Nu_Documento_Identidad_Chofer" ).val('');
			$( "#txt-No_Chofer" ).val('');
			$( "#txt-Nu_Odometro" ).val('');
			
			$( "#btn-save" ).prop( "disabled", false );

			$( "#div-Vale_CRUD" ).hide();
			$( "#template-Vale_Credito" ).hide();
			$( "#template-Vale_Credito_Agregar" ).show();

			$( "#cbo-Nu_Almacen" ).prop( "disabled", true );
			$( "select[name=cbo-Nu_Almacen]" ).html('<option value="' + response.arrValeCabecera['ch_sucursal'] + '">' + response.arrValeCabecera['no_almacen'] + '</option>');

			$( "#txt-Fe_Emision" ).prop( "disabled", true );
	        $( "#txt-Fe_Emision" ).val(response.arrValeCabecera['fe_emision']);

			$( "#txt-Ch_Documento" ).prop( "disabled", true );
	        $( "#txt-Ch_Documento" ).val(response.arrValeCabecera['ch_documento']);

			$( "#txt-Ch_Documento_Manual" ).prop( "disabled", false );
	        $( "#txt-Ch_Documento_Manual" ).val($.trim(response.arrValeCabecera['no_documento_manual']));

	        $( ".cbo-Tipo_Venta" ).hide();

			var arrTurnos = response.arrTurnos;
			var arrCajas = response.arrCajas;
			var arrLados = response.arrLados;

			$( "#cbo-Nu_Turno" ).prop( "disabled", false );
			$( "select[name=cbo-Nu_Turno]" ).html('<option value="">Seleccionar..</option>');
			for (var i = 1; i <= arrTurnos[0].turno; i++){
				var selected = '';
				if(response.arrValeCabecera['ch_turno'] == i)
					selected = "selected";
	    		$('#cbo-Nu_Turno').append( '<option value="'+i+'" ' + selected + '>'+i+'</option>' );
			}

			if ( arrCajas != null ) {
				$( "#cbo-Nu_Caja" ).prop( "disabled", false );
				$( "select[name=cbo-Nu_Caja]" ).html('<option value="">Seleccionar..</option>');
				for (var i = 0; i < arrCajas.length; i++){
					var selected = '';
					if(response.arrValeCabecera['nu_caja'] == arrCajas[i]['caja'])
						selected = "selected";
		    		$('#cbo-Nu_Caja').append( '<option value="' + arrCajas[i]['caja']+'" ' + selected + '>' + arrCajas[i]['caja'] + '</option>' );
				}
			}

			if ( arrLados != null ) {
				if(arrLados.length > 0){
					$( "#cbo-Nu_Lado" ).prop( "disabled", false );
					$( "#cbo-Nu_Lado" ).addClass('required');
					$( "select[name=cbo-Nu_Lado]" ).html('<option value="">Seleccionar..</option>');
					for (var i = 0; i < arrLados.length; i++){
						var selected = '';
						if(response.arrValeCabecera['nu_lado'] == arrLados[i]['pump'])
							selected = "selected";
			    		$('#cbo-Nu_Lado').append( '<option value="' + arrLados[i]['pump'] + '" ' + selected + '>' + arrLados[i]['pump'] + '</option>' );
					}
				}else{
					$( ".cbo-Nu_Lado" ).hide();
					$( "#cbo-Nu_Lado" ).removeClass('required');
				}
			}

			$( "#txt-Nu_Documento_Identidad" ).prop( "disabled", false );
	        $( "#txt-Nu_Documento_Identidad" ).val(response.arrValeCabecera['nu_documento_identidad']);

			$( "#txt-No_Razsocial" ).prop( "disabled", false );
	        $( "#txt-No_Razsocial" ).val(response.arrValeCabecera['no_razon_social']);

			$( "#txt-No_Placa" ).prop( "disabled", false );
	        $( "#txt-No_Placa" ).val(response.arrValeCabecera['ch_placa']);

			$( "#txt-Nu_Tarjeta" ).prop( "disabled", false );
	        $( "#txt-Nu_Tarjeta" ).val(response.arrValeCabecera['ch_tarjeta']);

			$( "#txt-Nu_Documento_Identidad_Chofer" ).prop( "disabled", false );
	        $( "#txt-Nu_Documento_Identidad_Chofer" ).val(response.arrValeCabecera['nu_documento_identidad_chofer']);

			$( "#txt-No_Chofer" ).prop( "disabled", false );
	        $( "#txt-No_Chofer" ).val(response.arrValeCabecera['no_chofer']);

			$( "#txt-Nu_Odometro" ).prop( "disabled", false );
	        $( "#txt-Nu_Odometro" ).val(response.arrValeCabecera['nu_odometro']);

	        /* Detalle Vale */
            for (var key in response.arrValeDetalle) {
                var table_sales_invoice_detail =
				"<tr id='tr_detalle_producto" + $.trim(response.arrValeDetalle[key].ch_articulo) + "'>"
					+"<td style='display:none;' class='text-left'>" + $.trim(response.arrValeDetalle[key].ch_articulo) + "</td>"
		            +"<td class='text-left'>" + $.trim(response.arrValeDetalle[key].no_producto) + "</td>"
		            +"<td class='text-right'>" + $.trim(response.arrValeDetalle[key].nu_cantidad) + "</td>"
		            +"<td class='text-right'>" + $.trim(response.arrValeDetalle[key].nu_precio_venta) + "</td>"
		            +"<td class='text-right'>" + $.trim(response.arrValeDetalle[key].nu_importe) + "</td>"
		            +"<td class='text-center'><button type='button' id='btn-delete-sale_invoice_detail_item' class='button is-danger is-small icon-size btn-danger'><span class='icon is-small'><i class='fa fa-trash'></i></span></button></td>"
				+ "</tr>";

				$( '#div-credit_detail' ).show();
				$( '#table-credit_detail >tbody' ).append(table_sales_invoice_detail);
            }

	        $( ".btn-label_save_edit" ).text('Actualizar');
	    }
	}, 'JSON')
}

function delete_vale(Nu_Almacen, Fe_Emision, Ch_Documento, Nu_Turno){

	if(confirm('¿Desea eliminar el vale ' + Ch_Documento + '?')){
		
		/* Cargador */
		var block_loding_modal = $('<div class="block-loading" />');
		$( "#div-Vale_CRUD" ).prepend(block_loding_modal);

		var data = {
			'Nu_Almacen' 	: Nu_Almacen,
			'Fe_Emision' 	: Fe_Emision,
			'Ch_Documento' 	: Ch_Documento,
			'Nu_Turno' 		: Nu_Turno,
		};

		$.post( "reportes/c_vale_crud.php", {
			accion 	: 'deleteVale',
	        data  	: data,
		},function(response){
			$( '.MsgError' ).show();
			$( '.message-header-text' ).html('');
			$( '.message-body' ).html('');

			if(response.status == 'error'){
				$( '.message' ).removeClass('is-primary');
				$( '.message' ).addClass('is-danger');
				$( '.message-header-text' ).html('Error');
				$( '.message-body' ).html(response.message);
			}else{
				$( '.message' ).removeClass('is-danger');
				$( '.message' ).addClass('is-primary');
				$( '.MsgError' ).delay(1000).hide(600);//1000 = 1 Segundos
				$( '.message-header-text' ).html('Realizado');
				$( '.message-body' ).html(response.message);
				//Delete
				var data = {
			        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
			        Fe_Inicial 	: $('#txt-fe_inicial').val(),
			        Fe_Final	: $('#txt-fe_final').val(),
	        		Nu_Estado 	: $('#cbo-Nu_Estado option:selected').val(),
			        Nu_Ticket 	: $('#txt-nu_ticket').val(),
				};

				$.post( "reportes/c_vale_crud.php", {
					accion 	: 'listAll',
			        data 	: data,
			        page 	: 1
				}, function(data){
					setTimeout(function(){
						$( "#div-Vale_CRUD" ).html(data);
					}, 1050)
				})
			}
			$( ".block-loading" ).remove();
		}, 'JSON')
	}
}

$( function() {
	/* Cargador */
	var block_loding_modal = $('<div class="block-loading" />');

	$( "#template-Vale_Credito_Agregar" ).hide();

	$( "#btn-buscar" ).click(function(){
		$( "#div-Vale_CRUD" ).show();
		$( "#div-Vale_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-buscar' ).attr('disabled', true);
		$( '#btn-buscar' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
	        Fe_Inicial 	: $('#txt-fe_inicial').val(),
	        Fe_Final	: $('#txt-fe_final').val(),
	        Nu_Estado 	: $('#cbo-Nu_Estado option:selected').val(),
			Nu_Ticket 	: $('#txt-nu_ticket').val(),
			sIdCliente : $('#hidden-filtro-cliente-id').val(),
			sNombreCliente : $('#txt-filtro-cliente-nombre').val(),
		};

		$.post( "reportes/c_vale_crud.php", {
			accion 	: 'listAll',
			data 	: data,
       		page 	: 1
		}, function(data){
			$( "#div-Vale_CRUD" ).html(data);

			$( '#btn-buscar' ).removeClass('is-loading');
			$( '#btn-buscar' ).attr('disabled', false);
		})
	});

	$( "#btn-excel" ).click(function(e) {
		$( "#div-Vale_CRUD" ).show();
		$( "#div-Vale_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-excel' ).attr('disabled', true);
		$( '#btn-excel' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
	        Fe_Inicial 	: $('#txt-fe_inicial').val(),
	        Fe_Final	: $('#txt-fe_final').val(),
	        Nu_Estado 	: $('#cbo-Nu_Estado option:selected').val(),
			Nu_Ticket 	: $('#txt-nu_ticket').val(),
			sIdCliente : $('#hidden-filtro-cliente-id').val(),
			sNombreCliente : $('#txt-filtro-cliente-nombre').val(),
		};

		$.post( "reportes/c_vale_crud.php", {
			accion 	: 'listAll',
			data 	: data,
       		page 	: 1
		}, function(data){
			$( "#div-Vale_CRUD" ).html(data);
		})

		var data = {
	        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
	        Fe_Inicial 	: $('#txt-fe_inicial').val(),
	        Fe_Final	: $('#txt-fe_final').val(),
	        Nu_Estado 	: $('#cbo-Nu_Estado option:selected').val(),
			Nu_Ticket 	: $('#txt-nu_ticket').val(),
			sIdCliente : $('#hidden-filtro-cliente-id').val(),
			sNombreCliente : $('#txt-filtro-cliente-nombre').val(),
		};

		$.post( "reportes/c_vale_crud.php", {
			accion 	: 'listAllExcel',
			data 	: data,
		}, function(data){
			$( '#btn-excel' ).removeClass('is-loading');
			$( '#btn-excel' ).attr('disabled', false);

			$( "#div-excel" ).hide();
			$( "#div-excel" ).html(data);
			var No_Archivo_Excel = 'Vales_Credito_' + $('#txt-fe_inicial').val() + '-' + $('#txt-fe_final').val();

			e.preventDefault();
	         $("#div-excel").table2excel({
	            exclude: ".noExl",
	            name: "demo",
	            filename : No_Archivo_Excel
	        });
		})
	});

	$( "#btn-agregar" ).click(function() {
		$( "#template-Vale_Credito" ).hide();
		$( "#template-Vale_Credito_Agregar" ).show();
		$( "#div-Vale_CRUD" ).hide();

		/* CLEAN HIDDEN, TEXT and SELECT */
		$(".required").each(function(){	
			$($(this)).val('');
		});

		/* Limpiar valores opcionales solo si tienen valor */
		$( "#txt-Ch_Documento_Manual" ).val('');
		$( "#txt-Nu_Documento_Identidad_Chofer" ).val('');
		$( "#txt-No_Chofer" ).val('');
		$( "#txt-Nu_Odometro" ).val('');


		$( "#cbo-Nu_Almacen" ).prop( "disabled", false );
		$( "#cbo-Nu_Almacen" ).val("");

		$.post( "../assets/helper.php", {
	    	accion 		: 'getAlmacenes',
		}, function(response){
			var arrAlmacenes = response.arrAlmacenes;
			if (response.status == 'success'){
				$("select[name=cbo-Nu_Almacen]").html('<option value="">Seleccionar..</option>');
				for (var i = 0; i < arrAlmacenes.length; i++)
					$("select[name=cbo-Nu_Almacen]").append(new Option(arrAlmacenes[i]["ch_nombre_almacen"], arrAlmacenes[i]["ch_almacen"]));
			}else{
				alert('Error al obtener almacenes');
			}
		}, 'JSON');

		$( "#txt-Fe_Emision" ).val("");

    	$( ".cbo-Tipo_Venta" ).show();

        $( ".btn-label_save_edit" ).text('Guardar');
		save_accion = 'add';

		// Nuevos cambios
		$( '#div-credit_detail' ).hide();
		$( '#table-credit_detail >tbody' ).empty();
	})

	$( '#btn-add-product_detail' ).click(function() {
		// ID's input for button add item temporal
		$iIdItem = $( '#txt-Nu_Id_Producto' );
		$sNombreItem = $( '#txt-No_Producto_Detalle' );
		$fCantidad = $( '#txt-Nu_Cantidad' );
		$fPrecioVenta = $( '#txt-Nu_Precio' );
		$fTotal = $( '#txt-Nu_Total' );

		bEstadoValidacion = validatePreviousDocumentToSave();
		if (bEstadoValidacion) {
			//Consultar si valido stock, en el antiguo programa no lo hacia.
			var table_sales_invoice_detail =
			"<tr id='tr_detalle_producto" + $iIdItem.val() + "'>"
				+"<td style='display:none;' class='text-left'>" + $iIdItem.val() + "</td>"
	            +"<td class='text-left'>" + $sNombreItem.val() + "</td>"
	            +"<td class='text-right'>" + $fCantidad.val() + "</td>"
	            +"<td class='text-right'>" + $fPrecioVenta.val() + "</td>"
	            +"<td class='text-right'>" + $fTotal.val() + "</td>"
	            +"<td class='text-center'><button type='button' id='btn-delete-sale_invoice_detail_item' class='button is-danger is-small icon-size btn-danger'><span class='icon is-small'><i class='fa fa-trash'></i></span></button></td>"
			+ "</tr>";

			if( isExistItem($iIdItem.val()) ){
				alert('Ya se agregó el item -> ' + $sNombreItem.val());
				$( $sNombreItem ).focus();
				$( $sNombreItem ).select();
			} else {
				$( '#div-credit_detail' ).show();
				$( '#table-credit_detail >tbody' ).append(table_sales_invoice_detail);

				//Limpiar item
				$iIdItem.val( '' );
				$sNombreItem.val( '' );
				$fCantidad.val( '' );
				$fPrecioVenta.val( '' );
				$fTotal.val( '' );

				//Ubicar en nombre de item
				$( $sNombreItem ).focus();

				//Calcular totales
				calcAmounts();

				//Activar button guardar factura de venta
				$( '#btn-save' ).prop( "disabled", false );
			}
	    }
	});
	// /. Agregar Item temporal

	$( '#table-credit_detail tbody' ).on('click', '#btn-delete-sale_invoice_detail_item', function(){
    	$(this).closest('tr').remove();

    	calcAmounts();

		$( '#btn-save' ).prop( "disabled", false );
	    if ($( '#table-credit_detail > tbody > tr' ).length == 0){
			$( '#div-credit_detail' ).hide();
			$( '#btn-save' ).prop( "disabled", true );
	    }
    })

    $( ".btn-close" ).click(function(){
		$( "#template-Vale_Credito" ).show();
		$( "#template-Vale_Credito_Agregar" ).hide();
		$( "#div-Vale_CRUD" ).show();

		$( '#div-credit_detail' ).hide();
		$( '#table-credit_detail >tbody' ).empty();
    })

	$( "#btn-save" ).click(function(){
		/* Validacion de Formulario */
	    var verify_inputs_required = true;

		$(".required").each(function(){
			if ($($(this)).val().length === 0){
				verify_inputs_required = false;
				$( ".help" ).show();
				$( "#" + $( this )['context']['id'] ).closest('.column').find('.help').html('Ingresar valor');
				$( "#" + $( this )['context']['id'] ).closest('.column').find('.help').addClass('is-danger');
			}else
				$( "#" + $( this )['context']['id'] ).closest('.column').find('.help').html('');
		})	

		/* Validacion especial */
		var $cbo_Nu_Lado = $( "#cbo-Nu_Lado" );
		if($( "#cbo-no_tipo_venta" ).val() == 'C'){
			if( $cbo_Nu_Lado.val().length === 0 ){
				$( ".help" ).show();
				$( $cbo_Nu_Lado ).closest('.column').find('.help').html('Debes seleccionar un Lado');
				$( $cbo_Nu_Lado ).closest('.column').find('.help').addClass('is-danger');
				verify_inputs_required = false;
			}else{
				$( $cbo_Nu_Lado ).addClass('combobox');
				$( $cbo_Nu_Lado ).closest('.column').find('.help').html('');
			}
		}else
			$( $cbo_Nu_Lado ).removeClass('combobox');

		if(verify_inputs_required){
			var arrFormAgregarVale = {
				'Nu_Almacen' 				: $('#cbo-Nu_Almacen option:selected').val(),
				'Fe_Emision' 				: $('#txt-Fe_Emision').val(),
				'Ch_Documento' 				: $('#txt-Ch_Documento').val(),
				'Ch_Documento_Manual' 		: $('#txt-Ch_Documento_Manual').val(),
				'Nu_Turno' 					: $('#cbo-Nu_Turno option:selected').val(),
				'Nu_Caja' 					: $('#cbo-Nu_Caja option:selected').val(),
				'Nu_Lado' 					: $('#cbo-Nu_Lado option:selected').val(),
				'Nu_Documento_Identidad' 	: $('#txt-Nu_Documento_Identidad').val(),
				'No_Placa' 					: $('#txt-No_Placa').val(),
				'Nu_Tarjeta' 				: $('#txt-Nu_Tarjeta').val(),
				'Nu_Id_Producto' 			: $('#txt-Nu_Id_Producto').val(),
				'Nu_Cantidad' 				: $('#txt-Nu_Cantidad').val(),
				'Nu_Precio' 				: $('#txt-Nu_Precio').val(),
				'Nu_Total' 					: $('#txt-Nu_Total').val(),
			};

			/* Agregar valores opcionales solo si tienen valor */
			($( "#txt-Nu_Documento_Identidad_Chofer" ).val().length > 0 ? arrFormAgregarVale.Nu_Documento_Identidad_Chofer = $('#txt-Nu_Documento_Identidad_Chofer').val() : '');
			($( "#txt-Nu_Odometro" ).val().length > 0 ? arrFormAgregarVale.Nu_Odometro = $('#txt-Nu_Odometro').val() : '');

			if ($( '#table-credit_detail > tbody > tr' ).length == 0){
				alert('Primero se debe agregar item');
			} else {
				$( "#template-Vale_Credito_Agregar" ).prepend(block_loding_modal);

				// Vale de crédito -> Detalle
				var arrDetailCreditVoucher = [];

				var $fCantidad = 0.00;
				var $fPrecioVenta = 0.00;
				var $fTotal = 0.00;

				$( '#table-credit_detail > tbody > tr' ).each(function(){
					var rows = $(this);
					$iIdItem = $.trim(rows.find("td:eq(0)").text());
					$sNombreItem = $.trim(rows.find("td:eq(1)").text());
					$fCantidad = parseFloat(rows.find("td:eq(2)").text());
					$fPrecioVenta = parseFloat(rows.find("td:eq(3)").text());
					$fTotal = parseFloat(rows.find("td:eq(4)").text());

					var obj = {};
					obj.iIdItem = $iIdItem;
					obj.sNombreItem = $sNombreItem;
					obj.fCantidad = $fCantidad;
					obj.fPrecioVenta = $fPrecioVenta;
					obj.fTotal = $fTotal;

					arrDetailCreditVoucher.push(obj);
				});

				$.post( "reportes/c_vale_crud.php", {
					accion 				: save_accion,
				    arrFormAgregarVale 	: arrFormAgregarVale,
				    arrDetailCreditVoucher : arrDetailCreditVoucher,
				}, function(response){
					$( '.message-body' ).html('');
					$( '.message' ).removeClass('is-danger is-primary is-warning');

					if ( response.status == 'success' ){
						alert( response.message );

						// CLEAN HIDDEN, TEXT and SELECT
						$(".required").each(function(){	
							$($(this)).val('');
						});

						// Limpiar valores opcionales solo si tienen valor
						$( "#txt-Nu_Documento_Identidad_Chofer" ).val('');
						$( "#txt-No_Chofer" ).val('');
						$( "#txt-Nu_Odometro" ).val('');

						var data = {
					        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
					        Fe_Inicial 	: $('#txt-fe_inicial').val(),
					        Fe_Final	: $('#txt-fe_final').val(),
	        				Nu_Estado 	: $('#cbo-Nu_Estado option:selected').val(),
					        Nu_Ticket 	: $('#txt-nu_ticket').val(),
						};

						$.post( "reportes/c_vale_crud.php", {
							accion 	: 'listAll',
					        data 	: data,
					        page 	: 1
						}, function(data){
							$( "#template-Vale_Credito_Agregar" ).hide();
							$( "#template-Vale_Credito" ).show();
							$( "#div-Vale_CRUD" ).show();
							$( "#div-Vale_CRUD" ).html(data);
						})
					} else {
						var class_modal = 'is-danger';
						if ( response.status == 'warning' )
							class_modal = 'is-warning';

						alert( response.message );
					}

					$( ".block-loading" ).remove();
				}, 'JSON');
			}// /. Entrando a guardar vale de crédito
		}
	})

    //Buscar antes de cargar la pagina
	var data = {
        Nu_Almacen 	: '',
        Fe_Inicial 	: $('#txt-fe_inicial').val(),
        Fe_Final 	: $('#txt-fe_final').val(),
        Nu_Estado 	: '',
        Nu_Ticket 	: '',
	};

	BuscarValeCreditos('listAll', data, 1);
});

function BuscarValeCreditos(accion, data, page){
	var block_loding_modal = $('<div class="block-loading" />');

	$( "#div-Vale_CRUD" ).show();
	$( "#div-Vale_CRUD" ).prepend(block_loding_modal);
	$.post( "reportes/c_vale_crud.php", {
		accion 	: accion,
   		data 	: data,
   		page 	: page
	}, function(data){
		$( "#div-Vale_CRUD" ).html(data);
	})
}

function validatePreviousDocumentToSave(){
	// ID's input for button add item temporal
	$iIdItem = $( '#txt-Nu_Id_Producto' );
	$sNombreItem = $( '#txt-No_Producto_Detalle' );
	$fCantidad = $( '#txt-Nu_Cantidad' );
	$fPrecioVenta = $( '#txt-Nu_Precio' );
	$fTotal = $( '#txt-Nu_Total' );

	bEstadoValidacion = true;

	if ( $iIdItem.val().length == 0 || $sNombreItem.val().length == 0){
		alert('Seleccionar item');
		bEstadoValidacion = false;

		scrollToError($iIdItem);
	} else if ( $fCantidad.val().length == 0 ){
		alert('Ingresar cantidad');
		bEstadoValidacion = false;

		scrollToError($fCantidad);
	}
	/* else if ( $fCantidad.val() <= 0 ){
		alert('La cantidad debe ser mayor a 0');
		bEstadoValidacion = false;

		scrollToError($fCantidad);
	} 
	*/else if ( $fPrecioVenta.val().length == 0 ){
		alert('Ingresar precio venta');
		bEstadoValidacion = false;

		scrollToError($fPrecioVenta);
	} else if ( $fPrecioVenta.val() <= 0 ){
		alert('El precio de venta debe ser mayor a 0');
		bEstadoValidacion = false;

		scrollToError($fPrecioVenta);
	} else if ( $fTotal.val().length == 0 ){
		alert('Ingresar total');
		bEstadoValidacion = false;

		scrollToError($fTotal);
	} /*else if ( $fTotal.val() <= 0 ){
		alert('El total debe ser mayor a 0');
		bEstadoValidacion = false;

		scrollToError($fTotal);
	}
	*/
	return bEstadoValidacion;
}


function calcAmounts(){
	var $fSumCantidad = 0.00;
	var $fSumSubtotal = 0.00;
	var $fSumImpuesto = 0.00;
	var $fSumDescuento = 0.00;
	var $fSumTotal = 0.00;
	var $sEstadoInafecto = "N";
	$( '#table-sales_invoice_detail > tbody > tr' ).each(function(){
		var rows = $(this);
		$fSumCantidad += parseFloat(rows.find("td:eq(2)").text());
		$fSumSubtotal += parseFloat(rows.find("td:eq(4)").text());
		$fSumImpuesto += parseFloat(rows.find("td:eq(5)").text());
		$fSumDescuento += (isNaN(parseFloat(rows.find("td:eq(6)").text())) ? 0 : parseFloat(rows.find("td:eq(6)").text()));
		$fSumTotal += parseFloat(rows.find("td:eq(7)").text());
		$iCodigoImpuestoItem = $.trim(rows.find("td:eq(9)").text());

  		if ( $iCodigoImpuestoItem.length == 0)
  			$sEstadoInafecto = "S";
	});
}

function isExistItem($ID_Producto){
  return Array.from($('tr[id*=tr_detalle_producto]'))
    .some(element => ($('td:nth(0)',$(element)).html()===$ID_Producto));
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