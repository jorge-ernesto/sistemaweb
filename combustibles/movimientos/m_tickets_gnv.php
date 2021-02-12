<?php
// Descomentar estas líneas, cuando estamos en modo - development

error_reporting(-1);
ini_set('display_errors', 1);

// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

include_once('../include/Classes/PHPExcel.php');

date_default_timezone_set('America/Lima');

class TicketsGNVModel extends Model {

	function extension($archivo) {
		$partes = explode(".", $archivo);
		$extension = end($partes);
		return $extension;
	}

	function array_debug($data){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

    function managerTransaction($sNameTransaction){
    	global $sqlca;

    	try {
			$iStatusSQL = $sqlca->query($sNameTransaction);
			if ((int)$iStatusSQL < 0) {
			    return array(
			    	'sStatus' => 'danger',
			    	'sMessage' => 'Error al iniciar transaccion SQL - function managerTransaction(' . $sNameTransaction . ')',
	                'sMessageSQL' => $sqlca->get_error(),
			   	);
			}
		    return array(
		    	'sStatus' => 'success',
		    	'sMessage' => $sNameTransaction . ' ejecutado satisfactoriamente'
		   	);
    	} catch (Exception $e) {
	        return array(
	            'sStatus' => 'danger',
	            'sMessage' => 'problemas con transaccion ' . $sNameTransaction,
                'sMessagePHP' => $e->getMessage(),
	        );    		
    	}
    }

	function xls2array($filename) {
		$objReader = new PHPExcel_Reader_Excel5();
		$objReader->setReadDataOnly( true );
		$obj = $objReader->load( $filename );
		$cells = $obj->getActiveSheet()->getCellCollection();
		$coords = array();

		foreach ($cells as $cell) {
			$value = $obj->getActiveSheet()->getCell($cell)->getValue();
			$coord = PHPExcel_Cell::coordinateFromString($cell);
			$col = $coord[1] - 1;
			$row = PHPExcel_Cell::columnIndexFromString($coord[0]) - 1;
			$coords[$col][$row] = $value;
		}
		return $coords;
	}

	function array2sqlHeader($arrDataExcel, $arrDataHeader) {
		global $sqlca;
		
		if ( strtoupper($arrDataExcel[7][1]) != 'RECIBO'){
			$this->managerTransaction('ROLLBACK');

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Plantilla de excel incompatible - Header 1',
                'sMessageSQL' => $sqlca->get_error(),
            );			
		} else {
			//Eliminar valores de excel que no sirve
			unset($arrDataExcel[0],$arrDataExcel[1],$arrDataExcel[2],$arrDataExcel[3],$arrDataExcel[4],$arrDataExcel[5],$arrDataExcel[6],$arrDataExcel[7]);
			$counter = $arrDataHeader['iStartExcel'];
			foreach ($arrDataExcel as $row) {
				if ( isset($row[7]) && strtoupper($row[1]) != 'RECIBO' ) {
					//Get datetime from float
	                $EXCEL_DATE = $row[2];
	                $UNIX_DATE = ($EXCEL_DATE - 25569) * 86400;
	                $EXCEL_DATE = 25569 + ($UNIX_DATE / 86400);
	                $UNIX_DATE = ($EXCEL_DATE - 25569) * 86400;
	                $dFechaHoraEmision = gmdate("d-m-Y H:i:s", $UNIX_DATE);
	                $arrFechaHoraEmision = explode(' ', $dFechaHoraEmision);
	                $arrFechaEmision = explode('-', $arrFechaHoraEmision[0]);
	                $dFechaEmision = $arrFechaEmision[2] .'-'. $arrFechaEmision[1] .'-'. $arrFechaEmision[0];

					$created[$counter] = $dFechaEmision." ".$row[3];
					$createdby[$counter] = 1;
					$updated[$counter] = $dFechaEmision." ".$row[3];
					$updatedby[$counter] = 1;
					$isactive[$counter] = 1;
					$c_org_id[$counter] = $arrDataHeader['sCodeWarehouse'];
					$c_currency_id[$counter] = 1;
					$issale[$counter] = 1;

					if(strtoupper($row[18]) == 'EFECTIVO') {
						$c_tendertype_id[$counter] = 1;
					} else {
						$c_tendertype_id[$counter] = 2;
					}

					$status[$counter] = 2;

					$sTypeDocument = strtoupper($row[11]);
					if ($sTypeDocument == 'BOLETA') {
						$c_doctype_id[$counter] 	= 35;
						$c_bpartner_id[$counter] 	= 9999;
					} else if ($sTypeDocument == 'FACTURA' || $sTypeDocument == 'VENTA CREDITO') {
						$c_doctype_id[$counter] 	= 10;
						$c_bpartner_id[$counter] 	= trim($row[7]);
					}

					// get number and serial
					$arrDocumentNumberSerial = explode(' ', $row[12]);
					$documentno[$counter]  	= $arrDocumentNumberSerial[1];
					$documentserial[$counter] = $arrDocumentNumberSerial[0];

					$arrHeaderGNV[] = "
(
'" . pg_escape_string($created[$counter]) . "',
" . pg_escape_string($createdby[$counter]) . ",
'" . pg_escape_string($updated[$counter]) . "',
" . pg_escape_string($updatedby[$counter]) . ",
" . pg_escape_string($isactive[$counter]) . ",
" . pg_escape_string($c_org_id[$counter]) . ",
" . pg_escape_string($c_bpartner_id[$counter]) . ",
" . pg_escape_string($c_currency_id[$counter]) . ",
" . pg_escape_string($issale[$counter]) . ",
" . pg_escape_string($c_tendertype_id[$counter]) . ",
" . pg_escape_string($status[$counter]) . ",
'" . pg_escape_string($documentno[$counter]) . "',
" . pg_escape_string($c_doctype_id[$counter]) . ",
'" . pg_escape_string($documentserial[$counter]) . "'
)
					";
					$counter++;
				}// ./ if
			}// ./ Foreach
		}// ./ Else

		$sql = "INSERT INTO " . $arrDataHeader['sNameTable'] . " (" . implode ( ",", array_filter ( $arrDataHeader['arrTableColumns'], 'is_string' ) ) . ") VALUES " . implode(',', $arrHeaderGNV);

    	$iStatusSQL = $sqlca->query($sql);
    	if ( (int)$iStatusSQL < 0 ) {
    		$this->managerTransaction('ROLLBACK');

            return array(
                'sStatus' => 'danger',
                'sMessage' => ' Problemas al generar registros - Header 2' ,
                'sMessageSQL' => $sqlca->get_error(),
            );
    	}

        return array(
            'sStatus' => 'success',
            'sMessage' => 'Header registrado',
        );
        unset($arrHeaderGNV);
	}

	function array2sqlDetail($arrDataExcel, $arrDataDetail) {
		global $sqlca;

		if ( strtoupper($arrDataExcel[7][1]) != 'RECIBO'){
			$this->managerTransaction('ROLLBACK');

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Plantilla de excel incompatible - Header',
                'sMessageSQL' => $sqlca->get_error(),
            );			
		} else {
			//Eliminar valores de excel que no sirve
			unset($arrDataExcel[0],$arrDataExcel[1],$arrDataExcel[2],$arrDataExcel[3],$arrDataExcel[4],$arrDataExcel[5],$arrDataExcel[6],$arrDataExcel[7]);
			$counter = $arrDataDetail['iStartExcel'];
			foreach ($arrDataExcel as $row) {
				if ( isset($row[7]) && strtoupper($row[1]) != 'RECIBO' ) {
					//Get datetime from float
	                $EXCEL_DATE = $row[2];
	                $UNIX_DATE = ($EXCEL_DATE - 25569) * 86400;
	                $EXCEL_DATE = 25569 + ($UNIX_DATE / 86400);
	                $UNIX_DATE = ($EXCEL_DATE - 25569) * 86400;
	                $dFechaHoraEmision = gmdate("d-m-Y H:i:s", $UNIX_DATE);
	                $arrFechaHoraEmision = explode(' ', $dFechaHoraEmision);
	                $arrFechaEmision = explode('-', $arrFechaHoraEmision[0]);
	                $dFechaEmision = $arrFechaEmision[2] .'-'. $arrFechaEmision[1] .'-'. $arrFechaEmision[0];

					$created[$counter] = $dFechaEmision." ".$row[3];
					$createdby[$counter] = 1;
					$updated[$counter] = $dFechaEmision." ".$row[3];
					$updatedby[$counter] = 1;
					$isactive[$counter] = 1;
					$c_product_id[$counter] = '11620308';
					$unitprice[$counter] = (double)$row[17];
					$quantity[$counter] = (double)$row[18];
					$linetotal[$counter] = (double)$row[24];
			
					// get number and serial
					$arrDocumentNumberSerial 	= explode(' ', $row[12]);
					$documentno[$counter]  		= $arrDocumentNumberSerial[1];
					$documentserial[$counter] 	= $arrDocumentNumberSerial[0];

					$sql = "SELECT c_invoiceheader_id FROM c_invoiceheader WHERE documentserial = '" . $documentserial[$counter] . "' AND documentno = '" . $documentno[$counter] . "' LIMIT 1;";
					if($sqlca->query($sql) < 0) {
	    				$this->managerTransaction('ROLLBACK');

			            return array(
			                'sStatus' => 'danger',
			                'sMessage' => 'Problemas al obtener registro - ID Header',
			                'sMessageSQL' => $sqlca->get_error(),
			            );
					}
					$bpr = $sqlca->fetchRow();
					$c_invoiceheader_id[$counter] = $bpr[0];

					$arrDetailGNV[] = "
(
'" . pg_escape_string($created[$counter]) . "',
" . pg_escape_string($createdby[$counter]) . ",
'" . pg_escape_string($updated[$counter]) . "',
" . pg_escape_string($updatedby[$counter]) . ",
" . pg_escape_string($isactive[$counter]) . ",
" . pg_escape_string($c_invoiceheader_id[$counter]) . ",
'" . pg_escape_string($c_product_id[$counter]) . "',
" . pg_escape_string($unitprice[$counter]) . ",
" . pg_escape_string($linetotal[$counter]) . ",
" . pg_escape_string($quantity[$counter]) . "
)
					";
					$counter++;
				}// ./ if
			}// ./ Foreach
		}// ./ Else

		$sql = "INSERT INTO " . $arrDataDetail['sNameTable'] . " (" . implode ( ",", array_filter ( $arrDataDetail['arrTableColumns'], 'is_string' ) ) . ") VALUES " . implode(',', $arrDetailGNV);
    	$iStatusSQL = $sqlca->query($sql);
    	if ( (int)$iStatusSQL < 0 ) {
    		$this->managerTransaction('ROLLBACK');

            return array(
                'sStatus' => 'danger',
                'sMessage' => 'Problemas al generar registros - Detalle',
                'sMessageSQL' => $sqlca->get_error(),
            );
    	}

        return array(
            'sStatus' => 'success',
            'sMessage' => 'Detalle registrado',
        );
        unset($arrDetailGNV);
    }

	function xls2sql($arrDataHeader) {
		return self::array2sqlHeader(self::xls2array($arrDataHeader['sFileName']), $arrDataHeader);
	}

	function xls2sqldet($arrDataDetail) {
		return self::array2sqlDetail(self::xls2array($arrDataDetail['sFileName']), $arrDataDetail);
	}
}