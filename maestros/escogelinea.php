<?php
include("../config.php");

?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>

<script> 
function escojlinea(k_par){ 
    eval("opener.document."+k_par+".value = document.formular1.escoglinea.value")
//	formular.linea
	window.opener.enviadatos() 
	window.close() 
} 
</script> 

</head>

<body>
LINEAS <hr noshade>
<form name='formular1'>

<input name='linea1' type='text' value='<?php echo $escoglinea;?>' size='20'>
<input type="submit" name="boton" value="Ok"> <?php
 $linea1=strtoupper($linea1);
//if(strlen($linea1)>0) {

  $sqllin="select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and tab_elemento!='000000' and (tab_elemento like '%".$linea1."%' or tab_descripcion like '%".$linea1."%')";
  $xsqllin=pg_exec($coneccion,$sqllin);
  $ilimitlin=pg_numrows($xsqllin);
  if($ilimitlin>0) {  ?><br>
<select name="escoglinea" size="10">
<?php	$irowlin=0;
while($irowlin<$ilimitlin) {
	$rli0=pg_result($xsqllin,$irowlin,0);
	$rli1=pg_result($xsqllin,$irowlin,1);
	echo "<option value='".$rli0."'>".$rli0."-".$rli1."</option>";
	$irowlin++;
}
?>
</select>
<input type="Button" name="boton" value="Seleccionar" onclick="escojlinea('<?php echo $k_variable; ?>')">
<?php
  }
//}
?>
</form>
</body>
</html>
<?php pg_close($coneccion);?>