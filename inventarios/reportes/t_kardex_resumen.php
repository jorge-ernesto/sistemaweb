<?php

class KardexActTemplate extends Template {

	function Titulo() {
        	return '<h2 align="center" style="color: #336699"><b>Resumen valorizado</b></h2>';
	}

	function formSearch() {

		$estaciones = KardexActModel::obtenerEstaciones();
		$ano = date("Y");
		$mes = date("m");
		$acciones = array("Normal" => "Normal", "Agrupado" => "Agrupado", "Detallado" => "Detallado");

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.KARDEXRESUMEN"));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("action", "Buscar"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "TODAS", $estaciones, ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Año</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("ano", "", $ano, '', 4, 5));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mes: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes", "", $mes, '', 2, 4));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Acción</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("accion", "", "Normal", $acciones, ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.gif" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();

	}

    	function listado($accion, $listado_productos, $stock_inicial, $ingreso_inventario, $ventas_inventario, $saldos_final, $linea, $mermas) {

        	$result = '';

		foreach ($listado_productos as $key_alm => $value_linea) {

		    	$result .= '<table border="0" width="1500px">';
		    	$result .= '<tr>';
		    	$result .= '<td colspan=2 bgcolor="#336699"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>ALMACÉN</font></center></td>';
		    	$result .= '<td  colspan=1 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_alm . '</font></center></td>';
		    	$result .= '</tr>';

            		foreach ($value_linea as $key_linea => $value_articulo) {

				$total_stock_inicial = 0.0;
				$total_stock_costototal = 0.0;

				$result .= '<tr>';
				$result .= '<td colspan=2 bgcolor="#66A3E0"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>LINEA</font></center></td>';
				$result .= '<td  bgcolor="#66A3E0" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_linea . ':' . $linea[$key_linea]['tab_descripcion'] . '</font></center></td>';
				$result .= '</tr>';

				$result .= '<tr>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"></td>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALDO INICIAL</font></td>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>ENTRADO</font></td>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALIDA</font></td>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALDO FINAL</font></td>';
				$result .= '<td colspan=1 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>-</font></td>';
				$result .= '<td colspan=1 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>-</font></td>';
				$result .= '</tr>';

				$result .= '<tr>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CODIGO EXISTENCIA</font></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>UNID MEDIDA</font></center></td>';
				$result .= '<td align="center" bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>DESCRIPCIÓN</strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD</strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO</strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL</strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>MERMA </strong></td>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>STOCK CONTABLE </strong></td>';
				$result .= '</tr>';

				$total_stock_entrada		= 0.00;
				$total_stock_entradacostototal	= 0.00;
				$total_stock_salida_venta	= 0.00;
				$total_stock_salidacostototal	= 0.00;
				$total_stock_final		= 0.00;
				$total_stock_finalcostototal	= 0.00;
				$total_merma			= 0.00;
				$total_al_costo			= 0.00;
				$total_ala_venta		= 0.00;
				$stock_contable_total		= 0;

				foreach ($value_articulo as $key_articulo => $value) {

					if ($accion == "Normal") {

						$costo_unitario = $saldos_final['st_final'][$key_alm][$value['cod']]['stk_costounitario'];
						$total_al_costo += $costo_unitario * $mermas[$key_alm][$value['cod']]['mov_cantidad'];
						$total_ala_venta += (($costo_unitario * 0.18) + $costo_unitario) * $mermas[$key_alm][$value['cod']]['mov_cantidad'];
						$stock_contable = $costo_unitario * ($saldos_final['st_final'][$key_alm][$value['cod']]['stk_stock'] - $mermas[$key_alm][$value['cod']]['mov_cantidad']);

						$result .= '<tr>';
						$result .= '<td bgcolor="#FFFFFF"  align="right"><b>' . $value['cod'] . '</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><b>' . $value['unidades'] . '</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><b>' . $value['desc'] . '</strong></td>';

						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_stock'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costounitario'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costototal'], 2, '.', ',') . '</strong></td>';

						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ingreso_inventario[$key_alm][$value['cod']]['mov_cantidad'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ingreso_inventario[$key_alm][$value['cod']]['mov_costounitario'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($ingreso_inventario[$key_alm][$value['cod']]['mov_costototal'], 2, '.', ',') . '</strong></td>';

						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_cantidad'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_costounitario'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_costototal'], 2, '.', ',') . '</strong></td>';

						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_stock'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_costounitario'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_costototal'], 2, '.', ',') . '</strong></td>';

						$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($mermas[$key_alm][$value['cod']]['mov_cantidad'], 2, '.', ',') . '</strong></td>';
						$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($stock_contable, 2, '.', ',') . '</strong></td>';

						$result .= '</tr>';

					} else {
						$costo_unitario = $saldos_final['st_final'][$key_alm][$value['cod']]['stk_costounitario'];
						$total_al_costo += $costo_unitario * $mermas[$key_alm][$value['cod']]['mov_cantidad'];
						$total_ala_venta += (($costo_unitario * 0.18) + $costo_unitario) * $mermas[$key_alm][$value['cod']]['mov_cantidad'];
					}

					$total_stock_inicial+=(double) $stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_stock'];
					$total_stock_costototal+=(double) $stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costototal'];

					$total_stock_entrada+=(double) $ingreso_inventario[$key_alm][$value['cod']]['mov_cantidad'];
					$total_stock_entradacostototal+=(double) $ingreso_inventario[$key_alm][$value['cod']]['mov_costototal'];

					$total_stock_salida_venta+=(double) $ventas_inventario[$key_alm][$value['cod']]['mov_cantidad'];
					$total_stock_salidacostototal+=(double) $ventas_inventario[$key_alm][$value['cod']]['mov_costototal'];

					$total_stock_final+=(double) $saldos_final['st_final'][$key_alm][$value['cod']]['stk_stock'];
					$total_stock_finalcostototal+=(double) $saldos_final['st_final'][$key_alm][$value['cod']]['stk_costototal'];

					$total_merma+=(double) $mermas[$key_alm][$value['cod']]['mov_cantidad'];
					$stock_contable_total += $costo_unitario * ($saldos_final['st_final'][$key_alm][$value['cod']]['stk_stock'] - $mermas[$key_alm][$value['cod']]['mov_cantidad']);

				}

				$result .= '<tr>';
				$result .= '<td colspan=2 bgcolor="#336699"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>TOTALES DE LA LINEA</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_linea . ':' . $linea[$key_linea]['tab_descripcion'] . '</font></center></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_inicial, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_costototal, 2, '.', ',') . '</font></center></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_entrada, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_entradacostototal, 2, '.', ',') . '</font></center></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_salida_venta, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_salidacostototal, 2, '.', ',') . '</font></center></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_final, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_finalcostototal, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_merma, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($stock_contable_total, 2, '.', ',') . '</font></center></td>';
				$result .= '</tr>';

				$result .= '<tr>';
				$result .= '<td colspan=12 bgcolor="#FFFFFF"  ></td>';
				$result .= '<td  bgcolor="#FFFFFF"  style="color:#3A3A3A;font-size:11px;"><center><font size=1><b>M AL P.COSTO</font></center></td>';
				$result .= '<td  bgcolor="#FFFFFF"  style="color:#3A3A3A;font-size:11px;"><center><font size=1><b>' . $total_al_costo . '</font></center></td>';
				$result .= '<td  bgcolor="#FFFFFF"  style="color:#3A3A3A;font-size:11px;"><center><font size=1><b>M AL P.VENTA</font></center></td>';
				$result .= '<td  bgcolor="#FFFFFF"  style="color:#3A3A3A;font-size:11px;"><center><font size=1><b>' . $total_ala_venta . '</font></center></td>';
				$result .= '</tr>';
			}
 		}

		$result .= '</table>';

		return $result;

	}

	function listado_detallado($listado_productos, $stock_inicial, $ingreso_inventario, $ventas_inventario, $saldos_final, $linea) {

        	$result = '';

        	foreach ($listado_productos as $key_alm => $value_linea) {

		    	$result .= '<table border="0" width="1500px">';
		    	$result .= '<tr>';
		    	$result .= '<td colspan=2 bgcolor="#336699"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>ALMACEN</font></center></td>';
		    	$result .= '<td  colspan=1 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_alm . '</font></center></td>';
		    	$result .= '</tr>';

            		foreach ($value_linea as $key_linea => $value_articulo) {

				$total_stock_inicial = 0.0;
				$total_stock_costototal = 0.0;

				$result .= '<tr>';
				$result .= '<td colspan=2 bgcolor="#66A3E0"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>LINEA</font></center></td>';
				$result .= '<td  bgcolor="#66A3E0" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_linea . ':' . $linea[$key_linea]['tab_descripcion'] . '</font></center></td>';
				$result .= '</tr>';

				$result .= '<tr>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"></td>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALDO INICIAL</font></td>';
				$result .= '<td colspan=4 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>ENTRADA</font></td>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALIDA</font></td>';
				$result .= '<td colspan=3 bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><font size=1><b>SALDO FINAL</font></td>';
				$result .= '</tr>';

				$result .= '<tr>';
				$result .= '<td bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CODIGO EXISTENCIA</font></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>UNID MEDIDA</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>DESCRIPCION</strong></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD</strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO</strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL</strong></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>TIPO MOV</strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>CANTIDAD </strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO UNITARIO </strong></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><font size=1><b>COSTO TOTAL </strong></td>';
				$result .= '</tr>';

				$total_stock_entrada		= 0.00;
				$total_stock_entradacostototal	= 0.00;
				$total_stock_salida_venta	= 0.00;
				$total_stock_salidacostototal	= 0.00;
				$total_stock_final		= 0.00;
				$total_stock_finalcostototal	= 0.00;

				foreach ($value_articulo as $key_articulo => $value) {

				    	$result .= '<tr>';
				    	$result .= '<td bgcolor="#FFFFFF"  align="right" ><b>' . $value['cod'] . '</strong></td>';
				    	$result .= '<td  bgcolor="#FFFFFF" align="right" ><b>' . $value['unidades'] . '</strong></td>';
				    	$result .= '<td  bgcolor="#FFFFFF" align="right" ><b>' . $value['desc'] . '</strong></td>';

				    	$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_stock'], 2, '.', ',') . '</strong></td>';
				    	$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costounitario'], 2, '.', ',') . '</strong></td>';
				    	$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costototal'], 2, '.', ',') . '</strong></td>';

				    	$total_stock_inicial+=(double) $stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_stock'];
				    	$total_stock_costototal+=(double) $stock_inicial['st_inicial'][$key_alm][$value['cod']]['stk_costototal'];

                    			$resultado_tab = '';

					if (!empty($ingreso_inventario[$key_alm][$value['cod']])) {

						$cantidad_tmp_ingreso		= 0.00;
						$costototal_tmp_ingreso		= 0.00;
						$precio_promedio_tmp_ingreso 	= 0.00;
						$iteracion_tmp_ingreso 		= 0;

						foreach ($ingreso_inventario[$key_alm][$value['cod']] as $key_articulo => $value_inve) {

						    	$resultado_tab .= '<tr>';
						    	$resultado_tab .= '<td bgcolor="#FFFFFF"  align="right" style="color:white"><font size=1><b></font></td>';
						    	$resultado_tab .= '<td  bgcolor="#FFFFFF" align="right" style="color:white"><center><font size=1><b></font></center></td>';
						    	$resultado_tab .= '<td  bgcolor="#FFFFFF" align="right" style="color:white"><font size=1><b></strong></td>';
						    	$resultado_tab .= '<td bgcolor="#FFFFFF"  colspan=2><font size=1><b></font></td>';
						    	$resultado_tab .= '<td bgcolor="#C4C4C4"  ><font size=1><b></font></td>';

						    	$resultado_tab .= '<td   bgcolor="#FFFFFF" ><font size=1><b>' . $value_inve['tran_descripcion'] . '</strong></td>';
						    	$resultado_tab .= '<td   bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($value_inve['mov_cantidad'], 2) . '</strong></td>';
						    	$resultado_tab .= '<td   bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($value_inve['mov_costounitario'], 2) . '</strong></td>';
						    	$resultado_tab .= '<td   bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($value_inve['mov_costototal'], 2) . '</strong></td>';

						    	$resultado_tab .= '<td bgcolor="#FFFFFF"  colspan=2><font size=1><b></font></td>';
						    	$resultado_tab .= '<td bgcolor="#C4C4C4"  ><font size=1><b></font></td>';
						    	$resultado_tab .= '<td bgcolor="#FFFFFF"  colspan=2><font size=1><b></font></td>';
						    	$resultado_tab .= '<td bgcolor="#C4C4C4"  ><font size=1><b></font></td>';
						    	$resultado_tab .= '</tr>';

						    	$cantidad_tmp_ingreso +=doubleval($value_inve['mov_cantidad']);
						    	$costototal_tmp_ingreso += doubleval($value_inve['mov_costototal']);
						    	$precio_promedio_tmp_ingreso += doubleval($value_inve['mov_costounitario']);

						    	$iteracion_tmp_ingreso++;

						}

				        	$resultado_tab .= '<tr><td colspan=16   bgcolor="#336699">&nbsp;</td></tr>';
				        	$result .= '<td  bgcolor="#FFFFFF" ><font size=1><b>TOTAL MOV</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($cantidad_tmp_ingreso, 2) . '</strong></td>';
				        	$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format(($precio_promedio_tmp_ingreso / $iteracion_tmp_ingreso), 2) . '</strong></td>';
				        	$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($costototal_tmp_ingreso, 2) . '</strong></td>';

                    			} else {

						$result .= '<td  bgcolor="#FFFFFF" ><font size=1><b>SIN MOV</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>0.00</strong></td>';
						$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>0.00</strong></td>';
						$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>0.00</strong></td>';

					}

				    	$total_stock_entrada+=$cantidad_tmp_ingreso;
				    	$total_stock_entradacostototal+=$costototal_tmp_ingreso;

				    	$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_cantidad'], 2, '.', ',') . '</strong></td>';
				    	$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_costounitario'], 2, '.', ',') . '</strong></td>';
				    	$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($ventas_inventario[$key_alm][$value['cod']]['mov_costototal'], 2, '.', ',') . '</strong></td>';

				    	$total_stock_salida_venta+=(double) $ventas_inventario[$key_alm][$value['cod']]['mov_cantidad'];
				    	$total_stock_salidacostototal+=(double) $ventas_inventario[$key_alm][$value['cod']]['mov_costototal'];

				    	$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_stock'], 2, '.', ',') . '</strong></td>';
				    	$result .= '<td  bgcolor="#FFFFFF" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_costounitario'], 2, '.', ',') . '</strong></td>';
				    	$result .= '<td  bgcolor="#C4C4C4" align="right"><font size=1><b>' . number_format($saldos_final['st_final'][$key_alm][$value['cod']]['stk_costototal'], 2, '.', ',') . '</strong></td>';

				    	$total_stock_final+=(double) $saldos_final['st_final'][$key_alm][$value['cod']]['stk_stock'];
				    	$total_stock_finalcostototal+=(double) $saldos_final['st_final'][$key_alm][$value['cod']]['stk_costototal'];

				    	$result .= '</tr>';

				    	$result .= $resultado_tab;

				}

				$result .= '<tr>';
				$result .= '<td colspan=2 bgcolor="#336699"  style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>TOTALES DE LA LINEA</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;"><center><font size=1><b>' . $key_linea . ':' . $linea[$key_linea]['tab_descripcion'] . '</font></center></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_inicial, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_costototal, 2, '.', ',') . '</font></center></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>-</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_entrada, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_entradacostototal, 2, '.', ',') . '</font></center></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_salida_venta, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_salidacostototal, 2, '.', ',') . '</font></center></td>';

				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_final, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format(0, 2, '.', ',') . '</font></center></td>';
				$result .= '<td  bgcolor="#336699" style="color:#FFFFFF;font-size:11px;" align="center"><center><font size=1><b>' . number_format($total_stock_finalcostototal, 2, '.', ',') . '</font></center></td>';
				$result .= '</tr>';
			}

		}

        	$result .= '</table>';

        	return $result;

	}

}
