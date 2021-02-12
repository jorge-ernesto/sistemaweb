<?php

class TipoCambioTemplate extends Template {
    
	function search_form($f_desde,$f_hasta) {

		if($f_desde == '' || $f_hasta == ''){
			$f_desde = date(d."/".m."/".Y); 
			$f_hasta = date(d."/".m."/".Y);
		}

		$tipocambios = Array(	"01"=>"Compra Libre", 
					"02"=>"Venta Libre",
					"03"=>"Compra Banco",
					"04"=>"Venta Banco",
					"05"=>"Compra Oficial",
					"06"=>"Venta Oficial"); 

		$form = new form2('Actualizar Tipos de Cambio', 'form_tipo_cambio', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.TIPOCAMBIO"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $f_desde, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_tipo_cambio.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $f_hasta, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_tipo_cambio.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>&nbsp</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('tipocambio','Tipo de Cambio: ', '', $tipocambios, '&nbsp', '',''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>&nbsp</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Migrar", espacios(5)));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
    	}    
}
