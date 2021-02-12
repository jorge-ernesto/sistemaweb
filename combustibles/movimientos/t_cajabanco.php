<?php

class CajaBancoTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Reporte Caja y Banco</b></h2>';
    }
    
	function formSearch($arrAlmacenes, $iAlmacen, $dYear, $dMonth, $dYearPeriod) {
		for ($i=$dYearPeriod; $i <= date('Y'); $i++)
			$arrYear[$i] = $i;
		
		arsort($arrYear);

		$arrMonth = array(
			'01' => 'Enero',
			'02' => 'Febrero',
			'03' => 'Marzo',
			'04' => 'Abril',
			'05' => 'Mayo',
			'06' => 'Junio',
			'07' => 'Julio',
			'08' => 'Agosto',
			'09' => 'Setiembre',
			'10' => 'Octubre',
			'11' => 'Noviembre',
			'12' => 'Diciembre',
		);

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.CAJAYBANCO'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacen: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iAlmacen', '', $iAlmacen, $arrAlmacenes, espacios(3), array("")));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Mes: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-month', '', $dMonth, $arrMonth, espacios(3), array("")));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Año: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-year', '', $dYear, $arrYear, espacios(3), array("")));
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

		return $form->getForm();
    }
    
    function listado($resultados, $iAlmacen, $dYear, $dMonth) {
		$result = '';
		$result .= '<table align="center" border = "0.5px" style="background:#FFFFFF"> ';
			$result .= '<tr>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>FECHA</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>VENTAS GASOLINA</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>DESC. / INCRE.</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>VENTAS GNV</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>VENTAS GLP</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>LUBRICANTES</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>PROMOCIONES</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>VENTA TOTAL</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>CREDITOS (GLP + GASOLINA + GNV)</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>TARJETAS</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>EGRESOS</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>SOB. / FAL.</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>BCP</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>BBVA</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>SCOTIABANK</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>INTERBANK</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>OTROS INGRESOS</b></th>';
				$result .= '<th bgcolor="30767F" span style="color:#FFFFFF"><b>SALDO</b></th>';
			$result .= '</tr>';

		$saldo = 0;

		$saldo1 = $resultados[count($resultados)-1]['total_venta_comb'];
		$saldo2 = $resultados[count($resultados)-1]['total_venta_gnv'];
		$saldo3 = $resultados[count($resultados)-1]['total_venta_glp'];
		$saldo4 = $resultados[count($resultados)-1]['lubricantes'];
		$saldo5 = $resultados[count($resultados)-1]['otros'];
		$saldo6 = $resultados[count($resultados)-1]['clientescredito'];
		$saldo7 = $resultados[count($resultados)-1]['tarjetascredito'];
		$saldo8 = $resultados[count($resultados)-1]['bcp'];
		$saldo9 = $resultados[count($resultados)-1]['bbva'];
		$saldo10 = $resultados[count($resultados)-1]['scotiabank'];
		$saldo11 = $resultados[count($resultados)-1]['facimporte'];
		$saldo12 = $resultados[count($resultados)-1]['af_comb'];
		$saldo13 = $resultados[count($resultados)-1]['af_glp'];
		$saldo14 = $resultados[count($resultados)-1]['descuentos'];
		$saldo15 = $resultados[count($resultados)-1]['creditognv'];
		$saldo16 = $resultados[count($resultados)-1]['egresos'];
		$saldo17 = $resultados[count($resultados)-1]['otherimp'];
		$saldo18 = $resultados[count($resultados)-1]['promociones'];
		$saldo19 = $resultados[count($resultados)-1]['interbank'];//NUEVO

		$saldo20 = $resultados[count($resultados)-1]['manual_af_comb'];
		$saldo21 = $resultados[count($resultados)-1]['manual_af_glp'];

		$saldo_total = $saldo1 + $saldo2 + $saldo3 + $saldo4 + $saldo5 + $saldo18 + $saldo11 - (empty($saldo12) ? $saldo20 : $saldo12) - (empty($saldo13) ? $saldo21 : $saldo13) + $saldo14;
		$saldo_final = $saldo_total - $saldo6 - $saldo7 - $saldo8 - $saldo9 - $saldo10 - $saldo + $saldo14 + $saldo15 + $saldo16 + $saldo17 + $saldo18;

		$saldo_acu = 0;
		$saldo_market = 0;

		$sum_comb 	= 0;
		$sum_gnv 	= 0;
		$sum_glp 	= 0;
		$sum_lubri 	= 0;
		$sum_otros 	= 0;
		$sum_promo 	= 0;
		$sum_total 	= 0;
		$sum_cli 	= 0;
		$sum_tar 	= 0;
		$sum_bcp 	= 0;
		$sum_bbva 	= 0;
		$sum_scot 	= 0;
		$sum_inter 	= 0;
		$sum_falta 	= 0;
		$sum_sobra 	= 0;
		$sum_faclubri 	= 0;
		$sum_descuentos = 0;
		$sum_creditognv = 0;
		$sum_faltagnv 	= 0;
		$sum_sobragnv 	= 0;
		$sum_egre 	= 0;
		$sum_other 	= 0;

		//Get Class
		$objModelCajaBanco = new CajaBancoModel();

		//Si el mes es Enero, debemos de mostrar Diciembre, ya que el sistema debe de verificar si existe saldo en el mes anterior
		if ($dMonth == '01') {
			$dYear = $dYear - 1;
			$dMonth = '12';
		} else
			$dMonth = $dMonth - 1;

        // Mostrar saldo inicial por mes
		$arrData = array(
			'Nu_Warehouse' => $iAlmacen,
			'Fe_Validate_Previous_Year' => $dYear,
			'Fe_Validate_Previous_Month' => $dMonth,
		);
        $arrResponse = $objModelCajaBanco->getBalance($arrData);
		$result .= '<tr>';
		$result .= '<td class="grid_detalle_especial" colspan="16"></td>';
		$result .= '<td class="grid_detalle_especial" align = "right">Saldo Inicial</td>';
		$result .= '<td class="grid_detalle_especial" align = "right">' . htmlentities(number_format($arrResponse['fSaldoInicial'], 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		$saldo_acu += (float)$arrResponse['fSaldoInicial'];
		// ./ Saldo Inicial por Mes

		// Guardar saldo por mes
		$fFinalBalanceAmount = 0.00;

		for ($i = 0; $i < count($resultados); $i++) {

			$color	= ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

			$a 	= $resultados[$i];

			if(empty($a['af_comb']))
				$data_af_comb = $a['manual_af_comb'];
			else
				$data_af_comb = $a['af_comb'];

			if(empty($a['af_glp']))
				$data_af_glp = $a['manual_af_glp'];
			else
				$data_af_glp = $a['af_glp'];

			$sum_comb 	= $sum_comb + $a['total_venta_comb'] - $data_af_comb;
			$sum_gnv 	= $sum_gnv + $a['total_venta_gnv'];
			$sum_glp 	= $sum_glp + $a['total_venta_glp'] - $data_af_glp;
			$sum_lubri 	= $sum_lubri + $a['lubricantes'];
			$sum_otros 	= $sum_otros + $a['otros'];
			$sum_promo 	= $sum_promo + $a['promociones'];
			$sum_total 	= $sum_total + $a['total_venta_comb'] - $data_af_comb + $a['total_venta_gnv'] + $a['total_venta_glp'] - $data_af_glp + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones'] + $a['descuentos'];
			$sum_cli 	= $sum_cli + $a['clientescredito'];
			$sum_tar 	= $sum_tar + $a['tarjetascredito'];
			$sum_bcp 	= $sum_bcp + $a['bcp'];
			$sum_bbva 	= $sum_bbva + $a['bbva'];
			$sum_scot 	= $sum_scot + $a['scotiabank'];
			$sum_inter 	= $sum_inter + $a['interbank'];
			$sum_falta 	= $sum_falta + $a['faltante'];
			$sum_sobra 	= $sum_sobra + $a['sobrante'];
			$sum_faclubri 	= $sum_faclubri + $a['facimporte'];
			$sum_descuentos = $sum_descuentos + $a['descuentos'];
			$sum_creditognv = $sum_creditognv + $a['creditognv'];
			$sum_faltagnv 	= $sum_faltagnv + $a['faltagnv'];
			$sum_sobragnv 	= $sum_sobragnv + $a['sobragnv'];
			$sum_egre 	= $sum_egre + $a['egresos'];
			$sum_other 	= $sum_other + $a['otherimp'];

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['fecha']) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total_venta_comb'] - $data_af_comb, 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['descuentos'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total_venta_gnv'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total_venta_glp'] - $data_af_glp, 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['lubricantes'] + $a['facimporte'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['otros'] + $a['promociones'] , 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total_venta_comb'] - $data_af_comb + $a['total_venta_gnv'] + $a['total_venta_glp'] - $data_af_glp + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones'] + $a['descuentos'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['clientescredito'] + $a['creditognv'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['tarjetascredito'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['egresos'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['faltante'] + $a['sobrante'] + $a['sobragnv'] - $a['faltagnv'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['bcp'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['bbva'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['scotiabank'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['interbank'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['otherimp'], 2, '.', ',')) . '</td>';

			$saldo_acu += $a['total_venta_comb'] - $data_af_comb + $a['total_venta_gnv'] + $a['total_venta_glp'] - $data_af_glp + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones'] - $a['clientescredito'] - $a['creditognv'] - $a['tarjetascredito'] - $a['egresos'] + ($a['faltante'] + $a['sobrante'] + $a['sobragnv'] - $a['faltagnv']) - $a['bcp'] - $a['bbva'] - $a['scotiabank'] - $a['interbank'] + $a['descuentos']  + $a['otherimp'];
			$saldo_market += ($a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones']);

			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($saldo_acu, 2, '.', ',')) . '</td>';
			$result .= '</tr>';

			//Obtener el saldo del último día del mes, implementado GLLE
			$dFormatDate = DateTime::createFromFormat('d/m/Y', $a['fecha']);
			$dDate = $dFormatDate->format('Y-m-d');
			//Obtener el último día del mes
            $dFinalBalanceDate = date("Y-m-t", strtotime($dDate));

			if ($dDate == $dFinalBalanceDate){
				//Validando si es el primer mes de cada año
				$arrDate = explode("-", $dFinalBalanceDate);
				$_iYear = $arrDate[0];
				$_iMonth = $arrDate[1];

				$fFinalBalanceAmount = round($saldo_acu, 4);

				$arrData = array(
					'Nu_Warehouse' => $iAlmacen,
					'Fe_Validate_Previous_Year' => $dYear,
					'Fe_Validate_Previous_Month' => $dMonth,
					'Fe_Final_Balance' => $dFinalBalanceDate,
					'Ss_Final_Balance' => $fFinalBalanceAmount,
				);
				$arrResponse = $objModelCajaBanco->saveFinalBalance($arrData);
				unset($arrData);
				echo "<script>alert('" . $arrResponse['message'] . "');</script>";
			}
		}

		$result .= '<tr>';
		$result .= '<td class="bgcolor_cabecera" align = "right">TOTAL: </td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_comb, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_descuentos, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_gnv, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_glp, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_lubri + $sum_faclubri, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_otros + $sum_promo, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_total, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_cli + $sum_creditognv, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_tar, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_egre, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_sobra + $sum_falta + $sum_sobragnv + $sum_faltagnv, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_bcp, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_bbva, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_scot, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_inter, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($sum_other, 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align = "right"></td>';
		$result .= '</tr>';

		//SALDO ESTACION
		$saldo_combu	= ($sum_comb+$sum_descuentos+$sum_gnv+$sum_glp - ($sum_cli + $sum_creditognv) - $sum_tar + ($sum_sobra + $sum_falta + $sum_sobragnv - $sum_faltagnv) - $sum_bcp - $sum_bbva - $sum_scot - $sum_inter - $sum_egre);

		$result .= '<tr bgcolor=""><td> </td></tr>';
		$result .= '<tr bgcolor=""><td> </td></tr>';
		$result .= '<tr bgcolor=""><td> </td></tr>';

		$result .= '</table>';

		$result .= '<table align="center" border = "0.5px" style="background:#FFFFFF">';
		$result .= '<tr bgcolor="">';
		$result .= '<td class="bgcolor_cabecera">SALDO DE ESTACION: </td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($saldo_combu, 2, '.', ',')) . '</td>';
		$result .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
		$result .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
		$result .= '<td class="bgcolor_cabecera">SALDO DE MARKET: </td>';
		$result .= '<td class="bgcolor_cabecera" align = "right">' . htmlentities(number_format($saldo_market, 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		$result .= '</table>';

		return $result;
    }

    function reportePDF($res, $dYear, $dMonth, $sNameWarehouse, $sWarehouse) {
		if($dMonth == '01')
			$sMonth = 'Enero';
		elseif($dMonth == '02')
			$sMonth = 'Febrero';
		elseif($dMonth == '03')
			$sMonth = 'Marzo';
		elseif($dMonth == '04')
			$sMonth = 'Abril';
		elseif($dMonth == '05')
			$sMonth = 'Mayo';
		elseif($dMonth == '06')
			$sMonth = 'Junio';
		elseif($dMonth == '07')
			$sMonth = 'Julio';
		elseif($dMonth == '08')
			$sMonth = 'Agosto';
		elseif($dMonth == '09')
			$sMonth = 'Setiembre';
		elseif($dMonth == '10')
			$sMonth = 'Octubre';
		elseif($dMonth == '11')
			$sMonth = 'Noviembre';
		elseif($dMonth == '12')
			$sMonth = 'Diciembre';

    	$cab = array(
			"fecha"		=>	"FECHA",
			"gasolina"	=>	"VENTA GASOLINA",
			"descuentos" =>	"DESC. / INCRE.",
			"gnv"		=>	"VENTA GNV",
			"glp"		=>	"VENTA GLP",
			"lubricantes" => "LUBRICANTES",
			"otros"		=>	"PROMOCIONES",
			"total"		=>	"VENTA TOTAL",
			"creditos"	=>	"CREDITOS",
			"tarjetas"	=>	"TARJETAS",
			"egresos"	=>	"EGRESOS",
			"sobfal"	=>	"SOB. / FALT.",
			"bcp"		=>	"BCP",
			"bbva"		=>	"BBVA",
			"scot"		=>	"SCOT.B",
			"inter"		=>	"INTER.B",
			"oingresos"	=>	"OTROS INGRESOS",
			"saldo"		=>	"SALDO"
		);

		$reporte = new CReportes2("P","pt","A3");

		$reporte->Ln();	 
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabeceraSize(3, "L", "courier,B,13", "                                           REPORTE DE CAJA Y BANCO");
		$reporte->definirCabecera(4, "L", "ALMACEN: " . $sNameWarehouse);
		$reporte->definirCabecera(5, "L", "MES: " . $sMonth . " DEL " . $dYear);
		$reporte->definirCabecera(6, "L", "");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 6.5);

		$reporte->definirColumna("fecha",$reporte->TIPO_TEXTO,10,"C", "_pri");
		$reporte->definirColumna("gasolina",$reporte->TIPO_TEXTO,15,"R", "_pri");
		$reporte->definirColumna("descuentos",$reporte->TIPO_TEXTO,15,"R", "_pri");
		$reporte->definirColumna("gnv",$reporte->TIPO_TEXTO,10,"R", "_pri");
		$reporte->definirColumna("glp",$reporte->TIPO_TEXTO,15,"R", "_pri");
		$reporte->definirColumna("lubricantes",$reporte->TIPO_TEXTO,12,"R", "_pri");
		$reporte->definirColumna("otros",$reporte->TIPO_TEXTO,8,"R", "_pri");
		$reporte->definirColumna("total",$reporte->TIPO_IMPORTE,12,"R", "_pri");
		$reporte->definirColumna("creditos",$reporte->TIPO_TEXTO,10,"R", "_pri");
		$reporte->definirColumna("tarjetas",$reporte->TIPO_TEXTO,10,"R", "_pri");
		$reporte->definirColumna("egresos",$reporte->TIPO_TEXTO,10,"R", "_pri");
		$reporte->definirColumna("sobfal",$reporte->TIPO_TEXTO,10,"R", "_pri");
		$reporte->definirColumna("bcp",$reporte->TIPO_TEXTO,8,"R", "_pri");
		$reporte->definirColumna("bbva",$reporte->TIPO_IMPORTE,8,"R", "_pri");
		$reporte->definirColumna("scot",$reporte->TIPO_IMPORTE,8,"R", "_pri");
		$reporte->definirColumna("inter",$reporte->TIPO_IMPORTE,8,"R", "_pri");
		$reporte->definirColumna("oingresos",$reporte->TIPO_IMPORTE,12,"R", "_pri");
		$reporte->definirColumna("saldo",$reporte->TIPO_TEXTO,12,"L", "_pri");

		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab, "_pri");
		$reporte->AddPage();
		$reporte->Ln();	

		// Saldo
		$saldo_acu = 0;
		$saldo_combu = 0;
		$saldo_market = 0;

		$sum_comb 	= 0;
		$sum_gnv 	= 0;
		$sum_glp 	= 0;
		$sum_lubri 	= 0;
		$sum_otros 	= 0;
		$sum_promo 	= 0;
		$sum_total 	= 0;
		$sum_cli 	= 0;
		$sum_tar 	= 0;
		$sum_bcp 	= 0;
		$sum_bbva 	= 0;
		$sum_scot 	= 0;
		$sum_inter 	= 0;
		$sum_falta 	= 0;
		$sum_sobra 	= 0;
		$sum_faclubri 	= 0;
		$sum_descuentos = 0;
		$sum_creditognv = 0;
		$sum_faltagnv 	= 0;
		$sum_sobragnv 	= 0;
		$sum_egre 	= 0;
		$sum_other 	= 0;

		//Get Class
		$objModelCajaBanco = new CajaBancoModel();
		//Si el mes es Enero, debemos de mostrar Diciembre, ya que el sistema debe de verificar si existe saldo en el mes anterior
		if ($dMonth == '01') {
			$dYear = $dYear - 1;
			$dMonth = '12';
		} else
			$dMonth = $dMonth - 1;

        // Mostrar saldo inicial por mes
		$arrData = array(
			'Nu_Warehouse' => $sWarehouse,
			'Fe_Validate_Previous_Year' => $dYear,
			'Fe_Validate_Previous_Month' => $dMonth,
		);
        $arrResponse = $objModelCajaBanco->getBalance($arrData);
		$saldo_acu += (float)$arrResponse['fSaldoInicial'];

		$arrSaldoInicial = array(
			"oingresos"	=> "SALDO DE ",
			"saldo"	=> (float)$arrResponse['fSaldoInicial'],
		);
		$reporte->nuevaFila($arrSaldoInicial, "_pri");

		for ($i = 0; $i<count($res); $i++) {
			$a = $res[$i];
			$sum_comb = $sum_comb + $a['total_venta_comb'] - $a['af_comb'];
			$sum_gnv = $sum_gnv + $a['total_venta_gnv'];
			$sum_glp = $sum_glp + $a['total_venta_glp'] - $a['af_glp'];
			$sum_lubri = $sum_lubri + $a['lubricantes'];
			$sum_otros = $sum_otros + ($a['otros'] + $a['promociones']);
			$sum_total = $sum_total + $a['total_venta_comb'] - $a['af_comb'] + $a['total_venta_gnv'] + $a['total_venta_glp'] - $a['af_glp'] + $a['lubricantes'] + $a['otros'];
			$sum_cli = $sum_cli + $a['clientescredito'];
			$sum_tar = $sum_tar + $a['tarjetascredito'];
			$sum_bcp = $sum_bcp + $a['bcp'];
			$sum_bbva = $sum_bbva + $a['bbva'];
			$sum_scot = $sum_scot + $a['scotiabank'];
			$sum_inter = $sum_inter + $a['interbank'];
			$sum_falta = $sum_falta + $a['faltante'];
			$sum_sobra = $sum_sobra + $a['sobrante'];
			$sum_faclubri = $sum_faclubri + $a['facimporte'];
			$sum_descuentos = $sum_descuentos + $a['descuentos'];
			$sum_creditognv = $sum_creditognv + $a['creditognv'];
			$sum_faltagnv = $sum_faltagnv + $a['faltagnv'];
			$sum_sobragnv = $sum_sobragnv + $a['sobragnv'];
			$sum_egre = $sum_egre + $a['egresos'];
			$sum_other = $sum_other + $a['otherimp'];

			$saldo_acu += $a['total_venta_comb'] - $data_af_comb + $a['total_venta_gnv'] + $a['total_venta_glp'] - $data_af_glp + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones'] - $a['clientescredito'] - $a['creditognv'] - $a['tarjetascredito'] - $a['egresos'] + ($a['faltante'] + $a['sobrante'] - $a['faltagnv'] + $a['sobragnv']) - $a['bcp'] - $a['bbva'] - $a['scotiabank'] - $a['interbank'] + $a['descuentos']  + $a['otherimp'];
			$saldo_market += ($a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones']);

			$arr = array(
				"fecha"		=>$a['fecha'],
				"gasolina"	=>$a['total_venta_comb'] - $a['af_comb'], 
				"descuentos"	=>$a['descuentos'], 
				"gnv"		=>$a['total_venta_gnv'], 
				"glp"		=>$a['total_venta_glp'] - $a['af_glp'],
				"lubricantes"	=>$a['lubricantes'] + $a['facimporte'], 
				"otros"		=>$a['otros'] + $a['promociones'], 
				"total"		=>$a['total_venta_comb'] - $a['af_comb'] + $a['total_venta_gnv'] + $a['total_venta_glp'] - $a['af_glp'] + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['descuentos'], 
				"creditos"	=>$a['clientescredito'] + $a['creditognv'], 
				"tarjetas"	=>$a['tarjetascredito'], 
				"egresos"	=>$a['egresos'], 
				"sobfal"	=>$a['faltante'] + $a['sobrante'] - $a['faltagnv'] + $a['sobragnv'], 
				"bcp"		=>$a['bcp'], 
				"bbva"		=>$a['bbva'],
				"scot"		=>$a['scotiabank'],
				"inter"		=>$a['interbank'],
				"oingresos"	=>$a['otherimp'],
				"saldo"		=>$saldo_acu, 
			);

			$suma = array(
				"fecha"		=> "TOTAL: ",
				"gasolina"	=>$sum_comb, 
				"descuentos"	=>$sum_descuentos,
				"gnv"		=>$sum_gnv, 
				"glp"		=>$sum_glp, 
				"lubricantes"	=>$sum_lubri + $sum_faclubri, 
				"otros"		=>$sum_otros,  
				"total"		=>$sum_total, 
				"creditos"	=>$sum_cli + $sum_creditognv,  
				"tarjetas"	=>$sum_tar,  
				"egresos"	=>$sum_egre,
				"sobfal"	=>$sum_falta + $sum_sobra - $sum_faltagnv + $sum_sobragnv,
				"bcp"		=>$sum_bcp, 
				"bbva"		=>$sum_bbva, 
				"scot"		=>$sum_scot,
				"inter"		=>$sum_inter,
				"oingresos"	=>$sum_other,
			);
			$reporte->nuevaFila($arr, "_pri");
		}
		$reporte->nuevaFila("                             ");
		$reporte->lineaH();
		$reporte->nuevaFila($suma, "_pri");
		$reporte->lineaH();
/*
		$reporte->nuevaFila("                              ");
		$reporte->lineaH();
		$reporte->nuevaFila("                             ");
		$reporte->nuevaFila($suma, "_pri");
		$reporte->nuevaFila("                              ");
		$reporte->lineaH();
		$reporte->nuevaFila("                             ");

		$reporte->nuevaFila("                              ");
		$reporte->nuevaFila($saldo, "_pri");
		$reporte->nuevaFila("                             ");
		$reporte->nuevaFila($sobra, "_pri");
		$reporte->nuevaFila("                              ");
		$reporte->nuevaFila($falta, "_pri");
		$reporte->nuevaFila("                             ");
		$reporte->nuevaFila($total, "_pri");
*/

		$reporte->nuevaFila("                             ");
		//SALDO ESTACION
		$saldo_combu	= ($sum_comb+$sum_descuentos+$sum_gnv+$sum_glp - ($sum_cli + $sum_creditognv) - $sum_tar + ($sum_sobra + $sum_falta + $sum_sobragnv - $sum_faltagnv) - $sum_bcp - $sum_bbva - $sum_scot - $sum_inter - $sum_egre);

		$arrSaldosFinales = array(
			"gasolina"	=> "ESTACION: ",
			"descuentos" =>$saldo_combu,
			"bcp"		=> "MARKET: ",
			"bbva"		=>$saldo_market,
		);
		$reporte->nuevaFila($arrSaldosFinales, "_pri");

		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();
		$reporte->Lnew();				
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/cajaybanco.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/cajaybanco.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}
    
	function reporteExcel($res, $dYear, $dMonth, $sNameWarehouse, $sWarehouse) {
		if($dMonth == '01')
			$sMonth = 'Enero';
		elseif($dMonth == '02')
			$sMonth = 'Febrero';
		elseif($dMonth == '03')
			$sMonth = 'Marzo';
		elseif($dMonth == '04')
			$sMonth = 'Abril';
		elseif($dMonth == '05')
			$sMonth = 'Mayo';
		elseif($dMonth == '06')
			$sMonth = 'Junio';
		elseif($dMonth == '07')
			$sMonth = 'Julio';
		elseif($dMonth == '08')
			$sMonth = 'Agosto';
		elseif($dMonth == '09')
			$sMonth = 'Setiembre';
		elseif($dMonth == '10')
			$sMonth = 'Octubre';
		elseif($dMonth == '11')
			$sMonth = 'Noviembre';
		elseif($dMonth == '12')
			$sMonth = 'Diciembre';

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 16);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);
		$worksheet1->set_column(16, 16, 20);
		$worksheet1->set_column(17, 17, 15);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(0, 5, "REPORTE DE CAJA Y BANCO",$formato0);
		$worksheet1->write_string(1, 4, "ALMACEN: " . $sNameWarehouse, $formato0);
		$worksheet1->write_string(1, 6, "MES: " . $sMonth . " DEL " . $dYear, $formato0);

		$e = 3;

		$worksheet1->write_string($e, 0, "FECHA",$formato2);
		$worksheet1->write_string($e, 1, "VENTA GASOLINA",$formato2);
		$worksheet1->write_string($e, 2, "DESC. / INCRE.",$formato2);
		$worksheet1->write_string($e, 3, "VENTA GNV",$formato2);
		$worksheet1->write_string($e, 4, "VENTA GLP",$formato2);
		$worksheet1->write_string($e, 5, "LUBRICANTES",$formato2);
		$worksheet1->write_string($e, 6, "PROMOCIONES",$formato2);
		$worksheet1->write_string($e, 7, "VENTA TOTAL",$formato2);	
		$worksheet1->write_string($e, 8, "CREDITOS",$formato2);
		$worksheet1->write_string($e, 9, "TARJETAS",$formato2);
		$worksheet1->write_string($e, 10, "EGRESOS",$formato2);
		$worksheet1->write_string($e, 11, "SOB. / FALT.",$formato2);
		$worksheet1->write_string($e, 12, "BCP",$formato2);
		$worksheet1->write_string($e, 13, "BBVA",$formato2);
		$worksheet1->write_string($e, 14, "SCOTIABANK",$formato2);
		$worksheet1->write_string($e, 15, "INTERBANK",$formato2);
		$worksheet1->write_string($e, 16, "OTROS INGRESOS",$formato2);
		$worksheet1->write_string($e, 17, "SALDO",$formato2);

		// Saldo
		$saldo_acu = 0;
		$saldo_combu = 0;
		$saldo_market = 0;

		$sum_comb 	= 0;
		$sum_gnv 	= 0;
		$sum_glp 	= 0;
		$sum_lubri 	= 0;
		$sum_otros 	= 0;
		$sum_promo 	= 0;
		$sum_total 	= 0;
		$sum_cli 	= 0;
		$sum_tar 	= 0;
		$sum_bcp 	= 0;
		$sum_bbva 	= 0;
		$sum_scot 	= 0;
		$sum_inter 	= 0;
		$sum_falta 	= 0;
		$sum_sobra 	= 0;
		$sum_faclubri 	= 0;
		$sum_descuentos = 0;
		$sum_creditognv = 0;
		$sum_faltagnv 	= 0;
		$sum_sobragnv 	= 0;
		$sum_egre 	= 0;
		$sum_other 	= 0;

		//Get Class
		$objModelCajaBanco = new CajaBancoModel();
		//Si el mes es Enero, debemos de mostrar Diciembre, ya que el sistema debe de verificar si existe saldo en el mes anterior
		if ($dMonth == '01') {
			$dYear = $dYear - 1;
			$dMonth = '12';
		} else
			$dMonth = $dMonth - 1;

        // Mostrar saldo inicial por mes
		$arrData = array(
			'Nu_Warehouse' => $sWarehouse,
			'Fe_Validate_Previous_Year' => $dYear,
			'Fe_Validate_Previous_Month' => $dMonth,
		);
        $arrResponse = $objModelCajaBanco->getBalance($arrData);
		$saldo_acu += (float)$arrResponse['fSaldoInicial'];

		$e=4;
		$worksheet1->write_string($e, 16, "SALDO INICIAL: ", $formato5);
		$worksheet1->write_number($e, 17, number_format((float)$arrResponse['fSaldoInicial'], 2, '.', ''), $formato5);	

		$e=5;//Fila

		for ($j=0; $j<count($res); $j++) {
			$a = $res[$j];
			$sum_comb = $sum_comb + $a['total_venta_comb'] - $a['af_comb'];
			$sum_gnv = $sum_gnv + $a['total_venta_gnv'];
			$sum_glp = $sum_glp + $a['total_venta_glp'] - $a['af_glp'];
			$sum_lubri = $sum_lubri + $a['lubricantes'];
			$sum_otros = $sum_otros + ($a['otros'] + $a['promociones']);
			$sum_total = $sum_total + $a['total_venta_comb'] - $a['af_comb'] + $a['total_venta_gnv'] + $a['total_venta_glp'] - $a['af_glp'] + $a['lubricantes'] + $a['otros'];
			$sum_cli = $sum_cli + $a['clientescredito'];
			$sum_tar = $sum_tar + $a['tarjetascredito'];
			$sum_bcp = $sum_bcp + $a['bcp'];
			$sum_bbva = $sum_bbva + $a['bbva'];
			$sum_scot = $sum_scot + $a['scotiabank'];
			$sum_inter = $sum_inter + $a['interbank'];
			$sum_falta = $sum_falta + $a['faltante'];
			$sum_sobra = $sum_sobra + $a['sobrante'];
			$sum_faclubri = $sum_faclubri + $a['facimporte'];
			$sum_descuentos = $sum_descuentos + $a['descuentos'];
			$sum_creditognv = $sum_creditognv + $a['creditognv'];
			$sum_faltagnv = $sum_faltagnv + $a['faltagnv'];
			$sum_sobragnv = $sum_sobragnv + $a['sobragnv'];
			$sum_egre = $sum_egre + $a['egresos'];
			$sum_other = $sum_other + $a['otherimp'];

			$saldo_acu += $a['total_venta_comb'] - $a['af_comb'] + $a['total_venta_gnv'] + $a['total_venta_glp'] - $a['af_glp'] + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones'] - $a['clientescredito'] - $a['creditognv'] - $a['tarjetascredito'] - $a['egresos'] + ($a['faltante'] + $a['sobrante'] - $a['faltagnv'] + $a['sobragnv']) - $a['bcp'] - $a['bbva'] - $a['scotiabank'] - $a['interbank'] + $a['descuentos']  + $a['otherimp'];

			$saldo_combu	= ($sum_comb+$sum_descuentos+$sum_gnv+$sum_glp - ($sum_cli + $sum_creditognv) - $sum_tar + ($sum_sobra + $sum_falta + $sum_sobragnv - $sum_faltagnv) - $sum_bcp - $sum_bbva - $sum_inter - $sum_scot - $sum_egre);
			$saldo_market	= $saldo_market + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones'];

			$worksheet1->write_string($e, 0, $a['fecha'],$formato5);
			$worksheet1->write_number($e, 1, number_format($a['total_venta_comb'] - $a['af_comb'], 2, '.', ''),$formato5);	
			$worksheet1->write_number($e, 2, number_format($a['descuentos'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 3, number_format($a['total_venta_gnv'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 4, number_format($a['total_venta_glp'] - $a['af_glp'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 5, number_format($a['lubricantes'] + $a['facimporte'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 6, number_format($a['otros'] + $a['promociones'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 7, number_format($a['total_venta_comb'] - $a['af_comb'] + $a['total_venta_gnv'] + $a['total_venta_glp'] - $a['af_glp'] + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['descuentos'],2,'.',''),$formato5);	
			$worksheet1->write_number($e, 8, number_format($a['clientescredito'] + $a['creditognv'],2,'.',''),$formato5);	
			$worksheet1->write_number($e, 9, number_format($a['tarjetascredito'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 10, number_format($a['egresos'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 11, number_format($a['faltante'] + $a['sobrante'] + $a['faltagnv'] + $a['sobragnv'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 12, number_format($a['bcp'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 13, number_format($a['bbva'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 14, number_format($a['scotiabank'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 15, number_format($a['interbank'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 16, number_format($a['otherimp'],2,'.',''),$formato5);
			$worksheet1->write_number($e, 17, number_format($saldo_acu,2,'.',''),$formato5);
			$e++;
		}

		$worksheet1->write_string($e+1, 0, "TOTAL: ",$formato2);
		$worksheet1->write_number($e+1, 1, number_format($sum_comb, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 2, number_format($sum_descuentos, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 3, number_format($sum_gnv, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 4, number_format($sum_glp, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 5, number_format($sum_lubri, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 6, number_format($sum_otros, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 7, number_format($sum_total, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 8, number_format($sum_cli + $sum_creditognv, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 9, number_format($sum_tar, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 10, number_format($sum_egre, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 11, number_format($sum_falta + $sum_sobra - $sum_faltagnv + $sum_sobragnv, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 12, number_format($sum_bcp, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 13, number_format($sum_bbva, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 14, number_format($sum_scot, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 15, number_format($sum_inter, 2, '.', ''),$formato5);
		$worksheet1->write_number($e+1, 16, number_format($sum_other, 2, '.', ''),$formato5);
	
		// CUADRO FINAL

		$worksheet1->write_string($e+3, 0, "SALDO DE ESTACION: ",$formato2);
		$worksheet1->write_number($e+3, 1, number_format($saldo_combu, 2, '.', ''),$formato5);
		$worksheet1->write_string($e+3, 4, "SALDO DE MARKET: ",$formato2);
		$worksheet1->write_number($e+3, 5, number_format($saldo_market, 2, '.', ''),$formato5);

		$workbook->close();	

		$chrFileName = "cajaybanco";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}
