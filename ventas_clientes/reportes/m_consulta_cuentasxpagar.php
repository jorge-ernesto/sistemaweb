<?php

class ConsultaCuentaxPagarModel extends Model {

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

    	function CuentasCobrar($almacen) {
		global $sqlca;

		$sql = "SELECT
				cliente,
				razsocial,
				sum(total),
				sum(total2),
				sum(total3)
			FROM
			(SELECT * FROM(
				SELECT
					pro.pro_codigo AS cliente,
					pro.pro_razsocial AS razsocial,
					sum(cab.pro_cab_imptotal) AS total,
					0::integer AS total2,
					0::integer AS total3
				FROM
					cpag_ta_cabecera cab
					LEFT JOIN int_proveedores pro ON(cab.pro_codigo = pro.pro_codigo)
				WHERE
					cab.pro_cab_almacen = '$almacen'
					AND cab.pro_cab_tipdocumento NOT IN('21')
					AND cab.pro_cab_impsaldo > 0
				GROUP BY
					pro.pro_codigo,
					pro.pro_razsocial
				) AS K

				UNION
				(
				SELECT
					pro.pro_codigo AS cliente,
					pro.pro_razsocial AS razsocial,
					sum(cab.pro_cab_imptotal) AS total,
					0::integer AS total2,
					0::integer AS total3
				FROM
					cpag_ta_cabecera cab
					LEFT JOIN int_proveedores pro ON(cab.pro_codigo = pro.pro_codigo)
				WHERE
					cab.pro_cab_almacen = '$almacen'
					AND cab.pro_cab_tipdocumento = '21'
					AND cab.pro_cab_impsaldo > 0
				GROUP BY
					pro.pro_codigo,
					pro.pro_razsocial
				)

				UNION
				(
				SELECT
					pro.pro_codigo AS cliente,
					pro.pro_razsocial AS razsocial,
					0::integer AS total,
					0::integer AS total2,
					sum(cab.mov_costototal) AS total3
				FROM
					inv_ta_compras_devoluciones cab
					LEFT JOIN int_proveedores pro ON(cab.mov_entidad = pro.pro_codigo)
				WHERE
					cab.mov_almacen = '$almacen'
					AND (cab.cpag_tipo_pago IS NULL OR cab.cpag_serie_pago IS NULL OR cab.cpag_num_pago IS NULL)
				GROUP BY
					pro.pro_codigo,
					pro.pro_razsocial
				)
			) t
			GROUP BY
				cliente,
				razsocial
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
			$resultado[$i]['total']		= $a[2];
			$resultado[$i]['total2']	= $a[3];
			$resultado[$i]['total3']	= $a[4];
		}

		return $resultado;

    	}

	function DocumentosCobrar($cliente,$estacion){
	global $sqlca;
		
	$sql = "SELECT
			gen.tab_desc_breve AS doc,
			cab.pro_cab_tipdocumento||' - '||cab.pro_cab_numdocumento AS documento,
			to_char(cab.pro_cab_fechaemision,'dd/mm/yyyy') AS femision,
			to_char(cab.pro_cab_fechavencimiento,'dd/mm/yyyy') AS fvencimiento,
			cab.pro_cab_imptotal AS total,
			cab.pro_cab_impsaldo AS saldo
		FROM
			cpag_ta_cabecera cab
			LEFT JOIN int_proveedores pro ON(cab.pro_codigo = pro.pro_codigo)
			LEFT JOIN int_tabla_general as gen ON(cab.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
		WHERE
			cab.pro_codigo = '$cliente'
			AND cab.pro_cab_almacen = '$estacion'
			AND cab.pro_cab_tipdocumento NOT IN('21')
			AND cab.pro_cab_impsaldo > 0
		ORDER BY
			cab.pro_cab_fechaemision DESC; ";

	echo "<pre>DocumentosCobrar:";
	echo $sql;
	echo "</pre>";

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

	function DocumentosAnticipos($cliente,$estacion){
	global $sqlca;
		
	$sql = "SELECT
			gen.tab_desc_breve AS doc,
			cab.pro_cab_tipdocumento||' - '||cab.pro_cab_numdocumento AS documento,
			to_char(cab.pro_cab_fechaemision,'dd/mm/yyyy') AS femision,
			to_char(cab.pro_cab_fechavencimiento,'dd/mm/yyyy') AS fvencimiento,
			cab.pro_cab_imptotal AS total,
			cab.pro_cab_impsaldo AS saldo
		FROM
			cpag_ta_cabecera cab
			LEFT JOIN int_proveedores pro ON(cab.pro_codigo = pro.pro_codigo)
			LEFT JOIN int_tabla_general as gen ON(cab.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
		WHERE
			cab.pro_codigo = '$cliente'
			AND cab.pro_cab_almacen = '$estacion'
			AND cab.pro_cab_tipdocumento = '21'
			AND cab.pro_cab_impsaldo > 0
		ORDER BY
			cab.pro_cab_fechaemision DESC; ";

	echo "<pre>DocumentosAnticipos:";
	echo $sql;
	echo "</pre>";

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
		
	$sql = "SELECT
			'O/C' AS doc,
			cab.mov_tipdocuref||' - '||cab.mov_docurefe AS documento,
			to_char(cab.mov_fecha,'dd/mm/yyyy') AS femision,
			to_char(cab.mov_fecha,'dd/mm/yyyy') AS fvencimiento,
			SUM(cab.mov_costototal) AS total,
			SUM(cab.mov_costototal) AS saldo
		FROM
			inv_ta_compras_devoluciones cab
			LEFT JOIN int_proveedores pro ON(cab.mov_entidad = pro.pro_codigo)
		WHERE
			cab.mov_entidad = '$cliente'
			AND cab.mov_almacen = '$estacion'
			AND (cab.cpag_tipo_pago IS NULL OR cab.cpag_serie_pago IS NULL OR cab.cpag_num_pago IS NULL) --SOLO LO MUESTRA SI ES NULL
		GROUP BY
			documento,
			femision
		ORDER BY
			femision DESC;";

	echo "<pre>DocumentosVales:";
	echo $sql;
	echo "</pre>";

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
		
	$sql = "SELECT
			det.art_codigo AS item,
			art.art_descripcion AS producto,
			det.mov_costounitario AS precio,
			det.mov_cantidad AS cantidad,
			det.mov_costototal AS total
		FROM
			inv_ta_compras_devoluciones det
			LEFT JOIN int_articulos art ON(art.art_codigo = det.art_codigo)
			INNER JOIN cpag_ta_cabecera cab ON(det.com_num_compra = cab.com_cab_numorden AND cab.pro_codigo = det.mov_entidad AND cab.pro_cab_tipdocumento = det.cpag_tipo_pago AND cab.pro_cab_seriedocumento = det.cpag_serie_pago AND cab.pro_cab_numdocumento = det.cpag_num_pago)
		WHERE
			cab.pro_codigo = '$cliente'
			AND cab.pro_cab_almacen = '$estacion'
			AND cab.pro_cab_tipdocumento||' - '||cab.pro_cab_numdocumento = '$documento'
			AND cab.pro_cab_tipdocumento NOT IN('21')
			AND cab.pro_cab_impsaldo > 0;";

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
		
	$sql = "SELECT
			det.art_codigo AS item,
			art.art_descripcion AS producto,
			det.mov_costounitario AS precio,
			det.mov_cantidad AS cantidad,
			det.mov_costototal AS total
		FROM
			inv_ta_compras_devoluciones det
			LEFT JOIN int_articulos art ON(art.art_codigo = det.art_codigo)
			INNER JOIN cpag_ta_cabecera cab ON(det.com_num_compra = cab.com_cab_numorden AND cab.pro_codigo = det.mov_entidad AND cab.pro_cab_tipdocumento = det.cpag_tipo_pago AND cab.pro_cab_seriedocumento = det.cpag_serie_pago AND cab.pro_cab_numdocumento = det.cpag_num_pago)
		WHERE
			cab.pro_codigo = '$cliente'
			AND cab.pro_cab_almacen = '$estacion'
			AND cab.pro_cab_tipdocumento||' - '||cab.pro_cab_numdocumento = '$documento'
			AND cab.pro_cab_tipdocumento = '21'
			AND cab.pro_cab_impsaldo > 0; ";

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
		
	$sql = "SELECT
			det.art_codigo AS item,
			art.art_descripcion AS producto,
			det.mov_costounitario AS precio,
			det.mov_cantidad AS cantidad,
			det.mov_costototal AS total
		FROM
			inv_ta_compras_devoluciones det
			LEFT JOIN int_articulos art ON(art.art_codigo = det.art_codigo)
		WHERE
			det.mov_entidad = '$cliente'
			AND det.mov_almacen = '$estacion'
			AND det.com_serie_compra||' - '||det.com_num_compra = '$documento'
			AND (det.cpag_tipo_pago IS NULL OR det.cpag_serie_pago IS NULL OR det.cpag_num_pago IS NULL);";

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
