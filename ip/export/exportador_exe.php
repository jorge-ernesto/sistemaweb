<?php

require_once("dbsqlca.php");
require_once("exportador_funcion.php");

$db_host = "localhost";
$db_name = "opensoft";
$db_user = "postgres";
$db_password = "postgres";
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

$migerr = NULL;
$mssql = NULL;
$estadoActu = FALSE;

$array_close_period = array(
    "1" => "1", "2" => "1", //la molina grifo  //la molina market
    "4"=>5,"5"=>5,
    "6" => 6, //la comas grifo
    "7" => 8, "8" => 8,
    "9" => 10, "10" => 10,
    "11" => 11,
    "12" => 12,
    "13" => 13, "14" => 14);
$array_c_org = array();
$array_c_org_global = array();
$array_c_org_date = array();

/* $array_c_org = array(//los  id de las 
  1,2,
  6,
  7, 8,
  9, 10,
  11,
  13); */

function obtenerParametros() {

    global $sqlca;

    /* $sql = "	SELECT
      p1.par_valor,
      p2.par_valor,
      p3.par_valor,
      p4.par_valor
      FROM
      int_parametros p1
      LEFT JOIN int_parametros p2 ON p2.par_nombre = 'iridium_username'
      LEFT JOIN int_parametros p3 ON p3.par_nombre = 'iridium_password'
      LEFT JOIN int_parametros p4 ON p4.par_nombre = 'iridium_dbname'
      WHERE
      p1.par_nombre = 'iridium_server'";

      if ($sqlca->query($sql) < 0)
      return $defaultparams;

      if ($sqlca->numrows() != 1)
      return $defaultparams;

      //$sqlca->query($sql);

      $reg = $sqlca->fetchRow();




      return Array($reg[0],$reg[1],$reg[2],$reg[3]); */

    $defaultparams = Array("192.168.1.222:1433", "sa", "ene1/.34", "CENTRAL");
    return $defaultparams;
}
if ((isset($_REQUEST['do']) && $_REQUEST['do'] == 'precio') ) {
 try {
        $Parametros = obtenerParametros();

        $MSSQLDBHost = $Parametros[0];
        $MSSQLDBUser = $Parametros[1];
        $MSSQLDBPass = $Parametros[2];
        $MSSQLDBName = $Parametros[3];

        $mssql = mssql_connect($MSSQLDBHost, $MSSQLDBUser, $MSSQLDBPass);

        mssql_select_db($MSSQLDBName, $mssql);

        if ($mssql === FALSE) {
            $menx = "Error al conectarse a la base de datos de Energigas";
            throw new Exception($menx);
        }

        
        exportProcessPrecio();
        



        mssql_close($mssql);
    } catch (Exception $e) {
        echo "<strong>Problemas con la exportacion de producto " . $e->getMessage() . "</strong>";
    }
        /* -------- */   
}
else if ((isset($_REQUEST['do']) && $_REQUEST['do'] == 'export') && (isset($_REQUEST['ultD']))) {

    try {
        $Parametros = obtenerParametros();

        $MSSQLDBHost = $Parametros[0];
        $MSSQLDBUser = $Parametros[1];
        $MSSQLDBPass = $Parametros[2];
        $MSSQLDBName = $Parametros[3];

        $mssql = mssql_connect($MSSQLDBHost, $MSSQLDBUser, $MSSQLDBPass);

        mssql_select_db($MSSQLDBName, $mssql);

        if ($mssql === FALSE) {
            $menx = "Error al conectarse a la base de datos de Energigas";
            throw new Exception($menx);
        }

        /* -------- */

        $i = 0;
        foreach ($_REQUEST['data'] as $value) {
           $array_c_org[$i] = trim($value); 
           $i++;
        }
        exportProcess($_REQUEST['ultD']);
        if ($estadoActu == TRUE) {
            actualiza_fecha($_REQUEST['ultD'], $array_c_org[0]);
        }



        mssql_close($mssql);
    } catch (Exception $e) {
        echo "<strong>Problemas con la exportacion " . $e->getMessage() . "</strong>";
    }
//}
} else if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'exportall') {

    /* conexion */

    /* include 'connec.php';

      if (!$dbconn) {
      $menx = "Error al conectar a la Base de datos OPENSOFT\n";
      throw new Exception($menx);
      } */
    try {

        $Parametros = obtenerParametros();

        $MSSQLDBHost = $Parametros[0];
        $MSSQLDBUser = $Parametros[1];
        $MSSQLDBPass = $Parametros[2];
        $MSSQLDBName = $Parametros[3];

        $mssql = mssql_connect($MSSQLDBHost, $MSSQLDBUser, $MSSQLDBPass);

        mssql_select_db($MSSQLDBName, $mssql);

        if ($mssql === FALSE) {
            $menx = "Error al conectarse a la base de datos de Energigas";
            throw new Exception($menx);
        }
        /* -------- */

        $data = $_REQUEST['data'];
        $fecha_search = $_REQUEST['ultD'];

        foreach ($data as $c_org_id) {
            $array_c_org_global[] = $c_org_id;
        }

        foreach ($fecha_search as $fecha) {
            $array_c_org_date[] = $fecha;
        }

        $sali = 0;

        foreach ($array_c_org_global as $value_x) {
            $estadoActu = FALSE;
            $i = 0;
            unset($array_c_org);
            foreach ($value_x as $org) {
                $array_c_org[$i] = $org;
                $i++;
            }
            try {

                exportProcess($array_c_org_date[$sali]);
                $sali++;
                if ($estadoActu == TRUE) {
                    actualiza_fecha($array_c_org_date[$sali - 1], $array_c_org[0]);
                }
            } catch (Exception $er) {
                continue;
            }
        }

        mssql_close($mssql);
    } catch (Exception $e) {
        echo "<strong>Problemas con la exportacion " . $e->getMessage() . "</strong>";
    }
// pg_close($dbconn);
}

function actualiza_fecha($fecha_expo, $id_org) {
    global $sqlca;
//15/11/2012
    $dia = substr($fecha_expo, 0, 2);
    $mes = substr($fecha_expo, 3, 2);
    $ano = substr($fecha_expo, 6, 4);
    $fecha_e = $ano . "-" . $mes . "-" . $dia;
    $sql = "select id_remote from  mig_cowmap where c_org_id ={$id_org} limit 1;";
    if ($sqlca->query($sql) <= 0) {
        $menx = "ERROR NO SE ENCONTRO ID_REOMOTO ".$sql;
        echo "<strong>" . $menx . "<strong><br/>";
        return;
    }
    $data = $sqlca->fetchRow();
    $id_reomoto = $data['id_remote'];
    $sql = "INSERT INTO mig_export(
             created, systemdate, creado, mig_remote_id)
    VALUES ('now()', '$fecha_e', '1', $id_reomoto);";
// echo $sql;

    if ($sqlca->query($sql) < 0) {
        $menx = "ERROR NO SE INSERTO LA FECHA  DE LA ORG ";
        echo "<br/><strong>" . $menx . "  : " . $sql . "<strong><br/>";
    }
}

