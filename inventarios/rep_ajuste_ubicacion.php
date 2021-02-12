<!DOCTYPE html>
	<meta charset="UTF-8">
    	<head>
		<title>Ajuste Inventario Ubicacion</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script src="/sistemaweb/inventarios/js/rep_ajuste_ubicacion.js"></script>
	</head>
	<body>
        	<?php include "../menu_princ.php"; ?>
		<div id="content">
		    <script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
		    <script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
		</div>

        <div id="footer">&nbsp;</div>
        <div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>

        <?php

		include('/sistemaweb/include/mvc_sistemaweb.php');
		include('reportes/t_rep_ajuste_ubicacion.php');
		include('reportes/m_rep_ajuste_ubicacion.php');

		//Variables de Entrada

		$hoy		= date("d/m/Y");

		$estaciones	= ModelReporteAjusteUbicacion::GetAlmacen(null);
		$ubicaciones	= ModelReporteAjusteUbicacion::GetUbicacion(null);
		echo TemplateReporteAjusteUbicacion::Inicio($estaciones, $ubicaciones, $hoy);

        ?>

	</body>
</html>
