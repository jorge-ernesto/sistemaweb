<?php
class VencimientoLotesTemplate extends Template {

	function getTitulo() {
		return '</br><h2 style="color: #336699;" align="center"><b>Control de Vencimiento de Lotes de Compra</b></h2>';
    }

	function formPrincipal($arrAlmacenes, $iAlmacen, $dInicial, $dFinal, $dCierre, $sTipoOrdenFVencimiento, $sTipoOrdenFEmision) {

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.VENCIMIENTOLOTES'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dCierre . '">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"  cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacen: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iAlmacen', '', $iAlmacen, $arrAlmacenes, espacios(3), array("onfocus" => "getFechasIF();getDatos();")));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dInicial', '', $dInicial, '', 12, 10));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dFinal', '', $dFinal, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Filtrar por: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		    	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="radio-sTipoOrden" value="FV" ' . $sTipoOrdenFVencimiento . '>Fecha Vencimiento'));
		    	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="radio-sTipoOrden" value="FE" ' . $sTipoOrdenFEmision . '>Fecha Emisión'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Estado: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
	    			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
    				<select name="cbo-Estado">
						<option value="0" >Todos</option>
						<option value="1" >Con Stock</option>
						<option value="2" >Vendido</option>
						<option value="3" >Caducado</option>
					</select>'
					));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" >'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("cbo-iAlmacen").focus();}</script>'));
		return $form->getForm();
	}

	function gridViewHTML($arrResult, $almacen, $fe_desde, $fe_hasta, $filtroV, $filtroE, $nu_estado) {

		//var_dump($arrResult);

		$result = '';
		$result .= '</br>';
		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera"></th>';
				$result .= '<th class="grid_cabecera">ALMACEN</th>';
				$result .= '<th class="grid_cabecera">NRO. COMPRA</th>';
				$result .= '<th class="grid_cabecera">NRO. LOTE</th>';
				$result .= '<th class="grid_cabecera">F. EMISIÓN</th>';
				$result .= '<th class="grid_cabecera">F. VENCIMIENTO</th>';
				$result .= '<th class="grid_cabecera">PROVEEDOR</th>';
				$result .= '<th class="grid_cabecera">DOCUMENTO</th>';
				$result .= '<th class="grid_cabecera">PRODUCTO</th>';
				$result .= '<th class="grid_cabecera">CANTIDAD</th>';
				$result .= '<th class="grid_cabecera">ESTADO</th>';
				$result .= '<th colspan="2"class="grid_cabecera"></th>';
				$result .= '<th class="grid_cabecera"></th>';
				$result .= '<th class="grid_cabecera"></th>';
			$result .= '</tr>';

			$result .= '<tbody>';
			if($arrResult['estado'] == FALSE) {
				$result .= '<tr class="bgcolor">';
					$result .= '<td colspan="12" class="grid_detalle_par" align="center"><b>No hay registros</b></td>';
				$result .= '</tr>';
			} else {
				$counter = 0;
				$_counter = 1;
				$validar_btn = FALSE;
				foreach ($arrResult['result'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
			    		$result .= '<td align="right">' . htmlentities($_counter) . '. </td>';
				    	$result .= '<td align="center">' . htmlentities($row["no_almacen"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["id_nu_formulario"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["no_lote"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["fe_emision"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["fe_vencimiento"]) . '</td>';
				    	$result .= '<td align="left">' . htmlentities($row["no_proveedor"]) . '</td>';
				    	$result .= '<td align="center">' . htmlentities($row["nu_documento"]) . '</td>';
				    	$result .= '<td align="left">' . htmlentities($row["no_producto"]) . '</td>';
				    	$result .= '<td align="right">' . htmlentities($row["ss_cantidad"]) . '</td>';
				    	
						if($row["no_estado"] == 'CADUCADO'){
				    	$result .= '<td align="left" style="color:#FF0000;">' . htmlentities($row["no_estado"]) . '</td>';
				    	}elseif($row["no_estado"] == 'CON STOCK'){
				    	$result .= '<td align="left" style="color:#088A08;">' . htmlentities($row["no_estado"]) . '</td>';
				    	}else{
				    	$result .= '<td align="left" style="color:#0404B4;">' . htmlentities($row["no_estado"]) . '</td>';
				    	}

				    	$formulario = $row["id_nu_formulario"];
				    	$articulo 	= $row["id_no_producto"];
				    	$lote 		= $row["id_lote"];

				    	$filtro="FV";

						if($row["nu_estado"] == '1'){
							$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="control.php?rqst=REPORTES.VENCIMIENTOLOTES&action=Actualizar&formulario='.$formulario.'&articulo='.$articulo.'&lote='.$lote.'&nu_estado=2&almacen='.$almacen.'&desde='.$fe_desde.'&hasta='.$fe_hasta.'&filtro='.$filtro.'&estado='.$nu_estado.'" target="control"><img src="/sistemaweb/icons/export.gif" align="right"/></A></td>';
							$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="control.php?rqst=REPORTES.VENCIMIENTOLOTES&action=Actualizar&formulario='.$formulario.'&articulo='.$articulo.'&lote='.$lote.'&nu_estado=3&almacen='.$almacen.'&desde='.$fe_desde.'&hasta='.$fe_hasta.'&filtro='.$filtro.'&estado='.$nu_estado.'" target="control"><img src="/sistemaweb/icons/gdelete.png" align="right"/></A></td>';
						}elseif($row["nu_estado"] == '2'){
							$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="control.php?rqst=REPORTES.VENCIMIENTOLOTES&action=Actualizar&formulario='.$formulario.'&articulo='.$articulo.'&lote='.$lote.'&nu_estado=1&almacen='.$almacen.'&desde='.$fe_desde.'&hasta='.$fe_hasta.'&filtro='.$filtro.'&estado='.$nu_estado.'" target="control"><img src="/sistemaweb/icons/update2.png" align="right"/></A></td>';
							$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="control.php?rqst=REPORTES.VENCIMIENTOLOTES&action=Actualizar&formulario='.$formulario.'&articulo='.$articulo.'&lote='.$lote.'&nu_estado=3&almacen='.$almacen.'&desde='.$fe_desde.'&hasta='.$fe_hasta.'&filtro='.$filtro.'&estado='.$nu_estado.'" target="control"><img src="/sistemaweb/icons/gdelete.png" align="right"/></A></td>';
						}else{
							$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="control.php?rqst=REPORTES.VENCIMIENTOLOTES&action=Actualizar&formulario='.$formulario.'&articulo='.$articulo.'&lote='.$lote.'&nu_estado=1&almacen='.$almacen.'&desde='.$fe_desde.'&hasta='.$fe_hasta.'&filtro='.$filtro.'&estado='.$nu_estado.'" target="control"><img src="/sistemaweb/icons/update2.png" align="right"/></A></td>';
							$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="control.php?rqst=REPORTES.VENCIMIENTOLOTES&action=Actualizar&formulario='.$formulario.'&articulo='.$articulo.'&lote='.$lote.'&nu_estado=2&almacen='.$almacen.'&desde='.$fe_desde.'&hasta='.$fe_hasta.'&filtro='.$filtro.'&estado='.$nu_estado.'" target="control"><img src="/sistemaweb/icons/export.gif" align="right"/></A></td>';
						}
						$result .= '<td>&nbsp&nbsp&nbsp&nbsp</td><td><A href="control.php?rqst=REPORTES.VENCIMIENTOLOTES&action=Modificar&formulario=' . htmlentities($row["id_nu_formulario"]) . '&articulo=' . htmlentities($row["id_no_producto"]) . '&lote=' . htmlentities($row["id_lote"]) . '" target="control" alt="Editar" title="Editar"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
				    $result .= '</tr>';
				    if(empty($row["sap_codigo"]))
				    	$validar_btn = TRUE;
				    $counter++;
				    $_counter++;
				}
			}
			$result .= '</tbody>';
		$result .= '</table>';
		return $result;
    }

	function formEdit($_row, $dCierre) {
		$row = $_row["result"][0];

		$form = new form2('', 'Editar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.VENCIMIENTOLOTES'));
	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-tformulario" name="txt-tformulario" value="' . $row['id_nu_formulario'] . '">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-tno_producto" name="txt-tno_producto" value="' . $row['id_no_producto'] . '">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-tlote" name="txt-tlote" value="' . $row['id_lote'] . '">'));

		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="text" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dCierre . '" onfocus="getFechasIF();getDatos();">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			</br>
			<tr align="center">
				<td colspan="2"><h2><b>Informacion del Registro: </b></h2></td>
			<tr>
			<tr>
				<td>Almacen: </td>
				<td><input type="text" value="' . $row['no_almacen'] . '" readonly=""></td>
			</tr>
			<tr>
				<td>Formulario Compra: </td>
				<td><input type="text" value="' . $row['id_nu_formulario'] . '" readonly=""></td>
			</tr>
			<tr>
				<td>Fecha Emisión: </td>
				<td><input type="text" value="' . $row['fe_emision'] . '" readonly=""></td>
			</tr>
			<tr>
				<td>Proveedor: </td>
				<td><input type="text" value="' . $row['no_proveedor'] . '" readonly=""></td>
			</tr>
			<tr>
				<td>Documento: </td>
				<td><input type="text" value="' . $row['nu_documento'] . '" readonly=""></td>
			</tr>
			<tr>
				<td>Producto: </td>
				<td><input type="text" value="' . $row['no_producto'] . '" readonly=""></td>
			</tr>
			<tr>
				<td>Cantidad: </td>
				<td><input type="text" value="' . $row['ss_cantidad'] . '" readonly=""></td>
			</tr>
			<tr>
				<td>Estado: </td>
				<td><input type="text" value="' . $row['no_estado'] . '" readonly=""></td>
			</tr>
			<tr align="center">
				<td colspan="2"><h2><b>Datos a Modificar: </b></h2></td>
			<tr>
			'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">Fecha Vencimiento: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="text" id="txt-dFechaVencimiento" name="txt-dFechaVencimiento" value="' . $row['fe_vencimiento'] . '">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">Lote: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="text" id="txt-dLote" name="txt-dLote" value="' . $row['no_lote'] . '" onfocus="getFechasIF();getDatos();">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Editar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Guardar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="button" id="btn-regresar" value="Buscar" onclick="regresar()"><img src="/sistemaweb/icons/greturn.png" align="right" />Regresar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("txt-dLote").focus();}</script>'));
		return $form->getForm();
	}


function gridViewExcel($arrData) {

		//var_dump($arrData);

		$chrFileName = "";

		$workbook = new Workbook($chrFileName);

		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato_string_sborder =& $workbook->add_format();

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

		$formato_string->set_size(10);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_align('right');

		$worksheet1 =& $workbook->add_worksheet('Control de Vencimiento de Lotes');
		$worksheet1->set_column(0, 0, 25);
		$worksheet1->set_column(1, 1, 20);
		$worksheet1->set_column(2, 2, 15);
		$worksheet1->set_column(3, 3, 20);//FECHA EMISION
		$worksheet1->set_column(4, 4, 20);
		$worksheet1->set_column(5, 5, 30);
		$worksheet1->set_column(6, 6, 15);
		$worksheet1->set_column(7, 7, 25);
		$worksheet1->set_column(8, 8, 15);
		$worksheet1->set_column(9, 9, 20);


		$fila = 0;
		$worksheet1->write_string($fila, 3, "CONTROL DE VENCIMIENTO DE LOTES DE COMPRAS", $formato0);

		$fila = 3;
		$worksheet1->write_string($fila, 0, "ALMACEN", $formato2);
		$worksheet1->write_string($fila, 1, "FORMULARIO COMPRA", $formato2);
		$worksheet1->write_string($fila, 2, "NRO. LOTE", $formato2);
		$worksheet1->write_string($fila, 3, "FECHA EMISION", $formato2);
		$worksheet1->write_string($fila, 4, "FECHA VENCIMIENTO", $formato2);
		$worksheet1->write_string($fila, 5, "PROVEEDOR", $formato2);
		$worksheet1->write_string($fila, 6, "DOCUMENTO", $formato2);
		$worksheet1->write_string($fila, 7, "PRODUCTO", $formato2);
		$worksheet1->write_string($fila, 8, "CANTIDAD", $formato2);
		$worksheet1->write_string($fila, 9, "ESTADO", $formato2);

		$fila++;
		
			$i=0;
			foreach ($arrData['result'] as $rows) {
					$worksheet1->write_string($fila, 0, $rows["no_almacen"], $formato0);
					$worksheet1->write_string($fila, 1, $rows["id_nu_formulario"], $formato0);
					$worksheet1->write_string($fila, 2, $rows["no_lote"], $formato0);
					$worksheet1->write_string($fila, 3, $rows["fe_emision"], $formato0);
					$worksheet1->write_string($fila, 4, $rows["fe_vencimiento"], $formato0);
					$worksheet1->write_string($fila, 5, $rows["no_proveedor"], $formato0);
					$worksheet1->write_string($fila, 6, $rows["nu_documento"], $formato0);
					$worksheet1->write_string($fila, 7, $rows["no_producto"], $formato0);
					$worksheet1->write_string($fila, 8, $rows["ss_cantidad"], $formato0);
					$worksheet1->write_string($fila, 9, $rows["no_estado"], $formato0);

					$fila++;
			}

		$workbook->close();	

		$chrFileName = "VencimientoLotes";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}


}

