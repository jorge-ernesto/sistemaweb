<?php

class GraficoVentasHorasTemplate extends Template
{
    
    function search_form(){
	$ayer = time()-(24*60*60);
	$fecha = date("d/m/Y", $ayer);

	$estaciones = GraficoVentasHorasModel::obtieneListaEstaciones();
	$estaciones['TODAS'] = "Todas las Estaciones";

	$tipos = GraficoVentasHorasModel::obtieneTipos();
	$tipos['TODAS'] = "Todos los Tipos";

	$dia = GraficoVentasHorasModel::obtieneDias();
	$dia['TODAS'] = "Todos los Dias";

	$producto = GraficoVentasHorasModel::obtieneProductos();
	$producto['TODAS'] = "Todos los Productos";

	$lado = GraficoVentasHorasModel::obtieneLados();
	$lado['TODAS'] = "Todos los Lados";

	$form = new form2("Grafico de Ventas por Horas", "form_ventas_horas", FORM_METHOD_POST, "control.php", '', "control");
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.GRAFICOVENTASHORAS"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $fecha, '', 10, 12));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_ventas_horas.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="right">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha, '', 10, 12));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_ventas_horas.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipo", "Tipo:", "TODAS", $tipos, espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="right">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("dia", "Dia:", "TODAS", $dia, espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("producto", "Producto:", "TODAS", $producto, espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="right">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("lado", "Lado:", "TODAS", $lado, espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center" colspan="2">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "TODAS", $estaciones, espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td colspan="2" align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	return $form->getForm();
    }
    
    function reporte($rs_diario, $desde, $hasta) {
		$total = 0;
		$maximo = 0;
		$minimo = 0;
		for($i=0;$i<pg_numrows($rs_diario);$i++){ 
			$A = pg_fetch_array($rs_diario,$i);    
			$total += $A[1];
			if ($maximo == 0)
				$minimo = $A[1];
			if ($A[1]>$maximo)
				$maximo = $A[1];
			if ($A[1]<$minimo)
				$minimo = $A[1];
		}
	$result  = '';
	$result .= '<table border="1" width="1250" cellspacing="0" cellpadding="2">';
	$result .= '<tr>';
	$result .= '<th width="7%">Fecha</th>';
	$result .= '<th width="4%">Hora</th>';
	$result .= '<th width="8%">Venta</th>';
	$result .= '<th width="4%">Porcentaje</th>';
	$result .= '<td>&nbsp;</td>';
	$result .= '</tr>';
	for($i=0;$i<pg_numrows($rs_diario);$i++) { 
		$A = pg_fetch_array($rs_diario,$i);
		$porcentaje = round((( $A[1] / $total ) * 100),2);
		$color="#2EFE2E";
		$result .= '<tr>';
		$result .= '<td width="7%" align="center">'.$A[0].'</td>';
		$result .= '<td width="4%" align="center">'.$A[2].':00</td>';
		$result .= '<td width="8%" align="right">S/. '.htmlentities(number_format($A[1], 2, '.', ',')).'</td>';
		$result .= '<td width="4%" align="right">'.htmlentities(number_format($porcentaje, 2, '.', ',')).'%</td>';
		$result .= '<td>';
		if($maximo == $A[1]) $color="#0101DF";
		if($minimo == $A[1]) $color="#FF0000";
		$result .= '<table border="0" width="'.($porcentaje*10).'%"  bgcolor="'.$color.'">';
		$result .= '<tr><td></td></tr>';
		$result .= '</table>';
		$result .= '</td>';

		$result .= '</tr>';
	}
	$result .= '<tr>';
	$result .= '<th width="7%" style="color: blue">TOTAL</th>';
	$result .= '<td width="4%">&nbsp;</td>';
	$result .= '<td width="8%" align="right" style="color: blue">S/. '.htmlentities(number_format($total, 2, '.', ',')).'</td>';
	$result .= '<td width="4%" align="right" style="color: blue">100.00%</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '</tr>';
	$result .= '</table>';
	
	return $result;
    }
    
    function imprimirLinea($array, $label)
    {
	$result  = '<tr>';
	$result .= '<td>' . htmlentities($label) . '</td>';
		
	/* 84 */
	$result .= '<td align="right">' . htmlentities(number_format($array['11620301_galones'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['11620301_importe'], 2, '.', ',')) . '</td>';
    
	/* 90 */
    	$result .= '<td align="right">' . htmlentities(number_format($array['11620302_galones'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['11620302_importe'], 2, '.', ',')) . '</td>';

	/* 95 */
	$result .= '<td align="right">' . htmlentities(number_format($array['11620305_galones'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['11620305_importe'], 2, '.', ',')) . '</td>';

	/* 97 */
	$result .= '<td align="right">' . htmlentities(number_format($array['11620303_galones'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['11620303_importe'], 2, '.', ',')) . '</td>';

	/* D2 */
	$result .= '<td align="right">' . htmlentities(number_format($array['11620304_galones'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['11620304_importe'], 2, '.', ',')) . '</td>';

	/* Kerosene */
	$result .= '<td align="right">' . htmlentities(number_format($array['11620306_galones'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['11620306_importe'], 2, '.', ',')) . '</td>';

	/* Total Combustibles */
	$result .= '<td align="right">' . htmlentities(number_format($array['total_galones'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['total_importe'], 2, '.', ',')) . '</td>';

	/* GLP */
	$result .= '<td align="right">' . htmlentities(number_format($array['11620307_galones'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['11620307_importe'], 2, '.', ',')) . '</td>';

	$result .= '<td align="right">' . htmlentities(number_format($array['lubricantes'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['accesorios'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['servicios'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['market'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['whiz'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['ob'], 2, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['otros'], 2, '.', ',')) . '</td>';

	$result .= '<td align="right">' . htmlentities(number_format($array['total'], 2, '.', ',')) . '</td>';
	$result .= '</tr>';
	
	return $result;
    }
    
    function reportePDF($results, $desde, $hasta)
    {
	$cab1 = Array(
		"84"		=> "84",
		"90"		=> "90",
		"95"		=> "95",
		"97"		=> "97",
		"D2"		=> "D2",
		"K"		=> "K",
		"total_comb"	=> "Total Combustible",
		"GLP"		=> "GLP",
	);

        $cab1_col = array(
		"lubricantes"	=> "Lubrican",
		"accesorios"	=> "Accesori",
		"servicios"	=> "Servicio",
		"market"	=> "Market",
		"whiz"		=> "Whiz",
		"ob"		=> "O.B.",
		"otros"		=> "Otros",
		"total"		=> "Total"
	);
        
	$cab2 = Array(
		"fecha"			=> "Dia",
		"11620301_galones"	=> "Galones",
		"11620301_importe"	=> "Importe",
		"11620302_galones"	=> "Galones",
		"11620302_importe"	=> "Importe",
		"11620305_galones"	=> "Galones",
		"11620305_importe"	=> "Importe",
		"11620303_galones"	=> "Galones",
		"11620303_importe"	=> "Importe",
		"11620304_galones"	=> "Galones",
		"11620304_importe"	=> "Importe",
		"11620306_galones"	=> "Galones",
		"11620306_importe"	=> "Importe",
		"total_galones"		=> "Galones",
		"total_importe"		=> "Importe",
		"11620307_galones"	=> "Galones",
		"11620307_importe"	=> "Importe"
	);
        
        $cab2_col = array(
        
		"total_galones"		=> "Galones",
		"total_importe"		=> "Importe",
		"11620307_galones"	=> "Galones",
		"11620307_importe"	=> "Importe"
	);
        
	$reporte = new CReportes2("L", "pt", Array(525.28,810));

	$reporte->definirColumna("fecha", $reporte->TIPO_IMPORTE, 15, "L");
	$reporte->definirColumna("11620301_galones", $reporte->TIPO_IMPORTE, 8, "R");
	$reporte->definirColumna("11620301_importe", $reporte->TIPO_IMPORTE, 8, "R");	
	$reporte->definirColumna("11620302_galones", $reporte->TIPO_IMPORTE, 8, "R");
	$reporte->definirColumna("11620302_importe", $reporte->TIPO_IMPORTE, 8, "R");	
	$reporte->definirColumna("11620305_galones", $reporte->TIPO_IMPORTE, 8, "R");
	$reporte->definirColumna("11620305_importe", $reporte->TIPO_IMPORTE, 8, "R");	
	$reporte->definirColumna("11620303_galones", $reporte->TIPO_IMPORTE, 8, "R");
	$reporte->definirColumna("11620303_importe", $reporte->TIPO_IMPORTE, 8, "R");	
	$reporte->definirColumna("11620304_galones", $reporte->TIPO_IMPORTE, 8, "R");
	$reporte->definirColumna("11620304_importe", $reporte->TIPO_IMPORTE, 8, "R");	
	$reporte->definirColumna("11620306_galones", $reporte->TIPO_IMPORTE, 8, "R");
	$reporte->definirColumna("11620306_importe", $reporte->TIPO_IMPORTE, 8, "R");	
	$reporte->definirColumna("total_galones", $reporte->TIPO_IMPORTE, 12, "R");
	$reporte->definirColumna("total_importe", $reporte->TIPO_IMPORTE, 12, "R");	
	$reporte->definirColumna("11620307_galones", $reporte->TIPO_IMPORTE, 8, "R");
	$reporte->definirColumna("11620307_importe", $reporte->TIPO_IMPORTE, 8, "R");

	$reporte->definirColumna("almacen", $reporte->TIPO_TEXTO, 40, "L", "_almacen");
	
	$reporte->definirColumna("blank", $reporte->TIPO_TEXTO, 15, "L", "_cab");
	$reporte->definirColumna("84", $reporte->TIPO_TEXTO, 17, "C", "_cab");
	$reporte->definirColumna("90", $reporte->TIPO_TEXTO, 17, "C", "_cab");
	$reporte->definirColumna("95", $reporte->TIPO_TEXTO, 17, "C", "_cab");
	$reporte->definirColumna("97", $reporte->TIPO_TEXTO, 17, "C", "_cab");
	$reporte->definirColumna("D2", $reporte->TIPO_TEXTO, 17, "C", "_cab");
	$reporte->definirColumna("K", $reporte->TIPO_TEXTO, 17, "C", "_cab");
	$reporte->definirColumna("total_comb", $reporte->TIPO_TEXTO, 25, "C", "_cab");
	$reporte->definirColumna("GLP", $reporte->TIPO_TEXTO, 17, "C", "_cab");

	$reporte->definirCabecera(1, "L", "Sistema Web");
	$reporte->definirCabecera(1, "R", "Pag. %p");
	$reporte->definirCabecera(2, "L", "Usuario: %u");
	$reporte->definirCabecera(2, "R", "%f %h");
	$reporte->definirCabecera(3, "C", "Grafico de Ventas Horas del " . $desde . " al " . $hasta);

	$reporte->definirCabeceraPredeterminada($cab1, "_cab");
	$reporte->definirCabeceraPredeterminada($cab2);

	$reporte->SetFont("courier", "", 8);
	$reporte->SetMargins(0,0,0);
	$reporte->SetAutoPageBreak(true, 0);
	$reporte->AddPage();

	foreach($results['propiedades'] as $a => $almacenes) {
	$reporte->Ln();
	    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		$array = Array("almacen"=>$ch_almacen);
		$reporte->nuevaFila($array, "_almacen");

		foreach($venta['partes'] as $dt_fecha=>$dia) {
		    $dia['fecha'] = $dt_fecha;
		    $reporte->nuevaFila($dia);
		}
	    
		$reporte->nuevaFila($venta['totales']);
            $reporte->Ln();
	    }

	    $almacenes['totales']['fecha'] = "SubTotal Grupo";
	    $reporte->nuevaFila($almacenes['totales']);
	}

	$results['totales']['fecha'] = "Total General";
	$reporte->nuevaFila($results['totales']);

	$reporte->templates = Array();
	$reporte->cabeceraImagen = Array();
	$reporte->cabeceraSize = Array();
	$reporte->cab_default = Array();

	$reporte->definirColumna("blank", $reporte->TIPO_TEXTO, 15, "L");
	$reporte->definirColumna("lubricantes", $reporte->TIPO_ENTERO, 8, "R");
	$reporte->definirColumna("accesorios", $reporte->TIPO_ENTERO, 8, "R");
	$reporte->definirColumna("servicios", $reporte->TIPO_ENTERO, 8, "R");
	$reporte->definirColumna("market", $reporte->TIPO_ENTERO, 8, "R");
	$reporte->definirColumna("whiz", $reporte->TIPO_ENTERO, 8, "R");
	$reporte->definirColumna("ob", $reporte->TIPO_ENTERO, 8, "R");
	$reporte->definirColumna("otros", $reporte->TIPO_ENTERO, 8, "R");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 14, "R");
	
	$reporte->definirColumna("almacen", $reporte->TIPO_TEXTO, 40, "L", "_almacen");

	$reporte->definirColumna("blank", $reporte->TIPO_TEXTO, 15, "L", "_cab_1");
	$reporte->definirColumna("lubricantes", $reporte->TIPO_TEXTO, 8, "C", "_cab_1");
	$reporte->definirColumna("accesorios", $reporte->TIPO_TEXTO, 8, "C", "_cab_1");
	$reporte->definirColumna("servicios", $reporte->TIPO_TEXTO, 8, "C", "_cab_1");
	$reporte->definirColumna("market", $reporte->TIPO_TEXTO, 8, "C", "_cab_1");
	$reporte->definirColumna("whiz", $reporte->TIPO_TEXTO, 8, "C", "_cab_1");
	$reporte->definirColumna("ob", $reporte->TIPO_TEXTO, 8, "C", "_cab_1");
	$reporte->definirColumna("otros", $reporte->TIPO_TEXTO, 8, "C", "_cab_1");
	$reporte->definirColumna("total", $reporte->TIPO_TEXTO, 14, "C", "_cab_1");

	$reporte->definirCabeceraPredeterminada($cab1_col, "_cab_1");
	$reporte->definirCabeceraPredeterminada($cab2_col);
	$reporte->AddPage();
	foreach($results['propiedades'] as $a => $almacenes) {
	$reporte->Ln();
	    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		$array = Array("almacen"=>$ch_almacen);
		$reporte->nuevaFila($array, "_almacen");

		/*foreach($venta['partes'] as $dt_fecha=>$dia) {
		    $dia['fecha'] = $dt_fecha;
		    $reporte->nuevaFila($dia);
		}*/
	    
		$reporte->nuevaFila($venta['totales']);
            $reporte->Ln();
	    }

	    $almacenes['totales']['fecha'] = "SubTotal Grupo";
	    $reporte->nuevaFila($almacenes['totales']);
	}

	$results['totales']['fecha'] = "Total General";
	$reporte->nuevaFila($results['totales']);

	$reporte->Output();
	exit;
    }
}

