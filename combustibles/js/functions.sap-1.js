// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal
btn.onclick = function() {
	modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
	modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target == modal) {
		modal.style.display = "none";
	}
}	

function openTab(_this) {
	$('.tablinks').removeClass('active');
	$('.tabcontent').addClass('none');
	$('#'+_this.attr('data-id')).removeClass('none');
	$(_this).addClass('active');
}

function deleteExportDay(iID) {
	$('.container-table-exports').html(loading());
	var params = {
		action: 'delete-exports_day',
		iID: iID,
	}

	$.ajax({
		url: '/sistemaweb/combustibles/interface_sap.php',
		type: 'POST',
		dataType: 'JSON',
		data: params,
		success: function(data) {
			html = '';
			if (data.sStatus === 'success') {
				alert(data.sMessage);
				consultExports();
			} else {
				alert(data.sMessage);
			}
		}
	});
}

function consultExports() {
	$('.container-table-exports').html(loading());
	var params = {
		action: 'consult-exports',
		initial_systemdate: $('#consult-initial-date').val(),
	}

	$.ajax({
		url: '/sistemaweb/combustibles/interface_sap.php',
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			console.log(data);
			$('.container-table-exports').html(data);
		}
	});
}

function consultConfiguration() {
	/*** Requerimiento CRUD ***/
	var es_requerimiento_sap_energigas = $('.es_requerimiento_sap_energigas').val();		
	if(es_requerimiento_sap_energigas == true){
		var table_id = $('#consult-table').val();
		if(table_id == 1){
			$('.btn-add-centro-costo').show();		
		}
		if(table_id == 2){
			$('.btn-add-almacen').show();
		}
		if(table_id == 4){
			$('.btn-add-tarjeta-credito').show();
		}
		if(table_id == 5){
			$('.btn-add-fondo-efectivo').show();
		}
	}	
	/***/

	$('.container-table-configuration').html(loading());
	var params = {
		action: 'consult-configuration',
		table_id: $('#consult-table').val(),
	}

	$.ajax({
		url: '/sistemaweb/combustibles/interface_sap.php',
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			// console.log(data);
			$('.container-table-configuration').html(data);

			if(table_id == 1 || table_id == 2 || table_id == 4 || table_id == 5){
				$('.update').show();
				$('.delete').show();
				$('.buscar').show();
				$('.eliminar').show();
			}
		}
	});
}

function previewExport() {
	$('.container-preview').html(loading());
	var params = {
		action: 'preview',
		warehouse: $('#export-warehouse').val(),
		initial_date: $('#export-initial-date').val(),
	}

	$.ajax({
		url: '/sistemaweb/combustibles/interface_sap.php',
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			console.log(data);
			$('.container-preview').html(data);
			var html = '<button class="is-btn is-btn-default preview-export">Previsualizar</button><button class="is-btn is-btn-default send-export">Exportar</button>';
			$('.container-btn-export').html(html);
		}
	});
}

function sendExport() {
	var r = confirm('Realmente desea exportar '+$('#export-initial-date').val()+'?');
	if (r == true) {
		$('.container-preview').html(loading());
		var params = {
			action: 'export',
			warehouse: $('#export-warehouse').val(),
			initial_date: $('#export-initial-date').val(),
		}

		$.ajax({
			url: '/sistemaweb/combustibles/interface_sap.php',
			type: 'POST',
			dataType: 'json',
			data: params,
			success: function(data) {
				console.log(data);
				var html = '';
				if (data.error) {
					if (data.code >= 1 && data.code <= 3) {
						html = '<br><div align="center" class="alert alert-danger">'+data.message+'</div>';
						$('.container-preview').html(html);
						return false;
					} else if (data.code == 4) {
						html += '<br><div align="center" class="alert alert-danger">'+data.message+'</div>';
						var tables = data.tables;
						console.log(tables);
						html += '<div>Tabla: '+data.site+', Código: '+tables.errorCode+'</div>';
						$('.container-preview').html(html);
					}
				} else {
					html = '<br><div align="center" class="alert alert-info">Estado de conexión: Ok</div>';
					html += '<h5>Exportado:</h5><ul>';
					var tables = data.tables.tables;
					for (key in tables) {
						console.log('key:');
						console.log(key);
						html += '<li>'+key+'</li>';
					};
					html += '</ul>';
					$('.container-preview').html(html);
				}
			}
		});
	}
}

function loading() {
	return '<div class="container-alert"><div align="center" class="alert alert-info">Cargando...</div></div>';
}

function viewTableBpartner(data) {
	var bpartner = data.bpartner
	var html = ``;
}