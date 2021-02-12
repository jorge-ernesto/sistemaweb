<?php
class VarillasModel extends Model {

	function obtenerSucursales($alm) {
		global $sqlca;
		
		if(trim($alm) == "")
			$cond = "";
		else
			$cond = " AND ch_almacen = '$alm'"; 
	
		$sql = "
SELECT
 ch_almacen,
 ch_almacen||' - '||ch_nombre_almacen
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1' $cond 
ORDER BY
 ch_almacen;
		";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();		    
	    	$result[$a[0]] = $a[1];
		}
		return $result;
    }

    function search($fecha, $estacion) {
		global $sqlca;
	
		$pos_trans = "pos_trans".substr($fecha,6,4).substr($fecha,3,2);

		$query="
SELECT
	to_char(C.fecha,'DD/MM/YYYY') AS fecha,
	TC.tipocambio AS tipocambio,
	COMB.total_venta_comb AS total_venta_comb,
	COMB.total_venta_glp AS total_venta_glp,
	AFC.af_comb AS af_comb,
	AFG.af_glp AS af_glp,
	M.total_venta_market AS total_venta_market,
	MANU.total_venta_market_manual AS total_venta_market_manual,
	CRE.clientescredito AS clientescredito,
	COBRA.clientecobranza AS clientecobranza,
	GNV.total_venta_gnv AS total_venta_gnv,
	GNV.creditognv AS creditognv,
	GNV.faltagnv AS faltagnv,
	GNV.sobragnv AS sobragnv
FROM
	(SELECT
		da_fecha AS fecha
	FROM
		pos_aprosys
	WHERE
		da_fecha = to_date('$fecha', 'DD/MM/YYYY')
	GROUP BY
		da_fecha) AS C

	LEFT JOIN
	(SELECT
		tca_fecha AS fecha,
		FIRST(tca_venta_oficial) AS tipocambio
	FROM 
		int_tipo_cambio
	 WHERE 	
		tca_fecha = to_date('$fecha', 'DD/MM/YYYY')
	GROUP BY 
		tca_fecha
	) AS TC ON(TC.fecha = C.fecha)

	LEFT JOIN
	(SELECT
		comb.dt_fechaparte AS fecha, 
		SUM(CASE WHEN comb.ch_codigocombustible!='11620307' THEN (CASE WHEN comb.nu_ventagalon!=0 THEN comb.nu_ventavalor ELSE 0 END) ELSE 0 END) AS total_venta_comb,
		SUM(CASE WHEN comb.ch_codigocombustible='11620307' THEN (CASE WHEN comb.nu_ventagalon!=0 THEN comb.nu_ventavalor ELSE 0 END) ELSE 0 END) AS total_venta_glp
	 FROM 
		comb_ta_contometros comb
	 WHERE 	
		comb.ch_sucursal = '$estacion' 
		and comb.dt_fechaparte = to_date('$fecha', 'DD/MM/YYYY')
	GROUP BY 
		comb.dt_fechaparte
	) AS COMB ON(COMB.fecha = C.fecha)

	LEFT JOIN
	(SELECT 
		af.dia as dia,
		SUM(CASE WHEN af.codigo!='11620307' THEN af.importe ELSE 0 END) AS af_comb
	FROM 
		pos_ta_afericiones af
	WHERE
		af.es = '$estacion' 
		AND af.dia = to_date('$fecha', 'DD/MM/YYYY')
	GROUP BY
		af.dia
	) AS AFC ON(AFC.dia = C.fecha)

	LEFT JOIN
	(SELECT
		af.dia as dia, 
		SUM(CASE WHEN af.codigo='11620307' THEN af.importe ELSE 0 END) AS af_glp
	FROM 
		pos_ta_afericiones af
	WHERE
		af.es = '$estacion' 
		AND af.dia = to_date('$fecha', 'DD/MM/YYYY')
	GROUP BY
		af.dia
	) AS AFG ON(AFG.dia = C.fecha)

	LEFT JOIN
	(SELECT
	 	t.dia,
		sum(t.importe) AS total_venta_market
	 FROM 
		$pos_trans t
	 WHERE 
		t.es = '$estacion'
		AND t.tipo = 'M'
		AND t.dia = to_date('$fecha', 'DD/MM/YYYY')
	GROUP BY
		t.dia
	) AS M ON(M.dia = C.fecha)

	LEFT JOIN
	(SELECT 
		cab.dt_fac_fecha AS fecha,
		sum(det.nu_fac_valortotal) AS total_venta_market_manual
	FROM
		fac_ta_factura_cabecera cab
		INNER JOIN fac_ta_factura_detalle det ON(det.ch_fac_tipodocumento=cab.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=cab.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=cab.ch_fac_numerodocumento)
	WHERE
		cab.ch_almacen = '$estacion'
		AND cab.dt_fac_fecha = to_date('$fecha', 'DD/MM/YYYY')
		AND det.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')
		AND cab.ch_fac_tipodocumento != '45'
	GROUP BY
		cab.dt_fac_fecha
	) AS MANU ON(MANU.fecha = C.fecha)

	LEFT JOIN
	(SELECT
		v.dia as fecha,
		SUM(v.importe) AS clientescredito
	 FROM
		$pos_trans v
		LEFT JOIN int_clientes c ON (v.cuenta = c.cli_codigo)
	 WHERE
		v.es = '$estacion'
		AND v.dia = to_date('$fecha', 'DD/MM/YYYY')
		AND v.grupo != 'D'
		AND c.cli_ndespacho_efectivo != 1
	 GROUP BY
		v.dia 
	) AS CRE ON(CRE.fecha = C.fecha)

	LEFT JOIN
	(SELECT
		c.d_system as fecha,
		(CASE WHEN p.c_currency_id = '02' THEN (SUM(p.amount) * FIRST(c.rate)) ELSE SUM(p.amount) END) AS clientecobranza
	 FROM
		c_cash_transaction c
		LEFT JOIN c_cash_transaction_payment p ON (c.c_cash_transaction_id = p.c_cash_transaction_id)
	 WHERE
		c.ware_house = '$estacion'
		AND p.created = to_date('$fecha', 'DD/MM/YYYY')
		AND c.c_cash_operation_id = 1
		AND c.type = 0
	 GROUP BY
		c.d_system,
		p.c_currency_id
	) AS COBRA ON(COBRA.fecha = C.fecha)

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
		AND gnv.dt_fecha = to_date('$fecha', 'DD/MM/YYYY')
	 GROUP BY
		gnv.dt_fecha
	) AS GNV ON(GNV.fecha = C.fecha)
GROUP BY
	C.fecha,
	TC.tipocambio,
	COMB.total_venta_comb,
	COMB.total_venta_glp,
	AFC.af_comb,
	AFG.af_glp,
	AFG.af_glp,
	M.total_venta_market,
	MANU.total_venta_market_manual,
	CRE.clientescredito,
	COBRA.clientecobranza,
	GNV.total_venta_gnv,
	GNV.creditognv,
	GNV.faltagnv,
	GNV.sobragnv
ORDER BY
	C.fecha;
		";

		echo "<pre>";
		echo $query;
		echo "</pre>";

		if ($sqlca->query($query) < 0)
			return false;
	   
		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['fecha']						= $a[0];
			$resultado[$i]['tipocambio']				= $a[1];
			$resultado[$i]['total_venta_comb']			= $a[2];
			$resultado[$i]['total_venta_glp']			= $a[3];
			$resultado[$i]['af_comb']					= $a[4];
			$resultado[$i]['af_glp']					= $a[5];
			$resultado[$i]['total_venta_market']		= $a[6];
			$resultado[$i]['total_venta_market_manual']	= $a[7];
			$resultado[$i]['clientescredito']			= $a[8];
			$resultado[$i]['clientecobranza']			= $a[9];
			$resultado[$i]['total_venta_gnv']			= $a[10];
			$resultado[$i]['creditognv']				= $a[11];
			$resultado[$i]['faltagnv']					= $a[12];
			$resultado[$i]['sobragnv']					= $a[13];
		}
	
		return $resultado;
    }

	function SearchMovement($fecha, $estacion) {
		global $sqlca;

		$sql = "
SELECT
	b.name || ' ' || a.c_bank_account_id AS nombre,
	p.amount AS soles,
	(CASE WHEN p.c_currency_id = '02' THEN (p.amount * c.rate) END) AS dolares,
	p.created AS deposito
FROM
	c_cash_transaction c
	LEFT JOIN c_cash_transaction_payment p ON (c.c_cash_transaction_id = p.c_cash_transaction_id)
	LEFT JOIN c_bank b ON (p.c_bank_id = b.c_bank_id)
	LEFT JOIN c_bank_account a ON (p.c_bank_id = a.c_bank_id AND p.c_bank_account_id = a.c_bank_account_id)
WHERE
	c.ware_house = '$estacion'
	AND p.created = to_date('$fecha', 'DD/MM/YYYY')
	AND c_cash_operation_id IN(1);--1=Venta Clientes
		";

		echo "<pre>";
		echo $sql;
		echo "</pre>";

		if ($sqlca->query($sql) < 0)
			return false;
	   
		$resultado = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['nombre']	= $a[0];
			$resultado[$i]['soles']		= $a[1];
			$resultado[$i]['dolares']	= $a[2];
			$resultado[$i]['deposito']	= $a[3];
		}
		return $resultado;
	}

}
