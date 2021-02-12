<!DOCTYPE html>
<html lang="en">
	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Orden Compra</title>
		<?php include "../header2.php"; ?>
		<?php include "../footer2.php"; ?>
		<script type="text/javascript" src="/sistemaweb/compras/js/ordencompra.js"></script>
		<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>
		<script>
		function sendUpdate(doc) {
			document.getElementById("radio-"+doc).click();
			document.getElementById("update-"+doc).click();
		}
		</script>
	</head>
	<body>
		<?php include "../menu_princ.php"; ?>
		<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>	
		<div id="content">
			<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
			<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
		    <div id="content_title">&nbsp;</div>
		    <div id="content_body">&nbsp;</div>
		    <div id="content_footer">&nbsp;</div>
		</div>
		<div id="footer">&nbsp;</div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.ORDENCOMPRA" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>