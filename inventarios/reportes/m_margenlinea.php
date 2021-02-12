<?php
class MargenLineaModel extends Model {
	function busqueda ($almacen, $tipocosto, $anio, $mes) {
		global $sqlca;

		$query = "SELECT par_valor FROM int_parametros WHERE par_nombre='lista precio';";
		if($sqlca->query($query) < 0) 
			return false;

		$a = $sqlca->fetchRow();
		$listaprecio = trim($a[0]); 

	if ($tipocosto == "01"){ // costo promedio : inv_saldoalma
		$sql = "SELECT
					art.art_linea AS linea,
					MAX(tab.tab_descripcion) as descripcion_linea,
					(SUM(Case when pre.pre_precio_act1> 0 then (1-((sa.stk_costo$mes*1.18)/pre.pre_precio_act1))
					Else 0 end*100)/
					(SELECT 
					count(art2.*) 
					FROM 
					int_articulos art2 
					RIGHT JOIN inv_saldoalma sa2 ON (sa2.art_codigo = art2.art_codigo)
					RIGHT JOIN fac_lista_precios pre2 ON (pre2.art_codigo=sa2.art_codigo AND pre2.pre_lista_precio='01')
					RIGHT JOIN int_tabla_general tab2 ON (tab2.tab_tabla='20' AND tab2.tab_elemento=art2.art_linea)
					WHERE 
					sa2.stk_costo$mes>0
					AND sa2.stk_almacen='$almacen'
					AND art2.art_estado='0'
					AND sa2.stk_periodo='$anio'
					AND art2.art_linea=art.art_linea)
					) AS margen_actual,
					MAX(tab.tab_num_01) AS margen_linea
				FROM
					int_articulos art

				RIGHT JOIN inv_saldoalma sa ON (sa.art_codigo = art.art_codigo)

					RIGHT JOIN fac_lista_precios pre ON (pre.art_codigo=sa.art_codigo AND pre.pre_lista_precio='$listaprecio')
					RIGHT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
				WHERE
					sa.stk_costo$mes>0
					AND sa.stk_almacen='$almacen'
					AND art.art_estado='0'
					AND sa.stk_periodo='$anio'
				GROUP BY
					art.art_linea
				ORDER BY 
					art.art_linea ";
	} else { // costo por ultima compra : com_rec_pre_proveedor
		$sql = "SELECT
						art.art_linea AS linea,
						MAX(tab.tab_descripcion) as descripcion_linea,
						ROUND(((ROUND(SUM(pre.pre_precio_act1),2) - ROUND(SUM(pro.rec_precio),2) * 1.18) / ROUND(SUM(pre.pre_precio_act1),2) * 100),2) margen_actual,
						MAX(tab.tab_num_01) AS margen_linea
					FROM
						int_articulos art
						RIGHT JOIN com_rec_pre_proveedor pro ON (pro.art_codigo = art.art_codigo)
						RIGHT JOIN fac_lista_precios pre ON (pre.art_codigo=art.art_codigo AND pre.pre_lista_precio='$listaprecio')
						RIGHT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
					WHERE
						pro.rec_precio > 0
						AND pre.pre_precio_act1 > 0
						AND art.art_estado = '0'
					GROUP BY
						art.art_linea 
					ORDER BY
						art.art_linea";
	}

	echo "+++ EL QUERY LINEA FINAL ES: ".$sql." +++";

	if($sqlca->query($sql) < 0) 
		return false;

	$resultado = Array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$resultado[$i] = $sqlca->fetchRow();
		$resultado[$i]['extra1'] = $almacen;
		$resultado[$i]['extra2'] = $tipocosto;
		$resultado[$i]['extra3'] = $anio;
		$resultado[$i]['extra4'] = $mes;					
	}
	return $resultado;
	}

	function obtenerDetalleLinea($linea, $almacen, $tipocosto, $anio, $mes) {  //	if ($tipolista == "01"){
		global $sqlca;

		$query = "SELECT par_valor FROM int_parametros WHERE par_nombre='lista precio';";

		if($sqlca->query($query) < 0) 
			return false;

		$a = $sqlca->fetchRow();
		$listaprecio = trim($a[0]); 
		
		if($tipocosto == "01"){

			$sql = "SELECT
							sa.art_codigo as codigo,
							art.art_descripcion as descripcion,
							((1-((sa.stk_costo$mes *1.18)/pre.pre_precio_act1))*100) AS margen_actual,
							CASE 
							WHEN sa.stk_costo$mes = '0.0000' THEN 
							(SELECT 
							mov_costounitario 
							FROM inv_movialma WHERE mov_fecha < '" . ($anio . "-" . $mes . "-01") . " 00:00:00' AND art_codigo = art.art_codigo
							GROUP BY
							mov_costounitario,
							mov_fecha
							ORDER BY 
							mov_fecha DESC 
							LIMIT 1)
							ELSE sa.stk_costo$mes
							END as costo,
							pre.pre_precio_act1 as precio
						FROM
							int_articulos art
							RIGHT JOIN inv_saldoalma sa ON (sa.art_codigo = art.art_codigo)
							RIGHT JOIN fac_lista_precios pre ON (pre.art_codigo=art.art_codigo AND pre.pre_lista_precio='$listaprecio')
						WHERE
							sa.stk_costo$mes>=0
							AND sa.stk_periodo='$anio'
							AND sa.stk_almacen='$almacen'
							AND art.art_estado='0'
							AND art.art_linea='". pg_escape_string($linea) ."'
						ORDER BY
							sa.art_codigo ASC ";
		} else {		
			$sql = "SELECT
							pro.art_codigo as codigo,
							art.art_descripcion as descripcion,
							((1-((pro.rec_precio*1.18)/pre.pre_precio_act1))*100) AS margen_actual,
							pro.rec_precio as costo,
							pre.pre_precio_act1 as precio
						FROM
							int_articulos art
							RIGHT JOIN com_rec_pre_proveedor pro ON (pro.art_codigo = art.art_codigo)
							RIGHT JOIN fac_lista_precios pre ON (pre.art_codigo=art.art_codigo AND pre.pre_lista_precio='$listaprecio')
						WHERE
							pro.rec_precio>0
							AND pre.pre_precio_act1 >= 0.01
							AND art.art_estado = '0'
							AND art.art_linea = '" . pg_escape_string($linea) . "'
						ORDER BY
							pro.art_codigo ASC ";
		}

		echo "+++ EL QUERY DETALLE FINAL ES: ".$sql." +++";

		if ($sqlca->query($sql) < 0) 
			return false;

		$resultado = array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$resultado[$i] = $sqlca->fetchRow();
		}

		return $resultado;	
	}

	function obtieneListaEstaciones() {
		global $sqlca;
	
		$sql = "SELECT 
				ch_almacen, trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1'
			ORDER BY 
				ch_almacen ; ";

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}	

		return $result;
    	}

}
