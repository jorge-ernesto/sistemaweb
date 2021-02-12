<?php

class ResumenDiarioValesXEmpresaTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>Diario de Consistencia de Vales x Centro de Costo</b></h2>';
    }
    
	function formPrincipal($arrAlmacenes, $iAlmacen, $dInicial, $dFinal, $dCierre, $iDocumentoIdentidad, $sRazSocial, $sTipoVistaDetallado, $sTipoVistaResumido) {

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.RESUMENDIARIOVALESXEMPRESA'));

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
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
		    	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="radio-iTipoVista" value="D" ' . $sTipoVistaDetallado . '>Detallado'));
		    	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="radio-iTipoVista" value="R" ' . $sTipoVistaResumido . '>Resumido'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel </button>'));
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
				$result .= '<th rows="2" class="grid_cabecera"># VALE MANUAL</th>';
				$result .= '<th rows="2" class="grid_cabecera"># TICKET</th>';
				$result .= '<th rows="2" class="grid_cabecera">FECHA EMISION</th>';
				$result .= '<th colspan="2" class="grid_cabecera">GASOHOL 84</th>';
				$result .= '<th colspan="2" class="grid_cabecera">GASOHOL 90</th>';
				$result .= '<th colspan="2" class="grid_cabecera">GASOHOL 97</th>';
				$result .= '<th colspan="2" class="grid_cabecera">DIESEL D2</th>';
				$result .= '<th colspan="2" class="grid_cabecera">GASOHOL 95</th>';
				$result .= '<th colspan="2" class="grid_cabecera">GLP</th>';
				$result .= '<th class="grid_cabecera">TOTAL</th>';
			$result .= '</tr>';

			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th colspan="3" class="grid_cabecera"></th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">SOLES</th>';
				$result .= '<th class="grid_cabecera"></th>';
			$result .= '</tr>';

			if(count($arrData) === 0) {
				$result .= '<tr class="bgcolor">';
					$result .= '<td class="grid_detalle_par" align ="center" colspan="16" style="font-size: 10.5px;"><STRONG>No hay registros</STRONG></td>';
				$result .= '</tr>';
			} else {
				$iDocumentoIdentidad = 0;

				$dSumCantidad84 = 0.0000;
				$dSumSoles84 = 0.0000;
				$dSumCantidad90 = 0.0000;
				$dSumSoles90 = 0.0000;
				$dSumCantidad97 = 0.0000;
				$dSumSoles97 = 0.0000;
				$dSumCantidadD2 = 0.0000;
				$dSumSolesD2 = 0.0000;
				$dSumCantidad95 = 0.0000;
				$dSumSoles95 = 0.0000;
				$dSumCantidadGLP = 0.0000;
				$dSumSolesGLP = 0.0000;
				$dSumTotal = 0.0000;
				//$dGranSumTotal = [];
				$suma=0.0000;
				$counter = 0;

				$result .= '<tbody>';
				foreach ($arrData as $rows) {
					$color = ($counter%2) == 0 ? 'grid_detalle_impar' : 'grid_detalle_par';
					if($iDocumentoIdentidad != $rows["nu_documento_identidad"]){
						if($counter != 0){
					    	$result .= '<tr class="grid_detalle_total">';
					    		$result .= '<td align ="right" colspan="3">TOTAL CLIENTE: </td>';
					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad84, '4', '.', ',')) . '</td>';
					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSoles84, '4', '.', ',')) . '</td>';

					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad90, '4', '.', ',')) . '</td>';
					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSoles90, '4', '.', ',')) . '</td>';

					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad97, '4', '.', ',')) . '</td>';
					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSoles97, '4', '.', ',')) . '</td>';

					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidadD2, '4', '.', ',')) . '</td>';
					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSolesD2, '4', '.', ',')) . '</td>';

					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad95, '4', '.', ',')) . '</td>';
					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSoles95, '4', '.', ',')) . '</td>';

					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidadGLP, '4', '.', ',')) . '</td>';
					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSolesGLP, '4', '.', ',')) . '</td>';

					    		$result .= '<td align ="right">' . htmlentities(number_format($dSumTotal, '4', '.', ',')) . '</td>';
						    $result .= '</tr>';

							$dSumCantidad84 = 0.0000;
							$dSumSoles84 = 0.0000;
							$dSumCantidad90 = 0.0000;
							$dSumSoles90 = 0.0000;
							$dSumCantidad97 = 0.0000;
							$dSumSoles97 = 0.0000;
							$dSumCantidadD2 = 0.0000;
							$dSumSolesD2 = 0.0000;
							$dSumCantidad95 = 0.0000;
							$dSumSoles95 = 0.0000;
							$dSumCantidadGLP = 0.0000;
							$dSumSolesGLP = 0.0000;
							$dSumTotal = 0.0000;

						}
					    $result .= '<tr class="grid_detalle_especial">';
					    	$result .= '<td align="right">CLIENTE: </td>';
				    		$result .= '<td align="right">' . htmlentities($rows["nu_documento_identidad"]) . '</td>';
				    		$result .= '<td align="left" colspan="14">' . htmlentities($rows["no_razsocial"]) . '</td>';
				    	$result .= '</tr>';
						$iDocumentoIdentidad = $rows["nu_documento_identidad"];
					}
					if ($sTipoVista == "D"){
						$result .= '<tr class="'. $color. '">';
					    	$result .= '<td align="center">' . htmlentities($rows["nu_vale_manual"]) . '</td>';
					    	$result .= '<td align="center">' . htmlentities($rows["nu_trans"]) . '</td>';
					    	$result .= '<td align="center">' . htmlentities($rows["fe_emision"]) . '</td>';
				    	if($rows["nu_codigo_producto"] == "11620301"){
				    		$result .= '<td align="right">' . htmlentities($rows["nu_cantidad"]) . '</td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
				    		$result .= '<td colspan="10" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
							$dSumCantidad84+=$rows["nu_cantidad"];
							$dSumSoles84+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620302"){
				    		$result .= '<td colspan="2" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["nu_cantidad"]) . '</td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
				    		$result .= '<td colspan="8" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
							$dSumCantidad90+=$rows["nu_cantidad"];
							$dSumSoles90+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620303"){
				    		$result .= '<td colspan="4" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["nu_cantidad"]) . '</td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
				    		$result .= '<td colspan="6" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
							$dSumCantidad97+=$rows["nu_cantidad"];
							$dSumSoles97+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620304"){
				    		$result .= '<td colspan="6" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["nu_cantidad"]) . '</td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
				    		$result .= '<td colspan="4" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
							$dSumCantidadD2+=$rows["nu_cantidad"];
							$dSumSolesD2+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620305"){
				    		$result .= '<td colspan="8" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["nu_cantidad"]) . '</td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
				    		$result .= '<td colspan="2" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
							$dSumCantidad95+=$rows["nu_cantidad"];
							$dSumSoles95+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620307"){
				    		$result .= '<td colspan="10" align="center"></td>';
				    		$result .= '<td align="right">' . htmlentities($rows["nu_cantidad"]) . '</td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
				    		$result .= '<td align="right">' . htmlentities($rows["ss_importe"]) . '</td>';
							$dSumCantidadGLP+=$rows["nu_cantidad"];
							$dSumSolesGLP+=$rows["ss_importe"];
						}
					}
					elseif($sTipoVista == "R"){
						if($rows["nu_codigo_producto"] == "11620301"){
							$dSumCantidad84+=$rows["nu_cantidad"];
							$dSumSoles84+=$rows["ss_importe"];
						}
						if($rows["nu_codigo_producto"] == "11620302"){
							$dSumCantidad90+=$rows["nu_cantidad"];
							$dSumSoles90+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620303"){
							$dSumCantidad97+=$rows["nu_cantidad"];
							$dSumSoles97+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620304"){
							$dSumCantidadD2+=$rows["nu_cantidad"];
							$dSumSolesD2+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620305"){
							$dSumCantidad95+=$rows["nu_cantidad"];
							$dSumSoles95+=$rows["ss_importe"];
				    	}
				    	if($rows["nu_codigo_producto"] == "11620307"){
							$dSumCantidadGLP+=$rows["nu_cantidad"];
							$dSumSolesGLP+=$rows["ss_importe"];
						}
					}
					$dSumTotal+=$rows["ss_importe"];
					//$dGranSumTotal[]=array('data' => $rows["ss_importe"]);
					$dGranSumTotal[]=array($rows["ss_importe"]);
					$dSumAcumulado=array_sum(array_map("array_sum",$dGranSumTotal));
					$result .= '</tr>';
					$counter++;
				}
				// echo "<script>console.log('" . json_encode(array_map("array_sum",$dGranSumTotal)) . "')</script>";//cai

				   	$result .= '<tr class="grid_detalle_total">';
			    		$result .= '<td align ="right" colspan="3">TOTAL CLIENTE: </td>';
			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad84, '4', '.', ',')) . '</td>';
			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSoles84, '4', '.', ',')) . '</td>';

			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad90, '4', '.', ',')) . '</td>';
			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSoles90, '4', '.', ',')) . '</td>';

			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad97, '4', '.', ',')) . '</td>';
			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSoles97, '4', '.', ',')) . '</td>';

			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidadD2, '4', '.', ',')) . '</td>';
			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSolesD2, '4', '.', ',')) . '</td>';

			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidad95, '4', '.', ',')) . '</td>';
			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSoles95, '4', '.', ',')) . '</td>';

			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumCantidadGLP, '4', '.', ',')) . '</td>';
			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumSolesGLP, '4', '.', ',')) . '</td>';

			    		$result .= '<td align ="right">' . htmlentities(number_format($dSumTotal, '4', '.', ',')) . '</td>';
					$result .= '</tr>';
					$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<td colspan="15" align="right" >SUMA TOTAL</td>';
				
				$result .= '<td align ="right">' . htmlentities(number_format($dSumAcumulado, '4', '.', ',')) . '</td>';
				
				$result .= '</tbody>';
			$result .= '</tr>';
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

		$formato_string_sborder =& $workbook->add_format();

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

		$formato_string->set_size(10);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_align('right');

		$worksheet1 =& $workbook->add_worksheet('Resumen Diario de Vales Credito');
		$worksheet1->set_column(0, 0, 15);
		$worksheet1->set_column(1, 1, 15);
		$worksheet1->set_column(2, 2, 20);//FECHA EMISION
		$worksheet1->set_column(3, 3, 15);
		$worksheet1->set_column(4, 4, 10);
		$worksheet1->set_column(5, 5, 15);
		$worksheet1->set_column(6, 6, 10);
		$worksheet1->set_column(7, 7, 15);
		$worksheet1->set_column(8, 8, 10);
		$worksheet1->set_column(9, 9, 15);
		$worksheet1->set_column(10, 10, 10);
		$worksheet1->set_column(11, 11, 15);
		$worksheet1->set_column(12, 12, 10);
		$worksheet1->set_column(13, 13, 15);
		$worksheet1->set_column(14, 14, 15);

		$fila = 0;
		$worksheet1->write_string($fila, 6, "Diario de Consistencia de Vales x Centro de Costo", $formato0);

		$fila = 3;
		$worksheet1->write_string($fila, 0, "# VALE MANUAL", $formato2);
		$worksheet1->write_string($fila, 1, "# TICKET", $formato2);
		$worksheet1->write_string($fila, 2, "FECHA EMISION", $formato2);
		$worksheet1->write_string($fila, 3, "GASOHOL 84", $formato_string_sborder);
		$worksheet1->write_string($fila, 5, "GASOHOL 90", $formato_string_sborder);
		$worksheet1->write_string($fila, 7, "GASOHOL 97", $formato_string_sborder);
		$worksheet1->write_string($fila, 9, "DIESEL D2", $formato_string_sborder);
		$worksheet1->write_string($fila, 11, "GASOHOL 95", $formato_string_sborder);
		$worksheet1->write_string($fila, 13, "GLP", $formato_string_sborder);
		$worksheet1->write_string($fila, 14, "TOTAL", $formato_string_sborder);

		$fila++;
		$worksheet1->write_string($fila, 3, "SOLES", $formato2);
		$worksheet1->write_string($fila, 4, "IMPORTE", $formato2);
		$worksheet1->write_string($fila, 5, "SOLES", $formato2);
		$worksheet1->write_string($fila, 6, "IMPORTE", $formato2);
		$worksheet1->write_string($fila, 7, "SOLES", $formato2);
		$worksheet1->write_string($fila, 8, "IMPORTE", $formato2);
		$worksheet1->write_string($fila, 9, "SOLES", $formato2);
		$worksheet1->write_string($fila, 10, "IMPORTE", $formato2);
		$worksheet1->write_string($fila, 11, "SOLES", $formato2);
		$worksheet1->write_string($fila, 12, "IMPORTE", $formato2);
		$worksheet1->write_string($fila, 13, "SOLES", $formato2);
		$worksheet1->write_string($fila, 14, "IMPORTE", $formato2);
		$worksheet1->write_string($fila, 15, "IMPORTE", $formato2);

		$fila++;
		if(count($arrData) === 0) {
			$worksheet1->write_string($fila, 5, "No hay registros", $formato0);
		} else {
			$iDocumentoIdentidad = 0;
			$dSumCantidad84 = 0.0000;
			$dSumSoles84 = 0.0000;
			$dSumCantidad90 = 0.0000;
			$dSumSoles90 = 0.0000;
			$dSumCantidad97 = 0.0000;
			$dSumSoles97 = 0.0000;
			$dSumCantidadD2 = 0.0000;
			$dSumSolesD2 = 0.0000;
			$dSumCantidad95 = 0.0000;
			$dSumSoles95 = 0.0000;
			$dSumCantidadGLP = 0.0000;
			$dSumSolesGLP = 0.0000;
			$dSumTotal = 0.0000;
			foreach ($arrData as $rows) {
				if($iDocumentoIdentidad != $rows["nu_documento_identidad"]){
					if($counter != 0){
						$worksheet1->write_string($fila, 2, "TOTAL CLIENTE: ", $formato_total);
						$worksheet1->write_number($fila, 3, number_format($dSumCantidad84, 4, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 4, number_format($dSumSoles84, 4, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 5, number_format($dSumCantidad90, 4, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 6, number_format($dSumSoles90, 2, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 7, number_format($dSumCantidad97, 4, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 8, number_format($dSumSoles97, 4, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 9, number_format($dSumCantidadD2, 2, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 10, number_format($dSumSolesD2, 2, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 11, number_format($dSumCantidad95, 2, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 12, number_format($dSumCantidad95, 2, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 13, number_format($dSumCantidadGLP, 2, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 14, number_format($dSumSolesGLP, 2, '.', ''), $formato_total);
						$worksheet1->write_number($fila, 15, number_format($dSumTotal, 2, '.', ''), $formato_total);
						$dSumCantidad84 = 0.0000;
						$dSumSoles84 = 0.0000;
						$dSumCantidad90 = 0.0000;
						$dSumSoles90 = 0.0000;
						$dSumCantidad97 = 0.0000;
						$dSumSoles97 = 0.0000;
						$dSumCantidadD2 = 0.0000;
						$dSumSolesD2 = 0.0000;
						$dSumCantidad95 = 0.0000;
						$dSumSoles95 = 0.0000;
						$dSumCantidadGLP = 0.0000;
						$dSumSolesGLP = 0.0000;
						$dSumTotal = 0.0000;
						$fila++;
					}

					$worksheet1->write_string($fila, 0, "CLIENTE: ", $formato_special);
					$worksheet1->write_string($fila, 1, $rows["nu_documento_identidad"], $formato_special);
					$worksheet1->write_string($fila, 2, $rows["no_razsocial"], $formato_special);
					$iDocumentoIdentidad = $rows["nu_documento_identidad"];
					$fila++;
				}
				if ($sTipoVista == "D"){
					$worksheet1->write_string($fila, 0, $rows["nu_vale_manual"], $formato_string);
					$worksheet1->write_string($fila, 1, $rows["nu_trans"], $formato_string);
					$worksheet1->write_string($fila, 2, $rows["fe_emision"], $formato_string);

					if($rows["nu_codigo_producto"] == "11620301"){
						$worksheet1->write_number($fila, 3, number_format($rows["nu_cantidad"], 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 4, number_format($rows['ss_importe'], 4, '.', ''), $formato_numero);
						$dSumCantidad84+=$rows["nu_cantidad"];
						$dSumSoles84+=$rows["ss_importe"];
					}

					if($rows["nu_codigo_producto"] == "11620302"){
						$worksheet1->write_number($fila, 5, number_format($rows['nu_cantidad'], 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 6, number_format($rows['ss_importe'], 4, '.', ''), $formato_numero);
						$dSumCantidad90+=$rows["nu_cantidad"];
						$dSumSoles90+=$rows["ss_importe"];
					}

					if($rows["nu_codigo_producto"] == "11620303"){
						$worksheet1->write_number($fila, 7, number_format($rows['nu_cantidad'], 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 8, number_format($rows['ss_importe'], 4, '.', ''), $formato_numero);
						$dSumCantidad97+=$rows["nu_cantidad"];
						$dSumSoles97+=$rows["ss_importe"];
					}

					if($rows["nu_codigo_producto"] == "11620304"){
						$worksheet1->write_number($fila, 9, number_format($rows['nu_cantidad'], 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 10, number_format($rows['ss_importe'], 4, '.', ''), $formato_numero);
						$dSumCantidadD2+=$rows["nu_cantidad"];
						$dSumSolesD2+=$rows["ss_importe"];
					}

					if($rows["nu_codigo_producto"] == "11620305"){
						$worksheet1->write_number($fila, 11, number_format($rows['nu_cantidad'], 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 12, number_format($rows['ss_importe'], 4, '.', ''), $formato_numero);
						$dSumCantidad95+=$rows["nu_cantidad"];
						$dSumSoles95+=$rows["ss_importe"];
					}

					if($rows["nu_codigo_producto"] == "11620307"){
						$worksheet1->write_number($fila, 13, number_format($rows['nu_cantidad'], 4, '.', ''), $formato_numero);
						$worksheet1->write_number($fila, 14, number_format($rows['ss_importe'], 4, '.', ''), $formato_numero);
						$dSumCantidadGLP+=$rows["nu_cantidad"];
						$dSumSolesGLP+=$rows["ss_importe"];
					}
					$worksheet1->write_number($fila, 15, number_format($rows['ss_importe'], 4, '.', ''), $formato_numero);
					$fila++;
				}
				$dSumTotal+=$rows["ss_importe"];
				$counter++;
			}
			$worksheet1->write_string($fila, 2, "TOTAL CLIENTE: ", $formato_total);
			$worksheet1->write_number($fila, 3, number_format($dSumCantidad84, 4, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 4, number_format($dSumSoles84, 4, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 5, number_format($dSumCantidad90, 4, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 6, number_format($dSumSoles90, 2, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 7, number_format($dSumCantidad97, 4, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 8, number_format($dSumSoles97, 4, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 9, number_format($dSumCantidadD2, 2, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 10, number_format($dSumSolesD2, 2, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 11, number_format($dSumCantidad95, 2, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 12, number_format($dSumCantidad95, 2, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 13, number_format($dSumCantidadGLP, 2, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 14, number_format($dSumSolesGLP, 2, '.', ''), $formato_total);
			$worksheet1->write_number($fila, 15, number_format($dSumTotal, 2, '.', ''), $formato_total);
		}

		$workbook->close();	

		$chrFileName = "ResumenDiarioValesxEmpresa";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}
