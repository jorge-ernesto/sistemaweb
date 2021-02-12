<?php

class PuntosxProductoModel {

	function ingresarPuntosxProducto($idcampania,$idarticulo,$puntossol,$puntosunidad) {
		global $sqlca;

		$query ="INSERT INTO prom_ta_puntos_x_producto
				(id_campana,
				art_codigo,
				puntos_sol,
				puntos_unidad) ".
			" VALUES(".
				pg_escape_string($idcampania).",
				'".pg_escape_string($idarticulo)."',
				".pg_escape_string($puntossol).",
				".pg_escape_string($puntosunidad).")";	
				   
		if($sqlca->query($query) < 0){
			return '0';
		}	

		return '1';
	}

	function actualizarPuntosxProducto($idcampania,$idarticulo,$puntossol,$puntosunidad){
		global $sqlca;
		
		$query ="UPDATE prom_ta_puntos_x_producto 
			SET 
				puntos_sol=".pg_escape_string($puntossol).",
				puntos_unidad=".pg_escape_string($puntosunidad)." 
			WHERE 
				id_campana=".pg_escape_string($idcampania)."
				AND art_codigo= '".pg_escape_string($idarticulo)."' ";
	
		if($sqlca->query($query)<0){
			return 0;
		}	
			
		return '1';
	}

	function eliminarPuntosxProducto($idcampania,$idarticulo) {
		global $sqlca;

		$query = "	DELETE 
				FROM 
					prom_ta_puntos_x_producto 
				WHERE 
					id_campana=".pg_escape_string($idcampania)."
					AND art_codigo= '".pg_escape_string($idarticulo)."' ";
		
		$result= $sqlca->query($query);
		if ($sqlca->query($query)<=0){
			return 0;
		}

		return 1;
	}


  	function tmListado($filtro,$tipo,$pp,$pagina) {
		global $sqlca;
		$cond = '';
		
		if (!empty($filtro)){
			
			if(strtoupper(trim($tipo)) =='DEFAULT'){
			$cond =" ORDER BY
					ptc.ch_campana_descripcion ASC,
					art.art_codigo ASC ";
			}
			else if(strtoupper(trim($tipo)) =='C') {
			
			$cond = "WHERE 
					ptc.ch_campana_descripcion LIKE '".pg_escape_string($filtro)."%' 
				ORDER BY 
					pxp.id_campana ASC";
			
			}
			else if(strtoupper(trim($tipo)) =='A') {
			
			$cond = " WHERE 
					art.art_descripcion LIKE '".pg_escape_string($filtro)."%' 
				ORDER BY 
					pxp.art_codigo ASC";
			
			}
			else{
			$cond = " 
				WHERE 
					ptc.ch_campana_descripcion LIKE '".pg_escape_string($filtro)."%' 
					OR art.art_descripcion LIKE '".pg_escape_string($filtro)."%' 
				ORDER BY 
					ptc.ch_campana_descripcion,
					art.art_descripcion ";
			}
		}
		
		$query = "
			SELECT
				pxp.id_campana AS codigocampania,
				ptc.ch_campana_descripcion AS descripcioncampania,
				art.art_codigo AS codigoarticulo,
				art.art_descripcion AS descripcionarticulo,
				pxp.puntos_sol AS puntossol,
				pxp.puntos_unidad AS puntosunidad
			FROM
				prom_ta_puntos_x_producto pxp
				LEFT JOIN prom_ta_campanas ptc ON (pxp.id_campana=ptc.id_campana)
				LEFT JOIN int_articulos art ON (pxp.art_codigo=art.art_codigo)
			" . $cond;
	

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
}
