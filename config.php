<?php
$v_url			= "http://localhost/sistemaweb/";
// $v_path_linux	= "/var/www/html/sistemaweb/";
$v_path_linux	= "$_SERVER[DOCUMENT_ROOT]/sistemaweb/";
$v_path_url		= "/sistemaweb";
$v_host			= "localhost";
$v_db			= "integrado";
$v_user			= "postgres";
$v_password    = "conejitalinda777";
$coneccion		= pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=".$v_user." password=".$v_password." ");
error_log("Conexion");
error_log("host=".$v_host." port=5432 dbname=".$v_db." user=".$v_user." password=".$v_password." ");
if( isset($_COOKIE["PHPSESSID"]) ){
	$xsql			= pg_exec($coneccion,"SELECT almac FROM tab_logueo WHERE id_sesion='".$_COOKIE["PHPSESSID"]."'");

	if(pg_numrows($xsql)>0) { $almacen=pg_result($xsql,0,0); }
}

$tamPag		= 10;
$rutadbf	= "/var/www/html/pccombex/td/bastra.dbf";
$tamPag		= 15;
$xsql		= pg_exec($coneccion,"SELECT pos,nroserie,timeprint,pc_samba,prn_samba,rutaprint FROM pos_cfg WHERE ip='".$_SERVER["REMOTE_ADDR"]."' ");

if(pg_numrows($xsql)>0) {
	$caja		= pg_result($xsql,0,0);
	$nroserie	= pg_result($xsql,0,1);
	$timep		= pg_result($xsql,0,2);
	$pc_samba	= trim( pg_result($xsql,0,3) );
	$prn_samba	= trim( pg_result($xsql,0,4) );
	$rutaprint	= trim( pg_result($xsql,0,5) );
	$pc_ip 		= $_SERVER["REMOTE_ADDR"];
} else {
	$rutaprint="/tmp/imprimir/";
}
