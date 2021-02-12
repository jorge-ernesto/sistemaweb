$( document ).ready(function() {          
	$('#btnbuscar').click(function() {
		var cod_almacen 	= $('#cod_almacen').val();
		var cod_linea	= $('#cod_linea').val();
		var periodo	= $('#periodo').val();
		var modo	= $('#modo').val();

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_ventas_mensuales.php",
			data	: {
				accion		: 'search',
				cod_almacen	: cod_almacen,
				cod_linea	: cod_linea,
				periodo		: periodo,
				modo		: modo,
			},
			success:function(data) {
				$('#cargardor').css({'display':'none'});
				// console.log(data);
				$('#VentasMensuales').html(data);
			}
		});
	});

	$('#btnexcel').click(function() {
		var cod_almacen 	= $('#cod_almacen').val();
		var cod_linea	= $('#cod_linea').val();
		var periodo	= $('#periodo').val();
		var modo	= $('#modo').val();

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_ventas_mensuales.php",
			data	: {
					accion		: 'exportExcel',
					cod_almacen	: cod_almacen,
					cod_linea	: cod_linea,
					periodo		: periodo,
					modo		: modo,
			},
			success:function(data) {
				$('#cargardor').css({'display':'none'});
				// console.log(data);
				$('#VentasMensuales').html(data);				
				location.href = "/sistemaweb/ventas_clientes/reportes/rep_ventas_mensuales.php";
			}
		});
	});
});