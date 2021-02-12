<?php

include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
require("/sistemaweb/clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();
// conectar con la base de datos
$conector_id = $funcion->conectar("","","","","");

if(!empty($_POST["keyword"])) {

$sql = "SELECT * FROM int_articulos WHERE trim(art_descripcion) LIKE '$keyword%' ORDER BY art_descripcion;";

if ($sqlca->query($sql) < 0)
    return false;

for ($i = 0; $i < $sqlca->numrows(); $i++) {
	$a		= $sqlca->fetchRow();
	$resultset[]	= $a;
}

?>

<ul id="productos-list">

	<?php foreach($resultset as $country) { ?>
		<li onClick="SelectProduct('<?php echo $country["art_codigo"]; ?>', '<?php echo $country["art_descripcion"]; ?>');"><?php echo $country["art_descripcion"]; ?></li>
	<?php } ?>

</ul>

<?php

}

