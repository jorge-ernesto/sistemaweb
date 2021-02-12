<?php

class ConsumoValesDiasModel extends Model{

    function obtieneVentas($desde, $hasta, $estaciones, $producto, $cliente, $bResumido){
		global $sqlca;
	
		$propiedad = ConsumoValesDiasModel::obtenerPropiedadAlmacenes();
		$almacenes = ConsumoValesDiasModel::obtieneListaEstaciones();

		$sql = "
			SELECT	
				cab.ch_sucursal	 as sucursal,
				cab.ch_cliente codcliente,
				cli.cli_razsocial nomcliente,
				pf.numtar as TARJETA,
				split_part(pf.nomusu, ' ', 1) as UNIDAD,
				SUBSTRING(pf.nomusu FROM '[^ ]* (.*)') AS SUBUNIDAD,
				cab.ch_placa as PLACA,
				SUM(det.nu_cantidad) as CANTIDAD,
				cab.dt_fecha as FECHA,
				EXTRACT(DAY FROM cab.dt_fecha),
				(SELECT 
				COUNT(DISTINCT cab.ch_placa) 
				from val_ta_cabecera cab 
				LEFT JOIN val_ta_detalle det ON (cab.ch_sucursal = det.ch_sucursal AND cab.ch_documento = det.ch_documento AND cab.dt_fecha = det.dt_fecha)
				where cab.dt_fecha::DATE BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') 
				AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";
				if ($producto != "TODOS") {
    			$sql .= " AND det.ch_articulo='" . pg_escape_string($producto) . "'";
				}

				if ($estaciones != "TODAS") {
				    $sql .= " AND cab.ch_sucursal ='" . pg_escape_string($estaciones) . "'";
				}
			
				if ($cliente != '') {
				    $sql .= " AND cab.ch_cliente LIKE '%" . pg_escape_string($cliente) . "%'";
				}
				$sql .= ") as contador,
				pf.nu_limite_galones AS GALONES,
				(SELECT SUM(nu_limite_galones) AS SUMGALONES FROM
					(
					SELECT DISTINCT(cab.ch_placa), pf.nu_limite_galones
					FROM
					val_ta_cabecera cab
					LEFT JOIN val_ta_detalle det ON (cab.ch_sucursal = det.ch_sucursal AND cab.ch_documento = det.ch_documento AND cab.dt_fecha = det.dt_fecha)
					LEFT JOIN val_ta_complemento com ON (cab.ch_sucursal = com.ch_sucursal AND cab.ch_documento = com.ch_documento AND cab.dt_fecha = com.dt_fecha)
					LEFT JOIN val_ta_complemento_documento fac ON (fac.art_codigo = det.ch_articulo AND fac.ch_numeval = cab.ch_documento AND fac.ch_cliente = cab.ch_cliente AND fac.ch_sucursal = cab.ch_sucursal AND fac.dt_fecha = cab.dt_fecha)
					LEFT JOIN pos_fptshe1 pf ON (pf.numtar = cab.ch_tarjeta)
					LEFT JOIN inv_ta_almacenes alma ON (cab.ch_sucursal = alma.ch_almacen)
			  		LEFT JOIN int_clientes cli ON (cli.cli_codigo = cab.ch_cliente)
					WHERE
					cab.dt_fecha::DATE BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') 
					AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')";
					if ($producto != "TODOS") {
    				$sql .= " AND det.ch_articulo='" . pg_escape_string($producto) . "'";
					}

					if ($estaciones != "TODAS") {
					$sql .= " AND cab.ch_sucursal ='" . pg_escape_string($estaciones) . "'";
					}
				
					if ($cliente != '') {
				    $sql .= " AND cab.ch_cliente LIKE '%" . pg_escape_string($cliente) . "%'";
					}
					$sql .= " 
					GROUP BY
					cab.dt_fecha,
					pf.numtar,
					cab.ch_placa,
					cab.ch_cliente,
					cli.cli_razsocial,
					pf.nomusu,
					cab.ch_sucursal,
					det.ch_articulo,
					pf.nu_limite_galones
 					)AS T1
				)AS GALONESTOT
			FROM
				val_ta_cabecera cab
				LEFT JOIN val_ta_detalle det ON (cab.ch_sucursal = det.ch_sucursal AND cab.ch_documento = det.ch_documento AND cab.dt_fecha = det.dt_fecha)
				LEFT JOIN val_ta_complemento com ON (cab.ch_sucursal = com.ch_sucursal AND cab.ch_documento = com.ch_documento AND cab.dt_fecha = com.dt_fecha)
				LEFT JOIN val_ta_complemento_documento fac ON (fac.art_codigo = det.ch_articulo AND fac.ch_numeval = cab.ch_documento AND fac.ch_cliente = cab.ch_cliente AND fac.ch_sucursal = cab.ch_sucursal AND fac.dt_fecha = cab.dt_fecha)
				LEFT JOIN pos_fptshe1 pf ON (pf.numtar = cab.ch_tarjeta)
				LEFT JOIN inv_ta_almacenes alma ON (cab.ch_sucursal = alma.ch_almacen)
			  	LEFT JOIN int_clientes cli ON (cli.cli_codigo = cab.ch_cliente)
			WHERE
				cab.dt_fecha::DATE BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		";

		if ($producto != "TODOS") {
		    $sql .= " AND det.ch_articulo='" . pg_escape_string($producto) . "'";
		}

		if ($estaciones != "TODAS") {
		    $sql .= " AND cab.ch_sucursal ='" . pg_escape_string($estaciones) . "'";
		}

		if ($cliente != '') {
		    $sql .= " AND cab.ch_cliente LIKE '%" . pg_escape_string($cliente) . "%'";
		}
		$sql .= " 
			GROUP BY
				cab.dt_fecha,
				pf.numtar,
				cab.ch_placa,
				cab.ch_cliente,
				cli.cli_razsocial,
				pf.nomusu,
				cab.ch_sucursal,
				det.ch_articulo,
				pf.nu_limite_galones
			ORDER BY
				cli.cli_razsocial,
				cab.dt_fecha,
				pf.numtar;
		";

		//echo $sql;

		if ($sqlca->query($sql) < 0) return false;
		
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    
		    $ch_sucursal = $a[0];

		    // si es que se muestra el importe o la cantidad (galones)
		    $nu_ventagalon = $a[7];
		    $nu_ventavalor = $a[7];
		    $dt_fechaparte = $a[6];
		    $dt_horaparte = $a[9];
		    $subunidad = $a[5];
		    $unidad = $a[4];
		    $placa = $a[6];
		    $contador = $a[10];
		    $asignado = $a[11];
		    $totalasignado = $a[12];

		    $propio = "ESTACION";
		    $ch_sucursal = $almacenes[$ch_sucursal];

		    /* Si no esta resumido, totalizar venta por dia */
		    if (!$bResumido) {
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte][$dt_horaparte] += $nu_ventavalor;

				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total'] += $nu_ventavalor;
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['unidad'] = $unidad;
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['subunidad'] = $subunidad;
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['placa'] = $placa;
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['contador'] = $contador;
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['asignado'] = $asignado;
				@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['saldo'] = $asignado - $result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total'];
		    }
		
		    /* Calcula total por CC */
		    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales'][$dt_horaparte] += $nu_ventavalor;
		    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total'] += $nu_ventavalor;

		    /* Calcula total por Grupo */
		    @$result['propiedades'][$propio]['totales'][$dt_horaparte] += $nu_ventavalor;

		    @$result['propiedades'][$propio]['totales']['total'] += $nu_ventavalor;

		    /* Calcula total General */
		    @$result['totales'][$dt_horaparte] += $nu_ventavalor;
		    @$result['totales']['total'] += $nu_ventavalor;

		   	@$result['totales']['contador'] = $contador;
		  
		   	@$result['totales']['asignado'] = $totalasignado ;

		   	@$result['totales']['totalfinal'] = $result['totales']['asignado'] - $result['totales']['total'] ;
		}
		return $result;	
    }

    function obtenerPropiedadAlmacenes(){
		global $sqlca;
	
		$sql = "
		SELECT
		    ch_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen = '1';
		";

		if ($sqlca->query($sql) < 0) return false;
		
		$result = Array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    
		    $result[$a[0]] = $a[1];
		}
		
		return $result;
    }
    
    function obtieneListaEstaciones(){
		global $sqlca;
		
		$sql = "
		SELECT
		    ch_almacen,
		    trim(ch_nombre_almacen)
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		ORDER BY
		    ch_almacen;
		";

		if ($sqlca->query($sql) < 0) return false;
		
		$result = Array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $result[$a[0]] = $a[0] . " - " . $a[1];
		}
		
		return $result;
    }

    function obtieneProductos(){
		global $sqlca;
		
		$sql = "
		SELECT DISTINCT
			f.product,
			ch_nombrecombustible
		FROM
			f_grade f
			LEFT JOIN comb_ta_combustibles c ON (c.ch_codigocombustible = f.product)
		ORDER BY
			f.product;
		";

		if ($sqlca->query($sql) < 0) return false;
		
		$producto = Array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
		    $producto[$a[0]] = $a[1];
		}
		return $producto;
    } 
}

