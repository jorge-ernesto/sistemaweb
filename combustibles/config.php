<?php
global $coneccion;
$v_url="http://128.1.2.70/sistemaweb_combustibles/";
$v_path_linux="/var/www/html/sistemaweb_combustibles/";
//$v_path_url="/sistemaweb_combustibles/";
$v_path_url="/sistemaweb/";
$db="integrado";
$coneccion = pg_connect("host=localhost port=5432 dbname=".$db." user=postgres");
$tamPag=15;
//$xsql=pg_exec($coneccion,"select pos,nroserie,timeprint from pos_cfg where ip='".$_SERVER["REMOTE_ADDR"]."' ");
//if(pg_numrows($xsql)>0) { $caja=pg_result($xsql,0,0); $nroserie=pg_result($xsql,0,1); $timep=pg_result($xsql,0,2); }

$rutaprint="/tmp/imprimir/";

