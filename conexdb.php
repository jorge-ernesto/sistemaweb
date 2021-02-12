<?php

class DB_postgres {
/* variabls de conexion */
	var $v_url="http://128.1.2.70/sistemaweb/";
	var $v_path_linux="/var/www/html/sistemaweb/";
	var $v_path_url="/sistemaweb/";
	var $v_host="localhost";
	var $v_db="integrado";
	var $v_user="postgres";
	var $v_pass="postgres";
	var $v_port=5432;

/* identificador de conexion y consulta */
	var $Conexion_ID=0;
	var $Consulta_ID=0;

/* Numero de error y texto error */
	var $Errno = 0;
	var $Error = "";

/* Metodo Constructor */
	function __construct ($host="", $port="", $dbname="", $user="") {
		$this->v_host = $host;
		$this->v_port = $port;
		$this->v_db = $dbname;
		$this->v_user = $user;

	}

/* Conexion a la base de datos */
	function conectar($host, $port, $dbname, $user){
		if($host != "") $this->v_host = $host;
		if($port != "") $this->v_port = $port;
		if($dbname != "") $this->v_db = $dbname;
		if($user != "") $this->v_user = $user;

	/* Conexion al servidor */
		$this->$Conexion_ID = pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=".$v_user." ");
		if(!$this->Conexion_ID) {
			$this->Error = "Ha fallado la conexon !!!";
		}
	}
}
?>

<?php
$v_url="http://128.1.2.70/sistemaweb/";
$v_path_linux="/var/www/html/sistemaweb/";
$v_path_url="/sistemaweb/";
$v_host="localhost";
$v_db="integrado";
$v_user="postgres";
$coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=".$v_user." ");
//$almacen="018";
$xsql=pg_exec($coneccion,"select almac from tab_logueo where id_sesion='".$_COOKIE["PHPSESSID"]."'");
if(pg_numrows($xsql)>0) {
	$almacen=pg_result($xsql,0,0);
}
$tamPag=10;

$rutadbf="/var/www/html/sistemaweb_grifos/nbastra/bastra.dbf";
$tamPag=15;
//$estab="18";
$xsql=pg_exec($coneccion,"select pos,nroserie,timeprint from pos_cfg where ip='".$_SERVER["REMOTE_ADDR"]."' ");
if(pg_numrows($xsql)>0) { $caja=pg_result($xsql,0,0); $nroserie=pg_result($xsql,0,1); $timep=pg_result($xsql,0,2); }

$rutaprint="/tmp/imprimir/";

