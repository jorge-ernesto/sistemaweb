$(document).ready(function(){
	var url;

	var No_Producto = $("#txt-No_Producto");
	if(No_Producto.val() !== undefined){
		autocompleteProducto(No_Producto);
	}

	var No_Linea = $("#txt-No_Linea");
	if(No_Linea.val() !== undefined){
		autocompleteLinea(No_Linea);
	}

	var desc_art = $("#desc_art");
	if(desc_art.val() !== undefined){
		autocompleteProducto(desc_art);
	}

	var No_Proveedor = $("#txt-No_Proveedor");
	if(No_Proveedor.val() !== undefined){
		autocompleteProveedor(No_Proveedor);
	}
});

function autocompleteProducto(No_Producto){
	//var No_Producto;

	url = "../helper/autocomplete.php";

	//No_Producto = $("#txt-No_Producto");
	No_Producto.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            criterio 	: request.term.toUpperCase(),
	            criterio2 	: "1"
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 		: item.art_codigo,
	                        value 	: item.art_descripcion,
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
		.append( '<a><span style="float:center;font-size:12px;"> ' + item.id + ' / ' + item.value + '</span></a>')
		.appendTo( ul );
	};

	var No_Producto_Saliente = $("#txt-No_Producto_Saliente");
	if(No_Producto_Saliente.val() !== undefined){
		No_Producto_Saliente.autocomplete({
		    dataType: 'JSON',
		    source: function (request, response) {
		    	jQuery.ajax({
		            url			: url,
		            type		: "POST",
		            dataType	: "JSON",
		            data		: {
		            criterio 	: request.term.toUpperCase(),
		            criterio2 	: "2"
		            },
		            success: function (data) {
		                response($.map(data, function (item) {
		                    return {
		                        id 		: item.art_codigo,
		                        value 	: item.art_descripcion,
		                    }
		                }))
		            }
		        })
		    },
		    search  : function(){$(this).addClass('ui-autocomplete-loading');},
		    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
		    select 	: function (e, ui) {
		    	$("#txt-Nu_Id_Producto_Saliente").val(ui.item.id);
		    	$("#txt-No_Producto_Saliente").val(ui.item.value);
		    	return false;
		    }
		}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			return $( "<li>" )
			.append( '<a><span style="float:center;font-size:12px;"> ' + item.id + ' / ' + item.value + '</span></a>')
			.appendTo( ul );
		};
	}


	var desc_art = $("#desc_art");
	if(desc_art.val() !== undefined){
		desc_art.autocomplete({
		    dataType: 'JSON',
		    source: function (request, response) {
		    	jQuery.ajax({
		            url			: url,
		            type		: "POST",
		            dataType	: "JSON",
		            data		: {
		            criterio 	: request.term.toUpperCase(),
		            criterio2 	: "1"
		            },
		            success: function (data) {
		                response($.map(data, function (item) {
		                    return {
		                        id 		: item.art_codigo,
		                        value 	: item.art_descripcion,
		                    }
		                }))
		            }
		        })
		    },
		    search  : function(){$(this).addClass('ui-autocomplete-loading');},
		    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
		    select 	: function (e, ui) {
		    	$("#cod_art").val(ui.item.id);
		    	$("#desc_art").val(ui.item.value);
		    	return false;
		    }
		}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			return $( "<li>" )
			.append( '<a><span style="float:center;font-size:12px;"> ' + item.id + ' / ' + item.value + '</span></a>')
			.appendTo( ul );
		};
	}
}


function autocompleteLinea(No_Linea){

	url = "../helper/autocomplete.php";

	No_Linea.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	accion 		: 'getLineas',
		            criterio 	: request.term.toUpperCase(),
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 		: item.tab_elemento,
	                        value 	: $.trim(item.tab_descripcion),
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-Nu_Id_Linea").val(ui.item.id);
	    	$("#txt-No_Linea").val(ui.item.value);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a><span style="float:center;font-size:12px;"> ' + item.id + ' / ' + item.value + '</span></a>')
		.appendTo( ul );
	};
}


function autocompleteLinea(No_Linea){

	url = "../helper/autocomplete.php";

	No_Linea.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	accion 		: 'getLineas',
		            criterio 	: request.term.toUpperCase(),
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                        id 		: item.tab_elemento,
	                        value 	: $.trim(item.tab_descripcion),
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-Nu_Id_Linea").val(ui.item.id);
	    	$("#txt-No_Linea").val(ui.item.value);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a><span style="float:center;font-size:12px;"> ' + item.id + ' / ' + item.value + '</span></a>')
		.appendTo( ul );
	};
}

/**
 * Autocompletador general
 * @param String elementReq: elemento que hace la solicitud, este es el campo en el que se escribe para ejecutar el evento de autocompletado, muestra las sugerencias de la concidencia encontrada
 * @param String elementRes: elemento que recibe la respuesta, por lo general es un input hidden y almacena el id
 * @param String action: backend que buscará las coincidencias
 * @param Array eventsAfter: eventos que se ejecutan despues de selecionar la sugerencia del autocompletado
 * 
 * By kwn
 */
function generalAutocomplete(elementReq, elementRes, action, eventsAfter) {
	var objElementReq = $(elementReq);
	url = "../helper/autocomplete.php";

	objElementReq.autocomplete({
		dataType: 'JSON',
		source: function (request, response) {
			jQuery.ajax({
				url			: url,
				type		: "POST",
				dataType	: "JSON",
				data		: {
					accion 		: action,
					criterio 	: request.term.toUpperCase(),
				},
				success: function (data) {
					response($.map(data, function (item) {
						return {
							id 		: item._id,
							value 	: $.trim(item._value),
						}
					}))
				}
			})
		},
		search: function(){$(this).addClass('ui-autocomplete-loading');},
		open: function(){$(this).removeClass('ui-autocomplete-loading');},
		select: function (e, ui) {
			$(elementRes).val(ui.item.id);
			$(elementReq).val(ui.item.value);
			if (eventsAfter.length > 0) {
				for (var i = 0; i < eventsAfter.length; i++) {
					eval(eventsAfter[i]);
				};
			}
			return false;
		}
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a><span style="float:center;font-size:12px;"> ' + item.id + ' / ' + item.value + '</span></a>')
		.appendTo( ul );
	};
}


function autocompleteProveedor(No_Proveedor){

	url = "../helper/autocomplete.php";

	No_Proveedor.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	accion 		: 'getProveedores',
		            criterio 	: request.term.toUpperCase(),
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                       id 		: item.pro_codigo,
	                       value 	: item.pro_razsocial,
	                    }
	                }))
	            }
	        })
	    },
	    search  : function(){$(this).addClass('ui-autocomplete-loading');},
	    open    : function(){$(this).removeClass('ui-autocomplete-loading');},
	    select 	: function (e, ui) {
	    	$("#txt-No_ProveedorRUC").val(ui.item.id);
	    	$("#txt-No_Proveedor").val(ui.item.value);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a><span style="float:center;font-size:12px;"> ' + item.id + ' / ' + item.value + '</span></a>')
		.appendTo( ul );
	};
}

function autocompleteTransportistaProveedor(No_Proveedor){

	url = "../helper/autocomplete.php";

	No_Proveedor.autocomplete({
	    dataType: 'JSON',
	    source: function (request, response) {
	    	jQuery.ajax({
	            url			: url,
	            type		: "POST",
	            dataType	: "JSON",
	            data		: {
	            	accion 		: 'getProveedores',
		            criterio 	: request.term.toUpperCase(),
	            },
	            success: function (data) {
	                response($.map(data, function (item) {
	                    return {
	                       id 		: item.pro_codigo,
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
	    	$("#txt-No_Transportista_Proveedor").val(ui.item.value);
	    	return false;
	    }
	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( '<a><span style="float:center;font-size:12px;"> ' + item.id + ' / ' + item.value + '</span></a>')
		.appendTo( ul );
	};
}