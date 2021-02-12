<?php

include("../menu_princ.php");
include("../functions.php");
include("../utils/acceso_sistem.php");

$sql = "SELECT
		ch_codigocombustible,
		ch_nombrecombustible,
		nu_preciocombustible,
		ch_codigopec,
		ch_codigocombex,
		ch_nombrebreve
	FROM
		comb_ta_combustibles
	ORDER BY
		ch_codigocombustible ";

switch($boton){
	case Agregar:

		if(strlen($newcod)>0) {

			$xsqlbusc = pg_exec($coneccion,"SELECT ch_codigocombustible FROM comb_ta_combustibles WHERE ch_codigocombustible='".$newcod."' ");

			if(pg_numrows($xsqlbusc)==0){
				if(strlen($newprec)==0){
					$newprec=0;
				}

				$sqlins = " INSERT INTO
						comb_ta_combustibles(
									ch_codigocombustible,
									ch_nombrecombustible,
									ch_nombrebreve,
									nu_preciocombustible,
									ch_codigopec,
									ch_codigocombex
						) VALUES (
									'".$newcod."',
									'".$newdes."',
									'".$newdescbrev."',
									".$newprec.",
									'".$codpec."',
									'".$codcombex."'
						)";

				$xsqlins = pg_exec($coneccion,$sqlins);

			}else{  ?>

			<script>alert(" El Codigo ya existe!! ")</script>

<?php			}

		}else{  ?>
			<script>alert(" Debe ingresar un código válido")</script>
<?php		}
	break;

	case Modificar:

		$xsqlm 		= pg_exec($coneccion,$sql);
		$ilimitm 	= pg_numrows($xsqlm);

		while($irowm<$ilimitm) {

			$am0		= pg_result($xsqlm,$irowm,0);
			$idm[$am0]	= $am0;

			if($idm[$am0]==$idp[$am0]) {

				$sqlupd = "
						UPDATE
							comb_ta_combustibles
						SET
							ch_nombrecombustible = '".$nom[$am0]."',
							nu_preciocombustible = ".$prec[$am0].",
							ch_nombrebreve = '".$nompeq[$am0]."'
					    	WHERE
							ch_codigocombustible = '".$idm[$am0]."';
						";

				$xsqlupd = pg_exec($coneccion,$sqlupd);

				$sqlupd2 = "
						UPDATE
							fac_lista_precios
						SET
							pre_precio_act1 = '".$prec[$am0]."'
						WHERE
							art_codigo = '".$idm[$am0]."';
					";

				$xsqlupd = pg_exec($coneccion,$sqlupd2);
			}
			$irowm++;
		}

	break;

	case Eliminar:

		$xsqlm		= pg_exec($coneccion,$sql);
		$ilimitm	= pg_numrows($xsqlm);

		while($irowm<$ilimitm) {
			$am0		= pg_result($xsqlm,$irowm,0);
			$idm[$am0]	= $am0;

			if($idm[$am0] == $idp[$am0]) {
				$sqlupd = "DELETE FROM comb_ta_combustibles WHERE ch_codigocombustible='".$idm[$am0]."' ";
				$xsqlupd = pg_exec($coneccion,$sqlupd);
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
<h2 style="color:#336699;" align="center">CONFIGURACION DE PRODUCTOS</td>
<hr noshade><br>
<form action='' method='get'>
<table border="0" cellpadding="0">

	<tr>
	    <th class="grid_cabecera">&nbsp;</th>
	   	<th class="grid_cabecera">CODIGO</th>
	   	<th class="grid_cabecera">DESCRIPCION</th>
		<th class="grid_cabecera">DESC BREVE</th>
	    <th class="grid_cabecera">PRECIO VENTA</th>
	    <th class="grid_cabecera">COD PEC</th>
	  	<th class="grid_cabecera">COD COMBEX</th>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td><input type='text' name='newcod' size='15' maxlength='13'></td>
		<td><input type='text' name='newdes' size='25' maxlength='30'></td><td><input type='text' maxlength='10' name='newdescbrev' size='25' maxlength=''></td>
		<td><input type='text' name='newprec' size='15'></td><td><input type='text' name='codpec' size='10' maxlength='2'></td>
		<td><input type='text' name='codcombex' size='15' maxlength='2'></td>
		<td><button name="boton" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button></td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>

	<tr>
	    <th class="grid_cabecera">&nbsp;</th>
	   	<th class="grid_cabecera">CODIGO</th>
	   	<th class="grid_cabecera">DESCRIPCION</th>
		<th class="grid_cabecera">DESC BREVE</th>
    	<th class="grid_cabecera">PRECIO VENTA</th>
    	<th class="grid_cabecera">COD PEC</th>
	  	<th class="grid_cabecera">COD COMBEX</th>
	</tr>
<?php

$xsql 	= pg_exec($coneccion,$sql);
$irow 	= 0;
$ilimit = pg_numrows($xsql);

while($irow<$ilimit) {

	$a0		= pg_result($xsql,$irow,0);
	$nom[$a0]	= pg_result($xsql,$irow,1);
	$prec[$a0]	= pg_result($xsql,$irow,2);
	$a3		= pg_result($xsql,$irow,3);
	$a4		= pg_result($xsql,$irow,4);
	$nompeq[$a0]	= pg_result($xsql,$irow,5);

	echo "
		<tr>
		<td align='center'><input type='checkbox' name='idp[$a0]' value='".$a0."'></td>
		<td align='center'>&nbsp;".$a0."</td>";
	echo "
		<td align='center'><input type='text' size='20' name='nom[$a0]' value='".$nom[$a0]."' maxlength='30'></td>";
	echo "
		<td align='center'><input type='text' size='15' name='nompeq[$a0]' value='".$nompeq[$a0]."' maxlength='10'></td>";
	echo "
		<td align='center'><input type='text' size='15' style='text-align:right' name='prec[$a0]' value='".$prec[$a0]."'></td>";
	echo "
		<td align='center'>&nbsp;".$a3."</td>	
		<td align='center'>&nbsp;".$a4."</td>
		</tr>";

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
</form>
</body>
</html>
<?php include("../close_connect.php"); ?>

