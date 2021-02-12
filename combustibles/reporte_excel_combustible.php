<?php

include_once('../include/Classes/PHPExcel.php');
include("../valida_sess.php");
include("config.php");
include("../functions.php");

$fechad = $_REQUEST['fechad'];
$fechaa = $_REQUEST['fechaa'];
$almacen = trim($_REQUEST['cod_almacen']);
$codproducto = $_REQUEST['codproducto'];

$fecha_de = $fechad;
$fecha_hasta = $fechaa;

$and_almacen = null;
$and_sucursal = null;
$and_almacen_sub = null;
$and_almacen_afe = null;

$cond2 = null;
$cond3 = null;

if(empty($almacen)) {
	$almacen = "";
} else {
	$and_almacen 		= " AND cont.ch_sucursal = TRIM('" . $almacen . "')";
	$and_sucursal 		= " WHERE ch_sucursal = '" . $almacen . "'";
	$and_almacen_sub	= " AND comb.ch_sucursal = TRIM('" . $almacen . "')";
	$and_almacen_afe	= " AND af.es = TRIM('" . $almacen . "')";
}

if($codproducto == "TODOS") {
	$cond = "";
} else {
	$cond = "AND cont.ch_codigocombustible = TRIM('$codproducto')";
	$cond2 = "AND comb.ch_codigocombustible = TRIM('$codproducto')";
	$cond3 = "AND af.codigo = TRIM('$codproducto')";
}

$q3 = "
	SELECT
		cont.ch_numeroparte AS parte, 
		cont.ch_codigocombustible, 
		cont.ch_tanque AS tanque, 
		cont.ch_surtidor AS manguera, 
		cont.nu_contometroinicialgalon, 
		cont.nu_contometrofinalgalon, 
		cont.nu_ventagalon, 
		cont.nu_contometroinicialvalor, 
		cont.nu_contometrofinalvalor, 
		cont.nu_ventavalor, 
		cont.nu_afericionveces_x_5, 
		cont.nu_consumogalon, 
		-cont.nu_descuentos, 
		comb.ch_nombrecombustible, 
		cont.dt_fechaparte, 
		cont.ch_responsable, 
		surt.ch_numerolado AS lado,
		(
		SELECT
			ROUND((SUM(precio) / COUNT(*)),2)
		FROM
			pos_contometros
		WHERE
			dia = cont.dt_fechaparte
			AND num_lado::text = surt.ch_numerolado
			AND manguera = nu_manguera
		) AS precio	
	FROM 
		comb_ta_contometros cont
		LEFT JOIN comb_ta_surtidores surt ON (cont.ch_sucursal = surt.ch_sucursal AND cont.ch_surtidor = surt.ch_surtidor)
		LEFT JOIN comb_ta_combustibles comb ON (cont.ch_codigocombustible = comb.ch_codigocombustible)				
	WHERE 				
		cont.dt_fechaparte BETWEEN to_date('$fecha_de','DD-MM-YYYY') AND to_date('$fecha_hasta','DD-MM-YYYY')			
		$and_almacen
		$cond
	ORDER BY 
		parte,
		lado,
		manguera,
		tanque;
	";

$resultado_comb_ta_contometros = pg_exec($q3);


$q4 = "
	SELECT
		C.codigo AS codigo,
		COMB.descripcion AS descripcion,
		ROUND(COMB.total_cantidad,3) AS total_cantidad,
		ROUND(COMB.total_venta,2) AS total_venta,
		AFC.af_cantidad AS af_cantidad,
		AFC.af_total AS af_total,
		'0.000' AS consumo_galon,
		'0.000' AS consumo_valor,
		COMB.descuentos AS descuentos,
		CASE WHEN AFC.af_cantidad IS NULL THEN COMB.total_cantidad ELSE COMB.total_cantidad - AFC.af_cantidad END as resumen,
		CASE WHEN AFC.af_cantidad IS NULL THEN (COMB.total_venta + COMB.descuentos) ELSE ((COMB.total_venta + COMB.descuentos) - AFC.af_total) END as neto_soles				
	FROM

		(SELECT ch_codigocombustible AS codigo FROM comb_ta_tanques $and_sucursal) C

		INNER JOIN 

		(SELECT
			comb.ch_codigocombustible AS codigo,
			cmb.ch_nombrecombustible AS descripcion,
			SUM(CASE WHEN comb.nu_ventagalon != 0 THEN comb.nu_ventavalor ELSE 0 END) AS total_venta,
			SUM(CASE WHEN comb.nu_ventagalon != 0 THEN comb.nu_ventagalon ELSE 0 END) AS total_cantidad,
			ROUND(SUM(comb.nu_descuentos),2) AS descuentos
		 FROM 
			comb_ta_contometros comb
			LEFT JOIN comb_ta_combustibles cmb ON (comb.ch_codigocombustible = cmb.ch_codigocombustible)
		 WHERE
			comb.dt_fechaparte BETWEEN to_date('$fecha_de', 'DD/MM/YYYY') and to_date('$fecha_hasta', 'DD/MM/YYYY')
			$and_almacen_sub
			$cond2						
		GROUP BY 
			comb.ch_codigocombustible,
			cmb.ch_nombrecombustible
		) COMB on COMB.codigo = C.codigo

		LEFT JOIN

		(SELECT 
			af.codigo as codigo,
			SUM(af.importe) AS af_total,
			ROUND(SUM(af.cantidad), 3) AS af_cantidad
		FROM 
			pos_ta_afericiones af
		WHERE
			af.dia BETWEEN to_date('$fecha_de', 'DD/MM/YYYY') and to_date('$fecha_hasta', 'DD/MM/YYYY')
			$and_almacen_afe
			$cond3
		GROUP BY
			af.codigo
		)AFC ON AFC.codigo = C.codigo;

	";

$resultado_comb_ta_contometros_resumen = pg_exec($q4);

$_almacen = "TODOS";

if(!empty($almacen)){
	$sql = "
		SELECT
			ch_almacen||' - '||ch_nombre_almacen almacen
		FROM
			inv_ta_almacenes
		WHERE
			ch_clase_almacen = '1'
			AND ch_almacen = '" . $almacen . "'
		ORDER BY
			ch_almacen;
		";

	$_almacen = pg_exec($sql);
}

reporteExcelPersonalizado($resultado_comb_ta_contometros, $resultado_comb_ta_contometros_resumen, $fechad, $fechaa, $_almacen);

pg_close();

function reporteExcelPersonalizado($resultado, $resultado_comb_ta_contometros_resumen, $fechad, $fechaa, $_almacen) {

   	$array_index_global = array();
   	$index_global = 1;
    $conta_tm = 1;

	if($_almacen != 'TODOS')
		$almacen = pg_fetch_array($_almacen,0);
	else
		$almacen[0] = $_almacen;

	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	date_default_timezone_set('Europe/London');

    if (PHP_SAPI == 'cli')
		die('This example should only be run from a Web Browser');

	$objPHPExcel = new PHPExcel();

	$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
        	->setLastModifiedBy("OpenSysperu")
            	->setTitle("Office 2007 XLSX Test Document")
            	->setSubject("Office 2007 XLSX Test Document")
            	->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            	->setKeywords("office 2007 openxml php")
            	->setCategory("Test result file");

	$cabecera = array('fill' => array(
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

	$objPHPExcel->getActiveSheet()->freezePane('A6');
	$bucle = 6;

    $titulo = "PARTE DE VENTA";
    $periodo = "Periodo: ";

    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet()->mergeCells('A1:O1');
	
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

	$objPHPExcel->getActiveSheet()->mergeCells('G3:I3');

	$objPHPExcel->setActiveSheetIndex($hoja)
        	->setCellValue('A1', $titulo)
        	->setCellValue('F2', $periodo)
        	->setCellValue('G2', $fechad)
        	->setCellValue('H2', 'Al: ')
        	->setCellValue('I2', $fechaa)
        	->setCellValue('F3', 'Almacen: ')
        	->setCellValue('G3', $almacen[0])
        	->setCellValue('A5', 'Nro Parte')
            ->setCellValue('B5', 'Cod. Articulo')
            ->setCellValue('C5', 'Lado - Tanque')
            ->setCellValue('D5', 'Manguera')
            ->setCellValue('E5', 'Precio')
           	->setCellValue('F5', 'Contometro Inicial (galones)')
            ->setCellValue('G5', 'Contometro Final (galones)')
            ->setCellValue('H5', 'Galones Vendidos')
            ->setCellValue('I5', 'Contómetro Inicial (Soles)')
            ->setCellValue('J5', 'Contometro Final (soles)')
            ->setCellValue('K5', 'Soles Vendidos')
            ->setCellValue('L5', 'Afericiones')
            ->setCellValue('M5', 'Descuentos')
            ->setCellValue('N5', 'Descripción')
            ->setCellValue('O5', 'Fecha');

	if (pg_num_fields($resultado) > 0) {

		for ($i = 0; $i < pg_numrows($resultado); $i++) {

			$E = pg_fetch_row($resultado, $i);

			$objPHPExcel->setActiveSheetIndex($hoja)
                    		->setCellValue('A' . $bucle, $E[0])
                    		->setCellValue('B' . $bucle, $E[1])
                    		->setCellValue('C' . $bucle, $E[16] . ' - ' . $E[2])
                    		->setCellValue('D' . $bucle, $E[3])
                    		->setCellValue('E' . $bucle, $E[17])
                    		->setCellValue('F' . $bucle, $E[4])
                    		->setCellValue('G' . $bucle, $E[5])
                    		->setCellValue('H' . $bucle, $E[6])
                    		->setCellValue('I' . $bucle, $E[7])
                    		->setCellValue('J' . $bucle, $E[8])
                    		->setCellValue('K' . $bucle, $E[9])
                    		->setCellValue('L' . $bucle, $E[10])
                    		->setCellValue('M' . $bucle, $E[12])
                    		->setCellValue('N' . $bucle, $E[13])
                    		->setCellValue('O' . $bucle, $E[14] . '   ' . $E[15]);
			$bucle++;
		}
	}

	$bucle += 5;

	if (pg_num_fields($resultado_comb_ta_contometros_resumen) > 0) {

        $total_res_ven_gal 	= 0;
		$total_res_ven_val 	= 0;
		$total_res_afe_gal 	= 0;
		$total_res_afe_val 	= 0;
		$total_res_descuentos 	= 0;
		$total_resumen_gal 	= 0;
		$total_neto_val 	= 0;
        
		$objPHPExcel->setActiveSheetIndex($hoja)
                	->setCellValue('A' . $bucle, 'Producto')
                	->setCellValue('B' . $bucle, 'Descripción')
                	->setCellValue('C' . $bucle, 'Galones venta')
                	->setCellValue('D' . $bucle, 'Soles venta')
                	->setCellValue('E' . $bucle, 'Galones Afericion')
                	->setCellValue('F' . $bucle, 'Soles Afericion')
                	->setCellValue('G' . $bucle, 'Soles')
                	->setCellValue('H' . $bucle, 'Galones')
                	->setCellValue('I' . $bucle, 'Soles Neto');

		$bucle++;

		for ($i = 0; $i < pg_numrows($resultado_comb_ta_contometros_resumen); $i++) {

			$Q4 = pg_fetch_row($resultado_comb_ta_contometros_resumen, $i);

			$total_res_ven_gal 	= $total_res_ven_gal + $Q4[2];
			$total_res_ven_val 	= $total_res_ven_val + $Q4[3];
			$total_res_afe_gal 	= $total_res_afe_gal + $Q4[4];
			$total_res_afe_val 	= $total_res_afe_val + $Q4[5];
			$total_res_descuentos 	= $total_res_descuentos + $Q4[8];
			$total_resumen_gal 	= $total_resumen_gal + $Q4[9];
			$total_neto_val 	= $total_neto_val + $Q4[10];

    		$objPHPExcel->setActiveSheetIndex($hoja)
            	->setCellValue('A' . $bucle, $Q4[0])
            	->setCellValue('B' . $bucle, $Q4[1])
            	->setCellValue('C' . $bucle, $Q4[2])
            	->setCellValue('D' . $bucle, $Q4[3])
            	->setCellValue('E' . $bucle, $Q4[4])
            	->setCellValue('F' . $bucle, $Q4[5])
            	->setCellValue('G' . $bucle, $Q4[8])
            	->setCellValue('H' . $bucle, $Q4[9])
            	->setCellValue('I' . $bucle, $Q4[10]);

			$bucle++;
		}
	}

    header('Content-Type: application/vnd.ms-excel');
   	header('Content-Disposition: attachment;filename="ParteVenta.xls"');
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
