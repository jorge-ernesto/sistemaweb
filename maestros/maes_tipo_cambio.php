<?php
include("../menu_princ.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
$clase_error = new OpensoftError;
$coneccion=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="") {
	$almacen="001";
}

if($almacen=="001") {
	if($txtcampo=="A") {
		$ch=" checked";
	} elseif($txtcampo=="B") {
		$ch1=" checked";
	} else {
		$ch=" checked";
	}
}
$txtxbusqueda=strtoupper($txtxbusqueda);

if($boton=="buscar" or strlen(trim($txtxbusqueda))>0) {
	if($txtcampo=="A") {
		$addsql=" WHERE tca_moneda ='".$txtxbusqueda."' ";
	} elseif($txtcampo=="B"){
		$addsql=" WHERE tca_fecha = '".$txtxbusqueda."' ";
	} else {
		$addsql=" ";
	}
} else {
	$bddsql=" LIMIT 10";
}

//$new_compra_libre = $_POST[new_compra_libre];
$_POST[new_compra_libre];

if($boton=="adicionar"){
	$sqlai="SELECT
				tca_moneda, tca_fecha
			FROM
				int_tipo_cambio
			WHERE
				tca_moneda='".$new_moneda."'
				AND tca_fecha='".$new_fecha."'
			";
	$xsqlai=pg_exec($coneccion,$sqlai);
	$ilimitai=pg_numrows($xsqlai);
	if($ilimitai==0){
		if( strlen(trim($new_compra_libre))==0 ) {$new_compra_libre='0';} 
		if( strlen(trim($new_venta_libre))==0 ) {$new_venta_libre='0';} 
		if( strlen(trim($new_compra_banco))==0 ) {$new_compra_banco='0';} 
		if( strlen(trim($new_venta_banco))==0 ) {$new_venta_banco='0';} 
		if( strlen(trim($new_compra_oficial))==0 ) {$new_compra_oficial='0';}
		if( strlen(trim($new_venta_oficial))==0 ) {$new_venta_oficial='0';}

		$sqli="INSERT INTO int_tipo_cambio (
					tca_moneda,
					tca_fecha,
					tca_compra_libre,
					tca_venta_libre,
					tca_compra_banco,
					tca_venta_banco,
					tca_compra_oficial,
					tca_venta_oficial)
				VALUES (
					'".$new_moneda."',
					(SELECT da_fecha FROM pos_aprosys WHERE da_fecha = '".$new_fecha."'),
					'".$new_compra_libre."',
					'".$new_venta_libre."',
					'".$new_compra_banco."',
					'".$new_venta_banco."',
					'".$new_compra_oficial."',
					'".$new_venta_oficial."'
				)";
		$xsqli=pg_exec($coneccion,$sqli);
	} else {
		echo "<script>alert( 'El Tipo de Cambio del Dia ya existe!!')</script> ";
	}
}

if($boton == "eliminar") {
	$sql2 = "SELECT
				tca_moneda, tca_fecha
			FROM
				int_tipo_cambio ".$addsql."
			ORDER BY 1 
			";
	$xsql2=pg_exec($coneccion,$sql2);
	$ilimit2=pg_numrows($xsql2);
	while($irow2<$ilimit2) {
		$cod1=pg_result($xsql2,$irow2,0);
		$cod2=pg_result($xsql2,$irow2,1);
		$xelem="id_".$cod1.$cod2;
		if($xelem==$cod1.$cod2) {
			$sql1="delete from int_tipo_cambio where tca_moneda||to_char(tca_fecha,'YYYY-mm-dd')='".$xelem."'";
			$xsql1=pg_exec($coneccion,$sql1);
		}
		$irow2++;
	}
}

echo $sql2;

if($boton=="modificar"){
	$sql2="select tca_moneda, tca_fecha, tca_compra_libre, tca_venta_libre,
		tca_compra_banco, tca_venta_banco, tca_compra_oficial, tca_venta_oficial
		from int_tipo_cambio ".$addsql." order by 1,2 ";
	$xsql2=pg_exec($coneccion,$sql2);
	$ilimit2=pg_numrows($xsql2);
	while($irow2<$ilimit2){
		$cod1=pg_result($xsql2,$irow2,0);
		$cod2=pg_result($xsql2,$irow2,1);
		//	$elem=pg_result($xsql2,$irow2,1);
		$xelem="id_".$cod1.$cod2;
		$upd_compra_libre="compra_libre_".$cod1.$cod2;
		$upd_venta_libre="venta_libre_".$cod1.$cod2;
		$upd_compra_banco="compra_banco_".$cod1.$cod2;
		$upd_venta_banco="venta_banco_".$cod1.$cod2;
		$upd_compra_oficial="compra_oficial_".$cod1.$cod2;
		$upd_venta_oficial="venta_oficial_".$cod1.$cod2;

		if($xelem==$cod1.$cod2){
			if( strlen(trim($upd_compra_libre))==0 ) {$val1='0';} else {$val1=$upd_compra_libre;}
			if( strlen(trim($upd_venta_libre))==0 ) {$val2='0';} else {$val2=$upd_venta_libre;}
			if( strlen(trim($upd_compra_banco))==0 ) {$val3='0';} else {$val3=$upd_compra_banco;}
			if( strlen(trim($upd_venta_banco))==0 ) {$val4='0';} else {$val4=$upd_venta_banco;}
			if( strlen(trim($upd_compra_oficial))==0 ) {$val5='0';} else {$val5=$upd_compra_oficial;}
			if( strlen(trim($upd_venta_oficial))==0 ) {$val6='0';} else {$val6=$upd_venta_oficial;}

			$sql1="update int_tipo_cambio set
				tca_compra_libre='".$val1."', tca_venta_libre='".$val2."',
				tca_compra_banco='".$val3."', tca_venta_banco='".$val4."',
				tca_compra_oficial='".$val5."', tca_venta_oficial='".$val6."',flg_replicacion=0
				where tca_moneda||to_char(tca_fecha,'YYYY-mm-dd')='".$xelem."' ";
			//echo $sql1;
			$xsql1=pg_exec($coneccion,$sql1);
		}
		$irow2++;
	}
}
?>

<html>
	<link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
	<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
	<head>
		<title>sistemaweb</title>
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
	</head>
	<body>
		<div align="center">
			<form action="" method="post" style="background: #FFFFFF">
				<table>
					<tr>
						<td>
							Busqueda rapida:
						</td>
						<td>
							<input name="txtxbusqueda" type="text" value="<?php echo $txtxbusqueda; ?>">
						</td>
						<td>
							<input name="boton" type="submit" value="buscar">
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<input name="txtcampo" type="radio" value="A" <?php echo $ch ;?>>Moneda
							<input name="txtcampo" type="radio" value="B" <?php echo $ch1;?>>Fecha
						</td>
					</tr>
				</table>
				<h2>MANTENIMIENTO DE TIPO CAMBIO</h2>
				<!--<input type="hidden" name="addsql" value="<?php echo $addsql;?>">-->
				<input type="hidden" name="varx" value="<?php echo $varx;?>">
				<table>
					<tr>
						<th class="grid_cabecera">&nbsp;</th>
						<th class="grid_cabecera">MONEDA</th>
						<th class="grid_cabecera">FECHA</th>
						<th class="grid_cabecera">COMPRA LIBRE</th>
						<th class="grid_cabecera">VENTA_LIBRE</th>
						<th class="grid_cabecera">COMPRA BANCO</th>
						<th class="grid_cabecera">VENTA BANCO</th>
						<th class="grid_cabecera">COMPRA OFICIAL</th>
						<th class="grid_cabecera">VENTA OFICIAL</th>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input name='new_moneda' type='text' size='4' maxlength='2'></td>
						<td><input name='new_fecha' type='text' size='15' maxlength='10'></td>
						<td><input name='new_compra_libre' type='text' size='15' maxlength='10'></td>
						<td><input name='new_venta_libre' type='text' size='15' maxlength='10'></td>
						<td><input name='new_compra_banco' type='text' size='15' maxlength='10'></td>
						<td><input name='new_venta_banco' type='text' size='15' maxlength='10'></td>
						<td><input name='new_compra_oficial' type='text' size='15' maxlength='10'></td>
						<td><input name='new_venta_oficial' type='text' size='15' maxlength='10'></td>
					</tr>
					<tr align="center">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><input name='boton' type='submit' value='Modificar'></td>
						<td><input name='boton' type='submit' value='Eliminar'></td>
						<td><input name='boton' type='submit' value='Adicionar'></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>

<?php
$clavecod=" ";
$claveread=" ";

$sql="SELECT
		tca_moneda,
		tca_fecha,
		tca_compra_libre,
		tca_venta_libre,
		tca_compra_banco,
		tca_venta_banco,
		tca_compra_oficial,
		tca_venta_oficial
	FROM
		int_tipo_cambio ".$addsql."
	ORDER BY
		tca_moneda,
		tca_fecha desc ".$bddsql."
	";
//echo $sql;
	
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);

while($irow<$ilimit) {
	$cod1=pg_result($xsql,$irow,0);
	$cod2=pg_result($xsql,$irow,1);
	//$elem=pg_result($xsql,$irow,1);
	$compra_libre=pg_result($xsql,$irow,2);
	$venta_libre=pg_result($xsql,$irow,3);
	$compra_banco=pg_result($xsql,$irow,4);
	$venta_banco=pg_result($xsql,$irow,5);
	$compra_oficial=pg_result($xsql,$irow,6);
	$venta_oficial=pg_result($xsql,$irow,7);
		
	echo "<tr>";
	echo "<td><input type='checkbox' name='id_".$cod1.$cod2."' value='".$cod1.$cod2."' ".$clavecod."></td>";
	echo "<td><input name='cod1_".$cod1."' type='text' size='4' maxlength='2' value='".$cod1."' readonly></td>";
	echo "<td><input name='cod2_".$cod2."' type='text' size='15' maxlength='10' value='".$cod2."' readonly></td>";
	//echo "<td><input name='elem_".$cod."' type='text' size='7' maxlength='6' value='".$elem."' readonly></td>";
	echo "<td><input name='compra_libre_".$cod1.$cod2."' type='text' size='15' maxlength='10' value='".$compra_libre."' ".$claveread."></td>";
	echo "<td><input name='venta_libre_".$cod1.$cod2."' type='text' size='15' maxlength='10' value='".$venta_libre."' ".$claveread."></td>";
	echo "<td><input name='compra_banco_".$cod1.$cod2."' type='text' size='15' maxlength='10' value='".$compra_banco."' ".$claveread."></td>";
	echo "<td><input name='venta_banco_".$cod1.$cod2."' type='text' size='15' maxlength='10' value='".$venta_banco."' ".$claveread."></td>";
	echo "<td><input name='compra_oficial_".$cod1.$cod2."' type='text' size='15' maxlength='10' value='".$compra_oficial."' ".$claveread."></td>";
	echo "<td><input name='venta_oficial_".$cod1.$cod2."' type='text' size='15' maxlength='10' value='".$venta_oficial."' ".$claveread."></td>";
	echo "</tr>";
	
	$irow++;
}
?>
	
					<tr align="center">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><input name='boton' type='submit' value='Modificar'></td>
						<td><input name='boton' type='submit' value='Eliminar'></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</table>
			</form>
		</div>
	</body>
</html>

<?php
pg_close($coneccion);
