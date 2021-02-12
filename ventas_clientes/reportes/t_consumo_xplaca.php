<?php
class Consumos_Placa_Template extends Template {
	function Inicio($estaciones, $hoy) { ?>
        <div align="center">
		    <h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Consumos por Placa de Veh&iacute;culos</b></h2>
			<table border="0">
				<tr>
            	    <td align="right">Almacen: </td>
				   	<td colspan="3">
						<select id="almacen">
							<option value="T">Todos</option>
							<?php
							foreach($estaciones as $value)
								echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
							?>
						</select>
					</td>
				</tr>
				<tr>
                	<td align="right">Fecha Inicio: </td>
                	<td><input maxlength="10" size="10" type='text' id='fecha_inicial' class='fecha_formato' value=<?php echo (empty($_REQUEST['fecha_inicial']) ? $hoy : $_REQUEST['fecha_inicial']); ?> /></td>
            	</tr>
				<tr>
                	<td align="right">Fecha Final: </td>
                	<td><input maxlength="10" size="10" type='text' id='fecha_final' class='fecha_formato'  value=<?php echo (empty($_REQUEST['fecha_final']) ? $hoy : $_REQUEST['fecha_final']); ?> /></td>
            	</tr>
				<tr>
                    <td align="right">Cod. Cliente: </td>
                    <td colspan="3"><input maxlength="12" size="12" type='text' id='cliente' class='fecha_formato' /></td>
				</tr>
				<tr>
                    <td align="right">Nro. Placa: </td>
                    <td colspan="3"><input maxlength="8" size="10" type='text' id='placa' class='fecha_formato' /></td>
				</tr>
				<tr>
					<td align="right">Mostrar reporte: </td>
	                <td id="cont_ubica">
						<input type="radio" name="radio-mostrar_reporte" value="R">Resumido
						<input type="radio" name="radio-mostrar_reporte" value="D" checked>Detallado</td>
					</td>
                </tr>
				<tr>
                    <td colspan="4" align="center">
			            <button id="buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
						<button id="pdf" onclick="pdf();"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF</button>
					</td>
                </tr>
			</table>
		</div>
        <div align="center" id="tab_id_detalle"></div>
	<?php
	}
	
	function CrearTablaReporte($data, $arrPost) {
		?>
    	<table class="report_CRUD">	
		    <thead>
		    	<tr>
		    		<th style="background-color: white">&nbsp;</th>
		    	</tr>
		    </thead>
			<thead>
		    	<tr bgcolor="#FFFFCD">
				    <th class="grid_cabecera">Fecha</th>
				    <th class="grid_cabecera">Estacion</th>
				    <th class="grid_cabecera">Nº Ticket</th>
				    <th class="grid_cabecera">Odometro</th>
				    <th class="grid_cabecera">Codigo Producto</th>
				    <th class="grid_cabecera">Nombre Producto</th>
				    <th class="grid_cabecera">Precio</th>
				    <th class="grid_cabecera">Cantidad</th>
				    <th class="grid_cabecera">Importe</th>
		    	</tr>
			</thead>
			<tbody>
				<?php
				$i 				= 0;
				$tickets 		= 0;
				$cliente 		= "";
				$placa 			= "";
			  	$numvales 		= 0;
			  	$precio 		= 0;
				$cantidadplaca 	= 0;
				$importeplaca 	= 0;
				$sumcantidad 	= 0;
				$sumimporte 	= 0;
				$cantidadcli 	= 0;
				$importecli 	= 0;

				$tickets = count($data);
				for($i=0; $i < $tickets; $i++){
					$estila = "grid_detalle_impar";
					if ($i % 2 == 0)
						$estila = "grid_detalle_par";

					if($placa != $data[$i]['placa']){
						if($i!=0){
							echo "<tr class='bgcolor'>";
								echo "<td colspan='7' align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Total por Placa: </td>";
								echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($cantidadplaca, 2, '.', ',')."</b></p></td>";
								echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($importeplaca, 2, '.', ',')."</b></p></td>";
							echo "</tr>";
							$cantidadplaca = 0;
							$importeplaca = 0;

							//if($data[$i]['codcliente'] != $data[$i+1]['codcliente']){
							if($cliente != $data[$i]['codcliente'] && ($arrPost['sIdCliente'] == '' || empty($arrPost['sIdCliente']))){
								echo "<tr class='$estila'>";
									echo "<td colspan='7' align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Total por Cliente: </b></td>";
									echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($cantidadcli, 2, '.', ',')."</b></p></td>";
									echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($importecli, 2, '.', ',')."</b></p></td>";
							    echo "</tr>";
								$cantidadcli = 0;
								$importecli = 0;
								$cliente = $data[$i]['codcliente'];
							}
						}

						echo "<tr class='" . $estila . "'>";
							echo "<td colspan='9' align='left' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Placa: </b>" . $data[$i]['placa'] . " <b>Cliente: </b>".$data[$i]['codcliente']." - ".$data[$i]['descliente']."</p></td>";
						echo "</tr>";
						$placa = $data[$i]['placa'];
					}

					if ( $arrPost['sMostrarReporte'] == 'D' ) {
	                    echo "<tr class='" . $estila . "'>";
	                        echo "<td align='center'>" . $data[$i]['fecha'] . "</td>";
	                        echo "<td align='center'>" . $data[$i]['desalmacen'] . "</td>";
	                        echo "<td align='center'>" . $data[$i]['ticket'] . "</td>";
	                        echo "<td align='center'>" . $data[$i]['odometro'] . "</td>";
	                        echo "<td align='center'>" . $data[$i]['codproducto'] . "</td>";
	                        echo "<td align='center'>" . $data[$i]['nomproducto'] . "</td>";
	                        echo "<td align='center'>" . number_format($data[$i]['precio'], 2, '.', ',') . "</td>";
	                        echo "<td align='right'>" . number_format($data[$i]['cantidad'], 2, '.', ',') . "</td>";
	                        echo "<td align='right'>" . number_format($data[$i]['importe'], 2, '.', ',') . "</td>";
	                    echo "</tr>";
	                }

					$cantidadcli += (double)$data[$i]['cantidad'];
					$importecli	+= (double)$data[$i]['importe'];

					$sumcantidad = $sumcantidad + $data[$i]['cantidad'];
					$sumimporte	= $sumimporte + $data[$i]['importe'];
					$cantidadplaca = $cantidadplaca + $data[$i]['cantidad'];
					$importeplaca = $importeplaca + $data[$i]['importe'];
					$numvales++;
				}// /. FOR
				echo "<tr class='" . $estila . "'>";
					echo "<td colspan='7' align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Total por Placa: </td>";
					echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($cantidadplaca, 2, '.', ',')."</b></p></td>";
					echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($importeplaca, 2, '.', ',')."</b></p></td>";
		        echo "</tr>";
				echo "<tr class='$estila'>";
					echo "<td colspan='7' align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Total por Cliente: </b></td>";
					echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($cantidadcli, 2, '.', ',')."</b></p></td>";
					echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($importecli, 2, '.', ',')."</b></p></td>";
			    echo "</tr>";
                echo "<thead>";
                	echo "<tr>";
	                    echo "<th class='grid_cabecera' colspan='8' style='text-align:right''>Total Documentos: </th>";
	                    echo "<th class='grid_cabecera' style='text-align:right'>" . $tickets . "</th>";
					echo "</tr>";
                echo "</thead>";
                echo "<thead>";
                	echo "<tr>";
		                echo "<th class='grid_cabecera' colspan='8' style='text-align:right'>Total Cantidad: </th>";
		                echo "<th class='grid_cabecera' style='text-align:right'>" . number_format($sumcantidad, 2 , '.', ',') . "</th>";
					echo "</tr>";
                echo "<thead>";
                	echo "<tr>";
	                    echo "<th class='grid_cabecera' colspan='8' style='text-align:right'>Total Importe: </th>";
	                    echo "<th class='grid_cabecera' style='text-align:right'>" . number_format($sumimporte, 2 , '.', ',') . "</th>";
					echo "</tr>";
                echo "</thead>";
				?>
			</tbody>
		</table>
	<?php
	}
}
