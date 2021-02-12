<?php

class ImportarPreciosTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Importacion de Lista de Precios<br><br>de Proveedor</b></h2></div>';
	}
    
	function search_form() {
		$form = new form2('', 'form_importar_precios', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.IMPORTARPRECIOS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Archivo: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="file" name="ubicacion" id="ubicacion" size="60">&nbsp;&nbsp;&nbsp;'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Mostrar", espacios(3)));

		return $form->getForm();
    	}
    
    	function reporte($res) {
    		$act = $res['actualiza'];
    		$ins = $res['ingresa'];
    		$inv = $res['invalido'];
	
	    	$form = new form2('', 'form_lista', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.IMPORTARPRECIOS"));
		
		// Datos para actualizar
		if(count($act)>0) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><h3>Datos para Actualizar</h3>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="1" align="center" cellpadding="2"><tr bgcolor="#D9F9B2">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2">&nbsp;&nbsp;Proveedor&nbsp;&nbsp;</th><th colspan="2">&nbsp;&nbsp;Articulo&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th>&nbsp;&nbsp;Tipo de Moneda&nbsp;&nbsp;</th><th>&nbsp;&nbsp;Precio Unit.&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				
			for($i=0; $i<count($act); $i++) {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center">'));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("acod_pro[".$i."]", "", $act[$i]['cod_pro'], '</td><td>', 20, 20, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("anom_pro[".$i."]", "", $act[$i]['nom_pro'], '</td><td>', 40, 40, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("acod_art[".$i."]", "", $act[$i]['cod_art'], '</td><td>', 20, 20, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("anom_art[".$i."]", "", $act[$i]['nom_art'], '</td><td>', 60, 100, '',array('readonly')));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("amoneda[".$i."]", "", $act[$i]['moneda'], '</td><td>', 20, 20, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("aprecio[".$i."]", "", $act[$i]['precio'], '</td>', 20, 20, '',array('readonly')));						
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));		    	
			}		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><div align="center"><button type="submit" name="action" value="Actualizar">Actualizar&nbsp;&nbsp;<img src="/sistemaweb/icons/actualizar2.gif" alt="actualizar"/></button></div>'));
		}
		
		// Datos para insertar
		if(count($ins)>0) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><h3>Datos para Insertar</h3>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="1" align="center" cellpadding="2"><tr bgcolor="#D9F9B2">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2">&nbsp;&nbsp;Proveedor&nbsp;&nbsp;</th><th colspan="2">&nbsp;&nbsp;Articulo&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th>&nbsp;&nbsp;Tipo de Moneda&nbsp;&nbsp;</th><th>&nbsp;&nbsp;Precio Unit.&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				
			for($i=0; $i<count($ins); $i++) {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center">'));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("icod_pro[".$i."]", "", $ins[$i]['cod_pro'], '</td><td>', 20, 20, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("inom_pro[".$i."]", "", $ins[$i]['nom_pro'], '</td><td>', 40, 40, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("icod_art[".$i."]", "", $ins[$i]['cod_art'], '</td><td>', 20, 20, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("inom_art[".$i."]", "", $ins[$i]['nom_art'], '</td><td>', 60, 100, '',array('readonly')));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("imoneda[".$i."]", "", $ins[$i]['moneda'], '</td><td>', 20, 20, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("iprecio[".$i."]", "", $ins[$i]['precio'], '</td>', 20, 20, '',array('readonly')));						
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));		    	
			}		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><div align="center"><button type="submit" name="action" value="Insertar">Insertar&nbsp;&nbsp;<img src="/sistemaweb/icons/agregar.gif" alt="agregar"/></button></div>'));
		}
		
		// Datos invalidos
		if(count($inv)>0) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><h3>Datos invalidos</h3>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="1" align="center" cellpadding="2"><tr bgcolor="#D9F9B2">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2">&nbsp;&nbsp;Proveedor&nbsp;&nbsp;</th><th colspan="2">&nbsp;&nbsp;Articulo&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th>&nbsp;&nbsp;Tipo de Moneda&nbsp;&nbsp;</th><th>&nbsp;&nbsp;Precio Unit.&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));		
		
			for($i=0; $i<count($inv); $i++) {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				
				if($inv[$i]['nom_pro']=="---")
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100">'.$inv[$i]['cod_pro'].'&nbsp;&nbsp;<img src="/sistemaweb/icons/bad.gif" alt="bad"/></td>'));
				else
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100">'.$inv[$i]['cod_pro'].'&nbsp;&nbsp;<img src="/sistemaweb/icons/ok.gif" alt="ok"/></td>'));
					
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:220">'.$inv[$i]['nom_pro'].'</td>'));

				if(substr($inv[$i]['cod_art'],0,3)=="---")
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100">'.substr($inv[$i]['cod_art'],3).'&nbsp;&nbsp;<img src="/sistemaweb/icons/bad.gif" alt="bad"/></td>'));
				else 
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100">'.$inv[$i]['cod_art'].'&nbsp;&nbsp;<img src="/sistemaweb/icons/ok.gif" alt="ok"/></td>'));
					
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:320">'.$inv[$i]['nom_art'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100">'.$inv[$i]['moneda'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100">'.$inv[$i]['precio'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));		    	
			}		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		}

		return $form->getForm();
    	}
}
