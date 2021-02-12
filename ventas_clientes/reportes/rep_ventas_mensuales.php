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

include_once('../../include/Classes/PHPExcel.php');

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
$cod_almacen 	= '001';//$_SESSION['noalmacen'];
$periodo 	= $_SESSION['periodo'];
$cod_linea 	= $_SESSION['cod_linea'];
$modo 	= $_SESSION['modo'];

$noarchivo 	= "Informe_Ventas_Mensuales".$periodo;
$titulo 	= "REPORTE VENTAS MENSUALES";

/* Verificar data */
// echo "<script>console.log('" . json_encode($data) . "')</script>";
// echo "<script>console.log('" . json_encode($cod_almacen) . "')</script>";
// echo "<script>console.log('" . json_encode($periodo) . "')</script>";
// echo "<script>console.log('" . json_encode($cod_linea) . "')</script>";
// echo "<script>console.log('" . json_encode($modo) . "')</script>";
// return;
/* Fin Verificar data */

/* ESTILO DE HEADER */

$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
$objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
$objPHPExcel->getActiveSheet()->mergeCells('B2:E2');
$objPHPExcel->getActiveSheet()->mergeCells('G2:H2');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);

/* FIN */

/* TITULO DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A1', $titulo)
		->setCellValue('A2', 'Almacen: ')
		->setCellValue('B2', $cod_almacen)
		->setCellValue('F2', 'Periodo: ')
		->setCellValue('G2', $periodo);

$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getFont()->setBold(true);

/* FIN */

/* TABLE HEADER DE EXCEL */
	
	if ($modo == 'todo'){
				$objPHPExcel->setActiveSheetIndex($hoja)
				->setCellValue('A4', 'COD. ITEM - DESCRIPCION')
				->setCellValue('B4', 'CAN01')
				->setCellValue('C4', 'VAL01')
				->setCellValue('D4', 'CAN02')
				->setCellValue('E4', 'VAL02')
				->setCellValue('F4', 'CAN03')
				->setCellValue('G4', 'VAL03')
				->setCellValue('H4', 'CAN04')
				->setCellValue('I4', 'VAL04')
				->setCellValue('J4', 'CAN05')
				->setCellValue('K4', 'VAL05')
				->setCellValue('L4', 'CAN06')
				->setCellValue('M4', 'VAL06')
				->setCellValue('N4', 'CAN07')
				->setCellValue('O4', 'VAL07')
				->setCellValue('P4', 'CAN08')
				->setCellValue('Q4', 'VAL08')
				->setCellValue('R4', 'CAN09')
				->setCellValue('S4', 'VAL09')
				->setCellValue('T4', 'CAN10')
				->setCellValue('U4', 'VAL10')
				->setCellValue('V4', 'CAN11')
				->setCellValue('W4', 'VAL11')
				->setCellValue('X4', 'CAN12')
				->setCellValue('Y4', 'VAL12');
			}elseif($modo == 'cantidades'){
				$objPHPExcel->setActiveSheetIndex($hoja)
				->setCellValue('A4', 'COD. ITEM - DESCRIPCION')
				->setCellValue('B4', 'CAN01')
				->setCellValue('C4', 'CAN02')
				->setCellValue('D4', 'CAN03')
				->setCellValue('E4', 'CAN04')
				->setCellValue('F4', 'CAN05')
				->setCellValue('G4', 'CAN06')
				->setCellValue('H4', 'CAN07')
				->setCellValue('I4', 'CAN08')
				->setCellValue('J4', 'CAN09')
				->setCellValue('K4', 'CAN10')
				->setCellValue('L4', 'CAN11')
				->setCellValue('M4', 'CAN12');
			}else{
				$objPHPExcel->setActiveSheetIndex($hoja)
				->setCellValue('A4', 'COD. ITEM - DESCRIPCION')
				->setCellValue('B4', 'VAL01')
				->setCellValue('C4', 'VAL02')
				->setCellValue('D4', 'VAL03')
				->setCellValue('E4', 'VAL04')
				->setCellValue('F4', 'VAL05')
				->setCellValue('G4', 'VAL06')
				->setCellValue('H4', 'VAL07')
				->setCellValue('I4', 'VAL08')
				->setCellValue('J4', 'VAL09')
				->setCellValue('K4', 'VAL10')
				->setCellValue('L4', 'VAL11')
				->setCellValue('M4', 'VAL12');
			}


$objPHPExcel->getActiveSheet()->getStyle('A4:Y4')->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->freezePane('A5');//LINEA HORIZONTAL

/* FIN */

$bucle = 5;

if (count($data) > 0) {

	$i	 		= 0;
	$nolinea 		= null;
	$noalmacen		= null;

	foreach ($data as $row) {

			if ($modo == 'todo'){
			$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, $row['2'])
	       		->setCellValue('B' . $bucle, $row['3'])
	       		->setCellValue('C' . $bucle, $row['4'])
	       		->setCellValue('D' . $bucle, $row['5'])
	       		->setCellValue('E' . $bucle, $row['6'])
	       		->setCellValue('F' . $bucle, $row['7'])
	       		->setCellValue('G' . $bucle, $row['8'])
	       		->setCellValue('H' . $bucle, $row['9'])
	       		->setCellValue('I' . $bucle, $row['10'])
	       		->setCellValue('J' . $bucle, $row['11'])
	       		->setCellValue('K' . $bucle, $row['12'])
	       		->setCellValue('L' . $bucle, $row['13'])
	       		->setCellValue('M' . $bucle, $row['14'])
	       		->setCellValue('N' . $bucle, $row['15'])
	       		->setCellValue('O' . $bucle, $row['16'])
	       		->setCellValue('P' . $bucle, $row['17'])
	       		->setCellValue('Q' . $bucle, $row['18'])
	       		->setCellValue('R' . $bucle, $row['19'])
	       		->setCellValue('S' . $bucle, $row['20'])
	       		->setCellValue('T' . $bucle, $row['21'])
	       		->setCellValue('U' . $bucle, $row['22'])
	       		->setCellValue('V' . $bucle, $row['23'])
	       		->setCellValue('W' . $bucle, $row['24'])
	       		->setCellValue('X' . $bucle, $row['25'])
	       		->setCellValue('Y' . $bucle, $row['26']);
			}elseif($modo == 'cantidades'){
				$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, $row['2'])
	       		->setCellValue('B' . $bucle, $row['3'])
	       		->setCellValue('C' . $bucle, $row['5'])
	       		->setCellValue('D' . $bucle, $row['7'])
	       		->setCellValue('E' . $bucle, $row['9'])
	       		->setCellValue('F' . $bucle, $row['11'])
	       		->setCellValue('G' . $bucle, $row['13'])
	       		->setCellValue('H' . $bucle, $row['15'])
	       		->setCellValue('I' . $bucle, $row['17'])
	       		->setCellValue('J' . $bucle, $row['19'])
	       		->setCellValue('K' . $bucle, $row['21'])
	       		->setCellValue('L' . $bucle, $row['23'])
	       		->setCellValue('M' . $bucle, $row['25']);
			}else{
				$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, $row['2'])
	       		->setCellValue('B' . $bucle, $row['4'])
	       		->setCellValue('C' . $bucle, $row['6'])
	       		->setCellValue('D' . $bucle, $row['8'])
	       		->setCellValue('E' . $bucle, $row['10'])
	       		->setCellValue('F' . $bucle, $row['12'])
	       		->setCellValue('G' . $bucle, $row['14'])
	       		->setCellValue('H' . $bucle, $row['16'])
	       		->setCellValue('I' . $bucle, $row['18'])
	       		->setCellValue('J' . $bucle, $row['20'])
	       		->setCellValue('K' . $bucle, $row['22'])
	       		->setCellValue('L' . $bucle, $row['24'])
	       		->setCellValue('M' . $bucle, $row['26']);
			}

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