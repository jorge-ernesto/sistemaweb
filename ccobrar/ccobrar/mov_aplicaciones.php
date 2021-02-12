<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Sistema de Cuentas x Cobrar - Aplicaciones</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/ccobrar/js/modu_movimientos.js"></script>

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
				});
			}
		</script>
	</head>
<body>
<?php include "../menu_princ.php"; ?>
<div id="content">
  <script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
  <script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.APLICACIONES&task=APLICACIONES&action=Interfaz" frameborder="1" width="5" height="5"></iframe>
</body>
</html>