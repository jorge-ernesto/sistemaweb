<?php

class ImpresionCierresTemplate extends Template {
    
	function search_form($dia) {
		$opciones = array('t'=>'Turno','d'=>'Dia');
		$nomalma = ImpresionCierresModel::obtenerEstacion($_SESSION['almacen']);

		$form = new form2('Impresion de Cierres', 'form_impresion_cierres', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.IMPRESIONCIERRES"));			

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><table border="0" cellspacing="0" cellpadding="7" bgcolor="#CEECF5">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Sucursal </td><td>:&nbsp;&nbsp;'. $_SESSION['almacen']."&nbsp;&nbsp".$nomalma));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('sucursal', $_SESSION['almacen']));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('dia','Dia  </td><td>: ', date(d."/".m."/".Y) ,'', 10, 12,'','')); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_impresion_cierres.dia'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Cierre</td><td>:&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('opcion', '', '', $opciones, '',''));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Mostrar', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
    	}
    
    	function reporte($resultados) {

		$result  = '';
		$result .= '<table border="1" cellpadding="3" style="border: 1; border-style: simple; border-color: #000000;" align="center">';

		for($i = 0; $i < count($resultados)-1; $i++) {					
			$result .= '<tr>';
			$result .= '<td align="center">&nbsp;'.htmlentities($resultados[$i]['nom']).'&nbsp;</td>';	
			$result .= '<td><A href="javascript:confirmarLink(\'Desea imprimir '.htmlentities(trim($resultados[$i]['nom'])).'?\',\'control.php?rqst=MOVIMIENTOS.IMPRESIONCIERRES&action=Imprimir&file='.trim($resultados[$i]['link']).'\', \'control\')">Imprimir Cierre</A></td>';      	 	      	 
			$result .= '</tr>';		    	
		}		
		$result .= '</table>';

		return $result;
    	}

}
