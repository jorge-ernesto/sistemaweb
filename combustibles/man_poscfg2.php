<?php
/*session_start();
include("../config.php");
require("../clases/funciones.php");
include("../combustibles/inc_top.php");*/
include("../menu_princ.php");
include("../functions.php");
// include("../utils/acceso_sistem.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");


if ( is_null($almacen) or trim($almacen)=="")
	{
	$almacen="001";

	}
else

$new_estacion=$almacen;

if ($new_estacion=="001")
{
	$sql = "select POS, TIPO, ACTINV, PRE_LISTA_PRECIO, INTERF
		,LD01, LD02, LD03, LD04, LD05, LD06, LD07
		,LD08, LD09, LD10, LD11, LD12, LD13, LD14, LD15, LD16
		,IP, NROSERIE, AUTSUNAT, PC_SAMBA, PRN_SAMBA, RUTAPRINT
		,NUME, ES, DISPOSITIVO, EJECT, LINES, CH_SUCURSAL, MENSAJE_CAB
		from POS_CFG
		order by ch_sucursal, es, pos ";
	$editar="";
}
else
{
	$sql = "select POS, TIPO, ACTINV, PRE_LISTA_PRECIO, INTERF
		,LD01, LD02, LD03, LD04, LD05, LD06, LD07
		,LD08, LD09, LD10, LD11, LD12, LD13, LD14, LD15, LD16
		,IP, NROSERIE, AUTSUNAT, PC_SAMBA, PRN_SAMBA, RUTAPRINT
		,NUME, ES, DISPOSITIVO, EJECT, LINES, CH_SUCURSAL, MENSAJE_CAB
		from POS_CFG
		order by ch_sucursal, es, pos ";
	$editar=" readonly tabindex=-1 ";
}
//echo $sql;
$ip_remote = $_SERVER['REMOTE_ADDR'];

if($usuario=="SISTEMAS")
	{ $editar="";}



//echo $boton;
switch ($boton) {
	case Agregar:
		if(strlen($new_pos)>0)
		{
			$okgraba=true;
			$sql_busc = "select pos from pos_cfg where trim(pos)='".trim($new_pos)."' and trim(es)='".trim($almacen)."' ";
			$xsqlbusc=pg_query($conector_id,$sql_busc);
			if(pg_num_rows($xsqlbusc)>0)
			{
				$okgraba=false;
				$mensaje="Error!! \\n POS ya Existe";
			}
			else
			{
				if(strlen($new_tipo)>0)
				{
					$okgraba=true;
					$sql_busc = "select tab_elemento from int_tabla_general where trim(tab_tabla)='TPOS' and trim(tab_elemento)='".trim($new_tipo)."' and tab_elemento!='000000'";
					$xsqlbusc=pg_query($conector_id,$sql_busc);
					if(pg_num_rows($xsqlbusc)==0)
					{
						$okgraba=false;
						$mensaje="Error!! \\n Tipo no es Combustible o Market";
					}
					else
					{
						if(strlen($new_actinv)>0)
						{
							$okgraba=true;
							if( ($new_actinv!="S") && ($new_actinv!="N")  )
							{
								$okgraba=false;
								$mensaje="Error!! \\n Actualiza Inventario S o N";
							}
							else
							{
								if(strlen($new_interf)>0)
								{
									$okgraba=true;
									$sql_busc = "select tab_elemento from int_tabla_general where trim(tab_tabla)='TINT' and trim(tab_elemento)='".trim($new_interf)."' and tab_elemento!='000000'";
									$xsqlbusc=pg_query($conector_id,$sql_busc);
									if(pg_num_rows($xsqlbusc)==0)
									{
										$okgraba=false;
										$mensaje="Error!! \\n Interfase no existe en lista";
									}
								}
								else
								{
									$okgraba=false;
									$mensaje="Error!! \\n Interfase en Blanco ";
								}
							}
						}
						else
						{
							$okgraba=false;
							$mensaje="Error!! \\n Actualiza Inventarios en Blanco ";
						}
					}
				}
				else
				{
					$okgraba=false;
					$mensaje="Error!! \\n Tipo POS en Blanco";
				}
			}
		}
		else
		{
			$okgraba=false;
			$mensaje="Error!! \\n POS en Blanco";
		}



		if($okgraba==true)
		{
			$new_sucursal=pg_result(pg_query($conector_id,"select ch_sucursal from inv_ta_almacenes where ch_almacen='".$new_estacion."' "),0,0);
			$sql_insert = "INSERT INTO POS_CFG
				( CH_SUCURSAL, ES, POS, TIPO, ACTINV, INTERF)
				values
				('$new_sucursal','$new_estacion','$new_pos','$new_tipo','$new_actinv','$new_interf')";
			//echo $sql_insert;
			$xsql_insert = pg_exec($conector_id, $sql_insert);
			if($xsql_insert)
			{
				$new_pos="";
				$new_tipo="";
				$new_actinv="";
				$new_interf="";
			}
		}
		else
		{
			echo '<script>alert("'.$mensaje.'")</script>';
		}
	break;

	case Modificar:

		$xsqlm=pg_query($conector_id,$sql);
		$ilimitm=pg_num_rows($xsqlm);
		$irowm=0;
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			//echo "--".$idm[$am0]."--".$idp[$am0]."--<br>";
			if($idm[$am0]==$idp[$am0]) {

				if(strlen($tipo[$am0])>0)
				{
					$okgraba=true;
					$sql_busc = "select tab_elemento from int_tabla_general where trim(tab_tabla)='TPOS' and trim(tab_elemento)='".trim($tipo[$am0])."' and tab_elemento!='000000'";
					$xsqlbusc=pg_query($conector_id,$sql_busc);
					if(pg_num_rows($xsqlbusc)==0)
					{
						$okgraba=false;
						$mensaje="Error!! \\n Tipo no es Combustible o Market";
					}
					else
					{
						if(strlen($actinv[$am0])>0)
						{
							$okgraba=true;
							if( ($actinv[$am0]!="S") && ($actinv[$am0]!="N")  )
							{
								$okgraba=false;
								$mensaje="Error!! \\n Actualiza Inventario S o N";
							}
							else
							{
								if(strlen($interface[$am0])>0)
								{
									$okgraba=true;
									$sql_busc = "select tab_elemento from int_tabla_general where trim(tab_tabla)='TINT' and trim(tab_elemento)='".trim($interface[$am0])."' and tab_elemento!='000000'";
									$xsqlbusc=pg_query($conector_id,$sql_busc);
									if(pg_num_rows($xsqlbusc)==0)
									{
										$okgraba=false;
										$mensaje="Error!! \\n Interfase no existe en lista";
									}
									else
									{
										if(strlen($lprecio[$am0])>0)
										{
											$okgraba=true;
											$sql_busc = "select tab_elemento from int_tabla_general where trim(tab_tabla)='LPRE' and trim(tab_elemento)='".trim($lprecio[$am0])."' and tab_elemento!='000000'";
											$xsqlbusc=pg_query($conector_id,$sql_busc);
											if(pg_num_rows($xsqlbusc)==0)
											{
												$okgraba=false;
												$mensaje="Error!! \\n Lista de Precios no existe ";
											}
										}
										else
										{
											$okgraba=false;
											$mensaje="Error!! \\n Lista Precios en Blanco ";
										}
									}
								}
								else
								{
									$okgraba=false;
									$mensaje="Error!! \\n Interfase en Blanco ";
								}
							}
						}
						else
						{
							$okgraba=false;
							$mensaje="Error!! \\n Actualiza Inventarios en Blanco ";
						}
					}
				}
				else
				{
					$okgraba=false;
					$mensaje="Error!! \\n Tipo POS en Blanco";
				}


				if($okgraba==true)
				{
					$sqlupd=" update  POS_CFG set
						  interf='".$interface[$am0]."'
						,ld01='".$ld01[$am0]."'
						,ld02='".$ld02[$am0]."'
						,ld03='".$ld03[$am0]."'
						,ld04='".$ld04[$am0]."'
						,ld05='".$ld05[$am0]."'

						,ld06='".$ld06[$am0]."'
						,ld07='".$ld07[$am0]."'
						,ld08='".$ld08[$am0]."'
						,ld09='".$ld09[$am0]."'
						,ld10='".$ld10[$am0]."'

						,ld11='".$ld11[$am0]."'
						,ld12='".$ld12[$am0]."'
						,ld13='".$ld13[$am0]."'
						,ld14='".$ld14[$am0]."'

						,ld15='".$ld15[$am0]."'
						,ld16='".$ld16[$am0]."'

						,ip='".$ip[$am0]."'
						,nroserie='".$nro_serie[$am0]."'
						,autsunat='".$aut_sunat[$am0]."'
						,pc_samba='".$name_samba[$am0]."'
						,prn_samba='".$print_name[$am0]."'
						,rutaprint='".$ruta_print[$am0]."'
						,eject='".$eject[$am0]."'
						,lines='".$lines[$am0]."'
						,dispositivo='".$dispositivo[$am0]."'
						,mensaje_cab='".$mensaje_cab[$am0]."'
						where trim(pos)='".$idm[$am0]."' and trim(ch_sucursal)='".trim($almacen)."' ";

					//echo $sqlupd;
					$xsqlupd=pg_exec($conector_id,$sqlupd);
				}
				else
				{
					echo '<script>alert("'.$mensaje.'")</script>';
				}
			}
			$irowm++;
		}
	break;

	case Eliminar:
		$xsqlm=pg_exec($conector_id,$sql);
		$ilimitm=pg_numrows($xsqlm);
		$irowm=0;
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			if($idm[$am0]==$idp[$am0]) {
				$sqlupd="delete from pos_cfg
						 where trim(pos) ='".$idm[$am0]."' and trim(es)='".trim($almacen)."' ";
				$xsqlupd=pg_exec($conector_id,$sqlupd);
			}
			$irowm++;
		}
	break;
}



?>

<script language="javascript">

function agregar()
{
	var n_lado = formular.new_lado.value;
	if(n_lado>0)
	{
		document.formular.submit();
	}
	else {
		alert("LADO VACIO");
	}
}

</script>

<form name="formular" action="" method="post">
<table border="1" cellspacing="0" cellpadding="0">
	<tr>
		<th>
		
      <th>SUC
<th>POS
		<th>TIPO
		
      <th>INV 
      <th>INTERF
	</tr>
	<tr>
		<td>
		<td align="center">&nbsp;<?php echo $new_estacion; ?>
		<td><input type='text' name='new_pos' value='<?php echo $new_pos; ?>' size='5' maxlength='1'>
		<td><input type='text' name='new_tipo' value='<?php echo $new_tipo; ?>' size='5' maxlength='1'>
		<td><input type='text' name='new_actinv' value='<?php echo $new_actinv; ?>' size='5' maxlength='1'>
		<td><input type='text' name='new_interf' value='<?php echo $new_interf; ?>' size='5' maxlength='1'>
		<td colspan="3"><input type="submit" name="boton" value="Agregar">
	</tr>
	<tr>
		<th>
		
      <th>SUC
      <th>CC
<th>POS
		<th>TIPO
		
      <th>INV 
      <th>LP
<th>INTERF
		<th>LD01
		<th>LD02
		<th>LD03
		<th>LD04
		<th>LD05
		<th>LD06
		<th>LD07
		<th>LD08
		<th>LD09
		<th>LD10
		<th>LD11
		<th>LD12
		<th>LD13
		<th>LD14
		<th>LD15
		<th>LD16
		<th>IP
		<th>SERIE
		<th>AUT SUNAT
		<th>NOMBRE POS
		<th>NOMBRE IMPRESORA
		<th>RUTA IMPRESION
		<th>DISPOSITIVO
		<th>EJECT
		<th>LINES
		<th>MENSAJE
		<th>NUMERO<BR>TICKET
	<tr>
		<td>
		<th colspan="2"><input type="submit" name="boton" value="Modificar">
      <th colspan="2"> 
      <td><td><td><td><td><td><td><td>
		<td><td><td><td><td><td><td><td>
		<td><td><td><td><td><td><td><td>&nbsp;
	<?php
		$xsql = pg_query($conector_id, $sql);
		$i=0;
		while($i<pg_num_rows($xsql))
		{
			$rs = pg_fetch_array($xsql, $i);
			$a= $rs[0];
			?>
			<tr bgcolor=""
			onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';"
			onMouseOut="this.style.backgroundColor=''">
			<?php
		
			echo "
					<td><input type='checkbox' name='idp[$a]' value='$rs[0]'>
					<td align='center'>$rs[32]
					<td align='center'>$rs[28]
					<td align='center'>$a
					<td align='center'><input type='text' name='tipo[$a]' value='$rs[1]' size='2' maxlength='1'>
					
					<td align='center'><input type='text' name='actinv[$a]' value='$rs[2]' size='2' maxlength='1' $editar>
					<td align='center'><input type='text' name='lprecio[$a]' value='$rs[3]' size='4' maxlength='2' $editar>
					
					<td align='center'><input type='text' name='interface[$a]' value='$rs[4]' size='2' maxlength='1'>

					<td><input type='text' name='ld01[$a]' value='$rs[5]' size='3' maxlength='1'>
					<td><input type='text' name='ld02[$a]' value='$rs[6]' size='3' maxlength='1'>
					<td><input type='text' name='ld03[$a]' value='$rs[7]' size='3' maxlength='1'>
					<td><input type='text' name='ld04[$a]' value='$rs[8]' size='3' maxlength='1'>
					<td><input type='text' name='ld05[$a]' value='$rs[9]' size='3' maxlength='1'>
					<td><input type='text' name='ld06[$a]' value='$rs[10]' size='3' maxlength='1'>
					<td><input type='text' name='ld07[$a]' value='$rs[11]' size='3' maxlength='1'>
					<td><input type='text' name='ld08[$a]' value='$rs[12]' size='3' maxlength='1'>
					<td><input type='text' name='ld09[$a]' value='$rs[13]' size='3' maxlength='1'>
					<td><input type='text' name='ld10[$a]' value='$rs[14]' size='3' maxlength='1'>
					<td><input type='text' name='ld11[$a]' value='$rs[15]' size='3' maxlength='1'>
					<td><input type='text' name='ld12[$a]' value='$rs[16]' size='3' maxlength='1'>
					<td><input type='text' name='ld13[$a]' value='$rs[17]' size='3' maxlength='1'>
					<td><input type='text' name='ld14[$a]' value='$rs[18]' size='3' maxlength='1'>
					<td><input type='text' name='ld15[$a]' value='$rs[19]' size='3' maxlength='1'>
					<td><input type='text' name='ld16[$a]' value='$rs[20]' size='3' maxlength='1'>


					<td><input type='text' name='ip[$a]' value='$rs[21]' size='20' maxlength='15'>
					<td><input type='text' name='nro_serie[$a]' value='$rs[22]' size='15' maxlength='15'>
					<td><input type='text' name='aut_sunat[$a]' value='$rs[23]' size='15' maxlength='15'>
					<td><input type='text' name='name_samba[$a]' value='$rs[24]' size='15' maxlength='15'>
					<td><input type='text' name='print_name[$a]' value='$rs[25]' size='15' maxlength='15'>
					<td><input type='text' name='ruta_print[$a]' value='$rs[26]' size='15' maxlength='30'>


					<td><input type='text' name='dispositivo[$a]' value='$rs[29]' size='15' maxlength='20'>
					<td><input type='text' name='eject[$a]' value='$rs[30]' size='5' maxlength='5' >
					<td><input type='text' name='lines[$a]' value='$rs[31]' size='5' maxlength='5' >
					<td><input type='text' name='mensaje_cab[$a]' value='$rs[33]' size='15' maxlength='200'>
					<td align='center'>$rs[27]
					";
			$i++;
		}
	?>
	<tr>
		<td>
		<th colspan="2"><input type="submit" name="boton" value="Modificar">
		
      <th colspan="2"> 
      <td>
		<td>
		<td>
</table>

<BR>

<table border="1" cellspacing="0" cellpadding="0">

	<tr>
		<th>TRABAJADOR
		<th>LD01
		<th>LD02
		<th>LD03
		<th>LD04
		<th>LD05
		<th>LD06
		<th>LD07
		<th>LD08
		<th>LD09
		<th>LD10
		<th>LD11
		<th>LD12
		<th>LD13
		<th>LD14
		<th>LD15
		<th>LD16
	</tr>
	<?php
		$sql  = "select p.ch_codigo_trabajador||' - '||p.ch_apellido_paterno||p.ch_apellido_materno||p.ch_nombre1||p.ch_nombre2 , lado from pos_cmblados, pla_ta_trabajadores p  where pos_cmblados.ch_codigo_trabajador=p.ch_codigo_trabajador order by p.ch_codigo_trabajador,lado";
		$xsql = pg_query($conector_id, $sql);
		$i=0;
		while($i<pg_num_rows($xsql))
		{
			$rs = pg_fetch_array($xsql, $i);
			$clave= $rs[0];
			?>
			<tr bgcolor="#CCCC99"
			onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';"
			onMouseOut="this.style.backgroundColor='#CCCC99'">
			<?php
			echo "<td>$rs[0]";
			$lado01=" -- ";
			$lado02=" -- ";
			$lado03=" -- ";
			$lado04=" -- ";
			$lado05=" -- ";
			$lado06=" -- ";
			$lado07=" -- ";
			$lado08=" -- ";
			$lado09=" -- ";
			$lado10=" -- ";
			$lado11=" -- ";
			$lado12=" -- ";
			$lado13=" -- ";
			$lado14=" -- ";
			$lado15=" -- ";
			$lado16=" -- ";

			while ( $clave == $rs[0] and $i<pg_num_rows($xsql) )
			{
				$lado="lado".$rs[1];
				$$lado="ok";
				$i++;
				if ($i<pg_num_rows($xsql) )
				{
					$rs= pg_fetch_array($xsql,$i);
				}
			}

			echo "<td align='center'>".$lado01."</td>";
			echo "<td align='center'>".$lado02."</td>";
			echo "<td align='center'>".$lado03."</td>";
			echo "<td align='center'>".$lado04."</td>";
			echo "<td align='center'>".$lado05."</td>";
			echo "<td align='center'>".$lado06."</td>";
			echo "<td align='center'>".$lado07."</td>";
			echo "<td align='center'>".$lado08."</td>";
			echo "<td align='center'>".$lado09."</td>";
			echo "<td align='center'>".$lado10."</td>";
			echo "<td align='center'>".$lado11."</td>";
			echo "<td align='center'>".$lado12."</td>";
			echo "<td align='center'>".$lado13."</td>";
			echo "<td align='center'>".$lado14."</td>";
			echo "<td align='center'>".$lado15."</td>";
			echo "<td align='center'>".$lado16."</td>";

			echo "</tr>";
		}
	?>
	</tr>

</table>

</form>
<?php pg_close($conector_id); ?>

