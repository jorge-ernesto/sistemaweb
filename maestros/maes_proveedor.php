<?php
include("../config.php");
include("../menu_princ.php");
include("../functions.php");
$coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=postgres");
if($txtcampo=="A")
	{
	$ch=" checked";
	}
elseif($txtcampo=="B")
	{
	$ch1=" checked";
	}
elseif($txtcampo=="C")
	{
	$ch2=" checked";
	}
else 
	{ 
	$ch=" checked";
	}
$txtxbusqueda=strtoupper($txtxbusqueda);
if($boton=="buscar" or strlen(trim($txtxbusqueda))>0){
	if($txtcampo=="A")
		{
		$addsql=" where pro_codigo='".$txtxbusqueda."' ";
		}
	elseif($txtcampo=="B") 
		{
		$addsql=" where pro_razsocial like '".$txtxbusqueda."%' ";
		}
	elseif($txtcampo=="C")
		{
		$addsql=" where pro_razsocial like '%".$txtxbusqueda."%' ";
		}
	else
		{
		$addsql=" ";
		}
	}
else
	{   
	$bddsql=" limit 20";  
	}
?>

<html>
	<link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
	<head>
		<title>sistemaweb</title>
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
	</head>
	<body>
		<table align="center">
			<tr>
				<td>B&uacute;squeda r&aacute;pida: </td>
				<td>
					<input name="txtxbusqueda" type="text" value="<?php echo $txtxbusqueda; ?>">
				</td>
				<td>
					<input name="boton" type="submit" value="Buscar">
				</td>
			</tr>
			<tr>
				<td colspan="3"><input name="txtcampo" type="radio" value="A" <?php echo $ch;?>>
        			Código <input type="radio" name="txtcampo" value="B" <?php echo $ch1;?>>
        			Raz&oacute;n Social (iniciales) <input type="radio" name="txtcampo" value="C" <?php echo $ch2;?>>
        			Raz&oacute;n Social (contenido) </td>
			</tr>
		</table><br>
		<h2 align="center" style="color:#336699"><b>MANTENIMIENTO DE PROVEEDORES</b></h2>
			<input type="hidden" name="varx" value="<?php echo $varx;?>">
		<table align="center">
			<tr> 
				<th class="grid_cabecera">&nbsp;</th>
				<th class="grid_cabecera">CÓDIGO</th>
				<th class="grid_cabecera">RAZ SOC LARGA</th>
				<th class="grid_cabecera">RAZ SOC CORTA</th>
				<th class="grid_cabecera">DIRECCiÓN</th>
				<th class="grid_cabecera">RUC</th>
				<th class="grid_cabecera">TIPO MONEDA</th>
				<th class="grid_cabecera">TELEFONO</th>
				<th class="grid_cabecera">FAX</th>
    		</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input name='newcod' type='text' size='12' maxlength='12'></td>
				<td><input name='newrazlarga' type='text' size='40' maxlength='40'></td>
				<td><input name='newrazcorta' type='text' size='20' maxlength='20'></td>
				<td><input name='newdir' type='text' size='40' maxlength='40'></td>
				<td><input name='newruc' type='text' size='11' maxlength='11'></td>
				<td><input name='newtipomon' type='text' size='2' maxlength='2'></td>
				<td><input name='newfono1' type='text' size='12' maxlength='12'></td>
				<td><input name='newfono2' type='text' size='12' maxlength='12'></td>
			</tr>
			<tr> 
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<!--<td><input name='boton' type='submit' value='adicionar'></td>-->
				<td>
					<input name='boton' type='submit' value='Ins' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';" disabled="true">
					<input name='boton' type='submit' value='Mod' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';" disabled="true">
					<input name='boton' type='submit' value='Del' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';" disabled="true">
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
				<?php
				$sql="SELECT
						pro_codigo,
						pro_razsocial,
						pro_rsocialbreve,
						pro_direccion,
						pro_ruc,
						pro_moneda,
						pro_telefono1,
						pro_telefono2
					FROM
						int_proveedores ".$addsql."
					ORDER BY 1 ".$bddsql." "
					;

				$xsql=pg_exec($coneccion,$sql);
				$ilimit=pg_numrows($xsql);

				while($irow<$ilimit) {
					$cod 		= pg_result($xsql,$irow,0);
					$razslarga 	= pg_result($xsql,$irow,1);
					$razscorta 	= pg_result($xsql,$irow,2);
					$dir 		= pg_result($xsql,$irow,3);
					$ruc 		= pg_result($xsql,$irow,4);
					$tipomon 	= pg_result($xsql,$irow,5);
					$fono1 		= pg_result($xsql,$irow,6);
					$fono2 		= pg_result($xsql,$irow,7);
						
				echo "<tr><td><input type='checkbox' name='id_".$cod."' value='".$cod."'></td>";
				echo "<td><input name='cod_".$cod."' type='text' size='12' maxlength='12' value='".$cod."' readonly></td>";
				echo "<td><input name='razlarga_".$cod."' type='text' size='40' maxlength='40' value='".$razslarga."'></td>";
				echo "<td><input name='razcorta_".$cod."' type='text' size='20' maxlength='20' value='".$razscorta."'></td>";
				echo "<td><input name='dir_".$cod."' type='text' size='40' maxlength='40' value='".$dir."'></td>";
				echo "<td><input name='ruc_".$cod."' type='text' size='11' maxlength='11' value='".$ruc."'></td>";
				echo "<td><input name='tipomon_".$cod."' type='text' size='2' maxlength='1' value='".$tipomon."'></td>";
				echo "<td><input name='fono1_".$cod."' type='text' size='15' maxlength='12' value='".$fono1."'></td>";
				echo "<td><input name='fono2_".$cod."' type='text' size='15' maxlength='12' value='".$fono2."'></td></tr>";
					
				$irow++;
				}
				?>

			<tr> 
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>
					<input name='boton' type='submit' value='Ins' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';" disabled="true">
					<input name='boton' type='submit' value='Mod' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';" disabled="true">
					<input name='boton' type='submit' value='Del' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';" disabled="true">
				</td>
				<!--<td>
					<input name='boton' type='submit' value='eliminar'>
				</td>
				<td>
					<input name='boton' type='submit' value='modificar'>
				</td> -->
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</table>
	</body>
</html>

<?php
pg_close($coneccion);
