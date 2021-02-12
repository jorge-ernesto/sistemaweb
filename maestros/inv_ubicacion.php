<?php

//include("../config.php");
//include("inc_top.php");
//include("../functions.php");

include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

/*echo '<pre>';
var_dump($_POST);
echo '</pre>';*/
$ch_almacen = isset($_POST['ch_almacen']) ? $_POST['ch_almacen'] : '';

if($ch_almacen=="") {
	$ch_almacen=trim($almacen);
}

if (isset($_POST)) {
	$boton = $_POST['boton'];
	$newcodubicac = $_POST['newcodubicac'];
	$newdescubicac = $_POST['newdescubicac'];
}


if($boton=="adicionar") {
	if(strlen(trim($newcodubicac))>0 and strlen(trim($newdescubicac))>0) {
		$xsqlb=pg_exec($coneccion,"select cod_ubicac from inv_ta_ubicacion where cod_ubicac='".$newcodubicac."' and cod_almacen='".$ch_almacen."' ");
		if(pg_numrows($xsqlb)==0) {
			$xsqladd=pg_exec($coneccion,"insert into inv_ta_ubicacion(cod_ubicac,cod_almacen,desc_ubicac) values('".$newcodubicac."','".$ch_almacen."','".$newdescubicac."') ");
		} else {
?>

<script> alert(" El c�digo de ubicaci�n <?php echo $newcodubicac; ?> ya existe!! ") </script>

<?php
		}
	} else {
?>

<script> alert(" Debe ingresar un c�digo y una descripci�n v�lida!! ") </script>
	<?php
	}
} elseif($boton=="eliminar") {
	$sqld=" select trim(cod_ubicac),trim(desc_ubicac) from inv_ta_ubicacion WHERE cod_almacen='$ch_almacen' order by desc_ubicac";
	//echo $sqld;
	$xsqld=pg_exec($coneccion,$sqld);
	$ilimitd=pg_numrows($xsqld);
	while($irowd<$ilimitd) {
		$a0d=pg_result($xsqld,$irowd,0);
		$xid="id_".$a0d;
		if($_POST[$xid]==$a0d) {
			$sql_delete = "delete from inv_ta_ubicacion where trim(cod_ubicac)='".trim($a0d)."' and cod_almacen='$ch_almacen'";
			//echo $sql_delete;
			$xsqldel=pg_exec($coneccion,$sql_delete);
		}
		$irowd++;
	}
}
elseif($boton=="modificar") {
	$sqld=" select trim(cod_ubicac),trim(desc_ubicac) from inv_ta_ubicacion WHERE cod_almacen='$ch_almacen' order by desc_ubicac";
	/*echo '<hr>';
	var_dump($sqld);
	echo '<hr>';*/
	$xsqld=pg_exec($coneccion,$sqld);
	$ilimitd=pg_numrows($xsqld);
	while($irowd<$ilimitd) {
		$a0d=pg_result($xsqld,$irowd,0);
		$xid="id_".$a0d;
		$xdesc="upddescubicac".$a0d;
		
		// echo '<hr>$a0d: ';
		// var_dump($a0d);
		
		// echo '<hr>$xid: ';
		// var_dump($xid);
		
		// echo '<hr>$$xid: ';
		// var_dump($$xid);
		
		// echo '<hr>VAL: ';
		// var_dump($_POST[$xid]==$a0d);

		// echo "<pre>";
		// print_r( array( $a0d, $_POST[$xdesc], $ch_almacen ) );
		// echo "</pre>";

		if($_POST[$xid]==$a0d) {
			$desc_tab = $_POST[$xdesc];
			$xsqldel=pg_exec($coneccion,"update inv_ta_ubicacion set desc_ubicac='".$desc_tab."' where trim(cod_ubicac)='".trim($a0d)."' and cod_almacen='$ch_almacen'");
			$sql_ = "update inv_ta_ubicacion set desc_ubicac='".$desc_tab."' where trim(cod_ubicac)='".trim($a0d)."' and cod_almacen='$ch_almacen'";

			// echo "<pre>";
			// print_r( array( $desc_tab, $sql_ ) );
			// echo "</pre>";

			/*var_dump("update inv_ta_ubicacion set desc_ubicac='".$desc_tab."' where trim(cod_ubicac)='".trim($a0d)."' and cod_almacen='$ch_almacen'");
			echo '<hr>';*/
		}
		$irowd++;
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>integrado</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	</head>
	<body>
		<h2 style="color:#336699;" align="center"><b> UBICACIONES </b></h2>
			<div align="center">
				<form action="" method="post">
				Almacen:
				<?php
				$query = "SELECT
							ch_almacen,
							ch_nombre_almacen
						FROM
							inv_ta_almacenes
						WHERE
							TRIM(ch_clase_almacen)='1'
						ORDER BY
							ch_nombre_almacen;
						";

				$xquery = pg_query($coneccion, $query);
				$cont_i = 0;

				echo "<SELECT name='ch_almacen' onChange='submit()'>";

				while(pg_num_rows($xquery)>$i) {
					$rs = pg_fetch_array($xquery, $i);
					if($ch_almacen==trim($rs[0])) {
						echo "<option value='".trim($rs[0])."' selected>".$rs[0]." - ".$rs[1]."</option>";
					} else {
						echo "<option value='".trim($rs[0])."'>".$rs[0]." - ".$rs[1]."</option>";
					}
					$i++;
				}

				echo "</SELECT>";
		?>

				<table align="center" cellpadding="2">
		    		<tr>
						<td class="grid_cabecera" align="center">&nbsp;</td>
						<td class="grid_cabecera" align="center">C&Oacute;DIGO</td>
						<td class="grid_cabecera" align="center">DESCRIPCI&Oacute;N</td>
						<td class="grid_cabecera" align="center">FLAG</td>
						<td class="grid_cabecera" align="center">LOG</td>
						<td class="grid_cabecera" align="center">ALMACEN</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input name="newcodubicac" type="text" size="10" maxlength="6" /></td>
						<td><input name="newdescubicac" type="text" size="60" maxlength="60" /></td>
						<td>&nbsp;</td>
						<td><button name="boton" type="submit" value="adicionar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><button name="boton" type="submit" value="eliminar"><img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button></td>
						<td>&nbsp;&nbsp; <button name="boton" type="submit" value="modificar"><img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
		<?php

		$sql = "SELECT
					TRIM(cod_ubicac),
					TRIM(desc_ubicac),
					TRIM(flg_ubicac),
					TRIM(audit),
					TRIM(cod_almacen)
				FROM
					inv_ta_ubicacion
				WHERE
					cod_almacen='".TRIM($ch_almacen)."'
				ORDER BY cod_ubicac, desc_ubicac;
				";
		$xsql=pg_exec($coneccion,$sql);
		$ilimit=pg_numrows($xsql);

		while($irow<$ilimit) {
			$a0=pg_result($xsql,$irow,0);
			$a1=pg_result($xsql,$irow,1);
			$a2=pg_result($xsql,$irow,2);
			$a3=pg_result($xsql,$irow,3);
			$a4=pg_result($xsql,$irow,4);
			echo "<tr><td><input type='checkbox' name='id_".$a0."' value='".$a0."'></td>";
			echo "<td>".$a0."</td>";
			echo "<td><input name='upddescubicac".$a0."' type='text' size='60' maxlength='60' value='".$a1."'></td>";
			echo "<td>&nbsp;".$a2."</td>
				  <td>&nbsp;".$a3."</td>
				  <td>".$a4."</td>
				</tr>";
			$irow++;
		}
		?>

					<tr>
						<td>&nbsp;</td>
						<td><button name="boton" type="submit" value="eliminar"><img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button></td>
						<td><button name="boton" type="submit" value="modificar"><img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</table>
				</form>
			</div>
	</body>
</html>

<?php pg_close($coneccion); ?>
