<?php

class MargenLineaTemplate extends Template {
	function titulo() {
		return '<div align="center"><h2 style="color:#336699">MARGEN ACTUAL vs. MARGEN ESPERADO</h2></div>';
	}

	function search_form() {
		$estaciones = MargenLineaModel::obtieneListaEstaciones();
		$lista_precio = Array ("01"=>"Costo Promedio", "02"=>"Por ultima compra");

		$form = new form2("", "form_margen_linea", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.MARGENLINEA"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estaci&oacute;n:", "", $estaciones, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipolista", "Tipo de costo:", "", $lista_precio, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Periodo:&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes", "Mes", date(m), '', 2, 2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("anio", "A&ntilde;o", date(Y), '', 4, 4));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" ></td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		return $form->getForm();
	}

	function listado($resultados) {
		$result  = '<p align="center">';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera" colspan="2">C&oacute;digo</th>';
		$result .= '<th class="grid_cabecera">Descripci&oacute;n Linea</th>';
		$result .= '<th class="grid_cabecera">Margen Actual</th>';
		$result .= '<th class="grid_cabecera">Margen Esperado</th>';
		$result .= '</tr>';

		$cont = count($resultados);
		for ($i = 0; $i < $cont; $i++) { 
			$a = $resultados[$i];

			$result .= '<tr bgcolor="">';
			$result .= '<td><img src="images/plus.gif" id="img'.htmlentities($a['linea']) .'" onclick="javascript:mostrarDetalle2(\''.htmlentities($a['linea']).'\',\''.htmlentities($a['extra1']).'\',\''.htmlentities($a['extra2']).'\',\''.htmlentities($a['extra3']).'\',\''.htmlentities($a['extra4']).'\')" /></td>';
			$result .= '<td>'. htmlentities($a['linea']) .'</td>';
			$result .= '<td>'. htmlentities($a['descripcion_linea']) .'</td>';
			$result .= '<td align="right">'. htmlentities(number_format($a['margen_actual'], 3)) .'</td>';
			$result .= '<td align="right">'. htmlentities(number_format($a['margen_linea'], 3)) .'</td>';
			$result .= '</tr>';
			$result .= '<tr style="display:none;" id="tr'. htmlentities($a['linea']) .'">';
			$result .= '<td>&nbsp;</td>';
			$result .= '<td colspan="4"><div id="div'. htmlentities($a['linea']) .'" name="div'. htmlentities($a['linea']) .'">Cargando...</div></td>';
			$result .= '</tr>';
		}

		$result .= '<tr>';
		$result .= '</p>';
		return $result;
	}

	function mostrarDetalle($detalle) {
		$result  = '';
		$result .= '<table border="0">';
		$result .= '<tr>';
		$result .= '<td class="grid_cabecera">Código</td>';
		$result .= '<td class="grid_cabecera">Descripción</td>';
		$result .= '<td class="grid_cabecera">Costo</td>';
		$result .= '<td class="grid_cabecera">Precio</td>';
		$result .= '<td class="grid_cabecera">Margen actual</td>';
		$result .= '</tr>';

		for ($i = 0; $i < count($detalle); $i++) {
			$color = ($i%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			$a = $detalle[$i];
			$result .= "<tr class=\"fila bgcolor $color\">";
			$result .= '<td>' . htmlentities($a['codigo']) . '</td>';
			$result .= '<td>' . htmlentities($a['descripcion']) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['costo'], 3)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['precio'], 3)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['margen_actual'], 3)) . '</td>';
			$result .= '</tr>';
		}

		$result .= '</table>';
		return $result;
	}
}

