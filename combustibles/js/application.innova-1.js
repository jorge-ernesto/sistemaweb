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
	$( "#consult-final-date" ).datepicker();

	$( "#export-initial-date" ).datepicker();
	$( "#export-final-date" ).datepicker();

	$(document).on('click', '.preview-export', function(event) {
		previewExport();
	});

	$(document).on('click', '.send-export', function(event) {
		sendExport($(this));
	});

	$(document).on('click', '.consult-configuration-table', function(event) {
		consultConfiguration();
	});

	$(document).on('click', '.upd-configuration', function(event) {
		updTableConfiguration($(this));
	});

	$(document).on('click', '.save-configuration', function(event) {
		saveTableConfiguration($(this));
	});
});