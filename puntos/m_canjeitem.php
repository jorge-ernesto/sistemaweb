<?php
class CanjeitemModel {
	function ingresarItem($campana,$codarticulo,$descripcion,$fechaven,$puntos,$observacion,$usuario,$sucursal) {
		global $sqlca;
		$queryInicial = "SELECT art_codigo from int_articulos where art_codigo = '".$codarticulo."' ";

		if($sqlca->query($queryInicial) <= 0) {
			return '0';
		} else {
			/*
			$id_ = "SELECT max(id_item)+1 FROM prom_ta_items_canje;";
			$sqlca->query($id_);
			$id = $sqlca->fetchRow();
			$id = $id[0];
			*/
			$query = "INSERT INTO 
					prom_ta_items_canje (
						id_item,
						art_codigo,
						ch_item_descripcion,
						dt_item_fecha_vencimiento,
						nu_item_puntos,
						ch_item_observacion,
						ch_usuario,
						ch_sucursal,
						id_campana
					) VALUES (
						nextval(('prom_seq_iditemcanje'::text)),
						'".pg_escape_string($codarticulo)."','".
						pg_escape_string($descripcion)."',".
				  		($fechaven==""?"null":"to_date('".pg_escape_string($fechaven)."','dd/mm/yyyy')").",".
					  	pg_escape_string($puntos).",'".
					  	pg_escape_string($observacion)."','".
					  	pg_escape_string($usuario)."','".
					  	pg_escape_string($sucursal)."',".
					  	pg_escape_string($campana).")";
			
			$result = $sqlca->query($query);
			return $query;
		}
	}

	function actualizarItem($campana,$iditem,$codarticulo,$descripcion,$fechaven,$puntos,$observacion,$usuario,$sucursal) {
		global $sqlca;
		$queryInicial = "SELECT art_codigo FROM int_articulos WHERE art_codigo = '".$codarticulo."' ";

		if($sqlca->query($queryInicial) <= 0) {
			return '0';
		} else {
		$query ="UPDATE prom_ta_items_canje SET 
						   art_codigo='".pg_escape_string($codarticulo)."',".
						  "ch_item_descripcion='".pg_escape_string($descripcion)."',".
						  "dt_item_fecha_vencimiento=".
						  ($fechaven==""?"null":"to_date('".pg_escape_string($fechaven)."','dd/mm/yyyy')").",".
						  "nu_item_puntos=".pg_escape_string($puntos).",".
						  "ch_item_observacion='".pg_escape_string($observacion)."',".  
						  "ch_usuario='".pg_escape_string($usuario)."',".
						  "dt_fecha_actualiza=now(),".
						  "ch_sucursal='".pg_escape_string($sucursal)."',".
						  "id_campana=".pg_escape_string($campana)." ".
			" WHERE id_item= ".pg_escape_string($iditem);
	
			$result = $sqlca->query($query);
			return '1';
		}
	}

	function obtenerCuenta($campovalor,$tipocampo) {
		global $sqlca;
		$registro = array();
		$campo ='';	
		// 1 = busqueda por ID
		if($tipocampo=='1') {
			$campo='id_cuenta='.pg_escape_string($campovalor)." ";
		}
		// 2 = busqueda por Numero
		else {
			$campo='nu_cuenta_numero='.pg_escape_string($campovalor)." ";	
		}

		$query = "SELECT id_cuenta,nu_cuenta_numero,ch_cuenta_nombres,ch_cuenta_apellidos,ch_cuenta_dni,ch_cuenta_ruc,ch_cuenta_direccion,
				ch_cuenta_telefono1,ch_cuenta_telefono2,ch_cuenta_email ,nu_cuenta_puntos,ch_usuario FROM prom_ta_cuentas WHERE ".$campo;
		$sqlca->query($query);

		while($reg = $sqlca->fetchRow()) {
			$registro = $reg;			
		}
		return $registro;
	}

	function eliminarItem($iditem) {
		global $sqlca;
		$query = "DELETE FROM prom_ta_items_canje WHERE id_item=".pg_escape_string($iditem)." ";
		$result= $sqlca->query($query);
		return '1';
	}

	function listarItems($filtro) {
		global $sqlca;
		$cond = " ";
		if(!empty($filtro) || $filtro!=" ") {
		$cond = " WHERE ch_item_descripcion like '".pg_escape_string(trim($filtro))."%'";
		}
		$query="SELECT id_item,art_codigo,ch_item_descripcion FROM prom_ta_items_canje ".$cond." ORDER BY ch_item_descripcion";
		$listado = array();
		$resultado= $sqlca->query($query);
		while($reg = $sqlca->fetchRow()){
			$listado['datositems'][] = $reg;
		}
		return $listado;
	}	
	function listarArticulos($filtro){
		global $sqlca;

		$cond = " ";
		if(!empty($filtro) || $filtro!=" "){
		$cond = " WHERE art_descripcion LIKE '".pg_escape_string(trim($filtro))."%'";
		}
		$query="SELECT art_codigo, art_descripcion FROM int_articulos ".$cond;
		$listado = array();
		$resultado= $sqlca->query($query);
		while($reg = $sqlca->fetchRow()){
			$listado['datosarticulos'][] = $reg;
		}
		return $listado;
	}
	
	function obtenerArticulo($campovalor,$tipocampo){
		global $sqlca;
		$registro = array();
		$campo ='';	
		// 1 = busqueda por CODIGO
		if($tipocampo=='1'){
			$campo="WHERE art_codigo='".pg_escape_string(trim($campovalor))."' ";
		}
		// 2 = busqueda por DESCRIPCION
		else{
			$campo='WHERE art_descripcion like '.pg_escape_string(trim($campovalor))."%'";	
		}

		$query = "SELECT art_codigo, art_descripcion FROM int_articulos ".$campo;
		$sqlca->query($query);

		while($reg = $sqlca->fetchRow()) {
			$registro = $reg;			
		}

		return $registro;
	}

  //Otras funciones para consultar la DB

  function tmListado($filtro,$tipo,$pp, $pagina){
    global $sqlca;
    $cond = '';

    if (!empty($filtro)){

			if(strtoupper(trim($tipo)) =='DEFAULT'){
			$cond ="  ";
			}
			else if(strtoupper(trim($tipo)) =='C') {
			
			$cond = "WHERE 
					c.ch_campana_descripcion LIKE '".pg_escape_string($filtro)."%' ";
			
			}
			else if(strtoupper(trim($tipo)) =='A') {
			
			$cond = " WHERE 
					a.art_codigo LIKE '%".pg_escape_string($filtro)."%' ";
			
			}
			else{
			$cond = " 
				WHERE 
					i.ch_item_descripcion like '%".pg_escape_string($filtro)."%' ";
			}

    }

    $query = "Select  		
					a.art_codigo,
					a.art_descripcion,
					i.id_item,
					i.ch_item_descripcion,
					to_char(i.dt_item_fecha_creacion,'dd/mm/yyyy') as dt_item_fecha_creacion,
					to_char(i.dt_item_fecha_vencimiento,'dd/mm/yyyy') as dt_item_fecha_vencimiento,
					i.nu_item_puntos, 
					i.ch_item_observacion,
					i.id_campana,
					c.ch_campana_descripcion 
			from prom_ta_items_canje i inner join int_articulos a on i.art_codigo = a.art_codigo inner join prom_ta_campanas c on i.id_campana=c.id_campana ".$cond.
	      " order by a.art_codigo,i.ch_item_descripcion,a.art_descripcion ";

	
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

