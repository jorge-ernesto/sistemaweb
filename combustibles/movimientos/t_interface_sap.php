<?php

class InterfaceSAPTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>SAP Business One</b></h2>';
    }

	function formPrincipal($arrAlmacenes, $iAlmacen, $dInicial, $dCierre, $sBoletaDetallada, $sBoletaAgrupada, $iGenerate, $arrTableConfiguration, $iTableConfiguration) {
		if ($arrTableConfiguration['bStatus']) {
			$html_option_table_configuration = '';
			$html_option_table_configuration = '<option value="0">- Seleccionar -</option>';
			foreach ($arrTableConfiguration['arrData'] as $row) {
				$selected = '';
				if($row['id_tipo_tabla'] == $iTableConfiguration)
					$selected = "selected";
				$html_option_table_configuration .= '<option value="'.$row['id_tipo_tabla'].'-'.$row['no_tabla'].'" ' . $selected . '>' . $row['no_tabla'] . '</option>';
			}
		} else {
			$html_option_table_configuration = $arrTableConfiguration['sMessage'];
		}

		$arrGenerate = array(
			'arrData' => array(
				0 => Array(
					'value' => 'C-Cabecera',
					'name' => 'Cabecera',
				),
				1 => Array(
					'value' => 'D-Detalle',
					'name' => 'Detalle',
				),
				2 => Array(
					'value' => 'R-Resumen',
					'name' => 'Resumen',
				),
			),
		);
		$html_option_generate = '';
		foreach ($arrGenerate['arrData'] as $row) {
			$selected = '';
			if($row['value'] == $iGenerate)
				$selected = "selected";
			$html_option_generate .= '<option value="'.$row['value'].'" ' . $selected . '>' . $row['name'] . '</option>';
		}

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZSAP'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dCierre . '">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<div style="margin: auto; width: 100%;padding: 10px;">
			<div class="tab" style="background-color:transparent;">
				<button type="button" value="0" class="tablinks active" data-id="export" title="Previualización y Exportación">Exportar </button>
				<button type="button" value="1" class="tablinks" data-id="configuration" title="Configuración de tablas SAP">Configuración </button>
			</div>
			<div id="export" class="tabcontent">
				<table style="text-align: left; width: 100%;" border="0">
					<tr>
						<th align="right">Almacen:</th>
						<th>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iAlmacen', '', $iAlmacen, $arrAlmacenes, espacios(3), array("onfocus" => "getFechasIF();getDatos();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
						</th>
					</tr>
					<tr>
						<th align="right">Fecha:</th>
						<th>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dInicial', '', $dInicial, '', 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
						</th>
					</tr>
					<tr>
						<th align="right">Generar:</th>
						<th>
							<select id="cbo-iGenerate" name="cbo-iGenerate">
							    ' . $html_option_generate . '
							</select>
						</th>
					</tr>
					<tr>
						<th align="right">Generar boletas:</th>
						<th>
							<input type="radio" name="radio-sGenerarBoleta" value="D" ' . $sBoletaDetallada . '>Detallada
							<input type="radio" name="radio-sGenerarBoleta" value="R" ' . $sBoletaAgrupada . '>Agrupada
						</th>
					</tr>
					<tr>
						<th align="center" colspan="2" class="container-btn-export">
							<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>
							<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel </button>
						</th>
					</tr>
				</table>
				<div class="container-preview"></div>
			</div>
			<div id="configuration" class="tabcontent none">
				<table style="text-align: left;">
					<tr>
						<th>Tabla:</th>
						<th>
							<select id="cbo-iTableConfiguration" name="cbo-iTableConfiguration">
							    ' . $html_option_table_configuration . '
						    </select>
						</th>
					</tr>
					<tr>
						<th align="center" colspan="2" class="container-btn-export">
							<button id="btn-html" name="action" type="submit" value="Tablas"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>
						</th>
					</tr>
				</table>
				<div class="container-table-configuration"></div>
			</div>
		</div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("cbo-iAlmacen").focus();}</script>'));
		return $form->getForm();
    }

    function gridViewHTMLHeader($arrResult, $sGenerate) {
		$result = '';
		$result .= '<div id="div-header" style="overflow-x:auto;margin-left:30px; margin-right:30px;"><h2><b>Información de '.$sGenerate.'</b></h2>';
		$result .= '<table id="table-header" border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">DocNum</th>';
				$result .= '<th class="grid_cabecera">DocType</th>';
				// $result .= '<th class="grid_cabecera">Series</th>'; //CABECERA YA NO VA
				$result .= '<th class="grid_cabecera">CardCode</th>';
				$result .= '<th class="grid_cabecera">DocDate</th>';
				$result .= '<th class="grid_cabecera">DocDueDate</th>';
				$result .= '<th class="grid_cabecera">TaxDate</th>';
				$result .= '<th class="grid_cabecera">DocCurrency</th>';
				$result .= '<th class="grid_cabecera">NumAtCard</th>';
				$result .= '<th class="grid_cabecera">DocumentSubType</th>';
				$result .= '<th class="grid_cabecera">U_SYP_MDSD</th>'; //CAMBIAMOS FolioPrefixString por U_SYP_MDSD
				$result .= '<th class="grid_cabecera">U_SYP_MDCD</th>'; //CAMBIAMOS FolioNumber por U_SYP_MDCD
				$result .= '<th class="grid_cabecera">PaymentGroupCode</th>';
				$result .= '<th class="grid_cabecera">U_SYP_MDTD</th>'; //CAMBIAMOS Indicator por U_SYP_MDTD
				$result .= '<th class="grid_cabecera">U_SYP_MDMT</th>'; //CAMBIAMOS U_EXX_TIPOOPER por U_SYP_MDMT
				// $result .= '<th class="grid_cabecera">U_EXX_SUJPER</th>'; //CABECERA YA NO VA
				// $result .= '<th class="grid_cabecera">U_EXX_INCPER</th>'; //CABECERA YA NO VA
				$result .= '<th class="grid_cabecera">DocTotal</th>';
				$result .= '<th class="grid_cabecera">SalesPersonCode</th>';
				// $result .= '<th class="grid_cabecera">U_EXC_TIPVEN</th>'; //CABECERA YA NO VA
				$result .= '<th class="grid_cabecera">Comments</th>';
				$result .= '<th class="grid_cabecera">JournalMemo</th>';
				$result .= '<th class="grid_cabecera">U_CTG_FECRECEP</th>'; //CAMBIAMOS U_EXC_FECRECEP por U_CTG_FECRECEP
				// $result .= '<th class="grid_cabecera">U_EXX_COMPER</th>'; //CABECERA YA NO VA
				$result .= '<th class="grid_cabecera">U_CTG_NUMLIQ</th>';
				// $result .= '<th class="grid_cabecera">U_EXX_SERIE</th>'; //CABECERA YA NO VA
				// $result .= '<th class="grid_cabecera">U_EXX_NROINI</th>'; //CABECERA YA NO VA
				// $result .= '<th class="grid_cabecera">U_EXX_NROFIN</th>'; //CABECERA YA NO VA
			$result .= '</tr>';

			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">Num SAP</th>';
				$result .= '<th class="grid_cabecera">Tipo Documento</th>';
				// $result .= '<th class="grid_cabecera">Codigo Serie</th>'; //CABECERA YA NO VA
				$result .= '<th class="grid_cabecera">Codigo SN</th>';
				$result .= '<th class="grid_cabecera">Fecha Contabilizacion</th>';
				$result .= '<th class="grid_cabecera">Fecha Vencimiento</th>';
				$result .= '<th class="grid_cabecera">Fecha Documento</th>';
				$result .= '<th class="grid_cabecera">Moneda</th>';
				$result .= '<th class="grid_cabecera">Numero Documento</th>';
				$result .= '<th class="grid_cabecera">DocumentSubType</th>';
				$result .= '<th class="grid_cabecera">Serie</th>'; //CAMBIAMOS Folio Serie por Serie
				$result .= '<th class="grid_cabecera">Num. Correlativo</th>'; //CAMBIAMOS Folio Correlativo por Num. Correlativo
				$result .= '<th class="grid_cabecera">Condicion Pago</th>';
				$result .= '<th class="grid_cabecera">Tipo Documento</th>'; //CAMBIAMOS Tipo Documento por Tipo Documento
				$result .= '<th class="grid_cabecera">Motivo Traslado</th>'; //CAMBIAMOS Tipo Operacion por Motivo Traslado
				// $result .= '<th class="grid_cabecera">Sujeto a percepcion</th>'; //CABECERA YA NO VA
				// $result .= '<th class="grid_cabecera">Incluye Percepcion</th>'; //CABECERA YA NO VA
				$result .= '<th class="grid_cabecera">Importe Total</th>';
				$result .= '<th class="grid_cabecera">Empleado Ventas</th>';
				// $result .= '<th class="grid_cabecera">Tipo de Venta</th>'; //CABECERA YA NO VA
				$result .= '<th class="grid_cabecera">Comentario</th>';
				$result .= '<th class="grid_cabecera">Glosa Asiento Diario</th>';
				$result .= '<th class="grid_cabecera">Fecha de Recepcion</th>'; //CAMBIAMOS Fecha de Recepcion por Fecha de Recepcion
				// $result .= '<th class="grid_cabecera">Es Doc Percep</th>'; //CABECERA YA NO VA
				$result .= '<th class="grid_cabecera">Turno</th>';
				// $result .= '<th class="grid_cabecera">Serie</th>'; //CABECERA YA NO VA
				// $result .= '<th class="grid_cabecera">Numero Inicio</th>'; //CABECERA YA NO VA
				// $result .= '<th class="grid_cabecera">Numero Final</th>'; //CABECERA YA NO VA
			$result .= '</tr>';

			$result .= '<tbody>';
			if($arrResult['bStatus']) {
				$counter = 0;
				$iCorrelativo=1;
				foreach ($arrResult['arrData'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
			    		$result .= '<td align="center">' . $iCorrelativo . '</td>';
			    		$result .= '<td align="center">dDocument_Items</td>';
			    		// $result .= '<td align="center">' . $row['series'] . '</td>'; //CABECERA YA NO VA
			    		$result .= '<td align="center">' . $row['cardcode'] . '</td>';
			    		$result .= '<td align="center">' . $row['docdate'] . '</td>';
			    		$result .= '<td align="center">' . $row['docduedate'] . '</td>';
			    		$result .= '<td align="center">' . $row['taxdate'] . '</td>';
			    		$result .= '<td align="center">S/.</td>'; //CAMBIAMOS SOL POR S/.
			    		$result .= '<td align="center">' . $row['indicator'] . "-" . $row['numatcard'] . '</td>'; //AGREGAMOS INDICADOR CON UN GUION PARA QUE TENGA ESTE FORMATO: 03-B020-00502974
			    		$result .= '<td align="center">bod_None</td>';
			    		$result .= '<td align="center">' . $row['folioprefixstring'] . '</td>';
			    		$result .= '<td align="center">' . $row['folionumber'] . '</td>';
			    		$result .= '<td align="center">' . $row['paymentgroupcode'] . '</td>';
			    		$result .= '<td align="center">' . $row['indicator'] . '</td>';
			    		$result .= '<td align="center">01</td>';
			    		// $result .= '<td align="center">N</td>'; //CABECERA YA NO VA
						// $result .= '<td align="center">N</td>'; //CABECERA YA NO VA
			    		$result .= '<td align="center">' . $row['doctotal'] . '</td>';
			    		$result .= '<td align="center">' . $row['salespersoncode'] . '</td>';
			    		// $result .= '<td align="center">06</td>'; //CABECERA YA NO VA
			    		$result .= '<td align="center">' . $row['comments'] . '</td>';
			    		$result .= '<td align="center">' . $row['journalmemo'] . '</td>';
			    		$result .= '<td align="center">' . $row['u_exc_fecrecep'] . '</td>';
			    		// $result .= '<td align="center">N</td>'; //CABECERA YA NO VA
			    		$result .= '<td align="center">' . $row['u_ctg_numliq'] . '</td>';
			    		// $result .= '<td align="center">' . $row['u_exx_serie'] . '</td>'; //CABECERA YA NO VA
			    		// $result .= '<td align="center">' . $row['u_exx_nroini'] . '</td>'; //CABECERA YA NO VA
			    		// $result .= '<td align="center">' . $row['u_exx_nrofin'] . '</td>'; //CABECERA YA NO VA
				    $result .= '</tr>';
				    ++$counter;
				    ++$iCorrelativo;
				}
			}
			$result .= '</tbody>';
		$result .= '</table></div>';
		return $result;
    }

	function gridViewExcelHeader($arrResult, $sGenerate) {
		$chrFileName='';
		$workbook = new Workbook($chrFileName);

		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato_string_sborder =& $workbook->add_format();

		$formato_string_left =& $workbook->add_format();
		$formato_string =& $workbook->add_format();
		$formato_numero =& $workbook->add_format();
		$resumen_formato =& $workbook->add_format();
		$formato_special =& $workbook->add_format();
		$formato_total =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		$formato_string_sborder->set_size(10);
		$formato_string_sborder->set_bold(1);
		$formato_string_sborder->set_align('center');

		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_bottom(1);
		$formato2->set_bottom_color(8);
		$formato2->set_top(1);
		$formato2->set_top_color(8);
		$formato2->set_right(1);
		$formato2->set_right_color(8);
		$formato2->set_left(1);
		$formato2->set_left_color(8);
		$formato2->set_align('center');

		$formato_special->set_size(10);
		$formato_special->set_bold(1);

		$formato_total->set_size(9);
		$formato_total->set_bold(1);
		$formato_total->set_align('right');

		$formato_string_left->set_size(10);
		$formato_string_left->set_align('left');

		$formato_string->set_size(10);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_align('right');

		$worksheet1 =& $workbook->add_worksheet($sGenerate);
		$worksheet1->set_column(0, 0, 10);//Correlativo SAP
		$worksheet1->set_column(1, 1, 15);//DocType
		// $worksheet1->set_column(2, 2, 20);//Series
		$worksheet1->set_column(2, 2, 15);//CardCode
		$worksheet1->set_column(3, 3, 23);//DocDate
		$worksheet1->set_column(4, 4, 23);//DocDueDate
		$worksheet1->set_column(5, 5, 23);//TaxDate
		$worksheet1->set_column(6, 6, 13);//Moneda
		$worksheet1->set_column(7, 7, 20);//NumAtCard
		$worksheet1->set_column(8, 8, 20);//DocumentSubType
		$worksheet1->set_column(9, 9, 15);
		$worksheet1->set_column(10, 10, 15);
		$worksheet1->set_column(11, 11, 15);
		$worksheet1->set_column(12, 12, 15);
		$worksheet1->set_column(13, 13, 15);
		$worksheet1->set_column(14, 14, 15);
		$worksheet1->set_column(15, 15, 15);
		$worksheet1->set_column(16, 16, 15);
		$worksheet1->set_column(17, 17, 15);
		$worksheet1->set_column(18, 18, 15);
		$worksheet1->set_column(19, 19, 15);

		$fila = 0;
		$worksheet1->write_string($fila, 6, "Informacion de " . $sGenerate, $formato0);

		$fila = 2;
		$worksheet1->write_string($fila, 0, "DocNum", $formato2);
		$worksheet1->write_string($fila, 1, "DocType", $formato2);
		// $worksheet1->write_string($fila, 2, "Series", $formato2); //CABECERA YA NO VA
		$worksheet1->write_string($fila, 2, "CardCode", $formato2);
		$worksheet1->write_string($fila, 3, "DocDate", $formato2);
		$worksheet1->write_string($fila, 4, "DocDueDate", $formato2);
		$worksheet1->write_string($fila, 5, "TaxDate", $formato2);
		$worksheet1->write_string($fila, 6, "DocCurrency", $formato2);
		$worksheet1->write_string($fila, 7, "NumAtCard", $formato2);
		$worksheet1->write_string($fila, 8, "DocumentSubType", $formato2);
		$worksheet1->write_string($fila, 9, "U_SYP_MDSD", $formato2); //CAMBIAMOS FolioPrefixString por U_SYP_MDSD
		$worksheet1->write_string($fila, 10, "U_SYP_MDCD", $formato2); //CAMBIAMOS FolioNumber por U_SYP_MDCD
		$worksheet1->write_string($fila, 11, "PaymentGroupCode", $formato2);
		$worksheet1->write_string($fila, 12, "U_SYP_MDTD", $formato2); //CAMBIAMOS Indicator por U_SYP_MDTD
		$worksheet1->write_string($fila, 13, "U_SYP_MDMT", $formato2); //CAMBIAMOS U_EXX_TIPOOPER por U_SYP_MDMT
		// $worksheet1->write_string($fila, 15, "U_EXX_SUJPER", $formato2); //CABECERA YA NO VA
		// $worksheet1->write_string($fila, 16, "U_EXX_INCPER", $formato2); //CABECERA YA NO VA
		$worksheet1->write_string($fila, 14, "DocTotal", $formato2);
		$worksheet1->write_string($fila, 15, "SalesPersonCode", $formato2);
		// $worksheet1->write_string($fila, 19, "U_EXC_TIPVEN", $formato2); //CABECERA YA NO VA
		$worksheet1->write_string($fila, 16, "Comments", $formato2);
		$worksheet1->write_string($fila, 17, "JournalMemo", $formato2);
		$worksheet1->write_string($fila, 18, "U_CTG_FECRECEP", $formato2); //CAMBIAMOS U_EXC_FECRECEP por U_CTG_FECRECEP
		// $worksheet1->write_string($fila, 23, "U_EXX_COMPER", $formato2); //CABECERA YA NO VA
		$worksheet1->write_string($fila, 19, "U_CTG_NUMLIQ", $formato2);
		// $worksheet1->write_string($fila, 25, "U_EXX_SERIE", $formato2); //CABECERA YA NO VA
		// $worksheet1->write_string($fila, 26, "U_EXX_NROINI", $formato2); //CABECERA YA NO VA
		// $worksheet1->write_string($fila, 27, "U_EXX_NROFIN", $formato2); //CABECERA YA NO VA

		$fila++;
		$worksheet1->write_string($fila, 0, "Num SAP", $formato2);
		$worksheet1->write_string($fila, 1, "Tipo Documento", $formato2);
		// $worksheet1->write_string($fila, 2, "Codigo Serie", $formato2); //CABECERA YA NO VA
		$worksheet1->write_string($fila, 2, "Codigo SN", $formato2);
		$worksheet1->write_string($fila, 3, "Fecha Contabilizacion", $formato2);
		$worksheet1->write_string($fila, 4, "Fecha Vencimiento", $formato2);
		$worksheet1->write_string($fila, 5, "Fecha Documento", $formato2);
		$worksheet1->write_string($fila, 6, "Moneda", $formato2);
		$worksheet1->write_string($fila, 7, "Numero Documento", $formato2);
		$worksheet1->write_string($fila, 8, "DocumentSubType", $formato2);
		$worksheet1->write_string($fila, 9, "Serie", $formato2); //CAMBIAMOS Folio Serie por Serie
		$worksheet1->write_string($fila, 10, "Num. Correlativo", $formato2); //CAMBIAMOS Folio Correlativo por Num. Correlativo
		$worksheet1->write_string($fila, 11, "Condicion Pago", $formato2);
		$worksheet1->write_string($fila, 12, "Tipo Documento", $formato2); //CAMBIAMOS Tipo Documento por Tipo Documento
		$worksheet1->write_string($fila, 13, "Motivo Traslado", $formato2); //CAMBIAMOS Tipo Operacion por Motivo Traslado
		// $worksheet1->write_string($fila, 15, "Sujeto a percepcion", $formato2); //CABECERA YA NO VA
		// $worksheet1->write_string($fila, 16, "Incluye Percepcion", $formato2); //CABECERA YA NO VA
		$worksheet1->write_string($fila, 14, "Importe Total", $formato2);
		$worksheet1->write_string($fila, 15, "Empleado Ventas", $formato2);
		// $worksheet1->write_string($fila, 19, "Tipo de Venta", $formato2); //CABECERA YA NO VA
		$worksheet1->write_string($fila, 16, "Comentario", $formato2);
		$worksheet1->write_string($fila, 17, "Glosa Asiento Diario", $formato2);
		$worksheet1->write_string($fila, 18, "Fecha de Recepcion", $formato2); //CAMBIAMOS Fecha de Recepcion por Fecha de Recepcion
		// $worksheet1->write_string($fila, 23, "Es Doc Percep", $formato2); //CABECERA YA NO VA
		$worksheet1->write_string($fila, 19, "Turno", $formato2);
		// $worksheet1->write_string($fila, 25, "Serie", $formato2); //CABECERA YA NO VA
		// $worksheet1->write_string($fila, 26, "Numero Inicio", $formato2); //CABECERA YA NO VA
		// $worksheet1->write_string($fila, 27, "Numero Final", $formato2); //CABECERA YA NO VA

		++$fila;
		if($arrResult['bStatus']) {
			$iCorrelativo=1;
			foreach ($arrResult['arrData'] as $row) {
				$worksheet1->write_string($fila, 0, $iCorrelativo, $formato_string);
				$worksheet1->write_string($fila, 1, 'dDocument_Items', $formato_string);
				// $worksheet1->write_string($fila, 2, $row['series'], $formato_string); //CABECERA YA NO VA
				$worksheet1->write_string($fila, 2, $row['cardcode'], $formato_string);
				$worksheet1->write_string($fila, 3, $row['docdate'], $formato_string);
				$worksheet1->write_string($fila, 4, $row['docduedate'], $formato_string);
				$worksheet1->write_string($fila, 5, $row['taxdate'], $formato_string);
				$worksheet1->write_string($fila, 6, 'S/.', $formato_string); //CAMBIAMOS SOL POR S/.
				$worksheet1->write_string($fila, 7, $row['indicator'] . "-" . $row['numatcard'], $formato_string); //AGREGAMOS INDICADOR CON UN GUION PARA QUE TENGA ESTE FORMATO: 03-B020-00502974
				$worksheet1->write_string($fila, 8, 'bod_None', $formato_string);
				$worksheet1->write_string($fila, 9, $row['folioprefixstring'], $formato_string);
				$worksheet1->write_string($fila, 10, $row['folionumber'], $formato_string);
				$worksheet1->write_string($fila, 11, $row['paymentgroupcode'], $formato_string);
				$worksheet1->write_string($fila, 12, $row['indicator'], $formato_string);
				$worksheet1->write_string($fila, 13, '01', $formato_string);
				// $worksheet1->write_string($fila, 15, 'N', $formato_string); //CABECERA YA NO VA
				// $worksheet1->write_string($fila, 16, 'N', $formato_string); //CABECERA YA NO VA
				$worksheet1->write_string($fila, 14, $row['doctotal'], $formato_string);
				$worksheet1->write_string($fila, 15, $row['salespersoncode'], $formato_string);
				// $worksheet1->write_string($fila, 19, '06', $formato_string); //CABECERA YA NO VA
				$worksheet1->write_string($fila, 16, $row['comments'], $formato_string);
				$worksheet1->write_string($fila, 17, $row['journalmemo'], $formato_string);
				$worksheet1->write_string($fila, 18, $row['u_exc_fecrecep'], $formato_string);
				// $worksheet1->write_string($fila, 23, 'N', $formato_string); //CABECERA YA NO VA
				$worksheet1->write_string($fila, 19, $row['u_ctg_numliq'], $formato_string);
				// $worksheet1->write_string($fila, 25, $row['u_exx_serie'], $formato_string); //CABECERA YA NO VA
				// $worksheet1->write_string($fila, 26, $row['u_exx_nroini'], $formato_string); //CABECERA YA NO VA 
				// $worksheet1->write_string($fila, 27, $row['u_exx_nrofin'], $formato_string); //CABECERA YA NO VA
				++$iCorrelativo;
				++$fila;
			}
		}

		$workbook->close();

		$chrFileName = $sGenerate;
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}

    function gridViewHTMLDetail($arrResult, $sGenerate, $fTax) {
		$result = '';
		$result .= '<div id="div-detail" style="overflow-x:auto;margin-left:30px; margin-right:30px;"><h2><b>Información de '.$sGenerate.'</b></h2>';
		$result .= '<table id="table-detail" border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">ParentKey</th>'; //1
				$result .= '<th class="grid_cabecera">LineNum</th>'; //2
				$result .= '<th class="grid_cabecera">ItemCode</th>'; //3
				$result .= '<th class="grid_cabecera">WarehouseCode</th>'; //4
				$result .= '<th class="grid_cabecera">Quantity</th>'; //5
				$result .= '<th class="grid_cabecera">Price</th>'; //6
				$result .= '<th class="grid_cabecera">TaxCode</th>'; //7		

				$result .= '<th class="grid_cabecera">CostingCode</th>'; //9
				$result .= '<th class="grid_cabecera">CostingCode2</th>'; //14
				$result .= '<th class="grid_cabecera">CostingCode3</th>'; //10		
				$result .= '<th class="grid_cabecera">CostingCode4</th>'; //15

				// $result .= '<th class="grid_cabecera">U_EXX_GRUPOPER</th>'; //8
				// $result .= '<th class="grid_cabecera">U_EXX_PERDGHDCM</th>'; //12
				$result .= '<th class="grid_cabecera">UnitsOfMeasurment</th>'; //11
				$result .= '<th class="grid_cabecera">MeasureUnit</th>'; //13
			$result .= '</tr>';

			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">Num SAP</th>'; //1
				$result .= '<th class="grid_cabecera">Num Linea</th>'; //2
				$result .= '<th class="grid_cabecera">Codigo Articulo</th>'; //3
				$result .= '<th class="grid_cabecera">Almacen</th>'; //4
				$result .= '<th class="grid_cabecera">Cantidad</th>'; //5
				$result .= '<th class="grid_cabecera">Precio</th>'; //6
				$result .= '<th class="grid_cabecera">Indicador Impuesto</th>'; //7
				
				$result .= '<th class="grid_cabecera">Establecimiento</th>'; //9
				$result .= '<th class="grid_cabecera">Centro de Costo</th>'; //14
				$result .= '<th class="grid_cabecera">Unidad Negocio</th>'; //10
				$result .= '<th class="grid_cabecera">Destino</th>'; //15

				// $result .= '<th class="grid_cabecera">Grupo Percepcion</th>'; //8				
				// $result .= '<th class="grid_cabecera">Dispensador combustible</th>'; //12
				$result .= '<th class="grid_cabecera">Factor Conversion</th>'; //11
				$result .= '<th class="grid_cabecera">Unidad Medida</th>'; //13
			$result .= '</tr>';

			$result .= '<tbody>';
			if($arrResult['bStatus']) {
				$counter = 0;
				$iCorrelativo=1;
				foreach ($arrResult['arrData'] as $row) {
					/**
					 * Conversion de Litros a GALONES solo para el producto GLP
					 * Cantidad / 3.7854 = Galones
					 * Precio * 3.7854 = Galones
					 */
					$fQuantity = $row['quantity'];
					$fPrice = $row['price'];
					if ( trim($row['itemcodeocs']) == '11620307' ){
						$fQuantity = $row['quantity'] / 3.785411784;
						$fPrice = $row['price'] * 3.785411784;
					}

					$fPrice = $fPrice / $fTax['tax'];

					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
			    		$result .= '<td align="center">' . $iCorrelativo . '</td>'; //1
			    		$result .= '<td align="center">0</td>'; //2
			    		$result .= '<td align="center">' . $row['itemcode'] . '</td>'; //3
			    		$result .= '<td align="center">' . $row['warehousecode'] . '</td>'; //4
			    		$result .= '<td align="center">' . round($fQuantity, 3) . '</td>'; //5
			    		$result .= '<td align="center">' . round($fPrice, 3) . '</td>'; //6
			    		$result .= '<td align="center">IGV</td>'; //7

						$result .= '<td align="center">' . $row['costingcode'] . '</td>'; //9
						$result .= '<td align="center">' . $row['costingcode2'] . '</td>'; //14
			    		$result .= '<td align="center">' . $row['costingcode3'] . '</td>'; //10
						$result .= '<td align="center">' . $row['costingcode4'] . '</td>'; //15

			    		// $result .= '<td align="center">0000</td>'; //8			    		
						// $result .= '<td align="center">' . $row['u_exx_perdghdcm'] . '</td>'; //12
			    		$result .= '<td align="center">' . $row['unitsofmeasurment'] . '</td>'; //11			    		
			    		$result .= '<td align="center">' . $row['measureunit'] . '</td>'; //13
				    $result .= '</tr>';
				    ++$counter;
				    ++$iCorrelativo;
				}
			}
			$result .= '</tbody>';
		$result .= '</table></div>';
		return $result;
    }
	
	function gridViewExcelDetail($arrResult, $sGenerate, $fTax) {
		$chrFileName='';
		$workbook = new Workbook($chrFileName);

		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato_string_sborder =& $workbook->add_format();

		$formato_string_left =& $workbook->add_format();
		$formato_string =& $workbook->add_format();
		$formato_numero =& $workbook->add_format();
		$resumen_formato =& $workbook->add_format();
		$formato_special =& $workbook->add_format();
		$formato_total =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		$formato_string_sborder->set_size(10);
		$formato_string_sborder->set_bold(1);
		$formato_string_sborder->set_align('center');

		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_bottom(1);
		$formato2->set_bottom_color(8);
		$formato2->set_top(1);
		$formato2->set_top_color(8);
		$formato2->set_right(1);
		$formato2->set_right_color(8);
		$formato2->set_left(1);
		$formato2->set_left_color(8);
		$formato2->set_align('center');

		$formato_special->set_size(10);
		$formato_special->set_bold(1);

		$formato_total->set_size(9);
		$formato_total->set_bold(1);
		$formato_total->set_align('right');

		$formato_string_left->set_size(10);
		$formato_string_left->set_align('left');

		$formato_string->set_size(10);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_align('right');

		$worksheet1 =& $workbook->add_worksheet($sGenerate);
		$worksheet1->set_column(0, 0, 10);//Correlativo SAP
		$worksheet1->set_column(1, 1, 15);//DocType
		$worksheet1->set_column(2, 2, 20);//Series
		$worksheet1->set_column(3, 3, 15);//CardCode
		$worksheet1->set_column(4, 4, 15);//DocDate
		$worksheet1->set_column(5, 5, 15);//DocDueDate
		$worksheet1->set_column(6, 6, 10);//TaxDate
		$worksheet1->set_column(7, 7, 15);//Moneda
		$worksheet1->set_column(8, 8, 15);//NumAtCard
		$worksheet1->set_column(9, 9, 15);//DocumentSubType

		$fila = 0;
		$worksheet1->write_string($fila, 6, "Informacion de " . $sGenerate, $formato0);

		$fila = 2;
		$worksheet1->write_string($fila, 0, "ParentKey", $formato2); //1
		$worksheet1->write_string($fila, 1, "LineNum", $formato2); //2
		$worksheet1->write_string($fila, 2, "ItemCode", $formato2); //3
		$worksheet1->write_string($fila, 3, "WarehouseCode", $formato2); //4
		$worksheet1->write_string($fila, 4, "Quantity", $formato2); //5
		$worksheet1->write_string($fila, 5, "Price", $formato2); //6
		$worksheet1->write_string($fila, 6, "TaxCode", $formato2); //7
	
		$worksheet1->write_string($fila, 7, "CostingCode", $formato2); //9
		$worksheet1->write_string($fila, 8, "CostingCode2", $formato2); //14
		$worksheet1->write_string($fila, 9, "CostingCode3", $formato2); //10
		$worksheet1->write_string($fila, 10, "CostingCode4", $formato2); //15

		// $worksheet1->write_string($fila, 11, "U_EXX_GRUPOPER", $formato2); //8
		// $worksheet1->write_string($fila, 12, "U_EXX_PERDGHDCM", $formato2); //12
		$worksheet1->write_string($fila, 11, "UnitsOfMeasurment", $formato2); //11
		$worksheet1->write_string($fila, 12, "MeasureUnit", $formato2); //13

		$fila++;
		$worksheet1->write_string($fila, 0, "Num SAP", $formato2); //1
		$worksheet1->write_string($fila, 1, "Num Linea", $formato2); //2
		$worksheet1->write_string($fila, 2, "Codigo Articulo", $formato2); //3
		$worksheet1->write_string($fila, 3, "Almacen", $formato2); //4
		$worksheet1->write_string($fila, 4, "Cantidad", $formato2); //5
		$worksheet1->write_string($fila, 5, "Precio", $formato2); //6
		$worksheet1->write_string($fila, 6, "Indicador Impuesto", $formato2); //7

		$worksheet1->write_string($fila, 7, "Establecimiento", $formato2); //9
		$worksheet1->write_string($fila, 8, "Centro de Costo", $formato2); //14
		$worksheet1->write_string($fila, 9, "Unidad Negocio", $formato2); //10
		$worksheet1->write_string($fila, 10, "Destino", $formato2); //15

		// $worksheet1->write_string($fila, 11, "Grupo Percepcion", $formato2); //8
		// $worksheet1->write_string($fila, 12, "Dispensador combustible", $formato2); //12
		$worksheet1->write_string($fila, 11, "Factor Conversion", $formato2); //11
		$worksheet1->write_string($fila, 12, "Unidad Medida", $formato2); //13

		++$fila;
		if($arrResult['bStatus']) {
			$iCorrelativo=1;
			foreach ($arrResult['arrData'] as $row) {
				/**
				 * Conversion de Litros a GALONES solo para el producto GLP
				 * Cantidad / 3.7854 = Galones
				 * Precio * 3.7854 = Galones
				 */
				$fQuantity = $row['quantity'];
				$fPrice = $row['price'];
				if ( trim($row['itemcodeocs']) == '11620307' ){
					$fQuantity = $row['quantity'] / 3.785411784;
					$fPrice = $row['price'] * 3.785411784;
				}

				$fPrice = $fPrice / $fTax['tax'];

				$worksheet1->write_string($fila, 0, $iCorrelativo, $formato_string); //1
				$worksheet1->write_string($fila, 1, '0', $formato_string); //2
				$worksheet1->write_string($fila, 2, $row['itemcode'], $formato_string); //3
				$worksheet1->write_string($fila, 3, $row['warehousecode'], $formato_string); //4
				$worksheet1->write_string($fila, 4, round($fQuantity, 3), $formato_string); //5
				$worksheet1->write_string($fila, 5, round($fPrice, 3), $formato_string); //6
				$worksheet1->write_string($fila, 6, 'IGV', $formato_string); //7
				
				$worksheet1->write_string($fila, 7, $row['costingcode'], $formato_string); //9
				$worksheet1->write_string($fila, 8, $row['costingcode2'], $formato_string); //14
				$worksheet1->write_string($fila, 9, $row['costingcode3'], $formato_string); //10
				$worksheet1->write_string($fila, 10, $row['costingcode4'], $formato_string); //15

				// $worksheet1->write_string($fila, 11, '0000', $formato_string); //8
				// $worksheet1->write_string($fila, 12, $row['u_exx_perdghdcm'], $formato_string); //12
				$worksheet1->write_string($fila, 11, $row['unitsofmeasurment'], $formato_string); //11
				$worksheet1->write_string($fila, 12, $row['measureunit'], $formato_string); //13
				++$iCorrelativo;
				++$fila;
			}
		}
		$workbook->close();

		$chrFileName = $sGenerate;
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}

    function gridViewHTMLGroupDetail($arrResult, $sGenerate) {
		$result = '';
		$result .= '<div id="div-detail-agrupados" style="overflow-x:auto;margin-left:30px; margin-right:30px;"><h2><b>Información de '.$sGenerate.' Agrupados</b></h2>';
		$result .= '<table id="table-detail-agrupados" border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">CODE</th>';
				$result .= '<th class="grid_cabecera">NAME</th>';
				$result .= '<th class="grid_cabecera">U_CTG_OFICINA</th>';
				$result .= '<th class="grid_cabecera">U_CTG_NUM_RANGO</th>';
				$result .= '<th class="grid_cabecera">U_CTG_FECHA</th>';
				$result .= '<th class="grid_cabecera">U_CTG_TIPOCOM</th>';
				$result .= '<th class="grid_cabecera">U_CTG_SERIE</th>';
				$result .= '<th class="grid_cabecera">U_CTG_NROINI</th>';
				$result .= '<th class="grid_cabecera">U_CTG_NROFIN</th>';
				$result .= '<th class="grid_cabecera">U_CTG_DOCIDE</th>';
				$result .= '<th class="grid_cabecera">U_CTG_RSOCIAL</th>';
				$result .= '<th class="grid_cabecera">U_CTG_COMP_PAGO</th>';
				$result .= '<th class="grid_cabecera">U_CTG_SER_PAGO</th>';
				$result .= '<th class="grid_cabecera">U_CTG_NUM_PAGO</th>';
				$result .= '<th class="grid_cabecera">U_CTG_TIPOIDE</th>';
				$result .= '<th class="grid_cabecera">U_CTG_BASE_IGV</th>';
				$result .= '<th class="grid_cabecera">U_CTG_BASE_EXO</th>';
				$result .= '<th class="grid_cabecera">U_CTG_BASE_INA</th>';
				$result .= '<th class="grid_cabecera">U_CTG_ISC</th>';
				$result .= '<th class="grid_cabecera">U_CTG_IGV</th>';
				$result .= '<th class="grid_cabecera">U_CTG_OTROS</th>';
				$result .= '<th class="grid_cabecera">U_CTG_IMP_TOTAL</th>';
				$result .= '<th class="grid_cabecera">U_CTG_DOC</th>';
			$result .= '</tr>';

			$result .= '<tbody>';
			if (count($arrResult['arrData'])>0) {
				$counter = 0;
				foreach ($arrResult['arrData'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
			    		$result .= '<td align="center">' . $row['numatcard'] . '</td>';
			    		$result .= '<td align="center">' . $row['numatcard'] . '</td>';
			    		$result .= '<td align="center">006</td>';
			    		$result .= '<td align="center">' . $row['number_ini_fin'] . '</td>';
			    		$result .= '<td align="center">' . $row['docdate'] . '</td>';
			    		$result .= '<td align="center">' . $row['indicator'] . '</td>';
			    		$result .= '<td align="center">' . $row['folioprefixstring'] . '</td>';
			    		$result .= '<td align="center">' . $row['folionumber'] . '</td>';
			    		$result .= '<td align="center"></td>';
			    		$result .= '<td align="center">66666666</td>';
			    		$result .= '<td align="center"></td>';
			    		$result .= '<td align="center"></td>';
			    		$result .= '<td align="center"></td>';
			    		$result .= '<td align="center"></td>';
			    		$result .= '<td align="center"></td>';
			    		$result .= '<td align="center">' . $row['importe'] . '</td>';
			    		$result .= '<td align="center">0</td>';
			    		$result .= '<td align="center">0</td>';
			    		$result .= '<td align="center">0</td>';
			    		$result .= '<td align="center">' . $row['impuestos'] . '</td>';
			    		$result .= '<td align="center">0</td>';
			    		$result .= '<td align="center">' . $row['doctotal'] . '</td>';
			    		$result .= '<td align="center">6</td>';
				    $result .= '</tr>';
				    ++$counter;
				}
			}
			$result .= '</tbody>';
		$result .= '</table></div>';
		return $result;
    }
	
	function gridViewExcelGroupDetail($arrResult, $sGenerate) {
		$chrFileName='';
		$workbook = new Workbook($chrFileName);

		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato_string_sborder =& $workbook->add_format();

		$formato_string_left =& $workbook->add_format();
		$formato_string =& $workbook->add_format();
		$formato_numero =& $workbook->add_format();
		$resumen_formato =& $workbook->add_format();
		$formato_special =& $workbook->add_format();
		$formato_total =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		$formato_string_sborder->set_size(10);
		$formato_string_sborder->set_bold(1);
		$formato_string_sborder->set_align('center');

		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_bottom(1);
		$formato2->set_bottom_color(8);
		$formato2->set_top(1);
		$formato2->set_top_color(8);
		$formato2->set_right(1);
		$formato2->set_right_color(8);
		$formato2->set_left(1);
		$formato2->set_left_color(8);
		$formato2->set_align('center');

		$formato_special->set_size(10);
		$formato_special->set_bold(1);

		$formato_total->set_size(9);
		$formato_total->set_bold(1);
		$formato_total->set_align('right');

		$formato_string_left->set_size(10);
		$formato_string_left->set_align('left');

		$formato_string->set_size(10);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_align('right');

		$worksheet1 =& $workbook->add_worksheet($sGenerate . ' Agrupados');
		$worksheet1->set_column(0, 0, 20);//Correlativo SAP
		$worksheet1->set_column(1, 1, 20);//DocType
		$worksheet1->set_column(2, 2, 13);//Series
		$worksheet1->set_column(3, 3, 25);//CardCode
		$worksheet1->set_column(4, 4, 15);//DocDate
		$worksheet1->set_column(5, 5, 15);//DocDueDate
		$worksheet1->set_column(6, 6, 13);//TaxDate
		$worksheet1->set_column(7, 7, 15);//Moneda
		$worksheet1->set_column(8, 8, 15);//NumAtCard
		$worksheet1->set_column(9, 9, 15);//DocumentSubType

		$fila = 0;
		$worksheet1->write_string($fila, 6, "Informacion de " . $sGenerate . " Agrupados", $formato0);

		$fila = 2;
		$worksheet1->write_string($fila, 0, "CODE", $formato2);
		$worksheet1->write_string($fila, 1, "NAME", $formato2);
		$worksheet1->write_string($fila, 2, "U_CTG_OFICINA", $formato2);
		$worksheet1->write_string($fila, 3, "U_CTG_NUM_RANGO", $formato2);
		$worksheet1->write_string($fila, 4, "U_CTG_FECHA", $formato2);
		$worksheet1->write_string($fila, 5, "U_CTG_TIPOCOM", $formato2);
		$worksheet1->write_string($fila, 6, "U_CTG_SERIE", $formato2);
		$worksheet1->write_string($fila, 7, "U_CTG_NROINI", $formato2);
		$worksheet1->write_string($fila, 8, "U_CTG_NROFIN", $formato2);
		$worksheet1->write_string($fila, 9, "U_CTG_DOCIDE", $formato2);
		$worksheet1->write_string($fila, 10, "U_CTG_RSOCIAL", $formato2);
		$worksheet1->write_string($fila, 11, "U_CTG_COMP_PAGO", $formato2);
		$worksheet1->write_string($fila, 12, "U_CTG_SER_PAGO", $formato2);
		$worksheet1->write_string($fila, 13, "U_CTG_NUM_PAGO", $formato2);
		$worksheet1->write_string($fila, 14, "U_CTG_TIPOIDE", $formato2);
		$worksheet1->write_string($fila, 15, "U_CTG_BASE_IGV", $formato2);
		$worksheet1->write_string($fila, 16, "U_CTG_BASE_EXO", $formato2);
		$worksheet1->write_string($fila, 17, "U_CTG_BASE_INA", $formato2);
		$worksheet1->write_string($fila, 18, "U_CTG_ISC", $formato2);
		$worksheet1->write_string($fila, 19, "U_CTG_IGV", $formato2);
		$worksheet1->write_string($fila, 20, "U_CTG_OTROS", $formato2);
		$worksheet1->write_string($fila, 21, "U_CTG_IMP_TOTAL", $formato2);
		$worksheet1->write_string($fila, 22, "U_CTG_DOC", $formato2);

		++$fila;
		if (count($arrResult['arrData'])>0) {
			$iCorrelativo=1;
			foreach ($arrResult['arrData'] as $row) {
				$worksheet1->write_string($fila, 0, $row['numatcard'], $formato_string);
				$worksheet1->write_string($fila, 1, $row['numatcard'], $formato_string);
				$worksheet1->write_string($fila, 2, '006', $formato_string);
				$worksheet1->write_string($fila, 3, $row['number_ini_fin'], $formato_string);
				$worksheet1->write_string($fila, 4, $row['docdate'], $formato_string);
				$worksheet1->write_string($fila, 5, $row['indicator'], $formato_string);
				$worksheet1->write_string($fila, 6, $row['folioprefixstring'], $formato_string);
				$worksheet1->write_string($fila, 7, $row['folionumber'], $formato_string);
				$worksheet1->write_string($fila, 8, '', $formato_string);
				$worksheet1->write_string($fila, 9, '66666666', $formato_string);
				$worksheet1->write_string($fila, 10, '', $formato_string);
				$worksheet1->write_string($fila, 11, '', $formato_string);
				$worksheet1->write_string($fila, 12, '', $formato_string);
				$worksheet1->write_string($fila, 13, '', $formato_string);
				$worksheet1->write_string($fila, 14, '', $formato_string);
				$worksheet1->write_string($fila, 15, $row['importe'], $formato_string);
				$worksheet1->write_string($fila, 16, '0', $formato_string);
				$worksheet1->write_string($fila, 17, '0', $formato_string);
				$worksheet1->write_string($fila, 18, '0', $formato_string);
				$worksheet1->write_string($fila, 19, $row['impuestos'], $formato_string);
				$worksheet1->write_string($fila, 20, '0', $formato_string);
				$worksheet1->write_string($fila, 21, $row['doctotal'], $formato_string);
				$worksheet1->write_string($fila, 22, '6', $formato_string);
				++$fila;
			}
		}
		$workbook->close();

		$chrFileName = $sGenerate.'_agrupados';
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}

    function gridViewHTMLResumen($arrResult, $sGenerate) {
		$result = '';
		$result .= '<div id="div-resumen-productos" style="overflow-x:auto;margin-left:30px; margin-right:30px;"><h2 align="center"><b>Información de '.$sGenerate.' - Productos</b></h2>';
		$result .= '<table id="table-resumen-productos" border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">CODIGO</th>';
				$result .= '<th class="grid_cabecera">PRODUCTO</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">IMPORTE</th>';
				$result .= '<th class="grid_cabecera">IMPUESTOS</th>';
				$result .= '<th class="grid_cabecera" colspan="5">TOTAL</th>';
			$result .= '</tr>';

			$result .= '<tbody>';
				//Resumen de productos
				$counter = 0;
				foreach ($arrResult['arrData']['arrProductos'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
			    		$result .= '<td align="center">' . $row['codigo'] . '</td>';
			    		$result .= '<td align="center">' . $row['producto'] . '</td>';
			    		$result .= '<td align="center">' . $row['cantidad'] . '</td>';
			    		$result .= '<td align="center">' . $row['importe'] . '</td>';
			    		$result .= '<td align="center">' . $row['impuestos'] . '</td>';
			    		$result .= '<td align="center" colspan="5">' . $row['total'] . '</td>';
				    $result .= '</tr>';
				    ++$counter;
				}
			$result .= '</tbody>';
		$result .= '</table></div>';

		$result .= '<div id="div-resumen-vales" style="overflow-x:auto;margin-left:30px; margin-right:30px;"><h2 align="center"><b>Información de '.$sGenerate.' - Vales</b></h2>';
		$result .= '<table id="table-resumen-vales" border="0" align="center" class="report_CRUD">';
			$result .= '<tbody>';
				//Resumen de notas de despachos
				$result .= '<tr bgcolor="#FFFFCD">';
					$result .= '<th class="grid_cabecera">FECHA</th>';
					$result .= '<th class="grid_cabecera">DESPACHO</th>';
					$result .= '<th class="grid_cabecera">PRODUCTO</th>';
					$result .= '<th class="grid_cabecera">CANTIDAD</th>';
					$result .= '<th class="grid_cabecera">PLACA</th>';
					$result .= '<th class="grid_cabecera">PAGO</th>';
					$result .= '<th class="grid_cabecera">CLIENTE</th>';
					$result .= '<th class="grid_cabecera">TURNO</th>';
					$result .= '<th class="grid_cabecera">HORA</th>';
					$result .= '<th class="grid_cabecera">ESTADO</th>';
				$result .= '</tr>';
				$counter = 0;
				foreach ($arrResult['arrData']['arrVales'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
			    		$result .= '<td align="center">' . $row['fecha'] . '</td>';
			    		$result .= '<td align="center">' . $row['despacho'] . '</td>';
			    		$result .= '<td align="center">' . $row['producto'] . '</td>';
			    		$result .= '<td align="center">' . $row['cantidad'] . '</td>';
			    		$result .= '<td align="center">' . $row['placa'] . '</td>';
			    		$result .= '<td align="center">' . $row['pago'] . '</td>';
			    		$result .= '<td align="center">' . $row['cliente'] . '</td>';
			    		$result .= '<td align="center">' . $row['turno'] . '</td>';
			    		$result .= '<td align="center">' . $row['hora'] . '</td>';
			    		$result .= '<td align="center">' . $row['estado'] . '</td>';
				    $result .= '</tr>';
				    ++$counter;
				}
			$result .= '</tbody>';
		$result .= '</table></div>';
		return $result;
    }
	
	function gridViewExcelResumen($arrResult, $sGenerate) {
		$chrFileName='';
		$workbook = new Workbook($chrFileName);

		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato_string_sborder =& $workbook->add_format();

		$formato_string_left =& $workbook->add_format();
		$formato_string =& $workbook->add_format();
		$formato_numero =& $workbook->add_format();
		$resumen_formato =& $workbook->add_format();
		$formato_special =& $workbook->add_format();
		$formato_total =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		$formato_string_sborder->set_size(10);
		$formato_string_sborder->set_bold(1);
		$formato_string_sborder->set_align('center');

		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_bottom(1);
		$formato2->set_bottom_color(8);
		$formato2->set_top(1);
		$formato2->set_top_color(8);
		$formato2->set_right(1);
		$formato2->set_right_color(8);
		$formato2->set_left(1);
		$formato2->set_left_color(8);
		$formato2->set_align('center');

		$formato_special->set_size(10);
		$formato_special->set_bold(1);

		$formato_total->set_size(9);
		$formato_total->set_bold(1);
		$formato_total->set_align('right');

		$formato_string_left->set_size(10);
		$formato_string_left->set_align('left');

		$formato_string->set_size(10);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_align('right');

		$worksheet1 =& $workbook->add_worksheet($sGenerate);
		$worksheet1->set_column(0, 0, 30);//CODIGO
		$worksheet1->set_column(1, 1, 20);//PRODUCTO
		$worksheet1->set_column(2, 2, 20);//CANTIDAD
		$worksheet1->set_column(3, 3, 15);//IMPORTE
		$worksheet1->set_column(4, 4, 15);//IMPUESTOS
		$worksheet1->set_column(5, 5, 15);//TOTAL
		$worksheet1->set_column(6, 6, 20);//CLIENTE
		$worksheet1->set_column(7, 7, 10);//TURNO
		$worksheet1->set_column(8, 8, 15);//HORA
		$worksheet1->set_column(9, 9, 15);//ESTADO

		$fila = 0;
		$worksheet1->write_string($fila, 0, "Informacion de " . $sGenerate . " - Productos", $formato0);

		$fila = 2;
		$worksheet1->write_string($fila, 0, "CODIGO", $formato2);
		$worksheet1->write_string($fila, 1, "PRODUCTO", $formato2);
		$worksheet1->write_string($fila, 2, "CANTIDAD", $formato2);
		$worksheet1->write_string($fila, 3, "IMPORTE", $formato2);
		$worksheet1->write_string($fila, 4, "IMPUESTOS", $formato2);
		$worksheet1->write_string($fila, 5, "TOTAL", $formato2);

		++$fila;
		foreach ($arrResult['arrData']['arrProductos'] as $row) {
			$worksheet1->write_string($fila, 0, $row['codigo'], $formato_string);
			$worksheet1->write_string($fila, 1, $row['producto'], $formato_string);
			$worksheet1->write_string($fila, 2, $row['cantidad'], $formato_string);
			$worksheet1->write_string($fila, 3, $row['importe'], $formato_string);
			$worksheet1->write_string($fila, 4, $row['impuestos'], $formato_string);
			$worksheet1->write_string($fila, 5, $row['total'], $formato_string);
			++$fila;
		}

		++$fila;
		$worksheet1->write_string($fila, 0, "Informacion de " . $sGenerate . " - Vales", $formato0);

		++$fila;
		$worksheet1->write_string($fila, 0, "FECHA", $formato2);
		$worksheet1->write_string($fila, 1, "DESPACHO", $formato2);
		$worksheet1->write_string($fila, 2, "PRODUCTO", $formato2);
		$worksheet1->write_string($fila, 3, "CANTIDAD", $formato2);
		$worksheet1->write_string($fila, 4, "PLACA", $formato2);
		$worksheet1->write_string($fila, 5, "PAGO", $formato2);
		$worksheet1->write_string($fila, 6, "CLIENTE", $formato2);
		$worksheet1->write_string($fila, 7, "TURNO", $formato2);
		$worksheet1->write_string($fila, 8, "HORA", $formato2);
		$worksheet1->write_string($fila, 9, "ESTADO", $formato2);

		++$fila;
		foreach ($arrResult['arrData']['arrVales'] as $row) {
			$worksheet1->write_string($fila, 0, $row['fecha'], $formato_string);
			$worksheet1->write_string($fila, 1, $row['despacho'], $formato_string);
			$worksheet1->write_string($fila, 2, $row['producto'], $formato_string);
			$worksheet1->write_string($fila, 3, $row['cantidad'], $formato_string);
			$worksheet1->write_string($fila, 4, $row['placa'], $formato_string);
			$worksheet1->write_string($fila, 5, $row['pago'], $formato_string);
			$worksheet1->write_string($fila, 6, $row['cliente'], $formato_string);
			$worksheet1->write_string($fila, 7, $row['turno'], $formato_string);
			$worksheet1->write_string($fila, 8, $row['hora'], $formato_string);
			$worksheet1->write_string($fila, 9, $row['estado'], $formato_string);
			++$fila;
		}

		$workbook->close();

		$chrFileName = $sGenerate;
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
    
    function gridViewHTMLTableConfiguration($arrResult, $sTitleTableConfiguration, $arrPOSTHeader) {    	
		$result = '';
		$result .= '<form method="post" name="Save" action="control.php" target="control">';
		$result .= '<input type="hidden" name="rqst" value="MOVIMIENTOS.INTERFAZSAP">';
		$result .= '<input type="hidden" name="iCodeTableConfiguration" value="' . $arrPOSTHeader['iCodeTableConfiguration'] . '">';
		$result .= '<div id="div-configuration" style="overflow-x:auto;margin-left:30px; margin-right:30px;"><h2 align="center"><b>Tabla de '.$sTitleTableConfiguration.'</b></h2>';
		$result .= '<table id="table-configuration" border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">internal_id</th>';
				$result .= '<th class="grid_cabecera">ocs_id</th>';
				$result .= '<th class="grid_cabecera">sap_id</th>';
				$result .= '<th class="grid_cabecera">name</th>';
				$result .= '<th class="grid_cabecera">description</th>';
				$result .= '<th class="grid_cabecera"></th>';
			$result .= '</tr>';
 			
 			$sNote = '';
			if ( $arrPOSTHeader['iCodeTableConfiguration'] == 3 ) {
				$sNote = '- Playa: ID de serie y Oficina: 10 = Factura / 35 = Boleta / 11 = ND / 20 = NC';
			}

			$result .= '<tbody>';
			if($arrResult['bStatus']) {
				$counter = 0;
				foreach ($arrResult['arrData'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'.$color.'">';
			    		$result .= '<td align="center">' . htmlentities($row["id_tipo_tabla_detalle"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["opencomb_codigo"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["sap_codigo"]) . '</td>';
				    	$result .= '<td align="left">' . htmlentities($row["name"]) . '</td>';
				    	$result .= '<td align="left">' . htmlentities($row["description"]) . '</td>';
				    	$result .= '<td align="left"><A href="javascript:confirmarLink(\'Deseas eliminar el registro Nro. '. htmlentities($row['id_tipo_tabla_detalle']).'?\',\'control.php?rqst=MOVIMIENTOS.INTERFAZSAP&action=Delete&iIDTipoTablaDetalle='.($row['id_tipo_tabla_detalle']).'&iCodeTableConfiguration='.($arrPOSTHeader['iCodeTableConfiguration']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';
				    $result .= '</tr>';
				    $counter++;
				}
				$result .= '<tr class="'.$color.'">';
					$result .= '<td align="center">&nbsp;</td>';
		    		$result .= '<td align="center"><input type="text" id="iIDOCS" name="iIDOCS" maxlength="30" size="30" value="" title="' . $sNote . '" autocomplete="off"></td>';
			    	$result .= '<td align="center"><input type="text" id="iIDSAP" name="iIDSAP" maxlength="50" size="50" value="" autocomplete="off"></td>';
			    	$result .= '<td align="center"><input type="text" id="sName" name="sName" maxlength="30" size="30" value="" autocomplete="off"></td>';
			    	$result .= '<td align="left"><input type="text" id="sDescription" name="sDescription" maxlength="255" value="" autocomplete="off"></td>';
			    $result .= '</tr>';
				$result .= '<tr class="'.$color.'">';
					$result .= '<td align="right" colspan="5"><button id="btn-html-add" name="action" type="submit" value="Save"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar </button></td>';
				$result .= '</tr>';
			}
			$result .= '</tbody>';
		$result .= '</table>';
		$result .= '</div>';
		$result .= '</form>';
		return $result;
    }
}
