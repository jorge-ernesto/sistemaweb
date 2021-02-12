<?php

//include("/sistemaweb/valida_sess.php");
//include("/sistemaweb/functions.php");

include_once('/sistemaweb/include/mvc_sistemaweb.php');
include_once('/sistemaweb/include/dbsqlca.php');

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

$accion 	= null;
$nu_almacen = null;
$Fe_Emision = null;
$postrans 	= null;

$accion = trim($_POST['accion']);

if(isset($_POST['nu_almacen']))
	$nu_almacen 	= trim($_POST['nu_almacen']);
if(isset($_POST['Fe_Emision'])){
	$Fe_Emision 	= trim($_POST['Fe_Emision']);
	$Fe_Emision 	= explode("/", $Fe_Emision);

	$postrans  		= "pos_trans".$Fe_Emision[2].$Fe_Emision[1];
	$Fe_Emision 	= $Fe_Emision[2] . "-" . $Fe_Emision[1] . "-" . $Fe_Emision[0];
}

if($accion == 'getAlmacenes'){
	$arrAlmacenes 	= array(); //M = Market o C = Combustible
	$status 		= 'success';

	$sql = "SELECT ch_almacen, ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_clase_almacen = '1' ORDER BY 1;";

	if ($sqlca->query($sql) <= 0)
		$status = 'error';

	$arrAlmacenes = $sqlca->fetchAll();

	echo json_encode(array(
		'status' => $status,
		'arrAlmacenes' => $arrAlmacenes,
	));

} else if($accion == 'verifyExistDocument'){
	
	$Ch_Documento 	= trim($_POST['Ch_Documento']);

	$sql = "
	SELECT
		COUNT(*) AS nu_existe_documento
	FROM
		val_ta_cabecera
	WHERE
		ch_sucursal 			= '" . $nu_almacen . "'
		AND dt_fecha 			= '" . $Fe_Emision . "'
		AND trim(ch_documento) 	= '" . $Ch_Documento . "';
	";

	if ($sqlca->query($sql) < 0)
		return false;

	print_r(json_encode($sqlca->fetchRow()));

} else if($accion == 'getTipoVenta'){
	$arrTipoVenta 	= array(); //M = Market o C = Combustible
	$status 		= 'success';

	// Get tipos de ventas de market o combustible, dependiendo del almacen y fecha
	//$sql = "SELECT DISTINCT tipo AS nu_tipo_venta, (CASE WHEN tipo = 'C' THEN 'Combustible' ELSE 'Market' END) AS tipo FROM " . $postrans . " WHERE es = '" . $nu_almacen . "' AND dia = '" . $Fe_Emision . "' ORDER BY 1;";
	
	$sql = "SELECT CASE WHEN s_postype_id = 2 THEN 'M' ELSE 'C' END AS nu_tipo_venta, CASE WHEN s_postype_id = 2 THEN 'Market' ELSE 'Combustible' END AS tipo FROM s_pos WHERE warehouse = '" . $nu_almacen . "' GROUP BY s_postype_id;";

	if ($sqlca->query($sql) <= 0) {
		//$status = 'error';
		$arrTipoVenta = array(
	    	0 => array(
	            "nu_tipo_venta" => "C",
	            "tipo" => "Combustible"
	        ),
		    1 => array(
	            "nu_tipo_venta" => "M",
	            "tipo" => "Market"
	        )
		);
	} else {
		$arrTipoVenta = $sqlca->fetchAll();
	}

	echo json_encode(array(
		'status' => $status,
		'arrTipoVenta' => $arrTipoVenta,
	));
} else if($accion == 'getValuesFechaTCL'){//Array TCL = Get Values: Turno, Caja, Lado

	$nu_tipo_venta 	= trim($_POST['nu_tipo_venta']);

	$cerrado 		= NULL;
	$arrTurnos 		= array();
	$arrCajas 		= array();
	$arrLados 		= array();

	$sql = "SELECT ch_poscd FROM pos_aprosys WHERE da_fecha='" . $Fe_Emision . "';";
	if ($sqlca->query($sql) < 0)
	    return false;

	$a = $sqlca->fetchRow();

	if ($a['ch_poscd'] == 'S')
		$cerrado = '- 1';

	$sql = "SELECT ch_posturno::INTEGER " . $cerrado . " AS turno FROM pos_aprosys WHERE da_fecha = '" . $Fe_Emision . "';";

	if ($sqlca->query($sql) <= 0) {
		$arrTurnos = array(
	    	0 => array(
	            "turno" => "1",
	        ),
		    1 => array(
	            "turno" => "2",
	        ),
		    2 => array(
	            "turno" => "3",
	        ),
		    3 => array(
	            "turno" => "4",
	        ),
		    3 => array(
	            "turno" => "5",
	        ),
		    3 => array(
	            "turno" => "6",
	        )
		);
	} else {
		$arrTurnos = $sqlca->fetchAll();
	}

	$sql = "SELECT s_pos_id AS caja FROM s_pos WHERE warehouse = '" . $nu_almacen . "' ORDER BY 1;";
	if ($sqlca->query($sql) <= 0) {
		$arrCajas = array(
	    	0 => array(
	            "caja" => "1",
	        ),
		    1 => array(
	            "caja" => "2",
	        ),
		    2 => array(
	            "caja" => "3",
	        ),
		    3 => array(
	            "caja" => "4",
	        )
		);
	} else {
		$arrCajas = $sqlca->fetchAll();
	}

	//Lados
	if($nu_tipo_venta != 'M'){
		$sql = "SELECT f_pump.name AS pump FROM f_pump_pos JOIN f_pump ON f_pump_pos.f_pump_id = f_pump.f_pump_id JOIN s_pos ON f_pump_pos.s_pos_id = s_pos.s_pos_id WHERE  s_pos.warehouse = '" . $nu_almacen . "' ORDER BY f_pump.f_pump_id;";
		if ($sqlca->query($sql) <= 0) {
			$sql2 = "SELECT lado AS pump FROM pos_cmblados ORDER BY 1;";
			$sqlca->query($sql2);
			$arrLados = $sqlca->fetchAll();	
		} else {
			$arrLados = $sqlca->fetchAll();
		}
	}

	//Array TCL = Get Values: Turno, Caja, Lado
	echo json_encode(array(
		'arrTurnos' => $arrTurnos,
		'arrCajas' => $arrCajas,
		'arrLados' => $arrLados,
	));
	exit();
} else if($accion == 'verifyValidationDayAndTurnoConsolidado'){

	$Nu_Turno 	= trim($_POST['Nu_Turno']);

	$sql = "SELECT validar_consolidacion('" . $Fe_Emision . "', '" . $Nu_Turno . "', '" . $nu_almacen . "');";

	if ($sqlca->query($sql) < 0){
		//return false;
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'data' => null,
		)));
		die();
	}

	$response = array(
		'status' => 'success',
		'data' => $sqlca->fetchRow(),
	);

	print_r(json_encode($response));

} else if($accion == 'verifyValidationDayConsolidacion'){

	$Nu_Almacen_Interno = trim(strip_tags($_POST['Nu_Almacen_Interno']));
	$Fe_Emision_Compra 	= trim(strip_tags($_POST['Fe_Emision_Compra']));

	$sql = "SELECT validar_consolidacion('" . $Fe_Emision_Compra . "', 0, '" . $Nu_Almacen_Interno . "');";

	if ($sqlca->query($sql) < 0){
		//return false;
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'data' => null,
		)));
		die();
	}

	$response = array(
		'status' => 'success',
		'data' => $sqlca->fetchRow(),
	);

	print_r(json_encode($response));

} else if($accion == 'getTipoCambio') {

	$Fe_Emision_Compra 	= trim(strip_tags($_POST['Fe_Emision_Compra']));

	$sql = "SELECT * FROM int_tipo_cambio WHERE tca_fecha = '" . $Fe_Emision_Compra . "' AND tca_moneda = '02'";

	$iStatus = $sqlca->query($sql);

	if ($iStatus < 0){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'fTipoCambio' => null,
		)));
	} else if ( $iStatus == 0 ) {//No hay registros
		$response = array(
			'status' => 'success',
			'fTipoCambio' => 0,
		);
		print_r(json_encode($response));
	} else {
		$row = $sqlca->fetchRow();
		$fTipoCambio = (float)$row['tca_compra_oficial'];

		$response = array(
			'status' => 'success',
			'fTipoCambio' => $fTipoCambio,
		);
		print_r(json_encode($response));
	}
} else if($accion == 'getCorrelativoRC') {

	//$sql = "SELECT LPAD(CAST((MAX(pro_cab_numreg) + 1) AS bpchar), 10, '0') AS nu_registro_compra FROM cpag_ta_cabecera;";
	$sql = "SELECT LPAD(CAST((CASE WHEN max(pro_cab_numreg) IS NOT NULL THEN max(pro_cab_numreg)+1 ELSE 1 END) AS bpchar), 10, '0') AS nu_registro_compra FROM cpag_ta_cabecera;";
	
	if ($sqlca->query($sql) < 0){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'data' => null,
		)));
		die();
	}

	$response = array(
		'status' => 'success',
		'data' => $sqlca->fetchRow(),
	);

	print_r(json_encode($response));
} else if($accion == 'getRubros') {

	$sql = "SELECT * FROM cpag_ta_rubros WHERE ch_tipo_item != '' ORDER BY 1;";

	if ($sqlca->query($sql) < 0){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'data' => null,
		)));
		die();
	}

	$response = array(
		'status' => 'success',
		'arrRubros' => $sqlca->fetchAll(),
	);

	print_r(json_encode($response));

} else if($accion == 'getMonedas') {

	$sql="
	SELECT
		substr(tab_elemento,5) currency,
		tab_descripcion || ' ' || tab_desc_breve mone
	FROM
		int_tabla_general
	WHERE
		tab_tabla = '04'
		AND tab_elemento != '000000'
	ORDER BY
		1;
	";

	if ($sqlca->query($sql) < 0){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'data' => null,
		)));
		die();
	}

	$response = array(
		'status' => 'success',
		'arrMonedas' => $sqlca->fetchAll(),
	);

	print_r(json_encode($response));
} else if($accion == 'getCintillo') {
	$arrData = $_POST["arrData"];

	$Nu_Tipo_Movimiento_Inventario = trim($arrData['Nu_Tipo_Movimiento_Inventario']);
	$Nu_Tipo_Movimiento_Inventario = strip_tags($Nu_Tipo_Movimiento_Inventario);

	$id_proveedor = '';
	if ($Nu_Tipo_Movimiento_Inventario != '18' && $Nu_Tipo_Movimiento_Inventario != '27' && $Nu_Tipo_Movimiento_Inventario != '28' && $Nu_Tipo_Movimiento_Inventario != '07' && $Nu_Tipo_Movimiento_Inventario != '08' && $Nu_Tipo_Movimiento_Inventario != '16') {
		$id_proveedor = trim($arrData['id_proveedor']);
		$id_proveedor = strip_tags($id_proveedor);
		$id_proveedor = "AND PROVEE.pro_codigo='" . $id_proveedor . "'";
	}

	$Nu_Formulario = trim($arrData['Nu_Formulario']);
	$Nu_Formulario = strip_tags($Nu_Formulario);

	$Fe_Emision = trim($arrData['Fe_Emision']);
	$Fe_Emision = strip_tags($Fe_Emision);
	$Fe_Emision = explode("/", $Fe_Emision);
	$Fe_Emision = $Fe_Emision[2] . "-" . $Fe_Emision[1] . "-" . $Fe_Emision[0];

	$sql_campo_precio_venta = "ROUND(pre_precio_act1, 3)";
	$sql_tabla_comb_ta_combustibles = "";
	if($Nu_Tipo_Movimiento_Inventario == '21'){
		$sql_campo_precio_venta = "ROUND(nu_preciocombustible, 3)";
		$sql_tabla_comb_ta_combustibles = "LEFT JOIN comb_ta_combustibles AS COMB ON(COMB.ch_codigocombustible = MOVI.art_codigo)";
	}

	// New params POS
	$sIdTipoDocumento = trim($arrData['sIdTipoDocumento']);
	$sIdTipoDocumento = strip_tags($sIdTipoDocumento);

	$sSerieDocumento = trim($arrData['sSerieDocumento']);
	$sSerieDocumento = strip_tags($sSerieDocumento);

	$sNumeroDocumento = trim($arrData['sNumeroDocumento']);
	$sNumeroDocumento = strip_tags($sNumeroDocumento);

	$sql="
SELECT
 INVTMI.tran_descripcion AS no_tipo_operacion_inventario,
 MOVI.mov_numero AS num_mov,
 TO_CHAR(MOVI.mov_fecha, 'dd/mm/yyyy hh24:mi:ss') AS fe_emision,
 MOVI.com_num_compra AS num_compra,
 MOVI.mov_entidad AS entidad,
 PROVEE.pro_ruc || ' - ' || PROVEE.pro_razsocial AS no_razon_social,
 TD.tab_desc_breve AS no_tipo_documento,
 SUBSTR(MOVI.mov_docurefe, 1, 4) AS nu_serie_documento,
 SUBSTR(MOVI.mov_docurefe, 5, 8) AS nu_numero_documento,
 MOVI.art_codigo|| ' - ' || trim(a.art_descripcion) AS no_producto,
 ROUND(MOVI.mov_cantidad, 4) AS nu_cantidad,
 ROUND(MOVI.mov_costounitario, 6) AS nu_costo_unitario,
 ROUND(MOVI.mov_costounitario * MOVI.mov_cantidad, 4) AS nu_total_sigv,
 MOVI.mov_almaorigen || '-' || ALMAORIGEN.ch_nombre_almacen AS alma_ori,
 MOVI.mov_almadestino || '-' || ALMADESTINO.ch_nombre_almacen AS alma_des,
 MOVI.mov_tipdocuref AS tip_docref, 
 MOVI.mov_docurefe AS docref, 
 ".$sql_campo_precio_venta." AS precio_compra,
 ROUND(g.tab_num_01, 0) AS margen,
 (CASE WHEN MOVI.mov_costounitario > 0 THEN
  (CASE WHEN MOVI.mov_costo_participacion IS NULL OR MOVI.mov_costo_participacion = 0.0000 THEN
   ROUND(100 * ((".$sql_campo_precio_venta." / (1 + util_fn_igv() / 100) / MOVI.mov_costounitario) - 1),0)
  ELSE
   mov_costo_participacion
  END)
 ELSE
  0
 END) AS margen_real,
 (CASE WHEN g.tab_num_01 > (CASE WHEN MOVI.mov_costounitario > 0 THEN
   ROUND(100 * ((".$sql_campo_precio_venta." / (1 + util_fn_igv() / 100) / MOVI.mov_costounitario) - 1),0)
  ELSE
   '0'
  END) THEN
  ' (*) '
 ELSE
  '&nbsp;'
 END) AS mayor,
 (util_fn_igv()/100) AS Ss_impuesto
FROM
 inv_movialma AS MOVI
 LEFT JOIN int_proveedores AS PROVEE ON(MOVI.mov_entidad = PROVEE.pro_codigo)
 JOIN int_articulos AS a ON (a.art_codigo = MOVI.art_codigo)
 LEFT JOIN fac_lista_precios AS p ON (p.art_codigo = a.art_codigo AND p.pre_lista_precio = util_fn_cd_precio())
 LEFT JOIN int_tabla_general AS g ON (g.tab_tabla = '20' and g.tab_elemento = a.art_linea)
 LEFT JOIN int_tabla_general AS TD ON(MOVI.mov_tipdocuref = substring(TRIM(TD.tab_elemento) for 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla = '08' AND TD.tab_elemento <> '000000')
 LEFT JOIN inv_ta_almacenes AS ALMAORIGEN ON (MOVI.mov_almaorigen = ALMAORIGEN.ch_almacen)
 LEFT JOIN inv_ta_almacenes AS ALMADESTINO ON (MOVI.mov_almadestino = ALMADESTINO.ch_almacen)
 LEFT JOIN inv_tipotransa AS INVTMI USING (tran_codigo)
 ".$sql_tabla_comb_ta_combustibles."
WHERE
 MOVI.tran_codigo='".$Nu_Tipo_Movimiento_Inventario."'
 AND MOVI.mov_fecha::DATE='".$Fe_Emision."'
 AND MOVI.mov_tipdocuref='".$sIdTipoDocumento."'
 AND MOVI.mov_docurefe='".$sSerieDocumento.$sNumeroDocumento."'
 ".$id_proveedor."
ORDER BY
 2 DESC;
	";

	error_log($sql);

	if ($sqlca->query($sql) <= 0){
		echo json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'data' => null,
			'sql' => $sql,
		));
		exit();
	}

    $Ss_base_imponible = 0.00;
    $Ss_igv = 0.00;
    $Ss_total = 0.00;
	$data = array();

    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $row = $sqlca->fetchRow();
        $data[$i]["no_tipo_operacion_inventario"] = $row["no_tipo_operacion_inventario"];
        $data[$i]["num_mov"] = $row["num_mov"];
        $data[$i]["fe_emision"] = $row["fe_emision"];
        $data[$i]["num_compra"] = $row["num_compra"];
        $data[$i]["entidad"] = $row["entidad"];
        $data[$i]["no_razon_social"] = $row["no_razon_social"];
        $data[$i]["no_tipo_documento"] = $row["no_tipo_documento"];
        $data[$i]["nu_serie_documento"] = $row["nu_serie_documento"];
        $data[$i]["nu_numero_documento"] = $row["nu_numero_documento"];
        $data[$i]["no_producto"] = utf8_encode($row["no_producto"]);
        $data[$i]["nu_cantidad"] = $row["nu_cantidad"];
        $data[$i]["nu_costo_unitario"] = $row["nu_costo_unitario"];
        $data[$i]["nu_total_sigv"] = $row["nu_total_sigv"];
        $data[$i]["nu_total_cigv"] = round(($row["nu_total_sigv"] + ($row["nu_total_sigv"] * $row["ss_impuesto"])), 3);
        $data[$i]["alma_ori"] = $row["alma_ori"];
        $data[$i]["alma_des"] = $row["alma_des"];
        $data[$i]["tip_docref"] = $row["tip_docref"];
        $data[$i]["docref"] = $row["docref"];
        $data[$i]["precio_compra"] = $row["precio_compra"];
        $data[$i]["margen"] = $row["margen"];
        $data[$i]["margen_real"] = $row["margen_real"];
        $data[$i]["mayor"] = $row["mayor"];

        $Ss_base_imponible = $Ss_base_imponible + $row["nu_total_sigv"];
        $Ss_igv = $Ss_igv + ($row["nu_total_sigv"] * $row["ss_impuesto"]);
        $Ss_total = $Ss_total + ($row["nu_total_sigv"] + ($row["nu_total_sigv"] * $row["ss_impuesto"]));
    }

	$response = array(
		'status' => 'success',
		'message' => 'Cintillo encontrado',
		'arrCintillo' => $data,
		'Ss_base_imponible' => $Ss_base_imponible,
		'Ss_igv' => $Ss_igv,
		'Ss_total' => $Ss_total,
	);

	echo json_encode($response);
} else if ($accion == 'getClientesDiasCredito'){

	$Nu_Codigo_Cliente = trim(strip_tags($_POST['Nu_Codigo_Cliente']));

	$sql = "
	SELECT
		CLI.cli_fpago_credito AS nu_codigo_dias_vencimiento,
		tab_descripcion,
		CAST(tab_num_01 as INT) AS nu_dias_vencimiento
	FROM
		int_clientes AS CLI
		LEFT JOIN int_tabla_general AS DIACRED ON (tab_tabla = '96' AND tab_elemento <> '000000' AND TRIM(CLI.cli_fpago_credito) = substring(tab_elemento for 2 from length(tab_elemento)-1))
	WHERE
		trim(CLI.cli_codigo) = '" . $Nu_Codigo_Cliente . "'
	";

	if ($sqlca->query($sql) < 0){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'data' => null,
		)));
		die();
	}

	$response = array(
		'status' => 'success',
		'data' => $sqlca->fetchRow(),
	);

	print_r(json_encode($response));
} else if($accion == 'getOrdenesCompra') {
	$arrData = $_POST["arrData"];
	$nu_orden_compra = trim(strip_tags($arrData['nu_orden_compra']));
	$pro_codigo = trim(strip_tags($arrData['pro_codigo']));

	$cond_nu_orden_compra = '';
	if ( strlen($nu_orden_compra) > 0 )
		$cond_nu_orden_compra = "AND TRIM(OC.com_cab_numorden) LIKE '%" . $nu_orden_compra . "%'";

	$sql = "
	SELECT
		TRIM(OC.com_cab_numorden) AS nu_orden_compra,
		TRIM(PRO.art_codigo) AS nu_codigo_producto,
		TRIM(PRO.art_descripcion) AS no_producto
	FROM
		com_cabecera AS OC
		JOIN com_detalle AS OD USING (pro_codigo, num_tipdocumento, num_seriedocumento, com_cab_numorden)
		JOIN int_articulos AS PRO USING (art_codigo)
	WHERE
		OC.pro_codigo = '" . $pro_codigo . "'
		AND OC.com_cab_estado = '1'
		" . $cond_nu_orden_compra . ";
	";

	//error_log($sql);
	if ($sqlca->query($sql) <= 0){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'arrOrdenesCompra' => null,
		)));
	} else {
		$response = print_r(json_encode(array(
			'status' => 'success',
			'arrOrdenesCompra' => $sqlca->fetchAll(),
		)));
	}
} else if($accion == 'getOrdenCompraDetalle') {
	$Nu_Almacen_Interno 				= trim(strip_tags($_POST['Nu_Almacen_Interno']));
	$nu_orden_compra_codigo_producto 	= trim(strip_tags($_POST['nu_orden_compra_codigo_producto'][0]));
	$mes 								= date("m");
	$ano 								= date("Y");
	$producto 							= substr($nu_orden_compra_codigo_producto, 8);

	//el calculo de margen se activa con el parametro margincheck y tiene que estar descativado actualiza_precio
	$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='actualiza_precio' LIMIT 1;";
	$valor = $sqlca->query($sql);
	$par = $sqlca->fetchRow();

	$sql2 = "SELECT par_valor FROM int_parametros WHERE par_nombre='margincheck' LIMIT 1;";
	$valor2 = $sqlca->query($sql2);
	$par2 = $sqlca->fetchRow();

	if (($par['par_valor'] == '0' || $par['par_valor'] == NULL) && $par2['par_valor'] == '1'){

	$sql3 = "
	SELECT
	a.art_descripcion as descripcion,
	tg.tab_descripcion as linea,
	util_fn_precio_articulo('" . $producto . "') as precio,
	tg.tab_num_01 as margen
	FROM
	int_articulos a
	LEFT JOIN int_tabla_general tg on tg.tab_tabla='20' and tg.tab_elemento = a.art_linea
	WHERE 
	a.art_codigo = '" . $producto . "';
	";

	$valor3 = $sqlca->query($sql3);
	$par3 	= $sqlca->fetchRow();

		if ($valor3 < 0){
			$statusmargin 	= 'error';
			$messagemargin 	= 'Producto no encontrado';
		}else{	
			$statusmargin 	= 'success';
		}
	
	}else{
		$statusmargin 	= 'notvalid';
		$messagemargin 	= 'No se cumplen requisitos';
	}

	$sql4 = "
	SELECT
		TRIM(OC.num_seriedocumento) AS nu_serie_orden_compra,
		TRIM(OC.com_cab_numorden) AS nu_orden_compra,
		TRIM(PRO.art_codigo) AS nu_codigo_producto,
		TRIM(PRO.art_descripcion) AS no_producto,
		OD.com_det_cantidadpedida AS qt_cantidad_pedida,
		OD.com_det_cantidadatendida AS qt_cantidad_atendida,
		ROUND((OD.com_det_imparticulo - OD.com_det_impuesto1) / OD.com_det_cantidadpedida, 4) AS ss_costo_unitario,
		ROUND((OD.com_det_cantidadpedida * ROUND((OD.com_det_imparticulo - OD.com_det_impuesto1) / OD.com_det_cantidadpedida, 4)), 4) AS ss_total_sigv,
		SALDOALMA.stk_stock" . $mes . " AS qt_stock_actual
	FROM
		com_cabecera AS OC
		JOIN com_detalle AS OD USING (pro_codigo, num_tipdocumento, num_seriedocumento, com_cab_numorden)
		JOIN int_articulos AS PRO USING (art_codigo)
		LEFT JOIN inv_saldoalma SALDOALMA ON (PRO.art_codigo=SALDOALMA.art_codigo AND SALDOALMA.stk_periodo = '" . $ano . "' AND SALDOALMA.stk_almacen = '" . $Nu_Almacen_Interno . "')
	WHERE
		TRIM(OC.com_cab_numorden)||TRIM(OD.art_codigo) = '" . $nu_orden_compra_codigo_producto . "'
		AND OC.com_cab_estado = '1';
	";

	if ($sqlca->query($sql4) <= 0 || $valor3 < 0){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'status2' => $statusmargin,
			'message2' => $messagemargin,
			'arrOrdenesCompraDetalle' => null,
		)));
	} else {
		$response = print_r(json_encode(array(
			'status' => 'success',
			'arrOrdenesCompraDetalle' => $sqlca->fetchAll(),
			'status2' => $statusmargin,
			'arrMargen' => $par3,
		)));
	}

} else if($accion == 'getNewPrice'){

	$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='actualiza_precio' LIMIT 1;";
	$valor = $sqlca->query($sql);
	$par = $sqlca->fetchRow();

	if ($par['par_valor'] == '0' || $par['par_valor'] == NULL ){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'No hay datos',
			'reponseData' => ''
		)));
	} else {
		$id_producto = $_POST['id_producto'];
		$id_proveedor = $_POST['id_proveedor'];
		$qt_cantidad = $_POST['qt_cantidad'];
		$ss_costo_sigv = $_POST['ss_costo_sigv'];
        settype($qt_cantidad, "double");
        settype($ss_costo_sigv, "double");

		$sql = "SELECT par_valor AS nu_tipo_lista_precio FROM int_parametros WHERE par_nombre = 'lista precio' LIMIT 1;";
		$status = $sqlca->query($sql);
		if ($status <= 0)
        	$sTipoListaPrecio = '01';
        else {
	        $row = $sqlca->fetchRow();
	        if($row['nu_tipo_lista_precio'] != '')
	        	$sTipoListaPrecio = $row['nu_tipo_lista_precio'];
	    }

		$sql = "
		SELECT
			TRIM(PRO.art_codigo) AS art_codigo,
			PRO.art_descripcion,
			LINEA.tab_descripcion AS no_linea,
			ROUND((" . $ss_costo_sigv . " * ROUND(1 + (util_fn_igv() / 100), 2)), 2) AS ss_costo_cigv,
			" . $ss_costo_sigv . " AS ss_costo_sigv,
			" . $qt_cantidad . " AS qt_cantidad,
			ROUND(((" . $ss_costo_sigv . " * ROUND(1 + (util_fn_igv() / 100), 2)) * " . $qt_cantidad . "), 2) AS ss_subtotal,
			FLP.pre_precio_act1 AS ss_precio_venta,
			LINEA.tab_num_01 AS ss_porcentaje_margen,
			'" . $sTipoListaPrecio . "' AS nu_tipo_lista_precio
		FROM
			int_articulos AS PRO
			LEFT JOIN int_tabla_general AS LINEA ON(PRO.art_linea = LINEA.tab_elemento AND LINEA.tab_tabla = '20' AND tab_elemento != '000000')
			LEFT JOIN fac_lista_precios AS FLP ON(FLP.art_codigo = PRO.art_codigo)
		WHERE
			PRO.art_codigo = '" . $id_producto . "'
			AND FLP.pre_lista_precio = '" . $sTipoListaPrecio . "';
		";
		$status = $sqlca->query($sql);
		if ($status <= 0){
			$status = 'error';
			$message = 'No hay datos';
			$row = '';
		} else {
			$status = 'success';
			$message = 'Datos encontrado';
			$row = $sqlca->fetchRow();	
		}

		$response = print_r(json_encode(array(
			'status' => $status,
			'message' => $message,
			'reponseData' => $row,
		)));
	}
} else if($accion == 'updNewPrice') {
	$nu_tipo_lista_precio = $_POST["nu_tipo_lista_precio"];
	$id_producto = $_POST["id_producto_nuevo"];
	$ss_precio_venta_sugerido = $_POST["ss_precio_venta_sugerido"];

	$sql = "
	UPDATE
		fac_lista_precios
	SET
		pre_precio_act1 = " . round($ss_precio_venta_sugerido, 2) . "
	WHERE
		art_codigo = '" . $id_producto . "'
		AND pre_lista_precio = '" . $nu_tipo_lista_precio . "'
	";
	if ($sqlca->query($sql) < 0){
		$response = print_r(json_encode(array(
			'status' => 'error',
			'message' => 'Error al actualizar',
		)));
	} else {
		$response = print_r(json_encode(array(
			'status' => 'success',
			'message' => 'Registro actualizado',
		)));
	}
}else if($accion == 'getCalculoMargen') {
	//el calculo de margen se activa con el parametro margincheck y tiene que estar descativado actualiza_precio
	$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='actualiza_precio' LIMIT 1;";
	$valor = $sqlca->query($sql);
	$par = $sqlca->fetchRow();

	$sql2 = "SELECT par_valor FROM int_parametros WHERE par_nombre='margincheck' LIMIT 1;";
	$valor2 = $sqlca->query($sql2);
	$par2 = $sqlca->fetchRow();

	if (($par['par_valor'] == '0' || $par['par_valor'] == NULL) && $par2['par_valor'] == '1'){

		$id_producto = $_POST['id_producto'];

		$sql = "
		SELECT
		a.art_descripcion as descripcion,
		tg.tab_descripcion as linea,
		util_fn_precio_articulo('" . $id_producto . "') as precio,
		tg.tab_num_01 as margen
		FROM
		int_articulos a
		LEFT JOIN int_tabla_general tg on tg.tab_tabla='20' and tg.tab_elemento = a.art_linea
		WHERE 
		a.art_codigo = '" . $id_producto . "';
		";

		if ($sqlca->query($sql) < 0){
			$response = array(
				'status' => 'error',
				'message' => 'Producto no encontrado',
			);
		}else{
			$response = array(
				'status' => 'success',
				'data' => $sqlca->fetchRow(),
			);
		}
	}else{
		$response = array(
			'status' => 'error',
			'message' => 'No se cumplen los requisitos'
		);
	}

	echo json_encode($response);
}else if($accion == 'getYearPeriod'){
	$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre='inv_ano_cierre'";

	if ($sqlca->query($sql) < 0){
		$response = array(
			'status' => 'danger',
			'arrData' => NULL,
		);
	} else if ($sqlca->query($sql) === 0){
		$response = array(
			'status' => 'warning',
			'arrData' => NULL,
		);
	} else {
		$response = array(
			'status' => 'success',
			'arrData' => $sqlca->fetchRow(),
		);
	}
	echo json_encode($response);
}