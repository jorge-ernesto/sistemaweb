<?php

class VentasDiariasTemplate extends Template{

	function titulo() {
        return '<div align="center"><h2><b>Reporte de Ventas Diarias</b></h2></div>';
	}

	function search_form($estaciones){
    	$desde	= date("d/m/Y", strtotime('-1 day'));
    	$hasta	= date("d/m/Y", strtotime('-1 day'));

		$estaciones['TODAS'] = "Todas las estaciones";

		$form = new form2("", "form_ventas_diarias", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.VENTASDIARIAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Seleccionar Almac&eacute;n: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Fecha Inicio: </td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<td>
			<input type="text" name="desde" id="desde" maxlength="10" size="10" class="fecha_formato" value="'.(empty($_REQUEST['desde']) ? $desde : $_REQUEST['desde']).'" />	
		</td>
		</tr>'
		));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Fecha Final: </td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<td>
			<input type="text" name="hasta" id="hasta" maxlength="10" size="10" class="fecha_formato" value="'.(empty($_REQUEST['hasta']) ? $hasta : $_REQUEST['hasta']).'" />	
		</td>
		</tr>'
		));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Tipo Reporte: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio("modo", "Detallado", "DETALLADO", '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '', Array("checked")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio("modo", "Resumido", "RESUMIDO", ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center"><br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Reporte"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="pdf"><img src="/sistemaweb/images/icono_pdf.gif" align="right"/> PDF</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Excel</button>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="AsientosContablesSiigo"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Asientos Contables Siigo</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		return $form->getForm();
	}

	function reporte($results, $desde, $hasta, $modo, $estacion) {

		//$result = '<button name="fm" value="" onClick="javascript:parent.location.href=\'control.php?rqst=REPORTES.VENTASDIARIAS&action=pdf&desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta) . '&modo=' . urlencode($modo) . '&estacion=' . urlencode($estacion) . '\';return false"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>';
		$result = '';
		$result .= '<table align="center" border="0">';
		$result .= '<tr>';
		$result .= '<td class="theader">&nbsp;</td>';
		$result .= '<td class="theader" align="center" colspan="2">84</td>';
		$result .= '<td class="theader" align="center" colspan="2">90</td>';
		$result .= '<td class="theader" align="center" colspan="2">95</td>';
		$result .= '<td class="theader" align="center" colspan="2">'.substr($results['producto']['art_descbreve'],8).'</td>';
		$result .= '<td class="theader" align="center" colspan="2">D2</td>';
		$result .= '<td class="theader" align="center" colspan="2">K</td>';
		$result .= '<td class="theader" align="center" colspan="2">Total Combustible</td>';
		$result .= '<td class="theader" align="center" colspan="2">GLP</td>';
		$result .= '<td class="theader" align="center">Lubricantes</td>';
		$result .= '<td class="theader" align="center">Accesorios</td>';
		$result .= '<td class="theader" align="center">Servicios</td>';
		$result .= '<td class="theader" align="center">Market</td>';
		$result .= '<td class="theader" align="center">Whiz</td>';
		$result .= '<td class="theader" align="center">O.B.</td>';
		$result .= '<td class="theader" align="center">Otros</td>';
		$result .= '<td class="theader" align="center">Total</td>';
		$result .= '</tr><tr>';
		$result .= '<td class="theader">Dia</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Importe</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Importe</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Importe</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Importe</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Importe</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Importe</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Importe</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Importe</td>';
		$result .= '<td class="theader" colspan="8">&nbsp;</td>';
		$result .= '</tr>';
	
		foreach($results['propiedades'] as $a => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				$result .= '<tr>';
				$result .= '<td class="theader">Almacen: </td><td class="theader" colspan="24">' . $ch_almacen . '</td>';
				$result .= '</tr>';
				$i = 0;

				foreach($venta['partes'] as $dt_fecha=>$dia) {
					$result .= $this->imprimirLinea($dia, $dt_fecha,$i,0);
					$i++;
				}
		    }
		    $result .= $this->imprimirLinea($almacenes['totales'], "Sub-Total: ",0,2);
		}

		$result .= $this->imprimirLinea($results['totales'], "Total: ",0,2);
		$result .= '</table>';

		return $result;
	}
    
	function imprimirLinea($array, $fecha, $i,$f){

		if($f==2){
			$estilo = "theader";
			$align = "right";
		}else {
			$estilo = "tbodyimpar";
			if ($i % 2 == 0)
				$estilo = "tbodypar";
			$align = "center";
		}

		$result  = '<tr>';

		$result .= '<td align="'.$align.'" class="'.$estilo.'">' . htmlentities($fecha) . '</td>';
		
		/* 84 */
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620301_galones'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620301_importe'], 2, '.', ',')) . '</td>';
	    
		/* 90 */
	    	$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620302_galones'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620302_importe'], 2, '.', ',')) . '</td>';

		/* 95 */
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620305_galones'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620305_importe'], 2, '.', ',')) . '</td>';

		/* 97 */
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['11620303_galones']) ? $array['11620303_galones'] : 0), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['11620303_importe']) ? $array['11620303_importe'] : 0), 2, '.', ',')) . '</td>';

		/* D2 */
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620304_galones'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620304_importe'], 2, '.', ',')) . '</td>';

		/* Kerosene */
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['11620306_galones']) ? $array['11620306_galones'] : 0), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['11620306_importe']) ? $array['11620306_importe'] : 0), 2, '.', ',')) . '</td>';

		/* Total Combustibles */
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['total_galones'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['total_importe'], 2, '.', ',')) . '</td>';

		/* GLP */
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620307_galones'], 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['11620307_importe'], 2, '.', ',')) . '</td>';

		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['lubricantes']) ? $array['lubricantes'] : 0), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['accesorios']) ? $array['accesorios'] : 0), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['servicios']) ? $array['servicios'] : 0), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['market']) ? $array['market'] : 0), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['whiz']) ? $array['whiz'] : 0), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['ob']) ? $array['ob'] : 0), 2, '.', ',')) . '</td>';
		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format((isset($array['otros']) ? $array['otros'] : 0), 2, '.', ',')) . '</td>';

		$result .= '<td align="right" class="'.$estilo.'">' . htmlentities(number_format($array['total'], 2, '.', ',')) . '</td>';
		$result .= '</tr>';
		
		return $result;
	}
    
	function reportePDF($results, $desde, $hasta){

		$cab1 = Array(
			"84"		=> "84",
			"90"		=> "90",
			"95"		=> "95",
			"98"		=> "98",
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

		$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 15, "L");
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
		$reporte->definirColumna("98", $reporte->TIPO_TEXTO, 17, "C", "_cab");
		$reporte->definirColumna("D2", $reporte->TIPO_TEXTO, 17, "C", "_cab");
		$reporte->definirColumna("K", $reporte->TIPO_TEXTO, 17, "C", "_cab");
		$reporte->definirColumna("total_comb", $reporte->TIPO_TEXTO, 25, "C", "_cab");
		$reporte->definirColumna("GLP", $reporte->TIPO_TEXTO, 17, "C", "_cab");

		$reporte->definirCabecera(1, "L", "Sistema Web");
		$reporte->definirCabecera(1, "R", "Pag. %p");
		$reporte->definirCabecera(2, "L", "Usuario: %u");
		$reporte->definirCabecera(2, "R", "%f %h");
		$reporte->definirCabecera(3, "C", "Reporte de Ventas Diarias del " . $desde . " al " . $hasta);

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
					echo "<script>console.log('" . json_encode($dia['fecha']) . "')</script>";
					echo "<script>console.log('" . json_encode($dia) . "')</script>";
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
			    
				$reporte->nuevaFila($venta['totales']);
			    $reporte->Ln();
		    }
		    $almacenes['totales']['fecha'] = "SubTotal Grupo";
		    $reporte->nuevaFila($almacenes['totales']);
		}

		$results['totales']['fecha'] = "Total General";
		$reporte->nuevaFila($results['totales']);
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/VentasDiarias.pdf", "F");
		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/VentasDiarias.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
		exit;
	}

	function reporteExcel($resultados, $alma, $dia1, $dia2) {
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

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 12);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 12);
		$worksheet1->set_column(6, 6, 12);
		$worksheet1->set_column(7, 7, 12);
		$worksheet1->set_column(8, 8, 12);
		$worksheet1->set_column(9, 9, 12);
		$worksheet1->set_column(10, 10, 12);
		$worksheet1->set_column(11, 11, 12);
		$worksheet1->set_column(12, 12, 12);
		$worksheet1->set_column(13, 13, 20);
		$worksheet1->set_column(14, 14, 20);
		$worksheet1->set_column(15, 15, 12);
		$worksheet1->set_column(16, 16, 12);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "REPORTE DE VENTAS DIARIAS",$formato0);
		$worksheet1->write_string(3, 0, "FECHA DEL ".$dia1."   AL   ".$dia2,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 6;
		
		$worksheet1->write_string($a, 0, "DIA",$formato2);
		$worksheet1->write_string($a, 1, "GALONES 84",$formato2);
		$worksheet1->write_string($a, 2, "IMPORTE 84",$formato2);
		$worksheet1->write_string($a, 3, "GALONES 90",$formato2);	
		$worksheet1->write_string($a, 4, "IMPORTE 90",$formato2);
		$worksheet1->write_string($a, 5, "GALONES 95",$formato2);
		$worksheet1->write_string($a, 6, "IMPORTE 95",$formato2);
		$worksheet1->write_string($a, 7, "GALONES 97",$formato2);
		$worksheet1->write_string($a, 8, "IMPORTE 97",$formato2);
		$worksheet1->write_string($a, 9, "GALONES D2",$formato2);
		$worksheet1->write_string($a, 10, "IMPORTE D2",$formato2);
		$worksheet1->write_string($a, 11, "GALONES K",$formato2);
		$worksheet1->write_string($a, 12, "IMPORTE K",$formato2);
		$worksheet1->write_string($a, 13, "GALONES TOTAL COMB",$formato2);
		$worksheet1->write_string($a, 14, "IMPORTE TOTAL COMB",$formato2);
		$worksheet1->write_string($a, 15, "GALONES GLP",$formato2);
		$worksheet1->write_string($a, 16, "IMPORTE GLP",$formato2);
		$worksheet1->write_string($a, 17, "LUBRICANTES",$formato2);
		$worksheet1->write_string($a, 18, "ACCESORIOS",$formato2);
		$worksheet1->write_string($a, 19, "SERVICIOS",$formato2);
		$worksheet1->write_string($a, 20, "MARKET",$formato2);
		$worksheet1->write_string($a, 21, "WHIZ",$formato2);
		$worksheet1->write_string($a, 22, "O.B.",$formato2);
		$worksheet1->write_string($a, 23, "OTROS",$formato2);	
		$worksheet1->write_string($a, 24, "TOTAL",$formato2);													

		$a = 7;	

		foreach($resultados['propiedades'] as $estaciones => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen => $venta) {
		    	if ($ch_almacen == '')
		    		$worksheet1->write_string($a, 0, "-", $formato5);	
		    	else
		    		$worksheet1->write_string($a, 0, $ch_almacen, $formato5);

		    	$a++;

				foreach($venta['partes'] as $dt_fecha=>$dia) {

					$total_gal = $dia['11620301_galones'] + $dia['11620302_galones'] + $dia['11620304_galones'] + $dia['11620305_galones'] + $dia['11620303_galones'];
					$total_imp = $dia['11620301_importe'] + $dia['11620302_importe'] + $dia['11620304_importe'] + $dia['11620305_importe'] + $dia['11620303_importe'];
					$tt = $total_imp + $dia['11620307_importe'] + $dia['lubricantes'] + $dia['accesorios'] + $dia['servicios'] + $dia['market'];

					$worksheet1->write_string($a, 0, $dt_fecha, $formato5);
					$worksheet1->write_string($a, 1, $dia['11620301_galones'], $formato5);
				    $worksheet1->write_string($a, 2, $dia['11620301_importe'], $formato5);
				    $worksheet1->write_string($a, 3, $dia['11620302_galones'], $formato5);
				    $worksheet1->write_string($a, 4, $dia['11620302_importe'], $formato5);
				    $worksheet1->write_string($a, 5, $dia['11620305_galones'], $formato5);
				    $worksheet1->write_string($a, 6, $dia['11620305_importe'], $formato5);
				    $worksheet1->write_string($a, 7, $dia['11620303_galones'], $formato5);
				    $worksheet1->write_string($a, 8, $dia['11620303_importe'], $formato5);	
				    $worksheet1->write_string($a, 9, $dia['11620304_galones'], $formato5);
				    $worksheet1->write_string($a, 10, $dia['11620304_importe'], $formato5);
				    $worksheet1->write_string($a, 11, $dia['11620306_galones'], $formato5);
				    $worksheet1->write_string($a, 12, $dia['11620306_importe'], $formato5);
				    $worksheet1->write_string($a, 13, $total_gal, $formato5);
				    $worksheet1->write_string($a, 14, $total_imp, $formato5);
				    $worksheet1->write_string($a, 15, $dia['11620307_galones'], $formato5);
				    $worksheet1->write_string($a, 16, $dia['11620307_importe'], $formato5);	
				    $worksheet1->write_string($a, 17, $dia['lubricantes'], $formato5);
				    $worksheet1->write_string($a, 18, $dia['accesorios'], $formato5);
				    $worksheet1->write_string($a, 19, $dia['servicios'], $formato5);
				    $worksheet1->write_string($a, 20, $dia['market'], $formato5);
				    $worksheet1->write_string($a, 21, $dia['whiz'], $formato5);
				    $worksheet1->write_string($a, 22, $dia['ob'], $formato5);
				    $worksheet1->write_string($a, 23, $dia['otros'], $formato5);
				    $worksheet1->write_string($a, 24, $tt, $formato5);								
					$a++;
					$tttotal = $tt + $tttotal;
				}
		  	}
		}

		$a++;

		foreach($resultados['propiedades'] as $estaciones => $almacenes) {
		    foreach($almacenes['almacenes'] as $ch_almacen => $venta) {
				$worksheet1->write_string($a, 0, "TOTALES:", $formato5);
				$worksheet1->write_string($a, 1, $venta['totales']['11620301_galones'], $formato5);
				$worksheet1->write_string($a, 2, $venta['totales']['11620301_importe'], $formato5);
				$worksheet1->write_string($a, 3, $venta['totales']['11620302_galones'], $formato5);
				$worksheet1->write_string($a, 4, $venta['totales']['11620302_importe'], $formato5);
				$worksheet1->write_string($a, 5, $venta['totales']['11620305_galones'], $formato5);
				$worksheet1->write_string($a, 6, $venta['totales']['11620305_importe'], $formato5);
				$worksheet1->write_string($a, 7, $venta['totales']['11620303_galones'], $formato5);
				$worksheet1->write_string($a, 8, $venta['totales']['11620303_importe'], $formato5);
				$worksheet1->write_string($a, 9, $venta['totales']['11620304_galones'], $formato5);
				$worksheet1->write_string($a, 10, $venta['totales']['11620304_importe'], $formato5);
				$worksheet1->write_string($a, 11, $venta['totales']['11620306_galones'], $formato5);
				$worksheet1->write_string($a, 12, $venta['totales']['11620306_importe'], $formato5);
				$worksheet1->write_string($a, 13, $venta['totales']['total_galones'], $formato5);
				$worksheet1->write_string($a, 14, $venta['totales']['total_importe'], $formato5);
				$worksheet1->write_string($a, 15, $venta['totales']['11620307_galones'], $formato5);
				$worksheet1->write_string($a, 16, $venta['totales']['11620307_importe'], $formato5);
				$worksheet1->write_string($a, 17, $venta['totales']['lubricantes'], $formato5);
				$worksheet1->write_string($a, 18, $venta['totales']['accesorios'], $formato5);
				$worksheet1->write_string($a, 19, $venta['totales']['servicios'], $formato5);
				$worksheet1->write_string($a, 20, $venta['totales']['market'], $formato5);
				$worksheet1->write_string($a, 21, $venta['totales']['whiz'], $formato5);
				$worksheet1->write_string($a, 22, $venta['totales']['ob'], $formato5);
				$worksheet1->write_string($a, 23, $venta['totales']['otros'], $formato5);
				$worksheet1->write_string($a, 24, $tttotal, $formato5);
			}
		}

		$workbook->close();	

		$chrFileName = "Ventas Diarias";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}
}

