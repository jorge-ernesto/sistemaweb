<?php
session_start();
$v_url="http://128.1.2.70/sistemaweb/";
$v_path_linux="/var/www/html/sistemaweb/";
$v_path_url="/sistemaweb/";
$v_host="localhost";
//$v_host = "128.1.2.120";
$v_db="integrado";
$v_user="postgres";
$coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=".$v_user." ");
//$almacen="018";
$xsql=pg_exec($coneccion,"select almac from tab_logueo where id_sesion='".$_COOKIE["PHPSESSID"]."'");
if(pg_numrows($xsql)>0) {
	$almacen=pg_result($xsql,0,0);
}
$tamPag=10;

// $rutadbf="/var/www/html/sistemaweb_grifos/nbastra/bastra.dbf";
$rutadbf="/var/www/html/pccombex/td/bastra.dbf";
$tamPag=15;
//$estab="18";
$xsql=pg_exec($coneccion,"select pos,nroserie,timeprint from pos_cfg where ip='".$_SERVER["REMOTE_ADDR"]."' ");
if(pg_numrows($xsql)>0) { $caja=pg_result($xsql,0,0); $nroserie=pg_result($xsql,0,1); $timep=pg_result($xsql,0,2); }

$rutaprint="/tmp/imprimir/";


if($usuario==""){
/*
?>
	<script language="JavaScript" >
		alert("No se ha registrado el usuario");
	</script>
<?php
*/
header("location: ../login.php");
}
include("/sistemaweb/inventarios/js/store_procedures.php");
include("/sistemaweb/inventarios/js/funciones.php");

$fecha = getdate();
$dia = $fecha['mday'];
//$mes = $fecha['mon'];
$mes = date("m");
$year = $fecha['year'];
$hoy = $dia.'/'.$mes.'/'.$year;

if($mes=="12"){$mes_cons="";}
$fecha_cons = $dia.'/'.$mes.'/'.$year;
$ultimo_dia = ultimo_Dia($fecha_cons);


if($diad==""){$diad="01";}
if($diaa==""){$diaa="29";}
if($mesd==""){$mesd=$mes;}
if($mesa==""){$mesa=$mes;}
if($anoa==""){$anoa=$year;}
if($anod==""){$anod=$year;}

if($cod_almacen!=""){
$rs6 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' 
and ch_almacen=trim('$cod_almacen') order by ch_nombre_almacen"); 
$R6 = pg_fetch_row($rs6,0);
$sucursal_dis = $R6[1];
$sucursal_val = $R6[0];
$almacen=$cod_almacen;
}
?>