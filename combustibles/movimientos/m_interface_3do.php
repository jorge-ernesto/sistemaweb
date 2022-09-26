<?php

function os_mssql_escape($str) {
	return str_replace("'","''",$str);
}

function onError_AIExit($mssql,$msg) {
	error_log("onError_AIExit");
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

class Interface3DOModel extends Model {

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

	function obtenerParametros() {
		global $sqlca;

		//$defaultparams = Array("190.223.76.109:1433","opencomb","cix","GRUPO");

		$sql = "	SELECT
					p1.par_valor,
					p2.par_valor,
					p3.par_valor,
					p4.par_valor
				FROM
					int_parametros p1
					LEFT JOIN int_parametros p2 ON p2.par_nombre = '3do_username'
					LEFT JOIN int_parametros p3 ON p3.par_nombre = '3do_password'
					LEFT JOIN int_parametros p4 ON p4.par_nombre = '3do_dbname'
				WHERE
					p1.par_nombre = '3do_server'";
		//echo $sql;
		if ($sqlca->query($sql) < 0)
			return $defaultparams;

		if ($sqlca->numrows() != 1)
			return $defaultparams;

		$reg = $sqlca->fetchRow();

		return Array($reg[0],$reg[1],$reg[2],$reg[3]);
	}

	function actualizarParametros($server,$user,$pass,$dbname) {
		global $sqlca;

		if ($sqlca->query("UPDATE int_parametros SET par_valor = '{$server}' WHERE par_nombre = '3do_server'") < 0)
			return FALSE;
		if ($sqlca->query("UPDATE int_parametros SET par_valor = '{$user}' WHERE par_nombre = '3do_username'") < 0)
			return FALSE;
		if ($sqlca->query("UPDATE int_parametros SET par_valor = '{$pass}' WHERE par_nombre = '3do_password'") < 0)
			return FALSE;
		if ($sqlca->query("UPDATE int_parametros SET par_valor = '{$dbname}' WHERE par_nombre = '3do_dbname'") < 0)
			return FALSE;

		return TRUE;
	}

	function ActualizarInterfaces($Parametros,$FechaIni,$FechaFin,$CodAlmacen, $agrupado) { //ActualizarInterfaces
        require_once("/sistemaweb/include/mssqlemu.php");

		/*** Agregado 2020-01-22 ***/
		echo "<script>console.log('Parametros: " . json_encode($Parametros) . "')</script>";
		echo "<script>console.log('FechaIni: " . json_encode($FechaIni) . "')</script>";
		echo "<script>console.log('FechaFin: " . json_encode($FechaFin) . "')</script>";
		echo "<script>console.log('CodAlmacen: " . json_encode($CodAlmacen) . "')</script>";
		echo "<script>console.log('agrupado: " . json_encode($agrupado) . "')</script>";
		echo "<script>console.log('Cod3DOCliVarios: 1049')</script>";
		/***/

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

		/*** Agregado 2020-01-22 ***/
		echo "<script>console.log('FechaIni: " . json_encode($FechaIni) . "')</script>";
		echo "<script>console.log('FechaFin: " . json_encode($FechaFin) . "')</script>";
		echo "<script>console.log('postrans: " . json_encode($postrans) . "')</script>";
		/***/

		if (("pos_trans".$FechaDiv[2].$FechaDiv[1]) != $postrans) {
			return "INVALID_DATE";
		}

		if (strlen($FechaIni) < 10 || strlen($FechaFin) < 10) {
			return "Fecha no valida. Debe ser ingresada en formato DD/MM/YYYY, completando con ceros.";
		}

		/*** Agregado 2020-01-22 ***/
		$sql = "SELECT c.id_consolidacion FROM pos_consolidacion c JOIN pos_aprosys a ON (c.dia = a.da_fecha AND c.turno = (a.ch_posturno - 1)) WHERE c.dia = '{$FechaFin}' AND c.estado = '1' AND a.ch_poscd = 'S' AND c.almacen = '{$CodAlmacen}';";
		echo "<pre> Query 1:";
		print_r($sql);
		echo "</pre>";
		/***/

		if ($sqlca->query("SELECT c.id_consolidacion FROM pos_consolidacion c JOIN pos_aprosys a ON (c.dia = a.da_fecha AND c.turno = (a.ch_posturno - 1)) WHERE c.dia = '{$FechaFin}' AND c.estado = '1' AND a.ch_poscd = 'S' AND c.almacen = '{$CodAlmacen}';") < 1)
			return "Fecha de fin no consolidada. Debe consolidar fecha de fin para migrar informacion.";

		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);
		// error_log("mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass)");

		if ($mssql===FALSE) {
			// error_log("Error al conectarse a la base de datos del 3DO");
			return "Error al conectarse a la base de datos del 3DO";
		}

		// error_log("mssql_connect");
		// error_log($mssql);
		// error_log(json_encode($mssql));

		// if (function_exists('mssql_connect')) {
		// 	error_log("Las funciones de mssql_connect estan disponibles");
		// } else {
		// 	error_log("Las funciones de mssql_connect no estan disponibles");
		// }
		// die();

		mssql_select_db($MSSQLDBName,$mssql);

		$Almacenes = Array();
		$res = mssql_query("SELECT centrocosto,CodigoAlmacen FROM Almacen WHERE centrocosto IS NOT NULL AND centrocosto != ''",$mssql);
		if ($res===FALSE)
			return "Error interno en base de datos 3DO (1)";
		while ($row = mssql_fetch_row($res))
			$Almacenes[$row[0]] = $row[1];
		$Cod3DOAlmacen = $Almacenes[$CodAlmacen];
		/*?><script>alert("<?php echo '+++ la campania es: '.$Cod3DOAlmacen ; ?> ");</script><?php*/
		if ($Cod3DOAlmacen == NULL || $Cod3DOAlmacen == "")
			return "No se pudo asociar el almacen Opensoft con un almacen 3DO.";

		/*$res = mssql_query("EXECUTE sp_OpenComb_validaEliminacion '" . $FechaIni . "','" . $FechaFin . "','" . $Cod3DOAlmacen . "','OPENCOMB'",$mssql);
		if ($res===FALSE)
			return "Error al validar la migracion de datos";
		$row = mssql_fetch_row($res);
		if ($row[0] !== NULL)
			return "La infomacion ya fue migrada al 3DO anteriormente";*/

/*		$res = mssql_query("SELECT idCliente FROM Clientes WHERE DocIde='00000000'",$mssql);
		if ($res===FALSE)
			return "Error interno en base de datos 3DO (2)";
		$row = mssql_fetch_row($res);
		$Cod3DOCliVarios = $row[0];
		if ($Cod3DOCliVarios == NULL || $Cod3DOCliVarios == "")
			return "No se pudo asociar Clientes Varios en base de datos 3DO";*/
		$Cod3DOCliVarios = "1049";

		// error_log("Cod3DOAlmacen");
		// error_log(json_encode($Almacenes));
		// die();

		mssql_query("BEGIN TRANSACTION;",$mssql);
		$sqlca->query("BEGIN;");
		$sqlca->query("LOCK TABLE \"3do_migraciones\" IN ACCESS EXCLUSIVE MODE");

		/*** Agregado 2020-01-22 ***/
		$sql = "SELECT 1 FROM \"3do_migraciones\" WHERE ('$FechaIni' BETWEEN fecha_inicio AND fecha_fin OR '$FechaFin' BETWEEN fecha_inicio AND fecha_fin) AND ch_almacen = '$CodAlmacen'";
		echo "<pre> Query 2:";
		print_r($sql);
		echo "</pre>";
		/***/

		if ($sqlca->query("SELECT 1 FROM \"3do_migraciones\" WHERE ('$FechaIni' BETWEEN fecha_inicio AND fecha_fin OR '$FechaFin' BETWEEN fecha_inicio AND fecha_fin) AND ch_almacen = '$CodAlmacen'")<0)
			return onError_AIExit($mssql,"No se pudo consultar el historial de migraciones.");

		if ($sqlca->numrows() != 0)
			return onError_AIExit($mssql,"El rango de fechas seleccionadas coincide con una migracion anterior.");

		$ObsVal = "%%*OPENSOFT_WORKING_ES$CodAlmacen*%%";

/*******************************************************************************
* Clientes por RUC (pos_trans / ruc)                                           *
*******************************************************************************/
		echo "========== SINCRONIZANDO CLIENTES ==========\n";
		$Clientes = Array();
		$res = mssql_query("SELECT RUC, idCliente FROM Clientes",$mssql);
		if ($res===FALSE) {
			return "Error al obtener los clientes";
		}
		while ($row = mssql_fetch_row($res))
			$Clientes[trim($row[0])] = trim($row[1]);

		mssql_free_result($res);

		$sql ="	SELECT
				q.RUC,
				max(q.RazSocial)
			FROM
				((
					SELECT
						trim(t.ruc) AS RUC,
						max(COALESCE(r.razsocial,trim(t.ruc))) AS RazSocial
					FROM
						$postrans t
						LEFT JOIN ruc r ON (trim(t.ruc) = r.ruc)
					WHERE
						td IN ('B','F')
						AND t.ruc IS NOT NULL AND t.ruc != ''
						AND t.es = '$CodAlmacen'
						AND t.dia BETWEEN '$FechaIni 00:00:00' AND '$FechaFin 23:59:59'
					GROUP BY
						1
				) UNION (
					SELECT
						trim(c.cli_ruc) AS RUC,
						max(trim(c.cli_razsocial)) AS RazSocial
					FROM
						fac_ta_factura_cabecera fc
						JOIN int_clientes c ON fc.cli_codigo = c.cli_codigo
					WHERE
						fc.cli_codigo != '9999'
						AND fc.ch_fac_tipodocumento IN ('10','11','20','35')
						AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
						AND fc.ch_almacen = '$CodAlmacen'
					GROUP BY
						1
				)) q
			GROUP BY
				1";

		//echo $sql;

		if ($sqlca->query($sql)<0)
			return OnError_AIExit($mssql,"Error al consultar los clientes a migrar");

		while ($reg = $sqlca->fetchRow())
			if (!isset($Clientes[$reg[0]])) {

				echo "INSERT INTO Clientes (idCliente,Razon,RUC,Notificar,PorcArt,Precio) VALUES ('{$reg[0]}','{$reg[1]}','{$reg[0]}',0,0,0);\n";

				if($reg[0] == '0000000000'){
					echo "--";
				}else{
					if (mssql_query("INSERT INTO Clientes (idCliente,Razon,RUC,Notificar,PorcArt,Precio) VALUES ('" . substr($reg[0],0,10) . "','" . os_mssql_escape($reg[1]) . "','{$reg[0]}',0,0,0);",$mssql)==FALSE)
						return OnError_AIExit($mssql,"Error al insertar el cliente RUC {$reg[0]} en 3DO");
				}
				$Clientes[$reg[0]] = substr($reg[0],0,10);
			}
//		$Clientes = NULL;

/*******************************************************************************
* ARTICULOS (int_articulos)                                                    *
*******************************************************************************/

		$Articulos = Array();
		$res = mssql_query("SELECT Codigo FROM Articulos",$mssql);
		if ($res===FALSE) {
			return "Error al obtener articulos existentes en 3DO";
		}
		while ($row = mssql_fetch_row($res))
			$Articulos[trim($row[0])] = trim($row[0]);

		mssql_free_result($res);

		$sql = "SELECT
				x.art_codigo,
				max(x.art_descripcion)
			FROM ((
				SELECT
					DISTINCT trim(m.art_codigo) AS art_codigo,
					a.art_descripcion AS art_descripcion
				FROM
					inv_movialma m
					JOIN int_articulos a ON m.art_codigo = a.art_codigo
				WHERE
				m.mov_fecha BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
				AND '$CodAlmacen' IN (m.mov_almaorigen,m.mov_almadestino)
			) UNION (
				SELECT
					DISTINCT trim(t.codigo) AS art_codigo,
					a.art_descripcion AS art_descripcion
				FROM
					$postrans t
					JOIN int_articulos a ON t.codigo = a.art_codigo
				WHERE
					t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
					AND es = '$CodAlmacen')
			) x
			GROUP BY
				1;";


		if ($sqlca->query($sql)<0)
			return OnError_AIExit($mssql,"Error al seleccionar articulos de tickets a migrar");
			
echo "========== SINCRONIZANDO ARTICULOS DE TICKETS ==========\n";
		while ($reg = $sqlca->fetchRow())
			if (!isset($Articulos[$reg[0]])) {
				if (mssql_query("INSERT INTO Articulos (Codigo,Descripcion,Unidad1,CantUn1,Unidad2,CantUn2,Precio2,Unidad3,CantUn3,Precio3,Unidad,CantidadEnMano,PrecioLista,Pack,Cajas,pos,NoComision,NoGuia,Estado,CantidadEnProceso,IdModelo,ConSerie,Cod_moneda,N_Cuenta,Afecto,Compuesto,Unidad4,CantUn4,Precio4,Unidad5,CantUn5,Precio5,StkMin,TipArti,Lote,StkMax,CtaCompras,N_Cuenta2,PrecioB1,PrecioB2,PrecioB3,PrecioB4,PrecioB5,PrecioC1,PrecioC2,PrecioC3,PrecioC4,PrecioC5,PrecioD1,PrecioD2,PrecioD3,PrecioD4,PrecioD5,IdGrupArt,PorArancel,Merma,CTACTE) VALUES ('{$reg[0]}','" . addslashes_mssql($reg[1]) . "','UND',0,null,0,0,null,0,0,1,null,0,0,0,0,0,1,1,0,256,0,'001','7010101',1,0,null,0,0,null,0,0,0,0,0,0,'','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);",$mssql)==FALSE)
					return OnError_AIExit($mssql,"Error al insertar articulo ticket codigo {$reg[0]} en 3DO");
				$Articulos[$reg[0]] = $reg[0];
			}

		$sql ="	SELECT
				DISTINCT trim(fd.art_codigo) AS Codigo,
				fd.ch_art_descripcion AS Descripcion
			FROM
				fac_ta_factura_detalle fd
				JOIN fac_ta_factura_cabecera fc ON (fd.ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND fd.ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND fd.ch_fac_numerodocumento = fc.ch_fac_numerodocumento)
				JOIN int_articulos a ON fd.art_codigo = a.art_codigo
			WHERE
				fc.ch_fac_tipodocumento IN ('10','11','20','35')
				AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
				AND fc.ch_almacen = '$CodAlmacen';";

		if ($sqlca->query($sql)<0)
			return OnError_AIExit($mssql,"Error al seleccionar articulos de documentos manuales a migrar");

echo "========== SINCRONIZANDO ARTICULOS DE DOCUMENTOS MANUALES ==========\n";

		while ($reg = $sqlca->fetchRow())
			if (!isset($Articulos[$reg[0]]))
				if (mssql_query("INSERT INTO Articulos (Codigo,Descripcion,Unidad1,CantUn1,Unidad2,CantUn2,Precio2,Unidad3,CantUn3,Precio3,Unidad,CantidadEnMano,PrecioLista,Pack,Cajas,pos,NoComision,NoGuia,Estado,CantidadEnProceso,IdModelo,ConSerie,Cod_moneda,N_Cuenta,Afecto,Compuesto,Unidad4,CantUn4,Precio4,Unidad5,CantUn5,Precio5,StkMin,TipArti,Lote,StkMax,CtaCompras,N_Cuenta2,PrecioB1,PrecioB2,PrecioB3,PrecioB4,PrecioB5,PrecioC1,PrecioC2,PrecioC3,PrecioC4,PrecioC5,PrecioD1,PrecioD2,PrecioD3,PrecioD4,PrecioD5,IdGrupArt,PorArancel,Merma,CTACTE) VALUES ('{$reg[0]}','" . addslashes_mssql($reg[1]) . "','UND',0,null,0,0,null,0,0,1,null,0,0,0,0,0,1,1,0,256,0,'001','7010101',1,0,null,0,0,null,0,0,0,0,0,0,'','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);",$mssql)==FALSE)
					return OnError_AIExit($mssql,"Error al insertar articulo documento codigo {$reg[0]} en 3DO");
//				echo "INSERT INTO Articulos (Codigo,Descripcion,Unidad1,CantUn1,Unidad2,CantUn2,Precio2,Unidad3,CantUn3,Precio3,Unidad,CantidadEnMano,PrecioLista,Pack,Cajas,pos,NoComision,NoGuia,Estado,CantidadEnProceso,IdModelo,ConSerie,Cod_moneda,N_Cuenta,Afecto,Compuesto,Unidad4,CantUn4,Precio4,Unidad5,CantUn5,Precio5,StkMin,TipArti,Lote,StkMax,CtaCompras,N_Cuenta2,PrecioB1,PrecioB2,PrecioB3,PrecioB4,PrecioB5,PrecioC1,PrecioC2,PrecioC3,PrecioC4,PrecioC5,PrecioD1,PrecioD2,PrecioD3,PrecioD4,PrecioD5,IdGrupArt,PorArancel,Merma,CTACTE) VALUES ('{$reg[0]}','" . addslashes_mssql($reg[1]) . "','UND',0,null,0,0,null,0,0,1,null,0,0,0,0,0,1,1,0,256,0,'001','7010101',1,0,null,0,0,null,0,0,0,0,0,0,'','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);\n";

		$Articulos = NULL;

if ($agrupado == 'S') {

/*******************************************************************************
* TICKETS BOLETA ACUMULADOS (pos_trans)                                        *
*******************************************************************************/

		$sql = "SELECT
				lpad(t.caja,4,'0') || '-' || lpad(min(t.trans)::text,8,'0') || '-' || lpad(max(t.trans)::text,8,'0') AS DocNro,
				'{$Cod3DOCliVarios}' AS idCliente,
				t.dia AS fecha,
				sum(t.importe)-sum(t.igv) AS SubTotal,
				sum(t.igv) AS IGV,
				sum(t.importe) AS Total,
				'03'::text AS TipoDoc,
				min(t.dia) AS fechadoc,
				max(t.dia) AS Vencimiento,
				max(t.caja) AS Sucursal,
				cfp.nu_posz_z_serie as serie,
				'CONTADO' AS forma_pago_origen,
				
				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT 
																						CASE 
																							WHEN FIRST(td) = 'B' AND FIRST(tm) = 'V' THEN '03'::text
																							WHEN FIRST(td) = 'F' AND FIRST(tm) = 'V' THEN '01'::text
																							ELSE '10'::text
																						END
																					 FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1) 
					ELSE NULL
				END AS ref_tipodoc,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT SUBSTR(TRIM(usr), 0, 5) FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1) 
					ELSE NULL
				END AS ref_serie,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT SUBSTR(TRIM(usr), 6)    FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1)
					ELSE NULL
				END AS ref_numero,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT dia                     FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1)
					ELSE NULL
				END AS ref_fecha_doc,
				
				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN '01'
					ELSE NULL
				END AS ref_tiponota
			FROM
				{$postrans} t
				LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
			WHERE
				td = 'B'
				AND t.dia BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
				AND t.es = '{$CodAlmacen}'
				AND (t.usr IS NULL OR t.usr = '')
			GROUP BY
				t.dia,
				t.caja,
				cfp.nu_posz_z_serie
			ORDER BY
				t.dia,
				t.caja";

		// echo "<pre> Tickets Boleta Cabecera Agrupados:";
		// print_r($sql);
		// echo "</pre>";

		// echo "\nTickets Boleta Cabecera Agrupados:\n".$sql;

		if ($sqlca->query($sql)<0)
			return onError_AIExit($mssql,"Error al obtener cabaceras de tickets boleta acumulados a trasladar");

		while ($reg = $sqlca->fetchRow()) {
			$sql ="	INSERT INTO
					tmpheadback
				(
					DocNro,
					idCliente,
					idVendedor,
					Fecha,
					Pedido,
					SubTotal,
					IGV,
					ImpAd,
					Total,
					TC,
					Tipodoc,
					DocRef,
					Cod_Moneda,
					doc,
					gui,
					fechadoc,
					Usuario,
					Almacen,
					Observaciones,
					Semana,
					Transf,
					Vencimiento,
					IdGrup,
					IdTipo,
					MtoInafecto,
					DocCierre,
					Sucursal,
					Direccion,
					Descuento,
					forma_pago_origen,
					ref_tipodoc,
					ref_serie,
					ref_numero,
					ref_fecha_doc,
					ref_tiponota
				) VALUES (
					'{$reg[0]}',
					'{$reg[1]}',
					'0',
					convert(datetime, '".substr($reg[2],0,19)."', 120),
					'0',
					{$reg[3]},
					{$reg[4]},
					0,
					{$reg[5]},
					2.85,
					{$reg[6]},
					null,
					'001',
					1,
					0,
					convert(datetime, '".substr($reg[7],0,19)."', 120),
					'OPENCOMB',
					{$Cod3DOAlmacen},
					'Interfase Sistema OpenComb',
					1,
					0,
					convert(datetime, '".substr($reg[8],0,19)."', 120),
					145,
					56,
					0,
					'{$reg[10]}',
					'{$reg[9]}',
					'',
					0,
					'{$reg[11]}',
					'{$reg[12]}',
					'{$reg[13]}',
					'{$reg[14]}',
					convert(datetime, '".substr($reg[15],0,19)."', 120),
					'{$reg[16]}'
				);";

			//echo $sql."\n";

			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error al trasladar cabecera de ticket boleta acumulado {$reg[0]} al 3DO");

		}

		echo "\n-------------ENTRO A BOLETAS--------\n";

		$sql = "SELECT
				trim(t.codigo) AS Codigo,
				sum(t.cantidad) AS Cantidad,
				substring(first(a.art_unidad) from 4) AS Unidad,
				avg(t.precio) AS Precio,
				first(a.art_descripcion) AS Descripcion,
				first(t.trans) trans,
				t.caja caja,
				first(B.rango) rango,
				(sum(t.cantidad) * (avg(t.precio) / 1.18)) parcial,
				first(t.dia) dia,
				first(t.fecha) fecha
			FROM
				$postrans t
				JOIN int_articulos a ON (t.codigo = a.art_codigo)

				JOIN
				(
					SELECT
						min(t.trans) AS minimo,
						max(t.trans) AS maximo,
						t.caja AS caja,
						lpad(t.caja,4,'0') || '-' || lpad(min(t.trans)::text,8,'0') || '-' || lpad(max(t.trans)::text,8,'0') AS rango
					FROM
						$postrans t
						LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
					WHERE
						td = 'B'
						AND t.dia BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
						AND t.es = '{$CodAlmacen}'
						AND (t.usr IS NULL OR t.usr = '')
					GROUP BY
						t.dia,
						t.caja

				) AS B

				ON trans BETWEEN B.minimo AND B.maximo AND t.caja = B.caja

			WHERE
				td = 'B'
				AND t.dia BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
				AND t.es = '{$CodAlmacen}'
				AND (t.usr IS NULL OR t.usr = '')
			GROUP BY
				t.caja,
				t.codigo
			ORDER BY
				rango,
				t.codigo;";

			echo "\nTickets Boleta Detalle:\n".$sql;

			if ($sqlca->query($sql,"detalle")<0)
				return onError_AIExit($mssql,"Error al seleccionar detalle de tickets boleta acumulados a trasladar");

			while ($rex = $sqlca->fetchRow("detalle")) {

				$resql = mssql_query("SELECT NumeroOperacion FROM tmpheadback WHERE Sucursal='{$rex[6]}' AND DocNro='{$rex[7]}'",$mssql);

			if ($resql===FALSE)
				return onError_AIExit($mssql,"Error asociar NumeroOperacion al detalle de ticket boleta acumulado {$rex[7]}");

			$rresgistro = mssql_fetch_row($resql);

			mssql_free_result($resql);

			$currentIdentity = $rresgistro[0];

			$sql="
				INSERT INTO
					tmpbodyback(
						DocNro,
						Secuencia,
						Fecha,
						Codigo,
						Cantidad,
						Unidad,
						Precio,
						Parcial,
						DocRef,
						doc,
						gui,
						Codigoalmacen,
						NumeroOperacion,
						fechaDoc,
						idTipoMovimiento,
						Saldo,
						Costo,
						CodInterno,
						TC,
						Inafecto,
						PorcDesc,
						Grupo,
						Cant,
						DesGrupo,
						CantOrig,
						Descripcion
					) VALUES (
						'{$rex[7]}',
						null,
						convert(datetime, '".substr($rex[9],0,19)."', 120),
						'{$rex[0]}',
						{$rex[1]},
						'{$rex[2]}',
						{$rex[3]},
						{$rex[8]},
						null,
						1,
						0,
						'{$Cod3DOAlmacen}',
						{$currentIdentity},
						convert(datetime, '".substr($rex[10],0,19)."', 120),
						2,
						0,
						0,
						0,
						2.85,
						0,
						0,
						'',
						0,
						'',
						1,
						'{$rex[4]}'
					);";

			echo "\n--".$sql."--\n";

			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error 1 al trasladar detalle de ticket BOLETA AGRUPADO {$reg[0]} articulo {$reg[2]}");
		}


}else{

/*******************************************************************************
* TICKETS BOLETAS DETALLADO (pos_trans)                                        *
*******************************************************************************/

		$sql = "SELECT
				lpad(max(t.caja),4,'0'::text) || '-' || lpad(to_char(t.trans,'FM9999999999'),8,'0') AS DocNro,
				'{$Cod3DOCliVarios}' AS idCliente,
				t.dia AS fecha,
				sum(t.importe)-sum(t.igv) AS SubTotal,
				sum(t.igv) AS IGV,
				sum(t.importe) AS Total,
				'03'::text AS TipoDoc,
				min(t.dia) AS fechadoc,
				max(t.dia) AS Vencimiento,
				max(t.caja) AS Sucursal,
				cfp.nu_posz_z_serie as serie,
				FIRST(t.fecha) fechaActual,
				trim(FIRST(t.codigo)) AS Codigo,
				sum(t.cantidad) AS Cantidad,
				substring(FIRST(a.art_unidad) from 4) AS Unidad,
				sum(t.precio) AS Precio,
				FIRST(a.art_descripcion) AS Descripcion,
				(sum(t.cantidad) * (avg(t.precio) / 1.18)) parcial,
				'CONTADO' AS forma_pago_origen,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT 
																							CASE 
																								WHEN FIRST(td) = 'B' AND FIRST(tm) = 'V' THEN '03'::text
																								WHEN FIRST(td) = 'F' AND FIRST(tm) = 'V' THEN '01'::text
																								ELSE '10'::text
																							END
																					 FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1) 
					ELSE NULL
				END AS ref_tipodoc,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT SUBSTR(TRIM(usr), 0, 5) FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1) 
					ELSE NULL
				END AS ref_serie,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT SUBSTR(TRIM(usr), 6)    FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1)
					ELSE NULL
				END AS ref_numero,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT dia                    FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1)
					ELSE NULL
				END AS ref_fecha_doc,
				
				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN '01'
					ELSE NULL
				END AS ref_tiponota
			FROM
				{$postrans} t
				LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
				JOIN int_articulos a ON (t.codigo = a.art_codigo)
			WHERE
				td = 'B'
				AND t.dia BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
				AND t.es = '{$CodAlmacen}'
				AND (t.usr IS NULL OR t.usr = '')
			GROUP BY
				t.trans,
				t.dia,
				t.caja, 
				cfp.nu_posz_z_serie
			ORDER BY
				t.trans,
				t.dia,
				t.caja";

		// echo "<pre> Tickets Boleta Cabecera Detallado:";
		// print_r($sql);
		// echo "</pre>";

		// echo "\nTickets Boleta Cabecera Detallado:\n".$sql;

		if ($sqlca->query($sql)<0)
			return onError_AIExit($mssql,"Error al obtener cabaceras de tickets boleta detallado a trasladar");

		while ($reg = $sqlca->fetchRow()) {

			$sql ="	INSERT INTO
					tmpheadback
				(
					DocNro,
					idCliente,
					idVendedor,
					Fecha,
					Pedido,
					SubTotal,
					IGV,
					ImpAd,
					Total,
					TC,
					Tipodoc,
					DocRef,
					Cod_Moneda,
					doc,
					gui,
					fechadoc,
					Usuario,
					Almacen,
					Observaciones,
					Semana,
					Transf,
					Vencimiento,
					IdGrup,
					IdTipo,
					MtoInafecto,
					DocCierre,
					Sucursal,
					Direccion,
					Descuento,
					forma_pago_origen,
					ref_tipodoc,
					ref_serie,
					ref_numero,
					ref_fecha_doc,
					ref_tiponota
				) VALUES (
					'{$reg[0]}',
					'{$reg[1]}',
					'0',
					convert(datetime, '".substr($reg[2],0,19)."', 120),
					'0',
					{$reg[3]},
					{$reg[4]},
					0,
					{$reg[5]},
					2.85,
					{$reg[6]},
					null,
					'001',
					1,
					0,
					convert(datetime, '".substr($reg[7],0,19)."', 120),
					'OPENCOMB',
					{$Cod3DOAlmacen},
					'Interfase Sistema OpenComb',
					1,
					0,
					convert(datetime, '".substr($reg[8],0,19)."', 120),
					145,
					56,
					0,
					'{$reg[10]}',
					'{$reg[9]}',
					'',
					0,
					'{$reg[18]}',
					'{$reg[19]}',
					'{$reg[20]}',
					'{$reg[21]}',
					convert(datetime, '".substr($reg[22],0,19)."', 120),
					'{$reg[23]}'
				);";

			echo "\n INSERT Ticket - Boleta Cabecera: ".$sql."\n";

			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error al trasladar cabecera de ticket boleta detallado {$reg[0]} al 3DO");

			echo "\n ===================== TICKET BOLETA DETALLADO (DETALLE) ===================== \n";

			$res = mssql_query("SELECT @@IDENTITY",$mssql);
			if ($res===FALSE)
				return onError_AIExit($mssql,"Error asociar detalle de ticket boleta DETALLADO {$reg[0]}");

			$rr = mssql_fetch_row($res);
			mssql_free_result($res);

			$currentIdentity = $rr[0];

			$sql="
				INSERT INTO
					tmpbodyback(
							DocNro,
							Secuencia,
							Fecha,
							Codigo,
							Cantidad,
							Unidad,
							Precio,
							Parcial,
							DocRef,
							doc,
							gui,
							Codigoalmacen,
							NumeroOperacion,
							fechaDoc,
							idTipoMovimiento,
							Saldo,
							Costo,
							CodInterno,
							TC,
							Inafecto,
							PorcDesc,
							Grupo,
							Cant,
							DesGrupo,
							CantOrig,
							Descripcion
					) VALUES (
							'{$reg[0]}',				
							null,
							convert(datetime, '".substr($reg[2],0,19)."', 120),
							'{$reg[12]}',
							{$reg[13]},
							'{$reg[14]}',
							{$reg[15]},
							{$reg[17]},
							null,
							1,
							0,
							'{$Cod3DOAlmacen}',
							{$currentIdentity},
							convert(datetime, '".substr($reg[11],0,19)."', 120),
							2,
							0,
							0,
							0,
							2.85,
							0,
							0,
							'',
							0,
							'',
							1,
							'{$reg[16]}'
					);";
				//'{$rex[16]}'

				echo "\n INSERT Ticket - Boleta Detalle: ".$sql."\n";

				if (mssql_query($sql,$mssql)===FALSE)
					return onError_AIExit($mssql,"Error 1 al trasladar detalle de TICKET BOLETA DETALLE {$rex[5]} articulo {$rex[0]}");

		}

//codigo anterior(B)


}

/*******************************************************************************
* TICKETS FACTURA (pos_trans GB)                                               *
*******************************************************************************/

		$sql = "SELECT
				lpad(max(t.caja),4,'000'::text) || '-' || lpad(to_char(t.trans,'FM9999999999'),8,'0') AS DocNro,
				max(t.ruc) AS idCliente,
				max(t.dia) AS fecha,
				sum(t.importe)-sum(t.igv) AS SubTotal,
				sum(t.igv) AS IGV,
				sum(t.importe) AS Total,
				'01'::text AS TipoDoc,
				min(t.dia) AS fechadoc,
				max(t.dia) AS Vencimiento,
				max(t.caja) AS Sucursal,
				t.trans AS TransactionID,
				cfp.nu_posz_z_serie as serie,
				'CONTADO' AS forma_pago_origen,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT 
																						CASE 
																							WHEN FIRST(td) = 'B' AND FIRST(tm) = 'V' THEN '03'::text
																							WHEN FIRST(td) = 'F' AND FIRST(tm) = 'V' THEN '01'::text
																							ELSE '10' 
																						END
																					FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1) 
					ELSE NULL
				END AS ref_tipodoc,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT SUBSTR(TRIM(usr), 0, 5) FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1) 
					ELSE NULL
				END AS ref_serie,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT SUBSTR(TRIM(usr), 6)    FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1)
					ELSE NULL
				END AS ref_numero,

				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT dia                     FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1)
					ELSE NULL
				END AS ref_fecha_doc,
				
				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN '01'
					ELSE NULL
				END AS ref_tiponota
			FROM
				{$postrans} t
				LEFT JOIN pos_z_cierres cfp ON(t.caja=cfp.ch_posz_pos AND t.dia = cfp.dt_posz_fecha_sistema::date AND t.turno::integer = cfp.nu_posturno AND t.es = cfp.ch_sucursal)
			WHERE
				td = 'F'
				AND t.dia BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
				AND t.es = '{$CodAlmacen}'
				AND (t.usr IS NULL OR t.usr = '')
			GROUP BY
				t.trans,
				t.caja,
				t.dia, 
				cfp.nu_posz_z_serie
			ORDER BY
				t.trans,
				t.caja,
				t.dia";

			// echo "<pre> Tickets Factura Cabecera:";
			// print_r($sql);
			// echo "</pre>";

			//echo "Tickets Factura Cabecera:\n".$sql;

			if ($sqlca->query($sql)<0)
				return onError_AIExit($mssql,"Error al obtener cabaceras de tickets factura a trasladar");

		while ($reg = $sqlca->fetchRow()) {
			$sql ="	INSERT INTO
					tmpheadback
				(
					DocNro,
					idCliente,
					idVendedor,
					Fecha,
					Pedido,
					SubTotal,
					IGV,
					ImpAd,
					Total,
					TC,
					Tipodoc,
					DocRef,
					Cod_Moneda,
					doc,
					gui,
					fechadoc,
					Usuario,
					Almacen,
					Observaciones,
					Semana,
					Transf,
					Vencimiento,
					IdGrup,
					IdTipo,
					MtoInafecto,
					DocCierre,
					Sucursal,
					Direccion,
					Descuento,
					forma_pago_origen,
					ref_tipodoc,
					ref_serie,
					ref_numero,
					ref_fecha_doc,
					ref_tiponota
				) VALUES (
					'{$reg[0]}',
					'" . (isset($Clientes[$reg[1]])?$Clientes[$reg[1]]:substr($reg[1],0,10)) . "',
					'0',
					convert(datetime, '".substr($reg[2],0,19)."', 120),
					'0',
					{$reg[3]},
					{$reg[4]},
					0,
					{$reg[5]},
					2.85,
					{$reg[6]},
					null,
					'001',
					1,
					0,
					convert(datetime, '".substr($reg[7],0,19)."', 120),
					'OPENCOMB',
					{$Cod3DOAlmacen},
					'{$ObsVal}',
					1,
					0,
					convert(datetime, '".substr($reg[8],0,19)."', 120),
					145,
					56,
					0,
					'{$reg[11]}',
					'{$reg[9]}',
					'',
					0,
					'{$reg[12]}',
					'{$reg[13]}',
					'{$reg[14]}',
					'{$reg[15]}',
					convert(datetime, '".substr($reg[16],0,19)."', 120),
					'{$reg[17]}'
				);";

			//echo $sql;

			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error al trasladar cabecera de ticket factura {$reg[0]} al 3DO");									
		}


echo "========== SINCRONIZANDO CABECERAS DE TICKETS ==========\n";

		while ($reg = $sqlca->fetchRow()) {
			$sql ="	INSERT INTO
					tmpheadback
				(
					DocNro,
					idCliente,
					idVendedor,
					Fecha,
					Pedido,
					SubTotal,
					IGV,
					ImpAd,
					Total,
					TC,
					Tipodoc,
					DocRef,
					Cod_Moneda,
					doc,
					gui,
					fechadoc,
					Usuario,
					Almacen,
					Observaciones,
					Semana,
					Transf,
					Vencimiento,
					IdGrup,
					IdTipo,
					MtoInafecto,
					DocCierre,
					Sucursal,
					Direccion,
					Descuento,
					forma_pago_origen,
					ref_tipodoc,
					ref_serie,
					ref_numero,
					ref_fecha_doc,
					ref_tiponota
				) VALUES (
					'{$reg[0]}',
					'" . (isset($Clientes[$reg[1]])?$Clientes[$reg[1]]:substr($reg[1],0,10)) . "',
					'0',
					convert(datetime, '".substr($reg[2],0,19)."', 120),
					'0',
					{$reg[3]},
					{$reg[4]},
					0,
					{$reg[5]},
					2.85,
					{$reg[6]},
					null,
					'001',
					1,
					0,
					convert(datetime, '".substr($reg[7],0,19)."', 120),
					'OPENCOMB',
					{$Cod3DOAlmacen},
					'{$ObsVal}',
					1,
					0,
					convert(datetime, '".substr($reg[8],0,19)."', 120),
					145,
					56,
					0,
					'{$reg[11]}',
					'{$reg[9]}',
					'',
					0,
					'{$reg[12]}',
					'{$reg[13]}',
					'{$reg[14]}',
					'{$reg[15]}',
					convert(datetime, '".substr($reg[16],0,19)."', 120),
					'{$reg[17]}'
				);";

			//echo $sql;

			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error al trasladar cabecera ticket factura {$reg[0]} al 3DO");
		}

/*******************************************************************************
* TICKETS FACTURA (pos_trans)                                                  *
*******************************************************************************/

		$HeadID_Trans = Array();
		$res = mssql_query("SELECT DocNro,NumeroOperacion FROM tmpheadback WHERE Observaciones='$ObsVal'",$mssql);
		if ($res===FALSE)
			return onError_AIExit($mssql,"Error al asociar numero de documento tickets");

		if ($res!==TRUE) {
			while ($row = mssql_fetch_row($res))
				$HeadID_Trans[trim($row[0])] = $row[1];
			mssql_free_result($res);

			$sql = "SELECT
					lpad(t.caja,4,'000'::text) || '-' || lpad(to_char(t.trans,'FM9999999999'),8,'0') AS DocNro,
					t.dia AS Fecha,
					trim(t.codigo) AS Codigo,
					t.cantidad AS Cantidad,
					substring(a.art_unidad from 4) AS Unidad,
					t.precio AS Precio,
					t.es AS Codigoalmacen,
					t.trans AS NumeroOperacion,
					t.dia AS fechaDoc,
					a.art_descripcion AS Descripcion,
					(t.cantidad * (t.precio / 1.18)) AS Parcial
				FROM
					$postrans t
					JOIN int_articulos a ON t.codigo = a.art_codigo
				WHERE
					td = 'F'
					AND t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
					AND t.es = '{$CodAlmacen}'
					AND (t.usr IS NULL OR t.usr = '')
				ORDER BY
					t.trans;";

// echo "<pre> Tickets Factura Detalle:";
// print_r($sql);
// echo "</pre>";

//echo "Tickets Factura Detalle:\n".$sql;

			if ($sqlca->query($sql)<0)
				return onError_AIExit($mssql,"Error al seleccionar detalle de tickets a trasladar");

//echo "========== SINCRONIZANDO DETALLE DE TICKETS MANUALES ==========\n";

			while ($reg = $sqlca->fetchRow()) {
				$sql ="	INSERT INTO
						tmpbodyback
					(
						DocNro,
						Secuencia,
						Fecha,
						Codigo,
						Cantidad,
						Unidad,
						Precio,
						Parcial,
						DocRef,
						doc,
						gui,
						Codigoalmacen,
						NumeroOperacion,
						fechaDoc,
						idTipoMovimiento,
						Saldo,
						Costo,
						CodInterno,
						TC,
						Inafecto,
						PorcDesc,
						Grupo,
						Cant,
						DesGrupo,
						CantOrig,
						Descripcion
					) VALUES (
						'{$reg[0]}',
						null,
						convert(datetime, '".substr($reg[1],0,19)."', 120),
						'{$reg[2]}',
						{$reg[3]},
						'{$reg[4]}',
						{$reg[5]},
						{$reg[10]},
						null,
						1,
						0,
						'{$Cod3DOAlmacen}',
						" . $HeadID_Trans[$reg[0]] . ",
						convert(datetime, '".substr($reg[8],0,19)."', 120),
						2,
						0,
						0,
						0,
						2.85,
						0,
						0,
						'',
						0,
						'',
						1,
						'{$reg[9]}'
					);";

				//echo "$sql\n";

				if (mssql_query($sql,$mssql)===FALSE)
					return onError_AIExit($mssql,"Error 1 al trasladar detalle de ticket {$reg[0]} articulo {$reg[2]}");
			}
			if (mssql_query("UPDATE tmpheadback SET Observaciones='Interfase Sistema OpenComb' WHERE Observaciones='$ObsVal';",$mssql)===FALSE)
				return onError_AIExit($mssql,"Error 2 al trasladar detalle de ticket {$reg[0]} articulo {$reg[2]}");
		}

/*******************************************************************************
* CPE Playa Cabecera (pos_trans GB)                                               *
*******************************************************************************/

		$sql = "SELECT
				FIRST(t.usr) AS DocNro,
				max(t.ruc) AS idCliente,
				max(t.dia) AS fecha,

				CASE
					WHEN FIRST(t.td) = 'B' AND FIRST(t.tm) = 'V' THEN sum(t.importe)-sum(t.igv)
					WHEN FIRST(t.td) = 'F' AND FIRST(t.tm) = 'V' THEN sum(t.importe)-sum(t.igv)
					ELSE abs(sum(t.importe)-sum(t.igv))
				END AS SubTotal,

				--sum(t.importe)-sum(t.igv) AS SubTotal,
				CASE
					WHEN FIRST(t.td) = 'B' AND FIRST(t.tm) = 'V' THEN sum(t.igv)
					WHEN FIRST(t.td) = 'F' AND FIRST(t.tm) = 'V' THEN sum(t.igv)
					ELSE abs(sum(t.igv))
				END AS IGV,
				--sum(t.igv) AS IGV,

				CASE
					WHEN FIRST(t.td) = 'B' AND FIRST(t.tm) = 'V' THEN sum(t.importe)
					WHEN FIRST(t.td) = 'F' AND FIRST(t.tm) = 'V' THEN sum(t.importe)
					ELSE abs(sum(t.importe))
				END AS Total,
				--sum(t.importe) AS Total,

				CASE
					WHEN FIRST(t.td) = 'B' AND FIRST(t.tm) = 'V' THEN '03'::text
					WHEN FIRST(t.td) = 'F' AND FIRST(t.tm) = 'V' THEN '01'::text
					ELSE '10'::text
				END AS TipoDoc,
				min(t.dia) AS fechadoc,
				max(t.dia) AS Vencimiento,
				max(t.caja) AS Sucursal,
				t.trans AS TransactionID,
				'CONTADO' AS forma_pago_origen,
				
				CASE 
			        WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT 
																							CASE 
																								WHEN FIRST(td) = 'B' AND FIRST(tm) = 'V' THEN '03'::text
																								WHEN FIRST(td) = 'F' AND FIRST(tm) = 'V' THEN '01'::text
																								ELSE '10'::text 
																							END
																						FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1) 
					ELSE NULL
				END AS ref_tipodoc,
				
				CASE 
			        WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT SUBSTR(TRIM(usr), 0, 5) FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1) 
					ELSE NULL
				END AS ref_serie,

				CASE 
			        WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT SUBSTR(TRIM(usr), 6)    FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1)
					ELSE NULL
				END AS ref_numero,

				CASE 
			        WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN (SELECT dia                     FROM {$postrans} WHERE es = FIRST(t.es) AND caja = FIRST(t.caja) AND td = FIRST(t.td) AND trans = FIRST(t.rendi_gln) AND tm = 'V' AND grupo != 'D' LIMIT 1)
					ELSE NULL
				END AS ref_fecha_doc,
				
				CASE 
					WHEN (FIRST(t.tm = 'A') AND FIRST(t.rendi_gln)::CHAR != '') THEN '01'
					ELSE NULL
				END AS ref_tiponota
			FROM
				{$postrans} t
			WHERE
				t.dia BETWEEN '{$FechaIni}' AND '{$FechaFin} 23:59:59'
				AND t.es = '{$CodAlmacen}'
				AND t.usr IS NOT NULL AND t.usr != '' --DOCUMENTOS ELECTRONICOS --
			GROUP BY
				t.trans,
				t.caja,
				t.dia
			ORDER BY
				t.trans,
				t.caja,
				t.dia";

			// echo "<pre> Tickets Factura Cabecera:";
			// print_r($sql);
			// echo "</pre>";

			//echo "Tickets Factura Cabecera:\n".$sql;
			//modificado TD(Eliminado)

			if ($sqlca->query($sql)<0)
				return onError_AIExit($mssql,"Error al obtener cabaceras de tickets factura a trasladar");

		while ($reg = $sqlca->fetchRow()) {
			$docid = "1049";
			if (isset($Clientes[$reg[1]]))
				$docid = $Clientes[$reg[1]];
			else
				$docid = "1049";
//				$docid = substr($reg[1],0,10);
//'" . (isset($Clientes[$reg[1]])?$Clientes[$reg[1]]:substr($reg[1],0,10)) . "',
			$sql ="	INSERT INTO
					tmpheadback
				(
					DocNro,
					idCliente,
					idVendedor,
					Fecha,
					Pedido,
					SubTotal,
					IGV,
					ImpAd,
					Total,
					TC,
					Tipodoc,
					DocRef,
					Cod_Moneda,
					doc,
					gui,
					fechadoc,
					Usuario,
					Almacen,
					Observaciones,
					Semana,
					Transf,
					Vencimiento,
					IdGrup,
					IdTipo,
					MtoInafecto,
					DocCierre,
					Sucursal,
					Direccion,
					Descuento,
					forma_pago_origen,
					ref_tipodoc,
					ref_serie,
					ref_numero,
					ref_fecha_doc,
					ref_tiponota
				) VALUES (
					'{$reg[0]}',
					'{$docid}',
					'0',
					convert(datetime, '".substr($reg[2],0,19)."', 120),
					'0',
					{$reg[3]},
					{$reg[4]},
					0,
					{$reg[5]},
					2.85,
					{$reg[6]},
					null,
					'001',
					1,
					0,
					convert(datetime, '".substr($reg[7],0,19)."', 120),
					'OPENCOMB',
					{$Cod3DOAlmacen},
					'{$ObsVal}',
					1,
					0,
					convert(datetime, '".substr($reg[8],0,19)."', 120),
					145,
					56,
					0,
					'{$reg[0]}',
					'{$reg[9]}',
					'',
					0,
					'{$reg[11]}',
					'{$reg[12]}',
					'{$reg[13]}',
					'{$reg[14]}',
					convert(datetime, '".substr($reg[15],0,19)."', 120),
					'{$reg[16]}'
				);";

			//echo $sql;

			if (mssql_query($sql,$mssql)===FALSE) {
				error_log("Insert cabecera de ticket factura (FE)");
				error_log($sql);
				error_log($mssql);
				return onError_AIExit($mssql,"Error al trasladar cabecera de ticket factura (FE) {$reg[0]} al 3DO");
			}
		}


echo "========== SINCRONIZANDO CABECERAS DE TICKETS ==========\n";

/*		while ($reg = $sqlca->fetchRow()) {
			$sql ="	INSERT INTO
					tmpheadback
				(
					DocNro,
					idCliente,
					idVendedor,
					Fecha,
					Pedido,
					SubTotal,
					IGV,
					ImpAd,
					Total,
					TC,
					Tipodoc,
					DocRef,
					Cod_Moneda,
					doc,
					gui,
					fechadoc,
					Usuario,
					Almacen,
					Observaciones,
					Semana,
					Transf,
					Vencimiento,
					IdGrup,
					IdTipo,
					MtoInafecto,
					DocCierre,
					Sucursal,
					Direccion,
					Descuento
				) VALUES (
					'{$reg[0]}',
					'" . (isset($Clientes[$reg[1]])?$Clientes[$reg[1]]:substr($reg[1],0,10)) . "',
					'0',
					convert(datetime, '".substr($reg[2],0,19)."', 120),
					'0',
					{$reg[3]},
					{$reg[4]},
					0,
					{$reg[5]},
					2.85,
					{$reg[6]},
					null,
					'001',
					1,
					0,
					convert(datetime, '".substr($reg[7],0,19)."', 120),
					'OPENCOMB',
					{$Cod3DOAlmacen},
					'{$ObsVal}',
					1,
					0,
					convert(datetime, '".substr($reg[8],0,19)."', 120),
					145,
					56,
					0,
					'{$reg[11]}',
					'{$reg[9]}',
					'',
					0
				);";

			//echo $sql;

			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error al trasladar cabecera ticket factura {$reg[0]} al 3DO");
		}*/


/*******************************************************************************
* CPE Playa Detalle (pos_trans)                                                *
*******************************************************************************/

		$HeadID_Trans = Array();
		$res = mssql_query("SELECT DocNro,NumeroOperacion FROM tmpheadback WHERE Observaciones='$ObsVal'",$mssql);
		if ($res===FALSE)
			return onError_AIExit($mssql,"Error al asociar numero de documento tickets");

		if ($res!==TRUE) {
			while ($row = mssql_fetch_row($res))
				$HeadID_Trans[trim($row[0])] = $row[1];
			mssql_free_result($res);

			$sql = "SELECT
					t.usr AS DocNro,
					t.dia AS Fecha,
					trim(t.codigo) AS Codigo,
					t.cantidad AS Cantidad,
					substring(a.art_unidad from 4) AS Unidad,
					t.precio AS Precio,
					t.es AS Codigoalmacen,
					t.trans AS NumeroOperacion,
					t.dia AS fechaDoc,
					a.art_descripcion AS Descripcion,
					(t.cantidad * (t.precio / 1.18)) AS Parcial
				FROM
					$postrans t
					JOIN int_articulos a ON t.codigo = a.art_codigo
				WHERE
					t.dia BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
					AND t.es = '{$CodAlmacen}'
					AND t.usr IS NOT NULL AND t.usr != ''
				ORDER BY
					t.trans;";

// echo "<pre> Tickets Factura Detalle:";
// print_r($sql);
// echo "</pre>";
//echo "Tickets Factura Detalle:\n".$sql;

			if ($sqlca->query($sql)<0)
				return onError_AIExit($mssql,"Error al seleccionar detalle de tickets a trasladar");

//echo "========== SINCRONIZANDO DETALLE DE TICKETS MANUALES ==========\n";

			while ($reg = $sqlca->fetchRow()) {
				$sql ="	INSERT INTO
						tmpbodyback
					(
						DocNro,
						Secuencia,
						Fecha,
						Codigo,
						Cantidad,
						Unidad,
						Precio,
						Parcial,
						DocRef,
						doc,
						gui,
						Codigoalmacen,
						NumeroOperacion,
						fechaDoc,
						idTipoMovimiento,
						Saldo,
						Costo,
						CodInterno,
						TC,
						Inafecto,
						PorcDesc,
						Grupo,
						Cant,
						DesGrupo,
						CantOrig,
						Descripcion
					) VALUES (
						'{$reg[0]}',
						null,
						convert(datetime, '".substr($reg[1],0,19)."', 120),
						'{$reg[2]}',
						{$reg[3]},
						'{$reg[4]}',
						{$reg[5]},
						{$reg[10]},
						null,
						1,
						0,
						'{$Cod3DOAlmacen}',
						" . $HeadID_Trans[$reg[0]] . ",
						convert(datetime, '".substr($reg[8],0,19)."', 120),
						2,
						0,
						0,
						0,
						2.85,
						0,
						0,
						'',
						0,
						'',
						1,
						'{$reg[9]}'
					);";

				//echo "$sql\n";

				if (mssql_query($sql,$mssql)===FALSE)
					return onError_AIExit($mssql,"Error 1 al trasladar detalle de ticket {$reg[0]} articulo {$reg[2]}");
			}
			if (mssql_query("UPDATE tmpheadback SET Observaciones='Interfase Sistema OpenComb' WHERE Observaciones='$ObsVal';",$mssql)===FALSE)
				return onError_AIExit($mssql,"Error 2 al trasladar detalle de ticket {$reg[0]} articulo {$reg[2]}");
		}

/*******************************************************************************
* CABECERAS DE DOCUMENTOS MANUALES (fac_ta_factura_cabecera)                   *
*******************************************************************************/

		$sql = "SELECT
				fc.ch_fac_seriedocumento || '-' || lpad(fc.ch_fac_numerodocumento,8,'0') AS DocNro,
				CASE
					WHEN fc.cli_codigo = '9999' THEN '{$Cod3DOCliVarios}'::text
					ELSE trim(c.cli_ruc)
				END AS idCliente,
				fc.dt_fac_fecha AS fecha,
				fc.nu_fac_valorbruto AS SubTotal,
				fc.nu_fac_impuesto1 AS IGV,
				fc.nu_fac_valortotal  AS Total,
				CASE
					WHEN fc.ch_fac_tipodocumento = '10' THEN '01'::text--factura
					WHEN fc.ch_fac_tipodocumento = '35' THEN '03'::text--boleta
					WHEN fc.ch_fac_tipodocumento = '11' THEN '04'::text--liquidación de compra
					WHEN fc.ch_fac_tipodocumento = '20' THEN '10'::text--nota de credito
					ELSE '00'::text
				END AS TipoDoc,
				fc.dt_fac_fecha AS fechadoc,
				CASE
					WHEN fc.ch_fac_forma_pago = '01' THEN fc.dt_fac_fecha + 7
					WHEN fc.ch_fac_forma_pago = '02' THEN fc.dt_fac_fecha + 15
					WHEN fc.ch_fac_forma_pago = '03' THEN fc.dt_fac_fecha + 30
					WHEN fc.ch_fac_forma_pago = '04' THEN fc.dt_fac_fecha + 45
					WHEN fc.ch_fac_forma_pago = '05' THEN fc.dt_fac_fecha + 60
					WHEN fc.ch_fac_forma_pago = '06' THEN fc.dt_fac_fecha
					WHEN fc.ch_fac_forma_pago = '07' THEN fc.dt_fac_fecha
					WHEN fc.ch_fac_forma_pago = '08' THEN fc.dt_fac_fecha + 5
					WHEN fc.ch_fac_forma_pago = '09' THEN fc.dt_fac_fecha + 10
					WHEN fc.ch_fac_forma_pago = '21' THEN fc.dt_fac_fecha + 18
					WHEN fc.ch_fac_forma_pago = '30' THEN fc.dt_fac_fecha
				END AS vencimiento,
				''::text AS Sucursal,
				CASE
					WHEN fc.ch_liquidacion IS NULL THEN '{$ObsVal}'
					WHEN fc.ch_liquidacion = '' THEN '{$ObsVal}'
					ELSE fc.ch_liquidacion
				END AS Liquidacion,
				CASE
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '00' THEN '78'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '01' THEN '40'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '02' THEN '41'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '03' THEN '42'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '04' THEN '43'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '05' THEN '44'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '06' THEN '78'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '07' THEN '78'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '08' THEN '78'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '09' THEN '86'
					WHEN fc.ch_fac_credito = 'S' AND fc.ch_fac_forma_pago = '21' THEN '78'
					ELSE '78'
				END AS ch_fac_forma_pago,
				CASE 
					WHEN fc.nu_tipo_pago = '06' THEN 'CREDITO' --SI ES DOCUMENTO CON FORMA DE PAGO CREDITO
					ELSE 'CONTADO'
				END AS forma_pago_origen,

				CASE
					WHEN (string_to_array(fcc.ch_fac_observacion2, '*'))[3] = '10' THEN '01'::text--factura
					WHEN (string_to_array(fcc.ch_fac_observacion2, '*'))[3] = '35' THEN '03'::text--boleta
					WHEN (string_to_array(fcc.ch_fac_observacion2, '*'))[3] = '11' THEN '04'::text--liquidación de compra
					WHEN (string_to_array(fcc.ch_fac_observacion2, '*'))[3] = '20' THEN '10'::text--nota de credito
					ELSE NULL
				END AS ref_tipodoc,

				(string_to_array(fcc.ch_fac_observacion2, '*'))[2] AS ref_serie,
				
				(string_to_array(fcc.ch_fac_observacion2, '*'))[1] AS ref_numero,
				
				ch_fac_observacion3 AS ref_fecha_doc,
				
				ch_cat_sunat AS ref_tiponota
			FROM
				fac_ta_factura_cabecera fc
				JOIN int_clientes c ON fc.cli_codigo = c.cli_codigo
				LEFT JOIN fac_ta_factura_complemento fcc ON (fc.ch_fac_tipodocumento = fcc.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = fcc.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = fcc.ch_fac_numerodocumento AND fc.dt_fac_fecha = fcc.dt_fac_fecha)
			WHERE
				fc.ch_fac_tipodocumento IN ('10','11','20','35')
				AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
				AND fc.ch_almacen = '$CodAlmacen'";

		// echo "<pre> Cabeceras de documentos manuales:";
		// print_r($sql);
		// echo "</pre>";

		if ($sqlca->query($sql)<0)
			return onError_AIExit($mssql,"Error al obtener cabeceras de documentos manuales a trasladas");

echo "========== SINCRONIZANDO CABECERAS DE DOCUMENTOS MANUALES ==========\n";

		while ($reg = $sqlca->fetchRow()) {
			$sql ="	INSERT INTO
					tmpheadback
				(
					DocNro,
					idCliente,
					idVendedor,
					Fecha,
					Pedido,
					SubTotal,
					IGV,
					ImpAd,
					Total,
					TC,
					Tipodoc,
					DocRef,
					Cod_Moneda,
					doc,
					gui,
					fechadoc,
					Usuario,
					Almacen,
					Observaciones,
					Semana,
					Transf,
					Vencimiento,
					IdGrup,
					IdTipo,
					MtoInafecto,
					DocCierre,
					Sucursal,
					Direccion,
					Descuento,
					forma_pago_origen,
					ref_tipodoc,
					ref_serie,
					ref_numero,
					ref_fecha_doc,
					ref_tiponota
				) VALUES (
					'{$reg[0]}',
					'" . (isset($Clientes[$reg[1]])?$Clientes[$reg[1]]:substr($reg[1],0,10)) . "',
					'0',
					convert(datetime, '".substr($reg[2],0,19)."', 120),
					'0',
					{$reg[3]},
					{$reg[4]},
					0,
					{$reg[5]},
					2.85,
					'{$reg[6]}',
					null,
					'001',
					1,
					0,
					convert(datetime, '".substr($reg[7],0,19)."', 120),
					'OPENCOMB',
					{$Cod3DOAlmacen},
					'{$ObsVal}',
					1,
					0,
					convert(datetime, '".substr($reg[8],0,19)."', 120),
					145,
					'{$reg[11]}',
					0,
					null,
					'{$reg[9]}',
					'',
					0,
					'{$reg[12]}',
					'{$reg[13]}',
					'{$reg[14]}',
					'{$reg[15]}',
					convert(datetime, '".substr($reg[16],0,19)."', 120),
					'{$reg[17]}'
				);";
			//echo "$sql\n";
			if (mssql_query($sql,$mssql)===FALSE) {
				trigger_error($sql);
				return onError_AIExit($mssql,"Error al trasladar cabecera de documento manual {$reg[0]}");
			}
		}

/*******************************************************************************
* DETALLE DE DOCUMENTOS MANUALES (fac_ta_factura_detalle)                      *
*******************************************************************************/

		$HeadID_Trans = Array();
		$res = mssql_query("SELECT DocNro,NumeroOperacion FROM tmpheadback WHERE Observaciones='$ObsVal'",$mssql);
		if ($res===FALSE)
			return onError_AIExit($mssql,"Error al asociar documentos manuales a migrar");

		if ($res!==TRUE) {
			while ($row = mssql_fetch_row($res)) {
				$HeadID_Trans[trim($row[0])] = $row[1];
			}
			mssql_free_result($res);

			$sql = "SELECT
					fc.ch_fac_seriedocumento || '-' || lpad(fc.ch_fac_numerodocumento,8,'0') AS DocNro,
					fc.dt_fac_fecha AS Fecha,
					fd.art_codigo AS Codigo,
					fd.nu_fac_cantidad * CASE
						WHEN fc.ch_fac_tipodocumento = '20' THEN -1
						ELSE 1
					END AS Cantidad,
					substring(a.art_unidad from 4) AS Unidad,
					fd.nu_fac_precio AS Precio,
					fc.ch_almacen AS Codigoalmacen,
					fc.ch_fac_numerodocumento AS NumeroOperacion,
					fc.dt_fac_fecha AS fechaDoc,
					fd.ch_art_descripcion AS Descripcion,
					((fd.nu_fac_cantidad * CASE WHEN fc.ch_fac_tipodocumento = '20' THEN -1 ELSE 1 END) * (fd.nu_fac_precio / 1.18)) AS Parcial
				FROM
					fac_ta_factura_detalle fd
					JOIN fac_ta_factura_cabecera fc ON (fd.ch_fac_tipodocumento = fc.ch_fac_tipodocumento AND fd.ch_fac_seriedocumento = fc.ch_fac_seriedocumento AND fd.ch_fac_numerodocumento = fc.ch_fac_numerodocumento)
					JOIN int_articulos a ON fd.art_codigo = a.art_codigo
				WHERE
					fc.ch_fac_tipodocumento IN ('10','11','20','35')
					AND fc.dt_fac_fecha BETWEEN '$FechaIni' AND '$FechaFin'
					AND fc.ch_almacen = '$CodAlmacen';";

			// echo "<pre> Detalle de documentos manuales:";
			// print_r($sql);
			// echo "</pre>";

			if ($sqlca->query($sql)<0)
				return onError_AIExit($mssql,"Error al seleccionar delatte de documentos manuales a trasladas");

echo "========== SINCRONIZANDO DETALLE DE DOCUMENTOS MANUALES ==========\n";

			while ($reg = $sqlca->fetchRow()) {
				$sql ="	INSERT INTO
						tmpbodyback
					(
						DocNro,
						Secuencia,
						Fecha,
						Codigo,
						Cantidad,
						Unidad,
						Precio,
						Parcial,
						DocRef,
						doc,
						gui,
						Codigoalmacen,
						NumeroOperacion,
						fechaDoc,
						idTipoMovimiento,
						Saldo,
						Costo,
						CodInterno,
						TC,
						Inafecto,
						PorcDesc,
						Grupo,
						Cant,
						DesGrupo,
						CantOrig,
						Descripcion
					) VALUES (
						'{$reg[0]}',
						null,
						convert(datetime, '".substr($reg[1],0,19)."', 120),
						'{$reg[2]}',
						{$reg[3]},
						'{$reg[4]}',
						{$reg[5]},
						{$reg[10]},
						null,
						1,
						0,
						'" . $Almacenes[$reg[6]] . "',
						" . $HeadID_Trans[$reg[0]] . ",
						convert(datetime, '".substr($reg[8],0,19)."', 120),
						2,
						0,
						0,
						0,
						2.85,
						0,
						0,
						'',
						0,
						'',
						1,
						'{$reg[9]}'
					);";
				//echo "$sql\n";
				if (mssql_query($sql,$mssql)===FALSE)
					return onError_AIExit($mssql,"Error 1 al trasladar detalle de documento {$reg[0]} articulo {$reg[2]}");
			}
			if (mssql_query("UPDATE tmpheadback SET Observaciones='Interfase Sistema OpenComb' WHERE Observaciones='$ObsVal';",$mssql)===FALSE)
				return onError_AIExit($mssql,"Error 1 al trasladar detalle de documento {$reg[0]} articulo {$reg[2]}");
		}

/*******************************************************************************
* KARDEX (inv_movialma)                                                        *
*******************************************************************************/

		$sql ="	SELECT
				m.art_codigo AS CodigoArticulo,
				CASE
					WHEN tt.tran_naturaleza IN ('1','2') THEN 1
					ELSE 2
				END AS IdTipoMovimiento,
				m.mov_fecha AS FechaMovimiento,
				m.mov_cantidad AS Cantidad,
				substring(a.art_unidad from 4) AS Unidad,
				m.mov_docurefe AS DocReferencia,
				m.mov_tipdocuref AS TipoDocumento,
				m.mov_almacen AS CodigoAlmacen,
				COALESCE(m.mov_costounitario,0) AS CostoUnitario,
				COALESCE(m.mov_costototal,0) AS CostoTotal
			FROM
				inv_movialma m
				JOIN inv_tipotransa tt ON m.tran_codigo = tt.tran_codigo
				JOIN int_articulos a ON m.art_codigo = a.art_codigo
			WHERE
				m.mov_fecha BETWEEN '$FechaIni' AND '$FechaFin 23:59:59'
				AND m.mov_almacen = '$CodAlmacen';";

		// echo "<pre> Movimientos de almacen:";
		// print_r($sql);
		// echo "</pre>";

		if ($sqlca->query($sql)<0)
			return onError_AIExit($mssql,"Error al obtener movimientos de almacen a trasladar");

echo "========== SINCRONIZANDO KARDEX ==========\n";

		while ($reg = $sqlca->fetchRow()) {
			$sql ="	INSERT INTO
					Kardex
				(
					CodigoArticulo,
					IdTipoMovimiento,
					FechaMovimiento,
					Cantidad,
					Unidad,
					Saldo,
					DocReferencia,
					TipoDocumento,
					Secuencia,
					Precio,
					CodigoAlmacen,
					CostoUnitario,
					CostoTotal,
					CodInterno,
					Lote,
					Vencimiento,
					SaldoLote
				) VALUES (
					'{$reg[0]}',
					{$reg[1]},
					convert(datetime, '".substr($reg[2],0,19)."', 120),
					{$reg[3]},
					'{$reg[4]}',
					0,
					'{$reg[5]}',
					'{$reg[6]}',
					null,
					0,
					'{$Cod3DOAlmacen}',
					{$reg[8]},
					{$reg[9]},
					0,
					null,
					null,
					0
				);";
			//echo "$sql\n";
			if (mssql_query($sql,$mssql)===FALSE)
				return onError_AIExit($mssql,"Error al trasladar movimiento de almacen {$reg[5]} articulo {$reg[0]}");
		}

		$sqlca->query("INSERT INTO \"3do_migraciones\" (ch_almacen,fecha_inicio,fecha_fin,ch_usuario) VALUES ('$CodAlmacen','$FechaIni','$FechaFin','{$_SESSION['auth_usuario']}');");
		// $sqlca->query("COMMIT;");
		// mssql_query("COMMIT TRANSACTION;",$mssql);
		$sqlca->query("ROLLBACK;");
		mssql_query("ROLLBACK TRANSACTION;",$mssql);
		mssql_close($mssql);

		return TRUE;
	}

	function ConsultaProcesos ($desde,$hasta,$sucursal){
    		global $sqlca;
		if($sucursal=="all") {
				$query = "SELECT \"3do_migracion_id\" as id,ch_almacen as almacen,to_char(fecha_inicio, 'DD/MM/YYYY') as inicio,to_char(fecha_fin, 'DD/MM/YYYY') as fin,to_char(fecha_actual, 'DD/MM/YYYY HH:MI:SS') as actual, ch_usuario as usuario  
				  FROM \"3do_migraciones\"
				  WHERE fecha_inicio between to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')
				  ORDER BY fecha_inicio DESC;";
		} else {
			$query = "SELECT \"3do_migracion_id\" as id,ch_almacen as almacen,to_char(fecha_inicio, 'DD/MM/YYYY') as inicio,to_char(fecha_fin, 'DD/MM/YYYY') as fin,to_char(fecha_actual, 'DD/MM/YYYY HH:MI:SS') as actual, ch_usuario as usuario  
				  FROM \"3do_migraciones\"
				  WHERE ch_almacen = '".pg_escape_string($sucursal)."' and fecha_inicio between to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')
				  ORDER BY fecha_inicio DESC;";
		}
         	if ($sqlca->query($query) < 0) return null;
		$resultado = array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$fila = $sqlca->fetchRow();
			$resultado[$i] = $fila;
		}
    		return $resultado;
	}

	function Eliminar ($id,$Parametros){
        require_once("/sistemaweb/include/mssqlemu.php");

    	global $sqlca;

		$MSSQLDBHost = $Parametros[0];
		$MSSQLDBUser = $Parametros[1];
		$MSSQLDBPass = $Parametros[2];
		$MSSQLDBName = $Parametros[3];

		$sql = "SELECT ch_almacen,fecha_inicio,fecha_fin FROM \"3do_migraciones\" WHERE \"3do_migracion_id\"= ".$id."";
		if ($sqlca->query($sql) < 0 || $sqlca->numrows() < 1)
			return "Migracion no valida";
		$w = $sqlca->fetchRow();
		$CodAlmacen = $w[0];
		$FechaIni = $w[1];
		$FechaFin = $w[2];
		
		$mssql = mssql_connect($MSSQLDBHost,$MSSQLDBUser,$MSSQLDBPass);

		if ($mssql===FALSE) {trigger_error(mssql_get_last_message());
			return "Error al conectarse a la base de datos del 3DO";
		}

		mssql_select_db($MSSQLDBName,$mssql);

		$Almacenes = Array();
		$res = mssql_query("SELECT centrocosto,CodigoAlmacen FROM Almacen WHERE centrocosto IS NOT NULL AND centrocosto != ''",$mssql);

		if ($res===FALSE)
			return "Error interno en base de datos 3DO (1)";

		while ($row = mssql_fetch_row($res))
			$Almacenes[$row[0]] = $row[1];

		$Cod3DOAlmacen = $Almacenes[$CodAlmacen];

		if ($Cod3DOAlmacen == NULL || $Cod3DOAlmacen == "")
			return "No se pudo asociar el almacen Opensoft con un almacen 3DO.";

		$FechaIni = substr($FechaIni,5,2)."/".substr($FechaIni,8,2)."/".substr($FechaIni,0,4);
		$FechaFin = substr($FechaFin,5,2)."/".substr($FechaFin,8,2)."/".substr($FechaFin,0,4);
		
		//$res = mssql_query("EXECUTE sp_OpenComb_validaEliminacion '" . $FechaIni . "','" . $FechaFin . "','" . $Cod3DOAlmacen . "','OPENCOMB'",$mssql);
		$res = mssql_query("EXECUTE sp_OpenComb_validaEliminacion '" . $FechaIni . "','" . $FechaFin . "','" . $Cod3DOAlmacen . "','OPENCOMB'",$mssql);

		if ($res===FALSE)
			return "Error al validar la migracion de datos";
		$row = mssql_fetch_row($res);

		if ($row[0] !== NULL)
			return "La infomacion ya fue migrada al 3DO anteriormente";
		mssql_close($mssql);

		$query = "DELETE FROM \"3do_migraciones\" WHERE \"3do_migracion_id\"= ".$id.";";
    		return $sqlca->query($query);
	}
}
