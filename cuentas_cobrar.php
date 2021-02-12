<?php

$dbconn = pg_connect("host=localhost dbname=integrado user=postgres");
if ($dbconn===FALSE)
	die("Error de conexion DB");

$fh = fopen("/sistemaweb/saldos_clientes.csv","r");

if ($fh===FALSE) {
	pg_close($dbconn);
	die("No se pudo abrir el archivo");
}

$sql =	"
		SELECT
			ch_almacen
		FROM
			inv_ta_almacenes
		WHERE
			ch_clase_almacen='1'
		LIMIT
			1;
	";

$res = pg_query($dbconn,$sql);
if ($res===FALSE) {
		pg_close($dbconn);
		fclose($fh);
		die("No se pudo obtener almacen");
}

pg_query("BEGIN;");

while (!feof($fh)) {

	$line = fgets($fh);

	if ($line=="")
		break;

	$data = explode(",",$line);

	if (insertaCliente($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6])===FALSE) {
		pg_query("ROLLBACK");
		pg_close($dbconn);
		fclose($fh);
		die("Error procesando");
	}

}

pg_query("COMMIT");

fclose($fh);
pg_close($dbconn);

die("Todo OK");

function insertaCliente($codigo,$tipo,$serie,$numero,$fe,$fv,$importe) {
	global $dbconn,$almacen;

	$d = substr($fe, 0, 2);
	$m = substr($fe, 3, 3);
	$y = substr($fe, 7, 2);

	if($m == "Ene")
		$m = "01";
	else if($m == "Feb")
		$m = "02";
	else if($m == "Mar")
		$m = "03";
	else if($m == "Apr")
		$m = "03";
	else if($m == "May")
		$m = "05";
	else if($m == "Jun")
		$m = "06";
	else if($m == "Jul")
		$m = "07";
	else if($m == "Aug")
		$m = "08";	
	else if($m == "Sep")
		$m = "09";	
	else if($m == "Oct")
		$m = "10";	
	else if($m == "Nov")
		$m = "11";	
	else if($m == "Dec")
		$m = "12";

	$fe = $d."-".$m."-20".$y;

	$sql="
		INSERT INTO
			ccob_ta_cabecera
		(
			cli_codigo,
			ch_tipdocumento,
			ch_seriedocumento,
			ch_numdocumento,
			ch_tipcontable,
			dt_fechaemision,
			dt_fecharegistro,
			dt_fechavencimiento,
			nu_dias_vencimiento,
			ch_moneda,
			nu_tipocambio,
			nu_importetotal,
			nu_importesaldo,
			dt_fechasaldo,
			ch_sucursal,
			nu_importeafecto,
			nu_impuesto1,
			ch_formapago,
			plc_codigo
		)VALUES(
			'$codigo',
			'$tipo',
			'$serie',
			'$numero',
			'A',
			'$fe',
			'$fe',
			'$fv',
			'30',
			'01',
			3.2,
			$importe,
			$importe,
			'$fe',
			'001',
			'".round(($importe/1.18),2)."',
			'".round(($importe - ($importe/1.18)),2)."',
			'02',
			'-'
		);
	";

	//echo $sql;

	$res = pg_query($dbconn,$sql);
	if ($res===FALSE)
		return FALSE;


	$sql="
		INSERT INTO
			ccob_ta_detalle
		(
			cli_codigo,
			ch_tipdocumento,
			ch_seriedocumento,
			ch_numdocumento,
			ch_identidad,
			ch_tipmovimiento,
			dt_fechamovimiento,
			ch_moneda,
			nu_tipocambio,
			nu_importemovimiento,
			ch_sucursal,
			plc_codigo
		)VALUES(
			'$codigo',
			'$tipo',
			'$serie',
			'$numero',
			'1',
			'1',
			'$fe',
			'02',
			3.2,
			$importe,
			'001',
			'-'
		);
	";

	$res = pg_query($dbconn,$sql);
	if ($res===FALSE)
		return FALSE;

	return TRUE;

}

