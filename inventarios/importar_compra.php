<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Importar compra | Inventarios</title>
	   	<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
	   	<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
	   	<script language="JavaScript" src="js/importar_compra.js"></script>
		<!--
	   	<script src="../js/jquery-2.1.4.js" type="text/javascript"></script> 
	   	<script src="../js/jquery-1.11.3.js" type="text/javascript"></script>
	   	<script src="../js/jquery-migrate-1.2.1.min.js"></script>
	   	-->

		<link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
		<script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>

		<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>

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

			function autocompleteBridge(type) {
				console.log('type: '+type);
				if (type == 0) {
					var bpartner = $("#in-order-product-name");
					if(bpartner.val() !== undefined) {
						generalAutocomplete('#in-order-product-name', '#in-order-product-id', 'getProductXByCodeOrName', []);
					}
				}
			}
		</script>
	</head>

	<body>
		<?php include "../menu_princ.php"; ?>
			<div id="header">&nbsp;</div>
			<div id="content">
			<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
			<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
			<div id="content_title">&nbsp;</div>
		    	<div id="content_body">&nbsp;</div>
		    	<div id="content_footer">&nbsp;</div>
		   	</div>
		   	<div id="footer">&nbsp;</div>
		    <iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.IMPORTARCOMPRA" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>

