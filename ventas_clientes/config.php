<?php

//var_dump($_REQUEST);
////session_start();

$v_url="http://localhost/sistemaweb/";
// $v_path_linux="/var/www/html/sistemaweb/";
$v_path_linux="$_SERVER[DOCUMENT_ROOT]/sistemaweb/";
$v_path_url="/sistemaweb/";
$v_host="localhost";
//$v_host = "localhost";
$v_db="integrado";
$v_user="postgres";
$v_pass="conejitalinda777";
$coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=".$v_user." password=".$v_pass." ");
error_log("Conexion ventas_clientes");
error_log("host=".$v_host." port=5432 dbname=".$v_db." user=".$v_user." password=".$v_pass." ");
//$almacen="018";
if( isset($_COOKIE["PHPSESSID"]) ){
      $xsql=pg_exec($coneccion,"select almac from tab_logueo where id_sesion='".$_COOKIE["PHPSESSID"]."'");
      if(pg_numrows($xsql)>0) {
            $almacen=pg_result($xsql,0,0);
      }
}

$tamPag=10;

// $rutadbf="/var/www/html/sistemaweb_grifos/nbastra/bastra.dbf";
$rutadbf="/var/www/html/pccombex/td/bastra.dbf";
$tamPag=15;
//$estab="18";
$xsql=pg_exec($coneccion,"select pos,nroserie,timeprint from pos_cfg where ip='".$_SERVER["REMOTE_ADDR"]."' ");
if(pg_numrows($xsql)>0) { $caja=pg_result($xsql,0,0); $nroserie=pg_result($xsql,0,1); $timep=pg_result($xsql,0,2); }

$rutaprint="/tmp/imprimir/";

$tabla_todo = '<tr> 
      <td width="33"><font size="-4" face="Arial, Helvetica, sans-serif">Articulo</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can01</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val01</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can02</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val02</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">cab03</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val03</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can04</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val04</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can05</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val05</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can06</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val06</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can07</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val07</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can08</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val08</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can09</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val09</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can10</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val10</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can11</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val11</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">can12</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val12</font></td>
    </tr> ';


$tabla_valor = '<tr> 
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">Articulo</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val01</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val02</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val03</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val04</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val05</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val06</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val07</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val08</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val09</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val10</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val11</font></td>
      <td width="10"><font size="-4" face="Arial, Helvetica, sans-serif">val12</font></td>
    </tr>';


$tabla_cantidad = '<tr> 
      <td width="33"><font size="-4" face="Arial, Helvetica, sans-serif">Articulo</font></td>
      <td width="28"><font size="-4" face="Arial, Helvetica, sans-serif">can01</font></td>
      <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can02</font></td>
      <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">cab03</font></td>
      <td width="28"><font size="-4" face="Arial, Helvetica, sans-serif">can04</font></td>
      <td width="28"><font size="-4" face="Arial, Helvetica, sans-serif">can05</font></td>
      <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can06</font></td>
      <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can07</font></td>
      <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can08</font></td>
      <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can09</font></td>
      <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can10</font></td>
      <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can11</font></td>
      <td width="29"><font size="-4" face="Arial, Helvetica, sans-serif">can12</font></td>
    </tr>';

//exit;

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
include("store_procedures.php");

/*
//$fecha = getdate();
////$dia = $fecha['mday'];
$dia = date('d');
//$mes = $fecha['mon'];
$mes = date("m");
////$year = $fecha['year'];
$year = date('Y');
$hoy = $dia.'/'.$mes.'/'.$year;

if($diad==""){$diad="01";}
if($diaa==""){$diaa="29";}
if($mesd==""){$mesd=$mes;}
if($mesa==""){$mesa=$mes;}
if($anoa==""){$anoa=$year;}
if($anod==""){$anod=$year;}
*/

$cod_almacen = null;

if($cod_almacen!="") {
      $rs6 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
       and  trim(ch_almacen)=trim('$cod_almacen') order by ch_nombre_almacen"); 
      $R6 = pg_fetch_row($rs6,0);
      $sucursal_dis = $R6[1];
      $sucursal_val = $R6[0];
      $almacen=$cod_almacen;
}