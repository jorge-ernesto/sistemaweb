<?php

include("../menu_princ.php");
include("../functions.php");

$nuevofechad 	= explode('/',$_REQUEST['diasd']);
$diad		= $nuevofechad[0];
$mesd		= $nuevofechad[1];
$anod		= $nuevofechad[2];

$nuevofechaa 	= explode('/',$_REQUEST['diasa']);
$diaa		= $nuevofechaa[0];
$mesa		= $nuevofechaa[1];
$anoa		= $nuevofechaa[2];

function inserta_item_movxfisico($coneccion,$nromov,$fm,$newcodartic,$fecmov,$updalmac,$updalmaco,$updalmad,$updnatu,$updtipodocref,$updnrodocref,$updprov,$newcant,$flagcito,$cu,$entform,$sfis) {
        global $costopromprod,$costoproducto,$msg_insert,$f_existeitem,$sqlinsdet,$sqlca;

        $period = date("Y"); 

	$fec = "select da_fecha from pos_aprosys where ch_poscd = 'A'";
	$sqlca->query($fec);
	$a = $sqlca->fetchRow();

	$feca = substr($a['da_fecha'],0,4);
	$fecm = substr($a['da_fecha'],5,2);
	$fecd = substr($a['da_fecha'],8,2);

	$fecmov = $feca."-".$fecm."-".$fecd;

        valida_item_mov($coneccion,$newcodartic,$fm,$nromov);

	$mesact	= $fecm;
	$periodo = $feca;
	$period = $feca;

	$sql_costo_promedio = "	SELECT 
					stk_costo$mesact
				FROM
					INV_SALDOALMA
				WHERE 
					STK_ALMACEN='$updalmac'
					AND STK_PERIODO='$periodo' 
					AND ART_CODIGO='$newcodartic'";

	@$costoproducto = pg_result(pg_query($coneccion, $sql_costo_promedio), 0, 0);

	$costototal = $costoproducto * $newcant;

	if(strlen(trim($costoproducto)) == 0) {
		$costoproducto = 0;
	}

	if($entform == "P") {
		$sqlinsdet = "INSERT INTO
					inv_movialma
					(	mov_numero,
						tran_codigo,
						art_codigo,
						mov_fecha,
						mov_almacen,
						mov_almaorigen,
						mov_almadestino,
						mov_naturaleza,
						mov_tipdocuref,
						mov_docurefe,
						mov_entidad,
						mov_cantidad,
						mov_costounitario,
						mov_costopromedio,
						mov_costototal
					)
				VALUES (
						'$nromov',
						'$fm',
						'$newcodartic',
						'$fecmov',
						'$updalmac',
						'$updalmaco',
						'$updalmad',
						'$updnatu',
						'$updtipodocref',
						'$updnrodocref',
						'$updprov',
						$newcant,
						$costoproducto,
						$costoproducto,
						$costototal
					)";

	} else {
		$sqlinsdet = "INSERT INTO inv_movialma
					(	mov_numero,
						tran_codigo,
						art_codigo,
						mov_fecha,
						mov_almacen,
						mov_almaorigen,
						mov_almadestino,
						mov_naturaleza,
						mov_tipdocuref,
						mov_docurefe,
						mov_cantidad,
						mov_costounitario,
						mov_costopromedio,
						mov_costototal)
				VALUES(
						'$nromov',
						'$fm',
						'$newcodartic',
						'$fecmov',
						'$updalmac',
						'$updalmaco',
						'$updalmad',
						'$updnatu',
						'$updtipodocref',
						'$updnrodocref',
						$newcant,
						$costoproducto,
						$costoproducto,
						$costototal
					)";
	}

	$xsqlinsdet = pg_exec($coneccion,$sqlinsdet);

	if($f_tipo_trans == "I") {
		$xsqlfec = pg_exec($coneccion,"SELECT stk_ucompra FROM inv_saldoalma WHERE art_codigo='".$newcodartic."' AND stk_periodo='".$period."' AND stk_almacen='".$updalmac."'");
		//print_r($xsqlfec);
		if(pg_numrows($xsqlfec) > 0) {
			$fecuc = pg_result($xsqlfec, 0, 0);
                        if(strlen($fecuv) > 0) {
				comparafechamayor($fecmov,$fecuc);				
                        }
		}
	} else {
		$xsqlfec = pg_exec($coneccion,"SELECT stk_uventa FROM inv_saldoalma WHERE art_codigo='".$newcodartic."' AND stk_periodo='".$period."' AND stk_almacen='".$updalmac."'");
		//print_r($xsqlfec);
		if(pg_numrows($xsqlfec) > 0) {
			$fecuv = pg_result($xsqlfec,0,0);
			if(strlen($fecuv) > 0) {
				comparafechamayor($fecmov,$fecuv);
			}
		}
	}

}

function completarEspacios($longitud, $palabra, $caracter, $Der_O_Izq) {

	$palabra = trim($palabra);
	$long_inicial = strlen($palabra);

	for($i = 0; $i < $longitud - $long_inicial; $i++) {
		if($Der_O_Izq == "D") {
			$palabra = $palabra.$caracter;
		} else {
			$palabra = $caracter.$palabra;
		}
	}

	return $palabra;
}

if($flg == "A") {
	$diad 	   = date("d");  
	$mesd 	   = date("m");  
	$anod  	   = date("Y");
	$detoresum = "D"; 
	$txtform   = "17";  
	$txtalma   = $almacen;
	$rad1 	   = ""; 
	$rad2 	   = " checked";  
	$updalmaco = $almacen;
} else {
	if($orden == "C") { 
		$rad1 = " checked";  
		$rad2 = ""; 
	} elseif($orden == "D") { 
		$rad1 = ""; 
		$rad2 = " checked"; 
	}
}

if($updalmaco == "") {
	$updalmaco = $almacen;
}

$boton = $_REQUEST['boton'];

if($boton == "Procesar") {

	$xsqldeltemp = pg_exec($coneccion,"DELETE FROM tempajuste");
	$mesact = date("m"); 
	$anoact = date("Y");

	if($orden == "C") {
		$ord = " ORDER BY a.art_codigo";
	} elseif($orden == "D") {
		$ord = " ORDER BY a.art_descripcion";
	}

	if(strlen(trim($newcostounit)) == 0) {
		$newcostounit = 0;
	}

	$sqlp = "SELECT trim(a.art_codigo),
			s.stk_stock".$mesact.",
			stk_fisico".$mesact.",
			a.art_descripcion,
			a.art_costoactual,
			s.stk_costo".$mesact."
		FROM 
			int_articulos a,
			inv_saldoalma s
		WHERE 	
			s.art_codigo=a.art_codigo 
			AND stk_periodo='".$anoact."' 
			AND s.stk_almacen='".$updalmaco."' 
			AND a.art_cod_ubicac='".$ubicac."' ".$mayomen." ".$ord." ";

	$sqlp = "SELECT trim(a.art_codigo), 
			round(s.stk_stock".$mesact.",2), 
			round(stk_fisico".$mesact.",2),
			a.art_descripcion, 
			a.art_costoactual,
			s.stk_costo".$mesact."
		FROM 
			int_articulos a 
			LEFT JOIN inv_saldoalma s ON a.art_codigo = s.art_codigo
			AND stk_periodo='".$anoact."'
			AND s.stk_almacen='".$updalmaco."'
		WHERE
			a.art_cod_ubicac='".$ubicac."' ".$mayomen." ".$ord;

	$xsqlp   = pg_exec($coneccion,$sqlp);
	$ilimitp = pg_numrows($xsqlp);

	$query_trans  = "
			SELECT
				tran_nform, 
				trim(tran_naturaleza), 
				tran_origen, 
				tran_destino 
			FROM 
				inv_tipotransa 
			WHERE
				tran_codigo='17';
			";

	$xquery_trans = pg_query($coneccion, $query_trans);

	if(pg_num_rows($xquery_trans) > 0) {

		$rs 	   = pg_result($xquery_trans,0,0);
		$updnatu   = pg_result($xquery_trans,0,1);
		$updalmaco = pg_result($xquery_trans,0,2);
		$updalmad  = pg_result($xquery_trans,0,3);

		while(strlen($rs) < 10) {
			$rs = ("0".$rs) + 1;
			pg_exec($coneccion, "UPDATE inv_tipotransa SET tran_nform='$rs' WHERE tran_codigo='17'");
			$rs = $almacen.completarCeros($rs,7,"0");
		}
		$nromov=$rs;
	}

	while($irowp <= $ilimitp) {

		$a0p = pg_result($xsqlp,$irowp,0);
		$a1p = pg_result($xsqlp,$irowp,1);

		if(strlen(trim($a1p)) == 0) {
			$a1p = 0;
		}

		$a2p = pg_result($xsqlp,$irowp,2);
		$a3p = pg_result($xsqlp,$irowp,3);

		$sql_precio = "SELECT util_fn_precio_articulo('$a0p')";
		$a4p=pg_result(pg_query($coneccion, $sql_precio),0,0);
		$a5p=pg_result($xsqlp,$irowp,5);

		if(strlen(trim($a5p)) == 0) {
			$a5p = 0;
		}

		$a0p = str_replace(" ","_",$a0p);

		$cod = "id_".$a0p;
		$fis = "fisico_".$a0p;

		if($a0p == str_replace(" ","_",$_REQUEST[$cod])) {
			$periodo     = date("Y");
			$variac      = $$fis-$a1p;
			$fm          = "17";
			$newcodartic = str_replace("_"," ",$a0p);
			$fecmov      = date("Y/m/d");
			$updalmac    = $almacen;
			$newcant     = $variac;

			if($variac != 0) {
				inserta_item_movxfisico($coneccion,$nromov,$fm,$newcodartic,$fecmov,$updalmac,$updalmaco,$updalmad,$updnatu,$updtipodocref,$updnrodocref,$updprov,$newcant,$flagcito,$cu,$entform,$fis);
			}

			$avta = $variac * $a4p;
			$sqladdtemp = "INSERT INTO tempajuste
					   	(
							cod,
							descart,
							precio,
							contable,
							fisico,
							variacion,
							costo,
							venta
						)
					VALUES	(
							'".$newcodartic."',
							'".$a3p."',
							".round($a4p,2).",
							".round($a1p,2).",
							".round($$fis,2).",
							".round($variac,2).",
							".round($a5p,2).",
							".round($avta,2)."
						)";

			if($variac != 0) {
				$xsqladdtemp = pg_exec($coneccion,$sqladdtemp);
			}
		}
		$irowp++;
	}

	$fec = "select da_fecha from pos_aprosys where ch_poscd = 'A'";
	$sqlca->query($fec);
	$a = $sqlca->fetchRow();

	$feca = substr($a['da_fecha'],0,4);
	$fecm = substr($a['da_fecha'],5,2);
	$fecd = substr($a['da_fecha'],8,2);

	$fecmov = $fecd."/".$fecm."/".$feca;
	$hour = date("d/m/Y H:i:s");
	$hora = substr($hour,11);

	$aud = $fecmov." ".$hora."-".$user;

	$xsqlupdflgubic = pg_exec($coneccion,"UPDATE inv_ta_ubicacion SET flg_ubicac='0',audit='".$aud."' WHERE cod_ubicac='".$ubicac."' AND cod_almacen='".$almacen."' ");

}
?>

<html>
<head>
<title>Integrado</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script language="javascript">
var miPopup

function abrealmao() {
    miPopup = window.open("almac.php","miwin","width=500,height=400,scrollbars=yes")
    miPopup.focus()
}

function abreubica() {

<?php
	(empty($updalmaco) ? $updalmaco = $_REQUEST['txtalma'] : $updalmaco);
	$url="ubicac.php?ch_almacen=$updalmaco&in_process=1";
	//echo $url;
	echo 'miPopup = window.open("'.$url.'","miwin","width=500,height=400,scrollbars=yes")'; ?>
	//miPopup = window.open("ubicacf.php","miwin","width=500,height=400,scrollbars=yes")

	miPopup.focus()

}
function enviadatos() {
	document.formular.submit()
}

function ChequearTodos(chkbox) {
	for (var i=0;i < document.forms[0].elements.length;i++) {
		var elemento = document.forms[0].elements[i];
		if (elemento.type == "checkbox") {
			elemento.checked = chkbox.checked
		}
	}
}
</script>
</head>
<body>
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
AJUSTE DE INVENTARIO FISICO<hr noshade><br>
<form name="formular" action="aj_inv_fisico.php" method="post">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<th>ALMACEN</th>
      		<td>:</td>
      		<td><input name="txtalma" id="txtalma" type="text" size="6" maxlength="3" value='<?php echo $updalmaco;?>'

        		<!--<input type="submit" name="boton2" value="Ok"> -->
        		<input name="imgalmac0" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrealmao()">
		<?php
		if(strlen($updalmaco) > 0) {
			$sqlao    = "SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen LIKE '%".$updalmaco."%' ";
			$xsqlao   = pg_exec($coneccion,$sqlao);
			$ilimitao = pg_numrows($xsqlao);

			if($ilimitao > 0) {
				$codao  = pg_result($xsqlao,0,0);
				$descao = pg_result($xsqlao,0,1);
				echo $descao;
			}
		}
		?>
		</td>
	</tr>
	<tr>
		<td >FECHA</td>
		<td >:</td>
		<td ><input type="text" name="diasd" size="10" value="<?php echo $diad.'/'.$mesd.'/'.$anod ?>" readonly="true"/>
			&nbsp;<!--<a href="javascript:show_calendar('formular.diasd');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></td>-->
	</tr>
	<tr>
		<td>UBICACION</td>
		<td>:</td>
		<td><input name="ubicac" type="text" value="<?php echo $ubicac; ?>" size="10" maxlength="6">


		<?php 

		if(strlen($ubicac) > 0) {
			$sqlao    = "SELECT cod_ubicac, desc_ubicac from inv_ta_ubicacion where cod_ubicac like '%".$ubicac."%' and cod_almacen='".$updalmaco."' and flg_ubicac='1'";
			$xsqlao   = pg_exec($coneccion,$sqlao);
			$ilimitao = pg_numrows($xsqlao);

			if($ilimitao > 0) {				
				$txtalma  = pg_result($xsqlao,0,0);
				$descubic = pg_result($xsqlao,0,1);
			}
		}

		?>

        		<input name="imgubica" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abreubica()">
        		<?php echo $descubic; ?> </td>
    	</tr>
    	<tr>
      		<td>ORDEN</td>
      		<td>:</td>
      		<td><input type="radio" name="orden" value="C" <?php echo $rad1;?>>
        		C&oacute;digo
        		<input type="radio" name="orden" value="D" <?php echo $rad2;?>>
        		Descripci&oacute;n</td>
    	</tr>
    	<tr>
      		<td>&nbsp;</td>
      		<td>&nbsp;</td>
      		<td><input type="submit" name="boton" value="Buscar"></td>
    	</tr>
</table>
<br><br>

<?php
if($boton == "Buscar" and strlen($ubicac) > 0 and strlen($descubic) > 0) {
?>

<table border="1" cellspacing="0" cellpadding="0">
	<tr>
		<th>&nbsp;</th>
		<th>CODIGO </th>
		<th>DESCRIPCION</th>
		<th>STK ACTUAL</th>
		<th>STK FISICO</th>
	</tr>
	<?php
	$mesact = date("m"); 
	$anoact = date("Y");

	if($orden == "C") {
		$ord = " ORDER BY a.art_codigo";
	} elseif($orden == "D") {
		$ord = " ORDER BY a.art_descripcion";
	}

/***   FRED  -  el siguiente query es cambiado para que pueda tomar articulos que no tengan inicializado stock ***/
/*	$sql = "	SELECT 
				a.art_codigo,a.art_descripcion,
				s.stk_stock".$mesact.",
				stk_fisico".$mesact."
			FROM 
				int_articulos a,
				inv_saldoalma s
			WHERE 
				s.art_codigo=a.art_codigo 
				and stk_periodo='".$anoact."'
				and s.stk_almacen='".$updalmaco."' 
				and a.art_cod_ubicac='".$ubicac."' ".$mayomen." ".$ord." ";
*/

	$sql="SELECT	trim(a.art_codigo), 
			a.art_descripcion, 
			round(s.stk_stock".$mesact.",2), 
			round(stk_fisico".$mesact.",2)
		FROM 
			int_articulos a 
			LEFT JOIN inv_saldoalma s ON a.art_codigo=s.art_codigo AND stk_periodo='".$anoact."' AND s.stk_almacen='".$updalmaco."'
		WHERE
			a.art_cod_ubicac='".$ubicac."' ".$mayomen." ".$ord;

	//echo $sql;

	$xsql   = pg_exec($coneccion,$sql);
	$ilimit = pg_numrows($xsql);

	while($irow < $ilimit) {
		$a0 = pg_result($xsql,$irow,0);
		$a1 = pg_result($xsql,$irow,1);
		$a2 = pg_result($xsql,$irow,2);
		$a3 = pg_result($xsql,$irow,3);
		
		echo "	<tr>
				<td><input type='checkbox' name='id_".$a0."' value='".$a0."'></td>";

		echo "		<td>".$a0."</td>
				<td>".$a1."</td>
				<td align='right'>".$a2."</td>";

		echo "		<td>
				<input name='fisico_".$a0."' type='text' size='20' maxlength='20' value='".$a2."'></td>
			</tr>";
			
		$irow++;
	}
	echo '<tr>';
	echo '<td bgcolor="#FFFFCC" colspan="5"><input type="checkbox" name="checkbox11" value="checkbox" onClick="ChequearTodos(this);">&nbsp;Seleccionar Todos</td>';
?>
	<tr>
      		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><input name="boton" type="submit" value="Procesar"></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>
<?php
} elseif($boton == "Buscar" and strlen($descubic) == 0) {
?>
	<script>
	alert(" Debe de ingresar una ubicaci�n v�lida !!! ")
	</script>
<?php	
} 
?>
<p>&nbsp;</p>
</form>
<?php

if($boton == "Procesar") {
	$directorio 	  = "/sistemaweb/inventarios";
	$archivo_cabecera = "aj_inv_fisico_cabecera.txt";
	$archivo_cuerpo   = "aj_inv_fisico_cuerpo.txt";

	$ft     = fopen($archivo_cuerpo,'w');
	$ft_cab = fopen($archivo_cabecera,'w');

	if ($ft > 0) {
		$sql_almacen = "SELECT trim(ch_nombre_almacen) FROM inv_ta_almacenes WHERE ch_almacen='$almacen'";
		$snewbuffer = $snewbuffer."                                        AJUSTE DE INVENTARIO FISICO                              \n";
		$snewbuffer = $snewbuffer.str_pad($almacen." - ".pg_result(pg_query($coneccion, $sql_almacen),0,0)."  ".trim($descao)." - UBIC. ".$ubicac  ,120, " ", STR_PAD_BOTH)." \n \n";
		$snewbuffer = $snewbuffer."CODIGO\t\tNOMBRE\t\t\t\t      PRECIO   CONTABLE     FISICO    VARIACION    COSTO     DIFEREN\n";
		$snewbuffer = $snewbuffer."==================================================================================================================================\n";

		fwrite($ft_cab,$snewbuffer);
		fclose($ft_cab);
		$snewbuffer = "";
	}
?>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/sistemaweb/utils/impresiones.php?imprimir=paginar&cabecera=/sistemaweb/inventarios/aj_inv_fisico_cabecera.txt&cuerpo=/sistemaweb/inventarios/aj_inv_fisico_cuerpo.txt&archivo_final=/sistemaweb/inventarios/aj_inv_fisico.txt" target="_blank">Imprimir</a>
<table border="1" cellspacing="0" cellpadding="0">
	<tr>
		<td>CODIGO</td>
		<td>NOMBRE</td>
		<td>PRECIO</td>
		<td>CONTABLE</td>
		<td>FISICO</td>
		<td>VARIACION</td>
		<td>COSTO</td>
		<td>DIFEREN</td>
	</tr>
<?php
$sqlrep    = "SELECT * FROM tempajuste;";
$xsqlrep   = pg_exec($coneccion,$sqlrep);
$ilimitrep = pg_numrows($xsqlrep);

while($irowrep < $ilimitrep) {
	$rep0 = trim(pg_result($xsqlrep,$irowrep,0));
	$rep1 = pg_result($xsqlrep,$irowrep,1);
	$rep2 = pg_result($xsqlrep,$irowrep,2);
	$rep3 = pg_result($xsqlrep,$irowrep,3);
	$rep4 = pg_result($xsqlrep,$irowrep,4);
	$rep5 = pg_result($xsqlrep,$irowrep,5);
	$rep6 = pg_result($xsqlrep,$irowrep,6)*$rep5;
	$rep7 = pg_result($xsqlrep,$irowrep,7);
	$totrep6 = $totrep6+$rep6;  
	$totrep7 = $totrep7+$rep7;
	echo "<tr><td>&nbsp;".$rep0."</td><td>&nbsp;".$rep1."</td>";
	echo "<td align='right'>&nbsp;".$rep2."</td><td align='right'>&nbsp;".$rep3."</td>";
	echo "<td align='right'>&nbsp;".$rep4."</td><td align='right'>&nbsp;".$rep5."</td>";
	echo "<td align='right'>&nbsp;".$rep6."</td><td align='right'>&nbsp;".$rep7."</td></tr>";

	$irowrep++;
//  	$snewbuffer = $snewbuffer.$rep0."\t".substr($rep1,0,20)."\t".$rep2."\t".$rep3." \t ".$rep4." \t".$rep5." \t ".$rep6." \t".$rep7." \n";
//   	$snewbuffer = $snewbuffer.$rep0."\t".completarEspacios(30,substr($rep1,0,30)," ","D")."\t ".$rep2."\t   ".$rep3."\t   ".$rep4."\t   ".$rep5."\t   ".$rep6."\t   ".$rep7."\n";
	$snewbuffer = $snewbuffer.$rep0."\t".completarEspacios(30,substr($rep1,0,30)," ","D");
	$snewbuffer = $snewbuffer."\t".completarEspacios(11,$rep2," ","I").completarEspacios(11,$rep3," ","I").completarEspacios(11,$rep4," ","I").completarEspacios(11,$rep5," ","I").completarEspacios(11,$rep6," ","I").completarEspacios(11,$rep7," ","I")."\n";
}
?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align="right">TOTAL</td>
		<td align="right">&nbsp;<?php echo $totrep6; ?></td>
		<td align="right">&nbsp;<?php echo $totrep7;?></td>
	</tr>
</table>
<?php
$snewbuffer = $snewbuffer."==================================================================================================================================\n";
$snewbuffer = $snewbuffer."\t\t\t\t\t\t\t\t\t\t TOTAL \t\t".$totrep6." \t  ".$totrep7." \n";
fwrite($ft,$snewbuffer);
fclose($ft); ?>
<a href="aj_inv_fisico.txt" target="_blank">exportar a txt</a>
<?php } ?>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php pg_close($coneccion);
