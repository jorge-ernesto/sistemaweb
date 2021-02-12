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
$bucle = 8;

if ($_SESSION['data_excel'] != null) {
	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A4', 'Fecha')
	->setCellValue('B4', 'Estacion')
        ->setCellValue('C4', 'Nro. Ticket')
        ->setCellValue('D4', 'Odometro')
        ->setCellValue('E4', 'Codigo Producto')
        ->setCellValue('F4', 'Nombre Producto')
        ->setCellValue('G4', 'Precio')
        ->setCellValue('H4', 'Cantidad')
        ->setCellValue('I4', 'Importe');

	$data = $_SESSION['data_excel'];

	if (count($data) > 0) {

		$i = 0;
		$tickets = 0;
		$cliente = "";
		$placa = "";

        	for($i=0; $i < count($data); $i++){

			$tickets = count($data);

			if($placa != $data[$i]['placa']){
$ya=$bucle+1;
            			$objPHPExcel->setActiveSheetIndex($hoja)
                    		->setCellValue('A' . $ya, "ssss");

				$placa = $data[$i]['placa'];
			}else{
$yo=$bucle+2;
            			$objPHPExcel->setActiveSheetIndex($hoja)
                    		->setCellValue('A' . $yo, $data[$i]["fecha"]);

			}

				/*$d = count($data[$i]['placa']);
				/*if($i != 0){
					//$v = $v + $i;
					$objPHPExcel->setActiveSheetIndex($hoja)
                    			->setCellValue('B' . $bucle, "Total por Placa: ".$i);
					$cantidadplaca = 0;
					$importeplaca = 0;

				}
				
				$celda = $bucle + 1;
                    		->setCellValue('A' . $celda, "Placa: ".$value["placa"]." Cliente: ".$value["codcliente"]. " - ".$value["descliente"]);
				$ya=8+$i;
				
				$objPHPExcel->setActiveSheetIndex($hoja)
                    		->setCellValue('B' . $bucle, "Total por Placa: ".$i);

				$placa = $data[$i]['placa'];

			}

            		$objPHPExcel->setActiveSheetIndex($hoja)
                    	->setCellValue('A' . $bucle, $data[$i]["fecha"]);
                    	/*->setCellValue('B' . $bucle, $value["desalmacen"])
                    	->setCellValue('C' . $bucle, $value["ticket"])
                    	->setCellValue('D' . $bucle, $value["odometro"])
                    	->setCellValue('E' . $bucle, $value["codproducto"])
                    	->setCellValue('F' . $bucle, $value["nomproducto"])
                    	->setCellValue('G' . $bucle, $value["precio"])
                    	->setCellValue('H' . $bucle, $value["cantidad"])
                    	->setCellValue('I' . $bucle, $value["importe"]);*/
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
header('Content-Disposition: attachment;filename="ConsumoPlaca.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

