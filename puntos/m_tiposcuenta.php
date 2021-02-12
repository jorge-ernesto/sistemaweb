<?php

class TiposCuentaModel {

	function ingresartiposCuenta($idtipocuenta,$descripcion,$sucursal,$usuario) {
		global $sqlca;

		$query ="INSERT INTO 
				prom_ta_tipo_cuentas
				(
					id_tipo_cuenta,
					ch_tipo_descripcion,
					ch_usuario,
					ch_sucursal
				) VALUES (
					(SELECT max(id_tipo_cuenta)+1 FROM prom_ta_tipo_cuentas),
					'".pg_escape_string($descripcion)."',
					'".pg_escape_string($usuario)."',
					'".pg_escape_string($sucursal)."'
				)";	

		if($sqlca->query($query) < 0) {
			return '0';
		}	

		return '1';
	}

	function actualizartiposCuenta($idtipocuenta,$descripcion) {
		global $sqlca;
		
		$query ="	UPDATE 
					prom_ta_tipo_cuentas 
				SET 
					ch_tipo_descripcion='".pg_escape_string($descripcion)."' 
				WHERE 
					id_tipo_cuenta=".pg_escape_string($idtipocuenta);
	
		if($sqlca->query($query)<0){
			return 0;
		}	
			
		return '1';
	}

	function eliminartiposcuenta($idtipocuenta) {
		global $sqlca;

		$query = "	DELETE FROM prom_ta_tipo_cuentas WHERE id_tipo_cuenta=".pg_escape_string($idtipocuenta);
		
		$result= $sqlca->query($query);
		if ($sqlca->query($query) <= 0) {
			return 0;
		}

		return 1;
	}


	function tmListado($filtro,$tipo) {		
		global $sqlca;

		$cond = '';		
		if (!empty($filtro)) {
			
			if(strtoupper(trim($tipo)) =='default') {
				$cond = " ORDER BY id_tipo_cuenta ASC ";
			} else if(strtoupper(trim($tipo)) =='D') {
				$cond = "WHERE ch_tipo_descripcion LIKE '".pg_escape_string($filtro)."%' ORDER BY id_tipo_cuenta ASC";			
			} else {
				$cond = " ORDER BY id_tipo_cuenta ASC ";
			}
		}
		
		$query = " SELECT id_tipo_cuenta, ch_tipo_descripcion FROM prom_ta_tipo_cuentas " . $cond;
		$resultado_1 = $sqlca->query($query);
	
		$numrows = $sqlca->numrows();
		$paginador = new paginador($numrows,100,0); 
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();
       
		if ($sqlca->query($query) <= 0) {
			return $sqlca->get_error();
		}

		$listado[] = array();
		while($reg = $sqlca->fetchRow()) {
			$listado['datos'][] = $reg;
		}        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}
}
