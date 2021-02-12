<?php

class EstadisticaTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Informe de Estadistica de Ventas</b></h2></div>';
	}
    
	function search_form() {
		$almacenes = EstadisticaModel::obtenerAlmacenes("");
		
		$form = new form2('', 'form_search', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.ESTADISTICA"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="6" align="center">Almacen : '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $_SESSION['almacen'], $almacenes, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha Anterior</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde1", "", date("d/m/Y"), '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_search.desde1'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;a</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde2", "", date("d/m/Y"), '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_search.desde2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha Actual</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta1", "", date("d/m/Y"), '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_search.hasta1'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;a</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta2", "", date("d/m/Y"), '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_search.hasta2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="6" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", ""));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		return $form->getForm();
    }
    
    	function reporte($res) {
    	
    		$TP_L = '000002';
	     	$TP_A = '000003';
	     	$TP_S = '000006';
	     	$TP_M = '000005';
	     	$TP_O = '000010';
	     	$TP_W = '000009';

	     	$G84 = '11620301';
     		$G90 = '11620302';
     		$G97 = '11620303';
     		$GD2 = '11620304';
     		$G95 = '11620305';
     		$KD  = '11620306';
     		$GLP = '11620307';

		$result  = '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera" rowspan="2">&nbsp;&nbsp;ALMACEN&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera" colspan="5">&nbsp;&nbsp;GALONES&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;TOTAL&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;</th>';
		$result .= '<th class="grid_cabecera" colspan="5">&nbsp;&nbsp;SOLES&nbsp;&nbsp;</th>';		
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;TOTAL&nbsp;&nbsp;</th>';
		$result .= '<tr><th class="grid_cabecera">&nbsp;&nbsp;84&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;90&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;95&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;97&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;D2&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;GALONES&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;GLP&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;ACCESOR.&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;SERVIC.&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;LUBRI&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;MARKET&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;OTROS&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;SOLES&nbsp;&nbsp;</th>';
		$result .= '</tr>';
			
		for($i=0; $i<count($res); $i++) {
			$result .= '<tr>';
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");	
			$nomalma = EstadisticaModel::nomAlmacen($res[$i][0]);		
			$result .= '<td class="'.$color.'" >&nbsp;'.$res[$i][1]." - ".$nomalma.'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][2].'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][3].'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][4].'&nbsp;</td>';			
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][5].'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][6].'&nbsp;</td>';	
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i]['totcom'].'&nbsp;</td>';	
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][8].'&nbsp;</td>';	
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][9].'&nbsp;</td>';	
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][10].'&nbsp;</td>';	
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][11].'&nbsp;</td>';	
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][12].'&nbsp;</td>';	
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i][13].'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.$res[$i]['totmar'].'&nbsp;</td>';
			$result .= '</tr>';
			
			// Totales Porcentaje
			if($res[$i][1]=="ANTERIOR") {
				if(trim($res[$i][2])=="" and trim($res[$i-1][2])=="") $t2 = "";
				else $t2 = round(100-(($res[$i][2]*100)/$res[$i-1][2]),2)." %";
				if(trim($res[$i][3])=="" and trim($res[$i-1][3])=="") $t3 = "";
				else $t3 = round(100-(($res[$i][3]*100)/$res[$i-1][3]),2)." %";
				if(trim($res[$i][4])=="" and trim($res[$i-1][4])=="") $t4 = "";
				else $t4 = round(100-(($res[$i][4]*100)/$res[$i-1][4]),2)." %";
				if(trim($res[$i][5])=="" and trim($res[$i-1][5])=="") $t5 = "";
				else $t5 = round(100-(($res[$i][5]*100)/$res[$i-1][5]),2)." %";
				if(trim($res[$i][6])=="" and trim($res[$i-1][6])=="") $t6 = "";
				else $t6 = round(100-(($res[$i][6]*100)/$res[$i-1][6]),2)." %";
				
				if(trim($res[$i]['totcom'])=="" and trim($res[$i-1]['totcom'])=="") $totcom = "";
				else $totcom = round(100-(($res[$i]['totcom']*100)/$res[$i-1]['totcom']),2)." %";
				
				if(trim($res[$i][8])=="" and trim($res[$i-1][8])=="") $t8 = "";
				else $t8 = round(100-(($res[$i][8]*100)/$res[$i-1][8]),2)." %";
				if(trim($res[$i][9])=="" and trim($res[$i-1][9])=="") $t9 = "";
				else $t9 = round(100-(($res[$i][9]*100)/$res[$i-1][9]),2)." %";
				if(trim($res[$i][10])=="" and trim($res[$i-1][10])=="") $t10 = "";
				else $t10 = round(100-(($res[$i][10]*100)/$res[$i-1][10]),2)." %";
				if(trim($res[$i][11])=="" and trim($res[$i-1][11])=="") $t11 = "";
				else $t11 = round(100-(($res[$i][11]*100)/$res[$i-1][11]),2)." %";
				if(trim($res[$i][12])=="" and trim($res[$i-1][12])=="") $t12 = "";
				else $t12 = round(100-(($res[$i][12]*100)/$res[$i-1][12]),2)." %";
				if(trim($res[$i][13])=="" and trim($res[$i-1][13])=="") $t13 = "";
				else $t13 = round(100-(($res[$i][13]*100)/$res[$i-1][13]),2)." %";	
				
				if(trim($res[$i]['totmar'])=="" and trim($res[$i-1]['totmar'])=="") $totmar = "";
				else $totmar = round(100-(($res[$i]['totmar']*100)/$res[$i-1]['totmar']),2)." %";		
			
				$result .= '<tr>';	
				$result .= '<td class="'.$color.'" >&nbsp;DIFERENCIA PORCENTUAL&nbsp;</td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t2.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t3.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t4.' </td>';			
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t5.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t6.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$totcom.'</td>';	
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t8.' </td>';	
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t9.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t10.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t11.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t12.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$t13.' </td>';
				$result .= '<td align="center" class="grid_detalle_total" >&nbsp;'.$totmar.'</td>';	
				$result .= '</tr>';
			}				    	
		}		
		$result .= '</table>';

		return $result;
    	}
}
