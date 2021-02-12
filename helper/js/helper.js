$( function() {

	$.datepicker.setDefaults( $.datepicker.regional[ "es" ] );

	$.datepicker.regional['es'] = {
	    closeText: 'Cerrar',
	    prevText: '<Ant',
	    nextText: 'Sig>',
	    currentText: 'Hoy',
	    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
	    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
	    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
	    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
	    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
	    weekHeader: 'Sm',
	    dateFormat: 'dd/mm/yy',
	    firstDay: 1,
	    isRTL: false,
	    showMonthAfterYear: false,
	    yearSuffix: ''
	};

	$( "#fecha_inicio" ).datepicker({changeMonth: true, changeYear: true});
    $( "#fecha_final" ).datepicker({changeMonth: true, changeYear: true});  
    
});

function returnValue(name, src)
{
    var s = document.getElementById(src);
    var a = opener.document.getElementsByName(name);
    var dest = a[0];
    
    dest.value = s.value;
    
    window.close();
}
