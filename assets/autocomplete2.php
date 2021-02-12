<?php


include_once('/sistemaweb/include/mvc_sistemaweb.php');
include_once('/sistemaweb/include/dbsqlca.php');

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

$Accion 	= trim($_POST['Accion']);
$criterio 	= trim($_POST['criterio']);
$sql 		= null;

if ($Accion == 'getClientes'){

	$sql = "
	SELECT
		cli_codigo,
		cli_ruc,
		cli_razsocial
	FROM
		int_clientes
	WHERE
		(cli_codigo LIKE '" . $criterio . "%' OR cli_razsocial LIKE '" . $criterio . "%')
	ORDER BY
		cli_razsocial
	LIMIT 15;
	";

	if ($sqlca->query($sql) < 0)
		return false;

	print_r(json_encode($sqlca->fetchAll()));
}else if ($Accion == 'getProductos'){

	$sql = "
	SELECT
		PRO.art_codigo,
		PRO.art_descripcion
	FROM
		int_articulos AS PRO
	WHERE
		PRO.art_plutipo = '1'
		AND (PRO.art_codigo LIKE '" . $criterio . "%' OR PRO.art_descripcion LIKE '" . $criterio . "%')
	ORDER BY
		PRO.art_descripcion
	LIMIT 15;
	";

	if ($sqlca->query($sql) < 0)
		return false;

	print_r(json_encode($sqlca->fetchAll()));
}
