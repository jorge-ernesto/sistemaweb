<?php
include("../functions.php");

if($cod_almacen=="") {
	$and_cont = "and cont.ch_sucursal='$almacen'";
} else {
	$cod_almacen = trim($cod_almacen);
	$and_cont = "and cont.ch_sucursal='$cod_almacen'";
}

$q3 = "	SELECT 
		cont.ch_numeroparte as parte, 
		cont.ch_codigocombustible, 
		cont.ch_tanque as tanque, 
		cont.ch_surtidor as manguera,
                cont.nu_contometroinicialgalon, 
                cont.nu_contometrofinalgalon, 
                cont.nu_ventagalon,
                cont.nu_contometroinicialvalor, 
                cont.nu_contometrofinalvalor, 
                cont.nu_ventavalor,
                cont.nu_afericionveces_x_5, 
                cont.nu_consumogalon, 
                comb.ch_nombrecombustible, 
                cont.dt_fechaparte,
                cont.ch_responsable, 
                surt.ch_numerolado as lado
	FROM 
		comb_ta_contometros cont, 
		comb_ta_combustibles comb, 
		comb_ta_surtidores surt 
	WHERE 
		cont.ch_codigocombustible = comb.ch_codigocombustible
                $and_cont 
		AND cont.ch_surtidor = surt.ch_surtidor 
		AND cont.dt_fechaparte >= to_date('$fechad','DD-MM-YYYY') 
		AND cont.dt_fechaparte <= to_date('$fechaa','DD-MM-YYYY') 
	ORDER BY 
		arte,lado,manguera,tanque" ;

$rs3 = pg_exec($q3);

$q4 = "	SELECT 
		comb.ch_codigocombustible as producto, 
		comb.ch_nombrecombustible as descripcion,
		sum(cont.nu_ventagalon) as venta_galon, 
		sum(cont.nu_ventavalor) as venta_valor,
		sum(cont.nu_afericionveces_x_5) as afer_gal,
		round( sum(cont.nu_afericionveces_x_5)*5*round( sum(cont.nu_ventagalon)/sum(cont.nu_ventavalor),3 ), 3) as afer_val,
		sum(cont.nu_consumogalon) as consumo_galon, round( sum(cont.nu_consumogalon)*round(sum(cont.nu_ventagalon)/sum(cont.nu_ventavalor),3),3 ) as consumo_valor,
		round ( sum(cont.nu_ventagalon) - sum(cont.nu_afericionveces_x_5) - sum(cont.nu_consumogalon) ,3) as resumen_galones,
		round ( sum(cont.nu_ventavalor) - sum(cont.nu_afericionveces_x_5)*5*round( sum(cont.nu_ventagalon)/sum(cont.nu_ventavalor),3 ) - sum(cont.nu_consumogalon)*round(sum(cont.nu_ventagalon)/sum(cont.nu_ventavalor),3)  ,3) as neto_soles,
	FROM 
		comb_ta_combustibles comb, 
		comb_ta_contometros cont
	WHERE 	
		cont.ch_codigocombustible = comb.ch_codigocombustible 
		$and_cont 
		AND cont.dt_fechaparte >= to_date('$fechad','DD-MM-YYYY') 
		AND cont.dt_fechaparte <= to_date('$fechaa','DD-MM-YYYY') 
	GROUP BY 
		producto, descripcion";
		
$rs4 = pg_exec($q4);

if($action=="exportar"){

	for($i=0;$i<pg_numrows($rs4);$i++){
          	$Q4 = pg_fetch_row($rs4,$i);
          	$total_res_ven_gal = $total_res_ven_gal + $Q4[2];
          	$total_res_ven_val = $total_res_ven_val + $Q4[3];
          	$total_res_afe_gal = $total_res_afe_gal + $Q4[4];
          	$total_res_afe_val = $total_res_afe_val + $Q4[5];
          	$total_res_con_gal = $total_res_con_gal + $Q4[6];
          	$total_res_con_val = $total_res_con_val + $Q4[7];
          	$total_resumen_gal = $total_resumen_gal + $Q4[8];
          	$total_neto_val    = $total_neto_val    + $Q4[9];
        }
        $titulo   = "PARTE DE MOVIMIENTO DE COMBUSTIBLES DEL: $fechad AL: $fechaa \n SUCURSAL: $cod_almacen";
        $cabecera = "Num. Parte , Cod. Art , Tanque , Manguera , Contometro Inicial (galones) , Contometro Final (galones), Galones Vendidos , Cont�metro Inicial (Soles) , Contometro Final (soles) , Soles Vendidos , Afericiones ,Consumo ,Descripci�n , Fecha ";
        $head[0]  = $cabecera;
        $head[1]  = "Producto,Descripcion,Galones,Soles,Galones,Soles,Galones,Soles,Galones,Soles";

        $T[0]  = $q3;
        $T[1]  = $q4;
        $P[1]  = ",,RESUMEN DE VENTA ,,RESUMEN DE AFERICION , ,CONSUMO INTERNO, , RESUMEN, NETO ";
        $BA[0] = ",TOTAL,$total_res_ven_gal ,$total_res_ven_val ,$total_res_afe_gal,$total_res_afe_val,$total_res_con_gal,$total_res_con_val,$total_resumen_gal ,$total_neto_val ";
        $TI[0] = $titulo;
        $url   = reporteExcel($user,$head,$T,$BA,$P,$TI);
	?>
        <script language="JavaScript1.3" type="text/javascript">
        window.open('<?php echo $url;?>','miwin','width=10,height=35,scrollbars=yes');
        </script>
        <?php
}
pg_close();

?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  	if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    		document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  	else if 
  		(innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
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
<form action='cmb_medvarilla-edit.php' method='post' name="form2">
<table width="767" border="1" cellpadding="0" cellspacing="0">
    	<tr>
      		<td width="457"><a href="cmb_contometro-reporte.php?action=exportar&fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&titulo=<?php echo $titulo;?>&cod_almacen=<?php echo $cod_almacen;?>" >Exportar
        	a Excel</a></td>
      		<td><a href="#" onClick="javascript:window.print();">Imprimir</a> </td>
    	</tr>
</table>
</form>
<div align="center"><font size="2" face="Arial, Helvetica, sans-serif">PARTE DE MOVIMIENTO DE COMBUSTIBLES DEL:<?php echo $fechad; ?> al <?php echo $fechaa;?></font><br>
<div align="left"><font size="2" face="Arial, Helvetica, sans-serif">SUCURSAL: <?php echo $cod_almacen;?>
</font></div>
</div>
<table width="98%" border="1" cellpadding="0" cellspacing="0">
  <!--DWLayoutTable-->
  	<tr>
    		<td width="32" height="59"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Nro Parte</strong></font></div></td>
    		<td width="32"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Cod. Art</strong></font></div></td>
    		<td width="33"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Lado-Tq</strong></font></div></td>
    		<td width="44"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Manguera</strong></font></div></td>
    		<td width="54"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Inicial (galones)</strong></font></div></td>
    		<td width="54"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Final (galones)</strong></font></div></td>
    		<td width="46"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Galones<br>Vendidos </strong></font></div></td>
    		<td width="53"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Cont&oacute;metro<br>Inicial (Soles)</strong></font></div></td>
    		<td width="54"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Final (Soles)</strong></font></div></td>
    		<td width="46"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Soles<br>Vendidos </strong></font></div></td>
    		<td width="50"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Afericiones</strong></font></div></td>
    		<td width="44"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Consumo</strong></font></div></td>
    		<td width="54"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Descripci&oacute;n</strong></font></div></td>
    		<td width="105" valign="top"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong><br>Fecha</strong></font></div></td>
  	</tr>
  	<!-- <?php   
	for($i=0;$i<pg_numrows($rs3);$i++){
        	$E = pg_fetch_row($rs3,$i);
                        print '
        ?> -->
  	<tr>
    		<td height="21"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[0].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[1].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[15].' - '.$E[2].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[3].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[4].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[5].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$E[6].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[7].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[8].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[9].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[10].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[11].'</font></div></td>
    		<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[12].'</font></div></td>
    		<td width="105" valign="top"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[13].'   '.$E[14].'</font></div></td>
  	</tr>
  	<!-- <?php '; } ?> -->
</table>
<br>
<table width="763" border="1" cellpadding="0" cellspacing="0">
	<tr>
    		<td width="37"><div align="center"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font size="-7"></font></font></font></div></td>
    		<td width="48"><div align="center"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font size="-7"></font></font></font></div></td>
    		<td colspan="2"><div align="center"></div><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>RESUMEN DE VENTA</strong></font></div></td>
    		<td colspan="2"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>RESUMEN DE AFERICI&Oacute;N</strong></font></div><div align="center"></div></td>   		
      		<td colspan="2"> <div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>CONSUMO INTERNO</strong></font></div><div align="center"></div></td>
    		<td width="84"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>RESUMEN</strong></font></div></td>
    		<td width="82"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><strong>NETO</strong></font></div></td>
  	</tr>
  	<tr>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Producto</em></font></div></td>
    		<td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Descripci&oacute;n</em></font></div></td>
    		<td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Galones</em></font></div></td>
    		<td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
    		<td width="79"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Galones</em></font></div></td>
    		<td width="79"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
    		<td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Galones</em></font></div></td>
    		<td width="83"><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Galones</em></font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif"><em>Soles</em></font></div></td>
  	</tr>
  	<!-- <?php for($i=0;$i<pg_numrows($rs4);$i++){
  	$Q4 = pg_fetch_row($rs4,$i);
  	$total_res_ven_gal = $total_res_ven_gal + $Q4[2];
  	$total_res_ven_val = $total_res_ven_val + $Q4[3];
  	$total_res_afe_gal = $total_res_afe_gal + $Q4[4];
  	$total_res_afe_val = $total_res_afe_val + $Q4[5];
  	$total_res_con_gal = $total_res_con_gal + $Q4[6];
  	$total_res_con_val = $total_res_con_val + $Q4[7];
  	$total_resumen_gal = $total_resumen_gal + $Q4[8];
  	$total_neto_val          = $total_neto_val    + $Q4[9];
  	print '
 	?>-->
  	<tr>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[0].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[1].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[2].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[3].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[4].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[5].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[6].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[7].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[8].'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$Q4[9].'</font></div></td>
  	</tr>
  	<!-- <?php '; }?> -->
  	<!-- <?php print ' ?> -->
  	<tr>
    		<td><div align="center"><font size="2"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font size="-7"></font></font></font></font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">TOTAL :</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_ven_gal.'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_ven_val.'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_afe_gal.'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_afe_val.'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_con_gal.'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_res_con_val.'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_resumen_gal.'</font></div></td>
    		<td><div align="center"><font size="-7" face="Arial, Helvetica, sans-serif">'.$total_neto_val.'</font></div></td>
  	</tr>
  	<!-- <?php '; ?>-->
</table>
<p>&nbsp;</p>
</body>
</html>
