<?php

class Descuentos_Especiales_Template extends Template {

	function Inicio($estaciones, $desde, $hasta) { ?>

        	<div align="center" id="Inicio">

			<h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Interface Opensoft --> CLUB ESMERALDA<b></h2>

			<table border="0" cellspacing="5" cellpadding="5">
				<tr>
		        		<td align="right">M&oacute;dulos: </td>
					    	<td>
						    <select id="modulos" name="modulos">
							    <option value="TODOS">[ Todos ]</option>
						    </select>
					</td>
				</tr>

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
		        		<td align="right">A&ntildeo: </td>
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
		                	<td colspan="2" align="center">
						<button id="excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>
					</td>
		            	</tr>

			</table>
		</div>
                <div  align="center" id="tab_id_detalle">
		</div>

	<?php
	}

}
