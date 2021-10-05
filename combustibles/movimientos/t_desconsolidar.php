<?php

class DesconsolidarTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2 align="center"><b>Deshacer Consolidacion de Turno</b></h2></div>';
	}

	function formDesconsolidar($siguiente, $almacenes, $almacen) {

		$almacenes['']	= "Seleccionar Almacen..";

		$form = new form2('', 'form_desconsolidar', FORM_METHOD_GET, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.DESCONSOLIDAR"));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("almacen", $siguiente['almacen']));		
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("fecha", $siguiente['dia']));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("turno", $siguiente['turno']));


		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="3" >'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td><strong style="font-size:1.2em; color:#126775;">Seleccionar Almacen: </strong>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td align=left>'));
	        $form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $almacen, $almacenes, "", "", array('onChange="BuscarDataAlmacenDes(this.value);"'),""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right"><strong style="font-size:1.2em; color:#126775;">Dia: </strong></td><td>'.$siguiente['diab'].'</td></tr>')); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right"><strong style="font-size:1.2em; color:#126775;">Turno: </strong></td><td>'.$siguiente['turno'].'</td></tr>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<button type="submit" name="action" value="Desconsolidar" style="color:#126775; font-size:12px;" onClick="return confirm(\'Esta seguro que desea desconsolidar el turno '.$siguiente['turno'].' del dia '.$siguiente['diab'].' \');"><img src="/sistemaweb/icons/can3.png" align="right">Desconsolidar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		//FORMULARIO PARA DESCONSOLIDAR POR DIA Y TUNO
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<br><br><br><input name type="button" value = "Deshacer consolidación por día y turno" onClick="MostrarTabla(\'tabla1\'); obtenerDatePicker(\''.date("Y-m-d").'\');" style="color:#126775; font-size:12px;" />'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

		$form2 = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form2->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.DESCONSOLIDAR"));

		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table id='tabla1' style='display: none'>"));

		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td style='width:50%;text-align:right;'>"));
		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("Almacén:</td><td style='text-align:left;'>"));
		$form2->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen_", "", $almacen, $almacenes, "", "", array(''),""));
		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td style='width:50%;text-align:right;'>"));
		// $form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("Del:</td><td style='text-align:left;'>"));
		// $form2->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", date("d/m/Y"), '<td><a href="javascript:show_calendar(\'Agregar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, false));
		// $form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("Al:</td><td style='text-align:left;'>"));
		// $form2->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", date("d/m/Y"), '<td><a href="javascript:show_calendar(\'Agregar.fecha2\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td colspan="3" style="text-align:center;">', '', 10, 10, false));
		//Fecha
		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext('<input type="text" name="fecha_" id="fecha_" class="fecha_formato" onchange="obtenerTurno()"></td></tr><tr><td style="text-align:right;">'));
		//Turno
		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("Turno:</td><td style='text-align:left;'>"));
		$form2->addElement(FORM_GROUP_MAIN, new f2element_combo("turno_", "", "-", array("-" => "Seleccionar.."), "", "", array(''),""));
		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));

		$form2->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Desconsolidar dia y turno", '', '', 20));
		$form2->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));
		//CERRAR FORMULARIO PARA DESCONSOLIDAR POR DIA Y TUNO
		
		return $form->getForm() . $form2->getForm();
	}
}
