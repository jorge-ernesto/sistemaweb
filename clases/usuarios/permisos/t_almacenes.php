<?php

class AlmacenesTemplate extends Template {

	function titulo() {
		return '<h2><b>Acceso a Almacenes</b></h2>';
	}
    
    	function listado() {
		$accesos   = AlmacenesModel::obtenerListado();
		$grupos    = AlmacenesModel::obtenerGrupos();
		$usuarios  = AlmacenesModel::obtenerUsuarios();
		$almacenes = AlmacenesModel::obtenerAlmacenes();
	
		$result = '';
	
		$result .= '<form name="accesos" method="post" target="control" action="control.php">';
		$result .= '<input type="hidden" name="rqst" value="PERMISOS.ALMACENES">';
		$result .= '<table border="1">';
		$result .= '<tr>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>Nombre</td>';
		$result .= '<td>Sistema</td>';
		$result .= '</tr>';
	
		foreach($accesos as $i => $acceso) {
		    	$result .= '<tr>';
		    	$result .= '<td><input type="checkbox" name="keys[]" value="' . htmlentities($acceso['gid'].$acceso['uid'].$acceso['ch_almacen']) . '"></td>';

		    	if ($acceso['gid'] > -1) {
				$result .= '<td>Grupo: ' . htmlentities($grupos[$acceso['gid']]) . '</td>';
		    	} else {
				$result .= '<td>Usuario:' . htmlentities($usuarios[$acceso['uid']]) . '</td>';
		    	}
		    
		    	$result .= '<td>' . htmlentities($acceso['ch_almacen'] . "-" . $almacenes[$acceso['ch_almacen']]) . '</td>';
		    	$result .= '</tr>';
		}

		$result .= '</table>';
		$result .= '<input type="submit" name="action" value="Borrar">';
		$result .= '<input type="button" name="btAgregar" value="Agregar" onclick="formAlmacenAgregar()">';
		$result .= '</form>';
	
		return $result;
    	}
    
    	function agregar() {

		$opts = Array("usuario"=>"Usuario", "grupo"=>"Grupo");
		$usuarios = AlmacenesModel::obtenerUsuarios();
		$grupos = AlmacenesModel::obtenerGrupos();
		$almacenes = AlmacenesModel::obtenerAlmacenes();

		$form = new Form('', 'Agregar', FORM_METHOD_POST, "control.php", '', "control");	
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "PERMISOS.ALMACENES"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoAgregar"));

		$form->addGroup("GROUP_CHOICES", "", "inline");
		$form->addElement("GROUP_CHOICES", new form_element_radio('', 'addoption" onclick="checkSistemaOption(this.value)', 'grupo', '', '', 1, $opts));
	
		$form->addGroup("GROUP_GRUPO", "Grupo", "inline");
		$form->addElement("GROUP_GRUPO", new form_element_combo("Grupo:", "gid", '', '<br>', '', 1, $grupos, false));
	
		$form->addGroup("GROUP_USUARIO", "Usuario", "none");
		$form->addElement("GROUP_USUARIO", new form_element_combo("Usuario:", "uid", '', '<br>', '', 1, $usuarios, false));
	
		$form->addGroup("GROUP_SISTEMAS", "Almacenes", "inline");
		$form->addElement("GROUP_SISTEMAS", new form_element_combo("Almacen:", "almacen", '', '<br>', '', 1, $almacenes, false));
	
		$form->addGroup("GROUP_BOTONES", "", "inline");
		$form->addElement("GROUP_BOTONES", new form_element_submit("submit", "Agregar acceso", '', '', 20));
		
		return $form->getForm();	
    	}
}
