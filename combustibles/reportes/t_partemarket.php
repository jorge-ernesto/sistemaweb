<?php

class ParteMarketTemplate extends Template {

	function search_form() {
		$fecha = date(d."/".m."/".Y); 

		$estaciones = ParteMarketModel::obtieneListaEstaciones();
		//$estaciones['TODAS'] = "Todas las estaciones";
		$turnos = ParteMarketModel::obtieneTurnos();

		$form = new form2("Parte Diario de Liquidacion de Ventas de Market", "form_parte_diario", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.PARTEMARKET"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0"><tr><td align="right">Seleccionar Almacen: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Fecha Inicial: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_parte_diario.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Fecha Final: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_parte_diario.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Seleccionar Turno: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "turno", '', '<br>', '', '', $turnos, false, ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td  colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Reporte"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp<button name="action" type="submit" value="Imprimir"><img src="/sistemaweb/icons/imprimir.gif" align="right" />Imprimir</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}

	function reporte($resultado0,$resultado1,$resultado2, $resultado3, $desde, $hasta, $estacion) {

		$result = '<table border="0" align="center" width="650px"><tr>';
		$result .= '<td  align="right" style="font-weight:bold">';
		$result .= 'T.C. DEL DIA: '.$resultado0['propiedades']['0'];
		$result .= '</td>';
		$result .= '</tr></table>';

		$result .= '<br/>';

		$result .= '<table align="center">';

		$result .= '<tr><td>';
		$result .= '<table border="1" align="center">';
		$result .= '<tr>';
		$result .= '<th>Codigo</th>';
		$result .= '<th>Descripcion Linea</th>';
		$result .= '<th>Cantidad</th>';
		$result .= '<th>% Cant</th>';
		$result .= '<th>Importe</th>';
		$result .= '<th>% Import</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultado1['filas']); $i++) {
			$a = $resultado1['filas'][$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td>' . htmlentities($a['linea']) . '</td>';
			$result .= '<td>' . htmlentities($a['descripcion_linea']) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['cantidad'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['porcentaje_cantidad'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['importe'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['porcentaje_importe'], 2)) . '</td>';
			$result .= '</tr>';
		}

		$result .= '<tr>';
		$result .= '<td colspan="2"><b>TOTAL</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado1['totales']['cantidad'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado1['totales']['porcentaje_cantidad'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado1['totales']['importe'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado1['totales']['porcentaje_importe'], 2)) . '</b></td>';
		$result .= '</tr>';

		$result .= '</table>';

		$result .= '</br>';
		$result .= 'Resumen de Venta por Turno - Caja';
//reporte por caja turno

		$result .= '<table border="1" align="center">';
		$result .= '<tr>';
		$result .= '<th>Turno</th>';
		$result .= '<th>Caja 1</th>';
		$result .= '<th>Caja 2</th>';
		$result .= '<th>Caja 3</th>';
		$result .= '<th>Caja 4</th>';
		$result .= '<th>Caja 5</th>';
		$result .= '<th>Caja 6</th>';
		$result .= '<th>Caja 7</th>';
		$result .= '<th>Caja 8</th>';
		$result .= '<th>Caja 9</th>';
		$result .= '<th>Total Turno</th>';
		$result .= '</tr>';
	
		for ($i = 0; $i < count($resultado3['filas']); $i++) {
			$a = $resultado3['filas'][$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td align="right">Turno ' . $a['turno'] . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja1'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja2'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja3'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja4'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja5'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja6'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja7'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja8'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja9'], 2)) . '</td>';
			$result .= '<td align="right">' . htmlentities(number_format($a['caja1']+$a['caja2']+$a['caja3']+$a['caja4']+$a['caja5']+$a['caja6']+$a['caja7']+$a['caja8']+$a['caja9'], 2)) . '</td>';
			$result .= '</tr>';
		}

		$result .= '<tr>';
		$result .= '<td colspan="1"><b>TOTAL</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja1'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja2'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja3'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja4'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja5'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja6'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja7'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja8'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totcaja9'], 2)) . '</b></td>';
		$result .= '<td align="right"><b>' . htmlentities(number_format($resultado3['totales']['totaltodo'], 2)) . '</b></td>';
		$result .= '</tr>';

		$result .= '</table>';



		$result .= '</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>';

		$result .= '<table border="1" align="center">';
		$result .= '<tr><td colspan="10"><h3>IV. VENTAS MARKET</h3>';
		$numfilas = 0;

		foreach($resultado2['propiedades'] as $a => $almacenes) {
			foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				$numfilas= $numfilas +1;
				$result .= ParteMarketTemplate::imprimirLineaUnica($venta, $ch_almacen, "");
			}
		}

		$result .= '</table>';

		$result .= '</td></tr></table>';
		return $result;
	}

	function imprimirLineaUnica($array, $label,$totalventa) {
		$result  = '<tr><td>&nbsp;</td><td align="right">&nbsp;</td></tr>';
		$result .= '<tr><td>&nbsp;</td><td align="right">&nbsp;</td></tr>';
		$result .= '<tr><td style="font-weight:bold">'.$array[2]['valor'][0].'</td>';
		$result .= '<td align="right" style="font-weight:bold">'.htmlentities(number_format($array[2]['valor'][1], 2, '.', ',')).'</td></tr>';
		$result .= '<tr><td colspan="2">&nbsp;</td></tr>';
		$result .= '<tr><td>'.$array[3]['valor'][0].'</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[3]['valor'][1], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>'.$array[4]['valor'][0].'</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[4]['valor'][1], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td colspan="2">&nbsp;</td></tr>';
		$result .= '<tr><td colspan="2" style="font-weight:bold">TARJETAS DE CREDITO</td></tr>';

		for($i = 5; $i < $array[0]['valor'][0] + 5; $i++) {
			$result .= '<tr><td>'.$array[$i]['valor'][0].'</td>';
			$result .= '<td align="right">'. htmlentities(number_format($array[$i]['valor'][1], 2, '.', ',')) .'</td></tr>';
		}

		$result .= '<tr><td style="font-weight:bold">'.$array[$i]['valor'][0].'</td>';
		$result .= '<td align="right" style="font-weight:bold">'. htmlentities(number_format($array[$i]['valor'][1], 2, '.', ',')) .'</td></tr>';

		$result .= '<tr><td colspan="2">&nbsp;</td></tr>';
		$result .= '<tr><td>'.$array[$i+1]['valor'][0].'</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[$i+1]['valor'][1], 2, '.', ',')) .'</td></tr>';

		$result .= '<tr><td>'.$array[$i+4]['valor'][0].'</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[$i+4]['valor'][1], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>'.$array[$i+5]['valor'][0].'</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[$i+5]['valor'][1], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>'.$array[$i+2]['valor'][0].'</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[$i+2]['valor'][1], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>'.$array[$i+3]['valor'][0].'</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[$i+3]['valor'][1], 2, '.', ',')) .'</td></tr>';

		$result .= '<tr><td style="font-weight:bold">SUSTENTO</td>';
		$result .= '<td align="right" style="color:blue;font-weight:bold">'. htmlentities(number_format($array[3]['valor'][1]+$array[4]['valor'][1]+$array[$i]['valor'][1]+$array[$i+1]['valor'][1]+$array[$i+2]['valor'][1]+$array[$i+3]['valor'][1]+$array[$i+4]['valor'][1]+$array[$i+5]['valor'][1], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td colspan="2">&nbsp;</td></tr>';
		$result .= '<tr><td style="font-weight:bold">DIFERENCIA</td>';
		$result .= '<td align="right" style="color:blue;font-weight:bold">'. htmlentities(number_format($array[2]['valor'][1]-($array[3]['valor'][1]+$array[4]['valor'][1]+$array[$i]['valor'][1]+$array[$i+1]['valor'][1]+$array[$i+2]['valor'][1]+$array[$i+3]['valor'][1]+$array[$i+4]['valor'][1]+$array[$i+5]['valor'][1]), 2, '.', ',')) .'</td></tr>';
		return $result;
	}

	function imprimir($acumula) {
		$texto_impresion = "";
		$CRLF = "\r\n";
		$texto_impresion .= $CRLF;
		$texto_impresion .= alinea("VENTA POR PRODUCTOS EN TURNO", 2, 40) . $CRLF;
		$texto_impresion .= $CRLF;
		$texto_impresion .= "EDS: " . $acumula['info']['almacen'] . $CRLF;
		$texto_impresion .= "ANIO " . $acumula['info']['periodo'] . " MES " . $acumula['info']['mes'] . $CRLF;
		$texto_impresion .= "DEL " . $acumula['info']['desde'] . " AL " . $acumula['info']['hasta'] . $CRLF;
		$texto_impresion .= $CRLF;

		$diaturno = $acumula['header'];
		$resul = $acumula['body'];

		for ($k = 0; $k < count($diaturno); $k++) {
			$texto_impresion .= "DIA: " . $diaturno[$k]['dia'] . $CRLF . "TURNO: " . $diaturno[$k]['turno'] . $CRLF;
			$texto_impresion .= "----------------------------------------" . $CRLF;
	//		$texto_impresion .= "DESCRIPCION            CANTIDAD    TOTAL".$CRLF;
			$texto_impresion .= "DESCRIPCION                	     " . $CRLF;
			$texto_impresion .= "----------------------------------------" . $CRLF;

			for ($j = 0; $j < count($resul); $j++) {
				if(($diaturno[$k]['dia'] == $resul[$j]['dia']) and ($diaturno[$k]['turno'] == $resul[$j]['turno'])) {
					if($resul[$j]['producto'] != $resul[$j + 1]['producto']) {
						$texto_impresion .= alinea($resul[$j]['producto'], 0, 22) . $CRLF;
						$texto_impresion .= "CANTIDAD: " . alinea(showNumber(round($resul[$j]['cantidad'], 2)), 1, 9) . "  TOTAL: " . alinea(showNumber($resul[$j]['importe']), 1, 9) . $CRLF;
					}
				}
			}

			$texto_impresion .= "----------------------------------------" . $CRLF;
			$texto_impresion .= alinea("TOTAL", 0, 22) . alinea(showNumber(round($diaturno[$k]['tot_can'], 2)), 1, 9) . alinea(showNumber($diaturno[$k]['tot_imp']), 1, 9) . $CRLF . $CRLF;
		}

		$file = "/tmp/imprimir/acumula_linea_turno.txt";
		$fh = fopen($file, "a");
		fwrite($fh, $texto_impresion . PHP_EOL . PHP_EOL . PHP_EOL);
		fclose($fh);
	}
}

function alinea($str, $tipo, $ll) {
	if ($tipo == 0)
		return ($str . espaciosA(($ll - strlen($str))));
	else if ($tipo == 1)
		return (espaciosA(($ll - strlen($str))) . $str);
	return (espaciosA((($ll / 2) - (strlen($str) / 2))) . $str . espaciosA((($ll / 2) - (strlen($str) / 2))));
}

function showNumber($num) {
	return number_format(round($num, 2), 2, ".", "");
}

function espaciosA($q) {
	$ret = "";
	for ($q; $q > 0; $q--)
		$ret .= " ";
	return $ret;
}
