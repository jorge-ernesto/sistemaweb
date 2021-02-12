<?php

class MantenimientoClienteTemplate extends Template {
    
	function search_form() {

		$form = new form2('Precios Especiales', 'form_mantenimiento_cliente', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MAESTROS.MANTENIMIENTOCLIENTE"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("cliente", "Cliente: ", '', espacios(3), 12, 11));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Buscar", espacios(3)));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Agregar", espacios(3)));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
    	}
    
    	function reporte($resultados) {
		$result  = '';
		$result .= '<table border="1" style="border: 1; border-style: simple; border-color: #000000;" align="center">';
		$result .= '<tr>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">COD. CLIENTE</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">&nbsp;&nbsp;NOMBRE CLIENTE&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">COD. ARTICULO</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">&nbsp;&nbsp;NOMBRE ARTICULO&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">&nbsp;&nbsp;FECHA INICIO&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">&nbsp;&nbsp;FECHA FIN&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">&nbsp;&nbsp;PRECIO&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">&nbsp;&nbsp;USUARIO&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">FECHA ACTUALIZACION</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">&nbsp;&nbsp;HABILITADO&nbsp;&nbsp;</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">CARTA DE REF.</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2">TIPO DE CLIENTE</th>';
		$result .= '<th style="color:blue;background-color: #D9F9B2" colspan="2">&nbsp;</th>';
		$result .= '</tr>';

		foreach ($resultados as $x => $a) {	
			
			if ($a['habilitado'] == 'f')
				$habil = "NO";
			else
				$habil = "SI";	

			$result .= '<tr>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['cod_cliente']).'&nbsp;</td>';	
			$result .= '<td align="center">&nbsp;'.htmlentities($a['nom_cliente']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['cod_articulo']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['nom_articulo']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['fec_inicio']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['fec_fin']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['precio']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['usuario']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['actualiza']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.$habil.'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['carta_ref']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['tipo_cli']).'&nbsp;</td>';
			$result .= '<td><A href="control.php?rqst=MAESTROS.MANTENIMIENTOCLIENTE&action=Editar&cod_cliente='.trim($a['cod_cliente']).'&nom_cliente='.trim($a['nom_cliente']).'&cod_articulo='.trim($a['cod_articulo']).'&nom_articulo='.trim($a['nom_articulo']).'&fec_inicio='.trim($a['fec_inicio']).'&fec_fin='.trim($a['fec_fin']).'&precio='.trim($a['precio']).'&habilitado='.trim($a['habilitado']).'&carta_ref='.trim($a['carta_ref']).'&tipo_cli='.trim($a['tipo_cli']).'" target="control")"><img src="/sistemaweb/icons/anular.gif" alt="Editar" align="middle" border="0"/></A></td>';
			$result .= '<td><A href="javascript:confirmarLink(\'Desea eliminar el descuento?\',\'control.php?rqst=MAESTROS.MANTENIMIENTOCLIENTE&action=Eliminar&cod_cliente='.trim($a['cod_cliente']).'&cod_articulo='.trim($a['cod_articulo']).'&fec_ini='.trim($a['fec_inicio']).'&fec_fin='.trim($a['fec_fin']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Eliminar" align="middle" border="0"/></A></td>';      	 	      	 
			$result .= '</tr>';		    	
		}		
		$result .= '</table>';

		return $result;
    	}

	function formAgregar($tipo, $cod_cliente, $nom_cliente, $cod_articulo, $nom_articulo, $fec_inicio, $fec_fin, $precio, $habilitado, $cartaref, $tipocli) {

		$form = new form2('Edicion Precio Especial', 'form_edicion_especial', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.MANTENIMIENTOCLIENTE'));

		if ($tipo == "A") { 
			$desde = date(d."/".m."/".Y);
			$hasta = date(d."/".m."/".Y);
		} else {
			$desde = $fec_inicio;
			$hasta = $fec_fin;
		}

		$tipo_cliente = Array("credito"=>"Credito", "contado"=>"Contado");
		$habilita = Array("v"=>"Si", "f"=>"No");

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="5" bgcolor="#FFFFCD">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Cliente:</td><td>'));

		if ($tipo == "A") { 
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_cliente','', $cod_cliente,'', 13, 13, '', ''));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_cliente','', $nom_cliente,'', 30, 50, '', array('readonly')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'form_edicion_especial.cod_cliente\',\'form_edicion_especial.nom_cliente\',\'clientes\',\'\',\'<?php echo $valor;?>\');"> ')); 
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_cliente','', $cod_cliente,'', 11, 11, '', array('readonly')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_cliente','', $nom_cliente,'', 30, 50, '', array('readonly')));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Cod. Articulo:</td><td>'));

		if ($tipo == "A") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_articulo', '', $cod_articulo, '', 13, 13,'','')); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_articulo', '', $nom_articulo, '', 30, 50,'',array('readonly')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'form_edicion_especial.cod_articulo\',\'form_edicion_especial.nom_articulo\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> ')); 
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('cod_articulo','', $cod_articulo, '', 13, 13,'',array('readonly'))); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nom_articulo','', $nom_articulo, '', 30, 50,'',array('readonly'))); 
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha Inicio:</td><td>'));

		if ($tipo == "A") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fec_inicio','', $desde, '', 10, 12, '', ''));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_edicion_especial.fec_inicio'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fec_inicio','', $desde, '', 10, 12, '', array('readonly')));			
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha Fin:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fec_fin','', $hasta,'', 10, 12,'', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_edicion_especial.fec_fin'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));  

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Precio:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('precio','', $precio,'', 5, 5, '', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		if ($tipo == "A")
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("tipoguardar", "A"));
		else 
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("tipoguardar", "E"));


		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Habilitado:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('habilitado','', $habilitado, $habilita, '&nbsp', '',''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Carta Referencia:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('carta_ref','', $cartaref,'', 30, 30, '', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Tipo cliente:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('tipo_cli','', $tipocli, $tipo_cliente, '&nbsp', '',''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(10)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}
}
