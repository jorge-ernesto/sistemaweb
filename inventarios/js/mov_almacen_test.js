$( function() {

    //Buscar antes de cargar la pagina
	var data = {
        Nu_Almacen 		: '',
        Fe_Inicial 		: $('#txt-fe_inicial').val(),
        Fe_Final 		: $('#txt-fe_final').val(),
        Nu_Documento 	: '',
        No_Producto 	: '',
        No_Proveedor 	: '',
        Nu_Tipo_Movimiento_Inventario : $('#txt-Nu_Tipo_Movimiento_Inventario').val()
	};

	BuscarCompras('listAll', data, 1);

	/* Registro de Compras */
});

function BuscarCompras(accion, data, page){
	/* Cargador */
	var block_loding_modal = $('<div class="block-loading" />');
	$( "#div-Movimiento_Inventario_Table" ).show();
	$( "#div-Movimiento_Inventario_Table" ).prepend(block_loding_modal);

	$.post( "reportes/c_mov_almacen_crud.php", {
		accion 	: accion,
   		data 	: data,
   		page 	: page
	}, function(data){
		$( "#div-Movimiento_Inventario_Table" ).html(data);
	})
}