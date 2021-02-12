<?php

//include("../valida_sess.php");
//include("../config.php");
//include("store_procedures.php");
//include("inc_top.php");
include("../menu_princ.php");
include("include/utils.php");
//include("../menu_princ.php");

$accion      = $_POST['accion'];
$c_fec_desde = $_POST['c_fec_desde'];
$c_fec_hasta = $_POST['c_fec_hasta'];

$hoy = date("d/m/Y");
if($c_fec_desde==""){$c_fec_desde=$hoy;}else{$c_fec_desde=$_POST['c_fec_desde'];}
if($c_fec_hasta==""){$c_fec_hasta=$hoy;}else{$c_fec_hasta=$_POST['c_fec_hasta'];}

$col = 10;
$lin = str_repeat("=",240);
$lin1 = str_repeat("-",240);
$lin2 = str_repeat("=",240);
$salto = chr(12);
$nombre_cliente = "";
switch($accion){
	
	case "Reporte":	
		
		if($c_est==""){$c_est="TODAS";}
		if($c_cliente==""){$c_cliente="TODOS";}else{
			$rs = pg_exec("select cli_razsocial from int_clientes where cli_codigo='".$c_cliente."'");
			$nombre_cliente = pg_result($rs,0);
			print_r($nombre_cliente);
		}
    $rs = REPORTE_RESUMEN_DIARIO_VALES_X_CENTRO_COSTO($c_est, $c_fec_desde,$c_fec_hasta,$c_cliente);
    echo "<pre>";
    print_r($rs);
    echo "</pre>";
		
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
		
		$txt = "/tmp/reporte_ventas_diarias.txt";
		
		exec("smbclient //".$print_netbios."/".$print_name." -c 'print /tmp/".$txt."' -P -N -I ".$print_server." ");
		pg_close();
		

		
	break;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Reporte de Ventas Diarias</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="reportes.css" rel="stylesheet" type="text/css">
<script language="JavaScript">
	function mandarDatos(form,opt){
		form.accion.value = opt;
		form.submit();
	}
</script>
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
</head>

<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="form1" action="" method="post">
  <table width="774" border="0" cellpadding="1" cellspacing="1">
    <tr> 
      <td width="22%" height="24">&nbsp;</td>
      <td colspan="3"><div align="center">REPORTE DIARIO DE CONSISTENCIA DE VALES 
          x CENTRO DE COSTO</div></td>
      <td width="17%">
        <!--<a href="#" onClick="javascript:accion.value='Imprimir',form1.submit();">IMPRIMIR</a>-->
        <a href="#" onClick="javascript:window.open('generar_reporte_diario_vales_x_centro_costo.php?c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&accion=<?php echo $accion;?>&c_opt_reporte=<?php echo $c_opt_reporte;?>&c_est=<?php echo $c_est;?>&c_cliente=<?php echo $c_cliente;?>&nombre=<?php echo $nombre_cliente; ?>','winrep1','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">IMPRESION</a> 
      </td>
    </tr>
    <tr> 
      <td><div align="right">Estacion: 
          <input type="text" name="c_est" size="11" value="<?php echo $c_est;?>">
        </div></td>
      <td width="20%" rowspan="2">Desde: 
        <input type="text" name="c_fec_desde" size="11" value="<?php echo $c_fec_desde;?>"> <a href="javascript:show_calendar('form1.c_fec_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
      <td width="20%" rowspan="2">Hasta: 
        <input type="text" name="c_fec_hasta" size="11" value="<?php echo $c_fec_hasta;?>"> <a href="javascript:show_calendar('form1.c_fec_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
      <td width="29%" rowspan="2"> <input type="radio" name="c_opt_reporte" value="RESUMIDO" <?php if($c_opt_reporte=="RESUMIDO"){echo "checked";} ?> >
        Resumido <br> <input type="radio" name="c_opt_reporte" value="DETALLADO" <?php if($c_opt_reporte=="DETALLADO"){echo "checked";} ?>>
        Detallado </td>
      <td rowspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td><div align="right">Cliente: 
          <input type="text" name="c_cliente" size="11" value="<?php echo $c_cliente; ?>">
        </div></td>
    </tr>
    <tr> 
      <td height="28">&nbsp;</td>
      <td>&nbsp;</td>
      <td><input type="submit" name="btn_reporte" value="Reporte" onClick="javascript:mandarDatos(form1,'Reporte');"> 
        <input type="hidden" name="accion"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>

<table width="100%" border="1" cellpadding="2" cellspacing="0">

	<tr>
		<td width="25%">CLIENTE: <br> </td>
		<td colspan="17"><?php echo $c_cliente; ?> - <?php echo $nombre_cliente; ?></td>
	</tr>

  <tr> 
    <td width="25%" rowspan="2">C.C DESCRIPCION<br> </td>
    <td height="39" colspan="2"><div align="center">84</div></td>
    <td colspan="2"><div align="center">95</div></td>
    <td colspan="2"><div align="center">DIESEL 2</div></td>
    <td colspan="2"><div align="center">KEROSENE</div></td>
    <td colspan="2"><div align="center">90</div></td>
    <td colspan="2"><div align="center">97</div></td>
    <td colspan="2"><div align="center">GLP</div></td>
    <td width="6%"><div align="center">LUBRI.</div></td>
    <td width="6%"><div align="center">OTROS</div></td>
    <td width="11%"><div align="center">TOTALES</div></td>
  </tr>
  <tr> 
    <td width="3%" height="22"><div align="center">GAL</div></td>
    <td width="4%"><div align="center">IMP</div></td>
    <td width="3%"><div align="center">GAL</div></td>
    <td width="4%"><div align="center">IMP</div></td>
    <td width="3%"  ><div align="center">GAL</div></td>
    <td width="4%" ><div align="center">IMP</div></td>
    <td width="5%" ><div align="center">GAL</div></td>
    <td width="5%" ><div align="center">IMP</div></td>
    <td width="3%"><div align="center">GAL</div></td>
    <td width="4%"><div align="center">IMP</div></td>
    <td width="3%"><div align="center">GAL</div></td>
    <td width="4%"><div align="center">IMP</div></td>
    <td width="3%"><div align="center">GAL</div></td>
    <td width="4%"><div align="center">IMP</div></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
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

	  $reporte_txt = $reporte_txt.$tmp_rep_ch_numeval;
	  $reporte_txt = $reporte_txt.$A["ch_sucursal"]." ".$A["des_sucursal"];
	  $reporte_txt = $reporte_txt.str_pad($A["dt_fecha"],3," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["imp84"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["gal84"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["imp95"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["gal95"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["impd2"], 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["gald2"], 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["impkd"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["galkd"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["imp90"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["gal90"], 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["imp97"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["gal97"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["impglp"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["litglp"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["imp_lubricantes"], 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["imp_otros"], 2, '.', ','),13," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($A["imp_total"], 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt."\n";
	  $reporte_txt .= $lin2."\n";

  ?>
  <tr> 
    <td>C.COSTO <?php echo $A["ch_sucursal"]." ".$A["des_sucursal"];?></td>
    <td height="23" align="right"><?php echo number_format($A["gal84"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["imp84"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["gal95"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["imp95"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["gald2"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["impd2"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["galkd"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["impkd"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["gal90"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["imp90"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["gal97"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["imp97"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["litglp"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["impglp"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["imp_lubricantes"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["imp_otros"], 2, ".", ",")?></td>
    <td align="right"><?php echo number_format($A["imp_total"], 2, ".", ",")?></td>
    <?php
      //$value = number_format($valores[$nombre], 2, ".", ",");
    ?>
  </tr>
  <?php } ?>
  <tr> 
    <td><strong>TOTAL GENERAL :</strong></td>
    <td height="22" align="right"><strong><?php echo number_format($tot_gal84, 2, ".",",")?><br>
      </strong></td>
    <td align="right"><strong><?php echo number_format($tot_imp84, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_gal95, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_imp95, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_gald2, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_impd2, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_galkd, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_impkd, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_gal90, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_imp90, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_gal97, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_imp97, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_litglp, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_impglp, 2, ".",",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_lubricantes, 2, ".", ",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_otros, 2, ".", ",")?></strong></td>
    <td align="right"><strong><?php echo number_format($tot_total, 2, ".", ",")?></strong></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>
<?php
	  $reporte_txt = $reporte_txt."TOTAL GENERAL :";
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp84, 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal84, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp95, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal95, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_impd2, 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gald2, 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_impkd, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_galkd, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp90, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal90, 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp97, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal97, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_impglp, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_litglp, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_lubricantes, 2, '.', ','),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_otros, 2, '.', ','),12," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_total, 2, '.', ','),14," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt."\n";
	  $reporte_txt = $reporte_txt.$lin2."\n";  	

?>
<?php pg_close();
    $cab[0] = "Sucursal {sucursal} ".str_pad("REPORTE DIARIO DE CONSISTENCIA DE VALES x CENTRO DE COSTO",195," ", STR_PAD_BOTH)." Pag. {pagina}";
    //$cab = $cab.str_pad("Del $c_fec_desde AL $c_fec_hasta",220," ", STR_PAD_BOTH)."\n";
    $cab[1] = str_pad("Del $c_fec_desde AL $c_fec_hasta",130," ",STR_PAD_LEFT);
    $cab[2] = "Usuario:{usuario} - Fecha: {fecha} - Cliente: ".$c_cliente.' - '.$nombre_cliente;
    $cab[3] = $lin2;
    $cab[4] = "C.C DESCRIPCION              84                  95                  DIESEL 2               KEROSENE               90                  97                 GLP            LUBRI.      OTROS     TOTALES";
    $cab[5] = "NUMERACION DE VALES     IMP      GAL        IMP      GAL          IMP        GAL         IMP        GAL      IMP         GAL      IMP       GAL      IMP        GAL      ";
    $cab[6] = $lin2;
	$man_arch = fopen("/tmp/archivo-diario-vale-".$_COOKIE["PHPSESSID"].".txt","w");
	fwrite($man_arch,$reporte_txt);
    fclose($man_arch);
	crearPostscript("/tmp/archivo-diario-vale-".$_COOKIE["PHPSESSID"].".txt","/sistemaweb/tmp/diario_vales-".$_COOKIE['PHPSESSID'].".pdf",$cab);
	//$ar = file("/tmp/reporte_ventas_diarias.txt");
	//echo count($ar);
	//echo $BUF;	
