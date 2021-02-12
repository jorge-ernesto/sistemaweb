<?php

class TemplateReporteAjusteUbicacion extends Template {

	function Inicio($estaciones, $ubicaciones, $hoy) { ?>

		<div align="center" id="AjusteUbicacionCabecera">

			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Reporte Ajuste Inventario de Ubicaciones<b></h2>

				<table cellspacing="0" cellpadding="2" border="0">

				    	<tr>
						<td align="right">Almacen: </td>
					    	<td>
						    <select id="nualmacen">
							    <option value="T">Seleccionar...</option>
							    <?php
								foreach($estaciones as $value){
									echo "<option value='" . $value['nualmacen'] . "'>". $value['noalmacen'] . "</option>";
								}
							    ?>
						    </select>
						</td>
					</tr>

				    	<tr>
						<td align="right">Ubicacion: </td>
					    	<td>
						    <select id="nuubicacion">
						    </select>
						</td>
					</tr>

				    	<tr>
						<td align="right">Fecha: </td>
					    	<td>
						    <input type="text" id="fbuscar" name="fbuscar" size="12" maxlength="10" value="<?php echo $hoy; ?>" />
						</td>
					</tr>

				    	<tr>
						<td align="right">Tipo: </td>
					    	<td>
						    <input type="radio" name="notipo" value="D" checked >Detallado
						    <input type="radio" name="notipo" value="R" >Resumido
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

	<div  align="center" id="AjusteUbicacionDetalle">

	</div>

	<?php }

        function ListaAjusteUbicacion($data, $notipo) { ?>

		<div style="width: auto;border: 1px;">

			<br/>

                	<table border="0">

				<tr>
					<th class="grid_cabecera">FECHA</th>
					<th class="grid_cabecera"># FORMULARIO</th>
					<th class="grid_cabecera">PRODUCTO</th>
					<th class="grid_cabecera">CANTIDAD</th>
					<th class="grid_cabecera">COSTO UNITARIO S/IGV</th>
					<th class="grid_cabecera">TOTAL</th>

				</tr>

				<?php

				$i 			= 0;
				$result			= null;
				$noubicacion 		= null;
				$sumcantubicacion	= 0;
				$sumcostubicacion	= 0;
				$sumtotubicacion	= 0;

		                foreach ($data as $row) {

					$color = ($i % 2 == 0 ? "grid_detalle_par" : "grid_detalle_par");

					if($noubicacion != $row['noubicacion']){

						if($i != 0){

							$result .= '<tr>';
							$result .= '<td colspan="3" class="grid_detalle_impar" align ="right">TOTAL UBICACION: </td>';
							$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumcantubicacion, 2, '.', ',') . '</td>';
							$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumcostubicacion, 2, '.', ',') . '</td>';
							$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumtotubicacion, 2, '.', ',') . '</td>';
							$result .= '</tr>';

							$sumcantubicacion 	= 0;
							$sumcostubicacion 	= 0;
							$sumtotubicacion 	= 0;

						}

						$result .= '<tr>';
						$result .= '<td colspan="6" class="grid_detalle_impar" align ="left">UBICACION: ' . htmlentities($row['noubicacion']) . '</td>';
						$result .= '</tr>';

						$noubicacion 	= $row['noubicacion'];
						
					}

					if($notipo == 'D'){//Detallado

						$result .= '<tr bgcolor="">';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['femision']) . '</td>';
						$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nuformulario']) . '</td>';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['noproducto']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['nucantidad']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['nucosto']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['nutotal']) . '</td>';
						$result .= '</tr>';

					}

					$sumcantubicacion 	= $sumcantubicacion + $row['nucantidad'];
					$sumcostubicacion 	= $sumcostubicacion + $row['nucosto'];
					$sumtotubicacion 	= $sumtotubicacion + $row['nutotal'];

					$sumcantubicaciong 	= $sumcantubicaciong + $row['nucantidad'];
					$sumcostubicaciong 	= $sumcostubicaciong + $row['nucosto'];
					$sumtotubicaciong 	= $sumtotubicaciong + $row['nutotal'];

		                        $i++;

		                }

				$result .= '<tr>';
				$result .= '<td colspan="3" class="grid_detalle_impar" align ="right">TOTAL UBICACION: </td>';
				$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumcantubicacion, 2, '.', ',') . '</td>';
				$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumcostubicacion, 2, '.', ',') . '</td>';
				$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumtotubicacion, 2, '.', ',') . '</td>';
				$result .= '</tr>';

				//TOTAL GLOBAL DE UBICACIONES

				$result .= '<tr>';
				$result .= '<td colspan="3" class="grid_detalle_impar" align ="right">TOTAL GENERAL: </td>';
				$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumcantubicaciong, 2, '.', ',') . '</td>';
				$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumcostubicaciong, 2, '.', ',') . '</td>';
				$result .= '<td class="grid_detalle_impar" align ="right">' . number_format($sumtotubicaciong, 2, '.', ',') . '</td>';
				$result .= '</tr>';

				echo $result;

				?>

			</table>
		</div>

	<?php }

}


