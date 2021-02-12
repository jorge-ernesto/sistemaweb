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

$data 			= $_SESSION['data'];
$razsocial 		= $_SESSION['razsocial'];
$txtnofechaini 	= $_SESSION['txtnofechaini'];
$txtnofechafin 	= $_SESSION['txtnofechafin'];
$notipo 		= $_SESSION['rdnotipo'];
$noarchivo 		= "Informe_Transacciones_Ventas";
$titulo 		= "Transacciones Diarias de Venta Sunat";

//TAMAÑO DE COLUMNAS DE CELDA

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);

/* FIN */

/* TITULO DE EXCEL */

$rownumber = 3;

/* ESTILO RAZON SOCIAL */

$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(15);
$objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

/* ESTILO DEL TITULO */

$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
$objPHPExcel->getActiveSheet()->mergeCells('A2:K2');


$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A1', $razsocial)
		->setCellValue('A2', $titulo)
		->setCellValue('D' . $rownumber, 'DEL: ')
		->setCellValue('E' . $rownumber, $txtnofechaini)
		->setCellValue('F' . $rownumber, 'AL: ')
		->setCellValue('G' . $rownumber, $txtnofechafin);

$objPHPExcel->getActiveSheet()->getStyle('D3:G3')->getFont()->setBold(true);

/* FIN */

/* TABLE HEADER DE EXCEL */

//	ESTILOS DE BORDES

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

$rownumber = 5;

//BORDES
$objPHPExcel->getActiveSheet()->getStyle('B' . $rownumber)->applyFromArray($BStyle);
$objPHPExcel->getActiveSheet()->getStyle('D' . $rownumber)->applyFromArray($BStyle);
$objPHPExcel->getActiveSheet()->getStyle('F' . $rownumber)->applyFromArray($BStyle);
$objPHPExcel->getActiveSheet()->getStyle('H' . $rownumber)->applyFromArray($BStyle);
$objPHPExcel->getActiveSheet()->getStyle('J' . $rownumber)->applyFromArray($BStyle);

//CABECERA
$objPHPExcel->getActiveSheet()->getStyle('A5:A6')->applyFromArray($top);

//DERECHA
$objPHPExcel->getActiveSheet()->getStyle('A5:A6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('B5')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('D5:D6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('F5:F6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('G6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('H5:H6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('I6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('J5:J6')->applyFromArray($right);
$objPHPExcel->getActiveSheet()->getStyle('K5:K6')->applyFromArray($right);


$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A' . $rownumber, 'FECHA')
		->setCellValue('B' . $rownumber, 'BOLETA')
		->setCellValue('D' . $rownumber, 'FACTURA')
		->setCellValue('F' . $rownumber, 'NOTA CREDITO')
		->setCellValue('H' . $rownumber, 'NOTA DEBITO')
		->setCellValue('J' . $rownumber, 'TOTALES');

$objPHPExcel->getActiveSheet()->mergeCells('B' . $rownumber . ':C' . $rownumber);
$objPHPExcel->getActiveSheet()->mergeCells('D' . $rownumber . ':E' . $rownumber);
$objPHPExcel->getActiveSheet()->mergeCells('F' . $rownumber . ':G' . $rownumber);
$objPHPExcel->getActiveSheet()->mergeCells('H' . $rownumber . ':I' . $rownumber);
$objPHPExcel->getActiveSheet()->mergeCells('J' . $rownumber . ':K' . $rownumber);

$objPHPExcel->getActiveSheet()->getStyle('A' . $rownumber . ':K' . $rownumber)->getFont()->setBold(true);

$rownumber = 6;

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A' . $rownumber, 'EMISION')
		->setCellValue('B' . $rownumber, 'NUMERO')
		->setCellValue('C' . $rownumber, 'IMPORTE')
		->setCellValue('D' . $rownumber, 'NUMERO')
		->setCellValue('E' . $rownumber, 'IMPORTE')
		->setCellValue('F' . $rownumber, 'NUMERO')
		->setCellValue('G' . $rownumber, 'IMPORTE')
		->setCellValue('H' . $rownumber, 'NUMERO')
		->setCellValue('I' . $rownumber, 'IMPORTE')
		->setCellValue('J' . $rownumber, 'NUMERO')
		->setCellValue('K' . $rownumber, 'IMPORTE');

$objPHPExcel->getActiveSheet()->getStyle('A' . $rownumber . ':K' . $rownumber)->getFont()->setBold(true);

/* FIN */

$bucle = 7;

$objPHPExcel->getActiveSheet()->freezePane('A7');//LINEA HORIZONTAL

if (count($data) > 0) {

	$result		= null;
	$registrob	= 0;
	$importeb	= 0.00;
	$registrof	= 0;
	$importef	= 0.00;
	$registronc	= 0;
	$importenc	= 0.00;
	$registrond	= 0;
	$importend	= 0.00;
	$registro	= 0;//TOTAL REGITROS X DIA
	$importe	= 0.00;//TOTAL IMPORTE X DIA

	//T = Tickets
	//DM = Documento Manuales

	//VARIBLES PARA SUMA TOTAL
	$sumregistrob 		= 0;
	$sumimporteb 		= 0.00;
	$sumregistrof 		= 0;
	$sumimportef		= 0.00;
	$sumnuregistrodmnc 	= 0;
	$sumnuimportedmnc 	= 0.00;
	$sumnuregistrodmnd 	= 0;
	$sumnuimportedmnd 	= 0.00;
	//TOTAL
	$sumregistro 		= 0;
	$sumimporte 		= 0.00;

	foreach ($data as $row) {

		$registrob	= ($row['nuregistrotb'] + $row['nuregistrodmb']);
		$importeb 	= ($row['nuimportetb'] + $row['nuimportedmb']);
		$registrof	= ($row['nuregistrotf'] + $row['nuregistrodmf']);
		$importef	= ($row['nuimportetf'] + $row['nuimportedmf']);
		$registronc = $row['nuregistrodmnc'];
		$importenc	= $row['nuimportedmnc'];
		$registrond = $row['nuregistrodmnd'];
		$importend 	= $row['nuimportedmnd'];
		//TOTAL
		$registro 	= ($registrob + $registrof + $registronc + $registrond);
		$importe 	= ($importeb + $importef + $importenc + $importend);

		if($notipo == "D"){

			$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, $row['fapertura'])
	       		->setCellValue('B' . $bucle, number_format($registrob, 0, '.', ','))
	       		->setCellValue('C' . $bucle, number_format($importeb, 2, '.', ','))
	       		->setCellValue('D' . $bucle, number_format($registrof, 0, '.', ','))
	       		->setCellValue('E' . $bucle, number_format($importef, 2, '.', ','))
	       		->setCellValue('F' . $bucle, number_format($registronc, 0, '.', ','))
	       		->setCellValue('G' . $bucle, number_format($importenc, 2, '.', ','))
	       		->setCellValue('H' . $bucle, number_format($registrond, 0, '.', ','))
	       		->setCellValue('I' . $bucle, number_format($importend, 2, '.', ','))
	       		->setCellValue('J' . $bucle, number_format($registro, 0, '.', ','))
	       		->setCellValue('K' . $bucle, number_format($importe, 2, '.', ','));

			$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
			$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle . ':K' . $bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
			
			$bucle++;
			
		}


		//SUMA DE TOTALES
		$sumregistrob 		+= $registrob;
		$sumimporteb 		+= $importeb;
		$sumregistrof 		+= $registrof;
		$sumimportef		+= $importef;
		$sumnuregistrodmnc 	+= $registronc;
		$sumnuimportedmnc 	+= $importenc;
		$sumnuregistrodmnd 	+= $registrond;
		$sumnuimportedmnd 	+= $importend;
		//TOTAL
		$sumregistro 		+= $registro;
		$sumimporte 		+= $importe;

	}

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A' . $bucle, 'TOTALES: ')
	->setCellValue('B' . $bucle, number_format($sumregistrob, 0, '.', ','))
	->setCellValue('C' . $bucle, number_format($sumimporteb, 2, '.', ','))
	->setCellValue('D' . $bucle, number_format($sumregistrof, 0, '.', ','))
	->setCellValue('E' . $bucle, number_format($sumimportef, 2, '.', ','))
	->setCellValue('F' . $bucle, number_format($sumnuregistrodmnc, 0, '.', ','))
	->setCellValue('G' . $bucle, number_format($sumnuimportedmnc, 2, '.', ','))
	->setCellValue('H' . $bucle, number_format($sumnuregistrodmnd, 0, '.', ','))
	->setCellValue('I' . $bucle, number_format($sumnuimportedmnd, 2, '.', ','))
	->setCellValue('J' . $bucle, number_format($sumregistro, 0, '.', ','))
	->setCellValue('K' . $bucle, number_format($sumimporte, 2, '.', ','));


	//BORDES TODA LA CELDA
	$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('I' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('J' . $bucle)->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('K' . $bucle)->applyFromArray($BStyle);

	$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle . ':K' . $bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle . ':K' . $bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));

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


