<?php

$dbconn = pg_connect("host=localhost dbname=integrado user=postgres");
if ($dbconn===FALSE)
	die("Error de conexion DB");

$tabla = $_GET['tabla'];
$ninicio = $_GET['ninicio'];
if (isset($_GET['nfin']))
	$nfin = $_GET['nfin'];
else
	$nfin = $ninicio;
$caja = $_GET['caja'];
$psf = "";
if (isset($_GET['market']))
	$psf = "_market";

if (isset($_GET['fmt']))
	$fmt = 1;
else
	$fmt = 0;

$sql =	"	SELECT
			p1.par_valor,
			p2.par_valor,
			p3.par_valor,
			p4.par_valor,
			p.nroserie,
			p.autsunat
		FROM
			int_parametros p1
			LEFT JOIN int_parametros p2 ON p2.par_nombre = 'desces'
			LEFT JOIN int_parametros p3 ON p3.par_nombre = 'dires'
			LEFT JOIN int_parametros p4 ON p4.par_nombre = 'ruc$psf'
			LEFT JOIN pos_cfg p ON p.pos = '$caja'
		WHERE
			p1.par_nombre = 'razsocial$psf';";

$res = pg_query($dbconn,$sql);
if ($res===FALSE)
	die("Error al obtener parametros(1)");

if (pg_num_rows($res)!=1)
	die("Error al obtener parametros(2)");

$row = pg_fetch_array($res,0,PGSQL_NUM);
$razsocial = $row[0];
$desces = $row[1];
$dires = $row[2];
$ruc = $row[3];
$nroserie = $row[4];
$autsunat = $row[5];

$sql =	"	SELECT
			t.trans
		FROM
			$tabla t
		WHERE
			t.trans BETWEEN $ninicio AND $nfin
			AND t.caja = '$caja'
		GROUP BY
			1
		ORDER BY
			1;";

$res = pg_query($dbconn,$sql);
if ($res===FALSE)
	die("Error al obtener rango(1)");

$rows = pg_num_rows($res);
if ($rows<=0)
	die("Error al obtener rango(2)");

for ($i=0;$i<$rows;$i++) {
	$rt = pg_fetch_array($res,$i,PGSQL_NUM);

	$sql =	"	SELECT
				t.td,
				to_char(t.fecha,'DD/MM/YYYY HH24:MI:SS'),
				t.trans,
				t.caja,
				t.ruc,
				r.razsocial,
				t.pump,
				c.ch_nombrecombustible,
				t.cantidad,
				substring(a.art_unidad from 4),
				t.precio,
				t.importe,
				t.igv,
				t.tipo,
				a.art_descripcion,
				t.soles_km,
				t.fpago
			FROM
				$tabla t
				LEFT JOIN ruc r ON t.ruc = r.ruc
				LEFT JOIN comb_ta_combustibles c ON t.codigo = c.ch_codigocombustible
				LEFT JOIN int_articulos a ON t.codigo = a.art_codigo
			WHERE
				t.caja = '$caja'
				AND t.trans = {$rt[0]};";

	$resx = pg_query($dbconn,$sql);
	if ($resx===FALSE)
		die("Error al obtener ticket {$rt[0]} (1)");

	$tr = pg_num_rows($resx);
	if ($rows<=0)
		die("Error al obtener ticket {$rt[0]} (2)");

	$ra = Array();
	for ($x=0;$x<$tr;$x++)
		$ra[] = pg_fetch_array($resx,$x,PGSQL_NUM);
	pg_free_result($resx);
	echo reimprimirTicket($ra) . "\n\n\n\n\n";
}
pg_free_result($res);
pg_close($dbconn);

die("");

function reimprimirTicket($rows) {
	global $razsocial,$desces,$dires,$ruc,$nroserie,$razsocial,$autsunat,$fmt;

	$td = $rows[0][0];
	$fecha = $rows[0][1];
	$trans = $rows[0][2];
	$caja = $rows[0][3];
	$cruc = $rows[0][4];
	$crazsocial = $rows[0][5];
	$pump = $rows[0][6];
	$combustible = $rows[0][7];
	$cantidad = trim(number_format($rows[0][8],3,".",""));
	$unidad = trim($rows[0][9]);
	$precio = trim(number_format($rows[0][10],3,".",""));
	$importe = trim(number_format($rows[0][11],2,".",""));
	$igv = trim(number_format($rows[0][12],2,".",""));
	$tipo = $rows[0][13];
	$totalticket = trim(number_format($rows[0][15],2,".",""));
	$fpago = $rows[0][16];

	$buffer = "";

	$buffer .= align($razsocial,1) . "\n";
	$buffer .= align($desces,1) . "\n";
	$buffer .= align($dires,1) . "\n";
	if ($fmt == 0) {
		$buffer .= align($fecha . " No. " . $trans . "/" . $caja,1) . "\n";
		$buffer .= align("RUC: " . $ruc . " N/S: " . $nroserie,1) . "\n";
		$buffer .= "========================================\n";
	} else {
		$buffer .= align("R.U.C.: " . $ruc,1) . "\n\n";
		$buffer .= align($fecha,1) . "\n";
		$buffer .= dbAlign("Punto de Venta " . $caja,"Serie " . $nroserie) . "\n";
		$buffer .= align("Ticket No. " . $trans,1) . "\n\n";
		if ($td == "B")
			$buffer .= "----------------------------------------\n";
	}
	if ($td == "F") {
		if ($fmt == 0) {
			$buffer .= align("RUC Cliente: " . $cruc,1) . "\n";
			$buffer .= align($crazsocial,1) . "\n";
			$buffer .= "========================================\n";
		} else {
			$buffer .= align("R.U.C. CLIENTE: " . $cruc,1) . "\n";
			$buffer .= align($crazsocial,1) . "\n\n";
			$buffer .= "----------------------------------------\n";
		}
	}
	if ($tipo == "C") {
		if ($fmt == 0)
			$buffer .= "Posicion: $pump\n";
		if (count($rows) == 2 && $importe == $totalticket)
			$totalticket = $totalticket + $rows[1][11];
	}
	for ($i = 0;$i < count($rows);$i++) {
		if ($tipo == "C") {
			if ($fmt == 0) {
				if ($i == 0)
					$buffer .= dbAlign("$combustible $cantidad $unidad @ $precio","S/. $importe") . "\n";
				else
					$buffer .= dbAlign("Bonificacion","S/. " . trim(number_format($rows[$i][11],2,".",""))) . "\n";
			} else {
				if ($i == 0) {
					$buffer .= "Lado " . $pump . " " . $combustible . "\n";
					$buffer .= dbAlign("$cantidad $unidad x $precio","S/. $importe") . "\n";
				} else
					$buffer .= dbAlign("Dif. Precio","S/. " . trim(number_format($rows[$i][11],2,".",""))) . "\n";
			}
		} else {
			$buffer .= $rows[$i][14] . "\n";
			$buffer .= dbAlign(trim(number_format($rows[$i][8],2,".","")) . " @ " . trim(number_format($rows[$i][10],2,".","")),"S/. " . trim(number_format($rows[$i][11],2,".",""))) . "\n";
		}
	}
	if ($fmt == 0)
		$buffer .= "========================================\n";
	else
		$buffer .= "\n";

	if ($td == "F") {
		$igv = trim(number_format($igv,2,".",""));
		$buffer .= dbAlign("Valor Venta:","S/. ".number_format($totalticket - ($totalticket-$totalticket/1.18),2,".","")) . "\n";;
		$buffer .= dbAlign("IGV(18%):","S/. ".number_format(($totalticket-$totalticket/1.18),2,".","")) . "\n";;
	}

	$buffer .= dbAlign("Total:","S/. $totalticket") . "\n";;
	$buffer .= "\n";

	if ($fpago == "1")
		$fpago = "Efectivo";
	else
		$fpago = "Credito";

	$buffer .= dbAlign("F. Pago: " . $fpago) . "\n";
	$buffer .= "\n";

	if ($autsunat != NULL)
		$buffer .= align("   Autorizacion Sunat: " . $autsunat,1) . "\n";

	return $buffer;
}

function espacios($q) {
	$str = "";
	for ($i=0;$i<$q;$i++)
		$str .= " ";
	return $str;
}

function align($str,$dir) {
	$len = strlen($str);
	switch ($dir) {
		case 1: // '\001'
			$str = espacios((40 - $len) / 2) . $str;
			break;
		case 2: // '\002'
			$str = espacios(40 - $len) . $str;
			break;
	}
	return $str;
}

function dbAlign($str1,$str2) {
	return $str1 . espacios(40 - (strlen($str1) + strlen($str2))) . $str2;
}

