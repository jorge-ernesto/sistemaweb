$( document ).ready(function() {
	var error = 'Error: ';

	/* FORMATO HOY WHIT JQUERY */
	var hoy 	= new Date();
	var dd 		= hoy.getDate();
	var mm 		= hoy.getMonth()+1; //hoy es 0!
	var yyyy 	= hoy.getFullYear();

	if(dd < 10)
	    dd = '0' + dd;

	if(mm < 10)
	    mm = '0' + mm;

	hoy = yyyy + '-' + mm + '-' + dd;

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
		onSelect:function(txtnofechaini,obj){

			var txtnofechafin 	= $('#txtnofechafin').val();

			fechainicial 	= txtnofechaini.substr(6, 4) + '-' + txtnofechaini.substr(3, 2) + '-' + txtnofechaini.substr(0, 2);
			fechafinal 		= txtnofechafin.substr(6, 4) + '-' + txtnofechafin.substr(3, 2) + '-' + txtnofechafin.substr(0, 2);

			if(hoy < fechainicial){
				$(".MsgValidacion_Fecha_Inicial").html(error + "La Fecha inicial no debe de ser mayor a la Fecha Actual: " + hoy);
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else if(txtnofechaini.length < 1){
				$(".MsgValidacion_Fecha_Inicial").html(error + "Debes ingresar una fecha inicial");
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else if (txtnofechafin.length < 1){
				$(".MsgValidacion_Fecha_Final").html(error + "Debes ingresar una fecha final");
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else if(txtnofechafin.substr(3, 2) != txtnofechaini.substr(3, 2)){
				$(".MsgValidacion_Fecha_Inicial").html(error + "Ambas fechas deben coincidir en el mismo mes");
				$(".MsgValidacion_Fecha_Final").html(error + "Ambas fechas deben coincidir en el mismo mes");
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else if(txtnofechafin.substr(6, 4) != txtnofechaini.substr(6, 4)){
				$(".MsgValidacion_Fecha_Inicial").html(error + "Ambas fechas deben coincidir en el mismo año");
				$(".MsgValidacion_Fecha_Final").html(error + "Ambas fechas deben coincidir en el mismo año");
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else{
				if(hoy < fechafinal){
					$(".MsgValidacion_Fecha_Final").html(error + "La Fecha final no debe de ser mayor a la Fecha Actual: " + hoy);
					$("#btnbuscar").prop( "disabled", true );
					$("#btntxt").prop( "disabled", true );
				}else{
					$(".MsgValidacion_Fecha_Final").html("");
					$("#btnbuscar").prop( "disabled", false );
					$("#btntxt").prop( "disabled", false );
				}
				$(".MsgValidacion_Fecha_Inicial").html("");
			}
		}
	});

	$("#txtnofechafin").datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect:function(txtnofechafin,obj){

			var txtnofechaini 	= $('#txtnofechaini').val();

			fechainicial 	= txtnofechaini.substr(6, 4) + '-' + txtnofechaini.substr(3, 2) + '-' + txtnofechaini.substr(0, 2);
			fechafinal 		= txtnofechafin.substr(6, 4) + '-' + txtnofechafin.substr(3, 2) + '-' + txtnofechafin.substr(0, 2);

	   		if(hoy < fechafinal){
				$(".MsgValidacion_Fecha_Final").html(error + "La Fecha final no debe de ser mayor a la Fecha Actual: " + hoy);
				$("#btnbuscar").prop( "disabled", true );
			}else if(txtnofechaini.length < 1){
				$(".MsgValidacion_Fecha_Inicial").html(error + "Debes ingresar una fecha inicial");
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else if (txtnofechafin.length < 1){
				$(".MsgValidacion_Fecha_Final").html(error + "Debes ingresar una fecha final");
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else if(txtnofechaini.substr(3, 2) != txtnofechafin.substr(3, 2)){
				$(".MsgValidacion_Fecha_Inicial").html(error + "Ambas fechas deben coincidir en el mismo mes");
				$(".MsgValidacion_Fecha_Final").html(error + "Ambas fechas deben coincidir en el mismo mes");
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else if(txtnofechaini.substr(6, 4) != txtnofechafin.substr(6, 4)){
				$(".MsgValidacion_Fecha_Inicial").html(error + "Ambas fechas deben coincidir en el mismo año");
				$(".MsgValidacion_Fecha_Final").html(error + "Ambas fechas deben coincidir en el mismo año");
				$("#btnbuscar").prop( "disabled", true );
				$("#btntxt").prop( "disabled", true );
			}else{
				if(hoy < fechainicial){
					$(".MsgValidacion_Fecha_Inicial").html(error + "La Fecha Inicial no debe de ser mayor a la Fecha Actual: " + hoy);
					$("#btnbuscar").prop( "disabled", true );
					$("#btntxt").prop( "disabled", true );
				}else{
					$(".MsgValidacion_Fecha_Inicial").html("");
					$("#btnbuscar").prop( "disabled", false );
					$("#btntxt").prop( "disabled", false );
				}
				$(".MsgValidacion_Fecha_Final").html("");
			}
		}
	});

	$( '#btnbuscar' ).click(function(){
		var cboalmacen		= $('#cboalmacen').val();
		var cbomtc 			= $('#cbomtc').val();
		var cbotv 			= $('#cbotv').val();
		var txtnofechaini 	= $('#txtnofechaini').val();
		var txtnofechafin 	= $('#txtnofechafin').val();
		var iCantidadEnviado = $( '#cbo-veces_enviada' ).val();

		txtnofechaini = txtnofechaini.substr(6, 4) + '-' + txtnofechaini.substr(3, 2) + '-' + txtnofechaini.substr(0, 2);
		txtnofechafin = txtnofechafin.substr(6, 4) + '-' + txtnofechafin.substr(3, 2) + '-' + txtnofechafin.substr(0, 2);

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_contingencia_facturacion_electronica.php",
			data	: {
					accion			: 'Search',
					cboalmacen		: cboalmacen,
					cbomtc			: cbomtc,
					cbotv			: cbotv,
					txtnofechaini	: txtnofechaini,
					txtnofechafin	: txtnofechafin,
					iCantidadEnviado : iCantidadEnviado,
			},
			success:function(data){
				$('#cargardor').css({'display':'none'});
				$('#GridviewContingenciaFE').html(data);
			}
		});
	});

	$('#btntxt').click(function(){

		var cboalmacen		= $('#cboalmacen').val();
		var cbomtc 			= $('#cbomtc').val();
		var cbotv 			= $('#cbotv').val();
		var txtnofechaini 	= $('#txtnofechaini').val();
		var txtnofechafin 	= $('#txtnofechafin').val();
		var iCantidadEnviado = $( '#cbo-veces_enviada' ).val();

		txtnofechaini = txtnofechaini.substr(6, 4) + '-' + txtnofechaini.substr(3, 2) + '-' + txtnofechaini.substr(0, 2);
		txtnofechafin = txtnofechafin.substr(6, 4) + '-' + txtnofechafin.substr(3, 2) + '-' + txtnofechafin.substr(0, 2);

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_contingencia_facturacion_electronica.php",
			data	: {
					accion			: 'GenerarTxt',
					cboalmacen		: cboalmacen,
					cbomtc			: cbomtc,
					cbotv			: cbotv,
					txtnofechaini	: txtnofechaini,
					txtnofechafin	: txtnofechafin,
					iCantidadEnviado : iCantidadEnviado,
			},
			success:function(data){
				$('#cargardor').css({'display':'none'});
				$('#GridviewContingenciaFE').html(data);
				location.href="/sistemaweb/combustibles/reportes/contingencia_facturacion_electronica_texto.php";
			}
		});

        });

});

