<?php
date_default_timezone_set('America/Lima');
class LibroMayorTemplate extends Template {
	function Inicio($estaciones, $fecha) { ?>
        <div id="template-LibroMayor">
        	<div class="container">
		        <h1 align="center">LIBRO MAYOR</h1>
		        <br>
	    	</div>

			<div class="columns">
	  			<div class="column">
		            <label class="label">Almacen</label>
	    			<span class="select" style="width: 100%">
					    <select id="cbo-almacen" style="width: 100%">
						    <option value="" selected>Todos</option>
						    <?php
								foreach($estaciones as $value)
									if ($_SESSION['almacen'] == $value['almacen'])
										echo "<option selected value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
									else 
										echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
							?>
					    </select>
				    </span>
	        	</div>

				<div class="column">
		        	<label class="label">Periodo</label>
		        	<input type="text" class="input" id="txt-periodo" name="periodo" autocomplete="on" placeholder="Ingresar periodo" value="<?php echo date("Y"); ?>" />
		       	</div>

				<div class="column">
		        	<label class="label">Mes</label>
		        	<input type="text" class="input" id="txt-mes" name="mes" autocomplete="on" placeholder="Ingresar mes" value="<?php echo date("m"); ?>" />
		       	</div>

				<div class="column">
		        	<label class="label">Since Account</label>
		        	<input type="text" class="input" id="txt-sinceaccount" name="mes" autocomplete="on" placeholder="Ingresar cuenta contable" value="1" />
		       	</div>

				<div class="column">
		        	<label class="label">To Account</label>
		        	<input type="text" class="input" id="txt-toaccount" name="mes" autocomplete="on" placeholder="Ingresar cuenta contable" value="9" />
		       	</div>
		    </div>

	        <br/>

	        <div class="columns is-centered">
				<div class="column is-12">
            		<div class="columns is-mobile">
						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-warning" id="btn-pdf"><i class="fa fa-file-pdf-o icon-size" aria-hidden="true"> <label class="label-btn-name">PDF</label></i></button>
	  					</div>

						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-success" id="btn-excel"><i class="fa fa-file-excel-o icon-size" aria-hidden="true"> <label class="label-btn-name">Excel</label></i></button>
	  					</div>

						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-success" id="btn-ple"><i class="fa fa-file-excel-o icon-size" aria-hidden="true"> <label class="label-btn-name">Exportar PLE</label></i></button>
	  					</div>
					</div>
				</div>
			</div>
		</div>

		<!--Modal Message Delete-->
		<div class="modal MsgError">
			<div class="modal-content">
				<article class="message">
					<div class="message-header">
						<div class="message-header-text"></div>
						<button class="delete btn-close">
					</div>
					<div class="message-body">
					</div>
				</article>
			</div>
		</div>

		<div class="columns is-desktop" id="div-LibroMayor_CRUD">
<?php
	}

	function gridViewPDF($response) {
		$response = json_decode($response);
		require('/sistemaweb/contabilidad/include/mc_table_fpdf.php');

		$pdf = new PDF_MC_Table();
		$pdf->DefinirParametrosHeader('LIBRO_MAYOR', $response);

		$sTipoLetra = 'Helvetica';
		$pdf->AddPage();

		//HEADER
		$this->pdf_header($pdf, $sTipoLetra, $response);

		//BODY	
		$this->pdf_body($pdf, $sTipoLetra, $response);

		$pdf->Output("/sistemaweb/contabilidad/reportes/pdf/reporte_libro_mayor.pdf", "F");
	}

	function pdf_header($pdf, $sTipoLetra, $response){
	}

	function pdf_body($pdf, $sTipoLetra, $response){
		//SETEAMOS FONT
		$pdf->SetFont($sTipoLetra, '', 6);

		//OBTENEMOS DATA
		$data = $response->data;
		$param = $response->param;

		//RECORREMOS ASIENTOS
		foreach ($data as $keyAccount => $entries) {
			//MOSTRAMOS CABECERA
			$porciones             = explode("*", $keyAccount);
			$acctcode              = $porciones[1];
			$denominacion_acctcode = $this->getDenominacion($acctcode);
			$pdf->Cell(181, 0, $denominacion_acctcode, 0, 0, 'L');
			$pdf->Ln(3);

			//VARIABLES PARA TOTALIZAR
			$total_debe  = 0;
			$total_haber = 0;

			//MOSTRAMOS DETALLE
			$cantidad_array = count($entries->detalle);
			foreach ($entries->detalle as $key => $rows) {
				$total_debe  += $rows->amtdt;
				$total_haber += $rows->amtct;

				$pdf->Row(
					array('border' => 0),
					array(
						array('text' => $rows->documentdate, 'align' => 'C'),
						array('text' => $param->Fe_Mes, 'align' => 'C'),
						array('text' => $rows->subbookcode, 'align' => 'C'),
						array('text' => $rows->registerno, 'align' => 'C'),
						array('text' => $rows->description_detail, 'align' => 'L'),
						array('text' => $rows->amtdt, 'align' => 'R'),
						array('text' => $rows->amtct, 'align' => 'R'),
					)
				);

				if ($key == $cantidad_array-1) {
					$this->mostrarTotal($pdf, $total_debe, $total_haber);
				}
			}
		}
	}

	function mostrarTotal($pdf, $total_debe, $total_haber) {
		$pdf->Cell(181, 0, '___________________________', 0, 0, 'R');
		$pdf->Ln(1.3);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => '', 'align' => 'C'),
				array('text' => '', 'align' => 'C'),
				array('text' => '', 'align' => 'C'),
				array('text' => '', 'align' => 'C'),
				array('text' => '', 'align' => 'C'),
				array('text' => $total_debe, 'align' => 'R'),
				array('text' => $total_haber, 'align' => 'R'),
			)
		);
		$pdf->Ln(1.5);
	}

	function gridViewExcel($response) {
		$response = json_decode($response);	
		include_once('../../include/Classes/PHPExcel.php');

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

		//CONTENIDO
		// Miscellaneous glyphs, UTF-8
		$hoja++;
		$objPHPExcel->createSheet($hoja);//creamos la pestaña
		$objPHPExcel->setActiveSheetIndex($hoja);
		$objPHPExcel->getActiveSheet($hoja)->setTitle("LIBRO DIARIO DETALLADO");

		$objPHPExcel->getActiveSheet($hoja)->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet($hoja)->mergeCells('A1:S1');
		$objPHPExcel->getActiveSheet($hoja)->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

		$objPHPExcel->getActiveSheet($hoja)->getStyle('A3:A5')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet($hoja)->getStyle('A7:Q7')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet($hoja)->getStyle('A7:Q7')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
		
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('A')->setWidth(11);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('B')->setWidth(4);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('C')->setWidth(4);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('D')->setWidth(5);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('E')->setWidth(10);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('F')->setWidth(12);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('G')->setWidth(9);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('H')->setWidth(9);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('I')->setWidth(14);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('J')->setWidth(4);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('K')->setWidth(7);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('L')->setWidth(9);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('M')->setWidth(11);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('N')->setWidth(11);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('O')->setWidth(12);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('P')->setWidth(22);
		$objPHPExcel->getActiveSheet($hoja)->getColumnDimension('Q')->setWidth(20);

		$objPHPExcel->getActiveSheet($hoja)->freezePane('A8');
		$bucle = 8;

		$meses     = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
		$periodo   = $meses[intval($response->param->Fe_Mes)] . " " . $response->param->Fe_Periodo;
		$ruc       = $response->data_company->ruc;
		$razsocial = $response->data_company->razsocial;

		$objPHPExcel->setActiveSheetIndex($hoja)
		->setCellValue("A1", "LIBRO DIARIO DETALLADO")
		->setCellValue("A3", "PERIODO: $periodo")
		->setCellValue("A4", "RUC: $ruc")
		->setCellValue("A5", "RAZÓN SOCIAL: $razsocial")
		->setCellValue("A7", "FECHA")
		->setCellValue("B7", "MES")
		->setCellValue("C7", "S/D")
		->setCellValue("D7", "ASI.")
		->setCellValue("E7", "CUENTA")
		->setCellValue("F7", "DESCRIPCION")
		->setCellValue("G7", "DEBE")
		->setCellValue("H7", "HABER")
		->setCellValue("I7", "GLOSA")
		->setCellValue("J7", "DOC")
		->setCellValue("K7", "SERIE")
		->setCellValue("L7", "NUMERO")
		->setCellValue("M7", "FECHA DOC")
		->setCellValue("N7", "FECHA VEN")
		->setCellValue("O7", "RUC")
		->setCellValue("P7", "RAZ SOCIAL")
		->setCellValue("Q7", "FECHA Y HORA REG");		

		//RECORREMOS ASIENTOS
		foreach ($response->data as $key => $rows) {
			
			/**
			* Los valores para el campo "tableid"
			* Descripción breve
			* 1 = pos_transXXXXYY
			* 2 = fac_ta_factura_cabecera
			* 3 = cpag_ta_cabecera
			* 4 = c_cash_transaction
			*/

			/**
			* Obtenemos Razon Social
			*/
			$razsocial = "";
			if ($rows->tableid == "1" || $rows->tableid == "3") {
				$razsocial = $rows->razsocial;
			} else if ($rows->tableid == "2") {
				$razsocial = $rows->cli_razsocial;
			} else {
				$razsocial = isset($rows->razsocial) ? $rows->razsocial : $rows->cli_razsocial;
			}
			
			/**
			* Obtenemos Fecha de Vencimiento
			*/
			$duedate = "";
			if ( strpos($rows->acctcode, "12") === 0 || strpos($rows->acctcode, "42") === 0 ) {
				$duedate = $rows->documentdate;
			}

			$objPHPExcel->setActiveSheetIndex($hoja)
			->setCellValue('A' . $bucle, $rows->documentdate)
			->setCellValue('B' . $bucle, "=\"".$response->param->Fe_Mes."\"")
			->setCellValue('C' . $bucle, "=\"".$rows->subbookcode."\"")
			->setCellValue('D' . $bucle, "=\"".$rows->registerno."\"")
			->setCellValue('E' . $bucle, "=\"".$rows->acctcode."\"")
			->setCellValue('F' . $bucle, $rows->name)
			->setCellValue('G' . $bucle, $rows->amtdt)
			->setCellValue('H' . $bucle, $rows->amtct)
			->setCellValue('I' . $bucle, $rows->description_detail)
			->setCellValue('J' . $bucle, "=\"".$rows->tipo_documento_sunat."\"")
			->setCellValue('K' . $bucle, "=\"".$rows->serie."\"")
			->setCellValue('L' . $bucle, "=\"".$rows->numero."\"")
			->setCellValue('M' . $bucle, $rows->documentdate)
			->setCellValue('N' . $bucle, $duedate)
			->setCellValue('O' . $bucle, $rows->int_clientes_id)
			->setCellValue('P' . $bucle, $razsocial)			
			->setCellValue('Q' . $bucle, $rows->documentdate_datetime);

			$bucle++;
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('/sistemaweb/contabilidad/reportes/excel/LIBRO_DIARIO.xls');

		$data = array(
			"ruta" => "/sistemaweb/contabilidad/reportes/excel/",
			"nombre_archivo" => "LIBRO_DIARIO.xls",
		);
		echo json_encode($data);
	}

	function gridViewPLE($response) {
		$response = json_decode($response);
		
		//ELIMINAMOS ARCHIVOS PLE
		$files = glob('/sistemaweb/contabilidad/reportes/excel/LE*.txt'); //Obtenemos todos los nombres de los ficheros que comienzan con "LE"
		foreach($files as $file){
			if(is_file($file))
			unlink($file); //Elimino el fichero
		}
		
		ob_clean();
		
		//VARIABLES PARA INDEXAR
		$contador_asiento = 0;	

		//RECORREMOS ASIENTOS
		$param = $response->param;
		foreach ($response->data as $key => $rows) {
			if ($act_entry_id != $rows->act_entry_id) {				
				$contador_asiento = 0;
			}
			$contador_asiento++;

			//OBTENEMOS LINEAS PARA EL PLE
			$PLEDATA    = $this->ImprimirLiniaPLE($rows, $param, $contador_asiento);
			$PLETXTLINI = $PLEDATA['registro'];
			$result    .= implode("|", $PLETXTLINI) . "|" . PHP_EOL;
			
			$act_entry_id = $rows->act_entry_id;
		}

		//NOMBRE DE ARCHIVO TXT
		$nombre_archivo = "LE_LIBRO_DIARIO.txt";

		//CREAMOS EL ARCHIVO TXT
		$archivo = fopen("/sistemaweb/contabilidad/reportes/excel/$nombre_archivo", "w") or die("error creando fichero!");
				
		//REESCRIBIMOS EL ARCHIVO TXT
		fwrite($archivo, $result);
		fclose($archivo);

		$data = array(
			"ruta" => "/sistemaweb/contabilidad/reportes/excel/",
			"nombre_archivo" => $nombre_archivo,
		);
		echo json_encode($data);
	}

	function ImprimirLiniaPLE($rows, $param, $contador_asiento) {
		$tipo_documento_sunat = $rows->tipo_documento_sunat;
		$serie = $rows->serie;
		$numero = $rows->numero;
		
		$PLETXT[0] = $param->Fe_Periodo . $param->Fe_Mes . "00"; //CAMPO 1
		$PLETXT[1] = $param->Fe_Mes .".". $rows->subbookcode .".". $rows->registerno; //CAMPO 2
		$PLETXT[2] = "M".$contador_asiento; //CAMPO 3
		$PLETXT[3] = $rows->acctcode; //CAMPO 4
		$PLETXT[4] = ""; //CAMPO 5
		$PLETXT[5] = ""; //CAMPO 6
		$PLETXT[6] = $rows->isocode; //CAMPO 7
		$PLETXT[7] = "0"; //CAMPO 8
		$PLETXT[8] = "0"; //CAMPO 9
		$PLETXT[9] = isset($tipo_documento_sunat) && !empty($tipo_documento_sunat) ? $tipo_documento_sunat : '00'; //CAMPO 10
		$PLETXT[10] = isset($serie) && !empty($serie) ? $serie : '0'; //CAMPO 11
		$PLETXT[11] = isset($numero) && !empty($numero) ? $numero : '0'; //CAMPO 12
		$PLETXT[12] = $rows->documentdate; //CAMPO 13
		$PLETXT[13] = ""; //CAMPO 14
		$PLETXT[14] = $rows->documentdate; //CAMPO 15
		$PLETXT[15] = $rows->description_detail; //CAMPO 16
		$PLETXT[16] = ""; //CAMPO 17
		$PLETXT[17] = $rows->amtdt; //CAMPO 18
		$PLETXT[18] = $rows->amtct; //CAMPO 19
		$PLETXT[19] = ""; //CAMPO 20
		$PLETXT[20] = "1"; //CAMPO 21

		return array(
			"registro" => $PLETXT
		);
	}

	/* FUNCIONES ADICIONALES */
	function printText($text) {
		return utf8_decode($text);
	}	

	function getDenominacion($acctcode) {
		global $sqlca;
		
		//OBTENEMOS DENOMINACIONES
		//MOSTRARA SOLO UNO, EL PRIMER RESULTADO. COMENTAR "LIMIT 1" SI QUEREMOS QUE SE MUESTRE TODOS LOS POSIBLES RESULTADOS (SOLO SI LOS HUBIERA) Y QUE SE CONCATENEN
		$sql = "
			SELECT 
				DISTINCT(name) AS name
			FROM 
				act_account 
			WHERE 
				TRIM(acctcode) = '" . TRIM($acctcode) . "' 
			/*LIMIT 1*/";
		
		if ($sqlca->query($sql) < 0) {
			return "-";
		}

		//RECORREMOS Y CONCATENAMOS DENOMINACIONES
		$denominacion = "";
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();			
			$denominacion .= $a['name'] . " | ";
		}
		$denominacion = $acctcode . " " . substr($denominacion, 0, -3);
		
		//VALIDAMOS LARGO DE LA DENOMINACION
		if (strlen($denominacion) >= 80) {
			$denominacion = substr($denominacion, 0, 80) . "...";
		}

		//RETORNAMOS DENOMINACIONES
		return $denominacion;
	}
}
