<?php

class ContometrosModel extends Model {

	function Paginacion ($desde, $hasta, $pp, $pagina) {
		global $sqlca;

		$cond = '';    
		$query = "SELECT to_char(fecha, 'DD/MM/YYYY') || ' ' || to_char(fecha, 'HH:MI:SS AM'),
						to_char(dia, 'DD/MM/YYYY'),
						turno, 
						count(manguera)||' '||' Mangueras' AS mangueras,
						fecha 
					FROM
						pos_contometros
					";

		if($desde != '') {
			$query .= " WHERE date(dia) BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";
		}

		$query .= "GROUP BY dia,fecha, turno
					ORDER BY fecha desc ";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();

		if($pp && $pagina) {
			echo "ENTRO 2\n REGPP : $pp \n PAG : $pagina\n";
			$paginador = new paginador($numrows,$pp, $pagina);
		} else {
			echo "ENTRO 2 ELSE\n REGPP : $pp \n PAG : $pagina\n";
			$paginador = new paginador($numrows,16,1);
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

		if($pp > 0) {
			$query .= "LIMIT " . pg_escape_string($pp) . " ";
		}

		if($pagina > 0) {
			$query .= "OFFSET " . pg_escape_string($paginador->partir());
		}

		if($sqlca->query($query)<=0) {
			return $sqlca->get_error();
		}

		$listado[] = array();
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = pg_escape_string($estaciones);
			$propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
			@$result['propiedades'][$propio]['almacenes'][$i][0] = $a[0];
			@$result['propiedades'][$propio]['almacenes'][$i][1] = $a[1];
			@$result['propiedades'][$propio]['almacenes'][$i][2] = $a[2];
			@$result['propiedades'][$propio]['almacenes'][$i][3] = $a[3];
			@$result['propiedades'][$propio]['almacenes'][$i][4] = $a[4];
		}

		$listado['datos'] 	= $result;
		$listado['paginacion'] 	= $listado2;

		return $listado;
	}
}
