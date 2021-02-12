<?php
//include("../config.php");
//include("../inc_top.php");

//include("../valida_sess.php");
// incluye config.php y inc_top_xxx 
// protege para que no accedan a otro menu por usuario
// pero es necesario que haga coneccion de nuevo
include("../menu_princ.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");


 if($txtcampo=="A"){ $ch=" checked"; } elseif($txtcampo=="B"){ $ch1=" checked"; } elseif($txtcampo=="C"){ $ch2=" checked"; } else { $ch=" checked"; }
$txtxbusqueda=strtoupper($txtxbusqueda);

if($boton=="buscar" or strlen(trim($txtxbusqueda))>0) {
  if($txtcampo=="A") {
    $addsql=" where cli_codigo='".$txtxbusqueda."' ";
  } elseif($txtcampo=="B") {
    $addsql=" where cli_razsocial like '".$txtxbusqueda."%' ";
  } elseif($txtcampo=="C") {
    $addsql=" where cli_razsocial like '%".$txtxbusqueda."%' ";
  } else {  $addsql=" ";  }
}else{   $bddsql=" limit 20";  }




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
        C�igo<input type="radio" name="txtcampo" value="B" <?php echo $ch1;?>>
        Raz&oacute;n Social(iniciales)<input type="radio" name="txtcampo" value="C" <?php echo $ch2;?>>Raz&oacute;n Social(contenido)</td>
    </tr></table>
  <br>
  
  <p>MANTENIMIENTO DE CLIENTES</p>
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
      <td><input name='newcod' type='text' size='10' maxlength='6'></td>
      <td><input name='newrazlarga' type='text' size='50' maxlength='40'></td>
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
      <td><input name='boton' type='submit' value='adicionar' disabled="true"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php
//  $_POST['varx']=20;
$sql="select cli_codigo,cli_razsocial,cli_rsocialbreve,cli_direccion,
cli_ruc,cli_moneda,cli_telefono1,cli_telefono2
 from int_clientes ".$addsql." order by 1 ".$bddsql." ";
/*
 $sql="select cli_codigo,cli_razsocial,cli_rsocialbreve,cli_grupo,cli_direccion,
cli_ruc,cli_moneda,cli_telefono1,cli_telefono2
 from int_clientes ".$addsql." order by 1 ".$bddsql." ";*/
// echo $sql;
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
  echo "<tr><td><input type='checkbox' name='id_".$cod."' value='".$cod."'></td>";
  echo "<td><input name='cod_".$cod."' type='text' size='10' maxlength='6' value='".$cod."' readonly></td>";
// echo "<td><input name='elem_".$cod."' type='text' size='7' maxlength='6' value='".$elem."' readonly></td>";
  echo "<td><input name='razlarga_".$cod."' type='text' size='50' maxlength='40' value='".$razslarga."'></td>";
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
      <td><input name='boton' type='submit' value='eliminar' disabled="true"></td>
      <td><input name='boton' type='submit' value='modificar' disabled="true"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
<?php
pg_close($coneccion);
