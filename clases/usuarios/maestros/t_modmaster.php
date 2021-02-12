<?php

class ModMasterTemplate extends Template
{
    function titulo()
    {
	return '<h2><b>Maestro de Modulos y Opciones</b></h2>';
    }
    
    function listado()
    {
	$modulos = ModMasterModel::obtenerModulos();
	
	$result = '';
	
	$result .= '<form name="MODULOS" method="post" action="control.php" target="control">';
	$result .= '<input type="hidden" name="rqst" value="MAESTROS.MODULES">';
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>Nombre</td>';
	$result .= '<td>Descripcion</td>';
	$result .= '</tr>';
	
	foreach($modulos as $ch_modulo => $ch_descripcion) {
	    $result .= '<tr>';
	    $result .= '<td><input type="checkbox" name="keys[]" value="' . htmlentities($ch_modulo) . '"></td>';
	    $result .= '<td><a href="control.php?rqst=MAESTROS.MODULES&action=Modificar&ch_modulo=' . htmlentities($ch_modulo) . '" target="control">' . htmlentities($ch_modulo) . '</td>';
	    $result .= '<td>' . htmlentities($ch_descripcion) . '</td>';
	    $result .= '</tr>';
	}

	$result .= '</table>';
	$result .= '<input type="submit" name="action" value="Borrar">';
	$result .= '<input type="button" name="btAgregar" value="Agregar" onclick="formModuloAgregar()">';
	$result .= '</form>';
	
	return $result;
    }
    
    function formAgregar()
    {
	$modulos = ModMasterModel::obtenerModulos();

	$form = new Form('', 'Agregar', FORM_METHOD_POST, "control.php", '', "control");
	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.MODULES"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoAgregar"));

	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Modulo:", "ch_modulo", '', '<br>', '', 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Descripcion:", "ch_descripcion", '', '<br>', '', 22, 30));	

	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Agregar", '', '', 20));
	return $form->getForm();
    }
    
    function formModificar($ch_modulo)
    {
	$modulo = ModMasterModel::obtenerModulo($ch_modulo);
	
	$form = new Form('', 'Modificar', FORM_METHOD_POST, "control.php", '', "control");
	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.MODULES"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoModificar"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_modulo", $ch_modulo));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Descripcion:", "ch_descripcion", $modulo, '<br>', '', 22, 30));
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Modificar", '', '', 20));
	
	return $form->getForm();
    }
    
}

