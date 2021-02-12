<?php include("../config.php"); ?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>integrado</title>
<script>
function escogalmac(){
    opener.document.formular.txtalma.value = document.formular1.escogalmacen.value
	window.opener.enviadatos()
	window.close()
}
</script>
</head>
<body>
ALMACEN <hr noshade><br>
<form name='formular1'>
<input name="escogalmacx" type="text" size="20"><input name="boton" type="submit" value="Ok"><br>
<select name="escogalmacen" size="10">
<?php
$escogalmacx=strtoupper($escogalmacx);
$sqlao="select ch_almacen,ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1'
and (ch_almacen like '%".$escogalmacx."%' or ch_nombre_almacen like '%".$escogalmacx."%') order by ch_nombre_almacen";

$xsqlao=pg_exec($coneccion,$sqlao);
$ilimitao=pg_numrows($xsqlao);
while($irowao<$ilimitao) {
	$codalmo=pg_result($xsqlao,$irowao,0);
	$descalmo=pg_result($xsqlao,$irowao,1);
	  echo "<option value='".$codalmo."'>".$codalmo." - ".$descalmo."</option>";
	$irowao++;
}
?>
</select>
<input type="Button" name="boton" value="Seleccionar" onclick="escogalmac()">
</form>
</body>
</html>
<?php pg_close($coneccion);?>
