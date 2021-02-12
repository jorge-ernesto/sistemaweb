<?php
class LiquidacionTemplate extends Template{

    function titulo(){
		return '<h2><b>Consulta por Facturas Liquidadas</b></h2>';
    }
	
	function TmpReportePDF($datos){
		$columnas = array(
			'FECHA CONSUMO' => 'FECHA CONSUMO',
			'NRO DESPACHO' => 'NRO. DESPACHO',
			'IMPORTE' => 'IMPORTE',
			'NUMERACION DE VALES' => 'NUMERACION DE VALES'
		);

		$fontsize = 7.5;
		$reporte = new CReportes2();
		$reporte->SetMargins(5, 5, 5);
		$reporte->SetFont("courier", "", $fontsize);
		
		$reporte->definirColumna("FECHA CONSUMO", $reporte->TIPO_TEXTO, 30, "L");
        $reporte->definirColumna("NRO DESPACHO", $reporte->TIPO_TEXTO, 25, "C");
        $reporte->definirColumna("IMPORTE",$reporte->TIPO_TEXTO, 20, "L");
        $reporte->definirColumna("NUMERACION DE VALES",$reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("-",$reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("+",$reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("*",$reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("/",$reporte->TIPO_TEXTO, 15, "L");

		$rows = 0;

		for($j=0;$j<count($datos);$j++){
			if (($datos[$j-1][1] != $datos[$j][1])){
				$nro_liq = $datos[$j][1];
				$cliente = trim($datos[$j][3]);
				$raz_social = $datos[$j][4];
				$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
				$reporte->definirCabecera(1, "C", "RESUMEN DE FACTURAS");
				$reporte->definirCabecera(1, "R", "PAG.%p");
				$reporte->definirCabecera(2, "R", "Emitido : "." %f");
				$reporte->definirCabecera(3, "L", "NRO. LIQUIDACION : ".$nro_liq);
				$reporte->definirCabecera(4, "L", "CLIENTE : ".$cliente."-".$raz_social);
				$reporte->definirCabeceraPredeterminada($columnas);
				$reporte->AddPage();
				$reporte->lineaH();
			 }

			 $datos_array['FECHA CONSUMO'] = $datos[$j][5];
			 $datos_array['NRO DESPACHO'] = $datos[$j][2];
			 $datos_array['IMPORTE'] = $datos[$j][6];
			 $datos_array['NUMERACION DE VALES'] = $datos[$j][9];

			 if($datos[$j-1][2] == $datos[$j][2]){
			 	$datos_array['FECHA CONSUMO'] = '';
				$datos_array['NRO DESPACHO'] = '';
				$datos_array['IMPORTE'] = '';
				$acumulador = $acumulador - $datos[$j][6];
				$dobles = $dobles +1;	
			 }

			 $acumulador = $acumulador + $datos[$j][6];
			 $rows = $rows + 1;
			 $reporte->nuevaFila($datos_array);
			 $vales_dobles['-'] = '';

			 if (($datos[$j][1] != $datos[$j+1][1])){
				$reporte->Ln();
				$reporte->Ln();
				$reporte->lineaH();
				$trans = $rows -$dobles;
				$dinero['FECHA CONSUMO'] = "TOTAL IMPORTE :  S/.".$acumulador;
				$totales['FECHA CONSUMO'] = "TOTAL TRANSACCIONES : ".$trans;
				$totales['NRO DESPACHO'] = "TOTAL VALES : ".$rows;
				$reporte->nuevaFila($dinero);
				$reporte->nuevaFila($totales);
				$acumulador = 0;
				$rows = 0;
				$reporte->Ln();
				$reporte->Ln();
			}
		}

		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_resumen_facturas.pdf", "F");
		
		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/reporte_resumen_facturas.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}
    
    function formSearch(){
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
	
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "FACTURACION.LIQUIDACION"));
	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<center>"));
		// $form->addElement(FORM_GROUP_MAIN, new f2element_text('desde', 'Desde:', '', espacios(2), 12, 10));
		// $form->addElement(FORM_GROUP_MAIN, new f2element_text('hasta', 'Hasta:', '', espacios(2), 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('serie', 'Serie:', '', espacios(2), 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('numero', 'Número:', '', espacios(2), 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Buscar', espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Reporte', espacios(0)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</center>"));

		return $form->getForm();
    }
    
    function listado($list, $desde, $hasta){
		$result = '<p align="right"><a href="/sistemaweb/ventas_clientes/generar_liquidacion_vales.php?desde=' . htmlentities($desde) . '&hasta=' . htmlentities($hasta) . '" target="_blank"></p>';
	
		$result .= '<center>';
		$result .= '<table border="1">';

		foreach($list as $ch_liquidacion=>$vales) {
			$result .= '<tr>';
				$result .= '<td colspan="7"><b>Serie: ' . htmlentities($vales['serie']) . ' Número: ' . htmlentities($vales['numero']) . '</b></td>';
			$result .= '</tr>';
			$result .= '<tr>';
				$result .= '<td colspan="7"><b>Liquidacion: ' . htmlentities($ch_liquidacion) . '</b></td>';
			$result .= '</tr>';
			$result .= '<tr>';
				$result .= '<td colspan="7"><b>Cliente: ' . htmlentities($vales['ch_cliente'] . " - " . $vales['cli_razsocial']) . '</b></td>';
			$result .= '</tr>';
			$result .= '<tr>';
				// $result .= '<td>Fecha Consumo</td>';
				// $result .= '<td># de Vale</td>';
				$result .= '<td>Articulo</td>';
				$result .= '<td>Descripcion</td>';
				$result .= '<td>Cantidad</td>';
				$result .= '<td>Precio</td>';
				$result .= '<td>Importe</td>';
			$result .= '</tr>';
			foreach($vales['detalle'] as $i=>$vale) {
				$result .= '<tr>';
					// $result .= '<td>' . htmlentities($vale['dt_fecha']) . '</td>';
					// $result .= '<td>' . htmlentities($vale['ch_documento']) . '</td>';
					$result .= '<td>' . htmlentities($vale['ch_articulo']) . '</td>';
					$result .= '<td>' . htmlentities($vale['art_descripcion']) . '</td>';
					$result .= '<td>' . htmlentities($vale['nu_cantidad']) . '</td>';
					$result .= '<td>' . htmlentities($vale['art_precio']) . '</td>';
					$result .= '<td>' . htmlentities($vale['art_importe']) . '</td>'; //art_importe']) . '</td>';
				$result .= '</tr>';
			}
			// $result .= '<tr>';
			// 	$result .= '<td colspan="7"></td>';
			// $result .= '</tr>';
			$result .= '<tr>';
				$result .= '<td colspan="7" align="right">Total Importe: ' . htmlentities($vales['total']) . '</td>';
			$result .= '</tr>';
			$result .= '<tr>';
				$result .= '<td colspan="7">&nbsp;</td>';
			$result .= '</tr>';
		} // ./ For
	
		$result .= '</table>';
		$result .= '</center>';
	
		return $result;
    }
}


