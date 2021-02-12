<html>
	<head>
	<title>Interface Sigo</title>
  	<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
  	<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
  	<script language="JavaScript" src="js/combustibles.js"></script>
  	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
    <script src="/sistemaweb/js/jquery-ui.js"></script>
	<script>

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
				    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
				    weekHeader: 'Sm',
				    dateFormat: 'dd/mm/yy',
				    firstDay: 1,
				    isRTL: false,
				    showMonthAfterYear: false,
				    yearSuffix: ''
				};

			    $.datepicker.setDefaults($.datepicker.regional['es']); 

	 			$('input[name="datos[fechaini]"]').datepicker({
					changeMonth: true,
					changeYear: true,
				});

				$('input[name="datos[fechafin]"]').datepicker({
					changeMonth: true,
					changeYear: true,
				});
			});
		}
	</script>
</head>

<body leftmargin="0" topmargin="0">
<?php include "../menu_princ.php"; ?>
<div id="content">
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.INTERFAZSIGO&task=INTERFAZSIGO" frameborder="1" width="5" height="5"></iframe>
</body>
</html>
