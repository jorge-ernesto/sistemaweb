<?php

class PuntosFidelizaManualModel {

	function ingresarpuntosfidelizamanual($tarjeta,$puntos,$sucursal,$usuario) {
		global $sqlca;

		$query = "INSERT INTO 
				prom_ta_movimiento_puntos 
				(	
					id_tarjeta,
					nu_punto_tipomov,
					dt_punto_fecha,
					nu_punto_puntaje,
					ch_trans_td,
					ch_trans_caja,
					ch_trans_numero,
					ch_trans_codigo,
					nu_trans_cantidad,
					nu_trans_importe,
					ch_usuario,
					ch_sucursal
				) VALUES (
					(SELECT id_tarjeta FROM prom_ta_tarjetas WHERE nu_tarjeta_numero=".pg_escape_string($tarjeta)."),
					1,
					'now()',
					".pg_escape_string($puntos).",
					'X',
					'0',
					0,
					'0',
					0,
					0,
					'".pg_escape_string($usuario)."',
					'".pg_escape_string($sucursal)."'
					);";

		if($sqlca->query($query) < 0) {
			return '0';
		}	

		return '1';
	}

	function actualizarpuntosfidelizamanual($idpunto,$puntaje) {
	 	global $sqlca;
		
		$query ="UPDATE 
				prom_ta_movimiento_puntos 
			SET 
				nu_punto_puntaje='".pg_escape_string($puntaje)."' 
			WHERE 
				id_punto=".pg_escape_string($idpunto);
	
		if($sqlca->query($query) < 0) {
			return 0;
		}	
						
		return '1';
	}

	function eliminarpuntosfidelizamanual($idpunto) { 
		global $sqlca;

		$query = "DELETE FROM prom_ta_movimiento_puntos WHERE id_punto=".pg_escape_string($idpunto);
		
		$result= $sqlca->query($query);
		if ($sqlca->query($query) <= 0) {
			return 0;
		}

		return 1;
	}


  	function tmListado($almacen, $filtro1, $filtro2, $tipo, $pp, $pagina) {		
		global $sqlca;

		$cond_almacen = "";
		if(!empty($almacen) || $almacen != "")
			$cond_almacen = "AND MP.ch_sucursal = '" . $almacen . "'";

		$cond_rango_fecha = "";
		if((!empty($filtro1) && !empty($filtro2)) || ($filtro1 != "" && $filtro2 != ""))
			$cond_rango_fecha = "AND MP.dt_punto_fecha::DATE BETWEEN TO_dATE('".pg_escape_string($filtro1)."', 'DD/MM/YYYY') AND TO_dATE('".pg_escape_string($filtro2)."', 'DD/MM/YYYY')";

		$query = "
		SELECT
			MP.id_punto,
			T.nu_tarjeta_numero,
			C.ch_cuenta_nombres || ' ' || C.ch_cuenta_apellidos As Nombre,
			MP.nu_punto_puntaje,
			to_char(MP.dt_punto_fecha::DATE, 'DD/MM/YYYY') As Fecha,
			to_char(MP.dt_punto_fecha::TIME, 'HH24:MI:SS') As Hora,
			MP.ch_usuario,
			alma.ch_nombre_breve_almacen as ch_sucursal					
		FROM
			prom_ta_movimiento_puntos MP
			LEFT JOIN prom_ta_tarjetas T ON MP.id_tarjeta  = T.id_tarjeta
			LEFT JOIN prom_ta_cuentas C ON T.id_cuenta = C.id_cuenta 
			LEFT JOIN inv_ta_almacenes alma ON (C.ch_sucursal = alma.ch_almacen)
		WHERE
			ch_trans_td = 'X'
			" . $cond_almacen . "
			" . $cond_rango_fecha . "
		";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		if($pp && $pagina) {
			$paginador = new paginador($numrows,$pp, $pagina);
		} else {
			$paginador = new paginador($numrows,20,0);
		}
	
		$listado2['partir'] 			= $paginador->partir();
		$listado2['fin'] 				= $paginador->fin();
		$listado2['numero_paginas']		= $paginador->numero_paginas();
		$listado2['pagina_previa'] 		= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 				= $paginador->pp;
		$listado2['paginas'] 			= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 		= $paginador->ultima_pagina();
	
		if ($pp > 0)
			$query .= " LIMIT " . pg_escape_string($pp) . " ";
		if ($pagina > 0)
			$query .= " OFFSET " . pg_escape_string($paginador->partir());
       
		if ($sqlca->query($query) <= 0) {
			return $sqlca->get_error();
   		}
    
		$listado[] = array();
		while($reg = $sqlca->fetchRow()){
			$listado['datos'][] = $reg;
		}            
		$listado['paginacion'] = $listado2;

		return $listado;
  	}
}
