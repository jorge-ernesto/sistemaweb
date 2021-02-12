<html>
<head>
<title>Sistema de Ventas - Ventas Diarias</title>
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
<link rel="stylesheet" href="/sistemaweb/css/opensoft.css" type="text/css">
<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
<script language="JavaScript" src="js/ventas.js"></script>
<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
<script src="/sistemaweb/js/jquery-ui.js"></script>
<script  type="text/javascript">
window.onload = function () {
	$(function(){
		$.datepicker.regional['es'] = {
			    closeText: 'Cerrar',
			    prevText: '<Ant',
			    nextText: 'Sig>',
			    currentText: 'Hoy',
			    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
			    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
			    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
			    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
			    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
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
		});

			$( "#hasta" ).datepicker({
			changeMonth: true,
			changeYear: true,
		});
	});
}
</script>
</head>
<body>
<?php include "../menu_princ.php"; ?>
<div id="content">
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.VENTASDIARIAS" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
