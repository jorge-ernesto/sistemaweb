<?php

class ImpresionesTemplate extends Template
{
    function formSearch()
    {
	$documentos = ImpresionesModel::obtenerDocumentos();

	$form = new Form('Impresion de Documentos', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
	
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "FACTURACION.IMPRESIONES"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td>Documento:</td><td colspan="3">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tipo_documento', '', '10', $documentos));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>No. Inicial:</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text('desde', '', '', '', 8, 7));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="right">No. Final:</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text('hasta', '', '', '', 8, 7));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Imprimir'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	return $form->getForm();
    }
}

