<?php
if($accion=="excel"){
 header("Content-Type: application/vnd.ms-excel"); 
 header("Content-Disposition: filename=\"pedido_merca_auto.xls\""); 
 //echo $accion;
}

include("../../functions.php");
include("../store_procedures.php");
require("../../clases/funciones.php");

$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if($filtro=="menos"){
	$and1 = " and a.flg='-A' ";
}
if($filtro=="mas"){
	$and1 = " and a.flg='+A' ";
}
if($filtro=="todos"){
	$and1 = "  ";
}



$rs1 = pg_exec("select a.art_codigo, b.art_descripcion , a.pedido, a.stock
, a.stock_minimo,a.v3meses,a.promed, a.actual , a.mes_1 , a.mes_2 , a.mes_3 
, a.dias_minimo ,a.dias_maximo, a.flg from tmp_cmp_pedido_automatico a,int_articulos b 
where a.art_codigo=b.art_codigo $and1");
pg_close();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Pedido Automatico de Mercaderia</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" >
<table width="723" border="0">
  <tr> 
      <td width="165"><a href="#" onClick="accion.value='excel',form1.submit();">Pasar a Excel 
        &gt;&gt;</a></td>
      <td width="87">&nbsp;</td>
    <td width="223"><div align="center">Pedido Automatico de Mercaderia</div></td>
      <td width="141"><input type="hidden" name="filtro" value="<?php echo $filtro;?>"> 
        <input type="hidden" name="accion" value="<?php echo $accion;?>"> </td>
    <td width="85">&nbsp;</td>
  </tr>
</table>
<br>
<table width="769" height="47" border="1" cellpadding="0" cellspacing="0">
  <tr> 
    <th height="24"><font size="-4" face="Arial, Helvetica, sans-serif">CODIGO</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">DESCRIPCION</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">PEDIDO</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">STOCK</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">DURAC<br>
      STOCK</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">STOCK<br>
      MINIM </font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">V<br>
      3 MESES</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">E<br>
      PROMED </font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">N<br>
      ACTUAL</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">T<br>
      MES-1 </font></th>
    <th width="51"><font size="-4" face="Arial, Helvetica, sans-serif">A<br>
      MES-2 </font></th>
    <th width="39"><font size="-4" face="Arial, Helvetica, sans-serif">S<br>
      MES-3</font></th>
    <th width="44"><font size="-4" face="Arial, Helvetica, sans-serif">DIAS<br>
      MINIM </font></th>
    <th width="36"><font size="-4" face="Arial, Helvetica, sans-serif">DIAS<br>
      MAXIM</font></th>
    <th width="50">#</th>
  </tr>
  <!-- <?php for($i=0;$i<pg_numrows($rs1);$i++){
  $A = pg_fetch_array($rs1,$i); 
  if($A[3]!=0 && $A[6]!=0){
  $dura_stock = $A[3]/$A[6];
  }else{
  $dura_stock = 0;
  }
  print '
  ?> -->
  <tr> 
    <th width="49" height="21"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[0].'</font></th>
    <th width="83"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[1].'</font></th>
    <th width="44"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[2].'</font></th>
    <th width="39"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[3].'</font></th>
    <th width="47"><font size="-4" face="Arial, Helvetica, sans-serif">'.$dura_stock.'</font></th>
    <th width="49"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[4].'</font></th>
    <th width="48"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[5].'</font></th>
    <th width="46"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[6].'</font></th>
    <th width="58"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[7].'</font></th>
    <th width="54"><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[8].'</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[9].'</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[10].'</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[11].'</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[12].'</font></th>
    <th><font size="-4" face="Arial, Helvetica, sans-serif">'.$A[13].'</font></th>
  </tr>
  <!-- <?php ';} ?>-->
</table>
</form>
</body>
</html>
