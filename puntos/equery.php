<?php
date_default_timezone_set('America/Lima');

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

/*
 * Codigos de Error
 *
 * 0 - Ok
 * 1 - Error interno
 * 2 - Tarjeta no valida
 * 3 - Tarjeta no existe
 * 3 - 
 * 4 - 
 * 5 - 
 *
 */

$card = $_GET["tarjeta"];
if (!is_numeric($card))
	die(json_encode(Array("status" => 21)));
settype($card,"int");

$sql = "
SELECT
	c.nu_cuenta_puntos,
	t.id_cuenta
FROM
	prom_ta_tarjetas t
	JOIN prom_ta_cuentas c ON t.id_cuenta = c.id_cuenta
WHERE
	t.nu_tarjeta_numero = {$card};";

if ($sqlca->query($sql) < 0)
	die(json_encode(Array("status" => 1)));

if ($sqlca->numrows() != 1)
	die(json_encode(Array("status" => 3)));

$row = $sqlca->fetchRow();
$balance = $row["nu_cuenta_puntos"];
$acctid = $row["id_cuenta"];

$sql = "
SELECT
	id_tarjeta
FROM
	prom_ta_tarjetas
WHERE
	id_cuenta = {$acctid};";

if ($sqlca->query($sql) < 0)
	die(json_encode(Array("status" => 1)));

//if ($sqlca->numrows() != 1)
//	die(json_encode(Array("status" => 3)));

$cards = Array();
for ($i = 0;$i < $sqlca->numrows();$i++) {
	$row = $sqlca->fetchRow();
	$cards[] = $row["id_tarjeta"];
}
$cardids = implode(",",$cards);

$sql = "
SELECT
	to_char(m.dt_punto_fecha,'DD/MM/YYYY') AS dt_punto_fecha,
	m.nu_punto_tipomov,
	m.nu_punto_puntaje,
	COALESCE(m.nu_trans_importe,0) AS nu_trans_importe,
	trim(m.ch_trans_codigo) AS ch_trans_codigo
FROM
	prom_ta_movimiento_puntos m
WHERE
	m.id_tarjeta IN ({$cardids})
	AND m.nu_punto_tipomov != 4
ORDER BY
	m.dt_punto_fecha DESC
LIMIT
	10;";

if ($sqlca->query($sql) <= 0)
	die(json_encode(Array("status" => 1)));

$movements = Array();
for ($i = 0;$i < $sqlca->numrows();$i++) {
	$row = $sqlca->fetchRow();
	$points = $row["nu_punto_puntaje"];
	if ($row["nu_punto_tipomov"] != 1)
		$points .= -1;
	$movements[] = Array(
		"date"		=>	$row["dt_punto_fecha"],
		"type"		=>	$row["nu_punto_tipomov"],
		"amount"	=>	$row["nu_trans_importe"],
		"points"	=>	$points,
		"product"	=>	$row["ch_trans_codigo"],
	);
}

$ret = Array(
	"status"	=>	0,
	"card"		=>	$card,
	"balance"	=>	$balance,
	"movements"	=>	$movements
);

die(json_encode($ret));
