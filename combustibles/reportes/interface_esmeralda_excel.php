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

/*** Agregado 2020-01-28 ***/
// echo "<pre>";
// print_r($_SESSION['data_excel']);
// echo "</pre>";
// die();
/***/

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
    ->setCellValue('A4', 'SERIE')
    ->setCellValue('B4', 'NRO FACTURA')
    ->setCellValue('C4', 'FECHA EMISION')
    ->setCellValue('D4', 'HORA EMISION')
    ->setCellValue('E4', 'USUARIO')
    ->setCellValue('F4', 'CLIENTE')
    ->setCellValue('G4', 'DESCRIPCION')
    ->setCellValue('H4', 'MONEDA')
    ->setCellValue('I4', 'SEDE VENTA')
    ->setCellValue('J4', 'ALMACEN')
    ->setCellValue('K4', 'ESTADO')
    ->setCellValue('L4', 'ARTICULO')
    ->setCellValue('M4', 'CANTIDAD VTA')
    ->setCellValue('N4', 'P.U.VTA')
    ->setCellValue('O4', 'DESCUENTO')
    ->setCellValue('P4', 'IMPORTE')
    ->setCellValue('Q4', 'MEDIO PAGO')
	->setCellValue('R4', 'LOTE(PERIODO)')
	->setCellValue('S4', 'DOC. REFERENCIA')
	->setCellValue('T4', 'MOTIVO');

	$data	= $_SESSION['data_excel'];
	$fecha	= $_SESSION['data_fecha'];

	/*** Agregado 2020-02-10 ***/
	foreach($data as $key=>$value){
		for ($i=0;$i<=22;$i++) { 
			unset($data[$key][$i]);
		}			
		
		if($data[$key]['medio_pago_requerimiento'] == "000001"){
			$data[$key]['medio_pago_requerimiento'] = "VISA";
		}
		if($data[$key]['medio_pago_requerimiento'] == "000002"){
			$data[$key]['medio_pago_requerimiento'] = "AMERICAN EXPRESS";
		}
		if($data[$key]['medio_pago_requerimiento'] == "000003"){
			$data[$key]['medio_pago_requerimiento'] = "MASTERCARD";
		}
		if($data[$key]['medio_pago_requerimiento'] == "000004"){
			$data[$key]['medio_pago_requerimiento'] = "DINNERS";
		}
		if($data[$key]['medio_pago_requerimiento'] == "000005"){
			$data[$key]['medio_pago_requerimiento'] = "CMR";
		}
		if($data[$key]['medio_pago_requerimiento'] == "000006"){
			$data[$key]['medio_pago_requerimiento'] = "RIPLEY";
		}
		if($data[$key]['medio_pago_requerimiento'] == "000007"){
			$data[$key]['medio_pago_requerimiento'] = "DEPOSITO BANCARIO";
		}		
		if($data[$key]['medio_pago_requerimiento'] == "000008"){
			$data[$key]['medio_pago_requerimiento'] = "ESMERALDACARD";
		}		
		if($data[$key]['medio_pago_requerimiento'] == "000009"){
			$data[$key]['medio_pago_requerimiento'] = "METROPLAZOS";
		}	
		
		if(trim($data[$key]['ruc_requerimiento']) == ""){
			$data[$key]['ruc_requerimiento'] = "99999999999";
			$data[$key]['rs_requerimiento'] = "CLIENTES VARIOS";
		}

		$data[$key]['motivo_requerimiento'] = "";
		if(trim($data[$key]['documento_referencia_requerimiento']) != ""){
			$data[$key]['motivo_requerimiento'] = "01";
		}
	}

	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
	// die();
	/***/

	if (count($data) > 0) {
        foreach ($data as $value) {
			$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A' . $bucle, $value["serie_documento"])
			->setCellValue('C' . $bucle, $value["fecha_emision"])
			->setCellValue('D' . $bucle, $value["hora_emision"])
			->setCellValue('E' . $bucle, "Sistema")
			->setCellValue('F' . $bucle, $value["ruc_requerimiento"])
			->setCellValue('G' . $bucle, $value["rs_requerimiento"])
            ->setCellValue('H' . $bucle, "S")
            ->setCellValue('I' . $bucle, "98")
			->setCellValue('K' . $bucle, $value["estado_requerimiento"])
			->setCellValue('L' . $bucle, $value["articulo"])
			->setCellValue('M' . $bucle, round($value["cantidad"], 3))
			->setCellValue('N' . $bucle, $value["precio"])
			->setCellValue('O' . $bucle, "0")
			->setCellValue('P' . $bucle, $value["importe"])
			->setCellValue('Q' . $bucle, $value["medio_pago_requerimiento"])
			->setCellValue('R' . $bucle, $fecha)
			->setCellValue('S' . $bucle, $value["documento_referencia_requerimiento"])
			->setCellValue('T' . $bucle, $value["motivo_requerimiento"]);

			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, $bucle, str_pad($value["num_documento"], 8, "0", STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(9, $bucle, "004", PHPExcel_Cell_DataType::TYPE_STRING);

		    $bucle++;
		}
	}

	$objPHPExcel->getActiveSheet()->getStyle('G7:G' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$objPHPExcel->getActiveSheet()->getStyle('H7:H' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$objPHPExcel->getActiveSheet()->getStyle('N7:N' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$objPHPExcel->getActiveSheet()->getStyle('M7:M' . $bucle)->getNumberFormat()->setFormatCode('0.000');
	$objPHPExcel->getActiveSheet()->getStyle('Q7:Q' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$objPHPExcel->getActiveSheet()->getStyle('T7:T' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$objPHPExcel->getActiveSheet()->getStyle('P7:P' . $bucle)->getNumberFormat()->setFormatCode('0.00');
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