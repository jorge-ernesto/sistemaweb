<?php
ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();
include_once('../include/Classes/PHPExcel.php');

$resultado_postrans = $_SESSION['info'];

$biincre = $_SESSION['biincre'];
$igvincre = $_SESSION['igvincre'];
$totincre = $_SESSION['totincre'];
$arrParamsPOST = $_SESSION['arrParamsPOST_excel'];

/*** Agregado 2020-02-24 ***/
// echo "<script>console.log('" . json_encode($resultado_postrans) . "')</script>";

// foreach($resultado_postrans['ticket_tmp'] as $key=>$value){
// 	$resultado_postrans['ticket_tmp'][$key]['refser_backup'] = $resultado_postrans['manual'][$key]['refser'];
// 	$resultado_postrans['ticket_tmp'][$key]['refnum_backup'] = $resultado_postrans['manual'][$key]['refnum'];
// }
// foreach($resultado_postrans['ticket'] as $key=>$value){
// 	$resultado_postrans['ticket'][$key]['refser_backup'] = $resultado_postrans['manual'][$key]['refser'];
// 	$resultado_postrans['ticket'][$key]['refnum_backup'] = $resultado_postrans['manual'][$key]['refnum'];
// }
// foreach($resultado_postrans['manual'] as $key=>$value){
// 	$resultado_postrans['manual'][$key]['refser_backup'] = $resultado_postrans['manual'][$key]['refser'];
// 	$resultado_postrans['manual'][$key]['refnum_backup'] = $resultado_postrans['manual'][$key]['refnum'];
// }

// echo "<pre>";
// print_r($resultado_postrans);
// print_r($biincre);
// print_r($igvincre);
// print_r($totincre);
// echo "</pre>";
// die();
/***/

reporteExcelPersonalizado($resultado_postrans, $biincre, $igvincre, $totincre, $arrParamsPOST);

function reporteExcelPersonalizado($resultado_postrans, $biincre, $igvincre, $totincre, $arrParamsPOST) {
	$array_index_global = array();
	$index_global = 1;
	$conta_tm = 1;
	error_reporting(E_ALL);
	date_default_timezone_set('Europe/London');

	if (PHP_SAPI == 'cli')
    	die('This example should only be run from a Web Browser');

   	$objPHPExcel = new PHPExcel();

   	$objPHPExcel->getProperties()->setCreator("opensoft")
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

	// Miscellaneous glyphs, UTF-8
    $objPHPExcel->setActiveSheetIndex(0);
    $hoja = 0;

	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->mergeCells('A1:S1');
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(40);

    $objPHPExcel->getActiveSheet()->freezePane('A4');
    $bucle = 4;

    $objPHPExcel->setActiveSheetIndex($hoja)
    ->setCellValue('A1', 'Registro de Venta Detallada')
	->setCellValue('A3', 'Numero de Registro')
	->setCellValue('B3', "Fecha Emision ")
	->setCellValue('C3', "Fecha Vencimiento")
	->setCellValue('D3', "Tipo ")
	->setCellValue('E3', 'Serie')
	->setCellValue('F3', 'Numero')
	->setCellValue('G3', 'Tipo Documento')
	->setCellValue('H3', 'Numero Documento  ')
	->setCellValue('I3', 'Razon social')
	->setCellValue('J3', 'Base imponible')
	->setCellValue('K3', 'IGV')
	->setCellValue('L3', 'ICBPER') //JEL
	->setCellValue('M3', 'Exonerada')
	->setCellValue('N3', 'Inafecto')
	->setCellValue('O3', 'Importe Total')
	->setCellValue('P3', 'Tipo Cambio')
	->setCellValue('Q3', 'Ref. Fecha Emision')
	->setCellValue('R3', 'Ref. Tipo')
	->setCellValue('S3', 'Ref. Serie')
	->setCellValue('T3', 'Ref. Numero');

	// ->setCellValue('K3', 'IGV')
	// ->setCellValue('L3', 'Exonerada')
	// ->setCellValue('M3', 'Inafecto')
	// ->setCellValue('N3', 'Importe Total')
	// ->setCellValue('O3', 'Tipo Cambio')
	// ->setCellValue('P3', 'Ref. Fecha Emision')
	// ->setCellValue('Q3', 'Ref. Tipo')
	// ->setCellValue('R3', 'Ref. Serie')
	// ->setCellValue('S3', 'Ref. Numero');

	$sTipo="";
	$sSerie="";
	$sNumero="";
	$dFecha="";

	$iCountPostrans = count($resultado_postrans);

	//Para verificar la referencia en tickets extornados
	if( $_SESSION['sTipoVistaReporte'] == "SU" ){//SU = Sunat
		$iCountPostransTicketTMP = count($resultado_postrans['ticket_tmp']);
		$arrTicketTMP = 'ticket_tmp';
	} else if ( $_SESSION['sTipoVistaReporte'] == "N" ){//N = Detallado
		$iCountPostransTicketTMP = count($resultado_postrans['ticket']);
		$arrTicketTMP = 'ticket';
	}

	if ($iCountPostrans > 0) {
		$iCountPostransTicket = count($resultado_postrans['ticket']);
        for ($i = 0; $i < $iCountPostransTicket; $i++) {
        	if (!empty($resultado_postrans['ticket'][$i])) {
				$sTipo="";
				$sSerie="";
				$sNumero="";
				$dFecha="";
        		if ( $resultado_postrans['ticket'][$i]['rendi_gln'] != "" && $resultado_postrans['ticket'][$i]['tipo'] == "07" ){
        			$fIdTransReferencia = (float)$resultado_postrans['ticket'][$i]['rendi_gln'];
        			$iIdCajaReferencia = (int)$resultado_postrans['ticket'][$i]['caja'];
					//Busco en todo el arreglo de SOLO TICKETS - Tener en cuenta como el extorno es dentro del mismo turno, no tengo que buscar historicamente, caso contrario con los documentos manuales de oficina
        			for ($y = 0; $y < $iCountPostransTicketTMP; $y++) {
        				$fIdTransOrigen = (float)$resultado_postrans[$arrTicketTMP][$y]['id_trans'];
        				$iIdCajaOrigen = (int)$resultado_postrans[$arrTicketTMP][$y]['caja'];
        				if ( $fIdTransReferencia == $fIdTransOrigen && $iIdCajaReferencia == $iIdCajaOrigen ) {
							$sTipo=$resultado_postrans[$arrTicketTMP][$y]['tipo'];
							$sSerie=$resultado_postrans[$arrTicketTMP][$y]['serie'];
							$sNumero=$resultado_postrans[$arrTicketTMP][$y]['numero'];
							$dFecha=$resultado_postrans[$arrTicketTMP][$y]['emision'];
        				}
        			}
		        }

				/* Obtener DATOS REFERENCIA */
				if( $resultado_postrans['ticket'][$i]['rendi_gln'] != "" ) {
					$arrData = array(
						//Datos para buscar registros
						"sNombreTabla" => $arrParamsPOST['sTablePostransYM'],
						"sCodigoAlmacen" => $arrParamsPOST['sCodigoAlmacen'],
						//Datos para buscar documento origen
						"sCaja" => $resultado_postrans['ticket'][$i]['caja'],
						"sTipoDocumento" => $resultado_postrans['ticket'][$i]['td'],
						"fIDTrans" => $resultado_postrans['ticket'][$i]['rendi_gln'],
						"iNumeroDocumentoIdentidad" => $resultado_postrans['ticket'][$i]['ruc_bd_interno'],
					);					
					$arrResponseModel = verify_reference_sales_invoice_document($arrData);
					// error_log("****** Data Documento Referencia ******");
					// error_log( json_encode( $arrData ) );
					// error_log( json_encode( $arrResponseModel ) );
					$sSerieNumeroReferencia = "";
					if ($arrResponseModel["sStatus"] == "success") {
						$resultado_postrans['ticket'][$i]['reffec'] = $arrResponseModel["arrDataModel"]["fecharef"];
						$resultado_postrans['ticket'][$i]['reftip'] = $arrResponseModel["arrDataModel"]["tiporef"];
						$resultado_postrans['ticket'][$i]['refser'] = $arrResponseModel["arrDataModel"]["serieref"];
						$resultado_postrans['ticket'][$i]['refnum'] = $arrResponseModel["arrDataModel"]["numref"];
					}
				}

				$serie 	= (is_null($resultado_postrans['ticket'][$i]['serie'])) ? "-" : $resultado_postrans['ticket'][$i]['serie'];
				
				$reftip = (is_null($sTipo)) ? "-" : $sTipo;
				$refser = (is_null($sSerie)) ? "-" : $sSerie;
				$refnum = (is_null($sNumero)) ? "-" : $sNumero;
				
				if($dFecha!=null){
				    $fecharef=explode("-", $dFecha);
				    $reffec=$fecharef[2]."/".$fecharef[1]."/".$fecharef[0];
				}else
				    $reffec = (is_null($dFecha)) ? "-" : $dFecha;

				$tipo_cambio = (is_null($resultado_postrans['ticket'][$i]['tipocambio'])) ? "0.00" : $resultado_postrans['ticket'][$i]['tipocambio'];

				if ($i == 0 && (abs($biincre) > 0 || abs($igvincre) > 0 || abs($totincre) > 0)){
					$resultado_postrans['ticket'][$i]['imponible'] = $resultado_postrans['ticket'][$i]['imponible'] + $biincre;
					$resultado_postrans['ticket'][$i]['igv'] = $resultado_postrans['ticket'][$i]['igv'] + $igvincre;
					$resultado_postrans['ticket'][$i]['importe'] = $resultado_postrans['ticket'][$i]['importe'] + $totincre;
				}

				$fechaemi = explode("-", $resultado_postrans['ticket'][$i]['emision']);
				$fechae = $fechaemi[2]."/".$fechaemi[1]."/".$fechaemi[0];

				$fechaven = explode("-", $resultado_postrans['ticket'][$i]['vencimiento']);
				$fechaev = $fechaven[2]."/".$fechaven[1]."/".$fechaven[0];

				$objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('A' . $bucle, $resultado_postrans['ticket'][$i]['trans'])
                ->setCellValue('B' . $bucle, $fechae)
                ->setCellValue('C' . $bucle, $fechaev);

                $sTipoDocumento = $resultado_postrans['ticket'][$i]['tipo'];
				$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $bucle, $sTipoDocumento, PHPExcel_Cell_DataType::TYPE_STRING);

				$objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('E' . $bucle, $serie);

                $sNumeroDocumento = $resultado_postrans['ticket'][$i]['numero'];
				$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(5, $bucle, $sNumeroDocumento, PHPExcel_Cell_DataType::TYPE_STRING);

                $objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('G' . $bucle, $resultado_postrans['ticket'][$i]['tipodi']);

                $nu_documento = $resultado_postrans['ticket'][$i]['ruc'];
				$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(7, $bucle, $nu_documento, PHPExcel_Cell_DataType::TYPE_STRING);

    			$objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('I' . $bucle, $resultado_postrans['ticket'][$i]['cliente'])
                ->setCellValue('J' . $bucle, $resultado_postrans['ticket'][$i]['imponible'])
				->setCellValue('K' . $bucle, $resultado_postrans['ticket'][$i]['igv'])
				->setCellValue('L' . $bucle, $resultado_postrans['ticket'][$i]['balance'])
                ->setCellValue('M' . $bucle, $resultado_postrans['ticket'][$i]['exonerada'])
                ->setCellValue('N' . $bucle, $resultado_postrans['ticket'][$i]['inafecto'])
                ->setCellValue('O' . $bucle, $resultado_postrans['ticket'][$i]['importe'])
                ->setCellValue('P' . $bucle, $tipo_cambio)
                ->setCellValue('Q' . $bucle, $reffec)
                ->setCellValue('R' . $bucle, $resultado_postrans['ticket'][$i]['reftip'])
				->setCellValue('S' . $bucle, $resultado_postrans['ticket'][$i]['refser'])
				->setCellValue('T' . $bucle, $resultado_postrans['ticket'][$i]['refnum']);

				$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(19, $bucle, $refnum, PHPExcel_Cell_DataType::TYPE_STRING);

				$bucle++;
        	}
		}

		// DOCUMENTOS MANUALES
		if (!empty($resultado_postrans['manual'])) {
			for ($i = 0; $i < count($resultado_postrans['manual']); $i++) {
				if (!empty($resultado_postrans['manual'][$i])) {
	            	$serie = (is_null($resultado_postrans['manual'][$i]['serie'])) ? "-" : $resultado_postrans['manual'][$i]['serie'];
					$fechaemi = explode("-", $resultado_postrans['manual'][$i]['emision']);
					$fechaemision = $fechaemi[2]."/".$fechaemi[1]."/".$fechaemi[0];
					$fechaven = explode("-", $resultado_postrans['manual'][$i]['vencimiento']);
					$fechavencimiento = $fechaven[2]."/".$fechaven[1]."/".$fechaven[0];

					$refnum = (is_null($resultado_postrans['manual'][$i]['refnum'])) ? "-" : $resultado_postrans['manual'][$i]['refnum'];
			    	$tipo_cambio = (is_null($resultado_postrans['manual'][$i]['tipocambio'])) ? "0.00" : $resultado_postrans['manual'][$i]['tipocambio'];

			    	$objPHPExcel->setActiveSheetIndex($hoja)
                	->setCellValue('A' . $bucle, $resultado_postrans['manual'][$i]['trans'])
                	->setCellValue('B' . $bucle, $fechaemision)
                	->setCellValue('C' . $bucle, $fechavencimiento);

	                $sTipoDocumento = $resultado_postrans['manual'][$i]['tipo'];//D = 3
					$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $bucle, $sTipoDocumento, PHPExcel_Cell_DataType::TYPE_STRING);

			    	$objPHPExcel->setActiveSheetIndex($hoja)
                	->setCellValue('E' . $bucle, $serie);

	                $sTipoDocumento = $resultado_postrans['manual'][$i]['numero'];//F = 5
					$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(5, $bucle, $sTipoDocumento, PHPExcel_Cell_DataType::TYPE_STRING);

                	$objPHPExcel->setActiveSheetIndex($hoja)
               		->setCellValue('G' . $bucle, $resultado_postrans['manual'][$i]['tipodi']);
               		
               		$nu_documento1 = $resultado_postrans['manual'][$i]['ruc'];
					$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(7, $bucle, $nu_documento1, PHPExcel_Cell_DataType::TYPE_STRING);
					
					$dReferencia='';
					if ( $resultado_postrans['manual'][$i]['reffec'] != '' ) {
						$dReferencia = explode("-", $resultado_postrans['manual'][$i]['reffec']);
						$dReferencia = $dReferencia[2]."/".$dReferencia[1]."/".$dReferencia[0];
					}

					$objPHPExcel->setActiveSheetIndex($hoja)
                	->setCellValue('I' . $bucle, $resultado_postrans['manual'][$i]['cliente'])
                	->setCellValue('J' . $bucle, $resultado_postrans['manual'][$i]['imponible'])
					->setCellValue('K' . $bucle, $resultado_postrans['manual'][$i]['igv'])
					->setCellValue('L' . $bucle, $resultado_postrans['manual'][$i]['balance'])
                	->setCellValue('M' . $bucle, $resultado_postrans['manual'][$i]['exonerada'])
                	->setCellValue('N' . $bucle, $resultado_postrans['manual'][$i]['inafecto'])
                	->setCellValue('O' . $bucle, $resultado_postrans['manual'][$i]['importe'])
                	->setCellValue('P' . $bucle, $tipo_cambio)
                	->setCellValue('Q' . $bucle, $dReferencia)
                	->setCellValue('R' . $bucle, $resultado_postrans['manual'][$i]['reftip'])
					->setCellValue('S' . $bucle, $resultado_postrans['manual'][$i]['refser'])
					->setCellValue('T' . $bucle, $resultado_postrans['manual'][$i]['refnum']);

					$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(19, $bucle, $refnum, PHPExcel_Cell_DataType::TYPE_STRING);

	            	$bucle++;
		        }
		    }
		}// /. Fin de documentos manuales
	}

	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="VENTA_DETALLADA.xls"');
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

function verify_reference_sales_invoice_document($arrCond){
	error_log(json_encode($arrCond));

	$conn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
	pg_set_client_encoding($conn, "utf8");		

	$nombre_tabla = $arrCond['sNombreTabla'];
	$cond_codigo_almacen = $arrCond['sCodigoAlmacen'];
	$cond_caja = $arrCond['sCaja'];
	$cond_tipo_documento = $arrCond['sTipoDocumento'];
	$cond_trans = $arrCond['fIDTrans'];

	$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'No existe documento de referencia');

	$sql = "
SELECT
usr,
CASE
WHEN tm='V' AND td='B' THEN '03'
WHEN tm='V' AND td='F' THEN '01'
ELSE '07'
END AS tiporef,
TO_CHAR(fecha, 'DD/MM/YYYY') AS fecharef,
SUBSTR(TRIM(usr), 0, 5) AS serieref,
SUBSTR(TRIM(usr), 6) AS numref
FROM
" . $nombre_tabla . "
WHERE
es = '" . $cond_codigo_almacen . "'
AND caja = '" . $cond_caja . "'
AND td = '" . $cond_tipo_documento . "'
AND trans = " . $cond_trans . "
AND tm = 'V'
AND grupo != 'D'
LIMIT 1;
	";
	error_log(json_encode($sql));

	$result = pg_query($conn, $sql);
	$fila = pg_fetch_assoc($result);

	if($fila){
		$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Documento de playa encontrado', 'arrDataModel' => $fila);
	}
	error_log(json_encode($arrResponse));
	return $arrResponse;
}