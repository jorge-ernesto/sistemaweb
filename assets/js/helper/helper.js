$( function() {
	$( '.input-number_letter' ).on('input', function () {
		this.value = this.value.replace(/[^a-zA-Z0-9]/g,'');
	});

	$( '.input-number' ).on('input', function () {
		this.value = this.value.replace(/[^0-9]/g,'');
	});

	/* Ocultar excel */
	$('#div-excel').hide();
	
	/* Configuracion hora */
	var options = {
        now: "08:00", //hh:mm 24 hour formatonly, defaults to current time 
        twentyFour: false, //Display 24 hour format, defaults to false
        upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
        downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
        close: 'wickedpicker__close', //The close class selector to use, for custom CSS
        hoverState: 'hover-state', //The hover state class to use, for custom CSS
        title: 'Hora', //The Wickedpicker's title,
        showSeconds: false, //Whether or not to show seconds,
        secondsInterval: 1, //Change interval for seconds, defaults to 1  ,
        minutesInterval: 1, //Change interval for minutes, defaults to 1
        beforeShow: null, //A function to be called before the Wickedpicker is shown
        show: null, //A function to be called when the Wickedpicker is shown
        clearable: false, //Make the picker's input clearable (has clickable "x")
    };

	var Fe_Hora_Recepcion 	= $( "#txt-Fe_Hora_Recepcion" );
	if(Fe_Hora_Recepcion.val() !== undefined)
    	$('.timepicker').wickedpicker(options);

	$.datepicker.regional['es'] = {
	    closeText: 'Cerrar',
	    prevText: '<Ant',
	    nextText: 'Sig>',
	    currentText: 'Hoy',
	    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
	    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
	    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
	    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
	    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
	    weekHeader: 'Sm',
	    dateFormat: 'dd/mm/yy',
	    firstDay: 1,
	    isRTL: false,
	    showMonthAfterYear: false,
	    yearSuffix: ''
	};

	$.datepicker.setDefaults( $.datepicker.regional[ "es" ] );
	
	$( "#txt-fe_inicial" ).datepicker({
		changeMonth: true,
		changeYear: true,
		maxDate: $("#txt-fe_final").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_final").datepicker("option", "minDate", selectedDate);
		},
	});

    $( "#txt-fe_final" ).datepicker({
    	changeMonth: true,
    	changeYear: true,
		minDate: $("#txt-fe_inicial").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_inicial").datepicker("option", "maxDate", selectedDate);
		}
    });

    //Cerrar message error bulma
    $( ".icon-delete" ).click(function(){
		$( ".MsgData" ).hide();
		$( ".modal-cintillo" ).hide();
		$( ".modal-PreciVentaMargen" ).hide();
    })

    $( ".delete" ).click(function(){
    	$( ".MsgData" ).hide();
		$( ".MsgError" ).hide();
		$( ".MsgDataRVC" ).hide();
    })

	// Cargador
	var block_loding_modal = $('<div class="block-loading" />');

    //ocultar Messages Error
    $( ".help" ).hide();
    $( ".MsgData" ).hide();

	//Validar Campos del CRUD
	ValidarCamposCRUD();

	$( "#cbo-Nu_Almacen" ).change(function(){
		ValidarCamposCRUD();
	})

	// Validar Button
	$( "#btn-save" ).prop( "disabled", true );
	$( ".btn-close" ).prop( "disabled", false );

	/* Vale de Creditos */
	var $Fe_Emision 	= $( "#txt-Fe_Emision" );
	var Ch_Documento 	= $( "#txt-Ch_Documento" );

	if(Ch_Documento.val() !== undefined){
	    $("#txt-Ch_Documento").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 45, 109]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
	}

	var Nu_Odometro 	= $( "#txt-Nu_Odometro" );

	if(Nu_Odometro.val() !== undefined){
	    $("#txt-Nu_Odometro").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
	}

	var Nu_Odometro 	= $( "#txt-Nu_Odometro" );

	if(Nu_Odometro.val() !== undefined){
	    $("#txt-Nu_Odometro").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
	}

/*
	var Nu_Cantidad 	= $( "#txt-Nu_Cantidad" );

	if(Nu_Cantidad.val() !== undefined){
	    $("#txt-Nu_Cantidad").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
		Nu_Cantidad.keyup (function() { calculateAmounts() });
	}
*/
	var Nu_Cantidad 	= $( "#txt-Nu_Cantidad" );

	if(Nu_Cantidad.val() !== undefined){
		Nu_Cantidad.keyup (function() { calculateAmounts() });
	}

	var Nu_Precio 	= $( "#txt-Nu_Precio" );

	if(Nu_Precio.val() !== undefined){
	    $("#txt-Nu_Precio").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
		Nu_Precio.keyup (function() { calculateAmounts() });
	}

	var Nu_Total 	= $( "#txt-Nu_Total" );

	if(Nu_Total.val() !== undefined){
	    $("#txt-Nu_Total").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
	}

	var Nu_IGV 	= $( "#txt-Nu_IGV" );

	var Nu_Serie_Compra 	= $( "#txt-Nu_Serie_Compra" );
	if(Nu_Serie_Compra.val() !== undefined)
		Nu_Serie_Compra.blur (function() { autocompleteSerieCompraCeros() });

	var Nu_Serie_Compra_Referencia 	= $( "#txt-Nu_Serie_Compra_Referencia" );
	if(Nu_Serie_Compra_Referencia.val() !== undefined)
		Nu_Serie_Compra_Referencia.blur (function() { autocompleteSerieCompraReferenciaCeros });

	var Nu_Numero_Compra 	= $( "#txt-Nu_Numero_Compra" );
	if(Nu_Numero_Compra.val() !== undefined){
	    $("#txt-Nu_Numero_Compra").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
		Nu_Numero_Compra.blur (function() { autocompleteNumeroCompraCeros() });
	}

	var Nu_Numero_Compra_Referencia 	= $( "#txt-Nu_Numero_Compra_Referencia" );
	if(Nu_Numero_Compra_Referencia.val() !== undefined){
	    $("#txt-Nu_Numero_Compra_Referencia").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
		Nu_Numero_Compra_Referencia.blur (function() { autocompleteNumeroCompraReferenciaCeros() });
	}

	/* Registro Compras */
	var Nu_Dias_Vencimiento_RC 	= $( "#txt-Nu_Dias_Vencimiento_RC" );
	if(Nu_Dias_Vencimiento_RC.val() !== undefined){
	    $("#txt-Nu_Dias_Vencimiento_RC").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
	    Nu_Dias_Vencimiento_RC.keyup (function() { calculateDuoDate(Nu_Dias_Vencimiento_RC) });
		//Nu_Dias_Vencimiento_RC.keyup (function() { calcularFechaVencimientoXDias(Nu_Dias_Vencimiento_RC) });
	}

	var Nu_Percepcion_RC 	= $( "#txt-Nu_Percepcion_RC" );
	if(Nu_Percepcion_RC.val() !== undefined){
	    $("#txt-Nu_Percepcion_RC").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
	}

	var Nu_Inafecto_IGV_RC 	= $( "#txt-Nu_Inafecto_IGV_RC" );
	if(Nu_Inafecto_IGV_RC.val() !== undefined){
	    $("#txt-Nu_Inafecto_IGV_RC").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
	}

/*
	var $Fe_Emision_Registro_Compra = $( "#txt-Fe_Emision_Registro_Compra" );
	if($Fe_Emision_Registro_Compra.val() !== undefined) {
	    $( "#txt-Fe_Emision_Registro_Compra" ).datepicker({changeMonth: true, changeYear: true});
	}
*/

	if($Fe_Emision.val() !== undefined) {
	    $( "#txt-Fe_Emision" ).datepicker({
			changeMonth: true,
			changeYear: true,
			onSelect:function(Fe_Emision, obj){
				var nu_almacen = $( "#cbo-Nu_Almacen option:selected" ).val();

				if(nu_almacen == ''){
					$( '.help' ).show();
					$( "#cbo-Nu_Almacen" ).closest('.column').find('.help').html('Seleccionar almacen');
					$( "#cbo-Nu_Almacen" ).closest('.column').find('.help').addClass('is-danger');
					$( "#txt-Fe_Emision" ).val('');
				}else{
					/* Cargador */
					$( "#template-Vale_Credito_Agregar" ).prepend(block_loding_modal);

					$.post( "../assets/helper.php", {
		            	accion 		: 'getTipoVenta',
			       		nu_almacen 	: nu_almacen,
				        Fe_Emision 	: Fe_Emision,
					}, function(data){
						if(data.status == 'success'){
							var arrTipoVenta = data.arrTipoVenta;

				    		$("select[name=cbo-no_tipo_venta]").html('<option value="">Seleccionar..</option>');
							for (var i = 0; i < arrTipoVenta.length; i++)
								$("select[name=cbo-no_tipo_venta]").append(new Option(arrTipoVenta[i]["tipo"], arrTipoVenta[i]["nu_tipo_venta"]));

							$( ".help" ).hide();
						}else{
							$("select[name=cbo-no_tipo_venta]").html('<option value="">No hay datos</option>');
							ValidarCamposCRUD();
						}
						$( ".block-loading" ).remove();
					}, 'JSON');
				}
			}
	    })
	}

	var $Fe_Emision_Compra = $( "#txt-Fe_Emision_Compra" );

	if($Fe_Emision_Compra.val() !== undefined) {

		/* Obtener almacen interno según el tipo de Naturaleza */
		var Nu_Naturaleza_Movimiento_Inventario = $( "#txt-Nu_Naturaleza_Movimiento_Inventario" );

		//if(Nu_Naturaleza_Movimiento_Inventario.val() !== undefined){
			var Nu_Almacen_Interno 	= "";
			var Nu_Almacen_Origen 	= $( ".Nu_Almacen_Origen" );
		   	var Nu_Almacen_Destino 	= $( ".Nu_Almacen_Destino" );
		   	
			if(Nu_Naturaleza_Movimiento_Inventario.val() == 1 || Nu_Naturaleza_Movimiento_Inventario.val() == 2)
		   		Nu_Almacen_Interno = Nu_Almacen_Destino.val();
		   	else
		   		Nu_Almacen_Interno = Nu_Almacen_Origen.val();

			/* Solo se mostrará el Registro de Compras, para los documentos permitidos PLE */
			$( "#cbo-Nu_Tipo_Documento_Compra" ).change(function(){
				$( ".div-PrincipalRegistroCompras" ).hide();
				$( ".div-ReferenciaDocumentoOriginal" ).hide();
				if($Fe_Emision_Compra.val() != ''){
					Nu_Tipo_Documento_Compra = $.trim($( "#cbo-Nu_Tipo_Documento_Compra option:selected" ).val());
					//if(Nu_Naturaleza_Movimiento_Inventario.val() == 2 && (Nu_Tipo_Documento_Compra == '09' || Nu_Tipo_Documento_Compra == '10' || Nu_Tipo_Documento_Compra == '11' || Nu_Tipo_Documento_Compra == '35' || Nu_Tipo_Documento_Compra == '20')){
					if( Nu_Tipo_Documento_Compra == '10' || Nu_Tipo_Documento_Compra == '11' || Nu_Tipo_Documento_Compra == '35' || Nu_Tipo_Documento_Compra == '20' ){
						$( ".div-PrincipalRegistroCompras" ).show();

						if (Nu_Tipo_Documento_Compra == '11' || Nu_Tipo_Documento_Compra == '20') {
							$( ".div-ReferenciaDocumentoOriginal" ).show();
							$( "#cbo-Nu_Tipo_Documento_Compra_Referencia" ).addClass('required');
							$( "#txt-Nu_Serie_Compra_Referencia" ).addClass('required');
							$( "#txt-Nu_Numero_Compra_Referencia" ).addClass('required');
						}else{
							$( "#cbo-Nu_Tipo_Documento_Compra_Referencia" ).removeClass('required');
							$( "#txt-Nu_Serie_Compra_Referencia" ).removeClass('required');
							$( "#txt-Nu_Numero_Compra_Referencia" ).removeClass('required');
						}
					}
				}else{
					$( "select" ).each( function () {
						var $combobox = $('#' + $( this )['context']['id']);
						$combobox.val($combobox.children('option:first').val());
					});

					$( '.help' ).show();
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').html('Seleccionar F. Emision');
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').addClass('is-danger');
				}
			})
	   	//}

	    $( "#txt-Fe_Emision_Compra" ).datepicker({
			changeMonth: true,
			changeYear: true,
			onSelect:function(Fe_Emision_Compra, obj){
				$( "#btn-addProducto" ).prop( "disabled", true );
				
			    var _FE = Fe_Emision_Compra.split('/');
			    var _Fe_Emision_Compra = _FE[2] + '-' + _FE[1] + '-' + _FE[0];

				if (Nu_Almacen_Origen.val() == '') {
					$( '.help' ).show();
					$( ".Nu_Almacen_Origen" ).closest('.column').find('.help').html('Seleccionar almacen');
					$( ".Nu_Almacen_Origen" ).closest('.column').find('.help').addClass('is-danger');
					$( "#txt-Fe_Emision_Compra" ).val('');
				} else if (Nu_Almacen_Destino.val() == '') {
					$( '.help' ).show();
					$( ".Nu_Almacen_Destino" ).closest('.column').find('.help').html('Seleccionar almacen');
					$( ".Nu_Almacen_Destino" ).closest('.column').find('.help').addClass('is-danger');
					$( "#txt-Fe_Emision_Compra" ).val('');
				} else if (_Fe_Emision_Compra > $( '#txt-Fe_Sistema' ).val()) {
					$( '.help' ).show();
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').html('La <b>Fecha Emision</b> no debe ser mayor a la <b>Fecha Sistema</b>');
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').addClass('is-danger');
				} else if (_Fe_Emision_Compra < $( '#txt-Fe_Sistema_Sistema' ).val()) {
					$( '.help' ).show();
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').html('La <b>Fecha Emision</b> no debe ser menor a la <b>Fecha Inicio Sistema</b>');
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').addClass('is-danger');
				} else if (_FE[1] < $( '#txt-Fe_Mes' ).val() || _FE[1] > $( '#txt-Fe_Mes' ).val()) {
					$( '.help' ).show();
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').html('El <b>mes</b> debe ser igual al mes de la <b>Fecha Sistema</b>');
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').addClass('is-danger');
				} else if (_FE[2] <= $( '#txt-Fe_Cierre_Year' ).val() && _FE[1] <= $( '#txt-Fe_Cierre_Month' ).val()) {
					$( '.help' ).show();
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').html('<b>Periodo de Inventario Cerrado el Año: ' + $( '#txt-Fe_Cierre_Year' ).val() + ' y Mes: ' + $( '#txt-Fe_Cierre_Month' ).val());
					$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').addClass('is-danger');
				} else {
					$( '.help' ).hide();
					$('#txt-Fe_Emision_Compra').closest('.column').find('.help').html('');
					$( "#template-Movimiento_Inventario_Agregar" ).prepend(block_loding_modal);
					$.post( "../assets/helper.php", {
		            	accion 				: 'verifyValidationDayConsolidacion',
		            	Nu_Almacen_Interno 	: Nu_Almacen_Interno, 
				        Fe_Emision_Compra 	: _Fe_Emision_Compra,
					}, function(response){
						if(response.data[0] == 1){//Consolidado
							$( ".help" ).show();
							$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').html('<b>Dia: ' + Fe_Emision_Compra + ' consolidado</b>, <br/>seleccionar otra fecha');
							$( "#txt-Fe_Emision_Compra" ).closest('.column').find('.help').addClass('is-danger');
		            	} else {
							ValidarCamposCRUD();
							$( "#btn-addProducto" ).prop( "disabled", false );
							$( '#txt-Fe_Emision_Compra' ).closest('.column').find('.help').html('');
							$( ".help" ).hide();
							//$( '#txt-Fe_Vencimiento_RC' ).val($( "#txt-Fe_Emision_Compra" ).val());
							$( '#txt-Fe_Flete' ).val($( "#txt-Fe_Emision_Compra" ).val());
							$( '#txt-Fe_Emision_Datos_Complementarios' ).val($( "#txt-Fe_Emision_Compra" ).val());
							$( '#txt-Fe_Recepcion' ).val($( "#txt-Fe_Emision_Compra" ).val());

						    var $Fe_Sistema = $("#txt-Fe_Sistema").val().split('-');
						    var $Fe_Sistema = $Fe_Sistema[2] + '/' + $Fe_Sistema[1] + '/' + $Fe_Sistema[0];

							
						    $( "#txt-Fe_Flete" ).datepicker({
								changeMonth: true,
								changeYear: true,
								minDate: $("#txt-Fe_Emision_Compra").val(),
							})

							$( '#txt-Fe_Vencimiento_Pedido' ).val($Fe_Sistema);
						    $( "#txt-Fe_Vencimiento_Pedido" ).datepicker({
								changeMonth: true,
								changeYear: true,
								minDate: $Fe_Sistema,
							})

						    $( "#txt-Fe_Recepcion" ).datepicker({
								changeMonth: true,
								changeYear: true,
								minDate: $("#txt-Fe_Emision_Compra").val(),
							})

							// Fecha de emision de Registro de Compras - 19/07/2018
							$( '#txt-Fe_Emision_Registro_Compra' ).val($( "#txt-Fe_Emision_Compra" ).val());

						    $( "#txt-Fe_Emision_Registro_Compra" ).datepicker({
								changeMonth: true,
								changeYear: true,
								maxDate: $("#txt-Fe_Emision_Compra").val(),
							})

							//calculateDuoDate();
		            	}
						$( ".block-loading" ).remove();
					}, 'JSON');

					$.post( "../assets/helper.php", {
		            	accion 				: 'getTipoCambio',
				        Fe_Emision_Compra 	: _Fe_Emision_Compra,
					}, function(response){
						//console.log('tipo de cambio -> ' + response.fTipoCambio);
						$( "#txt-Nu_Tipo_Cambio_Compra" ).val( response.fTipoCambio );
					}, 'JSON');
				}
			}
	    })

	    $( "#txt-Fe_Emision_Registro_Compra" ).datepicker({
			changeMonth: true,
			changeYear: true,
			onSelect:function(Fe_Emision_Compra, obj){
				calculateDuoDate(0);
			}
		})

	    var Nu_Tipo_Movimiento_Inventario = $( "#txt-Nu_Tipo_Movimiento_Inventario" ).val();
		var Nu_Cantidad_Negativo = 109;
		//var Nu_Cantidad_Negativo = 190;
	    /* validacion para colocar negativo solo cuando sea tipo de movimiento Regularizacion */
	    //if (Nu_Tipo_Movimiento_Inventario == '18' || Nu_Tipo_Movimiento_Inventario == '18' || Nu_Tipo_Movimiento_Inventario == '18') {
	    //	Nu_Cantidad_Negativo = 109;
	    //}

	   	/* Campos de texto para ingresar Cantida, Costo y total del Articulo */
		/*Campo cantidad*/
		var Nu_Cantidad_Compra 	= $( "#txt-Nu_Cantidad_Compra" );
		if(Nu_Cantidad_Compra.val() !== undefined){
		    $("#txt-Nu_Cantidad_Compra").keydown(function (e) {
		        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 45, Nu_Cantidad_Negativo]) !== -1 ||
		            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
		            (e.keyCode >= 35 && e.keyCode <= 40)) {
		                 return;
		        }
		        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
		            e.preventDefault();
		    });
			Nu_Cantidad_Compra.keyup (function() { calculateTotalSIGVCompra(Nu_IGV) });
		}

		/*Campo costo*/
		var Nu_Costo_Unitario 	= $( "#txt-Nu_Costo_Unitario" );
		if(Nu_Costo_Unitario.val() !== undefined){
		    $("#txt-Nu_Costo_Unitario").keydown(function (e) {
		        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 45, Nu_Cantidad_Negativo]) !== -1 ||
		            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
		            (e.keyCode >= 35 && e.keyCode <= 40)) {
		                 return;
		        }
		        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
		            e.preventDefault();
		    });
			Nu_Costo_Unitario.keyup (function() { calculateAmountsCompra(Nu_IGV) });
		}

		/*Campo total sin igv*/
		var Nu_Total_SIGV 	= $( "#txt-Nu_Total_SIGV" );
		if(Nu_Total_SIGV.val() !== undefined){
		    $("#txt-Nu_Total_SIGV").keydown(function (e) {
		        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 45, Nu_Cantidad_Negativo]) !== -1 ||
		            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
		            (e.keyCode >= 35 && e.keyCode <= 40)) {
		                 return;
		        }
		        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
		            e.preventDefault();
		    });
			Nu_Total_SIGV.keyup (function() { calculateCostoUnitarioCompra(Nu_IGV) });
		}

		/*Campo total con igv*/
		var Nu_Total_CIGV = $('#label-Nu_Total_CIGV');
		if (Nu_Total_CIGV.val() !== undefined) {
			Nu_Total_CIGV.keydown(function (e) {
				if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 45, Nu_Cantidad_Negativo]) !== -1 ||
					(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
					(e.keyCode >= 35 && e.keyCode <= 40)) {
					return;
				}
				if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
					e.preventDefault();
			});
			Nu_Total_CIGV.keyup (function() { calculateMontosPorTotalIGV(Nu_IGV) });
		}
	}

	var $Fe_Vencimiento_RC = $( "#txt-Fe_Vencimiento_RC" );
	if($Fe_Vencimiento_RC.val() !== undefined) {
	    $( "#txt-Fe_Vencimiento_RC" ).datepicker({changeMonth: true, changeYear: true});
	}

	var $Fe_Periodo_RC = $( "#txt-Fe_Periodo_RC" );
	if($Fe_Periodo_RC.val() !== undefined) {
	    $( "#txt-Fe_Periodo_RC" ).datepicker({changeMonth: true, changeYear: true});
	}

	var $no_tipo_venta = $( "#cbo-no_tipo_venta" );
	if($no_tipo_venta.val() !== undefined){
	   	$( "#cbo-no_tipo_venta" ).change(function(){
			var nu_almacen 		= $( "#cbo-Nu_Almacen option:selected" ).val();
	   		var Fe_Emision 		= $('#txt-Fe_Emision').val();
	   		var nu_tipo_venta 	= $( "#cbo-no_tipo_venta option:selected" ).val();

			ValidarCamposCRUD();

			if(nu_tipo_venta.length > 0){
				/* Cargador */
				$( "#template-Vale_Credito_Agregar" ).prepend(block_loding_modal);

				$( "#btn-save" ).prop( "disabled", false );

				$( "#cbo-Nu_Lado" ).addClass('combobox');

				$.post( "../assets/helper.php", {
		        	accion 			: 'getValuesFechaTCL',//TCL = Get Values: Turno, Caja, Lado
		       		nu_almacen 		: nu_almacen,
			        Fe_Emision 		: Fe_Emision,
			        nu_tipo_venta 	: nu_tipo_venta
				}, function(data){

					var arrTurnos = data.arrTurnos;
					var arrCajas = data.arrCajas;
					var arrLados = data.arrLados;

		    		$("select[name=cbo-Nu_Turno]").html('<option value="">Seleccionar..</option>');
					for (var i = 1; i <= arrTurnos[0].turno; i++)
						$("select[name=cbo-Nu_Turno]").append(new Option(i, i));

		    		$("select[name=cbo-Nu_Caja]").html('<option value="">Seleccionar..</option>');
					for (var i = 0; i < arrCajas.length; i++)
						$("select[name=cbo-Nu_Caja]").append(new Option(arrCajas[i]["caja"], arrCajas[i]["caja"]));

					if(arrLados.length > 0){
						$( ".cbo-Nu_Lado" ).show();
			    		$("select[name=cbo-Nu_Lado]").html('<option value="">Seleccionar..</option>');
						for (var i = 0; i < arrLados.length; i++)
							$("select[name=cbo-Nu_Lado]").append(new Option(arrLados[i]["pump"], arrLados[i]["pump"]));
					}else
						$( ".cbo-Nu_Lado" ).hide();

					$( ".help" ).hide();

					$( ".block-loading" ).remove();
				}, 'JSON');
			}else{
				$("select[name=cbo-Nu_Turno]").html('<option value=""></option>');
				$("select[name=cbo-Nu_Caja]").html('<option value=""></option>');
				
				$( "#cbo-Nu_Lado" ).removeClass('combobox');
				$("select[name=cbo-Nu_Lado]").html('<option value=""></option>');
			}
		})
	}

	var $Nu_Turno = $( "#cbo-Nu_Turno" );
	if($Nu_Turno.val() !== undefined){
	   	$( "#cbo-Nu_Turno" ).change(function(){
			var Nu_Almacen 	= $( "#cbo-Nu_Almacen option:selected" ).val();
	   		var Fe_Emision 	= $('#txt-Fe_Emision').val();
	   		var Nu_Turno 	= $( "#cbo-Nu_Turno option:selected" ).val();

			if(Nu_Turno > 0){
				$.post( "../assets/helper.php", {
		        	accion 		: 'verifyValidationDayAndTurnoConsolidado',
		       		nu_almacen 	: Nu_Almacen,
			        Fe_Emision 	: Fe_Emision,
			        Nu_Turno 	: Nu_Turno
				}, function(response){
					if(response.data[0] == 1){//Consolidado
						$( ".help" ).show();
						$( "#cbo-Nu_Turno" ).closest('.column').find('.help').html('<b>Dia: ' + Fe_Emision + ' y turno consolidado</b>');
						$( "#cbo-Nu_Turno" ).closest('.column').find('.help').addClass('is-danger');
						$( "#btn-save" ).prop( "disabled", true );
	            	} else {
						$('#cbo-Nu_Turno').closest('.column').find('.help').html('');
						$( ".help" ).hide();
						$( "#btn-save" ).prop( "disabled", false );
	            	}
				}, 'JSON');
			}
	   	})
	}

});

function ValidarCamposCRUD(){

	var $Fe_Emision = $( "#txt-Fe_Emision" );
	var $Ch_Documento = $( "#txt-Ch_Documento" );
	var $Ch_Documento_Manual = $( "#txt-Ch_Documento_Manual" );

	var $no_tipo_venta = $( "#cbo-no_tipo_venta" );
	var $Nu_Turno = $( "#cbo-Nu_Turno" );
	var $Nu_Caja = $( "#cbo-Nu_Caja" );
	var $Nu_Lado = $( "#cbo-Nu_Lado" );

	var $No_Razsocial = $( "#txt-No_Razsocial" );
	var $No_Placa = $( "#txt-No_Placa" );
	var $Nu_Tarjeta = $( "#txt-Nu_Tarjeta" );
	var $Nu_Documento_Identidad_Chofer = $( "#txt-Nu_Documento_Identidad_Chofer" );
	//var $No_Chofer = $( "#txt-No_Chofer" );
	var $Nu_Odometro = $( "#txt-Nu_Odometro" );
	var $No_Producto_Detalle = $( "#txt-No_Producto_Detalle" );
	var $Nu_Cantidad = $( "#txt-Nu_Cantidad" );
	var $Nu_Precio = $( "#txt-Nu_Precio" );
	var $Nu_Total = $( "#txt-Nu_Total" );

	/*Datos de compra*/
	var $Fe_Emision_Compra = $( "#txt-Fe_Emision_Compra" );
	var $Nu_Tipo_Documento_Compra = $( "#cbo-Nu_Tipo_Documento_Compra" );
	var $Nu_Serie_Compra = $( "#txt-Nu_Serie_Compra" );
	var $Nu_Numero_Compra = $( "#txt-Nu_Numero_Compra" );
	var $No_Proveedor = $( "#txt-No_Proveedor" );
	var $No_Producto_Detalle_Compra = $( "#txt-No_Producto_Detalle_Compra" );
	var $Nu_Cantidad_Compra = $( "#txt-Nu_Cantidad_Compra" );
	var $Nu_Costo_Unitario = $( "#txt-Nu_Costo_Unitario" );
	var $Nu_Total_SIGV = $( "#txt-Nu_Total_SIGV" );
	var $Nu_Total_CIGV = $( "#txt-Nu_Total_CIGV" );
	var $chk_datosComplementarios = $( "#chk-datosComplementarios" );
	var $chk_pedido_vencimiento = $( "#chk-pedido_vencimiento" );

	if($Fe_Emision.val() !== undefined)
		$Fe_Emision.prop( "disabled", false );

	if($Ch_Documento.val() !== undefined)
		$Ch_Documento.prop( "disabled", false );

	if($Ch_Documento_Manual.val() !== undefined)
		$Ch_Documento_Manual.prop( "disabled", false );

	if($no_tipo_venta.val() !== undefined)
		$no_tipo_venta.prop( "disabled", false );

	if($Nu_Turno.val() !== undefined)
		$Nu_Turno.prop( "disabled", false );

	if($Nu_Caja.val() !== undefined)
		$Nu_Caja.prop( "disabled", false );

	if($Nu_Lado.val() !== undefined)
		$Nu_Lado.prop( "disabled", false );

	if($No_Razsocial.val() !== undefined)
		$No_Razsocial.prop( "disabled", false );

	if($No_Placa.val() !== undefined)
		$No_Placa.prop( "disabled", false );

	if($Nu_Tarjeta.val() !== undefined)
		$Nu_Tarjeta.prop( "disabled", false );

	if($Nu_Documento_Identidad_Chofer.val() !== undefined)
		$Nu_Documento_Identidad_Chofer.prop( "disabled", false );

	//if($No_Chofer.val() !== undefined)
	//	$No_Chofer.prop( "disabled", false );

	if($Nu_Odometro.val() !== undefined)
		$Nu_Odometro.prop( "disabled", false );

	if($No_Producto_Detalle.val() !== undefined)
		$No_Producto_Detalle.prop( "disabled", false );

	if($Nu_Cantidad.val() !== undefined)
		$Nu_Cantidad.prop( "disabled", false );

	if($Nu_Precio.val() !== undefined)
		$Nu_Precio.prop( "disabled", false );

	if($Nu_Total.val() !== undefined)
		$Nu_Total.prop( "disabled", false );

	/*Datos de Compra*/
	if($Fe_Emision_Compra.val() !== undefined)
		$Fe_Emision_Compra.prop( "disabled", false );

	if($Nu_Tipo_Documento_Compra.val() !== undefined)
		$Nu_Tipo_Documento_Compra.prop( "disabled", false )

	if($Nu_Serie_Compra.val() !== undefined)
		$Nu_Serie_Compra.prop( "disabled", false );
	
	if($Nu_Numero_Compra.val() !== undefined)
		$Nu_Numero_Compra.prop( "disabled", false );

	if($No_Proveedor.val() !== undefined)
		$No_Proveedor.prop( "disabled", false );

	if($No_Producto_Detalle_Compra.val() !== undefined)
		$No_Producto_Detalle_Compra.prop( "disabled", false );

	if($Nu_Cantidad_Compra.val() !== undefined)
		$Nu_Cantidad_Compra.prop( "disabled", false );

	if($Nu_Costo_Unitario.val() !== undefined)
		$Nu_Costo_Unitario.prop( "disabled", false );

	if($Nu_Total_SIGV.val() !== undefined)
		$Nu_Total_SIGV.prop( "disabled", false );

	if($Nu_Total_CIGV.val() !== undefined)
		$Nu_Total_CIGV.prop( "disabled", false );

	if($chk_datosComplementarios.val() !== undefined)
		$chk_datosComplementarios.prop( "disabled", false );

	if($chk_pedido_vencimiento.val() !== undefined)
		$chk_pedido_vencimiento.prop( "disabled", false );

	if($("#cbo-Nu_Almacen").val() == ''){
		if($Fe_Emision.val() !== undefined)
			$Fe_Emision.prop( "disabled", true );

		if($Ch_Documento.val() !== undefined)
			$Ch_Documento.prop( "disabled", true );

		if($Ch_Documento_Manual.val() !== undefined)
			$Ch_Documento_Manual.prop( "disabled", true );

		if($no_tipo_venta.val() !== undefined)
			$no_tipo_venta.prop( "disabled", true );

		if($Nu_Turno.val() !== undefined)
			$Nu_Turno.prop( "disabled", true );

		if($Nu_Caja.val() !== undefined)
			$Nu_Caja.prop( "disabled", true );

		if($Nu_Lado.val() !== undefined)
			$Nu_Lado.prop( "disabled", true );

		if($No_Razsocial.val() !== undefined)
			$No_Razsocial.prop( "disabled", true );

		if($No_Placa.val() !== undefined)
			$No_Placa.prop( "disabled", true );

		if($Nu_Tarjeta.val() !== undefined)
			$Nu_Tarjeta.prop( "disabled", true );

		if($Nu_Documento_Identidad_Chofer.val() !== undefined)
			$Nu_Documento_Identidad_Chofer.prop( "disabled", true );

		//if($No_Chofer.val() !== undefined)
		//	$No_Chofer.prop( "disabled", true );

		if($Nu_Odometro.val() !== undefined)
			$Nu_Odometro.prop( "disabled", true );

		if($No_Producto_Detalle.val() !== undefined)
			$No_Producto_Detalle.prop( "disabled", true );

		if($Nu_Cantidad.val() !== undefined)
			$Nu_Cantidad.prop( "disabled", true );

		if($Nu_Precio.val() !== undefined)
			$Nu_Precio.prop( "disabled", true );

		if($Nu_Total.val() !== undefined)
			$Nu_Total.prop( "disabled", true );
	}

	if($no_tipo_venta.val() !== undefined){
		if($no_tipo_venta.val() == ''){

			if($Nu_Turno.val() !== undefined)
				$Nu_Turno.prop( "disabled", true );

			if($Nu_Caja.val() !== undefined)
				$Nu_Caja.prop( "disabled", true );

			if($Nu_Lado.val() !== undefined)
				$Nu_Lado.prop( "disabled", true );

			if($No_Razsocial.val() !== undefined)
				$No_Razsocial.prop( "disabled", true );

			if($No_Placa.val() !== undefined)
				$No_Placa.prop( "disabled", true );

			if($Nu_Tarjeta.val() !== undefined)
				$Nu_Tarjeta.prop( "disabled", true );

			if($Nu_Documento_Identidad_Chofer.val() !== undefined)
				$Nu_Documento_Identidad_Chofer.prop( "disabled", true );

			//if($No_Chofer.val() !== undefined)
			//	$No_Chofer.prop( "disabled", true );

			if($Nu_Odometro.val() !== undefined)
				$Nu_Odometro.prop( "disabled", true );

			if($No_Producto_Detalle.val() !== undefined)
				$No_Producto_Detalle.prop( "disabled", true );

			if($Nu_Cantidad.val() !== undefined)
				$Nu_Cantidad.prop( "disabled", true );

			if($Nu_Precio.val() !== undefined)
				$Nu_Precio.prop( "disabled", true );

			if($Nu_Total.val() !== undefined)
				$Nu_Total.prop( "disabled", true );

			$( "#btn-save" ).prop( "disabled", true );
		}
	}

	/*Datos de compra*/
	if($Fe_Emision_Compra.val() == ''){

		if($Nu_Tipo_Documento_Compra.val() !== undefined)
			$Nu_Tipo_Documento_Compra.prop( "disabled", true )

		if($Nu_Serie_Compra.val() !== undefined)
			$Nu_Serie_Compra.prop( "disabled", true );

		if($Nu_Numero_Compra.val() !== undefined)
			$Nu_Numero_Compra.prop( "disabled", true );

		if($No_Proveedor.val() !== undefined)
			$No_Proveedor.prop( "disabled", true );

		if($No_Producto_Detalle_Compra.val() !== undefined)
			$No_Producto_Detalle_Compra.prop( "disabled", true );

		if($Nu_Cantidad_Compra.val() !== undefined)
			$Nu_Cantidad_Compra.prop( "disabled", true );

		if($Nu_Costo_Unitario.val() !== undefined)
			$Nu_Costo_Unitario.prop( "disabled", true );

		if($Nu_Total_SIGV.val() !== undefined)
			$Nu_Total_SIGV.prop( "disabled", true );

		if($Nu_Total_CIGV.val() !== undefined)
			$Nu_Total_CIGV.prop( "disabled", true );

		if($chk_datosComplementarios.val() !== undefined)
			$chk_datosComplementarios.prop( "disabled", true );
		
		if($chk_pedido_vencimiento.val() !== undefined)
			$chk_pedido_vencimiento.prop( "disabled", true );
	}

}
/*
function verifyExistDocument(block_loding_modal, $Fe_Emision){

	var url = "../assets/helper.php";
	if($Fe_Emision.length != ''){
	$("input[name=Ch_Documento]").change(function() {

		$( "#template-Vale_Credito_Agregar" ).prepend(block_loding_modal);

		$.ajax({
            url			: url,
            type		: "POST",
            dataType	: "JSON",
            data: {
		    	accion 			: 'verifyExistDocument',//Verificar si existe documento
		   		nu_almacen 		: $( "#cbo-Nu_Almacen option:selected" ).val(),
		        Fe_Emision 		: $( "#txt-Fe_Emision").val(),
		        Ch_Documento 	: $( "#txt-Ch_Documento" ).val()
            },
            success: function (data) {
            	if (data[0].nu_existe_documento == '1'){//Existe
					$( ".help" ).show();
					$( "#txt-Ch_Documento" ).closest('.column').find('.help').html('Ya existe ticket: ' + $( "#txt-Ch_Documento" ).val());
					$( "#txt-Ch_Documento" ).closest('.column').find('.help').addClass('is-danger');
					$( "#btn-save" ).prop( "disabled", true );
            	} else {
					$('#txt-Ch_Documento').closest('.column').find('.help').html('');
					$( ".help" ).hide();
					$( "#btn-save" ).prop( "disabled", false );
            	}
				$( ".block-loading" ).remove();
            }
        })
	})
	}
}
*/

function calculateAmounts(){
	console.log('calculateAmounts');

	var Nu_Cantidad = null;
    var Nu_Precio 	= null;
    
    Nu_Cantidad = $( "#txt-Nu_Cantidad" ).val();
    Nu_Precio 	= $( "#txt-Nu_Precio" ).val();

	var re = /-/;
	var str = Nu_Cantidad;

	if ( str.search(re) == -1 ) {
		console.log('sin signo');
    	if(Nu_Cantidad > 0 && Nu_Precio > 0) {
        	$( "#txt-Nu_Total" ).val(parseFloat(Nu_Cantidad * parseFloat(Nu_Precio).toFixed(3)).toFixed(3));
    	}
	} else {
		console.log('negativo');
        var fCantidad = str.split('-');
        fCantidad = fCantidad[1];
		if (Nu_Precio > 0) {        
       		$( "#txt-Nu_Total" ).val(parseFloat(fCantidad * parseFloat(Nu_Precio).toFixed(3)).toFixed(3));
       		$( "#txt-Nu_Total" ).val( '-' + $( "#txt-Nu_Total" ).val() );
       	}
	}

}

function calculateAmountsCompra(Nu_IGV){
	console.log('calculateAmountsCompra');

	var Nu_Cantidad_Compra 	= null;
    var Nu_Costo_Unitario 	= null;

    Nu_Cantidad_Compra 	= $( "#txt-Nu_Cantidad_Compra" ).val();
    Nu_Costo_Unitario 	= $( "#txt-Nu_Costo_Unitario" ).val();

	//if(Math.abs(Nu_Costo_Unitario) > 0){
	if ( Math.abs(Nu_Cantidad_Compra) > 0 && Math.abs(Nu_Costo_Unitario) > 0 ){
        $( "#txt-Nu_Total_SIGV" ).val(parseFloat(Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)).toFixed(4));
    	$( "#txt-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    	$( "#label-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    }
}

function calculateCostoUnitarioCompra(Nu_IGV){
	console.log('calculateCostoUnitarioCompra');	

	var Nu_Cantidad_Compra 	= null;
    var Nu_Costo_Unitario 	= null;
    var Nu_Total_SIGV 		= null;

    Nu_Cantidad_Compra 	= $( "#txt-Nu_Cantidad_Compra" ).val();
    Nu_Total_SIGV 		= $( "#txt-Nu_Total_SIGV" ).val();

	if(Math.abs(Nu_Cantidad_Compra) > 0 && Math.abs(Nu_Total_SIGV) > 0){
		Nu_Costo_Unitario = parseFloat(parseFloat(Nu_Total_SIGV).toFixed(4) / parseFloat(Nu_Cantidad_Compra).toFixed(4)).toFixed(6);
	    $( "#txt-Nu_Costo_Unitario" ).val(Nu_Costo_Unitario);
	    $( "#txt-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * Nu_Costo_Unitario) * Nu_IGV.val()).toFixed(4));
	    $( "#label-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * Nu_Costo_Unitario) * Nu_IGV.val()).toFixed(4));
    }
}

function calculateMontosPorTotalIGV(Nu_IGV) {
	console.log('calculateMontosPorTotalIGV');	

	//console.log('calculateMontosPorTotalIGV');
	var Nu_Cantidad_Compra = null;
	var Nu_Costo_Unitario = null;
	var Nu_Total_CIGV = null;

	Nu_Cantidad_Compra 	= $( "#txt-Nu_Cantidad_Compra" ).val();
	Nu_Total_CIGV 		= $( "#label-Nu_Total_CIGV" ).val();
	$( "#txt-Nu_Total_CIGV" ).val($( "#label-Nu_Total_CIGV" ).val());

	if(Math.abs(Nu_Cantidad_Compra) > 0 && Math.abs(Nu_Total_CIGV) > 0){
		Nu_Costo_Unitario = parseFloat(parseFloat(Nu_Total_CIGV).toFixed(4) / parseFloat(Nu_Cantidad_Compra).toFixed(4)).toFixed(6);
		Nu_Costo_Unitario = parseFloat(Nu_Costo_Unitario/Nu_IGV.val());
		//console.log(Nu_Costo_Unitario);
		$( "#txt-Nu_Costo_Unitario" ).val(Nu_Costo_Unitario.toFixed(4));
		$( "#txt-Nu_Total_SIGV" ).val(parseFloat((Nu_Cantidad_Compra * Nu_Costo_Unitario)).toFixed(4));
		$( "#label-Nu_Total_SIGV" ).val(parseFloat((Nu_Cantidad_Compra * Nu_Costo_Unitario)).toFixed(4));
	}
}

function calculateTotalSIGVCompra(Nu_IGV){
	console.log('calculateTotalSIGVCompra');

	var Nu_Cantidad_Compra 	= null;
    var Nu_Costo_Unitario 	= null;

    Nu_Cantidad_Compra 	= $( "#txt-Nu_Cantidad_Compra" ).val();
    Nu_Costo_Unitario 	= $( "#txt-Nu_Costo_Unitario" ).val();
    Nu_Total_SIGV 		= $( "#txt-Nu_Total_SIGV" ).val();

	if ( Math.abs(Nu_Cantidad_Compra) > 0 && Math.abs(Nu_Costo_Unitario) > 0 ){
        $( "#txt-Nu_Total_SIGV" ).val(parseFloat(Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)).toFixed(4));
    	$( "#txt-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    	$( "#label-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    }

    /*
	if(Math.abs(Nu_Cantidad_Compra) > 0 && Math.abs(Nu_Costo_Unitario) > 0 && Math.abs(Nu_Total_SIGV.length === 0)){
        $( "#txt-Nu_Total_SIGV" ).val(parseFloat(Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)).toFixed(4));
    	$( "#txt-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    	$( "#label-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    }else if(Math.abs(Nu_Cantidad_Compra) > 0 && Math.abs(Nu_Costo_Unitario) > 0 && Math.abs(Nu_Total_SIGV.length > 0)){
		Nu_Costo_Unitario = parseFloat(parseFloat(Nu_Total_SIGV).toFixed(4) / parseFloat(Nu_Cantidad_Compra).toFixed(4)).toFixed(6);
	    $( "#txt-Nu_Costo_Unitario" ).val(Nu_Costo_Unitario);
	    $( "#txt-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * Nu_Costo_Unitario) * Nu_IGV.val()).toFixed(4));
	    $( "#label-Nu_Total_CIGV" ).val(parseFloat((Nu_Cantidad_Compra * Nu_Costo_Unitario) * Nu_IGV.val()).toFixed(4));
    }
    */
}

function autocompleteSerieCompraCeros(){
    var Nu_Serie_Compra = null;
    Nu_Serie_Compra = $( "#txt-Nu_Serie_Compra" ).val();
    $( "#txt-Nu_Serie_Compra" ).val(('0000' + Nu_Serie_Compra).slice(-4));
}

function autocompleteSerieCompraReferenciaCeros(){
    var Nu_Serie_Compra_Referencia = null;
    Nu_Serie_Compra_Referencia = $( "#txt-Nu_Serie_Compra_Referencia" ).val();
    $( "#txt-Nu_Serie_Compra_Referencia" ).val(('0000' + Nu_Serie_Compra_Referencia).slice(-4));
}

function autocompleteNumeroCompraCeros(){
    var Nu_Numero_Compra = null;
    Nu_Numero_Compra = $( "#txt-Nu_Numero_Compra" ).val();
    $( "#txt-Nu_Numero_Compra" ).val(('00000000' + Nu_Numero_Compra).slice(-8));
}

function autocompleteNumeroCompraReferenciaCeros(){
    var Nu_Numero_Compra_Referencia = null;
    Nu_Numero_Compra_Referencia = $( "#txt-Nu_Numero_Compra_Referencia" ).val();
    $( "#txt-Nu_Numero_Compra_Referencia" ).val(('00000000' + Nu_Numero_Compra_Referencia).slice(-8));
}

/*
function calcularFechaVencimientoXDias(Nu_Dias_Vencimiento_RC){

	var Fe_Emision_Compra = $(' #txt-Fe_Emision_Compra' ).val();

    Fe_Emision_Compra = Fe_Emision_Compra.split('/');
    Fe_Emision_Compra = Fe_Emision_Compra[2] + '/' + Fe_Emision_Compra[1] + '/' + Fe_Emision_Compra[0];

	var Fe_Emision_Compra_New = Fe_Emision_Compra;

	var Fe_Actual = new Date(Fe_Emision_Compra_New);

	day 			= Fe_Actual.getDate();
	month 			= Fe_Actual.getMonth()+1;
	year 			= Fe_Actual.getFullYear();

	tiempo 			= Fe_Actual.getTime();
	milisegundos 	= parseInt(Nu_Dias_Vencimiento_RC.val() * 24 * 60 * 60 * 1000);
	total 			= Fe_Actual.setTime(tiempo+milisegundos);
	day 			= Fe_Actual.getDate();
	month 			= Fe_Actual.getMonth()+1;
	year 			= Fe_Actual.getFullYear();

	if(month.toString().length < 2)
		month = "0".concat(month);

	if(day.toString().length<2)
		day = "0".concat(day);

	var Fe_Vencimiento_RC = day + '/' + month + '/' + year;	
	$(' #txt-Fe_Vencimiento_RC' ).val(Fe_Vencimiento_RC);
}
*/

function isNumberKey(evt){
	var charCode = (evt.which) ? evt.which : evt.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;
	return true;
}

function calculateDuoDate(iDiasVencimiento){
	var iDiasVencimiento = parseInt($( '#txt-Nu_Dias_Vencimiento_RC' ).val());
	//var dFechaVencimiento = sTypeDate('fecha_dmy', $( '#txt-Fe_Emision_Compra' ).val(), '/')
	var dFechaVencimiento = sTypeDate('fecha_dmy', $( '#txt-Fe_Emision_Registro_Compra' ).val(), '/');
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

	$( "#txt-Fe_Vencimiento_RC" ).val(dDay + '/' + dMonth + '/' + dYear);

    $( "#txt-Fe_Vencimiento_RC" ).datepicker({
    	changeMonth: true,
    	changeYear: true,
		minDate: $("#txt-Fe_Emision_Registro_Compra").val(),
		onClose: function (selectedDate) {
			$("#txt-Fe_Emision_Registro_Compra").datepicker("option", "maxDate", selectedDate);
		}
    });
}