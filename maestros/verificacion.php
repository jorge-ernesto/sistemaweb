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

$almacen	= trim($_REQUEST['almacen']);
$fecha		= trim($_REQUEST['fecha']);
$numvale	= trim($_REQUEST['numvale']);
$codcliente	= trim($_REQUEST['codcliente']);
$numtar		= trim($_REQUEST['numtar']);
$nro_placa	= trim($_REQUEST['nro_placa']);

if($_REQUEST['accion'] == 'vvale'){//VERIFICAR SI EXISTE VALE

	$sql = "
	SELECT
		CH_SUCURSAL||DT_FECHA||CH_DOCUMENTO as CLAVE
	FROM
		val_ta_cabecera
	WHERE
		CH_SUCURSAL			= '" . $almacen . "'
		AND DT_FECHA		= '" . $fecha . "'
		AND CH_DOCUMENTO 	= '" . $numvale . "';
	";

	if ($sqlca->query($sql) < 0)
		return false;

	if ($sqlca->numrows() > 0)
		echo "<blink style='color: red'> Ya existe Vale: ".$numvale."</blink>";

} elseif($_REQUEST['accion'] == 'GetTarjetas'){

	$sql = "SELECT * FROM pos_fptshe1 WHERE TRIM(codcli) = '$codcliente' ORDER BY numtar;";

	if ($sqlca->query($sql) < 0)
	    return false;

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a		= $sqlca->fetchRow();
		$resultset[]	= $a;
	}

	?>

	<ul id="card-list">

		<?php foreach($resultset as $card) { ?>
			<li onClick="SelectCard('<?php echo $card["numtar"]; ?>', '<?php echo $codcliente; ?>');"><?php echo $card["numtar"]; ?></li>
		<?php } ?>

	</ul>

	<?php

} elseif($_REQUEST['accion'] == 'GetPlaca'){

	$sql = "SELECT nomusu, nu_documento_chofer, numpla as nro_placa FROM pos_fptshe1 WHERE TRIM(codcli) = '$codcliente' AND numtar = '$numtar';";

	if ($sqlca->query($sql) < 0)
	    return false;

	$data = $sqlca->fetchRow();
	echo json_encode($data);

} elseif($_REQUEST['accion'] == 'GetPlacaCliente'){

	$sql = "SELECT numpla FROM pos_fptshe1 WHERE TRIM(codcli) = '" . $codcliente. "'";

	if ($sqlca->query($sql) < 0)
	    return false;

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a				= $sqlca->fetchRow();
		$resultset[]	= $a;
	}

	?>

	<ul id="nro_placa-list">

		<?php foreach($resultset as $card) { ?>
			<li onClick="SelectPlaca('<?php echo $card["numpla"]; ?>', '<?php echo $codcliente; ?>');"><?php echo $card["numpla"]; ?></li>
		<?php } ?>

	</ul>

	<?php

} elseif($_REQUEST['accion'] == 'GetTarjeta'){

	$sql = "SELECT nomusu, nu_documento_chofer, numtar FROM pos_fptshe1 WHERE TRIM(codcli) = '" . $codcliente. "' AND numpla = '" . $nro_placa. "';";

	if ($sqlca->query($sql) < 0)
	    return false;

	$data 	= $sqlca->fetchRow();
	echo json_encode($data);

}

