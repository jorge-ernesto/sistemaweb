<?php
include("../config.php");

?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>/css/sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>

<script> 
function brow_arti(k_par){ 
    eval("opener.document."+k_par+".value = document.formular1.escogearticulo.value")
//	formular.v_art_codigo
	window.opener.enviadatos() 
	window.close() 
} 
</script> 

</head>

<body>
ARTICULOS <hr noshade>

<form name='formular1'>
<input name='codig' type='text' value='<?php echo $escogearticulo; ?>' size='20'>
<input type="submit" name="boton" value="Ok"> 
<?php
$codig=strtoupper($codig);
$sqlart="select art_codigo, art_descripcion from int_articulos where (art_codigo like '%".$codig."%' or art_descripcion like '%".$codig."%') ";
$xsqlart=pg_exec($coneccion,$sqlart);
$ilimitart=pg_numrows($xsqlart);
if($ilimitart>0) 
	{
	?>
	<br>
	<select name="escogearticulo" size="20">
	<?php
	$irowart=0 ;
	while($irowart<$ilimitart) 
		{
		$rli0=pg_result($xsqlart,$irowart,0);
		$rli1=pg_result($xsqlart,$irowart,1);
		echo "<option value='".$rli0."'>".$rli0."-".$rli1."</option>";
		$irowart++;
		}
	?>
	</select>
	<input type="Button" name="boton" value="Seleccionar" onclick="brow_arti('<?php echo $k_variable; ?>')">
	<?php
	}
?>
</form>
</body>
</html>
<?php pg_close($coneccion);?>