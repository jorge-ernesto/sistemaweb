<!DOCTYPE html>
<html lang="en">
	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Lote Vencimiento - Inventarios</title>
		<?php include "../header2.php"; ?>
		<?php include "../footer2.php"; ?>
		<script type="text/javascript" src="/sistemaweb/inventarios/js/lote_vencimiento.js"></script>
	</head>
	<body>
		<?php include "../menu_princ.php";

			$today = date("Y-m-d 00:00:00");

			$sql = "UPDATE
					inv_pedido_vencimiento
					SET
					nu_estado = '3',
					fe_actualizacion = now(),
					no_usuario='AUTO'
					WHERE
					fe_vencimiento < '".$today."'" ;

			$sqlca->query($sql);

			/*$sql1 = "UPDATE
					inv_pedido_vencimiento
					SET
					nu_estado = '1',
					fe_actualizacion = now(),
					no_usuario='AUTO'
					WHERE
					fe_vencimiento > '".$today."' OR fe_vencimiento = '".$today."'";
					
			$sqlca->query($sql1);*/
		?>
		<div id="content">
		    <div id="content_title">&nbsp;</div>
		    <div id="content_body">&nbsp;</div>
		    <div id="content_footer">&nbsp;</div>
		</div>
		<div id="footer">&nbsp;</div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.VENCIMIENTOLOTES" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>