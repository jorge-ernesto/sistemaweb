function getFechasIF(){
	$.datepicker.regional['es'] = {
	    closeText: 'Cerrar',
	    prevText: '<Ant',
	    nextText: 'Sig>',
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

	$( "#dialog" ).dialog({
    	autoOpen: false,
    	modal: true,
    });

	var msg_fe_mes = "Ambas fechas deben coincidir en el mismo mes";
	var msg_fe_anio = "Ambas fechas deben coincidir en el mismo año";

	$( "#txt-fe_inicial" ).datepicker({
		changeMonth: true,
		changeYear: true,
		maxDate: $("#txt-fe_final").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_final").datepicker("option", "minDate", selectedDate);
		},
		onSelect:function(fe_inicial,obj){
			$("#btn-html").prop( "disabled", true );
			$("#btn-excel").prop( "disabled", true );
			$("#btn-pdf").prop( "disabled", true );
			var fe_final 	= $('#txt-fe_final').val();
			if(fe_final.substr(3, 2) != fe_inicial.substr(3, 2)){
				$(".msg-validacion_fe_inicial").html(msg_fe_mes);
				$(".msg-validacion_fe_final").html(msg_fe_mes);
			}else if(fe_final.substr(6, 4) != fe_inicial.substr(6, 4)){
				$(".msg-validacion_fe_inicial").html(msg_fe_anio);
				$(".msg-validacion_fe_final").html(msg_fe_anio);
			} else{
				$(".msg-validacion_fe_inicial").html("");
				$(".msg-validacion_fe_final").html("");
				$("#btn-html").prop( "disabled", false );
				$("#btn-excel").prop( "disabled", false );
				$("#btn-pdf").prop( "disabled", false );
			}
		}
	})

    $( "#txt-fe_final" ).datepicker({
    	changeMonth: true,
    	changeYear: true,
		minDate: $("#txt-fe_inicial").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_inicial").datepicker("option", "maxDate", selectedDate);
		},
		onSelect:function(fe_final,obj){
			$("#btn-html").prop( "disabled", true );
			$("#btn-excel").prop( "disabled", true );
			$("#btn-pdf").prop( "disabled", true );
			var fe_inicial 	= $('#txt-fe_inicial').val();
			if(fe_inicial.substr(3, 2) != fe_final.substr(3, 2)){
				$(".msg-validacion_fe_inicial").html(msg_fe_mes);
				$(".msg-validacion_fe_final").html(msg_fe_mes);
			}else if(fe_inicial.substr(6, 4) != fe_final.substr(6, 4)){
				$(".msg-validacion_fe_inicial").html(msg_fe_anio);
				$(".msg-validacion_fe_final").html(msg_fe_anio);
			} else{
				$(".msg-validacion_fe_inicial").html("");
				$(".msg-validacion_fe_final").html("");
				$("#btn-html").prop( "disabled", false );
				$("#btn-excel").prop( "disabled", false );
				$("#btn-pdf").prop( "disabled", false );
			}
		}
    })

    /* Fidelizacion */
	$( "#fechainicio" ).datepicker({
		changeMonth: true,
		changeYear: true,
		maxDate: $("#fechafin").val(),
		onClose: function (selectedDate) {
			$("#fechafin").datepicker("option", "minDate", selectedDate);
		},
	})

    $( "#fechafin" ).datepicker({
    	changeMonth: true,
    	changeYear: true,
		minDate: $("#fechainicio").val(),
		onClose: function (selectedDate) {
			$("#fechainicio").datepicker("option", "maxDate", selectedDate);
		},
    })

    /* Fidelizacion - Campaña */
	$( "#campaniafechaini" ).datepicker({
		changeMonth: true,
		changeYear: true,
		minDate: $( "#campaniafechaini" ).val(),
		onClose: function (selectedDate) {
			$("#campaniafechafin").datepicker("option", "minDate", selectedDate);
		},
	})

    $( "#campaniafechafin" ).datepicker({
    	changeMonth: true,
    	changeYear: true,
		minDate: $("#campaniafechaini").val(),
		onClose: function (selectedDate) {
			$("#campaniafechaini").datepicker("option", "maxDate", selectedDate);
		},
    })

    /* Reporte - para visualizar solo información de fecha cerradas de la tabla pos_aprosys */
	$( "#txt-dInicial" ).datepicker({
		changeMonth: true,
		changeYear: true,
		maxDate: $( "#txt-dUltimoCierre" ).val(),
		onClose: function (selectedDate) {
			$( "#txt-dFinal" ).datepicker("option", "minDate", selectedDate);
		},
	})

    $( "#txt-dFinal" ).datepicker({
    	changeMonth: true,
    	changeYear: true,
    	minDate: $( "#txt-dInicial" ).val(),
		maxDate: $( "#txt-dUltimoCierre" ).val(),
		onClose: function (selectedDate) {
			$("#txt-dInicial").datepicker("option", "maxDate", selectedDate);
		},
    })

    /* Orden Compra */
    $( "#txt-dFechaEmision" ).datepicker({
		changeMonth: true,
		changeYear: true,
		maxDate: $( "#txt-dUltimoCierre" ).val(),
		onClose: function (selectedDate) {
			$( "#txt-dFinal" ).datepicker("option", "minDate", selectedDate);
		},
	})

	/* Vencimiento de Lotes */
    $( "#txt-dFechaVencimiento" ).datepicker({
		changeMonth: true,
		changeYear: true,
		onClose: function (selectedDate) {
			$( "#txt-dFechaVencimiento" ).datepicker("option", "minDate", selectedDate);
		},
	})

    /* Interface SAP */
    // Verificar estado de conexión
	var $estadoConexionSAP = $( "#txt-estadoConexionSAP" );
	var $estadoOpensoft = $( "#txt-estadoOpensoft" );
	if($estadoConexionSAP.val() !== undefined && $estadoOpensoft.val() !== undefined){
		console.log("Opensfot Configuration Status -> " + $estadoOpensoft.val());
		console.log("SAP Conecction Status -> " + $estadoConexionSAP.val());
		$( "#btn-html" ).prop( "disabled", true );
		if ($estadoOpensoft.val() == 1) {
			$( "#btn-html" ).prop( "disabled", false );
			if ($estadoConexionSAP.val() == 1) {
				$( "#btn-html" ).prop( "disabled", false );
			} else {
				$( ".div-errorMsg" ).append("<span style='color: red'>No hay conexión a HANA - SAP</span>");
			}
		} else {
			$( ".div-errorMsg" ).append("<span style='color: red'>No se ha configurado los parametros de conexión a HANA - SAP</span>");
		}
	}
}