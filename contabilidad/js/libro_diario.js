$( function() {
	/* Cargador */
	var block_loding_modal = $('<div class="block-loading" />');

	$( "#btn-buscar" ).click(function(){
		$( "#div-LibroDiario_CRUD" ).show();
		$( "#div-LibroDiario_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-buscar' ).attr('disabled', true);
		$( '#btn-buscar' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
	        Fe_Periodo 	: $('#txt-periodo').val(),
	        Fe_Mes  	: $('#txt-mes').val(),
			Nu_Cantreg  : $('#txt-cantidadregistros').val(),
		};
		console.log('data', data);

		$.post( "reportes/c_libro_diario.php", {
			accion 	: 'listAll',
			data 	: data,
       		page 	: 1
		}, function(data){
			$( "#div-LibroDiario_CRUD" ).html(data);

			$( '#btn-buscar' ).removeClass('is-loading');
			$( '#btn-buscar' ).attr('disabled', false);
		})
	});

	$( "#btn-pdf" ).click(function(){	
		$( "#div-LibroDiario_CRUD" ).show();
		$( "#div-LibroDiario_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-pdf' ).attr('disabled', true);
		$( '#btn-pdf' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
	        Fe_Periodo 	: $('#txt-periodo').val(),
	        Fe_Mes  	: $('#txt-mes').val(),
			Nu_Cantreg  : $('#txt-cantidadregistros').val(),
		};
		console.log('data', data);

		$.post( "reportes/c_libro_diario.php", {
			accion 	: 'listPDF',
			data 	: data
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-info">Exportado</div></div>`;
			$( "#div-LibroDiario_CRUD" ).html(message);

			window.open('/sistemaweb/contabilidad/reportes/pdf/reporte_libro_diario.pdf');
			// window.open("/sistemaweb/contabilidad/reportes/pdf/reporte_libro_diario.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");

			$( '#btn-pdf' ).removeClass('is-loading');
			$( '#btn-pdf' ).attr('disabled', false);
		})
	});

	$( "#btn-excel" ).click(function(e) {
		$( "#div-LibroDiario_CRUD" ).show();
		$( "#div-LibroDiario_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-excel' ).attr('disabled', true);
		$( '#btn-excel' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
	        Fe_Periodo 	: $('#txt-periodo').val(),
	        Fe_Mes  	: $('#txt-mes').val(),
			Nu_Cantreg  : $('#txt-cantidadregistros').val(),
		};
		console.log('data', data);

		$.post( "reportes/c_libro_diario.php", {
			accion 	: 'listExcel',
			data 	: data
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-info">Exportado</div></div>`;
			$( "#div-LibroDiario_CRUD" ).html(message);

			data = JSON.parse(data);
			console.log(data);

			var link = document.createElement('a');
			link.href = `${data.ruta}${data.nombre_archivo}`;
			link.download = `${data.nombre_archivo}`;
			link.dispatchEvent(new MouseEvent('click'));

			$( '#btn-excel' ).removeClass('is-loading');
			$( '#btn-excel' ).attr('disabled', false);
		})
	});

	$( "#btn-ple" ).click(function(e) {
		$( "#div-LibroDiario_CRUD" ).show();
		$( "#div-LibroDiario_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-ple' ).attr('disabled', true);
		$( '#btn-ple' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	: $('#cbo-almacen option:selected').val(),
	        Fe_Periodo 	: $('#txt-periodo').val(),
	        Fe_Mes  	: $('#txt-mes').val(),
			Nu_Cantreg  : $('#txt-cantidadregistros').val(),
		};
		console.log('data', data);

		$.post( "reportes/c_libro_diario.php", {
			accion 	: 'listPLE',
			data 	: data
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-info">Exportado</div></div>`;
			$( "#div-LibroDiario_CRUD" ).html(message);

			data = JSON.parse(data);
			console.log(data);

			var link = document.createElement('a');
			link.href = `${data.ruta}${data.nombre_archivo}`;
			link.download = `${data.nombre_archivo}`;
			link.dispatchEvent(new MouseEvent('click'));		

			$( '#btn-ple' ).removeClass('is-loading');
			$( '#btn-ple' ).attr('disabled', false);
		})
	});
});
