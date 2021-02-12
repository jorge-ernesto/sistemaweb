<?php

include("../menu_princ.php");
$hoy = date("d/m/Y");
if($c_fec_desde==""){$c_fec_desde=$hoy;}
if($c_fec_hasta==""){$c_fec_hasta=$hoy;}

$col = 10;
$lin = str_repeat("=",240);
$salto = chr(12);
switch($accion){
	
	case "Reporte":	
		
		if($c_est==""){$c_est="TODAS";}
		if($c_cliente==""){$c_cliente="TODOS";}
		$sql = "select to_date('$c_fec_hasta_0','dd/mm/yyyy') -  to_date('$c_fec_desde_0','dd/mm/yyyy') as rango_1,
		to_date('$c_fec_hasta_1','dd/mm/yyyy') -  to_date('$c_fec_desde_1','dd/mm/yyyy') as rango_0";

		$rs = pg_exec($sql);

		$rango_0 = pg_result($rs,0,"rango_0") + 1;
		$rango_1 = pg_result($rs,0,"rango_1") + 1;
		
		$rs = REPORTE_ESTADISTICA_VENTAS($c_fec_desde_0,$c_fec_hasta_0,$c_fec_desde_1,$c_fec_hasta_1);
		
	break;

	case "Imprimir":
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
</head>

<body>
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<form name="form1" action="" method="post">
  <table width="774" border="0" cellpadding="1" cellspacing="1">
    <tr> 
      <td height="24" colspan="2">&nbsp;</td>
      <td colspan="3"><div align="center">ESTADISTICA DE VENTAS</div></td>
      <td width="18%"> 
        <a href="#" onClick="javascript:window.open('generar_reporte_diario_vales_x_centro_costo.php?c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&accion=<?php echo $accion;?>&c_opt_reporte=<?php echo $c_opt_reporte;?>&c_est=<?php echo $c_est;?>&c_cliente=<?php echo $c_cliente;?>','winrep1','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">IMPRESION</a> 
      </td>
    </tr>
    <tr> 
      <td width="6%"><div align="right">: </div></td>
      <td width="15%">&nbsp;</td>
      <td width="16%">FECHA ANTERIOR</td>
      <td width="20%">Desde: 
        <input type="text" name="c_fec_desde_0" size="11" value="<?php echo $c_fec_desde_0;?>"><a href="javascript:show_calendar('form1.c_fec_desde_0');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td>
      <td width="20%">Hasta: 
        <input type="text" name="c_fec_hasta_0" size="11" value="<?php echo $c_fec_hasta_0;?>"><a href="javascript:show_calendar('form1.c_fec_hasta_0');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>
      </td>
      <td rowspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right"></div></td>
      <td>&nbsp;</td>
      <td width="16%">FECHA ACTUAL</td>
      <td width="20%">Desde: 
        <input type="text" name="c_fec_desde_1" size="11" value="<?php echo $c_fec_desde_1;?>"><a href="javascript:show_calendar('form1.c_fec_desde_1');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td>
      <td width="20%">Hasta: 
        <input type="text" name="c_fec_hasta_1" size="11" value="<?php echo $c_fec_hasta_1;?>"><a href="javascript:show_calendar('form1.c_fec_hasta_1');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></td>
    </tr>
    <tr> 
      <td height="28" colspan="2">&nbsp;</td>
      <td>&nbsp;</td>
      <td><input type="submit" name="btn_reporte" value="Reporte" onClick="javascript:mandarDatos(form1,'Reporte');"> 
        <input type="hidden" name="accion"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>

<table width="81%" border="1">
  <tr> 
    <td width="13%" rowspan="2"><b> ESTACIONES <br></b></td>
    <td height="20" colspan="6"> <div align="center"><b> GALONES </b></div></td>
    <td width="14%"><div align="center"><b> TOTAL </b><br>
      </div></td>
    <td width="4%"><div align="center"></div></td>
    <td colspan="6"><div align="center"><b> SOLES </b></div></td>
    <td width="7%"><div align="center"><b> TOTAL </b></div></td>
  </tr>
  <tr> 
    <td width="3%" height="22"> <div align="center"><b> 84 </b></div></td>
    <td width="4%"><div align="center"><b> 90 </b></div></td>
    <td width="3%"><div align="center"><b> 95 </b></div></td>
    <td width="4%" ><div align="center"><b> 97 </b></div></td>
    <td width="3%" ><div align="center"><b> D2 </b></div></td>
    <td width="3%" > <div align="center"><b> KD </b></div>
      <div align="center"></div></td>
    <td><b>GALONES</b></td>
    <td><b>GLP</b></td>
    <td width="10%"><div align="center"><b>ACCESOR.</b></div></td>
    <td width="8%"><div align="center"><b>SERVIC.</b></div></td>
    <td width="7%"><b>LUBRI.</b></td>
    <td width="8%"><b>MARKET</b></td>
    <td width="5%"><b> WHIZ </b></td>
    <td width="4%"><b>  OB  </b></td>
    <td><b>SOLES</b></td>
  </tr>
  <?php for($i=0;$i<pg_numrows($rs);$i++){
  	$A = pg_fetch_array($rs,$i);
  	
	$total_comb = $A["gal90"]+$A["gal95"]+$A["gal97"]+$A["gald2"]+$A["galkd"]+$A["gal84"];
  			
  ?>
  <tr> 
    <td><?php echo $A["posicion"];?></td>
    <td height="23"><?php echo $A["gal84"];?></td>
    <td><?php echo $A["gal90"];?></td>
    <td><?php echo $A["gal95"];?></td>
    <td><?php echo $A["gal97"];?></td>
    <td><?php echo $A["gald2"];?></td>
    <td ><?php echo $A["galkd"];?></td>
    <td><?php echo $total_comb;?></td>
    <td><?php echo $A["litglp"];?></td>
    <td><?php echo $A["vt_a"];?></td>
    <td><?php echo $A["vt_s"];?></td>
    <td><?php echo $A["vt_l"];?></td>
    <td><?php echo $A["vt_m"];?></td>
    <td><?php echo $A["vt_w"];?></td>
    <td><?php echo $A["vt_o"];?></td>
    <td><?php echo $A["total_soles"];?></td>
  </tr>
  	<?php if($A["posicion"]=="ACTUAL"){
		$prom_84_0 = floor($A["gal84"]/$rango_0);
		$prom_90_0 = floor($A["gal90"]/$rango_0);
		$prom_95_0 = floor($A["gal95"]/$rango_0);
		$prom_97_0 = floor($A["gal97"]/$rango_0);
		$prom_d2_0 = floor($A["gald2"]/$rango_0);
		$prom_kd_0 = floor($A["galkd"]/$rango_0);
		$prom_litglp_0 = floor($A["litglp"]/$rango_0);
		$prom_vt_a_0 = floor($A["vt_a"]/$rango_0);
		$prom_vt_s_0 = floor($A["vt_s"]/$rango_0);
		$prom_vt_l_0 = floor($A["vt_l"]/$rango_0);
		$prom_vt_m_0 = floor($A["vt_m"]/$rango_0);
		$prom_vt_w_0 = floor($A["vt_w"]/$rango_0);
		$prom_vt_o_0 = floor($A["vt_o"]/$rango_0);
		
	?><tr>
    <td>&nbsp;</td>
    <td height="23"><?php echo floor($A["gal84"]/$rango_0);?></td>
    <td><?php echo floor($A["gal90"]/$rango_0);?></td>
    <td><?php echo floor($A["gal95"]/$rango_0);?></td>
    <td><?php echo floor($A["gal97"]/$rango_0);?></td>
    <td><?php echo floor($A["gald2"]/$rango_0);?></td>
    <td><?php echo floor($A["galkd"]/$rango_0);?></td>
    <td><?php echo $total_comb;?></td>
    <td><?php echo $prom_litglp_0;?></td>
    <td><?php echo $prom_vt_a_0;?></td>
    <td><?php echo $prom_vt_s_0;?></td>
    <td><?php echo $prom_vt_l_0;?></td>
    <td><?php echo $prom_vt_m_0;?></td>
    <td><?php echo $prom_vt_w_0;?></td>
    <td><?php echo $prom_vt_o_0;?></td>
    <td>&nbsp;</td>
  	</tr><?php } ?>
  
  	<?php if($A["posicion"]=="ANTERIOR"){
		$prom_84_1 = floor($A["gal84"]/$rango_1);
		$prom_90_1 = floor($A["gal90"]/$rango_1);
		$prom_95_1 = floor($A["gal95"]/$rango_1);
		$prom_97_1 = floor($A["gal97"]/$rango_1);
		$prom_d2_1 = floor($A["gald2"]/$rango_1);
		$prom_kd_1 = floor($A["galkd"]/$rango_1);
		$prom_glp_1 = floor($A["litglp"]/$rango_1);
		$prom_vt_a_1 = floor($A["vt_a"]/$rango_1);
		$prom_vt_s_1 = floor($A["vt_s"]/$rango_1);
		$prom_vt_l_1 = floor($A["vt_l"]/$rango_1);
		$prom_vt_m_1 = floor($A["vt_m"]/$rango_1);
		$prom_vt_w_1 = floor($A["vt_w"]/$rango_1);
		$prom_vt_o_1 = floor($A["vt_o"]/$rango_1);
	?><tr>
    <td>&nbsp;</td>
    <td height="23"><?php echo round($A["gal84"]/$rango_1)  ;?></td>
    <td><?php echo floor($A["gal90"]/$rango_1);?></td>
    <td><?php echo floor($A["gal95"]/$rango_1);?></td>
    <td><?php echo floor($A["gal97"]/$rango_1);?></td>
    <td><?php echo floor($A["gald2"]/$rango_1);?></td>
    <td ><?php echo floor($A["galkd"]/$rango_1);?></td>
    <td><?php echo $total_comb;?></td>
    <td><?php echo $prom_litglp_1;?></td>
    <td><?php echo $prom_vt_a_1;?></td>
    <td><?php echo $prom_vt_s_1;?></td>
    <td><?php echo $prom_vt_l_1;?></td>
    <td><?php echo $prom_vt_m_1;?></td>
    <td><?php echo $prom_vt_w_1;?></td>
    <td><?php echo $prom_vt_o_1;?></td>
    <td>&nbsp;</td>
  	</tr><?php } ?>
  
  
  <?php if($A["nom_est"]!=pg_result($rs,$i+1,"nom_est")){
  	$var_84 = round((($prom_84_0*100)/$prom_84_1)-100);
	$var_90 = round((($prom_90_0*100)/$prom_90_1)-100);
	$var_95 = round((($prom_95_0*100)/$prom_95_1)-100);
	$var_97 = round((($prom_97_0*100)/$prom_97_1)-100);
	$var_d2 = round((($prom_d2_0*100)/$prom_d2_1)-100);
	$var_kd = round((($prom_kd_0*100)/$prom_kd_1)-100);
	$var_glp = round((($prom_glp_0*100)/$prom_glp_1)-100);
	
	$var_vt_a = round((($prom_vt_a_0*100)/$prom_vt_a_1)-100);
	$var_vt_s = round((($prom_vt_s_0*100)/$prom_vt_s_1)-100);
	$var_vt_l = round((($prom_vt_l_0*100)/$prom_vt_l_1)-100);
	$var_vt_m = round((($prom_vt_m_0*100)/$prom_vt_m_1)-100);
	$var_vt_w = round((($prom_vt_w_0*100)/$prom_vt_w_1)-100);
	$var_vt_o = round((($prom_vt_o_0*100)/$prom_vt_0_1)-100);

	
  ?>
  <tr> 
    <td><b><?php echo $A["nom_est"];?></b></td>
    <td height="23"><?php echo $var_84;?></td>
    <td><?php echo $var_90;?></td>
    <td><?php echo $var_95;?></td>
    <td><?php echo $var_d2;?></td>
    <td><?php echo $var_kd;?></td>
    <td><?php echo $var_84;?></td>
    <td><?php echo $var_84;?></td>
    <td>&nbsp;</td>
     
    <td><?php echo $var_vt_a;?></td>
    <td><?php echo $var_vt_s;?></td>
    <td><?php echo $var_vt_l;?></td>
    <td><?php echo $var_vt_m;?></td>
    <td><?php echo $var_vt_w;?></td>
    <td><?php echo $var_vt_o;?></td>
    <td>&nbsp;</td>
  </tr>
  <?php } ?>
  <?php } ?>
  <tr> 
    <td><strong><br>
      TOTAL GENERAL :</strong></td>
    <td height="22"><strong><?php echo $tot_gal84;?><br>
      </strong></td>
    <td><strong><?php echo $tot_gal95;?></strong></td>
    <td>&nbsp;</td>
    <td><strong><?php echo $tot_gald2;?></strong></td>
    <td>&nbsp;</td>
    <td ><strong><?php echo $tot_galkd;?></strong></td>
    <td><strong><?php echo $tot_gal90;?></strong></td>
    <td><strong><?php echo $tot_imp97;?></strong></td>
    <td><strong><?php echo $tot_litglp;?></strong></td>
    <td><strong><?php echo $tot_impglp;?></strong></td>
    <td><strong><?php echo $tot_lubricantes;?></strong></td>
    <td><strong><?php echo $tot_otros;?></strong></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><strong><?php echo $tot_total;?></strong></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>

<?php pg_close();
	
