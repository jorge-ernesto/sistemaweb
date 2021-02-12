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
	    if ($a[0]!='007' && $a[0]!='008' && $a[0]!='019' && $a[0]!='020' && $a[0]!='030')
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

	if ($estacion=='TODAS'){
		$total_estaciones = VentasOficialModel::obtenerEstaciones();
	}else {
		$total_estaciones = array($estacion=>$estacion);
	}
	$estacion='';
	
	
	/*-----------------------> Parte 1: Venta de combustibles al contado <---------------------------*/
	//if ($tipo_cliente == "CONTADO" || $tipo_cliente == "AMBOS") {
	
	    /*Obtiene la venta y cantidad de combustible*/
	 foreach ($total_estaciones as $estacion => $valor){
	    
	    if ($estacion!='001'){
	    	$sql = "SELECT
						ch_sucursal as es,
				        nu_ventavalor + nu_descuentos as total,
				        nu_ventagalon as cantidad,
				        round(1+(util_fn_igv_porarticulo(ch_codigocombustible)/100), 2) as factor_igv,
			    	    (nu_afericionveces_x_5*5) as afericion,
				        (round((nu_ventavalor/nu_ventagalon), 2)) as precio,
				        ch_codigocombustible as codigo,
				        to_char(dt_fechaparte, 'dd/mm') as dia, 
				        '01' as moneda,
				        '3.22' as tcambio
		    		FROM
	    				comb_ta_contometros
		    		WHERE
			    		dt_fechaparte BETWEEN '" . pg_escape_string($desde_ano.'-'.$desde_mes.'-'.$desde_dia) . "' AND '" . pg_escape_string($hasta_ano.'-'.$hasta_mes.'-'.$hasta_dia) . "'
						AND nu_ventavalor > 0  AND nu_ventagalon > 0 ";
			    		$sql .= "AND ch_sucursal='" . pg_escape_string($estacion) . "'";
			    		$sql .= " ORDER BY	ch_sucursal, dt_fechaparte, ch_numeroparte,	ch_surtidor;";
	    }else {
	    		$sql = "select c.ch_almacen as es, c.ch_fac_moneda as moneda, c.nu_tipocambio as tcambio, d.nu_fac_cantidad as cantidad, d.nu_fac_precio as precio, d.nu_fac_valortotal as total, 
					to_char(dt_fac_fecha, 'dd/mm') as dia, trim(a.art_codigo) as codigo, round(1+(util_fn_igv_porarticulo(a.art_codigo)/100), 2) as factor_igv, 0 as afericion
					from fac_ta_factura_cabecera c inner join fac_ta_factura_detalle d 
					on c.cli_codigo=d.cli_codigo and c.ch_fac_seriedocumento=d.ch_fac_seriedocumento 
					and c.ch_fac_tipodocumento=d.ch_fac_tipodocumento and c.ch_fac_numerodocumento=d.ch_fac_numerodocumento inner join int_articulos a 
					on a.art_codigo=d.art_codigo
					where c.dt_fac_fecha between '".pg_escape_string($desde_ano.'-'.$desde_mes.'-'.$desde_dia)."' and '".pg_escape_string($hasta_ano.'-'.$hasta_mes.'-'.$hasta_dia)."' and c.ch_almacen='001' and c.ch_fac_tipodocumento='10' 
					and (a.art_tipo='01' or a.art_tipo='04') and c.ch_fac_anticipo='N'";
		    		if ($tipo_cliente!='AMBAS'){
		    			$sql .= "and c.ch_fac_credito='".$tipo_cliente."'";
		    		}
		    		$sql .= " order by dt_fac_fecha";
	    }
//print_r($sql);
	    if ($sqlca->query($sql) < 0) {
			return false;
	    }
		//print_r($desde_dia.'m:'.$desde_mes.'h:'.$hasta_dia);
		//print_r($sql);
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
	        $moneda = $a['moneda'];
	        $tcambio = $a['tcambio'];
	     	//print_r($a);
	        /* Descuenta afericiones y actualiza totales en valor con la nueva venta. Eso
		 	* se hace debido a que las afericiones no pagan impuestos debido a que son
		 	* despachos de prueba y no son venta.
		 	*/
    	    $cantidad -= $afericion;
	        $total -= ($afericion*$precio);
	        $neto = round($total/$factor_igv, 2);
	        $igv = round($total-$neto, 2);
			$cantidad=($cantidad<0?0:$cantidad);
			$total=($total<0?0:$total);
			if ($moneda=='02'){
				$total = $total*$tcambio;
				$neto = $neto*$tcambio;
				$igv = $igv*$tcambio;
			}
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
	    //print_r($ventas);

	    /* Parte 1b: Obtiene los numeros de tickets por cada punto de combustibles */
	    
	    
	    if ($estacion!='001'){
		    $sql = "SELECT  substring(min(date_trunc('hour',fecha)||trans),20) as min, 
							substring(max(date_trunc('hour',fecha)||trans),20) as max, 
							caja, 
							to_char(dia, 'dd/mm') as dia, 
							es
			    	FROM
			        		pos_trans" . pg_escape_string($desde_ano.$desde_mes) . "
			    	WHERE
				    		dia BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'";
				   	$sql .= "AND es='" . pg_escape_string($estacion) . "' ";
				    $sql .= " GROUP BY es, dia, caja ORDER BY es, dia, caja;";
		 }else{
	    	$sql="SELECT MIN(C.CH_FAC_NUMERODOCUMENTO) AS min , 
	    				max(c.ch_fac_numerodocumento) as max, '01' as caja,
	    				trim(to_char(c.dt_fac_fecha, 'dd/mm')) as dia,
	    				c.ch_almacen as es 
	    		  from fac_ta_factura_cabecera c where c.dt_fac_fecha between '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
	    		  	and c.ch_fac_tipodocumento='10' and c.ch_fac_seriedocumento='001' ";
	    			if ($tipo_cliente!='AMBAS'){
		    			$sql .= " and c.ch_fac_credito='".$tipo_cliente."'";
		    		}
	    	$sql .= " GROUP BY es, dia ORDER BY es, dia;";
	    }
	    
	    if ($sqlca->query($sql) < 0) {
		return false;
	    }
	//print_r($ventas);
	    for($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
	        $min = $a['min'];
	        $max = $a['max'];
	        $caja = $a['caja'];
	        $dia = $a['dia'];
	        $es = $a['es'];
	        $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['tickets'] .= str_pad($caja, 2, '0', STR_PAD_LEFT) . '-' . str_pad($min, 8, '0', STR_PAD_LEFT) . ' AL ' . str_pad($max, 8, '0', STR_PAD_LEFT) . ' ';
	    }
	//}
	//print_r($ventas);
	/*-----------------> Fin de parte 1 <------------------------*/
	
	/*Descontar vales*/
	if ($estacion!='001'){
		
		if ($descontar=='' && $tipo_cliente=='N'){
				
		}else{
		
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
		    		WHERE det.dt_fecha BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'";
		    			$sql .= " AND det.ch_sucursal='" . pg_escape_string($estacion) . "' ";
		    			$sql .= " AND art.art_codigo=det.ch_articulo
						AND art.art_tipo IN ('01','04')
		    		GROUP BY
						art.art_tipo,
			       		det.ch_articulo,
						det.dt_fecha,
						det.ch_sucursal
		    		ORDER BY
						det.ch_sucursal,
						det.dt_fecha,
						art.art_tipo,
						det.ch_articulo";
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

				/* Descuenta en dia */
				$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_cantidad'] -= $cantidad;
				$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_importe'] -= $neto;
				$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_cantidad'] = ($ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_cantidad']<0?0:$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_cantidad']);
				$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_importe'] = ($ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_importe']<0?0:$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle'][$codigo.'_importe']);
				$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] -= $neto;
				$ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] -= $igv;
				
				if ($tipo != '04') {
				    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['total_cantidad'] -= $cantidad;
				    $ventas['combustibles']['estaciones'][$es]['dias'][$dia]['detalle']['total_importe'] -= $neto;
				}
		    
				/* Descuenta total por CC */
				$ventas['combustibles']['estaciones'][$es]['totales'][$codigo.'_cantidad'] -= $cantidad;
				$ventas['combustibles']['estaciones'][$es]['totales'][$codigo.'_importe'] -= $neto;
				$ventas['combustibles']['estaciones'][$es]['totales']['neto'] -= $neto;
				$ventas['combustibles']['estaciones'][$es]['totales']['impuestos'] -= $igv;
				if ($tipo != '04') {
				    $ventas['combustibles']['estaciones'][$es]['totales']['total_cantidad'] -= $cantidad;
				    $ventas['combustibles']['estaciones'][$es]['totales']['total_importe'] -= $neto;
				}
		    
				/* Descuenta total de combustibles */
				$ventas['combustibles']['totales'][$codigo.'_cantidad'] -= $cantidad;
				$ventas['combustibles']['totales'][$codigo.'_importe'] -= $neto;
				$ventas['combustibles']['totales']['neto'] -= $neto;
				$ventas['combustibles']['totales']['impuestos'] -= $igv;
				if ($tipo != '04') {
				    $ventas['combustibles']['totales']['total_cantidad'] -= $cantidad;
				    $ventas['combustibles']['totales']['total_importe'] -= $neto;
				}
		    
		  }
	    
	    
		}
	    
	}
	/*fin descontar vales*/
	
	/*anticipos*/
	    if ($estacion=='001'){
	    	$sql = "SELECT	d.nu_fac_valortotal as total,
		        d.nu_fac_importeneto as neto,
		        d.nu_fac_impuesto1 as igv,
				d.nu_fac_cantidad as cantidad,
		        trim(d.art_codigo) as codigo,
		        to_char(c.dt_fac_fecha, 'dd/mm') as dia,
		        c.ch_fac_moneda as moneda,
		        c.nu_tipocambio as tcambio, 
		        '001' as es
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
		    	AND art.art_codigo = d.art_codigo and c.ch_fac_anticipo='S'";
	    		if ($tipo_cliente!='AMBAS'){
		    			$sql .= " and c.ch_fac_credito='".$tipo_cliente."' ";
		    	}
		    	$sql .= " ORDER BY c.dt_fac_fecha,art.art_tipo;";
	    	
	    	 if ($sqlca->query($sql) < 0) {
	        	return false;
	    	 }
	    	 for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
	    		$total = $a['total'];
	    	    $neto = $a['neto'];
	        	$igv = $a['igv'];
	        	$dia = $a['dia'];
	        	$es = $a['es'];
	        	$moneda=$a['moneda'];
	        	$tcambio = $a['tcambio'];
	        	if ($moneda=='02'){
	        		$total=$total*$tcambio;
	        		$neto=$neto*$tcambio;
	        		$igv=$igv*$tcambio;
	        	}
	    
				/* Ver nota en la parte 1 para la explicacion de esta linea */
				if (!isset($ventas['market']['estaciones'][$es])) {
			    	$ventas['market']['estaciones'][$es] = VentasOficialModel::agregarEstacionMarket($desde_dia, $hasta_dia, $desde_mes);
				}
			    
		    	$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['anticipos'] += $neto;
	        	$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] += $neto;
	        	$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] += $igv;
	        	$ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['total'] += $total;
	    
	        	/* Calcula total por CC */
	        	$ventas['market']['estaciones'][$es]['totales']['anticipos'] += $neto;
	        	$ventas['market']['estaciones'][$es]['totales']['neto'] += $neto;
	        	$ventas['market']['estaciones'][$es]['totales']['impuestos'] += $igv;
	        	$ventas['market']['estaciones'][$es]['totales']['total'] += $total;
	    
	        	/* Calcula total general */
	        	$ventas['market']['totales']['anticipos'] += $neto;
	        	$ventas['market']['totales']['neto'] += $neto;
	        	$ventas['market']['totales']['impuestos'] += $igv;
	        	$ventas['market']['totales']['total'] += $total;
	    
	    	}
	    }
	    /*fin anticipos*/
	    
	    /*calcular venta de market*/
	    $sql = "	SELECT	sum(d.nu_fac_valortotal) as total,
		        			sum(d.nu_fac_importeneto) as neto,
		        			sum(d.nu_fac_impuesto1) as igv,
		        			trim(art.art_tipo) as tipo,
		        			to_char(c.dt_fac_fecha, 'dd/mm') as dia,
		        			c.ch_punto_venta as es,
		        			c.ch_fac_moneda as moneda,
		        			c.nu_tipocambio as tcambio
		    		FROM
							fac_ta_factura_cabecera c,
		        			fac_ta_factura_detalle d,
		        			int_articulos art
		    		WHERE
			    			c.dt_fac_fecha BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
							AND c.ch_fac_tipodocumento=d.ch_fac_tipodocumento
							AND c.ch_fac_seriedocumento=d.ch_fac_seriedocumento
		    				AND c.ch_fac_numerodocumento=d.ch_fac_numerodocumento
		    				AND c.cli_codigo=d.cli_codigo
		    				AND art.art_codigo = d.art_codigo ";
    	       				if ($estacion=='001') $sql .= " and c.ch_fac_anticipo='N' ";
		    				if ($estacion!='001') $sql .=" and c.ch_fac_tipodocumento='45' "; else $sql.=" and c.ch_fac_tipodocumento='10' ";
    	    				if ($tipo_cliente!='AMBAS'){
		    					$sql .= " and c.ch_fac_credito='".$tipo_cliente."' ";
		    				}
    	 		    		$sql .= " AND c.ch_almacen='" . pg_escape_string($estacion) . "' ";
	    					$sql .= " AND (art.art_tipo != '01' and art.art_tipo != '04') 
            	    GROUP BY
        	        	c.ch_punto_venta,
                		c.dt_fac_fecha,
                		c.ch_fac_moneda,
                		c.nu_tipocambio,
            	        art.art_tipo
            	    ORDER BY
                		c.ch_punto_venta,
            			c.dt_fac_fecha,
        				art.art_tipo;";
	    //print_r($ventas);
	    if ($sqlca->query($sql) < 0) {
	        return false;
	    }
		if (!isset($ventas['market']['estaciones'][$estacion])) {
			    $ventas['market']['estaciones'][$estacion] = VentasOficialModel::agregarEstacionMarket($desde_dia, $hasta_dia, $desde_mes);
			}
	    for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
	    
			$total = $a['total'];
	        $neto = $a['neto'];
	        $igv = $a['igv'];
	        $tipo = $a['tipo'];
	        $dia = $a['dia'];
	        $es = $a['es'];
	        $moneda = $a['moneda'];
	        $tcambio = $a['tcambio'];
	        if ($moneda=='02'){
	        	$total = $total*$tcambio;
	        	$neto = $neto*$tcambio;
	        	$igv = $igv*$tcambio;
	        }
	    
			/* Ver nota en la parte 1 para la explicacion de esta linea */
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
			    case '13':
			        $tipo = "market";
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
	    	ksort($ventas['market']['estaciones'][$es]['dias']);
	    }
	    
	    
	    
	    /*Descontar vales lubricantes, etc*/
	if ($estacion!='001'){
		
		if ($descontar=='' && $tipo_cliente=='N'){
				
		}else{
		
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
		    		WHERE det.dt_fecha BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'";
		    			$sql .= " AND det.ch_sucursal='" . pg_escape_string($estacion) . "' ";
		    			$sql .= " AND art.art_codigo=det.ch_articulo
						AND (art.art_tipo != '01' and art.art_tipo != '04') 
		    		GROUP BY
						art.art_tipo,
			       		det.ch_articulo,
						det.dt_fecha,
						det.ch_sucursal
		    		ORDER BY
						det.ch_sucursal,
						det.dt_fecha,
						art.art_tipo,
						det.ch_articulo";
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
			    case '13':
			        $tipo = "market";
			        break;
			    default:
			        $tipo = "otros";
			        break;
			}
				/* Calcula ventas de dia actual */
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle'][$tipo] -= $neto;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] -= $neto;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] -= $igv;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['total'] -= $total;
	    
	        /* Calcula total por CC */
	        $ventas['market']['estaciones'][$es]['totales'][$tipo] -= $neto;
	        $ventas['market']['estaciones'][$es]['totales']['neto'] -= $neto;
	        $ventas['market']['estaciones'][$es]['totales']['impuestos'] -= $igv;
	        $ventas['market']['estaciones'][$es]['totales']['total'] -= $total;
	    
	        /* Calcula total general */
	        $ventas['market']['totales'][$tipo] -= $neto;
	        $ventas['market']['totales']['neto'] -= $neto;
	        $ventas['market']['totales']['impuestos'] -= $igv;
	        $ventas['market']['totales']['total'] -= $total;
	    	ksort($ventas['market']['estaciones'][$es]['dias']);
		    
		  }
	    
	    
		}
	    
	}
	/*fin descontar vales lubricantes, etc*/
	    
	    	    
	    /*recargo otros*/
	     $sql = "	SELECT	c.nu_fac_recargo2 as total,
	     					round(c.nu_fac_recargo2/round((1+util_fn_igv()/100),2),2) as neto,
	     					round(c.nu_fac_recargo2/round((1+util_fn_igv()/100),2),2)*(util_fn_igv()/100) as igv,
		        			'otros' as tipo,
		        			to_char(c.dt_fac_fecha, 'dd/mm') as dia,
		        			c.ch_punto_venta as es,
		        			c.ch_fac_moneda as moneda,
		        			c.nu_tipocambio as tcambio
		    		FROM
							fac_ta_factura_cabecera c
		        			
		    		WHERE
			    			c.dt_fac_fecha BETWEEN '" . $desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."' ";
    	       				if ($estacion=='001') $sql .= " and c.ch_fac_anticipo='N' ";
		    				if ($estacion!='001') $sql .=" and c.ch_fac_tipodocumento='45' "; else $sql.=" and c.ch_fac_tipodocumento='10' ";
    	    				if ($tipo_cliente!='AMBAS'){
		    					$sql .= " and c.ch_fac_credito='".$tipo_cliente."' ";
		    				}
    	 		    		$sql .= " AND c.ch_almacen='" . pg_escape_string($estacion) . "' ";
	    					$sql .= " and c.nu_fac_recargo2>0
            	    ORDER BY
                		c.ch_punto_venta,
            			c.dt_fac_fecha";
	    if ($sqlca->query($sql) < 0) {
	        return false;
	    }
		if (!isset($ventas['market']['estaciones'][$estacion])) {
			    $ventas['market']['estaciones'][$estacion] = VentasOficialModel::agregarEstacionMarket($desde_dia, $hasta_dia, $desde_mes);
			}
			
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
	        $total = $a['total'];
	        $neto = $a['neto'];
	        $igv = $a['igv'];
	        $tipo = $a['tipo'];
	        $dia = $a['dia'];
	        $es = $a['es'];
	        $moneda = $a['moneda'];
	        $tcambio = $a['tcambio'];
	        if ($moneda=='02'){
	        	$neto = $neto*$tcambio;
	        }
	        
	     
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle'][$tipo] += $neto;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['neto'] += $neto;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['impuestos'] += $igv;
	        $ventas['market']['estaciones'][$es]['dias'][$dia]['detalle']['total'] += $total;
	    
	        
	        $ventas['market']['estaciones'][$es]['totales'][$tipo] += $neto;
	        $ventas['market']['estaciones'][$es]['totales']['neto'] += $neto;
	        $ventas['market']['estaciones'][$es]['totales']['impuestos'] += $igv;
	        $ventas['market']['estaciones'][$es]['totales']['total'] += $total;
	    
	       
	        $ventas['market']['totales'][$tipo] += $neto;
	        $ventas['market']['totales']['neto'] += $neto;
	        $ventas['market']['totales']['impuestos'] += $igv;
	        $ventas['market']['totales']['total'] += $total;
	        
		}	
	    /*fin recargo otros*/
	
	 }
	 
	 /*fin de estaciones*/
	
	 foreach($ventas['combustibles']['estaciones'] as $es=>$est) {
		//$ventas['market']['estaciones'][$es] = VentasOficialModel::agregarEstacionMarket($desde_dia, $hasta_dia, $desde_mes);
		
		foreach($est['dias'] as $fecha=>$dia) {
		    $neto = $dia['detalle']['neto'];
		    $igv = $dia['detalle']['impuestos'];
			
		    /* Pone fecha */
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['fecha'] = $fecha;
		
		    /* Transporta total por dia */
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['combustibles'] += $neto;
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['neto'] += $neto;
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['impuestos'] += $igv;
		    $ventas['market']['estaciones'][$es]['dias'][$fecha]['detalle']['total'] += $neto+$igv;
		}
	   
		$neto = $est['totales']['neto'];
	    $igv = $est['totales']['impuestos'];
	    
		/* Transporta total por CC */
	    	$ventas['market']['estaciones'][$es]['totales']['combustibles'] += $neto;
	        $ventas['market']['estaciones'][$es]['totales']['neto'] += $neto;
	        $ventas['market']['estaciones'][$es]['totales']['impuestos'] += $igv;
	        $ventas['market']['estaciones'][$es]['totales']['total'] += $neto+$igv;
	    }
	     
	    //print_r('Entro aqui');
	    $neto = $ventas['combustibles']['totales']['neto'];
	    $igv = $ventas['combustibles']['totales']['impuestos'];
	
	    /* Transporta el total de combustibles */
	    $ventas['market']['totales']['combustibles'] += $neto;
	    $ventas['market']['totales']['neto'] += $neto;
	    $ventas['market']['totales']['impuestos'] += $igv;
	    $ventas['market']['totales']['total'] += $neto+$igv;

		/*transporta totales de market*/
		$ventas['documentos']['totales'] = $ventas['market']['totales'];
		
		
	 foreach ($total_estaciones as $estacion => $valor){	
	    
	    /*factura manual*/
	    
	    //if ($estacion!='001'){
	    
	    $sql = "SELECT
				to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
				'01' as td,
		        trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        comp.ch_fac_ruc as cli_ruc,
		        iif(comp.ch_fac_nombreclie='',cli.cli_razsocial,comp.ch_fac_nombreclie) as cli_razsocial,
		        cab.nu_tipocambio,
		        cab.nu_fac_valorbruto as neto,
		        cab.nu_fac_impuesto1 as impuestos,
		        cab.nu_fac_valortotal as total,
		        cab.ch_fac_anulado,
		        cab.ch_fac_moneda as moneda
		FROM	fac_ta_factura_cabecera cab inner join fac_ta_factura_complemento comp on 
				cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
				and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento
				inner join int_clientes cli on comp.cli_codigo=cli.cli_codigo
 		WHERE
			    cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
				and (cab.ch_fac_seriedocumento!='001' and cab.ch_fac_seriedocumento!='501')
				and cab.ch_fac_tipodocumento='10' and cab.ch_almacen='".$estacion."'";
	    		if ($tipo_cliente!='AMBAS'){
		    		$sql .= " and (cab.ch_fac_credito='".$tipo_cliente."' or cab.ch_fac_credito='".($tipo_cliente=='S'?'1':'0')."')";
		    	}
		$sql .= " union SELECT
				to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
				'01' as td,
		        trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        cli.cli_ruc,
		        cli.cli_razsocial,
		        cab.nu_tipocambio,
		        cab.nu_fac_valorbruto as neto,
		        cab.nu_fac_impuesto1 as impuestos,
		        cab.nu_fac_valortotal as total,
		        cab.ch_fac_anulado,
		        cab.ch_fac_moneda as moneda
		FROM	fac_ta_factura_cabecera cab inner join int_clientes cli on 
				cab.cli_codigo=cli.cli_codigo
 		WHERE cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
				and (cab.ch_fac_seriedocumento!='001' and cab.ch_fac_seriedocumento!='501')
				and cab.ch_fac_tipodocumento='10' and not exists(select comp.* from fac_ta_factura_complemento comp
							where cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
							and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento) ";
				if ($tipo_cliente!='AMBAS'){
		    		$sql .= " and (cab.ch_fac_credito='".$tipo_cliente."' or cab.ch_fac_credito='".($tipo_cliente=='S'?'1':'0')."')";
		    	}
				$sql .= " and cab.ch_almacen='".$estacion."' ORDER BY 
				documento, fecha;";
	    	
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
    	        $td_descripcion = $a['td'];
    	        $moneda = $a['moneda'];
				if ($moneda=='02'){
    	        	$neto=$neto*$tc;
    	        	$total=$total*$tc;
    	        	$impuestos=$impuestos*$tc;
    	        }
	        	if ($cuenta > 60) {
        	    	$cuenta = 0;
            	    $pagina++;
    			}
		        if ($old_td != $td) {
            	    $old_td = $td;
            	    $pagina++;
            	    $cuenta = 0;
        		}
	        	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['fecha'] = $fecha;
    	        $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['td'] = $td;
    	        $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['documento'] = $documento;
	        	if ($anulado != 'S') {
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['ruc'] = $ruc;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = $razsocial;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['tc'] = $tc;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['neto'] = $neto;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['impuestos'] = $impuestos;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['total'] = $total;
			    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['neto'] += $neto;
		    		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'] += $impuestos;
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['total'] += $total;
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['neto'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['neto']+$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['neto'];
		    		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['impuestos'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['impuestos']+$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'];
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['total'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['total']+$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['total'];
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['totales']['neto'] += $neto;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['totales']['impuestos'] += $impuestos;
		    		$ventas['documentos']['tipos'][$td_descripcion]['totales']['total'] += $total;
		    		$ventas['documentos']['totales']['neto']+=$neto;
		    		$ventas['documentos']['totales']['impuestos']+=$impuestos;
		    		$ventas['documentos']['totales']['total']+=$total;
				}
        		else $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = "A N U L A D A";
    			$cuenta++;
	    	}
	    //}
	    	/*fin factura manual*/
	    	
	    	/*boleta manual*/
	    	$sql = "SELECT to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
					'03' as td,
		        	trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        	comp.ch_fac_ruc as cli_ruc,
		        	iif(comp.ch_fac_nombreclie='',cli.cli_razsocial,comp.ch_fac_nombreclie) as cli_razsocial,
		        	cab.nu_tipocambio,
		        	cab.nu_fac_valorbruto as neto,
		        	cab.nu_fac_impuesto1 as impuestos,
		        	cab.nu_fac_valortotal as total,
		        	cab.ch_fac_anulado,
		        	cab.ch_fac_moneda as moneda
				FROM fac_ta_factura_cabecera cab inner join fac_ta_factura_complemento comp on 
					cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
					and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento
					inner join int_clientes cli on comp.cli_codigo=cli.cli_codigo
 				WHERE
			    	cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
					and cab.ch_fac_tipodocumento='35' and cab.ch_almacen='".$estacion."' ";
	    	$sql .= " union SELECT to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
					'03' as td,
					trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
					cli.cli_ruc,
					cli.cli_razsocial,
					cab.nu_tipocambio,
					cab.nu_fac_valorbruto as neto,
					cab.nu_fac_impuesto1 as impuestos,
					cab.nu_fac_valortotal as total,
					cab.ch_fac_anulado,
					cab.ch_fac_moneda as moneda
					FROM fac_ta_factura_cabecera cab inner join int_clientes cli on 
					cab.cli_codigo=cli.cli_codigo where
					cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
					and cab.ch_fac_tipodocumento='35' and not exists(select comp.* from fac_ta_factura_complemento comp
					where cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
					and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento) ";
	    	$sql .= " and cab.ch_almacen='".$estacion."' ORDER BY  
					documento, fecha ";		
	    	//print_r($ventas);
	    	if ($sqlca->query($sql) < 0) {
	        	return false;
	    	}
	    	//print_r($ventas);
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
    	        $td_descripcion = $a['td'];
    	        $moneda = $a['moneda'];
				if ($moneda=='02'){
    	        	$neto=$neto*$tc;
    	        	$total=$total*$tc;
    	        	$impuestos=$impuestos*$tc;
    	        }
	        	if ($cuenta > 60) {
        	    	$cuenta = 0;
            	    $pagina++;
    			}
		        if ($old_td != $td) {
            	    $old_td = $td;
            	    $pagina++;
            	    $cuenta = 0;
        		}
	        	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['fecha'] = $fecha;
    	        $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['td'] = $td;
    	        $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['documento'] = $documento;
	        	if ($anulado != 'S') {
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['ruc'] = $ruc;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = $razsocial;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['tc'] = $tc;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['neto'] = $neto;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['impuestos'] = $impuestos;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['total'] = $total;
			    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['neto'] += $neto;
		    		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'] += $impuestos;
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['total'] += $total;
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['neto'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['neto']+$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['neto'];
		    		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['impuestos'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['impuestos']+$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'];
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['total'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['total']+$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['total'];
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['totales']['neto'] += $neto;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['totales']['impuestos'] += $impuestos;
		    		$ventas['documentos']['tipos'][$td_descripcion]['totales']['total'] += $total;
		    		$ventas['documentos']['totales']['neto']+=$neto;
		    		$ventas['documentos']['totales']['impuestos']+=$impuestos;
		    		$ventas['documentos']['totales']['total']+=$total;
				}
        		else {
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = "A N U L A D A";
    			}
				$cuenta++;
	    	}
	    	/*fin boleta manual*/
	    	
	    	/**nota de credtio manual*/
	    	$sql = "
				SELECT	to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
					'07' as td,
		        	trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        	comp.ch_fac_ruc as cli_ruc,
		        	iif(comp.ch_fac_nombreclie='',cli.cli_razsocial,comp.ch_fac_nombreclie) as cli_razsocial,
		        	cab.nu_tipocambio,
		        	cab.nu_fac_valorbruto as neto,
		        	cab.nu_fac_impuesto1 as impuestos,
		        	cab.nu_fac_valortotal as total,
		        	cab.ch_fac_anulado,
		        	cab.ch_fac_moneda as moneda
				FROM fac_ta_factura_cabecera cab inner join fac_ta_factura_complemento comp on 
					cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
					and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento
					inner join int_clientes cli on comp.cli_codigo=cli.cli_codigo
 				WHERE
			    	cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
					and cab.ch_fac_tipodocumento='20' and cab.ch_almacen='".$estacion."'";
	    	$sql .= " union SELECT	to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
					'07' as td,
		        	trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        	cli.cli_ruc,
		        	cli.cli_razsocial,
		        	cab.nu_tipocambio,
		        	cab.nu_fac_valorbruto as neto,
		        	cab.nu_fac_impuesto1 as impuestos,
		        	cab.nu_fac_valortotal as total,
		        	cab.ch_fac_anulado,
		        	cab.ch_fac_moneda as moneda 
		        FROM fac_ta_factura_cabecera cab inner join int_clientes cli on cab.cli_codigo=cli.cli_codigo 
		        where cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
					and cab.ch_fac_tipodocumento='20' and not exists(select comp.* from fac_ta_factura_complemento comp
							where cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
							and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento) 
				and cab.ch_almacen='".$estacion."' order by documento, fecha;";
	    	//print_r($ventas);
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
    	        $td_descripcion = $a['td'];
    	        $moneda = $a['moneda'];
				if ($moneda=='02'){
    	        	$neto=$neto*$tc;
    	        	$total=$total*$tc;
    	        	$impuestos=$impuestos*$tc;
    	        }
	        	if ($cuenta > 60) {
        	    	$cuenta = 0;
            	    $pagina++;
    			}
		        if ($old_td != $td) {
            	    $old_td = $td;
            	    $pagina++;
            	    $cuenta = 0;
        		}
	        	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['fecha'] = $fecha;
    	        $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['td'] = $td;
    	        $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['documento'] = $documento;
	        	if ($anulado != 'S') {
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['ruc'] = $ruc;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = $razsocial;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['tc'] = $tc;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['neto'] = $neto;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['impuestos'] = $impuestos;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['total'] = $total;
			    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['neto'] += $neto;
		    		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'] += $impuestos;
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['total'] += $total;
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['neto'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['neto'] + $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['neto'];
		    		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['impuestos'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['impuestos'] + $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'];
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['total'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['total'] + $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['total'];
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['totales']['neto'] += $neto;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['totales']['impuestos'] += $impuestos;
		    		$ventas['documentos']['tipos'][$td_descripcion]['totales']['total'] += $total;
		    		$ventas['documentos']['totales']['neto']-=$neto;
		    		$ventas['documentos']['totales']['impuestos']-=$impuestos;
		    		$ventas['documentos']['totales']['total']-=$total;
				}
        		else {
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = "A N U L A D A";
    			}
				$cuenta++;
	    	}
	    	/*fin de nota de credito manual*/
	    	
	    	/*nota de debito */
	    	$sql = "SELECT to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
					'08' as td,
		        	trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        	comp.ch_fac_ruc as cli_ruc,
		        	iif(comp.ch_fac_nombreclie='',cli.cli_razsocial,comp.ch_fac_nombreclie) as cli_razsocial,
		        	cab.nu_tipocambio,
		        	cab.nu_fac_valorbruto as neto,
		        	cab.nu_fac_impuesto1 as impuestos,
		        	cab.nu_fac_valortotal as total,
		        	cab.ch_fac_anulado,
		        	cab.ch_fac_moneda as moneda
				FROM fac_ta_factura_cabecera cab inner join fac_ta_factura_complemento comp on 
					cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
					and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento
					inner join int_clientes cli on comp.cli_codigo=cli.cli_codigo
 				WHERE
			    	cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
					and cab.ch_fac_tipodocumento='11' and cab.ch_almacen='".$estacion."' 
				";
	    	$sql .= " union SELECT to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
					'08' as td,
		        	trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        	cli.cli_ruc,
		        	cli.cli_razsocial,
		        	cab.nu_tipocambio,
		        	cab.nu_fac_valorbruto as neto,
		        	cab.nu_fac_impuesto1 as impuestos,
		        	cab.nu_fac_valortotal as total,
		        	cab.ch_fac_anulado,
		        	cab.ch_fac_moneda as moneda
		        FROM	fac_ta_factura_cabecera cab inner join int_clientes cli on cab.cli_codigo=cli.cli_codigo
				WHERE
			    	cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
					and cab.ch_fac_tipodocumento='11' and not exists(select comp.* from fac_ta_factura_complemento comp
							where cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
							and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento) 
				    and cab.ch_almacen='".$estacion."'	order by documento, fecha		
					";
	    			
		    	//print_r($ventas);
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
    	        $td_descripcion = $a['td'];
    	        $moneda = $a['moneda'];
				if ($moneda=='02'){
    	        	$neto=$neto*$tc;
    	        	$total=$total*$tc;
    	        	$impuestos=$impuestos*$tc;
    	        }
	        	if ($cuenta > 60) {
        	    	$cuenta = 0;
            	    $pagina++;
    			}
		        if ($old_td != $td) {
            	    $old_td = $td;
            	    $pagina++;
            	    $cuenta = 0;
        		}
	        	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['fecha'] = $fecha;
    	        $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['td'] = $td;
    	        $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['documento'] = $documento;
	        	if ($anulado != 'S') {
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['ruc'] = $ruc;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = $razsocial;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['tc'] = $tc;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['neto'] = $neto;
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['impuestos'] = $impuestos;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['total'] = $total;
			    	$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['neto'] += $neto;
		    		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'] += $impuestos;
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['total'] += $total;
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['neto'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['neto'] + $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['neto'];
		    		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['impuestos'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['impuestos'] + $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'];
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['acumulados']['total'] = $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['total'] + $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['totales']['total'];
		   	 		$ventas['documentos']['tipos'][$td_descripcion]['totales']['neto'] += $neto;
        	    	$ventas['documentos']['tipos'][$td_descripcion]['totales']['impuestos'] += $impuestos;
		    		$ventas['documentos']['tipos'][$td_descripcion]['totales']['total'] += $total;
		    		$ventas['documentos']['totales']['neto']+=$neto;
		    		$ventas['documentos']['totales']['impuestos']+=$impuestos;
		    		$ventas['documentos']['totales']['total']+=$total;
				}
        		else {
            	    $ventas['documentos']['tipos'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = "A N U L A D A";
    			}
				$cuenta++;
	    	}
	    	/*fin de nota de debito*/
	 
	    	
	    		
	    	/*factura oficina*/
	    	$sql = "SELECT to_char(cab.dt_fac_fecha, 'dd/mm/yyyy') as fecha,
					'01' as td,
		        	trim(cab.ch_fac_seriedocumento)||cab.ch_fac_numerodocumento as documento,
		        	comp.ch_fac_ruc as cli_ruc,
		        	comp.ch_fac_nombreclie as cli_razsocial,
		        	cab.nu_tipocambio,
		        	cab.nu_fac_valorbruto+coalesce(cab.nu_fac_descuento1,0) as totalcondescuento,
		        	cab.nu_fac_descuento1 as descuentos,
		        	cab.nu_fac_valorbruto as neto,
		        	cab.nu_fac_impuesto1 as impuestos,
		        	cab.nu_fac_valortotal as total,
		        	cab.ch_fac_anulado,
		        	cab.ch_fac_moneda as moneda
				FROM fac_ta_factura_cabecera cab inner join fac_ta_factura_complemento comp on 
					cab.cli_codigo=comp.cli_codigo and cab.ch_fac_seriedocumento=comp.ch_fac_seriedocumento
					and cab.ch_fac_numerodocumento=comp.ch_fac_numerodocumento and cab.ch_fac_tipodocumento=comp.ch_fac_tipodocumento
					inner join int_clientes cli on comp.cli_codigo=cli.cli_codigo
 				WHERE
			    	cab.dt_fac_fecha BETWEEN '" .$desde_ano."-".$desde_mes."-".$desde_dia."' AND '" . $hasta_ano."-".$hasta_mes."-".$hasta_dia."'
					and cab.ch_fac_tipodocumento='10' and cab.ch_fac_seriedocumento='001' and cab.ch_almacen='".$estacion."'
				ORDER BY cab.dt_fac_fecha, 
					cab.ch_fac_tipodocumento,
		        	cab.ch_fac_seriedocumento,
		        	cab.ch_fac_numerodocumento;";
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
				$descuento = $a['descuentos'];
				$totalcondescuento=$a['totalcondescuento'];
		        $anulado = $a['ch_fac_anulado'];
    	        $td_descripcion = $a['td'];
    	        $moneda = $a['moneda'];
				if ($moneda=='02'){
    	        	$neto=$neto*$tc;
    	        	$total=$total*$tc;
    	        	$impuestos=$impuestos*$tc;
    	        	$descuento=$descuento*$tc;
    	        	$totalcondescuento=$totalcondescuento*$tc;
    	        }
	        	if ($cuenta > 60) {
        	    	$cuenta = 0;
            	    $pagina++;
    			}
		        if ($old_td != $td) {
            	    $old_td = $td;
            	    $pagina++;
            	    $cuenta = 0;
        		}
	        	$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['fecha'] = $fecha;
    	        $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['td'] = $td;
    	        $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['documento'] = $documento;
	        	if ($anulado != 'S') {
            	    $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['ruc'] = $ruc;
        	    	$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = $razsocial;
            	    $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['tc'] = $tc;
            	    $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['descuento'] = $descuento;
            	    $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['totaldescuento'] = $totalcondescuento;
            	    $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['neto'] = $neto;
            	    $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['impuestos'] = $impuestos;
        	    	$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['total'] = $total;
        	    	$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['totaldescuento'] += $totalcondescuento;
        	    	$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['descuento'] += $descuento;
			    	$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['neto'] += $neto;
		    		$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'] += $impuestos;
		   	 		$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['total'] += $total;
		   	 		$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['acumulados']['totaldescuento'] = $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['totaldescuento']+$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['totaldescuento'];
		   	 		$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['acumulados']['descuento'] = $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['descuento']+$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['descuento'];
			    	$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['acumulados']['neto'] = $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['neto']+$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['neto'];
		    		$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['acumulados']['impuestos'] = $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['impuestos']+$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['impuestos'];
		   	 		$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['acumulados']['total'] = $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina-1]['acumulados']['total']+$ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['totales']['total'];
		   	 		$ventas['documentos']['facturas'][$td_descripcion]['totales']['totaldescuento'] += $totalcondescuento;
        	    	$ventas['documentos']['facturas'][$td_descripcion]['totales']['descuento'] += $descuento;
		    		$ventas['documentos']['facturas'][$td_descripcion]['totales']['neto'] += $neto;
        	    	$ventas['documentos']['facturas'][$td_descripcion]['totales']['impuestos'] += $impuestos;
		    		$ventas['documentos']['facturas'][$td_descripcion]['totales']['total'] += $total;
				}
        		else {
            	    $ventas['documentos']['facturas'][$td_descripcion]['paginas'][$pagina]['documentos'][$documento]['razsocial'] = "A N U L A D A";
    			}
				$cuenta++;
	    	}
	 }		
	    	print_r($ventas['documentos']['facturas']);
	    	
	/*-------------------------> Parte 6: Generacion del resumen <--------------------------*/
	
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
	
	/*-------------------------------> Fin de parte 6 <----------------------------------*/
	//print_r($ventas);
	return $ventas;
    }
}

