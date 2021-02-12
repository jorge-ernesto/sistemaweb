<?php
include("../valida_sess.php");
//include("../menu_princ.php");
include("../config.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if($accion=="Eliminar"){
	/*for($i=0;$i<count($cpag_det);$i++){
		//print $cpag_det[$i]."<br>";
		#$c_tipo#$c_serie#$c_numero#$c_proveedor#$c_tipmovimiento#$c_pro_det_identidad'
		//$vars = cortarCadena2($cpag_det[$i],"#");
		$q = "select CPAGAR_FN_SOLTAR_ORDENES('$vars[0]','$vars[1]','$vars[2]','$vars[3]','$vars[4]','$vars[5]')";	
		echo $q;
		pg_exec($q);
	}*/
}


if($v_filtro_pre == "S"){ $w_pre1=" and CAB.nu_importesaldo != 0 ";}
if($v_filtro_pre == "N"){ $w_pre1="  ";}

?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>acosa.css" type="text/css">
<head>
<title>ACOSA</title>
<script language="JavaScript" src="/acosa/clases/calendario.js"></script>
<script language="JavaScript" src="/acosa/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/acosa/clases/reloj.js"></script>
<script>
	function mostrarCintilloCpagar(c_tipo,c_serie,c_numero,c_proveedor,d){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
	//url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	//$c_tipo#$c_serie#$c_numero#$c_proveedor
	//url = "cpag_cintillo_liq_compras.php";
	//url = url+"?c_tipo="+c_tipo+"&c_serie="+c_serie+"&c_numero="+c_numero+"&c_proveedor="+c_proveedor;
	//window.open(url,'cintillo_cpagar','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
	//alert(d.value);
	}
	
	function ifmandarDatos(form,opcion){
		if(opcion=="Precancelacion"){
			form.action="forms_popup/ccob_form_precancelacion_iframe.php";
			form.target="ifr1";
		}
		else{
			form.action="ccob_doc_por_pagar_1.php";
			form.target="";
		}
		form.accion.value=opcion;
		form.submit();
	}
</script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name="f_name2" method="post" >
<input type="hidden" name="v_fecha_desde" value="<?php echo $v_fecha_desde;?>">
<input type="hidden" name="v_fecha_hasta" value="<?php echo $v_fecha_hasta;?>">
<input type="hidden" name="v_almacen" value="<?php echo $v_almacen;?>">
<input type="hidden" name="v_proveedor" value="<?php echo $v_proveedor;?>">
  <?php
	$var_pers="v_fecha_desde=".$v_fecha_desde."&v_fecha_hasta=".$v_fecha_hasta."&v_almacen=".$v_almacen."&v_proveedor=".$v_proveedor;
	include("../maestros/pagina.php");
	$bddsql=" limit $tamPag offset $limitInf ";
?>
  <?php if($v_filtro_pre == "S"){?><input type="button" name="btn_soltar_ordenes" value="Hacer Precancelacion" onClick="javascript:ifmandarDatos(f_name2,'Precancelacion');"><?php } ?>
  <input type="hidden" name="accion" value="">
  <input type="button" name="btn_soltar_ordenes2" value="CLICK" onClick="javascript:alert(fila_seleccionada.value);">
  <input type="hidden" name="v_filtro_pre" value="<?php echo $v_filtro_pre;?>">
  <input type="hidden" name="fila_seleccionada" value="<?php echo $fila_seleccionada;?>">
  <table border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td>&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td>&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td >&nbsp;</td>
		<th>Fecha Registro</th>
		<th>Fecha Emision</th>
		<th>Almacen</th>
		<th>Tipo Doc</th>
		<th>Ser Doc</th>
		<th>Numero Doc</th>
		<th>Proveedor</th>
		<th>Moneda</th>
		<th>Total</th>
		<th>Saldo</th>
		<th>Rubro</th>
		<th>Fecha Vencimiento</th>
		<th>Inafecto</th>
	</tr>
<?php


if ( trim($v_proveedor)!='')  {$v_proveedor_ref = " and CAB.CLI_CODIGO='$v_proveedor' ";} else {$v_proveedor_ref = " and true ";}
if ( trim($v_almacen)!='')    {$v_almacen_ref = " and CAB.ch_sucursal ='$v_almacen' ";} else {$v_almacen_ref = " and true ";}

$v_sql1="select CAB.ch_tipdocumento||'#'||CAB.ch_seriedocumento||'#'||CAB.ch_numdocumento||'#'||CAB.cli_codigo as CLAVE,
				CAB.dt_fecharegistro,
				CAB.dt_fechaemision,
				CAB.ch_sucursal ,
				CAB.ch_tipdocumento,
				CAB.ch_seriedocumento,
				CAB.ch_numdocumento,
				CAB.cli_codigo,
				CLI.cli_rsocialbreve,
				CAB.nu_importetotal,
				CAB.nu_importesaldo,
				CAB.CLI_CODIGO AS PRO_CODIGO,
				TAB1.TAB_DESCRIPCION AS PRO_CAB_MONEDA,
				CAB.ch_sucursal_precancelado ,
				CAB.dt_fechavencimiento
				,coalesce(CAB.nu_importe_precancelado,0.00) as nu_importe_precancelado 

			from ccob_ta_cabecera CAB, int_clientes CLI, INV_TA_ALMACENES ALM,
					INT_TABLA_GENERAL TAB1

			where CAB.CLI_CODIGO = CLI.CLI_CODIGO
			and CAB.ch_sucursal = ALM.CH_ALMACEN

			and CAB.ch_moneda = TAB1.TAB_ELEMENTO and (TAB1.TAB_TABLA='MONE')
	
			and	CAB.dt_fecharegistro between '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' and '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."'
			".$v_proveedor_ref.$v_almacen_ref."
			".$w_pre1."
			order by
			CAB.dt_fecharegistro
			".$bddsql."  ";
//echo $v_sql1;
$v_xsql1 = pg_exec($conector_id,$v_sql1);
$v_ilimit1=pg_numrows($v_xsql1);

$chk='checked';
$v_irow1=0;

if($v_ilimit1>0) {
	while($v_irow1<$v_ilimit1) {
		$c_clave=pg_result($v_xsql1,$v_irow1,'CLAVE');

		$c_fecha_registro = pg_result($v_xsql1,$v_irow1,'dt_fecharegistro');
		$c_fecha=pg_result($v_xsql1,$v_irow1,'dt_fechaemision');

		$c_almacen=pg_result($v_xsql1,$v_irow1,'ch_sucursal');
		$c_tipo=pg_result($v_xsql1,$v_irow1,'ch_tipdocumento');
		$c_serie=pg_result($v_xsql1,$v_irow1,'ch_seriedocumento');
		$c_numero=pg_result($v_xsql1,$v_irow1,'ch_numdocumento');
		$c_proveedor=pg_result($v_xsql1,$v_irow1,'PRO_CODIGO');
		$c_descprov=pg_result($v_xsql1,$v_irow1,'cli_rsocialbreve');
		$c_imptotal=pg_result($v_xsql1,$v_irow1,'nu_importetotal');
		$c_impsaldo=pg_result($v_xsql1,$v_irow1,'nu_importesaldo');

		$c_moneda=pg_result($v_xsql1,$v_irow1,'PRO_CAB_MONEDA');
		$c_rubro=pg_result($v_xsql1,$v_irow1,'ch_sucursal_precancelado');
		$c_vencimiento=pg_result($v_xsql1,$v_irow1,'dt_fechavencimiento');
		$c_inafecto=pg_result($v_xsql1,$v_irow1,'nu_importe_precancelado'); 

		if ( $checkbox[$v_irow1]==$c_clave ){
			$chk='checked';
			}
		else{
			$chk=' ';
			}
		echo "<tr onMouseOver=\"this.style.backgroundColor='#FFFFCC',this.style.cursor='hand';\" 
		onMouseOut=\"this.style.background='#CCCC99'\" >";
		echo "<td><input type='checkbox' id='d_$v_irow1' name='checkbox[$v_irow1]' value='$c_clave' onclick=\"javascript:fila_seleccionada.value='$v_irow1',ifmandarDatos(f_name2,'enviar');\" $chk ></td>";
		echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor',d_$v_irow1);\">&nbsp;".$c_fecha_registro."</td>";
		echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor',d_$v_irow1);\">&nbsp;".$c_fecha."</td>";
		echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor',d_$v_irow1);\">&nbsp;".$c_almacen."</td>";
		echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor',d_$v_irow1);\">&nbsp;".$c_tipo."</td>";
		echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor',d_$v_irow1);\">&nbsp;".$c_serie."</td>";
		echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor',d_$v_irow1);\">&nbsp;".$c_numero."</td>";
//		echo "<td>&nbsp;".$c_proveedor."</td>";
//		echo '<td bgcolor="#CCCC99" onMouseOver="window.status=\'Codigo \'; overlib(\'-'.$c_proveedor.'-\'); return true;" onMouseOut="window.status=\'\'; nd(); return true;"> ';
		echo '<td>';
		echo '&nbsp;'.$c_proveedor." - ".$c_descprov;
		echo '</td>';
		echo "<td align='right'>&nbsp;".$c_moneda."</td>";
		echo "<td align='right'>&nbsp;".$c_imptotal."</td>";
		echo "<td align='right'>&nbsp;".$c_impsaldo."</td>";

		echo "<td align='right'>&nbsp;".$c_rubro."</td>";
		echo "<td align='right'>&nbsp;".$c_vencimiento."</td>";
		echo "<td align='right'>&nbsp;".$c_inafecto."</td>";


		echo "</a></tr>";
		if ( $checkbox[$v_irow1]==$c_clave ){
			$v_sql2="select
						DET.ch_tipdocumento,
						DET.ch_seriedocumento,
						DET.ch_numdocumento,
						DET.cli_codigo,
						DET.ch_tipmovimiento,
						DET.dt_fechamovimiento,
						DET.nu_importemovimiento,
						DET.ch_identidad
					from ccob_ta_detalle DET
					where
						DET.ch_tipdocumento='$c_tipo' and
						DET.ch_seriedocumento='$c_serie' and
						DET.ch_numdocumento='$c_numero' and
						DET.cli_codigo='$c_proveedor'
						order by DET.dt_fechamovimiento, DET.ch_tipmovimiento
						".$bddsql."  ";
			$v_xsql2=pg_exec($conector_id,$v_sql2);
			$v_ilimit2=pg_numrows($v_xsql2);
			$v_irow2=0;
			if($v_ilimit2>0) {
				while($v_irow2<$v_ilimit2){
					$c_tipmovimiento= pg_result($v_xsql2,$v_irow2,'ch_tipdocumento');
					$c_fechamovimiento= pg_result($v_xsql2,$v_irow2,'dt_fechamovimiento');
					$c_impmovimiento= pg_result($v_xsql2,$v_irow2,'nu_importemovimiento');
					$c_pro_det_identidad= pg_result($v_xsql2,$v_irow2,'ch_identidad');
					echo "<tr>";
					echo "<td>&nbsp;</td>";
					echo "<td><!--<input type='checkbox' name='cpag_det[]' value='$c_tipo#$c_serie#$c_numero#$c_proveedor#$c_tipmovimiento#$c_pro_det_identidad'>$c_tipmovimiento--></td>";
					echo "<td>$c_fechamovimiento</td>";
					echo "<td align='right'>$c_impmovimiento</td>";
					echo "</tr>";
					$v_irow2++;
					}
				}
			}
		$v_irow1++;
		}
	}
?>

</table>

<iframe style="overflow:hidden" name="ifr1" height="0" width="0"></iframe>
</form>
</html>
