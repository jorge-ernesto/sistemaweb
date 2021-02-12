<?php

//

class InterfaceMovTemplateCE extends Template {

    function titulo() {
        $titulo = '<div align="center"><h2>Interface Opensoft  -->  CLUB ESMERALDA</h2></div><hr>';
        return $titulo;
    }

    function errorResultado($errormsg) {
        return '<blink>' . $errormsg . '</blink>';
    }

    function ResultadoEjecucion($msg) {
        return '<blink>' . $msg . '</blink>';
    }

    function ListadoModulos() {
        $CbModulos = array(
            "TODOS" => "[ Todos ]",
            "VENTAS" => "VENTAS",
            "VENTASC" => "VENTAS COMBUSTIBLES",
            "VENTASM" => "VENTAS MARKET",
            "COMPRAS" => "COMPRAS y CPAGAR",
            "INVENTARIOS" => "INVENTARIOS",
            "VALES" => "VALES",
            "FACTURACION" => "FACTURACION",
            "PRECANCELACION" => "PRECANCELACION",
            "SERVICIOS" => "SERVICIOS",
            "PLANILLA" => "COMISIONES y ASISTENCIA"
        );
        $CbModulos = array("TODOS" => "[ Todos ]");

        return $CbModulos;
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
        return $cbArray;
    }

    function ListadoMes() {
        $CbMes = array(
            "01" => "ENERO",
            "02" => "FEBRERO",
            "03" => "MARZO",
            "04" => "ABRIL",
            "05" => "MAYO",
            "06" => "JUNIO",
            "07" => "JULIO",
            "08" => "AGOSTO",
            "09" => "SETIEMBRE",
            "10" => "OCTUBRE",
            "11" => "NOVIEMBREA",
            "12" => "DICIEMBRE"
        );

        return $CbMes;
    }

    function formInterfaceMov() {
        $CbModulos = InterfaceMovTemplateCE::ListadoModulos();
        $CbMes = InterfaceMovTemplateCE::ListadoMes();
        $CbSucursales = InterfaceMovTemplateCE::sucursalesCBArray();

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

        $form = new form2('INTERFACE CLUB ESMERALDA', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZESMERALDA'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZESMERALDA'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["ch_ruc"]));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr><td class="form_td_title">'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[modulos]', 'M&oacute;dulos </td><td>: ', trim(@$datos["modulos"]), $CbModulos, espacios(3)));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]', 'Sucursal </td><td>: ', trim(@$datos["sucursal"]), $CbSucursales, espacios(50)));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
        $fecha = explode("/", $datos["fechaini"]);
        $fecha_mostrar = $fecha[2] . "-" . $fecha[1];
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('datos[fechaini]', 'Fecha de Generacion</td><td>: ', @$fecha_mostrar, '', 12, 10));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>a&ntilde;o/mes</b></div>'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
        $form->addGroup('buttons', '');
        $form->addElement('buttons', new f2element_submit('action', 'Actualizar', espacios(2)));

        return $form->getForm() . '<div id="error_body" align="center"></div><hr>';
    }

//	function reporteExcel($resultado_postrans, $desde, $hasta, $tipo,$fecha) {
	function reporteExcel() {

		$array_index_global	= array();
		$index_global		= 1;
		$conta_tm		= 1;

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
		$bucle = 7;

		$objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('A4', 'SERIE')
                ->setCellValue('B4', "NRO FACTURA ")
                ->setCellValue('C4', " FECHA EMISION ")
                ->setCellValue('D4', "HORA EMISION ")
                ->setCellValue('E4', 'USUARIO')
                ->setCellValue('F4', 'CLIENTE')
                ->setCellValue('G4', 'DESCRIPCION')
                ->setCellValue('H4', 'MONEDA')
                ->setCellValue('I4', 'SEDE VENTA')
                ->setCellValue('J4', 'ALMACEN')
                ->setCellValue('K4', 'ESTADO')
                ->setCellValue('L4', 'ARTICULO')
                ->setCellValue('M4', 'CANTIDAD VTA')
                ->setCellValue('N4', 'P.U.VTA')
                ->setCellValue('O4', 'DESCUENTO')
                ->setCellValue('P4', 'IMPORTE')
                ->setCellValue('Q4', 'MEDIO PAGO')
                ->setCellValue('R4', 'LOTE(PERIODO)');

		if (count($resultado_postrans) > 0) {

		    	foreach ($resultado_postrans as $key => $value) {

				if (empty($value["ruc"])) {
					$ruc = "99999999999";
				} else {
					$ruc = $value["ruc"];
				}

				if (empty($value["rs"])) {
					$rs = "CLIENTE VARIOS";
				} else {
					$rs = $value["rs"];
				}

				if (empty($array_index_global[$value["id"]])) {
					$array_index_global[$value["id"]] = $index_global;
					$conta_tm = $index_global;
					$index_global++;
				} else {
					$conta_tm = $array_index_global[$value["id"]];
				}

		        	$objPHPExcel->setActiveSheetIndex($hoja)
		                ->setCellValue('A' . $bucle, $value["serie_documento"])
		                ->setCellValue('C' . $bucle, $value["fecha_emision"])
		                ->setCellValue('D' . $bucle, $value["hora_emision"])
		                ->setCellValue('E' . $bucle, $value["usuario"])
		                ->setCellValue('F' . $bucle, $ruc)
		                ->setCellValue('G' . $bucle, $rs)
		                ->setCellValue('H' . $bucle, "S")
		                ->setCellValue('I' . $bucle, "98")
		                ->setCellValue('K' . $bucle, $value["estado"])
		                ->setCellValue('L' . $bucle, $value["articulo"])
		                ->setCellValue('M' . $bucle, round($value["cantidad"], 3))
		                ->setCellValue('N' . $bucle, $value["precio"])
		                ->setCellValue('O' . $bucle, $value["descuento"])
		                ->setCellValue('P' . $bucle, $value["importe"])
		                ->setCellValue('Q' . $bucle, $value["medio_pago"])
		                ->setCellValue('R' . $bucle, $fecha);

			        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, $bucle, str_pad($value["num_documneto"], 8, "0", STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
			        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(9, $bucle, "004", PHPExcel_Cell_DataType::TYPE_STRING);

			        $bucle++;

			}

		}

		$objPHPExcel->getActiveSheet()->getStyle('G7:G' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('H7:H' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('N7:N' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('M7:M' . $bucle)->getNumberFormat()->setFormatCode('0.000');
		$objPHPExcel->getActiveSheet()->getStyle('Q7:Q' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('T7:T' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('P7:P' . $bucle)->getNumberFormat()->setFormatCode('0.00');
		$objPHPExcel->getActiveSheet()->getStyle('AL7:AL' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="ClubEsmeralda.xls"');
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

