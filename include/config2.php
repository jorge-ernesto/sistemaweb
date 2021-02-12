<?php
$v_url		= "http://localhost/sistemaweb/";
$v_path_linux	= "/var/www/html/sistemaweb/";
$v_path_url	= "/sistemaweb/";
$db_host	= "localhost";
$db_name	= "integrado";
$db_user	= "postgres";
$coneccion	= pg_connect("host=".$db_host." port=5432 dbname=".$db_name." user=".$db_user." ");
$xsql		= pg_exec($coneccion,"select almac from tab_logueo where id_sesion='".$_COOKIE["PHPSESSID"]."'");

if(pg_numrows($xsql) > 0) {
	$almacen = pg_result($xsql,0,0);
	$g_almacen = $almacen;
}
$tamPag = 10;

$rutadbf = "/var/www/html/pccombex/td/bastra.dbf";
$tamPag  = 15;

$xsql = pg_exec($coneccion,"select pos,nroserie,timeprint,pc_samba,prn_samba,rutaprint from pos_cfg where ip='".$_SERVER["REMOTE_ADDR"]."' ");

if(pg_numrows($xsql) > 0) { 
	$caja		= pg_result($xsql,0,0);  
	$nroserie	= pg_result($xsql,0,1); 
	$timep		= pg_result($xsql,0,2); 
	$pc_samba	= trim( pg_result($xsql,0,3) ); 
	$prn_samba	= trim( pg_result($xsql,0,4) ); 
	$rutaprint	= trim( pg_result($xsql,0,5) );
	$pc_ip 		= $_SERVER["REMOTE_ADDR"];
} else {
	$rutaprint	= "/tmp/imprimir/";
}
