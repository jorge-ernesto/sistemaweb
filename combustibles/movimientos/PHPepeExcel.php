<?php

ini_set("upload_max_filesize", "15M"); 
include_once('../include/Classes/PHPExcel.php');

class PHPepeExcel {

    /**
     * Comverts an excel to array 
     * @param unknown_type $filename
     */

    static function xls2array($filename) {
        $objReader = new PHPExcel_Reader_Excel5 ();
        $objReader->setReadDataOnly ( true );
        $obj = $objReader->load ( $filename );
        $cells = $obj->getActiveSheet ()->getCellCollection ();
        $coords = array ();
        foreach ( $cells as $cell ) {
            $value = $obj->getActiveSheet ()->getCell ( $cell )->getValue ();
            $coord = PHPExcel_Cell::coordinateFromString ( $cell );
            $col = $coord [1] - 1;
            $row = PHPExcel_Cell::columnIndexFromString ( $coord [0] ) - 1;
            $coords [$col] [$row] = $value;	
		//var_dump($coords [$col] [$row]);me da todos los datos del excel
        }
        return $coords;
    }

    /**
     * 
     * Converts Array to SQL INSERT statement
     * 
     * @param array $array
     * @param array $columns - Column map (null to avoid certain columns)
     * @param string $table - Name of the table to be inserted
     * @param array $parameters - associative array of key-values
     */

    static function array2sql($array, $columns, $table, $parameters) {
	global $sqlca;

        $i = 8;
	$contador = count($array) + 1;
	$fectete = '';

	while($i<=$contador){

		if(empty($array[$i][5]) || $array[$i][1] == 'RECIBO'){
			echo "nada\n";
		}else{

			$fecha[$i] = substr($array[$i][5],6,4)."-".substr($array[$i][5],3,2)."-".substr($array[$i][5],0,2);
			$created[$i] 		= $fecha[$i]." ".$array[$i][3];
			$createdby[$i] 		= '1';
			$updated[$i] 		= $fecha[$i]." ".$array[$i][3];
			$updatedby[$i]		= '1';
			$isactive[$i]  		= '1';
			$c_org_id[$i]		= '009';
			$c_currency_id[$i]	= '1';
			$issale[$i]  		= '1';

			if($array[$i][17] == 'Efectivo')
				$c_tendertype_id[$i] = '1';
			else
				$c_tendertype_id[$i] = '2';

			$status[$i]  		= '2';
//			$documentno[$i]  	= substr(($array[$i][9]),0,3)." - ".substr($array[$i][9],4);
			$documentno[$i]  	= $array[$i][9];

			if($array[$i][7] == 'Boleta'){
				$c_doctype_id[$i] 	= '35';
				$c_bpartner_id[$i]  	= '9999';
			}elseif($array[$i][7] == 'Factura' || $array[$i][7] == 'Venta Credito'){
				$c_doctype_id[$i] 	= '10';
				$c_bpartner_id[$i]  	= trim($array[$i][6]);
			}

			$documentserial[$i] 	= 'FFCF0157280';

			$sql = "INSERT INTO $table
						(" . implode ( ",", array_filter ( $columns, 'is_string' ) ) . "
				) VALUES (
						'{$created[$i]}',
						'{$createdby[$i]}',
						'{$updated[$i]}',
						'{$updatedby[$i]}',
						'{$isactive[$i]}',
						'{$c_org_id[$i]}',
						'{$c_bpartner_id[$i]}',
						'{$c_currency_id[$i]}',
						'{$issale[$i]}',
						'{$c_tendertype_id[$i]}',
						'{$status[$i]}',
						'{$documentno[$i]}',
						'{$c_doctype_id[$i]}',
						'{$documentserial[$i]}');";

			if ($sqlca->query($sql) < 0) {
				?><script>alert("<?php echo 'Error c_invoiceheader' ; ?> ");</script><?php
				return false;
			}
		}

		$i++;
	}	

        return $sql;
    }

    static function array2sqldet($array, $columns, $table, $parameters) {
	global $sqlca;

        $d = 8;
	$contador = count($array) + 1;

	while($d<=$contador){

		if(empty($array[$d][5]) || $array[$d][1] == 'RECIBO'){
			echo "nada\n";
		}else{

			$fecha[$d] = substr($array[$d][5],6,4)."-".substr($array[$d][5],3,2)."-".substr($array[$d][5],0,2);

			$created[$d] 		= $fecha[$d]." ".$array[$d][3];
			$createdby[$d] 		= '1';
			$updated[$d] 		= $fecha[$d]." ".$array[$d][3];
			$updatedby[$d]		= '1';
			$isactive[$d]  		= '1';
			$c_product_id[$d]  	= '11620308';
			$unitprice[$d]  	= $array[$d][15];
			$linetotal[$d]  	= $array[$d][21];
			$quantity[$d]  		= $array[$d][16];
		
//			$documentno[$d]  	= substr(($array[$d][9]),0,3)." - ".substr($array[$d][9],4);
			$documentno[$d]  	= $array[$d][9];
			$documentserial[$d] 	= 'FFCF0157280';

			$sqlid = "SELECT
					h.C_InvoiceHeader_ID
				FROM
					C_InvoiceHeader h
				WHERE
					h.DocumentNo = '{$documentno[$d]}'
					AND h.DocumentSerial = '{$documentserial[$d]}';";

			if ($sqlca->query($sqlid) < 0) {
				?><script>alert("<?php echo 'Error al traer el c_invoicedetail' ; ?> ");</script><?php
				return false;
			}

			$sqlca->query($sqlid);

			$bpr = $sqlca->fetchRow();
			$c_invoiceheader_id[$d] = $bpr[0];

			$sql = "INSERT INTO $table
						(" . implode ( ",", array_filter ( $columns, 'is_string' ) ) . "
				) VALUES (
						'{$created[$d]}',
						'{$createdby[$d]}',
						'{$updated[$d]}',
						'{$updatedby[$d]}',
						'{$isactive[$d]}',
						'{$c_invoiceheader_id[$d]}',
						'{$c_product_id[$d]}',
						{$unitprice[$d]},
						{$linetotal[$d]},
						{$quantity[$d]});";

			if ($sqlca->query($sql) < 0) {
				?><script>alert("<?php echo 'Error c_invoicedetail' ; ?> ");</script><?php
				return false;
			}
		}

		$d++;

	}	

        return $sql;
    }

    static function xls2sql($filename,  $columns, $table, $parameters) {
	return self::array2sql(self::xls2array($filename), $columns, $table, $parameters);
    }

    static function xls2sqldet($filename,  $columns, $table, $parameters) {
	return self::array2sqldet(self::xls2array($filename), $columns, $table, $parameters);
    }

}
