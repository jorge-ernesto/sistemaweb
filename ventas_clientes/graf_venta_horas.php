<html>
<head>
<title>Sistema de Ventas - Grafico de Ventas Diarias</title>
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	<style type="text/css">
		table {
			font: 11px Verdana, Arial, Helvetica, sans-serif;
			color: #777;
			padding:3px;
		}
	</style>
<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
<script language="JavaScript" src="js/ventas.js"></script>
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
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.GRAFICOVENTASHORAS" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
