<?php
extract($_REQUEST);
include("config.php");
include("store_procedures.php");
$user=$usuario;

$user_temp = $user."_rep4";
if($action=="exportar"){
	
	exec(" mv /var/www/html/sistemaweb/tmp/$user_temp.txt /var/www/html/sistemaweb/tmp/$user_temp.csv");
	$url = "/sistemaweb/tmp/$user_temp.csv";
	?>
	<script language="JavaScript1.3" type="text/javascript">
	window.open('<?php echo $url;?>','miwin','width=10,height=35,scrollbars=yes');
	</script>

	<?php
}else{
$titulo = "DETALLE DE VENTAS MENSUALES";
switch($modo){
	case "todo":
	$tabla = $tabla_todo;
	$fila = $fila_todo;
	$modo_dis = "Todo";
	$cabecera = "Articulo, can01, val01 ,can02 ,val02 ,cab03 ,val03 ,can04 ,val04 ,can05 ,val05, can06 ,val06 ,can07 ,val07 ,can08 ,val08, can09, val09, can10 ,val10 ,can11 ,val11 ,can12 ,val12 ";
	break;
	
	case "cantidades":
	$tabla = $tabla_cantidad;
	$fila = $fila_cantidad;
	$cabecera = "Articulo ,can01 ,can02 ,cab03 ,can04 ,can05 ,can06 ,can07 ,can08 ,can09 ,can10 ,can11 ,can12 ";
	break;
	
	case "valores":
	$tabla = $tabla_valor;
	$cabecera = "Articulo, val01 ,val02 ,val03 ,val04, val05 ,val06 ,val07 ,val08, val09 ,val10 ,val11, val12 ";
	$modo_dis = "Solo Valores";
	break;
}
$c1 = "<!--";
$c2 = "-->";
$rs = reporte_ventas_mensuales($periodo,$cod_almacen,$cod_linea,$modo);
//creamos el cvs

$cabecera = "Periodo $periodo \n".$cabecera;
sacarExcelRepVentasMensuales($usuario,$titulo,$cod_almacen,$cabecera,$rs);
}//FIN DEL IF DE ACTION!=EXPORTAR
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Reporte de Ventas Mensuales</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table width="767" border="1" cellpadding="0" cellspacing="0">
  <tr> 
    <td width="457"><a href="reporte_ventas_mensuales.php?action=exportar" >Exportar 
      a Excel</a></td>
    <td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
  </tr>
</table>
<div align="center"><strong><font size="2" face="Arial, Helvetica, sans-serif">Detalle 
  de Ventas Mensuales</font></strong> <font size="1" face="Arial, Helvetica, sans-serif"> 
  </font></div>
<br>
P<font size="1" face="Arial, Helvetica, sans-serif">eriodo: <?php echo $periodo;?></font> 
<br>
<table width="744" border="1" cellpadding="0" cellspacing="0">
  <?php print $tabla;
   /*
   ?> 
  <tr> 
    <td width="200"><font size="-4" face="Arial, Helvetica, sans-serif">Codigo y Descripcion del Articulo</font></td>
    <td width="28"><font size="-4" face="Arial, Helvetica, sans-serif">can01</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val01</font></td>
    <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can02</font></td>
    <td width="26"><font size="-4" face="Arial, Helvetica, sans-serif">val02</font></td>
    <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">cab03</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val03</font></td>
    <td width="28"><font size="-4" face="Arial, Helvetica, sans-serif">can04</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val04</font></td>
    <td width="28"><font size="-4" face="Arial, Helvetica, sans-serif">can05</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val05</font></td>
    <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can06</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val06</font></td>
    <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can07</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val07</font></td>
    <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can08</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val08</font></td>
    <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can09</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val09</font></td>
    <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can10</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val10</font></td>
    <td width="25"><font size="-4" face="Arial, Helvetica, sans-serif">can11</font></td>
    <td width="23"><font size="-4" face="Arial, Helvetica, sans-serif">val11</font></td>
    <td width="29"><font size="-4" face="Arial, Helvetica, sans-serif">can12</font></td>
    <td width="26"><font size="-4" face="Arial, Helvetica, sans-serif">val12</font></td>
  </tr>
  <!--<?php */  ?>
    <!-- <?php
	if($modo!="todo"){
	$com1=$c1;
	$com2=$c2;
	}
	for($i=0;$i<pg_numrows($rs);$i++){  
	$A = pg_fetch_array($rs,$i);  
	print '
	?> -->
  <tr> 
    <td><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[3].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[4].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[5].'</font></td>
    <td width="5"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[6].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[7].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[8].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[9].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[10].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[11].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[12].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[13].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[14].'</font></td>
    '.$com1.'
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[15].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[16].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[17].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[18].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[19].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[20].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[21].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[22].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[23].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[24].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$A[25].'</font></td>
    <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;&nbsp;'.$A[26].'</font></td>
    '.$com2.' </tr>
  <!-- <?php '; } ?> -->
</table>
</body>
</html>
