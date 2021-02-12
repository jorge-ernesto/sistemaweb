<?php

include_once('/sistemaweb/include/mvc_sistemaweb.php');
include_once('/sistemaweb/include/dbsqlca.php');

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

$Accion 		= trim($_POST['Accion']);
$Nu_Almacen 	= (isset($_POST['Nu_Almacen']) ? trim($_POST['Nu_Almacen']) : '');
$Fe_Emision 	= (isset($_POST['Fe_Emision']) ? trim($_POST['Fe_Emision']) : '');
$Ch_Documento 	= (isset($_POST['Ch_Documento']) ? trim($_POST['Ch_Documento']) : '');

$criterio 		= trim($_POST['criterio']);
$sql = null;

if ($Accion == 'getClientes'){

	$sql = "
	SELECT
		cli_codigo,
		cli_ruc,
		cli_razsocial
	FROM
		int_clientes
	WHERE
		trim(cli_codigo) LIKE '%" . $criterio . "%'
		OR trim(cli_razsocial) LIKE '%" . $criterio . "%'
	ORDER BY
		cli_razsocial
	LIMIT 15;
	";

	if ($sqlca->query($sql) < 0)
		return false;

	$arrClientes = array();
    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $row = $sqlca->fetchRow();
        $arrClientes[$i]["cli_codigo"] = $row["cli_codigo"];
        $arrClientes[$i]["cli_ruc"] = $row["cli_ruc"];
        $arrClientes[$i]["cli_razsocial"] = utf8_encode($row["cli_razsocial"]);
    }

	print_r(json_encode($arrClientes));
}else if ($Accion == 'getProveedores'){
	$sql = "
	SELECT
		PROVEE.pro_codigo,
		PROVEE.pro_ruc,
		PROVEE.pro_razsocial,
		CAST(DV.tab_num_01 AS INT) AS nu_dias_vencimiento,
		RUBRO.ch_codigo_rubro AS nu_codigo_rubro,
		RUBRO.ch_descripcion AS no_descripcion_rubro,
		SUBSTR(MONE.tab_elemento, 5) AS nu_tipo_moneda
	FROM
		int_proveedores AS PROVEE
		LEFT JOIN int_tabla_general AS MONE ON('0'||PROVEE.pro_moneda = substring(TRIM(MONE.tab_elemento) for 2 FROM length(TRIM(MONE.tab_elemento))-1) AND MONE.tab_tabla = '04' AND MONE.tab_elemento <> '000000')
		LEFT JOIN int_tabla_general AS DV ON(PROVEE.pro_forma_pago = substring(TRIM(DV.tab_elemento) for 2 FROM length(TRIM(DV.tab_elemento))-1) AND DV.tab_tabla = '96' AND DV.tab_elemento <> '000000')
		LEFT JOIN cpag_ta_rubros AS RUBRO ON (RUBRO.ch_codigo_rubro = PROVEE.pro_grupo)
	WHERE
		trim(PROVEE.pro_codigo) LIKE '%" . $criterio . "%'
		OR trim(PROVEE.pro_razsocial) LIKE '%" . $criterio . "%'
	ORDER BY
		PROVEE.pro_razsocial
	LIMIT 15;
	";

	if ($sqlca->query($sql) < 0)
		return false;

   $data = array();
   for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$row = $sqlca->fetchRow();
		$data[$i]["pro_codigo"] = $row["pro_codigo"];
		$data[$i]["pro_ruc"] = $row["pro_ruc"];
		$data[$i]["pro_razsocial"] = utf8_encode($row["pro_razsocial"]);
		$data[$i]["nu_codigo_rubro"] = $row["nu_codigo_rubro"];
		$data[$i]["no_descripcion_rubro"] = $row["no_descripcion_rubro"];
		$data[$i]["nu_tipo_moneda"] = $row["nu_tipo_moneda"];
		$data[$i]["nu_dias_vencimiento"] = $row["nu_dias_vencimiento"];
    }
    print_r(json_encode($data));
}else if ($Accion == 'getTarjetasYPlacasXCliente'){

	$Nu_Documento_Identidad 	= trim($_POST['Nu_Documento_Identidad']);

	$sql = "
	SELECT
		*
	FROM
		pos_fptshe1
	WHERE
		codcli = '" . $Nu_Documento_Identidad . "'
		AND estblo = 'N'
		AND (
			numpla LIKE '%" . $criterio . "%'
			OR numtar LIKE '%" . $criterio . "%'
		)
	ORDER BY
		numpla
	LIMIT 15;
	";

	if ($sqlca->query($sql) < 0)
		return false;

	print_r(json_encode($sqlca->fetchAll()));

}else if ($Accion == 'getProductos'){
	$Nu_Tipo_Movimiento_Inventario 	= trim(strip_tags($_POST['Nu_Tipo_Movimiento_Inventario']));
	$Nu_Almacen_Interno 			= trim(strip_tags($_POST['Nu_Almacen_Interno']));
	$mes 							= date("m");
	$ano 							= date("Y");

	$cond_Productos = '';

	/*
	if($Nu_Tipo_Movimiento_Inventario == '21' || $Nu_Tipo_Movimiento_Inventario == '27' || $Nu_Tipo_Movimiento_Inventario == '28')
		$cond_Productos = "AND PRO.art_linea IN('0000CO', '0000GL')";
		*/
	//if ($Nu_Tipo_Movimiento_Inventario == '01' || $Nu_Tipo_Movimiento_Inventario == '07' || $Nu_Tipo_Movimiento_Inventario == '08')
	//if ($Nu_Tipo_Movimiento_Inventario == '01' || $Nu_Tipo_Movimiento_Inventario == '08')
	if ($Nu_Tipo_Movimiento_Inventario == '01')
		$cond_Productos = "AND PRO.art_linea NOT IN('0000CO', '0000GL')";

	$sql = "
	SELECT
		PRO.art_codigo,
		PRO.art_descripcion,
		PRE.pre_precio_act1 AS nu_precio_venta,
		SALDOALMA.stk_stock" . $mes . " AS nu_cantidad_actual,
		(SELECT rec_precio FROM com_rec_pre_proveedor WHERE art_codigo = PRO.art_codigo ORDER BY rec_fecha_ultima_compra DESC LIMIT 1) AS nu_costo_unitario
	FROM
		int_articulos AS PRO
		LEFT JOIN fac_lista_precios AS PRE ON(PRE.art_codigo = PRO.art_codigo AND pre_lista_precio = (SELECT par_valor FROM int_parametros WHERE par_nombre='lista precio'))
		LEFT JOIN inv_saldoalma SALDOALMA ON (PRO.art_codigo=SALDOALMA.art_codigo AND SALDOALMA.stk_periodo = '" . $ano . "' AND SALDOALMA.stk_almacen = '" . $Nu_Almacen_Interno . "')
	WHERE
		PRO.art_plutipo='1'
		AND (PRO.art_codigo LIKE '%" . $criterio . "%' OR PRO.art_descripcion LIKE '%" . $criterio . "%')
		" . $cond_Productos . "
	ORDER BY
		PRO.art_descripcion
	LIMIT 15;
	";

	if ($sqlca->query($sql) < 0)
		return false;

	//print_r(json_encode($sqlca->fetchAll()));
    $data = array();
    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $row = $sqlca->fetchRow();
        $data[$i]["art_codigo"] = $row["art_codigo"];
        $data[$i]["art_descripcion"] = utf8_encode($row["art_descripcion"]);
        $data[$i]["nu_precio_venta"] = $row["nu_precio_venta"];
        $data[$i]["nu_cantidad_actual"] = $row["nu_cantidad_actual"];
        $data[$i]["nu_costo_unitario"] = $row["nu_costo_unitario"];
    }
    print_r(json_encode($data));
} else if ($Accion == 'obtenerProductos2') {
	//No se considera art_plutipo

	$Nu_Tipo_Movimiento_Inventario = trim(strip_tags($_POST['Nu_Tipo_Movimiento_Inventario']));
	$Nu_Almacen_Interno = trim(strip_tags($_POST['Nu_Almacen_Interno']));
	$mes = date("m");
	$ano = date("Y");

	$cond_Productos = '';

	if ($Nu_Tipo_Movimiento_Inventario == '01' || $Nu_Tipo_Movimiento_Inventario == '07' || $Nu_Tipo_Movimiento_Inventario == '08') {
		$cond_Productos = "AND PRO.art_linea NOT IN('0000CO', '0000GL')";
	}

	$sql = "SELECT
 PRO.art_codigo,
 PRO.art_descripcion,
 PRE.pre_precio_act1 AS nu_precio_venta,
 SALDOALMA.stk_stock" . $mes . " AS nu_cantidad_actual,
 (SELECT rec_precio FROM com_rec_pre_proveedor WHERE art_codigo = PRO.art_codigo ORDER BY rec_fecha_ultima_compra LIMIT 1) AS nu_costo_unitario
FROM
 int_articulos AS PRO
 LEFT JOIN fac_lista_precios AS PRE ON(PRE.art_codigo = PRO.art_codigo AND pre_lista_precio = (SELECT par_valor FROM int_parametros WHERE par_nombre='lista precio'))
 LEFT JOIN inv_saldoalma SALDOALMA ON (PRO.art_codigo=SALDOALMA.art_codigo AND SALDOALMA.stk_periodo = '" . $ano . "' AND SALDOALMA.stk_almacen = '" . $Nu_Almacen_Interno . "')
WHERE
 (PRO.art_codigo LIKE '" . $criterio . "%' OR PRO.art_descripcion LIKE '" . $criterio . "%')
 " . $cond_Productos . "
ORDER BY
 PRO.art_descripcion
LIMIT 15;";

	$data = array();
	if ($sqlca->query($sql) < 0) {
		print_r(json_encode($data));
	}

    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $row = $sqlca->fetchRow();
        $data[$i]["art_codigo"] = $row["art_codigo"];
        $data[$i]["art_descripcion"] = utf8_encode($row["art_descripcion"]);
        $data[$i]["nu_precio_venta"] = $row["nu_precio_venta"];
        $data[$i]["nu_cantidad_actual"] = $row["nu_cantidad_actual"];
        $data[$i]["nu_costo_unitario"] = $row["nu_costo_unitario"];
    }
    print_r(json_encode($data));

} else if ($Accion == 'validarClienteAnticipo') {
	$bpartner = $_POST['bpartner'];
	$sql = "SELECT count(*) AS valid FROM
int_clientes
WHERE
cli_codigo = '".pg_escape_string($bpartner)."'
AND cli_anticipo = 'S'
AND cli_ndespacho_efectivo = '0';";

	$data = array();
	if ($sqlca->query($sql) < 0) {
		echo json_encode(array('error' => true));
		exit;
	}

	$row = $sqlca->fetchRow();
	$esAnticipo = $row['valid'] > 0 ? true : false;
	echo json_encode(array(
		'error' => false,
		'esAnticipo' => $esAnticipo,
 	));
	exit;
}
