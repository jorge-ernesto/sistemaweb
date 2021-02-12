<?php

//get the q parameter from URL
$fecha = $_GET['fecha'];

include("../valida_sess.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
?>
<select name="turno">
<?php

	$sql = "SELECT
			ch_posturno 
		FROM
			pos_aprosys 
		WHERE
			da_fecha = to_date('$fecha','DD/MM/YYYY')
		ORDER BY
			ch_posturno;";

if ($sqlca->query($sql) < 0) 
	return false;	

$result = Array();
$a = $sqlca->fetchRow();

for ($i = 0; $i < $a[0]-1; $i++) {
	$result[$i+1] = $i+1;
?>
<option value="<?php echo $result[$i+1]; ?>">
	<?php echo $result[$i+1]; ?>
</option>
<?php } ?>
</select>
