<?php

class TemplateMovPorDias extends Template {

	function Form($estaciones, $lineas, $hoy) { ?>

		<div align="center" id="AjusteUbicacionCabecera">

			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Movimientos Acumulados por Rango de Fecha<b></h2>

			<table cellspacing="0" cellpadding="3" border="0">

				<tr>
					<td align="right">Almacen: </td>
					<td>
						<select id="nualmacen">
							<?php
							foreach($estaciones as $value){
								echo "<option value='" . $value['nualmacen'] . "'>". $value['noalmacen'] . "</option>";
							}
							?>
						</select>
					</td>
				</tr>

				<tr>
                    <td align="right">Desde: </td>
                    <td><input type='text' id='fecha_inicio' class='fecha_formato' size="12" maxlength="10" value="<?php echo $hoy; ?>" />
                <tr>

                <tr>
                    <td align="right">Hasta: </td>
                    <td><input type='text' id='fecha_final' class='fecha_formato' size="12" maxlength="10" value="<?php echo $hoy; ?>" />
                <tr>

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
				<tr>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>CODIGO</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>DESCRIPCION</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>STK INICIAL</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>ENTRADAS</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>SALIDAS</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>AJUSTES</th>
					<th class="grid_cabecera" style="font-size:0.9em;"><b>STK FINAL</th>
				</tr>

				<?php

				$i 			= 0;
				$result			= null;
				$noalmacen 		= null;
				$art_linea 		= null;
			
				foreach ($data as $row) {

					$A1 = $row['stock'] + $row['movimiento'];
					$A2 = $row['entrada'];
					$A3 = $row['salida'];
					$A4 = $row['ajuste'];
					$A5 = $row['stock'] + $row['movimiento'] + $row['entrada'] - $row['salida'] + $row['ajuste'];

					if ($A1 == '') $A1 = 0.00;
					if ($A2 == '') $A2 = 0.00;
					if ($A3 == '') $A3 = 0.00;
					if ($A4 == '') $A4 = 0.00;

					$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
					
						$result .= '<tr bgcolor="">';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['art_codigo']) . '</td>';
						$result .= '<td class="'.$color.'" align ="left">' . htmlentities($row['art_descripcion']) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($A1) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($A2) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($A3) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($A4) . '</td>';
						$result .= '<td class="'.$color.'" align ="right">' . htmlentities($A5) . '</td>';
			
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