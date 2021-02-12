<?php

class KardexActModel extends Model {

	function saldo_inicial_mes_anterior($ano, $mes, $estacion) {

		global $sqlca;

		if ($mes == 1 || $mes == "01") {
			$mes = 12;
			$ano--;
		} else {
			$mes--;
		}

		$mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
		$query_stacion = ($estacion == "TODAS") ? '' : "AND sa.stk_almacen='$estacion'";

		$sql = "
			SELECT
                                art.art_codigo,
                                uni.unidad_medida,
                                art.art_descripcion,
			    	sa.stk_almacen,
			    	sa.stk_stock" . $mes . ",
			    	sa.stk_costo" . $mes . "
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
                                LEFT JOIN (SELECT tab.tab_elemento,tab_car_03 ||'-'|| tab_car_04  as unidad_medida FROM int_tabla_general tab WHERE tab.tab_tabla='34' ) uni ON(uni.tab_elemento = art.art_unidad)
			WHERE
				sa.stk_periodo = '$ano' 
                                $query_stacion
                        ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;
                ";

		echo "SALDO INCIAL MES ANTERIOR:\n".$sql;

		if ($sqlca->query($sql) < 0)
			return -1;

        	$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();

		    	$stk_almacen	= trim($a['stk_almacen']);
		    	$art_codigo	= trim($a['art_codigo']);
		    	$stk_stock	= $a['stk_stock' . $mes];
		    	$stk_costo	= $a['stk_costo' . $mes];

		    	$resultado['st_inicial'][$stk_almacen][$art_codigo]['stk_stock']		= $stk_stock;
		    	$resultado['st_inicial'][$stk_almacen][$art_codigo]['stk_costopromedio']	= $stk_costo;
		    	$resultado['st_inicial'][$stk_almacen][$art_codigo]['stk_costounitario']	= $stk_costo;
		    	$resultado['st_inicial'][$stk_almacen][$art_codigo]['stk_costototal']		= $stk_stock * $stk_costo;

		}

        	return $resultado;

	}

	function saldo_inicial_mes_actual($ano, $mes, $estacion) {

        	global $sqlca;

        	$mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
		$query_stacion = ($estacion == "TODAS") ? '' : "AND sa.stk_almacen='$estacion'";

		$sql = "
			SELECT
                                art.art_codigo,
                                uni.unidad_medida,
                                art.art_descripcion,
			    	sa.stk_almacen,
			    	sa.stk_stock" . $mes . ",
			    	sa.stk_costo" . $mes . "
			FROM
			    	inv_saldoalma sa 
			    	LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)  
                                LEFT JOIN (SELECT tab.tab_elemento,tab_car_03 ||'-'|| tab_car_04  as unidad_medida FROM int_tabla_general tab WHERE tab.tab_tabla='34' ) uni ON(uni.tab_elemento = art.art_unidad)
			WHERE
				sa.stk_periodo = '$ano' 
                                $query_stacion
                        ORDER BY
			    	sa.stk_periodo,
			    	sa.stk_almacen,
			    	sa.art_codigo;
                ";

		echo "SALDO INCIAL MES ACTUAL:\n".$sql;

        	if ($sqlca->query($sql) < 0)
			return -1;

        	$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    	$a = $sqlca->fetchRow();

		    	$stk_almacen	= trim($a['stk_almacen']);
		    	$art_codigo	= trim($a['art_codigo']);
		    	$stk_stock	= $a['stk_stock' . $mes];
		    	$stk_costo	= $a['stk_costo' . $mes];

		    	$resultado['st_final'][$stk_almacen][$art_codigo]['stk_stock']		= $stk_stock;
		    	$resultado['st_final'][$stk_almacen][$art_codigo]['stk_costopromedio']	= $stk_costo;
		    	$resultado['st_final'][$stk_almacen][$art_codigo]['stk_costounitario']	= $stk_costo;
			$resultado['st_final'][$stk_almacen][$art_codigo]['stk_costototal']	= $stk_stock * $stk_costo;

		}

        	return $resultado;

	}

	function ingreso_inventario($ano, $mes, $estacion) {

        	global $sqlca;

		$ultimo_dia	= KardexActModel::getUltimoDiaMes($ano, $mes);
		$query_stacion	= ($estacion == "TODAS") ? '' : " AND inv.mov_almacen='$estacion'";
		$fecha_inicio	= $ano . "-" . $mes . "-01 00:00:00";
		$fecha_final	= $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";

		$sql = "
			SELECT  
		                inv.mov_almacen,
		                inv.art_codigo,
		                SUM(inv.mov_cantidad) as mov_cantidad,
				(CASE WHEN SUM(inv.mov_costototal) < 1 THEN '0.00' ELSE ROUND((SUM(inv.mov_costototal)/SUM(inv.mov_cantidad)),4) END) as mov_costounitario,
		                SUM(inv.mov_costopromedio) as mov_costopromedio,
		                SUM(inv.mov_costototal) as mov_costototal
			FROM
				inv_movialma inv
				INNER JOIN inv_tipotransa inv_t ON (inv.tran_codigo = inv_t.tran_codigo)
			WHERE
				inv_t.tran_naturaleza IN('1','2') 
				AND (inv.mov_fecha BETWEEN '$fecha_inicio' AND '$fecha_final')
				$query_stacion
			GROUP BY 
                        	inv.mov_almacen,
                        	inv.art_codigo
			ORDER BY
				inv.art_codigo;
		";

        	echo "INGRESOS:\n".$sql;

        	if ($sqlca->query($sql) < 0)
            		return -1;

        	$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();

            		$mov_almacen		= trim($a['mov_almacen']);
            		$art_codigo		= trim($a['art_codigo']);
			$mov_cantidad		= $a['mov_cantidad'];
            		$mov_costounitario	= $a['mov_costounitario'];
            		$mov_costopromedio	= $a['mov_costopromedio'];
            		$mov_costototal		= $a['mov_costototal'];
            		$tran_descripcion	= $a['tran_descripcion'];

            		$resultado[$mov_almacen][$art_codigo]['mov_cantidad']		= $mov_cantidad;
            		$resultado[$mov_almacen][$art_codigo]['mov_costounitario']	= $mov_costounitario;
            		$resultado[$mov_almacen][$art_codigo]['mov_costopromedio']	= $mov_costopromedio;
            		$resultado[$mov_almacen][$art_codigo]['mov_costototal']		= $mov_costototal;
            		$resultado[$mov_almacen][$art_codigo]['tran_descripcion']	= $tran_descripcion;

        	}

		return $resultado;

    	}

	function ventas_inventario($ano, $mes, $estacion) {

        	global $sqlca;

        	$ultimo_dia	= KardexActModel::getUltimoDiaMes($ano, $mes);
        	$query_stacion	= ($estacion == "TODAS") ? '' : " AND inv.mov_almacen='$estacion'";
        	$fecha_inicio	= $ano . "-" . $mes . "-01 00:00:00";
        	$fecha_final	= $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";

        	$sql = "
			SELECT
				inv.mov_almacen,
                        	inv.art_codigo,
				SUM(inv.mov_cantidad) as mov_cantidad,
				(CASE WHEN SUM(inv.mov_costototal) < 1 THEN '0.00' ELSE ROUND((SUM(inv.mov_costototal)/SUM(inv.mov_cantidad)),4) END) as mov_costounitario,
				SUM(inv.mov_costopromedio) as mov_costopromedio,
				SUM(inv.mov_costototal) as mov_costototal
			FROM
				inv_movialma inv
				INNER JOIN inv_tipotransa inv_t ON (inv.tran_codigo = inv_t.tran_codigo)
	                WHERE
				inv_t.tran_naturaleza IN('3','4')
				AND (inv.mov_fecha BETWEEN '$fecha_inicio' AND '$fecha_final')
				$query_stacion
	                GROUP BY 
				inv.mov_almacen,
				inv.art_codigo               
	                ORDER BY
				inv.art_codigo;
                	";

		echo "SALIDAS:\n".$sql;

		if ($sqlca->query($sql) < 0)
			return -1;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

			$a = $sqlca->fetchRow();

		    	$mov_almacen		= trim($a['mov_almacen']);
		    	$art_codigo		= trim($a['art_codigo']);
		    	$mov_cantidad		= $a['mov_cantidad'];
		    	$mov_costounitario	= $a['mov_costounitario'];
		    	$mov_costopromedio	= $a['mov_costopromedio'];
		   	$mov_costototal		= $a['mov_costototal'];
		    	$tran_descripcion	= $a['tran_descripcion'];

		    	$resultado[$mov_almacen][$art_codigo]['mov_cantidad']		= $mov_cantidad;
		    	$resultado[$mov_almacen][$art_codigo]['mov_costounitario']	= $mov_costounitario;
		    	$resultado[$mov_almacen][$art_codigo]['mov_costopromedio']	= $mov_costopromedio;
		    	$resultado[$mov_almacen][$art_codigo]['mov_costototal']		= $mov_costototal;
		    	$resultado[$mov_almacen][$art_codigo]['tran_descripcion']	= $tran_descripcion;

		}

		return $resultado;

	}

	function ingreso_ajuste_inventario($ano, $mes, $estacion) {

        	global $sqlca;

		$ultimo_dia	= KardexActModel::getUltimoDiaMes($ano, $mes);
		$query_stacion	= ($estacion == "TODAS") ? '' : " AND inv.mov_almacen='$estacion'";
		$fecha_inicio	= $ano . "-" . $mes . "-01 00:00:00";
		$fecha_final	= $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";

        	$sql = "
			SELECT  
		                inv.mov_almacen,
		                inv.art_codigo,
		                SUM(inv.mov_cantidad) as mov_cantidad
			FROM
				inv_movialma inv
		        	INNER JOIN inv_tipotransa inv_t ON (inv.tran_codigo = inv_t.tran_codigo)
		        WHERE
				inv_t.tran_naturaleza = '1'
				AND inv_t.tran_codigo = '17'
				AND (inv.mov_fecha BETWEEN '$fecha_inicio' AND '$fecha_final')
				$query_stacion
			GROUP BY 
                        	inv.mov_almacen,
                        	inv.art_codigo
			ORDER BY
				inv.art_codigo;
                ";

        	echo "AJUSTE INVENTARIO:\n" . $sql;

		if ($sqlca->query($sql) < 0)
			return -1;

        	$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

            		$a = $sqlca->fetchRow();

		    	$mov_almacen	= trim($a['mov_almacen']);
		    	$art_codig	= trim($a['art_codigo']);
		    	$mov_cantidad	= $a['mov_cantidad'];

			$resultado[$mov_almacen][$art_codigo]['mov_cantidad'] = $mov_cantidad;

		}

        	return $resultado;

	}

	function ingreso_inventario_detallado($ano, $mes, $estacion) {

        	global $sqlca;

        	$ultimo_dia = KardexActModel::getUltimoDiaMes($ano, $mes);
        	$query_stacion = ($estacion == "TODAS") ? '' : " AND inv.mov_almacen='$estacion'";
        	$fecha_inicio = $ano . "-" . $mes . "-01 00:00:00";
        	$fecha_final = $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";

		$sql = "
                  	SELECT  
		                inv.mov_almacen,
		                inv.art_codigo,
		                inv.tran_codigo,
		                SUM(inv.mov_cantidad) as mov_cantidad,
				(CASE WHEN SUM(inv.mov_costototal) < 1 THEN '0.00' ELSE ROUND((SUM(inv.mov_costototal)/SUM(inv.mov_cantidad)),4) END) as mov_costounitario,
                        	SUM(inv.mov_costopromedio) as mov_costopromedio,
                        	SUM(inv.mov_costototal) as mov_costototal,
                        	inv_t.tran_descripcion
			FROM
				inv_movialma inv
				INNER JOIN inv_tipotransa inv_t ON (inv.tran_codigo = inv_t.tran_codigo)
			WHERE
				inv_t.tran_naturaleza IN('1','2')
		                AND (inv.mov_fecha BETWEEN '$fecha_inicio' AND '$fecha_final')
				$query_stacion
			GROUP BY
				inv.mov_almacen,
	                        inv.art_codigo,
	                        inv.tran_codigo,
	                        inv_t.tran_descripcion
			ORDER BY
				inv.art_codigo;
		";

		echo "INGRESOS DETALLADO:\n".$sql;

        	if ($sqlca->query($sql) < 0)
			return -1;

	        $resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

		    	$mov_almacen		= trim($a['mov_almacen']);
		    	$art_codigo		= trim($a['art_codigo']);
		    	$tran_codigo		= trim($a['tran_codigo']);
		    	$mov_cantidad		= $a['mov_cantidad'];
		    	$mov_costounitario	= $a['mov_costounitario'];
		    	$mov_costopromedio	= $a['mov_costopromedio'];
		    	$mov_costototal		= $a['mov_costototal'];
		    	$tran_descripcion	= $a['tran_descripcion'];

		    	$resultado[$mov_almacen][$art_codigo][$tran_codigo]['mov_cantidad']		= $mov_cantidad;
		    	$resultado[$mov_almacen][$art_codigo][$tran_codigo]['mov_costounitario']	= $mov_costounitario;
		    	$resultado[$mov_almacen][$art_codigo][$tran_codigo]['mov_costopromedio']	= $mov_costopromedio;
		    	$resultado[$mov_almacen][$art_codigo][$tran_codigo]['mov_costototal']		= $mov_costototal;
		    	$resultado[$mov_almacen][$art_codigo][$tran_codigo]['tran_descripcion']		= $tran_descripcion;

		}

		return $resultado;

	}

    	function lista_productos($ano, $mes, $estacion) {

        	global $sqlca;

		$ultimo_dia = KardexActModel::getUltimoDiaMes($ano, $mes);
		$query_stacion = ($estacion == "TODAS") ? '' : " WHERE  inv.mov_almacen='$estacion'";
		$fecha_inicio = $ano . "-" . $mes . "-01 00:00:00";
		$fecha_final = $ano . "-" . $mes . "-" . $ultimo_dia . " 23:59:59";

        	$sql = "
                	SELECT
                    		inv.mov_almacen,
                    		art.art_linea,
                    		inv.art_codigo ,
                    		uni.unidad_medida,
                    		art.art_descripcion
			FROM
                        	inv_movialma inv 
		                LEFT JOIN int_articulos art ON (inv.art_codigo=art.art_codigo) 
                		LEFT JOIN (SELECT tab.tab_elemento,tab_car_03 ||'-'|| tab_car_04 as unidad_medida FROM int_tabla_general tab WHERE tab.tab_tabla='34') uni ON (uni.tab_elemento = art.art_unidad)
				$query_stacion
			GROUP BY
				inv.mov_almacen,
				art.art_linea,
				inv.art_codigo ,
				uni.unidad_medida,
				art.art_descripcion
			ORDER BY
				art.art_linea;
			";

		echo "PRODUCTOS:\n".$sql;

        	if ($sqlca->query($sql) < 0)
			return -1;

		$resultado = Array();

        	for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    	$a = $sqlca->fetchRow();

		    	$mov_almacen		= trim($a['mov_almacen']);
		    	$art_linea		= trim($a['art_linea']);
		    	$art_codigo		= trim($a['art_codigo']);
		    	$art_descripcion	= $a['art_descripcion'];
		    	$unidad_medida		= $a['unidad_medida'];
		    	$tab_elemento		= $a['tab_elemento'];

            		$resultado[$mov_almacen][$art_linea][$art_codigo] = array("desc" => $art_descripcion, "cod" => trim($art_codigo), "unidades" => $unidad_medida, "linea" => $tab_elemento);

        	}

		return $resultado;

	}

	function getdescripcion_linea() {

        	global $sqlca;

		$sql = "
			SELECT 
				tab_elemento,
				tab_descripcion 
			FROM
				int_tabla_general
			WHERE
				tab_tabla = '20';
			";

        	if ($sqlca->query($sql) < 0)
			return -1;

		$resultado = Array();

        	for ($i = 0; $i < $sqlca->numrows(); $i++) {
            		$a = $sqlca->fetchRow();
            		$tab_elemento = trim($a['tab_elemento']);
            		$resultado[$tab_elemento]['tab_descripcion'] = trim($a['tab_descripcion']);
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
				ch_clase_almacen = '1'
			ORDER BY
				ch_almacen;
			";
	
		if ($sqlca->query($sql, "_estaciones") < 0)
			return null;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows("_estaciones"); $i++) {
			$array = $sqlca->fetchRow("_estaciones");
			$resultado[$array[0]] = $array[0] . " - " . $array[1];
		}

		$resultado['TODAS'] = "Todas las estaciones";

		return $resultado;

	}

	function getUltimoDiaMes($elAnio, $elMes) {
        	return date("d", (mktime(0, 0, 0, $elMes + 1, 1, $elAnio) - 1));
	}

}
