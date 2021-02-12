<?php

class Tarjetas_Credito_Template extends Template {

	function Inicio($tarjetas, $estaciones, $hoy) {
        ?>
        <div align="center">
	    <h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Ventas con Tarjetas de Cr&eacute;dito<b></h2>
            <table border="0">
				    <tr>
                                        <td align="right">Almacen: </td>
				    	<td colspan="3">
					    <select id="almacen">
						    <option value="T">Todos</option>
						    <?php
							foreach($estaciones as $value){
								echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
							}
						    ?>
					    </select>
					</td>
				    </tr>
                                    <tr>
                                        <td align="right">Fecha Inicio: </td>
                                        <td><input maxlength="10" size="10" type='text' id='fecha_inicial' class='fecha_formato' value=<?php echo (empty($_REQUEST['fecha_inicial']) ? $hoy : $_REQUEST['fecha_inicial']); ?> /></td>
                                        <td align="right">Turno: </td>
                                        <td id="turno_inicial"><select id="opt_inicial"><option>Opcion</option></select></td>
                                    </tr>
                                    <tr>
                                        <td align="right">Fecha Final: </td>
                                        <td><input maxlength="10" size="10" type='text' id='fecha_final' class='fecha_formato' value=<?php echo (empty($_REQUEST['fecha_final']) ? $hoy : $_REQUEST['fecha_final']); ?> /></td>
                                        <td align="right">Turno: </td>
                                        <td id="turno_final"><select id="opt_final"><option>Opcion</option></select></td>
                                    </tr>
                                    <tr>
                                        <td align="right">Tipo: </td>
                                        <td colspan="3" align="left">
						<select id="tipo">
							<option value='T'>Todos</option>
							<option value='C'>Combustible</option>
							<option value='GLP'>GLP</option>
							<option value='M'>Market</option>
						</select>
					</td>
                                    </tr>
                                    <tr>
                                        <td align="right">Tipo de Tarjeta: </td>
                                        <td colspan="3" align="left">
						<select id="tarjeta">
	                                                <option value='T'>Todos</option>
	                                                <?php
	                                                foreach ($tarjetas as $value) {
	                                                    echo "<option value='" . $value['id'] . "'>" . $value['name'] . "</option>";
	                                                }
	                                                ?>
						</select>
					</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" align="right"><button id="buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button></td>
                                        <td colspan="2" align="left"><button id="excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button></td>
                                    </tr>
                                    </table>
                                    </div>
                                    <div  align="center" id="tab_id_detalle">

                                    </div>

	<?php
	}

                                function CrearTablaReporte($data) {
                                    ?>

                                    <div style="width: auto;border: 1px;">
                                        <table cellspacing="0" cellpadding="0" border="0">	
					    <thead><th>&nbsp;</th></thead>
                                            <thead>
		                                    <th class="th_cabe">Tipo</th>
		                                    <th class="th_cabe">#Tarjeta</th>
		                                    <th class="th_cabe">Tipo Tarjeta</th>
		                                    <th class="th_cabe">Cliente</th>
		                                    <th class="th_cabe">Caja</th>
		                                    <th class="th_cabe">Cantidad</th>
		                                    <th class="th_cabe">Importe</th>
		                                    <th class="th_cabe">Nº Ticket</th>
		                                    <th class="th_cabe">Fecha</th>
		                                    <th class="th_cabe">Hora</th>
					    </thead>
                                            <tbody>
						<?php

						$i = 0;
						$tickets = 0;
						$sumimporte = 0;

                                                foreach ($data as $valor_cadena) {

							$estila = "fila_registro_imppar";

							if ($i % 2 == 0) {
								$estila = "fila_registro_par";
							}if ($valor_cadena['contador'] == 2){
								$estila = "fila_repetida";
							}
							$sumcantidad = $sumcantidad + $valor_cadena['cantidad'];
							$sumimporte = $sumimporte + $valor_cadena['importe'];
							$tickets = count($data); 

		                                        echo "<tr class='$estila'>";
		                                        echo "<td align='center'>" . $valor_cadena['tipo'] . "</td>";
		                                        echo "<td align='center'>" . $valor_cadena['numtar'] . "</td>";
		                                        echo "<td align='left'>" . $valor_cadena['nomtar'] . "</td>";
		                                        echo "<td align='left'>" . $valor_cadena['cliente'] . "</td>";
		                                        echo "<td align='center'>" . $valor_cadena['caja'] . "</td>";
		                                        echo "<td align='right'>" . $valor_cadena['cantidad'] . "</td>";
		                                        echo "<td align='right'>" . $valor_cadena['importe'] . "</td>";
		                                        echo "<td align='center'>" . $valor_cadena['ticket'] . "</td>";
		                                        echo "<td align='center'>" . $valor_cadena['fecha'] . "</td>";
		                                        echo "<td align='center'>" . $valor_cadena['hora'] . "</td>";
		                                        if ($valor_cadena['contador'] == 2){
		                                        echo "<td style='background-color: #FFFFFF' align='center'>AP/REF Repetido</td>";
		                                        }
		                                        echo "</tr>";

		                                        $i++;

                                                }
							
	                                        echo "<thead>";
	                                        echo "<th class='th_cabe' colspan='9' align='right'>Total Documentos: </th>";
	                                        echo "<th class='th_cabe' align='right'>" . $tickets . "</th>";
	                                        echo "<thead>";
	                                        echo "<th class='th_cabe' colspan='9' align='right'>Total Cantidad: </th>";
	                                        echo "<th class='th_cabe' align='right'>" . number_format($sumcantidad, 2 , '.', ',') . "</th>";
	                                        echo "<thead>";
	                                        echo "<th class='th_cabe' colspan='9' align='right'>Total Importe: </th>";
	                                        echo "<th class='th_cabe' align='right'>" . number_format($sumimporte, 2 , '.', ',') . "</th>";

                                                ?>
                                            </tbody>


                                        </table>
                                    </div>
                                    <?php
                                }

}
