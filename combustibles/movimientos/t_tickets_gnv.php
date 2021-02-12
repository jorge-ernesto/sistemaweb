<?php
class TicketsGNVTemplate extends Template {
	function titulo() {
		return '<h2 align="center"><b>Importación De Tickets GNV - Ventas Diarias Detalladas</b></h2>';
	}

	function FormCargar() {
		$arrVersion = array('V1' => 'Version 1','V2' => 'Version 2', 'V3' => 'Version 3' );
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.TICKETSGNV"));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table><div id="cargando" style="display:none; color: green;"><img id="cargador" src="/sistemaweb/images/cg.gif"/></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Seleccionar Version de Documento: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('version','','', $arrVersion));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbspSeleccionar archivo: <input type="file" name="ubica" id="ubica" size="70" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Cargar Datos',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="destino" align="center"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
	}
}
