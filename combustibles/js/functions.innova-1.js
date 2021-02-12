var $url;

function openTab(_this) {
	$('.tablinks').removeClass('active');
	$('.tabcontent').addClass('none');
	$('#'+_this.attr('data-id')).removeClass('none');
	$(_this).addClass('active');
}

function consultConfiguration() {
	$('.container-table-configuration').html(loading());
	var params = {
		action: 'consult-configuration',
		table_id: $('#consult-table').val(),
	}

	$.ajax({
		url: '/sistemaweb/combustibles/interface_innova.php',
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			console.log(data);
			$('.container-table-configuration').html(data);
		}
	});
}

function previewExport() {
	$('.container-preview').html(loading());
	var params = {
		action: 'preview',
		warehouse: $('#export-warehouse').val(),
		initial_date: $('#export-initial-date').val(),
		final_date: $('#export-final-date').val(),
	}

	var initial_date = params.initial_date.split("/");
	var final_date = params.final_date.split("/");
	var initial_day = parseInt(initial_date[0]);
	var final_day = parseInt(final_date[0]);

	initial_date = initial_date[1]+'/'+initial_date[2];
	final_date = final_date[1]+'/'+final_date[2];

	console.log('initial_date: '+initial_date+', final_date: '+final_date+' - '+'initial_day: '+initial_day+', final_day: '+final_day);

	if (initial_date == final_date) {
		if (initial_day <= final_day) {
			$.ajax({
				url: '/sistemaweb/combustibles/interface_innova.php',
				type: 'POST',
				dataType: 'html',
				data: params,
				success: function(data) {
					console.log(data);
					$('.container-preview').html(data);
				}
			});
		} else {
			$('.container-preview').html('<div class="container-alert"><div align="center" class="alert alert-info"><b>Importante</b>, Error en el rango de fechas.</div></div>');
		}
	} else {
		$('.container-preview').html('<div class="container-alert"><div align="center" class="alert alert-info"><b>Importante</b>, Las fechas a consultar deben estar en el mismo mes.</div></div>');
	}
}

function sendExport(_this) {
	console.log(_this);
	
	var params = {
		action: 'export',
		mode: _this.attr('data-mode'),
		warehouse: $('#export-warehouse').val(),
		initial_date: $('#in-export-initial-date').val(),
		final_date: $('#in-export-final-date').val(),
	}
	console.log(params);

	var initial_date = params.initial_date.split("/");
	var final_date = params.final_date.split("/");
	var initial_day = parseInt(initial_date[0]);
	var final_day = parseInt(final_date[0]);

	initial_date = initial_date[1]+'/'+initial_date[2];
	final_date = final_date[1]+'/'+final_date[2];

	console.log('initial_date: '+initial_date+', final_date: '+final_date);

	if(initial_date == final_date) {
		if (initial_day <= final_day) {
			var url = '/sistemaweb/combustibles/interface_innova.php?action='+params.action+'&warehouse='+params.warehouse+'&initial_date='+params.initial_date+'&final_date='+params.final_date+'&mode='+params.mode;
			window.open(url, '_blank');
			return false;
		} else {
			alert('Importante,  Error en el rango de fechas.');
		}
	} else {
		alert('Importante, Las fechas a consultar deben estar en el mismo mes.');
	}
}

function loading() {
	return '<div class="container-alert"><div align="center" class="alert alert-info">Cargando...</div></div>';
}

function viewTableBpartner(data) {
	var bpartner = data.bpartner
	var html = ``;
}

function updTableConfiguration(_this){
	var $arrParameterPOST = {
		action: 'upd-configuration',
		arrData: {
			id_tipo_tabla : $( '#consult-table' ).val(),
			id_tipo_tabla_detalle : _this.attr('data-id'),
			sap_codigo : $( '#sap-codigo-' + _this.attr('data-id') ).val(),
			name : $( '#name-' + _this.attr('data-id') ).val(),
			description : $( '#description-' + _this.attr('data-id') ).val(),
		},
	}

	$('.container-table-configuration').html(loading());

	$url = '/sistemaweb/combustibles/interface_innova.php';
	$.post( $url, $arrParameterPOST, function( response ) {
		console.log(response);
		$('.container-table-configuration').html(response);
	}, 'HTML')
    .fail(function() {
		$('.container-table-configuration').html('<div class="container-alert"><div align="center" class="alert alert-info">Problemas</div></div>');
    });
}

function saveTableConfiguration(_this){
	if ( $( '#ocs-codigo-ins' ).val().length == 0 ) {
		alert( 'Ingresar Código OCS' );
	} else if ( $( '#sap-codigo-ins' ).val().length == 0 ) {
		alert( 'Ingresar Código ERP' );
	} else {
		var $arrParameterPOST = {
			action: 'save-configuration',
			arrData: {
				id_tipo_tabla : $( '#consult-table' ).val(),
				ocs_codigo : $( '#ocs-codigo-ins' ).val(),
				sap_codigo : $( '#sap-codigo-ins' ).val(),
				name : $( '#name-ins' ).val(),
				description : $( '#description-ins' ).val(),
			},
		}

		$('.container-table-configuration').html(loading());

		$url = '/sistemaweb/combustibles/interface_innova.php';
		$.post( $url, $arrParameterPOST, function( response ) {
			console.log(response);
			$('.container-table-configuration').html(response);
		}, 'HTML')
	    .fail(function() {
			$('.container-table-configuration').html('<div class="container-alert"><div align="center" class="alert alert-info">Problemas</div></div>');
	    });
	}
}