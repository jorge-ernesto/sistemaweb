<?php
if($boton=="Adicionar") {
?>
<script languaje="JavaScript">
	location.href='inv_addmov.php?fm=<?php echo $fm;?>&flg=A';
</script>
<?php
}elseif($boton=="Modificar") {
   if(strlen($nform)>0){
?>
<script languaje="JavaScript">
	location.href='inv_updmov.php?fm=<?php echo $fm;?>&nromov=<?php echo $nform;?>&flg=A';
</script>
<?php }else{  ?>
<script languaje="JavaScript">
//	location.href='inv_updmov.php?fm=<?php echo $fm;?>&nromov=<?php echo $nform;?>';
	alert(" Debe seleccionar un movimiento !!! ")
</script>
<?php   }
}
//include("../config.php");
//include("inc_top.php");
include("../valida_sess.php");
include("../menu_princ.php");

include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

/*anulando variable de sesion para enlace con cuentas por pagar */
unset($numero_movimiento);
unset($tran_codigo);

/*anulando variable de sesion para enlace con cuentas por pagar */

tipoform($fm,$coneccion);

if(strlen($diad)==0 or strlen($mesd)==0 or strlen($anod)==0 or strlen($anoa)==0 or strlen($mesa)==0 or strlen($diaa)==0) {
	$dia_actual = 1;
	$mes=date("m");
	$ano=date("Y");
	$fecini=date("Y-m")."-01";
	$ultimo_dia = ultimoDia($mes,$ano);
	$fecfin=date("Y-m")."-".$ultimo_dia;
} else {
	$fecini=$anod."/".$mesd."/".$diad." 00:00:00";
	$fecfin=$anoa."/".$mesa."/".$diaa." 23:59:59";
}
$sqladd=" and m.mov_fecha between '$fecini' and '$fecfin' ";
//echo $sqladd;

if($boton=="Consultar" or (strlen($diad)>0 and strlen($mesd)>0 and strlen($anod)>0 and strlen($diaa)>0 and strlen($mesa)>0 and strlen($anoa)>0)) 
{
	$sql2="select m.mov_numero,to_char(m.mov_fecha,'dd/mm/yyyy hh24:mi:ss') as mov_fecha,m.mov_tipdocuref,m.mov_docurefe,m.mov_almaorigen,m.mov_almadestino,m.mov_almacen,
	 m.art_codigo,m.mov_cantidad,m.mov_costounitario,a.art_descripcion from inv_movialma m,int_articulos a
	 where m.tran_codigo='$fm' and m.art_codigo=a.art_codigo ".$sqladd." ".$xlim." ";
	//echo $sql2;	
	$flg = "V";
	$xsql2=pg_exec($coneccion,$sql2);
	$ilimit2=pg_numrows($xsql2);
	//echo "aqui comienza";
	if($ilimit2>0) {
		//echo "aqui numero";
		
		$numeroRegistros=$ilimit2;
		//echo $numeroRegistros;
	}
	//echo "aqui es el numero".$ilimit2;
}

if($flg=="A") {
	rangodefechas();
	$diad=$zdiad; $mesd=$zmesd; $anod=$zanod; $diaa=$zdiaa; $mesa=$zmesa; $anoa=$zanoa;
	$fechad=$anod."/".$mesd."/".$diad." 00:00:00";
	$fechaa=$anoa."/".$mesa."/".$diaa." 23:59:59";
}

?>

MOVIMIENTOS DE <?php echo $descform; ?>
<hr noshade>

<form action="" method="post">
  <table border="1">
    <tr>
      <th colspan="5">CONSULTAR POR RANGO DE FECHAS</th>
    </tr>
    <tr>
      <th>DESDE :</th>
      <th><input type="text" name="diad" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $diad;?>'>
        /
        <input type="text" name="mesd" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $mesd;?>'>
        /
        <input type="text" name="anod" size="6" maxlength="4" onKeyPress="return esIntspto(event)" value='<?php echo $anod;?>'></th>
      <th>HASTA:</th>
      <th><input type="text" name="diaa" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $diaa;?>'>
        /
        <input type="text" name="mesa" size="4" maxlength="2" onKeyPress="return esIntspto(event)" value='<?php echo $mesa;?>'>
        /
        <input type="text" name="anoa" size="6" maxlength="4" onKeyPress="return esIntspto(event)" value='<?php echo $anoa;?>'></th>
		<th><input type="submit" name="boton" value="Consultar"></th>
    </tr>
  </table>
    <input type="hidden" name="fm" value='<?php echo $fm;?>'><br>
  <a href="#" onClick="javascript:window.open('inv_movdalmacen-reporte.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&fm=<?php echo $fm;?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar Reporte>></a>
  <?php
$var_pers="fm=".$fm."&diad=".$diad."&mesd=".$mesd."&anod=".$anod."&diaa=".$diaa."&mesa=".$mesa."&anoa=".$anoa;
include("../maestros/pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";
?>
  <table border="1" cellpadding="0" cellspacing="0">
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="boton" value="Adicionar"></td>
      <td>&nbsp;</td>
      <td><!--<input type="submit" name="boton" value="Modificar">--></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <th>FORMULARIO</th>
      <th>FECHA</th>
      <th>No O/C</th>
      <th>No DOC. REF</th>
      <th>ORI</th>
      <th>DEST</th>
      <th>ALM</th>
      <th>COD ART</th>
      <th>CANTIDAD</th>
      <th>COSTO UNIT.</th>
      <th>DESCRIPCION ART</th>
    </tr>
    <?php
if(strlen($diad)==0 or strlen($mesd)==0 or strlen($anod)==0 or strlen($anoa)==0 or strlen($mesa)==0 or strlen($diaa)==0) {
	$dia_actual = 1;
	$mes=date("m");
	$ano=date("Y");
	$fecini=date("Y-m")."-01";
	$ultimo_dia = ultimoDia($mes,$ano);
	$fecfin=date("Y-m")."-".$ultimo_dia;
} else {
	$fecini=$anod."/".$mesd."/".$diad." 00:00:00";
	$fecfin=$anoa."/".$mesa."/".$diaa." 23:59:59";
}
$sqladd=" and m.mov_fecha between '$fecini' and '$fecfin' ";

$sql2="select m.mov_numero,to_char(m.mov_fecha,'dd/mm/yyyy hh24:mi:ss') as mov_fecha,m.mov_tipdocuref,m.mov_docurefe,m.mov_almaorigen,m.mov_almadestino,m.mov_almacen,
m.art_codigo,m.mov_cantidad,m.mov_costounitario,a.art_descripcion,m.com_serie_compra,m.com_num_compra from inv_movialma m,int_articulos a
 where m.tran_codigo='$fm' and m.art_codigo=a.art_codigo ".$sqladd." order by m.mov_fecha desc  ".$bddsql."  ";


//echo $sql2;
/*
$sqlC="select m.mov_numero AS FORMULARIO,m.mov_fecha AS FECHA,m.mov_numero AS No_OC
, m.mov_tipdocuref || '-' || m.mov_docurefe AS No_DOCREF,m.mov_almaorigen AS ORIGEN ,m.mov_almadestino AS DESTINO, m.mov_almacen AS ALMACEN
,m.art_codigo AS COD_ARTICULO, m.mov_cantidad AS CANTIDAD,m.mov_costounitario AS COSTO_UNI
,a.art_descripcion AS DESCRIPCION from inv_movialma m,int_articulos a
 where m.tran_codigo='$fm' and m.art_codigo=a.art_codigo ".$sqladd." ".$bddsql." ";
 echo $sqlC;
*/
$xsql2=pg_exec($coneccion,$sql2);
$ilimit2=pg_numrows($xsql2);
if($ilimit2>0) {
	while($irow2<$ilimit2) {
		$a0=pg_result($xsql2,$irow2,0);
		$a1=pg_result($xsql2,$irow2,1);
		$a2=pg_result($xsql2,$irow2,2);
		$a3=pg_result($xsql2,$irow2,3);
		$a4=pg_result($xsql2,$irow2,4);
		$a5=pg_result($xsql2,$irow2,5);
		$a6=pg_result($xsql2,$irow2,6);
		$a7=pg_result($xsql2,$irow2,7);
		$a8=pg_result($xsql2,$irow2,8);
		$a9=pg_result($xsql2,$irow2,9);
		$a10=pg_result($xsql2,$irow2,10);
		$a11=pg_result($xsql2,$irow2,11);
		$a12=pg_result($xsql2,$irow2,12);
		
		if($nform==$a0) {
//			echo "<tr><td>&nbsp;<input type='radio' name='nform' value='".$a0."' onClick='submit(this)' checked></td>";
?>
	<tr bgcolor="#CCCC99" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#CCCC99'"o"];" >
<?php			echo "<td>&nbsp;<input type='radio' name='nform' value='".$a0."' checked></td>";
		}else {
?>
	<tr bgcolor="#CCCC99" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#CCCC99'"o"];" onClick="mostrarCintillo('<?php echo $a0;?>','<?php echo $fm;?>');">
<?php
			echo "<td>&nbsp;<input type='radio' name='nform' value='".$a0."'></td>";
//			echo "<tr><td>&nbsp;<input type='radio' name='nform' value='".$a0."' onClick='submit(this)'></td>";
		}
        echo "<td>&nbsp;".$a0."</td><td>&nbsp;".$a1."</td><td>".$a12."</td><td>&nbsp;".$a2." - ".$a3."</td>";
        echo "<td>".$a4."</td><td>&nbsp;".$a5."</td><td>&nbsp;".$a6."</td>";
		echo "<td>".$a7."</td><td><p align='right'>".$a8."</p></td><td><p align='right'>".$a9."</p></td><td>".$a10."</td></tr>";
		$irow2++;
	}
}
?>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="boton" value="Adicionar"></td>
      <td>&nbsp;</td>
      <td><!--<input type="submit" name="boton" value="Modificar">--></td>
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
</body>
</html>
<?php
pg_close($coneccion);
