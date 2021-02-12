<?php
include "../valida_sess.php";
include "../config.php";
include "store_procedures.php";
include "include/fpdf.php";
include "include/reportes.inc.php";

$hoy = date("d/m/Y");
if ($_REQUEST['c_fec_desde'] == "") $desde = $hoy; else $desde = $_REQUEST['c_fec_desde'];
if ($_REQUEST['c_fec_hasta'] == "") $hasta = $hoy; else $hasta = $_REQUEST['c_fec_hasta'];

$rs_todas = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "TODAS");
$rs_anticipados = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "ANTICIPADOS");
$rs_facturas_contado = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "FACTURAS_CONTADO");
$rs_vales_no_facturados = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "VALES_NO_FACTURADOS");
$rs_vales_liquidados_no_facturados = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "VALES_LIQUIDADOS_NO_FACTURADOS");
$rs_vales_pago_adelantado = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "VALES_PAGO_ADELANTADO");
$rs_vales_mes_pasado =  REPORTE_CUADRE_FACTURACION_1($desde,$hasta,"VALES_PASADO");
$rs_cuenta_mantenimiento = REPORTE_CUADRE_FACTURACION_1($desde, $hasta, "CUENTA_MANTENIMIENTO");
$rs_ventas_credito_det = REPORTE_CUADRE_FACTURACION_2($desde, $hasta, "DETALLADO");


$fontsize = 10;

$reporte = new CReportes(8,'L','10','A3');

$cabecera = Array(
		    0 => Array(
				'texto'  => "SISTEMA WEB                                        Cuadre de facturacion del ".$desde." al ".$hasta,
				'estilo' => "L"
			),
		    1 => Array(
				'texto'  => "Pagina: %p",
				'estilo' => "R"
			),
		    2 => Array(
				'texto'  => "Fecha: %f",
				'estilo' => "L"
			)
		);

/************************** INICIO DEL INFORME DE TODOS LOS DOCUMENTOS ***********************/
$reporte->ponerCabecera($cabecera);
    $reporte->definirColumna(0, "CLIENTES",   $reporte->TIPO_STRING, 40, "L");
    $reporte->definirColumna(1, "FACTURAS",   $reporte->TIPO_STRING, 10, "R");
    $reporte->definirColumna(2, "IMPORTES",   $reporte->TIPO_IMPORTE,  12, "R");
    $reporte->definirColumna(3, "VALES",      $reporte->TIPO_TEXTO,  12, "R");
    $reporte->definirColumna(4, "CTA. MANT.", $reporte->TIPO_TEXTO,  12, "R");
    $reporte->definirColumna(5, "TOTAL",      $reporte->TIPO_TEXTO,  12, "R");
    $reporte->definirColumna(6, "DIFERENCIA", $reporte->TIPO_TEXTO,  12, "R");
    $reporte->definirColumna(7, "DIFERENCIA", $reporte->TIPO_TEXTO,  12, "R");
if (pg_numrows($rs_todas) > 0) {
    for ($i = 0; $i < pg_numrows($rs_todas); $i++) {
        $A = pg_fetch_array($rs_todas, $i);
        $reporte->irFila($reporte->agregarFila());
        $reporte->poneValor($A['clientes'], 0);
        $reporte->poneValor($A['facturas'], 1);
        $reporte->poneValor($A['importes'], 2);
        $reporte->poneValor($A['vales'], 3);
        $reporte->poneValor($A['mant'], 4);
        $reporte->poneValor($A['total'], 5);
        $reporte->poneValor($A['dif1'], 6);
        $reporte->poneValor($A['dif2'], 7);
        $TOTALES['FACTURAS']['importes'] += $A['importes'];
        $TOTALES['FACTURAS']['vales'] += $A['vales'];
        $TOTALES['FACTURAS']['mant'] += $A['mant'];
        $TOTALES['FACTURAS']['total'] += $A['total'];
        $TOTALES['FACTURAS']['dif1'] += $A['dif1'];
        $TOTALES['FACTURAS']['dif2'] += $A['dif2'];
        $TOTALES['TOTALES']['importes'] += $A['importes'];
        $TOTALES['TOTALES']['vales'] += $A['vales'];
        $TOTALES['TOTALES']['mant'] += $A['mant'];
        $TOTALES['TOTALES']['total'] += $A['total'];
        $TOTALES['TOTALES']['dif1'] += $A['dif1'];
        $TOTALES['TOTALES']['dif2'] += $A['dif2'];
    }
    //$reporte->generar();
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('TOTAL', 0);
$reporte->poneValor('', 1);
$reporte->poneValor($TOTALES['FACTURAS']['importes'], 2);
$reporte->poneValor($TOTALES['FACTURAS']['vales'], 3);
$reporte->poneValor($TOTALES['FACTURAS']['dif1'], 6);
$reporte->poneValor($TOTALES['FACTURAS']['dif2'], 7);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar();
/************************** FIN DEL INFORME DE TODOS LOS DOCUMENTOS *************************/

/*********************** INICIO DEL INFORME DE ANTICIPOS ***********************/
$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Anticipos       ", 0);
$reporte->generar(false);
$reporte->nuevoInforme(8);
$reporte->definirColumna(0, "CLIENTES",   $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(1, "FACTURAS",   $reporte->TIPO_STRING, 10, "R");
$reporte->definirColumna(2, "IMPORTES",   $reporte->TIPO_IMPORTE,  12, "R");
$reporte->definirColumna(3, "VALES",      $reporte->TIPO_TEXTO,  12, "R");
$reporte->definirColumna(4, "CTA. MANT.", $reporte->TIPO_TEXTO,  12, "R");
$reporte->definirColumna(5, "TOTAL",      $reporte->TIPO_TEXTO,  12, "R");
$reporte->definirColumna(6, "DIFERENCIA", $reporte->TIPO_TEXTO,  12, "R");
$reporte->definirColumna(7, "DIFERENCIA", $reporte->TIPO_TEXTO,  12, "R");
if (pg_numrows($rs_anticipados) > 0) {
    
    for ($i = 0; $i < pg_numrows($rs_anticipados); $i++) {
        $A = pg_fetch_array($rs_anticipados, $i);
        $reporte->irFila($reporte->agregarFila());
        $reporte->poneValor($A['clientes'], 0);
		$reporte->poneValor($A['facturas'], 1);
        $reporte->poneValor($A['importes'], 2);
        $reporte->poneValor($A['vales'], 3);
        $reporte->poneValor($A['mant'], 4);
        $reporte->poneValor($A['total'], 5);
        $reporte->poneValor($A['dif1'], 6);
        $reporte->poneValor($A['dif2'], 7);
        $TOTALES['ANTICIPOS']['importes'] += $A['importes'];
        $TOTALES['ANTICIPOS']['vales'] += $A['vales'];
        $TOTALES['ANTICIPOS']['mant'] += $A['mant'];
        $TOTALES['ANTICIPOS']['total'] += $A['total'];
        $TOTALES['ANTICIPOS']['dif1'] += $A['dif1'];
        $TOTALES['ANTICIPOS']['dif2'] += $A['dif2'];
        $TOTALES['TOTALES']['importes'] += $A['importes'];
        $TOTALES['TOTALES']['vales'] += $A['vales'];
        $TOTALES['TOTALES']['mant'] += $A['mant'];
        $TOTALES['TOTALES']['total'] += $A['total'];
        $TOTALES['TOTALES']['dif1'] += $A['dif1'];
        $TOTALES['TOTALES']['dif2'] += $A['dif2'];
        $TOTALES['TOTALES']['VALES'] = $TOTALES['TOTALES']['vales'] + $TOTALES['TOTALES']['dif2'] - $TOTALES['TOTALES']['mant'];
    }
    //$reporte->generar();
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('TOTAL', 0);
$reporte->poneValor('', 1);
$reporte->poneValor('', 2);
$reporte->poneValor($TOTALES['ANTICIPOS']['vales'], 3);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar(false);

/******************************* FIN DEL INFORME DE ANTICIPOS *****************************/


$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Totales         ", 0);
$reporte->generar(false);
$reporte->nuevoInforme(8);
$reporte->definirColumna(0, "CLIENTES",   $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(1, "FACTURAS",   $reporte->TIPO_STRING, 10, "R");
$reporte->definirColumna(2, "IMPORTES",   $reporte->TIPO_IMPORTE,  12, "R");
$reporte->definirColumna(3, "VALES",      $reporte->TIPO_TEXTO,  12, "R");
$reporte->definirColumna(4, "CTA. MANT.", $reporte->TIPO_TEXTO,  12, "R");
$reporte->definirColumna(5, "TOTAL",      $reporte->TIPO_TEXTO,  12, "R");
$reporte->definirColumna(6, "DIFERENCIA", $reporte->TIPO_TEXTO,  12, "R");
$reporte->definirColumna(7, "DIFERENCIA", $reporte->TIPO_TEXTO,  12, "R");
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('', 0);
$reporte->poneValor('', 1);
$reporte->poneValor($TOTALES['TOTALES']['importes'], 2);
$reporte->poneValor($TOTALES['TOTALES']['vales'], 3);
$reporte->poneValor($TOTALES['TOTALES']['mant'], 4);
$reporte->poneValor($TOTALES['TOTALES']['total'], 5);
$reporte->poneValor($TOTALES['TOTALES']['dif1'], 6);
$reporte->poneValor($TOTALES['TOTALES']['dif2'], 7);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar(false);

/******************************** INICIO DEL INFORME DE CONTADO *************************/

$reporte->nuevoInforme(2);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 50, "L");
$reporte->definirColumna(1, " ",   $reporte->TIPO_STRING, 12, "R");
$reporte->poneValor("C O N C I L I A C I O N", 0);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor("SALDO SEGUN FACTURAS ", 0);
$reporte->poneValor($TOTALES['TOTALES']['importes'], 1);
$reporte->generar(false);

$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Contado y Otros", 0);
$reporte->generar(false);
$reporte->nuevoInforme(3);
//$cabecera[0]['texto'] = "Reporte de Cuadre de Facturacion - Contado Whiz y Otros";
//$reporte->ponerCabecera($cabecera);
$reporte->definirColumna(0, "FACTURAS", $reporte->TIPO_STRING, 10, "L");
$reporte->definirColumna(1, "CLIENTES", $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(2, "IMPORTES", $reporte->TIPO_IMPORTE,  12, "R");
if (pg_numrows($rs_facturas_contado) > 0) {
    for ($i = 0; $i < pg_numrows($rs_facturas_contado); $i++) {
        $A = pg_fetch_array($rs_facturas_contado, $i);
        $reporte->irFila($reporte->agregarFila());
        $reporte->poneValor($A['facturas'], 0);
        $reporte->poneValor($A['clientes'], 1);
        $reporte->poneValor($A['importes'], 2);
        $TOTALES['CONTADO']['importes'] += $A['importes'];
        $TOTALES['TOTALES']['importes'] -= $A['importes'];
    }

    //$reporte->generar();
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('TOTAL', 0);
$reporte->poneValor('', 1);
$reporte->poneValor($TOTALES['CONTADO']['importes'].'(-)', 2);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar(false);

/************************************ FIN DEL INFORME DE CONTADO ***********************/

/**************************** INICIO DEL INFORME DE VALES NO FACTURADOS ***********************/
$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Vales no facturados", 0);
$reporte->generar(false);
$reporte->nuevoInforme(3);
//$cabecera[0]['texto'] = "Reporte de Cuadre de Facturacion - Vales no facturados";
//$reporte->ponerCabecera($cabecera);
$reporte->definirColumna(0, "VALES",    $reporte->TIPO_STRING, 10, "L");
$reporte->definirColumna(1, "CLIENTES", $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(2, "IMPORTES", $reporte->TIPO_IMPORTE,  12, "R");
if (pg_numrows($rs_vales_no_facturados) > 0) {
    
    for ($i = 0; $i < pg_numrows($rs_vales_no_facturados); $i++) {
        $A = pg_fetch_array($rs_vales_no_facturados, $i);
        $reporte->irFila($reporte->agregarFila());
        $reporte->poneValor($A['facturas'], 0);
        $reporte->poneValor($A['clientes'], 1);
        $reporte->poneValor($A['importes'], 2);
        $TOTALES['VALESN']['importes'] += $A['importes'];
        $TOTALES['TOTALES']['importes'] += $A['importes'];
    }
    //$reporte->generar();
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('TOTAL', 0);
$reporte->poneValor('', 1);
$reporte->poneValor(($TOTALES['VALESN']['importes']==''?'0.00':$TOTALES['VALESN']['importes']).'(+)', 2);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar(false);

/******************************* FIN DEL INFORME DE VALES NO FACTURADOS *********************/

/***************************** INICIO DEL INFORME DE VALES LIQUIDADOS NO FACTURADOS *************/
$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Vales liquidados no facturados", 0);
$reporte->generar(false);
$reporte->nuevoInforme(3);
//$cabecera[0]['texto'] = "Reporte de Cuadre de Facturacion - Vales liquidados no facturados";
//$reporte->ponerCabecera($cabecera);
$reporte->definirColumna(0, "VALES",    $reporte->TIPO_STRING, 10, "L");
$reporte->definirColumna(1, "CLIENTES", $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(2, "IMPORTES", $reporte->TIPO_IMPORTE,  12, "R");
if (pg_numrows($rs_vales_liquidados_no_facturados) > 0) {
     for ($i = 0; $i < pg_numrows($rs_vales_liquidados_no_facturados); $i++) {
        $A = pg_fetch_array($rs_vales_liquidados_no_facturados, $i);
        $reporte->irFila($reporte->agregarFila());
        $reporte->poneValor($A['facturas'], 0);
        $reporte->poneValor($A['clientes'], 1);
        $reporte->poneValor($A['importes'], 2);
        $TOTALES['VALESL']['importes'] += $A['importes'];
        $TOTALES['TOTALES']['importes'] += $A['importes'];
    }
    //$reporte->generar();
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('TOTAL', 0);
$reporte->poneValor('', 1);
$reporte->poneValor(($TOTALES['VALESL']['importes']==''?'0.00':$TOTALES['VALESL']['importes']).'(+)', 2);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar(false);

/******************************** FIN DEL INFORME DE VALES LIQUIDADOS NO FACTURADOS ************/

/************************* INICIO DEL INFORME DE VALES PAGO ADELANTADO ********************/
$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Vales de pago Adelantado", 0);
$reporte->generar(false);
$reporte->nuevoInforme(3);
//$cabecera[0]['texto'] = "Reporte de Cuadre de Facturacion - Vales de Pago Adelantado";
//$reporte->ponerCabecera($cabecera);
$reporte->definirColumna(0, "VALES",    $reporte->TIPO_STRING, 10, "L");
$reporte->definirColumna(1, "CLIENTES", $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(2, "IMPORTES", $reporte->TIPO_IMPORTE,  12, "R");
if (pg_numrows($rs_vales_pago_adelantado) > 0) {
    for ($i = 0; $i < pg_numrows($rs_vales_pago_adelantado); $i++) {
        $A = pg_fetch_array($rs_vales_pago_adelantado, $i);
        $reporte->irFila($reporte->agregarFila());
        $reporte->poneValor($A['facturas'], 0);
        $reporte->poneValor($A['clientes'], 1);
        $reporte->poneValor($A['importes'], 2);
        $TOTALES['VALESG']['importes'] += $A['importes'];
        $TOTALES['TOTALES']['importes'] += $A['importes'];
    }
    //$reporte->generar();
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('TOTAL', 0);
$reporte->poneValor('', 1);
$reporte->poneValor(($TOTALES['VALESG']['importes']==''?'0.00':$TOTALES['VALESG']['importes']).'(+)', 2);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar(false);


/***************************** FIN DEL INFORME DE VALES PAGO ADELANTADO *************************/

/************************* INICIO DEL INFORME DE VALES PASADO ********************/
$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Vales liquidados el mes pasado facturados este mes", 0);
$reporte->generar(false);
$reporte->nuevoInforme(3);
//$cabecera[0]['texto'] = "Reporte de Cuadre de Facturacion - Vales de Pago Pasado Mes";
//$reporte->ponerCabecera($cabecera);
$reporte->definirColumna(0, "VALES",    $reporte->TIPO_STRING, 10, "L");
$reporte->definirColumna(1, "CLIENTES", $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(2, "IMPORTES", $reporte->TIPO_IMPORTE,  12, "R");
if (pg_numrows($rs_vales_mes_pasado) > 0) {
    for ($i = 0; $i < pg_numrows($rs_vales_mes_pasado); $i++) {
        $A = pg_fetch_array($rs_vales_mes_pasado, $i);
        $reporte->irFila($reporte->agregarFila());
        $reporte->poneValor($A['facturas'], 0);
        $reporte->poneValor($A['clientes'], 1);
        $reporte->poneValor($A['importes'], 2);
        $TOTALES['VALESP']['importes'] += $A['importes'];
        $TOTALES['TOTALES']['importes'] += $A['importes'];
    }
   // $reporte->generar();
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('TOTAL', 0);
$reporte->poneValor('', 1);
$reporte->poneValor(($TOTALES['VALESP']['importes']==''?'0.00':$TOTALES['VALESP']['importes']).'(+)', 2);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar(false);

/***************************** FIN DEL INFORME DE VALES PASADO *************************/


/********************* INICIO DEL INFORME DE CUENTA DE MANTEMINIENTO **********************/
$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Cuenta de Mantenimiento", 0);
$reporte->generar(false);
$reporte->nuevoInforme(3);
//$cabecera[0]['texto'] = "Reporte de Cuadre de Facturacion - Cuenta de Manteminiento";
//$reporte->ponerCabecera($cabecera);
$reporte->definirColumna(0, "FACTURAS", $reporte->TIPO_STRING, 10, "L");
$reporte->definirColumna(1, "CLIENTES", $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(2, "IMPORTES", $reporte->TIPO_IMPORTE,  12, "R");
if (pg_numrows($rs_cuenta_mantenimiento) > 0) {
    
    for ($i = 0; $i < pg_numrows($rs_cuenta_mantenimiento); $i++) {
        $A = pg_fetch_array($rs_cuenta_mantenimiento, $i);
        $reporte->irFila($reporte->agregarFila());
        $reporte->poneValor($A['facturas'], 0);
        $reporte->poneValor($A['clientes'], 1);
        $reporte->poneValor($A['importes'], 2);
        $TOTALES['CUENTA']['importes'] += $A['importes'];
        $TOTALES['TOTALES']['importes'] -= $A['importes'];
    }
    //$reporte->generar();
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('TOTAL', 0);
$reporte->poneValor('', 1);
$reporte->poneValor(($TOTALES['CUENTA']['importes']==''?'0.00':$TOTALES['CUENTA']['importes']).'(-)', 2);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->generar(false);

/*************************** FIN DEL INFORME DE CUENTA DE MANTEMINIENTO ***********************/
for ($i = 0; $i < pg_numrows($rs_ventas_credito_det); $i++) {
		$A = pg_fetch_array($rs_ventas_credito_det, $i);
		$TOTALES['TOTAL']['FINAL'] += $A['totaldia'];
}
$reporte->nuevoInforme(1);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->definirColumna(0, " ",   $reporte->TIPO_STRING, 160, "L");
$reporte->poneValor("Reporte de Cuadre de Facturacion - Diferencias", 0);
$reporte->generar(false);
//$reporte->nuevoInforme(3);
$reporte->nuevoInforme(4);
//$cabecera[0]['texto'] = "Reporte de Cuadre de Facturacion - Diferencias";
//$reporte->ponerCabecera($cabecera);
$reporte->definirColumna(0, "FACTURAS", $reporte->TIPO_STRING, 10, "L");
$reporte->definirColumna(1, "CLIENTES", $reporte->TIPO_STRING, 40, "L");
$reporte->definirColumna(2, "IMPORTES", $reporte->TIPO_IMPORTE,  12, "R");
$reporte->definirColumna(3, " ", $reporte->TIPO_IMPORTE,  12, "R");
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('IMPORTE', 0);
$reporte->poneValor('', 1);
$reporte->poneValor($TOTALES['TOTALES']['importes'], 2);
$reporte->poneValor('', 3);
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('VALES', 0);
$reporte->poneValor('', 1);
$reporte->poneValor($TOTALES['TOTAL']['FINAL'], 2);
$reporte->poneValor('', 3);
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor('DIFERENCIA', 0);
$reporte->poneValor('', 1);
$reporte->poneValor(round($TOTALES['TOTALES']['importes']-$TOTALES['TOTAL']['FINAL'],2), 2);
$reporte->poneValor('', 3);
$reporte->generar(false);

$TOTALES['TOTAL']['FINAL']=0;
/************* INICIO DEL INFORME DE VENTAS POR CENTRO DE COSTO POR DIA **************/
/*
$reporte->nuevoInforme(17);
    
    $cabecera[0]['texto'] = "Reporte de Ventas por Centro de Costo por Dia";
    $reporte->ponerCabecera($cabecera);
    
   
    $reporte->definirColumna(0, "DIA", $reporte->TIPO_STRING, 3, "C");
    $reporte->definirColumna(1, "OFICINA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(2, "CASTILLA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(3, "MAGDALENA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(4, "FAUCETT", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(5, "BRENA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(6, "LA PERLA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(7, "SUCRE", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(8, "RISSO", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(9, "SAN LUIS", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(10, "LA MARINA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(11, "ORRANTIA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(12, "ALEGRIA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(13, "EJERCITO", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(14, "SAN ISIDRO", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(15, "SAN BORJA", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna(16, "TOTAL", $reporte->TIPO_IMPORTE, 11, "R");
if (pg_numrows($rs_ventas_credito_det) > 0) {
    
   
    for ($i = 0; $i < pg_numrows($rs_ventas_credito_det); $i++) {
		$A = pg_fetch_array($rs_ventas_credito_det, $i);
		$reporte->irFila($reporte->agregarFila());
		$reporte->poneValor($A['dia'], 0);
		$reporte->poneValor($A['importe_oficina'], 1);
		$reporte->poneValor($A['importe_castilla'], 2);
		$reporte->poneValor($A['importe_magdalena'], 3);
		$reporte->poneValor($A['importe_faucet'], 4);
		$reporte->poneValor($A['importe_brena'], 5);
		$reporte->poneValor($A['importe_perla'], 6);
		$reporte->poneValor($A['importe_sucre'], 7);
		$reporte->poneValor($A['importe_risso'], 8);
		$reporte->poneValor($A['importe_luis'], 9);
		$reporte->poneValor($A['importe_marina'], 10);
		$reporte->poneValor($A['importe_orrantia'], 11);
		$reporte->poneValor($A['importe_alegria'], 12);
		$reporte->poneValor($A['importe_ejercito'], 13);
		$reporte->poneValor($A['importe_isidro'], 14);
		$reporte->poneValor($A['importe_borja'], 15);
		$reporte->poneValor($A['totaldia'], 16);
		$TOTALES['TOTAL']['OFICINA'] += $A['importe_oficina'];
		$TOTALES['TOTAL']['CASTILLA'] += $A['importe_castilla'];
		$TOTALES['TOTAL']['MAGDALENA'] += $A['importe_magdalena'];
		$TOTALES['TOTAL']['FAUCET'] += $A['importe_faucet'];
		$TOTALES['TOTAL']['BRENA'] += $A['importe_brena'];
		$TOTALES['TOTAL']['PERLA'] += $A['importe_perla'];
		$TOTALES['TOTAL']['SUCRE'] += $A['importe_sucre'];
		$TOTALES['TOTAL']['RISSO'] += $A['importe_risso'];
		$TOTALES['TOTAL']['LUIS'] += $A['importe_luis'];
		$TOTALES['TOTAL']['MARINA'] += $A['importe_marina'];
		$TOTALES['TOTAL']['ORRANTIA'] += $A['importe_orrantia'];
		$TOTALES['TOTAL']['ALEGRIA'] += $A['importe_alegria'];
		$TOTALES['TOTAL']['EJERCITO'] += $A['importe_ejercito'];
		$TOTALES['TOTAL']['ISIDRO'] += $A['importe_isidro'];
		$TOTALES['TOTAL']['BORJA'] += $A['importe_borja'];
		$TOTALES['TOTAL']['FINAL'] += $A['totaldia'];
    }
    
}
$reporte->agregarFila();
$reporte->irFila($reporte->agregarFila());
$reporte->poneValor("TOTAL", 0);
$reporte->poneValor($TOTALES['TOTAL']['OFICINA'], 1);
    $reporte->poneValor($TOTALES['TOTAL']['CASTILLA'], 2);
    $reporte->poneValor($TOTALES['TOTAL']['MAGDALENA'], 3);
    $reporte->poneValor($TOTALES['TOTAL']['FAUCET'], 4);
    $reporte->poneValor($TOTALES['TOTAL']['BRENA'], 5);
    $reporte->poneValor($TOTALES['TOTAL']['PERLA'], 6);
    $reporte->poneValor($TOTALES['TOTAL']['SUCRE'], 7);
    $reporte->poneValor($TOTALES['TOTAL']['RISSO'], 8);
    $reporte->poneValor($TOTALES['TOTAL']['LUIS'], 9);
	$reporte->poneValor($TOTALES['TOTAL']['MARINA'], 10);
    $reporte->poneValor($TOTALES['TOTAL']['ORRANTIA'], 11);
    $reporte->poneValor($TOTALES['TOTAL']['ALEGRIA'], 12);
	$reporte->poneValor($TOTALES['TOTAL']['EJERCITO'], 13);
	$reporte->poneValor($TOTALES['TOTAL']['ISIDRO'], 14);
	$reporte->poneValor($TOTALES['TOTAL']['BORJA'], 15);
	$reporte->poneValor($TOTALES['TOTAL']['FINAL'], 16);
	$reporte->agregarFila();
	$reporte->irFila($reporte->agregarFila());
	$reporte->generar();
*/
/******************* FIN DEL INFORME DE VENTAS POR CENTRO DE COSTO POR DIA ***************/
$reporte->mostrar();
