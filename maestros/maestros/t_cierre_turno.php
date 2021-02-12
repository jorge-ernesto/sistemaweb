<?php
class CierreTurnoTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Mantenimiento Cierre de Dia y Turno</b></h2>';
	}

	function Formulario($fecha,$fecha2,$campo){

		$hoy_timestamp  = time();
		$ayer_timestamp = $hoy_timestamp - (24*60*60);
		$ayer = date("d/m/Y", $ayer_timestamp);

		$mes  = date("m");
		$dia  = "01";
		$year = date("Y");
	
		$inicio_mes = mktime(0, 0, 0, $mes, $dia, $year);
		$fin_mes    = mktime(0, 0, 0, $mes+1, 0, $year);
		
		if ($fecha == "" or $fecha2 == "") {
			$fecha      = date("d/m/Y", $inicio_mes);
			$fecha2     = date("d/m/Y", $fin_mes);
		}

		//$campote = Array("0"=>"turno", "1"=>"Dia");

		$ver = CierreTurnoModel::buscar($fecha,$fecha2,"");
		return $ver;

	}

	function formSearch($fecha,$fecha2,$campo){

		$hoy_timestamp  = time();
		$ayer_timestamp = $hoy_timestamp - (24*60*60);
		$ayer = date("d/m/Y", $ayer_timestamp);

		$mes  = date("m");
		$dia  = "01";
		$year = date("Y");
	
		$inicio_mes = mktime(0, 0, 0, $mes, $dia, $year);
		$fin_mes    = mktime(0, 0, 0, $mes+1, 0, $year);
		
		if ($fecha == "" or $fecha2 == "") {
			$fecha      = date("d/m/Y", $inicio_mes);
			$fecha2     = date("d/m/Y", $fin_mes);
		} 

		$campo = Array("2" => "Todos", "0"=>"Turno", "1"=>"Dia");
		
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.DIACIERRE"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td style="text-align:right;">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Fecha Inicio:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", $fecha, '<a href="javascript:show_calendar(\'Buscar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Fecha Final:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", $fecha2, '<a href="javascript:show_calendar(\'Buscar.fecha2\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:center;">', '', 10, 10, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('</tr></td><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Buscar por:'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("campo", "", "", $campo, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Buscar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('</td><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Agregar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));
		return $form->getForm();
	}

	function formAgregar($fecha,$fecha2,$campo) {

		$campito = Array("0"=>"Turno", "1"=>"Dia");

		$form = new Form('','Editar', FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.DIACIERRE"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td style="text-align:center;">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Ingresar Dia/Turno:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("campo", "", "", $campito, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></td><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'fecha', date('d/m/Y'), '<a href="javascript:show_calendar(\'Editar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:center;">', '', 10, 10,true));			
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Nueva Hora Inicio:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','hora_inicial','',$hora_inicial,'',8,8,false,'onkeypress="return validar(event,2)"'));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tca_moneda', "" , '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 4, 4,false,'onkeypress="return validar(event,2)"'));
		//$form->addElement('doc_detalle', new f2element_text ('monto','Monto a Cancelar </td><td>:', $_REQUEST['saldo'], espacios(2), 15, 15,array('onkeypress'=>'return validar(event,1);')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></td><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Nueva Hora Cierre:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text ('','hora_final','', $hora_final,'',8,8,false,'onkeypress="return validar(event,2)"'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></td><tr><td>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Fecha: </td><td>'));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'fecha_actualizacion', date("d/m/Y"), '', '', 10, 10, ($_SESSION['usuario'] == "OCS" || $_SESSION['usuario'] == "SISTEMAS") ? false : true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</tr></td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align='right'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Guardar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='right'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '<br>', '', 1, 'onclick="CierreDiaRegresar(\'' . $fecha . '\', \'' . $fecha2 . '\')"'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

		return $form->getForm();
	}

	function resultadosBusqueda($resultados) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">TIPO</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">HORA INICIO</th>';
		$result .= '<th class="grid_cabecera">HORA FIN</th>';
		$result .= '<th class="grid_cabecera">FECHA CREADA</th>';
		$result .= '<th class="grid_cabecera">USUARIO</th>';
		$result .= '</tr>';
		for ($i = 0; $i < count($resultados); $i++) {
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];
			
			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'">' . htmlentities($a['stype']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['systemdate']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['begintime']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['endtime']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['created']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['createdby']) . '</td>';
			$result .= '</tr>';
		}
		$result .= '</table>';
		return $result;
	}
}
