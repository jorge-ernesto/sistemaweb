<html>
	<head>
		<title>Sistema de Combustibles - Reporte de Sustento de Ventas</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/combustibles/js/sismovpuntosfideliza.js"></script>
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/combustibles/js/validacion.js"></script>

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

					$( "#desde" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})

					$( "#hasta" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})
				});
			}
		</script>
	</head>
	<body>
		<?php include "../menu_princ.php"; ?>
		<div id="content">
			<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
			<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
			<script language="JavaScript" src="js/miguel.js"></script>
			<div id="content_title">&nbsp;</div>
			<div id="content_body">&nbsp;</div>
			<div id="content_footer">&nbsp;</div>
		</div>
		<div id="footer">&nbsp;</div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.SUSTENTOVENTAS&task=SUSTENTOVENTAS" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>
