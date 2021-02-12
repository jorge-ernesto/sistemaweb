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
$nuyear 	= $_SESSION['nuyear'];
$numonth 	= $_SESSION['numonth'];
$notipo 	= $_SESSION['notipo'];

$noarchivo 	= "Informe_Stock_General_Linea_".$nuyear."_".$numonth;
$titulo 	= "REPORTE STOCK GENERAL POR LINEA";

/* ESTILO DE HEADER */

$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

/* FIN */

/* TITULO DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A1', $titulo)
		->setCellValue('A2', 'Almacen: ')
		->setCellValue('B2', $noalmacen)
		->setCellValue('C2', 'Año: ')
		->setCellValue('D2', $nuyear)
		->setCellValue('E2', 'Mes: ')
		->setCellValue('F2', $numonth);

$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);

/* FIN */

/* TABLE HEADER DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A4', 'PRODUCTO')
		->setCellValue('B4', 'UNIDAD MEDIDA')
		->setCellValue('C4', 'CANTIDAD')
		->setCellValue('D4', 'COSTO UNITARIO')
		->setCellValue('E4', 'TOTAL');

$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('E4')->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->freezePane('A5');//LINEA HORIZONTAL

/* FIN */

$bucle = 5;

if (count($data) > 0) {

	$i	 		= 0;
	$nolinea 		= null;
	$noalmacen		= null;
	$sumcantnolinea		= 0;
	$sumcostnolinea		= 0;
	$sumtotnolinea		= 0;
	$sumcantnolineag	= 0;
	$sumcostnolineag	= 0;
	$sumtotnolineag		= 0;

	foreach ($data as $row) {

		if($nolinea != $row['nolinea']){

			$bucle++;

			if($i != 0){

				$objPHPExcel->setActiveSheetIndex($hoja)
		       		->setCellValue('B' . $bucle, 'Total Linea: ')
		       		->setCellValue('C' . $bucle, $sumcantnolinea)
		       		->setCellValue('D' . $bucle, $sumcostnolinea)
		       		->setCellValue('E' . $bucle, $sumtotnolinea);

				$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle .':'.'F' . $bucle)->getFont()->setBold(true);

				$sumcantnolinea 	= 0;
				$sumcostnolinea 	= 0;
				$sumtotnolinea 		= 0;

				$bucle++;

			}

			if($noalmacen != $row['noalmacen']){

				$bucle++;

				$objPHPExcel->setActiveSheetIndex($hoja)
		       		->setCellValue('B' . $bucle, 'Almacen: ')
		       		->setCellValue('C' . $bucle, $row['noalmacen']);

				$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle .':'.'C' . $bucle)->getFont()->setBold(true);

				$noalmacen 	= $row['noalmacen'];

			}

			$bucle++;

			$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, 'Linea: ')
	       		->setCellValue('B' . $bucle, $row['nolinea']);

			$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle .':'.'B' . $bucle)->getFont()->setBold(true);

			$nolinea 	= $row['nolinea'];

		}

		$bucle++;

		$objPHPExcel->setActiveSheetIndex($hoja)
       		->setCellValue('A' . $bucle, $row['noproducto'])
       		->setCellValue('B' . $bucle, $row['nucodunidad'])
       		->setCellValue('C' . $bucle, $row['nucantidad'])
       		->setCellValue('D' . $bucle, $row['nucosto'])
       		->setCellValue('E' . $bucle, $row['nutotal']);

		$sumcantnolinea 	+= $row['nucantidad'];
		$sumcostnolinea 	+= $row['nucosto'];
		$sumtotnolinea	 	+= $row['nutotal'];

		$sumcantnolineag 	+= $row['nucantidad'];
		$sumcostnolineag 	+= $row['nucosto'];
		$sumtotnolineag		+= $row['nutotal'];

		$i++;

	}

	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('B' . $bucle, 'Total Linea: ')
	->setCellValue('C' . $bucle, $sumcantnolinea)
	->setCellValue('D' . $bucle, $sumcostnolinea)
	->setCellValue('E' . $bucle, $sumtotnolinea);

	$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle .':'.'F' . $bucle)->getFont()->setBold(true);

	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('B' . $bucle, 'Total General: ')
	->setCellValue('C' . $bucle, $sumcantnolineag)
	->setCellValue('D' . $bucle, $sumcostnolineag)
	->setCellValue('E' . $bucle, $sumtotnolineag);

	$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle .':'.'F' . $bucle)->getFont()->setBold(true);


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

