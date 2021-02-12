<?php
class MargenLineaModel extends Model
{

	function obtenerAlmacenes() {
		global $sqlca;

		$sql = "SELECT
		            ch_almacen,
		            ch_almacen||' - '||ch_nombre_almacen
		        FROM
		            inv_ta_almacenes
		        WHERE
		            ch_clase_almacen='1'
		        ;
		        ";
		if ($sqlca->query($sql) < 0) return false;

		$result = array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();

		    $ch_almacen = $a[0];
		    $ch_nombre_almacen = $a[1];

		    $result[$ch_almacen] = $ch_nombre_almacen;
		}
		return $result;
    	}

	function busqueda($numlinea, $codalmacen){
		global $sqlca;
		
		$sql = "SELECT
				art.art_linea AS linea,
				MAX(tab.tab_descripcion) as descripcion_linea,
				(SUM(Case when pre.pre_precio_act1> 0 then (1-((art.art_costoreposicion*1.19)/pre.pre_precio_act1))
				Else 0 end*100)/(SELECT count(art2.*) FROM int_articulos art2 WHERE art2.art_linea=art.art_linea)) AS margen_actual,
				MAX(tab.tab_num_01) AS margen_linea
			FROM
				int_articulos art
				RIGHT JOIN fac_lista_precios pre ON (pre.art_codigo=art.art_codigo AND pre.pre_lista_precio='01')
				RIGHT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
				--right join inv_movialma ma on (art.art_codigo = ma.art_codigo)

			WHERE
				art.art_costoreposicion>0
				--and ma.mov_almacen='001'
				AND art.art_estado='0'";

	if(trim($numlinea) != '') 
		$sql .= " AND art.art_linea = '".$numlinea."' ";
		$sql .= "
	
			GROUP BY
				art.art_linea ORDER BY linea";

		echo '%%%%% query 1: '.$sql.' %%%%%';
		if ($sqlca->query($sql) < 0) return false;

		$resultado = Array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$resultado[$i] = $sqlca->fetchRow();
		}
		
		return $resultado;
	}

	function obtenerDetalleLinea($lineas, $codalmacen) {
		global $sqlca;
		$c = 0; $final = array();
		//$stk_periodo = date('Y');
		$stk_periodo = '2010';  
		$stk_mesperiodo = date('m');
		for ($k = 0; $k < count($lineas); $k++) {

			$sql = "SELECT
					art.art_codigo as codigo,
					art.art_descripcion as descripcion,
					((1-((art.art_costoreposicion*1.19)/nullif(pre.pre_precio_act1,0)))*100) AS margen_actual,
					art.art_costoreposicion as costo,
					pre.pre_precio_act1 as precio,
					art.art_linea as lineap,
					substr(art.art_unidad,4,3) as unidad,	
					sa.stk_stock".$stk_mesperiodo." as stock
	,(select max(mov_fecha) from inv_movialma where art_codigo=art.art_codigo and (trim(mov_naturaleza)='1' or trim(mov_naturaleza)='2')) as fecha_ult_compra
	,(select max(mov_fecha) from inv_movialma where art_codigo=art.art_codigo and (trim(mov_naturaleza)='3' or trim(mov_naturaleza)='4')) as fecha_ult_venta
,(select mov_cantidad from inv_movialma where art_codigo=art.art_codigo and (trim(mov_naturaleza)='1' or trim(mov_naturaleza)='2') 
and mov_fecha=(select max(mov_fecha) from inv_movialma where art_codigo=art.art_codigo and (trim(mov_naturaleza)='1' or trim(mov_naturaleza)='2')) LIMIT 1) as cantidadc
				FROM
					int_articulos art
					RIGHT JOIN fac_lista_precios pre ON (pre.art_codigo=art.art_codigo AND pre.pre_lista_precio='01')
					left join inv_saldoalma sa on (art.art_codigo = sa.art_codigo and stk_almacen='".$codalmacen."' 
									and stk_periodo='".$stk_periodo."')
				WHERE
					art.art_costoreposicion>0
					AND art.art_estado='0'
					AND art.art_linea='" . pg_escape_string($lineas[$k]['linea']) . "'
				
				ORDER BY
					art.art_codigo ASC
				";

			echo '***** query 2: '.$sql.' *****';
			if ($sqlca->query($sql) < 0) return false;
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$final[$c] = $sqlca->fetchRow();							
				$c = $c + 1;			
			}
		}
		return $final;	
	}

}
