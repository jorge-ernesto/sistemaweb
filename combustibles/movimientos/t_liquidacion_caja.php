<?php
class VarillasTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Liquidacion de Caja</b></h2>';
    }
    
	function formSearch($fecha, $estacion) {
		if ($estacion == "") 
			$estacion = $_SESSION['almacen'];

		$estaciones = VarillasModel::obtenerSucursales("");
		
		$form = new form2('', 'Form', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.LIQUIDACIONCAJA"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Almacen: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Fecha: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:center"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1500;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>&nbsp;<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		return $form->getForm();
    }

    function listado($resultados, $movi) {
		$result = '';
		$result .= '<table align="center" border = "0.5px" style="background:#FFFFFF">';

		$result .= '<tr>';
			$result .= '<td colspan="8" align="right" style="font-weight:bold">T.C. DEL DIA: '. $resultados[0]["tipocambio"];
		$result .= '</tr>';

		$result .= '<tr>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>FECHA</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>VENTA GASOLINA</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>VENTA GLP</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>VENTA GNV</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>VENTA TIENDA</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>CREDITOS</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>COBRANZAS</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>A RENDIR</b></th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$color = ($i%2==0?"grid_detalle_impar":"grid_detalle_impar");
			$a = $resultados[$i];

			$rendir = ((($a['total_venta_comb'] - $a['af_comb']) + ($a['total_venta_glp'] - $a['af_glp'])) - $a['clientescredito'] + $a['total_venta_market'] + $a['clientecobranza']);

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['fecha']) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total_venta_comb'] - $a['af_comb'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total_venta_glp'] - $a['af_glp'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total_venta_gnv'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total_venta_market'] + $a['total_venta_market_manual'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['clientescredito'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['clientecobranza'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($rendir, 2, '.', ',')) . '</td>';
			$result .= '</tr>';
		}

		$result .= '<tr bgcolor="">';
			$result .= '<td>&nbsp;</td>';
		$result .= '</tr>';

		$result .= '<tr bgcolor="">';
			$result .= '<th colspan="4" bgcolor="4682B4" span style="color:#FFFFFF"><b>NOMBRE</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>S/</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>$</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>DEPOSITO</b></th>';
		$result .= '</tr>';

		for ($x = 0; $x < count($movi); $x++) {
			$color = ($i%2==0?"grid_detalle_impar":"grid_detalle_impar");
			$b = $movi[$x];

			$result .= '<tr bgcolor="">';
			$result .= '<td colspan="4" class="'.$color.'" align = "left">' . htmlentities($b['nombre']) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($b['soles'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($b['dolares'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($b['deposito']) . '</td>';
		}
		$result .= '</table>';
		return $result;
    }
}
