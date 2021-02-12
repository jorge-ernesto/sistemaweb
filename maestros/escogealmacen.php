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
function brow_alma(k_par){ 
    eval("opener.document."+k_par+".value = document.formular1.escogealmacen.value")
	window.opener.enviadatos() 
	window.close() 
} 
</script> 

</head>

<body onBlur="window.focus()">

ALMACEN <hr noshade>
<form name='formular1'>

<input name='codig' type='text' value='<?php echo $escogealmacen; ?>' size='20'>
<input type="submit" name="boton" value="Ok"> 
<?php
$codig=strtoupper($codig);
$sqlalm="select TAB_ELEMENTO, 
				TAB_DESCRIPCION 
				from INT_TABLA_GENERAL 
				where TAB_TABLA='ALMA' and TAB_ELEMENTO!='000000' 
				and TAB_CAR_02='1' 
				and (TAB_ELEMENTO like '%".$codig."%' or TAB_DESCRIPCION like '%".$codig."%') 
				order by TAB_DESCRIPCION";
$xsqlalm=pg_exec($conector_id,$sqlalm);
$ilimitalm=pg_numrows($xsqlalm);
if($ilimitalm>0) 
	{
	?>
	<br>
	<select name="escogealmacen" size="10">
	<?php
	$irowalm=0 ;
	while($irowalm<$ilimitalm) 
		{
		$rli0=pg_result($xsqlalm,$irowalm,0);
		$rli1=pg_result($xsqlalm,$irowalm,1);
		echo "<option value='".$rli0."'>".$rli0."-".$rli1."</option>";
		$irowalm++;
		}
	?>
	</select>
	<input type="Button" name="boton" value="Seleccionar" onclick="brow_alma('<?php echo $k_variable; ?>')">
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

