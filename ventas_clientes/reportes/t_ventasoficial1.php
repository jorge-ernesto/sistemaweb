<?php

class VentasOficialTemplate extends Template
{
    function formSearch(){
		global $usuario;
		$estaciones = VentasOficialModel::obtenerEstaciones();
		$estaciones['TODAS'] = 'Todas';
		$form = new form2('Registro de Ventas Oficial', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return verificar_reporte();"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.VENTASOFICIAL"));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('hoy', date('d/m/Y')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" width="500px" ><tr><td valign="center">Estacion:</td><td colspan="2">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('estacion', '', 'TODAS', $estaciones, ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="center">Buscar :</td><td width="130px">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('desde', '', date('d/m/Y'), '', 12, 10,array(),array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('-</td><td> '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('hasta', '', date('d/m/Y'), '', 12, 10,array(),array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="center">Modo:</td><td>Resumido'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio('modo', '', 'RESUMIDO', '',array(),array('checked')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(' &nbsp;</td><td>Detallado'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio('modo', '', 'DETALLADO', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td >Facturas:</td><td valign="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('facturas', '', 'AMBAS', array('AMBAS'=>'AMBAS','S'=>'DE CREDITO','N'=>'DE CONTADO'), '',array('onChange'=>'Mostrar_Descontar(this.value);')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;</td><td>Descontar vales'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('descontar', '', 'S', ''));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="center">Impresion: </td><td colspan="2">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('impresion', '', date("d/m/Y"), '', 12, 10,array(),array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.impresion'".');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action', 'Reporte', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		return $form->getForm();
    }
    
    function listado($results, $desde, $hasta, $almacen, $modo, $tipo_cliente, $descontar, $impresion) {
    	//print_r($results);
		$estaciones = VentasOficialModel::obtenerEstaciones();
		$result = '';
		
		$result .= '<form name="imprimir" action="control.php" target="_blank" method="post">';
		$result .= '<input type="hidden" name="rqst" value="REPORTES.VENTASOFICIAL">';
		$result .= '<input type="hidden" name="action" value="pdf">';
		$result .= '<input type="hidden" name="desde" value="' . htmlentities($desde) . '">';
		$result .= '<input type="hidden" name="hasta" value="' . htmlentities($hasta) . '">';
		$result .= '<input type="hidden" name="estacion" value="' . htmlentities($almacen) . '">';
		$result .= '<input type="hidden" name="modo" value="' . htmlentities($modo) . '">';
		$result .= '<input type="hidden" name="cli_tipo" value="' . htmlentities($tipo_cliente) . '">';
		$result .= '<input type="hidden" name="descontar" value="' . htmlentities($descontar) . '">';
		$result .= '<input type="hidden" name="impresion" value="' . htmlentities($impresion) . '">';
		$result .= '</form>';
	
		$result .= '<a href="javascript:imprimir.submit()">Imprimir</a>';

		/*---------------------> Primera Parte: venta de combustibles <----------------- */
		$result .= '<table border="1">';
		$result .= '<tr>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td colspan="2">97</td>';
		$result .= '<td colspan="2">84</td>';
		$result .= '<td colspan="2">95</td>';
		$result .= '<td colspan="2">D2</td>';
		$result .= '<td colspan="2">KD</td>';
		$result .= '<td colspan="2">90</td>';
		$result .= '<td colspan="2">TOTAL COMBUSTIBLE</td>';
		$result .= '<td colspan="2">GLP</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '</tr><tr>';
		$result .= '<td>Dia</td>';
		$result .= '<td>Galones</td>';
		$result .= '<td>Importe</td>';
		$result .= '<td>Galones</td>';
		$result .= '<td>Importe</td>';
		$result .= '<td>Galones</td>';
		$result .= '<td>Importe</td>';
		$result .= '<td>Galones</td>';
		$result .= '<td>Importe</td>';
		$result .= '<td>Galones</td>';
		$result .= '<td>Importe</td>';
		$result .= '<td>Galones</td>';
		$result .= '<td>Importe</td>';
		$result .= '<td>Galones</td>';
		$result .= '<td>Importe</td>';
		$result .= '<td>Litros</td>';
		$result .= '<td>Importe</td>';
		$result .= '<td>Neto</td>';
		$result .= '</tr>';
	foreach($results['combustibles']['estaciones'] as $estacion=>$dias) {
	    $result .= '<tr>';
	    $result .= '<td colspan="18"><b>Centro de Costo: ' . htmlentities($estaciones[$estacion]) . '</b></td>';
	    $result .= '</tr>';
	    if ($modo == "DETALLADO") {
			foreach($dias['dias'] as $fecha=>$dia) {
			    $detalle = $dia['detalle'];
			    $result .= '<tr>';
			    $result .= '<td colspan="18">&nbsp;' . $dia['tickets'] . '</td>';
			    $result .= '</tr>';
			    $result .= '<tr>';
			    $result .= '<td>' . htmlentities($detalle['fecha']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620303_cantidad']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620303_importe']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620301_cantidad']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620301_importe']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620305_cantidad']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620305_importe']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620304_cantidad']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620304_importe']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620306_cantidad']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620306_importe']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620302_cantidad']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620302_importe']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['total_cantidad']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['total_importe']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620307_cantidad']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['11620307_importe']) . '</td>';
			    $result .= '<td align="right">' . htmlentities($detalle['neto']) . '</td>';
			    $result .= '</tr>';
			}
	    }	
	    $total = $dias['totales'];
	    $result .= '<tr>';
	    $result .= '<td colspan="18"><b>Sub-Total CC ' .  htmlentities($estaciones[$estacion]) . '</b></td>';
	    $result .= '</tr><tr>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620303_cantidad']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620303_importe']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620301_cantidad']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620301_importe']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620305_cantidad']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620305_importe']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620304_cantidad']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620304_importe']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620306_cantidad']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620306_importe']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620302_cantidad']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620302_importe']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['total_cantidad']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['total_importe']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620307_cantidad']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['11620307_importe']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['neto']) . '</td>';
	    $result .= '</tr>';
	}
	$result .= '</table><br><br>';
	/*-----------------------> FIN de primera parte <-----------------------------*/
	
	
	/*-----------------------------> Segunda parte: Venta de market <------------------------*/
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>Dia</td>';
	$result .= '<td>Combustible</td>';
	$result .= '<td>Lubricantes</td>';
	$result .= '<td>Accesorios</td>';
	$result .= '<td>Servicios</td>';
	$result .= '<td>Market</td>';
	$result .= '<td>Whiz</td>';
	$result .= '<td>O.B.</td>';
	$result .= '<td>Anticipos</td>';
	$result .= '<td>Otros</td>';
	$result .= '<td>Neto</td>';
	$result .= '<td>IGV</td>';
	$result .= '<td>Total</td>';
	$result .= '</tr>';
	foreach($results['market']['estaciones'] as $estacion=>$dias) {
	    $result .= '<tr>';
	    $result .= '<td colspan="13"><b>Centro de Costo: ' . htmlentities($estaciones[$estacion]) . '</b></td>';
	    $result .= '</tr>';
	    if ($modo == "DETALLADO") {
		foreach($dias['dias'] as $fecha=>$dia) {
	    	    $detalle = $dia['detalle'];
	    	    $result .= '<tr>';
		    $result .= '<td>' . htmlentities($detalle['fecha']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['combustibles']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['lubricantes']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['accesorios']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['servicios']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['market']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['whiz']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['ob']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['anticipos']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['otros']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['neto']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['impuestos']) . '</td>';
		    $result .= '<td align="right">' . htmlentities($detalle['total']) . '</td>';
		    $result .= '</tr>';
		}
	    }
	    $total = $dias['totales'];
	    $result .= '<tr>';
	    $result .= '<td colspan="13"><b>Sub-Total CC ' . htmlentities($estaciones[$estacion]) . '</b></td>';
	    $result .= '</tr><tr>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td align="right">' . htmlentities($total['combustibles']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['lubricantes']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['accesorios']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['servicios']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['market']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['whiz']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['ob']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['anticipos']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['otros']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['neto']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['impuestos']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['total']) . '</td>';
	    $result .= '</tr>';
	}

	$result .= '</table><br><br>';
	/*-----------------------> FIN de segunda parte <-------------------------------*/
	
	/*-----------------> Tercera parte: documentos manuales <--------------------*/
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>Fecha</td>';
	$result .= '<td>TD</td>';
	$result .= '<td>Documento</td>';
	$result .= '<td>RUC</td>';
	$result .= '<td>Razon Social</td>';
	$result .= '<td>T.Camb</td>';
	$result .= '<td>Valor Neto</td>';
	$result .= '<td>Impuestos</td>';
	$result .= '<td>Total Venta</td>';
	$result .= '</tr>';
	
	if ($modo == "DETALLADO") {
	foreach($results['documentos']['tipos'] as $td=>$paginas) {
		if ($td=='01'){
		    foreach($paginas['paginas'] as $pagina=>$documentos) {
				foreach($documentos['documentos'] as $numero=>$documento) {
				    $result .= '<tr>';
				    $result .= '<td>' . htmlentities($documento['fecha']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['td']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['documento']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['ruc']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['razsocial']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['tc']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['neto']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['impuestos']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['total']) . '</td>';
				    $result .= '</tr>';
				}
		    }
		}
	}
	}
		$total = $results['documentos']['tipos']['01']['totales'];
	    $result .= '<tr>';
	    $result .= '<td colspan="13"><b>Sub-Total Factura Manual </b></td>';
	    $result .= '</tr><tr>';
	    $result .= '<td colspan="6">&nbsp;</td>';
	    $result .= '<td align="right">' . htmlentities($total['neto']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['impuestos']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['total']) . '</td>';
	    $result .= '</tr>';
	

	$result .= '</table><br><br>';
	/*--------------------> Fin de tercera parte <-----------------------*/
	
/*Boleta*/
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>Fecha</td>';
	$result .= '<td>TD</td>';
	$result .= '<td>Documento</td>';
	$result .= '<td>RUC</td>';
	$result .= '<td>Razon Social</td>';
	$result .= '<td>T.Camb</td>';
	$result .= '<td>Valor Neto</td>';
	$result .= '<td>Impuestos</td>';
	$result .= '<td>Total Venta</td>';
	$result .= '</tr>';
	
	if ($modo == "DETALLADO") {
	foreach($results['documentos']['tipos'] as $td=>$paginas) {
		if ($td=='03'){
		    foreach($paginas['paginas'] as $pagina=>$documentos) {
				foreach($documentos['documentos'] as $numero=>$documento) {
				    $result .= '<tr>';
				    $result .= '<td>' . htmlentities($documento['fecha']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['td']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['documento']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['ruc']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['razsocial']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['tc']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['neto']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['impuestos']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['total']) . '</td>';
				    $result .= '</tr>';
				}
		    }
		}
	}
	}
	
		$total = $results['documentos']['tipos']['03']['totales'];
	    $result .= '<tr>';
	    $result .= '<td colspan="13"><b>Sub-Total B/Venta </b></td>';
	    $result .= '</tr><tr>';
	    $result .= '<td colspan="6">&nbsp;</td>';
	    $result .= '<td align="right">' . htmlentities($total['neto']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['impuestos']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['total']) . '</td>';
	    $result .= '</tr>';
	

	$result .= '</table><br><br>';
	/*fin de boleta*/
	
	/*inicio de nota de credito*/
	
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>Fecha</td>';
	$result .= '<td>TD</td>';
	$result .= '<td>Documento</td>';
	$result .= '<td>RUC</td>';
	$result .= '<td>Razon Social</td>';
	$result .= '<td>T.Camb</td>';
	$result .= '<td>Valor Neto</td>';
	$result .= '<td>Impuestos</td>';
	$result .= '<td>Total Venta</td>';
	$result .= '</tr>';
	
	if ($modo == "DETALLADO") {
	    foreach($results['documentos']['tipos'] as $td=>$paginas) {
		if ($td=='07'){
		    foreach($paginas['paginas'] as $pagina=>$documentos) {
				foreach($documentos['documentos'] as $numero=>$documento) {
				    $result .= '<tr>';
				    $result .= '<td>' . htmlentities($documento['fecha']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['td']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['documento']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['ruc']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['razsocial']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['tc']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['neto']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['impuestos']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['total']) . '</td>';
				    $result .= '</tr>';
				}
		    }
		}
	    }
	}
	
		$total = $results['documentos']['tipos']['07']['totales'];
	    $result .= '<tr>';
	    $result .= '<td colspan="13"><b>Sub-Total N/Credito </b></td>';
	    $result .= '</tr><tr>';
	    $result .= '<td colspan="6">&nbsp;</td>';
	    $result .= '<td align="right">' . htmlentities($total['neto']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['impuestos']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['total']) . '</td>';
	    $result .= '</tr>';
	
	/*fin de nota de credito*/
	
	/*inicio nota de debito*/
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>Fecha</td>';
	$result .= '<td>TD</td>';
	$result .= '<td>Documento</td>';
	$result .= '<td>RUC</td>';
	$result .= '<td>Razon Social</td>';
	$result .= '<td>T.Camb</td>';
	$result .= '<td>Valor Neto</td>';
	$result .= '<td>Impuestos</td>';
	$result .= '<td>Total Venta</td>';
	$result .= '</tr>';
	if ($modo == "DETALLE") {
    	    foreach($results['documentos']['tipos'] as $td=>$paginas) {
    		    if ($td=='08'){
			foreach($paginas['paginas'] as $pagina=>$documentos) {
				foreach($documentos['documentos'] as $numero=>$documento) {
				    $result .= '<tr>';
				    $result .= '<td>' . htmlentities($documento['fecha']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['td']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['documento']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['ruc']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['razsocial']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['tc']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['neto']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['impuestos']) . '</td>';
				    $result .= '<td>' . htmlentities($documento['total']) . '</td>';
				    $result .= '</tr>';
				}
			}
		    }
	    }
	}
	
	$total = $results['documentos']['tipos']['08']['totales'];
	    $result .= '<tr>';
	    $result .= '<td colspan="13"><b>Sub-Total N/Debito </b></td>';
	    $result .= '</tr><tr>';
	    $result .= '<td colspan="6">&nbsp;</td>';
	    $result .= '<td align="right">' . htmlentities($total['neto']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['impuestos']) . '</td>';
	    $result .= '<td align="right">' . htmlentities($total['total']) . '</td>';
	    $result .= '</tr>';
	/*fin nota de debito*/
	
	/*---------------> Cuerta parte: Resumen <---------------------*/
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>Base Imponible</td>';
	$result .= '<td>Impuestos</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Ventas</td>';
	$result .= '<td>' . htmlentities(round($results['market']['totales']['neto'], 2)) . '</td>';
	$result .= '<td>' . htmlentities($results['market']['totales']['impuestos']) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Factura</td>';
	$result .= '<td>' . htmlentities($results['documentos']['tipos']['01']['totales']['neto']) . '</td>';
	$result .= '<td>' . htmlentities($results['documentos']['tipos']['01']['totales']['impuestos']) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Boleta de Venta</td>';
	$result .= '<td>' . htmlentities($results['documentos']['tipos']['03']['totales']['neto']) . '</td>';
	$result .= '<td>' . htmlentities($results['documentos']['tipos']['03']['totales']['impuestos']) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Nota de Debito</td>';
	$result .= '<td>' . htmlentities($results['documentos']['tipos']['08']['totales']['neto']) . '</td>';
	$result .= '<td>' . htmlentities($results['documentos']['tipos']['08']['totales']['impuestos']) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Nota de Credito</td>';
	$result .= '<td>' . htmlentities($results['documentos']['tipos']['07']['totales']['neto']) . '</td>';
	$result .= '<td>' . htmlentities($results['documentos']['tipos']['07']['totales']['impuestos']) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td><b>Total General</b></td>';
	$result .= '<td>' . htmlentities(round($results['totales']['neto'], 2)) . '</td>';
	$result .= '<td>' . htmlentities($results['totales']['impuestos']) . '</td>';
	$result .= '</tr>';
	
	$result .= '</table><br><br>';
	
	/*--------------------------> Fin de cuarta parte <------------------------*/

	/*------------------------> Quinta parte: detalle de tickets factura <----------------------*/
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>Fecha</td>';
	$result .= '<td>Tipo Doc.</td>';
	$result .= '<td>Serie</td>';
	$result .= '<td>Numero</td>';
	$result .= '<td>RUC</td>';
	$result .= '<td>Cliente</td>';
	$result .= '<td>Base Imponible</td>';
	$result .= '<td>IGV</td>';
	$result .= '<td>Importe</td>';
	$result .= '</tr>';
	
	foreach ($results['tickets_factura']['estaciones'] as $estacion=>$lineas) {
	    $result .= '<tr>';
	    $result .= '<td colspan="9">Centro de Costo: ' . htmlentities($estaciones[$estacion]) . '</td>';
	    $result .= '</tr>';
	    
	    if ($modo == "DETALLADO") {
		foreach($lineas['detalle'] as $i=>$linea) {
		    $result .= '<tr>';
		    $result .= '<td>' . htmlentities($linea['fecha']) . '</td>';
		    $result .= '<td>' . htmlentities($linea['td']) . '</td>';
		    $result .= '<td>' . htmlentities($linea['caja']) . '</td>';
		    $result .= '<td>' . htmlentities($linea['trans']) . '</td>';
		    $result .= '<td>' . htmlentities($linea['ruc']) . '</td>';
		    $result .= '<td>' . htmlentities($linea['razsocial']) . '</td>';
		    $result .= '<td>' . htmlentities($linea['vventa']) . '</td>';
		    $result .= '<td>' . htmlentities($linea['igv']) . '</td>';
		    $result .= '<td>' . htmlentities($linea['importe']) . '</td>';
		    $result .= '</tr>';
		}
	    }
	    $result .= '<tr>';
	    $result .= '<td colspan="6">Total Centro de Costo: ' . htmlentities($estaciones[$estacion]) . '</td>';
	    $result .= '<td>' . htmlentities($lineas['total_vventa']) . '</td>';
	    $result .= '<td>' . htmlentities($lineas['total_igv']) . '</td>';
	    $result .= '<td>' . htmlentities($lineas['total_importe']) . '</td>';
	    $result .= '</tr>';
	}
	
	$result .= '<tr>';
	$result .= '<td colspan="6">Total Tickets Factura</td>';
	$result .= '<td>' . htmlentities($results['tickets_factura']['total_vventa']) . '</td>';
	$result .= '<td>' . htmlentities($results['tickets_factura']['total_igv']) . '</td>';
	$result .= '<td>' . htmlentities($results['tickets_factura']['total_importe']) . '</td>';
	$result .= '</tr>';
	
	$result .= '</table>';

	/***************************> Fin de quinta parte <*************************/
	
	/*-----------------------------> Quinta parte: facturacion de oficina <-----------------*/
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td>Fecha</td>';
	$result .= '<td>TD</td>';
	$result .= '<td>Documento</td>';
	$result .= '<td>RUC</td>';
	$result .= '<td>Razon Social</td>';
	$result .= '<td>T.Camb.</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>Valor Neto</td>';
	$result .= '<td>Impuestos</td>';
	$result .= '<td>Total Venta</td>';
	$result .= '</tr>';
	
	foreach($results['oficina']['paginas'] as $pagina=>$documentos) {
	    foreach($documentos['documentos'] as $numero=>$documento) {
		$result .= '<tr>';
	        $result .= '<td>' . htmlentities($documento['fecha']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['td']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['documento']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['ruc']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['razsocial']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['tc']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['neto']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['descuento']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['neto_real']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['impuestos']) . '</td>';
	        $result .= '<td>' . htmlentities($documento['total']) . '</td>';
	        $result .= '</tr>';
	    }
	}
	$result .= '</table>';
	return $result;
    }

    function reportePDF($results, $desde, $hasta, $modo, $fecha_impresion)
    {
	$estaciones = VentasOficialModel::obtenerEstaciones();

	$cab_comb1 = Array(
	    "97"			=>	"97 Octanos",
	    "84"			=>	"84 Octanos",
	    "95"			=>	"95 Octanos",
	    "D2"			=>	"Diesel 2",
	    "KD"			=>	"Kerosene",
	    "90"			=>	"90 Octanos",
	    "total"			=>	"Total Combustible",
	    "glp"			=>	"GLP"
	);

	$cab_comb2 = Array(
	    "fecha"			=>	"Dia",
	    "11620303_cantidad"		=>	"Cantidad",
	    "11620303_importe"		=>	"Importe",
	    "11620301_cantidad"		=>	"Cantidad",
	    "11620301_importe"		=>	"Importe",
	    "11620305_cantidad"		=>	"Cantidad",
	    "11620305_importe"		=>	"Importe",
	    "11620304_cantidad"		=>	"Cantidad",
	    "11620304_importe"		=>	"Importe",
	    "11620306_cantidad"		=>	"Cantidad",
	    "11620306_importe"		=>	"Importe",
	    "11620302_cantidad"		=>	"Cantidad",
	    "11620302_importe"		=>	"Importe",
	    "total_cantidad"		=>	"Cantidad",
	    "total_importe"		=>	"Importe",
	    "11620307_cantidad"		=>	"Cantidad",
	    "11620307_importe"		=>	"Importe",
	    "neto"			=>	"Neto"
	);

	$cab_market = array(
	    "fecha"			=>	"Dia",
	    "combustibles"		=>	"Combustibles",
	    "lubricantes"		=>	"Lubricantes",
	    "accesorios"		=>	"Accesorios",
	    "servicios"			=>	"Servicios",
	    "market"			=>	"Market",
	    "whiz"			=>	"Whiz",
	    "ob"			=>	"O.B.",
	    "anticipos"			=>	"Anticipos",
	    "otros"			=>	"Otros",
	    "neto"			=>	"Neto",
	    "impuestos"			=>	"IGV",
	    "total"			=>	"Total"
	);

	$cab_documentos = array(
	    "fecha"			=>	"Fecha",
	    "td"			=>	"TD",
	    "documento"			=>	"Documento",
	    "ruc"			=>	"RUC",
	    "razsocial"			=>	"Razon Social",
	    "tc"			=>	"T.C.",
	    "neto"			=>	"V. Neto",
	    "impuestos"			=>	"Impuesto",
	    "total"			=>	"Total"
	);
	
	$cab_facturas = array(
	    "fecha"			=>	"Fecha",
	    "td"			=>	"TD",
	    "documento"			=>	"Documento",
	    "ruc"			=>	"RUC",
	    "razsocial"			=>	"Razon Social",
	    "tc"			=>	"T.C.",
	    "totaldescuento"	=> "",
	    "descuento"		=> "",
	    "neto"			=>	"V. Neto",
	    "impuestos"			=>	"Impuesto",
	    "total"			=>	"Total"
	);

	$cab_resumen = array(
	    "rotulo"			=>	'',
	    "neto"			=>	"Base Imponible",
	    "impuestos"			=>	"Impuesto"
	);

	$cab_tktfactura = array(
	    "fecha"			=>	"Fecha",
	    "td"			=>	"Tipo Doc.",
	    "caja"			=>	"Ser.",
	    "trans"			=>	"Num.",
	    "ruc"			=>	"RUC",
	    "razsocial"			=>	"Cliente",
	    "vventa"			=>	"Base Imp.",
	    "igv"			=>	"IGV",
	    "importe"			=>	"Importe"
	);

	$reporte = new CReportes2("L","pt","A3");
	$reporte->definirCabecera(2, "L", "EESS");
	$reporte->definirCabecera(2, "R", "Pagina %p");
	$reporte->definirCabecera(3, "C", "DEL " . $desde . " AL " . $hasta);
	$reporte->definirCabecera(4, "C", " ");
	$reporte->definirColumna("dummy", $reporte->TIPO_TEXTO, 7, "L", "_combustibles");
	$reporte->definirColumna("97", $reporte->TIPO_TEXTO, 19, "C", "_combustibles");
	$reporte->definirColumna("84", $reporte->TIPO_TEXTO, 24, "C", "_combustibles");
	$reporte->definirColumna("95", $reporte->TIPO_TEXTO, 23, "C", "_combustibles");
	$reporte->definirColumna("D2", $reporte->TIPO_TEXTO, 22, "C", "_combustibles");
	$reporte->definirColumna("KD", $reporte->TIPO_TEXTO, 21, "C", "_combustibles");
	$reporte->definirColumna("90", $reporte->TIPO_TEXTO, 21, "C", "_combustibles");
	$reporte->definirColumna("total", $reporte->TIPO_TEXTO, 22, "C", "_combustibles");
	$reporte->definirColumna("glp", $reporte->TIPO_TEXTO, 23, "C", "_combustibles");
	$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 5, "L");
	$reporte->definirColumna("11620303_cantidad", $reporte->TIPO_IMPORTE, 9, "R");
	$reporte->definirColumna("11620303_importe", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("11620301_cantidad", $reporte->TIPO_IMPORTE, 9, "R");
	$reporte->definirColumna("11620301_importe", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("11620305_cantidad", $reporte->TIPO_IMPORTE, 9, "R");
	$reporte->definirColumna("11620305_importe", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("11620304_cantidad", $reporte->TIPO_IMPORTE, 9, "R");
	$reporte->definirColumna("11620304_importe", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("11620306_cantidad", $reporte->TIPO_IMPORTE, 9, "R");
	$reporte->definirColumna("11620306_importe", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("11620302_cantidad", $reporte->TIPO_IMPORTE, 9, "R");
	$reporte->definirColumna("11620302_importe", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("total_cantidad", $reporte->TIPO_IMPORTE, 9, "R");
	$reporte->definirColumna("total_importe", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("11620307_cantidad", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("11620307_importe", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 5, "L", "_market");
	$reporte->definirColumna("combustibles", $reporte->TIPO_IMPORTE, 14, "R", "_market");
	$reporte->definirColumna("lubricantes", $reporte->TIPO_IMPORTE, 11, "R", "_market");
	$reporte->definirColumna("accesorios", $reporte->TIPO_IMPORTE, 10, "R", "_market");
	$reporte->definirColumna("servicios", $reporte->TIPO_IMPORTE, 10, "R", "_market");
	$reporte->definirColumna("market", $reporte->TIPO_IMPORTE, 10, "R", "_market");
	$reporte->definirColumna("whiz", $reporte->TIPO_IMPORTE, 10, "R", "_market");
	$reporte->definirColumna("ob", $reporte->TIPO_IMPORTE, 10, "R", "_market");
	$reporte->definirColumna("anticipos", $reporte->TIPO_IMPORTE, 10, "R", "_market");
	$reporte->definirColumna("otros", $reporte->TIPO_IMPORTE, 10, "R", "_market");
	$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 13, "R", "_market");
	$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 12, "R", "_market");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 13, "R", "_market");
	$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 10, "L", "_documentos");
	$reporte->definirColumna("td", $reporte->TIPO_TEXTO, 2, "L", "_documentos");
	$reporte->definirColumna("documento", $reporte->TIPO_TEXTO, 10, "L", "_documentos");
	$reporte->definirColumna("ruc", $reporte->TIPO_TEXTO, 11, "L", "_documentos");
	$reporte->definirColumna("razsocial", $reporte->TIPO_TEXTO, 40, "L", "_documentos");
	$reporte->definirColumna("tc", $reporte->TIPO_CANTIDAD, 5, "R", "_documentos");
	$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 11, "R", "_documentos");
	$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 11, "R", "_documentos");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 11, "R", "_documentos");
	
	$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 10, "L", "_facturas");
	$reporte->definirColumna("td", $reporte->TIPO_TEXTO, 2, "L", "_facturas");
	$reporte->definirColumna("documento", $reporte->TIPO_TEXTO, 10, "L", "_facturas");
	$reporte->definirColumna("ruc", $reporte->TIPO_TEXTO, 11, "L", "_facturas");
	$reporte->definirColumna("razsocial", $reporte->TIPO_TEXTO, 40, "L", "_facturas");
	$reporte->definirColumna("tc", $reporte->TIPO_CANTIDAD, 5, "R", "_facturas");
	$reporte->definirColumna("totaldescuento", $reporte->TIPO_IMPORTE, 10, "R", "_facturas");
	$reporte->definirColumna("descuento", $reporte->TIPO_IMPORTE, 10, "R", "_facturas");
	$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 11, "R", "_facturas");
	$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 11, "R", "_facturas");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 11, "R", "_facturas");
	
	
	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 83, "L", "_rot_documentos");
	$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 11, "R", "_rot_documentos");
	$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 11, "R", "_rot_documentos");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 11, "R", "_rot_documentos");
	
	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 83, "L", "_rot_facturas");
	$reporte->definirColumna("totaldescuento", $reporte->TIPO_IMPORTE, 10, "R", "_rot_facturas");
	$reporte->definirColumna("descuento", $reporte->TIPO_IMPORTE, 10, "R", "_rot_facturas");
	$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 11, "R", "_rot_facturas");
	$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 11, "R", "_rot_facturas");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 11, "R", "_rot_facturas");
	
	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 25, "L", "_resumen");
	$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 15, "R", "_resumen");
	$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 15, "R", "_resumen");
	$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 10, "L", "_anexo");
	$reporte->definirColumna("td", $reporte->TIPO_TEXTO, 2, "L", "_anexo");
	$reporte->definirColumna("documento", $reporte->TIPO_TEXTO, 10, "L", "_anexo");
	$reporte->definirColumna("ruc", $reporte->TIPO_TEXTO, 11, "L", "_anexo");
	$reporte->definirColumna("razsocial", $reporte->TIPO_TEXTO, 40, "L", "_anexo");
	$reporte->definirColumna("tc", $reporte->TIPO_CANTIDAD, 5, "R", "_anexo");
	$reporte->definirColumna("neto", $reporte->TIPO_IMPORTE, 11, "R", "_anexo");
	$reporte->definirColumna("descuento", $reporte->TIPO_IMPORTE, 11, "R", "_anexo");
	$reporte->definirColumna("neto_real", $reporte->TIPO_IMPORTE, 11, "R", "_anexo");
	$reporte->definirColumna("impuestos", $reporte->TIPO_IMPORTE, 11, "R", "_anexo");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 11, "R", "_anexo");
	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 120, "L", "_rotulo", "B");

	$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 10, "L", "_anexo_tkt");
	$reporte->definirColumna("td", $reporte->TIPO_TEXTO, 2, "L", "_anexo_tkt");
	$reporte->definirColumna("caja", $reporte->TIPO_TEXTO, 2, "L", "_anexo_tkt");
	$reporte->definirColumna("trans", $reporte->TIPO_TEXTO, 4, "L", "_anexo_tkt");
	$reporte->definirColumna("ruc", $reporte->TIPO_TEXTO, 11, "L", "_anexo_tkt");
	$reporte->definirColumna("razsocial", $reporte->TIPO_TEXTO, 40, "L", "_anexo_tkt");
	$reporte->definirColumna("vventa", $reporte->TIPO_IMPORTE, 11, "R", "_anexo_tkt");
	$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 11, "R", "_anexo_tkt");
	$reporte->definirColumna("importe", $reporte->TIPO_IMPORTE, 11, "R", "_anexo_tkt");

	$reporte->SetMargins(10,10,10);
	$reporte->SetFont("courier", "", 9.5);

	/************************> Ventas de Combustibles <***************************/
	$reporte->definirCabeceraPredeterminada($cab_comb1, "_combustibles");
	$reporte->definirCabeceraPredeterminada($cab_comb2);
	if ($modo != "DETALLADO") {
	    $reporte->definirCabecera(2, "C", "Registro de Ventas");
	    $reporte->AddPage();
	}

	foreach($results['combustibles']['estaciones'] as $estacion=>$dias) {
	    if ($modo == "DETALLADO") {
			$reporte->definirCabecera(2, "C", "Registro de Ventas - Centro de Costo: " . $estaciones[$estacion]);
			$reporte->AddPage();
	    }
	    $reporte->nuevaFila(array("rotulo"=>"Centro de Costo: " . $estaciones[$estacion]), "_rotulo");
	    if ($modo == "DETALLADO") {
		    foreach($dias['dias'] as $fecha=>$dia) {
				$result .= '<tr>';
				$result .= '<td colspan="18">&nbsp;' . $dia['tickets'] . '</td>';
				$result .= '</tr>';
				$reporte->nuevaFila(array("rotulo"=>$dia['tickets']), "_rotulo");
				$reporte->nuevaFila($dia['detalle']);
		    }
	    }
	    $reporte->nuevaFila(array("rotulo"=>"SubTotal Centro de Costo: " . $estaciones[$estacion]), "_rotulo");
	    $reporte->nuevaFila($dias['totales']);
	    $reporte->Ln();
	}
	
	for ($i = 0; $i < 2; $i++) $reporte->Ln();
		$reporte->lineaH();
		$reporte->nuevaFila(array("rotulo"=>"Total Venta:"), "_rotulo");
		$reporte->nuevaFila($results['combustibles']['totales']);
		$reporte->lineaH();
	
		/**************************> Ventas de Market <****************************/
		$reporte->borrarCabeceraPredeterminada();	
		$reporte->definirCabeceraPredeterminada($cab_market, "_market");
		$reporte->SetFont("courier", "", 9.5);

		if ($modo != "DETALLADO") $reporte->AddPage();

		foreach($results['market']['estaciones'] as $estacion=>$dias) {
	    	if ($modo == "DETALLADO") {
				$reporte->definirCabecera(2, "C", "Registro de Ventas - Centro de Costo: " . $estaciones[$estacion]);
				$reporte->AddPage();
				foreach($dias['dias'] as $fecha=>$dia) {
					$reporte->nuevaFila($dia['detalle'], "_market");
	    		}
	    	}
	    	
	    	$reporte->nuevaFila(array("rotulo"=>"SubTotal Centro de Costo: " . $estaciones[$estacion]), "_rotulo");
	    	$reporte->nuevaFila($dias['totales'], "_market");
	    	$reporte->Ln();
		}
		
		for ($i = 0; $i < 5; $i++) $reporte->Ln();
		$reporte->lineaH();
		$reporte->nuevaFila(array("rotulo"=>"Total Venta:"), "_rotulo");
		$reporte->nuevaFila($results['market']['totales'], "_market");
		$reporte->lineaH();
		/***************> Listado de Documentos <********************/
	
		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab_documentos, "_documentos");

		$reporte->definirCabecera(2, "C", "Registro de Ventas del " . $desde . " al " . $hasta);
		$reporte->definirCabecera(3, "L", "");
		$reporte->definirCabecera(3, "C", "( En S/. )");
		
		if ($modo == "RESUMIDO"){
			$reporte->AddPage();
		}
	foreach($results['documentos']['tipos'] as $td=>$paginas) {
		
			$contador = count($paginas['paginas']);
		    foreach($paginas['paginas'] as $pagina=>$documentos) {
		    	$contador--;
				if($modo=='DETALLADO'){
					$reporte->AddPage();
					foreach($documentos['documentos'] as $numero=>$documento) {
				    	$reporte->nuevaFila($documento, "_documentos");
					}	
				}
				
				$reporte->lineaH();
				switch ($td){
					case '01': $auxi = "FACTURAS"; break;
					case '03': $auxi = "BOLETAS"; break;
					case '07': $auxi = "NOTAS DE CREDITO"; break;
					case '08': $auxi = "NOTAS DE DEBITO"; break;
				}
				
				if ($modo=='DETALLADO'){
					$documentos['totales']['rotulo'] = "     Total Pagina - " . $auxi;
					$reporte->nuevaFila($documentos['totales'], "_rot_documentos");
					$reporte->lineaH();
					$documentos['acumulados']['rotulo'] = "  Total ".($contador==0?'Documento':'Acumulado')." - " . $auxi;
					$reporte->nuevaFila($documentos['acumulados'], "_rot_documentos");
					$reporte->lineaH();
				}else{
					if ($contador==0){
						$documentos['acumulados']['rotulo'] = "  Total Documento - " . $auxi;
						$reporte->nuevaFila($documentos['acumulados'], "_rot_documentos");
						$reporte->lineaH();
					}
				}
			}
	}
	for ($i = 0; $i < 5; $i++) $reporte->Ln();
	$reporte->lineaH();
	$reporte->nuevaFila(array("rotulo"=>"Total General:"), "_rotulo");
	$reporte->nuevaFila($results['documentos']['totales'], "_market");
	$reporte->lineaH();

	
	
	
	
	/***************> Resumen <*************/
	$reporte->borrarCabecera();
	$reporte->borrarCabeceraPredeterminada();
	$reporte->definirCabecera(1, "L", "Resumen de Impuestos");
	$reporte->definirCabeceraPredeterminada($cab_resumen, "_resumen");
	$reporte->AddPage();
		
	$results['market']['totales']['rotulo'] = "Venta";
	$totalfinalneto = $results['market']['totales']['neto'];
	$totalfinalimpuesto = $results['market']['totales']['impuestos'];
	
	$results['documentos']['tipos']['01']['totales']['rotulo'] = "Facturas";
	$totalfinalneto += $results['documentos']['tipos']['01']['totales']['neto'];
	$totalfinalimpuesto += $results['documentos']['tipos']['01']['totales']['impuestos'];
	$results['documentos']['tipos']['03']['totales']['rotulo'] = "Boleta Venta";
	$totalfinalneto += $results['documentos']['tipos']['03']['totales']['neto'];
	$totalfinalimpuesto += $results['documentos']['tipos']['03']['totales']['impuestos'];
	$results['documentos']['tipos']['08']['totales']['rotulo'] = "Nota Debito";
	$totalfinalneto += $results['documentos']['tipos']['08']['totales']['neto'];
	$totalfinalimpuesto += $results['documentos']['tipos']['08']['totales']['impuestos'];
	$results['documentos']['tipos']['07']['totales']['rotulo'] = "Nota Credito";
	$totalfinalneto -= $results['documentos']['tipos']['07']['totales']['neto'];
	$totalfinalimpuesto -= $results['documentos']['tipos']['07']['totales']['impuestos'];
	//$results['documentos']['totales']['rotulo'] = "Total Documentos";
	$results['totales']['rotulo'] = "Total General";
	$results['totales']['neto'] = $totalfinalneto;
	$results['totales']['impuestos']=$totalfinalimpuesto;
	
	$reporte->nuevaFila($results['market']['totales'], "_resumen");
	$reporte->nuevaFila($results['documentos']['tipos']['01']['totales'], "_resumen");
	$reporte->nuevaFila($results['documentos']['tipos']['03']['totales'], "_resumen");
	$reporte->nuevaFila($results['documentos']['tipos']['08']['totales'], "_resumen");
	$reporte->nuevaFila($results['documentos']['tipos']['07']['totales'], "_resumen");
	//$reporte->nuevaFila($results['documentos']['totales'], "_resumen");
	$reporte->nuevaFila($results['totales'], "_resumen");
	
	/**************facturas de oficina***************/
	$reporte->borrarCabeceraPredeterminada();
	$reporte->definirCabeceraPredeterminada($cab_facturas, "_facturas");
	$reporte->definirCabecera(1, "L", "Oficina Central");
	$reporte->definirCabecera(1, "R", "Pagina %p");
	$reporte->definirCabecera(1, "C", "Registro de Ventas del " . $desde . " al " . $hasta);
	$reporte->definirCabecera(2, "L", "");
	$reporte->definirCabecera(2, "C", "( En S/. )");
	$reporte->definirCabecera(3, "C", " ");
	$contador=0;
	foreach($results['documentos']['facturas'] as $td=>$paginas) {
			$contador = count($paginas['paginas']);
		    foreach($paginas['paginas'] as $pagina=>$documentos) {
		    	$contador--;
		    	if($modo=='DETALLADO'){
					$reporte->AddPage();
					foreach($documentos['documentos'] as $numero=>$documento) {
					    $reporte->nuevaFila($documento, "_facturas");
					}
		    	}
		    	if ($modo=='DETALLADO'){
					$reporte->lineaH();
					$documentos['totales']['rotulo'] = "     Total Pagina - FACTURAS " ;
					$reporte->nuevaFila($documentos['totales'], "_rot_facturas");
					$reporte->lineaH();
					$documentos['acumulados']['rotulo'] = "  Total ".($contador==0?"Documento":"Acumulado")." - FACTURAS " ;
					$reporte->nuevaFila($documentos['acumulados'], "_rot_facturas");
					$reporte->lineaH();
		    	}else{
		    		if ($contador==0){
		    			$reporte->AddPage();
		    			$reporte->lineaH();
						$documentos['acumulados']['rotulo'] = "  Total Documento - FACTURAS " ;
						$reporte->nuevaFila($documentos['acumulados'], "_rot_facturas");
						$reporte->lineaH();
		    		}
		    	}
		    }
	}
	/*fin facturas de oficina*/
	/*facturas de oficina*/
	
	
	/*fin de facturas de oficina*/
	/****************> Anexo <******************/
	/*$reporte->borrarCabecera();
	$reporte->borrarCabeceraPredeterminada();
	$reporte->definirCabeceraPredeterminada($cab_documentos, "_anexo");
	$reporte->definirCabecera(1, "L", "Oficina Central");
	$reporte->definirCabecera(1, "R", "Pagina %p");
	$reporte->definirCabecera(2, "L", $fecha_impresion);
	$reporte->definirCabecera(3, "C", "DEL " . $desde . " AL " . $hasta);
	$reporte->AddPage();

	foreach($results['oficina']['paginas'] as $numero=>$pagina) {
	    foreach($pagina['documentos'] as $n=>$documento) {
			$reporte->nuevaFila($documento, "_anexo");
	    }
	    $reporte->lineaH();
	    $reporte->nuevaFila(array("rotulo"=>"Total Pagina:"), "_rotulo");
	    $reporte->nuevaFila($pagina['totales'], "_anexo");
	    $reporte->lineaH();
	}*/
		/*for ($i = 0; $i < 5; $i++) $reporte->Ln();
		$reporte->lineaH();
		$reporte->nuevaFila(array("rotulo"=>"Total General:"), "_rotulo");
		$reporte->nuevaFila($pagina['totales'], "_anexo");
		$reporte->lineaH();*/
	$reporte->borrarCabeceraPredeterminada();
	$reporte->definirCabeceraPredeterminada($cab_tktfactura, "_anexo_tkt");
	$reporte->definirCabecera(1, "L", "Oficina Central");
	$reporte->definirCabecera(1, "R", "Pagina %p");
	$reporte->definirCabecera(1, "C", "Registro de Ventas del " . $desde . " al " . $hasta);
	$reporte->definirCabecera(2, "L", "");
	$reporte->definirCabecera(2, "C", "( En S/. )");
	$reporte->definirCabecera(3, "C", "Tickets Factura");
	$reporte->AddPage();
	
	foreach($results['tickets_factura']['estaciones'] as $es=>$estacion) {
	    if ($modo == "DETALLADO") {
		foreach($estacion['detalle'] as $i=>$linea) {
		    $reporte->nuevaFila($linea, "_anexo_tkt");
	        }
	    }
	    $arr = array("razsocial"=>"Total CC ".$es, "vventa"=>$estacion['total_vventa'], "igv"=>$estacion['total_igv'], "importe"=>$estacion['total_importe']);
	    $reporte->nuevaFila($arr, "_anexo_tkt");
	    $reporte->Ln();
	}
	$reporte->lineaH();
	$arr = array("razsocial"=>"Total Tickets Factura ", "vventa"=>$results['tickets_factura']['total_vventa'], "igv"=>$results['tickets_factura']['total_igv'], "importe"=>$results['tickets_factura']['total_importe']);
	$reporte->nuevaFila($arr, "_anexo_tkt");
/*
	    "razsocial"			=>	"Cliente",
	    "vventa"			=>	"Base Imp.",
	    "igv"			=>	"IGV",
	    "importe"			=>	"Importe"
*/
	$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/registro_ventas.pdf", "F");
	return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/registro_ventas.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    }
    
}
