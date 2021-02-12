<?php

class AuditorVentaTemplate extends Template {

    function Titulo() {
        return '<div align="center"><h3><b style="color:#336699">CUADRO RESUMEN DE VERIFICACION DE VENTAS.</b></h2></div>';
    }

    function formSearch() {
        $estaciones = AuditorVentaModel::obtenerEstaciones();
        $ano = date("Y");
        $mes = date("m");
        $acciones = array("Normal" => "Normal", "Agrupado" => "Agrupado");
        $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.AUDITORVENTA"));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("action", "Buscar"));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "TODAS", $estaciones, ''));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Ano</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("ano", "", $ano, '', 4, 5));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mes: '));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("mes", "", $mes, '', 2, 4));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.gif" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
        // $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>'));
        // $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"> Excel</button>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

        return $form->getForm();
    }

    function listado($sAlmacen, $venta_contrometros, $ventas_tickes, $ventas_tickes_market, $monto_registro_detallado, $monto_vales_generados, $monto_facturado, $ano_mes) {
        echo "<script>console.log('" . json_encode($venta_contrometros) . "')</script>";

		$result = '';
		$result .= '<h3>I. VENTAS DE COMBUSTIBLE<h3><br/>';
		$result .= '<table border="0" width="1130px">';

		$result .= '<tr>';
		$result .= '<td rowspan=2 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Fecha</strong></td>';
		$result .= '<td colspan=4 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"><b>Contometros Digitales de Venta de Combustible </strong></td>';
		$result .= '<td colspan=5 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"> <b>Tickets Automaticos de Venta de Combustible </strong></td>';
		$result .= '<td rowspan=2 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"> <b>Vales Manuales</strong></td>';
		$result .= '<td align="center" bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Despachos Perdidos</strong></td>';
		$result .= '<td rowspan=2 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Diferencia</strong></td>';
		$result .= '<td rowspan=2 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1  style="text-transform: uppercase; text-align: center;" align="center"><b>Estado F.E. (OFICINA)</strong></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Contometros</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Afericion</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Descuentos</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Venta Neta</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Boleta</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Factura</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>N.Despacho</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Afericion</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Venta Neta</strong></td>';
		$result .= '<td bgcolor="#4682B4" style="color:#FFFFFF;font-size:10px;text-align: center;"><font size=1 style="text-transform: uppercase;"><b>Factura y Boletas</strong></td>';
		$result .= '</tr>';

		// Object Model
		$objModelAuditorVenta = new AuditorVentaModel();

        foreach ($venta_contrometros as $key_alm => $value_fecha_row) {
	    	$suma_venta_contrometro = 0;
	    	$suma_venta_afericion = 0;
	    	$suma_venta_descuento = 0;
	    	$suma_venta_neto = 0;

	    	//TOTALE TICKES
	    	$suma_tickes_b = 0;
	    	$suma_tickes_f = 0;
	    	$suma_tickes_n = 0;
	    	$suma_tickes_a = 0;
	    	$suma_tickes_neto = 0;
	    	$suma_vales_manuales = 0;
	    	$suma_documento_manuales = 0;
	    	$suma_diff_contrometro_tickes = 0;
	    	$suma_importe_descuento_extornos = 0;

	    	foreach ($value_fecha_row as $key_fecha => $value_row) {

				$importe_combustible = doubleval($value_row['importe_combustible']);
				$importe_afericion = (!empty($value_row['importe_afericon'])) ? doubleval($value_row['importe_afericon']) : "0.00";
				$importe_descuento = (empty($value_row['importe_descuento'])) ? "0.00" : doubleval($value_row['importe_descuento']);
                //$neto_contrometros = doubleval($importe_combustible - ($importe_afericion + abs($importe_descuento)));
                $neto_contrometros = doubleval(($importe_combustible - $importe_afericion) + $importe_descuento);

				//VENTAS TICKES
				$importe_tickes_b 		= doubleval($ventas_tickes[$key_alm][$key_fecha]['monto_boleta']);
				$importe_tickes_f 		= doubleval($ventas_tickes[$key_alm][$key_fecha]['monto_facturas']);
				$importe_tickes_n 		= doubleval($ventas_tickes[$key_alm][$key_fecha]['monto_nota_despachos']);
				$importe_tickes_a 		= doubleval($ventas_tickes[$key_alm][$key_fecha]['monto_afericiones']);
				$importe_descuento_extornos 	= doubleval($ventas_tickes[$key_alm][$key_fecha]['monto_descuento_extornos']);

				//VALES MANUAlES
				$vales_manuales			= doubleval($ventas_tickes[$key_alm][$key_fecha]['monto_vales_manuales']);		
				$documento_manuales		= doubleval($ventas_tickes[$key_alm][$key_fecha]['monto_documento_manuales']);		
		
				//TOTAL TICKETS
				$importe_tickes_neto 		= ($importe_tickes_b + $importe_tickes_f + $importe_tickes_n + $vales_manuales + $documento_manuales);

				//VENTAS DIFERENCIAS
				$diferencia_contrometros_tickes = $neto_contrometros - $importe_tickes_neto;

				$arrParams = array (
					'sAlmacen' => $sAlmacen,
				    'dFechaEmision' => $key_fecha,
				);
				$arrResponseEstado = $objModelAuditorVenta->verificarDocumentosOficinaEnviadosEBI($arrParams);
				/*
				echo "<pre>";
				var_dump($arrResponseEstado);
				echo "</pre>";
				*/
				$sMessageStatus = '';
				if ( $arrResponseEstado['sStatus'] == 'success' ){
					foreach ($arrResponseEstado['arrData'] as $row) {
						if ( isset($row['nu_cantidad_registrado']) && (int)$row['nu_cantidad_registrado'] > 0 ) {
							$sMessageStatus .= 'Registrado(s): ' . $row['nu_cantidad_registrado'];
						}
						if ( isset($row['nu_cantidad_completado']) && (int)$row['nu_cantidad_completado'] > 0 ) {
							$sMessageStatus .= '<br>';
							$sMessageStatus .= 'Completado(s): ' . $row['nu_cantidad_completado'];
						}
						if ( isset($row['nu_cantidad_anulado']) && (int)$row['nu_cantidad_anulado'] > 0 ) {
							$sMessageStatus .= '<br>';
							$sMessageStatus .= 'Anulado(s): ' . $row['nu_cantidad_anulado'];
						}
						if ( isset($row['nu_cantidad_completado_error']) && (int)$row['nu_cantidad_completado_error'] > 0 ) {
							$sMessageStatus .= '<br>';
							$sMessageStatus .= 'Completado(s) error: ' . $row['nu_cantidad_completado_error'];
						}
						if ( isset($row['nu_cantidad_anulado_error']) && (int)$row['nu_cantidad_anulado_error'] > 0 ) {
							$sMessageStatus .= '<br>';
							$sMessageStatus .= 'Anualdo(s) error: ' . $row['nu_cantidad_anulado_error'];
						}
					}
				} else {
					$sMessageStatus = $arrResponseEstado['sMessage'];
				}

				$result .= '<tr>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;"><font size=1><b>' . $key_fecha . '</strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $importe_combustible . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($importe_afericion, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format(abs($importe_descuento), 2) . ' </strong></td>';
				$result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($neto_contrometros, 2) . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($importe_tickes_b, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($importe_tickes_f, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($importe_tickes_n, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($importe_tickes_a, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($importe_tickes_neto, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($vales_manuales, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($documento_manuales, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($diferencia_contrometros_tickes, 4) . ' </strong></td>';
				$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: left;width: 100px;"><font size=1>' . $sMessageStatus . '</td>';
				$result .= '</tr>';

				$suma_venta_contrometro += $importe_combustible;
				$suma_venta_afericion += $importe_afericion;
				$suma_venta_descuento += $importe_descuento;
				$suma_venta_neto += $neto_contrometros;

				$suma_tickes_b			+= $importe_tickes_b;
				$suma_tickes_f			+= $importe_tickes_f;
				$suma_tickes_n			+= $importe_tickes_n;
				$suma_tickes_a			+= $importe_tickes_a;
				$suma_vales_manuales		+= $vales_manuales;
				$suma_documento_manuales	+= $documento_manuales;
				$suma_tickes_neto 		+= $importe_tickes_neto;
				$suma_diff_contrometro_tickes	+= $diferencia_contrometros_tickes;
				// $suma_importe_descuento_extornos+=$importe_descuento_extornos;
			}// /. For each detalle

            $result .= '<tr>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;"><font size=1><b>' . $key_fecha . '</strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_contrometro, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format("-" . $suma_venta_afericion, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_descuento, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_neto, 4) . ' </strong></td>';

            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_tickes_b, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_tickes_f, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_tickes_n, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format("-" . $suma_tickes_a, 4) . ' </strong></td>';
            //$result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_importe_descuento_extornos, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_tickes_neto, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_vales_manuales, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_documento_manuales, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_diff_contrometro_tickes, 4) . ' </strong></td>';
            $result .= '</tr>';
        }// /. For each totales
        $result .= '</table>';





        //SEGUNDO CUADRO DE VENTAS II. VENTAS DE PRODUCTOS DE TIENDA, VALES DE CREDITO Y FACTURACION
        $result .= '<h3>II. VENTAS DE PRODUCTOS DE TIENDA, VALES DE CREDITO Y FACTURACION<h3><br/>';

        $result .= '<table border="0" width="1130px">';

        $result .= '<tr>';
        $result .= '<td rowspan=2 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Fecha</strong></td>';
        $result .= '<td  colspan=4 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"><b>Tickets de Venta de Tienda </strong></td>';
        $result .= '<td  colspan=1 rowspan=2 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"> <b>Registro Venta Tickets </strong></td>';
        $result .= '<td  colspan=4  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Vales de Credito</strong></td>';
        $result .= '<td  colspan=5  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Facturacion</strong></td>';
        $result .= '</tr>';
        $result .= '<tr>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Boleta</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Factura</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>N.Despacho</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Total</strong></td>';

        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Total</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Sin Liquidar</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Vales Liquidados</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Vales . Efectivo</strong></td>';

        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Liquidada</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Anticipos</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Manual BOL y FAC</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>Manual N.Credito</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;width: 97px;"><font size=1 style="text-transform: uppercase;"><b>-</strong></td>';


        $result .= '</tr>';
        foreach ($venta_contrometros as $key_alm => $value_fecha_row) {//se usa tambien el areglo de la venta de combustible por que tiene todos los dias
            $suma_venta_tickes_b = 0;
            $suma_venta_tickes_f = 0;
            $suma_venta_tickes_n = 0;
            $suma_venta_tickes_neto = 0;
            $suma_reg_detallado = 0;

            $suma_venta_tickes_vales_total = 0;
            $suma_venta_tickes_vales_sin_liquidar = 0;
            $suma_venta_tickes_vales_liquidar = 0;
            $suma_venta_tickes_vales_efectivo = 0;

            $suma_fac_importe_liquidada = 0;
            $suma_fac_importe_anticipos = 0;
            $suma_fac_monto_normal = 0;


            foreach ($value_fecha_row as $key_fecha => $value_row) {

                $venta_tickes_market_b = doubleval($ventas_tickes_market[$key_alm][$key_fecha]['monto_boleta']);
                $venta_tickes_market_f = doubleval($ventas_tickes_market[$key_alm][$key_fecha]['monto_facturas']);
                $venta_tickes_market_n = doubleval($ventas_tickes_market[$key_alm][$key_fecha]['monto_nota_despachos']);
                $neto_tickes_venta = doubleval($venta_tickes_market_b + $venta_tickes_market_f + $venta_tickes_market_n);
                //------------------------
                $monto_reg_venta_detallado = doubleval($monto_registro_detallado[$key_alm][$key_fecha]['monto_registro_detallado']);
                //-------------------------

                $venta_tickes_vales_total = doubleval($monto_vales_generados[$key_alm][$key_fecha]['importe']);
                $venta_tickes_vales_sin_liquidar = doubleval($monto_vales_generados[$key_alm][$key_fecha]['importe_sin_liquidar']);
                $venta_tickes_vales_liquidar = doubleval($monto_vales_generados[$key_alm][$key_fecha]['nu_importe_liquidado']);
                $venta_tickes_vales_efectivo = doubleval($monto_vales_generados[$key_alm][$key_fecha]['nu_importe_efectivo']);

                //------------------
                $fac_importe_liquidada = doubleval($monto_facturado[$key_alm][$key_fecha]['importe_liquidada']);
                $fac_importe_anticipos = doubleval($monto_facturado[$key_alm][$key_fecha]['importe_anticipos']);
                $fac_monto_normal = doubleval($monto_facturado[$key_alm][$key_fecha]['monto_normal']);
                $fac_monto_normal_nc=doubleval($monto_facturado[$key_alm][$key_fecha]['monto_normal_nc']);


                $result .= '<tr>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;"><font size=1><b>' . $key_fecha . '</strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($venta_tickes_market_b, 4) . ' </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($venta_tickes_market_f, 4) . ' </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($venta_tickes_market_n, 2) . ' </strong></td>';
                $result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($neto_tickes_venta, 4) . ' </strong></td>';

                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($monto_reg_venta_detallado, 4) . ' </strong></td>';

                $result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($venta_tickes_vales_total, 4) . ' </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($venta_tickes_vales_sin_liquidar, 4) . ' </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($venta_tickes_vales_liquidar, 4) . ' </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($venta_tickes_vales_efectivo, 4) . ' </strong></td>';

                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b><a style="color:#4682B4;text-decoration:none" href="informe_facturas_liquidada.php?accion=1&yyyyaa=' . $ano_mes . '&fecha_filtro=' . $key_fecha . '">' . number_format($fac_importe_liquidada, 4) . '</a> </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($fac_importe_anticipos, 4) . ' </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($fac_monto_normal, 4) . ' </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($fac_monto_normal_nc, 4) . ' </strong></td>';
                $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b><a style="color:#4682B4;text-decoration:none" href="informe_facturas_liquidada.php?accion=2&yyyyaa=' . $ano_mes . '&fecha_filtro=' . $key_fecha . '">Rel.vales</a> </strong></td>';




                $result .= '</tr>';
                $suma_venta_tickes_b += $venta_tickes_market_b;
                $suma_venta_tickes_f += $venta_tickes_market_f;
                $suma_venta_tickes_n += $venta_tickes_market_n;
                $suma_venta_tickes_neto += $neto_tickes_venta;
                $suma_reg_detallado+=$monto_reg_venta_detallado;

                $suma_venta_tickes_vales_total += $venta_tickes_vales_total;
                $suma_venta_tickes_vales_sin_liquidar += $venta_tickes_vales_sin_liquidar;
                $suma_venta_tickes_vales_liquidar+=$venta_tickes_vales_liquidar;
                $suma_venta_tickes_vales_efectivo += $venta_tickes_vales_efectivo;

                $suma_fac_importe_liquidada += $fac_importe_liquidada;
                $suma_fac_importe_anticipos += $fac_importe_anticipos;
                $suma_fac_monto_normal += $fac_monto_normal;
            }
            $result .= '<tr>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;"><font size=1><b>' . $key_fecha . '</strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_tickes_b, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_tickes_f, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_tickes_n, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_tickes_neto, 4) . ' </strong></td>';

            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_reg_detallado, 4) . ' </strong></td>';

            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_tickes_vales_total, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_tickes_vales_sin_liquidar, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_tickes_vales_liquidar, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_tickes_vales_efectivo, 4) . ' </strong></td>';

            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_fac_importe_liquidada, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_fac_importe_anticipos, 4) . ' </strong></td>';
            $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_fac_monto_normal, 4) . ' </strong></td>';
            $result .= '</tr>';
        }
        $result .= '</table>';



        return $result;
    }

    function vales_liquidadas($facturas_liquidadas) {

        $result = '';
        $result .= '<h3>I. VALES RELACIONADO CON LA FACTURA LIQUIDADA<h3><br/>';
        $result .= '<table border="0" width="1120px">';

        $result .= '<tr>';
        $result .= '<td  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>F. Vales Creado</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Num Vale</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>T.DOC</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Serie</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>N.DOC</strong></td>';

        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>F.Liquidada</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Cliente</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Monto Total</strong></td>';


        $result .= '</tr>';
        foreach ($facturas_liquidadas as $key_alm => $value_fecha_row) {

            // var_dump($value_fecha_row);



            $dt_fecha = $value_fecha_row['dt_fecha'];
            $ch_numeval = $value_fecha_row['ch_numeval'];
            $ch_fac_tipodocumento = $value_fecha_row['ch_fac_tipodocumento'];
            $ch_fac_seriedocumento = $value_fecha_row['ch_fac_seriedocumento'];
            $ch_fac_numerodocumento = $value_fecha_row['ch_fac_numerodocumento'];
            $ch_liquidacion = $value_fecha_row['ch_liquidacion'];
            $ch_cliente = $value_fecha_row['ch_cliente'];
            $nu_fac_valortotal = $value_fecha_row['nu_fac_valortotal'];


            $result .= '<tr>';
            $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $dt_fecha . ' </strong></td>';
            $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $ch_numeval . ' </strong></td>';
            $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $ch_fac_tipodocumento . ' </strong></td>';
            $result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $ch_fac_seriedocumento . ' </strong></td>';

            $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $ch_fac_numerodocumento . ' </strong></td>';
            $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $ch_liquidacion . ' </strong></td>';
            $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $ch_cliente . ' </strong></td>';
            $result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($nu_fac_valortotal, 4) . ' </strong></td>';


            $result .= '</tr>';
            /*
              $result .= '<tr>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;"><font size=1><b>' . $key_fecha . '</strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_contrometro, 4) . ' </strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format("-" . $suma_venta_afericion, 4) . ' </strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_descuento, 4) . ' </strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_venta_neto, 4) . ' </strong></td>';

              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_tickes_b, 4) . ' </strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_tickes_f, 4) . ' </strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_tickes_n, 4) . ' </strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format("-" . $suma_tickes_a, 4) . ' </strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_tickes_neto, 4) . ' </strong></td>';
              $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($suma_diff_contrometro_tickes, 4) . ' </strong></td>';
              $result .= '</tr>';
             */
        }
        $result .= '</table>';








        echo $result;
    }

    function diferencias_vales($vales_postrans, $vales_cabecera, $vales_complem, $maximo) {

        $result = '';
        $result .= '<h3>I. DIFERENCIAS DE VALES(TICKES VALES,VALES ,VALES LIQUIDADOS)<h3><br/>';
        $result .= '<table border="0" width="1120px">';

        $result .= '<tr>';
        $result .= '<td  colspan=3 bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1  style="text-transform: uppercase;text-align: center;"><b>Tickes Notas.D Emitidos</strong></td>';
        $result .= '<td  colspan=3  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"><b>Notas D. Manejables</strong></td>';
        $result .= '<td  colspan=4  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"><b>Notas D. Liquidada</strong></td>';
        $result .= '</tr>';
        $result .= '<tr>';

        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Fecha</strong></td>';
        $result .= '<td    bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>N.vale</strong></td>';
        $result .= '<td    bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Monto</strong></td>';
        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Fecha</strong></td>';
        $result .= '<td    bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>N.vale</strong></td>';
        $result .= '<td    bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Monto</strong></td>';

        $result .= '<td   bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>Fecha</strong></td>';
        $result .= '<td    bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>N.vale</strong></td>';
        $result .= '<td    bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>Monto</strong></td>';
        $result .= '<td    bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: right;"><font size=1 style="text-transform: uppercase;"><b>-</strong></td>';
        $result .= '</tr>';
        $sum_importe_pos_trans = 0;
        $sum_importe_vale = 0;
        $sum_importe_vale_c = 0;
        for ($i = 0; $i < $maximo; $i++) {

            $dt_fecha = $vales_postrans[$i]['fecha'];
            $trans = $vales_postrans[$i]['vale'];
            $importe_pos_trans = doubleval($vales_postrans[$i]['importe']);

            $dt_fecha_v = $vales_cabecera[$i]['fecha'];
            $numvale = $vales_cabecera[$i]['vale'];
            $importe_vale = doubleval($vales_cabecera[$i]['importe']);

            $dt_fecha_vc = $vales_complem[$i]['fecha'];
            $numvale_c = $vales_complem[$i]['vale'];
            $importe_vale_c = doubleval($vales_complem[$i]['importe']);
            $bg_color = "#FFFFFF";

            $estado = "";
            if (trim($trans) == trim($numvale)) {
                $estado = "Correcto";
            } else {
                $estado = "Error";
            }

            $result .= '<tr>';

            $result .= '<td  bgcolor="' . $bg_color . '" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $dt_fecha . ' </strong></td>';
            $result .= '<td  bgcolor="' . $bg_color . '" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $trans . ' </strong></td>';
            $result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $importe_pos_trans . ' </strong></td>';

            $result .= '<td  bgcolor=""' . $bg_color . '" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $dt_fecha_v . ' </strong></td>';
            $result .= '<td  bgcolor="' . $bg_color . '" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $numvale . ' </strong></td>';
            $result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $importe_vale . ' </strong></td>';


            $result .= '<td  bgcolor="' . $bg_color . '" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $dt_fecha_vc . ' </strong></td>';
            $result .= '<td  bgcolor="' . $bg_color . '" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $numvale_c . ' </strong></td>';
            $result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $importe_vale_c . ' </strong></td>';
            $result .= '<td  bgcolor=""' . $bg_color . '" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . $estado . '</strong></td>';

            $result .= '</tr>';
            $sum_importe_pos_trans += $importe_pos_trans;
            $sum_importe_vale += $importe_vale;
            $sum_importe_vale_c += $importe_vale_c;
        }
        $result .= '<tr>';
        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;"><font size=1><b>-</strong></td>';
        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>- </strong></td>';
        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($sum_importe_pos_trans, 4) . ' </strong></td>';

        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>- </strong></td>';
        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>-</strong></td>';
        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($sum_importe_vale, 4) . ' </strong></td>';

        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>- </strong></td>';
        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>- </strong></td>';
        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($sum_importe_vale_c, 4) . ' </strong></td>';
        $result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format(($sum_importe_pos_trans - $sum_importe_vale), 4) . ' </strong></td>';
        $result .= '</tr>';
        $result .= '</table>';








        echo $result;
    }

    function getUltimoDiaMes($elAnio, $elMes) {


        $fecha_actual = date("Y-m");
        $fecha_ingresada = trim($elAnio . "-" . $elMes);
        if ($fecha_actual == $fecha_ingresada) {
            return date("d");
        } else {
            return date("d", (mktime(0, 0, 0, $elMes + 1, 1, $elAnio) - 1));
        }
    }

}
