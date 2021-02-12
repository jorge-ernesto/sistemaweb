<?php
include("valida_sess.php");
include("config.php");
if ($_SERVER['REQUEST_URI'] != '/sistemaweb/menu_princ.php') {
?>
<html>
	<head>
		<link rel="stylesheet" href="<?php echo $v_path_url; ?>/css/sistemaweb.css" type="text/css">
		<title>OpenSoft</title>
	</header>
	<body>
		<div>
		<?php include "/sistemaweb/include/menu.php"; ?><br/>
		</div>
	</body>
</html>
<?php
} else {
	$showModal = array('error' => false);
	$sql = "SELECT
 *
FROM
 int_parametros
WHERE
 par_nombre = 'ebiProvider';";

		if ($sqlca->query($sql) >= 1) {
			$sql = "SELECT
 *
FROM
 int_ta_sucursales
WHERE
 ebikey IS NOT NULL OR ebikey != '';";

			if ($sqlca->query($sql) >= 1) {
				$sql = "SELECT 1
FROM information_schema.tables
WHERE table_schema = 'public'
AND table_name = 'ebi_queue';";

				if ($sqlca->query($sql) == 1) {
					$sql = "SELECT
 COUNT(*) AS count_documents,
 EXTRACT(EPOCH FROM NOW() - MIN(created)) AS diff_tt
FROM
 ebi_queue
WHERE
 status = 0;";
					$_res = $sqlca->query($sql);
//					$_res1 = $sqlca->query($sql1);
					$res = $sqlca->fetchRow();


  					$sql1 = "SELECT
 COUNT(*) AS count_documents_pending
from 
 fac_ta_factura_cabecera 
where 
 (ch_fac_seriedocumento LIKE 'B%' OR ch_fac_seriedocumento LIKE 'F%') AND nu_fac_recargo3='0' and dt_fac_fecha <= now() - CAST('2 DAYS' AS INTERVAL);";
					$sqlca->query($sql1);
					$row_pending_sale = $sqlca->fetchRow();

					if ( ($res['count_documents'] > 1000 || $res['diff_tt'] > 86400) || ($row_pending_sale['count_documents_pending'] > 0) ) {

						$time = $res['diff_tt'];
						$days = floor($time / (24*60*60));
						$hours = floor(($time - ($days*24*60*60)) / (60*60));
						$minutes = floor(($time - ($days*24*60*60)-($hours*60*60)) / 60);
						$seconds = ($time - ($days*24*60*60) - ($hours*60*60) - ($minutes*60)) % 60;

						$res['diff_dt'] = $days.' días '.$hours.' horas '.$minutes.' minutos '.$seconds.' segundos';

						$showModal = array(
							'error' => true,
							'message' => 'Documentos pendientes',
							'res' => $res,
							'res1' => $row_pending_sale,
							'message' => 'Documentos pendientes de envio',
						);

					} else {
						$showModal = array(
							'error' => false,
							'code' => 3,
							'message' => 'Excluido de la condición',
							'res' => $res,
						);
					}
				} else {
					$showModal = array(
						'error' => false,
						'code' => 2,
						'message' => 'No existe tabla',
					);
				}
			} else {
				$showModal = array(
					'error' => false,
					'code' => 1,
					'message' => 'No existe llave',
				);
			}
		} else {
			$showModal = array(
				'error' => false,
				'code' => 0,
				'message' => 'No existe configuración',
			);
		}
?>
<html>
	<head>
		<link rel="stylesheet" href="<?php echo $v_path_url; ?>/css/sistemaweb.css" type="text/css">
		<title>OpenSoft</title>
		<script type="text/javascript" charset="utf-8">
		function loadCheck() {
			if ( document.getElementById('accept_dialog') !== null ) {
				if (document.getElementById('accept_dialog').checked) {
					document.getElementById('accept_dialog').checked = false;
				}
			}
		}

		function checkAccept() {
			if ( document.getElementById('accept_dialog') !== null ) {
				if (document.getElementById('accept_dialog').checked) {
					var btn = document.getElementById('content-btn-accept');
					btn.innerHTML = '<button onclick="next()">Aceptar</button>';
				} else {
					var btn = document.getElementById('content-btn-accept');
					btn.innerHTML = '';
				}
			}
		}

		function next() {
			var modal = document.getElementById('modal-fe');
			modal.setAttribute('style','display: none;');

			var container = document.getElementById('container-sistemaweb');
			container.setAttribute('style','display: block;');
		}
		</script>
		<style type="text/css">
		#modal-fe {
			overflow-x: hidden;
			font-size: 30px;
			overflow-y: auto
			opacity: 1;
			position: fixed;
			right: 0;
			top: 0;
			z-index: 1050;
			width: 100%;
			height: 100%;
			padding-top: 20%;
			color: #fff;
			background-color: rgb(0,0,0);
			background-color: rgba(0,0,0,0.4);
		}
		</style>
	</header>
	<body onload="loadCheck()">
		<div id="container-sistemaweb">
		<?php include "/sistemaweb/include/menu.php"; ?><br/>
		</div>
		<?php if ($showModal['error']) { ?>
		<div id="modal-fe" align="center">
			<div style="color: #000; background-color: #fff; margin: 5%; margin-top: -5%;">
				<div style="background-color: #30767f; padding: 10px;font-size: 13px; color: #fff; font-weight: bold;" align="left">OCS</div>
				<div style="font-size: 15px; padding: 18px;">
					<h2 style="color: red;">ADVERTENCIA</h2>
					<?php if((int)$showModal['res']['count_documents']>0) { ?>
					<p>Documentos electrónicos de playa pendientes: <span style="color: red;"><b><?php echo $showModal['res']['count_documents'] ?></b><span></p>
					<p>Diferencia de tiempo: <span style="color: red;"><b><?php echo $showModal['res']['diff_dt'] ?></b></span></p>
					<?php } ?>
					<?php if((int)$showModal['res1']['count_documents_pending']>0) { ?>
					<p>Documentos electrónicos de oficina pendientes: <span style="color: red;"><b><?php echo $showModal['res1']['count_documents_pending'] ?></b<span></p>
					<?php } ?>
					<input type="checkbox" id="accept_dialog" onclick="checkAccept()" style="cursor: pointer"><label for="accept_dialog" style="cursor: pointer">Tomo conocimiento del error, y me comprometo a coordinar con el personal y proveedores que corresponda para solucionarlo lo antes posible.</label>
					<div id="content-btn-accept" style="padding-top: 15px;"></div>
				</div>
			</div>
		</div>
		<?php } ?>
	</body>
</html>
<?php } ?>