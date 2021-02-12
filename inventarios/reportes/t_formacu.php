<?php

class FormAcuTemplate extends Template {

	function Titulo() {
		return '<h2 align="center" style="color:#336699"><b> MOVIMIENTOS ACUMULADOS </b></h2>';
    	}
    
    	function formSearch() {
		$modos = Array("DETALLADO"=>"Detallado", "RESUMIDO"=>"Resumido");

		$estaciones 	= FormProcesModel::ObtenerEstaciones();
		$formularios 	= FormProcesModel::ObtenerTiposFormularios();

		if (!$_REQUEST['desde']) 
			$hoyd = date("d/m/Y");
		else 
			$hoyd = $_REQUEST['desde'];

		if (!$_REQUEST['hasta']) 
			$hoyh = date("d/m/Y");
		else 
			$hoyh = $_REQUEST['hasta'];
	       
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");

		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.FORMACU"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));


		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<table border="0">
		<tr>
			<td align="right">
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Almacen: </td><td>", "estaciones", $_REQUEST['estacion'], "<br>", "", 1, $estaciones, false, ''));
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<tr>
			<td align="right">
		'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Tipo formulario: </td><td>", "formulario", $_REQUEST['formulario'], "<br>", '', 1, $formularios, false, ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<tr>
			<td>
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Desde:", "desde", $hoyd, "", '', 12, 10, ""));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
			<td>
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Hasta:", "hasta", $hoyh, "", '', 12, 10, ""));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;<br/>'));
		
		//$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Estaciones:", "estaciones", $_REQUEST['estaciones'], "<br>", "", 1, $estaciones, false, ''));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Formularios:", "formulario", $_REQUEST['formulario'], "<br>", '', 1, $formularios, false, ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<tr>
			<td colspan="3" align="center">
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_radio("Modo:", "modo", "DETALLADO", "<br>", '', 2, $modos, ""));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<tr>
			<td colspan="3" align="center">
		'));

		//$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '', '', 20));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('&nbsp;&nbsp;&nbsp;&nbsp;'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button>'));

		return $form->getForm();
    }

   	function Search($results, $estaciones, $desde, $hasta, $formulario, $modo) {
		$modelo = $results['modelo'];
		$ncols = (count($modelo)*2)+4;

		//$result = '<button name="fm" value="" onClick="javascript:parent.location.href=\'/sistemaweb/inventarios/control.php?rqst=REPORTES.FORMACU&desde='.htmlentities($desde).'&hasta='.htmlentities($hasta).'&formulario='.htmlentities($formulario).'&modo='.htmlentities($modo).'&estaciones='.htmlentities($estaciones).'&action=pdf\';return false"><img align="right" src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '</tr><tr>';

		/* primera linea de cabecera */
		$result .= '<td colspan="2" class="grid_cabecera" align="left">TIPO FORMULARIO</td>';
		//$result .= '<td>&nbsp</td>';

		foreach($modelo as $ch_almacen) {
		    	$result .= '<td align="center" class="grid_cabecera" colspan="2">' . htmlentities(FormProcesModel::obtenerDescripcionAlmacen($ch_almacen)) . '</td>';
		}
		$result .= '<td align="center" class="grid_cabecera" colspan="2">TOTAL</td>';
		$result .= '</tr><tr>';
	
		/* segunda linea de cabecera */
		$result .= '<td align="center" class="grid_cabecera">CODIGO</td>';
		$result .= '<td align="center" class="grid_cabecera">DESCRIPCIÓN</td>';

		for($i = 0; $i < count($modelo)+1; $i++) {
		    	$result .= '<td align="center" class="grid_cabecera">CANTIDAD</td>';
		    	$result .= '<td align="center" class="grid_cabecera">COSTO</td>';
		}
		$result .= '</tr>';

		foreach($results['formularios'] as $tran_codigo=>$formulario) {
			$result .= '<tr class="grid_detalle_especial">';
			$result .= '<td colspan="' . htmlentities($ncols) . '" align="left" style="font-size:12px; color:black;"><strong>*** Formulario: ' . htmlentities($tran_codigo) . '</td>';
			$result .= '</tr>';

		foreach($formulario['tipos'] as $art_tipo=>$tipo) {
			$result .= '<tr>';
			$result .= '<td class="grid_detalle_especial" colspan="' . htmlentities($ncols) . '" align="left" style="font-size:11px; color:black;"><strong>** Tipo: ' . htmlentities($art_tipo) . '</td>';
			$result .= '</tr>';

		foreach($tipo['lineas'] as $art_linea=>$linea) {
			$result .= '<tr>';
			$result .= '<td class="grid_detalle_especial" colspan="' . htmlentities($ncols) . '" align="left" style="font-size:11px; color:black;"><strong>* Linea: ' . htmlentities($art_linea) . '</td>';
			$result .= '</tr>';

			$i = 0;
			foreach($linea['articulos'] as $art_codigo=>$articulo) {
				$color = ($i % 2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");
				$result .= '<tr class="' . $color . '">';
				$result .= '<td>' . htmlentities($art_codigo) . '</td>';
				$result .= '<td>' . htmlentities($articulo['art_descripcion']) . '</td>';

				foreach($modelo as $ch_almacen) {
					$result .= '<td align="right">' . htmlentities(number_format($articulo[$ch_almacen.'_cant'], 4, ',', '.')) . '</td>';
					$result .= '<td align="right">' . htmlentities(number_format($articulo[$ch_almacen.'_cost'], 4, ',', '.')) . '</td>';
				}

				$result .= '<td align="right">' . htmlentities(number_format($articulo['total_cant'], 4, ',', '.')) . '</td>';
				$result .= '<td align="right">' . htmlentities(number_format($articulo['total_cost'], 4, ',', '.')) . '</td>';
				$result .= '</tr>';
				$i++;
			}
			    
			$result .= '<tr>';
			$result .= '<td colspan="2" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities($art_linea) . ' Total: </td>';

		foreach($modelo as $ch_almacen) {
			$result .= '<td align="right" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($linea['total'][$ch_almacen.'_cant'], 4, ',', '.')) . '</td>';
			$result .= '<td align="right" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($linea['total'][$ch_almacen.'_cost'], 4, ',', '.')) . '</td>';
		}

			$result .= '<td align="right" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($linea['total']['total_cant'], 4, ',', '.')) . '</td>';
			$result .= '<td align="right" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($linea['total']['total_cost'], 4, ',', '.')) . '</td>';
			$result .= '</tr>';
		}

			$result .= '<tr>';
			//$result .= '<td colspan="2">SUB-TOTAL Tipo ' . htmlentities($art_tipo) . '</td>';
			$result .= '<td colspan="2" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities($art_tipo) . ' Total: </td>';

		foreach($modelo as $ch_almacen) {
			$result .= '<td align="right" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($tipo['total'][$ch_almacen.'_cant'], 4, ',', '.')) . '</td>';
			$result .= '<td align="right" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($tipo['total'][$ch_almacen.'_cost'], 4, ',', '.')) . '</td>';
		}

			$result .= '<td align="right" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($tipo['total']['total_cant'], 4, ',', '.')) . '</td>';
			$result .= '<td align="right" class="grid_detalle_total" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($tipo['total']['total_cost'], 4, ',', '.')) . '</td>';
			$result .= '</tr>';
		}

		$result .= '<tr>';
		//$result .= '<td colspan="2">SUB-TOTAL Formulario ' . htmlentities($tran_codigo) . '</td>';
		$result .= '<td colspan="2" class="bgcolor_cabecera" align="right" style="font-size:11px; color:black;"><strong> Formulario - ' . htmlentities($tran_codigo) . ' Total: </td>';

		foreach($modelo as $ch_almacen) {
			$result .= '<td align="right" class="bgcolor_cabecera" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($formulario['total'][$ch_almacen.'_cant'], 4, ',', '.')) . '</td>';
			$result .= '<td align="right" class="bgcolor_cabecera" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($formulario['total'][$ch_almacen.'_cost'], 4, ',', '.')) . '</td>';
		}

			$result .= '<td align="right" class="bgcolor_cabecera" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($formulario['total']['total_cant'], 4, ',', '.')) . '</td>';
			$result .= '<td align="right" class="bgcolor_cabecera" align="right" style="font-size:11px; color:black;"><strong>' . htmlentities(number_format($formulario['total']['total_cost'], 4, ',', '.')) . '</td>';
			$result .= '</tr>';
		}

		$result .= '<tr>';
		//$result .= '<td colspan="2">Total General</td>';
		$result .= '<td colspan="2" class="bgcolor_cabecera" align="right" style="font-size:13px; color:black;"><strong> Total General: </td>';

		foreach($modelo as $ch_almacen) {
		   	$result .= '<td align="right" class="bgcolor_cabecera" align="right" style="font-size:13px; color:black;"><strong>' . htmlentities(number_format($results['total'][$ch_almacen.'_cant'], 4, ',', '.')) . '</td>';
		   	$result .= '<td align="right" class="bgcolor_cabecera" align="right" style="font-size:13px; color:black;"><strong>' . htmlentities(number_format($results['total'][$ch_almacen.'_cost'], 4, ',', '.')) . '</td>';
		}

			$result .= '<td align="right" class="bgcolor_cabecera" align="right" style="font-size:13px; color:black;"><strong>' . htmlentities(number_format($results['total']['total_cant'], 4, ',', '.')) . '</td>';
			$result .= '<td align="right" class="bgcolor_cabecera" align="right" style="font-size:13px; color:black;"><strong>' . htmlentities(number_format($results['total']['total_cost'], 4, ',', '.')) . '</td>';
			$result .= '</tr>';
			$result .= '</table>';

		return $result;
    	}

	function reportePDF($results, $desde, $hasta) {

		$n_estaciones_x_pagina = 8;
		$ancho_columna = 7;

		$reporte = new CReportes2("L");
		$reporte->SetFont("courier", "", 8);
		$reporte->SetMargins(0, 0, 0);

		$modelo = $results['modelo'];
		sort($modelo);
	
		$cabecera1 = Array(
		    			"total"		=>	"Total"
				);
		foreach($modelo as $ch_almacen) {
		    	$cabecera1[$ch_almacen] = FormProcesModel::obtenerDescripcionAlmacen($ch_almacen);
		}

		$cabecera2 = Array(
				    "art_codigo"	=>	"Cod. Art.",
				    "art_descripcion"	=>	"Descripcion",
				    "total_cost"	=>	"Costo",
				    "total_cant"	=>	"Cant."
				);
		foreach($modelo as $ch_almacen) {
		    	$cabecera2[$ch_almacen."_cant"] = "Cant.";
		    	$cabecera2[$ch_almacen."_cost"] = "Costo";
		}
	
		$n_pedazos = (int)((count($modelo)/$n_estaciones_x_pagina)+0.5);
	
		for ($i = 0; $i < $n_pedazos; $i++) {
		    	$linea = Array();

		        for($a = ($i*$n_estaciones_x_pagina); $a<(($i+1)*$n_estaciones_x_pagina); $a++) {
				$linea[$a] = $modelo[$a];
		    	}

			// Tipo de fila: principal 
		    	$reporte->definirColumna("art_codigo", $reporte->TIPO_TEXTO, 13, "L");
		    	$reporte->definirColumna("art_descripcion", $reporte->TIPO_TEXTO, 28, "L");

		    	foreach($linea as $ch_almacen) {
				$reporte->definirColumna($ch_almacen."_cant", $reporte->TIPO_ENTERO, $ancho_columna, "R");
				$reporte->definirColumna($ch_almacen."_cost", $reporte->TIPO_ENTERO, $ancho_columna, "R");
		    	}
		    	if ($i == ($n_pedazos-1)) {
				$reporte->definirColumna("total_cant", $reporte->TIPO_ENTERO, $ancho_columna, "R");
				$reporte->definirColumna("total_cost", $reporte->TIPO_ENTERO, $ancho_columna, "R");
		    	}

		    	// Tipo de fila: totales 
		    	$reporte->definirColumna("info", $reporte->TIPO_TEXTO, 42, "L", "_total");

		    	foreach($linea as $ch_almacen) {
				$reporte->definirColumna($ch_almacen."_cant", $reporte->TIPO_ENTERO, $ancho_columna, "R", "_total");
				$reporte->definirColumna($ch_almacen."_cost", $reporte->TIPO_ENTERO, $ancho_columna, "R", "_total");
		    	}
		    	if ($i == ($n_pedazos-1)) {
				$reporte->definirColumna("total_cant", $reporte->TIPO_ENTERO, $ancho_columna, "R", "_total");
				$reporte->definirColumna("total_cost", $reporte->TIPO_ENTERO, $ancho_columna, "R", "_total");
		    	}

		    	// Tipo de fila: cabecera 1 
		    	$reporte->definirColumna("dummy", $reporte->TIPO_TEXTO, 42, "L", "_cabecera1");
		    	foreach($linea as $ch_almacen) {
				$reporte->definirColumna($ch_almacen, $reporte->TIPO_TEXTO, ($ancho_columna*2)+1, "C", "_cabecera1");
		    	}
		    	if ($i == ($n_pedazos-1)) {
				$reporte->definirColumna("total", $reporte->TIPO_TEXTO, ($ancho_columna*2)+1, "C", "_cabecera1");
		    	}
		
		    	// Tipo de fila: rotulo 
		    	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 50, "L", "_rotulo");

		    	// Cabecera 
		    	if ($i == 0) {
				$reporte->definirCabecera(1, "C", "Movimientos Acumulados por Formulario");
				$reporte->definirCabecera(1, "L", "Pagina %p");
				$reporte->definirCabecera(2, "L", "%u");
				$reporte->definirCabecera(2, "C", "del " . $desde . " al " . $hasta);
		    	} else if ($i == ($n_pedazos-1)) {
				$reporte->definirCabecera(1, "R", "SISTEMA INTEGRADO");
				$reporte->definirCabecera(2, "R", "%f %h");
		    	}
		    	$reporte->definirCabeceraPredeterminada($cabecera1, "_cabecera1");
		    	$reporte->definirCabeceraPredeterminada($cabecera2);

		    	$reporte->AddPage();
			FormAcuTemplate::reporteParte($results, $reporte);
			$reporte->borrarCabeceras();
		}
		
		$reporte->Output();
		exit;

	}
    
    	function reporteParte($results, &$reporte) {

		foreach($results['formularios'] as $tran_codigo=>$formulario) {
		    	$a = Array("rotulo"=>"*** Formulario: " . $tran_codigo);
		    	$reporte->nuevaFila($a, "_rotulo");

		    	foreach($formulario['tipos'] as $art_tipo=>$tipo) {
				$a = Array("rotulo"=>"   *** Tipo: " . $art_tipo);
				$reporte->nuevaFila($a, "_rotulo");

				foreach($tipo['lineas'] as $art_linea=>$linea) {
			    		$a = Array("rotulo"=>"      *** Linea: " . $art_linea);
			    		$reporte->nuevaFila($a, "_rotulo");

			    		foreach($linea['articulos'] as $art_codigo=>$articulo) {
						$reporte->nuevaFila($articulo);
			    		}
			    		$linea['total']['info'] = "      *** Sub-Total Linea " . $art_linea;
			    		$reporte->nuevaFila($linea['total'], "_total");
			    		$reporte->Ln();
				}
				$tipo['total']['info'] = "   *** Sub-Total Tipo " . $art_tipo;
				$reporte->nuevaFila($tipo['total'], "_total");
				$reporte->Ln();
		    	}
		    	$formulario['total']['info'] = "*** Sub-Total Formulario " . $tran_codigo;
		    	$reporte->nuevaFila($formulario['total'], "_total");
		    	$reporte->Ln();
		}
		$results['total']['info'] = "Total General";
		$reporte->nuevaFila($results['total'], "_total");
    	}

	function reporteExcel($res, $almacen, $desde, $hasta) {

		//$nomalmacen = VarillasModel::obtenerSucursales($almacen);

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados Varillaje');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 50);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "MEDIDA DIARIA DE VARILLA",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$nomalmacen[$almacen],$formato0);
		$worksheet1->write_string(4, 0, "FECHA DEL ".$desde." AL ".$hasta,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;
		$worksheet1->write_string($a, 0, "FECHA",$formato2);
		$worksheet1->write_string($a, 1, "TANQUE",$formato2);
		$worksheet1->write_string($a, 2, "NOMBRE COMBUSTIBLE",$formato2);
		$worksheet1->write_string($a, 3, "MEDICION",$formato2);	
		$worksheet1->write_string($a, 4, "RESPONSABLE",$formato2);
		
		$a = 8;	

		for ($j=0; $j<count($res); $j++) {	
			//$nomtanque = VarillasModel::obtenerTanques($almacen, $res[$j]['ch_tanque']);	
			
			$worksheet1->write_string($a, 0, $res[$j]['dt_fecha'],$formato5);
			$worksheet1->write_string($a, 1, $nomtanque[$res[$j]['ch_tanque']],$formato5);
			$worksheet1->write_string($a, 2, $res[$j]['ch_nombre'],$formato5);	
			$worksheet1->write_number($a, 3, number_format($res[$j]['nu_medicion'],3,'.',''),$formato5);
			$worksheet1->write_string($a, 4, $res[$j]['ch_responsable'],$formato5);	
			$a++;
		}
			
		$workbook->close();	

		$chrFileName = "Varillaje";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}

}
