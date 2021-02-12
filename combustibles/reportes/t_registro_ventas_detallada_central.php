<?php

class TemplateRegistroVentasCental extends Template {

	function Inicio($status_connection_server_central, $organizaciones, $txtnofechaini, $txtnofechafin) {

	?>
		<div align="center" id="GriRegistroVentasCentral">
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Transacciones Diarias Centralizadas<b></h2>

				<table cellspacing="2" cellpadding="2" border="0">
				<?php
					if(!$status_connection_server_central){
				?>
				    <tr>
						<td colspan="2" align="center"><?php echo $organizaciones; ?></td>
				<?php
					}else{
				?>
				    <tr>
						<td align="right">Organizaciones: </td>
					    <td>
						    <select id="cboalmacen">
							    <option value="T">Todos</option>
							    <?php
								foreach($organizaciones as $value){
									echo "<option value='" . $value['nu_id_org'] . "'>". $value['no_organizacion'] . "</option>";
								}
							    ?>
						    </select>
						</td>
					</tr>

				    <tr>
						<td align="right">Fecha Inicial: </td>
					    <td>
						    <input type="text" id="txtnofechaini" name="txtnofechaini" step="1" size="12" maxlength="10" value="<?php echo $txtnofechaini; ?>" required />
						    <span class="MsgValidacion_Fecha_Inicial"></span>
						</td>
					</tr>
				    <tr>
						<td align="right">Fecha Final: </td>
					    <td>
						    <input type="text" id="txtnofechafin" name="txtnofechafin" step="1" size="12" maxlength="10" value="<?php echo $txtnofechafin; ?>" required />
						    <span class="MsgValidacion_Fecha_Final"></span>
						</td>
					</tr>

					<tr>
						<td align="right">Tipo Movimiento: </td>
						<td align="left">
							<select id="cbotv" name="cbotv">
								<option value="T">Todos</option>
								<option value="1">Tickets</option>
								<option value="0">Compras</option>
							</select>
						</td>
					</tr>

				    <tr>
						<td align="right">Tipo Vista: </td>
					    	<td>
						    <input type="radio" name="rdnotipo" value="D" checked >Detallado
						    <input type="radio" name="rdnotipo" value="RD" >Resumen Diario
						    <input type="radio" name="rdnotipo" value="RM" >Resumen Mensual
						    <input type="radio" name="rdnotipo" value="RA" >Resumen Anual
						</td>
					</tr>

				    <tr>
						<td colspan="2" align="center">
							<button id="btnbuscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
						</td>
					</tr>
				<?php
					}
				?>
				</table>

		</div>

	<div align="center" id="GriRegistroVentasCentralDetalle">

	</div>

	<?php }

        function RegistroVentasCentral($data) { ?>

			<div style="width: auto;border: 1px;">

				<br/>

					<table border="0">

						<tr>
							<th class="grid_cabecera">FECHA</th>
							<th class="grid_cabecera">TIPO MOVIMIENTO</th>
							<th class="grid_cabecera">TIPO</th>
							<th class="grid_cabecera">SERIE</th>
							<th class="grid_cabecera">NUMERO</th>
							<th class="grid_cabecera">RUC</th>
							<th class="grid_cabecera">RAZON SOCIAL</th>
							<th class="grid_cabecera">BASE IMPONIBLE</th>
							<th class="grid_cabecera">I.G.V</th>
							<th class="grid_cabecera">TOTAL</th>
						</tr>

						<?php

							$i 					= 0;
							$result				= null;
							$no_cliente 		= "";
							$nu_organizacion 	= null;
							$fe_emision 		= null;

							//VARIBLES PARA SUMA TOTAL
							$sumbi 		= 0.00;
							$sumigv 	= 0.00;
							$sumtotal 	= 0.00;

							//TOTAL
							$sumtotbi 		= 0.00;
							$sumtotigv 		= 0.00;
							$sumtottotal 	= 0.00;

							foreach ($data as $row) {

								$color = ($i % 2 == 0 ? "grid_detalle_impar" : "grid_detalle_par");

								if($nu_organizacion != $row['nu_organizacion']){

									if($i!=0){

										$result .= '<tr>';
											$result .= '<td colspan="7" class="grid_detalle_impar" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL: </b></td>';
											$result .= '<td class="grid_detalle_impar" align ="right" style="font-size:0.9em; color:black;"><b>' . TemplateRegistroVentasCental::NumberFormat($sumbi, 2, '.', ',') . '</td>';
											$result .= '<td class="grid_detalle_impar" align ="right" style="font-size:0.9em; color:black;"><b>' . TemplateRegistroVentasCental::NumberFormat($sumigv, 2, '.', ',') . '</td>';
											$result .= '<td class="grid_detalle_impar" align ="right" style="font-size:0.9em; color:black;"><b>' . TemplateRegistroVentasCental::NumberFormat($sumtotal, 2, '.', ',') . '</td>';
										$result .= '</tr>';

										$sumbi = 0.00;
										$sumigv = 0.00;
										$sumtotal = 0.00;

									}

									$result .= '<tr>';
										$result .= '<td colspan="10" class="grid_detalle_impar" align ="left" style="font-size:0.9em; color:black;"><b>ORGANIZACION: ' . htmlentities($row['no_orgarnizacion']) . '</td>';
									$result .= '</tr>';

									$nu_organizacion = $row['nu_organizacion'];

								}

								//if($notipo == 'D'){//Detallado

									$result .= '<tr>';
										$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['fe_emision']) . '</td>';
										$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['no_tipo_movimiento']) . '</td>';
										$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['no_tipo_documento']) . '</td>';
										$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['no_serie_documento']) . '</td>';
										$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nu_documento']) . '</td>';
										$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nu_ruc']) . '</td>';
										$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['no_cliente']) . '</td>';
										//COLUMNA TOTAL
										$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateRegistroVentasCental::NumberFormat($row['nu_bi'])) . '</td>';
										$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateRegistroVentasCental::NumberFormat($row['nu_igv'])) . '</td>';
										$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateRegistroVentasCental::NumberFormat($row['nu_total'])) . '</td>';
									$result .= '</tr>';

								//}

								//SUMA TOTALES
								$sumbi 		+= $row['nu_bi'];
								$sumigv 	+= $row['nu_igv'];
								$sumtotal 	+= $row['nu_total'];

/*
								if($fe_emision != $row['fe_emision']){
									$result .= '<tr>';
										$result .= '<td colspan="10" class="grid_detalle_impar" align ="left" style="font-size:0.9em; color:black;"><b>DIA: ' . htmlentities($row['fe_emision']) . '</td>';
									$result .= '</tr>';
									$sumbi = 0.00;
									$sumigv = 0.00;
									$sumtotal = 0.00;
								}
*/
								//TOTAL GENERAL
								$sumtotbi 		+= $row['nu_bi'];
								$sumtotigv 		+= $row['nu_igv'];
								$sumtottotal 	+= $row['nu_total'];

								$i++;

							}

							$result .= '<tr>';
								$result .= '<td colspan="7" class="grid_detalle_impar" align ="right" style="font-size:0.9em; color:black;"><b>TOTAL: </b></td>';
								$result .= '<td class="grid_detalle_impar" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumbi, 2, '.', ',') . '</td>';
								$result .= '<td class="grid_detalle_impar" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumigv, 2, '.', ',') . '</td>';
								$result .= '<td class="grid_detalle_impar" align ="right" style="font-size:0.9em; color:black;"><b>' . number_format($sumtotal, 2, '.', ',') . '</td>';
							$result .= '</tr>';

							$result .= '<tr>';
								$result .= '<th colspan="7" class="grid_total" align="right">TOTAL GENERAL: </th>';
								$result .= '<th class="grid_total" align="right">' . TemplateRegistroVentasCental::NumberFormat($sumtotbi) . '</th>';
								$result .= '<th class="grid_total" align="right">' . TemplateRegistroVentasCental::NumberFormat($sumtotigv) . '</th>';
								$result .= '<th class="grid_total" align="right">' . TemplateRegistroVentasCental::NumberFormat($sumtottotal) . '</th>';
							$result .= '</tr>';

							echo $result;

						?>

				</table>

			</div>

	<?php }

	function NumberFormat($number) {
		return number_format($number, 2, '.', ',');
	}

	function NumberFormatTransaction($number) {
		return number_format($number, 0, '.', ',');
	}


	function renderFromVentasCentralizadas($params) { ?>
		<div align="center" id="GriRegistroVentasCentral">
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Transacciones Diarias Centralizadas<b></h2>
				<table cellspacing="2" cellpadding="2" border="0">
				<?php
				if(!$params['status_connection_server_central']) {
				?>
					<tr>
						<td colspan="2" align="center"><?php echo $params['organizaciones']; ?></td>
					</tr><!--?-->
				<?php
				} else {
				?>
					<tr>
						<td align="right">Organizaciones: </td>
						<td>
							<select id="cboalmacen">
								<option value="T">Todos</option>
							    <?php
								foreach($params['organizaciones'] as $value) {
									echo "<option value='" . $value['nu_id_org'] . "'>". $value['no_organizacion'] . "</option>";
								}
							    ?>
						    </select>
						</td>
					</tr>

					<tr>
						<td align="right">Fecha Inicial: </td>
						<td>
							<input type="date" id="txtnofechaini" name="txtnofechaini" step="1" size="12" maxlength="10" value="<?php echo $params['desde']; ?>" required />
							<span class="MsgValidacion_Fecha_Inicial"></span>
						</td>
					</tr>
					<tr>
						<td align="right">Fecha Final: </td>
					    <td>
						    <input type="date" id="txtnofechafin" name="txtnofechafin" step="1" size="12" maxlength="10" value="<?php echo $params['hasta']; ?>" required />
						    <span class="MsgValidacion_Fecha_Final"></span>
						</td>
					</tr>

					<!--<tr>
						<td align="right">Tipo Movimiento: </td>
						<td align="left">
							<select id="cbotv" name="cbotv">
								<option value="T">Todos</option>
								<option value="1">Tickets</option>
								<option value="0">Compras</option>
							</select>
						</td>
					</tr>

				    <tr>
						<td align="right">Tipo Vista: </td>
					    	<td>
						    <input type="radio" name="rdnotipo" value="D" checked >Detallado
						    <input type="radio" name="rdnotipo" value="R" >Resumido
						</td>
					</tr>-->

					<tr>
						<td colspan="2" align="center">
							<button id="btnbuscarvc"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
						</td>
					</tr>
				<?php
				}
				?>
				</table>
		</div>
		<div align="center" id="GriRegistroVentasCentralizadasDetalle"></div>
	<?php }

	function renderTablaVentasCentralizadas($data) { ?>
		<?php
		$org_id = null;
		//echo 'query: '.$data['query'];
		?>

		<div style="width: auto;border: 1px;">
			<br/>
			<table border="0">
				<tr>
					<th class="grid_cabecera"></th>
					<th colspan="2" class="grid_cabecera">GLP</th>
					<th colspan="2" class="grid_cabecera">LIQUIDO</th>
					<th colspan="2" class="grid_cabecera">GNV</th>
					<th colspan="2" class="grid_cabecera">MARKET</th>
					<th colspan="2" class="grid_cabecera">TOTAL</th>
				</tr>
				<tr>
					<th class="grid_cabecera">FECHA Y HORA</th>
					<th class="grid_cabecera">Galones</th>
					<th class="grid_cabecera">Importe</th>
					<th class="grid_cabecera">Galones</th>
					<th class="grid_cabecera">Importe</th>
					<th class="grid_cabecera">M3</th>
					<th class="grid_cabecera">Importe</th>
					<th class="grid_cabecera" colspan="2">Importe</th>
					<th class="grid_cabecera">GALONES</th>
					<th class="grid_cabecera">Importe</th>
				</tr>
				<?php
				//var_dump($data['data']);
				$icg = 0; $iig = 0;
				$cg = 0; $ig = 0;
				$v2 = 0;
				$v3 = 0;
				$v4 = 0;
				$v5 = 0;
				$v7 = 0;
				foreach ($data['data'] as $key => $result) {
					$cg = $result[2] + $result[4]; $ig = $result[3] + $result[5] + $result[7];
					
					$color = ($key % 2 == 0 ? "grid_detalle_impar" : "grid_detalle_par");
					if($org_id != $result[1]) {
						if($key != 0) {
							?>
							<tr>
								<td colspan="9"></td>
								<td style="font-size:0.9em; color:black;" align ="right"><b><?php echo TemplateRegistroVentasCental::NumberFormat($icg) ?></b></td>
								<td style="font-size:0.9em; color:black;" align ="right"><b><?php echo TemplateRegistroVentasCental::NumberFormat($iig) ?></b></td>
							</tr>
							<?php
							$icg = 0; $iig = 0;
						}
					?>
					<tr>
						<td colspan="11" style="font-size:0.9em; color:black;"><b>ORGANIZACIÓN: <?php echo $result[0] ?></b></td>
					</tr>
					<?php
					} $icg += $cg; $iig += $ig; ?>
				<tr>
					<!--<td class="<?php echo $color ?>"><?php echo 'D: '.$data['fechaIni'].'<br>H: '.$data['fechaIni'];  ?></td>-->
					<td class="<?php echo $color ?>"><?php echo $result[8]; ?></td>
					<td class="<?php echo $color ?>" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($result[2] == NULL ? 0.0 : $result[2]) ?></td>
					<td class="<?php echo $color ?>" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($result[3] == NULL ? 0.0 : $result[3]) ?></td>
					<td class="<?php echo $color ?>" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($result[4] == NULL ? 0.0 : $result[4]) ?></td>
					<td class="<?php echo $color ?>" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($result[5] == NULL ? 0.0 : $result[5]) ?></td>
					<td class="<?php echo $color ?>" align ="right">0</td>
					<td class="<?php echo $color ?>" align ="right">0</td>
					<td class="<?php echo $color ?>" colspan="2" align ="right"><?php echo $result[7] == NULL ? 0.0 : $result[7] ?></td>
					<!--<?php $cg = $result[2] + $result[4]; $ig = $result[3] + $result[5] + $result[7];//2,4,7 ?>-->
					<td class="<?php echo $color ?>" align ="right"><?php echo $cg; ?></td>
					<td class="<?php echo $color ?>" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($ig) ?></td>
				</tr>
				<?php
				$tcg += $cg; $tig += $ig;
				$cg = 0;
				$v2 += $result[2];
				$v3 += $result[3];
				$v4 += $result[4];
				$v5 += $result[5];
				$v7 += $result[7];

				$org_id = $result[1];
				} ?>
				<tr>
					<td class="grid_total">TOTAL GENERAL</td>
					<td class="grid_total" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($v2) ?></td>
					<td class="grid_total" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($v3) ?></td>
					<td class="grid_total" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($v4) ?></td>
					<td class="grid_total" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($v5) ?></td>
					<td class="grid_total" align ="right">0</td>
					<td class="grid_total" align ="right">0</td>
					<td class="grid_total" colspan="2" align ="right"><?php echo $v7 ?></td>
					<td class="grid_total" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($tcg) ?></td>
					<td class="grid_total" align ="right"><?php echo TemplateRegistroVentasCental::NumberFormat($tig) ?></td>
				</tr>
			</table>
		</div>
	<?php }
}


