<?php
class ActFormaPagoTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Actualizar Formas de Pago</b></h2></div>';
	}

	function formSearch($fecha) {
		$almacenes = ActFormaPagoModel::obtenerAlmacenes();
		
		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ACTFORMAPAGO"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<table border="0" cellpadding="2px"><tr><td align="right">Seleccionar Almac&eacute;n: </td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "ch_almacen", '', '<br>', '', '', $almacenes, false, ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td align="right">Fecha: </td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('
			<td>
				<input type="text" name="ch_fecha" id="ch_fecha" maxlength="10" size="10" class="fecha_formato" value="'.(empty($_REQUEST['ch_fecha']) ? $fecha : $_REQUEST['ch_fecha']).'" />	
			</td>
		</tr>
		<tr>
			<td align="right">Seleccionar Turno: </td>
			<td id="turno_final">
				<select id="opt_final" name="ch_turno"></select>
				<div id="tab_turnos" style="font-size:1.2em; color:red;"></div>
			</td>
		</tr>
		<tr>
			<td align="right">Seleccionar Caja: </td>
			<td id="cajas">
				<select id="id_cajas" name="ch_caja">
					<option value="TODAS">Todas las Cajas</option>
				</select>
				<div id="tab_cajas" style="font-size:1.2em; color:red;"></div>
			</td>'
		));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr></table>'));		

		return $form->getForm();
	}

	function formEdit($fila) {
		$formas = ActFormaPagoModel::obtenerFormasDePago();
		$tarjetas = ActFormaPagoModel::obtenerTarjetas();

		$form = new Form('','editar', FORM_METHOD_POST, "control.php", '', '');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ACTFORMAPAGO"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "update"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("oid", $fila['oid']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("caja", $fila['caja']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("tabla", $fila['tabla']));$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("td", $fila['td']));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("turno", $fila['turno']));

		$form->addGroup("GRUPO_DETALLES","Detalles");
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Tipo Movimiento: {$fila['tm']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Tipo Documento: {$fila['td']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Trans: {$fila['trans']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Fecha: {$fila['fecha']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Descripci&oacute;n: {$fila['art_descripcion']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Cantidad: {$fila['cantidad']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Precio: {$fila['precio']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Importe: {$fila['importe']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Turno: {$fila['turno']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Caja: {$fila['caja']}",'<br/>'));
		$form->addElement("GRUPO_DETALLES", new form_element_onlytext('',"Lado: {$fila['pump']}",'<br/>'));

		while (strlen($fila['fpago']) < 6)
			$fila['fpago'] = '0' . $fila['fpago'];

		while (strlen($fila['at'])<6)
			$fila['at'] = '0' . $fila['at'];

		$formas[''] = '';
		$tarjetas[''] = '';

		if (!isset($formas[$fila['fpago']]))
			$fila['fpago'] = '';

		if (!isset($tarjetas[$fila['at']]))
			$fila['at'] = '';

		$form->addGroup("GRUPO_EDITABLES","Editables");
		$form->addElement("GRUPO_EDITABLES", new form_element_combo('Forma de Pago','fpago',$fila['fpago'],'<br/>','', '', $formas, false, ''));
		$form->addElement("GRUPO_EDITABLES", new form_element_combo('Tipo de Tarjeta','tarjeta',$fila['at'],'<br/>','', '', $tarjetas, false, ''));
		$form->addElement("GRUPO_EDITABLES", new form_element_text('Tarjeta','ntarjeta',$fila['text1'],'<br/>'));
		//$form->addElement("GRUPO_EDITABLES", new form_element_text('Trabajador','ntrabajador',$fila['cajero'],'<br/>'));

		$form->addGroup("GRUPO_BOTONES", "");
		$form->addElement("GRUPO_BOTONES", new form_element_submit("action2", "Actualizar", '', '', 20));

		return $form->getForm();
	}

	function listado($resultados) {
		$totales['cantidad'] = 0;
		$totales['importe'] = 0;
		$fpago = "";

		$formas = ActFormaPagoModel::obtenerFormasDePago();
		$tarjetas = ActFormaPagoModel::obtenerTarjetas();

		$result = '';
		$result .= '<center><table>';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">&nbsp;</th>';
		$result .= '<th class="grid_cabecera">TM</th>';
		$result .= '<th class="grid_cabecera">TD</th>';
		$result .= '<th class="grid_cabecera">TRAN</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION</th>';
		$result .= '<th class="grid_cabecera">CANTIDAD</th>';
		$result .= '<th class="grid_cabecera">PRECIO</th>';
		$result .= '<th class="grid_cabecera">IMPORTE</th>';
		$result .= '<th class="grid_cabecera">TURNO</th>';
		$result .= '<th class="grid_cabecera">CAJA</th>';
		$result .= '<th class="grid_cabecera">LADO</th>';
		$result .= '<th class="grid_cabecera">FORMA DE PAGO</th>';
		$result .= '<th class="grid_cabecera">TIPO TARJETA</th>';
		$result .= '<th class="grid_cabecera">TARJETA</th>';
		$result .= '<th class="grid_cabecera">USUARIO</th>';
		$result .= '<th class="grid_cabecera">TRABAJADOR</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];

			$url = "/sistemaweb/combustibles/control.php?rqst=MOVIMIENTOS.ACTFORMAPAGO&action=edit&oid={$a['oid']}&caja={$a['caja']}&dia={$a['dia']}&td={$a['td']}&turno={$a['turno']}";
			$result .= '<tr bgcolor="" onMouseOver=this.style.backgroundColor="#FFFFCC"; this.style.cursor="hand"; onMouseOut=this.style.backgroundColor=""; onClick="window.open(\''.$url.'\',\'reimpresion\',\'width=600,height=600,scrollbars=yes,menubar=no,left=60,top=20\')";>';

			while (strlen($a['fpago']) < 6)
				$a['fpago'] = '0' . $a['fpago'];

			$fpago = $formas[$a['fpago']];

			if($a['at']!="") {
				while (strlen($a['at'])<6)
					$a['at'] = '0' . $a['at'];
				$ttarj = $tarjetas[$a['at']];
			} else
			$ttarj = '-';

			$result .= '<td class="'.$color.'"><input type="radio" name="xxx" onClick="window.open(\''.$url.'\',\'reimpresion\',\'width=600,height=600,scrollbars=yes,menubar=no,left=60,top=20\')"></td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['tm']) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['td']) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['trans']) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['fecha']) . '</td>';
			$result .= '<td class="'.$color.'">&nbsp;' . htmlentities($a['art_descripcion']) . '</td>';
			$result .= '<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($a['cantidad'], 4, '.', '')) . '</td>';
			$result .= '<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($a['precio'], 4, '.', '')) . '</td>';
			$result .= '<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($a['importe'], 4, '.', '')) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['turno']) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['caja']) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['pump']) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($fpago) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($ttarj) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['text1']) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['usuario']) . '</td>';
			$result .= '<td align="center" class="'.$color.'">&nbsp;' . htmlentities($a['documento']) . '</td>';
			$result .= '</tr>';
		}

		$result .= '</table></center>';

		return $result;
	}
}
