<?php
	include("../menu_princ.php");
	include("../functions.php");

	/*obtenemos fecha*/
	$nuevofechad = split('/',$_REQUEST['diasd']);
	$diad=$nuevofechad[0];
	$mesd=$nuevofechad[1];
	$anod=$nuevofechad[2];
	$nuevofechaa = split('/',$_REQUEST['diasa']);
	$diaa=$nuevofechaa[0];
	$mesa=$nuevofechaa[1];
	$anoa=$nuevofechaa[2];
	
	//include("../valida_sess.php");
	if($boton!="Reporte"){
		$cod_tanque="";
	}
		
	$fecha = getdate();
	$dia = $fecha['mday'];
	$mes = $fecha['mon'];
	$year = $fecha['year'];
	$hoy = $dia.'-'.$mes.'-'.$year;
	
	if($diad==""){$diad="01";}
	if($diaa==""){$diaa="29";}
	if($mesd==""){$mesd=$mes;}
	if($mesa==""){$mesa=$mes;}
	if($anoa==""){$anoa=$year;}
	if($anod==""){$anod=$year;}
	
	if(trim($cod_almacen)!=""){
		$rs6 = pg_exec("
				SELECT 
					ch_almacen ,
					ch_nombre_almacen 
				FROM 
					inv_ta_almacenes 
				WHERE 
					ch_clase_almacen='1' 
					AND  trim(ch_almacen)=trim('$cod_almacen')  
				ORDER BY 
					ch_nombre_almacen
				");
		
		$R6 = pg_fetch_row($rs6,0);
		$sucursal_dis = $R6[1];
		$sucursal_val = $R6[0];
		$almacen=$cod_almacen;
	}
	if($cod_almacen==""){
		$cod_almacen = $almacen;
	}
	if($cod_tanque==""){
		$cod_tanque="01";
	}
	
	$fechad = $diad.'-'.$mesd.'-'.$anod;
	$fechaa = $diaa.'-'.$mesa.'-'.$anoa;
	$rs1 = pg_exec($coneccion,"	SELECT DISTINCT 
						a.ch_tanque,
						a.ch_tanque  || ' -- ' || b.ch_nombrecombustible 
					FROM 
						comb_ta_tanques a,
						comb_ta_combustibles b,
						comb_ta_tanques c
					WHERE 
						a.ch_codigocombustible=b.ch_codigocombustible
						AND a.ch_tanque=c.ch_tanque
						AND c.ch_codigocombustible=b.ch_codigocombustible
						AND c.ch_sucursal=trim('$almacen') ");
	
	$rs2 = pg_exec("SELECT 
				ch_almacen ,
				ch_nombre_almacen 
			FROM 
				inv_ta_almacenes 
			WHERE 
				ch_clase_almacen='1' 
			ORDER BY 
				ch_almacen");
	
	$comb = pg_exec("SELECT 
				comb.ch_nombrecombustible 
			FROM 
				comb_ta_combustibles comb, 
				comb_ta_tanques tan 
			WHERE 
				tan.ch_codigocombustible=comb.ch_codigocombustible 
				AND tan.ch_tanque='$cod_tanque'
				AND tan.ch_sucursal=trim('$cod_almacen') ");

	if(pg_numrows($comb)>0){
		$C = pg_fetch_row($comb,0);
		$comb=$C[0];
		
		$REP = sobrantesyfaltantesReporte($almacen,$cod_tanque,$fechad,$fechaa, $_REQUEST["unidadmedida"], $_REQUEST["detallecompras"]);
	
		if($_REQUEST['detallecompras']=="Si"){
			$REP1 = DetalleComprasReporte($almacen,$cod_tanque,$fechad,$fechaa);
		}
		//echo "<br>Fecha de -->".$fechad."Fecha a-->".$fechaa;
		}else{ $comb="";}
	pg_close();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>REPORTE SOBRANTE Y FALTANTES</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" src="miguel-funciones.js"></script>
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
</head>


<body>
<div align="center"> 
  <p><font face="Arial, Helvetica, sans-serif">Reporte de Sobrantes y Faltantes<font size="2"> 
    del: </font><font face="Arial, Helvetica, sans-serif"><font size="2"><?php echo $fechad; ?></font></font><font size="2"> 
    al <?php echo $fechaa;?></font></font></p>
  <div align="left"> <font size="2" face="Arial, Helvetica, sans-serif">SUCURSAL: 
    <?php echo $almacen;?> -- Tanque: <?php echo $cod_tanque;?> --- Combustible: <?php echo $comb;?></font> 
    <form name="form1" method="post" action="cmb_sobyfaltantes.php">
      <table width="657" border="1">
        <!--DWLayoutTable-->
	<tr> 
        	<td colspan="7" valign="top">Sucursales 
			<select name="cod_almacen" onChange="javascript:form1.submit()">
			<?php 
				if(trim($cod_almacen)!="")
					for($i=0;$i<pg_numrows($rs2);$i++)
					{
						$B = pg_fetch_row($rs2,$i);
						print "<option value='$B[0]' >$B[0] -- $B[1]</option>";	
					}
			?>
			</select>
            		<input type="hidden" name="hiddenField"><?php echo $boton;?>
		</td>
        </tr>

        <tr> 
		<td valign="top">
			<strong>DESDE:
			</strong>
		</td>
		<td width="350" valign="top">
			<input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>&nbsp;
				<a href="javascript:show_calendar('form1.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/>
				</a>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">
		</td>
	
		<td width="86" valign="top"><strong>HASTA:</strong></td>
	
		<td width="350"  valign="top">
			<input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>&nbsp;
				<a href="javascript:show_calendar('form1.diasa');">
					<img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/>
				</a>
				<div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">
		</td>	
	
		<th width="115" valign="top">
			<div align="left">Codigo Tanque 
				<select name="cod_tanque">
					<?php 
					if($comb!=""){
						print "<option value='$cod_tanque'>$cod_tanque -- $comb</option>";
					} else {
					for($i=0;$i<pg_numrows($rs1);$i++){
						$A = pg_fetch_row($rs1,$i);
						print "<option value='$A[0]'>$A[1]</option>";
					}}
					?>
				</select>
			</div>
		</th>
	
		<th width="150" valign="top">
			<div align="left">Unidad de Medida 
				<select name="unidadmedida">
					<OPTION value="Galones">Galones</OPTION>
					<OPTION value="Litros">Litros</OPTION>

				</select>
			</div>
		</th>
	
		<th width="150" valign="top">
			<div align="left">Detalle de Compras 
				<select name="detallecompras">
					<OPTION value="No">No</OPTION>
					<OPTION value="Si">Si</OPTION>
				</select>
			</div>
		</th>

		<th width="73" valign="top">
			<input type="submit" name="boton" value="Reporte">
		</th>
        </tr>
      </table>
    </form>
  </div>
</div>
<table width="488" height="26" border="0">
  <tr> 
    <td width="257"><a href="#" onClick="javascript:window.open('cmb_sobyfaltantes-reporte.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_tanque=<?php echo $cod_tanque;?>&cod_almacen=<?php echo $cod_almacen;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar 
      Reporte</a> </td>
    <td width="172"><div align="right"><a href="/sistemaweb/utils/impresiones.php?imprimir=ok&archivo=/sistemaweb/caja_bancos/liquidacion_venta.txt" target="_blank"> 
        </a><a href="/sistemaweb/utils/impresiones.php?imprimir=ok&archivo=/sistemaweb/combustibles/sobrantes_faltantes_comb.txt" target="_blank"><strong>Imprimir 
        &gt;&gt;</strong></a> 
        <?php
/***   FRED - Exportacion a Texto   ***/

	$ft=fopen('sobrantes_faltantes_comb.txt','w');

	if ($ft>0){
		$sql = "select ch_nombre_sucursal from int_ta_sucursales where ch_sucursal='$almacen'";
		$dia = date ("d/m/Y - H:i:s", time());
	
		$cod_almacen = trim(pg_result(pg_query($coneccion, $sql),0,0));
	
		$snewbuffer=$snewbuffer.str_pad("SOBRANTES Y FALTANTES DEL : $fechad  AL: $fechaa", 131, " ", STR_PAD_BOTH)."\n";
		$snewbuffer=$snewbuffer.str_pad("  SUCURSAL : $almacen  $cod_almacen    TANQUE : $cod_tanque    COMBUSTIBLE : $comb",106).str_pad($dia,25," ",STR_PAD_LEFT)."\n";
		$snewbuffer=$snewbuffer.str_pad("-",131,"-")."\n";
		$snewbuffer=$snewbuffer."                                                                  TRANSFERENCIA                                     DIFERENCIAS    \n";
		$snewbuffer=$snewbuffer."  FECHA         SALDO      COMPRA     MEDICION      VENTA      INGRESO      SALIDA      PARTE      VARILLA      DIARIA    ACUMULADA\n";
		$snewbuffer=$snewbuffer.str_pad("-",131,"-")."\n";
	}
/***   FRED - Exportacion a Texto   ***/

	$ft1=fopen('sobrantes_faltantes_DetalleCompras.txt','w');
	if ($ft1>0) {
		$sql = "select ch_nombre_sucursal from int_ta_sucursales where ch_sucursal='$almacen'";
		$dia = date ("d/m/Y - H:i:s", time());
	
		$cod_almacen = trim(pg_result(pg_query($coneccion, $sql),0,0));
	
		$snewbuffer1=$snewbuffer1.str_pad("DETALLE DE COMPRAS DEL : $fechad  AL: $fechaa", 131, " ", STR_PAD_BOTH)."\n";
		$snewbuffer1=$snewbuffer1.str_pad("  SUCURSAL : $almacen  $cod_almacen    TANQUE : $cod_tanque    COMBUSTIBLE : $comb",106).str_pad($dia,25," ",STR_PAD_LEFT)."\n";
		$snewbuffer1=$snewbuffer1.str_pad("-",131,"-")."\n";
		$snewbuffer1=$snewbuffer1."                                                                  TRANSFERENCIA                                     DIFERENCIAS    \n";
		$snewbuffer1=$snewbuffer1."  FECHA         DOCUMENTO      KILOS     G.E.      GALONES\n";
		$snewbuffer1=$snewbuffer1.str_pad("-",131,"-")."\n";
	}
?>
      </div></td>
  </tr>
</table>

<table width="771" border="1" cellpadding="0" cellspacing="0">
  <tr> 
    <td height="22"> <div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td colspan="2"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">TRANSFERENCIAS</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"></font></div></td>
    <td colspan="2"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DIFERENCIA</font></div></td>
  </tr>
  <tr> 
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">FECHA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">SALDO</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">COMPRA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">MEDICION</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">VENTA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">INGRESO</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">SALIDA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">PARTE</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">VARILLA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DIARIA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">ACUMULADA</font></div></td>
  </tr>
<!-- <?php 
	for($i=0;$i<count($REP);$i++) {  
		$TOT_SALDO 	= 	$TOT_SALDO + $REP[$i][1];
		$TOT_COMPRA 	= 	$TOT_COMPRA + $REP[$i][2];
		$TOT_MEDICION 	= 	$TOT_MEDICION + $REP[$i][3];
		$TOT_VENTA 	= 	$TOT_VENTA + $REP[$i][4];
		$TOT_INGRESO 	= 	$TOT_INGRESO + $REP[$i][5];
		$TOT_SALIDA 	= 	$TOT_SALIDA + $REP[$i][6];
		$TOT_PARTE 	= 	$TOT_PARTE + $REP[$i][7];
		$TOT_VARILLA 	= 	$TOT_VARILLA + $REP[$i][8];
		$TOT_DIARIA 	= 	$TOT_DIARIA + $REP[$i][9];
		$TOT_ACUMULADA 	= 	$TOT_ACUMULADA + $REP[$i][10];
	
	
	/*  FRED - importacion a texto  */
		$snewbuffer=$snewbuffer.$REP[$i][0]." ";
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][1],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][2],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][3],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][4],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][5],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][6],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][7],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][8],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][9],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($REP[$i][10],3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer."\n";
	/*  FRED - importacion a texto  */
	
	
		print '?>-->
		<tr> 
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP[$i][0].'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][1],3).'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][2],3).'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][3],3).'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][4],3).'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][5],3).'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][6],3).'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][7],3).'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][8],3).'</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][9],3).'&nbsp;&nbsp;</font></div></td>
		<td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP[$i][10],3).'&nbsp;&nbsp;</font></div></td>
		</tr>
		<!-- <?php '; 
	} 	
?>-->
  <tr> 
    <td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        TOTALES</font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_SALDO,3);?></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_COMPRA,3);?></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_MEDICION,3);?></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_VENTA,3);?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_INGRESO,3);?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_SALIDA,3);?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_PARTE,3);?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_VARILLA,3);?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo number_format($TOT_DIARIA,3); ?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
    <td><div align="right"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
  <!--      <?php echo number_format($TOT_ACUMULADA,3) ;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>
-->
  </tr></table>
    <?php
/*  FRED - importacion a texto  */
		$snewbuffer=$snewbuffer.str_pad("-",131,"-")."\n";
		$snewbuffer=$snewbuffer.str_pad("  TOTALES.:",10," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_SALDO,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_COMPRA,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_MEDICION,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_VENTA,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_INGRESO,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_SALIDA,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_PARTE,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_VARILLA,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_DIARIA,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer.str_pad(number_format($TOT_ACUMULADA,3),12," ",STR_PAD_LEFT);
		$snewbuffer=$snewbuffer."\n";

	fwrite($ft,$snewbuffer);
	fclose($ft);
/*  FRED - importacion a texto  */
	?>


<?php
	if($_REQUEST['detallecompras']=="Si"){

	

?>
<br><br>
<font size="2" face="Arial, Helvetica, sans-serif"><strong>COMPRAS </strong></font>
<table width="771" border="1" cellpadding="0" cellspacing="0">
    
    

  <tr> 
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">FECHA</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">DOCUMENTO</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">KILOS</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">G.E.</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">GALONES</font></div></td>
  </tr>
<!-- <?php 
print_r($REP1);
	for($i=0;$i<count($REP1);$i++) {  
		
		$TOT_KILOS 	= 	$TOT_KILOS + $REP1[$i][2];
		$TOT_GALONES 	= 	$TOT_GALONES + $REP1[$i][4];
	
		$snewbuffer1=$snewbuffer1.$REP1[$i][0]." ";
		$snewbuffer1=$snewbuffer1.$REP1[$i][1]." ";
		$snewbuffer1=$snewbuffer1.str_pad(number_format($REP1[$i][2],3),12," ",STR_PAD_LEFT);
		$snewbuffer1=$snewbuffer1.$REP1[$i][3]." ";
		$snewbuffer1=$snewbuffer1.str_pad(number_format($REP1[$i][4],3),12," ",STR_PAD_LEFT);
		$snewbuffer1=$snewbuffer1."\n";
	
		print '?>-->
		<tr> 
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP1[$i][0].'</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP1[$i][1].'</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP1[$i][2].'&nbsp;&nbsp;</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.$REP1[$i][3].'</font></div></td>
		<td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">'.number_format($REP1[$i][4],3).'&nbsp;&nbsp;</font></div></td>
		</tr>
		<!-- <?php '; 
	} 	
?>-->
  <tr> 
    <td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        TOTALES</font></font></font></font></font></font></div></td>

    <td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        *</font></font></font></font></font></font></div></td>

    <td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_KILOS;?></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>

    <td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        **</font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>

    <td><div align="center"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font face="Arial, Helvetica, sans-serif"><font face="Arial, Helvetica, sans-serif"><font size="-4"><font size="-4"><font size="-4"><font size="-4"><br>
        <?php echo $TOT_GALONES;?></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font></font><font size="-4"></font></font></font></font></font></font></div></td>

  </tr></table>
    <?php
}
		$snewbuffer1=$snewbuffer1.str_pad("-",131,"-")."\n";
		$snewbuffer1=$snewbuffer1.str_pad("  TOTALES.:",10," ",STR_PAD_LEFT);
		$snewbuffer1=$snewbuffer1.str_pad(number_format($TOT_KILOS,3),12," ",STR_PAD_LEFT);
		$snewbuffer1=$snewbuffer1.str_pad(number_format($TOT_GALONES,3),12," ",STR_PAD_LEFT);
		$snewbuffer1=$snewbuffer1."\n";

	fwrite($ft1,$snewbuffer1);
	fclose($ft1);
?>

</body>
</html>
