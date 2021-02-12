<?php

class ConsumoValesDiasTemplate extends Template{

    function search_form(){

		$ayer = time()-(24*60*60);
		$fecha = date("d/m/Y", $ayer);

		$estaciones = ConsumoValesDiasModel::obtieneListaEstaciones();
		$estaciones['TODAS'] = "Todas las estaciones";

		$producto = ConsumoValesDiasModel::obtieneProductos();
		$producto['TODOS'] = "Todos los Productos";

		$form = new form2("Consumo de Vales en Galones por Dia", "form_vales_xdias", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.VALESXDIAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_vales_xdias.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_vales_xdias.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "TODAS", $estaciones, espacios(6)));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("producto", "Producto:", "TODOS", $producto, espacios(3)));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("cliente","Cliente", "", "", 20, 12));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Reporte"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Reporte</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
    }
    
    function reporte($results, $desde, $hasta, $estacion, $producto, $cliente) {
		$result = '<table border="1" align="center">';
		$result .= '<tr>';

		foreach($results['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
			$result .= '<tr>';
			$result .= '<td colspan="37"><h3>' . $ch_almacen . '</h3></td>';
			$result .= '</tr>';
		    }
		}

		$result .= '<tr>';
		$result .= '<td colspan="2"><h3>MES: ' . substr($desde,3,8) . ' &nbsp;&nbsp;&nbsp;</h3></td> <td colspan="3"><h3>PRODUCTO: ' . $producto .'&nbsp;&nbsp;&nbsp;</h3></td>';
		$result .= '</tr>';
		$result .= '<td>UNIDAD</td>';
		$result .= '<td>SUB UNI</td>';
		$result .= '<td>PLACA</td>';
		$result .= '<td>GAL. A</td>';

		for($i=1;$i<32;$i++)
			$result .= '<td align="center">'.substr('0'.$i, -2).'</td>';

		$result .= '<td align="center">CONSUMIDO</td>';
		$result .= '<td align="center">SALDO</td>';
		$result .= '</tr><tr>';
		$result .= '</tr>';
		$numfilas = 0;

		foreach($results['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				foreach($venta['partes'] as $dt_fecha=>$dia) {
                    $numfilas= $numfilas +1;
					$result .= ConsumoValesDiasTemplate::imprimirLinea2($dia, $dia['unidad'], $dia['subunidad'], $dia['placa'], $dia['asignado'] );
				}
		    	$result .= '<tr><td colspan="37"><h3>TOTAL GENERAL</h3></td></tr>';
		    }
		}

		$result .= ConsumoValesDiasTemplate::imprimirLinea($results['totales'], $results['totales']['asignado'], $results['totales']['contador']);
		$result .= '</table>';
		
		return $result;
    }
    
    function imprimirLinea2($array, $label, $label2, $label3, $label4){
		$result  = '<tr>';
		$result .= '<td>' . htmlentities($label) . '</td>';
		$result .= '<td>' . htmlentities($label2) . '</td>';
		$result .= '<td>' . htmlentities($label3) . '</td>';
		$result .= '<td>' . htmlentities($label4) . '</td>';
	    $decimal = 0;
		
		for($i=1;$i<32;$i++)
			$result .= '<td align="right">' . htmlentities(number_format($array[$i], 3, '.', ',')) . '</td>';

		$result .= '<td align="right">' . htmlentities(number_format($array['total'], 3, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($array['saldo'], 3, '.', ',')) . '</td>';
		$result .= '</tr>';
		return $result;
	}

	function imprimirLinea($array, $label, $label2){
		$result  = '<tr>';
		$result .= '<td colspan="3">Cantidad de Vehiculos: ' . htmlentities($label2) . ' </td>';
		$result .= '<td colspan="1">' . htmlentities($label) . '</td>';
	    $decimal = 0;

		for($i=1;$i<32;$i++)
			$result .= '<td align="right">' . htmlentities(number_format($array[$i], 3, '.', ',')) . '</td>';

		$result .= '<td align="right">' . htmlentities(number_format($array['total'], 3, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($array['totalfinal'], 3, '.', ',')) . '</td>';
		$result .= '</tr>';
		return $result;
    }

	function reportExcel($results, $almacenes, $desde, $hasta) {

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados Varillaje');
		$worksheet1->set_column(0, 0, 30);
		$worksheet1->set_column(1, 1, 50);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "CONSUMO DE VALES EN GALONES POR DIA", $formato0);
		$worksheet1->write_string(2, 0, " ",$formato0);

		$fila = 3;
		foreach($results['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				$worksheet1->write_string($fila, 0, $ch_almacen, $formato2);
		    }
		}

		$fila = 4;
		$worksheet1->write_string($fila, 0, "MES: " . substr($desde,3,8), $formato2);

		$result .= '<tr>';
		$result .= '<td colspan="2"><h3>MES: ' . substr($desde,3,8) . ' &nbsp;&nbsp;&nbsp;</h3></td> <td colspan="3"><h3>PRODUCTO: ' . $producto .'&nbsp;&nbsp;&nbsp;</h3></td>';
		$result .= '</tr>';

		$fila = 5;
		$worksheet1->write_string($fila, 0, "UNIDAD", $formato2);
		$worksheet1->write_string($fila, 1, "SUB UNIDAD", $formato2);
		$worksheet1->write_string($fila, 2, "PLACA", $formato2);
		$worksheet1->write_string($fila, 3, "GAL. A", $formato2);

		$columna = 4;
		for($i=1;$i<32;$i++){
			$worksheet1->write_string($fila, $columna, substr('0'.$i, -2), $formato2);
			$columna++;
		}

		$worksheet1->write_string($fila, $columna++, "CONSUMIDO", $formato2);
		$worksheet1->write_string($fila, $columna++, "SALDO", $formato2);

		$fila 			= 6;
		$new_colummna 	= 4;
		$_new_columna 	= 0;
		$last_fila 		= 0;

		foreach($results['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				foreach($venta['partes'] as $dt_fecha=>$dia) {
					$worksheet1->write_string($fila, 0, $dia['unidad'], $formato2);
					$worksheet1->write_string($fila, 1, $dia['subunidad'], $formato2);
					$worksheet1->write_string($fila, 2, $dia['placa'], $formato2);
					$worksheet1->write_string($fila, 3, (empty($dia['asignado']) ? '0.00' : $dia['asignado']), $formato2);
					for($i=1;$i<32;$i++){
						$worksheet1->write_string($fila, $new_colummna, (empty($dia[$i]) ? '0.00' : $dia[$i]), $formato2);
						$new_colummna++;
						$_new_columna = $new_colummna;
						$worksheet1->write_string($fila, $_new_columna++, $dia['total'], $formato2);
						$worksheet1->write_string($fila, $_new_columna++, $dia['saldo'], $formato2);
					}
					$new_colummna=4;
					$fila++;
					$last_fila = $fila;
				}
		    }
		}

		$fila = $last_fila++;
		$worksheet1->write_string($fila, 0, "TOTAL GENERAL", $formato2);

		$fila = $fila++;
		$worksheet1->write_string($fila, 0, "CANTIDAD DE VEHICULOS: " . $results['totales']['contador'], $formato2);
		$worksheet1->write_string($fila, 3, $results['totales']['asignado'], $formato2);

		$columna = 4;
		$_columna = 0;

		for($i=1;$i<32;$i++){
			if ($results['totales'][$i] != 0){
			$worksheet1->write_string($fila, $columna, $results['totales'][$i], $formato2);
			}else{
			$worksheet1->write_string($fila, $columna, 0.00, $formato2);
			}
			$columna++;
			$_columna = $columna;
			$worksheet1->write_string($fila, $_columna++, $results['totales']['total'], $formato2);
			$worksheet1->write_string($fila, $_columna++, $results['totales']['totalfinal'], $formato2);
		}
		
		$workbook->close();	

		$chrFileName = "Consumo_Galones_X_Dia_Mes_" . substr($desde,3,8);
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename= " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}

