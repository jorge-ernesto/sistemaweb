<?php
require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

// carga de nuevo la clase con el control de errores
// $clase_error->error();


?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
<head>
<title>sistemaweb</title>

<script>
function brow_prov(k_par){
    eval("opener.document."+k_par+".value = document.formular1.escogeproveedor.value")
	// formular.m_proveedor
	window.opener.enviadatos()
	window.close()
}
</script>

</head>

<body>
CLIENTES <hr noshade>
<form name='formular1'>

<input name='codig' type='text' value='<?php echo $escogeproveedor; ?>' size='20'>
<input type="submit" name="boton" value="Ok">
<?php
$codig=strtoupper($codig);
$sqlpro="select trim(cli_codigo), cli_razsocial from int_clientes where (cli_codigo like '%".$codig."%' or cli_razsocial like '%".$codig."%') ";
$xsqlpro=pg_exec($conector_id,$sqlpro);
$ilimitpro=pg_numrows($xsqlpro);
if($ilimitpro>0)
	{
	?>
	<br>
	<select name="escogeproveedor" size="10">
	<?php
	$irowpro=0 ;
	while($irowpro<$ilimitpro)
		{
		$rli0=pg_result($xsqlpro,$irowpro,0);
		$rli1=pg_result($xsqlpro,$irowpro,1);
		echo "<option value='".$rli0."'>".$rli0."-".$rli1."</option>";
		$irowpro++;
		}
	?>
	</select>
	<input type="Button" name="boton" value="Seleccionar" onclick="brow_prov('<?php echo $k_variable; ?>')">
	<?php
	}
?>
</form>
</body>
</html>
<?php 
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();

