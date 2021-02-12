<?php

include("../menu_princ.php");
include("../functions.php");
include("functions.php");

$boton = $_POST['boton'];
$newcodigo = $_POST['newcodigo'];
$newsucursal = $_POST['newsucursal'];
$newdesc_larga = $_POST['newdesc_larga'];
$newdesc_corta = $_POST['newdesc_corta'];
$newsiglas = $_POST['newsiglas'];
$newclase = $_POST['newclase'];
$idp = $_POST['idp'];

$sql = "SELECT
		ch_almacen,
		ch_sucursal,
		ch_nombre_almacen,
		ch_nombre_breve_almacen,
		ch_sigla_almacen,
		ch_clase_almacen
	FROM
		inv_ta_almacenes
	ORDER BY
		1";
 
switch ($boton) {
   
   	case Agregar:

		if(strlen($newcodigo)>0) {
			$xsqlbusc = pg_exec($coneccion,"SELECT
								ch_almacen,
								ch_sucursal,
								ch_nombre_almacen,
								ch_nombre_breve_almacen,
								ch_sigla_almacen,
								ch_clase_almacen
							FROM
								inv_ta_almacenes
							WHERE
								ch_almacen = '".$newcodigo."'");
			if(pg_numrows($xsqlbusc)==0){

				$sqlins = "INSERT INTO 
						inv_ta_almacenes 
						(
							ch_almacen, 
							ch_sucursal, 
							ch_nombre_almacen, 
							ch_nombre_breve_almacen, 
							ch_sigla_almacen, 
							ch_clase_almacen
						) VALUES (
							'".$newcodigo."',
							'".$newsucursal."',
							'".$newdesc_larga."',
							'".$newdesc_corta."',
							'".$newsiglas."',
							'".$newclase."'
						)";

				$xsqlins = pg_exec($coneccion,$sqlins);

			} else {  ?>
				<script>alert(" El Codigo ya existe! ")</script>
<?php			}
		} else {  ?>
			<script>alert("Debe ingresar un Codigo valido")</script>
<?php		}
      		break;
      		
	case Modificar:

		$xsqlm 		= pg_exec($coneccion,$sql);
		$ilimitm 	= pg_numrows($xsqlm);
		
		while($irowm < $ilimitm) {

			$am0		= pg_result($xsqlm,$irowm,0);
			$idm[$am0]	= $am0;
			
			if($idm[$am0] == $idp[$am0]) {
				$sqlupd = "	UPDATE
							inv_ta_almacenes 
						SET
							ch_sucursal = '".$_REQUEST['sucursal'][$am0]."',
							ch_nombre_almacen = '".$_REQUEST['desc_larga'][$am0]."',
							ch_nombre_breve_almacen = '".$_REQUEST['desc_corta'][$am0]."',
							ch_sigla_almacen = '".$_REQUEST['siglas'][$am0]."',
							ch_clase_almacen = '".$_REQUEST['clase'][$am0]."'
						WHERE
							ch_almacen = '".$idm[$am0]."' ";

				$xsqlupd = pg_exec($coneccion,$sqlupd);
			}

			$irowm++;
		}

      		break;
      		
   	case Eliminar:

		$xsqlm		= pg_exec($coneccion,$sql);
		$ilimitm	= pg_numrows($xsqlm);

		while($irowm < $ilimitm) {

			$am0 		= pg_result($xsqlm,$irowm,0);
			$idm[$am0]	= $am0;

			if($idm[$am0]==$idp[$am0]) {

				$sqlupd 	= "DELETE FROM inv_ta_almacenes WHERE ch_almacen='".$idm[$am0]."' ";
				$xsqlupd	= pg_exec($coneccion,$sqlupd);
			}

			$irowm++;

		}

      		break;
}
?>
<html>
<head>
<title>Sistema OpenSoft - Almacenes</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<div align="center">
<h2 style="color:#336699;" align="center">CONFIGURACI&Oacute;N DE ALMACENES<h2></div>
<hr noshade><br>
<form action='' method='post'><div align="center">
<table border="0" cellpadding="0">
	<tr>
    		<th class="grid_cabecera">&nbsp;</th>
    		<th class="grid_cabecera">C&Oacute;DIGO</th>
		<th class="grid_cabecera">SUCURSAL</th>
    		<th class="grid_cabecera">DESCRIPCI&Oacute;N LARGA</th>
		<th class="grid_cabecera">DESCRIPCI&Oacute;N CORTA</th>
    		<th class="grid_cabecera">SIGLAS</th>
    		<th class="grid_cabecera">CLASE</th>
  	</tr>

	<tr>
		<td>&nbsp;</td>
		<td align = 'center'><input type='text' name='newcodigo' size='5' maxlength='3'></td>
		<td align = 'center'><input type='text' name='newsucursal' size='5' maxlength='3'></td>
		<td><input type='text' name='newdesc_larga' size='35' maxlength='30'></td>
		<td><input type='text' name='newdesc_corta' size='20' maxlength='10'></td>
		<td><input type='text' name='newsiglas' size='5' maxlength='3'></td>
		<!--<td><input type='text' name='newclase' size='5' maxlength='1'></td>-->
		<td align='right'><select name="newclase">
			<option value="1">Interno</option>
			<option value="2">Externo Emisor</option>
			<option value="3">Externo Receptor</option>
			<option value="4">GNV FE</option>
			</select>
		</td>
		<td><button name="boton" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button></td>
	</tr>

	<tr>
			<th  colspan="7">&nbsp;</th>
	</tr>

  	<tr>
    		<th class="grid_cabecera">&nbsp;</th>
    		<th class="grid_cabecera">C&Oacute;DIGO</th>
		<th class="grid_cabecera">SUCURSAL</th>
    		<th class="grid_cabecera">DESCRIPCI&Oacute;N LARGA</th>
		<th class="grid_cabecera">DESCRIPCI&Oacute;N CORTA</th>
    		<th class="grid_cabecera">SIGLAS</th>
    		<th class="grid_cabecera">CLASE</th>
  	</tr>
<?php
$xsql = pg_exec($coneccion,$sql);
$irow = 0;
$ilimit = pg_numrows($xsql);

while($irow<$ilimit) {
	$a0		 = pg_result($xsql,$irow,0);
	$sucursal[$a0]	 = trim(pg_result($xsql,$irow,1));
	$desc_larga[$a0] = trim(pg_result($xsql,$irow,2));
	$desc_corta[$a0] = trim(pg_result($xsql,$irow,3));
	$siglas[$a0]	 = trim(pg_result($xsql,$irow,4));
	$clase[$a0]	 = trim(pg_result($xsql,$irow,5));
	
	echo "<tr> 	<td><input type='checkbox' name='idp[$a0]' value='".$a0."'></td>
			<td align = 'center'>$a0</td>";
	echo "		<td align = 'center'><input type='text' size='5' name='sucursal[$a0]' value='".$sucursal[$a0]."' maxlength='3'></td>";	
	echo "<td align='right'><input type='text' size='35'  name='desc_larga[$a0]' value='".$desc_larga[$a0]."' maxlength='30'></td>";
	echo "<td align='right'><input type='text' size='20'  name='desc_corta[$a0]' value='".$desc_corta[$a0]."' maxlength='10'></td>";
	echo "<td align='right'><input type='text' size='5'  name='siglas[$a0]' value='".$siglas[$a0]."' maxlength='3'></td>";
	echo "<td align='right'><select name='clase[$a0]'>";
		combito_cuatriopcional($clase[$a0],'1','2','3','4','Interno','Externo Emisor','Externo Receptor','GNV FE');
	echo "</select></td>";
	$irow++;
}
?>
	<tr>
    		<td>&nbsp;</td>
   		<td><button name="boton" type="submit" value="Modificar"><img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button></td>
    		<td>&nbsp;</td>
    		<td><button name="boton" type="submit" value="Eliminar"><img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button></td>
 	</tr>

</table>
</div>
</form>
<div id="footer" align="right">v 1.0&nbsp;</div>
</body>
</html>
<?php include("../close_connect.php"); ?>
