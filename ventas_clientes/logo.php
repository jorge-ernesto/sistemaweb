<?php

//Obtenemos OID
$logo_oid = isset($_GET['oid']) ? $_GET['oid'] : '';                   

$dbconn = pg_connect("host=localhost user=postgres dbname=integrado port=5432") or die('Could not connect: ' . pg_last_error());
                        
//Comenzamos transaccion
pg_query($dbconn, "BEGIN") or die('BEGIN failed: ' . pg_last_error());
            
//Recurso de large object
$lo_handle = pg_lo_open($dbconn, $logo_oid, "r") or die('pg_lo_open failed: ' . pg_last_error());
            
//Leemos large object
$logo_data = pg_lo_read($lo_handle, '50000') or die('pg_lo_read failed: ' . pg_last_error());
if ($logo_data === false)
      echo "";

//Cerramos transaccion
pg_query($dbconn, "COMMIT;")  or die('COMMIT failed: ' . pg_last_error());

// header("Content-Type: image/png");
// header("Content-Disposition: attachment; filename=logo.png");
// readfile($logo_data);

header ("Content-type: image/png");
echo $logo_data;