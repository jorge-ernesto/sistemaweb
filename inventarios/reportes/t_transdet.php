<?php

class TransDetTemplate extends Template
{
    function formSearch()
    {
	$desde = "01".date("/m/Y");
	$hasta = date("d/m/Y");

	$estaciones = FormProcesModel::obtenerEstaciones();

	$form = new form2('Consistencia de Transferencias en Detalle', 'buscar', FORM_METHOD_POST, 'control.php', '', 'control');
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.TRANSDET"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $desde, '', 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $hasta, '', 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", 'TODAS', $estaciones, ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox("costos", "Verificar costos?", "S"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Generar"));
	return $form->getForm();
    }
    
    function listado($results, $desde, $hasta, $estacion, $costos)
    {
	$result  = '<a href="control.php?rqst=REPORTES.TRANSDET&action=pdf&desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta) . '&estacion=' . urlencode($estacion) . '&costos=' . urlencode($costos) . '" target="_blank">Imprimir</a>';
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<caption>Consistencia de transferencias del ' . htmlentities($desde) . ' al ' . htmlentities($hasta) . '</caption>';
	$result .= '</tr><tr>';
	$result .= '<td>Nro. Form.</td>';
	$result .= '<td>Nro. Guia</td>';
	$result .= '<td>F. Salida</td>';
	$result .= '<td colspan="2">Articulo</td>';
	$result .= '<td>Cant. Salida</td>';
	$result .= '<td>Alm. Destino</td>';
	$result .= '<td>F. Ingreso</td>';
	$result .= '<td>Cant. Ingreso</td>';
	$result .= '<td>Diferencia</td>';
	$result .= '<td>Cost. Orig.</td>';
	$result .= '<td>Cost. Dest.</td>';
	$result .= '<td>Tipo Item</td>';
	$result .= '</tr>';

	foreach($results['salidas'] as $ch_almacen=>$filas) {
	    $result .= '<tr>';
	    $result .= '<td colspan="13">Almacen de Salida: ' . htmlentities($ch_almacen) . '</td>';
	    $result .= '</tr>';
	
	    foreach($filas as $key=>$fila) {
		$result .= '<tr>';
		$result .= '<td>' . htmlentities($fila['mov_numero']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_docurefe']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_fecha_salida']) . '</td>';
		$result .= '<td>' . htmlentities($fila['art_codigo']) . '</td>';
		$result .= '<td>' . htmlentities($fila['art_descripcion']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_cantidad_salida']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_nombre_almadestino']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_fecha_ingreso']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_cantidad_ingreso']) . '</td>';
		$result .= '<td>' . htmlentities($fila['dif_cantidad']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_costototal_ingreso']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_costototal_salida']) . '</td>';
		$result .= '<td>' . htmlentities($fila['art_tipo']) . '</td>';
		$result .= '</tr>';
	    }
	}

	foreach($results['entradas'] as $ch_almacen=>$filas) {
	    $result .= '<tr>';
	    $result .= '<td colspan="13">Almacen de Entrada: ' . htmlentities($ch_almacen) . '</td>';
	    $result .= '</tr>';
	
	    foreach($filas as $key=>$fila) {
		$result .= '<tr>';
		$result .= '<td>' . htmlentities($fila['mov_numero']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_docurefe']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_fecha_salida']) . '</td>';
		$result .= '<td>' . htmlentities($fila['art_codigo']) . '</td>';
		$result .= '<td>' . htmlentities($fila['art_descripcion']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_cantidad_salida']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_nombre_almadestino']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_fecha_ingreso']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_cantidad_ingreso']) . '</td>';
		$result .= '<td>' . htmlentities($fila['dif_cantidad']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_costototal_ingreso']) . '</td>';
		$result .= '<td>' . htmlentities($fila['mov_costototal_salida']) . '</td>';
		$result .= '<td>' . htmlentities($fila['art_tipo']) . '</td>';
		$result .= '</tr>';
	    }
	}
	$result .= '</table>';
	
	return $result;
    }
    
    function reportePDF($results, $desde, $hasta, $almacen)
    {
	$cabecera = Array(
			"mov_numero"		=>	"Nro.Form.",
			"mov_docurefe"		=>	"Nro. Guia",
			"mov_fecha_salida"	=>	"Fec.Salida",
			"art_codigo"		=>	"Cod. Articulo",
			"art_descripcion"	=>	"Descripcion",
			"mov_cantidad_salida"	=>	"Can.Sal",
			"mov_nombre_almadestino"=>	"Alm.Dest",
			"mov_fecha_ingreso"	=>	"Fec.Ingres",
			"mov_cantidad_ingreso"	=>	"Can.Ing",
			"dif_cantidad"		=>	"Dif",
			"mov_costototal_ingreso"=>	"Cost.Ing",
			"mov_costototal_salida"	=>	"Cost.Sal",
			"art_tipo"		=>	"Tipo"
		    );

	$reporte = new CReportes2("L");
	
	$reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 10, "L");
	$reporte->definirColumna("mov_docurefe", $reporte->TIPO_TEXTO, 10, "L");
	$reporte->definirColumna("mov_fecha_salida", $reporte->TIPO_TEXTO, 10, "L");
	$reporte->definirColumna("art_codigo", $reporte->TIPO_TEXTO, 13, "L");
	$reporte->definirColumna("art_descripcion", $reporte->TIPO_TEXTO, 20, "L");
	$reporte->definirColumna("mov_cantidad_salida", $reporte->TIPO_CANTIDAD, 7, "R");
	$reporte->definirColumna("mov_nombre_almadestino", $reporte->TIPO_TEXTO, 10, "L");
	$reporte->definirColumna("mov_fecha_ingreso", $reporte->TIPO_TEXTO, 10, "L");
	$reporte->definirColumna("mov_cantidad_ingreso", $reporte->TIPO_CANTIDAD, 7, "R");
	$reporte->definirColumna("dif_cantidad", $reporte->TIPO_CANTIDAD, 5, "R");
	$reporte->definirColumna("mov_costototal_ingreso", $reporte->TIPO_COSTO, 10, "R");
	$reporte->definirColumna("mov_costototal_salida", $reporte->TIPO_COSTO, 10, "R");
	$reporte->definirColumna("art_tipo", $reporte->TIPO_TEXTO, 10, "L");
	$reporte->definirColumna("texto", $reporte->TIPO_TEXTO, 50, "L", "_rotulo", "B");

	$reporte->definirCabecera(1, "L", "SISTEMA INTEGRADO");
	$reporte->definirCabecera(1, "C", "Consistencia de Transferencias del " . $desde . " al " . $hasta);
	$reporte->definirCabecera(1, "R", "Pagina %p");
	$reporte->definirCabecera(2, "L", "%u");
	$reporte->definirCabecera(2, "R", "%f %h");

	$reporte->definirCabeceraPredeterminada($cabecera);

	$reporte->SetFont("courier", "", 9);
	$reporte->SetMargins(0,0,0);
	$reporte->AddPage();
	
	foreach($results['salidas'] as $ch_almacen=>$filas) {
	    $a = Array("texto"=>"Almacen de Salida: " . $ch_almacen);
	    $reporte->nuevaFila($a, "_rotulo");
	    foreach($filas as $key=>$fila) {
		$reporte->nuevaFila($fila);
	    }
	    $reporte->Ln();
	}
	
	$reporte->AddPage();

	foreach($results['entradas'] as $ch_almacen=>$filas) {
	    $a = Array("texto"=>"Almacen de Entrada: " . $ch_almacen);
	    $reporte->nuevaFila($a, "_rotulo");
	    foreach($filas as $key=>$fila) {
		$reporte->nuevaFila($fila);
	    }
	    $reporte->Ln();
	}
	
	return $reporte->Output();
    }
}

