<?php

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
					'color' => array('argb' => 'FFCCFFCC')
				),
					'borders' => array(
					'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
		)
);

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);
$hoja = 0;

$objPHPExcel->getActiveSheet()->freezePane('A6');
$bucle = 7;

if ($_SESSION['data_excel'] != null) {
	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A4', 'T. Doc.')
	->setCellValue('B4', 'Fecha Sistema')
        ->setCellValue('C4', 'Turno')
        ->setCellValue('D4', 'Fecha Emision')
        ->setCellValue('E4', 'Nro. Caja')
        ->setCellValue('F4', 'Nro. Ticket')
        ->setCellValue('G4', 'Importe')
        ->setCellValue('H4', 'Descripcion')
        ->setCellValue('I4', 'RUC')
        ->setCellValue('J4', 'Razon Social');

	$data = $_SESSION['data_excel'];

	if (count($data) > 0) {
        	$sumimporte = 0;
        	foreach ($data as $value) {

            		$objPHPExcel->setActiveSheetIndex($hoja)
                    	->setCellValue('A' . $bucle, $value["td"])
                    	->setCellValue('B' . $bucle, $value["dia"])
                    	->setCellValue('C' . $bucle, $value["turno"])
                    	->setCellValue('D' . $bucle, $value["fecha"])
                    	->setCellValue('E' . $bucle, $value["caja"])
                    	->setCellValue('F' . $bucle, $value["trans"])
                    	->setCellValue('G' . $bucle, $value["importe"])
                    	->setCellValue('H' . $bucle, $value["descripcion"])
                    	->setCellValue('I' . $bucle, $value["ruc"])
                    	->setCellValue('J' . $bucle, $value["razsocial"]);

			$bucle++;
		}
	}

	$objPHPExcel->getActiveSheet()->getStyle('G7:G' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$objPHPExcel->getActiveSheet()->getStyle('H7:H' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    	$objPHPExcel->getActiveSheet()->getStyle('N7:N' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    	$objPHPExcel->getActiveSheet()->getStyle('M7:M' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    	$objPHPExcel->getActiveSheet()->getStyle('Q7:Q' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    	$objPHPExcel->getActiveSheet()->getStyle('R7:R' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    	$objPHPExcel->getActiveSheet()->getStyle('T7:T' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    	$objPHPExcel->getActiveSheet()->getStyle('AJ7:AJ' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    	$objPHPExcel->getActiveSheet()->getStyle('AL7:AL' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
}


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Esmeralda.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

