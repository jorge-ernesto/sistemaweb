<?php

class ModulosTemplate extends Template
{
    function titulo() {
    	return '<h2 style="color:#336699;" align="center">Acceso de modulos</td>';
    }
    
    function listado()
    {
	$accesos = ModulosModel::obtenerAccesos();
	$usuarios = ModulosModel::obtenerUsuarios();
	$grupos = ModulosModel::obtenerGrupos();
	$modulos = ModulosModel::obtenerModulos();
	
	$result = '';
	
	$result .= '<form name="accesos" method="post" action="control.php" target="control">';
	$result .= '<input type="hidden" name="rqst" value="PERMISOS.MODULOS">';
	$result .= '<table align = "center">';
	$result .= '<tr>';
	$result .= '<th class="grid_cabecera" width="30">&nbsp;</th>';
	$result .= '<th class="grid_cabecera" width="60">Modulo</th>';
	$result .= '<th class="grid_cabecera" width="180">Nombre</th>';
	$result .= '</tr>';

	foreach($accesos as $i=>$acceso) {
		$color = ($i%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';

	    $result .= "<tr class=\"fila bgcolor $color\">";
	    $result .= '<td><input type="checkbox" name="keys[]" value="' . htmlentities(trim($acceso['ch_modulo']).$acceso['uid'].$acceso['gid']) . '"></td>';
	    $result .= '<td>' . htmlentities($modulos[$acceso['ch_modulo']]) . '</td>';

	    if ($acceso['uid'] < 0) {
		$result .= '<td>' . htmlentities("Grupo: " . $grupos[$acceso['gid']]) . '</td>';
	    }	    
	    else {
		$result .= '<td>' . htmlentities("Usuario: " . $usuarios[$acceso['uid']]) . '</td>';
	    }
	    
	    $result .= '</tr>';
	}

	$result .= '</table>';
	$result .= '<div align = "center">';
	$result .= '<input type="submit" name="action" value="Borrar">';
	$result .= '<input type="button" name="btAgregar" value="Agregar" onclick="formAccesoAgregar()">';
	$result .= '</form>';
	
	return $result;
    }
    
    function formAgregar()
    {
        $opts = Array("usuario"=>"Usuario", "grupo"=>"Grupo");
        $usuarios = SistemasModel::obtenerUsuarios();
        $grupos = SistemasModel::obtenerGrupos();
        $modulos = ModulosModel::obtenerModulos();

        $form = new Form('', 'Agregar', FORM_METHOD_POST, "control.php", '', "control");
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "PERMISOS.MODULOS"));
        $form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoAgregar"));

        $form->addGroup("GROUP_CHOICES", "", "inline");
        $form->addElement("GROUP_CHOICES", new form_element_radio('', 'addoption" onclick="checkSistemaOption(this.value)', 'grupo', '', '', 1, $opts));

        $form->addGroup("GROUP_GRUPO", "Grupo", "inline");
        $form->addElement("GROUP_GRUPO", new form_element_combo("Grupo:", "gid", '', '<br>', '', 1, $grupos, false));

        $form->addGroup("GROUP_USUARIO", "Usuario", "none");
        $form->addElement("GROUP_USUARIO", new form_element_combo("Usuario:", "uid", '', '<br>', '', 1, $usuarios, false));

        $form->addGroup("GROUP_MODULOS", "Sistemas", "inline");
	$form->addElement("GROUP_MODULOS", new form_element_combo("Modulo:", "modulo", '', '<br>', '', 1, $modulos, false));

        $form->addGroup("GROUP_BOTONES", "", "inline");
        $form->addElement("GROUP_BOTONES", new form_element_submit("submit", "Agregar acceso", '', '', 20));

	return $form->getForm();
    }
}

