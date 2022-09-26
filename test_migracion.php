<?php

$serverName = "10.0.1.80\serverspring, 1433"; //serverName\instanceName, portNumber (por defecto es 1433)
$connectionInfo = array( "Database"=>"LLAMAGAS_SVO", "UID"=>"EESS", "PWD"=>"EESS");
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
