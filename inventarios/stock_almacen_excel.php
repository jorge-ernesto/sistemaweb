<?php

include("../funcjch.php");
require("../clases/funciones.php");
include_once('/sistemaweb/include/Classes/PHPExcel.php');
include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

$funcion	= new class_funciones;
$coneccion	= $funcion->conectar("","","","","");

session_start();

$sql= $_SESSION['A'];


$xsql		= pg_exec($coneccion,$sql);
$ilimit		= pg_numrows($xsql);

$filas=0;

$column_precio_venta = null;

if($_SESSION['U']=="U"){
	$column_precio_venta = 15;
	$var = 14;
}else{
	$column_precio_venta = 11;
	$var = 10;
}

while($filas < $ilimit) {

	$data[$filas]['codproducto']	= pg_result($xsql,$filas,0);
	$data[$filas]['producto']	= pg_result($xsql,$filas,1);
	$data[$filas]['unidad']		= pg_result($xsql,$filas,2);
	$data[$filas]['stock']		= pg_result($xsql,$filas,3);
	$data[$filas]['costo']		= pg_result($xsql,$filas,4);
	$data[$filas]['nu_precio_venta']		= pg_result($xsql,$filas, $column_precio_venta);
	$data[$filas]['subtotal']	= pg_result($xsql,$filas,5);
	$data[$filas]['margen']		= pg_result($xsql,$filas,10);
	$data[$filas]['impmargen']	= pg_result($xsql,$filas,11);
	$data[$filas]['igv']		= pg_result($xsql,$filas,12);
	$data[$filas]['total']		= pg_result($xsql,$filas,13);
	$data[$filas]['codlinea']	= pg_result($xsql,$filas,6);
	$data[$filas]['linea']		= pg_result($xsql,$filas,$var);
	$data[$filas]['almacen']	= pg_result($xsql,$filas,8);

	$filas++;

}


error_reporting(E_ALL);

//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

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

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);
$hoja = 0;

//TITULO
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A' . 2 . ':O' . 2)->getFont()->setBold(true);
$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A2', ' STOCKS VALORIZADOS POR ALMACEN - LINEAS');


$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A' . 4 . ':O' . 4)->getFont()->setBold(true);
$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A4', 'ALMACEN: ');
$objPHPExcel->setActiveSheetIndex($hoja);
$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, 4, $data[0]['almacen'], PHPExcel_Cell_DataType::TYPE_STRING);
                       	

$objPHPExcel->getActiveSheet()->freezePane('A7');
$bucle = 7;

	$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
	$objPHPExcel->getActiveSheet()->getStyle('A' . 6 . ':O' . 6)->getFont()->setBold(true);

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A6', 'Cod. Linea')
	->setCellValue('B6', 'Linea')
        ->setCellValue('C6', 'Cod. Producto')
        ->setCellValue('D6', 'Producto')
        ->setCellValue('E6', 'Cod. Unidad')
        ->setCellValue('F6', 'Stock Total')
        ->setCellValue('G6', 'Costo')
        ->setCellValue('H6', 'P. Venta')
        ->setCellValue('I6', 'Valor Total');

	if($_SESSION['U']=="U"){
		$objPHPExcel->setActiveSheetIndex($hoja)
        	->setCellValue('J6', 'Margen')
        	->setCellValue('K6', 'Val. Margen')
        	->setCellValue('L6', 'IGV')
        	->setCellValue('M6', 'Total');
	}

	if (count($data) > 0) {

        	for($i=0; $i < count($data); $i++){
//				$objBold1 = $objRichText->createTextRun("Cliente: ".$data[$i]["codcliente"]. " - ".utf8_encode(strtr($data[$i]["nomcliente"],utf8_decode('´'), "'")));
			$objPHPExcel->setActiveSheetIndex($hoja);
                       	$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(0, $bucle, $data[$i]['codlinea'], PHPExcel_Cell_DataType::TYPE_STRING);
                       	$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, $bucle, $data[$i]['linea'], PHPExcel_Cell_DataType::TYPE_STRING);
                       	$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(2, $bucle, $data[$i]['codproducto'], PHPExcel_Cell_DataType::TYPE_STRING);
                       	$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $bucle, utf8_encode(strtr($data[$i]['producto'],utf8_decode('ñ'), "n")), PHPExcel_Cell_DataType::TYPE_STRING);
                       	$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(4, $bucle, $data[$i]['unidad'], PHPExcel_Cell_DataType::TYPE_STRING);

                        $objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('F' . $bucle, $data[$i]["stock"])
			->setCellValue('G' . $bucle, $data[$i]["costo"])
			->setCellValue('H' . $bucle, $data[$i]["nu_precio_venta"])
			->setCellValue('I' . $bucle, $data[$i]["subtotal"]);

			if($_SESSION['U']=="U"){

		                $objPHPExcel->setActiveSheetIndex($hoja)
				->setCellValue('J' . $bucle, $data[$i]["margen"])
				->setCellValue('K' . $bucle, $data[$i]["impmargen"])
				->setCellValue('L' . $bucle, $data[$i]["igv"])
				->setCellValue('M' . $bucle, $data[$i]["total"]);

			}

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


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="StockAlmacen.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

