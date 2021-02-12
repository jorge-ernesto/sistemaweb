<?php

class ExVentasTemplate extends Template
{
    function titulo()
    {
	return '<h2><b>Existencia de Combustitles</b></h2>';
    }
    
    function formSearch()
    {
	$hoy = date("d/m/Y");

	$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.EXVENTAS"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "search"));
	$form->addGroup("FORM_GROUP_FECHA", "A la fecha:");
	$form->addElement("FORM_GROUP_FECHA", new form_element_text("", "fecha", $hoy, '', '', 10, 12));
	$form->addElement("FORM_GROUP_FECHA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement("FORM_GROUP_FECHA", new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
	$form->addGroup("FORM_GROUP_TIPO_REPORTE", "Dias para promedio de venta:");
	$form->addElement("FORM_GROUP_TIPO_REPORTE", new form_element_text('', "dias", "5", '', '', 2, 3));
	$form->addGroup("FORM_GROUP_BOTONES", "");
	$form->addElement("FORM_GROUP_BOTONES", new form_element_submit("submit", "Buscar", "", "", ""));
	return $form->getForm();
    }
    
    function listado($resultado, $fecha, $dias)
    {
    
	//$result = '<a href="control.php?rqst=REPORTES.EXVENTAS&action=PDF&fecha=' . htmlentities($fecha) . '&dias=' . htmlentities($dias) . '" target="_blank">Imprimir</a><br>';

	$result = '<button name="fm" value="" onClick="javascript:parent.location.href=\'control.php?rqst=REPORTES.EXVENTAS&fecha=' . htmlentities($fecha).'&action=PDF\';return false"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>';
	
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<th colspan="17">EXISTENCIA DE COMBUSTIBLES AL: ' . htmlentities($fecha) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>ESTACION</td>';
	$result .= '<td>84</td>';
	$result .= '<td>(%)</td>';
	$result .= '<td>90</td>';
	$result .= '<td>(%)</td>';
	$result .= '<td>95</td>';
	$result .= '<td>(%)</td>';
	$result .= '<td>97</td>';
	$result .= '<td>(%)</td>';
	$result .= '<td>D2</td>';
	$result .= '<td>(%)</td>';
	$result .= '<td>D1</td>';
	$result .= '<td>(%)</td>';
	$result .= '<td>TOT.COMB.</td>';
	$result .= '<td>(%)</td>';
	$result .= '<td>GLP</td>';
	$result .= '<td>(%)</td>';
	$result .= '</tr>';
	
	foreach($resultado['sucursales'] as $ch_sucursal => $z) {
	    $result .= '<tr>';
	    $result .= '<td>' . htmlentities($ch_sucursal) . '</td>';
	    
	    $productos = $z['productos'];
												// anteriormente: 
	    $result .= '<td>' . htmlentities($productos['11620301_medicion']) . '</td>'; 	// $productos['84 OCTANOS_medicion']
	    $result .= '<td>' . htmlentities($productos['11620301_porcentaje']) . '</td>';	// $productos['84 OCTANOS_porcentaje']
	    $result .= '<td>' . htmlentities($productos['11620302_medicion']) . '</td>';	// $productos['90 OCTANOS_medicion']
	    $result .= '<td>' . htmlentities($productos['11620302_porcentaje']) . '</td>';	// $productos['90 OCTANOS_porcentaje']
	    $result .= '<td>' . htmlentities($productos['11620305_medicion']) . '</td>';	// $productos['95 OCTANOS_medicion']
	    $result .= '<td>' . htmlentities($productos['11620305_porcentaje']) . '</td>';	// $productos['95 OCTANOS_porcentaje']
	    $result .= '<td>' . htmlentities($productos['11620303_medicion']) . '</td>';	// $productos['97 OCTANOS_medicion']
	    $result .= '<td>' . htmlentities($productos['11620303_porcentaje']) . '</td>';	// $productos['97 OCTANOS_porcentaje']
	    $result .= '<td>' . htmlentities($productos['11620304_medicion']) . '</td>';	// $productos['D2 DIESEL_medicion']
	    $result .= '<td>' . htmlentities($productos['11620304_porcentaje']) . '</td>';	// $productos['D2 DIESEL_porcentaje']
	    $result .= '<td>' . htmlentities($productos['11620306_medicion']) . '</td>';	// $productos['KEROSENE_medicion']
	    $result .= '<td>' . htmlentities($productos['11620306_porcentaje']) . '</td>';	// $productos['KEROSENE_porcentaje']
	    $result .= '<td>' . htmlentities($z['totales']['medicion']) . '</td>';		
	    $result .= '<td>' . htmlentities($z['totales']['porcentaje']) . '</td>';		
	    $result .= '<td>' . htmlentities($productos['11620307_medicion']) . '</td>';	// $productos['GLP_medicion']
	    $result .= '<td>' . htmlentities($productos['11620307_porcentaje']) . '</td>';	// $productos['GLP_porcentaje']   
	    $result .= '</tr>';
	    
	    $result .= '<tr>';
	    $result .= '<td>VENTA PROM.</td>';
	    $result .= '<td>' . htmlentities($productos['GASOHOL 84_promedio']) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td>' . htmlentities($productos['90 OCTANOS_promedio']) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td>' . htmlentities($productos['95 OCTANOS_promedio']) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td>' . htmlentities($productos['97 OCTANOS_promedio']) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td>' . htmlentities($productos['D2 DIESEL_promedio']) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td>' . htmlentities($productos['KEROSENE_promedio']) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td>' . htmlentities($z['totales']['promedio']) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '<td>' . htmlentities($productos['GLP_promedio']) . '</td>';
	    $result .= '<td>&nbsp;</td>';
	    $result .= '</tr>';
	    $result .= '<tr><td colspan="17">&nbsp;</td></tr>';
	}

//	$result .= '
	$result .= '</table>';
	return $result;
    }

    function reportePDF($resultado, $fecha)
    {
	$cabecera = Array(
		    "ESTACION"			=> "ESTACION",
		    "84 OCTANOS_medicion"	=>	"84",
		    "84 OCTANOS_porcentaje"	=>	"(%)",
		    "90 OCTANOS_medicion"	=>	"90",
		    "90 OCTANOS_porcentaje"	=>	"(%)",
		    "95 OCTANOS_medicion"	=>	"95",
		    "95 OCTANOS_porcentaje"	=>	"(%)",
		    "97 OCTANOS_medicion"	=>	"97",
		    "97 OCTANOS_porcentaje"	=>	"(%)",
		    "D2 DIESEL_medicion"	=>	"D2",
		    "D2 DIESEL_porcentaje"	=>	"(%)",
		    "KEROSENE_medicion"		=>	"D1",
		    "KEROSENE_porcentaje"	=>	"(%)",
		    "TOTAL_medicion"		=>	"TOT.COMB.",
		    "TOTAL_porcentaje"		=>	"(%)",
		    "GLP_medicion"		=>	"GLP",
		    "GLP_porcentaje"		=>	"(%)"
		    );

	$liniecitas = Array(
		    "label"			=>	"LLEGA",
		    "space1"			=>	"___________",
		    "space2"			=>	"___________",
		    "space3"			=>	"___________",
		    "space4"			=>	"___________",
		    "space5"			=>	"___________",
		    "space6"			=>	"___________",
		    "space7"			=>	"___________",
		    "space8"			=>	"___________"
		    );

	$reporte = new CReportes2();	
	$reporte->definirColumna("ESTACION", $reporte->TIPO_TEXTO, 12, "L");
	$reporte->definirColumna("84 OCTANOS_medicion", $reporte->TIPO_TEXTO, 7, "R");
	$reporte->definirColumna("84 OCTANOS_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
	$reporte->definirColumna("90 OCTANOS_medicion", $reporte->TIPO_TEXTO, 7, "R");
	$reporte->definirColumna("90 OCTANOS_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
	$reporte->definirColumna("95 OCTANOS_medicion", $reporte->TIPO_TEXTO, 7, "R");
	$reporte->definirColumna("95 OCTANOS_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
	$reporte->definirColumna("97 OCTANOS_medicion", $reporte->TIPO_TEXTO, 7, "R");
	$reporte->definirColumna("97 OCTANOS_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
	$reporte->definirColumna("D2 DIESEL_medicion", $reporte->TIPO_TEXTO, 7, "R");
	$reporte->definirColumna("D2 DIESEL_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
	$reporte->definirColumna("KEROSENE_medicion", $reporte->TIPO_TEXTO, 7, "R");
	$reporte->definirColumna("KEROSENE_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
	$reporte->definirColumna("TOTAL_medicion", $reporte->TIPO_TEXTO, 7, "R");
	$reporte->definirColumna("TOTAL_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
	$reporte->definirColumna("GLP_medicion", $reporte->TIPO_TEXTO, 7, "R");
	$reporte->definirColumna("GLP_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
	
	$reporte->definirColumna("promedio", $reporte->TIPO_TEXTO, 12, "L", "_promedio");
	$reporte->definirColumna("84 OCTANOS_promedio", $reporte->TIPO_TEXTO, 7, "R", "_promedio");
	$reporte->definirColumna("dummy84", $reporte->TIPO_TEXTO, 3, "C", "_promedio");
	$reporte->definirColumna("90 OCTANOS_promedio", $reporte->TIPO_TEXTO, 7, "R", "_promedio");
	$reporte->definirColumna("dummy90", $reporte->TIPO_TEXTO, 3, "C", "_promedio");
	$reporte->definirColumna("95 OCTANOS_promedio", $reporte->TIPO_TEXTO, 7, "R", "_promedio");
	$reporte->definirColumna("dummy95", $reporte->TIPO_TEXTO, 3, "C", "_promedio");
	$reporte->definirColumna("97 OCTANOS_promedio", $reporte->TIPO_TEXTO, 7, "R", "_promedio");
	$reporte->definirColumna("dummy97", $reporte->TIPO_TEXTO, 3, "C", "_promedio");
	$reporte->definirColumna("D2 DIESEL_promedio", $reporte->TIPO_TEXTO, 7, "R", "_promedio");
	$reporte->definirColumna("dummyd2", $reporte->TIPO_TEXTO, 3, "C", "_promedio");
	$reporte->definirColumna("KEROSENE_promedio", $reporte->TIPO_TEXTO, 7, "R", "_promedio");
	$reporte->definirColumna("dummyd1", $reporte->TIPO_TEXTO, 3, "C", "_promedio");
	$reporte->definirColumna("TOTAL_promedio", $reporte->TIPO_TEXTO, 7, "R", "_promedio");
	$reporte->definirColumna("dummytot", $reporte->TIPO_TEXTO, 3, "C", "_promedio");
	$reporte->definirColumna("GLP_promedio", $reporte->TIPO_TEXTO, 7, "R", "_promedio");
	
	$reporte->definirColumna("capacidad", $reporte->TIPO_TEXTO, 12, "L", "_capacidad");
	$reporte->definirColumna("84 OCTANOS_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
	$reporte->definirColumna("dummy84", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
	$reporte->definirColumna("90 OCTANOS_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
	$reporte->definirColumna("dummy90", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
	$reporte->definirColumna("95 OCTANOS_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
	$reporte->definirColumna("dummy95", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
	$reporte->definirColumna("97 OCTANOS_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
	$reporte->definirColumna("dummy97", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
	$reporte->definirColumna("D2 DIESEL_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
	$reporte->definirColumna("dummyd2", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
	$reporte->definirColumna("KEROSENE_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
	$reporte->definirColumna("dummyd1", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
	$reporte->definirColumna("TOTAL_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
	$reporte->definirColumna("dummytot", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
	$reporte->definirColumna("GLP_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");

	$reporte->definirColumna("label", $reporte->TIPO_TEXTO, 12, "L", "_labels");
	$reporte->definirColumna("space1", $reporte->TIPO_TEXTO, 11, "L", "_labels");
	$reporte->definirColumna("space2", $reporte->TIPO_TEXTO, 11, "L", "_labels");
	$reporte->definirColumna("space3", $reporte->TIPO_TEXTO, 11, "L", "_labels");
	$reporte->definirColumna("space4", $reporte->TIPO_TEXTO, 11, "L", "_labels");
	$reporte->definirColumna("space5", $reporte->TIPO_TEXTO, 11, "L", "_labels");
	$reporte->definirColumna("space6", $reporte->TIPO_TEXTO, 11, "L", "_labels");
	$reporte->definirColumna("space7", $reporte->TIPO_TEXTO, 11, "L", "_labels");
	$reporte->definirColumna("space8", $reporte->TIPO_TEXTO, 11, "L", "_labels");

	$reporte->definirCabecera(1, "L", "SISTEMA WEB");
	$reporte->definirCabecera(1, "R", "%f");
	$reporte->definirCabecera(2, "C", "EXISTENCIA DE COMBUSTIBLES CON VENTA PROMEDIO AL: " . $fecha);

	$reporte->definirCabeceraPredeterminada($cabecera);

	$reporte->SetFont("courier", "", 8);
	$reporte->AddPage();

	foreach($resultado['sucursales'] as $cod_sucursal => $sucursal) {
	    $fila = $sucursal['productos'];
	    
	    $fila['ESTACION'] = $cod_sucursal;
	    $fila['TOTAL_medicion'] = $sucursal['totales']['medicion'];
	    $fila['TOTAL_porcentaje'] = $sucursal['totales']['porcentaje'];
	    $reporte->nuevaFila($fila);

	    $fila['promedio'] = "VENTA PROM.";
	    $fila['TOTAL_promedio'] = $sucursal['totales']['promedio'];
	    $reporte->nuevaFila($fila, "_promedio");
	    
	    $liniecitas['label'] = "LLEGA";
	    $reporte->nuevaFila($liniecitas, "_labels");
	    $liniecitas['label'] = "PEDIDO";
	    $reporte->nuevaFila($liniecitas, "_labels");
		    
	    $reporte->Ln();
	}
	
	$reporte->Ln();
	$reporte->lineaH();
	
	$resultado['totales']['ESTACION'] = "TOTALES:";
	$reporte->nuevaFila($resultado['totales']);
	
	$resultado['totales']['capacidad'] = "Capacidad";
	$reporte->nuevaFila($resultado['totales'], "_capacidad");
	
	$reporte->lineaH();

	$reporte->Output();
    }    
}

