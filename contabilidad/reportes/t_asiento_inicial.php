<?php
date_default_timezone_set('America/Lima');
class AsientoInicialTemplate extends Template {
	function Inicio($estaciones, $fecha) { ?>
        <div id="template-LibroDiario">
        	<div class="container">
		        <h1 align="center">ASIENTO INICIAL</h1>
		        <br>
	    	</div>

			<div class="columns">
	  			<div class="column">
		            <label class="label">Almacen</label>
	    			<span class="select" style="width: 100%">
					    <select id="cbo-almacen" style="width: 100%">
						    <option value="" selected>Todos</option>
						    <?php
								foreach($estaciones as $value)
									if ($_SESSION['almacen'] == $value['almacen'])
										echo "<option selected value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
									else 
										echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
							?>
					    </select>
				    </span>
	        	</div>

				<div class="column">
		        	<label class="label">SubDiario</label>
		        	<input type="text" class="input" id="txt-subdiario" name="subdiario" autocomplete="on" placeholder="Ingresar SubDiario" value="026" />
		       	</div>

				<div class="column">
		        	<label class="label">Descripcion Subdiario</label>
		        	<input type="text" class="input" id="txt-descripcion-subdiario" name="descripcion-subdiario" autocomplete="on" placeholder="Ingresar descripcion" value="APERTURA" />
		       	</div>

				<div class="column">
					<?php $periodo_apertura = date("Y") + 1; ?>
		        	<label class="label">Periodo Apertura</label>
		        	<input type="text" class="input" id="txt-periodo" name="periodo" autocomplete="on" placeholder="Ingresar periodo a aperturar" value="<?php echo $periodo_apertura; ?>" />
		       	</div>				
		    </div>			

			<div class="columns">
				<div class="column">
					<label class="label">Generar Asientos</label>	        	
		        	<div class="form-check">
						<label class="form-check-label">
							<input type="checkbox" class="form-check-input" id="chk-generar-asientos-apertura" name="generar-asientos-apertura"> ¿Esta seguro de generar Asiento Inicial?
    					</label>
						
						<br>

						<label class="form-check-label">
							<input type="checkbox" class="form-check-input" id="chk-aperturar-periodo" name="cerrar-periodo"> <span id="chk-txt-periodo">¿Desea aperturar el periodo <?php echo "(".$periodo_apertura.")"; ?> indicado?</span> 
						</label>
					</div>

					<label class="label">Eliminar Asientos</label>	        	
		        	<div class="form-check">
						<label class="form-check-label">
							<input type="checkbox" class="form-check-input" id="chk-eliminar-asientos-apertura" name="eliminar-asientos-reversion"> ¿Esta seguro de eliminar Asientos de Iniciales del año <?php echo "(".$periodo_apertura.")"; ?>?
    					</label>
					</div>
		       	</div>			
		    </div>

	        <br/>

	        <div class="columns is-centered">
				<div class="column is-12">
            		<div class="columns is-mobile">
					  	<div class="column is-4">
					  		<button style="width: 100%;" class="button is-info btn-info" id="btn-previsualizar"><i class="fa fa-search icon-size" aria-hidden="true"> <label class="label-btn-name">Previsualizar</label></i></button>
	            		</div>	

						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-primary btn-primary" id="btn-generar"><i class="fa fa-save icon-size" aria-hidden="true"> <label class="label-btn-name">Generar Asiento</label></i></button>
	  					</div>
						
						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-danger btn-danger" id="btn-eliminar"><i class="fa fa-save icon-size" aria-hidden="true"> <label class="label-btn-name">Eliminar Asiento</label></i></button>
	  					</div>
					</div>
				</div>
			</div>
		</div>

		<!--Modal Message Delete-->
		<div class="modal MsgError">
			<div class="modal-content">
				<article class="message">
					<div class="message-header">
						<div class="message-header-text"></div>
						<button class="delete btn-close">
					</div>
					<div class="message-body">
					</div>
				</article>
			</div>
		</div>

		<div class="columns is-desktop" id="div-ProcesoContable_CRUD">
<?php
	}

	function gridView($response) {
		$response = json_decode($response);
		if($response->status != "success"){ ?>
            <div class="column is-12 text-center">
            	<div class="notification is-<?php echo $response->status; ?>"><?php echo $response->message; ?></div>
		    </div>
		<?php
		}else{
		?>
	  		<div class="column is-12 table__wrapper StandardTable">
				<table class="table report_CRUD">
					<thead>
		                <th align="text-center">Fecha</th>
						<th colspan="3" align="text-center">Correlativo de Asiento</th>
						<th colspan="2" align="text-center">Cuenta Contable</th>
		                <th colspan="5" align="text-center">Movimiento</th>
						<th align="text-center">Glosa</th>
						<th align="text-center">Fecha y Hora Reg</th>
				    </thead>
					<thead>
		                <th align="text-center" style="width:6%;"></th>
						<th align="text-center" style="width:4%;">M.</th>
		                <th align="text-center" style="width:4%;">S/D</th>
		                <th align="text-center" style="width:4%;">ASI</th>
						<th align="text-center" style="width:4%;">Cuenta</th>
		                <th align="text-center" style="width:15%;">Descripcion</th>
						<th align="text-center" style="width:4%;">Debe S/.</th>
		                <th align="text-center" style="width:4%;">Haber S/.</th>
						<th align="text-center" style="width:4%;">T/C</th>
						<th align="text-center" style="width:4%;">Debe US$</th>
						<th align="text-center" style="width:4%;">Haber US$</th>
						<th align="text-center" style="width:10%;"></th>
						<th align="text-center" style="width:4%;"></th>
				    </thead>
					<tbody>
					<?php
						// echo "<pre>";
						// print_r($response->entry[0]->act_entryline);
						// echo "</pre>";

						$i = 0;
						foreach ($response->entry[0]->act_entryline as $rows) {	
							$color = ($i%2==0?" grid_detalle_par ":" grid_detalle_impar ");
					?>
							<tr class="grid_detalle_impar <?php echo $color; ?>">
								<td align="center"><?php echo "01/01/".$response->param->Fe_Anio_Apertura; ?></td>
	            				<td align="center"><?php echo "00" ?></td>
	            				<td align="center"><?php echo $response->param->SubDiario; ?></td>
	            				<td align="center"><?php echo "1"; ?></td>
	            				<td align="left"><?php echo $rows->acctcode; ?></td>
	            				<td align="left"><?php echo $rows->name; ?></td>
	            				<td align="right"><?php echo $rows->amtdt; ?></td>
	            				<td align="right"><?php echo $rows->amtct;  ?></td>
	            				<td align="right"><?php echo $response->data_tc->tca_venta_oficial; ?></td>
								<td align="right"><?php echo $rows->amtsourcedt;  ?></td>
								<td align="right"><?php echo $rows->amtsourcect; ?></td>
								<td align="left"><?php echo "ASIENTO INICIAL"; ?></td>
								<td align="center"><?php echo "NOW();"; ?></td>
	            			</tr>
					<?php
							$i++;
						}
					?>
					</tbody>
				</table>
			</div>
		</div>

	<div class="columns is-desktop" id="div-excel">
<?php
		}
	}
}
