<?php

class RegistroVentasTemplate extends Template {

	function search_form() {

		$fecha = date(d . "/" . m . "/" . Y);
		$almacenes = RegistroVentasModel::obtieneListaEstaciones();

		$form = new form2("Registro de Ventas e Ingresos", "form_registro_ventas", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.REGISTROVENTAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0"><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "Almac&eacute;n:", "", $almacenes, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("anio", "Periodo:", date(Y), '', 04, 04));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes", "Mes:", date(m), '&nbsp&nbsp', 02, 02));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", date(d), '&nbsp&nbsp', 02, 02));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", date(d), '&nbsp&nbsp', 02, 02));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">Tipo Vista: <select name="tipo" id="tipo" class="form_combo">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="SU">Sunat</option>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="N">Detallado</option>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</select>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">Version PLE: <select name="tipo_ple" id="tipo_ple" class="form_combo">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="3">PLE 5 </option>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="4">PLE 5 Simplificado</option>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</select>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center">Considerar Notas Despacho: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('nd', '', 'S', ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center">Considerar GNV: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('gnv', '', 'S', ''));

		/* REEMPLAZAR SERIES */
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("serie", "Series:", "", '&nbsp&nbsp', 20, 20));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("nserie", "Reemplazar Series:", "", '&nbsp&nbsp', 20, 20));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="6"><div id="space" align="center" />&nbsp;</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp<button name="action" type="submit" value="Reporte"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp<button name="action" type="submit" value="Libros-Electronico"><img src="/sistemaweb/icons/gbook.png" align="right" />Libros Electronicos</button>'));


/*		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "PDF"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Libros-Electronico"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Excel"));*/

/*		$form->addElement(FORM_GROUP_MAIN, new f2element_text("bi", "B.I(+-):", 0.00, '&nbsp&nbsp', 15, 15));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("igv", "IGV(+-):", 0.00, '&nbsp&nbsp', 15, 15));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("valor_venta", "V.V(+-):", 0.00, '&nbsp&nbsp', 15, 15));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("correlativo", "Inicio Correlativo:", 0, '&nbsp&nbsp', 15, 15));
*/

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

        	return $form->getForm();

	}

	function reporte($results, $resultsgnv, $BI_incre, $IGV_incre, $TOTAL_incre, $arrParamsPOST) {
		$result = '<table align="center" border="0">';
		$result .= '<tr><td colspan="15">---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		$result .= '<tr>';
		$result .= '<th align="center" rowspan="2">NUMERO DE<br>REGISTRO</th>';
		$result .= '<th align="center" rowspan="2">FECHA DE<br>EMISION</th>';
		$result .= '<th align="center" rowspan="2">FECHA DE<br>VENCIMIENTO</th>';
		$result .= '<th align="center" colspan="3">COMPROBANTE DE PAGO</th>';
		$result .= '<th align="center" colspan="3">INFORMACION DEL CLIENTE</th>';
		$result .= '<th align="center" rowspan="2">BASE<br>IMPONIBLE</th>';
		$result .= '<th align="center" rowspan="2">&nbsp&nbspIGV&nbsp&nbsp</th>'; //IGV
		$result .= '<th align="center" rowspan="2">&nbsp&nbspICBPER&nbsp&nbsp</th>'; //JEL
		$result .= '<th align="center" rowspan="2">&nbspEXONERADA&nbsp</th>';
		$result .= '<th align="center" rowspan="2">&nbspINAFECTO&nbsp</th>';
		$result .= '<th align="center" rowspan="2">IMPORTE<br>TOTAL</th>';
		$result .= '<th align="center" rowspan="2">TIPO DE<br>CAMBIO</th>';
		$result .= '<th align="center" rowspan="2">DATOS<br>REFERENCIA</th>';
		$result .= '</tr>';
		$result .= '<tr><td align="center">TIPO</td><td align="center">NRO. SERIE</td><td align="center">NUMERO</td>';
		$result .= '<td align="center">TIPO DI</td><td align="center">NUMERO</td><td align="center">RAZON SOCIAL</td></tr>';
		$result .= '<tr><td colspan="15">---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td></tr>';

		$ntickets		= count($results['ticket']);

		$base_imponible_tickes	= 0;
		$igv_total_ticket	= 0;
		$exonerada_total_ticket	= 0;
		$inafecto_total_ticket= 0;
		$totalimporte_ticket= 0;

		$validar = null;

        $modelRegistroVentas = new RegistroVentasModel();
    	for ($i = 0; $i < $ntickets - 5; $i++) {
	    	$result .= RegistroVentasTemplate::imprimirLinea($results['ticket'][$i], $BI_incre, $IGV_incre, $TOTAL_incre, $i, 'trans', $modelRegistroVentas, $arrParamsPOST);
    	}

        if ($ntickets > 0) {
	    	$base_imponible_ticket	= $results['ticket']["total_imponible"];
			$igv_total_ticket	= $results['ticket']["total_igv"];
			$balance_total_ticket	= $results['ticket']["total_balance"]; //JEL
	    	$exonerada_total_ticket	= $results['ticket']["total_exonerada"];
	    	$inafecto_total_ticket	= $results['ticket']["total_inafecto"];
	    	$totalimporte_ticket	= $results['ticket']["total_importe"];

	    	$result .= '<tr>';
	    	$result .= '<td align="right" colspan="9" style="font-weight:bold; color:blue">TOTAL PLAYA: </td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . number_format($base_imponible_ticket, 2, '.', ',') . '</td>';
			$result .= '<td align="right" style="font-weight:bold; color:blue">' . number_format($igv_total_ticket, 2, '.', ',') . '</td>';
			$result .= '<td align="right" style="font-weight:bold; color:blue">' . number_format($balance_total_ticket, 2, '.', ',') . '</td>'; //JEL
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . number_format($exonerada_total_ticket, 2, '.', ',') . '</td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . number_format($inafecto_total_ticket, 2, '.', ',') . '</td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . number_format($totalimporte_ticket, 2, '.', ',') . '</td>';
	    	$result .= '</tr>';
        }

    	$nmanuales = count($results['manual']);

    	//for ($i = 0; $i < $nmanuales - 5; $i++) { cai
    	for ($i = 0; $i < $nmanuales - 5; $i++) {
        	$result .= RegistroVentasTemplate::imprimirLinea($results['manual'][$i], "", "", "", "", "", "", "");
    	}

		$totalimporte_manual	= 0;
		$igv_total_manual	= 0;
		$igv_total_manual	= 0;
		$igv_total_manual	= 0;
		$base_imponible_manual	= 0;

    	if ($nmanuales > 0) {
	    	$base_imponible_manual = $results['manual']['total_imponible'] - abs($results['totales_imponible_credito']);
			$igv_total_manual = $results['manual']['total_igv'] - abs($results['totales_igv_credito']);
			$balance_total_manual = "0.00"; //JEL
	    	$exonerada_total_manual	= $results['manual']['total_exonerada'] - abs($results['totales_exonerada_nc']) - abs($results['totales_inafecto_nc']);
	    	$inafecto_total_manual = $results['manual']['total_inafecto'];
	    	$totalimporte_manual = $results['manual']['total_importe'] - abs($results['totales_importe_credito']);

	    	$result .= '<tr>';
	    	$result .= '<td align="right" colspan="9" style="font-weight:bold; color:blue">TOTAL DOCUMENTOS MANUALES: </td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($base_imponible_manual), 2, '.', ',')) . '</td>';
			$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($igv_total_manual), 2, '.', ',')) . '</td>';
			$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($balance_total_manual), 2, '.', ',')) . '</td>'; //JEL
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($exonerada_total_manual), 2, '.', ',')) . '</td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($inafecto_total_manual), 2, '.', ',')) . '</td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($totalimporte_manual), 2, '.', ',')) . '</td>';
	    	$result .= '</tr>';
		}

    	$ngnv = count($resultsgnv['gnv']);

    	for ($i = 0; $i < $ngnv - 3; $i++) {
        	$result .= RegistroVentasTemplate::imprimirLinea($resultsgnv['gnv'][$i], "", "", "", "", "", "", "");
    	}

    	if ($ngnv > 0) {
	    	$totalimporte_gnv	= $resultsgnv['gnv']['total_importe'];
			$igv_total_gnv		= (($totalimporte_gnv * 0.18) / 1.18);
			$balance_total_gnv = "0.00"; //JEL
	    	$base_imponible_gnv	= $totalimporte_gnv - $igv_total_gnv;

	    	$result .= '<tr>';
	    	$result .= '<td align="right" colspan="9" style="font-weight:bold; color:blue">TOTAL TICKETS GNV: </td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($base_imponible_gnv), 2, '.', ',')) . '</td>';
			$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($igv_total_gnv), 2, '.', ',')) . '</td>';
			$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($balance_total_gnv), 2, '.', ',')) . '</td>'; //JEL
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">0.00</td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">0.00</td>';
	    	$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($totalimporte_gnv), 2, '.', ',')) . '</td>';
	   		$result .= '</tr>';
    	}

		$result .= '<tr><td colspan="15">---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		$result .= '<tr>';
		$result .= '<td align="right" colspan="9" style="font-weight:bold; color:blue">TOTAL GENERAL: </td>';

		$base_imponible 	= ($base_imponible_ticket + $base_imponible_manual + $BI_incre+$base_imponible_gnv);
		$igv_total 			= ($igv_total_ticket + $igv_total_manual + $IGV_incre + $igv_total_gnv);
		$balance_total 		= ($balance_total_ticket + $balance_total_manual + $balance_total_gnv);
		$exonerada_total 	= ($exonerada_total_ticket + $exonerada_total_manual);
		$inafecto_total 	= ($inafecto_total_ticket + $inafecto_total_manual);
		$totalimporte 		= ($totalimporte_ticket + $totalimporte_manual + $TOTAL_incre + $totalimporte_gnv);

		$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($base_imponible), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($igv_total), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($balance_total), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($exonerada_total), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($inafecto_total), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format(($totalimporte), 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		$result .= '<tr><td colspan="15">---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		$result .= '</table>';

        return $result;
    }

    function imprimirLinea($linea, $BI_incre, $IGV_incre, $TOTAL_incre, $validar, $tipo_data, $modelRegistroVentas, $arrParamsPOST) {
    	if( $linea['rendi_gln'] != "" ) {
	        $arrData = array(
	            //Datos para buscar registros
	            "sNombreTabla" => $arrParamsPOST['sTablePostransYM'],
	            "sCodigoAlmacen" => $arrParamsPOST['sCodigoAlmacen'],
	            //Datos para buscar documento origen
	            "sCaja" => $linea['caja'],
	            "sTipoDocumento" => $linea['td'],
	            "fIDTrans" => $linea['rendi_gln'],
	            "iNumeroDocumentoIdentidad" => $linea['ruc_bd_interno'],
	        );
			$arrResponseModel = $modelRegistroVentas->verify_reference_sales_invoice_document($arrData);
			// error_log("****** Data Documento Referencia ******");
			// error_log( json_encode( $arrData ) );
			// error_log( json_encode( $arrResponseModel ) );
	        $sSerieNumeroReferencia = "";
	        if ($arrResponseModel["sStatus"] == "success") {
	            $sSerieNumeroReferencia = $arrResponseModel["arrDataModel"]["usr"];
	        }
	    }

	    if( $linea['reffec'] != "" && $linea['reftip'] != "" && $linea['refser'] != "" && $linea['refnum'] != "" ){
			$sSerieNumeroReferencia = $linea['refser']  . '-' . $linea['refnum'];
	    }
            
		$result = '<tr>';
			$result .= '<td align="center">' . htmlentities($linea['trans']) . '</td>';
			$result .= '<td align="center">' . htmlentities($linea['emision']) . '</td>';
			$result .= '<td align="center">' . htmlentities($linea['vencimiento']) . '</td>';
			$result .= '<td align="center">' . htmlentities($linea['tipo']) . '</td>';
			$result .= '<td align="center">' . htmlentities($linea['serie']) . '</td>';
			$result .= '<td align="center">' . htmlentities($linea['numero']) . '</td>';
			$result .= '<td align="center">' . htmlentities($linea['tipodi']) . '</td>';
			$result .= '<td align="center">' . htmlentities($linea['ruc']) . '</td>';
			$result .= '<td align="left">' . htmlentities($linea['cliente']) . '</td>';

			if($validar == 0 && (abs($BI_incre) > 0 || abs($IGV_incre) > 0 || abs($TOTAL_incre) > 0)){
				$newbi 		= $linea['imponible'] + $BI_incre;
				$newigv 	= $linea['igv'] + $IGV_incre;
				$newtotal 	= $linea['importe'] + $TOTAL_incre;
				
				$result .= '<td align="right">' . $newbi . '</td>';
				$result .= '<td align="right">' . $newigv . '</td>';
				$result .= '<td align="right">' . $linea['exonerada'] . '</td>';
				$result .= '<td align="right">' . $linea['inafecto'] . '</td>';
				$result .= '<td align="right">' . $newtotal . '</td>';
				
			}else{
				$result .= '<td align="right">' . $linea['imponible']  . '</td>';
				$result .= '<td align="right">' . $linea['igv'] . '</td>';
				$result .= '<td align="right">' . $linea['balance'] . '</td>'; //JEL //ICBPER

				if($linea['istranfer'] == 'S')
					$result .= '<td align="right">' . $linea['exonerada'] . '</td>';
				else
					$result .= '<td align="right">0.00</td>';
				$result .= '<td align="right">' . $linea['inafecto'] . '</td>';
				$result .= '<td align="right">' . $linea['importe'] . '</td>';
			}
			
			$result .= '<td align="right">' . htmlentities($linea['tipocambio']) . '</td>';
			$result .= '<td align="right">' . htmlentities($sSerieNumeroReferencia) . '</td>';
		$result .= '</tr>';
		return $result;
	}

    function reportePDF($results, $almacen, $anio, $mes, $tipo_reporte, $BI_incre, $IGV_incre, $TOTAL_incre, $modelRegistroVentas, $arrParamsPOST) {
		$estaciones	= RegistroVentasModel::obtieneListaEstaciones();
		$v		= RegistroVentasModel::obtenerAlma($almacen);
		$razsoc 	= $v[0];
		$ruc 		= $v[1];

		$cab_general = array(
		    "trans" => "Num. Registro",
		    "emision" => "Fec. Emision",
		    "vencimiento" => "Fec.Vencimiento",
		    "tipo" => "TD",
		    "serie" => "Serie",
		    "numero" => "Numero",
		    "tipodi" => "Tipo",
		    "ruc" => "Cod. Cliente",
		    "cliente" => "Raz. Social",
		    "vfexp" => "V.Exp.",
		    "imponible" => "Imponible",
		    "exonerada" => "Exon.",
		    "inafecto" => "Inaf.",
		    "isc" => "ISC",
			"igv" => "IGV", //JEL
			"balance" => "ICBPER",
		    "otros" => "Ot.Trib",
		    "importe" => "Importe",
		    "tipocambio" => "TC",
		    "reffec" => "Ref. F",
		    "reftip" => "Ref. T",
		    "refser" => "Ref. S",
		    "refnum" => "Ref. N"
		);

		$cab_resumen = array(
		    "rotulo" => '',
		    "neto" => "Base Imponible",
		    "impuestos" => "Impuesto"
		);

		$reporte = new CReportes2("L", "pt", "A3");
		$reporte->definirCabecera(1, "C", "Registro de Ventas e Ingresos");
		$reporte->definirCabecera(1, "L", "Centro de Costo: " . $estaciones[$almacen]);

		$reporte->definirCabecera(2, "L", "Periodo: " . $anio . " - " . $mes);
		$reporte->definirCabecera(3, "L", "Ruc: " . $ruc);
		$reporte->definirCabecera(4, "L", "Razon Social: " . $razsoc);
		$reporte->definirCabecera(5, "C", "Pag %p");

		$reporte->definirColumna("trans", $reporte->TIPO_TEXTO, 13, "L", "_tickets");
		$reporte->definirColumna("emision", $reporte->TIPO_TEXTO, 10, "C", "_tickets");
		$reporte->definirColumna("vencimiento", $reporte->TIPO_TEXTO, 10, "C", "_tickets");
		$reporte->definirColumna("tipo", $reporte->TIPO_TEXTO, 2, "C", "_tickets");
		$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 10, "C", "_tickets");
		$reporte->definirColumna("numero", $reporte->TIPO_TEXTO, 22, "C", "_tickets");
		$reporte->definirColumna("tipodi", $reporte->TIPO_TEXTO, 2, "C", "_tickets");
		$reporte->definirColumna("ruc", $reporte->TIPO_TEXTO, 11, "L", "_tickets");
		$reporte->definirColumna("cliente", $reporte->TIPO_TEXTO, 40, "L", "_tickets");
		$reporte->definirColumna("vfexp", $reporte->TIPO_IMPORTE, 6, "C", "_tickets");
		$reporte->definirColumna("imponible", $reporte->TIPO_IMPORTE, 13, "R", "_tickets");
		$reporte->definirColumna("exonerada", $reporte->TIPO_IMPORTE, 5, "R", "_tickets");
		$reporte->definirColumna("inafecto", $reporte->TIPO_IMPORTE, 5, "R", "_tickets");
		$reporte->definirColumna("isc", $reporte->TIPO_IMPORTE, 5, "R", "_tickets");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 11, "R", "_tickets"); //JEL
		$reporte->definirColumna("balance", $reporte->TIPO_IMPORTE, 11, "R", "_tickets");
		$reporte->definirColumna("otros", $reporte->TIPO_IMPORTE, 7, "R", "_tickets");
		$reporte->definirColumna("importe", $reporte->TIPO_IMPORTE, 13, "R", "_tickets");
		$reporte->definirColumna("tipocambio", $reporte->TIPO_TEXTO, 5, "R", "_tickets");
		$reporte->definirColumna("reffec", $reporte->TIPO_TEXTO, 12, "C", "_tickets");
		$reporte->definirColumna("reftip", $reporte->TIPO_TEXTO, 6, "C", "_tickets");
		$reporte->definirColumna("refser", $reporte->TIPO_TEXTO, 6, "C", "_tickets");
		$reporte->definirColumna("refnum", $reporte->TIPO_TEXTO, 10, "C", "_tickets");

		$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 138, "C", "_rot");
		$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 13, "R", "_rot");
		$reporte->definirColumna("exonerada", $reporte->TIPO_IMPORTE, 13, "R", "_rot");
		$reporte->definirColumna("inafecto", $reporte->TIPO_IMPORTE, 13, "R", "_rot");
		$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 15, "R", "_rot");
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 21, "R", "_rot");

		$reporte->definirColumna("motivo", $reporte->TIPO_TEXTO, 138, "R", "_sub");
		$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 13, "R", "_sub");
		$reporte->definirColumna("exonerada", $reporte->TIPO_IMPORTE, 13, "R", "_sub");
		$reporte->definirColumna("inafecto", $reporte->TIPO_IMPORTE, 13, "R", "_sub");
		$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 15, "R", "_sub");
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 21, "R", "_sub");
		$reporte->definirColumna("tcambio", $reporte->TIPO_TEXTO, 5, "R", "_sub");

		$reporte->definirColumna("nombre", $reporte->TIPO_TEXTO, 30, "L", "_res");
		$reporte->definirColumna("imponible", $reporte->TIPO_IMPORTE, 13, "R", "_res");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 13, "R", "_res");
		$reporte->definirColumna("balance", $reporte->TIPO_IMPORTE, 13, "R", "_res"); //JEL
		$reporte->definirColumna("exonerada", $reporte->TIPO_IMPORTE, 13, "R", "_res");
		$reporte->definirColumna("inafecto", $reporte->TIPO_IMPORTE, 13, "R", "_res");
		$reporte->definirColumna("importe", $reporte->TIPO_IMPORTE, 13, "R", "_res");

		$reporte->definirColumna("libre", $reporte->TIPO_TEXTO, 30, "L", "_sr");
		$reporte->definirColumna("imp", $reporte->TIPO_TEXTO, 13, "R", "_sr");
		$reporte->definirColumna("igv", $reporte->TIPO_TEXTO, 13, "R", "_sr");
		$reporte->definirColumna("balance", $reporte->TIPO_TEXTO, 13, "R", "_sr"); //JEL
		$reporte->definirColumna("exo", $reporte->TIPO_TEXTO, 13, "R", "_sr");
		$reporte->definirColumna("inf", $reporte->TIPO_TEXTO, 13, "R", "_sr");
		$reporte->definirColumna("tot", $reporte->TIPO_TEXTO, 13, "R", "_sr");

		$reporte->definirColumna("nombre", $reporte->TIPO_TEXTO, 30, "L", "_restitu");

		$reporte->SetMargins(10, 10, 10);
		$reporte->SetFont("courier", "", 8);

		// TICKET BOLETA
		// $reporte->definirCabecera(5, "R", "Tickets Boleta");
		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab_general, "_tickets");
		$reporte->AddPage();

		$x = 1;
		//$tickboleta = count($results['ticket']);
		$tickes = count($results['ticket']); //incluyen tickes facturas y boletas.
		//$tickfactura = $results['fin_tickes_facturas'];

		$impreso = TRUE;

		for ($h = 0; $h < $tickes - 5; $h++) {//SIGNIFICA QUE VA DESCONTAR LOS SUBTOTALES

			/*if ($results['ticket'][$h]['tipo_pdf'] == 'B') {

				if($h == 0 && (abs($BI_incre) > 0 || abs($IGV_incre) > 0 || abs($TOTAL_incre) > 0)){

					$newbi 		= $results['ticket'][$h]['imponible'] + $BI_incre;
					$newigv 	= $results['ticket'][$h]['igv'] + $IGV_incre;
					$newtotal 	= $results['ticket'][$h]['importe'] + $TOTAL_incre;

					$total_ticket_boleta_imp	+= round($newbi, 2);
					$total_ticket_boleta_igv	+= round($newigv, 2);
					$total_ticket_boleta_exonerada	+= round($results['ticket'][$h]['exonerada'], 2);
					$total_ticket_boleta_inafecto	+= round($results['ticket'][$h]['inafecto'], 2);
					$total_ticket_boleta_tot	+= round($newtotal, 2);
				}else{
					$total_ticket_boleta_imp	+= round($results['ticket'][$h]['imponible'], 2);
					$total_ticket_boleta_igv	+= round($results['ticket'][$h]['igv'], 2);
					$total_ticket_boleta_exonerada	+= round($results['ticket'][$h]['exonerada'], 2);
					$total_ticket_boleta_inafecto	+= round($results['ticket'][$h]['inafecto'], 2);
					$total_ticket_boleta_tot	+= round($results['ticket'][$h]['importe'], 2);
				//}

		    	} else {*/

				/*if ($tipo_reporte == "S" && $impreso == TRUE) {

				    $reporte->lineaH();
				    $arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto1, "exonerada" => $exonerada1, "inafecto" => $Inafecto1, "impuestos" => $Impuestos1, "total" => $TotalVentas1, "tcambio" => " ");
				    $reporte->nuevaFila($arr, "_sub");
				    $x = 1;
				    $ValorNeto1 = 0;
				    $Impuestos1 = 0;
				    $exonerada1 = 0;
				    $inafecto1 = 0;
				    $TotalVentas1 = 0;

				    $reporte->Ln();
				    $reporte->lineaH();
				    $arr = array("rotulo" => "Total Tickets Boleta", "neto" => ($total_ticket_boleta_imp), "exonerada" => ($total_ticket_boleta_exonerada), "inafecto" => ($total_ticket_boleta_inafecto), "impuestos" => ($total_ticket_boleta_igv), "total" => ($total_ticket_boleta_tot));
				    $reporte->nuevaFila($arr, "_rot");

				    // TICKET FACTURA
				    //  $reporte->definirCabecera(5, "R", "Tickets Factura");
				    $reporte->borrarCabeceraPredeterminada();
				    $reporte->definirCabeceraPredeterminada($cab_general, "_tickets");
				    $reporte->AddPage();
				    $impreso = FALSE;

				}

				$total_ticket_factura_imp	+= round($results['ticket'][$h]['imponible'], 2);
				$total_ticket_factura_igv 	+= round($results['ticket'][$h]['igv'], 2);
				$total_ticket_factura_exonerada	+= round($results['ticket'][$h]['exonerada'], 2);
				$total_ticket_factura_inafecto 	+= round($results['ticket'][$h]['inafecto'], 2);
				$total_ticket_factura_tot 	+= round($results['ticket'][$h]['importe'], 2);*/

		    	//}

		   	/*if ($x <= 90) {

				/*if($h == 0 && (abs($BI_incre) > 0 || abs($IGV_incre) > 0 || abs($TOTAL_incre) > 0)){

					$newbi 		= $results['ticket'][$h]['imponible'] + $BI_incre;
					$newigv 	= $results['ticket'][$h]['igv'] + $IGV_incre;
					$newtotal 	= $results['ticket'][$h]['importe'] + $TOTAL_incre;

					$ValorNeto1	+= $newbi;
					$Impuestos1	+= $newigv;
					$exonerada1	+= $results['ticket'][$h]['exonerada'];
					$Inafecto1	+= $results['ticket'][$h]['inafecto'];
					$TotalVentas1	+= $newtotal;
		
				}else{
					$ValorNeto1	+= $results['ticket'][$h]['imponible'];
					$Impuestos1	+= $results['ticket'][$h]['igv'];
					$exonerada1	+= $results['ticket'][$h]['exonerada'];
					$Inafecto1	+= $results['ticket'][$h]['inafecto'];
					$TotalVentas1	+= $results['ticket'][$h]['importe'];
				}

			}*/

		    	/*if ($x == 90) {

				$reporte->lineaH();
				$arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto1, "exonerada" => $exonerada1, "inafecto" => $Inafecto1, "impuestos" => $Impuestos1, "total" => $TotalVentas1, "tcambio" => " ");
				$reporte->nuevaFila($arr, "_sub");
				$x = 1;
				$ValorNeto1 = 0;
				$Impuestos1 = 0;
				$exonerada1 = 0;
				$Inafecto1 = 0;
				$TotalVentas1 = 0;
		    	}*/

			//AQUI MUESTRA LOS VALORES EN EL PDF REGISTRO POR REGISTRO

			/*if($h == 0 && (abs($BI_incre) > 0 || abs($IGV_incre) > 0 || abs($TOTAL_incre) > 0)){
				$results['ticket'][$h]['imponible'] 	= $results['ticket'][$h]['imponible'] + $BI_incre;
				$results['ticket'][$h]['igv'] 		= $results['ticket'][$h]['igv'] + $IGV_incre;
				$results['ticket'][$h]['importe'] 	= $results['ticket'][$h]['importe'] + $TOTAL_incre;
			}*/
			/*
			echo "<pre>";
			var_dump($results['ticket'][$h]);
			echo "<pre>";
			*/

			$results['ticket'][$h]['reffec'] = "";
			$results['ticket'][$h]['reftip'] = "";
			$results['ticket'][$h]['refser'] = "";
			$results['ticket'][$h]['refnum'] = "";
			if( $results['ticket'][$h]['rendi_gln'] != "" ) {
		        $arrData = array(
		            //Datos para buscar registros
		            "sNombreTabla" => $arrParamsPOST['sTablePostransYM'],
		            "sCodigoAlmacen" => $arrParamsPOST['sCodigoAlmacen'],
		            //Datos para buscar documento origen
		            "sCaja" => $results['ticket'][$h]['caja'],
		            "sTipoDocumento" => $results['ticket'][$h]['td'],
		            "fIDTrans" => $results['ticket'][$h]['rendi_gln'],
		            "iNumeroDocumentoIdentidad" => $results['ticket'][$h]['ruc_bd_interno'],
		        );
				$arrResponseModel = $modelRegistroVentas->verify_reference_sales_invoice_document($arrData);
				// error_log("****** Data Documento Referencia ******");
				// error_log( json_encode( $arrData ) );
				// error_log( json_encode( $arrResponseModel ) );
		        if ($arrResponseModel["sStatus"] == "success") {
					$results['ticket'][$h]['reffec'] = $arrResponseModel["arrDataModel"]["fecharef"];
					$results['ticket'][$h]['reftip'] = $arrResponseModel["arrDataModel"]["tiporef"];
					$results['ticket'][$h]['refser'] = $arrResponseModel["arrDataModel"]["serieref"];
					$results['ticket'][$h]['refnum'] = $arrResponseModel["arrDataModel"]["numref"];
		        }
		    }

			$reporte->nuevaFila($results['ticket'][$h], "_tickets");

			//$x++;

			//FIN

		}//FIN DE LA INTERACION DE LOS TICKES

		//SUMA DE LOS TICKETS BOLETA Y FACTURAS

	    	$ValorNeto1	= $results['ticket']["total_imponible"]; //$results['ticket']['total_imponible'];
			$Impuestos1	= $results['ticket']["total_igv"]; //$results['ticket']['total_igv'];
			$Balance1	= $results['ticket']["total_balance"]; //$results['ticket']['total_balance']; //JEL
	    	$exonerada1	= $results['ticket']["total_exonerada"]; //$results['ticket']['total_igv'];
	    	$Inafecto1	= $results['ticket']["total_inafecto"]; //$results['ticket']['total_igv'];
	    	$TotalVentas1	= $results['ticket']["total_importe"]; //$results['ticket']['total_importe'];

		if ($tipo_reporte == "SU" || $tipo_reporte == "N") {
	    	$reporte->Ln();
	    	$reporte->lineaH();
	    	$arr = array("rotulo" => "Total Tickets ", "neto" => ($ValorNeto1), "exonerada" => ($Impuestos1), "inafecto" => ($exonerada1), "impuestos" => ($Inafecto1), "total" => ($TotalVentas1));
	    	$reporte->nuevaFila($arr, "_rot");
	    	// $reporte->AddPage();
		}

		$cantitotal = count($results['manual']); // manuales

		if ($cantitotal > 0) {
		    //BOLETAS MANUALES
			$reporte->definirCabecera(5, "R", "Documentos Manuales Boleta");
	    	$reporte->borrarCabeceraPredeterminada();
	    	$reporte->definirCabeceraPredeterminada($cab_general, "_tickets");
			$reporte->AddPage();

		    	$z = 1;
		    	$nmanuales = count($results['manual']);

		    	for ($j = 0; $j < $nmanuales - 5; $j++) {

		        	if ($results['manual'][$j]['tipo'] == '03') {

				    	$total_manual_boleta_imp 	+= round($results['manual'][$j]['imponible'], 2);
				    	$total_manual_boleta_igv 	+= round($results['manual'][$j]['igv'], 2);
				    	$total_manual_boleta_exonerada 	+= round($results['manual'][$j]['exonerada'], 2);
				    	$total_manual_boleta_inafecto 	+= round($results['manual'][$j]['inafecto'], 2);
				    	$total_manual_boleta_tot 	+= round($results['manual'][$j]['importe'], 2);

				    	if ($z <= 90) {

						$ValorNeto3	+= $results['manual'][$j]['imponible'];
						$Impuestos3	+= $results['manual'][$j]['igv'];
						$exonerada3	+= $results['manual'][$j]['exonerada'];
						$inafecto3	+= $results['manual'][$j]['inafecto'];
						$TotalVentas3	+= $results['manual'][$j]['importe'];

				    	}

				    	if ($z == 90) {

						$reporte->lineaH();
						$arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto3, "impuestos" => $Impuestos3, "exonerada" => $exonerada3, "inafecto" => $inafecto3, "total" => $TotalVentas3, "tcambio" => " ");
						$reporte->nuevaFila($arr, "_sub");

						$z = 1;

						$ValorNeto3 	= 0;
						$Impuestos3 	= 0;
						$exonerada3 	= 0;
						$inafecto3 	= 0;
						$TotalVentas3 	= 0;

					}

				    	$reporte->nuevaFila($results['manual'][$j], "_tickets");
				    	$z++;

				}

			}

		    	$reporte->lineaH();
		    	$arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto3, "exonerada" => $exonerada3, "inafecto" => $inafecto3, "impuestos" => $Impuestos3, "total" => $TotalVentas3, "tcambio" => " ");
		    	$reporte->nuevaFila($arr, "_sub");

		    	$reporte->Ln();
		    	$reporte->lineaH();
		    	$arr = array("rotulo" => "Total Manuales Boleta", "neto" => $total_manual_boleta_imp, "exonerada" => $total_manual_boleta_exonerada, "inafecto" => $total_manual_boleta_inafecto, "impuestos" => $total_manual_boleta_igv, "total" => $total_manual_boleta_tot);
		    	$reporte->nuevaFila($arr, "_rot");

		    	//MANUALES FACTURA
		    	$reporte->definirCabecera(5, "R", "Documentos Manuales Factura");
		    	$reporte->borrarCabeceraPredeterminada();
		    	$reporte->definirCabeceraPredeterminada($cab_general, "_tickets");
		    	$reporte->AddPage();

		    	//ACA APARTIR SE GENERA LA VENTA FACTURAS MANUALES

		    	$w = 1;

			$nmanuales = count($results['manual']);

			for ($i = 0; $i < $nmanuales - 5; $i++) {

		        	if ($results['manual'][$i]['tipo'] == '01') {

					$total_manual_factura_imp 	+= round($results['manual'][$i]['imponible'], 2);
					$total_manual_factura_igv 	+= round($results['manual'][$i]['igv'], 2);
					$total_manual_factura_exonerada	+= round($results['manual'][$i]['exonerada'], 2);
					$total_manual_factura_inafecto	+= round($results['manual'][$i]['inafecto'], 2);
					$total_manual_factura_tot	+= round($results['manual'][$i]['importe'], 2);

					if ($w <= 90) {
						$ValorNeto4 	+= $results['manual'][$i]['imponible'];
						$Impuestos4 	+= $results['manual'][$i]['igv'];
						$exonerada4 	+= $results['manual'][$i]['exonerada'];
						$inafecto4 	+= $results['manual'][$i]['inafecto'];
						$TotalVentas4 	+= $results['manual'][$i]['importe'];

					}

					if ($w == 90) {
						$reporte->lineaH();
						$arr = array("motivo" => "Valor por Pagina: ", "neto" => $ValorNeto4, "exonerada" => $exonerada4, "inafecto" => $inafecto4, "impuestos" => $Impuestos4, "total" => $TotalVentas4, "tcambio" => " ");
						$reporte->nuevaFila($arr, "_sub");
						$w = 1;
						$ValorNeto4 	= 0;
						$Impuestos4 	= 0;
						$exonerada4 	= 0;
						$inafecto4 	= 0;
						$TotalVentas4 	= 0;
					}

					$reporte->nuevaFila($results['manual'][$i], "_tickets");

					$w++;

				}

			}

			$reporte->lineaH();
			$arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto4, "exonerada" => $exonerada4, "inafecto" => $inafecto4, "impuestos" => $Impuestos4, "total" => $TotalVentas4, "tcambio" => " ");
			$reporte->nuevaFila($arr, "_sub");

			$reporte->Ln();
			$reporte->lineaH();
			$arr = array("rotulo" => "Total Manuales Factura", "neto" => $total_manual_factura_imp, "exonerada" => $total_manual_factura_exonerada, "inafecto" => $total_manual_factura_inafecto, "impuestos" => $total_manual_factura_igv, "total" => $total_manual_factura_tot);
			$reporte->nuevaFila($arr, "_rot");

			///FACTURA MANUAL FIN

		    	// NOTAS DE CREDITO
		    	$reporte->definirCabecera(3, "R", "Notas de Credito");
		    	$reporte->borrarCabeceraPredeterminada();
		    	$reporte->definirCabeceraPredeterminada($cab_general, "_tickets");
		   	$reporte->AddPage();

		    	$a = 1;
		    	$nmanuales = count($results['manual']);

		    	for ($i = 0; $i < $nmanuales - 5; $i++) {

		        	if ($results['manual'][$i]['tipo'] == '07') {

				    	$total_manual_credito_imp += round($results['manual'][$i]['imponible'], 2);
				    	$total_manual_credito_igv += round($results['manual'][$i]['igv'], 2);
				   	$total_manual_credito_tot += round($results['manual'][$i]['importe'], 2);

				    	if ($a <= 90) {

				        	$ValorNeto5 += $results['manual'][$i]['imponible'];
				        	$Impuestos5 += $results['manual'][$i]['igv'];
				        	$TotalVentas5 += $results['manual'][$i]['importe'];

				    	}

				    	if ($a == 90) {

						$reporte->lineaH();
						$arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto5, "impuestos" => $Impuestos5, "total" => $TotalVentas5, "tcambio" => " ");
						$reporte->nuevaFila($arr, "_sub");
						$a = 1;
						$ValorNeto5 = 0;
						$Impuestos5 = 0;
				        	$TotalVentas5 = 0;

				    	}

				    	$reporte->nuevaFila($results['manual'][$i], "_tickets");
				    	$a++;

		        	}

		    	}

		    	$reporte->lineaH();
		    	$arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto5, "impuestos" => $Impuestos5, "total" => $TotalVentas5, "tcambio" => " ");
		    	$reporte->nuevaFila($arr, "_sub");

		    	$reporte->Ln();
		    	$reporte->lineaH();
		    	$arr = array("rotulo" => "Total Notas de Credito", "neto" => $total_manual_credito_imp, "impuestos" => $total_manual_credito_igv, "total" => $total_manual_credito_tot);
		    	$reporte->nuevaFila($arr, "_rot");

		    	// NOTAS DE DEBITO
		    	$reporte->definirCabecera(3, "R", "Notas de Debito");
		    	$reporte->borrarCabeceraPredeterminada();
		    	$reporte->definirCabeceraPredeterminada($cab_general, "_tickets");
		    	$reporte->AddPage();

		    	$b = 1;
		    	$nmanuales = count($results['manual']);

		    	for ($i = 0; $i < $nmanuales - 5; $i++) {

		        	if ($results['manual'][$i]['tipo'] == '08') {
				    	$total_manual_debito_imp += round($results['manual'][$i]['imponible'], 2);
				    	$total_manual_debito_igv += round($results['manual'][$i]['igv'], 2);
				   	$total_manual_debito_tot += round($results['manual'][$i]['importe'], 2);

				    	if ($b <= 90) {
					       	$ValorNeto6 += $results['manual'][$i]['imponible'];
						$Impuestos6 += $results['manual'][$i]['igv'];
						$TotalVentas6 += $results['manual'][$i]['importe'];
				    	}

				    	if ($b == 90) {
						$reporte->lineaH();
						$arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto6, "impuestos" => $Impuestos6, "total" => $TotalVentas6, "tcambio" => " ");
						$reporte->nuevaFila($arr, "_sub");
						$b = 1;
						$ValorNeto6 = 0;
						$Impuestos6 = 0;
						$TotalVentas6 = 0;
				    	}

				    	$reporte->nuevaFila($results['manual'][$i], "_tickets");
				   	$b++;

		        	}

			}

	    	$reporte->lineaH();
	    	$arr = array("motivo" => "Valor por Pagina ", "neto" => $ValorNeto6, "impuestos" => $Impuestos6, "total" => $TotalVentas6, "tcambio" => " ");
	    	$reporte->nuevaFila($arr, "_sub");

	    	$reporte->Ln();
	    	$reporte->lineaH();
	    	$arr = array("rotulo" => "Total Notas de Debito", "neto" => $total_manual_debito_imp, "impuestos" => $total_manual_debito_igv, "total" => $total_manual_debito_tot);
	    	$reporte->nuevaFila($arr, "_rot");
		}

		// RESUMEN TOTALES
		$reporte->definirCabecera(3, "R", " ");
		$reporte->borrarCabeceraPredeterminada();
		$reporte->AddPage();
		$reporte->Ln();

		$arr = array("nombre" => "R E S U M E N");
		$reporte->nuevaFila($arr, "_restitu");
		$reporte->lineaH();
		$reporte->Ln();

		$arr = array("nada" => "", "imp" => "Imponible", "igv" => "Igv", "balance" => "ICBPER", "exo" => "Exonerada", "inf" => "Inafecto", "tot" => "Total");
		$reporte->nuevaFila($arr, "_sr");
		$reporte->Ln();

		$arr = array("nombre" => "Total Tickets", "imponible" => $ValorNeto1, "igv" => $Impuestos1, "balance" => $Balance1, "exonerada" => $exonerada1, "inafecto" => $Inafecto1, "importe" => $TotalVentas1);
		$reporte->nuevaFila($arr, "_res");
		$reporte->Ln();

/*		$arr = array("nombre" => "Total Tickets Boletas ", "imponible" => $total_ticket_boleta_imp, "igv" => ($total_ticket_boleta_imp * 0.18), "exonerada" => $total_ticket_boleta_exonerada, "inafecto" => $total_ticket_boleta_inafecto, "importe" => $total_ticket_boleta_tot);
		$reporte->nuevaFila($arr, "_res");

		$arr = array("nombre" => "Total Tickets Facturas ", "imponible" => $total_ticket_factura_imp, "igv" => ($total_ticket_factura_imp * 0.18), "exonerada" => $total_ticket_factura_exonerada, "inafecto" => $total_ticket_factura_inafecto, "importe" => (($total_ticket_factura_imp * 0.18) + $total_ticket_factura_tot));
		$reporte->nuevaFila($arr, "_res");
		$reporte->Ln();
*/

		$arr = array("nombre" => "Total Manual Boletas", "imponible" => $total_manual_boleta_imp, "igv" => $total_manual_boleta_igv, "exonerada" => $total_manual_boleta_exonerada, "inafecto" => $total_manual_boleta_inafecto, "importe" => $total_manual_boleta_tot);
		$reporte->nuevaFila($arr, "_res");

		$arr = array("nombre" => "Total Manual Facturas", "imponible" => $total_manual_factura_imp, "igv" => $total_manual_factura_igv, "exonerada" => $total_manual_factura_exonerada, "inafecto" => $total_manual_factura_inafecto, "importe" => $total_manual_factura_tot);
		$reporte->nuevaFila($arr, "_res");

		$arr = array("nombre" => "Total Notas de Credito", "imponible" => $total_manual_credito_imp, "igv" => $total_manual_credito_igv, "importe" => $total_manual_credito_tot);
		$reporte->nuevaFila($arr, "_res");

		$arr = array("nombre" => "Total Notas de Debito", "imponible" => $total_manual_debito_imp, "igv" => $total_manual_debito_igv, "importe" => $total_manual_debito_tot);
		$reporte->nuevaFila($arr, "_res");

		$reporte->Ln();

		$total_imp		= 0;
		$total_igv		= 0;
		$total_exonerada	= 0;
		$total_inafecto		= 0;
		$total_general		= 0;

//		$total_imp		= $total_ticket_boleta_imp + $total_ticket_factura_imp + $total_manual_boleta_imp + $total_manual_factura_imp - $total_manual_credito_imp - $total_manual_debito_imp;
//		$total_igv		= $total_ticket_boleta_igv + $total_ticket_factura_igv + $total_manual_boleta_igv + $total_manual_factura_igv - $total_manual_credito_igv - $total_manual_debito_igv;
		$total_imp		= $ValorNeto1 + $total_manual_boleta_imp + $total_manual_factura_imp - abs($total_manual_credito_imp) - $total_manual_debito_imp;
		$total_igv		= $Impuestos1 + $total_manual_boleta_igv + $total_manual_factura_igv - abs($total_manual_credito_igv) - $total_manual_debito_igv;
		$total_balance  = $Balance1;
		$total_exonerada	= $exonerada1 + $total_manual_boleta_exonerada + $total_manual_factura_exonerada;
		$total_inafecto		= $Inafecto1 + $total_manual_boleta_inafecto + $total_manual_factura_inafecto;
		$total_general		= $TotalVentas1 + $total_manual_boleta_tot + $total_manual_factura_tot - abs($total_manual_credito_tot) - $total_manual_debito_tot;

		$reporte->lineaH();
		$reporte->Ln();

		$arr = array("nombre" => "T O T A L   G E N E R A L", "imponible" => $total_imp, "igv" => $total_igv, "balance" => $total_balance, "exonerada" => $total_exonerada, "inafecto" => $total_inafecto, "importe" => $total_general);

		$reporte->nuevaFila($arr, "_res");
		$reporte->Ln();
		$reporte->lineaH();

		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/registros_ventas_ingresos.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/registros_ventas_ingresos.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}

	function listaSeries() {
		$series = RegistroVentasModel::obtenerSeries();
		$result = '<select name="seriesdocs[]" size="7" multiple>';

		for ($k = 0; $k < count($series); $k++) {
		    $ser = trim($series[$k]['serie']);
		    $doc = trim($series[$k]['documento']);
		    $tip = trim($series[$k]['tipodoc']);
		    $result .= '<option value="' . $tip . '-' . $ser . '">' . $ser . ' - ' . $doc . '</option>';
		}

		$result .= '</select>';

		return $result;
   	}
}

