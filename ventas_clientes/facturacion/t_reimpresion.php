<?php

class ReimpresionTemplate extends Template
{
    function formSearch()
    {
	$tipos = ReimpresionModel::obtenerTiposDocumentos();
	
	$form = new form2('Reimpresion de Documentos', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "FACTURACION.REIMPRESION"));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td valign="center"><table border="0"><tr><td>Tipo de documento:</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('ch_tipodocumento', '', '10', $tipos, ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table></td><td valign="center"><table border="0"><tr><td>Serie del Documento:</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text('ch_seriedocumento', '', '', '', 3, 3));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Numero del documento:</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text('ch_numerodocumento', '', '', '', 8, 7));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table></td></tr><tr><td colspan="2"><p align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Procesar', ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</p></td></tr></table>'));
	return $form->getForm();
    }
}

?>