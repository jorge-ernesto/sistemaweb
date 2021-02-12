<?php include("../config.php"); ?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>integrado</title>
<script> 
function escogalmad(){ 
    opener.document.formular.updalmacd.value = document.formular1.escogalmacd.value
	window.opener.enviadatos() 
	window.close() 
} 
</script> 
</head>
<body>
ALMACEN DESTINO 
<hr noshade>
<form name='formular1'>
	  <input name="updalmacd1" type="text" size="20"><input name="boton" type="submit" value="Ok"><br>
        <select name="escogalmacd" size="10">
          <?php
$updalmacd1=strtoupper($updalmacd1);
/*$sqlad="select tab_elemento,tab_descripcion from int_tabla_general 
where tab_tabla='ALMA' and tab_elemento!='000000' and tab_car_02='3' and (tab_elemento like '%".$updalmacd1."%' or tab_descripcion like '%".$updalmacd1."%') order by tab_descripcion";*/
$sqlad="select tab_elemento,tab_descripcion from int_tabla_general 
where tab_tabla='ALMA' and tab_elemento!='000000' and tab_car_02='".$ofcentral_almd."' 
and (tab_elemento like '%".$updalmacd1."%' or tab_descripcion like '%".$updalmacd1."%') order by tab_descripcion";
$xsqlad=pg_exec($coneccion,$sqlad);
$ilimitad=pg_numrows($xsqlad);
while($irowad<$ilimitad) {
	$codalmd=pg_result($xsqlad,$irowad,0);
	$descalmd=pg_result($xsqlad,$irowad,1);
	if($codalmd==$movalmd) {
	  echo "<option value='".$codalmd."' selected>".$descalmd." - ".$codalmd."</option>";
	}else{
	  echo "<option value='".$codalmd."'>".$descalmd." - ".$codalmd."</option>";
	}
	$irowad++;
}
?>
        </select><input type="Button" name="boton" value="Seleccionar" onclick="escogalmad()">
</form>
</body>
</html>
<?php pg_close($coneccion);?>