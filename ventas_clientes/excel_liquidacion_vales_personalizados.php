<?php

include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
include_once('/sistemaweb/include/Classes/PHPExcel.php');

//Datos GET
$nu_liquidacion = strip_tags($_GET["ch_liquidacion"]);
$nu_codigo_cliente = strip_tags($_GET["ch_cliente"]);
$no_tipo_documento = strip_tags($_GET["no_tipo_documento"]);
$nu_tipo_documento = strip_tags($_GET["nu_tipo_documento"]);
$nu_serie_documento = strip_tags($_GET["nu_serie_documento"]);
$nu_numero_documento = strip_tags($_GET["nu_numero_documento"]);

//get IGV
$sqlca->query("
SELECT
	1 + ROUND(tab_num_01 / 100,2) igv
FROM
	int_tabla_general
WHERE
	TRIM(tab_tabla||tab_elemento) = (SELECT par_valor FROM int_parametros WHERE TRIM(par_nombre) = 'igv actual')
");

$row = $sqlca->fetchRow();
$nu_igv = $row['igv'];

error_reporting(E_ALL);
date_default_timezone_set('UTC');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
	->setLastModifiedBy("OpenSysperu")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");

// Add some data
$cabecera = array('fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('argb' => 'FFFFFFFF')
				),
					'borders' => array(
					'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
		)
);

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);
$hoja = 0;

//Estilo de bordes
$BStyle = array(
  'borders' => array(
    'outline' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);

$top = array(
  'borders' => array(
    'top' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);

$right = array(
  'borders' => array(
    'right' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);

$left = array(
  'borders' => array(
    'left' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);

//get Datos del Cliente y Factura
$sqlca->query("
SELECT cli_razsocial FROM int_clientes WHERE cli_codigo = '" . $nu_codigo_cliente . "'
");
$row = $sqlca->fetchRow();
$no_cliente = $row['cli_razsocial'];

$nu_fila = 1;

$objRichText = new PHPExcel_RichText();
$objBold1 = $objRichText->createTextRun("Cliente: ");
$objBold1->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getCell('B' . $nu_fila)->setValue($objRichText);

$objPHPExcel->setActiveSheetIndex($hoja)
->setCellValue('C'.$nu_fila, $no_cliente);

$labelruc = new PHPExcel_RichText();
$objBold1 = $labelruc->createTextRun("RUC: ");
$objBold1->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getCell('D' . $nu_fila)->setValue($labelruc);

$objPHPExcel->setActiveSheetIndex($hoja)
->setCellValue('E'.$nu_fila, $nu_codigo_cliente);

//Get Datos de la factura
//Get Fecha de Emision
$sqlca->query("
 SELECT
 	TO_CHAR(dt_fac_fecha, 'dd/mm/YYYY') AS Fe_Emision
 FROM
 	fac_ta_factura_cabecera
 WHERE
 	ch_fac_tipodocumento = '" . $nu_tipo_documento . "'
 	AND ch_fac_seriedocumento = '" . $nu_serie_documento . "'
 	AND ch_fac_numerodocumento = '" . $nu_numero_documento . "'
 	AND cli_codigo = '" . $nu_codigo_cliente . "'
");
$row = $sqlca->fetchRow();
$fe_emision = $row['fe_emision'];

$nu_fila = 2;
$labelfecha = new PHPExcel_RichText();
$objBold1 = $labelfecha->createTextRun("Fecha: ");
$objBold1->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getCell('B' . $nu_fila)->setValue($labelfecha);
$objPHPExcel->setActiveSheetIndex($hoja)
->setCellValue('C'.$nu_fila, $fe_emision);

$nu_fila = 3;
$labeltipo = new PHPExcel_RichText();
$objBold1 = $labeltipo->createTextRun("Tipo Documento: ");
$objBold1->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getCell('B' . $nu_fila)->setValue($labeltipo);
$objPHPExcel->setActiveSheetIndex($hoja)
->setCellValue('C'.$nu_fila, $no_tipo_documento);

$nu_fila = 4;
$labelserie = new PHPExcel_RichText();
$objBold1 = $labelserie->createTextRun("Serie Documento: ");
$objBold1->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getCell('B' . $nu_fila)->setValue($labelserie);
$objPHPExcel->setActiveSheetIndex($hoja)
->setCellValue('C'.$nu_fila, $nu_serie_documento);

$nu_fila = 5;
$labelnumero = new PHPExcel_RichText();
$objBold1 = $labelnumero->createTextRun("Numero Documento: ");
$objBold1->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getCell('B' . $nu_fila)->setValue($labelnumero);
$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(2, $nu_fila, $nu_numero_documento, PHPExcel_Cell_DataType::TYPE_STRING);

$nu_fila = 6;
$labelmoneda = new PHPExcel_RichText();
$objBold1 = $labelmoneda->createTextRun("Moneda: ");
$objBold1->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getCell('B' . $nu_fila)->setValue($labelmoneda);
$objPHPExcel->setActiveSheetIndex($hoja)
->setCellValue('C'.$nu_fila, 'Soles');

$nu_fila = 7;
//Formato de titulo
$objPHPExcel->getActiveSheet()->getRowDimension($nu_fila)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('G' . $nu_fila . ':J' . $nu_fila)->applyFromArray($cabecera);
$objPHPExcel->getActiveSheet()->getStyle('G' . $nu_fila . ':J' . $nu_fila)->getFont()->setBold(true);
//Unir celdas
$objPHPExcel->getActiveSheet()->mergeCells('H'.$nu_fila.':I'.$nu_fila);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
//Bordes
$objPHPExcel->getActiveSheet()->getStyle('G'.$nu_fila.':J'.$nu_fila)->applyFromArray($top);
$objPHPExcel->getActiveSheet()->getStyle('G'.$nu_fila)->applyFromArray($left);

$objPHPExcel->setActiveSheetIndex($hoja)
->setCellValue('H'.$nu_fila, 'Soles (S/.)');

$nu_fila = 8;
//Formato de titulo
$objPHPExcel->getActiveSheet()->getRowDimension($nu_fila)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A' . $nu_fila . ':J' . $nu_fila)->applyFromArray($cabecera);
$objPHPExcel->getActiveSheet()->getStyle('A' . $nu_fila . ':J' . $nu_fila)->getFont()->setBold(true);
//Bordes
$objPHPExcel->getActiveSheet()->getStyle('A'.$nu_fila.':J'.$nu_fila)->applyFromArray($top);
$objPHPExcel->getActiveSheet()->getStyle('B'.$nu_fila)->applyFromArray($left);
$objPHPExcel->getActiveSheet()->getStyle('C'.$nu_fila)->applyFromArray($left);
$objPHPExcel->getActiveSheet()->getStyle('D'.$nu_fila)->applyFromArray($left);
$objPHPExcel->getActiveSheet()->getStyle('E'.$nu_fila)->applyFromArray($left);
$objPHPExcel->getActiveSheet()->getStyle('F'.$nu_fila)->applyFromArray($left);
$objPHPExcel->getActiveSheet()->getStyle('G'.$nu_fila)->applyFromArray($left);
$objPHPExcel->getActiveSheet()->getStyle('H'.$nu_fila)->applyFromArray($left);
$objPHPExcel->getActiveSheet()->getStyle('I'.$nu_fila)->applyFromArray($left);
$objPHPExcel->getActiveSheet()->getStyle('J'.$nu_fila)->applyFromArray($left);

$objPHPExcel->setActiveSheetIndex($hoja)
->setCellValue('A'.$nu_fila, 'Item')
->setCellValue('B'.$nu_fila, 'Fecha')
->setCellValue('C'.$nu_fila, 'Descripción de Item / Servicio')
->setCellValue('D'.$nu_fila, 'Placa')
->setCellValue('E'.$nu_fila, 'Kilometraje')
->setCellValue('F'.$nu_fila, 'Cantidad')
->setCellValue('G'.$nu_fila, 'Costo Unit.')
->setCellValue('H'.$nu_fila, 'Valor de Venta')
->setCellValue('I'.$nu_fila, 'IGV')
->setCellValue('J'.$nu_fila, 'Total');

//get Lista de Liquidaciones
$sqlca->query("
SELECT
	VC.dt_fecha AS Fe_Emision,
	PRO.art_descripcion AS No_Producto,
	PLACA.numpla AS No_Placa,
	VD.nu_cantidad AS Nu_Cantidad,
	VC.nu_odometro AS Nu_Kilometraje,
	ROUND(COALESCE(VD.nu_importe, 0) / COALESCE(VD.nu_cantidad, 1), 4) AS Nu_Precio_Venta,
	ROUND(VD.nu_importe / " . $nu_igv . ", 4) AS nu_valor_venta,
	ROUND(VD.nu_importe - (VD.nu_importe / " . $nu_igv . "), 4) AS nu_igv,
	ROUND(VD.nu_importe, 4) AS nu_total
FROM
	val_ta_complemento_documento AS VTCD
	JOIN val_ta_cabecera AS VC ON (VC.dt_fecha = VTCD.dt_fecha AND VTCD.ch_numeval = VC.ch_documento)
	JOIN val_ta_detalle AS VD ON (VC.dt_fecha = VD.dt_fecha AND VC.ch_documento = VD.ch_documento)
	--JOIN val_ta_cabecera AS VC ON (VC.ch_sucursal = VTCD.ch_sucursal AND VC.dt_fecha = VTCD.dt_fecha AND VTCD.ch_numeval = VC.ch_documento)
	--JOIN val_ta_detalle AS VD ON (VC.ch_sucursal = VD.ch_sucursal AND VC.dt_fecha = VD.dt_fecha AND VC.ch_documento = VD.ch_documento)
	JOIN pos_fptshe1 AS PLACA ON (PLACA.numtar = VC.ch_tarjeta)
	JOIN int_articulos AS PRO ON (PRO.art_codigo = VD.ch_articulo)
WHERE
	VTCD.ch_liquidacion = '" . $nu_liquidacion . "'
	AND VTCD.ch_cliente = '" . $nu_codigo_cliente . "'
	AND VTCD.ch_fac_tipodocumento = '" . $nu_tipo_documento . "'
	AND VTCD.ch_fac_seriedocumento = '" . $nu_serie_documento . "'
	AND VTCD.ch_fac_numerodocumento = '" . $nu_numero_documento . "'
ORDER BY
	VC.fecha_replicacion;
");

$sum_cantidad = 0.00;
$sum_valor_venta = 0.00;
$sum_igv = 0.00;
$sum_total = 0.00;

$nu_fila = 9;
$objPHPExcel->getActiveSheet()->freezePane('A'.$nu_fila);

//Formato de columnas
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);

$counter = 1;

for ($i = 0; $i < $sqlca->numrows(); $i++) {
    $row = $sqlca->fetchRow();
	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A' . $nu_fila, $counter)
	->setCellValue('B' . $nu_fila, $row["fe_emision"])
	->setCellValue('C' . $nu_fila, $row["no_producto"])
	->setCellValue('D' . $nu_fila, $row["no_placa"])
	->setCellValue('E' . $nu_fila, $row["nu_kilometraje"])
	->setCellValue('F' . $nu_fila, $row["nu_cantidad"])
	->setCellValue('G' . $nu_fila, $row["nu_precio_venta"])
	->setCellValue('H' . $nu_fila, $row["nu_valor_venta"])
	->setCellValue('I' . $nu_fila, $row["nu_igv"])
	->setCellValue('J' . $nu_fila, $row["nu_total"]);
	$sum_cantidad += $row['nu_cantidad'];
	$sum_valor_venta += $row['nu_valor_venta'];
	$sum_igv += $row['nu_igv'];
	$sum_total += $row['nu_total'];
	$counter++;
	$nu_fila++;
}

$nu_fila++;
$objRichText = new PHPExcel_RichText();
$objBold1 = $objRichText->createTextRun("TOTALES: ");
$objBold1->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getCell('G' . $nu_fila)->setValue($objRichText);

$objRichText2 = new PHPExcel_RichText();
$objBold1 = $objRichText2->createTextRun($sum_valor_venta);
$objPHPExcel->getActiveSheet()->getCell('H' . $nu_fila)->setValue($objRichText2);

$objRichText2 = new PHPExcel_RichText();
$objBold1 = $objRichText2->createTextRun($sum_igv);
$objPHPExcel->getActiveSheet()->getCell('I' . $nu_fila)->setValue($objRichText2);

$objRichText2 = new PHPExcel_RichText();
$objBold1 = $objRichText2->createTextRun($sum_total);
$objPHPExcel->getActiveSheet()->getCell('J' . $nu_fila)->setValue($objRichText2);

//$objPHPExcel->getActiveSheet()->getStyle('G7:G' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="LiquidacionValesPersonalizados.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

exit;