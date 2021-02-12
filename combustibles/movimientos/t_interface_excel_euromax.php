<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

class InterfaceMovTemplateEuromax extends Template {

    function titulo() {
		$titulo = '<div align="center"><h2>Interface Opensoft --> PECANA</h2></div><hr>';
		return $titulo;
    }

   	function errorResultado($errormsg) {
    	return '<blink>' . $errormsg . '</blink>';
	}

   	function ResultadoEjecucion($msg) {
       	return '<blink>' . $msg . '</blink>';
   	}

	function sucursalesCBArray() {
        global $sqlca;

		$query = "SELECT ch_sucursal, ch_sucursal||' '||ch_nombre_breve_sucursal FROM int_ta_sucursales ORDER BY ch_sucursal";
		$cbArray = array();

		if ($sqlca->query($query) <= 0)
		    return $cbArray;

		while ($result = $sqlca->fetchRow()) {
			$cbArray[trim($result[0])] = $result[1];
		}

		$cbArray['all'] = "ALL";
		return $cbArray;
	}

	function formInterfaceMov($datos) {

		$CbSucursales = InterfaceMovTemplateEuromax::sucursalesCBArray();

    	if (empty($datos["fechaini"])) {
	    	$dia = date("d");
	    	$mes = date("m");
	    	$anio = date("Y");
	    	$datos["fechaini"] = $dia . "/" . $mes . "/" . $anio;
    	}

    	if (empty($datos["fechafin"])) {
	    	$dia = date("d");
	    	$mes = date("m");
	    	$anio = date("Y");
	    	$datos["fechafin"] = $dia . "/" . $mes . "/" . $anio;
    	}

		$form = new form2('INTERFACE PECANA', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZEUROMAX'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZEUROMAX'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["ch_ruc"]));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr><td class="form_td_title">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]', 'Sucursal </td><td>: ', trim(@$datos["sucursal"]), $CbSucursales, espacios(50)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    	$fecha = explode("/", $datos["fechaini"]);
    	$fecha_mostrar = $fecha[2] . "-" . $fecha[1];
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text('datos[fechaini]', 'Fecha a Generar</td><td>: ', @$fecha_mostrar, '', 12, 7));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>a&ntilde;o-mes</b></div>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    	$form->addGroup('buttons', '');
    	$form->addElement('buttons', new f2element_submit('action', 'Generar', espacios(2)));

    	return $form->getForm() . '<div id="error_body" align="center"></div><hr>';
	}

    function reporteExcelPersonalizado($array_unior) {
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);

		date_default_timezone_set('America/Lima');

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
        	$bucle = 2;

		$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A1', 'Tipodocext')
	        ->setCellValue('B1', "Serieventa ")
	        ->setCellValue('C1', " Nventa ")
	        ->setCellValue('D1', "Moneda")
	        ->setCellValue('E1', 'Fecha')
	        ->setCellValue('F1', 'Fechavenc')
	        ->setCellValue('G1', 'Condicion')
	        ->setCellValue('H1', 'Idsucursal')
	        ->setCellValue('I1', 'RUCcliente')
	        ->setCellValue('J1', 'DNIcliente')
	        ->setCellValue('K1', 'Nomcliente')
	        ->setCellValue('L1', 'Dircliente')
	        ->setCellValue('M1', 'Tipocliente')
	        ->setCellValue('N1', 'Serieticke')
	        ->setCellValue('O1', 'Nomvendedo')
	        ->setCellValue('P1', 'Formapago')
	        ->setCellValue('Q1', 'Nomtarjeta')
	        ->setCellValue('R1', 'Ntarjeta')
	        ->setCellValue('S1', 'Obs')
	        ->setCellValue('T1', 'Cantiditem')
	        ->setCellValue('U1', 'Precioitem')
	        ->setCellValue('V1', 'Totalitem')
	        ->setCellValue('W1', 'Bonificaci')
	        ->setCellValue('X1', 'Subtotalve')
	        ->setCellValue('Y1', 'Igvventa')
	        ->setCellValue('Z1', 'Totalventa')
	        ->setCellValue('AA1', 'Anulada')
	        ->setCellValue('AB1', 'Panultiket')
	        ->setCellValue('AC1', 'Nventaanul')
	        ->setCellValue('AD1', 'Pigvitem')
	        ->setCellValue('AE1', 'Igvitem')
	        ->setCellValue('AF1', 'Idcasop')
	        ->setCellValue('AG1', 'Idturno')
	        ->setCellValue('AH1', 'Idmanguera')
	        ->setCellValue('AI1', 'Serieproducto')
	        ->setCellValue('AJ1', 'cadVentaAnticipo')
	        ->setCellValue('AK1', 'nomPOS')
	        ->setCellValue('AL1', 'pPercepcion')
	        ->setCellValue('AM1', 'Percepcion')
            ->setCellValue('AN1', 'Codigo Producto')
	        ->setCellValue('AO1', 'Idprodext');

       	$objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(20);

		if (count($array_unior) > 0) {
			foreach ($array_unior as $rows_insert) {
				$cod_producto = "";
				$cod_producto = $rows_insert['codigo'];
				$no_producto = "";

				$objPHPExcel->setActiveSheetIndex($hoja)
			        ->setCellValue('A' . $bucle, $rows_insert[0])
	                ->setCellValue('B' . $bucle, $rows_insert[1])
	                ->setCellValue('C' . $bucle, $rows_insert[2])
	                ->setCellValue('D' . $bucle, $rows_insert[3])
	                ->setCellValue('E' . $bucle, $rows_insert[4])
	                ->setCellValue('F' . $bucle, $rows_insert[5])
	                ->setCellValue('G' . $bucle, $rows_insert[6])
	                ->setCellValue('H' . $bucle, $rows_insert[7]);

				if($rows_insert[38] == 'A'){
					$objPHPExcel->setActiveSheetIndex($hoja)
	                	->setCellValue('I' . $bucle, "-")//RUC CLIENTE
	                	->setCellValue('J' . $bucle, "-")//DNI CLIENTE
		                ->setCellValue('K' . $bucle, "ANULADO");//NAME CLIENTE
				}else{
					$objPHPExcel->setActiveSheetIndex($hoja)
		                ->setCellValue('I' . $bucle, $rows_insert[8])//RUC CLIENTE
	                	->setCellValue('J' . $bucle, "-")//DNI CLIENTE
	               		->setCellValue('K' . $bucle, $rows_insert[9]);//NAME CLIENTE
				}

				$objPHPExcel->setActiveSheetIndex($hoja)
		        		->setCellValue('L' . $bucle, $rows_insert[10]);

				if($rows_insert[8] == '-'){
					$objPHPExcel->setActiveSheetIndex($hoja)
		                ->setCellValue('M' . $bucle, "0");//TYPE CLIENTE
				}else{
					$objPHPExcel->setActiveSheetIndex($hoja)
		                ->setCellValue('M' . $bucle, "1");//TYPE CLIENTE
				}

				$objPHPExcel->setActiveSheetIndex($hoja)
	                ->setCellValue('N' . $bucle, $rows_insert[11])
	                ->setCellValue('O' . $bucle, $rows_insert[12])
	                ->setCellValue('P' . $bucle, $rows_insert[13])
	                ->setCellValue('Q' . $bucle, $rows_insert[14])
	                ->setCellValue('R' . $bucle, "-")
	                ->setCellValue('S' . $bucle, "-")
			        ->setCellValue('T' . $bucle, $rows_insert[18])
		            ->setCellValue('U' . $bucle, $rows_insert[19])
			        ->setCellValue('V' . $bucle, $rows_insert[20])
		            ->setCellValue('W' . $bucle, $rows_insert[21])
			        ->setCellValue('X' . $bucle, $rows_insert[22])
			        ->setCellValue('Y' . $bucle, $rows_insert[23])
	              	->setCellValue('Z' . $bucle, $rows_insert[24])
	                ->setCellValue('AA' . $bucle, $rows_insert[25])
	                ->setCellValue('AB' . $bucle, $rows_insert[26])
					->setCellValue('AC' . $bucle, $rows_insert[27])
	                ->setCellValue('AD' . $bucle, $rows_insert[28])
	                ->setCellValue('AE' . $bucle, $rows_insert[29])
	                ->setCellValue('AF' . $bucle, $rows_insert[30])
	                ->setCellValue('AG' . $bucle, $rows_insert[31])
	                ->setCellValue('AH' . $bucle, $rows_insert[32])
					->setCellValue('AI' . $bucle, "-")
					->setCellValue('AJ' . $bucle, "-")
					->setCellValue('AK' . $bucle, "USUARIO-POS")
					->setCellValue('AL' . $bucle, "-")
					->setCellValue('AM' . $bucle, "-");
					//->setCellValue('AN' . $bucle, $rows_insert[36]);//39

				if ( !empty($rows_insert[17]) )
					$no_producto = trim($rows_insert[17]);

				$objPHPExcel->setActiveSheetIndex($hoja)
					->setCellValue('AN' . $bucle, $rows_insert[36])
	                ->setCellValue('AO' . $bucle, $no_producto);
				$bucle++;
			}
		}

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="EUROMAXXX.xls"');
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
}

