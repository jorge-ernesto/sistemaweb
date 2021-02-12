
<html>
<head>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript">

		$(document).ready(function(){

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

 			$( "#fecha" ).datepicker({
				changeMonth: true,
				changeYear: true,
				onSelect:function(fecha,obj){

				var fecha = $('#fecha').val();

					$.ajax({
						type: "POST",
						url: "cmb_contometro_ajax.php",
						data:{
							accion: "aprosys",
							dia:fecha
						},
				            	success:function(response){
							$("#resultado").html(response);
				            	}
					});
				}
	        	});


                	$('#buscar').click(function(){

				var fecha = $('#fecha').val();

				if(fecha.length < 1){
					alert('Seleccionar Fecha');
				}else{
					$('#cargardor').css({'display':'block'});
					$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 50 + 'px'});
	
					$.ajax({
						type: "POST",
						url : "cmb_contometro_ajax.php",
						data:{
							accion:"eliminar",
				                	fecha:fecha,
						},
						success:function(xm){

							if(xm == 'Error\n'){
								$('#cargardor').css({'display':'none'});
								alert('No hay datos Dia: ' + fecha);
							} else {
								$('#cargardor').css({'display':'none'});
								alert('Elimando Dia: ' + fecha);
							}

				        	}
					});
				}
			});


		});

	</script>
</head>

<body>
<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>
<div align="center">
<table>
<tr>
	<td>
		Fecha: 
	</td>
	<td>
		<input type="text" name="fecha" id="fecha" maxlength="10" size="10" class="fecha_formato" value="<?php echo date('d/m/Y'); ?>" />
	</td>
	<td align="right">
		<button id="buscar"><img src="/sistemaweb/icons/gdelete.png" align="right" />Eliminar</button>
	</td>
</tr>
<tr>
	<td colspan="3" align="left">
		<span id="resultado"></span>
	</td>
</tr>
</table>
</div>

