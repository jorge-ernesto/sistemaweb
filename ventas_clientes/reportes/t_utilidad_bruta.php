<?php

class UtilidadBrutaTemplate extends Template {
    
	function search_form() {
		$fecha = date("d/m/Y");
		$estaciones = UtilidadBrutaModel::obtieneListaEstaciones();
		$tipo = array("K"=>"Kardex","T"=>"Tickets");
		$uprecio = array("P"=>"Promedio","U"=>"Ultimo");
		$arrDetalladoPorDia = array("0"=>"No","1"=>"Si");

		$form = new form2("Reporte de Utilidad Bruta por Almacen", "form_utilidad_bruta", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.UTILIDADBRUTA"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><table border="0" cellspacing="5"><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $fecha, '', 10, 10));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_utilidad_bruta.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha, '', 10, 10));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_utilidad_bruta.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipo", "Tipo de Cantidad de Venta: ", "", $tipo, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("uprecio", "Calculo de Ultimo Costo: ", "", $uprecio, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("iDetalladoPorDia", "Detallado por dia: ", "", $arrDetalladoPorDia, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
    }

	function reporte($results,$market,$ucosto_combu,$vta_combu, $iAlmacen, $dHasta, $iTipoCalculoCosto, $iDetalladoPorDia){
		$limitemar = count($market);
		$xlinea	= "";
		$totales = Array();
		$t = 0;

		$linea = NULL;
		$total_total = 0;
		$total_totalb = 0;
		$total_linea = NULL;
		$total_lineab = NULL;

		$result  = '';

		$result .= '<div align="center">';
		$result .= '<table cellspacing="0">';
		$result .= '<tr><td colspan="8" align="center" style="color: blue">&nbsp; MARKET &nbsp;</td></tr>';
		$result .= '<tr><td colspan="8" align="center" style="color: blue">&nbsp;</td></tr>';
		$result .= '</table>';
		$result .= '</div>';

		$result .= '<div align="center">';
		$result .= '<table border="1" cellspacing="0">';

		for($i = 0; $i < $limitemar; $i++) {
			$codigo 	= $market[$i]['codigo'];
			$articulo 	= $market[$i]['articulo'];
			$costovta 	= $market[$i]['costovta'];

			$ultmcosto 	= $market[$i]['ultmcosto'];
			if($iTipoCalculoCosto == "U"){
				$arrData = UtilidadBrutaModel::obtenerCostoUltimaCompra($iAlmacen, $dHasta, $codigo);
				$ultmcosto = $arrData["result"];
			}

			$ganancia = number_format($costovta, 2, '.', '') - number_format($ultmcosto, 2, '.', '');

			$margen = ($ganancia*100);
			if($ultmcosto > 0.000000 || $ultmcosto < 0.000000)
				$margen 	= ($ganancia*100) / number_format($ultmcosto, 2, '.', '');
			
			$cantidad 	= $market[$i]['cantidad'];
			$utilidad 	= ($ganancia * number_format($cantidad, 2, '.', ''));
			$almacen 	= $market[$i]['almacen'];
			$linea 		= $market[$i]['linea'];
			$vimporte 	= $market[$i]['vimporte'];
			$kimporte 	= $market[$i]['kimporte'];
			$bruta		= ($vimporte - $kimporte);

			$total_linea[$linea] = $total_linea[$linea] + $utilidad;
			$total_lineab[$linea] = $total_lineab[$linea] + $bruta;

			if($xlinea != $linea and $i > 0) {
				$totales[$t]['codigo_linea'] = $xlinea;
				$totales[$t]['nombre_linea'] = UtilidadBrutaModel::nombre_linea($xlinea);
				$totales[$t]['total_linea']  = $total_linea[$xlinea];
				$totales[$t]['total_lineab']  = $total_lineab[$xlinea];

				$t++;

				$result .= '<tr bgcolor="#BCF5A9">';
				$result .= '<td colspan="7" align="right">TOTAL LINEA </td>';
				$result .= '<td align="right">'.number_format($total_linea[$xlinea],2).'</td>';	
				$result .= '<td colspan="2"><td align="right">'.number_format($total_lineab[$xlinea],2).'</td>';
				$result .= '</tr>';
			}

			if($xlinea != $linea){
				$nomlinea = UtilidadBrutaModel::nombre_linea($linea);				

				$result .= '<tr bgcolor="#A9D0F5">';
				$result .= '<td>LINEA</td>';
				$result .= '<td colspan="10">'.$linea.' - '.$nomlinea.'</td>';		
				$result .= '</tr>';

				$result .= '<tr bgcolor="#F2F5A9">';
				$result .= '<td align="center">&nbsp;&nbsp;Codigo&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Articulo&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Costo Vta. S/IGV&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Ultm. Costo S/IGV&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Ganancia&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Margen&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Cant. Venta&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Total Ganancia&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Venta Total S/. S/IGV.&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Costo Total S/. S/IGV.&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Utilidad Bruta Total S/. &nbsp;&nbsp;</td>';

				$result .= '</tr>';
			}

			$result .= '<tr>';
			$result .= '<td>&nbsp;'.$codigo.'&nbsp;</td>';
			$result .= '<td>&nbsp;'.$articulo.'</td>';
			$result .= '<td align="right">'.number_format($costovta,2).'</td>';
			$result .= '<td align="right">'.number_format($ultmcosto,2).'</td>';
			$result .= '<td align="right">'.(number_format($costovta,2) - number_format($ultmcosto,2)).'</td>';
			$result .= '<td align="right">'.number_format($margen,2).' %</td>';
			$result .= '<td align="right">'.number_format($cantidad,2).'</td>';	
			$result .= '<td align="right">'.number_format($utilidad,2).'</td>';
			$result .= '<td align="right">'.number_format($vimporte,2).'</td>';
			$result .= '<td align="right">'.number_format($kimporte,2).'</td>';
			$result .= '<td align="right">'.number_format($bruta,2).'</td>';
			$result .= '</tr>';

			$xlinea	= $linea;  


		}

		if($limitemar > 0){
			$totales[$t]['codigo_linea'] = $xlinea;
			$totales[$t]['nombre_linea'] = UtilidadBrutaModel::nombre_linea($xlinea);
			$totales[$t]['total_linea']  = $total_linea[$xlinea];
			$totales[$t]['total_lineab']  = $total_lineab[$xlinea];
			$result .= '<tr bgcolor="#BCF5A9">';
			$result .= '<td colspan="7" align="right">TOTAL LINEA</td>';
			$result .= '<td align="right">'.number_format($total_linea[$xlinea],2).'</td>';	
			$result .= '<td colspan="2"><td align="right">'.number_format($total_lineab[$xlinea],2).'</td>';
			$result .= '</tr>';
		}

		$result .= '</table>';
		$result .= '</div><br><br>';

		// RESUMEN DE UTILIDAD BRUTA POR LINEA
		
		$result .= '<div align="center">';
		$result .= '<table border="0" cellspacing="0">';
		$result .= '<tr><td colspan="4" align="center" style="color: blue">RESUMEN POR LINEAS DE MARKET</td></tr>';
		$result .= '<tr><td colspan="2">&nbsp;</td><td>&nbsp;Utilidad Bruta&nbsp;</td>';
		$result .= '<td>&nbsp;Utilidad Bruta Totales&nbsp;</td></tr>';


		for($i = 0; $i < count($totales); $i++) {
			$result .= '<tr>';
			$result .= '<td>&nbsp;'.$totales[$i]['codigo_linea'].'&nbsp;</td>';
			$result .= '<td>&nbsp;'.$totales[$i]['nombre_linea'].'</td>';
			$result .= '<td align="right">'.number_format($totales[$i]['total_linea'],2).'</td>';
			$result .= '<td align="right">'.number_format($totales[$i]['total_lineab'],2).'</td>';
			$result .= '</tr>';
			$total_total 	= $total_total + $totales[$i]['total_linea'];
			$total_totalb 	= $total_totalb + $totales[$i]['total_lineab'];
		}

		$result .= '<tr><td>&nbsp;</td><td  style="color: blue">&nbsp;Total General Market&nbsp;</td><td align="right" style="color: blue">'.number_format($total_total,2).'</td><td align="right" style="color: blue">'.number_format($total_totalb,2).'</td></tr>';
		$result .= '</table>';
		$result .= '</div>';
		/* FIN DE MARKET */

		$limite	= count($results);
		$xlinea	= "";
		$totales = Array();
		$t = 0;
		$total_total = 0;

		$colspan_header_combustible="8";
		if ($iDetalladoPorDia == 1){
			$colspan_header_combustible="9";
		}

		$result .= '<div align="center">';
		$result .= '<table cellspacing="0">';
		$result .= '<tr><td colspan="' . $colspan_header_combustible . '" align="center" style="color: blue">&nbsp;</td></tr>';
		$result .= '<tr><td colspan="' . $colspan_header_combustible . '" align="center" style="color: blue">&nbsp; COMBUSTIBLE &nbsp;</td></tr>';
		$result .= '<tr><td colspan="' . $colspan_header_combustible . '" align="center" style="color: blue">&nbsp;</td></tr>';
		$result .= '</table>';
		$result .= '</div>';

		$result .= '<div align="center">';
		$result .= '<table border="1" cellspacing="0">';

		$dFechaParte = '';

		for($i = 0; $i < $limite; $i++) {
			$codigo 	= $results[$i]['codigo'];
			$articulo 	= $results[$i]['articulo'];
			$costovta 	= $results[$i]['costovta'];
			//$costovta 	= $vta_combu[$i]['costovta'];
			
			$ultmcosto 	= $results[$i]['ultmcosto'];
			//$ultmcosto 	= $ucosto_combu[$i]['ultmcosto'];
			if($iTipoCalculoCosto == "U"){
				$arrData = UtilidadBrutaModel::obtenerCostoUltimaCompra($iAlmacen, $dHasta, $codigo);
				$ultmcosto = $arrData["result"];
			}

			$ganancia = number_format($costovta, 2, '.', '') - number_format($ultmcosto, 2, '.', '');

			$margen = ($ganancia*100);
			if($ultmcosto > 0.000000 || $ultmcosto < 0.000000)
				$margen 	= ($ganancia*100) / number_format($ultmcosto, 2, '.', '');

			$cantidad 	= $results[$i]['cantidad'];
			$afericiones= $results[$i]['afericiones'];

			$totales_cantidad = $cantidad - $afericiones;

			$utilidad 	= ($ganancia * number_format($totales_cantidad, 2, '.', ''));
			$almacen 	= $results[$i]['almacen'];
			$linea 		= $results[$i]['linea'];

			$total_linea[$linea] = $total_linea[$linea] + $utilidad;
			//if ( $dFechaParte != $results[$i]['dt_fechaparte'] ) {
				if($xlinea != $linea and $i > 0) {
					$totales[$t]['codigo_linea'] = $xlinea;
					$totales[$t]['nombre_linea'] = UtilidadBrutaModel::nombre_linea($xlinea);
					$totales[$t]['total_linea']  = $total_linea[$xlinea];
					$t++;

					$result .= '<tr bgcolor="#BCF5A9">';
					$result .= '<td colspan="' . $colspan_header_combustible . '" align="right">TOTAL LINEA </td>';//Todas las filas y no muestra la ultima
					$result .= '<td align="right">'.number_format($total_linea[$xlinea],2).'</td>';	
					$result .= '</tr>';

					if ($iDetalladoPorDia == 1){
						$total_linea[$xlinea] = 0;
						//$utilidad=0;
					}
				}
				//$dFechaParte = $results[$i]['dt_fechaparte'];
			//}

			if($xlinea != $linea){
				$nomlinea = UtilidadBrutaModel::nombre_linea($linea);				

				$result .= '<tr bgcolor="#A9D0F5">';
				$result .= '<td>LINEA</td>';
				$result .= '<td colspan="' . $colspan_header_combustible . '">'.$linea.' - '.$nomlinea.'</td>';		
				$result .= '</tr>';

				$result .= '<tr bgcolor="#F2F5A9">';

				if ($iDetalladoPorDia == 1){
					$result .= '<td align="center">&nbsp;&nbsp;Fecha&nbsp;&nbsp;</td>';
				}

				$result .= '<td align="center">&nbsp;&nbsp;Codigo&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Articulo&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Precio Vta S/IGV.&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Costo Promedio&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Ganancia&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Margen&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Cant. Venta&nbsp;&nbsp;</td>';
				$result .= '<td align="center">&nbsp;&nbsp;Afericiones&nbsp;&nbsp;</td>';	
				$result .= '<td align="center">&nbsp;&nbsp;Utilidad Bruta&nbsp;&nbsp;</td>';		
				$result .= '</tr>';
			}

			$result .= '<tr>';
			if ($iDetalladoPorDia == 1){
				$result .= '<td>&nbsp;'.$results[$i]['dt_fechaparte'].'&nbsp;</td>';
			}
			$result .= '<td>&nbsp;'.$codigo.'&nbsp;</td>';
			$result .= '<td>&nbsp;'.$articulo.'</td>';

			/* COSTO DE VENTA DE COMBUSTIBLE  */

			$result .= '<td align="right">'.number_format($costovta,4).'</td>';

			/* ULTIMO COSTO DE COMBUSTIBLE    */
			$result .= '<td align="right">'.number_format($ultmcosto,4).'</td>';

			$result .= '<td align="right">'.number_format($ganancia,2).'</td>';
			$result .= '<td align="right">'.number_format($margen,2).' %</td>';
			$result .= '<td align="right">'.number_format($cantidad,2).'</td>';
			$result .= '<td align="right">'.number_format($afericiones,2).'</td>';	
			$result .= '<td align="right">'.number_format($utilidad,2).'</td>';
			$result .= '</tr>';

			$xlinea	= $linea;
		}

		if($limite > 0){
			$totales[$t]['codigo_linea'] = $xlinea;
			$totales[$t]['nombre_linea'] = UtilidadBrutaModel::nombre_linea($xlinea);
			$totales[$t]['total_linea']  = $total_linea[$xlinea];
			$result .= '<tr bgcolor="#BCF5A9">';
			$result .= '<td colspan="' . $colspan_header_combustible . '" align="right">TOTAL LINEA</td>';
			$result .= '<td align="right">'.number_format($total_linea[$xlinea],2).'</td>';			
			$result .= '</tr>';
			if ($iDetalladoPorDia == 1){
				$total_linea[$xlinea] = 0;
			}
		}

		$result .= '</table>';
		$result .= '</div><br><br>';

		// RESUMEN DE UTILIDAD BRUTA POR LINEA
		
		$result .= '<div align="center">';
		$result .= '<table border="0" cellspacing="0">';
		$result .= '<tr><td colspan="3" align="center" style="color: blue">RESUMEN POR LINEAS DE COMBUSTIBLES</td></tr>';
		$result .= '<tr><td colspan="2">&nbsp;</td><td>&nbsp;Utilidad Bruta&nbsp;</td></tr>';


		for($i = 0; $i < count($totales); $i++) {
			$result .= '<tr>';
			$result .= '<td>&nbsp;'.$totales[$i]['codigo_linea'].'&nbsp;</td>';
			$result .= '<td>&nbsp;'.$totales[$i]['nombre_linea'].'</td>';
			$result .= '<td align="right">'.number_format($totales[$i]['total_linea'],2).'</td>';					
			$result .= '</tr>';
			$total_total 	= $total_total + $totales[$i]['total_linea'];
		}
		$result .= '<tr><td>&nbsp;</td><td  style="color: blue">&nbsp;Total General Combustible &nbsp;</td><td align="right" style="color: blue">'.number_format($total_total,2).'</td></tr>';
		$result .= '</table>';
		$result .= '</div>';

		return $result;
    }
}
