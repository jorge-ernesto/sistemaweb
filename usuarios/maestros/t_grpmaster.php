<?php
class GroupMasterTemplate extends Template {
    function Titulo() {
	return '<h2 style="color:#336699;" align="center">Maestro de Grupos</td>';
    }
    
    function listado($grupos) {
	$result  = "";
	$result .= '<form name="users" method="post" action="control.php" target="control">';
	$result .= '<input type="hidden" name="rqst" value="MAESTROS.GROUPS">';
	$result .= '<input type="hidden" name="action" value="Delete">';
	$result .= '<table align = "center">';
	$result .= '<tr>';
	$result .= '<th class="grid_cabecera" width="30">GID</th>';
	$result .= '<th class="grid_cabecera" width="60">Grupo</th>';
	$result .= '<th class="grid_cabecera" width="160">Nombre</th>';
	$result .= '<th class="grid_cabecera" width="20">&nbsp;</th>';
	$result .= '</tr>';

	$cont = 0;
		foreach($grupos as $gid => $grupo) {
			$color = ($cont%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';

		    $result .= "<tr class=\"fila bgcolor $cont $color\">";
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
		    $cont++;
		}
	
	$result .= '</table>';
	$result .= '<div align = "center">';
	$result .= '<input type="submit" name="submit" value="Borrar Seleccionado(s)">';
	$result .= '<input type="button" name="btAgregar" value="Agregar Grupo" onclick="formAgregarGrupo()">';
	$result .= '</form>';
	return $result;
    }
    
	    function formSearch() {
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MASTROS.GROUPS"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Nombre:", "ch_nombre", '', '', 10, 25));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '','', 20));
		return $form->getForm();
	    }
    
    function formModificar($grupo, $gid, $mode = 0, $flag = false) {
	$form = new Form('', "Modificar", FORM_METHOD_POST, "control.php", '', "control");
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.GROUPS"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoModificar"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("gid", $gid));
	
		if ($mode == 1) {
		    if ($flag)
			$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Actualizaci&oacute;n correcta</b>", "<br>", ''));
		    else
			$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Error al actualizar</b>", "<br>", ''));
		}

	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Grupo:", "ch_grupo", trim($grupo['ch_grupo']), "<br>", '', 15, 15));
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Nombre:", "ch_nombre", trim($grupo['ch_nombre']), "<br>", '', 30, 45, false));
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Modificar", '', '', 20));
	$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "<- Regresar", '', '', '', 'onclick="formModificarGrupoRegresar()"'));
	return $form->getForm();
    }

    function formAgregar($mode = 0, $flag = false) {
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
