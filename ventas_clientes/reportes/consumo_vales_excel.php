<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();

$session_data_excel = $_SESSION['data_excel'];
$session_orden      = $_SESSION['orden'];
$session_arrRequest = $_SESSION['arrRequest'];
unset($_SESSION['data_excel']);
unset($_SESSION['orden']);
unset($_SESSION['arrRequest']);
error_log("Paso 1");
error_log( json_encode($_SESSION) );

include_once('../../include/Classes/PHPExcel.php');

error_reporting(E_ALL);

date_default_timezone_set('UTC');

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

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);
$hoja = 0;

$objPHPExcel->getActiveSheet()->freezePane('A2');
$bucle = 1;

if ($session_data_excel != null) {

	$objPHPExcel->getActiveSheet()->getRowDimension($bucle)->setRowHeight(20);
	$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle . ':R' . $bucle)->applyFromArray($cabecera);
	$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle . ':R' . $bucle)->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A1', 'Almacen')
	->setCellValue('B1', 'Nro.Liquidacion')
    ->setCellValue('C1', 'Nro.Factura')
    ->setCellValue('D1', 'Nro.Despacho')
	->setCellValue('E1', 'Fecha')
	->setCellValue('F1', 'Hora')
    ->setCellValue('G1', 'Nro. Manual')
    ->setCellValue('H1', 'Placa')
    ->setCellValue('I1', 'Producto')
    ->setCellValue('J1', 'Odometro')
    ->setCellValue('K1', 'Usuario')
    ->setCellValue('L1', 'DNI')
    ->setCellValue('M1', 'Cantidad')
    ->setCellValue('N1', 'Precio Contratado')
	->setCellValue('O1', 'Importe Contratado');
	
	error_log("Paso 2");
	error_log( json_encode($_SESSION) );
	if ( $session_arrRequest['sPrecioPizarra']=='true' ) {
		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('P1', 'Precio Pizarra')
		->setCellValue('Q1', 'Importe Pizarra')
		->setCellValue('R1', 'Diferencia Precio')
		->setCellValue('S1', 'Diferencia Importe');
	}
	error_log("Paso 3");
	error_log( json_encode($_SESSION) );

	$data 	= $session_data_excel;
	$bucle 	= 0;
	error_log("Paso 4");
	error_log( json_encode($_SESSION) );

	if (count($data) > 0) {
		$i 				= 0;
		$tickets 		= 0;
		$cliente 		= "";
		$placa 			= "";
		$importecli 	= 0;
		$cantidadcli 	= 0;
		$nomcliente 	= "";

		$fImportePizarra = 0.00;
		$fImporteDiferencia = 0.00;

		$sTipoCliente = '';

        for($i=0; $i < count($data); $i++){
			$sTipoCliente = 'EFECTIVO';
			if ( $data[$i]['nu_tipo_efectivo'] == '0' && $data[$i]['no_tipo_anticipo'] == 'N' ){
				$sTipoCliente = 'CREDITO';
			} else if ( $data[$i]['nu_tipo_efectivo'] == '0' && $data[$i]['no_tipo_anticipo'] == 'S' ){
				$sTipoCliente = 'ANTICIPO';
			}

			$nomcliente = htmlentities($data[$i]["nomcliente"]);
			$nomcliente = iconv("utf-8", "utf-8//IGNORE", $nomcliente);

			if($cliente != $data[$i]["codcliente"]){
				$bucle++;

				if($i!=0){
					$objRichText 	= new PHPExcel_RichText();
					$objBold1 		= $objRichText->createTextRun("Total Cantidad: ".$cantidadcli. "  - Total Importe: ".$importecli);
					$objBold1->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);
					
					$cantidadcli 	= 0;
					$importecli 	= 0;
				}
	
				$cliente 		= $data[$i]['codcliente'];
				$bucle 			= $bucle + 2;
				$objRichText 	= new PHPExcel_RichText();
				$objBold1 		= $objRichText->createTextRun("Cliente " . $sTipoCliente . ": " . $data[$i]["codcliente"] . " - " . $nomcliente);
				$objBold1->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);
			}

			error_log("Paso 5");
			error_log( json_encode($_SESSION) );
			if ( $session_arrRequest['sPrecioPizarra']=='true' ){
				$fImportePizarra=round($data[$i]['cantidad'] * $data[$i]['nu_precio_especial'],2);
				$fImporteDiferencia=round($data[$i]['importe'],2) - $fImportePizarra;

				$fTotImportePizarra+=$fImportePizarra;
				$fTotImporteDiferencia+=$fImporteDiferencia;
			}
			error_log("Paso 6");
			error_log( json_encode($_SESSION) );
			
			if($session_orden == "D"){
				$sDocumento = ( $data[$i]["documento"] != '' ? $data[$i]["documento"] : $data[$i]["documento2"]);

				$bucle = $bucle + 1;

				$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, $data[$i]["almacen"])
	       		->setCellValue('B' . $bucle, $data[$i]["liquidacion"])
	       		->setCellValue('C' . $bucle, $data[$i]["documento"])
	       		->setCellValue('D' . $bucle, $data[$i]["numero"])
				->setCellValue('E' . $bucle, $data[$i]["fecha"])
				->setCellValue('F' . $bucle, $data[$i]["hora"])
	       		->setCellValue('G' . $bucle, $data[$i]["vale"])
	       		->setCellValue('H' . $bucle, $data[$i]["placa"])
	       		->setCellValue('I' . $bucle, $data[$i]["producto"])
	       		->setCellValue('J' . $bucle, $data[$i]["odometro"]);

                $usuario2 = (empty($data[$i]["chofer"])) ? '  ' : (string) $data[$i]['chofer'];
				$usuario2 = htmlentities($usuario2);
				$usuario2 = iconv("utf-8", "utf-8//IGNORE", $usuario2);
				$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(10, $bucle, $usuario2, PHPExcel_Cell_DataType::TYPE_STRING);

                $nu_documento_chofer = (empty($data[$i]["nu_documento_chofer"])) ? '  ' : (string) $data[$i]['nu_documento_chofer'];
				$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(11, $bucle, $nu_documento_chofer, PHPExcel_Cell_DataType::TYPE_STRING);

				$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('M' . $bucle, $data[$i]["cantidad"])
	       		->setCellValue('N' . $bucle, $data[$i]["ss_precio_contratado"])
				->setCellValue('O' . $bucle, $data[$i]["importe"]);
				   
				error_log("Paso 7");
				error_log( json_encode($_SESSION) );
				if ( $session_arrRequest['sPrecioPizarra']=='true' ){
					$objPHPExcel->setActiveSheetIndex($hoja)
					->setCellValue('P' . $bucle, $data[$i]["nu_precio_especial"])
					->setCellValue('Q' . $bucle, $fImportePizarra)
					->setCellValue('R' . $bucle, $data[$i]["ss_precio_contratado"] - $data[$i]["nu_precio_especial"])
					->setCellValue('S' . $bucle, $fImporteDiferencia);
				}
				error_log("Paso 8");
				error_log( json_encode($_SESSION) );
			}

			$cantidadcli+=$data[$i]['cantidad'];
			$importecli+=$data[$i]['importe'];
		} // ./ For

		$bucle 			= $bucle + 1;
		$objRichText 	= new PHPExcel_RichText();
		
		$objBold1 		= $objRichText->createTextRun("Total Cantidad: ".$cantidadcli. "  - Total Importe: ".$importecli);
		$objBold1->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);		
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
header('Content-Disposition: attachment;filename="ConsumoVales.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;