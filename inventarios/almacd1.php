<?php include("../config.php"); ?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>integrado</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
ALMACEN DESTINO <hr noshade>
<form name='formular1'>
  <select name="updalmacd" size="10">
    <?php
$sqlad="select tab_elemento,tab_descripcion from int_tabla_general 
where tab_tabla='ALMA' order by tab_descripcion";

$xsqlad=pg_exec($coneccion,$sqlad);
$ilimitad=pg_numrows($xsqlad);
while($irowad<$ilimitad) {
	$codalmd=pg_result($xsqlad,$irowad,0);
	$descalmd=pg_result($xsqlad,$irowad,1);
	if($codalmd==$movalmd) {
	  echo "<option value='".$codalmd."' selected>".$codalmd." - ".$descalmd."</option>";
	}else{
	  echo "<option value='".$codalmd."'>".$codalmd." - ".$descalmd."</option>";
	}
	$irowad++;
}
?>
  </select><input type="Button" name="boton" value="Seleccionar" onclick="escojalmad()">
</form>
</body>
</html>
<?php pg_close($coneccion); ?>