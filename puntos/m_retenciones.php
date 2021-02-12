<?php
class RetencionesModel {
	function tmListado($fechaini,$fechafin,$pp, $pagina){
		global $sqlca;

		list($dia_fi,$mes_fi,$anio_fi) = explode('/',pg_escape_string($fechaini));
		$fecha_inicio = $anio_fi.'-'.$mes_fi.'-'.$dia_fi;
		list($dia_ff,$mes_ff,$anio_ff) = explode('/',pg_escape_string($fechafin));
		$fecha_fin = $anio_ff.'-'.$mes_ff.'-'.$dia_ff;

		$query = "
			SELECT 
				t.nu_tarjeta_numero,
				t.ch_tarjeta_descripcion,
				t.ch_tarjeta_placa,
				to_char(mp.dt_punto_fecha,'DD/MM/YYYY HH24:MM:SS') as dt_punto_fecha,
				mp.nu_punto_tipomov,
				mp.ch_trans_td,
				mp.ch_trans_caja,
				mp.ch_trans_numero,
				mp.ch_trans_codigo,
				a.art_descbreve,
				mp.nu_trans_importe,
				mp.nu_punto_puntaje,
				mp.id_punto
			FROM 
				prom_ta_movimiento_puntos mp 
				INNER JOIN prom_ta_tarjetas t ON (mp.id_tarjeta = t.id_tarjeta)
				INNER JOIN int_articulos a ON (mp.ch_trans_codigo = a.art_codigo)
			WHERE
				mp.nu_punto_tipomov = 4 AND
				mp.dt_punto_fecha BETWEEN '" . $anio_fi . "-" . $mes_fi . "-" . $dia_fi . " 00:00:00.0000' AND ' " . $anio_ff . "-" . $mes_ff . "-" . $dia_ff . " 23:59:59.9999'
			ORDER BY 
				mp.id_tarjeta ASC, 
				mp.dt_punto_fecha ASC 
		";	

		$resultado_1 = $sqlca->query($query);
		$numregs = $sqlca->numrows();
		$listado_completo = [];
		for ($i = 0; $i < $numregs; $i++) {
			$row = $sqlca->fetchRow();
			$listado_completo[] = $row;
		}

		if($pp && $pagina){
			$paginador = new paginador($numregs,$pp, $pagina);
		}else{
			$paginador = new paginador($numregs,100,0);
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

	function liberaRetencion($id) {
		global $sqlca;
		settype($id,"integer");
		$sql = "UPDATE prom_ta_movimiento_puntos SET nu_punto_tipomov = 1 WHERE id_punto = $id;";
		$res = $sqlca->query($sql);
		if ($res<0)
			return false;
		return true;
	}

}
