<?php

class ParteLiquidacionTemplate extends Template {
    
	function search_form(){
		$fecha = date(d."/".m."/".Y); 
		$estaciones = ParteLiquidacionModel::obtieneListaEstaciones();
		$form = new form2("Parte Diario de Liquidacion de Ventas NUEVO", "form_parte_diario", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.PARTELIQUIDACIONNEW"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0"><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;<input type="checkbox" name="market" id="market"  value="1">Ver Datos de Market'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;<input type="checkbox" name="documento" id="documento"  value="2">Ver Total de Documentos'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;<input type="checkbox" name="gnv" id="gnv"  value="3">Datos de GNV'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_parte_diario.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>&nbsp;<td/><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_parte_diario.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" ></td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		return $form->getForm();
    	}
    
	function reporte($results0,$results1,$results2,$results3,$results4,$results5,$results6,$desde, $hasta, $estacion, $mostrar_market,$mostrar_documento,$mostrar_gnv) {
		$result = '<table border="0" align="center" width="650px"><tr>';
		$result .= '<td  align="right" style="font-weight:bold">';
		$result .= 'T.C. DEL DIA: '.$results0['propiedades']['0'];
		$result .= '</td>';
		$result .= '</tr></table>';
		$result .= '<br/>';
		$result .= '<table border="1" align="center">';
		$result .= '<tr><td colspan="11"><h3>I.  COMBUSTIBLES</h3></td></tr><tr>';
		$result .= '<th>PRODUCTO</th>';
		$result .= '<th>STOCK INICIAL</th>';
		$result .= '<th>COMPRAS</th>';
		$result .= '<th>VENTAS</th>';
		$result .= '<th>%</th>';
		$result .= '<th>TRANSFERENCIAS</th>';
		$result .= '<th>STOCK FINAL</th>';
		$result .= '<th>MEDICION</th>';
		$result .= '<th colspan="2">DIFERENCIAS</th>';
		$result .= '<th>IMPORTE VENTA</th>';
		$result .= '</tr><tr>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<th>&nbsp;</th>';
		$result .= '<th>DIA</th>';
		$result .= '<td>MES</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '</tr>';
		$numfilas = 0;

		foreach($results1['propiedades'] as $a => $almacenes) {
			foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				foreach($venta['partes'] as $dt_producto=>$producto) {
					if ($dt_producto != '11620307'){ //Si no es GLP
		            			$numfilas= $numfilas +1;
			    			$result .= ParteLiquidacionTemplate::imprimirLinea($producto, $dt_producto,$venta['totales']['total']['ventas']);
			    		}
				}
				$result .= ParteLiquidacionTemplate::imprimirLinea($venta['totales']['total'], "Total");
				$result .= '<tr><td colspan="11">&nbsp;</td></tr><tr>';
				$numfilas = 0;
				foreach($venta['partes'] as $dt_producto=>$producto) {
		            		if ($dt_producto == '11620307'){ //Si es GLP
					    	$numfilas= $numfilas +1;
					    	$result .= ParteLiquidacionTemplate::imprimirLinea($producto, $dt_producto,'');
			    		}
				}
		    	}
		}
		$result .= '</table>';
		$result .= '<br/<br/>';
		$result .= '<table  align="center" border=0 cellspacing=1 cellpadding=1><tr><td>';
		$result .= '<table border="1" align="center">';
		$result .= '<tr><td colspan="10"><h3>II. VENTAS COMBUSTIBLE</h3>';
		$numfilas = 0;

		foreach($results2['propiedades'] as $a => $almacenes) {
			foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		        	$numfilas= $numfilas +1;
				if($numfilas=1) 
					$result .='<h4>'.$venta[12].'</h4></td></tr>';				
				$result .= ParteLiquidacionTemplate::imprimirLineaUnica($venta, $ch_almacen,'C');
			}
		}
		$result .= '</table>';
		/*$result .= '</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>';
		$result .= '<table border="1" align="center">';
		$result .= '<tr><td colspan="10"><h3>III. VENTAS GLP</h3>';
		$numfilas = 0;

		foreach($results3['propiedades'] as $a => $almacenes) {
			foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		        	$numfilas= $numfilas +1;
				if($numfilas=1) 
					$result .='<h4>'.$venta[11].'</h4></td></tr>';
			    	$result .= ParteLiquidacionTemplate::imprimirLineaUnica($venta, $ch_almacen,'');
		    	}
		}
		$result .= '</table>';

		$result .= '</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>';
*/
		/*if ($mostrar_market == '1'){
			$result .= '<table border="1" align="center">';
			$result .= '<tr><td colspan="10"><h3>IV. VENTAS MARKET</h3>';
			$numfilas = 0;
			foreach($results4['propiedades'] as $a => $almacenes) {
				foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
					$numfilas = $numfilas +1;
					$result .= ParteLiquidacionTemplate::imprimirLineaUnica($venta, $ch_almacen,'M');
				}
			}
			$result .= '</table>';
		}

		$result .= '</td></tr></table>';

		if ($mostrar_documento == '2'){
			$result .= '<table border="1" align="center">';
			$result .= '<tr><td colspan="10"><h3>V. TOTAL DE VENTA POR DOCUMENTOS</h3>';
			$numfilas = 0;
			foreach($results5['propiedades'] as $a => $almacenes) {
				foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
					$numfilas = $numfilas +1;
					$result .= ParteLiquidacionTemplate::imprimirLineaDoc($venta, $ch_almacen,'');
				}
			}
			$result .= '</table>';
		}

		$result .= '</table>';

		$result .= '</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>';

		if ($mostrar_gnv == '3'){
			$result .= '<table border="1" align="center">';
			$result .= '<tr><td colspan="10"><h3>VI. VENTAS DE GNV</h3>';
			$numfilas = 0;
			foreach($results6['propiedades'] as $a => $almacenes) {
				foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
					$numfilas = $numfilas +1;
					$result .= ParteLiquidacionTemplate::imprimirLineaGNV($venta, $ch_almacen);
				}
			}
			$result .= '</table>';
		}

		$result .= '</td></tr></table>';
*/
		return $result;
    	}

	function imprimirLineaGNV($array,$label) {

		$result .= '<tr><td>Contometros Generales</td><td align="right">'. htmlentities(number_format($array[5] - $array[4], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Cantidad GNV</td><td align="right">'. htmlentities(number_format($array[6], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Total Venta</td><td align="right">'. htmlentities(number_format($array[7], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Abono Cofide</td><td align="right">'. htmlentities(number_format($array[8], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Despachos de prueba</td><td align="right">'. htmlentities(number_format($array[9], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Ventas a Clientes de Credito</td><td align="right">'. htmlentities(number_format($array[10], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Ventas a Clientes Anticipo</td><td align="right">'. htmlentities(number_format($array[11], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Ventas con Tarjetas de Credito</td><td align="right">'. htmlentities(number_format($array[12], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Descuentos Otorgados</td><td align="right">'. htmlentities(number_format($array[13], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Faltantes Trabajador</td><td align="right">'. htmlentities(number_format($array[14], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Sobrantes Trabajador</td><td align="right">'. htmlentities(number_format($array[15], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Efectivo Soles</td><td align="right">'. htmlentities(number_format($array[16], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Efectivo Dolares</td><td align="right">'. htmlentities(number_format($array[17], 2, '.', ',')) .'</td></tr>';

		return $result;
	}

	function imprimirLineaDoc($array, $label,$totalventa) {
		global $marketing,$glp,$combustible;

		$result .= '<tr><td style="font-weight:bold">TICKET FACTURAS</td><td align="right" style="font-weight:bold">'.htmlentities(number_format($array[2], 2, '.', ',')).'</td></tr>';
		$result .= '<tr><td style="font-weight:bold">TICKET BOLETA</td><td align="right" style="font-weight:bold">'.htmlentities(number_format($array[3], 2, '.', ',')).'</td></tr>';
		$result .= '<tr><td style="font-weight:bold">NOTA DE DESPACHO</td><td align="right" style="font-weight:bold">'.htmlentities(number_format($array[4], 2, '.', ',')).'</td></tr>';
		$result .= '<tr><td style="font-weight:bold">DESPACHO DE PRUEBA</td><td align="right" style="font-weight:bold">'.htmlentities(number_format($array[5], 2, '.', ',')).'</td></tr>';
		$result .= '<tr><td style="font-weight:bold">DOCUMENTOS MANUALES</td><td align="right" style="font-weight:bold">'.htmlentities(number_format($array[6], 2, '.', ',')).'</td></tr>';
		$result .= '<tr><td style="font-weight:bold">TOTAL</td><td align="right" style="font-weight:bold">'. htmlentities(number_format(($array[2]+$array[3]+$array[4]+$array[5]+$array[6]), 2, '.', ',')) .'</td></tr>';

		$total_tickets = number_format($array[2]+($array[3]+$array[4]+$array[5]+$array[6]-$marketing-$glp-$combustible),2,'.',',');

		$result .= '<tr><td  style="font-weight:bold">DIFERENCIA FINAL DE VENTA</td><td align="right" style="font-weight:bold">'.$total_tickets.'</td></tr>';
		
		return $result;
	}
    
	function imprimirLineaUnica($array, $label,$totalventa) {
		global $marketing,$glp,$combustible;

		if($totalventa=='M'){
			$result  = '<tr><td>&nbsp;</td><td align="right">&nbsp;</td></tr>';
			$result .= '<tr><td>&nbsp;</td><td align="right">&nbsp;</td></tr>';
			$marketing = array();
			$marketing = $array[2];			
		}else{
			$result  = '<tr><td>Contometros</td><td align="right">'. htmlentities(number_format($array[0], 2, '.', ',')). '</td></tr>';
			$result .= '<tr><td>Afericion</td><td align="right">'. htmlentities(number_format($array[1], 2, '.', ',')). '</td></tr>';
			$glp = array();
			$glp = $array[2];
		}
		$result .= '<tr><td style="font-weight:bold">VENTA</td><td align="right" style="font-weight:bold">'.htmlentities(number_format($array[2], 2, '.', ',')).'</td></tr>';
		$result .= '<tr><td colspan="2">&nbsp;</td></tr>';
		$result .= '<tr><td>Credito Clientes</td><td align="right">'. htmlentities(number_format($array[3], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Credito Anticipos</td><td align="right">'. htmlentities(number_format($array[4], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Tarjetas de Credito</td><td align="right">'. htmlentities(number_format($array[5], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Descuentos</td><td align="right">'. htmlentities(number_format($array[6], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Faltantes Trabajador</td><td align="right">'. htmlentities(number_format($array[9], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Sobrantes Trabajador</td><td align="right">'. htmlentities(number_format($array[10], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Efectivo Soles</td><td align="right">'. htmlentities(number_format($array[7], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td>Efectivo Dolares</td><td align="right">'. htmlentities(number_format($array[8], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td style="font-weight:bold">SUSTENTO</td><td align="right" style="color:blue;font-weight:bold">'. htmlentities(number_format($array[3]+$array[4]+$array[5]+$array[6]+$array[7]+$array[8]+$array[9]+$array[10], 2, '.', ',')) .'</td></tr>';
		$result .= '<tr><td colspan="2">&nbsp;</td></tr>';
		$result .= '<tr><td style="font-weight:bold">DIFERENCIA</td><td align="right" style="color:blue;font-weight:bold">'. htmlentities(number_format($array[2]-($array[3]+$array[4]+$array[5]+$array[6]+$array[7]+$array[8]+$array[9]+$array[10]), 2, '.', ',')) .'</td></tr>';
		
		if($totalventa=='C'){
			$result .= '<tr><td colspan="2">&nbsp;</td></tr>';
			$result .= '<tr><td colspan="2" style="font-weight:bold">TOTALES A BANCOS</td></tr>';
			$result .= '<tr><td>Soles</td><td align="right">'. htmlentities(number_format($array[7], 2, '.', ',')) .'</td></tr>';
			$result .= '<tr><td>Dolares</td><td align="right">'. htmlentities(number_format($array[11], 2, '.', ',')) .'</td></tr>';
			$result .= '<tr><td colspan="2" style="font-weight:bold">&nbsp;</td></tr>';
			$result .= '<tr><td colspan="2" style="font-weight:bold">DIFERENCIAS MANUALES</td></tr>';
			$result .= '<tr><td>Total</td><td align="right">'. htmlentities(number_format($array[13], 2, '.', ',')) .'</td></tr>';
			$combustible = array();
			$combustible = $array[2];
		}

		return $result;
	}


	function imprimirLinea($array, $label,$totalventa) {
		$result  = '<tr>';
		$decimal = 0;
		if ($label == "Total") {
			$negrita1 = ' style="color:blue; font-weight:bold"">';
			$negrita2 = '';
		} else {
			$negrita1 = '>';
			$negrita2 = '';
		} 
		$result .= '<td align="left" style="font-weight:bold">'. htmlentities($array['producto']) . '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['inicial'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['compras'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['ventas'], 2, '.', ',')) .$negrita2. '</td>';

		/* Si no es el total ni GLP hallamos el porcentaje de c/producto con respecto al total */
		if ($label != "Total" && $label != "11620307") {
			$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['porcentaje']/$totalventa, 2, '.', ',')) .$negrita2. '</td>';
		} else {
			$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['porcentaje'], 0, '.', ',')) .$negrita2. '</td>';
		}

		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['transfe'] , 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['final'] + $array['transfe'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['medicion'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['dia'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['mes'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right" style="font-weight:bold">' . htmlentities(number_format($array['importe'], 2, '.', ',')) . '</td>';
		$result .= '</tr>';
		return $result;
	    }
    
    	
}
