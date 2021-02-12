<?php

class Reporte_NumTransFidelizaModel {

	function tmListado($almacen,$turno,$fechaini,$fechafin,$pp, $pagina) {
		global $sqlca;

		list($dia_fi,$mes_fi,$anio_fi) = explode('/',pg_escape_string($fechaini));
		$fecha_inicio = $anio_fi.'-'.$mes_fi.'-'.$dia_fi;
		list($dia_ff,$mes_ff,$anio_ff) = explode('/',pg_escape_string($fechafin));
		$fecha_fin = $anio_ff.'-'.$mes_ff.'-'.$dia_ff;

		$numregs = 0;
		$listado_completo = array();

		$cond = '';
		$cond = " WHERE p1.dia BETWEEN '" . $anio_fi . "-" . $mes_fi . "-" . $dia_fi . " ' AND ' " . $anio_ff . "-" . $mes_ff . "-" . $dia_ff . "' ";

		if($almacen != '') {
			$cond.= " AND p1.es ='".pg_escape_string($almacen)."' ";
		}

		if($turno != '') {
			$cond.= " AND p1.turno ='".pg_escape_string($turno)."' ";
		}

		$query = "SELECT
				fecha,
				turno,
				ventasoles,
				trans_total,
				trans_fide,
				porcentaje
			FROM (
				(
				SELECT
					to_char(p1.dia,'DD/MM/YYYY') AS fecha,
					p1.turno AS turno,
					SUM(p1.importe) AS ventasoles,
					COUNT(p1.*) AS trans_total,
					(SELECT COUNT(p2.*) FROM pos_trans".$anio_ff."".$mes_ff." p2 WHERE p2.dia=p1.dia AND p2.turno=p1.turno AND  p2.indexa !='') AS trans_fide,
					((SELECT COUNT(p2.*) FROM pos_trans".$anio_ff."".$mes_ff." p2 WHERE p2.dia=p1.dia AND p2.turno=p1.turno AND p2.indexa !=''))*100/COUNT(p1.*) AS porcentaje,
					0::integer AS tf
				FROM
					pos_trans".$anio_ff."".$mes_ff." p1"
					.$cond.
				"GROUP BY
					p1.dia,
					p1.turno
				ORDER BY
					p1.dia ASC,
					p1.turno ASC
				) 
				UNION 
				(
				SELECT
					to_char(p1.dia,'DD/MM/YYYY') AS fecha,
					'A'::character AS turno,
					SUM(p1.importe) AS ventasoles,
					COUNT(p1.*) AS trans_total,
					(SELECT COUNT(p2.*) FROM pos_trans".$anio_ff."".$mes_ff." p2 WHERE p2.dia=p1.dia AND  p2.indexa !='') AS trans_fide,
					((SELECT COUNT(p2.*) FROM pos_trans".$anio_ff."".$mes_ff." p2 WHERE p2.dia=p1.dia AND p2.indexa !=''))*100/COUNT(p1.*) AS porcentaje,
					1::integer AS tf
				FROM
					pos_trans".$anio_ff."".$mes_ff." p1"
					.$cond.
				"GROUP BY
					p1.dia
				ORDER BY
					p1.dia ASC
				)
				) q1
			ORDER BY
				fecha ASC,
				tf ASC,
				turno ASC
			";

		$resultado_1 = $sqlca->query($query);
		$numregs2 = $sqlca->numrows();
		$numregs += $numregs2;

		for ($i = 0; $i < $numregs2; $i++) {
			$row = $sqlca->fetchRow();
			$listado_completo[] = $row;
		}

		if($pp && $pagina) {
			$paginador = new paginador($numregs,$pp, $pagina);
		} else {
			$paginador = new paginador($numregs,100,0);
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
	
		$start = (($pagina>0 && $pp>0)?($pagina-1)*$pp:0);
		$limit = $start+(($pp>0)?$pp:100);

		$listado[] = array();
		$listado['datos'] = array();
		$a = 0;

		for ($i = $start; $i < $limit; $i++) {
			if ($i >= count($listado_completo))
				break;
			$listado['datos'][$a++] = $listado_completo[$i];
		}
	
		$listado['paginacion'] = $listado2;
		return $listado;
	}
}
