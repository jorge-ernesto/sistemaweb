<?php
include("../config.php");
include("inc_top.php");
include("../functions.php");
if($txtcampo=="A"){ $ch=" checked"; } elseif($txtcampo=="B"){ $ch1=" checked"; } elseif($txtcampo=="C"){ $ch2=" checked"; } else { $ch=" checked"; }
$txtxbusqueda=strtoupper($txtxbusqueda);
if($boton=="buscar" or strlen(trim($txtxbusqueda))>0) {
  if($txtcampo=="A") {
    $addsql=" where art_codigo='".$txtxbusqueda."' ";
  } elseif($txtcampo=="B") {
    $addsql=" where art_descripcion like '".$txtxbusqueda."%' ";
  }  elseif($txtcampo=="C") {
    $addsql=" where art_descripcion like '%".$txtxbusqueda."%' ";
  }  else {
    $addsql=" ";
  }	//$bddsql=" limit $tamPag offset $limitInf ";
 } else{   //$bddsql=" limit $tamPag offset $limitInf ";
	$fbuscar="A";
 }

$sql="select art_codigo,art_descripcion,art_costoactual,art_stockactual,art_linea
 from int_articulos ".$addsql." order by 1 ";
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
if($ilimit>0) {
	$numeroRegistros=$ilimit;
}



if($boton=="adicionar") {/*
  $sqlai="select art_codigo from int_articulos where art_codigo='".$newcod."' ";
//  echo $sqlai;
  $xsqlai=pg_exec($coneccion,$sqlai);
  $ilimitai=pg_numrows($xsqlai);
  if($ilimitai==0) {
  $sqli="insert into int_articulos(art_codigo,art_descripcion,art_costoactual,art_stockactual,art_linea)
   values('".$newcod."','".$newdesc."',".$newprecio.",".$newstock.",'".$newlinea."')";
  $xsqli=pg_exec($coneccion,$sqli);
  } else {
//     echo "el codigo del item ya existe !!!";
?>
<script>11111111111111111111111111111111111111111111111111111111111111111
	alert(" El código de artículo <?php echo $newcod; ?>  ya existe !!!")
</script>
<?php
  }  */
?>
<script>
location.href='additem.php';
</script>
<?php  
}

if($boton=="eliminar") {
  
  $xsqlbuscamov=pg_exec($coneccion,"select art_codigo from inv_movialma where art_codigo='".$id."' "); 
  if(pg_numrows($xsqlbuscamov)==0){
    $sql1="delete from int_articulos where art_codigo='".$id."'";
    $xsql1=pg_exec($coneccion,$sql1);
	$xsqldelprecart=pg_exec($coneccion,"delete from fac_lista_precios where art_codigo='".$id."' ");

  }else{  ?>
<script>
alert(" Existen movimientos con este codigo de artículo -  <?php echo $id; ?> !!! ")
</script>  
<?php }
}

if($boton=="modificar") {
?>
<script>
location.href='upditem.php?idart=<?php echo $id; ?>&fupd=Z';
</script>
<?php  
/* $sql2="select art_codigo,art_descripcion,art_costoactual,art_stockactual,art_linea
 from int_articulos ".$addsql." order by 1 ";
 $xsql2=pg_exec($coneccion,$sql2);
 $ilimit2=pg_numrows($xsql2);
 while($irow2<$ilimit2) {
	$cod=pg_result($xsql2,$irow2,0);
//	$elem=pg_result($xsql2,$irow2,1);
	$xelem="id_".$cod;
	$upddesc="desc_".$cod;
	$updprecio="precio_".$cod;
	$updstock="stock_".$cod;
	$updlinea="linea_".$cod;
	if($$xelem==$cod) {
	  if(strlen(trim($$updlinea))>3) { 
	  	$cad2= strlen(trim($$updlinea));
		$updlin=substr($$updlinea,$cad2-3,$cad2);
	  } elseif(strlen(trim($$updlinea))<=3) {  $updlin=trim($$updlinea); }

    $sql1="update int_articulos set 
	 art_descripcion='".$$upddesc."',art_costoactual=".$$updprecio.",
	 art_stockactual=".$$updstock.",art_linea='".$updlin."'
	 where art_codigo='".$$xelem."' ";
//	 echo $sql1;
 	$xsql1=pg_exec($coneccion,$sql1);
	}
  $irow2++;
 } */
}


?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>MAESTRO DE ITEMS</title>
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
        Descripci&oacute;n(iniciales)<input type="radio" name="txtcampo" value="C" <?php echo $ch2;?>>Descripci&oacute;n(contenido)
        </td>
    </tr></table>
  <br>
  
  <p>MAESTRO DE ITEMS</p>
<?php include("../../pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";
?>

<input type="hidden" name="varx" value="<?php echo $varx;?>">
  <table border='1' cellpadding='0' cellspacing='0'>
    <tr> 
      <th>&nbsp;</th>
      <th>CODIGO</th>
      <th>DESCRIPCION</th>
      <th>PRECIO</th>
      <th>STOCK</th>
      <th>LINEA</th>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><input name='boton' type='submit' value='adicionar'></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
	 <tr> 
      <td>&nbsp;</td>
      <td colspan="5" align="center">&nbsp;</td>
    </tr>
<?php	
//  $_POST['varx']=20;
/*$sql="select tab_tabla,tab_elemento,tab_descripcion,tab_desc_breve,tab_num_01
 from int_tabla_general where tab_tabla='".$_POST['varx']."' ";*/
$sql="select art_codigo,art_descripcion,art_costoactual,art_stockactual,art_linea
 from int_articulos ".$addsql." order by 1  ".$bddsql." ";
// echo $sql;
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);

while($irow<$ilimit) {
	$cod=pg_result($xsql,$irow,0);
	$desc=pg_result($xsql,$irow,1);
	$precio=pg_result($xsql,$irow,2);
	$stock=pg_result($xsql,$irow,3);
	$linea=pg_result($xsql,$irow,4);
?><tr bgcolor="#CCCC99" onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#CCCC99'"o"];">
<?php
//  echo "<td><input type='checkbox' name='id_".$cod."' value='".$cod."'></td>";
  echo "<td><input type='radio' name='id' value='".$cod."'></td>";
  echo "<td>&nbsp;".$cod."</td>";
  echo "<td>&nbsp;".$desc."</td>";
  echo "<td align='right'>&nbsp;".$precio."</td>";
  echo "<td align='right'>&nbsp;".$stock."</td>";
  $nrocaract=6; $cadena=$linea;
  completaceros($nrocaract,$cadena);
  $linea=$cadena;
$sqlopt="select tab_tabla,tab_elemento,tab_descripcion,tab_desc_breve,tab_num_01
 from int_tabla_general where tab_tabla='20' and tab_elemento='".$linea."' ";
  $xsqlopt=pg_exec($coneccion,$sqlopt);
  $ilimitopt=pg_numrows($xsqlopt);  
  if($ilimitopt>0) { $x0=pg_result($xsqlopt,0,1);  $x1=pg_result($xsqlopt,0,2);  }
  echo "<td>".$x0." - ".$x1."</td></tr>";
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
    </tr>
  </table>
</form>
</body>
</html>
<?php
pg_close($coneccion);
