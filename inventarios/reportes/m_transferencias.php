<?php

class TransferenciasModel extends Model
{
    function search($desde, $hasta, $bActualizar)
    {
	global $sqlca;

	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");
	
	$sql = "SELECT
		    m.mov_numero,
		    m.mov_tipdocuref,
		    m.mov_docurefe,
		    m.mov_almacen,
		    m.mov_almadestino,
		    m.mov_almaorigen,
		    trim(m.tran_codigo),
		    m.mov_cantidad,
		    m.mov_costototal,
		    m.mov_fecha,
		    a.art_codigo,
		    trim(a.art_tipo)||'-'||trim(tab.tab_descripcion)
		FROM
		    inv_movialma m,
		    int_articulos a,
		    int_tabla_general tab
		WHERE
			m.mov_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . " 23:59:59'
		    AND m.tran_codigo in ('07','08','27','28')
		    AND a.art_codigo=m.art_codigo
		    AND tab.tab_tabla='21'
		    AND tab.tab_elemento=a.art_tipo
		ORDER BY
		    a.art_tipo,
		    m.mov_almacen,
		    m.mov_fecha";

	if ($sqlca->query($sql) < 0) return false;
	
	$movimientos = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $mov_numero = $a[0];
	    $mov_tipdocuref = $a[1];
	    $mov_docurefe = $a[2];
	    $mov_almacen = $a[3];
	    $mov_almadestino = $a[4];
	    $mov_almaorigen = $a[5];
	    $tran_codigo = $a[6];
	    $mov_cantidad = $a[7];
	    $mov_costototal = $a[8];
	    $mov_fecha = $a[9];
	    $art_codigo = $a[10];
	    $art_tipo = $a[11];
	    
	    $key = str_pad($mov_tipdocuref, 2) . str_pad($mov_docurefe, 10) . str_pad($art_codigo, 13) . str_pad($mov_almacen, 3);
	    
	    $movimientos[$key]['mov_numero'] = $mov_numero;
	    $movimientos[$key]['mov_tipdocuref'] = $mov_tipdocuref;
	    $movimientos[$key]['mov_docurefe'] = $mov_docurefe;
	    $movimientos[$key]['mov_almacen'] = $mov_almacen;
	    $movimientos[$key]['mov_almadestino'] = str_pad(substr($mov_almadestino, 1, 2), 3, "0", STR_PAD_LEFT);
	    $movimientos[$key]['mov_almaorigen'] = str_pad(substr($mov_almaorigen, 1, 2), 3, "0", STR_PAD_LEFT);
	    $movimientos[$key]['tran_codigo'] = $tran_codigo;
	    $movimientos[$key]['mov_cantidad'] = $mov_cantidad;
	    $movimientos[$key]['mov_costototal'] = $mov_costototal;
	    $movimientos[$key]['mov_fecha'] = $mov_fecha;
	    $movimientos[$key]['art_codigo'] = $art_codigo;
	    $movimientos[$key]['art_tipo'] = $art_tipo;
	    
	}
	
	/* Aparear movimientos */
	foreach ($movimientos as $key => $movimiento) {
	    if ($movimiento['tran_codigo'] == '08' || $movimiento['tran_codigo'] == '28') $mov_almacen = $movimiento['mov_almadestino'];
	    else $mov_almacen = $movimiento['mov_almaorigen'];
	
	    $newkey = str_pad($movimiento['mov_tipdocuref'], 2) . str_pad($movimiento['mov_docurefe'], 10) . str_pad($movimiento['art_codigo'], 13) . str_pad($mov_almacen, 3);

	    if (isset($movimientos[$newkey])) {
		$movimientos[$key]['estado'] = "E";
		$movimientos[$key]['par'] = $newkey;

		// Al menos existe el movimiento opuesto. Falta chequear cantidades y montos

		if ($movimiento['mov_cantidad'] == $movimientos[$newkey]['mov_cantidad'] && $movimiento['mov_costototal'] == $movimientos[$newkey]['mov_costototal']) {
		    $movimientos[$key]['estado'] = "OK";
		}
	    }
	    else $movimientos[$key]['estado'] = "NE";
	}

	/* Explicacion del campo 'estado' de un movimiento:
		E=existe, pero no coinciden cantidades o costos (para el reporte de consistencia)
		NE=No existe (para reporte de "sin origen" o "sin destino"
		OK=Coincide todo (para reporte de transferencias de origen o destino)
	*/
	$entradas = Array();
	$salidas = Array();

	/* Registrar transferencias con E/S correctos */
	foreach ($movimientos as $key => $movimiento) {
	    if ($movimiento['estado'] == "OK") {
		if ($movimiento['tran_codigo'] == '08' || $movimiento['tran_codigo'] == '28') {
		    $salidas['tipos'][$movimiento['art_tipo']][$movimiento['mov_almacen']] += $movimiento['mov_costototal'];
		    $salidas['tipos'][$movimiento['art_tipo']]['total'] += $movimiento['mov_costototal'];
		    $salidas['tipos'][$movimiento['art_tipo']]['art_tipo'] = $movimiento['art_tipo'];
		    $salidas['total'][$movimiento['mov_almacen']] += $movimiento['mov_costototal'];
		    $salidas['total']['total'] += $movimiento['mov_costototal'];
		}
		else {
		    $entradas['tipos'][$movimiento['art_tipo']][$movimiento['mov_almacen']] += $movimiento['mov_costototal'];
		    $entradas['tipos'][$movimiento['art_tipo']]['total'] += $movimiento['mov_costototal'];
		    $entradas['tipos'][$movimiento['art_tipo']]['art_tipo'] = $movimiento['art_tipo'];
		    $entradas['total'][$movimiento['mov_almacen']] += $movimiento['mov_costototal'];
		    $entradas['total']['total'] += $movimiento['mov_costototal'];
		}
	    }
	}
	
//	echo "entradas\n";
//	var_dump($entradas);
//	echo "salidas\n";
//	var_dump($salidas);
//	echo "--->";

	$err_salida = Array();
	$err_entrada = Array();

	/* Registrar transferencias sin destino (08/28) o sin origen (07/27) */
	foreach ($movimientos as $key => $movimiento) {
	    if ($movimiento['estado'] == "NE") {
		if ($movimiento['tran_codigo'] == '08' || $movimiento['tran_codigo'] == '28') {
		    $err_salida['tipos'][$movimiento['art_tipo']][$movimiento['mov_almacen']] += $movimiento['mov_costototal'];
		    $err_salida['tipos'][$movimiento['art_tipo']]['total'] += $movimiento['mov_costototal'];
		    $err_salida['tipos'][$movimiento['art_tipo']]['art_tipo'] = $movimiento['art_tipo'];
		    $err_salida['total'][$movimiento['mov_almacen']] += $movimiento['mov_costototal'];
		    $err_salida['total']['total'] += $movimiento['mov_costototal'];
		}
		else {
		    $err_entrada['tipos'][$movimiento['art_tipo']][$movimiento['mov_almacen']] += $movimiento['mov_costototal'];
		    $err_entrada['tipos'][$movimiento['art_tipo']]['total'] += $movimiento['mov_costototal'];
		    $err_entrada['tipos'][$movimiento['art_tipo']]['art_tipo'] = $movimiento['art_tipo'];
		    $err_entrada['total'][$movimiento['mov_almacen']] += $movimiento['mov_costototal'];
		    $err_entrada['total']['total'] += $movimiento['mov_costototal'];
		}
	    }
	}

//	var_dump($movimientos);
//	echo "errores salida\n";
//	var_dump($err_salida);
//	echo "errores entrada\n";
//	var_dump($err_entrada);	
//	echo "-->";

	$consistencia = Array();
	$i = 0;

	/* Armar consistencia */
	foreach ($movimientos as $key => $movimiento) {
	    if ($movimiento['estado'] == "E" && ($movimiento['tran_codigo'] == '08' || $movimiento['tran_codigo'] == '28')) {
		if (floatval($movimiento['mov_cantidad']) != floatval($movimientos[$movimiento['par']]['mov_cantidad'])) {
		    $consistencia[$i] = "Error cantidades: Guia:" . $movimiento['mov_docurefe'] . " Item:" . $movimiento['art_codigo']
					. " Origen: " . $movimiento['tran_codigo'] . $movimiento['mov_numero'] . " " . substr($movimiento['mov_fecha'], 0, 10)
					. " " . $movimiento['mov_cantidad'] . " Destino: " . $movimientos[$movimiento['par']]['tran_codigo'] . $movimientos[$movimiento['par']]['mov_numero']
					. " " . substr($movimientos[$movimiento['par']]['mov_fecha'], 0, 10) . " " . $movimientos[$movimiento['par']]['mov_cantidad'];
		    $i++;
		}
		if (floatval($movimiento['mov_costototal']) != floatval($movimientos[$movimiento['par']]['mov_costototal'])) {
		    $consistencia[$i] = "Error costo: Guia:" . $movimiento['mov_docurefe'] . " Item:" . $movimiento['art_codigo']
					. " Origen: " . $movimiento['tran_codigo'] . $movimiento['mov_numero'] . " " . substr($movimiento['mov_fecha'], 0, 10)
					. " " . $movimiento['mov_costototal'] . " Destino: " . $movimientos[$movimiento['par']]['tran_codigo'] . $movimientos[$movimiento['par']]['mov_numero']
					. " " . substr($movimientos[$movimiento['par']]['mov_fecha'], 0, 10) . " " . $movimientos[$movimiento['par']]['mov_costototal'];
		    $i++;
		}
	    }
	}

//	echo "consistencia\n";
//	var_dump($consistencia);
//	echo "-->";	
	$resultado = Array();
	$resultado['entradas'] = $entradas;
	$resultado['salidas'] = $salidas;
	$resultado['err_entrada'] = $err_entrada;
	$resultado['err_salida'] = $err_salida;
	$resultado['consistencia'] = $consistencia;
	
//	echo "RESULTADO:\n";
//	var_dump($resultado);
	return $resultado;
    }
    
    function obtenerListaAlmacenes($err) {
	global $sqlca;
	
	$alma = Array();
	$resultado = Array();

	foreach($err['tipos'] as $art_tipo => $tipo) {
	    foreach($tipo as $ch_almacen => $monto) {
//		if ($monto != 0) 
		    $alma[$ch_almacen] = "foo";
	    }
	}

	$sql = "SELECT
		    ch_almacen,
		    trim(ch_nombre_almacen)
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_almacen IN (";
	$i = 0;
	foreach($alma as $ch_almacen=>$foo) {
	    if ($i > 0) $sql .= ",";
	    $sql .= "'" . pg_escape_string($ch_almacen) . "'";
	    $i++;
	}

	$sql .= ")
		ORDER BY
		    ch_almacen
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    $resultado[$a[0]] = $a[1];
	}

	return $resultado;
    }    
}


