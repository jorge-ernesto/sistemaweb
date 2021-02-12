<?php
session_start();
include("../config.php");
include("store_procedures.php");

$user_temp = $user."_rep3";
$titulo = "Detalle de Ventas del Punto $cod_almacen - $almacen_dis DEL: $fechaa AL: $fechad";

if($action=="exportar"){
	$user_temp = $user."_rep3";
	
	exec(" mv /var/www/html/sistemaweb/tmp/$user_temp.txt /var/www/html/sistemaweb/tmp/$user_temp.csv");
	$url = "/sistemaweb/tmp/$user_temp.csv";
	?>
	<script language="JavaScript1.3" type="text/javascript">
	window.open('<?php echo $url;?>','miwin','width=10,height=35,scrollbars=yes');
	</script>

	<?php
}else{
	
	exec("echo -e '$titulo \n \n' > /var/www/html/sistemaweb/tmp/$user_temp.txt");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Detalle de Ventas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<div align="center"> 
  <table width="767" border="1" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="457"><a href="reporte_detalle_ventas.php?action=exportar" >Exportar 
        a Excel</a></td>
      <td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
    </tr>
  </table>
  <strong><font size="2" face="Arial, Helvetica, sans-serif">Detalle de Ventas 
  del Punto <?php echo $cod_almacen." - ".$almacen_dis;?> DEL: <?php echo $fechaa;?> AL: 
  <?php echo $fechad;?></font></strong> <br>
</div>
  <!-- {Tabla}
<?php	 
	$rs2 = pg_exec("select to_char(dt_fac_fecha,'dd/mm/yyyy'),trim(ch_fac_seriedocumento) as serie 
	,ch_fac_numerodocumento from fac_ta_factura_cabecera where 	
	dt_fac_fecha >= to_date('$fechad','dd/mm/yyyy') and
	dt_fac_fecha <= to_date('$fechaa','dd/mm/yyyy') and
	trim(ch_fac_seriedocumento) = '$cod_almacen'");
	/*echo "select to_char(dt_fac_fecha,'dd/mm/yyyy'),trim(ch_fac_seriedocumento) as serie 
	,ch_fac_numerodocumento from fac_ta_factura_cabecera where 	
	dt_fac_fecha >= to_date('$fechad','dd/mm/yyyy') and
	dt_fac_fecha <= to_date('$fechaa','dd/mm/yyyy') and
	trim(ch_fac_seriedocumento) = '$cod_almacen'";*/
	for($j=0;$j<pg_numrows($rs2);$j++){ //FOR PARA LAS TABLAS 
	$B = pg_fetch_array($rs2,$j);
	$cabecera = " $B[0] CAJA $B[1]$B[2] \n ITEM , CANTIDAD , PREC_VENTA , PREC_LISTA , DIFERENCIA , VALOR_VENTA , IMPUESTOS , TOTAL";

	
?>
 -->
  <font size="-4" face="Arial, Helvetica, sans-serif"><strong><em><?php echo $B[0];?> 
  CAJA <?php echo $B[1];?><?php echo $B[2];?></em></strong> </font> </div> 
<table width="740" border="1" cellpadding="0" cellspacing="0">
  <tr> 
    <td width="221"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font><font size="-4" face="Arial, Helvetica, sans-serif">ITEM</font></div></td>
    <td width="84"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">CANTIDAD</font></div></td>
    <td width="77"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PREC.VENTA</font></div></td>
    <td width="69"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PREC.LISTA</font></div></td>
    <td width="71"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DIFERENCIA</font></div></td>
    <td width="73"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">VALOR 
        VENTA </font></div></td>
    <td width="63"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">IMPUESTOS</font></div></td>
    <td width="71"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL</font></div></td>
  </tr>
  <!-- {FILA}<?php 
  $rs1 = reporte_detalle_ventas(trim($cod_almacen),$B[0],$B[2]);
  $total_venta = 0;
  $total_imp = 0;
  $total = 0;
  sacarExcelDetVentas($user,$titulo,$cod_almacen,$cabecera,$rs1);
  $cabecera = "TIPO DE ITEM , VALOR NETO , IMPUESTOS , TOTAL VENTA , PORCENTAJE"; 
  
  for($i=0;$i<pg_numrows($rs1);$i++){
  $A = pg_fetch_array($rs1,$i);
  $total_venta = $total_venta + $A[5];
  $total_imp = $total_imp + $A[6];
  $total = $total + $A[7];
  
 	
  		$k++;	
  print '
  ?> {FILA}-->
  <tr> 
    <td height="15">
<div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[0].' - <font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[1].'</font></font></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></font></font><font face="Arial, Helvetica, sans-serif"></font></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[3].'</font></font></font><font face="Arial, Helvetica, sans-serif"></font></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif">-</font></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">-</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[5].'</font></font></font></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[6].'</font></font></font></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[7].'</font></font></font></font></div></td>
  </tr>
  <!--{FILAS} <?php   ';
  echo "ESTE FOR CORRIO ".$i;
   }
   ?> -->
  <tr> 
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><strong>TOTAL</strong></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total_venta;?></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total_imp;?></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total;?></font></div></td>
  </tr>
</table>
<!-- {TABLA}
<?php 
$BASE = "TOTAL , ,,,,$total_venta,$total_imp,$total";
exec("echo -e '$BASE \n' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
//exec("echo -e '' >> /var/www/html/sistemaweb/tmp/$user_temp.txt");
 }

} //FIN DEL IF DE EXPORTAR
?>

-->
<br>
<!--SEGUNDO PROCEDIMIENTO -->
<font size="-4" face="Arial, Helvetica, sans-serif">RESUMEN POR TIPO DEL:<?php echo $fechad;?> 
al <?php echo $fechaa;?> </font> 
<table border="1" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="159"><div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">TIPO 
        ITEM</font></div></td>
    <td width="77"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">VALOR 
        NETO </font></div></td>
    <td width="67"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">IMPUESTOS</font></div></td>
    <td width="52"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TOTAL</font></div></td>
    <td width="109"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PORCENTAJE</font></div></td>
  </tr>
  <!--BEGIN FILA 
	<?php 
	$rs22 = reporte_detalle_ventasxtipo($cod_almacen,$fechad,$fechaa);
	$titulo ="RESUMEN POR TIPO DEL:$fechad al $fechaa ";
	$cabecera = "TIPO ITEM , VALOR NETO , IMPUESTOS , TOTAL , PORCENTAJE" ;
	for($i=0;$i<pg_numrows($rs22);$i++){
		$S = pg_fetch_array($rs22,$i);
		$total_vn2 = $total_vn2 + $S[1];
		$total_imp2 = $total_imp2 + $S[2];
		$total2 =  $total2 + $S[3];
	}
	$T[0] = $total_vn2;
	$T[1] = $total_imp2;
	$T[2] = $total2;
	$T[3] = "100%";
	sacarExcelDetVentasXTipo($user,$titulo,$almacen,$cabecera,$rs22,$T);
	for($i=0;$i<pg_numrows($rs22);$i++){
	$S = pg_fetch_array($rs22,$i);
	$porcentaje = 100*($S[1]/$total_vn2); 
	print '
	-->
  <tr> 
    <td><div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;'.$S[0].'</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$S[1].'</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$S[2].'</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$S[3].'</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$porcentaje.'%</font></div></td>
  </tr>
  <!-- END FILA
	'; } 
	
	?>
	-->
  <tr> 
    <td><div align="left"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;TOTALES 
        DEL RESUMEN</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $total_vn2;?></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $total_imp2;?>&nbsp;</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $total2;?></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">100%&nbsp;</font></div></td>
  </tr>
</table>
<font size="-4" face="Arial, Helvetica, sans-serif"> 
<!--FIN DEL SEGUNDO PROCEDIEMIENTO -->
</font>
</body>
</html>
<?php  pg_close();?>
