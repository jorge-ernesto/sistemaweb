<?php

date_default_timezone_set('UTC');
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

$objPHPExcel->getActiveSheet()->freezePane('A2');
$bucle = 2;

$data			= $_SESSION['data_asientos'];
$cuentas		= $_SESSION['data_cuentas'];
$almacen		= $_SESSION['almacen'];
$modulos		= $_SESSION['modulos'];	
$decimales		= $_SESSION['decimales'];	
$asientos		= NULL;
$nombre_archivo = "mymventaAPP.xls";
$vacio 			= NULL;

if(!empty($data)){
	$objPHPExcel->setActiveSheetIndex($hoja)
        ->setCellValue('A1', 'Vou.Origen')
        ->setCellValue('B1', 'Vou.Numero')
        ->setCellValue('C1', 'Vou.Fecha')
        ->setCellValue('D1', 'Doc')
        ->setCellValue('E1', 'Numero')
        ->setCellValue('F1', 'Fec.Doc')
        ->setCellValue('G1', 'Fec.Venc.')
        ->setCellValue('H1', 'Codigo')
        ->setCellValue('I1', 'Valor Exp.')
        ->setCellValue('J1', 'B.Imponible')
        ->setCellValue('K1', 'Inafecto')
        ->setCellValue('L1', 'Exonerado')
        ->setCellValue('M1', 'I.S.C.')
        ->setCellValue('N1', 'IGV')
        ->setCellValue('O1', 'OTROS TRIB.')

		->setCellValue('P1', 'IMP. BOLSA')

        ->setCellValue('Q1', 'Moneda')
        ->setCellValue('R1', 'TC')
        ->setCellValue('S1', 'Glosa')
        ->setCellValue('T1', 'Cta Ingreso')
        ->setCellValue('U1', 'Cta IGV')
        ->setCellValue('V1', 'Cta O. Trib.')
        ->setCellValue('W1', 'Cta x Cobrar')
        ->setCellValue('X1', 'C.Costo')
        ->setCellValue('Y1', 'Presupuesto')
        ->setCellValue('Z1', 'R.Doc')
        ->setCellValue('AA1', 'R.numero')
        ->setCellValue('AB1', 'R.Fecha')
        ->setCellValue('AC1', 'RUC')
        ->setCellValue('AD1', 'R.Social')
        ->setCellValue('AE1', 'Tipo')
        ->setCellValue('AF1', 'Tip.Doc.Iden')
        ->setCellValue('AG1', 'Medio de Pago')
        ->setCellValue('AH1', 'Apellido 1')
        ->setCellValue('AI1', 'Apellido 2')
        ->setCellValue('AJ1', 'Nombre')
        ;

    $nu_tipo_documento = NULL;

    foreach ($data as $value) {
		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A' . $bucle, $value["nu_tipo_operacion"])
		->setCellValue('B' . $bucle, $value["nu_correlativo"])
		->setCellValue('C' . $bucle, $value["fe_emision"]);

		$nu_tipo_documento = (string) $value['nu_tipo_documento'];
        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $bucle, $nu_tipo_documento, PHPExcel_Cell_DataType::TYPE_STRING);

		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('E' . $bucle, $value["nu_documento"])
		->setCellValue('F' . $bucle, $value["fe_documento"])
		->setCellValue('G' . $bucle, $value["fe_vencimiento"]);

		$nu_codigo = (string) $value['nu_codigo'];
        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(7, $bucle, $nu_codigo, PHPExcel_Cell_DataType::TYPE_STRING);

		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('I' . $bucle, $vacio)//Valor Exp.
		->setCellValue('J' . $bucle, $value["nu_base_imponible"])
		->setCellValue('K' . $bucle, $vacio)//Inafecto
		->setCellValue('L' . $bucle, $value["nu_exonerado"])
		->setCellValue('M' . $bucle, $vacio)//I.S.C.
		->setCellValue('N' . $bucle, $value["nu_igv"])
		->setCellValue('O' . $bucle, $vacio)//OTROS TRIB.
		->setCellValue('P' . $bucle, $value["balance"])//ICBPER
		->setCellValue('Q' . $bucle, $value["no_moneda"])
		->setCellValue('R' . $bucle, $value["nu_tipo_cambio"])
		->setCellValue('S' . $bucle, $value["no_glosa"])
		->setCellValue('T' . $bucle, $value["nu_cuenta_bi"])
		->setCellValue('U' . $bucle, $value["nu_cuenta_igv"])
		->setCellValue('V' . $bucle, $vacio)//Cta O. Trib.
		->setCellValue('W' . $bucle, $value["nu_cuenta_caja_cobrar"])
		->setCellValue('X' . $bucle, $vacio)//C.Costo
		->setCellValue('Y' . $bucle, $vacio);//Presupuesto columna = 23

		$nu_tipo_documento_original = (string) (empty($value['nu_tipo_documento_original']) ? '' : $value['nu_tipo_documento_original']);//R.Doc
        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(24, $bucle, $nu_tipo_documento_original, PHPExcel_Cell_DataType::TYPE_STRING);
	
		$nu_serie_numero_documento_original = (string) (empty($value['nu_serie_numero_documento_original']) ? '' : $value['nu_serie_numero_documento_original']);//R.Numero
        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(25, $bucle, $nu_serie_numero_documento_original, PHPExcel_Cell_DataType::TYPE_STRING);

		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('AB' . $bucle, (empty($value["fe_emision_original"]) ? '' : $value["fe_emision_original"]));//R.Fecha

        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(28, $bucle, $nu_codigo, PHPExcel_Cell_DataType::TYPE_STRING);

		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('AD' . $bucle, $value["no_razon_social"])
		->setCellValue('AE' . $bucle, $value["nu_tiposiscont"])
		->setCellValue('AF' . $bucle, $value["nu_tipo_documento_identidad"])
		->setCellValue('AG' . $bucle, $vacio)
		->setCellValue('AH' . $bucle, $vacio)
		->setCellValue('AI' . $bucle, $vacio)
		->setCellValue('AJ' . $bucle, $vacio);

	    $bucle++;
	}
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="' . $nombre_archivo . '"');
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