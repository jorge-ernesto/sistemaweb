<?php
class RegistroOficialTemplate extends Template
{

    function formSearch()
    {
	$almacenes = RegistroModel::obtenerListaAlmacenes();
	$documentos = RegistroModel::obtenerListaDocumentos();
	$rubros = RegistroOficialModel::obtenerRubros();

	$almacenes['TODOS'] = "Todos los almacenes";
	$documentos['TODOS'] = "Todos los documentos";
	$rubros['TODOS'] = "Todos los rubros";

	$ayer = time()-(24*60*60);
	$fecha = date("d/m/Y", $ayer);

	$form = new form2("Reporte de Registro de Compras", "form_cpagar", FORM_METHOD_POST, "control.php", '', "control");
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.REGISTROOFICIAL"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("params[desde]", "Desde:", $fecha, espacios(5), 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("params[hasta]", "Hasta:", $fecha, espacios(5), 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("params[proveedor]", "Proveedor:", '', '', 7, 6));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("params[tipo]", "Tipo de Doc.:", "TODOS", $documentos, ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td><table border="0"><tr><td valign="center">Modo:</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("params[modo]", 'Resumido', 'RESUMIDO', '<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("params[modo]", 'Detallado', 'DETALLADO', '<br>', array(), array("checked")));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("params[almacen]", "Almacen:", "TODOS", $almacenes, ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("params[rubro]", "Rubro:", "TODOS", $rubros, ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Buscar"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	return $form->getForm();
    }
    
    function listado($results, $params)
    {
	$result  = '<a href="control.php?' . urlencode("params[desde]") . '=' . urlencode($params['desde']) . '&' . urlencode("params[hasta]") . '=' . urlencode($params['hasta']) . '&' . urlencode("params[proveedor]") . '=' . urlencode($params['proveedor']) . '&' . urlencode("params[tipo]") . '=' . urlencode($params['tipo']) . '&' . urlencode("params[almacen]") . '=' . urlencode($params['almacen']) . '&' . urlencode("params[modo]") . "=" . urlencode($params['modo']) . "&" . urlencode("params[rubro]") . "=" . urlencode($params['rubro']) . '&rqst=REPORTES.REGISTROOFICIAL&action=pdf" target="_blank">Imprimir</a>';
	$result .= '<table border="0" cellspacing="2" cellpadding="3" bgcolor="#FFFFCC">';
	$result .= '<tr bgcolor="#cccc99">';
	$result .= '<td>Fecha Emision</td>';
	$result .= '<td>Tipo</td>';
	$result .= '<td>Serie</td>';
	$result .= '<td>Numero</td>';
	$result .= '<td>RUC</td>';
	$result .= '<td>Proveedor</td>';
	$result .= '<td>Total (USD)</td>';
	$result .= '<td>Inafecto</td>';
	$result .= '<td>Base Destino Gravado</td>';
	$result .= '<td>Base destino compartido</td>';
	$result .= '<td>Base imponible</td>';
	$result .= '<td>IGV Destino Gravado</td>';
	$result .= '<td>IGV Destino Compartido</td>';
	$result .= '<td>Percepcion combustible</td>';
	$result .= '<td>Renta 4ta categoria</td>';
	$result .= '<td>Impuesto Solidaridad</td>';
	$result .= '<td>Total</td>';
	$result .= '<td>Num. Reg.</td>';
	$result .= '</tr>';
	
	foreach($results['rubros'] as $ch_rubro=>$rubro) {
	    $result .= '<tr bgcolor="#cccc99">';
	    $result .= '<td colspan="18"><b>Rubro: ' . htmlentities($ch_rubro) . '</b></td>';
	    $result .= '</tr>';
	    foreach($rubro['estaciones'] as $ch_estacion=>$estacion) {
		if ($params['modo'] == "DETALLADO") {
		    $result .= '<tr bgcolor="#cccc99">';
		    $result .= '<td colspan="18"><b>Sucursal: ' . htmlentities($ch_estacion) . '</b></td>';
		    $result .= '</tr>';
		    foreach($estacion['documentos'] as $key=>$documento) {
			$result .= '<tr bgcolor="#cccc99">';
		        $result .= '<td>' . htmlentities($documento['emision']) . '</td>';
		        $result .= '<td>' . htmlentities($documento['td']) . '</td>';
		        $result .= '<td>' . htmlentities($documento['serie']) . '</td>';
		        $result .= '<td>' . htmlentities($documento['numero']) . '</td>';
		        $result .= '<td>' . htmlentities($documento['ruc']) . '</td>';
		        $result .= '<td>' . htmlentities($documento['proveedor']) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['total_usd'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['inafecto'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['base_gravado'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['base_compartido'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['base_imponible'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['igv_gravado'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['igv_compartido'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['percepcion'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['renta4ta'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['solidaridad'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities(number_format($documento['total'], 2, '.', ',')) . '</td>';
		        $result .= '<td align="right">' . htmlentities($documento['registro']) . '</td>';
		        $result .= '</tr>';
		    }
		}
		$result .= '<tr bgcolor="#cccc99">';
		$result .= '<td colspan="6"><b>' . htmlentities("* Sub-Total Sucursal " . $ch_estacion . " rubro " . $ch_rubro) . '</b></td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['total_usd'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['inafecto'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['base_gravado'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['base_compartido'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['base_imponible'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['igv_gravado'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['igv_imponible'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['percepcion'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['renta4ta'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['solidaridad'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right">' . htmlentities(number_format($estacion['totales']['total'], 2, '.', ',')) . '</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '</tr>';
	    }
	    $result .= '<tr bgcolor="#cccc99">';
	    $result .= '<td colspan="6"><b>' . htmlentities("* Sub-Total Rubro " . $ch_rubro) . '</b></td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['total_usd'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['inafecto'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['base_gravado'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['base_compartido'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['base_imponible'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['igv_gravado'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['igv_imponible'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['percepcion'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['renta4ta'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['solidaridad'], 2, '.', ',')) . '</td>';
	    $result .= '<td align="right">' . htmlentities(number_format($rubro['totales']['total'], 2, '.', ',')) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '</tr>';
	}
	$result .= '<tr bgcolor="#cccc99">';
	$result .= '<td colspan="6"><b>' . htmlentities("Total General") . '</b></td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['total_usd'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['inafecto'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['base_gravado'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['base_compartido'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['base_imponible'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['igv_gravado'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['igv_imponible'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['percepcion'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['renta4ta'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['solidaridad'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($results['totales']['total'], 2, '.', ',')) . '</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '</tr>';

	$result .= '</table>';
	return $result;
    }
    
    function reportePDF($results, $params)
    {
	$cab = Array(
	    "emision"		=> "Emision",
	    "td"		=> "TD",
	    "serie"		=> "Ser",
	    "numero"		=> "Numero",
	    "ruc"		=> "RUC",
	    "proveedor"		=> "Proveedor",
	    "total_usd"		=> "Total(USD)",
	    "inafecto"		=> "Inafecto",
	    "base_gravado"	=> "Gravado",
	    "base_compartido"	=> "Compartido",
	    "base_imponible"	=> "Imponible",
	    "igv_gravado"	=> "Gravado",
	    "igv_compartido"	=> "Compartido",
	    "percepcion"	=> "Combustibl",
	    "renta4ta"		=> "Renta 4ta",
	    "solidaridad"	=> "Solidarida",
	    "total"		=> "Total",
	    "registro"		=> "Num. Reg."
	);

	$cab1 = Array(
	    "emision"		=> "Fecha",
	    "td"		=> "",
	    "serie"		=> "",
	    "numero"		=> "",
	    "ruc"		=> "",
	    "proveedor"		=> "",
	    "total_usd"		=> "",
	    "inafecto"		=> "",
	    "base_gravado"	=> "Base Dest.",
	    "base_compartido"	=> "Base Dest.",
	    "base_imponible"	=> "",
	    "igv_gravado"	=> "IGV Dest.",
	    "igv_compartido"	=> "IGV Dest.",
	    "percepcion"	=> "Percepcion",
	    "renta4ta"		=> "",
	    "solidaridad"	=> "Impuesto",
	    "total"		=> "",
	    "registro"		=> ""
	);

	$reporte = new CReportes2("L");
	
	$reporte->definirColumna("emision", $reporte->TIPO_TEXTO, 11, "L");
	$reporte->definirColumna("td", $reporte->TIPO_TEXTO, 2, "L");
	$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 3, "L");
	$reporte->definirColumna("numero", $reporte->TIPO_TEXTO, 8, "L");
	$reporte->definirColumna("ruc", $reporte->TIPO_TEXTO, 11, "L");
	$reporte->definirColumna("proveedor", $reporte->TIPO_TEXTO, 30, "L");
	$reporte->definirColumna("total_usd", $reporte->TIPO_TEXTO, 10, "R");
	$reporte->definirColumna("inafecto", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("base_gravado", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("base_compartido", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("base_imponible", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("igv_gravado", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("igv_compartido", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("percepcion", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("renta4ta", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("solidaridad", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("registro", $reporte->TIPO_TEXTO, 6, "L");
	
	$reporte->definirColumna("emision", $reporte->TIPO_TEXTO, 11, "L", "_cab1");
	$reporte->definirColumna("td", $reporte->TIPO_TEXTO, 2, "L", "_cab1");
	$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 3, "L", "_cab1");
	$reporte->definirColumna("numero", $reporte->TIPO_TEXTO, 8, "L", "_cab1");
	$reporte->definirColumna("ruc", $reporte->TIPO_TEXTO, 11, "L", "_cab1");
	$reporte->definirColumna("proveedor", $reporte->TIPO_TEXTO, 30, "L", "_cab1");
	$reporte->definirColumna("total_usd", $reporte->TIPO_TEXTO, 10, "R", "_cab1");
	$reporte->definirColumna("inafecto", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("base_gravado", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("base_compartido", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("base_imponible", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("igv_gravado", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("igv_compartido", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("percepcion", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("renta4ta", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("solidaridad", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 10, "R", "_cab1");
	$reporte->definirColumna("registro", $reporte->TIPO_TEXTO, 6, "L", "_cab1");

	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 70, "L", "_totales");
	$reporte->definirColumna("total_usd", $reporte->TIPO_TEXTO, 10, "R", "_totales");
	$reporte->definirColumna("inafecto", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("base_gravado", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("base_compartido", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("base_imponible", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("igv_gravado", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("igv_compartido", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("percepcion", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("renta4ta", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("solidaridad", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 10, "R", "_totales");
	$reporte->definirColumna("registro", $reporte->TIPO_TEXTO, 6, "L", "_totales", "B");

	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 50, "L", "_rotulo", "B");

	$reporte->definirCabecera(1, "L", "Asesoria Comercial S.A. - ACOSA");
	$reporte->definirCabecera(1, "R", "Pag. %p");
	$reporte->definirCabecera(2, "L", "Usuario: %u");
	$reporte->definirCabecera(2, "R", "%f");
	$reporte->definirCabecera(3, "C", "Reporte de Registro de Compras del " . $params['desde'] . " al " . $params['hasta']);

	$reporte->definirCabeceraPredeterminada($cab1, "_cab1");
	$reporte->definirCabeceraPredeterminada($cab);
	
	$reporte->SetFont("courier", "", 7);
	$reporte->SetMargins(0,0,0);
	
	$reporte->AddPage();

	foreach($results['rubros'] as $ch_rubro=>$rubro) {
	    $reporte->nuevaFila(array("rotulo"=>" *** Rubro:".$ch_rubro), "_rotulo");
	    foreach($rubro['estaciones'] as $ch_estacion=>$estacion) {
		if ($params['modo'] == "DETALLADO") {
		    $reporte->nuevaFila(array("rotulo"=>"   *** Sucursal:".$ch_estacion), "_rotulo");
		    foreach($estacion['documentos'] as $key=>$documento) {
			$reporte->nuevaFila($documento);
		    }

		    $reporte->Ln();
		}
		$estacion['totales']['rotulo'] =  "   *** Subtotal Sucursal " . $ch_estacion . " Rubro " . $ch_rubro;
		$reporte->nuevaFila($estacion['totales'], "_totales");
		
	    }
	    $rubro['totales']['rotulo'] = " *** Subtotal Rubro " . $ch_rubro;
	    $reporte->nuevaFila($rubro['totales'], "_totales");
	}
	$results['totales']['rotulo'] = "Total General";
	$reporte->nuevaFila($results['totales'], "_totales");

	$reporte->Output();
	
    }
}

?>