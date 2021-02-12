<?php
class ProductoxProveedorModel extends Model
{
	function obtenerAlmacenes()
	{
		global $sqlca;
	
		$sql = "SELECT
			ch_almacen,
			ch_almacen||' - '||ch_nombre_almacen
			FROM
			inv_ta_almacenes
			WHERE
			ch_clase_almacen='1'
			;
			";
		if ($sqlca->query($sql) < 0) return false;
	
		$result = array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
	
		$ch_almacen = $a[0];
		$ch_nombre_almacen = $a[1];
	
		$result[$ch_almacen] = $ch_nombre_almacen;
		}
	
		return $result;
	}

	function busqueda($almacen, $mes, $anio, $proveedor, $producto)
	{
		global $sqlca;
	
		$mes_actual = str_pad(pg_escape_string($mes), 2, "0", STR_PAD_LEFT);
		$anio_actual = $anio;
	
		if ($mes == 1) {
			$mes_anterior = "12";
			$anio_anterior = pg_escape_string($anio - 1);
		}
		else {
			$mes_anterior = str_pad(pg_escape_string($mes-1), 2, "0", STR_PAD_LEFT);
			$anio_anterior = pg_escape_string($anio);
		}
	
		$sql = "
			SELECT
				trim(pro.pro_codigo) || ' - ' || prov.pro_razsocial AS proveedor,
				trim(pro.art_codigo) || ' - ' || art.art_descripcion AS producto,
				mon.tab_descripcion AS moneda,
				pro.rec_precio AS costo_unitario,
				pro.rec_fecha_precio AS fecha_creacion,
				alm.stk_stock" . $mes_actual . " AS stock_actual,
				vmi.nu_can" . $mes_actual . " AS venta_mes,
				vmi2.nu_can" . $mes_anterior . " AS venta_mes_anterior,
				trim(pro.pro_codigo),
				trim(pro.art_codigo),
				art.art_descripcion
			FROM
				com_rec_pre_proveedor pro
				LEFT JOIN int_articulos art ON (art.art_codigo=pro.art_codigo)
				LEFT JOIN int_proveedores prov ON (prov.pro_codigo=pro.pro_codigo)
				LEFT JOIN inv_saldoalma alm ON (alm.art_codigo=pro.art_codigo AND alm.stk_almacen='". pg_escape_string($almacen) . "' AND alm.stk_periodo='" . $anio_actual . "')
				LEFT JOIN ven_ta_venta_mensualxitem vmi ON (vmi.art_codigo=pro.art_codigo AND vmi.ch_sucursal='" . pg_escape_string($almacen) . "' AND vmi.ch_periodo='" . $anio_actual . "')
				LEFT JOIN ven_ta_venta_mensualxitem vmi2 ON (vmi2.art_codigo=pro.art_codigo AND vmi2.ch_sucursal='" . pg_escape_string($almacen) . "' AND vmi2.ch_periodo='" . $anio_anterior . "')
				LEFT JOIN int_tabla_general mon ON (mon.tab_tabla='04' AND mon.tab_elemento LIKE '%'||pro.rec_moneda)
			WHERE 
				true ";
				if ($proveedor != '')
					$sql .= "AND pro.pro_codigo = '" .pg_escape_string($proveedor). "' ";
				if ($producto != '')
					$sql .= "AND pro.art_codigo = '" .pg_escape_string($producto). "' " ;
		$sql .= "ORDER BY
				pro.pro_codigo,
				pro.art_codigo";			
	
		if ($sqlca->query($sql) < 0) return false;
	
		$resultado = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		$a = $sqlca->fetchRow();
	
		$resultado[$i]['proveedor'] = $a[0];
		$resultado[$i]['producto'] = $a[1];
		$resultado[$i]['moneda'] = $a[2];
		$resultado[$i]['costo_unitario'] = $a[3];
		$resultado[$i]['fecha_creacion'] = $a[4];
		$resultado[$i]['stock_actual'] = $a[5];
		$resultado[$i]['venta_mes'] = $a[6];
		$resultado[$i]['venta_mes_anterior'] = $a[7];
		$resultado[$i]['codproveedor'] = $a[8];
		$resultado[$i]['codproducto'] = $a[9];
		$resultado[$i]['descproducto'] = $a[10];
		}
	
		return $resultado;
	}

	function guardarFila($ch_proveedor, $ch_producto, $ch_moneda, $ch_costounitario, $ch_fechacreacion)
	{
		global $sqlca;
		
		$sql = "
			INSERT INTO
				com_rec_pre_proveedor (pro_codigo, art_codigo, rec_moneda, rec_precio, rec_fecha_precio) 
			VALUES
				('".pg_escape_string($ch_proveedor)."','".
				pg_escape_string($ch_producto)."','".
				pg_escape_string($ch_moneda)."','".
				pg_escape_string($ch_costounitario)."','".
				pg_escape_string($ch_fechacreacion)."')";
		echo $sql;
		if ($sqlca->query($sql) < 0)
			return false;
		else
			return true;
	}
	
	function obtenerFila($ch_proveedor, $ch_producto)
	{
		global $sqlca;

		$sql = "SELECT
				pro_codigo as ch_proveedor,
				art_codigo as ch_producto,
				rec_moneda as ch_moneda,
				rec_precio as ch_costounitario,
				rec_fecha_precio as ch_fechacreacion
			
			FROM
				com_rec_pre_proveedor
			WHERE
				pro_codigo='" . pg_escape_string($ch_proveedor) . "'
				AND art_codigo='" . pg_escape_string($ch_producto) . "'
			";
		if ($sqlca->query($sql) < 0) return false;
	
		return $sqlca->fetchRow();
		
	}

	function actualizarFila($ch_proveedor, $ch_producto, $ch_moneda, $ch_costounitario, $ch_fechacreacion)
	{
		global $sqlca;
		
		$query = "
			UPDATE
				com_rec_pre_proveedor 
			SET 
				rec_moneda ='".pg_escape_string($ch_moneda)."', 
				rec_precio ='".pg_escape_string($ch_costounitario)."', 
				rec_fecha_precio ='".pg_escape_string($ch_fechacreacion)."'
		
			WHERE 
				pro_codigo ='".pg_escape_string($ch_proveedor)."' AND art_codigo = '" . pg_escape_string($ch_producto) . "'";
	
		if ($sqlca->query($query) < 0)
			return false;
		else
			return true;

	}

	function eliminarFila($codproveedor, $codproducto) {
		global $sqlca;
		$sql = "DELETE 
			FROM 
				com_rec_pre_proveedor 
			WHERE 
				pro_codigo = '" . pg_escape_string($codproveedor) . "' AND art_codigo = '" . pg_escape_string($codproducto) . "'";

		if ($sqlca->query($sql) < 0)
			return false;
		else
			return true;
	}

}
