<?php
 
$newcodart = $artic;
include("../config.php");
include("inc_top.php");
include("../functions.php");

obteneralmamov($fm,$nromov,$coneccion);
tipoform($fm,$coneccion);

if(trim($naturform) == "1" or trim($naturform) == "2") { 
	$flag_bot_almacd = "F";
	$fdest		 = " disabled";
	$fdest1		 = " readonly style='background-color: #CCCCCC' ";
	$updalmac	 = $updalmacd;
	$updnatu	 = "3";
	$addalma1	 = " and tab_car_02='2' ";  
	$addalma2	 = "";
	$flagcito	 = "B";
} elseif(trim($naturform) == '3' or trim($naturform) == '4') {
	$flag_bot_almaco = "F";
	$forig		 = " disabled";
	$forig1		 = " readonly style='background-color: #CCCCCC' ";
	$updalmac	 = $updalmaco;
	$updnatu	 = '3';
	$addalma1	 = "";
	$addalma2	 = " and (tab_car_02='3' or tab_car_02='1') ";
	$flagcito	 = "A";
} else { 
	$fdest = "";  	
	$flagcito = "A"; 
}

if($boton == "Insertar") {
	$newcodart = $artic;

	if(strlen(trim($newcodart)) > 0) {
		$newcodartic = $newcodart;
		valida_existe_art($coneccion,$newcodart);
	} else {
		$msg_art = " Cód Artículo ";
	}

	if(strlen(trim($updalmaco1)) > 0) {
		$updalmaco = $updalmaco1;
		valida_almo($coneccion,$updalmaco1);
	}

	if(strlen(trim($updalmacd1)) > 0) {
		$updalmacd = $updalmacd1;
		valida_almd($coneccion,$updalmacd1);
	}

	if(strlen(trim($updtipodocref1)) > 0) {
		$updtipodocref = $updtipodocref1;
		valida_docr($coneccion,$updtipodocref1);
	}

	if(strlen(trim($updprov1)) > 0) {
		$updprov = $updprov1;
		valida_prov($coneccion,$updprov1);
	}

	$updnrodocref = $updnrodocref1.$updnrodocref2;
	if(strlen(trim($msg_art)) == 0 and strlen(trim($f_valart)) == 0 and strlen($f_almo) == 0 and strlen($f_almd) == 0 and strlen($f_prov) == 0 and strlen($f_tipodoc) == 0) {
		inserta_item_mov($coneccion,$nromov,$fm,$newcodartic,$fecmov,$updalmac,$updalmaco,$updalmacd,$updnatu,$updtipodocref,$updnrodocref,$updprov,$newcant,$flagcito,$newcostounit,$entform);
		echo $msg_insert;
	} else {  
		$cadena_tot = $msg_art ." ".$f_almo." ".$f_almd." ".$f_prov." ".$f_tipodoc." ".$f_valart." incorrectos !!!";
		?><script>
			alert(" <?php echo $cadena_tot; ?> ")
		</script><?php
	}
} elseif($boton == "Eliminar") {
	$sqlcant = "SELECT mov_cantidad FROM inv_movialma WHERE mov_numero='$nromov' AND tran_codigo='$fm' AND art_codigo='$codart' ";
	$xsqlcant = pg_exec($coneccion,$sqlcant);
	if(pg_numrows($xsqlcant) > 0) { 
		$cantart = pg_result($xsqlcant,0,0);
		$sqlinsdet = "DELETE FROM inv_movialma WHERE mov_numero='$nromov' AND tran_codigo='$fm' AND art_codigo='$codart' ";
		$xsqlinsdet = pg_exec($coneccion,$sqlinsdet);
		recalcula_costo_art($codart,$cantart);
	}
} elseif($boton == "Regresar") {
	?><script languaje="JavaScript">
		//location.href='movdalmacen.php?fm=<?php echo $fm;?>&nromov=<?php echo $nromov;?>';
		location.href='inv_movdalmacen.php?fm=<?php echo $fm;?>&flg=A';
	</script><?php
} elseif($boton == "Modificar cabecera") {
	$sqlupdc = "	UPDATE
				inv_movialma 
			SET
				mov_almacen='$updalmac',
				mov_almaorigen='$updalmaco',
				mov_almadestino='$updalmacd',
				mov_tipdocuref='$updtipodocref',
				mov_docurefe='$updnrodocref1$updnrodocref2',
				mov_entidad='$updprov'
			WHERE
				mov_numero='$nromov' 
				AND tran_codigo='$fm' ";
	// echo $sqlupdc;
	$xsqlupdc = pg_exec($coneccion,$sqlupdc);
}

if($flg == "A") {
	obtenermov($fm,$nromov,$coneccion);
	$updnrodocref1 = substr($movnrodocref,0,3);
	$updnrodocref2 = substr($movnrodocref,3,7);
}
?>

<html><head>

<script language="javascript">
var miPopup
function abrealmao() {
	miPopup = window.open("almaco.php","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
}
function abrealmad() {
	miPopup = window.open("almad.php","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
}
function abreprov() {
	miPopup = window.open("prov.php","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
}
function abretipodoc() {
	miPopup = window.open("tipodoc.php","miwin","width=500,height=400,scrollbars=yes")
	miPopup.focus()
}
function abreart() {
	miPopup = window.open("escogeart.php","miwin","width=600,height=350,scrollbars=yes")
	miPopup.focus()
}
function enviadatos() {
	document.formular.submit()
}
</script>
</head><body>

<form name='formular' action="inv_updmov.php?fm=<?php echo $fm;?>&nromov=<?php echo $nromov;?>" method="post">
<input type="hidden" name="fm" value="<?php echo $fm;?>">
<input type="hidden" name="fecmov" value="<?php echo $movfecha;?>">
<input type="hidden" name="nromov" value="<?php echo $nromov;?>">
<table border="1">
	<tr>
		<th width="211">FORMULARIO</th>
		<td width="8">:</td>
		<td width="218">&nbsp;<?php echo $fm."-".$descform;?></td>
	</tr>
	<tr>
		<th>N&deg; FORMULARIO</th>
		<td>:</td>
		<td>&nbsp;<?php echo $nromov;?></td>
	</tr>
	<tr>
		<th>FECHA</th>
		<td>:</td>
		<td>&nbsp;<?php echo $movfecha;?><input type="hidden" name="movfecha" value="<?php echo $movfecha;?>"><input type="submit" name="boton3" value="Ok" onClick="enviadatos()"></td>
	</tr>
	<tr>
		<th>ALMACEN ORIGEN</th>
		<td>:</td>
		<td><input name="updalmaco" type="text" size="6" maxlength="3" value='<?php echo $updalmaco;?>' <?php echo $forig1; ?>>
		<?php if($flag_bot_almaco == "F") {?>
		<?php } else {?>
			  <input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealmao()">
		<?php }?>
		<?php
		if(strlen($updalmaco) > 0) {
			$sqlao    = "SELECT tab_elemento,tab_descripcion FROM int_tabla_general WHERE tab_tabla='ALMA' AND tab_elemento LIKE '%".$updalmaco."%' ";
			$xsqlao   = pg_exec($coneccion,$sqlao);
			$ilimitao = pg_numrows($xsqlao);
			if($ilimitao > 0) {
			$codao    = pg_result($xsqlao,0,0);
			$descao   = pg_result($xsqlao,0,1);	
			echo $descao;
			}
		}
		?>
		</td>
	</tr>
	<tr>
		<th>ALMACEN DESTINO</th>
		<td>:</td>
		<td><input name="updalmacd" type="text" size="6" maxlength="3"  value='<?php echo $updalmacd;?>' <?php echo $fdest1; ?>>
		<?php if($flag_bot_almacd == "F") { ?>
		<?php }else{?>
			<input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealmad()">
		<?php }?>
		<?php
		if(strlen($updalmacd) > 0) {
			$sqlad    = "SELECT tab_elemento,tab_descripcion FROM int_tabla_general WHERE tab_tabla='ALMA' AND tab_elemento LIKE '%".$updalmacd."%' ";
			// echo $sqlao;
			$xsqlad   = pg_exec($coneccion,$sqlad);
			$ilimitad = pg_numrows($xsqlad);
			$codad    = pg_result($xsqlad,0,0);
			$descad   = pg_result($xsqlad,0,1);	
			echo $descad;
		}
		?>
		</td>
	</tr>
	<?php if($entform == "P") { ?>
		<input type="hidden" name="entform" value="<?php echo $entform;?>">
    	<tr>
      		<th>PROVEEDOR</th>
      		<td>:</td>
      		<td><input name="updprov" type="text" size="15" maxlength="12" value="<?php echo $updprov;?>">
        	<input name="imgalmac02" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreprov()">
		<?php
		if(strlen($updprov) > 0) {
			$sqlprov = "SELECT pro_razsocial FROM int_proveedores WHERE pro_codigo='".$updprov."' ";
			//echo $sqlprov;
			$xsqlprov = pg_exec($coneccion,$sqlprov);
			if(pg_numrows($xsqlprov) > 0) { 
				$razsoc = pg_result($xsqlprov,0,0); 
				echo $razsoc; 
			}
		}
		?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<th>TIPO Y No DE DOCUMENTO</th>
		<td>:</td>
		<td>
		<input type="text" name="updtipodocref" size="5" value="<?php echo $updtipodocref;?>">
		<input name="imgalmac03" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abretipodoc()">
		<?php
		if(strlen($updtipodocref) > 0) {
			$sqltipdoc = "SELECT tab_descripcion FROM int_tabla_general WHERE tab_tabla='08' AND tab_elemento like '%".$updtipodocref."%' ";
			$xsqltipdoc = pg_exec($coneccion,$sqltipdoc);
			if(pg_numrows($xsqltipdoc) > 0) { 
				$desctipo = pg_result($xsqltipdoc,0,0); 
				echo $desctipo; 
			}
		}
		?>
		<br>
		<input type="text" name="updnrodocref1" size="5" value='<?php echo $updnrodocref1;?>' maxlength="3">
        	-
        	<input type="text" name="updnrodocref2" size="10" value='<?php echo $updnrodocref2;?>' maxlength="7">
      		</td>
    	</tr>
</table>
<table border="1" cellpadding="0" cellspacing="0">
	<tr>
      		<th>&nbsp;</th>
      		<th>CODIGO</th>
      		<th>DESCRIPCION</th>
      		<th>CANTIDAD</th>
      		<th>COSTO UNITARIO</th>
      		<th>&nbsp;</th>
    	</tr>
    	<tr>
      		<th>&nbsp;</th>
      		<th><!--<input type="text" name="newcodart" size='13' maxlength="13">-->
		<?php
		if(strlen($artic) > 0) {
			$xsqlart = pg_exec($coneccion,"select art_codigo,art_descripcion from int_articulos where art_codigo like '%".$artic."%' ");
			if(pg_numrows($xsqlart)>0) { $artic=pg_result($xsqlart,0,0); $descart=pg_result($xsqlart,0,1); }
		}
		?>
	  	<input type="text" name="artic" size='20' maxlength="13" value="<?php echo $artic;?>"></th>
      		<td><input name="imglinea" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreart()">&nbsp;<?php echo $descart; ?>
		</td>
      		<th><input name="newcant" type="text" size='15' maxlength="15"></th>
      		<th><input name="newcostounit" type="text" size='15' maxlength="15"></th>
      		<th><input type="submit" name="boton" value="Insertar"></th>
    	</tr>
    	<?php
	$sql3 = "SELECT
			m.art_codigo,
			a.art_descripcion,
			m.mov_cantidad,
			m.mov_costounitario
		FROM
			inv_movialma m,
			int_articulos a
		WHERE	
			m.art_codigo=a.art_codigo 
			AND m.mov_numero='$nromov' 
			AND tran_codigo='$fm' ";
	// echo $sql3;
	$xsql3 = pg_exec($coneccion,$sql3);
	$ilimit3 = pg_numrows($xsql3);
	while($irow3 < $ilimit3) {
		$ad0 = pg_result($xsql3,$irow3,0);
		$ad1 = pg_result($xsql3,$irow3,1);
		$ad2 = pg_result($xsql3,$irow3,2);
		$ad3 = pg_result($xsql3,$irow3,3);
		echo "<tr><td><input type='radio' name='codart' value='".$ad0."'></td><td>".$ad0."</td><td>".$ad1."</td><td><p align='right'>".$ad2."</p></td><td><p align='right'>".$ad3."</p></td><td>&nbsp;</td></tr>";
		$irow3++;
	}
	?>
    	<tr>
      		<td>&nbsp;</td>
      		<td><input type="submit" name="boton" value="Eliminar"></td>
      		<td><input type="submit" name="boton" value="Modificar cabecera">&nbsp;&nbsp;
        	<input type="submit" name="boton" value="Regresar"></td>
      		<td>&nbsp;</td>
      		<td>&nbsp;</td>
      		<td>&nbsp;</td>
    	</tr>
</table>
</form>
</body>
</html>
<?php pg_close($coneccion); ?>
