<?php

class ComprasModel extends Model{

	function obtenerSucursales($alm) {
		global $sqlca;
		
		if(trim($alm) == "")
			$cond = "";
		else
			$cond = " AND ch_almacen = '$alm'"; 
	
		$sql = "SELECT
			    ch_almacen,
			    ch_almacen||' - '||ch_nombre_almacen
			FROM
			    inv_ta_almacenes
			WHERE
			    ch_clase_almacen='1' $cond 
			ORDER BY
			    ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    	}

	function search($desde, $hasta, $estaciones, $bDetallado) {
		global $sqlca;
	
		$igv = ComprasModel::obtenerFactorIGV();

		if($estaciones != "TODAS")
			$cond = "AND k.mov_almacen = '" . pg_escape_string($estaciones) . "'";

		$sql = "SELECT
				k.mov_numero,
				trim(k.art_codigo),
			    	k.mov_costounitario,
			    	k.mov_cantidad,
			    	to_char(k.mov_fecha, 'YYYY-MM-DD'),
			    	k.mov_docurefe,
			    	k.com_num_compra,
			    	k.mov_almacen,
			    	k.mov_costo_participacion,
			   	c.numero_scop,
				prove.pro_ruc|| ' - ' ||prove.pro_razsocial AS noproveedor
			FROM
				inv_movialma k
				LEFT JOIN inv_movialma_complemento c ON(k.tran_codigo = c.tran_codigo AND k.mov_numero = c.mov_numero AND date(k.mov_fecha) = date(c.mov_fecha))
				LEFT JOIN int_proveedores prove ON(k.mov_entidad = prove.pro_codigo)
			WHERE
		        	k.tran_codigo='21'
				AND date(k.mov_fecha) BETWEEN to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') and to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				$cond
			ORDER BY
		    		k.mov_almacen,
		    		k.mov_numero;
		";

	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();
	
	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $mov_numero 	= $a[0];
	    $art_codigo 	= $a[1];
	    $mov_costounitario = $a[2];
	    $mov_cantidad = $a[3];
	    $mov_fecha = $a[4];
	    $mov_docurefe = $a[5];
	    $com_num_compra = $a[6];
	    $mov_almacen = $a[7] . " " . ComprasModel::obtenerNombreEstacion($a[7]);
	    $mov_participacion = $a[8];
	    $mov_scop = $a[9];
	    $noproveedor = $a[10];
	    
	    $precio_venta = (ComprasModel::obtenerPrecioCombustible($art_codigo, $a[7], $mov_fecha)/$igv);
	    $mov_utilidad = ($mov_cantidad*$precio_venta)-($mov_cantidad*$mov_costounitario);

	    if ($bDetallado) {
		$result['estaciones'][$mov_almacen]['movimientos'][$mov_numero][$art_codigo.'_costo'] = $mov_costounitario;
	        $result['estaciones'][$mov_almacen]['movimientos'][$mov_numero][$art_codigo.'_cantidad'] = $mov_cantidad;
	        $result['estaciones'][$mov_almacen]['movimientos'][$mov_numero][$art_codigo.'_utilidad'] = $mov_utilidad;
	        $result['estaciones'][$mov_almacen]['movimientos'][$mov_numero]['fecha'] 	= $mov_fecha;
	        $result['estaciones'][$mov_almacen]['movimientos'][$mov_numero]['factura'] 	= $mov_docurefe;
	        $result['estaciones'][$mov_almacen]['movimientos'][$mov_numero]['orden'] 	= $com_num_compra;
	        $result['estaciones'][$mov_almacen]['movimientos'][$mov_numero]['noproveedor'] 	= $noproveedor;

	        @$result['estaciones'][$mov_almacen]['movimientos'][$mov_numero]['scop'] = $mov_scop;

		/* Total utilidad por movimiento */
	        @$result['estaciones'][$mov_almacen]['movimientos'][$mov_numero]['utilidad'] += $mov_utilidad;
	        @$result['estaciones'][$mov_almacen]['movimientos'][$mov_numero]['participacion'] += $mov_participacion;

	    }

	    /* Totales por CC */
//	    @$result['estaciones'][$mov_almacen]['totales'][$art_codigo.'_costo'] 	+= (($mov_costounitario * $mov_cantidad) / ($mov_cantidad));
	    @$result['estaciones'][$mov_almacen]['totales'][$art_codigo.'_cantidad'] 	+= $mov_cantidad;
	    @$result['estaciones'][$mov_almacen]['totales'][$art_codigo.'_total'] 	+= ($mov_costounitario * $mov_cantidad);
	    @$result['estaciones'][$mov_almacen]['totales'][$art_codigo.'_utilidad'] 	+= $mov_utilidad;
	    @$result['estaciones'][$mov_almacen]['totales']['participacion']		+= $mov_participacion;
	    @$result['estaciones'][$mov_almacen]['totales']['utilidad'] 		+= $mov_utilidad;
	    
	    /* Total general */
//	    @$result['totales'][$art_codigo.'_costo'] 		+= ($mov_costounitario * $mov_cantidad) / ($mov_cantidad);
	    @$result['totales'][$art_codigo.'_cantidad'] 	+= $mov_cantidad;
	    @$result['totales'][$art_codigo.'_total'] 		+= ($mov_costounitario * $mov_cantidad);
	    @$result['totales'][$art_codigo.'_utilidad']	+= $mov_utilidad;
	    @$result['totales']['participacion'] 		+= $mov_participacion;
	    @$result['totales']['utilidad'] 			+= $mov_utilidad;
	}
	return $result;
	
    }

    function obtenerFactorIGV()
    {
	global $sqlca;
	
	/*
	 * La funcion util_fn_igv() devuelve un numero entero que indica el porcentaje.
	 * Ejemplo: 19 para 19%.
	 * Sin embargo esta funcion debe devolver un factor para multiplicar o dividir
	 * como es 1.19 para 19%. Es por eso la division y la suma.
	 */
	$sql = "SELECT
		    (util_fn_igv()/100)+1
		;
		";
	if ($sqlca->query($sql, "_igv") < 0) return 1.19;
	
	$a = $sqlca->fetchRow("_igv");
	
	return $a[0];
    }

    function obtenerPrecioCombustible($codigo, $sucursal, $fecha)
    {
	global $sqlca;
	
	$sql = "SELECT
		    (nu_ventavalor/nu_ventagalon) as nu_precio
		FROM
		    comb_ta_contometros
		WHERE
			dt_fechaparte<='" . pg_escape_string($fecha) . "'
		    AND ch_codigocombustible='" . pg_escape_string($codigo) . "'
		    AND nu_ventagalon > 0
		    AND ch_sucursal='" . pg_escape_string($sucursal) . "'
		ORDER BY
		    dt_fechaparte DESC
		LIMIT
		    1
		;
		";

	if ($sqlca->query($sql, "_precio") < 0) return false;
	
	if ($sqlca->numrows("_precio") == 0) return 0;
	
	$a = $sqlca->fetchRow("_precio");
	return $a[0];
    }

    function obtenerNombreEstacion($codigo)
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_nombre_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_almacen='" . pg_escape_string($codigo) . "'
		;
		";

	if ($sqlca->query($sql, "_nombre") < 0) return false;
	
	$a = $sqlca->fetchRow("_nombre");
	return $a[0];
    }
    
    function obtenerListaEstaciones()
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
}

