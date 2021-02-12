<?php

class DifInvTemplate extends Template
{
    function Titulo()
    {
	return "<h2><b>Reporte de diferencias de Inventario</b></h2>";
    }
    
    function formSearch()
    {
	if (!$_REQUEST['periodo']) $hoy = date("m/Y");
	else $hoy = $_REQUEST['periodo'];
	$estaciones = FormProcesModel::ObtenerEstaciones();
	
	$form = new Form('', "Buscar", FORM_METHOD_POST, 'control.php', '', "control");

	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.DIFINV"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Periodo:", "periodo", $hoy, '<br>', '', 9, 7));
	$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Estaciones:", "estaciones", $_REQUEST['estaciones'], '<br>', '', 1, $estaciones, false, ''));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '', '', 20));
	$form->addElement(FORM_GROUP_MAIN, new form_element_button("btImportar", "Importar...", '', '', 20, 'onClick="abrirImportarStock()"', false, ''));
	return $form->getForm();
    }
    
    function listado($resultados,$periodo)
    {
	global $totales;

	$result  = '<a href="/sistemaweb/inventarios/control.php?rqst=REPORTES.DIFINV&action=pdf&periodo=' . urlencode($periodo) . '" target="_blank">Imprimir</a>';
	$result .= '<table border="1">';
	$result .= '<tr><caption>Cuadro de Diferencias de Inventario</caption></tr>';
	
	$result .= '<tr>';
	$result .= '<td colspan="3">&nbsp;</td>';
	$result .= '<td colspan="2">Sistema WEB</td>';
	$result .= '<td colspan="2">Stock Fisico</td>';
	$result .= '<td colspan="2">Diferencia</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Codigo</td>';
	$result .= '<td>Articulo</td>';
	$result .= '<td>Cost. Unit.</td>';
	$result .= '<td>Cantidad</td>';
	$result .= '<td>Importe</td>';
	$result .= '<td>Cantidad</td>';
	$result .= '<td>Importe</td>';
	$result .= '<td>Cantidad</td>';
	$result .= '<td>Importe</td>';
	$result .= '<td>Periodo</td>';
	$result .= '</tr>';
	
        $old_alma = '';
        $totales = Array();

        for ($i = 0; $i < count($resultados); $i++) {
            if ($old_alma != $resultados[$i]['stk_almacen']) {
                if ($old_alma != '') $result .= DifInvTemplate::totalAlmacen($old_alma);
                $result .= '<tr>';
                $result .= '<td colspan="10">Almacen: ' . htmlentities($resultados[$i]['stk_almacen'] . " - " . $resultados[$i]['ch_nombre_almacen']) . '</td>';
                $result .= '</tr>';
                $old_alma = $resultados[$i]['stk_almacen'];
            }

            $result .= '<tr>';
            $result .= '<td>' . htmlentities($resultados[$i]['art_codigo']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['art_descripcion']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['stk_costo']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['stk_stock']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['stk_importe_stock']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['stk_fisico']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['stk_importe_fisico']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['stk_diferencia']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['stk_importe_diferencia']) . '</td>';
            $result .= '<td>' . htmlentities($resultados[$i]['stk_periodo']) . '</td>';
            $result .= '</tr>';

            $totales['sis_cant'] += $resultados[$i]['stk_stock'];
            $totales['sis_imp'] += $resultados[$i]['stk_importe_stock'];
            $totales['fis_cant'] += $resultados[$i]['stk_fisico'];
            $totales['fis_imp'] += $resultados[$i]['stk_importe_fisico'];
            $totales['dif_cant'] += $resultados[$i]['stk_diferencia'];
            $totales['dif_imp'] += $resultados[$i]['stk_importe_diferencia'];
        }

        if ($old_alma != "") $result .= DifInvTemplate::totalAlmacen($old_alma);
        $result .= '</table>';
	
	
	return $result;
    }
    
    function totalAlmacen($almacen)
    {
	global $totales;

	$result  = '<tr>';
	$result .= '<td colspan="3">Total Almacen = ' . htmlentities($almacen) . '</td>';
	$result .= '<td>' . htmlentities($totales['sis_cant']) . '</td>';
	$result .= '<td>' . htmlentities($totales['sis_imp']) . '</td>';
	$result .= '<td>' . htmlentities($totales['fis_cant']) . '</td>';
	$result .= '<td>' . htmlentities($totales['fis_imp']) . '</td>';
	$result .= '<td>' . htmlentities($totales['dif_cant']) . '</td>';
	$result .= '<td>' . htmlentities($totales['dif_imp']) . '</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '</tr>';
	
	$totales = Array();

	return $result;
    }

    function reportePDF($resultados, $periodo)
    {
	global $totales;
	global $usuario;

	$cabecera_1 = Array(
			    "stk_blanco" => " ",
			    "stk_web" => "Sistema Web",
			    "stk_fisico" => "Stock Fisico",
			    "stk_diferencia" => "Diferencia"
			);
			    
	$cabecera_2 = Array(
			    "art_codigo" => "Codigo",
			    "art_descripcion" => "Descripcion",
			    "stk_costo" => "Cos.Unit",
			    "stk_stock" => "Cantidad",
			    "stk_importe_stock" => "Importe",
			    "stk_fisico" => "Cantidad",
			    "stk_importe_fisico" => "Importe",
			    "stk_diferencia" => "Cantidad",
			    "stk_importe_diferencia" => "Importe",
			    "stk_periodo" => "Periodo"
			    );
	
	$reporte = new CReportes2("L");
	
	/* Definicion de cabecera */
	$reporte->definirCabecera(0, "L", "SISTEMA INTEGRADO - sistemaweb");
	$reporte->definirCabecera(0, "R", "Pag. %p");
	$reporte->definirCabecera(1, "L", "Usuario: " . $usuario->obtenerUsuario());
	$reporte->definirCabecera(1, "R", "%f %h");
	$reporte->definirCabecera(2, "C", "Reporte de Diferencias de Inventario");
	
	/* Definicion de columnas */
	$reporte->definirColumna("art_codigo", $reporte->TIPO_TEXTO, 14, "L");
	$reporte->definirColumna("art_descripcion", $reporte->TIPO_TEXTO, 30, "L");
	$reporte->definirColumna("stk_costo", $reporte->TIPO_COSTO, 10, "R");
	$reporte->definirColumna("stk_stock", $reporte->TIPO_CANTIDAD, 12, "R");
	$reporte->definirColumna("stk_importe_stock", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("stk_fisico", $reporte->TIPO_CANTIDAD, 12, "R");
	$reporte->definirColumna("stk_importe_fisico", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("stk_diferencia", $reporte->TIPO_CANTIDAD, 12, "R");
	$reporte->definirColumna("stk_importe_diferencia", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("stk_periodo", $reporte->TIPO_TEXTO, 12, "C");
	
	$reporte->definirColumna("stk_almacen", $reporte->TIPO_TEXTO, 50, "L", "_almacen", "B");
	
	$reporte->definirColumna("stk_blanco", $reporte->TIPO_TEXTO, 56, "L", "_cab1");
	$reporte->definirColumna("stk_web", $reporte->TIPO_TEXTO, 25, "C", "_cab1");
	$reporte->definirColumna("stk_fisico", $reporte->TIPO_TEXTO, 25, "C", "_cab1");
	$reporte->definirColumna("stk_diferencia", $reporte->TIPO_TEXTO, 25, "C", "_cab1");
	
	$reporte->definirColumna("sis_rotulo", $reporte->TIPO_TEXTO, 56, "L", "_totales", "B");
	$reporte->definirColumna("sis_cant", $reporte->TIPO_CANTIDAD, 12, "R", "_totales", "B");
	$reporte->definirColumna("sis_imp", $reporte->TIPO_IMPORTE, 12, "R", "_totales", "B");
	$reporte->definirColumna("fis_cant", $reporte->TIPO_CANTIDAD, 12, "R", "_totales", "B");
	$reporte->definirColumna("fis_imp", $reporte->TIPO_IMPORTE, 12, "R", "_totales", "B");
	$reporte->definirColumna("dif_cant", $reporte->TIPO_CANTIDAD, 12, "R", "_totales", "B");
	$reporte->definirColumna("dif_imp", $reporte->TIPO_IMPORTE, 12, "R", "_totales", "B");
	
	$reporte->definirCabeceraPredeterminada($cabecera_1, "_cab1");
	$reporte->definirCabeceraPredeterminada($cabecera_2);

	$reporte->SetFont("courier", "", 9);
	$reporte->AddPage();

	$old_alma = '';
	$totales = Array();
	
	for ($i = 0; $i < count($resultados); $i++) {
	    if ($old_alma != $resultados[$i]['stk_almacen']) {
		if ($old_alma != '') DifInvTemplate::totalAlmacenPDF($old_alma, $reporte);
		$a = Array("stk_almacen" => "Almacen: " . htmlentities($resultados[$i]['stk_almacen'] . " - " . $resultados[$i]['ch_nombre_almacen']));
		$reporte->nuevaFila($a, "_almacen");
		$old_alma = $resultados[$i]['stk_almacen'];
	    }
	    $reporte->nuevaFila($resultados[$i]);
//	    $reporte->lineaH();

	    @$totales['sis_cant'] += $resultados[$i]['stk_stock'];
	    @$totales['sis_imp'] += $resultados[$i]['stk_importe_stock'];
	    @$totales['fis_cant'] += $resultados[$i]['stk_fisico'];
	    @$totales['fis_imp'] += $resultados[$i]['stk_importe_fisico'];
	    @$totales['dif_cant'] += $resultados[$i]['stk_diferencia'];
	    @$totales['dif_imp'] += $resultados[$i]['stk_importe_diferencia'];
	}

	if ($old_alma != "") DifInvTemplate::totalAlmacenPDF($old_alma, $reporte);

	$reporte->Output();

    }

    function totalAlmacenPDF($almacen, &$reporte)
    {
	global $totales;

	$totales['sis_rotulo'] = "Total Almacen = " . $almacen;
	$reporte->nuevaFila($totales, "_totales");
	$reporte->Ln();
    }

    function formImportar()
    {
	$ayer = time()-(24*60*60);
	$fecha = date("d/m/Y", $ayer);
	$estaciones = FormProcesModel::ObtenerEstaciones();

	$form = new Form('', "Buscar", FORM_METHOD_POST, 'control.php', '', "control");

	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.DIFINV"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "DoImportar"));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Fecha:", "fecha", $fecha, '<br>', '', 11, 10));
	$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Estaciones:", "estaciones[]", "TODAS", '<br>', '', count($estaciones), $estaciones, true, ''));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Importar", '', '', 20));
	
	return $form->getForm();
    }
    
    function reporteImportacion($errores)
    {
	$result = '';

	if (is_array($errores)) {
	    $result .= '<table border="1">';
	    $result .= '<tr>';
	    $result .= '<td colspan="2">Reporte de estado en importacion</td>';
	    $result .= '</tr>';
	    $result .= '<tr>';
	    $result .= '<td>Almacen</td>';
	    $result .= '<td>Error</td>';
	    $result .= '</tr>';
	    foreach($errores as $ch_almacen => $valor) {
		if ($valor != 0) {
		    $result .= '<tr>';
	    	    $result .= '<td>' . htmlentities($ch_almacen) . '</td>';
		    $result .= '<td>' . htmlentities($valor) . '</td>';
	    	    $result .= '</tr>';
		}
		else {
		    $result .= '<tr>';
		    $result .= '<td>' . htmlentities($ch_almacen) . '</td>';
		    $result .= '<td>Actualizacion Correcta.</td>';
		    $result .= '</tr>';
		}
	    }
	
	    $result .= '</table>';
	}
	else {
	    if (!$errores) {
		$result .= '<b>Error de actualizacion</b>';
	    }
	    else {
		$result .= '<b>Actualizacion correcta.</b>';
	    }
	}

	$result .= '<input type="button" onclick="control.location.href=\'control.php?rqst=REPORTES.DIFINV&action=Importar\'" value="&lt;- Regresar">';
	return $result;
    }
}

