<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}

include_once('/sistemaweb/include/Classes/PHPExcel.php');
$objPHPExcel = new PHPExcel();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

$sql = "
SELECT
 PRE.pre_lista_precio AS id_lista_precio,
 LPRE.tab_descripcion AS no_lista_precio,
 ITEM.art_codigo AS nu_codigo_item,
 ITEM.art_descripcion AS no_nombre_item,
 PRE.pre_precio_act1
FROM
 fac_lista_precios AS PRE 
 JOIN int_articulos AS ITEM
  USING(art_codigo)
 JOIN int_tabla_general AS LPRE
  ON(PRE.pre_lista_precio = LPRE.tab_elemento AND tab_tabla='LPRE' AND tab_elemento!='000000')
ORDER BY 2;
";

$iStatusSQL = $sqlca->query($sql);
if ( (int)$iStatusSQL > 0 ) {
	error_log('iniciando');
	$arrData = $sqlca->fetchAll();

	error_log('empieza');
	$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
	->setLastModifiedBy("OpenSysperu")
	->setTitle("Office 2007 XLSX Test Document")
	->setSubject("Office 2007 XLSX Test Document")
	->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
	->setKeywords("office 2007 openxml php")
	->setCategory("Test result file");
	
	$cabecera = array(
		'fill' => array(
        	'type' => PHPExcel_Style_Fill::FILL_SOLID,
        	'color' => array('argb' => 'FFCCFFCC')
    	),
    	'borders' => array(
    		'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
    		'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
    	)
	);

	$objPHPExcel->setActiveSheetIndex(0);
	$hoja = 0;

    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A1', 'LISTA DE PRECIO')
	->setCellValue('A3', 'EDS LISTA PRECIO')
    ->setCellValue('B3', 'COD. ITEM')
    ->setCellValue('C3', 'DESCRIPCION')
    ->setCellValue('D3', 'PRECIO');
	
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

	$objPHPExcel->getActiveSheet()->freezePane('A3');
	$fila = 3;

	error_log('ejecutando...');
	foreach ($arrData as $row) {
		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('A' . $fila, $row['no_lista_precio'])
		->setCellValue('B' . $fila, $row['nu_codigo_item'])
		->setCellValue('C' . $fila, $row['no_nombre_item'])
		->setCellValue('D' . $fila, $row['pre_precio_act1']);
		++$fila;
	}
	error_log('Finalizando...');

    header('Content-Type: application/vnd.ms-excel');
   	header('Content-Disposition: attachment;filename="ListaPrecio.xls"');
    header('Cache-Control: max-age=0');

	header('Cache-Control: max-age=1');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: cache, must-revalidate');
	header('Pragma: public');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
} else if ( $iStatusSQL == 0 ) {
    return array(
        'sStatus' => 'warning',
        'sMessage' => 'No hay registros',
    );
}
return array(
    'sStatus' => 'danger',
    'sMessage' => 'Problemas al obtener lista de precio',
    'sql' => $sql,
    'sMessageSQL' => $sqlca->get_error(),
);