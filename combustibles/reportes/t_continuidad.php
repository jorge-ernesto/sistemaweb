<?php

class ContinuidadTemplate extends Template {
    
	function search_form($f_desde,$f_hasta) {

		if($f_desde == '' || $f_hasta == ''){
			$f_desde = date(d."/".m."/".Y); 
			$f_hasta = date(d."/".m."/".Y);
		}

		$estaciones=array('' => 'TODAS');

		$form = new form2('Reporte de Continuidad de Contometros', 'form_reporte', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.CONTINUIDAD"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('estacion', '', $almacen, $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $f_desde, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_reporte.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $f_hasta, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_reporte.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", espacios(5)));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center">'));
	
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));
		
		return $form->getForm();
    }
    
    	function reporte($reporte) {
		if (count($reporte)==0) {
			$result = '<p style="text-align:center;">Los contometros de las fecha indicadas son perfectamente continuos.</p>';
			return $result;
		}

		$result .= '<table border="1" style="border: 1; border-style: simple; border-color: #000000;" align="center">';
		$result .= '<tr>';
		$result .= '<th style="color:blue;">Fecha</th>';
		$result .= '<th style="color:blue;">Numero de Parte</th>';
		$result .= '<th style="color:blue;">Lado</th>';
		$result .= '<th style="color:blue;">Manguera</th>';		
		$result .= '<th style="color:blue;">Combustible</th>';
		$result .= '<th style="color:blue;">Inicial (Volumen)</th>';
		$result .= '<th style="color:blue;">Final (Volumen)</th>';
		$result .= '<th style="color:blue;">Inicial (Valor)</th>';
		$result .= '<th style="color:blue;">Final (Valor)</th>';
		$result .= '<th style="color:blue;">Origen</th>';
		$result .= '</tr>';

		foreach ($reporte as $d) {
			$a = $d['a'];
			$s = $d['s'];
			switch ($d['e']) {
				case 1:
					$m = "Contometro de volumen no coincide";
					$c1 = "color:red;";
					$c2 = "";
					break;
				case 2:
					$m = "Contometro de valor no coincide";
					$c1 = "";
					$c2 = "color:red;";
					break;
				default:
					$m = "Error desconocido";
					$c1 = "color:red;";
					$c2 = "color:red;";
					break;
			}
			$result .= "<tr>";
			$result .= "<td style=\"text-align:center;\" colspan=\"11\">".htmlentities($m)."</td>";
			$result .= "</tr>";
			$result .= "<tr>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($a['dt_fechaparte'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($a['ch_numeroparte'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($a['ch_numerolado'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($a['nu_manguera'])."</td>";			
			$result .= "<td style=\"text-align:center;\">".htmlentities($a['ch_nombrecombustible'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($a['nu_contometroinicialgalon'])."</td>";
			$result .= "<td style=\"text-align:center;{$c1}\">".htmlentities($a['nu_contometrofinalgalon'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($a['nu_contometroinicialvalor'])."</td>";
			$result .= "<td style=\"text-align:center;{$c2}\">".htmlentities($a['nu_contometrofinalvalor'])."</td>";
			$result .= "<td style=\"text-align:center;\">".(($a['ch_usuario']=="AUTO")?"Autom&aacute;tico":"Manual")."</td>";
			$result .= "</tr>";
			$result .= "<tr>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($s['dt_fechaparte'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($s['ch_numeroparte'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($s['ch_numerolado'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($s['nu_manguera'])."</td>";			
			$result .= "<td style=\"text-align:center;\">".htmlentities($a['ch_nombrecombustible'])."</td>";
			$result .= "<td style=\"text-align:center;{$c1}\">".htmlentities($s['nu_contometroinicialgalon'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($s['nu_contometrofinalgalon'])."</td>";
			$result .= "<td style=\"text-align:center;{$c2}\">".htmlentities($s['nu_contometroinicialvalor'])."</td>";
			$result .= "<td style=\"text-align:center;\">".htmlentities($s['nu_contometrofinalvalor'])."</td>";
			$result .= "<td style=\"text-align:center;\">".(($s['ch_usuario']=="AUTO")?"Autom&aacute;tico":"Manual")."</td>";
			$result .= "</tr>";		    	
		}		
		$result .= '</table>';

		return $result;
    	}
}
