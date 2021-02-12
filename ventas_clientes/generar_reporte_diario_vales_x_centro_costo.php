<?php
include("../valida_sess.php");
//include("../menu_princ.php");
include("../config.php");
include("../functions.php");
include("store_procedures.php");
require("../clases/funciones.php");
$funcion = new class_funciones;

	// crea la clase para controlar errores
	$clase_error = new OpensoftError;
	$clase_error->_error();
	
	// conectar con la base de datos
	$conector_id=$funcion->conectar("","","","","");
	$v = "$c_tipo, $c_serie ,$c_numero, $c_proveedor";
	//echo $v;
	$fecha_actual = date("d/m/Y h:i:s");
	$col = 10;
$lin = str_repeat("=",240);
$salto = chr(12);
$bordes = 0;
switch($accion){
	
	case "Reporte":	
		if($c_est==""){$c_est="TODAS";}
		$rs = REPORTE_RESUMEN_DIARIO_VALES_X_CENTRO_COSTO($c_est, $c_fec_desde,$c_fec_hasta,$c_cliente);
		break;

	case "Acrobat":
		header("Content-type: application/pdf");
		readfile("/sistemaweb/tmp/diario_vales-" . $_COOKIE['PHPSESSID'] . ".pdf");
		exit;
		
	case "Excel":
		header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=reporte_diario_vales_x_centro_costo.xls");
		if($c_est==""){$c_est="TODAS";}
		$rs = REPORTE_RESUMEN_DIARIO_VALES_X_CENTRO_COSTO($c_est, $c_fec_desde,$c_fec_hasta,$c_cliente);
		$bordes = 1;
	
	break;

	case "Imprimir":
		
		$rs = pg_exec("select par_valor as print_server from int_parametros 
	    where par_nombre ='print_server' 
		UNION
		select par_valor as print_netbios from int_parametros 
	    where par_nombre ='print_netbios' 
		UNION 
		select par_valor as print_name from int_parametros 
	    where par_nombre ='print_name' ");
	    
		$print_server =  pg_result($rs,0,"print_server");
		$print_netbios = pg_result($rs,1,"print_netbios");
		$print_name = pg_result($rs,2,"print_name");
		
		$txt = "/sistemaweb/ventas_clientes/reportes/pdf/reporte_ventas_diarias.pdf";
		
//		exec("smbclient //".$print_netbios."/".$print_name." -c 'print /tmp/".$txt."' -P -N -I ".$print_server." ");
		pg_close();
		
		
	break;
}
?>

<html>
<head>
<link rel="stylesheet" href="../cpagar/js/style.css" type="text/css" media="screen"/>
<link rel="stylesheet" href="/sistemaweb/cpagar/js/print.css" type="text/css" media="print"/>

<title>REPORTE DIARIO DE CONSISTENCIA DE VALES x CENTRO DE COSTO</title>
<script>
	function imprimirCintillo(form){
		form.accion.value="Imprimir";
		form.submit();
	}
</script>
</head>
<body>
<form name="form1" method="post"> 
  <table border="0" cellpadding="0" cellspacing="0">
    <tr class="letra_titulo"> 
      <td colspan="2" align="left">ALMACEN : <?php echo $cab_almacen." - ".otorgarAlmacen($conector_id, $_SESSION["almacen"]);?> 
        <input type="hidden" name="accion"></td>
      <?php $BUF = $cab_almacen." - ".otorgarAlmacen($conector_id, $cab_almacen)."\n";?>
      <th width="378" align="right"> 
      <th><a href="generar_reporte_diario_vales_x_centro_costo.php?accion=Excel&c_est=<?php echo $c_est;?>&c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&c_cliente=<?php echo $c_cliente;?>&nombre=<?php echo $nombre; ?>"><font size="2">EXCEL</font></a></th>
      <th><a href="generar_reporte_diario_vales_x_centro_costo.php?accion=Acrobat&c_est=<?php echo $c_est;?>&c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&c_cliente=<?php echo $c_cliente;?>&nombre=<?php echo $nombre; ?>"><font size="2">Acrobat</font></a></th>
    <tr class="letra_titulo"> 
      <td align="center" colspan="2">&nbsp;</td>
      <td align="center" <?php if ($accion=='Excel') echo 'colspan=8'; ?> >REPORTE DIARIO DE CONSISTENCIA DE VALES x CENTRO DE COSTO</td>
      <?php $BUF = $BUF.str_pad("CINTILLO DE LIQUIDACION DE COMPRAS        $fecha_actual\n\n",80," ",STR_PAD_LEFT);?>
      <td width="166" align="center"><?php echo $fecha_actual;?></td>
    <tr class="letra_titulo"> 
      <td width="201">&nbsp; </td>
      <td width="19">&nbsp;</td>
      <td <?php if ($accion=='Excel') echo 'colspan=8'; ?> ><div align="center">Desde<?php echo $c_fec_desde;?>: Hasta:<?php echo $c_fec_hasta;?></div></td>
      <td>&nbsp;</td>
    <tr class="letra_titulo">
		<td colspan="10"><div align="left">Cliente: <?php echo $c_cliente; ?> - <?php echo $nombre;?></div></td>
  </table>
<br>
  <?php if($accion=="Excel"){$bg="#FFFFFF";}else{$bg="#BBBBBB";}
	?>
  <table border="<?php echo $bordes;?>" cellspacing="1" cellpadding="2" bgcolor="#BBBBBB" width="80%">
  	
    <tr class="letra_cabecera"> 
      <td width="13%" rowspan="2"><font size="2">C.C DESCRIPCION<br>
        </font></td>
      <td height="39" colspan="2"><div align="center"><font size="2">84</font></div></td>
      <td colspan="2"><div align="center"><font size="2">95</font></div></td>
      <td colspan="2"><div align="center"><font size="2">DIESEL 2</font></div></td>
      <td colspan="2"><div align="center"><font size="2">KEROSENE</font></div></td>
      <td colspan="2"><div align="center"><font size="2">90</font></div></td>
      <td colspan="2"><div align="center"><font size="2">97</font></div></td>
      <td colspan="2"><div align="center"><font size="2">GLP</font></div></td>
      <td width="7%"><div align="center"><font size="2">LUBRI.</font></div></td>
      <td width="7%"><div align="center"><font size="2">OTROS</font></div></td>
      <td width="9%"><div align="center"><font size="2">TOTALES</font></div></td>
    </tr>
    <tr class="letra_cabecera"> 
      <td width="4%" height="23"> <div align="center"><font size="2">GAL</font></div></td>
      <td width="4%"><div align="center"><font size="2">IMP</font></div></td>
      <td width="4%"><div align="center"><font size="2">GAL</font></div></td>
      <td width="4%"><div align="center"><font size="2">IMP</font></div></td>
      <td width="4%"  ><div align="center"><font size="2">GAL</font></div></td>
      <td width="7%" ><div align="center"><font size="2">IMP</font></div></td>
      <td width="4%" ><div align="center"><font size="2">GAL</font></div></td>
      <td width="9%" ><div align="center"><font size="2">IMP</font></div></td>
      <td width="4%"><div align="center"><font size="2">GAL</font></div></td>
      <td width="4%"><div align="center"><font size="2">IMP</font></div></td>
      <td width="4%"><div align="center"><font size="2">GAL</font></div></td>
      <td width="4%"><div align="center"><font size="2">IMP</font></div></td>
      <td width="4%"><div align="center"><font size="2">GAL</font></div></td>
      <td width="4%"><div align="center"><font size="2">IMP</font></div></td>
      <td><font size="2">&nbsp;</font></td>
      <td><font size="2">&nbsp;</font></td>
      <td><font size="2">&nbsp;</font></td>
    </tr>
    <?php for($i=0;$i<pg_numrows($rs);$i++){
  	$A = pg_fetch_array($rs,$i);
  	
	
  	$tot_gal84 +=$A["gal84"];
    $tot_imp84 +=$A["imp84"];
    $tot_gal95 +=$A["gal95"];
    $tot_imp95 +=$A["imp95"];
    $tot_gald2 +=$A["gald2"];
    $tot_impd2 +=$A["impd2"];
    $tot_galkd +=$A["galkd"];
    $tot_impkd +=$A["impkd"];
    $tot_gal90 +=$A["gal90"];
    $tot_imp90 +=$A["imp90"];
    $tot_gal97 +=$A["gal97"];
    $tot_imp97 +=$A["imp97"];
    $tot_litglp +=$A["litglp"];
    $tot_impglp +=$A["impglp"];
    $tot_lubricantes +=$A["imp_lubricantes"];
	$tot_total +=$A["imp_total"];
    $tot_otros +=$A["imp_otros"];
    $tot_gal +=$A["imp_total"];
  	
  ?>
    <tr class="letra_detalle"> 
      <td><font size="2">C.COSTO <?php echo $A["ch_sucursal"]." ".$A["des_sucursal"];?></font></td>
      <td height="22"><font size="2"><?php echo $A["gal84"];?></font></td>
      <td><font size="2"><?php echo $A["imp84"];?></font></td>
      <td><font size="2"><?php echo $A["gal95"];?></font></td>
      <td><font size="2"><?php echo $A["imp95"];?></font></td>
      <td><font size="2"><?php echo $A["gald2"];?></font></td>
      <td><font size="2"><?php echo $A["impd2"];?></font></td>
      <td ><font size="2"><?php echo $A["galkd"];?></font></td>
      <td ><font size="2"><?php echo $A["impkd"];?></font></td>
      <td><font size="2"><?php echo $A["gal90"];?></font></td>
      <td><font size="2"><?php echo $A["imp90"];?></font></td>
      <td><font size="2"><?php echo $A["gal97"];?></font></td>
      <td><font size="2"><?php echo $A["imp97"];?></font></td>
      <td><font size="2"><?php echo $A["litglp"];?></font></td>
      <td><font size="2"><?php echo $A["impglp"];?></font></td>
      <td><font size="2"><?php echo $A["imp_lubricantes"];?></font></td>
      <td><font size="2"><?php echo $A["imp_otros"];?></font></td>
      <td><font size="2"><?php echo $A["imp_total"];?></font></td>
    </tr>
    <?php } ?>
    <tr class="letra_detalle"> 
      <td><font size="2"><strong><br>
        TOTAL GENERAL :</strong></font></td>
      <td height="22"><font size="2"><strong><?php echo $tot_gal84;?><br>
        </strong></font></td>
      <td><font size="2"><strong><?php echo $tot_imp84;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_gal95;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_imp95;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_gald2;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_impd2;?></strong></font></td>
      <td ><font size="2"><strong><?php echo $tot_galkd;?></strong></font></td>
      <td ><font size="2"><strong><?php echo $tot_impkd;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_gal90;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_imp90;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_gal97;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_imp97;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_litglp;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_impglp;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_lubricantes;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_otros;?></strong></font></td>
      <td><font size="2"><strong><?php echo $tot_total;?></strong></font></td>
    </tr>
  </table>
</form>
<?php pg_close();?>
</html>
