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
//$fecha_inicio 	= $_SESSION['fecha_inicio'];
$p_stock 	= $_SESSION['p_stock'];
$c_stock 	= $_SESSION['c_stock'];
$n_stock 	= $_SESSION['n_stock'];
$utilidad 	= $_SESSION['utilidad'];

$noarchivo 	= "Informe_Items_Por_Almacen_".$nuyear.$numonth;
$titulo 	= "REPORTE ITEMS POR ALMACEN";

/* ESTILO DE HEADER */

$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
$objPHPExcel->getActiveSheet()->mergeCells('A1:I1');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(17);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);

/* FIN */

/* TITULO DE EXCEL */

$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A1', $titulo)
	->setCellValue('A2', 'Almacen: ')
	->setCellValue('B2', $noalmacen)
	->setCellValue('C2', 'Año: ')
	->setCellValue('D2', $nuyear)
	->setCellValue('E2', 'Mes: ')
	->setCellValue('F2', $numonth);

$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);

/* FIN */

/* TABLE HEADER DE EXCEL */

if ($_SESSION['utilidad'] != 'S') {
	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A4', 'CODIGO')
	->setCellValue('B4', 'PRODUCTO')
	->setCellValue('C4', 'UNIDAD MEDIDA')
	->setCellValue('D4', 'CANTIDAD')
	->setCellValue('E4', 'COSTO UNITARIO')
	->setCellValue('F4', 'VALOR TOTAL');
} else {
	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A4', 'CODIGO')
	->setCellValue('B4', 'PRODUCTO')
	->setCellValue('C4', 'UNIDAD MEDIDA')
	->setCellValue('D4', 'CANTIDAD')
	->setCellValue('E4', 'COSTO UNITARIO')
	->setCellValue('F4', 'VALOR TOTAL')
	->setCellValue('G4', 'PRECIO VENTA')
	->setCellValue('H4', 'IGV')
	->setCellValue('I4', 'TOTAL')
	->setCellValue('J4', 'MARGEN %')
	->setCellValue('K4', 'VAL. MARGEN');
}

$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('D4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('E4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('F4')->getFont()->setBold(true);
if ($_SESSION['utilidad'] == 'S') {
	$objPHPExcel->getActiveSheet()->getStyle('G4')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('H4')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('I4')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('J4')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('K4')->getFont()->setBold(true);
}

$objPHPExcel->getActiveSheet()->freezePane('A5');//LINEA HORIZONTAL

/* FIN */

$bucle = 5;

if (count($data) > 0) {
	$i	 				= 0;
	$nolinea 			= '';
	$noalmacen			= '';
	$sumcantnolinea		= 0;
	$sumcostnolinea		= 0;
	$sumtotnolinea		= 0;
	$sumcantnolineag	= 0;
	$sumcostnolineag	= 0;
	$sumtotnolineag		= 0;

	if ($_SESSION['utilidad'] == 'S') {
		$valor_margen = 0;
		$igv = 0;
		$total = 0;

		$valor_margeng = 0;
		$igvg = 0;
		$totalg = 0;
	}

	$fPorcentajeMargen = 0;

	foreach ($data as $row) {
		if ($nolinea != $row['art_linea']) {
			$bucle++;
			if ($i != 0) {
				if ($_SESSION['utilidad'] != 'S') {
					$objPHPExcel->setActiveSheetIndex($hoja)
						->setCellValue('C' . $bucle, 'Total Linea: ')
						->setCellValue('D' . $bucle, $sumcantnolinea)
						->setCellValue('E' . $bucle, $sumcostnolinea)
						->setCellValue('F' . $bucle, $sumtotnolinea);
				} else {
					$objPHPExcel->setActiveSheetIndex($hoja)
						->setCellValue('C' . $bucle, 'Total Linea: ')
						->setCellValue('D' . $bucle, $sumcantnolinea)
						->setCellValue('E' . $bucle, $sumcostnolinea)
						->setCellValue('F' . $bucle, $sumtotnolinea)
						->setCellValue('H' . $bucle, $igv)
						->setCellValue('I' . $bucle, $total)
						->setCellValue('K' . $bucle, $valor_margen);
				}

				$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle .':'.'K' . $bucle)->getFont()->setBold(true);

				$sumcantnolinea 	= 0;
				$sumcostnolinea 	= 0;
				$sumtotnolinea 		= 0;

				if ($_SESSION['utilidad'] == 'S') {
					$valor_margen = 0;
					$igv = 0;
					$total = 0;
				}

				$bucle++;
			}

			if ($noalmacen != $row['stk_almacen']){
				$bucle++;
				$objPHPExcel->setActiveSheetIndex($hoja)
					->setCellValue('A' . $bucle, 'Almacen: ')
					->setCellValue('B' . $bucle, $row['ch_nombre_almacen']);

				$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle .':'.'C' . $bucle)->getFont()->setBold(true);

				$noalmacen 	= $row['stk_almacen'];
			}

			$bucle++;

			$objPHPExcel->setActiveSheetIndex($hoja)
				->setCellValue('A' . $bucle, 'Linea: ')
				->setCellValue('B' . $bucle, $row['desclinea']);

			$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle .':'.'B' . $bucle)->getFont()->setBold(true);

			$nolinea 	= $row['art_linea'];
		}

		$bucle++;

		if ($_SESSION['utilidad'] != 'S') {
			$objPHPExcel->setActiveSheetIndex($hoja)
				->setCellValue('A' . $bucle, $row['art_codigo'])
				->setCellValue('B' . $bucle, $row['descripcion'])
				->setCellValue('C' . $bucle, $row['unidad'])
				->setCellValue('D' . $bucle, $row['nucantidad'])
				->setCellValue('E' . $bucle, $row['nucosto'])
				->setCellValue('F' . $bucle, $row['subtot']);
		} else {
			$fFormulaMargen = (round(($row['precio_venta'] / $row['ss_impuesto']), 2) - $row['nucosto']);
			settype($fFormulaMargen, "double");
			settype($row['nucosto'], "double");
			if ($row['nucosto'] > 0)
				$fPorcentajeMargen = (($fFormulaMargen * 100) / $row['nucosto']);
			$fImpoteMargen = ($fFormulaMargen * $row['nucantidad']);

			$objPHPExcel->setActiveSheetIndex($hoja)
				->setCellValue('A' . $bucle, $row['art_codigo'])
				->setCellValue('B' . $bucle, $row['descripcion'])
				->setCellValue('C' . $bucle, $row['unidad'])
				->setCellValue('D' . $bucle, $row['nucantidad'])
				->setCellValue('E' . $bucle, $row['nucosto'])
				->setCellValue('F' . $bucle, $row['subtot'])
				->setCellValue('G' . $bucle, $row['precio_venta'])
				->setCellValue('H' . $bucle, $row['igv'])
				->setCellValue('I' . $bucle, $row['total'])
				->setCellValue('J' . $bucle, round($fPorcentajeMargen, 0))
				->setCellValue('K' . $bucle, round($fImpoteMargen, 2));
		}

		$sumcantnolinea 	+= $row['nucantidad'];
		$sumcostnolinea 	+= $row['nucosto'];
		$sumtotnolinea	 	+= $row['subtot'];

		$sumcantnolineag 	+= $row['nucantidad'];
		$sumcostnolineag 	+= $row['nucosto'];
		$sumtotnolineag		+= $row['subtot'];

		if ($_SESSION['utilidad'] == 'S') {
			$igv += $row['igv'];
			$total += $row['total'];
			$valor_margen += $fImpoteMargen;

			$igvg += $row['igv'];
			$totalg += $row['total'];
			$valor_margeng += $fImpoteMargen;
		}

		$i++;

	}

	$bucle++;

	if ($_SESSION['utilidad'] != 'S') {
		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('C' . $bucle, 'Total Linea: ')
		->setCellValue('D' . $bucle, $sumcantnolinea)
		->setCellValue('E' . $bucle, $sumcostnolinea)
		->setCellValue('F' . $bucle, $sumtotnolinea);
	} else {
		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('C' . $bucle, 'Total Linea: ')
		->setCellValue('D' . $bucle, $sumcantnolinea)
		->setCellValue('E' . $bucle, $sumcostnolinea)
		->setCellValue('F' . $bucle, $sumtotnolinea)
		->setCellValue('H' . $bucle, $igv)
		->setCellValue('I' . $bucle, $total)
		->setCellValue('K' . $bucle, $valor_margen);
	}

	$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle .':'.'K' . $bucle)->getFont()->setBold(true);

	$bucle++;

	if ($_SESSION['utilidad'] != 'S') {
		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('C' . $bucle, 'Total General: ')
		->setCellValue('D' . $bucle, $sumcantnolineag)
		->setCellValue('E' . $bucle, $sumcostnolineag)
		->setCellValue('F' . $bucle, $sumtotnolineag);
	} else {
		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('C' . $bucle, 'Total General: ')
		->setCellValue('D' . $bucle, $sumcantnolineag)
		->setCellValue('E' . $bucle, $sumcostnolineag)
		->setCellValue('F' . $bucle, $sumtotnolineag)
		->setCellValue('H' . $bucle, $igvg)
		->setCellValue('I' . $bucle, $totalg)
		->setCellValue('K' . $bucle, $valor_margeng);
	}

	$objPHPExcel->getActiveSheet()->getStyle('B' . $bucle .':'.'K' . $bucle)->getFont()->setBold(true);


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