<?php

class GroupMasterTemplate extends Template
{
    function Titulo()
    {
	return '<h2><b>Maestro de Grupos</b></h2>';
    }
    
    function listado($grupos, $modo, $flag)
    {
	$result  = "";
	
	$result .= '<form name="users" method="post" action="control.php" target="control">';
	$result .= '<input type="hidden" name="rqst" value="MAESTROS.GROUPS">';
	$result .= '<input type="hidden" name="action" value="Delete">';
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<th>GID</th>';
	$result .= '<th>Grupo</th>';
	$result .= '<th>Nombre</th>';
	$result .= '<th>&nbsp;</th>';
	$result .= '</tr>';

	foreach($grupos as $gid => $grupo) {
	    $result .= '<tr>';
	    $result .= '<td><a href="control.php?rqst=MAESTROS.GROUPS&action=Modificar&gid=' . htmlentities($gid) . '" target="control">' . htmlentities($gid) . '</a></td>';
	    $result .= '<td>' . htmlentities($grupo['ch_grupo']) . '</td>';
	    $result .= '<td>' . htmlentities($grupo['ch_nombre']) . '&nbsp;</td>';
	    $result .= '<td>';
	    if ($gid > 0)
		$result .= '<input type="checkbox" name="gids[]" value="' . htmlentities($gid) . '">';
	    else
		$result .= '&nbsp;';
	    $result .= '</td>';
	    $result .= '</tr>';
	}
	
	$result .= '</table>';
	$result .= '<input type="submit" name="submit" value="Borrar Seleccionados">';
	$result .= '<input type="button" name="btAgregar" value="Agregar" onclick="formAgregarGrupo()">';
	$result .= '</form>';
	return $result;
    }
    

    function formSearch()
    {
	$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MASTROS.GROUPS"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Nombre:", "ch_nombre", '', "<br>", '', 10, 15));
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '','', 20));
	return $form->getForm();
    }
    
    function formModificar($grupo, $gid, $mode = 0, $flag = false)
    {
	$form = new Form('', "Modificar", FORM_METHOD_POST, "control.php", '', "control");
	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.GROUPS"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoModificar"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("gid", $gid));
	
	if ($mode == 1) {
	    if ($flag)
		$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Actualizacion correcta</b>", "<br>", ''));
	    else
		$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Error al actualizar</b>", "<br>", ''));
	}

	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Grupo:", "ch_grupo", trim($grupo['ch_grupo']), "<br>", '', 10, 15));
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Nombre:", "ch_nombre", trim($grupo['ch_nombre']), "<br>", '', 10, 45, false));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Modificar", '', '', 20));
	$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "<- Regresar", '', '', '', 'onclick="formModificarGrupoRegresar()"'));
	return $form->getForm();
    }
    
    function formAgregar($mode = 0, $flag = false)
    {
	$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.GROUPS"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoAgregar"));
	
	if ($mode == 1) {
	    switch ($flag) {
		case 0:
		    $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Grupo agregado correctamente</b>", "<br>", ''));
		    break;
		case 3:
		    $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>El grupo ya existe</b>", "<br>", ''));
		    break;
		default:
		    $form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Error desconocido. Llame a Jaime Cachi al 2759</b>", "<br>", ''));
		    break;
	    }
	}

	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Grupo:", "ch_grupo", '', '<br>', '', 10, 15));
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Nombre:", "ch_nombre", '', '<br>', '', 10, 45));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Modificar", '', '', 20));
	$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "<- Regresar", '', '', '', 'onclick="formModificarGrupoRegresar()"'));

	return $form->getForm();
    }
}
