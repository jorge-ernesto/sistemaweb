<?php
//include("../valida_sess.php");
//include("../config.php");
//include("store_procedures.php");
//include("inc_top.php");
include("../menu_princ.php");
$hoy = date("d/m/Y");
if($c_fec_desde==""){$c_fec_desde=$hoy;}
if($c_fec_hasta==""){$c_fec_hasta=$hoy;}

$col = 10;
$lin = str_repeat("=",240);
$lin1 = str_repeat("-",205);
$lin2 = str_repeat("=",205);
$salto = chr(12);
switch($accion){
	
	case "Reporte":	
		if($c_est==""){$c_est="TODOS";}
		if($c_cliente==""){$c_cliente="TODOS";}
		$rs = REPORTE_CONSUMO_PLACA($c_fec_desde,$c_fec_hasta,$c_est,$c_cliente);
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

$reporte_txt = "";
$cab = "Sucursal {sucursal} ".str_pad("CONSUMOS POR PLACA DE VEHICULO",180," ", STR_PAD_BOTH)." Pag. {pagina}\n";
//$cab = $cab.str_pad("Del $c_fec_desde AL $c_fec_hasta",220," ", STR_PAD_BOTH)."\n";
$cab = $cab.str_pad("Del $c_fec_desde AL $c_fec_hasta",130," ",STR_PAD_LEFT)."\n";
$cab = $cab."Usuario:{usuario} - Fecha: {fecha}\n";
$cab = $cab.$lin2."\n";
$cab = $cab."FECHA                  ESTACION              TRANSACCION         ODOMETRO                       CODIGO        TIPO DE PRODUCTO                            PRECIO UNIT.         CANT/GALO        IMPORTE\n";

$cab = $cab.$lin2."\n";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Reporte de Consumo por Placa</title>
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
      <td height="24" colspan="2">&nbsp;</td>
      <td colspan="3"><div align="center">CONSUMOS POR PLACA DE VEHICULOS</div></td>
      <td width="17%"> 
        <!--<a href="#" onClick="javascript:accion.value='Imprimir',form1.submit();">IMPRIMIR</a>-->
        <a href="#" onClick="javascript:window.open('generar_reporte_detalle_consumo_vales.php?c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&accion=<?php echo $accion;?>&c_opt_reporte=<?php echo $c_opt_reporte;?>&c_est=<?php echo $c_est;?>&c_cliente=<?php echo $c_cliente;?>&c_num_liqui=<?php echo $c_num_liqui;?>','winrep1','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">IMPRESION</a> 
      </td>
    </tr>
    <tr> 
      <td width="15%"><div align="right">Estacion : </div></td>
      <td width="7%"><input type="text" name="c_est" size="11" value="<?php echo $c_est;?>"></td>
      <td width="20%" rowspan="2">Desde: 
        <input type="text" name="c_fec_desde" size="11" value="<?php echo $c_fec_desde;?>"> <a href="javascript:show_calendar('form1.c_fec_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
      <td width="20%" rowspan="2">Hasta: 
        <input type="text" name="c_fec_hasta" size="11" value="<?php echo $c_fec_hasta;?>"> <a href="javascript:show_calendar('form1.c_fec_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
      <td width="29%" rowspan="2">&nbsp; </td>
      <td rowspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td><div align="right">Cliente: </div></td>
      <td width="7%"><input type="text" name="c_cliente" size="11" value="<?php echo $c_cliente;?>"></td>
    </tr>
    <tr> 
      <td height="28" colspan="3">&nbsp; </td>
      <td><input type="submit" name="btn_reporte" value="Reporte" onClick="javascript:mandarDatos(form1,'Reporte');"> 
        <input type="hidden" name="accion"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>

<table width="99%" border="1">
  <tr> 
    <td width="15%">Fecha</td>
    <td width="7%">Estacion</td>
    <td width="11%">Transaccion</td>
    <td width="8%">Odometro</td>
    <td width="6%">Codigo</td>
    <td width="22%">Tipo de Producto</td>
    <td width="9%">Precio Unit.</td>
    <td width="11%">Cant / Galo.</td>
    <td width="11%">Importe</td>
  </tr>
  <?php
  $DatosCab = array();
  //$DatosDet = array();
  $DatosFinal = array();
  $pla = "";
  $total_xplaca = 0 ;
  $cantidad_xplaca = 0;
  $num_vales_xcliente = 0;
  $num_vales_total = 0;
  $total_general = 0;
  $x=0;
  $c=0;
  for($i=0;$i<pg_numrows($rs);$i++){
  	$A = pg_fetch_array($rs,$i);
	
	$total_cantidad=$total_cantidad+$A["nu_cantidad"];
	$total_importe=$total_importe+$A["nu_importe"];
	$cli_total_cantidad=$cli_total_cantidad+$A["nu_cantidad"];
	$cli_total_importe=$cli_total_importe+$A["nu_importe"];
	$num_vales_xcliente++;
	$num_vales_total++;
	?>
  <?php if($pla!=$A["ch_placa"]){
          $DatosCab[$x]['CABECERA']['PLACA'] = $A["ch_placa"];
          $DatosCab[$x]['CABECERA']['COD CLIENTE'] = $A["ch_cliente"];
          $DatosCab[$x]['CABECERA']['DESC CLIENTE'] = $A["des_cliente"];
	  $pla = $A["ch_placa"];	
	  
	  $reporte_txt = $reporte_txt."Placa: ".$A["ch_placa"]." Cliente: ".$A["ch_cliente"]." - ".$A["des_cliente"];
	  $reporte_txt = $reporte_txt."\n";
  ?>
  <tr> 
    <td colspan="9">Placa : <?php echo $A["ch_placa"];?> Cliente : <?php echo $A["ch_cliente"];?> 
      - <?php echo $A["des_cliente"];?></td>
  </tr>
  <?php
  $x++;
  }
   $DatosCab[$x]['DETALLE']['FECHA']            = $A["dt_fecha"];
   $DatosCab[$x]['DETALLE']['ESTACION']         = $A["des_sucursal"];
   $DatosCab[$x]['DETALLE']['TRANSACCION']      = $A["ch_documento"];
   $DatosCab[$x]['DETALLE']['ODOMETRO']         = $A["nu_odometro"];
   $DatosCab[$x]['DETALLE']['CODIGO']           = $A["ch_articulo"];
   $DatosCab[$x]['DETALLE']['TIPO DE PRODUCTO'] = $A["art_descripcion"];
   $DatosCab[$x]['DETALLE']['PRECIO UNITARIO']  = $A["precio"];
   $DatosCab[$x]['DETALLE']['CANT GALON']       = $A["nu_cantidad"];
   $DatosCab[$x]['DETALLE']['IMPORTE']          = $A["nu_importe"];
  ?>
  <tr> 
  	<?php $reporte_txt = $reporte_txt.$A["dt_fecha"];
	  $reporte_txt = $reporte_txt.str_pad($A["des_sucursal"],23," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["ch_documento"],23," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["nu_odometro"],16," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["ch_articulo"],22," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad(trim($A["art_descripcion"]),32," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["precio"],33," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["nu_cantidad"],20," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt.str_pad($A["nu_importe"],20," ",STR_PAD_LEFT);
	  $reporte_txt = $reporte_txt."\n";
	?>
  
    <td>&nbsp;<?php echo $A["dt_fecha"];?></td>
    <td><?php echo $A["des_sucursal"];?></td>
    <td><?php echo $A["ch_documento"];?></td>
    <td><?php echo $A["nu_odometro"];?></td>
    <td><?php echo $A["ch_articulo"];?></td>
    <td><?php echo $A["art_descripcion"];?></td>
    <td><?php echo $A["precio"];?></td>
    <td><?php echo $A["nu_cantidad"];?></td>
    <td><?php echo $A["nu_importe"];?> </td>
  </tr>
  
  <?php if($A["ch_placa"]!=@pg_result($rs,$i+1,"ch_placa")){?><tr> 
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td><?php $reporte_txt = $reporte_txt.str_pad("Total por placa ".$A["ch_placa"]." ->",150," ",STR_PAD_LEFT);
					 $reporte_txt = $reporte_txt.str_pad(number_format($total_cantidad, 2, '.', ''),29," ",STR_PAD_LEFT);
					 $reporte_txt = $reporte_txt.str_pad(number_format($total_importe, 2, '.', ''),20," ",STR_PAD_LEFT);
					 $reporte_txt = $reporte_txt."\n";
    //$x= $x+1;
    $DatosCab[$x+1]['TOTALES PLACA']['TOT PLACA'] = $A["ch_placa"];
    $DatosCab[$x+1]['TOTALES PLACA']['TOTAL CANTIDAD'] = $total_cantidad;
    $DatosCab[$x+1]['TOTALES PLACA']['TOTAL IMPORTE'] = $total_importe;

	?>
    <td colspan="2"><div align="right">Total por placa <?php echo $A["ch_placa"];?> 
        -&gt;</div></td>
    <td><?php echo $total_cantidad;?></td>
    <td><?php echo $total_importe;?></td>
  </tr><?php $total_cantidad=0;
  		$total_importe=0;
  $x++;
  }?>
  
    <?php if($A["ch_cliente"]!=@pg_result($rs,$i+1,"ch_cliente")){?><tr> 
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td><?php $reporte_txt = $reporte_txt.str_pad("TOTAL CLIENTE ".$A["ch_cliente"]." -> ".$num_vales_xcliente." vales",158," ",STR_PAD_LEFT);
					 $reporte_txt = $reporte_txt."\n\n";
    //$x= $x+1;
    $DatosCab[$x+1]['TOTALES CLIENTE']['TOT CLIENTE'] = $A["ch_cliente"]."->(".$num_vales_xcliente." VALES)";
    $DatosCab[$x+1]['TOTALES CLIENTE']['TOTAL CANTIDAD'] = $cli_total_cantidad;
    $DatosCab[$x+1]['TOTALES CLIENTE']['TOTAL IMPORTE'] = $cli_total_importe;
    ?>
    <td colspan="2"><div align="left">TOTAL CLIENTE <?php echo $A["ch_cliente"];?>-&gt;( 
        <?php echo $num_vales_xcliente;?> vales )</div></td>
    <td><?php echo $cli_total_cantidad;?></td>
    <td><?php echo $cli_total_importe;?></td>
  </tr><?php $total_general = $total_general + $cli_total_importe;
  		$cli_total_cantidad=0;
  		$cli_total_importe=0;
		$num_vales_xcliente = 0;
      $x++;
      }?>
  <?php
  
  $x++;
  }
  //$DatosCab['DETALLE'] = $DatosDet;

  ?>
  
  <tr> 
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td><?php $reporte_txt = $reporte_txt.str_pad("TOTAL GENERAL -> ".$num_vales_total." vales",146," ",STR_PAD_LEFT);
					 $reporte_txt = $reporte_txt.str_pad($total_general,33," ",STR_PAD_LEFT);
					 $reporte_txt = $reporte_txt."\n";
	?>
    <td colspan="2"><div align="left">TOTAL GENERAL -&gt;( <?php echo $num_vales_total;?> 
        vales )</div></td>
    <td>&nbsp;</td>
    <td><?php echo $total_general;?></td>
  </tr>
  
</table>
<p>&nbsp;</p>
</body>
</html>

<?php
  $DatosCab['TOT GEN']['VALES'] = "-> ( ".$num_vales_total." VALES) ";
  $DatosCab['TOT GEN']['TOTAL'] = $total_general;
  echo "<!--";
  //print_r($DatosCab);
  echo "-->";
  if($DatosCab && $_REQUEST['c_fec_desde'])
  {
    $Fechas['DESDE'] = $_REQUEST['c_fec_desde'];
    $Fechas['HASTA'] = $_REQUEST['c_fec_hasta'];
    include_once('vta_consumo_xplaca_reporte.php');
    $reporte = VtaConsumoxPlacaTemplate::ReportePDF($DatosCab, $Fechas);
    echo "$reporte";
  }
  
 pg_close();
	
	$reporte_txt=$cab.$reporte_txt;
	$man_arch = fopen("/sistemaweb/tmp/vtarep_consumo_vales.txt","w");
	fwrite($man_arch,$reporte_txt);
    fclose($man_arch);
		
