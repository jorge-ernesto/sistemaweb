<?php
include("../valida_sess.php");
include("../config.php");
include("store_procedures.php");
include("inc_top.php");
//include("../menu_princ.php");
$hoy = date("d/m/Y");
if($c_fec_desde==""){$c_fec_desde=$hoy;}
if($c_fec_hasta==""){$c_fec_hasta=$hoy;}

$col = 10;
$lin = str_repeat("=",240);
$salto = chr(12);
switch($accion){
	
	case "Reporte":	
		
		$rs_todas =  REPORTE_CUADRE_FACTURACION_1($c_fec_desde,$c_fec_hasta,"TODAS");
		$rs_anticipados =  REPORTE_CUADRE_FACTURACION_1($c_fec_desde,$c_fec_hasta,"ANTICIPADOS");
		$rs_facturas_contado =  REPORTE_CUADRE_FACTURACION_1($c_fec_desde,$c_fec_hasta,"FACTURAS_CONTADO");
		$rs_vales_no_facturados =  REPORTE_CUADRE_FACTURACION_1($c_fec_desde,$c_fec_hasta,"VALES_NO_FACTURADOS");
		$rs_vales_liquidados_no_facturados =  REPORTE_CUADRE_FACTURACION_1($c_fec_desde,$c_fec_hasta,"VALES_LIQUIDADOS_NO_FACTURADOS");
		$rs_vales_pago_adelantado =  REPORTE_CUADRE_FACTURACION_1($c_fec_desde,$c_fec_hasta,"VALES_PAGO_ADELANTADO");
		$rs_cuenta_mantenimiento =  REPORTE_CUADRE_FACTURACION_1($c_fec_desde,$c_fec_hasta,"CUENTA_MANTENIMIENTO");

		$rs_ventas_credito_det =  REPORTE_CUADRE_FACTURACION_2($c_fec_desde,$c_fec_hasta,"DETALLADO");
		$rs_ventas_credito_res =  pg_exec("SELECT
          round(SUM(importe_castilla),2)     as importe_castilla
         , round(SUM(importe_magdalena),2)   as importe_magdalena
         , round(SUM(importe_faucet),2)      as importe_faucet
         , round(SUM(importe_brena),2)       as importe_brena
         , round(SUM(importe_perla),2)       as importe_perla
         , round(SUM(importe_sucre),2)       as importe_sucre
         , round(SUM(importe_risso),2)       as importe_risso
         , round(SUM(importe_orrantia),2)    as importe_orrantia
         FROM TMP_VENTAS_FN_REPORTE_CUADRE_FACTURACION_22 ");
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
</head>

<body>
<form name="form1" action="generar_reporte_cuadre_facturacion_2.php" target="_blank" method="post">
  <table width="774" border="0" cellpadding="1" cellspacing="1">
    <tr> 
      <td width="22%" height="24">&nbsp;</td>
      <td colspan="3"><div align="center">REPORTE DIARIO DE CONSISTENCIA DE VALES 
          x CENTRO DE COSTO</div></td>
      <td width="17%">&nbsp;
      </td>
    </tr>
    <tr> 
      <td><div align="right"></div></td>
      <td width="16%" rowspan="2">Desde: 
        <input type="text" name="c_fec_desde" size="11" value="<?php echo $c_fec_desde;?>"></td>
      <td width="16%" rowspan="2">Hasta: 
        <input type="text" name="c_fec_hasta" size="11" value="<?php echo $c_fec_hasta;?>"></td>
      <td width="29%" rowspan="2">&nbsp; </td>
      <td rowspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td><div align="right"></div></td>
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
</form>

</body>
</html>

<?php pg_close();
	//$ar = file("/tmp/reporte_ventas_diarias.txt");
	//echo count($ar);
	//echo $BUF;	
