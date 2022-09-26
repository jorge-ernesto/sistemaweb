$( function() {	
	/* Funciones */	
	$("#txt-periodo").on('keyup', function(){
		$("#chk-txt-periodo").text(`¿Desea aperturar el periodo (${$('#txt-periodo').val()}) indicado?`);
	}).keyup();

	/* Cargador */
	var block_loding_modal = $('<div class="block-loading" />');

	$( "#btn-previsualizar" ).click(function(){
		$( "#div-ProcesoContable_CRUD" ).show();
		$( "#div-ProcesoContable_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-previsualizar' ).attr('disabled', true);
		$( '#btn-previsualizar' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	          : $('#cbo-almacen option:selected').val(),
	        SubDiario 	          : $('#txt-subdiario').val(),
	        Descripcion_Subdiario : $('#txt-descripcion-subdiario').val(),
			Fe_Anio_Pasado        : $('#txt-periodo').val() - 1, //Año pasado
			Fe_Anio_Apertura      : $('#txt-periodo').val(),     //Año apertura
			Generar_Asientos      : $('#chk-generar-asientos-apertura').prop("checked"),
			Aperturar_Periodo     : $('#chk-aperturar-periodo').prop("checked"),
		};
		console.log('data', data);

		$.post( "reportes/c_asiento_inicial.php", {
			accion 	: 'previewEntry',
			data 	: data,
       		page 	: 1
		}, function(data){
			$( "#div-ProcesoContable_CRUD" ).html(data);

			$( '#btn-previsualizar' ).removeClass('is-loading');
			$( '#btn-previsualizar' ).attr('disabled', false);
		})
	});

	$( "#btn-generar" ).click(function(){
		$( "#div-ProcesoContable_CRUD" ).show();
		$( "#div-ProcesoContable_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-generar' ).attr('disabled', true);
		$( '#btn-generar' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	          : $('#cbo-almacen option:selected').val(),
	        SubDiario 	          : $('#txt-subdiario').val(),
	        Descripcion_Subdiario : $('#txt-descripcion-subdiario').val(),
			Fe_Anio_Pasado        : $('#txt-periodo').val() - 1, //Año pasado
			Fe_Anio_Apertura      : $('#txt-periodo').val(),     //Año apertura
			Generar_Asientos      : $('#chk-generar-asientos-apertura').prop("checked"),
			Aperturar_Periodo     : $('#chk-aperturar-periodo').prop("checked"),
		};
		console.log('data', data);

		//VALIDAMOS OPCIONES PARA GENERAR ASIENTOS
		if (data.Generar_Asientos == false || data.Aperturar_Periodo == false) {
			alert('Debe aceptar las opciones para generar asientos');
			$( '#btn-generar' ).removeClass('is-loading');
			$( '#btn-generar' ).attr('disabled', false);
			$( "#div-ProcesoContable_CRUD" ).html('');
			return;			
		}

		$.post( "reportes/c_asiento_inicial.php", {
			accion 	: 'generateEntry',
			data 	: data,
       		page 	: 1
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-info">Se generaron Asientos de Reversión para el año ${$('#txt-periodo').val()}</div></div>`;
			$( "#div-ProcesoContable_CRUD" ).html(message);

			$( '#btn-generar' ).removeClass('is-loading');
			$( '#btn-generar' ).attr('disabled', false);
		})
	});

	$( "#btn-eliminar" ).click(function(){
		$( "#div-ProcesoContable_CRUD" ).show();
		$( "#div-ProcesoContable_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-eliminar' ).attr('disabled', true);
		$( '#btn-eliminar' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	          : $('#cbo-almacen option:selected').val(),
	        SubDiario 	          : $('#txt-subdiario').val(),
	        Descripcion_Subdiario : $('#txt-descripcion-subdiario').val(),
			Fe_Anio_Pasado        : $('#txt-periodo').val() - 1, //Año pasado
			Fe_Anio_Apertura      : $('#txt-periodo').val(),     //Año apertura
			Eliminar_Asientos     : $('#chk-eliminar-asientos-apertura').prop("checked"),
		};
		console.log('data', data);

		//VALIDAMOS OPCIONES PARA ELIMINAR ASIENTOS
		if (data.Eliminar_Asientos == false) {
			alert('Debe aceptar las opciones para eliminar asientos');
			$( '#btn-eliminar' ).removeClass('is-loading');
			$( '#btn-eliminar' ).attr('disabled', false);
			$( "#div-ProcesoContable_CRUD" ).html('');
			return;			
		}

		$.post( "reportes/c_asiento_inicial.php", {
			accion 	: 'deleteEntry',
			data 	: data,
       		page 	: 1
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-danger">Se eliminaron Asientos de Reversion para el año ${$('#txt-periodo').val()}</div></div>`;
			$( "#div-ProcesoContable_CRUD" ).html(message);

			$( '#btn-eliminar' ).removeClass('is-loading');
			$( '#btn-eliminar' ).attr('disabled', false);
		})
	});
});
