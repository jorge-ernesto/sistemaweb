<?php
ini_set('memory_limit', '-1');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();
include_once('../include/Classes/PHPExcel.php');

$resultado_postrans = $_SESSION['info']; //Toda la información formateada esta aqui

$biincre = $_SESSION['biincre'];
$igvincre = $_SESSION['igvincre'];
$totincre = $_SESSION['totincre'];
$arrParamsPOST = $_SESSION['arrParamsPOST_excel'];
$desde = $_SESSION['desde'];
$hasta = $_SESSION['hasta'];

/*** Agregado 2020-02-24 ***/
// echo "<script>console.log('" . json_encode($resultado_postrans) . "')</script>";

// foreach($resultado_postrans['ticket_tmp'] as $key=>$value){
// 	$resultado_postrans['ticket_tmp'][$key]['refser_backup'] = $resultado_postrans['manual_completado'][$key]['refser'];
// 	$resultado_postrans['ticket_tmp'][$key]['refnum_backup'] = $resultado_postrans['manual_completado'][$key]['refnum'];
// }
// foreach($resultado_postrans['ticket'] as $key=>$value){
// 	$resultado_postrans['ticket'][$key]['refser_backup'] = $resultado_postrans['manual_completado'][$key]['refser'];
// 	$resultado_postrans['ticket'][$key]['refnum_backup'] = $resultado_postrans['manual_completado'][$key]['refnum'];
// }
// foreach($resultado_postrans['manual_completado'] as $key=>$value){
// 	$resultado_postrans['manual_completado'][$key]['refser_backup'] = $resultado_postrans['manual_completado'][$key]['refser'];
// 	$resultado_postrans['manual_completado'][$key]['refnum_backup'] = $resultado_postrans['manual_completado'][$key]['refnum'];
// }

// echo "<pre>";
// print_r($resultado_postrans);
// print_r($biincre);
// print_r($igvincre);
// print_r($totincre);
// echo "</pre>";
// die();
/***/

reporteExcelPersonalizado($resultado_postrans, $biincre, $igvincre, $totincre, $arrParamsPOST, $desde, $hasta);

function reporteExcelPersonalizado($resultado_postrans, $biincre, $igvincre, $totincre, $arrParamsPOST, $desde, $hasta) {
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

	$hoja = -1;
	if( $_SESSION['sTipoVistaReporte'] != "R" ) {
		// Miscellaneous glyphs, UTF-8
		$hoja++;
		$objPHPExcel->createSheet($hoja);//creamos la pestaña
		$objPHPExcel->setActiveSheetIndex($hoja);
		$objPHPExcel->getActiveSheet($hoja)->setTitle("REGISTRO DE VENTAS DETALLADA");

		$objPHPExcel->getActiveSheet($hoja)->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet($hoja)->mergeCells('A1:S1');
		$objPHPExcel->getActiveSheet($hoja)->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('A')->setWidth(20);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('B')->setWidth(15);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('C')->setWidth(15);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('F')->setWidth(20);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('H')->setWidth(25);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('I')->setWidth(40);

		$objPHPExcel->getActiveSheet($hoja)->freezePane('A4');
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
							"sNombreTabla_Ant" => $arrParamsPOST['sTablePostransYM_Ant'],
							"sNombreTabla_Des" => $arrParamsPOST['sTablePostransYM_Des'],
							"sStatusTabla_Ant" => $arrParamsPOST['sStatusPostransYM_Ant'],
							"sStatusTabla_Des" => $arrParamsPOST['sStatusPostransYM_Des'],
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
					$objPHPExcel->getActiveSheet($hoja)->setCellValueExplicitByColumnAndRow(3, $bucle, $sTipoDocumento, PHPExcel_Cell_DataType::TYPE_STRING);

					$objPHPExcel->setActiveSheetIndex($hoja)
					->setCellValue('E' . $bucle, $serie);

					$sNumeroDocumento = $resultado_postrans['ticket'][$i]['numero'];
					$objPHPExcel->getActiveSheet($hoja)->setCellValueExplicitByColumnAndRow(5, $bucle, $sNumeroDocumento, PHPExcel_Cell_DataType::TYPE_STRING);

					$objPHPExcel->setActiveSheetIndex($hoja)
					->setCellValue('G' . $bucle, $resultado_postrans['ticket'][$i]['tipodi']);

					$nu_documento = $resultado_postrans['ticket'][$i]['ruc'];
					$objPHPExcel->getActiveSheet($hoja)->setCellValueExplicitByColumnAndRow(7, $bucle, $nu_documento, PHPExcel_Cell_DataType::TYPE_STRING);

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

					$objPHPExcel->getActiveSheet($hoja)->setCellValueExplicitByColumnAndRow(19, $bucle, $refnum, PHPExcel_Cell_DataType::TYPE_STRING);

					$bucle++;
				}
			}

			// DOCUMENTOS MANUALES
			if (!empty($resultado_postrans['manual_completado'])) {
				// for ($i = 0; $i < count($resultado_postrans['manual_completado']); $i++) {
				// 	if (!empty($resultado_postrans['manual_completado'][$i])) {
				// 	}
				// }

				foreach ($resultado_postrans['manual_completado'] as $key => $manual_completado) {					
					if (is_int($key)) {
						$i = $key;
						if (!empty($resultado_postrans['manual_completado'][$i])) {
							$serie = (is_null($resultado_postrans['manual_completado'][$i]['serie'])) ? "-" : $resultado_postrans['manual_completado'][$i]['serie'];
							$fechaemi = explode("-", $resultado_postrans['manual_completado'][$i]['emision']);
							$fechaemision = $fechaemi[2]."/".$fechaemi[1]."/".$fechaemi[0];
							$fechaven = explode("-", $resultado_postrans['manual_completado'][$i]['vencimiento']);
							$fechavencimiento = $fechaven[2]."/".$fechaven[1]."/".$fechaven[0];

							$refnum = (is_null($resultado_postrans['manual_completado'][$i]['refnum'])) ? "-" : $resultado_postrans['manual_completado'][$i]['refnum'];
							$tipo_cambio = (is_null($resultado_postrans['manual_completado'][$i]['tipocambio'])) ? "0.00" : $resultado_postrans['manual_completado'][$i]['tipocambio'];

							$objPHPExcel->setActiveSheetIndex($hoja)
							->setCellValue('A' . $bucle, $resultado_postrans['manual_completado'][$i]['trans'])
							->setCellValue('B' . $bucle, $fechaemision)
							->setCellValue('C' . $bucle, $fechavencimiento);

							$sTipoDocumento = $resultado_postrans['manual_completado'][$i]['tipo'];//D = 3
							$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $bucle, $sTipoDocumento, PHPExcel_Cell_DataType::TYPE_STRING);

							$objPHPExcel->setActiveSheetIndex($hoja)
							->setCellValue('E' . $bucle, $serie);

							$sTipoDocumento = $resultado_postrans['manual_completado'][$i]['numero'];//F = 5
							$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(5, $bucle, $sTipoDocumento, PHPExcel_Cell_DataType::TYPE_STRING);

							$objPHPExcel->setActiveSheetIndex($hoja)
							->setCellValue('G' . $bucle, $resultado_postrans['manual_completado'][$i]['tipodi']);
							
							$nu_documento1 = $resultado_postrans['manual_completado'][$i]['ruc'];
							$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(7, $bucle, $nu_documento1, PHPExcel_Cell_DataType::TYPE_STRING);
							
							$dReferencia='';
							if ( $resultado_postrans['manual_completado'][$i]['reffec'] != '' ) {
								$dReferencia = explode("-", $resultado_postrans['manual_completado'][$i]['reffec']);
								$dReferencia = $dReferencia[2]."/".$dReferencia[1]."/".$dReferencia[0];
							}

							$objPHPExcel->setActiveSheetIndex($hoja)
							->setCellValue('I' . $bucle, $resultado_postrans['manual_completado'][$i]['cliente'])
							->setCellValue('J' . $bucle, $resultado_postrans['manual_completado'][$i]['imponible'])
							->setCellValue('K' . $bucle, $resultado_postrans['manual_completado'][$i]['igv'])
							->setCellValue('L' . $bucle, $resultado_postrans['manual_completado'][$i]['balance'])
							->setCellValue('M' . $bucle, $resultado_postrans['manual_completado'][$i]['exonerada'])
							->setCellValue('N' . $bucle, $resultado_postrans['manual_completado'][$i]['inafecto'])
							->setCellValue('O' . $bucle, $resultado_postrans['manual_completado'][$i]['importe'])
							->setCellValue('P' . $bucle, $tipo_cambio)
							->setCellValue('Q' . $bucle, $dReferencia)
							->setCellValue('R' . $bucle, $resultado_postrans['manual_completado'][$i]['reftip'])
							->setCellValue('S' . $bucle, $resultado_postrans['manual_completado'][$i]['refser'])
							->setCellValue('T' . $bucle, $resultado_postrans['manual_completado'][$i]['refnum']);

							$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(19, $bucle, $refnum, PHPExcel_Cell_DataType::TYPE_STRING);

							$bucle++;
						}
					}
				}
			}// /. Fin de documentos manuales		
		}
	}

	/* Cuadro Resumen */
	$results = $resultado_postrans;

	//TOTAL TICKETS
	$ntickets = count($results['ticket']);
	if ($ntickets > 0) {						
		//TOTAL TICKETS POR BOLETAS
		$total_ticket_boleta = $results['ticket']['tipo']['B'];

		//TOTAL TICKETS POR FACTURAS
		$total_ticket_factura = $results['ticket']['tipo']['F'];

		//TOTAL TICKETS POR NOTA DE CREDITO
		$total_ticket_nc = $results['ticket']['tipo']['A'];

		//TOTAL TICKETS
		$total_ticket = $results['ticket'];
	}

	//TOTAL MANUALES COMPLETADOS
	$nmanuales = count($results['manual_completado']);
	if ($nmanuales > 0) {
		//TOTAL MANUALES POR BOLETA
		$total_manual_completado_boleta = $results['manual_completado']['tipo']['03'];
		
		//TOTAL MANUALES POR FACTURA
		$total_manual_completado_factura = $results['manual_completado']['tipo']['01'];
		
		//TOTAL MANUALES POR NOTA DE CREDITO
		$total_manual_completado_nc = $results['manual_completado']['tipo']['07'];
		
		//TOTAL MANUALES POR NOTA DE DEBITO
		$total_manual_completado_nd = $results['manual_completado']['tipo']['08'];

		//TOTAL MANUALES
		$total_manual_completado['total_imponible'] = $results['manual_completado']['total_imponible'] - abs($results['manual_completado']['nota_credito']['totales_imponible_credito']);
		$total_manual_completado['total_igv']       = $results['manual_completado']['total_igv']       - abs($results['manual_completado']['nota_credito']['totales_igv_credito']);
		$total_manual_completado['total_balance']   = $results['manual_completado']['total_balance'];
		$total_manual_completado['total_exonerada']	= $results['manual_completado']['total_exonerada'] - abs($results['manual_completado']['nota_credito']['totales_exonerada_nc']);
		$total_manual_completado['total_inafecto']  = $results['manual_completado']['total_inafecto']  - abs($results['manual_completado']['nota_credito']['totales_inafecto_nc']);
		$total_manual_completado['total_importe']   = $results['manual_completado']['total_importe']   - abs($results['manual_completado']['nota_credito']['totales_importe_credito']);
	}		

	//TOTAL MANUALES REGISTRADO
	$nmanuales = count($results['manual_registrado']);
	if ($nmanuales > 0) {
		//TOTAL MANUALES POR BOLETA
		$total_manual_registrado_boleta = $results['manual_registrado']['tipo']['03'];
		
		//TOTAL MANUALES POR FACTURA
		$total_manual_registrado_factura = $results['manual_registrado']['tipo']['01'];
		
		//TOTAL MANUALES POR NOTA DE CREDITO
		$total_manual_registrado_nc = $results['manual_registrado']['tipo']['07'];
		
		//TOTAL MANUALES POR NOTA DE DEBITO
		$total_manual_registrado_nd = $results['manual_registrado']['tipo']['08'];

		//TOTAL MANUALES
		$total_manual_registrado['total_imponible'] = $results['manual_registrado']['total_imponible'] - abs($results['manual_registrado']['nota_credito']['totales_imponible_credito']);
		$total_manual_registrado['total_igv']       = $results['manual_registrado']['total_igv']       - abs($results['manual_registrado']['nota_credito']['totales_igv_credito']);
		$total_manual_registrado['total_balance']   = $results['manual_registrado']['total_balance'];
		$total_manual_registrado['total_exonerada']	= $results['manual_registrado']['total_exonerada'] - abs($results['manual_registrado']['nota_credito']['totales_exonerada_nc']);
		$total_manual_registrado['total_inafecto']  = $results['manual_registrado']['total_inafecto']  - abs($results['manual_registrado']['nota_credito']['totales_inafecto_nc']);
		$total_manual_registrado['total_importe']   = $results['manual_registrado']['total_importe']   - abs($results['manual_registrado']['nota_credito']['totales_importe_credito']);
	}

	//TOTAL MANUALES ANULADO
	$nmanuales = count($results['manual_anulado']);
	if ($nmanuales > 0) {
		//TOTAL MANUALES POR BOLETA
		$total_manual_anulado_boleta = $results['manual_anulado']['tipo']['03'];
		
		//TOTAL MANUALES POR FACTURA
		$total_manual_anulado_factura = $results['manual_anulado']['tipo']['01'];
		
		//TOTAL MANUALES POR NOTA DE CREDITO
		$total_manual_anulado_nc = $results['manual_anulado']['tipo']['07'];
		
		//TOTAL MANUALES POR NOTA DE DEBITO
		$total_manual_anulado_nd = $results['manual_anulado']['tipo']['08'];			
		
		//TOTAL MANUALES
		$total_manual_anulado = $results['manual_anulado']['tipo'];
	}

	$hoja++;
	$objPHPExcel->createSheet($hoja);//creamos la pestaña
	$objPHPExcel->setActiveSheetIndex($hoja);//seteamos pestaña
	$objPHPExcel->getActiveSheet($hoja)->setTitle("RESUMEN");

	$objPHPExcel->getActiveSheet($hoja)->getStyle('A1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->mergeCells('A1:G1');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('A')->setWidth(40);
	$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('B')->setWidth(15);
	$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('C')->setWidth(15);
	$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('D')->setWidth(15);
	$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('E')->setWidth(15);
	$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('F')->setWidth(15);
	$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('G')->setWidth(15);

	$objPHPExcel->getActiveSheet($hoja)->getStyle('A')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet($hoja)->getStyle('C')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet($hoja)->getStyle('D')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet($hoja)->getStyle('E')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet($hoja)->getStyle('F')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$objPHPExcel->getActiveSheet($hoja)->getStyle('G')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$bucle = 7;

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A1', 'RESUMEN REGISTRO DE VENTAS');

	//INFORMACION DE CONSULTA
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A3', 'FECHA DE CONSULTA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A4', 'PERIODO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A5', 'USUARIO');
	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B3', date("Y-m-d H:i:s"));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B4', $desde ." al ". $hasta);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B5', $_SESSION['auth_usuario']);

	$objPHPExcel->getActiveSheet($hoja)->getStyle('A3:B3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A4:B4')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A5:B5')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet($hoja)->getStyle('B3')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,));
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B4')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,));
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B5')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,));
	//CERRAR INFORMACION DE CONSULTA


	//TOTAL TICKETS
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'Comprobante Playa');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, 'Imponible');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, 'IGV');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, 'ICBPER');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, 'Exonerada');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, 'Inafecto');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, 'Total');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'BOLETA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_ticket_boleta['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_ticket_boleta['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_ticket_boleta['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_ticket_boleta['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_ticket_boleta['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_ticket_boleta['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'FACTURA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_ticket_factura['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_ticket_factura['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_ticket_factura['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_ticket_factura['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_ticket_factura['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_ticket_factura['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE CREDITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_ticket_nc['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_ticket_nc['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_ticket_nc['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_ticket_nc['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_ticket_nc['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_ticket_nc['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;
	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'TOTAL PLAYA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_ticket['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_ticket['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_ticket['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_ticket['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_ticket['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_ticket['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');	
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;
	$bucle++;		

	//TOTAL MANUALES COMPLETADOS
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'Comprobante Oficina Completados');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, 'Imponible');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, 'IGV');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, 'ICBPER');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, 'Exonerada');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, 'Inafecto');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, 'Total');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'BOLETA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_completado_boleta['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_completado_boleta['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_completado_boleta['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_completado_boleta['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_completado_boleta['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_completado_boleta['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'FACTURA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_completado_factura['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_completado_factura['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_completado_factura['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_completado_factura['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_completado_factura['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_completado_factura['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE CREDITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_completado_nc['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_completado_nc['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_completado_nc['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_completado_nc['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_completado_nc['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_completado_nc['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE DEBITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_completado_nd['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_completado_nd['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_completado_nd['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_completado_nd['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_completado_nd['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_completado_nd['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;
	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'TOTAL OFICINA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_completado['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_completado['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_completado['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_completado['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_completado['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_completado['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');	
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;
	$bucle++;		

	//RESUMEN TOTAL
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'RESUMEN TOTAL');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, 'Imponible');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, 'IGV');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, 'ICBPER');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, 'Exonerada');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, 'Inafecto');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, 'Total');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'BOLETA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)($total_ticket_boleta['total_imponible'] + $total_manual_completado_boleta['total_imponible']));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)($total_ticket_boleta['total_igv']       + $total_manual_completado_boleta['total_igv']      ));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)($total_ticket_boleta['total_balance']   + $total_manual_completado_boleta['total_balance']  ));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)($total_ticket_boleta['total_exonerada'] + $total_manual_completado_boleta['total_exonerada']));	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)($total_ticket_boleta['total_inafecto']  + $total_manual_completado_boleta['total_inafecto'] ));	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)($total_ticket_boleta['total_importe']   + $total_manual_completado_boleta['total_importe']  ));	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'FACTURA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)($total_ticket_factura['total_imponible'] + $total_manual_completado_factura['total_imponible']));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)($total_ticket_factura['total_igv']       + $total_manual_completado_factura['total_igv']      ));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)($total_ticket_factura['total_balance']   + $total_manual_completado_factura['total_balance']  ));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)($total_ticket_factura['total_exonerada'] + $total_manual_completado_factura['total_exonerada']));	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)($total_ticket_factura['total_inafecto']  + $total_manual_completado_factura['total_inafecto'] ));	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)($total_ticket_factura['total_importe']   + $total_manual_completado_factura['total_importe']  ));	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE CREDITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)($total_ticket_nc['total_imponible'] + $total_manual_completado_nc['total_imponible']));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)($total_ticket_nc['total_igv']       + $total_manual_completado_nc['total_igv']      ));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)($total_ticket_nc['total_balance']   + $total_manual_completado_nc['total_balance']  ));
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)($total_ticket_nc['total_exonerada'] + $total_manual_completado_nc['total_exonerada']));	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)($total_ticket_nc['total_inafecto']  + $total_manual_completado_nc['total_inafecto'] ));	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)($total_ticket_nc['total_importe']   + $total_manual_completado_nc['total_importe']  ));	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE DEBITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_completado_nd['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_completado_nd['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_completado_nd['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_completado_nd['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_completado_nd['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_completado_nd['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;
	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'TOTAL GENERAL');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, $total_ticket['total_imponible'] + $total_manual_completado['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, $total_ticket['total_igv']       + $total_manual_completado['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, $total_ticket['total_balance']   + $total_manual_completado['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, $total_ticket['total_exonerada'] + $total_manual_completado['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, $total_ticket['total_inafecto']  + $total_manual_completado['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, $total_ticket['total_importe']   + $total_manual_completado['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');	
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	
	$bucle++;
	$bucle++;	
	
	//TOTAL MANUALES REGISTRADO
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'Documentos Registrados y no enviados');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, 'Imponible');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, 'IGV');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, 'ICBPER');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, 'Exonerada');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, 'Inafecto');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, 'Total');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'BOLETA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_registrado_boleta['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_registrado_boleta['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_registrado_boleta['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_registrado_boleta['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_registrado_boleta['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_registrado_boleta['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'FACTURA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_registrado_factura['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_registrado_factura['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_registrado_factura['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_registrado_factura['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_registrado_factura['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_registrado_factura['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE CREDITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_registrado_nc['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_registrado_nc['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_registrado_nc['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_registrado_nc['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_registrado_nc['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_registrado_nc['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE DEBITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_registrado_nd['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_registrado_nd['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_registrado_nd['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_registrado_nd['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_registrado_nd['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_registrado_nd['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;
	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'TOTAL');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_registrado['total_imponible']);
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, (float)$total_manual_registrado['total_igv']      );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('D'.$bucle, (float)$total_manual_registrado['total_balance']  );
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('E'.$bucle, (float)$total_manual_registrado['total_exonerada']);	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('F'.$bucle, (float)$total_manual_registrado['total_inafecto'] );	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('G'.$bucle, (float)$total_manual_registrado['total_importe']  );	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');	
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	//TOTAL MANUALES REGISTRADOS - DETALLE
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'Detalle Documentos Registrados y no enviados');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, 'Serie - Numero');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, 'Estado');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	foreach ($results['manual_registrado'] as $key => $value) {
		if (is_int($key)) {
			$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'DOCUMENTO');
			$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, $value['serie'] . '-' . $value['numero']);		
			$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('C'.$bucle, $value['statusname']);	
			$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));	
			$bucle++;	
		}		
	}
	$bucle++;
	$bucle++;

	//TOTAL MANUALES ANULADO
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'Documentos Anulados');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, 'Cantidad');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'BOLETA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_anulado_boleta['cantidad']);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'FACTURA');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_anulado_factura['cantidad']);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;	

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE CREDITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_anulado_nc['cantidad']);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;

	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'NOTAS DE DEBITO');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_anulado_nd['cantidad']);	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;
	
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('A'.$bucle, 'TOTAL');
	$objPHPExcel->setActiveSheetIndex($hoja)->setCellValue('B'.$bucle, (float)$total_manual_anulado['cantidad']);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$bucle.':G'.$bucle)->getNumberFormat()->setFormatCode('0.00');	
	$objPHPExcel->getActiveSheet($hoja)->getStyle('A'.$bucle.':G'.$bucle)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet($hoja)->getStyle('B'.$bucle.':G'.$bucle)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$bucle++;
	$bucle++;
	/* Cerrar Cuadro Resumen */

	// header('Content-Type: application/vnd.ms-excel');
	// header('Content-Disposition: attachment;filename="VENTA_DETALLADA.xls"');
	// header('Cache-Control: max-age=0');
	// header('Cache-Control: max-age=1');
	// header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	// header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
	// header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	// header('Pragma: public'); // HTTP/1.0

	// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
   	// $objWriter->save('php://output');

   	// exit;

	unset( $_SESSION['info'] );
	unset( $_SESSION['biincre'] );
	unset( $_SESSION['igvincre'] );
	unset( $_SESSION['totincre'] );
	unset( $_SESSION['arrParamsPOST_excel'] );
	unset( $_SESSION['desde'] );
	unset( $_SESSION['hasta'] );

   	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
   	$objWriter->save('D:/PORTABLES/laragon/www/sistemaweb/ventas_clientes/reportes/excel/VENTA_DETALLADA.xls'); //
	   
	echo "<script>
			var link = document.createElement('a');
			link.href = '/sistemaweb/ventas_clientes/reportes/excel/VENTA_DETALLADA.xls';
			link.download = 'VENTA_DETALLADA.xls';
			link.dispatchEvent(new MouseEvent('click'));		
		</script>";
}

function verify_reference_sales_invoice_document($arrCond){
	error_log(json_encode($arrCond));

	$conn = pg_connect("host=localhost port=5432 dbname=integrado user=postgres") or die('No se ha podido conectar: ' . pg_last_error());
	pg_set_client_encoding($conn, "utf8");		

	$nombre_tabla = $arrCond['sNombreTabla'];
	$nombre_tabla_ant = $arrCond['sNombreTabla_Ant'];
	$nombre_tabla_des = $arrCond['sNombreTabla_Des'];
	$status_tabla_ant = $arrCond['sStatusTabla_Ant'];
	$status_tabla_des = $arrCond['sStatusTabla_Des'];
	$cond_codigo_almacen = $arrCond['sCodigoAlmacen'];
	$cond_caja = $arrCond['sCaja'];
	$cond_tipo_documento = $arrCond['sTipoDocumento'];
	$cond_trans = $arrCond['fIDTrans'];

	$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'No existe documento de referencia');

	$sql = "";
	if ( $status_tabla_ant == true ) {
		$sql .= "
			(SELECT
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
				" . $nombre_tabla_ant . "
			WHERE
				es = '" . $cond_codigo_almacen . "'
				AND caja = '" . $cond_caja . "'
				AND td = '" . $cond_tipo_documento . "'
				AND trans = " . $cond_trans . "
				AND tm = 'V'
				AND grupo != 'D')
				
			UNION ALL
		";
	}

	$sql .= "
		(SELECT
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
			AND grupo != 'D')
	";

	if ( $status_tabla_des == true ) {
		$sql .= "
			UNION ALL

			(SELECT
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
				" . $nombre_tabla_des . "
			WHERE
				es = '" . $cond_codigo_almacen . "'
				AND caja = '" . $cond_caja . "'
				AND td = '" . $cond_tipo_documento . "'
				AND trans = " . $cond_trans . "
				AND tm = 'V'
				AND grupo != 'D')
		";
	}

	$sql .= "LIMIT 1;";

	error_log("trans: " . $cond_trans);
	error_log("sql: " . $sql);

	$result = pg_query($conn, $sql);
	$fila = pg_fetch_assoc($result);

	if($fila){
		$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Documento de playa encontrado', 'arrDataModel' => $fila);
	}
	error_log(json_encode($arrResponse));
	return $arrResponse;
}