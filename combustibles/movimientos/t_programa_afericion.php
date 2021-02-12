<?php
class ProgramaAfericionTemplate extends Template {	function titulo() {		return '<h2><b>Programar Afericion</b></h2>';	}	function formProgramar($lados,$modos) {		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.PROGRAMA_AFERICION"));		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Lado:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("","lado","","</td></tr><tr><td style=\"text-align:right;\">","","",$lados,FALSE));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Modo:</td><td style='text-align:left;'>"));		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("","modo","","</td></tr><tr><td style=\"text-align:right;\">","","",$modos,FALSE));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Lineas:</td><td style='text-align:left;'>"));		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "lineas", "", '</td></tr><tr><td style="text-align:center;" colspan="2">', '', 10, 10, false));		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Programar", '', '', 20));		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));		return $form->getForm();	}

	function muestraResultado($resultado) {
		if ($resultado===FALSE)
			return "<script>alert('No se pudo programar la afericion. Intenta Nuevamente.')</script>";
		else
			return "<script>alert('Se programo la afericion correctamente. Puede colgar la manguera.')</script>";	}
}
