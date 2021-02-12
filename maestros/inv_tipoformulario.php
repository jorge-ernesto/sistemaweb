<?php

include('../menu_princ.php');
include("../functions.php");
include("functions.php");

$boton = $_POST['boton'];
$newcodigo = $_POST['newcodigo'];
$newdescripcion = $_POST['newdescripcion'];
$newnaturaleza = $_POST['newnaturaleza'];
$newvalor = $_POST['newvalor'];
$newentidad = $_POST['newentidad'];
$newref = $_POST['newref'];
$neworigen = $_POST['neworigen'];
$newdestino = $_POST['newdestino'];


$sql = "SELECT
		tran_codigo,
		tran_descripcion,
		tran_naturaleza,
		tran_valor,
		tran_entidad,
		tran_referencia,
		tran_origen,
		tran_destino,
		tran_nform,
		tran_guia_remision 
	FROM
		inv_tipotransa
	ORDER BY
		1";

switch ($boton) {

	case "Agregar":

		if(strlen($newcodigo)>0) {
			$xsqlbusc = pg_exec($coneccion,"SELECT tran_codigo,tran_descripcion,tran_naturaleza,tran_valor,tran_entidad,tran_referencia,tran_origen,tran_destino,tran_nform from inv_tipotransa where tran_codigo='".$newcodigo."'");
			if(pg_numrows($xsqlbusc)==0){
				$sqlins = "INSERT INTO 
						inv_tipotransa 
						(
							tran_codigo,
							tran_descripcion,
							tran_naturaleza,
							tran_valor,
							tran_entidad,
							tran_referencia,
							tran_origen,
							tran_destino
						) VALUES (
							'".$newcodigo."',
							'".$newdescripcion."',
							'".$newnaturaleza."',
							'".$newvalor."',
							'".$newentidad."',
							'".$newref."',
							'".$neworigen."',
							'".$newdestino."'
						)";

				$xsqlins = pg_exec($coneccion,$sqlins);
			} else{  ?>
				<script>alert(" El Codigo ya existe !!! ")</script>
<?php			}
		}else{  ?>
			<script>alert(" Debe ingresar un Codigo válido")</script>
<?php		}
      		break;
      		
	case "Modificar":

		$xsqlm		= pg_exec($coneccion,$sql);
		$ilimitm	= pg_numrows($xsqlm);
		while($irowm < $ilimitm) {
			$am0		= pg_result($xsqlm,$irowm,0);
			$idm[$am0]	= $am0;
			if($idm[$am0] == $idp[$am0]) {
				$sqlupd = "	UPDATE
							inv_tipotransa 
						SET
							tran_descripcion='".$descripcion[$am0]."',
							tran_naturaleza='".$naturaleza[$am0]."',
							tran_valor='".$valor[$am0]."',
							tran_entidad='".$entidad[$am0]."',
							tran_referencia='".$ref[$am0]."',
							tran_origen='".$origen[$am0]."',
							tran_destino='".$destino[$am0]."',
							tran_guia_remision='".$tran_guia[$am0]."'
						WHERE 
							tran_codigo='".$idm[$am0]."' ";

				$xsqlupd = pg_exec($coneccion,$sqlupd);
			}
			$irowm++;
		}
      		break;
      		
	case "Eliminar":

		$xsqlm		= pg_exec($coneccion,$sql);
		$ilimitm	= pg_numrows($xsqlm);
		while($irowm < $ilimitm) {
			$am0		= pg_result($xsqlm,$irowm,0);
			$idm[$am0]	= $am0;
			if($idm[$am0] == $idp[$am0]) {
				//VALIDAR QUE NFORM NO SEA 0 PARA VALIDAR SU ELIMINACION
				if($nform[$am0]!=0){
					echo "<script language='javascript'> alert ('Nform != 0, el registro no puede ser borrado'); </script>";
				}else{
					$sqlupd		= "DELETE FROM inv_tipotransa WHERE tran_codigo = '".$idm[$am0]."' ";
					$xsqlupd	= pg_exec($coneccion,$sqlupd);
				}
			}
			$irowm++;
		}
      		break;
}
?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<h2 style="color:#336699;" align="center"><b>CONFIGURACI&Oacute;N
<BR><h2 style="color:#336699;" align="center"><b>TIPOS DE TRANSACCIONES
<hr noshade><br>
<form action='' method='post'>
<table border="0" cellpadding="0">
	<tr>
		<th class="grid_cabecera">&nbsp;</th>
		<th class="grid_cabecera">C&Oacute;DIGO</th>
		<th class="grid_cabecera">DESCRIPCI&Oacute;N</th>
		<th class="grid_cabecera">NATURALEZA</th>
		<th class="grid_cabecera">VALOR</th>
		<th class="grid_cabecera">ENTIDAD</th>
		<th class="grid_cabecera">REF</th>
		<th class="grid_cabecera">ORIGEN</th>
		<th class="grid_cabecera">DESTINO</th>
		<!-- <th class="grid_cabecera">NFORM</th>-->
  </tr>
<tr>
<td>&nbsp;</td>
<td align='center'><input type='text' name='newcodigo' size='4' maxlength='3'></td>
<td><input type='text' name='newdescripcion' size='32' maxlength='30'></td>
<!--<td><input type='text' name='newnaturaleza' size='4' maxlength='3'></td>-->

<td align='center'><select name="newnaturaleza">
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
</select></td>

<td align='right'><select name="newvalor">
	<option value="S">Si</option>
	<option value="N">No</option>
</select></td>
<td align='right'><select name="newentidad">
	<option value="P">Proveedor</option>
	<option value="C">Cliente</option>
	<option value="N">Ninguno</option>
</select></td>
<td align='right'><select name="newref">
	<option value="S">Si</option>
	<option value="N">No</option>
</select></td>

	<!----
	<td><input type='text' name='newentidad' size='2' maxlength='1'></td>
	<td><input type='text' name='newref' size='2' maxlength='1'></td>----->
	<td align='right'><input type='text' name='neworigen' size='4' maxlength='3'></td>
	<td align='right'><input type='text' name='newdestino' size='4' maxlength='3'></td>
	<!-- <td><input type='text' name='newnform' size='6' maxlength='5'></td>-->
	<td colspan="2"><button name="boton" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button></td>
</tr>

<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
 </tr>

	<tr>
    		<th class="grid_cabecera">&nbsp;</th>
		<th class="grid_cabecera">C&Oacute;DIGO</th>
		<th class="grid_cabecera">DESCRIPCI&Oacute;N</th>
		<th class="grid_cabecera">NATURALEZA</th>
		<th class="grid_cabecera">VALOR</th>
		<th class="grid_cabecera">ENTIDAD</th>
		<th class="grid_cabecera">REF</th>
		<th class="grid_cabecera">ORIGEN</th>
		<th class="grid_cabecera">DESTINO</th>
		<th class="grid_cabecera">NFORM</th>
		<th class="grid_cabecera">TRAN_GUIA</th>
	</tr>
<?php
$xsql	= pg_exec($coneccion,$sql);
$irow	= 0;
$ilimit	= pg_numrows($xsql);
while($irow<$ilimit) {

	$a0=pg_result($xsql,$irow,0);

	$descripcion[$a0]=pg_result($xsql,$irow,1);
	//$descripcion[$a0]=pg_result($xsql,$irow,2);

	$naturaleza[$a0]=pg_result($xsql,$irow,2);
	$valor[$a0]=pg_result($xsql,$irow,3);

	$entidad[$a0]=pg_result($xsql,$irow,4);
	$ref[$a0]=pg_result($xsql,$irow,5);

	$origen[$a0]=pg_result($xsql,$irow,6);
	$destino[$a0]=pg_result($xsql,$irow,7);

	$nform[$a0]=pg_result($xsql,$irow,8);

	$tran_guia_remision[$a0]=pg_result($xsql,$irow,9);

	echo "<tr>
		<td><input type='checkbox' name='idp[$a0]' value='".$a0."'></td>
		<td align='center'>$a0</td>";
	echo "<td><input type='text' size='30' name='descripcion[$a0]' value='".$descripcion[$a0]."' maxlength='30'></td>";

	//echo "<td align='right'><input type='text' size='4' style='text-align:right' name='naturaleza[$a0]' value='".$naturaleza[$a0]."' maxlength='3'></td>";
	echo "<td align='center'><select name='naturaleza[$a0]'>";
		combitonumerico(1,4,$naturaleza[$a0]);
	echo "</select></td>";

	//echo "<td align='right'><input type='text' size='4' style='text-align:right' name='valor[$a0]' value='".$valor[$a0]."' maxlength='1'></td>";
	echo "<td align='right'><select name='valor[$a0]'>";
		combito_biopcional($valor[$a0],'S','N','SI','NO');
	echo "</select></td>";

	//echo "<td align='right'><input type='text' size='2' style='text-align:right' name='entidad[$a0]' value='".$entidad[$a0]."' maxlength='1'></td>";
	echo "<td align='right'><select name='entidad[$a0]'>";
		combito_triopcional($entidad[$a0],'P','C','N','Proveedor','Cliente','Ninguno');
	echo "</select></td>";

	//echo "<td align='right'><input type='text' size='2' style='text-align:right' name='ref[$a0]' value='".$ref[$a0]."' maxlength='1'></td>";
	echo "<td align='right'><select name='ref[$a0]'>";
		combito_biopcional($ref[$a0],'S','N','SI','NO');
	echo "</select></td>";

	echo "<td align='right'><input type='text' size='4' style='text-align:right' name='origen[$a0]' value='".$origen[$a0]."' maxlength='3'></td>";
	echo "<td align='right'><input type='text' size='4' style='text-align:right' name='destino[$a0]' value='".$destino[$a0]."' maxlength='3'></td>";
	echo "<td align='right'><input type='hidden' name='nform[$a0]' value='".$nform[$a0]."'></input>".$nform[$a0]."</td>";

	//Agregado para las guias de Ramon 29/12/2004
	echo "<td align='left'><input type='text' size='4' style='text-align:right' name='tran_guia[$a0]' value='".$tran_guia_remision[$a0]."' maxlength='3'></td>";

	$irow++;
}
?>
 <tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><button name="boton" type="submit" value="Modificar"><img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button></td>
	<td><button name="boton" type="submit" value="Eliminar"><img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button></td>
	<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
 </tr>
</table>
</form>
</body>
</html>
<?php include("../close_connect.php"); ?>
