<?php
	include "/sistemaweb/valida_sess.php";
	include "/sistemaweb/functions.php";
	require "/sistemaweb/clases/funciones.php";	
	include "functions.php";
	include "/sistemaweb/include/reportes2.inc.php";
	extract($_REQUEST);
	
	$funcion = new class_funciones;
	$conector_id=$funcion->conectar("","","","","");

	$sql = "SELECT
			SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as liquido,
			SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as liquido_canti,
			SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as glp,
			SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as glp_canti
		FROM
			comb_ta_contometros
		WHERE
			ch_sucursal='" . pg_escape_string($almacen) . "' AND 
			dt_fechaparte BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		";
	
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
			round(sum(VD.galones),2) cantidad,
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
			SUM(t.importe)-SUM(COALESCE(t.km,0)) as importetarjeta
		FROM
			pos_trans" . $ano_del . $mes_del . " t
			JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at)
		WHERE
			t.es = '" . pg_escape_string($almacen) . "' AND
			t.fpago = '2' AND
			t.dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		GROUP BY
			1 ";
	
	$x_tarjetas_credito_detalle = pg_query($conector_id, $sql);
	
	$sql = "SELECT
		SUM(t.importe)-SUM(COALESCE(t.km,0)) AS tarjetascredito
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
 AND td IN ('B','F')
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
			dt_dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		";
	
	$x_depositos_pos = pg_query($conector_id, $sql);
	
	// GASTOS

	/*
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
	*/

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
			--SUM(importe) as sumatotal
			0 as sumatotal
		FROM
			comb_liquidacion_gastos
		WHERE
			es ='" . pg_escape_string($almacen) . "' AND 
			fecha BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		";
	
	$x_liquidacion_gastos_total = pg_query($conector_id, $sql);
	
	$sql ="	SELECT
			SUM(importe) as sfttotal
		FROM
			comb_diferencia_trabajador
		WHERE
			es ='" . pg_escape_string($almacen) . "'
			AND dia BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
		";
	
	$x_sobrantes_faltantes_trabajador = pg_query($conector_id, $sql);

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

	// OTROS INGRESOS BANCOS

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
			AND c.type = '0'
	";

	$x_otros_ingresos = pg_query($conector_id, $sql);


	// OTROS INGRESOS BANCOS

	/*$sql =" SELECT
			i.pay_number documento,
			i.amount otros
		FROM
			c_cash_transaction c
			INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		WHERE
			c.ware_house = '" . pg_escape_string($almacen) . "'
			AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
			AND i.c_bank_id = '0' ";

	$x_otros_ingresos = pg_query($conector_id, $sql);

	$sql =" SELECT
			SUM(i.amount) AS total
		FROM
			c_cash_transaction c
			INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		WHERE
			c.ware_house = '" . pg_escape_string($almacen) . "'
			AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
			AND i.c_bank_id = '0' ";

	$x_otros_totingresos = pg_query($conector_id, $sql);*/

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

	$sql =" SELECT
			SUM(i.amount) AS total
		FROM
			c_cash_transaction c
			INNER JOIN c_cash_transaction_payment i ON(c.c_cash_transaction_id = i.c_cash_transaction_id)
		WHERE
			c.ware_house = '" . pg_escape_string($almacen) . "'
			AND c.d_system BETWEEN '" . pg_escape_string($fecha_del) . "' AND '" . pg_escape_string($fecha_al) . "'
			AND c.type = '1'";

	$x_caja_totegresos = pg_query($conector_id, $sql);

//**************************************** Inventario de combustible ****************************************

	function obtieneParte($fecha_del, $fecha_al, $almacen) {
		global $sqlca;//2011-12-28

		$desde = substr($fecha_del,8,2)."/".substr($fecha_del,5,2)."/".substr($fecha_del,0,4);//01/01/2003
		$hasta = substr($fecha_al,8,2)."/".substr($fecha_al,5,2)."/".substr($fecha_al,0,4);

		$propiedad = obtenerPropiedadAlmacenes();
		$almacenes = obtieneListaEstaciones();

		$sqlA = "SELECT	Co.ch_codigocombustible as Codigo,
				Co.ch_nombrebreve as Producto,
				MD1.nu_medicion as Stock_Inicial,
				MA.compras, 
				C.ventas,
				MD1.nu_medicion - C.ventas as Stock_Final,
				MD2.nu_medicion as Medicion,
				MD2.nu_medicion - (MD1.nu_medicion - C.ventas) as Dia,
				M.mes,
				C.valor as importe

			FROM	(select nu_medicion,ch_tanque from comb_ta_mediciondiaria where ch_sucursal='" . pg_escape_string($almacen) . "' and dt_fechamedicion = to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')-1) MD1

				inner join (select nu_medicion,ch_tanque from comb_ta_mediciondiaria where ch_sucursal='" . pg_escape_string($almacen) . "' and dt_fechamedicion = to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')) MD2
				on MD1.ch_tanque = MD2.ch_tanque

				inner join (select ch_tanque,ch_codigocombustible from comb_ta_tanques where ch_sucursal= '" . pg_escape_string($almacen) . "') T
				on T.ch_tanque = MD1.ch_tanque and T.ch_tanque = MD2.ch_tanque

				left join (select art_codigo,sum(mov_cantidad) as compras from inv_movialma where tran_codigo='21' and date(mov_fecha) between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') AND mov_almacen='$almacen' group by art_codigo) MA
				on MA.art_codigo = T.ch_codigocombustible
	
				inner join (select ch_codigocombustible,sum(nu_ventagalon) - (sum(nu_afericionveces_x_5) * 5) as ventas,sum(nu_ventavalor) - (sum(nu_afericionveces_x_5) * 5  * sum(nu_ventavalor)/CASE WHEN sum(nu_ventagalon)>0 THEN sum(nu_ventagalon) ELSE 1 END) as valor from comb_ta_contometros where ch_sucursal='" . pg_escape_string($almacen) . "' and date(dt_fechaparte) between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') group by ch_codigocombustible) C
				on C.ch_codigocombustible = T.ch_codigocombustible

				inner join comb_ta_combustibles Co
				on Co.ch_codigocombustible = C.ch_codigocombustible

				inner join(
				SELECT T.ch_codigocombustible as combustible,
				sum(MD2.nu_medicion-(MD1.nu_medicion+CASE WHEN MA.compras>0 THEN MA.compras ELSE 0.00 END-C.ventas)) as Mes
			FROM	comb_ta_mediciondiaria MD1

				inner join (select ch_sucursal,ch_tanque,ch_codigocombustible from comb_ta_tanques) T
				on T.ch_tanque = MD1.ch_tanque and T.ch_sucursal=MD1.ch_sucursal

				left join (	select date(mov_fecha) as fecha,art_codigo,sum(mov_cantidad) as compras 
						from inv_movialma 
						where tran_codigo='21'  AND mov_almacen='$almacen'
						group by art_codigo,date(mov_fecha)) MA
				on MA.art_codigo = T.ch_codigocombustible and MA.fecha=MD1.dt_fechamedicion+1

				inner join (select ch_sucursal,dt_fechaparte,ch_codigocombustible,sum(nu_afericionveces_x_5) as afericion,sum(nu_ventagalon)as venta,sum(nu_ventagalon-(nu_afericionveces_x_5*5)) as ventas from comb_ta_contometros group by ch_sucursal,dt_fechaparte,ch_codigocombustible) C
				on C.ch_sucursal = MD1.ch_sucursal and C.dt_fechaparte = MD1.dt_fechamedicion+1 and C.ch_codigocombustible=T.ch_codigocombustible

				inner join comb_ta_combustibles Co
				on Co.ch_codigocombustible = T.ch_codigocombustible

				inner join (select dt_fechamedicion,ch_sucursal,ch_tanque,nu_medicion from comb_ta_mediciondiaria) MD2
				on MD2.dt_fechamedicion=MD1.dt_fechamedicion+1 and MD2.ch_sucursal=MD1.ch_sucursal and MD2.ch_tanque = T.ch_tanque
				 
			WHERE 	MD1.dt_fechamedicion between to_date('01".substr($desde,2,10)."','DD/MM/YYYY')-1 and to_date('" . pg_escape_string($hasta) . "','DD/MM/YYYY')-1
				and MD1.ch_sucursal='" . pg_escape_string($almacen) . "'
			GROUP BY T.ch_codigocombustible) M on M.combustible = T.ch_codigocombustible";

		$sqlA .= " ORDER BY Co.ch_nombrebreve ;";
		//echo '- PARTE: '.$sqlA.' -';

		if ($sqlca->query($sqlA) < 0) 
			return false;
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();		    
			$ch_sucursal = pg_escape_string($almacen);
			$producto = $a[0];
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			$ch_sucursal = $almacenes[$ch_sucursal];

			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['producto'] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['inicial'] = $a[2];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['compras'] = $a[3];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['ventas'] = $a[4];
			@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['porcentaje'] = $a[4]*100;
			if ($a[3]!=''){
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['final'] = $a[5] + $a[3];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['dia'] = $a[7] - $a[3];
			}else{
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['final'] = $a[5];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$producto]['dia'] = $a[7];
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
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['dia'] += ($a[7] - $a[3]);
				} else {
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['final'] += $a[5];
					@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['dia'] += $a[7];
				}
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['producto'] = "TOTAL";
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['inicial'] += $a[2];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['compras'] += $a[3];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['ventas'] += $a[4];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['porcentaje'] = "100";
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['medicion'] += $a[6];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['mes'] += $a[8];
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total']['importe'] += $a[9];
			}
		}
		return $result;	
	}

	function obtenerPropiedadAlmacenes() {
		global $sqlca;
	
		$sql = "SELECT ch_almacen, ch_sucursal
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
//**************************************** Cerrar Inventario de combustible ****************************************

//**************************************** Reporte Caja y Banco ****************************************
// echo "<pre>";
// print_r($_GET);
// echo "</pre>";							

$iAlmacen    = $_GET['almacen'];
$dYear       = $_GET['ano_al'];
$dMonth      = $_GET['mes_al'];
$dDay        = $_GET['dia_al'];
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

/***********************************************************************************************************************************/
/*******************************VARIABLES*****************************************************************************************/
/***********************************************************************************************************************************/

	$venta_combustible 	= pg_fetch_all($x_venta_combustible);
	$venta_tienda 		= pg_fetch_all($x_venta_tienda);
	$vales_credito 		= pg_fetch_all($x_vales_credito);
	$vales_credito_detalle 	= pg_fetch_all($x_vales_credito_detalle);

	$tarjetas_credito_detalle = pg_fetch_all($x_tarjetas_credito_detalle);
	$tarjetas_credito_total   = pg_fetch_all($x_tarjetas_credito_total);
	$tct = number_format($tarjetas_credito_total[0]['tarjetascredito'],2);

	$liquidacion_gastos 	  = pg_fetch_all($x_liquidacion_gastos);
	$liquidacion_gastos_total = pg_fetch_all($x_liquidacion_gastos_total);
	$lgt = number_format($liquidacion_gastos_total[0]['sumatotal'],2);

	$descuentos 	= pg_fetch_all($x_descuentos);
	$difprecio 	= pg_fetch_all($x_difprecio);
	$afericiones 	= pg_fetch_all($x_afericiones);
	$depositos_pos 	= pg_fetch_all($x_depositos_pos);

	$sobrantes_faltantes_trabajador = pg_fetch_all($x_sobrantes_faltantes_trabajador);
	$importe_sobfaltrab 		= number_format($sobrantes_faltantes_trabajador[0]['sfttotal'],2);

	$dif_trabajadores = pg_fetch_all($diferencia_trabajadores); // diferencias de los trabajadores 
	$totmanuales 	= pg_fetch_all($x_totmanuales); // diferencias de los trabajadores 
	$total_manuales = number_format($totmanuales[0]['total'],2); // total de doc manuales 
	$manuales 	= pg_fetch_all($x_manuales); // lista de documentos venta manual

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
	
	$vales_de_credito 	= number_format($vales_credito[0]['valescredito'],2);
	$difprecio_total 	= number_format($difprecio[0]['difprecio'],2);
	$tarjetas_de_credito	= number_format($tarjetas_credito_total[0]['tarjetascredito'],2); //tarjetas de credito total
	$descuentos_total 	= number_format(abs($descuentos[0]['descuentos']),2);
	$afericiones_total 	= number_format($afericiones[0]['afericiones'],2);
	
	$TVCO = $vales_credito[0]['valescredito']+$tarjetas_credito_total[0]['tarjetascredito']+abs($descuentos[0]['descuentos'])+$difprecio[0]['difprecio']+$afericiones[0]['afericiones']; //TVCO: Total Venta Creditos y Otros
	$total_venta_creditos_otros = number_format($TVCO,2);
	
	$TVContado = $TV - $TVCO; //TVContado: Total Venta Contado
	$a1=$TVContado; //TVContado: Total Venta Contado
	$total_venta_contado = number_format($TVContado,2);
	
	$TDP = $depositos_pos[0]['depositospos']; //TDP: Total Depositos POS
	$total_depositos_pos = number_format($TDP,2);
	
	//$DD =  $TDP - $importe_sobfaltrab - $TVContado - $lgt;  //DD: Diferencia Diaria
	$DD =  $TDP - $importe_sobfaltrab - $TVContado;  //DD: Diferencia Diaria
	$diferencia_diaria = number_format($DD,2);

/***********************************************************************************************************************************/
/*******************************REPORTE PDF*****************************************************************************************/
/***********************************************************************************************************************************/

	// nombre del almacen
	$alm = "SELECT ch_almacen as codalma, trim(ch_nombre_almacen) as nomalma FROM inv_ta_almacenes WHERE ch_clase_almacen='1' AND ch_almacen='" . pg_escape_string($almacen) . "' ";
	$x_alm = pg_query($conector_id, $alm);
	$almas = pg_fetch_all($x_alm);
	$nombre_almacen = $almas[0]['codalma']." - ".$almas[0]['nomalma'];


	$cab_def = Array(
		"field"		=>	"               DESCRIPCION",
		"quantity"	=>	"CANTIDAD",
		"value"		=>	"MONTO (S/.)"
	);

	$cab2 = Array(
		"producto"	=>	"PRODUCTO",
		"stk_ini"	=>	"STK. INI.",
		"compras"	=>	"COMPRAS",
		"ventas"	=>	"VENTAS",
		"porcentaje"	=>	" % ",
		"stk_fin"	=>	"STK. FIN",
		"medicion"	=>	"MEDICION",
		"dif_dia"	=>	"DIF.DIA",
		"dif_mes"	=>	"DIF.MES",
		"importe"	=>	"IMP. VENTA"
	);

	$reporte = new CReportes2("P","pt","A4");

	$reporte->Ln();	
	$reporte->Ln();	 

	$reporte->definirCabecera(2, "L", " ");
	$reporte->definirCabecera(2, "L", "Pagina %p                                                                            Fecha:".date("d/m/Y H:i"));
	$reporte->definirCabecera(3, "L", "                              LIQUIDACION DE VENTA TOTAL DEL $dia_del/$mes_del/$ano_del AL $dia_al/$mes_al/$ano_al" );
	$reporte->definirCabecera(4, "L", "");
	$reporte->definirCabecera(5, "L", $nombre_almacen);
//	$reporte->definirCabecera(6, "L", "Fecha:".date("d/m/Y H:i"));
	$reporte->definirCabecera(7, "L", " ");

	$reporte->SetMargins(10,10,10);
	$reporte->SetFont("courier", "", 9);

	$reporte->definirColumna("field",$reporte->TIPO_TEXTO,65,"L");
	$reporte->definirColumna("quantity",$reporte->TIPO_TEXTO,15,"R");//212.21
	$reporte->definirColumna("value",$reporte->TIPO_TEXTO,15,"R");

	$reporte->definirColumna("producto",$reporte->TIPO_TEXTO,13,"L", "_inv");
	$reporte->definirColumna("stk_ini",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("compras",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("ventas",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("porcentaje",$reporte->TIPO_IMPORTE,8,"R", "_inv");
	$reporte->definirColumna("stk_fin",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("medicion",$reporte->TIPO_IMPORTE,13,"R", "_inv");
	$reporte->definirColumna("dif_dia",$reporte->TIPO_IMPORTE,8,"R", "_inv");
	$reporte->definirColumna("dif_mes",$reporte->TIPO_IMPORTE,8,"R", "_inv");
	$reporte->definirColumna("importe",$reporte->TIPO_IMPORTE,13,"R", "_inv");

	$reporte->definirCabeceraPredeterminada($cab_def);
	$reporte->AddPage();

	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"     1. Venta de Combustible","value"=>' '));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"        1.1 Liquido", "quantity"=>$liquido_canti,"value"=>$liquido		));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"        1.2 GLP","quantity"=>$glp_canti, "value"=>$glp			));
	$reporte->Ln();	
	
	$reporte->nuevaFila(array("field"=>"                TOTAL VENTA COMBUSTIBLE","quantity"=>$total_canti_combustible,"value"=>$total_venta_combustible));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     2. Venta de Productos y Promociones","quantity"=>$canti_de_tienda, "value"=>$venta_de_tienda		));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"                TOTAL VENTA","quantity"=>$total_canti,"value"=>$total_venta		));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     3. Credito Clientes","value"=>""		));
	$reporte->Ln();	


 	$val_can = 0; 
	$val_imp = 0; 
	foreach($vales_credito_detalle as $val) {
		$reporte->nuevaFila(array("field"=>"        ".$val['codcliente']."  ".$val['ruc']."  ".$val['cliente'],"quantity"=>$val['cantidad'],"value"=>$val['importe']));
		$val_can = $val_can + $val['cantidad']; 
		$val_imp = $val_imp + $val['importe'];	
	}	
	$reporte->Ln();		
	$reporte->nuevaFila(array("field"=>"                TOTAL CREDITO CLIENTES","quantity"=>"","value"=>$vales_de_credito		));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     4. Tarjetas de Credito","value"=>' '		));
	$reporte->Ln();	

	foreach($tarjetas_credito_detalle as $t){
		$reporte->nuevaFila(array("field"=>"        ".$t['descripciontarjeta'],"value"=>$t['importetarjeta']		));
		$reporte->Ln();	
	}

	$reporte->nuevaFila(array("field"=>"                TOTAL TARJETAS DE CREDITO","value"=>$tct		));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     5. Descuentos","value"=>$descuentos_total		));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     6. Diferencia de Precio de Vales","value"=>$difprecio_total		));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     7. Afericiones","value"=>$afericiones_total		));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"                TOTAL VENTA CREDITOS Y OTROS NO AL CONTADO",		"value"=>$total_venta_creditos_otros));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"                TOTAL DEPOSITOS POS","value"=>$total_depositos_pos		));
	$reporte->Ln();	

	$reporte->nuevaFila(array("field"=>"                TOTAL VENTA CONTADO","value"=>$total_venta_contado		));
	$reporte->Ln();	
	$reporte->lineaH();

	$reporte->nuevaFila(array("field"=>"     8. Sobrantes y Faltantes por Trabajador","value"=>""	));
	$reporte->Ln();	

	foreach($dif_trabajadores as $d) {
		if($d['flag']=='0'){
			$flag='AUTO';
		}else{
			$flag='MANUAL';
		}
		$sumsobfal = $sumsobfal + $d['importe'];
		$a2=$sumsobfal;

		$reporte->nuevaFila(array("field"=>"        ".$d['nom_trabajador'],"quantity"=>$flag,"value"=>$d['importe']));		
	}

	$reporte->Ln();	
	$reporte->nuevaFila(array("field"=>"                TOTAL SOBRANTES Y FALTANTES POR TRABAJADOR","value"=>$importe_sobfaltrab));
	$reporte->Ln();	
	$reporte->lineaH();

	// $reporte->nuevaFila(array("field"=>"     9. Otros","value"=>' '		));
	// $reporte->Ln();	

	// foreach($liquidacion_gastos as $r){
	// 	$reporte->nuevaFila(array("field"=>"        ".$r['descripcion'],"value"=>$r['importe']		));
	// 	$reporte->Ln();	
	// }

	// $reporte->nuevaFila(array("field"=>"       TOTAL OTROS      ","value"=>$lgt		));
	// $reporte->Ln();	
	$reporte->nuevaFila(array("field"=>"       DIFERENCIA DIARIA","value"=>$diferencia_diaria		));
	$reporte->Ln();	

	$reporte->lineaH();
	$reporte->nuevaFila(array("field"=>"     10. Ingresos","value"=>' '		));
	$reporte->Ln();	

	$val_igre = 0; 
	foreach($caja_ingresos_contado_dia as $igre) {
		$val_igre = $val_igre + $igre['ingresos'];
	}
	$reporte->nuevaFila(array("field"=>"        10.1 Ingresos al contado del dia", "value"=>$val_igre		));
	$reporte->Ln();	

	foreach($caja_ingresos_contado_dia as $m){
		$mostrar_solo_si_es_transferencia = ($m['c_cash_mpayment_id'] == 1 || TRIM($m['metodo_pago']) == "DEPOSITO BANCARIO") ? " - " . htmlentities($m['banco']) : "";
		$reporte->nuevaFila(array("field"=>"        ".$m['documento'].$mostrar_solo_si_es_transferencia ,"value"=>$m['ingresos']		));
		$reporte->Ln();	
	}

	$val_igre = 0; 
	foreach($caja_ingresos_cobranzas as $igre) {
		$val_igre = $val_igre + $igre['ingresos'];
	}
	$reporte->nuevaFila(array("field"=>"        10.2 Cobranzas y amortizacion por CC", "value"=>$val_igre		));
	$reporte->Ln();		

	foreach($caja_ingresos_cobranzas as $m){
		$reporte->nuevaFila(array("field"=>"        ".$m['documento'],"value"=>$m['ingresos']		));
		$reporte->Ln();	
	}

	// $reporte->lineaH();
	// $reporte->nuevaFila(array("field"=>"     11. Otros Ingresos","value"=>' '		));
	// $reporte->Ln();	

	// foreach($otros_ingresos as $m){
	// 	$reporte->nuevaFila(array("field"=>"        ".$m['documento'],"value"=>$m['otros']		));
	// 	$reporte->Ln();	
	// }

	$val_egre = 0;
	foreach($caja_egresos as $egre) {
		$val_egre = $val_egre + $egre['egresos'];
	}
	$a5=$val_egre;
	$reporte->lineaH();
	$reporte->nuevaFila(array("field"=>"     12. Egresos","value"=>$val_egre		));
	$reporte->Ln();	

	foreach($caja_egresos as $m){
		$reporte->nuevaFila(array("field"=>"        ".$m['documento'],"value"=>$m['egresos']		));
		$reporte->Ln();	
	}

	$reporte->lineaH();
	$reporte->nuevaFila(array("field"=>"     13. Documentos de Venta Manual","value"=>$total_manuales		));
	$reporte->Ln();	

	foreach($manuales as $m){
		$reporte->nuevaFila(array("field"=>"        ".$m['documento'],"value"=>$m['importe']		));
		$reporte->Ln();	
	}

	$calculo=( ($a1+$a2) - ($a3_1) ) - $a5;
	$calculo = htmlentities(number_format($calculo,2));
	$reporte->lineaH();
	$reporte->nuevaFila(array("field"=>"     14. Saldo Neto a Depositar","value"=>$calculo		));
	$reporte->Ln();	

	$resultado_ = searchCajaBanco($iAlmacen, $dYear, $dMonth, $pos_transYM);
	$result_ = listadoCajaBanco($resultado_, $iAlmacen, $dYear, $dMonth, $dDay);
	$saldo_acumulado_caja_banco = htmlentities(number_format($result_,2));	
	$reporte->lineaH();
	$reporte->nuevaFila(array("field"=>"     15. Saldo acumulado Caja y Banco","value"=>$saldo_acumulado_caja_banco		));
	$reporte->Ln();	

	$reporte->Ln();	$reporte->Ln();	
	$reporte->lineaH();
	$reporte->Ln();	$reporte->Ln();	
	$reporte->lineaH();
	
	if($opcion == 'S') {  // *************** si pide inventario de combustible *****************

		$results1 = obtieneParte($fecha_del, $fecha_al, $almacen);
	
		$reporte->definirCabecera(7, "L", "COMBUSTIBLE");
		$reporte->definirCabecera(8, "L", "");
		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab2, "_inv");
		$reporte->SetFont("courier", "", 7.5);
		$reporte->AddPage();
		

		foreach($results1['propiedades'] as $a => $almacenes) {
			foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				foreach($venta['partes'] as $dt_producto=>$producto) {
					if ($dt_producto != '11620307'){ //Si no es GLP
						$array = $producto;
						$label = $dt_producto;
						$totalventa = $venta['totales']['total']['ventas'];
						if ($label != "Total" && $label != "11620307") {
							$porcentaje = $array['porcentaje']/$totalventa;
						} else {
							$porcentaje = $array['porcentaje'];
						}
						$reporte->nuevaFila(array("producto"=>$array['producto'],"stk_ini"=>$array['inicial'],"compras"=>$array['compras'],"ventas"=>$array['ventas'],
									"porcentaje"=>$porcentaje,"stk_fin"=>$array['final'],"medicion"=>$array['medicion'],"dif_dia"=>$array['dia'],
									"dif_mes"=>$array['mes'],"importe"=>$array['importe']), "_inv");
			    		}
				}				
				$array = $venta['totales']['total'];
				$label = "Total";
				$porcentaje = $array['porcentaje'];
				$reporte->lineaH();
				$reporte->nuevaFila(array("producto"=>$array['producto'],"stk_ini"=>$array['inicial'],"compras"=>$array['compras'],"ventas"=>$array['ventas'],
							"porcentaje"=>$porcentaje,"stk_fin"=>$array['final'],"medicion"=>$array['medicion'],"dif_dia"=>$array['dia'],
							"dif_mes"=>$array['mes'],"importe"=>$array['importe']), "_inv");
				$reporte->Ln();	
				foreach($venta['partes'] as $dt_producto=>$producto) {
		            		if ($dt_producto == '11620307'){ //Si es GLP
						$array = $producto;
						$label = $dt_producto;
						$totalventa = '';

						if ($label != "Total" && $label != "11620307") {
							$porcentaje = $array['porcentaje']/$totalventa;
						} else {
							$porcentaje = $array['porcentaje'];
						}
						$reporte->nuevaFila(array("producto"=>$array['producto'],"stk_ini"=>$array['inicial'],"compras"=>$array['compras'],"ventas"=>$array['ventas'],
									"porcentaje"=>$porcentaje,"stk_fin"=>$array['final'],"medicion"=>$array['medicion'],"dif_dia"=>$array['dia'],
									"dif_mes"=>$array['mes'],"importe"=>$array['importe']), "_inv");
			    		}
				}
		    	}
		}
	}
	$reporte->borrarCabecera();
	$reporte->borrarCabeceraPredeterminada();
	$reporte->Lnew();$reporte->Lnew();

	$reporte->Output("reporteLiquidacionVentasDiarias.pdf","I");
