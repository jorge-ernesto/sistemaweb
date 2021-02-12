<?php require_once("/sistemaweb/valida_sess.php"); ?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Sistema de Ventas - Cuadre de Ventas</title>
		<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
		<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script language="JavaScript" src="js/combustibles.js"></script>

		
	    <link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
		<script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>

		<script charset="utf-8" type="text/javascript">
			window.onload = function() {
				$(function() {
					$.datepicker.regional['es'] = {
						closeText: 'Cerrar',
						prevText: '<Ant',
						nextText: 'Sig>',
						currentText: 'Hoy',
						monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
						monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
						dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
						dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
						dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
						weekHeader: 'Sm',
						dateFormat: 'dd/mm/yy',
						firstDay: 1,
						isRTL: false,
						showMonthAfterYear: false,
						yearSuffix: ''
					};

					$.datepicker.setDefaults($.datepicker.regional['es']);

					$( "#dia1" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})
				});
			}
		</script>
	</head>
<body>
<!--
	<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
	<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
	<script src="/sistemaweb/utils/cintillo.js" type="text/javascript" ></script>
-->
	<style type="text/css">
		table#tablaPrincipalCuadre {
			width: 1000px;
			border: 1px solid black;
			vertical-align: text-bottom;
			text-align: center;
			font-size: 12px;
			padding: 0px;
			border-spacing: 0px;
		}

		table#tablaPrincipalCuadre tr td.celdaDiaTurno {
			width: 100%;
			border-style: none;
			font-size: 16px;
			font-weight: bold;
		}

		table#tablaPrincipalCuadre tr td.celdaContenido {
			width: 100%;
			border-style: none;
			vertical-align: top;
			text-align: left;
		}

		table.tablaContometros {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
		}

		table.tablaContometros tr td {
			font-size: 11px;
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaContometros tr td.celdaTrabajador {
			width: 100%;
			border-width: 1px 0px 1px 0px;
			font-size: 14px;
			font-weight: bold;
		}

		table.tablaContometros tr td.celdaCabecera {
			font-weight: bold;
		}

		table.tablaContometros tr td.celdaEtiqueta {
			font-weight: normal;
		}

		table.tablaContometros tr td.celdaImporte {
			text-align: right;
			font-weight: normal;
		}

		table.tablaContometros tr td.celdaContometro {
			text-align: right;
			font-weight: normal;
		}

		table.tablaContometros tr td.celdaEtiquetaAcum {
			font-weight: bold;
		}

		table.tablaContometros tr td.celdaImporteAcum {
			text-align: right;
			font-weight: bold;
		}

		table.tablaContometros tr td.celdaContometroAcum {
			text-align: right;
			font-weight: bold;
		}

		table.tablaDetalles {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
		}

		table.tablaDetalles tr td {
			width: 50%;
			text-align: left;
			vertical-align: top;
			border-style: none;
		}

		table.tablaND {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
			border-style: solid;
			border-color: black;
			border-width: 1px 0px 0px 1px;
		}

		table.tablaND tr td {
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaND tr td.celdaEncabezado {
			width: 100%;
			font-weight: bold;
		}

		table.tablaND tr td.celdaTransCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaND tr td.celdaClienteCabecera {
			width: 20%;
			font-weight: bold;
		}

		table.tablaND tr td.celdaNombreCabecera {
			width: 50%;
			font-weight: bold;
		}

		table.tablaND tr td.celdaImporteCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaND tr td.celdaTotal {
			width: 85%;
			font-weight: bold;
		}

		table.tablaND tr td.celdaImporteTotal {
			width: 15%;
			font-weight: bold;
		}


		table.tablaND tr td.celdaTrans {
			width: 15%;
			font-weight: normal;
		}

		table.tablaND tr td.celdaCliente {
			width: 20%;
			font-weight: normal;
		}

		table.tablaND tr td.celdaNombre {
			width: 50%;
			font-weight: normal;
		}

		table.tablaND tr td.celdaImporte {
			width: 15%;
			font-weight: normal;
		}

		table.tablaTC {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
			border-style: solid;
			border-color: black;
			border-width: 1px 0px 0px 1px;
		}

		table.tablaTC tr td {
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaTC tr td.celdaEncabezado {
			width: 100%;
			font-weight: bold;
		}

		table.tablaTC tr td.celdaTransCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaTC tr td.celdaHoraCabecera {
			width: 20%;
			font-weight: bold;
		}

		table.tablaTC tr td.celdaTipoCabecera {
			width: 20%;
			font-weight: bold;
		}

		table.tablaTC tr td.celdaTarjetaCabecera {
			width: 30%;
			font-weight: bold;
		}

		table.tablaTC tr td.celdaImporteCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaTC tr td.celdaTrans {
			width: 15%;
			font-weight: normal;
		}

		table.tablaTC tr td.celdaHora {
			width: 20%;
			font-weight: normal;
		}

		table.tablaTC tr td.celdaTipo {
			width: 20%;
			font-weight: normal;
		}

		table.tablaTC tr td.celdaTarjeta {
			width: 30%;
			font-weight: normal;
		}

		table.tablaTC tr td.celdaImporte {
			width: 15%;
			font-weight: normal;
		}

		table.tablaTC tr td.celdaTotal {
			width: 85%;
			font-weight: bold;
		}

		table.tablaTC tr td.celdaImporteTotal {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDesc {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
			border-style: solid;
			border-color: black;
			border-width: 1px 0px 0px 1px;
		}

		table.tablaDesc tr td {
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaDesc tr td.celdaEncabezado {
			width: 100%;
			font-weight: bold;
		}

		table.tablaDesc tr td.celdaTransCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDesc tr td.celdaFPagoCabecera {
			width: 20%;
			font-weight: bold;
		}

		table.tablaDesc tr td.celdaDescripcionCabecera {
			width: 50%;
			font-weight: bold;
		}

		table.tablaDesc tr td.celdaImporteCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDesc tr td.celdaTrans {
			width: 15%;
			font-weight: normal;
		}

		table.tablaDesc tr td.celdaFPago {
			width: 20%;
			font-weight: normal;
		}

		table.tablaDesc tr td.celdaDescripcion {
			width: 50%;
			font-weight: normal;
		}

		table.tablaDesc tr td.celdaImporte {
			width: 15%;
			font-weight: normal;
		}

		table.tablaDesc tr td.celdaTotal {
			width: 85%;
			font-weight: bold;
		}

		table.tablaDesc tr td.celdaImporteTotal {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDevol {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
			border-style: solid;
			border-color: black;
			border-width: 1px 0px 0px 1px;
		}

		table.tablaDevol tr td {
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaDevol tr td.celdaEncabezado {
			width: 100%;
			font-weight: bold;
		}

		table.tablaDevol tr td.celdaTransCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDevol tr td.celdaFPagoCabecera {
			width: 70%;
			font-weight: bold;
		}

		table.tablaDevol tr td.celdaImporteCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDevol tr td.celdaTrans {
			width: 15%;
		}

		table.tablaDevol tr td.celdaFPago {
			width: 70%;
		}

		table.tablaDevol tr td.celdaImporte {
			width: 15%;
		}

		table.tablaDevol tr td.celdaTotal {
			width: 85%;
			font-weight: bold;
		}

		table.tablaDevol tr td.celdaImporteTotal {
			width: 15%;
			font-weight: bold;
		}

		table.tablaAfer {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
			border-style: solid;
			border-color: black;
			border-width: 1px 0px 0px 1px;
		}

		table.tablaAfer tr td {
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaAfer tr td.celdaEncabezado {
			width: 100%;
			font-weight: bold;
		}

		table.tablaAfer tr td.celdaTransCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaAfer tr td.celdaProductoCabecera {
			width: 35%;
			font-weight: bold;
		}

		table.tablaAfer tr td.celdaDetalleCabecera {
			width: 35%;
			font-weight: bold;
		}

		table.tablaAfer tr td.celdaImporteCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaAfer tr td.celdaTrans {
			width: 15%;
			font-weight: normal;
		}

		table.tablaAfer tr td.celdaProducto {
			width: 35%;
			font-weight: normal;
		}

		table.tablaAfer tr td.celdaDetalle {
			width: 35%;
			font-weight: normal;
		}

		table.tablaAfer tr td.celdaImporte {
			width: 15%;
			font-weight: normal;
		}

		table.tablaAfer tr td.celdaTotal {
			width: 85%;
			font-weight: bold;
		}

		table.tablaAfer tr td.celdaImporteTotal {
			width: 15%;
			font-weight: bold;
		}

		table.tablaTransgrat {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
			border-style: solid;
			border-color: black;
			border-width: 1px 0px 0px 1px;
		}

		table.tablaTransgrat tr td {
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaTransgrat tr td.celdaEncabezado {
			width: 100%;
			font-weight: bold;
		}

		table.tablaTransgrat tr td.celdaTransCabecera {
			width: 45%;
			font-weight: bold;
		}

		table.tablaTransgrat tr td.celdaHoraCabecera {
			width: 40%;
			font-weight: bold;
		}

		table.tablaTransgrat tr td.celdaImporteCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaTransgrat tr td.celdaTrans {
			width: 45%;
			font-weight: normal;
		}

		table.tablaTransgrat tr td.celdaHora {
			width: 40%;
			font-weight: normal;
		}

		table.tablaTransgrat tr td.celdaImporte {
			width: 15%;
			font-weight: normal;
		}

		table.tablaTransgrat tr td.celdaTotal {
			width: 85%;
			font-weight: bold;
		}

		table.tablaTransgrat tr td.celdaImporteTotal {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDepo {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
			border-style: solid;
			border-color: black;
			border-width: 1px 0px 0px 1px;
		}

		table.tablaDepo tr td {
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaDepo tr td.celdaEncabezado {
			width: 100%;
			font-weight: bold;
		}

		table.tablaDepo tr td.celdaCorrelativoCabecera {
			width: 20%;
			font-weight: bold;
		}

		table.tablaDepo tr td.celdaHoraCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDepo tr td.celdaMonedaCabecera {
			width: 10%;
			font-weight: bold;
		}

		table.tablaDepo tr td.celdaTCCabecera {
			width: 15%;
			font-weight: bold;
		}

		table.tablaDepo tr td.celdaImporteCabecera {
			width: 20%;
			font-weight: bold;
		}

		table.tablaDepo tr td.celdaImporteSolesCabecera {
			width: 20%;
			font-weight: bold;
		}

		table.tablaDepo tr td.celdaCorrelativo {
			width: 20%;
		}

		table.tablaDepo tr td.celdaHora {
			width: 15%;
			font-weight: normal;
		}

		table.tablaDepo tr td.celdaMoneda {
			width: 10%;
			font-weight: normal;
		}

		table.tablaDepo tr td.celdaTC {
			width: 15%;
			font-weight: normal;
		}

		table.tablaDepo tr td.celdaImporte {
			width: 20%;
			font-weight: normal;
		}

		table.tablaDepo tr td.celdaImporteSoles {
			width: 20%;
			font-weight: normal;
		}

		table.tablaDepo tr td.celdaTotal {
			width: 80%;
			font-weight: bold;
		}

		table.tablaDepo tr td.celdaImporteTotal {
			width: 20%;
			font-weight: bold;
		}

		table.tablaResumen {
			width: 100%;
			margin-left: auto;
			margin-right: auto;
			border-spacing: 0px;
			border-style: solid;
			border-color: black;
			border-width: 1px 0px 0px 1px;
		}

		table.tablaResumen tr td {
			text-align: center;
			vertical-align: bottom;
			border-style: solid;
			border-color: black;
			border-width: 0px 1px 1px 0px;
		}

		table.tablaResumen tr td.celdaEncabezado {
			width: 100%;
			font-weight: bold;
		}

		table.tablaResumen tr td.celdaConcepto {
			width: 75%;
			text-align: right;
			margin-right: 5px;
			font-weight: normal;
		}

		.celdaConcepto{
			font-weight: normal !important;			
		}

		table.tablaResumen tr td.celdaImporte {
			width: 15%;
			font-weight: normal;
		}

		table.tablaResumen tr td.celdaOperacion {
			width: 5%;
			font-weight: bold;
			font-size: 14px;
		}

		table.tablaResumen tr td.celdaTotal {
			width: 75%;
			font-weight: normal;
			text-align: right;
			margin-right: 5px;
		}

		table.tablaResumen tr td.celdaImporteTotal {
			width: 15%;
			font-weight: normal;
		}

		table tr td.width100 {
			width: 100%;
		}

		table tr td.width6 {
			width: 6%;
		}

		table tr td.width7 {
			width: 7%;
		}

		table tr td.width8 {
			width: 8%;
		}

		table tr td.width10 {
			width: 10%;
		}

		table tr td.width70 {
			width: 70%;
		}
	</style>
<?php include "../menu_princ.php"; ?>
<div id="content">
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.CUADREVENTAS" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
