<?php

class TransferenciasTemplate extends Template
{
    function Titulo()
    {
	return '<h2><b>Consistencia de transferencias</b></h2>';
    }
    
    function formSearch()
    {
	if (!$_REQUEST['desde']) $hoyd = date("d/m/Y");
	else $hoyd = $_REQUEST['desde'];
	if (!$_REQUEST['hasta']) $hoyh = date("d/m/Y");
	else $hoyh = $_REQUEST['hasta'];
	
	$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.TRANSF"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Desde:", "desde", $hoyd, '', '', 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div><br/>'));
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Hasta:", "hasta", $hoyh, '', '', 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;<br/>'));
	$form->addElement(FORM_GROUP_MAIN, new form_element_checkbox("Actualizar costos?", "bActualizar", '', "<br>", ''));
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '', '', 20));
	return $form->getForm();
    }
    
    function listado($resultado, $desde, $hasta)
    {
	$nSalidas = TransferenciasModel::ObtenerListaAlmacenes($resultado['salidas']);
	$nEntradas = TransferenciasModel::ObtenerListaAlmacenes($resultado['entradas']);
	$nErrSalidas = TransferenciasModel::ObtenerListaAlmacenes($resultado['err_salida']);
	$nErrEntradas = TransferenciasModel::ObtenerListaAlmacenes($resultado['err_entrada']);

	$result = "";

	$result .= '<a href="control.php?rqst=REPORTES.TRANSF&desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta) . '&action=pdf" target="_blank">Imprimir</a>';
	if (count($nSalidas) > 0 && count($resultado['salidas']) > 0) {
	    $result .= '<table border="1">';
	    $result .= '<tr>';
	    $result .= '<th colspan="' . (count($nSalidas)+2) . '">Transferencias origen (08/28)</th>';
	    $result .= '</tr>';
	    $result .= '<tr>';
	    $result .= '<th>TIPO/ESTACIONES</th>';

	    foreach($nSalidas as $mov_almacen => $ch_nombre) {	
		$result .= '<th>' . htmlentities($ch_nombre) . '</th>';
	    }
	    
	    $result .= '<th>TOTAL</th>';
	    $result .= '</tr>';
	    
	    $salidas = $resultado['salidas'];
	    foreach($salidas['tipos'] as $art_tipo => $tipo) {
		$result .= '<tr>';
		$result .= '<td>' . htmlentities($art_tipo) . '</td>';
		foreach($nSalidas as $ch_almacen => $ch_descripcion) {
		    $result .= '<td>' . htmlentities(@$tipo[$ch_almacen]) . '&nbsp;</td>';
		}
		$result .= '<td>' . htmlentities($tipo['total']) . '</td>';
		$result .= '</tr>';
	    }
	
	    $result .= '<tr>';
	    $result .= '<td><b>TOTAL GENERAL</b></td>';
	    foreach($nSalidas as $ch_almacen=>$ch_descripcion) {
		$result .= '<td>' . htmlentities($salidas['total'][$ch_almacen]) . '</td>';
	    }
	    $result .= '<td>' . htmlentities($salidas['total']['total']) . '</td>';
	    $result .= '</tr>';

	    $result .= '</table>';
	}

	if (count($nEntradas) > 0 && count($resultado['entradas']) > 0) {
	    $result .= '<table border="1">';
	    $result .= '<tr>';
	    $result .= '<th colspan="' . (count($nEntradas)+2) . '">Transferencias destino (07/27)</th>';
	    $result .= '</tr>';
	    $result .= '<tr>';
	    $result .= '<th>TIPO/ESTACIONES</th>';

	    foreach($nEntradas as $mov_almacen => $ch_nombre) {	
		$result .= '<th>' . htmlentities($ch_nombre) . '</th>';
	    }

	    $result .= '<th>TOTAL</th>';
	    $result .= '</tr>';
	    $entradas = $resultado['entradas'];
	    foreach($entradas['tipos'] as $art_tipo => $tipo) {
		$result .= '<tr>';
		$result .= '<td>' . htmlentities($art_tipo) . '</td>';
		foreach($nEntradas as $ch_almacen => $ch_descripcion) {
		    $result .= '<td>' . htmlentities(@$tipo[$ch_almacen]) . '&nbsp;</td>';
		}
		$result .= '<td>' . htmlentities($tipo['total']) . '</td>';
		$result .= '</tr>';
	    }

	    $result .= '<tr>';
	    $result .= '<td><b>TOTAL GENERAL</b></td>';
	    foreach($nEntradas as $ch_almacen=>$ch_descripcion) {
		$result .= '<td>' . htmlentities($entradas['total'][$ch_almacen]) . '</td>';
	    }
	    $result .= '<td>' . htmlentities($entradas['total']['total']) . '</td>';
	    $result .= '</tr>';

	    $result .= '</table>';
	}

	if (count($nErrSalidas) > 0 && count($resultado['err_salida']) > 0) {
	    $result .= '<table border="1">';
	    $result .= '<tr>';
	    $result .= '<th colspan="' . (count($nErrSalidas)+2) . '">Transferencias sin destino (08/28)</th>';
	    $result .= '</tr>';
	    $result .= '<tr>';
	    $result .= '<th>TIPO/ESTACIONES</th>';
	    
	    foreach($nErrSalidas as $mov_almacen => $ch_nombre) {	
		$result .= '<th>' . htmlentities($ch_nombre) . '</th>';
	    }
	    
	    $result .= '<th>TOTAL</th>';
	    $result .= '</tr>';
	    $err_salida = $resultado['err_salida'];
	    foreach($err_salida['tipos'] as $art_tipo => $tipo) {
		$result .= '<tr>';
		$result .= '<td>' . htmlentities($art_tipo) . '</td>';
		foreach($nErrSalidas as $ch_almacen => $ch_descripcion) {
		    $result .= '<td>' . htmlentities(@$tipo[$ch_almacen]) . '&nbsp;</td>';
		}
		$result .= '<td>' . htmlentities($tipo['total']) . '</td>';
		$result .= '</tr>';
	    }

	    $result .= '<tr>';
	    $result .= '<td><b>TOTAL GENERAL</b></td>';
	    foreach($nErrSalidas as $ch_almacen=>$ch_descripcion) {
		$result .= '<td>' . htmlentities($err_salida['total'][$ch_almacen]) . '</td>';
	    }
	    $result .= '<td>' . htmlentities($err_salida['total']['total']) . '</td>';
	    $result .= '</tr>';

	    $result .= '</table>';
	}

	if (count($nErrEntradas) > 0 && count($resultado['err_entrada']) > 0) {
	    $result .= '<table border="1">';
	    $result .= '<tr>';
	    $result .= '<th colspan="' . (count($nErrEntradas)+2) . '">Transferencias sin origen (07/27)</th>';
	    $result .= '</tr>';
	    $result .= '<tr>';
	    $result .= '<th>TIPO/ESTACIONES</th>';
	    
	    foreach($nErrEntradas as $mov_almacen => $ch_nombre) {	
		$result .= '<th>' . htmlentities($ch_nombre) . '</th>';
	    }
	    
	    $result .= '<th>TOTAL</th>';
	    $result .= '</tr>';
	    $err_entrada = $resultado['err_entrada'];
	    foreach($err_entrada['tipos'] as $art_tipo => $tipo) {
		$result .= '<tr>';
		$result .= '<td>' . htmlentities($art_tipo) . '</td>';
		foreach($nErrEntradas as $ch_almacen => $ch_descripcion) {
		    $result .= '<td>' . htmlentities($tipo[$ch_almacen]) . '&nbsp;</td>';
		}
		$result .= '<td>' . htmlentities($tipo['total']) . '</td>';
		$result .= '</tr>';
	    }

	    $result .= '<tr>';
	    $result .= '<td><b>TOTAL GENERAL</b></td>';
	    foreach($nErrEntradas as $ch_almacen=>$ch_descripcion) {
		$result .= '<td>' . htmlentities($err_entrada['total'][$ch_almacen]) . '</td>';
	    }
	    $result .= '<td>' . htmlentities($err_entrada['total']['total']) . '</td>';
	    $result .= '</tr>';

	    $result .= '</table>';
	}

/*
	if (count($resultado['consistencia']) > 0) {	
	    $result .= '<br><br><h2><b>CONSISTENCIA DEL REPORTE</b></h2><br>';
	
	    for($i = 0; $i < count($resultado['consistencia']); $i++) {
		$result .= htmlentities($resultado['consistencia'][$i]) . '<br>';
	    }
	}*/

	return $result;
    }

    function reportePDF($results, $desde, $hasta)
    {
	$n_estaciones_x_pagina = 11;
	$ancho_columna = 12;

	$nSalidas = TransferenciasModel::ObtenerListaAlmacenes($results['salidas']);
	$nEntradas = TransferenciasModel::ObtenerListaAlmacenes($results['entradas']);
	$nErrSalidas = TransferenciasModel::ObtenerListaAlmacenes($results['err_salida']);
	$nErrEntradas = TransferenciasModel::ObtenerListaAlmacenes($results['err_entrada']);

	$lista_almacenes_salidas = TransferenciasTemplate::obtenerAlmacenes($nSalidas);
	$lista_almacenes_entradas = TransferenciasTemplate::obtenerAlmacenes($nEntradas);
	$lista_almacenes_errsalidas = TransferenciasTemplate::obtenerAlmacenes($nErrSalidas);
	$lista_almacenes_errentradas = TransferenciasTemplate::obtenerAlmacenes($nErrEntradas);

	$npag_salidas = ceil(count($lista_almacenes_salidas)/$n_estaciones_x_pagina);
	$npag_entradas = ceil(count($lista_almacenes_entradas)/$n_estaciones_x_pagina);
	$npag_errsalidas = ceil(count($lista_almacenes_errsalidas)/$n_estaciones_x_pagina);
	$npag_errentradas = ceil(count($lista_almacenes_errentradas)/$n_estaciones_x_pagina);

	$reporte = new CReportes2("L");

	$reporte->definirCabecera(1, "L", "SISTEMA INTEGRADO");
	$reporte->definirCabecera(1, "C", "Consistencia de Transferencias del " . $desde . " al " . $hasta);
	$reporte->definirCabecera(1, "R", "Pagina %p");
	$reporte->definirCabecera(2, "L", "%u");
	$reporte->definirCabecera(2, "R", "%f %h");

	$reporte->SetFont("courier", "", 7);

	for($i = 0; $i < $npag_salidas; $i++) {
	    $linea = Array();
	    $reporte->templates = Array();
	    for($a = ($i*$n_estaciones_x_pagina); $a < (($i+1)*$n_estaciones_x_pagina); $a++) {
		$linea[$a] = $lista_almacenes_salidas[$a];
	    }

	    $salidas = Array(
		"art_tipo"		=>		"Tipo",
		"total"		=>		"Total"
	    );
	    
	    foreach($linea as $mov_almacen) {
		$salidas[$mov_almacen] = $nSalidas[$mov_almacen];
	    }

	    $reporte->definirColumna("art_tipo", $reporte->TIPO_TEXTO, 20, "L", "_salidas");
	    foreach($linea as $ch_almacen) {
		$reporte->definirColumna($ch_almacen, $reporte->TIPO_IMPORTE, $ancho_columna, "R", "_salidas");
	    }
	    
	    if ($i == ($npag_salidas-1)) {
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, $ancho_columna, "R", "_salidas");
	    }

	    TransferenciasTemplate::imprimirParteReporte($reporte, $salidas, "_salidas", $results['salidas'], "Transferencias origen (08/28)");
	}

	for($i = 0; $i < $npag_entradas; $i++) {
	    $linea = Array();
	    $reporte->templates = Array();
	    for($a = ($i*$n_estaciones_x_pagina); $a < (($i+1)*$n_estaciones_x_pagina); $a++) {
		$linea[$a] = $lista_almacenes_entradas[$a];
	    }

	    $entradas = Array(
		"art_tipo"		=>		"Tipo",
		"total"		=>		"Total"
	    );
	    
	    foreach($linea as $mov_almacen) {
		$entradas[$mov_almacen] = $nSalidas[$mov_almacen];
	    }

	    $reporte->definirColumna("art_tipo", $reporte->TIPO_TEXTO, 20, "L", "_entradas");
	    foreach($linea as $ch_almacen) {
		$reporte->definirColumna($ch_almacen, $reporte->TIPO_IMPORTE, $ancho_columna, "R", "_entradas");
	    }
	    
	    if ($i == ($npag_entradas-1)) {
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, $ancho_columna, "R", "_entradas");
	    }

	    TransferenciasTemplate::imprimirParteReporte($reporte, $entradas, "_entradas", $results['entradas'], "Transferencias destino (07/27)");
	}

	for($i = 0; $i < $npag_errsalidas; $i++) {
	    $linea = Array();
	    $reporte->templates = Array();
	    for($a = ($i*$n_estaciones_x_pagina); $a < (($i+1)*$n_estaciones_x_pagina); $a++) {
		$linea[$a] = $lista_almacenes_errsalidas[$a];
	    }

	    $errsalidas = Array(
		"art_tipo"		=>		"Tipo",
		"total"		=>		"Total"
	    );
	    
	    foreach($linea as $mov_almacen) {
		$errsalidas[$mov_almacen] = $nErrSalidas[$mov_almacen];
	    }

	    $reporte->definirColumna("art_tipo", $reporte->TIPO_TEXTO, 20, "L", "_errsalidas");
	    foreach($linea as $ch_almacen) {
		$reporte->definirColumna($ch_almacen, $reporte->TIPO_IMPORTE, $ancho_columna, "R", "_errsalidas");
	    }
	    
	    if ($i == ($npag_errsalidas-1)) {
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, $ancho_columna, "R", "_errsalidas");
	    }

	    TransferenciasTemplate::imprimirParteReporte($reporte, $errsalidas, "_errsalidas", $results['err_salida'], "Transferencias sin origen (08/28)");
	}

	for($i = 0; $i < $npag_errentradas; $i++) {
	    $linea = Array();
	    $reporte->templates = Array();
	    for($a = ($i*$n_estaciones_x_pagina); $a < (($i+1)*$n_estaciones_x_pagina); $a++) {
		$linea[$a] = $lista_almacenes_errsalidas[$a];
	    }

	    $errentradas = Array(
		"art_tipo"	=>		"Tipo",
	        "total"		=>		"Total"
	    );
	    foreach($linea as $mov_almacen) {	
		$errentradas[$mov_almacen] = $nErrEntradas[$mov_almacen];
	    }

	    $reporte->definirColumna("art_tipo", $reporte->TIPO_TEXTO, 10, "L", "_errentradas");
	    foreach($linea as $ch_almacen) {
		$reporte->definirColumna($ch_almacen, $reporte->TIPO_IMPORTE, $ancho_columna, "R", "_errentradas");
	    }
	    
	    if ($i == ($npag_errentradas-1)) {
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, $ancho_columna, "R", "_errentradas");
	    }

	    TransferenciasTemplate::imprimirParteReporte($reporte, $errentradas, "_errentradas", $results['err_entrada'], "Transferencias sin destino (07/27)");

	}

	$reporte->Output();
    }


    function imprimirParteReporte(&$reporte, $cabecera, $estilo, $results, $titulo)
    {
	$reporte->cab_default=Array();
	$reporte->definirCabeceraPredeterminada($cabecera, $estilo);
	$reporte->definirCabecera(2, "C", $titulo);
	$reporte->AddPage();
	
	foreach($results['tipos'] as $art_tipo=>$tipo) {
	    $reporte->nuevaFila($tipo, $estilo);
	}
	$results['total']['art_tipo']="TOTAL GENERAL:";
	$reporte->lineaH();
	$reporte->nuevaFila($results['total'], $estilo);
    }
    
    function obtenerAlmacenes($array)
    {
	$result = Array();
	$i = 0;
	foreach($array as $ch_almacen => $ch_descripcion) {
	    $result[$i] = $ch_almacen;
	    $i++;
	}
	return $result;
    }
}

