<?php

class TemplateStockGeneralLinea extends Template {

	function Inicio($estaciones, $lineas, $hoy) { ?>
		<div align="center" id="AjusteUbicacionCabecera">
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Stock General por Linea<b></h2>
				<table cellspacing="0" cellpadding="3" border="0">
				    <tr>
						<td align="right">Almacén: </td>
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
							<select id="comboyear" name="year"></select>
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
				    	<tr>
						<td align="right">Solo con Stock: </td>
					    	<td>
						    <input type="radio" name="notipo" value="S" checked >Si
						    <input type="radio" name="notipo" value="N" >No
						</td>
					</tr>


				    	<tr>
		                                <td colspan="2" align="center">
							<button id="btnbuscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
			                                <button id="btnexcel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>
						</td>
					</tr>

				</table>
		</div>

	<div  align="center" id="ListaStockLinea">

	</div>

	<?php }

        function ListaStockLinea($data, $notipo) { ?>

		<div style="width: auto;border: 1px;">

			<br/>

                	<table border="0">

				<tr>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>PRODUCTO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>UNIDAD MEDIDA</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>CANTIDAD</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>COSTO UNITARIO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>TOTAL</th>

				</tr>

				<?php

				$i 					= 0;
				$result				= null;
				$noalmacen 			= null;
				$nolinea 			= null;
				$sumcantnolinea		= 0;
				$sumcostnolinea		= 0;
				$sumtotnolinea		= 0;
				$sumcantnolineag	= 0;
				$sumcostnolineag	= 0;
				$sumtotnolineag		= 0;

		        foreach ($data as $row) {

					$color = ($i % 2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");
					

						if($nolinea != $row['nolinea']){
					
							if($i != 0){

								$result .= '<tr>';
								$result .= '<td colspan="2" class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL LINEA: </b></td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantnolinea, 2, '.', ',') . '</td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcostnolinea, 2, '.', ',') . '</td>';
								$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumtotnolinea, 2, '.', ',') . '</td>';
								$result .= '</tr>';

								$sumcantnolinea 	= 0;
								$sumcostnolinea 	= 0;
								$sumtotnolinea 		= 0;

							}

							if($noalmacen != $row['noalmacen']){

								$result .= '<tr>';
								$result .= '<td colspan="5" class="grid_detalle_especial" align ="center" style="font-size:0.9em; color:black;"><b>ALMACEN: ' . htmlentities($row['noalmacen']) . '</td>';
								$result .= '</tr>';

								$noalmacen 	= $row['noalmacen'];

							}

							$result .= '<tr>';
							$result .= '<td colspan="5" class="grid_detalle_especial" align ="left" style="font-size:0.9em; color:black;"><b>** LINEA: ' . htmlentities($row['nolinea']) . '</td>';
							$result .= '</tr>';

							$nolinea 	= $row['nolinea'];
						
						}

						$result .= '<tr bgcolor="">';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['noproducto']) . '</td>';
						$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nucodunidad']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['nucantidad']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['nucosto']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['nutotal']) . '</td>';
						$result .= '</tr>';

						$sumcantnolinea 	= $sumcantnolinea + $row['nucantidad'];
						$sumcostnolinea 	= $sumcostnolinea + $row['nucosto'];
						$sumtotnolinea 		= $sumtotnolinea + $row['nutotal'];

						$sumcantnolineag 	= $sumcantnolineag + $row['nucantidad'];
						$sumcostnolineag 	= $sumcostnolineag + $row['nucosto'];
						$sumtotnolineag		= $sumtotnolineag + $row['nutotal'];

				                $i++;

		        }

				$result .= '<tr>';
				$result .= '<td colspan="2" class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL LINEA: </td>';
				$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantnolinea, 2, '.', ',') . '</td>';
				$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcostnolinea, 2, '.', ',') . '</td>';
				$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumtotnolinea, 2, '.', ',') . '</td>';
				$result .= '</tr>';

				//TOTAL GLOBAL DE UBICACIONES

				$result .= '<tr>';
				$result .= '<td colspan="2" class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL GENERAL: </td>';
				$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcantnolineag, 2, '.', ',') . '</td>';
				$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumcostnolineag, 2, '.', ',') . '</td>';
				$result .= '<td class="grid_detalle_total" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumtotnolineag, 2, '.', ',') . '</td>';
				$result .= '</tr>';

				echo $result;

				?>

			</table>
		</div>

	<?php }

}


