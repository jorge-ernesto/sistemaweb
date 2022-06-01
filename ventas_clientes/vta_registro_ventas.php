<html>
	<head>
		<title>Sistema de Ventas - Registro de Ventas e Ingresos</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script language="JavaScript" src="js/ventas.js"></script>
		<style type="text/css">
			table#tablaResumen {
				width: 50%;
			}
			table#tablaResumen th {
				height: 30px;
				width: 100%;
				border-width: 1px -32px 1px 0px;
				font-size: 13px;
				font-weight: bold;
				color: #000000;
				background-color: #C9F4D4;
			}
			table#tablaResumen td {
				text-align: right;
				font-size: 11px;
			}
			table#tablaResumen .text-center {
				text-align: center;
			}
			table#tablaResumen .text-right {
				text-align: right;
			}
			
			/* Spinner */
			.spinner {
				border: 4px solid rgba(0, 0, 0, 0.1);
				width: 36px;
				height: 36px;
				border-radius: 50%;
				border-left-color: #09f;

				animation: spin 1s ease infinite;
			}

			@keyframes spin {
				0% {
					transform: rotate(0deg);
				}

				100% {
					transform: rotate(360deg);
				}
			}
		</style>
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
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.REGISTROVENTAS" frameborder="1" width="10" height="10"></iframe>
		<script>
			function preloader(posta){
				if(posta){
					console.log('Spinner true');
					document.getElementById('content_footer').innerHTML = '<div align="center"><div class="spinner"></div></div>';
				}
			}
		</script>
	</body>
</html>
