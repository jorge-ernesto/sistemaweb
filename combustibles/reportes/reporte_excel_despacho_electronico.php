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
            ->setCellValue('A4', 'Fecha Emision')
            ->setCellValue('B4', "Lado ")
            ->setCellValue('C4', " Manguera ")
            ->setCellValue('D4', "Precio ")
            ->setCellValue('E4', 'Cantidad')
            ->setCellValue('F4', 'Importe')
            ->setCellValue('G4', 'Cnt.Volumen')
            ->setCellValue('H4', 'Cnt.Importe')
            ->setCellValue('I4', 'Cnt.Volumen Proyeccion')
            ->setCellValue('J4', 'Cnt.Importe Proyeccion')
            ->setCellValue('K4', 'Diferencia Volumen')
            ->setCellValue('L4', 'Diferencia Importe');


    $data = $_SESSION['data_excel'];
    if (count($data) > 0) {
        $volumen = 0;
        $importe = 0;
        $cont = 0;
        foreach ($data as $value) {

            $tragal = round($value['tragal'], 3);
            $tratot = round($value['tratot'], 3);
            $tot_volume = round($value['tot_volume'], 3);
            $tot_value = round($value['tot_value'], 3);

            $proyecion_volumen = round($volumen + $tragal, 2);
            $proyecion_importe = round($importe + $tratot, 2);
            $dif_vol = round($proyecion_volumen - $tot_volume, 2);
            $dif_imp = round($proyecion_importe - $tot_value, 2);


            if ($cont == 0) {
                $excel_pv = '0';
                $excel_im = '0';
                $excel_difvol = '0';
                $excel_difimp = '0';
            } else {
                $excel_pv = $proyecion_volumen;
                $excel_im = $proyecion_importe;
                $excel_difvol = $dif_vol;
                $excel_difimp = $dif_imp;
            }

            $objPHPExcel->setActiveSheetIndex($hoja)
                    ->setCellValue('A' . $bucle, $value["hora"])
                    ->setCellValue('B' . $bucle, $value["tralad"])
                    ->setCellValue('C' . $bucle, $value["tragra"])
                    ->setCellValue('D' . $bucle, $value["trapre"])
                    ->setCellValue('E' . $bucle, $value["tragal"])
                    ->setCellValue('F' . $bucle, $value["tratot"])
                    ->setCellValue('G' . $bucle, $tot_volume)
                    ->setCellValue('H' . $bucle, $tot_value)
                    ->setCellValue('I' . $bucle, $excel_pv)
                    ->setCellValue('J' . $bucle, $excel_im)
                    ->setCellValue('K' . $bucle, $excel_difvol)
                    ->setCellValue('L' . $bucle, $excel_difimp);




            $cont++;

            $volumen = round($value['tot_volume'], 3);
            $importe = round($value['tot_value'], 3);

            //**********************



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
