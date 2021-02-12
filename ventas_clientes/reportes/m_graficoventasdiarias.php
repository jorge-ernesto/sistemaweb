<?php

class GraficoVentasDiariasModel extends Model
{

    function obtieneVentas($desde, $hasta, $estaciones)
    {
	global $sqlca;
	
	$propiedad = GraficoVentasDiariasModel::obtenerPropiedadAlmacenes();
	$almacenes = GraficoVentasDiariasModel::obtieneListaEstaciones();

	$sql = "SELECT 	to_char(dt_fechaparte, 'DD/MM/YYYY'),
				sum(nu_ventavalor),
				ch_sucursal
			FROM
		    		comb_ta_contometros
			WHERE
				    dt_fechaparte>=to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')
		    		AND dt_fechaparte<=to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		    		AND nu_ventavalor > 0
		    		AND nu_ventagalon > 0 
		    ";
	if ($estaciones != "TODAS") {
	    $sql .= " AND ch_sucursal='" . pg_escape_string($estaciones) . "'";
	}
	
	$sql .= "
		group by 
			dt_fechaparte,ch_sucursal
		ORDER BY
		    ch_sucursal,
		    dt_fechaparte
		;";
	$rs_diario = pg_exec($sql);
	echo $sql;
	return $rs_diario;	
    }

    function obtenerPropiedadAlmacenes()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_almacen,
		    'S' AS ch_almacen_propio
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[$a[0]] = $a[1];
	}
	
	return $result;
    }    
    
    function obtieneListaEstaciones()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_almacen,
		    trim(ch_nombre_almacen)
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		ORDER BY
		    ch_almacen
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $result[$a[0]] = $a[0] . " - " . $a[1];
	}
	
	return $result;
    }
}

