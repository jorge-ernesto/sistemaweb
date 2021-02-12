<?php
include("/acosa/valida_sess.php");
	$items_pre = $checkbox[$fila_seleccionada];
	$_SESSION["ITEMS_PRECANCELACION"] = $items_pre;
	/*for($i=0;$i<count($items_pre);$i++){
		//print "<script>alert('-> ".$clave[$i]."');</script>";
		$dat = $items_pre[$i];
	}*/
	print "<script>alert('-> ".$items_pre."');</script>";
	print "<script>window.open('/acosa/ccobrar/forms_popup/ccob_form_precancelacion.php?items_pre=$items_pre','precan','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');</script>";
	
?>