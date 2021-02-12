<?php
//session_start();
//include("../config.php");
//include("../combustibles/inc_top.php");
include("../functions.php");
//include("../valida_sess.php");
include("../menu_princ.php");
/*obtenemos fecha*/
$nuevofechad = split('/',$_REQUEST['diasd']);
$diad=$nuevofechad[0];
$mesd=$nuevofechad[1];
$anod=$nuevofechad[2];
$nuevofechaa = split('/',$_REQUEST['diasa']);
$diaa=$nuevofechaa[0];
$mesa=$nuevofechaa[1];
$anoa=$nuevofechaa[2];
include("store_procedures.php");
$fecha = getdate();
$dia = $fecha['mday'];
$mes = $fecha['mon'];
$year = $fecha['year'];
$hoy = $dia.'-'.$mes.'-'.$year;

if($cod_almacen==""){$cod_almacen = $almacen;}
if($consultar=="Consultar"){

$fechad = $diad."/".$mesd."/".$anod;
$fechaa = $diaa."/".$mesa."/".$anoa;

pg_exec("truncate trans_pend ");
pg_exec("select rep_dif_trans(to_date('$fechad','dd/mm/yyyy') , to_date('$fechaa','dd/mm/yyyy') ) ");
//echo  "select rep_dif_trans(to_date('$fechad','dd/mm/yyyy') , to_date('$fechaa','dd/mm/yyyy') ) ";
}else{

$diad = "01";
$anod = $year;
$mesd = $mes;

$diaa = "31";
$anoa = $year;
$mesa = $mes;

$fechad = $diad."/".$mesd."/".$anod;
$fechaa = $diaa."/".$mesa."/".$anoa;
}
//para la tabla de busqueda por fechas

?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" src="miguel-funciones.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->

var miPopup 
function abririPopup(){
	miPopup = window.open("prueba.php","miwin","width=500,height=400,scrollbars=yes") 
	miPopup.focus() 
	}

</script>
</head>

<body>
INGRESO DE VARILLAS 
<hr noshade>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
ALMACEN:<?php echo $almacen.' -- '.$sucursal_dis;?> 
<form name="form1" method="post" action="generar_rep_diftrans.php">
  <table border="0">
  <tr> 
    <th colspan="5">CONSULTAR POR RANGO DE FECHAS </th>
  </tr>
  <tr> 
    <th>DESDE :</th>
    <th><input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('form1.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">&nbsp;</th>
    <th>HASTA:</th>
      <th><input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('form1.diasa');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></th>
      <th><input type="submit" name="consultar" value="Consultar"></th>
  </tr>
</table>
  <p align="center"><strong><font size="2" face="Arial, Helvetica, sans-serif">Transferencias 
    Pendientes de Productos DEL: <?php echo $fechad;?> AL: <?php echo $fechaa;?> <a href="#" onClick="javascript:window.open('cmb_medvarilla-reporte.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"> 
    </a> </font></strong></p>
  <strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="#" onClick="javascript:window.open('reporte_dif_trans.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar 
  Reporte</a></font></strong> <br>
  <!-- <?php 
	$rs = pg_exec("select distinct pend.mov_almacen,int.tab_descripcion 
	from int_tabla_general int,trans_pend pend where 
	trim(pend.mov_almacen) = trim(int.tab_elemento) and int.tab_tabla='ALMA' and int.tab_car_02='1' 
	and pend.trans_cod='08'");
	for($a=0;$a<pg_numrows($rs);$a++){
	$K = pg_fetch_array($rs,$a);
	$almacen = $K[0];
	$nom_almacen = $K[1];
	/*$rs1 = pg_exec("select distinct mov.mov_numero,mov.mov_docurefe,to_char(mov.mov_fecha,'dd/mm/yy')
	,art.art_descbreve,mov.mov_cantidad,mov.mov_almadestino,''||'*NO ING*'||''
	,''||'_'||'' as cant_ingreso , ''||'_'||'' as diferencia
	,mov.mov_costounitario as cost_ori , ''||'_'||'' as cost_des
	,art.art_tipo
	from inv_movialma mov, trans_pend pend, int_articulos art
	where  pend.trans_cod='08' and mov.mov_docurefe = pend.num_guia and mov.mov_almacen=pend.mov_almacen 
	and art.art_codigo = mov.art_codigo and mov.mov_almacen='$cod_almacen' ");*/
	$trans_cod="08";
	$rs1 = reporte_diftrans($almacen,$trans_cod);
 ?>-->
  <font size="-4" face="Arial, Helvetica, sans-serif">ALMACEN DE SALIDA : <?php echo $nom_almacen;?> 
  <?php echo $cod_almacen;?> </font> 
  <table width="769" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="54"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">Nro. 
          FORM</font></div></td>
      <td width="52"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">Nro.GUIA</font></div></td>
      <td width="54"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">F.SALIDA</font></div></td>
      <td width="104"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">ARTICULO</font></div></td>
      <td width="67"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">CANT.SALIDA</font></div></td>
      <td width="81"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">ALM.DESTINO</font></div></td>
      <td width="52"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">F.INGRESO</font></div></td>
      <td width="71"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">CANT.INGRESO</font></div></td>
      <td width="58"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">DIFERENCIA</font></div></td>
      <td width="45"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">COST.ORI</font></div></td>
      <td width="47"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">COST.DES</font></div></td>
      <td width="49"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">TIPO 
          ITEM</font></div></td>
    </tr>
    <!--<?php for($i=0;$i<pg_numrows($rs1);$i++){ 
  $A = pg_fetch_array($rs1,$i);
  if($A[6]==""){ $A[6]="*NO ING*"; }
  if($A[7]==""){ $A[7]="-"; }
  if($A[8]==""){ $A[8]="-"; }
  $A[10] = "-";
  print '
  ?>-->
    <tr> 
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[0].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[1].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[3].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[4].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[5].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[6].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[7].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[8].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[9].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[10].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[11].'</font></div></td>
    </tr>
    <!-- <?php  ';  } 
  
  ?>-->
    <tr> 
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
    </tr>
  </table>
  <p> 
    <!--{END} <?php } 
  
  ?>-->
  </p>
  <p> 
    <!-- <?php 
  	$rs = pg_exec("select distinct pend.mov_almacen,int.tab_descripcion 
	from int_tabla_general int,trans_pend pend where 
	trim(pend.mov_almacen) = trim(int.tab_elemento) and int.tab_tabla='ALMA' and int.tab_car_02='1' 
	and pend.trans_cod='07'");
	for($a=0;$a<pg_numrows($rs);$a++){
	$K = pg_fetch_array($rs,$a);
	$almacen = $K[0];
	$nom_almacen = $K[1];
	/*$rs1 = pg_exec("select distinct mov.mov_numero,mov.mov_docurefe,to_char(mov.mov_fecha,'dd/mm/yy')
	,art.art_descbreve,mov.mov_cantidad,mov.mov_almadestino,''||'*NO ING*'||''
	,''||'_'||'' as cant_ingreso , ''||'_'||'' as diferencia
	,mov.mov_costounitario as cost_ori , ''||'_'||'' as cost_des
	,art.art_tipo
	from inv_movialma mov, trans_pend pend, int_articulos art
	where  pend.trans_cod='08' and mov.mov_docurefe = pend.num_guia and mov.mov_almacen=pend.mov_almacen 
	and art.art_codigo = mov.art_codigo and mov.mov_almacen='$cod_almacen' ");*/
	$trans_cod="07";
	$rs1 = reporte_diftrans($almacen,$trans_cod);
 	
 ?>-->
    <font size="-4" face="Arial, Helvetica, sans-serif">ALMACEN DE ENTRADA : <?php echo $nom_almacen;?> 
    <?php echo $cod_almacen;?> </font> </p>
  <table width="769" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="54"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">Nro. 
          FORM</font></div></td>
      <td width="52"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">Nro.GUIA</font></div></td>
      <td width="54"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">F.SALIDA</font></div></td>
      <td width="104"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">ARTICULO</font></div></td>
      <td width="67"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">CANT.SALIDA</font></div></td>
      <td width="81"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">ALM.DESTINO</font></div></td>
      <td width="52"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">F.INGRESO</font></div></td>
      <td width="71"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">CANT.INGRESO</font></div></td>
      <td width="58"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">DIFERENCIA</font></div></td>
      <td width="45"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">COST.ORI</font></div></td>
      <td width="47"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">COST.DES</font></div></td>
      <td width="49"><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">TIPO 
          ITEM</font></div></td>
    </tr>
    <!--<?php for($i=0;$i<pg_numrows($rs1);$i++){ 
  $A = pg_fetch_array($rs1,$i);
  
  if($A[2]==""){ $A[2]="*NO ING*"; }
  if($A[7]==""){ $A[7]="-"; }
  if($A[8]==""){ $A[8]="-"; }
  $A[9] = "-";
  print '
  ?>-->
    <tr> 
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[0].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[1].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[3].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[4].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[5].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[6].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[7].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[8].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[9].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[10].'</font></div></td>
      <td><div align="center"><font size="-6" face="Arial, Helvetica, sans-serif">'.$A[11].'</font></div></td>
    </tr>
    <!-- <?php  ';  } 
  
  ?>-->
    <tr> 
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-6"></font></font></div></td>
    </tr>
  </table>
  <br>
  <!--{END} <?php } 
  
  pg_close();
  ?>-->
</form>
  
<br>
</body>
</html>  
<?php include("../close_connect.php"); ?>