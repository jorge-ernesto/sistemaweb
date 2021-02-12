$( function() {

	var No_Razsocial 		= $( "#txt-No_Razsocial" );
	var No_Proveedor 		= $( ".txt-No_Proveedor" );

	var No_Transportista_Proveedor = $( ".txt-No_Transportista_Proveedor" );

	var No_Placa 			= $( "#txt-No_Placa" );
	var Nu_Tarjeta 			= $( "#txt-Nu_Tarjeta" );
	var No_Producto 		= $( "#txt-No_Producto" );
	var No_Producto_Detalle = $( "#txt-No_Producto_Detalle" );
	var No_Producto_Detalle_Compra = $( "#txt-No_Producto_Detalle_Compra" );

	/* Obtener almacen interno según el tipo de Naturaleza */
	var Nu_Naturaleza_Movimiento_Inventario = $( "#txt-Nu_Naturaleza_Movimiento_Inventario" );
	var Nu_Almacen_Interno 	= "";

	var Nu_Tipo_Movimiento_Inventario_Agregar = $( "#txt-Nu_Tipo_Movimiento_Inventario_Agregar" );

	if(No_Razsocial.val() !== undefined)
		autocompleteCliente(No_Razsocial);

	if(No_Proveedor.val() !== undefined)
		autocompleteProveedor(No_Proveedor);

	if(No_Transportista_Proveedor.val() !== undefined)
		autocompleteProveedorTransportista(No_Transportista_Proveedor);

	if(No_Placa.val() !== undefined)
		autocompletegetPlacasXCliente(No_Placa);

	if(Nu_Tarjeta.val() !== undefined)
		autocompleteGetTarjetasXCliente(Nu_Tarjeta);

	/* Mostrará Codigo, Nombre */
	if(No_Producto.val() !== undefined)
		autocompleteProducto(No_Producto);

	/* Mostrará Codigo, Nombre y Precio de Venta */
	if(No_Producto_Detalle.val() !== undefined)
		autocompleteProductoDetalle2(No_Producto_Detalle);

	/* Mostrará Codigo, Nombre, Stock actual y Costo Unitario */
	if(No_Producto_Detalle_Compra.val() !== undefined)
		autocompleteProductoDetalleCompra(No_Producto_Detalle_Compra, Nu_Tipo_Movimiento_Inventario_Agregar, Nu_Naturaleza_Movimiento_Inventario, Nu_Almacen_Interno);
});

function autocompleteCliente(No_Razsocial){
	var url = "../assets/autocomplete.php";

	No_Razsocial.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 		: 'getClientes',
	                criterio 	: request.term.toUpperCase()
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 		: item.cli_codigo,
	                        ruc		: item.cli_ruc,
	                        value 	: item.cli_razsocial,
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-Nu_Documento_Identidad").val(ui.item.id);
	    	$("#txt-No_Razsocial").val(ui.item.value);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a>' + item.ruc + ' - ' + item.value + '</a>')
		.appendTo( ul );
	};
}

function autocompleteProveedor(No_Proveedor){
	var url = "../assets/autocomplete.php";
	var iNumDiasVencimiento = 0;

	No_Proveedor.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {	    	
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 		: 'getProveedores',
	                criterio 	: request.term.toUpperCase()
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 						: item.pro_codigo,
	                        ruc						: item.pro_ruc,
	                        value 					: item.pro_razsocial,
	                        Nu_Dias_Vencimiento 	: $.trim(item.nu_dias_vencimiento),
	                        Nu_Codigo_Rubro 		: $.trim(item.nu_codigo_rubro),
	                        No_Descripcion_Rubro 	: item.no_descripcion_rubro,
	                        Nu_Tipo_Moneda 			: item.nu_tipo_moneda
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-Nu_Documento_Identidad").val(ui.item.id);
	    	$(".txt-No_Proveedor").val(ui.item.value);
	    	iNumDiasVencimiento = (ui.item.Nu_Dias_Vencimiento != '' ? ui.item.Nu_Dias_Vencimiento : 0);
	    	iNumDiasVencimiento = parseInt(iNumDiasVencimiento);
	    	$("#txt-Nu_Dias_Vencimiento_RC").val(iNumDiasVencimiento);
	    	if(iNumDiasVencimiento > 0){
	    		var Fe_Emision_Compra = $(' #txt-Fe_Emision_Compra' ).val();

			    Fe_Emision_Compra = Fe_Emision_Compra.split('/');
			    Fe_Emision_Compra = Fe_Emision_Compra[2] + '/' + Fe_Emision_Compra[1] + '/' + Fe_Emision_Compra[0];

				var Fe_Emision_Compra_New = Fe_Emision_Compra;

				var Fe_Actual = new Date(Fe_Emision_Compra_New);

				day 			= Fe_Actual.getDate();
				month 		= Fe_Actual.getMonth()+1;
				year 			= Fe_Actual.getFullYear();

				/* Dia Actual */
				tiempo 			= Fe_Actual.getTime();
				milisegundos 	= parseInt(iNumDiasVencimiento * 24 * 60 * 60 * 1000);
				total 			= Fe_Actual.setTime(tiempo+milisegundos);
				day 				= Fe_Actual.getDate();
				month 			= Fe_Actual.getMonth()+1;
				year 				= Fe_Actual.getFullYear();

				if(month.toString().length < 2)
					month = "0".concat(month);

				if(day.toString().length<2)
					day = "0".concat(day);

				/* Fecha de Vencimiento Registro Compras */
				var Fe_Vencimiento_RC = day + '/' + month + '/' + year;	
				$(' #txt-Fe_Vencimiento_RC' ).val(Fe_Vencimiento_RC);
	    	} else {
				$(' #txt-Fe_Vencimiento_RC' ).val($(' #txt-Fe_Emision_Compra' ).val());
	    	}

	    	$( '#txt-Nu_Memoria_Rubro' ).val(ui.item.Nu_Codigo_Rubro);
	    	$( "#txt-Nu_Memoria_Moneda" ).val(ui.item.Nu_Tipo_Moneda);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a>' + item.ruc + ' - ' + item.value + ' / Dias Venc.: ' + item.Nu_Dias_Vencimiento + ' / Rubro: ' + item.No_Descripcion_Rubro + '</a>')
		.appendTo( ul );
	}
}

function autocompleteProveedorTransportista(No_Transportista_Proveedor){
	var url = "../assets/autocomplete.php";
	
	No_Transportista_Proveedor.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {	    	
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 		: 'getProveedores',
	                criterio 	: request.term.toUpperCase()
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 		: item.pro_codigo,
	                        ruc		: item.pro_ruc,
	                        value 	: item.pro_razsocial,
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-ID_Transportista_Proveedor").val(ui.item.id);
	    	$(".txt-No_Transportista_Proveedor").val(ui.item.value);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a>' + item.ruc + ' - ' + item.value + ' </a>')
		.appendTo( ul );
	}
}

function autocompletegetPlacasXCliente(Nu_Placa){
	var url = "../assets/autocomplete.php";

	Nu_Placa.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 					: 'getTarjetasYPlacasXCliente',
	            	Nu_Documento_Identidad 	: $( "#txt-Nu_Documento_Identidad" ).val(),
	                criterio 				: request.term.toUpperCase()
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 		: item.numpla,
	                        value 	: item.numtar,
	                        No_Chofer : item.nomusu,
	                        Nu_Documento_Identidad_Chofer : item.nu_documento_chofer,
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-No_Placa").val(ui.item.id);
	    	$("#txt-Nu_Tarjeta").val(ui.item.value);
	    	$("#txt-Nu_Documento_Identidad_Chofer").val(ui.item.Nu_Documento_Identidad_Chofer);
	    	$("#txt-No_Chofer").val(ui.item.No_Chofer);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a><span> ' + item.id + '</span></a>')
		.appendTo( ul );
	};
}

function autocompleteGetTarjetasXCliente(Nu_Tarjeta){
	var url = "../assets/autocomplete.php";

	Nu_Tarjeta.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 					: 'getTarjetasYPlacasXCliente',
	            	Nu_Documento_Identidad 	: $( "#txt-Nu_Documento_Identidad" ).val(),
	                criterio 				: request.term.toUpperCase()
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 		: item.numpla,
	                        value 	: item.numtar,
	                        No_Chofer : item.nomusu,
	                        Nu_Documento_Identidad_Chofer : item.nu_documento_chofer,
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-No_Placa").val(ui.item.id);
	    	$("#txt-Nu_Tarjeta").val(ui.item.value);
	    	$("#txt-Nu_Documento_Identidad_Chofer").val(ui.item.Nu_Documento_Identidad_Chofer);
	    	$("#txt-No_Chofer").val(ui.item.No_Chofer);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a><span> ' + item.value + '</span></a>')
		.appendTo( ul );
	};
}

function autocompleteProducto(No_Producto){
	var url = "../assets/autocomplete.php";

	No_Producto.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 		: 'getProductos',
	                criterio 	: request.term.toUpperCase()
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 				: item.art_codigo,
	                        value 			: item.art_descripcion,
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-Nu_Id_Producto").val(ui.item.id);
	    	$("#txt-No_Producto").val(ui.item.value);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a>' + item.id + ' - ' + item.value + ' </span></a>')
		.appendTo( ul );
	};
}

function autocompleteProductoDetalle(No_Producto_Detalle){
	console.log('autocompleteProductoDetalle');
	var url = "../assets/autocomplete.php";

	No_Producto_Detalle.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 		: 'getProductos',
	                criterio 	: request.term.toUpperCase()
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 				: item.art_codigo,
	                        value 			: item.art_descripcion,
	                        nu_precio_venta : item.nu_precio_venta,
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-Nu_Id_Producto").val(ui.item.id);
	    	$("#txt-No_Producto_Detalle").val(ui.item.value);
	    	$("#txt-Nu_Precio").val(ui.item.nu_precio_venta);

	    	$("#txt-Nu_Cantidad").val('');
	    	$("#txt-Nu_Total").val('');
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a>' + item.id + ' - ' + item.value + ' / Precio: ' + item.nu_precio_venta + '</a>')
		.appendTo( ul );
	};
}

function autocompleteProductoDetalle2(No_Producto_Detalle){
	console.log('autocompleteProductoDetalle2');
	var url = "../assets/autocomplete.php";

	No_Producto_Detalle.autocomplete({
		dataType: 'JSON',
		source: function (request, response) {
			jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 		: 'obtenerProductos2',
	                criterio 	: request.term.toUpperCase()
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 				: item.art_codigo,
	                        value 			: item.art_descripcion,
	                        nu_precio_venta : item.nu_precio_venta,
	                    }
	                }))
	            }
	        })
		},
		search  : function(){$(this).addClass('ui-autocomplete-loading');},
		open    : function(){$(this).removeClass('ui-autocomplete-loading');},
		select 	: function (e, ui) {
			$("#txt-Nu_Id_Producto").val(ui.item.id);
			$("#txt-No_Producto_Detalle").val(ui.item.value);
			$("#txt-Nu_Precio").val(ui.item.nu_precio_venta);

			$("#txt-Nu_Cantidad").val('');
			$("#txt-Nu_Total").val('');
			return false;
		}
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a>' + item.id + ' - ' + item.value + ' / Precio: ' + item.nu_precio_venta + '</a>')
		.appendTo( ul );
	};
}

function autocompleteProductoDetalleCompra(No_Producto_Detalle_Compra, Nu_Tipo_Movimiento_Inventario_Agregar, Nu_Naturaleza_Movimiento_Inventario, Nu_Almacen_Interno){
	var url = "../assets/autocomplete.php";
	No_Producto_Detalle_Compra.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
			/* Obtener almacen interno según el tipo de Naturaleza */
			if(Nu_Naturaleza_Movimiento_Inventario.val() !== undefined){
				var Nu_Almacen_Origen 	= $( ".Nu_Almacen_Origen" );
			   	var Nu_Almacen_Destino 	= $( ".Nu_Almacen_Destino" );

				if(Nu_Naturaleza_Movimiento_Inventario.val() == 1 || Nu_Naturaleza_Movimiento_Inventario.val() == 2)
			   		Nu_Almacen_Interno = Nu_Almacen_Destino.val();
			   	else
			   		Nu_Almacen_Interno = Nu_Almacen_Origen.val();
		   	}

	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	Accion 							: 'getProductos',
	                criterio 						: request.term.toUpperCase(),
	                Nu_Tipo_Movimiento_Inventario 	: Nu_Tipo_Movimiento_Inventario_Agregar.val(),
	                Nu_Almacen_Interno 				: Nu_Almacen_Interno
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 					: item.art_codigo,
	                        value 				: item.art_descripcion,
	                        Nu_Cantidad_Actual	: item.nu_cantidad_actual,
	                        Nu_Costo_Unitario 	: item.nu_costo_unitario,
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-Nu_Id_Producto").val(ui.item.id);
	    	$("#txt-No_Producto_Detalle_Compra").val(ui.item.value);
	    	$("#txt-Nu_Cantidad_Actual").val(ui.item.Nu_Cantidad_Actual);
	    	$("#txt-Nu_Costo_Unitario").val(ui.item.Nu_Costo_Unitario);

	    	$("#txt-Nu_Cantidad_Compra").val('');
			$("#txt-Nu_Cantidad_Compra").prop( "disabled", false );

	    	$("#txt-Nu_Total_SIGV").val('');
	    	$("#txt-Nu_Total_CIGV").val('');
	    	$("#label-Nu_Total_CIGV").val('');

			if($.trim($("#txt-Nu_Id_Producto").val()) == '11620307'){
				$('.checkbox-conversionGLP').show();
				$("#chk-conversionGLP").prop('checked', false);
			}else{
				$("#chk-conversionGLP").prop('checked', false);
				$("#txt-Nu_Cantidad_Compra").show();
				$(".span-Nu_Cantidad_Compra").show();
				$('.checkbox-conversionGLP').hide();
		        $(".div-conversionGLP").hide();
			}

	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a>' + item.id + ' - ' + item.value + ' / Stock: ' + item.Nu_Cantidad_Actual + ' / C.U.: ' + item.Nu_Costo_Unitario + '</a>')
		.appendTo( ul );
		//.append( '<a><span style="float:center;font-size:12px;"> <b>Codigo: </b>' + item.id + ' / <b>Nombre: </b>' + item.value + ' / <b>Stock actual: </b>' + item.Nu_Cantidad_Actual + ' / <b>Costo Unitario: </b>' + item.Nu_Costo_Unitario + '</span></a>')
	};
}