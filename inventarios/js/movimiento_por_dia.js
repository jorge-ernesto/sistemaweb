$( document ).ready(function() {  

	$('#btnbuscar').click(function() {
		var nualmacen 	= $('#nualmacen').val();
		var fecha_inicio = $('#fecha_inicio').val();
		var fecha_final = $('#fecha_final').val();

	
		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_mov_por_dia.php",
			data	: {
				accion		: 'search',
				nualmacen	: nualmacen,
				fecha_inicio: fecha_inicio,
				fecha_final : fecha_final,
			},
			success:function(data) {
				$('#cargardor').css({'display':'none'});
				$('#ListaStockLinea').html(data);
			}
		});
	});

	$('#btnexcel').click(function() {
		var nualmacen 	 = $('#nualmacen').val();
		var fecha_inicio = $('#fecha_inicio').val();
		var fecha_final  = $('#fecha_final').val();

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_mov_por_dia.php",
			data	: {
					accion		: 'exportExcel',
					nualmacen	: nualmacen,
					fecha_inicio: fecha_inicio,
					fecha_final : fecha_final,
			},
			success:function(data) {
				$('#cargardor').css({'display':'none'});
				$('#ListaStockLinea').html(data);
				location.href = "/sistemaweb/inventarios/reportes/rep_mov_por_dia_excel.php";
			}
		});
	});

	$('#btnprint').click(function() {
		console.log('click en boton print');
		$.post( 'reportes/c_mov_por_dia.php', {accion: 'print'} , function( data ) {
			console.log( 'data: '+data );
			alert(data.data.message);
		}, 'json');
	});


});