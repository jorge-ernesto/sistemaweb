<?php include("../config.php"); ?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>integrado</title>
<script>
function escogalmao(){
    opener.document.formular.updalmaco.value = document.formular1.escogalmaco.value
	window.opener.enviadatos()
	window.close()
}
</script>
</head>
<body>
ALMACEN DE ORIGEN <hr noshade>
<form name='formular1'>
	  <input name="updalmaco1" type="text" size="20"><input name="boton" type="submit" value="Ok"><br>
        <select name="escogalmaco" size="10">
          <?php
$updalmaco1=strtoupper($updalmaco1);

//$sqlao="select tab_elemento,tab_descripcion from int_tabla_general
//where tab_tabla='ALMA' and tab_elemento!='000000' and tab_car_02='".$ofcentral_almo."' and (tab_elemento like '%".$updalmaco1."%' or tab_descripcion like '%".$updalmaco1."%') order by tab_descripcion";

$sqlao="select ch_almacen,ch_nombre_almacen from inv_ta_almacenes
where ch_clase_almacen='".$ofcentral_almo."' and (ch_almacen like '%".$updalmaco1."%' or ch_nombre_almacen like '%".$updalmaco1."%') order by ch_nombre_almacen";

echo $sqlao;

$xsqlao=pg_exec($coneccion,$sqlao);
$ilimitao=pg_numrows($xsqlao);
while($irowao<$ilimitao) {
	$codalmo=pg_result($xsqlao,$irowao,0);
	$descalmo=pg_result($xsqlao,$irowao,1);
	if($codalmo==$movalmo) {
	  echo "<option value='".$codalmo."' selected>".$descalmo." - ".$codalmo."</option>";
	}else{
	  echo "<option value='".$codalmo."'>".$descalmo." - ".$codalmo."</option>";
	}
	$irowao++;
}
echo $sqlao;
?>
        </select><input type="Button" name="boton" value="Seleccionar" onclick="escogalmao()">
</form>
</body>
</html>
<?php pg_close($coneccion);?>