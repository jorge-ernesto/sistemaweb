<?php
class depacho_electronico_Template extends Template {
	function titulo() {
		$titulo = '<div align="center"><h2>Facturaci&oacute;n</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>' . $errormsg . '</blink>';
	}

	function Inicio($lados) {
?>

<div  align="center">
	<table>
		<tr>
			<td colspan="5" style="text-align: center;">
				<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;">
					<b>Despachos Electronicos.<b>
				</h2>
			</td>
		</tr>
		<tr>
			<td>Fecha Inicio</td>
			<td><input type='text' id='fecha_inicio' class='fecha_formato'/></td>
			<td id="cont_ini"><select id="opt_inicio"><option>Opcion</option></select></td>
			<td><input type="checkbox" value="si" id="ch_manual_ini"/>Manual</td>
		</tr>
		<tr>
			<td>Fecha Final</td>
			<td><input type='text' id='fecha_final' class='fecha_formato'/></td>
			<td id="cont_final"><select id="opt_final"><option>Opcion</option></select></td>
			<td><input type="checkbox" value="si" id="ch_manual_final"/>Manual</td>
		</tr>
		<tr>
			<td>Lado</td>
			<td>
				<select id="opt_lados">
					<option>00</option>
					<?php
					foreach ($lados as $value) {
						echo "<option value='" . $value['f_pump_id'] . "'>" . $value['name'] . "</option>";
					}
					?>
				</select>
			</td>
			<td><input type='text' id='id_lado' class='fecha_formato' style="width: 30px;" value="01"/></td>
		</tr>
		<tr>
			<td>Mangueral</td>
			<td id="cont_final">
				<select id="opt_grade_id">
					<option>00</option>
				</select></td>
				<td><input type='text' id='id_manguera' class='fecha_formato' style="width: 30px;" value="01"/></td>
			</tr>
			<tr>
				<td colspan="2"><button id="executar">Consultar</button></td>
				<td colspan="2"><button id="executar_excel">Reporte Excel</button></td>
			</tr>
		</table>
	</div>
	<div  align="center" id="tab_id_detalle"></div>
	<?php
	}

	function CrearTablaReporte($data) {
		//var_dump($data);
	?>

	<div style="width: auto;border: 1px;">
		<table cellspacing="0" cellpadding="0" border="0">	
			<thead>
				<th class="th_cabe">F.Emision</th>
				<th class="th_cabe">Lado</th>
				<th class="th_cabe">Manguera </th>
				<th class="th_cabe">Precio </th>
				<th class="th_cabe">Cantidad</th>
				<th class="th_cabe">Importe</th>
				<th class="th_cabe">Cnt.Vol</th>
				<th class="th_cabe">Cnt.imp</th>
				<th class="th_cabe">Cnt.Vol pry</th>
				<th class="th_cabe">Cnt.Imp pry</th>
				<th class="th_cabe">diff.Vol</th>
				<th class="th_cabe">diff.Imp</th>
				<th class="th_cabe">type</th>
			</thead>
			<tbody>
				<?php
				$i = 0;
				$volumen = 0;
				$importe = 0;
				$cont = 0;
				foreach ($data as $valor_cadena) {
					$tragal = round($valor_cadena['tragal'], 3);
					$tratot = round($valor_cadena['tratot'], 3);
					$tot_volume = round($valor_cadena['tot_volume'], 3);
					$tot_value = round($valor_cadena['tot_value'], 3);

					$proyecion_volumen = round($volumen + $tragal, 2);
					$proyecion_importe = round($importe + $tratot, 2);
					$dif_vol = round($proyecion_volumen - $tot_volume, 2);
					$dif_imp = round($proyecion_importe - $tot_value, 2);

					$estila = "fila_registro_imppar";
					if ($i % 2 == 0) {
						$estila = "fila_registro_par";
					}

					$color_v = '';
					if ($dif_vol != 0) {
						$color_v = "style='color:red'";
					}

					$color_c = '';
					if ($dif_imp != 0) {
						$color_c = "style='color:red'";
					}

					echo "<tr class='$estila'>";
					echo "<td>" . $valor_cadena['hora'] . "</td>";
					echo "<td>" . $valor_cadena['tralad'] . "</td>";
					echo "<td>" . $valor_cadena['tragra'] . "</td>";
					echo "<td>" . $valor_cadena['trapre'] . "</td>";
					echo "<td>" . $valor_cadena['tragal'] . "</td>";
					echo "<td>" . $valor_cadena['tratot'] . "</td>";
					echo "<td>" . $tot_volume . "</td>";
					echo "<td>" . $tot_value . "</td>";
					if ($cont == 0) {
						echo "<td>-</td>";
						echo "<td>-</td>";
						echo "<td >-</td>";
						echo "<td >-</td>";
						echo "<td >-</td>";
					} else {
						echo "<td>" . $proyecion_volumen . "</td>";
						echo "<td>" . $proyecion_importe . "</td>";
						echo "<td $color_v>" . ($dif_vol) . "</td>";
						echo "<td $color_c>" . ($dif_imp) . "</td>";
						echo "<td>" . $valor_cadena['codpos'] . "</td>";
					}

					$cont++;
					$volumen = round($valor_cadena['tot_volume'], 3);
					$importe = round($valor_cadena['tot_value'], 3);
					echo "</tr>";
					$i++;
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
	}
}
?>
