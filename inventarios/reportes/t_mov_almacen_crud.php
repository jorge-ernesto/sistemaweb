<?php
class MovimientoAlmacenCRUDTemplate extends Template { 
	function Inicio($estaciones, $Fe_Sistema, $TipoMovimientoInventario, $TipoDocumentosReferencia, $Nu_IGV, $Cierre_Inventario, $Fe_Sistema_Inicio, $Fe_Inicio, $Fe_Fin, $Nu_Tipo_Movimiento_Inventario, $flg, $save) { ?>
        <div id="template-Movimiento_Inventario">
			<div class="container">
		        <h1 align="center"><?php echo ucwords(strtolower($TipoMovimientoInventario[0]['no_tipo_movimiento_inventario'])); ?></h1>
		        <br>
	    	</div>
	    	
			<input type="hidden" class="input" id="txt-Nu_Tipo_Movimiento_Inventario" name="Nu_Tipo_Movimiento_Inventario" value="<?php echo $Nu_Tipo_Movimiento_Inventario; ?>" />
		  	<input type="hidden" class="input" id="txt-flg" name="flg" value="<?php echo $flg; ?>" />
			
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
   		        	<label class="label">F. Inicio</label>
		        	<input type="text" class="input" id="txt-fe_inicial" name="fe_inicial" autocomplete="off" value="<?php echo (empty($_REQUEST['fe_inicial']) ? $Fe_Inicio : $_REQUEST['fe_inicial']); ?>" />
		        </div>

			  	<div class="column">
		        	<label class="label">F. Final</label>
		        	<input type="text" class="input" id="txt-fe_final" name="fe_final" autocomplete="off" value="<?php echo (empty($_REQUEST['fe_final']) ? $Fe_Fin : $_REQUEST['fe_final']); ?>" />
		        </div>

	  			<div class="column">
		        	<label class="label">(Serie / Numero)</label>
		        	<input type="text" class="input mayuscula input-number_letter" id="txt-Nu_Documento" name="Nu_Documento" autocomplete="on" placeholder="Ingresar serie o numero" />
		        </div>

	  			<div class="column">
	        		<label class="label">Producto</label>
		        	<input type="text" class="input" id="txt-No_Producto" name="No_Producto" autocomplete="off" placeholder="Ingresar Código o Nombre" />
				</div>
		    </div>

	        <br>

	        <div class="columns is-centered">
				<div class="column is-12">
            		<div class="columns is-mobile">
					  	<div class="column is-4">
					  		<button style="width: 100%;" class="button is-info btn-info" id="btn-buscar"><i class="fa fa-search icon-size" aria-hidden="true"> <label class="label-btn-name">Buscar</label></i></button>
						</div>

					  	<div class="column is-4">
					  		<button style="width: 100%;" class="button is-success" id="btn-excel"><i class="fa fa-file-excel-o icon-size" aria-hidden="true"> <label class="label-btn-name">Excel</label></i></button>
						</div>

						<div class="column is-4">
					  		<button style="width: 100%;" class="button is-primary btn-primary" id="btn-agregar"><i class="fa fa-plus-circle icon-size" aria-hidden="true"> <label class="label-btn-name">Agregar</label></i></button>
						</div>
					</div><!-- ./ Row button -->
            	</div>
            </div>
		</div>

		<div id="template-Movimiento_Inventario_Agregar">
			<?php
			$Nu_Almacen_Interno = 0;
			$Nu_Almacen_Interno = ($TipoMovimientoInventario[0]['nu_naturaleza_movimiento_inventario'] == 1 || $TipoMovimientoInventario[0]['nu_naturaleza_movimiento_inventario'] == 2 ? $TipoMovimientoInventario[0]['nu_almacen_destino'] : $TipoMovimientoInventario[0]['nu_almacen_origen']);
			?>

			<input type="hidden" class="input" id="txt-Nu_Almacen_Interno" name="Nu_Almacen_Interno" value="<?php echo $Nu_Almacen_Interno; ?>"/>
			<input type="hidden" class="input" id="txt-Nu_Naturaleza_Movimiento_Inventario" name="Nu_Naturaleza_Movimiento_Inventario" value="<?php echo $TipoMovimientoInventario[0]['nu_naturaleza_movimiento_inventario']; ?>"/>

			<input type="hidden" class="input" id="txt-Nu_Tipo_Movimiento_Inventario_Agregar" name="Nu_Tipo_Movimiento_Inventario_Agregar" value="<?php echo $Nu_Tipo_Movimiento_Inventario; ?>"/>

			<input type="hidden" class="input" id="txt-Nu_Formulario" name="Nu_Formulario" value="<?php echo $TipoMovimientoInventario[0]["nu_almacen_destino"] . $TipoMovimientoInventario[0]['nu_formulario']; ?>"/>

			<input type="hidden" class="input" id="txt-Nu_Tipo_Cambio_Compra" name="Nu_Tipo_Cambio_Compra" value="0.00"/>

			<!-- Cierre de Inventario -->
			<input type="hidden" class="input" id="txt-Fe_Cierre_Year" value="<?php echo $Cierre_Inventario['fe_cierre_year']; ?>"/>
			<input type="hidden" class="input" id="txt-Fe_Cierre_Month" value="<?php echo $Cierre_Inventario['fe_cierre_month']; ?>"/>

			<!-- Fecha Sistema Inicio -->
			<input type="hidden" class="input" id="txt-Fe_Sistema_Sistema" value="<?php echo $Fe_Sistema_Inicio; ?>"/>

			<input type="hidden" class="input" id="txt-Nu_IGV" name="Nu_IGV" autocomplete="off" value="<?php echo $Nu_IGV; ?>" />

			<h5 class="title is-5 title-report"><?php echo $TipoMovimientoInventario[0]['no_tipo_movimiento_inventario']; ?></h5>

			<!--Modal Message Delete-->
			<div class="modal MsgData">
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
				<div class="column is-3">
			        <div class="field">
		        		<label class="label">Almacen Origen</label>
		        		<?php
		        			if(trim($TipoMovimientoInventario[0]["nu_almacen_origen"]) == ""){ ?>
	    						<span class="select">
		        				<select class="Nu_Almacen_Origen combobox required">
						    		<option value="" selected>Seleccionar...</option>
								<?php
									foreach($estaciones as $value){
										if (
												(
													$value['tipo'] == 1 && ($Nu_Tipo_Movimiento_Inventario == '01' || $Nu_Tipo_Movimiento_Inventario == '21' || $Nu_Tipo_Movimiento_Inventario == '16' || $Nu_Tipo_Movimiento_Inventario == '05' || $Nu_Tipo_Movimiento_Inventario == '08' || $Nu_Tipo_Movimiento_Inventario == '28' || $Nu_Tipo_Movimiento_Inventario == '55')
												)
										){
											echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
								    	} else if (
								    		(
								    			$value['tipo'] == 2 && ($Nu_Tipo_Movimiento_Inventario == '07' || $Nu_Tipo_Movimiento_Inventario == '27')
								    		)
								    	) {
											echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
								    	}
							    	}
							    ?>
							    </select>
							    </span>
		        		<?php } else { ?>
						<p class="control has-icon">
							<input type="hidden" class="input Nu_Almacen_Origen" name="Nu_Almacen_Origen" value="<?php echo $TipoMovimientoInventario[0]['nu_almacen_origen']; ?>"/>
			        		<input type="text" class="input" name="Nu_Almacen_Origen" disabled autocomplete="off" value="<?php echo $TipoMovimientoInventario[0]['no_almacen_origen']; ?>"/>
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<?php } ?>
					  	<p class="help"></p>
					</div>
		        </div>

				<div class="column is-3">
			        <div class="field">
		        		<label class="label">Almacen Destino</label>
		        		<?php
		        			if(trim($TipoMovimientoInventario[0]["nu_almacen_destino"]) == ""){ ?>
	    						<span class="select">
		        				<select class="Nu_Almacen_Destino combobox required">
						    		<option value="" selected>Seleccionar...</option>
								<?php
									foreach($estaciones as $value) {
										if (
												(
													$value['tipo'] == 1 && ($Nu_Tipo_Movimiento_Inventario == '01' || $Nu_Tipo_Movimiento_Inventario == '21' || $Nu_Tipo_Movimiento_Inventario == '16' || $Nu_Tipo_Movimiento_Inventario == '18' || $Nu_Tipo_Movimiento_Inventario == '07' || $Nu_Tipo_Movimiento_Inventario == '27')
												)
										){
											echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
								    	} else if (
								    		(
								    			$value['tipo'] == 3 && ($Nu_Tipo_Movimiento_Inventario == '08' || $Nu_Tipo_Movimiento_Inventario == '28')
								    		)
								    	) {
											echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
								    	}
								    }
							    ?>
							    </select>
							    </span>
		        		<?php }else{ ?>
						<p class="control has-icon">
							<input type="hidden" class="input Nu_Almacen_Destino" name="Nu_Almacen_Destino" value="<?php echo $TipoMovimientoInventario[0]['nu_almacen_destino']; ?>"/>
			        		<input type="text" class="input" name="Nu_Almacen_Destino" disabled autocomplete="off" value="<?php echo $TipoMovimientoInventario[0]['no_almacen_destino']; ?>"/>
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<?php } ?>
					  	<p class="help"></p>
					</div>
		        </div>

			  	<div class="column is-3">
			        <div class="field">
		        		<label class="label">F. Emisión (Inventario)</label>
						<p class="control has-icon">
		        			<input type="text" class="input required" id="txt-Fe_Emision_Compra" name="Fe_Emision_Compra" value="" /><span class="icon is-small">
						    	<i class="fa fa-calendar"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

				<div class="column is-2">
			        <div class="field">
		        		<label class="label">F. Sistema</label>
						<p class="control has-icon">
							<?php $_FE_Sistema = explode('/', $Fe_Sistema); ?>
							<input type="hidden" class="input" id="txt-Fe_Mes" value="<?php echo $_FE_Sistema[1]; ?>"/>
							<input type="hidden" class="input" id="txt-Fe_Sistema" name="Fe_Sistema" value="<?php echo $_FE_Sistema[2].'-'.$_FE_Sistema[1].'-'.$_FE_Sistema[0]; ?>"/>
			        		<input type="text" class="input" disabled autocomplete="off" value="<?php echo $Fe_Sistema; ?>"/>
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>
	        </div>

	        <div class="columns">
			  	<div class="column is-5">
			  		<?php
			  		if (
			  			$Nu_Tipo_Movimiento_Inventario == '07' ||
						$Nu_Tipo_Movimiento_Inventario == '08' ||
						$Nu_Tipo_Movimiento_Inventario == '27' ||
						$Nu_Tipo_Movimiento_Inventario == '28' ||
						$Nu_Tipo_Movimiento_Inventario == '18' ||
						$Nu_Tipo_Movimiento_Inventario == '16'
			  			) { ?>
			  			<input type="hidden" class="input" id="txt-Nu_Documento_Identidad" name="Nu_Documento_Identidad" value="1" />
				  		<input type="hidden" class="input txt-No_Proveedor" id="txt-No_Proveedor" name="No_Proveedor" autocomplete="off" value="A" />
			  		<?php } else { ?>
			        <div class="field">
				        <label class="label">Proveedor</label>
						<p class="control has-icon">
							<input type="hidden" class="input required" id="txt-Nu_Documento_Identidad" name="Nu_Documento_Identidad" />
				        	<input type="text" class="input required txt-No_Proveedor" id="txt-No_Proveedor" name="No_Proveedor" autocomplete="off" placeholder="Ingresar Código o Nombre" />
						    <span class="icon is-small">
						    	<i class="fa fa-user"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
					<?php } ?>
		        </div>

			  	<div class="column">
			        <div class="field">
		            <label class="label">Tipo Documento</label>
					    <p class="control has-icon">
		    				<span class="select">
							    <select id="cbo-Nu_Tipo_Documento_Compra" class="combobox required">
								    <option value="" selected>Seleccionar..</option>
								    <?php
									foreach($TipoDocumentosReferencia as $value)
										echo "<option value='" . $value['nu_tipo_documento'] . "'>" . $value['nu_tipo_documento'] . " - " . $value['no_tipo_documento'] . "</option>";
								    ?>
							    </select>
							  	<p class="help"></p>
						    </span>
						</p>
				    </div>
	        	</div>

				<div class="column is-1">
			        <div class="field">
		        		<label class="label">Serie</label>
						<p class="control has-icon">
			        		<input type="text" class="input required mayuscula input-number_letter" id="txt-Nu_Serie_Compra" name="Nu_Serie_Compra" autocomplete="off" maxlength="4" placeholder="" />
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

				<div class="column">
			        <div class="field">
		        		<label class="label">Numero</label>
						<p class="control has-icon">
			        		<input type="text" class="input required input-number" id="txt-Nu_Numero_Compra" name="Nu_Numero_Compra" autocomplete="off" maxlength="8" placeholder="Ingresar numero" />
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>
			</div>

	        <div class="columns div-ReferenciaDocumentoOriginal">
			  	<div class="column is-5">
			  	</div>
			  	<div class="column">
			        <div class="field">
		            <label class="label">Ref. Tipo Documento</label>
	    			<span class="select">
					    <select id="cbo-Nu_Tipo_Documento_Compra_Referencia" class="combobox combobox_required ">
						    <option value="" selected>Seleccionar..</option>
						    <?php
							foreach($TipoDocumentosReferencia as $value)
								echo "<option value='" . $value['nu_tipo_documento'] . "'>" . $value['nu_tipo_documento'] . " - " . $value['no_tipo_documento'] . "</option>";
						    ?>
					    </select>
					  	<p class="help"></p>
				    </span>
				    </div>
	        	</div>

				<div class="column is-1">
			        <div class="field">
		        		<label class="label">Serie Ref.</label>
						<p class="control has-icon">
			        		<input type="text" class="input mayuscula" id="txt-Nu_Serie_Compra_Referencia" name="Nu_Serie_Compra_Referencia" autocomplete="off" maxlength="4" placeholder="" />
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>

				<div class="column">
			        <div class="field">
		        		<label class="label">Numero Ref.</label>
						<p class="control has-icon">
			        		<input type="text" class="input" id="txt-Nu_Numero_Compra_Referencia" name="Nu_Numero_Compra_Referencia" autocomplete="off" maxlength="8" placeholder="Ingresar numero doc. de ref." />
							<span class="icon is-small">
						    	<i class="fa fa-list-alt"></i>
						    </span>
					  	</p>
					  	<p class="help"></p>
					</div>
		        </div>
			</div>

			<!--Flete-->
			<!--<article class="message is-primary">
				<div class="message-header">
					<div class="column div_title_RC">
						<label class="label label-message-header-title">Flete <input type="checkbox" id="chk-addFlete" onclick="addFlete(this)"></label>
					</div>
				</div>
				<div class="message-body div-Fletes">
					<div class="columns">
				        <div class="column is-2">
					        <div class="field">
					        	<label class="label">Fecha Traslado</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Fe_Flete" autocomplete="off"/>
						        	<span class="icon is-small">
									    <i class="fa fa-calendar"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

					  	<div class="column is-5">
					  		<?php
							    $arrMotivoTraslado = array(
							    	0 => "Venta",
							    	1 => "Venta Sujeta a Confirmacion del Comprador",
							    	2 => "Compra",
									3 => "Consignacion",
									4 => "Devolucion",
									5 => "Traslado entre Establecimentos de la Misma Empresa",
									6 => "Traslado de Bienes para Transformacion",
									7 => "Recojo de Bienes Transformados",
									8 => "Traslado por Emisor Itinerante de Comprobantes de Pago",
									9 => "Traslado Zona Primaria",
									10 => "Importacion",
									11 => "Exportacion",
									12 => "Venta con entrega a terceros",
									13 => "Otros"
								);
							?>
					        <!--<div class="field">
				            <label class="label">Motivo Traslado</label>
			    			<span class="select">
							    <select id="cbo-MotivoTraslado" class="combobox">
								    <option value="" selected>Seleccionar..</option>
								    <?php
								    	foreach($arrMotivoTraslado as $key => $value)
											echo "<option value='" . $key . "'>" . $value . "</option>";
								    ?>
							    <!--</select>
							  	<p class="help"></p>
						    </span>
						    </div>
			        	</div>

				        <div class="column is-2">
					        <div class="field">
					        	<label class="label">Placa Vehiculo</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Placa" maxlength="64" autocomplete="off" />
						        	<span class="icon is-small">
									    <i class="fa fa-list-alt"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column is-2">
					        <div class="field">
					        	<label class="label">Licencia</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Licencia" maxlength="64" autocomplete="off" />
						        	<span class="icon is-small">
									    <i class="fa fa-list-alt"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
				    </div>

					<div class="columns">
				        <div class="column is-4">
					        <div class="field">
					        	<label class="label">Certificado de Inscripción</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Autorizacion" maxlength="64" autocomplete="off" />
						        	<span class="icon is-small">
									    <i class="fa fa-list-alt"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
				        <div class="column is-8">
					        <div class="field">
						        <label class="label">Proveedor</label>
								<p class="control has-icon">
									<input type="hidden" class="input" id="txt-ID_Transportista_Proveedor" />
						        	<input type="text" class="input txt-No_Transportista_Proveedor" autocomplete="off" placeholder="Ingresar Código o Nombre" />
								    <span class="icon is-small">
								    	<i class="fa fa-user"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
						</div>
				    </div>
				</div>
			</article>-->

			<?php if ($Nu_Tipo_Movimiento_Inventario == '01' || $Nu_Tipo_Movimiento_Inventario == '21') { ?>
	        <div class="columns">
				<div class="column is-3" align="center">
			        <div class="field">
		        		<label class="label"></label>
					    <p class="control has-icon">
					    	<label class="label"><h1>¿Ingreso Directo?</h1></label>
					    </p>
		        	</div>
		        </div>
				<div class="column">
					<div class="tabs is-toggle is-fullwidth is-large">
					  	<ul>
							<li id="tab_orden_no" class="is-active">
								<a onclick="activarOrden(0);">
									<span class="icon"><i class="fa fa-check"></i></span>
									<span><b>SI</b></span>
								</a>
						    </li>
						    <li id="tab_orden_si">
								<input type="hidden" id="txt-tab_orden_si" value="">
						    	<a onclick="activarOrden(1);">
							        <span class="icon"><i class="fa fa-check"></i></span>
							        <span><b>NO</b></span>
						    	</a>
						    </li>
						</ul>
					</div>
				</div>
			</div>
			<?php } else { ?>
			<input type="hidden" id="txt-tab_orden_si" value="">
			<?php } ?>

			<article class="message is-primary" id="div-orden_compra">
				<div class="message-header">
					<div class="column div_title_RC">
						<label class="label label-message-header-title">Orden de Compra</label>
					</div>
				</div>
				<div class="message-body">
		    		<div class="columns">
					  	<div class="column is-2">
					        <div class="field">
				        		<label class="label">Número Orden</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Numero_Orden" name="Numero_Orden" autocomplete="off" placeholder="Ingresar número" />
					        		<span class="icon is-small">
									    <i class="fa fa-list-alt"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

					  	<div class="column is-2">
					        <div class="field">
				        		<label class="label">*</label>
					    			<p class="control has-icon">
			        					<button style="width: 100%;" class="button is-info btn-info" id="btn-buscarOrdenCompra"><label class="label-btn-name">Buscar</label></button>
			        				</p>
			        			</label>
			        		</div>
			        	</div>

					  	<div class="column">
					        <div class="field">
					            <label class="label">Lista de Ordenes de Compra</label>
					    		<p class="control has-icon">
							        <div class="select multiple is-multiple">
									  <select id="cbo-arrOrdenesCompra" multiple size="25">
									  </select>
									</div>
								</p>
							  	<p class="help"></p>
							</div>
						</div>
					  	<div class="column">
					        <div class="field">
					            <label class="label">Mensaje</label>
							  	<p id="msg-orden_compra"></p>
							</div>
						</div>
				    </div>
				    <br/>
		    		<div class="columns">
						<table class="bulma table report_CRUD" id="table-OrdenCompra">
							<thead>
							  	<tr>
							  		<th class="text-center" align="center" style="display:none;"></th>
							  		<th class="text-center" align="center">Serie O/C</th>
							  		<th class="text-center" align="center">Número O/C</th>
							  		<th class="text-center" align="center">Cod. Producto</th>
							  		<th class="text-center" align="center">Nom. Producto</th>
							  		<th class="text-center" align="center">Cantidad Pedida</th>
							  		<th class="text-center" align="center">Cantidad Atendida</th>
							  		<th class="text-center" align="center">Costo Unitario</th>
							  		<th class="text-center" align="center">Total S/I.G.V.</th>
							  		<th class="text-center" align="center">Total C/I.G.V.</th>
							  		<th class="text-center" align="center">Stock</th>
							  	</tr>
						  	</thead>
						  	<tbody>
						  	</tbody>
						  	<tfoot>
						  	</tfoot>
						</table>
					</div>
				</div>
			</article>

			<article class="message is-primary" id="div-detalle_producto">
				<div class="message-header">
					<div class="column div_title_RC">
						<label class="label label-message-header-title">Agregar productos</label>
					</div>
				</div>
				<div class="message-body">
		    		<div class="columns">
					  	<div class="column is-4">
					        <div class="field">
				        		<label class="label">Producto</label>
					        	<p class="control has-icon">			        		
						        	<input type="hidden" class="input" id="txt-Nu_Id_Producto" name="Nu_Id_Producto" >
						        	<input type="text" class="input" id="txt-No_Producto_Detalle_Compra" name="No_Producto_Detalle_Compra" autocomplete="off" placeholder="Ingresar Codigo o Nombre del Producto" />
					        		<span class="icon is-small">
									    <i class="fa fa-shopping-cart"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">Stock Act.</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Cantidad_Actual" disabled autocomplete="off" placeholder="Ingresar precio" />
						        	<span class="icon is-small">
									    <i class="fa fa-plus-square"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">Cantidad</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Cantidad_Compra" name="Nu_Cantidad_Compra" autocomplete="off" placeholder="Ingresar cantidad" />
						        	<label class="checkbox checkbox-conversionGLP"><input type="checkbox" id="chk-conversionGLP" onclick="checkVerifyconversionGLP(this)"><strong>Conversion GLP</strong></label>
						        	<span class="icon is-small span-Nu_Cantidad_Compra">
									    <i class="fa fa-plus-square"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">Costo Uni.</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Costo_Unitario" name="Nu_Costo_Unitario" autocomplete="off" placeholder="Ingresar costo unitario" />
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column is-2">
					        <div class="field">
					        	<label class="label">Total Sin I.G.V.</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Total_SIGV" name="Nu_Total_SIGV" autocomplete="off" required/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column is-2">
					        <div class="field">
					        	<label class="label">Total Con I.G.V. (Referencial)</label>
					        	<p class="control has-icon">
					        		<input type="hidden" class="input" id="txt-Nu_Total_CIGV" name="Nu_Total_CIGV" autocomplete="off" required/>
						        	<input type="text" class="input" id="label-Nu_Total_CIGV" autocomplete="off" required/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
					</div>

					<?php //SOLO INGRESO DE COMPRAS MARKET
			  		if ( $Nu_Tipo_Movimiento_Inventario == '01' ) { ?>
			    	<div class="columns">
				        <!-- Activar Pedido Vencimiento -->
					  	<div class="column is-4">
					        <div class="field">
								<label class="checkbox checkbox-pedido-vencimiento"><input type="checkbox" id="chk-pedido_vencimiento" onclick="activarPedidoVencimiento(this)"><strong>Activar Vencimiento Lote</strong></label>
							</div>
				        </div>
					</div>

			        <!-- Lote Pedido Vencimiento -->
	    			<div class="columns div-LotePedidoVencimiento">
				        <div class="column is-2">
					        <div class="field">
					        	<label class="label">Numero Lote</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Lote" maxlength="15" autocomplete="off" />
						        	<span class="icon is-small">
									    <i class="fa fa-list-alt"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
				        <div class="column is-2">
					        <div class="field">
					        	<label class="label">Fecha Vencimiento</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Fe_Vencimiento_Pedido" autocomplete="off"/>
						        	<span class="icon is-small">
									    <i class="fa fa-calendar"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
				    </div>
					<?php } ?>

	                <div class="columns">
				        <div class="column is-10"></div>
				        <div class="column">
							<button style="width: 100%;" class="button is-primary btn-primary" id="btn-addProducto"><i class="fa fa-plus-circle icon-size" aria-hidden="true"> <label class="label-btn-name">Agregar item</label></i></button>
						</div>
				    </div><!-- ./ is-2 -->

			    	<!--<div class="columns">
				        <div class="column is-10"></div>
				        <div class="column">
					        <div class="field">
								<button type="button" class="button is-success btn-success" id="btn-addProducto"><i class="fa fa-plus-circle icon-size" aria-hidden="true"><label class="label-btn-name"> Agregar Producto</label></i></button>
							</div>
				        </div>
			        </div>-->

			        <!-- Activar datos Complementarios -->
			    	<div class="columns">
				        <div class="column is-10"></div>
				        <div class="column">
					        <div class="field">
								<label class="checkbox checkbox-datosComplementarios"><input type="checkbox" id="chk-datosComplementarios" onclick="activeDatosComplementarios(this)"><strong>Datos Complementarios</strong></label>
							</div>
				        </div>
			        </div>
			    </div>
	        </article>

	        <!--Modal Actualizar P.V según Margen -->
			<div class="modal modal-PreciVentaMargen">
	  			<div class="modal-background"></div>
				<div class="modal-content">
					<article class="message">
						<div class="message-header">
							<div class="column div_title_RC">
								<label class="label label-message-header-title"><strong>Actualizar precio de venta según Margen</strong></label>
							</div>
							<button class="delete icon-delete"></button>
						</div>
						<div class="message-body">
							<table id="table-PrecioVentaMargen" class="bulma table report_CRUD">
								<tbody>
								</tbody>
							</table>
							<article class="message div-msg_PrecioVentaMargen">
							  <div class="message-body" id="div-msg_PrecioVentaMargen">
							  </div>
							</article>
						</div>
	    				<footer class="modal-card-foot">
							<div class="column div_title_RC">
					    		<button type="button" id="btn-cambiarprecio" class="button is-success btn-success"><label class="label-btn-name">Cambiar Precio </label></button>
					    		<button type="button" class="button is-danger btn-danger icon-delete"><i class="fa fa-sign-out icon-size" aria-hidden="true"> <label class="label-btn-name">Salir</label></i></button>
					    	</div>
					    </footer>
					</article>
				</div>
			</div>
			<!-- ./Modal -->

			  <!--Modal Actualizar P.V según Margen -->
			<div class="modal modal-MargenGanancia">
	  			<div class="modal-background"></div>
				<div class="modal-content">
					<article class="message">
						<div class="message-header">
							<div class="column div_title_RC">
								<label class="label label-message-header-title"><strong>Error en Costo Unitario</strong></label>
							</div>
						</div>
						<div class="message-body">
							<table id="table-MargenGanancia" class="bulma table report_CRUD">
								<tbody>
								</tbody>
							</table>
							<article class="message div-msg_PrecioVentaMargen">
							  <div class="message-body" id="div-msg_PrecioVentaMargen">
							  </div>
							</article>
						</div>
	    				<footer class="modal-card-foot">
							<div class="column div_title_RC">
					    		<button type="button" onclick="$('.modal-MargenGanancia').hide()" class="button is-danger btn-danger icon-delete"><i class="fa fa-sign-out icon-size" aria-hidden="true"> <label class="label-btn-name">Salir</label></i></button>
					    	</div>
					    </footer>
					</article>
				</div>
			</div>
			<!-- ./Modal -->

	        <!-- Datos Complementarios -->
	        <article class="message is-primary div-datosComplementarios">
				<div class="message-header div_title_RC">
					<div class="column div_title_RC">
						<label class="label label-message-header-title">Datos Complementarios</label>
					</div>
				</div>
				<div class="message-body">
	    			<div class="columns">
				        <div class="column">
					        <div class="field">
					        	<label class="label">Tipo Formulario</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-No_Tipo_Movimiento_Inventario_Datos_Complementarios" autocomplete="off" disabled value="<?php echo ucwords(strtolower($TipoMovimientoInventario[0]['no_tipo_movimiento_inventario'])); ?>" />
						        	<span class="icon is-small">
									    <i class="fa fa-plus-square"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">Fecha Emisión</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Fe_Emision_Datos_Complementarios" autocomplete="off" disabled value="" />
						        	<span class="icon is-small">
									    <i class="fa fa-calendar"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">Fecha Recepcion</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Fe_Recepcion" name="Fe_Recepcion" autocomplete="off" value="" />
						        	<span class="icon is-small">
									    <i class="fa fa-calendar"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
						</div>

						<div class="column">
					        <div class="field">
					        	<label class="label">Hora Recepcion</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input timepicker" id="txt-Fe_Hora_Recepcion" name="Fe_Hora_Recepcion" autocomplete="off" value="" />
						        	<span class="icon is-small">
									    <i class="fa fa-calendar"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
						</div>

						<div class="column">
					        <div class="field">
					        	<label class="label">Turno Recepcion</label>
					        	<p class="control has-icon">
						        	<span class="select">
				        				<select class="cbo-Nu_Turno_Recepcion combobox" id="cbo-Nu_Turno_Recepcion">
								    		<option value="" selected>Seleccionar...</option>
											<?php
											for ($i = 1; $i < 10; $i++)
												echo "<option value='" . $i . "'>" . $i . "</option>";
									    	?>
									    </select>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
						</div>

						<div class="column">
					        <div class="field">
					        	<label class="label">Numero SCOP</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Numero_Scop_Recepcion" onkeypress="return isNumberKey(event)"  autocomplete="off" maxlength="16" value="" />
						        	<span class="icon is-small">
									    <i class="fa fa-list-alt"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
						</div>
					</div>

					<div class="columns">
						<div class="column">
					        <div class="field">
					        	<label class="label">Observacion</label>
					        	<p class="control has-icon">
						        	<textarea class="input" id="txt-Txt_Observacion_Recepcion" name="Txt_Observacion_Recepcion" maxlength="200"></textarea>
						        	<span class="icon is-small">
									    <i class="fa fa-list-alt"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
				    </div>
				</div>
			</article>

	        <!--Conversion de GLP compra -->
			<article class="message is-primary div-conversionGLP">
				<div class="message-header div_title_RC">
					<div class="column div_title_RC">
						<label class="label label-message-header-title">Conversion GLP</label>
					</div>
				</div>
				<div class="message-body">
	    			<div class="columns">

						<input type="hidden" class="input" id="txt-Enviar_Conversion_GLP" autocomplete="off" value="false" />
		        		<input type="hidden" class="input" id="txt-_Nu_Kilos" autocomplete="off" />
		        		<input type="hidden" class="input" id="txt-_Nu_Gravedad_Especifica" autocomplete="off" />
		        		<input type="hidden" class="input" id="txt-_Nu_Galones_GLP" autocomplete="off" />
		        		<input type="hidden" class="input" id="txt-_Nu_Litros_GLP" autocomplete="off" />

				        <div class="column">
					        <div class="field">
					        	<label class="label">Kilos</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input conversionGLP" id="txt-Nu_Kilos" name="Nu_Kilos" autocomplete="off" placeholder="Ingresar kilos" />
						        	<span class="icon is-small">
									    <i class="fa fa-plus-square"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">G.E.</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input conversionGLP" id="txt-Nu_Gravedad_Especifica" name="Nu_Gravedad_Especifica" autocomplete="off" placeholder="Ingresar G.E." />
						        	<span class="icon is-small">
									    <i class="fa fa-plus-square"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">Galones</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input conversionGLP" id="txt-Nu_Galones_GLP" name="Nu_Galones_GLP" autocomplete="off" required />
						        	<span class="icon is-small">
									    <i class="fa fa-plus-square"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">Litros</label>
					        	<p class="control has-icon">
					        		<input type="hidden" class="input conversionGLP" id="txt-Nu_Litros_GLP" name="Nu_Litros_GLP" autocomplete="off" required/>
						        	<input type="text" class="input conversionGLP" id="label-Nu_Litros_GLP" autocomplete="off" disabled/>
						        	<span class="icon is-small">
									    <i class="fa fa-plus-square"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
	        		</div>
		        </div>
	        </article>

	        <div class="columns div-table_producto">
				<table class="bulma table report_CRUD" id="table-producto">
					<thead>
					  	<tr>
					  		<th colspan="2" class="text-center">Producto</th>
					  		<th class="text-center" align="center">Cantidad</th>
					  		<th class="text-center" align="center">Costo Unitario</th>
					  		<th class="text-center" align="center">Total Sin I.G.V.</th>
					  		<th class="text-center" align="center">Total Con I.G.V.</th>
					  		<th class="text-center" align="center">Nro. Lote</th>
					  		<th class="text-center" align="center">F. Vencimiento</th>
							<th class="text-center" align="center">-</th>
					  	</tr>
				  	</thead>
				    <tfoot>
			  			<tr>
			  				<td class="text-right" align="right" colspan="2"><label class="label">Total: </label></td>
			  				<td class="text-right" align="right">
			  					<input type="hidden" class="input" id="txt-Nu_Cantidad_Tot_Actual" name="Nu_Cantidad_Tot_Actual" value="0.00"/>
			  					<label class="label txt-Nu_Cantidad_Tot_Actual">0.00</label>
							</td>
							<td class="text-right" align="right">
			  					<label class="label"></label>
							</td>
							<td class="text-right" align="right">
			  					<input type="hidden" class="input" id="txt-Nu_Total_SIGV_Tot_Actual" name="Nu_Total_SIGV_Tot_Actual" value="0.00"/>
			  					<label class="label txt-Nu_Total_SIGV_Tot_Actual">0.00</label>
							</td>
			  				<td class="text-right" align="right">
			  					<input type="hidden" class="input" id="txt-Nu_Total_CIGV_Tot_Actual" name="Nu_Total_CIGV_Tot_Actual" value="0.00"/>
			  					<label class="label txt-Nu_Total_CIGV_Tot_Actual">0.00</label>
							</td>
							<td class="text-right" align="right">
			  					<label class="label"></label>
							</td>
			  			</tr>
					</tfoot>
				</table>
			</div>

			<!--Registro de Compras-->
			<article class="message is-primary div-PrincipalRegistroCompras">
				<div class="message-header">
					<div class="column div_title_RC">
						<label class="label label-message-header-title">Registro de Compras SUNAT: <input type="checkbox" id="chk-addCUentasXPagar" onclick="addCuentasXPagar(this)"></label>
					</div>
				</div>

				<div class="message-body div-RegistroCompras">
					<div class="columns">
				         <div class="column is-1_5">
					        <div class="field">
					        	<label class="label">Nro. Registro: </label>
					        	<p class="control has-icon">
					        		<strong><label class="label label-Nu_Registro_Compra"></label></strong>
					        	</p>
							</div>
				        </div>
					    
					  	<div class="column is-2">
					        <div class="field">
				        		<label class="label">Fecha Emision (Factura)</label>
								<p class="control has-icon">
				        			<input type="text" class="input required" id="txt-Fe_Emision_Registro_Compra" name="Fe_Emision_Registro_Compra" autocomplete="off" value="" />
				        			<span class="icon is-small">
								    	<i class="fa fa-calendar"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column is-0_5">
					        <div class="field">
					        	<label class="label">Dias V.</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Dias_Vencimiento_RC" name="Nu_Dias_Vencimiento_RC" autocomplete="off" required/>
						        	<span class="icon is-small">
									    <i class="fa fa-calendar"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
					    
					  	<div class="column is-1_5">
					        <div class="field">
				        		<label class="label">F. Vencimiento</label>
								<p class="control has-icon">
				        			<!--<input type="text" class="input" id="txt-Fe_Vencimiento_RC" name="Fe_Vencimiento_RC" autocomplete="off" value="<?php echo $Fe_Fin; ?>" />-->
				        			<input type="text" class="input" id="txt-Fe_Vencimiento_RC" name="Fe_Vencimiento_RC" autocomplete="off" value="" />
				        			<span class="icon is-small">
								    	<i class="fa fa-calendar"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

					  	<div class="column is-2">
					        <div class="field">
						    	<label class="label">Contabilizar otro Periodo</label>
				       			<span class="select">
			        				<select id="cbo-Contablizar_Periodo_RC" class="combobox">
							    		<option value="N" selected>No</option>
							    		<option value="S">Si</option>
								    </select>
								</span>
							</div>
						</div>
					    
					  	<div class="column is-1_5 div-Fe_Periodo">
					        <div class="field">
				        		<label class="label">F. Periodo</label>
				        		<input type="text" class="input" id="txt-Fe_Periodo_RC" name="Fe_Periodo_RC" autocomplete="off" value="<?php echo $Fe_Sistema; ?>" />
							  	<p class="help"></p>
							</div>
				        </div>

					  	<div class="column is-2">
					        <div class="field">
						    	<label class="label">Rubros</label>
				       			<span class="select">
				       				<input type="hidden" class="input" id="txt-Nu_Memoria_Rubro" name="Nu_Memoria_Rubro" autocomplete="off" value="" />
			        				<select id="cbo-Rubros_RC" name="cbo-Rubros_RC" class="combobox">
							    		<option value="N" selected>No</option>
							    		<option value="S">Si</option>
								    </select>
					  				<p class="help"></p>
								</span>
							</div>
						</div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">I.G.V</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" autocomplete="off" disabled value="<?php echo $Nu_IGV; ?>"/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column">
					        <div class="field">
					        	<label class="label">T.C</label>
					        	<p class="control has-icon">
					        		<input type="hidden" class="input" id="txt-Nu_TC_RC" name="Nu_TC_RC" autocomplete="off" required/>
						        	<input type="text" class="input" id="label-Nu_TC_RC" autocomplete="off" disabled/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>
					</div>

					<div class="columns div-RegistroCompras">
					  	<div class="column is-2">
					        <div class="field">
						    	<label class="label">Monedas</label>
				       			<span class="select">
				       				<input type="hidden" class="input" id="txt-Nu_Memoria_Moneda" name="Nu_Memoria_Moneda" autocomplete="off" value="" />
			        				<select id="cbo-Moneda_RC" name="cbo-Moneda_RC" class="combobox">
							    		<option value="N" selected>No</option>
							    		<option value="S">Si</option>
								    </select>
							  		<p class="help"></p>
								</span>
							</div>
						</div>

						<div class="column is-1"></div>

				        <div class="column is-1_5">
					        <div class="field">
					        	<label class="label">Base imponible</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_BI_RC" name="Nu_BI_RC" autocomplete="off" required/>
						        	<input type="hidden" class="input" id="label-Nu_BI_RC" autocomplete="off" disabled required/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column is-1_5">
					        <div class="field">
					        	<label class="label">I.G.V.</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_IGV_RC" name="Nu_IGV_RC" autocomplete="off" required/>
						        	<input type="hidden" class="input" id="label-Nu_IGV_RC" autocomplete="off" disabled required/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column is-2">
					        <div class="field">
					        	<label class="label">Total</label>
					        	<p class="control has-icon">
					        		<input type="text" class="input" id="txt-Nu_Totacl_RC" name="Nu_Totacl_RC" autocomplete="off" required/>
						        	<input type="hidden" class="input" id="label-Nu_Totacl_RC" autocomplete="off" disabled required/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							  	<p class="help"></p>
							</div>
				        </div>

				        <div class="column is-1_5">
					        <div class="field">
					        	<label class="label">Percepcion</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Percepcion_RC" name="Nu_Percepcion_RC" autocomplete="off" required/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							</div>
				        </div>

					  	<div class="column is-1">
					        <div class="field">
						    	<label class="label">Inafecto IGV</label>
				       			<span class="select">
			        				<select id="cbo-Nu_Inafecto_IGV_RC" class="combobox">
							    		<option value="N" selected>No</option>
							    		<option value="S">Si</option>
								    </select>
								</span>
							</div>
						</div>
					  	<div class="column is-1_5 div-Inafecto_IGV_RC">
					        <div class="field">
					        	<label class="label">Inafecto</label>
					        	<p class="control has-icon">
						        	<input type="text" class="input" id="txt-Nu_Inafecto_IGV_RC" name="Nu_Inafecto_IGV_RC" autocomplete="off" required/>
						        	<span class="icon is-small">
									    <i class="fa fa-money"></i>
								    </span>
							  	</p>
							</div>
				        </div>
					</div>

					<div class="columns div-RegistroCompras">
				        <div class="column is-3_5">
					        <div class="field">
					        	<label class="label">Glosa</label>
						        <input type="text" class="input" id="txt-Txt_Glosa_RC" name="Nu_Glosa_RC" autocomplete="off" maxlength="40" required/>
							</div>
				        </div>
				    </div>
				</div>
			</article>
			<!--FIN Registro de Compras-->

			<br>

            <div class="columns">
			  	<div class="column is-6">
			  		<button style="width: 100%;" class="button is-danger btn-close is-large" id="btn-close"><i class="fa fa-sign-out icon-size" aria-hidden="true"> <label class="label-btn-name">Regresar</label></i></button>
				</div>

				<div class="column is-6">
			  		<button type="button" style="width: 100%;" class="button is-primary btn-primary is-large" id="btn-save"><i class="fa fa-save icon-size" aria-hidden="true"> <label class="btn-label_save_edit label-btn-name">Guardar</label></i></button>
				</div>
			</div><!-- ./ Row button -->
		</div>

		<!--Modal Message Delete-->
		<div class="modal modal-cintillo">
  			<div class="modal-background"></div>
			<div class="modal-content">
				<article class="message">
					<div class="message-header">
						<div class="column div_title_RC">
							<label class="label label-message-header-title"><strong>Cintillo</strong></label>
						</div>
						<button class="delete icon-delete"></button>
					</div>
					<div id="message-status-print" class="message-body message-status"></div>
    				<footer class="modal-card-foot">
				    	<div class="column is-6 div_title_RC">
				    		<button style="width: 100%;" class="button is-danger btn-danger icon-delete"><i class="fa fa-sign-out icon-size" aria-hidden="true"> <label class="label-btn-name">Salir</label></i></button>
					   	</div>
	            		<div class="column is-6 div_title_RC">
				    		<button style="width: 100%;" id="btn-print" class="button is-default btn-default" onclick="printDiv();"><i class="fa fa-print icon-size" aria-hidden="true"> <label class="label-btn-name">Imprimir</label></i></button>
				    	</div>
				    </footer>
				</article>
			</div>
		</div>

		<div class="columns is-desktop" id="div-Movimiento_Inventario_Table">
<?php
	}

	function gridView($response) {
		if ($response["status"] != "success") { ?>
            <div class="column is-12 text-center">
            	<div class="notification is-<?php echo $response["status"]; ?>"><?php echo $response["message"]; ?></div>
		    </div>
		<?php
		} else { ?>
	  		<div class="column is-12 table__wrapper StandardTable">
				<table class="table report_CRUD">
					<thead>
						<tr>
			                <th align="text-center">Formulario</th>
			                <th align="text-center">F. Emision</th>
			                <th align="text-center">Proveedor</th>
			                <th align="text-center">Tipo</th>
			                <th align="text-center">Serie</th>
			                <th align="text-center">Numero</th>
			                <th align="text-center">Producto</th>
			                <th align="text-center">Cantidad</th>
			                <th align="text-center">C. U.</th>
			                <th align="text-center">Usuario</th>
			                <th align="text-center">Estado</th>
			                <th align="text-center">Acciones</th>
			            </tr>
				    </thead>
					<tbody>
					<?php
						$i = 0;
						foreach ($response["data"]->rows as $rows) {
							$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
							$sEstado = 'Completado';
							$sClassSpan = 'info';
							if ( $rows["nu_cantidad"] == '0.0000' && $rows["nu_costo_unitario"] == '0.000000' ){
								$sEstado = 'Anulado';
								$sClassSpan = 'warning';
							}
					?>
							<tr class="<?php echo $color; ?> custList">
	            				<td align="left"><?php echo $rows["nu_formulario"]; ?></td>
	            				<td align="center" style="width: 140px;"><?php echo $rows["fe_emision"]; ?></td>
	            				<td align="left" style="width: 250px;"><?php echo $rows["no_razon_social"]; ?></td>
	            				<td align="center"><?php echo $rows["no_tipo_documento"]; ?></td>
	            				<td align="center"><?php echo $rows["nu_serie_documento"]; ?></td>
	            				<td align="left"><?php echo $rows["nu_numero_documento"]; ?></td>
	            				<td align="left" style="width: 160px;"><?php echo utf8_encode($rows["no_producto"]); ?></td>
	            				<td align="right"><?php echo number_format($rows["nu_cantidad"], 4, '.', ','); ?></td>
	            				<td align="right"><?php echo number_format($rows["nu_costo_unitario"], 6, '.', ','); ?></td>
	            				<td align="center"><?php echo $rows["no_usuario"]; ?></td>
		            			<td align="center">
		            				<span class="tag is-<?php echo $sClassSpan;?>"><?php echo $sEstado; ?></span>
		            			</td>
	            				<td align="center">
		                			<a class="button is-info is-small btn-info" title="Ver Cintillo" onclick="verCintillo('<?php echo $rows["id_proveedor"];?>', '<?php echo $rows["nu_formulario"];?>', '<?php echo $rows["nu_tipo_movimiento_inventario"];?>', '<?php echo $rows["fe_sistema"];?>', '<?php echo $rows["nu_tipo_documento"];?>', '<?php echo $rows["nu_serie_documento"];?>', '<?php echo $rows["nu_numero_documento"];?>')"><span class="icon is-small"><i class="fa fa-search-plus"></i></span></a>
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
	            <input type="hidden" id="pageActual" value="<?php echo $response["data"]->page ?>">
	            <input type="hidden" id="cantidadPage" value="<?php echo $response["data"]->total ?>"><!--Cantidad de Paginas -->
				<nav class="bulma pagination is-centered">
					<a class="bulma pagination-previous"><<</a>
					<a class="bulma pagination-next">>></a>
					<ul class="bulma pagination-list">
						<?php
						for ($i=1; $i <= $response["data"]->total; $i++) {
							if($i >= $response["data"]->page - $response["data"]->Pagelimit && $i <= $response["data"]->page + $response["data"]->Pagelimit) {
						?>
					    <li>
					    	<a href="#" class="bulma pagination-link <?php echo ($i == $response["data"]->page ? 'is-current' : '') ?>" data-page="<?php echo $i ?>">
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

	function gridViewExcel($response){
		$response = json_decode($response);
		if ($response->status != "success") { ?>
            <div class="column is-12 text-center">
            	<div class="notification is-<?php echo $response->status; ?>"><?php echo $response->message; ?></div>
		    </div>
		<?php
		} else { ?>
			<!-- Excel -->
	  		<div class="column is-10 is-offset-1">
				<table class="bulma table">
					<thead>
						<tr>
			                <th align="text-center">Formulario</th>
			                <th align="text-center">F. Emision</th>
			                <th align="text-center">Proveedor</th>
			                <th align="text-center">Tipo</th>
			                <th align="text-center">Serie</th>
			                <th align="text-center">Numero</th>
			                <th align="text-center">Producto</th>
			                <th align="text-center">Cantidad</th>
			                <th align="text-center">C. U.</th>
			                <th align="text-center">Usuario</th>
			                <th colspan="2" align="text-center">Acciones</th>
			            </tr>
				    </thead>
					<tbody>
					<?php
						$i = 0;
						foreach ($response->data as $rows) {
							$color = ($i%2==0?" grid_detalle_par ":" grid_detalle_impar ");
					?>
							<tr class="grid_detalle_impar <?php echo $color; ?>">
	            				<td align="left"><?php echo $rows->nu_formulario; ?></td>
	            				<td align="center" style=" width: 140px;"><?php echo $rows->fe_emision; ?></td>
	            				<td align="left" style=" width: 250px;"><?php echo $rows->no_razon_social; ?></td>
	            				<td align="center"><?php echo $rows->no_tipo_documento; ?></td>
	            				<td align="center"><?php echo $rows->nu_serie_documento; ?></td>
	            				<td align="left"><?php echo $rows->nu_numero_documento; ?></td>
	            				<td align="left" style=" width: 160px;"><?php echo $rows->no_producto; ?></td>
	            				<td align="right"><?php echo number_format($rows->nu_cantidad, 4, '.', ','); ?></td>
	            				<td align="right"><?php echo number_format($rows->nu_costo_unitario, 6, '.', ','); ?></td>
	            				<td align="center"><?php echo $rows->no_usuario; ?></td>
	            				<td align="center">
		                			<a class="button is-info is-small btn-info" title="Ver Cintillo" onclick="verCintillo('<?php echo $rows->nu_formulario;?>', '<?php echo $rows->nu_tipo_movimiento_inventario;?>', '<?php echo $rows->fe_sistema;?>')"><span class="icon is-small"><i class="fa fa-search-plus"></i></span></a>
		            			</td>
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
