<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();

include_once('../../include/Classes/PHPExcel.php');

error_reporting(E_ALL);

ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

date_default_timezone_set('Europe/London');

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

$objPHPExcel->setActiveSheetIndex(0);
$hoja = 0;



/* GET VARIABLES DE ENTRADA */

$data 		= $_SESSION['data'];
$noalmacen 	= $_SESSION['noalmacen'];
$femision 	= $_SESSION['fbuscar'];
$notipo 	= $_SESSION['notipo'];

$noarchivo 	= "Informe_Ajuste_Ubicacion";
$titulo 	= "REPORTE AJUSTE INVENTARIO DE UBICACIONES";

/* ESTILO DE HEADER */

$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);

/* FIN */

/* TITULO DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A1', $titulo)
		->setCellValue('B2', 'Almacen: ')
		->setCellValue('C2', $noalmacen)
		->setCellValue('D2', 'Fecha: ')
		->setCellValue('E2', $femision);

$objPHPExcel->getActiveSheet()->getStyle('B2:E2')->getFont()->setBold(true);

/* FIN */

/* TABLE HEADER DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A4', 'FECHA')
		->setCellValue('B4', '# FORMULARIO')
		->setCellValue('C4', 'PRODUCTO')
		->setCellValue('D4', 'CANTIDAD')
		->setCellValue('E4', 'COSTO UNITARIO S/IGV')
		->setCellValue('F4', 'TOTAL');

$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('E4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('F4')->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->freezePane('A5');//LINEA HORIZONTAL


/* FIN */

$bucle = 5;

if (count($data) > 0) {

	$i	 		= 0;
	$noubicacion 		= null;
	$sumcantubicacion	= 0;
	$sumcostubicacion	= 0;
	$sumtotubicacion	= 0;
	$sumcantubicaciong	= 0;
	$sumcostubicaciong	= 0;
	$sumtotubicaciong	= 0;

	foreach ($data as $row) {

		if($noubicacion != $row['noubicacion']){

			$bucle++;

			if($i != 0){

				$objPHPExcel->setActiveSheetIndex($hoja)
		       		->setCellValue('C' . $bucle, 'Total Ubicacion: ')
		       		->setCellValue('D' . $bucle, $sumcantubicacion)
		       		->setCellValue('E' . $bucle, $sumcostubicacion)
		       		->setCellValue('F' . $bucle, $sumtotubicacion);

				$objPHPExcel->getActiveSheet()->getStyle('C' . $bucle .':'.'F' . $bucle)->getFont()->setBold(true);

				$sumcantubicacion 	= 0;
				$sumcostubicacion 	= 0;
				$sumtotubicacion 	= 0;

				$bucle++;

			}

			$bucle++;

			$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, 'Ubicacion: ')
	       		->setCellValue('B' . $bucle, $row['noubicacion']);

			$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle .':'.'B' . $bucle)->getFont()->setBold(true);

			$noubicacion 	= $row['noubicacion'];

		}

		$bucle++;

		if($notipo == "D"){

			$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, $row['femision'])
	       		->setCellValue('B' . $bucle, $row['nuformulario'])
	       		->setCellValue('C' . $bucle, $row['noproducto'])
	       		->setCellValue('D' . $bucle, $row['nucantidad'])
	       		->setCellValue('E' . $bucle, $row['nucosto'])
	       		->setCellValue('F' . $bucle, $row['nutotal']);

		}

		$sumcantubicacion 	+= $row['nucantidad'];
		$sumcostubicacion 	+= $row['nucosto'];
		$sumtotubicacion 	+= $row['nutotal'];

		$sumcantubicaciong 	+= $row['nucantidad'];
		$sumcostubicaciong 	+= $row['nucosto'];
		$sumtotubicaciong	+= $row['nutotal'];

		$i++;

	}

	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('C' . $bucle, 'Total Ubicacion: ')
	->setCellValue('D' . $bucle, $sumcantubicacion)
	->setCellValue('E' . $bucle, $sumcostubicacion)
	->setCellValue('F' . $bucle, $sumtotubicacion);

	$objPHPExcel->getActiveSheet()->getStyle('C' . $bucle .':'.'F' . $bucle)->getFont()->setBold(true);

	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('C' . $bucle, 'Total General: ')
	->setCellValue('D' . $bucle, $sumcantubicaciong)
	->setCellValue('E' . $bucle, $sumcostubicaciong)
	->setCellValue('F' . $bucle, $sumtotubicaciong);

	$objPHPExcel->getActiveSheet()->getStyle('C' . $bucle .':'.'F' . $bucle)->getFont()->setBold(true);


}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$noarchivo.'.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

