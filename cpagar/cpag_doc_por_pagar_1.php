<?php
include("../valida_sess.php");
//include("../menu_princ.php");
include("../config.php");
// include("../functions.php");

include("../include/functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
//$conector_repli_id = $funcion->conectar("","","acosa_backups","","");

if($accion=="Eliminar"){
	?><script>//alert('Entro a eliminar <?php echo "|".$cpag_det[0]."|".$cpag_det[1]."|".$cpag_det[$i]."|"; ?>');</script><?php

	for($i=0;$i<count($cpag_det);$i++){

		$vars = cortarCadena2($cpag_det[$i],"#");
		// primero elimina el documento en estaciones 
		// por que despues de eliminado en la oficina no queda referencia
		/*$inclusion= 1;
		$q = "select pro_cab_imptotal-pro_cab_impsaldo as saldo from cpag_ta_cabecera
			where pro_cab_tipdocumento = '$vars[0]'
			and pro_cab_seriedocumento = '$vars[1]'
			and pro_cab_numdocumento = '$vars[2]'
			and pro_codigo = '$vars[3]';";
?><script>alert('<?php echo $q.$inclusion; ?>');</script><?php
		$dif=pg_result(pg_exec($conector_id,$q),0,0);*/

		/*if ($dif==0.00 && $vars[4]==$inclusion) 
			{
			$q="select mov_almacen from inv_ta_compras_devoluciones 
					where cpag_tipo_pago = '$vars[0]'
					and cpag_serie_pago = '$vars[1]' 
					and cpag_num_pago = '$vars[2]'
					and mov_entidad = '$vars[3]'
					;";
			$rs=pg_exec($conector_id,$q);
			$ili_rs=pg_numrows($rs);
			$irs=0;
			if($ili_rs>0) 
				{
				while($irs<$ili_rs) 
					{
					$c_alma=pg_result($rs,$irs,0);
					$Datos['Ip_Estacion']  = ObtenerIPAlmacen($conector_repli_id, trim($c_alma));
					$Datos['Cod_Estacion'] = $c_alma;
					$q = "SELECT cpagar_fn_soltar_ordenes_grifo('$vars[0]','$vars[1]','$vars[2]','$vars[3]','$vars[4]','$vars[5]')";	
		    	    $SentenciasReplicacion = SentenciasReplicacion($conector_repli_id, $q, $Datos);
					$irs++;
					}
				}
			}*/
		// aqui recien elimina el documento			
		$q = "SELECT cpagar_fn_soltar_ordenes('$vars[0]','$vars[1]','$vars[2]','$vars[3]','$vars[4]','$vars[5]')";	
		pg_exec($conector_id,$q);
	}
}
?>

<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>/sistemaweb.css" type="text/css">
<head>
<title></title>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script>
	function mostrarCintilloCpagar(c_tipo,c_serie,c_numero,c_proveedor){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
	//url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	//$c_tipo#$c_serie#$c_numero#$c_proveedor
	url = "cpag_cintillo_liq_compras.php";
	url = url+"?c_tipo="+c_tipo+"&c_serie="+c_serie+"&c_numero="+c_numero+"&c_proveedor="+c_proveedor;
	window.open(url,'cintillo_cpagar','width=700,height=500,scrollbars=yes,menubar=yes,resizable=yes,left=20,top=20');
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
<input type="hidden" name="tip_docum" value="<?php echo $_REQUEST['tip_docum'];?>">
  <?php
	$var_pers="v_fecha_desde=".$v_fecha_desde."&v_fecha_hasta=".$v_fecha_hasta."&v_almacen=".$v_almacen."&v_proveedor=".$v_proveedor;
	include("../maestros/pagina.php");
	//$bddsql=" limit $tamPag offset $limitInf ";
?>
  <input class="form_button" type="button" name="btn_soltar_ordenes" value="Soltar Ordenes" onClick="javascript:accion.value='Eliminar',f_name2.submit();">
  <input type="hidden" name="accion" value="">
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
		<th width='250'>Proveedor</th>
		<th>Moneda</th>
		<th>Total</th>
		<th>Saldo</th>
		<th width='150'>Rubro</th>
		<th>Fecha Vencimiento</th>
		<th>Inafecto</th>
	</tr>
<?php


if ( trim($v_proveedor)!='')  {$v_proveedor_ref = " AND trim(cab.pro_codigo)='$v_proveedor' ";} else {$v_proveedor_ref = " AND true ";}
if ( trim($v_almacen)!='')    {$v_almacen_ref = " AND cab.pro_cab_almacen='$v_almacen' ";} else {$v_almacen_ref = " AND true ";}
if ( trim($_REQUEST['tip_docum'])!=''){$v_tipodoc_ref = " AND cab.pro_cab_tipdocumento='".$_REQUEST['tip_docum']."' ";}
if ( trim($_REQUEST['nro_docum'])!=''){$v_nrodoc_ref = " AND cab.pro_cab_numdocumento='".$_REQUEST['nro_docum']."' ";}

$v_sql1="SELECT cab.pro_cab_tipdocumento||cab.pro_cab_seriedocumento||cab.pro_cab_numdocumento||cab.pro_codigo as clave, ".
	       "to_char(cab.pro_cab_fecharegistro,'DD/MM/YYYY') as pro_cab_fecharegistro,".
	       "to_char(cab.pro_cab_fechaemision,'DD/MM/YYYY') as pro_cab_fechaemision,".
	       "cab.pro_cab_almacen, ".
	       "cab.pro_cab_tipdocumento, ".
	       "cab.pro_cab_seriedocumento, ".
	       "cab.pro_cab_numdocumento, ".
	       "cab.pro_codigo, ".
	       "pro.pro_rsocialbreve, ".
	       "cab.pro_cab_imptotal, ".
	       "cab.pro_cab_impsaldo, ".
	       "tab1.tab_descripcion as pro_cab_moneda, ".
	       "(cast(trim(cab.pro_cab_rubrodoc) as varchar)||' - '||tab2.ch_descripcion) as pro_cab_rubrodoc, ".
	       "to_char(cab.pro_cab_fechavencimiento,'DD/MM/YYYY') as pro_cab_fechavencimiento,".
	       "coalesce(cab.pro_cab_impinafecto,0.00) as pro_cab_impinafecto ".
        "FROM cpag_ta_cabecera cab, ".
             "int_proveedores pro, ".
             "inv_ta_almacenes alm, ".
	     "int_tabla_general tab1, ".
	     "cpag_ta_rubros tab2 ".
        "WHERE cab.pro_codigo = pro.pro_codigo ".
	"AND cab.pro_cab_almacen = alm.ch_almacen ".
	"AND cab.pro_cab_moneda = tab1.tab_elemento ".
	"AND (tab1.tab_tabla='MONE') ".
	"AND cab.pro_cab_rubrodoc = tab2.ch_codigo_rubro ".
	"AND cab.pro_cab_fecharegistro BETWEEN '".$funcion->date_format($v_fecha_desde,'YYYY/MM/DD')."' ".
	"AND '".$funcion->date_format($v_fecha_hasta,'YYYY/MM/DD')."' ".
	"".$v_proveedor_ref." ".
	"".$v_almacen_ref." ".
	"".$v_tipodoc_ref." ".
	"".$v_nrodoc_ref." ".
	"ORDER BY cab.pro_cab_fecharegistro DESC,cab.pro_cab_numdocumento DESC ".
	"".$bddsql."  ";trigger_error($v_sql1);

echo "<!-- QUERY : $v_sql1-->\n";
//print_r($v_sql1);
//DELETE FROM cpag_ta_detalle  WHERE pro_codigo ~ '00M377';
//DELETE FROM cpag_ta_cabecera  WHERE pro_codigo ~ '00M377';
$v_xsql1 = pg_exec($conector_id,$v_sql1);
$v_ilimit1=pg_numrows($v_xsql1);

$chk='checked';
$v_irow1=0;

if($v_ilimit1>0) 
{
    while($v_irow1<$v_ilimit1) 
    {
	$c_clave=pg_result($v_xsql1,$v_irow1,'clave');

	$c_fecha_registro = pg_result($v_xsql1,$v_irow1,'pro_cab_fecharegistro');
	$c_fecha=pg_result($v_xsql1,$v_irow1,'pro_cab_fechaemision');

	$c_almacen=pg_result($v_xsql1,$v_irow1,'pro_cab_almacen');
	$c_tipo=pg_result($v_xsql1,$v_irow1,'pro_cab_tipdocumento');
	$c_serie=pg_result($v_xsql1,$v_irow1,'pro_cab_seriedocumento');
	$c_numero=pg_result($v_xsql1,$v_irow1,'pro_cab_numdocumento');
	$c_proveedor=pg_result($v_xsql1,$v_irow1,'pro_codigo');
	$c_descprov=pg_result($v_xsql1,$v_irow1,'pro_rsocialbreve');
	$c_imptotal=pg_result($v_xsql1,$v_irow1,'pro_cab_imptotal');
	$c_impsaldo=pg_result($v_xsql1,$v_irow1,'pro_cab_impsaldo');

	$c_moneda=pg_result($v_xsql1,$v_irow1,'pro_cab_moneda');
	$c_rubro=pg_result($v_xsql1,$v_irow1,'pro_cab_rubrodoc');
	$c_vencimiento=pg_result($v_xsql1,$v_irow1,'pro_cab_fechavencimiento');
	$c_inafecto=pg_result($v_xsql1,$v_irow1,'pro_cab_impinafecto'); 

	if ( $checkbox[$v_irow1]==$c_clave ){
		$chk='checked';
		}
	else{
		$chk=' ';
		}
	echo "<tr onMouseOver=\"this.style.backgroundColor='#FFFFCC',this.style.cursor='hand';\" 
	onMouseOut=\"this.style.background='#CCCC99'\" >";
	echo "<td><input type='checkbox' name='checkbox[$v_irow1]' value='$c_clave' onclick='document.f_name2.submit()' $chk ></td>";
	echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor');\" align='center'>&nbsp;".$c_fecha_registro."</td>";
	echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor');\" align='center'>&nbsp;".$c_fecha."</td>";
	echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor');\" align='center'>&nbsp;".$c_almacen."</td>";
	echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor');\" align='center'>&nbsp;".$c_tipo."</td>";
	echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor');\" align='center'>&nbsp;".$c_serie."</td>";
	echo "<td onclick=\"mostrarCintilloCpagar('$c_tipo','$c_serie','$c_numero','$c_proveedor');\" align='center'>&nbsp;".$c_numero."</td>";
	//echo "<td>&nbsp;".$c_proveedor."</td>";
	//echo '<td bgcolor="#CCCC99" onMouseOver="window.status=\'Codigo \'; overlib(\'-'.$c_proveedor.'-\'); return true;" onMouseOut="window.status=\'\'; nd(); return true;"> ';
	echo '<td align="center">';
	echo '&nbsp;'.$c_proveedor." - ".$c_descprov;
	echo '</td>';
	echo "<td align='center'>&nbsp;".$c_moneda."</td>";
	echo "<td align='right'>&nbsp;".$c_imptotal."</td>";
	echo "<td align='right'>&nbsp;".$c_impsaldo."</td>";

	echo "<td align='center'>&nbsp;".$c_rubro."</td>";
	echo "<td align='center'>&nbsp;".$c_vencimiento."</td>";
	echo "<td align='right'>&nbsp;".$c_inafecto."</td>";
	echo '<td><a href="cpag_inclu_fac_edit.php?regid='.$c_tipo.$c_serie.$c_numero.$c_proveedor.'" target="_blank"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"></a></td>';
	echo "</a></tr>";
	if ( $checkbox[$v_irow1]==$c_clave )
	{
	    $v_sql2="SELECT	det.pro_cab_tipdocumento, ".
			"det.pro_cab_seriedocumento, ".
			"det.pro_cab_numdocumento, ".
			"det.pro_codigo, ". 
			"det.pro_det_tipmovimiento, ".
			"det.pro_det_fechamovimiento, ".
			"det.pro_det_impmovimiento, ".
			"det.pro_det_identidad, ".
			"det.pro_det_almacen ".			
		    "FROM cpag_ta_detalle det ".
		    "WHERE det.pro_cab_tipdocumento='$c_tipo' ".
		    "AND det.pro_cab_seriedocumento='$c_serie' ".
		    "AND det.pro_cab_numdocumento='$c_numero' ".
		    "AND det.pro_codigo='$c_proveedor' ".
		    "ORDER BY det.pro_det_fechamovimiento, ".
		    "det.pro_det_tipmovimiento ".
		    "".$bddsql."  ";
	   echo "<!-- QUERY DETALLE : $v_sql2-->\n";
	    $v_xsql2=pg_exec($conector_id,$v_sql2);
	    $v_ilimit2=pg_numrows($v_xsql2);
	    $v_irow2=0;
	    if($v_ilimit2>0) 
	    {
		while($v_irow2<$v_ilimit2)
		{
		    $c_tipmovimiento= pg_result($v_xsql2,$v_irow2,'PRO_DET_TIPMOVIMIENTO');
		    $c_fechamovimiento= pg_result($v_xsql2,$v_irow2,'PRO_DET_FECHAMOVIMIENTO');
		    $c_impmovimiento= pg_result($v_xsql2,$v_irow2,'PRO_DET_IMPMOVIMIENTO');
		    $c_pro_det_identidad= pg_result($v_xsql2,$v_irow2,'PRO_DET_IDENTIDAD');
		    $c_pro_det_almacen= pg_result($v_xsql2,$v_irow2,'PRO_DET_ALMACEN');
		    echo "<tr>";
		    echo "<td>&nbsp;</td>";
		    echo "<td><input type='checkbox' name='cpag_det[]' value='$c_tipo#$c_serie#$c_numero#$c_proveedor#$c_tipmovimiento#$c_pro_det_identidad#$c_pro_det_almacen'>$c_tipmovimiento</td>";
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


</form>
</html>
