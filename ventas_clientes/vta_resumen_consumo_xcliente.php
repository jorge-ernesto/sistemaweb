<?php
//include("../valida_sess.php");
//session_start();
//include("store_procedures.php");
include "../menu_princ.php";
include("../clases/funciones.php");
//include("inc_top.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

$COMP = $_SESSION["ARR_COMP"];
//echo $cod_cliente."-".$accion."-";
switch($accion){

	case "Reporte":
		$rs = REPORTE_RESUMEN_CONSUMOS_XCLIENTE($c_fec_desde,$c_fec_hasta,$tablas,$opcion1,$xgrupo);
		$total = 0;

		for ($i = 0; $i < pg_numrows($rs); $i++) {
    			$A = pg_fetch_array($rs, $i);
    			$total += $A['imptotal'];
    			$totales[$i] = $A['imptotal'];
		}

		for ($i = 0; $i < pg_numrows($rs); $i++) {
    			$porcentajes[$i] = (($totales[$i]/$total)*100) . "%";
		}
	break;
}
if (!$_REQUEST['c_fec_desde']) {
	$c_fec_desde = date('d/m/Y');
	$c_fec_hasta = date('d/m/Y');
}
//echo $campo;
?>

<script language="JavaScript">
function enviarDatos(form,tipo){
		form.accion.value=tipo;
		form.submit();
}
function imprimir() {
    window.open("reportes/pdf/resumen_consumo_x_cliente.pdf", "ventana_imprimir");
}
</script>

<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="form1" method="post" action="">
  RESUMEN DE CONSUMOS POR CLIENTES DEL AL 
  <table width="91%" border="0">
    <tr> 
      <td width="14%" height="22">&nbsp;</td>
      <td width="10%">DESDE:</td>
      <td width="18%"><input type="text" name="c_fec_desde" value="<?php echo $c_fec_desde;?>" size="12"> <a href="javascript:show_calendar('form1.c_fec_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
      <td width="10%"> HASTA :</td>
      <td width="18%"><input type="text" name="c_fec_hasta" value="<?php echo $c_fec_hasta;?>" size="12"> <a href="javascript:show_calendar('form1.c_fec_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" ><img src="/sistemaweb/images/showcalendar.gif"  border=0></a></td>
      <td width="27%">&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>SELECCIONAR</td>
      <td colspan="3">FACTURAS 
        <input type="radio" name="tablas" value="FACTURAS" <?php if($tablas=="FACTURAS"){echo "checked";}?>>
        - VALES 
        <input type="radio" name="tablas" value="VALES" <?php if($tablas=="VALES"){echo "checked";}?>></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>MOSTRAR</td>
      <td colspan="3">IMPORTES <input type="radio" name="campo" value="imp" <?php if($campo=="imp"){echo "checked";}?>>
        - GALONES <input type="radio" name="campo" value="can" <?php if($campo=="can"){echo "checked";}?>></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td height="59">&nbsp;</td>
      <td>CONDICION</td>
      <td colspan="3"><input type="radio" name="opcion1" value="TODOS" <?php if($opcion1=="TODOS"){echo "checked";}?>>
        TODOS <br> <input type="radio" name="opcion1" value="CREDITO" <?php if($opcion1=="CREDITO"){echo "checked";}?>>
        CREDITO <br> <input type="radio" name="opcion1" value="CONTADO" <?php if($opcion1=="CONTADO"){echo "checked";}?>>
        CONTADO <br> <input type="radio" name="opcion1" value="ANTICIPADO" <?php if($opcion1=="ANTICIPADO"){echo "checked";}?>>
        ANTICIPADO </td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="4">POR GRUPO EMPRESARIAL 
        <input type="radio" name="xgrupo" value="XGRUPO"></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="4"><div align="center"> 
          <input type="button" name="btn_terminar" value="Reporte" onClick="javascript:enviarDatos(form1,'Reporte')">
          <input type="hidden" name="accion">
        </div></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <br>
  <table width="100%" border="1">
    <tr> 
      <td width="20%">CLIENTE</td>
      <td width="8%">VENTA_84</td>
      <td width="8%">VENTA_90</td>
      <td width="8%">VENTA_95</td>
      <td width="8%">VENTA_97</td>
      <td width="8%">VENTA_D2</td>
      <td width="6%">VENTA_KD</td>
      <td width="11%">SUB-TOTAL</td>
      <td width="10%">VENTA_GLP</td>
      <td width="6%">TOTAL</td>
      <td width="7%">(%)</td>
    </tr>
    <?php if($accion=="Reporte"){
    $DatosPdf = Array();
    $DatosTotal=array();
    $DatosPdfFinal=array();
	for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
	   $SubTotal = $A["$campo"."total"]-$A["$campo"."glp"];
	   $Porcentaje = $porcentajes[$i];
	?>
    <tr> 
      <td><div align="center"><?php echo $A["cliente"];?></div></td>
      <td><div align="center"><?php echo $A["$campo"."84"];?></div></td>
      <td><div align="center"><?php echo $A["$campo"."90"];?></div></td>
      <td><div align="center"><?php echo $A["$campo"."95"];?></div></td>
      <td><div align="center"><?php echo $A["$campo"."97"];?></div></td>
      <td><div align="center"><?php echo $A["$campo"."d2"];?> </div></td>
      <td><div align="center"><?php echo $A["$campo"."kd"];?> </div></td>
      <td><div align="center"><?php echo $SubTotal?> </div></td>
      <td><div align="center"><?php echo $A["$campo"."glp"];?> </div></td>
      <td><div align="center"><?php echo $A["$campo"."total"];?> </div></td>
      <td><div align="center"><?php echo $Porcentaje?></div></td>
    </tr>
    <?php
        $DatosPdf['VENTA_84']   = $A["$campo"."84"];
        $DatosPdf['VENTA_90']   = $A["$campo"."90"];
        $DatosPdf['VENTA_95']   = $A["$campo"."95"];
        $DatosPdf['VENTA_97']   = $A["$campo"."97"];
        $DatosPdf['VENTA_D2']   = $A["$campo"."d2"];
        $DatosPdf['VENTA_KD']   = $A["$campo"."kd"];
        $DatosPdf['SUB_TOTAL']  = $SubTotal;
        $DatosPdf['VENTA_GLP']  = $A["$campo"."glp"];
        $DatosPdf['TOTAL']      = $A["$campo"."total"];
        $DatosPdf['PORCENTAJE'] = $Porcentaje;
        
        $DatosPdfFinal[$A["cliente"]] = $DatosPdf;
        
        $DatosTotal['VENTA_84'] += $DatosPdf['VENTA_84'];
        $DatosTotal['VENTA_90'] += $DatosPdf['VENTA_90'];
        $DatosTotal['VENTA_95'] += $DatosPdf['VENTA_95'];
        $DatosTotal['VENTA_97'] += $DatosPdf['VENTA_97'];
        $DatosTotal['VENTA_D2'] += $DatosPdf['VENTA_D2'];
        $DatosTotal['VENTA_KD'] += $DatosPdf['VENTA_KD'];
        $DatosTotal['SUB_TOTAL'] += $DatosPdf['SUB_TOTAL'];
        $DatosTotal['VENTA_GLP'] += $DatosPdf['VENTA_GLP'];
        $DatosTotal['TOTAL'] += $DatosPdf['TOTAL'];
    }
    
    $DatosPdfFinal['TOTALES FINAL']=$DatosTotal;
    ?>
    <tr>
	<td colspan="14"><input type="button" name="boton_imprimir" value="Imprimir reporte" onClick="javascript:imprimir()"></td>
    </tr>
    <?php
    /*echo "<!--";
    print_r($DatosPdfFinal);
    echo "-->";*/
    include("vta_resumen_consumo_xcliente_reporte.php");
    $Reporte = ResuConsXclienteReporteTemplate::ReportePdf($DatosPdfFinal);
    echo "$Reporte";
    }?>
  </table>
</form>
</body>
</html>
<?php pg_close();?>