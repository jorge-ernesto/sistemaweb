<?php

class TiposCuentaTemplate extends Template {

	function titulo(){
		$titulo = '<div align="center"><h2>Tipos de Cuenta</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}
	

	function listado($registros) {
		print 'entrando a model canje';
		$contador = 0;
		$titulo_grid = "LISTADO";
		$columnas = array('CODIGO','DESCRIPCION');
		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
				<table>
				<caption class="grid_title">'.$titulo_grid.'</caption>
				<thead align="center" valign="center" >
				<tr class="grid_header">';

		for($i = 0; $i < count($columnas); $i++) {
			$listado .= '<td class="grid_cabecera" height="20"> '.strtoupper($columnas[$i]).'&nbsp;</td>';
		}
		$listado .= '</tr><tbody class="grid_body" style="height:250px;">';

		foreach($registros as $reg) {
			$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");	
			$listado .= '<td class="'.$color.'">'.$reg[0].'</td>';	
			$listado .= '<td class="'.$color.'">'.$reg[1].'</td>';			
			$listado .= '<td class="'.$color.'"> <a href="control.php?rqst=PROMOCIONES.TIPOSCUENTA&task=TIPOSCUENTA&action=Modificar&idtipocuenta='.$reg[0].'&descripcion='.$reg[1].'" target="control"><img alt="Editar Tipo de Cuenta" title="Editar Tipo de Cuenta" src="/sistemaweb/icons/kedit32x32.png" align="middle" border="0"/></a>&nbsp;</td>';
			$listado .= '<td class="'.$color.'" ><a href="javascript:confirmarLink(\'Desea eliminar el registro de codigo='.$reg[0].'?\',\'control.php?rqst=PROMOCIONES.TIPOSCUENTA&task=TIPOSCUENTA&action=Eliminar&idtipocuenta='.$reg[0].'\',\'control\')"><img alt="Eliminar Tipo de Cuenta" title="Eliminar Tipo de Cuenta" src="/sistemaweb/icons/delete16x16.png" align="middle" 	border="0"/></a>&nbsp;</td>';	
			$listado .= '</tr>';
			$contador++;
		}
		$listado .= '</tbody></table></div>';

		return $listado;
	}

	function formBuscar($paginacion) {
		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.TIPOSCUENTA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TIPOSCUENTA'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Nuevo',espacios(3)));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
		return $form->getForm();
	}

	function formTiposcuenta($tiposcuenta) {
		
		$form = new form2('', 'form_Tiposcuenta', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_Tiposcuenta();"');		
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.TIPOSCUENTA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TIPOSCUENTA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idtipocuenta', @$tiposcuenta["idtipocuenta"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" class="form_cabecera">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($_REQUEST['titulo'].'</td></tr>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		if($_REQUEST['action'] == 'Modificar') {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizartiposcuenta'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('descripcion','DESCRIPCION *  </td><td colspan="2" class="form_texto">: ', trim(@$tiposcuenta["descripcion"]),'', 50, 13,'',array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		} else {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('descripcion','DESCRIPCION *  </td><td colspan="2" class="form_texto">: ', trim(@$tiposcuenta["descripcion"]),'', 50, 13,'',array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3"><HR></td></tr>'));				
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	
		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}
}
