<?php
class InterfaceTemplate extends Template {

	function formInterfaceCopetrol($datos,$CbSucursales,$Parametros) {

		$form = new form2('INTERFACE', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZ'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('desde', @$datos['datos']['fechaini']));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('hasta', @$datos['datos']['fechafin']));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('sucursal', @$datos['datos']['sucursal']));

		return $form->getForm();
	}

}

