<?php
class AfericionReportTemplate extends Template {
	function getTitulo() {
		return '<h2 align="center"><b>Afericiones</b></h2>';
	}

	function formPrincipal($dInicial, $dFinal, $dUltimoCierre) {
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.AFERICIONESREP'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dUltimoCierre . '">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dInicial', '', $dInicial, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dFinal', '', $dFinal, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar" onfocus=getFechasIF();><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("btn-html").focus();}</script>'));
		return $form->getForm();
	}

	function gridViewHTML($arrResult) {
		$result = '';

		$result .= '<table border="0" align="center" class="report_CRUD">';
		$result .= '<tr bgcolor="#FFFFCD">';
		$result .= '<th class="grid_cabecera">DIA</th>';
		$result .= '<th class="grid_cabecera">TURNO</th>';
		$result .= '<th class="grid_cabecera">CAJA</th>';
		$result .= '<th class="grid_cabecera">TRANS</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">LADO</th>';
		$result .= '<th class="grid_cabecera">CODIGO</th>';
		$result .= '<th class="grid_cabecera">CANTIDAD</th>';
		$result .= '<th class="grid_cabecera">PRECIO</th>';
		$result .= '<th class="grid_cabecera">IMPORTE</th>';
		$result .= '<th class="grid_cabecera">VELOC</th>';
		$result .= '<th class="grid_cabecera">LINEA</th>';
		$result .= '<th class="grid_cabecera">RESPONSABLE</th>';
		$result .= '</tr>';

		$result .= '<tbody>';
		if($arrResult['estado'] == FALSE) {
			$result .= '<tr class="bgcolor">';
			$result .= '<td colspan="13" class="grid_detalle_par" align="center"><b>No hay registros</b></td>';
			$result .= '</tr>';
		} else {
			$counter = 0;
			foreach ($arrResult['result'] as $row) {
				$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
				$result .= '<tr class="'. $color. '">';
					$result .= '<td align ="center">' . htmlentities($row['dia']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['turno']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['caja']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['trans']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['fecha']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['pump']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['producto']) . '</td>';
					$result .= '<td align ="right">' . htmlentities($row['cantidad']) . '</td>';
					$result .= '<td align ="right">' . htmlentities($row['precio']) . '</td>';
					$result .= '<td align ="right">' . htmlentities($row['importe']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['veloc']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['lineas']) . '</td>';
					$result .= '<td align ="center">' . htmlentities($row['responsabl']) . '</td>';
				$result .= '</tr>';
				$counter++;
			}
		}

		$result .= '</tbody>';
		$result .= '</table>';
		return $result;
	}
}
