$( document ).ready(function() {

//alert('hola');
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

	$('#nualmacen').change(function(){

		var nualmacen	= $(this).val();
		var noalmacen	= $("#nualmacen option:selected").html();

		if(nualmacen == 'T'){
			alert('Seleccionar Almacen');
			$('#nuubicacion').html('');
		}else{
		        $('#cargardor').css({'display':'block'});
		        $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		        $.ajax({
				type	: "POST",
				url	: "reportes/c_rep_ajuste_ubicacion.php",
				data	: {
						accion		: 'GetUbicacion',
						nualmacen	: nualmacen
				},
				success:function(xm){
					var json = eval('('+xm+')');
		                        $('#noalmacen').val(noalmacen);
					$('#nuubicacion').html(json.msg);
					$('#cargardor').css({'display':'none'});
				}
			});
		}
	});
                

	$("#fbuscar").datepicker({
		changeMonth: true,
		changeYear: true,
	});
          
	$('#btnbuscar').click(function(){

		var nualmacen 	= $('#nualmacen').val();
		var nuubicacion	= $('#nuubicacion').val();
		var fbuscar 	= $('#fbuscar').val();
		var notipo 	= $('[name="notipo"]:checked').attr('value');

		if(nualmacen == 'T'){
			alert('Debes de seleccionar un Almacen');
		}else if(fbuscar.length < 1){
			alert('Debes Ingresar Fecha');
		}else{

			$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			$.ajax({
				type	: "POST",
				url	: "reportes/c_rep_ajuste_ubicacion.php",
				data	: {
						accion		: 'BuscarUbicacion',
						nualmacen	: nualmacen,
						nuubicacion	: nuubicacion,
						fbuscar		: fbuscar,
						notipo		: notipo,
					},
				success:function(xm){
					$('#cargardor').css({'display':'none'});
					$('#AjusteUbicacionDetalle').html(xm);
				}
			});

		}

        });

	$('#btnexcel').click(function(){

		var nualmacen 	= $('#nualmacen').val();
		var nuubicacion	= $('#nuubicacion').val();
		var fbuscar 	= $('#fbuscar').val();
		var notipo 	= $('[name="notipo"]:checked').attr('value');

		if(nualmacen == 'T'){
			alert('Debes de seleccionar un Almacen');
		}else if(fbuscar.length < 1){
			alert('Debes Ingresar Fecha');
		}else{

			$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			$.ajax({
				type	: "POST",
				url	: "reportes/c_rep_ajuste_ubicacion.php",
				data	: {
						accion		: 'BuscarUbicacionExcel',
						nualmacen	: nualmacen,
						nuubicacion	: nuubicacion,
						fbuscar		: fbuscar,
						notipo		: notipo,
					},
				success:function(xm){
					$('#cargardor').css({'display':'none'});
					$('#AjusteUbicacionDetalle').html(xm);
					location.href="/sistemaweb/inventarios/reportes/rep_ajuste_ubicacion_excel.php";

				}
			});

		}

        });

});

