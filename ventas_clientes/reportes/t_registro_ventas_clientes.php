<?php

class RegistroVentasClientesTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>Registro de Ventas Clientes</b></h2>';
    }
    
	function formPrincipal($arrAlmacenes, $iAlmacen, $dInicial, $dFinal, $iTipoVenta, $iFormaPago, $iDocumentoIdentidad, $sRazSocial, $dCierre, $sTipoVistaDetallado, $sTipoVistaResumido) {

		$arrTipoVenta = array("T" => "Todos", "C" => "Combustible", "M" => "Market");
		$arrFormaPago = array("T" => "Todos", "N" => "Contado", "S" => "Credito");

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.REGITROVENTASCLIENTES'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dCierre . '">'));

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
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Tipo Venta: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		    	    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iTipoVenta', '', $iTipoVenta, $arrTipoVenta, espacios(3), ''));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Forma Pago: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		    	    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iFormaPago', '', $iFormaPago, $arrFormaPago, espacios(3), ''));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Cliente: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		                <input type="hidden" id="txt-Nu_Documento_Identidad" name="Nu_Documento_Identidad" value="' . $iDocumentoIdentidad . '" />
			        	<input type="text" maxlength="50" size="50" id="txt-No_Razsocial" name="No_Razsocial" autocomplete="off" placeholder="Ingresar Código ó Nombre" value="' . $sRazSocial . '" />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Tipo Vista: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		    	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="radio-iTipoVista" value="D" ' . $sTipoVistaDetallado . '>Detallado'));
		    	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="radio-iTipoVista" value="R" ' . $sTipoVistaResumido . '>Resumido'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("cbo-iAlmacen").focus();}</script>'));
		return $form->getForm();
    }
    
    function gridViewHTML($arrData, $sTipoVista) {
		$result = '';

		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">FECHA</th>';
				$result .= '<th class="grid_cabecera">TIPO</th>';
				$result .= '<th class="grid_cabecera">SERIE</th>';
				$result .= '<th class="grid_cabecera">NUMERO</th>';
				$result .= '<th class="grid_cabecera">RUC</th>';
				$result .= '<th class="grid_cabecera">RAZON SOCIAL</th>';
				$result .= '<th class="grid_cabecera">CODIGO</th>';
				$result .= '<th class="grid_cabecera">NOMBRE PRODUCTO</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">PRECIO</th>';
				$result .= '<th class="grid_cabecera">VALOR BRUTO</th>';
				$result .= '<th class="grid_cabecera">DESCUENTO</th>';
				$result .= '<th class="grid_cabecera">VALOR VENTA</th>';
				$result .= '<th class="grid_cabecera">IGV</th>';
				$result .= '<th class="grid_cabecera">TOTAL</th>';
				$result .= '<th class="grid_cabecera">T.C.</th>';
				$result .= '<th class="grid_cabecera"># LIQUIDACION</th>';
			$result .= '</tr>';

			if(count($arrData) === 0) {
				$result .= '<tr class="bgcolor">';
					$result .= '<td class="grid_detalle_par" align ="center" colspan="16" style="font-size: 10.5px;"><STRONG>No hay registros</STRONG></td>';
				$result .= '</tr>';
			} else {
				$a=0;
				$dSumCantidad = 0.0000;
				$dSumVB = 0.0000;
				$dSumDscto = 0.00;
				$dSumVV = 0.0000;
				$dSumIGV = 0.0000;
				$dSumTotal = 0.00;
				$counter = 0;
				$sIdDocumento = "";
				$result .= '<tbody>';
				foreach ($arrData as $rows) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_par';
					if($sIdDocumento != $rows["tipo_documento"] . $rows["serie_documento"] . $rows["numero_documento"]){
						if($counter != 0){
					    	$result .= '<tr class="grid_detalle_especial">';
					    		$result .= '<td align ="right" colspan="8">TOTAL: </td>';
						    	$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad, '4', '.', ',')) . '</td>';
						    	$result .= '<td align ="right">' . htmlentities(number_format(($dSumTotal / $dSumCantidad), '4', '.', ',')) . '</td>';
						    	$result .= '<td align ="right">' . htmlentities(number_format($dSumVB, '4', '.', ',')) . '</td>';
						    	$result .= '<td align ="right">' . htmlentities(number_format($dSumDscto, '2', '.', ',')) . '</td>';
						    	$result .= '<td align ="right">' . htmlentities(number_format($dSumVV, '4', '.', ',')) . '</td>';
						    	$result .= '<td align ="right">' . htmlentities(number_format($dSumIGV, '4', '.', ',')) . '</td>';
						    	$result .= '<td align ="right">' . htmlentities(number_format($dSumTotal, '2', '.', ',')) . '</td>';
					    		$result .= '<td align ="right" colspan="2"></td>';
						    $result .= '</tr>';
							$dSumCantidad = 0.0000;
							$dSumVB = 0.0000;
							$dSumDscto = 0.00;
							$dSumVV = 0.0000;
							$dSumIGV = 0.0000;
							$dSumTotal = 0.00;
						}

				    	$result .= '<tr class="grid_detalle_impar">';
					    	$result .= '<td align ="center">' . htmlentities($rows["fe_emision"]) . '</td>';
					    	$result .= '<td align ="center">' . htmlentities($rows["tipo_documento"]) . '</td>';
					    	$result .= '<td align ="center">' . htmlentities($rows["serie_documento"]) . '</td>';
					    	$result .= '<td align ="center">' . htmlentities($rows["numero_documento"]) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities($rows["nu_documento_identidad"]) . '</td>';
					    	$result .= '<td align ="left" colspan="11">' . htmlentities($rows["no_razsocial"]) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities($rows["nu_liquidacion_vales"]) . '</td>';
					    $result .= '</tr>';
					    $sIdDocumento = $rows["tipo_documento"] . $rows["serie_documento"] . $rows["numero_documento"];
					}
					
					if ($sTipoVista == "D"){
						$result .= '<tr class="'. $color. '">';
						    $result .= '<td align ="center" colspan="6"></td>';
					    	$result .= '<td align ="right">' . htmlentities($rows["nu_codigo_producto"]) . '</td>';
					    	$result .= '<td align ="left">' . htmlentities($rows["no_producto"]) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities(number_format($rows["ss_cantidad"], '4', '.', ',')) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities(number_format($rows["ss_precio"], '4', '.', ',')) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities(number_format($rows["ss_valor_bruto"], '4', '.', ',')) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities(number_format($rows["ss_descuento"], '2', '.', ',')) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities(number_format($rows["ss_valor_venta"], '4', '.', ',')) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities(number_format($rows["ss_igv"], '4', '.', ',')) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities(number_format($rows["ss_total"], '2', '.', ',')) . '</td>';
					    	$result .= '<td align ="right">' . htmlentities(number_format($rows["ss_tipo_cambio"], '3', '.', ',')) . '</td>';
					    	$result .= '<td align ="left"></td>';
		    			$result .= '</tr>';
		    		}
					$dSumCantidad+=$rows["ss_cantidad"];
					$dSumVB+=$rows["ss_valor_bruto"];
					$dSumDscto+=$rows["ss_descuento"];
					$dSumVV+=$rows["ss_valor_venta"];
					$dSumIGV+=$rows["ss_igv"];
					$dSumTotal+=$rows["ss_total"];
			    	$counter++;
				}
			    	$result .= '<tr class="grid_detalle_especial">';
			    		$result .= '<td align ="right" colspan="8">TOTAL: </td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad, '4', '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format(($dSumTotal / $dSumCantidad), '4', '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($dSumVB, '4', '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($dSumDscto, '4', '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($dSumVV, '4', '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($dSumIGV, '4', '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($dSumTotal, '2', '.', ',')) . '</td>';
				    	$result .= '<td align ="right" colspan="2"></td>';
				    $result .= '</tr>';
				$result .= '</tbody>';
			}
		$result .= '</table>';
		return $result;
    }
	
	function gridViewExcel($arrData, $sTipoVista) {
		$chrFileName = "";

		$workbook = new Workbook($chrFileName);

		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato_string =& $workbook->add_format();
		$formato_numero =& $workbook->add_format();
		$resumen_formato =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

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

		$formato5->set_size(11);
		$formato5->set_align('left');

		$formato_string->set_size(10);
		$formato_string->set_bottom(1);
		$formato_string->set_bottom_color(8);
		$formato_string->set_top(1);
		$formato_string->set_top_color(8);
		$formato_string->set_right(1);
		$formato_string->set_right_color(8);
		$formato_string->set_left(1);
		$formato_string->set_left_color(8);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_bottom(1);
		$formato_numero->set_bottom_color(8);
		$formato_numero->set_top(1);
		$formato_numero->set_top_color(8);
		$formato_numero->set_right(1);
		$formato_numero->set_right_color(8);
		$formato_numero->set_left(1);
		$formato_numero->set_left_color(8);//Border
		$formato_numero->set_align('right');

		$worksheet1 =& $workbook->add_worksheet('Registro Ventas Clientes');
		$worksheet1->set_column(0, 0, 12);
		$worksheet1->set_column(1, 1, 25);
		$worksheet1->set_column(2, 2, 6);
		$worksheet1->set_column(3, 3, 10);
		$worksheet1->set_column(4, 4, 20);
		$worksheet1->set_column(5, 5, 40);//Razon Social
		$worksheet1->set_column(6, 6, 20);//Codigo producto
		$worksheet1->set_column(7, 7, 30);//Nombre producto
		$worksheet1->set_column(8, 8, 15);//Cantidad
		$worksheet1->set_column(9, 9, 10);
		$worksheet1->set_column(10, 10, 15);
		$worksheet1->set_column(11, 11, 15);
		$worksheet1->set_column(12, 12, 15);
		$worksheet1->set_column(13, 13, 6);
		$worksheet1->set_column(14, 14, 23);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 5, "REGISTRO DE VENTAS CLIENTES", $formato0);

		$fila = 3;
		$worksheet1->write_string($fila, 0, "FECHA", $formato2);
		$worksheet1->write_string($fila, 1, "TIPO", $formato2);
		$worksheet1->write_string($fila, 2, "SERIE", $formato2);
		$worksheet1->write_string($fila, 3, "NUMERO", $formato2);
		$worksheet1->write_string($fila, 4, "RUC", $formato2);
		$worksheet1->write_string($fila, 5, "RAZON SOCIAL", $formato2);
		$worksheet1->write_string($fila, 6, "CODIGO", $formato2);
		$worksheet1->write_string($fila, 7, "NOMBRE PRODUCTO", $formato2);
		$worksheet1->write_string($fila, 8, "CANTIDAD", $formato2);
		$worksheet1->write_string($fila, 9, "PRECIO", $formato2);
		$worksheet1->write_string($fila, 10, "VALOR BRUTO", $formato2);
		$worksheet1->write_string($fila, 11, "DESCUENTO", $formato2);
		$worksheet1->write_string($fila, 12, "VALOR VENTA", $formato2);
		$worksheet1->write_string($fila, 13, "IGV", $formato2);
		$worksheet1->write_string($fila, 14, "TOTAL", $formato2);
		$worksheet1->write_string($fila, 15, "T.C.", $formato2);
		$worksheet1->write_string($fila, 16, "LIQUIDACION", $formato2);

		$fila++;
		if(count($arrData) === 0) {
			$worksheet1->write_string($fila, 5, "No hay registros", $formato0);
		} else {
			$counter = 0;
			$dSumCantidad = 0.0000;
			$dSumVB = 0.0000;
			$dSumDscto = 0.00;
			$dSumVV = 0.0000;
			$dSumIGV = 0.0000;
			$dSumTotal = 0.00;
			$sIdDocumento = "";
			foreach ($arrData as $rows) {
				if($sIdDocumento != $rows["tipo_documento"] . $rows["serie_documento"] . $rows["numero_documento"]){
					if($counter != 0){
						$worksheet1->write_string($fila, 7, "TOTAL: ", $formato_numero);
						$worksheet1->write_number($fila, 8, number_format($dSumCantidad, 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 9, number_format(($dSumTotal / $dSumCantidad), 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 10, number_format($dSumVB, 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 11, number_format($dSumDscto, 2, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 12, number_format($dSumVV, 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 13, number_format($dSumIGV, 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 14, number_format($dSumTotal, 2, '.', ''), $formato_numero);
						$dSumCantidad = 0.0000;
						$dSumVB = 0.0000;
						$dSumDscto = 0.00;
						$dSumVV = 0.0000;
						$dSumIGV = 0.0000;
						$dSumTotal = 0.00;
						$fila++;
					}

					$worksheet1->write_string($fila, 0, $rows["fe_emision"], $formato_string);
					$worksheet1->write_string($fila, 1, $rows["tipo_documento"], $formato_string);
					$worksheet1->write_string($fila, 2, $rows["serie_documento"], $formato_string);
					$worksheet1->write_string($fila, 3, $rows["numero_documento"], $formato_string);
					$worksheet1->write_string($fila, 4, $rows["nu_documento_identidad"], $formato_string);
					$worksheet1->write_string($fila, 5, $rows["no_razsocial"], $formato_string);
					$worksheet1->write_string($fila, 16, $rows["nu_liquidacion_vales"], $formato_string);
					$sIdDocumento = $rows["tipo_documento"] . $rows["serie_documento"] . $rows["numero_documento"];
					$fila++;
				}
				if ($sTipoVista == "D"){
					$worksheet1->write_string($fila, 6, $rows["nu_codigo_producto"], $formato_string);
					$worksheet1->write_string($fila, 7, $rows["no_producto"], $formato_string);
					$worksheet1->write_number($fila, 8, number_format($rows["ss_cantidad"], 4, '.', ''), $formato_numero);
					$worksheet1->write_number($fila, 9, number_format($rows['ss_precio'], 4, '.', ''), $formato_numero);
					$worksheet1->write_number($fila, 10, number_format($rows['ss_valor_bruto'], 4, '.', ''), $formato_numero);
					$worksheet1->write_number($fila, 11, number_format($rows['ss_descuento'], 4, '.', ''), $formato_numero);
					$worksheet1->write_number($fila, 12, number_format($rows['ss_valor_venta'], 4, '.', ''), $formato_numero);
					$worksheet1->write_number($fila, 13, number_format($rows['ss_igv'], 4, '.', ''), $formato_numero);
					$worksheet1->write_number($fila, 14, number_format($rows['ss_total'], 2, '.', ''), $formato_numero);
					$worksheet1->write_number($fila, 15, number_format($rows['ss_tipo_cambio'], 3, '.', ''), $formato_numero);					$fila++;
				}
				$dSumCantidad+=$rows["ss_cantidad"];
				$dSumVB+=$rows["ss_valor_bruto"];
				$dSumDscto+=$rows["ss_descuento"];
				$dSumVV+=$rows["ss_valor_venta"];
				$dSumIGV+=$rows["ss_igv"];
				$dSumTotal+=$rows["ss_total"];
				$counter++;
			}
			$worksheet1->write_string($fila, 7, "TOTAL: ", $formato_numero);
			$worksheet1->write_number($fila, 8, number_format($dSumCantidad, 4, '.', ''), $formato_numero);
			$worksheet1->write_number($fila, 9, number_format(($dSumTotal / $dSumCantidad), 4, '.', ''), $formato_numero);
			$worksheet1->write_number($fila, 10, number_format($dSumVB, 4, '.', ''), $formato_numero);
			$worksheet1->write_number($fila, 11, number_format($dSumDscto, 4, '.', ''), $formato_numero);
			$worksheet1->write_number($fila, 12, number_format($dSumVV, 4, '.', ''), $formato_numero);
			$worksheet1->write_number($fila, 13, number_format($dSumIGV, 4, '.', ''), $formato_numero);
			$worksheet1->write_number($fila, 14, number_format($dSumTotal, 2, '.', ''), $formato_numero);
		}

		$workbook->close();	

		$chrFileName = "RegistroVentasClientes";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}

    function gridViewPDF($arrData, $sTipoVista) {
		$cab = Array(
			"fe_emision"				=>	"FECHA",
			"tipo_documento"			=>	"TIPO",
			"serie_documento"			=>	"SERIE",
			"numero_documento"			=>	"NUMERO",
			"nu_documento_identidad"	=>	"RUC",
			"no_razsocial"				=>	"RAZON SOCIAL",
			"nu_codigo_producto"		=>	"CODIGO",
			"no_producto"				=>	"NOMBRE PRODUCTO",
			"ss_cantidad"				=>	"CANTIDAD",
			"ss_precio"					=>	"PRECIO",
			"ss_valor_bruto"			=>	"VALOR BRUTO",
			"ss_descuento"				=>	"DSCTO.",
			"ss_valor_venta"			=>	"VALOR VENTA",
			"ss_igv"					=>	"IGV",
			"ss_total"					=>	"TOTAL",
			"ss_tipo_cambio"			=>	"T.C.",
			"nu_liquidacion_vales"		=>	"# LIQUIDACION",
		);

		$reporte = new CReportes2("L");

		$reporte->Ln();	 
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabeceraSize(3, "C", "courier,B,15", "Registro de Ventas Clientes");
		$reporte->definirCabecera(6, "L", "");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 6.3);

		$reporte->definirColumna("fe_emision",$reporte->TIPO_TEXTO,10,"L", "_pri");
		$reporte->definirColumna("tipo_documento",$reporte->TIPO_TEXTO,20,"L", "_pri");
		$reporte->definirColumna("serie_documento",$reporte->TIPO_TEXTO,6,"R", "_pri");
		$reporte->definirColumna("numero_documento",$reporte->TIPO_TEXTO,10,"R", "_pri");
		$reporte->definirColumna("nu_documento_identidad",$reporte->TIPO_TEXTO,11,"R", "_pri");
		$reporte->definirColumna("no_razsocial",$reporte->TIPO_TEXTO,45,"L", "_pri");
		$reporte->definirColumna("nu_codigo_producto",$reporte->TIPO_TEXTO,11,"R", "_pri");
		$reporte->definirColumna("no_producto",$reporte->TIPO_TEXTO,20,"L", "_pri");
		$reporte->definirColumna("ss_cantidad",$reporte->TIPO_COSTO,10,"R", "_pri");
		$reporte->definirColumna("ss_precio",$reporte->TIPO_COSTO,8,"R", "_pri");
		$reporte->definirColumna("ss_valor_bruto",$reporte->TIPO_COSTO,10,"R", "_pri");
		$reporte->definirColumna("ss_descuento",$reporte->TIPO_COSTO,8,"R", "_pri");
		$reporte->definirColumna("ss_valor_venta",$reporte->TIPO_COSTO,10,"R", "_pri");
		$reporte->definirColumna("ss_igv",$reporte->TIPO_COSTO,8,"R", "_pri");
		$reporte->definirColumna("ss_total",$reporte->TIPO_IMPORTE,12,"R", "_pri");
		$reporte->definirColumna("ss_tipo_cambio",$reporte->TIPO_IMPORTE,6,"L", "_pri");
		$reporte->definirColumna("nu_liquidacion_vales",$reporte->TIPO_TEXTO,20,"L", "_pri");

		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab, "_pri");
		$reporte->AddPage();
		$reporte->Ln();

		if(count($arrData) === 0) {
			$arr = array(
				"no_producto" => "No hay registros",
				"ss_cantidad" => " ",
				"ss_precio" => " ",
				"ss_valor_bruto" => " ",
				"ss_descuento" => " ",
				"ss_valor_venta" => " ",
				"ss_igv" => " ",
				"ss_total" => " ",
				"ss_tipo_cambio" => " ",
			);
			$reporte->nuevaFila($arr, "_pri");
		} else {
			$counter = 0;
			$dSumCantidad = 0.0000;
			$dSumVB = 0.0000;
			$dSumDscto = 0.00;
			$dSumVV = 0.0000;
			$dSumIGV = 0.0000;
			$dSumTotal = 0.00;
			$sIdDocumento = "";
			foreach ($arrData as $rows) {
				if($sIdDocumento != $rows["tipo_documento"] . $rows["serie_documento"] . $rows["numero_documento"]){
					if($counter != 0){
						$arr = array(
							"no_producto" => "TOTAL: ",
							"ss_cantidad" => number_format($dSumCantidad, 4, '.', ','),
							"ss_precio" => number_format(($dSumTotal / $dSumCantidad), 4, '.', ','),
							"ss_valor_bruto" => number_format($dSumVB, 4, '.', ','),
							"ss_descuento" => number_format($dSumDscto, 2, '.', ','),
							"ss_valor_venta" => number_format($dSumVV, 4, '.', ','),
							"ss_igv" => number_format($dSumIGV, 4, '.', ','),
							"ss_total" => number_format($dSumTotal, 4, '.', ','),
							"ss_tipo_cambio" => " ",
						);
						$dSumCantidad = 0.0000;
						$dSumVB = 0.0000;
						$dSumDscto = 0.00;
						$dSumVV = 0.0000;
						$dSumIGV = 0.0000;
						$dSumTotal = 0.00;
						$reporte->nuevaFila($arr, "_pri");
					}
					$arr = array(
						"fe_emision" => $rows["fe_emision"],
						"tipo_documento" => $rows["tipo_documento"],
						"serie_documento" => $rows["serie_documento"],
						"numero_documento" => $rows["numero_documento"],
						"nu_documento_identidad" => $rows["nu_documento_identidad"],
						"no_razsocial" => $rows["no_razsocial"],
						"ss_cantidad" => " ",
						"ss_precio" => " ",
						"ss_valor_bruto" => " ",
						"ss_descuento" => " ",
						"ss_valor_venta" => " ",
						"ss_igv" => " ",
						"ss_total" => " ",
						"ss_tipo_cambio" => " ",
						"nu_liquidacion_vales" => $rows["nu_liquidacion_vales"],
					);
					$sIdDocumento = $rows["tipo_documento"] . $rows["serie_documento"] . $rows["numero_documento"];
					$reporte->nuevaFila($arr, "_pri");
				}
				if ($sTipoVista == "D"){
					$arr = array(
						"nu_codigo_producto" => $rows["nu_codigo_producto"],
						"no_producto" => $rows["no_producto"],
						"ss_cantidad" => number_format($rows["ss_cantidad"], 4, '.', ','),
						"ss_precio" => number_format($rows["ss_precio"], 4, '.', ','),
						"ss_valor_bruto" => number_format($rows["ss_valor_bruto"], 4, '.', ','),
						"ss_descuento" => number_format($rows["ss_descuento"], 2, '.', ','),
						"ss_valor_venta" => number_format($rows["ss_valor_venta"], 4, '.', ','),
						"ss_igv" => number_format($rows["ss_igv"], 4, '.', ','),
						"ss_total" => number_format($rows["ss_total"], 2, '.', ','),
						"ss_tipo_cambio" => number_format($rows["ss_tipo_cambio"], 3, '.', ','),
					);
					$reporte->nuevaFila($arr, "_pri");
				}
				$dSumCantidad+=$rows["ss_cantidad"];
				$dSumVB+=$rows["ss_valor_bruto"];
				$dSumDscto+=$rows["ss_descuento"];
				$dSumVV+=$rows["ss_valor_venta"];
				$dSumIGV+=$rows["ss_igv"];
				$dSumTotal+=$rows["ss_total"];
				$counter++;
			}
			$arr = array(
				"no_producto" => "TOTAL: ",
				"ss_cantidad" => number_format($dSumCantidad, 4, '.', ','),
				"ss_precio" => number_format(($dSumTotal / $dSumCantidad), 4, '.', ','),
				"ss_valor_venta" => number_format($dSumVB, 4, '.', ','),
				"ss_valor_bruto" => number_format($dSumDscto, 4, '.', ','),
				"ss_descuento" => number_format($dSumVV, 2, '.', ','),
				"ss_igv" => number_format($dSumIGV, 4, '.', ','),
				"ss_total" => number_format($dSumTotal, 4, '.', ','),
				"ss_tipo_cambio" => " ",
			);
			$reporte->nuevaFila($arr, "_pri");
		}

		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();
		$reporte->Lnew();
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/RegistroVentasClientes.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/RegistroVentasClientes.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}
}
