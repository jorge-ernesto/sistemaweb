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

<script language="javascript">

function escoje(tabla,k_par){
	switch (tabla) {
	case 'MONE': 
    	eval("opener.document."+k_par+".value = document.formular1.escoge.value;")
		//formular.m_moneda
		break;
	case '05': 
    	eval("opener.document."+k_par+".value = document.formular1.escoge.value.substr(4,2);")
		break;
	case '96': 
    	eval("opener.document."+k_par+".value = document.formular1.escoge.value.substr(4,2);")
		break;
	default: 
    	eval("opener.document."+k_par+".value = document.formular1.escoge.value;")
		break;
	}
	window.opener.enviadatos();
	window.close();
} 
</script> 

</head>

<body onBlur="window.focus()">

LINEAS <hr noshade>
<form name='formular1'>


<input name='linea1' type='text' value='<?php echo $escoglinea;?>' size='20'>
<input type="submit" name="boton" value="Ok"> 
<?php
 $linea1=strtoupper($linea1);

//if(strlen($linea1)>0) {

  $sqllin="select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='". $m_tabla."' and tab_elemento!='000000' and (tab_elemento like '%".$linea1."%' or tab_descripcion like '%".$linea1."%')";
  
  $xsqllin=pg_exec($conector_id,$sqllin);
  $ilimitlin=pg_numrows($xsqllin);
  if($ilimitlin>0) {  ?><br>
<select name="escoge" size="10">
<?php	$irowlin=0;
while($irowlin<$ilimitlin) {
	$rli0=pg_result($xsqllin,$irowlin,0);
	$rli1=pg_result($xsqllin,$irowlin,1);
	echo "<option value='".$rli0."'>".$rli0."-".$rli1."</option>";
	$irowlin++;
}
?>
</select>
<input type="Button" name="boton" value="Seleccionar" onclick="escoje('<?php echo $m_tabla; ?>','<?php echo $k_variable; ?>')" >
<?php
  }
//}
?>
</form>
</body>
</html>
<?php
 
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();


