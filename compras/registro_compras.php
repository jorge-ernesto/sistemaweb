<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Sistema de Compras - Registro de Compras</title>
		<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
		<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script language="JavaScript1.2" src="/sistemaweb/compras/js/compras.js?ver=1.0"></script>

		<link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
		<script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
		<script charset="utf-8" type="text/javascript">
			window.onload = function() {
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

					$( "#fecha" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})

					$( "#fecha2" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})
				});
			}

			function getFechaEmision(){
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

				$( "#fecha" ).datepicker({
					changeMonth: true,
					changeYear: true,
				})

				$( "#fecha2" ).datepicker({
					changeMonth: true,
					changeYear: true,
				})
			}
		</script>

		<!--
		<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script  type="text/javascript">
		$(window).load(function() {
			$(function () {
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

				$("#fecha").datepicker({
					changeMonth: true,
					changeYear: true,
				});

				$("#fecha2").datepicker({
					changeMonth: true,
					changeYear: true,
				});
			});
		});
		</script>
		-->
	</head>
	<body>
	<?php include "../menu_princ.php"; ?>
		<div id="content">
			<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
			<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
			<div id="content_title">&nbsp;</div>
			<div id="content_body">&nbsp;</div>
			<div id="content_footer">&nbsp;</div>
		</div>
		<div id="footer">&nbsp;</div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS" frameborder="1" width="10" height="10"></iframe>
		<iframe id="excel" name="excel" scrolling="no" src="excel.php" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>
