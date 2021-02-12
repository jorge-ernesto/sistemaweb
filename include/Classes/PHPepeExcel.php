<?php
include('../include/Classes/PHPExcel.php');

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
        extract($parameters);// $limit, $start
        if (!$start) $start = 0 ; 
        $sql = "INSERT into $table ('" . implode ( "','", array_filter ( $columns, 'is_string' ) ) . "') VALUES ";
        $i = 0 ;
        foreach ( $array as $row_num => $row ) {
            $i++;
            if ( $i <= $start) continue;
            if ( is_numeric($limit) && $i > $start + $limit) break;
            $sql .= "(";
            foreach ( $row as $col_num => $cell ) {
                if ($columns [$col_num]) {
                    $sql .= "'$cell',";
                }
            }
            $sql = substr ( $sql, 0, - 1 );
            $sql .= ")";

        }
        return $sql;
    }

    static function xls2sql($filename,  $columns, $table, $parameters) {
        return self::array2sql(self::xls2array($filename), $columns, $table, $parameters);
    }
}
