<?php

//

class InterfaceMovTemplateCE extends Template {

    function titulo() {
        $titulo = '<div align="center"><h2>Interface Opensoft  -->  PECANA</h2></div><hr>';
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

        //$query = "SELECT ch_sucursal, ch_sucursal||' '||ch_nombre_breve_sucursal FROM int_ta_sucursales ORDER BY ch_sucursal";
        $query = "SELECT ch_almacen, ch_almacen||' '||ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' ORDER BY ch_almacen";
        $cbArray = array();
        if ($sqlca->query($query) <= 0)
            return $cbArray;
        while ($result = $sqlca->fetchRow()) {
            $cbArray[trim($result[0])] = $result[1];
        }
        $cbArray['all'] = "ALL";
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

    function formInterfaceMov($datos) {
        $CbModulos = InterfaceMovTemplateCE::ListadoModulos();
        $CbMes = InterfaceMovTemplateCE::ListadoMes();
        $CbSucursales = InterfaceMovTemplateCE::sucursalesCBArray();

        /*
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
        */

//ingresra  identificador de sucursal de pecano
        $form = new form2('INTERFACE PECANA', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZPECANA'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZPECANA'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["ch_ruc"]));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr><td class="form_td_title">'));


        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]', 'Sucursal </td><td>: ', trim(@$datos["sucursal"]), $CbSucursales, espacios(50)));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

        //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
        //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

        // $form->addElement(FORM_GROUP_MAIN, new f2element_text('datos[cod_pecana]', 'Cod Pecana</td><td>: ', '1', '', 12, 10));
        //  $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
<tr>
    <td>Año</td>
    <td>:
        <select id="year" name="year"></select>
    </td>
</tr>
<tr>
    <td>Mes</td>
    <td>:
<select id="month" name="month">
<option value="01">Enero</option>
<option value="02">Febrero</option>
<option value="03">Marzo</option>
<option value="04">Abril</option>
<option value="05">Mayo</option>
<option value="06">Junio</option>
<option value="07">Julio</option>
<option value="08">Agosto</option>
<option value="09">Septiembre</option>
<option value="10">Octubre</option>
<option value="11">Noviembre</option>
<option value="12">Diciembre</option>
</select>
    </td>
        '));

/*
        $fecha = explode("/", $datos["fechaini"]);
        $fecha_mostrar = $fecha[2] . "-" . $fecha[1];
        $form->addElement(FORM_GROUP_MAIN, new f2element_text('datos[fechaini]', 'Fecha a Generar</td><td>: ', @$fecha_mostrar, '', 12, 7));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>a&ntilde;o-mes</b></div>'));
*/

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
        $form->addGroup('buttons', '');
        $form->addElement('buttons', new f2element_submit('action', 'Generar', espacios(2)));

        return $form->getForm() . '<div id="error_body" align="center"></div><hr>';
    }

    function reporteExcel($resultado_postrans, $desde, $hasta, $tipo, $fecha) {
        header('Content-type: text/csv');
        header('Content-disposition: attachment;filename=interface.csv');
        //ob_start();

        $fp = tmpfile();

        $titulo = array("TipoDocExt", "SerieVenta", "nVenta", "Moneda",
            "Fecha", "FechaVenc", "Condicion", "IdSucursal",
            "RucCliente", "NomCliente", "DirCliente", "SerieTicke",
            "NomVendedo", "Formapago", "NomTarjeta", "nTarjeta",
            "Obs", "IdProdExt", "Cantiditem", "Precioitem",
            "Totalitem", "Bonificaci", "SubtotalVe", "IGVventa",
            "TotalVenta", "Anulada", "PAnulTiket", "nVentaAnul",
            "pIGVitem", "IGVitem", "IdCasoP", "IdTurno",
            "IdManguera", "SerieProducto", "CadVentaAnticipo", "NomPOS",
        );



        fputcsv($fp, $titulo, ",");
        $byte_fila = 512;
        $count_rows = count($resultado_postrans);
        $file_size = $byte_fila * $count_rows;
        if (count($resultado_postrans) > 0) {

            foreach ($resultado_postrans as $fila) {
                $data_ins = array();
                foreach ($titulo as $head) {
                    $data_ins[] = strtr($fila[strtolower($head)], ",", " ");
                }
                fputcsv($fp, $data_ins, ",");
                $data_ins = null;
            }
            rewind($fp);
            echo fread($fp, $file_size);

            fclose($fp);
        }
        exit;
    }

    function reporteExcelPersonalizado($array_unior) {
        //$empresa = KardexActModel::datosEmpresa();
        error_log('0');
        $array_index_global = array();
        $index_global = 1;
        $conta_tm = 1;
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Europe/London');

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        error_log('1');
        /** Include PHPExcel */
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
                'color' => array('argb' => 'FFCCFFCC')
            ),
            'borders' => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
            )
        );


    // Miscellaneous glyphs, UTF-8
    $objPHPExcel->setActiveSheetIndex(0);
    $hoja = 0;

    //$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
    //$objPHPExcel->getActiveSheet()->freezePane('A6');
    $bucle = 2;
    //$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setWrapText(TRUE);

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
        ->setCellValue('T1', 'Idprodext')
        ->setCellValue('U1', 'Cantiditem')
        ->setCellValue('V1', 'Precioitem')
        ->setCellValue('W1', 'Totalitem')
        ->setCellValue('X1', 'Bonificaci')
        ->setCellValue('Y1', 'Subtotalve')
        ->setCellValue('Z1', 'Igvventa')
        ->setCellValue('AA1', 'Totalventa')
        ->setCellValue('AB1', 'Anulada')
        ->setCellValue('AC1', 'Panultiket')
        ->setCellValue('AD1', 'Nventaanul')
        ->setCellValue('AE1', 'Pigvitem')
        ->setCellValue('AF1', 'Igvitem')
        ->setCellValue('AG1', 'Idcasop')
        ->setCellValue('AH1', 'Idturno')
        ->setCellValue('AI1', 'Idmanguera')
        ->setCellValue('AJ1', 'Serieproducto')
        ->setCellValue('AK1', 'cadVentaAnticipo')
        ->setCellValue('AL1', 'nomPOS')
        ->setCellValue('AM1', 'pPercepcion')
        ->setCellValue('AN1', 'Percepcion');


        //error_log('PECANA: $rows_insert:');
        //error_log($rows_insert);

		if (count($array_unior) > 0) {
			foreach ($array_unior as $rows_insert) {

    			$cod_producto = "";
    			$cod_producto = $rows_insert['codigo'];

    			$objPHPExcel->setActiveSheetIndex($hoja)
            	        ->setCellValue('A' . $bucle, $rows_insert[0])
                            ->setCellValue('B' . $bucle, $rows_insert[1])
                            ->setCellValue('C' . $bucle, $rows_insert[2])
                            ->setCellValue('D' . $bucle, $rows_insert[3])
                            ->setCellValue('E' . $bucle, $rows_insert[4])
                            ->setCellValue('F' . $bucle, $rows_insert[5])
                            ->setCellValue('G' . $bucle, $rows_insert[6])
                            ->setCellValue('H' . $bucle, $rows_insert[7]);

                //error_log(isset($rows_insert[37]) ? '['.$rows_insert[37].'] OK' : '[ERR: --'.$rows_insert[37].' '.$rows_insert[1].'-'.$rows_insert[2].']');

    			if($rows_insert[37] == 'A'){//antes 38
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
                ->setCellValue('T'  . $bucle, $rows_insert[17])
                ->setCellValue('U'  . $bucle, $rows_insert[18])
                ->setCellValue('V'  . $bucle, $rows_insert[19])
                ->setCellValue('W'  . $bucle, $rows_insert[20])
                ->setCellValue('X'  . $bucle, $rows_insert[21])
                ->setCellValue('Y'  . $bucle, $rows_insert[22])
    	        ->setCellValue('Z' . $bucle, $rows_insert[23])
                ->setCellValue('AA' . $bucle, $rows_insert[24])
                ->setCellValue('AB' . $bucle, $rows_insert[25])
                ->setCellValue('AC' . $bucle, $rows_insert[26])
    			->setCellValue('AD' . $bucle, $rows_insert[27])
                ->setCellValue('AE' . $bucle, $rows_insert[28])
                ->setCellValue('AF' . $bucle, $rows_insert[29])
                ->setCellValue('AG' . $bucle, $rows_insert[30])
                ->setCellValue('AH' . $bucle, $rows_insert[31])
                ->setCellValue('AI' . $bucle, $rows_insert[32])
    			->setCellValue('AJ' . $bucle, "-")
    			->setCellValue('AK' . $bucle, "-")
    			->setCellValue('AL' . $bucle, "USUARIO-POS")
    			->setCellValue('AM' . $bucle, "-")
    			->setCellValue('AN' . $bucle, "-");

                //$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, $bucle, str_pad($value["num_documneto"], 8, "0", STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
                //$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(18, $bucle, $cod_producto, PHPExcel_Cell_DataType::TYPE_STRING);

                /*echo '<hr>';
                var_dump($rows_insert);*/

                $bucle++;
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="PECANA.xls"');
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

