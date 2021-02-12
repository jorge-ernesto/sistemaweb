<?php

require_once("dbsqlca.php");

$db_host = "localhost";
$db_name = "opensoft";
$db_user = "postgres";
$db_password = "postgres";
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);
$fecha_global_validar_duplica = "";
$c_org_duplica = "";
$datosql = array();


/* $array_close_period = array("7" => 8, "8" => 8);
  $array_c_org = array(7, 8); */

$COWMap = Array();
$ar_cr8 = '';
$PHE = 0;
$resultado_exe = array();


$debug = $_REQUEST['debug'];
$debug_data = $_REQUEST['debug_data'];
/*
 * 
 * $array_close_period = array(
  "1" => "1", "2" => "1", //la molina grifo  //la molina market
  "4" => 5, "5" => 5,
  "6" => 6, //la comas grifo
  "7" => 8, "8" => 8,
  "9" => 10, "10" => 10,
  "11" => 11,"18" => 11,
  "12" => 12,
  "13" => 13,
  "14" => 14,
  "16" => 16,
  "40" => 40
  );
 */


//solo deben IR los Estaciones COMBUSTIBLE
$array_sale_cobus = array("1" => 1, "2" => 2, "4" => 4, "6" => 6, "8" => 8, "10" => 10, "12" => 12, "14" => 14,
    "16" => 16, "18" => 18, "19" => 19, "21" => 21, "22" => 22, "24" => 24, "26" => 26);

$array_sale_market = array("1" => 1, "2" => 3, "4" => 5, "6" => 7, "8" => 9, "10" => 11, "12" => 13, "14" => 15,
    "16" => 17, "18" => 18, "19" => 20, "21" => 21, "22" => 23, "24" => 25, "26" => 27);

/*
 * Consideraciones para el proceso de migracion:
 *
 *  - El Business Partner Generico debe tener TaxID 00000000000
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */

function obtenerParametrosToIridium() {
    $sqlca = null;
    $db_host = "localhost";
    $db_name = "integrado";
    $db_user = "postgres";
    $db_password = "postgres";
    $sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

    $sql = "	SELECT
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
      p1.par_nombre = 'iridium_server';";





    if ($sqlca->query($sql) < 0) {
        return $defaultparams;
    }
    if ($sqlca->numrows() != 1) {
        return $defaultparams;
    }
    $reg = $sqlca->fetchRow();




    $defaultparams = Array($reg[0], $reg[1], $reg[2], $reg[3]);


    // $defaultparams = Array("192.168.1.222:1433", "sa", "ene1/.34", "CENTRAL");
    return $defaultparams;
}

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

require_once 'exportador_funcion.php';
/* insertamos 2 tablas a sql server d_ventas y d_compras */

function insertar_detalles($datosx, $ii) {

    $Parametros = obtenerParametros();

    $MSSQLDBHost = $Parametros[0];
    $MSSQLDBUser = $Parametros[1];
    $MSSQLDBPass = $Parametros[2];
    $MSSQLDBName = $Parametros[3];

    $mssql = mssql_connect($MSSQLDBHost, $MSSQLDBUser, $MSSQLDBPass);

    mssql_select_db($MSSQLDBName, $mssql);

    if ($mssql === FALSE) {
        $menx = "Error al conectarse a la base de datos de Energigas (CENTRAL)";
        return header('Location: export_control.php?ress=' . $menx . '&colorF=highvaluecell');
    }

    if ($ii == 1) {
        $tablax = "aux_energigas_d_ventas";
        $opx = "op_venta";
    } else {
        $tablax = "aux_energigas_d_compras";
        $opx = "op_compra";
    }

    for ($i = 0; $i < count($datosx); $i++) {
        $sql_d = "insert into
					" . $tablax . "(
							" . $opx . ",
							producto,
							cantidad,
							precio,
							dcto,
							importe
					)VALUES(
							" . $datosx[$i][0] . ",
							'" . $datosx[$i][1] . "',
							" . $datosx[$i][3] . ",
							" . $datosx[$i][2] . ",
							0,
							" . $datosx[$i][4] . "
					);";

        if (mssql_query($sql_d, $mssql) === FALSE) {
            echo $sql . "<br/>";
            $menx = "Error:: Problemas al insertar datos en la tabla -> " . $tablax;
            return header('Location: export_control.php?ress=' . $menx . '&colorF=highvaluecell');
        }
    }

    return true;
}

function actualiza_fecha($date) {

    include 'connec.php';

    if (!$dbconn) {
        $menx = 'Error al conectar a la Base de datos OPENSOFT\n';
    }

    //$dates=date('Y-m-d',strtotime($date));  
    $fecha_reg = date('Y-m-d H:i:s');
    $creado = date('d-m-Y H:i:s-a'); // H:i:s-a >> 'am' y 'pm'
    $sql = "update mig_export set created='" . $fecha_reg . "', systemdate='" . $date . "', creado='" . $creado . "' where mig_export_id=1";
    $res = pg_query($dbconn, $sql);

    if ($res === FALSE) {
        return false;
    } else {
        return TRUE;
    }
}

function getCentralizedData2($url) {
    ///
    $old = ini_set('default_socket_timeout', 120);
    $fh = fopen($url, 'rb');
    /* ini_set('default_socket_timeout', $old);
      stream_set_timeout($fh, 60);
      stream_set_blocking($fh, 0); */

    ///
    //$fh = fopen($url, 'rb');

    /*
      if ($fh === FALSE)
      return FALSE;

      $res = '';

      while (!feof($fh)) {
      $res .= fread($fh, 8192);
      }

      fclose($fh);
      return explode("\n", $res); */
}

function getCentralizedData($url) {
    ///
    error_log("CAPTURANDO INFORMACION DE SUCURSALES :" . $url . "=(" . date("Y-M-d h:i:ss"));
    $old = ini_set('default_socket_timeout', 120);
    $fh = fopen($url, 'rb');
    /* ini_set('default_socket_timeout', $old);
      stream_set_timeout($fh, 60);
      stream_set_blocking($fh, 0); */

    ///
    if ($fh === FALSE)
        return FALSE;
    $res = '';
    while (!feof($fh)) {
        $res .= fread($fh, 8192);
    }
    fclose($fh);
    $descomprimido = gzuncompress($res);
    error_log("*****FIN DE OBTENER DATO****** =" . date("Y-M-d h:i:ss"));
    return explode("\n", $descomprimido);
}

function insertCentralizedArray($TableName, $FieldList, $RawRows) {
    global $sqlca;

    $dbfl = implode(",", $FieldList);

    foreach ($RawRows as $rr) {
        $ffvl = explode("|", $rr);
        $sql = "INSERT INTO {$TableName} ({$FieldList}) VALUES (";
        for ($i = 1; $i <= count($dbfl); $i++)
            $sql .= (($i > 1) ? "," : "") . "\${$i}";
        $sql .= ");";
        if ($sqlca->query_params($sql, Array($ffvl)) < 0)
            return FALSE;
    }

    return TRUE;
}

function importProcess_Precio($cturl, $dt) {
    global $debug, $debug_data;


    echo $cturl . "?mod=DCP&from={$dt}&to={$dt}";

    $ctdata = getCentralizedData($cturl . "?mod=DCP&from={$dt}&to={$dt}");
    if (!is_array($ctdata) || importPrecio_cabecera_detalle($ctdata, $dt) == FALSE) {
        return FALSE;
    }
    return TRUE;
}

function importProcess($cturl, $dt) {
    global $sqlca, $COWMap, $migerr, $debug, $debug_data, $fecha_global_validar_duplica, $c_org_duplica, $datosql;

    $fecha_global_validar_duplica = $dt;
    $BeginDate = substr($fecha_global_validar_duplica, 0, 4) . "-" . substr($fecha_global_validar_duplica, 4, 2) . "-" . substr($fecha_global_validar_duplica, 6, 2);

    echo "*******INICIO CENTRALIZER ********<br/>";
    $sql = "SELECT
			m.ch_almacen,
			m.c_client_id,
			m.c_org_id,
			m.i_warehouse_id
		FROM
			mig_cowmap m;";

    if ($sqlca->query($sql) < 0) {
        $migerr = "Cannot initialize COWMap";
        return FALSE;
    }


    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $rR = $sqlca->fetchRow();
        $COWMap[$rR[0]] = Array($rR[1], $rR[2], $rR[3]);
    }

    //-------------------------------------
    //VERIFICAMOS QUE EL DIA ANTERIOR ESTE BIEN MIGRADO Y SI NO ESTA MIGRADO DOBLE QUE NO DEJE MIGRAR
    //-------------------------------------
//echo $cturl . "?mod=PC&from={$dt}&to={$dt}";
    $ctdata = getCentralizedData($cturl . "?mod=PC&from={$dt}&to={$dt}");
    if (!is_array($ctdata) || verifica_cierre($ctdata, $dt) == FALSE) {
        return FALSE;
    }
    if (isset($debug) && $debug == "si") {
        echo "PASO verifica_cierre ::<br/>";
    }


    $ctdata = getCentralizedData($cturl . "?mod=PC&from={$dt}&to={$dt}");
    if (!is_array($ctdata) || importDC($ctdata) == FALSE) {
        return FALSE;
    }
    if (isset($debug) && $debug == "si") {
        echo "PASO importDC ::<br/>";
    }




    $ctdata = getCentralizedData($cturl . "?mod=MH&from={$dt}&to={$dt}");
    if (!is_array($ctdata) || importMH($ctdata, $cturl) == FALSE) {
        if (isset($debug_data) && $debug_data == "si") {
            echo "**************Informacion Recolectada de MH*****************<br/>";
            echo $cturl . "?mod=MH&from={$dt}&to={$dt}";
            print_r($ctdata);
            echo "<br/>*************************************************<br/>";
        }
        return FALSE;
    }
    if (isset($debug) && $debug == "si") {
        echo "Modulo importMH se ejecuto satisfactoria mente ....<br/>";
    }


    $ctdata = getCentralizedData($cturl . "?mod=MD&from={$dt}&to={$dt}");
    if (!is_array($ctdata) || importMD($ctdata, $cturl) == FALSE) {
        if (isset($debug_data) && $debug_data == "si") {
            echo "**************Informacion Recolectada de MD*****************<br/>";
            echo $cturl . "?mod=MD&from={$dt}&to={$dt}";
            var_dump($ctdata);
            echo "<br/>*************************************************<br/>";
        }
        return FALSE;
    }
    if (isset($debug) && $debug == "si") {
        echo "Modulo importMD se ejecuto satisfactoria mente ....<br/>";
    }



    $ctdata = getCentralizedData($cturl . "?mod=IH&from={$dt}&to={$dt}");
    if (!is_array($ctdata) || importIH($ctdata, $cturl) == FALSE) {
        if (isset($debug_data) && $debug_data == "si") {
            echo "**************Informacion Recolectada de IH*****************<br/>";
            echo $cturl . "?mod=IH&from={$dt}&to={$dt}";
            var_dump($ctdata);
            echo "<br/>*************************************************<br/>";
        }
        return FALSE;
    }


    if (isset($debug) && $debug == "si") {
        echo "PASO importIH ::<br/>";
    }

    $c_org_duplica = substr($c_org_duplica, 0, -1) . "";
    echo "Centro de costos :" . $c_org_duplica . "<br/><br/>";
    $estado_fecha_anterior = verificar_fecha_exportacion($BeginDate, $c_org_duplica);


    if ($estado_fecha_anterior != TRUE) {
        echo "No se Centralizara el dia ,por que tienes duplicado la informacion ->><br/><br/>";
        return FALSE;
    }


    $ctdata = getCentralizedData($cturl . "?mod=ID&from={$dt}&to={$dt}");
    if (!is_array($ctdata) || importID($ctdata, $cturl) == FALSE) {
        if (isset($debug) && $debug == "si") {
            echo "<br/>**************Informacion Recolectada de ID*****************<br/>";
            echo $cturl . "?mod=ID&from={$dt}&to={$dt}";
            var_dump($ctdata);
            echo "<br/>*************************************************<br/>";
        }
        return FALSE;
    }
    if (isset($debug) && $debug == "si") {
        echo "PASO importID ::<br/>";
    }


    $ctdata = getCentralizedData($cturl . "?mod=IT&from={$dt}&to={$dt}");
    if (!is_array($ctdata) || importIT($ctdata, $cturl) == FALSE) {
        return FALSE;
    }
    if (isset($debug) && $debug == "si") {
        echo "PASO importIT ::<br/>";
    }





    $cantidad_migrada = verificar_info_duplicada($BeginDate, $c_org_duplica);
    if ($cantidad_migrada == 1) {
        return TRUE;
    } else {
        return FALSE;
    }


    return FALSE;
}

//Aqui empieza a insertar a las tablas de la base de datos opensoft
function importPrecio_cabecera_detalle($ctdata, $cturl) {
    global $sqlca, $COWMap, $migerr;
    $aregloid_nombre = array();
    $r = 0;
    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);
        if (empty($cr[0])) {
            continue;
        }
        echo "Ejecunatdo la insercion\n";
        $sql = "";
        $value = trim($cr[0]);
        $sql = "SELECT 
                    c_product_id,name 
                FROM c_product 
                WHERE value like '%" . $value . "%' limit 1 ;";
        //   echo $sql."<br/>";

        if ($sqlca->query($sql) <= 0) {
            echo "no hay " . $cr[0] . " - -" . $r++ . "<br/>";
            continue;
        } else {
            echo "=>" . $r++ . "  -- " . $cr[0] . "<br/>";
        }

        $bpr = $sqlca->fetchRow();
        $id_producto = $bpr['c_product_id'];
        $nombre = $bpr['name'];
        $aregloid_nombre[] = array("id" => $id_producto, "nombre" => $nombre, "precio" => $cr[1], "fecha" => $cr[2]);
    }
    foreach ($aregloid_nombre as $fila) {
        echo "Insertando Producto " . $fila['id'] . "\n";
        InsertarPrecio($fila['id'], $fila['nombre'], $fila['precio'], $fila['fecha']);
    }
    return TRUE;
}

function InsertarPrecio($id_producto, $nombre, $precio, $fecha) {
    global $sqlca, $COWMap, $migerr;
    include 'connec.php';

    if (!$dbconn) {
        $migerr = 'Error al conectar a la Base de datos OPENSOFT\n';
        return FALSE;
    }

    $sqlquery = "SELECT 
                    * 
                FROM c_pricelistdetail 
                 WHERE  c_product_id ='{$id_producto}' ;";

    if ($sqlca->query($sqlquery) <= 0) {
        $fecha = empty($fecha) ? date('Y-m-d') : $fecha;
        $sql = "INSERT INTO c_pricelistheader(
                            created,
                            createdby,
                            updated,
                            updatedby, 
                            isactive,
                            name,
                            description,
                            c_client_id,
                            c_currency_id)
            VALUES (
                    '{$fecha}',
                      1,
                     '{$fecha}',
                      1,
                      1, 
                     '" . str_replace("'", " ", $nombre) . "',
                     '" . str_replace("'", " ", $nombre) . "',
                      1,
                      1);";
        $res = pg_query($dbconn, $sql);

        if ($res === FALSE) {
            echo $sql . "<br/>";
            $migerr = "Database error creating Business Partner no inserto" . $sql;
            return;
        }
        $res = pg_query($dbconn, "SELECT max(c_pricelistheader_id) FROM c_pricelistheader;");
        if ($res === FALSE) {
            return;
        }
        $data = pg_fetch_array($res);
        $id_price_header = $data[0];
        $sql = " INSERT INTO c_pricelistdetail(
            
            created,
            createdby,
            updated,
            updatedby, 
            isactive,
            c_pricelistheader_id, 
            c_product_id,
            minprice, 
            defprice, 
            maxprice)
    VALUES ('{$fecha}',
            1,
            '{$fecha}',
            1,
            1, 
            {$id_price_header},
            '{$id_producto}',  
            '{$precio}',
            '{$precio}', 
            '{$precio}');";

        $res = pg_query($dbconn, $sql);
        if ($res === FALSE) {
            echo $sql . "<br/>";
            $migerr = "Database error creating Business Partner no inserto" . $sql;
            return;
        }
    }
}

function sanear_string($string) {

    $string = trim($string);

    $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string
    );

    $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string
    );

    $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string
    );

    $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string
    );

    $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string
    );

    $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string
    );

    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
            array("\\", "¨", "º", "-", "~",
        "#", "@", "|", "!", "\"",
        "·", "$", "%", "&", "/",
        "(", ")", "?", "'", "¡",
        "¿", "[", "^", "`", "]",
        "+", "}", "{", "¨", "´",
        ">", "< ", ";", ",", ":",
        ".", " "), '', $string
    );


    return $string;
}

function slug($cadena) {
    $table = array('Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ñ' => 'N',
        'Ó' => 'O', 'Ú' => 'U', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ñ' => 'n', 'ó' => 'o', 'ú' => 'u', '@' => '');

    $a = strtr($cadena, $table);

    echo "Buscar :" . htmlspecialchars($cadena, ENT_QUOTES) . "<br/>";
    return $a;
}

function importBP($bpval, $cturl, $c_client_id) {
    global $sqlca, $COWMap, $migerr;
    $sql = "select c_bpartner_id from c_bpartner where taxid like '%{$bpval}%' and c_client_id='$c_client_id';";

    $sqlca->query($sql);
    $numrow = $sqlca->numrows();
    $id_barther = "-1";
    $tax = $sqlca->fetchRow(); // 
    $id_tmp_cbather = $tax[0];
    if ($numrow == 0) {
        error_log("CREANDO CLIENTE EXTRA PARA EL CLIENTE:" . $c_client_id . " CON RUC :" . $bpval);
        $ctdata = getCentralizedData($cturl . "?mod=BI&sk={$bpval}");

        if (!is_array($ctdata)) {
            $migerr = "Cannot get centralized data for Business Partner '{$bpval}'";
            return FALSE;
        }

        if (count($ctdata) == 0) {
            $migerr = "Centralized data is not valid for Business Partner '{$bpval}'";
            return FALSE;
        }

        $cr = explode("|", $ctdata[0]);

        include 'connec.php';

        if (!$dbconn) {
            $migerr = 'Error al conectar a la Base de datos OPENSOFT\n';
            return FALSE;
        }


        if ($bpval == "GENERIC") {
            $cr[0] = "GENERIC";
        } elseif ($bpval == "SELF") {
            $cr[0] = "SELF";
        }

        if ($cr[0] == "" or $cr[0] == " " or empty($cr[0]) or empty($ctdata)) {
            $cr[0] = $bpval;
        }
        $taxid_bus = trim($cr[0]);
        $sql = "select count(taxid) from c_bpartner where taxid like '%{$taxid_bus}%' and c_client_id='$c_client_id';";

        $sqlca->query($sql);

        $tax = $sqlca->fetchRow();

        if ($tax[0] <= 0) {
            $taxid = trim($cr[0]);
            $sql = "INSERT INTO
				C_BPartner(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						C_Client_ID,
						Name,
						Description,
						TaxID,
						Value,
						IsWorker
				) VALUES (
						now(),
						0,
						now(),
						0,
						1,
						{$c_client_id},
						'" . addslashes($cr[1]) . "',
						'',
						'{$taxid}',
						'{$taxid}',
						0
				);";
        }


        $res = pg_query($dbconn, $sql);

        if ($res === FALSE) {
            echo "<br/><br/><br/>Procedimiento para el segundo Intento ::::" . $sql . "<br/><br/>";
            echo "Antes" . $cr[1] . "\n";
            $nombre_mod = slug($cr[1]);
            echo "con slug :" . $nombre_mod . "\n";
            $sql2 = 'INSERT INTO
				C_BPartner(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						C_Client_ID,
						Name,
						Description,
						TaxID,
						Value,
						IsWorker
				) VALUES (
						now(),
						0,
						now(),
						0,
						1,
						' . $c_client_id . ',
						"' . $nombre_mod . '",
						"-",
						"' . $taxid . '",
						"' . $taxid . '",
						0
				);';
            $res2 = pg_query($dbconn, $sql2);

            if ($res2 === FALSE) {

                $sql2 = "INSERT INTO
				C_BPartner(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						C_Client_ID,
						Name,
						Description,
						TaxID,
						Value,
						IsWorker
				) VALUES (
						now(),
						0,
						now(),
						0,
						1,
						{$c_client_id},
						'{$taxid}',
						'',
						'{$taxid}',
						'{$taxid}',
						0
				);";
                $res2 = pg_query($dbconn, $sql2);

                if ($res2 === FALSE) {
                    echo "ERROR al Insertar Cliente :" . $sql2 . "<br/>";
                    $migerr = "Database error creating Business Partner no inserto" . $sql2;
                    return FALSE;
                }
            }
        }
        $sqlca->query("SELECT max(C_BPartner_id) FROM C_BPartner;");
        $lr = $sqlca->fetchRow();
        $id_barther = $lr[0];
    } else {

        $id_barther = $id_tmp_cbather;
        //echo "Codigo Existe:" . $id_barther . "<br/>";
    }

    //$migerr = "Error al insertar cbpartner ".$sql;



    return $id_barther;
}

function importProduct($pval, $cturl, $c_client_id) {
    global $sqlca, $COWMap, $migerr;
    $valor_buscar = trim($pval);
    $sql = "SELECT c_product_id from c_product
               WHERE value like '%$valor_buscar%'  ;";

    $sqlca->query($sql);
    $numrow = $sqlca->numrows();
    $_c_producto_id = "-1";
    $tax = $sqlca->fetchRow(); // 
    $_c_producto_id_tmp = $tax[0];


    if ($numrow == 0) {

        error_log("CREANDO PRODUCTO VALOR:" . $valor_buscar);
        $ctdata = getCentralizedData($cturl . "?mod=PI&sk={$pval}");

        if (!is_array($ctdata)) {
            $migerr = "Cannot get centralized data for Product '{$pval}'";
            return FALSE;
        }

        if (count($ctdata) == 0) {
            $migerr = "Centralized data is not valid for Product '{$pval}'";
            return FALSE;
        }

        $cr = explode("|", $ctdata[0]);

        $sql = "SELECT
			C_TaxGroup_ID
		FROM
			C_TaxGroup
		WHERE
			C_Client_ID = {$c_client_id};";

        if ($sqlca->query($sql) <= 0) {
            echo $sql . "<br/>";
            $migerr = "Cannot get Tax Group for new Product '{$pval}'<br>";
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_taxgroup_id = $bpr[0];

        $sql = "SELECT
			f.C_ProductFamily_ID
		FROM
			C_ProductFamily f
			JOIN C_ProductType t USING (C_ProductType_ID)
		WHERE
			t.C_Client_ID = {$c_client_id};";

        if ($sqlca->query($sql) <= 0) {
            echo $sql . "<br/>";
            $migerr = "Cannot get Product Family for new Product '{$pval}'";
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_productfamily_id = $bpr[0];

        $sql = "SELECT
			C_ProductUOM_ID
		FROM
			C_ProductUOM
		WHERE
			C_Client_ID = {$c_client_id};";

        if ($sqlca->query($sql) <= 0) {
            echo $sql . "<br/>";
            $migerr = "Cannot get Product UOM for new Product '{$pval}'";
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_productuom_id = $bpr[0];

        $sql = "SELECT
			C_ProductGroup_ID
		FROM
			C_ProductGroup
		WHERE
			C_Client_ID = {$c_client_id};";

        if ($sqlca->query($sql) <= 0) {
            echo $sql . "<br/>";
            $migerr = "Cannot get Product Group for new Product '{$pval}'";
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_productgroup_id = $bpr[0];

        include 'connec.php';

        if (!$dbconn) {
            $migerr = 'Error al conectar a la Base de datos OPENSOFT\n';
            return FALSE;
        }

        // $sql = "select count(value) from c_product where value = '{$cr[0]}';";//$c_taxgroup_id
        $sql = "SELECT count(value) from c_product p inner join c_taxgroup tx on p.c_taxgroup_id=tx.c_taxgroup_id
            where value = trim('{$cr[0]}') and tx.c_taxgroup_id='{$c_taxgroup_id}'
                ;"; //$c_taxgroup_id

        $sqlca->query($sql);

        $pro = $sqlca->fetchRow();

        if ($pro[0] <= 0 or $pro == "0" or $pro[0] == 0) {
            $nombre_raz = str_replace("'", " ", $cr[1]);
            // echo "Cadena sub:" . substr($nombre_raz, 0, strpos($nombre_raz, "'"));
            $sql = "INSERT INTO
				C_Product(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						C_ProductFamily_ID,
						C_ProductUOM_ID,
						C_ProductGroup_ID,
						C_TaxGroup_ID,
						Name,
						IsComposite,
						IsSellable,
						IsInventory,
						Value
				) VALUES (
						now(),
						0,
						now(),
						0,
						1,
						{$c_productfamily_id},
						{$c_productuom_id},
						{$c_productgroup_id},
						{$c_taxgroup_id},
						'" . utf8_encode($nombre_raz) . "',
						0,
						1,
						1,
						'{$cr[0]}'
				);";
        }
        error_log("C_Product:" . $sql);
        $res = pg_query($dbconn, $sql);

        if ($res === FALSE) {

            $rz1 = substr($nombre_raz, 0, strpos($nombre_raz, "'"));
            $rz2 = substr($nombre_raz, strpos($nombre_raz, "'") + 1);
            $nombre_raz = $rz1 . " " . $rz2;

            $sql = "INSERT INTO
				C_Product(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						C_ProductFamily_ID,
						C_ProductUOM_ID,
						C_ProductGroup_ID,
						C_TaxGroup_ID,
						Name,
						IsComposite,
						IsSellable,
						IsInventory,
						Value
				) VALUES (
						now(),
						0,
						now(),
						0,
						1,
						{$c_productfamily_id},
						{$c_productuom_id},
						{$c_productgroup_id},
						{$c_taxgroup_id},
						'" . utf8_encode($nombre_raz) . "',
						0,
						1,
						1,
						'{$cr[0]}'
				);";

            error_log("C_Product:" . $sql);
            $res = pg_query($dbconn, $sql);




            if ($res === FALSE) {
                echo $sql . "<br/>";
                $migerr = "Database error creating Product '{$cr[0][0]}' - " . $sql;
                return FALSE;
            }
        }

        $sqlca->query("SELECT max(c_product_id) FROM C_Product;");
        $lr = $sqlca->fetchRow();
        $_c_producto_id = $lr[0];
    } else {
        $_c_producto_id = $_c_producto_id_tmp;
    }

    return $_c_producto_id;
}

function importIH($ctdata, $cturl) {
    global $sqlca, $COWMap, $migerr, $debug, $c_org_duplica, $datosql;

    if (isset($debug) && $debug == "si") {
        echo "--------detalle de informacion importIH----------<br/>";
    }
    echo "Cantidad de IH=" . count($ctdata) . "<br/>";

    foreach ($ctdata as $crv) {

        $cr = explode("|", $crv);
        /* echo  implode("|-|", $cr)."el org id es :".$COWMap[$cr[1]][1];

          echo "-----------<br/>"; */
        if (count($cr) <= 1)
            break;

        if (!isset($COWMap[$cr[1]])) {
            continue;
        }
        $valor_id_org = trim($COWMap[$cr[1]][1]);
        if (strlen($valor_id_org) > 0 && $c_org_duplica != $valor_id_org . ",") {

            $c_org_duplica.="" . trim($COWMap[$cr[1]][1]) . ",";
        }


        $c_client_id = $COWMap[$cr[1]][0];
        //$c_org_id	= $COWMap[$cr[1]][1];
        $i_warehouse_id = $COWMap[$cr[1]][2];
        $TaxID = "";
        $c_bpartner_id = "";
//---------------------------

        if (($cr[8] == "GENERIC" or $cr[8] != "") and (strlen($cr[8]) > 6 )) {
            $c_bpartner_id = importBP($cr[8], $cturl, $c_client_id);
            $TaxID = trim($cr[8]);
        } else {
            if (strlen($cr[8]) == 0 or strlen($cr[8]) <= 8) {
                $TaxID = "GENERIC";
                $c_bpartner_id = importBP("GENERIC", $cturl, $c_client_id);
            } else {
                echo "Error se esta ejecutando la condicion Restante(" . $cr[8] . ") <br/>";
                $c_bpartner_id = "ERROR_" . $cr[8];
            }
        }

///----------------------------
        if ($cr[5] != "") {
//             $sql = "SELECT
//              c_org_id
//              FROM
//              c_documentserial
//              WHERE
//              documentserial = trim('{$cr[5]}')";
//
//              if ($sqlca->query($sql) <= 0) {
//              $migerr = "IH error c_org_id " . $sql;
//              return FALSE;
//              }
//
//              $bpr = $sqlca->fetchRow();
//              $c_org_id = $bpr[0]; 
            $c_org_id = $COWMap[$cr[1]][1];
            $documentserial = $cr[5];
        } else {
            $sql = "SELECT
				c_org_id
			FROM
				mig_cowmap
			WHERE
				ch_almacen = '{$cr[0]}'";

            if ($sqlca->query($sql) <= 0) {
                if (isset($debug) && $debug == "si") {
                    echo "importIH->(1):ERROR al ejecutar la consulta :" . $sql . "<br/>";
                }


                return FALSE;
            }

            $bpr = $sqlca->fetchRow();
            $c_org_id = $bpr[0];
            $documentserial = "";
        }

        /* $sql = "SELECT
          C_BPartner_ID
          FROM
          C_BPartner
          WHERE
          C_Client_ID = {$c_client_id}
          AND trim(TaxID) = '{$TaxID}';";

          if ($sqlca->query($sql) <= 0) {
          if (isset($debug) && $debug == "si") {
          echo "Error del IH:<br/>$sql  <br/>";
          }
          return FALSE;
          }

          $bpr = $sqlca->fetchRow();
          $c_bpartner_id = $bpr[0]; */

        $sql = "SELECT
			C_TenderType_ID
		FROM
			C_TenderType
		WHERE
			C_Client_ID = {$c_client_id}
			AND IsCredit = " . (($cr[9] == "0") ? 0 : 1) . ";";

        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {
                echo "importIH->(2):ERROR al ejecutar la consulta :" . $sql . "<br/>";
            }
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_tendertype_id = $bpr[0];

        if ($cr[6] == "B")
            $cr[6] = "35";
        else if ($cr[6] == "F")
            $cr[6] = "10";

        $sql = "SELECT
			C_DocType_ID
		FROM
			C_DocType
		WHERE
			Value = '{$cr[6]}';";

        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {

                echo "importIH->(3):ERROR al ejecutar la consulta :" . $sql . "<br/>";
            }
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_doctype_id = $bpr[0];
        $documentserial = trim($documentserial);

        $sql = "INSERT INTO
				C_InvoiceHeader(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						C_Org_ID,
						C_BPartner_ID,
						C_Currency_ID,
						IsSale,
						C_TenderType_ID,
						Status,
						DocumentNo,
						C_DocType_ID,
						documentserial,
                                                ap
				) VALUES (
						'{$cr[2]}',
						1,
						'{$cr[3]}',
						1,
						'{$cr[11]}',
						{$c_org_id},
						{$c_bpartner_id},
						1,
						{$cr[7]},
						{$c_tendertype_id},
						2,
						'" . addslashes($cr[4]) . "',
						{$c_doctype_id},
						'" . addslashes($documentserial) . "',
                                                    '" . $cr[10] . "'
				);";
//echo "<br/>INVOCEHEADER: ".$sql."<br/>";
          error_log("INVOICEHEADER:" . $sql);
        if ($sqlca->query($sql) < 0) {

            if (isset($debug) && $debug == "si") {

                echo "importIH->(4):ERROR al ejecutar la consulta :" . $sql . "<br/>";
            }
            return FALSE;
        }
    }

    return TRUE;
}

function importID($ctdata, $cturl) {
    global $sqlca, $COWMap, $migerr, $debug;
    if (isset($debug) && $debug == "si") {
        echo "--------detalle de informacion importID----------<br/>";
    }

    foreach ($ctdata as $crv) {

        $cr = explode("|", $crv);

        if (count($cr) <= 1)
            break;

        if (!isset($COWMap[$cr[1]])) {
            continue;
        }

        $c_client_id = $COWMap[$cr[1]][0];
        $c_org_id = $COWMap[$cr[1]][1];
        $i_warehouse_id = $COWMap[$cr[1]][2];

        $sql = "SELECT
			h.C_InvoiceHeader_ID,
			h.created,
			h.updated
		FROM
			C_InvoiceHeader h
			JOIN C_Org o USING (C_Org_ID)
		WHERE
			o.C_Client_ID = {$c_client_id}
			AND h.DocumentNo = '" . addslashes(trim($cr[2])) . "'
			AND h.DocumentSerial = '" . addslashes(trim($cr[3])) . "';";
//    AND h.c_org_id={$c_org_id}
        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {
                echo "importID->(1):ERROR al ejecutar la consulta :" . $sql . "<br/>";
                error_log("INVOICEDETAIL:" . $sql);
            }
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_invoiceheader_id = $bpr[0];
        $created = $bpr[1];
        $updated = $bpr[2];

        $sql = "SELECT
			p.C_Product_ID
		FROM
			C_Product p
			JOIN C_TaxGroup t USING (C_TaxGroup_ID)
		WHERE
			t.C_Client_ID = {$c_client_id}
			AND p.Value = '{$cr[5]}';";




        if ($sqlca->query($sql) <= 0) {
            $c_product_id = importProduct($cr[5], $cturl, $c_client_id);
            if ($c_product_id === FALSE) {
                if ($migerr == NULL)
                    echo "Error no se pudo encontrar el siguiente PRODUCTO '{$cr[5]}'";

                return FALSE;
            }
        }else {
            $bpr = $sqlca->fetchRow();
            $c_product_id = $bpr[0];
        }

        $sql = "INSERT INTO
				C_InvoiceDetail(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						C_InvoiceHeader_ID,
						C_Product_ID,
						UnitPrice,
						LineTotal,
						Quantity
				) VALUES (
						'$created',
						1,
						'$updated',
						1,
						1,
						{$c_invoiceheader_id},
						{$c_product_id},
						{$cr[6]},
						{$cr[8]},
						{$cr[7]}
				);";
        error_log("INVOICEDETAIL:" . $sql);
        if ($sqlca->query($sql) < 0) {

            if (isset($debug) && $debug == "si") {
                if (isset($debug) && $debug == "si") {
                    echo "importID->(3):ERROR al ejecutar la consulta :" . $sql . "<br/>";
                }
            }
            return FALSE;
        }
    }

    return TRUE;
}

function importIT($ctdata) {
    global $sqlca, $COWMap, $migerr, $debug;
    if (isset($debug) && $debug == "si") {
        echo "--------detalle de informacion importIT----------<br/>";
    }

    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);
        if (count($cr) <= 1)
            break;

        if (!isset($COWMap[$cr[1]])) {
            continue;
        }

        $c_client_id = $COWMap[$cr[1]][0];
        $c_org_id = $COWMap[$cr[1]][1];
        $i_warehouse_id = $COWMap[$cr[1]][2];

        $sql = "SELECT
			h.C_InvoiceHeader_ID,
			h.created,
			h.updated
		FROM
			C_InvoiceHeader h
			JOIN C_Org o USING (C_Org_ID)
		WHERE
			o.C_Client_ID = {$c_client_id}
			AND DocumentNo = '" . addslashes(trim($cr[2])) . "'
			AND DocumentSerial = '" . addslashes(trim($cr[3])) . "';";

        if ($sqlca->query($sql) <= 0) {


            if (isset($debug) && $debug == "si") {

                echo "importIT->(1):ERROR al ejecutar la consulta :" . $sql . "<br/>";
            }
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_invoiceheader_id = $bpr[0];
        $created = $bpr[1];
        $updated = $bpr[2];

        $sql = "SELECT
			C_Tax_ID
		FROM
			C_Tax
		WHERE
			C_Client_ID = {$c_client_id};";

        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {

                echo "importIT->(2):ERROR al ejecutar la consulta :" . $sql . "<br/>";
            }
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_tax_id = $bpr[0];

        $sql = "INSERT INTO
				C_InvoiceTax(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						C_InvoiceHeader_ID,
						C_Tax_ID,
						BaseAmount,
						TaxAmount
				) VALUES (
						'$created',
						1,
						'$updated',
						1,
						1,
						{$c_invoiceheader_id},
						{$c_tax_id},
						{$cr[6]},
						{$cr[7]}
				);";
        error_log("INVOICETAX:" . $sql);
        if ($sqlca->query($sql) < 0) {
            if (isset($debug) && $debug == "si") {
                echo "importIT->(3):ERROR al ejecutar la consulta :" . $sql . "<br/>";
            }
            return FALSE;
        }
    }

    return TRUE;
}

function insertaOrWh($cliente, $org, $descrip) {
    global $sqlca, $COWMap, $migerr;

    $cod_idx = 0;
    $xxid = 0;

    include 'connec.php';

    if (!$dbconn) {
        $migerr = 'Error al conectar a la Base de datos OPENSOFT\n';
        return FALSE;
    }

    $sql = "select max(c_org_id) from c_org";

    $res = pg_query($dbconn, $sql);

    if ($res === FALSE) {
        $migerr = "Database error creating Product '{$cr[0][0]}' - " . $sql;
        return $sql;
    }

    $row = pg_fetch_row($res);
    $xxid = $row[0];
    $cod_idx = $xxid + 1;

    $sql = "INSERT INTO
			c_org(
				c_org_id,
				Created,
				CreatedBy,
				Updated,
				UpdatedBy,
				IsActive,
				c_client_id,
				name,
				description,
				value,
				postaladdress
			) VALUES (
				{$cod_idx},
				now(),
				0,
				now(),
				0,
				1,
				{$cliente},
				'of c',
				'of c',
				0,
				'-------'
			);";

    $res = pg_query($dbconn, $sql);

    if ($res === FALSE) {
        $migerr = "Database error creating c_org '{$cr[0][0]}' - " . $sql;
        return $sql;
    }

    $sql = "INSERT INTO
			i_warehouse(
					i_warehouse_id,
					Created,
					CreatedBy,
					Updated,
					UpdatedBy,
					IsActive,
					c_org_id,
					name,
					description,
					isinternal,
					isprovider
			) VALUES (
					{$cod_idx},
					now(),
					0,
					now(),
					0,
					1,
					{$org},
					'Ventas',
					'{$descrip}',
					0,
					0
			);";

    $res = pg_query($dbconn, $sql);

    if ($res === FALSE) {
        $migerr = "Database error creating i_warehouse '{$cr[0][0]}' - " . $sql;
        return $sql;
    }

    return $cod_idx;
}

function importMH($ctdata, $cturl) {
    global $sqlca, $COWMap, $migerr, $debug;

    if (isset($debug) && $debug == "si") {
        echo "--------detalle de informacion importMH----------<br/>";
    }

    $fila = 0;
    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);
        if (count($cr) <= 1) {
            if (isset($debug) && $debug == "si") {
                echo "No se encontro datos en importMH .>$fila<br/>";
            }
            break;
        }

        $fila++;

        if (!isset($COWMap[$cr[1]])) {
            continue;
        }

        $c_client_id = $COWMap[$cr[1]][0];
        $c_org_id = $COWMap[$cr[1]][1];
        $i_warehouse_id = $COWMap[$cr[1]][2];

        if ($cr[4] == "SELF" or $cr[4] != " ") {
            $c_bpartner_id = importBP($cr[4], $cturl, $c_client_id);
        } else {
            $migerr = "Error" . $cr[4];
        }

        $sql = "SELECT
			C_DocType_ID
		FROM
			C_DocType
		WHERE
			Value = '{$cr[9]}';";

        if ($sqlca->query($sql) <= 0) {


            if (isset($debug) && $debug == "si") {
                echo "Error del MH:<br/>$sql  <br/>";
            }
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_doctype_id = $bpr[0];

        $sql = "SELECT
			w.I_Warehouse_ID
		FROM
			I_Warehouse w
			JOIN C_Org o USING (C_Org_ID)
		WHERE
			o.C_Client_ID = {$c_client_id}
			AND w.Description = '{$cr[0]}'";

        if ($sqlca->query($sql) <= 0) {

            if (isset($debug) && $debug == "si") {
                echo "Error del MH:<br/>$sql  <br/>";
            }
        } else {
            $bpr = $sqlca->fetchRow();
            $destination_i_warehouse_id = $bpr[0];
        }

        $sql = "SELECT
			w.I_Warehouse_ID
		FROM
			I_Warehouse w
			JOIN C_Org o USING (C_Org_ID)
		WHERE
			o.C_Client_ID = {$c_client_id}
			AND w.Description = '{$cr[2]}'";

        //$source_i = $bpr[0];	

        if ($sqlca->query($sql) <= 0) {
            $migerr = "M.H Error de consulta warehouse - " . $sql;
            //$source_i_warehouse_id = insertaOrWh($c_client_id, $c_org_id, $cr[2]);
            if (isset($debug) && $debug == "si") {
                echo "Error del MH:No se encontro almacen($c_client_id*$c_org_id*$cr[2])<br/>$sql  <br/>";
            }
        } else {
            $bpr = $sqlca->fetchRow();
            $source_i_warehouse_id = $bpr[0];
        }
        $taxid = trim($cr[4]);
        $sql = "SELECT
			C_BPartner_ID
		FROM
			C_BPartner
		WHERE
			C_Client_ID = {$c_client_id} 
			AND TaxID like '%{$taxid}%';"; // falta poner esto--

        if ($sqlca->query($sql) <= 0) {

            if (isset($debug) && $debug == "si") {
                echo "Error del MH:<br/>$sql  <br/>";
            }

            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_bpartner_id = $bpr[0];



        $f_pump = $cr[12];
        $documento = str_replace(array("-", "/", "*"), "", $cr[11]);

        echo $documento . "=>";
        $sql = "INSERT INTO
				I_MovementHeader(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						Source_I_Warehouse_ID,
						Destination_I_Warehouse_ID,
						C_BPartner_ID,
						C_DocType_ID,
						DocumentSerial,
						DocumentNo,
						C_InvoiceHeader_ID,
						Status,
						c_org_id,
                                                f_pump_id
				) VALUES (
						'{$cr[8]}',
						0,
						'{$cr[8]}',
						0,
						1,
						{$source_i_warehouse_id},
						{$destination_i_warehouse_id},
						{$c_bpartner_id},
						{$c_doctype_id},
						'" . addslashes($documento) . "',
						'" . addslashes($documento) . "',
						NULL,
						2,
						{$c_org_id},
                                                {$f_pump}
				);";
//addslashes(str_replace('-','',$cr_5))
        error_log("I_MovementHeader:" . $sql);
        if ($sqlca->query($sql) < 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del MH:<br/>$sql  <br/>";
            }

            return FALSE;
        }
    }

    return TRUE;
}

function importMD($ctdata, $cturl) {
    global $sqlca, $COWMap, $migerr, $debug;
    $fila = 0;
    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);
        if (count($cr) <= 1) {
            if (isset($debug) && $debug == "si") {
                echo "No se encontro datos en importMD .>$fila<br/>";
            }
            break;
        }
        $fila++;
        if (!isset($COWMap[$cr[1]])) {
            continue;
        }

        $c_client_id = $COWMap[$cr[1]][0];
        $c_org_id = $COWMap[$cr[1]][1];
        $i_warehouse_id = $COWMap[$cr[1]][2];

        $documento = str_replace(array("-", "/", "*"), "", $cr[5]);
        $documento = trim($documento);
        $sql = "SELECT
			h.I_MovementHeader_ID,
			h.created,
			h.updated	
		FROM
			I_MovementHeader h
			JOIN C_BPartner p USING (C_BPartner_ID)
		WHERE
			p.C_Client_ID = {$c_client_id}
			AND h.DocumentNo = '" . addslashes(str_replace('-', '', $documento)) . "';"; //falta esto 

        if ($sqlca->query($sql) <= 0) {
            $migerr = "Cannot get MHID for ID '" . $sql;
            if (isset($debug) && $debug == "si") {
                echo "Error del MD:<br/>$sql  <br/>";
            }
            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $i_movementheader_id = $bpr[0];
        $created = $bpr[1];
        $updated = $bpr[2];

        $sql = "SELECT
			p.C_Product_ID
		FROM
			C_Product p
			JOIN C_TaxGroup t USING (C_TaxGroup_ID)
		WHERE
			t.C_Client_ID = {$c_client_id}
			AND p.Value = trim('{$cr[8]}');"; //falta esto 	

        if ($sqlca->query($sql) <= 0) {
            // $migerr = "Error de consulta importMD - " . $sql;
            $c_product_id = importProduct($cr[8], $cturl, $c_client_id);
            if ($c_product_id === FALSE) {
                if ($migerr == NULL)
                    $migerr = "Error no exite el producto '{$cr[8]}'";
                if (isset($debug) && $debug == "si") {
                    echo "$migerr.><br/>";
                }
                return FALSE;
            }
        } else {
            $bpr = $sqlca->fetchRow();
            $c_product_id = $bpr[0];
        }

        $sql = "INSERT INTO
				I_MovementDetail(
						Created,
						CreatedBy,
						Updated,
						UpdatedBy,
						IsActive,
						I_MovementHeader_ID,
						C_Product_ID,
						Quantity,
						UnitPrice,
						LineTotal
				) VALUES (
						'$created',
						1,
						'$updated',
						1,
						1,
						{$i_movementheader_id},
						{$c_product_id},
						{$cr[10]},
						{$cr[9]},
						{$cr[11]}
				);";
        error_log("I_MovementDetail:" . $sql);
        if ($sqlca->query($sql) < 0) {

            if (isset($debug) && $debug == "si") {
                echo "Error del MD:<br/>$sql  <br/>";
            }
            return FALSE;
        }
    }

    return TRUE;
}

function importDC($ctdata) {
    global $sqlca, $COWMap, $migerr, $debug;


    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);





        if (count($cr) <= 1)
            break;

        if (!isset($COWMap[$cr[0]])) {
            continue;
        }

        $c_org_id = $COWMap[$cr[0]][1];
        $sql = "SELECT
			d.c_daycontrol_id,
			d.systemdate,
			d.c_org_id
		FROM
			c_daycontrol d
		WHERE
			d.c_org_id = {$c_org_id}
			AND d.systemdate = '{$cr[1]}';";

        //**************************
        echo "importDC(1)->Verificando Migracion Existente :";
        if ($sqlca->query($sql) <= 0) {
            
        } else {
            while ($fecha_migrado = $sqlca->fetchRow()) {
                echo "Fecha Migrada: <br/>";
                var_dump($fecha_migrado);
                echo "<br/>";
            }
        }
        /////////////////////////////             

        if ($sqlca->query($sql) <= 0) {
            $c_daycontrol_id = InsertPC($c_org_id, $cr[1]);
            if ($c_daycontrol_id === FALSE) {
                if ($migerr == NULL)
                    $migerr = " {$c_org_id} - '{$cr[1]}'";
                return FALSE;
            }
        } else {
            $bpr = $sqlca->fetchRow();
            $c_daycontrol_id = $bpr[0];
        }


        $sql = " INSERT INTO
				c_periodcontrol(
						created,
						createdby,
						updated,
						updatedby,
						isactive,
						c_daycontrol_id,
						isclosed,
						c_org_id
				)VALUES (
						'{$cr[3]}',
						0,
						'{$cr[3]}',
						0,
						1,
						{$c_daycontrol_id},
						1,
						{$c_org_id}
				)";
        error_log("c_periodcontrol:" . $sql);
        if ($sqlca->query($sql) < 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del importDC:<br/>$sql  <br/>";
            }
            return FALSE;
        }
    }

    return TRUE;
}

function importFT($ctdata) {
    global $sqlca, $COWMap, $migerr, $debug;

    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);
        if (count($cr) <= 1)
            break;

        if (!isset($COWMap[$cr[0]])) {
            continue;
        }

        $c_org_id = $COWMap[$cr[0]][1];

        $sql = " SELECT 
			c_periodcontrol_id
		 FROM 
			c_periodcontrol
		 WHERE
			created = '{$cr[3]}'
			AND c_org_id = '$c_org_id';";





        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del FT:<br/>$sql  <br/>";
            }
            return FALSE;
        } else {
            $bpr = $sqlca->fetchRow();
            $c_periodcontrol_id = $bpr[0];
        }

        $sql = " SELECT
			m.f_grade_id,
			m.value,
			l.value
		 FROM
			f_pump l
				INNER JOIN f_grade m ON(l.f_pump_id = m.f_pump_id)
				INNER JOIN f_pumpnetwork p ON(l.f_pumpnetwork_id = p.f_pumpnetwork_id)
		 WHERE 
			p.c_org_id = $c_org_id
			AND l.value = '{$cr[6]}'
			AND m.value::integer = {$cr[7]};";

        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del FT:<br/>$sql  <br/>";
            }
            return FALSE;
        } else {
            $bpr = $sqlca->fetchRow();
            $f_grade_id = $bpr[0];
        }

        $sql = " INSERT INTO
				f_totalizer(
						created,
						createdby,
						updated,
						updatedby,
						isactive,
						f_grade_id,
						volume,
					   	amount,
					    	c_periodcontrol_id
				)VALUES(
						'{$cr[3]}',
						0,
						'{$cr[3]}',
						0,
						1,
						$f_grade_id,
						{$cr[4]},
						{$cr[5]},
						$c_periodcontrol_id
				)";
        error_log("f_totalizer:" . $sql);
        if ($sqlca->query($sql) < 0) {

            if (isset($debug) && $debug == "si") {
                echo "Error del FT:<br/>$sql  <br/>";
            }
            return FALSE;
        }
    }

    return TRUE;
}

function importSF($ctdata) {
    global $sqlca, $COWMap, $migerr, $debug, $array_sale_cobus, $array_sale_market; //barranco esta desabilitada
    // $array_sale_cobus = array("1" => 1, "3" => 3, "6" => 6, "8" => 8, "10" => 10, "11" => 11, "12" => 12, "13" => 13, "14" => 14, "40" => 40);
    // $array_sale_market = array("1" => 2, "3" => 3, "6" => 6, "8" => 7, "10" => 9, "11" => 11, "12" => 12, "13" => 13, "14" => 14, "40" => 40);
    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);

        if (count($cr) <= 1)
            break;

        if (!isset($COWMap[$cr[0]])) {
            continue;
        }

        $c_org_id = $COWMap[$cr[0]][1];
        //echo $c_org_id."<br/>----------";

        $sql = "SELECT 
			c_periodcontrol_id
		FROM 
			c_periodcontrol
		WHERE
			created = '{$cr[15]}'
			AND c_org_id = '{$c_org_id}';";
//echo $sql."<br/>";  
        if ($sqlca->query($sql) <= 0) {

            if (isset($debug) && $debug == "si") {
                echo "Error del importSF:<br/>$sql  <br/>";
            }

            return FALSE;
        }

        $bpr = $sqlca->fetchRow();
        $c_periodcontrol_id = $bpr[0];

        $sql = "SELECT 
                        d.c_org_id,
			p.c_pos_id
		FROM 
			c_pos p
			INNER JOIN c_documentserial d on(p.c_documentserial_id = d.c_documentserial_id)
		WHERE
			d.documentserial = trim('{$cr[14]}') ";

        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del importSF:<br/>$sql  <br/>";
            }
            return FALSE;
        }
        $c_pos_id = -1;
        $cantidad = $c = $sqlca->numrows();

        /* $array_sale_cobus=array("10"=>10);
          $array_sale_market=array("10"=>9); */
        if ($cantidad > 1) {
            while ($bpr = $sqlca->fetchRow()) {
                $c_org_id_vec = $bpr[0];
                if ($array_sale_cobus[$c_org_id] == $c_org_id_vec || $array_sale_market[$c_org_id] == $c_org_id_vec) {
                    $c_pos_id = $bpr[1];
                    break;
                }
            }
        } else if ($cantidad == 1) {
            $bpr = $sqlca->fetchRow();
            $c_pos_id = $bpr[1];
        }
        //echo "la salida es :".$c_pos_id."<br/>";

        $cash_number = (int) $cr[16];

        /* if ($cr[9] == "") {
          $invoice_total = "";
          $invoice = "";
          } else {
          $invoice_total = "invoice_total,";
          $invoice = $cr[9] . ",";
          }

          if ($cr[10] == "") {
          $invoice_tax = "";
          $transactions = "";
          } else {
          $invoice_tax = "invoice_tax,";
          $transactions = $cr[10] . ",";
          } */
        //validar que este en cero o vacio
        if ($cr[8] == 0) {
            $invoice_total = '0';
            $invoice_tax = '0';
        } else {

            $invoice_total = $cr[9];
            $invoice_tax = $cr[10];
        }
        if ($cr[5] == 0) {
            $ticket_total = '0';
            $ticket_tax = '0';
        } else {

            $ticket_total = $cr[6];
            $ticket_tax = $cr[7];
        }

        $sql = " INSERT INTO
				c_sale_shift(
						 created,
						 createdby, 
						 updated,
						 updatedby, 
						 isactive,
						 c_pos_id,
						 c_periodcontrol_id,
						 number_zz,
						 documentno_initial,
						 documentno_final,
						 ticket_transactions,
						 ticket_total,
						 ticket_tax,
						 invoice_transactions,
						 invoice_total,
						 invoice_tax,
						 total_transactions,
						 total_total,
						 total_tax,
						 cash_number
				) VALUES (
						 '{$cr[1]}',
						 1,
			 		         '{$cr[1]}',
						 1,
					         1,
						 {$c_pos_id},
						 {$c_periodcontrol_id},
						 {$cr[2]},
					         {$cr[3]},
						 {$cr[4]}, 
					         {$cr[5]},
						 {$ticket_total},
						 {$ticket_tax}, 
					         {$cr[8]},
						 {$invoice_total},
						 {$invoice_tax},
					    	 {$cr[11]},
						 {$cr[12]},
						 {$cr[13]},
						 {$cash_number}
				);";
        error_log("c_sale_shift:" . $sql);

        if ($sqlca->query($sql) < 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del importSF:<br/>$sql  <br/>";
            }
            return FALSE;
        }
    }

    return TRUE;
}

function importINH($ctdata) {
    global $sqlca, $COWMap, $migerr, $debug;

    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);
        if (count($cr) <= 1)
            break;

        if (!isset($COWMap[$cr[0]])) {
            continue;
        }

        $c_org_id = $COWMap[$cr[0]][1];

        $sql = " SELECT 
				i_warehouselocation_id 
			 FROM
				i_warehouselocation 
			 WHERE 
				description = '{$cr[3]}';";

        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del importINH:<br/>$sql  <br/>";
            }
            return FALSE;
        } else {
            $bpr = $sqlca->fetchRow();
            $i_warehouse_id = $bpr[0];
        }

        $sql = " INSERT INTO
				i_inventoryheader(
							created,
							createdby,
							updated,
							updatedby, 
							isactive,
							i_warehouselocation_id
				) VALUES (
							'{$cr[1]}', 
							1,
							'{$cr[2]}',
							1, 
							1,
							{$i_warehouse_id}
				);";
        error_log("i_inventoryheader:" . $sql);
        if ($sqlca->query($sql) < 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del importINH:<br/>$sql  <br/>";
            }

            return FALSE;
        }
    }

    return TRUE;
}

function importIND($ctdata) { //$ctdata es  un arreglo 
    global $sqlca, $COWMap, $migerr, $debug;

    foreach ($ctdata as $crv) {
        $cr = explode("|", $crv);
        if (count($cr) <= 1)
            break;

        if (!isset($COWMap[$cr[0]])) {
            continue;
        }

        $c_org_id = $COWMap[$cr[0]][1];

        $sql = " SELECT 
                            	c_product_id
			 FROM
				c_product 
                         WHERE
				value = '{$cr[3]}';";

        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del importIND:<br/>$sql  <br/>";
            }
            return FALSE;
        } else {
            $bpr = $sqlca->fetchRow();
            $c_product_id = $bpr[0];
        }

        $sql = " SELECT 
                    		i_inventoryheader_id 
                    	 FROM
				i_inventoryheader
			 WHERE	
				created ='{$cr[1]}';";

        if ($sqlca->query($sql) <= 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del importIND:<br/>$sql  <br/>";
            }
            return FALSE;
        } else {
            $inv_h = $sqlca->fetchRow();
            $i_inventoryheader_id = $inv_h[0];
        }


        $sql = " INSERT INTO
				i_inventorydetail(
                                		    created,
                                		    createdby,
				                    updated,
				                    updatedby, 
				                    isactive,
				                    i_inventoryheader_id,
				                    c_product_id,
				                    quantity
				) VALUES (
                                  		  '{$cr[1]}',
				                    1,
				                    '{$cr[2]}',
				                    1,
				                    1,
				                    {$i_inventoryheader_id},
				                    {$c_product_id},
				                    {$cr[4]}
				);";
        error_log("i_inventorydetail:" . $sql);
        if ($sqlca->query($sql) < 0) {
            if (isset($debug) && $debug == "si") {
                echo "Error del importIND:<br/>$sql  <br/>";
            }
            return FALSE;
        }
    }

    return TRUE;
}

function InsertPC($id_org, $fecha) {
    global $sqlca, $migerr;

    $sql = " INSERT INTO
			c_daycontrol(
					created,
					createdby,
					updated,
					updatedby,
					isactive,
					systemdate,
					isclosed,
					c_org_id
			)VALUES (
					now(),
					0,
					now(),
					0,
					1,
					'{$fecha}',
					1,
					{$id_org}
			)";
    error_log("c_daycontrol:" . $sql);
    "Insertando la columna de c_day :" . $sql;
    if ($sqlca->query($sql) < 0) {
        echo $sql . "<br/>";
        $migerr = "Database error creating DayControl <br>" . $sql;
        return FALSE;
    }

    $sqlca->query("SELECT max(c_daycontrol_id) FROM c_daycontrol;");
    $lr = $sqlca->fetchRow();

    return $lr[0];
}

function verifica_cierre($ctdata, $dt) {
    global $sqlca, $COWMap, $migerr;

    $datex = substr($dt, 0, 4) . "-" . substr($dt, 4, 2) . "-" . substr($dt, 6, 2);

    if ($datex == date('Y-m-d')) {

        foreach ($ctdata as $crv) {

            $cr = explode("|", $crv);
            if (count($cr) <= 1)
                break;

            if (!isset($COWMap[$cr[0]])) {
                continue;
            }

            $fecha_turno = date('Y-m-d', strtotime($cr[3]));
        }

        $fecha_cierre = date('Y') . '-' . date('m') . '-' . (date('d') + 1);

        if ($fecha_turno != $fecha_cierre) {
            $migerr = 'No puede Importar datos del dia actual (' . date('d-m-Y') . ') hasta que haya cierre de turno !!!';
            return FALSE;
        }
    }

    return true;
}

function verificar_fecha_exportacion($fecha_bus, $c_org_id) {
    global $sqlca, $resultado_exe, $org_cade, $array_c_org, $estadoActu, $org_cade_market, $debug, $datosql;



    $nuevafecha = strtotime('-1 day', strtotime($fecha_bus));
    $nuevafecha = date('Y-m-j', $nuevafecha);

    if (isset($debug) && $debug == "si") {
        echo "--------detalle de informacion verificar_fecha_exportacion----------<br/>";
        echo "verificar_fecha_exportacion->(1):Fecha actual Migrar  :$fecha_bus<br/>";
        echo "verificar_fecha_exportacion->(1):Fecha Anterior  :$nuevafecha<br/>";
    }

    $sql = "
        select count(*) as cant from mig_process where mig_remote_id  in
        (select mig_remote_id from mig_remote where ip in(select ip from mig_remote where mig_remote_id in 
        ((select id_remote from mig_cowmap where c_org_id in($c_org_id)    ))))
        and systemdate ='$nuevafecha';
";




    if ($sqlca->query($sql) <= 0) {
        throw new Exception("Error al verificar informacion a Importacion -> <BR/>$sql");
    }
    $row = $sqlca->fetchRow();


    if (isset($debug) && $debug == "si") {

        echo "verificar_fecha_exportacion->(2):Consulta ejecutada  :$sql<br/>";
        echo "verificar_fecha_exportacion->(2):Cantidad de registro encontrado en mig_process del dia Anterior  :" . $row['cant'] . "<br/>";
        echo "verificar_fecha_exportacion->(2):Resultado  :<br/>";
        var_dump($row);
        echo "<br/>";
    }



    if ($row['cant'] == 1) {

        return TRUE;
    } else {
        $datosql = EliminarInformacionD_dia($nuevafecha, $c_org_id);
        return FALSE;
    }

    return FALSE;
}

function EliminarInformacionD_dia($fecha, $c_org_id) {
    $datosql[0] = array("Infoduplicado" => 1, "f" => $fecha, "c" => $c_org_id);
    return $datosql;
}

function verificar_info_duplicada($fecha, $c_org_id) {
    global $sqlca, $COWMap, $migerr, $debug;
    $valor_buscar = trim($pval);

    //cuando es market no genera en c_daycontrol
    $sql = "
        SELECT count(*), systemdate,c_org_id
        FROM c_daycontrol WHERE c_org_id in($c_org_id) and  
         systemdate ='$fecha' group by systemdate,c_org_id order by c_org_id,systemdate limit 1  ;";



    $sqlca->query($sql);
    $numrow = $sqlca->numrows();

    $row = $sqlca->fetchRow(); // 
    $cantidad = 0;


    if (isset($debug) && $debug == "si") {
        echo "-----------detalle de informacion verificar_info_duplicada-----------<br/>";
        echo "verificar_info_duplicada->(1):consulta ejecutada  : $sql<br/>";
        echo "verificar_info_duplicada->(1):Resultado:<br/>";
        var_dump($row);
        echo "<br/>";
    }
    if (is_null($row) || empty($row)) {

        $sql_market = "SELECT count(description) as cantidad from  c_org where  c_org_id in($c_org_id) and description='M'";
        $sqlca->query($sql_market);



        $row = $sqlca->fetchRow();
        $cantidad_market = $row[0];
        if ($cantidad_market >= 1) {
            $cantidad = 1;
        }

        if (isset($debug) && $debug == "si") {
            echo "**Verificando si es solo market :<br/>";
            echo "verificar_info_duplicada->(2):consulta ejecutada  : $sql_market<br/>";
            echo "verificar_info_duplicada->(2:resultado :<br/>";
            var_dump($row);
            echo "<br/>";
        }
    } else {

        $cantidad = $row[0];

        if (isset($debug) && $debug == "si") {
            echo "**Verificando si se inserto solo un registro en tabla_periodos :<br/>";
            echo "verificar_info_duplicada->(3):Cantidad registros en c_daycontrol:" . $cantidad . "<br/>";
        }
    }



    return $cantidad;
}
