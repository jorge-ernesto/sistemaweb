<?php

include ('/sistemaweb/include/dbsqlca.php');

/*
 * Autor: Nestor Hernandez Loli
 * Fecha: 10/02/2012
 * Para gestionar las impresiones LPR
 */

/**
 * Permite imprimir archivos vía Lpr
 * @param type $archivo la ruta del archivo a imprimir
 */
function imprimirLpr($archivo) {
    //Conectarse a una base datos postgre con PHP
    //Cadena de conexion
    $cadena = 'host=localhost user=postgres password=postgres port=5432 dbname=integrado';
    //Obtener una conexion
    $conn = pg_connect($cadena);
    //Obtener una consulta
    $query = pg_query('select * from list_impresoras') or die(pg_last_error());
    //Iterar sobre la consulta
    while ($row = pg_fetch_array($query)) {
        foreach ($row as $cell) {
            echo "$cell--";
        }
        echo "<br>";
    }
    //Liberar recursos

    pg_free_result($query);
    pg_close($conn);

    //$row = $sqlca->fetchRow();
    //  echo $row[0];
//El siguiente SQL puede ser modificado a acorde a obtener los datos de la impresora 
    /*  $sql = "select print_name, print_server from list_impresoras";
      $xsql = pg_query($coneccion, $sql);

      $nombreimp = trim(pg_result($xsql, 0, 0));
      $ip = trim(pg_result($xsql, 0, 1));

      $smbc = "lpr -H $ip -P $nombreimp $archivo";
      echo $smbc;

      exec($smbc);
     */
}

