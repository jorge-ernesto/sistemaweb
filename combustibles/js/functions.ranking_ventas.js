var url;
var block_loding_modal = $('<div class="block-loading" />');

function getRankingVentas(iPage){
	$( '#div-ranking-ventas' ).prepend(block_loding_modal);

	$( '#btn-html-ranking-ventas' ).attr('disabled', true);
	$( '#btn-html-ranking-ventas' ).addClass('is-loading');

	var params = {
		action: 'search-ranking-ventas',
		sAlmacen: $( '#cbo-filtro-almacen' ).val(),
		sGeneradoPor: $( '#cbo-filtro-generado' ).val(),
		sYear: $( '#cbo-filtro-year' ).val(),
		sMonth: $( '#cbo-filtro-month' ).val(),
	}

	url = '/sistemaweb/combustibles/rep_ranking_ventas.php';

	$.ajax({
		url: url,
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			$( '.div-ranking-ventas' ).html(data);

			$( '#btn-html-ranking-ventas' ).removeClass('is-loading');
			$( '#btn-html-ranking-ventas' ).attr('disabled', false);
		}
	}, "JSON");
}

function obtenerDetalle(sAlmacen, sGeneradoPor, sYear, sMonth, sIdCliente, sNombreCliente, iIdTr){
	$( '#div-ranking-ventas-detalle' ).prepend(block_loding_modal);

	$( '#btn-html-ranking-ventas' ).attr('disabled', true);
	$( '#btn-html-ranking-ventas' ).addClass('is-loading');

	var params = {
		action: 'search-ranking-ventas-detalle',
		sAlmacen: sAlmacen,
		sGeneradoPor: sGeneradoPor,
		sYear: sYear,
		sMonth: sMonth,
		sIdCliente: sIdCliente,
		sNombreCliente: sNombreCliente,
		iIdTr: iIdTr,
	}

	url = '/sistemaweb/combustibles/rep_ranking_ventas.php';

	$.ajax({
		url: url,
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			$( '.div-ranking-ventas-detalle' ).html(data);

			scrollToError( $( '#div-ranking-ventas-detalle' ) );

			$( '#btn-html-ranking-ventas' ).removeClass('is-loading');
			$( '#btn-html-ranking-ventas' ).attr('disabled', false);
		}
	}, "JSON");
}

function regresarCliente(iIdTr){scrollToError( $( '#tr-' + iIdTr ) );}