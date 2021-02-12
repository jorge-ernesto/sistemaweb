<?php

class KardexModel extends Model {

	function search($desde, $hasta, $art_desde, $art_hasta, $estacion) {
		global $sqlca;
	
		list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
		list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

		if ($art_hasta == '') 
			$art_hasta='ZZZZZZZZZZZZZ';

		$saldos = KardexModel::saldoInicial($desde, $art_desde, $art_hasta, $estacion);
		$formularios = FormProcesModel::obtenerTiposFormularios();

		$resultado = Array();
		$anteriores = Array();

		foreach($saldos['almacenes'] as $cod_almacen => $almacen) {
		    	foreach($almacen['codigos'] as $codigo => $articulo) {
				$resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['cant_anterior'] = $articulo['stk_stock'];
				$resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['unit_anterior'] = $articulo['stk_costounitario'];
				$resultado['almacenes'][$cod_almacen]['articulos'][$codigo]['saldoinicial']['costo_total'] = $articulo['stk_costototal'];

				$anteriores[$cod_almacen][$codigo]['cant_anterior'] = $articulo['stk_stock'];
				$anteriores[$cod_almacen][$codigo]['unit_anterior'] = $articulo['stk_costounitario'];
		    	}
		}

		$sql = "SELECT
				mov_fecha,
			    	trim(tran_codigo),
			    	mov_numero,
			    	mov_almaorigen,
			    	mov_almadestino,
			    	mov_entidad,
			    	mov_docurefe,
			    	mov_cantidad,
			    	mov_costounitario,
			    	mov_costototal,
			    	mov_costopromedio,
			    	art_codigo,
			    	mov_naturaleza,
			    	mov_almacen
			FROM
			    	inv_movialma
			WHERE
				mov_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . " 23:59:59'
			    	AND art_codigo>='" . pg_escape_string($art_desde) . "'
			    	AND art_codigo<='" . pg_escape_string($art_hasta) . "'
			    ";
		if ($estacion != "TODAS")
		    	$sql .= "AND mov_almacen='" . pg_escape_string($estacion) . "' ";
	
		$sql .= "ORDER BY mov_almacen,art_codigo,date_trunc('day',mov_fecha),mov_naturaleza;";
		
		//order  by mv_ano, mv_mes, mv_dia, mov_naturaleza

		echo "\n\nMovimientos: ".$sql;

		if ($sqlca->query($sql) < 0) 
			return null;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    
		    	$mov_fecha 		= substr($a[0], 0, 19);
		    	$tran_codigo 		= $a[1];
		    	$mov_numero 		= $a[2];
		    	$mov_almaorigen 	= $a[3];
		    	$mov_almadestino	= $a[4];
		    	$mov_entidad 		= $a[5];
		    	$mov_docurefe 		= $a[6];
		    	$mov_cantidad 		= $a[7];
		    	$mov_costounitario 	= $a[8];
		    	$mov_costototal 	= $a[9];
		    	$mov_costopromedio 	= $a[10];
		    	$art_codigo 		= $a[11];
		    	$mov_naturaleza 	= $a[12];
		    	$mov_almacen 		= $a[13];
		    
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_fecha'] = $mov_fecha;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['tran_codigo'] = $formularios[$tran_codigo];
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_numero'] = $mov_numero;
		    
		    	if ($mov_naturaleza < 3) {
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_almacen'] = "DE " . FormProcesModel::obtenerDescripcionAlmacen($mov_almaorigen);
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_entidad'] = FormProcesModel::obtenerProveedor($mov_entidad);
		    	} else {
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_almacen'] = "A " . FormProcesModel::obtenerDescripcionAlmacen($mov_almadestino);	
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_entidad'] = FormProcesModel::obtenerCliente($mov_entidad);
		    	}
	
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_docurefe'] = $mov_docurefe;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_anterior'] = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_ant'] = $anteriores[$mov_almacen][$art_codigo]['unit_anterior'];

		    	if ($mov_naturaleza < 3) {
				$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] += $mov_cantidad;
				$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_entrada'] = $mov_cantidad;
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_entrada'] = $mov_costototal;
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_entrada'] += $mov_cantidad;
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_entrada'] += $mov_costototal;
		    	} else {
				$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] -= $mov_cantidad;
				$cant_actual = $anteriores[$mov_almacen][$art_codigo]['cant_anterior'];
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_salida'] = $mov_cantidad;
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cost_salida'] = $mov_costototal;
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cant_salida'] += $mov_cantidad;
				$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['cost_salida'] += $mov_costototal;
		    	}
		    
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_costounitario'] = $mov_costounitario;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_cant_actual'] = $cant_actual;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_val_unit_act'] = $mov_costopromedio;
		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['movimientos'][$i]['mov_total_act'] = $cant_actual*$mov_costopromedio;

		    	$resultado['almacenes'][$mov_almacen]['articulos'][$art_codigo]['totales']['valor_total'] += ($cant_actual*$mov_costopromedio);
		    	$anteriores[$mov_almacen][$art_codigo]['cant_anterior'] = $cant_actual;
		    	$anteriores[$mov_almacen][$art_codigo]['unit_anterior'] = $mov_costounitario;
		}
	
		return $resultado;
    	}

    	function saldoInicial($desde, $art_desde, $art_hasta, $estacion) {
		global $sqlca;
	
		list($dia, $mes, $ano) = sscanf($desde, "%2s/%2s/%4s");

		if ($mes == 1) {
		    	$mes = 12;
		    	$ano--;
		} else {
		    	$mes--;
		}
	
		if (strlen($mes) == 1) 
			$mes = "0" . $mes;

		$sql = "SELECT
			    	stk_almacen,
			    	art_codigo,
			    	stk_stock" . pg_escape_string($mes) . ",
			    	stk_costo" . pg_escape_string($mes) . "
			FROM
			    	inv_saldoalma
			WHERE
				stk_periodo='" . pg_escape_string($ano) . "'
			    	AND art_codigo>='" . pg_escape_string($art_desde) . "'
			    	AND art_codigo<='" . pg_escape_string($art_hasta) . "' ";
		if ($estacion != "TODAS")
			$sql .= " AND stk_almacen='" . pg_escape_string($estacion) . "' ";
	
		$sql .= "ORDER BY
			    	stk_periodo,
			    	stk_almacen,
			    	art_codigo;";

		echo "\n\nSaldo Inicial:".$sql;

		if ($sqlca->query($sql) < 0) 
			return -1;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    
		    	$stk_almacen = $a[0];
		    	$art_codigo = $a[1];
		    	$stk_stock = $a[2];
		    	$stk_costo = $a[3];
		    
		    	$resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_stock'] = $stk_stock;
		    	$resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costopromedio'] = $stk_costo;
		    	$resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $stk_costo;
		    	$resultado['almacenes'][$stk_almacen]['codigos'][$art_codigo]['stk_costototal'] = $stk_stock*$stk_costo;
		}

		if ($mes == 12) {
		    	$mes = 1;
		    	$ano++;
		} else {
		    	$mes++;
		}

		if ($dia > 1) {
		    	$sql = "SELECT
					mov_cantidad,
					mov_costototal,
					mov_costopromedio,
					mov_costounitario,
					mov_naturaleza,
					mov_almacen,
					art_codigo
			    	FROM
					inv_movialma
			   	WHERE
				    	mov_fecha BETWEEN '" . pg_escape_string($ano."-".$mes."-01") . " 00:00:00' AND '" . pg_escape_string($ano."-".$mes."-".($dia-1)) . " 23:59:59'
					AND art_codigo>='" . pg_escape_string($art_desde) . "'
					AND art_codigo<='" . pg_escape_string($art_hasta) . "' ";
			if ($estacion != "TODAS")
				$sql .= "AND mov_almacen='" . pg_escape_string($estacion) . "' ";
	
			$sql .= " ORDER BY
					mov_almacen,
					art_codigo,
					mov_fecha;";

			echo $sql;
					
		    	if ($sqlca->query($sql) < 0) 
				return $resultado;
	
		    	for($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
			    
				$mov_cantidad 		= $a[0];
				$mov_costototal 	= $a[1];
				$mov_costopromedio 	= $a[2];
				$mov_costounitario 	= $a[3];
				$mov_naturaleza 	= $a[4];
				$mov_almacen 		= $a[5];
				$art_codigo 		= $a[6];
			    
				if ($mov_naturaleza < 3) {
			    	    	$resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] += $mov_cantidad;
				    	$resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $mov_costounitario;
				} else {
				    	$resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock'] -= $mov_cantidad;		
				}
				$resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costounitario'] = $mov_costounitario;
				$resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costopromedio'] = $mov_costopromedio;
				$resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costototal'] = $resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_stock']*$resultado['almacenes'][$mov_almacen]['codigos'][$art_codigo]['stk_costopromedio'];
		    	}
		}
		
		return $resultado;	
    	}    
}
																					  
