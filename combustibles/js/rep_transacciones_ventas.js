$( document ).ready(function() {

	$.datepicker.regional['es'] = {
		    closeText: 'Cerrar',
		    prevText: '<Ant',
		    nextText: 'Sig>',
		    currentText: 'Hoy',
		    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
		    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
		    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
		    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
		    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
		    weekHeader: 'Sm',
		    dateFormat: 'dd/mm/yy',
		    firstDay: 1,
		    isRTL: false,
		    showMonthAfterYear: false,
		    yearSuffix: ''
	};

	$.datepicker.setDefaults($.datepicker.regional['es']); 

	$("#txtnofechaini").datepicker({
		changeMonth: true,
		changeYear: true,
	});

	$("#txtnofechafin").datepicker({
		changeMonth: true,
		changeYear: true,
	});

	$('#btnbuscar').click(function(){

		var cmbnualmacen 		= $('#cmbnualmacen').val();
		var txtnofechaini 		= $('#txtnofechaini').val();
		var txtnofechafin 		= $('#txtnofechafin').val();
		var rdnotipo 			= $('[name="rdnotipo"]:checked').attr('value');

		if(txtnofechaini.length < 1){
			alert('Debes ingresar una fecha inicial');
		}else if (txtnofechafin.length < 1){
			alert('Debes ingresar una fecha final');
		}else if(txtnofechaini.substr(3, 2) != txtnofechafin.substr(3, 2)){
			alert('Ambas fechas deben coincidir en el mismo mes');
		}else if(txtnofechaini.substr(6, 4) != txtnofechafin.substr(6, 4)){
			alert('Ambas fechas deben coincidir en el mismo año');
		}else{

			$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			$.ajax({
				type	: "POST",
				url		: "reportes/c_rep_transacciones_ventas.php",
				data	: {
						accion				: 'Search',
						cmbnualmacen		: cmbnualmacen,
						txtnofechaini		: txtnofechaini,
						txtnofechafin		: txtnofechafin,
						rdnotipo			: rdnotipo,
				},
				success:function(data){
					$('#cargardor').css({'display':'none'});
					$('#TransaccionVentaDetalle').html(data);
				}
			});

		}

	});

	$('#btnexcel').click(function(){

		var cmbnualmacen 		= $('#cmbnualmacen').val();
		var txtnofechaini 		= $('#txtnofechaini').val();
		var txtnofechafin 		= $('#txtnofechafin').val();
		var rdnotipo 			= $('[name="rdnotipo"]:checked').attr('value');

		if(txtnofechaini.length < 1){
			alert('Debes ingresar una fecha inicial');
		}else if (txtnofechafin.length < 1){
			alert('Debes ingresar una fecha final');
		}else if(txtnofechaini.substr(3, 2) != txtnofechafin.substr(3, 2)){
			alert('Ambas fechas deben coincidir en el mismo mes');
		}else if(txtnofechaini.substr(6, 4) != txtnofechafin.substr(6, 4)){
			alert('Ambas fechas deben coincidir en el mismo año');
		}else{

			$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			$.ajax({
				type	: "POST",
				url		: "reportes/c_rep_transacciones_ventas.php",
				data	: {
						accion				: 'SearchExcel',
						cmbnualmacen		: cmbnualmacen,
						txtnofechaini		: txtnofechaini,
						txtnofechafin		: txtnofechafin,
						rdnotipo			: rdnotipo,
				},
				success:function(data){
					$('#cargardor').css({'display':'none'});
					$('#TransaccionVentaDetalle').html(data);
					location.href="/sistemaweb/combustibles/reportes/rep_transacciones_ventas_excel.php";
				}
			});

		}

        });

});

