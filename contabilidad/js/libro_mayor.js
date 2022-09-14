$( function() {
	/* Cargador */
	var block_loding_modal = $('<div class="block-loading" />');

	$( "#btn-pdf" ).click(function(){	
		$( "#div-LibroMayor_CRUD" ).show();
		$( "#div-LibroMayor_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-pdf' ).attr('disabled', true);
		$( '#btn-pdf' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	   : $('#cbo-almacen option:selected').val(),
	        Fe_Periodo 	   : $('#txt-periodo').val(),
	        Fe_Mes  	   : $('#txt-mes').val(),
			Since_Account  : $('#txt-sinceaccount').val(),
			To_Account     : $('#txt-toaccount').val(),
		};
		console.log('data', data);

		$.post( "reportes/c_libro_mayor.php", {
			accion 	: 'listPDF',
			data 	: data
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-info">Exportado</div></div>`;
			$( "#div-LibroMayor_CRUD" ).html(message+data);

			window.open('/sistemaweb/contabilidad/reportes/pdf/reporte_libro_mayor.pdf');
			// window.open("/sistemaweb/contabilidad/reportes/pdf/reporte_libro_mayor.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");

			$( '#btn-pdf' ).removeClass('is-loading');
			$( '#btn-pdf' ).attr('disabled', false);
		})
	});

	$( "#btn-excel" ).click(function(e) {
		$( "#div-LibroMayor_CRUD" ).show();
		$( "#div-LibroMayor_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-excel' ).attr('disabled', true);
		$( '#btn-excel' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	   : $('#cbo-almacen option:selected').val(),
	        Fe_Periodo 	   : $('#txt-periodo').val(),
	        Fe_Mes  	   : $('#txt-mes').val(),
			Since_Account  : $('#txt-sinceaccount').val(),
			To_Account     : $('#txt-toaccount').val(),
		};
		console.log('data', data);

		$.post( "reportes/c_libro_mayor.php", {
			accion 	: 'listExcel',
			data 	: data
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-info">Exportado</div></div>`;
			$( "#div-LibroMayor_CRUD" ).html(message+data);

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
		$( "#div-LibroMayor_CRUD" ).show();
		$( "#div-LibroMayor_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-ple' ).attr('disabled', true);
		$( '#btn-ple' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	   : $('#cbo-almacen option:selected').val(),
	        Fe_Periodo 	   : $('#txt-periodo').val(),
	        Fe_Mes  	   : $('#txt-mes').val(),
			Since_Account  : $('#txt-sinceaccount').val(),
			To_Account     : $('#txt-toaccount').val(),
		};
		console.log('data', data);

		$.post( "reportes/c_libro_mayor.php", {
			accion 	: 'listPLE',
			data 	: data
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-info">Exportado</div></div>`;
			$( "#div-LibroMayor_CRUD" ).html(message+data);

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
