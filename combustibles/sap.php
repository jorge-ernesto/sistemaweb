<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>SAP Business One</title>
	<?php include "../header2.php"; ?>
	<?php include "../footer2.php"; ?>
	<script charset="utf-8" type="text/javascript">
		$(document).ready(function() {
			$(document).on('click', '.tablinks', function(event) {
				var _this = $(this);
				$('.tablinks').removeClass('active');
				$('.tabcontent').addClass('none');
				$( '#' + _this.attr('data-id') ).removeClass( 'none' );
				$(_this).addClass('active');

				if (_this.val()=='0') {//Exportar
					$( '#div-header' ).show();
					$( '#div-detail' ).show();
					$( '#div-detail-agrupados' ).show();
					$( '#div-resumen-productos' ).show();
					$( '#div-resumen-vales' ).show();
					$( '#div-configuration' ).hide();
				}

				if (_this.val()=='1') {//Configuración
					$( '#div-header' ).hide();
					$( '#div-detail' ).hide();
					$( '#div-detail-agrupados' ).hide();
					$( '#div-resumen-productos' ).hide();
					$( '#div-resumen-vales' ).hide();
					$( '#div-configuration' ).show();
				}
			});
		});

		function confirmarLink(pregunta, accionY, accionN, target) {
			if(confirm(pregunta))
				document.getElementById('control').src = accionY;
			else
				document.getElementById('control').src = accionN;
		}
	</script>
</head>
<body>
	<?php include "../menu_princ.php"; ?>
	<div id="content">
		<div id="content_title">&nbsp;</div>
		<div id="content_body">&nbsp;</div>
		<div id="content_footer">&nbsp;</div>
	</div>
	<div id="footer">&nbsp;</div>
	<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.INTERFAZSAP" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
