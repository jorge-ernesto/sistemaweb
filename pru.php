<?php
$d=sscanf("10/10/2012", "%2s/%2s/%4s");
var_dump($d);
list($desde_dia, $desde_mes, $desde_ano) = sscanf("10/10/2012", "%2s/%2s/%4s");
echo $desde_dia."--".$desde_mes."--".$desde_ano;
