<?php
include_once('../include/Classes/PHPExcel.php');
class InterfaceMovTemplate extends Template {

    function titulo() {
        $titulo = '<div align="center"><h2>Interface Opensoft  -->  BARRANCA VIP</h2></div><hr>';
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

    function formInterfaceMov($datos) {
        $CbModulos = InterfaceMovTemplate::ListadoModulos();
        $CbMes = InterfaceMovTemplate::ListadoMes();
        $CbSucursales = InterfaceMovTemplate::sucursalesCBArray();

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

        $form = new form2('INTERFACE BARRANCA VIP', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZBARRANCA'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZBARRANCA'));
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

    function reporteExcel($resultado, $resultado_postrans, $desde, $hasta, $tipo) {
        //$empresa = KardexActModel::datosEmpresa();
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Europe/London');

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

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
        $objPHPExcel->getActiveSheet()->freezePane('A6');
        $bucle = 7;
        //  $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setWrapText(TRUE);
        $objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('A4', 'Fecha')
                ->setCellValue('B4', "Tipo ")
                ->setCellValue('C4', " Serie ")
                ->setCellValue('D4', "Numero A ")
                ->setCellValue('E4', 'Numero B')
                ->setCellValue('F4', 'Detalle')
                ->setCellValue('G4', 'Importe')
                ->setCellValue('H4', 'IGV')
                ->setCellValue('I4', 'Otros')
                ->setCellValue('J4', 'RUC')
                ->setCellValue('K4', 'Importe')
                ->setCellValue('L4', 'Razon social')
                ->setCellValue('M4', 'Tasa')
                ->setCellValue('N4', 'Total Imponible')
                ->setCellValue('O4', 'Cuenta')
                ->setCellValue('P4', 'Moneda')
                ->setCellValue('Q4', 'Dimporte')
                ->setCellValue('R4', 'Digv')
                ->setCellValue('S4', 'Dotros')
                ->setCellValue('T4', 'Dimporte total')
                ->setCellValue('U4', 'Cod')
                ->setCellValue('V4', 'Cancelado')
                ->setCellValue('W4', 'Feacha Cancelacion')
                ->setCellValue('X4', 'Operacion')
                ->setCellValue('Y4', 'Cuenta 1')
                ->setCellValue('Z4', 'Deposito')
                ->setCellValue('AA4', 'Fecha dep')
                ->setCellValue('AB4', 'Mes')
                ->setCellValue('AC4', 'Galon')
                ->setCellValue('AD4', 'Cuenta 2')
                ->setCellValue('AE4', 'Analitico')
                ->setCellValue('AF4', 'Detratot')
                ->setCellValue('AG4', 'Tipodoc')
                ->setCellValue('AH4', 'Cod producto')
                ->setCellValue('AI4', 'Unidad')
                ->setCellValue('AJ4', 'Tipo cambio')
                ->setCellValue('AK4', 'Ad')
                ->setCellValue('AL4', 'Precio costo')
                ->setCellValue('AM4', 'Oricom')
                ->setCellValue('AN4', 'Pigv')
                ->setCellValue('AO4', 'Ptotal')
                ->setCellValue('AP4', 'ndfecha')
                ->setCellValue('AQ4', 'ndtipo')
                ->setCellValue('AR4', 'ndserie')
                ->setCellValue('AS4', 'ndnumero')
                ->setCellValue('AT4', 'Codigo')
                ->setCellValue('AU4', 'Estado')
                ->setCellValue('AV4', 'Perido');

        if (count($resultado) > 0) {

            foreach ($resultado as $key => $value) {
                $datos_ruc_rz = explode('-', $value["ruc_dni_rz"]);
                  $datos_ruc_rz=(count($datos_ruc_rz)==2)?$datos_ruc_rz:array("GENERICO","GENERICO");
                $objPHPExcel->setActiveSheetIndex($hoja)
                        ->setCellValue('A' . $bucle, $value["fecha_dococumento"])
                        ->setCellValue('B' . $bucle, $value["tipo_documento"])
                        ->setCellValue('C' . $bucle, $value["serie"])
                        ->setCellValue('D' . $bucle, $value["num_dococumento_a"])
                        ->setCellValue('E' . $bucle, $value["num_dococumento_b"])
                        ->setCellValue('F' . $bucle, $value["des_producto"])
                        ->setCellValue('G' . $bucle, $value["importe"])
                        ->setCellValue('H' . $bucle, $value["igv"])
                        ->setCellValue('I' . $bucle, $value["otros"])
                        ->setCellValue('J' . $bucle, $datos_ruc_rz[0])
                        ->setCellValue('K' . $bucle, $value["cod_producto"])
                        ->setCellValue('L' . $bucle, $datos_ruc_rz[1])
                        ->setCellValue('M' . $bucle, $value["tasa"])
                        ->setCellValue('N' . $bucle, $value["total_imponible"])
                        ->setCellValue('O' . $bucle, $value["cuenta_contable"])
                        ->setCellValue('P' . $bucle, $value["tipo_moneda"])
                        ->setCellValue('Q' . $bucle, $value["dimporte"])
                        ->setCellValue('R' . $bucle, $value["digv"])
                        ->setCellValue('S' . $bucle, $value["dotros"])
                        ->setCellValue('T' . $bucle, $value["dimportetotal"])
                        ->setCellValue('U' . $bucle, $value["cod"])
                        ->setCellValue('V' . $bucle, $value["cancelado"])
                        ->setCellValue('W' . $bucle, $value["fecha_cancelado"])
                        ->setCellValue('X' . $bucle, $value["operacion"])
                        ->setCellValue('Y' . $bucle, $value["cuenta1"])
                        ->setCellValue('Z' . $bucle, $value["deposito_datrecom"])
                        ->setCellValue('AA' . $bucle, $value["fecha_datrecom"])
                        ->setCellValue('AB' . $bucle, $value["mes_emision"])
                        ->setCellValue('AC' . $bucle, $value["galon"])
                        ->setCellValue('AD' . $bucle, $value["cuenta2"])
                        ->setCellValue('AE' . $bucle, $value["analitico"])
                        ->setCellValue('AF' . $bucle, $value["detratot"])
                        ->setCellValue('AG' . $bucle, $value["tipo_documneto"])
                        ->setCellValue('AH' . $bucle, $value["cod_producto_grupo"])
                        ->setCellValue('AI' . $bucle, $value["unidad_medida"])
                        ->setCellValue('AJ' . $bucle, $value["tipo_cambio"])
                        ->setCellValue('AK' . $bucle, $value["ad"])
                        ->setCellValue('AL' . $bucle, $value["precio_promedio"])
                        ->setCellValue('AM' . $bucle, $value["oricom"])
                        ->setCellValue('AN' . $bucle, $value["pigv"])
                        ->setCellValue('AO' . $bucle, $value["ptotal"])
                        ->setCellValue('AP' . $bucle, $value["ndfecha"])
                        ->setCellValue('AQ' . $bucle, $value["ndtipo"])
                        ->setCellValue('AR' . $bucle, $value["ndserie"])
                        ->setCellValue('AS' . $bucle, $value["ndnumero"])
                        ->setCellValue('AT' . $bucle, $value["cod_producto_grupo"])
                        ->setCellValue('AU' . $bucle, $value["estado"])
                        ->setCellValue('AV' . $bucle, $value["periodo"]);



                $bucle++;
            }
        }

$bucle++;
$bucle++;
        if (count($resultado_postrans) > 0) {
            foreach ($resultado_postrans as $key => $value) {
                $datos_ruc_rz = explode('-', $value["ruc_dni_rz"]);
                $datos_ruc_rz=(count($datos_ruc_rz)==2)?$datos_ruc_rz:array("GENERICO","GENERICO");
                $objPHPExcel->setActiveSheetIndex($hoja)
                        ->setCellValue('A' . $bucle, $value["fecha_dococumento"])
                        ->setCellValue('B' . $bucle, $value["tipo_documento"])
                        ->setCellValue('C' . $bucle, $value["serie"])
                        ->setCellValue('D' . $bucle, $value["num_dococumento_a"])
                        ->setCellValue('E' . $bucle, $value["num_dococumento_b"])
                        ->setCellValue('F' . $bucle, $value["des_producto"])
                        ->setCellValue('G' . $bucle, $value["importe"])
                        ->setCellValue('H' . $bucle, $value["igv"])
                        ->setCellValue('I' . $bucle, $value["otros"])
                        ->setCellValue('J' . $bucle, $datos_ruc_rz[0])
                        ->setCellValue('K' . $bucle, $value["cod_producto"])
                        ->setCellValue('L' . $bucle, $datos_ruc_rz[1])
                        ->setCellValue('M' . $bucle, $value["tasa"])
                        ->setCellValue('N' . $bucle, $value["total_imponible"])
                        ->setCellValue('O' . $bucle, $value["cuenta_contable"])
                        ->setCellValue('P' . $bucle, $value["tipo_moneda"])
                        ->setCellValue('Q' . $bucle, $value["dimporte"])
                        ->setCellValue('R' . $bucle, $value["digv"])
                        ->setCellValue('S' . $bucle, $value["dotros"])
                        ->setCellValue('T' . $bucle, $value["dimportetotal"])
                        ->setCellValue('U' . $bucle, $value["cod"])
                        ->setCellValue('V' . $bucle, $value["cancelado"])
                        ->setCellValue('W' . $bucle, $value["fecha_cancelado"])
                        ->setCellValue('X' . $bucle, $value["operacion"])
                        ->setCellValue('Y' . $bucle, $value["cuenta1"])
                        ->setCellValue('Z' . $bucle, $value["deposito_datrecom"])
                        ->setCellValue('AA' . $bucle, $value["fecha_datrecom"])
                        ->setCellValue('AB' . $bucle, $value["mes_emision"])
                        ->setCellValue('AC' . $bucle, $value["galon"])
                        ->setCellValue('AD' . $bucle, $value["cuenta2"])
                        ->setCellValue('AE' . $bucle, $value["analitico"])
                        ->setCellValue('AF' . $bucle, $value["detratot"])
                        ->setCellValue('AG' . $bucle, $value["tipo_documneto"])
                        ->setCellValue('AH' . $bucle, $value["cod_producto_grupo"])
                        ->setCellValue('AI' . $bucle, $value["unidad_medida"])
                        ->setCellValue('AJ' . $bucle, $value["tipo_cambio"])
                        ->setCellValue('AK' . $bucle, $value["ad"])
                        ->setCellValue('AL' . $bucle, $value["precio_promedio"])
                        ->setCellValue('AM' . $bucle, $value["oricom"])
                        ->setCellValue('AN' . $bucle, $value["pigv"])
                        ->setCellValue('AO' . $bucle, $value["ptotal"])
                        ->setCellValue('AP' . $bucle, $value["ndfecha"])
                        ->setCellValue('AQ' . $bucle, $value["ndtipo"])
                        ->setCellValue('AR' . $bucle, $value["ndserie"])
                        ->setCellValue('AS' . $bucle, $value["ndnumero"])
                        ->setCellValue('AT' . $bucle, $value["cod_producto_grupo"])
                        ->setCellValue('AU' . $bucle, $value["estado"])
                        ->setCellValue('AV' . $bucle, $value["periodo"]);

//$objPHPExcel->getActiveSheet()->getStyle('G10:G60')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $bucle++;
            }
        }

        /*
          $ini=6;
          foreach ($resultado['almacenes'] as $mov_almacen => $almacen) {
          $establecimiento =  $mov_almacen . " " . KardexActModel::obtenerDescripcionAlmacen($mov_almacen) . " " . $empresa['direccion'];
          //  $ini++;
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('A'.$ini, 'ESTABLECIMIENTO :')
          ->setCellValue('B'.$ini, $establecimiento);



          foreach ($almacen['articulos'] as $art_codigo => $articulo) {
          $cod_exi =  $art_codigo;
          $descrip =  KardexActModel::obtenerDescripcion($art_codigo);
          $cod_uni_med =  KardexActModel::unidadMedida($art_codigo);
          $ini++;


          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('A'.$ini, "CODIGO DE  LA EXISTENCIA:")//5
          ->setCellValue('B'.$ini, $cod_exi)
          ->setCellValue('C'.$ini, "DESCRIPCION :")
          ->setCellValue('D'.$ini, $descrip)
          ->setCellValue('E'.$ini, "CODIGO DE LA UNIDAD DE MEDIDA:")
          ->setCellValue('F'.$ini, $cod_uni_med);
          $ini++;


          if ($tipo == "CONTABLE") {
          $ini++;
          $objPHPExcel->getActiveSheet()->getRowDimension($ini)->setRowHeight(30);
          $objPHPExcel->getActiveSheet()->getStyle('A'.$ini.':O'.$ini)->applyFromArray($cabecera);
          $objPHPExcel->getActiveSheet()->getStyle('A'.$ini.':O'.$ini)->getFont()->setBold(true);
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('A'.$ini, 'FECHA')//10
          ->setCellValue('B'.$ini, 'TIPO')
          ->setCellValue('C'.$ini, 'SERIE')
          ->setCellValue('D'.$ini, 'NÚMERO')
          ->setCellValue('E'.$ini, ' FORMULARIO')
          ->setCellValue('F'.$ini, ' TIPO DE  OPERACIÓN')
          ->setCellValue('G'.$ini, 'CANTIDAD')
          ->setCellValue('H'.$ini, 'COSTO UNITARIO')
          ->setCellValue('I'.$ini, 'COSTO TOTAL')
          ->setCellValue('J'.$ini, 'CANTIDAD')
          ->setCellValue('K'.$ini, 'COSTO UNITARIO')
          ->setCellValue('L'.$ini, 'COSTO TOTAL')
          ->setCellValue('M'.$ini, 'CANTIDAD')
          ->setCellValue('N'.$ini, 'COSTO UNITARIO')
          ->setCellValue('O'.$ini, 'COSTO TOTAL');

          $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
          $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
          $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
          $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
          $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
          $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
          $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
          $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
          } else {
          $ini++;
          $objPHPExcel->getActiveSheet()->getRowDimension($ini)->setRowHeight(30);
          $objPHPExcel->getActiveSheet()->getStyle('A'.$ini.':I'.$ini)->applyFromArray($cabecera);
          $objPHPExcel->getActiveSheet()->getStyle('A'.$ini.':I'.$ini)->getFont()->setBold(true);
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('A'.$ini, 'FECHA')//10
          ->setCellValue('B'.$ini, 'TIPO')
          ->setCellValue('C'.$ini, 'SERIE')
          ->setCellValue('D'.$ini, 'NÚMERO')
          ->setCellValue('E'.$ini, ' FORMULARIO')
          ->setCellValue('F'.$ini, ' TIPO DE  OPERACIÓN')
          ->setCellValue('G'.$ini, 'ENTRADA')
          ->setCellValue('H'.$ini, 'SALIDA')
          ->setCellValue('I'.$ini, ' SALDO  FINAL');
          $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
          $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

          }

          $ini = $ini+1;
          foreach ($articulo['movimientos'] as $i => $movimiento) {
          if ($tipo == "CONTABLE") {
          $mov_numero = (!empty($movimiento['mov_numero'])) ? $movimiento['mov_numero'] : '--------';
          $mov_cant_entrada = (!empty($movimiento['mov_cant_entrada'])) ? (float) $movimiento['mov_cant_entrada'] : 0;
          $mov_unit_entrada = (!empty($movimiento['mov_unit_entrada'])) ? (float) $movimiento['mov_unit_entrada'] : 0;
          $mov_cost_entrada = (!empty($movimiento['mov_cost_entrada'])) ? (float) $movimiento['mov_cost_entrada'] : 0;
          $mov_cant_salida = (!empty($movimiento['mov_cant_salida'])) ? (float) $movimiento['mov_cant_salida'] : 0;
          $mov_unit_salida = (!empty($movimiento['mov_unit_salida'])) ? (float) $movimiento['mov_unit_salida'] : 0;
          $mov_cost_salida = (!empty($movimiento['mov_cost_salida'])) ? (float) $movimiento['mov_cost_salida'] : 0;
          $mov_cant_actual = (!empty($movimiento['mov_cant_actual'])) ? (float) $movimiento['mov_cant_actual'] : 0;
          $mov_val_unit_act = (!empty($movimiento['mov_val_unit_act'])) ? (float) $movimiento['mov_val_unit_act'] : 0;
          $mov_total_act = (!empty($movimiento['mov_total_act'])) ? (float) $movimiento['mov_total_act'] : 0;

          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('A' . $ini, substr($movimiento['mov_fecha'], 0, 10));
          $tipodocu = (empty($movimiento['tipodocu'])) ? '  ' : (string) $movimiento['tipodocu'];
          $seriedocu = (empty($movimiento['seriedocu'])) ? '  ' : (string) $movimiento['seriedocu'];
          $numdocu = (empty($movimiento['numdocu'])) ? '  ' : (string) $movimiento['numdocu'];
          $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(1, $ini, $tipodocu, PHPExcel_Cell_DataType::TYPE_STRING);
          $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(2, $ini, $seriedocu, PHPExcel_Cell_DataType::TYPE_STRING);
          $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $ini, $numdocu, PHPExcel_Cell_DataType::TYPE_STRING);
          $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(4, $ini, $mov_numero, PHPExcel_Cell_DataType::TYPE_STRING);

          $objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F' . $ini, $movimiento['tran_codigo'])
          ->setCellValue('G' . $ini, $mov_cant_entrada)
          ->setCellValue('H' . $ini, $mov_unit_entrada)
          ->setCellValue('I' . $ini, $mov_cost_entrada)
          ->setCellValue('J' . $ini, $mov_cant_salida)
          ->setCellValue('K' . $ini, $mov_unit_salida)
          ->setCellValue('L' . $ini, $mov_cost_salida)
          ->setCellValue('M' . $ini, $mov_cant_actual)
          ->setCellValue('N' . $ini, $mov_val_unit_act)
          ->setCellValue('O' . $ini, $mov_total_act);
          } else {
          $mov_numero = (!empty($movimiento['mov_numero'])) ? $movimiento['mov_numero'] : '--------';
          $mov_cant_entrada = (!empty($movimiento['mov_cant_entrada'])) ? (float) $movimiento['mov_cant_entrada'] : 0;
          $mov_cant_salida = (!empty($movimiento['mov_cant_salida'])) ? (float) $movimiento['mov_cant_salida'] : 0;
          $mov_cant_actual = (!empty($movimiento['mov_cant_actual'])) ? (float) $movimiento['mov_cant_actual'] : 0;


          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('A' . $ini, substr($movimiento['mov_fecha'], 0, 10))
          ->setCellValue('B' . $ini, (empty($movimiento['tipodocu'])) ? '  ' : (string) $movimiento['tipodocu'])
          ->setCellValue('C' . $ini, (empty($movimiento['seriedocu'])) ? '  ' : $movimiento['seriedocu'])
          ->setCellValue('D' . $ini, (empty($movimiento['numdocu'])) ? '  ' : $movimiento['numdocu'])
          ->setCellValue('E' . $ini, $mov_numero)
          ->setCellValue('F' . $ini, $movimiento['tran_codigo'])
          ->setCellValue('G' . $ini, $mov_cant_entrada)
          ->setCellValue('H' . $ini, $mov_cant_salida)
          ->setCellValue('I' . $ini, $mov_cant_actual);
          }
          $ini++;
          }

          //TOTALES
          if ($tipo == "CONTABLE") {

          $cant_entrada=(empty($articulo['totales']['cant_entrada']))?0:$articulo['totales']['cant_entrada'];
          $cost_entrada=(empty($articulo['totales']['cost_entrada']))?0:$articulo['totales']['cost_entrada'];
          $cant_salida=(empty($articulo['totales']['cant_salida']))?0:$articulo['totales']['cant_salida'];
          $cost_salida=(empty($articulo['totales']['cost_salida']))?0:$articulo['totales']['cost_salida'];
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('G' . $ini, $cant_entrada);
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('I' . $ini, $cost_entrada);
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('J' . $ini, $cant_salida);
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('L' . $ini, $cost_salida);
          $objPHPExcel->getActiveSheet()->getStyle('G'.$ini.':L'.$ini)->getFont()->setBold(true);
          } else {
          $cant_entrada=(empty($articulo['totales']['cant_entrada']))?0:$articulo['totales']['cant_entrada'];
          $cant_salida=(empty($articulo['totales']['cant_salida']))?0:$articulo['totales']['cant_salida'];
          $cantidad_total=(empty($articulo['totales']['cantidad_total']))?0:$articulo['totales']['cantidad_total'];
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('G' . $ini, $cant_entrada);
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('H' . $ini, $cant_salida);
          $objPHPExcel->setActiveSheetIndex($hoja)
          ->setCellValue('I' . $ini, $cantidad_total);
          $objPHPExcel->getActiveSheet()->getStyle('G'.$ini.':I'.$ini)->getFont()->setBold(true);
          }
          $ini++;
          }
          $hoja++;
          }


          // Rename worksheet
          $objPHPExcel->getActiveSheet()->setTitle('Excel');


          $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

          // Set active sheet index to the first sheet, so Excel opens this as the first sheet
          $objPHPExcel->setActiveSheetIndex(0);
          $name_kar="";
          if ($tipo == "CONTABLE") {
          $name_kar="Inv.Per_valorizado.xls";
          }else{
          $name_kar="Inv.Per_fisico.xls";
          } */

// Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Archivo.xls"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;





        //----------------------------
        //----------------------
    }

}

