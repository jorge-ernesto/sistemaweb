<?php

class PedidoComprasTemplate extends Template {  
	function searchForm() {
		$desde = date(d."/".m."/".Y);
		$hasta = date(d."/".m."/".Y);  
		$almacenes = PedidoComprasModel::obtieneListaEstaciones();

		$form = new form2('<h3><b>Pedido de Mercaderia</b></h3>', 'form_pedidocompras', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.PEDIDOCOMPRAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $_SESSION['almacen'], $almacenes, espacios(3), array("onfocus" => "getFechaEmision();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $desde, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_pedidocompras.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $hasta, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_pedidocompras.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Buscar", espacios(5)));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Agregar", espacios(5)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));	
	
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("almacen").focus();
			}
		</script>'
		));

		return $form->getForm();
	}

	function reporte($resultados) {
		$result='';
		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Nro. Pedido&nbsp;&nbsp;</th>';
				$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Fecha&nbsp;&nbsp;</th>';
				$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Tipo&nbsp;&nbsp;</th>';
				$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Observacion&nbsp;&nbsp;</th>';
				$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Fecha Actualizacion&nbsp;&nbsp;</th>';
				$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Usuario&nbsp;&nbsp;</th>';
				$result .= '<th class="grid_cabecera">&nbsp;&nbsp;IP&nbsp;&nbsp;</th>';
				$result .= '<th class="grid_cabecera" colspan="3" style="background: white;">&nbsp;</th>';
			$result .= '</tr>';
			$result .= '<tbody>';
		$iCounter = 0;
		for ($i=0; $i<count($resultados); $i++) {
			$sClassColorBackground = ($i%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			$result .= '<tr class="'. $sClassColorBackground. '">';
				$result .= '<td align="center">'.trim($resultados[$i]['num_pedido']).'</td>';
				$result .= '<td align="center">'.trim($resultados[$i]['fecha']).'</td>';
				$result .= '<td align="center">'.trim($resultados[$i]['tipo']).'</td>';
				$result .= '<td align="center">'.trim($resultados[$i]['observacion']).'</td>';
				$result .= '<td align="center">'.trim($resultados[$i]['actualizacion']).'</td>';
				$result .= '<td align="center">'.trim($resultados[$i]['usuario']).'</td>';
				$result .= '<td align="center">'.trim($resultados[$i]['ip']).'&nbsp;</td>';
				$nombre_almacen = PedidoComprasModel::obtieneNombreEstacion(trim($resultados[$i]['almacen']));
				$onclick = "'" . trim($resultados[$i]['num_pedido']) . "','" . trim($resultados[$i]['fecha']) . "','" . trim($resultados[$i]['tipo']) . "','" . trim($resultados[$i]['almacen']) . "','" . trim($nombre_almacen) . "','" . trim($resultados[$i]['observacion']) . "'";
				$result .= '<td>
								<a onclick="editarPedido('."$onclick".')">
									<img src="/sistemaweb/icons/anular.gif" alt="Editar" align="top" border="0"/>
								</a>								
								<!-- <a href="control.php?rqst=REPORTES.PEDIDOCOMPRAS&action=Editar&num_pedido='.trim($resultados[$i]['num_pedido']).'&fecha='.trim($resultados[$i]['fecha']).'&tipo='.trim($resultados[$i]['tipo']).'&almacen='.trim($resultados[$i]['almacen']).'&observacion='.trim($resultados[$i]['observacion']).'" target="control")">
									<img src="/sistemaweb/icons/anular.gif" alt="Editar" align="middle" border="0"/>
								</a> -->
							</td>';
				$result .= '<td><a href="control.php?rqst=REPORTES.PEDIDOCOMPRAS&action=PDF&id_cab='.trim($resultados[$i]['id_cab']).'"><img src="/sistemaweb/images/icono_pdf.gif" alt="PDF" align="middle" border="0"/></a></td>';
				$result .= '<td><a href="control.php?rqst=REPORTES.PEDIDOCOMPRAS&action=EXCEL&id_cab='.trim($resultados[$i]['id_cab']).'"><img src="/sistemaweb/icons/gexcel.png" alt="PDF" title="Excel" align="middle" border="0"/></a></td>';
				$result .= '<td><a href="control.php?rqst=REPORTES.PEDIDOCOMPRAS&action=Eliminar&id_cab='.trim($resultados[$i]['id_cab']).'"><img src="/sistemaweb/icons/delete.gif" alt="PDF" title="Eliminar" align="middle" border="0"/></a></td>';
			$result .= '</tr>';
		}
			$result .= '</tbody>';
		$result .= '</table>';
		return $result;
    }

	function formAgregarHeader() {
		$almacenes = PedidoComprasModel::obtieneListaEstaciones();
		$nropedido = PedidoComprasModel::obtieneNroPedido();
		$diaactual = date('d/m/Y');

		$tipopedido = Array(	"MINIMO"=>"Minimo", 
					"MAXIMO"=>"Maximo"); 

		$form = new form2('<h3><b>Agregar Pedido de Mercaderia</b></h3>', 'form_agregarheader', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.PEDIDOCOMPRAS'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="5">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen2", "", $_SESSION['almacen'], $almacenes, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Nro. Pedido</td><td>:</td><td class="form_label">'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("nropedido", "", $nropedido, "", 10, 10, '',array('readonly')));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha</td><td>:</td><td class="form_label">'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "", $diaactual, "", 10, 10, '',array('readonly')));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Linea</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('cod_linea2', '', '', '', 13, 15));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('nom_linea2', '', '', '', 30, 50,'',array('readonly')));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'lista_ayuda.php\',\'form_agregarheader.cod_linea2\',\'form_agregarheader.nom_linea2\',\'lineas\',\'\',\'<?php echo $valor;?>\');"> ')); */
		$viewLine = '<tr><td>Linea</td><td>:</td><td><input type="text" style="width: 100%" class="form_input" value="" id="cod_linea2" onkeyup="autocompleteBridge(0)" name="cod_linea2" placeholder = "Ingrese Linea">';
		$viewLine .= '<input type="hidden" readonly="" maxlength="50" size="30" class="form_input" value="" id="nom_linea2" name="nom_linea2"></td></tr>';
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($viewLine));

		/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Proveedor</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('cod_proveedor2', '', '', '', 13, 13));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('nom_proveedor2', '', '', '', 30, 50,'',array('readonly')));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'lista_ayuda.php\',\'form_agregarheader.cod_proveedor2\',\'form_agregarheader.nom_proveedor2\',\'proveedores\',\'\',\'<?php echo $valor;?>\');"> ')); */
		$viewPartner = '<tr><td>Proveedor</td><td>:</td><td><input type="text" style="width: 100%" maxlength="13" size="13" class="form_input" value="" id="cod_proveedor2" onkeyup="autocompleteBridge(1)" name="cod_proveedor2" placeholder = "Ingrese Nombre o RUC Proveedor">';
		$viewPartner .= '<input type="hidden" readonly="" maxlength="50" size="30" class="form_input" value="" id="nom_proveedor2" name="nom_proveedor2"></td></tr>';
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($viewPartner));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Tipo de Pedido</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('tipopedido','', '', $tipopedido, '&nbsp', '',''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Observaci&oacute;n</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('observacion" maxlength="255', '', '', '', 50, 80));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Modo de Filtro</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio("modo", 'Con Ventas', 'C', '<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio("modo", 'Sin ventas', 'S', '<br>', array(), array("checked")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" height="30">'));
		// $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Listar', '&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><div align="center"><input type="button" id="listar-pedido" name="action" value="Listar"  />&nbsp;&nbsp;&nbsp;'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="button" value="Regresar" onclick="regresar()"/></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}

	function formAgregarBody($res, $flag, $modo = "") {
		$cabecera   = $res['cabecera'];
		$resultados = $res['detalle'];

		$almacen = PedidoComprasModel::obtieneNombreEstacion($cabecera['almacen']);//2012-10-05	

		$form = new form2('', 'form_agregarbody" id="form-pedido', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.PEDIDOCOMPRAS'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" name="cab_almacen" class="cab_almacen" value="'.$cabecera['almacen'].'" />'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" name="sCodLinea" class="sCodLinea" value="'.trim($cabecera['linea']).'" />'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" name="sCodProveedor" class="sCodProveedor" value="'.trim($cabecera['proveedor']).'" />'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" name="cab_nropedido" class="cab_nropedido" value="'.$cabecera['nropedido'].'" />'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" name="cab_tipo" class="cab_tipo" value="'.$cabecera['tipo'].'" />'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" name="cab_observacion" class="cab_observacion" value="'.$cabecera['observacion'].'" />'));

		if($flag == "E") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div align="center"><h1 style="color:#336699">Pedido de Compra</h1></div>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Almacen</td><td>:</td><td><input type="text" style="width:160px" value="'.$almacen.'" readonly=""></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Nro. Pedido</td><td>:</td><td><input type="text" style="width:100px" value="'.$cabecera['nropedido'].'" readonly=""></td></tr>'));			
			$fecha = substr($cabecera['fecha'],8,2)."/".substr($cabecera['fecha'],5,2)."/".substr($cabecera['fecha'],0,4);
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Fecha</td><td>:</td><td><input type="text" style="width:70px" value="'.$fecha.'" readonly=""></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" name="cab_fecha" value="'.$cabecera['fecha'].'" />'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Tipo de Pedido</td><td>:</td><td><input type="text" style="width:60px" value="'.$cabecera['tipo'].'" readonly=""></td></tr>'));

			//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Observacion</td><td>:</td><td><input type="text" style="width:160px" value="'.$cabecera['observacion'].'" ></td></tr>'));		
			//$form->addElement(FORM_GROUP_MAIN, new f2element_text("pedido[".$i."]", "", $resultados[$i]['cantidad'], "", 15, 15, '',''));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Observaci&oacute;n</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text('observacion','',$cabecera['observacion'], '', 50, 80));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table><br><br><br>'));
		}
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" style="border: 1; border-style: simple; border-color: #000000;" align="center" id="tablaprueba">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;<input type="checkbox" class="check-all"></th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;Codigo&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;Descripcion&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" colspan="3">&nbsp;&nbsp;Venta 3 Ultimos Meses&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" colspan="3">&nbsp;&nbsp;Stock&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;Cantidad Pedido&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" rowspan="2">&nbsp;Cantidad Sugerida&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;Mes 1&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;Mes 2&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;Mes 3&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;Actual&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;Minimo&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;Maximo&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$color = '';
		for ($i = 0; $i<count($resultados); $i++) { //Recorremos array

			if($modo == "C"){
				if($resultados[$i]['mes_3'] > 0  || $resultados[$i]['mes_2'] > 0 || $resultados[$i]['mes_1'] > 0){ //Validamos que haya compras en los ultimos 3 meses

					$color = ($i%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<tr class='". $color. "'>"));
					if($flag == "E"){
						$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("id_det[".$i."]", @$resultados[$i]['num_detalle']));
					}

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox("vec_check[".$i."]".'" class="product-check', "", "S", "", "", array('checked')));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("codigo[".$i."]".'" class="product-code', "", trim($resultados[$i]['art_codigo']), "", 18, 18, '',array('readonly')));
					//$form->addElement(FORM_GROUP_MAIN, new f2element_text("descripcion[".$i."]", "", trim($resultados[$i]['art_descripcion']), "<td>", 45, 45, '',array('readonly')));

					/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['art_codigo'])));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));*/

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['art_descripcion'])));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['mes_1'])));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['mes_2'])));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['mes_3'])));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($resultados[$i]['stk_actual']));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

					/*
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes1[".$i."]", "", trim($resultados[$i]['mes_1']), "<td>", 10, 10, '',array('readonly')));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes2[".$i."]", "", trim($resultados[$i]['mes_2']), "<td>", 10, 10, '',array('readonly')));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes3[".$i."]", "", trim($resultados[$i]['mes_3']), "<td>", 10, 10, '',array('readonly')));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_actual[".$i."]", "", $resultados[$i]['stk_actual'], "<td>", 11, 11, '',array('readonly')));
					*/

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_minimo[".$i."]".'" class="stk-min', "", $resultados[$i]['stk_minimo'], "<td>", 11, 11, array("onKeyUp"=>"javascript:actPedido(this.value, '".$resultados[$i]['stk_actual']."', 'pedido[".$i."]')"),''));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_maximo[".$i."]".'" class="stk-max', "", $resultados[$i]['stk_maximo'], "<td>", 11, 11, array("onKeyUp"=>"javascript:actPedido(this.value, '".$resultados[$i]['stk_actual']."', 'pedido[".$i."]')"),''));

					if($resultados[$i]['sugerido'] > 0){
						/*
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($resultados[$i]['sugerido']));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
						*/
						$form->addElement(FORM_GROUP_MAIN, new f2element_text("pedido[".$i."]".'" class="qty', "", $resultados[$i]['sugerido'], "", 15, 15, '',''));
					} else {
						/*
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($resultados[$i]['cantidad']));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
						*/
						$form->addElement(FORM_GROUP_MAIN, new f2element_text("pedido[".$i."]".'" class="qty', "", $resultados[$i]['cantidad'], "", 15, 15, '',''));
					}

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['sugerido'])));	
					//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="text" style="width:110px" value="'.$resultados[$i]['sugerido'].'" readonly=""><td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;</tr>'));   
				
				}
				
			}else{

				$color = ($i%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<tr class='". $color. "'>"));
				if($flag == "E"){
					$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("id_det[".$i."]", @$resultados[$i]['num_detalle']));
				}

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox("vec_check[".$i."]".'" class="product-check', "", "S", "", "", array('checked')));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("codigo[".$i."]".'" class="product-code', "", trim($resultados[$i]['art_codigo']), "", 18, 18, '',array('readonly')));
				//$form->addElement(FORM_GROUP_MAIN, new f2element_text("descripcion[".$i."]", "", trim($resultados[$i]['art_descripcion']), "<td>", 45, 45, '',array('readonly')));

				/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['art_codigo'])));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));*/

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['art_descripcion'])));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['mes_1'])));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['mes_2'])));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['mes_3'])));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($resultados[$i]['stk_actual']));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				/*
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes1[".$i."]", "", trim($resultados[$i]['mes_1']), "<td>", 10, 10, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes2[".$i."]", "", trim($resultados[$i]['mes_2']), "<td>", 10, 10, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes3[".$i."]", "", trim($resultados[$i]['mes_3']), "<td>", 10, 10, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_actual[".$i."]", "", $resultados[$i]['stk_actual'], "<td>", 11, 11, '',array('readonly')));
				*/

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_minimo[".$i."]".'" class="stk-min', "", $resultados[$i]['stk_minimo'], "<td>", 11, 11, array("onKeyUp"=>"javascript:actPedido(this.value, '".$resultados[$i]['stk_actual']."', 'pedido[".$i."]')"),''));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_maximo[".$i."]".'" class="stk-max', "", $resultados[$i]['stk_maximo'], "<td>", 11, 11, array("onKeyUp"=>"javascript:actPedido(this.value, '".$resultados[$i]['stk_actual']."', 'pedido[".$i."]')"),''));

				if($resultados[$i]['sugerido'] > 0){
					/*
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($resultados[$i]['sugerido']));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
					*/
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("pedido[".$i."]".'" class="qty', "", $resultados[$i]['sugerido'], "", 15, 15, '',''));
				} else {
					/*
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($resultados[$i]['cantidad']));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
					*/
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("pedido[".$i."]".'" class="qty', "", $resultados[$i]['cantidad'], "", 15, 15, '',''));
				}

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(trim($resultados[$i]['sugerido'])));	
				//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="text" style="width:110px" value="'.$resultados[$i]['sugerido'].'" readonly=""><td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;</tr>')); 
			}

		}

		if($flag == "A"){
			// ****** linea para agregar nuevo articulo
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr id="row_insertar">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>')); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
			$viewProductoY .= '<input type="input" maxlength="18" size="18" class="form_input" value="" id="codigox" name="codigox"><td>';
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($viewProductoY));
			$viewProductoX = '<input type="text" style="width: 100%" maxlength="13" size="13" class="form_input" value="" id="descripcionx" onkeyup="autocompleteBridge(2)" name="descripcionx" placeholder = "Ingrese Nombre o Codigo de Producto"><td>';

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($viewProductoX));
			//$form->addElement(FORM_GROUP_MAIN, new f2element_text("descripcionx", "", "", "<td>", 45, 45, '',array('readonly')));


			$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes1x", "", "", "<td>", 10, 10, '',array('readonly')));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes2x", "", "", "<td>", 10, 10, '',array('readonly')));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes3x", "", "", "<td>", 10, 10, '',array('readonly')));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_actualx", "", "", "<td>", 11, 11, '',array('readonly')));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_minimox", "", "", "<td>", 11, 11, '',''));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("stk_maximox", "", "", "<td>", 11, 11, '',''));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("pedidox", "", "", "<td>", 15, 15, '',''));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("sugeridox", "", "", "<td>", 17, 17, '',array('readonly')));
			//$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Insertar", ""));	
			$ultimo_elemento = count($resultados);
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div align="center"><input type="button" id="insertar-pedido" data-rowinsertar="'.$ultimo_elemento.'" name="action" value="Insertar"  />'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));  
			// ****** fin linea para agregar nuevo articulo
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));	  

		if($flag != "E") {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><div align="center"><input type="button" id="guardar-pedido" name="action" value="Guardar Pedido"  />'));
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><div align="center"><input type="submit" name="action" value="Modificar" />'));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<input type="button" value="Regresar" onclick="regresar()"/></div>'));

		return $form->getForm();
	}

	function reportePDF($res) {
		//print_r($res);
		$cabecera   = $res['cabecera'];
		$resultados = $res['detalle'];

		$almacen = PedidoComprasModel::obtieneNombreEstacion($cabecera['almacen']);//2012-10-05	

		$cab = Array(
				"codigo"	=>	"CODIGO",
				"descripcion"	=>	"DESCRIPCION",
				"pedido"	=>	"CANTIDAD",
				"costouni"	=>	"C.U.",
				"costototal"	=>	"COSTO TOTAL"
			);

		/*$cab = Array(
				"codigo"	=>	"CODIGO",
				"descripcion"	=>	"DESCRIPCION",
				"mes3"		=>	"MES 3",
				"mes2"		=>	"MES 2",
				"mes1"		=>	"MES 1",
				"stk_actual"	=>	"STK.ACTUAL",
				"stk_minimo"	=>	"STK.MIN.",
				"stk_maximo"	=>	"STK.MAX.",
				"pedido"	=>	"PEDIDO"
			);
		*/

		$reporte = new CReportes2("P","pt","A4");

		$reporte->Ln();	 
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabecera(2, "L", "Pagina %p");
		$reporte->definirCabeceraSize(3, "C", "courier,B,15", "PEDIDO DE COMPRA Nro. ".$cabecera['numpedido']."");
		$reporte->definirCabecera(4, "L", "ALMACEN         : ".$almacen);
		$reporte->definirCabecera(5, "L", "FECHA           : ".$cabecera['fecha']);
		$reporte->definirCabecera(6, "L", "TIPO DE PEDIDO  : ".$cabecera['tipo']);
		$reporte->definirCabecera(7, "L", "OBSERVACION     : ".$cabecera['observacion']);
		$reporte->definirCabecera(8, "L", "USUARIO         : ".$cabecera['usuario']);
		$reporte->definirCabecera(9, "C", " ");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 8);

		$reporte->definirColumna("codigo",$reporte->TIPO_TEXTO,13,"L", "_pri");
		$reporte->definirColumna("descripcion",$reporte->TIPO_TEXTO,60,"L", "_pri");
		$reporte->definirColumna("pedido",$reporte->TIPO_IMPORTE,15,"R", "_pri");
		$reporte->definirColumna("costouni",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("costototal",$reporte->TIPO_IMPORTE,15,"R", "_pri");

		$reporte->definirColumna("texto",$reporte->TIPO_TEXTO,101,"R", "_pro");
		$reporte->definirColumna("totalizado",$reporte->TIPO_IMPORTE,15,"R", "_pro");

		/*$reporte->definirColumna("codigo",$reporte->TIPO_TEXTO,13,"L", "_pri");
		$reporte->definirColumna("descripcion",$reporte->TIPO_TEXTO,26,"L", "_pri");
		$reporte->definirColumna("mes3",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("mes2",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("mes1",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("stk_actual",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("stk_minimo",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("stk_maximo",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("pedido",$reporte->TIPO_IMPORTE,10,"R", "_pri");*/

		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab, "_pri");
		$reporte->AddPage();
		$reporte->Ln();	

		for ($i = 0; $i<count($resultados); $i++) {

		$arrData = PedidoComprasModel::obtenerCostoUltimaCompra($cabecera['almacen'], $cabecera['fecha'], $resultados[$i]['codigo']);
		$ultmcosto = $arrData["result"];
		$costototal = $resultados[$i]['cantidad'] * $ultmcosto;

		//error_log($ultmcosto);

		/* MODIFICACION DE COPETROL QUE NO QUERIA ESTOS CAMPOS
			$arr = array("codigo"=>$resultados[$i]['codigo'], "descripcion"=>$resultados[$i]['descripcion'], "mes3"=>$resultados[$i]['mes1'], "mes2"=>$resultados[$i]['mes2'], "mes1"=>$resultados[$i]['mes3'],
					"stk_actual"=>$resultados[$i]['actual'],"stk_minimo"=>$resultados[$i]['minimo'],"stk_maximo"=>$resultados[$i]['maximo'],"pedido"=>$resultados[$i]['cantidad']);
			$reporte->nuevaFila($arr, "_pri"); 	
		*/
			$arr = array("codigo"=>$resultados[$i]['codigo'], "descripcion"=>$resultados[$i]['descripcion'], "pedido"=>$resultados[$i]['cantidad'], "costouni"=>$ultmcosto, "costototal"=>$costototal);
			$reporte->nuevaFila($arr, "_pri"); 	

			$totalneto = $totalneto + $costototal;
		}

		$reporte->Ln();	
		$reporte->Ln();
		$text = "SUBTOTAL: ";
		$arr3 = array("texto"=>$text,"totalizado"=>$totalneto);
		$reporte->nuevaFila($arr3, "_pro"); 

		$igv = ($totalneto*1.18) - $totalneto;
		$text = "IGV: ";
		$arr3 = array("texto"=>$text,"totalizado"=>$igv);
		$reporte->nuevaFila($arr3, "_pro"); 

		$total = ($totalneto*1.18);
		$text = "TOTAL: ";
		$arr3 = array("texto"=>$text,"totalizado"=>$total);
		$reporte->nuevaFila($arr3, "_pro"); 

		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();
		$reporte->Lnew();
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/PedidoDeCompra.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/PedidoDeCompra.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}
	
	function gridViewEXCEL($arrResponsePedidoCompra) {
		$buff='';
		$sIdItem='';
		ob_end_clean();
		foreach ($arrResponsePedidoCompra['detalle'] as $row) {
			$arrData = PedidoComprasModel::obtenerCostoUltimaCompra($arrResponsePedidoCompra['cabecera']['almacen'], $arrResponsePedidoCompra['cabecera']['fecha'], $row['codigo']);
			$ultmcosto = $arrData["result"];
			$costototal = $row['cantidad'] * $ultmcosto;
			if ( $sIdItem != $row['codigo'] ){
				$buff .= "{$row['codigo']},{$row['descripcion']},{$row['cantidad']},{$ultmcosto},{$costototal}";
				$sIdItem = $row['codigo'];
				$buff .= "\n";
			}
		}
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"PedidoMercaderia.csv\"");
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        die($buff);
	}
}