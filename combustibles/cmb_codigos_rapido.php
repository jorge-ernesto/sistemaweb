<!DOCTYPE html>
<html lang="en">
  	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Sistema de Combustibles - Edicion de Codigos</title>
		<?php include "../header2.php"; ?>
		<?php include "../footer2.php"; ?>
		<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>
		<script>
		function autocompleteBridge(type) {
			if (type == 0) {
				//new
				var No_Producto = $("#txt-No_Producto");
				if(No_Producto.val() !== undefined) {
					generalAutocomplete('#txt-No_Producto', '#txt-Nu_Id_Producto', 'getProductXByCodeOrName', []);
				}
			} else {
				//buscar

			}
		}
		function selectBuscar() {
			var option = $('#select-buscar').val();
			if(option == 0) {
				$('#text-nombre-producto').addClass('none');
				$('#text-identidicador').removeClass('none');
			} else {
				$('#text-nombre-producto').removeClass('none');
				$('#text-identidicador').addClass('none');
			}
		}
		function confirmarLink(pregunta, accionY, accionN, target) {  
  		if(confirm(pregunta))
    		document.getElementById('control').src = accionY;
    		//alert(actionY);
    		//location.reload();
    		//window.location = '?action=eliminar&identificador=';
		}
		</script>
		<style type="text/css" media="screen">
			.none {
				display: none;
			}
		</style>
	</head>
	<body>
		<?php include "../menu_princ.php"; ?>
		<div id="content">
		    <div id="content_title">&nbsp;</div>
		    <div id="content_body">&nbsp;</div>
		    <div id="content_footer">&nbsp;</div>
		</div>
		<div id="footer">&nbsp;</div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.CODIGOSRAPIDO" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>
