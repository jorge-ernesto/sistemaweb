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
$op_inv  = $_POST['inv_comb'];
$sobfal  = $_POST['sobfal'];
$sDocumentoManualVenta = $_POST['cbo-documento_venta_manual'];

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

$sql = "SELECT 
		g.tab_descripcion as descripciontarjeta,
		SUM(t.importe) as importetarjeta
	FROM
		pos_trans" . $ano_del . $mes_del . " t
		LEFT JOIN int_tabla_general g ON (trim(t.at) = substring(g.tab_elemento,6,6) AND g.tab_tabla='95' AND g.tab_elemento != '000000')
	WHERE
		t.es		= '" . pg_escape_string($almacen) . "'
		AND t.fpago	= '2'
		AND t.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
	GROUP BY
		1";

$x_tarjetas_credito_detalle = pg_query($conector_id, $sql);

$sql = "SELECT
		SUM(t.importe) AS tarjetascredito
	FROM
		pos_trans" . $ano_del . $mes_del . " t
	WHERE
		t.es 		= '" . pg_escape_string($almacen) . "'
		AND t.fpago	= '2'
		AND t.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

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

$sql = "SELECT 
		SUM(importe) AS afericiones
	FROM
		pos_ta_afericiones
	WHERE
		es='" . pg_escape_string($almacen) . "' AND
		dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$x_afericiones = pg_query($conector_id, $sql);

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

// GASTOS

$sql ="
	SELECT
		i.pay_number descripcion,
		(CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END) AS importe
	FROM
		c_cash_transaction c
		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
	WHERE
		c.ware_house = '" . pg_escape_string($almacen) . "'
		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		AND i.c_bank_id = '0'
		AND c.type = '1'
";


$x_liquidacion_gastos = pg_query($conector_id, $sql);

$sql ="
	SELECT
		SUM((CASE WHEN i.c_currency_id = '2' THEN ROUND(i.amount * c.rate,2) ELSE i.amount END)) AS sumatotal
	FROM
		c_cash_transaction c
		INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
	WHERE
		c.ware_house = '" . pg_escape_string($almacen) . "'
		AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		AND i.c_bank_id = '0'
		AND c.type = '1'
";

$x_liquidacion_gastos_total = pg_query($conector_id, $sql);

$sql ="	SELECT
		b.nombre||'	: '||a.descripcion as descripcion,
		a.importe as importe
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
		ROUND(SUM(importe),2) as sumatotal
	FROM
		comb_liquidacion_gastos
	WHERE
		es ='" . pg_escape_string($almacen) . "' AND 
		fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "' ";

$x_liquidacion_gastos_total = pg_query($conector_id, $sql);

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

$sql = "
SELECT 
 TDOCU.tab_desc_breve||' - '||cab.ch_fac_seriedocumento||' - '||cab.ch_fac_numerodocumento||' - '||cab.cli_codigo||' '||cli.cli_rsocialbreve AS documento,
 cab.nu_fac_valortotal AS importe
FROM
 fac_ta_factura_cabecera AS cab
 JOIN int_clientes AS cli
  USING(cli_codigo)
 JOIN int_tabla_general AS TDOCU
  ON(SUBSTRING(TDOCU.tab_elemento, 5) = cab.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
WHERE 
 cab.ch_fac_tipodocumento IN ('10','35','20')
 AND cab.ch_almacen='" . pg_escape_string($almacen) . "'
 AND cab.dt_fac_fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
 AND (cab.ch_liquidacion='' OR cab.ch_liquidacion IS NULL)
ORDER BY 
 cab.ch_fac_tipodocumento,
 cab.ch_fac_seriedocumento,
 cab.ch_fac_numerodocumento,
 cab.cli_codigo
";

$x_manuales = pg_query($conector_id, $sql);

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


/*echo "<pre>";
echo $sql;
echo "</pre>";*/

//*********************** inventario? ************************************************************
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

//************************************************************************************************


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
	<tr>	<th colspan="5">Inventario de Combustible :
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
	$total_ingresos = number_format($totingresos[0]['total'],2);
	$caja_ingresos 	= pg_fetch_all($x_caja_ingresos); // total ingresos
	$a3				= $totingresos[0]['total'];

	//DETALLE Y TOTAL DE OTROS CAJA INGRESOS
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

	if($sobfal == "S")
		$DD =  $TDP - $importe_sobfaltrab - $TVContado - $lgt;  //DD: Diferencia Diaria: TOTAL DEPOSITOS - DIFERENCIA TRABAJADOR - VENTA CONTADO - OTROS GASTOS
	else
		$DD =  $TDP - $TVContado - $lgt;  //DD: Diferencia Diaria: TOTAL DEPOSITOS - VENTA CONTADO - OTROS GASTOS

	$diferencia_diaria = number_format($DD,2);

?>
<table width="800px" cellspacing="0" cellpadding="3" border="1" align="center">
	<tr bgcolor='#81BEF7'>
		<td width="85%" align="center" style="font-size:1em">Concepto</td>
		<td width="*" align="center" style="font-size:1em">Cantidad</td>
		<td width="*" align="center" style="font-size:1em">Importe</td>
	</tr>
	
	<tr>
		<td width="85%" style="font-size:1.2em" colspan="3">1.&nbsp;Venta de Combustible</td>
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
		<td style="font-size:1.2em">2.&nbsp; Venta de Productos y Promociones</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($canti_de_tienda); ?></p></td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($venta_de_tienda); ?></p></td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>TOTAL VENTA</b></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_canti); ?></b></p></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_venta); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em" colspan="3">3.&nbsp;Vales de Credito</td>
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
		<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Vales de Credito</b></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo number_format($val_can,2); ?></b></p></td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo number_format($val_imp,2); ?></b></p></td>
	</tr>

	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td width="85%" colspan="3" style="font-size:1.2em">4.&nbsp;Tarjetas de Credito</td>
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
		<td style="font-size:1.2em">5.&nbsp;Descuentos</td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($descuentos_total); ?></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">6.&nbsp;Diferencias de Precio de Vales</td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($difprecio_total); ?></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">7.&nbsp;Afericiones</td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities($afericiones_total); ?></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Venta Creditos y Otros No al Contado</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_venta_creditos_otros); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Depositos POS</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_depositos_pos); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Venta Contado</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_venta_contado);?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>

	<?php if($sobfal == "S"){ ?>
	<tr>
		<td style="font-size:1.2em" colspan="3">8.&nbsp;Sobrantes Faltantes por Trabajador</td>
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
	<tr>
		<td width="85%" style="font-size:1.2em">9.&nbsp;Otros</td>
		<td width="*"><p align="right" style="font-size:1.5em">&nbsp;</p></td>
		<td width="*"><p align="right" style="font-size:1.5em">&nbsp;</p></td>
	</tr>

	<?php foreach($liquidacion_gastos as $r){?>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($r['descripcion']); ?></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($r['importe'],2)); ?></p></td>
	</tr>
	<?php }?>

	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>
	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Otros</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($lgt); ?></b></p></td>
	</tr>
	<tr>
		<td colspan="3" style="font-size:1.2em">&nbsp;</td>
	</tr>

	<tr>
		<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Diferencia Diaria</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($diferencia_diaria); ?></b></p></td>
	</tr>
		
	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<tr>
		<td style="font-size:1.2em">10. Ingresos</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b><?php

			foreach($caja_ingresos as $igre) {

				$val_igre = $val_igre + $igre['ingresos'];

			}

			echo htmlentities(number_format($val_igre,2)); ?></b></p></td>

		<?php
			$val_igre = 0; 
			foreach($caja_ingresos as $igre) {?>
		<tr>
			
			<td style="font-size:1em">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($igre['documento']); ?></td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em"><?php echo htmlentities(number_format($igre['ingresos'],2)); ?></p></td>
			<?php
				$val_igre = $val_igre + $igre['ingresos'];
			?>
		</tr>
		<?php 	}	?>

	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<tr>
		<td style="font-size:1.2em">11. Otros Ingresos</b></td>
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

	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>

	<tr>
		<td style="font-size:1.2em">12. Egresos</b></td>
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
		<td style="font-size:1.2em">13. Documentos de Venta Manual</b></td>
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
		<td style="font-size:1.2em" title="((Venta contado + Faltantes)) + (Ingresos + Ingresos otros)) - Egresos">14. Saldo Neto a Depositar</b></td>
		<td>&nbsp;</td>
		<td><p align="right" style="font-size:1.5em"><b>
		
		<?php
		//$a1 venta contado $a2 faltantes $a3 ingresos $a4 ingresos otros $a6 documentos manuales $a5 egresos
		$calculo=(($a1+abs($a2))+($a3+$a4))-$a5;
		echo $calculo;
		?>

		</b></p></td>
	
	<tr><td colspan="3" style="font-size:1.2em">&nbsp;</td></tr>
</table><br>

<?php //-----------------------------------------------------------------------------------
	if($inv_comb == 'S') {

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
