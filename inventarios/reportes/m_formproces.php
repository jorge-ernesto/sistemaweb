<?php

class FormProcesModel extends Model
{
    function obtenerProveedor($codigo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    pro_razsocial
		FROM
		    int_proveedores
		WHERE
		    pro_codigo='" . pg_escape_string($codigo) . "'
		;";
	
	if ($sqlca->query($sql, "_proveedor") < 0) return null;
	
	$a = $sqlca->fetchRow("_proveedor");
	return $a[0];
    }
    
    function obtenerCliente($codigo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    cli_razsocial
		FROM
		    int_clientes
		WHERE
		    cli_codigo='" . pg_escape_string($codigo) . "'
		;";
	if ($sqlca->query($sql, "_cliente") < 0) return null;
	
	$a = $sqlca->fetchRow("_cliente");
	return $a[0];
    }
    
    function obtenerDescripcion($codigo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    art_descripcion
		FROM
		    int_articulos
		WHERE
		    art_codigo='" . pg_escape_string($codigo) . "'
		;";
		
	if ($sqlca->query($sql, "_articulo") < 0) return null;
	
	$a = $sqlca->fetchRow("_articulo");
	return $a[0];
    }
    
    function obtenerUnidadPresentacion($codigo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    tab_descripcion
		FROM
		    int_tabla_general
		WHERE
			tab_tabla='34'
		    AND tab_elemento like '%" . pg_escape_string(trim($codigo)) . "'
		;";
	if ($sqlca->query($sql, "_unidad") < 0) return null;

	$a = $sqlca->fetchRow("_unidad");
	
	return $a[0];
    }
    
    function obtenerEstaciones()
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_almacen,
		    ch_nombre_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen='1'
		ORDER BY
		    ch_almacen
		;";
	
	if ($sqlca->query($sql, "_estaciones") < 0) return null;
	
	$resultado = Array();
	
	for ($i = 0; $i < $sqlca->numrows("_estaciones"); $i++) {
	    $array = $sqlca->fetchRow("_estaciones");
	    $resultado[$array[0]] = $array[0] . " - " . $array[1];
	}

	$resultado['TODAS'] = "Todas las estaciones";
	return $resultado;
    }
    
    function obtenerTiposFormularios()
    {
	global $sqlca;
	
	$sql = "SELECT
		    trim(tran_codigo) as tran_codigo,
		    trim(tran_descripcion) as tran_descripcion
		FROM
		    inv_tipotransa
		ORDER BY
		    tran_codigo
		;
		";
	if ($sqlca->query($sql,"_formularios") < 0) return null;
	
	$resultado = Array();
	
	$resultado['TODOS'] = "Todos los tipos";
	
	for ($i = 0; $i < $sqlca->numrows("_formularios"); $i++) {
	    $array  = $sqlca->fetchRow("_formularios");
	    $resultado[$array[0]] = $array[0] . " - " . $array[1];
	}
	

	return $resultado;
    }

    function obtenerDescripcionAlmacen($codigo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    trim(ch_nombre_almacen)
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_almacen='" . pg_escape_string($codigo) . "'
		;";
	if ($sqlca->query($sql, "_almacenes") < 0) return null;
	
	$a = $sqlca->fetchRow("_almacenes");
	
	return $a[0];
    }
    
    function obtenerDescripcionDocumento($codigo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    tab_descripcion
		FROM
		    int_tabla_general
		WHERE
			tab_tabla='08'
		    AND tab_elemento='" . pg_escape_string(str_pad($codigo, 6, "0", STR_PAD_LEFT)) . "'
		    AND tab_elemento != '000000'
		;";
	if ($sqlca->query($sql, "_documentos") < 0) return null;
	
	$a = $sqlca->fetchRow("_documentos");
	
	return $a[0];
    }
    function busqueda($desde, $hasta, $estacion, $formulario)
    {
	global $sqlca;

	$formularios = FormProcesModel::obtenerTiposFormularios();

	list($desde_dia, $desde_mes, $desde_ano) = sscanf($desde, "%2s/%2s/%4s");
	list($hasta_dia, $hasta_mes, $hasta_ano) = sscanf($hasta, "%2s/%2s/%4s");

	$sql = "SELECT
		    mov_numero,
		    tran_codigo,
		    mov_fecha,
		    art_codigo,
		    mov_tipdocuref,
		    mov_docurefe,
		    mov_tipoentidad,
		    mov_entidad,
		    mov_costounitario,
		    mov_cantidad,
		    mov_costototal,
		    mov_almaorigen,
		    mov_almadestino
		FROM
		    inv_movialma
		WHERE
			mov_fecha BETWEEN '" . pg_escape_string($desde_ano."-".$desde_mes."-".$desde_dia) . " 00:00:00' AND '" . pg_escape_string($hasta_ano."-".$hasta_mes."-".$hasta_dia) . " 23:59:59'
		";
	if ($estacion != "TODAS")
	    $sql .= "AND mov_almacen='" . pg_escape_string($estacion) . "' ";
	if ($formulario != "TODOS")
	    $sql .= "AND tran_codigo='" . pg_escape_string($formulario) . "' ";
	    
	$sql .= "ORDER BY
		    tran_codigo,
		    mov_numero,
		    art_codigo
		;";
	//echo $sql;
	if ($sqlca->query($sql) < 0) return null;
	
	$resultado = Array();
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	
	    //-------------> Recopilacion de informacion <-------------------------
	    $array = $sqlca->fetchRow();
	    $mov_numero = $array[0];
	    $tran_codigo = $array[1];
	    $mov_fecha = $array[2];
	    $art_codigo = $array[3];
	    $mov_tipdocuref = $array[4] . " - " . FormProcesModel::ObtenerDescripcionDocumento($array[4]);
	    $mov_docurefe = $array[5];
	    $mov_tipoentidad = $array[6];
	    $mov_entidad = $array[7];
	    $mov_costounitario = $array[8];
	    $mov_cantidad = $array[9];
	    $mov_costototal = $array[10];
	    $mov_almaorigen = $array[11] . " - " . FormProcesModel::ObtenerDescripcionAlmacen($array[11]);
	    $mov_almadestino = $array[12] . " - " . FormProcesModel::ObtenerDescripcionAlmacen($array[12]);
	    
	    $art_descripcion = FormProcesModel::obtenerDescripcion($art_codigo);
	    $pro_razsocial = FormProcesModel::obtenerProveedor($mov_entidad);
	    $cli_razsocial = FormProcesModel::obtenerCliente($mov_entidad);
	    
	    $sql = "SELECT
			art_unidad
		    FROM
		        int_articulos
		    WHERE
		        art_codigo='" . pg_escape_string($art_codigo) . "'
		    ;";
	    $sqlca->query($sql, "art");
	    $a = $sqlca->fetchRow("art");

	    $art_presentacion = FormProcesModel::obtenerUnidadPresentacion($a[0]);

	    // ------------------> Fin de recopilacion de informacion <----------------
	    
	    $key = $tran_codigo.$mov_numero.$i;
	    
	    // Datos de cabecera
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['mov_numero'] = $mov_numero;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['mov_fecha'] = $mov_fecha;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['mov_tipdocuref'] = $mov_tipdocuref;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['mov_docurefe'] = $mov_docurefe;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['mov_almaorigen'] = $mov_almaorigen;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['mov_almadestino'] = $mov_almadestino;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['pro_razsocial'] = $pro_razsocial;
	    
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['art_codigo'] = $art_codigo;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['art_descripcion'] = $art_descripcion;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['art_presentacion'] = $art_presentacion;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['mov_costounitario'] = $mov_costounitario;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['mov_cantidad'] = $mov_cantidad;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['mov_costototal'] = $mov_costototal;

	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['mov_tipoentidad'] = $mov_tipoentidad;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['mov_entidad'] = $mov_entidad;
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['lineas'][$key]['cli_razsocial'] = $cli_razsocial;
	    
	    $resultado['formulario'][$tran_codigo]['movimientos'][$mov_numero]['total'] += $mov_costototal;
	    $resultado['formulario'][$tran_codigo]['total'] += $mov_costototal;
	    $resultado['total'] += $mov_costototal;
	}

	return $resultado;
    }
}

