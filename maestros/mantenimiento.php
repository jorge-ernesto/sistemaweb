<?php
include("../menu_princ.php");
include("../functions.php");
require("../clases/funciones.php");

$funcion   = new class_funciones;
$coneccion = $funcion->conectar("","","","","");

$varx = $_GET['varx'];
$boton = $_POST['boton'];
$newelem = $_POST['newelem'];
$newdesclarga = $_POST['newdesclarga'];
$newdesccorta = $_POST['newdesccorta'];
$txtcampo = $_POST['txtcampo'];

$sql = "SELECT tab_descripcion FROM int_tabla_general WHERE tab_tabla='".$varx."' AND tab_elemento='000000';";
$rsd       = pg_exec($sql);
$D         = pg_fetch_assoc($rsd,0);
$txtxbusqueda = strtoupper($_REQUEST['txtxbusqueda']);

if($txtcampo == "A") { 
	$ch = " checked"; 
} elseif($txtcampo == "B") { 
	$ch1 = " checked"; 
} else { 
	$ch = " checked"; 
}

if($boton == "buscar" or strlen(trim($txtxbusqueda)) > 0) {
	if($txtcampo == "A") {
		$addsql = " AND tab_elemento = '".$txtxbusqueda."' ";
	} elseif($txtcampo == "B") {
		$addsql = " AND tab_descripcion LIKE '%".$txtxbusqueda."%' ";
	} else {
		$addsql = " ";
	}
}

if($boton == "adicionar") {

	$sqlai = " SELECT
				tab_elemento
			FROM
				int_tabla_general
			WHERE
				tab_tabla = '".$varx."'
				AND tab_elemento = '".$newelem."' ";

	$xsqlai 	= pg_exec($coneccion,$sqlai);
	$ilimitai 	= pg_numrows($xsqlai);

	if($ilimitai == 0) {

		$sqli = "INSERT INTO 
				int_tabla_general
				(
					tab_tabla,
					tab_elemento,
					tab_descripcion,
					tab_desc_breve
				) VALUES (
					'".$varx."',
					'".$newelem."',
					'".$newdesclarga."',
					'".$newdesccorta."'
				)";

		$xsqli = pg_exec($coneccion,$sqli);

	} else {
		?>
		<script LANGUAGE="JavaScript">
			alert(" El c�digo <?php echo $newelem; ?>  ya existe !!!")
		</script>
		<?php
	}
}

if($boton == "eliminar") {
	foreach($_REQUEST['cod'] as $k => $v) {
		$sql1  = "DELETE FROM int_tabla_general WHERE tab_tabla='".$varx."' AND tab_elemento='".$k."'";
		$xsql1 = pg_exec($coneccion,$sql1);
	}
}

if($boton == "modificar") {
	foreach($_REQUEST['cod'] as $k => $v) {
		$sql1 = "UPDATE 
				int_tabla_general 
			SET
				tab_descripcion = '".$_REQUEST['desclarga'][$k]."',
				tab_desc_breve = '".$_REQUEST['desccorta'][$k]."',
				tab_num_01 = '".intval($_REQUEST['margen'][$k])."',
				tab_car_01 = '".$_REQUEST['tipo'][$k]."',
				tab_car_02 = '".$_REQUEST['stock'][$k]."',
				tab_num_02 = '".intval($_REQUEST['diasstk'][$k])."'
			WHERE 
				tab_tabla = '".$varx."' AND 
				tab_elemento = '".$k."'";

		$xsql1 = pg_exec($coneccion,$sql1);
	}
}

if($boton == "buscar") {
	if($txtcampo == "A") {
		$addsql=" AND tab_elemento='".$txtxbusqueda."' ";
	} elseif($txtcampo == "B") {
		$addsql = " AND tab_descripcion LIKE '%".$txtxbusqueda."%' ";
	} else {
		$addsql = " ";
	}
}
?>
<html><link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
</head>

<body>
<form action="" method="post">
<div align="center">
<br>
<?php
$sql = "SELECT
		tg.tab_descripcion,
		sum(pt.cantidad),
		sum(pt.importe)
	FROM 
		pos_transtmp pt,
		int_tabla_general tg,
		int_articulos a 
	WHERE
		pt.codigo = a.art_codigo 
		AND (a.art_linea = tg.tab_elemento OR a.art_linea = substr(tg.tab_elemento,5,2))
		AND (trans is not null) 
		AND tg.tab_tabla='20' 
	GROUP BY
		tg.tab_descripcion";

$xsql 	= pg_exec($coneccion,$sql);
$ilimit = pg_numrows($xsql);

if($ilimit > 0) {
	$cod 		= pg_result($xsql,$irow,0);
	$elem		= pg_result($xsql,$irow,1);
	$desclarga 	= pg_result($xsql,$irow,2);
}
?>
<table>
	<tr>
		<td>Busqueda r&aacute;pida: </td>
		<td><input name="txtxbusqueda" type="text" value="<?php echo $txtxbusqueda; ?>"></td>
		<!--<td><input name="boton" type="submit" value="buscar"></td>-->
		<td><button name="action" type="submit" value="buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button></td>
	</tr>
	<tr>
		<td colspan="3"><input name="txtcampo" type="radio" value="A" <?php echo $ch;?>>
	        C&oacute;digo<input type="radio" name="txtcampo" value="B" <?php echo $ch1;?>>Descripci&oacute;n
		</td>
	</tr>
</table>
<br>
<?php $desclarga = $D[tab_descripcion];?>

<p>
	<h2 style="color:#336699;" align="center"><b>MANTENIMIENTO DE LA TABLA: &nbsp;<?php echo $varx." ";?>- <?php echo " ".$desclarga;?></b></h2></p>
	<!--<input type="hidden" name="addsql" value="<?php echo $addsql;?>">-->
	<input type="hidden" name="varx" value="<?php echo $varx;?>">
<table border="0" cellpadding="0">
	<tr>
		<th class="grid_cabecera">&nbsp;</th>
		<th class="grid_cabecera">C&Oacute;DIGO</th>
		<th class="grid_cabecera">DESCRIPCI&Oacute;N LARGA</th>
		<th class="grid_cabecera">DESCRIPCI&Oacute;N CORTA</th>

<?php
	if($varx == 20) {
?>
		<th class="grid_cabecera">% MARGEN</th>
		<th class="grid_cabecera">TIPO</th>
		<th class="grid_cabecera">STK</th>
		<th class="grid_cabecera">DIAS STK</th>

<?php
		}
		if($varx == 17) {
		?>
      			<th class="grid_cabecera">% VALOR</th>
      		<?php
		}
		if($varx == 96) { // forma de pago credito
		?>
      	<th class="grid_cabecera">DIAS STK</th>
      	<?php
		}
		?>
    	</tr>
    	<tr>
      		<td>&nbsp;</td>
      		<td><input name='newelem' type='text' size='7' maxlength='6'></td>
		<td><input name='newdesclarga' type='text' size='40' maxlength='40'></td>
		<td><input name='newdesccorta' type='text' size='20' maxlength='20'></td>
		<?php if($varx==20) {?>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		<?php } if($varx==17) {?>
			<td>&nbsp;</td>
		<?php } if($varx==96) {?>
			<td>&nbsp;</td>
		<?php }?>
		<td><button name="boton" type="submit" value="adicionar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<?php if($varx==20) {?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<?php } if($varx==17) {?>
		<td>&nbsp;</td>
		<?php } if($varx==96) {?>
		<td>&nbsp;</td>
		<?php } ?>
    </tr>
    		<?php

 		$sql = "SELECT 
				tab_tabla,
				tab_elemento,
				tab_descripcion,
				tab_desc_breve,
				tab_num_01,
				tab_car_01,
				tab_car_02,
				tab_num_02
		 	FROM 
				int_tabla_general 
			WHERE 
				tab_tabla='".$varx."' ".$addsql." 
			ORDER BY
				1";

	$xsql = pg_exec($coneccion,$sql);
	$ilimit = pg_numrows($xsql);
	while($irow < $ilimit) {
		$cod 	   = pg_result($xsql,$irow,0);
		$elem 	   = pg_result($xsql,$irow,1);
		$desclarga = pg_result($xsql,$irow,2);
		$desccorta = pg_result($xsql,$irow,3);
		$margen    = pg_result($xsql,$irow,4);
		$tipo 	   = pg_result($xsql,$irow,5);
		$stk 	   = pg_result($xsql,$irow,6);
		$diasstk   = pg_result($xsql,$irow,7);
		
		if($elem != "000000") {
			echo "<tr><td><input type='checkbox' name='cod[".$elem."]' value='".$elem."'></td>";
			echo "<td><input name='elem[".$elem."]' type='text' size='7' maxlength='6' value='".$elem."' readonly></td>";
			echo "<td><input name='desclarga[".$elem."]' type='text' size='40' maxlength='40' value='".$desclarga."'></td>";
			echo "<td><input name='desccorta[".$elem."]' type='text' size='20' maxlength='20' value='".$desccorta."'></td>";

			if($varx == 20) {
				echo "<td><input name='margen[".$elem."]' type='text' size='10' maxlength='6' value='".$margen."'></td>";
				echo "<td><input name='tipo[".$elem."]' type='text' size='2' maxlength='2' value='".$tipo."'></td>";
				echo "<td><input name='stock[".$elem."]' type='text' size='2' maxlength='1' value='".$stk."'></td>";
				echo "<td><input name='diasstk[".$elem."]' type='text' size='15' maxlength='15' value='".$diasstk."'></td></tr>";
			}
			if($varx==17) {
				echo "<td><input name='margen[".$elem."]' type='text' size='10' maxlength='6' value='".$margen."'></td>";
			}
			if($varx==96) {
				echo "<td><input name='margen[".$elem."]' type='text' size='10' maxlength='6' value='".$margen."'></td>";
			}
		}
		$irow++;
	}
?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><button name="boton" type="submit" value="eliminar"><img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button></td>
		<td><button name="boton" type="submit" value="modificar"><img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button></td>
		<?php if($varx==20) {?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<?php } if($varx==17) {?>
		<td>&nbsp;</td>
		<?php } if($varx==96) {?>
		<td>&nbsp;</td>
		<?php  }  ?>
	</tr>
</table>
</div>
</form>
</body>
</html>
<?php
pg_close($coneccion);
