$( document ).ready(function() {          
	$('#btnbuscar').click(function() {
		var nualmacen 	= $('#nualmacen').val();
		var nucodlinea	= $('#combolinea').val();
		var nuyear	= $('#comboyear').val();
		var numonth	= $('#combomonth').val();
		var cod_art 	= $('#cod_art').val();
		var desc_art 	= $('#desc_art').val();

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_saldos_mensuales.php",
			data	: {
				accion		: 'search',
				nualmacen	: nualmacen,
				nucodlinea	: nucodlinea,
				nuyear		: nuyear,
				numonth		: numonth,
				cod_art		: cod_art,
				desc_art	: desc_art,
			},
			success:function(data) {
				$('#cargardor').css({'display':'none'});
				$('#SaldosMensuales').html(data);
			}
		});
	});

	$('#btnexcel').click(function() {
		var nualmacen 	= $('#nualmacen').val();
		var nucodlinea	= $('#combolinea').val();
		var nuyear	= $('#comboyear').val();
		var numonth	= $('#combomonth').val();
		var cod_art 	= $('#cod_art').val();
		var desc_art 	= $('#desc_art').val();

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_saldos_mensuales.php",
			data	: {
					accion		: 'exportExcel',
					nualmacen	: nualmacen,
					nucodlinea	: nucodlinea,
					nuyear		: nuyear,
					numonth		: numonth,
					cod_art		: cod_art,
					desc_art	: desc_art,
			},
			success:function(data) {
				$('#cargardor').css({'display':'none'});
				$('#SaldosMensuales').html(data);
				location.href = "/sistemaweb/inventarios/reportes/rep_saldos_mensuales.php";
			}
		});
	});
});