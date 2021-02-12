<?php

class ModelVentasMensuales extends Model {

	function GetAlmacen($nualmacen) {
		global $sqlca;

		$cond = '';
		if ($nualmacen != 'T') {
			$cond = "AND ch_almacen = '".$nualmacen."'";
		}

		try {
			$sql = "
			SELECT
				ch_almacen AS nualmacen,
				TRIM (ch_almacen) || ' - ' || TRIM (ch_nombre_almacen) AS noalmacen
			FROM
				inv_ta_almacenes
			WHERE
				ch_clase_almacen = '1' $cond
			ORDER BY
				ch_almacen;
			";

			if ($sqlca->query($sql) <= 0) {
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;

		} catch (Exception $e) {
			throw $e;
		}
	}

	function GetLinea() {
		global $sqlca;

		try {
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
					nolinea;
			";

		if ($sqlca->query($sql) <= 0) {
			$registro[] = array("nucodlinea"=>"","nolinea"=>"No hay Lineas");
		}

		while ($reg = $sqlca->fetchRow()) {
			$registro[] = $reg;
		}

		return $registro;

		} catch (Exception $e) {
			throw $e;
		}

	}

	function search($data) {
		global $sqlca;
		$registro = array();

		//var_dump($data);
		$cod_almacen 	= $data['cod_almacen'];
		$periodo = $data['periodo'];
		$cod_linea 	= $data['cod_linea'];
		$modo 	= $data['modo'];

		if ($cod_almacen != 'T') {
			$cond = "AND v.ch_sucursal = '".$cod_almacen."' ";
		}
		if ($cod_linea != 'T') {
			$cond2 = " AND a.art_linea = '".$cod_linea."'";
		}

		$sql = "
			SELECT  
			v.ch_periodo,
			v.ch_sucursal,
			a.art_codigo||'-'||a.art_descripcion,
			v.nu_can01,
			v.nu_val01,
			v.nu_can02,
			v.nu_val02,
			v.nu_can03,
			v.nu_val03,
			v.nu_can04,
			v.nu_val04,
			v.nu_can05,                      
			v.nu_val05,
			v.nu_can06,
			v.nu_val06,
			v.nu_can07,
			v.nu_val07,
			v.nu_can08,
			v.nu_val08,
			v.nu_can09,
			v.nu_val09,
			v.nu_can10,
			v.nu_val10,
			v.nu_can11,
			v.nu_val11,
			v.nu_can12,
			v.nu_val12
			FROM 
			ven_ta_venta_mensualxitem v,
			int_articulos a,
			int_tabla_general t
			WHERE 
			v.ch_periodo = '$periodo'
			AND a.art_codigo = v.art_codigo
			AND t.tab_elemento = a.art_linea
			AND t.tab_tabla='20'
			$cond
			$cond2; 
		";
		
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

		if ($sqlca->query($sql) <= 0) {
			throw new Exception("No se encontro ningun registro");
		}

		while ($reg = $sqlca->fetchRow()) {
			$registro[] = $reg;
		}

		return $registro;
	}

}