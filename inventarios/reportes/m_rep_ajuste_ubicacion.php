<?php

class ModelReporteAjusteUbicacion extends Model {

    	function GetAlmacen($nualmacen) {
		global $sqlca;

		if(!empty($nualmacen))
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

	function GetUbicacion($nualmacen){
		global $sqlca;
		
		try{

			$sql = "SELECT
					cod_ubicac nuubicacion,
					TRIM(cod_ubicac) || ' - ' || TRIM(desc_ubicac) noubicacion
				FROM
					inv_ta_ubicacion
				WHERE
					cod_almacen = '$nualmacen'
				ORDER BY
					cod_ubicac;
			";

			if($sqlca->query($sql) <= 0){
				$registro[] = array("nuubicacion"=>"","noubicacion"=>"No hay Ubicaciones");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

    	}
	
	function BuscarUbicacion($data){
		global $sqlca;

		$nualmacen 	= $data['nualmacen'];
		$nuubicacion 	= $data['nuubicacion'];
		$fbuscar 	= $data['fbuscar'];
		$notipo 	= $data['notipo'];

		if($nuubicacion != 'T')
			$cond = "AND ubi.cod_ubicac = '".$nuubicacion."'";

		try {

			$registro = array();

			if($notipo == "D"){

				$sql = "
					SELECT
						TRIM(movi.mov_almacen)|| ' - ' ||TRIM(alma.ch_nombre_almacen) noalmacen,
						TRIM(ubi.cod_ubicac)|| ' - ' ||TRIM(ubi.desc_ubicac) noubicacion,
						TRIM(art.art_codigo)|| ' - ' ||TRIM(art.art_descripcion) noproducto,
						TO_CHAR(movi.mov_fecha, 'DD/MM/YYYY') femision,
						ROUND(SUM(movi.mov_cantidad), 2) nucantidad,
						ROUND(SUM(movi.mov_costounitario), 2) nucosto,
						ROUND(SUM(movi.mov_costototal), 2) nutotal,
						movi.mov_numero nuformulario
					FROM
						inv_movialma movi
						JOIN inv_ta_almacenes alma ON (alma.ch_almacen = movi.mov_almacen)			
						JOIN int_articulos art ON(art.art_codigo = movi.art_codigo)
						JOIN inv_ta_ubicacion as ubi ON(ubi.cod_almacen = movi.mov_almacen AND ubi.cod_ubicac = art.art_cod_ubicac)--UBICACION
					WHERE
						movi.tran_codigo		= '17'
						AND movi.mov_almacen		= '".$nualmacen."'
						AND movi.mov_fecha::DATE	= '".$fbuscar."'
						$cond
					GROUP BY
						noalmacen,
						noubicacion,
						noproducto,
						femision,
						nuformulario
					ORDER BY
						noubicacion,
						noproducto;
					";

			}else{

				$sql = "
					SELECT
						TRIM(movi.mov_almacen)|| ' - ' ||TRIM(alma.ch_nombre_almacen) noalmacen,
						TRIM(ubi.cod_ubicac)|| ' - ' ||TRIM(ubi.desc_ubicac) noubicacion,
						movi.mov_fecha femision,
						ROUND(SUM(movi.mov_cantidad), 2) nucantidad,
						ROUND(SUM(movi.mov_costounitario), 2) nucosto,
						ROUND(SUM(movi.mov_costototal), 2) nutotal
					FROM
						inv_movialma movi
						JOIN inv_ta_almacenes alma ON (alma.ch_almacen = movi.mov_almacen)		
						JOIN int_articulos art ON(art.art_codigo = movi.art_codigo)
						JOIN inv_ta_ubicacion as ubi ON(ubi.cod_almacen = movi.mov_almacen AND ubi.cod_ubicac = art.art_cod_ubicac)--UBICACION
					WHERE
						movi.tran_codigo		= '17'
						AND movi.mov_almacen		= '".$nualmacen."'
						AND movi.mov_fecha::DATE	= '".$fbuscar."'
						$cond
					GROUP BY
						noalmacen,
						noubicacion,
						femision
					ORDER BY
						noubicacion;
					";

			}
/*
			echo "<pre>";
			print_r($sql);
			echo "</pre>";
*/
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

?>
