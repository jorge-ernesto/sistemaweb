<?php

include_once('/sistemaweb/include/libexcel/Worksheet.php');
include_once('/sistemaweb/include/libexcel/Workbook.php');

$workbook = new Workbook("archivo.xls");//$chrFileName
$formato0 = & $workbook->add_format();
$formato2 = & $workbook->add_format();
$formato5 = & $workbook->add_format();

$formato0->set_size(11);
$formato0->set_bold(1);
$formato0->set_align('left');
$formato2->set_size(10);
$formato2->set_bold(1);
$formato2->set_align('center');
$formato5->set_size(11);
$formato5->set_align('left');

$worksheet1 = & $workbook->add_worksheet('Hoja de Resultados Varillaje');
$worksheet1->set_column(0, 0, 16);
$worksheet1->set_column(1, 1, 50);
$worksheet1->set_column(2, 2, 12);
$worksheet1->set_column(3, 3, 12);
$worksheet1->set_column(4, 4, 12);
$worksheet1->set_column(5, 5, 16);
$worksheet1->set_column(6, 6, 16);

$worksheet1->set_zoom(100);
$worksheet1->set_landscape(100);

$worksheet1->write_string(1, 0, "MEDIDA DIARIA DE VARILLA", $formato0);


$worksheet1->write_string(5, 0, " ", $formato0);

$a = 7;
$worksheet1->write_string($a, 0, "FECHA", $formato2);
$worksheet1->write_string($a, 1, "TANQUE", $formato2);
$worksheet1->write_string($a, 2, "NOMBRE COMBUSTIBLE", $formato2);
$worksheet1->write_string($a, 3, "MEDICION", $formato2);
$worksheet1->write_string($a, 4, "RESPONSABLE", $formato2);

$a = 8;

/* for ($j=0; $j<count($res); $j++) {	
  $nomtanque = VarillasModel::obtenerTanques($almacen, $res[$j]['ch_tanque']);

  $worksheet1->write_string($a, 0, $res[$j]['dt_fecha'],$formato5);
  $worksheet1->write_string($a, 1, $nomtanque[$res[$j]['ch_tanque']],$formato5);
  $worksheet1->write_string($a, 2, $res[$j]['ch_nombre'],$formato5);
  $worksheet1->write_number($a, 3, number_format($res[$j]['nu_medicion'],3,'.',''),$formato5);
  $worksheet1->write_string($a, 4, $res[$j]['ch_responsable'],$formato5);
  $a++;
  } */

$workbook->close();

$chrFileName = "Varillaje";
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$chrFileName.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");
