<?php

class VentasxHorasModel extends Model
{

    function obtieneVentas($desde, $hasta, $diasemana, $producto, $lado, $estaciones,$local,$importe, $bResumido)
    {
	global $sqlca;
	
	$propiedad = VentasxHorasModel::obtenerPropiedadAlmacenes();
	$almacenes = VentasxHorasModel::obtieneListaEstaciones();

	$sql = "SELECT  es as ch_sucursal,
        		importe as nu_ventavalor,
        		cantidad as nu_ventagalon,
        		'0.00' nu_afericion,
        		precio as nu_preciogalon,
        		codigo as ch_codigocombustible,
				fecha::DATE as dt_fechaparte,
                        EXTRACT(HOUR FROM fecha),
			Case EXTRACT(DOW FROM fecha)
                        when 0 then 'DOMINGO'
                        when 1 then 'LUNES'
                        when 2 then 'MARTES'
                        when 3 then 'MIERCOLES'
                        when 4 then 'JUEVES'
                        when 5 then 'VIERNES'
                        when 6 then 'SABADO'
                        end as dia ,
                        pump AS lado,
			tipo".
			
		" 
		FROM 	pos_trans". substr($desde,6,4) . substr($desde,3,2) . " ".

		"
		WHERE
			fecha::DATE between to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')  
		    AND importe > 0
		    AND cantidad > 0 
		    ";
	if ($diasemana != "TODOS") {
	    if (pg_escape_string($diasemana) == '7') {
	    	$sql .= "AND EXTRACT(DOW FROM fecha)=0";
	    }
            else {
	    	$sql .= "AND EXTRACT(DOW FROM fecha)=" . pg_escape_string($diasemana) . "";
            }
	}
	if ($producto != "TODOS") {
	    $sql .= " AND codigo='" . pg_escape_string($producto) . "'";
	}
	if ($lado != "TODOS") {
	    $sql .= "AND pump='" . pg_escape_string($lado) . "'";
	}

	if ($estaciones != "TODAS") {
	    $sql .= " AND es='" . pg_escape_string($estaciones) . "'";
	}

	if (!$local) {
	    $sql .= " AND tipo='C'";
	} else {
	    $sql .= " AND tipo='M'";
	}

	$sql .= " 
		ORDER BY
		    ch_sucursal,
		    dt_fechaparte
		;";

	echo $sql;
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();


	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $ch_sucursal = $a[0];

	    // si es que se muestra el importe o la cantidad (galones)
	    if (!$importe){
	    	$nu_ventagalon = $a[1];
	    	$nu_ventavalor = $a[2];
	    }
	    else
	    {
	    	$nu_ventavalor = $a[1];
	    	$nu_ventagalon = $a[2];
	    }

	    $nu_afericion = $a[3];
	    $nu_preciogalon = $a[4];
	    $ch_codigocombustible = $a[5];
	    $dt_fechaparte = $a[6];
	    $dt_horaparte = $a[7];
	    $dt_diaparte = $a[8];

	    $propio = ($propiedad[$ch_sucursal]=='S'?"ESTACION":"OTROS");
	    $ch_sucursal = $almacenes[$ch_sucursal];

	    /* Si no esta resumido, totalizar venta por dia */
	    if (!$bResumido) {
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte][$dt_horaparte] += $nu_ventavalor;

		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['total'] += $nu_ventavalor;
		@$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes'][$dt_fechaparte]['promedio'] += $nu_ventavalor/24;
	    }
	    
	    /* Calcula total por CC */
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales'][$dt_horaparte] += $nu_ventavalor;
	    
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['total'] += $nu_ventavalor;
	    @$result['propiedades'][$propio]['almacenes'][$ch_sucursal]['totales']['promedio'] += $nu_ventavalor/24;

	    /* Calcula total por Grupo */
	    @$result['propiedades'][$propio]['totales'][$dt_horaparte] += $nu_ventavalor;

	    @$result['propiedades'][$propio]['totales']['total'] += $nu_ventavalor;
	    @$result['propiedades'][$propio]['totales']['promedio'] += $nu_ventavalor/24;

	    /* Calcula total General */
	    @$result['totales'][$dt_horaparte] += $nu_ventavalor;
	    @$result['totales']['total'] += $nu_ventavalor;
	    @$result['totales']['promedio'] += $nu_ventavalor/24;

	    /* Calcula total General */
	    @$result['promedio'][$dt_horaparte] += $nu_ventavalor;
	    @$result['promedio']['total'] += $nu_ventavalor;
	    @$result['promedio']['promedio'] += $nu_ventavalor/24;

	    /* Calcula Porcentaje */
	    @$result['porcentaje'][$dt_horaparte] += $nu_ventavalor;
	    @$result['porcentaje']['total'] += $nu_ventavalor;
	    @$result['porcentaje']['promedio'] += $nu_ventavalor/24;
	}

	//$numerodias = count($result['propiedades'][$propio]['almacenes'][$ch_sucursal]['partes']);
        $numerodias = substr($hasta,0,2) - substr($desde,0,2) + 1;

            for($i=0;$i<24;$i++){
	    	@$result['promedio'][$i] = $result['promedio'][$i]/$numerodias;

		@$result['porcentaje'][$i] = $result['porcentaje'][$i]*100/$result['porcentaje']['total'];
            }
	    @$result['promedio']['total'] = $result['promedio']['total']/$numerodias;
	    @$result['promedio']['promedio'] = $result['promedio']['promedio']/$numerodias;

	    @$result['porcentaje']['total'] = '100';
	    @$result['porcentaje']['promedio'] = ' ';

	return $result;	
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

    function obtieneDiasSemana(){
	$diasemana = Array();
	$diasemana[1] = "LUNES";
	$diasemana[2] = "MARTES";
	$diasemana[3] = "MIERCOLES";
	$diasemana[4] = "JUEVES";
	$diasemana[5] = "VIERNES";
	$diasemana[6] = "SABADO";
	$diasemana[7] = "DOMINGO";
	return $diasemana;
    }
}

