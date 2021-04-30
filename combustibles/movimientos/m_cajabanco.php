<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


class CajaBancoModel extends Model {

	function getAlmacenes($sAlmacen) {
		global $sqlca;
		$cond_almacen = (empty($sAlmacen) ? "" : "AND ch_almacen = '".$sAlmacen."'");

		$sql = "
SELECT
 ch_almacen AS nu_codigo_almacen,
 ch_nombre_almacen AS no_nombre_almacen
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
 ".$cond_almacen."
ORDER BY
 ch_almacen;
		";

        $iStatusSQL = $sqlca->query($sql);
        $arrResponse = array(
            'status_sql' => $iStatusSQL,
            'message_sql' => $sqlca->get_error(),
            'sStatus' => 'danger',
            'sMessage' => 'problemas al obtener almacén',
        );
        if ( $iStatusSQL == 0 ) {
            $arrResponse = array(
                'sStatus' => 'warning',
                'sMessage' => 'No hay registros'
            );
        } else if ( $iStatusSQL > 0 ) {
            $arrDataSQL = $sqlca->fetchAll();
            $arrData = array();
            foreach ($arrDataSQL as $row)
				$arrData[trim($row["nu_codigo_almacen"])] = trim($row["no_nombre_almacen"]);
            $arrResponse = array(
                'sStatus' => 'success',
                'arrData' => $arrData,
            );
        }
        return $arrResponse;
    }

    function getPeriodDate() {
		global $sqlca;
		$sqlca->query("
SELECT
 Y.par_valor AS nu_year,
 M.par_valor AS nu_month
FROM
 int_parametros Y,
 int_parametros M
WHERE
 Y.par_nombre='inv_ano_cierre'
 AND M.par_nombre='inv_mes_cierre';
		");
		$row = $sqlca->fetchRow();
		return $row;
    }

    function search($estacion, $dYear, $dMonth, $pos_transYM) {
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

    function saveFinalBalance($arrData){
    	global $sqlca;
    	$dEndPreviousMonth = $arrData['Fe_Validate_Previous_Year'] . '-' . $arrData['Fe_Validate_Previous_Month'];
    	$dEndPreviousMonth = date("Y-m-t", strtotime($dEndPreviousMonth));

		// Verificar inicio de saldo
		$iStatus = $sqlca->query("SELECT COUNT(*) AS existe FROM c_cashdeposit WHERE ch_almacen = '" . pg_escape_string($arrData['Nu_Warehouse']) . "'");
		$row = $sqlca->fetchRow();

		$response = array(
			'status_query_execution' => $iStatus,
			'message_query_execution' => 'ejecutado',
			'status' => 'danger',
			'message' => 'No existe saldo inicial'
		);
		if($row['existe'] > 0) {//Si existe saldo inicial, podemos empezar a generar saldos para los meses posteriores
			$response = array(
				'status_query_execution' => $iStatus,
				'message_query_execution' => 'ejecutado',
				'status' => 'success',
				'message' => 'Inicio de saldo'
			);

	    	//Verificar si hay data, para poder determinar si se buscará saldo final el último día del mes anterior
			$iStatus = $sqlca->query("SELECT COUNT(*) AS existe FROM pos_aprosys WHERE da_fecha = '" . pg_escape_string($dEndPreviousMonth) . "'");
			$row = $sqlca->fetchRow();

			if($row['existe'] != 0) {//Si hay data, debemos buscar
		    	//Verificar saldo final el último día del mes anterior
		    	$iStatus = $sqlca->query("SELECT COUNT(*) AS existe FROM c_cashdeposit WHERE ch_almacen = '" . pg_escape_string($arrData['Nu_Warehouse']) . "' AND d_system = '" . pg_escape_string($dEndPreviousMonth) . "'");
				$row = $sqlca->fetchRow();

				if($row['existe'] == 0) {//No existe saldo final el último día del mes anterior
					$response = array(
						'status_query_execution' => $iStatus,
						'message_query_execution' => 'ejecutado',
						'status' => 'danger',
						'message' => 'No existe saldo en el Año: ' . $arrData['Fe_Validate_Previous_Year'] . ' - Mes: ' . $arrData['Fe_Validate_Previous_Month']
					);
				} else { //El saldo final existe en el mes anterior
					//Verificar la consolidación, el campo estado debe de ser 0 o no existir el registro para poder iniciar proceso para guardar saldo final del mes
					$iStatus = $sqlca->query("SELECT COUNT(*) AS existe FROM pos_consolidacion WHERE almacen = '" . pg_escape_string($arrData['Nu_Warehouse']) . "' AND dia = '" . pg_escape_string($arrData['Fe_Final_Balance']) . "' AND estado='1'");
					$row = $sqlca->fetchRow();

					$sMessageQueryExecution = 'Advertencia: Día no consolidado, no se ejecutará el proceso de insertar y/o actualizar el saldo final del mes';
					$response = array(
						'status_query_execution' => $iStatus,
						'message_query_execution' => 'ejecutado',
						'status' => 'warning',
						'message' => $sMessageQueryExecution
					);
					if((int)$row['existe'] > 0) {//Si esta consolidado el último día del mes consultado
						//Convertir del formato Y-m-d a mes en letras
						$sMonth = date('F', strtotime($arrData['Fe_Final_Balance']));

						$sqlca->query("SELECT COUNT(*) AS existe FROM c_cashdeposit WHERE ch_almacen = '" . pg_escape_string($arrData['Nu_Warehouse']) . "' AND d_system = '" . pg_escape_string($arrData['Fe_Final_Balance']) . "'");
						$row = $sqlca->fetchRow();

						if ( $row['existe'] == 0) {//No existe
							$sMessageQueryExecution = 'Guardado';
				    		$iStatus = $sqlca->query("
							INSERT INTO c_cashdeposit (
							c_cashdeposit_id,
							created,
							createdby,
							d_system,
							c_currency_id,
							c_bank_id,
							amount,
							doc_number,
							reference,
							ch_almacen
							) VALUES (
							nextval('seq_c_cashdeposit_id'),
							now(),
							1,
							'" . pg_escape_string($arrData['Fe_Final_Balance']) . "',
							1,
							1,
							" . pg_escape_string($arrData['Ss_Final_Balance']) . ",
							0,
							'final balance for " . $sMonth . " - user: " . $_SESSION['auth_usuario'] . " - insert',
							'" . pg_escape_string($arrData['Nu_Warehouse']) . "'
							);
				    		");
						} else {
							$sMessageQueryExecution = 'Actualizado';
							$iStatus = $sqlca->query("
							UPDATE
								c_cashdeposit
							SET
								amount = " . pg_escape_string($arrData['Ss_Final_Balance']) . ",
								created = now(),
								reference = 'final balance for " . $sMonth . " - user: " . $_SESSION['auth_usuario'] . " - update'
							WHERE
								ch_almacen = '" . pg_escape_string($arrData['Nu_Warehouse']) . "'
								AND d_system = '" . pg_escape_string($arrData['Fe_Final_Balance']) . "'
							");
						}

						$response = array(
							'status_query_execution' => $iStatus,
							'message_query_execution' => $sMessageQueryExecution,
							'status' => 'success',
							'message' => 'Saldo ' . $sMessageQueryExecution
						);
				    	if ($iStatus < 0){
				    		$response = array(
				    			'status_query_execution' => $iStatus,
				    			'message_query_execution' => 'problemas para ejecutar sql',
				    			'status' => 'danger',
				    			'message' => 'Problemas al ' . $sMessageQueryExecution,
				    		);
				    	}
					}
				}
			} else {
				$response = array(
					'status_query_execution' => $iStatus,
					'message_query_execution' => 'ejecutado',
					'status' => 'warning',
					'message' => 'Todavía no se ha cerrado fin de mes'
				);
			}
		}
    	return $response;
    }
}
