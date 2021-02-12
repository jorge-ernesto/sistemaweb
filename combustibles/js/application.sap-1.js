$.datepicker.regional['es'] = {
	closeText: 'Cerrar',
	prevText: '< Ant',
	nextText: 'Sig >',
	currentText: 'Hoy',
	monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
	monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
	dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
	dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
	dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
	weekHeader: 'Sm',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
 };
$.datepicker.setDefaults($.datepicker.regional['es']);
$(document).ready(function() {
	$(document).on('click', '.tablinks', function(event) {
		//event.preventDefault();
		openTab($(this));
	});

	$( "#consult-initial-date" ).datepicker();
	$( "#consult-end-date" ).datepicker();

	$( "#export-initial-date" ).datepicker();
	$( "#export-end-date" ).datepicker();

	$(document).on('click', '.remove-export', function(event) {
		deleteExportDay( $( this ).data("id") );
	});

	$(document).on('click', '.btn-consult-exports', function(event) {
		consultExports();
	});

	$(document).on('click', '.preview-export', function(event) {
		previewExport();
	});

	$(document).on('click', '.send-export', function(event) {
		sendExport();
	});

	/*** Requerimiento CRUD ***/	
	$('.is-select').on('change', function() {		
		$('.btn-add-centro-costo').hide();
		$('.btn-add-almacen').hide();		
		$('.btn-add-tarjeta-credito').hide();
		$('.btn-add-fondo-efectivo').hide();
	});

	$('.btn-add-centro-costo').on('click', function() {
		$('#divFormularioCentroCosto').show();
		$('#divTablaEquivalencias').hide();
	});
	$('.btn-add-almacen').on('click', function() {
		$('#divFormularioAlmacen').show();
		$('#divTablaEquivalencias').hide();
	});
	$('.btn-add-tarjeta-credito').on('click', function() {
		$('#divFormularioTarjetaCredito').show();
		$('#divTablaEquivalencias').hide();
	});
	$('.btn-add-fondo-efectivo').on('click', function() {
		$('#divFormularioFondoEfectivo').show();
		$('#divTablaEquivalencias').hide();
	});

	$(document).on('click', '.back', function() {
		consultConfiguration();
	});	
	/***/

	/*** Guardar/editar Centro Costo ***/
	$(document).on('click', '.crear-centro-costo', function(e) {
		id = $('#id-centro-costo').val();		
		var r = (id == "") ? confirm('Realmente desea crear centro costo?') : confirm('Realmente desea editar centro costo?');
		
		if(r == true){
			guardarCentroCosto(e);
			return;
		}
		e.preventDefault();		
	});

	function guardarCentroCosto(e) {
		e.preventDefault(); // avoid to execute the actual submit of the form
		$('.crear-centro-costo').attr('disabled', true); // Si usamos boolean no usar comillas simples
		var formData = new FormData($('#formularioCentroCosto')[0]);
	
		$.ajax({
			data: formData, // Lo que se envie a través de variables se obtiene por el método que se especifique
			method: 'post',
			url: '/sistemaweb/combustibles/movimientos/c_sap-1.php?action=guardar-centro-costo', // Lo que se envia a través de la url se obtiene por el método get
			contentType: false,
			processData: false,
			success: function(data) {
				alert(data);
				consultConfiguration();
			}
		});
	}	
	/***/

	/*** Guardar/editar Almacen ***/
	$(document).on('click', '.crear-almacen', function(e) {
		id = $('#id-almacen').val();		
		var r = (id == "") ? confirm('Realmente desea crear almacen?') : confirm('Realmente desea editar almacen?');
		
		if(r == true){
			guardarAlmacen(e);
			return;
		}
		e.preventDefault();		
	});

	function guardarAlmacen(e) {
		e.preventDefault(); // avoid to execute the actual submit of the form
		$('.crear-almacen').attr('disabled', true); // Si usamos boolean no usar comillas simples
		var formData = new FormData($('#formularioAlmacen')[0]);
	
		$.ajax({
			data: formData, // Lo que se envie a través de variables se obtiene por el método que se especifique
			method: 'post',
			url: '/sistemaweb/combustibles/movimientos/c_sap-1.php?action=guardar-almacen', // Lo que se envia a través de la url se obtiene por el método get
			contentType: false,
			processData: false,
			success: function(data) {
				alert(data);
				consultConfiguration();
			}
		});
	}	
	/***/

	/*** Guardar/editar Tarjeta Credito ***/
	$(document).on('click', '.crear-tarjeta-credito', function(e) {
		id = $('#id-tarjeta-credito').val();		
		var r = (id == "") ? confirm('Realmente desea crear tarjeta credito?') : confirm('Realmente desea editar tarjeta credito?');
		
		if(r == true){
			guardarTarjetaCredito(e);
			return;
		}
		e.preventDefault();		
	});

	function guardarTarjetaCredito(e) {
		e.preventDefault(); // avoid to execute the actual submit of the form
		$('.crear-tarjeta-credito').attr('disabled', true); // Si usamos boolean no usar comillas simples
		var formData = new FormData($('#formularioTarjetaCredito')[0]);
	
		$.ajax({
			data: formData, // Lo que se envie a través de variables se obtiene por el método que se especifique
			method: 'post',
			url: '/sistemaweb/combustibles/movimientos/c_sap-1.php?action=guardar-tarjeta-credito', // Lo que se envia a través de la url se obtiene por el método get
			contentType: false,
			processData: false,
			success: function(data) {
				alert(data);
				consultConfiguration();
			}
		});
	}	
	/***/

	/*** Guardar/editar Fondo Efectivo ***/
	$(document).on('click', '.crear-fondo-efectivo', function(e) {
		id = $('#id-fondo-efectivo').val();		
		var r = (id == "") ? confirm('Realmente desea crear fondo efectivo?') : confirm('Realmente desea editar fondo efectivo?');
		
		if(r == true){
			guardarFondoEfectivo(e);
			return;
		}
		e.preventDefault();		
	});

	function guardarFondoEfectivo(e) {
		e.preventDefault(); // avoid to execute the actual submit of the form
		$('.crear-fondo-efectivo').attr('disabled', true); // Si usamos boolean no usar comillas simples
		var formData = new FormData($('#formularioFondoEfectivo')[0]);
	
		$.ajax({
			data: formData, // Lo que se envie a través de variables se obtiene por el método que se especifique
			method: 'post',
			url: '/sistemaweb/combustibles/movimientos/c_sap-1.php?action=guardar-fondo-efectivo', // Lo que se envia a través de la url se obtiene por el método get
			contentType: false,
			processData: false,
			success: function(data) {
				alert(data);
				consultConfiguration();
			}
		});
	}	
	/***/

	/*** Buscar ***/
	$(document).on('click', '.buscar', function(e) {
		value                 = this.value;
		value_split           = value.split(',');		
		id_tipo_tabla         = value_split[0];
		id_tipo_tabla_detalle = value_split[1];

		//Buscar Centro Costo
		//En la tabla sap_mapeo_tabla, el codigo para Centro Costo es 1
		if(id_tipo_tabla == 1){ 
			$.post('/sistemaweb/combustibles/movimientos/c_sap-1.php?action=buscar', {id_tipo_tabla:id_tipo_tabla,id_tipo_tabla_detalle:id_tipo_tabla_detalle}, function(data) {                
				data = JSON.parse(data);						
				console.log(data);
				$('#id-centro-costo').val(data.id_tipo_tabla_detalle);
				$('#nombre-centro-costo').val(data.name);
				$('#consult-warehouse-centro-costo').val(data.opencomb_codigo);
				$('#codigo-sap-centro-costo').val(data.sap_codigo);
				
				$('#divFormularioCentroCosto').show();
				$('#divTablaEquivalencias').hide();				
			});	
		}

		//Buscar Almacen
		//En la tabla sap_mapeo_tabla, el codigo para Almacen es 2
		if(id_tipo_tabla == 2){ 
			$.post('/sistemaweb/combustibles/movimientos/c_sap-1.php?action=buscar', {id_tipo_tabla:id_tipo_tabla,id_tipo_tabla_detalle:id_tipo_tabla_detalle}, function(data) {                
				data = JSON.parse(data);						
				console.log(data);
				$('#id-almacen').val(data.id_tipo_tabla_detalle);
				$('#nombre-almacen').val(data.name);
				$('#consult-warehouse-almacen').val(data.opencomb_codigo);
				$('#codigo-sap-almacen').val(data.sap_codigo);
				
				$('#divFormularioAlmacen').show();
				$('#divTablaEquivalencias').hide();				
			});	
		}

		//Buscar Tarjeta Credito
		//En la tabla sap_mapeo_tabla, el codigo para Tarjeta Credito es 4
		if(id_tipo_tabla == 4){ 
			$.post('/sistemaweb/combustibles/movimientos/c_sap-1.php?action=buscar', {id_tipo_tabla:id_tipo_tabla,id_tipo_tabla_detalle:id_tipo_tabla_detalle}, function(data) {                
				data = JSON.parse(data);						
				console.log(data);
				$('#id-tarjeta-credito').val(data.id_tipo_tabla_detalle);
				$('#nombre-tarjeta-credito').val(data.name);
				$('#consult-tarjeta-credito').val(data.opencomb_codigo);
				$('#codigo-sap-tarjeta-credito').val(data.sap_codigo);
				
				$('#divFormularioTarjetaCredito').show();
				$('#divTablaEquivalencias').hide();
			});	
		}

		//Buscar Fondo Efectivo
		//En la tabla sap_mapeo_tabla, el codigo para Fondo Efectivo es 5
		if(id_tipo_tabla == 5){ 
			$.post('/sistemaweb/combustibles/movimientos/c_sap-1.php?action=buscar', {id_tipo_tabla:id_tipo_tabla,id_tipo_tabla_detalle:id_tipo_tabla_detalle}, function(data) {                
				data = JSON.parse(data);						
				console.log(data);
				$('#id-fondo-efectivo').val(data.id_tipo_tabla_detalle);
				$('#nombre-fondo-efectivo').val(data.name);
				$('#consult-fondo-efectivo').val(data.opencomb_codigo);
				$('#codigo-sap-fondo-efectivo').val(data.sap_codigo);
				
				$('#divFormularioFondoEfectivo').show();
				$('#divTablaEquivalencias').hide();
			});	
		}
	});
	/***/

	/*** Eliminar ***/
	$(document).on('click', '.eliminar', function(e) {		
		//alert(this.value); //id_tipo_tabla, id_tipo_tabla_detalle
		value = this.value;		

		var r = confirm('Realmente desea eliminar registro?');
		if(r == true){			
			$.post('/sistemaweb/combustibles/movimientos/c_sap-1.php?action=eliminar', {value:value}, function(data) {
				alert(data);
				consultConfiguration();
            });
		}
	});	
	/***/

	$(document).on('click', '.consult-configuration-table', function(event) {
		consultConfiguration();
	});	
});