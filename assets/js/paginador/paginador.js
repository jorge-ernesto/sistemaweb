var page_atras;
var page;
var page_siguiente;
var block_loding_modal = $('<div class="block-loading" />');

$( function() {
	//Menu 1 : Combustibles -> Compras de Combustible
	$( '.pagination-previous' ).click(function() {
		var page_atras	= $('#pageActual').val();

		if(parseInt(page_atras) > 1) {
			page_atras--;
			PaginadorMovimientosInventarios(page_atras);
		}
	})
	
	$( '.pagination-link' ).click(function() {
		var page		= $(this).attr("data-page");
		PaginadorMovimientosInventarios(page);
	})
	
	$( '.pagination-next' ).click(function() {
		var page_siguiente	= $('#pageActual').val();
		var page_cantidad	= $('#cantidadPage').val();

		if(parseInt(page_siguiente) < parseInt(page_cantidad)) {
			page_siguiente++;
			PaginadorMovimientosInventarios(page_siguiente);
		}
	})

	//Menu 2: Ventas -> Vales de Creditos -> Registro de Vales de credito
	//RVC = Registro de Vales de credito
	$( '.pagination-previousRVC' ).click(function() {
		var page_atras	= $('#pageActual').val();

		if(parseInt(page_atras) > 1) {
			page_atras--;
			PaginadorValeCreditos(page_atras);
		}
	})
	
	$( '.pagination-linkRVC' ).click(function() {
		var page		= $(this).attr("data-page");
		PaginadorValeCreditos(page);
	})
	
	$( '.pagination-nextRVC' ).click(function() {
		var page_siguiente	= $('#pageActual').val();
		var page_cantidad	= $('#cantidadPage').val();

		if(parseInt(page_siguiente) < parseInt(page_cantidad)) {
			page_siguiente++;
			PaginadorValeCreditos(page_siguiente);
		}
	})
});

function PaginadorMovimientosInventarios(page){
	$("#div-Movimiento_Inventario_Table").prepend(block_loding_modal);
	var data = {
        almacen 		: $('#cbo-almacen option:selected').val(),
        Fe_Inicial 		: $('#txt-fe_inicial').val(),
        Fe_Final 		: $('#txt-fe_final').val(),
        Nu_Documento 	: $('#txt-Nu_Documento').val(),
        No_Producto 	: $('#txt-No_Producto').val(),
        No_Proveedor 	: $('#txt-No_Proveedor').val(),
        Nu_Tipo_Movimiento_Inventario : $('#txt-Nu_Tipo_Movimiento_Inventario').val()
	};
	$.post('reportes/c_mov_almacen_crud.php', {
		accion 	: 'listAll',
   		data 	: data,
		page 	: page,
	}, function(data){
		$( "#div-Movimiento_Inventario_Table" ).html(data);
	})
}

function PaginadorValeCreditos(page){
	$("#div-Vale_CRUD").prepend(block_loding_modal);
	var data = {
        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
        Fe_Inicial 	: $('#txt-fe_inicial').val(),
        Fe_Final	: $('#txt-fe_final').val(),
	    Nu_Estado 	: $('#cbo-Nu_Estado option:selected').val(),
        Nu_Ticket 	: $('#txt-nu_ticket').val(),
		sIdCliente : $('#hidden-filtro-cliente-id').val(),
		sNombreCliente : $('#txt-filtro-cliente-nombre').val(),
	};
	$.post('reportes/c_vale_crud.php', {
		accion 	: 'listAll',
   		data 	: data,
		page 	: page,
	}, function(data){
		$( "#div-Vale_CRUD" ).html(data);
	})
}