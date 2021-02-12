<?php
include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

$sql = "
SELECT
	p1.par_valor,
	p2.par_valor,
	p3.par_valor,
	p4.par_valor
FROM
	int_parametros p1
	LEFT JOIN int_parametros p2 ON p2.par_nombre = 'iridium_username'
	LEFT JOIN int_parametros p3 ON p3.par_nombre = 'iridium_password'
	LEFT JOIN int_parametros p4 ON p4.par_nombre = 'iridium_dbname2'
WHERE
	p1.par_nombre = 'iridium_server'";

if ($sqlca->query($sql) < 0)
	return "ERROR\nINIT";

if ($sqlca->numrows() != 1)
	return "ERROR\nINIT";

$reg = $sqlca->fetchRow();

$MSSQLDBHost = $reg[0];
$MSSQLDBUser = $reg[1];
$MSSQLDBPass = $reg[2];
$MSSQLDBName = $reg[3];

$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
mssql_select_db($MSSQLDBName,$mssql);

$action = $_GET['action'];
$terminalId = $_GET['terminalId'];

switch ($action) {
	case "syncDocument":
		$reqNumber = $_GET['reqnumber'];
		$quantity = $_GET['quantity'];
		$lineTotal = $_GET['linetotal'];
		$taxTotal = $_GET['taxtotal'];
		$grandTotal = $_GET['grandtotal'];
		$saleNumber = $_GET['salenumber'];
		if (isset($_GET['serialb'])) {
			$docType = "03";
			$docSerial = $_GET['serialb'];
			$docNumber = $_GET['numberb'];
		} else if (isset($_GET['serialf'])) {
			$docType = "01";
			$docSerial = $_GET['serialf'];
			$docNumber = $_GET['numberf'];
		} else {
			$docType = "";
			$docSerial = "";
			$docNumber = "";
		}
		if (isset($_GET['serialw'])) {
			$wSerial = $_GET['serialw'];
			$wNumber = $_GET['numberw'];
		} else {
			$wSerial = "";
			$wNumber = "";
		}

		$sql = "
UPDATE
	Aux_Tesacom
SET " . (($docType != "") ? "
	Tipo_Documento = {$docType}, 
	serie = '{$docSerial}',
	numero = {$docNumber}," : "") . " " . (($wSerial != "") ? "
	Serie_guia = '{$wSerial}',
	Numero_guia = {$wNumber}," : "") . "
	Numero_Golpe = '{$saleNumber}',
	Cantidad_D_Galones = {$quantity}
WHERE
	Codigo_Pedido = '{$reqNumber}'";trigger_error($sql);
		if (mssql_query($sql,$mssql) == TRUE)
			echo "OK";
		else
			echo "ERROR\nSDQ1";

		break;
	case "syncWeight":
		$weight = $_GET['weight'];
		$ts = $_GET['ts'];
		$gravity = $_GET['gravity'];
		$sql = "SELECT p.ID_Placa FROM Placa p WHERE p.Placa = '{$terminalId}'";
		$res = mssql_query($sql,$mssql);
		if ($res === FALSE || mssql_num_rows($res) == 0) {
			echo "ERROR\nSWQ1";
			break;
		}
		$r = mssql_fetch_row($res);
		$VID = $r[0];
		mssql_free_result($res);

		$sql = "
INSERT INTO
	Aux_Energigas_Pesaje
(
	ID_PLACA,
	Fecha,
	Peso,
	Gravedad
) VALUES (
	{$VID},
	DATEADD(ss,{$ts},'1970-01-01'),
	{$weight},
	{$gravity}
);";
		if (mssql_query($sql,$mssql) == TRUE)
			echo "OK";
		else
			echo "ERROR\nSWQ2";

		break;
	case "syncRequests":
		$sql = "
SELECT
	r.Codigo_Pedido,
	r.Cantidad_P_Galones,
	r.Precio_Galon,
	'GAS LICUADO DE PETROLEO',
	r.Razon_Social,
	r.Direccion_Anexo,
	r.Ruc,
	r.Direccion_Fiscal,
	r.ID_Item
FROM
	Aux_Tesacom r
	JOIN Placa p ON (r.ID_Placa = p.ID_Placa)
WHERE
	p.Placa = '{$terminalId}'
	AND (
		r.TerminalSent IS NULL
		OR r.TerminalSent = 0
	);";

		$res = mssql_query($sql,$mssql);
		if ($res === FALSE) {
			echo "ERROR\nSRQ1";
			break;
		}			

		for ($i = 0; $i < mssql_num_rows($res); ++$i) {
			$r = mssql_fetch_row($res);
			$hypos = strpos($r[8],"-");
			if ($hypos !== FALSE)
				$r[8] = substr($r[8],$hypos);
			echo "{$r[0]}\t{$r[1]}\t{$r[2]}\t{$r[3]}\t{$r[4]}\t{$r[5]}\t{$r[6]}\t{$r[7]}\t{$r[8]}\t{$r[9]}\n";
			$sql = "UPDATE Aux_Tesacom SET TerminalSent = 1 WHERE Codigo_Pedido = '{$r[0]}';";
			mssql_query($sql,$mssql);
		}

		mssql_free_result($res);

		if ($i == 0)
			echo "NOPREQ :D xD u_U";

		break;
	case "syncConfig":
		break;
	case "getVersion":
		$sql = "
SELECT
	par_valor
FROM
	int_parametros
WHERE
	par_nombre = 'granel_cv'";

		if ($sqlca->query($sql) < 0)
			return "ERROR\nINIT";

		if ($sqlca->numrows() != 1)
			return "ERROR\nINIT";

		$reg = $sqlca->fetchRow();

		$cv = $reg[0];
		echo $cv;

		break;
	case "getUpdate":
		$sql = "
SELECT
	par_valor
FROM
	int_parametros
WHERE
	par_nombre = 'granel_cv'";

		if ($sqlca->query($sql) < 0)
			return "ERROR\nINIT";

		if ($sqlca->numrows() != 1)
			return "ERROR\nINIT";

		$reg = $sqlca->fetchRow();

		$cv = $reg[0];
		readfile("/usr/local/granel7110/updatedist/g7_{$cv}");

		break;
}

mssql_close($mssql);
?>
