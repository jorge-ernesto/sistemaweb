<?php

class SISCONTCtaContablesCRUDTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>SISCONT - Cuentas Contables</b></h2>';
    }
    
	function formPrincipal() {
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.SISCONTCTACONTABLES'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button id="btn-html-agregar" name="action" type="submit" value="Add"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
    }
    
    function gridViewHTML($arrResult) {
		$result = '';

		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th colspan="8" class="grid_cabecera">SISCONT - Cuentas Contables</th>';
			$result .= '</tr>';

			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">Tipo SISCONT</th>';
				$result .= '<th class="grid_cabecera">Tipo Operacion</th>';
				$result .= '<th class="grid_cabecera">Cuenta Contable</th>';
				$result .= '<th class="grid_cabecera">Forma Pago</th>';
				$result .= '<th class="grid_cabecera">Nombre Flujo Efectivo</th>';
				$result .= '<th class="grid_cabecera">Numero Medio Pago</th>';
				$result .= '<th class="grid_cabecera">Nombre Tipo Libro</th>';
				$result .= '<th class="grid_cabecera"></th>';
			$result .= '</tr>';

			$result .= '<tbody>';
			if($arrResult['estado'] == FALSE) {
				$result .= '<tr class="bgcolor">';
					$result .= '<td colspan="8" class="grid_detalle_par" align="center"><b>No hay registros</b></td>';
				$result .= '</tr>';
			} else {
				$counter = 0;
				foreach ($arrResult['result'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
				    	$result .= '<td align ="center">' . htmlentities($row["nu_tiposiscont"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["no_tipo_operacion"]) . '</td>';
				    	$result .= '<td align ="left">' . htmlentities($row["nu_cuentacontable"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["no_forma_pago"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["no_flujoefectivo"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["nu_mediopago"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["no_tipo_libro"]) . '</td>';
				    	$result .= '<td align ="center"><A href="control.php?rqst=MAESTROS.SISCONTCTACONTABLES&action=Upd&nu_id='.$row['nu_id'].'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
				    $result .= '</tr>';
				    $counter++;
				}
			}
			$result .= '</tbody>';
		$result .= '</table>';
		return $result;
    }
    
	function formUpdate($arrResult) {
		$arrData = $arrResult['result'][0];

		$form = new form2('', 'Upd', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.SISCONTCTACONTABLES'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-nu_id" name="nu_id" value="' . $arrData['nu_id'] . '">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Numero Tipo Operación: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			        	<input type="text" maxlength="2" size="4" id="txt-nu_tipooperacion" name="nu_tipooperacion" autocomplete="off" value="' . $arrData['nu_tipooperacion'] . '" />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Numero Cuenta Contable: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			        	<input type="text" maxlength="15" size="17" id="txt-nu_cuentacontable" name="nu_cuentacontable" autocomplete="off" value="' . $arrData['nu_cuentacontable'] . '" />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			if($arrData['no_flujoefectivo'] != ''){
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Nombre Flujo Efectivo: </td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
				        	<input type="text" maxlength="4" size="6" id="txt-no_flujoefectivo" name="no_flujoefectivo" autocomplete="off" value="' . $arrData['no_flujoefectivo'] . '" />
			        	'));
			        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			}

			if($arrData['nu_mediopago'] != ''){
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Numero Medio Pago: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			        	<input type="text" maxlength="3" size="5" id="txt-nu_mediopago" name="nu_mediopago" autocomplete="off" value="' . $arrData['nu_mediopago'] . '" />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			}

			if($arrData['no_tipolibro'] != ''){
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Nombre Tipo Libro: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			        	<input type="text" maxlength="1" size="3" id="txt-no_tipolibro" name="no_tipolibro" autocomplete="off" value="' . $arrData['no_tipolibro'] . '" />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			}

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Numero Tipo Siscont (tablas): </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			        	<input type="text" maxlength="1" size="3" id="txt-nu_tiposiscont" name="nu_tiposiscont" autocomplete="off" value="' . $arrData['nu_tiposiscont'] . '" />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			if($arrData['nu_fpago'] != ''){
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Numero Forma Pago: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			        	<input type="text" maxlength="1" size="3" id="txt-nu_fpago" name="nu_fpago" autocomplete="off" value="' . $arrData['nu_fpago'] . '" />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			}

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html-update" name="action" type="submit" value="Save"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
    }
}
