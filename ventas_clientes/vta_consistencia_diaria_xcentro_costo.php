<?php
ob_start();
include "../menu_princ.php";
include("/sistemaweb/utils/funcion-texto.php");
include("include/utils.php");

$accion      = $_POST['accion'];
$c_fec_desde = $_POST['c_fec_desde'];
$c_fec_hasta = $_POST['c_fec_hasta'];

$hoy = date("d/m/Y");
if($c_fec_desde==""){$c_fec_desde=$hoy;}else{$c_fec_desde=$_POST['c_fec_desde'];}
if($c_fec_hasta==""){$c_fec_hasta=$hoy;}else{$c_fec_hasta=$_POST['c_fec_hasta'];}

$col = 10;
$lin1 = str_repeat("-",240);
$lin2 = str_repeat("=",240);
$maximo_lineas = 80;
$salto = chr(12);

switch($accion){
	
	case "Reporte":	
		if($c_est==""){
			$c_est="TODAS";
		}
		$rs = REPORTE_VALES_X_CENTRO_COSTO($c_est, $c_fec_desde,$c_fec_hasta);
		break;

	case "Excel":
		if($c_est==""){
			$c_est="TODAS";
		}
		$rs = REPORTE_VALES_X_CENTRO_COSTO($c_est, $c_fec_desde,$c_fec_hasta);

		global $sqlca;
		$sql =	"SELECT ch_almacen,ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1';";
		if ($sqlca->query($sql) < 0)
			return false;
		$result = array();
		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$i][0] = $a[0];
			$result[$i][1] = $a[1];
		}

		ob_end_clean();		

		$buff = "CLIENTE, DESPACHO, FECHA, IMP.84, GAL.84, IMP.95, GAL.95, IMP.Diesel 2, GAL.Diesel 2, IMP.Kerosene, GAL.Kerosene, IMP.90, GAL.90, IMP.97, GAL.97, IMP.GLP, LIT.GLP, LUBRICANTE, OTROS, TOTALES\n";
		for($i=0;$i<pg_numrows($rs);$i++){
			$A = pg_fetch_array($rs,$i);
			$cliente = trim($A["ch_cliente"]);
  			$buff .= "{$cliente}"." - "."{$A["cli_razsocial"]},{$A["ch_documento"]},{$A["dt_fecha"]},{$A["imp84"]},{$A["gal84"]},{$A["imp95"]},{$A["gal95"]},{$A["impd2"]},{$A["gald2"]},{$A["impkd"]},{$A["galkd"]},{$A["imp90"]},{$A["gal90"]},{$A["imp97"]},{$A["gal97"]},{$A["impglp"]},{$A["litglp"]},{$A["imp_lubricantes"]},{$A["imp_otros"]},{$A["imp_total"]}\n";
		}

		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=\"reporte.csv\"");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		die($buff);
		break;
}
ob_end_flush();
$reporte_txt = "";

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
  <table width="900" border="0" cellpadding="1" cellspacing="1">
    <tr> 

    <td colspan="4"><div align="center">REPORTE DIARIO DE CONSISTENCIA DE VALES 
          x CENTRO DE COSTO</div></td>
      <!--<td width="17%"><!--<a href="#" onClick="javascript:accion.value='Imprimir',form1.submit();">IMPRIMIR</a>
	  	<a href="#" onClick="javascript:window.open('generar_reporte_vales_x_centro_costo.php?c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&accion=<?php echo $accion;?>&c_opt_reporte=<?php echo $c_opt_reporte;?>&c_est=<?php echo $c_est;?>','winrep1','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">IMPRESION</a>
	  </td>-->
  </tr>
  <tr> 
      <td><div align="center">Estacion:<input type="text" name="c_est" size="15" value="<?php echo $c_est;?>"></div></td>
      <td width="20%">Desde:<input type="text" name="c_fec_desde" size="10" value="<?php echo $c_fec_desde;?>"><a href="javascript:show_calendar('form1.c_fec_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>
</td>
    <td width="20%">Hasta:<input type="text" name="c_fec_hasta" size="10" value="<?php echo $c_fec_hasta;?>"> <a href="javascript:show_calendar('form1.c_fec_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
      <td width="29%"> <input type="radio" name="c_opt_reporte" value="RESUMIDO" checked= "checked">
        Resumido <br>
        <input type="radio" name="c_opt_reporte" value="DETALLADO">
        Detallado </td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2"><div align="center"><button type="submit" name="btn_reporte" value="Reporte" onClick="javascript:mandarDatos(form1,'Reporte');"><img src="/sistemaweb/images/reporte.png" alt="left" />  Reporte</button>&nbsp;
    <!--<button type="submit" name="btn_reporte" value="Excel" onClick="javascript:mandarDatos(form1,'Reporte');"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button><input type="hidden" name="accion"></div></td>-->
    <button type="submit" name="boton" value="Excel" onClick="javascript:mandarDatos(form1,'Excel');"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button><input type="hidden" name="accion"></div></td>
    <td>&nbsp;</td>
  </tr>
</table>
</form>

<table width="108%" border="1">
  	<tr> 
    		<td width="11%" rowspan="2">C.C DESCRIPCION<br>NUMERACION DE VALES</td>
    		<td width="9%" rowspan="2">NUMERO<br>DESPACHO </td>
    		<td width="9%" rowspan="2">FECHA<br>CONSUMO </td>
    		<td height="39" colspan="2">84</td>
    		<td colspan="2">95</td>
    		<td colspan="2">DIESEL 2</td>
    		<td colspan="2">KEROSENE</td>
    		<td colspan="2">90</td>
    		<td colspan="2">97</td>
    		<td colspan="2">GLP</td>
    		<td width="5%">LUBRI.</td>
    		<td width="6%">OTROS</td>
    		<td width="9%">TOTALES</td>
    	</tr>
  	<tr> 
    		<td width="3%" height="20">IMP</td>
    		<td width="3%">GAL</td>
    		<td width="3%">IMP</td>
    		<td width="3%">GAL</td>
    		<td width="3%">IMP</td>
    		<td width="6%">GAL</td>
    		<td width="9%">IMP</td>
    		<td width="3%">GAL</td>
    		<td width="3%">IMP</td>
    		<td width="3%">GAL</td>
    		<td width="3%">IMP</td>
    		<td width="3%">GAL</td>
    		<td width="3%">IMP</td>
    		<td width="3%">GAL</td>
    		<td>&nbsp;</td>
    		<td>&nbsp;</td>
    		<td>&nbsp;</td>
  	</tr>
	<?php $est="";
  	 $cli=""; 
  	$tot_imp_g84 = 0;
	$tot_gal_g84 = 0;
	$tot_imp_g95 = 0;
	$tot_gal_g95 = 0;
	$tot_imp_gd2 = 0;
	$tot_gal_gd2 = 0;
	$tot_imp_kd = 0;
	$tot_gal_kd = 0;
	$tot_imp_g90 = 0;
	$tot_gal_g90 = 0;
	$tot_imp_g97 = 0;
	$tot_gal_g97 = 0;
	$tot_imp_glp = 0;
	$tot_lit_glp = 0;
	
	$tot_imp = 0;
	$tot_gal = 0;
	
  	for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
	  
	  	$tot_imp_g84 += $A["imp84"];
		$tot_gal_g84 += $A["gal84"];
		$tot_imp_g95 += $A["imp95"];
		$tot_gal_g95 += $A["gal95"];
		$tot_imp_gd2 += $A["impd2"];
		$tot_gal_gd2 += $A["gald2"];
		$tot_imp_kd += $A["impkd"];
		$tot_gal_kd += $A["galkd"];
		$tot_imp_g90 += $A["imp90"];
		$tot_gal_g90 += $A["gal90"];
		$tot_imp_g97 += $A["imp97"];
		$tot_gal_g97 += $A["gal97"];
		$tot_imp_glp += $A["impglp"];
		$tot_lit_glp += $A["litglp"];
		$tot_imp_lubricantes += $A["imp_lubricantes"];
		$tot_imp_otros += $A["imp_otros"];
	
		$tot_imp_total = $tot_imp_total+$A["imp_total"];
		$tot_gal += 0;	
  		
  	?>
  	<?php	if($cli.$est!=$A["ch_cliente"].$A["ch_sucursal"]){
			if($est!=$A["ch_sucursal"]){
				$cc = "C.COSTO ".$A["ch_sucursal"]." ".$A["des_sucursal"];
			}else{
				$cc = "";
			}
		$est = $A["ch_sucursal"];
		$cli = $A["ch_cliente"];

  	?>
  	<tr> 
    <td><?php echo $cc;?><br> <?php echo $A["ch_cliente"];?> - <?php echo $A["cli_razsocial"];
	$reporte_txt = $reporte_txt.$cc."\n".trim($A["ch_cliente"])." - ".$A["cli_razsocial"]."\n";
	?></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td colspan="2">&nbsp;</td>
    <td colspan="2">&nbsp;</td>
    <td colspan="2">&nbsp;</td>
    <td colspan="2">&nbsp;</td>
    <td colspan="2">&nbsp;</td>
    <td colspan="2">&nbsp;</td>
    <td colspan="5">&nbsp;</td>
  </tr>
  <?php } ?>
  <tr> 
  	<?php
  	/*$NumEval = str_replace("<br>","",trim($A["ch_numeval"]));
  	//echo "<!-- NUMEVAL 0 : ".$NumEval."-->\n";
  	$NumEval = str_replace(",","<br>", $NumEval);*/
  	//echo "<!-- NUMEVAL : ".$NumEval."-->\n";
  	$AR_1 = columnaVales($A["ch_numeval"],37,STR_PAD_RIGHT); 
  	//print_r($AR_1);
	   /* $cadena = str_replace("<br>","",$A["ch_numeval"]);
	    $cadena = str_replace(",","\n",trim($cadena));
	    
  	  $AR_1["dato"] = $cadena;*/
	  $tmp_rep_ch_numeval = $AR_1["dato"];
	  
	  //$long_strvales	  = $AR_1["long_final"];
	  if(trim($tmp_rep_ch_numeval)==""){$tmp_rep_ch_numeval=$A["ch_numeval"];} 
	  
	  //$tmp_rep_ch_numeval = str_replace(",","\n", $A["ch_numeval"]);
	  
	  //echo "<!-- TMP DOCUMENTO : ".$tmp_rep_ch_numeval."-->\n";
	  $reporte_txt = $reporte_txt.$tmp_rep_ch_numeval;
	  $reporte_txt = $reporte_txt.$A["ch_documento"];
	  $reporte_txt = $reporte_txt.str_pad($A["dt_fecha"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["imp84"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["gal84"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["imp95"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["gal95"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["impd2"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["gald2"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["impkd"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["galkd"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["imp90"],8," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["gal90"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["imp97"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["gal97"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["impglp"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["litglp"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["imp_lubricantes"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["imp_otros"],10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["imp_total"],15," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt."\n";
	  ?>
	  
    <td><?php echo $A["ch_numeval"];?></td>
    <td><?php echo $A["ch_documento"];?></td>
    <td><?php echo $A["dt_fecha"];?></td>
    <td><?php echo $A["imp84"];?></td>
    <td><?php echo $A["gal84"];?></td>
    <td><?php echo $A["imp95"];?></td>
    <td><?php echo $A["gal95"];?></td>
    <td><?php echo $A["impd2"];?></td>
    <td><?php echo $A["gald2"];?></td>
    <td><?php echo $A["impkd"];?></td>
    <td><?php echo $A["galkd"];?></td>
    <td><?php echo $A["imp90"];?></td>
    <td><?php echo $A["gal90"];?></td>
    <td><?php echo $A["imp97"];?></td>
    <td><?php echo $A["gal97"];?></td>
    <td><?php echo $A["impglp"];?></td>
    <td><?php echo $A["litglp"];?></td>
    <td><?php echo $A["imp_lubricantes"];?></td>
    <td><?php echo $A["imp_otros"];?></td>
    <td><?php echo $A["imp_total"];?></td>
  </tr>
  <?php if( $i == pg_numrows($rs)-1 || (pg_result($rs,$i+1,"ch_cliente").pg_result($rs,$i+1,"ch_sucursal") != $A["ch_cliente"].$A["ch_sucursal"]) ){
  	//number_format(20, 2, '.', '');
  	  $reporte_txt = $reporte_txt.$lin1."\n";
	  $reporte_txt = $reporte_txt."              TOTAL CLIENTE";
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_g84, 2, '.', ''),40," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal_g84, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_g95, 2, '.', ''),9," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal_g95, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_gd2, 2, '.', ''),11," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal_gd2, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_kd, 2, '.', ''),8," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal_kd, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_g90, 2, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal_g90, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_g97, 2, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal_g97, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_glp, 2, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_gal_glp, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_lubricantes, 2, '.', ''),9," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_otros, 2, '.', ''),13," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($tot_imp_total, 2, '.', ''),13," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt."\n";
	  $reporte_txt = $reporte_txt.$lin1."\n\n";
  ?>
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2">TOTAL CLIENTE:</td>
    <td><?php echo $tot_imp_g84;?></td>
    <td><?php echo $tot_gal_g84;?></td>
    <td><?php echo $tot_imp_g95;?></td>
    <td><?php echo $tot_gal_g95;?></td>
    <td><?php echo $tot_imp_gd2;?></td>
    <td><?php echo $tot_gal_gd2;?></td>
    <td><?php echo $tot_imp_kd;?></td>
    <td><?php echo $tot_gal_kd;?></td>
    <td><?php echo $tot_imp_g90;?></td>
    <td><?php echo $tot_gal_g90;?></td>
    <td><?php echo $tot_imp_g97;?></td>
    <td><?php echo $tot_gal_g97;?></td>
    <td><?php echo $tot_imp_glp;?></td>
    <td><?php echo $tot_gal_glp;?></td>
    <td><?php echo $tot_imp_lubricantes;?></td>
    <td><?php echo $tot_imp_otros;?></td>
    <td><?php echo $tot_imp_total;?></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td colspan="3">&nbsp;</td>
  </tr>
  <?php $cc_tot_imp_g84 += $tot_imp_g84;
	$cc_tot_gal_g84 += $tot_gal_g84 ;
	$cc_tot_imp_g95 += $tot_imp_g95;
	$cc_tot_gal_g95 += $tot_gal_g95;
	$cc_tot_imp_gd2 += $tot_imp_gd2;
	$cc_tot_gal_gd2 += $tot_gal_gd2;
	$cc_tot_imp_kd += $tot_imp_kd;
	$cc_tot_gal_kd += $tot_gal_kd;
	$cc_tot_imp_g90 += $tot_imp_g90;
	$cc_tot_gal_g90 += $tot_gal_g90;
	$cc_tot_imp_g97 += $tot_imp_g97;
	$cc_tot_gal_g97 += $tot_gal_g97;
	$cc_tot_imp_glp += $tot_imp_glp;
	$cc_tot_lit_glp += $tot_lit_glp;
	$cc_tot_imp_lubricantes += $tot_imp_lubricantes;
	$cc_tot_imp_otros += $tot_imp_otros;
  	$cc_tot_imp_total += $tot_imp_total;
  
  	$tot_imp_g84 = 0;
	$tot_gal_g84 = 0;
	$tot_imp_g95 = 0;
	$tot_gal_g95 = 0;
	$tot_imp_gd2 = 0;
	$tot_gal_gd2 = 0;
	$tot_imp_kd = 0;
	$tot_gal_kd = 0;
	$tot_imp_g90 = 0;
	$tot_gal_g90 = 0;
	$tot_imp_g97 = 0;
	$tot_gal_g97 = 0;
	$tot_imp_glp = 0;
	$tot_lit_glp = 0;
	$tot_imp_lubricantes = 0;
	$tot_imp_otros = 0;
	$tot_imp_total = 0;
	
	$tot_imp = 0;
	$tot_gal = 0;	
  } //FIN DEL IF DEL CAMBIO DE CLIENTE
  ?>
  <?php if( $i == pg_numrows($rs)-1 || ($A["ch_sucursal"]!=pg_result($rs,$i+1,"ch_sucursal"))){ //IF PARA EL CAMBIO DE ESTACION
  ?>
  <tr> 
  	<?php $reporte_txt = $reporte_txt."              TOTAL C/C ".$A["des_sucursal"].":";
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_g84, 2, '.', ''),32," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_gal_g84, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_g95, 2, '.', ''),9," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_gal_g95, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_gd2, 2, '.', ''),11," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_gal_gd2, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_kd, 2, '.', ''),8," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_gal_kd, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_g90, 2, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_gal_g90, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_g97, 2, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_gal_g97, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_glp, 2, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_gal_glp, 3, '.', ''),10," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_lubricantes, 2, '.', ''),9," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_otros, 2, '.', ''),13," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(number_format($cc_tot_imp_total, 2, '.', ''),13," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt."\n";
	  $reporte_txt = $reporte_txt.$lin2."\n";
	?>
    <td>&nbsp;</td>
    <td colspan="2">TOTAL C/C: <?php echo $A["des_sucursal"];?></td>
    <td><?php echo $cc_tot_imp_g84;?></td>
    <td><?php echo $cc_tot_gal_g84;?></td>
    <td><?php echo $cc_tot_imp_g95;?></td>
    <td><?php echo $cc_tot_gal_g95;?></td>
    <td><?php echo $cc_tot_imp_gd2;?></td>
    <td><?php echo $cc_tot_gal_gd2;?></td>
    <td><?php echo $cc_tot_imp_kd;?></td>
    <td><?php echo $cc_tot_gal_kd;?></td>
    <td><?php echo $cc_tot_imp_g90;?></td>
    <td><?php echo $cc_tot_gal_g90;?></td>
    <td><?php echo $cc_tot_imp_g97;?></td>
    <td><?php echo $cc_tot_gal_g97;?></td>
    <td><?php echo $cc_tot_imp_glp;?></td>
    <td><?php echo $cc_tot_gal_glp;?></td>
    <td><?php echo $cc_tot_imp_lubricantes;?></td>
    <td><?php echo $cc_tot_imp_otros;?></td>
    <td><?php echo $cc_tot_imp_total;?></td>
  </tr>
  <?php $fin_tot_imp_g84 += $cc_tot_imp_g84;
	$fin_tot_gal_g84 += $cc_tot_gal_g84 ;
	$fin_tot_imp_g95 += $cc_tot_imp_g95;
	$fin_tot_gal_g95 += $cc_tot_gal_g95;
	$fin_tot_imp_gd2 += $cc_tot_imp_gd2;
	$fin_tot_gal_gd2 += $cc_tot_gal_gd2;
	$fin_tot_imp_kd += $cc_tot_imp_kd;
	$fin_tot_gal_kd += $cc_tot_gal_kd;
	$fin_tot_imp_g90 += $cc_tot_imp_g90;
	$fin_tot_gal_g90 += $cc_tot_gal_g90;
	$fin_tot_imp_g97 += $cc_tot_imp_g97;
	$fin_tot_gal_g97 += $cc_tot_gal_g97;
	$fin_tot_imp_glp += $cc_tot_imp_glp;
	$fin_tot_lit_glp += $cc_tot_lit_glp;
	$fin_tot_imp_lubricantes += $cc_tot_imp_lubricantes;
	$fin_tot_imp_otros += $cc_tot_imp_otros;
  	$fin_tot_imp_total += $cc_tot_imp_total;
  
  
  $cc_tot_imp_g84 = 0;
	$cc_tot_gal_g84 = 0;
	$cc_tot_imp_g95 = 0;
	$cc_tot_gal_g95 = 0;
	$cc_tot_imp_gd2 = 0;
	$cc_tot_gal_gd2 = 0;
	$cc_tot_imp_kd = 0;
	$cc_tot_gal_kd = 0;
	$cc_tot_imp_g90 = 0;
	$cc_tot_gal_g90 = 0;
	$cc_tot_imp_g97 = 0;
	$cc_tot_gal_g97 = 0;
	$cc_tot_imp_glp = 0;
	$cc_tot_lit_glp = 0;
	$cc_tot_imp_lubricantes = 0;
	$cc_tot_imp_otros = 0;
	$cc_tot_imp_total = 0;
  } //FIN DEL IF DE CAMBIO DE ALMACEN 
  ?>
  <?php } //fin del for
  ?>
  <?php $reporte_txt = $reporte_txt."TOTAL GENERAL:";
    $reporte_txt = $reporte_txt.str_pad($fin_tot_imp_g84,37," ",STR_PAD_LEFT);
    $reporte_txt = $reporte_txt.str_pad($fin_tot_gal_g84,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_g95,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_gal_g95,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_gd2,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_gal_gd2,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_kd,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_gal_kd,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_g90,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_gal_g90,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_g97,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_gal_g97,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_glp,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_gal_glp,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_lubricantes,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_otros,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt.str_pad($fin_tot_imp_total,10," ",STR_PAD_LEFT);
	$reporte_txt = $reporte_txt."\n";
  ?>
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2"></td>
    <td><?php echo $fin_tot_imp_g84;?></td>
    <td><?php echo $fin_tot_gal_g84;?></td>
    <td><?php echo $fin_tot_imp_g95;?></td>
    <td><?php echo $fin_tot_gal_g95;?></td>
    <td><?php echo $fin_tot_imp_gd2;?></td>
    <td><?php echo $fin_tot_gal_gd2;?></td>
    <td><?php echo $fin_tot_imp_kd;?></td>
    <td><?php echo $fin_tot_gal_kd;?></td>
    <td><?php echo $fin_tot_imp_g90;?></td>
    <td><?php echo $fin_tot_gal_g90;?></td>
    <td><?php echo $fin_tot_imp_g97;?></td>
    <td><?php echo $fin_tot_gal_g97;?></td>
    <td><?php echo $fin_tot_imp_glp;?></td>
    <td><?php echo $fin_tot_gal_glp;?></td>
    <td><?php echo $fin_tot_imp_lubricantes;?></td>
    <td><?php echo $fin_tot_imp_otros;?></td>
    <td><?php echo $fin_tot_imp_total;?></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>

<?php pg_close();
//VARIABLES PARA IMPRESION DE TEXTO
    $cab[0] = "Sucursal {sucursal} ".str_pad("REPORTE DIARIO DE CONSISTENCIA DE VALES x CENTRO DE COSTO",195," ", STR_PAD_BOTH)." Pag. {pagina}";
    //$cab = $cab.str_pad("Del $c_fec_desde AL $c_fec_hasta",220," ", STR_PAD_BOTH)."\n";
    $cab[1] = str_pad("Del $c_fec_desde AL $c_fec_hasta",130," ",STR_PAD_LEFT);
    $cab[2] = "Usuario:{usuario} - Fecha: {fecha}";
    $cab[3] = $lin2;
    $cab[4] = "C.C DESCRIPCION                       NUMERO      FECHA             84                95                DIESEL 2            KEROSENE               90                  97                 GLP            LUBRI.      OTROS       TOTALES";
    $cab[5] = "NUMERACION DE VALES                  DESPACHO    CONSUMO       IMP      GAL      IMP       GAL       IMP        GAL      IMP        GAL      IMP         GAL      IMP       GAL      IMP        GAL      ";
    $cab[6] = $lin2;
	$man_arch = fopen("/tmp/archivo-diario-vale-".$_COOKIE["PHPSESSID"].".txt","w");
	fwrite($man_arch,$reporte_txt);
    fclose($man_arch);

//	$reporte_listo = FormatearReporte("/sistemaweb/tmp/archivo-diario-vale".$_COOKIE["PHPSESSID"].".txt",$maximo_lineas-7,"/sistemaweb/tmp/diario_vales.txt",$salto);

	crearPostscript("/tmp/archivo-diario-vale-".$_COOKIE["PHPSESSID"].".txt","/sistemaweb/tmp/diario_vales-".$_COOKIE['PHPSESSID'].".pdf",$cab);

	//$ar = file("/tmp/reporte_ventas_diarias.txt");
	//echo count($ar);
	//echo $BUF;	
