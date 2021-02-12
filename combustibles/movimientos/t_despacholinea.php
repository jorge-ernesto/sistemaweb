<?php
class DespachoLineaTemplate extends Template {
	function titulo() {
		return '<h2 align="center" style="color:#336699"><b>Despachos en Linea</b></h2>';
	}

	function formSearch() {
		/*$form = new Form('', "Agregar", FORM_METHOD_GET, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.DESPACHOLINEA"));
		$form->addGroup("GRUPO_DESPACHOS", "Tipos");
		$form->addElement("GRUPO_DESPACHOS", new form_element_radio('','rb_tipoconsulta','0','<br/>','','',Array(0=>'Pendientes',1=>'Todos'),''));
		$form->addGroup("GRUPO_BOTONES", "");
		$form->addElement("GRUPO_BOTONES", new form_element_submit("action", "Buscar", '', '', 20));

		return $form->getForm();*/

		$result = '<form method="get" name="Agregar" action="control.php" target="control">';
		$result .= '<table align="center" class="form_body" cellspacing="1" cellpadding="5">';
		$result .= '<tbody><tr>';
		$result .= '<td colspan="1" class="form_group">';
		$result .= '<input id="rqst" name="rqst" value="MOVIMIENTOS.DESPACHOLINEA" type="hidden">';
		$result .= '<fieldset class="form_group" id="GRUPO_DESPACHOS" style="display:inline;">';
		$result .= '<legend class="form_group_title">Tipos</legend>';
		$result .= '<span class="form_radio">';
		$result .= '<input class="form_radio" name="rb_tipoconsulta" value="0" checked="" type="radio">Pendientes';
		$result .= '<input class="form_radio" name="rb_tipoconsulta" value="1" type="radio">Todos';
		$result .= '</span><br></fieldset><br>';
		$result .= '<fieldset class="form_group" id="GRUPO_BOTONES" style="display:inline;">';
		$result .= '<legend class="form_group_title"></legend>';
		$result .= '<input name="action" value="Buscar" class="form_button" size="20" type="submit">';
		$result .= '</fieldset><br></td></td></tr></tbody></table></form>';

		return $result;
	}

	function listado($resultados) {
		$result = '<meta http-equiv="refresh" content="3"/>';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">Estado</th>';
		$result .= '<th class="grid_cabecera"> Fecha y Hora</th>';
		$result .= '<th class="grid_cabecera">Lado</th>';
		$result .= '<th class="grid_cabecera">Manguera</th>';
		$result .= '<th class="grid_cabecera">Precio</th>';
		$result .= '<th class="grid_cabecera">Cantidad</th>';
		$result .= '<th class="grid_cabecera">Total</th>';
		$result .= '<th class="grid_cabecera">Num. Caja</th>';
		$result .= '<th class="grid_cabecera">Num. Tiket</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td align="center">' . htmlentities($a['estado']) . '</td>';
			$result .= '<td>' . htmlentities($a['hora']) . '</td>';
			$result .= '<td align="center">' . htmlentities($a['lado_surtidor']) . '</td>';
			$result .= '<td align="center">' . htmlentities($a['num_mangueras']) . '</td>';
			$result .= '<td align="right">' . htmlentities($a['precio_galon']) . '</td>';
			$result .= '<td align="right">' . htmlentities($a['cant_galones']) . '</td>';
			$result .= '<td align="right">' . htmlentities($a['total']) . '</td>';
			$result .= '<td align="center">' . htmlentities($a['num_caja']) . '</td>';
			$result .= '<td align="center">' . htmlentities($a['num_tiket']) . '</td>';
			$result .= '</tr>';
		}

		$result .= '<tr>';
		return $result;
	}
}
