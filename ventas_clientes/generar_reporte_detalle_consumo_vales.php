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
$bordes = 0;
switch($accion){
	
	case "Reporte":	
		
		if($c_est==""){$c_est="TODAS";}
		if($c_cliente==""){$c_cliente="TODOS";}
		if($c_num_liqui==""){$c_num_liqui="TODOS";}
		//echo $c_num_liqui;
		$rs = REPORTE_DETALLE_CONSUMO_VALES($c_est, $c_fec_desde,$c_fec_hasta,$c_cliente,$c_num_liqui);
		
	break;

	case "Excel":
	
		header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=reporte_detalle_consumo_vales.xls");
		if($c_est==""){$c_est="TODAS";}
		if($c_cliente==""){$c_cliente="TODOS";}
		if($c_num_liqui==""){$c_num_liqui="TODOS";}
		$rs = REPORTE_DETALLE_CONSUMO_VALES($c_est, $c_fec_desde,$c_fec_hasta,$c_cliente,$c_num_liqui);
		$bordes = 1;
	
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

<html>
<head>
<link rel="stylesheet" href="../cpagar/js/style.css" type="text/css" media="screen"/>
<link rel="stylesheet" href="/sistemaweb/cpagar/js/print.css" type="text/css" media="print"/>

<title>DETALLE DE CONSUMO DE VALES</title>
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
      <th width="378" align="right">&nbsp; 
      <th><a href="generar_reporte_detalle_consumo_vales.php?accion=Excel&c_est=<?php echo $c_est;?>&c_fec_desde=<?php echo $c_fec_desde;?>&c_fec_hasta=<?php echo $c_fec_hasta;?>&c_cliente=<?php echo $c_cliente;?>&c_num_liqui=<?php echo $c_num_liqui;?>"><font size="2">EXCEL</font></a></th>
    <tr class="letra_titulo"> 
      <td align="center" colspan="2">&nbsp;</td>
      <td align="center">DETALLE DE CONSUMO DE VALES</td>
      <?php $BUF = $BUF.str_pad("CINTILLO DE LIQUIDACION DE COMPRAS        $fecha_actual\n\n",80," ",STR_PAD_LEFT);?>
      <td width="166" align="center"><?php echo $fecha_actual;?></td>
    <tr class="letra_titulo"> 
      <td width="201">&nbsp; </td>
      <td width="19">&nbsp;</td>
      <td><div align="center">Desde:<?php echo $c_fec_desde;?>: Hasta:<?php echo $c_fec_hasta;?></div></td>
      <td>&nbsp;</td>
  </table>
<br>
  <?php if($accion=="Excel"){$bg="#FFFFFF";}else{$bg="#BBBBBB";}
	?>
  <table border="<?php echo $bordes;?>" cellspacing="1" cellpadding="2" width="100%">
    <tr class="letra_cabecera"> 
      <td width="21%">DESCRIPCION</td>
      <td width="7%">#LIQ</td>
      <td width="17%"># DESPACHO</td>
      <td width="11%">FECHA</td>
      
      <td width="17%">NUMERACION DE VALES</td>
      
      <td width="5%">PLACA</td>
      <td width="5%">COMBUSTIBLE</td>
      <td width="5%">CANTIDAD</td>
      <td width="5%">PRECIO</td>
      <td width="9%">TOTAL</td>
    </tr>
    <?php $cli = "";
    $total_cliente=0;
	$total_general=0;
  for($i=0;$i<pg_numrows($rs);$i++){
  	$A = pg_fetch_array($rs,$i);
	?>
    <?php if($cli!=$A["c_ch_cliente"]){
	  $cli = $A["c_ch_cliente"];
	  $total_cliente = 0;	
  ?>
    <tr class="letra_detalle"> 
      <td colspan="7">Cliente : <?php echo $A["c_ch_cliente"]." ".$A["c_cli_razsocial"];?></td>
    </tr>
    <?php } ?>
    <tr class="letra_detalle"> 
      <td>&nbsp;<?php echo $A["c_ch_sucursal"]." - ".$A["c_des_sucursal"];?></td>
      <td><?php echo $A["c_ch_liquidacion"];?></td>
      <td><?php echo $A["c_ch_documento"];?></td>
      <td><?php echo $A["c_dt_fecha"];?></td>
      
      <td><?php echo $A["c_vales"];?></td>
      
      <td><?php echo $A["c_ch_placa"];?></td>
      <td><?php echo $A["c_ch_articulo"];?></td>
      <td><?php echo $A["c_cantidad"];?></td>
      <td><?php echo $A["c_precio"];?></td>
      <td><?php echo $A["c_nu_importe"];?></td>
    </tr>
	<?php $total_cliente=$total_cliente+$A["c_nu_importe"];?>
	<?php if(pg_result($rs,$i+1,"c_ch_cliente")!=$A["c_ch_cliente"]){?>
  <tr class="letra_cabecera"> 
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp; </td>
    <td>&nbsp; </td>
    <td>&nbsp;TOTAL</td>
    <td><?php $total_general = $total_general + $total_cliente;
    echo $total_cliente;?></td>
  </tr>
  <?php } ?>
  <?php } ?>
  <tr class="letra_cabecera"> 
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp; </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td colspan="2">&nbsp;TOTAL GENERAL</td>
    <td><?php echo $total_general;?></td>
    
  </tr>
    
  </table>
</form>
<?php pg_close();?>
</html>
