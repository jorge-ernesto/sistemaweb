<?php

class MovimientosCajaTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Informe de Movimientos de Caja</b></h2>';
    }
    
	function formSearch($arrAlmacenes, $iAlmacen, $fe_inicial, $fe_final, $dUltimoCierre, $arrCajas, $iCaja, $arrTipoMovimientoCaja, $iTipoMovimientoCaja, $arrMediosPago, $iMedioPago, $arrCuentasBancarias, $iCuentaBancaria) {
		if ($arrMediosPago['estado']) {
			$html_option = '';
			$html_option = '<option value="0">TODOS</option>';
			foreach ($arrMediosPago['result'] as $row) {
				$selected = NULL;
				if($row['nu_id'] == $iMedioPago)
					$selected = "selected";
				$html_option .= '<option value="' . $row['nu_id'] . '" ' . $selected . '>' . $row['no_descripcion'] . '</option>';
			}
		} else
			$html_option = $arrMediosPago['mensaje'];

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.CAJA'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dUltimoCierre . '">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacen</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iAlmacen', '', $iAlmacen, $arrAlmacenes, espacios(3), array("onfocus" => "getFechasIF();getDatos();")));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Caja</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iCaja', '', $iCaja, $arrCajas, espacios(3), ""));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Tipo Movimiento Caja</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iTipoMovimientoCaja', '', $iTipoMovimientoCaja, $arrTipoMovimientoCaja, espacios(3), ""));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Medios Pago</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
					'<select id="cbo-iMedioPago" name="cbo-iMedioPago">
					    ' . $html_option . '
				    </select>'
					));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Cuenta Bancaria</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iCuentaBancaria', '', $iCuentaBancaria, $arrCuentasBancarias, espacios(3), ""));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dInicial', '', $fe_inicial, '', 12, 10));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dFinal', '', $fe_final, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="HTML"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="EXCEL"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("cbo-iAlmacen").focus();}</script>'));
		return $form->getForm();
    }
    
    function gridViewHTML($arrSaldoInicial, $arrMovimientosIngresosEgresos) {
		$result = '';
		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<thead>';
				$result .= '<tr>';
					$result .= '<td class="grid_cabecera"><b>FECHA</b></td>';
					$result .= '<td class="grid_cabecera"><b>RECIBO</b></td>';
					$result .= '<td class="grid_cabecera"><b>DOCUMENTO</b></td>';
					$result .= '<td class="grid_cabecera"><b>CLIENTE / PROVEEDOR</b></td>';
					$result .= '<td class="grid_cabecera"><b>GLOSA</b></td>';
					$result .= '<td class="grid_cabecera"><b>MEDIO PAGO</b></td>';
					$result .= '<td class="grid_cabecera"><b>OPERACION</b></td>';
					$result .= '<td class="grid_cabecera"><b>INGRESOS</b></td>';
					$result .= '<td class="grid_cabecera"><b>EGRESOS</b></td>';
					$result .= '<td class="grid_cabecera"><b>SALDO</b></td>';
				$result .= '</tr>';
			$result .= '</thead>';

			$result .= '<tr>';
			if ($arrSaldoInicial["estado"] == FALSE)
				$result .= '<td class="bgcolor_cabecera" colspan="10" align="right">' . $arrSaldoInicial["mensaje"] . '</td>';
			else
				$result .= '<td class="bgcolor_cabecera" colspan="10" align="right">SALDO INICIAL: <b>S/ ' . htmlentities(number_format($arrSaldoInicial["result"][0]["ss_saldo_inicial"], 2, '.', ',')) . '</b></td>';
			$result .= '</tr>';
		
			$result .= '<tbody>';
			if($arrMovimientosIngresosEgresos['estado'] == FALSE) {
				$result .= '<tr class="bgcolor">';
					$result .= '<td colspan="15" class="grid_detalle_par" align="center"><b>' . $arrMovimientosIngresosEgresos['mensaje'] . '</b></td>';
				$result .= '</tr>';
			} else {
				$counter = 0;
				$sumSaldo = 0.00;
				$sumIngresos = 0.00;
				$sumEgresos = 0.00;
				$sumSaldo += (float)$arrSaldoInicial["result"][0]["ss_saldo_inicial"];
				foreach ($arrMovimientosIngresosEgresos['result'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';

			    	$result .= '<tr class="'. $color. '">';
				    	$result .= '<td align="center">' . htmlentities($row["fe_emision"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["nu_recibo"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["no_documento"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities(($row["nu_tipo_operacion"] == "0" ? $row["no_cliente"] : $row["no_proveedor"])) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["txt_glosa"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["no_medio_pago"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["no_tipo_operacion"]) . '</td>';
				    	if ($row["nu_tipo_operacion"] == "0") {
				    		$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], 2, '.', ',')) . '</td>';
				    		$result .= '<td align="right">0.00</td>';
							$sumSaldo += $row['ss_total'];
							$sumIngresos += $row['ss_total'];
				    	} else {
				    		$result .= '<td align="right">0.00</td>';
				    		$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], 2, '.', ',')) . '</td>';
							$sumSaldo -= $row['ss_total'];
							$sumEgresos -= $row['ss_total'];
				    	}
				    	$result .= '<td align="center">' . htmlentities(number_format($sumSaldo, 2, '.', ',')) . '</td>';
				    $result .= '</tr>';
				    $counter++;
				}

					$result .= '<tr bgcolor="#FFFFCD">';
						$result .= '<td colspan="7" class="bgcolor_total" align="right"><strong>TOTAL: </font></strong></td>';
						$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumIngresos, '3', '.', ',')) . '</strong></td>';
						$result .= '<td class="bgcolor_total" align="right"><strong>' . htmlentities(number_format($sumEgresos, '3', '.', ',')) . '</strong></td>';
						$result .= '<td class="bgcolor_total" align="right"></td>';
			    	$result .= '</tr>';
				$result .= '</tbody>';
			}
		return $result;
    }

    function gridViewPDF($arrSaldoInicial, $arrMovimientosIngresosEgresos, $arrNombreAlmacen, $iAlmacen, $fe_inicial_pdf, $fe_final_pdf) {

		$cab = Array(
				"fecha"			=>	"FECHA",
				"recibo"		=>	"RECIBO",
				"documento"		=>	"DOCUMENTO",
				"cp"			=>	"CLIENTE / PROVEEDOR",
				"glosa"			=>	"GLOSA",
				"pago"			=>	"PAGO",
				"operacion"		=>	"OPERACION",
				"ingresos"		=>	"INGRESOS",
				"egresos"		=>	"EGRESOS",
				"saldo"			=>	"SALDO"
			);

		$reporte = new CReportes2("P","pt","A4");

		$reporte->Ln();	 
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabeceraSize(3, "L", "courier,B,9", "                                           INFORME DE MOVIMIENTOS DE CAJA");
		$reporte->definirCabecera(4, "L", "EMPRESA: ".$arrNombreAlmacen[$iAlmacen]);
		$reporte->definirCabecera(5, "L", "FECHA: ".$fe_inicial_pdf." AL ".$fe_final_pdf);
		$reporte->definirCabecera(6, "L", "");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 6.5);

		$reporte->definirColumna("fecha",$reporte->TIPO_TEXTO,10,"C", "_pri");
		$reporte->definirColumna("recibo",$reporte->TIPO_TEXTO,15,"C", "_pri");
		$reporte->definirColumna("documento",$reporte->TIPO_TEXTO,15,"C", "_pri");
		$reporte->definirColumna("cp",$reporte->TIPO_TEXTO,20,"C", "_pri");
		$reporte->definirColumna("glosa",$reporte->TIPO_TEXTO,15,"C", "_pri");
		$reporte->definirColumna("pago",$reporte->TIPO_TEXTO,10,"C", "_pri");
		$reporte->definirColumna("operacion",$reporte->TIPO_TEXTO,10,"C", "_pri");
		$reporte->definirColumna("ingresos",$reporte->TIPO_TEXTO,13,"R", "_pri");
		$reporte->definirColumna("egresos",$reporte->TIPO_TEXTO,13,"R", "_pri");
		$reporte->definirColumna("saldo",$reporte->TIPO_TEXTO,13,"R", "_pri");

		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab, "_pri");
		$reporte->AddPage();
		$reporte->Ln();	

		if ($arrSaldoInicial["estado"] == FALSE) {
			$inicial = array(
				"saldo"	=> $arrSaldoInicial["mensaje"]
			);
		} else {
			$inicial = array(
				"egresos" => "SALDO INICIAL: ",
				"saldo"	=> number_format($arrSaldoInicial["result"][0]["ss_saldo_inicial"], 2, '.', ',')
			);
		}

		$reporte->Ln();
		$reporte->lineaH();
		$reporte->nuevaFila("                             ");
		$reporte->nuevaFila($inicial, "_pri");

		if($arrMovimientosIngresosEgresos['estado'] == FALSE) {
			$arr = array(
				"cp"		=> $arrMovimientosIngresosEgresos['mensaje'],
			);
			$reporte->nuevaFila($arr, "_pri");
		} else {
			$sumSaldo = 0.00;
			$sumIngresos = 0.00;
			$sumEgresos = 0.00;
			$sumSaldo = $sumSaldo + $arrSaldoInicial["result"][0]["ss_saldo_inicial"];
			foreach ($arrMovimientosIngresosEgresos['result'] as $row) {
				$arr = array(
					"fecha"		=> $row['fe_emision'],
					"recibo"	=> $row['nu_recibo'],
					"documento"	=> $row['no_documento'],
					"cp"		=> ($row['nu_tipo_operacion'] == 0 ? $row['no_cliente'] : $row['no_proveedor']),
					"glosa"		=> $row['txt_glosa'],
					"pago"		=> $row['no_medio_pago'],
					"operacion"	=> $row['no_tipo_operacion'],
				);

				if($row['nu_tipo_operacion'] == 0){
					$arr += array(
						"ingresos" 	=> $row['ss_total'],
						"egresos" 	=> "0.00"
					);
					$sumSaldo += $row['ss_total'];
					$sumIngresos += $row['ss_total'];
				}else{
					$arr += array(
						"ingresos"	=>"0.00",
						"egresos"	=>$row['ss_total']
					);
					$sumSaldo -= $row['ss_total'];
					$sumEgresos -= $row['ss_total'];
				}
				$arr += array(
					"saldo"	=> $sumSaldo
				);
				$reporte->nuevaFila($arr, "_pri");
			}

			$suma = array(
				"operacion"	=> "TOTAL: ",
				"ingresos"	=>$sumIngresos,
				"egresos"	=>$sumEgresos,
			);

			$reporte->nuevaFila("                              ");
			$reporte->lineaH();
			$reporte->nuevaFila("                             ");
			$reporte->nuevaFila($suma, "_pri");
			$reporte->nuevaFila("                              ");
			$reporte->lineaH();
			$reporte->nuevaFila("                             ");
		}

		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();
		$reporte->Lnew();				
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/MovimientosCaja.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/MovimientosCaja.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}
    
	function gridViewEXCEL($arrSaldoInicial, $arrMovimientosIngresosEgresos, $arrNombreAlmacen, $iAlmacen, $fe_inicial_pdf, $fe_final_pdf) {
		$chrFileName="";
		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();
		$formato6 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');
		$formato6->set_size(11);
		$formato6->set_align('right');

		$worksheet1 =& $workbook->add_worksheet('Informe de Movimientos de Caja');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 16);
		$worksheet1->set_column(2, 2, 15);
		$worksheet1->set_column(3, 3, 40);
		$worksheet1->set_column(4, 4, 40);
		$worksheet1->set_column(5, 5, 30);
		$worksheet1->set_column(6, 6, 30);
		$worksheet1->set_column(7, 7, 25);
		$worksheet1->set_column(8, 8, 25);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(0, 5, "INFORME DE MOVIMIENTOS DE CAJA", $formato0);
		$worksheet1->write_string(1, 1, "EMPRESA: ".$arrNombreAlmacen[$iAlmacen], $formato0);
		$worksheet1->write_string(1, 5, "FECHA: ". $fe_inicial_pdf." AL ".$fe_final_pdf, $formato0);

		$row = 3;
		$worksheet1->write_string($row, 0, "FECHA",$formato2);
		$worksheet1->write_string($row, 1, "RECIBO",$formato2);
		$worksheet1->write_string($row, 2, "DOCUMENTO",$formato2);
		$worksheet1->write_string($row, 3, "CLIENTE / PROVEEDOR",$formato2);
		$worksheet1->write_string($row, 4, "GLOSA",$formato2);
		$worksheet1->write_string($row, 5, "MEDIO PAGO",$formato2);
		$worksheet1->write_string($row, 6, "OPERACION",$formato2);
		$worksheet1->write_string($row, 7, "INGRESOS",$formato2);
		$worksheet1->write_string($row, 8, "EGRESOS",$formato2);
		$worksheet1->write_string($row, 9, "SALDO",$formato2);

		$row = 4;
		if ($arrSaldoInicial["estado"] == FALSE)
			$worksheet1->write_string($row, 9, $arrSaldoInicial['mensaje'], $formato6);
		else {
			$worksheet1->write_string($row, 8, "SALDO INICIAL: ", $formato6);
			$worksheet1->write_number($row, 9, number_format($arrSaldoInicial["result"][0]["ss_saldo_inicial"], 2, '.', ''), $formato6);
		}

		$fila = 5;
		if($arrMovimientosIngresosEgresos['estado'] == FALSE) {
			$worksheet1->write_string($fila, 4, $arrMovimientosIngresosEgresos['mensaje'], $formato5);
		} else {
			$sumSaldo = 0.00;
			$sumIngresos = 0.00;
			$sumEgresos = 0.00;
			$sumSaldo = $sumSaldo + $arrSaldoInicial["result"][0]["ss_saldo_inicial"];
			foreach ($arrMovimientosIngresosEgresos['result'] as $row) {
				$worksheet1->write_string($fila, 0, $row['fe_emision'], $formato5);
				$worksheet1->write_string($fila, 1, $row['nu_recibo'], $formato5);
				$worksheet1->write_string($fila, 2, $row['no_documento'], $formato5);
				$worksheet1->write_string($fila, 3, ($row['nu_tipo_operacion'] == "0" ? $row['no_cliente'] : $row['no_proveedor']), $formato5);
				$worksheet1->write_string($fila, 4, $row['txt_glosa'], $formato5);
				$worksheet1->write_string($fila, 5, $row['no_medio_pago'], $formato5);
				$worksheet1->write_string($fila, 6, $row['no_tipo_operacion'], $formato5);
				if($row['nu_tipo_operacion'] == 0){
					$worksheet1->write_number($fila, 7, number_format($row['ss_total'], 2, '.', ''), $formato6);
					$worksheet1->write_number($fila, 8, 0.00, $formato6);
					$sumSaldo += $row['ss_total'];
					$sumIngresos += $row['ss_total'];
				}else{
					$worksheet1->write_number($fila, 7, 0.00, $formato6);
					$worksheet1->write_number($fila, 8, number_format($row['ss_total'], 2, '.', ''), $formato6);
					$sumSaldo -= $row['ss_total'];
					$sumEgresos -= $row['ss_total'];
				}
				$worksheet1->write_number($fila, 9, number_format($sumSaldo, 2, '.', ''), $formato6);
				$fila++;
			}
		}
		$fila++;
		$worksheet1->write_string($fila, 6, "TOTALES: ",$formato2);
		$worksheet1->write_number($fila, 7, number_format($sumIngresos, 2, '.', ''),$formato6);
		$worksheet1->write_number($fila, 8, number_format($sumEgresos, 2, '.', ''),$formato6);
		
		$workbook->close();	

		$chrFileName = "MovimientosCaja";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}

