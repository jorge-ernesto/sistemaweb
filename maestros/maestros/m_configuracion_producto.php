<?php

class ConfiguracionProductoModel extends Model {
	
	function Paginacion($pp, $pagina, $nombre_combu, $nombre_combu2){

		global $sqlca;

		$query = "select 
				ch_codigocombustible,
				ch_nombrecombustible,
				nu_preciocombustible,
				ch_codigopec,
				ch_codigocombex,
				ch_nombrebreve
			 from
				comb_ta_combustibles
 			 order by
				ch_codigocombustible";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		$paginador = new paginador($numrows,$pp, $pagina);
	
		$listado2['partir'] 		= $paginador->partir();
		$listado2['fin'] 		= $paginador->fin();
		$listado2['numero_paginas'] 	= $paginador->numero_paginas();
		$listado2['pagina_previa'] 	= $paginador->pagina_previa();
		$listado2['pagina_siguiente'] 	= $paginador->pagina_siguiente();
		$listado2['pp'] 		= $paginador->pp;
		$listado2['paginas'] 		= $paginador->paginas();
		$listado2['primera_pagina'] 	= $paginador->primera_pagina();
		$listado2['ultima_pagina'] 	= $paginador->ultima_pagina();

		$query .= " LIMIT " . pg_escape_string($pp) . " ";
		$query .= " OFFSET " . pg_escape_string($paginador->partir());

		//echo $query;

		if ($sqlca->query($query) < 0)
			return false;
	    
    		$listado[] = array();
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['ch_codigocombustible']		= $a[0];
			$resultado[$i]['ch_nombrecombustible']		= $a[1];
			$resultado[$i]['nu_preciocombustible'] 		= $a[2];
			$resultado[$i]['ch_codigopec']		 	= $a[3];
			$resultado[$i]['ch_codigocombex'] 		= $a[4];
			$resultado[$i]['ch_nombrebreve'] 		= $a[5];
			
		}
		
		$query = "COMMIT";
		$sqlca->query($query);

		$listado['datos']      = $resultado;        
		$listado['paginacion'] = $listado2;

		return $listado;
  	}

	function agregar($codigo_combu,$nombre_combu,$nu_precio_combu,$codigopec,$codigo_combex,$nombre_breve) {
		global $sqlca;
		
		$validar = ConfiguracionProductoModel::ValidaProductos($codigo);

		if ($validar == 1){
	
		$query2 = "INSERT INTO comb_ta_combustibles
							(ch_codigocombustible,
						         ch_nombrecombustible,
						         ch_nombrebreve,
						         nu_preciocombustible,
						         ch_codigopec,
						         ch_codigocombex)
		       VALUES
							('$codigo_combu',
						         '$nombre_combu',
					    	         '$nu_precio_combu',
						         '$codigopec',
						         '$codigo_combex',
						         '$nombre_breve');";

		echo $query2;

			if ($sqlca->query($query2) < 0)
					return 0;
				return 1;
			}else{
				return 2;
			}

	}
	
	function eliminarRegistro($codigo_combu){

		global $sqlca;

		$query = "DELETE FROM comb_ta_combustibles WHERE ch_codigocombustible = '$codigo_combu';";
		//echo $sql;
		$sqlca->query($query);
		return 'OK';
	}

	function actualizar($codigo,$nombre_combu,$nu_precio_combu,$codigopec,$codigo_combex,$nombre_breve){
		global $sqlca;

			$query = "UPDATE 
					comb_ta_combustibles
				  SET
					ch_nombrecombustible  	= '$nombre_combu', 
					nu_preciocombustible 	= '$nu_precio_combu',
					ch_codigopec   		= '$codigopec',
					ch_codigocombex 	= '$codigo_combex',
					ch_nombrebreve   	= '$nombre_breve'
				  WHERE 
					ch_codigocombustible = '$codigo';";
			
			//echo $query;

			$result = $sqlca->query($query);
			return '';
 	}
	
	function recuperarRegistroArray($codigo){
	  	global $sqlca;

		    $registro = array();
		    $query = "select 
					ch_codigocombustible,
					ch_nombrecombustible,
					nu_preciocombustible,
					ch_codigopec,
					ch_codigocombex,
					ch_nombrebreve
				from
					comb_ta_combustibles
				where
					ch_codigocombustible = '$codigo';";
		
		    //echo $query;
			 
		    $sqlca->query($query);

		    while( $reg = $sqlca->fetchRow()){
				$registro = $reg;
			}
		    
		    return $registro;
	  }

	function ValidaProductos($codigo){
		global $sqlca;

		$codigo = $_REQUEST['ch_codigocombustible'];

		$query = "select count(*) from comb_ta_combustibles where ch_codigocombustible = '$codigo';";

		echo $query;

		if ($sqlca->query($query) < 0) 
			return false;
		$a = $sqlca->fetchRow();
		if($a[0]>=1){
			return 0;
		}else{
			return 1;
		}

	}

}
