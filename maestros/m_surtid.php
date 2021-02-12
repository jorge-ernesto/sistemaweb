<?php
date_default_timezone_set('UTC');

include("../menu_princ.php");
include("../functions.php");
include("../utils/acceso_sistem.php");

$hoy = date("Y-m-d");

/*echo '<pre>';
var_dump($_SESSION);
echo '</pre>';*/

//var_dump($_REQUEST);

$cod_almacen = $_SESSION['usuario']->almacenActual;

//var_dump($cod_almacen);

if($cod_almacen != "") {
	$almacen = $cod_almacen;
}


$rs1 = pg_exec("
			SELECT
				t.ch_tanque AS tanque,
				t.ch_tanque || ' -- ' || c.ch_nombrecombustible AS desc
			FROM
				comb_ta_combustibles c,comb_ta_tanques t
			WHERE
				c.ch_codigocombustible = t.ch_codigocombustible
				AND t.ch_sucursal = trim('$almacen')
			ORDER BY
				tanque
		");

//para buscar los campos del surtidor que ya estan ingresados
$rs2 = pg_exec("
			SELECT
				s.ch_surtidor,
				s.ch_tanque,
				c.ch_nombrecombustible,
				c.nu_preciocombustible,
				s.nu_contomtrovalor,
				s.nu_contometrogalon,
				s.dt_fechactualizacion,
				s.ch_numerolado,
				s.nu_manguera
			FROM
				comb_ta_surtidores s,
				comb_ta_combustibles c
			WHERE
				s.ch_codigocombustible = c.ch_codigocombustible
				AND s.ch_sucursal=trim('$almacen')
			ORDER BY
				s.ch_surtidor
		");

//para dejar libres solo aquellos surtidores que no esten ingresados
$rs3 = pg_exec("
			SELECT
				ch_surtidor
			FROM
				comb_ta_surtidores
			WHERE
				ch_sucursal = TRIM('$almacen')
			ORDER BY
				ch_surtidor
		");

for($i=0;$i<pg_numrows($rs3);$i++) {
	$A = pg_fetch_row($rs3,$i);
	$SUR[$i] =  $A[0];
}

if(count($SUR)==0) {
	for($k=0;$k<65;$k++) {
		if ($k+1<10) {
			$S[$k] = "0".($k+1);
		} else {
			$S[$k] = $k+1;
		}
	}
} else {
	$S = diferenciarArrays($SUR,64);
}

$rs4 = pg_exec("
			SELECT
				ch_almacen as cod,
				ch_nombre_almacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1'
			ORDER BY
				cod
		");

if($cod_almacen=="") {
	$cod_almacen = $almacen;
}

$rs5 = pg_exec("
			SELECT
				trim(tab_elemento) as cod,
				tab_descripcion
			FROM
				int_tabla_general
			WHERE
				tab_tabla = 'ALMA'
				AND tab_car_02 = '1'
				AND TRIM(tab_elemento) = TRIM('$cod_almacen')
			ORDER BY
				cod
		");

$R5 = "";

if(pg_numrows($rs5)>0)
	$R5 = pg_fetch_row($rs5,0);

$sucursal_val = $R5[0];
$sucursal_dis = $R5[1];

$rs6 = pg_exec("
			SELECT
				TRIM(lado) as lado
			FROM
				pos_cmblados
			ORDER BY
				lado
		");

pg_close();
?>

<html>
	<head>
		<title>sistemaweb</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<script language="JavaScript" src="../combustibles/miguel-funciones.js"></script>
			<script>
			function cambiarAction(form, txt){
				form.boton.value = txt;
				form.submit();
			}
			</script>
	</head>
	<body>
		<h2 style="color:#336699;" align="center">CONFIGURACION DE SURTIDORES</td>
		<form action='m_surtid_edit.php' method='post' name="form1">
			Almacen :
			<select name="cod_almacen" onChange="javascript:cambiarAction(form1,'change_alma') , form1.submit()" >
				<?php
				for($i=0;$i<pg_numrows($rs4);$i++) {
					$B = pg_fetch_row($rs4,$i);
					if($B[0]==$cod_almacen) {
						print "<option value='$B[0]' selected>$B[0] -- $B[1]</option>";
					} else {
						print "<option value='$B[0]' >$B[0] -- $B[1]</option>";
					}
				}
				?>
			</select>
			<p></p>
			<table border="0" cellpadding="0">
				<tr>
					<th class="grid_cabecera" width="20">&nbsp;</th>
					<th class="grid_cabecera" width="100">SURTIDOR</th>
					<th class="grid_cabecera" width="66">TANQUE</th>
					<th class="grid_cabecera" width="60">Nro. LADO</th>
					<th class="grid_cabecera" width="70">Nro. MANGUERA</th>
					<th class="grid_cabecera" width="120">ULT LECT S/.</th>
					<th class="grid_cabecera" width="120">ULT LECT GAL</th>
					<th class="grid_cabecera" width="120">&nbsp;</th>
					<th class="grid_cabecera" width="120">&nbsp;</th>
					<th class="grid_cabecera" width="120">&nbsp;</th>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="center"><select name='cod_surtidor' >
					<?php
						for($i=0;$i<count($S);$i++) {
							print "<option value=$S[$i]>$S[$i]</option>";
						}
					?>	
					</select></td>
					<td><select name='cod_tanque'>
					<?php
					$select_tanque = "";
					for($i=0;$i<pg_numrows($rs1);$i++) {
						$A = pg_fetch_row($rs1,$i);
						print "<option value='$A[0]'>$A[1]</option>";
						$select_tanque = $select_tanque."<option value='$A[0]'>$A[1]</option>";
					}
					?>
					</select><input type="hidden" name="cod_producto"></td>
					<td align="center"><select name="num_lado">
					<?php
						for($i=0; $i<pg_num_rows($rs6); $i++) {
							$A = pg_fetch_array($rs6, $i);
							echo "<option value='$A[0]'>$A[0]</option>";
						}
					?>
					</select></td>
					<td align="center"><select name="num_manguera">
						<option value="1"> 1</option>
						<option value="2"> 2</option>
						<option value="3"> 3</option>
						<option value="4"> 4</option>
					</select></td>
					<td>&nbsp; <input type='text' name='ult_lecvalor' size='15' onKeyUp="javascript:validarNumeroDecimales(this)"></td>
					<td><input type='text' name='ult_lecgalon' size='15' onKeyUp="javascript:validarNumeroDecimales(this)"> </td>
					<td><button name="boton1" type="submit" value="Agregar" onClick="javascript:cambiarAction(form1,'Agregar')"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button></td>
					<td>&nbsp;<input type="hidden" name="boton"></td>
				</tr>
				<tr><th  colspan="7">&nbsp;</th></tr>
				<tr>
					<th class="grid_cabecera" width="20">&nbsp;</th>
					<th class="grid_cabecera" width="110">SURTIDOR</th>
					<th class="grid_cabecera" width="66">TANQUE</th>
					<th class="grid_cabecera" width="100">LADO</th>
					<th class="grid_cabecera" width="90">MANGUERA</th>
					<th class="grid_cabecera" width="90">DESCRIPCION</th>
					<th class="grid_cabecera" width="90">PRECIO</th>
					<th class="grid_cabecera" width="120">ULT LECT S/.</th>
					<th class="grid_cabecera" width="120">ULT LECT GAL</th>
					<th class="grid_cabecera" width="120">FECHA</th>
					<!-- <?php
					for($i=0;$i<pg_numrows($rs2);$i++) {
						$B = pg_fetch_row($rs2,$i);
					///
					print "?> -->

				<tr>
					<th height='24'><input type='checkbox' name='cod_surtidor_arr[]' value='$B[0]'></th>
					<th><input type='text' name='arr_surti[$B[0]][0]' value='$B[0]' $readonly size='10'></th>
					<th><select name='arr_surti[$B[0]][1]'> <option value='$B[1]' selected>$B[1]</option> $select_tanque</select></th>
					<th><input type='text' name='arr_surti[$B[0]][7]' value='$B[7]'  size='3'></th>
					<th><input type='text' name='arr_surti[$B[0]][8]' value='$B[8]'  size='3'></th>
					<th><input type='text' name='arr_surti[$B[0]][2]' value='$B[2]' $readonly></th>
					<th><input type='text' name='arr_surti[$B[0]][3]' value='$B[3]' $readonly></th>
					<th><input type='text' name='arr_surti[$B[0]][4]' value='$B[4]' $readonly></th>
					<th><input type='text' name='arr_surti[$B[0]][5]' value='$B[5]' $readonly></th>
					<th><input type='text' name='arr_surti[$B[0]][6]' value='$B[6]' $readonly size='12'></th>
				</tr>
					<!--<?php "; } ?> -->
					<!-- <th>HORA ULT LECT</th> -->
				<tr>
					<td>&nbsp;</td>
					<td><button name="boton" type="submit" value="Modificar" onClick="javascript:cambiarAction(form1,'Modificar')"><img src="/sistemaweb/icons/update2.png" align="right" />Modificar</button></td>
					<td>&nbsp;</td>
					<td><button name="boton" type="submit" value="Eliminar" onClick="javascript:cambiarAction(form1,'Eliminar')"><img src="/sistemaweb/icons/delete.gif" align="right" />Eliminar</button></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table><br>
		</form>
	</body>
</html>
