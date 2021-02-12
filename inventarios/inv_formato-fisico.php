<?php

include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/func_print_now.php");

if($flg=="A") { $rad1=""; $rad2=" checked";  $stkcero="N";
	$updalmaco=$almacen;
} else {
	$updalmaco=$almacen;
	if($orden=="C") { $rad1=" checked";  $rad2=""; } elseif($orden=="D") { $rad1=""; $rad2=" checked"; }
}

/*if($imprimir=="ok")
{
	exec("smbclient //server14nw/epson -c 'print /sistemaweb/inventarios/$archivo' -P -N -I 192.168.1.1 ");
}*/

/*
*
*	PONER 'D' si quiere que se complete a la derecha
*	PONER 'I' si quiere que se complete a la izquierda
*
*/

function completarEspacios($longitud, $palabra, $caracter, $Der_O_Izq){
	$palabra = trim($palabra);
	$long_inicial = strlen($palabra);

	for($i=0;$i<$longitud - $long_inicial;$i++){
		if($Der_O_Izq=="D") {
			$palabra = $palabra.$caracter;
		} else {
			$palabra = $caracter.$palabra;
		}
	}
	return $palabra;
}
?>
<html>
<head>
<title>Sistema OpenSoft - Formato de Toma de Inventario</title>
<script language="javascript">
var miPopup
function abrealmao(){
    	miPopup = window.open("almac.php","miwin","width=500,height=400,scrollbars=yes")
    	miPopup.focus()
}

function abreubica(){
	<?php $url="ubicac.php?ch_almacen=$updalmaco";
	//echo $url;
    	echo 'miPopup = window.open("'.$url.'","miwin","width=500,height=400,scrollbars=yes")'; ?>
	//miPopup = window.open("ubicac.php","miwin","width=500,height=400,scrollbars=yes")
    	miPopup.focus()
}

function enviadatos(){
	document.formular.submit();
}
</script>
</head>
<body>
FORMATO DE INVENTARIO FISICO
<hr noshade="noshade" />
<form name="formular" action="inv_formato-fisico.php" method="post">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
      		<th>ALMACEN</th>
      		<td>:</td>
      		<td><input name="txtalma" type="text" size="6" maxlength="3" value='<?php echo $updalmaco;?>' <?php echo $forig1; ?>>
        	<input type="submit" name="boton2" value="Ok">
        	<input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealmao()">
		<?php
		if(strlen($updalmaco)>0) {
			//$sqlao="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$updalmaco."%' ";
			$sqlao="select ch_almacen,ch_nombre_almacen from inv_ta_almacenes where ch_almacen like '%".$updalmaco."%' ";
			$xsqlao=pg_exec($coneccion,$sqlao);
			$ilimitao=pg_numrows($xsqlao);
			if($ilimitao>0){
				$codao=pg_result($xsqlao,0,0);
				$descao=pg_result($xsqlao,0,1);	echo $descao;
			}
		}
		?>
		</td>
    	</tr>
    	<tr>
      		<td >UBICACION</td>
      		<td >:</td>
      		<td ><input name="ubicac" type="text" value="<?php echo $ubicac; ?>" size="10" maxlength="6">
		<?php
		if(strlen($ubicac)>0) {
  			if ($ubicac=="TODOS") {
    				$sqlao="select cod_ubicac,desc_ubicac from inv_ta_ubicacion where cod_almacen='".$almacen."' ";
    			}else {
    				$sqlao="select cod_ubicac,desc_ubicac from inv_ta_ubicacion where cod_ubicac like '%".$ubicac."%' and cod_almacen='".$almacen."' ";
    			}
  			//  echo $sqlao;
  			$xsqlao=pg_exec($coneccion,$sqlao);
  			$ilimitao=pg_numrows($xsqlao);
  			if($ilimitao>0){
    				$txtalma=pg_result($xsqlao,0,0);
    				$descubic=pg_result($xsqlao,0,1);
  			}
		}
		?>
    		<input name="imgubica" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreubica()">
      		<?php echo $descubic; ?>
	  	</td>
    	</tr>
    	<tr>
      		<td colspan="3">ORDEN: &nbsp;&nbsp; <input type="radio" name="orden" value="C" <?php echo $rad1;?>>
        	C&oacute;digo
        	<input type="radio" name="orden" value="D" <?php echo $rad2;?>>
        	Descripci&oacute;n</td>
    	</tr>
    	<tr>
      		<td>CONSIDERAR STOCK CERO (0) S/N</td>
      		<td>:</td>
      		<td><input name="stkcero" type="text" value="<?php echo $stkcero; ?>" size="7" maxlength="1"></td>
    	</tr>
   	<tr>
      		<td>&nbsp;</td>
      		<td>&nbsp;</td>
      		<td><input type="submit" name="boton" value="Buscar"></td>
	</tr>
</table>
<br>
<table  border="1" cellspacing="0" cellpadding="0">
	<tr>
      		<th >CODIGO</th>
      		<th >NOMBRE DEL PLU</th>
      		<th >UBICACION</th>
      		<th >PRECIO</th>
      		<th >STOCK</th>
      		<th >INVENTARIO FISICO</th>
    	</tr>
	<?php
	if($boton=="Buscar") {
	?>
  	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="/sistemaweb/utils/impresiones.php?imprimir=paginar&cabecera=/sistemaweb/inventarios/formato-invfis_cabecera.txt&cuerpo=/sistemaweb/inventarios/formato-invfis_cuerpo.txt&archivo_final=/sistemaweb/inventarios/formato_invfis.txt" target="_blank">Imprimir</a>
	<?php
	
$ft = fopen('formato-invfis_cuerpo.txt','w+');
$ft_cab = fopen('formato-invfis_cabecera.txt','w+');
if ($ft>0) {
	$snewbuffer=$snewbuffer."                           FORMATO DE INVENTARIO FISICO                             \n";
	$snewbuffer=$snewbuffer."\t    ".$almacen." - ".pg_result(pg_query($coneccion, "select trim(ch_nombre_almacen) from inv_ta_almacenes where ch_almacen='$almacen'"),0,0);
	$snewbuffer=$snewbuffer." - UBIC. ".$txtalma." - ".$descubic."  \n\n";
	$snewbuffer=$snewbuffer."CODIGO\t\tNOMBRE     \t\t\tPRECIO \t STOCK   INVENTARIO FISICO \n";
	$snewbuffer=$snewbuffer."=========================================================================================\n";
}
fwrite($ft_cab,$snewbuffer);
fclose($ft_cab); 
$snewbuffer="";

$aud=date("d/m/Y H:i:s")."-".$user;

$xsqlupdflgubic=pg_exec($coneccion,"update inv_ta_ubicacion set flg_ubicac='1',audit='".$aud."' where cod_ubicac='".$ubicac."' and cod_almacen='".$almacen."' ");

$mesact=date("m"); $anoact=date("Y");

if($stkcero=="N"){
	$mayomen=" and s.stk_stock".$mesact."!=0 ";
}

if($orden=="C"){
	$ord=" order by a.art_codigo";
}elseif($orden=="D"){
	$ord=" order by a.art_descripcion";
}

if(strlen($ubicac)>0){
  	if ($ubicac=="TODOS"){
		$ubic=" true ";
	}else{
		$ubic=" a.art_cod_ubicac='".$ubicac."' ";
	}
}else{
	$ubic=" a.art_cod_ubicac isnull ";
}

$sql = "SELECT 
		a.art_codigo,
		a.art_descripcion,
		a.art_costoactual,
		round(s.stk_stock".$mesact.",2),
		stk_fisico".$mesact."
	FROM 
		int_articulos a,
		inv_saldoalma s
	WHERE 
		s.art_codigo=a.art_codigo 
		AND stk_periodo='".$anoact."' 
		AND s.stk_almacen='".$updalmaco."' ".$ubic." ".$mayomen." ".$ord." ";

$sql = "SELECT 
		a.art_codigo, 
		a.art_descripcion , 
		round(a.art_costoactual,2), 
		round(s.stk_stock".$mesact.",2), 
		round(stk_fisico".$mesact.",2), 
		a.art_cod_ubicac 
	FROM 
		int_articulos a 
		LEFT JOIN inv_saldoalma s ON  a.art_codigo=s.art_codigo AND stk_periodo='".$anoact."' AND s.stk_almacen='".$updalmaco."' 
	WHERE ".$ubic." ".$mayomen." ".$ord;

/*echo "<pre>";
echo print_r($sql);
echo "</pre>";*/

$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {
	$a0=pg_result($xsql,$irow,0);
	$a1=pg_result($xsql,$irow,1);
	$a2=pg_result(pg_query($coneccion, "select round(util_fn_precio_articulo('$a0'),2)"),0,0);
	$a3=pg_result($xsql,$irow,3);
	$a4=pg_result($xsql,$irow,4);
	$a5=pg_result($xsql,$irow,5);

	$at=$at+$a3;
	echo "<tr><td>&nbsp;".$a0."</td><td>&nbsp;".$a1."</td>";
	echo "<td>&nbsp;".$a5."</td>";
	echo "<td align='right'>&nbsp;".$a2."</td><td align='right'>&nbsp;".$a3."</td>";
	echo "<td>______________________</td></tr>";

	$snewbuffer=$snewbuffer.$a0."\t".completarEspacios(30,substr($a1,0,30)," ","D")."\t".$a5."\t".$a2."\t".completarEspacios(5,$a3," ","I")." \t _______________________\n";
	$irow++;
}
?>
    	<tr>
      		<td>&nbsp;</td>
      		<td>&nbsp;</td>
      		<td><div align="right"><b>TOTAL</b></div></td>
      		<td align='right'>&nbsp;<?php echo number_format($at,2);?></td>
      		<td>&nbsp;</td>
    	</tr>
</table>
<?php
$snewbuffer=$snewbuffer."=========================================================================================\n";
$snewbuffer=$snewbuffer."\t\t\t\t\t\tTOTAL   ".number_format($at,2)."\n";
fwrite($ft,$snewbuffer);
fclose($ft); ?>
<p>&nbsp;&nbsp;&nbsp;
	<a href="formato_invfis.txt" target="_blank">
		Exportar a txt
	</a>
</p>
<?php } ?>
</form>
<p>&nbsp;</p>
</body>
</html>
<?php pg_close($coneccion); ?>
