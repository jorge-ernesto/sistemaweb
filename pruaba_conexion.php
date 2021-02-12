<?php 
$dbstr ="INTERFAZO7=
(DESCRIPTION=
    (ADDRESS=
    (PROTOCOL=TCP)
    (HOST=192.168.0.4)
    (PORT=1521)
    )
    (CONNECT_DATA=
    (SERVER=dedicated)
    (SERVICE_NAME=INTERFAZO7)
    )

) ";
echo "Conectandose";
//$conn = oci_connect("OPENSOFT", "OPENSOFT", "192.168.0.4/XE");  
//var_dump();
//$mssql =  oci_connect("OPENSOFT ", "OPENSOFT ", $dbstr);
//var_dump($mssql);
phpinfo($conn);
/*
 INTERFAZO7=
(DESCRIPTION=
    (ADDRESS=
    (PROTOCOL=TCP)
    (HOST=192.168.0.4)
    (PORT=1521)
    )
    (CONNECT_DATA=
    (SERVER=dedicated)
    (SERVICE_NAME=INTERFAZO7)
    )

) 
 */
