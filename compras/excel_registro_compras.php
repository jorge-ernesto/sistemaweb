<?php

include_once('../include/Classes/PHPExcel.php');
include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
require("/sistemaweb/clases/funciones.php");
$funcion = new class_funciones;
$clase_error = new OpensoftError;
$clase_error->_error();
$conector_id = $funcion->conectar("","","","","");

/* DATOS DEL FORMULARIO */

$fecha 		= $_REQUEST['fecha'];
$fecha2		= $_REQUEST['fecha2'];
$estacion	= $_REQUEST['estacion'];
$proveedor	= $_REQUEST['proveedor'];
$documento	= $_REQUEST['documento'];
$tdocu		= $_REQUEST['tdocu'];
$tmoneda	= $_REQUEST['tmoneda'];
$type_ple	= $_REQUEST['type_ple'];

/* CONSULTA A LA B.D */

$sql="
	SELECT
		p1.par_valor,
		p2.par_valor
	FROM
		int_parametros p1,
		int_parametros p2,
		int_parametros p3
	WHERE
		p1.par_nombre = 'razsocial'
		AND p2.par_nombre = 'ruc';
	";

$sqlca->query($sql);

$res = Array();

$a = $sqlca->fetchRow();

$res['razsocial']	= $a[0];
$res['ruc']		= $a[1];

/* ----------------- */

/* LIBRO PARA EXCEL */

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

	/*$cabecera = array('fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('argb' => 'FFCCFFCC')
			),'borders' => array(
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
			)
        );*/

        $objPHPExcel->setActiveSheetIndex(0);
        $hoja = 0;

        $titulo = "REGISTRO DE COMPRAS";

	$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

        $objPHPExcel->setActiveSheetIndex($hoja)
                ->setCellValue('A1', $titulo)
                ->setCellValue('A3', 'PERIODO:')
                ->setCellValue('A4', 'RUC:')
                ->setCellValue('A5', 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL:')
                ->setCellValue('A7', ' ')
                ->setCellValue('A8', 'NUMERO')
                ->setCellValue('A9', 'CORRELATIVO')
                ->setCellValue('A10', 'DEL REGISTRO')
                ->setCellValue('A11', 'CODIGO UNICO')
                ->setCellValue('A12', 'DE LA OPERACION')
                ->setCellValue('A13', ' ')
                ->setCellValue('B3', $fecha.' AL '.$fecha2)
                ->setCellValue('B4', $res['ruc'])
                ->setCellValue('B5', $res['razsocial'])
                ->setCellValue('B7', ' ')
                ->setCellValue('B8', 'FECHA DE')
                ->setCellValue('B9', 'EMISION DEL')
                ->setCellValue('B10', 'COMPROBANTE')
                ->setCellValue('B11', 'DE PAGO')
                ->setCellValue('B12', 'O DOCUMENTO')
                ->setCellValue('B13', ' ')
                ->setCellValue('C7', ' ')
                ->setCellValue('C8', 'FECHA')
                ->setCellValue('C9', 'DE')
                ->setCellValue('C10', 'VENCIMIENTO')
                ->setCellValue('C11', 'O FEHCA')
                ->setCellValue('C12', 'DE PAGO (1)')
                ->setCellValue('C13', ' ')
                ->setCellValue('D7', '             COMPROBANTE DE PAGO')
                ->setCellValue('D8', '                          O DOCUMENTO')
                ->setCellValue('D9', ' ')
                ->setCellValue('D10', 'TIPO')
                ->setCellValue('D11', '(TABLA 10)')
                ->setCellValue('E9', 'SERIE O')
                ->setCellValue('E10', 'CODIGO DE LA')
                ->setCellValue('E11', 'DEPENDENCIA')
                ->setCellValue('E12', 'ADUANERA')
                ->setCellValue('E13', '(TABLA 11)')
                ->setCellValue('F9', 'AÑO DE')
                ->setCellValue('F10', 'EMISION DE')
                ->setCellValue('F11', 'LADUA')
                ->setCellValue('F12', 'O DSI')
                ->setCellValue('F13', '')
                ->setCellValue('G7', 'N° DEL COMPROBANTE DE PAGO')
                ->setCellValue('G8', 'DOCUMENTO, N° DE ORDEN DEL')
                ->setCellValue('G9', 'FORMULARIO FÍSICO O VIRTUAL,')
                ->setCellValue('G10', 'N° DE DUA, DSI O LIQUIDACIÓN DE ')
                ->setCellValue('G11', 'COBRANZA U OTROS DOCUMENTOS ')
                ->setCellValue('G12', 'EMITIDOS POR SUNAT PARA ACREDITAR ')
                ->setCellValue('G13', 'EL CRÉDITO FISCAL EN LA IMPORTACIÓN')
                ->setCellValue('H7', 'INFORMACIÓN DEL')
                ->setCellValue('H8', 'PROVEEDOR')
                ->setCellValue('H9', 'DOCUMENTO DE IDENTIDAD')
                ->setCellValue('H10', ' ')
                ->setCellValue('H11', 'TIPO')
                ->setCellValue('H12', ' (TABLA 2)')
                ->setCellValue('I10', ' ')
                ->setCellValue('I11', 'NUMERO')
                ->setCellValue('I12', ' ')
                ->setCellValue('J9', 'APELLIDOS')
                ->setCellValue('J10', 'Y NOMBRES')
                ->setCellValue('J11', 'DENOMINACIÓN')
                ->setCellValue('J12', 'O RAZÓN ')
                ->setCellValue('J13', 'SOCIAL')//PAGINA 1 TERMINA Y EMPIEZA PAGINA 2
                ->setCellValue('K7', ' ADQUISICIONES GRAVADAS DESTINADAS A OPERACIONES')
                ->setCellValue('K8', '             GRAVADAS Y/O DE EXPORTACIÓN')
                ->setCellValue('K9', ' ')
                ->setCellValue('K10', 'BASE')
                ->setCellValue('K11', 'IMPONIBLE')
                ->setCellValue('L11', 'IGV')
                ->setCellValue('M7', 'ADQUISICIONES GRAVADAS DESTINADAS A OPERACIONES')
                ->setCellValue('M8', 'GRAVADAS Y/O DE EXPORTACIÓN Y A OPERACIONES NO GRAVADAS')
                ->setCellValue('M9', ' ')
                ->setCellValue('M10', 'BASE')
                ->setCellValue('M11', 'IMPONIBLE')
                ->setCellValue('N11', 'IGV')//PAGINA 3
                ->setCellValue('M7', 'ADQUISICIONES GRAVADAS DESTINADAS A OPERACIONES')
                ->setCellValue('M8', 'GRAVADAS Y/O DE EXPORTACIÓN Y A OPERACIONES NO GRAVADAS')
                ->setCellValue('M9', ' ')
                ->setCellValue('M10', 'BASE')
                ->setCellValue('M11', 'IMPONIBLE')
                ->setCellValue('N11', 'IGV')
                ->setCellValue('O7', ' ADQUISICIONES GRAVADAS DESTINADAS A OPERACIONES')
                ->setCellValue('O8', '                                                 NO GRAVADAS')
                ->setCellValue('O9', ' ')
                ->setCellValue('O10', 'BASE')
                ->setCellValue('O11', 'IMPONIBLE')
                ->setCellValue('P11', 'IGV')
                ->setCellValue('Q7', ' ')
                ->setCellValue('Q8', 'VALOR')
                ->setCellValue('Q9', 'DE LAS')
                ->setCellValue('Q10', 'ADQUISICIONES')
                ->setCellValue('Q11', 'NO')
                ->setCellValue('Q12', 'GRAVADAS')
                ->setCellValue('Q13', ' ')
                ->setCellValue('R10', 'ISC')
                ->setCellValue('S9', 'OTROS')
                ->setCellValue('S10', 'TRIBUTOS Y')
                ->setCellValue('S11', ' CARGOS')
                ->setCellValue('T9', 'IMPORTE')
                ->setCellValue('T10', 'TOTAL')
                ->setCellValue('U7', '        N° DE')
                ->setCellValue('U8', '  COMPROBANTE')
                ->setCellValue('U9', '      DE PAGO ')
                ->setCellValue('U10', '    EMITIDO POR')
                ->setCellValue('U11', '    SUJETO NO')
                ->setCellValue('U12', ' DOMICILIADO (2)')
                ->setCellValue('U13', ' ')
                ->setCellValue('V7', 'CONSTANCIA DE DEPÓSITO')
                ->setCellValue('V8', 'DE DETRACCIÓN (3)')
                ->setCellValue('V9', ' ')
                ->setCellValue('V10', ' ')
                ->setCellValue('V11', 'NÚMERO')
                ->setCellValue('V12', ' ')
                ->setCellValue('V13', ' ')
                ->setCellValue('W9', ' ')
                ->setCellValue('W10', '      FECHA')
                ->setCellValue('W11', '         DE')
                ->setCellValue('W12', '    EMISIÓN')
                ->setCellValue('W13', ' ')
                ->setCellValue('X9', 'TIPO')
                ->setCellValue('X10', 'DE')
                ->setCellValue('X11', 'CAMBIO')
                ->setCellValue('Y7', 'REFERENCIA DEL COMPROBANTE DE PAGO')
                ->setCellValue('Y8', 'O DOCUMENTO ORIGINAL QUE SE MODIFICA')
                ->setCellValue('Y9', ' ')
                ->setCellValue('Y10', 'FECHA')
                ->setCellValue('Y11', '')
                ->setCellValue('Y12', ' ')
                ->setCellValue('Y13', ' ')
                ->setCellValue('Z10', 'TIPO')
                ->setCellValue('Z11', '(TABLA 10)')
                ->setCellValue('AA10', 'SERIE')
                ->setCellValue('AB9', 'N° DEL')
                ->setCellValue('AB10', 'COMPROBANTE ')
                ->setCellValue('AB11', 'DE PAGO O')
                ->setCellValue('AB12', 'DOCUMENTO')
                ->setCellValue('AB13', ' ');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(45);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(45);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(4);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(15);

	//Unir celdas 
	$objPHPExcel->getActiveSheet()->mergeCells('D7:F7');
	$objPHPExcel->getActiveSheet()->mergeCells('H7:J7');
	$objPHPExcel->getActiveSheet()->mergeCells('H8:J8');
	$objPHPExcel->getActiveSheet()->mergeCells('H9:I9');
	$objPHPExcel->getActiveSheet()->mergeCells('k7:L7');
	$objPHPExcel->getActiveSheet()->mergeCells('k8:L8');
	$objPHPExcel->getActiveSheet()->mergeCells('M7:N7');
	$objPHPExcel->getActiveSheet()->mergeCells('M8:N8');
	$objPHPExcel->getActiveSheet()->mergeCells('O7:P7');
	$objPHPExcel->getActiveSheet()->mergeCells('O8:P8');
	$objPHPExcel->getActiveSheet()->mergeCells('V7:W7');
	$objPHPExcel->getActiveSheet()->mergeCells('V8:W8');
	$objPHPExcel->getActiveSheet()->mergeCells('Y7:AB7');
	$objPHPExcel->getActiveSheet()->mergeCells('Y8:AB8');

	$BStyle = array(
	  'borders' => array(
	    'outline' => array(
	      'style' => PHPExcel_Style_Border::BORDER_THIN
	    )
	  )
	);

	$top = array(
	  'borders' => array(
	    'top' => array(
	      'style' => PHPExcel_Style_Border::BORDER_THIN
	    )
	  )
	);

	$right = array(
	  'borders' => array(
	    'right' => array(
	      'style' => PHPExcel_Style_Border::BORDER_THIN
	    )
	  )
	);

	//CABECERA
	$objPHPExcel->getActiveSheet()->getStyle('D7:F7')->applyFromArray($top);
	$objPHPExcel->getActiveSheet()->getStyle('H7:J7')->applyFromArray($top);
	$objPHPExcel->getActiveSheet()->getStyle('k7:L7')->applyFromArray($top);
	$objPHPExcel->getActiveSheet()->getStyle('M7:N7')->applyFromArray($top);
	$objPHPExcel->getActiveSheet()->getStyle('O7:P7')->applyFromArray($top);
	$objPHPExcel->getActiveSheet()->getStyle('V7:W7')->applyFromArray($top);
	$objPHPExcel->getActiveSheet()->getStyle('Y7:AB7')->applyFromArray($top);

	//DERECHA
	$objPHPExcel->getActiveSheet()->getStyle('J7:J8')->applyFromArray($right);
	$objPHPExcel->getActiveSheet()->getStyle('L7:L8')->applyFromArray($right);
	$objPHPExcel->getActiveSheet()->getStyle('N7:N8')->applyFromArray($right);
	$objPHPExcel->getActiveSheet()->getStyle('AB7:AB8')->applyFromArray($right);

	//BORDES
	$objPHPExcel->getActiveSheet()->getStyle('A7:A13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B7:B13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C7:C13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D9:D13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E9:E13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F9:F13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G7:G13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H9:I9')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H10:H13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('I10:I13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('J9:J13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('K9:K13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('L9:L13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('M9:M13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('N9:N13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('O9:O13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('P9:P13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('Q7:Q13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('R7:R13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('S7:S13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('T7:T13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('U7:U13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('V9:W13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('W9:W13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('X7:X13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('Y9:Y13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('Z9:Z13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AA9:AA13')->applyFromArray($BStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AB9:AB13')->applyFromArray($BStyle);


        $objPHPExcel->getActiveSheet()->freezePane('A14');//LINEA HORIZONTAL PARA SEPARAR CABECERA Y DETALLE

	/* ------------------------------------------------ DATOS ----------------------------------------------------- */

		if($type_ple == 'RCS')
			$ple = "AND c.pro_cab_tipdocumento NOT IN ('02','11','20', '91')";
                else if($type_ple == 'RCD')
                        $ple = "AND c.pro_cab_tipdocumento IN ('91')";
		else
			$ple = "AND c.pro_cab_tipdocumento NOT IN ('02', '91')";

		$query = "
			SELECT DISTINCT
				LPAD(CAST(c.pro_cab_numreg AS bpchar),10,'0') corre,
				to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') femision,
				CASE WHEN
					gen2.tab_car_03 = '14' THEN to_char(c.pro_cab_fechavencimiento, 'DD/MM/YYYY') 
				ELSE
					''
				END as fvencimiento,
				gen2.tab_car_03 tipo,
				c.pro_cab_seriedocumento serie,
				'' dsi,
				c.pro_cab_numdocumento numero,
				CASE WHEN
					gen2.tab_car_03 in ('01','07') THEN '6'
				ELSE
					''
				END AS identidad,
				p.pro_ruc AS ruc,
				p.pro_razsocial AS razonsocial,
				CASE WHEN
					gen2.tab_car_03 in ('07') THEN --TF-0000005690: se quito el 08
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_impafecto ELSE ROUND((-c.pro_cab_impafecto * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_impafecto ELSE ROUND((c.pro_cab_impafecto * c.pro_cab_tcambio), 2) END)
				END AS imponible,
				CASE WHEN
					gen2.tab_car_03 in ('07') THEN --TF-0000005690: se quito el 08
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_impto1 ELSE ROUND((-c.pro_cab_impto1 * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_impto1 ELSE ROUND((c.pro_cab_impto1 * c.pro_cab_tcambio), 2) END)
				END AS impuesto,
				CASE WHEN
					gen2.tab_car_03 in ('07') THEN --TF-0000005690: se quito el 08
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_imptotal ELSE ROUND((-c.pro_cab_imptotal * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_imptotal ELSE ROUND((c.pro_cab_imptotal * c.pro_cab_tcambio), 2) END)
				END AS total,
				c.regc_sunat_percepcion perce,
				ROUND(c.pro_cab_tcambio,3) tc,
				gen.tab_car_03 tiporef,
				--substr(c.pro_cab_numdocreferencia,1,4) serieref, 
				--substr(c.pro_cab_numdocreferencia,5,7) docuref,
				(SELECT substr(ima.mov_docurefe2,1,4) FROM inv_movialma ima WHERE ima.mov_docurefe = trim(c.pro_cab_seriedocumento)||''||trim(c.pro_cab_numdocumento) AND ima.mov_docurefe2 != '' LIMIT 1) as serieref,
				(SELECT substr(ima.mov_docurefe2,5,8) FROM inv_movialma ima WHERE ima.mov_docurefe = trim(c.pro_cab_seriedocumento)||''||trim(c.pro_cab_numdocumento) AND ima.mov_docurefe2 != '' LIMIT 1) as docuref,
				to_char(c.pro_cab_fecharegistro, 'DD/MM/YYYY') fregistro,
				ROUND(c.pro_cab_impinafecto, 2) inafecto,
				rubro.ch_tipo_item rubro,
				to_char(c.fecha_replicacion, 'DD/MM/YYYY') fperiodo,
				c.pro_cab_numreg id,
				(SELECT pay_number FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento) LIMIT 1) dnumero,
				(SELECT created FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento) LIMIT 1) dfecha,
				--(SELECT to_char(nc.pro_cab_fechaemision, 'DD/MM/YYYY') FROM cpag_ta_cabecera nc WHERE nc.pro_codigo = c.pro_codigo AND nc.pro_cab_tipdocumento = c.pro_cab_tipdocreferencia AND nc.pro_cab_seriedocumento||nc.pro_cab_numdocumento = c.pro_cab_numdocreferencia) fecharef
				(SELECT (SELECT to_char(ima2.mov_fecha, 'DD/MM/YYYY') FROM inv_movialma ima2 WHERE ima2.mov_docurefe = ima.mov_docurefe2 LIMIT 1) FROM inv_movialma ima WHERE ima.mov_docurefe = trim(c.pro_cab_seriedocumento)||''||trim(c.pro_cab_numdocumento) AND ima.mov_docurefe2 != '' LIMIT 1) as fecharef
			FROM
				cpag_ta_cabecera c
				INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
				LEFT JOIN int_proveedores p ON (c.pro_codigo = p.pro_codigo)
				LEFT JOIN inv_ta_almacenes a ON(c.pro_cab_almacen = a.ch_almacen)
				LEFT JOIN int_tabla_general as gen ON((CASE WHEN c.pro_cab_tipdocumento IN ('20','11') THEN c.pro_cab_tipdocreferencia = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) END) and tab_tabla ='08')
				LEFT JOIN int_tabla_general as gen2 ON((c.pro_cab_tipdocumento = substring(TRIM(gen2.tab_elemento) for 2 from length(TRIM(gen2.tab_elemento))-1) and gen2.tab_tabla ='08'))
				LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
		";
		
	
		if($fecha != ''){
		$query.="WHERE
				c.pro_cab_fecharegistro::DATE BETWEEN to_date('$fecha','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')
				$ple
			";
		}

		if($estacion != '')
		$query .= "	AND c.pro_cab_almacen = '$estacion' ";

		if($proveedor != '')
		$query .= "	AND c.pro_codigo = '$proveedor' ";
		
		if($documento != '')
		$query .= "	AND c.pro_cab_numdocumento = '$documento' ";

		if($tdocu != 'TODOS')
		$query .= "	AND c.pro_cab_tipdocumento = '$tdocu' ";
		
                $query .= "
                        ORDER BY 
                                to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY'); ";

		/*$query .= "
			ORDER BY 
				LPAD(CAST(c.pro_cab_numreg AS bpchar),10,'0'),
				to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') DESC; ";*/

		//echo "<pre>";
		//print_r($query);
		//echo "</pre>";
		//return;
		$sqlca->query($query);

		$bucle		= 13;//AQUI EMPIEZAN LOS REGISTROS PARA EXCEL
		$imponible	= 0;
		$inafecto	= 0;
		$igv		= 0;
		$total		= 0;
                $nu_total       = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();

			$femision = $a[1];

			if($a[3] == '07' || $a[3] == '08'){
			}else{
				$femision   = '';
				$a[15]      = '';
				$a[16]      = '';
				$a[17]      = '';
			}

			$imponible	= $imponible + $a[10];
			$inafecto	= $inafecto + $a[19];
			$igv		= $igv + $a[11];
			$total		= $total + ($a[10] + $a[11] + $a[19]);

                        if($a[3] == '91')
                                $nu_total = $a[19];//INAFECTO
                        else
                                $nu_total = ($a[10] + $a[11] + $a[19]);//IMPORTE TOTAL

			$a[19] = (empty($a[19])) ? '0.00' : $a[19];

			$bucle = $bucle + 1;

			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(0, $bucle, $a[0], PHPExcel_Cell_DataType::TYPE_STRING);//CORRELATIVO
			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(3, $bucle, $a[3], PHPExcel_Cell_DataType::TYPE_STRING);//TIPO
			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(4, $bucle, "0".trim($a[4]), PHPExcel_Cell_DataType::TYPE_STRING);//SERIE
			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(6, $bucle, $a[6], PHPExcel_Cell_DataType::TYPE_STRING);//NUMERO
			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(25, $bucle, $a[15], PHPExcel_Cell_DataType::TYPE_STRING);//TIPO
			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(26, $bucle, $a[16], PHPExcel_Cell_DataType::TYPE_STRING);//SERIE
			$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow(27, $bucle, $a[17], PHPExcel_Cell_DataType::TYPE_STRING);//NUMERO

			$objPHPExcel->setActiveSheetIndex($hoja)
               		->setCellValue('B' . $bucle, $a[1])
               		->setCellValue('C' . $bucle, $a[2])
               		->setCellValue('H' . $bucle, $a[7])
               		->setCellValue('J' . $bucle, $a[9])
               		->setCellValue('k' . $bucle, $a[10])
               		->setCellValue('I' . $bucle, $a[8])
               		->setCellValue('L' . $bucle, $a[11])
               		->setCellValue('Q' . $bucle, $a[19])
               		->setCellValue('R' . $bucle, '0.00')
               		->setCellValue('S' . $bucle, '0.00')
               		->setCellValue('T' . $bucle, $nu_total)//IMPORTE TOTAL
               		->setCellValue('U' . $bucle, '0.00')
               		->setCellValue('V' . $bucle, $a['23'])
               		->setCellValue('W' . $bucle, $a['24'])
               		->setCellValue('X' . $bucle, $a[14])
               		->setCellValue('Y' . $bucle, $a['25']);
		}

		$bucle = $bucle + 1;

		/* TOTALES */

		$imponible 	= number_format($imponible, 4, '.', ',');
		$igv		= number_format($igv, 4, '.', ',');
		$inafecto	= number_format($inafecto, 4, '.', ',');
		$total		= number_format($total, 4, '.', ',');

		$objRichText = new PHPExcel_RichText();
		$objBold1 = $objRichText->createTextRun("TOTALES: ");
		$objBold1->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getCell('J' . $bucle)->setValue($objRichText);

		$objRichText2 = new PHPExcel_RichText();
		$objBold1 = $objRichText2->createTextRun($imponible);
		$objBold1->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getCell('K' . $bucle)->setValue($objRichText2);

		$objRichText3 = new PHPExcel_RichText();
		$objBold1 = $objRichText3->createTextRun($igv);
		$objBold1->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getCell('L' . $bucle)->setValue($objRichText3);

		$objRichText4 = new PHPExcel_RichText();
		$objBold1 = $objRichText4->createTextRun($inafecto);
		$objBold1->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getCell('Q' . $bucle)->setValue($objRichText4);

		$objRichText5 = new PHPExcel_RichText();
		$objBold1 = $objRichText5->createTextRun($total);
		$objBold1->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getCell('T' . $bucle)->setValue($objRichText5);

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="RegistroCompras.xls"');
		header('Cache-Control: max-age=0');

		$objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
		$objWriter->save('php://output');
		exit;
?>
