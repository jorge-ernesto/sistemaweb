<?php
class TemplateContingenciaFE extends Template {
	function Inicio($estaciones, $txtnofechaini, $txtnofechafin) { ?>
		<div align="center" id="BodyContingenciaFE">
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Contingencia Facturacion Electronica<b></h2>
				<table cellspacing="4" cellpadding="4" border="0">
				    <tr>
						<td align="right">Almacen: </td>
					    <td>
						    <select id="cboalmacen">
							    <?php
								foreach($estaciones as $value){
									echo "<option value='" . $value['nualmacen'] . "'>". $value['noalmacen'] . "</option>";
								}
							    ?>
						    </select>
						</td>
					</tr>

					<tr>
						<td align="right">Motivo Contingencia: </td>
						<td align="left">
							<select id="cbomtc" name="cbomtc">
								<option value="1">Conexión internet</option>
						    	<option value="2">Fallas Fluido eléctrico</option>
						    	<option value="3">Desastres Naturales</option>
						    	<option value="4">Robo</option>
						    	<option value="5">Fallas en el sistema de facturación</option>
						    	<option value="6">Venta Itinerante</option>
						    	<option value="7">Otros</option>
							</select>
						</td>
					</tr>

					<tr>
						<td align="right">Tipo Venta: </td>
						<td align="left">
							<select id="cbotv" name="cbotv">
								<option value="T">Todos</option>
								<option value="TK">Tickets</option>
								<option value="CM">Comprobantes Manuales</option>
							</select>
						</td>
					</tr>

					<tr>
						<td align="right">N. de veces que se envía: </td>
						<td align="left">
							<select id="cbo-veces_enviada">
							<?php
							for ($i=1; $i < 100; $i++) {
								$i = (strlen($i) == 1 ? "0" . $i : $i);?>
								<option value="<?php echo $i;?>"><?php echo (int)$i;?></option>
							<?php
							} ?>
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
						<td colspan="2" align="center">
							<button id="btnbuscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar&nbsp; </button>
							&nbsp;&nbsp;&nbsp;<button id="btntxt"><img src="/sistemaweb/icons/texto.png" align="right" />Generar Archivo &nbsp; </button>
						</td>
					</tr>
				</table>
		</div>
		<div  align="center" id="GridviewContingenciaFE"></div>
	<?php
	}

    function GridviewContingenciaFE($data) { ?>
			<div style="width: auto;border: 1px;">
				<br>
				<table border="0">
					<tr>
						<th class="grid_cabecera">Fecha Emision</th>
						<th class="grid_cabecera">Tipo Documento</th>
						<th class="grid_cabecera">Numero Serie</th>
						<th class="grid_cabecera">Numero Documento Inicial</th>
						<th class="grid_cabecera">Numero Documento Final</th>
						<th class="grid_cabecera">Tipo Documento</th>
						<th class="grid_cabecera"># Documento Identidad</th>
						<th class="grid_cabecera">Nombre</th>
						<th class="grid_cabecera">Valor Venta</th>
						<th class="grid_cabecera">Exoneradas</th>
						<th class="grid_cabecera">Inafectas</th>
						<th class="grid_cabecera">ISC</th>
						<th class="grid_cabecera">I.G.V</th>
						<th class="grid_cabecera">Otros Cargos</th>
						<th class="grid_cabecera">Total</th>
						<th class="grid_cabecera">Tipo Documento Ref.</th>
						<th class="grid_cabecera">Serie Documento Ref.</th>
						<th class="grid_cabecera">Numero Documento Ref.</th>
					</tr>
					<?php
					$i 		= 0;
					$result	= null;

					// Variables para totalizar
					$sumnuvalor_venta_og 	= 0.00;
					$sumnuvalor_venta_oe 	= 0.00;
					$sumnuvalor_venta_oi 	= 0.00;
					$sumnuisc 				= 0.00;
					$sumnuigv 				= 0.00;
					$sumnuotros_cargos 		= 0.00;
					$sumnutotal 			= 0.00;

					foreach ($data as $row) {
						$color = ($i % 2 == 0 ? "grid_detalle_impar" : "grid_detalle_par");
						$result .= '<tr>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['femision']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nutd']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['noserie']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nudocumento_inicial']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nudocumento_final']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nutd_identidad']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nudocumento_identidad']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nodocumento_identidad']) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($this->NumberFormat($row['nuvalor_venta_og'])) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($this->NumberFormat($row['nuvalor_venta_oe'])) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($this->NumberFormat($row['nuvalor_venta_oi'])) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($this->NumberFormat($row['nuisc'])) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($this->NumberFormat($row['nuigv'])) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($this->NumberFormat($row['nuotros_cargos'])) . '</td>';
							$result .= '<td class="'.$color.'" align ="right">' . htmlentities($this->NumberFormat($row['nutotal'])) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nutd_referencia']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nuserie_referencia']) . '</td>';
							$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['nunumero_referencia']) . '</td>';
						$result .= '</tr>';

						// Sum total
						$sumnuvalor_venta_og 	+= $row['nuvalor_venta_og'];
						$sumnuvalor_venta_oe 	+= $row['nuvalor_venta_oe'];
						$sumnuvalor_venta_oi	+= $row['nuvalor_venta_oi'];
						$sumnuisc 				+= $row['nuisc'];
						$sumnuigv 				+= $row['nuigv'];
						$sumnuotros_cargos		+= $row['nuotros_cargos'];
						$sumnutotal 			+= $row['nutotal'];

						$i++;
					}

					$result .= '<tr>';
						$result .= '<th colspan="8" class="grid_total" align="right">TOTALES: </th>';
						$result .= '<th class="grid_total" align="right">' . $this->NumberFormat($sumnuvalor_venta_og) . '</th>';
						$result .= '<th class="grid_total" align="right">' . $this->NumberFormat($sumnuvalor_venta_oe) . '</th>';
						$result .= '<th class="grid_total" align="right">' . $this->NumberFormat($sumnuvalor_venta_oi) . '</th>';
						$result .= '<th class="grid_total" align="right">' . $this->NumberFormat($sumnuisc) . '</th>';
						$result .= '<th class="grid_total" align="right">' . $this->NumberFormat($sumnuigv) . '</th>';
						$result .= '<th class="grid_total" align="right">' . $this->NumberFormat($sumnuotros_cargos) . '</th>';
						$result .= '<th class="grid_total" align="right">' . $this->NumberFormat($sumnutotal) . '</th>';
						$result .= '<th colspan="3" class="grid_total" align="right">&nbsp;</th>';
					$result .= '</tr>';

					echo $result;
					?>
				</table>
			</div>
	<?php
	}

	function NumberFormat($number) {
		return number_format($number, 2, '.', ',');
	}

	function NumberFormatTransaction($number) {
		return number_format($number, 0, '.', ',');
	}
}