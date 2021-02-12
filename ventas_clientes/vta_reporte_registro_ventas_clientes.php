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
    $rs = REPORTE_REGISTRO_VENTAS_CLIENTES($c_fec_desde,$c_fec_hasta,$c_credito_contado, $c_estacion_oficina);
      echo $c_fec_desde."--".$c_fec_hasta."---".$c_credito_contado."---". $c_estacion_oficina;
  break;

}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Registro de Ventas por Clientes</title>
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
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<form name="form1" action="" method="post">
  <table width="774" border="0" cellpadding="1" cellspacing="1">
    <tr> 
      <td height="24" colspan="2">&nbsp;</td>
      <td colspan="3"><div align="center">REGISTRO DE VENTAS POR CLIENTES</div></td>
      <td width="17%"> 
        <!--<a href="#" onClick="javascript:accion.value='Imprimir',form1.submit();">IMPRIMIR</a>-->
        <a href="#" onClick="javascript:window.open('generar_reporte_detalle_consumo_vales.php?c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&accion=<?php echo $accion;?>&c_opt_reporte=<?php echo $c_opt_reporte;?>&c_est=<?php echo $c_est;?>&c_cliente=<?php echo $c_cliente;?>&c_num_liqui=<?php echo $c_num_liqui;?>','winrep1','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">IMPRESION</a> 
      </td>
    </tr>
    <tr> 
      <td width="15%">&nbsp;</td>
      <td width="7%">&nbsp;</td>
      <td width="20%" rowspan="2">Desde: 
        <input type="text" name="c_fec_desde" size="11" value="<?php echo $c_fec_desde;?>"><a href="javascript:show_calendar('form1.c_fec_desde');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></td>
      <td width="20%" rowspan="2">Hasta: 
        <input type="text" name="c_fec_hasta" size="11" value="<?php echo $c_fec_hasta;?>"><a href="javascript:show_calendar('form1.c_fec_hasta');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></td>
      <td width="29%" rowspan="2">
	<table width="100%" cellspacing="2" border="0" cellpadding="2">
	  <tbody>
	    <tr>
	      <td colspan="2"></td>
	      <td colspan="2"></td>
	    </tr>
	    <tr>
	      <td>Ambos</td>
	      <td><input type="radio" name="c_credito_contado" value="AMBOS" <?php if($c_credito_contado=="AMBOS"){echo "checked";}?>></td>
	      <td>Todos</td>
	      <td><input type="radio" name="c_estacion_oficina" value="TODOS" <?php if($c_estacion_oficina=="TODOS"){echo "checked";}?>></td>
	    </tr>
	    <tr>
	      <td>Credito</td>
	      <td><input type="radio" name="c_credito_contado" value="CREDITO" <?php if($c_credito_contado=="CREDITO"){echo "checked";}?>></td>
	      <td>Oficina</td>
	      <td><input type="radio" name="c_estacion_oficina" value="OFICINA" <?php if($c_estacion_oficina=="OFICINA"){echo "checked";}?>></td>
	    </tr>
	    <tr>
	      <td>Contado</td>
	      <td><input type="radio" name="c_credito_contado" value="CONTADO" <?php if($c_credito_contado=="CONTADO"){echo "checked";}?>></td>
	      <td>Estaciones</td>
	      <td><input type="radio" name="c_estacion_oficina" value="ESTACION" <?php if($c_estacion_oficina=="ESTACION"){echo "checked";}?>></td>
	    </tr>
	  </tbody>
	</table>
      <td rowspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td width="7%">&nbsp;</td>
    </tr>
    <tr> 
      <td height="28" colspan="3">&nbsp; </td>
      <td><input type="submit" name="btn_reporte" value="Reporte" onClick="javascript:mandarDatos(form1,'Reporte');"> 
        <input type="hidden" name="accion"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>


  <table width="99%" border="1">
    <tr> 
      <td width="6%"><strong>FECHA</strong></td>
      <td width="3%"><strong>TD</strong></td>
      <td width="11%"><strong>DOCUMENTO</strong></td>
      <td width="4%"><strong>RUC</strong></td>
      <td width="31%"><strong>RAZON SOCIAL</strong></td>
      
      <td width="13%"><strong>COD PRO</strong></td>
      <td width="13%"><strong>DESC</strong></td>
      
      <td width="7%"><strong>T.CAMB</strong></td>
      <td width="13%"><strong>CANTIDAD</strong></td>
      <td width="13%"><strong>PRECIO</strong></td>
      
      <td width="9%"><strong>VALOR NETO</strong></td>
      <td width="10%"><strong>IMPUESTOS</strong></td>
      <td width="6%"><strong>TOTAL VENTA</strong></td>
      <td width="13%"><strong># LIQUI</strong></td>
      
    </tr>
    <?php for($i=0;$i<pg_numrows($rs);$i++){
	$A = pg_fetch_array($rs,$i);
	$datos[]=$A;
	?>
    <tr> 
      <td><?php echo $A["fecha"];?></td>
      <td><?php echo $A["doc_sunat"];?></td>
      <td><?php echo $A["documento"];?></td>
      
      <td><?php echo $A["ruc"];?></td>
      <td><?php echo $A["razon_social"];?></td>
      <td><?php echo $A["art_codigo"];?></td>
      <td><?php echo $A["art_descripcion"];?></td>
      <td><?php echo $A["tipo_cambio"];?></td>
      <td><?php echo $A["nu_fac_cantidad"];?></td>
      <td><?php echo $A["nu_fac_precio"];?></td>
      
      <td><?php echo $A["valor_neto"];?></td>
      <td><?php echo $A["impuestos"];?></td>
      <td><?php echo $A["total_venta"];?></td>
      <td><?php echo $A["num_liqui"];?></td>
      

    </tr>
    <?php } ?>
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
    </tr>
  </table>
</form>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>

<?php 
	
	echo "<!--";
	print_r($datos);
	echo "-->";

  if($datos && $_REQUEST['c_fec_desde'])
  {
    $Fechas['DESDE'] = $_REQUEST['c_fec_desde'];
    $Fechas['HASTA'] = $_REQUEST['c_fec_hasta'];
    include_once('vta_reporte_registro_ventas_clientes_pdf.php');
    $reporte = VtaRegVentasClientesTemplate::ReportePDF($datos, $Fechas);
    echo "$reporte";
  }

pg_close();

