<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

$coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=postgres");

$sql="select PROMO.ART_CODIGO, INT_ARTICULOS.ART_DESCRIPCION, fac_lista_precios.pre_precio_act1 , PROMO.SPECIAL1, PROMO.SPECIAL2, PROMO.SPECIAL3, PROMO.SPECIAL4, PROMO.SPECIAL5, PROMO.SPECIAL6, PROMO.ACTIV_DATE, PROMO.DEACT_DATE
		from PROMO , INT_ARTICULOS , FAC_LISTA_PRECIOS
		WHERE PROMO.ART_CODIGO = INT_ARTICULOS.ART_CODIGO 
		AND  PROMO.ART_CODIGO = FAC_LISTA_PRECIOS.ART_CODIGO order by 1 ";
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);
 
if($ilimit>0) 
	{
	$numeroRegistros=$ilimit;
	}

if($boton=="Adicionar" or $boton=="Ins") 
	{
	?>
	<script>
	location.href='maes_promo_1.php';
	</script>
	<?php  
	}

if($boton=="Eliminar" or $boton=="Del" )
	{
	$sql1="delete from PROMO where ART_CODIGO='".$id."'";
   	$xsql1=pg_exec($coneccion,$sql1);
	}

if($boton=="Modificar" or $boton=="Mod") 
	{
	?>
	<script>
	location.href='maes_promo_2.php?idart=<?php echo $id; ?>' ;
	</script>
	<?php  
	}
	?>




<html><link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<head>
<h2 style="color:#336699;" align="center">MAESTRO DE PROMOCIONES ESPECIALES</td>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<script language="javascript"> 
var miPopup
function enviadatos(dato){
	document.forma.varx.value=dato
	document.forma.submit()
}
</script> 
</head>
<body>
<form  name="forma" action="" method="post">
<br>

<?php
include("/sistemaweb/maestros/pagina.php");
$bddsql=" limit $tamPag offset $limitInf ";
?>

<input type="hidden" name="varx" value="<?php echo $ord; ?>"> 

<table border="0" cellpadding="0">
	<tr>
		<th class="grid_cabecera" width="20">&nbsp;</th>
		<th class="grid_cabecera" width="60">CODIGO</th>
		<th class="grid_cabecera" width="130">DESCRIPCION</th>
		<th class="grid_cabecera" width="40">LP</th>
		<th class="grid_cabecera" width="60">PRECIO</th>
		<th class="grid_cabecera" width="40">SKU1</th>
		<th class="grid_cabecera" width="40">SKU2</th>
		<th class="grid_cabecera" width="40">SKU3</th>
		<th class="grid_cabecera" width="40">SKU4</th>
		<th class="grid_cabecera" width="40">SKU5</th>
		<th class="grid_cabecera" width="40">SKU6</th>
		<th class="grid_cabecera" width="90">FECHA ACTIVA</th>
		<th class="grid_cabecera" width="90">FECHA DESACT</th>
	</tr>
	<tr>
		<th class="grid_cabecera" width="20">&nbsp;</th>
		<th class="grid_cabecera" width="60">CODIGO</th>
		<th class="grid_cabecera" width="130">DESCRIPCION</th>
		<th class="grid_cabecera" width="40">LP</th>
		<th class="grid_cabecera" width="60">PRECIO</th>
		<th class="grid_cabecera" width="40">SKU1</th>
		<th class="grid_cabecera" width="40">SKU2</th>
		<th class="grid_cabecera" width="40">SKU3</th>
		<th class="grid_cabecera" width="40">SKU4</th>
		<th class="grid_cabecera" width="40">SKU5</th>
		<th class="grid_cabecera" width="40">SKU6</th>
		<th class="grid_cabecera" width="90">FECHA ACTIVA</th>
		<th class="grid_cabecera" width="90">FECHA DESACT</th>
	</tr>
    <tr> 
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>
      	<input name='boton' type='submit' value='Ins' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';"> 
        <input name='boton' type='submit' value='Mod' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';"> 
        <input name='boton' type='submit' value='Del' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';"> 
      </td>
    </tr>

<?php
$sql="select PROMO.ART_CODIGO, INT_ARTICULOS.ART_DESCRIPCION,fac_lista_precios.pre_lista_precio, fac_lista_precios.pre_precio_act1 , PROMO.SPECIAL1, PROMO.SPECIAL2, PROMO.SPECIAL3, PROMO.SPECIAL4, PROMO.SPECIAL5, PROMO.SPECIAL6, PROMO.ACTIV_DATE, PROMO.DEACT_DATE
		from PROMO , INT_ARTICULOS , FAC_LISTA_PRECIOS
		WHERE PROMO.ART_CODIGO = INT_ARTICULOS.ART_CODIGO 
		AND  PROMO.ART_CODIGO = FAC_LISTA_PRECIOS.ART_CODIGO order by 1 " . $bddsql;
$xsql=pg_exec($coneccion,$sql);
$ilimit=pg_numrows($xsql);


$irow=0;
while($irow<$ilimit) {
	$m_articulo=pg_result($xsql,$irow,0);
	$m_descart=pg_result($xsql,$irow,1);
	$lista=pg_result($xsql,$irow,2);
	$precio=pg_result($xsql,$irow,3);
	$sku1=pg_result($xsql,$irow,4);
	$sku2=pg_result($xsql,$irow,5);
	$sku3=pg_result($xsql,$irow,6);
	$sku4=pg_result($xsql,$irow,7);
	$sku5=pg_result($xsql,$irow,8);
	$sku6=pg_result($xsql,$irow,9);
	$feca=pg_result($xsql,$irow,10);
	$fecd=pg_result($xsql,$irow,11);
	?>

	<tr onClick="enviadatos('<?php echo $m_articulo; ?>') " onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor=''"o"];"> 

<?php
	if($m_articulo==$varx) {
		echo "<td><input type='radio' checked name='id' value='".$m_articulo."'></td>";
	}
	else {
		echo "<td><input type='radio' name='id' value='".$m_articulo."'></td>";
	}
	
	echo "<td>&nbsp;".$m_articulo."</td>";
	echo "<td>&nbsp;".$m_descart."</td>";
	echo "<td>&nbsp;".$lista."</td>";
	echo "<td>&nbsp;".$precio."</td>";
	echo "<td>&nbsp;".$sku1."</td>";
	echo "<td>&nbsp;".$sku2."</td>";
	echo "<td>&nbsp;".$sku3."</td>";
	echo "<td>&nbsp;".$sku4."</td>";
	echo "<td>&nbsp;".$sku5."</td>";
	echo "<td>&nbsp;".$sku6."</td>";
	echo "<td>&nbsp;".$feca."</td>";	
	echo "<td>&nbsp;".$fecd."</td>";
	//$nrocaract=6; 
	//$cadena=$linea;
	//completaceros($nrocaract,$cadena);

	$irow++;
	}

?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td> 
        <!--- <input name='boton' type='submit' value='Adicionar' >
<input name='boton' type='submit' value='Modificar'>
<input name='boton' type='submit' value='Eliminar'> --->
        <input name='boton' type='submit' value='Ins' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';"> 
        <input name='boton' type='submit' value='Mod' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';"> 
        <input name='boton' type='submit' value='Del' onMouseOver="this.style.backgroundColor='#FFFFCC';this.style.cursor='hand';" onMouseOut="this.style.backgroundColor='#c0c0c0';"> 
      </td>
    </tr>
  </table>
</form>
</body>
</html>
<?php
pg_close($coneccion);
