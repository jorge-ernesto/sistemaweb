<?php

include "../menu_princ.php";
include "/sistemaweb/functions.php";
require "/sistemaweb/clases/funciones.php";
// include "functions.php";
extract($_REQUEST);

list($dia_del, $mes_del, $ano_del) = explode('/',$_REQUEST['dia_del']);
list($dia_al, $mes_al, $ano_al)    = explode('/',$_REQUEST['dia_al']);

$funcion     = new class_funciones;
$conector_id = $funcion->conectar("","","","","");
$debug       = false;
$no_mostrar  = "style='display:none;'";

if (strlen($dia_del) == 0) {
	rangodefechas();
	$dia_del = $zdiaa; 
	$mes_del = $zmesa; 
	$ano_del = $zanoa;
}
$fecha_del = $ano_del."-".$mes_del."-".$dia_del;

if (strlen($dia_al) == 0) {
	rangodefechas();
	$dia_al = $zdiaa; 
	$mes_al = $zmesa; 
	$ano_al = $zanoa;
}
$fecha_al = $ano_al."-".$mes_al."-".$dia_al;

// verifica fecha de inicio consolidada

if ($_REQUEST['boton'] == "Consultar") {
	$flag = validaDia($fecha_del,$_POST['almacen']);
	if ($flag == 1) { 
		echo "<script>alert('La fecha de inicio debe estar consolidada!');</script>";	
		$dia_del=date("d");
		$mes_del=date("m");
		$ano_del=date("Y");
		$fecha_del = "";
		$dia_al=date("d");
		$mes_al=date("m");
		$ano_al=date("Y");
		$fecha_al = "";		
	}
}
// fin fecha consolidada

$almacen = $_POST['almacen'];
$sobfal  = $_POST['sobfal'];
$sDocumentoManualVenta = $_POST['cbo-documento_venta_manual'];
$op_inv  = $_POST['inv_comb'];

$sql = "SELECT
		SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as liquido,
		SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as liquido_canti,
		SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as glp,
		SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as glp_canti
	FROM
		comb_ta_contometros
	WHERE
		ch_sucursal='" . pg_escape_string($almacen) . "' AND 
		dt_fechaparte BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$x_venta_combustible = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 1. Venta de Combustible</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql = "SELECT
		SUM(d.nu_fac_valortotal) AS ventatienda,
		SUM(d.nu_fac_cantidad) AS cantienda
	FROM
		fac_ta_factura_cabecera f 
		LEFT JOIN int_clientes c on f.cli_codigo=c.cli_codigo 
		LEFT JOIN fac_ta_factura_detalle d ON (f.ch_fac_tipodocumento=d.ch_fac_tipodocumento and f.ch_fac_seriedocumento=d.ch_fac_seriedocumento
							and f.ch_fac_numerodocumento=d.ch_fac_numerodocumento and f.cli_codigo=d.cli_codigo)
	WHERE
		f.ch_fac_seriedocumento='" . pg_escape_string($almacen) . "' AND 
		f.ch_fac_tipodocumento='45' AND
		f.dt_fac_fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'	
		AND c.cli_ndespacho_efectivo != 1 ";

$x_venta_tienda = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 2. Venta de Productos y Promociones</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql = "SELECT
		SUM(CASE WHEN v.ch_estado='1' THEN v.nu_importe ELSE 0 END) AS valescredito
	FROM
		val_ta_cabecera v LEFT JOIN int_clientes c on v.ch_cliente = c.cli_codigo 
	WHERE
		v.ch_sucursal='" . pg_escape_string($almacen) . "' AND 
		v.dt_fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		AND c.cli_ndespacho_efectivo != 1 ";

$x_vales_credito = pg_query($conector_id, $sql); 

$sql = "SELECT   
		VC.ch_cliente as codcliente,
		C.cli_ruc as ruc,
		C.cli_razsocial	as cliente,
		sum(VD.galones) as cantidad,
		sum(VC.nu_importe) as importe 
	FROM
		val_ta_cabecera VC 
		inner join (SELECT ch_sucursal,dt_fecha,ch_documento,sum(nu_cantidad) as galones FROM val_ta_detalle VTD GROUP BY ch_sucursal,dt_fecha,ch_documento) VD 
		on VC.ch_sucursal = VD.ch_sucursal and VC.dt_fecha = VD.dt_fecha and VC.ch_documento = VD.ch_documento 
 		inner join (SELECT cli_codigo,cli_ruc,cli_razsocial FROM int_clientes WHERE cli_ndespacho_efectivo != 1) C 
		on VC.ch_cliente = C.cli_codigo 
	WHERE		
		VC.ch_sucursal='" . pg_escape_string($almacen) . "' 
		AND VC.dt_fecha between '" . pg_escape_string($fecha_del) . "' and '" . pg_escape_string($fecha_al) . "' 
		AND VC.ch_estado='1'
	GROUP BY	
		VC.ch_cliente,
		C.cli_ruc,
		C.cli_razsocial 
	ORDER BY 	
		VC.ch_cliente";

$x_vales_credito_detalle = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 3. Credito Clientes - Detalle</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql = "SELECT 
		g.tab_descripcion as descripciontarjeta,
		SUM(t.importe)-SUM(COALESCE(t.km,0)) as importetarjeta
	FROM
		pos_trans" . $ano_del . $mes_del . " t
		LEFT JOIN int_tabla_general g ON (trim(t.at) = substring(g.tab_elemento,6,6) AND g.tab_tabla='95' AND g.tab_elemento != '000000')
	WHERE
		t.es		= '" . pg_escape_string($almacen) . "'
		AND t.fpago	= '2'
		AND t.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
	GROUP BY
		1";
// echo "<pre>";
// echo $sql;
// echo "</pre>";

$x_tarjetas_credito_detalle = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 4. Tarjetas de Credito</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql = "SELECT
		SUM(t.importe)-SUM(COALESCE(t.km,0)) AS tarjetascredito
	FROM
		pos_trans" . $ano_del . $mes_del . " t
	WHERE
		t.es 		= '" . pg_escape_string($almacen) . "'
		AND t.fpago	= '2'
		AND t.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";
// echo "<pre>";
// echo $sql;
// echo "</pre>";

$x_tarjetas_credito_total = pg_query($conector_id, $sql);

$sql = "
SELECT
 SUM(importe) AS descuentos
FROM
 pos_trans" . $ano_del . $mes_del . "
WHERE
 es='" . pg_escape_string($almacen) . "'
 AND td IN('B','F')
 AND dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' 
 AND grupo='D'
";

$x_descuentos = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 5. Descuentos</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql = "
SELECT
 SUM(t.importe*-1) AS difprecio
FROM
 pos_trans" . $ano_del . $mes_del . " AS t
 LEFT JOIN int_clientes AS c
  ON (c.cli_codigo = t.cuenta AND c.cli_ndespacho_efectivo != 1)
WHERE
 t.es='" . pg_escape_string($almacen) . "'
 AND t.td='N'
 AND t.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' 
 AND grupo='D'
";

$x_difprecio = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 6. Diferencias de Precio de Vales</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql = "SELECT 
		SUM(importe) AS afericiones
	FROM
		pos_ta_afericiones
	WHERE
		es='" . pg_escape_string($almacen) . "' AND
		dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$x_afericiones = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 7. Afericiones</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql ="SELECT 
		SUM(
			CASE 
				WHEN ch_moneda='01'THEN nu_importe 
				WHEN ch_moneda='02'THEN nu_importe * nu_tipo_cambio
			END) AS depositospos
	FROM 
		pos_depositos_diarios
	WHERE 
		ch_almacen='" . pg_escape_string($almacen) . "' AND 
		(ch_valida='S' or ch_valida='s' ) AND
		dt_dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$x_depositos_pos = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query Total Depositos POS</b><br>";
	echo $sql;
	echo "</pre>";
}

// GASTOS

// $sql ="
// 	SELECT
// 		i.pay_number descripcion,
// 		(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS importe
// 	FROM
// 		c_cash_transaction c
// 		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
// 	WHERE
// 		c.ware_house = '" . pg_escape_string($almacen) . "'
// 		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
// 		AND i.c_bank_id = '0'
// 		AND c.type = '1'
// ";


// $x_liquidacion_gastos = pg_query($conector_id, $sql);

// $sql ="
// 	SELECT
// 		SUM((CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END)) AS sumatotal
// 	FROM
// 		c_cash_transaction c
// 		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
// 	WHERE
// 		c.ware_house = '" . pg_escape_string($almacen) . "'
// 		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
// 		AND i.c_bank_id = '0'
// 		AND c.type = '1'
// ";

// $x_liquidacion_gastos_total = pg_query($conector_id, $sql);

$sql ="	SELECT
		b.nombre||'	: '||a.descripcion as descripcion,
		--a.importe as importe
		0 as importe
	FROM
		comb_liquidacion_gastos a
		LEFT JOIN comb_tipo_gasto b ON (a.id_tipo_gasto=b.id_tipo_gasto)
	WHERE
		a.es ='" . pg_escape_string($almacen) . "' AND 
		a.fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
	ORDER BY
		b.id_tipo_gasto";

$x_liquidacion_gastos = pg_query($conector_id, $sql);

$sql ="	SELECT
		--ROUND(SUM(importe),2) as sumatotal
		0 as sumatotal
	FROM
		comb_liquidacion_gastos
	WHERE
		es ='" . pg_escape_string($almacen) . "' AND 
		fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$x_liquidacion_gastos_total = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 9. Otros</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql ="	SELECT
		ROUND(SUM(importe),2) AS sfttotal
	FROM
		comb_diferencia_trabajador
	WHERE
		dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$x_sobrantes_faltantes_trabajador = pg_query($conector_id, $sql);

// mostrando diferencias de trabajadores
$sql ="	SELECT 
		c.ch_codigo_trabajador cod_trabajador, 
		t.ch_apellido_paterno||' '||t.ch_apellido_materno||' '||t.ch_nombre1||' '||t.ch_nombre2 as nom_trabajador,
		c.importe,
		c.flag
	FROM 
		comb_diferencia_trabajador c
		LEFT JOIN pla_ta_trabajadores t ON (c.ch_codigo_trabajador = t.ch_codigo_trabajador)
	WHERE 
		es ='" . pg_escape_string($almacen) . "'
		AND c.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$diferencia_trabajadores = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 8. Sobrantes Faltantes por Trabajador</b><br>";
	echo $sql;
	echo "</pre>";
}

// DETALLE Y TOTAL DE DOCUMENTOS MANUALES

$sql = "SELECT 
		sum(nu_fac_valortotal) AS total
	FROM
		fac_ta_factura_cabecera 
	WHERE 
		ch_fac_tipodocumento IN ('10','35','20') AND 
		ch_almacen='" . pg_escape_string($almacen) . "' AND 
		dt_fac_fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$x_totmanuales = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 13. Documentos de Venta Manual - Total</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql = "
SELECT 
 TDOCU.tab_desc_breve||' - '||cab.ch_fac_seriedocumento||' - '||cab.ch_fac_numerodocumento||' - '||cab.cli_codigo||' '||cli.cli_rsocialbreve AS documento,
 cab.nu_fac_valortotal AS importe
FROM
 fac_ta_factura_cabecera AS cab
 LEFT JOIN int_clientes AS cli
  USING(cli_codigo)
 LEFT JOIN int_tabla_general AS TDOCU
  ON(SUBSTRING(TDOCU.tab_elemento, 5) = cab.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
WHERE 
 cab.ch_fac_tipodocumento IN ('10','35','20')
 AND cab.ch_almacen='" . pg_escape_string($almacen) . "'
 AND cab.dt_fac_fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
 --AND (cab.ch_liquidacion='' OR cab.ch_liquidacion IS NULL)
ORDER BY 
 cab.ch_fac_tipodocumento,
 cab.ch_fac_seriedocumento,
 cab.ch_fac_numerodocumento,
 cab.cli_codigo
";

$x_manuales = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 13. Documentos de Venta Manual - Detalle</b><br>";
	echo $sql;
	echo "</pre>";
}

// INGRESOS BANCOS

$sql ="
	SELECT
		i.pay_number || ' - ' || CASE WHEN cli.cli_rsocialbreve IS NULL THEN c.reference ELSE cli.cli_rsocialbreve END AS documento,
		(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS ingresos
	FROM
		c_cash_transaction c
		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		LEFT JOIN int_clientes cli ON (cli.cli_codigo = c.bpartner)
	WHERE
		c.ware_house = '" . pg_escape_string($almacen) . "'
		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		AND c.type = '0'
	ORDER BY
		documento desc ";

$x_caja_ingresos = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 10. Ingresos</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql ="
	SELECT
		c_op.name || ' - ' || c_mp.name || ' - ' ||  i.pay_number || ' - ' || CASE WHEN cli.cli_rsocialbreve IS NULL THEN c.reference ELSE cli.cli_rsocialbreve END AS documento,
		(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS ingresos,	
		c_mp.c_cash_mpayment_id AS c_cash_mpayment_id,
		c_mp.name AS metodo_pago,
		c_ba.name AS banco
	FROM
		c_cash_transaction c
		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		LEFT JOIN int_clientes cli ON (cli.cli_codigo = c.bpartner)
		LEFT JOIN c_cash_operation c_op ON (c.c_cash_operation_id = c_op.c_cash_operation_id AND c_op.type = 0) 
		LEFT JOIN c_cash_mpayment c_mp ON (i.c_cash_mpayment_id = c_mp.c_cash_mpayment_id)
		LEFT JOIN c_bank c_ba ON (i.c_bank_id = c_ba.c_bank_id)
	WHERE
		c.ware_house = '" . pg_escape_string($almacen) . "'
		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		AND c.type = '0'
		AND c_op.c_cash_operation_id = '2' --VENTAS TICKETS
	ORDER BY
		documento desc ";

$x_caja_ingresos_contado_dia = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 10.1 Ingresos al contado del dia</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql ="
	SELECT
		c_op.name || ' - ' || c_mp.name || ' - ' ||  i.pay_number || ' - ' || CASE WHEN cli.cli_rsocialbreve IS NULL THEN c.reference ELSE cli.cli_rsocialbreve END AS documento,
		(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS ingresos
	FROM
		c_cash_transaction c
		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		LEFT JOIN int_clientes cli ON (cli.cli_codigo = c.bpartner)
		LEFT JOIN c_cash_operation c_op ON (c.c_cash_operation_id = c_op.c_cash_operation_id AND c_op.type = 0) 
		LEFT JOIN c_cash_mpayment c_mp ON (i.c_cash_mpayment_id = c_mp.c_cash_mpayment_id)
		LEFT JOIN c_bank c_ba ON (i.c_bank_id = c_ba.c_bank_id)
	WHERE
		c.ware_house = '" . pg_escape_string($almacen) . "'
		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		AND c.type = '0'
		AND c_op.c_cash_operation_id != '2' --VENTAS TICKETS
	ORDER BY
		documento desc ";

$x_caja_ingresos_cobranzas = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 10.2 Cobranzas y Amortizaciones por CC</b><br>";
	echo $sql;
	echo "</pre>";
}

$sql =" SELECT
		SUM(i.amount) AS total
	FROM
		c_cash_transaction c
		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
	WHERE
		c.ware_house = '" . pg_escape_string($almacen) . "'
		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		AND c.type = '0' ";

$x_caja_totingresos = pg_query($conector_id, $sql);

$sql =" SELECT
			SUM(i.amount) AS total
		FROM
			c_cash_transaction c
			INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
			LEFT JOIN int_clientes cli ON (cli.cli_codigo = c.bpartner)
			LEFT JOIN c_cash_operation c_op ON (c.c_cash_operation_id = c_op.c_cash_operation_id AND c_op.type = 0) 
			LEFT JOIN c_cash_mpayment c_mp ON (i.c_cash_mpayment_id = c_mp.c_cash_mpayment_id)
			LEFT JOIN c_bank c_ba ON (i.c_bank_id = c_ba.c_bank_id)
		WHERE
			c.ware_house = '" . pg_escape_string($almacen) . "'
			AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
			AND c.type = '0'
			AND c_op.c_cash_operation_id = '2' --VENTAS TICKETS"; 

$x_caja_totingresos_contado_dia = pg_query($conector_id, $sql);

$sql =" SELECT
			SUM(i.amount) AS total
		FROM
			c_cash_transaction c
			INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
			LEFT JOIN int_clientes cli ON (cli.cli_codigo = c.bpartner)
			LEFT JOIN c_cash_operation c_op ON (c.c_cash_operation_id = c_op.c_cash_operation_id AND c_op.type = 0) 
			LEFT JOIN c_cash_mpayment c_mp ON (i.c_cash_mpayment_id = c_mp.c_cash_mpayment_id)
			LEFT JOIN c_bank c_ba ON (i.c_bank_id = c_ba.c_bank_id)
		WHERE
			c.ware_house = '" . pg_escape_string($almacen) . "'
			AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
			AND c.type = '0'
			AND c_op.c_cash_operation_id != '2' --VENTAS TICKETS";

$x_caja_totingresos_cobranzas = pg_query($conector_id, $sql);

// EGRESOS BANCOS

$sql ="
	SELECT
		i.pay_number || ' - ' || CASE WHEN pro.pro_rsocialbreve IS NULL THEN c.reference ELSE pro.pro_rsocialbreve END AS documento,
		(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS egresos
	FROM
		c_cash_transaction c
		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		LEFT JOIN int_proveedores pro ON (pro.pro_codigo = c.bpartner)
	WHERE
		c.ware_house = '" . pg_escape_string($almacen) . "'
		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		AND i.c_bank_id = 0
		AND c.type = '1'
	ORDER BY
		documento desc";

$x_caja_egresos = pg_query($conector_id, $sql);
if($debug){
	echo "<pre>";
	echo "<b>Query 12. Egresos</b><br>";
	echo $sql;
	echo "</pre>";
}

/*echo "<pre>";
echo $sql;
echo "</pre>";*/

//**************************************** Inventario de combustible ****************************************
	$desde = $_REQUEST['dia_del'];
	$hasta = $_REQUEST['dia_al'];
	$estaciones = $almacen;
	/*
	if ($flag == 1) { 
		$desde = "";
		$hasta = "";
	}	*/

	function obtieneParte($desde, $hasta, $estaciones) {
		global $sqlca;

		$propiedad = obtenerPropiedadAlmacenes();
		$almacenes = obtieneListaEstaciones();

		$sqlA = "SELECT 
				Co.ch_codigocombustible as Codigo,
				Co.ch_nombrebreve as Producto,
				MD1.nu_medicion as Stock_Inicial,
				MA.compras, 
				C.ventas,
				MD1.nu_medicion - C.ventas as Stock_Final,
				MD2.nu_medicion as Medicion,
				MD2.nu_medicion - (MD1.nu_medicion - C.ventas) as Dia, ";
		if($desde == $hasta)		
		$sqlA .= "	
				(CASE WHEN SAL.cantidad > 0 THEN  M.mes + SAL.cantidad ELSE M.mes END) AS Mes, ";
		else
		$sqlA .= "	
				M.mes AS Mes, ";
		$sqlA .= "
				(C.valval-C.afeafe) as importe,
				ENT.cantidad,
				SAL.cantidad
			FROM 
				(select nu_medicion,ch_tanque from comb_ta_mediciondiaria where ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fechamedicion = to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')-1) MD1

				inner join (select nu_medicion,ch_tanque from comb_ta_mediciondiaria where ch_sucursal='" . pg_escape_string($estaciones) . "' and dt_fechamedicion = to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) MD2
				on MD1.ch_tanque = MD2.ch_tanque

				inner join (select ch_tanque,ch_codigocombustible from comb_ta_tanques where ch_sucursal= '" . pg_escape_string($estaciones) . "') T
				on T.ch_tanque = MD1.ch_tanque and T.ch_tanque = MD2.ch_tanque

				left join (select art_codigo,sum(mov_cantidad) as compras from inv_movialma where tran_codigo='21' and date(mov_fecha) between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') AND mov_almacen='$estaciones' group by art_codigo) MA
				on MA.art_codigo = T.ch_codigocombustible
				
				LEFT JOIN
					(SELECT
						art_codigo codigo,
						round(sum(mov_cantidad),2) as cantidad
					FROM
						inv_movialma alma
					WHERE
						mov_fecha::date BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
						AND tran_codigo = '27'
					GROUP BY
						art_codigo) ENT ON ENT.codigo = T.ch_codigocombustible

				LEFT JOIN
					(SELECT
						art_codigo codigo,
						round(sum(mov_cantidad),2) as cantidad
					FROM
						inv_movialma alma
					WHERE
						mov_fecha::date BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
						AND tran_codigo = '28'
					GROUP BY
						art_codigo) SAL ON SAL.codigo = T.ch_codigocombustible
	
				inner join 
					(select com1.ch_codigocombustible as codcod, sum(com1.nu_ventagalon) - (sum(com1.nu_afericionveces_x_5) * 5) as ventas, sum(com1.nu_ventavalor) valval, 
						(SELECT COALESCE(sum(afe.importe),0) FROM pos_ta_afericiones afe WHERE afe.codigo = com1.ch_codigocombustible AND afe.dia between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') ) as afeafe 
					from comb_ta_contometros com1 
 					where com1.ch_sucursal='" . pg_escape_string($estaciones) . "' and com1.dt_fechaparte between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') 
					group by com1.ch_codigocombustible) C 

				on C.codcod = T.ch_codigocombustible
				
				

				INNER JOIN comb_ta_combustibles Co ON Co.ch_codigocombustible = C.codcod

				INNER JOIN(SELECT
						T.ch_codigocombustible as combustible,
						sum(MD2.nu_medicion-(MD1.nu_medicion+CASE WHEN MA.compras>0 THEN MA.compras ELSE 0.00 END-C.ventas)) - (CASE WHEN first(ENT.cantidad) > 0 THEN first(ENT.cantidad) ELSE 0 END - CASE WHEN first(SAL.cantidad) > 0 THEN first(SAL.cantidad) ELSE 0 END) as Mes
					FROM  
						comb_ta_mediciondiaria MD1

					inner join (select ch_sucursal,ch_tanque,ch_codigocombustible from comb_ta_tanques) T
					on T.ch_tanque = MD1.ch_tanque and T.ch_sucursal=MD1.ch_sucursal

					left join (	select date(mov_fecha) as fecha,art_codigo,sum(mov_cantidad) as compras 
							from inv_movialma 
							where tran_codigo='21'  AND mov_almacen='$estaciones'
							group by art_codigo,date(mov_fecha)) MA
					on MA.art_codigo = T.ch_codigocombustible and MA.fecha=MD1.dt_fechamedicion+1

					inner join (select ch_sucursal,dt_fechaparte,ch_codigocombustible,sum(nu_afericionveces_x_5) as afericion,sum(nu_ventagalon)as venta,sum(nu_ventagalon-(nu_afericionveces_x_5*5)) as ventas from comb_ta_contometros group by ch_sucursal,dt_fechaparte,ch_codigocombustible) C
					on C.ch_sucursal = MD1.ch_sucursal and C.dt_fechaparte = MD1.dt_fechamedicion+1 and C.ch_codigocombustible=T.ch_codigocombustible

					inner join comb_ta_combustibles Co
					on Co.ch_codigocombustible = T.ch_codigocombustible

					inner join (select dt_fechamedicion,ch_sucursal,ch_tanque,nu_medicion from comb_ta_mediciondiaria) MD2
					on MD2.dt_fechamedicion=MD1.dt_fechamedicion+1 and MD2.ch_sucursal=MD1.ch_sucursal and MD2.ch_tanque = T.ch_tanque

					LEFT JOIN
						(SELECT
							art_codigo codigo,
							round(sum(mov_cantidad),2) as cantidad
						FROM
							inv_movialma alma
						WHERE
							mov_fecha::date BETWEEN to_date('01".substr($desde,2,10)."','DD/MM/YYYY')-1 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1
							AND tran_codigo = '27'
						GROUP BY
							art_codigo) ENT ON ENT.codigo = T.ch_codigocombustible

					LEFT JOIN
						(SELECT
							art_codigo codigo,
							round(sum(mov_cantidad),2) as cantidad
						FROM
							inv_movialma alma
						WHERE
							mov_fecha::date BETWEEN to_date('01".substr($desde,2,10)."','DD/MM/YYYY')-1 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1
							AND tran_codigo = '28'
						GROUP BY
							art_codigo) SAL ON SAL.codigo = T.ch_codigocombustible
				 
			WHERE  ";
		
		if($desde == $hasta)		
		$sqlA .= "	
				MD1.dt_fechamedicion between to_date('01".substr($desde,2,10)."','DD/MM/YYYY')-1 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1 ";
		else
		$sqlA .= "	
				MD1.dt_fechamedicion between to_date('" . pg_escape_string($desde) . "','DD/MM/YYYY')-2 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1 ";
		$sqlA .= "

				AND MD1.ch_sucursal='" . pg_escape_string($estaciones) . "'
			GROUP BY 
				T.ch_codigocombustible) M on M.combustible = T.ch_codigocombustible";

		$sqlA .= " ORDER BY Co.ch_nombrebreve ;";
		$debug = false;
		if($debug){
			echo "<pre>";
			echo "<b>Query Inventario de Combustible</b><br>";
			echo $sqlA;
			echo "</pre>";
		}

//		echo '- PARTE: '.$sqlA.' -';

		if ($sqlca->query($sqlA) < 0) 
			return false;
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();		    
			$ch_sucursal = pg_escape_string($estaciones);
			$producto = $a[0];
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];

			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['producto'] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['inicial'] = $a[2];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['compras'] = $a[3];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['transfe'] = $a[10] - $a[11];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['ventas'] = $a[4];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['porcentaje'] = $a[4] * 100;
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['transfesalida'] = $a[11];

			if ($a[3]!=''){
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['final'] = $a[5] + $a[3];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['dia'] = $a[7] - $a[3];
			}else{
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['final'] = $a[5];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['dia'] = $a[7] ;
			}
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['medicion'] = $a[6];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['mes'] = $a[8];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['importe'] = $a[9];

			//TOTALES
			if ($a[1] == 'GLP'){
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['porcentaje'] = "100";
			} else {
				if ($a[3]!=''){
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['final'] += ($a[5] + $a[3]);
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['dia'] += (($a[7] - $a[3]) + $a[11]);

				} else {
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['final'] += $a[5];
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['dia'] += $a[7] + $a[11];
				}
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['producto'] = "TOTAL";
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['inicial'] += $a[2];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['compras'] += $a[3];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['ventas'] += $a[4];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['porcentaje'] = "100";
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['transfe'] = $a[10] - $a[11];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['medicion'] += $a[6] ;
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['mes'] += $a[8];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['importe'] += $a[9];
			}
		}
		return $result;	
	}

	function obtenerPropiedadAlmacenes() {
		global $sqlca;
	
		$sql = "SELECT ch_almacen, 'S' AS ch_almacen_propio
			FROM inv_ta_almacenes
			WHERE ch_clase_almacen='1'; ";

		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();		    
			$result[$a[0]] = $a[1];
		}	
		return $result;
   }   

   function obtieneListaEstaciones() {
		global $sqlca;
	
		$sql = "SELECT ch_almacen, trim(ch_nombre_almacen)
			FROM inv_ta_almacenes
			WHERE ch_clase_almacen='1'
			ORDER BY ch_almacen ; ";
		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}	
		return $result;
   }
    	
   function validaDia($dia, $almacen) {
		global $sqlca;

		/*$sql = "SELECT CASE WHEN ch_poscd='A' THEN ch_posturno ELSE ch_posturno-1 END FROM pos_aprosys WHERE da_fecha='$dia'";
		if ($sqlca->query($sql) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		$maxturno = $a[0];
		
		if(trim($maxturno) == "")
			$maxturno = 0;*/

		$turno = 0;

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";
//echo $sql;
		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

//		echo "devolvio:\n";
//		var_dump($estado);

		if($estado[0] == 1){
			return 0;//No Consolidado
		}else{
			return 1;//Consolidado
		}
	}
//**************************************** Cerrar Inventario de combustible ****************************************

//**************************************** Reporte Caja y Banco ****************************************
//echo "<script>console.log('POST: " . json_encode($_POST) . "')</script>";							

$fecha_explode = explode("/", $_POST['dia_al']);

$iAlmacen    = $_POST['almacen'];
$dYear       = $fecha_explode['2'];
$dMonth      = $fecha_explode['1'];
$dDay        = $fecha_explode['0'];
$pos_transYM = "pos_trans" . $dYear . $dMonth;

function searchCajaBanco($estacion, $dYear, $dMonth, $pos_transYM){
	global $sqlca;

	$query = "
SELECT
	to_char(C.fecha,'DD/MM/YYYY') as fecha,
	COMB.total_venta_comb as total_venta_comb,
	GNV.total_venta_gnv as total_venta_gnv,
	COMB.total_venta_glp as total_venta_glp,
	L.lubricantes as lubrincates,
	O.otros as otros,
	CRE.clientescredito as clientescredito,
	TC.tarjetascredito as tarjetascredito,
	BCP.bcp as bcp,
	BBVA.bbva as bbva,
	SCOT.scotiabank as scotiabank,
	INTER.interbank as interbank,
	FA.faltante as faltante,
	SO.sobrante as sobrante,
	(COALESCE(FAC.facimporte, 0) - COALESCE(NCMANUAL.facimporte, 0)) AS facimporte,
	AFC.af_comb,
	AFG.af_glp,
	DSCTO.descuentos as descuentos,
	GNV.creditognv as creditognv,
	GNV.faltagnv as faltagnv,
	GNV.sobragnv as sobragnv,
	(COALESCE(PRO.promociones, 0) - COALESCE(PRONCMANUAL.promociones, 0)) AS promociones,
	EGRE.egresos,
	OTHER.otherimp,
	COMB.af_comb AS manual_af_comb,
	COMB.af_glp AS manual_af_glp
  FROM	
	(SELECT
		da_fecha AS fecha
	FROM
		pos_aprosys
	WHERE
		TO_CHAR(da_fecha, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
	GROUP BY
		da_fecha) C
	LEFT JOIN
	(SELECT
		comb.dt_fechaparte AS fecha, 
		SUM(CASE WHEN comb.ch_codigocombustible != '11620307' THEN (CASE WHEN comb.nu_ventagalon!=0 THEN comb.nu_ventavalor ELSE 0 END) ELSE 0 END) AS total_venta_comb,
		SUM(CASE WHEN comb.ch_codigocombustible = '11620307' THEN (CASE WHEN comb.nu_ventagalon!=0 THEN comb.nu_ventavalor ELSE 0 END) ELSE 0 END) AS total_venta_glp,
		CAST(SUM(CASE WHEN nu_ventagalon > 0 THEN (CASE WHEN ch_codigocombustible != '11620307' THEN ((nu_ventavalor / nu_ventagalon) * nu_afericionveces_x_5 * 5) ELSE 0 END) END) AS decimal(8,2)) AS af_comb,
		CAST(SUM(CASE WHEN nu_ventagalon > 0 THEN (CASE WHEN ch_codigocombustible = '11620307' THEN ((nu_ventavalor / nu_ventagalon) * nu_afericionveces_x_5 * 5) ELSE 0 END) END) AS decimal(8,2)) AS af_glp,
		ROUND(SUM(comb.nu_descuentos), 2) AS descuentos
	 FROM 
		comb_ta_contometros AS comb
	 WHERE 	
		comb.ch_sucursal = '" . $estacion . "'
		AND TO_CHAR(comb.dt_fechaparte, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
	GROUP BY 
		comb.dt_fechaparte
	) COMB ON (COMB.fecha = C.fecha)
	LEFT JOIN
	(SELECT
		gnv.dt_fecha AS fecha,
		SUM(gnv.tot_surtidor_soles) AS total_venta_gnv,
		SUM(gnv.tot_cli_credito) AS creditognv,
		SUM(gnv.tot_trab_faltantes) AS faltagnv,
		SUM(gnv.tot_trab_sobrantes) AS sobragnv
	 FROM 
		comb_liquidaciongnv AS gnv
	 WHERE 	
		gnv.ch_almacen = '" . $estacion . "'
		AND TO_CHAR(gnv.dt_fecha, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
	 GROUP BY
		gnv.dt_fecha
	) GNV ON (GNV.fecha = C.fecha)
	LEFT JOIN
	(SELECT
		v.dt_fecha AS fecha,
		SUM(v.nu_importe) AS clientescredito
	 FROM
		val_ta_cabecera AS v
		LEFT JOIN int_clientes AS c
			ON (v.ch_cliente = c.cli_codigo)
	 WHERE
		v.ch_sucursal = '" . $estacion . "'
		AND TO_CHAR(v.dt_fecha, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
		AND c.cli_ndespacho_efectivo != 1
		AND v.ch_estado = '1'
	 GROUP BY
		v.dt_fecha 
	) CRE ON (CRE.fecha = C.fecha)
	LEFT JOIN
	(SELECT
	 	t.dia, 
		SUM(t.importe)-SUM(COALESCE(t.km,0)) AS tarjetascredito
	FROM 
		" . $pos_transYM . " AS t
	WHERE 
		t.es = '" . $estacion . "'
		AND t.fpago = '2'
		AND t.td in('B','F')
	GROUP BY
	 	t.dia
	) TC ON (TC.dia = C.fecha)
	LEFT JOIN
	(SELECT
	 	t.dia,
		SUM(t.importe) AS descuentos
	FROM 
		" . $pos_transYM . " AS t
	WHERE 
		t.es = '" . $estacion . "'
		AND t.grupo = 'D'
		AND t.td in('N','B','F')
	GROUP BY
	 	t.dia
	) DSCTO ON (DSCTO.dia = C.fecha)
	LEFT JOIN
	(SELECT
	 	t.dia, 
		SUM(t.importe) AS lubricantes
	 FROM 
		" . $pos_transYM . " AS t
		LEFT JOIN int_articulos AS art
			ON(t.codigo = art.art_codigo)
	 WHERE 
		t.es = '" . $estacion . "'
		AND t.tipo = 'M'
		AND art.art_tipo = '02'
	GROUP BY
		t.dia
	) L ON (L.dia = C.fecha)
	LEFT JOIN
	(SELECT
	 	t.dia,
		SUM(t.importe) AS otros
	 FROM 
		" . $pos_transYM . " AS t
		LEFT JOIN int_articulos AS art
			ON(t.codigo = art.art_codigo)
	 WHERE 
		t.es = '" . $estacion . "'
		AND t.tipo = 'M'
		AND art.art_tipo != '02'
	GROUP BY
		t.dia
	) O ON (O.dia = C.fecha)
	LEFT JOIN
	(SELECT
		c.d_system,
		SUM(CASE WHEN c.c_currency_id=1 THEN i.amount
		ELSE i.amount*c.rate
		END)  AS bcp
	FROM
		c_cash_transaction AS c
		JOIN c_cash_transaction_payment AS i
			ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		JOIN c_cash_transaction_detail AS CD
			ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
	WHERE
		c.ware_house = '" . $estacion . "'
		AND c.c_cash_id = 1--Solo se filtra caja principal
		AND TO_CHAR(c.d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
		AND i.c_bank_id = '1'
		AND c.type = '0'
		AND i.c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE TO_CHAR(d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "' AND bpartner ='99999999')
		AND CD.doc_type NOT IN ('10','35')
	GROUP BY
		c.d_system
	) BCP ON (BCP.d_system = C.fecha)
	LEFT JOIN
	(SELECT
		c.d_system,
		SUM(CASE WHEN c.c_currency_id=1 THEN i.amount
		ELSE i.amount*c.rate
		END)  AS bbva
	FROM
		c_cash_transaction as c
		JOIN c_cash_transaction_payment AS i
			ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		JOIN c_cash_transaction_detail AS CD
			ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
	WHERE
		c.ware_house = '" . $estacion . "'
		AND c.c_cash_id = 1--Solo se filtra caja principal
		AND TO_CHAR(c.d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
		AND i.c_bank_id = '2'
		AND c.type = '0'
		AND i.c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE TO_CHAR(d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "' AND bpartner ='99999999')
		AND CD.doc_type NOT IN ('10','35')
	GROUP BY
		c.d_system
	) BBVA ON (BBVA.d_system = C.fecha)
	LEFT JOIN
	(SELECT
		c.d_system,
		SUM(CASE WHEN c.c_currency_id=1 THEN i.amount
		ELSE i.amount*c.rate
		END)  AS scotiabank
	FROM
		c_cash_transaction AS c
		JOIN c_cash_transaction_payment AS i
			ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		JOIN c_cash_transaction_detail AS CD
			ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
	WHERE
		c.ware_house = '" . $estacion . "'
		AND c.c_cash_id = 1--Solo se filtra caja principal
		AND TO_CHAR(c.d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
		AND i.c_bank_id = '3'
		AND c.type = '0'
		AND CD.doc_type NOT IN ('10','35')
	GROUP BY
		c.d_system 
	) SCOT ON (SCOT.d_system = C.fecha)
	LEFT JOIN
	(SELECT
		c.d_system,
		SUM(CASE WHEN c.c_currency_id=1 THEN i.amount
		ELSE i.amount*c.rate
		END)  AS interbank
	FROM
		c_cash_transaction AS c
		JOIN c_cash_transaction_payment AS i
			ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		JOIN c_cash_transaction_detail AS CD
			ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
	WHERE
		c.ware_house = '" . $estacion . "'
		AND c.c_cash_id = 1--Solo se filtra caja principal
		AND TO_CHAR(c.d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
		AND i.c_bank_id = '4'
		AND c.type = '0'
		AND i.c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE TO_CHAR(d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "' AND bpartner ='99999999')
		AND CD.doc_type NOT IN ('10','35')
	GROUP BY
		c.d_system
	) INTER ON (INTER.d_system = C.fecha)
	LEFT JOIN
	(SELECT
		dia,
		SUM(importe) AS faltante
	 FROM
		comb_diferencia_trabajador
	 WHERE
		importe < 0
		AND es = '" . $estacion . "'
		AND TO_CHAR(dia, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
	GROUP BY
		dia
	) FA ON (FA.dia = C.fecha)
	LEFT JOIN
	(SELECT
		dia,
		SUM(importe) AS sobrante
	 FROM
		comb_diferencia_trabajador
	 WHERE
		importe > 0
		AND es = '" . $estacion . "'
		AND TO_CHAR(dia, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
	 GROUP BY
		dia
	) SO ON (SO.dia = C.fecha)
	LEFT JOIN
	(SELECT
		cab.dt_fac_fecha,
		SUM(det.nu_fac_valortotal) AS facimporte
	FROM
		fac_ta_factura_cabecera AS cab
		JOIN fac_ta_factura_detalle AS det
			USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
		LEFT JOIN int_articulos AS art
			ON(det.art_codigo = art.art_codigo)
	WHERE
		cab.ch_almacen 								= '" . $estacion . "'
		AND art.art_tipo 							= '02'
		AND TO_CHAR(cab.dt_fac_fecha, 'YYYY-MM') 	= '" . $dYear . '-' . $dMonth . "'
		AND cab.ch_fac_tipodocumento NOT IN('45','20')
	GROUP BY
		cab.dt_fac_fecha
	)FAC ON (FAC.dt_fac_fecha = C.fecha)
	LEFT JOIN
	(SELECT
		cab.dt_fac_fecha,
		SUM(det.nu_fac_valortotal) AS facimporte
	FROM
		fac_ta_factura_cabecera AS cab
		JOIN fac_ta_factura_detalle AS det
			USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
		LEFT JOIN int_articulos AS art
			ON(det.art_codigo = art.art_codigo)
	WHERE
		cab.ch_almacen 								= '" . $estacion . "'
		AND art.art_tipo 							= '02'
		AND TO_CHAR(cab.dt_fac_fecha, 'YYYY-MM') 	= '" . $dYear . '-' . $dMonth . "'
		AND cab.ch_fac_tipodocumento IN('20')
	GROUP BY
		cab.dt_fac_fecha
	) NCMANUAL ON (NCMANUAL.dt_fac_fecha = C.fecha)
	LEFT JOIN
	(SELECT 
		af.dia AS dia,
		SUM(af.importe) AS af_comb
	FROM 
		pos_ta_afericiones AS af
	WHERE
		af.es 							= '" . $estacion . "'
		AND af.codigo 					!= '11620307'
		AND TO_CHAR(af.dia, 'YYYY-MM') 	= '" . $dYear . '-' . $dMonth . "'
	GROUP BY
		af.dia
	)AFC ON (AFC.dia = C.fecha)
	LEFT JOIN
	(SELECT
		af.dia AS dia, 
		SUM(af.importe) AS af_glp
	FROM 
		pos_ta_afericiones AS af
	WHERE
		af.es 							= '" . $estacion . "'
		AND af.codigo 					= '11620307'
		AND TO_CHAR(af.dia, 'YYYY-MM') 	= '" . $dYear . '-' . $dMonth . "'
	GROUP BY
		af.dia
	)AFG ON (AFG.dia = C.fecha)
	LEFT JOIN
	(SELECT 
		cab.dt_fac_fecha,
		SUM(TOT.tot_promocion) AS promociones
	FROM
		fac_ta_factura_cabecera AS cab
		JOIN fac_ta_factura_detalle AS det
			USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
		LEFT JOIN int_articulos AS art
			ON(det.art_codigo = art.art_codigo)
		LEFT JOIN (
		SELECT
			cab.ch_fac_tipodocumento, cab.ch_fac_seriedocumento, cab.ch_fac_numerodocumento,
			(CASE WHEN ((cab.nu_fac_descuento1 = 0.00 OR cab.nu_fac_descuento1 IS NULL) AND cab.ch_fac_tiporecargo2 = 'S' AND cab.nu_fac_impuesto1 > 0.00 AND (cab.ch_fac_anulado IS NULL OR cab.ch_fac_anulado != 'S')) THEN
				0
			ELSE
				cab.nu_fac_valortotal
			END) AS tot_promocion
		FROM
			fac_ta_factura_cabecera AS cab
			JOIN fac_ta_factura_detalle AS det
				USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
			LEFT JOIN int_articulos AS art
				ON(det.art_codigo = art.art_codigo)
		WHERE
			cab.ch_almacen = '" . $estacion . "'
			AND art.art_tipo = '08'
			AND TO_CHAR(cab.dt_fac_fecha, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
			AND cab.ch_fac_tipodocumento NOT IN('45','20')
		) AS TOT USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
	WHERE
		cab.ch_almacen 								= '" . $estacion . "'
		AND art.art_tipo 							= '08'
		AND TO_CHAR(cab.dt_fac_fecha, 'YYYY-MM') 	= '" . $dYear . '-' . $dMonth . "'
		AND cab.ch_fac_tipodocumento NOT IN('45','20')
	GROUP BY
		cab.dt_fac_fecha
	)PRO ON (PRO.dt_fac_fecha = C.fecha)
	LEFT JOIN
	(SELECT 
		cab.dt_fac_fecha,
		SUM(TOT.tot_promocion) AS promociones
	FROM
		fac_ta_factura_cabecera AS cab
		JOIN fac_ta_factura_detalle AS det
			USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
		LEFT JOIN int_articulos AS art
			ON(det.art_codigo = art.art_codigo)
		LEFT JOIN (
		SELECT
			cab.ch_fac_tipodocumento, cab.ch_fac_seriedocumento, cab.ch_fac_numerodocumento,
			(CASE WHEN ((cab.nu_fac_descuento1 = 0.00 OR cab.nu_fac_descuento1 IS NULL) AND cab.ch_fac_tiporecargo2 = 'S' AND cab.nu_fac_impuesto1 > 0.00 AND (cab.ch_fac_anulado IS NULL OR cab.ch_fac_anulado != 'S')) THEN
				0
			ELSE
				cab.nu_fac_valortotal
			END) AS tot_promocion
		FROM
			fac_ta_factura_cabecera AS cab
			JOIN fac_ta_factura_detalle AS det
				USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
			LEFT JOIN int_articulos AS art
				ON(det.art_codigo = art.art_codigo)
		WHERE
			cab.ch_almacen = '" . $estacion . "'
			AND art.art_tipo = '08'
			AND TO_CHAR(cab.dt_fac_fecha, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
			AND cab.ch_fac_tipodocumento = '20'
		) AS TOT USING(ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
	WHERE
		cab.ch_almacen 								= '" . $estacion . "'
		AND art.art_tipo 							= '08'
		AND TO_CHAR(cab.dt_fac_fecha, 'YYYY-MM') 	= '" . $dYear . '-' . $dMonth . "'
		AND cab.ch_fac_tipodocumento = '20'
	GROUP BY
		cab.dt_fac_fecha
	)PRONCMANUAL ON (PRONCMANUAL.dt_fac_fecha = C.fecha)
	LEFT JOIN
	(SELECT
		c.d_system,
		SUM(CASE WHEN c.c_currency_id=1 THEN i.amount
		ELSE i.amount*c.rate
		END)  AS egresos
	FROM
		c_cash_transaction AS c
		JOIN c_cash_transaction_payment AS i
			ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		JOIN c_cash_mpayment AS m
			ON (m.c_cash_mpayment_id = i.c_cash_mpayment_id)
	WHERE
		c.ware_house 	= '" . $estacion . "'
		AND c.c_cash_id = 1--Solo se filtra caja principal
		AND c.type 		= '1'
		AND m.banking 	= 0
		AND TO_CHAR(c.d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
	GROUP BY
		c.d_system
	) EGRE ON (EGRE.d_system = C.fecha)
	LEFT JOIN
	(SELECT
		c.d_system,
		SUM(CASE WHEN c.c_currency_id=1 THEN i.amount ELSE i.amount*c.rate END) AS otherimp
	FROM
		c_cash_transaction AS c
		JOIN c_cash_transaction_payment AS i
			ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		JOIN c_cash_transaction_detail AS CD
			ON(c.c_cash_transaction_id = CD.c_cash_transaction_id)
	WHERE
		c.ware_house = '" . $estacion . "'
		AND c.c_cash_id = 1--Solo se filtra caja principal
		AND c.type = 0
		AND TO_CHAR(c.d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "'
		AND i.c_cash_mpayment_id != 1
		AND i.c_cash_transaction_id IN(SELECT c_cash_transaction_id FROM c_cash_transaction WHERE TO_CHAR(d_system, 'YYYY-MM') = '" . $dYear . '-' . $dMonth . "' AND c.c_cash_id = 1 AND bpartner = '99999999')
	GROUP BY
		c.d_system
	) OTHER ON (OTHER.d_system = C.fecha)
GROUP BY
	C.fecha,
	COMB.total_venta_comb,
	AFC.af_comb,
	GNV.total_venta_gnv,
	COMB.total_venta_glp,
	AFG.af_glp,
	L.lubricantes,
	O.otros,
	CRE.clientescredito,
	TC.tarjetascredito,
	BCP.bcp,
	BBVA.bbva,
	SCOT.scotiabank,
	INTER.interbank,
	FA.faltante,
	SO.sobrante,
	FAC.facimporte,
	DSCTO.descuentos,
	GNV.creditognv,
	GNV.faltagnv,
	GNV.sobragnv,
	PRO.promociones,
	EGRE.egresos,
	OTHER.otherimp,
	COMB.af_comb,
	COMB.af_glp,
	NCMANUAL.facimporte,
	PRONCMANUAL.promociones
ORDER BY
	C.fecha;
";


// echo "<pre>";
// echo "QUERY:";
// print_r($query);
// echo "</pre>";


	if ($sqlca->query($query) < 0)
		return false;

	$resultado = array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
		$resultado[$i]['fecha']				= $a[0];
		$resultado[$i]['total_venta_comb']	= $a[1];
		$resultado[$i]['total_venta_gnv']	= $a[2];
		$resultado[$i]['total_venta_glp']	= $a[3];
		$resultado[$i]['lubricantes']		= $a[4];
		$resultado[$i]['otros']				= $a[5];
		$resultado[$i]['clientescredito']	= $a[6];
		$resultado[$i]['tarjetascredito']	= $a[7];
		$resultado[$i]['bcp']				= $a[8];
		$resultado[$i]['bbva']				= $a[9];
		$resultado[$i]['scotiabank']		= $a[10];		
		$resultado[$i]['interbank']			= $a[11];
		$resultado[$i]['faltante']			= $a[12];
		$resultado[$i]['sobrante']			= $a[13];
		$resultado[$i]['facimporte']		= $a[14];
		$resultado[$i]['af_comb']			= $a[15];
		$resultado[$i]['af_glp']			= $a[16];
		$resultado[$i]['descuentos']		= $a[17];
		$resultado[$i]['creditognv']		= $a[18];
		$resultado[$i]['faltagnv']			= $a[19];
		$resultado[$i]['sobragnv']			= $a[20];
		$resultado[$i]['promociones']		= $a[21];
		$resultado[$i]['egresos']			= $a[22];
		$resultado[$i]['otherimp']			= $a[23];
		$resultado[$i]['manual_af_comb']	= $a[24];
		$resultado[$i]['manual_af_glp']		= $a[25];
	}
	return $resultado;
}

function listadoCajaBanco($resultados, $iAlmacen, $dYear, $dMonth, $dDay = "31") {			
	//Get Class
	//$objModelCajaBanco = new CajaBancoModel();

	//Si el mes es Enero, debemos de mostrar Diciembre, ya que el sistema debe de verificar si existe saldo en el mes anterior
	if ($dMonth == '01') {
		$dYear = $dYear - 1;
		$dMonth = '12';
	} else
		$dMonth = $dMonth - 1;

	// Mostrar saldo inicial por mes
	$arrData = array(
		'Nu_Warehouse' => $iAlmacen,
		'Fe_Validate_Previous_Year' => $dYear,
		'Fe_Validate_Previous_Month' => $dMonth,
	);
	//$arrResponse = $objModelCajaBanco->getBalance($arrData);
	$arrResponse = getBalance($arrData);			

	$saldo_acu += (float)$arrResponse['fSaldoInicial'];
	// ./ Saldo Inicial por Mes

	for ($i = 0; $i < count($resultados); $i++) {

		$a 	= $resultados[$i];

		if(empty($a['af_comb']))
			$data_af_comb = $a['manual_af_comb'];
		else
			$data_af_comb = $a['af_comb'];

		if(empty($a['af_glp']))
			$data_af_glp = $a['manual_af_glp'];
		else
			$data_af_glp = $a['af_glp'];

		//Obtenemos dia de data "fecha" del array $resultados
		$fecha_explode = explode('/', $a['fecha']);
		$dia_explode = $fecha_explode['0'];

		//Solo sumara si el dia de data "fecha" del array $resultados, es menor o igual a la "Fecha Final" indicada en el reporte al presionar "Consultar"
		//if($dia_explode <= $dDay){
			$saldo_acu += $a['total_venta_comb'] - $data_af_comb + $a['total_venta_gnv'] + $a['total_venta_glp'] - $data_af_glp + $a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones'] - $a['clientescredito'] - $a['creditognv'] - $a['tarjetascredito'] - $a['egresos'] + ($a['faltante'] + $a['sobrante'] + $a['sobragnv'] - $a['faltagnv']) - $a['bcp'] - $a['bbva'] - $a['scotiabank'] - $a['interbank'] + $a['descuentos']  - $a['otherimp'];
			$saldo_market += ($a['lubricantes'] + $a['facimporte'] + $a['otros'] + $a['promociones']);
		//}		
	}

	return $saldo_acu;
}

function getBalance($arrData){
	global $sqlca;
  //Verificar saldo final el último día del mes anterior
	$dEndPreviousMonth = $arrData['Fe_Validate_Previous_Year'] . '-' . $arrData['Fe_Validate_Previous_Month'];
	$dEndPreviousMonth = date("Y-m-t", strtotime($dEndPreviousMonth));
	$sql = "SELECT COUNT(*) AS existe, amount FROM c_cashdeposit WHERE ch_almacen = '" . pg_escape_string($arrData['Nu_Warehouse']) . "' AND d_system = '" . pg_escape_string($dEndPreviousMonth) . "' GROUP BY amount";
	$iStatus = $sqlca->query($sql);
  $arrResponse = array(
	  'status_query_execution' => $iStatus,
	  'message_query_execution' => 'problemas para ejecutar sql',
	  'status' => 'danger',
	  'message' => 'Problemas al obtener saldo inicial',
  );
  if ( $iStatus == 0 ) {
	  $arrResponse = array(
		  'status_query_execution' => $iStatus,//BD
		  'message_query_execution' => 'ejecutado',//BD
		  'status' => 'warning',
		  'message' => 'No existe saldo inicial para el Año: ' . $arrData['Fe_Validate_Previous_Year'] . ' - Mes: ' . $arrData['Fe_Validate_Previous_Month'],
		  'fSaldoInicial' => 0,
	  );
  } else if ( $iStatus > 0 ) {
	  $row = $sqlca->fetchRow();
	  if($row['existe'] != '0') {//No existe saldo final el último día del mes anterior
		  $arrResponse = array(
			  'status_query_execution' => $iStatus,//BD
			  'message_query_execution' => 'ejecutado',//BD
			  'status' => 'success',
			  'message' => 'Saldo inicial encontrado',
			  'fSaldoInicial' => (float)$row['amount'],
		  );
	  }
  }
  return $arrResponse;
}
//**************************************** Cerrar Reporte Caja y Banco ****************************************

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>OCS - Liquidacion de ventas diarias</title>

	    <link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
		<script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
		<script charset="utf-8" type="text/javascript">
			window.onload = function() {
				$(function() {
					$.datepicker.regional['es'] = {
						closeText: 'Cerrar',
						prevText: '<Ant',
						nextText: 'Sig>',
						currentText: 'Hoy',
						monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
						monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
						dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
						dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
						dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
						weekHeader: 'Sm',
						dateFormat: 'dd/mm/yy',
						firstDay: 1,
						isRTL: false,
						showMonthAfterYear: false,
						yearSuffix: ''
					};

					$.datepicker.setDefaults($.datepicker.regional['es']);

					$( "#txt-fecha-ini" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})

					$( "#txt-fecha-fin" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})
				});
			}
		</script>
	</head>
<link href="styles/tabla.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="/sistemaweb/assets/css/style.css">
<tr>
	<td><p align="center" style="font-size:2.0em"><b>LIQUIDACION DE VENTA TOTAL</b></p></td>
</tr>
<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<hr noshade>
<form action="" method="post" name = "frm">
<table border="1" align="center">
	<tr>
		<th colspan="5">Consultar por Fecha</th>
   	</tr>
	<tr>
		<th>Fecha inicio: <input id="txt-fecha-ini" type="text" name="dia_del" size="10" value="<?php echo $dia_del.'/'.$mes_del.'/'.$ano_del ?>"></th>
		<th>Fecha final: <input id="txt-fecha-fin" type="text" name="dia_al" size="10" value="<?php echo $dia_al.'/'.$mes_al.'/'.$ano_al ?>"></th>
		<!--
		<th>Del: <input id="txt-fecha-ini" type="text" name="dia_del" size="10" value="<?php echo $dia_del.'/'.$mes_del.'/'.$ano_del ?>">&nbsp;<a href="javascript:show_calendar('frm.dia_del');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></th>
		<th>Al: <input type="text" name="dia_al" size="10" value="<?php echo $dia_al.'/'.$mes_al.'/'.$ano_al ?>">&nbsp;<a href="javascript:show_calendar('frm.dia_al');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;">
			-->
		<th><input type="submit" name="boton" value="Consultar"></th>
    </tr>
	<tr>	<th colspan="5">Almacen :
		<select name="almacen">
		<?php
			$almacen_sql = "select ch_almacen, ch_nombre_almacen from inv_ta_almacenes where ch_clase_almacen='1' order by ch_almacen;";
			$x_almacen = pg_query($conector_id, $almacen_sql);
			$i = 0;

			while($i<pg_num_rows($x_almacen)) {
				$rs = pg_fetch_array($x_almacen,$i);
				$codigo = $rs[0];
				$desc_almacen = $rs[1];
				if($almacen==trim($rs[0])) {
					echo "<option selected value=".trim($rs[0]).">".$rs[0]." - ".$rs[1]."</option>";
				} else {
					echo "<option value=".trim($rs[0]).">".$rs[0]." - ".$rs[1]."</option>";
				}
				$i++;
			}
		?>
		</select>
	</tr>
	<tr>
		<th colspan="5">Sobrantes Y Faltantes de Trabajador:
		<select name="sobfal">
			<option value="S"><?php echo "Si" ?></option>
			<option value="N"><?php echo "No" ?></option>
		</select>
	</tr>
	<tr>
		<th colspan="5">Documentos de venta manual:
		<select name="cbo-documento_venta_manual">
			<option value="S"><?php echo "Si" ?></option>
			<option value="N"><?php echo "No" ?></option>
		</select>
	</tr>
	<tr>	
		<th colspan="5">Inventario de Combustible :
		<select name="inv_comb">
		<?php
		$opcion = Array();
		$opcion['N'] = "No";
		$opcion['S'] = "Si";		

		foreach($opcion as $opt  => $lab) {
			if($opt == $op_inv) {
				echo "<option selected value=".$opt.">".$lab."</option>";
			} else {
				echo "<option value=".$opt.">".$lab."</option>";
			}
		}
		?>
		</select>
	</tr>	
</table>

<div align="center"><br>
<button name="fm" value="<?php echo $fm;?>" onClick="javascript:parent.location.href='/sistemaweb/combustibles/LiquidacionVentasPDF.php?almacen=<?php echo urlencode($almacen);?>&fecha_del=<?php echo urlencode($fecha_del);?>&fecha_al=<?php echo urlencode($fecha_al);?>&ano_del=<?php echo urlencode($ano_del);?>&mes_del=<?php echo urlencode($mes_del);?>&dia_del=<?php echo urlencode($dia_del);?>&ano_al=<?php echo urlencode($ano_al);?>&mes_al=<?php echo urlencode($mes_al);?>&dia_al=<?php echo urlencode($dia_al);?>&opcion=<?php echo urlencode($op_inv);?>';return false"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>
</div>

</form>
<?php

	$venta_combustible	= pg_fetch_all($x_venta_combustible);
	$venta_tienda 		= pg_fetch_all($x_venta_tienda);
	$vales_credito 		= pg_fetch_all($x_vales_credito);
	$vales_credito_detalle 	= pg_fetch_all($x_vales_credito_detalle);

	$tarjetas_credito_detalle = pg_fetch_all($x_tarjetas_credito_detalle);
	$tarjetas_credito_total   = pg_fetch_all($x_tarjetas_credito_total);
	$tct = number_format($tarjetas_credito_total[0]['tarjetascredito'],2);

	$liquidacion_gastos 	  = pg_fetch_all($x_liquidacion_gastos);
	$liquidacion_gastos_total = pg_fetch_all($x_liquidacion_gastos_total);
	$lgt 			  = number_format($liquidacion_gastos_total[0]['sumatotal'],2);
	//$lgt = $liquidacion_gastos_total[0]['sumatotal'];

	$descuentos 	= pg_fetch_all($x_descuentos);
	$difprecio 	= pg_fetch_all($x_difprecio);
	$afericiones  	= pg_fetch_all($x_afericiones);
	$depositos_pos 	= pg_fetch_all($x_depositos_pos);

	$sobrantes_faltantes_trabajador = pg_fetch_all($x_sobrantes_faltantes_trabajador);
	//$importe_sobfaltrab 		= number_format($sobrantes_faltantes_trabajador[0]['sfttotal'], 2);

	$dif_trabajadores = pg_fetch_all($diferencia_trabajadores); // diferencias de los trabajadores

	foreach($dif_trabajadores as $d){
		$importe_sobfaltrab = $importe_sobfaltrab  + $d['importe'];
	}

	//var_dump($importe_sobfaltrab);

	//DETALLE Y TOTAL DE DOCUMENTOS MANUALES
	$totmanuales 	= pg_fetch_all($x_totmanuales);
	$total_manuales = number_format($totmanuales[0]['total'],2);
	$manuales 	    = pg_fetch_all($x_manuales); // lista de documentos venta manual
	$a6 			= $totmanuales[0]['total'];

	//DETALLE Y TOTAL DE CAJA INGRESOS
	$totingresos	= pg_fetch_all($x_caja_totingresos);
	$totingresos_contado_dia = pg_fetch_all($x_caja_totingresos_contado_dia);
	$totingresos_cobranzas	 = pg_fetch_all($x_caja_totingresos_cobranzas);
	$total_ingresos = number_format($totingresos[0]['total'],2);
	$caja_ingresos 	= pg_fetch_all($x_caja_ingresos); // total ingresos
	$caja_ingresos_contado_dia = pg_fetch_all($x_caja_ingresos_contado_dia); // total ingresos
	$caja_ingresos_cobranzas   = pg_fetch_all($x_caja_ingresos_cobranzas); // total ingresos
	$a3				= $totingresos[0]['total'];
	$a3_1				= $totingresos_contado_dia[0]['total'];
	$a3_2				= $totingresos_cobranzas[0]['total'];

	//DETALLE Y TOTAL DE OTROS CAJA INGRESOS - ESTO NO HACE CONSULTA A NADA
	$totingresoso 	= pg_fetch_all($x_otros_totingresos);
	$total_other	= number_format($totingresoso[0]['total'],2);
	$otros_ingresos	= pg_fetch_all($x_otros_ingresos); // total otros ingresos
	$a4				= $totingresoso[0]['total'];


	//DETALLE Y TOTAL DE CAJA EGRESOS
	$totegresos 	= pg_fetch_all($x_caja_totegresos);
	$total_egresos  = number_format($totegresos[0]['total'],2);
	$caja_egresos 	= pg_fetch_all($x_caja_egresos); // total egresos

	$liquido 	= number_format($venta_combustible[0]['liquido'], 2);
	$liquido_canti 	= number_format($venta_combustible[0]['liquido_canti'], 2);
	$glp 		= number_format($venta_combustible[0]['glp'], 2);
	$glp_canti 	= number_format($venta_combustible[0]['glp_canti'], 2);
	$TVCombustible 	= $venta_combustible[0]['liquido'] + $venta_combustible[0]['glp']; //TVCombustible: Total Venta Combustible
	$TVCombustible_canti 	= $venta_combustible[0]['liquido_canti'] + $venta_combustible[0]['glp_canti']; //TVCombustible: Total Venta Combustible CANTIDAD
	$total_venta_combustible = number_format($TVCombustible, 2);
	$total_canti_combustible = number_format($TVCombustible_canti, 2);
	
	$VT = $venta_tienda[0]['ventatienda']; //VT: Venta Tienda
	$CT = $venta_tienda[0]['cantienda']; //CT: Cantidad Tienda
	$venta_de_tienda = number_format($VT, 2);
	$canti_de_tienda = number_format($CT, 2);
	
	$TVC = $TVCombustible_canti + $CT; //TVC: Total Venta Cantidad
	$TV  = $TVCombustible + $VT; //TV: Total Venta
	$total_venta = number_format($TV, 2);
	$total_canti = number_format($TVC, 2);
	
	$vales_de_credito	= number_format($vales_credito[0]['valescredito'],2);
	$difprecio_total 	= number_format($difprecio[0]['difprecio'],2);
	$tarjetas_de_credito 	= number_format($tarjetas_credito_total[0]['tarjetascredito'],2); //tarjetas de credito total
	$descuentos_total 	= number_format(abs($descuentos[0]['descuentos']),2);
	$afericiones_total 	= number_format($afericiones[0]['afericiones'],2);
	
	$TVCO = $vales_credito[0]['valescredito']+$tarjetas_credito_total[0]['tarjetascredito']+abs($descuentos[0]['descuentos'])+$difprecio[0]['difprecio']+$afericiones[0]['afericiones']; //TVCO: Total Venta Creditos y Otros
	$total_venta_creditos_otros = number_format($TVCO,2);
	
	$TVContado = $TV - $TVCO;
	$a1=$TVContado; //TVContado: Total Venta Contado
	$total_venta_contado = number_format($TVContado,2);
	
	$TDP = $depositos_pos[0]['depositospos']; //TDP: Total Depositos POS
	$total_depositos_pos = number_format($TDP,2);

	// if($sobfal == "S")
	// 	$DD =  $TDP - $importe_sobfaltrab - $TVContado - $lgt;  //DD: Diferencia Diaria: TOTAL DEPOSITOS - DIFERENCIA TRABAJADOR - VENTA CONTADO - OTROS GASTOS
	// else
	// 	$DD =  $TDP - $TVContado - $lgt;  //DD: Diferencia Diaria: TOTAL DEPOSITOS - VENTA CONTADO - OTROS GASTOS

	//CAMBIO REALIZADO PARA NO RESTAR "OTROS GASTOS"
	if($sobfal == "S")
		$DD =  $TDP - $importe_sobfaltrab - $TVContado;  //DD: Diferencia Diaria: TOTAL DEPOSITOS - DIFERENCIA TRABAJADOR - VENTA CONTADO - OTROS GASTOS
	else
		$DD =  $TDP - $TVContado;  //DD: Diferencia Diaria: TOTAL DEPOSITOS - VENTA CONTADO - OTROS GASTOS

	$diferencia_diaria = number_format($DD,2);

?>
<table width="800px" cellspacing="0" cellpadding="3" border="1" align="center">
	<tr bgcolor='#81BEF7'>
		<td width="85%" align="center" style="font-size:1em">Concepto</td>
		<td width="*" align="center" style="font-size:1em">Cantidad</td>
		<td width="*" align="center" style="font-size:1em">Importe</td>
	</tr>
	
	<tr>
		<td width="85%" style="font-size:1.2em" colspan="3">1.&nbsp;Venta de Combustible</td> <!-- 1. Venta de Combustible -->
	</tr>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;1.1.&nbsp;Liquido</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($liquido_canti); ?></p></td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($liquido); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;1.2.&nbsp;GLP</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($glp_canti); ?></p></td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($glp); ?></p></td>
	</tr>
	<tr>
		<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Venta Combustible</b></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_canti_combustible); ?></b></p></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_venta_combustible); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">2.&nbsp; Venta de Productos y Promociones</td> <!-- 2. Venta de Productos y Promociones -->
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($canti_de_tienda); ?></p></td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($venta_de_tienda); ?></p></td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Venta</b>
			&nbsp;<b style="font-size:0.6em; color:red;">(1+2)</b>
		</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_canti); ?></b></p></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_venta); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em" colspan="3">3.&nbsp;Credito Clientes</td> <!-- 3. Credito Clientes -->
	</tr>

	<?php 	$val_can = 0; 
		$val_imp = 0; 
		foreach($vales_credito_detalle as $val) {?>
	<tr>
		<td style="font-size:1em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($val['codcliente']).'&nbsp;&nbsp'.htmlentities($val['ruc']).'&nbsp;&nbsp'.htmlentities($val['cliente']); ?></td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($val['cantidad'],2)); ?></p></td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($val['importe'],2)); ?></p></td>
		<?php 	$val_can = $val_can + $val['cantidad']; 
			$val_imp = $val_imp + $val['importe'];
		?>
	</tr>
	<?php 	}	?>

	<tr>
		<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Credito Clientes</b></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo number_format($val_can,2); ?></b></p></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo number_format($val_imp,2); ?></b></p></td>
	</tr>

	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td width="85%" colspan="3" style="font-size:1.2em">4.&nbsp;Tarjetas de Credito</td> <!-- 4. Tarjetas de Credito -->
	</tr>

	<?php foreach($tarjetas_credito_detalle as $t){?>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($t['descripciontarjeta']); ?></td>
		<td><p align="right" style="font-size:1.5em">&nbsp;</p></td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($t['importetarjeta'],2)); ?></p></td>
	</tr>
	<?php }?>

	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Tarjetas de Credito</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($tct); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">5.&nbsp;Descuentos</td> <!-- 5. Descuentos -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($descuentos_total); ?></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">6.&nbsp;Diferencias de Precio de Vales</td> <!-- 6. Diferencias de Precio de Vales -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($difprecio_total); ?></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">7.&nbsp;Afericiones</td> <!-- 7. Afericiones -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($afericiones_total); ?></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Venta Creditos y Otros No al Contado</b>			
			<br><br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style="font-size:0.6em; color:red">(3+4+5+6+7)<b>
		</td> <!-- Total Venta Creditos y Otros No al Contado -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_venta_creditos_otros); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Efectivo en Boveda (Total Depositos POS)</b>
		</td> <!-- Total Efectivo en Boveda (Total Depositos POS) -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_depositos_pos); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Venta Contado</b>
			<br><br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style="font-size:0.6em; color:red">(Total Venta - Total Venta Creditos)<b>
		</td> <!-- Total Venta Contado -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_venta_contado);?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>

	<?php if($sobfal == "S"){ ?>
	<tr>
		<td style="font-size:1.2em" colspan="3">8.&nbsp;Sobrantes Faltantes por Trabajador</td> <!-- 8. Sobrantes Faltantes por Trabajador -->
	</tr>

	<?php foreach($dif_trabajadores as $d){?>
	 <tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($d['nom_trabajador']); ?></td>
		<td style="font-size:0.9em"><?php 
			if($d['flag']=='0'){
				echo htmlentities('AUTO');
			}else{
				echo htmlentities('MANUAL');
			}
		?></td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($d['importe'],2)); ?></p></td>
	</tr> 
	<?php $sumsobfal = $sumsobfal + $d['importe'];
		  $a2=$sumsobfal;
		
		}?>
	<tr>
		<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Sobrantes y Faltantes por Trabajador</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities(number_format($sumsobfal,2)); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<?php } else { ?>

	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	
	<?php } ?>
	<tr <?= $no_mostrar ?>>
		<td width="85%" style="font-size:1.2em">9.&nbsp;Otros</td> <!-- 9. Otros -->
		<td width="*"><p align="right" style="font-size:1.5em">&nbsp;</p></td>
		<td width="*"><p align="right" style="font-size:1.5em">&nbsp;</p></td>
	</tr>

	<?php foreach($liquidacion_gastos as $r){?>
	<tr <?= $no_mostrar ?>>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($r['descripcion']); ?></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($r['importe'],2)); ?></p></td>
	</tr>
	<?php }?>

	<tr <?= $no_mostrar ?>>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr <?= $no_mostrar ?>>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Otros</b></td> <!-- Total otros -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($lgt); ?></b></p></td>
	</tr>
	<tr <?= $no_mostrar ?>>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>

	<tr>
		<td style="font-size:1.2em">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Diferencia Diaria</b>
			<br><br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style="font-size:0.6em; color:red">(Total Efectivo en Boveda - Sobrantes y Faltantes - Total Venta Contado)<b>
		</td> <!-- Diferencia Diaria -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($diferencia_diaria); ?></b></p></td>
	</tr>
		
	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<tr>
		<td style="font-size:1.2em" colspan="3">10.&nbsp;Ingresos</td> <!-- 10. Ingresos -->
	</tr>

	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;10.1&nbsp;Ingresos al contado del dia</b></td> <!-- 10.1 Ingresos al contado del dia -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php

			$val_igre = 0; 
			foreach($caja_ingresos_contado_dia as $igre) {

				$val_igre = $val_igre + $igre['ingresos'];

			}

			echo htmlentities(number_format($val_igre,2)); ?></b></p></td>

		<?php
			$val_igre = 0; 
			foreach($caja_ingresos_contado_dia as $igre) {?>
		<tr>
			
			<td style="font-size:1em">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($igre['documento']); ?>
				<?php echo ($igre['c_cash_mpayment_id'] == 1 || TRIM($igre['metodo_pago']) == "DEPOSITO BANCARIO") ? " - " . htmlentities($igre['banco']) : ""; ?>
			</td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($igre['ingresos'],2)); ?></p></td>
			<?php
				$val_igre = $val_igre + $igre['ingresos'];
			?>
		</tr>
		<?php 	}	?>

	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>	

	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;10.2&nbsp;Cobranzas y Amortizaciones por CC</b></td> <!-- 10.2 Cobranzas y Amortizaciones por CC -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php

			$val_igre = 0; 
			foreach($caja_ingresos_cobranzas as $igre) {

				$val_igre = $val_igre + $igre['ingresos'];

			}

			echo htmlentities(number_format($val_igre,2)); ?></b></p></td>

		<?php
			$val_igre = 0; 
			foreach($caja_ingresos_cobranzas as $igre) {?>
		<tr>
			
			<td style="font-size:1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($igre['documento']); ?></td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($igre['ingresos'],2)); ?></p></td>
			<?php
				$val_igre = $val_igre + $igre['ingresos'];
			?>
		</tr>
		<?php 	}	?>

	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<tr <?= $no_mostrar ?>>
		<td style="font-size:1.2em">11. Otros Ingresos</b></td> <!-- 11. Otros Ingresos -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php

			foreach($otros_ingresos as $other) {

				$val_other = $val_other + $other['otros'];

			}

			echo htmlentities(number_format($val_other,2)); ?></b></p></td>

		<?php
			$val_other = 0; 
			foreach($otros_ingresos as $other) {?>
		<tr>
			
			<td style="font-size:1em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($other['documento']); ?></td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($other['otros'],2)); ?></p></td>
			<?php
				$val_other = $val_other + $other['otros'];
			?>
		</tr>
		<?php 	}	?>

	<tr <?= $no_mostrar ?>><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<tr>
		<td style="font-size:1.2em">12. Egresos</b></td> <!-- 12. Egresos -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php

			foreach($caja_egresos as $egre) {

				$val_egre = $val_egre + $egre['egresos'];

			}

			echo htmlentities(number_format($val_egre,2)); $a5=$val_egre; ?></b></p></td>
		<?php
			$val_egre = 0; 
			foreach($caja_egresos as $egre) {?>
		<tr>
			
			<td style="font-size:1em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($egre['documento']); ?></td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($egre['egresos'],2)); ?></p></td>
			<?php
				$val_egre = $val_egre + $egre['egresos'];
			?>
		</tr>
		<?php 	}	?>

	
	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<?php
	if($sDocumentoManualVenta == "S"){ ?>
	<tr>
		<td style="font-size:1.2em">13. Documentos de Venta Manual</b></td> <!-- 13. Documentos de Venta Manual -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_manuales); ?></b></p></td>
		<?php
		foreach($manuales as $m){ ?>
		<tr>
			<td style="font-size:1em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($m['documento']); ?></td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($m['importe'],2)); ?></p></td>
		</tr>
	<?php
			}
	} ?>

	<!-- EDS Sr de la Soledad -->
	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<tr>
		<td style="font-size:1.2em"> <!-- title="((Venta contado + Faltantes)) + (Ingresos + Ingresos otros)) - Egresos" -->
			14. Saldo Neto a Depositar
			<br><br>
			<b style="font-size:0.6em; color:red">(Total Venta Contado + Sobrantes y Faltantes - Ingresos al contado del dia - Egresos)<b>
		</td> <!-- 14. Saldo Neto a Depositar -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b>
		
		<?php
		//$a1 venta contado $a2 faltantes $a3 ingresos $a4 ingresos otros $a6 documentos manuales $a5 egresos
		//$calculo=( ($a1+abs($a2)) + ($a3+$a4) ) - $a5;		

		//$a1 venta contado $a2 faltantes $a3 ingresos $a3_1 ingresos_contado_dia $a3_2 ingreso_cobranzas $a4 ingresos otros $a6 documentos manuales $a5 egresos
		$calculo=( ($a1+$a2) - ($a3_1) ) - $a5; //ESTO QUEDA
		$calculo = htmlentities(number_format($calculo,2));
		echo $calculo;
		?>

		</b></p></td>
	
	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<tr>
		<td style="font-size:1.2em"> <!-- title="((Venta contado + Faltantes)) + (Ingresos + Ingresos otros)) - Egresos" -->
			15. Saldo acumulado Caja y Banco			
		</td> <!-- 14. Saldo Neto a Depositar -->
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b>
		
		<?php		
			$resultado_ = searchCajaBanco($iAlmacen, $dYear, $dMonth, $pos_transYM);
			$result_ = listadoCajaBanco($resultado_, $iAlmacen, $dYear, $dMonth, $dDay);
			$saldo_acumulado_caja_banco = htmlentities(number_format($result_,2));
			echo $saldo_acumulado_caja_banco;
		?>

		</b></p></td>
	
	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>
</table><br>

<?php 
	//**************************************** Inventario de combustible ****************************************
	if($op_inv == 'S') {

		$results1 = obtieneParte($desde, $hasta, $estaciones);

		$result  = '<br><table border="1" align="center" cellspacing="0">';
		$result .= '<tr><td colspan="11"><h3>COMBUSTIBLES</h3></td></tr><tr>';
		$result .= '<th>&nbsp;&nbsp;PRODUCTO&nbsp;&nbsp;</th>';
		$result .= '<th>&nbsp;&nbsp;STOCK INICIAL&nbsp;&nbsp;</th>';
		$result .= '<th>&nbsp;&nbsp;COMPRAS&nbsp;&nbsp;</th>';
		$result .= '<th>&nbsp;&nbsp;VENTAS&nbsp;&nbsp;</th>';
		$result .= '<th>&nbsp;&nbsp;%&nbsp;&nbsp;</th>';
		$result .= '<th>&nbsp;&nbsp;TRANSFERENCIAS&nbsp;&nbsp;</th>';
		$result .= '<th>&nbsp;&nbsp;STOCK FINAL&nbsp;&nbsp;</th>';
		$result .= '<th>&nbsp;&nbsp;MEDICION&nbsp;&nbsp;</th>';
		$result .= '<th colspan="2">DIFERENCIAS</th>';
		$result .= '<th>IMPORTE VENTA</th>';
		$result .= '</tr><tr>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<td>&nbsp;</td>';
		$result .= '<th>DIA</th>';
		$result .= '<th>MES</th>';
		$result .= '<td>&nbsp;</td>';
		$result .= '</tr>';
		$numfilas = 0;

		foreach($results1['propiedades'] as $a => $almacenes) {
			foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				foreach($venta['partes'] as $dt_producto=>$producto) {
					if ($dt_producto != '11620307'){ //Si no es GLP
		            			$numfilas= $numfilas +1;
			    			$result .= imprimirLinea($producto, $dt_producto,$venta['totales']['total']['ventas']);
			    		}
				}
				$result .= imprimirLinea($venta['totales']['total'], "Total");
				$result .= '<tr><td colspan="11">&nbsp;</td></tr><tr>';
				$numfilas = 0;
				foreach($venta['partes'] as $dt_producto=>$producto) {
		            		if ($dt_producto == '11620307'){ //Si es GLP
					    	$numfilas= $numfilas +1;
					    	$result .= imprimirLinea($producto, $dt_producto,'');
			    		}
				}
		    	}
		}
		$result .= '</table><br><br>';
		echo $result;
	}

	function imprimirLinea($array, $label,$totalventa = "") {

		$result  = '<tr>';
		$decimal = 0;

		if ($label == "Total") {
			$negrita1 = ' style="color:blue; font-weight:bold"">';
			$negrita2 = '';
		} else {
			$negrita1 = '>';
			$negrita2 = '';
		} 

		$result .= '<td align="left" style="font-weight:bold">'. htmlentities($array['producto']) . '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['inicial'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['compras'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['ventas'], 2, '.', ',')) .$negrita2. '</td>';

		/* Si no es el total ni GLP hallamos el porcentaje de c/producto con respecto al total */
		if ($label != "Total" && $label != "11620307") {
			$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['porcentaje']/$totalventa, 2, '.', ',')) .$negrita2. '</td>';
		} else {
			$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['porcentaje'], 0, '.', ',')) .$negrita2. '</td>';
		}

		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['transfe'] , 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['final'] + $array['transfe'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['medicion'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['dia'] + $array['transfesalida'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right"' .$negrita1. htmlentities(number_format($array['mes'], 2, '.', ',')) .$negrita2. '</td>';
		$result .= '<td align="right" style="font-weight:bold">' . htmlentities(number_format($array['importe'], 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		return $result;

	}
?>

</body>
</html>
