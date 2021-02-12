<?php

class FormProcesTemplate extends Template
{
    function Titulo()
    {
	return '<h2 align="center" style="color:#336699"><b> FORMULARIOS PROCESADOS </b></h2>';
    }
    
    function formSearch(){
		$estaciones = FormProcesModel::ObtenerEstaciones();
		$formularios = FormProcesModel::ObtenerTiposFormularios();
		if (!$_REQUEST['desde']) $hoyd = date("d/m/Y");
		else $hoyd = $_REQUEST['desde'];
		if (!$_REQUEST['hasta']) $hoyh = date("d/m/Y");
		else $hoyh = $_REQUEST['hasta'];
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.FORMPROCES"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<table border="0">
		<tr>
			<td align="right">
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Almacen: </td><td>", "estacion", $_REQUEST['estacion'], "<br>", "", 1, $estaciones, false, ''));
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<tr>
			<td align="right">
		'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Tipo formulario: </td><td>", "formulario", $_REQUEST['formulario'], "<br>", '', 1, $formularios, false, ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<tr>
			<td>
		'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Desde: ", "desde", $hoyd, "", '', 12, 10, ""));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif"></a>&nbsp;&nbsp;'));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
			<td>
		'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Hasta: ", "hasta", $hoyh, "", '', 12, 10, ""));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif"></a>&nbsp;&nbsp;<br/>'));

		//$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '', '', 20));	
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
		<tr>
			<td colspan="3" align="center">
				<button type="submit" id="buscar" name="buscar" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
			</td>
		</tr>
		'));
		return $form->getForm();
    }

    function listado($resultados)
    {
	$resultado = '<table align="center">';
	$resultado .= '<tr>';
	$resultado .= '<td class="grid_cabecera" colspan="7" align="center">TIPO FORMULARIO</td>';
	$resultado .= '</tr><tr class="grid_cabecera">';
	//$resultado .= '<tr class="grid_cabecera">';
	$resultado .= '<td></td>';
	$resultado .= '<td align="center" class="grid_cabecera">FECHA</td>';
	$resultado .= '<td align="center" class="grid_cabecera">ORIGEN</td>';
	$resultado .= '<td align="center" class="grid_cabecera">DESTINO</td>';
	$resultado .= '<td align="center" class="grid_cabecera">DOC. EXTERNO</td>';
	$resultado .= '<td align="center" class="grid_cabecera">REFERENCIA</td>';
	$resultado .= '<td align="center" class="grid_cabecera">PROVEEDOR</td>';
	$resultado .= '</tr><tr>';
	$resultado .= '<td class="grid_cabecera" colspan="7">ARTICULOS</td>';
	$resultado .= '</tr><tr>';
	$resultado .= '<td align="center" class="grid_cabecera">CÓDIGO</td>';
	$resultado .= '<td colspan="2" align="center" class="grid_cabecera">DESCRIPCIÓN</td>';
	$resultado .= '<td align="center" class="grid_cabecera">PRESENTACIÓN</td>';
	$resultado .= '<td align="center" class="grid_cabecera">CANTIDAD</td>';
	$resultado .= '<td align="center" class="grid_cabecera">COST. UNIT.</td>';
	$resultado .= '<td align="center" class="grid_cabecera">COSTO TOTAL</td>';
	$resultado .= '</tr>';
	
	$old_key = "";
	$old_form = "";
	$totalgen = 0 ;
	$totalfom = 0 ;
	$formularios = FormProcesModel::ObtenerTiposFormularios();
	$sumGranTotCantidad = 0.00;

	foreach($resultados['formulario'] as $tran_codigo => $formulario) {
		/*
	    $resultado .= '<tr>';
	    $resultado .= '<td colspan="7">&nbsp;</td>';
	    $resultado .= '</tr>';*/
	    
		$resultado .= '<tr class="grid_detalle_especial">';
	    $resultado .= '<td colspan="7" align="center" style="font-size:11px; color:black;"><strong>(*) TIPO DE FORMULARIO: ' . htmlentities($formularios[trim($tran_codigo)]) . '</strong></td>';
	    $resultado .= '</tr>';
	    
	    foreach($formulario['movimientos'] as $mov_numero => $movimiento) {
	        $resultado .= '<tr class="grid_detalle_impar"><b>';
	        $resultado .= '<td>' . htmlentities($movimiento['mov_numero']) . '</td>';
	        $resultado .= '<td>' . htmlentities($movimiento['mov_fecha']) . '</td>';
	        $resultado .= '<td>' . htmlentities($movimiento['mov_almaorigen']) . '</td>';
	        $resultado .= '<td>' . htmlentities($movimiento['mov_almadestino']) . '</td>';
	        $resultado .= '<td>' . htmlentities($movimiento['mov_tipdocuref']) . '</td>';
	        $resultado .= '<td>' . htmlentities($movimiento['mov_docurefe']) . '</td>';
	        $resultado .= '<td>' . htmlentities($movimiento['pro_razsocial']) . '</td>';
	        $resultado .= '</tr>';

	    $sumTotCantidad = 0.00;

		foreach($movimiento['lineas'] as $key => $linea) {
		    $resultado .= '<tr class="bgcolor">';
	    	$resultado .= '<td>' . htmlentities($linea['art_codigo']) . '</td>';
		    $resultado .= '<td colspan="2">' . htmlentities($linea['art_descripcion']) . '</td>';
    	    $resultado .= '<td>' . htmlentities($linea['art_presentacion']) . '</td>';
    	    $resultado .= '<td align="right">' . htmlentities(number_format($linea['mov_cantidad'], '4', '.', ',')) . '</td>';
		    $resultado .= '<td align="right">' . htmlentities(number_format($linea['mov_costounitario'], '6', '.', ',')) . '</td>';
    	    $resultado .= '<td align="right">' . htmlentities(number_format($linea['mov_costototal'], '4', '.', ',')) . '</td>';
    	    $resultado .= '</tr>';
    	    $sumTotCantidad += $linea['mov_cantidad'];
		}
		//$resultado .= '<tr>';
		$resultado .= '<tr class="grid_detalle_total"><b>';
		$resultado .= '<td colspan="4" align="right" style="font-size:11px; color:black;"><strong>Total: </strong></td>';
		$resultado .= '<td align="right" style="font-size:11px; color:black;"><strong> ' . number_format($sumTotCantidad, '4', '.', ',') . '</strong></td>';
		$resultado .= '<td>&nbsp;</td>';
		$resultado .= '<td align="right" style="font-size:11px; color:black;"><strong> ' . number_format($movimiento['total'], '4', '.', ',') . '</strong></td>';
		//$resultado .= '<td colspan="7" align="right">Total: ' . htmlentities($movimiento['mov_numero']) . ': ' . $movimiento['total'] . '</td>';
		$resultado .= '</tr>';
		/*
        $resultado .= '<tr>';
        $resultado .= '<td colspan="7">&nbsp;</td>';
        $resultado .= '</tr>';
        */
        $sumGranTotCantidad += $sumTotCantidad;
        }

	    $resultado .= '<tr class="bgcolor_cabecera">';
	    //$resultado .= '<td align="right" colspan="4" style="font-size:11.5px; color:black;"><strong>Total ' .htmlentities($formularios[trim($tran_codigo)]) . ': </td>';
	    $resultado .= '<td align="right" colspan="4" style="font-size:11.5px; color:black;"><strong>Total Formulario: </td>';
	    $resultado .= '<td align="right" style="font-size:11.5px; color:black;"><strong> ' . number_format($sumGranTotCantidad, '4', '.', ',') . '</td>';
	    $resultado .= '<td>&nbsp;</td>';
	    $resultado .= '<td align="right" style="font-size:11.5px; color:black;"><strong> ' . number_format($formulario['total'], '4', '.', ',') . '</td>';
	    $resultado .= '</tr>';
	}
	
	$resultado .= '<tr class="bgcolor_cabecera">';
    $resultado .= '<td align="right" colspan="4"  style="font-size:15px; color:black;"><strong>Total General: </td>';
    $resultado .= '<td align="right" style="font-size:15px; color:black;"><strong>' . $resultados['total'] . '</td>';
    $resultado .= '<td>&nbsp;</td>';
    $resultado .= '<td align="right" style="font-size:15px; color:black;"><strong>' . $resultados['total'] . '</td>';
    $resultado .= '</tr>';
    $resultado .= '<tr>';
    $resultado .= '<td colspan="7">&nbsp;</td>';
    $resultado .= '</tr>';
	$resultado .= '</table>';
	return $resultado;
    }
}

