<?php
class ParticipaVentaLineaModel extends Model {
	function busqueda($desde,$hasta, $estacion) {
		global $sqlca;
		$sql = "
			SELECT
				sum(d.nu_fac_cantidad) AS total_cantidad,
				sum(d.nu_fac_valortotal) AS total_importe
			FROM
				fac_ta_factura_cabecera c
				RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
			WHERE
				c.ch_fac_tipodocumento='45'
				AND c.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'dd/mm/YYYY')
			";
		if($estacion != 'TODAS') {
			$sql .= "AND c.ch_almacen='" . pg_escape_string($estacion) . "'";
		}

		if($sqlca->query($sql) < 0) return false;

		$row = $sqlca->fetchRow();
		$total_cantidad = $row[0];
		$total_importe = $row[1];

		$sql = "
			SELECT
				art.art_linea AS linea,
				max(tab.tab_descripcion) AS descripcion_linea,
				sum(d.nu_fac_cantidad) AS cantidad,
				sum(d.nu_fac_valortotal) AS importe
			FROM
				fac_ta_factura_cabecera c
				RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
				RIGHT JOIN int_articulos art ON (art.art_codigo=d.art_codigo)
				LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
			WHERE
				c.ch_fac_tipodocumento='45'
				AND c.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'dd/mm/YYYY')
			";
		if($estacion != 'TODAS') {
			$sql .= "AND c.ch_almacen='" . pg_escape_string($estacion) . "'";
		}
		$sql .= "GROUP BY
				art.art_linea
			ORDER BY
				art.art_linea;";

		if($sqlca->query($sql) < 0) return false;
		
		$resultado = Array();
		
		$resultado['totales']['importe'] = 0;
		$resultado['totales']['cantidad'] = 0;
		$resultado['totales']['porcentaje_importe'] = 0;
		$resultado['totales']['porcentaje_cantidad'] = 0;

	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$row = $sqlca->fetchRow();
			$resultado['filas'][$i]['linea'] = $row[0];
			$resultado['filas'][$i]['descripcion_linea'] = $row[1];
			$resultado['filas'][$i]['cantidad'] = $row[2];
			$resultado['filas'][$i]['importe'] = $row[3];
			$porcentaje_cantidad = ($row[2]/$total_cantidad)*100;
			$porcentaje_importe = ($row[3]/$total_importe)*100;
			$resultado['filas'][$i]['porcentaje_cantidad'] = $porcentaje_cantidad;
			$resultado['filas'][$i]['porcentaje_importe'] = $porcentaje_importe;

			$resultado['totales']['importe'] += $row[3];
			$resultado['totales']['porcentaje_importe'] += $porcentaje_importe;
			$resultado['totales']['cantidad'] += $row[2];
			$resultado['totales']['porcentaje_cantidad'] += $porcentaje_cantidad;
		}

		return $resultado;
	}

	function obtenerEstaciones() {
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

		if ($sqlca->query($sql, "_estaciones") < 0) return null;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows("_estaciones"); $i++) {
			$array = $sqlca->fetchRow("_estaciones");
			$resultado[$array[0]] = $array[0] . " - " . $array[1];
		}

		$resultado['TODAS'] = "Todas las estaciones";
		return $resultado;
	}

	function obtenerDetalleLinea($linea,$desde,$hasta, $estacion) {
		global $sqlca;
		$sql = "
			SELECT
				art.art_codigo as codigo,
				art.art_descripcion as descripcion,
				d.nu_fac_cantidad AS cantidad,
				d.nu_fac_valortotal AS importe
			FROM
				fac_ta_factura_cabecera c
				RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
				RIGHT JOIN int_articulos art ON (art.art_codigo=d.art_codigo)
				LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
			WHERE
				c.ch_fac_tipodocumento='45'
				AND c.dt_fac_fecha BETWEEN to_date('" . pg_escape_string($desde) . "', 'dd/mm/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'dd/mm/YYYY')
				AND art.art_linea='" . pg_escape_string($linea) . " '";
		if($estacion != 'TODAS') {
			$sql .= " AND c.ch_almacen='" . pg_escape_string($estacion) . " '
			";
		}
		$sql .= " ORDER BY
				art.art_codigo ASC;";
		echo $sql;
		if($sqlca->query($sql) < 0) return false;
		$resultado = array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$resultado[$i] = $sqlca->fetchRow();
		}
		return $resultado;	
	}
}
