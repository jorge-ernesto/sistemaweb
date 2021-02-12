<?php
//include("../config.php");
include("../menu_princ.php");
/*obtenemos fecha*/
$nuevofechad = split('/',$_REQUEST['diasd']);
$diad=$nuevofechad[0];
$mesd=$nuevofechad[1];
$anod=$nuevofechad[2];
$nuevofechaa = split('/',$_REQUEST['diasa']);
$diaa=$nuevofechaa[0];
$mesa=$nuevofechaa[1];
$anoa=$nuevofechaa[2];
include("../functions.php");
if($flg=="A") {
	rangodefechas();
	$diad=$zdiad; $mesd=$zmesd; $anod=$zanod; $diaa=$zdiaa; $mesa=$zmesa; $anoa=$zanoa;
}
?>
<html>
<head>
<title>integrado</title>
<script language="javascript">
var miPopup
function abreart(){
    miPopup = window.open("escogeart.php","miwin","width=600,height=350,scrollbars=yes")
    miPopup.focus()
}
function abreform(){
    miPopup = window.open("escogeform.php","miwin","width=500,height=350,scrollbars=yes")
    miPopup.focus()
}

function enviadatos(){
	document.formular.submit()
}
</script>
</head>
<body>
MOVIMIENTOS POR ALMACEN DESTINO <hr noshade><br>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<form name="formular" action="" method="post">
  <table border="0" cellspacing="2" cellpadding="2">
    <tr>
      <td><b>Formulario: </b>
<?php if($flg=="A") { $tipform=14;  } ?>
        <input type="text" size="04" maxlength="02" name="tipform" value="<?php echo $tipform;?>">
		<input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreform()">
<?php
if(strlen($tipform)>0) {
	$xsqltipform=pg_exec($coneccion,"select tran_descripcion from inv_tipotransa where tran_codigo='".$tipform."' ");
	if(pg_numrows($xsqltipform)>0) { $desctipform=pg_result($xsqltipform,0,0);  echo $desctipform; }
}
?><br/><br/>
		</td>
    </tr>
    <tr>
      <th align="left">Del:
      <input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('formular.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"/></div><br/><br/>
      Al:&nbsp; <input type="text" name="diasa" size="10" value="<?php echo $diaa.'/'.$mesa.'/'.$anoa ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('formular.diasa');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></th>
    </tr>
    <tr>
      <td><b>Art&iacute;culo:</b>
        <input type="text" name="artic" value="<?php echo $artic; ?>" size="17" maxlength="13">
		<input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreart()">
<?php
if(strlen($artic)>0) {
	$xsqlart=pg_exec($coneccion,"select art_descripcion from int_articulos where art_codigo='".$artic."' ");
	if(pg_numrows($xsqlart)>0) { $descart=pg_result($xsqlart,0,0);  echo $descart; }
}
?><br/><br/>
		</td>
    </tr>
    <tr>
      <td align="center"><input type="submit" name="boton" value="Buscar"></td>
    </tr>
    </table>
  <br>
<?php
		$fechad=$anod."/".$mesd."/".$diad;
		$fechaa=$anoa."/".$mesa."/".$diaa;
$sqlqry=" select distinct a.tab_elemento,a.tab_descripcion from inv_movialma m,int_tabla_general a where (m.mov_fecha between '".$fechad."' and '".$fechaa."') and
a.tab_tabla='ALMA' and a.tab_elemento=m.mov_almadestino and m.art_codigo='".$artic."' and m.tran_codigo='".$tipform."' ";
//echo $sqlqry;
//and m.mov_almacen>700
//$sqlqry=" select distinct mov_numero,tran_codigo,art_codigo,mov_fecha,mov_almacen from inv_movialma where mov_fecha between '".$fechad."' and '".$fechaa."' ";
//$sqlao="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$updalmaco."%' ";
$xsqlqry=pg_exec($coneccion,$sqlqry);
$ilimitc=pg_numrows($xsqlqry);

$ft=fopen('mov_xalmacdest.csv','w');
	if ($ft>0) {
		$snewbuffer="MOVIMIENTOS POR ALMACEN DESTINO \n";
		$snewbuffer=$snewbuffer."FORMULARIO : ".$tipform." - ".$desctipform." \n";
		$snewbuffer=$snewbuffer."DEL ".$fechad." AL ".$fechaa." \n";
		$snewbuffer=$snewbuffer."COD. ART :".$artic." \n";
	}
?>
  <table border="1" cellspacing="0" cellpadding="0">
    <tr>
      <th>FECHA</th>
<?php
   $snewbuffer=$snewbuffer."FECHA,";
   while($irowc<$ilimitc) {
	  $alm0[$irowc]=pg_result($xsqlqry,$irowc,0);
	  $alm1[$irowc]=pg_result($xsqlqry,$irowc,1);
      echo "<th>&nbsp;".$alm1[$irowc]."</th>";
	  $snewbuffer=$snewbuffer.$alm1[$irowc].",";
     $irowc++;
   }
   $irowc=0;
   $snewbuffer=$snewbuffer.",TOTAL \n";
?>
      <th>TOTAL</th>
    </tr>
<?php
$sqlqry1=" select distinct mov_fecha from inv_movialma where (mov_fecha between '".$fechad."' and '".$fechaa."')
and art_codigo='".$artic."' and tran_codigo='".$tipform."' ";
//echo $sqlqry11;
$xsqlqry1=pg_exec($coneccion,$sqlqry1);
$ilimitc1=pg_numrows($xsqlqry1);
$irowc1=0;
while($irowc1<$ilimitc1) {
	$sfecha=pg_result($xsqlqry1,$irowc1);
    echo "<tr><th>&nbsp;".$sfecha."</th>";
	$snewbuffer=$snewbuffer.$sfecha.",";
	$irowc=0;
	while($irowc<$ilimitc) {
	  $alm0[$irowc]=pg_result($xsqlqry,$irowc,0);
	  calculastkxdia($coneccion,$sfecha,$artic,$tipform,$alm0[$irowc]);
      echo "<td align='right'>&nbsp;".$sumtotxalma."</td>";
	  $snewbuffer=$snewbuffer.$sumtotxalma.",";
	  $tsumtotxalma=$tsumtotxalma+$sumtotxalma;
	  $xstkalmac[$irowc1][$irowc]=$sumtotxalma;
      $irowc++;
   }
   $irowc=0;
   echo "<td align='right'>&nbsp;".$tsumtotxalma."</td></tr>";
	$snewbuffer=$snewbuffer.$tsumtotxalma."\n";
 $irowc1++;
}
$irowc1=0; $irowc=0;
echo "<tr><th>TOTAL</th>";
$snewbuffer=$snewbuffer."TOTAL,";
while($irowc<$ilimitc) {
  $irowc1=0;
  while($irowc1<$ilimitc1) {
  $totxalma[$irowc]=$totxalma[$irowc]+$xstkalmac[$irowc1][$irowc];
//  echo "hola".$xstkalmac[$irowc1][$irowc];
  $irowc1++;
  }
  echo "<td align='right'>&nbsp;".$totxalma[$irowc]."</td>";
  $snewbuffer=$snewbuffer.$totxalma[$irowc].",";
  $irowc++;
}
echo "<td>&nbsp;</td></tr>";
?>

  </table>
  <p><a href="mov_xalmacdest.csv" target="_blank">exportar a excel</a></p>
<?php
fwrite($ft,$snewbuffer);
fclose($ft);?>
</form>
</body>
</html>
<?php pg_close($coneccion); ?>