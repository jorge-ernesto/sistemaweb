<?php

class ConsolidacionTemplate extends Template {

	function titulo() {
		return '<h2 align="center" style="color:#336699;"><b>Consolidacion de Turno</b></h2>';
	}

	function formSearch($siguiente, $almacenes, $almacen) {

		$almacenes['']	= "Seleccionar Almacen..";

		$form2 = new Form('', "Consolidar", FORM_METHOD_POST, "control.php", '', "control");
		$form2->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.CONSOLIDACION"));
		$form2->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("almacen", "{$siguiente['almacen']}"));
		$form2->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("dia", "{$siguiente['dia']}"));
		$form2->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("turno", "{$siguiente['turno']}"));

		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table border= '0'>"));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align=right>"));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<strong style='font-size:1.2em; color:#126775;'>Seleccionar Almacen: "));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align=left>"));
		$form2->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $almacen, $almacenes, "", "", array('onChange="BuscarDataAlmacen(this.value);"'),""));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align=right>"));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<strong style='font-size:1.2em; color:#126775;'>Dia: "));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align=left>"));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<strong style='font-size:1.2em; color:126775;'> {$siguiente['dia']}"));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align=right>"));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<strong style='font-size:1.2em; color:#126775;'>Turno: "));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align=left>"));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("<strong style='font-size:1.2em; color:126775;'> {$siguiente['turno']}"));

		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));

		if ($siguiente['flag']=="1") { // ultimo turno del dia
			$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext('<button type="submit" name="action" value="Consolidacion" style="color:#126775; font-size:12px;"  onClick="return confirm(\'Debe haber culminado los procesos del dia '.$siguiente['diab'].' (Varillas, Facturas manuales, compras, vales). Desea consolidar?\');"><img src="/sistemaweb/icons/can3.png" align="right">Consolidacion</button>'));
		} else {
			$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext('<button type="submit" name="action" value="Consolidacion" style="color:#126775; font-size:12px;"  onClick="return confirm(\'Esta seguro que desea consolidar el turno '.$siguiente['turno'].' del dia '.$siguiente['diab'].' ? La liquidacion del grifero debe estar culminada\');"><img src="/sistemaweb/icons/can3.png" align="right">Consolidacion</button>'));
		}

		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext('<br><br><br><input name type="button" value = "Ver Consolidaciones" onClick="MostrarTabla(\'tabla1\');" style="color:#126775; font-size:12px;" />'));
		$form2->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.CONSOLIDACION"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table id='tabla1' style='display: none'>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td style='width:50%;text-align:right;'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Almacén:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $almacen, $almacenes, "", "", array(''),""));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td style='width:50%;text-align:right;'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Del:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", date("d/m/Y"), '<td><a href="javascript:show_calendar(\'Agregar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Al:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", date("d/m/Y"), '<td><a href="javascript:show_calendar(\'Agregar.fecha2\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td colspan="3" style="text-align:center;">', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Buscar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));

		return $form2->getForm() . $form->getForm();
	}

	function resultadosBusqueda($resultados) { 
		$result = '';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th bgcolor="126775" style="font-size:0.7em; color:#FFFFFF">D&iacute;a</th>';
		$result .= '<th bgcolor="126775" style="font-size:0.7em; color:#FFFFFF">Turno</th>';
		$result .= '<th bgcolor="126775" style="font-size:0.7em; color:#FFFFFF">Fecha Consolidaci&oacute;n</th>';
		$result .= '<th bgcolor="126775" style="font-size:0.7em; color:#FFFFFF">Usuario</th>';
		$result .= '<th bgcolor="126775" style="font-size:0.7em; color:#FFFFFF">IP</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_par");
			$a = $resultados[$i];

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['fe_sistema']) . '</td>';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['turno']) . '</td>';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['fecha']) . '</td>';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['usuario']) . '</td>';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['ip']) . '</td>';
			$result .= '</tr>';

		}

		return $result;
	}

	function generarWinchaReporte($td,$eess) {
		$result = "";
		$CRLF = "\r\n";

/*
$eess {
 0 => razsocial,
 1 => desces,
 2 => dires,
 3 => razsocial_market
*/
/*
1234567890123456789012345678901234567890
ESTACION DE SERVICIOS ULTRA COMBUSTIBLES
            DEL PERU S.R.L.
 Panamericana Norte Km. 569 El Milagro

  REPORTE DE LIQUIDACION DE TRABAJADOR

 NOMBRE: XXXXXXXXX
 DIA: 17/11/2010 TURNO: 1

LD PR INICIAL        FINAL      VENTA     
01 D2 9999999.99  9999999.99  999999.99
01 84 9999999.99  9999999.99  999999.99
01 90 9999999.99  9999999.99  999999.99
02 D2 9999999.99  9999999.99  999999.99
02 84 9999999.99  9999999.99  999999.99
02 90 9999999.99  9999999.99  999999.99

Venta Contometros             999999.99
Venta Tickets                 999999.99
Diferencia                    999999.99

Notas de Despacho
TRANS      CLIENTE              IMPORTE
1234567890 NOMBRE CLIENTE     999999.99
1234567890 NOMBRE CLIENTE     999999.99
1234567890 NOMBRE CLIENTE     999999.99
1234567890 NOMBRE CLIENTE     999999.99
1234567890 NOMBRE CLIENTE     999999.99
========================================
                              999999.99

Tarjetas de Credito
TRANS      TIPO                 IMPORTE
1234567890 VISA               999999.99
1234567890 VISA               999999.99
1234567890 VISA               999999.99
1234567890 VISA               999999.99
1234567890 VISA               999999.99
========================================
                              999999.99

Depositos
NRO DOC    M T/C      IMPORTE     SOLES
1234567890 1 2.7910 999999.99 999999.99
1234567890 1 2.7910 999999.99 999999.99
1234567890 1 2.7910 999999.99 999999.99
1234567890 1 2.7910 999999.99 999999.99
1234567890 1 2.7910 999999.99 999999.99
========================================
                              999999.99

Venta Contometros             999999.99-
Total Notas de Despacho       999999.99-
Total Tarjetas de Credito     999999.99+
Total Descuentos              999999.99+
Total Devoluciones            999999.99-
Total Afericiones             999999.99-
Total Depositos               999999.99=
========================================
Sobrante/Faltante             999999.99

Fecha de Impresion: 17/11/2010 16:03




------------------    ------------------
 Firma Trabajador     Firma Responsable
*/

		foreach ($td['cuadres'] as $cuadre) {
			if (strlen(trim($cuadre['lados'][0]['lado']))==2)
				$result .= alinea($eess[0],2,40) . $CRLF;
			else
				$result .= alinea($eess[3],2,40) . $CRLF;
			$result .= alinea($eess[1],2,40) . $CRLF;
			$result .= alinea($eess[2],2,40) . $CRLF;
			$result .= $CRLF;
			$result .= alinea("REPORTE DE LIQUIDACION DE TRABAJADOR",2,40) . $CRLF;
			$result .= $CRLF;
			$result .= " NOMBRE: " . $cuadre['nombre'] . $CRLF;
			$result .= " DIA: " . $td['dia'] . " TURNO: " . $td['turno'] . $CRLF;
			$result .= $CRLF;

			if (count($cuadre['lados']) != 0) {
				$result .= "LD PR      INICIAL       FINAL    VENTA" . $CRLF;
				foreach ($cuadre['lados'] as $lado) {
					foreach ($lado['mangueras'] as $nm => $manguera) {
						$result .= alinea($lado['lado'],0,3) . alinea($manguera['codigocombex'],0,3) . alinea(showNumber($manguera['conto_inicial_sol']),1,12) . alinea(showNumber($manguera['conto_final_sol']),1,12) . alinea(showNumber($manguera['conto_venta_sol']),1,9) . $CRLF;
					}
				}
				$result .= $CRLF;
				$result .= "Venta Contometros             " . alinea(showNumber($cuadre['venta_conto']),1,9) . $CRLF;
			}

			$result .= "Venta Tickets                 " . alinea(showNumber($cuadre['venta_ticket']),1,9) . $CRLF;
			$result .= "Diferencia                    " . alinea(showNumber($cuadre['diferencia']),1,9) . $CRLF;
			$result .= $CRLF;

			if (count($cuadre['pos']) != 0) {
				$result .= "POS                               VENTA" . $CRLF;
				foreach ($cuadre['pos'] as $pos)
					$result .= alinea($pos['pos'],1,3) . alinea(showNumber($pos['ticket_venta_sol']),1,36) . $CRLF;
				$result .= $CRLF;
				$result .= "Venta Tienda                  " . alinea(showNumber($cuadre['venta_market']),1,9) . $CRLF;
			}

//AQUI VA ND
			if ($cuadre['nd']['total']!=0) {
				$result .= "Notas de Despacho" . $CRLF;
				$result .= "TRANS      CLIENTE              IMPORTE" . $CRLF;

				foreach ($cuadre['nd'] as $nd) {
					if (!is_array($nd))
						continue;

					$result .= alinea($nd['trans'],0,11) . alinea(substr($nd['nombre'],0,18),0,19) . alinea(showNumber($nd['importe']),1,9) . $CRLF;
				}

				$result .= "========================================" . $CRLF;
				$result .= alinea(showNumber($cuadre['nd']['total']),1,39) . $CRLF;
				$result .= $CRLF;
			}
//AQUI VA TC
			if ($cuadre['tc']['total']!=0) {
				$result .= "Tarjetas de Credito" . $CRLF;
				$result .= "TRANS      TIPO                 IMPORTE" . $CRLF;

				foreach ($cuadre['tc'] as $tc) {
					if (!is_array($tc))
						continue;

					$result .= alinea($tc['trans'],0,11) . alinea(substr($tc['tipo'],0,18),0,19) . alinea(showNumber($tc['importe']),1,9) . $CRLF;
				}

				$result .= "========================================" . $CRLF;
				$result .= alinea(showNumber($cuadre['tc']['total']),1,39) . $CRLF;
				$result .= $CRLF;
			}

//AQUI VA DEVOL
			if ($cuadre['devol']['total']!=0) {
				$result .= "Devoluciones" . $CRLF;
				$result .= "TRANS      F. PAGO               IMPORTE" . $CRLF;

				foreach ($cuadre['devol'] as $devol) {
					if (!is_array($devol))
						continue;

					$result .= alinea($devol['trans'],0,11) . alinea(substr($devol['fpago'],0,18),0,19) . alinea(showNumber($devol['importe']),1,9) . $CRLF;
				}

				$result .= "========================================" . $CRLF;
				$result .= alinea(showNumber($cuadre['devol']['total']),1,39) . $CRLF;
				$result .= $CRLF;
			}

//AQUI VA AFER
			if ($cuadre['afer']['total']!=0) {
				$result .= "Afericiones" . $CRLF;
				$result .= "TRANS      PRODUCTO              IMPORTE" . $CRLF;

				foreach ($cuadre['afer'] as $afer) {
					if (!is_array($afer))
						continue;

					$result .= alinea($afer['trans'],0,11) . alinea(substr($afer['producto'],0,18),0,19) . alinea(showNumber($afer['importe']),1,9) . $CRLF;
				}

				$result .= "========================================" . $CRLF;
				$result .= alinea(showNumber($cuadre['afer']['total']),1,39) . $CRLF;
				$result .= $CRLF;
			}

//AQUI VA DEPO
			$result .= "Depositos" . $CRLF;
			$result .= "NRO DOC    M T/C      IMPORTE     SOLES" . $CRLF;

			foreach ($cuadre['depositos'] as $depo) {
				if (!is_array($depo))
					continue;

				$result .= alinea($depo['correlativo'],0,11) . alinea($depo['moneda'],0,2) . alinea($depo['tc'],0,7) . alinea(showNumber($depo['importe']),1,9) . " " . alinea(showNumber($depo['importe_soles']),1,9) . $CRLF;
			}

			$result .= "========================================" . $CRLF;
			$result .= alinea(showNumber($cuadre['depositos']['total']),1,39) . $CRLF;
			$result .= $CRLF;

//AQUI VA RESUMEN

			$result .= "Venta Contometros             " . alinea(showNumber($cuadre['venta_conto']),1,9) . "-" . $CRLF;
			$result .= "Total Notas de Despacho       " . alinea(showNumber($cuadre['nd']['total']),1,9) . "-" . $CRLF;
			$result .= "Total Tarjetas de Credito     " . alinea(showNumber($cuadre['tc']['total']),1,9) . "+" . $CRLF;
			$result .= "Total Descuentos              " . alinea(showNumber($cuadre['desc']['total']),1,9) . "+" . $CRLF;
			$result .= "Total Devoluciones            " . alinea(showNumber($cuadre['devol']['total']),1,9) . "-" . $CRLF;
			$result .= "Total Afericiones             " . alinea(showNumber($cuadre['afer']['total']),1,9) . "-" . $CRLF;
			$result .= "Total Depositos               " . alinea(showNumber($cuadre['depositos']['total']),1,9) . "=" . $CRLF;
			$result .= "========================================" . $CRLF;
			if (showNumber($cuadre['fs'])>0)
				$word = "Sobrante";
			else
				$word = "Faltante";
			$result .= "$word                      " . alinea(showNumber($cuadre['fs']),1,9) . $CRLF;

			$result .= $CRLF;
			$result .= "Consolidado: " . date("d/m/Y H:i:s") . $CRLF;
			$result .= $CRLF;
			$result .= $CRLF;
			$result .= $CRLF;
			$result .= $CRLF;
			$result .= $CRLF;

			$result .= "------------------    ------------------" . $CRLF;
			$result .= " Firma Trabajador     Firma Responsable" . $CRLF;

			$result .= $CRLF;
			$result .= $CRLF;
		}

		$result .= $CRLF;
		$result .= $CRLF;
		$result .= $CRLF;
		$result .= $CRLF;
		$result .= $CRLF;
		$result .= $CRLF;
		$result .= $CRLF;

		return $result;
	}
}
