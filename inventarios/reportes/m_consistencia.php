<?php

class ConsistenciaModel extends Model
{
    function search($desde, $hasta, $almacen)
    {
	global $sqlca;
	
	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

	$sql = "SELECT
		    m.mov_almacen,
		    trim(m.tran_codigo),
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
			m.mov_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . " 23:59:59'
		    AND m.tran_codigo not in ('25','45')
		    AND m.art_codigo=a.art_codigo
		";

	if ($almacen != "TODAS")
	    $sql .= "	AND m.mov_almacen='" . pg_escape_string($almacen) . "' ";
	
	$sql .= "ORDER BY
		    m.mov_almacen,
		    m.tran_codigo,
		    m.mov_numero,
		    m.art_codigo
		;
		";
	//echo $sql;
	if ($sqlca->query($sql) < 0) return null;
	
	$resultado = Array();

	$old_alma = "";
	$old_tran = "";
	$old_nume = "";
	
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


	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['art_codigo'] = $art_codigo;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['art_descripcion'] = $art_descripcion;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_cantidad'] = $mov_cantidad;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_costounitario'] = $mov_costounitario;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_costototal'] = $mov_costototal;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_almaorigen'] = $mov_almaorigen;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_almadestino'] = $mov_almadestino;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_tipdocuref'] = $mov_tipdocuref;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['articulos'][$i]['mov_docurefe'] = $mov_docurefe;

	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['mov_fecha'] = $mov_fecha;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['com_num_compra'] = $com_num_compra;
	    $resultado['almacenes'][$mov_almacen]['tipos'][$tran_codigo]['movimientos'][$mov_numero]['mov_entidad'] = $mov_entidad;
	    
	}
	
	return $resultado;
    }
    
    function obtenerDescripcionDocumento($codigo) {
	global $sqlca;
	
	$sql = "SELECT
		    num_descdocumento
		FROM
		    int_num_documentos
		WHERE
		    num_tipdocumento='" . pg_escape_string($codigo) . "'
		;
		";
	if ($sqlca->query($sql) < 0) return "";
	
	$a = $sqlca->fetchRow();
	
	return $codigo . " - " . $a[0];
    }
    
    function obtenerCuentaTransacciones($desde, $hasta, $almacen, $tipo)
    {
	global $sqlca;

	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");
	
	$sql = "SELECT
		    count(*)
		FROM
		    inv_movialma
		WHERE
			m.mov_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . " 23:59:59'
		    AND tran_codigo='" . pg_escape_string($tipo) . "'
		";
	if ($almacen != "TODAS") {
	    $sql .= "	AND mov_almacen='" . pg_escape_string($almacen) . "' ";
	}
	
	$sql .= ";";

	if ($sqlca->query($sql) < 0) return 0;
	
	$a = $sqlca->fetchRow();
	return $a[0];
    }
    
}

?>