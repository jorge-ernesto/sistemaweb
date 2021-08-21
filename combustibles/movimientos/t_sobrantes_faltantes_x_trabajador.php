<?php

class SobrantesFaltantesTrabajadorTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Sobrantes y Faltantes por Trabajador</b></h2>';
	}

	function formSearch($almacenes) {
		$ordenpor = Array (1 => "Fecha", 2 => "Trabajador");
		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.SOBRANTESFALTANTESTRABAJADOR"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Almacen:</td><td style='width:50%;text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "almacen", $_SESSION['almacen'], '</td></tr><tr><td style="text-align:right;">', '', '', $almacenes, false, ''));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Del:</td><td style='text-align:left;'>"));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.fecha\');"></td></tr><tr><td style="text-align:right;">', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Al:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", date("d/m/Y"), '</td></tr>', '', 10, 10, false));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.fecha2\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr>', '', 10, 10, false));
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='width:50%;text-align:right;'>Trabajador:</td><td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "codtrabajador", '', '</td></tr>', '', 10, 10, false));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='width:50%;text-align:right;'>Ordenar por:</td><td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "ordenpor", $ordenpor[0], '</td></tr>', '', '', $ordenpor, false, ''));
	
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan='2' style='text-align:center;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Buscar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Agregar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Importar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

		return $form->getForm();
	}

	function formImportar() {
		$fecha_actual = date("Y-m-d");

		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.SOBRANTESFALTANTESTRABAJADOR"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "doImportar"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
		// $form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, true));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="date" name="fecha" id="fecha" value="'.$fecha_actual.'"></td></tr><tr><td style="text-align:right;">'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submitbutton", "Importar", '', '', 20));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));

		return $form->getForm();
	}

	function formAgregar($almacenes, $trabajadores,$fila=Array()) {
		$fecha_actual = date("Y-m-d");

		//Cambiamos formato de fecha
		if( isset($fila['dia']) ){
			$fecha = explode("/", $fila['dia']);
			$fila['dia'] = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
		}
		//Cerramos Cambiamos formato de fecha

		$form = new Form('','editar', FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.SOBRANTESFALTANTESTRABAJADOR"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_submit("submitButton\" style=\"visibility:hidden;\" id=\"submitButton", "submitButton", '', '', 20));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action\" id=\"action", "actualizaAgregar"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Almacen:</td><td style='width:50%;text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo('','almacen',$fila['almacen'],'</td></tr><tr><td style="text-align:right;">','', '', $almacenes, false, 'onBlur=""'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Dia:</td><td style='text-align:left;'>"));
		// $form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'dia" id="dia', (isset($fila['dia'])?$fila['dia']:date("d/m/Y")), '<a href="javascript:show_calendar(\'editar.dia\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, true,'onChange="actualizaTrabajador();"'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="date" name="dia" id="dia" value="'.(isset($fila['dia'])?$fila['dia']:$fecha_actual).'" onchange="actualizaTrabajador();"></td></tr><tr><td style="text-align:right;">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Turno:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'turno" id="turno', $fila['turno'], '</td></tr><tr><td style="text-align:right;">', '', 10, 15, false,'onChange="actualizaTrabajador();"'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Trabajador:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo('','trabajador',$fila['trabajador'],'</td></tr><tr><td style="text-align:right;">','', '', $trabajadores, false, ''));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Importe:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'importe', $fila['importe'], '</td></tr><tr><td style="text-align:right;;">', '', 10, 15, false));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Observacion:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'observacion', $fila['observacion'], '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 30, 255, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_button("saveButton\" id=\"saveButton\"", "Guardar", "", "", 20, "onClick='preValidateSubmit()'", (count($trabajadores)==0)));
//		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Guardar", '', '', 20,(count($trabajadores)>0)));
		$form->addElement(FORM_GROUP_MAIN, new form_element_button("goBackButton\" id=\"goBackButton\"", "Regresar", "", "", 20, "onClick='goBack()'", false));
//		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Regresar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));

		return $form->getForm();
	}

	function resultadosBusqueda($resultados,$almacenes,$tipos,$ordenpor) {
		$result = '';
		$result .= '<table align="center">';
		$result .= '<tr>';
		//$result .= '<th>&nbsp;</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">TURNO</th>';
		$result .= '<th class="grid_cabecera">TRABAJADOR</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;&nbsp;IMPORTE&nbsp;&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">OBSERVACION</th>';
		$result .= '<th class="grid_cabecera">PLANILLA</th>';
		$result .= '<th class="grid_cabecera">FLAG</th>';
		$result .= '</tr>';

		$amount = 0;
		$subtotal = 0;
		$vec = array();
		//$xtra = $resultados[0]['trabajador'];

		// vector de flags para diferenciar grupo de trabajadores
		for ($i = 0; $i < count($resultados); $i++) {
			if(trim($resultados[$i]['trabajador']) == trim($resultados[$i+1]['trabajador'])){
				$vec[$i] = 0;
			}else{
				$vec[$i] = 1;
			}
		}
		$vec[count($resultados)-1] = 1;


		for ($i = 0; $i < count($resultados); $i++) {			

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

			$a = $resultados[$i];

			$amount 	= $amount + $a['importe'];
			$subtotal 	= $subtotal + $a['importe'];

			$result .= '<tr>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['dia']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['turno']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['trabajador']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities($a['importe']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['observacion']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['planilla']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['flagescrito']) . '</td>';
			$result .= '</tr>';

			if($ordenpor == 2 and $vec[$i] == 1){
				$result .= '<tr><td colspan="3" style="text-align:right" bgcolor="#CEF6F5"> Sub-Total por Trabajador</td><td bgcolor="#CEF6F5" colspan="4" style="text-align:left">'.(number_format($subtotal, 2, '.', ',')).'</td></tr>';
				$subtotal = 0;
			}

		}

		$result .= '<tr><td colspan="3" style="text-align:right;font-size:11px; font-weight:bold"  class="grid_detalle_total"> TOTAL&nbsp&nbsp</td><td class="grid_detalle_total" colspan="4">'.(number_format($amount, 2, '.', ',')).'</td></tr>';

		return $result;
	}
}
