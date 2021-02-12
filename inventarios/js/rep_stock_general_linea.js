var fecha	= new Date();
var annosistema	= "2010";
var anno	= fecha.getFullYear();
var annoact	= anno + 6;
var month 		= fecha.getMonth() + 1;

$( document ).ready(function() {

	for(var i = annosistema; i < annoact; i++){
		$("select[name=year]").append(new Option(i,i));
	}

	$( "select[name=year]" ).val( anno );

	if (month < 10)
		month = "0" + month;
	$( "select[name=month]" ).val( month );

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

	$('#btnbuscar').click(function(){

		var nualmacen 	= $('#nualmacen').val();
		var nucodlinea	= $('#combolinea').val();
		var nuyear	= $('#comboyear').val();
		var numonth	= $('#combomonth').val();

		var notipo 	= $('[name="notipo"]:checked').attr('value');


			$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			$.ajax({
				type	: "POST",
				url	: "reportes/c_rep_stock_general_linea.php",
				data	: {
						accion		: 'SearchLinea',
						nualmacen	: nualmacen,
						nucodlinea	: nucodlinea,
						nuyear		: nuyear,
						numonth		: numonth,
						notipo		: notipo,
					},
				success:function(xm){
					$('#cargardor').css({'display':'none'});
					$('#ListaStockLinea').html(xm);
				}
			});

        });

	$('#btnexcel').click(function(){


		var nualmacen 	= $('#nualmacen').val();
		var nucodlinea	= $('#combolinea').val();
		var nuyear	= $('#comboyear').val();
		var numonth	= $('#combomonth').val();
		var notipo 	= $('[name="notipo"]:checked').attr('value');

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url	: "reportes/c_rep_stock_general_linea.php",
			data	: {
					accion		: 'SearchLineaExcel',
					nualmacen	: nualmacen,
					nucodlinea	: nucodlinea,
					nuyear		: nuyear,
					numonth		: numonth,
					notipo		: notipo,
				},
			success:function(xm){
				$('#cargardor').css({'display':'none'});
				$('#ListaStockLinea').html(xm);
				location.href = "/sistemaweb/inventarios/reportes/rep_stock_general_linea_excel.php";

			}
		});


        });

});

