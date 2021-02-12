<?php

class CRUDItemAliasTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>Alias de productos</b></h2>';
    }
    
	function formPrincipal($arrAlmacenes, $iAlmacen, $dInicial, $dFinal, $dCierre, $arrLados, $arrLadosForm, $arrProductos, $iProducto, $sTipoVistaDetallado, $sTipoVistaResumido) {
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.ITEMALIAS'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dInicial', '', $dInicial, '', 12, 10));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dFinal', '', $dFinal, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="Add"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
    }
    
    function gridViewHTML($arrResult, $sTipoVista) {
		$result = '';
		$result .= '<table border="0" align="center" class="report_CRUD">';

		if( $sTipoVista == 'D') {
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">SURTIDOR</th>';
				$result .= '<th class="grid_cabecera">ALMACEN</th>';
				$result .= '<th class="grid_cabecera">F. EMISIÓN</th>';
				$result .= '<th class="grid_cabecera">LADO - MANGUERA</th>';
				$result .= '<th class="grid_cabecera">PRODUCTO</th>';
				$result .= '<th class="grid_cabecera">PRECIO</th>';
				$result .= '<th class="grid_cabecera">CONTOMETRO CANT. INICIAL</th>';
				$result .= '<th class="grid_cabecera">CONTOMETRO CANT. FINAL</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">CONTOMETRO IMP. INICIAL</th>';
				$result .= '<th class="grid_cabecera">CONTOMETRO IMP. FINAL</th>';
				$result .= '<th class="grid_cabecera">IMPORTE</th>';
				$result .= '<th class="grid_cabecera">AFERICIÓN</th>';
				$result .= '<th class="grid_cabecera">DESC. / INCRE.</th>';
				$result .= '<th class="grid_cabecera">TIPO</th>';
				$result .= '<th class="grid_cabecera">ESTADO</th>';
			$result .= '</tr>';

			$result .= '<tbody>';
			if($arrResult['estado'] == FALSE) {
				$result .= '<tr class="bgcolor">';
					$result .= '<td colspan="15" class="grid_detalle_par" align="center"><b>' . $arrResult['mensaje'] . '</b></td>';
				$result .= '</tr>';
			} else {
				$counter = 0;
				$validar_btn = FALSE;
				$cssStyleEstadoParte='';
				foreach ($arrResult['result'] as $row) {
					$cssStyleBackgroundEstadoParte = "";
					$cssStyleColorEstadoParte = "";
					$sNombreEstadoParte = "-";
					if ( (int)$row["nu_estado_parte"] > 1 ) {
						$cssStyleEstadoParte = "background-color:#ff3860;";
						$cssStyleColorEstadoParte = "color: #ffffff; font-weight: bold;";
						$sNombreEstadoParte = "<b>PARTE DUPLICADO</b>";
					}

					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr style="' . $cssStyleEstadoParte . '" class="'. $color. '">';
			    		$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="center">' . htmlentities($row["id_surtidor"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="center">' . htmlentities($row["nu_almacen"]) . ' ' . htmlentities($row["no_almacen"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="center">' . htmlentities($row["fe_emision"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="center">' . htmlentities($row["nu_lado"]) . ' - ' . htmlentities($row["nu_manguera"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="left">' . htmlentities($row["no_producto"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities($row["ss_precio"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities($row["nu_lectura_inicial_cantidad"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities($row["nu_lectura_final_cantidad"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities(number_format($row["ss_cantidad"], '3', '.', ',')) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities($row["nu_lectura_inicial_soles"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities($row["nu_lectura_final_soles"]) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities(number_format($row["ss_afericion_soles"], '2', '.', ',')) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="right">' . htmlentities(number_format($row["ss_descuentos_incrementos"], '2', '.', ',')) . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="center">' . htmlentities(( empty($row["nu_tipo_venta"]) ? 'AUTO' : $row["nu_tipo_venta"]))  . '</td>';
				    	$result .= '<td style="' . $cssStyleColorEstadoParte . '" align="center">' . $sNombreEstadoParte . '</td>';
				    $result .= '</tr>';
				    $counter++;
				}
			}
			$result .= '</tbody>';
		}

		if($arrResult['estado']) {//TRUE -> SQL se ejecuto satisfactoriamente
	        $dOrderByCodeItem = array();
	        foreach ($arrResult["result"] as $key => $row) {
				$dOrderByCodeItem[$key] = trim($row["id_producto"]);
	        }
			array_multisort($dOrderByCodeItem, SORT_ASC, $arrResult["result"]);
        	
        	$iIDProducto = '';
        	$sNombreProducto = '';
			$arrResumentTotal = array();

			//Total x contometros
			$fTotalCantidadContometros = 0.00;
			$fTotalSolesContometros = 0.00;
			//Total afericion
			$fTotalCantidadAfericion = 0.00;
			$fTotalSolesAfericion = 0.00;
			//Total Descuento / Incremento Nota de despacho
			$fTotalSolesNotaDespacho = 0.00;

			$counter=0;
			foreach ($arrResult['result'] as $row) {
				if ( $iIDProducto != $row["id_producto"] ) {
					if ($counter!=0) {
						$_arrResumentTotal = array(
							'iIDProducto' => $iIDProducto,
							'sNombreProducto' => $sNombreProducto,
							'fTotalCantidadContometros' => $fTotalCantidadContometros,
							'fTotalSolesContometros' => $fTotalSolesContometros,
							'fTotalCantidadAfericion' => $fTotalCantidadAfericion,
							'fTotalSolesAfericion' => $fTotalSolesAfericion,
							'fTotalSolesNotaDespacho' => $fTotalSolesNotaDespacho,
						);
						$arrResumentTotal[] = $_arrResumentTotal;
						$fTotalCantidadContometros = 0.00;
						$fTotalSolesContometros = 0.00;
						$fTotalCantidadAfericion = 0.00;
						$fTotalSolesAfericion = 0.00;
						$fTotalSolesNotaDespacho = 0.00;
					}
					$iIDProducto = $row["id_producto"];
					$sNombreProducto = $row["no_producto"];
				}
				$counter++;
				$fTotalCantidadContometros += (float)$row["ss_cantidad"];
				$fTotalSolesContometros += (float)$row["ss_total"];

				$fTotalCantidadAfericion += (float)$row["ss_afericion_cantidad"];
				$fTotalSolesAfericion += (float)$row["ss_afericion_soles"];

				$fTotalSolesNotaDespacho += (float)$row["ss_descuentos_incrementos"];
			}
			$_arrResumentTotal = array(
				'iIDProducto' => $iIDProducto,
				'sNombreProducto' => $sNombreProducto,
				'fTotalCantidadContometros' => $fTotalCantidadContometros,
				'fTotalSolesContometros' => $fTotalSolesContometros,
				'fTotalCantidadAfericion' => $fTotalCantidadAfericion,
				'fTotalSolesAfericion' => $fTotalSolesAfericion,
				'fTotalSolesNotaDespacho' => $fTotalSolesNotaDespacho,
			);
			$arrResumentTotal[] = $_arrResumentTotal;

			$result .= '<tr><td></td></tr>';
			$result .= '<tr><td></td></tr>';
			$result .= '<tr><td></td></tr>';
			$result .= '<tr><td></td></tr>';

			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th colspan="4" style="background-color: white;"></th>';
				$result .= '<th row="2" class="grid_cabecera"></th>';
				$result .= '<th row="2" colspan="2" class="grid_cabecera">RESUMEN VENTA</th>';
				$result .= '<th row="2" colspan="2" class="grid_cabecera">RESUMEN AFERICIÓN</th>';
				$result .= '<th class="grid_cabecera">RESUMEN DESC. / INCRE.</th>';
				$result .= '<th row="2" colspan="2" class="grid_cabecera">RESUMEN NETO</th>';
	    	$result .= '</tr>';

			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th colspan="4" style="background-color: white;"></th>';
				$result .= '<th class="grid_cabecera">PRODUCTO</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
	    	$result .= '</tr>';

			$result .= '<tbody>';
				$sumcan = 0.00;
				$sumtot = 0.00;
				$sumcanafe = 0.00;
				$sumtotafe = 0.00;
				$sumtotdesincre = 0.00;
				$sumcanneto = 0.00;
				$sumtotneto = 0.00;
		    	foreach ($arrResumentTotal as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
						$result .= '<td colspan="4" style="background-color: white;"></td>';
				    	$result .= '<td align="left">' . htmlentities($row["iIDProducto"]) . ' ' . htmlentities($row["sNombreProducto"]) . '</td>';
				    	$result .= '<td align="right">' . htmlentities(number_format($row["fTotalCantidadContometros"], '3', '.', ',')) . '</td>';
				    	$result .= '<td align="right">' . htmlentities(number_format($row["fTotalSolesContometros"], '2', '.', ',')) . '</td>';
				    	$result .= '<td align="right">' . htmlentities(number_format($row["fTotalCantidadAfericion"], '3', '.', ',')) . '</td>';
				    	$result .= '<td align="right">' . htmlentities(number_format($row["fTotalSolesAfericion"], '3', '.', ',')) . '</td>';
				    	$result .= '<td align="right">' . htmlentities(number_format($row["fTotalSolesNotaDespacho"], '3', '.', ',')) . '</td>';
				    	$result .= '<td align="right">' . htmlentities(number_format(($row["fTotalCantidadContometros"] - $row["fTotalCantidadAfericion"]), '3', '.', ',')) . '</td>';
				    	$result .= '<td align="right">' . htmlentities(number_format((($row["fTotalSolesContometros"] - $row["fTotalSolesAfericion"]) - $row["fTotalSolesNotaDespacho"]), '3', '.', ',')) . '</td>';

				    	$sumcan += $row["fTotalCantidadContometros"];
				    	$sumtot += $row["fTotalSolesContometros"];
				    	$sumcanafe += $row["fTotalCantidadAfericion"];
				    	$sumtotafe += $row["fTotalSolesAfericion"];
				    	$sumtotdesincre += $row["fTotalSolesNotaDespacho"];
				    	$sumcanneto += ($row["fTotalCantidadContometros"] - $row["fTotalCantidadAfericion"]);
				    	$sumtotneto += (($row["fTotalSolesContometros"] - $row["fTotalSolesAfericion"]) - $row["fTotalSolesNotaDespacho"]);

				    $result .= '</tr>';
				    $counter++;
				}

				$result .= '<tr bgcolor="#FFFFCD">';
					$result .= '<th colspan="4" style="background-color: white;"></th>';
					$result .= '<td class="bgcolor_total" align="right"><strong>TOTAL: </font></strong></td>';
					$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumcan, '3', '.', ',')) . '</strong></td>';
					$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumtot, '2', '.', ',')) . '</strong></td>';
					$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumcanafe, '3', '.', ',')) . '</strong></td>';
					$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumtotafe, '3', '.', ',')) . '</strong></td>';
					$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumtotdesincre, '3', '.', ',')) . '</strong></td>';
					$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumcanneto, '3', '.', ',')) . '</strong></td>';
					$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumtotneto, '3', '.', ',')) . '</strong></td>';
		    	$result .= '</tr>';
			$result .= '<tbody>';
		}
		$result .= '</table>';
		return $result;
    }
	
	function gridViewExcel($arrResult, $sTipoVista) {
		$chrFileName = "";

		$workbook = new Workbook($chrFileName);

		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato_string_sborder =& $workbook->add_format();

		$formato_string_left =& $workbook->add_format();
		$formato_string =& $workbook->add_format();
		$formato_numero =& $workbook->add_format();
		$resumen_formato =& $workbook->add_format();
		$formato_special =& $workbook->add_format();
		$formato_total =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		$formato_string_sborder->set_size(10);
		$formato_string_sborder->set_bold(1);
		$formato_string_sborder->set_align('center');

		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_bottom(1);
		$formato2->set_bottom_color(8);
		$formato2->set_top(1);
		$formato2->set_top_color(8);
		$formato2->set_right(1);
		$formato2->set_right_color(8);
		$formato2->set_left(1);
		$formato2->set_left_color(8);
		$formato2->set_align('center');

		$formato_special->set_size(10);
		$formato_special->set_bold(1);

		$formato_total->set_size(9);
		$formato_total->set_bold(1);
		$formato_total->set_align('right');

		$formato_string_left->set_size(10);
		$formato_string_left->set_align('left');

		$formato_string->set_size(10);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_align('right');

		$worksheet1 =& $workbook->add_worksheet('Venta Combustible por Lados');
		$worksheet1->set_column(0, 0, 15);
		$worksheet1->set_column(1, 1, 15);
		$worksheet1->set_column(2, 2, 20);
		$worksheet1->set_column(3, 3, 15);
		$worksheet1->set_column(4, 4, 20);//PRODUCTO
		$worksheet1->set_column(5, 5, 15);
		$worksheet1->set_column(6, 6, 30);
		$worksheet1->set_column(7, 7, 30);
		$worksheet1->set_column(8, 8, 15);
		$worksheet1->set_column(9, 9, 30);
		$worksheet1->set_column(10, 10, 30);
		$worksheet1->set_column(11, 11, 15);
		$worksheet1->set_column(12, 12, 10);
		$worksheet1->set_column(13, 13, 15);
		$worksheet1->set_column(14, 14, 15);

		$fila = 0;
		$worksheet1->write_string($fila, 6, "Venta Combustible por Lados", $formato0);

		$fila = 3;
		if( $sTipoVista == 'D') {//Detallada
			$worksheet1->write_string($fila, 0, "ALMACEN", $formato2);
			$worksheet1->write_string($fila, 1, "F. EMISION", $formato2);
			$worksheet1->write_string($fila, 2, "LADO", $formato2);
			$worksheet1->write_string($fila, 3, "MANGUERA", $formato2);
			$worksheet1->write_string($fila, 4, "PRODUCTO", $formato2);
			$worksheet1->write_string($fila, 5, "PRECIO", $formato2);
			$worksheet1->write_string($fila, 6, "CONTOMETRO CANT. INICIAL", $formato2);
			$worksheet1->write_string($fila, 7, "CONTOMETRO CANT. FINAL", $formato2);
			$worksheet1->write_string($fila, 8, "CANTIDAD", $formato2);
			$worksheet1->write_string($fila, 9, "CONTOMETRO IMP. INICIAL", $formato2);
			$worksheet1->write_string($fila, 10, "CONTOMETRO IMP. FINAL", $formato2);
			$worksheet1->write_string($fila, 11, "IMPORTE", $formato2);
			$worksheet1->write_string($fila, 12, "AFERICION", $formato2);
			$worksheet1->write_string($fila, 13, "DESC. / INCRE.", $formato2);
			$worksheet1->write_string($fila, 14, "TIPO", $formato2);

			$fila++;
			if($arrResult['estado'] == FALSE) {
				$worksheet1->write_string($fila, 7, $arrResult['mensaje'], $formato0);
			} else {
				foreach ($arrResult['result'] as $row) {
					$worksheet1->write_string($fila, 0, $row["nu_almacen"] . ' ' . $row["no_almacen"], $formato_string);
					$worksheet1->write_string($fila, 1, $row["fe_emision"], $formato_string);
					$worksheet1->write_string($fila, 2, $row["nu_lado"], $formato_string);
					$worksheet1->write_string($fila, 3, $row["nu_manguera"], $formato_string);
					$worksheet1->write_string($fila, 4, $row["no_producto"], $formato_string_left);
					$worksheet1->write_number($fila, 5, $row["ss_precio"], $formato_numero);
					$worksheet1->write_number($fila, 6, $row["nu_lectura_inicial_cantidad"], $formato_numero);
					$worksheet1->write_number($fila, 7, $row["nu_lectura_final_cantidad"], $formato_numero);
					$worksheet1->write_number($fila, 8, $row["ss_cantidad"], $formato_numero);
					$worksheet1->write_number($fila, 9, $row["nu_lectura_inicial_soles"], $formato_numero);
					$worksheet1->write_number($fila, 10, $row["nu_lectura_final_soles"], $formato_numero);
					$worksheet1->write_number($fila, 11, $row["ss_total"], $formato_numero);
					$worksheet1->write_number($fila, 12, $row["ss_afericion_soles"], $formato_numero);
					$worksheet1->write_number($fila, 13, $row["ss_descuentos_incrementos"], $formato_numero);
					$worksheet1->write_string($fila, 14, (empty($row["nu_tipo_venta"]) ? 'AUTO' : $row["nu_tipo_venta"]), $formato_string);
					$fila++;
				}
			}
		}

		if($arrResult['estado']) {
	        $dOrderByCodeItem = array();
	        foreach ($arrResult["result"] as $key => $row) {
				$dOrderByCodeItem[$key] = trim($row["id_producto"]);
	        }
			array_multisort($dOrderByCodeItem, SORT_ASC, $arrResult["result"]);
        	
        	$iIDProducto = '';
        	$sNombreProducto = '';
			$arrResumentTotal = array();

			//Total x contometros
			$fTotalCantidadContometros = 0.00;
			$fTotalSolesContometros = 0.00;
			//Total afericion
			$fTotalCantidadAfericion = 0.00;
			$fTotalSolesAfericion = 0.00;
			//Total Descuento / Incremento Nota de despacho
			$fTotalSolesNotaDespacho = 0.00;

			$counter=0;
			foreach ($arrResult['result'] as $row) {
				if ( $iIDProducto != $row["id_producto"] ) {
					if ($counter!=0) {
						$_arrResumentTotal = array(
							'iIDProducto' => $iIDProducto,
							'sNombreProducto' => $sNombreProducto,
							'fTotalCantidadContometros' => $fTotalCantidadContometros,
							'fTotalSolesContometros' => $fTotalSolesContometros,
							'fTotalCantidadAfericion' => $fTotalCantidadAfericion,
							'fTotalSolesAfericion' => $fTotalSolesAfericion,
							'fTotalSolesNotaDespacho' => $fTotalSolesNotaDespacho,
						);
						$arrResumentTotal[] = $_arrResumentTotal;
						$fTotalCantidadContometros = 0.00;
						$fTotalSolesContometros = 0.00;
						$fTotalCantidadAfericion = 0.00;
						$fTotalSolesAfericion = 0.00;
						$fTotalSolesNotaDespacho = 0.00;
					}
					$iIDProducto = $row["id_producto"];
					$sNombreProducto = $row["no_producto"];
				}
				$counter++;
				$fTotalCantidadContometros += (float)$row["ss_cantidad"];
				$fTotalSolesContometros += (float)$row["ss_total"];

				$fTotalCantidadAfericion += (float)$row["ss_afericion_cantidad"];
				$fTotalSolesAfericion += (float)$row["ss_afericion_soles"];

				$fTotalSolesNotaDespacho += (float)$row["ss_descuentos_incrementos"];
			}
			$_arrResumentTotal = array(
				'iIDProducto' => $iIDProducto,
				'sNombreProducto' => $sNombreProducto,
				'fTotalCantidadContometros' => $fTotalCantidadContometros,
				'fTotalSolesContometros' => $fTotalSolesContometros,
				'fTotalCantidadAfericion' => $fTotalCantidadAfericion,
				'fTotalSolesAfericion' => $fTotalSolesAfericion,
				'fTotalSolesNotaDespacho' => $fTotalSolesNotaDespacho,
			);
			$arrResumentTotal[] = $_arrResumentTotal;		

			$fila++;
			$worksheet1->write_string($fila, 5, "RESUMEN", $formato2);
			$worksheet1->write_string($fila, 6, "VENTA", $formato2);
			$worksheet1->write_string($fila, 7, "RESUMEN", $formato2);
			$worksheet1->write_string($fila, 8, "AFERICION", $formato2);
			$worksheet1->write_string($fila, 9, "RESUMEN DESC. / INCRE.", $formato2);
			$worksheet1->write_string($fila, 10, "RESUMEN", $formato2);
			$worksheet1->write_string($fila, 11, "NETO", $formato2);

			$fila++;
			$worksheet1->write_string($fila, 4, "PRODUCTO", $formato2);
			$worksheet1->write_string($fila, 5, "CANTIDAD", $formato2);
			$worksheet1->write_string($fila, 6, "SOLES", $formato2);
			$worksheet1->write_string($fila, 7, "CANTIDAD", $formato2);
			$worksheet1->write_string($fila, 8, "SOLES", $formato2);
			$worksheet1->write_string($fila, 9, "SOLES", $formato2);
			$worksheet1->write_string($fila, 10, "CANTIDAD", $formato2);
			$worksheet1->write_string($fila, 11, "SOLES", $formato2);

			$fila++;
			$sumcan = 0.00;
			$sumtot = 0.00;
			$sumcanafe = 0.00;
			$sumtotafe = 0.00;
			$sumtotdesincre = 0.00;
			$sumcanneto = 0.00;
			$sumtotneto = 0.00;
			foreach ($arrResumentTotal as $row) {
				$worksheet1->write_string($fila, 4, $row["iIDProducto"] . ' ' . $row["no_producto"], $formato_string_left);
				$worksheet1->write_number($fila, 5, $row["fTotalCantidadContometros"], $formato_numero);
				$worksheet1->write_number($fila, 6, $row["fTotalSolesContometros"], $formato_numero);
				$worksheet1->write_number($fila, 7, $row["fTotalCantidadAfericion"], $formato_numero);
				$worksheet1->write_number($fila, 8, $row["fTotalSolesAfericion"], $formato_numero);
				$worksheet1->write_number($fila, 9, $row["fTotalSolesNotaDespacho"], $formato_numero);
				$worksheet1->write_number($fila, 10, ($row["fTotalCantidadContometros"] - $row["fTotalCantidadAfericion"]), $formato_numero);
				$worksheet1->write_number($fila, 11, (($row["fTotalSolesContometros"] - $row["fTotalSolesAfericion"]) - $row["fTotalSolesNotaDespacho"]), $formato_numero);

		    	$sumcan += $row["fTotalCantidadContometros"];
		    	$sumtot += $row["fTotalSolesContometros"];
		    	$sumcanafe += $row["fTotalCantidadAfericion"];
		    	$sumtotafe += $row["fTotalSolesAfericion"];
		    	$sumtotdesincre += $row["fTotalSolesNotaDespacho"];
		    	$sumcanneto += ($row["fTotalCantidadContometros"] - $row["fTotalCantidadAfericion"]);
		    	$sumtotneto += (($row["fTotalSolesContometros"] - $row["fTotalSolesAfericion"]) - $row["fTotalSolesNotaDespacho"]);
				$fila++;
			}

			$worksheet1->write_string($fila, 4, "TOTAL", $formato_total);
			$worksheet1->write_number($fila, 5, number_format($sumcan, 3, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 6, number_format($sumtot, 2, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 7, number_format($sumcanafe, 3, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 8, number_format($sumtotafe, 3, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 9, number_format($sumtotdesincre, 3, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 10, number_format($sumcanneto, 3, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 11, number_format($sumtotneto, 3, '.', ''), $formato_total);
		}// Total Resumen

		$workbook->close();	

		$chrFileName = "VentaCombustiblexLato";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}
