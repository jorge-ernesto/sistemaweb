<?php
class t_innova_1 {
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
	<script charset="utf-8" type="text/javascript" src="/sistemaweb/combustibles/js/application.innova-1.js?ver=1.0"></script>
	<script charset="utf-8" type="text/javascript" src="/sistemaweb/combustibles/js/functions.innova-1.js?ver=2.0"></script>
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
		$this->head(array('title' => 'Interface de Datos Innova'));
		$consult_initial_date = $data['consult_initial_date'];
		$consult_initial_date = $consult_initial_date['day'].'/'.$consult_initial_date['month'].'/'.$consult_initial_date['year'];
	?>
	<div style="margin: auto; width: 88%;padding: 10px;">
		<h2 style="text-align: center">Innova</h2>
		<div class="tab">
			<button class="tablinks active" data-id="export" title="Previualización y Exportación">Exportar</button>
			<button class="tablinks" data-id="configuration" title="Configuración de tablas Innova">Configuración</button>
		</div>
		<div id="export" class="tabcontent">
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
					<th>Fecha Inicio:</th>
					<th><input type="text" id="export-initial-date" class="is-input" value="<?php echo $consult_initial_date ?>"></th>
				</tr>
				<tr>
					<th>Fecha Final:</th>
					<th><input type="text" id="export-final-date" class="is-input" value="<?php echo $consult_initial_date ?>"></th>
				</tr>
				<tr>
					<th></th>
					<th class="container-btn-export">
						<button class="is-btn is-btn-default preview-export" style="width: 100%">Previsualizar</button>
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
						<button class="is-btn is-btn-primary consult-configuration-table" style="width: 100%">Consultar</button>
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

	public function inputDateHidden($id, $consult_date) { ?>
		<input type="hidden" id="<?php echo $id ?>" class="is-input" value="<?php echo $consult_date ?>">
	<?php }

	public function tableDinamic($data) { ?>

	<br>
	<div class="container-table-preview">
		<h3><?php echo $data['tableTitle'] ?></h3>
		<button class="is-btn is-btn-default send-export" data-mode="<?php echo $data['mode'] ?>" style="width: 100%">Exportar</button>
		<hr>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<?php
					foreach (array_keys($data[$data['nodeData']][0]) as $key => $name) {
					?>
						<th><?php echo $name ?></th>
					<?php
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				for ($i = 0; $i < count($data[$data['nodeData']]); $i++) {
					?><tr><?php
					foreach (array_keys($data[$data['nodeData']][$i]) as $key => $value) {
						$text = $data[$data['nodeData']][$i][$value];
						?>
						<th><?php echo $text ?></th>
						<?php
					}
					?></tr><?php
				}
				?>
			</tbody>
		</table>
	</div>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	public function statusRequest($text, $data) { ?>

	<br>
	<div align="center" class="alert <?php echo ($data['error'] ? 'alert-danger' : 'alert-info') ?>">
		<div><?php echo $text.''.$data['code'].']: '.$data['message'] ?></div>
	</div>

	<?php
	}

	/**
	 * Table Head Configuration INNOVA
	 */
	public function viewDetailTableConfiguration($data) { ?>

	<div class="container-table-preview">
		<h3>Tabla de equibalencias OCS - ERP</h3><hr>
		<table class="table-sap-1">
			<thead class="head-table-preview-sap-1">
				<tr>
					<th>ID</th>
					<th>Código OCS</th>
					<th>Código ERP</th>
					<th>name</th>
					<th>description</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th style="text-align: center;"></th>
					<th style="text-align: left;"><input type="text" id="ocs-codigo-ins" value="<?php echo $row['sap_codigo']; ?>" autocomplete="off"></th>
					<th style="text-align: left;"><input type="text" id="sap-codigo-ins" value="<?php echo $row['sap_codigo']; ?>" autocomplete="off"></th>
					<th style="text-align: left;"><input type="text" id="name-ins" value="<?php echo $row['name'] ?>" autocomplete="off"></th>
					<th style="text-align: left;"><input type="text" id="description-ins" value="<?php echo $row['description'] ?>" autocomplete="off"></th>
					<th style="text-align: center;"><button class="is-btn is-btn-default save-configuration" style="width: 100%">Insertar</button></th>
				</tr>
				<?php foreach ($data['detailTableConfiguration'] as $key => $row) {	?>
				<tr>
					<th style="text-align: center;"><?php echo $row['id_tipo_tabla_detalle'] ?></th>
					<th style="text-align: left;"><?php echo $row['opencomb_codigo'] ?></th>
					<th style="text-align: left;"><input type="text" id="sap-codigo-<?php echo $row['id_tipo_tabla_detalle'] ?>" value="<?php echo $row['sap_codigo']; ?>" autocomplete="off"></th>
					<th style="text-align: left;"><input type="text" id="name-<?php echo $row['id_tipo_tabla_detalle'] ?>" value="<?php echo $row['name'] ?>" autocomplete="off"></th>
					<th style="text-align: left;"><input type="text" id="description-<?php echo $row['id_tipo_tabla_detalle'] ?>" value="<?php echo $row['description'] ?>" autocomplete="off"></th>
					<th style="text-align: center;"><button class="is-btn is-btn-default upd-configuration" data-id="<?php echo $row['id_tipo_tabla_detalle'] ?>" style="width: 100%">Modificar</button></th>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<br>
	<div>Cantidad de registros: <?php echo $data['count'] ?></div>
	<?php }

	public function lines($data, $req) {
		header('Content-disposition: attachment; filename='.$data['filename'].'.txt');
		header('Content-type: text/plain');
		$val = '';
		for ($i = 0; $i < count($data[$data['nodeData']]); $i++) {
			foreach (array_keys($data[$data['nodeData']][$i]) as $key => $value) {
				$text = $data[$data['nodeData']][$i][$value];
				//echo $text;
				echo trim($text)."\t";
			}
			echo "\n";
		}
	}
}