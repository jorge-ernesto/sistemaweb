<?php
class UserMasterTemplate extends Template {
	function Titulo() {
		return '<h2 style="color:#336699;" align="center">Maestro de usuarios</td>';
	}

	function listado($usuarios) {
		$result  = "";
		$result .= '<form name="users" method="post" action="control.php" target="control">';
		$result .= '<input type="hidden" name="rqst" value="MAESTROS.USERS">';
		$result .= '<input type="hidden" name="action" value="Delete">';
		$result .= '<table align = "center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera" width="30">UID</th>';
		$result .= '<th class="grid_cabecera" width="60">Login</th>';
		$result .= '<th class="grid_cabecera" width="120">Nombre</th>';
		$result .= '<th class="grid_cabecera" width="50">Email</th>';
		$result .= '<th class="grid_cabecera" width="20">&nbsp;</th>';
		$result .= '</tr>';

		$cont = 0;
		foreach($usuarios as $uid => $usuario) {
			$color = ($cont%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			
			$result .= "<tr class=\"fila bgcolor $color\">";
			$result .= '<td><a href="control.php?rqst=MAESTROS.USERS&action=Modificar&uid=' . htmlentities($uid) . '" target="control">' . htmlentities($uid) . '</a></td>';
			$result .= '<td>' . htmlentities($usuario['ch_login']) . '</td>';
			$result .= '<td>' . htmlentities($usuario['ch_nombre']) . '&nbsp;</td>';
			$result .= '<td>' . htmlentities($usuario['ch_email']) . '&nbsp;</td>';
			$result .= '<td>';

			if ($uid > 0) {
				$result .= '<input type="checkbox" name="uids[]" value="' . htmlentities($uid) . '">';
			} else {
				$result .= '&nbsp;';
			}

			$result .= '</td>';
			$result .= '</tr>';
			$cont++;
		}

		$result .= '</table>';
		$result .= '<div align = "center">';
		$result .= '<input type="submit" name="submit" value="Borrar Seleccionado(s)">';
		$result .= '<input type="button" name="btAgregar" value="Agregar Usuario" onclick="formAgregarUsuario()">';
		$result .= '</div>';
		$result .= '</form>';
		return $result;
	}

	function listadoGrupos($uid, $grupos) {
		$result = "";
		$result .= '<form name="grupos" method="post" action="control.php" target="control">';
		$result .= '<h2 style="color:#336699;" align="center">Grupos a los que pertenece el usuario</td>';
		$result .= '<input type="hidden" name="rqst" value="MAESTROS.USERS">';
		$result .= '<input type="hidden" name="action" value="BorrarGrupo">';
		$result .= '<input type="hidden" name="uid" value="' . htmlentities($uid) . '">';
		$result .= '<table>';
		$result .= '<br><br>';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">&nbsp;</th>';
		$result .= '<th class="grid_cabecera">Grupo</th>';
		$result .= '<th class="grid_cabecera">Nombre</th>';
		$result .= '<th class="grid_cabecera">&nbsp;</th>';
		$result .= '</tr>';

		foreach($grupos as $gid => $grupo) {
			$result .= '<tr>';
			$result .= '<td>' . htmlentities($gid) . '</td>';
			$result .= '<td>' . htmlentities($grupo['ch_grupo']) . '</td>';
			$result .= '<td>' . htmlentities($grupo['ch_nombre']) . '</td>';
			$result .= '<td><input type="checkbox" name="gids[]" value="' . htmlentities($gid) . '"></td>';
			$result .= '</tr>';
		}

		$result .= '</table>';
		$result .= '<input type="submit" name="submit" value="Borrar seleccionado(s)">';
		$result .= '<input type="button" name="btAgregar" value="Agregar" onclick="listadoGruposAgregar(' . htmlentities($uid) . ')">';
		$result .= '</form>';
		return $result;
	}

	function formSearch() {
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.USERS"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Nombre:", "ch_nombre", '', '', 10, 25));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '','', 20));
		return $form->getForm();
	}

	function formModificar($usuario, $uid, $mode = 0, $flag = false) {
		$form = new Form('', "Modificar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.USERS"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoModificar"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("uid", $uid));

		if($mode == 1) {
			if ($flag) {
				$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Actualizaci&oacute;n correcta</b>", "<br>", ''));
			} else {
				$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Error al actualizar</b>", "<br>", ''));
			}
		}

		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Login:", "ch_login", trim($usuario['ch_login']), "<br>", '', 10, 15));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Nombre:", "ch_nombre", trim($usuario['ch_nombre']), "<br>", '', 10, 45, true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_password("Password:", "ch_password1", '', "<br>", '', 10, 45));
		$form->addElement(FORM_GROUP_MAIN, new form_element_password("Repetir:", "ch_password2", '', "<br>", '', 10, 45));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("E-Mail:", "ch_email", trim($usuario['ch_email']), "<br>", '', 10, 45));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Clave POS:", "pospassword", trim($usuario['pospassword']), "<br>", '', 10, 45));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Modificar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "<- Regresar", '', '', '', 'onclick="formModificarRegresar()"'));
		return $form->getForm();
	}

	function formAgregar($mode = 0, $flag = false) {
		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.USERS"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoAgregar"));

		if($mode == 1) {
			switch ($flag) {
				case 0:
				$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Usuario agregado correctamente</b>", "<br>", ''));
				break;
				case 1:
				$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Se tiene que agregar contrase&ntilde;a</b>", "<br>", ''));
				break;
				case 2:
				$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Las contrase&ntilde;as no coinciden</b>", "<br>", ''));
				break;
				case 3:
				$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>El usuario ya existe</b>", "<br>", ''));
				break;
				default:
				$form->addElement(FORM_GROUP_MAIN, new form_element_onlytext('', "<b>Error desconocido. Llame a Jaime Cachi al 2759</b>", "<br>", ''));
				break;
			}
		}

		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Login:", "ch_login", '', '<br>', '', 10, 15));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Nombre:", "ch_nombre", '', '<br>', '', 10, 45));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("E-Mail:", "ch_email", '', '<br>', '', 10, 45));
		$form->addElement(FORM_GROUP_MAIN, new form_element_password("Password:", "ch_password1", '', '<br>', '', 10, 45));
		$form->addElement(FORM_GROUP_MAIN, new form_element_password("Repetir:", "ch_password2", '', '<br>', '', 10, 45));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Modificar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "<- Regresar", '', '', '', 'onclick="formModificarRegresar()"'));
		return $form->getForm();
	}

	function formGrupoAgregar($uid) {
		$grupos = UserMasterModel::obtenerGrupos();

		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.USERS"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoGrupoAgregar"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("uid", $uid));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Grupo:", "gid", '', '<br>', '', 1, $grupos, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Agregar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "<- Regresar", '', '', '', 'onclick="formAgregarGrupoRegresar(' . htmlentities($uid) . ')"'));
		return $form->getForm();	
	}
}
