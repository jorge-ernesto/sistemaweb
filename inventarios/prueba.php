<?php
include("../../config.php");
$valor = exec("/tmp/prueba1.sh");
echo "prueba".$valor;
