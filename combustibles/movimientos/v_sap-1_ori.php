<?php
class v_sap_1 {
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
	<script charset="utf-8" type="text/javascript" src="/sistemaweb/combustibles/js/application.sap-1.js"></script>
	<script charset="utf-8" type="text/javascript" src="/sistemaweb/combustibles/js/functions.sap-1.js"></script>	
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
		$this->head(array('title' => 'Interface de Datos SAP'));
		$consult_initial_date = $data['consult_initial_date'];
		$consult_initial_date = $consult_initial_date['day'].'/'.$consult_initial_date['month'].'/'.$consult_initial_date['year'];
	?>
	<div style="margin: auto; width: 88%;padding: 10px;">
		<h2 style="text-align: center">SAP</h2>
		<div class="tab">
			<button class="tablinks active" data-id="consult" title="Consultar y Eliminar días exportados">Consultar</button>
			<button class="tablinks" data-id="export" title="Previualización y Exportación">Exportar</button>
			<button class="tablinks" data-id="configuration" title="Configuración de tablas SAP">Configuración</button>
		</div>
		<div id="consult" class="tabcontent">
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
					<th><input type="text" id="consult-initial-date" class="is-input" value="<?php echo $consult_initial_date ?>"></th>
				</tr>
				<tr>
					<th></th>
					<th><button class="is-btn is-btn-primary btn-consult-exports">Consultar</button></th>
				</tr>
			</table>
			<div class="container-table-exports">
				<?php $this->tableDayExporter($data); ?>
			</div>
		</div>
		<div id="export" class="tabcontent none">
			<table style="text-align: left;">
				<tr>
					<th>Almacen:</th>
					<th>
						<select class="is-select" id="export-warehouse">
						<?php
						if ($data['warehouse'] != null) {
							$warehouse = $data['warehouse'];
							for ($i = 0; $i < count($warehouse); $i++) {
								echo '<option value="'.$warehouse[$i]['id'].'">'.$warehouse[$i]['name'].'</option>';
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
					<th><input type="text" id="export-initial-date" class="is-input" value="<?php echo $consult_initial_date ?>"></th>
				</tr>
				<tr>
					<th></th>
					<th class="container-btn-export">
						<button class="is-btn is-btn-default preview-export">Previsualizar</button>
						<button class="is-btn is-btn-default send-export">Exportar</button>
					</th>
				</tr>
			</table>
			<div class="container-preview"></div>
		</div>
		<div id="configuration" class="tabcontent none">		
			<table style="text-align: left;">
				<tr>
					<th>Tabla:</th>
					<th>
						<select class="is-select" id="consult-table">
						<?php
						if ($data['tableConfiguration'] != null) {
							$tableConfiguration = $data['tableConfiguration'];
							for ($i = 0; $i < count($tableConfiguration); $i++) {
								echo '<option value="'.$tableConfiguration[$i]['id_tipo_tabla'].'">'.$tableConfiguration[$i]['id_tipo_tabla'].' - '.$tableConfiguration[$i]['no_tabla'].'</option>';
							}
						} else {
							echo '<option value="">-SIN VALOR-</option>';
						}
						?>
						</select>
					</th>
				</tr>
				<tr>
					<th></th>
					<th class="container-btn-configuration-table">
						<button class="is-btn is-btn-primary consult-configuration-table">Consultar</button>
						<!-- Requerimiento CRUD -->
						<input type="hidden" class="es_requerimiento_sap_energigas" value="<?php echo $_SESSION['es_requerimiento_sap_energigas'] ?>">
						<button class="is-btn is-btn-primary btn-add-almacen" style="display: none;">Crear Almacen</button>
						<button class="is-btn is-btn-primary btn-add-centro-costo" style="display: none;">Crear Centro Costo</button>
						<button class="is-btn is-btn-primary btn-add-tarjeta-credito" style="display: none;">Crear Tarjeta Credito</button>
						<button class="is-btn is-btn-primary btn-add-fondo-efectivo" style="display: none;">Crear Fondo Efectivo</button>						
					</th>					
				</tr>
			</table>
			<div class="container-table-configuration"></div>
		</div>
	</div>
	<br>
	<?php
		$this->footer();
	}

	public function tableDayExporter($data) {
		?>
		<table class="table-sap-1">
			<thead class="head-table-sap-1">
				<tr>
					<th>Almacen</th>
					<th>Fecha exportación</th>
					<th>Creado en</th>
					<th>Creado por</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['exports'] as $key => $value) { ?>
				<tr>
					<th><?php echo $value['warehouse_code'] ?></th>
					<th><?php echo $value['systemdate'] ?></th>
					<th><?php echo $value['created'] ?></th>
					<th><?php echo $value['username'] ?></th>
					<th><button class="remove-export" data-id="<?php echo $value['id'] ?>">Eliminar</button></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php
	}

	public function statusRequest($text, $data) { ?>

	<br>
	<div align="center" class="alert <?php echo ($data['error'] ? 'alert-danger' : 'alert-info') ?>">
		<div><?php echo $text.''.$data['code'].']: '.$data['message'] ?></div>
	</div>

	<?php
	}

	/**
	 * Table Head Configuration SAP
	 */
	public function viewDetailTableConfiguration($data) { ?>	

	<div class="container-table-preview" id="divTablaEquivalencias">
		<h3>Tabla de equivalencias OCS - SAP</h3><hr>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>internal_id</th>
					<th>name</th>
					<th>ocs_id</th>
					<th>sap_id</th>	
					<?php if($_SESSION['es_requerimiento_sap_energigas'] == true): ?>				
					<th class="update" style="display: none;">update</th>
					<th class="delete" style="display: none;">delete</th>		
					<?php endif; ?>			
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailTableConfiguration'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['id_tipo_tabla_detalle'] ?></th>
					<th><?php echo $row['name'] ?></th>
					<th><?php echo $row['opencomb_codigo'] ?></th>
					<th><?php echo $row['sap_codigo'] ?></th>					
					<?php if($_SESSION['es_requerimiento_sap_energigas'] == true): ?>				
					<th><button class="is-btn is-btn-primary buscar" style="display: none;" value="<?php echo "".$row['id_tipo_tabla'].",".$row['id_tipo_tabla_detalle']."" ?>">Actualizar</button></th>
					<th><button class="is-btn is-btn-primary eliminar" style="display: none;" value="<?php echo "".$row['id_tipo_tabla'].",".$row['id_tipo_tabla_detalle']."" ?>">Eliminar</button></th>					
					<?php endif; ?>			
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	</div>	

	<!-- Requerimiento CRUD -->
	<!-- Formulario Centro Costo -->	
	<div id="divFormularioCentroCosto" style="display: none;">		
		<h3>Centro Costo</h3>
		<hr>
		<form id="formularioCentroCosto">
			<table style="text-align: left;">	
				<tbody>
					<tr>
						<!-- Cambiar segun la tabla -->
						<!-- En la tabla sap_mapeo_tabla, el codigo para Centro Costo es 1 -->
						<input type="hidden" name="id-tabla-centro-costo" value="1" class="form-control">
						<input type="hidden" id="id-centro-costo" name="id-centro-costo" value="" class="form-control">						
					</tr>
					<tr>
						<th>Nombre:</th>
						<th><input type="text" id="nombre-centro-costo" name="nombre-centro-costo" value="" class="is-input"></th>					
					</tr>
					<tr>
						<th>Centro Costo:</th>
						<th>
							<select class="is-select" id="consult-warehouse-centro-costo" name="consult-warehouse-centro-costo">
							<?php
							if ($_SESSION['sucursal'] != null) {
								$sucursal = $_SESSION['sucursal'];
								for ($i = 0; $i < count($sucursal); $i++) {
									echo '<option value="'.$sucursal[$i]['id'].'">'.$sucursal[$i]['name'].'</option>';
								}
							} else {
								echo '<option value="">-SIN VALOR-</option>';
							}
							?>
							</select>
						</th>
					</tr>
					<tr>
						<th>Codigo SAP:</th>
						<th><input type="text" id="codigo-sap-centro-costo" name="codigo-sap-centro-costo" value="" class="is-input"></th>					
					</tr>							
					<tr>
						<th></th>
						<th>							
							<button class="is-btn is-btn-default crear-centro-costo">Crear</button> <!-- is-btn-default -->
							<button class="is-btn is-btn-default back">Atras</button> <!-- is-btn-default -->
						</th>
					</tr>
				</tbody>
			</table>
		</form>		
	</div>

	<!-- Formulario Centro Almacen -->	
	<div id="divFormularioAlmacen" style="display: none;">		
		<h3>Almacen</h3>
		<hr>
		<form id="formularioAlmacen">
			<table style="text-align: left;">	
				<tbody>
					<tr>
						<!-- Cambiar segun la tabla -->
						<!-- En la tabla sap_mapeo_tabla, el codigo para Almacen es 2 -->
						<input type="hidden" name="id-tabla-almacen" value="2" class="form-control">
						<input type="hidden" id="id-almacen" name="id-almacen" value="" class="form-control">						
					</tr>
					<tr>
						<th>Nombre:</th>
						<th><input type="text" id="nombre-almacen" name="nombre-almacen" value="" class="is-input"></th>					
					</tr>
					<tr>
						<th>Almacen:</th>
						<th>
							<select class="is-select" id="consult-warehouse-almacen" name="consult-warehouse-almacen">
							<?php
							if ($_SESSION['warehouse'] != null) {
								$warehouse = $_SESSION['warehouse'];
								for ($i = 0; $i < count($warehouse); $i++) {
									echo '<option value="'.$warehouse[$i]['id'].'">'.$warehouse[$i]['name'].'</option>';
								}
							} else {
								echo '<option value="">-SIN VALOR-</option>';
							}
							?>
							</select>
						</th>
					</tr>
					<tr>
						<th>Codigo SAP:</th>
						<th><input type="text" id="codigo-sap-almacen" name="codigo-sap-almacen" value="" class="is-input"></th>					
					</tr>							
					<tr>
						<th></th>
						<th>							
							<button class="is-btn is-btn-default crear-almacen">Crear</button> <!-- is-btn-default -->
							<button class="is-btn is-btn-default back">Atras</button> <!-- is-btn-default -->
						</th>
					</tr>
				</tbody>
			</table>
		</form>		
	</div>
	
	<!-- Formulario Tarjeta Credito -->	
	<div id="divFormularioTarjetaCredito" style="display: none;">		
		<h3>Tarjeta Credito</h3>
		<hr>
		<form id="formularioTarjetaCredito">
			<table style="text-align: left;">	
				<tbody>
					<tr>
						<!-- Cambiar segun la tabla -->
						<!-- En la tabla sap_mapeo_tabla, el codigo para Tarjeta Credito es 4 -->
						<input type="hidden" name="id-tabla-tarjeta-credito" value="4" class="form-control">
						<input type="hidden" id="id-tarjeta-credito" name="id-tarjeta-credito" value="" class="form-control">						
					</tr>
					<tr>
						<th>Nombre:</th>
						<th><input type="text" id="nombre-tarjeta-credito" name="nombre-tarjeta-credito" value="" class="is-input"></th>					
					</tr>
					<tr>
						<th>Tarjeta Credito:</th>
						<th>
							<select class="is-select" id="consult-tarjeta-credito" name="consult-tarjeta-credito">
							<?php
							if ($_SESSION['tarjetaCredito'] != null) {
								$tarjetaCredito = $_SESSION['tarjetaCredito'];
								for ($i = 0; $i < count($tarjetaCredito); $i++) {
									echo '<option value="'.$tarjetaCredito[$i]['tab_elemento'].'">'.$tarjetaCredito[$i]['tab_descripcion'].'</option>';
								}
							} else {
								echo '<option value="">-SIN VALOR-</option>';
							}
							?>
							</select>
						</th>
					</tr>
					<tr>
						<th>Codigo SAP:</th>
						<th><input type="text" id="codigo-sap-tarjeta-credito" name="codigo-sap-tarjeta-credito" value="" class="is-input"></th>					
					</tr>							
					<tr>
						<th></th>
						<th>							
							<button class="is-btn is-btn-default crear-tarjeta-credito">Crear</button> <!-- is-btn-default -->
							<button class="is-btn is-btn-default back">Atras</button> <!-- is-btn-default -->
						</th>
					</tr>
				</tbody>
			</table>
		</form>		
	</div>

	<!-- Formulario Fondo Efectivo -->	
	<div id="divFormularioFondoEfectivo" style="display: none;">		
		<h3>Fondo Efectivo</h3>
		<hr>
		<form id="formularioFondoEfectivo">
			<table style="text-align: left;">	
				<tbody>
					<tr>
						<!-- Cambiar segun la tabla -->
						<!-- En la tabla sap_mapeo_tabla, el codigo para Fondo Efectivo es 5 -->
						<input type="hidden" name="id-tabla-fondo-efectivo" value="5" class="form-control">
						<input type="hidden" id="id-fondo-efectivo" name="id-fondo-efectivo" value="" class="form-control">						
					</tr>
					<tr>
						<th>Nombre:</th>
						<th><input type="text" id="nombre-fondo-efectivo" name="nombre-fondo-efectivo" value="" class="is-input"></th>					
					</tr>
					<tr>
						<th>Fondo Efectivo:</th>
						<th>
							<select class="is-select" id="consult-fondo-efectivo" name="consult-fondo-efectivo">
								<option value="01">01</option>
								<option value="02">02</option>
							</select>
						</th>
					</tr>
					<tr>
						<th>Codigo SAP:</th>
						<th><input type="text" id="codigo-sap-fondo-efectivo" name="codigo-sap-fondo-efectivo" value="" class="is-input"></th>					
					</tr>							
					<tr>
						<th></th>
						<th>							
							<button class="is-btn is-btn-default crear-fondo-efectivo">Crear</button> <!-- is-btn-default -->
							<button class="is-btn is-btn-default back">Atras</button> <!-- is-btn-default -->
						</th>
					</tr>
				</tbody>
			</table>
		</form>		
	</div>

	<?php }

	/**
	 * Clientes
	 */
	public function tableBpartner($data) { ?>

	<div class="container-table-preview">
		<h3>Socios de Negocios <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>cardcode</th>
					<th>cardname</th>
					<th>federaltaxid</th>
					<th>u_exx_tipopers</th>
					<th>u_exx_tipodocu</th>
					<th>u_exx_apellpat</th>
					<th>u_exx_apellmat</th>
					<th>u_exx_primerno</th>
					<th>u_exx_segundno</th>
					<th>phone</th>
					<th>email</th>
					<th>street</th>
					<th>name</th>
					<th>lastname</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( $data['sStatus']=='success' ) {
					foreach ($data['bpartner'] as $key => $row) {
				?>
				<tr>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['cardname'] ?></th>
					<th><?php echo $row['federaltaxid'] ?></th>
					<th><?php echo $row['u_exx_tipopers'] ?></th>
					<th><?php echo $row['u_exx_tipodocu'] ?></th>
					<th><?php echo $row['u_exx_apellpat'] ?></th>
					<th><?php echo $row['u_exx_apellmat'] ?></th>
					<th><?php echo $row['u_exx_primerno'] ?></th>
					<th><?php echo $row['u_exx_segundno'] ?></th>
					<th><?php echo $row['phone'] ?></th>
					<th><?php echo $row['email'] ?></th>
					<th><?php echo $row['street'] ?></th>
					<th><?php echo $row['name'] ?></th>
					<th><?php echo $row['lastname'] ?></th>
				</tr>
				<?php
					}// ./ Foreach
				} else { ?>
					<tr>
						<th colspan="14"><?php echo $data['sMessage'] ?></th>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Empleados
	 */
	public function tableEmployee($data) { ?>

	<br>
	<div>
		<h3>Empleados <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>extempno</th>
					<th>name</th>
					<th>lastname</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['employee'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['name'] ?></th>
					<th><?php echo $row['lastname'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * =============================================
	 * Contado
	 * Venta sin guia (Uno a uno)
	 *
	 * Venta al contado [CABECERA]
	 */
	public function tableInvoiceHeaderSaleCash($data) { ?>

	<br>
	<div>
		<h3>Factura Cliente (Venta Contado) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>		
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceheadersalecash'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>indicator</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
					<th>doccur</th>
				</tr>
			</thead>
			<tbody>			
				<?php foreach ($data['invoiceheadersalecash'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['indicador'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
					<th><?php echo $row['doccur'] ?></th>
				</tr>
				<?php } ?>				
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * =============================================
	 * Contado
	 * Venta sin guia (Uno a uno)
	 *
	 * Venta al contado [CABECERA]
	 */
	public function tableInvoiceHeaderSaleCashWithFechaEmision($data) { ?>

	<br>
	<div>
		<h3>Factura Cliente (Venta Contado) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>		
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceheadersalecash'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>indicator</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
					<th>doccur</th>
					<th>u_exc_fechaemi</th>
				</tr>
			</thead>
			<tbody>			
				<?php foreach ($data['invoiceheadersalecash'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['indicador'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
					<th><?php echo $row['doccur'] ?></th>
					<th><?php echo $row['u_exc_fechaemi'] ?></th>
				</tr>
				<?php } ?>				
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Venta al contado [DETALLE]
	 */
	public function tableInvoiceDetailSaleCash($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Factura Cliente – Detalle (Venta Contado) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceDetailSaleCash'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>u_exc_nrotarjbonus</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceDetailSaleCash'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>

					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjbonus'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Pago recibido de venta contado
	 */
	public function tablePaymentSaleCash($data) { ?>

	<br>
	<div>
		<h3>Pago Recibido (Venta Contado) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacionpe IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacionpe IN (";			
				foreach($data['paymentSaleCash'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacionpe</th>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>doctotal</th>
					<th>moneda</th>
					<th>fecuenta</th>
					<th>femoneda</th>
					<th>fetc</th>
					<th>femonto</th>
					<th>fecuentav</th>
					<th>tccod</th>
					<th>tccuenta</th>
					<th>tcnumero</th>
					<th>tcid</th>
					<th>tcvalido</th>
					<th>tcmonto</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['paymentSaleCash'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacionpe'] ?></th>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>

					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['moneda'] ?></th>
					<th><?php echo $row['fecuenta'] ?></th>
					<th><?php echo $row['femoneda'] ?></th>
					<th><?php echo $row['fetc'] ?></th>
					<th><?php echo $row['femonto'] ?></th>
					<th><?php echo $row['fecuentav'] ?></th>
					<th><?php echo $row['tccod'] ?></th>
					<th><?php echo $row['tccuenta'] ?></th>
					<th><?php echo $row['tcnumero'] ?></th>
					<th><?php echo $row['tcid'] ?></th>
					<th><?php echo $row['tcvalido'] ?></th>
					<th><?php echo $row['tcmonto'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * =============================================
	 * Efectivo
	 * Venta con guia (varias guías - liquidación)
	 *
	 * Guía cliente - Efectivo [CABECERA]
	 */
	public function tableShipmentHeaderSaleEffective($data) { ?>

	<br>
	<div>
		<h3>Guía Cliente (Venta Cliente Pago Efectivo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['shipmentHeaderSaleEffective'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>folioref</th>
					<th>folionum</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['shipmentHeaderSaleEffective'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>

					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Guía cliente - Efectivo [DETALLE]
	 */
	public function tableShipmentDetailSaleEffective($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Guía Cliente – Detalle (Venta Cliente Pago Efectivo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['shipmentDetailSaleEffective'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>u_exc_nrotarjbonus</th>
					<th>u_exc_nrotarjmag</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['shipmentDetailSaleEffective'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>

					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjbonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarmag'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Factura cliente - Efectivo [CABECERA]
	 */
	public function tableInvoiceHeaderSaleEffective($data) { ?>

	<br>
	<div>
		<h3>Factura Cliente (Venta Cliente Pago Efectivo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceHeaderSaleEffective'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceHeaderSaleEffective'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Factura cliente - Efectivo [DETALLE]
	 */
	public function tableInvoiceDetailSaleEffective($data) { ?>
	<br>
	<div>
		<h3>Factura Cliente – Detalle (Venta Cliente Pago Efectivo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceDetailSaleEffective'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemref</th>
					<th>noperacionref</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceDetailSaleEffective'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemref'] ?></th>
					<th><?php echo $row['noperacionref'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Pago cliente - Efectivo
	 */
	public function tablePaymentSaleEffective($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Pago Recibido (Venta Cliente Pago Efectivo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacionpe IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacionpe IN (";			
				foreach($data['paymentSaleEffective'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacionpe</th>
					<th>noperacion</th>
					<th>docdate</th>
					<th>doctotal</th>
					<th>moneda</th>
					<th>fecuenta</th>
					<th>femoneda</th>
					<th>fetc</th>
					<th>femonto</th>
					<th>fecuentav</th>
					<th>tccod</th>
					<th>tccuenta</th>
					<th>tcnumero</th>
					<th>tcid</th>
					<th>tcvalido</th>
					<th>tcmonto</th>
					<th>bcuenta</th>
					<th>breferencia</th>
					<th>bfecha</th>
					<th>bmonto</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['paymentSaleEffective'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacionpe'] ?></th>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['moneda'] ?></th>
					<th><?php echo $row['fecuenta'] ?></th>
					<th><?php echo $row['femoneda'] ?></th>
					<th><?php echo $row['fetc'] ?></th>
					<th><?php echo $row['femonto'] ?></th>
					<th><?php echo $row['fecuentav'] ?></th>
					<th><?php echo $row['tccod'] ?></th>
					<th><?php echo $row['tccuenta'] ?></th>
					<th><?php echo $row['tcnumero'] ?></th>
					<th><?php echo $row['tcid'] ?></th>
					<th><?php echo $row['tcvalido'] ?></th>
					<th><?php echo $row['tcmonto'] ?></th>
					<th><?php echo $row['bcuenta'] ?></th>
					<th><?php echo $row['breferencia'] ?></th>
					<th><?php echo $row['bfecha'] ?></th>
					<th><?php echo $row['bmonto'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }


	/**
	 * =============================================
	 * Credito
	 * Venta con guia (varias guías - Pago al final)
	 *
	 * Guía cliente - Credito [CABECERA]
	 */
	public function tableShipmentHeaderSaleCredit($data) { ?>

	<br>
	<div>
		<h3>Guía Cliente (Venta Cliente Pago Credito) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['shipmentHeaderSaleCredit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>folioref</th>
					<th>folionum</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['shipmentHeaderSaleCredit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>

					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * =============================================
	 * Credito
	 * Venta con guia (varias guías - Pago al final)
	 *
	 * Guía cliente - Credito [CABECERA]
	 */
	public function tableShipmentHeaderSaleCreditWithFechaEmision($data) { ?>

	<br>
	<div>
		<h3>Guía Cliente (Venta Cliente Pago Credito) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['shipmentHeaderSaleCredit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>folioref</th>
					<th>folionum</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
					<th>u_exc_fechaemi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['shipmentHeaderSaleCredit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>

					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
					<th><?php echo $row['u_exc_fechaemi'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Guía cliente - Credito [DETALLE]
	 */
	public function tableShipmentDetailSaleCredit($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Guía Cliente – Detalle (Venta Cliente Pago Credito) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['shipmentDetailSaleCredit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>u_exc_nrotarjbonus</th>
					<th>u_exc_nrotarjmag</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['shipmentDetailSaleCredit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>

					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjbonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjmag'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Factura cliente - Credito [CABECERA]
	 */
	public function tableInvoiceHeaderSaleCredit($data) { ?>

	<br>
	<div>
		<h3>Factura Cliente (Venta Cliente Pago Credito) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceHeaderSaleCredit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>indicator</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceHeaderSaleCredit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['indicator'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Factura cliente - Credito [DETALLE]
	 */
	public function tableInvoiceDetailSaleCredit($data) { ?>
	<br>
	<div>
		<h3>Factura Cliente – Detalle (Venta Cliente Pago Credito) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceDetailSaleCredit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemref</th>
					<th>noperacionref</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceDetailSaleCredit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemref'] ?></th>
					<th><?php echo $row['noperacionref'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Pago cliente - Credito
	 */
	public function tablePaymentSaleCredit($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Pago Recibido (Venta Cliente Pago Credito) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['paymentSaleCredit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacionpe</th>
					<th>noperacion</th>
					<th>docdate</th>
					<th>doctotal</th>
					<th>moneda</th>
					<th>fecuenta</th>
					<th>femoneda</th>
					<th>fetc</th>
					<th>femonto</th>
					<th>fecuentav</th>
					<th>tccod</th>
					<th>tccuenta</th>
					<th>tcnumero</th>
					<th>tcid</th>
					<th>tcvalido</th>
					<th>tcmonto</th>
					<th>bcuenta</th>
					<th>breferencia</th>
					<th>bfecha</th>
					<th>bmonto</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['paymentSaleCredit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacionpe'] ?></th>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['moneda'] ?></th>
					<th><?php echo $row['fecuenta'] ?></th>
					<th><?php echo $row['femoneda'] ?></th>
					<th><?php echo $row['fetc'] ?></th>
					<th><?php echo $row['femonto'] ?></th>
					<th><?php echo $row['fecuentav'] ?></th>
					<th><?php echo $row['tccod'] ?></th>
					<th><?php echo $row['tccuenta'] ?></th>
					<th><?php echo $row['tcnumero'] ?></th>
					<th><?php echo $row['tcid'] ?></th>
					<th><?php echo $row['tcvalido'] ?></th>
					<th><?php echo $row['tcmonto'] ?></th>
					<th><?php echo $row['bcuenta'] ?></th>
					<th><?php echo $row['breferencia'] ?></th>
					<th><?php echo $row['bfecha'] ?></th>
					<th><?php echo $row['bmonto'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }






	/**
	 * =============================================
	 * Anticipo
	 * Venta con guia (varias guías - Pago al final)
	 * Factura cliente - Anticipo [CABECERA]
	 */
	public function tableInvoiceHeaderSaleAnticipationInit($data) { ?>

	<br>
	<div>
		<h3>Factura Cliente (Venta Cliente Pago Anticipo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceHeaderSaleAnticipationInit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceHeaderSaleAnticipationInit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Factura cliente - Anticipo [DETALLE]
	 */
	public function tableInvoiceDetailSaleAnticipationInit($data) { ?>
	<br>
	<div>
		<h3>Factura Cliente – Detalle (Venta Cliente Pago Anticipo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceDetailSaleAnticipationInit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_turno</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceDetailSaleAnticipationInit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	public function tableInvoiceHeaderSaleAnticipation($data) { ?>

	<br>
	<div>
		<h3>Factura Cliente (Venta Cliente Pago Anticipo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceHeaderSaleAnticipation'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceHeaderSaleAnticipation'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Factura cliente - Anticipo [DETALLE]
	 */
	public function tableInvoiceDetailSaleAnticipation($data) { ?>
	<br>
	<div>
		<h3>Factura Cliente – Detalle (Venta Cliente Pago Anticipo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['invoiceDetailSaleAnticipation'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_turno</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['invoiceDetailSaleAnticipation'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Pago cliente - Anticipo
	 */
	public function tablePaymentSaleAnticipation($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Pago Recibido (Venta Cliente Pago Anticipo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['paymentSaleAnticipation'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacionpe</th>
					<th>noperacion</th>
					<th>docdate</th>
					<th>doctotal</th>
					<th>moneda</th>
					<th>fecuenta</th>
					<th>femoneda</th>
					<th>fetc</th>
					<th>femonto</th>
					<th>fecuentav</th>
					<th>tccod</th>
					<th>tccuenta</th>
					<th>tcnumero</th>
					<th>tcid</th>
					<th>tcvalido</th>
					<th>tcmonto</th>
					<th>bcuenta</th>
					<th>breferencia</th>
					<th>bfecha</th>
					<th>bmonto</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['paymentSaleAnticipation'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacionpe'] ?></th>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['moneda'] ?></th>
					<th><?php echo $row['fecuenta'] ?></th>
					<th><?php echo $row['femoneda'] ?></th>
					<th><?php echo $row['fetc'] ?></th>
					<th><?php echo $row['femonto'] ?></th>
					<th><?php echo $row['fecuentav'] ?></th>
					<th><?php echo $row['tccod'] ?></th>
					<th><?php echo $row['tccuenta'] ?></th>
					<th><?php echo $row['tcnumero'] ?></th>
					<th><?php echo $row['tcid'] ?></th>
					<th><?php echo $row['tcvalido'] ?></th>
					<th><?php echo $row['tcmonto'] ?></th>
					<th><?php echo $row['bcuenta'] ?></th>
					<th><?php echo $row['breferencia'] ?></th>
					<th><?php echo $row['bfecha'] ?></th>
					<th><?php echo $row['bmonto'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Guía cliente - Anticipo [CABECERA]
	 */
	public function tableShipmentHeaderSaleAnticipation($data) { ?>

	<br>
	<div>
		<h3>Guía Cliente (Venta Cliente Pago Anticipo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['shipmentHeaderSaleAnticipation'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>folioref</th>
					<th>folionum</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['shipmentHeaderSaleAnticipation'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>

					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Guía cliente - Anticipo [DETALLE]
	 */
	public function tableShipmentDetailSaleAnticipation($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Guía Cliente – Detalle (Venta Cliente Pago Anticipo) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['shipmentDetailSaleAnticipation'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>u_exc_nrotarjbonus</th>
					<th>u_exc_nrotarjmag</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['shipmentDetailSaleAnticipation'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>

					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjbonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjmag'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }







	/**
	 * Boletas cabecera
	 * 
	 */
	public function tableDocumentHeadTicket($data) { ?>

	<br>
	<div>
		<h3>Boleta (Venta Cliente) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['documentHeadTicket'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>u_exx_nroini</th>
					<th>u_exx_nrofin</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>transaccion</th>
					<th>indicator</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['documentHeadTicket'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['u_exx_nroini'] ?></th>
					<th><?php echo $row['u_exx_nrofin'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['transaccion'] ?></th>
					<th><?php echo $row['indicator'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Boletas detalle [DETALLE]
	 */
	public function tableDocumentDetailTicket($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Boleta Detalle (Venta Cliente) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['documentDetailTicket'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>u_exc_nrotarjbonus</th>
					<th>u_exc_serie</th>
					<th>u_exc_numero</th>
					<th>u_exc_ticket</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['documentDetailTicket'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>

					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjbonus'] ?></th>
					<th><?php echo $row['u_exc_serie'] ?></th>
					<th><?php echo $row['u_exc_numero'] ?></th>
					<th><?php echo $row['u_exc_ticket'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Boletas detalle [DETALLE]
	 */
	public function tableDocumentDetailTicketWithFechaEmision($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Boleta Detalle (Venta Cliente) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['documentDetailTicket'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>u_exc_nrotarjbonus</th>
					<th>u_exc_serie</th>
					<th>u_exc_numero</th>
					<th>u_exc_ticket</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
					<th>u_exc_fechaemi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['documentDetailTicket'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>

					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjbonus'] ?></th>
					<th><?php echo $row['u_exc_serie'] ?></th>
					<th><?php echo $row['u_exc_numero'] ?></th>
					<th><?php echo $row['u_exc_ticket'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
					<th><?php echo $row['u_exc_fechaemi'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Pago recibido de venta contado
	 */
	public function tablePaymentDocumentTicket($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Pago Recibido (Venta Boleta) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacionpe IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacionpe IN (";			
				foreach($data['paymentDocumentTicket'] as $key=>$row){
					$sql_data .= "'" . $row['noperacionpe'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacionpe</th>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>doctotal</th>
					<th>moneda</th>
					<th>fecuenta</th>
					<th>femoneda</th>
					<th>fetc</th>
					<th>femonto</th>
					<th>fecuentav</th>
					<th>tccod</th>
					<th>tccuenta</th>
					<th>tcnumero</th>
					<th>tcid</th>
					<th>tcvalido</th>
					<th>tcmonto</th>
					<th>foliopref</th>
					<th>folionum</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['paymentDocumentTicket'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacionpe'] ?></th>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>

					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['moneda'] ?></th>
					<th><?php echo $row['fecuenta'] ?></th>
					<th><?php echo $row['femoneda'] ?></th>
					<th><?php echo $row['fetc'] ?></th>
					<th><?php echo $row['femonto'] ?></th>
					<th><?php echo $row['fecuentav'] ?></th>
					<th><?php echo $row['tccod'] ?></th>
					<th><?php echo $row['tccuenta'] ?></th>
					<th><?php echo $row['tcnumero'] ?></th>
					<th><?php echo $row['tcid'] ?></th>
					<th><?php echo $row['tcvalido'] ?></th>
					<th><?php echo $row['tcmonto'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Pago recibido de venta contado
	 */
	public function tablePaymentDocumentTicketGroupByTurnoAndEfectivo($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Pago Recibido (Venta Boleta) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacionpe IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacionpe IN (";			
				foreach($data['paymentDocumentTicket'] as $key=>$row){
					$sql_data .= "'" . $row['noperacionpe'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacionpe</th>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>doctotal</th>
					<th>moneda</th>
					<th>fecuenta</th>
					<th>femoneda</th>
					<th>fetc</th>
					<th>femonto</th>
					<th>fecuentav</th>
					<th>tccod</th>
					<th>tccuenta</th>
					<th>tcnumero</th>
					<th>tcid</th>
					<th>tcvalido</th>
					<th>tcmonto</th>
					<th>foliopref</th>
					<th>folionum</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['paymentDocumentTicket'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacionpe'] ?></th>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>

					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['moneda'] ?></th>
					<th><?php echo $row['fecuenta'] ?></th>
					<th><?php echo $row['femoneda'] ?></th>
					<th><?php echo $row['fetc'] ?></th>
					<th><?php echo $row['femonto'] ?></th>
					<th><?php echo $row['fecuentav'] ?></th>
					<th><?php echo $row['tccod'] ?></th>
					<th><?php echo $row['tccuenta'] ?></th>
					<th><?php echo $row['tcnumero'] ?></th>
					<th><?php echo $row['tcid'] ?></th>
					<th><?php echo $row['tcvalido'] ?></th>
					<th><?php echo $row['tcmonto'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Contometer
	 */
	public function tableContometer($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Contómetro <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['contometer'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>u_exc_fecha</th>
					<th>u_exc_turno</th>
					<th>u_exc_articulo</th>
					<th>u_exc_continicial</th>
					<th>u_exc_contfinal</th>
					<th>u_exc_manguera</th>
					<th>ocrcode</th>
					<th>u_exc_caja</th>
					<th>u_exc_lado</th>
					<th>u_exc_cont</th>
					<th>u_exc_tipo</th>
					<th>u_exc_continicialgal</th>
					<th>u_exc_contfinalgal</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['contometer'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['u_exc_fecha'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>

					<th><?php echo $row['u_exc_articulo'] ?></th>
					<th><?php echo $row['u_exc_continicial'] ?></th>
					<th><?php echo $row['u_exc_contfinal'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_lado'] ?></th>
					<th><?php echo $row['u_exc_cont'] ?></th>
					<th><?php echo $row['u_exc_tipo'] ?></th>
					<th><?php echo $row['u_exc_continicialgal'] ?></th>
					<th><?php echo $row['u_exc_contfinalgal'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Change Price
	 */
	public function tableChangePrice($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Cambio de Precio <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['changePrice'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>u_exc_fecha</th>
					<th>u_exc_turno</th>
					<th>u_exc_precio</th>
					<th>u_exc_tipoprod</th>
					<th>ocrcode</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['changePrice'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['u_exc_fecha'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>

					<th><?php echo $row['u_exc_precio'] ?></th>
					<th><?php echo $row['u_exc_tipoprod'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Bonus
	 */
	public function tableBonus($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Bonus <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['bonus'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>u_exc_fecha</th>
					<th>u_exc_ntarjeta</th>
					<th>u_exc_hora</th>
					<th>u_exc_cantpnts</th>
					<th>u_exc_tickets</th>
					<th>cardcode</th>
					<th>extempno</th>
					<th>ocrcode</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['bonus'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['u_exc_fecha'] ?></th>
					<th><?php echo $row['u_exc_ntarjeta'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_cantpnts'] ?></th>
					<th><?php echo $row['u_exc_tickets'] ?></th>

					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Deposito
	 */
	public function tableDeposit($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Depósitos <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['deposit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>u_exc_fecha</th>
					<th>u_exc_turno</th>
					<th>u_exc_monto</th>
					<th>u_exc_tipo</th>
					<th>u_exc_falt</th>
					<th>extempno</th>
					<th>ocrcode</th>
					<th>u_exc_caja</th>
					<th>u_exc_moneda</th>
					<th>u_exc_tc</th>
					<th>u_exc_ncomprobante</th>
					<th>u_exc_denominacion</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['deposit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['u_exc_fecha'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_monto'] ?></th>
					<th><?php echo $row['u_exc_tipo'] ?></th>
					<th><?php echo $row['u_exc_falt'] ?></th>

					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>

					<th><?php echo $row['u_exc_moneda'] ?></th>
					<th><?php echo $row['u_exc_tc'] ?></th>
					<th><?php echo $row['u_exc_ncomprobante'] ?></th>
					<th><?php echo $row['u_exc_denominacion'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Deposito
	 */
	public function tableDepositWithFechaSistema($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Depósitos <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['deposit'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>u_exc_fecha</th>
					<th>u_exc_turno</th>
					<th>u_exc_monto</th>
					<th>u_exc_tipo</th>
					<th>u_exc_falt</th>
					<th>extempno</th>
					<th>ocrcode</th>
					<th>u_exc_caja</th>
					<th>u_exc_moneda</th>
					<th>u_exc_tc</th>
					<th>u_exc_ncomprobante</th>
					<th>u_exc_denominacion</th>
					<th>u_exc_fechaturno</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['deposit'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['u_exc_fecha'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_monto'] ?></th>
					<th><?php echo $row['u_exc_tipo'] ?></th>
					<th><?php echo $row['u_exc_falt'] ?></th>

					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>

					<th><?php echo $row['u_exc_moneda'] ?></th>
					<th><?php echo $row['u_exc_tc'] ?></th>
					<th><?php echo $row['u_exc_ncomprobante'] ?></th>
					<th><?php echo $row['u_exc_denominacion'] ?></th>
					<th><?php echo $row['u_exc_fechaturno'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Inventario Cabecera
	 * Salida y entrada
	 */
	/*
	public function tableHeadInventory($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Inventario Cabecera - Salida y entrada <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>docdate</th>
					<th>tipo</th>
					<th>u_exx_tipooper</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['headInventory'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['tipo'] ?></th>
					<th><?php echo $row['u_exx_tipooper'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }
	*/

	/**
	 * Inventario Detalle
	 * Salida y entrada
	 */
	/*
	public function tableDetailInventory($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Inventario Detalle - Salida y entrada <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailInventory'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }
	*/

	/**
	 * Trensferencias Cabecera
	 */
	public function tableHeadTransfers($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Transferencias Cabecera <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['headTransfers'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>docdate</th>
					<th>u_exx_tipooper</th>
					<th>filler</th>
					<th>towhscode</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['headTransfers'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['u_exx_tipooper'] ?></th>
					<th><?php echo $row['filler'] ?></th>
					<th><?php echo $row['towhscode'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Trensferencias Detalle
	 */
	public function tableDetailTransfers($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Transferencias - detalle <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailTransfers'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>quantity</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailTransfers'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }


	/**
	 * Afericiones - cabecera (Serafinado)
	 */
	public function tableHeadTestDispatch($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Afericiones - Cabecera (Serafinado) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['headTestDispatch'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['headTestDispatch'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Afericiones - cabecera (Serafinado)
	 */
	public function tableDetailTestDispatch($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Afericiones - Detalle (Serafinado) <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailTestDispatch'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>u_exc_nrotarjbonus</th>
					<th>u_exc_nlineas</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailTestDispatch'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['u_exc_nrotarjbonus'] ?></th>
					<th><?php echo $row['u_exc_nlineas'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }







	/**
	 * Nota de credito - Cabecera
	 */
	public function tableHeadCreditNote($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Nota de credito - Cabecera <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['headCreditNote'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>u_exx_serdocor</th>
					<th>u_exx_cordocor</th>
					<th>u_exx_fecdocor</th>
					<th>u_exx_tipdocor</th>
					<th>tabla</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['headCreditNote'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['u_exx_serdocor'] ?></th>
					<th><?php echo $row['u_exx_cordocor'] ?></th>
					<th><?php echo $row['u_exx_fecdocor'] ?></th>
					<th><?php echo $row['u_exx_tipdocor'] ?></th>
					<th><?php echo $row['tabla'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Nota de credito - Cabecera
	 */
	public function tableHeadCreditNoteWithFechaEmision($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Nota de credito - Cabecera <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['headCreditNote'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>u_exx_serdocor</th>
					<th>u_exx_cordocor</th>
					<th>u_exx_fecdocor</th>
					<th>u_exx_tipdocor</th>
					<th>tabla</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
					<th>u_exc_fechaemi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['headCreditNote'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['u_exx_serdocor'] ?></th>
					<th><?php echo $row['u_exx_cordocor'] ?></th>
					<th><?php echo $row['u_exx_fecdocor'] ?></th>
					<th><?php echo $row['u_exx_tipdocor'] ?></th>
					<th><?php echo $row['tabla'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
					<th><?php echo $row['u_exc_fechaemi'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Nota de credito - Detalle
	 */
	public function tableDetailCreditNote($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Nota de credito - Detalle <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailCreditNote'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>itemref</th>
					<th>noperacionref</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailCreditNote'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['itemref'] ?></th>
					<th><?php echo $row['noperacionref'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }



	/**
	 * Nota de debit - Cabecera
	 */
	public function tableHeadDebitNote($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Nota de debito - Cabecera <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['headDebitNote'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>u_exx_serdocor</th>
					<th>u_exx_cordocor</th>
					<th>u_exx_fecdocor</th>
					<th>u_exx_tipdocor</th>
					<th>tabla</th>
					<th>vatsum</th>
					<th>doctotal</th>
					<th>extempno</th>
					<th>u_exc_maqreg</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['headDebitNote'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['u_exx_serdocor'] ?></th>
					<th><?php echo $row['u_exx_cordocor'] ?></th>
					<th><?php echo $row['u_exx_fecdocor'] ?></th>
					<th><?php echo $row['u_exx_tipdocor'] ?></th>
					<th><?php echo $row['tabla'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['u_exc_maqreg'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Nota de credito - Detalle
	 */
	public function tableDetailDebitNote($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Nota de debito - Detalle <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailDebitNote'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>itemref</th>
					<th>noperacionref</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>u_exc_dispensador</th>
					<th>u_exc_caja</th>
					<th>u_exc_manguera</th>
					<th>u_exc_turno</th>
					<th>u_exc_hora</th>
					<th>u_exc_placa</th>
					<th>u_exc_km</th>
					<th>u_exc_bonus</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailDebitNote'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['itemref'] ?></th>
					<th><?php echo $row['noperacionref'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['u_exc_dispensador'] ?></th>
					<th><?php echo $row['u_exc_caja'] ?></th>
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['u_exc_turno'] ?></th>
					<th><?php echo $row['u_exc_hora'] ?></th>
					<th><?php echo $row['u_exc_placa'] ?></th>
					<th><?php echo $row['u_exc_km'] ?></th>
					<th><?php echo $row['u_exc_bonus'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Factura de proveedores - Cabecera
	 */
	public function tableHeadInvoicePurchase($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Factura de proveedores - Cabecera <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['headInvoicePurchase'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>cardcode</th>
					<th>docdate</th>
					<th>foliopref</th>
					<th>folionum</th>
					<th>extempno</th>
					<th>indicator</th>
					<th>vatsum</th>
					<th>doctotal</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['headInvoicePurchase'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['cardcode'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['foliopref'] ?></th>
					<th><?php echo $row['folionum'] ?></th>
					<th><?php echo $row['extempno'] ?></th>
					<th><?php echo $row['indicator'] ?></th>
					<th><?php echo $row['vatsum'] ?></th>
					<th><?php echo $row['doctotal'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Factura de Proveedores - Detalle
	 */
	public function tableDetailInvoicePurchase($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Factura de Proveedores - Detalle <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailInvoicePurchase'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>item</th>
					<th>itemcode</th>
					<th>whscode</th>
					<th>quantity</th>
					<th>price</th>
					<th>taxcode</th>
					<th>discprcnt</th>
					<th>ocrcode2</th>
					<th>priceafvat</th>
					<th>desc_sinigv</th>
					<th>desc_igv</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailInvoicePurchase'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['item'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['taxcode'] ?></th>
					<th><?php echo $row['discprcnt'] ?></th>
					<th><?php echo $row['ocrcode2'] ?></th>
					<th><?php echo $row['priceafvat'] ?></th>
					<th><?php echo $row['desc_sinigv'] ?></th>
					<th><?php echo $row['desc_igv'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Varillaje
	 */
	public function tableDetailVarillas($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Varillaje - Detalle <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailVarillas'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>docdate</th>
					<th>whscode</th>
					<th>itemcode</th>
					<th>quantity</th>					
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailVarillas'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>
					<th><?php echo $row['whscode'] ?></th>
					<th><?php echo $row['itemcode'] ?></th>
					<th><?php echo $row['quantity'] ?></th>					
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Venta de combustibles por manguera/día
	 */
	public function tableDetailCombustiblePorManguera($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Combustible por manguera/día - Detalle <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailCombustiblePorManguera'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>docdate</th>
					<th>itemcode</th>
					<th>u_exc_lado</th>
					<th>u_exc_manguera</th>					
					<th>price</th>					
					<th>u_exc_continigal</th>					
					<th>u_exc_contfingal</th>					
					<th>u_exc_continival</th>					
					<th>u_exc_contfinval</th>					
					<th>u_exc_afericiones</th>					
					<th>u_exc_descuentos</th>					
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailCombustiblePorManguera'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>					
					<th><?php echo $row['itemcode'] ?></th>	
					<th><?php echo $row['u_exc_lado'] ?></th>				
					<th><?php echo $row['u_exc_manguera'] ?></th>
					<th><?php echo $row['price'] ?></th>
					<th><?php echo $row['u_exc_continigal'] ?></th>
					<th><?php echo $row['u_exc_contfingal'] ?></th>
					<th><?php echo $row['u_exc_continival'] ?></th>
					<th><?php echo $row['u_exc_contfinval'] ?></th>
					<th><?php echo $row['u_exc_afericiones'] ?></th>
					<th><?php echo $row['u_exc_descuentos'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Stocks (sólo combustibles)
	 */
	public function tableDetailStock($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Stocks (sólo combustibles) - Detalle <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailStock'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>docdate</th>
					<th>itemcode</th>
					<th>u_exc_medicion</th>	
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailStock'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>					
					<th><?php echo $row['itemcode'] ?></th>	
					<th><?php echo $row['u_exc_medicion'] ?></th>				
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	/**
	 * Totales por forma de pago con notas de despacho
	 */
	public function tableDetailTotales($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3>Totales por forma de pago con notas de despacho - Detalle <?php echo $data['isViewTableName'] ? '['.$data['tableName'].']' : '' ?></h3><hr>
		<?php
			if($_SESSION['debug'] == true){
				/*** Debug ***/
				$sql_delete = "DELETE FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";
				$sql_select = "SELECT * FROM BDINT2.". $data['tableName'] ." WHERE noperacion IN (";			
				foreach($data['detailTotales'] as $key=>$row){
					$sql_data .= "'" . $row['noperacion'] . "',";
				}
				$sql_data = substr($sql_data,0,-1);
				$sql_data .= ");";
				$sql_delete = $sql_delete . $sql_data;
				$sql_select = $sql_select . $sql_data;
				echo "<pre>";
				echo "<h3>$sql_delete</h3>";
				echo "<h3>$sql_select</h3>";
				echo "</pre>";				
				/***/
			}
		?>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>noperacion</th>
					<th>docdate</th>
					<th>u_exc_turno</th>
					<th>u_exc_fpago</th>	
					<th>u_exc_nolinea</th>	
					<th>u_exc_importe</th>	
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detailTotales'] as $key => $row) { ?>
				<tr>
					<th><?php echo $row['noperacion'] ?></th>
					<th><?php echo $row['docdate'] ?></th>					
					<th><?php echo $row['u_exc_turno'] ?></th>	
					<th><?php echo $row['u_exc_fpago'] ?></th>				
					<th><?php echo $row['u_exc_nolinea'] ?></th>				
					<th><?php echo $row['u_exc_importe'] ?></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }
} ?>
