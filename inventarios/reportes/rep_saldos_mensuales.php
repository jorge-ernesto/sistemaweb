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
$nuyear 	= $_SESSION['nuyear'];
$numonth 	= $_SESSION['numonth'];
$cod_art 	= $_SESSION['cod_art'];
$utilidad 	= $_SESSION['desc_art'];

$noarchivo 	= "Informe_Saldos_Mensuales".$nuyear."_".$numonth;
$titulo 	= "REPORTE SALDOS MENSUALES";

/* ESTILO DE HEADER */

$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
$objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
$objPHPExcel->getActiveSheet()->mergeCells('C2:D2');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

/* FIN */

/* TITULO DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A1', $titulo)
		->setCellValue('A2', 'Almacen: ')
		->setCellValue('B2', $noalmacen)
		->setCellValue('C2', 'Periodo de Cierre ->')
		->setCellValue('E2', 'Año: '.$nuyear)
		->setCellValue('F2', 'Mes: '.$numonth);

$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);

/* FIN */

/* TABLE HEADER DE EXCEL */


	$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A4', 'ALMACEN')
			->setCellValue('B4', 'COD. ITEM')
			->setCellValue('C4', 'DESCRIPCION')
			->setCellValue('D4', 'STOCK')
			->setCellValue('E4', 'S. FISICO')
			->setCellValue('F4', 'COSTO P.')
			->setCellValue('G4', 'STK. INI.AÑO')
			->setCellValue('H4', 'C. INI.AÑO');
			//->setCellValue('I4', 'TIPO PLU');


$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('E4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('F4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('G4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('H4')->getFont()->setBold(true);
//$objPHPExcel->getActiveSheet()->getStyle('I4')->getFont()->setBold(true);


$objPHPExcel->getActiveSheet()->freezePane('A5');//LINEA HORIZONTAL

/* FIN */

$bucle = 5;

if (count($data) > 0) {

	$i	 		= 0;
	$nolinea 		= null;
	$noalmacen		= null;

	foreach ($data as $row) {

			$objPHPExcel->setActiveSheetIndex($hoja)
	       		->setCellValue('A' . $bucle, $row['7'])
	       		->setCellValue('B' . $bucle, $row['0'])
	       		->setCellValue('C' . $bucle, $row['1'])
	       		->setCellValue('D' . $bucle, $row['2'])
	       		->setCellValue('E' . $bucle, $row['3'])
	       		->setCellValue('F' . $bucle, $row['4'])
	       		->setCellValue('G' . $bucle, $row['5'])
	       		->setCellValue('H' . $bucle, $row['6']);

	       		//->setCellValue('H' . $bucle, $row['7']);

	       		//->setCellValue('I' . $bucle, $row['8']);
	

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