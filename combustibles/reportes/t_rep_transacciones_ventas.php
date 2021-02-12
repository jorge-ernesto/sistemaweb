<?php
class TemplateReporteTransaccionVenta extends Template {
	function Inicio($estaciones, $txtnofechaini, $txtnofechafin) { 
?>

		<div align="center" id="TransaccionVentaCabecera">
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Transacciones Diarias de Venta Sunat <b></h2>
			<table cellspacing="2" cellpadding="2" border="0">
				<tr>
					<td align="right">Almacen: </td>
					<td>
						<select id="cmbnualmacen">
							<option value="T">Todos</option>
							<?php
							foreach($estaciones as $value) {
								echo "<option value='" . $value['nualmacen'] . "'>". $value['noalmacen'] . "</option>";
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right">Fecha Inicial: </td>
					<td>
						<input type="text" id="txtnofechaini" name="txtnofechaini" step="1" size="12" maxlength="10" value="<?php echo $txtnofechaini; ?>" required />
					</td>
				</tr>
				<tr>
					<td align="right">Fecha Final: </td>
					<td>
						<input type="text" id="txtnofechafin" name="txtnofechafin" step="1" size="12" maxlength="10" value="<?php echo $txtnofechafin; ?>" required />
					</td>
				</tr>
				<tr>
					<td align="right">Tipo Vista: </td>
					<td>
						<input type="radio" name="rdnotipo" value="D" checked >Detallado
						<input type="radio" name="rdnotipo" value="R" >Resumido
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
		<div align="center" id="TransaccionVentaDetalle"></div>
<?php }
	function ListaTransaccionVenta($data, $notipo) {
?>
		
		<div style="width: auto;border: 1px;"><br/>
			<table border="0">
				<tr>
					<th rowspan="2" class="grid_cabecera">FECHA</th>
					<th colspan="2" class="grid_cabecera">BOLETA</th>
					<th colspan="2" class="grid_cabecera">FACTURA</th>
					<th colspan="2" class="grid_cabecera">NOTA CREDITO</th>
					<th colspan="2" class="grid_cabecera">NOTA DEBITO</th>
					<th colspan="2" class="grid_cabecera">TOTALES</th>
				</tr>
				<tr>
					<th class="grid_cabecera">NUMERO</th>
					<th class="grid_cabecera">IMPORTE</th>
					<th class="grid_cabecera">NUMERO</th>
					<th class="grid_cabecera">IMPORTE</th>
					<th class="grid_cabecera">NUMERO</th>
					<th class="grid_cabecera">IMPORTE</th>
					<th class="grid_cabecera">NUMERO</th>
					<th class="grid_cabecera">IMPORTE</th>
					<th class="grid_cabecera">NUMERO</th>
					<th class="grid_cabecera">IMPORTE</th>
				</tr>
				<?php
				$i 			= 0;
				$result		= null;
				$registrob	= 0;
				$importeb	= 0.00;
				$registrof	= 0;
				$importef	= 0.00;
				$registronc	= 0;
				$importenc	= 0.00;
				$registrond	= 0;
				$importend	= 0.00;
				$registro	= 0;//TOTAL REGITROS X DIA
				$importe	= 0.00;//TOTAL IMPORTE X DIA

				//T = Tickets
				//DM = Documento Manuales
				
				//VARIBLES PARA SUMA TOTAL
				$sumregistrob 		= 0;
				$sumimporteb 		= 0.00;
				$sumregistrof 		= 0;
				$sumimportef		= 0.00;
				$sumnuregistrodmnc 	= 0;
				$sumnuimportedmnc 	= 0.00;
				$sumnuregistrodmnd 	= 0;
				$sumnuimportedmnd 	= 0.00;
				//TOTAL
				$sumregistro 		= 0;
				$sumimporte 		= 0.00;

				foreach ($data as $row) {
					$color = ($i % 2 == 0 ? "grid_detalle_impar" : "grid_detalle_par");

					$registrob	= ($row['nuregistrotb'] + $row['nuregistrodmb']);
					$importeb 	= ($row['nuimportetb'] + $row['nuimportedmb']);
					$registrof	= ($row['nuregistrotf'] + $row['nuregistrodmf']);
					$importef	= ($row['nuimportetf'] + $row['nuimportedmf']);
					$registronc = $row['nuregistrodmnc'];
					$importenc	= $row['nuimportedmnc'];
					$registrond = $row['nuregistrodmnd'];
					$importend 	= $row['nuimportedmnd'];
					//TOTAL
					$registro 	= ($registrob + $registrof + $registronc + $registrond);
					$importe 	= ($importeb + $importef + $importenc + $importend);

					if($notipo == 'D') {
						//Detallado
						$result .= '<tr>';
						$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['fapertura']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormatTransaction($registrob)) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormat($importeb)) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormatTransaction($registrof)) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormat($importef)) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormatTransaction($registronc)) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormat($importenc)) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormatTransaction($registrond)) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormat($importend)) . '</td>';
						//COLUMNA TOTAL
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormatTransaction($registro)) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities(TemplateReporteTransaccionVenta::NumberFormat($importe)) . '</td>';
						$result .= '</tr>';
					}

					//SUMA DE TOTALES
					$sumregistrob 		+= $registrob;
					$sumimporteb 		+= $importeb;
					$sumregistrof 		+= $registrof;
					$sumimportef		+= $importef;
					$sumnuregistrodmnc 	+= $registronc;
					$sumnuimportedmnc 	+= $importenc;
					$sumnuregistrodmnd 	+= $registrond;
					$sumnuimportedmnd 	+= $importend;
					//TOTAL
					$sumregistro 		+= $registro;
					$sumimporte 		+= $importe;

					$i++;
				}

				$result .= '<tr>';
				$result .= '<th class="grid_total" align="right">TOTALES: </th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormatTransaction($sumregistrob) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormat($sumimporteb) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormatTransaction($sumregistrof) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormat($sumimportef) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormatTransaction($sumnuregistrodmnc) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormat($sumnuimportedmnc) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormatTransaction($sumnuregistrodmnd) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormat($sumnuimportedmnd) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormatTransaction($sumregistro) . '</th>';
				$result .= '<th class="grid_total" align="right">' . TemplateReporteTransaccionVenta::NumberFormat($sumimporte) . '</th>';
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
}
