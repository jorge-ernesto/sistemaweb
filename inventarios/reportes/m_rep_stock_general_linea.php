<?php

class ModelStockGeneralLinea extends Model {

    function GetAlmacen($nualmacen) {
		global $sqlca;

		if($nualmacen != 'T')
			$cond = "AND ch_almacen = '".$nualmacen."'";

		try {
			$sql = "
			SELECT
				ch_almacen as nualmacen,
				TRIM(ch_almacen) || ' - ' || TRIM(ch_nombre_almacen) as noalmacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1'
				$cond
			ORDER BY
				ch_almacen;
			";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}
			
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
    }

	function GetLinea(){
		global $sqlca;
		try{
			$sql = "
			SELECT
				tl.tab_elemento AS nucodlinea,
				tl.tab_elemento || ' - ' || tl.tab_descripcion AS nolinea
			FROM
				int_tabla_general as tl
			WHERE
				tl.tab_tabla = '20'
				AND (tl.tab_elemento != '000000' AND tl.tab_elemento != '')
			ORDER BY
				tl.tab_descripcion;
			";

			if($sqlca->query($sql) <= 0){
				$registro[] = array("nucodlinea"=>"","nolinea"=>"No hay Lineas");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
    }
	
	function SearchLinea($data){
		global $sqlca;

		$nualmacen 	= $data['nualmacen'];
		$nucodlinea = $data['nucodlinea'];
		$nuyear 	= $data['nuyear'];
		$numonth 	= $data['numonth'];
		$notipo 	= $data['notipo'];

		$condalmacen 	= NULL;
		$condlinea 		= NULL;
		$condtipo 		= NULL;

		if($nualmacen != 'T'){
			$columnalmacen	= "TRIM(alma.ch_almacen)|| ' - ' ||TRIM(alma.ch_nombre_almacen) noalmacen,";
			$condalmacen	= "AND saldo.stk_almacen = '" . $nualmacen . "'";
			$groupalmacen	= "noalmacen,";
			$orderalmacen	= "noalmacen,";
		}else{
			$columnalmacen	= "'TODOS' noalmacen,";
		}

		if($nucodlinea != 'T')
			$condlinea = "AND art.art_linea = '".$nucodlinea."'";

		if($notipo == 'S')
			$condtipo = "AND saldo.stk_stock".$numonth." > 0"; 

		try {

			$registro = array();

			$sql ="
				SELECT
					$columnalmacen
					TRIM(tl.tab_elemento)|| ' - ' ||TRIM(tl.tab_descripcion) nolinea,
					TRIM(art.art_codigo)|| ' - ' ||TRIM(art.art_descripcion) noproducto,
					TRIM(art.art_unidad) nucodunidad,
					ROUND(SUM(saldo.stk_stock" . $numonth . "), 2) nucantidad,
					ROUND(SUM(saldo.stk_costo" . $numonth . "), 2) nucosto,
					ROUND(SUM(saldo.stk_stock" . $numonth . " * saldo.stk_costo" . $numonth . "), 2) nutotal
				FROM
					inv_saldoalma saldo
					JOIN inv_ta_almacenes alma ON (alma.ch_almacen = saldo.stk_almacen)
					JOIN int_articulos art ON(art.art_codigo = saldo.art_codigo)
					LEFT JOIN int_tabla_general tl ON (tl.tab_elemento = art.art_linea AND tl.tab_tabla = '20' AND (tl.tab_elemento != '000000' AND tl.tab_elemento != ''))
				WHERE
					saldo.stk_periodo 	= '" . $nuyear . "'
					AND art.art_plutipo = '1'
					" . $condalmacen . "
					" . $condlinea . "
					" . $condtipo . "
				GROUP BY
					" . $groupalmacen . "
					nolinea,
					noproducto,
					nucodunidad
				ORDER BY
					" . $orderalmacen . "
					nolinea,
					noproducto;
				";
			// echo "<pre>";
			// print_r($sql);
			// echo "</pre>";

			if ($sqlca->query($sql) <= 0) {
            	throw new Exception("No se encontro ningun registro");
			}
			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
	}
}