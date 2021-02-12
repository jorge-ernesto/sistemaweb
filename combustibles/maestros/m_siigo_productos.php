<?php

class SIIGOProductosCRUDModel extends Model {

	function getAlmacenes() {
		global $sqlca;

		$sql = "
		SELECT
		    ch_almacen,
		    ch_almacen||' - '||ch_nombre_almacen
		FROM
		    inv_ta_almacenes
		WHERE
		    ch_clase_almacen = '1'
		ORDER BY
		    ch_almacen;
		";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    }
    
    function listarProductos($sNombreProducto) {
		global $sqlca;

		echo $sNombreProducto;

		$status = $sqlca->query("
		SELECT DISTINCT
			SIIGOPRO.centrocosto,
			SIIGOPRO.almacen,
			PRO.art_codigo AS nu_codigo_producto,
			SIIGOPRO.codigo_producto_siigo,
			PRO.art_descripcion AS no_nombre_producto,
			SIIGOPRO.serietickesboleta,
			SIIGOPRO.serietickesfactura,
			SIIGOPRO.nuvmseriefactura,
			SIIGOPRO.nucpseriefactura,
			SIIGOPRO.nucpseriefacturaglp
		FROM
			configsigo AS SIIGOPRO
			JOIN int_articulos AS PRO ON (PRO.art_codigo = SIIGOPRO.codigo_producto)
		WHERE
			tipo_documento = 'CP'
			AND (PRO.art_codigo LIKE '%" . $sNombreProducto . "%' OR PRO.art_descripcion LIKE '%" . $sNombreProducto . "%')
		ORDER BY
			5
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function listarProductos';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registros';
		else{
			$arrResult['estado'] = TRUE;
			$arrResult['result'] = $sqlca->fetchAll();
			$arrResult['cantidad_registros'] = $status;//status = Tambien contiene la cantidad de registros
		}

		return $arrResult;
    }

    function saveProducto($dataPost){
    	global $sqlca;

    	$No_Producto_Add = trim($dataPost['No_Producto_Add']);
    	$No_Producto_Add = strip_tags($No_Producto_Add);

    	$Nu_Id_Producto_Add = trim($dataPost['Nu_Id_Producto_Add']);
    	$Nu_Id_Producto_Add = strip_tags($Nu_Id_Producto_Add);

    	$codigo_producto_siigo = trim($dataPost['codigo_producto_siigo']);
    	$codigo_producto_siigo = strip_tags($codigo_producto_siigo);

    	// 1. INSERT TO SIIGO Compras y Ventas
		$status = $sqlca->query("
		SELECT DISTINCT
			centrocosto,
			tipo_documento,
			cuenta,
			cuenta_descripcion,
			naturaleza,
			cod_rapido_bus,
			almacen,
			serietickesboleta,
		    serietickesfactura,
		    cuenta_tickes,
		    nuvmseriefactura,
		    nu_serie_siigo_consolidado_x_dia
		FROM
		    configsigo
		WHERE
		    tipo_documento = 'VT'
		ORDER BY
		    tipo_documento DESC,
		    naturaleza,
		    cuenta;
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';
	    $arrResult['cantidad_registros'] = 0;

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function saveProducto Compras';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registros';
		else{

			$arrVenta = $sqlca->fetchAll();
			foreach ($arrVenta as $row) {
		    	$sql = "
		    	INSERT INTO configsigo (
		            id,
		            sucursal,
		            centrocosto,
		            tipo_producto,
		            tipo_documento,
		            cuenta,
		            cuenta_descripcion,
		            naturaleza,
		            codigo_producto,
		            tipo_asiento,
		            cod_rapido_bus,
		            almacen,
		            codigo_producto_siigo,
		            serietickesboleta,
		            serietickesfactura,
		            cuenta_tickes,
		            nuvmseriefactura,
		            nucpseriefactura,
		            nucpseriefacturaglp,
		            nu_serie_siigo_consolidado_x_dia,
		            nu_ruc_empresa
		    	) VALUES (
		    		(SELECT MAX(id) + 1 FROM configsigo),
		    		'000',--Sucursal
		    		'" . $row['centrocosto'] . "',
		    		'" . $No_Producto . "',
		    		'" . $row['tipo_documento'] . "',--SI es Compra o Venta
		    		'" . $row['cuenta'] . "',
		    		'" . $row['cuenta_descripcion'] . "',
					'" . $row['naturaleza'] . "',
					'" . $Nu_Id_Producto . "',
					'1',--tipo de Asiento
					'" . $row['cod_rapido_bus'] . "',
					'" . $row['almacen'] . "',
					'" . $codigo_producto_siigo . "',
					'" . $row['serietickesboleta'] . "',
					'" . $row['serietickesfactura'] . "',
                    '" . $row['cuenta_tickes'] . "',
                    '" . $row['nuvmseriefactura'] . "',
                    '',--nucpseriefactura
                    '',--nucpseriefacturaglp
                    '" . $row['nu_serie_siigo_consolidado_x_dia'] . "',
                    (SELECT * FROM int_ta_sucursales WHERE ch_sucursal = '" . $_SESSION['almacen'] . "' LIMIT 1)
		    	);
		    	";
		    	echo "\n".$sql;
		    }
		}
		return $arrResult;
    }
}
