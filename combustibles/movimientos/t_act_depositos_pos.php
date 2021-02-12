<?php
class ActDepositosPosTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Actualizar Depositos POS</b></h2></div>';
	}

	function formSearch($fecha) {
		$almacenes = ActDepositosPosModel::obtenerAlmacenes();

		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ACTDEPOSITOSPOS"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<table border="0" cellpadding="2px"><tr><td align="right">Almac&eacute;n:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "ch_almacen", '', '<br>', '', '', $almacenes, false, ''));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td align="right">Fecha: </td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
			<td>
				<input type="text" name="ch_fecha" id="ch_fecha" maxlength="10" size="12" class="fecha_formato" value="'.(empty($_REQUEST['ch_fecha']) ? $fecha : $_REQUEST['ch_fecha']).'" />	
			</td>
		</tr>
		<tr>
			<td align="right">Seleccionar Turno: </td>
			<td id="turno_final">
				<select id="opt_final" name="ch_turno"></select>
				<div id="tab_turnos" style="font-size:1.2em; color:red;"></div>
			</td>
		</tr>'
		));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr></table>'));	
				
		return $form->getForm();
    	}

	function formEdit($fila,$trabajadores,$usuario,$ip) {
		$form = new Form('','editar', FORM_METHOD_POST, "control.php", '', '');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ACTDEPOSITOSPOS"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "update"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_almacen", $fila['almacen']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("dt_dia", $fila['dia']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_posturno", $fila['turno']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_codigo_trabajador", $fila['codtrab']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_numero_documento", $fila['num']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("ch_numero_correl", $fila['seq']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("tabla", substr($fila['dia'],0,4) . substr($fila['dia'],5,2)));

		$form->addGroup("GRUPO_DETALLES","Detalles");
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Tipo: {$fila['tipo']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Fecha Creacion: {$fila['fecha']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Fecha Actualizacion: {$fila['fechaact']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Seq: {$fila['seq']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Num: {$fila['num']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Moneda: {$fila['moneda']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Cambio: {$fila['cambio']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Importe S/.: {$fila['importesoles']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Importe US$: {$fila['importedolares']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"USUARIO: {$fila['usuario']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"IP: {$fila['ip']}",'<br/>'));

		$form->addGroup("GRUPO_EDITABLES","Editables");

		$trabajadores[''] = '';

		foreach ($trabajadores as $t_k => $t_v)
			$trabajadores[$t_k] = $t_k . ' - ' . $t_v;

		if (trim($fila['valida']!='S') && trim($fila['valida']!='N'))
			$fila['valida'] = 'N';

		$form->addElement("GRUPO_EDITABLES", new form_element_combo('Valida','nvalida',trim($fila['valida']),'<br/>','', '', Array('S'=>'Si','N'=>'No'), false, ''));
		$form->addElement("GRUPO_EDITABLES", new form_element_text('Dia', 'ndia', $fila['dia'], '<a href="javascript:show_calendar(\'editar.ndia\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div><br/>', '', 10, 10, true));
		$form->addElement("GRUPO_EDITABLES", new form_element_text('Turno','nturno',$fila['turno'],'<br/>', '', 5, 1, false));
		$form->addElement("GRUPO_EDITABLES", new form_element_combo('Trabajador','ncodtrab',trim($fila['codtrab']),'<br/>','', '', $trabajadores, false, ''));

		$form->addGroup("GRUPO_BOTONES", "");
		$form->addElement("GRUPO_BOTONES", new form_element_submit("action2", "Actualizar", '', '', 20));
		return $form->getForm();
	}

    	function listado($resultados)  {
    	
		$result  = '';
		$result .= '<center><table>';
		$result .= '<tr bgcolor="#D9F9B2">';
		$result .= '<th class="grid_cabecera">&nbsp;</th>';
		$result .= '<th class="grid_cabecera">TIPO</th>';
		$result .= '<th class="grid_cabecera">VALIDA</th>';
		$result .= '<th class="grid_cabecera">DIA</th>';
		$result .= '<th class="grid_cabecera">TURNO</th>';
		$result .= '<th class="grid_cabecera">TRABAJADOR</th>';
		$result .= '<th class="grid_cabecera">FECHA CREACION</th>';
		$result .= '<th class="grid_cabecera">FECHA ACTUALIZACION</th>';
		$result .= '<th class="grid_cabecera">SEQ.</th>';
		$result .= '<th class="grid_cabecera">NUM.</th>';
		$result .= '<th class="grid_cabecera">MONEDA</th>';
		$result .= '<th class="grid_cabecera">CAMBIO</th>';
		$result .= '<th class="grid_cabecera">IMPORTE S/.</th>';
		$result .= '<th class="grid_cabecera">IMPORTE US$</th>';
		$result .= '<th class="grid_cabecera">USUARIO</th>';
		$result .= '<th class="grid_cabecera">IP</th>';

		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
		    	$a = $resultados[$i];
		    	list($ano,$mes,$dia) = explode("-", $a['dia']);
		    	$url = "/sistemaweb/combustibles/act_depositos_pos_popup.php?ch_almacen={$a['almacen']}&dt_dia=" . urlencode($dia."/".$mes."/".$ano) . "&ch_posturno={$a['turno']}&ch_codigo_trabajador={$a['codtrab']}&ch_numero_documento={$a['num']}&ch_numero_correl={$a['seq']}&tabla=" . substr($a['dia'],0,4) . substr($a['dia'],5,2);
		    	$result .= '<tr bgcolor="" onMouseOver=this.style.backgroundColor="#FFFFCC"; this.style.cursor="hand"; onMouseOut=this.style.backgroundColor=""; onClick="window.open(\''.$url.'\',\'reimpresion\',\'width=600,height=600,scrollbars=yes,menubar=no,left=60,top=20\')";>';

		    	$result .= '<td class="'.$color.'"><input type="radio" name="xxx" onClick="window.open(\''.$url.'\',\'reimpresion\',\'width=600,height=600,scrollbars=yes,menubar=no,left=60,top=20\')"></td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['tipo']) . '</td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['valida']) . '</td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['dia']) . '</td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['turno']) . '</td>';
		    	$result .= '<td class="'.$color.'">&nbsp;' . htmlentities($a['trabajador']) . '</td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['fecha']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['fechaact']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['seq']) . '</td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['num']) . '</td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['moneda']) . '</td>';
		    	$result .= '<td class="'.$color.'" align="right">&nbsp;' . htmlentities(number_format($a['cambio'], 4, '.', '')) . '</td>';
		    	$result .= '<td class="'.$color.'" align="right">&nbsp;' . htmlentities(number_format($a['importesoles'], 4, '.', '')) . '</td>';
		    	$result .= '<td class="'.$color.'" align="right">&nbsp;' . htmlentities(number_format($a['importedolares'], 4, '.', '')) . '</td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['usuario']) . '</td>';
		    	$result .= '<td class="'.$color.'" align="center">&nbsp;' . htmlentities($a['ip']) . '</td>';
		    	$result .= '</tr>';
		}
		$result .= '</table></center>';
		
		return $result;
    	}
}
