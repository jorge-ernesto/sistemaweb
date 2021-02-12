<?php

class Reporte_NumTransFidelizaModel {
    function obtenerAlmacenes() {
        global $sqlca;

        $sql = "
        SELECT
		    ch_almacen,
		    ch_nombre_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		ORDER BY
		    ch_almacen;
        ";
        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();
        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
            $result[$a[0]] = $a[1];
        }
        return $result;
    }

    function tmListado($almacen, $turno, $fechaini, $fechafin, $pp, $pagina) {
        global $sqlca;

        list($dia_fi, $mes_fi, $anio_fi) = explode('/', pg_escape_string($fechaini));
        $fecha_inicio = $anio_fi . '-' . $mes_fi . '-' . $dia_fi;
        list($dia_ff, $mes_ff, $anio_ff) = explode('/', pg_escape_string($fechafin));
        $fecha_fin = $anio_ff . '-' . $mes_ff . '-' . $dia_ff;

        $numregs = 0;
        $listado_completo = array();


        $cond = '';

        $cond = "WHERE
		p1.dia BETWEEN '" . $anio_fi . "-" . $mes_fi . "-" . $dia_fi . " ' AND ' " . $anio_ff . "-" . $mes_ff . "-" . $dia_ff . "' ";

        //opcionales

	$p1g = "";
	$p2c = "";
	if ($almacen != "") {
		$cond .= " AND p1.es ='" . pg_escape_string($almacen) . "' ";
		$p1g = "p1.es,";
		$p2c = " AND p2.es = p1.es ";
	}
	if ($turno != "")
		$cond .= " AND p1.turno ='" . pg_escape_string($turno) . "' ";

	$query = "
SELECT
	z.fecha AS fecha,
	z.turno AS turno,
	z.ventasoles AS ventasoles,
	z.trans_total AS trans_total,
	z.trans_fide AS trans_fide,
	((z.trans_fide * 100) / z.trans_total) AS porcentaje
FROM (
	SELECT
		p1.dia::date AS fecha,
		p1.turno AS turno,
		sum(p1.importe) AS ventasoles,
		count(p1.*) AS trans_total,
		COALESCE((SELECT sum(y.x) FROM (SELECT DISTINCT ON(p2.caja,p2.trans) 1 AS x FROM pos_trans" . $anio_ff . "" . $mes_ff . " p2 WHERE p2.dia = p1.dia AND p2.turno = p1.turno {$p2c} AND p2.indexa IS NOT NULL AND p2.indexa != '') y),0) AS trans_fide
	FROM
		pos_trans" . $anio_ff . "" . $mes_ff . " p1
	{$cond}
	GROUP BY
		{$p1g}p1.dia,
		p1.turno
	ORDER BY
		p1.dia ASC,
		p1.turno ASC
	) z;";

        $resultado_1 = $sqlca->query($query);
        $numregs2 = $sqlca->numrows();
        $numregs += $numregs2;
        for ($i = 0; $i < $numregs2; $i++) {
            $row = $sqlca->fetchRow();
            $listado_completo[] = $row;
        }

        if ($pp && $pagina) {
            $paginador = new paginador($numregs, $pp, $pagina);
        } else {
            $paginador = new paginador($numregs, 100, 0);
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


        $start = (($pagina > 0 && $pp > 0) ? ($pagina - 1) * $pp : 0);
        $limit = $start + (($pp > 0) ? $pp : 100);

        $listado[] = array();
        $listado['datos'] = array();
        //trigger_error("CANTIDAD: ".count($listado_completo) . " START $start LIMIT $limit");
        $a = 0;
        for ($i = $start; $i < $limit; $i++) {
            if ($i >= count($listado_completo))
                break;
            $listado['datos'][$a++] = $listado_completo[$i];
        }

        $listado['paginacion'] = $listado2;
        return $listado;
    }

    function generarReporte($almacen, $turno, $fechaini, $fechafin) {
        global $sqlca;

        list($dia_fi, $mes_fi, $anio_fi) = explode('/', pg_escape_string($fechaini));
        $fecha_inicio = $anio_fi . '-' . $mes_fi . '-' . $dia_fi;
        list($dia_ff, $mes_ff, $anio_ff) = explode('/', pg_escape_string($fechafin));
        $fecha_fin = $anio_ff . '-' . $mes_ff . '-' . $dia_ff;

        $numregs = 0;
        $listado_completo = array();

        $cond = '';
        $cond = "
            WHERE				
			 p1.dia BETWEEN '" . $anio_fi . "-" . $mes_fi . "-" . $dia_fi . " ' AND ' " . $anio_ff . "-" . $mes_ff . "-" . $dia_ff . "'
        ";

        if ($almacen != '')
            $cond.= " AND p1.es ='" . pg_escape_string($almacen) . "' ";

        if ($turno != '')
            $cond.= " AND p1.turno ='" . pg_escape_string($turno) . "' ";

        $sql = "
        CREATE TEMPORARY TABLE tmpRepNumTransFide AS
        SELECT
        	z.fecha AS fecha,
        	z.turno AS turno,
        	z.ventasoles AS ventasoles,
        	z.trans_total AS trans_total,
        	z.trans_fide AS trans_fide,
        	((z.trans_fide * 100) / z.trans_total) AS porcentaje
        FROM (
        	SELECT
        		p1.dia::date AS fecha,
        		p1.turno AS turno,
        		sum(p1.importe) AS ventasoles,
        		count(p1.*) AS trans_total,
        		COALESCE((SELECT sum(y.x) FROM (SELECT DISTINCT ON(p2.caja,p2.trans) 1 AS x FROM pos_trans" . $anio_ff . "" . $mes_ff . " p2 WHERE p2.dia = p1.dia AND p2.turno = p1.turno {$p2c} AND p2.indexa IS NOT NULL AND p2.indexa != '') y),0) AS trans_fide
        	FROM
        		pos_trans" . $anio_ff . $mes_ff . " p1
        	{$cond}
        	GROUP BY
        		{$p1g}p1.dia,
        		p1.turno
        	ORDER BY
        		p1.dia ASC,
        		p1.turno ASC
        	) z;
        ";

        $sqlca->query($sql);

        $sql = "COPY tmpRepNumTransFide TO '/sistemaweb/maestros/reporteMaestros/NumTransFidelizacion_" . session_id() . ".csv' WITH DELIMITER ',' NULL AS '';";
        $sqlca->query($sql);
    }

}

