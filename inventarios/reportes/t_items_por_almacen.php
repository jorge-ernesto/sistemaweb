<?php

class TemplateItemsPorAlmacen extends Template {

	function Form($estaciones, $lineas, $hoy) { ?>

		<div align="center" id="AjusteUbicacionCabecera">

			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Items por Almacen<b></h2>

			<table cellspacing="0" cellpadding="3" border="0">

				<tr>
					<td align="right">Almacen: </td>
					<td>
						<select id="nualmacen">
							<option value="T">Todos</option>
							<?php
							foreach($estaciones as $value){
								echo "<option value='" . $value['nualmacen'] . "'>". $value['noalmacen'] . "</option>";
							}
							?>
						</select>
					</td>
				</tr>

				<tr>
					<td align="right">Linea: </td>
					<td>
						<select id="combolinea">
							<option value="T">Todos</option>
							<?php
							foreach($lineas as $value){
								echo "<option value='" . $value['nucodlinea'] . "'>". $value['nolinea'] . "</option>";
							}
							?>
						</select>
					</td>
				</tr>

				<tr>
					<td align="right">Año: </td>
					<td>
						<select id="comboyear" name="year">
							<?php echo $this->renderOptionsYears(); ?>
						</select>
						Mes: 
						<select id="combomonth" name="month">
							<option value="01">Enero</option>
							<option value="02">Febrero</option>
							<option value="03">Marzo</option>
							<option value="04">Abril</option>
							<option value="05">Mayo</option>
							<option value="06">Junio</option>
							<option value="07">Julio</option>
							<option value="08">Agosto</option>
							<option value="09">Septiembre</option>
							<option value="10">Octubre</option>
							<option value="11">Noviembre</option>
							<option value="12">Diciembre</option>
						</select>
					</td>
				</tr>
				
				<!--<tr>
                    <td align="right">Fecha: </td>
                    <td><input type='text' id='fecha_inicio' class='fecha_formato' size="12" maxlength="10" value="<?php echo $hoy; ?>" />
                <tr>-->

				<tr>
					<td align="right">Solo con Stock: </td>
					<td>
						<input type="checkbox" name="p_stock" value="P" checked>Positivo
						<input type="checkbox" name="c_stock" value="C" checked>Cero
						<input type="checkbox" name="n_stock" value="N" checked>Negativo
					</td>
				</tr>

				<tr>
					<td align="right">Datos de utilidad: </td>
					<td>
						<input type="radio" name="utilidad" value="S" checked >Si
						<input type="radio" name="utilidad" value="N" >No
					</td>
				</tr>

				<tr style="display: none">
					<td align="right">Modo simplificado: </td>
					<td>
						<input type="radio" name="simple" value="N" checked >No
						<input type="radio" name="simple" value="S" >Si
					</td>
				</tr>

				<tr>
					<td colspan="2" align="center">
						<button id="btnbuscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar&nbsp;</button>
						<button id="btnexcel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel&nbsp;</button>
						<!--<button id="btnprint"> Imprimir&nbsp;</button>-->
					</td>
				</tr>

			</table>
		</div>

		<div  align="center" id="ListaStockLinea"></div>

	<?php
	}

	function ListaStockLinea($data,$request) { ?>

		<div style="width: auto;border: 1px;">

			<br/>

			<table border="0">

				<?php
				if ($request['simple'] == 'S') {
				?>
				<tr>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>CODIGO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>PRODUCTO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>UNIDAD MEDIDA</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>CANTIDAD</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>COSTO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>IGV COSTO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>PRECIO COMPRA</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>PRECIO VENTA</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>VAL. MARGEN</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>MARGEN</th>
				</tr>
				<?php } else { ?>
				<tr>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>CODIGO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>PRODUCTO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>UNIDAD MEDIDA</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>CANTIDAD TOTAL</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>COSTO UNITARIO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>VALOR TOTAL</th>
					<?php if($request['utilidad'] == 'S') { ?>
						<th class="grid_cabecera" style="font-size:0.9em;"><b>PRECIO VENTA</th>
						<th class="grid_cabecera" style="font-size:0.9em;"><b>IGV</th>
						<th class="grid_cabecera" style="font-size:0.9em;"><b>TOTAL</th>
						<th class="grid_cabecera" style="font-size:0.9em;"><b>MARGEN %</th>
						<th class="grid_cabecera" style="font-size:0.9em;"><b>VAL. MARGEN</th>
					<?php 
					}
				}
				?>
				</tr>

				<?php

				$i 			= 0;
				$result			= null;
				$noalmacen 		= null;
				$art_linea 		= null;
				$sumcantnolinea		= 0;
				$sumcostnolinea		= 0;
				$sumtotnolinea		= 0;
				$sumcantnolineag	= 0;
				$sumcostnolineag	= 0;
				$sumtotnolineag		= 0;

				$margen = 0;
				$val_margen = 0;
				$igv = 0;
				$total = 0;

				$val_margeng = 0;
				$igvg = 0;
				$totalg = 0;

				$cs = $request['utilidad'] == 'S' ? 11 : 6;

				foreach ($data as $row) {
					$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

					if ($art_linea != $row['art_linea']) {

						if ($i != 0) {

							if ($request['simple'] == 'S') {
								$result .= '<tr>';
								$result .= '<td colspan="3" class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL LINEA: </td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantidad, 2, '.', ',') . '</b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcosto, 2, '.', ',') . '</b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumigvcosto, 2, '.', ',') . '</b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumprecio_compra, 2, '.', ',') . '</b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumprecio_venta, 2, '.', ',') . '</b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($summargensmp, 2, '.', ',') . '</b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"></td>';
								$result .= '</tr>';
							} else {
								$result .= '<tr>';
								$result .= '<td colspan="3" class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL LINEA: </b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantnolinea, 2, '.', ',') . '</b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcostnolinea, 2, '.', ',') . '</b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumtotnolinea, 2, '.', ',') . '</b></td>';
								if ($request['utilidad'] == 'S') {
									$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"></td>';
									$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($igv, 2, '.', ',') . '</b></td>';
									$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($total, 2, '.', ',') . '</b></td>';
									$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"></td>';
									$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($val_margen, 2, '.', ',') . '</b></td>';
								}
								$result .= '</tr>';
							}

							$sumcantidad 		= 0;
							$sumcosto 			= 0;
							$sumigvcosto 		= 0;
							$sumprecio_compra	= 0;
							$sumprecio_venta	= 0;
							$summargensmp 		= 0;

							$sumcantnolinea 	= 0;
							$sumcostnolinea 	= 0;
							$sumtotnolinea 		= 0;

							$margen = 0;
							$val_margen = 0;
							$igv = 0;
							$total = 0;
						}

						if ($noalmacen != $row['ch_nombre_almacen']) {
							$result .= '<tr>';
							$result .= '<td colspan="'.$cs.'" class="grid_detalle_especial" align ="center" style="font-size:0.9em; color:black;"><b>ALMACEN: ' . htmlentities($row['ch_nombre_almacen']) . '</b></td>';
							$result .= '</tr>';
							$noalmacen 	= $row['ch_nombre_almacen'];
						}

						$result .= '<tr>';
						$result .= '<td colspan="'.$cs.'" class="grid_detalle_especial" align ="left" style="font-size:0.9em; color:black;"><b>** LINEA: ' . htmlentities($row['art_linea']) . ' - ' . htmlentities($row['desclinea']) . '</b></td>';
						$result .= '</tr>';
						$art_linea 	= $row['art_linea'];
					}

					if ($request['simple'] == 'S') {
						$margensmp = $row['precio_venta'] - $row['precio_compra'];

						$result .= '<tr bgcolor="">';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['art_codigo']) . '</td>';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['descripcion']) . '</td>';
						$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['unidad']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['cantidad']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['costo']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['igvcosto']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['precio_compra']) . '</td>';
						if ($row['precio_compra'] == 0.0000) {
							$result .= '<td class="'.$color.'" align ="right">0.00</td>';
							$result .= '<td class="'.$color.'" align ="right">0.00</td>';
						} else {
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['precio_venta']) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($margensmp) . '</td>';
						}
						$result .= '<td class="'.$color.'" align ="right">' . number_format((($margensmp * 100) / $row['precio_compra']), 0, '.', ',') . ' %</td>';
						$result .= '</tr>';
					} else {
						$result .= '<tr bgcolor="">';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['art_codigo']) . '</td>';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['descripcion']) . '</td>';
						$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['unidad']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['nucantidad']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['nucosto']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['subtot']) . '</td>';
						if ($request['utilidad'] === 'S') {
							$fFormulaMargen = (round(($row['precio_venta'] / $row['ss_impuesto']), 2) - $row['nucosto']);
							settype($fFormulaMargen, "double");
							settype($row['nucosto'], "double");
							$fPorcentajeMargen = (($fFormulaMargen * 100) / $row['nucosto']);
							$fImpoteMargen = ($fFormulaMargen * $row['nucantidad']);

							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['precio_venta']) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['igv']) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['total']) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities(round($fPorcentajeMargen, 0)) . ' %</td>';
							$result .= '<td class="'.$color.'" align ="right">' . round($fImpoteMargen, 2) . '</td>';
						}
						$result .= '</tr>';
					}

					$sumcantidad 		= $sumcantidad + $row['cantidad'];
					$sumcosto 			= $sumcosto + $row['costo'];
					$sumigvcosto 		= $igvcosto + $row['igvcosto'];
					$sumprecio_compra	= $precio_compra + $row['precio_compra'];
					$sumprecio_venta	= $precio_venta + $row['precio_venta'];
					$summargensmp 		= $summargensmp + $fFormulaMargen;

					$sumcantidadag 		= $sumcantidadag + $row['cantidad'];
					$sumcostoag 		= $sumcostoag + $row['costo'];
					$sumigvcostoag 		= $igvcostoag + $row['igvcosto'];
					$sumprecio_compraag	= $precio_compraag + $row['precio_compra'];
					$sumprecio_ventaag	= $precio_ventaag + $row['precio_venta'];
					$summargensmpag 	= $summargensmpag + $fFormulaMargen;

					$sumcantnolinea 	= $sumcantnolinea + $row['nucantidad'];
					$sumcostnolinea 	= $sumcostnolinea + $row['nucosto'];
					$sumtotnolinea 		= $sumtotnolinea + $row['subtot'];

					$sumcantnolineag 	= $sumcantnolineag + $row['nucantidad'];
					$sumcostnolineag 	= $sumcostnolineag + $row['nucosto'];
					$sumtotnolineag		= $sumtotnolineag + $row['subtot'];

					$margen = $margen + $fPorcentajeMargen;
					$val_margen = $val_margen + $fImpoteMargen;
					$igv = $igv + $row['igv'];
					$total = $total + $row['total'];

					$val_margeng = $val_margeng + $fImpoteMargen;
					$igvg = $igvg + $row['igv'];
					$totalg = $totalg + $row['total'];

					$i++;
				}

				if ($request['simple'] == 'S') {
					$result .= '<tr>';
					$result .= '<td colspan="3" class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL LINEA: </td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantidad, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcosto, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumigvcosto, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumprecio_compra, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumprecio_venta, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($summargensmp, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"></td>';
					$result .= '</tr>';
				} else {
					$result .= '<tr>';
					$result .= '<td colspan="3" class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL LINEA: </td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantnolinea, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcostnolinea, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumtotnolinea, 2, '.', ',') . '</b></td>';
					if($request['utilidad'] === 'S') {
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"></td>';
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($igv, 2, '.', ',') . '</b></td>';
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($total, 2, '.', ',') . '</b></td>';
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"></td>';
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($val_margen, 2, '.', ',') . '</b></td>';
					}
					$result .= '</tr>';
				}

				//TOTAL GLOBAL DE UBICACIONES

				if($request['simple'] == 'S') {
					$result .= '<tr>';
					$result .= '<td colspan="3" class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL GENERAL: </td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantidadag, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcostoag, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumigvcostoag, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumprecio_compraag, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumprecio_ventaag, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($summargensmpag, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"></td>';
					$result .= '</tr>';
				} else {
					$result .= '<tr>';
					$result .= '<td colspan="3" class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL GENERAL: </b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantnolineag, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcostnolineag, 2, '.', ',') . '</b></td>';
					$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumtotnolineag, 2, '.', ',') . '</b></td>';
					if($request['utilidad'] == 'S') {
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"></td>';
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($igvg, 2, '.', ',') . '</b></td>';
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($totalg, 2, '.', ',') . '</b></td>';
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"></td>';
						$result .= '<td class="' . $color . '" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($val_margeng, 2, '.', ',') . '</b></td>';
					}
					$result .= '</tr>';
				}
				echo $result;
				?>

			</table>
		</div>

	<?php
	}

	function renderOptionsYears() {
		$return = '';
		$year = date('Y');
		$yearMin = strtotime ( '-5 year', strtotime ( $year ) ) ;
		$yearMin = date ( 'Y', $yearMin );

		for ($i = $year; $i >= $yearMin; $i--) {
			$return .= '<option value="'.$i.'">'.$i.'</option>';
		}
		return $return;
	}

	function monthNow() {
		$mnow = date('m');//mes actual
		if(strlen($mnow) > 2) {
			$mnow = '0'.$mnow;
		}
		return $mnow;
	}
}