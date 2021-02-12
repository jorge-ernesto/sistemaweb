var url;
var block_loding_modal = $('<div class="block-loading" />');

function searchSalesInvoice(iPage){
	$( '#div-sales-employee' ).prepend(block_loding_modal);

	$( '#btn-html-sales-employee' ).attr('disabled', true);
	$( '#btn-html-sales-employee' ).addClass('is-loading');

	var params = {
		action: 'search-sales-employee',
		iAlmacen: $( '#cbo-filtro-almacen' ).val(),
		dInicial: $( '#txt-fe_inicial' ).val(),
		dFinal: $( '#txt-fe_final' ).val(),
		iIdTrabajador: $.trim($( '#hidden-filtro-trabajador-id' ).val()),
		sNombreTrabajador: $( '#txt-filtro-trabajador-nombre' ).val(),
		iIdItem: $.trim($( '#hidden-filtro-item-id' ).val()),
		sNombreItem: $( '#txt-filtro-item-nombre' ).val(),
       	page : iPage
	}

	url = '/sistemaweb/ventas_clientes/ventas_x_trabajador_v2.php';

	$.ajax({
		url: url,
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			$( '.div-sales-employee' ).html(data);

			$( '#btn-html-sales-employee' ).removeClass('is-loading');
			$( '#btn-html-sales-employee' ).attr('disabled', false);
		}
	}, "JSON");
}