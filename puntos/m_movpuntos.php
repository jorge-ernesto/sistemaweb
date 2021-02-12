<?php

//print 'entrando a model canje';
class MovpuntosModel {
	
	function obtenerCuentaxTarjeta($campovalor,$tipocampo){
		global $sqlca;
		$registro = array();
		$campo ='';	
		// 1 = busqueda por ID
		if($tipocampo=='1'/* || $tipocampo=='2' || $tipocampo=='3'*/){
			$campo='t.id_tarjeta='.pg_escape_string($campovalor)." ";
		}
		// 2 = busqueda por Numero
		else{
			$campo='t.nu_tarjeta_numero='.pg_escape_string($campovalor)." ";	
		}
		$query = "Select 
						c.id_cuenta,
						c.nu_cuenta_numero,
						c.ch_cuenta_nombres,
						c.ch_cuenta_apellidos,
						c.ch_cuenta_dni,
						c.ch_cuenta_ruc,
						c.ch_cuenta_direccion,
						c.ch_cuenta_telefono1,
						c.ch_cuenta_telefono2,
						c.ch_cuenta_email,
						c.nu_cuenta_puntos,
						c.ch_usuario,
						c.dt_fecha_actualiza 
				from prom_ta_cuentas c inner join prom_ta_tarjetas t on c.id_cuenta = t.id_cuenta where ".$campo;
					
		$sqlca->query($query);
			while($reg = $sqlca->fetchRow()){
				$registro = $reg;			
			}
	return $registro;
		
	}

	function obtenerTarjeta($campovalor,$tipocampo){
		global $sqlca;
		$registro = array();
		$campo ='';	
		// 1 = busqueda por ID
		if($tipocampo=='1'/* || $tipocampo=='2' || $tipocampo=='3'*/){
			$campo='id_tarjeta='.pg_escape_string($campovalor)." ";
		}
		// 2 = busqueda por Numero
		else{
			$campo='nu_tarjeta_numero='.pg_escape_string($campovalor)." ";	
		}
		$query = "Select id_tarjeta,
						 id_cuenta,
						 nu_tarjeta_numero,
					  	 ch_tarjeta_descripcion,
						 ch_tarjeta_placa,
						 to_char(dt_tarjeta_creacion,'dd/mm/yyyy') as dt_tarjeta_creacion,
						 to_char(dt_tarjeta_vencimiento,'dd/mm/yyyy') as dt_tarjeta_vencimiento,
						 nu_tarjeta_puntos,
						 nu_cuenta_numero,
						 ch_tarjeta_titular,
						 ch_usuario,
						 dt_fecha_actualiza 
				from prom_ta_tarjetas where ".$campo;
					
		$sqlca->query($query);
			while($reg = $sqlca->fetchRow()){
				$registro = $reg;			
			}
		return $registro;
	}
	

	function tmListado($numerotarjeta,$fechaini,$fechafin,$pp, $pagina){
		global $sqlca;
		$cond = '';
		if($fechaini=='0' and $fechafin =='0' and $numerotarjeta=='0'){
			$cond=" WHERE t.nu_tarjeta_numero=0";
		}else{
			$cond = " WHERE t.nu_tarjeta_numero =".pg_escape_string($numerotarjeta).
				" AND to_date(to_char(mp.dt_punto_fecha,'dd/mm/yyyy'),'dd/mm/yyyy') BETWEEN to_date('".pg_escape_string($fechaini)."','dd/mm/yyyy') AND to_date('" .pg_escape_string($fechafin)."','dd/mm/yyyy')  ";
		}
		
		$query = "
			SELECT
				mp.id_punto,
				mp.id_tarjeta,
				mp.nu_punto_tipomov,
				to_char(mp.dt_punto_fecha,'dd/mm/yyyy HH24:MI:SS') as dt_punto_fecha,
				mp.nu_punto_puntaje,
				mp.ch_trans_td,
				mp.ch_trans_caja,
				mp.ch_trans_numero,
				CASE
				WHEN mp.nu_punto_tipomov = '2' THEN it.ch_item_descripcion
				ELSE a.art_descbreve
				END AS ch_trans_codigo,
				mp.nu_trans_cantidad,
				mp.nu_trans_importe,
				mp.dt_fecha_actualiza,
				mp.ch_usuario,
				alma.ch_nombre_breve_almacen as ch_sucursal
			FROM 
				prom_ta_movimiento_puntos mp
				inner join prom_ta_tarjetas t on (mp.id_tarjeta = t.id_tarjeta)
				left join int_articulos a on (mp.ch_trans_codigo = a.art_codigo)
				LEFT JOIN inv_ta_almacenes alma ON (mp.ch_sucursal = alma.ch_almacen)
				LEFT JOIN prom_ta_canjes cj ON (cj.id_tarjeta = mp.id_tarjeta AND cj.dt_canje_fecha=mp.dt_punto_fecha AND cj.ch_sucursal = mp.ch_sucursal AND cj.ch_usuario = mp.ch_usuario AND cj.nu_canje_puntaje_canjeado = mp.nu_punto_puntaje AND mp.nu_punto_tipomov = '2')
				LEFT JOIN prom_ta_items_canje it ON (it.id_item = cj.id_item)"
			.$cond.
			"ORDER BY 
				mp.dt_punto_fecha desc
			";
			
			//echo $query;

			$resultado_1 = $sqlca->query($query);
			
			
			$numrows = $sqlca->numrows();
			if($pp && $pagina){
				$paginador = new paginador($numrows,$pp, $pagina);
			}else{
				$paginador = new paginador($numrows,100,0);
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
		
		if ($pp > 0)
			$query .= " LIMIT " . pg_escape_string($pp) . " ";
		
		if ($pagina > 0)
 			$query .= " OFFSET " . pg_escape_string($paginador->partir());
		
		if ($sqlca->query($query)<=0){
			return $sqlca->get_error();
		}
		
		$listado[] = array();
		while($reg = $sqlca->fetchRow()){
			$listado['datos'][] = $reg;
		}
			
		$listado['paginacion'] = $listado2;
			return $listado;
	}

	function tmResumen($numerotarjeta,$fechaini,$fechafin){
		global $sqlca;
		$cond = '';
		if($fechaini=='0' and $fechafin =='0' and $numerotarjeta=='0'){
			$cond=" WHERE t.nu_tarjeta_numero=0";
		}else{
			$cond = " WHERE t.nu_tarjeta_numero =".pg_escape_string($numerotarjeta).
				" AND to_date(to_char(mp.dt_punto_fecha,'dd/mm/yyyy'),'dd/mm/yyyy') BETWEEN to_date('".pg_escape_string($fechaini)."','dd/mm/yyyy') AND to_date('" .pg_escape_string($fechafin)."','dd/mm/yyyy') ";
		}
		
		$query = "SELECT mp.id_tarjeta as id,
				case mp.nu_punto_tipomov when 1 then 'PUNTO' when 2 then 'CANJE' 
							 when 3 then 'VENCIMIENTO' when 4 then 'RETENCION' 
							 Else 'Otros' 
				end as tipo,
				 sum(mp.nu_punto_puntaje) as puntos 
			  FROM 
				prom_ta_movimiento_puntos mp
				inner join prom_ta_tarjetas t on (mp.id_tarjeta = t.id_tarjeta)
				left join int_articulos a on (mp.ch_trans_codigo = a.art_codigo) "
			.$cond.
			"group by mp.id_tarjeta,mp.nu_punto_tipomov ORDER BY 
				mp.id_tarjeta  
			";

		$sqlca->query($query);

		$listado[] = array();
		while($reg = $sqlca->fetchRow()){
			$listado['datos'][] = $reg;
		}
		return $listado;
	}
  
}

