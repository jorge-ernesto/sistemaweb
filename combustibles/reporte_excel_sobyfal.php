<?php

include_once('../include/Classes/PHPExcel.php');
include("../config.php");
include("../valida_sess.php");
include("../functions.php");

session_start();
include_once('../include/Classes/PHPExcel.php');

$cod_almacen 	= trim($_GET["cod_almacen"]);
$cod_tanque 	= trim($_GET['cod_tanque']);
$fechad			= trim($_GET['fechad']);
$fechaa			= trim($_GET['fechaa']);
$unidad_medida	= trim($_GET['medida']);

/*
echo "<pre>";
var_dump($arrData);
echo "</pre>";
*/

/* ALMACEN */
$sql = "
	SELECT
		ch_almacen||' - '||ch_nombre_almacen almacen
	FROM
		inv_ta_almacenes
	WHERE
		ch_clase_almacen = '1'
		AND ch_almacen = '$cod_almacen'
	ORDER BY
		ch_almacen;
	";

$almacen = pg_exec($sql);
/* FIN ALMACEN */

/* ALMACEN */
$sql = "
	SELECT
		tank.ch_tanque || ' - ' || combu.ch_nombrecombustible notanque
	FROM
		comb_ta_tanques tank
		JOIN comb_ta_combustibles combu ON (combu.ch_codigocombustible = tank.ch_codigocombustible)
	WHERE
		tank.ch_tanque = '$cod_tanque'
	ORDER BY
		tank.ch_tanque;
	";

$tanque = pg_exec($sql);
/* FIN ALMACEN */

//LOGICA PARA VERIFICAR SI SE OBTIENE SOLO UN COMBUSTIBLE O TODOS
$rs1    = pg_exec("
						SELECT 
							'00',
							'00 -- TODOS'
		
						UNION

						SELECT DISTINCT 
							a.ch_tanque,
							a.ch_tanque  || ' -- ' || b.ch_nombrecombustible 
						FROM 
							comb_ta_tanques a,
							comb_ta_combustibles b,
							comb_ta_tanques c
						WHERE 
							a.ch_codigocombustible=b.ch_codigocombustible
							AND a.ch_tanque=c.ch_tanque
							AND c.ch_codigocombustible=b.ch_codigocombustible
							AND c.ch_sucursal=trim('" . $cod_almacen . "')
						ORDER BY
							1 ASC");

$comb = pg_exec("	SELECT 
						comb.ch_nombrecombustible 
					FROM 
						comb_ta_combustibles comb, 
						comb_ta_tanques tan 
					WHERE 
						tan.ch_codigocombustible=comb.ch_codigocombustible 
						AND tan.ch_tanque='$cod_tanque'
						AND tan.ch_sucursal=trim('$cod_almacen') ");

$procesar = false;
$combustibles = array();

if(pg_numrows($comb) > 0) { //Si existe el combustible
	$C    = pg_fetch_row($comb,0);
	$comb = $C[0];
	$procesar = true;
	
	//OBTENEMOS ARRAY COMBUSTIBLES
	$combustibles[$cod_tanque] = array(
		"codigo_tanque" => $cod_tanque,
		"nombre_combustible" => $cod_tanque . " -- " . $comb
	);
}elseif($cod_tanque == '00'){ //Si se selecciono todos los combustibles
	$comb = "TODOS";
	$procesar = true;

	//OBTENEMOS ARRAY COMBUSTIBLES
	for($i = 0; $i < pg_numrows($rs1); $i++) {
		$A = pg_fetch_row($rs1,$i);			
		
		if($A[0] == '00'){
			continue;
		}
		
		$combustibles[$A[0]] = array(
			"codigo_tanque" => $A[0],
			"nombre_combustible" => $A[1]
		);
	}
}else { 
	$comb = "";
}

if($procesar){
	//Buscar por combustible
	foreach ($combustibles as $key => $value) {				
		$arrData[$value['nombre_combustible']] = sobrantesyfaltantesReporte($cod_almacen,$value['codigo_tanque'],$fechad,$fechaa, $unidad_medida, "No", false);
	}	
}
// echo "<pre>";
// print_r($arrData);
// echo "</pre>";
// die();
//CERRAR LOGICA PARA VERIFICAR SI SE OBTIENE SOLO UN COMBUSTIBLE O TODOS

reporteExcelPersonalizado($arrData, $fechad, $fechaa, $almacen, $tanque);

pg_close();

function reporteExcelPersonalizado($arrData, $fechad, $fechaa, $almacen, $tanque) {

	$array_index_global	= array();
	$index_global		= 1;
	$conta_tm		= 1;

	if(pg_numrows($almacen)>0){
		$almacen = pg_fetch_array($almacen,0);
	}else{
		//$almacen[0] = "";
	}

	if(pg_numrows($tanque)>0){
		$tanque = pg_fetch_array($tanque,0);
	}else{
		//$tanque[0] = "";
	}

	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	date_default_timezone_set('Europe/London');

	if (PHP_SAPI == 'cli')
		die('This example should only be run from a Web Browser');

	$objPHPExcel = new PHPExcel();

	$objPHPExcel->getProperties()
			->setCreator("Maarten Balliauw")
			->setLastModifiedBy("OpenSysperu")
			->setTitle("Office 2007 XLSX Test Document")
			->setSubject("Office 2007 XLSX Test Document")
			->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
			->setKeywords("office 2007 openxml php")
			->setCategory("Test result file");

	$cabecera = array('fill' => 
			array(
				'type'	=> PHPExcel_Style_Fill::FILL_SOLID,
				'color'	=>
			 array(
				'argb'	=> 'FFCCFFCC')
			),

				'borders'	=> array(
				'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)

			)

    		);

	$objPHPExcel->setActiveSheetIndex(0);
	$hoja = 0;

        $titulo = "SOBRANTES Y FALTANTES DE COMBUSTIBLES";
        $periodo = "Periodo: ";

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
	
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);

	$objPHPExcel->getActiveSheet()->mergeCells('G3:I3');

	//TITULOS

	$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A1', $titulo)
			->setCellValue('F2', $periodo)
			->setCellValue('G2', $fechad)
			->setCellValue('H2', 'Al: ')
			->setCellValue('I2', $fechaa)
			->setCellValue('F3', 'Almacen: ')
			->setCellValue('G3', $almacen[0])
			->setCellValue('F4', 'Tanque: ')
			->setCellValue('G4', (is_null($tanque[0])) ? "TODOS" : $tanque[0]);
	
	//CABECERA DE COLUMNAS
	$bucle = 6;

	$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A' . $bucle, ' Fecha ')
			->setCellValue('B' . $bucle, ' Saldo ')
			->setCellValue('C' . $bucle, ' Compra ')
			->setCellValue('D' . $bucle, ' Medicion ')
			->setCellValue('E' . $bucle, ' Venta ')
			->setCellValue('F' . $bucle, ' Precio ')
			->setCellValue('G' . $bucle, ' Ingreso ')
			->setCellValue('H' . $bucle, ' Salida ')
			->setCellValue('I' . $bucle, ' Parte ')
			->setCellValue('J' . $bucle, ' Varilla ')
			->setCellValue('K' . $bucle, ' Diaria ')
			->setCellValue('L' . $bucle, ' Acumulada ');

	//ROWS

	$objPHPExcel->getActiveSheet()->freezePane('A7');
	$bucle = 7;

foreach ($arrData as $key => $value) {
	$arrData = $value;

	$total_compra	= 0;
	$total_medicion	= 0;
	$total_venta	= 0;
	$total_ingreso	= 0;
	$total_salida	= 0;
	$total_parte	= 0;
	$total_varilla	= 0;
	$total_diaria	= 0;

	if (is_array($arrData)) {

		$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A' . $bucle, ''.$key);
		$bucle += 1;

		for ($i = 0; $i < count($arrData); $i++) {

			$E = $arrData[$i];

			$total_compra	= $total_compra + $E[2];
			$total_medicion	= $total_medicion + $E[3];
			$total_venta	= $total_venta + $E[4];
			$total_ingreso	= $total_ingreso + $E[5];
			$total_salida	= $total_salida + $E[6];
			$total_parte	= $total_parte + $E[7];
			$total_varilla	= $total_varilla + $E[8];
			$total_diaria	= $total_diaria + $E[9];

			$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A' . $bucle, ((empty($E[0]) || $E[0] == '' || $E[0] == NULL) ? "0.00" : $E[0]))
			->setCellValue('B' . $bucle, ((empty($E[1]) || $E[1] == '' || $E[1] == NULL) ? "0.00" : $E[1]))
			->setCellValue('C' . $bucle, ((empty($E[2]) || $E[2] == '' || $E[2] == NULL) ? "0.00" : $E[2]))
			->setCellValue('D' . $bucle, ((empty($E[3]) || $E[3] == '' || $E[3] == NULL) ? "0.00" : $E[3]))
			->setCellValue('E' . $bucle, ((empty($E[4]) || $E[4] == '' || $E[4] == NULL) ? "0.00" : $E[4]))
			->setCellValue('F' . $bucle, ((empty($E[12]) || $E[12] == '' || $E[12] == NULL) ? "0.00" : $E[12]))//PRECIO_VENTA
			->setCellValue('G' . $bucle, ((empty($E[5]) || $E[5] == '' || $E[5] == NULL) ? "0.00" : $E[5]))
			->setCellValue('H' . $bucle, ((empty($E[6]) || $E[6] == '' || $E[6] == NULL) ? "0.00" : $E[6]))
			->setCellValue('I' . $bucle, ((empty($E[7]) || $E[7] == '' || $E[7] == NULL) ? "0.00" : $E[7]))
			->setCellValue('J' . $bucle, ((empty($E[8]) || $E[8] == '' || $E[8] == NULL) ? "0.00" : $E[8]))
			->setCellValue('K' . $bucle, ((empty($E[9]) || $E[9] == '' || $E[9] == NULL) ? "0.00" : number_format($E[9], 3, '.', '')))
			->setCellValue('L' . $bucle, ((empty($E[10]) || $E[10] == '' || $E[10] == NULL) ? "0.00" : $E[10]));
			$bucle++;
		}
		$bucle += 2;

		//$objPHPExcel->getActiveSheet()->mergeCells('A'.$bucle:'B'.$bucle);

		$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('B' . $bucle, 'TOTALES: ')
			->setCellValue('C' . $bucle, $total_compra)
			->setCellValue('D' . $bucle, $total_medicion)
			->setCellValue('E' . $bucle, $total_venta)
			->setCellValue('F' . $bucle, '-')
			->setCellValue('G' . $bucle, $total_ingreso)
			->setCellValue('H' . $bucle, $total_salida)
			->setCellValue('I' . $bucle, $total_parte)
			->setCellValue('J' . $bucle, $total_varilla)
			->setCellValue('K' . $bucle, $total_diaria);

		$bucle += 2;
	}	
}

	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="SobrantesFaltantesCombustibles.xls"');
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

