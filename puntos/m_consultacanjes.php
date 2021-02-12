<?php

class ConsultaCanjesModel {
	
	function tmListado($almacen,$numerotarjeta,$descitem,$fechaini,$fechafin,$pp, $pagina) {
		global $sqlca;

		$cond = '';	
		$cond = " WHERE 
				to_date(to_char(c.dt_canje_fecha,'DD/MM/YYYY'),'DD/MM/YYYY') 
				BETWEEN "."to_date('".pg_escape_string($fechaini)."','DD/MM/YYYY') AND "."to_date('".pg_escape_string($fechafin)."','DD/MM/YYYY')";

		
		//opcionales
		if($almacen != '') {
			} if($almacen != 'TODOS'){
			$cond .= "AND c.ch_sucursal='".pg_escape_string($almacen)."'";
			}
		if($numerotarjeta!=''){
			$cond.= " AND t.nu_tarjeta_numero ='".pg_escape_string($numerotarjeta)."'";
		}
		if($descitem!=''){
			$cond.= " AND ic.ch_item_descripcion LIKE '".pg_escape_string($descitem)."%'";
		}

		$query = "
				SELECT 
					to_char(c.dt_canje_fecha,'DD/MM/YYYY HH24:MI') as dt_canje_fecha,						
					t.id_cuenta,												
					t.id_tarjeta,													
					t.nu_tarjeta_numero,												
					t.ch_tarjeta_descripcion,											
					c.nu_canje_puntaje_canjeado,											
					ic.ch_item_descripcion,
					alma.ch_nombre_breve_almacen as ch_sucursal,
					c.ch_usuario 
				FROM 
					prom_ta_canjes c 
					INNER JOIN prom_ta_tarjetas t ON (c.id_tarjeta = t.id_tarjeta)
					INNER JOIN prom_ta_items_canje ic ON (c.id_item = ic.id_item) 
					LEFT JOIN inv_ta_almacenes alma ON (c.ch_sucursal = alma.ch_almacen)"
					.$cond.
				"ORDER BY 
					c.dt_canje_fecha DESC ";

	
		$resultado_1 = $sqlca->query($query);
	
		 $numrows = $sqlca->numrows();
		if($pp && $pagina){
			$paginador = new paginador($numrows,$pp, $pagina);
		}else{
			$paginador = new paginador($numrows,100,0);
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
	
		if ($pp > 0)
			$query .= "LIMIT " . pg_escape_string($pp) . " ";
		if ($pagina > 0)
		    	$query .= "OFFSET " . pg_escape_string($paginador->partir());
	       
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
}
