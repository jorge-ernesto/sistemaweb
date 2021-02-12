<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();
include_once('../include/Classes/PHPExcel.php');

$resultado_postrans = $_SESSION['info'];

reporteExcelPersonalizado($resultado_postrans);

function reporteExcelPersonalizado($resultado_postrans) {

    	$array_index_global = array();
    	$index_global = 1;
    	$conta_tm = 1;
    	error_reporting(E_ALL);
    	ini_set('display_errors', TRUE);
    	ini_set('display_startup_errors', TRUE);
    	date_default_timezone_set('Europe/London');

    	if (PHP_SAPI == 'cli')
        	die('This example should only be run from a Web Browser');

    	$objPHPExcel = new PHPExcel();

    	$objPHPExcel->getProperties()->setCreator("opensoft")
            ->setLastModifiedBy("OpenSysperu")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

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

    //$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
    $objPHPExcel->getActiveSheet()->freezePane('A6');
    $bucle = 7;
    //  $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setWrapText(TRUE);


    	$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A4', 'Numero de Registro')
		->setCellValue('B4', "Fecha Emision ")
		->setCellValue('C4', "Fecha Vencimiento")
		->setCellValue('D4', "Tipo ")
		->setCellValue('E4', 'Serie')
		->setCellValue('F4', 'Numero')
		->setCellValue('G4', 'Tipo Documento')
		->setCellValue('H4', 'Numero Documento  ')
		->setCellValue('I4', 'Razon social')
		->setCellValue('J4', 'Base imponible')
		->setCellValue('K4', 'IGV')
		->setCellValue('L4', 'Exonerada')
		->setCellValue('M4', 'Inafecto')
		->setCellValue('N4', 'Importe Total')
		->setCellValue('O4', 'Tipo Cambio');




    	header('Content-Type: application/vnd.ms-excel');
    	header('Content-Disposition: attachment;filename="VENTA_DETALLADA.xls"');
    	header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
    	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    	header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    	header('Pragma: public'); // HTTP/1.0

    	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    	$objWriter->save('php://output');

   	exit;

}

