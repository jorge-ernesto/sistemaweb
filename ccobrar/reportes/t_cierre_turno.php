<?php
class DiaCierreTurnoTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Estado de Cuenta Cierres</b></h2></div>';
	}

	function formSearch($fecha,$fecha2){

		$hoy_timestamp  = time();
		$ayer_timestamp = $hoy_timestamp - (24*60*60);
		$ayer = date("d/m/Y", $ayer_timestamp);

		$mes  = date("m");
		$dia  = "01";
		$year = date("Y");
	
		$inicio_mes = mktime(0, 0, 0, $mes, $dia, $year);
		$fin_mes    = mktime(0, 0, 0, $mes+1, 0, $year);
		
		if ($fecha == "" or $fecha2 == "") {
			$fecha      = date("d/m/Y", $inicio_mes);
			$fecha2     = date("d/m/Y", $fin_mes);
		} 

		$form = new form('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.DIACIERRE'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'DIACIERRE'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td style="text-align:center;">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Fecha Inicio:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", $fecha, '<a href="javascript:show_calendar(\'Agregar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Fecha Final:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", $fecha2, '<a href="javascript:show_calendar(\'Agregar.fecha2\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('</tr></td><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Buscar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('</td><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Agregar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));
		return $form->getForm();
	}

}
