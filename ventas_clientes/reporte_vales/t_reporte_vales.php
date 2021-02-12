<?php
class ReporteValesTemplate extends Template {
	function FormularioPrincipal() { ?>
<div class='contenedorprincipal'>
	<div>
		<h3 style="color: #336699; text-align: center;">Reporte de vales diarios x CC</h3>
	</div>
	<div class="separacion" style="width: 80%">
		<div class='fila'>
			<div class='etiquetavales' style="float: left;">Fecha Inicio: </div>
			<div style="float: left;"><input type='text' id='fecha_inicio' class='fecha_formato'/></div>
		</div>
		<div class='fila'>
			<div class='etiquetavales' style="float: left;">Fecha Final: </div>
			<div style="float: left;"><input type='text' id='fecha_final' class='fecha_formato'/></div>
		</div>
        
		<div class='fila'>
			<div class='etiquetavales' style="float: left;">Cliente: </div>
			<div style="float: left;">
        		<input type="hidden" id="txt-Nu_Documento_Identidad" />
	        	<input type="text" maxlength="50" size="32" id="txt-No_Razsocial" name="No_Razsocial" autocomplete="off" placeholder="Ingresar Codigo o Nombre del Cliente" />
            </div>
		</div>
		<div class='fila'>
			<div class='etiquetavales' style="float: left;">Tipo calculo: </div>
			<div style="float: left;">
				<select id="tipo_cal">
					<option value="02" selected>Precio</option>
					<option value="01">Costo</option>
				</select>
			</div>
		</div>
		<div class='fila'>
			<div class='etiquetavales' style="float: left;">Tipo Cliente:</div>
			<div style="float: left;">
			    <select id="cbo-tipo-cliente">
				    <option value="T">Todos</option>
				    <option value="0">Efectivo</option>
				    <option value="1">Crédito</option>
				    <option value="2">Anticipo</option>
			    </select>
			</div>
		</div>
		<div class='fila'>
			<div class='etiquetavales' style="float: left;">Decimales:</div>
			<div style="float: left;">
			    <select id="cbo-decimales">
				    <option value="4" selected="selected">4</option>
				    <option value="2">2</option>
			    </select>
			</div>
		</div>
		<div class='fila'>
			<div class='etiquetavales' style="float: left;">Ordenar por:</div>
			<div style="float: left;">
			    <select id="cbo-ordenar-por">
				    <option value="0" selected="selected">Placa, fecha, documento</option>
				    <option value="1">Fecha, placa, documento</option>
					<option value="2">Documento, fecha, placa</option>
			    </select>
			</div>
		</div>
	</div>
	<div  class="separacion" style="width: 80%">
		<div class='etiquetavales' style="float: left;margin-bottom: 5px;text-align: center;width: auto;"><button id="btnseleccionar"><img align="right" src="/sistemaweb/images/search.png"/>Buscar vales</button></div>
		<div class='etiquetavales' style="float: left;margin-bottom: 2px;margin-right: 2px;text-align: center;width: auto;"><button ><img align="right" src="/sistemaweb/images/search.png">Cancelar</button></div>
	</div>
</div>
<div class='contenedorprincipaltabla' id="contenidoTablaSelecionar"></div>

<?php
	}

	function CrearTablaSeleccionarCliente($registros, $cliente, $tipo_cal, $rason_z, $sTipoCliente, $iDecimales) {
		$array_prod = array(
			"11620301" => "GASOHOL  84 ",
			"11620302" => "GASOHOL  90",
			"11620303" => "GASOHOL  97",
			"11620304" => "DIESEL B5 UV",
			"11620305" => "GASOHOL  95",
			"11620306" => "DIESEL B5 S50",
			"11620307" => "GLP"
		);

		$totalescliente = array(
			"11620301" => array("V" => 0, "I" => 0),
			"11620302" => array("V" => 0, "I" => 0),
			"11620303" => array("V" => 0, "I" => 0),
			"11620304" => array("V" => 0, "I" => 0),
			"11620305" => array("V" => 0, "I" => 0),
			"11620306" => array("V" => 0, "I" => 0),
			"11620307" => array("V" => 0, "I" => 0)
		);
?>

<!--<div style="float: left;width: auto;border: 1px;color:red;">-->
<div align="center">
	<table border="0" align="center" class="report_CRUD">
		<thead>
			<th class="th_cabe">Almacen</th>
			<th class="th_cabe">Placa</th>
			<th class="th_cabe">Num Despacho</th>
			<th class="th_cabe">Fecha Consumo</th>
			<th class="th_cabe">Producto</th>
			<th class="th_cabe">Precio</th>
			<th class="th_cabe">Cantidad</th>
			<th class="th_cabe">Importe</th>
		</thead>
		<tbody>
			<?php
			echo "<tr><td colspan='8' style='font-weight: bold;font-size: 13px;'>Cliente " . $sTipoCliente . ": " . $cliente . " - " . $rason_z . "</td></tr>";
			$i = 0;
			$tran_codigo = '25';

			foreach ($registros as $llave => $value) {
				$estila = "grid_detalle_impar";
				if ($i % 2 == 0) {
					$estila = "grid_detalle_par";
				}

				echo "<tr class='" . $estila . "'>";
				$ch_cli = trim($value['ch_cliente']);
				echo "<td class='td_tabla_selecinar' style='text-align: center'>" . $value['desalmacen'] . "</td>";
				echo "<td class='td_tabla_selecinar' style='text-align: center'>" . $value['ch_placa'] . "</td>";
				echo "<td class='td_tabla_selecinar' style='text-align: center'>" . $value['ch_documento'] . "</td>";
				echo "<td class='td_tabla_selecinar' style='text-align: center'>" . $value['dt_fecha'] . "</td>";
				echo "<td class='td_tabla_selecinar' style='width:240px !important; text-align: left'>" . $value['ch_articulo'] . "</td>";

				$ch_articulo = trim($value['ch_articulo']);
        		$arrItem = explode(' ', $ch_articulo);
        		$sCodeItem = trim($arrItem[0]);
				$ch_documento = trim($value['ch_documento']);
				$importe_cliente = 0.0;
				
				foreach ($array_prod as $key => $producto) {
					if ($sCodeItem == $key) {
						if ($tipo_cal == "01") {
							$precio = ReporteValesModel::Mostrarprecio_promedio($value['dt_fecha'], $tran_codigo, $sCodeItem, $ch_documento);
							$precio_pro = $precio[0]['mov_costounitario'];
							$importe_cliente = $value['nu_cantidad'] * $precio_pro;
							echo "<td class='td_tabla_selecinar'>" . number_format($precio_pro, $iDecimales) . "</td>";
							echo "<td class='td_tabla_selecinar'>" . number_format($value['nu_cantidad'], $iDecimales) . "</td>";
							echo "<td class='td_tabla_selecinar'>" . number_format($importe_cliente, $iDecimales) . "</td>";
						} else {
							$importe_cliente = $value['nu_importe'];
							echo "<td class='td_tabla_selecinar'>" . number_format(($value['nu_importe'] / $value['nu_cantidad']), $iDecimales) . "</td>";
							echo "<td class='td_tabla_selecinar'>" . number_format($value['nu_cantidad'], $iDecimales) . "</td>";
							echo "<td class='td_tabla_selecinar'>" . number_format($importe_cliente, $iDecimales) . "</td>";
						}
					}
				}

				$totalescliente[$ch_articulo]['I']+=(double) $importe_cliente;
				$totalescliente[$ch_articulo]['V']+=$value['nu_cantidad'];

				echo "</tr>";
				$i++;
			}
			?>
		</tbody>
		<tfoot>
			<?php
			echo "<tr style='font-size: 13px;'><td></td></tr>";
			echo "<tr style='font-size: 13px;'><td></td><td></td><td></td><td></td><td style='font-weight: bold;font-size: 13px;' align='right'>DESCRIPCION </td><td></td><td style='font-weight: bold;font-size: 13px;' class='td_tabla_selecinar'>CANTIDAD</td> <td style='font-weight: bold;font-size: 13px;' class='td_tabla_selecinar'> IMPORTE </td></tr>";
			$impo_global = 0.00;
			$volu_global = 0.00;
			foreach ($totalescliente as $k => $data) {
				if ($data['V'] > 0) {
					$cod_prod = ReporteValesModel::descripcion($k);
					$des = $cod_prod['art_descripcion'];
					if($des==null && empty($des)){
						$des = $k;
					}
					//echo "<tr><td></td><td></td><td></td><td></td><td align='right' style='font-size: 11px;'>" . $des . " </td><td></td><td style='font-size: 11px;'  class='td_tabla_selecinar'>" . number_format($data['V'], 4) . "</td><td style='font-size: 11px;' class='td_tabla_selecinar'>" . number_format($data['I'], 4) . "</td></tr>";
					echo "<tr><td></td><td></td><td></td><td></td><td align='right' style='font-size: 11px;'>" . $des . " </td><td></td><td style='font-size: 11px;'  class='td_tabla_selecinar'>" . number_format($data['V'], $iDecimales, '.', '') . "</td><td style='font-size: 11px;' class='td_tabla_selecinar'>" . number_format($data['I'], $iDecimales, '.', '') . "</td></tr>";
					//$impo_global+=(double) $data['I'];
					//$volu_global+=(double) $data['V'];
					$impo_global+=(double) number_format($data['I'], $iDecimales, '.', '');
					$volu_global+=(double) number_format($data['V'], $iDecimales, '.', '');
				}
			}
			echo "<tr style='font-size: 13px;'><td></td><td></td><td></td><td></td><td style='font-weight: bold;font-size: 13px;' align='right'>TOTAL DE VENTA </td><td></td><td style='font-weight: bold;font-size: 13px;' class='td_tabla_selecinar'>" . number_format($volu_global, $iDecimales) . "</td> <td style='font-weight: bold;font-size: 13px;' class='td_tabla_selecinar'> " . number_format($impo_global, $iDecimales) . " </td></tr><br>";
			?>
		</tfoot>
	</table>
</div>
<?php
	}
}
