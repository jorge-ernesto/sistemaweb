<?php
function showNumber($n) {
	return number_format($n,3,".","");
}

function showInteger($n) {
	return number_format($n,0,".","");
}

class SYFTurnoTemplate extends Template {
	function titulo() {
		return '<h2><b>Sobrantes y Faltantes por Turno</b></h2>';
	}

	function formReporte($tanques) {
		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.SYFTURNO"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='0'>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>Fecha inicio: </td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dia1", @date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.dia1\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dia1", @date("d/m/Y"), '', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>Fecha Final: </td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dia2", @date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.dia2\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div id="overDivx" style="position:absolute; visibility:hidden; z-index:1000;"></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dia2", @date("d/m/Y"), '', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Tanque:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "tanque", "", '</td></tr><tr><td style="text-align:center;" colspan="2">', "", "", $tanques, false, ""));
//		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "tanque", '', '</td></tr><tr><td style="text-align:center;" colspan="2">', '', 10, 10, false));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan='5' align='center'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Procesar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));

		return $form->getForm();
	}

	function presentarReporte($reporte) {

		if (!is_array($reporte)) {
			$result = "<script language=\"javascript\">alert(\"No hay datos\");</script><!--MSG: $reporte -->\n";
			return $result;
		}

		$result  = "<table align='center'>";
		$result .= "<tr>";
		/*$result .= "<td style=\"text-align: center; width:  6%;\" rowspan=\"2\">D&iacute;a</td>";
		$result .= "<td style=\"text-align: center; width:  4%;\" rowspan=\"2\">Turno</td>";
		$result .= "<td style=\"text-align: center; width:  5%;\" rowspan=\"2\">Saldo</td>";
		$result .= "<td style=\"text-align: center; width: 22%;\" colspan=\"3\">Compra</td>";
		$result .= "<td style=\"text-align: center; width:  5%;\" rowspan=\"2\">Medici&oacute;n</td>";
		$result .= "<td style=\"text-align: center; width:  5%;\" rowspan=\"2\">Venta</td>";
		$result .= "<td style=\"text-align: center; width:  5%;\" rowspan=\"2\">Parte</td>";
		$result .= "<td style=\"text-align: center; width: 16%;\" colspan=\"2\">Stock F&iacute;sico</td>";
		$result .= "<td style=\"text-align: center; width: 16%;\" colspan=\"2\">Diferencia</td>";
		$result .= "<td style=\"text-align: center; width: 16%;\" colspan=\"2\">&#37;</td>";
		$result .= "</tr><tr>";
		$result .= "<td style=\"text-align: center; width:  8%;\">Factura</td>";
		$result .= "<td style=\"text-align: center; width:  8%;\">SCOP</td>";
		$result .= "<td style=\"text-align: center; width:  6%;\">Galones</td>";
		$result .= "<td style=\"text-align: center; width:  8%;\">&#37;</td>";
		$result .= "<td style=\"text-align: center; width:  8%;\">Varilla</td>";
		$result .= "<td style=\"text-align: center; width:  8%;\">Diaria</td>";
		$result .= "<td style=\"text-align: center; width:  8%;\">Acumulada</td>";
		$result .= "<td style=\"text-align: center; width:  8%;\">Acumulado</td>";
		$result .= "<td style=\"text-align: center; width:  8%;\">Diario</td>";*/
		$result .= "<tr>";
		$result .= "<td class='grid_cabecera' rowspan='2' style='text-align: center;'>DIA</td>";
		$result .= "<td class='grid_cabecera' rowspan='2' style='text-align: center;'>TURNO</td>";
		$result .= "<td class='grid_cabecera' rowspan='2' style='text-align: center;'>SALDO <br>(+)</td>";
		$result .= "<td class='grid_cabecera' rowspan='2' style='text-align: center;'>COMPRA <br>(+)</td>";
		$result .= "<td class='grid_cabecera' rowspan='2' style='text-align: center;'>AFERICION <br>(+)</td>";
		$result .= "<td class='grid_cabecera' rowspan='2' style='text-align: center;'>VENTA <br>(-)</td>";
		$result .= "<td class='grid_cabecera' colspan='2' style='text-align: center;'>TRANSFERENCIAS</td>";		
		$result .= "<td class='grid_cabecera' rowspan='2' style='text-align: center;'>PARTE</td>";
		$result .= "<td class='grid_cabecera' rowspan='2' style='text-align: center;'>VARILLA</td>";		
		$result .= "<td class='grid_cabecera' colspan='2' style='text-align: center;'>DIFERENCIA</td>";		
		$result .= "</tr>";

		$result .= "<tr>";			
		$result .= "<td class='grid_cabecera' style='text-align: center;'>INGRESO <br>(+)</td>";
		$result .= "<td class='grid_cabecera' style='text-align: center;'>SALIDA <br>(-)</td>";
		$result .= "<td class='grid_cabecera'>DIARIA</td>";
		$result .= "<td class='grid_cabecera'>ACUMULADA</td>";		
		$result .= "</tr><tr>";

		for ($i = 0;$i < (count($reporte)-1);$i++) {
			$color 	= ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$r 		= $reporte[$i];

			$result .= '<tr>';
			$result .= '<td class="'.$color.'" align ="center">'. $r['dia'] .'</td>';                            
			$result .= '<td class="'.$color.'" align ="center">'. $r['turno'] .'</td>';                     
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['saldo_anterior']) . '</td>'; 
			$result .= '<td class="'.$color.'" align ="center">'. showNumber($r['compra']) .'</td>';               
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['afericion']) . '</td>';    
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['venta']) . '</td>';        
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['ingreso']) . '</td>';            
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['salida']) . '</td>';             
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['parte']) . '</td>';            
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['varilla']) . '</td>';            
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['diferencia_diaria']) . '</td>';             
			$result .= '<td class="'.$color.'" align ="right">' . showNumber($r['diferencia_acumulada']) . '</td>';             
			$result .= "</tr>";
		}

		$r = $reporte[$i];

		$result .= "<tr>";
		$result .= '<td class="grid_detalle_total" colspan="3" align ="right">Total: </td>';
		$result .= '<td class="grid_detalle_total" align ="center">' . showNumber($r['compra']) . '</td>';
		$result .= '<td class="grid_detalle_total" align ="center">' . showNumber($r['afericion']) . '</td>';
		$result .= '<td class="grid_detalle_total" align ="center">' . showNumber($r['venta']) . '</td>';
		$result .= '<td class="grid_detalle_total" align ="center">' . showNumber($r['ingreso']) . '</td>';
		$result .= '<td class="grid_detalle_total" align ="center">' . showNumber($r['salida']) . '</td>';
		$result .= '<td class="grid_detalle_total" align ="center"></td>';
		$result .= '<td class="grid_detalle_total" align ="center"></td>';
		$result .= '<td class="grid_detalle_total" align ="center"></td>';
		$result .= '<td class="grid_detalle_total" align ="center"></td>';
		$result .= "</tr>";

		$result .= "</table>";
		return $result;
	}

}

