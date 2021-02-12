<?php

function os_mssql_escape($str) {
	return str_replace("'","''",$str);
}

function onError_AIExit($mssql,$msg) {
	global $sqlca;
	mssql_query("ROLLBACK TRANSACTION;",$mssql);
	$sqlca->query("ROLLBACK;");
	return $msg;
}

function clientUpsert($mssql,$ruc,$razsocial) {
	global $sqlca;
	$res = mssql_query("SELECT 1 FROM Clientes WHERE RUC = '$ruc';",$mssql);
	if (mssql_num_rows($res) == 0)
		return mssql_query("INSERT INTO Clientes (idCliente,Razon,RUC,Notificar,PorcArt,Precio) VALUES ('$ruc','$razsocial','$ruc',0,0,0);",$mssql);
	return TRUE;
}

function addslashes_mssql($str) {
	if (is_array($str)) {
		foreach($str AS $id => $value) {
			$str[$id] = addslashes_mssql($value);
		}
	} else {
		$str = str_replace("'", "''", $str);
	}

	return $str;
}

class InterfaceQuipuModel extends Model {

	function ListadoAlmacenes() {
		global $sqlca;

		$query = "SELECT ch_almacen,ch_nombre_almacen FROM inv_ta_almacenes WHERE trim(ch_clase_almacen)='1' ORDER BY ch_almacen";

		if ($sqlca->query($query)<=0)
			return $sqlca->get_error();

		$ret = Array();
		$numrows = $sqlca->numrows();
		while($r = $sqlca->fetchRow())
			$ret[$r[0]] .= $r[0]." - ".$r[1];

		return $ret;
	}

	function obtenerParametros($almacen) {
		global $sqlca;

		$sql = "	SELECT
					p1.par_valor,
					p2.par_valor,
					p3.par_valor,
					p4.par_valor
				FROM
					int_parametros p1
					LEFT JOIN int_parametros p2 ON p2.par_nombre = 'concar_username'
					LEFT JOIN int_parametros p3 ON p3.par_nombre = 'concar_password'
					LEFT JOIN int_parametros p4 ON p4.par_nombre = 'concar_dbname$almacen'
				WHERE
					p1.par_nombre = 'concar_ip'
		";

		if ($sqlca->query($sql) < 0)
			return $defaultparams;

		if ($sqlca->numrows() != 1)
			return $defaultparams;

		$reg = $sqlca->fetchRow();

		return Array($reg[0],$reg[1],$reg[2],$reg[3]);
	}

	function ActualizarInterfaces($Parametros,$FechaIni,$FechaFin,$CodAlmacen, $agrupado) {
		require_once("/sistemaweb/include/mssqlemu.php");

		global $sqlca;

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$FechaDiv = explode("/", $FechaIni);
		$FechaIni = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
		$postrans = "pos_trans".$FechaDiv[2].$FechaDiv[1];
		$FechaDiv = explode("/", $FechaFin);
		$FechaFin = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];

		$anno	= $FechaDiv[2];
		$mes	= $FechaDiv[1];

		if (("pos_trans".$FechaDiv[2].$FechaDiv[1]) != $postrans) {
			return "INVALID_DATE";
		}

		if (strlen($FechaIni) < 10 || strlen($FechaFin) < 10) {
			return "Fecha no valida. Debe ser ingresada en formato DD/MM/YYYY, completando con ceros.";
		}

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);

		if ($mssql === FALSE)
			return "Error al conectarse a la base de datos del QUIPU";

		mssql_select_db($MSSQLDBName,$mssql);

		/* TERMINA LA CONEXION SQL SERVER B.D */

		/* FIRST DELETE DATA OF TABLE'S */

		$sql = "DELETE tmp_migracion WHERE fecha_emision BETWEEN '$FechaIni' AND '$FechaFin';";

		$res = mssql_query($sql, $mssql);

		if ($res === FALSE)
			return "Error delete data of table tmp_migracion";

		/* FIN */

		/* INSERT'S TO TABLE'S */

		mssql_query("BEGIN TRANSACTION;",$mssql);

		$sqlca->query("BEGIN;");

		if ($agrupado == 'S'){

		/*******************************************************************************
		* TICKETS BOLETA ACUMULADOS (pos_trans)                                        *
		*******************************************************************************/

			$sql = "
				SELECT
					MIN(t.trans) || MAX(t.trans) AS nro,
					t.dia AS fecha_emision,
					t.dia AS fecha_pago,
					$anno AS anno,
					$mes AS mes,
					'12' AS tipodocu,
					cfp.nu_posz_z_serie AS serie,
					MIN(t.trans) || '-' || MAX(t.trans) AS numero,
					'0' AS tdi,
					'-' AS ruc,
					'-' AS nombre,
					0.00 AS bbii,
					0.00 AS igv,
					0.00 AS bbii0,
					0.00 AS igv0,
					0.00 AS bbii1,
					0.00 AS igv1,
					ROUND(SUM(t.importe), 2) AS no_grabada,
					0.00 AS otros,
					ROUND(SUM(t.importe), 2) AS total,
					'S' AS moneda,
					'1' AS tc,
					'0.00' AS total_dolar,
					'0.00' AS detrac_numero,
					'01/01/1900' AS detra_fecha,
					'0.00' AS tasa_detra,
					'0.00' AS monto_detra,
					'01/01/1900' AS ref_fecha,
					'999' AS ref_tipo_doc,
					'000' AS ref_serie,
					'0000' AS ref_numero,
					'--' AS observaciones,
					'--' AS glosa,
					'0' AS campo11,
					'0' AS campo12,
					'0' AS campo21,
					'0' AS campo22,
					'0' AS campo31,
					'0' AS campo32,
					'40' AS diario
				FROM
					{$postrans} t
					LEFT JOIN pos_z_cierres cfp ON(t.caja = cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				WHERE
					td 			= 'B'
					AND t.es 	= '{$CodAlmacen}'
					AND t.dia BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
				GROUP BY
					t.dia,
					t.caja,
					cfp.nu_posz_z_serie;
			";

//			echo "\nTickets Boletas Acumulados:\n".$sql;

			if ($sqlca->query($sql)<0)
				return onError_AIExit($mssql,"Error al obtener tickets boletas Acumulados");

			while ($reg = $sqlca->fetchRow()) {

				$sql = "
					INSERT INTO tmp_migracion(
									nro,
									fecha_emision,
									fecha_pago,
									anno,
									mes,
									tipodocu,
									serie,
									numero,
									tdi,
									ruc,
									nombre,
									bbii,
									igv,
									bbii0,
									igv0,
									bbii1,
									igv1,
									no_grabada,
									otros,
									total,
									moneda,
									tc,
									total_dolar,
									detrac_numero,
									detra_fecha,
									tasa_detra,
									monto_detra,
									ref_fecha,
									ref_tipo_doc,
									ref_serie,
									ref_numero,
									observaciones,
									glosa,
									campo11,
									campo12,
									campo21,
									campo22,
									campo31,
									campo32,
									diario,
									estado
					) VALUES (
									{$reg[0]},
									'{$reg[1]}',
									'{$reg[2]}',
									{$reg[3]},
									{$reg[4]},
									{$reg[5]},
									'{$reg[6]}',
									'{$reg[7]}',
									'{$reg[8]}',
									'{$reg[9]}',
									'{$reg[10]}',
									{$reg[11]},
									{$reg[12]},
									{$reg[13]},
									{$reg[14]},
									{$reg[15]},
									{$reg[16]},
									{$reg[17]},
									{$reg[18]},
									{$reg[19]},
									'{$reg[20]}',
									{$reg[21]},
									{$reg[22]},
									'',
									'{$reg[24]}',
									{$reg[25]},
									{$reg[26]},
									'{$reg[27]}',
									{$reg[28]},
									'{$reg[29]}',
									'{$reg[30]}',
									'{$reg[31]}',
									'{$reg[32]}',
									'{$reg[33]}',
									'{$reg[34]}',
									'{$reg[35]}',
									'{$reg[36]}',
									'{$reg[37]}',
									'{$reg[38]}',
									{$reg[39]},
									NULL
					);
				";

//				echo "\n INSERT Ticket - Boleta Cabecera acumulado: \n".$sql;

				if (mssql_query($sql,$mssql)===FALSE)
					return onError_AIExit($mssql,"Error al trasladar cabecera de ticket boleta acumulado: {$reg[0]}");

			}

		}else{

			/*******************************************************************************
			* TICKETS BOLETAS DETALLADO (pos_trans)                                        *
			*******************************************************************************/

			$sql = "
				SELECT * FROM (
					SELECT
						(venta.caja) || (venta.trans)::TEXT AS nro,
						venta.dia::TEXT AS fecha_emision,
						venta.dia::TEXT AS fecha_pago,
						$anno::TEXT AS anno,
						$mes::TEXT AS mes,
						'12'::TEXT AS tipodocu,
						cfp.nu_posz_z_serie::TEXT AS serie,
						venta.trans::TEXT AS numero,
						'0'::TEXT AS tdi,
						'-'::TEXT AS ruc,
						'-'::TEXT AS nombre,
						0.00 AS bbii,
						0.00 AS igv,
						'0.00'::TEXT AS bbii0,
						'0.00'::TEXT AS igv0,
						'0.00'::TEXT AS bbii1,
						'0.00'::TEXT AS igv1,
						0.00 AS no_grabada,
						'0.00'::TEXT AS otros,
						0.00 AS total,
						'S'::TEXT AS moneda,
						'1'::TEXT AS tc,
						'0.00'::TEXT AS total_dolar,
						'0.00'::TEXT AS detrac_numero,
						'01/01/1900'::TEXT AS detra_fecha,
						'0.00'::TEXT AS tasa_detra,
						'0.00'::TEXT AS monto_detra,
						'01/01/1900'::TEXT AS ref_fecha,
						'999'::TEXT AS ref_tipo_doc,
						'000'::TEXT AS ref_serie,
						'0000'::TEXT AS ref_numero,
						'VENTA'::TEXT AS observaciones,
						'--'::TEXT AS glosa,
						'0'::TEXT AS campo11,
						'0'::TEXT AS campo12,
						'0'::TEXT AS campo21,
						'0'::TEXT AS campo22,
						'0'::TEXT AS campo31,
						'0'::TEXT AS campo32,
						'40'::TEXT AS diario
					FROM
						{$postrans} venta
						INNER JOIN (
							SELECT 
								(caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
								fecha,
								es||caja||trans AS idticket
							FROM
								{$postrans}
							WHERE
								tm 			= 'A'
								AND es		= '{$CodAlmacen}'
								AND td 		= 'B'
								AND dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
						) AS extorno ON (venta.caja||venta.td||venta.dia||venta.turno||venta.codigo||abs(venta.cantidad)||abs(venta.precio)||abs(venta.igv)||abs(venta.importe)||venta.ruc||venta.tipo||venta.pump||venta.fpago||venta.at||venta.text1||venta.placa||venta.es) = extorno.registro
						AND venta.tm 	= 'V'
						AND venta.es	= '{$CodAlmacen}'
						AND venta.td 	= 'B'
						AND venta.dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
						AND venta.fecha < extorno.fecha
						LEFT JOIN ruc R ON (R.ruc = venta.ruc)
						LEFT JOIN pos_z_cierres cfp ON(venta.es = cfp.ch_sucursal AND venta.dia = cfp.dt_posz_fecha_sistema::DATE AND venta.caja = cfp.ch_posz_pos AND venta.turno::INTEGER = cfp.nu_posturno)
					GROUP BY
						extorno.idticket,
						venta.trans,
						venta.caja,
						venta.dia, 
						cfp.nu_posz_z_serie
					) AS P
					UNION
					(
					SELECT
						(t.caja) || (t.trans)::TEXT AS nro,
						t.dia::TEXT AS fecha_emision,
						t.dia::TEXT AS fecha_pago,
						$anno::TEXT AS anno,
						$mes::TEXT AS mes,
						'12'::TEXT AS tipodocu,
						cfp.nu_posz_z_serie::TEXT AS serie,
						t.trans::TEXT AS numero,
						'0'::TEXT AS tdi,
						'-'::TEXT AS ruc,
						'-'::TEXT AS nombre,
						0.00 AS bbii,
						0.00 AS igv,
						'0.00'::TEXT AS bbii0,
						'0.00'::TEXT AS igv0,
						'0.00'::TEXT AS bbii1,
						'0.00'::TEXT AS igv1,
						0.00 AS no_grabada,
						'0.00'::TEXT AS otros,
						0.00 AS total,
						'S'::TEXT AS moneda,
						'1'::TEXT AS tc,
						'0.00'::TEXT AS total_dolar,
						'0.00'::TEXT AS detrac_numero,
						'01/01/1900'::TEXT AS detra_fecha,
						'0.00'::TEXT AS tasa_detra,
						'0.00'::TEXT AS monto_detra,
						'01/01/1900'::TEXT AS ref_fecha,
						'999'::TEXT AS ref_tipo_doc,
						'000'::TEXT AS ref_serie,
						'0000'::TEXT AS ref_numero,
						'VENTA'::TEXT AS observaciones,
						'--'::TEXT AS glosa,
						'0'::TEXT AS campo11,
						'0'::TEXT AS campo12,
						'0'::TEXT AS campo21,
						'0'::TEXT AS campo22,
						'0'::TEXT AS campo31,
						'0'::TEXT AS campo32,
						'40'::TEXT AS diario
					FROM
						{$postrans} t
						LEFT JOIN pos_z_cierres cfp ON(t.es = cfp.ch_sucursal AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.caja = cfp.ch_posz_pos AND t.turno::INTEGER = cfp.nu_posturno)
					WHERE
						t.es 		= '{$CodAlmacen}'
						AND t.tm 	= 'A'
						AND t.td 	= 'B'
						AND t.dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
					GROUP BY
						t.trans,
						t.caja,
						t.dia, 
						cfp.nu_posz_z_serie
					)
					UNION
					(
					SELECT
						(t.caja) || (t.trans)::TEXT AS nro,
						t.dia::TEXT AS fecha_emision,
						t.dia::TEXT AS fecha_pago,
						$anno::TEXT AS anno,
						$mes::TEXT AS mes,
						'12'::TEXT AS tipodocu,
						cfp.nu_posz_z_serie::TEXT AS serie,
						t.trans::TEXT AS numero,
						'0'::TEXT AS tdi,
						'-'::TEXT AS ruc,
						'-'::TEXT AS nombre,
						0.00 AS bbii,
						0.00 AS igv,
						'0.00'::TEXT AS bbii0,
						'0.00'::TEXT AS igv0,
						'0.00'::TEXT AS bbii1,
						'0.00'::TEXT AS igv1,
						ROUND(SUM(t.importe), 2) AS no_grabada,
						'0.00'::TEXT AS otros,
						ROUND(SUM(t.importe), 2) AS total,
						'S'::TEXT AS moneda,
						'1'::TEXT AS tc,
						'0.00'::TEXT AS total_dolar,
						'0.00'::TEXT AS detrac_numero,
						'01/01/1900'::TEXT AS detra_fecha,
						'0.00'::TEXT AS tasa_detra,
						'0.00'::TEXT AS monto_detra,
						'01/01/1900'::TEXT AS ref_fecha,
						'999'::TEXT AS ref_tipo_doc,
						'000'::TEXT AS ref_serie,
						'0000'::TEXT AS ref_numero,
						'VENTA'::TEXT AS observaciones,
						'--'::TEXT AS glosa,
						'0'::TEXT AS campo11,
						'0'::TEXT AS campo12,
						'0'::TEXT AS campo21,
						'0'::TEXT AS campo22,
						'0'::TEXT AS campo31,
						'0'::TEXT AS campo32,
						'40'::TEXT AS diario
					FROM
						{$postrans} t
						LEFT JOIN pos_z_cierres cfp ON(t.es = cfp.ch_sucursal AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.caja = cfp.ch_posz_pos AND t.turno::INTEGER = cfp.nu_posturno)
					WHERE
						t.es 		= '{$CodAlmacen}'
						AND t.tm 	= 'V'
						AND t.td 	= 'B'
						AND t.dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
						AND t.es||t.caja||t.trans NOT IN (
						SELECT
							LAST(venta.es||venta.caja||venta.trans) co_vtrans
						FROM
							{$postrans} venta
							INNER JOIN (
								SELECT 
									(caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
									fecha,
									es||caja||trans AS idticket
								FROM
									{$postrans}
								WHERE
									tm 			= 'A'
									AND es		= '{$CodAlmacen}'
									AND td  	= 'B'
									AND dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
								) AS extorno ON (venta.caja||venta.td||venta.dia||venta.turno||venta.codigo||abs(venta.cantidad)||abs(venta.precio)||abs(venta.igv)||abs(venta.importe)||venta.ruc||venta.tipo||venta.pump||venta.fpago||venta.at||venta.text1||venta.placa||venta.es) = extorno.registro
								AND venta.tm 	= 'V'
								AND venta.es	= '{$CodAlmacen}'
								AND venta.td 	= 'B'
								AND venta.dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
								AND venta.fecha < extorno.fecha
						GROUP BY
							extorno.idticket
						)
					GROUP BY
						t.trans,
						t.caja,
						t.dia, 
						cfp.nu_posz_z_serie
				);
			";

			//echo "\nTickets Boletas Detallado:\n".$sql;

			if ($sqlca->query($sql)<0)
				return onError_AIExit($mssql,"Error al obtener tickets boleta detallado");

			while ($reg = $sqlca->fetchRow()) {

				$sql = "
					INSERT INTO tmp_migracion(
									nro,
									fecha_emision,
									fecha_pago,
									anno,
									mes,
									tipodocu,
									serie,
									numero,
									tdi,
									ruc,
									nombre,
									bbii,
									igv,
									bbii0,
									igv0,
									bbii1,
									igv1,
									no_grabada,
									otros,
									total,
									moneda,
									tc,
									total_dolar,
									detrac_numero,
									detra_fecha,
									tasa_detra,
									monto_detra,
									ref_fecha,
									ref_tipo_doc,
									ref_serie,
									ref_numero,
									observaciones,
									glosa,
									campo11,
									campo12,
									campo21,
									campo22,
									campo31,
									campo32,
									diario,
									estado
					) VALUES (
									{$reg[0]},
									'{$reg[1]}',
									'{$reg[2]}',
									{$reg[3]},
									{$reg[4]},
									{$reg[5]},
									'{$reg[6]}',
									'{$reg[7]}',
									'{$reg[8]}',
									'{$reg[9]}',
									'{$reg[10]}',
									{$reg[11]},
									{$reg[12]},
									{$reg[13]},
									{$reg[14]},
									{$reg[15]},
									{$reg[16]},
									{$reg[17]},
									{$reg[18]},
									{$reg[19]},
									'{$reg[20]}',
									{$reg[21]},
									{$reg[22]},
									'',
									'{$reg[24]}',
									{$reg[25]},
									{$reg[26]},
									'{$reg[27]}',
									{$reg[28]},
									'{$reg[29]}',
									'{$reg[30]}',
									'{$reg[31]}',
									'{$reg[32]}',
									'{$reg[33]}',
									'{$reg[34]}',
									'{$reg[35]}',
									'{$reg[36]}',
									'{$reg[37]}',
									'{$reg[38]}',
									{$reg[39]},
									NULL
					);
				";

//				echo "\n INSERT Ticket - Boleta Cabecera Detallado: \n".$sql;

				if (mssql_query($sql,$mssql)===FALSE)
					return onError_AIExit($mssql,"Error al trasladar ticket boleta detallado: {$reg[0]}");

			}

		}

		/*******************************************************************************
		* TICKETS FACTURA (pos_trans GB)                                               *
		*******************************************************************************/

		$sql = "
			SELECT * FROM (
				SELECT
					(venta.caja) || (venta.trans)::TEXT AS nro,
					venta.dia::TEXT AS fecha_emision,
					venta.dia::TEXT AS fecha_pago,
					$anno::TEXT AS anno,
					$mes::TEXT AS mes,
					'12'::TEXT AS tipodocu,
					cfp.nu_posz_z_serie::TEXT AS serie,
					venta.trans::TEXT AS numero,
					'6'::TEXT AS tdi,
					FIRST(R.ruc)::TEXT AS ruc,
					FIRST(R.razsocial)::TEXT AS nombre,
					0.00 AS bbii,
					0.00 AS igv,
					'0.00'::TEXT AS bbii0,
					'0.00'::TEXT AS igv0,
					'0.00'::TEXT AS bbii1,
					'0.00'::TEXT AS igv1,
					0.00 AS no_grabada,
					'0.00'::TEXT AS otros,
					0.00 AS total,
					'S'::TEXT AS moneda,
					'1'::TEXT AS tc,
					'0.00'::TEXT AS total_dolar,
					'0.00'::TEXT AS detrac_numero,
					'01/01/1900'::TEXT AS detra_fecha,
					'0.00'::TEXT AS tasa_detra,
					'0.00'::TEXT AS monto_detra,
					'01/01/1900'::TEXT AS ref_fecha,
					'999'::TEXT AS ref_tipo_doc,
					'000'::TEXT AS ref_serie,
					'0000'::TEXT AS ref_numero,
					'VENTA'::TEXT AS observaciones,
					'--'::TEXT AS glosa,
					'0'::TEXT AS campo11,
					'0'::TEXT AS campo12,
					'0'::TEXT AS campo21,
					'0'::TEXT AS campo22,
					'0'::TEXT AS campo31,
					'0'::TEXT AS campo32,
					'40'::TEXT AS diario
				FROM
					{$postrans} venta
					INNER JOIN (
						SELECT 
							(caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
							fecha,
							es||caja||trans AS idticket
						FROM
							{$postrans}
						WHERE
							tm 			= 'A'
							AND es		= '{$CodAlmacen}'
							AND td 		= 'F'
							AND dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
					) AS extorno ON (venta.caja||venta.td||venta.dia||venta.turno||venta.codigo||abs(venta.cantidad)||abs(venta.precio)||abs(venta.igv)||abs(venta.importe)||venta.ruc||venta.tipo||venta.pump||venta.fpago||venta.at||venta.text1||venta.placa||venta.es) = extorno.registro
					AND venta.tm 	= 'V'
					AND venta.es	= '{$CodAlmacen}'
					AND venta.td 	= 'F'
					AND venta.dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
					AND venta.fecha < extorno.fecha
					LEFT JOIN ruc R ON (R.ruc = venta.ruc)
					LEFT JOIN pos_z_cierres cfp ON(venta.es = cfp.ch_sucursal AND venta.dia = cfp.dt_posz_fecha_sistema::DATE AND venta.caja = cfp.ch_posz_pos AND venta.turno::INTEGER = cfp.nu_posturno)
				GROUP BY
					extorno.idticket,
					venta.trans,
					venta.caja,
					venta.dia, 
					cfp.nu_posz_z_serie
				) AS P
				UNION
				(
				SELECT
					(t.caja) || (t.trans)::TEXT AS nro,
					t.dia::TEXT AS fecha_emision,
					t.dia::TEXT AS fecha_pago,
					$anno::TEXT AS anno,
					$mes::TEXT AS mes,
					'12'::TEXT AS tipodocu,
					cfp.nu_posz_z_serie::TEXT AS serie,
					t.trans::TEXT AS numero,
					'6'::TEXT AS tdi,
					FIRST(R.ruc)::TEXT AS ruc,
					FIRST(R.razsocial)::TEXT AS nombre,
					0.00 AS bbii,
					0.00 AS igv,
					'0.00'::TEXT AS bbii0,
					'0.00'::TEXT AS igv0,
					'0.00'::TEXT AS bbii1,
					'0.00'::TEXT AS igv1,
					0.00 AS no_grabada,
					'0.00'::TEXT AS otros,
					0.00 AS total,
					'S'::TEXT AS moneda,
					'1'::TEXT AS tc,
					'0.00'::TEXT AS total_dolar,
					'0.00'::TEXT AS detrac_numero,
					'01/01/1900'::TEXT AS detra_fecha,
					'0.00'::TEXT AS tasa_detra,
					'0.00'::TEXT AS monto_detra,
					'01/01/1900'::TEXT AS ref_fecha,
					'999'::TEXT AS ref_tipo_doc,
					'000'::TEXT AS ref_serie,
					'0000'::TEXT AS ref_numero,
					'VENTA'::TEXT AS observaciones,
					'--'::TEXT AS glosa,
					'0'::TEXT AS campo11,
					'0'::TEXT AS campo12,
					'0'::TEXT AS campo21,
					'0'::TEXT AS campo22,
					'0'::TEXT AS campo31,
					'0'::TEXT AS campo32,
					'40'::TEXT AS diario
				FROM
					{$postrans} t
					LEFT JOIN ruc R ON (R.ruc = t.ruc)
					LEFT JOIN pos_z_cierres cfp ON(t.es = cfp.ch_sucursal AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.caja = cfp.ch_posz_pos AND t.turno::INTEGER = cfp.nu_posturno)
				WHERE
					t.es 		= '{$CodAlmacen}'
					AND t.tm 	= 'A'
					AND t.td 	= 'F'
					AND t.dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
				GROUP BY
					t.trans,
					t.caja,
					t.dia, 
					cfp.nu_posz_z_serie
				)
				UNION
				(
				SELECT
					(t.caja) || (t.trans)::TEXT AS nro,
					t.dia::TEXT AS fecha_emision,
					t.dia::TEXT AS fecha_pago,
					$anno::TEXT AS anno,
					$mes::TEXT AS mes,
					'12'::TEXT AS tipodocu,
					cfp.nu_posz_z_serie::TEXT AS serie,
					t.trans::TEXT AS numero,
					'6'::TEXT AS tdi,
					FIRST(R.ruc)::TEXT AS ruc,
					FIRST(R.razsocial)::TEXT AS nombre,
					(CASE WHEN ROUND(SUM(t.igv), 2) = 0.00 THEN 0.00 ELSE ROUND(SUM(t.importe - t.igv), 2) END) AS bbii,
					(CASE WHEN ROUND(SUM(t.igv), 2) = 0.00 THEN 0.00 ELSE ROUND(SUM(t.igv), 2) END) AS igv,
					'0.00'::TEXT AS bbii0,
					'0.00'::TEXT AS igv0,
					'0.00'::TEXT AS bbii1,
					'0.00'::TEXT AS igv1,
					(CASE WHEN ROUND(SUM(t.igv), 2) = 0.00 THEN ROUND(SUM(t.importe), 2) ELSE 0.00 END) AS no_grabada,
					'0.00'::TEXT AS otros,
					ROUND(SUM(t.importe), 2) AS total,
					'S'::TEXT AS moneda,
					'1'::TEXT AS tc,
					'0.00'::TEXT AS total_dolar,
					'0.00'::TEXT AS detrac_numero,
					'01/01/1900'::TEXT AS detra_fecha,
					'0.00'::TEXT AS tasa_detra,
					'0.00'::TEXT AS monto_detra,
					'01/01/1900'::TEXT AS ref_fecha,
					'999'::TEXT AS ref_tipo_doc,
					'000'::TEXT AS ref_serie,
					'0000'::TEXT AS ref_numero,
					'VENTA'::TEXT AS observaciones,
					'--'::TEXT AS glosa,
					'0'::TEXT AS campo11,
					'0'::TEXT AS campo12,
					'0'::TEXT AS campo21,
					'0'::TEXT AS campo22,
					'0'::TEXT AS campo31,
					'0'::TEXT AS campo32,
					'40'::TEXT AS diario
				FROM
					{$postrans} t
					LEFT JOIN ruc R ON (R.ruc = t.ruc)
					LEFT JOIN pos_z_cierres cfp ON(t.es = cfp.ch_sucursal AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.caja = cfp.ch_posz_pos AND t.turno::INTEGER = cfp.nu_posturno)
				WHERE
					t.es 		= '{$CodAlmacen}'
					AND t.tm 	= 'V'
					AND t.td 	= 'F'
					AND t.dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
					AND t.es||t.caja||t.trans NOT IN (
					SELECT
						LAST(venta.es||venta.caja||venta.trans) co_vtrans
					FROM
						{$postrans} venta
						INNER JOIN (
							SELECT 
								(caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
								fecha,
								es||caja||trans AS idticket
							FROM
								{$postrans}
							WHERE
								tm 			= 'A'
								AND es		= '{$CodAlmacen}'
								AND td  	= 'F'
								AND dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
							) AS extorno ON (venta.caja||venta.td||venta.dia||venta.turno||venta.codigo||abs(venta.cantidad)||abs(venta.precio)||abs(venta.igv)||abs(venta.importe)||venta.ruc||venta.tipo||venta.pump||venta.fpago||venta.at||venta.text1||venta.placa||venta.es) = extorno.registro
							AND venta.tm 	= 'V'
							AND venta.es	= '{$CodAlmacen}'
							AND venta.td 	= 'F'
							AND venta.dia 	BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
							AND venta.fecha < extorno.fecha
					GROUP BY
						extorno.idticket
					)
				GROUP BY
					t.trans,
					t.caja,
					t.dia, 
					cfp.nu_posz_z_serie
				);
		";

//		echo "\nTickets Factura: \n".$sql;

		if ($sqlca->query($sql)<0)
			return onError_AIExit($mssql,"Error al obtener tickets factura a trasladar");

		while ($reg = $sqlca->fetchRow()) {

			$reg[10] = str_replace("'", "''", $reg[10]);

			$sql = "
				INSERT INTO tmp_migracion(
									nro,
									fecha_emision,
									fecha_pago,
									anno,
									mes,
									tipodocu,
									serie,
									numero,
									tdi,
									ruc,
									nombre,
									bbii,
									igv,
									bbii0,
									igv0,
									bbii1,
									igv1,
									no_grabada,
									otros,
									total,
									moneda,
									tc,
									total_dolar,
									detrac_numero,
									detra_fecha,
									tasa_detra,
									monto_detra,
									ref_fecha,
									ref_tipo_doc,
									ref_serie,
									ref_numero,
									observaciones,
									glosa,
									campo11,
									campo12,
									campo21,
									campo22,
									campo31,
									campo32,
									diario,
									estado
				) VALUES (
									{$reg[0]},
									'{$reg[1]}',
									'{$reg[2]}',
									{$reg[3]},
									{$reg[4]},
									{$reg[5]},
									'{$reg[6]}',
									'{$reg[7]}',
									'{$reg[8]}',
									'{$reg[9]}',
									'{$reg[10]}',
									{$reg[11]},
									{$reg[12]},
									{$reg[13]},
									{$reg[14]},
									{$reg[15]},
									{$reg[16]},
									{$reg[17]},
									{$reg[18]},
									{$reg[19]},
									'{$reg[20]}',
									{$reg[21]},
									{$reg[22]},
									'',
									'{$reg[24]}',
									{$reg[25]},
									{$reg[26]},
									'{$reg[27]}',
									{$reg[28]},
									'{$reg[29]}',
									'{$reg[30]}',
									'{$reg[31]}',
									'{$reg[32]}',
									'{$reg[33]}',
									'{$reg[34]}',
									'{$reg[35]}',
									'{$reg[36]}',
									'{$reg[37]}',
									'{$reg[38]}',
									{$reg[39]},
									NULL
				);
			";

//			echo "\n INSERT Tickets Factura: \n".$sql;

			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error al trasladar ticket factura: {$reg[0]}");
		}

		/*******************************************************************************
		* DOCUMENTOS MANUALES DE VENTAS (fac_ta_factura_cabecera)                   *
		*******************************************************************************/

		$sql = "
			SELECT
				fc.ch_fac_numerodocumento AS DocNro,
				fc.dt_fac_fecha AS fecha_emision,
				(CASE WHEN ch_fac_credito = 'S' THEN fc.dt_fac_fecha + fp.tab_num_01::INTEGER ELSE fc.dt_fac_fecha END) AS fecha_pago,
				$anno AS anno,
				$mes AS mes,
				CASE
					WHEN fc.ch_fac_tipodocumento = '10' THEN '01'::text
					WHEN fc.ch_fac_tipodocumento = '35' THEN '03'::text
					WHEN fc.ch_fac_tipodocumento = '11' THEN '08'::text
					WHEN fc.ch_fac_tipodocumento = '20' THEN '07'::text
				END AS tipodocu,
				fc.ch_fac_seriedocumento AS serie,
				fc.ch_fac_numerodocumento AS numero,
				(CASE WHEN fc.ch_fac_tipodocumento = '35' THEN '0' ELSE '6' END) AS tdi,
				(CASE WHEN fc.ch_fac_tipodocumento = '35' THEN '-' ELSE c.cli_ruc END) AS ruc,
				(CASE WHEN fc.ch_fac_tipodocumento = '35' THEN '-' ELSE c.cli_razsocial END) AS nombre,
				(CASE WHEN ch_fac_tiporecargo2 = 'S' THEN 0.00 ELSE 
					(CASE WHEN fc.ch_fac_tipodocumento IN ('11', '20') THEN 
						(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN -fc.nu_fac_valorbruto ELSE ROUND((-fc.nu_fac_valorbruto * fc.nu_tipocambio), 2) END)
					ELSE
						(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN fc.nu_fac_valorbruto ELSE ROUND((fc.nu_fac_valorbruto * fc.nu_tipocambio), 2) END)
					END)
				END) AS bbii,
				(CASE WHEN ch_fac_tiporecargo2 = 'S' THEN 0.00 ELSE 
					(CASE WHEN fc.ch_fac_tipodocumento IN ('11', '20') THEN 
						(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN -fc.nu_fac_impuesto1 ELSE ROUND((-fc.nu_fac_impuesto1 * fc.nu_tipocambio), 2) END)
					ELSE
						(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN fc.nu_fac_impuesto1 ELSE ROUND((fc.nu_fac_impuesto1 * fc.nu_tipocambio), 2) END)
					END)
				END) AS igv,
				'0.00' AS bbii0,
				'0.00' AS igv0,
				'0.00' AS bbii1,
				'0.00' AS igv1,
				(CASE WHEN ch_fac_tiporecargo2 = 'S' THEN 
					(CASE WHEN
						fc.ch_fac_tipodocumento IN ('11', '20') THEN 
						(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN -fc.nu_fac_valortotal ELSE ROUND((-fc.nu_fac_valortotal * fc.nu_tipocambio), 2) END)
					ELSE
						(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN fc.nu_fac_valortotal ELSE ROUND((fc.nu_fac_valortotal * fc.nu_tipocambio), 2) END)
					END)
				ELSE
					0.00
				END) AS no_grabada,
				'0.00' AS otros,
				(CASE WHEN
					fc.ch_fac_tipodocumento IN ('11', '20') THEN 
					(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN -fc.nu_fac_valortotal ELSE ROUND((-fc.nu_fac_valortotal * fc.nu_tipocambio), 2) END)
				ELSE
					(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN fc.nu_fac_valortotal ELSE ROUND((fc.nu_fac_valortotal * fc.nu_tipocambio), 2) END)
				END) AS total,
				(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN 'S' ELSE 'D' END) AS moneda,
				(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN '1' ELSE ROUND(TC.tca_venta_oficial,3) END) AS tc,
				(CASE WHEN fc.ch_fac_moneda = '01' OR fc.ch_fac_moneda = '1' THEN '0.00' ELSE ROUND((fc.nu_fac_valortotal * fc.nu_tipocambio), 2) END) AS total_dolar,
				'0.00' AS detrac_numero,
				'1900-01-01' AS detra_fecha,
				'0.00' AS tasa_detra,
				'0.00' AS monto_detra,
				(CASE WHEN (com.ch_fac_observacion3 = '' OR com.ch_fac_observacion3 IS NULL) THEN '1900-01-01' ELSE TO_CHAR(com.ch_fac_observacion3::DATE, 'YYYY-MM-DD') END) AS rec_fecha,
				(SELECT (CASE WHEN ref_tipo_doc[3] IS NULL THEN '999' ELSE ref_tipo_doc[3] END) FROM (SELECT string_to_array(ch_fac_observacion2, '*') FROM fac_ta_factura_complemento WHERE ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND ch_fac_numerodocumento = fc.ch_fac_numerodocumento AND cli_codigo = fc.cli_codigo) AS dt(ref_tipo_doc)) AS ref_tipo_doc,
				(SELECT (CASE WHEN ref_serie[2] IS NULL THEN '000' ELSE ref_serie[2] END) FROM (SELECT string_to_array(ch_fac_observacion2, '*') FROM fac_ta_factura_complemento WHERE ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND ch_fac_numerodocumento = fc.ch_fac_numerodocumento AND cli_codigo = fc.cli_codigo) AS dt(ref_serie)) AS ref_serie,
				(SELECT (CASE WHEN ref_numero[1] IS NULL THEN '0000' ELSE ref_numero[1] END) FROM (SELECT string_to_array(ch_fac_observacion2, '*') FROM fac_ta_factura_complemento WHERE ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND ch_fac_numerodocumento = fc.ch_fac_numerodocumento AND cli_codigo = fc.cli_codigo) AS dt(ref_numero)) AS ref_numero,
				'--' AS observaciones,
				'--' AS glosa,
				'0' AS campo11,
				'0' AS campo12,
				'0' AS campo21,
				'0' AS campo22,
				'0' AS campo31,
				'0' AS campo32,
				'40' AS diario
			FROM
				fac_ta_factura_cabecera fc
				LEFT JOIN int_tipo_cambio tc ON (tc.tca_fecha = fc.dt_fac_fecha)
				JOIN int_clientes c ON (fc.cli_codigo = c.cli_codigo)
				JOIN int_tabla_general fp ON (fp.tab_tabla = '96' AND fc.ch_fac_forma_pago = substr(fp.tab_elemento,5,2))
				JOIN fac_ta_factura_complemento com ON (fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
			WHERE
				fc.ch_fac_tipodocumento IN ('10','11','20','35')
				AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
				AND fc.ch_almacen = '$CodAlmacen';
		";

//		echo "\n Documentos Manuales: \n".$sql;

		if ($sqlca->query($sql)<0)
			return onError_AIExit($mssql,"Error al obtener cabeceras de documentos manuales a trasladas");

		while ($reg = $sqlca->fetchRow()) {
			$sql = "
				INSERT INTO tmp_migracion(
								nro,
								fecha_emision,
								fecha_pago,
								anno,
								mes,
								tipodocu,
								serie,
								numero,
								tdi,
								ruc,
								nombre,
								bbii,
								igv,
								bbii0,
								igv0,
								bbii1,
								igv1,
								no_grabada,
								otros,
								total,
								moneda,
								tc,
								total_dolar,
								detrac_numero,
								detra_fecha,
								tasa_detra,
								monto_detra,
								ref_fecha,
								ref_tipo_doc,
								ref_serie,
								ref_numero,
								observaciones,
								glosa,
								campo11,
								campo12,
								campo21,
								campo22,
								campo31,
								campo32,
								diario,
								estado
				) VALUES (
								{$reg[0]},
								'{$reg[1]}',
								'{$reg[2]}',
								{$reg[3]},
								{$reg[4]},
								{$reg[5]},
								'{$reg[6]}',
								'{$reg[7]}',
								'{$reg[8]}',
								'{$reg[9]}',
								'{$reg[10]}',
								{$reg[11]},
								{$reg[12]},
								{$reg[13]},
								{$reg[14]},
								{$reg[15]},
								{$reg[16]},
								{$reg[17]},
								{$reg[18]},
								{$reg[19]},
								'{$reg[20]}',
								{$reg[21]},
								{$reg[22]},
								'',
								'{$reg[24]}',
								{$reg[25]},
								{$reg[26]},
								'{$reg[27]}',
								{$reg[28]},
								'{$reg[29]}',
								'{$reg[30]}',
								'{$reg[31]}',
								'{$reg[32]}',
								'{$reg[33]}',
								'{$reg[34]}',
								'{$reg[35]}',
								'{$reg[36]}',
								'{$reg[37]}',
								'{$reg[38]}',
								{$reg[39]},
								NULL
				);
			";

//			echo "\n INSERT Documento Manual: \n".$sql;

			if (mssql_query($sql,$mssql)===FALSE) {
				return onError_AIExit($mssql,"Error al trasladar Documento manual: {$reg[0]}");
			}

		}

		/*******************************************************************************
		* DOCUMENTOS MANUALES DE COMPRAS (cpag_ta_cabecera)                  		   *
		*******************************************************************************/

		$sql = "
			SELECT
				c.pro_cab_numreg AS nro,
				c.pro_cab_fechaemision AS fecha_emision,
				(CASE WHEN
					gen2.tab_car_03 = '14' THEN to_char(c.pro_cab_fechavencimiento, 'DD/MM/YYYY') 
				ELSE
					''
				END) as fecha_pago,
				$anno AS anno,
				$mes AS mes,
				gen2.tab_car_03 tipodocu,
				LPAD(CAST(c.pro_cab_seriedocumento AS bpchar), 4, '0') serie,
				c.pro_cab_numdocumento numero,
				(CASE WHEN
					gen2.tab_car_03 = '01' THEN '6'
				ELSE
					''
				END) AS identidad,
				(CASE WHEN p.pro_ruc IS NULL OR p.pro_ruc = '' THEN c.pro_codigo ELSE p.pro_ruc END) AS ruc,
				p.pro_razsocial AS nombre,
				(CASE WHEN
					gen2.tab_car_03 IN ('07','08') THEN 
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_impafecto ELSE ROUND((-c.pro_cab_impafecto * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_impafecto ELSE ROUND((c.pro_cab_impafecto * c.pro_cab_tcambio), 2) END)
				END) AS bbii,
				(CASE WHEN
					gen2.tab_car_03 IN ('07','08') THEN
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_impto1 ELSE ROUND((-c.pro_cab_impto1 * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_impto1 ELSE ROUND((c.pro_cab_impto1 * c.pro_cab_tcambio), 2) END)
				END) AS igv,
				'0.00' AS bbii0,
				'0.00' AS igv0,
				'0.00' AS bbii1,
				'0.00' AS igv1,
				c.pro_cab_impinafecto AS no_grabada,
				c.regc_sunat_percepcion AS otros,
				(CASE WHEN
					gen2.tab_car_03 IN ('07','08') THEN
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN -c.pro_cab_imptotal ELSE ROUND((-c.pro_cab_imptotal * c.pro_cab_tcambio), 2) END)
				ELSE
					(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN c.pro_cab_imptotal ELSE ROUND((c.pro_cab_imptotal * c.pro_cab_tcambio), 2) END)
				END) AS total,
				(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN 'S' ELSE 'D' END) AS moneda,
				(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN '1' ELSE ROUND(c.pro_cab_tcambio,3) END) AS tc,
				(CASE WHEN c.pro_cab_moneda = '01' OR c.pro_cab_moneda = '1' THEN '0.00' ELSE ROUND((c.pro_cab_imptotal * c.pro_cab_tcambio), 2) END) AS total_dolar,
				(CASE WHEN (SELECT pay_number FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento)) IS NULL THEN '0.00' ELSE (SELECT pay_number FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento)) END) detrac_numero,
				(CASE WHEN (SELECT created FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento)) IS NULL THEN '01/01/1900' ELSE (SELECT created FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento)) END) detra_fecha,
				(CASE WHEN (SELECT pay_number FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento)) IS NULL THEN '0.00' ELSE ROUND(c.pro_cab_tcambio,3) END) AS tasa_detra,
				(CASE WHEN (SELECT amount FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento)) IS NULL THEN '0.00' ELSE (SELECT amount FROM c_cash_transaction_payment WHERE c_cash_mpayment_id = '8' AND c_cash_transaction_id IN (SELECT c_cash_transaction_id FROM c_cash_transaction_detail WHERE doc_type = d.pro_cab_tipdocumento AND doc_serial_number = d.pro_cab_seriedocumento AND doc_number = d.pro_cab_numdocumento)) END) monto_detra,
				(CASE WHEN (SELECT to_char(nc.pro_cab_fechaemision, 'DD/MM/YYYY') FROM cpag_ta_cabecera nc WHERE nc.pro_codigo = c.pro_codigo AND nc.pro_cab_tipdocumento = c.pro_cab_tipdocreferencia AND nc.pro_cab_seriedocumento||nc.pro_cab_numdocumento = c.pro_cab_numdocreferencia) IS NULL THEN '01/01/1900' ELSE (SELECT to_char(nc.pro_cab_fechaemision, 'DD/MM/YYYY') FROM cpag_ta_cabecera nc WHERE nc.pro_codigo = c.pro_codigo AND nc.pro_cab_tipdocumento = c.pro_cab_tipdocreferencia AND nc.pro_cab_seriedocumento||nc.pro_cab_numdocumento = c.pro_cab_numdocreferencia) END) ref_fecha,
				(CASE WHEN gen.tab_car_03 IS NULL THEN '999' ELSE gen.tab_car_03 END) ref_tipo_doc,
				(CASE WHEN substr(c.pro_cab_numdocreferencia,1,4) IS NULL THEN '000' ELSE substr(c.pro_cab_numdocreferencia,1,4) END) ref_serie, 
				(CASE WHEN substr(c.pro_cab_numdocreferencia,5,8) IS NULL THEN '0000' ELSE substr(c.pro_cab_numdocreferencia,5,8) END) ref_numero,
				'--' AS observaciones,
				'--' AS glosa,
				'0' AS campo11,
				'0' AS campo12,
				'0' AS campo21,
				'0' AS campo22,
				'0' AS campo31,
				'0' AS campo32,
				'30' AS diario
			FROM
				cpag_ta_cabecera AS c
				INNER JOIN cpag_ta_detalle AS d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
				JOIN int_proveedores AS p ON (c.pro_codigo = p.pro_codigo)
				LEFT JOIN int_tabla_general AS gen ON((CASE WHEN c.pro_cab_tipdocumento = '20' THEN c.pro_cab_tipdocreferencia = substring(TRIM(tab_elemento) for 2 FROM length(TRIM(tab_elemento))-1) END) AND tab_tabla = '08')
				LEFT JOIN int_tabla_general as gen2 ON((c.pro_cab_tipdocumento = substring(TRIM(gen2.tab_elemento) for 2 from length(TRIM(gen2.tab_elemento))-1) and gen2.tab_tabla ='08'))
			WHERE
				c.pro_cab_almacen 					= '$CodAlmacen'
				AND c.pro_cab_fechaemision::DATE 	BETWEEN '$FechaIni' AND '$FechaFin';
		";

		//echo "\n Documentos Compra: \n".$sql;

		if ($sqlca->query($sql)<0)
			return onError_AIExit($mssql,"Error al obtener movimientos de almacen a trasladar");

		while ($reg = $sqlca->fetchRow()) {
			$sql = "
				INSERT INTO tmp_migracion(
								nro,
								fecha_emision,
								fecha_pago,
								anno,
								mes,
								tipodocu,
								serie,
								numero,
								tdi,
								ruc,
								nombre,
								bbii,
								igv,
								bbii0,
								igv0,
								bbii1,
								igv1,
								no_grabada,
								otros,
								total,
								moneda,
								tc,
								total_dolar,
								detrac_numero,
								detra_fecha,
								tasa_detra,
								monto_detra,
								ref_fecha,
								ref_tipo_doc,
								ref_serie,
								ref_numero,
								observaciones,
								glosa,
								campo11,
								campo12,
								campo21,
								campo22,
								campo31,
								campo32,
								diario,
								estado
				) VALUES (
								{$reg[0]},
								'{$reg[1]}',
								'{$reg[2]}',
								{$reg[3]},
								{$reg[4]},
								{$reg[5]},
								'{$reg[6]}',
								'{$reg[7]}',
								'{$reg[8]}',
								'{$reg[9]}',
								'{$reg[10]}',
								{$reg[11]},
								{$reg[12]},
								{$reg[13]},
								{$reg[14]},
								{$reg[15]},
								{$reg[16]},
								{$reg[17]},
								{$reg[18]},
								{$reg[19]},
								'{$reg[20]}',
								{$reg[21]},
								{$reg[22]},
								'',
								'{$reg[24]}',
								{$reg[25]},
								{$reg[26]},
								'{$reg[27]}',
								{$reg[28]},
								'{$reg[29]}',
								'{$reg[30]}',
								'{$reg[31]}',
								'{$reg[32]}',
								'{$reg[33]}',
								'{$reg[34]}',
								'{$reg[35]}',
								'{$reg[36]}',
								'{$reg[37]}',
								'{$reg[38]}',
								{$reg[39]},
								NULL
				);
			";

			//echo "\n INSERT Documento compra: \n".$sql;

			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error al trasladar documento de compra: {$reg[0]}");

		}

		$sqlca->query("COMMIT;");

		mssql_query("COMMIT TRANSACTION;",$mssql);

		mssql_close($mssql);

		return TRUE;

	}

	function BuscarData($Parametros, $fechaini, $fechafin){
		require_once("/sistemaweb/include/mssqlemu.php");

		$day	= substr($fechaini,0,2);
		$month	= substr($fechaini,3,2);
		$year	= substr($fechaini,6,4);

		$fechaini = $year."-".$month."-".$day;

		$day	= substr($fechafin,0,2);
		$month	= substr($fechafin,3,2);
		$year	= substr($fechafin,6,4);

		$fechafin = $year."-".$month."-".$day;

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);

		if ($mssql === FALSE)
			return "Error al conectarse a la base de datos del QUIPU";

		mssql_select_db($MSSQLDBName, $mssql);

		$sql = "
			SELECT
				TIPODOCU,
				SERIE,
				NUMERO,
				CONVERT(VARCHAR(10), FECHA_EMISION, 103),
				BBII,
				IGV,
				NO_GRABADA,
				OTROS,--PERCEPCION
				TOTAL + OTROS,
				(CASE
					WHEN TIPODOCU = 12 AND DIARIO = 40 THEN 'T'--TICKET
					WHEN TIPODOCU != 12 AND DIARIO = 40 THEN 'V'--VENTA MANUAL
					WHEN TIPODOCU != 12 AND DIARIO = 30 THEN 'C'--COMPRA
				END) TIPOVENTA,
				(CASE
					WHEN TIPODOCU = 12 AND DIARIO = 40 THEN 'TICKETS'
					WHEN TIPODOCU != 12 AND DIARIO = 40 THEN 'DOCUMENTOS MANUALES DE VENTAS'
					WHEN TIPODOCU != 12 AND DIARIO = 30 THEN 'DOCUMENTOS MANUALES DE COMPRAS'
				END) NOTIPOVENTA,
				ruc,
				nombre
			FROM
				tmp_migracion
			WHERE
				fecha_emision BETWEEN '$fechaini' AND '$fechafin'
			ORDER BY
				DIARIO ASC,
				TIPODOCU ASC,
				NRO ASC;
		";

		$res = mssql_query($sql, $mssql);

		if ($res === FALSE)
			return "Error execute query of table tmp_migracion";
	
		$data 	= array();
		$i 		= 0;

		while ($row = mssql_fetch_row($res)){
			$data[$i]['nutd'] 			= $row[0];
			$data[$i]['nuserie'] 		= $row[1];
			$data[$i]['nudocumento'] 	= $row[2];
			$data[$i]['femision']		= $row[3];
			$data[$i]['nubi'] 			= $row[4];
			$data[$i]['nuigv'] 			= $row[5];
			$data[$i]['nuexonerada']	= $row[6];
			$data[$i]['nupercepcion'] 	= $row[7];
			$data[$i]['nutotal'] 		= $row[8];
			$data[$i]['nutv'] 			= $row[9];
			$data[$i]['notipoventa'] 	= $row[10];
			$data[$i]['nuruc'] 			= $row[11];
			$data[$i]['norazsocial'] 	= $row[12];
			$i++;
		}
		return $data;
	}
}
