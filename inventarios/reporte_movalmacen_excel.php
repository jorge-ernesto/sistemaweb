<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$old = ini_set('default_socket_timeout', 5);

/*ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

error_reporting(E_ALL);

ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);*/

date_default_timezone_set('America/Bogota');

if (PHP_SAPI == 'cli') {
	die('This example should only be run from a Web Browser');
}

if (!isset($_SESSION['data_1010'])) {
	$html = '<div align="center">';
	$html .= '<h2>Error al generar reporte</h2>';
	$html .= '<a onclick="history.back()"> << Regresar </a>';
	$html .= '</div>';
	echo $html;
	exit;
}

include_once('../include/Classes/PHPExcel.php');

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

$data 		= $_SESSION['data_1010'];
$noalmacen 	= $_SESSION['almacen'];
$desde 	= $_SESSION['diasd'];
$hasta 	= $_SESSION['diasa'];
$cod_art 	=  $_SESSION['artic'];

$noarchivo 	= "Informe_Movimientos".$desde."_".$hasta;
$titulo 	= "REPORTE MOVIMIENTOS INVENTARIOS";

/* ESTILO DE HEADER */

$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
$objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
$objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
$objPHPExcel->getActiveSheet()->mergeCells('D2:F2');
$objPHPExcel->getActiveSheet()->mergeCells('G2:H2');
$objPHPExcel->getActiveSheet()->mergeCells('J2:K2');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(35);

/* FIN */

/* TITULO DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A1', $titulo)
		->setCellValue('A2', 'Almacen: '.$noalmacen)
		->setCellValue('D2', 'Desde: '.$desde)
		->setCellValue('G2', 'Hasta: '.$hasta)
		->setCellValue('J2', 'Producto:'.$cod_art);

$objPHPExcel->getActiveSheet()->getStyle('A2:M2')->getFont()->setBold(true);

/* FIN */

/* TABLE HEADER DE EXCEL */


	$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A4', 'NRO. FORMULARIO')
			->setCellValue('B4', 'FECHA')
			->setCellValue('C4', 'NRO. DOCUMENTO')
			->setCellValue('D4', 'ORIGEN')
			->setCellValue('E4', 'DESTINO')
			->setCellValue('F4', 'ALMACEN')
			->setCellValue('G4', 'ARTICULO')
			->setCellValue('H4', 'CANTIDAD')
			->setCellValue('I4', 'COSTO U.')
			->setCellValue('J4', 'NRO. OC')
			->setCellValue('K4', 'PROVEEDOR');


$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('E4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('F4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('G4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('H4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('I4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('J4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('K4')->getFont()->setBold(true);





$objPHPExcel->getActiveSheet()->freezePane('A5');//LINEA HORIZONTAL

/* FIN */

$bucle = 5;

if (count($data) > 0) {

	$i	 		= 0;
	$nolinea 		= null;
	$noalmacen		= null;

	foreach ($data as $row) {

			$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, $row['0'])
	       		->setCellValue('B' . $bucle, $row['1'])
	       		->setCellValue('C' . $bucle, $row['2']."-".$row['3'])
	       		->setCellValue('D' . $bucle, $row['4'])
	       		->setCellValue('E' . $bucle, $row['5'])
	       		->setCellValue('F' . $bucle, $row['6'])
	       		->setCellValue('G' . $bucle, $row['7'])
	       		->setCellValue('H' . $bucle, $row['8'])
	       		->setCellValue('I' . $bucle, $row['9'])
	       		->setCellValue('J' . $bucle, $row['10']."-".$row['11'])
	       		->setCellValue('K' . $bucle, $row['12']);

		$bucle++;

	}

}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$noarchivo.'.xls"');
header('Cache-Control: max-age=0');
/*header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0*/

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

unset($_SESSION['data_1010']);
exit;

?>
			