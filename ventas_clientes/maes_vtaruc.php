<html>
	<head>
		<title>Sistema de Ventas - Maestro RUC</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/ventas_clientes/js/sisventasruc.js"></script>
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/ventas_clientes/js/validacion.js"></script>
	</head>
	<body>
		<?php
		include "../menu_princ.php"; 
		require  "../lib/pzip.php";
		include("../functions.php");
		?>
		<div id="content">
			<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
			<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
			<div id="content_title">&nbsp;</div>
			<div id="content_body">&nbsp;</div>
			<div id="content_footer">&nbsp;</div>
		</div>
		<div id="footer">&nbsp;</div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MAESTROS.RUC&task=RUC" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>
