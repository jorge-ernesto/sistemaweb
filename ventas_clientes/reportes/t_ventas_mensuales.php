<?php

class TemplateVentasMensuales extends Template {

	function Form($estaciones, $lineas, $hoy) { ?>

		<div align="center" id="VentasMensualesCabecera">

			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Reporte de Ventas Mensuales<b></h2>

			<table cellspacing="0" cellpadding="3" border="0">

				<tr>
					<td align="right">Almacen: </td>
					<td>
						<select id="cod_almacen">
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
					<td align="right">Periodo: </td>
					<td  ><input type='text' id='periodo' name='periodo' class='periodo' required placeholder='Ingrese Año'/></td>
				</tr>
				<tr>
					<td align="right">Linea: </td>
					<td>
						<select id="cod_linea" name="cod_linea">
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
					<td align="right">Mostrar:</td>
					<td>
						<select id="modo" name="modo">
							<option value="todo">Todo</option>
							<option value="cantidades">Solo Cantidades</option>
							<option value="valores">Solo Valores</option>
						</select>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<button id="btnbuscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar&nbsp;</button>
						<button id="btnexcel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel&nbsp;</button>
					</td>
				</tr>

			</table>
		</div>

	<div  align="center" id="VentasMensuales"></div>

	<?php
	}

	function VentasMensuales($data,$request) { ?>

		<div style="width: auto;border: 1px;">

		<br/>
			<table border="1">
			<tr> 
			<?php
			$tipover = $request['modo'];
			if ($tipover == 'todo'){
			?>
		    <td width="200"><font size="-4" face="Arial, Helvetica, sans-serif">Codigo y Descripcion del Articulo</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can01</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val01</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can02</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val02</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can03</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val03</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can04</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val04</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can05</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val05</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can06</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val06</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can07</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val07</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can08</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val08</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can09</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val09</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can10</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val10</font></td>
		    <td width="41"><font size="-4" face="Arial, Helvetica, sans-serif">can11</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val11</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can12</font></td>
		    <td width="43"><font size="-4" face="Arial, Helvetica, sans-serif">val12</font></td>
		    <?php
			}else if ($tipover == 'cantidades'){
			?>
			<td width="200"><font size="-4" face="Arial, Helvetica, sans-serif">Codigo y Descripcion del Articulo</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can01</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can02</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can03</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">can04</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can05</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can06</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can07</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can08</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can09</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can10</font></td>
		    <td width="41"><font size="-4" face="Arial, Helvetica, sans-serif">can11</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">can12</font></td>
			<?php
			}else{
			?>
			<td width="200"><font size="-4" face="Arial, Helvetica, sans-serif">Codigo y Descripcion del Articulo</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val01</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val02</font></td>
		    <td width="35"><font size="-4" face="Arial, Helvetica, sans-serif">val03</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val04</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val05</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val06</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val07</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val08</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val09</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val10</font></td>
		    <td width="40"><font size="-4" face="Arial, Helvetica, sans-serif">val11</font></td>
		    <td width="43"><font size="-4" face="Arial, Helvetica, sans-serif">val12</font></td>
			<?php
			}
			?>
			</tr>
		   

			<?php
			foreach ($data as $row) {

			$tipover = $request['modo'];

			$result .= '<tr bgcolor="">';
			$result .= '<td align ="left">' . htmlentities($row['2']) . '</td>';

			if ($tipover == 'todo'){
				for($i = 3; $i < 27; $i++) {
				$result .= '<td align ="right">' . htmlentities($row[$i]) . '</td>';
				}
			}else if ($tipover == 'cantidades'){
				$result .= '<td align ="right">' . htmlentities($row['3']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['5']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['7']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['9']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['11']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['13']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['15']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['17']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['19']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['21']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['23']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['25']) . '</td>';
			}else{
				$result .= '<td align ="right">' . htmlentities($row['4']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['6']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['8']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['10']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['12']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['14']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['16']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['18']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['20']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['22']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['24']) . '</td>';
				$result .= '<td align ="right">' . htmlentities($row['26']) . '</td>';
			}

			$result .= '</tr>';
			}

			echo $result;
			?>

			</table>
		</div>

	<?php
	}

}