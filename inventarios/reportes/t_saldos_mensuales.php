<?php

class TemplateSaldosMensuales extends Template {

	function Form($estaciones, $lineas, $hoy) { ?>

		<div align="center" id="SaldosMensualesCabecera">

			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Saldos Mensuales por Almacen<b></h2>

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
				<tr>
                    <!--<td align="right" >Codigo Articulo</td>-->
                    <td><input type='hidden' id='cod_art' name='cod_art' class='cod_art'/></td>
				</tr>
				<tr>
                    <td align="right" >Descripcion Articulo</td>
                    <td  ><input type='text' id='desc_art' placeholder="Ingresar Codigo o Nombre" name='desc_art' class='desc_art' size="50" /></td>
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
					<td colspan="2" align="center">
						<button id="btnbuscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar&nbsp;</button>
						<button id="btnexcel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel&nbsp;</button>
					</td>
				</tr>

			</table>
		</div>

	<div  align="center" id="SaldosMensuales"></div>

	<?php
	}

	function SaldosMensuales($data,$request) { ?>
		<div style="width: auto;border: 1px;">
			<br/>
			<table border="0">
				<tr>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>ALMACEN</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>COD. ITEM</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>DESCRIPCION</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>STOCK</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>S. FISICO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>COSTO P.</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>STK. INI.A&Ntilde;O</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>C. INI.A&Ntilde;O</th>
					<!--<th class="grid_cabecera" style="font-size:0.9em;"><b>TIPO PLU</th>-->
				</tr>

				<?php
				$i = 0;
				foreach ($data as $row) {
					$color = ($i % 2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");
					$result .= '<tr bgcolor="">';
					$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['7']) . '</td>';
					$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['0']) . '</td>';
					$result .= '<td class="'.$color.'" align ="center">' . htmlentities($row['1']) . '</td>';
					$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['2']) . '</td>';
					$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['3']) . '</td>';
					$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['4']) . '</td>';
					$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['5']) . '</td>';
					$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['6']) . '</td>';
					//$result .= '<td class="'.$color.'" align ="right">' . htmlentities($row['8']) . '</td>';
					$result .= '</tr>';
					$i++;
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
		$yearMin = strtotime ( '-5 year' , strtotime ( $year ) ) ;
		$yearMin = date ( 'Y' , $yearMin );
		for ($i = $year; $i >= $yearMin; $i--) { 
			$return .= '<option value="'.$i.'">'.$i.'</option>';
		}
		return $return;
	}

}