<?php

class InterfaceDominioTemplate extends Template {
 
	function titulo(){
		$titulo = '<div align="center"><h2>Interface Dominio</h2></div><hr>';
		return $titulo;
	}

	function ResultadoEjecucion($msg){
		return '<blink>'.$msg.'</blink>';
	}	

  	function formBuscar($almacen, $desde, $hasta)  {
  		$almacenes = InterfaceDominioModel::obtenerAlmacenes("");

    		$form = new form2('', 'form_buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZDOMINIO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZDOMINIO'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellpadding="5"> <tr><td class="form_td_title">'));
    
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('almacen','Almacen</td><td>: ', $almacen, $almacenes, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('desde','Fecha de Inicio</td><td>: ', $desde, '', 12, 10));		
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_buscar.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));		

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('hasta','Fecha de Fin</td><td>: ', $hasta, '', 12, 10));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_buscar.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
									
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Procesar', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
  	}
}
