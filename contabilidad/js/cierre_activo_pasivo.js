$( function() {	
	/* Funciones */	
	$("#txt-periodo").on('keyup', function(){
		$("#chk-txt-periodo").text(`¿Desea cerrar el periodo (${$('#txt-periodo').val()}) indicado?`);
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
			Fe_Periodo            : $('#txt-periodo').val(),
			Generar_Asientos      : $('#chk-generar-asientos-reversion').prop("checked"), //$('input:checkbox[name=generar-asientos-reversion]:checked').val(),
			Cerrar_Periodo        : $('#chk-cerrar-periodo').prop("checked"),             //$('input:checkbox[name=cerrar-periodo]:checked').val(),
		};
		console.log('data', data);

		$.post( "reportes/c_cierre_activo_pasivo.php", {
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
			Fe_Periodo            : $('#txt-periodo').val(),
			Generar_Asientos      : $('#chk-generar-asientos-reversion').prop("checked"), //$('input:checkbox[name=generar-asientos-reversion]:checked').val(),
			Cerrar_Periodo        : $('#chk-cerrar-periodo').prop("checked"),             //$('input:checkbox[name=cerrar-periodo]:checked').val(),
		};
		console.log('data', data);

		//VALIDAMOS OPCIONES PARA GENERAR ASIENTOS
		if (data.Generar_Asientos == false || data.Cerrar_Periodo == false) {
			alert('Debe aceptar las opciones para generar asientos');
			$( '#btn-generar' ).removeClass('is-loading');
			$( '#btn-generar' ).attr('disabled', false);
			$( "#div-ProcesoContable_CRUD" ).html('');
			return;			
		}

		$.post( "reportes/c_cierre_activo_pasivo.php", {
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
			Fe_Periodo            : $('#txt-periodo').val(),
			Eliminar_Asientos     : $('#chk-eliminar-asientos-reversion').prop("checked"), //$('input:checkbox[name=eliminar-asientos-reversion]:checked').val(),
		};
		console.log('data', data);

		//VALIDAMOS OPCIONES PARA ELIMINAR ASIENTOS
		if (data.Eliminar_Asientos == false || data.Eliminar_Asientos == false) {
			alert('Debe aceptar las opciones para eliminar asientos');
			$( '#btn-eliminar' ).removeClass('is-loading');
			$( '#btn-eliminar' ).attr('disabled', false);
			$( "#div-ProcesoContable_CRUD" ).html('');
			return;			
		}

		$.post( "reportes/c_cierre_activo_pasivo.php", {
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

	$( "#btn-regenerar-balance" ).click(function(){
		$( "#div-ProcesoContable_CRUD" ).show();
		$( "#div-ProcesoContable_CRUD" ).prepend(block_loding_modal);
		
		$( '#btn-regenerar-balance' ).attr('disabled', true);
		$( '#btn-regenerar-balance' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 	          : $('#cbo-almacen option:selected').val(),
	        SubDiario 	          : $('#txt-subdiario').val(),
	        Descripcion_Subdiario : $('#txt-descripcion-subdiario').val(),
			Fe_Periodo            : $('#txt-periodo').val(),
			Regenerar_Balance     : $('#chk-regenerar-balance').prop("checked"), //$('input:checkbox[name=eliminar-asientos-reversion]:checked').val(),
		};
		console.log('data', data);

		//VALIDAMOS OPCIONES PARA REGENERAR BALANCE
		if (data.Regenerar_Balance == false || data.Regenerar_Balance == false) {
			alert('Debe aceptar las opciones para regenerar balance');
			$( '#btn-regenerar-balance' ).removeClass('is-loading');
			$( '#btn-regenerar-balance' ).attr('disabled', false);
			$( "#div-ProcesoContable_CRUD" ).html('');
			return;			
		}

		$.post( "reportes/c_cierre_activo_pasivo.php", {
			accion 	: 'regenerateBalance',
			data 	: data,
       		page 	: 1
		}, function(data){
			var message = `<div class="column is-12 text-center"><div class="notification is-info">Se regeneraron balances para el año ${$('#txt-periodo').val()}</div></div>`;
			$( "#div-ProcesoContable_CRUD" ).html(message);

			$( '#btn-regenerar-balance' ).removeClass('is-loading');
			$( '#btn-regenerar-balance' ).attr('disabled', false);
		})
	});
});
