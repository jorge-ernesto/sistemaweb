<?php
//
class Siscont_Template extends Template {

	function Inicio($estaciones, $dataTarjetasCredito) { ?>
        <div align="center" id="Inicio">
			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Interface SISCONT<b></h2>
			<table border="0" cellspacing="5" cellpadding="5">
				<tr>
					<td align="right">Almacen: </td>
					<td>
						<select id="sucursal" name="sucursal">
							<?php
							foreach($estaciones as $value){
								echo "<option value='" . $value['almacen'] . "'>" . $value['nomalmacen'] . "</option>";
							}
							?>
						</select>
					</td>
				</tr>

				<tr>
		        	<td align="right">M&oacute;dulos: </td>
			    	<td>
						<select id="modulos" name="modulos">
							<option value="1">Ventas</option>
							<option value="2">Cobranzas</option>
						</select>
					</td>
				</tr>

				<tr>
		        	<td align="right">Año: </td>
					<td>
						<select id="year" name="year"></select>
						 Mes: 
						<select id="month" name="month">
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
		        	<td align="right">Formato Deciamles: </td>
			    	<td>
						<select id="CboDecimales" name="CboDecimales">
							<option value="2">2</option>
							<option value="4">4</option>
						</select>
					</td>
				</tr>

				<tr>
		        	<td align="right">Tipo venta: </td>
			    	<td>
						<select id="cbo-tipo-venta" name="cbo-tipo-venta">
							<option value="0">Seleccionar...</option>
							<option value="1">Documentos Electrónicos</option>
							<option value="2">Documentos Electrónicos y Tickets</option>
							<option value="3">Tickets y Documentos Manuales</option>
						</select>
						<span class="span-msg" style="color: red"></span>
					</td>
				</tr>

				<tr>
		        	<td align="right">Considerar Notas de Despacho</td>
			    	<td>
						<select id="cbo-nota-despacho" name="cbo-nota-despacho">
							<option value="1">No</option>
							<option value="2">Si</option>
						</select>
					</td>
				</tr>

				<tr>
		        	<td align="right">Excluir Tarjeta Credito</td>
			    	<td>
						<select id="cbo-tarjeta-credito" name="cbo-tarjeta-credito">
							<option value="000000">Seleccionar...</option>
							<?php
							foreach($dataTarjetasCredito as $value){
								echo "<option value='" . $value['tab_descripcion'] . "'>" . $value['tab_descripcion'] . "</option>";															
							}	
							?>						
						</select>
					</td>
				</tr>

				<tr>
		           	<td colspan="2" align="center">
						<button id="asientos"><img src="/sistemaweb/icons/gbook.png" align="right" />Generar Asientos Texto</button>
						&nbsp;&nbsp;&nbsp;<button id="btn-excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Generar Asientos Excel</button>
					</td>
				</tr>
			</table>
		</div>
        <div  align="center" id="tab_id_detalle"></div>
	<?php
	}
}

