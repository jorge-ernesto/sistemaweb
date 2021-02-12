<?php
include("../config.php");
include("inc_top.php");

 if($txtcampo=="A"){ $ch=" checked"; } elseif($txtcampo=="B"){ $ch1=" checked"; } else { $ch=" checked"; }

if($boton=="buscar" or strlen(trim($txtxbusqueda))>0) {
  if($txtcampo=="A") {
    $addsql=" where pro_codigo='".$txtxbusqueda."' ";
  } elseif($txtcampo=="B") {
    $addsql=" where pro_razsocial like '%".$txtxbusqueda."%' ";
  } else {
    $addsql=" ";
  }

}

if($boton=="adicionar") {
  $sqlai="select pro_codigo from int_proveedores where pro_codigo='".$newcod."' ";
//  echo $sqlai;
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
  if($ilimitai==0) {
  $sqli="insert into int_proveedores(pro_codigo,pro_razsocial,pro_rsocialbreve,
   pro_direccion,pro_ruc,pro_moneda,pro_telefono1,pro_telefono2)
   values('".$newcod."','".$newrazlarga."','".$newrazcorta."','".$newdir."','".$newruc."',
   '".$newtipomon."','".$newfono1."','".$newfono2."')";
  $xsqli=pg_exec($coneccion,$sqli);
  } else {
     echo "el codigo del proveedor ya existe !!!";
  }
}

if($boton=="eliminar") {
 $sql2="select pro_codigo
 from int_proveedores ".$addsql." order by 1 limit 10";
 $xsql2=pg_exec($coneccion,$sql2);
 $ilimit2=pg_numrows($xsql2);
 while($irow2<$ilimit2) {
	$cod=pg_result($xsql2,$irow2,0);
//	$elem=pg_result($xsql2,$irow2,1);
	$xelem="id_".$cod;
//  $sql1="delete from int_tabla_general where tab_tabla='".$_POST['varx']."' and tab_tabla='".$_POST['varx']."'";
   if($$xelem==$cod) {
    $sql1="delete from int_proveedores where pro_codigo='".$$xelem."'";
    $xsql1=pg_exec($coneccion,$sql1);
  }
  $irow2++;
 } 
}

if($boton=="modificar") {
 $sql2="select pro_codigo,pro_razsocial,pro_rsocialbreve,
 pro_direccion,pro_ruc,pro_moneda,pro_telefono1,pro_telefono2
 from int_proveedores ".$addsql." order by 1 limit 10";
 $xsql2=pg_exec($coneccion,$sql2);
 $ilimit2=pg_numrows($xsql2);
 while($irow2<$ilimit2) {
	$cod=pg_result($xsql2,$irow2,0);
//	$elem=pg_result($xsql2,$irow2,1);
	$xelem="id_".$cod;
	$updrazlarga="razlarga_".$cod;
	$updrazcorta="razcorta_".$cod;
	$upddir="dir_".$cod;
	$updruc="ruc_".$cod;
	$updtipomon="tipomon_".$cod;
	$updfono1="fono1_".$cod;
	$updfono2="fono2_".$cod;

   if($$xelem==$cod) {
    $sql1="update int_proveedores set 
	 pro_razsocial='".$$updrazlarga."',pro_rsocialbreve='".$$updrazcorta."',
	 pro_direccion='".$$upddir."',pro_ruc='".$$updruc."',pro_moneda='".$$updtipomon."',
	 pro_telefono1='".$$updfono1."',pro_telefono2='".$$updfono2."' 
	 where pro_codigo='".$$xelem."' ";
	$xsql1=pg_exec($coneccion,$sql1);
   }
  $irow2++;
 } 
}

/*
if($boton=="buscar") {
  if($txtcampo=="A") {
    $addsql=" and tab_elemento='".$txtxbusqueda."' ";
  } elseif($txtcampo=="B") {
    $addsql=" and tab_descripcion like '%".$txtxbusqueda."%' ";
  } else {
    $addsql=" ";
  }
}
*/
?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
</head>

<body>
<form action="" method="post">
<br>
<table><tr><td>
Busqueda r&aacute;pida: </td>
<td><input name="txtxbusqueda" type="text" value="<?php echo $txtxbusqueda; ?>"></td>
  <td><input name="boton" type="submit" value="buscar"></td></tr>
  <tr><td colspan="3"><input name="txtcampo" type="radio" value="A" <?php echo $ch;?>>
        Código<input type="radio" name="txtcampo" value="B" <?php echo $ch1;?>>
        Raz&oacute;n Social</td>
    </tr></table>
  <br>
  
  <p>CODIGOS INCONSISTENTES</p>
<!--<input type="hidden" name="addsql" value="<?php echo $addsql;?>">-->
<input type="hidden" name="varx" value="<?php echo $varx;?>">
  <table border='1' cellpadding='0' cellspacing='0'>
    <tr> 
      <th>&nbsp;</th>
      <th>CODIGO</th>
      <th>RAZ SOC LARGA</th>
	  <th>RAZ SOC CORTA</th>
      <th>DIRECC</th>
      <th>RUC</th>
	  <th>TIPO MONEDA</th>
      <th>TELEFONO</th>
      <th>FAX</th>

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
      <td><input name='boton' type='submit' value='adicionar'></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
	  <td>&nbsp;</td>
    </tr>
    <?php
//  $_POST['varx']=20;
/*$sql="select tab_tabla,tab_elemento,tab_descripcion,tab_desc_breve,tab_num_01
 from int_tabla_general where tab_tabla='".$_POST['varx']."' ";  */
$sql="select pro_codigo,pro_razsocial,pro_rsocialbreve,
 pro_direccion,pro_ruc,pro_moneda,pro_telefono1,pro_telefono2
 from int_proveedores ".$addsql." order by 1 limit 10";
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {
	$cod=pg_result($xsql,$irow,0);
//	$elem=pg_result($xsql,$irow,1);
	$razslarga=pg_result($xsql,$irow,1);
	$razscorta=pg_result($xsql,$irow,2);
	$dir=pg_result($xsql,$irow,3);
	$ruc=pg_result($xsql,$irow,4);
	$tipomon=pg_result($xsql,$irow,5);
	$fono1=pg_result($xsql,$irow,6);
	$fono2=pg_result($xsql,$irow,7);
  if($elem!="000000") {
  echo "<tr><td><input type='checkbox' name='id_".$cod."' value='".$cod."'></td>";
  echo "<td><input name='cod_".$cod."' type='text' size='12' maxlength='12' value='".$cod."' readonly></td>";
// echo "<td><input name='elem_".$cod."' type='text' size='7' maxlength='6' value='".$elem."' readonly></td>";
  echo "<td><input name='razlarga_".$cod."' type='text' size='40' maxlength='40' value='".$razslarga."'></td>";
  echo "<td><input name='razcorta_".$cod."' type='text' size='20' maxlength='20' value='".$razscorta."'></td>";
  echo "<td><input name='dir_".$cod."' type='text' size='40' maxlength='40' value='".$dir."'></td>";
  echo "<td><input name='ruc_".$cod."' type='text' size='11' maxlength='11' value='".$ruc."'></td>";
  echo "<td><input name='tipomon_".$cod."' type='text' size='2' maxlength='1' value='".$tipomon."'></td>";
  echo "<td><input name='fono1_".$cod."' type='text' size='15' maxlength='12' value='".$fono1."'></td>";
  echo "<td><input name='fono2_".$cod."' type='text' size='15' maxlength='12' value='".$fono2."'></td></tr>";
  }
  $irow++;
 }
?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><input name='boton' type='submit' value='eliminar'></td>
      <td><input name='boton' type='submit' value='modificar'></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td><td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
<?php
pg_close($coneccion);
