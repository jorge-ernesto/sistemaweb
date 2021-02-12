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

$nuproducto = trim($_REQUEST["nuproducto"]);

if(!empty($_REQUEST["keyword"])) {

$sql = "SELECT * FROM int_clientes WHERE trim(cli_razsocial) LIKE '$keyword%' ORDER BY cli_razsocial;";

if ($sqlca->query($sql) < 0)
    return false;

for ($i = 0; $i < $sqlca->numrows(); $i++) {
	$a		= $sqlca->fetchRow();
	$resultset[]	= $a;
}

?>

<ul id="clientes-list">

	<?php foreach($resultset as $country) { ?>
		<li onClick="SelectCliente('<?php echo $country["cli_codigo"]; ?>', '<?php echo $country["cli_razsocial"]; ?>');"><?php echo $country["cli_razsocial"]; ?></li>
	<?php } ?>

</ul>

<?php

}

?>
