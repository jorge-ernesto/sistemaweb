<?php
class EliminaCuentaxCobrarTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Eliminaci&oacute;n de Cuentas por Cobrar</b></h2>';
	}
	
	function formSearch($almacen,$fecha,$fecha2){
	
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ELIMINACION"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Codigo:"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", $fecha2, '', '', 20, 20, false));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('</td><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Buscar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));
		return $form->getForm();
	}

	function listado($resultados) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">TIP. MOV</th>';
		$result .= '<th class="grid_cabecera">TIPO</th>';
		$result .= '<th class="grid_cabecera">SERIE</th>';
		$result .= '<th class="grid_cabecera">N&Uacute;MERO</th>';
		$result .= '<th class="grid_cabecera">FEC. MOV</th>';
		$result .= '<th class="grid_cabecera">FEC. ACTUA.</th>';
		$result .= '<th class="grid_cabecera">IMPORTE</th>';
		$result .= '<th class="grid_cabecera">NUM DOC REF.</th>';
		$result .= '<th class="grid_cabecera">MONEDA</th>';
		//$result .= '<th class="grid_cabecera"></th>';
		$result .= '</tr>';

			for ($i = 0; $i < count($resultados); $i++) {
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
				$a = $resultados[$i];
				$result .= '<tr bgcolor="">';			
				$result .= '<td class="'.$color.'">' . htmlentities($a['ch_tipmovimiento']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['ch_tipdocumento']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['ch_seriedocumento']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['dt_fechamovimiento']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['fecha_actualizacion']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['dt_fecha_actualizacion']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['nu_importemovimiento']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['fecha_actualizacion']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['ch_numdocreferencia']) . '</td>';
				$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Estas seguro de borrar este registro '. $a['ch_seriedocumento'].'?\',\'control.php?rqst=MOVIMIENTOS.ELIMINACION&action=Eliminar&cli_codigo='.($a['id_cuadre_turno_ticket']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
				$result .= '</tr>';
			}
		$result .= '</table>';
		return $result;
	}
}
