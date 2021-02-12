<?php

class SAPMapeoTablasCRUDTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>SAP - Mapeo Tablas</b></h2>';
    }
    
	function formPrincipal($arrSapMapeoTablas, $nu_id_tipo_tabla) {
		if ($arrSapMapeoTablas['estado']) {
			$html_option = '';
			foreach ($arrSapMapeoTablas['result'] as $row) {
				$selected = NULL;
				if($row['id_tipo_tabla'] == $nu_id_tipo_tabla)
					$selected = "selected";
				$html_option .= '<option value="' . $row['id_tipo_tabla'] . '" ' . $selected . '>' . $row['no_tabla'] . '</option>';
			}
		} else
			$html_option = $arrSapMapeoTablas['mensaje'];

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.SAPMAPEOTABLAS'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Seleccionar: '));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
					'<select id="cbo-sap-mapeo-tablas" onfocus="getSapMapeoTablas()">
					    ' . $html_option . '
				    </select>'
					));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
    }
    
    function gridViewHTML($arrResult, $nu_id_tipo_tabla) {
		$result = '';
		$result .= '<form method="post" name="formAdd" action="control.php?rqst=MAESTROS.SAPMAPEOTABLAS" target="control">';
		$result .= '<input type="hidden" id="id_tipo_tabla" name="id_tipo_tabla" value="' . $nu_id_tipo_tabla . '">';
			$result .= '<table border="0" align="center" class="report_CRUD">';
				$result .= '<tr bgcolor="#FFFFCD">';
					$result .= '<th class="grid_cabecera"></th>';
					$result .= '<th class="grid_cabecera">Codigo OpenComb</th>';
					$result .= '<th class="grid_cabecera">Nombre OpenComb</th>';
					$result .= '<th class="grid_cabecera">Codigo SAP</th>';
					$result .= '<th class="grid_cabecera"></th>';
				$result .= '</tr>';

				$result .= '<tbody>';
				if($arrResult['estado'] == FALSE) {
					$result .= '<tr class="bgcolor">';
						$result .= '<td colspan="4" class="grid_detalle_par" align="center"><b>No hay registros</b></td>';
					$result .= '</tr>';
				} else {
					$counter = 0;
					$_counter = 1;
					$validar_btn = FALSE;
					foreach ($arrResult['result'] as $row) {
						$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
					    if(empty($row["sap_codigo"]))
					    	$validar_btn = TRUE;
				    	$result .= '<tr class="'. $color. '">';
				    		$result .= '<td align="center">' . htmlentities($_counter) . '. </td>';
					    	$result .= '<td align="center"><input type="hidden" id="valueOpenCodigo' . $nu_id_tipo_tabla . $row['id_tipo_tabla_detalle'] . '" name="arrOpenCombCodigo[]" value = "' . $row["opencomb_codigo"] . '" />' . htmlentities($row["opencomb_codigo"]) . '</td>';
					    	$result .= '<td align="left">' . htmlentities($row["opencomb_nombre"]) . '</td>';
					    	$result .= '<td align="center"><input type="text" id="valueSapCodigo' . $nu_id_tipo_tabla . $row['id_tipo_tabla_detalle'] . '" name="arrSapCodigo[]" maxlength="16" size="16" value="' . htmlentities($row["sap_codigo"]) . '" autocomplete="off"/></td>';
					    	if (!empty($row["sap_codigo"]))
					    		$result .= '<td align ="center"><img src="/sistemaweb/icons/open.gif" align="middle" border="0" onclick="javascript:updateSapMapeoTabla(\'' . $nu_id_tipo_tabla . '\', \'' . $row['id_tipo_tabla_detalle'] . '\')" />&nbsp;</td>';
					    $result .= '</tr>';
					    $counter++;
					    $_counter++;
					}
				}
				$result .= '</tbody>';
				
				$result .= '<tfoot>';
					$result .= '<tr class="bgcolor">';
						$result .= '<td colspan="5" class="grid_detalle_par" align="center">&nbsp;</td>';
					$result .= '</tr>';
					if($validar_btn){
						$result .= '<tr class="bgcolor">';
							$result .= '<td colspan="3" class="grid_detalle_par" align="center">&nbsp;</td>';
							$result .= '<td class="grid_detalle_par" align="right"><button type="submit" id="btn-html-agregar" name="action" value="SAVE"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar  </button></td>';
						$result .= '</tr>';
					}
				$result .= '</tfoot>';
			$result .= '</table>';
		$result .= '</form>';
		return $result;
    }
}
