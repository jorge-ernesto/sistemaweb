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
	->setCellValue('A4', 'Tipo')
	->setCellValue('B4', '#Tarjeta')
        ->setCellValue('C4', 'Tipo Tarjeta')
        ->setCellValue('D4', 'Caja')
        ->setCellValue('E4', 'Importe')
        ->setCellValue('F4', 'Nro. Ticket')
        ->setCellValue('G4', 'Fecha')
        ->setCellValue('H4', 'Hora');

	$data = $_SESSION['data_excel'];

	if (count($data) > 0) {
        	$sumimporte = 0;
        	foreach ($data as $value) {

            if($value["contador"] == 2){ 
                $contador = "AP/REF Repetido";
            }else{ 
                $contador = "";
            }
            		$objPHPExcel->setActiveSheetIndex($hoja)
                    	->setCellValue('A' . $bucle, $value["tipo"])
                    	->setCellValue('B' . $bucle, $value["numtar"])
                    	->setCellValue('C' . $bucle, $value["nomtar"])
                    	->setCellValue('D' . $bucle, $value["caja"])
                    	->setCellValue('E' . $bucle, $value["importe"])
                    	->setCellValue('F' . $bucle, $value["ticket"])
                    	->setCellValue('G' . $bucle, $value["fecha"])
                    	->setCellValue('H' . $bucle, $value["hora"])
                        ->setCellValue('I' . $bucle, $contador);

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
header('Content-Disposition: attachment;filename="TarjetasCreditos.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

