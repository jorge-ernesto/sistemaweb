<?php
class InterfaceSAPTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>Interface SAP</b></h2>';
    }

	function formPrincipal($arrAlmacenes, $iAlmacen, $dInicial, $dFinal, $dUltimoCierre, $estadoConexionSAP, $estadoOpensoft) {
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.INTERFACESAP'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dUltimoCierre . '">'));
		//Verificar parametros de conexion a HANA - SAP estan configurados en opensoft BD integrado -> tabla int_parametros
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-estadoOpensoft" name="txt-estadoOpensoft" value="' . $estadoOpensoft . '">'));
		//Verificar estado conexion con el SAP
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-estadoConexionSAP" name="txt-estadoConexionSAP" value="' . $estadoConexionSAP . '">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacen: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iAlmacen', '', $iAlmacen, $arrAlmacenes, espacios(3), array("onfocus" => "getFechasIF();getDatos();")));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dInicial', '', $dInicial, '', 12, 10));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dFinal', '', $dFinal, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Migrar">Migrar &nbsp;<img src="/sistemaweb/icons/database.png" align="right" alt="Migrar"></button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div class="div-errorMsg" style="text-align: center;"></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("cbo-iAlmacen").focus();}</script>'));
		return $form->getForm();
    }
    
    function gridViewHTML($arrData) {
		$result = '';

		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">DESCRIPCION TABLA</th>';
				$result .= '<th class="grid_cabecera">TABLA</th>';
				$result .= '<th class="grid_cabecera">MENSAJE</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD REGISTROS</th>';
			$result .= '</tr>';
			$counter = 0;
			$result .= '<tbody>';
			foreach ($arrData['hana'] as $row) {
				$color = ($counter%2) == 0 ? 'grid_detalle_impar' : 'grid_detalle_par';
				$result .= '<tr class="'. $color. '">';
					$result .= '<td align="center">' . htmlentities($row["descripcion_tabla"]) . '</td>';
			    	$result .= '<td align="center">' . htmlentities($row["tabla"]) . '</td>';
			    	$result .= '<td align="left">' . htmlentities($row["mensaje"]) . '</td>';
			    	$result .= '<td align="center">' . htmlentities($row["cantidad_registros"]) . '</td>';
			    $result .= '</tr>';
			    $counter++;
			}
			$result .= '</tbody>';
		$result .= '</table>';
		return $result;
    }
}
