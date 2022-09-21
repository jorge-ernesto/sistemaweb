<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">a
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Movimientos Ventas</title>
		<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
		<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script language="JavaScript" src="js/registrosmov.js"></script>

	    <link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
		<script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>
		<script charset="utf-8" type="text/javascript">
			function autocompleteBridge(type) {
				if (type == 0) {
					//new
					var No_Producto = $("#txt-No_Producto");
					if(No_Producto.val() !== undefined){
						//console.log(No_Producto.val());
						autocompleteProducto(No_Producto);
					}
				} 
				if (type == 1) {
					//new
					var No_Cliente = $("#txt-No_Proveedor");
					if(No_Cliente.val() !== undefined){
						//console.log(No_Cliente.val());
						autocompleteProveedor(No_Cliente);
					}
				}
			}

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

					$( "#dia1" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})

					$( "#dia2" ).datepicker({
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

				$( "#dia1" ).datepicker({
					changeMonth: true,
					changeYear: true,
				})

				$( "#dia2" ).datepicker({
					changeMonth: true,
					changeYear: true,
				})
			}
		</script>
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
		<div id="footer" align="right">v 1.0&nbsp;</div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.MOVIMIENTOVENTAS" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>
