<?php
class RegistroTemplate extends Template
{

    function formSearch()
    {
	$almacenes = RegistroModel::obtenerListaAlmacenes();
	$documentos = RegistroModel::obtenerListaDocumentos();

	$almacenes['TODOS'] = "Todos los almacenes";
	$documentos['TODOS'] = "Todos los documentos";

	$ayer = time()-(24*60*60);
	$fecha = date("d/m/Y", $ayer);

	$form = new form2("Reporte de Registro de Compras", "form_cpagar", FORM_METHOD_POST, "control.php", '', "control");
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.REGISTRO"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("params[desde]", "Desde:", $fecha, espacios(5), 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("params[hasta]", "Hasta:", $fecha, espacios(5), 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("params[proveedor]", "Proveedor:", '', '', 7, 6));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("params[tipo]", "Tipo de Doc.:", "TODOS", $documentos, ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("params[numdoc]", "Numero de Doc.:", '', '', 8, 7));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("params[almacen]", "Almacen:", "TODOS", $almacenes, ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Buscar"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	return $form->getForm();
    }
    
    function reporte($results, $params)
    {
	$result  = '<a href="control.php?' . urlencode("params[desde]") . '=' . urlencode($params['desde']) . '&' . urlencode("params[hasta]") . '=' . urlencode($params['hasta']) . '&' . urlencode("params[proveedor]") . '=' . urlencode($params['proveedor']) . '&' . urlencode("params[tipo]") . '=' . urlencode($params['tipo']) . '&' . urlencode("params[numdoc]") . '=' . urlencode($params['numdoc']) . '&' . urlencode("params[almacen]") . '=' . urlencode($params['almacen']) . '&rqst=REPORTES.REGISTRO&action=pdf" target="_blank">Imprimir</a>';
	$result .= '<table border="0" cellspacing="2" cellpadding="3" bgcolor="#FFFFCC">';
	$result .= '<tr bgcolor="#cccc99">';
	$result .= '<td>Fecha Registro</td>';
	$result .= '<td>Fecha Emision</td>';
	$result .= '<td>Almacen</td>';
	$result .= '<td>Tipo</td>';
	$result .= '<td>Serie</td>';
	$result .= '<td>Numero</td>';
	$result .= '<td>Proveedor</td>';
	$result .= '<td>Moneda</td>';
	$result .= '<td>Inafecto</td>';
	$result .= '<td>V. Venta</td>';
	$result .= '<td>IGV</td>';
	$result .= '<td>Total</td>';
	$result .= '<td>T.C.</td>';
	$result .= '<td>Rubro</td>';
	$result .= '<td>Vencimiento</td>';
	$result .= '</tr>';
	
	foreach($results['almacenes'] as $cod_almacen=>$fechas) {
	    $result .= '<tr bgcolor="#cccc99">';
	    $result .= '<td colspan="15"><b>Almacen: ' . htmlentities($cod_almacen) . '</b></td>';
	    $result .= '</tr>';
	    
	    foreach($fechas['fechas'] as $fecha=>$documentos) {
		$result .= '<tr bgcolor="#cccc99">';
		$result .= '<td colspan="15"><b>Dia: ' . htmlentities($fecha) . '</b></td>';
		$result .= '</tr>';
		
		foreach($documentos['documentos'] as $key=>$documento) {
		    $result .= '<tr bgcolor="#cccc99">';
		    $result .= '<td>' . htmlentities($documento['fecha_registro']) . '</td>';
		    $result .= '<td>' . htmlentities($documento['fecha_emision']) . '</td>';
		    $result .= '<td>' . htmlentities($documento['almacen']) . '</td>';
		    $result .= '<td>' . htmlentities($documento['tip_documento']) . '</td>';
		    $result .= '<td>' . htmlentities($documento['serie_documento']) . '</td>';
		    $result .= '<td>' . htmlentities($documento['num_documento']) . '</td>';
		    $result .= '<td>' . htmlentities($documento['pro_codigo']) . '</td>';
		    $result .= '<td>' . htmlentities($documento['moneda']) . '</td>';
		    $result .= '<td align="right">' . htmlentities(number_format($documento['inafecto'], 2, '.', ',')) . '</td>';
		    $result .= '<td align="right">' . htmlentities(number_format($documento['vv'], 2, '.', ',')) . '</td>';
		    $result .= '<td align="right">' . htmlentities(number_format($documento['igv'], 2, '.', ',')) . '</td>';
		    $result .= '<td align="right">' . htmlentities(number_format($documento['total'], 2, '.', ',')) . '</td>';
		    $result .= '<td align="right">' . htmlentities(number_format($documento['tc'], 3, '.', ',')) . '</td>';
		    $result .= '<td>' . htmlentities($documento['rubro']) . '</td>';
		    $result .= '<td>' . htmlentities($documento['vencimiento']) . '</td>';
		}
		$result .= '<tr bgcolor="#cccc99"><td colspan="15">&nbsp;</td></tr>';
	    }
	}

	$result .= '</table>';

	return $result;
    }
    
    function reportePDF($results, $params)
    {
	$cab = Array(
	    "fecha_registro"	=> "Fec. Reg.",
	    "fecha_emision"	=> "Fec. Emi.",
	    "almacen"		=> "Almacen",
	    "tip_documento"	=> "Tipo",
	    "serie_documento"	=> "Ser",
	    "num_documento"	=> "Numero",
	    "pro_codigo"	=> "Codigo Proveedor",
	    "moneda"		=> "Moneda",
	    "inafecto"		=> "Inafecto",
	    "vv"		=> "V. Venta",
	    "igv"		=> "IGV",
	    "total"		=> "Total",
	    "tc"		=> "T.C.",
	    "saldo"		=> "Saldo",
	    "rubro"		=> "Rubro",
	    "vencimiento"	=> "Fec. Venc."
	);

	$reporte = new CReportes2("L");
	
	$reporte->definirColumna("fecha_registro", $reporte->TIPO_TEXTO, 11, "L");
	$reporte->definirColumna("fecha_emision", $reporte->TIPO_TEXTO, 11, "L");
	$reporte->definirColumna("almacen", $reporte->TIPO_TEXTO, 15, "L");
	$reporte->definirColumna("tip_documento", $reporte->TIPO_TEXTO, 15, "L");
	$reporte->definirColumna("serie_documento", $reporte->TIPO_TEXTO, 3, "L");
	$reporte->definirColumna("num_documento", $reporte->TIPO_TEXTO, 8, "L");
	$reporte->definirColumna("pro_codigo", $reporte->TIPO_TEXTO, 25, "L");
	$reporte->definirColumna("moneda", $reporte->TIPO_TEXTO, 7, "L");
	$reporte->definirColumna("inafecto", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("vv", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("tc", $reporte->TIPO_CANTIDAD, 6, "R");
	$reporte->definirColumna("rubro", $reporte->TIPO_TEXTO, 15, "L");
	$reporte->definirColumna("vencimiento", $reporte->TIPO_TEXTO, 10, "L");
	
	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 50, "L", "_rotulo", "B");

	$reporte->definirCabecera(1, "L", "Asesoria Comercial S.A. - ACOSA");
	$reporte->definirCabecera(1, "R", "Pag. %p");
	$reporte->definirCabecera(2, "L", "Usuario: %u");
	$reporte->definirCabecera(2, "R", "%f");
	$reporte->definirCabecera(3, "C", "Reporte de Registro de Compras desde el " . $params['desde'] . " hasta el " . $params['hasta']);

	$reporte->definirCabeceraPredeterminada($cab);
	
	$reporte->SetFont("courier", "", 7);
	
	$reporte->AddPage();
	
	foreach($results['almacenes'] as $cod_almacen=>$fechas) {
	    $a = Array("rotulo"=>"Almacen: " . $cod_almacen);
	    $reporte->nuevaFila($a, "_rotulo");

	    foreach($fechas['fechas'] as $fecha=>$documentos) {
		$a = Array("rotulo"=>"Dia: " . $fecha);
		$reporte->nuevaFila($a, "_rotulo");
		
		foreach($documentos['documentos'] as $key=>$documento) {
		    $reporte->nuevaFila($documento);
		}
		$reporte->Ln();
	    }
	}

	$reporte->Output();
	
    }
}

?>