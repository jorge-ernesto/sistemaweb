<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
require("../clases/funciones.php");
extract($_REQUEST);

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;

$new_lista_precio	= $_POST['new_lista_precio'];
$new_articulo = $_POST['new_articulo'];
$new_precio_act = $_POST['new_precio_act'];
$val1 = $_POST['val1'];

//extract($_REQUEST);
$boton = $_POST['boton'];

/*if($_POST) {
	var_dump($_POST);exit;
}*/

// conectar con la base de datos
$coneccion = $funcion->conectar("","","","","");
if(is_null($almacen) or trim($almacen) == "") {
	$almacen="001";
}
if($txtcampo=="A") {
	$ch=" checked";
} else if($txtcampo=="B") {
	$ch1=" checked";
} else if($txtcampo=="C") {
	$ch2=" checked";
} else {
	$ch=" checked";
}

$txtxbusqueda = strtoupper($txtxbusqueda);
if($boton == "Buscar" or strlen(trim($txtxbusqueda)) > 0 ) {
	if(($txtcampo == "A") and ($txtxlista == "  ")) {
		$addsql = " WHERE art_descripcion like '%".$txtxbusqueda."%' ";
	} else if(($txtcampo=="B") and ($txtxlista=="  ")) {
		$addsql = " WHERE fac_lista_precios.art_codigo = '".$txtxbusqueda."' ";
	} else if(($txtcampo=="C") and ($txtxlista=="  ")) {
		$addsql = " WHERE art_descripcion like '".$txtxbusqueda."%' ";
	} else if(($txtcampo=="A") and ($txtxlista!="  ")) {
		$addsql = " WHERE art_descripcion LIKE '%".$txtxbusqueda."%' AND pre_lista_precio='".$txtxlista."' ";
	} else if(($txtcampo=="B") and ($txtxlista!="  ")) {
		$addsql = " WHERE fac_lista_precios.art_codigo = '".$txtxbusqueda."' AND pre_lista_precio='".$txtxlista."' ";
	} else if(($txtcampo=="C") and ($txtxlista!="  ")) {
		$addsql = " WHERE art_descripcion LIKE '".$txtxbusqueda."%' AND pre_lista_precio='".$txtxlista."' ";
	} else {
		$addsql = " ";
	}

	if((strlen(trim($txtxbusqueda)) <= 0) or ($addsql == " ")) {
		$bddsql = " LIMIT 15";
	}
} else {
	$bddsql = " LIMIT 15";
}

//if($almacen=="001"){

if($boton == "Agregar") {
	$sqlai = "SELECT
					pre_lista_precio,
					art_codigo
				FROM
					fac_lista_precios
				WHERE
					pre_lista_precio = '".$new_lista_precio."'
					AND art_codigo = '".$new_articulo."';
				";

	$xsqlai = pg_exec($coneccion,$sqlai);
	$ilimitai = pg_numrows($xsqlai);

	if($ilimitai == 0) {
		if(strlen(trim($new_precio_act)) == 0) {
			$new_precio_act = '0';
		}

		$sqli = "INSERT INTO fac_lista_precios (
						pre_lista_precio,
						art_codigo,
						pre_precio_act1
					) VALUES (
						'".$new_lista_precio."',
						'".$new_articulo."',
						'".$new_precio_act."'
					);
					";

		$xsqli = pg_exec($coneccion,$sqli);
	} else {
		echo "<script>alert('Precio ya existe!!')</script>";
	}
}

if($boton == "Modificar") {
	$sql2 = "SELECT
					fac_lista_precios.pre_lista_precio,
					int_tabla_general.tab_descripcion,
					fac_lista_precios.art_codigo,
					art_descripcion,
					pre_precio_act1
				FROM
					fac_lista_precios
					LEFT JOIN int_articulos 
					ON (int_articulos.art_codigo=fac_lista_precios.art_codigo)
					LEFT JOIN int_tabla_general
					ON (tab_tabla='LPRE' AND tab_elemento=pre_lista_precio)
				".$addsql."
				ORDER BY
					tab_descripcion,
					art_descripcion
				".$bddsql.";
				";

	$xsql2 = pg_exec($coneccion,$sql2);
	$ilimit2 = pg_numrows($xsql2);
	while($irow2 < $ilimit2) {
		$cod1 = pg_result($xsql2,$irow2,0);
		$cod2 = pg_result($xsql2,$irow2,2);
		//	$elem=pg_result($xsql2,$irow2,1);
		$xelem = "id_".$cod1.$cod2;
		$upd_precio_actual = "precio_act_".$cod1.$cod2;

		if($$xelem == $cod1.$cod2) {
			if(strlen(trim($$upd_precio_actual)) == 0 ) {
				$val1 = '0';
			} else {
				$val1 = $$upd_precio_actual;
			}

			$sql1 = "UPDATE
							fac_lista_precios
						SET
							pre_precio_act1 = '".$val1."',
							flg_replicacion = 0
						WHERE
							pre_lista_precio||art_codigo = '".$$xelem."'
						";

			//echo $sql1;
			//var_dump($sql1);

			$xsql1 = pg_exec($coneccion,$sql1);
		}

		$irow2++;
	}
}
//}
?>

<html>
	<head>
		<link href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
		<script language="JavaScript" src="/sistemaweb/maestros/js/jaime.js"></script>
		<title>sistemaweb</title>
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
	</head>
	<body>
		<h2 style="color:#336699;" align="center">MANTENIMIENTO DE LISTA PRECIOS<br></h2>
		<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>
		<script type="text/javascript">
			function cambiarAction(form, txt){
				form.boton.value = txt;
				form.submit();
			}
			
			function autocompleteBridge(type) {
				if (type == 0) {
					//new
					var No_Producto = $("#txt-No_Producto");
					if(No_Producto.val() !== undefined) {
						console.log(No_Producto.val());
						autocompleteProducto(No_Producto);
						generalAutocomplete('#descripcionx', '#txt-Nu_Id_Producto', 'getProductXByCodeOrName', []);
					}

					var productox = $("#descripcionx");
					if(productox.val() !== undefined){
						console.log('text: '+productox.val());
						console.log(productox.val());
						generalAutocomplete('#descripcionx', '#codigox', 'getProductXByCodeOrName', ['AutoMercaderia()']);
					}
				} else {
					//buscar
				}
			}
		</script>
		<form name="f_name" action="" method="post"><br>
			<table align="center">
				<tr>
					<td>Busqueda rápida:</td>
					<td><input name="txtxbusqueda" type="text" value="<?php echo $txtxbusqueda;?>"></td>
					<td><input name="boton" type="submit" value="Buscar"></td>
				</tr>
				<tr>
					<td colspan="3">
						<input name="txtcampo" type="radio" value="A" <?php echo $ch;?>> Descripción Artículo (Contenido)
						<input name="txtcampo" type="radio" value="B" <?php echo $ch1;?>> Código
						<input name="txtcampo" type="radio" value="C" <?php echo $ch2;?>> Descripción Inicial
					</td>
				</tr>
			</table>
			<table align="center">
				<tr>
					<td>
						Lista de Precios: 
					</td>
					<td>
						<select name='txtxlista'>
							<?php
							$select_lista_precio = "
							<option value='' selected>TODOS</option>";
							$ssql = "SELECT
											TRIM(tab_elemento),
											TRIM(tab_descripcion)
										FROM
											int_tabla_general
										WHERE
											tab_tabla = 'LPRE'
											AND tab_elemento != '000000'
										";
							$rs1 = pg_exec($coneccion, $ssql);
							
							$select_lista_precio = "";
							for($i = 0; $i < pg_numrows($rs1); $i++) {
								$A = pg_fetch_row($rs1, $i);
								if ($A[0] == $txtxlista ) {
									$select_lista_precio = $select_lista_precio."<option value='$A[0]'>$A[0] - $A[1]</option>";
								} else {
										$select_lista_precio = $select_lista_precio."<option value='$A[0]'>$A[0] - $A[1]</option>";
								}
							}
							echo $select_lista_precio;
							?>
						</select>
						<?php //var_dump($A)?>
					</td>
					<td>
						<input name="boton" type="submit" value="Buscar">
					</td>
				</tr>
			</table>
			<input type="hidden" name="addsql" value="<?php echo $addsql;?>">
			<input type="hidden" name="varx" value="<?php echo $varx;?>">
			<table align="center" border="0" cellpadding="0">

			<?php //if($almacen=="001") {?>

				<tr>
					<th class="grid_cabecera" width="20">&nbsp;</th>
					<th class="grid_cabecera" width="60">COD LISTA</th>
					<th class="grid_cabecera" width="50">DESC LISTA</th>
					<th class="grid_cabecera" width="140">COD ARTICULO</th>
					<th class="grid_cabecera" width="120">DESC ARTICULO</th>
					<th class="grid_cabecera" width="40">PRECIO</th>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>
						<select name='new_lista_precio'>
						<?php
							$ssql2 = "SELECT
											trim(tab_elemento),
											trim(tab_descripcion)
										FROM
											int_tabla_general
										WHERE
											tab_tabla = 'LPRE'
											AND tab_elemento != '000000';
										";
							$rs1 = pg_exec($coneccion, $ssql2);

							$select_lista_precio = "";
								for($i = 0; $i < pg_numrows($rs1); $i++) {
									$A = pg_fetch_row($rs1,$i);
									$select_lista_precio = $select_lista_precio."<option value='$A[0]'>$A[0] - $A[1]</option>";
								}
								echo $select_lista_precio;
						?>
						</select>
					</td>
					<td>
						<!--<input type="text" name="new_articulo" value='<?php echo $new_articulo ; ?>' readonly="true">-->
						<input type="text" name="new_articulo" id="txt-Nu_Id_Producto" onkeyup="autocompleteBridge(0)" value="" readonly>
						<!--<img src="../images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','f_name.new_articulo','f_name.v_descart','articulos')">-->
					</td>
					<td>
						<!--<input type="text" name="v_descart" size="50" readonly="true">-->
						<input type="text" name="v_descart" id="txt-No_Producto" class="mayuscula" placeholder="Ingresar Código o Nombre" autocomplete="off" value="" size="50">
					</td>
					<td>
						<input name='new_precio_act' type='text' size='15' maxlength='10'>
					</td>
					<td>
						<button name="boton" type="submit" value="Agregar" onClick="javascript:cambiarAction(form1,'Agregar')">
							<img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar
						</button>
					</td>

				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>
						<button name="boton" type="submit" value="Modificar" onClick="javascript:cambiarAction(form1,'Modificar')">
						<img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button>
					</td>
					<td>
						<button name="boton" type="submit" value="Eliminar" onClick="javascript:cambiarAction(form1,'Eliminar')">
						<img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button>
					</td>
					<td>&nbsp;</td>
				</tr>

<?php
	$clavecod=" ";
	$claveread=" ";
	/*}
	else
	{
		$clavecod="disabled";
		$claveread="readonly";
	}*/
?>

				<tr>
					<th class="grid_cabecera" width="20">&nbsp;</th>
					<th class="grid_cabecera" width="60">COD LISTA</th>
					<th class="grid_cabecera" width="50">DESC LISTA</th>
					<th class="grid_cabecera" width="140">COD ARTICULO</th>
					<th class="grid_cabecera" width="120">DESC ARTICULO</th>
					<th class="grid_cabecera" width="40">PRECIO</th>
				</tr>

<?php
$sql = "SELECT
				fac_lista_precios.pre_lista_precio,
				int_tabla_general.tab_descripcion,
				fac_lista_precios.art_codigo,
				art_descripcion,
				pre_precio_act1
			FROM
				fac_lista_precios
				LEFT JOIN int_articulos
				ON (int_articulos.art_codigo = fac_lista_precios.art_codigo)
				LEFT JOIN int_tabla_general
				ON (tab_tabla='LPRE' and tab_elemento=pre_lista_precio)
			".$addsql."
			ORDER BY
				tab_descripcion,
				art_descripcion 
			".$bddsql ;

	// echo $sql	;
	$xsql=pg_exec($coneccion,$sql);
	$ilimit=pg_numrows($xsql);

	while($irow<$ilimit) {
		$cod1=pg_result($xsql,$irow,0);
		$cod2=pg_result($xsql,$irow,2);

		$desc_precio=pg_result($xsql,$irow,1);
		$desc_articulo=pg_result($xsql,$irow,3);
		$precio_act=pg_result($xsql,$irow,4);

		echo "<tr>";
		echo "<td><input type='checkbox' name='id_".$cod1.$cod2."' value='".$cod1.$cod2."' ".$clavecod."></td>";
		echo "<td><input name='cod1_".$cod1."' type='text' size='4' maxlength='2' value='".$cod1."' readonly></td>";
		echo "<td><input name='desc_precio_".$cod1.$cod2."' type='text' size='25' maxlength='15' value='".$desc_precio."' readonly></td>";
		echo "<td><input name='cod2_".$cod2."' type='text' size='15' maxlength='13' value='".$cod2."' readonly></td>";
		echo "<td><input name='desc_articulo_".$cod1.$cod2."' type='text' size='45' maxlength='30' value='".$desc_articulo."' readonly></td>";
		echo "<td><input name='precio_act_".$cod1.$cod2."' type='text' size='15' maxlength='10' value='".$precio_act."' ".$claveread."></td>";

		echo "</tr>";
		$irow++;
	}
	/*if($almacen=="001")
	{*/
?>

				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>
						<button name="boton" type="submit" value="Modificar" onClick="javascript:cambiarAction(form1,'Modificar')">
						<img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button>
					</td>
					<td>
						<button name="boton" type="submit" value="Eliminar" onClick="javascript:cambiarAction(form1,'Eliminar')">
						<img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button>
					</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>

<?php //}?>

			</table>
		</form>
	</body>
</html>

<?php pg_close($coneccion);?>
