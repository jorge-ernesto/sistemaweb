<?php include("../config.php"); ?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<html>
<head>
<title>integrado</title>
<script>
function escogprov(){
    opener.document.formular.updprov.value = document.formular1.escogeprov.value
	window.opener.enviadatos()
	window.close()
}
</script>
</head>

<body>
PROVEEDORES <hr noshade>
<form name='formular1'>
<input name="updprov1" type="text" size="20" maxlength="12" value="<?php echo $updprov1;?>">
  <input type="submit" name="boton2" value="Ok">
  <br>
 <select name="escogeprov" size="10">
<!--<option value="">--Seleccione Proveedor--</option>-->
<?php
$updprov1=strtoupper($updprov1);
$sqlprov="select pro_codigo,pro_razsocial from int_proveedores where pro_codigo like '%".$updprov1."%' or pro_razsocial like '%".$updprov1."%'
order by 2";
$xsqlprov=pg_exec($coneccion,$sqlprov);
$ilimitprov=pg_numrows($xsqlprov);
while($irowprov<$ilimitprov) {
	$codprov=pg_result($xsqlprov,$irowprov,0);
	$descprov=pg_result($xsqlprov,$irowprov,1);
	if($codprov==$movprov) {
	  echo "<option value='".$codprov."' selected>".$descprov." - ".$codprov."</option>";
	}else{
	  echo "<option value='".$codprov."'>".$codprov." - ".$descprov."</option>";
	}
	$irowprov++;
}
?>
        </select>
  <input type="Button" name="boton" value="Seleccionar" onClick="escogprov()">
</form>
</body>
</html>
<?php pg_close($coneccion); ?>