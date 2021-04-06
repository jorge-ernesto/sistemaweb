<?php

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();

$session_data_excel = $_SESSION['data_excel'];
$session_orden      = $_SESSION['orden'];
$session_arrRequest = $_SESSION['arrRequest'];
unset($_SESSION['data_excel']);
unset($_SESSION['orden']);
unset($_SESSION['arrRequest']);
error_log("Paso 1");
error_log( json_encode($_SESSION) );

//INCLUDES PARA OBTENER DATOS DE m_consumo_vales.php
date_default_timezone_set('UTC');

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('m_consumo_vales.php');
//CERRAR INCLUDES PARA OBTENER DATOS DE m_consumo_vales.php

include_once('../../include/Classes/PHPExcel.php');

error_reporting(E_ALL);

date_default_timezone_set('UTC');

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

// Add some data
$cabecera = array('fill' => array(
		'type' => PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array('argb' => 'FFFFFFFF')
	),
		'borders' => array(
		'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
		'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
	)
);

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);
$hoja = 0;

$objPHPExcel->getActiveSheet()->freezePane('A2');
$bucle = 1;

if ($session_data_excel != null) {

	$objPHPExcel->getActiveSheet()->getRowDimension($bucle)->setRowHeight(20);
	$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle . ':R' . $bucle)->applyFromArray($cabecera);
	$objPHPExcel->getActiveSheet()->getStyle('A' . $bucle . ':R' . $bucle)->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);

	$objPHPExcel->setActiveSheetIndex($hoja)
	->setCellValue('A1', 'Almacen')
	->setCellValue('B1', 'Nro.Liquidacion')
    ->setCellValue('C1', 'Nro.Factura')
    ->setCellValue('D1', 'Nro.Despacho')
	->setCellValue('E1', 'Fecha')
	->setCellValue('F1', 'Hora')
    ->setCellValue('G1', 'Nro. Manual')
    ->setCellValue('H1', 'Placa')
    ->setCellValue('I1', 'Producto')
    ->setCellValue('J1', 'Odometro')
    ->setCellValue('K1', 'Usuario')
    ->setCellValue('L1', 'DNI')
    ->setCellValue('M1', 'Cantidad')
    ->setCellValue('N1', 'Precio Contratado')
	->setCellValue('O1', 'Importe Contratado');
	
	error_log("Paso 2");
	error_log( json_encode($_SESSION) );
	if ( $session_arrRequest['sPrecioPizarra']=='true' ) {
		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue('P1', 'Precio Pizarra')
		->setCellValue('Q1', 'Importe Pizarra')
		->setCellValue('R1', 'Diferencia Precio')
		->setCellValue('S1', 'Diferencia Importe');
	}
	error_log("Paso 3");
	error_log( json_encode($_SESSION) );

	$data 	= $session_data_excel;
	$bucle 	= 0;
	error_log("Paso 4");
	error_log( json_encode($_SESSION) );

	$objmodel 	= new ConsumoValesModel();

	$nueva_logica = false;
	if($session_arrRequest['iTipoVersion'] == 1){
		$nueva_logica = true;
	}

	if($nueva_logica == true){
		if (count($data) > 0) {
			$i 				= 0;
			$tickets 		= 0;
			$cliente 		= "";
			$placa 			= "";
			$importecli 	= 0;
			$cantidadcli 	= 0;
			$nomcliente 	= "";
			// $documento = "SIN DOCUMENTO";
			// $documento_ = "SIN DOCUMENTO";
			// $producto      = "";
			// $cantidad_fac_anticipo = 0;
			// $importe_fac_anticipo = 0;

			$fImportePizarra = 0.00;
			$fImporteDiferencia = 0.00;

			$sTipoCliente = '';

			//RECORREMOS ARRAY DE CLIENTES
			foreach ($data as $key => $cliente) {		
				if((string)$key == "total_general"){
					continue;
				}

				//OBTENEMOS TITULO DEL CLIENTE
				$cliente_porciones = explode("|", $key);
				$sTipoCliente = $cliente_porciones[2];		
				$codCliente   = $cliente_porciones[0];														
				$nomCliente   = $cliente_porciones[1];
				$nomcliente = htmlentities($nomCliente);				
				$nomcliente = iconv("utf-8", "utf-8//IGNORE", $nomcliente);
				//
				$bucle 			= $bucle + 2;
				$objRichText 	= new PHPExcel_RichText();
				$objBold1 		= $objRichText->createTextRun("Cliente " . $sTipoCliente . ": " . $codCliente . " - " . $nomCliente);
				$objBold1->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);

				//RECORREMOS FACTURAS DEL CLIENTE
				foreach ($cliente as $key2 => $factura) {		
					if((string)$key2 == "total_cliente"){
						continue;
					}
					
					//OBTENEMOS TITULO DE LA FACTURA
					$documento = $key2;
					$total_factura = $objmodel->getTotalFactura($sTipoCliente, $documento);
					//
					$bucle 			= $bucle + 1;
					$objRichText 	= new PHPExcel_RichText();
					$objBold1 		= $objRichText->createTextRun("Documento: " . (TRIM($documento) == "" ? "SIN DOCUMENTO" : TRIM($documento)));
					$objBold1->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);
					//
					$objRichText 	= new PHPExcel_RichText();
					$objBold1 		= $objRichText->createTextRun("Cantidad: ".number_format((float)$total_factura[0], 4, '.', ',') );
					$objBold1->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('B' . $bucle)->setValue($objRichText);
					//
					$objRichText 	= new PHPExcel_RichText();
					$objBold1 		= $objRichText->createTextRun("Total: ".number_format((float)$total_factura[1], 2, '.', ',') );
					$objBold1->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('C' . $bucle)->setValue($objRichText);

					//RECORREMOS PRODUCTOS DE LA FACTURA
					foreach ($factura as $key3 => $item) {
						if((string)$key3 == "total_factura"){
							continue;
						}

						//OBTENEMOS TITULO DE LOS ITEMS													
						$item_porciones = explode("|", $key3);
						$codigo_item    = $item_porciones[0];
						$name_item      = $item_porciones[1];
						$total_item_factura         = $objmodel->getTotalItemByFactura($sTipoCliente, $documento, $codigo_item);			
						$mostrar_total_item_factura = ($sTipoCliente == "ANTICIPO") ? " | Cantidad: " . number_format((float)$total_item_factura[0], 2, '.', ',') . " | Total: " . number_format((float)$total_item_factura[1], 2, '.', ',') : "";			
						//
						$bucle 			= $bucle + 1;
						$objRichText 	= new PHPExcel_RichText();
						$objBold1 		= $objRichText->createTextRun($name_item);
						$objBold1->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);
						//
						if($sTipoCliente == "ANTICIPO"){
							$objRichText 	= new PHPExcel_RichText();
							$objBold1 		= $objRichText->createTextRun("Cantidad: ".number_format((float)$total_item_factura[0], 4, '.', ',') );
							$objBold1->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getCell('B' . $bucle)->setValue($objRichText);
							//
							$objRichText 	= new PHPExcel_RichText();
							$objBold1 		= $objRichText->createTextRun("Total: ".number_format((float)$total_item_factura[1], 2, '.', ',') );
							$objBold1->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getCell('C' . $bucle)->setValue($objRichText);
						}						

						//RECORREMOS VALES METIDOS DENTRO DEL ARRAY ITEM
						foreach ($item as $key4 => $vale) {
							if((string)$key4 == "total_item"){
								continue;
							}

							$tickets++;	

							if ( $session_arrRequest['sPrecioPizarra']=='true' ){
								$fImportePizarra=round($vale['cantidad'] * $vale['nu_precio_especial'],2);
								$fImporteDiferencia=round($vale['importe'],2) - $fImportePizarra;
			
								$fTotImportePizarra+=$fImportePizarra;
								$fTotImporteDiferencia+=$fImporteDiferencia;
							}

							if($session_orden == "D"){
								$sDocumento = ( $vale["documento"] != '' ? $vale["documento"] : $vale["documento2"]);

								$bucle = $bucle + 1;

								$objPHPExcel->setActiveSheetIndex($hoja)
									->setCellValue('A' . $bucle, $vale["almacen"])
									->setCellValue('B' . $bucle, $vale["liquidacion"])
									->setCellValue('C' . $bucle, $vale["documento"])
									->setCellValue('D' . $bucle, $vale["numero"])
								->setCellValue('E' . $bucle, $vale["fecha"])
								->setCellValue('F' . $bucle, $vale["hora"])
									->setCellValue('G' . $bucle, $vale["vale"])
									->setCellValue('H' . $bucle, $vale["placa"])
									->setCellValue('I' . $bucle, $vale["producto"])
									->setCellValue('J' . $bucle, $vale["odometro"]);

									$usuario2 = (empty($vale["chofer"])) ? '  ' : (string) $vale['chofer'];
								$usuario2 = htmlentities($usuario2);
								$usuario2 = iconv("utf-8", "utf-8//IGNORE", $usuario2);
								$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(10, $bucle, $usuario2, PHPExcel_Cell_DataType::TYPE_STRING);

									$nu_documento_chofer = (empty($vale["nu_documento_chofer"])) ? '  ' : (string) $vale['nu_documento_chofer'];
								$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(11, $bucle, $nu_documento_chofer, PHPExcel_Cell_DataType::TYPE_STRING);

								$objPHPExcel->setActiveSheetIndex($hoja)
									->setCellValue('M' . $bucle, $vale["cantidad"])
									->setCellValue('N' . $bucle, $vale["ss_precio_contratado"])
								->setCellValue('O' . $bucle, $vale["importe"]);
																	
								if ( $session_arrRequest['sPrecioPizarra']=='true' ){
									$objPHPExcel->setActiveSheetIndex($hoja)
									->setCellValue('P' . $bucle, $vale["nu_precio_especial"])
									->setCellValue('Q' . $bucle, $fImportePizarra)
									->setCellValue('R' . $bucle, $vale["ss_precio_contratado"] - $vale["nu_precio_especial"])
									->setCellValue('S' . $bucle, $fImporteDiferencia);
								}								
							}
						}

						//OBTENEMOS TOTALES POR ITEM SIN RECORRER ARRAY $item
						$bucle 			= $bucle + 1;
						$objRichText 	= new PHPExcel_RichText();
						$objBold1 		= $objRichText->createTextRun('Total Item', 4, '.', ',');
						$objBold1->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getCell('L' . $bucle)->setValue($objRichText);
						//
						$objRichText 	= new PHPExcel_RichText();
						$objBold1 		= $objRichText->createTextRun(number_format($item['total_item']['cantidad_item'], 4, '.', ','));
						$objBold1->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getCell('M' . $bucle)->setValue($objRichText);
						//
						$objRichText 	= new PHPExcel_RichText();
						$objBold1 		= $objRichText->createTextRun(number_format($item['total_item']['importe_item'], 2, '.', ','));
						$objBold1->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getCell('O' . $bucle)->setValue($objRichText);	

						if($sTipoCliente == "ANTICIPO"){
							//OBTENEMOS QUIEBRE ENTRE EL TOTAL ITEM FACTURA VS LOS VALES
							$bucle 			= $bucle + 1;
							$objRichText 	= new PHPExcel_RichText();
							$objBold1 		= $objRichText->createTextRun('Quiebre Item', 4, '.', ',');
							$objBold1->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getCell('L' . $bucle)->setValue($objRichText);
							//
							$objRichText 	= new PHPExcel_RichText();
							$objBold1 		= $objRichText->createTextRun(number_format((float)$total_item_factura[0] - $item['total_item']['cantidad_item'], 4, '.', ','));
							$objBold1->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getCell('M' . $bucle)->setValue($objRichText);
							//
							$objRichText 	= new PHPExcel_RichText();
							$objBold1 		= $objRichText->createTextRun(number_format((float)$total_item_factura[1] - $item['total_item']['importe_item'], 2, '.', ','));
							$objBold1->getFont()->setBold(true);
							$objPHPExcel->getActiveSheet()->getCell('O' . $bucle)->setValue($objRichText);			
						}
					}				

					//OBTENEMOS TOTALES POR FACTURA SIN RECORRER ARRAY $factura
					$bucle 			= $bucle + 1;
					$objRichText 	= new PHPExcel_RichText();
					$objBold1 		= $objRichText->createTextRun('Total Factura', 4, '.', ',');
					$objBold1->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('L' . $bucle)->setValue($objRichText);
					//
					$objRichText 	= new PHPExcel_RichText();
					$objBold1 		= $objRichText->createTextRun(number_format($factura['total_factura']['cantidad_factura'], 4, '.', ','));
					$objBold1->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('M' . $bucle)->setValue($objRichText);
					//
					$objRichText 	= new PHPExcel_RichText();
					$objBold1 		= $objRichText->createTextRun(number_format($factura['total_factura']['importe_factura'], 2, '.', ','));
					$objBold1->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('O' . $bucle)->setValue($objRichText);

					if($sTipoCliente == "ANTICIPO"){
						//OBTENEMOS QUIEBRE ENTRE EL TOTAL FACTURA VS LOS VALES
						$bucle 			= $bucle + 1;
						$objRichText 	= new PHPExcel_RichText();
						$objBold1 		= $objRichText->createTextRun('Quiebre Factura', 4, '.', ',');
						$objBold1->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getCell('L' . $bucle)->setValue($objRichText);
						//
						$objRichText 	= new PHPExcel_RichText();
						$objBold1 		= $objRichText->createTextRun(number_format((float)$total_factura[0] - $factura['total_factura']['cantidad_factura'], 4, '.', ','));
						$objBold1->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getCell('M' . $bucle)->setValue($objRichText);
						//
						$objRichText 	= new PHPExcel_RichText();
						$objBold1 		= $objRichText->createTextRun(number_format((float)$total_factura[1] - $factura['total_factura']['importe_factura'], 2, '.', ','));
						$objBold1->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getCell('O' . $bucle)->setValue($objRichText);						
					}	
				}

				//OBTENEMOS TOTALES POR CLIENTE SIN RECORRER ARRAY $cliente
				$bucle 			= $bucle + 1;
				$objRichText 	= new PHPExcel_RichText();
				$objBold1 		= $objRichText->createTextRun('Total Cliente', 4, '.', ',');
				$objBold1->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getCell('L' . $bucle)->setValue($objRichText);
				//
				$objRichText 	= new PHPExcel_RichText();
				$objBold1 		= $objRichText->createTextRun(number_format($cliente['total_cliente']['cantidad_cliente'], 4, '.', ','));
				$objBold1->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getCell('M' . $bucle)->setValue($objRichText);
				//
				$objRichText 	= new PHPExcel_RichText();
				$objBold1 		= $objRichText->createTextRun(number_format($cliente['total_cliente']['importe_cliente'], 2, '.', ','));
				$objBold1->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getCell('O' . $bucle)->setValue($objRichText);				
			}
		}

		$objPHPExcel->getActiveSheet()->getStyle('G7:G' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('H7:H' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('N7:N' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('M7:M' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('Q7:Q' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('R7:R' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('T7:T' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('AJ7:AJ' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('AL7:AL' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	} //FIN DE CONDICIONAL NUEVA LOGICA

	if($nueva_logica == false){
		if (count($data) > 0) {
			$i 				= 0;
			$tickets 		= 0;
			$cliente 		= "";
			$placa 			= "";
			$importecli 	= 0;
			$cantidadcli 	= 0;
			$nomcliente 	= "";
			// $documento = "SIN DOCUMENTO";
			// $documento_ = "SIN DOCUMENTO";
			// $producto      = "";
			// $cantidad_fac_anticipo = 0;
			// $importe_fac_anticipo = 0;

			$fImportePizarra = 0.00;
			$fImporteDiferencia = 0.00;

			$sTipoCliente = '';

			for($i=0; $i < count($data); $i++){
				$sTipoCliente = 'EFECTIVO';
				if ( $data[$i]['nu_tipo_efectivo'] == '0' && $data[$i]['no_tipo_anticipo'] == 'N' ){
					$sTipoCliente = 'CREDITO';
				} else if ( $data[$i]['nu_tipo_efectivo'] == '0' && $data[$i]['no_tipo_anticipo'] == 'S' ){
					$sTipoCliente = 'ANTICIPO';
				}

				$nomcliente = htmlentities($data[$i]["nomcliente"]);
				$nomcliente = iconv("utf-8", "utf-8//IGNORE", $nomcliente);

				if($cliente != $data[$i]["codcliente"]){
					$bucle++;

					if($i!=0){
						$objRichText 	= new PHPExcel_RichText();
						$objBold1 		= $objRichText->createTextRun("Total Cantidad: ".$cantidadcli. "  - Total Importe: ".$importecli);
						$objBold1->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);
						
						$cantidadcli 	= 0;
						$importecli 	= 0;
						// $documento = "SIN DOCUMENTO";
						// $documento_ = "SIN DOCUMENTO";
						// $producto = "";
					}
		
					$cliente 		= $data[$i]['codcliente'];
					$bucle 			= $bucle + 2;
					$objRichText 	= new PHPExcel_RichText();
					$objBold1 		= $objRichText->createTextRun("Cliente " . $sTipoCliente . ": " . $data[$i]["codcliente"] . " - " . $nomcliente);
					$objBold1->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);
				}

				error_log("Paso 5");
				error_log( json_encode($_SESSION) );
				if ( $session_arrRequest['sPrecioPizarra']=='true' ){
					$fImportePizarra=round($data[$i]['cantidad'] * $data[$i]['nu_precio_especial'],2);
					$fImporteDiferencia=round($data[$i]['importe'],2) - $fImportePizarra;

					$fTotImportePizarra+=$fImportePizarra;
					$fTotImporteDiferencia+=$fImporteDiferencia;
				}
				error_log("Paso 6");
				error_log( json_encode($_SESSION) );
				
				if($session_orden == "D"){
					// if($sTipoCliente == 'ANTICIPO'){ //SI EL CLIENTE ES ANTICIPO
											
					// 	//LOGICA PARA OBTENER LOS TOTALES POR FACTURAS DEL CLIENTE
					// 	if($documento != $data[$i]['documento']){ //SI LA COLUMNA #FACTURA ES DIFERENTE, ES DECIR SI YA PASO A UN NUEVO CLIENTE
					// 		if($i!=0 && $documento != "SIN DOCUMENTO"){ //NO ES EL PRIMER ELEMENTO DEL ARRAY, NI EL PRIMER VALE DE UN NUEVO CLIENTE
					// 			$bucle 			= $bucle + 1;
					// 			$objRichText 	= new PHPExcel_RichText();
					// 			$objBold1 		= $objRichText->createTextRun(number_format($cantidad_fac_anticipo, 4, '.', ','));
					// 			$objBold1->getFont()->setBold(true);
					// 			$objPHPExcel->getActiveSheet()->getCell('M' . $bucle)->setValue($objRichText);
								
					// 			$objRichText 	= new PHPExcel_RichText();
					// 			$objBold1 		= $objRichText->createTextRun(number_format($importe_fac_anticipo, 4, '.', ','));
					// 			$objBold1->getFont()->setBold(true);
					// 			$objPHPExcel->getActiveSheet()->getCell('O' . $bucle)->setValue($objRichText);		

					// 			$bucle 			= $bucle + 1;
					// 			$objRichText 	= new PHPExcel_RichText();
					// 			$objBold1 		= $objRichText->createTextRun(number_format($data[($i-1)]['importe_factura_anticipo'] - $importe_fac_anticipo, 4, '.', ','));
					// 			$objBold1->getFont()->setBold(true);
					// 			$objPHPExcel->getActiveSheet()->getCell('O' . $bucle)->setValue($objRichText);

					// 			$cantidad_fac_anticipo = 0;
					// 			$importe_fac_anticipo = 0;
					// 		}

					// 		$bucle 			= $bucle + 1;
					// 		$objRichText 	= new PHPExcel_RichText();
					// 		$objBold1 		= $objRichText->createTextRun("Documento :" . (TRIM($data[$i]['documento']) == "" ? "SIN DOCUMENTO" : TRIM($data[$i]['documento'])));
					// 		$objBold1->getFont()->setBold(true);
					// 		$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);

					// 		$objRichText 	= new PHPExcel_RichText();
					// 		$objBold1 		= $objRichText->createTextRun("Total: ".number_format($data[$i]['importe_factura_anticipo'], 2, '.', ',') );
					// 		$objBold1->getFont()->setBold(true);
					// 		$objPHPExcel->getActiveSheet()->getCell('B' . $bucle)->setValue($objRichText);
							
					// 		$documento = $data[$i]['documento'];
					// 	}

					// 	//LOGICA PARA MARCAR LOS PRODUCTOS AGRUPADAS POR FACTURAS
					// 	if($documento_ != $data[$i]['documento'] || $producto != $data[$i]['producto']){
					// 		$bucle 			= $bucle + 1;
					// 		$objRichText 	= new PHPExcel_RichText();
					// 		$objBold1 		= $objRichText->createTextRun($data[$i]['producto']);
					// 		$objBold1->getFont()->setBold(true);
					// 		$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);

					// 		$documento_ = $data[$i]['documento'];
					// 		$producto = $data[$i]['producto'];
					// 	}

					// }

					$sDocumento = ( $data[$i]["documento"] != '' ? $data[$i]["documento"] : $data[$i]["documento2"]);

					$bucle = $bucle + 1;

					$objPHPExcel->setActiveSheetIndex($hoja)
						->setCellValue('A' . $bucle, $data[$i]["almacen"])
						->setCellValue('B' . $bucle, $data[$i]["liquidacion"])
						->setCellValue('C' . $bucle, $data[$i]["documento"])
						->setCellValue('D' . $bucle, $data[$i]["numero"])
					->setCellValue('E' . $bucle, $data[$i]["fecha"])
					->setCellValue('F' . $bucle, $data[$i]["hora"])
						->setCellValue('G' . $bucle, $data[$i]["vale"])
						->setCellValue('H' . $bucle, $data[$i]["placa"])
						->setCellValue('I' . $bucle, $data[$i]["producto"])
						->setCellValue('J' . $bucle, $data[$i]["odometro"]);

						$usuario2 = (empty($data[$i]["chofer"])) ? '  ' : (string) $data[$i]['chofer'];
					$usuario2 = htmlentities($usuario2);
					$usuario2 = iconv("utf-8", "utf-8//IGNORE", $usuario2);
					$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(10, $bucle, $usuario2, PHPExcel_Cell_DataType::TYPE_STRING);

						$nu_documento_chofer = (empty($data[$i]["nu_documento_chofer"])) ? '  ' : (string) $data[$i]['nu_documento_chofer'];
					$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(11, $bucle, $nu_documento_chofer, PHPExcel_Cell_DataType::TYPE_STRING);

					$objPHPExcel->setActiveSheetIndex($hoja)
						->setCellValue('M' . $bucle, $data[$i]["cantidad"])
						->setCellValue('N' . $bucle, $data[$i]["ss_precio_contratado"])
					->setCellValue('O' . $bucle, $data[$i]["importe"]);
						
					error_log("Paso 7");
					error_log( json_encode($_SESSION) );
					if ( $session_arrRequest['sPrecioPizarra']=='true' ){
						$objPHPExcel->setActiveSheetIndex($hoja)
						->setCellValue('P' . $bucle, $data[$i]["nu_precio_especial"])
						->setCellValue('Q' . $bucle, $fImportePizarra)
						->setCellValue('R' . $bucle, $data[$i]["ss_precio_contratado"] - $data[$i]["nu_precio_especial"])
						->setCellValue('S' . $bucle, $fImporteDiferencia);
					}
					error_log("Paso 8");
					error_log( json_encode($_SESSION) );
				}

				$cantidadcli+=$data[$i]['cantidad'];
				$importecli+=$data[$i]['importe'];
				// $cantidad_fac_anticipo+=$data[$i]['cantidad'];
				// $importe_fac_anticipo+=$data[$i]['importe'];
			} // ./ For

			$bucle 			= $bucle + 1;
			$objRichText 	= new PHPExcel_RichText();
			
			$objBold1 		= $objRichText->createTextRun("Total Cantidad: ".$cantidadcli. "  - Total Importe: ".$importecli);
			$objBold1->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getCell('A' . $bucle)->setValue($objRichText);		
		}

		$objPHPExcel->getActiveSheet()->getStyle('G7:G' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('H7:H' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('N7:N' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('M7:M' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('Q7:Q' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('R7:R' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('T7:T' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('AJ7:AJ' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle('AL7:AL' . $bucle)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	} //FIN DE CONDICIONAL NUEVA LOGICA
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="ConsumoVales.xls"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;