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
$noalmacen 	= $_SESSION['noalmacen'];
//$nuyear 	= $_SESSION['nuyear'];
//$numonth 	= $_SESSION['numonth'];
$fecha_inicio 	= $_SESSION['fecha_inicio'];
$fecha_final 	= $_SESSION['fecha_final'];

$noarchivo 	= "Informe_Mov_Por_Dia_".$fecha_inicio;
$titulo 	= "MOVIMIENTOS ACUMULADOS POR RANGO DE FECHA";

/* ESTILO DE HEADER */

$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
$objPHPExcel->getActiveSheet()->mergeCells('A1:I1');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(17);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(16);

/* FIN */

/* TITULO DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A1', $titulo)
		->setCellValue('A2', 'Almacen: ')
		->setCellValue('B2', $noalmacen)
		->setCellValue('C2', 'Fecha Inicio: ')
		->setCellValue('D2', $fecha_inicio)
		->setCellValue('E2', 'Fecha Final: ')
		->setCellValue('F2', $fecha_final);

$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);

/* FIN */

/* TABLE HEADER DE EXCEL */


$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A4', 'CODIGO')
		->setCellValue('B4', 'DESCRIPCION')
		->setCellValue('C4', 'STK INICIAL')
		->setCellValue('D4', 'ENTRADAS')
		->setCellValue('E4', 'SALIDAS')
		->setCellValue('F4', 'AJUSTES')
		->setCellValue('G4', 'STK FINAL');


$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('E4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('F4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('G4')->getFont()->setBold(true);


$objPHPExcel->getActiveSheet()->freezePane('A5');//LINEA HORIZONTAL

/* FIN */

$bucle = 5;

if (count($data) > 0) {
	$i	 		= 0;
	$nolinea 		= null;
	$noalmacen		= null;

	foreach ($data as $row) {

		$bucle++;

			$A1 = $row['stock'] + $row['movimiento'];
			$A2 = $row['entrada'];
			$A3 = $row['salida'];
			$A4 = $row['ajuste'];
			$A5 = $row['stock'] + $row['movimiento'] + $row['entrada'] - $row['salida'] + $row['ajuste'];

			if ($A1 == '') $A1 = 0.00;
			if ($A2 == '') $A2 = 0.00;
			if ($A3 == '') $A3 = 0.00;
			if ($A4 == '') $A4 = 0.00;

			$objPHPExcel->setActiveSheetIndex($hoja)
				->setCellValue('A' . $bucle, $row['art_codigo']." ")
				->setCellValue('B' . $bucle, $row['art_descripcion'])
				->setCellValue('C' . $bucle, $A1)
				->setCellValue('D' . $bucle, $A2)
				->setCellValue('E' . $bucle, $A3)
				->setCellValue('F' . $bucle, $A4)
				->setCellValue('G' . $bucle, $A5);
		


		$i++;

	}

	$bucle++;






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