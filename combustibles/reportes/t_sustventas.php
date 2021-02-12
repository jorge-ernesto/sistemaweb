<?php

class SustentoVentasTemplate extends Template {
    
	function search_form(){
		$fecha = date(d."/".m."/".Y); 

		$estaciones = SustentoVentasModel::obtieneListaEstaciones();

		$form = new form2("Reporte de Sustento de Ventas", "form_sustento_ventas", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.SUSTENTOVENTAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0"><tr><td colspan="4" align="center">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_sustento_ventas.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>&nbsp;<td/><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_sustento_ventas.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">Tipo: <select name="tipo" id="tipo" class="form_combo">
					<option value="T">Todos</option>
					<option value="GLP">GLP</option>
					<option value="C">Combustible</option>
					<option value="M">Market</option>
				</select>
			</td></tr><tr><td>&nbsp;</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		return $form->getForm();
    }
    
    function reporte($results0,$results1,$results2,$results3,$results4,$results5,$desde, $hasta, $estacion, $tipo) {
	
		$result = '<table border="0" align="center" width="650px"><tr>';
		$result .= '<td  align="right" style="font-weight:bold">';
		$result .= 'T.C. DEL DIA: '.$results0['propiedades']['0'];
		$result .= '</td>';
		$result .= '</tr></table>';

		$result .= '<br/>';
		$result .= '<table align="center">';
		$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		$result .= '<tr>';
		$result .= '<th colspan="2">DESCRIPCI&Oacute;N</th>';
		$result .= '<th colspan="2">DOCUMENTO</th>';
		$result .= '<th colspan="2">OBSERVACI&Oacute;N</th>';
		$result .= '<th>CANTIDAD</th>';
		$result .= '<th>IMPORTE</th></tr>';
		$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">VALES DE CREDITO</td></tr>';

		$numfilas = 0;
		foreach($results1['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
			    $result .= SustentoVentasTemplate::imprimirLinea($venta,4);
	                    $numfilas= $numfilas +1;
		    }
		}
		$result .= '<tr>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
		$result .= '</tr>';

		$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">TARJETAS</td></tr>';

		$numfilas = 0;
		foreach($results2['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
			    $result .= SustentoVentasTemplate::imprimirLinea($venta,2);
	                    $numfilas= $numfilas +1;
		    }
		}
		$result .= '<tr>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
		$result .= '</tr>';

		$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">EFECTIVOS</td></tr>';

		$numfilas = 0;
		foreach($results3['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
			    $result .= SustentoVentasTemplate::imprimirLinea($venta,3);
	                    $numfilas= $numfilas +1;
		    }
		}
		$result .= '<tr>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
		$result .= '</tr>';

		$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">SOBRANTES Y FALTANTES</td></tr>';

		$numfilas = 0;
		foreach($results4['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
			    $result .= SustentoVentasTemplate::imprimirLinea($venta,3);
	                    $numfilas= $numfilas +1;
		    }
		}
		$result .= '<tr>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2" style="font-weight:bold">TOTAL SOBRANTES --></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right" style="font-weight:bold;">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total_Sob'], 2, '.', ',')) .'</td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2" style="font-weight:bold">TOTAL FALTANTES --></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right" style="font-weight:bold;">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total_Fal'], 2, '.', ',')) .'</td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
		$result .= '</tr>';

		$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		
		if ($tipo != 'M') {
			$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">AFERICIONES</td></tr>';

			$numfilas = 0;
			foreach($results5['propiedades'] as $a => $almacenes) {
			    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				    $result .= SustentoVentasTemplate::imprimirLinea($venta,2);
			            $numfilas= $numfilas +1;
			    }
			}
			$result .= '<tr>';
			$result .= '<td colspan="2">&nbsp;</td>';
			$result .= '<td colspan="2">&nbsp;</td>';
			$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
			$result .= '<td>&nbsp;</td>';
			$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
			$result .= '</tr>';

			$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
		}
		$result .= '</table>';
		
		return $result;
    }

    function reporteGLP($results0,$results1,$results2,$results3,$results4,$results5,$desde, $hasta, $estacion) {
	

	$result = '<table border="0" align="center" width="650px"><tr>';
	$result .= '<td  align="right" style="font-weight:bold">';
	$result .= 'T.C. DEL DIA: '.$results0['propiedades']['0'];
	$result .= '</td>';
	$result .= '</tr></table>';

	$result .= '<br/>';
	$result .= '<table align="center">';
	$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
	$result .= '<tr>';
	$result .= '<th colspan="2">DESCRIPCI&Oacute;N</th>';
	$result .= '<th colspan="2">DOCUMENTO</th>';
	$result .= '<th colspan="2">OBSERVACI&Oacute;N</th>';
	$result .= '<th>CANTIDAD</th>';
	$result .= '<th>IMPORTE</th></tr>';
	$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
	$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">VALES DE CREDITO</td></tr>';

	$numfilas = 0;
	foreach($results1['propiedades'] as $a => $almacenes) {
	    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		    $result .= SustentoVentasTemplate::imprimirLinea($venta,4);
                    $numfilas= $numfilas +1;
	    }
	}
	$result .= '<tr>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
	$result .= '</tr>';

	$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
	$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">TARJETAS</td></tr>';

	$numfilas = 0;
	foreach($results2['propiedades'] as $a => $almacenes) {
	    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		    $result .= SustentoVentasTemplate::imprimirLinea($venta,2);
                    $numfilas= $numfilas +1;
	    }
	}
	$result .= '<tr>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
	$result .= '</tr>';

	$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
	$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">EFECTIVOS</td></tr>';

	$numfilas = 0;
	foreach($results3['propiedades'] as $a => $almacenes) {
	    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		    $result .= SustentoVentasTemplate::imprimirLinea($venta,3);
                    $numfilas= $numfilas +1;
	    }
	}
	$result .= '<tr>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
	$result .= '</tr>';

	$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
	$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">SOBRANTES Y FALTANTES</td></tr>';

	$numfilas = 0;
	foreach($results4['propiedades'] as $a => $almacenes) {
	    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		    $result .= SustentoVentasTemplate::imprimirLinea($venta,3);
                    $numfilas= $numfilas +1;
	    }
	}
	$result .= '<tr>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2" style="font-weight:bold">TOTAL SOBRANTES --></td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td align="right" style="font-weight:bold;">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total_Sob'], 2, '.', ',')) .'</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2" style="font-weight:bold">TOTAL FALTANTES --></td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td align="right" style="font-weight:bold;">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total_Fal'], 2, '.', ',')) .'</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2">&nbsp;</td>';
	$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
	$result .= '</tr>';

	$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
	
	if ($tipo != 'M') {
		$result .= '<tr><td align="left" colspan="8" style="font-weight:bold">AFERICIONES</td></tr>';

		$numfilas = 0;
		foreach($results5['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
			    $result .= SustentoVentasTemplate::imprimirLinea($venta,2);
		            $numfilas= $numfilas +1;
		    }
		}
		$result .= '<tr>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2" style="font-weight:bold; color:blue">TOTAL POR TIPO --></td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right" style="font-weight:bold; color:blue">'. htmlentities(number_format($almacenes['almacenes'][$numfilas-1]['total'], 2, '.', ',')) .'</td>';
		$result .= '</tr>';

		$result .= '<tr><td colspan="8">------------------------------------------------------------------------------------------------------------------------------------</td></tr>';
	}
	$result .= '</table>';
	
	return $result;
    }

    function imprimirLinea($array,$num)
    {
	$result  = '<tr>';
	if ($num==2){
		$result .= '<td align="left" colspan="2">'. htmlentities($array[0]) . '</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[1], 2, '.', ',')) .'</td>';
	} else if ($num==3) {
		$result .= '<td align="left" colspan="2">'. htmlentities($array[0]) . '</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">'. htmlentities($array[1]) . '</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[2], 2, '.', ',')) .'</td>';
	} else {
		$result .= '<td align="left" colspan="2">'. htmlentities($array[0]) . '</td>';
		$result .= '<td colspan="2">&nbsp;</td>';
		$result .= '<td colspan="2">'. htmlentities($array[1]) . '</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[2], 2, '.', ',')) .'</td>';
		$result .= '<td align="right">'. htmlentities(number_format($array[3], 2, '.', ',')) .'</td>';
	}
	$result .= '</tr>';
	return $result;
    }
}

