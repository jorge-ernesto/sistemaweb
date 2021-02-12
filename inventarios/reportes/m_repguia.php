<?php

class RepGuiaModel extends Model
{
    function obtenerTiposDocumentos()
    {
	global $sqlca;
	
	$sql = "SELECT
		    tab_elemento,
		    tab_descripcion
		FROM
		    int_tabla_general
		WHERE
			tab_tabla='08'
		    AND tab_elemento!='000000'
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $result[substr($a[0], 4, 2)] = substr($a[0], 4, 2) . " - " . $a[1];
	}
	
	return $result;
    }
    
    function search($tipo, $documento)
    {
	global $sqlca;
	
	$sql = "SELECT
		    m.mov_almacen,
		    m.tran_codigo,
		    m.mov_numero,
		    m.mov_fecha,
		    m.com_num_compra,
		    m.mov_entidad,
		    m.art_codigo,
		    a.art_descripcion,
		    m.mov_cantidad,
		    m.mov_costounitario,
		    m.mov_costototal,
		    m.mov_almaorigen,
		    m.mov_almadestino,
		    m.mov_tipdocuref,
		    m.mov_docurefe
		FROM
		    inv_movialma m,
		    int_articulos a
		WHERE
		    m.mov_tipdocuref = '" . pg_escape_string($tipo) . "'
		    AND m.mov_docurefe = '" . pg_escape_string($documento) . "'
		    AND a.art_codigo = m.art_codigo
		ORDER BY
		    m.mov_almacen,
		    m.tran_codigo,
		    m.mov_numero
		;
		";

	if ($sqlca->query($sql) < 0) return false;

	$resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();
		    
            $mov_almacen = $a[0];
            $tran_codigo = $a[1];
            $mov_numero = $a[2];
            $mov_fecha = $a[3];
            $com_num_compra = $a[4];
            $mov_entidad = $a[5];
            $art_codigo = $a[6];
            $art_descripcion = $a[7];
            $mov_cantidad = $a[8];
            $mov_costounitario = $a[9];
            $mov_costototal = $a[10];
            $mov_almaorigen = $a[11];
            $mov_almadestino = $a[12];
            $mov_tipdocuref = $a[13];
            $mov_docurefe = $a[14];

            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['mov_fecha'] = $mov_fecha;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['com_num_compra'] = $com_num_compra;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['mov_entidad'] = $mov_entidad;

            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['art_codigo'] = $art_codigo;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['art_descripcion'] = $art_descripcion;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_cantidad'] = $mov_cantidad;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_costounitario'] = $mov_costounitario;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_costototal'] = $mov_costototal;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_almaorigen'] = $mov_almaorigen;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_almadestino'] = $mov_almadestino;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_tipdocuref'] = $mov_tipdocuref;
            $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_docurefe'] = $mov_docurefe;
        }

	return $resultado;
    }
}