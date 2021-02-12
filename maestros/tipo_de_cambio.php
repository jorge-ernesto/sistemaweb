<?php
include("../config.php");
//include("../inventarios/inc_top.php");
include("../menu_princ.php");
include("../functions.php");
include("functions.php");

$coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=postgres");

$v_grifo=true;

if ( is_null($almacen) or trim($almacen)=="")
        {
        $almacen="001";
        }
        
if ( trim($almacen)=="001")
	{
	$v_grifo=false;
	}



$sql="select tca_moneda , tca_fecha  , tca_compra_libre , tca_venta_libre , tca_compra_banco
 , tca_venta_banco , tca_compra_oficial , tca_venta_oficial from int_tipo_cambio order by 1,2 desc";


switch ($boton) {
   case Agregar:
   if(strlen($mone)>0&&strlen($fecha)>0&&strlen($comlib)>0&&strlen($venlib)>0&&strlen($comba)>0&&strlen($venba)>0&&strlen($comofi)>0&&strlen($venofi)>0){
	
	$xsqlbusc=pg_exec($coneccion,"select tca_moneda , tca_fecha  , tca_compra_libre , tca_venta_libre , tca_compra_banco
			, tca_venta_banco , tca_compra_oficial , tca_venta_oficial from int_tipo_cambio where tca_moneda='".$mone."'");
	if(pg_numrows($xsqlbusc)==0){
		$sqlins="insert into int_tipo_cambio(tca_moneda , tca_fecha  , tca_compra_libre , tca_venta_libre , tca_compra_banco
			, tca_venta_banco , tca_compra_oficial , tca_venta_oficial)
			values('".$mone."','".$fecha."','".$comlib."','".$venlib."','".$comba."','".$venba."','".$comofi."','".$venofi."')";
//				echo "esto es el insert:".$sqlins;
			$xsqlins=pg_exec($coneccion,$sqlins);
	
	}else{  ?>
			<script>alert(" El Codigo ya existe !!! ")</script> <?php }
	   
   }else{  ?>
			<script>alert(" Debe ingresar valores")</script>
	<?php		}
   break;
   
   
  case Modificar:
   $xsqlm=pg_exec($coneccion,$sql);
		$ilimitm=pg_numrows($xsqlm);
		while($irowm<$ilimitm) {
			$am0=pg_result($xsqlm,$irowm,0);
			$idm[$am0]=$am0;
	//		echo "idm".$idm[$am0]."<br>idp[$am0]:"; echo $idp[$am0]."<br>";
			
			if($idm[$am0]==$idp[$am0]) {
				//tca_fecha='".$fecha2[$am0]."',
				$sqlupd="update int_tipo_cambio set
						
						
						tca_compra_libre='".$cprlib2[$am0]."',
						tca_venta_libre='".$venlib2[$am0]."',
						tca_compra_banco='".$comban2[$am0]."',
						tca_venta_banco='".$venban2[$am0]."',
						tca_compra_oficial='".$conofi2[$am0]."',
						tca_venta_oficial='".$venofi2[$am0]."'
						where tca_moneda='".$idm[$am0]."' ";


				//echo $sqlupd;
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
				$sqlupd="delete from int_tipo_cambio where tca_moneda='".$idm[$am0]."' ";
				$xsqlupd=pg_exec($coneccion,$sqlupd);
			}
			$irowm++;
		}
   break;
}
?>

<html>
<head>
<title>sistemaweb</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
TIPOS DE CAMBIO
<hr noshade><br>
<form action='' method='post'>
<table border="1" cellspacing="0" cellpadding="0">
  <tr>
    <th>&nbsp;</th>
	<th width="82">MON</th>
    <th width="68">FECHA</th>
	<th width="99">COMPRA LIBRE</th>
	<th width="82">VENTA LIBRE</th>
    <th width="103">COMPRA BANCO</th>
	<th width="86">VENTA BANCO</th>
	<th width="107">COMPRA OFICIAL</th>
	<th width="94">VENTA OFICIAL</th>
	<th width="8"></th>
 
</tr>
<tr>

<th>&nbsp;</th>
<td align='center'><input type='text' name='mone' size='5' maxlength='5'>
      </td>
<td align='right'><input type='text' name='fecha' size='20' maxlength='20'></td>
<td align='right'><input type='text' name='comlib' size='10' maxlength='10'></td>
<td align='right'><input type='text' name='venlib' size='10' maxlength='10'></td>
<td align='right'><input type='text' name='comba' size='10' maxlength='10'></td>
<td align='right'><input type='text' name='venba' size='10' maxlength='10'></td>
<td align='right'><input type='text' name='comofi' size='10' maxlength='10'></td>
<td align='right' ><input type='text' name='venofi' size='10' maxlength='10'></td>
<!--<td><input type='text' name='newclase' size='5' maxlength='1'></td>-->
<td align='right'></td>

      <td width="8"><input type='submit' name='boton' size='15' value='Agregar'></td>
    </tr>
<tr>
    <td>&nbsp;</td>
      <td><input name="boton" type="submit"  value="Modificar"></td>
    <td>&nbsp;</td>
    <td><input name="boton" type="submit" value="Eliminar"></td><td>&nbsp;</td><td>&nbsp;</td>
      <td>&nbsp;</td>
 </tr>

  <tr>

    <th>&nbsp;</th>
    <th>Nº</th>
    <th>FECHA</th>
	<th>COMPRA LIBRE</th>
	<th>VENTA LIBRE</th>
    <th>COMPRA BANCO</th>
	<th>VENTA BANCO</th>
	<th>COMPRA OFICIAL</th>
	<th>VENTA OFICIAL</th>

  </tr>
<?php

$xsql=pg_exec($coneccion,$sql);
// $xsql=pg_exec($sql);
$irow=0;
$ilimit=pg_numrows($xsql);
while($irow<$ilimit) {

	$a0=pg_result($xsql,$irow,0);
	$fecha2[$a0]=trim(pg_result($xsql,$irow,1));
	$cprlib2[$a0]=trim(pg_result($xsql,$irow,2));
	$venlib2[$a0]=trim(pg_result($xsql,$irow,3));
	$comban2[$a0]=trim(pg_result($xsql,$irow,4));
	$venban2[$a0]=trim(pg_result($xsql,$irow,5));
	$conofi2[$a0]=trim(pg_result($xsql,$irow,6));
	$venofi2[$a0]=trim(pg_result($xsql,$irow,7));

	
echo "	
	<tr>";

echo"<td><input type='checkbox' name='idp[$a0]' value='".$a0."'></td>";
echo"<td align='center'><input type='text' size='5' name='a0' value='".$a0."' maxlength='3'></td>";
echo"<td align0='right'><input type='text' size='20'  name='fecha2[$a0]' value='".$fecha2[$a0]."' maxlength='25'></td>";

echo"<td align='right'><input type='text' size='10'  name='cprlib2[$a0]' value='".$cprlib2[$a0]."' maxlength='15'></td>";

echo"<td align='right'><input type='text' size='10' name='venlib2[$a0]' value='".$venlib2[$a0]."' maxlength='15'></td>";
echo"<td align='right'><input type='text' size='10' name='comban2[$a0]' value='".$comban2[$a0]."' maxlength='15'></td>";
echo"<td align='right'><input type='text' size='10' name='venban2[$a0]' value='".$venban2[$a0]."' maxlength='15'></td>";
echo"<td align='right'><input type='text' size='10' name='conofi2[$a0]' value='".$conofi2[$a0]."' maxlength='15'></td>";
echo"<td align='right'><input type='text' size='10' name='venofi2[$a0]' value='".$venofi2[$a0]."' maxlength='15'></td>";

echo"	<td align='right'></td>";
$irow++; }?>

 <tr>
    <td>&nbsp;</td>
    <td><input name="boton" type="submit"  value="Modificar"></td>
    <td>&nbsp;</td>
    
	
	
	
	<td><input name="boton" type="submit" value="Eliminar" ></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
 </tr>
</table>
</form>
</body>
</html>
<?php include("../close_connect.php"); ?>

