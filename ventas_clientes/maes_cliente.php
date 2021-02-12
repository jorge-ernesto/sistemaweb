<html>
<head>
<title>Sistema de Ventas - Maestro de clientes</title>
  <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
  <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
  <script language="JavaScript" src="js/sismaestros.js"></script>
  <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
  <script charset="utf-8" type="text/javascript">
	window.onload = function () {
		$(document).ready(function() {
			$( document ).on('click', '#btn_span', function(event) {
				if ( $( '[name="datos[cli_ruc]"]' ).val().length === 11 ) {
					//deshabilitar
					$( "#btn_span" ).prop('disabled', true);
					$( "#MensajeValidacionRuc" ).text( '  Cargando...' );

					var url = 'control.php';
					var params = {
						rqst: 'MAESTROS.CLIENTE',
						task: 'SUNAT',
						action: 'get_data_sunat',
						iTaxID: $( '[name="datos[cli_ruc]"]' ).val(),
					}

					$.post( url, params, function( response ) {
						if ( response.operation != 1 ) {
							alert( response.message );
						} else {
							console.log( response );

							$( '[name="datos[cli_razsocial]"]' ).val( response.name );
							$( '[name="datos[cli_direccion]"]' ).val( (response.streetName !== undefined ? response.streetName + " " : "") + (response.zone !== undefined ? response.zone + " " : "") + response.location );
						}
						$( "#btn_span" ).prop('disabled', false);
						$( "#MensajeValidacionRuc" ).text( '' );
					}, "json");
				} else {
					alert('RUC inválido');
				}
			})
		});
	}
  </script>
  <style type="text/css">
  	.sunat_span {
	    background: url("/sistemaweb/icons/sunat.png");
	    vertical-align: middle !important;
	    margin-left: 5px;
	}

	.btn_span {
	    width: 16px;
	    cursor: pointer;
	    height: 16px;
	    display: inline-block;
	    vertical-align: top;
	}
  </style>
</head>
<body >
<?php include "../menu_princ.php"; ?>
<div id="content">

    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MAESTROS.CLIENTE&task=CLIENTE" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
