<?php

class ConsumoValesTemplate extends Template {

	function Inicio($estaciones) {
	$fecha = date("d/m/Y");
	?>
        <div align="center">
	    	<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Detalle de Consumo de Vales<b></h2>
            <table border="0">
			    <tr>
					<td align="right">Almacen: </td>
			    	<td >
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
                    <td><input maxlength="10" size="12" type="text" id="fecha_inicio" name="fecha_inicio" class="fecha_formato" value="<?php echo (empty($_REQUEST['fecha_inicio']) ? $fecha : $_REQUEST['fecha_inicio']); ?>" /></td>
                </tr>
                <tr>
                    <td align="right">Fecha Final: </td>
                    <td><input maxlength="10" size="12" type="text" id="fecha_final" name="fecha_final" class="fecha_formato" value="<?php echo (empty($_REQUEST['fecha_final']) ? $fecha : $_REQUEST['fecha_final']); ?>" /></td>
                </tr>
                <tr>
                    <td align="right">Codigo Cliente: </td>
                    <td>
                    	<!--<input maxlength="12" size="30" type='text' id='cliente' class='fecha_formato' />-->
                		<input type="hidden" id="txt-Nu_Documento_Identidad" name="Nu_Documento_Identidad" />
			        	<input type="text" maxlength="50" size="50" id="txt-No_Razsocial" name="No_Razsocial" autocomplete="off" placeholder="Ingresar Codigo o Nombre del Cliente" />
                    </td>
                </tr>
                <tr>
                    <td align="right">Num. Liquidacion: </td>
                    <td ><input maxlength="12" size="12" type='text' id='liquidacion' class='fecha_formato' /></td>
                </tr>
                <tr>
                    <td align="right">Num. Factura: </td>
                    <td ><input maxlength="12" size="12" type='text' id='factura' class='fecha_formato' /></td>
                </tr>
				<tr>
					<td align="right">Tipo: </td>
	                <td id="cont_ubica">
						<input type="radio" name=myorden value="R" >Resumido
						<input type="radio" name=myorden value="D" checked>Detallado</td>
					</td>
                </tr>
				<tr>
					<td align="right">Mostrar hora: </td>
	                <td id="cont_ubica">
						<input type="checkbox" id="chk-hora" onclick="validacionFechaPorMes(this)"><span style="font-weight:bold;font-size:11px;color:#31708f" id="span-msg-validacion-hora"></span>
					</td>
                </tr>
				<tr>
					<td align="right">Mostrar precio pizarra: </td>
	                <td id="cont_ubica">
						<input type="checkbox" id="chk-precio_pizarra">
					</td>
                </tr>
			    <tr>
					<td align="right">Tipo cliente: </td>
			    	<td >
					    <select id="cbo-tipo-cliente">
						    <option value="T">Todos</option>
						    <option value="0">Efectivo</option>
						    <option value="1">Crédito</option>
						    <option value="2">Anticipo</option>
					    </select>
					</td>
			    </tr>
					<tr>
						<td align="right">Orden: </td>
						<td >
							<select id="cbo-tipo-version">
								<option value="0">Cliente - Fecha</option>
								<option value="1">Cliente - Documento</option>
							</select>
						</td>
					</tr>
                <tr>
                    <td colspan="2" align="center"><button id="buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>
                    <button id="excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button></td>
                </tr>
            </table>
        </div>
        <div  align="center" id="tab_id_detalle">
		</div>
	<?php
	}

	function CrearTablaReporte($data, $myorden, $hora, $arrRequest) { ?>
		<?php
			$nueva_logica = false;
			if($arrRequest['iTipoVersion'] == 1){
				$nueva_logica = true;
			}

			if(is_array($data)) {
		?>
		<div style="width: auto;border: 1px;">
			<table cellspacing="0" cellpadding="0" border="0">	
			<thead><th>&nbsp;</th></thead>
				<thead>
                    <th class="th_cabe">Almacen</th>
                    <th class="th_cabe">#Liquidacion</th>
                    <th class="th_cabe">#Factura</th>
                  
                    <th class="th_cabe">#Despacho</th>
                    <th class="th_cabe">Fecha</th>
                    <?php if ( $hora=='true' ) { ?><th class="th_cabe">Hora</th><?php } ?>
                    <th class="th_cabe">Nº Manual</th>
                    <th class="th_cabe">Placa</th>
                    <th class="th_cabe">Producto</th>
                    <th class="th_cabe">Odometro</th>
                    <th class="th_cabe">Usuario</th>
                    <th class="th_cabe">Cantidad</th>
                    <th class="th_cabe">Precio Contratado</th>
                    <th class="th_cabe">Importe Contratado</th>
					<?php if ( $arrRequest['sPrecioPizarra']=='true' ) { ?>
						<th class="th_cabe">Precio Pizarra</th>
						<th class="th_cabe">Importe Pizarra</th>
						<th class="th_cabe" title="Precio contratado - Precio pizarra">Diferencia Precio</th>
						<th class="th_cabe" title="Importe contratado - Importe pizarra">Diferencia Importe</th>
					<?php } ?>
                    <th class="th_cabe"></th>
			    </thead>
				<tbody>
					<?php
						$objmodel 	= new ConsumoValesModel();
						
						if($nueva_logica){

							$tickets 		= 0;
							$sumimporte 	= 0;
							$sumcantidad 	= 0;
							$cliente 		= "";
							$nomcliente 	= "";
							$cantidadcli 	= 0;
							$importecli 	= 0;
							// $documento     = "SIN DOCUMENTO";
							// $documento_    = "SIN DOCUMENTOS";
							// $producto      = "";
							// $cantidad_fac_anticipo = 0;
							// $importe_fac_anticipo  = 0;
	
							$fImportePizarra = 0.00;
							$fImporteDiferencia = 0.00;
	
							$sTipoCliente = '';
							
							//RECORREMOS ARRAY DE CLIENTES
							foreach ($data as $key => $cliente) {		
								if((string)$key == "total_general"){
									continue;
								}
								
								//OBTENEMOS TITULO DEL CLIENTE
								$cliente_porciones = explode("|", $key);
								$sTipoCliente = $cliente_porciones[2];		
								$codCliente   = $cliente_porciones[0];														
								$nomCliente   = $cliente_porciones[1];
								$badUTF8 = htmlentities($nomCliente);
								//iconv() can ignore characters which cannot be encoded in the target character set
								$nomcliente = iconv("utf-8", "utf-8//IGNORE", $badUTF8);
								echo "<tr class='fila_registro_par'>";
								echo "<td colspan='13' align='left' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Cliente " . $sTipoCliente . "</b>: " . $codCliente . " - " . $nomcliente . " </p></td>";
								echo "</tr>";								
								
								//RECORREMOS FACTURAS DEL CLIENTE
								foreach ($cliente as $key2 => $factura) {		
									if((string)$key2 == "total_cliente"){
										continue;
									}
									
									//OBTENEMOS TITULO DE LA FACTURA
									$documento = $key2;
									$total_factura = $objmodel->getTotalFactura($sTipoCliente, $documento);
									echo "<tr class='fila_registro_par'>";
									echo "<td colspan='20' align='left' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>Documento: " . (TRIM($documento) == "" ? "SIN DOCUMENTO" : TRIM($documento)) . " | <b>Cantidad: ".number_format((float)$total_factura[0], 4, '.', ',')." | <b>Total: ".number_format((float)$total_factura[1], 2, '.', ','). "</b></p></td>";
									echo "</tr>";		

									//RECORREMOS PRODUCTOS DE LA FACTURA
									foreach ($factura as $key3 => $item) {
										if((string)$key3 == "total_factura"){
											continue;
										}
										
										//OBTENEMOS TITULO DE LOS ITEMS													
										$item_porciones = explode("|", $key3);
										$codigo_item    = $item_porciones[0];
										$name_item      = $item_porciones[1];
										$total_item_factura         = $objmodel->getTotalItemByFactura($sTipoCliente, $documento, $codigo_item);
										$mostrar_total_item_factura = ($sTipoCliente == "ANTICIPO") ? " | Cantidad: " . number_format((float)$total_item_factura[0], 2, '.', ',') . " | Total: " . number_format((float)$total_item_factura[1], 2, '.', ',') : "";
										echo "<tr class='fila_registro_par'>";
										echo "<td colspan='20' align='left' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>". $name_item . $mostrar_total_item_factura ."</b></p></td>";
										echo "</tr>";

										//RECORREMOS VALES METIDOS DENTRO DEL ARRAY ITEM
										foreach ($item as $key4 => $vale) {			
											if((string)$key4 == "total_item"){
												continue;
											}

											$estila = "fila_registro_imppar";
											if ($i % 2 == 0)
												$estila = "fila_registro_par";

											$tickets++;											

											if ( $arrRequest['sPrecioPizarra']=='true' ){
												$fImportePizarra= round($vale['cantidad'] * $vale['nu_precio_especial'],2);
												$fImporteDiferencia= round($vale['importe'],2) - $fImportePizarra;

												$fTotImportePizarra+=$fImportePizarra;
												$fTotImporteDiferencia+=$fImporteDiferencia;
											}

											if( $myorden=='D' ){
												//OBTENEMOS VALES																				
												echo "<tr class='$estila'>";
													echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['almacen'] . "</td>";
													echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['liquidacion'] . "</td>";

													if( $vale["documento"]!='' )
														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale["documento"] . "</td>";
													else
														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale["documento2"] . "</td>";

														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['numero'] . "</td>";
														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['fecha'] . "</td>";

														if ( $hora=="true"){
															echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['hora'] . "</td>";
														}

														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['vale'] . "</td>";
														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['placa'] . "</td>";
														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['producto'] . "</td>";
														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['odometro'] . "</td>";
														echo "<td align='center' bgcolor='#DDFFE4'>" . $vale['chofer'] . "</td>";
															
														echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($vale['cantidad'], 4, '.', ',') . "</td>";
														echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($vale['ss_precio_contratado'], 2, '.', ',') . "</td>";
														echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($vale['importe'], 2, '.', ',') . "</td>";

													if ( $arrRequest['sPrecioPizarra']=='true' ){
														echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($vale['nu_precio_especial'], 2, '.', ',') . "</td>";
														echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($fImportePizarra, 2, '.', ',') . "</td>";
														echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($vale['ss_precio_contratado'] - $vale['nu_precio_especial'], 2, '.', ',') . "</td>";
														echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($fImporteDiferencia, 2, '.', ',') . "</td>";
													}

													echo "<td align='center' bgcolor='#DDFFE4'><a href='#' onclick='imprimir_comprobante_pdf(\"" . trim($vale['nu_almacen']) . "\", \"" . trim($vale['numero']) . "\", \"" . trim($vale['fecha']) . "\", \"" . trim($vale['ch_turno']) . "\");'>imprimir</a></td>";
												echo "</tr>";
											}
										}

										//OBTENEMOS TOTALES POR ITEM SIN RECORRER ARRAY $item
										echo "<tr class='fila_registro_par'>";
										echo "<td colspan='10' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>Total Item:</td>";
										echo "<td align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>".number_format($item['total_item']['cantidad_item'], 4, '.', ',')."</td>";
										echo "<td align='right' bgcolor='#DDFFE4'></td>";
										echo "<td align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>".number_format($item['total_item']['importe_item'], 2, '.', ',')."</td>";
										echo "<td colspan='7' align='right' bgcolor='#DDFFE4'></td>";
										echo "</tr>";	

										if($sTipoCliente == "ANTICIPO"){
											//OBTENEMOS QUIEBRE ENTRE EL TOTAL ITEM FACTURA VS LOS VALES
											echo "<tr class='fila_registro_par'>";
											echo "<td colspan='10' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:green;'><b>Quiebre Item:</td>";
											echo "<td align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:green;'><b>".number_format((float)$total_item_factura[0] - $item['total_item']['cantidad_item'], 4, '.', ',')."</td>";
											echo "<td align='right' bgcolor='#DDFFE4'></td>";
											echo "<td align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:green;'><b>".number_format((float)$total_item_factura[1] - $item['total_item']['importe_item'], 2, '.', ',')."</td>";
											echo "<td colspan='7' align='right' bgcolor='#DDFFE4'></td>";
											echo "</tr>";	
										}										
									}
									
									//OBTENEMOS TOTALES POR FACTURA SIN RECORRER ARRAY $factura
									echo "<tr class='fila_registro_par'>";
									echo "<td colspan='10' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>Total Factura:</td>";
									echo "<td align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>".number_format($factura['total_factura']['cantidad_factura'], 4, '.', ',')."</td>";
									echo "<td align='right' bgcolor='#DDFFE4'></td>";
									echo "<td align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>".number_format($factura['total_factura']['importe_factura'], 2, '.', ',')."</td>";
									echo "<td colspan='7' align='right' bgcolor='#DDFFE4'></td>";
									echo "</tr>";		

									if($sTipoCliente == "ANTICIPO"){
										//OBTENEMOS QUIEBRE ENTRE EL TOTAL FACTURA VS LOS VALES
										echo "<tr class='fila_registro_par'>";
										echo "<td colspan='10' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:green;'><b>Quiebre Factura:</td>";
										echo "<td align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:green;'><b>".number_format((float)$total_factura[0] - $factura['total_factura']['cantidad_factura'], 4, '.', ',')."</td>";
										echo "<td align='right' bgcolor='#DDFFE4'></td>";
										echo "<td align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:green;'><b>".number_format((float)$total_factura[1] - $factura['total_factura']['importe_factura'], 2, '.', ',')."</td>";
										echo "<td colspan='7' align='right' bgcolor='#DDFFE4'></td>";
										echo "</tr>";	
									}		
								}

								//OBTENEMOS TOTALES POR CLIENTE SIN RECORRER ARRAY $cliente
								echo "<tr class='fila_registro_par'>";
								echo "<td colspan='10' align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Total Cliente:</td>";
								echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($cliente['total_cliente']['cantidad_cliente'], 4, '.', ',')."</td>";
								echo "<td align='right' bgcolor='#F8F8F8'></td>";
								echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($cliente['total_cliente']['importe_cliente'], 2, '.', ',')."</td>";
								echo "</tr>";					
							}

							echo "<thead>";
								echo "<th class='th_cabe' colspan='12' align='right'>Cantidad Nota Despacho: </th>";
								echo "<th class='th_cabe' align='right'>" . $tickets . "</th>";
							echo "</thead>";

							echo "<thead>";
								echo "<th class='th_cabe' colspan='12' align='right'>Total Cantidad: </th>";
								echo "<th class='th_cabe' align='right'>" . number_format($data['total_general']['cantidad_general'], 4 , '.', ',') . "</th>";
							echo "</thead>";

							echo "<thead>";
								echo "<th class='th_cabe' colspan='12' align='right'>Total Importe: </th>";
								echo "<th class='th_cabe' align='right'>" . number_format($data['total_general']['importe_general'], 2 , '.', ',') . "</th>";
							echo "</thead>";
							
							if ( $arrRequest['sPrecioPizarra']=='true' ){
								echo "<thead>";
									echo "<th class='th_cabe' colspan='12' align='right'>Total Importe Pizarra: </th>";
									echo "<th class='th_cabe' align='right'>" . number_format($fTotImportePizarra, 2 , '.', ',') . "</th>";
								echo "</thead>";
								echo "<thead>";
									echo "<th class='th_cabe' colspan='12' align='right' title='Total (Importe contratado - Importe pizarra)'>Total Importe Diferencia: </th>";
									echo "<th class='th_cabe' align='right'>" . number_format($fTotImporteDiferencia, 2 , '.', ',') . "</th>";
								echo "</thead>";
							}

							return;							
						}						
					?>


					<?php
						$tickets 		= 0;
						$sumimporte 	= 0;
						$sumcantidad 	= 0;
						$cliente 		= "";
						$nomcliente 	= "";
						$cantidadcli 	= 0;
						$importecli 	= 0;
						// $documento     = "SIN DOCUMENTO";
						// $documento_    = "SIN DOCUMENTOS";
						// $producto      = "";
						// $cantidad_fac_anticipo = 0;
						// $importe_fac_anticipo  = 0;

						$fImportePizarra = 0.00;
						$fImporteDiferencia = 0.00;

						$sTipoCliente = '';

						for($i=0; $i < count($data); $i++){
							$estila = "fila_registro_imppar";
							if ($i % 2 == 0)
								$estila = "fila_registro_par";

							$tickets = count($data);

							$sTipoCliente = 'EFECTIVO';
							if ( $data[$i]['nu_tipo_efectivo'] == '0' && $data[$i]['no_tipo_anticipo'] == 'N' ){
								$sTipoCliente = 'CREDITO';
							} else if ( $data[$i]['nu_tipo_efectivo'] == '0' && $data[$i]['no_tipo_anticipo'] == 'S' ){
								$sTipoCliente = 'ANTICIPO';
							}

							if($cliente != $data[$i]['codcliente']){
								$badUTF8 = htmlentities($data[$i]["nomcliente"]);
								//iconv() can ignore characters which cannot be encoded in the target character set
								$nomcliente = iconv("utf-8", "utf-8//IGNORE", $badUTF8);

								if($i!=0){
									echo "<tr class='" . $estila . "'>";
									echo "<td colspan='10' align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Total:</td>";
									echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($cantidadcli, 4, '.', ',')."</td>";
									echo "<td align='right' bgcolor='#F8F8F8'></td>";
									echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($importecli, 2, '.', ',')."</td>";
								    echo "</tr>";

									$cantidadcli = 0;
									$importecli = 0;
									// $documento = "SIN DOCUMENTO";
									// $documento_ = "SIN DOCUMENTO";
									// $producto      = "";
								}

								echo "<tr class='$estila'>";
								echo "<td colspan='13' align='left' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Cliente " . $sTipoCliente . "</b>: " . $data[$i]['codcliente'] . " - " . $nomcliente . " </p></td>";
								echo "</tr>";

								$cliente = $data[$i]['codcliente'];
							}

							if ( $arrRequest['sPrecioPizarra']=='true' ){
								$fImportePizarra= round($data[$i]['cantidad'] * $data[$i]['nu_precio_especial'],2);
								$fImporteDiferencia= round($data[$i]['importe'],2) - $fImportePizarra;

								$fTotImportePizarra+=$fImportePizarra;
								$fTotImporteDiferencia+=$fImporteDiferencia;
							}

							if( $myorden=='D' ){
									// if($sTipoCliente == 'ANTICIPO'){ //SI EL CLIENTE ES ANTICIPO
										
									// 	//LOGICA PARA OBTENER LOS TOTALES POR FACTURAS DEL CLIENTE
									// 	if($documento != $data[$i]['documento']){ //SI LA COLUMNA #FACTURA ES DIFERENTE, ES DECIR SI YA PASO A UN NUEVO CLIENTE
									// 		if($i!=0){ //NO ES EL PRIMER ELEMENTO DEL ARRAY, NI EL PRIMER VALE DE UN NUEVO CLIENTE
									// 			echo "<tr class='fila_registro_par'>";
									// 				echo "<td colspan='10' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>Total</b></p></td>";
									// 				echo "<td colspan='1' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>".number_format($cantidad_fac_anticipo, 4, '.', ',')."</b></p></td>";
									// 				echo "<td colspan='1' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'></p></td>";
									// 				echo "<td colspan='1' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>".number_format($importe_fac_anticipo, 4, '.', ',')."</b></p></td>";
									// 				echo "<td colspan='1' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'></p></td>";
									// 			echo "</tr>";

									// 			echo "<tr class='fila_registro_par'>";
									// 				echo "<td colspan='13' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>".number_format($data[($i-1)]['importe_factura_anticipo'] - $importe_fac_anticipo, 4, '.', ',')."</b></p></td>";
									// 				echo "<td colspan='1' align='right' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'></p></td>";
									// 			echo "</tr>";

									// 			$cantidad_fac_anticipo = 0;
									// 			$importe_fac_anticipo = 0;
									// 		}

									// 		echo "<tr class='fila_registro_par'>";
									// 		echo "<td colspan='14' align='left' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>Documento: " . (TRIM($data[$i]['documento']) == "" ? "SIN DOCUMENTO" : TRIM($data[$i]['documento'])) . " | <b>Total: ".number_format($data[$i]['importe_factura_anticipo'], 2, '.', ',')."</b></p></td>";
									// 		echo "</tr>";
											
									// 		$documento = $data[$i]['documento'];
									// 	}

									// 	//LOGICA PARA MARCAR LOS PRODUCTOS AGRUPADAS POR FACTURAS
									// 	if($documento_ != $data[$i]['documento'] || $producto != $data[$i]['producto']){
									// 		echo "<tr class='fila_registro_par'>";
									// 			echo "<td colspan='2' align='left' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'><b>".$data[$i]['producto']."</b></p></td>";
									// 			echo "<td colspan='12' align='left' bgcolor='#DDFFE4'><p style='font-size:1.1em; color:black;'></p></td>";
									// 		echo "</tr>";
									// 		$documento_ = $data[$i]['documento'];
									// 		$producto = $data[$i]['producto'];
									// 	}

									// }

                                echo "<tr class='$estila'>";
		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['almacen'] . "</td>";
		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['liquidacion'] . "</td>";

									if( $data[$i]["documento"]!='' )
										echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]["documento"] . "</td>";
									else
										echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]["documento2"] . "</td>";

		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['numero'] . "</td>";
		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['fecha'] . "</td>";

		                            if ( $hora=='true')
		                            	echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['hora'] . "</td>";

		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['vale'] . "</td>";
		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['placa'] . "</td>";
		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['producto'] . "</td>";
		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['odometro'] . "</td>";
		                            echo "<td align='center' bgcolor='#DDFFE4'>" . $data[$i]['chofer'] . "</td>";
		                            
		                            echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($data[$i]['cantidad'], 4, '.', ',') . "</td>";
									echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($data[$i]['ss_precio_contratado'], 2, '.', ',') . "</td>";
									echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($data[$i]['importe'], 2, '.', ',') . "</td>";

									if ( $arrRequest['sPrecioPizarra']=='true' ){
										echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($data[$i]['nu_precio_especial'], 2, '.', ',') . "</td>";
										echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($fImportePizarra, 2, '.', ',') . "</td>";
										echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($data[$i]['ss_precio_contratado'] - $data[$i]['nu_precio_especial'], 2, '.', ',') . "</td>";
										echo "<td align='right' bgcolor='#DDFFE4'>" . number_format($fImporteDiferencia, 2, '.', ',') . "</td>";
									}

									echo "<td align='center' bgcolor='#DDFFE4'><a href='#' onclick='imprimir_comprobante_pdf(\"" . trim($data[$i]['nu_almacen']) . "\", \"" . trim($data[$i]['numero']) . "\", \"" . trim($data[$i]['fecha']) . "\", \"" . trim($data[$i]['ch_turno']) . "\");'>imprimir</a></td>";
								echo "</tr>";
							}

							$sumcantidad+=$data[$i]['cantidad'];
							$sumimporte+=$data[$i]['importe'];
							$cantidadcli+=$data[$i]['cantidad'];
							$importecli+=$data[$i]['importe'];
							// $cantidad_fac_anticipo+=$data[$i]['cantidad'];
							// $importe_fac_anticipo+=$data[$i]['importe'];
						}

						echo "<tr class='$estila'>";
							echo "<td colspan='10' align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>Total:</td>";
							echo "<td align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($cantidadcli, 4, '.', ',')."</td>";
							echo "<td colspan='2' align='right' bgcolor='#F8F8F8'><p style='font-size:1.1em; color:black;'><b>".number_format($importecli, 2, '.', ',')."</td>";
					    echo "</tr>";

                        echo "<thead>";
	                        echo "<th class='th_cabe' colspan='12' align='right'>Cantidad Nota Despacho: </th>";
	                        echo "<th class='th_cabe' align='right'>" . $tickets . "</th>";
                        echo "</thead>";

                        echo "<thead>";
	                        echo "<th class='th_cabe' colspan='12' align='right'>Total Cantidad: </th>";
	                        echo "<th class='th_cabe' align='right'>" . number_format($sumcantidad, 4 , '.', ',') . "</th>";
                        echo "</thead>";

						echo "<thead>";
	                        echo "<th class='th_cabe' colspan='12' align='right'>Total Importe: </th>";
	                        echo "<th class='th_cabe' align='right'>" . number_format($sumimporte, 2 , '.', ',') . "</th>";
						echo "</thead>";
						
						if ( $arrRequest['sPrecioPizarra']=='true' ){
							echo "<thead>";
								echo "<th class='th_cabe' colspan='12' align='right'>Total Importe Pizarra: </th>";
								echo "<th class='th_cabe' align='right'>" . number_format($fTotImportePizarra, 2 , '.', ',') . "</th>";
							echo "</thead>";
							echo "<thead>";
								echo "<th class='th_cabe' colspan='12' align='right' title='Total (Importe contratado - Importe pizarra)'>Total Importe Diferencia: </th>";
								echo "<th class='th_cabe' align='right'>" . number_format($fTotImporteDiferencia, 2 , '.', ',') . "</th>";
							echo "</thead>";
						}
					?>
					</tbody>
				</table>
			</div>
		<?php
		}
	}
}
