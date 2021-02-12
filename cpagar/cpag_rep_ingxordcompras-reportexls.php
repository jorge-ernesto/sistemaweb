<?php
include("config.php");
include("cpag_rep_ingxordcompras_support.php");
extract($_REQUEST);
extract($_POST);
extract($_GET);
	
	if($accion=="excel"){
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: filename=\"registro_compras.xls\"");//echo $accion;
	}
		switch($tipo_reporte){
			case "ord":
			$rs1 = reporteORD($opcion,$fechad,$fechaa,$cod_proveedor,$des_proveedor
			,$cod_articulo,$des_articulo,$opcion,$cod_almacen);
			break;
		
			case "dev":
			$rs1 = reporteDEV($opcion,$fechad,$fechaa,$cod_proveedor,$des_proveedor
			,$cod_articulo,$des_articulo,$opcion,$cod_almacen);
			break;
		}
	pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Exportar Reporte</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table width="742" border="0">
  <tr>
    <td width="370"><a href="cpag_rep_ingxordcompras-reportexls.php?tipo_reporte=<?php echo $tipo_reporte;?>&fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&opcion=<?php echo $opcion;?>&cod_proveedor=<?php echo $cod_proveedor;?>&des_proveedor=<?php echo $des_proveedor;?>&cod_articulo=<?php echo $cod_articulo;?>&des_articulo=<?php echo $des_articulo;?>&cod_almacen=<?php echo $codigo_almacen;?>&accion=excel">Exportar a Excel</a></td>
    <td width="362">Imprimir</td>
  </tr>
</table>
COMPRAS DEL REGISTRO DE COMPRAS DEL :<?php echo $fechad;?> al <?php echo $fechaa;?><br>
<br>
<?php if($tipo_reporte=="ord"){ ?>
<br>
<table width="767" border="1">
  <tr> 
    <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Fecha</font></div></td>
    <td width="55"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Orden 
        Compra</font></div></td>
    <td width="101"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Proveedor</font></div></td>
    <td width="53"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Docum. 
        Ref</font></div></td>
    <td width="43"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Almacen</font></div></td>
    <td width="99"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Articulo</font></div></td>
    <td width="48"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Cantidad</font></div></td>
    <td width="50"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Costo 
        Unitario</font></div></td>
    <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Costo 
        Total</font></div></td>
    <td width="64"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Nro. 
        <br>
        Movimiento</font></div></td>
    <td width="108"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Facturacion 
        Proveedor </font></div></td>
  </tr>
  <!-- <?php for($i=0;$i<pg_numrows($rs1);$i++){
  $A = pg_fetch_array($rs1,$i);
  	if($A[11]=="01/01/1841"){ $A[11]="";}
  ?> -->
  <tr> 
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[0];?></font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $A[1];?></font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $A[2];?> 
        <?php echo $A[14];?></font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[4];?>&nbsp;<?php echo $A[3];?></font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[5];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[6];?> 
        <?php echo $A[13];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[7];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[8];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[9];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[10];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[11];?> 
        <?php echo $A[12];?> &nbsp;</font></div></td>
  </tr>
  <!-- <?php } ?> -->
  <tr> 
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
  </tr>
</table>
<?php } ?>
<br>
<?php if($tipo_reporte=="dev"){ ?>
<br>
<table width="706" border="1">
  <tr> 
    <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Fecha</font></div></td>
    <td width="101"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Proveedor</font></div></td>
    <td width="53"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Docum. 
        Ref</font></div></td>
    <td width="43"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Almacen</font></div></td>
    <td width="99"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Articulo</font></div></td>
    <td width="48"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Cantidad</font></div></td>
    <td width="50"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Costo 
        Unitario</font></div></td>
    <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Costo 
        Total</font></div></td>
    <td width="64"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Nro. 
        <br>
        Movimiento</font></div></td>
    <td width="108"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Facturacion 
        Proveedor </font></div></td>
  </tr>
  <!-- <?php for($i=0;$i<pg_numrows($rs1);$i++){
  $A = pg_fetch_array($rs1,$i);
  ?> -->
  <tr> 
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[0];?></font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[1];?> 
        <br>
        <?php echo $A[13];?>&nbsp;</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[2];?><?php echo $A[3];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[4];?>&nbsp;</font></div></td>
    <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[5];?><br>
        <?php echo $A[12];?></font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[6];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[7];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[8];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[9];?>&nbsp;</font></div></td>
    <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[11];?>&nbsp;</font></div></td>
  </tr>
  <!-- <?php } ?> -->
  <tr> 
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
  </tr>
</table>
<?php } ?>
</body>
</html>
