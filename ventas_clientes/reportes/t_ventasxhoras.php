<?php

class VentasxHorasTemplate extends Template
{

    function search_form(){
	$ayer = time()-(24*60*60);
	$fecha = date("d/m/Y", $ayer);

	$estaciones = VentasxHorasModel::obtieneListaEstaciones();
	$estaciones['TODAS'] = "Todas las estaciones";

	$diasemana = VentasxHorasModel::obtieneDiasSemana();
	$diasemana['TODOS'] = "Todos los Dias";

	$producto = VentasxHorasModel::obtieneProductos();
	$producto['TODOS'] = "Todos los Productos";

	$lado = VentasxHorasModel::obtieneLados();
	$lado['TODOS'] = "Todos los Lados";

	$form = new form2("Reporte de Ventas por Horas", "form_ventas_xhoras", FORM_METHOD_POST, "control.php", '', "control");
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.VENTASXHORAS"));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $fecha, '', 10, 12));
	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_ventas_xhoras.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha, '', 10, 12));
	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_ventas_xhoras.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>&nbsp;</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("diasemana", "Dia:", "TODOS", $diasemana, espacios(3)));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("producto", "Producto:", "TODOS", $producto, espacios(3)));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td  colspan="2" align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("lado", "Lado:", "TODOS", $lado, espacios(3)));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "TODAS", $estaciones, espacios(6)));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td  align="right">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("local","Combustible", "COMBUSTIBLE", '<br>', '', Array("checked")));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("local","Market", "MARKET", ''));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td  align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("importe","Importe", "IMPORTE", '<br>', '', Array("checked")));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("importe","Cantidad", "CANTIDAD", ''));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td  align="right">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("modo", "Resumido", "RESUMIDO", '<br>', '', Array("checked")));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("modo", "Detallado", "DETALLADO", ''));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", ""));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	return $form->getForm();
    }
    
    function reporte($results, $desde, $hasta,$diasemana,$producto, $lado, $estacion, $local, $importe, $modo) {
	//$result  = '<a href="control.php?rqst=REPORTES.VENTASXHORAS&action=pdf&desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta) . '&diasemana=' . urlencode($diasemana). '&producto=' . urlencode($producto) . '&lado=' . urlencode($lado) . '&estacion=' . urlencode($estaciones) . '&local=' . urlencode($local). '&importe=' . urlencode($importe). '&modo=' . urlencode($modo) .'" target="_blank">Imprimir</a>';
	$result = '<table border="1" align="center">';
	$result .= '<tr>';
	$result .= '<td>HORAS -> </td>';
	for($i=0;$i<24;$i++){
		$result .= '<td align="center">'.substr('0'.$i, -2).'</td>';
	}
	$result .= '<td align="center">TOTAL</td>';
	$result .= '<td align="center">PROMEDIO</td>';
	$result .= '</tr><tr>';
	$result .= '<td>D&Iacute;AS</td><td colspan="26">&nbsp;</td>';
	$result .= '</tr>';
	$numfilas = 0;
	foreach($results['propiedades'] as $a => $almacenes) {
	    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
		$result .= '<tr>';
		$result .= '<td colspan="27">' . $ch_almacen . '</td>';
		$result .= '</tr>';

		foreach($venta['partes'] as $dt_fecha=>$dia) {
                    $numfilas= $numfilas +1;
		    $result .= VentasxHorasTemplate::imprimirLinea($dia, $dt_fecha);
		}
	    
		//$result .= VentasxHorasTemplate::imprimirLinea($venta['totales'], "");
                $result .= '<tr><td colspan="27">&nbsp;</td></tr>';
	    }

	    //$result .= VentasxHorasTemplate::imprimirLinea($almacenes['totales'], "Sub-Total Grupo");
	    //$result .= '<tr><td colspan="27">&nbsp;</td></tr>';
	}

	$result .= VentasxHorasTemplate::imprimirLinea($results['totales'], "Total General");
	$result .= VentasxHorasTemplate::imprimirLinea($results['promedio'], "Promedio");
	$result .= VentasxHorasTemplate::imprimirLinea($results['porcentaje'], "Porcentaje (%)");
	$result .= '</table>';
	
	return $result;
    }
    
    function imprimirLinea($array, $label)
    {
	$result  = '<tr>';
	$result .= '<td>' . htmlentities($label) . '</td>';
        $decimal = 0;
	if ($label == 'Porcentaje (%)'){$decimal = 2;}
	for($i=0;$i<24;$i++){
		$result .= '<td align="right">' . htmlentities(number_format($array[$i], $decimal, '.', ',')) . '</td>';
	}
	$result .= '<td align="right">' . htmlentities(number_format($array['total'], $decimal, '.', ',')) . '</td>';
	$result .= '<td align="right">' . htmlentities(number_format($array['promedio'], $decimal, '.', ',')) . '</td>';
	$result .= '</tr>';
	return $result;
    }

    function reportePDF($results, $desde, $hasta, $diasemana, $producto, $lado, $estaciones,$local,$importe, $bResumido)
    {

	/*$cab1 = Array(
		"00" = > "00",
		"01" = > "01",
		"02" = > "02",
		"03" = > "03",
		"04" = > "04",
		"05" = > "05",
		"06" = > "06",
		"07" = > "07",
		"08" = > "08",
		"09" = > "09",
		"10" = > "10",
		"11" = > "11",
		"12" = > "12",
		"13" = > "13",
		"14" = > "14",
		"15" = > "15",
		"16" = > "16",
		"17" = > "17",
		"18" = > "18",
		"19" = > "19",
		"20" = > "20",
		"21" = > "21",
		"22" = > "22",
		"23" = > "23"
	);
        
	$cab2 = Array(
		"fecha"			=> "Dia"
	);

        
	$reporte = new CReportes2("L", "pt", Array(525.27,810));

	$reporte->definirColumna("fecha", $reporte->TIPO_IMPORTE, 15, "L");

	$reporte->definirColumna("almacen", $reporte->TIPO_TEXTO, 40, "L", "_almacen");
	
	$reporte->definirColumna("blank", $reporte->TIPO_TEXTO, 15, "L", "_cab");
	for($i=0;$i<24;$i++){
		$reporte->definirColumna("".substr('0'.$i, -2)."", $reporte->TIPO_TEXTO, 17, "C", "_cab");
	}

	$reporte->definirCabecera(1, "L", "Sistema Web");
	$reporte->definirCabecera(1, "R", "Pag. %p");
	$reporte->definirCabecera(2, "L", "Usuario: %u");
	$reporte->definirCabecera(2, "R", "%f %h");
	$reporte->definirCabecera(3, "C", "Reporte de Ventas por Horas del " . $desde . " al " . $hasta);

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

	$reporte->definirColumna("blank", $reporte->TIPO_TEXTO, 15, "L", "_cab_1");
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

	$reporte->Output();
	exit;*/
    }
}

