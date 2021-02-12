function getDatos(){
	var No_Razsocial 		= $( "#txt-No_Razsocial" );
	var No_Producto 		= $( "#txt-No_Producto" );

	if(No_Razsocial.val() !== undefined)
		autocompleteCliente(No_Razsocial);

	if(No_Producto.val() !== undefined)
		autocompleteProducto(No_Producto);
};

function autocompleteCliente(No_Razsocial){
	var url = "../assets/autocomplete2.php";

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

function autocompleteProducto(No_Producto){
	var url = "../assets/autocomplete2.php";

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
