<?php

class ConsultaCuentaxCobrarModel extends Model {

	function obtenerSucursales($alm) {
		global $sqlca;
		
		if(trim($alm) == "")
			$cond = "";
		else
			$cond = " AND ch_almacen = '$alm'"; 
	
		$sql = "SELECT
			    ch_almacen,
			    ch_almacen||' - '||ch_nombre_almacen
			FROM
			    inv_ta_almacenes
			WHERE
			    ch_clase_almacen='1' $cond 
			ORDER BY
			    ch_almacen;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    }

    function CuentasCobrar($almacen,$tipo) {
		global $sqlca;

		if($tipo == 'AC')
			$query = "AND cli.cli_tipo = 'AC'";
		else if($tipo == 'IN')
			$query = "AND cli.cli_tipo = 'IN'";
		else
			$query = "";

		if(!empty($almacen))
			$condalmacen = "AND cab.ch_sucursal = '$almacen'";

		$sql = "
			SELECT
				cliente,
				razsocial,
				moneda,
				sum(total),
				sum(total2),
				sum(total3),
				first(credito)
			FROM
			(SELECT * FROM(
				SELECT
					cli.cli_codigo AS cliente,
					cli.cli_razsocial AS razsocial,
					--sum(cab.nu_importesaldo) AS total,
					(CASE WHEN cab.ch_moneda = '01' THEN 'S/' ELSE '$' END) as moneda,
					(CASE WHEN cab.ch_tipdocumento = '20' THEN SUM(cab.nu_importesaldo * -1) ELSE SUM(cab.nu_importesaldo) END) AS total,
					0::integer AS total2,
					0::integer AS total3,
					CASE WHEN sum(cli.cli_creditosol) is null THEN 0 ELSE sum(cli.cli_creditosol) END as credito,
					cli_ndespacho_efectivo as tipo,
					cli_anticipo as anticipo
				FROM
					ccob_ta_cabecera cab
					LEFT JOIN int_clientes cli ON(cab.cli_codigo = cli.cli_codigo)
				WHERE
					cab.ch_tipdocumento NOT IN('21')
					AND cab.nu_importesaldo > 0
					$condalmacen
					$query
				GROUP BY
					cab.ch_tipdocumento,
					cli.cli_codigo,
					cli.cli_razsocial,
					cab.ch_sucursal,
					cli.cli_ndespacho_efectivo,
					cli_anticipo,
					cab.ch_moneda
				) AS K

				UNION
				(
				SELECT
					cli.cli_codigo AS cliente,
					cli.cli_razsocial AS razsocial,
					(CASE WHEN cab.ch_moneda = '01' THEN 'S/' ELSE '$' END) as moneda,
					0::integer AS total,
					(CASE WHEN cab.ch_tipdocumento = '20' THEN SUM(cab.nu_importetotal * -1) ELSE SUM(cab.nu_importetotal) END) AS total2,
					0::integer AS total3,
					CASE WHEN sum(cli.cli_creditosol) is null THEN 0 ELSE sum(cli.cli_creditosol) END as credito,
					cli_ndespacho_efectivo as tipo,
					cli_anticipo as anticipo
				FROM
					ccob_ta_cabecera cab
					LEFT JOIN int_clientes cli ON(cab.cli_codigo = cli.cli_codigo)
				WHERE
					ch_tipdocumento = '21'
					AND nu_importesaldo > 0
					$condalmacen
					$query
				GROUP BY
					cab.ch_tipdocumento,
					cli.cli_codigo,
					cli.cli_razsocial,
					cab.ch_sucursal,
					cli.cli_ndespacho_efectivo,
					cli_anticipo,
					cab.ch_moneda
				)

				UNION
				(
				SELECT
					cab.ch_cliente AS cliente,
					cli.cli_razsocial AS razsocial,
					'S/' as moneda,
					0::integer AS total,
					0::integer AS total2,
					sum(cab.nu_importe) AS total3,
					CASE WHEN sum(cli.cli_creditosol) is null THEN 0 ELSE sum(cli.cli_creditosol) END as credito,
					cli_ndespacho_efectivo as tipo,
					cli_anticipo as anticipo
				FROM
					val_ta_cabecera cab
					LEFT JOIN int_clientes cli ON(cab.ch_cliente = cli.cli_codigo)
				WHERE
					cab.ch_liquidacion IS NULL
					AND cli.cli_ndespacho_efectivo != '1'
					$condalmacen
					$query
				GROUP BY
					cab.ch_cliente,
					cli.cli_razsocial,
					cab.ch_sucursal,
					cli.cli_ndespacho_efectivo,
					cli_anticipo
				)
			) t
			GROUP BY
				cliente,
				razsocial,
				moneda
			ORDER BY
				razsocial;";

		//echo $sql;
	
		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['cliente']	= $a[0];
			$resultado[$i]['razsocial']	= $a[1];
			$resultado[$i]['moneda']		= $a[2];
			$resultado[$i]['total']		= $a[3];
			$resultado[$i]['total2']	= $a[4];
			$resultado[$i]['total3']	= $a[5];
		}

		return $resultado;

    	}

	function DocumentosCobrar($cliente,$estacion){
		global $sqlca;


		if(!empty($estacion))
			$condalmacen = "AND cab.ch_sucursal = '$estacion'";
		
		$sql = "
			SELECT
				gen.tab_desc_breve AS doc,
				cab.ch_seriedocumento||' - '||cab.ch_numdocumento AS documento,
				to_char(cab.dt_fechaemision,'dd/mm/yyyy') AS femision,
				to_char(cab.dt_fechavencimiento,'dd/mm/yyyy') AS fvencimiento,
				(CASE WHEN cab.ch_moneda = '01' THEN 'S/' ELSE '$' END) as moneda,
				(CASE WHEN cab.ch_tipdocumento = '20' THEN (cab.nu_importetotal * -1) ELSE cab.nu_importetotal END) AS total,
				(CASE WHEN cab.ch_tipdocumento = '20' THEN (cab.nu_importesaldo * -1) ELSE cab.nu_importesaldo END) AS saldo
			FROM
				ccob_ta_cabecera cab
				LEFT JOIN int_clientes cli ON(cab.cli_codigo = cli.cli_codigo)
				LEFT JOIN int_tabla_general gen ON(cab.ch_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
			WHERE
				cab.cli_codigo = '$cliente'
				AND cab.ch_tipdocumento NOT IN('21')
				AND cab.nu_importesaldo > 0
				$condalmacen
			ORDER BY
				cab.dt_fechaemision DESC";

		//echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['doc']			= $a[0];
			$resultado[$i]['documento']		= $a[1];
			$resultado[$i]['femision']		= $a[2];
			$resultado[$i]['fvencimiento']	= $a[3];
			$resultado[$i]['moneda']			= $a[4];
			$resultado[$i]['total']			= $a[5];
			$resultado[$i]['saldo']			= $a[6];
		}

		return $resultado;

	}

	function DocumentosAnticipos($cliente,$estacion){
	global $sqlca;
	
	if(!empty($estacion))
			$condalmacen = "AND cab.ch_sucursal = '$estacion'";

	$sql = "
		SELECT
			gen.tab_desc_breve AS doc,
			cab.ch_seriedocumento||' - '||cab.ch_numdocumento AS documento,
			to_char(cab.dt_fechaemision,'dd/mm/yyyy') AS femision,
			to_char(cab.dt_fechavencimiento,'dd/mm/yyyy') AS fvencimiento,
			(CASE WHEN cab.ch_tipdocumento = '20' THEN (cab.nu_importetotal * -1) ELSE cab.nu_importetotal END) AS total,
			(CASE WHEN cab.ch_tipdocumento = '20' THEN (cab.nu_importesaldo * -1) ELSE cab.nu_importesaldo END) AS saldo
		FROM
			ccob_ta_cabecera cab
			LEFT JOIN int_clientes cli ON(cab.cli_codigo = cli.cli_codigo)
			LEFT JOIN int_tabla_general gen ON(cab.ch_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
		WHERE
			cab.cli_codigo = '$cliente'
			AND ch_tipdocumento = '21'
			AND nu_importesaldo > 0
			$condalmacen
		ORDER BY
			cab.dt_fechaemision DESC;";

	//echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['doc']			= $a[0];
			$resultado[$i]['documento']		= $a[1];
			$resultado[$i]['femision']		= $a[2];
			$resultado[$i]['fvencimiento']		= $a[3];
			$resultado[$i]['total']			= $a[4];
			$resultado[$i]['saldo']			= $a[5];
		}

		return $resultado;

	}

	function DocumentosVales($cliente,$estacion){
	global $sqlca;

	if(!empty($estacion))
			$condalmacen = "AND ch_sucursal = '$estacion'";

		
	$sql = "SELECT
			'VAL' AS doc,
			ch_documento AS documento,
			to_char(dt_fecha,'dd/mm/yyyy') AS femision,
			'' AS fvencimiento,
			nu_importe AS total,
			nu_importe AS saldo
		FROM
			val_ta_cabecera
		WHERE
			ch_cliente = '$cliente'
			AND ch_liquidacion is null
			$condalmacen
		ORDER BY
			dt_fecha DESC;";
	//echo $sql;
		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['doc']		= $a[0];
			$resultado[$i]['documento']	= $a[1];
			$resultado[$i]['femision']	= $a[2];
			$resultado[$i]['fvencimiento']	= $a[3];
			$resultado[$i]['total']		= $a[4];
			$resultado[$i]['saldo']		= $a[5];
		}

		return $resultado;

	}

	function DocumentosCobrarDetalle($cliente,$estacion,$documento){
	global $sqlca;

	if(!empty($estacion))
			$condalmacen = "AND cab.ch_sucursal = '$estacion'";
		
	$sql = "SELECT
			det.art_codigo AS item,
			art.art_descripcion AS producto,
			det.nu_fac_precio AS precio,
			det.nu_fac_cantidad AS cantidad,
			det.nu_fac_valortotal AS total
		FROM
			fac_ta_factura_detalle det
			LEFT JOIN int_articulos art ON(art.art_codigo = det.art_codigo)
			LEFT JOIN ccob_ta_cabecera cab ON(cab.cli_codigo = det.cli_codigo AND cab.ch_seriedocumento = det.ch_fac_seriedocumento AND cab.ch_numdocumento = det.ch_fac_numerodocumento)
		WHERE
			det.cli_codigo = '$cliente'
			AND det.ch_fac_seriedocumento||' - '||det.ch_fac_numerodocumento = '$documento'
			AND cab.ch_tipdocumento NOT IN('21')
			AND cab.nu_importesaldo > 0
			$condalmacen";

	//echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['item']		= $a[0];
			$resultado[$i]['producto']	= $a[1];
			$resultado[$i]['precio']	= $a[2];
			$resultado[$i]['cantidad']	= $a[3];
			$resultado[$i]['total']		= $a[4];
		}

		return $resultado;

	}

	function DocumentosAnticiposDetalle($cliente,$estacion,$documento){
	global $sqlca;

	if(!empty($estacion))
			$condalmacen = "AND cab.ch_sucursal = '$estacion'";
		
	$sql = "SELECT
			det.art_codigo AS item,
			art.art_descripcion AS producto,
			det.nu_fac_precio AS precio,
			det.nu_fac_cantidad AS cantidad,
			det.nu_fac_valortotal AS total
		FROM
			fac_ta_factura_detalle det
			LEFT JOIN int_articulos art ON(art.art_codigo = det.art_codigo)
			LEFT JOIN ccob_ta_cabecera cab ON(cab.cli_codigo = det.cli_codigo AND cab.ch_seriedocumento = det.ch_fac_seriedocumento AND cab.ch_numdocumento = det.ch_fac_numerodocumento)
		WHERE
			det.cli_codigo = '$cliente'
			AND det.ch_fac_seriedocumento||' - '||det.ch_fac_numerodocumento = '$documento'
			AND cab.ch_tipdocumento = '21'
			AND cab.nu_importesaldo > 0
			$condalmacen;";

	//echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['item']		= $a[0];
			$resultado[$i]['producto']	= $a[1];
			$resultado[$i]['precio']	= $a[2];
			$resultado[$i]['cantidad']	= $a[3];
			$resultado[$i]['total']		= $a[4];
		}

		return $resultado;

	}

	function DocumentosValesDetalle($cliente,$estacion,$documento){
	global $sqlca;

	if(!empty($estacion))
			$condalmacen = "AND cab.ch_sucursal = '$estacion'";
		
	$sql = "SELECT
			det.ch_articulo AS item,
			art.art_descripcion AS producto,
			det.nu_precio_unitario AS precio,
			det.nu_cantidad AS cantidad,
			det.nu_importe AS total
		FROM
			val_ta_cabecera cab
			JOIN val_ta_detalle det ON(cab.ch_documento = det.ch_documento AND cab.dt_fecha = det.dt_fecha AND cab.ch_sucursal = det.ch_sucursal)
			LEFT JOIN int_articulos art ON(art.art_codigo = det.ch_articulo)
		WHERE
			cab.ch_cliente = '$cliente'
			AND det.ch_documento = '$documento'
			AND cab.ch_liquidacion is null
			$condalmacen;";

	//echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['item']		= $a[0];
			$resultado[$i]['producto']	= $a[1];
			$resultado[$i]['precio']	= $a[2];
			$resultado[$i]['cantidad']	= $a[3];
			$resultado[$i]['total']		= $a[4];
		}

		return $resultado;

	}
	
}
