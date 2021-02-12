<?php
class TransDetModel extends Model
{
    function search($desde, $hasta, $estacion, $costos)
    {
	global $sqlca;

	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

	$sql = "SELECT
		    trim(mov.tran_codigo) as tran_codigo,
		    trim(mov.mov_numero) as mov_numero,
		    trim(mov.mov_docurefe) as mov_docurefe,
		    to_char(mov.mov_fecha, 'YYYY-MM-DD') as mov_fecha,
		    trim(mov.art_codigo) as art_codigo,
		    trim(art.art_descripcion) as art_descripcion,
		    mov.mov_cantidad,
		    mov.mov_almadestino,
		    mov.mov_almaorigen,
		    mov.mov_costototal,
		    trim(tab.tab_desc_breve) as art_tipo,
		    alma1.ch_nombre_breve_almacen as mov_nombre_almadestino,
		    alma2.ch_nombre_breve_almacen as mov_nombre_almaorigen
		FROM
		    inv_movialma mov,
		    int_articulos art,
		    int_tabla_general tab,
		    inv_ta_almacenes alma1,
		    inv_ta_almacenes alma2
		WHERE
			mov.mov_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . " 23:59:59'
		    AND mov.tran_codigo IN ('07','08','27','28','18')
		";
	if ($estacion!="TODAS") {
	    $sql .= "    AND (mov.mov_almaorigen like '%" . pg_escape_string(substr($estacion, 1, 2)) . "' OR mov.mov_almadestino like '%" . pg_escape_string(substr($estacion, 1, 2)) . "')
		";
	}

	$sql .="    AND art.art_codigo=mov.art_codigo
		    AND tab.tab_tabla='21'
		    AND tab.tab_elemento=art.art_tipo
		    AND alma1.ch_almacen=mov.mov_almadestino
		    AND alma2.ch_almacen=mov.mov_almaorigen
		ORDER BY
		    mov.tran_codigo,
		    mov.mov_almacen,
		    mov.mov_numero
		;
		";											    

	if ($sqlca->query($sql) < 0) return false;
	
	$transacciones = Array();

	/* cargar el array con los movimientos encontrados */
	for($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $tran_codigo = $a[0];
	    $mov_numero = $a[1];
	    $mov_docurefe = $a[2];
	    $mov_fecha = $a[3];
	    $art_codigo = $a[4];
	    $art_descripcion = $a[5];
	    $mov_cantidad = $a[6];
	    $mov_almadestino = str_pad(substr($a[7], 1, 2), 3, "0", STR_PAD_LEFT);;
	    $mov_almaorigen = str_pad(substr($a[8], 1, 2), 3, "0", STR_PAD_LEFT);;
	    $mov_costototal = $a[9];
	    $art_tipo = $a[10];
	    $mov_nombre_almadestino = $a[11];
	    $mov_nombre_almaorigen = $a[12];
	    
	    switch($tran_codigo) {
		case '07':
		case '27':
		    $nombre = "entrada";
		    $mov_almacen = $mov_almadestino;
		    break;
		case '08':
		case '28':
		    $nombre = "salida";
		    $mov_almacen = $mov_almaorigen;
		    break;
		case '18':
		    $nombre = "regularizacion";
		    $mov_almacen = $mov_almadestino;
		    break;
	    }
	    $key = $mov_almacen.$mov_docurefe.$art_codigo;
	    $transacciones[$nombre][$key]['mov_numero'] = $mov_numero;
	    $transacciones[$nombre][$key]['mov_docurefe'] = $mov_docurefe;
	    $transacciones[$nombre][$key]['mov_fecha'] = $mov_fecha;
	    $transacciones[$nombre][$key]['art_codigo'] = $art_codigo;
	    $transacciones[$nombre][$key]['art_descripcion'] = $art_descripcion;
	    $transacciones[$nombre][$key]['mov_cantidad'] = $mov_cantidad;
	    $transacciones[$nombre][$key]['mov_almadestino'] = $mov_almadestino;
	    $transacciones[$nombre][$key]['mov_almaorigen'] = $mov_almaorigen;
	    $transacciones[$nombre][$key]['mov_costototal'] = $mov_costototal;
	    $transacciones[$nombre][$key]['art_tipo'] = $art_tipo;
	    $transacciones[$nombre][$key]['mov_nombre_almadestino'] = /*$mov_almadestino . ' - ' .*/ $mov_nombre_almadestino;
	    $transacciones[$nombre][$key]['mov_nombre_almaorigen'] = /*$mov_almaorigen . ' - ' .*/ $mov_nombre_almaorigen;
	}
	
	/* liquidar regularizaciones */
	foreach($transacciones['regularizacion'] as $key=>$movimiento) {
	    /* 
	     * NOTA IMPORTANTE: el movimiento regularizacion es un movimiento de entrada,
	     * pero sin embargo se utiliza tambien como movimiento de salida poniendo un
	     * signo negativo en el campo de cantidad. Esto debe ser tomado en cuenta para
	     * este proceso.
	     */

	    /*
	     * podria sacar el campo almacen de mov_almacen, pero podria fallar en caso
	     * que este campo no coincida con el del origen/destino. Esto se da en el 
	     * caso de la oficina cuando entran como oficina principal y hacen moficaciones
	     * a los distintos almacenes.
	     */
	    if ($movimiento['mov_cantidad'] > 0) {
		$almacen = $movimiento['mov_almadestino'];
	    }
	    else {
		$almacen = $movimiento['mov_almaorigen'];
	    }
	    
	    $key = $almacen.$movimiento['mov_docurefe'].$movimiento['art_codigo'];

	    /* decide si el movimiento lo aplica a una entrada o a una salida */
	    if (isset($transacciones['salida'][$key])) {
		$transacciones['salida'][$key]['mov_cantidad'] -= $movimiento['mov_cantidad'];
	    }
	    else if (isset($transacciones['entrada'][$key])) {
		$transacciones['entrada'][$key]['mov_cantidad'] += $movimiento['mov_cantidad'];
	    }

	    /* actualiza el costo del movimiento */
	    if ($movimiento['cantidad'] > 0)
	        if (isset($transacciones['salida'][$key])) $transacciones['salida'][$key]['mov_costototal'] += $movimiento['mov_costototal'];
	    else
	        if (isset($transacciones['salida'][$key])) $transacciones['salida'][$key]['mov_costototal'] -= $movimiento['mov_costototal'];
	}

	/* aparear salidas con ingresos y verificar costos y cantidades */
	foreach($transacciones['salida'] as $key=>$movimiento) {
	    $new_key = $movimiento['mov_almadestino'].$movimiento['mov_docurefe'].$movimiento['art_codigo'];

	    /* si existe el movimiento de ingreso que coincida en documento, almacen y codigo */
	    if (isset($transacciones['entrada'][$new_key])) {
		/* encontrado */

		/* marcar a ambos movimientos la existencia del otro */
		$transacciones['entrada'][$new_key]['salida'] = $key;
		$transacciones['salida'][$key]['entrada'] = $new_key;
		
		/* verifica coincidencia de costos de aplicarse el caso*/
		if ($costos == 'S' && $transacciones['entrada'][$new_key]['mov_costototal'] != $movimiento['mov_costototal']) {
		    $transacciones['salida'][$key]['dif_costo'] = $transacciones['ingreso'][$new_key]['mov_costototal'] - $movimiento['mov_costototal'];
		    $transacciones['entrada'][$new_key]['dif_costo'] = $transacciones['ingreso'][$new_key]['mov_costototal'] - $movimiento['mov_costototal'];
		}
		
		/* verifica coincidencia de cantidades */
		if ($transacciones['entrada'][$new_key]['mov_cantidad'] != $movimiento['mov_cantidad']) {
		    $transacciones['salida'][$key]['dif_cantidad'] = $transacciones['entrada'][$new_key]['mov_cantidad'] - $movimiento['mov_cantidad'];
		    $transacciones['entrada'][$new_key]['dif_cantidad'] = $transacciones['entrada'][$new_key]['mov_cantidad'] - $movimiento['mov_cantidad'];
		}
	    }
	}
	

	/* quitar movimientos correctos y regularizaciones */
	foreach($transacciones['salida'] as $key=>$movimiento) {
	    if (isset($movimiento['entrada']) && !isset($movimiento['dif_costo']) && !isset($movimiento['dif_cantidad'])) {
		unset($transacciones['salida'][$key]);
	    }
	}
	foreach($transacciones['entrada'] as $key=>$movimiento) {
	    if (isset($movimiento['salida']) && !isset($movimiento['dif_costo']) && !isset($movimiento['dif_cantidad'])) {
		unset($transacciones['entrada'][$key]);
	    }
	}
	unset($transacciones['regularizacion']);


	/* ----------------> comienzo a preparar el resultado <-------------------- */
	$result = Array();
	$results = Array();

	/* Divide las salidas por almacen de salida y numero de documento,
	 * ademas pone en el mismo nivel a las entradas relacionadas con ese
	 * movimiento
	 */
	foreach($transacciones['salida'] as $key=>$movimiento) {
	    $result['salidas'][$movimiento['mov_nombre_almaorigen']]['documentos'][$movimiento['mov_docurefe']]['salidas'][$movimiento['art_codigo']] = $movimiento;
	    if (isset($transacciones['entrada'][$movimiento['entrada']]))
		$result['salidas'][$movimiento['mov_nombre_almaorigen']]['documentos'][$movimiento['mov_docurefe']]['entradas'][$movimiento['entrada']] = $transacciones['entrada'][$movimiento['entrada']];
	}

	/*
	 * Divide las entradas por almacen de entrada y numero de documento.
	 */
	foreach($transacciones['entrada'] as $key=>$movimiento) {
	    $result['entradas'][$movimiento['mov_nombre_almadestino']]['documentos'][$movimiento['mov_docurefe']]['entradas'][$movimiento['art_codigo']] = $movimiento;
	}

	/* ------------> Aqui es donde comienza el problema en serio :D <----------- */
	
	/*
	 * Comenzamos recorriendo las salidas por almacen
	 */
	foreach($result['salidas'] as $ch_almacen=>$documentos) {
	    
	    /*
	     * Recorremos los documentos de referencia de ese almacen
	     */
	    foreach($documentos['documentos'] as $mov_docurefe=>$documento) {
		$i = 0;
		
		/*
		 * Recorremos los movimientos de este documento, es aqui donde comienza
		 * realmente la carga de datos en el array
		 */
		foreach($documento['salidas'] as $art_codigo=>$movimiento) {
		    /* esta clave la usamos para almacenar este movimiento en el array dentro del nivel de almacenes */
		    $key = $mov_docurefe.$art_codigo;

		    /* movimiento de entrada asociado con esta salida */
		    $entrada = $documento['entradas'][$movimiento['entrada']];

		    /* solo ponemos esta informacion en la primera ocurrencia del movimiento */
		    if ($i == 0) {
			$results['salidas'][$ch_almacen][$key]['mov_numero'] = $movimiento['mov_numero'];
			$results['salidas'][$ch_almacen][$key]['mov_docurefe'] = $mov_docurefe;
			$results['salidas'][$ch_almacen][$key]['mov_fecha_salida'] = $movimiento['mov_fecha'];
			$i++;
		    }

		    /* demas datos faciles de la lista */
		    $results['salidas'][$ch_almacen][$key]['art_codigo'] = $movimiento['art_codigo'];
		    $results['salidas'][$ch_almacen][$key]['art_descripcion'] = $movimiento['art_descripcion'];
		    $results['salidas'][$ch_almacen][$key]['mov_cantidad_salida'] = $movimiento['mov_cantidad'];
		    $results['salidas'][$ch_almacen][$key]['mov_nombre_almadestino'] = $movimiento['mov_nombre_almadestino'];
		    
		    /* fecha de ingreso en el almacen de destino, si la hubiera */
		    if (isset($transacciones['entrada'][$movimiento['entrada']])) {
			$results['salidas'][$ch_almacen][$key]['mov_fecha_ingreso'] = $entrada['mov_fecha'];
		    }
		    else {
			$results['salidas'][$ch_almacen][$key]['mov_fecha_ingreso'] = '*NO ING*';
		    }

		    /* diferencia por cantidad entre salida e ingreso (si la hubiera) */
		    if (isset($movimiento['dif_cantidad'])) 
			$results['salidas'][$ch_almacen][$key]['dif_cantidad'] = $movimiento['dif_cantidad'];

		    /* otros datos */
		    $results['salidas'][$ch_almacen][$key]['mov_costototal_ingreso'] = $movimiento['mov_costototal'];
		    $results['salidas'][$ch_almacen][$key]['art_tipo'] = $movimiento['art_tipo'];
		}
		
		/*
		 * Por cada movimiento de salida completo, recorremos todos los movimientos de
		 * entrada asociados a el.
		 */
		foreach($documento['entradas'] as $key=>$movimiento) {
		    $results['salidas'][$ch_almacen][$key]['mov_fecha_salida'] = 'De: ' . $movimiento['mov_almaorigen'];
		    $results['salidas'][$ch_almacen][$key]['art_codigo'] = $movimiento['art_codigo'];
		    $results['salidas'][$ch_almacen][$key]['art_descripcion'] = $movimiento['art_descripcion'];
		    $results['salidas'][$ch_almacen][$key]['mov_nombre_almadestino'] = $movimiento['mov_nombre_almadestino'];
		    $results['salidas'][$ch_almacen][$key]['mov_fecha_ingreso'] = $movimiento['mov_fecha'];
		    $results['salidas'][$ch_almacen][$key]['mov_cantidad_ingreso'] = $movimiento['mov_cantidad'];
		    if (isset($movimiento['dif_cantidad'])) 
			$results['salidas'][$ch_almacen][$key]['dif_cantidad'] = $movimiento['dif_cantidad'];

		    $results['salidas'][$ch_almacen][$key]['mov_costototal_salida'] = $movimiento['mov_costototal'];
		    $results['salidas'][$ch_almacen][$key]['art_tipo'] = $movimiento['art_tipo'];
		}
	    }
	}

	/*
	 * Recorremos las entradas por cada almacen
	 */
	foreach($result['entradas'] as $ch_almacen=>$documentos) {
	    /*
	     * Recorremos los documentos de este almacen
	     */
	    foreach($documentos['documentos'] as $mov_docurefe=>$documento) {
		$i = 0;
		/*
		 * Recorremos los movimientos de este documento
		 */
		foreach($documento['entradas'] as $art_codigo=>$movimiento) {
		    /* clave utilizada para guardar este movimiento en el array */
		    $key = $mov_docurefe.$art_codigo;
		    
		    /* solo mostramos estos datos 1 vez por cada movimiento completo */
		    if ($i == 0) {
			$results['entradas'][$ch_almacen][$key]['mov_numero'] = $movimiento['mov_numero'];
			$results['entradas'][$ch_almacen][$key]['mov_docurefe'] = $mov_docurefe;
			$i++;
		    }

		    /* demas datos */
		    $results['entradas'][$ch_almacen][$key]['mov_fecha_salida'] = 'De: ' . $movimiento['mov_almaorigen'];
		    $results['entradas'][$ch_almacen][$key]['art_codigo'] = $movimiento['art_codigo'];
		    $results['entradas'][$ch_almacen][$key]['art_descripcion'] = $movimiento['art_descripcion'];
		    $results['entradas'][$ch_almacen][$key]['mov_nombre_almadestino'] = $movimiento['mov_nombre_almadestino'];
		    $results['entradas'][$ch_almacen][$key]['mov_fecha_ingreso'] = $movimiento['mov_fecha'];
		    $results['entradas'][$ch_almacen][$key]['mov_cantidad_ingreso'] = $movimiento['mov_cantidad'];
		    $results['entradas'][$ch_almacen][$key]['mov_costototal_salida'] = $movimiento['mov_costototal'];
		    $results['entradas'][$ch_almacen][$key]['art_tipo'] = $movimiento['art_tipo'];
		}
	    }
	}

	return $results;
    }
}

