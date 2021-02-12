<?php
include("config.php");
$rs = pg_exec("select * from int_articulos limit 7");

$x = pg_result($rs,2,"art_descripcion");
echo $x;
