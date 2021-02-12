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
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Seleccionar Almacen: '));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td align=left>'));
	        $form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $almacen, $almacenes, "", "", array('onChange="BuscarDataAlmacenDes(this.value);"'),""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Dia :</td><td>'.$siguiente['diab'].'</td></tr>')); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Turno :</td><td>'.$siguiente['turno'].'</td></tr>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<button type="submit" name="action" value="Desconsolidar" onClick="return confirm(\'Esta seguro que desea desconsolidar el turno '.$siguiente['turno'].' del dia '.$siguiente['diab'].' \');"><img src="/sistemaweb/icons/can3.png" align="right">Desconsolidar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}
}
