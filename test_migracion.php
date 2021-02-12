<?php

$serverName = "192.168.1.24, 1433"; //serverName\instanceName, portNumber (por defecto es 1433)
$connectionInfo = array( "Database"=>"Octano", "UID"=>"Opensoft", "PWD"=>"%%20eneg1g4$$2019");
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {
     echo "Conexión establecida.<br />";
}else{
     echo "Conexión no se pudo establecer.<br />";
     echo "<pre>";
     die( print_r( sqlsrv_errors(), true));
     echo "</pre>";
}
?>
