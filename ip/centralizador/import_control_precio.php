<?php

require('central.inc.php');

$migerr = NULL;
$preciocont = 0;

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'migrate') {
    $fecha = date('Y-m-d');
    
    if (importProcess_Precio("http://10.0.23.1/sistemaweb/centralizer.php", $fecha)) {
        echo "<h1>Se exportos los producto correctamente.</h1>";
    }
}
