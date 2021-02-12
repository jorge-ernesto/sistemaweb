<?php
include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

function llamadaRemota($parametros)
{
	global $sqlca;

	$sql = "select par_valor from int_parametros where par_nombre='master_puntos';";
	$sqlca->query($sql);
	$row = $sqlca->fetchRow();
	$ip = $row[0];

	$url = "http://" . $ip . "/sistemaweb/puntos/index.php?action=registrarConsumo";

	foreach($parametros as $parametro=>$valor) {
		$url .= "&" . $parametro . "=" . urlencode($valor);
	}echo "\n$url\n";

	$fh = fopen($url,"rb");
	if ($fh===FALSE)
		return FALSE;

	$res = '';

	while (!feof($fh)) {
		$res .= fread($fh, 8192);
	}

	fclose($fh);
	return $res;
}

$rs = $sqlca->query("SELECT par_valor FROM int_parametros WHERE par_nombre = 'codes';");
while ($row = $sqlca->fetchRow()) {
	$sucursal = $row['par_valor'];
}

$sql = "SELECT
		pos_puntosfide_id,
		tarjeta,
		trim(codigo) AS codigo,
		to_char(fecha,'YYYY-MM-DD HH24:MI:SS') AS fecha,
		td,
		pos,
		numero,
		cantidad,
		importe
	FROM
		pos_puntosfide
	WHERE
		procesado=false;";
$rs = $sqlca->query($sql, "_select");

while ($row = $sqlca->fetchRow("_select")) {
	$params = array();
	$params['tarjeta'] = $row['tarjeta'];
	$params['codigo'] = $row['codigo'];
	$params['fecha'] = $row['fecha'];
	$params['td'] = $row['td'];
	$params['caja'] = $row['pos'];
	$params['numero'] = $row['numero'];
	$params['cantidad'] = $row['cantidad'];
	$params['importe'] = $row['importe'];
	$params['sucursal'] = $sucursal;

	$result = llamadaRemota($params);

	if (!($result == FALSE || $result == "error"))
	{
		echo "Envio correcto id = " . $row['pos_puntosfide_id'] . "\n";
		$sql = "UPDATE pos_puntosfide SET procesado=true WHERE pos_puntosfide_id=" . $row['pos_puntosfide_id'];
		$sqlca->query($sql, "_update");
	}
	else {
		echo "Error de envio id = " . $row['pos_puntosfide_id'] . "\n";
	}
}

