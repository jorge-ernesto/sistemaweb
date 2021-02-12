<?php
class Formato_Fisico_Template extends Template {
	function Inicio($estaciones, $fecha) {
?>
		<div align="center">
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Formato de Inventario Fisico<b></h2>
				<table border="0">
					<tr>
						<td align="right"><p style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 10px;line-height: 14px;color: black;"><b>Fecha Sistema: </b></p></td>
						<td align="left"><p style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 10px;line-height: 14px;color: black;"><b><?php echo $fecha['fecha']; ?></b></p></td>
					</tr>
					<tr>
						<td align="right">Almacen: </td>
						<td>
							<select id="almacen">
								<option value="T">Seleccionar...</option>
								<?php
								foreach($estaciones as $value) {
									echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Ubicaciones: </td>
						<td id="cont_ubica">
							<select id="opt_ubica_id">
								<option>No hay ubicaciones</option>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Orden: </td>
						<td id="cont_ubica">
							<input type="radio" name=myorden value="C" >C&oacute;digo
							<input type="radio" name=myorden value="D" checked>Descripci&oacute;n
						</td>
					</tr>
					<tr>
						<td align="right">Considerar Stock Cero(0) S/N: </td>
						<td><select id="stk">
							<option value="S">SI</option>
							<option value="N">NO</option>
						</select></td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<button id="buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>
							<button id="excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button>
						</td>
					</tr>
				</table>
			</div>
			<div  align="center" id="tab_id_detalle"></div>

			<?php
	}

	function CrearTablaReporte($data) {
			?>
		<div style="width: auto;border: 1px;">
			<table cellspacing="0" cellpadding="0" border="0">	
				<thead><th>&nbsp;</th></thead>
				<thead>
					<th class="th_cabe">CODIGO</th>
					<th class="th_cabe">NOMBRE DEL PRODUCTO</th>
					<th class="th_cabe">UBICACI&Oacute;N</th>
					<th class="th_cabe">PRECIO</th>
					<th class="th_cabe">STOCK</th>
					<th class="th_cabe">INVENTARIO FISICO</th>
				</thead>
				<tbody>
					<?php
					$i = 0;
					foreach ($data as $valor_cadena) {
						$estila = "fila_registro_imppar";
						if($i % 2 == 0) {
							$estila = "fila_registro_par";
						}

						$totalstk = $totalstk + (empty($valor_cadena['stkact']) ? 0.00 : $valor_cadena['stkact']);
						echo "<tr class='$estila'>";
						echo "<td align='center'>" . $valor_cadena['codigo'] . "</td>";
						echo "<td align='left'>" . $valor_cadena['descripcion'] . "</td>";
						echo "<td align='center'>" . $valor_cadena['ubica'] . "</td>";
						echo "<td align='center'>" . $valor_cadena['precio'] . "</td>";
						echo "<td align='right'>" . (empty($valor_cadena['stkact']) ? '0.00' : $valor_cadena['stkact']) . "</td>";
						echo "<td align='center'>______________________</td>";
						echo "</tr>";

						$i++;
					}

					echo "<thead>";
					echo "<th class='th_cabe' colspan='4' align='right'>TOTAL STOCK </th>";
					echo "<th class='th_cabe' align='right'>" . number_format($totalstk, 2) . "</th>";
					echo "<th class='th_cabe' align='right'> </th>";
					?>
						
				</tbody>
			</table>
		</div>
		<?php
	}
}
		?>
