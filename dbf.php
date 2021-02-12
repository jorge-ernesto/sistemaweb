<?php
include("../menu_princ.php");
include("../functions.php");
include("../utils/acceso_sistem.php");

if($cod_almacen!=""){$almacen=$cod_almacen;}
if($cod_almacen==""){$cod_almacen=$almacen;}
$irow=0;
$fecactualiz=date("Y-m-d");
$sql=" select ch_tanque,ch_codigocombustible,nu_capacidad,nu_ultimamedida,dt_fechaultimamedida from comb_ta_tanques ";
 switch ($boton) {
   case Agregar:		if(strlen($newcod)>0) {
			$xsqlbusc=pg_exec($coneccion,"select ch_tanque from comb_ta_tanques where ch_tanque='".$newcod."' and ch_tanque=trim('$cod_almacen')");
			if(pg_numrows($xsqlbusc)==0){
				if(strlen($newprec)==0) { $newprec=0; }
				$sqlins="insert into comb_ta_tanques(ch_tanque,ch_codigocombustible,nu_capacidad,
					nu_ultimamedida,dt_fechaultimamedida,dt_fechactualizacion,ch_usuario,ch_sucursal)
					values('".$newcod."','".$newprod."',".$newcapac.",".$newultlect.",'".$fecactualiz."','".$fecactualiz."','".$user."',trim('$cod_almacen'))";
				$xsqlins=pg_exec($coneccion,$sqlins);
			}else{  ?>
			<script>alert(" El Codigo ya existe !!! ")</script>
<?php			}
		}else{  ?>
			<script>alert(" Debe ingresar un Codigo válido")</script>
<?php		}
      break;
   case Modificar:

		$xsqlm=pg_exec($coneccion,$sql);
		$ilimitm=pg_numrows($xsqlm);
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			if($idm[$am0]==$idp[$am0]) {
				if(strlen($capac[$am0])==0) { $capac[$am0]=0; }
				if(strlen($ultlectg[$am0])==0) { $ultlectg[$am0]=0; }
				$sqlupd=" update comb_ta_tanques set ch_codigocombustible='".$xprod[$am0]."',
				nu_capacidad=".$capac[$am0].",nu_ultimamedida='".$ultlectg[$am0]."',
				dt_fechactualizacion='".$fecactualiz."'
				where ch_tanque='".$idm[$am0]."' 
				and ch_sucursal=trim('$cod_almacen')
				";
				$xsqlupd=pg_exec($coneccion,$sqlupd);
			}
			$irowm++;
		}

      break;
   case Eliminar:

		$xsqlm=pg_exec($coneccion,$sql);
		$ilimitm=pg_numrows($xsqlm);
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
			if($idm[$am0]==$idp[$am0]) {
				$sqlupd=" delete from comb_ta_tanques where ch_tanque='".$idm[$am0]."' and ch_sucursal=trim('$cod_almacen') ";
				$xsqlupd=pg_exec($coneccion,$sqlupd);
			}
			$irowm++;
		}


      break;
// default:
 }
$rs4 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' 
 order by ch_nombre_almacen");
if($cod_almacen==""){$cod_almacen=$almacen;}
$rs5 = pg_exec("select ch_almacen ,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' 
and  trim(ch_almacen)=trim('$cod_almacen')  order by ch_nombre_almacen");
if(pg_numrows($rs5)>0)$R5 = pg_fetch_row($rs5,0);
$sucursal_val = $R5[0];
$sucursal_dis = $R5[1];


?>
<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script>
function cambiarAction(form, txt){
form.boton.value = txt;
form.submit();
//alert(form.boton.value);
}
</script>
</head>

<body>
CONFIGURACION DE TANQUES
<hr noshade>ALMACEN:<?php echo $almacen;?>

<form action='' method='post' name="form1">
  Sucursales 
  <select name="cod_almacen" onChange="javascript:cambiarAction(form1,'change_alma') , form1.submit()" >
    <?php 
			if($cod_almacen!=""){ print "<option value='$sucursal_val' selected>$sucursal_val -- $sucursal_dis</option>"; }
			for($i=0;$i<pg_numrows($rs4);$i++){		
			$B = pg_fetch_row($rs4,$i);		
			print "<option value='$B[0]'>$B[0] -- $B[1]</option>";	
		  }
		  ?>
  </select>
  <table border="1" cellspacing="0" cellpadding="0">
  <tr>
      <th>&nbsp;</th>
      <th>CODIGO</th>
      <th>PRODUCTO</th>
      <th>CAPACIDAD</th>
      <th>ULT LECT GAL</th>
      <th>FECHA ULT LECT</th>
  </tr>
   <tr>
      <td>&nbsp;</td>
      <td>&nbsp;<input type='text' name='newcod' size='10' maxlength='2'></td>
      <td>&nbsp;<select name='newprod'>
<?php   $sqlprod=" select ch_codigocombustible,ch_nombrecombustible from comb_ta_combustibles order by ch_codigocombustible ";
	$xsqlprod=pg_exec($coneccion,$sqlprod);
	$ilimitprod=pg_numrows($xsqlprod);
	$irowprod=0;
	while($irowprod<$ilimitprod) {
		$p0=pg_result($xsqlprod,$irowprod,0);
		$p1=pg_result($xsqlprod,$irowprod,1);
		echo "<option value='".$p0."'>".$p1."</option>";
		$irowprod++; 
	}  
?>    </select>

	</td>
      <td>&nbsp;<input type='text' name='newcapac' size='15'></td>
      <td>&nbsp;<input type='text' name='newultlect' size='15'></td>
      <td>&nbsp;<input type='submit' name='boton' value='Agregar'></td>

  </tr>
  <tr>
      <td>&nbsp;</td>
      <td><input name="boton" type="submit"  value="Modificar"></td>
      <td>&nbsp;</td>
      <td><input name="boton" type="submit" value="Eliminar"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <th>CODIGO</th>
    <th>PRODUCTO</th>
    <th>CAPACIDAD</th>
    <th>ULT LECT GAL</th>
    <th>FECHA ULT LECT</th>
  </tr>
<?php
$irow=0;
$sql=" select ch_tanque,ch_codigocombustible,nu_capacidad,nu_ultimamedida,dt_fechaultimamedida
from comb_ta_tanques where ch_sucursal=trim('$cod_almacen') order by ch_tanque";

$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {
	$a0=pg_result($xsql,$irow,0);
	$a1=pg_result($xsql,$irow,1);
	$a2=pg_result($xsql,$irow,2);
	$a3=pg_result($xsql,$irow,3);
	$a4=pg_result($xsql,$irow,4);
	$xprod[$a0]=$a1;
	echo "<tr><td><input type='checkbox' name='idp[$a0]' value='".$a0."'></td><td>&nbsp;".$a0."</td><td><select name='xprod[$a0]'>";
	$sqlprod=" select ch_codigocombustible,ch_nombrecombustible from comb_ta_combustibles order by ch_codigocombustible ";

	$xsqlprod=pg_exec($coneccion,$sqlprod);
	$ilimitprod=pg_numrows($xsqlprod);
	$irowprod=0;
	while($irowprod<$ilimitprod) {
		$p0=pg_result($xsqlprod,$irowprod,0);
		$p1=pg_result($xsqlprod,$irowprod,1);
		if($p0==$xprod[$a0]) {
		  echo "<option value='".$p0."' selected>".$p1."</option>";
		} else {
		  echo "<option value='".$p0."'>".$p1."</option>";
		}
		$irowprod++;
	}

	echo "</select></td>";
    echo "<td align='right'>&nbsp;<input type='text' name='capac[$a0]' value='".$a2."' style='text-align:right' size='15'></td><td align='right'>&nbsp;<input type='text' name='ultlectg[$a0]' value='".$a3."' style='text-align:right'  size='15'></td>";
    echo "<td align='right'>&nbsp;".$a4."</td></tr>";
	$irow++;
}	
?>   
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
    <td><input name="boton" type="submit"  value="Modificar"></td>
    <td>&nbsp;</td>
    <td><input name="boton" type="submit" value="Eliminar"></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
</form>
<br>
</body>
</html>  
<?php include("../close_connect.php"); ?>
