<?php
//include("../valida_sess.php");
//include("../config.php");
//include("store_procedures.php");
//include("inc_top.php");
include "../menu_princ.php";
$hoy = date("d/m/Y");
if($c_fec_desde==""){$c_fec_desde=$hoy;}
if($c_fec_hasta==""){$c_fec_hasta=$hoy;}

//$col = 10;
//$lin = str_repeat("=",240);
//$salto = chr(12);
switch($accion){
	
	case "Reporte":	
		
		if($c_est==""){$c_est="TODOS";}
		if($c_trabajador==""){$c_trabajador="TODOS";}
		//$rs = REPORTE_VENTAS_DIARIAS($c_est, $c_fec_desde,$c_fec_hasta,$c_opt_reporte);
		$rs = REPORTE_CONSUMO_PERSONAL($c_fec_desde,$c_fec_hasta,$c_est,$c_trabajador,$c_opt_reporte);
		
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
    <td colspan="3"><div align="center">REPORTE DE VENTAS DIARIAS EN RESUMEN</div></td>
      <td width="17%"><!--<a href="#" onClick="javascript:accion.value='Imprimir',form1.submit();">IMPRIMIR</a>-->
	  	<a href="#" onClick="javascript:window.open('generar_reporte_ventas_diarias.php?c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&accion=<?php echo $accion;?>&c_opt_reporte=<?php echo $c_opt_reporte;?>&c_est=<?php echo $c_est;?>','winrep1','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">IMPRESION</a>
	  </td>
  </tr>
  <tr> 
      <td><div align="right">Estacion: 
          <input type="text" name="c_est" size="11" value="<?php echo $c_est;?>">
          <br>
          Trabajador: 
          <input type="text" name="c_trabajador" size="11" value="<?php echo $c_trabajador;?>">
          </div></td>
    <td width="20%">Desde: 
      <input type="text" name="c_fec_desde" size="11" value="<?php echo $c_fec_desde;?>"> <a href="javascript:show_calendar('form1.c_fec_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
    <td width="20%">Hasta:
      <input type="text" name="c_fec_hasta" size="11" value="<?php echo $c_fec_hasta;?>"> <a href="javascript:show_calendar('form1.c_fec_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
      <td width="29%"> <input type="radio" name="c_opt_reporte" value="RESUMIDO_XT" <?php if($c_opt_reporte=="RESUMIDO_XT"){echo "checked";}?> >
        Resumido por Trabajador<br>
        <input type="radio" name="c_opt_reporte" value="DETALLADO_XT" <?php if($c_opt_reporte=="DETALLADO_XT"){echo "checked";}?>>
        Detallado por Trabajador<br>
        <input type="radio" name="c_opt_reporte" value="DETALLADO_XV" <?php if($c_opt_reporte=="DETALLADO_XV"){echo "checked";}?>>
        Detallado por Vale</td>
      <td>&nbsp;</td>
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
<p></p><?php if($c_opt_reporte=="RESUMIDO_XT"){?>
  Reporte Resumido Por Trabajador 
  <table width="71%" border="1">
    <tr>
      <td width="14%">Trabajador</td>
      <td width="59%">Nombres<br>
        Estacion </td>
      <td width="27%"><br>
        Total Importe</td>
    </tr>
	<?php $est = "";
	$sum_importe=0;
	for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
		$sum_importe = $sum_importe + $A["importe"];
	?>
    
		<?php if($est!=$A["ch_sucursal"]){
			$est = $A["ch_sucursal"];
		?><tr>
      <td><?php echo $A["ch_sucursal"];?></td>
      <td><?php echo $A["des_sucursal"];?></td>
      <td>&nbsp;</td>
    	</tr><?php } ?>
    <tr>
      <td><?php echo $A["cod_trabajador"]?></td>
      <td><?php echo $A["des_trabajador"]?></td>
      <td><?php echo $A["importe"];?></td>
    </tr>
	
    <?php if(pg_result($rs,$i+1,"ch_sucursal")!=$A["ch_sucursal"]){?><tr>
      <td>&nbsp;</td>
      <td><div align="right">Total Sucursal -&gt; </div></td>
      <td><?php echo $sum_importe?></td>
    </tr><?php $sum_importe=0;}?>
	
	<?php } //fin del for
	?>
  </table>
  <?php } ?>
  
  <?php if($c_opt_reporte=="DETALLADO_XT"){?>
  Reporte Detallado Por Trabajador 
  <table width="99%" border="1">
    <tr> 
      <td width="14%">Trabajador<br>
        Estacion</td>
      <td width="8%">Nombres<br> </td>
      <td width="18%"><br>
        Fecha</td>
      <td width="7%"><br>
        Vale</td>
      <td width="39%"><br>
        Producto</td>
      <td width="6%"><br>
        Total Importe</td>
      <td width="8%">&nbsp;</td>
    </tr>
    <?php $est = "";
	$trabajador="";
	$total_sucursal=0;
	$total_trabajador=0;
	$total_reporte=0;
	for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
		$total_sucursal = $total_sucursal+ $A["nu_importe"];
		$total_trabajador = $total_trabajador+ $A["nu_importe"];
		$total_reporte = $total_reporte+ $A["nu_importe"];
	?>
    <?php if($est!=$A["ch_sucursal"]){
			$est = $A["ch_sucursal"];
		?>
    <tr> 
      <td><?php echo $A["ch_sucursal"];?></td>
      <td colspan="4"><?php echo $A["des_sucursal"];?></td>
      <td colspan="2">&nbsp;</td>
    </tr>
    <?php } ?>
    <?php if($trabajador!=$A["cod_trabajador"]){
		$trabajador=$A["cod_trabajador"];
	?>
    <tr> 
      <td><?php echo $A["cod_trabajador"]?></td>
      <td colspan="4"><?php echo $A["des_trabajador"]?> Limite de Credito -&gt; <?php echo $A["limite_credito"]?></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php } ?>
    <tr> 
      <td colspan="2"><?php echo $A["ch_sucursal"];?> - <?php echo $A["des_sucursal"];?></td>
      <td><?php echo $A["dt_fecha"];?></td>
      <td><?php echo $A["vale"];?></td>
      <td><?php echo $A["ch_articulo"];?> <?php echo $A["des_articulo"];?> (<?php echo $A["nu_cantidad"];?>)</td>
      <td><?php echo $A["nu_importe"];?></td>
      <td>&nbsp;</td>
    </tr>
    <?php if(pg_result($rs,$i+1,"cod_trabajador")!=$A["cod_trabajador"]){?>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="4"><div align="right">Total Trabajador -&gt;</div></td>
      <td><?php echo $total_trabajador?></td>
      <td>&nbsp;</td>
    </tr>
    <?php $total_trabajador=0;
	}?>
    <?php if(pg_result($rs,$i+1,"ch_sucursal")!=$A["ch_sucursal"]){?>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="4"><div align="right">Total Sucursal -&gt; </div></td>
      <td><?php echo $total_sucursal?></td>
      <td>&nbsp;</td>
    </tr><?php $total_sucursal=0;}?>
    <?php } //fin del for
	?>
    <tr>
      <td>&nbsp;</td>
      <td colspan="4"><div align="right">Total Reporte -&gt;</div></td>
      <td><?php echo $total_reporte?></td>
      <td>&nbsp;</td>
    </tr>
    
  </table>
  <?php } ?>
  
  <?php if($c_opt_reporte=="DETALLADO_XV"){?>
  Reporte Detallado Por Vale 
  <table width="99%" border="1">
    <tr> 
      <td width="14%">Trabajador<br>
        Estacion</td>
      <td width="8%">Nombres<br> </td>
      <td width="18%"><br>
        Fecha</td>
      <td><br>
        Vale<br> </td>
      <td width="6%"><br>
        Total Importe</td>
      <td width="8%">&nbsp;</td>
    </tr>
    <?php $est = "";
	$trabajador="";
	$total_sucursal=0;
	$total_trabajador=0;
	$total_reporte=0;
	for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
		$total_sucursal = $total_sucursal+ $A["nu_importe"];
		$total_trabajador = $total_trabajador+ $A["nu_importe"];
		$total_reporte = $total_reporte+ $A["nu_importe"];
	?>
    <?php if($est!=$A["ch_sucursal"]){
			$est = $A["ch_sucursal"];
		?>
    <tr> 
      <td><?php echo $A["ch_sucursal"];?></td>
      <td colspan="3"><?php echo $A["des_sucursal"];?></td>
      <td colspan="2">&nbsp;</td>
    </tr>
    <?php } ?>
    <?php if($trabajador!=$A["cod_trabajador"]){
		$trabajador=$A["cod_trabajador"];
	?>
    <tr> 
      <td><?php echo $A["cod_trabajador"]?></td>
      <td colspan="3"><?php echo $A["des_trabajador"]?> Limite de Credito -&gt; <?php echo $A["limite_credito"]?></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php } ?>
    <tr> 
      <td colspan="2"><?php echo $A["ch_sucursal"];?> - <?php echo $A["des_sucursal"];?></td>
      <td><?php echo $A["dt_fecha"];?></td>
      <td><?php echo $A["vale"];?></td>
      <td><?php echo $A["nu_importe"];?></td>
      <td>&nbsp;</td>
    </tr>
    <?php if(pg_result($rs,$i+1,"cod_trabajador")!=$A["cod_trabajador"]){?>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="3"><div align="right">Total Trabajador -&gt;</div></td>
      <td><?php echo $total_trabajador?></td>
      <td>&nbsp;</td>
    </tr>
    <?php $total_trabajador=0;
	}?>
    <?php if(pg_result($rs,$i+1,"ch_sucursal")!=$A["ch_sucursal"]){?>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="3"><div align="right">Total Sucursal -&gt; </div></td>
      <td><?php echo $total_sucursal?></td>
      <td>&nbsp;</td>
    </tr>
    <?php $total_sucursal=0;}?>
    <?php } //fin del for
	?>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="3"><div align="right">Total Reporte -&gt;</div></td>
      <td><?php echo $total_reporte?></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <?php } ?>
</form>

</body>
</html>

<?php pg_close();?>