<?php

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");
$r = ob_start(true);

/**
 * pg_escape_string
 * para entrada de datos GET/POST
 */

function SQLImplode($sql) {
		global $sqlca;

		if ($sqlca->query($sql) < 0)
				return FALSE;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$rR = $sqlca->fetchRow();
				foreach ($rR as $k => $v) {
						if (is_numeric($k))
								echo (($k == 0) ? "" : "|") . $v;
				}
				echo "\n";
		}
}

function SQLImplodeSerialize($sql, $posta = false) {
		global $sqlca;
		if ($sqlca->query($sql) < 0) {
			return FALSE;
		}
		$result = array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$result[] = $sqlca->fetchRow();
		}
		if($posta == true){
			error_log( json_encode($result) );
			error_log( serialize($result) );
		}
		echo serialize($result);
}

function SQLImplodeJSON($sql) {
		global $sqlca;
		if ($sqlca->query($sql) < 0) {
			return FALSE;
		}
		$result = array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$result[] = $sqlca->fetchRow();
		}
		return json_encode($result);
}

function SQLImplodeMultiple($sqls) {
		global $sqlca;
		$result = '';

		foreach ($sqls as $key => $sql) {    
			if ($sqlca->query($sql) < 0)
					return FALSE;

			for ($i = 0; $i < $sqlca->numrows(); $i++) {
					$rR = $sqlca->fetchRow();
					foreach ($rR as $k => $v) {
							if (is_numeric($k))
									$result += (($k == 0) ? "" : "|") . $v;
					}
					$result += "\n\n";
			}
		}
		echo $result;
}

function SQLImplode2($sql) {
		global $sqlca;

		if ($sqlca->query($sql) < 0)
				return FALSE;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$rR = $sqlca->fetchRow();
				foreach ($rR as $k => $v) {
						if (is_numeric($k))
								echo (($k == 0) ? "" : "¿") . $v;
				}
				echo "\n";
		}
}

function argRangedCheck() {
		global $CxBegin, $CxEnd, $PosTransTable, $BeginDate, $EndDate;
		//el mismo mes y periodo para las consultas
		global $BeginYear, $BeginMonth, $BeginDay, $EndYear, $EndMonth, $EndDay, $_BeginYear, $_BeginMonth, $_BeginDay, $_EndYear, $_EndMonth, $_EndDay;
		global $_BeginDate, $_EndDate;
		if (!isset($_REQUEST['from']) || !isset($_REQUEST['to']))
				die("ERR_INVALID_ARGS_RANGED");

		$CxBegin = $_REQUEST['from'];
		$CxEnd = $_REQUEST['to'];

		if (strlen($CxBegin) != 8 || strlen($CxEnd) != 8 || !is_numeric($CxBegin) || !is_numeric($CxEnd))
				die("ERR_INVALID_DATE");

		if (!isset($_REQUEST['isvaliddiffmonths'])) {
			if(substr($CxBegin, 0, 6) != substr($CxEnd, 0, 6))
					die("ERR_DATE_DIFFERENT_MONTHS");
		}

		$PosTransTable = "pos_trans" . substr($CxBegin, 0, 6);
		//año - mes - dia
		$BeginDate = substr($CxBegin, 0, 4) . "-" . substr($CxBegin, 4, 2) . "-" . substr($CxBegin, 6, 2);
		$EndDate = substr($CxEnd, 0, 4) . "-" . substr($CxEnd, 4, 2) . "-" . substr($CxEnd, 6, 2);

		$BeginYear = substr($CxBegin, 0, 4);
		$BeginMonth = substr($CxBegin, 4, 2);
		$BeginDay = substr($CxBegin, 6, 2);

		$EndYear = substr($CxEnd, 0, 4);
		$EndMonth = substr($CxEnd, 4, 2);
		$EndDay = substr($CxEnd, 6, 2);

		//actualizacion para estadistica con 4 rangos de fecha 2017-05-24
		//rango anterior
		$_BeginDate = '';
		$_EndDate = '';
		$_PosTransTable = '';
		if (isset($_REQUEST['mod'])) {
			if ($_REQUEST['mod'] == 'TOTALS_STATISTICS_SALE') {
				$_CxBegin = $_REQUEST['_from'];
				$_CxEnd = $_REQUEST['_to'];

				$_BeginDate = substr($_CxBegin, 0, 4) . "-" . substr($_CxBegin, 4, 2) . "-" . substr($_CxBegin, 6, 2);
				$_EndDate = substr($_CxEnd, 0, 4) . "-" . substr($_CxEnd, 4, 2) . "-" . substr($_CxEnd, 6, 2);
				$_PosTransTable = "pos_trans" . substr($_CxBegin, 0, 6);


				$_BeginYear = substr($_CxBegin, 0, 4);
				$_BeginMonth = substr($_CxBegin, 4, 2);
				$_BeginDay = substr($_CxBegin, 6, 2);
				$_EndYear = substr($_CxEnd, 0, 4);
				$_EndMonth = substr($_CxEnd, 4, 2);
				$_EndDay = substr($_CxEnd, 6, 2);
			}
		}
}

function argKeyCheck() {
		global $CxSearchKey;

		if (!isset($_REQUEST['sk']))
				die("ERR_INVALID_ARGS_KEY");

		$CxSearchKey = $_REQUEST['sk'];
		if ($CxSearchKey == "" || strlen($CxSearchKey) < 5)
				die("ERR_INVALID_KEY");
}

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

if (!isset($_REQUEST['mod']))
		die("ERR_INVALID_ARGS");


$CxModule = $_REQUEST['mod'];


switch ($CxModule) {

		/**
		***************************
		** Casos para OCSMANAGER **
		***************************
		*/

		case 'ERR':
			echo 'ERR';
		break;

		case 'TOTALS_SALE_COMB':
			argRangedCheck();
			//pg_escape_string

			$warehouse_id = $_REQUEST['warehouse_id'];

			/**
			 * pos_trans
			 * fpago = 2
			 * td = 'B'
			 * td = 'F'
			 */
			$sql = "SELECT
				(
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS total_ventagalon, --0 cantidad
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) AS total_ventavalor, --1 soles
				C.codigo AS codigo, --2
				COST.costo_comb * (
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS costo, --3 costo promedio
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) / (1 + (COMB.igv/100)) AS venta_sin_igv --4 valor venta sin igv
			FROM
				(
					SELECT
						ch_codigocombustible AS codigo
					FROM
						comb_ta_tanques
					WHERE
						ch_sucursal = '$warehouse_id'
				) C
			INNER JOIN (
				SELECT
					comb.ch_codigocombustible AS codigo,
					cmb.ch_nombrecombustible AS descripcion,
					SUM (
						CASE 
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventavalor
						ELSE
							0
						END
					) AS total_venta,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventagalon
						ELSE
							0
						END
					) AS total_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							(comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_soles,
					ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
					nu_factor_igv AS igv
				FROM
					comb_ta_contometros comb
				LEFT JOIN comb_ta_combustibles cmb ON (
					comb.ch_codigocombustible = cmb.ch_codigocombustible
				)
				WHERE
					comb.dt_fechaparte BETWEEN '$BeginDate'
				AND '$EndDate'
				AND comb.ch_sucursal = TRIM ('$warehouse_id')
				GROUP BY
					comb.ch_codigocombustible,
					cmb.ch_nombrecombustible,
					comb.nu_factor_igv
			) COMB ON COMB.codigo = C .codigo
			LEFT JOIN (
				SELECT
					af.codigo AS codigo,
					SUM (af.importe) AS af_total,
					ROUND(SUM(af.cantidad), 3) AS af_cantidad
				FROM
					pos_ta_afericiones af
				WHERE
					af.dia BETWEEN '$BeginDate'
				AND '$EndDate'
				AND af.es = TRIM ('$warehouse_id')
				GROUP BY
					af.codigo
			) AFC ON AFC.codigo = C .codigo
			LEFT JOIN (
					SELECT stk_costo$BeginMonth as costo_comb, art_codigo from inv_saldoalma where stk_periodo = '$BeginYear'
					AND stk_almacen = TRIM ('$warehouse_id')
			) COST ON (COST.art_codigo =  C.codigo)
			UNION ALL
			SELECT
				tot_cantidad AS total_ventagalon,
				CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
					tot_surtidor_soles
				ELSE
					tot_surtidor_soles - tot_afericion
				END
				AS total_ventavalor,
				'11620308' AS codigo,
				nu_costo_unitario*tot_cantidad AS costo,
				CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
					tot_surtidor_soles
				ELSE
					tot_surtidor_soles - tot_afericion
				END / (1 + (util_fn_igv()/100)) AS venta_sin_igv
			FROM comb_liquidaciongnv
			WHERE ch_almacen = '$warehouse_id'
				AND dt_fecha BETWEEN '$BeginDate' AND '$EndDate';";

				//ultima compra del mes (inv_saldoalma)

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'TOTALS_SALE_MARKET':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];

			$sql = "
SELECT
 SUM(nu_fac_importeneto) AS nu_venta_soles,
 SUM(nu_fac_cantidad) AS nu_cantidad,
 SUM(ss_kardex_promedio_total) AS nu_costo_total,
 SUM(nu_fac_importeneto) - SUM(ss_kardex_promedio_total) AS nu_margen
FROM
 (SELECT
 d.nu_fac_cantidad,
 d.nu_fac_importeneto,
 (ROUND
  ((CASE
   WHEN sal.stk_costo" . $BeginMonth . " = 0.0000 THEN
    COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '" . $BeginYear . "-" . $BeginMonth . "-01 00:00:00' AND art_codigo = d.art_codigo GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
   ELSE
    sal.stk_costo" . $BeginMonth . "
   END),2)
  *
  ROUND(d.nu_fac_cantidad, 2)
 ) AS ss_kardex_promedio_total
FROM
 fac_ta_factura_cabecera AS c
 RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
 JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
 LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = d.art_codigo AND sal.stk_periodo = '" . $BeginYear . "')
WHERE
 c.ch_fac_tipodocumento='45'
 AND c.ch_almacen = '" . $warehouse_id . "'
 AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
 AND art.art_unidad NOT IN('000GLN', '0000GL')
) AS TOT_MARKET;
			";

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'DETAIL_SALE_COMB':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$sql = "SELECT
				C .codigo AS codigo,--0
				COMB.descripcion AS descripcion,--1
				ROUND(COMB.total_cantidad, 3) AS total_cantidad,--2
				ROUND(COMB.total_venta, 2) AS total_venta,--3
				(
					CASE
					WHEN AFC.af_cantidad IS NULL THEN
						COMB.af_cantidad
					ELSE
						AFC.af_cantidad
					END
				) AS af_cantidad,--4
				(
					CASE
					WHEN AFC.af_total IS NULL THEN
						COMB.af_soles
					ELSE
						AFC.af_total
					END
				) AS af_total,--5
				--0.000 AS consumo_galon,--
				--0.000 AS consumo_valor,--

				COST.costo_comb * (
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS costo, -- costo promedio 6
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) / (1 + (COMB.igv/100)) AS venta_sin_igv, --7 valor venta sin igv

				COMB.descuentos AS descuentos,--8
				(
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS neto_cantidad,--9
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) AS neto_soles--10
			FROM
				(
					SELECT
						ch_codigocombustible AS codigo
					FROM
						comb_ta_tanques
					WHERE ch_sucursal = '$warehouse_id'
				) C
			INNER JOIN (
				SELECT
					comb.ch_codigocombustible AS codigo,
					cmb.ch_nombrecombustible AS descripcion,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventavalor
						ELSE
							0
						END
					) AS total_venta,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventagalon
						ELSE
							0
						END
					) AS total_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							(comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_soles,
					ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
					nu_factor_igv AS igv
				FROM
					comb_ta_contometros comb
				LEFT JOIN comb_ta_combustibles cmb ON (
					comb.ch_codigocombustible = cmb.ch_codigocombustible
				)
				WHERE
					comb.dt_fechaparte BETWEEN '$BeginDate'
				AND '$EndDate'
				AND comb.ch_sucursal = TRIM ('$warehouse_id')
				GROUP BY
					comb.ch_codigocombustible,
					cmb.ch_nombrecombustible,
					comb.nu_factor_igv
			) COMB ON COMB.codigo = C .codigo
			LEFT JOIN (
				SELECT
					af.codigo AS codigo,
					SUM (af.importe) AS af_total,
					ROUND(SUM(af.cantidad), 3) AS af_cantidad
				FROM
					pos_ta_afericiones af
				WHERE
					af.dia BETWEEN '$BeginDate'
				AND '$EndDate'
				AND af.es = TRIM ('$warehouse_id')
				GROUP BY
					af.codigo
			) AFC ON AFC.codigo = C .codigo
			LEFT JOIN (
				SELECT stk_costo$BeginMonth as costo_comb, art_codigo from inv_saldoalma where stk_periodo = '$BeginYear'
				AND stk_almacen = TRIM ('$warehouse_id')
			) COST ON (COST.art_codigo =  C.codigo)
			UNION ALL
			SELECT
			'11620308' AS codigo,
			'GNV' AS descripcion,
			SUM(0) AS total_cantidad,
			SUM(0) AS total_venta,
			SUM(0) AS af_cantidad,
			SUM(0) AS af_total,
			SUM(nu_costo_unitario*tot_cantidad) AS costo,
			SUM( CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
				tot_surtidor_soles
			ELSE
				tot_surtidor_soles - tot_afericion
			END / (1 + (util_fn_igv()/100)) ) AS venta_sin_igv,
			SUM(0) AS descuentos,
			SUM(tot_cantidad) AS neto_cantidad,
			SUM(CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
				tot_surtidor_soles
			ELSE
				tot_surtidor_soles - tot_afericion
			END)
			AS neto_soles
			FROM comb_liquidaciongnv
			WHERE ch_almacen = '$warehouse_id'
			AND dt_fecha BETWEEN '$BeginDate' AND '$EndDate';";

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'DETAIL_SALE_MARKET':
			//corregido
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];

			$sql = "
SELECT
 codigo_linea AS co_linea,
 nombre_linea AS no_linea,
 nu_fac_cantidad AS nu_cantidad,
 0.0 AS nu_costo_promedio,
 ss_kardex_promedio_total AS nu_costo_total,
 nu_fac_importeneto AS nu_venta_soles,
 nu_fac_importeneto - ss_kardex_promedio_total AS nu_margen
FROM
 (SELECT
  linea.tab_elemento AS codigo_linea,
  FIRST(linea.tab_descripcion) AS nombre_linea,
  SUM(d.nu_fac_cantidad) AS nu_fac_cantidad,
  SUM(d.nu_fac_importeneto) AS nu_fac_importeneto,
  ROUND((COALESCE(FIRST(ITEMEST.ss_kardex_promedio_total), 0) + COALESCE(FIRST(ITEMPLU.ss_kardex_promedio_total), 0)), 2) AS ss_kardex_promedio_total
 FROM
  fac_ta_factura_cabecera AS c
  RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
  JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
  LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = d.art_codigo AND sal.stk_periodo = '" . $BeginYear . "')
  LEFT JOIN int_tabla_general AS linea ON(linea.tab_tabla = '20' AND art.art_linea = linea.tab_elemento AND linea.tab_elemento != '000000')--LINEA
  LEFT JOIN (
  SELECT
   art_linea,
   SUM(ss_kardex_promedio_total) AS ss_kardex_promedio_total
  FROM
   (SELECT
    art.art_linea,
    ROUND(
    CASE WHEN sal.stk_costo" . $BeginMonth . " = 0.0000 THEN
    COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '" . $BeginYear . "-" . $BeginMonth . "-01 00:00:00' AND art_codigo = d.art_codigo GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
    ELSE
    sal.stk_costo" . $BeginMonth . "
    END
    , 2) * ROUND(d.nu_fac_cantidad, 2) AS ss_kardex_promedio_total
   FROM
    fac_ta_factura_cabecera AS c
    RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
    JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
    LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = d.art_codigo AND sal.stk_periodo = '" . $BeginYear . "')
   WHERE
    c.ch_fac_tipodocumento='45'
    AND c.ch_almacen = '" . $warehouse_id . "'
    AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
    AND art.art_plutipo='1'
   ) AS EST
  GROUP BY
   1
  ) AS ITEMEST ON(ITEMEST.art_linea = art.art_linea)
  LEFT JOIN (
  SELECT
   art_linea,
   SUM(ss_kardex_promedio_total) AS ss_kardex_promedio_total
  FROM
   (SELECT
    art.art_linea,
    ROUND(
    CASE WHEN sal.stk_costo" . $BeginMonth . " = 0.0000 THEN
    COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '" . $BeginYear . "-" . $BeginMonth . "-01 00:00:00' AND art_codigo = ENLA.ch_item_estandar GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
    ELSE
    sal.stk_costo" . $BeginMonth . "
    END
    , 2) * ROUND(d.nu_fac_cantidad * COALESCE(ENLA.nu_cantidad_descarga,1), 2) AS ss_kardex_promedio_total
   FROM
    fac_ta_factura_cabecera AS c
    RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
    JOIN int_ta_enlace_items AS ENLA ON(ENLA.art_codigo = d.art_codigo)
    JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
    LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = ENLA.ch_item_estandar AND sal.stk_periodo = '" . $BeginYear . "')
   WHERE
    c.ch_fac_tipodocumento='45'
    AND c.ch_almacen = '" . $warehouse_id . "'
    AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
    AND art.art_plutipo='2'
   ) AS PLU
  GROUP BY
   1
  ) AS ITEMPLU ON(ITEMPLU.art_linea = art.art_linea)
 WHERE
  c.ch_fac_tipodocumento='45'
  AND c.ch_almacen = '" . $warehouse_id . "'
  AND art.art_unidad NOT IN('000GLN', '0000GL')
  AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
 GROUP BY
  linea.tab_elemento
) AS DETAIL_MARKET
ORDER BY
nu_margen DESC
			";

/*
			$sql = "
SELECT
 codigo_linea AS co_linea,
 nombre_linea AS no_linea,
 nu_fac_cantidad AS nu_cantidad,
 0.0 AS nu_costo_promedio,
 ss_kardex_promedio_total AS nu_costo_total,
 nu_fac_importeneto AS nu_venta_soles,
 nu_fac_importeneto - ss_kardex_promedio_total AS nu_margen
FROM
 (SELECT
  linea.tab_elemento AS codigo_linea,
  FIRST(linea.tab_descripcion) AS nombre_linea,
  SUM(d.nu_fac_cantidad) AS nu_fac_cantidad,
  SUM(d.nu_fac_importeneto) AS nu_fac_importeneto,
  (ROUND
   ((CASE
    WHEN FIRST(sal.stk_costo" . $BeginMonth . ") = 0.0000 THEN
     COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '" . $BeginYear . "-" . $BeginMonth . "-01 00:00:00' AND art_codigo = FIRST(d.art_codigo) GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
    ELSE
     FIRST(sal.stk_costo" . $BeginMonth . ")
    END),2)
   *
   ROUND(SUM(d.nu_fac_cantidad), 2)
  ) AS ss_kardex_promedio_total
 FROM
  fac_ta_factura_cabecera AS c
  RIGHT JOIN fac_ta_factura_detalle AS d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
  JOIN int_articulos AS art ON (art.art_codigo = d.art_codigo)
  LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = c.ch_almacen AND sal.art_codigo = d.art_codigo AND sal.stk_periodo = '" . $BeginYear . "')
  LEFT JOIN int_tabla_general AS linea ON(linea.tab_tabla = '20' AND art.art_linea = linea.tab_elemento AND linea.tab_elemento != '000000')--LINEA
 WHERE
  c.ch_fac_tipodocumento='45'
  AND c.ch_almacen = '" . $warehouse_id . "'
  AND c.dt_fac_fecha BETWEEN '" . $BeginDate . "' AND '" . $EndDate . "'
  AND art.art_unidad NOT IN('000GLN', '0000GL')
 GROUP BY
  linea.tab_elemento
 ) AS DETAIL_MARKET
 ORDER BY
  nu_margen DESC;
			";
*/

			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'STOCK_COMB':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$days = $_REQUEST['days'];

			$sql = "SELECT
				tanques.ch_codigocombustible AS cod_comb,
				combustibles.ch_nombrecombustible AS desc_comb,
				tanques.nu_capacidad,
				(SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days AS nu_venta,
				contometros.ch_tanque,
				mediciondiaria.nu_medicion,
				CASE
			WHEN tanques.nu_capacidad > 0 THEN
				(mediciondiaria.nu_medicion / tanques.nu_capacidad) * 100
			ELSE
				0
			END AS porcentaje_existente,
			 --mediciondiaria.nu_medicion / ((tanques.nu_capacidad - mediciondiaria.nu_medicion) / $days) AS dias,
			 mediciondiaria.nu_medicion / ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days) AS tiempo,
			 (SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) AS suma,
			 $days AS dia,
			 compra.mov_cantidad AS cantidad_ultima_compra,
			 compra.mov_fecha AS fecha_ultima_compra,
			 '$BeginDate' AS BeginDate,
			 '$EndDate' AS EndDate
			FROM
				comb_ta_contometros contometros
			JOIN comb_ta_tanques tanques ON (contometros.ch_tanque = tanques.ch_tanque AND tanques.ch_sucursal = '$warehouse_id' )
			LEFT JOIN comb_ta_mediciondiaria mediciondiaria ON (contometros.ch_tanque = mediciondiaria.ch_tanque AND mediciondiaria.ch_sucursal = '$warehouse_id' AND mediciondiaria.dt_fechamedicion = '$EndDate' )
			JOIN comb_ta_combustibles combustibles ON (tanques.ch_codigocombustible = combustibles.ch_codigocombustible)
			JOIN inv_ta_compras_devoluciones compra ON (TRIM(tanques.ch_codigocombustible) = TRIM(compra.art_codigo) AND compra.mov_fecha = (SELECT MAX(mov_fecha) FROM inv_ta_compras_devoluciones WHERE TRIM(art_codigo) = TRIM(tanques.ch_codigocombustible)))
			WHERE
				contometros.ch_sucursal = '$warehouse_id'
			AND contometros.dt_fechaparte BETWEEN '$BeginDate'
			AND '$EndDate'
			GROUP BY
				contometros.ch_tanque, tanques.ch_codigocombustible, tanques.nu_capacidad, mediciondiaria.nu_medicion, combustibles.ch_nombrecombustible,
				compra.mov_cantidad, compra.mov_fecha
			ORDER BY
				tanques.ch_codigocombustible ASC;";
			//echo $sql;
			SQLImplode($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'STOCK_COMB_R':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$days = $_REQUEST['days'];

			$sql = "SELECT
				tanques.ch_codigocombustible AS cod_comb,
				combustibles.ch_nombrecombustible AS desc_comb,
				tanques.nu_capacidad,
				(SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days AS nu_venta,
				contometros.ch_tanque,
				mediciondiaria.nu_medicion,
				CASE
			WHEN tanques.nu_capacidad > 0 THEN
				(mediciondiaria.nu_medicion / tanques.nu_capacidad) * 100
			ELSE
				0
			END AS porcentaje_existente,
			 --mediciondiaria.nu_medicion / ((tanques.nu_capacidad - mediciondiaria.nu_medicion) / $days) AS dias,
			 mediciondiaria.nu_medicion / ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / $days) AS tiempo,
			 (SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) AS suma,
			 $days AS dia,
			 compra.mov_cantidad AS cantidad_ultima_compra,
			 compra.mov_fecha AS fecha_ultima_compra,
			 '$BeginDate' AS BeginDate,
			 '$EndDate' AS EndDate
			FROM
				comb_ta_contometros contometros
			JOIN comb_ta_tanques tanques ON (contometros.ch_tanque = tanques.ch_tanque AND tanques.ch_sucursal = '$warehouse_id' )
			LEFT JOIN comb_ta_mediciondiaria mediciondiaria ON (contometros.ch_tanque = mediciondiaria.ch_tanque AND mediciondiaria.ch_sucursal = '$warehouse_id' AND mediciondiaria.dt_fechamedicion = '$EndDate' )
			JOIN comb_ta_combustibles combustibles ON (tanques.ch_codigocombustible = combustibles.ch_codigocombustible)
			JOIN inv_ta_compras_devoluciones compra ON (TRIM(tanques.ch_codigocombustible) = TRIM(compra.art_codigo) AND compra.mov_fecha = (SELECT MAX(mov_fecha) FROM inv_ta_compras_devoluciones WHERE TRIM(art_codigo) = TRIM(tanques.ch_codigocombustible)))
			WHERE
				contometros.ch_sucursal = '$warehouse_id'
			AND contometros.dt_fechaparte BETWEEN '$BeginDate'
			AND '$EndDate'
			GROUP BY
				contometros.ch_tanque, tanques.ch_codigocombustible, tanques.nu_capacidad, mediciondiaria.nu_medicion, combustibles.ch_nombrecombustible,
				compra.mov_cantidad, compra.mov_fecha
			ORDER BY
				tanques.ch_codigocombustible ASC;";
			//echo $sql;
			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		/**
		 * Demo de data serializada
		 */
		case 'DEMO_SERIAL':
			$sql = "SELECT 'Kewin' AS name, 'Serquen' AS surname, 'KWN' AS username;";
			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'DEMO_':
			$sql = "SELECT
				tanques.ch_codigocombustible AS cod_comb,
				combustibles.ch_nombrecombustible AS desc_comb,
				tanques.nu_capacidad,
				(SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / 7 AS nu_venta,
				contometros.ch_tanque,
				mediciondiaria.nu_medicion,
				CASE
			WHEN tanques.nu_capacidad > 0 THEN
				(mediciondiaria.nu_medicion / tanques.nu_capacidad) * 100
			ELSE
				0
			END AS porcentaje_existente,
			 --mediciondiaria.nu_medicion / ((tanques.nu_capacidad - mediciondiaria.nu_medicion) / 7) AS dias,
			 mediciondiaria.nu_medicion / ((SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) / 7) AS tiempo,
			 (SUM (contometros.nu_ventagalon) - SUM (contometros.nu_afericionveces_x_5 * 5)) AS suma,
			 7 AS dia,
			 compra.mov_cantidad AS cantidad_ultima_compra,
			 compra.mov_fecha AS fecha_ultima_compra,
			 '2017-02-05' AS BeginDate,
			 '2017-02-12' AS EndDate
			FROM
				comb_ta_contometros contometros
			JOIN comb_ta_tanques tanques ON (contometros.ch_tanque = tanques.ch_tanque AND tanques.ch_sucursal = '205' )
			LEFT JOIN comb_ta_mediciondiaria mediciondiaria ON (contometros.ch_tanque = mediciondiaria.ch_tanque AND mediciondiaria.ch_sucursal = '205' AND mediciondiaria.dt_fechamedicion = '2017-02-12' )
			JOIN comb_ta_combustibles combustibles ON (tanques.ch_codigocombustible = combustibles.ch_codigocombustible)
			JOIN inv_ta_compras_devoluciones compra ON (TRIM(tanques.ch_codigocombustible) = TRIM(compra.art_codigo) AND compra.mov_fecha = (SELECT MAX(mov_fecha) FROM inv_ta_compras_devoluciones WHERE TRIM(art_codigo) = TRIM(tanques.ch_codigocombustible)))
			WHERE
				contometros.ch_sucursal = '205'
			AND contometros.dt_fechaparte BETWEEN '2017-02-05'
			AND '2017-02-12'
			GROUP BY
				contometros.ch_tanque, tanques.ch_codigocombustible, tanques.nu_capacidad, mediciondiaria.nu_medicion, combustibles.ch_nombrecombustible,
				compra.mov_cantidad, compra.mov_fecha
			ORDER BY
				tanques.ch_codigocombustible ASC;";

			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		
		case 'TOTALS_SUMARY_SALE':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$sql = "SELECT
				C .codigo AS codigo,--0
				COMB.descripcion AS descripcion,--1
				ROUND(COMB.total_cantidad, 3) AS total_cantidad,--2
				ROUND(COMB.total_venta, 2) AS total_venta,--3
				(
					CASE
					WHEN AFC.af_cantidad IS NULL THEN
						COMB.af_cantidad
					ELSE
						AFC.af_cantidad
					END
				) AS af_cantidad,--4
				(
					CASE
					WHEN AFC.af_total IS NULL THEN
						COMB.af_soles
					ELSE
						AFC.af_total
					END
				) AS af_total,--5
				--0.000 AS consumo_galon,--
				--0.000 AS consumo_valor,--

				COST.costo_comb * (
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS costo, -- costo promedio 6
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) / (1 + (COMB.igv/100)) AS venta_sin_igv, --7 valor venta sin igv

				COMB.descuentos AS descuentos,--8
				(
					CASE
					WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
						(COMB.total_cantidad - COMB.af_cantidad)
					WHEN AFC.af_cantidad > 0 THEN
						(COMB.total_cantidad - AFC.af_cantidad)
					WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
						(COMB.total_cantidad)
					END
				) AS neto_cantidad,--9
				(
					CASE
					WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
						((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
					WHEN AFC.af_total > 0 THEN
						((COMB.total_venta + COMB.descuentos) - AFC.af_total)
					WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
						(COMB.total_venta + COMB.descuentos)
					END
				) AS neto_soles--10
				, gasto_interno.nu_importe as importe_ci
				, gasto_interno.nu_cantidad as cantidad_ci
			FROM
				(
					SELECT
						ch_codigocombustible AS codigo
					FROM
						comb_ta_tanques
					WHERE ch_sucursal = '$warehouse_id'
				) C
			INNER JOIN (
				SELECT
					comb.ch_codigocombustible AS codigo,
					cmb.ch_nombrecombustible AS descripcion,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventavalor
						ELSE
							0
						END
					) AS total_venta,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							comb.nu_ventagalon
						ELSE
							0
						END
					) AS total_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							(comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_cantidad,
					SUM (
						CASE
						WHEN comb.nu_ventagalon > 0 THEN
							((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
						ELSE
							0
						END
					) AS af_soles,
					ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
					nu_factor_igv AS igv
				FROM
					comb_ta_contometros comb
				LEFT JOIN comb_ta_combustibles cmb ON (
					comb.ch_codigocombustible = cmb.ch_codigocombustible
				)
				WHERE
					comb.dt_fechaparte BETWEEN '$BeginDate'
				AND '$EndDate'
				AND comb.ch_sucursal = TRIM ('$warehouse_id')
				GROUP BY
					comb.ch_codigocombustible,
					cmb.ch_nombrecombustible,
					comb.nu_factor_igv
			) COMB ON COMB.codigo = C .codigo
			LEFT JOIN (
				SELECT
					af.codigo AS codigo,
					SUM (af.importe) AS af_total,
					ROUND(SUM(af.cantidad), 3) AS af_cantidad
				FROM
					pos_ta_afericiones af
				WHERE
					af.dia BETWEEN '$BeginDate'
				AND '$EndDate'
				AND af.es = TRIM ('$warehouse_id')
				GROUP BY
					af.codigo
			) AFC ON AFC.codigo = C .codigo
			LEFT JOIN (
				SELECT stk_costo$BeginMonth as costo_comb, art_codigo from inv_saldoalma where stk_periodo = '$BeginYear'
				AND stk_almacen = TRIM ('$warehouse_id')
			) COST ON (COST.art_codigo =  C.codigo)
			LEFT JOIN (
				SELECT
					detalle.ch_articulo, SUM(detalle.nu_importe) AS nu_importe, SUM(detalle.nu_cantidad) AS nu_cantidad
				FROM
					val_ta_cabecera cabecera
				JOIN int_clientes clientes ON (
					cabecera.ch_cliente = clientes.cli_ruc
				)
				JOIN val_ta_detalle detalle ON (
					cabecera.ch_documento = detalle.ch_documento AND cabecera.ch_sucursal = detalle.ch_sucursal AND cabecera.dt_fecha = detalle.dt_fecha
				)
				JOIN int_ta_sucursales sucursales ON (
					clientes.cli_ruc = sucursales.ruc
				)
				WHERE
				sucursales.ch_sucursal = TRIM('$warehouse_id')
				AND detalle.dt_fecha BETWEEN '$BeginDate' AND '$EndDate'
				--AND detalle.ch_articulo = TRIM('11620304')
				GROUP BY detalle.ch_articulo
			) gasto_interno ON ( C.codigo = gasto_interno.ch_articulo)
			UNION ALL
			SELECT
			'11620308' AS codigo,
			'GNV' AS descripcion,
			SUM(0) AS total_cantidad,
			SUM(0) AS total_venta,
			SUM(0) AS af_cantidad,
			SUM(0) AS af_total,
			SUM(nu_costo_unitario*tot_cantidad) AS costo,
			SUM( CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
				tot_surtidor_soles
			ELSE
				tot_surtidor_soles - tot_afericion
			END / (1 + (util_fn_igv()/100)) ) AS venta_sin_igv,
			SUM(0) AS descuentos,
			SUM(tot_cantidad) AS neto_cantidad,
			SUM(CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
				tot_surtidor_soles
			ELSE
				tot_surtidor_soles - tot_afericion
			END)
			AS neto_soles
			, SUM(0) AS importe_ci
			, SUM(0) AS cantidad_ci
			FROM comb_liquidaciongnv
			WHERE ch_almacen = '$warehouse_id'
			AND dt_fecha BETWEEN '$BeginDate' AND '$EndDate';";
			error_log( $sql );

			if(isset($_REQUEST['unserialize'])) {
				SQLImplodeSerialize($sql, true);
			} else {
				SQLImplode($sql);
			}

			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);

			error_log($contenido);
			error_log($comprimido);
			
			echo $comprimido;
		break;

		case 'TOTALS_SALE_FOR_HOURS':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$desde = $_REQUEST['desde'];
			$hasta = $_REQUEST['hasta'];
			$local = $_REQUEST['local'];
			$producto = $_REQUEST['productos'];
			$unidadmedida = $_REQUEST['unidadmedida'];
			$factor = 3.785411784;
			
			$sql = "SELECT  
						es as ch_sucursal,
						importe as nu_ventavalor,

						CASE
							WHEN TRIM(codigo) = '11620307' AND 'GALONES' = '$unidadmedida' THEN cantidad/$factor
							ELSE cantidad
						END AS nu_ventagalon,

						'0.00' nu_afericion,
						precio as nu_preciogalon,
						TRIM(codigo) as ch_codigocombustible,
						fecha::DATE as dt_fechaparte,
						EXTRACT(HOUR FROM fecha),
						Case EXTRACT(DOW FROM fecha)
							when 0 then 'DOMINGO'
							when 1 then 'LUNES'
							when 2 then 'MARTES'
							when 3 then 'MIERCOLES'
							when 4 then 'JUEVES'
							when 5 then 'VIERNES'
							when 6 then 'SABADO'
						end as dia ,
						pump AS lado,
						tipo,
						
						(SELECT
							ch_almacen || ' - ' || trim(ch_nombre_almacen)
						FROM
							inv_ta_almacenes
						WHERE
							ch_clase_almacen='1'
							AND ch_almacen = es
						ORDER BY
							ch_almacen) as ch_almacen_completo

						".
					" 
					FROM pos_trans". substr($desde,6,4) . substr($desde,3,2) . " ".
					"
					WHERE
						dia::DATE between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')  
						--AND importe > 0
						--AND cantidad > 0 
					";
			if ($local == "COMBUSTIBLE") {
				$sql .= " AND tipo='C'";

				if ($producto != "TODOS") {
					$sql .= " AND codigo='" . pg_escape_string($producto) . "'";
				}
			} else if ($local == "MARKET") {
				$sql .= " AND tipo='M'";
			}

			// $sql .= " 
			// 	ORDER BY
			// 		ch_sucursal,
			// 		dt_fechaparte
			// 	";

			/*UNION*/
			$sql .= "
					UNION ALL
					";
			$sql .= "SELECT  
						es as ch_sucursal,
						importe as nu_ventavalor,
						
						CASE
							WHEN TRIM(codigo) = '11620307' AND 'GALONES' = '$unidadmedida' THEN cantidad/$factor
							ELSE cantidad
						END AS nu_ventagalon,

						'0.00' nu_afericion,
						precio as nu_preciogalon,
						TRIM(codigo) as ch_codigocombustible,
						fecha::DATE as dt_fechaparte,
						EXTRACT(HOUR FROM fecha),
						Case EXTRACT(DOW FROM fecha)
							when 0 then 'DOMINGO'
							when 1 then 'LUNES'
							when 2 then 'MARTES'
							when 3 then 'MIERCOLES'
							when 4 then 'JUEVES'
							when 5 then 'VIERNES'
							when 6 then 'SABADO'
						end as dia ,
						pump AS lado,
						tipo,
						
						(SELECT
							ch_almacen || ' - ' || trim(ch_nombre_almacen)
						FROM
							inv_ta_almacenes
						WHERE
							ch_clase_almacen='1'
							AND ch_almacen = es
						ORDER BY
							ch_almacen) as ch_almacen_completo

						".
					" 
					FROM pos_transtmp"." ".
					"
					WHERE
						dia::DATE between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')  
						--AND importe > 0
						--AND cantidad > 0 
					";
			if ($local == "COMBUSTIBLE") {
				$sql .= " AND tipo='C'";

				if ($producto != "TODOS") {
					$sql .= " AND codigo='" . pg_escape_string($producto) . "'";
				}
			} else if ($local == "MARKET") {
				$sql .= " AND tipo='M'";
			}

			$sql .= " 
				ORDER BY
					ch_sucursal,
					dt_fechaparte
				;";
			/*CERRAR UNION*/
			
			error_log( $sql );

			if(isset($_REQUEST['unserialize'])) {
				SQLImplodeSerialize($sql, true);
			} else {
				SQLImplode($sql);
			}

			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = ($contenido); //$comprimido = gzcompress($contenido);

			error_log($contenido);
			error_log($comprimido);
			
			echo $comprimido;
		break;

		case 'TOTALS_STATISTICS_SALE':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$sql = "SELECT
			_result._type,
			_result.codigo,
			_result.descripcion,
			round(_result.neto_cantidad, 2) AS neto_cantidad,
			round(_result.neto_soles, 2) AS neto_venta,
			round(_result.importe_ci, 2) AS importe_ci,
			round(_result.cantidad_ci, 2) AS cantidad_ci FROM (
			SELECT
							'anterior' as _type,
							C .codigo AS codigo,--0
							COMB.descripcion AS descripcion,--1
							(
								CASE
								WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
									(COMB.total_cantidad - COMB.af_cantidad)
								WHEN AFC.af_cantidad > 0 THEN
									(COMB.total_cantidad - AFC.af_cantidad)
								WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
									(COMB.total_cantidad)
								END
							) AS neto_cantidad,--9
							(
								CASE
								WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
									((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
								WHEN AFC.af_total > 0 THEN
									((COMB.total_venta + COMB.descuentos) - AFC.af_total)
								WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
									(COMB.total_venta + COMB.descuentos)
								END
							) AS neto_soles--10
							, gasto_interno.nu_importe as importe_ci
							, gasto_interno.nu_cantidad as cantidad_ci
						FROM
							(
								SELECT
									ch_codigocombustible AS codigo
								FROM
									comb_ta_tanques
								WHERE ch_sucursal = '$warehouse_id'
							) C
						INNER JOIN (
							SELECT
								comb.ch_codigocombustible AS codigo,
								cmb.ch_nombrecombustible AS descripcion,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										comb.nu_ventavalor
									ELSE
										0
									END
								) AS total_venta,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										comb.nu_ventagalon
									ELSE
										0
									END
								) AS total_cantidad,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										(comb.nu_afericionveces_x_5 * 5)
									ELSE
										0
									END
								) AS af_cantidad,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
									ELSE
										0
									END
								) AS af_soles,
								ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
								nu_factor_igv AS igv
							FROM
								comb_ta_contometros comb
							LEFT JOIN comb_ta_combustibles cmb ON (
								comb.ch_codigocombustible = cmb.ch_codigocombustible
							)
							WHERE
								comb.dt_fechaparte BETWEEN '$_BeginDate' AND '$_EndDate'
							AND comb.ch_sucursal = TRIM ('$warehouse_id')
							GROUP BY
								comb.ch_codigocombustible,
								cmb.ch_nombrecombustible,
								comb.nu_factor_igv
						) COMB ON COMB.codigo = C .codigo
						LEFT JOIN (
							SELECT
								af.codigo AS codigo,
								SUM (af.importe) AS af_total,
								ROUND(SUM(af.cantidad), 3) AS af_cantidad
							FROM
								pos_ta_afericiones af
							WHERE
								af.dia BETWEEN '$_BeginDate' AND '$_EndDate'
							AND af.es = TRIM ('$warehouse_id')
							GROUP BY
								af.codigo
						) AFC ON AFC.codigo = C .codigo
						LEFT JOIN (
							SELECT
								detalle.ch_articulo, SUM(detalle.nu_importe) AS nu_importe, SUM(detalle.nu_cantidad) AS nu_cantidad
							FROM
								val_ta_cabecera cabecera
							JOIN int_clientes clientes ON (
								cabecera.ch_cliente = clientes.cli_ruc
							)
							JOIN val_ta_detalle detalle ON (
								cabecera.ch_documento = detalle.ch_documento AND cabecera.ch_sucursal = detalle.ch_sucursal AND cabecera.dt_fecha = detalle.dt_fecha
							)
							JOIN int_ta_sucursales sucursales ON (
								clientes.cli_ruc = sucursales.ruc
							)
							WHERE
							sucursales.ch_sucursal = TRIM('$warehouse_id') AND
							detalle.dt_fecha BETWEEN '$_BeginDate' AND '$_EndDate'
							--AND detalle.ch_articulo = TRIM('11620304')
							GROUP BY detalle.ch_articulo
						) gasto_interno ON (C.codigo = gasto_interno.ch_articulo)
						
						UNION ALL
						SELECT
						'anterior' as _type,
						'11620308' AS codigo,
						'GNV' AS descripcion,
						SUM(tot_cantidad) AS neto_cantidad,
						SUM(CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
							tot_surtidor_soles
						ELSE
							tot_surtidor_soles - tot_afericion
						END)
						AS neto_soles
						, SUM(0) AS importe_ci
						, SUM(0) AS cantidad_ci
						FROM comb_liquidaciongnv
						WHERE ch_almacen = '$warehouse_id'
						AND dt_fecha BETWEEN '$_BeginDate' AND '$_EndDate'

						UNION ALL

						SELECT
							'anterior' as _type,
							'MARKET' AS codigo,
							'MKT' AS descripcion,
							SUM(MOVIALMA.mov_cantidad) AS neto_cantidad,
							SUM(PT.importe) AS neto_soles
							, SUM(0) AS importe_ci
							, SUM(0) AS cantidad_ci
						FROM
							inv_movialma AS MOVIALMA
							JOIN int_articulos AS ART ON (MOVIALMA.art_codigo = ART.art_codigo)
							LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
							JOIN (
								SELECT
									es,
									dia,
									PT.codigo,
									SUM(importe) AS importe
								FROM
									pos_trans$_BeginYear$_BeginMonth PT
									JOIN int_articulos AS ART ON (PT.codigo = ART.art_codigo)
									LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
								WHERE
									--PT.es       = '$warehouse_id' AND
									ART.art_plutipo   = '1'
									AND ART.art_unidad NOT IN('000GLN', '0000GL')
									AND PT.dia::DATE BETWEEN '$_BeginDate' AND '$_EndDate'
								GROUP BY
									1,
									2,
									3
							) AS PT ON (PT.es = MOVIALMA.mov_almacen AND MOVIALMA.mov_fecha::DATE = PT.dia AND PT.codigo = MOVIALMA.art_codigo)
						WHERE
							--MOVIALMA.mov_almacen             = '$warehouse_id' AND
							MOVIALMA.tran_codigo         = '45'
							AND ART.art_plutipo              = '1'
							AND ART.art_unidad NOT IN('000GLN', '0000GL')
							AND MOVIALMA.mov_fecha BETWEEN '$_BeginDate' AND '$_EndDate'


			UNION
			SELECT
							'actual' as _type,
							C .codigo AS codigo,--0
							COMB.descripcion AS descripcion,--1
							(
								CASE
								WHEN AFC.af_cantidad IS NULL AND COMB.af_cantidad > 0 THEN
									(COMB.total_cantidad - COMB.af_cantidad)
								WHEN AFC.af_cantidad > 0 THEN
									(COMB.total_cantidad - AFC.af_cantidad)
								WHEN AFC.af_cantidad IS NULL OR COMB.af_cantidad = 0 THEN
									(COMB.total_cantidad)
								END
							) AS neto_cantidad,--9
							(
								CASE
								WHEN AFC.af_total IS NULL AND COMB.af_soles > 0 THEN
									((COMB.total_venta + COMB.descuentos) - COMB.af_soles)
								WHEN AFC.af_total > 0 THEN
									((COMB.total_venta + COMB.descuentos) - AFC.af_total)
								WHEN AFC.af_total IS NULL OR COMB.af_soles = 0 THEN
									(COMB.total_venta + COMB.descuentos)
								END
							) AS neto_soles--10
							, gasto_interno.nu_importe as importe_ci
							, gasto_interno.nu_cantidad as cantidad_ci
						FROM
							(
								SELECT
									ch_codigocombustible AS codigo
								FROM
									comb_ta_tanques
								WHERE ch_sucursal = '$warehouse_id'
							) C
						INNER JOIN (
							SELECT
								comb.ch_codigocombustible AS codigo,
								cmb.ch_nombrecombustible AS descripcion,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										comb.nu_ventavalor
									ELSE
										0
									END
								) AS total_venta,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										comb.nu_ventagalon
									ELSE
										0
									END
								) AS total_cantidad,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										(comb.nu_afericionveces_x_5 * 5)
									ELSE
										0
									END
								) AS af_cantidad,
								SUM (
									CASE
									WHEN comb.nu_ventagalon > 0 THEN
										((comb.nu_ventavalor / comb.nu_ventagalon) * comb.nu_afericionveces_x_5 * 5)
									ELSE
										0
									END
								) AS af_soles,
								ROUND(SUM(comb.nu_descuentos), 2) AS descuentos,
								nu_factor_igv AS igv
							FROM
								comb_ta_contometros comb
							LEFT JOIN comb_ta_combustibles cmb ON (
								comb.ch_codigocombustible = cmb.ch_codigocombustible
							)
							WHERE
								comb.dt_fechaparte BETWEEN '$BeginDate' AND '$EndDate'
							AND comb.ch_sucursal = TRIM ('$warehouse_id')
							GROUP BY
								comb.ch_codigocombustible,
								cmb.ch_nombrecombustible,
								comb.nu_factor_igv
						) COMB ON COMB.codigo = C .codigo
						LEFT JOIN (
							SELECT
								af.codigo AS codigo,
								SUM (af.importe) AS af_total,
								ROUND(SUM(af.cantidad), 3) AS af_cantidad
							FROM
								pos_ta_afericiones af
							WHERE
								af.dia BETWEEN '$BeginDate' AND '$EndDate'
							AND af.es = TRIM ('$warehouse_id')
							GROUP BY
								af.codigo
						) AFC ON AFC.codigo = C .codigo
						LEFT JOIN (
							SELECT
								detalle.ch_articulo, SUM(detalle.nu_importe) AS nu_importe, SUM(detalle.nu_cantidad) AS nu_cantidad
							FROM
								val_ta_cabecera cabecera
							JOIN int_clientes clientes ON (
								cabecera.ch_cliente = clientes.cli_ruc
							)
							JOIN val_ta_detalle detalle ON (
								cabecera.ch_documento = detalle.ch_documento AND cabecera.ch_sucursal = detalle.ch_sucursal AND cabecera.dt_fecha = detalle.dt_fecha
							)
							JOIN int_ta_sucursales sucursales ON (
								clientes.cli_ruc = sucursales.ruc
							)
							WHERE
							sucursales.ch_sucursal = TRIM('$warehouse_id') AND
							detalle.dt_fecha BETWEEN '$BeginDate' AND '$EndDate'
							--AND detalle.ch_articulo = TRIM('11620304')
							GROUP BY detalle.ch_articulo
						) gasto_interno ON (C.codigo = gasto_interno.ch_articulo)

						UNION ALL
						SELECT
						'actual' as _type,
						'11620308' AS codigo,
						'GNV' AS descripcion,
						SUM(tot_cantidad) AS neto_cantidad,
						SUM(CASE WHEN tot_afericion IS NULL OR tot_afericion <= 0 THEN
							tot_surtidor_soles
						ELSE
							tot_surtidor_soles - tot_afericion
						END)
						AS neto_soles
						, SUM(0) AS importe_ci
						, SUM(0) AS cantidad_ci
						FROM comb_liquidaciongnv
						WHERE ch_almacen = '$warehouse_id'
						AND dt_fecha BETWEEN '$BeginDate' AND '$EndDate'

						UNION ALL

						SELECT
							'actual' as _type,
							'MARKET' AS codigo,
							'MKT' AS descripcion,
							SUM(MOVIALMA.mov_cantidad) AS neto_cantidad,
							SUM(PT.importe) AS neto_soles
							, SUM(0) AS importe_ci
							, SUM(0) AS cantidad_ci
						FROM
							inv_movialma AS MOVIALMA
							JOIN int_articulos AS ART ON (MOVIALMA.art_codigo = ART.art_codigo)
							LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
							JOIN (
								SELECT
									es,
									dia,
									PT.codigo,
									SUM(importe) AS importe
								FROM
									pos_trans$BeginYear$BeginMonth PT
									JOIN int_articulos AS ART ON (PT.codigo = ART.art_codigo)
									LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
								WHERE
									--PT.es       = '$warehouse_id' AND
									ART.art_plutipo   = '1'
									AND ART.art_unidad NOT IN('000GLN', '0000GL')
									AND PT.dia::DATE BETWEEN '$BeginDate' AND '$EndDate'
								GROUP BY
									1,
									2,
									3
							) AS PT ON (PT.es = MOVIALMA.mov_almacen AND MOVIALMA.mov_fecha::DATE = PT.dia AND PT.codigo = MOVIALMA.art_codigo)
						WHERE
							--MOVIALMA.mov_almacen             = '$warehouse_id' AND
							MOVIALMA.tran_codigo         = '45'
							AND ART.art_plutipo              = '1'
							AND ART.art_unidad NOT IN('000GLN', '0000GL')
							AND MOVIALMA.mov_fecha BETWEEN '$BeginDate' AND '$EndDate'

			)  as _result ORDER BY _result.codigo ASC, _result._type ASC
			;";

			//SQLImplode($sql);
			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

		case 'TOTALS_STATISTICS_SALE_':
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			/*
			$PosTransTable
			$_PosTransTable
			*/
			$sql = "SELECT * FROM 
				( SELECT 
						t.es, 
						'ANTERIOR'::text, 
						trim(t.codigo), 
						sum(t.cantidad) 
					FROM 
						$_PosTransTable t 
					WHERE 
						t.tm='V' 
						AND t.tipo='C'  and grupo!='D'
						AND t.dia BETWEEN '$_BeginDate' AND $_EndDate'
					GROUP BY 
						1,3 
				) AS A 
				UNION
				( SELECT 
						t.es, 
						'ACTUAL'::text, 
						trim(t.codigo), 
						sum(t.cantidad) 
					FROM 
						$PosTransTable t 
					WHERE 
						t.tm='V' 
						AND t.tipo='C'  and grupo!='D' 
						AND t.dia BETWEEN '$BeginDate' AND '$EndDate'
					GROUP BY 
						1,3 
				) 
				UNION
				( SELECT 
						t.es, 
						'ANTERIOR'::text, 
						lpad(art.art_tipo,6,'0'),  
						sum(t.cantidad) 
					FROM 
						$_PosTransTable t
						LEFT JOIN int_articulos art ON (art.art_codigo=t.codigo) 
					WHERE 
						tm='V' 
						AND tipo='M'
						AND dia BETWEEN '$_BeginDate' AND $_EndDate'
					GROUP BY 
						1,3 
				) 
				UNION
				( SELECT 
						t.es, 
						'ACTUAL'::text, 
						lpad(art.art_tipo,6,'0'), 
						sum(cantidad) 
					FROM 
						$PosTransTable t
						LEFT JOIN int_articulos art ON (art.art_codigo=t.codigo) 
					WHERE 
						tm='V' 
						AND tipo='M'
						AND dia BETWEEN '$BeginDate' AND '$EndDate'
					GROUP BY 
						1,3 
				)
			ORDER BY 1,2,3;";

			SQLImplodeSerialize($sql);
			$contenido = ob_get_contents();
			ob_end_clean();
			$comprimido = gzcompress($contenido);
			echo $comprimido;
		break;

	case 'DETAIL_PRODUCTS_LINE':
		//usado actualmete en market
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$line_id = $_REQUEST['line_id'];

			$sql = "SELECT
				LINEA.tab_elemento AS Co_Linea,
				LINEA.tab_descripcion AS No_Linea,
				ART.art_descripcion AS No_Producto,
				SUM(MOVIALMA.mov_cantidad) AS Nu_Cantidad,
				FIRST(SALDO.Nu_Costo_Promedio) AS Nu_Costo_Promedio,
				SUM(MOVIALMA.mov_costototal) AS Nu_Costo_Total,
				SUM(PT.importe) AS Nu_Venta_Soles,
				--ROUND(SUM(PT.importe / (1 + (util_fn_igv()/100))) - SUM(MOVIALMA.mov_costototal), 2) AS Nu_Margen
				ROUND(SUM(PT.importe / (1 + (util_fn_igv()/100))) - (
					CASE WHEN SUM(MOVIALMA.mov_costototal)::VARCHAR != '' OR SUM(MOVIALMA.mov_costototal) IS NOT NULL THEN
						SUM(MOVIALMA.mov_costototal)
					ELSE
					0
					END
				), 2) AS Nu_Margen
			FROM
				inv_movialma AS MOVIALMA
				JOIN int_articulos AS ART ON (MOVIALMA.art_codigo = ART.art_codigo)
				LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
				JOIN (
					SELECT
						es,
						dia,
						PT.codigo,
						SUM(importe) AS importe
					FROM
						pos_trans$BeginYear$BeginMonth PT
						JOIN int_articulos AS ART ON (PT.codigo = ART.art_codigo)
						LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
					WHERE
						PT.es = '$warehouse_id'
						AND ART.art_plutipo = '1'
						AND ART.art_unidad NOT IN('000GLN', '0000GL')
						AND PT.dia::DATE BETWEEN '$BeginDate' AND '$EndDate'
					GROUP BY
						1,
						2,
						3
				) AS PT ON (PT.es = MOVIALMA.mov_almacen AND MOVIALMA.mov_fecha::DATE = PT.dia AND PT.codigo = MOVIALMA.art_codigo)
				JOIN (
					SELECT
						stk_almacen,
						stk_periodo,
						SALDO.art_codigo,
						SUM(stk_costo$BeginMonth) AS Nu_Costo_Promedio
					FROM
						inv_saldoalma SALDO
						JOIN int_articulos AS ART ON (SALDO.art_codigo = ART.art_codigo)
						LEFT JOIN int_tabla_general AS LINEA ON(LINEA.tab_tabla = '20' AND ART.art_linea = LINEA.tab_elemento AND LINEA.tab_elemento != '000000')--LINEA
					WHERE
						stk_almacen = '$warehouse_id'
						AND stk_periodo = '$BeginYear'
						AND ART.art_plutipo = '1'
						AND ART.art_unidad NOT IN('000GLN', '0000GL')
					GROUP BY
						1,
						2,
						3
				) AS SALDO ON (SALDO.stk_almacen = MOVIALMA.mov_almacen AND SALDO.stk_periodo = SUBSTRING(MOVIALMA.mov_fecha::TEXT, 1, 4) AND SALDO.art_codigo = MOVIALMA.art_codigo)
			WHERE
				MOVIALMA.mov_almacen = '$warehouse_id'
				AND MOVIALMA.tran_codigo = '45'
				AND ART.art_plutipo = '1'
				AND ART.art_unidad NOT IN('000GLN', '0000GL')
				AND MOVIALMA.mov_fecha BETWEEN '$BeginDate' AND '$EndDate'
				AND LINEA.tab_elemento = '$line_id'
			GROUP BY
				Co_Linea,
				No_Linea,
				art.art_descripcion
			ORDER BY
				nu_venta_soles DESC-- LIMIT 3
			;";

			$data = SQLImplodeJSON($sql);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		case 'UTILITY_LINES':
			/**
			 * ventas/market_productos_linea
			 * Busqueda por estacion(Resumen)
			 */
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];

			$sql = "SELECT
codigo_linea as co_linea,
FIRST(nombre_linea) as no_linea,
SUM(qt_cantidad) AS nu_cantidad,
0.0 AS nu_costo_promedio,
SUM(ss_kardex_promedio_total) AS nu_costo_total,
SUM(ss_tickets_sigv_total) AS nu_venta_soles,
SUM(ss_tickets_sigv_total) - SUM(ss_kardex_promedio_total) AS nu_margen
FROM
(SELECT
 linea.tab_elemento AS codigo_linea,
 linea.tab_descripcion AS nombre_linea,
 art.art_codigo as codigo,
 SUM(m.mov_cantidad) AS qt_cantidad,
 (ROUND(MAX(pre.pre_precio_act1 / (1 + (util_fn_igv() / 100))), 2) * ROUND(SUM(m.mov_cantidad), 2)) AS ss_tickets_sigv_total,
 (ROUND(
  (CASE
  WHEN sal.stk_costo$BeginMonth = 0.0000 THEN
   COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '$BeginYear-$BeginMonth-01 00:00:00' AND art_codigo = art.art_codigo GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
  ELSE
   sal.stk_costo$BeginMonth
  END)
 , 2) * ROUND(SUM(m.mov_cantidad), 2)) AS ss_kardex_promedio_total
FROM
 inv_movialma AS m
JOIN int_articulos AS art ON (art.art_codigo = m.art_codigo)
LEFT JOIN int_tabla_general AS linea ON(linea.tab_tabla = '20' AND art.art_linea = linea.tab_elemento AND linea.tab_elemento != '000000')--LINEA
LEFT JOIN fac_lista_precios AS pre ON (pre.art_codigo = m.art_codigo)                
LEFT JOIN (SELECT art_codigo, MAX(rec_precio) AS rec_precio FROM com_rec_pre_proveedor GROUP BY art_codigo, rec_fecha_ultima_compra ORDER BY rec_fecha_ultima_compra DESC) AS pro ON (pro.art_codigo = art.art_codigo)
LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = m.mov_almacen AND sal.art_codigo = m.art_codigo AND sal.stk_periodo = '$BeginYear')
WHERE
 m.mov_almacen = '$warehouse_id'
 AND m.mov_fecha::DATE BETWEEN '$BeginDate' AND '$EndDate'
 AND m.tran_codigo = '45'
 AND art.art_plutipo = '1'
 AND art.art_unidad NOT IN('000GLN', '0000GL')
 AND pre.pre_lista_precio = (SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio')
GROUP BY
 linea.tab_elemento,
 linea.tab_descripcion,
 art.art_codigo,
 sal.stk_costo$BeginMonth
) AS A
GROUP BY
 codigo_linea
ORDER BY
 nu_margen DESC;";
 			error_log('Resumen de lineas:');
 			error_log($sql);

			$data = SQLImplodeJSON($sql);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		case 'UTILITY_LINES_DETAIL':
			/**
			 * ventas/market_productos_linea
			 * Busqueda por linea(Detalles)
			 */
			argRangedCheck();
			//pg_escape_string
			$warehouse_id = $_REQUEST['warehouse_id'];
			$line_id = $_REQUEST['line_id'];

			$sql = "SELECT
codigo_linea as co_linea,
nombre_linea as no_linea,
qt_cantidad AS nu_cantidad,
codigo AS co_producto,
articulo AS no_producto,
0.0 AS nu_costo_promedio,
ss_kardex_promedio_total AS nu_costo_total,
ss_tickets_sigv_total AS nu_venta_soles,
ss_tickets_sigv_total - ss_kardex_promedio_total AS nu_margen
FROM
(SELECT
 linea.tab_elemento AS codigo_linea,
 linea.tab_descripcion AS nombre_linea,
 art.art_codigo as codigo,
 art.art_descripcion as articulo,
 SUM(m.mov_cantidad) AS qt_cantidad,
 (ROUND(MAX(pre.pre_precio_act1 / (1 + (util_fn_igv() / 100))), 2) * ROUND(SUM(m.mov_cantidad), 2)) AS ss_tickets_sigv_total,
 (ROUND(
  (CASE
  WHEN sal.stk_costo$BeginMonth = 0.0000 THEN
   COALESCE((SELECT mov_costounitario FROM inv_movialma WHERE mov_fecha < '$BeginYear-$BeginMonth-01 00:00:00' AND art_codigo = art.art_codigo GROUP BY mov_costounitario, mov_fecha ORDER BY mov_fecha DESC LIMIT 1),0)
  ELSE
   sal.stk_costo$BeginMonth
  END)
 , 2) * ROUND(SUM(m.mov_cantidad), 2)) AS ss_kardex_promedio_total
FROM
inv_movialma AS m
JOIN int_articulos AS art ON (art.art_codigo = m.art_codigo)
LEFT JOIN int_tabla_general AS linea ON(linea.tab_tabla = '20' AND art.art_linea = linea.tab_elemento AND linea.tab_elemento != '000000')--LINEA
LEFT JOIN fac_lista_precios AS pre ON (pre.art_codigo = m.art_codigo)                
LEFT JOIN (SELECT art_codigo, MAX(rec_precio) AS rec_precio FROM com_rec_pre_proveedor GROUP BY art_codigo, rec_fecha_ultima_compra ORDER BY rec_fecha_ultima_compra DESC) AS pro ON (pro.art_codigo = art.art_codigo)
LEFT JOIN inv_saldoalma AS sal ON (sal.stk_almacen = m.mov_almacen AND sal.art_codigo = m.art_codigo AND sal.stk_periodo = '$BeginYear')
WHERE
 m.mov_almacen = '$warehouse_id'
 AND m.mov_fecha::DATE BETWEEN '$BeginDate' AND '$EndDate'
 AND m.tran_codigo = '45'
 AND art.art_plutipo = '1'
 AND art.art_unidad NOT IN('000GLN', '0000GL')
 AND linea.tab_elemento = '$line_id'
 AND pre.pre_lista_precio = (SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio')
GROUP BY
 linea.tab_elemento,
 linea.tab_descripcion,
 art.art_codigo,
 sal.stk_costo$BeginMonth
) AS A
ORDER BY
 nu_margen DESC;";
 			//830
 			error_log('Detalle de lineas:');
 			error_log($sql);

			$data = SQLImplodeJSON($sql);
			$comprimido = gzcompress($data);
			echo $comprimido;
		break;

		/**
		 *
		 */

		default:
			die("ERR_INVALID_MOD");
}
?>