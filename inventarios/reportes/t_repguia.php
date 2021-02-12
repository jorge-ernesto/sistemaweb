<?php

class RepGuiaTemplate extends Template
{
    function Titulo()
    {
	return '<h2 align="center" style="color:#336699"><b>Movimientos por Guia de Remisi&oacute;n</b></h2>';
    }
    
    function formSearch()
    {
	$tipos = RepGuiaModel::obtenerTiposDocumentos();

	$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.REPGUIA"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));
	$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Tipo de documento:", "mov_tipdocuref", '09', '<br>', '', 1, $tipos, false, ''));
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("N&uacute;mero de documento:", "mov_docurefe", '', '<br>', '', 12, 12, ""));
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", "", "", ""));
	return $form->getForm();
    }
}
