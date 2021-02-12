<?php

class FormAcuModel extends Model {

	function Search($desde, $hasta, $estaciones, $formulario, $modo) {
		global $sqlca;
	
		$tipos  = FormAcuModel::obtenerTablaGeneral('21');
		$lineas = FormAcuMOdel::obtenerTablaGeneral('20');

		list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
		list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

		$sql = "SELECT distinct mov_almacen FROM inv_movialma WHERE true ";

		if ($estaciones != "TODAS") {
		    	$sql .= " AND mov_almacen='" . pg_escape_string($estaciones) . "' ";
		}
		if ($formulario != "TODOS")
		    	$sql .= " AND tran_codigo='" . pg_escape_string($formulario) . "' ";

		$sql .= "  AND mov_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . " 23:59:59' ";

		if ($sqlca->query($sql) < 0) 
			return false;

		$fila = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$fila[$i] = $a[0];
		}

		$sql = "SELECT
			    	m.art_codigo,
			    	trim(a.art_descripcion),
			    	a.art_linea,
			    	trim(a.art_tipo),
			    	m.tran_codigo||' - '||tt.tran_descripcion,
			    	m.mov_almacen,
			    	sum(m.mov_cantidad),
			    	sum(m.mov_costototal)
			FROM
			    	int_articulos a,
			    	inv_tipotransa tt,
			    	inv_movialma m
		       	WHERE
				m.art_codigo=a.art_codigo 
			    	AND tt.tran_codigo=m.tran_codigo ";
			   
		if ($estaciones != "TODAS") {
		    	$sql .= " AND mov_almacen='" . pg_escape_string($estaciones) . "' ";
		}

		if ($formulario != "TODOS")
		    	$sql .= " AND m.tran_codigo='" . pg_escape_string($formulario) . "' ";

		$sql .= "  AND m.mov_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . " 23:59:59'
			GROUP BY
			   	m.tran_codigo,
			   	tt.tran_descripcion,
			   	a.art_tipo,
			   	a.art_linea,
			   	m.art_codigo,
			   	a.art_descripcion,
			   	m.mov_almacen";

		if ($sqlca->query($sql) < 0) 
			return null;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		        $art_codigo 	 = $a[0];
		    	$art_descripcion = $a[1];
		    	$art_linea 	 = $lineas[$a[2]];
		    	$art_tipo 	 = $tipos[$a[3]];
		    	$tran_codigo 	 = $a[4];
		    	$mov_almacen 	 = $a[5];
		    	$mov_cantidad 	 = $a[6];
		    	$mov_costototal  = $a[7];
		    
		    	/* prepara el resultado por todos los campos */
		    	if ($modo == "DETALLADO") {
				$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['articulos'][$art_codigo]['art_codigo'] = $art_codigo;
				$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['articulos'][$art_codigo]['art_descripcion'] = $art_descripcion;
				$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['articulos'][$art_codigo][$mov_almacen.'_cant'] = $mov_cantidad;
				$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['articulos'][$art_codigo][$mov_almacen.'_cost'] = $mov_costototal;
				$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['articulos'][$art_codigo]['total_cant'] += $mov_cantidad;
				$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['articulos'][$art_codigo]['total_cost'] += $mov_costototal;
		    	}

		    	/* Sub-total Linea */
		    	$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['total'][$mov_almacen.'_cant'] += $mov_cantidad;
		    	$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['total'][$mov_almacen.'_cost'] += $mov_costototal;
		    	$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['total']['total_cant'] += $mov_cantidad;
		    	$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['lineas'][$art_linea]['total']['total_cost'] += $mov_costototal;

		    	/* Sub-total Tipo */
		    	$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['total'][$mov_almacen.'_cant'] += $mov_cantidad;
		    	$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['total'][$mov_almacen.'_cost'] += $mov_costototal;
		    	$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['total']['total_cant'] += $mov_cantidad;
		    	$resultado['formularios'][$tran_codigo]['tipos'][$art_tipo]['total']['total_cost'] += $mov_costototal;

		    	/* Sub-total Formulario */
		    	$resultado['formularios'][$tran_codigo]['total'][$mov_almacen.'_cant'] += $mov_cantidad;
		    	$resultado['formularios'][$tran_codigo]['total'][$mov_almacen.'_cost'] += $mov_costototal;
		    	$resultado['formularios'][$tran_codigo]['total']['total_cant'] += $mov_cantidad;
		    	$resultado['formularios'][$tran_codigo]['total']['total_cost'] += $mov_costototal;
		    
		    	/* Total General */
		    	$resultado['total'][$mov_almacen.'_cant'] += $mov_cantidad;
		    	$resultado['total'][$mov_almacen.'_cost'] += $mov_costototal;
		    	$resultado['total']['total_cant'] += $mov_cantidad;
		    	$resultado['total']['total_cost'] += $mov_costototal;
		}
		$resultado['modelo'] = $fila;

		return $resultado;
    	}
    
    	function obtenerDescripcionTG($tabla, $tipo) {
		global $sqlca;
	
		$sql = "SELECT
			    	trim(tab_descripcion)
			FROM
			    	int_tabla_general
			WHERE
				tab_tabla='" . pg_escape_string($tabla) . "'
			    	AND tab_elemento='" . pg_escape_string($tipo) . "' ;";

		if ($sqlca->query($sql) < 0) 
			return null;
	
		$a = $sqlca->fetchRow();

		return $a[0];
    	}
    
    	function obtenerTablaGeneral($tabla) {
		global $sqlca;
	
		$sql = "SELECT
			    	trim(tab_elemento),
			    	trim(tab_descripcion)
			FROM
			    	int_tabla_general
			WHERE
				tab_tabla='" . pg_escape_string($tabla) . "'
			    	AND tab_elemento!='000000'
			ORDER BY
			    	tab_elemento;";

		if ($sqlca->query($sql, "_tg") < 0) 
			return null;
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows("_tg"); $i++) {
		    	$a = $sqlca->fetchRow("_tg");
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}

		return $result;
    	}
}
