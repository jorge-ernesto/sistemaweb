<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();

//INCLUDES PARA OBTENER DATOS DE m_consumo_vales.php
date_default_timezone_set('UTC');

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('/sistemaweb/ventas_clientes/facturacion/m_ticketspos.php');
//CERRAR INCLUDES PARA OBTENER DATOS DE m_consumo_vales.php

include_once('../include/Classes/PHPExcel.php');

$resultado_postrans = $_SESSION['info'];
$modo               = $_SESSION['info_']['modo'];
$iYear              = $_SESSION['info_']['iYear'];
$iMonth             = $_SESSION['info_']['iMonth'];

// echo "<pre>";
// print_r($resultado_postrans);
// echo "</pre>";
// die();

reporteExcelPersonalizado($resultado_postrans, $modo, $iYear, $iMonth);

function reporteExcelPersonalizado($resultado_postrans, $modo, $iYear, $iMonth) {
    $array_index_global = array();
    $index_global = 1;
    $conta_tm = 1;
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    date_default_timezone_set('Europe/London');

    if (PHP_SAPI == 'cli')
        die('This example should only be run from a Web Browser');

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

    $cabecera = array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('argb' => 'FFCCFFCC')
        ),
        'borders' => array(
            'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
        )
    );
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(3);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(8);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(19);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(11);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(11);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(3);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(3);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(14);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(3);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(3);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(5);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(12);

    $objPHPExcel->setActiveSheetIndex(0);
    $hoja = 0;

    $objPHPExcel->getActiveSheet()->freezePane('A6');
    $bucle = 7;

    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(20);

    $objPHPExcel->setActiveSheetIndex($hoja)
    ->setCellValue('A4', 'TM')
    ->setCellValue('B4', "TD ")
    ->setCellValue('C4', "#Ticket")
    ->setCellValue('D4', "Numero ")
    ->setCellValue('E4', "Fecha")
    ->setCellValue('F4', "Dia")
    ->setCellValue('G4', 'Turno')
    ->setCellValue('H4', 'Descripcion')
    ->setCellValue('I4', 'Cantidad')
    ->setCellValue('J4', 'Precio')
    ->setCellValue('K4', 'IGV  ')
    ->setCellValue('L4', 'Importe')
    ->setCellValue('M4', 'Tarjeta')
    ->setCellValue('N4', 'Odometro')
    ->setCellValue('O4', 'Placa')
    ->setCellValue('P4', 'Cod. Cli.')
    ->setCellValue('Q4', 'Usuario')
    ->setCellValue('R4', 'Caja')
    ->setCellValue('S4', 'Lado')
    ->setCellValue('T4', 'Bonus')    
    ->setCellValue('U4', 'RUC')
    ->setCellValue('V4', 'Razon Social')
    ->setCellValue('W4', 'Puntos Bonus')
    ->setCellValue('X4', 'Fecha Extorno')
    ->setCellValue('Y4', 'Documento Extorno')
    ->setCellValue('Z4', 'Fecha Nuevo')
    ->setCellValue('AA4', 'Documento Nuevo')
    ->setCellValue('AB4', 'Trabajador')
    ;

    $iCountPostrans = count($resultado_postrans);

    $modelTicketPos = new TicketsPosModel();
    $tabla = "pos_transtmp";
    if ($modo == "historico")
        $tabla = pg_escape_string("pos_trans" . $iYear . $iMonth);

    if ($iCountPostrans > 0) {
        foreach ($resultado_postrans as $valor => $fila) {
            if (!empty($fila['tm'])) {
                $usr="";
                if(isset($fila['usr']))
                    $usr=$fila['usr'];

                $objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('A' . $bucle, $fila['tm'])
                ->setCellValue('B' . $bucle, $fila['td'])
                ->setCellValue('C' . $bucle, $fila['trans'])
                ->setCellValue('D' . $bucle, $fila['feserie'] . ' -  ' . $fila['fenumero'])
                ->setCellValue('E' . $bucle, $fila['fecha'])
                ->setCellValue('F' . $bucle, $fila['dia'])
                ->setCellValue('G' . $bucle, $fila['turno'])
                ->setCellValue('H' . $bucle, $fila['art_descripcion'])
                ->setCellValue('I' . $bucle, $fila['cantidad'])
                ->setCellValue('J' . $bucle, $fila['precio'])
                ->setCellValue('K' . $bucle, $fila['igv'])
                ->setCellValue('L' . $bucle, $fila['importe'])
                ->setCellValue('M' . $bucle, $fila['tarjeta'])
                ->setCellValue('N' . $bucle, $fila['odometro'])
                ->setCellValue('O' . $bucle, $fila['placa'])
                ->setCellValue('P' . $bucle, $fila['codcli'])
                ->setCellValue('Q' . $bucle, $fila['chofer'])
                ->setCellValue('R' . $bucle, $fila['caja'])
                ->setCellValue('S' . $bucle, $fila['pump'])
                ->setCellValue('T' . $bucle, $fila['bonus'])
                ->setCellValue('U' . $bucle, $fila['ruc'])
                ->setCellValue('V' . $bucle, $fila['razsocial'])
                ->setCellValue('W' . $bucle, floor($fila['puntos']))
                ;
                
                //DOCUMENTO ORIGINAL
                $dFechaReferencia = "";
                $sSerieNumeroReferencia = "";
                if ( $fila['rendi_gln'] != "" ) {
                    $arrData = array(
                        "sNombreTabla" => $tabla,
                        "sCodigoAlmacen" => $fila['almacen'],
                        "sCaja" => $fila['caja'],
                        "sTipoDocumento" => $fila['td'],
                        "fIDTrans" => $fila['rendi_gln'],
                        "iNumeroDocumentoIdentidad" => $fila['ruc'],
                    );
                    $arrResponseModel = $modelTicketPos->verify_reference_sales_invoice_document($arrData);
                    $dFechaReferencia = "";
                    $sSerieNumeroReferencia = "";
                    if ($arrResponseModel["sStatus"] == "success") {
                        $dFechaReferencia = $arrResponseModel["arrDataModel"]["fecha"];
                        $sSerieNumeroReferencia = $arrResponseModel["arrDataModel"]["usr"];
                    }
                }

                //DOCUMENTO RESULTANTE
                $dFechaReferencia_resultante = "";
                $sSerieNumeroReferencia_resultante = "";
                if ( $fila['rendi_acu'] != "" ) {
                    $arrData = array(
                        "sNombreTabla" => $tabla,
                        "sCodigoAlmacen" => $fila['almacen'],
                        "sCaja" => $fila['caja'],
                        "sTipoDocumento" => $fila['td'],
                        "fIDTrans" => $fila['rendi_acu'],
                        "iNumeroDocumentoIdentidad" => $fila['ruc'],
                    );
                    $arrResponseModel = $modelTicketPos->verify_reference_sales_invoice_document_result($arrData);
                    $dFechaReferencia_resultante = "";
                    $sSerieNumeroReferencia_resultante = "";
                    if ($arrResponseModel["sStatus"] == "success") {
                        $dFechaReferencia_resultante = $arrResponseModel["arrDataModel"]["fecha"];
                        $sSerieNumeroReferencia_resultante = $arrResponseModel["arrDataModel"]["usr"];
                    }
                }

                $objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('X' . $bucle, $dFechaReferencia)
                ->setCellValue('Y' . $bucle, $sSerieNumeroReferencia)
                ->setCellValue('Z' . $bucle, $dFechaReferencia_resultante)
                ->setCellValue('AA' . $bucle, $sSerieNumeroReferencia_resultante)
                ->setCellValue('AB' . $bucle, $fila['trabajador'])
                ;
                $bucle++;
            }
        }
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="DATA_INTERFAZ_ERP.xls"');
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