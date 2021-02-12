<?php
session_start();
include("../config.php");
//include("../combustibles/inc_top.php");
include("../functions.php");
include("../valida_sess.php");
include("store_procedures.php");
$rs = pg_exec("select distinct pend.mov_almacen,int.tab_descripcion 
from int_tabla_general int,trans_pend pend where 
trim(pend.mov_almacen) = trim(int.tab_elemento) and int.tab_tabla='ALMA' and int.tab_car_02='1' 
and pend.trans_cod='08'");

if($action=="exportar"){
	$user_temp = $user."_rep2";
	exec(" mv /var/www/html/sistemaweb/tmp/$user_temp.txt /var/www/html/sistemaweb/tmp/$user_temp.csv");
	$url = "http://128.1.2.70/sistemaweb/tmp/$user_temp.csv";
	?>
	<script language="JavaScript1.3" type="text/javascript">
	window.open('<?php echo $url;?>','miwin','width=10,height=35,scrollbars=yes');
	</script>

	<?php
}else{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>TRANSFERENCIAS PENDIENTES DE PRODUCTOS</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table width="767" border="1" cellpadding="0" cellspacing="0">
  <tr> 
    <td width="457"><a href="reporte_dif_trans.php?action=exportar" >Exportar 
      a Excel</a></td>
    <td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
  </tr>
</table>
<p align="center">Transferencias Pendientes de Productos DEL: <?php echo $fechaa;?> AL: <?php echo $fechad;?> 
</p>
<!-- <?php $titulo = "Transferencias Pendientes de Productos DEL: $fechaa al: $fechad";
	 $user_temp = $user."_rep2";
 	exec("echo -e '$titulo \n \n' > /var/www/html/sistemaweb/tmp/$user_temp.txt"); 
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
<font size="-4" face="Arial, Helvetica, sans-serif">ALMACEN DE SALIDA : <?php echo $nom_almacen;?><?php echo $cod_almacen;?> 
</font> 
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
  
  
  $almacen = "ALMACEN DE SALIDA: $nom_almacen";
  $cabecera = " Nro. FORM , Nro.GUIA ,F.SALIDA, ARTICULO, CANT.SALIDA, ALM.DESTINO, F.INGRESO, CANT.INGRESO,	DIFERENCIA, COST.ORI,  COST.DES, TIPO ITEM";
 
  
  sacarExcelDifTrans($user,$titulo,$almacen,$cabecera,$A);
    
  print '
  ?> -->
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
	$cod_almacen = $K[0];
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
	$rs1 = reporte_diftrans($cod_almacen,$trans_cod);
 	
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
  } //FIN DEL IF DEL ACTION = EXPORTAR osea el if general
  ?>-->
</body>
</html>
