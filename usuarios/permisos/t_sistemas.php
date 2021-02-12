<?php

class SistemasTemplate extends Template
{
    function titulo()
    {
	return '<h2><b>Acceso a Sistemas</b></h2>';
    }
    
    function listado()
    {
	$accesos = SistemasModel::obtenerSistemasPorUsuario();
	$grupos = SistemasModel::obtenerGrupos();
	$usuarios = SistemasModel::obtenerUsuarios();
	$sistemas = SistemasModel::obtenerSistemas();
	
	$result = '';
	
	$result .= '<form name="accesos" method="post" target="control" action="control.php">';
	$result .= '<input type="hidden" name="rqst" value="PERMISOS.SISTEMAS">';
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>Nombre</td>';
	$result .= '<td>Sistema</td>';
	$result .= '</tr>';
	
	foreach($accesos as $i => $acceso) {
	    $result .= '<tr>';
	    $result .= '<td><input type="checkbox" name="keys[]" value="' . htmlentities($acceso['gid'].$acceso['uid'].$acceso['ch_sistema']) . '"></td>';
	    if ($acceso['gid'] > -1) {
		$result .= '<td>Grupo: ' . htmlentities($grupos[$acceso['gid']]) . '</td>';
	    }
	    else {
		$result .= '<td>Usuario:' . htmlentities($usuarios[$acceso['uid']]) . '</td>';
	    }
	    
	    $result .= '<td>' . htmlentities($acceso['ch_sistema'] . "-" . $sistemas[$acceso['ch_sistema']]) . '</td>';
	    $result .= '</tr>';
	}

	$result .= '</table>';
	$result .= '<input type="submit" name="action" value="Borrar">';
	$result .= '<input type="button" name="btAgregar" value="Agregar" onclick="formSistemaAgregar()">';
	$result .= '</form>';
	
	return $result;
    }
    
    function agregar()
    {
	$opts = Array("usuario"=>"Usuario", "grupo"=>"Grupo");
	$usuarios = SistemasModel::obtenerUsuarios();
	$grupos = SistemasModel::obtenerGrupos();
	$sistemas = SistemasModel::obtenerSistemas();

	$form = new Form('', 'Agregar', FORM_METHOD_POST, "control.php", '', "control");	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "PERMISOS.SISTEMAS"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoAgregar"));

	$form->addGroup("GROUP_CHOICES", "", "inline");
	$form->addElement("GROUP_CHOICES", new form_element_radio('', 'addoption" onclick="checkSistemaOption(this.value)', 'grupo', '', '', 1, $opts));
	
	$form->addGroup("GROUP_GRUPO", "Grupo", "inline");
	$form->addElement("GROUP_GRUPO", new form_element_combo("Grupo:", "gid", '', '<br>', '', 1, $grupos, false));
	
	$form->addGroup("GROUP_USUARIO", "Usuario", "none");
	$form->addElement("GROUP_USUARIO", new form_element_combo("Usuario:", "uid", '', '<br>', '', 1, $usuarios, false));
	
	$form->addGroup("GROUP_SISTEMAS", "Sistemas", "inline");
	$form->addElement("GROUP_SISTEMAS", new form_element_combo("Sistema:", "sistema", '', '<br>', '', 1, $sistemas, false));
	
	$form->addGroup("GROUP_BOTONES", "", "inline");
	$form->addElement("GROUP_BOTONES", new form_element_submit("submit", "Agregar acceso", '', '', 20));
	return $form->getForm();	
    }
}
