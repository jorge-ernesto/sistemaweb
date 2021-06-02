<?php

class CierresZTemplate extends Template {
	function titulo() {
		return '<div align="center"><h2><b>Cierres en Z</b></h2></div>';
	}

	function formSearch($almacenes) {    	
		$fecha_actual = date("Y-m-d");

		$form = new Form('', "Agregar", FORM_METHOD_GET, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CIERRES_Z"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table><tr><td align='right'>Almacen : </td><td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("","ch_almacen","--","<br/>","","",$almacenes,false));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td align='right'>Del : </td><td>"));		
		// $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "ch_fecha_del", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.ch_fecha_del\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>', '', 10, 10, true));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="date" name="ch_fecha_del" value="'.$fecha_actual.'">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td align='right'>Al : </td><td>"));		
		// $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "ch_fecha_al", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.ch_fecha_al\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>', '', 10, 10, true));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="date" name="ch_fecha_al" value="'.$fecha_actual.'">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td align='right'>Num. Caja : </td><td>"));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "ch_caja", '', '', '', 5, 2, false));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td colspan='2' align='center'>"));		
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Buscar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

		return $form->getForm();
	}

	function listado($resultados, $totales) {
		$result .= '<table border="0">';
		$result .= '<tr bgcolor="#D9F9B2">';
		$result .= '<th class="grid_cabecera">Fecha del Sistema</th>';
		$result .= '<th class="grid_cabecera">Turno</th>';
		$result .= '<th class="grid_cabecera">Numero de Caja</th>';
		$result .= '<th class="grid_cabecera">Serie Registradora</th>';
		$result .= '<th class="grid_cabecera">Numero Z</th>';
		$result .= '<th class="grid_cabecera">Fecha y Hora Apertura</th>';
		$result .= '<th class="grid_cabecera">Fecha y Hora Cierre</th>';
		$result .= '<th class="grid_cabecera">Numero Tiket Inicial</th>';
		$result .= '<th class="grid_cabecera">Numero Tiket Final</th>';
		$result .= '<th class="grid_cabecera">Numero Boletas</th>';
		$result .= '<th class="grid_cabecera">Importe Total Boletas</th>';
		$result .= '<th class="grid_cabecera">Impuesto Total Boletas</th>';
		$result .= '<th class="grid_cabecera">Numero Facturas</th>';
		$result .= '<th class="grid_cabecera">Importe Total Facturas</th>';
		$result .= '<th class="grid_cabecera">Impuesto Total Facturas</th>';
		$result .= '<th class="grid_cabecera">Numero Tikets</th>';
		$result .= '<th class="grid_cabecera">Importe Total Tikets</th>';
		$result .= '<th class="grid_cabecera">Impuesto Total Tikets</th>';
		$result .= '<th class="grid_cabecera">Tipo de Cambio</th>';
		$result .= '<th class="grid_cabecera">Sucursal</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td>' . htmlentities($a['fecha_sistema']) . '</td>';
			$result .= '<td>' . htmlentities($a['turno']) . '</td>';
			$result .= '<td>' . htmlentities($a['numero_caja']) . '</td>';
			$result .= '<td>' . htmlentities($a['serie_registradora']) . '</td>';
			$result .= '<td>' . htmlentities($a['numero_z']) . '</td>';
			$result .= '<td>' . htmlentities($a['fecha_hora_apertura']) . '</td>';
			$result .= '<td>' . htmlentities($a['fecha_hora_cierre']) . '</td>';
			$result .= '<td>' . htmlentities($a['numero_tiket_inicial']) . '</td>';
			$result .= '<td>' . htmlentities($a['numero_tiket_final']) . '</td>';
			$result .= '<td>' . htmlentities($a['numero_boletas']) . '</td>';
			$result .= '<td><p align="right">' . htmlentities($a['importe_total_boletas']) . '</td>';
			$result .= '<td><p align="right">' . htmlentities($a['impuesto_total_boletas']) . '</td>';
			$result .= '<td>' . htmlentities($a['numero_facturas']) . '</td>';
			$result .= '<td><p align="right">' . htmlentities($a['importe_total_facturas']) . '</td>';
			$result .= '<td><p align="right">' . htmlentities($a['impuesto_total_facturas']) . '</td>';
			$result .= '<td><p align="right">' . htmlentities($a['numero_tikets']) . '</td>';
			$result .= '<td><p align="right">' . htmlentities($a['importe_total_tikets']) . '</td>';
			$result .= '<td><p align="right">' . htmlentities($a['impuesto_total_tikets']) . '</td>';
			$result .= '<td><p align="right">' . htmlentities($a['tipo_cambio']) . '</td>';
			$result .= '<td>' . htmlentities($a['sucursal']) . '</td>';
			$result .= '</tr>';
		}

		$result .= '<tr>';
		$result .= '<td><b>TOTALES</b></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td><p align="right"><b>' . htmlentities($totales['total_importe_total_boletas']) . '</b></td>';
		$result .= '<td><p align="right"><b>' . htmlentities($totales['total_impuesto_total_boletas']) . '</b></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td><p align="right"><b>' . htmlentities($totales['total_importe_total_facturas']) . '</b></td>';
		$result .= '<td><p align="right"><b>' . htmlentities($totales['total_impuesto_total_facturas']) . '</b></td>';
		$result .= '<td><p align="right"><b>' . htmlentities($totales['total_numero_tikets']) . '</b></td>';
		$result .= '<td><p align="right"><b>' . htmlentities($totales['total_importe_total_tikets']) . '</b></td>';
		$result .= '<td<p align="right"><b>' . htmlentities($totales['total_impuesto_total_tikets']) . '</b></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';

		$result .= '</tr>';

		return $result;
	}
}
