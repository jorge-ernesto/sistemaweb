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
switch($accion){
	
	case "Reporte":	
		
		if($c_est==""){$c_est="TODAS";}
		//exec("lp -d laser /sistemaweb/tmp/diario_vales.ps");
		$rs = REPORTE_VALES_X_CENTRO_COSTO($c_est, $c_fec_desde,$c_fec_hasta);
		
	break;
	case "Acrobat":
		header("Content-type: application/pdf");
		
		
		//Uncomment next line to make browser show "save as" dialog
		//header("Content-Disposition: attachment; filename=reporte_vales_x_centro_costo.pdf");
		
		readfile("/sistemaweb/tmp/diario_vales-".$_COOKIE["PHPSESSID"].".pdf");
		exit;
	break;
	case "Excel":
		header("Content-type: application/vnd.ms-excel");
    		header("Content-Disposition: attachment; filename=reporte_vales_x_centro_costo.xls");
		if($c_est==""){$c_est="TODAS";}
		$rs = REPORTE_VALES_X_CENTRO_COSTO($c_est, $c_fec_desde,$c_fec_hasta);
	
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
		//$txt = "/tmp/reporte_ventas_diarias.txt";
		
		//exec("smbclient //".$print_netbios."/".$print_name." -c 'print /tmp/".$txt."' -P -N -I ".$print_server." ");
		pg_close();
		
		
	break;
}
?>
<html>
<head>
<link rel="stylesheet" href="../cpagar/js/style.css" type="text/css" media="screen"/>
<link rel="stylesheet" href="http://192.168.1.3/cpagar/js/print.css" type="text/css" media="print"/>

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
      <th><a href="generar_reporte_vales_x_centro_costo.php?accion=Excel&c_est=<?php echo $c_est;?>&c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>"><font size="2">EXCEL</font></a></th>
      <th><a href="generar_reporte_vales_x_centro_costo.php?accion=Acrobat&c_est=<?php echo $c_est;?>&c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>"><font size="2">IMPRIMIR</font></a></th>
    <tr class="letra_titulo"> 
      <td align="center" colspan="2">&nbsp;</td>
      <td align="center">REPORTE DIARIO DE CONSISTENCIA DE VALES x CENTRO DE COSTO</td>
      <?php $BUF = $BUF.str_pad("CINTILLO DE LIQUIDACION DE COMPRAS        $fecha_actual\n\n",80," ",STR_PAD_LEFT);?>
      <td width="166" align="center"><?php echo $fecha_actual;?></td>
    <tr class="letra_titulo"> 
      <td width="201">&nbsp; </td>
      <td width="19">&nbsp;</td>
      <td><div align="center">Desde<?php echo $c_fec_desde;?>: Hasta:<?php echo $c_fec_hasta;?></div></td>
      <td>&nbsp;</td>
  </table>
<br><?php if($accion=="Excel"){$bg="#FFFFFF";}else{$bg="#BBBBBB";}
	?>
  <table  border="0" cellspacing="1" cellpadding="2" bgcolor="<?php echo $bg;?>" width="80%">
    <tr class="letra_cabecera"> 
      <td width="11%" rowspan="2"><font size="1">C.C DESCRIPCION<br>
        NUMERACION DE VALES</font></td>
      <td width="9%" rowspan="2"><font size="1">NUMERO<br>
        DESPACHO </font></td>
      <td width="9%" rowspan="2"><font size="1">FECHA<br>
        CONSUMO </font></td>
      <td height="39" colspan="2"><font size="1">84</font></td>
      <td colspan="2"><font size="1">95</font></td>
      <td colspan="2"><font size="1">DIESEL 2</font></td>
      <td colspan="2"><font size="1">KEROSENE</font></td>
      <td colspan="2"><font size="1">90</font></td>
      <td colspan="2"><font size="1">97</font></td>
      <td colspan="2"><font size="1">GLP</font></td>
      <td width="5%"><font size="1">LUBRI.</font></td>
      <td width="6%"><font size="1">OTROS</font></td>
      <td width="9%"><font size="1">TOTALES</font></td>
    </tr>
    <tr class="letra_cabecera"> 
      <td width="3%" height="20"><font size="1">IMP</font></td>
      <td width="3%"><font size="1">GAL</font></td>
      <td width="3%"><font size="1">IMP</font></td>
      <td width="3%"><font size="1">GAL</font></td>
      <td width="3%"><font size="1">IMP</font></td>
      <td width="6%"><font size="1">GAL</font></td>
      <td width="9%"><font size="1">IMP</font></td>
      <td width="3%"><font size="1">GAL</font></td>
      <td width="3%"><font size="1">IMP</font></td>
      <td width="3%"><font size="1">GAL</font></td>
      <td width="3%"><font size="1">IMP</font></td>
      <td width="3%"><font size="1">GAL</font></td>
      <td width="3%"><font size="1">IMP</font></td>
      <td width="3%"><font size="1">GAL</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
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
    <?php if($cli!=$A["ch_cliente"]){
			if($est!=$A["ch_sucursal"]){
				$cc = "C.COSTO ".$A["ch_sucursal"]." ".$A["des_sucursal"];
			}else{$cc = "";}
	$est = $A["ch_sucursal"];
	$cli = $A["ch_cliente"];

  ?>
    <tr class="letra_detalle"> 
      <td><font size="1"><?php echo $cc;?><br>
        <?php echo $A["ch_cliente"];?> - <?php echo $A["cli_razsocial"];?></font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">&nbsp;</font></td>
      <td colspan="5"><font size="1">&nbsp;</font></td>
    </tr>
    <?php } ?>
    <tr class="letra_detalle"> 
      <td><font size="1"><?php echo $A["ch_numeval"];?></font></td>
      <td><font size="1"><?php echo $A["ch_documento"];?></font></td>
      <td><font size="1"><?php echo $A["dt_fecha"];?></font></td>
      <td><font size="1"><?php echo $A["imp84"];?></font></td>
      <td><font size="1"><?php echo $A["gal84"];?></font></td>
      <td><font size="1"><?php echo $A["imp95"];?></font></td>
      <td><font size="1"><?php echo $A["gal95"];?></font></td>
      <td><font size="1"><?php echo $A["impd2"];?></font></td>
      <td><font size="1"><?php echo $A["gald2"];?></font></td>
      <td><font size="1"><?php echo $A["impkd"];?></font></td>
      <td><font size="1"><?php echo $A["galkd"];?></font></td>
      <td><font size="1"><?php echo $A["imp90"];?></font></td>
      <td><font size="1"><?php echo $A["gal90"];?></font></td>
      <td><font size="1"><?php echo $A["imp97"];?></font></td>
      <td><font size="1"><?php echo $A["gal97"];?></font></td>
      <td><font size="1"><?php echo $A["impglp"];?></font></td>
      <td><font size="1"><?php echo $A["litglp"];?></font></td>
      <td><font size="1"><?php echo $A["imp_lubricantes"];?></font></td>
      <td><font size="1"><?php echo $A["imp_otros"];?></font></td>
      <td><font size="1"><?php echo $A["imp_total"];?></font></td>
    </tr>
    <?php if(pg_result($rs,$i+1,"ch_cliente")!=$A["ch_cliente"]){
  ?>
    <tr class="letra_detalle"> 
      <td><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">TOTAL CLIENTE:</font></td>
      <td><font size="1"><?php echo $tot_imp_g84;?></font></td>
      <td><font size="1"><?php echo $tot_gal_g84;?></font></td>
      <td><font size="1"><?php echo $tot_imp_g95;?></font></td>
      <td><font size="1"><?php echo $tot_gal_g95;?></font></td>
      <td><font size="1"><?php echo $tot_imp_gd2;?></font></td>
      <td><font size="1"><?php echo $tot_gal_gd2;?></font></td>
      <td><font size="1"><?php echo $tot_imp_kd;?></font></td>
      <td><font size="1"><?php echo $tot_gal_kd;?></font></td>
      <td><font size="1"><?php echo $tot_imp_g90;?></font></td>
      <td><font size="1"><?php echo $tot_gal_g90;?></font></td>
      <td><font size="1"><?php echo $tot_imp_g97;?></font></td>
      <td><font size="1"><?php echo $tot_gal_g97;?></font></td>
      <td><font size="1"><?php echo $tot_imp_glp;?></font></td>
      <td><font size="1"><?php echo $tot_gal_glp;?></font></td>
      <td><font size="1"><?php echo $tot_imp_lubricantes;?></font></td>
      <td><font size="1"><?php echo $tot_imp_otros;?></font></td>
      <td><font size="1"><?php echo $tot_imp_total;?></font></td>
    </tr>
    <tr class="letra_detalle"> 
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td><font size="1">&nbsp;</font></td>
      <td colspan="3"><font size="1">&nbsp;</font></td>
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
    <?php if($A["ch_sucursal"]!=pg_result($rs,$i+1,"ch_sucursal")){ //IF PARA EL CAMBIO DE ESTACION
  ?>
    <tr> 
      <td><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">TOTAL C/C:</font></td>
      <td><font size="1"><?php echo $cc_tot_imp_g84;?></font></td>
      <td><font size="1"><?php echo $cc_tot_gal_g84;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_g95;?></font></td>
      <td><font size="1"><?php echo $cc_tot_gal_g95;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_gd2;?></font></td>
      <td><font size="1"><?php echo $cc_tot_gal_gd2;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_kd;?></font></td>
      <td><font size="1"><?php echo $cc_tot_gal_kd;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_g90;?></font></td>
      <td><font size="1"><?php echo $cc_tot_gal_g90;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_g97;?></font></td>
      <td><font size="1"><?php echo $cc_tot_gal_g97;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_glp;?></font></td>
      <td><font size="1"><?php echo $cc_tot_gal_glp;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_lubricantes;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_otros;?></font></td>
      <td><font size="1"><?php echo $cc_tot_imp_total;?></font></td>
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
    <tr> 
      <td><font size="1">&nbsp;</font></td>
      <td colspan="2"><font size="1">TOTAL GENERAL:</font></td>
      <td><font size="1"><?php echo $fin_tot_imp_g84;?></font></td>
      <td><font size="1"><?php echo $fin_tot_gal_g84;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_g95;?></font></td>
      <td><font size="1"><?php echo $fin_tot_gal_g95;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_gd2;?></font></td>
      <td><font size="1"><?php echo $fin_tot_gal_gd2;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_kd;?></font></td>
      <td><font size="1"><?php echo $fin_tot_gal_kd;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_g90;?></font></td>
      <td><font size="1"><?php echo $fin_tot_gal_g90;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_g97;?></font></td>
      <td><font size="1"><?php echo $fin_tot_gal_g97;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_glp;?></font></td>
      <td><font size="1"><?php echo $fin_tot_gal_glp;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_lubricantes;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_otros;?></font></td>
      <td><font size="1"><?php echo $fin_tot_imp_total;?></font></td>
    </tr>
  </table>
</form>
<?php pg_close(); ?>
</html>
