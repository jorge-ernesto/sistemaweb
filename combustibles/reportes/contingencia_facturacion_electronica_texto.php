<?php
session_start();
date_default_timezone_set('UTC');

$data			= $_SESSION['data'];
$nuruc			= $_SESSION['nuruc'];
$txtnofechaini	= $_SESSION['txtnofechaini'];
$txtnofechafin	= $_SESSION['txtnofechafin'];
$iCantidadEnviado	= $_SESSION['iCantidadEnviado'];
$texto 			= null;

foreach($data as $rows){
	$texto .= $rows['nomotivo_contingencia'].'|';
	$texto .= $rows['tipoop'].'|';
	$texto .= $rows['femision'].'|';
	$texto .= $rows['nutd'].'|';
	$texto .= trim($rows['noserie']).'|';
	$texto .= $rows['nudocumento_inicial'].'|';
	$texto .= $rows['nudocumento_final'].'|';
	$texto .= $rows['nutd_identidad'].'|';
	$texto .= trim($rows['nudocumento_identidad']).'|';
	$texto .= $rows['nodocumento_identidad'].'|';
	$texto .= $rows['moneda'].'|';
	$texto .= number_format($rows['nuvalor_venta_og'],2,".","").'|';
	$texto .= number_format($rows['nuvalor_venta_oe'],2,".","").'|';
	$texto .= number_format($rows['nuvalor_venta_oi'],2,".","").'|';
	$texto .= number_format($rows['nuvalor_venta_ex'],2,".","").'|';
	$texto .= number_format($rows['nuisc'],2,".","").'|';
	$texto .= number_format($rows['nuigv'],2,".","").'|';
	$texto .= number_format($rows['nuotros_cargos'],2,".","").'|';
	$texto .= $rows['nutotal'].'|';
	$texto .= $rows['nutd_referencia'];
	$texto .= $rows['nuserie_referencia'];
	$texto .= $rows['nunumero_referencia'];
	$texto .= "|0.00|0.00|0.00|\r\n";
}

$date 	= explode('-', $txtnofechaini);
$year 	= $date[0];
$month 	= $date[1];
$day 	= $date[2];

$hoy = $year.$month.$day;

//RC = Régimen de Contingencia
$nombre_archivo = $nuruc . "-RF-" . $hoy . "-" . $iCantidadEnviado . ".txt";

header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

die($texto);