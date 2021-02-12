<?php
include("../config.php");
?>

<html><link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<head>
<title>integrado</title>
<script>
function escogalmac(){
	opener.document.formular.ubicac.value = document.formular1.escogalmacen.value;
	window.opener.enviadatos();
	window.close();
}
</script>
</head>
<body>
UBICACIONES
<hr noshade><br>
<form name='formular1'>
	<input name="escogalmacx" type="text" size="20" /><input name="boton" type="submit" value="Ok" /><br>
	<select name="escogalmacen" size="10">
	<?php
	$escogalmacx = strtoupper($escogalmacx);

	$sqlao = "SELECT 
			cod_ubicac,
			desc_ubicac 
		FROM 
			inv_ta_ubicacion 
		WHERE 
			cod_almacen='".$ch_almacen."'
			AND flg_ubicac LIKE '%$in_process%' 
			AND (cod_ubicac like '%".$escogalmacx."%' or desc_ubicac like '%".$escogalmacx."%') 
		ORDER BY 
			desc_ubicac";
	//echo $sqlao;
	$xsqlao   = pg_exec($coneccion,$sqlao);
	$ilimitao = pg_numrows($xsqlao);

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

