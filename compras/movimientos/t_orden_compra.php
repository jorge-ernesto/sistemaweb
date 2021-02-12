<?php
class templateOrderPurchase {
	public function head($data) { ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo (isset($data['title']) ? $data['title'].' - ' : '') ?> Opensoft</title>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<script src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js" type="text/javascript"></script>
	<link href="/sistemaweb/css/sistemaweb.css" rel="stylesheet">
	<link href="/sistemaweb/assets/css/style.css" rel="stylesheet">
	<link href="/sistemaweb/assets/css/jquery-ui.css" rel="stylesheet">
	<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
	<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>
	<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/init.js"></script>
	<script charset="utf-8" type="text/javascript" src="/sistemaweb/compras/js/orden_compra.js"></script>
</head>
<body>
	<?php
	require '/sistemaweb/include/menu.php';
	?>
	</div></div>
	<?php
	}

	public function footer() { ?>
	<footer>
	</footer>
</body>
</html>
	<?php
	}

	public function index($data) {
		$this->head(array('title' => 'Orden de Compra'));
		$consult_initial_date = $data['consult_initial_date'];
		$consult_initial_date = $consult_initial_date['day'].'/'.$consult_initial_date['month'].'/'.$consult_initial_date['year'];
	?>
	<div style="margin: auto; width: 88%; padding: 10px;" align="center">
		<h2 style="text-align: center">Orden de Compra</h2>
		<div>
			<table style="text-align: left;">
				<tr>
					<th>Almacen:</th>
					<th>
						<select class="is-select" id="consult-warehouse">
						<?php
						if ($data['warehouse'] != null) {
							$warehouse = $data['warehouse'];
							for ($i = 0; $i < count($warehouse); $i++) {
								echo '<option value="'.$warehouse[$i]['name'].'">'.$warehouse[$i]['name'].'</option>';
							}
						} else {
							echo '<option value="">-SIN VALOR-</option>';
						}
						?>
						</select>
					</th>
				</tr>
				<tr>
					<th>Fecha:</th>
					<th>
						<input type="text" id="consult-initial-date" class="is-input" value="<?php echo $consult_initial_date ?>">
						<input type="text" id="consult-end-date" class="is-input" value="<?php echo $consult_initial_date ?>">
					</th>
				</tr>
				<tr>
					<th></th>
					<th></th>
				</tr>
			</table>
			<table>
				<tr>
					<th><button class="btn-search-orders">Buscar <img align="right" src="/sistemaweb/icons/gbuscar.png"></button></th>
					<th><button class="btn-ref-new-orders">Agregar <img align="right" src="/sistemaweb/icons/gadd.png"></button></th>
					<th><button class="btn-ref-pdf" onclick="reportResumePDF()">Generar PDF <img align="right" src="/sistemaweb/images/icono_pdf.gif"></button></th>
				</tr>
			</table>
		</div>
		<br>
		<div class="container-result-order"></div>
	</div>
	<?php
	}

	public function renderSearchOrders($data) { ?>
	<div>
		<table class="table-sap-1">
			<thead class="head-table-sap-1">
				<tr>
					<th>Almacen</th>
					<th>Orden ID</th>
					<th>Documento</th>
					<th>Fecha</th>
					<th>Código Pro.</th>
					<th>Nombre Pro.</th>
					<th>Factura Ref.</th>
					<th>Moneda</th>
					<th>T. Cambio</th>
					<th>Importe</th>
					<th>Estado</th>
					<th></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['data'] as $key => $value) { ?>
				<tr>
					<th><?php echo $value['warehouse_name'] ?></th>
					<th><?php echo $value['id'] ?></th>
					<th><?php echo $value['document'] ?></th>
					<th><?php echo $value['created'] ?></th>
					<th><?php echo $value['bpartner_id'] ?></th>
					<th><?php echo $value['bpartner_name'] ?></th>
					<th><?php echo $value['invoice_ref'] ?></th>
					<th><?php echo $value['currency_name'] ?></th>
					<th><?php echo $value['exchange_rate_value'] ?></th>
					<th><?php echo $value['base'] ?></th>
					<th><?php echo $value['status'] ?></th>
					<th><button class="">Imprimir</button></th>
					<th><button class="">Modificar</button></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php
	}

	public function renderPageAddOrder($data) {
		$this->head(array('title' => 'Orden de Compra - Agregar'));
		$date = $data['date'];
		$date = $date['day'].'/'.$date['month'].'/'.$date['year'];
	?>
	<div align="center"><!--Container general-->
		<h3>Agregar Orden de Compra</h3>
		<div><!--Container cabecera-->
			<table align="" border="1">
				<thead>
					<tr>
						<th class="table-label">Almacen</th>
						<th class="table-input">
							<select id="order-warehouse">
							<?php
							//selected is warehouse session
							foreach ($data['warehouses'] as $key => $value) {
								?>
								<option value="<?php echo $value['id'] ?>"><?php echo $value['name'] ?></option>
								<?php
							}
							?>
							</select>
						</th>
						<th class="table-label">Fecha</th>
						<th class="table-input">
							<input type="text" id="order-date" class="is-input" value="<?php echo $date ?>">
						</th>
					</tr>
					<tr>
						<th class="table-label">Orden ID</th>
						<th class="table-input" style="font-weight:bold; font-size: 14px;">
							<input type="hidden" id="hidden-number-order" value="<?php echo $data['nextOrderId']['data'] ?>">
							<strong><?php echo $data['nextOrderId']['data'] ?></strong>
						</th>
						<th class="table-label">Proveedor</th>
						<th class="table-input">
							<input type="text" id="order-bpartner-id" placeholder="Código" size="10" class="required">
							<input type="text" id="order-bpartner-text" placeholder="Ingrese código o nombre" size="40" onkeyup="autocompleteBridge(0)" class="required">
						</th>
					</tr>
					<tr>
						<th class="table-label">Moneda</th>
						<th class="table-input">
							<select id="order-currency">
							<?php
							//selected is warehouse session
							foreach ($data['currency']['data'] as $key => $value) {
								?>
								<option value="<?php echo $value['id'] ?>"><?php echo $value['name'] ?></option>
								<?php
							}
							?>
							</select>
						</th>
						<th class="table-label">T. Cambio</th>
						<th class="table-input"><input type="text" id="order-exangerate" placeholder="" size="10"></th>
					</tr>
					<tr>
						<th class="table-label">Credito</th>
						<th class="table-input">
							<input type="radio" name="is-credit" value="1">
							<label for="is-credit-1">Si</label>
							<input type="radio" name="is-credit" value="0" checked="checked">
							<label for="is-credit-0">No</label>
						</th>
						<th class="table-label">Factura</th>
						<th class="table-input">
							<input type="hidden" id="order-invoice-id">
							<input type="text" id="order-invoice-text" placeholder="Serie - Número" size="20">
						</th>
					</tr>
					<tr>
						<th class="table-label">F. de Pago</th>
						<th class="table-input">
							<select id="order-tendertype">
							<?php
							foreach ($data['tendertype']['data'] as $key => $value) {
								//selecrted is warehouse session
								?>
								<option value="<?php echo $value['id'] ?>"><?php echo $value['name'] ?></option>
								<?php
							}
							?>
							</select>
						</th>
						<th class="table-label">Comentario</th>
						<th class="table-input"><input type="text" id="order-comment" size="50"></th>
					</tr>
					<tr>
						<th class="table-label">F. de Entrega</th>
						<th class="table-input">
							<input type="text" id="order-date-delivery" class="is-input" value="<?php echo $date ?>">
						</th>
						<th class="table-label">Glosa</th>
						<th class="table-input">
							<input type="checkbox" id="order-observation">
							<div class="order-content-observation" style="display:none">
								<textarea name="textarea-observation"></textarea>
								<span class="span-observation text-danger"></span>
							</div>
						</th>
					</tr>
					<tr>
						<th class="table-label">Percepcion</th>
						<th class="table-input">
							<input type="checkbox" id="order-perception" checked="false">
							<div class="order-content-perception" style="display:none">
								<input type="tel" id="order-value-perception" size="6" class="input-number"> %
							</div>
						</th>
						<th class="table-label">Flete</th>
						<th class="table-input"><input type="checkbox" id="order-freight" checked="false"></th>
					</tr>
					<tr class="order-content-freight" style="display:none">
						<th class="table-label">Fecha Entrega</th>
						<th class="table-input"><input type="text" id="order-date-transfer" class="is-input" value="<?php echo $date ?>"></th>
						<th class="table-label">Motivo de traslado</th>
						<th class="table-input">
							<select id="order-reason-transfer">
							<?php
							//selected is warehouse session
							foreach ($data['reasonForTransfer'] as $key => $value) {
								?>
								<option value="<?php echo $value['id'] ?>"><?php echo $value['name'] ?></option>
								<?php
							}
							?>
							</select>
						</th>
					</tr>

					<tr class="order-content-freight" style="display:none">
						<th class="table-label">Placa Vehículo</th>
						<th class="table-input"><input type="text" id="order-plate" class="is-input"><span class="span-plate text-danger"></span></th>
						<th class="table-label">Licencia</th>
						<th class="table-input"><input type="text" id="order-license" class="is-input"><span class="span-license text-danger"></span></th>
					</tr>
					<tr class="order-content-freight" style="display:none">
						<th class="table-label">Certificado de Inscripción</th>
						<th class="table-input"><input type="text" id="order-certificate-inscription" class="is-input"><span class="span-certificate-inscription text-danger"></span></th>
						<th class="table-label">Proveedor Transportista</th>
						<th class="table-input">
							<input type="text" id="order-carrier-id" class="is-input" size="10" placeholder="Código" readonly>
							<input type="text" id="order-carrier-text" class="is-input" placeholder="Ingrese código o nombre" size="40" onkeyup="autocompleteBridge(1)">
							<span class="span-carrier-id text-danger"></span>
						</th>
					</tr>
				</thead>
			</table>
		</div>
		<br>
		<div><!--Container Busqueda de Pedido de mercaderia-->
			<table border="0">
				<thead>
					<tr>
						<th>Pedido de Mercadería:</th>
						<th><input type="text" id="order-merchandise-order-id" placeholder="Nro. Pedido" size="15"><span class="help-block text-danger"></span></th>
						<th><input type="button" class="btn-order-merchandise-order" value="Incluir productos"></th>
					</tr>
				</thead>
			</table>
			<div><input type="hidden" id="include-merchandise-order-id"></div>
		</div>
		<br>
		<div class="msg-process-order" style="width: 50%; margin: 0 auto;"></div>
		<br>
		<div><!--Container detalle-->
			<table id="table-order_purcharse_detail" border="1">
				<thead>
					<tr>
						<td>Código</td>
						<td>Descripción</td>
						<td>Unidad</td>
						<td>Cantidad</td>
						<td>Costo Unitario</td>
						<td>Descuento</td>
						<td>Subtotal</td>
						<td></td>
					</tr>
				</thead>
				<tbody class="order-add-tbody">
				</tbody>
				<tfoot>
					<tr>
						<td><input type="text" id="in-order-product-id" size="18" readonly></td>
						<td><input type="text" id="in-order-product-name" placeholder="Igresar código o nombre" onkeyup="autocompleteBridge(2)" size="32"></td>
						<td><input type="text" id="in-order-product-uom" size="8"></td>
						<td><input type="text" id="in-order-product-quantity" size="10" onkeyup="calcTotal()"></td>
						<td><input type="text" id="in-order-product-unitcost" size="10" onkeyup="calcTotal()"></td>
						<td><input type="text" id="in-order-product-discount" size="10"></td>
						<td><input type="text" id="in-order-product-subtotal" size="10" onkeyup="calcTotal()"></td>
						<td align="center"><input type="button" value="+" onclick="addFormNewProduct()"></td>
					</tr>
				</tfoot>
			</table>
		</div>
		<br>
		<div class="msg-process-purchase" style="width: 50%; margin: 0 auto;"></div>
		<br>
		<div>
			<input type="hidden" id="g_cost_unit" value="0">
			<input type="hidden" id="g_desc" value="0">
			<input type="hidden" id="g_subtotal" value="0">
		</div>
		<div><!--Container controls-->
			<table border="0">
				<thead>
					<tr>
						<th><input type="button" id="btn-save-order" value="Guardar"></th>
						<th><input type="button" class="btn-add-order-cancel" value="Cancelar"></th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
	<?php
	}

	public function renderMerchandiseOrder($data) {
		if ($data['merchandiseOrder']['error']) { ?>
			<tr>
				<th colspan="8">
					<div align="center" class="alert <?php echo $data['merchandiseOrder']['alert']; ?>">
						<div><?php echo $data['merchandiseOrder']['message'] ?></div>
					</div>
				</th>
			</tr><?php
		} else {
			//Código 	Descripción 	Unidad 	Cantidad 	Costo Unitario 	Descuento 	Subtotal-->
			foreach ($data['merchandiseOrder']['data'] as $key => $value) { ?>
			<tr class="it-tr-<?php echo trim($value['art_codigo']) ?>">
				<th><input type="text" id="it-order-product-id[]" value="<?php echo trim($value['art_codigo']) ?>" size="18" readonly></th>
				<th><input type="text" value="<?php echo $value['art_descripcion'] ?>" size="32"></th>
				<th><input type="text" id="it-order-product-uom[]" value="<?php echo $value['art_unidad'] ?>" size="8"></th>
				<th><input type="text" id="in-order-product-quantity[]" value="<?php echo $value['ped_cantidad'] ?>" size="10"></th>
				<th><input type="text" id="in-order-product-unitcost[]" size="10"></th>
				<th><input type="text" id="in-order-product-discount[]" size="10"></th>
				<th><input type="text" id="in-order-product-subtotal[]" size="10"></th>
				<th><button onclick="removeFormNewProduct('<?php echo trim($value['art_codigo']) ?>')">Quitar</button></th>
			</tr>
			<?php
			}
		}
	}

	public function reportResumePDF($reporte_array) {
		require '/sistemaweb/compras/lib/reportes2.inc.php';
		$datos 		= array();
		$Cabecera 	= array( 
			"id" => "Orden",
			"warehouse_name" => "Almacen",
			"bpartner_id" => "Proveedor",
			"bpartner_name" => "Nombre",
			"created" => "Fecha",
			"currency_name" => "Moneda",
			"exchange_rate_value" => "T.Cambio",
			"base" => "Importe",
			"status" => "Estado",
			"invoice_ref" => "Factura"
		);

		$fontsize = 8;
		$reporte = new CReportes2();
		$reporte->SetMargins(5, 5, 5);
		$reporte->SetFont("courier", "", $fontsize);
		$reporte->definirCabecera(10, "L", "Ordenes de Compra");
		$reporte->definirCabecera(10, "R", "Pagina %p");
		$reporte->definirColumna("id", $reporte->TIPO_TEXTO, 7, "L");
		$reporte->definirColumna("warehouse_name", $reporte->TIPO_TEXTO, 16, "L");
		$reporte->definirColumna("bpartner_id", $reporte->TIPO_TEXTO, 7, "L");
		$reporte->definirColumna("bpartner_name", $reporte->TIPO_TEXTO, 28, "L");
		$reporte->definirColumna("created", $reporte->TIPO_TEXTO, 8, "L");
		$reporte->definirColumna("currency_name", $reporte->TIPO_TEXTO, 8, "L");
		$reporte->definirColumna("exchange_rate_value", $reporte->TIPO_TEXTO, 7, "L");
		$reporte->definirColumna("base", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("status", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("invoice_ref", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		$reporte->AddPage();
		foreach($reporte_array['data'] as $llave => $valores) {
			$reporte->nuevaFila($valores);
		}
		header('Content-type: application/pdf');
		$reporte->Output('reporte_ordenes.pdf', 'D');
	}
}
