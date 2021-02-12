<!DOCTYPE html>
<html>
	<head>
		<title>Ventas Mensuales - OpenSoft</title>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
		<!--<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>-->
		<script src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js" type="text/javascript"></script>
		<script src="/sistemaweb/ventas_clientes/js/ventas_mensuales.js"></script>
	</head>
	<body>
		<?php include "../menu_princ.php"; ?>
		<div id="footer">&nbsp;</div>
		<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>

		<?php

		include('/sistemaweb/include/mvc_sistemaweb.php');
		include('reportes/t_ventas_mensuales.php');
		include('reportes/m_ventas_mensuales.php');

		//Variables de Entrada

		$hoy = date('d/m/Y');

		$model = new ModelVentasMensuales;
		$template = new TemplateVentasMensuales;

		$estaciones	= $model->GetAlmacen('T');
		$lineas		= $model->GetLinea();
		echo $template->Form($estaciones, $lineas, $hoy);

		?>
	</body>
</html>