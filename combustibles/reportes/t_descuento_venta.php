<?php

class DescuentoVentaTemplate extends Template {

	function formDescuentoVenta() {
	
		$form = new form2('Datos del Ticket que salio como descuento', 'form_descuento_venta', FORM_METHOD_GET, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.DESCUENTOAVENTA"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><table border="0" cellspacing="0" cellpadding="8" bgcolor="#FFFFCD">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('C&oacute;digo Sucursal </td><td>:&nbsp;&nbsp;'. $_SESSION['almacen']));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('sucursal', $_SESSION['almacen']));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha','Fecha  </td><td>: ', date(d."/".m."/".Y) ,'', 10, 12,'','')); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_descuento_venta.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ticket','Nro. Ticket</td><td>: ', '','', 12, 14,''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(' / '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('caja','', '','', 3, 4,''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Cambiar', espacios(10)));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}
}
