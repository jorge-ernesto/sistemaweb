<?php

class RegistroCajasTemplate extends Template {

    function titulo() {
        return '<h2 align="center"><b>Recibo de Ingreso de Caja</b></h2>';
    }

    function FormularioPrincipal() {
        ?>
        <div style="text-align: center;position: relative;" id="id_nuevo_registro_view">
            <div id="cargardor" style="position: absolute;display: inline;z-index: 999;"><img src="movimientos/cg.gif" /></div>
            <div ><h3 style="color: #336699; text-align: center;">Recibo de Ingreso de Caja.</h3></div>
            <table style="text-align: left;position: relative;margin: 5px auto;">
                <tr>
                    	<td>Sucursal: </td>
                    	<td><div style="float: left;" id="cmbtipooperacion">
                        </div></td>
                    	<td></td>
                </tr>
                <tr>
                    	<td>Fecha Inicio: <input type="hidden" id="tmp_ini" value="<?php echo date('Y-m-') . "01"; ?>"/></td>
                    	<td><input type='text' id='fecha_inicial' class='fecha_formato'/> </td>
                    	<td></td>
                </tr>
                <tr>
			<td>Fecha Final: <input type="hidden" id="tmp_final" value="<?php echo date('Y-m-d'); ?>"/></td>
			<td><input type='text' id='fecha_final' class='fecha_formato'/> </td>
			<td></td>
                </tr>

            	<tr>
                	<td>Todos los Clientes: </td>
			<td id="cont_ubica">
			<input onclick="ViewClient(this)" type="radio" name=myclient value="S" checked>Si
			<input onclick="ViewClient(this)" type="radio" name=myclient value="N" >No
			</td>
            	</tr>

            	<tr class="ayuda_clientes_id" style="display: none;">
                	<td>Nombre Cliente: </td>
                	<td><input maxlength="100" size="100" type="text" placeholder="Ingresar Razon Social Cliente" id="keyword" class='fecha_formato'>
				</tr>

            	<tr class="ayuda_clientes_id" style="display: none;">
                	<td>Ruc Cliente: </td>
                	<td><label name='id_cliente_auto_ruc' id='id_cliente_auto_ruc' class='fecha_formato' /></td>
				</tr>

				<tr>
                    	<td>Operacion: </td>
                    	<td><div style="float: left;" id="cmboperacion">
                        </div></td>
                    	<td></td>
                </tr>

            	<tr>
                    	<td>Registros a Mostar: </td>
				<td>
			<select id="limit">
                            	<option value="10">10</option>
                            	<option value="20">20</option>
                            	<option value="30">30</option>
                            	<option value="40">40</option>
                            	<option value="100">100</option>
                            	<option value="200">200</option>
                            	<option value="300">300</option>
                            	<option value="400">400</option>
                            	<option value="500">500</option>
				<option value="500">1000</option>
                        </select>
			</td>
                    	<td></td>
                </tr>

                <tr>
                    	<td><button id="btnseleccionar"><img align="right" src="/sistemaweb/images/search.png"/>Consultar</button> </td>
                    	<td><button id="id_nuevo_registro"><img align="right" src="/sistemaweb/icons/agregar.gif">Nuevo Recibo</button> </td>
                    	<td><button id="id_nuevo_cuenta_bank" style="display: none;"><img align="right" src="/sistemaweb/icons/agregar.gif">Cuenta Bancaria</button> </td>
                </tr>

		</table>

        </div>
        <div  id="contenidoTablaSelecionar" style="text-align: center;position: relative;">
        </div>
        <?php
	}

    function FormularioPrincipalSegundario($estaciones, $caja, $operacion, $almacen, $fecha_actual, $serie, $tipo_cambio, $monedas) {
        ?>

        <div ><h3 style="color: #336699; text-align: center;">Nuevo Recibo de Ingreso</h3></div>
        <div id="cargardor" style="position: absolute;display: inline;z-index: 999;"><img src="movimientos/cg.gif" /></div>
        <table style="text-align: left;position: relative;margin: 5px auto;">
            <tr>
                <td>Sucursal: </td>
                <td> <select id="cmnsucursal_id" onchange="MostarNumeroRecibo(this)">
                        <option value="-1">Seleccione ..</option>
                        <?php
                        foreach ($estaciones as $key => $valor) {
                            if (strcmp($almacen, $valor[0]) == 0) {
                                echo "<option value='$valor[0]' selected>$valor[1]</option>";
                            } else {
                                echo "<option value='$valor[0]'>$valor[1]</option>";
                            }
                        }
                        ?>
                    </select> </td>
            </tr>
            <tr>
                <td>Recibo Nro  </td>
                <td> <input type='text' id='recibe_nro' class='fecha_formato' value="<?php echo $serie; ?>" /> </td>
            </tr>
            <tr>
                <td>Fecha  <input type="hidden" id="fecha_tmp" value="<?php echo $fecha_actual; ?>"/></td>
                <td> <input type='text' id='fecha_mostar' class='fecha_formato' /> </td>
            </tr>
            <tr>
                <td>Tipo Cambio  </td>
                <td> <input type='text' id='txttipo_cambio' class='fecha_formato' value="<?php echo $tipo_cambio;?>"/> </td>
            </tr>

            <tr>
                <td>Tipo Moneda: </td>
                <td id="idmoneda">
			<select onchange="DocumentosCobrarMoneda()" id="cmnmoneda_id">
	                        <?php
	                        foreach ($monedas as $key => $valor) {
	                            echo "<option value='$valor[0]'>$valor[1]</option>";
	                        }
	                        ?>
			</select>
		</td>
            </tr>

            <tr>
                <td>Caja:  </td>
                <td id="idcaja"> <select id="cmncaja_id">
                        <?php
                        foreach ($caja as $key => $valor) {
                            echo "<option value='$valor[0]'>$valor[1]</option>";
                        }
                        ?>
                    </select></td>
            </tr>

            <tr>
                <td>Operacion:  </td>
                <td id="idoperaion">  <select id="cmnoperacion_id" onchange="SelecionararAyuda(this)">
                        <option value='-' selected>Seleccione..</option>
                        <?php
                        foreach ($operacion as $key => $valor) {
                            echo "<option value='$valor[0]' mostarayuda='$valor[2]'>$valor[1]</option>";
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
		<td>Observacion: </td>
                <td> <input type='text' id='id_observacion' class='fecha_formato' style="width: 250px;"/></td>
            </tr>
            <tr class="ayuda_clientes_id" style="display: none;">
                <td>Nombre Cliente: </td>
                <td> <input type='text' id='id_cliente_auto' class='fecha_formato' style="width: 250px;"></td>
            </tr>
            <tr class="ayuda_clientes_id" style="display: none;">
                <td>Ruc Cliente: </td>
                <td> <input type='text' id='id_cliente_auto_ruc' class='fecha_formato' /></td>
            </tr>

            <tr >
                <td><input type="hidden" id="tipo_accion" value="-" /> 
                    <button id="btnbuscar"><img align="right" src="/sistemaweb/images/search.png"/>Documentos</button></td>
                <td><button  onclick="irhome()"><img align="right" src="/sistemaweb/icons/atra.gif">Regresar</button> </td>
            </tr>

	</table>

        <?php
	}

	function CrearTablaSeleccionarCliente($registros, $tc, $moneda) {

        ?>

        	<span style="color:#30767F;font-weight: bold;">Seleccion de Documentos Pendientes de Cliente</span>
        	<table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 5px auto;">	
		    	<thead>
			    	<th class="th_cabe_dos_cabe" style="background-color: #FFFFFF;"></th>
			    	<th class="th_cabe" align="center">Documento</th>
			    	<th class="th_cabe" align="center">Documento Referencia</th>
			    	<th class="th_cabe" align="center">Fecha Emision</th>
			    	<th class="th_cabe" align="center">Fecha Vencimiento</th>
			    	<th class="th_cabe" align="center">Moneda</th>
			    	<th class="th_cabe" align="center">Importe</th>
			    	<th class="th_cabe" align="center">Saldo</th>
			    	<th class="th_cabe" align="center">Tasa</th>
			    	<th class="th_cabe" align="center">Moneda</th>
			    	<th class="th_cabe" align="center">Monto a Pagar</th>
			</thead>

			<tbody id="registros">

		    	<?php

			$i = 0;
			$amount = 0;

		    	foreach ($registros as $llave => $value) {

				$estila = "fila_registro_imppar";

				if ($i % 2 == 0)
				    $estila = "fila_registro_par";

				if($moneda == '01' && $value['cod_moneda'] == '02')//SI EL CAJA ES EN SOLES Y DOCUMENTO PAGO EN DOLARES
					$amount = ($value['nu_importesaldo'] * $tc);
				elseif($moneda == '02' && $value['cod_moneda'] == '01')//SI EL CAJA ES EN DOLARES Y DOCUMENTO EN SOLES
					$amount = ($value['nu_importesaldo'] / $tc);
				elseif($moneda == '02' && $value['cod_moneda'] == '02')//SI EL CAJA ES EN DOLARES Y DOCUMENTO EN DOLARES
					$amount = $value['nu_importesaldo'];
				else
					$amount = $value['nu_importesaldo'];

				echo "<tr class='$estila'>";

				$ch_cli = trim($value['cli_codigo']);

				echo "<td class='th_cabe_dos' style='background-color: #FFFFFF;border:0'><input id='id_" . trim($value['cli_codigo']) . "' type='checkbox' saldo='" . trim($value['nu_importesaldo']) . "' value='" . trim($value['serie_num']) . "' class='cliente_selecionado'  /></td>";
				echo "<td align='center'>" . $value['tipo'] . " - ". $value['serie_num2'] . "</td>";
				echo "<td align='center'>" . $value['no_tipo_ref'] . " - ". $value['nu_serie_ref'] . " - ". $value['nu_numero_ref']."</td>";
				echo "<td align='center'>" . $value['dt_fechaemision'] . "</td>";
				echo "<td align='center'>" . $value['dt_fechavencimiento'] . "</td>";
				echo "<td align='center'>" . $value['moneda'] . "</td>";
				echo "<td align='right' class='td_tabla_selecinar'>" . number_format($value['nu_importetotal'], 2) . "</td>";
				echo "<td align='right' class='td_tabla_selecinar'>" . number_format($value['nu_importesaldo'], 2) . "</td>";
				echo "<td align='center'>" . $tc . "</td>";
				echo "<td align='center'>" . (($moneda == '01' || $moneda == 01) ? 'S/' : '$') . "</td>";
				echo "<td align='right' class='td_tabla_selecinar'>" . number_format($amount, 2) . "</td>";
			
				echo "</tr>";

				$i++;

		    	}

			?>

			</tbody>

			<tfoot>
				<th class="th_cabe_dos_cabe"></th>
				<th class="th_cabe"><button id="btnselec_elem"><img align="right" src="/sistemaweb/images/search.png"/>Seleccionar</button></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe" align='right'>Total: </th>
				<input type="hidden" value="0" id="idmonto_global"/></th>
				<th class="th_cabe" align="right" id="txt_monto_global">0.00</th>
			</tfoot>

        	</table>

		<?php

	}

	function CrearTablareporte($registros, $nu_almacen) { ?>

        	<!--<div style="float: left;width: auto;border: 1px;color:red;">-->
        	<table cellspacing="1" border="0" style="text-align: left;position: relative;margin: 20px auto;">

            	<thead>
		    	<th class="th_cabe" align="center">Nro. Recibo</th>
		    	<th class="th_cabe" align="center">Fecha</th>
		    	<th class="th_cabe" align="center">Caja</th>
		    	<th class="th_cabe" align="center">Operacion</th>
		    	<th class="th_cabe" align="center">Cliente</th>
		    	<th class="th_cabe" align="center">Referencia</th>
		    	<th class="th_cabe" align="center">Moneda</th>
		    	<th class="th_cabe" align="center">Tasa</th>
		    	<th class="th_cabe" align="center">Monto Documento</th>
		    	<th class="th_cabe" align="center">Monto de Pago</th>
		    	<th colspan="2" class="th_cabe"></th>
        	</thead>

        	<tbody id="registros">
            	<?php

            	$i = 0;

		foreach ($registros as $llave => $value) {

                	$estila = "fila_registro_imppar";

	                if ($i % 2 == 0) {
        	            $estila = "fila_registro_par";
        	        }

		        echo "<tr class='$estila'>";
		        echo "<td align='center'>" . $value['num'] . "</td>";
		        echo "<td align='center'>" . $value['d_system'] . "</td>";
		        echo "<td align='center'>" . $value['caja'] . "</td>";
		        echo "<td align='center'>" . $value['operacion'] . "</td>";
		        echo "<td align='center'>" . $value['cliente'] . "</td>";
		        echo "<td align='center'>" . $value['referencia'] . "</td>";
		        echo "<td align='center'>" . $value['moneda'] . "</td>";
		        echo "<td align='center'>" . $value['rate'] . "</td>";
		        echo "<td align='right'>" . number_format($value['monto'], 2) . "</td>";
		        echo "<td align='right'>" . number_format($value['importe_neto'], 2) . "</td>";
		        echo "<td align='center'><button onclick=verdetalle('" . $value['id_recibo'] . "')>Ver Recibo</button></td>";
		        echo '<td align="center"><button onclick="anular(\'' . $value['id_recibo'] . '\', \'' . $nu_almacen . '\', \'' . $value['d_system'] . '\')">Anular</button></td>';
		        echo "</tr>";

                	$i++;

            	}

            	?>

		</tbody>

        </table>

        <!--</div>-->

        <?php
    }

	function CrearTablaSeleccionarClienteDetalleRecibo($registros, $dat_medio_pago, $dat_banco, $tc, $moneda) {

        ?>

        	<span style="color:#30767F;font-weight: bold;">Detalle de Recibo</span>
        	<table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 5px auto;">	

		   	<thead>
			    	<th class="th_cabe" align="center">Documento</th>
			    	<th class="th_cabe" align="center">Fecha Emision</th>
			    	<th class="th_cabe" align="center">Fecha Vencimiento</th>
			    	<th class="th_cabe" align="center">Moneda</th>
			    	<th class="th_cabe" align="center">Importe</th>
			    	<th class="th_cabe" align="center">Saldo</th>
			    	<th class="th_cabe" align="center">Tasa</th>
			    	<th class="th_cabe" align="center">Moneda</th>
			    	<th class="th_cabe" align="center">Monto a Pagar</th>
			</thead>

			<tbody id="registros">

		    	<?php

		    	$i 		= 0;
		    	$suma 		= 0;
			$importe_pago 	= 0;
			$amount 	= 0;

	  		foreach ($registros as $llave => $value) {

		        	$estila = "fila_registro_imppar";

		        	if ($i % 2 == 0)
		            		$estila = "fila_registro_par";

				if($moneda == '01' && $value['cod_moneda'] == '02')//SI MI CAJA ES EN SOLES Y DOCUMENTO PAGO EN DOLARES
					$amount = ($value['nu_importesaldo'] * $tc);
				elseif($moneda == '02' && $value['cod_moneda'] == '01')//SI MI CAJA ES EN DOLARES Y DOCUMENTO PAGO EN SOLES
					$amount = ($value['nu_importesaldo'] / $tc);
				elseif($moneda == '02' && $value['cod_moneda'] == '02')//SI MI CAJA ES EN DOLARES Y DOCUMENTO PAGO EN DOLARES
					$amount = $value['nu_importesaldo'];
				else
					$amount = $value['nu_importesaldo'];

				if ($value['tipo_emitido'] == '20')
					$suma -= (float)$amount;
				else
					$suma += (float)$amount;

				echo "<tr class='$estila'>";

				$ch_cli = trim($value['cli_codigo']);

				echo "<td align='center'>" . $value['tipo'] . " ". $value['serie_num'] . "</td>";
				echo "<td align='center'>" . $value['dt_fechaemision'] . "</td>";
				echo "<td align='center'>" . $value['dt_fechavencimiento'] . "</td>";
				echo "<td align='center'>" . $value['moneda']. "</td>";
				echo "<td align='right' class='td_tabla_selecinar'>" . number_format($value['nu_importetotal'], 2) . "</td>";
				echo "<td align='right' class='td_tabla_selecinar'>" . number_format($value['nu_importesaldo'], 2) . "</td>";

				echo "<td align='center'>" . $tc. "</td>";
				echo "<td align='center'>" . (($moneda == '01' || $moneda == 01) ? 'S/' : '$') . "</td>";
				echo "<td align='right' class='td_tabla_selecinar'>" . number_format($amount, 2) . "</td>";

				$datos_partidos = str_replace("-", "*", $value['serie_num']);

				echo "<input type='hidden' class='fac_insertar' name='doc_$i' value='" . $value['tipo_emitido'] . "*" . $datos_partidos . "*" . $value['nu_importesaldo'] . "*" . $value['ch_numdocreferencia'] . "*" . $value['cod_moneda'] . "*" . $value['tipo_emitido'] . "' /></td>";
				echo "</tr>";

				$i++;

			}

		    	?>

			</tbody>

			<tfoot>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe">Total: </th>
				<input type="hidden" value="<?php echo $suma ?>" id="idmonto_global"/></th>
				<th class="th_cabe" id="txt_monto_global" style="text-align: right;"><?php echo number_format($suma, 3) ?></th>
			</tfoot>

        	</table>

		<span style="color:#30767F;font-weight: bold;">Detalle de Medio de pago</span>
        	<table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 5px auto;">	

		    	<thead>
			    	<th class="th_cabe">Medio Pago</th>
			    	<th class="th_cabe">Documento</th>
			    	<th class="th_cabe">Fecha</th>
			    	<th class="th_cabe">Banco</th>
			    	<th class="th_cabe">Nro. Cuenta</th>
			    	<th class="th_cabe">Moneda</th>
			    	<th class="th_cabe">Importe</th>
			</thead>

			<tbody id="registros_pymes">
        		</tbody>

        		<tfoot>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe"></th>
				<th class="th_cabe">Total: </th>
				<th class="th_cabe" id="importe_fp_id" value_se='0' style="text-align: right;"></th>
        		</tfoot>

        </table>
        <span style="color:#30767F;font-weight: bold;">Formulario de forma de pago</span>
        <table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 5px auto;">	

            <tbody id="registros">
                <tr>
                    <td>Medio pago</td>
                    <td><select id="cmbmediopago" onchange="ViewAcount(this)">
			<option value='-' selected>Seleccionar...</option>
                            <?php
                            foreach ($dat_medio_pago as $valor) {
                                echo "<option value='$valor[0]' nombre='$valor[1]' validacion='$valor[2]'>$valor[1]</option>";
                            }
                            ?>

                        </select></td>
                </tr>
                <tr>
                    <td>Numero Referencia</td>
                    <td><input type='text' id="txtnum_referencia" class='fecha_formato'/></td>
                </tr>
                <tr>
                    <td>Fecha</td>
                    <td><input type='text' id='txtfecha' class='fecha_formato'/></td>
                </tr>
                <tr class="ayuda_cuentas_id" style="display: none;">
                    <td>Banco</td>
                    <td><select onchange="vercuentasbancarias(this)" id="cmbbanco">
                            <option value="-1">Seleccione ..</option>
                            <?php
                            foreach ($dat_banco as $valor) {
                                echo "<option value='$valor[0]'>$valor[1]</option>";
                            }
                            ?>
                        </select></td>
                </tr>
                <tr class="ayuda_cuentas_id" style="display: none;">
                    <td>Cuenta Bancaria</td>
                    <td><select onchange="vercuentasbancariasMoneda(this)" id="cuentas_cmb_mostrar"></select></td>
                </tr>
                <tr>
                    <td>Moneda</td>
                    <td><select id="txtmostarmoneda"></select></td>
                </tr>
                <tr>
                    <td>Importe</td>
                    <td><input type='text' id='txtimporteG' class='fecha_formato'/></td>
                </tr>
                <tr>
                    <td style="text-align: center;"><button id="guardartmpcliente"><img align="right" src="/sistemaweb/icons/agregar.gif">Agregar Medio Pago</button></td>
                    <td style="text-align: center;"><button  id="finalizarproceso">Finalizar Proceso</button></td>

                </tr>
            </tbody>


        </table>

        <?php
    }

    function MostarMedioPago($dat_medio_pago, $dat_banco) {
        ?>
        <span style="color:#30767F;font-weight: bold;">Detalle de Medio de pago</span>
        <table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 5px auto;">	
            <thead>

            <th class="th_cabe">Medio Pago</th>
            <th class="th_cabe">Numero</th>
            <th class="th_cabe">Fecha</th>
            <th class="th_cabe">Banco</th>
            <th class="th_cabe">Nro. cuenta</th>
            <th class="th_cabe">Moneda</th>
            <th class="th_cabe">Importe</th>

        </thead>
        <tbody id="registros_pymes">

        </tbody>
        <tfoot>
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe">Total: </th>
        <th class="th_cabe" id="importe_fp_id" value_se='0' style="text-align: right;"></th>
        </tfoot>

        </table>
        <span style="color:#30767F;font-weight: bold;">Formulario de forma de pago</span>
        <table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 5px auto;">	

            <tbody id="registros">
                <tr>
                    <td>Medio pago</td>
                    <td><select id="cmbmediopago" onchange="ViewAcount(this)">
			<option value='-' selected>Seleccionar...</option>
                            <?php
                            foreach ($dat_medio_pago as $valor) {
                                echo "<option value='$valor[0]' nombre='$valor[1]' validacion='$valor[2]'>$valor[1]</option>";
                            }
                            ?>

                        </select></td>
                </tr>
                <tr>
                    <td>Numero Referencia</td>
                    <td><input type='text' id="txtnum_referencia" class='fecha_formato'/></td>
                </tr>
                <tr>
                    <td>Fecha</td>
                    <td><input type='text' id='txtfecha' class='fecha_formato'/></td>
                </tr>
                <tr class="ayuda_cuentas_id" style="display: none;">
                    <td>Banco</td>
                    <td><select onchange="vercuentasbancarias(this)" id="cmbbanco">
                            <option value="-1">Seleccione ..</option>
                            <?php
                            foreach ($dat_banco as $valor) {
                                echo "<option value='$valor[0]'>$valor[1]</option>";
                            }
                            ?>
                        </select></td>
                </tr>
                <tr class="ayuda_cuentas_id" style="display: none;">
                    <td>Cuenta Bancaria</td>
                    <td><select onchange="vercuentasbancariasMoneda(this)" id="cuentas_cmb_mostrar"></select></td>
                </tr>
                <tr>
                    <td>Moneda</td>
                    <td><select id="txtmostarmoneda"></select></td>
                </tr>
                <tr>
                    <td>Importe</td>
                    <td><input type='text' id='txtimporteG' class='fecha_formato'/></td>
                </tr>
                <tr>
                    <td style="text-align: center;"><button  id="guardartmpcliente"><img align="right" src="/sistemaweb/icons/agregar.gif">Agregar Medio Pago</button></td>
                    <td style="text-align: center;"><button   id="finalizarproceso">Finalizar Proceso</button></td>

                </tr>
            </tbody>


        </table>

        <?php
    }

    function FormularioCuentaBankaria($data, $dat_banco) {
        ?>

        <div ><h3 style="color: #336699; text-align: center;">REGISTRO DE CUENTA BANCARIO.</h3></div>
        <table style="text-align: left;position: relative;margin: 5px auto;">
            <tr>
                <td>Numero cuenta: </td>
                <td> <input type='text' id='txtnumero_cuenta' /></td>

            </tr>
            <tr>
                <td>Banco: </td>
                <td><select  id="cmbbanco_cliente">
                        <option value="-1">Seleccione ..</option>
                        <?php
                        foreach ($dat_banco as $valor) {
                            echo "<option value='$valor[0]'>$valor[1]</option>";
                        }
                        ?>
                    </select> </td>

            </tr>
            <tr>
                <td>Nombre: </td>
                <td><input type='text' id='txtnombrecuenta' /> </td>

            </tr>
            <tr>
                <td>Iniales: </td>
                <td><input type='text' id='txtinicuenta' /> </td>

            </tr>

            <tr>
                <td colspan="3"><button id="btnguardar_cuenta"><img align="right" src="/sistemaweb/images/search.png"/>Guardar</button> </td>
                <td><button  onclick="irhome()"><img align="right" src="/sistemaweb/icons/atra.gif">Regresar</button> </td>

            </tr>
        </table>


        <table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 5px auto;">	
            <thead>

            <th class="th_cabe">Numero Cuenta</th>
            <th class="th_cabe">Banco</th>
            <th class="th_cabe">Nombre Cliente</th>
            <th class="th_cabe">Iniciales </th>


        </thead>
        <tbody id="registros">
            <?php
            $i = 0;
            $suma = 0;
            foreach ($data as $llave => $value) {
                $estila = "fila_registro_imppar";
                if ($i % 2 == 0) {
                    $estila = "fila_registro_par";
                }
                echo "<tr class='$estila'>";
                echo "<td>" . $value['c_bank_account_id'] . "</td>";
                echo "<td>" . $value['initials'] . "</td>";
                echo "<td>" . $value['name'] . "</td>";
                echo "<td>" . $value['initials_cl'] . "</td>";


                echo "</tr>";
                $i++;
            }
            ?>
        </tbody>


        </table>




        <?php
    }

    function viewtabla_detalle_recibo($data_cabecera, $data_detalle, $data_medios_pago) {
        ?>
        <div ><h3 style="color: #336699; text-align: center;">RECIBO DE INGRESO DE CAJA.</h3></div>

        <table style="text-align: left;position: relative;margin: 10px auto;">
            <tr>
                <td>Sucursal: </td>
                <td> <?php echo $data_cabecera[0]['ware_house'] ?></td>
            </tr>
            <tr>
                <td>Nro. Recibo: </td>
                <td> <?php echo str_pad($data_cabecera[0]['transaction'], 10, '0', STR_PAD_LEFT) ?></td>
            </tr>
            <tr>
                <td>Fecha: </td>
                <td> <?php echo $data_cabecera[0]['d_system'] ?> </td>
            </tr>

            <tr>
                <td>Caja:  </td>
                <td id="idcaja"> <?php echo $data_cabecera[0]['name_caja'] ?></td>
            </tr>

            <tr>
                <td>Operacion:  </td>
                <td id="idoperaion"><?php echo $data_cabecera[0]['name_ope'] ?></td>
            </tr>


            <tr>
                <td>Observacion  </td>
                <td><?php echo $data_cabecera[0]['reference'] ?></td>
            </tr>

            <tr class="ayuda_clientes_id" style="display: none;">
                <td>Ruc cliente  </td>
                <td><?php echo $data_cabecera[0]['cli_razsocial'] ?></td>
            </tr>





        </table>
        <div ><h3 style="color: #336699; text-align: center;">DETALLE RECIBO</h3></div>
        <table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 10px auto;">	
            <thead>
            <th class="th_cabe" align="center">Documento</th>
            <th class="th_cabe" align="center">Referencia</th>
            <th class="th_cabe" align="center">Moneda</th>
            <th class="th_cabe" align="center">Importe</th>
            <th class="th_cabe" align="center">Saldo</th>
        </thead>
        <tbody id="registros">

            	<?php

            	$i = 0;
            	$sumadetalle=(float)0;
		$moneda_document = array();

            	foreach ($data_detalle as $llave => $value) {

                	$estila = "fila_registro_imppar";

                	if ($i % 2 == 0)
                    		$estila = "fila_registro_par";

			if ($value['doc_type'] == '20')
				$sumadetalle -= (float)$value['saldo'];
			else
				$sumadetalle += (float)$value['saldo'];

			$moneda_document[] = $value['moneda'];

		        echo "<tr class='$estila'>"; 
		        echo "<td align='center'>" . $value['document'] . "</td>";
		        echo "<td align='center'>" . $value['reference'] . "</td>";
		        echo "<td align='center'>" . $value['moneda']  . "</td>";
		        echo "<td align='right'>" . number_format($value['amount'],2) . "</td>";
		        echo "<td align='right'>" . number_format($value['saldo'],2) . "</td>";
		        echo "</tr>";

                	$i++;	

            	}

            	?>

        </tbody>
	<tfoot>
        
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe">TOTAL:</th>
        <th class="th_cabe"  style="text-align: right;"><?php echo number_format($sumadetalle,2)?></th>
        </tfoot>

        </table>


	<div ><h3 style="color: #336699; text-align: center;">DETALLE DE MEDIOS DE PAGO</h3></div>
	<table cellspacing="0" cellpadding="0" border="0" style="text-align: left;position: relative;margin: 10px auto;">	
            	<thead>
		    	<th class="th_cabe" align="center">Medio de pago</th>
		    	<th class="th_cabe" align="center">Numero</th>
		    	<th class="th_cabe" align="center">Fecha</th>
		    	<th class="th_cabe" align="center">Banco</th>
		   	<th class="th_cabe" align="center">Nro. Cuenta</th>
		    	<th class="th_cabe" align="center">Moneda</th>
		    	<th class="th_cabe" align="center">Tasa</th>
		    	<th class="th_cabe" align="center">S/ Monto</th>
		    	<th class="th_cabe" align="center">$ Monto</th>
        	</thead>

        	<tbody id="registros">

            	<?php

		$i = 0;
		$sumamediopago=(float)0;
		$spayamount = 0.00;
		$dpayamount = 0.00;

		foreach ($data_medios_pago as $llave => $value) {

		        $estila = "fila_registro_imppar";

		        if ($i % 2 == 0)
		            $estila = "fila_registro_par";

			if($moneda_document[0] == '$' && $value['cod_moneda_pay'] == '1'){
				$spayamount = $value['importe'];
				$dpayamount = ($value['importe'] / $value['rate']);
			}elseif($moneda_document[0] == 'S/' && $value['cod_moneda_pay'] == '2'){
				$spayamount = ($value['importe'] * $value['rate']);
				$dpayamount = ($value['importe']);
			}elseif($moneda_document[0] == '$' && $value['cod_moneda_pay'] == '2'){
				$dpayamount = $value['importe'];
			}elseif($moneda_document[0] == 'S/' && $value['cod_moneda_pay'] == '1'){
				$spayamount = $value['importe'];
			}

		        echo "<tr class='$estila'>";

			$sumamediopago		+=(float)$spayamount;
			$sumamediopagodolares	+=(float)$dpayamount;

		        echo "<td>" . $value['tipo_mp'] . "</td>";
		        echo "<td class='edicion' id_actualizar='".$value['id_pay']."'>" . $value['pay_number'] . "</td>";
		        echo "<td>" . $value['created'] . "</td>";
		        echo "<td>" . $value['banco'] . "</td>";
		        echo "<td>" . $value['cuenta_banco'] . "</td>";
		        echo "<td>" . $value['moneda'] . "</td>";
		        echo "<td align='center'>" . $value['rate']  . "</td>";
		        echo "<td align='right'>" . number_format($spayamount,2) . "</td>";
			echo "<td align='right'>" . number_format($dpayamount,2) . "</td>";
		        echo "</tr>";

			$moneda = $value['moneda'];

		        $i++;

		}

		?>

		</tbody>

        <tfoot>
        <th class="th_cabe"><button id="activar_edicion_id">ACTIVAR EDICION</button></th>
        <th class="th_cabe"><button id="guaradar_edicion_id">GUARDAR</button></th>
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe"></th>
        <th class="th_cabe">Total: </th>
        <th class="th_cabe" style="text-align: right;">S/ <?php echo number_format($sumamediopago,2)?></th>
        <th class="th_cabe" style="text-align: right;">$ <?php echo number_format($sumamediopagodolares,2)?></th>
        </tfoot>

        </table>
        <?php
    }

}
