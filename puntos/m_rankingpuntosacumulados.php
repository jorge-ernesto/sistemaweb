<?php

class RankingPuntosAcumuladosModel {

	function tmListado($fechaini,$fechafin,$sucursal,$pp, $pagina, $estado) {
		global $sqlca;

		list($dia_fi,$mes_fi,$anio_fi) = explode('/',pg_escape_string($fechaini));
		$fecha_inicio = $anio_fi.'-'.$mes_fi.'-'.$dia_fi;
		list($dia_ff,$mes_ff,$anio_ff) = explode('/',pg_escape_string($fechafin));
		$fecha_fin = $anio_ff.'-'.$mes_ff.'-'.$dia_ff;

		$cond = "";
		$cond2 = "";

		if(pg_escape_string($sucursal) != '')
			$cond = " AND ptm.ch_sucursal = '" . pg_escape_string($sucursal) . "' ";

		if ($estado == 'S')//Solo muestra cuentas activas
			$cond2 = " AND ptc.isactive = 1 ";

		$query = "
		SELECT
			ptc.nu_cuenta_numero as cuenta,
			TO_CHAR(ptc.dt_fecha_creacion, 'DD/MM/YYYY') as fecha_creacion_cuenta,	
			MAX(ptc.ch_cuenta_dni) as dni,
			MAX(ptc.ch_cuenta_apellidos||' '||ptc.ch_cuenta_nombres) as cliente,
			MAX(ptc.ch_cuenta_telefono1) as telefono,
			SUM(ptm.nu_punto_puntaje) as puntosacumulados,
			
			FIRST(ptc.nu_cuenta_puntos) as nu_puntaje_actual,    					

			TO_CHAR(MAX(ptm.dt_punto_fecha), 'DD/MM/YYYY') as ultdespacho,
			COALESCE(alma.ch_nombre_breve_almacen, ptc.ch_sucursal) as ch_sucursal,

			(
			SELECT
				SUM(PTOACU.nu_punto_puntaje) AS nu_acu_puntos
			FROM
				prom_ta_cuentas CUENT
			LEFT JOIN prom_ta_tarjetas AS PTOTAR ON(PTOTAR.id_cuenta=CUENT.id_cuenta)
			LEFT JOIN prom_ta_movimiento_puntos AS PTOACU ON(PTOTAR.id_tarjeta=PTOACU.id_tarjeta)
			WHERE
				PTOACU.nu_punto_tipomov = 1
				AND PTOACU.dt_punto_fecha >= '" . pg_escape_string($fecha_fin) . "'
				AND CUENT.id_cuenta = FIRST(ptc.id_cuenta)
			GROUP BY
				CUENT.id_cuenta
			) AS nu_acu_puntos,    

			(
			SELECT
				SUM(PTOACU.nu_punto_puntaje) AS nu_acu_canjes
			FROM
				prom_ta_cuentas CUENT
			LEFT JOIN prom_ta_tarjetas AS PTOTAR ON(PTOTAR.id_cuenta=CUENT.id_cuenta)
			LEFT JOIN prom_ta_movimiento_puntos AS PTOACU ON(PTOTAR.id_tarjeta=PTOACU.id_tarjeta)
			WHERE
				PTOACU.nu_punto_tipomov = 2
				AND PTOACU.dt_punto_fecha >= '" . pg_escape_string($fecha_fin) . "'
				AND CUENT.id_cuenta = FIRST(ptc.id_cuenta)
			GROUP BY
				CUENT.id_cuenta
			) AS nu_acu_canjes
		FROM
			prom_ta_cuentas ptc
			LEFT JOIN prom_ta_tarjetas ptt ON (ptc.id_cuenta=ptt.id_cuenta)
			LEFT JOIN prom_ta_movimiento_puntos ptm ON (ptt.id_tarjeta=ptm.id_tarjeta)
			LEFT JOIN inv_ta_almacenes alma ON (ptc.ch_sucursal = alma.ch_almacen)
		WHERE
			ptm.nu_punto_tipomov = 1
			AND ptm.dt_punto_fecha BETWEEN '" . pg_escape_string($fecha_inicio) . " 00:00:00' AND '" . pg_escape_string($fecha_fin) . " 23:59:59'
			" . $cond . "
			" . $cond2 . "
		GROUP BY
			cuenta,
			fecha_creacion_cuenta,
			alma.ch_nombre_breve_almacen,
			ptc.ch_sucursal
		ORDER BY
			puntosacumulados DESC
		";
		error_log("QUERY RANKING DE PUNTOS ACUMULADOS");
		error_log($query);		

		$listado[] = array();
		$listado['datos'] = array();
		$listado['completo'] = array();

		$resultado_1 = $sqlca->query($query);
		$numregs2 = $sqlca->numrows();
		$numregs += $numregs2;
		for ($i = 0; $i < $numregs2; $i++) {
			$row = $sqlca->fetchRow();

			$row['nu_puntaje_actual'] = $row['nu_puntaje_actual'] - ( $row['nu_acu_puntos'] - $row['nu_acu_canjes'] );

			$listado_completo[] = $row;
			$listado['completo'][$i] = $row;
		}

		if($pp && $pagina) {
			$paginador = new paginador($numregs,$pp, $pagina);
		} else {
			$paginador = new paginador($numregs,100,0);
		}
		
		$listado2['partir'] = $paginador->partir();
		$listado2['fin'] = $paginador->fin();
		$listado2['numero_paginas'] = $paginador->numero_paginas();
		$listado2['pagina_previa'] = $paginador->pagina_previa();
		$listado2['pagina_siguiente'] = $paginador->pagina_siguiente();
		$listado2['pp'] = $paginador->pp;
		$listado2['paginas'] = $paginador->paginas();
		$listado2['primera_pagina'] = $paginador->primera_pagina();
		$listado2['ultima_pagina'] = $paginador->ultima_pagina();
	
		$start = (($pagina>0 && $pp>0)?($pagina-1)*$pp:0);
		$limit = $start+(($pp>0)?$pp:100);

		$a = 0;

		for ($i = $start; $i < $limit; $i++) {
			if ($i>=count($listado_completo))
				break;
			$listado['datos'][$a++] = $listado_completo[$i];
		}
		$listado['paginacion'] = $listado2;
		error_log( json_encode( $listado ) );
		return $listado;
	}

	function listarDetalleMovimientos($iAlmacen, $dIni, $dFin, $iCuenta, $iTarjeta) {
		global $sqlca;

		$arrDataRows = array();
		$condAlmacen = (!empty($iAlmacen) ? "AND ptm.ch_sucursal = '" . $iAlmacen . "'" : NULL);

		list($dia_fi,$mes_fi,$anio_fi) = explode('/',pg_escape_string($dIni));
		$fecha_inicio = $anio_fi.'-'.$mes_fi.'-'.$dia_fi;
		list($dia_ff,$mes_ff,$anio_ff) = explode('/',pg_escape_string($dFin));
		$fecha_fin = $anio_ff.'-'.$mes_ff.'-'.$dia_ff;

		$sql = "
SELECT
ptt.nu_tarjeta_numero AS tarjeta,
TO_CHAR(ptm.dt_punto_fecha,'DD/MM/YYYY HH24:MI:SS') AS fe_emision_punto,
(CASE WHEN ptm.nu_punto_tipomov = 1 THEN 'PUNTO' ELSE 'CANJE' END) AS no_punto,
ptm.ch_trans_td AS no_tipo_documento,
ptm.ch_trans_caja AS nu_caja,
ptm.ch_trans_numero AS nu_ticket,
PRO.art_descbreve AS no_producto,
ptm.nu_punto_puntaje AS nu_puntaje,
ptm.ch_usuario AS no_usuario,
COALESCE(alma.ch_nombre_breve_almacen, ptm.ch_sucursal) as ch_sucursal

FROM
prom_ta_cuentas ptc
LEFT JOIN prom_ta_tarjetas ptt ON (ptc.id_cuenta = ptt.id_cuenta)
LEFT JOIN prom_ta_movimiento_puntos ptm ON (ptt.id_tarjeta = ptm.id_tarjeta)
LEFT JOIN int_articulos PRO on (ptm.ch_trans_codigo = PRO.art_codigo)
LEFT JOIN inv_ta_almacenes alma ON (ptm.ch_sucursal = alma.ch_almacen)
WHERE
ptc.nu_cuenta_numero = '" . pg_escape_string($iCuenta) . "'
AND ptm.dt_punto_fecha BETWEEN '" . pg_escape_string($fecha_inicio) . " 00:00:00' AND '" . pg_escape_string($fecha_fin) . " 23:59:59'
" . $condAlmacen . "
ORDER BY 
1 DESC
";

		// COMO ES POR CUENTA ES INDIFERENTE LA SUCURSAL DE LOS MOVIMIENTOS EN TARJETAS" . $condAlmacen . "

		$sqlca->query($sql);
		$arrDataRows = $sqlca->fetchAll();
		return $arrDataRows;
	}
}
