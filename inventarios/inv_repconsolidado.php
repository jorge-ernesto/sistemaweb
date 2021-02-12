<?php
//include("../config.php");
//include("inc_top.php");
include("../menu_princ.php");
include("../functions.php");
/*obtenemos fecha*/
$nuevofechad = split('/',$_REQUEST['diasd']);
$diad=$nuevofechad[0];
$mesd=$nuevofechad[1];
$anod=$nuevofechad[2];

if($boton=="Buscar") {
	if(strlen($txtalma)>0) {
	$sqlqry="select ch_almacen,ch_nombre_almacen from inv_ta_almacenes where ch_almacen='".pg_escape_string($txtalma)."' ";
	}else{
	$sqlqry="select ch_almacen,ch_nombre_almacen from inv_ta_almacenes where (ch_almacen like '6%' or ch_almacen='018')and ch_almacen!='601' ";
	}
	$xsqlqry=pg_exec($coneccion,$sqlqry);
	$ilimit=pg_numrows($xsqlqry);

   if(strlen($linea)>0) {
	$xano=date("Y");
	if(strlen($linea)==6) {
	$sqlqry1="select distinct a.art_codigo,a.art_descripcion from inv_saldoalma s,int_articulos a where a.art_codigo=s.art_codigo and s.stk_periodo='".$anod."' and a.art_linea like '%".substr($linea,4,2)."%' and a.art_codigo!='0000000000000' ";
	}else{
	$sqlqry1="select distinct a.art_codigo,a.art_descripcion from inv_saldoalma s,int_articulos a where a.art_codigo=s.art_codigo and s.stk_periodo='".$anod."' and a.art_linea like '%".$linea."%' and a.art_codigo!='0000000000000' ";
	}
   }else{
	$sqlqry1="select distinct a.art_codigo,a.art_descripcion from inv_saldoalma s,int_articulos a where a.art_codigo=s.art_codigo and s.stk_periodo='".$anod."' and a.art_codigo!='0000000000000' ";
   }
//	echo $sqlqry1;
   $xsqlqry1=pg_exec($coneccion,$sqlqry1);
   $ilimit1=pg_numrows($xsqlqry1);
}
?>
<html>
<head>
<title>integrado</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script language="javascript">
var miPopup
function abrealma(){
    miPopup = window.open("almac.php","miwin","width=500,height=400,scrollbars=yes")
    miPopup.focus()
}
function abrelinea(){
    miPopup = window.open("escogelinea.php","miwin","width=500,height=400,scrollbars=yes")
    miPopup.focus()
}
function enviadatos(){
	document.formular.submit()
}
</script>
</head>
<body>
REPORTE DE STOCK CONSOLIDADO<br><hr noshade="noshade">
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<br>
<?php
if($flg=="A"){
	$combust="N";  $ofppl="N";  $saldoini="N";  $unidadval="U";  $diasdevta=0;  $ventacero="N";  $bajostk="N";  $stkminmax="N";
	$diad=date("d"); $mesd=date("m"); $anod=date("Y");  $stkcero="N";  $detalladoresum="N";  $stkcosto="N";
	$stknegat="N";  $bajostk="N";  $sobrestk="N";
}
?>
<form name="formular" action="inv_repconsolidado.php" method="post">
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td>Considerar Combustibles S/N</td>
      <td>:</td>
      <td><input name="combust" type="text" size="4" maxlength="1"value='<?php echo $combust;?>'>
        <input type="submit" name="boton" value="Ok"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Considerar Oficina Principal S/N</td>
      <td>:</td>
      <td><input name="ofppl" type="text" size="4" maxlength="1" value="<?php echo $ofppl;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php //if($flg!="A") { ?>
    <tr>
      <td>Almacen</td>
      <td>:</td>
      <td colspan="3">
<?php
if(strlen($txtalma)>0) {
  $sqlao="select ch_almacen,ch_nombre_almacen from inv_ta_almacenes where ch_almacen like '%".pg_escape_string($txtalma)."%' ";
//  echo $sqlao;
  $xsqlao=pg_exec($coneccion,$sqlao);
  $ilimitao=pg_numrows($xsqlao);
  if($ilimitao>0){
//  $codao=pg_result($xsqlao,0,0);
    $txtalma=pg_result($xsqlao,0,0);
    $descao=pg_result($xsqlao,0,1);
  }
}else{
     $descao="TODOS LOS ALMACENES";
}
?>
<input type="text" name="txtalma" size="10" value="<?php echo $txtalma;?>">
<input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealma()">
<?php echo $descao; ?>
</td>
    </tr>
    <tr>
      <td>Solo Saldos Iniciales S/N</td>
      <td>:</td>
      <td><input name="saldoini" type="text" size="4" maxlength="1" value="<?php echo $saldoini;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Ingrese L&iacute;nea</td>
      <td>:</td>
      <td colspan="3"><input name='linea' type='text' value='<?php echo $linea;?>' size='10' maxlength='6'>
                <input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrelinea()">
                <?php
  $sqllin="select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and (tab_elemento like '%".$linea."%' or tab_descripcion like '%".$linea."%')";
  $xsqllin=pg_exec($coneccion,$sqllin);
  $ilimitlin=pg_numrows($xsqllin);
  if($ilimitlin>0) {
    $codlinea=pg_result($xsqllin,0,0);	$desclinea=pg_result($xsqllin,0,1);
	$flglinea=pg_result($xsqllin,0,2);  echo $desclinea; //echo "<input type='hidden' name='flglinea' value='".$flglinea."'>";
  }
  ?></td>
    </tr>
    <tr>
      <td height="24">Fecha de Stock</td>
      <td>:</td>
      <td> <input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
	&nbsp;<a href="javascript:show_calendar('formular.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>(U)nidades (V)alores</td>
      <td>:</td>
      <td><input name="unidadval" type="text" size="4" maxlength="1" value="<?php echo $unidadval;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>D&iacute;as de Venta (0=Sin Venta)</td>
      <td>:</td>
      <td><input name="diasdevta" type="text" size="4" maxlength="1" value="<?php echo $diasdevta;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Sólo con Venta 0 (S/N)</td>
      <td>:</td>
      <td><input name="ventacero" type="text" size="4" maxlength="1" value="<?php echo $ventacero;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>-Bajo Stock Minimo(S/N)</td>
      <td>:</td>
      <td><input name="bajostk" type="text" size="4" maxlength="1" value="<?php echo $bajostk;?>"></td>
      <td>Calcula Stock Min. Max. (S/N)</td>
      <td><input type="text" name="stkminmax" size="5" maxlength="1" value="<?php echo $stkminmax;?>"></td>
    </tr>
    <tr>
      <td>+Sobre Stock Maximo(S/N)</td>
      <td>:</td>
      <td><input name="sobrestk" type="text" size="4" maxlength="1" value="<?php echo $sobrestk;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Stocks Negativos (-) (S/N)</td>
      <td>:</td>
      <td><input name="stknegat" type="text" size="4" maxlength="1" value="<?php echo $stknegat;?>"></td>
      <td>Stock Cero (S/N)</td>
      <td><input type="text" name="stkcero" size="5" maxlength="1" value="<?php echo $stkcero;?>"></td>
    </tr>
    <tr>
      <td>Stocks c/Costo &lt;= 0 (S/N)</td>
      <td>:</td>
      <td><input name="stkcosto" type="text" size="4" maxlength="1" value="<?php echo $stkcosto;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>(D)etallado / (R)esumido</td>
      <td>:</td>
      <td><input name="detalladoresum" type="text" size="4" maxlength="1" value="<?php echo $detalladoresum;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><input type="submit" name="boton" value="Buscar"></td>
      <td>&nbsp;</td>
    </tr>
    <?php //} ?>
  </table>
  <p>&nbsp;</p>
  <table border="1" cellspacing="0" cellpadding="0">
    <tr>
      <th>CODIGO</th>
      <th>DESCRIPCION</th>
<?php while($irow<$ilimit) {
	 $z0=pg_result($xsqlqry,$irow,0);
	 $z1=pg_result($xsqlqry,$irow,1);
     echo "<th>&nbsp;".$z1." - ".$z0."</th>";
	$irow++;
 }?>
      <th>TOTAL</th>
    </tr>
<?php
$irow1=0;
while($irow1<$ilimit1) {
	$y0=pg_result($xsqlqry1,$irow1,0);
	$y1=pg_result($xsqlqry1,$irow1,1);
?>
    <tr>
      <td>&nbsp;<?php echo $y0;?></td>
      <td>&nbsp;<?php echo $y1;?></td>
<?php
$irow=0;
while($irow<$ilimit) {
	 $z0=pg_result($xsqlqry,$irow,0);
	 $z1=pg_result($xsqlqry,$irow,1);
	 $codart=$y0;
	 calcula_stkactual($coneccion,$codart,$diad,$mesd,$anod,$z0);
//	 $stk[$irow1][$irow]=$stkini;
	 $stk[$irow1][$irow]=$stkini;
     echo "<td align='right'>&nbsp;<b>".$stkini."</b></td>";
	 $subtotal[$irow1]=$subtotal[$irow1]+$stkini;
//	 $subtotal[$irow1][$irow]=$subtotal[$irow1][$irow]+$stkini;
//	 $subtotxy[$irow1][$irow]=$subtotxy[$irow1][$irow]+$stk[$irow1][$irow];
	$irow++;
 }?>
      <td align='right'>&nbsp;<b><?php echo $subtotal[$irow1];?></b></td>
    </tr>
<?php
	$irow1++;
} ?>
    <tr>
      <td>&nbsp;</td>
      <th>SUB-TOTAL LINEA</th>
<?php
$irow=0;  $tot=0;
while($irow<$ilimit) {
$irow1=0;
	while($irow1<$ilimit1) {
		$subtot[$irow]=$subtot[$irow]+$stk[$irow1][$irow];
		$irow1++;
	}
	echo "<td align='right'>&nbsp;<b>".$subtot[$irow]."</b></th>";
	$tot=$tot+$subtot[$irow];
	$irow++;
 }?>
      <td align='right'>&nbsp;<b><?php echo $tot;?></b></td>
    </tr>
  </table>
  <p>&nbsp;</p>
</form>
</body>
</html>
<?php pg_close($coneccion); ?>