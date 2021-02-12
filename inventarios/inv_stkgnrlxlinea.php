<?php

include("../menu_princ.php");

include("../functions.php");

$almacen = $_SESSION['usuario']->almacenActual;

$flg = $_GET['flg'];

if ($flg == "A") {
	$soloconstk = "S";
	$diad       = date("d"); 
	$mesd       = date("m"); 
	$anod       = date("Y");
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>integrado</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<script language="javascript">
		var miPopup
		function abrelinea(){
		    miPopup = window.open("escogelinea.php","miwin","width=500,height=400,scrollbars=yes")
		    miPopup.focus()
		}
		function abrealma(){
		    miPopup = window.open("/sistemaweb/menu/archivos/almac.php","miwin","width=500,height=400,scrollbars=yes")
		    miPopup.focus()
		}
		function enviadatos(){
			document.formular.submit()
		}
	</script>
</head>
<body>
STOCKS GENERAL POR LINEA
<hr noshade="noshade">
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
<form name='formular' action="inv_stkgnrlxlinea.php" method="post">
	<table border="1" cellspacing="0" cellpadding="0">
		<tr>
			<td>Fecha</td>
			<td><input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
			&nbsp;<a href="javascript:show_calendar('formular.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></td>
			Almacen Actual:<?php echo " ".$almacen." - "; $sqlca->query("SELECT ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen = '" . $almacen ."'"); $a = $sqlca->fetchRow(); echo $a[0];?>		
</tr>
		<!--
		<tr>
			<td>C&oacute;digo de Almac&eacute;n:</td>
			<td>
			<?php
			if (strlen($txtalma) > 0) {
				$sqlao="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento like '%".$txtalma."%' ";
				//echo $sqlao;
				$xsqlao=pg_exec($coneccion,$sqlao);
				$ilimitao=pg_numrows($xsqlao);
				if($ilimitao > 0){
					//$codao=pg_result($xsqlao,0,0);
					$txtalma=pg_result($xsqlao,0,0);
					$descao=pg_result($xsqlao,0,1);
				}
			}else{
				$descao="TODOS LOS ALMACENES";
			}
		?>

		<input type="text" name="txtalma" size="10" value="<?php echo $txtalma;?>">
		<input type="submit" name="boton" value="Ok"> <input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealma()">
		<?php echo $descao; ?></td>
		</tr> -->
		<tr>
			<td>C&oacute;digo de L&iacute;nea:</td>
			<td>
			<?php
			if(strlen($linea)>0) {
				$sqllin    = "select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and (tab_elemento like '%".$linea."%' or tab_descripcion like '%".$linea."%')";
				$xsqllin   = pg_exec($coneccion,$sqllin);
				$ilimitlin = pg_numrows($xsqllin);
				if ($ilimitlin > 0) {
					$linea     = pg_result($xsqllin,0,0);	
					$desclinea = pg_result($xsqllin,0,1);
					$flglinea  = pg_result($xsqllin,0,2); //echo "<input type='hidden' name='flglinea' value='".$flglinea."'>";
  				}
			} else { 	
				$desclinea="Todas las líneas"; 
			} 
			?>
			<input name='linea' type='text' value='<?php echo $linea;?>' size='10' maxlength='6'>
			<input type="submit" name="boton" value="Ok">
			<input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onclick="abrelinea()">
			<?php echo $desclinea; ?>
		 	
		</td>
	</tr>
	<tr>
		<td>S&oacute;lo con stock:</td>
		<td><input name="soloconstk" type="text" value="<?php echo $soloconstk;?>" size="4" maxlength="1"></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input name="boton" type="submit" value="Buscar"></td>
	</tr>
</table>
<p>&nbsp;</p>

<?php
if(isset($_POST)) {
	var_dump($_POST);
	$boton = $_POST['boton'];
	$soloconstk = $_POST['soloconstk'];

	$nuevofechad = split('/',$_POST['diasd']);
	$diad	     = $nuevofechad[0];
	$mesd	     = $nuevofechad[1];
	$anod 	     = $nuevofechad[2];

	var_dump($nuevofechad);
	//exit();
if ($boton == "Buscar") { ?>
<table border="1" cellspacing="0" cellpadding="0">
	<tr>
		<th>CODIGO</th>
		<th>ARTICULO</th>
		<th>UNID</th>
		<th>STK TOTAL</th>
		<th>COSTO</th>
		<th>VALOR TOTAL</th>
	</tr>
	<?php
	$ft = fopen('stkgnrlxlinea.csv','w');
	echo '<hr>$ft: ';
	var_dump($ft);
	echo '<hr>';
	if ($ft > 0) {
		$snewbuffer = " STOCKS GENERAL POR LINEA \n\n";
		$snewbuffer = $snewbuffer."CODART,DESC. ARTICULO,UNID,STK TOTAL,COSTO,VALOR TOTAL \n";
	}
	if ($soloconstk == "S") { 
		$sqladd = " and s.stk_stock".$mesd." >0 "; 
	}
	$linea  = substr($linea,4,2);

	//$almacen = $_SESSION["almacen"];
	
 
	$sql = "SELECT 
			ar.art_codigo,
			ar.art_descripcion,
			ar.art_unidad,
			sum(s.stk_stock".$mesd."),
			sum(s.stk_costo".$mesd."),
			sum(s.stk_stock".$mesd."*s.stk_costo".$mesd.") as subtot,
			ar.art_linea
		FROM 
			inv_saldoalma s,
			int_articulos ar
		WHERE 
			art_linea like '%".$linea."' 
			AND s.stk_periodo='".$anod."' 
			AND s.art_codigo=ar.art_codigo
			AND ar.art_plutipo!='2'
			AND ar.art_plutipo!='3' ".$sqladd."
			AND s.stk_almacen='".$almacen."'
  
		GROUP BY 
			ar.art_codigo,
			ar.art_descripcion,
			ar.art_unidad,
			art_linea,
			s.art_codigo 
		ORDER BY 
			ar.art_linea,
			s.art_codigo";

	$xsql	= pg_exec($coneccion,$sql);
	$ilimit	= pg_numrows($xsql);
	$xalma	= "";  
	$xlinea = ""; 
	$xa0	= "";

	var_dump($sql);
	exit();

	while($irow < $ilimit) {
		$a0 = pg_result($xsql,$irow,0);
		$a1 = pg_result($xsql,$irow,1);
		$a2 = pg_result($xsql,$irow,2);
		$a3 = pg_result($xsql,$irow,3);
		$a4 = pg_result($xsql,$irow,4);
		$a5 = pg_result($xsql,$irow,5);
		$a6 = pg_result($xsql,$irow,6);

		$stktotart[$a0]	= $stktotart[$a0]    + $a3;
		$ctototart[$a0]	= $ctostktotart[$a0] + $a4;
		$vttotart[$a0]	= $vttotart[$a0]     + $a5;
		$totlin[$a6]	= $totlin[$a6]       + $a5;
		$ctdorxlinea[$a6]++;

		if ($xlinea != $a6) { 
			nom_linea($coneccion,$a6);
			if ($irow > 0) {
				echo "<tr><td colspan='2'>&nbsp;TOTAL :  ".$ctdordisctin[$xlinea]."</td><td>&nbsp;</td><td>&nbsp;</td><td align='right'>&nbsp;TOTAL HETEROGENEO</td><td align='right'>&nbsp;".number_format($totlin[$xlinea],4)."</td></tr>";
				$snewbuffer = $snewbuffer."TOTAL :  ".$ctdordisctin[$xlinea].",,,,TOTAL HETEROGENEO ,".number_format($totlin[$xlinea],4)." \n";
			}
			echo "<tr><td colspan='3'><b>&nbsp;*** ".$a6." - ".$zdesclin."</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
			$snewbuffer = $snewbuffer."*** ".$a6." - ".$zdesclin." \n";
		}

		$ctdordisctin[$xlinea]++;
		echo "<tr><td>&nbsp;".$a0."</td><td>&nbsp;".$a1."</td>";
		echo "<td>&nbsp;".$a2."</td><td align='right'>&nbsp;".$stktotart[$a0]."</td>";
		echo "<td align='right'>&nbsp;".$ctototart[$a0]."</td><td align='right'>&nbsp;".number_format($vttotart[$a0],4)."</td></tr>";
		$snewbuffer=$snewbuffer."".$a0.",".$a1.",".$a2.",".$stktotart[$a0].",".$ctototart[$a0].",".number_format($vttotart[$a0],4)." \n";		
		$irow++;

		if ($irow == $ilimit) {
			$ctdordisctin[$xlinea]++;
			echo "<tr><td colspan='2'>&nbsp;TOTAL  :  ".$ctdordisctin[$xlinea]."</td><td>&nbsp;</td><td>&nbsp;</td><td align='right'>&nbsp;TOTAL HETEROGENEO</td><td align='right'>&nbsp;".number_format($totlin[$xlinea],4)."</td></tr>";
			$snewbuffer = $snewbuffer."TOTAL  :  ".$ctdordisctin[$xlinea].",,,,TOTAL HETEROGENEO,".number_format($totlin[$xlinea],4)." \n";
		}
		$xalma	= $a7;  
		$xlinea = $a6;  
		$xa8	= $a8;  
		$xa0	= $a0; 
		$xa1	= $a1; 
		$xa2	= $a2;
	}fwrite($ft,$snewbuffer);
	fclose($ft);
}
}
?>
	</table>
</form>
<button name="fm" value="" onClick="javascript:parent.location.href='stkgnrlxlinea.csv';return false"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Excel</button>
</body>
</html>
<?php pg_close($coneccion); ?>