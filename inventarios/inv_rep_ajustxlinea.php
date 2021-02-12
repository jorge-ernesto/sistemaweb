<?php
//include("../valida_sess.php");
//include("../valida_item_menu.php");
//include("../config.php");
include("../menu_princ.php");
include("../functions.php");
/*obtenemos fecha*/
$nuevofechad = split('/',$_REQUEST['diasd']);
$diad=$nuevofechad[0];
$mesd=$nuevofechad[1];
$anod=$nuevofechad[2];
$nuevofechaa = split('/',$_REQUEST['diasa']);
$diaa=$nuevofechaa[0];
$mesa=$nuevofechaa[1];
$anoa=$nuevofechaa[2];
if($flg=="A") {
	$diaa=date("d");  $mesa=date("m");  $anoa=date("Y");
	$diad=$diaa; $mesd=$mesa; $anod=$anoa;
	$detoresum="D"; $txtform="17";  $txtalma=$almacen;
}
$fechad=$anod."-".$mesd."-".$diad." 00:00:00";
$fechaa=$anod."-".$mesd."-".$diad." 23:59:59";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>integrado</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script language="javascript">
var miPopup
function abrealma(){
    miPopup = window.open("/sistemaweb/inventarios/almac.php","miwin","width=500,height=400,scrollbars=yes")
    miPopup.focus()
}
function abreform(){
    miPopup = window.open("/sistemaweb/menu/procesos/escogeform.php","miwin","width=600,height=350,scrollbars=yes")
    miPopup.focus()
}
function enviadatos(){
	document.formular.submit()
}
</script>
</head>

<body>
MOVIMIENTOS VALORIZADOS POR TIPO DE FORMULARIO
<hr noshade="noshade" />
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<form action="inv_rep_ajustxlinea.php" method="post" name="formular">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>ALMACEN</td>
    <td>:</td>
    <td><?php
if(strlen($txtalma)>0) {
  $sqlao="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$txtalma."%' ";
//  echo $sqlao;
  $xsqlao=pg_exec($coneccion,$sqlao);
  $ilimitao=pg_numrows($xsqlao);
  if($ilimitao>0){
//  $codao=pg_result($xsqlao,0,0);
    $txtalma=pg_result($xsqlao,0,0);
    $descao=pg_result($xsqlao,0,1);
  }
}
?><input type="text" name="txtalma" size="7" value="<?php echo $txtalma;?>">
        <input type="submit" name="boton" value="Ok">
        <input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealma()">
        <?php echo $descao; ?></td>
  </tr>
  <tr>
    <td>FORMULARIO</td>
    <td>:</td>
      <td>
        <?php
if(strlen($txtform)>0) {
$sqlf="select tran_codigo,tran_descripcion,tran_naturaleza,tran_valor,tran_entidad,tran_referencia,tran_origen,tran_destino,tran_nform
 from inv_tipotransa where tran_codigo='".$txtform."' ";
// echo $sqlf;
$xsqlf=pg_exec($coneccion,$sqlf);
$ilimitf=pg_numrows($xsqlf);
  if($ilimitf>0){
//  $codao=pg_result($xsqlao,0,0);
    $codform=pg_result($xsqlf,0,0);
    $descform=pg_result($xsqlf,0,1);
  }
}
?>
        <input type="text" name="txtform" size="7" value="<?php echo $txtform;?>">
<input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreform()">
<?php echo $descform; ?></td>
  </tr>
  <tr>
    <td colspan="3">DEL
      <input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('formular.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">
      AL
     <input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('formular.diasa');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">
    </td>
  </tr>
  <tr>
    <td>(D)etallado / (R)esumido</td>
    <td>:</td>
      <td><input name="detoresum" type="text" size="7" maxlength="1" value="<?php echo $detoresum;?>"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><input type="submit" name="boton" value="Buscar" /></td>
  </tr>
</table>
  <p>&nbsp;</p>
<?php if($boton=="Buscar") {
 $ft=fopen('rep_ajustxlinea.csv','w');
 if ($ft>0) {
 	$fdesde=$diad."/".$mesd."/".$anod;
	$fhasta=$diaa."/".$mesa."/".$anoa;
	$snewbuffer=" MOVIMIENTOS VALORIZADOS POR TIPO DE FORMULARIO - ".$descao." - Formulario: ".$txtform." ".date("d/m/Y H:i:s")." \n";
	$snewbuffer=$snewbuffer." del ".$fdesde." al ".$fhasta."  -  Detallado/Resumido(D/R):".$detoresum." \n\n";
	$snewbuffer=$snewbuffer."FECHA,FORMULARIO,CODART,DESC. ARTICULO,CANTIDAD,UNITARIO,TOTAL,UNITARIO,TOTAL \n";
 }
?>
  <table border="1" cellspacing="0" cellpadding="0">
    <tr>
      <th>FECHA</th>
      <th>FORMULARIO</th>
      <th>CODIGO</th>
      <th>DESCRIPCION</th>
      <th>CANTIDAD</th>
      <th>UNITARIO</th>
      <th>TOTAL</th>
      <th>UNITARIO</th>
      <th>TOTAL</th>
    </tr>
<?php
if($detoresum=="D") {
$sql=" select m.mov_fecha,m.mov_numero,a.art_codigo,a.art_descripcion,m.mov_cantidad,mov_costounitario,
(m.mov_cantidad*m.mov_costounitario) as tot1,a.art_costoactual,(a.art_costoactual*m.mov_cantidad) as tot2,a.art_linea
 from int_articulos a, inv_movialma m
where a.art_codigo=m.art_codigo and m.mov_almacen='".$txtalma."' and m.tran_codigo='".$txtform."'
and m.mov_fecha between '".$fechad."' and '".$fechaa."' order by a.art_linea,a.art_codigo ";
}elseif($detoresum=="R"){
$sql=" select 0,0,0,0,0,0,
sum(m.mov_cantidad*m.mov_costounitario) as tot1,0,sum(a.art_costoactual*m.mov_cantidad) as tot2,a.art_linea
 from int_articulos a, inv_movialma m
where a.art_codigo=m.art_codigo and m.mov_almacen='".$txtalma."' and m.tran_codigo='".$txtform."'
and m.mov_fecha between '".$fechad."' and '".$fechaa."' group by a.art_linea,a.art_codigo ";
}
//echo $sql;
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {
	$a0=pg_result($xsql,$irow,0);
	$a1=pg_result($xsql,$irow,1);
	$a2=pg_result($xsql,$irow,2);
	$a3=pg_result($xsql,$irow,3);
	$a4=pg_result($xsql,$irow,4);
	$a5=pg_result($xsql,$irow,5);
	$a6=pg_result($xsql,$irow,6);
	$a7=pg_result($xsql,$irow,7);
	$a8=pg_result($xsql,$irow,8);
	$a9=pg_result($xsql,$irow,9);

	$ctotlin[$a9]=$ctotlin[$a9]+$a6;
	$ptotlin[$a9]=$ptotlin[$a9]+$a8;
	$tot6=$tot6+$a6;  $tot8=$tot8+$a8;
  if($detoresum=="D") {
	if($xlinea!=$a9) { nom_linea($coneccion,$a9);
		if($irow>0) {
//		echo "<tr><td colspan='2'>&nbsp;TOTAL ".$xa8." :  ".$ctdorxlinea[$xlinea]."</td><td>&nbsp;</td><td>&nbsp;</td><td align='right'>&nbsp;TOTAL HETEROGENEO</td><td align='right'>&nbsp;".number_format($totlin[$xlinea],4)."</td></tr>";
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td colspan='3'>&nbsp;TOTAL LINEA ".$xlinea." : </td><td>&nbsp;</td><td align='right'>&nbsp;".number_format($ctotlin[$xlinea],4)."</td><td>&nbsp;</td><td align='right'>&nbsp;".number_format($ptotlin[$xlinea],4)."</td></tr>";
		$snewbuffer=$snewbuffer.",,,,TOTAL LINEA ".$xlinea." : ,,".number_format($ctotlin[$xlinea],4).",,".number_format($ptotlin[$xlinea],4)." \n";
		}
		echo "<tr><td colspan='3'><b>&nbsp;*** ".$a9." - ".$zdesclin."</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
		$snewbuffer=$snewbuffer."*** ".$a9." - ".$zdesclin." \n";
	}

   echo "<tr><td>&nbsp;".$a0."</td><td>&nbsp;".$a1."</td>";
   echo "<td>&nbsp;".$a2."</td><td>&nbsp;".$a3."</td>";
   echo "<td align='right'>&nbsp;".number_format($a4,4)."</td><td align='right'>&nbsp;".number_format($a5,4)."</td>";
   echo "<td align='right'>&nbsp;".number_format($a6,4)."</td><td align='right'>&nbsp;".number_format($a7,4)."</td>";
   echo "<td align='right'>&nbsp;".number_format($a8,4)."</td></tr>";
   $snewbuffer=$snewbuffer.$a0.",".$a1.",".$a2.",".$a3.",".number_format($a4,4).",".number_format($a5,4).",".number_format($a6,4).",".number_format($a7,4).",".number_format($a8,4)."\n";
  }else{
  if($xlinea!=$a9) { nom_linea($coneccion,$a9);
    if($irow>0) {
  echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td colspan='3'>&nbsp;*** LINEA ".$xlinea." : </td><td>&nbsp;</td><td align='right'>&nbsp;".number_format($ctotlin[$xlinea],4)."</td><td>&nbsp;</td><td align='right'>&nbsp;".number_format($ptotlin[$xlinea],4)."</td></tr>";
  $snewbuffer=$snewbuffer.",,*** LINEA ".$xlinea." : ,,".number_format($ctotlin[$xlinea],4).",,".number_format($ptotlin[$xlinea],4)." \n";
    }
  }
 }	$irow++;
		if($irow==$ilimit)	{
	$ctdordisctin[$xlinea]++;
		 if($detoresum=="D") {
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td colspan='3'>&nbsp;TOTAL LINEA ".$a9." : </td><td>&nbsp;</td><td align='right'>&nbsp;".number_format($ctotlin[$a9],4)."</td><td>&nbsp;</td><td align='right'>&nbsp;".number_format($ptotlin[$a9],4)."</td></tr>";
	$snewbuffer=$snewbuffer.",,,,TOTAL LINEA ".$a9." :,,".number_format($ctotlin[$a9],4).",,".number_format($ptotlin[$a9],4)." \n";
		 }else{
  echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td colspan='3'>&nbsp;*** LINEA ".$a9." : </td><td>&nbsp;</td><td align='right'>&nbsp;".number_format($ctotlin[$a9],4)."</td><td>&nbsp;</td><td align='right'>&nbsp;".number_format($ptotlin[$a9],4)."</td></tr>";
  $snewbuffer=$snewbuffer.",,,,TOTAL LINEA ".$a9." :,,".number_format($ctotlin[$a9],4).",,".number_format($ptotlin[$a9],4)." \n";
		 }
	}
	 $xlinea=$a9;
}
?>
<!--    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>-->
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
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td colspan="3"><div align="right"><strong>TOTAL GENERAL</strong></div></td>
      <td align="right">&nbsp;<?php echo number_format($tot6,4);?></td>
      <td>&nbsp;</td>
      <td align="right">&nbsp;<?php echo number_format($tot8,4);?></td>
    </tr>
<?php
$snewbuffer=$snewbuffer."\n ,,,,TOTAL GENERAL ,,".number_format($tot6,4).",,".number_format($tot8,4)." \n";
?>
  </table>
    <br>&nbsp;
  <a href="rep_ajustxlinea.csv" target="_blank">Exportar a Excel</a> &nbsp;&nbsp;&nbsp; <!--<a href="rep_ajustxlinea.txt">Exportar
  a txt</a> -->
<?php
 fwrite($ft,$snewbuffer);
 fclose($ft);
 } ?>
</form>
<br>
</body>
</html>
<?php pg_close($coneccion); ?>
