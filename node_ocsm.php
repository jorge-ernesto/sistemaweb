<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");
$r = ob_start(true);

$config['method'] = $_SERVER['REQUEST_METHOD'];

function _checkDate($params) {
	$return = array();
	if (strlen($params['date']) != 8 || !is_numeric($params['date'])) {
		errorExec(array(
			'code' => 10,
			'message' => 'Error en el envío de Fecha',
		));
	}

	if ($params['in_format'] == 'dd/mm/yyyy') {
		$arr = explode('/', $params['date']);
		if ($params['out_format'] == 'yyyy-mm-dd') {
			$return['date'] = $arr[2].'-'.$arr[1].'-'.$arr[0];
			$return['year'] = $arr[2];
			$return['month'] = $arr[1];
			$return['day'] = $arr[0];
		}
	} else if ($params['in_format'] == 'yyyymmdd') {
		$return['year'] = substr($params['date'], 0, 4);
		$return['month'] = substr($params['date'], 4, 2);
		$return['day'] = substr($params['date'], 6, 2);
		$return['date'] = $return['year'].'-'.$return['month'].'-'.$return['day'];
	}
	return $return;
}

function checkWarehouse($id) {
	if(strlen($id) > 0) {
		return $id;
	} else {
		errorExec(array(
			'code' => 20,
			'message' => 'Error en el envío de Alamacen',
		));
	}
}

function SQLImplodeSerialize($sql) {
	global $sqlca;
	if ($sqlca->query($sql) < 0) {
		return FALSE;
	}
	$result = array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$result[] = $sqlca->fetchRow();
	}
	echo serialize($result);
}

function errorExec($params) {
	$config['error'] = true;
	$config['code'] = $params['code'];
	$config['message'] = $params['message'];
	echo serialize(array($config));
	renderContent();
	exit;
}

function renderContent() {
	$contenido = ob_get_contents();
	ob_end_clean();
	$comprimido = gzcompress($contenido);
	echo $comprimido;
}

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

if ($config['method'] == 'POST') {
	$config['mode'] = $_POST['mode'];
} else {
	$config['mode'] = $_REQUEST['mode'];
}

switch ($config['mode']) {
	case 'FUEL_SALES':

		$config['date_'] = _checkDate(array(
			'date' => $_POST['start_date'],
			'in_format' => 'yyyymmdd',
		));

		$config['date_1'] = _checkDate(array(
			'date' => $_POST['final_date'],
			'in_format' => 'yyyymmdd',
		));

		$config['warehouse_id'] = checkWarehouse($_POST['warehouse_id']);

		//considerar sacar el producto para almacenar y usar lo mismo cuando se necesite tner el detalle
		$sql = "SELECT
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
	    ) / (1 + (COMB.igv/100)) AS venta_sin_igv, --4 valor venta sin igv
		COMB.dt_fechaparte as fecha
	  FROM
	    (
	      SELECT
	        ch_codigocombustible AS codigo
	      FROM
	        comb_ta_tanques
	      WHERE
	        ch_sucursal = '".pg_escape_string($config['warehouse_id'])."'
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
	      nu_factor_igv AS igv,
	      comb.dt_fechaparte
	    FROM
	      comb_ta_contometros comb
	    LEFT JOIN comb_ta_combustibles cmb ON (
	      comb.ch_codigocombustible = cmb.ch_codigocombustible
	    )
	    WHERE
	      comb.dt_fechaparte BETWEEN '".pg_escape_string($config['date_']['date'])."'
	    AND '".pg_escape_string($config['date_1']['date'])."'
	    AND comb.ch_sucursal = TRIM ('".pg_escape_string($config['warehouse_id'])."')
	    GROUP BY
	      comb.ch_codigocombustible,
	      cmb.ch_nombrecombustible,
	      comb.nu_factor_igv,
	      comb.dt_fechaparte
	  ) COMB ON COMB.codigo = C .codigo
	  LEFT JOIN (
	    SELECT
	      af.codigo AS codigo,
	      SUM (af.importe) AS af_total,
	      ROUND(SUM(af.cantidad), 3) AS af_cantidad
	    FROM
	      pos_ta_afericiones af
	    WHERE
	      af.dia BETWEEN '".pg_escape_string($config['date_']['date'])."'
	    AND '".pg_escape_string($config['date_1']['date'])."'
	    AND af.es = TRIM ('".pg_escape_string($config['warehouse_id'])."')
	    GROUP BY
	      af.codigo
	  ) AFC ON AFC.codigo = C .codigo
	  LEFT JOIN (
	      SELECT stk_costo".pg_escape_string($config['date_']['month'])." as costo_comb, art_codigo from inv_saldoalma where stk_periodo = '".pg_escape_string($config['date_0']['year'])."'
	      AND stk_almacen = TRIM ('".pg_escape_string($config['warehouse_id'])."')
	  ) COST ON (COST.art_codigo =  C.codigo)
	  UNION ALL
	  SELECT
	    'GNV' AS descripcion,
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
	    END / (1 + (util_fn_igv()/100)) AS venta_sin_igv,
	    dt_fecha as fecha
	  FROM comb_liquidaciongnv
	  WHERE ch_almacen = '".pg_escape_string($config['warehouse_id'])."'
	    AND dt_fecha BETWEEN '".pg_escape_string($config['date_']['date'])."' AND '".pg_escape_string($config['date_1']['date'])."'
	    GROUP BY dt_fecha, comb_liquidaciongnv.tot_cantidad, comb_liquidaciongnv.tot_afericion, comb_liquidaciongnv.tot_surtidor_soles, comb_liquidaciongnv.nu_costo_unitario
	    ;";

		SQLImplodeSerialize($sql);
		renderContent();
		break;

	default:
		errorExec(array(
			'code' => 0,
			'message' => 'Error al envíar el modo',
		));
}