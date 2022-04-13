<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
	<head>
		<title>Sistema de Ventas - Actualizar Formas de Pago</title>
		<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
		<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script language="JavaScript" src="js/combustibles.js"></script>
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script  type="text/javascript">
		window.onload = function() {		
			var fecha = $('#ch_fecha').val();
			var almacen = $('#ch_almacen').val();

			$.ajax({
				type: "POST",
				url: "reportes/c_descuentos_especiales.php",
				data:{
					accion:'ActualizarPagos',
					fecha_inicial:fecha,
					almacen:almacen,
				},
				success:function(xm) {
					if ( xm == 'WARNING_1' ) {
						$('#cargardor').css({'display':'none'});
						$('#opt_final').html("");
						$('#tab_turnos').html("No hay turnos en esta fecha");
						$('#id_cajas').html("");
						$('#tab_cajas').html("No hay cajas en esta fecha");
						$('#id_lados').html("");
						$('#tab_lados').html("No hay lados en esta fecha");
					} else if(xm == 'Error') {
						$('#cargardor').css({'display':'none'});
						$('#opt_final').html("");
						$('#tab_turnos').html("No hay turnos en esta fecha");
						$('#id_cajas').html("");
						$('#id_lados').html("");
					} else {
						var json=eval('('+xm+')');
						$('#cargardor').css({'display':'none'});
						$('#opt_final').html(json.msg);
						$('#id_cajas').html(json.msg2);
						$('#id_lados').html(json.msg3);
						$('#tab_turnos').html("");
						$('#tab_cajas').html("");
						$('#tab_lados').html("");
					}
				}
			});

			$(function() {
				$.datepicker.regional['es'] = {
					closeText: 'Cerrar',
					prevText: '<Ant',
					nextText: 'Sig>',
					currentText: 'Hoy',
					monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
					monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
					dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
					dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
					dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
					weekHeader: 'Sm',
					dateFormat: 'dd/mm/yy',
					firstDay: 1,
					isRTL: false,
					showMonthAfterYear: false,
					yearSuffix: ''
				};

				$.datepicker.setDefaults($.datepicker.regional['es']);

				$( "#ch_fecha" ).datepicker({
					changeMonth: true,
					changeYear: true,
					onSelect:function(fecha,obj) {
						$('#cargardor').css({'display':'block'});
						$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

						var fecha = $('#ch_fecha').val();
						var almacen = $('#ch_almacen').val();

						$.ajax({
							type: "POST",
							url: "reportes/c_descuentos_especiales.php",
							data:{
								accion:'ActualizarPagos',
								fecha_inicial:fecha,
								almacen:almacen,
							},
							success:function(xm) {
								if ( xm == 'WARNING_1' ) {
									$('#cargardor').css({'display':'none'});
									$('#opt_final').html("");
									$('#tab_turnos').html("No hay turnos en esta fecha");
									$('#id_cajas').html("");
									$('#tab_cajas').html("No hay cajas en esta fecha");
									$('#id_lados').html("");
									$('#tab_lados').html("No hay lados en esta fecha");
								} else if ( xm == 'Error' ) {
									$('#cargardor').css({'display':'none'});
									$('#opt_final').html("");
									$('#tab_turnos').html("No hay turnos en esta fecha");
									$('#id_cajas').html("");
									$('#tab_cajas').html("No hay cajas en esta fecha");
									$('#id_lados').html("");
									$('#tab_lados').html("No hay lados en esta fecha");
								} else {
									var json=eval('('+xm+')');
									$('#cargardor').css({'display':'none'});
									$('#opt_final').html(json.msg);
									$('#id_cajas').html(json.msg2);
									$('#id_lados').html(json.msg3);
									$('#tab_turnos').html("");
									$('#tab_cajas').html("");
									$('#tab_lados').html("");
								}
							}
						});
					}
				});
			});
		}
		</script>
	</head>
	<body>
		<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
		<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
		<script src="/sistemaweb/utils/cintillo.js" type="text/javascript" ></script>
		<?php include "../menu_princ.php"; ?>
		<div id="content">
			<div id="content_title">&nbsp;</div>
			<div id="content_body">&nbsp;</div>
			<div id="content_footer">&nbsp;</div>
		</div>
		<div id="footer">&nbsp;</div>
		<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.ACTFORMAPAGO" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>
