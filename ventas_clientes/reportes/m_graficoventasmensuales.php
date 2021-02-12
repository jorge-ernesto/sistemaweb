<?php

class GraficoVentasMensualesModel extends Model
{

    function obtieneVentas($desde, $hasta, $estaciones)
    {
	global $sqlca;
	
	$propiedad = GraficoVentasMensualesModel::obtenerPropiedadAlmacenes();
	$almacenes = GraficoVentasMensualesModel::obtieneListaEstaciones();

	$sql = "SELECT  Case 
		When date_part('month', dt_fechaparte)='1' then 'Enero ' || date_part('year', dt_fechaparte) 
		When date_part('month', dt_fechaparte)='2' then 'Febrero ' || date_part('year', dt_fechaparte) 
		When date_part('month', dt_fechaparte)='3' then 'Marzo ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='4' then 'Abril ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='5' then 'Mayo ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='6' then 'Junio ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='7' then 'Julio ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='8' then 'Agosto ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='9' then 'Setiembre ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='10' then 'Octubre ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='11' then 'Noviembre ' || date_part('year', dt_fechaparte)  
		When date_part('month', dt_fechaparte)='12' then 'Diciembre ' || date_part('year', dt_fechaparte)  
	end,
	
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
		group by date_part('month', dt_fechaparte),date_part('year', dt_fechaparte),ch_sucursal
		order by ch_sucursal,date_part('year', dt_fechaparte),date_part('month', dt_fechaparte)
		;";
	$rs_mensual = pg_exec($sql);
	echo $sql;
	return $rs_mensual;	
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

