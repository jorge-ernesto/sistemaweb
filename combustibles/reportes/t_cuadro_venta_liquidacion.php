<?php
class CuadroVentaLiquidacionTemplate extends Template {
	function getTitulo() {
		return '<h2 align="center"><b>Reporte Diario - Ventas del Día</b></h2>';
    }
    
	function formPrincipal($arrAlmacenes, $iAlmacen, $dInicial, $dFinal, $dCierre) {

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.CUADROVENTALIQUIDACION'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dCierre . '">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacen</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iAlmacen', '', $iAlmacen, $arrAlmacenes, espacios(3), array("onfocus" => "getFechasIF();getDatos();")));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dInicial', '', $dInicial, '', 12, 10));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dFinal', '', $dFinal, '', 12, 10));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button id="btn-pdf" name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button type="submit" name="action" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button>'));					
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("cbo-iAlmacen").focus();}</script>'));
		return $form->getForm();
    }

    function gridViewHTML(
	  $arrPOST,
	  $arrResponseConsolidacion,
	  $arrResult,
	  $arrResultDetalleLiquidacionNDCredito,
	  $arrResultDetalleLiquidacionTCRED,
	  $arrResultDetalleLiquidacionEGRESOS,
	  $arrResultDetalleLiquidacionINGRESOS,
	  $arrResultDetalleNDCredito,

	  $arrResultControlInventario,
	  $arrResultControlInventario_GASOHOL84,
	  $arrResultControlInventario_GASOGOL90,
	  $arrResultControlInventario_GASOGOL97,
	  $arrResultControlInventario_DIESELB5,
	  $arrResultControlInventario_GASOHOL95,
	  $arrResultControlInventario_GLP,

	  $arrResultDetalleDepositosValidados,
	  $arrResultDetalleBBVA,
	  $arrResultDetalleBCP,
	  $arrResultDetalleScotiabank,
	  $arrResultDetalleInterbank,
	  $arrResultObtieneMarket
	){
		$result = '';
		$result .= '<div class="table__wrapper StandardTable">';
		
		$result .= '<table border="0" align="center">';
		$result .= '<tbody>';
		$result .= '<tr style="' . $arrResponseConsolidacion["sCssStyle"] . '">';
		if ( $arrResponseConsolidacion["sStatus"]=='success' ) {
			$sStyleColor='font-size:12px; color: green;';
			$sLabel='';
			if ( $arrResponseConsolidacion['arrData'][0]["dia"]<$arrPOST['dBusqueda'] ) {
				$sStyleColor='font-size:12px; color: red;';
				$sLabel='Ultima fecha consolidada';
			}
			if (!empty($sLabel) ) {
				$result .= '<tr style="' . $arrResponseConsolidacion["sCssStyle"] . '">';
					$result .= '<td align="center" colspan="7" style="font-weight:bold;'.$sStyleColor.'">'.$sLabel.'</td>';
				$result	.= '</tr>';
			}
			$result .= '<td colspan="7" align="center" style="font-weight:bold;'.$sStyleColor.'">Dia: '.$arrResponseConsolidacion['arrData'][0]["dia"].' Turno: '.$arrResponseConsolidacion['arrData'][0]["turno"].'</td>';
		} else {
			$result .= '<td colspan="7" align="center" style="font-size:12px;font-weight:bold;">'.$arrResponseConsolidacion['sMessage'].'</td>';
		}
		$result .= '</tr>';
		$result .= '<tr>';
			$result .= '<td align="center" colspan="7">&nbsp;</td>';
		$result	.= '</tr>';
		$result .= '</tbody>';
		$result .= '</table>';

		/*
		Cuadro N° 1 - Venta de Combustible y Otros
		*/
		$result .= '<table border="0" align="center" class="report_CRUD" id="tblData">';
			$result .= '<thead>';
				$result .= '<tr bgcolor="#FFFFCD">';
					$result .= '<th class="grid_cabecera" colspan="7">VENTAS DE COMBUSTIBLES Y OTROS - cuadro N° 1</th>';
				$result .= '</tr>';
				$result .= '<tr bgcolor="#FFFFCD">';
					$result .= '<th class="grid_cabecera">PRODUCTO</th>';
					$result .= '<th class="grid_cabecera">GALONES</th>';
					$result .= '<th class="grid_cabecera" title="Si hubo variación de precio, se calculará un precio promedio">P.U.</th>';
					$result .= '<th class="grid_cabecera">IMPORTE</th>';
					$result .= '<th class="grid_cabecera">DESCUENTOS / INCREMENTOS</th>';
					$result .= '<th class="grid_cabecera">AFERICION (-)</th>';
					$result .= '<th class="grid_cabecera">TOTAL</th>';
				$result .= '</tr>';
			$result .= '</thead>';
			$result .= '<tbody>';
				$iCounter=0;
				$fTotNeto=0.00;
				$fTotCuadro1Quantity84=0.00;
				$fTotCuadro1Quantity90=0.00;
				$fTotCuadro1Quantity97=0.00;
				$fTotCuadro1QuantityD2=0.00;
				$fTotCuadro1Quantity95=0.00;
				$fTotCuadro1QuantityGLP=0.00;
				/*Agregado 2020-01-07*/
				$fTotNeto84 = 0.00;
				$fTotNeto90 = 0.00;
				$fTotNeto97 = 0.00;
				$fTotNetoD2 = 0.00;
				$fTotNeto95 = 0.00;
				$fTotNetoGLP = 0.00;
				/***/
				foreach ($arrResult['arrData'] as $row) {
					$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
						$result .= '<td align="left">' . htmlentities($row["id_item"]) . ' - ' . htmlentities($row["no_item"]) . '</td>';
						$result .= '<td align="right">' . htmlentities(number_format($row["qt_total"], '3', '.', ',')) . '</td>';						
						$result .= '<td align="right">' . htmlentities(number_format($row["ss_precio"], '2', '.', ',')) . '</td>';
						$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '</td>';						
						$result .= '<td align="right">' . htmlentities(number_format($row["ss_descuentos"], '2', '.', ',')) . '</td>';						
						$result .= '<td align="right">' . htmlentities(number_format($row["ss_total_afericion"], '2', '.', ',')) . '</td>';
						$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"], '2', '.', ',')) . '</td>';
						$fTotNeto+=($row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"]);						
						/*Agregado 2020-01-06*/
						$galones_total += $row["qt_total"];
						$importe_total += $row["ss_total"];
						$descuentos_total += $row["ss_descuentos"];
						$afericion_total += $row["ss_total_afericion"];
						//echo "<script>console.log('" . json_encode($row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"]) . "')</script>";				
						/*****/
						if ( trim($row["id_item"]) == '11620301' ){
							$fTotCuadro1Quantity84+=$row["qt_total"];
							$fTotNeto84 = $row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"]; //Agregado 2020-01-07
						}
						if ( trim($row["id_item"]) == '11620302' ){
							$fTotCuadro1Quantity90+=$row["qt_total"];
							$fTotNeto90 = $row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"]; //Agregado 2020-01-07
						}
						if ( trim($row["id_item"]) == '11620303' ){
							$fTotCuadro1Quantity97+=$row["qt_total"];
							$fTotNeto97 = $row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"]; //Agregado 2020-01-07
						}
						if ( trim($row["id_item"]) == '11620304' ){
							$fTotCuadro1QuantityD2+=$row["qt_total"];
							$fTotNetoD2 = $row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"]; //Agregado 2020-01-07
						}
						if ( trim($row["id_item"]) == '11620305' ){
							$fTotCuadro1Quantity95+=$row["qt_total"];
							$fTotNeto95 = $row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"]; //Agregado 2020-01-07
						}
						if ( trim($row["id_item"]) == '11620307' ){
							$fTotCuadro1QuantityGLP+=$row["qt_total"];
							$fTotNetoGLP = $row["ss_total"] + $row["ss_descuentos"] - $row["ss_total_afericion"]; //Agregado 2020-01-07
						}
				    $result .= '</tr>';
				    ++$iCounter;
				}				
				// echo "<script>console.log('" . json_encode($fTotCuadro1Quantity84) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotCuadro1Quantity90) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotCuadro1Quantity97) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotCuadro1QuantityD2) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotCuadro1Quantity95) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotCuadro1QuantityGLP) . "')</script>";				
				// echo "<script>console.log('')</script>";				
				// echo "<script>console.log('" . json_encode($fTotNeto84) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotNeto90) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotNeto97) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotNetoD2) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotNeto95) . "')</script>";				
				// echo "<script>console.log('" . json_encode($fTotNetoGLP) . "')</script>";				
				//die();
			$result .= '</tbody>';
			$result .= '<tfoot>';
				$result .= '<tr>';
					$result .= '<td align="left"></td>';
					$result .= '<td align="right">' . htmlentities(number_format($galones_total, '3', '.', ',')) . '</td>';
					$result .= '<td align="right"></td>';
					$result .= '<td align="right">' . htmlentities(number_format($importe_total, '2', '.', ',')) . '</td>';
					$result .= '<td align="right">' . htmlentities(number_format($descuentos_total, '2', '.', ',')) . '</td>';
					$result .= '<td align="right">' . htmlentities(number_format($afericion_total, '2', '.', ',')) . '</td>';
					// $result .= '<td align="center" colspan="6">TOTAL NETO</td>';
					$result .= '<td align="right">' . htmlentities(number_format($fTotNeto, '2', '.', ',')) . '</td>';
				$result .= '</tr>';
				$result .= '<tr>';
					$result .= '<td align="center" colspan="7">&nbsp;</td>';
				$result .= '</tr>';
			$result .= '</tfoot>';
		$result .= '</table>';

		/*
		Cuadro N° 2 - Detalle de Liquidación
		*/
		$fTotNDCredito=0.00;
		if ( $arrResultDetalleLiquidacionNDCredito['sStatus']=='success' ){
			$fTotNDCredito=$arrResultDetalleLiquidacionNDCredito['arrData'][0]['ss_total'];
		}
		$fTotEGRESOS=0.00;
		if ( $arrResultDetalleLiquidacionEGRESOS['sStatus']=='success' ){
			$fTotEGRESOS=$arrResultDetalleLiquidacionEGRESOS['arrData'][0]['ss_total'];
		}
		$fTotINGRESOSBANCO=0.00;
		if ( $arrResultDetalleLiquidacionINGRESOS['sStatus']=='success' ){
			$fTotINGRESOSBANCO=$arrResultDetalleLiquidacionINGRESOS['arrData'][0]['ss_total'];
		}

		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<thead>';
				$result .= '<tr bgcolor="#FFFFCD">';
					$result .= '<th class="grid_cabecera" colspan="2">DETALLE - LIQUIDACIÓN - cuadro N° 2</th>';
				$result .= '</tr>';
				$result .= '<tr bgcolor="#FFFFCD">';
					$result .= '<th class="grid_cabecera">DESCRIPCION</th>';
					$result .= '<th class="grid_cabecera">TOTAL</th>';
				$result .= '</tr>';
			$result .= '</thead>';
			$result .= '<tbody>';
				
				/*** Agregado 2020-01-22 ***/
				echo "<script>console.log('Market: " . json_encode($arrResultObtieneMarket) . "')</script>";
				foreach ($arrResultObtieneMarket['propiedades']['ESTACION']['almacenes'] as $key => $value) {
					$total_market = $value['2'] - $value['3'] - $value['4'] - $value['5'] - $value['6'] - $value['7'] - $value['8'] - $value['9'] - $value['10'];
					echo "<script>console.log('" . json_encode($total_market) . "')</script>";
				}
				$fTotNeto = $fTotNeto + $total_market;

				$result .= '<tr class="grid_detalle_par">';
					$result .= '<td align="left">TOTAL MARKET</td>';
					$result .= '<td align="right">' . htmlentities(number_format($total_market, '2', '.', ',')) . '(+)</td>';
				$result .= '</tr>';
				/***/

				$result .= '<tr class="grid_detalle_par">';
					$result .= '<td align="left">TOTAL NETO</td>';
					$result .= '<td align="right">' . htmlentities(number_format($fTotNeto, '2', '.', ',')) . '(-)</td>';
				$result .= '</tr>';				
				$result .= '<tr class="grid_detalle_impar">';
					$result .= '<td align="left">VENTA TOTAL CRÉDITOS</td>';
					$result .= '<td align="right">' . htmlentities(number_format($fTotNDCredito, '2', '.', ',')) . '(-)</td>';
				$result .= '</tr>';
				$iCounter=0;
				$fTotTarjetaCredito=0.00;
				foreach ($arrResultDetalleLiquidacionTCRED['arrData'] as $row) {
					$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
						$result .= '<td align="left">' . htmlentities($row["no_tarjeta_credito"]) . '</td>';
						$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '(-)</td>';
				    $result .= '</tr>';
				    ++$iCounter;
					$fTotTarjetaCredito+=$row["ss_total"];
					$C += $row["ss_total"];
				}
				$A = $fTotNeto;
				$B = $fTotNDCredito;
				$C = $C;
				$deposito_efectivo = $A - $B - $C;
				$result .= '<tr class="grid_detalle_par">';
					$result .= '<td align="left">DEPOSITOS EFECTIVO</td>';
					$result .= '<td align="right">' . htmlentities(number_format($A - $B - $C, '2', '.', ',')) . '(-)</td>';
				$result .= '</tr>';
				$result .= '<tr class="grid_detalle_impar">';
					$result .= '<td align="left">DEPOSITOS VALIDADOS</td>';
					$result .= '<td align="right">' . htmlentities(number_format($arrResultDetalleDepositosValidados['totales']['totsol'], '2', '.', ',')) . '(-)</td>';					
				$result .= '</tr>';
				$result .= '<tr class="grid_detalle_par">';
					$result .= '<td align="left">DIFERENCIA DE DEPOSITOS</td>';
					$result .= '<td align="right">' . htmlentities(number_format($deposito_efectivo - $arrResultDetalleDepositosValidados['totales']['totsol'], '2', '.', ',')) . '(-)</td>';					
				$result .= '</tr>';
				$result .= '<tr class="grid_detalle_impar">';
					$result .= '<td align="left">GASTOS VARIOS</td>';
					$result .= '<td align="right">' . htmlentities(number_format($fTotEGRESOS, '2', '.', ',')) . '(-)</td>';
				$result .= '</tr>';
				$result .= '<tr class="grid_detalle_par">';
					$result .= '<td align="left">DEPOSITOS EFECTIVO (BANCOS)</td>';
					$result .= '<td align="right">' . htmlentities(number_format($fTotINGRESOSBANCO, '2', '.', ',')) . '(-)</td>';
				$result .= '</tr>';
				if($arrResultDetalleBBVA['arrData']['total'] != 0){
					$result .= '<tr class="grid_detalle_par">';
						$result .= '<td align="left">BBVA CONTINENTAL</td>';
						$result .= '<td align="right">' . htmlentities(number_format($arrResultDetalleBBVA['arrData']['total'], '2', '.', ',')) . '(-)</td>';
					$result .= '</tr>';
				}				
				if($arrResultDetalleBCP['arrData']['total'] != 0){
					$result .= '<tr class="grid_detalle_par">';
						$result .= '<td align="left">BCP</td>';
						$result .= '<td align="right">' . htmlentities(number_format($arrResultDetalleBCP['arrData']['total'], '2', '.', ',')) . '(-)</td>';
					$result .= '</tr>';
				}			
				if($arrResultDetalleScotiabank['arrData']['total'] != 0){
					$result .= '<tr class="grid_detalle_par">';
						$result .= '<td align="left">SCOTIABANK</td>';
						$result .= '<td align="right">' . htmlentities(number_format($arrResultDetalleScotiabank['arrData']['total'], '2', '.', ',')) . '(-)</td>';
					$result .= '</tr>';
				}
				if($arrResultDetalleInterbank['arrData']['total'] != 0){
					$result .= '<tr class="grid_detalle_par">';
						$result .= '<td align="left">INTERBANK</td>';
						$result .= '<td align="right">' . htmlentities(number_format($arrResultDetalleInterbank['arrData']['total'], '2', '.', ',')) . '(-)</td>';
					$result .= '</tr>';
				}									
			$result .= '</tbody>';
			$result .= '<tfoot>';
				$result .= '<tr>';
					$result .= '<td align="left">DIFERENCIA DE VENTA</td>';
					$result .= '<td align="right">' . htmlentities(number_format($fTotNeto - ($fTotNDCredito + $fTotTarjetaCredito + $fTotEGRESOS + $fTotINGRESOSBANCO), '2', '.', ',')) . '</td>';
				$result .= '</tr>';
				$result .= '<tr>';
					$result .= '<td align="center" colspan="2">&nbsp;</td>';
				$result .= '</tr>';
			$result .= '</tfoot>';
		$result .= '</table>';

		/*
		Cuadro N° 3 - Detalle de Consumo de Vales
		*/
		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera" colspan="22">VENTAS DETALLADAS CREDITO / CONTADO - cuadro N° 3</th>';
			$result .= '</tr>';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th rowspan="2" class="grid_cabecera">CLIENTE</th>';
				$result .= '<th colspan="3" class="grid_cabecera">COMB. 84</th>';
				$result .= '<th colspan="3" class="grid_cabecera">COMB. 90</th>';
				$result .= '<th colspan="3" class="grid_cabecera">COMB. 97</th>';
				$result .= '<th colspan="3" class="grid_cabecera">COMB. D2</th>';
				$result .= '<th colspan="3" class="grid_cabecera">COMB. 95</th>';
				$result .= '<th colspan="3" class="grid_cabecera">GLP</th>';
				$result .= '<th rowspan="2" class="grid_cabecera">IMP. TOT.</th>';
			$result .= '</tr>';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera" title="P.U. incluido descuento">P.U.</th>';
				$result .= '<th class="grid_cabecera">GAL.</th>';
				$result .= '<th class="grid_cabecera">IMP.</th>';
				$result .= '<th class="grid_cabecera" title="P.U. incluido descuento">P.U.</th>';
				$result .= '<th class="grid_cabecera">GAL.</th>';
				$result .= '<th class="grid_cabecera">IMP.</th>';
				$result .= '<th class="grid_cabecera" title="P.U. incluido descuento">P.U.</th>';
				$result .= '<th class="grid_cabecera">GAL.</th>';
				$result .= '<th class="grid_cabecera">IMP.</th>';
				$result .= '<th class="grid_cabecera" title="P.U. incluido descuento">P.U.</th>';
				$result .= '<th class="grid_cabecera">GAL.</th>';
				$result .= '<th class="grid_cabecera">IMP.</th>';
				$result .= '<th class="grid_cabecera" title="P.U. incluido descuento">P.U.</th>';
				$result .= '<th class="grid_cabecera">GAL.</th>';
				$result .= '<th class="grid_cabecera">IMP.</th>';
				$result .= '<th class="grid_cabecera" title="P.U. incluido descuento">P.U.</th>';
				$result .= '<th class="grid_cabecera">GAL.</th>';
				$result .= '<th class="grid_cabecera">IMP.</th>';
			$result .= '</tr>';
			$result .= '<tbody>';
				if ( $arrResultDetalleNDCredito['sStatus']=='success' ) {
					$iCounter=0;
					$fTotAllItem=0.00;
					$fTotAllItem84=0.00;
					$fTotAllItem90=0.00;
					$fTotAllItem97=0.00;
					$fTotAllItemD2=0.00;
					$fTotAllItem95=0.00;
					$fTotAllItemGLP=0.00;
					$fTotAll=0.00;
					$sIdCliente='';
					$iColspanTotalCliente=0;

					/*Agregado 2020-01-07*/
					$cantidad_para_promediar_84 = 0;
					$cantidad_para_promediar_90 = 0;
					$cantidad_para_promediar_97 = 0;
					$cantidad_para_promediar_D2 = 0;
					$cantidad_para_promediar_95 = 0;					
					$cantidad_para_promediar_GLP = 0;
					/***/
					/************************ CUADRO 3 *************************/
					foreach ($arrResultDetalleNDCredito['arrData'] as $row) {
						$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
						$sTdTotal='No';
						if ($sIdCliente!=trim($row["id_cliente"])) {
							if ( $sPinto84=='Si' ) {
								$iColspanTotalCliente=22; //14
							}
							if ( $sPinto90=='Si' ) {
								$iColspanTotalCliente=20; //12
							}
							if ( $sPinto97=='Si' ) {
								$iColspanTotalCliente=18; //10
							}
							if ( $sPintoD2=='Si' ) {
								$iColspanTotalCliente=16; //8
							}
							if ( $sPinto95=='Si' ) {
								$iColspanTotalCliente=6; //3
							}
							if($iCounter!=0){
								$result .= '<td colspan="'.$iColspanTotalCliente.'" align="right">' . htmlentities(number_format($fTotAllItem, '2', '.', ',')) . '</td>';
							}

							$result .= '<tr class="'. $color. '">';
								$result .= '<td align="left">' . htmlentities($row["no_cliente"]) . '</td>';
								
							$sIdCliente=trim($row["id_cliente"]);
							$sPinto84='No';
							$sPinto90='No';
							$sPinto97='No';
							$sPintoD2='No';
							$sPinto95='No';
							$sPintoGLP='No';
							$fTotAllItem=0;
						}

						++$iCounter;

						if( $sPinto84=='No') {
							$sPinto84='Si';
							if( trim($row['id_item'])=='11620301' ) {
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_precio"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["qt_cantidad"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '</td>';
								$fTotAllItem+=$row["ss_total"];
								$fTotQuantityAllItem84+=$row["qt_cantidad"];
								$fTotAll+=$row["ss_total"];

								$fSsPrecioAllItem84+=$row["ss_precio"]; //Agregado 2020-01-07
								$fSsTotalAllItem84+=$row["ss_total"]; //Agregado 2020-01-07
								$cantidad_para_promediar_84++; //Agregado 2020-01-07
								continue;
							} else {
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
							}
						}
						if( $sPinto90=='No'){
							$sPinto90='Si';
							if( trim($row['id_item'])=='11620302' ) {
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_precio"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["qt_cantidad"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '</td>';
								$fTotAllItem+=$row["ss_total"];
								$fTotQuantityAllItem90+=$row["qt_cantidad"];
								$fTotAll+=$row["ss_total"];

								$fSsPrecioAllItem90+=$row["ss_precio"]; //Agregado 2020-01-07
								$fSsTotalAllItem90+=$row["ss_total"]; //Agregado 2020-01-07
								$cantidad_para_promediar_90++; //Agregado 2020-01-07
								continue;
							} else {
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
							}
						}
						if( $sPinto97=='No' ){
							$sPinto97='Si';
							if ( trim($row['id_item'])=='11620303' ) {
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_precio"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["qt_cantidad"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '</td>';
								$fTotAllItem+=$row["ss_total"];
								$fTotQuantityAllItem97+=$row["qt_cantidad"];
								$fTotAll+=$row["ss_total"];

								$fSsPrecioAllItem97+=$row["ss_precio"]; //Agregado 2020-01-07
								$fSsTotalAllItem97+=$row["ss_total"]; //Agregado 2020-01-07
								$cantidad_para_promediar_97++; //Agregado 2020-01-07
								continue;
							} else {
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
							}
						}
						if( $sPintoD2=='No' ) {
							$sPintoD2='Si';
							if ( trim($row['id_item'])=='11620304' ) {
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_precio"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["qt_cantidad"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '</td>';
								$fTotAllItem+=$row["ss_total"];
								$fTotQuantityAllItemD2+=$row["qt_cantidad"];
								$fTotAll+=$row["ss_total"];

								$fSsPrecioAllItemD2+=$row["ss_precio"]; //Agregado 2020-01-07
								$fSsTotalAllItemD2+=$row["ss_total"]; //Agregado 2020-01-07
								$cantidad_para_promediar_D2++; //Agregado 2020-01-07
								continue;
							} else {
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
							}
						}
						if( $sPinto95=='No' ) {
							$sPinto95='Si';
							if ( trim($row['id_item'])=='11620305' ) {
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_precio"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["qt_cantidad"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '</td>';
								$fTotAllItem+=$row["ss_total"];
								$fTotQuantityAllItem95+=$row["qt_cantidad"];
								$fTotAll+=$row["ss_total"];

								$fSsPrecioAllItem95+=$row["ss_precio"]; //Agregado 2020-01-07
								$fSsTotalAllItem95+=$row["ss_total"]; //Agregado 2020-01-07
								$cantidad_para_promediar_95++; //Agregado 2020-01-07
								continue;
							} else {
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
							}
						}
						if( $sPintoGLP=='No' ) {
							$sPintoGLP='Si';
							if ( trim($row['id_item'])=='11620307' ) {
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_precio"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["qt_cantidad"], '2', '.', ',')) . '</td>';
								$result .= '<td align="right">' . htmlentities(number_format($row["ss_total"], '2', '.', ',')) . '</td>';
								$fTotAllItem+=$row["ss_total"];
								$fTotQuantityAllItemGLP+=$row["qt_cantidad"];
								$fTotAll+=$row["ss_total"];

								$fSsPrecioAllItemGLP+=$row["ss_precio"]; //Agregado 2020-01-07
								$fSsTotalAllItemGLP+=$row["ss_total"]; //Agregado 2020-01-07
								$cantidad_para_promediar_GLP++; //Agregado 2020-01-07
								continue;
							} else {
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
								$result .= '<td align="right"></td>';
							}
						}
					}// ./ Foreach Detalle de vales

					/*Agregado 2020-01-07*/
					$precio_unitario_promediado_84 = $fSsPrecioAllItem84 / $cantidad_para_promediar_84;
					$precio_unitario_promediado_90 = $fSsPrecioAllItem90 / $cantidad_para_promediar_90;
					$precio_unitario_promediado_97 = $fSsPrecioAllItem97 / $cantidad_para_promediar_97;
					$precio_unitario_promediado_D2 = $fSsPrecioAllItemD2 / $cantidad_para_promediar_D2;
					$precio_unitario_promediado_95 = $fSsPrecioAllItem95 / $cantidad_para_promediar_95;
					$precio_unitario_promediado_GLP = $fSsPrecioAllItemGLP / $cantidad_para_promediar_GLP;
					/***/

					if ( $sPinto84=='Si' ) {
						$iColspanTotalCliente=22; //14
					}
					if ( $sPinto90=='Si' ) {
						$iColspanTotalCliente=20; //12
					}
					if ( $sPinto97=='Si' ) {
						$iColspanTotalCliente=18; //10
					}
					if ( $sPintoD2=='Si' ) {
						$iColspanTotalCliente=16; //8
					}
					if ( $sPinto95=='Si' ) {
						$iColspanTotalCliente=6; //3
					}
					$result .= '<td colspan="'.$iColspanTotalCliente.'" align="right">' . htmlentities(number_format($fTotAllItem, '2', '.', ',')) . '</td>';
				$result .= '</tbody>';
				$result .= '<tfoot>';
					$result .= '<tr>';
						$result .= '<td align="right">TOTAL CRÉDITO</td>';
						
						$result .= '<td colspan="1" align="right"></td>'; //$result .= '<td colspan="1" align="right">' . htmlentities(number_format($precio_unitario_promediado_84, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotQuantityAllItem84, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fSsTotalAllItem84, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>'; //$result .= '<td colspan="1" align="right">' . htmlentities(number_format($precio_unitario_promediado_90, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotQuantityAllItem90, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fSsTotalAllItem90, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>'; //$result .= '<td colspan="1" align="right">' . htmlentities(number_format($precio_unitario_promediado_97, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotQuantityAllItem97, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fSsTotalAllItem97, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>'; //$result .= '<td colspan="1" align="right">' . htmlentities(number_format($precio_unitario_promediado_D2, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotQuantityAllItemD2, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fSsTotalAllItemD2, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>'; //$result .= '<td colspan="1" align="right">' . htmlentities(number_format($precio_unitario_promediado_95, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotQuantityAllItem95, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fSsTotalAllItem95, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>'; //$result .= '<td colspan="1" align="right">' . htmlentities(number_format($precio_unitario_promediado_GLP, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotQuantityAllItemGLP, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fSsTotalAllItemGLP, '2', '.', ',')) . '</td>';

						$result .= '<td align="right">' . htmlentities(number_format($fTotAll, '2', '.', ',')) . '</td>';
					$result .= '</tr>';
					$result .= '<tr class=" grid_detalle_impar" title="CUADRO 1 GALONES - CUADRO 3 GALONES">';
						$result .= '<td align="right">TOTAL CONTADO</td>';
						
						$result .= '<td colspan="1" align="right"></td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotCuadro1Quantity84 - $fTotQuantityAllItem84, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotNeto84 - $fSsTotalAllItem84, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotCuadro1Quantity90 - $fTotQuantityAllItem90, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotNeto90 - $fSsTotalAllItem90, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotCuadro1Quantity97 - $fTotQuantityAllItem97, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotNeto97 - $fSsTotalAllItem97, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotCuadro1QuantityD2 - $fTotQuantityAllItemD2, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotNetoD2 - $fSsTotalAllItemD2, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotCuadro1Quantity95 - $fTotQuantityAllItem95, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotNeto95 - $fSsTotalAllItem95, '2', '.', ',')) . '</td>';

						$result .= '<td colspan="1" align="right"></td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotCuadro1QuantityGLP - $fTotQuantityAllItemGLP, '2', '.', ',')) . '</td>';
						$result .= '<td colspan="1" align="right">' . htmlentities(number_format($fTotNetoGLP - $fSsTotalAllItemGLP, '2', '.', ',')) . '</td>';

						$result .= '<td align="right">' . htmlentities(number_format($fTotNeto - $fTotAll, '2', '.', ',')) . '</td>';
					$result .= '</tr>';
					$result .= '<tr>';
						$result .= '<td align="center" colspan="22">&nbsp;</td>'; //14
					$result .= '</tr>';
				$result .= '</tfoot>';
			} else {
				$result .= '<tr style="' . $arrResultDetalleNDCredito["sCssStyle"] . '">';
					$result .= '<td colspan="22" align="center">' . htmlentities($arrResultDetalleNDCredito["sMessage"]) . '</td>'; //14
				$result .= '</tr>';
				$result .= '<tr>';
					$result .= '<td align="center" colspan="22">&nbsp;</td>'; //14
				$result .= '</tr>';
			}
		$result .= '</table>';

		/*
		Cuadro N° 4 - Control de Inventario
		*/
		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera" colspan="9">CONTROL DE INVENTARIO - cuadro N° 4</th>';
			$result .= '</tr>';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">FECHA</th>';
				$result .= '<th class="grid_cabecera">PRODUCTO</th>';
				$result .= '<th class="grid_cabecera" title="Varilla Ayer">INV. INICIAL</th>';
				$result .= '<th class="grid_cabecera">COMPRAS</th>';
				$result .= '<th class="grid_cabecera">VENTAS</th>';
				$result .= '<th class="grid_cabecera" title="Varilla Ayer + Compras - Ventas">INV. FINAL</th>';
				$result .= '<th class="grid_cabecera" title="Varilla Hoy">VARILLAJE</th>';
				$result .= '<th class="grid_cabecera" title="Varilla Hoy - Inv. Final" >DIF. DIARIA</th>';
				$result .= '<th class="grid_cabecera" title="Acumulacion de la diferencia diaria">DIF. ACUMULADA</th>';
			$result .= '</tr>';
			$result .= '<tbody>';				
				if ( $arrResultControlInventario_GASOHOL84['sStatus']=='success' || 
					 $arrResultControlInventario_GASOGOL90['sStatus']=='success' || 
					 $arrResultControlInventario_GASOGOL97['sStatus']=='success' || 
					 $arrResultControlInventario_DIESELB5['sStatus']=='success' ||
					 $arrResultControlInventario_GASOHOL95['sStatus']=='success' ||
					 $arrResultControlInventario_GLP['sStatus']=='success' ) {
					$iCounter=0;
					$fTotNeto=0.00;
					if($arrResultControlInventario_GASOHOL84['sStatus']=='success'){
						$result .= '<th class="grid_cabecera" colspan="9">11620301 - GASOHOL 84</th>';
					}					
					foreach ($arrResultControlInventario_GASOHOL84['arrData'] as $row) {
						$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
						$fInvFinal = (($row["qt_total_varilla_ayer"] + $row["qt_total_compra"] + $row["qt_total_entrada"]) - $row["qt_total_salida"]);
						$result .= '<tr class="'. $color. '">';
							$result .= '<td align="left">' . htmlentities($row["fecha"]) . '</td>';
							$result .= '<td align="left">' . htmlentities($row["id_item"]) . " - " . htmlentities($row["no_item"]) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_ayer"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_compra"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_salida"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"] - $fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_mes"], '3', '.', ',')) . '</td>';
						$result .= '</tr>';
						++$iCounter;
					}
					if($arrResultControlInventario_GASOGOL90['sStatus']=='success'){
						$result .= '<th class="grid_cabecera" colspan="9">11620302 - GASOHOL 90</th>';
					}
					foreach ($arrResultControlInventario_GASOGOL90['arrData'] as $row) {
						$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
						$fInvFinal = (($row["qt_total_varilla_ayer"] + $row["qt_total_compra"] + $row["qt_total_entrada"]) - $row["qt_total_salida"]);
						$result .= '<tr class="'. $color. '">';
							$result .= '<td align="left">' . htmlentities($row["fecha"]) . '</td>';
							$result .= '<td align="left">' . htmlentities($row["id_item"]) . " - " . htmlentities($row["no_item"]) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_ayer"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_compra"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_salida"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"] - $fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_mes"], '3', '.', ',')) . '</td>';
						$result .= '</tr>';
						++$iCounter;
					}
					if($arrResultControlInventario_GASOGOL97['sStatus']=='success'){
						$result .= '<th class="grid_cabecera" colspan="9">11620303 - GASOHOL 97</th>';
					}
					foreach ($arrResultControlInventario_GASOGOL97['arrData'] as $row) {
						$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
						$fInvFinal = (($row["qt_total_varilla_ayer"] + $row["qt_total_compra"] + $row["qt_total_entrada"]) - $row["qt_total_salida"]);
						$result .= '<tr class="'. $color. '">';
							$result .= '<td align="left">' . htmlentities($row["fecha"]) . '</td>';
							$result .= '<td align="left">' . htmlentities($row["id_item"]) . " - " . htmlentities($row["no_item"]) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_ayer"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_compra"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_salida"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"] - $fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_mes"], '3', '.', ',')) . '</td>';
						$result .= '</tr>';
						++$iCounter;
					}
					if($arrResultControlInventario_DIESELB5['sStatus']=='success'){
						$result .= '<th class="grid_cabecera" colspan="9">11620304 - DIESEL B5 UV</th>';
					}
					foreach ($arrResultControlInventario_DIESELB5['arrData'] as $row) {
						$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
						$fInvFinal = (($row["qt_total_varilla_ayer"] + $row["qt_total_compra"] + $row["qt_total_entrada"]) - $row["qt_total_salida"]);
						$result .= '<tr class="'. $color. '">';
							$result .= '<td align="left">' . htmlentities($row["fecha"]) . '</td>';
							$result .= '<td align="left">' . htmlentities($row["id_item"]) . " - " . htmlentities($row["no_item"]) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_ayer"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_compra"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_salida"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"] - $fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_mes"], '3', '.', ',')) . '</td>';
						$result .= '</tr>';
						++$iCounter;
					}
					if($arrResultControlInventario_GASOHOL95['sStatus']=='success'){
						$result .= '<th class="grid_cabecera" colspan="9">11620305 - GASOHOL 95</th>';
					}					
					foreach ($arrResultControlInventario_GASOHOL95['arrData'] as $row) {
						$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
						$fInvFinal = (($row["qt_total_varilla_ayer"] + $row["qt_total_compra"] + $row["qt_total_entrada"]) - $row["qt_total_salida"]);
						$result .= '<tr class="'. $color. '">';
							$result .= '<td align="left">' . htmlentities($row["fecha"]) . '</td>';
							$result .= '<td align="left">' . htmlentities($row["id_item"]) . " - " . htmlentities($row["no_item"]) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_ayer"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_compra"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_salida"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"] - $fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_mes"], '3', '.', ',')) . '</td>';
						$result .= '</tr>';
						++$iCounter;
					}	
					if($arrResultControlInventario_GLP['sStatus']=='success'){
						$result .= '<th class="grid_cabecera" colspan="9">11620307 - GLP</th>';
					}					
					foreach ($arrResultControlInventario_GLP['arrData'] as $row) {
						$color = ($iCounter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
						$fInvFinal = (($row["qt_total_varilla_ayer"] + $row["qt_total_compra"] + $row["qt_total_entrada"]) - $row["qt_total_salida"]);
						$result .= '<tr class="'. $color. '">';
							$result .= '<td align="left">' . htmlentities($row["fecha"]) . '</td>';
							$result .= '<td align="left">' . htmlentities($row["id_item"]) . " - " . htmlentities($row["no_item"]) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_ayer"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_compra"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_salida"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"], '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_hoy"] - $fInvFinal, '3', '.', ',')) . '</td>';
							$result .= '<td align="right">' . htmlentities(number_format($row["qt_total_varilla_mes"], '3', '.', ',')) . '</td>';
						$result .= '</tr>';
						++$iCounter;
					}				
				} else {
					$result .= '<tr style="' . $arrResultControlInventario["sCssStyle"] . '">';
						$result .= '<td colspan="9" align="center">' . htmlentities($arrResultControlInventario["sMessage"]) . '</td>';
					$result .= '</tr>';
				}
			$result .= '</tbody>';
		$result .= '</table>';

		$result .= '</div>';
		return $result;
	}
	
	function reporteExcel($arrResultVentaCombustibleyOtrasVentas, $arrResultDetalleLiquidacionNDCredito, $arrResultDetalleLiquidacionTCRED, $arrResultDetalleLiquidacionEGRESOS, $arrResultDetalleLiquidacionINGRESOS, $arrResultDetalleDepositosValidados, $arrResultDetalleBBVA, $arrResultDetalleBCP, $arrResultDetalleScotiabank, $arrResultDetalleInterbank, $arrResultObtieneMarket, $arrResultDetalleNDCredito, $arrResultControlInventario,
						  $arrResultControlInventario_GASOHOL84,
						  $arrResultControlInventario_GASOGOL90,
						  $arrResultControlInventario_GASOGOL97,
						  $arrResultControlInventario_DIESELB5,
						  $arrResultControlInventario_GASOHOL95,
						  $arrResultControlInventario_GLP) {
		//echo "<script>console.log('" . json_encode($arrResultVentaCombustibleyOtrasVentas) . "')</script>";
		//echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionNDCredito) . "')</script>";
		//echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionTCRED) . "')</script>";		
		//echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionEGRESOS) . "')</script>";		
		//echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionINGRESOS) . "')</script>";		
		//echo "<script>console.log('" . json_encode($arrResultDetalleDepositosValidados) . "')</script>";		
		// echo "<script>console.log('" . json_encode($arrResultDetalleBBVA) . "')</script>";		
		//echo "<script>console.log('" . json_encode($arrResultDetalleNDCredito) . "')</script>";		
		//echo "<script>console.log('" . json_encode($arrResultControlInventario) . "')</script>";		
		//die();

		$dataCuadro1 = $arrResultVentaCombustibleyOtrasVentas['arrData'];
		$dataCuadro2 = $arrResultDetalleLiquidacionNDCredito['arrData'];
		$dataCuadro21 = $arrResultDetalleLiquidacionTCRED['arrData'];
		$dataCuadro22 = $arrResultDetalleLiquidacionEGRESOS['arrData'];
		$dataCuadro23 = $arrResultDetalleLiquidacionINGRESOS;
		$dataCuadro24 = $arrResultDetalleDepositosValidados['totales'];
		$dataCuadro25 = $arrResultDetalleBBVA['arrData'];
		$dataCuadro26 = $arrResultDetalleBCP['arrData'];
		$dataCuadro27 = $arrResultDetalleScotiabank['arrData'];
		$dataCuadro28 = $arrResultDetalleInterbank['arrData'];
		$dataCuadro29 = $arrResultObtieneMarket;
		$dataCuadro3 = $arrResultDetalleNDCredito['arrData'];
		$dataCuadro4 = $arrResultControlInventario['arrData'];		
		$dataCuadro4_GASOHOL84 = $arrResultControlInventario_GASOHOL84['arrData'];		
		$dataCuadro4_GASOHOL90 = $arrResultControlInventario_GASOGOL90['arrData'];		
		$dataCuadro4_GASOHOL97 = $arrResultControlInventario_GASOGOL97['arrData'];		
		$dataCuadro4_DIESELB5  = $arrResultControlInventario_DIESELB5['arrData'];		
		$dataCuadro4_GASOHOL95 = $arrResultControlInventario_GASOHOL95['arrData'];		
		$dataCuadro4_GLP       = $arrResultControlInventario_GLP['arrData'];		

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

		$worksheet1 =& $workbook->add_worksheet('Cuadro 1');
		$worksheet1->set_column(0, 0, 30);
		$worksheet1->set_column(1, 1, 12);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 15);
		$worksheet1->set_column(5, 5, 15);
		$worksheet1->set_column(6, 6, 12);
		$worksheet1->set_column(7, 7, 12);
		$worksheet1->set_column(8, 8, 12);
		$worksheet1->set_column(9, 9, 12);
		$worksheet1->set_column(10, 10, 12);
		$worksheet1->set_column(11, 11, 12);
		$worksheet1->set_column(12, 12, 12);
		$worksheet1->set_column(13, 13, 20);
		$worksheet1->set_column(14, 14, 20);
		$worksheet1->set_column(15, 15, 12);
		$worksheet1->set_column(16, 16, 12);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet2 =& $workbook->add_worksheet('Cuadro 2');
		$worksheet2->set_column(0, 0, 30);
		$worksheet2->set_column(1, 1, 12);
		$worksheet2->set_column(2, 2, 12);
		$worksheet2->set_column(3, 3, 12);
		$worksheet2->set_column(4, 4, 12);
		$worksheet2->set_column(5, 5, 12);
		$worksheet2->set_column(6, 6, 12);
		$worksheet2->set_column(7, 7, 12);
		$worksheet2->set_column(8, 8, 12);
		$worksheet2->set_column(9, 9, 12);
		$worksheet2->set_column(10, 10, 12);
		$worksheet2->set_column(11, 11, 12);
		$worksheet2->set_column(12, 12, 12);
		$worksheet2->set_column(13, 13, 20);
		$worksheet2->set_column(14, 14, 20);
		$worksheet2->set_column(15, 15, 12);
		$worksheet2->set_column(16, 16, 12);

		$worksheet2->set_zoom(100);
		$worksheet2->set_landscape(100);

		$worksheet3 =& $workbook->add_worksheet('Cuadro 3');
		$worksheet3->set_column(0, 0, 57);
		$worksheet3->set_column(1, 1, 12);
		$worksheet3->set_column(2, 2, 12);
		$worksheet3->set_column(3, 3, 12);
		$worksheet3->set_column(4, 4, 12);
		$worksheet3->set_column(5, 5, 12);
		$worksheet3->set_column(6, 6, 12);
		$worksheet3->set_column(7, 7, 12);
		$worksheet3->set_column(8, 8, 12);
		$worksheet3->set_column(9, 9, 12);
		$worksheet3->set_column(10, 10, 12);
		$worksheet3->set_column(11, 11, 12);
		$worksheet3->set_column(12, 12, 12);
		$worksheet3->set_column(13, 13, 12);
		$worksheet3->set_column(14, 14, 12);
		$worksheet3->set_column(15, 15, 12);
		$worksheet3->set_column(16, 16, 12);
		$worksheet3->set_column(16, 17, 12);
		$worksheet3->set_column(16, 18, 12);
		$worksheet3->set_column(16, 19, 20);

		$worksheet3->set_zoom(100);
		$worksheet3->set_landscape(100);

		$worksheet4 =& $workbook->add_worksheet('Cuadro 4');
		$worksheet4->set_column(0, 0, 12);
		$worksheet4->set_column(1, 1, 37);
		$worksheet4->set_column(2, 2, 12);
		$worksheet4->set_column(3, 3, 12);
		$worksheet4->set_column(4, 4, 12);
		$worksheet4->set_column(5, 5, 12);
		$worksheet4->set_column(6, 6, 12);
		$worksheet4->set_column(7, 7, 12);
		$worksheet4->set_column(8, 8, 12);
		$worksheet4->set_column(9, 9, 12);
		$worksheet4->set_column(10, 10, 12);
		$worksheet4->set_column(11, 11, 12);
		$worksheet4->set_column(12, 12, 12);
		$worksheet4->set_column(13, 13, 20);
		$worksheet4->set_column(14, 14, 20);
		$worksheet4->set_column(15, 15, 12);
		$worksheet4->set_column(16, 16, 12);

		$worksheet4->set_zoom(100);
		$worksheet4->set_landscape(100);

		/************************ CUADRO 1 *************************/
		$worksheet1->write_string(1, 0, "REPORTE DIARIO - VENTAS DEL DIA",$formato0);
		$worksheet1->write_string(3, 0, "VENTAS DE COMBUSTIBLES Y OTROS - cuadro N. 1",$formato0);
		//$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 5;
		
		$worksheet1->write_string($a, 0, "PRODUCTO",$formato2);
		$worksheet1->write_string($a, 1, "GALONES",$formato2);
		$worksheet1->write_string($a, 2, "PU",$formato2);
		$worksheet1->write_string($a, 3, "IMPORTE",$formato2);	
		$worksheet1->write_string($a, 4, "DESCUENTOS / INCREMENTOS",$formato2);
		$worksheet1->write_string($a, 5, "AFERICION (-)",$formato2);														
		$worksheet1->write_string($a, 6, "TOTAL",$formato2);	

		$a = 6;			
		
		$dataProducto = array();
		foreach ($dataCuadro1 as $key=>$value) {
			//echo "<script>console.log('" . json_encode($dataCuadro1[$key]) . "')</script>";			

			$worksheet1->write_string($a, 0, $dataCuadro1[$key]['id_item']." - ".$dataCuadro1[$key]['no_item'], $formato5);
			$worksheet1->write_string($a, 1, $dataCuadro1[$key]['qt_total'], $formato5);
			$worksheet1->write_string($a, 2, $dataCuadro1[$key]['ss_precio'], $formato5);
			$worksheet1->write_string($a, 3, $dataCuadro1[$key]['ss_total'], $formato5);
			$worksheet1->write_string($a, 4, $dataCuadro1[$key]['ss_descuentos'], $formato5);
			$worksheet1->write_string($a, 5, $dataCuadro1[$key]['ss_total_afericion'] != "" ? $dataCuadro1[$key]['ss_total_afericion'] : "0.00" , $formato5);						
			$dataProducto[] = array("id_item"  => $dataCuadro1[$key]['id_item'],
									"qt_total" => $dataCuadro1[$key]['qt_total'],
									"total"    => $dataCuadro1[$key]['ss_total'] + $dataCuadro1[$key]['ss_descuentos'] - $dataCuadro1[$key]['ss_total_afericion']);

			$worksheet1->write_string($a, 6, $dataCuadro1[$key]['ss_total'] + $dataCuadro1[$key]['ss_descuentos'] - $dataCuadro1[$key]['ss_total_afericion'], $formato5);											
			$total_cuadro1 += $dataCuadro1[$key]['ss_total'] + $dataCuadro1[$key]['ss_descuentos'] - $dataCuadro1[$key]['ss_total_afericion'];

			$a++;
		}				
		$worksheet1->write_string($a, 6, $total_cuadro1, $formato2);
		$total_cuadro1_backup = $total_cuadro1;		
		
		// echo "<script>console.log('" . json_encode($total_cuadro1_backup) . "')</script>";		
		// die();

		/************************ CUADRO 2 *************************/
		$a = 1;
		$worksheet2->write_string($a, 0, "DETALLE - LIQUIDACION - cuadro N. 2",$formato0);

		$a = $a + 2;
		$worksheet2->write_string($a, 0, "DESCRIPCION",$formato2);
		$worksheet2->write_string($a, 1, "TOTAL",$formato2);		

		/*** Agregado 2020-01-22 ***/
		//echo "<script>console.log('Market: " . json_encode($arrResultObtieneMarket) . "')</script>";
		foreach ($dataCuadro29['propiedades']['ESTACION']['almacenes'] as $key => $value) {
			$total_market = $value['2'] - $value['3'] - $value['4'] - $value['5'] - $value['6'] - $value['7'] - $value['8'] - $value['9'] - $value['10'];
			//echo "<script>console.log('" . json_encode($total_market) . "')</script>";
		}
		$total_cuadro1 = $total_cuadro1 + $total_market;		
		/***/

		$a = $a + 1;
		$worksheet2->write_string($a, 0, "TOTAL MARKET",$formato5);
		$worksheet2->write_string($a, 1, $total_market,$formato5);	

		$a = $a + 1;
		$worksheet2->write_string($a, 0, "TOTAL NETO",$formato5);
		$worksheet2->write_string($a, 1, $total_cuadro1,$formato5);			

		$a = $a + 1;		
		foreach($dataCuadro2 as $key=>$value){			
			//echo "<script>console.log('" . json_encode($dataCuadro2[$key]['ss_total']) . "')</script>";			
			$worksheet2->write_string($a, 0, "VENTA TOTAL CREDITOS",$formato5);
			$worksheet2->write_string($a, 1, $dataCuadro2[$key]['ss_total'],$formato5);
			$a++;

			$total_cuadro1 = $total_cuadro1 - $dataCuadro2[$key]['ss_total'];
		}		
		//die();
			
		foreach($dataCuadro21 as $key=>$value){						
			$worksheet2->write_string($a, 0, $dataCuadro21[$key]['no_tarjeta_credito'],$formato5);		
			$worksheet2->write_string($a, 1, $dataCuadro21[$key]['ss_total'],$formato5);		
			$a++;

			$total_cuadro1 = $total_cuadro1 - $dataCuadro21[$key]['ss_total'];
		}				
		
		/*Agregado 2020-10-07*/	
		$worksheet2->write_string($a, 0, "DEPOSITO EFECTIVO",$formato5);		
		$worksheet2->write_string($a, 1, $total_cuadro1,$formato5);		
		$diferencia_de_depositos = $total_cuadro1 - $dataCuadro24['totsol'];

		$a = $a + 1;
		$worksheet2->write_string($a, 0, "DEPOSITO VALIDADOS",$formato5);		
		$worksheet2->write_string($a, 1, $dataCuadro24['totsol'],$formato5);						

		$a = $a + 1;
		$worksheet2->write_string($a, 0, "DIFERENCIA DE DEPOSITOS",$formato5);		
		$worksheet2->write_string($a, 1, $diferencia_de_depositos,$formato5);						
		/***/
		
		$a = $a + 1;
		foreach($dataCuadro22 as $key=>$value){						
			$worksheet2->write_string($a, 0, "GASTOS VARIOS",$formato5);					
			$worksheet2->write_string($a, 1, $dataCuadro22[$key]['ss_total'],$formato5);		
			$a++;

			$total_cuadro1 = $total_cuadro1 - $dataCuadro22[$key]['ss_total'];
		}	

		//echo "<script>console.log('" . json_encode($dataCuadro23['sMessage']) . "')</script>";			
		//die();
		if($dataCuadro23['sMessage'] == "No hay registros"):
			$worksheet2->write_string($a, 0, "DEPOSITO EFECTIVO",$formato5);					
			$worksheet2->write_string($a, 1, "0.00",$formato5);	
			
			$a++;
		else:
			foreach($dataCuadro23['arrData'] as $key=>$value):
				$worksheet2->write_string($a, 0, "DEPOSITO EFECTIVO",$formato5);					
				$worksheet2->write_string($a, 1, $dataCuadro23['arrData'][$key]['ss_total'],$formato5);		
				$a++;

				$total_cuadro1 = $total_cuadro1 - $dataCuadro23['arrData'][$key]['ss_total'];
			endforeach;
		endif;

		$worksheet2->write_string($a, 0, "BBVA CONTINENTAL",$formato5);
		$worksheet2->write_string($a, 1, number_format($dataCuadro25['total'], '2', '.', ','),$formato5);
		$a++;		

		$worksheet2->write_string($a, 0, "BCP",$formato5);
		$worksheet2->write_string($a, 1, number_format($dataCuadro26['total'], '2', '.', ','),$formato5);
		$a++;		

		$worksheet2->write_string($a, 0, "SCOTIABANK",$formato5);
		$worksheet2->write_string($a, 1, number_format($dataCuadro27['total'], '2', '.', ','),$formato5);
		$a++;		

		$worksheet2->write_string($a, 0, "INTERBANK",$formato5);
		$worksheet2->write_string($a, 1, number_format($dataCuadro28['total'], '2', '.', ','),$formato5);
		$a++;		

		$worksheet2->write_string($a, 0, "DIFERENCIA DE VENTA",$formato2);
		$worksheet2->write_string($a, 1, $total_cuadro1,$formato2);

		/************************ CUADRO 3 *************************/
		$a = 1;
		$worksheet3->write_string($a, 0, "VENTAS CREDITO - cuadro N. 3",$formato0);

		$a = $a + 2;
		$worksheet3->write_string($a, 0, "CLIENTE",$formato2);
		$worksheet3->write_string($a, 1, "COMB. 84",$formato2);
		$worksheet3->write_string($a, 4, "COMB. 90",$formato2);
		$worksheet3->write_string($a, 7, "COMB. 97",$formato2);	
		$worksheet3->write_string($a, 10, "COMB. D2",$formato2);
		$worksheet3->write_string($a, 13, "COMB. 95",$formato2);														
		$worksheet3->write_string($a, 16, "GLP",$formato2);	
		$worksheet3->write_string($a, 19, "IMPORTE TOTAL",$formato2);	

		$a = $a + 1;
		$worksheet3->write_string($a, 0, "",$formato2);
		$worksheet3->write_string($a, 1, "P.U.",$formato2);
		$worksheet3->write_string($a, 2, "GAL.",$formato2);
		$worksheet3->write_string($a, 3, "IMP.",$formato2);
		$worksheet3->write_string($a, 4, "P.U.",$formato2);
		$worksheet3->write_string($a, 5, "GAL.",$formato2);
		$worksheet3->write_string($a, 6, "IMP.",$formato2);
		$worksheet3->write_string($a, 7, "P.U.",$formato2);
		$worksheet3->write_string($a, 8, "GAL.",$formato2);
		$worksheet3->write_string($a, 9, "IMP.",$formato2);
		$worksheet3->write_string($a, 10, "P.U.",$formato2);
		$worksheet3->write_string($a, 11, "GAL.",$formato2);
		$worksheet3->write_string($a, 12, "IMP.",$formato2);
		$worksheet3->write_string($a, 13, "P.U.",$formato2);
		$worksheet3->write_string($a, 14, "GAL.",$formato2);
		$worksheet3->write_string($a, 15, "IMP.",$formato2);
		$worksheet3->write_string($a, 16, "P.U.",$formato2);
		$worksheet3->write_string($a, 17, "GAL.",$formato2);
		$worksheet3->write_string($a, 18, "IMP.",$formato2);
		$worksheet3->write_string($a, 19, "",$formato2);

		$a = $a + 1;		
		$credito1 = 0.00;
		$credito2 = 0.00;
		$credito3 = 0.00;
		$credito4 = 0.00;
		$credito5 = 0.00;
		$credito6 = 0.00;
		$importe1 = 0.00;
		$importe2 = 0.00;
		$importe3 = 0.00;
		$importe4 = 0.00;
		$importe5 = 0.00;
		$importe6 = 0.00;
		$total_cuadro3 = 0.00;
		$total_fila = 0.00;
		//echo "<script>console.log('" . json_encode($dataProducto) . "')</script>";
		//die();
		foreach($dataCuadro3 as $key=>$value){			
			// echo "<script>console.log('" . json_encode($dataCuadro3[$key]['no_cliente']) . "')</script>";				
			// echo "<script>console.log('" . json_encode($id_cliente_anterior) . "')</script>";				
			// echo "<script>console.log('" . json_encode($id_cliente) . "')</script>";			
			// echo "<script>console.log('*****')</script>";
			
			$id_cliente_anterior = $dataCuadro3[($key-1)]['id_cliente'];			
			$id_cliente = $dataCuadro3[$key]['id_cliente'];			
			if($key != 0){
				if(trim($id_cliente) != trim($id_cliente_anterior)){									
					$a++;
					$total_fila = 0;
				}						
			}						

			$worksheet3->write_string($a, 0, $dataCuadro3[$key]['no_cliente'],$formato5);
						
			if ( trim($dataCuadro3[$key]['id_item'])=='11620301' ) {
				$worksheet3->write_string($a, 1, $dataCuadro3[$key]['ss_precio'],$formato5);
				$worksheet3->write_string($a, 2, $dataCuadro3[$key]['qt_cantidad'],$formato5);				
				$worksheet3->write_string($a, 3, $dataCuadro3[$key]['ss_total'],$formato5);				
				$credito1 += $dataCuadro3[$key]['qt_cantidad'];
				$importe1 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620302' ) {
				$worksheet3->write_string($a, 4, $dataCuadro3[$key]['ss_precio'],$formato5);
				$worksheet3->write_string($a, 5, $dataCuadro3[$key]['qt_cantidad'],$formato5);				
				$worksheet3->write_string($a, 6, $dataCuadro3[$key]['ss_total'],$formato5);
				$credito2 += $dataCuadro3[$key]['qt_cantidad'];
				$importe2 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620303' ) {
				$worksheet3->write_string($a, 7, $dataCuadro3[$key]['ss_precio'],$formato5);
				$worksheet3->write_string($a, 8, $dataCuadro3[$key]['qt_cantidad'],$formato5);				
				$worksheet3->write_string($a, 9, $dataCuadro3[$key]['ss_total'],$formato5);
				$credito3 += $dataCuadro3[$key]['qt_cantidad'];
				$importe3 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620304' ) {
				$worksheet3->write_string($a, 10, $dataCuadro3[$key]['ss_precio'],$formato5);
				$worksheet3->write_string($a, 11, $dataCuadro3[$key]['qt_cantidad'],$formato5);				
				$worksheet3->write_string($a, 12, $dataCuadro3[$key]['ss_total'],$formato5);
				$credito4 += $dataCuadro3[$key]['qt_cantidad'];
				$importe4 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620305' ) {
				$worksheet3->write_string($a, 13, $dataCuadro3[$key]['ss_precio'],$formato5);
				$worksheet3->write_string($a, 14, $dataCuadro3[$key]['qt_cantidad'],$formato5);				
				$worksheet3->write_string($a, 15, $dataCuadro3[$key]['ss_total'],$formato5);
				$credito5 += $dataCuadro3[$key]['qt_cantidad'];
				$importe5 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620307' ) {
				$worksheet3->write_string($a, 16, $dataCuadro3[$key]['ss_precio'],$formato5);
				$worksheet3->write_string($a, 17, $dataCuadro3[$key]['qt_cantidad'],$formato5);				
				$worksheet3->write_string($a, 18, $dataCuadro3[$key]['ss_total'],$formato5);
				$credito6 += $dataCuadro3[$key]['qt_cantidad'];
				$importe6 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if (trim($dataCuadro3[$key]['id_item'])=='11620301' ||
				trim($dataCuadro3[$key]['id_item'])=='11620302' || 
				trim($dataCuadro3[$key]['id_item'])=='11620303' || 
				trim($dataCuadro3[$key]['id_item'])=='11620304' || 
				trim($dataCuadro3[$key]['id_item'])=='11620305' ||
				trim($dataCuadro3[$key]['id_item'])=='11620307'){
				$worksheet3->write_string($a, 19, $total_fila,$formato5);
				$total_cuadro3 += $dataCuadro3[$key]['ss_total'];
			}	
						
			// if($dataCuadro3[$key] == end($dataCuadro3)){				
			// 	$a++;
			// }
			
			// $a++;
		}		
		// die();
		$a = $a + 1;
		$worksheet3->write_string($a, 0, "TOTAL CREDITO",$formato2);
		$worksheet3->write_string($a, 2, number_format($credito1, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 3, number_format($importe1, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 5, number_format($credito2, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 6, number_format($importe2, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 8, number_format($credito3, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 9, number_format($importe3, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 11, number_format($credito4, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 12, number_format($importe4, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 14, number_format($credito5, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 15, number_format($importe5, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 17, number_format($credito6, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 18, number_format($importe6, 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 19, number_format($total_cuadro3, 2, '.', ''),$formato2);				
				
		// echo "<script>console.log('" . json_encode($dataCuadro3) . "')</script>";		
		// echo "<script>console.log('" . json_encode($dataProducto) . "')</script>";		
		// die();

		// foreach($dataProducto as $key2=>$value2){							
		// 	echo "<script>console.log('" . json_encode(trim($dataProducto[$key2]['id_item'])) . "')</script>";									
		// }
		// die();

		// echo "<script>console.log('" . json_encode($total_cuadro1_backup) . "')</script>";				
		// echo "<script>console.log('" . json_encode($total_cuadro3) . "')</script>";				
		// die();

		$a = $a + 1;

		$worksheet3->write_string($a, 0, "TOTAL CONTADO",$formato2);
		$worksheet3->write_string($a, 2, number_format("0.00", 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 3, number_format("0.00", 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 5, number_format("0.00", 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 6, number_format("0.00", 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 8, number_format("0.00", 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 9, number_format("0.00", 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 11, number_format("0.00", 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 12, number_format("0.00", 2, '.', ''),$formato2);
		$worksheet3->write_string($a, 14, number_format("0.00", 2, '.', ''),$formato2);	
		$worksheet3->write_string($a, 15, number_format("0.00", 2, '.', ''),$formato2);	
		$worksheet3->write_string($a, 17, number_format("0.00", 2, '.', ''),$formato2);	
		$worksheet3->write_string($a, 18, number_format("0.00", 2, '.', ''),$formato2);	
		$worksheet3->write_string($a, 19, number_format($total_cuadro1_backup - $total_cuadro3, 2, '.', ''),$formato2);			
		foreach($dataProducto as $key=>$value){
			if ( $dataProducto[$key]['id_item']=='11620301' ) {																				
				$worksheet3->write_string($a, 2, number_format($dataProducto[$key]['qt_total'], 2, '.', ''),$formato2);
				$worksheet3->write_string($a, 3, number_format($dataProducto[$key]['total'], 2, '.', ''),$formato2);
			}
			if ( $dataProducto[$key]['id_item']=='11620302' ) {																				
				$worksheet3->write_string($a, 5, number_format($dataProducto[$key]['qt_total'], 2, '.', ''),$formato2);
				$worksheet3->write_string($a, 6, number_format($dataProducto[$key]['total'], 2, '.', ''),$formato2);
			}			
			if ( $dataProducto[$key]['id_item']=='11620303' ) {																				
				$worksheet3->write_string($a, 8, number_format($dataProducto[$key]['qt_total'], 2, '.', ''),$formato2);
				$worksheet3->write_string($a, 9, number_format($dataProducto[$key]['total'], 2, '.', ''),$formato2);
			}			
			if ( $dataProducto[$key]['id_item']=='11620304' ) {																				
				$worksheet3->write_string($a, 11, number_format($dataProducto[$key]['qt_total'], 2, '.', ''),$formato2);
				$worksheet3->write_string($a, 12, number_format($dataProducto[$key]['total'], 2, '.', ''),$formato2);
			}			
			if ( $dataProducto[$key]['id_item']=='11620305' ) {																				
				$worksheet3->write_string($a, 14, number_format($dataProducto[$key]['qt_total'], 2, '.', ''),$formato2);
				$worksheet3->write_string($a, 15, number_format($dataProducto[$key]['total'], 2, '.', ''),$formato2);
			}			
			if ( $dataProducto[$key]['id_item']=='11620307' ) {																				
				$worksheet3->write_string($a, 17, number_format($dataProducto[$key]['qt_total'], 2, '.', ''),$formato2);
				$worksheet3->write_string($a, 18, number_format($dataProducto[$key]['total'], 2, '.', ''),$formato2);
			}			
		}				

		$worksheet3->write_string($a, 0, "TOTAL CONTADO",$formato2);	
		foreach($dataCuadro3 as $key=>$value){		
			foreach($dataProducto as $key2=>$value2){	
				if ( trim($dataCuadro3[$key]['id_item'])=='11620301' ) {																				
					if( trim($dataCuadro3[$key]['id_item']) == $dataProducto[$key2]['id_item'] ){
						$total_contado = (double) $dataProducto[$key2]['qt_total'] - (double) $credito1;
						$total_importe = (double) $dataProducto[$key2]['total'] - (double) $importe1;
						$worksheet3->write_string($a, 2, number_format($total_contado, 2, '.', ''),$formato2);
						$worksheet3->write_string($a, 3, number_format($total_importe, 2, '.', ''),$formato2);
					}
				}								
				if ( trim($dataCuadro3[$key]['id_item'])=='11620302' ) {					
					if( trim($dataCuadro3[$key]['id_item']) == $dataProducto[$key2]['id_item'] ){
						$total_contado = (double) $dataProducto[$key2]['qt_total'] - (double) $credito2;
						$total_importe = (double) $dataProducto[$key2]['total'] - (double) $importe2;
						$worksheet3->write_string($a, 5, number_format($total_contado, 2, '.', ''),$formato2);
						$worksheet3->write_string($a, 6, number_format($total_importe, 2, '.', ''),$formato2);
					}
				}
				if ( trim($dataCuadro3[$key]['id_item'])=='11620303' ) {				
					if( trim($dataCuadro3[$key]['id_item']) == $dataProducto[$key2]['id_item'] ){
						$total_contado = (double) $dataProducto[$key2]['qt_total'] - (double) $credito3;
						$total_importe = (double) $dataProducto[$key2]['total'] - (double) $importe3;
						$worksheet3->write_string($a, 8, number_format($total_contado, 2, '.', ''),$formato2);
						$worksheet3->write_string($a, 9, number_format($total_importe, 2, '.', ''),$formato2);
					}
				}
				if ( trim($dataCuadro3[$key]['id_item'])=='11620304' ) {					
					if( trim($dataCuadro3[$key]['id_item']) == $dataProducto[$key2]['id_item'] ){
						$total_contado = (double) $dataProducto[$key2]['qt_total'] - (double) $credito4;
						$total_importe = (double) $dataProducto[$key2]['total'] - (double) $importe4;
						$worksheet3->write_string($a, 11, number_format($total_contado, 2, '.', ''),$formato2);
						$worksheet3->write_string($a, 12, number_format($total_importe, 2, '.', ''),$formato2);
					}
				}
				if ( trim($dataCuadro3[$key]['id_item'])=='11620305' ) {					
					if( trim($dataCuadro3[$key]['id_item']) == $dataProducto[$key2]['id_item'] ){
						$total_contado = (double) $dataProducto[$key2]['qt_total'] - (double) $credito5;
						$total_importe = (double) $dataProducto[$key2]['total'] - (double) $importe5;
						$worksheet3->write_string($a, 14, number_format($total_contado, 2, '.', ''),$formato2);
						$worksheet3->write_string($a, 15, number_format($total_importe, 2, '.', ''),$formato2);
					}
				}
				if ( trim($dataCuadro3[$key]['id_item'])=='11620307' ) {
					// $worksheet3->write_string($a, 12, number_format($dataProducto[$key2]['qt_total'], 2, '.', ''),$formato2);

					if( trim($dataCuadro3[$key]['id_item']) == $dataProducto[$key2]['id_item'] ){
						$total_contado = (double) $dataProducto[$key2]['qt_total'] - (double) $credito6;
						$total_importe = (double) $dataProducto[$key2]['total'] - (double) $importe6;
						$worksheet3->write_string($a, 17, number_format($total_contado, 2, '.', ''),$formato2);
						$worksheet3->write_string($a, 18, number_format($total_importe, 2, '.', ''),$formato2);
					}
				}
			}							
		}		
		//die();

		/************************ CUADRO 4 *************************/
		$a = 1;
		$worksheet4->write_string($a, 0, "CONTROL DE INVENTARIO - cuadro N. 4",$formato0);
		
		$a = $a + 1;
		$worksheet4->write_string($a, 0, "FECHA",$formato0);
		$worksheet4->write_string($a, 1, "PRODUCTO",$formato0);
		$worksheet4->write_string($a, 2, "INV. INICIAL",$formato0);
		$worksheet4->write_string($a, 3, "COMPRAS",$formato0);
		$worksheet4->write_string($a, 4, "VENTAS",$formato0);
		$worksheet4->write_string($a, 5, "INV. FINAL",$formato0);
		$worksheet4->write_string($a, 6, "VARILLAJE",$formato0);
		$worksheet4->write_string($a, 7, "DIF. DIARIA",$formato0);
		$worksheet4->write_string($a, 8, "DIF. ACUMULADA",$formato0);

		$a++;
		$worksheet4->write_string($a, 0, "11620301 - GASOHOL 84",$formato0);
		
		if($dataCuadro4_GASOHOL84 != null){
			$a++;
			foreach($dataCuadro4_GASOHOL84 as $key=>$value){		
				$inv_final = $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GASOHOL84[$key]['qt_total_compra'] + $dataCuadro4_GASOHOL84[$key]['qt_total_entrada'] - $dataCuadro4_GASOHOL84[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_hoy'] - $inv_final;				

				$worksheet4->write_string($a, 0, $dataCuadro4_GASOHOL84[$key]['fecha'] ,$formato5);
				$worksheet4->write_string($a, 1, $dataCuadro4_GASOHOL84[$key]['id_item']." - ".$dataCuadro4_GASOHOL84[$key]['no_item'] ,$formato5);
				$worksheet4->write_string($a, 2, ($dataCuadro4_GASOHOL84[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 3, ($dataCuadro4_GASOHOL84[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GASOHOL84[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 4, ($dataCuadro4_GASOHOL84[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GASOHOL84[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '') ,$formato5);			
				$worksheet4->write_string($a, 5, ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 6, ($dataCuadro4_GASOHOL84[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 7, ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 8, ($dataCuadro4_GASOHOL84[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '') ,$formato5);
				$a++;
			}
		}		

		if($dataCuadro4_GASOHOL90 != null){
			$worksheet4->write_string($a, 0, "11620302 - GASOHOL 90",$formato0);
			$a++;
			foreach($dataCuadro4_GASOHOL90 as $key=>$value){		
				$inv_final = $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GASOHOL90[$key]['qt_total_compra'] + $dataCuadro4_GASOHOL90[$key]['qt_total_entrada'] - $dataCuadro4_GASOHOL90[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_hoy'] - $inv_final;				

				$worksheet4->write_string($a, 0, $dataCuadro4_GASOHOL90[$key]['fecha'] ,$formato5);
				$worksheet4->write_string($a, 1, $dataCuadro4_GASOHOL90[$key]['id_item']." - ".$dataCuadro4_GASOHOL90[$key]['no_item'] ,$formato5);
				$worksheet4->write_string($a, 2, ($dataCuadro4_GASOHOL90[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 3, ($dataCuadro4_GASOHOL90[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GASOHOL90[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 4, ($dataCuadro4_GASOHOL90[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GASOHOL90[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '') ,$formato5);			
				$worksheet4->write_string($a, 5, ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 6, ($dataCuadro4_GASOHOL90[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 7, ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 8, ($dataCuadro4_GASOHOL90[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '') ,$formato5);
				$a++;
			}
		}
		
		if($dataCuadro4_GASOHOL97 != null){
			$worksheet4->write_string($a, 0, "11620303 - GASOHOL 97",$formato0);
			$a++;
			foreach($dataCuadro4_GASOHOL97 as $key=>$value){		
				$inv_final = $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GASOHOL97[$key]['qt_total_compra'] + $dataCuadro4_GASOHOL97[$key]['qt_total_entrada'] - $dataCuadro4_GASOHOL97[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_hoy'] - $inv_final;				

				$worksheet4->write_string($a, 0, $dataCuadro4_GASOHOL97[$key]['fecha'] ,$formato5);
				$worksheet4->write_string($a, 1, $dataCuadro4_GASOHOL97[$key]['id_item']." - ".$dataCuadro4_GASOHOL97[$key]['no_item'] ,$formato5);
				$worksheet4->write_string($a, 2, ($dataCuadro4_GASOHOL97[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 3, ($dataCuadro4_GASOHOL97[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GASOHOL97[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 4, ($dataCuadro4_GASOHOL97[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GASOHOL97[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '') ,$formato5);			
				$worksheet4->write_string($a, 5, ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 6, ($dataCuadro4_GASOHOL97[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 7, ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 8, ($dataCuadro4_GASOHOL97[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '') ,$formato5);
				$a++;
			}
		}

		if($dataCuadro4_DIESELB5 != null){
			$worksheet4->write_string($a, 0, "11620304 - DIESEL B5",$formato0);
			$a++;
			foreach($dataCuadro4_DIESELB5 as $key=>$value){		
				$inv_final = $dataCuadro4_DIESELB5[$key]['qt_total_varilla_ayer'] + $dataCuadro4_DIESELB5[$key]['qt_total_compra'] + $dataCuadro4_DIESELB5[$key]['qt_total_entrada'] - $dataCuadro4_DIESELB5[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_DIESELB5[$key]['qt_total_varilla_hoy'] - $inv_final;								

				$worksheet4->write_string($a, 0, $dataCuadro4_DIESELB5[$key]['fecha'] ,$formato5);
				$worksheet4->write_string($a, 1, $dataCuadro4_DIESELB5[$key]['id_item']." - ".$dataCuadro4_DIESELB5[$key]['no_item'] ,$formato5);
				$worksheet4->write_string($a, 2, ($dataCuadro4_DIESELB5[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_DIESELB5[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 3, ($dataCuadro4_DIESELB5[$key]['qt_total_compra'] != null)       ? $dataCuadro4_DIESELB5[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 4, ($dataCuadro4_DIESELB5[$key]['qt_total_salida'] != null)       ? $dataCuadro4_DIESELB5[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '') ,$formato5);			
				$worksheet4->write_string($a, 5, ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 6, ($dataCuadro4_DIESELB5[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_DIESELB5[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 7, ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 8, ($dataCuadro4_DIESELB5[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_DIESELB5[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '') ,$formato5);
				$a++;
			}
		}		

		if($dataCuadro4_GASOHOL95 != null){
			$worksheet4->write_string($a, 0, "11620305 - GASOHOL 95",$formato0);
			$a++;
			foreach($dataCuadro4_GASOHOL95 as $key=>$value){	
				$inv_final = $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GASOHOL95[$key]['qt_total_compra'] + $dataCuadro4_GASOHOL95[$key]['qt_total_entrada'] - $dataCuadro4_GASOHOL95[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_hoy'] - $inv_final;

				$worksheet4->write_string($a, 0, $dataCuadro4_GASOHOL95[$key]['fecha'] ,$formato5);
				$worksheet4->write_string($a, 1, $dataCuadro4_GASOHOL95[$key]['id_item']." - ".$dataCuadro4_GASOHOL95[$key]['no_item'] ,$formato5);
				$worksheet4->write_string($a, 2, ($dataCuadro4_GASOHOL95[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 3, ($dataCuadro4_GASOHOL95[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GASOHOL95[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 4, ($dataCuadro4_GASOHOL95[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GASOHOL95[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '') ,$formato5);			
				$worksheet4->write_string($a, 5, ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 6, ($dataCuadro4_GASOHOL95[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 7, ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 8, ($dataCuadro4_GASOHOL95[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '') ,$formato5);
				$a++;
			}
		}		

		if($dataCuadro4_GLP != null){
			$worksheet4->write_string($a, 0, "11620307 - GLP",$formato0);
			$a++;
			foreach($dataCuadro4_GLP as $key=>$value){	
				$inv_final = $dataCuadro4_GLP[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GLP[$key]['qt_total_entrada'] - $dataCuadro4_GLP[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GLP[$key]['qt_total_varilla_hoy'] - $inv_final;				

				$worksheet4->write_string($a, 0, $dataCuadro4_GLP[$key]['fecha'] ,$formato5);
				$worksheet4->write_string($a, 1, $dataCuadro4_GLP[$key]['id_item']." - ".$dataCuadro4_GLP[$key]['no_item'] ,$formato5);
				$worksheet4->write_string($a, 2, ($dataCuadro4_GLP[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GLP[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 3, ($dataCuadro4_GLP[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GLP[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 4, ($dataCuadro4_GLP[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GLP[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '') ,$formato5);			
				$worksheet4->write_string($a, 5, ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 6, ($dataCuadro4_GLP[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GLP[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 7, ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '') ,$formato5);
				$worksheet4->write_string($a, 8, ($dataCuadro4_GLP[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GLP[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '') ,$formato5);
				$a++;
			}
		}

		$workbook->close();	

		$chrFileName = "Ventas Diarias";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}
	
	function reportePDF($arrResultVentaCombustibleyOtrasVentas, $arrResultDetalleLiquidacionNDCredito, $arrResultDetalleLiquidacionTCRED, $arrResultDetalleLiquidacionEGRESOS, $arrResultDetalleLiquidacionINGRESOS, $arrResultDetalleDepositosValidados, $arrResultDetalleBBVA, $arrResultDetalleBCP, $arrResultDetalleScotiabank, $arrResultDetalleInterbank, $arrResultObtieneMarket, $arrResultDetalleNDCredito, $arrResultControlInventario,
						$arrResultControlInventario_GASOHOL84,
						$arrResultControlInventario_GASOGOL90,
						$arrResultControlInventario_GASOGOL97,
						$arrResultControlInventario_DIESELB5,
						$arrResultControlInventario_GASOHOL95,
						$arrResultControlInventario_GLP){
		// echo "<script>console.log('" . json_encode($arrResultVentaCombustibleyOtrasVentas) . "')</script>";
		// echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionNDCredito) . "')</script>";
		// echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionTCRED) . "')</script>";		
		// echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionEGRESOS) . "')</script>";		
		// echo "<script>console.log('" . json_encode($arrResultDetalleLiquidacionINGRESOS) . "')</script>";		
		// echo "<script>console.log('" . json_encode($arrResultDetalleDepositosValidados) . "')</script>";		
		// echo "<script>console.log('" . json_encode($arrResultDetalleBBVA) . "')</script>";		
		// echo "<script>console.log('" . json_encode($arrResultDetalleNDCredito) . "')</script>";		
		// echo "<script>console.log('" . json_encode($arrResultControlInventario) . "')</script>";		
		//die();

		$dataCuadro1 = $arrResultVentaCombustibleyOtrasVentas['arrData'];
		$dataCuadro2 = $arrResultDetalleLiquidacionNDCredito['arrData'];
		$dataCuadro21 = $arrResultDetalleLiquidacionTCRED['arrData'];
		$dataCuadro22 = $arrResultDetalleLiquidacionEGRESOS['arrData'];
		$dataCuadro23 = $arrResultDetalleLiquidacionINGRESOS;
		$dataCuadro24 = $arrResultDetalleDepositosValidados['totales'];
		$dataCuadro25 = $arrResultDetalleBBVA['arrData'];
		$dataCuadro26 = $arrResultDetalleBCP['arrData'];
		$dataCuadro27 = $arrResultDetalleScotiabank['arrData'];
		$dataCuadro28 = $arrResultDetalleInterbank['arrData'];
		$dataCuadro29 = $arrResultObtieneMarket;
		$dataCuadro3 = $arrResultDetalleNDCredito['arrData'];
		$dataCuadro4 = $arrResultControlInventario['arrData'];
		$dataCuadro4_GASOHOL84 = $arrResultControlInventario_GASOHOL84['arrData'];		
		$dataCuadro4_GASOHOL90 = $arrResultControlInventario_GASOGOL90['arrData'];		
		$dataCuadro4_GASOHOL97 = $arrResultControlInventario_GASOGOL97['arrData'];		
		$dataCuadro4_DIESELB5  = $arrResultControlInventario_DIESELB5['arrData'];		
		$dataCuadro4_GASOHOL95 = $arrResultControlInventario_GASOHOL95['arrData'];		
		$dataCuadro4_GLP       = $arrResultControlInventario_GLP['arrData'];		
	
		/************************ CUADRO 1 *************************/
		$reporte = new CReportes2("L", "pt", Array(525.28,810));		

		$reporte->definirCabecera(1, "L", "Sistema Web");
		$reporte->definirCabecera(1, "R", "Pag. %p");
		$reporte->definirCabecera(2, "L", "Usuario: %u");
		$reporte->definirCabecera(2, "R", "%f %h");
		$reporte->definirCabecera(3, "L", "Reporte Diario - Ventas del Dia");

		$cab_cuadro1_titulo = Array(
			"titulo" => "VENTAS DE COMBUSTIBLES Y OTROS - cuadro N. 1"
		);
		$cab_cuadro1 = Array(
			"no_item"                => "Producto",
			"qt_total"               => "Galones",
			"ss_precio"              => "P.U.",
			"ss_total"               => "Importe",
			"ss_descuentos"          => "Descuentos / Incrementos",
			"ss_total_afericion"	 => "Afericion",
			"total"	                 => "Total"			
		);					

		$reporte->definirCabeceraPredeterminada($cab_cuadro1_titulo, "_cab");
		$reporte->definirCabeceraPredeterminada($cab_cuadro1);

		$reporte->definirColumna("titulo", $reporte->TIPO_TEXTO, 80, "L", "_cab");
		$reporte->definirColumna("no_item", $reporte->TIPO_TEXTO, 25, "L");
		$reporte->definirColumna("qt_total", $reporte->TIPO_IMPORTE, 15, "R");
		$reporte->definirColumna("ss_precio", $reporte->TIPO_IMPORTE, 15, "R");	
		$reporte->definirColumna("ss_total", $reporte->TIPO_IMPORTE, 15, "R");
		$reporte->definirColumna("ss_descuentos", $reporte->TIPO_IMPORTE, 30, "R");	
		$reporte->definirColumna("ss_total_afericion", $reporte->TIPO_IMPORTE, 15, "R");
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 15, "R");											

		$reporte->SetFont("courier", "", 8);
		$reporte->SetMargins(0,0,0);
		$reporte->SetAutoPageBreak(true, 0);
		$reporte->AddPage();

		// echo "<script>console.log('" . json_encode($dataCuadro1) . "')</script>";
		$totales = array();	
		$dataProducto = array();	
		foreach($dataCuadro1 as $key=>$value){					
			// echo "<script>console.log('" . json_encode($dataCuadro1[$key]) . "')</script>";
			$dataCuadro1[$key]['no_item'] = $dataCuadro1[$key]['id_item'] . " - " . $dataCuadro1[$key]['no_item'];
			$dataCuadro1[$key]['total'] = $dataCuadro1[$key]['ss_total'] + $dataCuadro1[$key]['ss_descuentos'] - $dataCuadro1[$key]['ss_total_afericion'];
			
			$reporte->Ln();
			$reporte->nuevaFila($dataCuadro1[$key]);			
			$reporte->Ln();

			$totales['qt_total']           += $dataCuadro1[$key]['qt_total'];
			$totales['ss_precio']          += $dataCuadro1[$key]['ss_precio'];
			$totales['ss_total']           += $dataCuadro1[$key]['ss_total'];
			$totales['ss_descuentos']      += $dataCuadro1[$key]['ss_descuentos'];
			$totales['ss_total_afericion'] += $dataCuadro1[$key]['ss_total_afericion'];
			$totales['total']              += $dataCuadro1[$key]['total'];	
			
			$dataProducto[] = array("id_item" => $dataCuadro1[$key]['id_item'],
									"qt_total" => $dataCuadro1[$key]['qt_total'],
								    "total" => $dataCuadro1[$key]['ss_total'] + $dataCuadro1[$key]['ss_descuentos'] - $dataCuadro1[$key]['ss_total_afericion']);
		}
		$reporte->nuevaFila($totales); 

		$reporte->templates = Array();
		$reporte->cabeceraImagen = Array();
		$reporte->cabeceraSize = Array();
		$reporte->cab_default = Array();

		/*** Obtener total ***/
		$total_cuadro1 = $totales['total'];
		$total_cuadro1_backup = $total_cuadro1;	
		/***/

		
		/*** Agregado 2020-01-22 ***/
		// echo "<script>console.log('Market: " . json_encode($arrResultObtieneMarket) . "')</script>";
		foreach ($dataCuadro29['propiedades']['ESTACION']['almacenes'] as $key => $value) {
			$total_market = $value['2'] - $value['3'] - $value['4'] - $value['5'] - $value['6'] - $value['7'] - $value['8'] - $value['9'] - $value['10'];
			// echo "<script>console.log('" . json_encode($total_market) . "')</script>";
		}
		$total_cuadro1 = $total_cuadro1 + $total_market;
		$total_cuadro1_backup = $total_cuadro1_backup + $total_market;
		/***/
		
		/************************ CUADRO 2 *************************/		
		$cab_cuadro2_titulo = Array(
			"titulo" => "DETALLE - LIQUIDACION - cuadro N. 2"
		);
		$cab_cuadro2 = Array(
			"descripcion" => "Descripcion",
			"total"       => "Total"						
		);
				
		$reporte->definirCabeceraPredeterminada($cab_cuadro2_titulo, "_cab");
		$reporte->definirCabeceraPredeterminada($cab_cuadro2);

		$reporte->definirColumna("titulo", $reporte->TIPO_TEXTO, 80, "L", "_cab");
		$reporte->definirColumna("descripcion", $reporte->TIPO_TEXTO, 25, "L");
		$reporte->definirColumna("total", $reporte->TIPO_TEXTO, 25, "L");
									
		$reporte->AddPage();	

		$totalMarket = array();
		$totalMarket['descripcion'] = "TOTAL MARKET";		
		$totalMarket['total'] = $total_market;	
		$reporte->Ln();
		$reporte->nuevaFila($totalMarket);
		$reporte->Ln();

		$totales['descripcion'] = "TOTAL NETO";		
		$totales['total'] = $total_cuadro1;	
		$reporte->Ln();
		$reporte->nuevaFila($totales);
		$reporte->Ln();

		foreach($dataCuadro2 as $key=>$value){		
			// echo "<script>console.log('" . json_encode($dataCuadro2[$key]) . "')</script>";
			$dataCuadro2[$key]['descripcion'] = "VENTA TOTAL CREDITOS";
			$dataCuadro2[$key]['total']       = $dataCuadro2[$key]['ss_total'];			

			$reporte->Ln();
			$reporte->nuevaFila($dataCuadro2[$key]);			
			$reporte->Ln();

			/*** Obtener Venta total creditos ***/
			$venta_total_creditos += $dataCuadro2[$key]['ss_total'];
			/***/
		}

		// echo "<script>console.log('" . json_encode($dataCuadro21) . "')</script>";
		foreach($dataCuadro21 as $key=>$value){						
			// echo "<script>console.log('" . json_encode($dataCuadro21[$key]) . "')</script>";
			$dataCuadro21[$key]['descripcion'] = $dataCuadro21[$key]['no_tarjeta_credito'];
			$dataCuadro21[$key]['total']       = $dataCuadro21[$key]['ss_total'];

			$reporte->Ln();
			$reporte->nuevaFila($dataCuadro21[$key]);
			$reporte->Ln();

			/*** Obtener Venta total creditos ***/
			$total_tarjeta += $dataCuadro21[$key]['ss_total'];
			/***/
		}

		$total_cuadro1 = $total_cuadro1 - $venta_total_creditos - $total_tarjeta;

		$depositoEvectivo = array();
		$depositoEfectivo['descripcion'] = "DEPOSITOS EFECTIVOS";		
		$depositoEfectivo['total'] = $total_cuadro1;
		$reporte->Ln();
		$reporte->nuevaFila($depositoEfectivo);
		$reporte->Ln();

		$depositoValidado = array();
		$depositoValidado['descripcion'] = "DEPOSITOS VALIDADOS";
		$depositoValidado['total'] = $dataCuadro24['totsol'];
		$reporte->Ln();
		$reporte->nuevaFila($depositoValidado);
		$reporte->Ln();

		$diferenciaDepositos = array();
		$diferenciaDepositos['descripcion'] = "DIFERENCIA DE DEPOSITOS";
		$diferenciaDepositos['total'] = $total_cuadro1 - $dataCuadro24['totsol'];
		$reporte->Ln();
		$reporte->nuevaFila($diferenciaDepositos);
		$reporte->Ln();

		foreach($dataCuadro22 as $key=>$value){
			$reporte->Ln();
			
			// echo "<script>console.log('" . json_encode($dataCuadro22[$key]) . "')</script>";
			$dataCuadro22[$key]['descripcion'] = "GATOS VARIOS";
			$dataCuadro22[$key]['total']       = $dataCuadro22[$key]['ss_total'];
			$total_cuadro1                     = $total_cuadro1 - $dataCuadro22[$key]['ss_total'];

			$reporte->nuevaFila($dataCuadro22[$key]);
			$reporte->Ln();			
		}

		// echo "<script>console.log('" . json_encode($dataCuadro23) . "')</script>";
		if($dataCuadro23['sMessage'] == "No hay registros"):
			$dataCuadro23[$key]['descripcion'] = "DEPOSITOS EFECTIVO";
			$dataCuadro23[$key]['total'] = "0.00";

			$reporte->Ln();
			// echo "<script>console.log('" . json_encode($dataCuadro23[$key]) . "')</script>";
			$reporte->nuevaFila($dataCuadro23[$key]);
			$reporte->Ln();
		else:
			foreach($dataCuadro23['arrData'] as $key=>$value):
				$dataCuadro23[$key]['descripcion'] = "DEPOSITOS EFECTIVO";
				$dataCuadro23[$key]['total'] = $dataCuadro23['arrData'][$key]['ss_total'];
				$total_cuadro1               = $total_cuadro1 - $dataCuadro23['arrData'][$key]['ss_total'];

				$reporte->Ln();
				// echo "<script>console.log('" . json_encode($dataCuadro23[$key]) . "')</script>";
				$reporte->nuevaFila($dataCuadro23[$key]);
				$reporte->Ln();
			endforeach;
		endif;

		$bbvaContinental = array();
		$bbvaContinental['descripcion'] = "BBVA CONTINENTAL";
		$bbvaContinental['total'] = number_format($dataCuadro25['total'], '2', '.', ',');
		$reporte->Ln();
		$reporte->nuevaFila($bbvaContinental);
		$reporte->Ln();	

		$bcp = array();
		$bcp['descripcion'] = "BCP";
		$bcp['total'] = number_format($dataCuadro26['total'], '2', '.', ',');
		$reporte->Ln();
		$reporte->nuevaFila($bcp);
		$reporte->Ln();
		
		$scotiabank = array();
		$scotiabank['descripcion'] = "SCOTIABANK";
		$scotiabank['total'] = number_format($dataCuadro27['total'], '2', '.', ',');
		$reporte->Ln();
		$reporte->nuevaFila($scotiabank);
		$reporte->Ln();

		$interbank = array();
		$interbank['descripcion'] = "INTERBANK";
		$interbank['total'] = number_format($dataCuadro28['total'], '2', '.', ',');
		$reporte->Ln();
		$reporte->nuevaFila($interbank);
		$reporte->Ln();		

		$diferenciaTotal = array();
		$diferenciaTotal['descripcion'] = "DIFERENCIA DE VENTAS";
		$diferenciaTotal['total'] = number_format($total_cuadro1, '2', '.', ',');
		$reporte->Ln();
		$reporte->nuevaFila($diferenciaTotal);
		$reporte->Ln();

		$reporte->templates = Array();
		$reporte->cabeceraImagen = Array();
		$reporte->cabeceraSize = Array();
		$reporte->cab_default = Array();

		/************************ CUADRO 3 *************************/		
		$cab_cuadro3_titulo = Array(			
			"combo84"  => "Combo 84",
			"combo90"  => "Combo 90",
			"combo97"  => "Combo 97",
			"combod2"  => "Combo D2",
			"combo95"  => "Combo 95",
			"comboglp" => "Combo GLP"			
		);

		$cab_cuadro3 = Array(
			"cliente"  => "Cliente",
			"pu84"  => "P.U.",
			"gal84" => "GAL.",
			"imp84" => "IMP.",
			"pu90"  => "P.U.",
			"gal90" => "GAL.",
			"imp90" => "IMP.",
			"pu97"  => "P.U.",
			"gal97" => "GAL.",
			"imp97" => "IMP.",
			"pud2"  => "P.U.",
			"gald2" => "GAL.",
			"impd2" => "IMP.",
			"pu95"  => "P.U.",
			"gal95" => "GAL.",
			"imp95" => "IMP.",
			"puglp"  => "P.U.",
			"galglp" => "GAL.",
			"impglp" => "IMP.",
			"imptot"   => "IMP. TOT."			
		);
				
		$reporte->definirCabeceraPredeterminada($cab_cuadro3_titulo, "_cab");
		$reporte->definirCabeceraPredeterminada($cab_cuadro3);

		$reporte->definirColumna("blank", $reporte->TIPO_TEXTO, 30, "C", "_cab");
		$reporte->definirColumna("combo84", $reporte->TIPO_TEXTO, 20, "C", "_cab");
		$reporte->definirColumna("combo90", $reporte->TIPO_TEXTO, 20, "C", "_cab");
		$reporte->definirColumna("combo97", $reporte->TIPO_TEXTO, 20, "C", "_cab");
		$reporte->definirColumna("combod2", $reporte->TIPO_TEXTO, 20, "C", "_cab");
		$reporte->definirColumna("combo95", $reporte->TIPO_TEXTO, 20, "C", "_cab");
		$reporte->definirColumna("comboglp", $reporte->TIPO_TEXTO, 20, "C", "_cab");
		$reporte->definirColumna("blank", $reporte->TIPO_TEXTO, 18, "C", "_cab");

		$reporte->definirColumna("cliente", $reporte->TIPO_TEXTO, 18, "L");
		$reporte->definirColumna("pu84", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("gal84", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("imp84", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("pu90", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("gal90", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("imp90", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("pu97", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("gal97", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("imp97", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("pud2", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("gald2", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("impd2", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("pu95", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("gal95", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("imp95", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("puglp", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("galglp", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("impglp", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("imptot", $reporte->TIPO_TEXTO, 18, "L");	
									
		$reporte->AddPage();	

		$credito1 = 0.00;
		$credito2 = 0.00;
		$credito3 = 0.00;
		$credito4 = 0.00;
		$credito5 = 0.00;
		$credito6 = 0.00;
		$importe1 = 0.00;
		$importe2 = 0.00;
		$importe3 = 0.00;
		$importe4 = 0.00;
		$importe5 = 0.00;
		$importe6 = 0.00;
		$total_cuadro3 = 0.00;
		$total_fila = 0.00;
		$ventasDetalladasCredito = array();
		foreach($dataCuadro3 as $key=>$value){	
			// echo "<script>console.log('" . json_encode($dataCuadro3[$key]['no_cliente']) . "')</script>";				
			// echo "<script>console.log('" . json_encode($id_cliente_anterior) . "')</script>";				
			// echo "<script>console.log('" . json_encode($id_cliente) . "')</script>";			
			// echo "<script>console.log('*****')</script>";
			
			$id_cliente_anterior = $dataCuadro3[($key-1)]['id_cliente'];			
			$id_cliente = $dataCuadro3[$key]['id_cliente'];			
			if($key != 0){
				if(trim($id_cliente) != trim($id_cliente_anterior)){									
					// $reporte->Ln();
					$reporte->nuevaFila($ventasDetalladasCredito);
					// $reporte->Ln();
					$ventasDetalladasCredito = array();
					$total_fila = 0;
				}						
			}									

			$ventasDetalladasCredito['cliente'] = $dataCuadro3[$key]['no_cliente'];
			// echo "<script>console.log('" . json_encode($dataCuadro3[$key]['id_item']) . "')</script>";
			// echo "<script>console.log('" . json_encode($dataCuadro3[$key]) . "')</script>";

			if ( trim($dataCuadro3[$key]['id_item'])=='11620301' ) {				
				$ventasDetalladasCredito['pu84'] = $dataCuadro3[$key]['ss_precio'];
				$ventasDetalladasCredito['gal84'] = $dataCuadro3[$key]['qt_cantidad'];
				$ventasDetalladasCredito['imp84'] = $dataCuadro3[$key]['ss_total'];
				$credito1 += $dataCuadro3[$key]['qt_cantidad'];
				$importe1 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620302' ) {				
				$ventasDetalladasCredito['pu90'] = $dataCuadro3[$key]['ss_precio'];
				$ventasDetalladasCredito['gal90'] = $dataCuadro3[$key]['qt_cantidad'];
				$ventasDetalladasCredito['imp90'] = $dataCuadro3[$key]['ss_total'];
				$credito2 += $dataCuadro3[$key]['qt_cantidad'];
				$importe2 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620303' ) {
				$ventasDetalladasCredito['pu97'] = $dataCuadro3[$key]['ss_precio'];
				$ventasDetalladasCredito['gal97'] = $dataCuadro3[$key]['qt_cantidad'];
				$ventasDetalladasCredito['imp97'] = $dataCuadro3[$key]['ss_total'];
				$credito3 += $dataCuadro3[$key]['qt_cantidad'];
				$importe3 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620304' ) {
				$ventasDetalladasCredito['pud2'] = $dataCuadro3[$key]['ss_precio'];
				$ventasDetalladasCredito['gald2'] = $dataCuadro3[$key]['qt_cantidad'];
				$ventasDetalladasCredito['impd2'] = $dataCuadro3[$key]['ss_total'];
				$credito4 += $dataCuadro3[$key]['qt_cantidad'];
				$importe4 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620305' ) {
				$ventasDetalladasCredito['pu95'] = $dataCuadro3[$key]['ss_precio'];
				$ventasDetalladasCredito['gal95'] = $dataCuadro3[$key]['qt_cantidad'];
				$ventasDetalladasCredito['imp95'] = $dataCuadro3[$key]['ss_total'];
				$credito5 += $dataCuadro3[$key]['qt_cantidad'];
				$importe5 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if ( trim($dataCuadro3[$key]['id_item'])=='11620307' ) {
				$ventasDetalladasCredito['puglp'] = $dataCuadro3[$key]['ss_precio'];
				$ventasDetalladasCredito['galglp'] = $dataCuadro3[$key]['qt_cantidad'];
				$ventasDetalladasCredito['impglp'] = $dataCuadro3[$key]['ss_total'];
				$credito6 += $dataCuadro3[$key]['qt_cantidad'];
				$importe6 += $dataCuadro3[$key]['ss_total'];
				$total_fila += $dataCuadro3[$key]['ss_total'];
			}
			if (trim($dataCuadro3[$key]['id_item'])=='11620301' ||
				trim($dataCuadro3[$key]['id_item'])=='11620302' || 
				trim($dataCuadro3[$key]['id_item'])=='11620303' || 
				trim($dataCuadro3[$key]['id_item'])=='11620304' || 
				trim($dataCuadro3[$key]['id_item'])=='11620305' ||
				trim($dataCuadro3[$key]['id_item'])=='11620307'){				
				$ventasDetalladasCredito['imptot'] = $total_fila;
				$total_cuadro3 += $dataCuadro3[$key]['ss_total'];
			}			

			if($dataCuadro3[$key] == end($dataCuadro3)){				
				$reporte->nuevaFila($ventasDetalladasCredito);
			}

			// echo "<script>console.log('" . json_encode($ventasDetalladasCredito) . "')</script>";
			// $reporte->Ln();
			// $reporte->nuevaFila($ventasDetalladasCredito);
			// $reporte->Ln();
			// $ventasDetalladasCredito = [];
		}		

		$dataTotalCredito = array();
		$dataTotalCredito['cliente'] = "TOTAL CREDITO";
		$dataTotalCredito['gal84'] = $credito1;
		$dataTotalCredito['gal90'] = $credito2;
		$dataTotalCredito['gal97'] = $credito3;
		$dataTotalCredito['gald2'] = $credito4;
		$dataTotalCredito['gal95'] = $credito5;
		$dataTotalCredito['galglp'] = $credito6;
		$dataTotalCredito['imp84'] = $importe1;
		$dataTotalCredito['imp90'] = $importe2;
		$dataTotalCredito['imp97'] = $importe3;
		$dataTotalCredito['impd2'] = $importe4;
		$dataTotalCredito['imp95'] = $importe5;
		$dataTotalCredito['impglp'] = $importe6;
		$dataTotalCredito['imptot'] = $total_cuadro3;
		$reporte->Ln();
		$reporte->nuevaFila($dataTotalCredito);
		$reporte->Ln();

		// echo "<script>console.log('" . json_encode($dataProducto) . "')</script>";
		// die();
		$dataTotalContado = array();		

		$dataTotalContado['cliente'] = "TOTAL CONTADO";
		foreach($dataProducto as $key=>$value){
			if ( $dataProducto[$key]['id_item']=='11620301' ) {																				
				$dataTotalContado['gal84'] = $dataProducto[$key]['qt_total'] - $credito1;			
				$dataTotalContado['imp84'] = $dataProducto[$key]['total'] - $importe1;
			}
			if ( $dataProducto[$key]['id_item']=='11620302' ) {																				
				$dataTotalContado['gal90'] = $dataProducto[$key]['qt_total'] - $credito2;
				$dataTotalContado['imp90'] = $dataProducto[$key]['total'] - $importe2;
			}			
			if ( $dataProducto[$key]['id_item']=='11620303' ) {																				
				$dataTotalContado['gal97'] = $dataProducto[$key]['qt_total'] - $credito3;
				$dataTotalContado['imp97'] = $dataProducto[$key]['total'] - $importe3;
			}			
			if ( $dataProducto[$key]['id_item']=='11620304' ) {																				
				$dataTotalContado['gald2'] = $dataProducto[$key]['qt_total'] - $credito4;
				$dataTotalContado['impd2'] = $dataProducto[$key]['total'] - $importe4;
			}			
			if ( $dataProducto[$key]['id_item']=='11620305' ) {																				
				$dataTotalContado['gal95'] = $dataProducto[$key]['qt_total'] - $credito5;
				$dataTotalContado['imp95'] = $dataProducto[$key]['total'] - $importe5;
			}			
			if ( $dataProducto[$key]['id_item']=='11620307' ) {																				
				$dataTotalContado['galglp'] = $dataProducto[$key]['qt_total'] - $credito6;
				$dataTotalContado['impglp'] = $dataProducto[$key]['total'] - $importe6;
			}			
		}	
		// echo "<script>console.log('" . json_encode($total_cuadro1_backup) . "')</script>";
		// die();
		$dataTotalContado['imptot'] = $total_cuadro1_backup - $total_cuadro3;
		$reporte->Ln();
		$reporte->nuevaFila($dataTotalContado);
		$reporte->Ln();

		$reporte->templates = Array();
		$reporte->cabeceraImagen = Array();
		$reporte->cabeceraSize = Array();
		$reporte->cab_default = Array();

		/************************ CUADRO 4 *************************/
		$cab_cuadro4_titulo = Array(
			"titulo" => "CONTROL DE INVENTARIO - cuadro N. 4"
		);
		$cab_cuadro4 = Array(
			"fecha"         => "Fecha",
			"producto"      => "Producto",
			"inv_inicial"   => "Inv. Inicial",
			"compras"       => "Compras",
			"ventas"        => "Ventas",
			"inv_final"     => "Inv. Final",
			"varillaje"     => "Varillaje",
			"dif_diaria"    => "Dif. Diaria",
			"dif_acumulada" => "Dif. Acumulada"
		);

		$reporte->definirCabeceraPredeterminada($cab_cuadro4_titulo, "_cab");
		$reporte->definirCabeceraPredeterminada($cab_cuadro4);

		$reporte->definirColumna("titulo", $reporte->TIPO_TEXTO, 70, "L", "_cab");		

		$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("producto", $reporte->TIPO_TEXTO, 25, "L");
		$reporte->definirColumna("inv_inicial", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("compras", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("ventas", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("inv_final", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("varillaje", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("dif_diaria", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("dif_acumulada", $reporte->TIPO_TEXTO, 15, "L");		
									
		$reporte->AddPage();	

		//echo "<script>console.log('" . json_encode($dataCuadro4) . "')</script>";
		// die();
		if($dataCuadro4_GASOHOL84 != null){			
			$dataTitulo = array();
			$dataTitulo['compras'] = "11620301";
			$dataTitulo['ventas'] = "GASOHOL 84";
			$reporte->Ln();			
			$reporte->nuevaFila($dataTitulo);
			$reporte->Ln();

			$dataControlInventario = array();		
			foreach($dataCuadro4_GASOHOL84 as $key=>$value){		
				$inv_final = $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GASOHOL84[$key]['qt_total_compra'] + $dataCuadro4_GASOHOL84[$key]['qt_total_entrada'] - $dataCuadro4_GASOHOL84[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_hoy'] - $inv_final;

				$dataControlInventario['fecha']         = $dataCuadro4_GASOHOL84[$key]['fecha'];
				$dataControlInventario['producto']      = $dataCuadro4_GASOHOL84[$key]['id_item']." - ".$dataCuadro4_GASOHOL84[$key]['no_item'];
				$dataControlInventario['inv_inicial']   = ($dataCuadro4_GASOHOL84[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '');
				$dataControlInventario['compras']       = ($dataCuadro4_GASOHOL84[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GASOHOL84[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['ventas']        = ($dataCuadro4_GASOHOL84[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GASOHOL84[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['inv_final']     = ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '');
				$dataControlInventario['varillaje']     = ($dataCuadro4_GASOHOL84[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_diaria']    = ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_acumulada'] = ($dataCuadro4_GASOHOL84[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GASOHOL84[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '');			

				// $reporte->Ln();
				//echo "<script>console.log('" . json_encode($dataControlInventario) . "')</script>";
				$reporte->nuevaFila($dataControlInventario);
				// $reporte->Ln();

				$dataControlInventario = array();
			}		
			//die();	
		}

		if($dataCuadro4_GASOHOL90 != null){			
			$dataTitulo = array();
			$dataTitulo['compras'] = "11620302";
			$dataTitulo['ventas'] = "GASOHOL 90";
			$reporte->Ln();			
			$reporte->nuevaFila($dataTitulo);
			$reporte->Ln();

			$dataControlInventario = array();		
			foreach($dataCuadro4_GASOHOL90 as $key=>$value){		
				$inv_final = $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GASOHOL90[$key]['qt_total_compra'] + $dataCuadro4_GASOHOL90[$key]['qt_total_entrada'] - $dataCuadro4_GASOHOL90[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_hoy'] - $inv_final;

				$dataControlInventario['fecha']         = $dataCuadro4_GASOHOL90[$key]['fecha'];
				$dataControlInventario['producto']      = $dataCuadro4_GASOHOL90[$key]['id_item']." - ".$dataCuadro4_GASOHOL90[$key]['no_item'];
				$dataControlInventario['inv_inicial']   = ($dataCuadro4_GASOHOL90[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '');
				$dataControlInventario['compras']       = ($dataCuadro4_GASOHOL90[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GASOHOL90[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['ventas']        = ($dataCuadro4_GASOHOL90[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GASOHOL90[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['inv_final']     = ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '');
				$dataControlInventario['varillaje']     = ($dataCuadro4_GASOHOL90[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_diaria']    = ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_acumulada'] = ($dataCuadro4_GASOHOL90[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GASOHOL90[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '');			

				// $reporte->Ln();
				//echo "<script>console.log('" . json_encode($dataControlInventario) . "')</script>";
				$reporte->nuevaFila($dataControlInventario);
				// $reporte->Ln();

				$dataControlInventario = array();
			}
		}

		if($dataCuadro4_GASOHOL97 != null){			
			$dataTitulo = array();
			$dataTitulo['compras'] = "11620303";
			$dataTitulo['ventas'] = "GASOHOL 97";
			$reporte->Ln();			
			$reporte->nuevaFila($dataTitulo);
			$reporte->Ln();

			$dataControlInventario = array();		
			foreach($dataCuadro4_GASOHOL97 as $key=>$value){		
				$inv_final = $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GASOHOL97[$key]['qt_total_compra'] + $dataCuadro4_GASOHOL97[$key]['qt_total_entrada'] - $dataCuadro4_GASOHOL97[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_hoy'] - $inv_final;

				$dataControlInventario['fecha']         = $dataCuadro4_GASOHOL97[$key]['fecha'];
				$dataControlInventario['producto']      = $dataCuadro4_GASOHOL97[$key]['id_item']." - ".$dataCuadro4_GASOHOL97[$key]['no_item'];
				$dataControlInventario['inv_inicial']   = ($dataCuadro4_GASOHOL97[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '');
				$dataControlInventario['compras']       = ($dataCuadro4_GASOHOL97[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GASOHOL97[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['ventas']        = ($dataCuadro4_GASOHOL97[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GASOHOL97[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['inv_final']     = ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '');
				$dataControlInventario['varillaje']     = ($dataCuadro4_GASOHOL97[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_diaria']    = ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_acumulada'] = ($dataCuadro4_GASOHOL97[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GASOHOL97[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '');			

				// $reporte->Ln();
				//echo "<script>console.log('" . json_encode($dataControlInventario) . "')</script>";
				$reporte->nuevaFila($dataControlInventario);
				// $reporte->Ln();

				$dataControlInventario = array();
			}
		}

		if($dataCuadro4_DIESELB5 != null){			
			$dataTitulo = array();
			$dataTitulo['compras'] = "11620304";
			$dataTitulo['ventas'] = "DIESEL B5";
			$reporte->Ln();			
			$reporte->nuevaFila($dataTitulo);
			$reporte->Ln();

			$dataControlInventario = array();		
			foreach($dataCuadro4_DIESELB5 as $key=>$value){		
				$inv_final = $dataCuadro4_DIESELB5[$key]['qt_total_varilla_ayer'] + $dataCuadro4_DIESELB5[$key]['qt_total_compra'] + $dataCuadro4_DIESELB5[$key]['qt_total_entrada'] - $dataCuadro4_DIESELB5[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_DIESELB5[$key]['qt_total_varilla_hoy'] - $inv_final;

				$dataControlInventario['fecha']         = $dataCuadro4_DIESELB5[$key]['fecha'];
				$dataControlInventario['producto']      = $dataCuadro4_DIESELB5[$key]['id_item']." - ".$dataCuadro4_DIESELB5[$key]['no_item'];
				$dataControlInventario['inv_inicial']   = ($dataCuadro4_DIESELB5[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_DIESELB5[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '');
				$dataControlInventario['compras']       = ($dataCuadro4_DIESELB5[$key]['qt_total_compra'] != null)       ? $dataCuadro4_DIESELB5[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['ventas']        = ($dataCuadro4_DIESELB5[$key]['qt_total_salida'] != null)       ? $dataCuadro4_DIESELB5[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['inv_final']     = ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '');
				$dataControlInventario['varillaje']     = ($dataCuadro4_DIESELB5[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_DIESELB5[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_diaria']    = ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_acumulada'] = ($dataCuadro4_DIESELB5[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_DIESELB5[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '');			

				// $reporte->Ln();
				//echo "<script>console.log('" . json_encode($dataControlInventario) . "')</script>";
				$reporte->nuevaFila($dataControlInventario);
				// $reporte->Ln();

				$dataControlInventario = array();
			}
		}

		if($dataCuadro4_GASOHOL95 != null){			
			$dataTitulo = array();
			$dataTitulo['compras'] = "11620305";
			$dataTitulo['ventas'] = "GASOHOL 95";
			$reporte->Ln();			
			$reporte->nuevaFila($dataTitulo);
			$reporte->Ln();

			$dataControlInventario = array();		
			foreach($dataCuadro4_GASOHOL95 as $key=>$value){		
				$inv_final = $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GASOHOL95[$key]['qt_total_compra'] + $dataCuadro4_GASOHOL95[$key]['qt_total_entrada'] - $dataCuadro4_GASOHOL95[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_hoy'] - $inv_final;

				$dataControlInventario['fecha']         = $dataCuadro4_GASOHOL95[$key]['fecha'];
				$dataControlInventario['producto']      = $dataCuadro4_GASOHOL95[$key]['id_item']." - ".$dataCuadro4_GASOHOL95[$key]['no_item'];
				$dataControlInventario['inv_inicial']   = ($dataCuadro4_GASOHOL95[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '');
				$dataControlInventario['compras']       = ($dataCuadro4_GASOHOL95[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GASOHOL95[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['ventas']        = ($dataCuadro4_GASOHOL95[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GASOHOL95[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['inv_final']     = ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '');
				$dataControlInventario['varillaje']     = ($dataCuadro4_GASOHOL95[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_diaria']    = ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_acumulada'] = ($dataCuadro4_GASOHOL95[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GASOHOL95[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '');			

				// $reporte->Ln();
				//echo "<script>console.log('" . json_encode($dataControlInventario) . "')</script>";
				$reporte->nuevaFila($dataControlInventario);
				// $reporte->Ln();

				$dataControlInventario = array();
			}
		}		

		if($dataCuadro4_GLP != null){			
			$dataTitulo = array();
			$dataTitulo['compras'] = "11620307";
			$dataTitulo['ventas'] = "GLP";
			$reporte->Ln();			
			$reporte->nuevaFila($dataTitulo);
			$reporte->Ln();

			$dataControlInventario = array();		
			foreach($dataCuadro4_GLP as $key=>$value){		
				$inv_final = $dataCuadro4_GLP[$key]['qt_total_varilla_ayer'] + $dataCuadro4_GLP[$key]['qt_total_compra'] + $dataCuadro4_GLP[$key]['qt_total_entrada'] - $dataCuadro4_GLP[$key]['qt_total_salida'];
				$dif_diaria = $dataCuadro4_GLP[$key]['qt_total_varilla_hoy'] - $inv_final;

				$dataControlInventario['fecha']         = $dataCuadro4_GLP[$key]['fecha'];
				$dataControlInventario['producto']      = $dataCuadro4_GLP[$key]['id_item']." - ".$dataCuadro4_GLP[$key]['no_item'];
				$dataControlInventario['inv_inicial']   = ($dataCuadro4_GLP[$key]['qt_total_varilla_ayer'] != null) ? $dataCuadro4_GLP[$key]['qt_total_varilla_ayer'] : number_format(0.00, 2, '.', '');
				$dataControlInventario['compras']       = ($dataCuadro4_GLP[$key]['qt_total_compra'] != null)       ? $dataCuadro4_GLP[$key]['qt_total_compra']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['ventas']        = ($dataCuadro4_GLP[$key]['qt_total_salida'] != null)       ? $dataCuadro4_GLP[$key]['qt_total_salida']       : number_format(0.00, 2, '.', '');
				$dataControlInventario['inv_final']     = ($inv_final != null)                                  ? $inv_final                                  : number_format(0.00, 2, '.', '');
				$dataControlInventario['varillaje']     = ($dataCuadro4_GLP[$key]['qt_total_varilla_hoy'] != null)  ? $dataCuadro4_GLP[$key]['qt_total_varilla_hoy']  : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_diaria']    = ($dif_diaria != null)                                 ? $dif_diaria                                 : number_format(0.00, 2, '.', '');
				$dataControlInventario['dif_acumulada'] = ($dataCuadro4_GLP[$key]['qt_total_varilla_mes'] != null)  ? $dataCuadro4_GLP[$key]['qt_total_varilla_mes']  : number_format(0.00, 2, '.', '');			

				// $reporte->Ln();
				//echo "<script>console.log('" . json_encode($dataControlInventario) . "')</script>";
				$reporte->nuevaFila($dataControlInventario);
				// $reporte->Ln();

				$dataControlInventario = array();
			}
		}		

		$reporte->Output("/sistemaweb/combustibles/reportes/pdf/VentasDiarias.pdf", "F");
		echo '<script> window.open("/sistemaweb/combustibles/reportes/pdf/VentasDiarias.pdf","miwin","width=950,height=650,scrollbars=yes, resizable=yes, menubar=no");</script>';
		exit;
	}	

}

