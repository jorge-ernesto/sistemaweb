<?php

class StkMinMaxModel extends Model { // Poder agregar eliminar editar proveedor, producto, precio

	function buscar($almacen, $periodo, $mes, $opcion, $orden) {
		global $sqlca;
	    
		$sql = "SELECT
				sa.art_codigo,
				art.art_descripcion,
				sa.stk_stock$mes,
				mm.stk_minimo,
				mm.stk_maximo,
				art.art_linea,
				tab.tab_descripcion
			FROM
				inv_saldoalma sa
				LEFT JOIN inv_stkminmax mm ON (sa.stk_almacen=mm.ch_almacen AND sa.art_codigo=mm.art_codigo)
				LEFT JOIN int_articulos art ON (sa.art_codigo=art.art_codigo)
				LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' and tab.tab_elemento=art.art_linea)
			WHERE 	
				sa.stk_almacen='$almacen'  
				AND sa.stk_periodo='$periodo'
				
			ORDER BY ";

		if ($orden == "D")
			$sql .= " art.art_descripcion";
		else
			$sql .= " art.art_linea, art.art_descripcion"; 

		//echo $sql;

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$res = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$res[$i]['codigo']		= $a[0];
			$res[$i]['descripcion']		= $a[1];
			$res[$i]['actual']		= $a[2];
			$res[$i]['minimo']		= $a[3];
			$res[$i]['maximo']		= $a[4];
			$res[$i]['linea']		= $a[5];
			$res[$i]['deslinea']		= $a[6];

			if($a[2]<$a[3]){
				$res[$i]['reqminimo']	= $a[3]-$a[2];
				$res[$i]['reqmaximo']	= $a[4]-$a[2];
			} else {
				if($a[2]>=$a[3] and $a[2]<=$a[4]) {
					$res[$i]['reqminimo']	= 0;
					$res[$i]['reqmaximo']	= 0;
				} else { // si stock actual mayor que maximo
					$res[$i]['reqminimo']	= 0;
					$res[$i]['reqmaximo']	= $a[4]-$a[2];
				}
			}			
		}
		
		return $res;
  	}	

	function obtieneListaEstaciones() {
		global $sqlca;
	
		$sql = "SELECT 
				ch_almacen, 
				trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1'
			ORDER BY 
				ch_almacen; ";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$result = Array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0]." - ".$a[1];
		}	
		return $result;
	}

	function obtieneNombreEstacion($almacen) {
		global $sqlca;
	
		$sql = "SELECT 
				ch_almacen, 
				trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1'
				AND ch_almacen='$almacen';";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$a = $sqlca->fetchRow();
		$nombre = $a[0]." - ".$a[1];

		return $nombre;
	}
}
