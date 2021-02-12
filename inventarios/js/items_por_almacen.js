$( document ).ready(function() {  

	$('#btnbuscar').click(function() {
		var nualmacen 	= $('#nualmacen').val();
		var nucodlinea	= $('#combolinea').val();
		var nuyear	= $('#comboyear').val();
		var numonth	= $('#combomonth').val();
		var p_stock = $('[name="p_stock"]:checked').attr('value');
		var c_stock = $('[name="c_stock"]:checked').attr('value');
		var n_stock	= $('[name="n_stock"]:checked').attr('value');
		//var a_stock 	= $('#a_stock').val();
		var utilidad 	= $('[name="utilidad"]:checked').attr('value');
		var simple 	= $('[name="simple"]:checked').attr('value');
		var fecha_inicio = $('#fecha_inicio').val();

	
		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_items_por_almacen.php",
			data	: {
				accion		: 'search',
				nualmacen	: nualmacen,
				nucodlinea	: nucodlinea,
				nuyear		: nuyear,
				numonth		: numonth,
				p_stock 	: p_stock,
				c_stock 	: c_stock,
				n_stock 	: n_stock,
				utilidad	: utilidad,
				simple		: simple,
				fecha_inicio: fecha_inicio,
			},
			success:function(data) {
				$('#cargardor').css({'display':'none'});
				$('#ListaStockLinea').html(data);
			}
		});
	});

	$('#btnexcel').click(function() {
		var nualmacen 	= $('#nualmacen').val();
		var nucodlinea	= $('#combolinea').val();
		var fecha_inicio	= $('#fecha_inicio').val();
		var nuyear	= $('#comboyear').val();
		var numonth	= $('#combomonth').val();
		//var notipo 	= $('[name="notipo"]:checked').attr('value');
		var notipo 	= $('#stock').val();
		var p_stock = $('[name="p_stock"]:checked').attr('value');
		var c_stock = $('[name="c_stock"]:checked').attr('value');
		var n_stock	= $('[name="n_stock"]:checked').attr('value');
		var utilidad = $('[name="utilidad"]:checked').attr('value');
		var simple = $('[name="simple"]:checked').attr('value');
		var fecha_inicio = $('#fecha_inicio').val();

		$('#cargardor').css({'display':'block'});
		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		$.ajax({
			type	: "POST",
			url		: "reportes/c_items_por_almacen.php",
			data	: {
					accion		: 'exportExcel',
					nualmacen	: nualmacen,
					nucodlinea	: nucodlinea,
					nuyear		: nuyear,
					numonth		: numonth,
					fecha_inicio: fecha_inicio,
					p_stock 	: p_stock,
					c_stock 	: c_stock,
					n_stock 	: n_stock,
					utilidad	: utilidad,
					simple		: simple,
					fecha_inicio: fecha_inicio,
			},
			success:function(data) {
				$('#cargardor').css({'display':'none'});
				$('#ListaStockLinea').html(data);
				location.href = "/sistemaweb/inventarios/reportes/rep_items_por_almacen_excel.php";
			}
		});
	});

	$('#btnprint').click(function() {
		console.log('click en boton print');
		$.post( 'reportes/c_items_por_almacen.php', {accion: 'print'} , function( data ) {
			console.log( 'data: '+data );
			alert(data.data.message);
		}, 'json');
	});


});