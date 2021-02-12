<!DOCTYPE html>
	<meta charset="UTF-8">
    <head>
		<title>Facturacion Electronica- Contingencia</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb_validacion.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" type="text/css">
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script src="/sistemaweb/combustibles/js/contingencia_facturacion_electronica.js"></script>
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
		include('reportes/t_contingencia_facturacion_electronica.php');
		include('reportes/m_contingencia_facturacion_electronica.php');

		// Obtener clases
		$objModel 		= new ModelContingEnciaFE();
		$objTemplate 	= new TemplateContingEnciaFE();

		//Parametros de Entrada
		$fToday			= date("d/m/Y");
		$arrWareHouse = $objModel->GetAlmacen();
		$objTemplate->Inicio($arrWareHouse, $fToday, $fToday);
        ?>
	</body>
</html>
