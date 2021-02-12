<?php
include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

function simpleQuery($sql) {
	global $sqlca;

	$ret = NULL;

	if ($sqlca->query($sql) < 0)
		return NULL;
	if ($rr = $sqlca->fetchRow())
		$ret = $rr[0];

	return $ret;
}

function simpleChange($sql) {
	global $sqlca;

	if ($sqlca->query($sql) < 0)
		return FALSE;

	return TRUE;
}

function checkExistanceHeader($sucursal,$dia,$trans) {
	$sql =	"	SELECT" .
		"		count(*)" .
		"	FROM" .
		"		val_ta_cabecera" .
		"	WHERE" .
		"		ch_sucursal = '" . pg_escape_string($sucursal) . "'" .
		"		AND dt_fecha = '" . pg_escape_string($dia) . "'" .
		"		AND ch_documento = '" . pg_escape_string($trans) . "';";

	return (simpleQuery($sql) != 0);
}

function checkExistanceDetail($sucursal,$dia,$trans,$codigo) {
	$sql =	"	SELECT" .
		"		count(*)" .
		"	FROM" .
		"		val_ta_detalle" .
		"	WHERE" .
		"		ch_sucursal = '" . pg_escape_string($sucursal) . "'" .
		"		AND dt_fecha = '" . pg_escape_string($dia) . "'" .
		"		AND ch_documento = '" . pg_escape_string($trans) . "'" .
		"		AND ch_articulo = '" . pg_escape_string($codigo) . "';";

	return (simpleQuery($sql) != 0);
}

function insertHeader($sucursal,$caja,$trans,$tarjeta,$odometer,$voucher,$pump,$dia,$turno) {
	global $sqlca;

	$sql =	"	SELECT" .
		"		f.numpla," .
		"		f.codcli," .
		"		f.codcue" .
		"	FROM" .
		"		pos_fptshe1 f" .
		"		LEFT JOIN int_clientes c ON trim(f.codcli) = trim(c.cli_codigo)" .
		"	WHERE" .
		"		numtar = '" . pg_escape_string($tarjeta) . "'" .
		"		AND estblo='N'";

	if ($sqlca->query($sql) < 0)
		return "ERRORH1";

	if ($sqlca->numrows() < 1)
		return "INVALID";

	$rr = $sqlca->fetchRow();

	$placa = $rr[0];
	$cliente = $rr[1];
	$cuenta = $rr[2];

	$sql =	"	INSERT INTO" .
		"		val_ta_cabecera" .
		"	(" .
		"		ch_sucursal," .
		"		dt_fecha," .
		"		ch_documento," .
		"		ch_cliente," .
		"		ch_glosa," .
		"		ch_placa, " .
		"		nu_odometro," .
		"		ch_tarjeta," .
		"		ch_caja," .
		"		ch_turno," .
		"		ch_lado," .
		"		dt_fechaactualizacion," .
		"		ch_planilla," .
		"		ch_estado" .
		"	) VALUES (" .
		"		'" . pg_escape_string($sucursal) . "'," .
		"		'" . pg_escape_string($dia) . "'," .
		"		'" . $trans . "'," .
		"		'" . pg_escape_string($cliente) . "'," .
		"		'VALES CLIENTE PCT COMB'," .
		"		'" . pg_escape_string($placa) . "', " .
		"		{$odometer}," .
		"		'" . pg_escape_string($tarjeta) . "'," .
		"		'" . $caja . "'," .
		"		'" . $turno . "'," .
		"		'" . pg_escape_string($pump) . "'," .
		"		now()," .
		"		''," .
		"		'1'" .
		"	);";

	if (!simpleChange($sql))
		return "ERRORH2";

	$sql =	"	INSERT INTO" .
		"		val_ta_complemento" .
		"	(" .
		"		ch_sucursal," .
		"		dt_fecha," .
		"		ch_documento," .
		"		ch_numeval," .
		"		nu_importe," .
		"		ch_estado," .
		"		dt_fechaactualizacion," .
		"		ch_usuario," .
		"		ch_auditorpc" .
		"	) VALUES (" .
		"		'" . pg_escape_string($sucursal) . "'," .
		"		'" . pg_escape_string($dia) . "'," .
		"		'" . $trans . "'," .
		"		'" . pg_escape_string($voucher) . "'," .
		"		0," .	// TODO : This may be needed to change
		"		'1'," .
		"		now()," .
		"		'TRIV'," .
		"		'TRIV'" .
		"	);";

	if (!simpleChange($sql))
		return "ERRORH3";

	return "OK";
}

function insertDetail($sucursal,$trans,$dia,$turno,$codigo,$cantidad,$precio,$importe) {
	$sql =	"	INSERT INTO" .
		"		val_ta_detalle" .
		"	(" .
		"		ch_sucursal," .
		"		dt_fecha," .
		"		ch_documento," .
		"		ch_articulo," .
		"		nu_cantidad," .
		"		nu_importe," .
		"		ch_estado," .
		"		dt_fechaactualizacion," .
		"		nu_factor_igv," .
		"		nu_precio_unitario" .
		"	) VALUES (" .
		"		'" . pg_escape_string($sucursal) . "'," .
		"		'" . pg_escape_string($dia) . "'," .
		"		'" . $trans . "'," .
		"		'" . pg_escape_string($codigo) . "'," .
		"		{$cantidad}," .
		"		{$importe}," .
		"		'1'," .
		"		now()," .
		"		util_fn_igv_porarticulo('" . pg_escape_string($codigo) . "')," .
		"		{$precio}" .
		"	);";

	if (!simpleChange($sql))
		return "ERRORDN1";

	return "OK";
}

function updateDetail($sucursal,$trans,$dia,$turno,$codigo,$cantidad,$precio,$importe) {
	$sql =	"	UPDATE" .
		"		val_ta_detalle" .
		"	SET" .
		"		nu_cantidad = nu_cantidad + {$cantidad}," .
		"		nu_importe = nu_importe + {$importe}," .
		"		nu_precio_unitario = nu_precio_unitario + {$precio} " .
		"	WHERE" .
		"		ch_sucursal = '" . pg_escape_string($sucursal) . "'" .
		"		AND dt_fecha = '" . pg_escape_string($dia) . "'" .
		"		AND ch_documento = '" . $trans . "'" .
		"		AND ch_articulo = '" . pg_escape_string($codigo) . "';";

	if (!simpleChange($sql))
		return "ERRORDE1";

	return "OK";
}

function validateData($regist) {
	global $sqlca;

/*
 * CType:
 *	0	Credit
 *	1	Prepaid
 *	2	Cash
 */


	$sql =	"	SELECT" .
		"		c.cli_codigo," .
		"		c.cli_creditosol," .
		"		c.cli_razsocial," .
		"		c.cli_anticipo," .
		"		c.cli_ndespacho_efectivo," .
		"		t.numtar," .
		"		c.cli_ruc" .
		"	FROM" .
		"		pos_fptshe1 t" .
		"		JOIN int_clientes c ON (t.codcli = c.cli_codigo)" .
		"	WHERE" .
		"		'" . pg_escape_string($regist) . "' IN (t.numtar,t.numpla)" .
		"		AND t.estblo = 'N';";

	if ($sqlca->query($sql) < 0)
		return "ERROR";

	if ($sqlca->numrows() < 1)
		return "INVALID";

	$rr = $sqlca->fetchRow();

	$client = $rr[0];
	$limit = $rr[1];
	$clientname = $rr[2];
	$clientruc = $rr[6];
	if ($rr[4] == "1")
		$ctype = 2;
	else
		$ctype = (($rr[3] == "S") ? 1 : 0);
	$realcard = $rr[5];

	if ($limit == NULL || $limit == 0 || $ctype == 2)
		$limit = null;

	if ($ctype == 1)
		$limit = 0;

	// Money already paid by the client
	if ($ctype == 1) {
		$sql =	"	SELECT" .
			"		COALESCE(sum(h.nu_importesaldo),0)" .
			"	FROM" .
			"		ccob_ta_cabecera h" .
			"	WHERE" .
			"		h.cli_codigo = '" . pg_escape_string($client) . "'" .
			"		AND h.ch_tipdocumento = '21';";
		$limit = simpleQuery($sql);
	}

	// Clients sales not invoices
	if (($ctype == 0 && $limit != NULL) || $ctype == 1) {
		$sql =	"	SELECT" .
			"		COALESCE(sum(h.nu_importe),0)" .
			"	FROM" .
			"		val_ta_cabecera h" .
			"	WHERE" .
			"		h.ch_cliente = '" . pg_escape_string($client) . "'" .
			"		AND ch_liquidacion IS NULL;";
		$limit -= simpleQuery($sql);
	}

	if ($ctype == 1) {
	// Preapid invoiced
		$sql =	"	SELECT" .
			"		COALESCE(sum(h.nu_importesaldo),0)" .
			"	FROM" .
			"		ccob_ta_cabecera h" .
			"	WHERE" .
			"		h.cli_codigo = '" . pg_escape_string($client) . "'" .
			"		AND h.ch_tipdocumento = '22';";
		$limit -= simpleQuery($sql);
	} else if ($ctype == 0 && $limit != null) {
		// Payment pending invoices
		$sql =	"	SELECT" +
			"		COALESCE(sum(x.xv),0)" +
			"	FROM" +
			"		(SELECT" +
			"			CASE" +
			"				WHEN h.ch_tipdocumento = '20' THEN sum(h.nu_importesaldo) * -1" +
			"				ELSE sum(h.nu_importesaldo)" +
			"			END AS xv" +
			"		FROM" +
			"			ccob_ta_cabecera h" +
			"		WHERE" +
			"			h.cli_codigo = '" . pg_escape_string($client) . "'" +
			"			AND h.ch_tipdocumento IN ('10','11','20','35')" +
			"		GROUP BY" +
			"			h.ch_tipdocumento" +
			"	) x;";
		$limit -= simpleQuery($sql);
	}

	if ($limit == NULL)
		$limit = "NaN";
	$ret = "{$realcard}\n{$clientname}\n{$ctype}\n{$limit}\n{$clientruc}";

	return $ret;
}

function registerData($sucursal,$caja,$trans,$tarjeta,$odometer,$voucher,$pump,$dia,$turno,$codigo,$cantidad,$precio,$importe) {
	global $sqlca;

	settype($odometer,"int");
	settype($turno,"int");
	settype($caja,"int");
	settype($trans,"int");
	settype($cantidad,"double");
	settype($precio,"double");
	settype($importe,"double");

	if (!is_numeric($sucursal))
		return "ERRORV1";

	if (!is_numeric($tarjeta))
		return "ERRORV2";

	if ($voucher == "NaN")
		$voucher = NULL;
	else if (!is_numeric($voucher))
		return "ERRORV3";

	if (!is_numeric($pump))
		return "ERRORV4";

	$ret = "OK";

	if (!checkExistanceHeader($sucursal,$dia,$trans))
		$ret = insertHeader($sucursal,$caja,$trans,$tarjeta,$odometer,$voucher,$pump,$dia,$turno);

	if ($ret != "OK")
		return "ERRORQ1-{$ret}";

	if (checkExistanceDetail($sucursal,$dia,$trans,$codigo))
		$ret = updateDetail($sucursal,$trans,$dia,$turno,$codigo,$cantidad,$precio,$importe);
	else
		$ret = insertDetail($sucursal,$trans,$dia,$turno,$codigo,$cantidad,$precio,$importe);

	if ($ret != "OK")
		return "ERRORQ2-{$ret}";

	return $ret;
}

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

if (!isset($_POST['action']) || $_POST['action'] == "")
	die("ERRORM1");

if ($_POST['action'] == "validateData") {
	if (!isset($_POST['regist']))
		die("ERRORM2");

	$regist = $_POST['regist'];
	die(validateData($regist));
} else if ($_POST['action'] == "registerLine") {
	if (!isset($_POST['sucursal']))
		die("ERRORM3");
	$sucursal = $_POST['sucursal'];

	if (!isset($_POST['caja']))
		die("ERRORM4");
	$caja = $_POST['caja'];

	if (!isset($_POST['trans']))
		die("ERRORM5");
	$trans = $_POST['trans'];

	if (!isset($_POST['tarjeta']))
		die("ERRORM6");
	$tarjeta = $_POST['tarjeta'];

	if (!isset($_POST['odometer']))
		die("ERRORM7");
	$odometer = $_POST['odometer'];

	if (!isset($_POST['voucher']))
		die("ERRORM8");
	$voucher = $_POST['voucher'];

	if (!isset($_POST['pump']))
		die("ERRORM9");
	$pump = $_POST['pump'];

	if (!isset($_POST['dia']))
		die("ERRORM10");
	$dia = $_POST['dia'];

	if (!isset($_POST['turno']))
		die("ERRORM11");
	$turno = $_POST['turno'];

	if (!isset($_POST['codigo']))
		die("ERRORM12");
	$codigo = $_POST['codigo'];

	if (!isset($_POST['cantidad']))
		die("ERRORM13");
	$cantidad = $_POST['cantidad'];

	if (!isset($_POST['precio']))
		die("ERRORM14");
	$precio = $_POST['precio'];

	if (!isset($_POST['importe']))
		die("ERRORM15");
	$importe = $_POST['importe'];

	die(registerData($sucursal,$caja,$trans,$tarjeta,$odometer,$voucher,$pump,$dia,$turno,$codigo,$cantidad,$precio,$importe));
}
