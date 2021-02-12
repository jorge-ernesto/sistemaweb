<?php

if($boton != "Nuevo_Parte" && $boton != "Eliminar_Ultimo_Parte") {
	require("../menu_princ.php");
}
include("../functions.php");

$fecha 	= getdate();
$dia 	= $fecha['mday'];
$mes 	= $fecha['mon'];
$year 	= $fecha['year'];
$hoy 	= $dia.'-'.$mes.'-'.$year;

if($diad == "") {
	$diad = "01";
}
if($diaa == "") {
	$diaa = "29";
}
if($mesd == "") {
	$mesd = $mes;
}
if($mesa == "") {
	$mesa = $mes;
}
if($anoa == "") {
	$anoa = $year;
}
if($anod == "") {
	$anod = $year;
}

if($cod_almacen == "") {
	$and_cont = " AND cont.ch_sucursal=trim('$almacen')";
	$rs6 = pg_exec("SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen='1' AND trim(ch_almacen)=trim('$almacen') ORDER BY ch_nombre_almacen");
	$R6  = pg_fetch_row($rs6,0);
	$sucursal_dis = $R6[1];
	$sucursal_val = $R6[0];
	$cod_almacen  = $almacen;
} else {
	$cod_almacen = trim($cod_almacen);
	$and_cont="and cont.ch_sucursal=trim('$cod_almacen')";
	$rs6 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
	and  trim(ch_almacen)=trim('$cod_almacen')  order by ch_nombre_almacen");
	$R6 = pg_fetch_row($rs6,0);
	$sucursal_dis = $R6[1];
	$sucursal_val = $R6[0];
}

if ($_REQUEST['diasd'] && $_REQUEST['diasa']) {
	$nuevofechad = split('/',$_REQUEST['diasd']);
	$diad = $nuevofechad[0];
	$mesd = $nuevofechad[1];
	$anod = $nuevofechad[2];
	$nuevofechaa = split('/',$_REQUEST['diasa']);
	$diaa = $nuevofechaa[0];
	$mesa = $nuevofechaa[1];
	$anoa = $nuevofechaa[2];
} else {
	$diad = date('d');
	$diaa = date('d');
	$mesd = date('m');
	$mesa = date('m');
	$anoa = date('Y');
	$anod = date('Y');
}

$rs1 = pg_exec("select c.ch_numeroparte , c.ch_numeroparte  || '---------' || c.dt_fechaparte from comb_ta_contometros c ");
$rs2 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' order by ch_nombre_almacen");

switch ($boton) {

        case "Nuevo_Parte":
                	pg_close();
                	header("Location: cmb_add_contometro.php");
        		break;

        case "Eliminar_Ultimo_Parte":
                	header("Location: cmb_edit_contometro.php?action=eliminar_ultimo&cod_almacen=$cod_almacen");
        		break;

        case "Reporte":
                	$fecha_de = $diad.'-'.$mesd.'-'.$anod;
                	$fecha_hasta = $diaa.'-'.$mesa.'-'.$anoa;
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
					-cont.nu_descuentos, 
					comb.ch_nombrecombustible, 
					cont.dt_fechaparte, 
					cont.ch_responsable, 
					surt.ch_numerolado as lado	
				FROM 
					comb_ta_contometros cont
					LEFT JOIN comb_ta_surtidores surt ON (cont.ch_sucursal= surt.ch_sucursal and cont.ch_surtidor=surt.ch_surtidor)
					LEFT JOIN comb_ta_combustibles comb ON (cont.ch_codigocombustible=comb.ch_codigocombustible)				
				WHERE 				
					cont.dt_fechaparte >= to_date('$fecha_de','DD-MM-YYYY')	
					and cont.dt_fechaparte <=to_date('$fecha_hasta','DD-MM-YYYY')
					$and_cont 
				ORDER BY 
					parte,
					lado,
					manguera,
					tanque";
		        //echo $q3;
		        $rs3 = pg_exec($q3);

			$q4 = "SELECT
					C.codigo as codigo,
					COMB.descripcion as descripcion,
					ROUND(COMB.total_cantidad,3) as total_cantidad,
					ROUND(COMB.total_venta,2) as total_venta,
					AFC.af_cantidad as af_cantidad,
					AFC.af_total as af_total,
					'0.000' as consumo_galon,
					'0.000' as consumo_valor,
					COMB.descuentos as descuentos,
					CASE WHEN AFC.af_cantidad IS NULL THEN COMB.total_cantidad ELSE COMB.total_cantidad - AFC.af_cantidad END as resumen,
					CASE WHEN AFC.af_cantidad IS NULL THEN (COMB.total_venta + COMB.descuentos) ELSE ((COMB.total_venta + COMB.descuentos) - AFC.af_total) END as neto_soles				
				FROM

					(SELECT ch_codigocombustible AS codigo FROM comb_ta_combustibles) C

					INNER JOIN 

					(SELECT
						comb.ch_codigocombustible AS codigo,
						cmb.ch_nombrecombustible AS descripcion,
						SUM(CASE WHEN comb.nu_ventagalon!=0 THEN comb.nu_ventavalor ELSE 0 END) AS total_venta,
						SUM(CASE WHEN comb.nu_ventagalon!=0 THEN comb.nu_ventagalon ELSE 0 END) AS total_cantidad,
						ROUND(SUM(comb.nu_descuentos),2) AS descuentos
					 FROM 
						comb_ta_contometros comb
						LEFT JOIN comb_ta_combustibles cmb ON (comb.ch_codigocombustible = cmb.ch_codigocombustible)
					 WHERE 	
						comb.ch_sucursal = '$almacen' 
						and comb.dt_fechaparte BETWEEN to_date('$fecha_de', 'DD/MM/YYYY') and to_date('$fecha_hasta', 'DD/MM/YYYY')
					GROUP BY 
						comb.ch_codigocombustible,
						cmb.ch_nombrecombustible
					) COMB on COMB.codigo = C.codigo
	
					LEFT JOIN

					(SELECT 
						af.codigo as codigo,
						SUM(af.importe) AS af_total,
						ROUND(SUM(af.cantidad), 3) AS af_cantidad
					FROM 
						pos_ta_afericiones af
					WHERE
						af.es = '$almacen' 
						AND af.dia BETWEEN to_date('$fecha_de', 'DD/MM/YYYY') and to_date('$fecha_hasta', 'DD/MM/YYYY')
					GROUP BY
						af.codigo
					)AFC ON AFC.codigo = C.codigo
				;";


			$rs4 = pg_exec($q4);
        		break;

        case "Importar" :
               	        break;
}
//echo $q3;
pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Configuracion de Contometros</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<script language="JavaScript" src="miguel-funciones.js"></script>
	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript"> 
            
		    $(document).ready(function(){
		        $( "#fecha" ).datepicker(
		        {changeMonth: true,
		            changeYear: true,
		            onSelect:function(fecha,obj){

				var fecha_v = $('#fecha').val();

				if(fecha_v > $('#fecha_aprosys').val()){
					alert('La fecha de ingreso no puede ser mayor a la Fecha Sistema');
				}else if(fecha_v.substr(5,2) < $('#mes_apro').val()){
					alert('El mes debe de ser el mismo al de la Fecha Sistema !');
				}else if(fecha_v.substr(5,2) > $('#mes_apro').val()){
					alert('El mes debe de ser el mismo al de la Fecha Sistema !');
				} else {			
					$.ajax({
						type: "POST",
						url: "forms/fecha.php",
						data:{fecha:fecha},
						success:function (response){
							$("#resultado").html(response);
						}
					});
				}
		            }
		        });
			    
			$( "#fecha" ).datepicker("option", "dateFormat","yy-mm-dd");
			$('#fecha').val($('#fecha_ini').val());

		    });

	        function mostrarVentanaImportar(form1){
        	        window.open('cmb_contometros_auto_insert.php','miwin','width=200,height=220,scrollbars=no,menubar=no,left=60,top=60');
        	}

	</script>
</head>

<body>
PARTES DE VENTA
<hr noshade>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<FORM name="form1" method="post" action="cmb_contometro.php">
<center>
<table width="800" border="0" cellpadding="0" cellspacing="0">
<!--DWLayoutTable-->
<tr>
	<td width="118" valign="top" colspan="3"> Sucursales
        	<select name="cod_almacen">
        	<?php
                if($cod_almacen!="") { 
			print "<option value='$sucursal_val' selected>$sucursal_val -- $sucursal_dis</option>"; 
		}
               	for($i=0;$i<pg_numrows($rs2);$i++) {
			$B = pg_fetch_row($rs2,$i);
                        print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
                }
                ?>
        	</select>
	</td>
      	<td width="304" valign="top"><div align="center"><strong>N&Uacute;MEROS DE PARTE</strong></div></td>
      	<td width="100">&nbsp;</td>
      	<td width="100">&nbsp;</td>
</tr>
<tr>
	<td height="126">&nbsp;</td>
      	<td colspan="4" valign="top"><table width="688" border="0">
        <!--DWLayoutTable-->
<tr>
	<td width="46" height="23" colspan="4">REPORTE POR RANGO DE FECHAS</td>
        <td width="36">&nbsp;</td>
	<td colspan="6" valign="top"> </td>
        <td width="63">&nbsp;</td>
</tr>

<tr>
	<td height="21">
		<div align="left">FECHA</div></td>
	<td>
		<input type="hidden" id="fecha_ini" name = "fecha_ini" value='<?php echo $_REQUEST['fecha']; ?>' />
		<input maxlength="10" size="10" type='text' name ='fecha' id='fecha' class='fecha_formato' value='<?php echo $_REQUEST['fecha']; ?>' /></td>
	<td align="left"><span id="resultado"></span>
</tr>

<!--<tr>
	<td height="44">&nbsp;</td>
        <th colspan="2" valign="top">DESDE <input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('form1.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:3;"></th>
        <th width="140" valign="top" colspan="2">HASTA <input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('form1.diasa');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></th>
        <th width="76" valign="top"><input type="submit" name="boton" value="Reporte"></th>
        <td></td>
</tr>
-->

<tr>
	<td height="42" colspan="4" valign="top">
	<!--<a href="#" onClick="javascript:window.open('cmb_contometro-texto.php?fechad=<?php echo $fecha_de;?>&fechaa=<?php echo $fecha_hasta;?>&cod_almacen=<?php echo $cod_almacen;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');"><br>
        Exportar Reporte
	</a> -->
            <a href="reporte_excel_combustible.php" ><br>
        Exportar Reporte
	</a>
            
	</td>
	<td colspan="2"><input type="submit" name="boton" value="Nuevo_Parte"></td>
	<td width="134"><input type="submit" name="boton" value="Eliminar_Ultimo_Parte"></td>
	<td><input type="button" name="btn_importar" value="Importar" onClick="javascript:mostrarVentanaImportar(form1);"></td>
        <td>&nbsp;</td>
</tr>
<tr>
	<td height="5"></td>
        <td></td>
        <td width="28"></td>
        <td></td>
        <td></td>
        <td width="0"></td>
        <td></td>
        <td></td>
        <td></td>
        </tr>
</table></td>
<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="6" valign="top"><table width="110%" border="1" cellpadding="0" cellspacing="0">
        <!--DWLayoutTable-->
<tr>
	<td width="32" height="59" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Nro Parte</strong></font></div></td>
	<td width="32" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Cod. Art</strong></font></div></td>
	<td width="33" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Lado-Tq</strong></font></div></td>
	<td width="44" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Manguera</strong></font></div></td>
	<td width="54" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Inicial (galones)</strong></font></div></td>
	<td width="54" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Final (galones)</strong></font></div></td>
	<td width="46" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Galones<br>Vendidos </strong></font></div></td>
	<td width="53" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Cont&oacute;metro<br>Inicial (Soles)</strong></font></div></td>
	<td width="54" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Contometro<br>Final (soles)</strong></font></div></td>
	<td width="46" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Soles<br>Vendidos </strong></font></div></td>
	<td width="50" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Afericiones</strong></font></div></td>
	<!--<td width="44" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Consumo</strong></font></div></td>-->
	<td width="44" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Descuentos</strong></font></div></td>
	<td width="100" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong>Descripci&oacute;n</strong></font></div></td>
	<td width="200" valign="top" style="color:blue;"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif"><strong><br>Fecha</strong></font></div></td>
</tr>
          <!-- <?php for($i=0;$i<pg_numrows($rs3);$i++){
                          $E = pg_fetch_row($rs3,$i);
                        print '
                  ?> -->
<tr>
	<td height="21"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[0].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[1].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[16].' - '.$E[2].'</font></div></td>
	<td><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[3].'</font></div></td>
	<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[4].'</font></div></td>
	<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[5].'</font></div></td>
	<td><div align="right"><font size="-7" face="Arial, Helvetica, sans-serif">'.$E[6].'</font></div></td>
	<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[7].'</font></div></td>
	<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[8].'</font></div></td>
	<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[9].'</font></div></td>
	<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[10].'</font></div></td>
	<!--<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[11].'</font></div></td>-->
	<td><div align="right"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[12].'</font></div></td>
	<td width="100" ><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[13].'</font></div></td>
	<td width="200" valign="top"><div align="center"><font size="-10" face="Arial, Helvetica, sans-serif">'.$E[14].'   '.$E[15].'</font></div></td>
</tr>
	<!-- <?php '; } ?> -->
</table></td>
</tr>
<tr>
	<td height="33">&nbsp;</td>
	<td>&nbsp;</td>
      	<td>&nbsp;</td>
      	<td>&nbsp;</td>
      	<td>&nbsp;</td>
      	<td>&nbsp;</td>
</tr>
</table>
<table width="763" border="1" cellpadding="0" cellspacing="0">
<tr>
	<td width="70" bgcolor="yellow"><div align="center"></div></td>
      	<td width="90" bgcolor="yellow"><div align="center"></div></td>
      	<td colspan="2" bgcolor="yellow"><div align="center"></div><div align="center"><font size="-5"><strong>RESUMEN DE VENTA</strong></font></div></td>
      	<td colspan="2" bgcolor="yellow"><div align="center"><font size="-5"><strong>RESUMEN DE AFERICI&Oacute;N</strong></font></div><div align="center"></div></td>
      	<!--<td colspan="2" bgcolor="yellow"> <div align="center"><font size="-5"><strong>CONSUMO INTERNO</strong></font></div><div align="center"></div></td>-->
      	<td width="84" bgcolor="yellow"><div align="center"><font size="-5"><strong>DESCUENTOS</strong></font></div></td>		
      	<td width="84" bgcolor="yellow"><div align="center"><font size="-5"><strong>RESUMEN</strong></font></div></td>
      	<td width="215" bgcolor="yellow"><div align="center"><font size="-5"><strong>NETO</strong></font></div></td>
</tr>
<tr>
      	<td bgcolor="yellow"><div align="center"><font size="-5"><em>Producto</em></font></div></td>
      	<td width="90" bgcolor="yellow"><div align="center"><font size="-5"><em>Descripci&oacute;n</em></font></div></td>
      	<td width="83" bgcolor="yellow"><div align="center"><font size="-5"><em>Galones</em></font></div></td>
      	<td width="83" bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>
      	<td width="79" bgcolor="yellow"><div align="center"><font size="-5"><em>Galones</em></font></div></td>
      	<td width="79" bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>
      	<!--<td width="83" bgcolor="yellow"><div align="center"><font size="-5"><em>Galones </em></font></div></td>
      	<td width="83" bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>-->
      	<td width="83" bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>	  
      	<td bgcolor="yellow"><div align="center"><font size="-5"><em>Galones</em></font></div></td>
      	<td bgcolor="yellow"><div align="center"><font size="-5"><em>Soles</em></font></div></td>
</tr>
<!-- <?php for($i=0;$i<pg_numrows($rs4);$i++){

  $Q4 = pg_fetch_row($rs4,$i);
  $total_res_ven_gal    = $total_res_ven_gal    + $Q4[2];
  $total_res_ven_val    = $total_res_ven_val    + $Q4[3];
  $total_res_afe_gal    = $total_res_afe_gal    + $Q4[4];
  $total_res_afe_val    = $total_res_afe_val    + $Q4[5];
  $total_res_descuentos = $total_res_descuentos + $Q4[8];
  $total_resumen_gal    = $total_resumen_gal    + $Q4[9];
  $total_neto_val       = $total_neto_val       + $Q4[10];
  print '
  ?>-->
<tr>
	<td><div align="center"><font size="-5">'.$Q4[0].'</font></div></td>
      	<td width="120"><div align="center"><font size="-5">'.$Q4[1].'</font></div></td>
      	<td><div align="right"><font size="-5">'.$Q4[2].'</font></div></td>
      	<td><div align="right"><font size="-5">'.$Q4[3].'</font></div></td>
     	<td><div align="right"><font size="-5">'.htmlentities(number_format($Q4[4], 3, '.', ',')).'</font></div></td>
      	<td><div align="right"><font size="-5">'.htmlentities(number_format($Q4[5], 3, '.', ',')).'</font></div></td>
      	<td><div align="right"><font size="-5">'.htmlentities(number_format($Q4[8], 3, '.', ',')).'</font></div></td>
      	<td><div align="right"><font size="-5">'.htmlentities(number_format($Q4[9], 3, '.', ',')).'</font></div></td>
      	<td><div align="right"><font size="-5">'.htmlentities(number_format($Q4[10], 3, '.', ',')).'</font></div></td>
</tr>
    <!-- <?php '; }?> -->
    <!-- <?php print ' ?> -->
<tr>
	<td><div align="center"><font size="2"><font size="-5"></font></font></div></td>
      	<td><div align="center" style="color:blue"><font size="-5">TOTAL</font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5">'.htmlentities(number_format($total_res_ven_gal, 3, '.', ',')).'</font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5">'.htmlentities(number_format($total_res_ven_val, 3, '.', ',')).'</font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5">'.htmlentities(number_format($total_res_afe_gal, 3, '.', ',')).'</font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5">'.htmlentities(number_format($total_res_afe_val, 3, '.', ',')).'</font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5">'.htmlentities(number_format($total_res_descuentos, 3, '.', ',')).'</font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5">'.htmlentities(number_format($total_resumen_gal, 3, '.', ',')).'</font></div></td>
      	<td><div align="right" style="color:blue"><font size="-5">'.htmlentities(number_format($total_neto_val, 3, '.', ',')).'</font></div></td>
</tr>
    <!-- <?php '; ?>-->
</table>
<p>&nbsp;</p>
</center>
</FORM>
</body>
</html>
