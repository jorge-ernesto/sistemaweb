<?php

function showNumber($num) {
	return number_format(round($num,2),2,".","");
}

function espaciosA($q) {
	$ret = "";
	for ($q;$q>0;$q--)
		$ret .= " ";
	return $ret;
}

function alinea($str,$tipo,$ll) {
	if ($tipo==0)
		return ($str . espaciosA(($ll-strlen($str))));
	else if ($tipo==1)
		return (espaciosA(($ll-strlen($str))) . $str);
	return (espaciosA((($ll/2)-(strlen($str)/2))) . $str . espaciosA((($ll/2)-(strlen($str)/2))));
}

function alineaNC($valores,$columnas) {
	$res = "|";
	for ($i=0;$i<count($columnas);$i++) {
		$ancho = $columnas[$i]-1;
		$valor = $valores[$i];
		if (strlen($valor)<$ancho)
			$valor = substr($valor,0,$ancho);
		else if (is_numeric($valor))
			$valor = alinea($valor,2,$ancho);
		else
			$valor = alinea($valor,0,$ancho);
		$res .= $valor . "|";
	}
	return $res;
}

class CuadreVentasTemplate extends Template {
	function titulo() {
		return '<div align="center"><h2><b>Cuadre de Ventas</b></h2></div>';
	}

	function formSearch($datos = []) {

		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.CUADREVENTAS"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Dia:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dia1", (isset($datos['dia1'])?$datos['dia1']:date("d/m/Y")), '<tr><td style="text-align:right;">', '', 10, 10, false));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dia1", (isset($datos['dia1'])?$datos['dia1']:date("d/m/Y")), '<a href="javascript:show_calendar(\'Agregar.dia1\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Turno:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "turno1", (isset($datos['turno1'])?$datos['turno1']:"1"), '</td></tr><tr><td style="text-align:right;">', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Trabajador:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "trabajador", (isset($datos['trabajador'])?$datos['trabajador']:""), '</td></tr><tr><td style="text-align:center;" colspan="2">', '', 10, 10, false));

		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Procesar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Imprimir", '', '', 20));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));

		return $form->getForm();
	}

	function mostrarReporte($reporte) {
		$result = "";

		$result .= "<center><table id=\"tablaPrincipalCuadre\">\n";
		foreach ($reporte as $td) {
			$result .= "<tr><td class=\"celdaDiaTurno\">Dia: {$td['dia']} Turno: {$td['turno']}</td></tr>\n";
			$result .= "<tr><td class=\"celdaDiaTurno\">Abierto: {$td['apertura']} Cerrado: {$td['cierre']}</td></tr>\n";

			foreach ($td['cuadres'] as $cuadre) { //Cuadre por Trabajador
//				$result .= "<tr><td class=\"celdaContenido\">\n";
				$result .= "<tr><td class=\"celdaContenido\"><table class=\"tablaContometros\">\n";
				$result .= "<tr class=\"bg-success\"><td class=\"celdaTrabajador\" colspan=\"14\">Trabajador: {$cuadre['trabajador']} - {$cuadre['nombre']}</td></tr>\n";
				$result .= "</table>\n";
				$result .= "</td></tr>\n";

				if (count($cuadre['lados']) != 0) {
					// Detalle de Contometros para Combustibles
					$result .= "<tr><td class=\"celdaContenido\">\n";
					$result .= CuadreVentasTemplate::reporteContometros($cuadre);
					$result .= "</td></tr>\n";
				}

				if (count($cuadre['pos']) != 0) {
					// Detalle por Punto de Venta para Market
					$result .= "<tr><td class=\"celdaContenido\">\n";
					$result .= CuadreVentasTemplate::reporteMarket($cuadre);
					$result .= "</td></tr>\n";
				}

				$result .= "<tr><td class=\"celdaContenido\"><table class=\"tablaDetalles\">\n";

				$result .= "<tr><td>\n";

//AQUI VA ND
				if ($cuadre['nd']['total']!=0) {
					$result .= "<table class=\"tablaND\">\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaEncabezado\" colspan=\"4\">Detalle de Notas de Despacho</td>\n";
					$result .= "</tr>\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTransCabecera\">Trans</td>\n";
					$result .= "<td class=\"celdaClienteCabecera\">Cliente</td>\n";
					$result .= "<td class=\"celdaNombreCabecera\">Nombre</td>\n";
					$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
					$result .= "</tr>\n";

					foreach ($cuadre['nd'] as $nd) {
						if (!is_array($nd))
							continue;

						$result .= "<tr>\n";
						$result .= "<td class=\"celdaTrans\">{$nd['trans']}</td>\n";
						$result .= "<td class=\"celdaCliente\">{$nd['cliente']}</td>\n";
						$result .= "<td class=\"celdaNombre\">{$nd['nombre']}</td>\n";
						$result .= "<td class=\"celdaImporte\">" . showNumber($nd['importe']) . "</td>\n";
						$result .= "</tr>\n";
					}

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\" colspan=\"3\">Total Notas de Despacho</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['nd']['total']) . "</td>\n";
					$result .= "</tr>\n";

					$result .= "</table><br/>\n";
				}

// AQUI VA NDE
				if ($cuadre['nde']['total']!=0) {
					$result .= "<table class=\"tablaND\">\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaEncabezado\" colspan=\"4\">Detalle de Notas de Despacho en Efectivo</td>\n";
					$result .= "</tr>\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTransCabecera\">Trans</td>\n";
					$result .= "<td class=\"celdaClienteCabecera\">Cliente</td>\n";
					$result .= "<td class=\"celdaNombreCabecera\">Nombre</td>\n";
					$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
					$result .= "</tr>\n";

					foreach ($cuadre['nde'] as $nde) {
						if (!is_array($nde))
							continue;

						$result .= "<tr>\n";
						$result .= "<td class=\"celdaTrans\">{$nde['trans']}</td>\n";
						$result .= "<td class=\"celdaCliente\">{$nde['cliente']}</td>\n";
						$result .= "<td class=\"celdaNombre\">{$nde['nombre']}</td>\n";
						$result .= "<td class=\"celdaImporte\">" . showNumber($nde['importe']) . "</td>\n";
						$result .= "</tr>\n";
					}

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\" colspan=\"3\">Total Notas de Despacho en Efectivo</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['nde']['total']) . "</td>\n";
					$result .= "</tr>\n";

					$result .= "</table><br/>\n";
				}

//AQUI VA TC
				if ($cuadre['tc']['total']!=0) {
					$result .= "<table class=\"tablaTC\">\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaEncabezado\" colspan=\"5\">Detalle de Tarjetas de Cr&eacute;dito</td>\n";
					$result .= "</tr>\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTransCabecera\">Trans</td>\n";
					$result .= "<td class=\"celdaHoraCabecera\">Hora</td>\n";
					$result .= "<td class=\"celdaTipoCabecera\">Tipo</td>\n";
					$result .= "<td class=\"celdaTarjetaCabecera\">Tarjeta</td>\n";
					$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
					$result .= "</tr>\n";

					foreach ($cuadre['tc'] as $tc) {
						if (!is_array($tc))
							continue;

						$result .= "<tr>\n";
						$result .= "<td class=\"celdaTrans\">{$tc['trans']}</td>\n";
						$result .= "<td class=\"celdaHora\">{$tc['hora']}</td>\n";
						$result .= "<td class=\"celdaTipo\">{$tc['tipo']}</td>\n";
						$result .= "<td class=\"celdaTarjeta\">{$tc['tarjeta']}</td>\n";
						$result .= "<td class=\"celdaImporte\">" . showNumber($tc['importe']) . "</td>\n";
						$result .= "</tr>\n";
					}

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\" colspan=\"4\">Total Tarjetas de Cr&eacute;dito</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['tc']['total']) . "</td>\n";
					$result .= "</tr>\n";

					$result .= "</table><br/>\n";
				}
//AQUI VA DESC
				if ($cuadre['desc']['total']!=0) {
					$result .= "<table class=\"tablaDesc\">\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaEncabezado\" colspan=\"4\">Detalle de Descuentos</td>\n";
					$result .= "</tr>\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTransCabecera\">Trans</td>\n";
					$result .= "<td class=\"celdaFPagoCabecera\">Forma de Pago</td>\n";
					$result .= "<td class=\"celdaDescripcionCabecera\">Descripci&oacute;n</td>\n";
					$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
					$result .= "</tr>\n";

					foreach ($cuadre['desc'] as $desc) {
						if (!is_array($desc))
							continue;

						if ($desc['fpago'] == NULL)
							$desc['fpago'] = "&nbsp;";

						$result .= "<tr>\n";
						$result .= "<td class=\"celdaTrans\">{$desc['trans']}</td>\n";
						$result .= "<td class=\"celdaFPago\">{$desc['fpago']}</td>\n";
						$result .= "<td class=\"celdaDescripcion\">{$desc['descripcion']}</td>\n";
						$result .= "<td class=\"celdaImporte\">" . showNumber($desc['importe']) . "</td>\n";
						$result .= "</tr>\n";
					}

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\" colspan=\"3\">Total Descuentos</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['desc']['total']) . "</td>\n";
					$result .= "</tr>\n";

					$result .= "</table><br/>\n";
				}
//AQUI VA DEVOL
				if ($cuadre['devol']['total']!=0) {
					$result .= "<table class=\"tablaDesc\">\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaEncabezado\" colspan=\"3\">Detalle de Devoluciones</td>\n";
					$result .= "</tr>\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTransCabecera\">Trans</td>\n";
					$result .= "<td class=\"celdaFPagoCabecera\">Forma de Pago</td>\n";
					$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
					$result .= "</tr>\n";

					foreach ($cuadre['devol'] as $devol) {
						if (!is_array($devol))
							continue;

						$result .= "<tr>\n";
						$result .= "<td class=\"celdaTrans\">{$devol['trans']}</td>\n";
						$result .= "<td class=\"celdaFPago\">{$devol['fpago']}</td>\n";
						$result .= "<td class=\"celdaImporte\">" . showNumber($devol['importe']) . "</td>\n";
						$result .= "</tr>\n";
					}

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\" colspan=\"2\">Total Devoluciones</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['devol']['total']) . "</td>\n";
					$result .= "</tr>\n";
					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\" colspan=\"2\">Total Devoluciones Efectivo</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['devol']['total_efectivo']) . "</td>\n";
					$result .= "</tr>\n";

					$result .= "</table><br/>\n";
				}
//AQUI VA ANUL
				if ($cuadre['anul']['total']!=0) {
					$result .= "<table class=\"tablaDesc\">\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaEncabezado\" colspan=\"2\">Detalle de Anulaciones</td>\n";
					$result .= "</tr>\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTransCabecera\">Trans</td>\n";
					$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
					$result .= "</tr>\n";

					foreach ($cuadre['anul'] as $anul) {
						if (!is_array($anul))
							continue;

						$result .= "<tr>\n";
						$result .= "<td class=\"celdaTrans\">{$anul['trans']}</td>\n";
						$result .= "<td class=\"celdaImporte\">" . showNumber($anul['importe']) . "</td>\n";
						$result .= "</tr>\n";
					}

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\">Total Anulaciones</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['anul']['total']) . "</td>\n";
					$result .= "</tr>\n";

					$result .= "</table><br/>\n";
				}
//AQUI VA AFER
				if ($cuadre['afer']['total']!=0) {
					$result .= "<table class=\"tablaAfer\">\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaEncabezado\" colspan=\"4\">Detalle de Afericiones</td>\n";
					$result .= "</tr>\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTransCabecera\">Trans</td>\n";
					$result .= "<td class=\"celdaProductoCabecera\">Producto</td>\n";
					$result .= "<td class=\"celdaDetalleCabecera\">Detalle</td>\n";
					$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
					$result .= "</tr>\n";

					foreach ($cuadre['afer'] as $afer) {
						if (!is_array($afer))
							continue;

						$result .= "<tr>\n";
						$result .= "<td class=\"celdaTrans\">{$afer['trans']}</td>\n";
						$result .= "<td class=\"celdaProducto\">{$afer['producto']}</td>\n";
						$result .= "<td class=\"celdaDetalle\">{$afer['detalle']}</td>\n";
						$result .= "<td class=\"celdaImporte\">" . showNumber($afer['importe']) . "</td>\n";
						$result .= "</tr>\n";
					}

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\" colspan=\"3\">Total Afericiones</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['afer']['total']) . "</td>\n";
					$result .= "</tr>\n";

					$result .= "</table><br/>\n";
				}

//AQUI VA TRANSGRAT
				if ($cuadre['tg']['total']!=0) {
					$result .= "<table class=\"tablaTransgrat\">\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaEncabezado\" colspan=\"3\">Detalle de Transferencias Gratuitas</td>\n";
					$result .= "</tr>\n";

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTransCabecera\">Trans</td>\n";
					$result .= "<td class=\"celdaHoraCabecera\">Hora</td>\n";
					$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
					$result .= "</tr>\n";

					foreach ($cuadre['tg'] as $trgrat) {
						if (!is_array($trgrat))
							continue;

						$result .= "<tr>\n";
						$result .= "<td class=\"celdaTrans\">{$trgrat['trans']}</td>\n";
						$result .= "<td class=\"celdaHora\">{$trgrat['hora']}</td>\n";
						$result .= "<td class=\"celdaImporte\">" . showNumber($trgrat['importe']) . "</td>\n";
						$result .= "</tr>\n";
					}

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaTotal\" colspan=\"2\">Total Transferencias Gratuitas</td>\n";
					$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['tg']['total']) . "</td>\n";
					$result .= "</tr>\n";

					$result .= "</table><br/>\n";
				}

				$result .= "</td><td>\n";

//AQUI VA DEPO
				$result .= "<table class=\"tablaDepo\">\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaEncabezado\" colspan=\"6\">Detalle de Dep&oacute;sitos</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaCorrelativoCabecera\">Nro. Doc.</td>\n";
				$result .= "<td class=\"celdaHoraCabecera\">Hora</td>\n";
				$result .= "<td class=\"celdaMonedaCabecera\">Moneda</td>\n";
				$result .= "<td class=\"celdaTCCabecera\">T/C</td>\n";
				$result .= "<td class=\"celdaImporteCabecera\">Importe</td>\n";
				$result .= "<td class=\"celdaImporteSolesCabecera\">Importe Soles</td>\n";
				$result .= "</tr>\n";

				foreach ($cuadre['depositos'] as $depo) {
					if (!is_array($depo))
						continue;

					$result .= "<tr>\n";
					$result .= "<td class=\"celdaCorrelativo\">{$depo['correlativo']}</td>\n";
					$result .= "<td class=\"celdaHora\">{$depo['hora']}</td>\n";
					$result .= "<td class=\"celdaMoneda\">{$depo['moneda']}</td>\n";
					$result .= "<td class=\"celdaTC\">{$depo['tc']}</td>\n";
					$result .= "<td class=\"celdaImporte\">" . showNumber($depo['importe']) . "</td>\n";
					$result .= "<td class=\"celdaImporteSoles\">" . showNumber($depo['importe_soles']) . "</td>\n";
					$result .= "</tr>\n";
				}

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaTotal\" colspan=\"5\">Total Dep&oacute;sitos</td>\n";
				$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['depositos']['total']) . "</td>\n";
				$result .= "</tr>\n";

				$result .= "</table><br/>\n";

//AQUI VA RESUMEN

				$result .= "<table class=\"tablaResumen\">\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaEncabezado\" colspan=\"3\">Resumen</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaConcepto\">Venta Total</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($cuadre['venta_exigible']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">-</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaConcepto\">Total Notas de Despacho</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($cuadre['nd']['total']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">-</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaConcepto\">Total Tarjetas de Cr&eacute;dito</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($cuadre['tc']['total']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">-</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaConcepto\">Total Redondeo Efectivo</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($cuadre['redondeo_efectivo']['total']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">+</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaConcepto\">Total Descuentos</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($cuadre['desc']['total_efectivo']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">+</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaConcepto\">Total Devoluciones</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($cuadre['devol']['total_efectivo']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">-</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaConcepto\">Total Afericiones</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($cuadre['afer']['total']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">-</td>\n";
				$result .= "</tr>\n";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaConcepto\">Total Dep&oacute;sitos</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($cuadre['depositos']['total']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">=</td>\n";
				$result .= "</tr>\n";

				if (showNumber($cuadre['fs'])>0)
					$word = "Sobrante";
				else
					$word = "Faltante";

				$result .= "<tr>\n";
				$result .= "<td class=\"celdaTotal\">Diferencia " . $word . "</td>\n";
				$result .= "<td class=\"celdaImporteTotal\">" . showNumber($cuadre['fs']) . "</td>\n";
				$result .= "<td class=\"celdaOperacion\">&nbsp;</td>\n";
				$result .= "</tr>\n";


				$result .= "</table><br/>\n";

				$result .= "</td></tr></table></td></tr>\n";

				$venta_exigible_acumulada          += $cuadre['venta_exigible'];
				$nd_total_acumulada                += $cuadre['nd']['total'];
				$tc_total_acumulada                += $cuadre['tc']['total'];
				$redondeo_efectivo_total_acumulada += $cuadre['redondeo_efectivo']['total'];
				$desc_total_efectivo_acumulada     += $cuadre['desc']['total_efectivo'];
				$devol_total_efectivo_acumulada    += $cuadre['devol']['total_efectivo'];
				$afer_total_acumulada              += $cuadre['afer']['total'];
				$depositos_total_acumulada         += $cuadre['depositos']['total'];
				$fs_acumulada                      += $cuadre['fs'];
			}
		}
		$result .= "</table></center><br/>\n";
		
		//RESUMEN LINEA POR DIA Y TURNO		
		$result .= "<br>";
		$result .= "<table class=\"tablaResumen\" style=\"width: 30%;\">\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenEncabezado\" colspan=\"3\">Resumen Market: {$td['dia']} Turno: {$td['turno']}</td>\n";
		$result .= "</tr>\n";
		
		$cantidad_acumulada = 0;
		$importe_acumulado = 0;
		if (empty($td['resumen_market_linea'])) {
			$result .= "<tr>\n";
			$result .= "<td class=\"celdaResumenConcepto\"> - </td>\n"; //
			$result .= "<td class=\"celdaResumenImporte\">" . showNumber(0) . "</td>\n";
			$result .= "<td class=\"celdaResumenImporte\">" . showNumber(0) . "</td>\n";
			$result .= "</tr>\n";
		}else{
			foreach ($td['resumen_market_linea'] as $key => $linea) {
				$result .= "<tr>\n";
				$result .= "<td class=\"celdaResumenConcepto\">" . $linea['linea'] . " - " . $linea['descripcion_linea'] . "</td>\n"; //
				$result .= "<td class=\"celdaResumenImporte\">" . showNumber($linea['cantidad']) . "</td>\n";
				$result .= "<td class=\"celdaResumenImporte\">" . showNumber($linea['importe']) . "</td>\n";
				$result .= "</tr>\n";

				$cantidad_acumulada += $linea['cantidad'];
				$importe_acumulado += $linea['importe'];
			}
		}		

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenEncabezado\">TOTAL</td>\n"; //
		$result .= "<td class=\"celdaResumenEncabezado\">" . showNumber($cantidad_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenEncabezado\">" . showNumber($importe_acumulado) . "</td>\n";
		$result .= "</tr>\n";

		$result .= "</table><br/>\n";

		//RESUMEN POR DIA Y TURNO
		$result .= "<br>";
		$result .= "<table class=\"tablaResumen\" style=\"width: 50%;\">\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenEncabezado\" colspan=\"3\">Cierre Caja: {$td['dia']} Turno: {$td['turno']}</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Venta Total</td>\n"; //
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($venta_exigible_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">-</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Total Notas de Despacho</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($nd_total_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">-</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Total Tarjetas de Cr&eacute;dito</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($tc_total_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">-</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Total Redondeo Efectivo</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($redondeo_efectivo_total_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">+</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Total Descuentos</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($desc_total_efectivo_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">+</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Total Devoluciones</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($devol_total_efectivo_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">-</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Total Afericiones</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($afer_total_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">-</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Total Depositos</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($depositos_total_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">=</td>\n";
		$result .= "</tr>\n";

		if (showNumber($fs_acumulada)>0)
			$word = "Sobrante";
		else
			$word = "Faltante";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">Sumatoria Sobrantes y Faltantes</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($fs_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">&nbsp;</td>\n";
		$result .= "</tr>\n";

		$result .= "<tr>\n";
		$result .= "<td class=\"celdaResumenConcepto\">TOTAL EFECTIVO EN BÓVEDA</td>\n";
		$result .= "<td class=\"celdaResumenImporte\">" . showNumber($depositos_total_acumulada - $fs_acumulada) . "</td>\n";
		$result .= "<td class=\"celdaResumenOperacion\">&nbsp;</td>\n";
		$result .= "</tr>\n";
		

		$result .= "</table><br/>\n";

		return $result;
	}

	function reporteContometros($cuadre) {
		$result  = "<table class=\"tablaContometros\">\n";
		$result .= "<tr><td class=\"celdaTrabajador\" colspan=\"14\" style=\"font-weight: bold;\">Detalle de Venta de Combustibles</td></tr>\n";
		$result .= "<tr>\n";
		$result .= "<td class=\"celdaCabecera width6\">Lado</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Manguera</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Producto</td>\n";
		$result .= "<td class=\"celdaCabecera width6\">Cantidad Tickets</td>\n";
		$result .= "<td class=\"celdaCabecera width6\">Importe Tickets</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Precio</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Contometro Cant. Inicial</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Contometro Cant. Final</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Contometro Imp. Inicial</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Contometro Imp. Final</td>\n";
		$result .= "<td class=\"celdaCabecera width7\">Cantidad Contometro</td>\n";
		$result .= "<td class=\"celdaCabecera width7\">Importe Contometro</td>\n";
		$result .= "<td class=\"celdaCabecera width6\">Diferencia X Cantidad</td>\n";
		$result .= "<td class=\"celdaCabecera width6\">Diferencia X Importe</td>\n";
		$result .= "</tr>\n";

		$ventaqticket = 0;
		$ventaqconto = 0;

		foreach ($cuadre['lados'] as $lado) {
			foreach ($lado['mangueras'] as $nm => $manguera) {
				$result .= "<tr>\n";
				$result .= "<td class=\"celdaEtiqueta\">{$lado['lado']}</td>\n";
				$result .= "<td class=\"celdaEtiqueta\">{$nm}</td>\n";
				$result .= "<td class=\"celdaEtiqueta\">{$manguera['producto']}</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['ticket_venta_vol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['ticket_venta_sol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['precio']) . "</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['conto_inicial_vol']) . "</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['conto_final_vol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['conto_inicial_sol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['conto_final_sol']) . "</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['conto_venta_vol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['conto_venta_sol']) . "</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['diferencia_vol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['diferencia_sol']) . "</td>\n";
				$result .= "</tr>\n";
			}
			$result .= "<tr>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td class=\"celdaEtiquetaAcum\">TOTAL</td>\n";
			$result .= "<td class=\"celdaEtiquetaAcum\">LADO</td>\n";
			$result .= "<td class=\"celdaContometroAcum\">" . showNumber($lado['ticket_venta_vol']) . "</td>\n";
			$result .= "<td class=\"celdaImporteAcum\">" . showNumber($lado['ticket_venta_sol']) . "</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td class=\"celdaContometroAcum\">" . showNumber($lado['conto_venta_vol']) . "</td>\n";
			$result .= "<td class=\"celdaImporteAcum\">" . showNumber($lado['conto_venta_sol']) . "</td>\n";
			$result .= "<td class=\"celdaContometroAcum\">" . showNumber($lado['diferencia_vol']) . "</td>\n";
			$result .= "<td class=\"celdaImporteAcum\">" . showNumber($lado['diferencia_sol']) . "</td>\n";
			$result .= "</tr>\n";

			$ventaqticket += $lado['ticket_venta_vol'];
			$ventaqconto += $lado['conto_venta_vol'];
		}
		$result .= "<tr class=\"bg-success\">\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td class=\"celdaEtiquetaAcum\">TOTAL</td>\n";
		$result .= "<td class=\"celdaEtiquetaAcum\">COMB.</td>\n";
		$result .= "<td class=\"celdaContometroAcum\">" . showNumber($ventaqticket) . "</td>\n";
		$result .= "<td class=\"celdaImporteAcum\">" . showNumber($cuadre['venta_ticket']) . "</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td class=\"celdaContometroAcum\">" . showNumber($ventaqconto) . "</td>\n";
		$result .= "<td class=\"celdaImporteAcum\">" . showNumber($cuadre['venta_conto']) . "</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td class=\"celdaImporteAcum\">" . showNumber($cuadre['diferencia']) . "</td>\n";
		$result .= "</tr>\n";

		$result .= "</table>\n";

		return $result;
	}

	function reporteMarket($cuadre) {
		$result  = "<table class=\"tablaContometros\">\n";
		$result .= "<tr><td class=\"celdaTrabajador\" colspan=\"14\" style=\"font-weight: bold;\">Detalle de Venta de Tienda</td></tr>\n";
		$result .= "<tr>\n";
		$result .= "<td class=\"celdaCabecera width10\">Punto de Venta</td>\n";
		$result .= "<td class=\"celdaCabecera width70\">&nbsp;</td>\n";
		$result .= "<td class=\"celdaCabecera width10\">Art&iacute;culos</td>\n";
		$result .= "<td class=\"celdaCabecera width10\">Importe Tickets</td>\n";
		$result .= "</tr>\n";
		foreach ($cuadre['pos'] as $pos) {
			$result .= "<tr>\n";
			$result .= "<td class=\"celdaEtiqueta\">{$pos['pos']}</td>\n";
			$result .= "<td class=\"celdaEtiqueta\">&nbsp;</td>\n";
			$result .= "<td class=\"celdaContometro\">" . showNumber($pos['ticket_venta_vol']) . "</td>\n";
			$result .= "<td class=\"celdaImporte\">" . showNumber($pos['ticket_venta_sol']) . "</td>\n";
			$result .= "</tr>\n";
		}
		$result .= "<tr>\n";
		$result .= "<td class=\"celdaEtiquetaAcum\">&nbsp;</td>\n";
		$result .= "<td class=\"celdaEtiquetaAcum\">TOTAL TIENDA</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td class=\"celdaImporteAcum bg-success\">" . showNumber($cuadre['venta_market']) . "</td>\n";
		$result .= "</tr>\n";

		$result .= "</table>\n";

		return $result;
	}

}
