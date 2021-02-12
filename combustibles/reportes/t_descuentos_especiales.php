<?php

class Descuentos_Especiales_Template extends Template {

	function Inicio($estaciones, $desde, $hasta, $tarjetas) { ?>

        	<div align="center" id="Inicio">
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Descuentos Especiales<b></h2>
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
		                	<td><input maxlength="10" size="10" type='text' id='fecha_inicial' class='fecha_formato' value=<?php echo (empty($_REQUEST['fecha_inicial']) ? $desde : $_REQUEST['fecha_inicial']); ?> /></td>
		                	<td align="right">Turno: </td>
		               		<td id="turno_inicial"><select id="opt_inicial"><option>Opcion</option></select></td>
		            	</tr>

		            	<tr>
		                	<td align="right">Fecha Final: </td>
		                	<td><input maxlength="10" size="10" type='text' id='fecha_final' class='fecha_formato'  value=<?php echo (empty($_REQUEST['fecha_final']) ? $hasta : $_REQUEST['fecha_final']); ?> /></td>
		                	<td align="right">Turno: </td>
		                	<td id="turno_final"><select id="opt_final"><option>Opcion</option></select></td>
		            	</tr>

				<tr>
					<td align="right">Tipo Venta: </td>
					<td colspan="3" align="left">
					<select id="txtnum_tv">
						<option value='T'>Todos</option>
						<option value='C'>Combustible</option>
						<option value='M'>Market</option>
					</select>
					</td>
				</tr>

				<tr>
					<td align="right">Tipo Documento: </td>
					<td colspan="3" align="left">
					<select id="txtnum_td">
						<option value='T'>Todos</option>
						<option value='B'>Ticket - Boleta</option>
						<option value='F'>Ticket - Factura</option>
						<option value='N'>Ticket - Nota de Despacho</option>
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
		                	<td colspan="4" align="center"><button id="buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
		                	&nbsp;&nbsp;&nbsp;<button id="excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>
		                	&nbsp;&nbsp;&nbsp;<button id="agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar</button></td>
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
				<th class="th_cabe">T. Doc.</td>
				<th class="th_cabe">Fecha Sistema</td>
				<th class="th_cabe">Turno</td>
				<th class="th_cabe">Fecha Emision</td>
				<th class="th_cabe">Nº Caja</td>
				<th class="th_cabe">Nº Ticket</td>
				<th class="th_cabe">Importe</td>
				<th class="th_cabe">Descripcion</td>
				<th class="th_cabe">Num. Doc. Identidad</td>
				<th class="th_cabe">Cliente</td>
				<th class="th_cabe">Forma Pago</td>
				<th class="th_cabe">T. Tarjeta</td>
		    </thead>
                    <tbody>
			<?php

			$i = 0;
			$tickets = 0;
			$sumimporte = 0;

                        foreach ($data as $valor_cadena) {

				$estila = "fila_registro_imppar";

				if ($i % 2 == 0)
					$estila = "fila_registro_par";

				$sumcantidad	= $sumcantidad + $valor_cadena['cantidad'];
				$sumimporte 	= $sumimporte + $valor_cadena['importe'];
				$tickets 	= count($data); 

                                echo "<tr class='$estila'>";
                                echo "<td align='center'>" . $valor_cadena['td'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['dia'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['turno'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['fecha'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['caja'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['trans'] . "</td>";
                                echo "<td align='right'>" . $valor_cadena['importe'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['descripcion'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['ruc'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['razsocial'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['formapago'] . "</td>";
                                echo "<td align='center'>" . $valor_cadena['nombretrj'] . "</td>";
                                echo "</tr>";

                                $i++;

                        }
				
                        echo "<thead>";
                        echo "<th class='th_cabe' colspan='11' align='right'>Total Documentos: </th>";
                        echo "<th class='th_cabe' align='right'>" . $tickets . "</th>";
                        echo "<thead>";
                        echo "<th class='th_cabe' colspan='11' align='right'>Total Cantidad: </th>";
                        echo "<th class='th_cabe' align='right'>" . number_format($sumcantidad, 2 , '.', ',') . "</th>";
                        echo "<thead>";
                        echo "<th class='th_cabe' colspan='11' align='right'>Total Importe: </th>";
                        echo "<th class='th_cabe' align='right'>" . number_format($sumimporte, 2 , '.', ',') . "</th>";

                        ?>
                    </tbody>


                </table>
            </div>
            <?php
        }

	function AgregarDescuento($estaciones, $lados) { ?>

		<center>
		<div>
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;">Descuentos Especiales</h2>
			<table cellspacing="0" cellpadding="2" border="0">

 				<tr>
		        		<td align="right">Almacen: </td>
					    	<td colspan="3">
						    <select id="almacen">
							    <?php
								foreach($estaciones as $value){
									echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
								}
							    ?>
						    </select>
					</td>
				</tr>

				<tr>

					<td align="right">Fecha Emisi&oacute;n:</td>
					<td><input maxlength="10" size="12" type="text" name="fecha" id="fecha" class="fecha_formato" placeholder="Ingresar Fecha" required/></td>

				</tr>

				<tr>

				    	<td align="right">Nro. Ticket: </td>
				    	<td><input maxlength="14" size="14" type='text' id='txtnum_tickes' placeholder="Ingresar Nro. Ticket" required/> </td>

				</tr>

				<tr>
			       		<td align="right">Nro. Caja: </td>
					<td id="cajas">
						<select id="txtnum_caja">
						</select>
						<div id="tab_cajas" style="font-size:1.2em; color:red;"></div>
					</td>
				</tr>

				<tr class="DetailDescuento" style="display: none">
					<td align="right">Producto: </td>
					<td><input type="text" name="nuproducto" id="nuproducto" size="13" maxlength="13" readonly/></td>
				</tr>

				<tr class="DetailDescuento" style="display: none">
					<td align="right">Razon Social: </td>
					<td>
						<input type="text" size="42" maxlength="40" id="nocliente" name="nocliente" readonly/>
					</td>
				</tr>

				<tr class="DetailDescuento" style="display: none">
					<td align="right">Importe Descuento: </td>
					<td><input type="text" id="nuimporte" name="nuimporte" size="16" maxlength="14" readonly/></td>

				</tr>

				<tr class="DetailMsg" style="display: none">
					<td colspan="2" align="center"><p style="color:blue; font-family:Arial; font-size: 12px;">El ticket ya tiene descuento</p>

				</tr>

				<tr class="DetailError" style="display: none">
					<td colspan="2" align="center"><p style="color:red; font-family:Arial; font-size: 12px;">Error: No existe ticket</p>

				</tr>

			    	<tr>
	        			<td colspan="2" align="center">
						<button class="DetailAction" style="display: none" id="guardar"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar</button>
			       			&nbsp;&nbsp;&nbsp;<button id="regresar"><img src="/sistemaweb/icons/greturn.png" align="right" />Regresar</button>
					</td>
				</tr>

			</table>
		</div>
                <div  align="center" id="tab_id_detalle">
		</div>
		</center>

	<?php }

}
