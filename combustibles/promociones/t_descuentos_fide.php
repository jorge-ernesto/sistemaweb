<?php

class DescuentosFideTemplate extends Template {
    
	function search_form() {

		$form = new form2('Registro de Descuentos de Fidelizacion', 'form_descuentos_fide', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "PROMOCIONES.DESCUENTOSFIDE"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("ruc", "RUC: ", '', espacios(3), 12, 11));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Buscar", espacios(3)));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Agregar", espacios(3)));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
    	}
    
    	function reporte($resultados) {
		$result  = '';
		$result .= '<table border="1" style="border: 1; border-style: simple; border-color: #000000;" align="center">';
		$result .= '<tr>';
		$result .= '<th style="color:blue;">&nbsp;&nbsp;RUC&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;">&nbsp;&nbsp;CODIGO DE ARTICULO&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;">&nbsp;&nbsp;NOMBRE DE ARTICULO&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;">&nbsp;DSCTO. X UNIDAD VENDIDA&nbsp;</th>';
		$result .= '<th style="color:blue;">&nbsp;&nbsp;FECHA INICIO&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;">&nbsp;&nbsp;FECHA FIN&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;" colspan="2">&nbsp;</th>';
		$result .= '</tr>';

		foreach ($resultados as $x => $a) {		
			$result .= '<tr>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['ruc']).'&nbsp;</td>';	
			$result .= '<td align="center">&nbsp;'.htmlentities($a['cod_articulo']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['nom_articulo']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['importe']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['inicio']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['fin']).'&nbsp;</td>';
			$result .= '<td><A href="control.php?rqst=PROMOCIONES.DESCUENTOSFIDE&action=Editar&id='.trim($a['id_descuento']).'&ruc='.trim($a['ruc']).'&cod_articulo='.trim($a['cod_articulo']).'&nom_articulo='.trim($a['nom_articulo']).'&descuento='.trim($a['importe']).'&inicio='.trim($a['inicio']).'&fin='.trim($a['fin']).'" target="control")"><img src="/sistemaweb/icons/anular.gif" alt="Borrar" align="middle" border="0"/></A></td>';
			$result .= '<td><A href="javascript:confirmarLink(\'Desea eliminar el descuento?\',\'control.php?rqst=PROMOCIONES.DESCUENTOSFIDE&action=Eliminar&id='.trim($a['id_descuento']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';      	 	      	 
			$result .= '</tr>';		    	
		}		
		$result .= '</table>';

		return $result;
    	}

	function formAgregar($tipo, $id, $ruc, $cod_articulo, $nom_articulo, $descuento, $inicio, $fin) {

		$form = new form2('Agregar nuevo descuento de fidelizaci&oacute;n', 'form_descuentos_fide', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.DESCUENTOSFIDE'));

		if ($tipo == "A") { 
			$desde = date(d."/".m."/".Y);
			$hasta = date(d."/".m."/".Y);
		} else {
			$desde = $inicio;
			$hasta = $fin;
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="5" bgcolor="#FFFFCD">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>RUC:</td><td>'));

		if ($tipo == "A") 
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc','', $ruc,'', 11, 11, '', ''));
		else
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc','', $ruc,'', 11, 11, '', array('readonly')));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Cod. Articulo:</td><td>'));

		if ($tipo == "A") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_articulo', '', $cod_articulo, '', 13, 13,'','')); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_articulo', '', $nom_articulo, '', 30, 50,'',array('readonly')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'form_descuentos_fide.cod_articulo\',\'form_descuentos_fide.nom_articulo\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> ')); 
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_articulo','', $cod_articulo, '', 13, 13,'',array('readonly'))); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_articulo','', $nom_articulo, '', 30, 50,'',array('readonly'))); 
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Dscto. x Unidad Vendida:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('descuento','', $descuento,'', 5, 5, '', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha Inicio:</td><td>'));

		if ($tipo == "A") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('inicio','', $desde, '', 10, 12, '', ''));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_descuentos_fide.inicio'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('inicio','', $desde, '', 10, 12, '', array('readonly')));			
		}

		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('id', $id));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha Fin:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fin','', $hasta,'', 10, 12,'', $ronly));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_descuentos_fide.fin'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));  

		if ($tipo == "A")
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("tipoguardar", "A"));
		else 
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("tipoguardar", "E"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(10)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}
}
