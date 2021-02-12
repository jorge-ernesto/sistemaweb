<?php
class ParticipaVentaLineaTemplate extends Template {
	function titulo() {
		return '<div align="center"><h2>PARTICIPACION DE VENTA POR LINEA</h2></div><hr>';
	}

	function formSearch() {
		$estaciones = ParticipaVentaLineaModel::ObtenerEstaciones();
		$hoy = date("d/m/Y");
		/*$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
	
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.PARTICIPAVENTALINEA"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));
	
		$form->addGroup("GRUPO_FECHA", "Fecha");
		$form->addElement("GRUPO_FECHA", new form_element_text("Desde:", "desde", $hoy, '', '', 12, 10));
		$form->addElement("GRUPO_FECHA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
		$form->addElement("GRUPO_FECHA", new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div><br/>'));
		$form->addElement("GRUPO_FECHA", new form_element_text("Hasta:", "hasta", $hoy, '', '', 12, 10));
		$form->addElement("GRUPO_FECHA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
		$form->addGroup("GRUPO_ALMACEN", "Almacenes");
		$form->addElement("GRUPO_ALMACEN", new form_element_combo("Almacenes:", "estacion", $_REQUEST['estacion'], "<br>", "", 1, $estaciones, false, ''));

		$form->addGroup("GRUPO_BOTONES", "&nbsp;");
		$form->addElement("GRUPO_BOTONES", new form_element_submit("submit", "Buscar"));
		return $form->getForm();*/

		var_dump($estaciones);

		$select = '<select name="estacion" id="estacion" class="form_combo" size="1" style="display:inline">';
	
		foreach ($estaciones as $key => $estacion) {
			var_dump($estacion);
			$select .= '<option value="'.$key.'"> '.$estacion.' </option><br>';
		}
	
		$form = <<<EOT
		<form method="post" name="Buscar" action="control.php" target="control">
	<table class="form_body" cellspacing="1" cellpadding="5" align="center">
		<tbody>
			<tr>
				<td colspan="1" class="form_group">
					<input id="rqst" name="rqst" value="REPORTES.PARTICIPAVENTALINEA" type="hidden">
					<input id="action" name="action" value="Buscar" type="hidden">
					<fieldset class="form_group" id="GRUPO_FECHA" style="display:inline;">
						<legend class="form_group_title">Fecha</legend>
						<span class="form_label">Desde:</span>&nbsp;
						<input name="desde" id="desde" value="$hoy" class="form_input" size="12" maxlength="10" type="text"><br>
						<span class="form_label">Hasta:</span>&nbsp;
						<input name="hasta" id="hasta" value="$hoy" class="form_input" size="12" maxlength="10" type="text">
					</fieldset><br>
					<fieldset class="form_group" id="GRUPO_ALMACEN" style="display:inline;">
						<legend class="form_group_title">Almacenes</legend>
						<span class="form_label">Almacenes:</span>&nbsp;
						{$select}&nbsp;&nbsp;&nbsp;
					</fieldset><br>
					<fieldset class="form_group" id="GRUPO_BOTONES" style="display:inline;">
						<legend class="form_group_title">&nbsp;</legend>
						<input name="submit" value="Buscar" class="form_button" size="" type="submit">
					</fieldset><br>
				</td>
			</tr>
		</tbody>
	</table>
</form>
EOT;

	return $form;
	}

	function listado($resultados,$fe_desde,$fe_hasta,$fe_estacion) {
	
		$result .= '<p align="center">';
		/*$result .= '<input type="text" id="f_desde" name="f_desde" value="'.$fe_desde.'">';
		$result .= '<input type="text" id="f_hasta" name="f_hasta" value="'.$fe_hasta.'">';
		$result .= '<input type="text" id="f_estacion" name="f_estacion" value="'.$fe_estacion.'">';*/
		$result .= '<table border="1">';
		$result .= '<tr>';
		$result .= '<th colspan="2">Codigo</th>';
		$result .= '<th>Descripcion Linea</th>';
		$result .= '<th>Cantidad</th>';
		$result .= '<th>% Cant</th>';
		$result .= '<th>Importe</th>';
		$result .= '<th>% Import</th>';
		$result .= '</tr>';
	
		for ($i = 0; $i < count($resultados['filas']); $i++) {
			$a = $resultados['filas'][$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td><img src="images/plus.gif" id="img' . htmlentities($a['linea']) . '" onclick="javascript:mostrarDetalle(\'' . htmlentities($a['linea']) . '\',\'' . htmlentities($fe_desde) . '\',\'' . htmlentities($fe_hasta) . '\',\'' . htmlentities($fe_estacion) . '\')"/></td>';
			$result .= '<td>' . htmlentities($a['linea']) . '</td>';
			$result .= '<td width="300px">' . htmlentities($a['descripcion_linea']) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['cantidad'], 3)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['porcentaje_cantidad'], 3)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['importe'], 3)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['porcentaje_importe'], 3)) . '</td>';
			$result .= '</tr>';
			$result .= '<tr style="display:none;" id="tr' . htmlentities($a['linea']) . '">';
			$result .= '<td>&nbsp;</td>';
			$result .= '<td colspan="6"><div id="div' . htmlentities($a['linea']) . '" name="div' . htmlentities($a['linea']) . '">Cargando...</div></td>';
			$result .= '</tr>';
		}
		$result .= '<tr>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td><b>TOTAL</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultados['totales']['cantidad'], 3)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultados['totales']['porcentaje_cantidad'], 3)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultados['totales']['importe'], 3)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultados['totales']['porcentaje_importe'], 3)) . '</b></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '</p>';
		return $result;
	}

	function mostrarDetalle($detalle) {
		$result .= '<table border="1">';
		$result .= '<tr>';
		$result .= '<td width="70px"><b>Codigo</b></td>';
		$result .= '<td width="248px"><b>Descripcion</b></td>';
		$result .= '<td width="50px"><b>Cantidad</b></td>';
		$result .= '<td width="42px">&nbsp;</td>';
		$result .= '<td width="45px"><b>Importe</b></td>';
		$result .= '<td width="52px">&nbsp;</td>';
		$result .= '</tr>';
		for ($i = 0; $i < count($detalle); $i++) {
			$a = $detalle[$i];
			$result .= '<tr>';
			$result .= '<td>' . htmlentities($a['codigo']) . '</td>';
			$result .= '<td>' . htmlentities($a['descripcion']) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['cantidad'], 3)) . '</td>';
			$result .= '<td align="right">&nbsp;</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['importe'], 3)) . '</td>';
			$result .= '<td align="right">&nbsp;</td>';
			$result .= '</tr>';
		}
		$result .= '</table>';
		return $result;
	}
}
