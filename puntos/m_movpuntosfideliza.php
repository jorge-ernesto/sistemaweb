<?php

class MovPuntosFidelizaModel {

	function tmListado($almacen,$numeroveces,$fechaini,$fechafin,$ruc,$pp, $pagina) {
		global $sqlca;

		list($dia_fi,$mes_fi,$anio_fi) = explode('/',pg_escape_string($fechaini));
		$fecha_inicio = $anio_fi.'-'.$mes_fi.'-'.$dia_fi;
		list($dia_ff,$mes_ff,$anio_ff) = explode('/',pg_escape_string($fechafin));
		$fecha_fin = $anio_ff.'-'.$mes_ff.'-'.$dia_ff;

		$query ="
		SELECT 
			DATE_TRUNC('day', dt_punto_fecha) 
		FROM 
			prom_ta_movimiento_puntos
		WHERE 
			dt_punto_fecha BETWEEN '" . $anio_fi . "-" . $mes_fi . "-" . $dia_fi . " 00:00:00.0000' AND ' " . $anio_ff . "-" . $mes_ff . "-" . $dia_ff . " 23:59:59.9999' 
		GROUP BY 
			1";

		$sqlca->query($query);

		$dias = Array();
		$i = 0;
		while($dia = $sqlca->fetchRow()) {
			$dias[$i++] = substr($dia[0],0,10);
		}

		$numregs = 0;
		$listado_completo = array();

		foreach ($dias as $dia) {
			$cond = '';
			$nveces = '';
			if($numeroveces!='') {
				$nveces = pg_escape_string($numeroveces);
			} else {
				$nveces = 0;
			}
			settype($nveces,"integer");

			$cond = "
				WHERE
					mp.nu_punto_tipomov IN (1,4) AND
					mp.dt_punto_fecha BETWEEN '" . $dia . " 00:00:00.0000' AND '" . $dia . " 23:59:59.9999'";

			if ($nveces > 0)
				$cond .= " AND
					(
						SELECT
							count(*)
						FROM 
							prom_ta_movimiento_puntos mp2
						WHERE 	
							mp2.id_tarjeta = mp.id_tarjeta AND
							mp2.nu_punto_tipomov = 1 AND
							mp2.dt_punto_fecha BETWEEN '" . $dia . " 00:00:00.0000' AND '" . $dia . " 23:59:59.9999'
						GROUP BY
							id_tarjeta,
							date_trunc('day',dt_punto_fecha)
				
					) >= " .$nveces;

			if ($ruc!="")
				$cond .= " AND c.ch_cuenta_ruc = '" . pg_escape_string($ruc) . "' ";

			if($almacen != '')
				$cond.= " AND mp.ch_sucursal='".pg_escape_string($almacen)."' ";
			
			$query = "
			SELECT 
				t.nu_tarjeta_numero,
				c.ch_cuenta_nombres ||' '||ch_cuenta_apellidos as ch_tarjeta_descripcion,
				t.ch_tarjeta_placa,
				to_char(mp.dt_punto_fecha,'DD/MM/YYYY HH24:MI:SS') as dt_punto_fecha,
				mp.nu_punto_tipomov,
				mp.ch_trans_td,
				mp.ch_trans_caja,
				mp.ch_trans_numero,
				mp.ch_trans_codigo,
				a.art_descbreve,
				CAST(mp.nu_trans_cantidad AS numeric(12,2)),
				(CASE
					WHEN mp.nu_trans_cantidad <= 0 THEN 0.00
				ELSE
					CAST(mp.nu_trans_importe / mp.nu_trans_cantidad AS numeric(12,2)) 
				END) AS nu_trans_preciounit,
				CAST(mp.nu_trans_importe AS numeric(12,2)),
				mp.nu_punto_puntaje,
				mp.ch_sucursal
			FROM 
				prom_ta_movimiento_puntos mp 
				INNER JOIN prom_ta_tarjetas t ON (mp.id_tarjeta = t.id_tarjeta)
				JOIN prom_ta_cuentas c ON (t.id_cuenta = c.id_cuenta)
				INNER JOIN int_articulos a ON (mp.ch_trans_codigo = a.art_codigo)
			".$cond."
			ORDER BY 
				mp.id_tarjeta ASC, 
				mp.dt_punto_fecha ASC 
			";

			$resultado_1 = $sqlca->query($query);
			$numregs2 = $sqlca->numrows();
			$numregs += $numregs2;
			for ($i = 0; $i < $numregs2; $i++) {
				$row = $sqlca->fetchRow();
				$listado_completo[] = $row;
			}
		}

		if($pp && $pagina) {
			$paginador = new paginador($numregs,$pp, $pagina);
		} else {
			$paginador = new paginador($numregs,100,0);
		}
		
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();
	

		$start = (($pagina>0 && $pp>0)?($pagina-1)*$pp:0);
		$limit = $start+(($pp>0)?$pp:100);

		$listado[] = array();
		$listado['datos'] = array();

		$a = 0;
		for ($i = $start; $i < $limit; $i++) {
			if ($i>=count($listado_completo))
				break;
			$listado['datos'][$a++] = $listado_completo[$i];
		}	
		$listado['paginacion'] = $listado2;

		return $listado;
	}
}
