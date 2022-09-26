<?php
class LibroDiarioCRUDTemplate extends Template {
	function Inicio($estaciones, $fecha) { ?>
        <div id="template-Vale_Credito">
        	<div class="container">
		        <h1 align="center">Cuenta Contable - Administración</h1>
		        <br>
	    	</div>

			<div class="columns">
	  			<div class="column">
		            <label class="label">Almacen</label>
	    			<span class="select">
					    <select id="cbo-almacen">
						    <option value="" selected>Todos</option>
						    <?php
							foreach($estaciones as $value)
								echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
						    ?>
					    </select>
				    </span>
	        	</div>

				<div class="column">
		        	<label class="label">Código de Cuenta</label>
		        	<input type="text" class="input" id="txt-codigo_cuenta" name="codigo_cuenta" autocomplete="on" placeholder="Ingresar código de cuenta" />
		       	</div>

				<div class="column">
		        	<label class="label">Nombre</label>
		        	<input type="text" class="input" id="txt-nombre_cuenta" name="nombre_cuenta" autocomplete="on" placeholder="Ingresar nombre de cuenta" />
		       	</div>

	  			<div class="column">
		            <label class="label">Nivel</label>
	    			<span class="select">
					    <select id="cbo-nivel">
						    <option value="0" selected>Todos</option>
						    <option value="1">1</option>
						    <option value="2">2</option>
						    <option value="3">3</option>
						    <option value="4">4</option>
						    <option value="5">5</option>
						    <option value="6">6</option>
					    </select>
				    </span>
				</div>
		    </div>

	        <br/>

	        <div class="columns is-centered">
				<div class="column is-12">
            		<div class="columns is-mobile">
					  	<div class="column is-4">
					  		<button style="width: 100%;" class="button is-info btn-info" id="btn-buscar"><i class="fa fa-search icon-size" aria-hidden="true"> <label class="label-btn-name">Buscar</label></i></button>
	            		</div>	

					  	<div class="column is-4">
		            		<button style="width: 100%;" class="button is-success" id="btn-excel"><i class="fa fa-file-excel-o icon-size" aria-hidden="true"> <label class="label-btn-name">Exportar Plan Contable PLE</label></i></button>
	  					</div>

					  	<div class="column is-4">
					  		<button style="width: 100%;" class="button is-primary btn-primary" id="btn-agregar"><i class="fa fa-plus-circle icon-size" aria-hidden="true"> <label class="label-btn-name">Agregar</label></i></button>
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

		<div id="template-Vale_Credito_Agregar">
			<h5 class="title is-5 title-report">Agregar Vales de Crédito</h5>
			<div class="modal MsgDataRVC">
				<div class="modal-content">
					<article class="message">
						<div class="message-header">
							<div class="message-header-text"></div>
							<button class="delete"></button>
						</div>
						<div class="message-body message-status"></div>
					</article>
				</div>
			</div>
			<div class="columns">
		  		<div class="column is-2">
		            <label class="label">Almacen </label>
	    			<span class="select">
					    <select id="cbo-Nu_Almacen" name="cbo-Nu_Almacen" class="combobox">
						    <option value="" selected>Seleccionar..</option>
						    <?php
							foreach($estaciones as $value)
								echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
						    ?>
					    </select>
					    <p class="help"></p>
				    </span>
	        	</div>

		  		<div class="column is-1"></div>

			  	<div class="column is-1_5">
			        <div class="field">
		        		<label class="label">F. Emisión </label>
						<p class="control has-icon">
		        			<input type="text" class="input required" id="txt-Fe_Emision" name="Fe_Emision" value="" /><span class="icon is-small">
						    	<i class="fa fa-calendar"></i>
						    </span>
					  	</p>
					</div>
		        </div>

				<div class="column is-1_5">
			        <div class="field">
		        		<label class="label"># Ticket </label>
						<p class="control has-icon">
			        		<input type="text" class="input required" id="txt-Ch_Documento" name="Ch_Documento" maxlength="20" autocomplete="off" placeholder="Ingresar numero ticket" />
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

				<div class="column is-1_5">
			        <div class="field">
		        		<label class="label"># Manual </label>
						<p class="control has-icon">
			        		<input type="text" class="input" id="txt-Ch_Documento_Manual" name="Ch_Documento_Manual" maxlength="20" autocomplete="off" placeholder="Ingresar vale manual" />
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

		  		<div class="column is-0_3"></div>

		  		<div class="column cbo-Tipo_Venta">
		            <label class="label">Tipo Venta </label>
	    			<span class="select">
					    <select id="cbo-no_tipo_venta" class="combobox" name="cbo-no_tipo_venta">
					    </select>
					</span>
				</div>
		  		<div class="column">
		            <label class="label">Turno </label>
						<span class="select eliminar_cbo-Nu_Turno">
							<select id="cbo-Nu_Turno" class="combobox required" name="cbo-Nu_Turno">
							</select>
							<p class="help"></p>
						</span>
				</div>

		  		<div class="column">
		            <label class="label">Caja </label>
						<span class="select eliminar_cbo-Nu_Caja">
							<select id="cbo-Nu_Caja" class="combobox required" name="cbo-Nu_Caja">
							</select>
							<p class="help"></p>
						</span>
					</div>

		  		<div class="column cbo-Nu_Lado">
		            <label class="label">Lado </label>
						<span class="select eliminar_cbo-Nu_Lado">
							<select id="cbo-Nu_Lado" class="combobox" name="cbo-Nu_Lado">
							</select>
							<p class="help"></p>
						</span>
				</div>

				<input type="hidden" id="opensoftCentral" value=""></input>
	        </div>

	        <div class="columns">
			  	<div class="column is-4">
			        <div class="field">
				        <label class="label">Cliente </label>
						<p class="control has-icon">
				        	<input type="hidden" class="input required" id="txt-Nu_Documento_Identidad" name="Nu_Documento_Identidad" />
				        	<input type="text" class="input required" id="txt-No_Razsocial" name="No_Razsocial" autocomplete="off" placeholder="Ingresar Codigo o Nombre del Cliente" />
						    <span class="icon is-small">
						    	<i class="fa fa-user"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
				</div>

	        	<div class="column is-1_5">
			        <div class="field">
		        		<label class="label"># Placa </label>
						<p class="control has-icon">
		        			<input type="text" class="input required" id="txt-No_Placa" name="No_Placa" autocomplete="off" placeholder="Ingresar Placa" />
		        			<span class="icon is-small">
						    	<i class="fa fa-car"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
		        	</div>
		        </div>



			  	<div class="column">
			        <div class="field">
		        		<label class="label"># Tarjeta </label>
						<p class="control has-icon">
				        	<input type="text" class="input required" id="txt-Nu_Tarjeta" name="Nu_Tarjeta" autocomplete="off" placeholder="Ingresar # Tarjeta" />
				        	<span class="icon is-small">
						    	<i class="fa fa-credit-card"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
		        	</div>
		        </div>

			  	<div class="column is-2">
			        <div class="field">
		        		<label class="label"># DOC. IDEN.</label>
						<p class="control has-icon">
			        		<input type="text" class="input input-number" id="txt-Nu_Documento_Identidad_Chofer" name="Nu_Documento_Identidad_Chofer" autocomplete="off" placeholder="Ingresar # D.I. Chofer" maxlength="16" />
			        		<span class="icon is-small">
							    	<i class="fa fa-credit-card"></i>
						    </span>
					  	</p>
					</div>
		        </div>

			  	<div class="column">
			        <div class="field">
			        	<label class="label">Nombre Chofer</label>
						<p class="control has-icon">
				        	<input type="text" class="input" id="txt-No_Chofer" name="No_Chofer" autocomplete="off" placeholder="Ingresar Chofer" disabled="" />
				        	<span class="icon is-small">
							   	<i class="fa fa-user"></i>
						    </span>
					  	</p>
					</div>
		        </div>

			  	<div class="column is-1_5">
			        <div class="field">
		        		<label class="label">Odometro</label>
						<p class="control has-icon">
			        		<input type="text" class="input" id="txt-Nu_Odometro" name="Nu_Odometro" autocomplete="off" placeholder="Ingresar Odometro" />
			        		<span class="icon is-small">
							    	<i class="fa fa-car"></i>
						    </span>
					  	</p>
					</div>
		        </div>
			</div>

        	<div class="columns">
			  	<div class="column is-6">
			        <div class="field">
		        		<label class="label">Nombre Producto </label>
			        	<p class="control has-icon">			        		
				        	<input type="hidden" class="input" id="txt-Nu_Id_Producto" name="Nu_Id_Producto" >
				        	<input type="text" class="input" id="txt-No_Producto_Detalle" name="No_Producto_Detalle" autocomplete="off" placeholder="Ingresar Codigo o Nombre del Producto" />
			        		<span class="icon is-small">
							    <i class="fa fa-shopping-cart"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

		        <div class="column">
			        <div class="field">
			        	<label class="label">Cantidad </label>
			        	<p class="control has-icon">
				        	<input type="text" class="input" id="txt-Nu_Cantidad" name="Nu_Cantidad" autocomplete="off" placeholder="Ingresar cantidad" />
				        	<span class="icon is-small">
							    <i class="fa fa-plus-square"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

		        <div class="column">
			        <div class="field">
			        	<label class="label">Precio </label>
			        	<p class="control has-icon">
				        	<input type="text" class="input" id="txt-Nu_Precio" name="Nu_Precio" autocomplete="off" placeholder="Ingresar precio" />
				        	<span class="icon is-small">
							    <i class="fa fa-money"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

		        <div class="column">
			        <div class="field">
			        	<label class="label">Total </label>
			        	<p class="control has-icon">
				        	<input type="text" class="input" id="txt-Nu_Total" name="Nu_Total" autocomplete="off"/>
				        	<span class="icon is-small">
							    <i class="fa fa-money"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

                <div class="column is-2">
			        <div class="field">
			        	<label class="label">&nbsp;</label>
			        		<p class="control has-icon">
								<button style="width: 100%;" class="button is-primary btn-primary" id="btn-add-product_detail"><i class="fa fa-plus-circle icon-size" aria-hidden="true"> <label class="label-btn-name">Agregar item</label></i></button>
							</p>
						</label>
					</div>
			    </div><!-- ./ is-2 -->
	        </div><!-- Add item -->

            <div class="columns">
                <div class="column is-12">
					<div id="div-credit_detail" class="table__wrapper StandardTable">
			            <table id="table-credit_detail" class="table report_CRUD">
			            	<thead>
				                <tr>
				                	<th class="text-center">Arículo</th>
				                	<th class="text-center">Cantidad</th>
				                	<th class="text-center">Precio</th>
				                	<th class="text-center">Total</th>
				               	</tr>
				            </thead>
				            <tbody>
				            </tbody>
				        </table>
				    </div>
				</div>
			</div><!-- /. Table Credit Temporal -->

            <div class="columns">
			  	<div class="column is-6">
			  		<button style="width: 100%;" class="button is-danger btn-close is-large" id="btn-close"><i class="fa fa-sign-out icon-size" aria-hidden="true"> <label class="label-btn-name">Regresar</label></i></button>
				</div>

				<div class="column is-6">
			  		<button type="button" style="width: 100%;" class="button is-primary btn-primary is-large" id="btn-save"><i class="fa fa-save icon-size" aria-hidden="true"> <label class="label-btn-name">Guardar</label></i></button>
				</div>
			</div><!-- ./ Row button -->
		</div>

		<div class="columns is-desktop" id="div-Vale_CRUD">
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
		                <th align="text-center">Almacen</th>
		                <th align="text-center">F. Sistema</th>
		                <th align="text-center">R.U.C.</th>
		                <th align="text-center">Razon Social</th>
		                <th align="text-center">Ticket</th>
		                <th align="text-center">Número Manual</th>
		                <th align="text-center">Importe</th>
		                <th align="text-center">Tarjeta</th>
		                <th align="text-center">Placa</th>
		                <th align="text-center">Chofer</th>
		                <th align="text-center">Odometro</th>
		                <th align="text-center">Modificado</th>
		                <th colspan="2" align="text-center">Acciones</th>
				    </thead>
					<tbody>
					<?php
						$i = 0;
						$sButtonModificado = 'No';
						$sMessageModificado = '';
						$sClassTooltip = '';
						$sClassButton = 'is-link';
						foreach ($response->data->rows as $rows) {
							$color = ($i%2==0?" grid_detalle_par ":" grid_detalle_impar ");
							$sButtonModificado = 'No';
							$sMessageModificado = '';
							$sClassTooltip = '';
							$sClassButton = 'is-info';
							if ( !empty($rows->ch_usuario) && !empty($rows->ch_auditorpc) && !empty($rows->dt_fechaactualizacion) ) {
								$sButtonModificado = 'Si';
								$sMessageModificado = 'Usuario: ' . $rows->ch_usuario . ' - IP: ' . $rows->ch_auditorpc . ' - Fecha: ' . $rows->dt_fechaactualizacion;
								$sClassTooltip = 'tooltip is-tooltip-multiline';
								$sClassButton = 'is-danger';
							}
					?>
							<tr class="grid_detalle_impar <?php echo $color; ?>">
	            				<td align="left"><?php echo $rows->no_almacen; ?></td>
	            				<td align="center"><?php echo $rows->fe_sistema; ?></td>
	            				<td align="center"><?php echo $rows->nu_documento_idenidad; ?></td>
	            				<td align="left"><?php echo $rows->no_razon_social; ?></td>
	            				<td align="center"><?php echo $rows->ch_documento; ?></td>
	            				<td align="center"><?php echo $rows->nu_vale_manual; ?></td>
	            				<td align="right"><?php echo number_format($rows->nu_importe, 2, '.', ','); ?></td>
	            				<td align="center"><?php echo $rows->ch_tarjeta; ?></td>
	            				<td align="center"><?php echo $rows->ch_placa; ?></td>
	            				<td align="center"><?php echo $rows->no_chofer; ?></td>
	            				<td align="center"><?php echo $rows->nu_odometro; ?></td>
	            				<td align="center">
	            					<button class="button <?php echo $sClassButton . ' ' . $sClassTooltip; ?>" data-tooltip="<?php echo $sMessageModificado; ?>"><?php echo $sButtonModificado; ?></button>
	            				</td>
	            				<td align="center">
		                			<a class="button is-info is-small btn-info" title="Editar" onclick="edit_vale('<?php echo $rows->nu_almacen;?>', '<?php echo $rows->fe_sistema;?>', '<?php echo $rows->ch_documento;?>', '<?php echo $rows->nu_turno;?>', '<?php echo $rows->nu_lado;?>')"><span class="icon is-small"><i class="fa fa-pencil-square-o"></i></span></a>
		            			</td>
		            			<td align="center">
		            				<a class="button is-danger is-small btn-danger btn-eliminar_vale" title="Eliminar" onclick="delete_vale('<?php echo $rows->nu_almacen;?>', '<?php echo $rows->fe_sistema;?>', '<?php echo $rows->ch_documento;?>', '<?php echo $rows->nu_turno;?>')"><span class="icon is-small"><i class="fa fa-trash"></i></span></a>
		            			</td>
	            			</tr>
					<?php
							$i++;
						}
					?>
					</tbody>
				</table>
				<!--Pagination-->
    			<script type="text/javascript" src="/sistemaweb/assets/js/paginador/paginador.js"></script>
	            <input type="hidden" id="pageActual" value="<?php echo $response->data->page ?>">
	            <input type="hidden" id="cantidadPage" value="<?php echo $response->data->total ?>"><!--Cantidad de Paginas -->
				<nav class="bulma pagination is-centered">
					<a class="bulma pagination-previous pagination-previousRVC"><<</a>
					<a class="bulma pagination-next pagination-nextRVC">>></a>
					<ul class="bulma pagination-list">
						<?php
						for ($i=1; $i <= $response->data->total; $i++) {
							if($i >= $response->data->page - $response->data->Pagelimit && $i <= $response->data->page + $response->data->Pagelimit) {
						?>
					    <li>
					    	<a href="#" class="bulma pagination-link pagination-linkRVC <?php echo ($i == $response->data->page ? 'is-current' : '') ?>" data-page="<?php echo $i ?>">
					    		<?php echo $i ?>
					    	</a>
					    </li>
					    <?php
							}
						}
						?>
					</ul>
				</nav>
			</div>
		</div>

	<div class="columns is-desktop" id="div-excel">
<?php
		}
	}

	function gridViewExcel($response) {
		$response = json_decode($response);

		if($response->status != "success"){ ?>
            <div class="column is-12 text-center">
            	<div class="notification is-<?php echo $response->status; ?>"><?php echo $response->message; ?></div>
		    </div>
		<?php
		} else { ?>

			<!-- Excel -->
	  		<div class="column is-12">
				<table class="bulma table">
					<thead>
						<tr>
			                <th align="text-center">Almacen</th>
			                <th align="text-center">F. Sistema</th>
			                <th align="text-center">R.U.C.</th>
			                <th align="text-center">Razon Social</th>
			                <th align="text-center">Ticket</th>
			                <th align="text-center">Número Manual</th>
			                <th align="text-center">Importe</th>
			                <th align="text-center">Tarjeta</th>
			                <th align="text-center">Placa</th>
			                <th align="text-center">Chofer</th>
			                <th align="text-center">Odometro</th>
			            </tr>
				    </thead>
					<tbody>
					<?php
						$i = 0;
						foreach ($response->data as $rows) {
							$color = ($i%2==0?" grid_detalle_par ":" grid_detalle_impar ");
					?>
							<tr class="grid_detalle_impar <?php echo $color; ?>">
	            				<td align="left"><?php echo $rows->no_almacen; ?></td>
	            				<td align="center"><?php echo $rows->fe_sistema; ?></td>
	            				<td align="center"><?php echo $rows->nu_documento_idenidad; ?></td>
	            				<td align="left"><?php echo $rows->no_razon_social; ?></td>
	            				<td align="center"><?php echo $rows->ch_documento; ?></td>
	            				<td align="center"><?php echo $rows->nu_vale_manual; ?></td>
	            				<td align="right"><?php echo number_format($rows->nu_importe, 2, '.', ','); ?></td>
	            				<td align="center"><?php echo $rows->ch_tarjeta; ?></td>
	            				<td align="center"><?php echo $rows->ch_placa; ?></td>
	            				<td align="center"><?php echo $rows->no_chofer; ?></td>
	            				<td align="center"><?php echo $rows->nu_odometro; ?></td>
	            			</tr>
					<?php
							$i++;
						}
					?>
					</tbody>
				</table>
			</div>
		<?php
		}
	}
}
