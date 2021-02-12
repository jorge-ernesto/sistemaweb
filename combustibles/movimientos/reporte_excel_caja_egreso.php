<?php

session_start();
include_once('../../include/Classes/PHPExcel.php');


//$empresa = KardexActModel::datosEmpresa();
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');

/** Include PHPExcel */
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

//$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
$objPHPExcel->getActiveSheet()->freezePane('A6');
$bucle = 7;
//  $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setWrapText(TRUE);

if ($_SESSION['data_excel'] != null) {
    $objPHPExcel->setActiveSheetIndex($hoja)
            ->setCellValue('A4', 'Fecha')
            ->setCellValue('B4', 'Nro. Recibo')
            ->setCellValue('C4', 'Caja')
            ->setCellValue('D4', 'Operacion')
            ->setCellValue('E4', 'Monto Documento')
            ->setCellValue('F4', 'Moneda')
            ->setCellValue('G4', 'Cliente')
            ->setCellValue('H4', 'Monto de Pago')
            ->setCellValue('I4', 'Tasa')
            ->setCellValue('J4', 'Referencia');


    $data = $_SESSION['data_excel'];
    if (count($data) > 0) {
        $volumen = 0;
        $importe = 0;
        $cont = 0;
        $sumatoria1=0;
        $sumatoria2=0;
        foreach ($data as $value) {

            $sumatoria1=$sumatoria1+$value["monto"];
            $sumatoria2=$sumatoria2+$value["importe_neto"];

            $objPHPExcel->setActiveSheetIndex($hoja)
                    ->setCellValue('A' . $bucle, $value["d_system"])
                    ->setCellValue('B' . $bucle, $value["num"])
                    ->setCellValue('C' . $bucle, $value["caja"])
                    ->setCellValue('D' . $bucle, $value["operacion"])
                    ->setCellValue('E' . $bucle, $value["monto"])
                    ->setCellValue('F' . $bucle, $value["moneda"])
                    ->setCellValue('G' . $bucle, $value["cliente"])
                    ->setCellValue('H' . $bucle, $value["importe_neto"])
                    ->setCellValue('I' . $bucle, $value["rate"])
                    ->setCellValue('J' . $bucle, $value["referencia"]);
            $bucle++;
        }

        $filafinal = $bucle++; 
        $objPHPExcel->setActiveSheetIndex($hoja)
                    ->setCellValue('D' . $filafinal, "Totales:")
                    ->setCellValue('E' . $filafinal, $sumatoria1)
                    ->setCellValue('H' . $filafinal, $sumatoria2);
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
header('Content-Disposition: attachment;filename="Archivo.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
