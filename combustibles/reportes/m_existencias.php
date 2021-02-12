<?php
class ExistenciasModel extends Model {
	function obtenerNombreCombustible($codigo) {
		global $sqlca;
		$sql = " SELECT
						ch_nombrecombustible
					FROM
						comb_ta_combustibles
					WHERE
						ch_codigocombustible = '" . pg_escape_string($codigo) . "';";

		if ($sqlca->query($sql, "_obtnombre") < 0) 
			return false;	
		$a = $sqlca->fetchRow("_obtnombre");
	
		return $a[0];
	}

	function obtenerCapacidadTanque($sucursal, $tanque) {
		global $sqlca;
		$sql = " SELECT
						nu_capacidad
					FROM
						comb_ta_tanques
					WHERE
						ch_sucursal = '" . pg_escape_string($sucursal) . "'
						AND ch_tanque = '" . pg_escape_string($tanque) . "';";

		if ($sqlca->query($sql, "_obtcap") < 0) 
			return false;	
		$a = $sqlca->fetchRow("_obtcap");
	
		return $a[0];
	}

	function obtenerNombreEstacion($sucursal) {
		global $sqlca;
		$sql = " SELECT
						ch_nombre_almacen
					FROM
						inv_ta_almacenes
					WHERE
						ch_almacen = '" . pg_escape_string($sucursal) . "'
						AND ch_sucursal = '" . pg_escape_string($sucursal) . "';";

		if ($sqlca->query($sql, "_nombest") < 0) 
			return false;	
		$a = $sqlca->fetchRow("_nombest");
	
		return $a[0];
	}

	function obtenerProductoTanque($sucursal, $tanque) {
		global $sqlca;
		$sql = " SELECT
						ch_codigocombustible
					FROM
						comb_ta_tanques
					WHERE
						ch_sucursal = '" . pg_escape_string($sucursal) . "'
						AND ch_tanque = '" . pg_escape_string($tanque) . "';";

		if ($sqlca->query($sql, "_obtprod") < 0) 
			return false;
		$a = $sqlca->fetchRow("_obtprod");
	
		$sql = " SELECT
						ch_nombrecombustible
					FROM
						comb_ta_combustibles
					WHERE
						ch_codigocombustible = '" . pg_escape_string($a[0]) . "';";

		if ($sqlca->query($sql, "_obtprod") < 0) 
			return false;
		$a = $sqlca->fetchRow("_obtprod");

		return $a[0];
	}

	function obtenerCodigoProductoTanque($sucursal, $tanque) {
		global $sqlca;
		$sql = " SELECT
						ch_codigocombustible
					FROM
						comb_ta_tanques
					WHERE
					ch_sucursal = '" . pg_escape_string($sucursal) . "'
						AND ch_tanque = '" . pg_escape_string($tanque) . "';";

		if ($sqlca->query($sql, "_obtprod") < 0) 
			return false;
		$a = $sqlca->fetchRow("_obtprod");
	
		return $a[0];
	}

	function obtenerNombreBreve() {
		global $sqlca;
		$sql = " SELECT
						ch_nombrebreve
					FROM
						comb_ta_combustibles
					WHERE
						ch_nombrebreve <> 'GLP'
						AND ch_nombrebreve <> 'GNV'
						AND ch_nombrebreve <> 'KEROSENE'
					ORDER BY
						ch_nombrebreve;";

		if ($sqlca->query($sql) < 0) 
			return null;
		$a = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$fila = $sqlca->fetchRow();
			$a[$i] = $fila[0];
		}

		return $a;
	}

	function search($fecha) {
		global $sqlca;
		$sql = " SELECT
						ch_sucursal,
						ch_tanque,
						nu_medicion
					FROM
						comb_ta_mediciondiaria
					WHERE
						dt_fechamedicion = to_date('" . pg_escape_string($fecha) . "', 'DD/MM/YYYY')
					ORDER BY
						ch_sucursal,
						ch_tanque;";

		if ($sqlca->query($sql) < 0) 
			return false;

		$resultado = Array();
		$totales = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$ch_sucursal = $a[0];
			$ch_tanque = $a[1];
			$nu_medicion = $a[2];
			$nu_capacidad = ExistenciasModel::obtenerCapacidadTanque($ch_sucursal, $ch_tanque);
			$producto = ExistenciasModel::obtenerProductoTanque($ch_sucursal, $ch_tanque);
			$codigo = ExistenciasModel::obtenerCodigoProductoTanque($ch_sucursal, $ch_tanque);
			$nombre = ExistenciasModel::obtenerNombreEstacion($ch_sucursal);

			$resultado['sucursales'][$nombre]['productos'][$codigo.'_producto'] = $producto;
			$resultado['sucursales'][$nombre]['productos'][$codigo.'_medicion'] = number_format($nu_medicion, 0, '', ',');
			$resultado['sucursales'][$nombre]['productos'][$codigo.'_capacidad'] = number_format($nu_capacidad, 0, '', ',');
			$resultado['sucursales'][$nombre]['productos'][$codigo.'_porcentaje'] = number_format(($nu_medicion/$nu_capacidad)*100, 0) . "%";
			$resultado['sucursales'][$nombre]['codigo'] = $ch_sucursal;
			$resultado['sucursales'][$nombre]['productos']['tanque_'.$ch_tanque] = $producto;

			$totales[$codigo.'_medicion']  += $nu_medicion;
			$totales[$codigo.'_capacidad'] += $nu_capacidad;

			if($producto != "GLP") {
				$resultado['sucursales'][$nombre]['totales']['medicion'] += $nu_medicion;
				$resultado['sucursales'][$nombre]['totales']['capacidad'] += $nu_capacidad;	    
				$resultado['sucursales'][$nombre]['totales']['porcentaje'] = ($resultado['sucursales'][$nombre]['totales']['medicion']/$resultado['sucursales'][$nombre]['totales']['capacidad'])*100;
			}
		}	

		foreach($resultado['sucursales'] as $nombre => $estacion) {
			$totales['TOTAL_medicion'] += $estacion['totales']['medicion'];
			$totales['TOTAL_capacidad'] += $estacion['totales']['capacidad'];
			$resultado['sucursales'][$nombre]['totales']['medicion'] = number_format($estacion['totales']['medicion'], 0, '', ',');
			$resultado['sucursales'][$nombre]['totales']['capacidad'] = number_format($estacion['totales']['capacidad'], 0, '', ',');
			$resultado['sucursales'][$nombre]['totales']['porcentaje'] = number_format($estacion['totales']['porcentaje'], 0) . "%";
		}

		$totales['84 OCTANOS_porcentaje'] = number_format(($totales['84 OCTANOS_medicion']/$totales['11620301_capacidad'])*100, 0) . "%";
		$totales['84 OCTANOS_medicion'] = number_format($totales['84 OCTANOS_medicion'], 0, '', ',');
		$totales['84 OCTANOS_capacidad'] = number_format($totales['84 OCTANOS_capacidad'], 0, '', ',');
		$totales['90 OCTANOS_porcentaje'] = number_format(($totales['90 OCTANOS_medicion']/$totales['11620302_capacidad'])*100, 0) . "%";
		$totales['90 OCTANOS_medicion'] = number_format($totales['90 OCTANOS_medicion'], 0, '', ',');
		$totales['90 OCTANOS_capacidad'] = number_format($totales['90 OCTANOS_capacidad'], 0, '', ',');
		$totales['95 OCTANOS_porcentaje'] = number_format(($totales['95 OCTANOS_medicion']/$totales['11620305_capacidad'])*100, 0) . "%";
		$totales['95 OCTANOS_medicion'] = number_format($totales['95 OCTANOS_medicion'], 0, '', ',');
		$totales['95 OCTANOS_capacidad'] = number_format($totales['95 OCTANOS_capacidad'], 0, '', ',');
		$totales['97 OCTANOS_porcentaje'] = number_format(($totales['97 OCTANOS_medicion']/$totales['11620303_capacidad'])*100, 0) . "%";
		$totales['97 OCTANOS_medicion'] = number_format($totales['97 OCTANOS_medicion'], 0, '', ',');
		$totales['97 OCTANOS_capacidad'] = number_format($totales['97 OCTANOS_capacidad'], 0, '', ',');
		$totales['D2 DIESEL_porcentaje'] = number_format(($totales['D2 DIESEL_medicion']/$totales['11620304_capacidad'])*100, 0) . "%";
		$totales['D2 DIESEL_medicion'] = number_format($totales['D2 DIESEL_medicion'], 0, '', ',');
		$totales['D2 DIESEL_capacidad'] = number_format($totales['D2 DIESEL_capacidad'], 0, '', ',');
		$totales['KEROSENE_porcentaje'] = number_format(($totales['KEROSENE_medicion']/$totales['11620306_capacidad'])*100, 0) . "%";
		$totales['KEROSENE_medicion'] = number_format($totales['KEROSENE_medicion'], 0, '', ',');
		$totales['KEROSENE_capacidad'] = number_format($totales['KEROSENE_capacidad'], 0, '', ',');
		$totales['TOTAL_porcentaje'] = number_format(($totales['TOTAL_medicion']/$totales['TOTAL_capacidad'])*100, 0) . "%";
		$totales['TOTAL_medicion'] = number_format($totales['TOTAL_medicion'], 0, '', ',');
		$totales['TOTAL_capacidad'] = number_format($totales['TOTAL_capacidad'], 0, '', ',');
		$totales['GLP_porcentaje'] = number_format(($totales['GLP_medicion']/$totales['11620307_capacidad'])*100, 0) . "%";
		$totales['GLP_medicion'] = number_format($totales['GLP_medicion'], 0, '', ',');
		$totales['GLP_capacidad'] = number_format($totales['GLP_capacidad'], 0, '', ',');

		$resultado['totales'] = $totales;

		return $resultado;
	}
}
