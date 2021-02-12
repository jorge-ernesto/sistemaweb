<?php
include("../valida_sess.php");
//include("../menu_princ.php");
include("../config.php");
include("../functions.php");

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
    $por_igv = pg_result(pg_exec("select util_fn_igv()"),0,0);
    
    $q_cab = "SELECT to_char(cab.pro_cab_fecharegistro,'dd/mm/yyyy') as pro_cab_fecharegistro,".
		"cab.pro_cab_tipdocumento||' - '||cab.pro_cab_seriedocumento||cab.pro_cab_numdocumento as docint, ".
		"cab.pro_cab_numreg, ".
		"cab.pro_cab_rubrodoc as pro_cab_rubrodoc, ".
		"cab.pro_codigo, ".
		"cab.pro_cab_impafecto as cab_valor, ".
		"cab.pro_cab_impto1 as cab_impuesto, ".
		"cab.pro_cab_imptotal as cab_total, ".
		"cab.pro_cab_almacen as pro_cab_almacen, ".
		"to_char(cab.pro_cab_fechaemision,'dd/mm/yyyy') as pro_cab_fechaemision, ".
		"prov.pro_rsocialbreve as prov, ".
		"alma.ch_nombre_almacen as ch_nombre_almacen, ".
		"tab.tab_desc_breve as mone, ".
		"cab.pro_cab_tcambio ".
	    "FROM cpag_ta_cabecera cab, ".
		"int_proveedores prov, ".
		"inv_ta_almacenes alma, ".
		"int_tabla_general tab ".
	    "WHERE cab.pro_codigo = prov.pro_codigo ".
	    "AND cab.pro_cab_tipdocumento = '$c_tipo' ".
	    "AND cab.pro_cab_seriedocumento = '$c_serie' ".
	    "AND cab.pro_cab_numdocumento = '$c_numero' ".
	    "AND cab.pro_codigo = '$c_proveedor' ".
	    "AND alma.ch_almacen=cab.pro_cab_almacen ".
	    "AND tab.tab_tabla ='04' and tab.tab_elemento<>'000000' ".
	    "AND tab.tab_elemento=lpad(cab.pro_cab_moneda,6,'0')";

    $rs_cab = pg_exec($q_cab);
    $CAB = pg_fetch_array($rs_cab,0);
    //print_r($CAB);
    $cab_almacen = $CAB["pro_cab_almacen"];
	
    $q_comp_dev = "SELECT cd.tran_codigo, ".
                          "cd.mov_almacen,".
                          "cd.mov_numero, ".
                          "cd.mov_fecha, ".
                          "cd.art_codigo, ".
                          "cd.mov_naturaleza, ".
                          "cd.com_tipo_compra, ".
                          "cd.com_serie_compra, ".
                          "cd.com_num_compra, ".
                          "cd.cpag_tipo_pago, ".
                          "cd.cpag_serie_pago, ".
                          "cd.cpag_num_pago, ".
                          "cd.mov_tipdocuref, ".
                          "cd.mov_docurefe, ".
                          "cd.mov_tipoentidad, ".
                          "cd.mov_entidad, ".
                          "cd.mov_cantidad, ".
                          "cd.mov_costounitario, ".
                          "round(cd.mov_costototal*(1+($por_igv/100))) as mov_costototal, ".
                          "cd.com_det_estado, ".
                          "cd.mov_fecha_actualizacion, ".
                          "cd.ip_addr,cd.mov_docurefe2, ".
                          "to_char(cd.mov_fecha,'dd/mm/yyyy') as mov_fecha_det, ".
                          "art.art_descripcion, ".
                          "substr(art.art_unidad, 4, 3) as art_unidad, ".
                          "util_fn_saldoalmacen(cd.art_codigo,cd.mov_almacen)  as stock, ".
                          "art.art_impuesto1 as impuesto,  ".
                          "fl.pre_precio_act1 ".
                  "FROM inv_ta_compras_devoluciones cd, ".
                       "int_articulos art, ".
                       "fac_lista_precios fl ".
                  "WHERE cpag_tipo_pago='$c_tipo' ".
                  "AND cpag_serie_pago='$c_serie' ". 
                  "AND cpag_num_pago='$c_numero' ".
                  "AND mov_entidad='$c_proveedor' ".
                  "AND art.art_codigo=cd.art_codigo ".
                  "AND art.art_codigo=fl.art_codigo ".
                  "AND pre_lista_precio='01' ".
                  "ORDER BY cd.cpag_tipo_pago, ".
                           "cd.mov_numero";
    //echo "<!-- QUERY : $q_comp_dev -->\n";
    $rs_det = pg_exec($q_comp_dev);
    
    $BUF = "";
    $txt = "cintillo_liq_compras.txt";
    exec("echo -e '$BUF' > /tmp/$txt");
    
    if($accion=="Imprimir")
    {
		
		$rs = pg_exec("select par_valor as print_server from int_parametros
	where par_nombre ='print_server' ");
	$A = pg_fetch_array($rs,0);
	$print_server =  $A["print_server"];
    
	$rs = pg_exec("select par_valor as print_netbios from int_parametros
	where par_nombre ='print_netbios' ");
	$A = pg_fetch_array($rs,0);
	$print_netbios =  $A["print_netbios"];
    
		$rs = pg_exec("select par_valor as print_name from int_parametros
	where par_nombre ='print_name' ");
	$A = pg_fetch_array($rs,0);
	$print_name =  $A["print_name"];
    
		exec("smbclient //".$print_netbios."/".$print_name." -c 'print /tmp/".$txt."' -P -N -I ".$print_server." ");
	pg_close();
    
	print   "<script>window.close();</script>";

    }

?>

<html>
<head>
<title>REPORTE - CUENTAS POR PAGAR</title>
<script>
	function imprimirCintillo(form){
		form.accion.value="Imprimir";
		form.submit();
	}
</script>
</head>
<body><link rel="stylesheet" href="js/style.css" type="text/css">
<form name="form1" method="post"> 
<?php
$DatosReportePdf = array();
$DatosReportePdf['ALMACEN'] = $cab_almacen." - ".otorgarAlmacen($conector_id, $cab_almacen);
$DatosReportePdf['CABECERA']['FECHA']           = $CAB["pro_cab_fecharegistro"];
$DatosReportePdf['CABECERA']['PROVEEDOR']       = trim($CAB["pro_codigo"])." - ".$CAB["prov"];
$DatosReportePdf['CABECERA']['ALMACEN']         = $CAB["ch_nombre_almacen"];
$DatosReportePdf['CABECERA']['DOC.INT']         = $CAB["docint"];
$DatosReportePdf['CABECERA']['NRO REGISTRO']    = $CAB["pro_cab_numreg"];

$DatosReportePdf['CABECERA DATOS']['RUBRO']     = $CAB["pro_cab_rubrodoc"];
$DatosReportePdf['CABECERA DATOS']['EMISION']   = $CAB["pro_cab_fechaemision"];
$DatosReportePdf['CABECERA DATOS']['MONEDA']    = $CAB["mone"];
$DatosReportePdf['CABECERA DATOS']['TIPO CAMBIO']    = $CAB["pro_cab_tcambio"];
$DatosReportePdf['CABECERA DATOS']['VALOR']     = $CAB["cab_valor"];
$DatosReportePdf['CABECERA DATOS']['IMPUESTOS'] = $CAB["cab_impuesto"];
$DatosReportePdf['CABECERA DATOS']['TOTAL']     = $CAB["cab_total"];



?>
<table border="1" cellpadding="0" cellspacing="0">
  <tr class="letra_titulo"> 
    <td align="left" colspan="4">ALMACEN : <?php echo $cab_almacen." - ".otorgarAlmacen($conector_id, $cab_almacen);?>
        <input type="hidden" name="accion"></td>
    <?php $BUF = $cab_almacen." - ".otorgarAlmacen($conector_id, $cab_almacen)."\n";?>
	<th colspan="2" align="right"> <a href="#" onClick="javascript:imprimirCintillo(form1);">Imprimir 
      Texto</a> 
  <tr class="letra_titulo"> 
    <td align="center" colspan="5">CINTILLO DE LIQUIDACION DE COMPRAS</td>
	<?php $BUF = $BUF.str_pad("CINTILLO DE LIQUIDACION DE COMPRAS        $fecha_actual\n\n",80," ",STR_PAD_LEFT);?>
    <td align="center"><?php echo $fecha_actual;?></td>
  <tr class="letra_titulo"> 
    <td colspan="6">&nbsp; </td>
  <tr class="letra_titulo"> 
    <td width="84">Fecha:&nbsp;<?php echo $CAB["pro_cab_fecharegistro"];?></td>
    <td width="165">Proveedor:&nbsp;<?php echo $CAB["pro_codigo"];?>-<?php echo $CAB["prov"];?></td>
    <td width="168">Almacen: &nbsp;<?php echo $CAB["ch_nombre_almacen"];?></td>
    <td width="112">Doc-Int:&nbsp;<?php echo $CAB["docint"];?></td>
    <td colspan="2">Nro. Registro&nbsp;<?php echo $CAB["pro_cab_numreg"];?></td>
	
	<?php $BUF = $BUF.str_pad("FECHA: ".$CAB["pro_cab_fecharegistro"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" PROVEEDOR: ".$CAB["pro_codigo"]." ".$CAB["prov"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" ALMACEN: ".trim($CAB["ch_nombre_almacen"]), 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" DOC.INT: ".$CAB["docint"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" Nro. REGISTRO: ".$CAB["pro_cab_numreg"]."\n", 10, " ", STR_PAD_RIGHT);?>
	
  <tr class="letra_titulo"> 
    <td>Rubro:&nbsp;<?php echo $CAB["pro_cab_rubrodoc"];?></td>
    <td>Emision:&nbsp;<?php echo $CAB["pro_cab_fechaemision"];?></td>
    <td>Moneda:&nbsp;<?php echo $CAB["mone"];?></td>
    <td>Valor:&nbsp;<?php echo $CAB["cab_valor"];?></td>
    <td width="69">Impuestos:&nbsp;<?php echo $CAB["cab_impuesto"];?></td>
    <td width="154">Total:&nbsp;<?php echo $CAB["cab_total"];?></td>
	<?php $CAB["mone"] = "S/.";?>
	<?php $BUF = $BUF.str_pad("RUBRO: ".trim($CAB["pro_cab_rubrodoc"]), 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" EMISION: ".$CAB["pro_cab_fechaemision"]." ".$CAB["prov"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" MONEDA: ".$CAB["mone"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" VALOR: ".$CAB["cab_valor"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" IMPUESTOS: ".$CAB["cab_impuesto"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" TOTAL: ".$CAB["cab_total"]."\n", 10, " ", STR_PAD_RIGHT);?>
	
	<?php $BUF = $BUF.str_pad("\n",132, "=", STR_PAD_LEFT);?>
</table>
<br>
<table border="0" cellspacing="1" cellpadding="1" bgcolor="#BBBBBB">
  <tr class="letra_cabecera"> 
    <td width="49">Codigo Articulo</td>
    <td width="255">Descripcion</td>
    <td width="83">Nro. O/C</td>
    <td width="34">UNI</td>
    <td width="100">Cantidad</td>
    <td width="73">Costo Total</td>
    <td width="61">Costo Unit con IGV</td>
    <td width="79">Stock</td>
	
	<?php $BUF = $BUF.str_pad("CODIGO", 15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad("DESCRPCION", 40, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad("N. O/C", 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad("UNI", 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad("CANTIDAD", 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad("COSTO TOTAL", 15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad("COSTO UNI", 15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad("STOCK\n", 5, " ", STR_PAD_RIGHT);?> 
	
	<?php $BUF = $BUF.str_pad("\n\n",132, "=", STR_PAD_LEFT);?>
  </tr>
  <?php
  $mov_numero_act = "";
  $Detalle = array();
  for($i=0;$i<pg_numrows($rs_det);$i++){ 
  	$DET = pg_fetch_array($rs_det,$i);
	
	
	if($mov_numero_act!=$DET["mov_numero"]){
		$mov_numero_act=$DET["mov_numero"];
  ?>
  <tr class="letra_detalle"> 
    <td colspan="2">Form. Compra <?php echo $DET["mov_numero"];?></td>
    <td colspan="2">Orden Compra <?php echo $DET["com_num_compra"];?></td>
    <td>Doc.Ext <?php echo $DET["mov_docurefe"];?></td>
    <td colspan="2">Fecha: <?php echo $DET["mov_fecha_det"];?></td>
    <td>&nbsp;</td>
	<?php
	$Detalle['CAB '.$i]['FORMULARIO COMPRA']   = $DET["mov_numero"];
	$Detalle['CAB '.$i]['ORDEN COMPRA']        = $DET["com_num_compra"];
	$Detalle['CAB '.$i]['DOC EXT']             = $DET["mov_docurefe"];
	$Detalle['CAB '.$i]['FECHA']               = $DET["mov_fecha_det"];
	
	?>
	<?php $BUF = $BUF.str_pad("Formulario Compra:".$DET["mov_numero"],15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" Orden Compra:".$DET["com_num_compra"],15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" DOC.EXT: ".$DET["mov_docurefe"],15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(" FECHA: ".$DET["mov_fecha_det"]."\n",15, " ", STR_PAD_RIGHT);?>
	
	<?php $i++; } ?>
  </tr>
  
  <tr class="letra_detalle"> 
    <td><?php echo $DET["art_codigo"];?></td>
    <td><?php echo $DET["art_descripcion"];?></td>
    <td><?php echo $DET["com_num_compra"];?></td>
    <td><?php echo $DET["art_unidad"];?></td>
    <td align="right"><?php echo round($DET["mov_cantidad"]);?></td>
    <td align="right"><?php echo $CAB["mone"]." ".money_format("%.2n", $DET["mov_costototal"]);?></td>
    <td align="right"><?php echo $DET["mov_costounitario"];?></td>
    <td align="right"><?php echo round($DET["stock"]);?></td>
<?php
	$Detalle[$i]['ART CODIGO']     = $DET["art_codigo"];
	$Detalle[$i]['ART DESCRIPCION']= $DET["art_descripcion"];
	$Detalle[$i]['NUM COMPRA']     = $DET["com_num_compra"];
	$Detalle[$i]['ART UNIDAD']     = $DET["art_unidad"];
	$Detalle[$i]['CANTIDAD']       = $DET["mov_cantidad"];
	$Detalle[$i]['MONEDA']         = $CAB["mone"];
	$Detalle[$i]['COSTO TOTAL']    = $DET["mov_costototal"];
	$Detalle[$i]['COSTO UNITARIO'] = $DET["mov_costounitario"];
	$Detalle[$i]['PRECIO UNITARIO'] = $DET["pre_precio_act1"];
	$Detalle[$i]['STOCK']          = $DET["stock"];

?>
	<?php $BUF = $BUF.str_pad($DET["art_codigo"], 15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad(trim($DET["art_descripcion"]), 40, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad($DET["com_num_compra"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad($DET["art_unidad"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad($DET["mov_cantidad"], 10, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad($CAB["mone"]." ".$DET["mov_costototal"], 15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad($DET["mov_costounitario"], 15, " ", STR_PAD_RIGHT);?>
	<?php $BUF = $BUF.str_pad($DET["stock"]."\n", 5, " ", STR_PAD_RIGHT);?> 
  </tr>
  <?php 
  $costo_total = $costo_total + $DET["mov_costototal"];
    $costo_total = money_format("%.2n", $costo_total);
  } 
  $DatosReportePdf['DETALLE'] = $Detalle;
//print_r($DatosReportePdf);
  if($DatosReportePdf)
  {
    $Fechas['DESDE'] = $_REQUEST['v_fecha_desde'];
    $Fechas['HASTA'] = $_REQUEST['v_fecha_hasta'];
    include_once('cpag_cintillo_liq_compras_pdf.php');
    $reporte = CintilloLiqComprasTemplate::ReportePDF($DatosReportePdf, $Fechas);
    echo "$reporte";

  }
  ?>
  <tr class="letra_detalle"> 
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td align="right">
		<?php echo $CAB["mone"]." ".$costo_total;?>
		<?php $BUF = $BUF.str_pad("-----------\n", 85, " ", STR_PAD_LEFT);?>
		<?php $BUF = $BUF.str_pad("$costo_total\n\n\n\n", 85, " ", STR_PAD_LEFT);?>
		<?php $BUF = $BUF.str_pad("===========\n", 85, " ", STR_PAD_LEFT);?>
		<?php $BUF = $BUF.str_pad("$costo_total\n", 82, " ", STR_PAD_LEFT);?>
		
		<?php $BUF = $BUF.str_pad("\n",132, "-", STR_PAD_LEFT);?>
	</td>
    <td></td>
    <td></td>
  </tr>
 
</table>
 </form>
<?php

exec("echo -e '$BUF' > /tmp/$txt");
?>
</html>
