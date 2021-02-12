<?php
class Regenerar_Saldos_Template extends Template {
	function Inicio($cierre, $estaciones) { ?>
        <div align="center">
		<h2 align="center" style="color:#336699;"><b>Regeneración de Saldos<b></h2>
		<table border="0">
			<tr>
				<td align="right">
					<p style="font-size:1.2em; color:black;">Almacén: 
				</td>
				<td>
					<select id="cbo-iWarehouse">
					    <?php
						foreach($estaciones as $value)
							echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
					    ?>
					</select>
				</td>
			</tr>
			<tr>
				<td align="right">
					<p style="font-size:1.2em; color:black;">Inventario Cerrado en el  
				</td>
				<td align="left">
					<b>
						<p style="font-size:1.2em; color:black;">Año:
						<?php
						$mes = $cierre[0]['mes'];
						$anio = $cierre[0]['anio'];

						if($mes < 12){
							$mes = $mes + 1;
						} else {
						    $mes = 1;
						    $anio = $anio + 1;	
						}
						if($mes>0 and $mes<10) $mes = "0".$mes;
						echo $anio; ?>
					</b>
					<input type="hidden" name="hidden-iYear" id="hidden-iYear" value="<?php echo $anio; ?>" />
			        <b>Mes: <?php echo $mes; ?></b>
					<input type="hidden" name="hidden-iMonth" id="hidden-iMonth" value="<?php echo $mes; ?>" />
				</td>
			</tr>	
			<tr>
				<td align="center" colspan="2">
					<button id="btn-procesar"><img src="/sistemaweb/images/MasterDetail.gif" align="right" />Procesar Regeneración &nbsp;</button>
				</td>
			</tr>

			<tr>
				<td align="center" colspan="2">&nbsp;</td>
			</tr>

			<tr>
				<td align="center" colspan="2">
					<div class="is-notification"></div>
				</td>
			</tr>

			<tr>
				<td align="center" colspan="2">
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
	} // /. Inicio
} // /. Class Regenerar_Saldos_Template