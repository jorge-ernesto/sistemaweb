<?php

class VentasOficialModel extends Model
{
    function obtenerEstaciones()
    {
	global $sqlca;
	
	$sql = "SELECT
		    trim(ch_almacen),
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
	    
	    $result[$a[0]] = $a[1];
	}
	
	return $result;
    }

    function obtenerArrayMarket()
    {
	return array(
        	    'fecha'             =>      '',
                    'combustibles'      =>      '0.00',
                    'lubricantes'       =>      '0.00',
                    'accesorios'        =>      '0.00',
                    'servicios'         =>      '0.00',
                    'market'            =>      '0.00',
                    'whiz'              =>      '0.00',
                    'ob'                =>      '0.00',
                    'otros'             =>      '0.00',
                    'anticipos'         =>      '0.00',
                    'neto'              =>      '0.00',
                    'impuestos'         =>      '0.00',
                    'total'             =>      '0.00'
                    );
    }

    function obtenerArrayCombustibles()
    {
	return array(
                    'fecha'     	=>      '',
                    '11620301_cantidad' =>      '0.00',
                    '11620301_importe'  =>      '0.00',
                    '11620302_cantidad' =>      '0.00',
                    '11620302_importe'  =>      '0.00',
                    '11620303_cantidad' =>      '0.00',
                    '11620303_importe'  =>      '0.00',
                    '11620304_cantidad' =>      '0.00',
                    '11620304_importe'  =>      '0.00',
                    '11620305_cantidad' =>      '0.00',
                    '11620305_importe'  =>      '0.00',
                    '11620306_cantidad' =>      '0.00',
                    '11620306_importe'  =>      '0.00',
                    '11620307_cantidad' =>      '0.00',
                    '11620307_importe'  =>      '0.00',
                    'total_cantidad'    =>      '0.00',
                    'total_importe'     =>      '0.00',
                    'neto'              =>      '0.00'
                    );
    }

    function agregarEstacionMarket($desde_dia, $hasta_dia, $mes)
    {
	$estacion = array();
	for($i = $desde_dia; $i <= $hasta_dia; $i++) {
	    $fecha = str_pad($i, 2, '0', STR_PAD_LEFT)."/".$mes;
	    $estacion['dias'][$fecha]['detalle'] = VentasOficialModel::obtenerArrayMarket();
	    $estacion['dias'][$fecha]['detalle']['fecha'] = $fecha;
	}
	$estacion['totales'] = VentasOficialModel::obtenerArrayMarket();
	return $estacion;
    }

    function agregarEstacionCombustible($desde_dia, $hasta_dia, $mes)
    {
	$estacion = array();
	for($i = $desde_dia; $i <= $hasta_dia; $i++) {
	    $fecha = str_pad($i, 2, '0', STR_PAD_LEFT)."/".$mes;
	    $estacion['dias'][$fecha]['detalle'] = VentasOficialModel::obtenerArrayCombustibles();
	    $estacion['dias'][$fecha]['detalle']['fecha'] = $fecha;
	}
	$estacion['totales'] = VentasOficialModel::obtenerArrayCombustibles();
	return $estacion;
    }
    
    function reporte($desde, $hasta, $modo, $tipo_cliente, $descontar, $estacion)
    {
	global $sqlca;

	/* prepara datos para busqueda por fecha */
	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");
	
	/* Verifica restricciones de fecha: desde y hasta deben estar en el mismo mes y anio */
	/* esto se debe a que la logica para saltar de mes y anio debido a como esta estructurado
	   pos_transYYYYMM hace muy compleja la logica requerida y no vale la pena debido a que 
	   ese caso es muy raro (por no decir imposible) */
	if ($desde_mes != $hasta_mes || $desde_ano != $hasta_ano) return false;

	/* prepara el string de fecha tal cual lo necesita postgres */
	$desde = pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia." 00:00:00");
	$hasta = pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia." 23:59:59");

	$ventas = array();
	$ventas['totales'] = VentasOficialModel::obtenerArrayMarket();

	/*-----------------------> Parte 1: Venta de combustibles al contado <---------------------------*/
	if ($tipo_cliente == "CONTADO" || $tipo_cliente == "AMBAS") {
	
	    /* Nota: este query ha sido tomado de ventas diarias casi sin modificacion. */
	    $sql = "SELECT
				ch_sucursal as es,
		        nu_ventavalor + nu_descuentos as total,
		        nu_ventagalon as cantidad,
		        round(1+(util_fn_igv_porarticulo(ch_codigocombustible)/100), 2) as factor_igv,
	    	        (nu_afericionveces_x_5*5) as afericion,
		        (round((nu_ventavalor/nu_ventagalon), 2)) as precio,
		        ch_codigocombustible as codigo,
		        to_char(dt_fechaparte, 'dd/mm') as dia
		    FROM
	    		comb_ta_contometros
		    WHERE
			    dt_fechaparte BETWEEN '" . pg_escape_string($desde_ano.'-'.$desde_mes.'-'.$desde_dia) . "' AND '" . pg_escape_string($hasta_ano.'-'.$hasta_mes.'-'.$hasta_dia) . "'
			AND nu_ventavalor > 0
	    	        AND nu_ventagalon > 0
    		    ";
	    if ($estacion != "TODAS") {
    		$sql .= "AND ch_sucursal='" . pg_escape_string($estacion) . "'";
	    }

	    $sql .= "
		    ORDER BY
			ch_sucursal,
	    	        dt_fechaparte,
		        ch_numeroparte,
	    		ch_surtidor;";
	    echo $sql;
	    if ($sqlca->query($sql) < 0) {
			return false;
	    }
		//print_r($desde_dia.'m:'.$desde_mes.'h:'.$hasta_dia);
	    for($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
	        $es = $a['es'];
	        $total = $a['total'];
	        $cantidad = $a['cantidad'];
	        $factor_igv = $a['factor_igv'];
	        $afericion = $a['afericion'];
	        $precio = $a['precio'];
	        $codigo = $a['codigo'];
	        $dia = $a['dia'];
	    
	        /* Descuenta afericiones y actualiza totales en valor con la nueva venta. Eso
		 * se hace debido a que las afericiones no pagan impuestos debido a que son
		 * despachos de prueba y no son venta.
		 */
    	    $cantidad -= $afericion;
	        $total -= ($afericion*$precio);
	        $neto = round($total/$factor_igv, 2);
	        $igv = round($total-$neto, 2);
		
		/* El modelo de este reporte exige que existan todos los dias en el rango especificado,
		 * aunque sean llenos de ceros. Aqui verificamos si existe el dia indicado y no existiera,
		 * lo creamos lleno de ceros.
		 */
		if (!isset($ventas['combustibles']['estaciones'][$es])) {
		
		    $ventas['combustibles']['estaciones'][$es] = VentasOficialModel::agregarEstacionCombustible($desde_dia, $hasta_dia, $desde_mes);
		}

	        /* Calcula totales por centro de costo */
	        $ventas['combustibles']['estaciones'][$es]['totales'][$codigo.'_cantidad'] += $cantidad;
	        $ventas['combustibles']['estaciones'][$es]['totales'][$codigo.'_importe'] += $neto;
	        $ventas['combustibles']['estaciones'][$es]['totales']['neto'] += $neto;
	        $ventas['combustibles']['estaciones'][$es]['totales']['impuestos'] += $igv;
    	    if ($codigo !='11620307') { $ventas['combustibles']['estaciones'][$es]['totales']['total_cantidad'] += $cantidad; $ventas['combustibles']['estaciones'][$es]['totales']['total_importe'] += $neto; }

			/* Calcula totales por combustibles */	    
	        $ventas['combustibles']['totales'][$codigo.'_cantidad'] += $cantidad;
	        $ventas['combustibles']['totales'][$codigo.'_importe'] += $neto;
	        $ventas['combustibles']['totales']['neto'] += $neto;
	        $ventas['combustibles']['totales']['impuestos'] += $igv;
	        if ($codigo !='11620307') { $ventas['combustibles']['totales']['total_cantidad'] += $cantidad; $ventas['combustibles']['totales']['total_importe'] += $neto; }
	    
	        /* Calcula dia */
	        $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['fecha'] = $dia;
	        $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_cantidad'] += $cantidad;
	        $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_importe'] += $neto;
	        $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] += $neto;
	        $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] += $igv;
	        if ($codigo !='11620307') { $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['total_cantidad'] += $cantidad; $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['total_importe'] += $neto; }
	    }
//var_dump($ventas);
	    /* Parte 1b: Obtiene los numeros de tickets por cada punto de combustibles */
	    $sql = "SELECT 
			(
			    SELECT
				CASE WHEN min(m.trans)=1 AND max(m.trans)=9999 THEN (SELECT m3.trans FROM pos_trans" . pg_escape_string($desde_ano.$desde_mes) . " m3 WHERE m3.dia=pt.dia AND m3.caja=pt.caja AND m3.es=pt.es ORDER BY m3.fecha ASC LIMIT 1) ELSE min(m.trans) END
			    FROM 
				pos_trans" . pg_escape_string($desde_ano.$desde_mes) . " m 
			    WHERE 
				m.dia=pt.dia 
				AND m.caja=pt.caja 
				AND m.es=pt.es 
			) AS min, 
			(
			    SELECT 
				CASE WHEN min(m2.trans)=1 AND max(m2.trans)=9999 THEN (SELECT m3.trans FROM pos_trans" . pg_escape_string($desde_ano.$desde_mes) . " m3 WHERE m3.dia=pt.dia AND m3.caja=pt.caja AND m3.es=pt.es ORDER BY m3.fecha DESC LIMIT 1) ELSE max(m2.trans) END
			    FROM 
				pos_trans" . pg_escape_string($desde_ano.$desde_mes) . " m2 
			    WHERE 
				m2.dia=pt.dia 
				AND m2.caja=pt.caja 
				AND m2.es=pt.es
			) AS max,
			pt.caja,
			to_char(pt.dia, 'dd/mm') as dia,
			pt.es
		    FROM 
			pos_trans" . pg_escape_string($desde_ano.$desde_mes) . " pt 
		    WHERE 
			pt.dia BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."' ";
	    if ($estacion != 'TODAS') {
		$sql .= "AND pt.es='" . pg_escape_string($estacion) . "' ";
	    }
	    $sql .= "	
		    GROUP BY 
			pt.dia, 
			pt.caja, 
			pt.es
		";
		echo $sql;
	    if ($sqlca->query($sql) < 0) {
		return false;
	    }

	    for($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
	        $min = $a['min'];
	        $max = $a['max'];
	        $caja = $a['caja'];
	        $dia = $a['dia'];
	        $es = $a['es'];
	    
	        $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['tickets'] .= str_pad($caja, 2, '0', STR_PAD_LEFT) . '-' . str_pad($min, 8, '0', STR_PAD_LEFT) . ' AL ' . str_pad($max, 8, '0', STR_PAD_LEFT) . ' ';
	    }
	}
	/*-----------------> Fin de parte 1 <------------------------*/
	//print_r($ventas);
	/************************ Parte 2: Venta de Market al contado *************************************/
	if ($tipo_cliente == "CONTADO" || $tipo_cliente == "AMBAS") {
	    /* Parte 2a: arrastrar totales de combustibles a Market */
	    foreach($ventas['combustibles']['estaciones'] as $es=>$est) {
		$ventas['market']['estaciones'][$es] = VentasOficialModel::agregarEstacionMarket($desde_dia, $hasta_dia, $desde_mes);

		foreach($est['dias'] as $fecha=>$dia) {
		    $neto = $dia['detalle']['neto'];
		    $igv = $dia['detalle']['impuestos'];
			//if ($neto>0){
				//print_r($neto.'igv:'.$igv.'/n');
			//}
		    /* Pone fecha */
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['fecha'] = $fecha;
		
		    /* Transporta total por dia */
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['combustibles'] = $neto;
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['neto'] = $neto;
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['impuestos'] = $igv;
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['total'] = $neto+$igv;
		}
	    
		$neto = $est['totales']['neto'];
	        $igv = $est['totales']['impuestos'];
	    
		/* Transporta total por CC */
	        $ventas['market']['estaciones'][$es]['totales']['combustibles'] = $neto;
	        $ventas['market']['estaciones'][$es]['totales']['neto'] = $neto;
	        $ventas['market']['estaciones'][$es]['totales']['impuestos'] = $igv;
	        $ventas['market']['estaciones'][$es]['totales']['total'] = $neto+$igv;
	    }
	    //print_r('Entro aqui');
	    $neto = $ventas['combustibles']['totales']['neto'];
	    $igv = $ventas['combustibles']['totales']['impuestos'];
	
	    /* Transporta el total de combustibles */
	    $ventas['market']['totales']['combustibles'] = $neto;
	    $ventas['market']['totales']['neto'] = $neto;
	    $ventas['market']['totales']['impuestos'] = $igv;
	    $ventas['market']['totales']['total'] = $neto+$igv;

	    /* Parte 2b: calcular venta de market por tipos */
    	    $sql = "SELECT
            		sum(d.nu_fac_valortotal) as total,
		        sum(d.nu_fac_importeneto) as neto,
		        sum(d.nu_fac_valortotal-d.nu_fac_importeneto) as igv,
		        trim(art.art_tipo) as tipo,
		        to_char(c.dt_fac_fecha, 'dd/mm') as dia,
		        c.ch_punto_venta as es
		    FROM
			fac_ta_factura_cabecera c,
		        fac_ta_factura_detalle d,
		        int_articulos art
		    WHERE
			    c.dt_fac_fecha BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
			AND  c.ch_fac_tipodocumento=d.ch_fac_tipodocumento
			AND c.ch_fac_seriedocumento=d.ch_fac_seriedocumento
		        AND c.ch_fac_numerodocumento=d.ch_fac_numerodocumento
		        AND c.cli_codigo=d.cli_codigo
		        AND art.art_codigo = d.art_codigo
		        ";
	    if ($estacion != 'TODAS') {
	        $sql .= "AND c.ch_almacen='" . pg_escape_string($estacion) . "' ";
	    }
	       /* AND c.ch_punto_venta!='001'*/
	    $sql .= " AND c.ch_fac_tipodocumento='45'
		     GROUP BY
        	        c.ch_punto_venta,
                	c.dt_fac_fecha,
            	        art.art_tipo
        	    ORDER BY
                	c.ch_punto_venta,
            		c.dt_fac_fecha,
        		art.art_tipo
		    ;
		    ";
	    if ($sqlca->query($sql) < 0) {
	        return false;
	    }

	    for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
	    
		$total = $a['total'];
	        $neto = $a['neto'];
	        $igv = $a['igv'];
	        $tipo = $a['tipo'];
	        $dia = $a['dia'];
	        $es = $a['es'];
	    
		/* Ver nota en la parte 1 para la explicacion de esta linea */
		if (!isset($ventas['market']['estaciones'][$es])) {
		    $ventas['market']['estaciones'][$es] = VentasOficialModel::agregarEstacionMarket($desde_dia, $hasta_dia, $desde_mes);
		}

		switch ($tipo) {
		    case '02':
		        $tipo = "lubricantes";
		        break;
		    case '03':
		        $tipo = "accesorios";
		        break;
		    case '06':
		        $tipo = "servicios";
		        break;
		    case '05':
		        $tipo = "market";
		        break;
		    case '08':
		        $tipo = "market";
		        break;
		    case '09':
		        $tipo = "whiz";
		        break;
		    case '10':
		        $tipo = "ob";
		        break;
		    default:
		        $tipo = "otros";
		        break;
		}
	    
		/* Calcula ventas de dia actual */
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle'][$tipo] += $neto;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] += $neto;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] += $igv;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['total'] += $total;
	    
	        /* Calcula total por CC */
	        $ventas['market']['estaciones'][$es]['totales'][$tipo] += $neto;
	        $ventas['market']['estaciones'][$es]['totales']['neto'] += $neto;
	        $ventas['market']['estaciones'][$es]['totales']['impuestos'] += $igv;
	        $ventas['market']['estaciones'][$es]['totales']['total'] += $total;
	    
	        /* Calcula total general */
	        $ventas['market']['totales'][$tipo] += $neto;
	        $ventas['market']['totales']['neto'] += $neto;
	        $ventas['market']['totales']['impuestos'] += $igv;
	        $ventas['market']['totales']['total'] += $total;
	    
	    }
	}
	/***********************> Fin de parte 2 <**************************/
	
	/*----------------------> Parte 3: Descuento de vales, de ser el caso <------------------*/
	
	/* Descuenta vales de credito si se ha pedido explicitamente eso o si solo se ha pedido la venta al contado */
	
	if (($descontar == 'S' && $tipo_cliente != 'CREDITO') || $tipo_cliente == "CONTADO") {
	//if (!($tipo_cliente=='CONTADO' && $descontar=='')){
	    $sql = "SELECT
			to_char(det.dt_fecha, 'dd/mm') as dia,
			ch_sucursal as es,
			trim(art.art_tipo) as tipo,
			trim(det.ch_articulo) as codigo,
			sum(det.nu_cantidad) as cantidad,
			sum(det.nu_importe) as total,
			round(1+(util_fn_igv_porarticulo(det.ch_articulo)/100), 2) as factor_igv
		    FROM
			val_ta_detalle det,
			int_articulos art
		    WHERE det.dt_fecha BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
		    ";
	    if ($estacion != 'TODAS') {
		$sql .= "AND det.ch_sucursal='" . pg_escape_string($estacion) . "' ";
	    }
	    $sql .= "
			AND art.art_codigo=det.ch_articulo
		    GROUP BY
			art.art_tipo,
		        det.ch_articulo,
			det.dt_fecha,
			det.ch_sucursal
		    ORDER BY
			det.ch_sucursal,
			det.dt_fecha,
			art.art_tipo,
			det.ch_articulo;
		    ";
	    if ($sqlca->query($sql) < 0) {
		return false;
	    }
	    
	    for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
		$dia = $a['dia'];
		$es = $a['es'];
		$tipo = $a['tipo'];
		$codigo = $a['codigo'];
		$cantidad = $a['cantidad'];
		$total = is_null($a['total'])?0:$a['total'];
		$factor_igv = $a['factor_igv'];
		$neto = round($total/$factor_igv, 2);
		$igv = round($total-$neto, 2);
		$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['fecha'] = $dia;
		/* Combustibles */

		switch ($tipo) {
		    case '01':
			$bCombustibles = true;
			break;
		    case '02':
		        $tipo = "lubricantes";
			$bCombustibles = false;
		        break;
		    case '03':
		        $tipo = "accesorios";
			$bCombustibles = false;		        
			break;
		    case '04':
			$bCombustibles = true;
			break;
		    case '06':
		        $tipo = "servicios";
			$bCombustibles = false;
		        break;
		    case '05':
		        $tipo = "market";
			$bCombustibles = false;
		        break;
		    case '08':
		        $tipo = "market";
			$bCombustibles = false;
		        break;
		    case '09':
		        $tipo = "whiz";
			$bCombustibles = false;
		        break;
		    case '10':
		        $tipo = "ob";
			$bCombustibles = false;
		        break;
		    default:
		        $tipo = "otros";
			$bCombustibles = false;
		        break;
		}

		if ($bCombustibles) {
		    /* Descuenta en dia */
		    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_cantidad'] -= $cantidad;
		    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_importe'] -= $neto;
		    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] -= $neto;
		    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] -= $igv;
		    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['total_cantidad'] -= $cantidad;
		    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['total_importe'] -= $neto;
		    
		    /* Descuenta total por CC */
		    $ventas['combustibles']['estaciones'][$es]['totales'][$codigo.'_cantidad'] -= $cantidad;
		    $ventas['combustibles']['estaciones'][$es]['totales'][$codigo.'_importe'] -= $neto;
		    $ventas['combustibles']['estaciones'][$es]['totales']['neto'] -= $neto;
		    $ventas['combustibles']['estaciones'][$es]['totales']['impuestos'] -= $igv;
		    $ventas['combustibles']['estaciones'][$es]['totales']['total_cantidad'] -= $cantidad;
    	    	    $ventas['combustibles']['estaciones'][$es]['totales']['total_importe'] -= $neto;
		    
		    /* Descuenta total de combustibles */
		    $ventas['combustibles']['totales'][$codigo.'_cantidad'] -= $cantidad;
		    $ventas['combustibles']['totales'][$codigo.'_importe'] -= $neto;
		    $ventas['combustibles']['totales']['neto'] -= $neto;
		    $ventas['combustibles']['totales']['impuestos'] -= $igv;
	    	    $ventas['combustibles']['totales']['total_cantidad'] -= $cantidad;
		    $ventas['combustibles']['totales']['total_importe'] -= $neto;

		    /* Descuenta lo de market */
		    $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['combustibles'] -= $neto;
		    $ventas['market']['estaciones'][$es]['totales']['combustibles'] -= $neto;
		    $ventas['market']['totales']['combustibles'] -= $neto;
		
		}
		else {
		    /* Descuento de market */
	    	    $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle'][$tipo] -= $neto;
	    	    $ventas['market']['estaciones'][$es]['totales'][$tipo] -= $neto;
	    	    $ventas['market']['totales'][$tipo] -= $neto;
		}
		    
		/* Descuenta total del dia */
		$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] -= $neto;
		$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] -= $igv;
		$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['total'] -= $total;
		
		/* Descuenta total por CC */
		$ventas['market']['estaciones'][$es]['totales']['neto'] -= $neto;
		$ventas['market']['estaciones'][$es]['totales']['impuestos'] -= $igv;
		$ventas['market']['estaciones'][$es]['totales']['total'] -= $total;
		
		/* Descuenta total por Market */
		$ventas['market']['totales']['neto'] -= $neto;
		$ventas['market']['totales']['impuestos'] -= $igv;
		$ventas['market']['totales']['total'] -= $total;
	    }
	}
	/*-------------------------> Fin de parte 3 <------------------------------*/

	/******************> Parte 4: Listado de documentos Sunat de estaciones <**************************/
	
	//if ($tipo_cliente == "CONTADO" || $tipo_cliente == "AMBAS") {
	    /* Parte 4a: Simple listado de documentos */
	    /*
	     * Esto incluye:
	     * - Facturas de todos excepto serie 001 (Oficina)
	     * - Notas de credito: todos
	     * - Notas de debito: todos
	     * - Boletas de venta: todos
	     *
	     * Esta parte es un poco complicada debido a que el reporte debe contener un total por pagina. 
	     */
	    $sql = "SELECT
			to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
		        trim(tab.tab_car_03) as td,
		        trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        trim(COALESCE(com.ch_fac_ruc, cli.cli_ruc)) AS cli_ruc,
		        trim(COALESCE(com.ch_fac_nombreclie, cli.cli_razsocial)) AS cli_razsocial,
		        cab.nu_tipocambio,
		        cab.nu_fac_valorbruto as neto,
		        (cab.nu_fac_valortotal-cab.nu_fac_valorbruto) as impuestos,
		        cab.nu_fac_valortotal as total,
		        cab.ch_fac_anulado,
				trim(tab.tab_car_03)||' - '||trim(tab.tab_descripcion) as td_descripcion
		    FROM
				fac_ta_factura_cabecera cab
			LEFT JOIN fac_ta_factura_complemento com ON (cab.ch_fac_seriedocumento=com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=com.ch_fac_numerodocumento AND cab.ch_fac_tipodocumento=com.ch_fac_tipodocumento),
		        int_clientes cli,
		        int_tabla_general tab
		    WHERE
			    cab.dt_fac_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . "' and '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . "'
				AND cab.ch_fac_tipodocumento IN ('10', '11', '20', '35')
		    ";
	    if ($estacion != 'TODAS') {
		$sql .= "AND cab.ch_fac_seriedocumento LIKE '%" . pg_escape_string(substr($estacion, 1)) . "' ";
	    }
	    $sql .= "
			AND cli.cli_codigo=cab.cli_codigo
		        AND tab.tab_tabla='08'
		        AND tab.tab_elemento=lpad(cab.ch_fac_tipodocumento, 6, '0')
		    ORDER BY
			cab.ch_fac_tipodocumento,
		        cab.ch_fac_seriedocumento,
		        cab.ch_fac_numerodocumento
		    ;
		    ";
	    if ($sqlca->query($sql) < 0) {
	        return false;
	    }

    	    $old_td = '';
	    $pagina = 1;
            $cuenta = 0;

    	    for($i = 0; $i < $sqlca->numrows(); $i++) {
        	$a = $sqlca->fetchRow();

        	$fecha = $a['fecha'];
    	        $td = $a['td'];
	        $documento = $a['documento'];
	        $ruc = $a['cli_ruc'];
	        $razsocial = $a['cli_razsocial'];
	        $tc = $a['nu_tipocambio'];
    	        $neto = $a['neto'];
	        $impuestos = $a['impuestos'];
		$total = $a['total'];
	        $anulado = $a['ch_fac_anulado'];
    	        $td_descripcion = $a['td_descripcion'];

        	if ($cuenta > 60) {
        	    $cuenta = 0;
            	    $pagina++;
    		}

	        if ($old_td != $td) {
            	    $old_td = $td;
            	    $pagina++;
            	    $cuenta = 0;
        	}

        	$ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['fecha'] = $fecha;
    	        $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['td'] = $td;
    	        $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['documento'] = $documento;

        	if ($anulado != 'S') {
            	    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['ruc'] = $ruc;
        	    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = $razsocial;
            	    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['tc'] = $tc;
            	    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['neto'] = $neto;
            	    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['impuestos'] = $impuestos;
        	    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['total'] = $total;

		    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['totales']['neto'] += $neto;
		    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['totales']['impuestos'] += $impuestos;
		    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['totales']['total'] += $total;
		    $ventas['documentos']['tipos'][$td]['totales']['neto'] += $neto;
        	    $ventas['documentos']['tipos'][$td]['totales']['impuestos'] += $impuestos;
		    $ventas['documentos']['tipos'][$td]['totales']['total'] += $total;
		}
        	else {
            	    $ventas['documentos']['tipos'][$td]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = "A N U L A D A";
    		}

		$cuenta++;
	    }
	
	    /* Parte 4b: acumulacion de totales por linea para esos documentos */
	    $sql = "SELECT
			sum(det.nu_fac_importeneto) as neto,
		        sum(det.nu_fac_valortotal-det.nu_fac_importeneto) as impuestos,
		        sum(det.nu_fac_valortotal) as total,
		        trim(art.art_tipo) as tipo,
				cab.ch_fac_tipodocumento as tipo_documento
		    FROM
				fac_ta_factura_cabecera cab,
		        fac_ta_factura_detalle det,
		        int_articulos art
		    WHERE
			    cab.dt_fac_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . "' and '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . "'
			AND cab.ch_fac_tipodocumento IN ('10', '11', '20', '35') ";
	    if ($estacion != 'TODAS') {
		$sql .= "AND cab.ch_fac_seriedocumento LIKE '%" . pg_escape_string(substr($estacion, 1)) . "' ";
	    }
	    $sql .= "
			AND (cab.ch_fac_anulado!='S' OR cab.ch_fac_anulado IS NULL)
		        AND det.ch_fac_tipodocumento=cab.ch_fac_tipodocumento
		        AND det.ch_fac_seriedocumento=cab.ch_fac_seriedocumento
		        AND det.ch_fac_numerodocumento=cab.ch_fac_numerodocumento
		        AND det.cli_codigo=cab.cli_codigo
			AND art.art_codigo=det.art_codigo
		    GROUP BY
			art.art_tipo,
			cab.ch_fac_tipodocumento
		    ;
		    ";
	    if ($sqlca->query($sql) < 0) {
		return false;
	    }
	
	    for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
	        $neto = $a['neto'];
	        $impuestos = $a['impuestos'];
	        $total = $a['total'];
	        $tipo = $a['tipo'];
		$tipo_documento = $a['tipo_documento'];
	    
		switch ($tipo) {
		    case '01':
		    case '04':
			$tipo = "combustibles";
			break;
		    case '02':
			$tipo = "lubricantes";
		        break;
		    case '03':
		        $tipo = "accesorios";
		        break;
		    case '06':
		        $tipo = "servicios";
		        break;
		    case '05':
		        $tipo = "market";
		        break;
		    case '08':
		        $tipo = "market";
		        break;
		    case '09':
		        $tipo = "whiz";
		        break;
		    case '10':
		        $tipo = "ob";
		        break;
		    default:
		        $tipo = "otros";
		        break;
		}

		if ($tipo_documento=='20') {
		    /* Nota de credito cuenta como negativo para el total */
		    $ventas['documentos']['totales'][$tipo] -= $neto;
		    $ventas['documentos']['totales']['neto'] -= $neto;
		    $ventas['documentos']['totales']['impuestos'] -= $impuestos;
		    $ventas['documentos']['totales']['total'] -= $total;
		}
		else {
		    /* Resto de documentos suman */
		    $ventas['documentos']['totales'][$tipo] += $neto;
	    	    $ventas['documentos']['totales']['neto'] += $neto;
		    $ventas['documentos']['totales']['impuestos'] += $impuestos;
	            $ventas['documentos']['totales']['total'] += $total;
		}
	    }
	//}
	/*******************************> Fin de parte 4 <*****************************/
	
	/********************> Parte 5: Anexo de Facturacion de Oficina <***********************/
	//if ($tipo_cliente == "CREDITO" || $tipo_cliente == "AMBOS") {
	
	    /* Parte 5a: Generacion del anexo de facturacion */
	   /* $sql = "SELECT
			to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
			trim(tab.tab_car_03) as td,
		        trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        cli.cli_ruc,
		        cli.cli_razsocial,
		        cab.nu_tipocambio,
		        cab.nu_fac_valorbruto as neto,
		        (cab.nu_fac_valortotal-cab.nu_fac_valorbruto) as impuestos,
		        cab.nu_fac_valortotal as total,
		        (cab.nu_fac_descuento1+cab.nu_fac_descuento2+cab.nu_fac_descuento3) as descuento,
		        cab.ch_fac_anulado,
		        trim(tab.tab_car_03)||' - '||trim(tab.tab_descripcion) as td_descripcion
		    FROM
			fac_ta_factura_cabecera cab,
		        int_clientes cli,
		        int_tabla_general tab
		    WHERE
			    cab.dt_fac_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . "' and '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . "'
			AND cab.ch_fac_tipodocumento='10'
		        AND cab.ch_fac_seriedocumento='001'
		        AND cli.cli_codigo=cab.cli_codigo
		        AND tab.tab_tabla='08'
		        AND tab.tab_elemento=lpad(cab.ch_fac_tipodocumento, 6, '0')
		    ORDER BY
			cab.ch_fac_tipodocumento,
		        cab.ch_fac_seriedocumento,
		        cab.ch_fac_numerodocumento
		    ;
		    ";*/
	    /*if ($sqlca->query($sql) < 0) {
		return false;
	    }
	    $pagina = 1;
	    $ndocs = 0;

	    for($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();

        	$fecha = $a['fecha'];
    	        $td = $a['td'];
	        $documento = $a['documento'];
	        $ruc = $a['cli_ruc'];
	        $razsocial = $a['cli_razsocial'];
	        $tc = $a['nu_tipocambio'];
    		$neto = $a['neto'];
	        $impuestos = $a['impuestos'];
		$total = $a['total'];
	        $descuento = $a['descuento'];
	        $anulado = $a['ch_fac_anulado'];
    	        $td_descripcion = $a['td_descripcion'];

		$neto_real = $neto-$descuento;*/

		/* 60 es la cantidad de documentos por pagina. si es necesario, se cambia aca */
	  /*      if ($ndocs > 60) {
		    $ndocs = 0;
		    $pagina++;
		}
		$ndocs++;*/

		/*$ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['fecha'] = $fecha;
	        $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['td'] = $td;
	        $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['documento'] = $documento;
	    
		if ($anulado != 'S') {
		    $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['ruc'] = $ruc;
	    	    $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['razsocial'] = $razsocial;
		    $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['tc'] = $tc;
	            $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['neto'] = $neto;
	    	    $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['descuento'] = $descuento;
	    	    $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['neto_real'] = $neto_real;
	    	    $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['impuestos'] = $impuestos;
	    	    $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['total'] = $total;
	*/
		    /* Total por pagina */	
		/*    $ventas['oficina']['paginas'][$pagina]['totales']['neto'] += $neto;
		    $ventas['oficina']['paginas'][$pagina]['totales']['descuento'] += $descuento;
		    $ventas['oficina']['paginas'][$pagina]['totales']['neto_real'] += $neto_real;
		    $ventas['oficina']['paginas'][$pagina]['totales']['impuestos'] += $impuestos;
		    $ventas['oficina']['paginas'][$pagina]['totales']['total'] += $total;
*/
		    /* Total general */
		   /* $ventas['oficina']['totales']['neto'] += $neto;
		    $ventas['oficina']['totales']['descuento'] += $descuento;
		    $ventas['oficina']['totales']['neto_real'] += $neto_real;
		    $ventas['oficina']['totales']['impuestos'] += $impuestos;
		    $ventas['oficina']['totales']['total'] += $total;
		}
		else {
		    $ventas['oficina']['paginas'][$pagina]['documentos'][$documento]['razsocial'] = "A N U L A D A";
		}
	    }*/
	    
	    /* Parte 5b: Llena ventas en market y combustibles con ventas de credito de la oficina */
	    /*Obtiene todos los montos de las facturas de credito y de contado, solo de la oficina central*/
	/*    if ($estacion=='001' || $estacion=='TODAS'){
    	    $sql = "SELECT
            		d.nu_fac_valortotal as total,
		        d.nu_fac_importeneto as neto,
		        (d.nu_fac_valortotal-d.nu_fac_importeneto) as igv,
			d.nu_fac_cantidad as cantidad,
		        trim(art.art_tipo) as tipo,
			trim(d.art_codigo) as codigo,
		        to_char(c.dt_fac_fecha, 'dd/mm') as dia,
			c.ch_fac_anticipo as anticipo
		    FROM
			fac_ta_factura_cabecera c,
		        fac_ta_factura_detalle d,
		        int_articulos art
		    WHERE
			    c.ch_fac_tipodocumento='10'
			AND c.ch_fac_seriedocumento='001'
			AND c.dt_fac_fecha BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
		    AND c.ch_fac_tipodocumento=d.ch_fac_tipodocumento
			AND c.ch_fac_seriedocumento=d.ch_fac_seriedocumento
		    AND c.ch_fac_numerodocumento=d.ch_fac_numerodocumento
		    AND c.cli_codigo=d.cli_codigo
		    AND art.art_codigo = d.art_codigo
        	ORDER BY c.dt_fac_fecha,art.art_tipo;
		    ";
	    if ($sqlca->query($sql) < 0) {
		return false;
	    }
	    
	    for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();

		$total = $a['total'];
	        $neto = $a['neto'];
	        $igv = $a['igv'];
		$cantidad = $a['cantidad'];
	        $tipo = $a['tipo'];
		$codigo = $a['codigo'];
	        $dia = $a['dia'];
	        $es = '001';
		$anticipo = $a['anticipo'];

		if (!isset($ventas['combustibles']['estaciones'][$es])) {
		    $ventas['combustibles']['estaciones'][$es] = VentasOficialModel::agregarEstacionCombustible($desde_dia, $hasta_dia, $desde_mes);
		}
		if (!isset($ventas['market']['estaciones'][$es])) {
		    $ventas['market']['estaciones'][$es] = VentasOficialModel::agregarEstacionMarket($desde_dia, $hasta_dia, $desde_mes);
		}

		if (($tipo == '01' || $tipo == '04') && $anticipo != 'S') {

	    	    /* Calcula totales por centro de costo */
	/*    	    $ventas['combustibles']['estaciones'][$es]['totales'][$codigo.'_cantidad'] += $cantidad;
	    	    $ventas['combustibles']['estaciones'][$es]['totales'][$codigo.'_importe'] += $neto;
	    	    $ventas['combustibles']['estaciones'][$es]['totales']['neto'] += $neto;
	    	    $ventas['combustibles']['estaciones'][$es]['totales']['impuestos'] += $igv;
    	    	    if ($codigo !='11620307') { $ventas['combustibles']['estaciones'][$es]['totales']['total_cantidad'] += $cantidad; $ventas['combustibles']['estaciones'][$es]['totales']['total_importe'] += $neto; }

		    /* Calcula totales por combustibles */	    
	/*    	    $ventas['combustibles']['totales'][$codigo.'_cantidad'] += $cantidad;
	    	    $ventas['combustibles']['totales'][$codigo.'_importe'] += $neto;
		    	$ventas['combustibles']['totales']['neto'] += $neto;
	    	    $ventas['combustibles']['totales']['impuestos'] += $igv;
	    	    if ($codigo !='11620307') { $ventas['combustibles']['totales']['total_cantidad'] += $cantidad; $ventas['combustibles']['totales']['total_importe'] += $neto; }
	    
	    	    /* Calcula dia */
	/*    	    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['fecha'] = $dia;
	    	    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_cantidad'] += $cantidad;
	    	    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_importe'] += $neto;
	    	    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] += $neto;
	    	    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] += $igv;		    
	    	    if ($codigo !='11620307') { $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['total_cantidad'] += $cantidad; $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['total_importe'] += $neto; }
		    
		    /* Actualiza totales de market */
	/*	    $ventas['market']['estaciones'][$es]['dias']['detalle']['combustibles'] += $neto;
		    $ventas['market']['estaciones'][$es]['totales']['combustibles'] += $neto;
		    $ventas['market']['totales']['combustibles'] += $neto;
		}
		else {
		    switch ($tipo) {
			case '02':
		    	    $tipo = "lubricantes";
		    	    break;
			case '03':
		    	    $tipo = "accesorios";
		    	    break;
			case '06':
		    	    $tipo = "servicios";
		    	    break;
			case '05':
		    	    $tipo = "market";
		    	    break;
		    case '08':
		    	    $tipo = "market";
		    	    break;	    
		   	case '09':
			    $tipo = "whiz";
		    	    break;
			case '10':
		            $tipo = "ob";
		            break;
		        default:
		            $tipo = "otros";
		            break;
		    }
		    /* Los anticipos estan por encima del tipo de producto del que se trate */
	//	    if ($anticipo == 'S') $tipo = "anticipos";

		    /* Calcula ventas de dia actual */
	//    	    $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle'][$tipo] += $neto;
	    
	    	    /* Calcula total por CC */
	//    	    $ventas['market']['estaciones'][$es]['totales'][$tipo] += $neto;
	    
	    	    /* Calcula total general */
	//    	    $ventas['market']['totales'][$tipo] += $neto;
	//	}
		/* Dia Actual */
	/*	$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] += $neto;
	    	$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] += $igv;
	    	$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['total'] += $total;

		/* Total por CC */
    	/*	$ventas['market']['estaciones'][$es]['totales']['neto'] += $neto;
	    	$ventas['market']['estaciones'][$es]['totales']['impuestos'] += $igv;
	    	$ventas['market']['estaciones'][$es]['totales']['total'] += $total;

		/* Total General */
	/*        $ventas['market']['totales']['neto'] += $neto;
	        $ventas['market']['totales']['impuestos'] += $igv;
	        $ventas['market']['totales']['total'] += $total;
	    }
	//}

	/* Parte 5c: Llena el rango de facturas en la venta de combustibles */
/*	$sql = "SELECT
		    max(ch_fac_numerodocumento) as maximo,
		    min(ch_fac_numerodocumento) as minimo,
		    to_char(dt_fac_fecha, 'dd/mm') as fecha
		FROM
		    fac_ta_factura_cabecera
		WHERE
		        ch_fac_tipodocumento='10'
		    AND ch_fac_seriedocumento='001'
		    AND dt_fac_fecha BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
		GROUP BY
		    dt_fac_fecha
		;
		";
	if ($sqlca->query($sql) < 0) {
	    return false;
	}

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $maximo = $a['maximo'];
	    $minimo = $a['minimo'];
	    $fecha = $a['fecha'];
	    
	    $ventas['combustibles']['estaciones']['001']['dias'][$fecha]['tickets'] = "01-" . $minimo . " AL " . $maximo;
	}*/
//	    }
//	ksort($ventas['market']['estaciones']);
//	ksort($ventas['combustibles']['estaciones']);

	/************************ Fin de parte 5 **************************/


	/*-------------------------> Parte 6: Detalle de tickets factura <-------------------------*/
	$sql = "SELECT
		    t.fecha as fecha,
		    t.td as td,
		    t.trans as trans,
		    t.caja as caja,
		    max(t.ruc) as ruc,
		    max(r.razsocial) as razsocial,
		    (sum(t.importe)-sum(t.igv)) as vventa,
		    sum(t.igv) as igv,
		    sum(t.importe) as importe,
		    t.es as es
		FROM
        	    pos_trans" . pg_escape_string($desde_ano.$desde_mes) . " t
		    LEFT JOIN ruc r ON (r.ruc=t.ruc)
		WHERE
		    dia BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
		    AND td='F' ";
	if ($estacion != 'TODAS') {
	    $sql .= "AND es='" . pg_escape_string($estacion) . "' ";
	}
	
	$sql = $sql . "
		GROUP BY
		    t.fecha,
		    t.td,
		    t.trans,
		    t.caja,
		    t.es
		ORDER BY
		    t.es,
		    t.fecha,
		    t.trans";
		    
	if ($sqlca->query($sql) < 0) {
	    return false;
	}
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $fecha = $a['fecha'];
	    $td = $a['td'];
	    $trans = $a['trans'];
	    $caja = $a['caja'];
	    $ruc = $a['ruc'];
	    $razsocial = $a['razsocial'];
	    $vventa = $a['vventa'];
	    $igv = $a['igv'];
	    $importe = $a['importe'];
	    $es = $a['es'];
	    
	    /* Parte 6a: acumular totales para el final del reporte */
	    $ventas['tickets_factura']['total_vventa'] += $vventa;
	    $ventas['tickets_factura']['total_igv'] += $igv;
	    $ventas['tickets_factura']['total_importe'] += $importe;
	    
	    /* Parte 6b: acumular totales por estacion */
	    $ventas['tickets_factura']['estaciones'][$es]['total_vventa'] += $vventa;
	    $ventas['tickets_factura']['estaciones'][$es]['total_igv'] += $igv;
	    $ventas['tickets_factura']['estaciones'][$es]['total_importe'] += $importe;
	    
	    /* Parte 6b: insertar detalle */
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['fecha'] = $fecha;
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['td'] = $td;
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['trans'] = $trans;
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['caja'] = $caja;
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['ruc'] = $ruc;
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['razsocial'] = $razsocial;
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['vventa'] = $vventa;
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['igv'] = $igv;
	    $ventas['tickets_factura']['estaciones'][$es]['detalle'][$i]['importe'] = $importe;
	}
	/************************ Fin de parte 6 **************************/

	/*-------------------------> Parte 7: Generacion del resumen <--------------------------*/
	
	/* Parte 6a: Acumular totales de market */
	$ventas['totales']['combustibles'] += $ventas['market']['totales']['combustibles'];
	$ventas['totales']['lubricantes'] += $ventas['market']['totales']['lubricantes'];
	$ventas['totales']['accesorios'] += $ventas['market']['totales']['accesorios'];
	$ventas['totales']['servicios'] += $ventas['market']['totales']['servicios'];
	$ventas['totales']['market'] += $ventas['market']['totales']['market'];
	$ventas['totales']['whiz'] += $ventas['market']['totales']['whiz'];
	$ventas['totales']['ob'] += $ventas['market']['totales']['ob'];
	$ventas['totales']['otros'] += $ventas['market']['totales']['otros'];
	$ventas['totales']['neto'] += $ventas['market']['totales']['neto'];
	$ventas['totales']['impuestos'] += $ventas['market']['totales']['impuestos'];
	$ventas['totales']['total'] += $ventas['market']['totales']['total'];
	
	/* Parte 6b: Acumular totales de Documentos */
	$ventas['totales']['combustibles'] += $ventas['documentos']['totales']['combustibles'];
	$ventas['totales']['lubricantes'] += $ventas['documentos']['totales']['lubricantes'];
	$ventas['totales']['accesorios'] += $ventas['documentos']['totales']['accesorios'];
	$ventas['totales']['servicios'] += $ventas['documentos']['totales']['servicios'];
	$ventas['totales']['market'] += $ventas['documentos']['totales']['market'];
	$ventas['totales']['whiz'] += $ventas['documentos']['totales']['whiz'];
	$ventas['totales']['ob'] += $ventas['documentos']['totales']['ob'];
	$ventas['totales']['otros'] += $ventas['documentos']['totales']['otros'];
	$ventas['totales']['neto'] += $ventas['documentos']['totales']['neto'];
	$ventas['totales']['impuestos'] += $ventas['documentos']['totales']['impuestos'];
	$ventas['totales']['total'] += $ventas['documentos']['totales']['total'];
	
	/*-------------------------------> Fin de parte 7 <----------------------------------*/

	return $ventas;
    }
}

