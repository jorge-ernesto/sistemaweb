<?php

class man_rubro_Model extends Model {

	function Rubros(){
		global $sqlca;

		$rubro = "
			SELECT
				ch_codigo_rubro,
				ch_descripcion
			FROM
				cpag_ta_rubros
			ORDER BY
				1;
			";

		if($sqlca->query($rubro) < 0)
			return false;

		$resultado = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
			$resultado[$a[0]] = $a[0] . " - " .$a[1];
		}
		
		return $resultado;

	}

    	function Paginacion($pp, $pagina, $rubro) {
		global $sqlca;

		if($rubro == "TODOS"){
			$condicion = "";
		}else if(!empty($rubro)){
			$condicion ="
			WHERE
				ch_codigo_rubro = '$rubro'
			";
		}else{
			$condicion = "";	
		}

        	$query="
			SELECT 
				ch_codigo_rubro,
        	                ch_descripcion,
        	                ch_descripcion_breve,
				ch_tipo_item
			FROM
				cpag_ta_rubros
			$condicion
			ORDER BY 
				 ch_codigo_rubro
		";

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

		if ($sqlca->query($query) < 0)
		    return false;

		$listado[] = array();
		$resultado = Array();


		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $resultado[$i]['ch_codigo_rubro']		= $a[0];
		    $resultado[$i]['ch_descripcion']		= $a[1];
		    $resultado[$i]['ch_descripcion_breve'] 	= $a[2];
		    $resultado[$i]['ch_tipo_item'] 		= $a[3];
		}

		$query = "COMMIT";
		$sqlca->query($query);

		$listado['datos']	= $resultado;
		$listado['paginacion']	= $listado2;

		return $listado;

    	}

	function agregar($cod_rubro, $descripcion_id, $desc_breve, $tipo_item) {
	        global $sqlca;

		$vali = man_rubro_Model::ValidarCuenta($cod_rubro);

		if ($vali == 1) {

			$ins = "
				INSERT INTO
					cpag_ta_rubros( 
							ch_codigo_rubro,
							ch_descripcion,
							ch_descripcion_breve,
							ch_tipo_item 
					)VALUES(
							'$cod_rubro',
							'$descripcion_id',
							'$desc_breve',
						    	'$tipo_item'
						    	       
					);
			";

	            	echo $ins;

			$sqlca->query($ins);

			return 1;

	        } else {
			return 2;
        	}

	}

	function eliminarRegistro($ncuenta) {
	        global $sqlca;

        	$del = "DELETE FROM cpag_ta_rubros WHERE ch_codigo_rubro = '$ncuenta';";

		$sqlca->query($del);

		return 'OK';

	}

	function actualizar($cod_rubro, $descripcion_id, $desc_breve, $tipo_item) {
        	global $sqlca;
                                     
        	$up = "
			UPDATE 
				cpag_ta_rubros
			SET 
				ch_descripcion	 		= '$descripcion_id',
				ch_descripcion_breve 		= '$desc_breve',
                                ch_tipo_item	 		= '$tipo_item'
			WHERE 
				ch_codigo_rubro 	= '$cod_rubro';
			";

	        //echo $up;

        	$result = $sqlca->query($up);

        	return '';
	}

    	function recuperarRegistroArray($ncuenta) {
        	global $sqlca;

        	$registro = array();

        	$query="
			SELECT 
				ch_codigo_rubro,
				ch_descripcion,
				ch_descripcion_breve,
				ch_tipo_item  
			FROM
				cpag_ta_rubros
			WHERE
				ch_codigo_rubro = '$ncuenta'
			LIMIT 1;
		";

		$sqlca->query($query);

		while ($reg = $sqlca->fetchRow()) {
			$registro = $reg;
        	}

		return $registro;

	}

	function ValidarCuenta($ncuenta) {
        	global $sqlca;

        	$vali="
			SELECT 
	                        count(*)  
                    	FROM
				cpag_ta_rubros
			WHERE
				ch_codigo_rubro = '$ncuenta'
			LIMIT 1;
		";

		//echo $vali;

		if ($sqlca->query($vali) < 0)
            		return false;

		$a = $sqlca->fetchRow();

		if ($a[0] >= 1)
			return 0;
		else
			return 1;

	}

}
