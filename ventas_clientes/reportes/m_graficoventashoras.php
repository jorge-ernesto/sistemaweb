<?php

class GraficoVentasHorasModel extends Model
{

    function obtieneVentas($desde, $hasta, $estaciones,$tipo,$dia,$producto,$lado)
    {
	global $sqlca;

	$propiedad = GraficoVentasHorasModel::obtenerPropiedadAlmacenes();
	$almacenes = GraficoVentasHorasModel::obtieneListaEstaciones();

	$sql = "SELECT  	to_char(fecha::DATE, 'DD/MM/YYYY') as dt_fechaparte,
				sum(importe) as nu_ventavalor,
				EXTRACT(HOUR FROM fecha),
				es as ch_sucursal,
				Case EXTRACT(DOW FROM fecha::DATE)
				        when 0 then 'DOMINGO'
				        when 1 then 'LUNES'
				        when 2 then 'MARTES'
				        when 3 then 'MIERCOLES'
				        when 4 then 'JUEVES'
				        when 5 then 'VIERNES'
				        when 6 then 'SABADO'
		                end as dia 
		FROM 		pos_trans". substr($desde,6,4) . substr($desde,3,2) . "  
		WHERE		fecha::DATE between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') 
		AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')  
		AND importe > 0
		AND cantidad > 0 
		"; 

	if ($estaciones != "TODAS") {
	    $sql .= " AND es='" . pg_escape_string($estaciones) . "'";
	}

	if ($dia != "TODAS") {
	    $sql .= " AND EXTRACT(DOW FROM fecha)=" . pg_escape_string($dia) . "";
	}

	if ($tipo != "TODAS") {
	    $sql .= " AND tipo='" . pg_escape_string($tipo) . "'";
	}

	if ($producto != "TODAS") {
	    $sql .= " AND codigo='" . pg_escape_string($producto) . "'";
	}
	if ($lado != "TODAS") {
	    $sql .= "AND pump='" . pg_escape_string($lado) . "'";
	}

	$sql .= "
		GROUP BY 	EXTRACT(HOUR FROM fecha),fecha::DATE,es
		ORDER BY	ch_sucursal,fecha::DATE,EXTRACT(HOUR FROM fecha)
		;";
	$rs_hora = pg_exec($sql);
	echo $sql;
	return $rs_hora;	
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

    function obtieneTipos()
    {
	$result = Array();
	$result['C'] = 'Combustible';
	$result['M'] = 'Market';
	
	return $result;
    }

    function obtieneDias()
    {
	$result = Array();
	$result['0'] = 'Domingo';
	$result['1'] = 'Lunes';
	$result['2'] = 'Martes';
	$result['3'] = 'Miercoles';
	$result['4'] = 'Jueves';
	$result['5'] = 'Viernes';
	$result['6'] = 'Sabado';
	
	return $result;
    }

    function obtieneLados(){
	global $sqlca;
	
	$sql = "SELECT
		    lado
		FROM
		    pos_cmblados 
		ORDER BY
		    lado
		;";
	if ($sqlca->query($sql) < 0) return false;
	
	$producto = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $lado[$a[0]] = $a[0];
	}
	
	return $lado;
    }

    function obtieneProductos(){
	global $sqlca;
	
	$sql = "SELECT
		    art_codigo,
		    trim(art_descbreve)
		FROM
		    int_articulos 
		WHERE 
		    art_descbreve<>'' 
		ORDER BY
		    art_descbreve
		;";
	if ($sqlca->query($sql) < 0) return false;
	
	$producto = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $producto[$a[0]] = $a[1];
	}
	
	return $producto;
    }
}

?>
