<!DOCTYPE html>
	<meta charset="UTF-8">
    <head>
		<title>Transacciones de Ventas</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb_validacion.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" type="text/css">
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script src="/sistemaweb/combustibles/js/registro_ventas_detallada_central.js"></script>
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

		date_default_timezone_set('UTC');
		
		include('/sistemaweb/include/mvc_sistemaweb.php');
		include('reportes/t_registro_ventas_detallada_central.php');
		include('reportes/m_registro_ventas_detallada_central.php');

		//Parametros de Entrada
		$hoy								= date("d/m/Y");
		$status_connection_server_central 	= ModelRegistroVentasCental::ConnectionServerCentral();
		if(!$status_connection_server_central)
			$organizaciones = "No hay conexión a la B.D de la Sede Central";
		else
			$organizaciones	= ModelRegistroVentasCental::GetOrganizaciones();

		echo TemplateRegistroVentasCental::Inicio($status_connection_server_central, $organizaciones, $hoy, $hoy);

        ?>

	</body>
</html>
