<?php

class PedidoComprasModel extends Model { // Poder agregar eliminar editar proveedor, producto, precio
	function buscar($almacen, $desde, $hasta) {
		global $sqlca;
	    
		$sql = "
		SELECT
			id_pedido_cabecera,
			num_pedido,
			ch_almacen,
			dt_fecha,
			ped_tipo,
			ped_observacion,
			fecha_actualizacion,
			ch_usuario,
			ch_ip
		FROM
			pedido_compra_cabecera
		WHERE 	
			ch_almacen='$almacen' 
			AND dt_fecha between to_date('$desde', 'dd/mm/YYYY') AND to_date('$hasta', 'dd/mm/YYYY')
		ORDER BY 
			id_pedido_cabecera;
		";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['id_cab']	= $a[0];
			$resultado[$i]['num_pedido']	= $a[1];
			$resultado[$i]['almacen']	= $a[2];
			$resultado[$i]['fecha']		= $a[3];
			$resultado[$i]['tipo']	 	= $a[4];
			$resultado[$i]['observacion'] 	= $a[5];		
			$resultado[$i]['actualizacion']	= substr($a[6], 0, 19);
			$resultado[$i]['usuario']	= $a[7];
			$resultado[$i]['ip']		= $a[8];
		}
		
		return $resultado;
  	}

  	function obtenerCostoUltimaCompra($almacen, $hasta, $art_codigo) {
		global $sqlca;

		$status = $sqlca->query("
		SELECT
			mov_costounitario AS ultmcosto
		FROM
			inv_movialma
		WHERE
			tran_codigo IN ('01', '21')
			AND mov_almacen = '" . $almacen . "'
			AND mov_fecha <= '" . $hasta . " 23:59:59'
			AND trim(art_codigo) = '" . trim($art_codigo) . "'
			AND mov_costounitario > 0
		ORDER BY
			mov_fecha DESC
		LIMIT 1;
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function obtenerCostoUltimaCompra';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		else{
			$arrResult['estado'] = TRUE;
			$row = $sqlca->fetchRow();
			$arrResult['result'] = $row["ultmcosto"];
		}

		return $arrResult;
	}

	function listar($almacen, $nropedido, $linea, $proveedor, $tipopedido, $fecha, $observacion) {
		ini_set('memory_limit','512M');

		global $sqlca;

		if ($proveedor == ''){
			echo "Debe ingresar un proveedor";
			die();
		}

		if ($linea != '')
			$line = "AND art.art_linea='$linea'";

		if ($proveedor != '') 
			$prov = "AND pro.pro_codigo = '$proveedor'";  
	    
		$sqlmes = "SELECT now() as mes, now() - interval '1 month' as mes1, now() - interval '2 month' as mes2, now() - interval '3 month' as mes3; ";
		if ($sqlca->query($sqlmes) < 0) 
			return false;	
		$a = $sqlca->fetchRow();

		$mes   	= substr($a['mes'], 5, 2);
		$anio  	= substr($a['mes'], 0, 4);
		$mes01 	= substr($a['mes1'], 5, 2);
		$anio01	= substr($a['mes1'], 0, 4);
		$mes02 	= substr($a['mes2'], 5, 2);
		$anio02	= substr($a['mes2'], 0, 4);
		$mes03 	= substr($a['mes3'], 5, 2);
		$anio03	= substr($a['mes3'], 0, 4);

		$sql = "
		SELECT
			art.art_codigo as codigo,
			art.art_descripcion as descripcion,
			(SELECT SUM(v3.mov_cantidad) FROM inv_movialma v3 WHERE v3.art_codigo=art.art_codigo and v3.mov_almacen='$almacen' and to_char(v3.mov_fecha,'YYYY-MM')='$anio03-$mes03' and v3.tran_codigo IN('25','35','45','10')) as mes3,
			(SELECT SUM(v2.mov_cantidad) FROM inv_movialma v2 WHERE v2.art_codigo=art.art_codigo and v2.mov_almacen='$almacen' and to_char(v2.mov_fecha,'YYYY-MM')='$anio02-$mes02' and v2.tran_codigo IN('25','35','45','10')) as mes2,
			(SELECT SUM(v1.mov_cantidad) FROM inv_movialma v1 WHERE v1.art_codigo=art.art_codigo and v1.mov_almacen='$almacen' and to_char(v1.mov_fecha,'YYYY-MM')='$anio01-$mes01' and v1.tran_codigo IN('25','35','45','10')) as mes1,
			(SELECT s.stk_stock$mes FROM inv_saldoalma s WHERE art.art_codigo=s.art_codigo AND s.stk_almacen='$almacen' AND s.stk_periodo='$anio') as stk_actual,
			mm.stk_minimo as stk_minimo,
			mm.stk_maximo as stk_maximo,
			tab.tab_num_02 as dias_repo
		FROM
			int_articulos AS art
			LEFT JOIN inv_stkminmax AS mm ON (mm.art_codigo = art.art_codigo AND mm.ch_almacen = '" . $almacen . "')
			LEFT JOIN com_rec_pre_proveedor AS com ON (art.art_codigo = com.art_codigo)
			LEFT JOIN int_proveedores AS pro ON (pro.pro_codigo = com.pro_codigo)
			LEFT JOIN int_tabla_general AS tab ON (tab.tab_elemento = art.art_linea AND tab_tabla = '20' AND tab.tab_elemento != '000000')
		WHERE
			art.art_estado 		= '0'
			AND art.art_plutipo = '1'
			$line
			$prov
		ORDER BY
			descripcion
		LIMIT
			20000
		";
					
		// echo "<pre>";
		// echo $sql;
		// echo "</pre>";

/*
		$sql = "SELECT
				art.art_codigo as codigo,
				art.art_descripcion as descripcion,

				(SELECT v2.nu_can$mes03 FROM ven_ta_venta_mensualxitem v2 WHERE art.art_codigo=v2.art_codigo AND v2.ch_sucursal='$almacen' AND v2.ch_periodo='$anio03') as mes3,
				(SELECT v3.nu_can$mes02 FROM ven_ta_venta_mensualxitem v3 WHERE art.art_codigo=v3.art_codigo AND v3.ch_sucursal='$almacen' AND v3.ch_periodo='$anio02') as mes2,
				(SELECT v4.nu_can$mes01 FROM ven_ta_venta_mensualxitem v4 WHERE art.art_codigo=v4.art_codigo AND v4.ch_sucursal='$almacen' AND v4.ch_periodo='$anio01') as mes1,

				(SELECT s.stk_stock$mes FROM inv_saldoalma s WHERE art.art_codigo=s.art_codigo AND s.stk_almacen='$almacen' AND s.stk_periodo='$anio') as stk_actual,
				mm.stk_minimo as stk_minimo,
				mm.stk_maximo as stk_maximo
			FROM
				int_articulos art
				LEFT JOIN ven_ta_venta_mensualxitem vta ON (vta.art_codigo=art.art_codigo AND vta.ch_sucursal='$almacen' AND vta.ch_periodo='$anio')
				LEFT JOIN inv_stkminmax mm ON (mm.art_codigo=art.art_codigo AND mm.ch_almacen='$almacen')
				LEFT JOIN com_rec_pre_proveedor com ON (art.art_codigo=com.art_codigo) 
				LEFT JOIN int_proveedores pro ON (pro.pro_codigo=com.pro_codigo) 
			WHERE 	
				art.art_estado='0'";

		if ($linea != '') 
			$sql .= "AND art.art_linea='$linea' ";
		
		if ($proveedor != '')
			$sql .= "AND pro.pro_codigo='$proveedor' ";
					
		$sql .= "ORDER BY 
				codigo ";

		echo "---<h1> ".$sql." </h1>---";
 	*/
		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['art_codigo']	= $a['codigo'];
			$resultado[$i]['art_descripcion']= $a['descripcion'];

			if (trim($a['mes3'])=='') 
				$a['mes3'] = 0;
			$resultado[$i]['mes_3'] = $a['mes3'];

			if (trim($a['mes2'])=='') 
				$a['mes2'] = 0;
			$resultado[$i]['mes_2'] = $a['mes2'];

			if (trim($a['mes1'])=='') 
				$a['mes1'] = 0;
			$resultado[$i]['mes_1'] = $a['mes1'];		

			if (trim($a['stk_actual'])=='') 
				$a['stk_actual'] = 0;
			$resultado[$i]['stk_actual'] = $a['stk_actual'];

			if (trim($a['stk_minimo'])=='') 
				$a['stk_minimo'] = 0;
			$resultado[$i]['stk_minimo'] = $a['stk_minimo'];

			if (trim($a['stk_maximo'])=='') 
				$a['stk_maximo'] = 0;
			$resultado[$i]['stk_maximo'] = $a['stk_maximo'];

			if($tipopedido == "MINIMO") {
				$resultado[$i]['cantidad'] = $a['stk_minimo']-$a['stk_actual'];
			} else {
				$resultado[$i]['cantidad'] = $a['stk_maximo']-$a['stk_actual'];
			}
			if($resultado[$i]['cantidad'] <= 0)
				$resultado[$i]['cantidad'] = 0;
			
			//calculo de cantidad sugerida

			if (trim($a['dias_repo'])=='') 
				$a['dias_repo'] = 0;
			$resultado[$i]['dias_repo'] = $a['dias_repo'];

			$cantidad_max = max($a['mes3'],$a['mes2'],$a['mes1']);

			$cantidad_max = ((($cantidad_max/4)*$a['dias_repo'])-$a['stk_actual']);

			$resultado[$i]['sugerido'] = round($cantidad_max);
		}
		
		$res = Array();
		$res['cabecera']['almacen']		= $almacen;
		$res['cabecera']['linea']		= $linea;
		$res['cabecera']['proveedor']	= $proveedor;
		$res['cabecera']['tipo']		= $tipopedido;
		$res['cabecera']['nropedido']	= $nropedido;
		$res['cabecera']['observacion']	= $observacion;
		$res['detalle']					= $resultado;

		// echo "<pre>";
		// print_r($res);
		// echo "</pre>";

		return $res;
	}

	function completarpedido($almacen, $producto, $tipopedido)
		{
		global $sqlca;
	    
		$sqlmes = "SELECT now() as mes, now() - interval '1 month' as mes1, now() - interval '2 month' as mes2, now() - interval '3 month' as mes3; ";		
		if ($sqlca->query($sqlmes) < 0) 
			return false;	
		$a = $sqlca->fetchRow();

		$mes   	= substr($a['mes'], 5, 2);
		$anio  	= substr($a['mes'], 0, 4);
		$mes01 	= substr($a['mes1'], 5, 2);
		$anio01	= substr($a['mes1'], 0, 4);
		$mes02 	= substr($a['mes2'], 5, 2);
		$anio02	= substr($a['mes2'], 0, 4);
		$mes03 	= substr($a['mes3'], 5, 2);
		$anio03	= substr($a['mes3'], 0, 4);

		//$almacen = '001';

		$sql = "SELECT
				art.art_codigo as codigo,
				art.art_descripcion as descripcion,

				(SELECT v2.nu_can$mes03 FROM ven_ta_venta_mensualxitem v2 WHERE art.art_codigo=v2.art_codigo AND v2.ch_sucursal='$almacen' AND v2.ch_periodo='$anio03') as mes3,
				(SELECT v3.nu_can$mes02 FROM ven_ta_venta_mensualxitem v3 WHERE art.art_codigo=v3.art_codigo AND v3.ch_sucursal='$almacen' AND v3.ch_periodo='$anio02') as mes2,
				(SELECT v4.nu_can$mes01 FROM ven_ta_venta_mensualxitem v4 WHERE art.art_codigo=v4.art_codigo AND v4.ch_sucursal='$almacen' AND v4.ch_periodo='$anio01') as mes1,

				(SELECT s.stk_stock$mes FROM inv_saldoalma s WHERE art.art_codigo=s.art_codigo AND s.stk_almacen='$almacen' AND s.stk_periodo='$anio') as stk_actual,
				mm.stk_minimo as stk_minimo,
				mm.stk_maximo as stk_maximo,
				tab.tab_num_02 as dias_repo
			FROM
				int_articulos art
				LEFT JOIN ven_ta_venta_mensualxitem vta ON (vta.art_codigo=art.art_codigo AND vta.ch_sucursal='$almacen' AND vta.ch_periodo='$anio')
				LEFT JOIN inv_stkminmax mm ON (mm.art_codigo=art.art_codigo AND mm.ch_almacen='$almacen')
				LEFT JOIN com_rec_pre_proveedor com ON (art.art_codigo=com.art_codigo) 
				LEFT JOIN int_proveedores pro ON (pro.pro_codigo=com.pro_codigo) 
				LEFT JOIN int_tabla_general tab ON (tab.tab_elemento=art.art_linea AND tab_tabla='20')
			WHERE 	
				art.art_estado='0'
				AND art.art_codigo = '".trim($producto)."'
				ORDER BY 
				codigo 
				LIMIT 
				1";

			//echo "---<h1> ".$sql." </h1>---";

			if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
				$resultado = Array();

				$a = $sqlca->fetchRow();
				$resultado['art_codigo']	= $a['codigo'];
				$resultado['art_descripcion']= $a['descripcion'];

				if (trim($a['mes3'])=='') 
					$a['mes3'] = 0;
				$resultado['mes_3'] = $a['mes3'];

				if (trim($a['mes2'])=='') 
					$a['mes2'] = 0;
				$resultado['mes_2'] = $a['mes2'];

				if (trim($a['mes1'])=='') 
					$a['mes1'] = 0;
				$resultado['mes_1'] = $a['mes1'];		

				if (trim($a['stk_actual'])=='') 
					$a['stk_actual'] = 0;
				$resultado['stk_actual'] = $a['stk_actual'];

				if (trim($a['stk_minimo'])=='') 
					$a['stk_minimo'] = 0;
				$resultado['stk_minimo'] = $a['stk_minimo'];

				if (trim($a['stk_maximo'])=='') 
					$a['stk_maximo'] = 0;
				$resultado['stk_maximo'] = $a['stk_maximo'];

				if($tipopedido == "MINIMO") {
					$resultado['cantidad'] = $a['stk_minimo']-$a['stk_actual'];
				} else {
					$resultado['cantidad'] = $a['stk_maximo']-$a['stk_actual'];
				}
				if($resultado['cantidad'] <= 0)
					$resultado['cantidad'] = 0;

				//calculo de cantidad sugerida

			if (trim($a['dias_repo'])=='') 
				$a['dias_repo'] = 0;
			$resultado['dias_repo'] = $a['dias_repo'];

			$cantidad_max = max($a['mes3'],$a['mes2'],$a['mes1']);

			$cantidad_max = ((($cantidad_max/4)*$a['dias_repo'])-$a['stk_actual']);

			$resultado['sugerido'] = round($cantidad_max);

			return $resultado;


	}

	function buscarDetalles($nropedido, $almacen, $fecha, $tipo, $observacion) {
		global $sqlca;

		$sql = "SELECT
				det.id_pedido_cabecera,
				det.id_pedido_detalle,
				det.art_codigo,
				art.art_descripcion,
				det.ped_cantidad,
				det.mes_1,
				det.mes_2,
				det.mes_3,
				det.stk_actual,
				det.stk_minimo,
				det.stk_maximo,
				det.ch_usuario,
				det.ch_ip,
				det.fecha_actualizacion,
				cab.num_pedido,
				tab.tab_num_02 as dias_repo
			FROM
				pedido_compra_detalle det
				LEFT JOIN pedido_compra_cabecera cab ON (cab.id_pedido_cabecera=det.id_pedido_cabecera)
				LEFT JOIN int_articulos art ON (art.art_codigo=det.art_codigo)
				LEFT JOIN int_tabla_general tab ON (tab.tab_elemento=art.art_linea AND tab_tabla='20')
			WHERE 	
				cab.num_pedido=$nropedido 
			ORDER BY 
				--det.id_pedido_cabecera,
				--det.art_codigo;
				--art.art_descripcion
				det.id_pedido_detalle";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$res = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$res[$i]['num_cabecera']	= $a[0];
			$res[$i]['num_detalle']		= $a[1];
			$res[$i]['art_codigo']		= $a[2];
			$res[$i]['art_descripcion']	= $a[3];
			$res[$i]['cantidad']		= $a[4];
			$res[$i]['mes_1'] 		= $a[5];		
			$res[$i]['mes_2']		= $a[6];
			$res[$i]['mes_3']		= $a[7];
			$res[$i]['stk_actual']		= $a[8];
			$res[$i]['stk_minimo']		= $a[9];
			$res[$i]['stk_maximo']		= $a[10];
			$res[$i]['ch_usuario']		= $a[11];
			$res[$i]['ch_ip']		= $a[12];
			$res[$i]['actualizacion']	= $a[13];

			if (trim($a[15])=='') 
				$a[15] = 0;
			$res[$i]['dias_repo'] = $a[15];

			$cantidad_max = max($res[$i]['mes_3'],$res[$i]['mes_2'],$res[$i]['mes_1']);

			$cantidad_max = ((($cantidad_max/4)*$res[$i]['dias_repo'])-$res[$i]['stk_actual']);

			$res[$i]['sugerido'] = round($cantidad_max);
		}		

		$resultado = Array();
		$resultado['cabecera']['nropedido']	= $nropedido;
		$resultado['cabecera']['almacen']	= $almacen;
		$resultado['cabecera']['fecha']		= $fecha;
		$resultado['cabecera']['tipo']		= $tipo;
		$resultado['cabecera']['observacion']		= $observacion;
		$resultado['detalle']			= $res;			
		
		return $resultado;
  	}

	function buscarPDF($id_cab) {
		global $sqlca;

		// Datos de Cabecera
		$sql = "SELECT
				num_pedido,
				ch_almacen,
				dt_fecha,
				ped_tipo,
				ped_observacion,
				ch_usuario				
			FROM
				pedido_compra_cabecera
			WHERE 	
				id_pedido_cabecera=$id_cab
			ORDER BY 
				id_pedido_cabecera;";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$a = $sqlca->fetchRow();
		$cab['numpedido']	= $a[0];
		$cab['almacen']		= $a[1];
		$cab['fecha']		= $a[2];
		$cab['tipo']		= $a[3];
		$cab['observacion'] 	= $a[4];
		$cab['usuario'] 	= $a[5];		

		// Datos de Detalles
		$sql = "SELECT
				det.art_codigo,
				art.art_descripcion,
				det.mes_1,
				det.mes_2,
				det.mes_3,
				det.stk_actual,
				det.stk_minimo,
				det.stk_maximo,
				det.ped_cantidad
			FROM
				pedido_compra_detalle det
				LEFT JOIN int_articulos art ON (art.art_codigo=det.art_codigo)
			WHERE 	
				det.id_pedido_cabecera=$id_cab 
			ORDER BY 
				--det.art_codigo;
				--art.art_descripcion
				det.id_pedido_detalle";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$det = Array();
		$res = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$det[$i]['codigo']	= $a[0];
			$det[$i]['descripcion']	= $a[1];
			$det[$i]['mes1'] 	= $a[2];		
			$det[$i]['mes2']	= $a[3];
			$det[$i]['mes3']	= $a[4];
			$det[$i]['actual']	= $a[5];
			$det[$i]['minimo']	= $a[6];
			$det[$i]['maximo']	= $a[7];
			$det[$i]['cantidad']	= $a[8];
		}

		$res['cabecera'] = $cab;
		$res['detalle']  = $det;
//		print_r($res);
		return $res;
  	}


  	function insertarPedido($almacen, $sCodLinea, $sCodProveedor, $nropedido, $tipo, $observacion, $usuario, $vec_check, $codigo, $stk_minimo, $stk_maximo, $cantidad, $ip) {
		global $sqlca;

		//error_log('insertarPedido!');

		$sql = "BEGIN;";
		if ($sqlca->query($sql) < 0) {
			return false;
		}

		if ($sCodLinea != '')
			$line = "AND art.art_linea='$sCodLinea' ";

		if ($sCodProveedor == '')
			return false;

		$sqlmes = "SELECT now() AS mes, now() - interval '1 month' AS mes1, now() - interval '2 month' AS mes2, now() - interval '3 month' AS mes3;";
		if ($sqlca->query($sqlmes) < 0) {
			return false;

		}
		$a = $sqlca->fetchRow();

		$mes   	= substr($a['mes'], 5, 2);
		$anio  	= substr($a['mes'], 0, 4);
		$mes01 	= substr($a['mes1'], 5, 2);
		$anio01	= substr($a['mes1'], 0, 4);
		$mes02 	= substr($a['mes2'], 5, 2);
		$anio02	= substr($a['mes2'], 0, 4);
		$mes03 	= substr($a['mes3'], 5, 2);
		$anio03	= substr($a['mes3'], 0, 4);

		$sql = "
		INSERT INTO	pedido_compra_cabecera(
			ch_almacen,
			num_pedido,
			dt_fecha,
			ch_usuario,
			ch_ip,
			ped_tipo,
			ped_observacion,
			fecha_actualizacion
		) VALUES (
			'".trim($almacen)."',
			".trim($nropedido).",
			now()::date,
			'".trim($usuario)."',
			'".trim($ip)."',
			'".trim($tipo)."',  
			'".trim($observacion)."', 
			now()
		);
		";

		$sqlca->query($sql);
		//id maximo de cabecera
		$sql = "SELECT MAX(id_pedido_cabecera) FROM pedido_compra_cabecera;";
		if ($sqlca->query($sql)<=0) {
			error_log('ROLLBACK');
			$sql = "ROLLBACK;";
			$sqlca->query($sql);
			return $sqlca->get_error();
		}

	    $a 		= $sqlca->fetchRow();
		$id_cab = $a[0];

		/*echo 'Cantidad de articulos: '.(count($codigo)).'<br>';
		var_dump($vec_check);*/
		error_log('Cantidad de articulos: '.(count($codigo)));

		for ($k = 0; $k < count($codigo); $k++) {
			//se puede ingresar cantidad cero o nula?
			if($vec_check[$k] == 'S') {
				$sql="
SELECT
 (SELECT SUM(v3.mov_cantidad) FROM inv_movialma v3 WHERE v3.art_codigo=art.art_codigo and v3.mov_almacen='$almacen' and to_char(v3.mov_fecha,'YYYY-MM')='$anio03-$mes03' and v3.tran_codigo IN('25','35','45','10')) as mes3,
 (SELECT SUM(v2.mov_cantidad) FROM inv_movialma v2 WHERE v2.art_codigo=art.art_codigo and v2.mov_almacen='$almacen' and to_char(v2.mov_fecha,'YYYY-MM')='$anio02-$mes02' and v2.tran_codigo IN('25','35','45','10')) as mes2,
 (SELECT SUM(v1.mov_cantidad) FROM inv_movialma v1 WHERE v1.art_codigo=art.art_codigo and v1.mov_almacen='$almacen' and to_char(v1.mov_fecha,'YYYY-MM')='$anio01-$mes01' and v1.tran_codigo IN('25','35','45','10')) as mes1,
 (SELECT s.stk_stock$mes FROM inv_saldoalma s WHERE art.art_codigo=s.art_codigo AND s.stk_almacen='$almacen' AND s.stk_periodo='$anio') as stk_actual
FROM
 int_articulos AS art
 LEFT JOIN inv_stkminmax AS mm ON (mm.art_codigo = art.art_codigo AND mm.ch_almacen = '" . $almacen . "')
 LEFT JOIN com_rec_pre_proveedor AS com ON (art.art_codigo = com.art_codigo)
 LEFT JOIN int_proveedores AS pro ON (pro.pro_codigo = com.pro_codigo)
 LEFT JOIN int_tabla_general AS tab ON (tab.tab_elemento = art.art_linea AND tab_tabla = '20' AND tab.tab_elemento != '000000')
WHERE
 art.art_estado = '0'
 AND art.art_plutipo = '1'
 AND art.art_codigo = '" . $codigo[$k] . "'
 $line
 AND pro.pro_codigo = '" . $sCodProveedor . "'
LIMIT 1;
";
				error_log('SELECT');
				error_log($sql);
				$sqlca->query($sql);
				$row = $sqlca->fetchRow();

				/*echo '$k: '.$k.'<pre>';
				var_dump($row);
				echo '</pre>';*/

				if (trim($row['mes3'])=='') 
					$row['mes3'] = 0;

				if (trim($row['mes2'])=='') 
					$row['mes2'] = 0;

				if (trim($row['mes1'])=='') 
					$row['mes1'] = 0;

				if (trim($row['stk_actual'])=='') 
					$row['stk_actual'] = 0;

				$sql = "
INSERT INTO pedido_compra_detalle(
 id_pedido_cabecera,
 art_codigo,
 ped_cantidad,
 mes_1,
 mes_2,
 mes_3,
 stk_actual,
 stk_minimo,
 stk_maximo,
 ch_usuario,
 ch_ip,
 fecha_actualizacion
) VALUES (
 " . trim($id_cab) . ",
 '"  .trim($codigo[$k]) . "',
 " . trim($cantidad[$k]) . ",
 " . trim($row['mes3']) . ",
 " . trim($row['mes2']) . ",
 " . trim($row['mes1']) . ",
 " . trim($row['stk_actual']) . ",
 " . trim($stk_minimo[$k]) . ",
 " . trim($stk_maximo[$k]) . ",
 '" . trim($usuario) . "',
 '" . trim($ip) . "',
 now()
);
";
				error_log('INSERT DETALLE');
				error_log($sql);

				//echo "\n-- Sentencia INSERT DETALLE: ".$sql.' --';
				//$sqlca->query($sql);
				error_log('sqlca');
				//error_log($sqlca->query($sql));
				if ($sqlca->query($sql)<0) {
					$sql = "ROLLBACK;";
					$sqlca->query($sql);
					return $sqlca->get_error();
				} else {
					$act = PedidoComprasModel::actualizaMinMax($almacen, $codigo[$k], $stk_minimo[$k], $stk_maximo[$k]);
				}
			}
		}
		$sql = "COMMIT;";
		$sqlca->query($sql);
		return 1;
	}


	//function ingresarPedido($almacen, $sCodLinea, $sCodProveedor, $nropedido, $tipo, $observacion, $usuario, $vec_check, $codigo, $descripcion, $mes1, $mes2, $mes3, $stk_actual, $stk_minimo, $stk_maximo, $cantidad, $ip) {
  	function ingresarPedido($almacen, $sCodLinea, $sCodProveedor, $nropedido, $tipo, $observacion, $usuario, $vec_check, $codigo, $stk_minimo, $stk_maximo, $cantidad, $ip) {
		global $sqlca;

		if ($sCodLinea != '')
			$line = "AND art.art_linea='$sCodLinea' ";

		if ($sCodProveedor == '')
			return false;
	    
		$sqlmes = "SELECT now() AS mes, now() - interval '1 month' AS mes1, now() - interval '2 month' AS mes2, now() - interval '3 month' AS mes3;";
		if ($sqlca->query($sqlmes) < 0) 
			return false;
		$a = $sqlca->fetchRow();

		$mes   	= substr($a['mes'], 5, 2);
		$anio  	= substr($a['mes'], 0, 4);
		$mes01 	= substr($a['mes1'], 5, 2);
		$anio01	= substr($a['mes1'], 0, 4);
		$mes02 	= substr($a['mes2'], 5, 2);
		$anio02	= substr($a['mes2'], 0, 4);
		$mes03 	= substr($a['mes3'], 5, 2);
		$anio03	= substr($a['mes3'], 0, 4);

		$sql = "
		INSERT INTO	pedido_compra_cabecera(
			ch_almacen,
			num_pedido,
			dt_fecha,
			ch_usuario,
			ch_ip,
			ped_tipo,
			ped_observacion,
			fecha_actualizacion
		) VALUES (
			'".trim($almacen)."',
			".trim($nropedido).",
			now()::date,
			'".trim($usuario)."',
			'".trim($ip)."',
			'".trim($tipo)."',  
			'".trim($observacion)."', 
			now()
		);
		";

		$sqlca->query($sql);
		//id maximo de cabecera
		$sql = "SELECT MAX(id_pedido_cabecera) FROM pedido_compra_cabecera;";
		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();

	    $a 		= $sqlca->fetchRow();
		$id_cab = $a[0];

		for ($k = 0; $k < count($codigo); $k++) {
			if($vec_check[$k] == 'S') {
				$sql="
				SELECT
					(SELECT SUM(v3.mov_cantidad) FROM inv_movialma v3 WHERE v3.art_codigo=art.art_codigo and v3.mov_almacen='$almacen' and to_char(v3.mov_fecha,'YYYY-MM')='$anio03-$mes03' and v3.tran_codigo IN('25','35','45','10')) as mes3,
					(SELECT SUM(v2.mov_cantidad) FROM inv_movialma v2 WHERE v2.art_codigo=art.art_codigo and v2.mov_almacen='$almacen' and to_char(v2.mov_fecha,'YYYY-MM')='$anio02-$mes02' and v2.tran_codigo IN('25','35','45','10')) as mes2,
					(SELECT SUM(v1.mov_cantidad) FROM inv_movialma v1 WHERE v1.art_codigo=art.art_codigo and v1.mov_almacen='$almacen' and to_char(v1.mov_fecha,'YYYY-MM')='$anio01-$mes01' and v1.tran_codigo IN('25','35','45','10')) as mes1,
					(SELECT s.stk_stock$mes FROM inv_saldoalma s WHERE art.art_codigo=s.art_codigo AND s.stk_almacen='$almacen' AND s.stk_periodo='$anio') as stk_actual
				FROM
					int_articulos AS art
					LEFT JOIN inv_stkminmax AS mm ON (mm.art_codigo = art.art_codigo AND mm.ch_almacen = '" . $almacen . "')
					LEFT JOIN com_rec_pre_proveedor AS com ON (art.art_codigo = com.art_codigo)
					LEFT JOIN int_proveedores AS pro ON (pro.pro_codigo = com.pro_codigo)
					LEFT JOIN int_tabla_general AS tab ON (tab.tab_elemento = art.art_linea AND tab_tabla = '20' AND tab.tab_elemento != '000000')
				WHERE
					art.art_estado 		= '0'
					AND art.art_plutipo = '1'
					AND art.art_codigo 	= '" . $codigo[$k] . "'
					$line
					AND pro.pro_codigo 	= '" . $sCodProveedor . "'
				LIMIT 1;
				";
				$sqlca->query($sql);
				$row = $sqlca->fetchRow();

				if (trim($row[$k]['mes3'])=='') 
					$row[$k]['mes3'] = 0;

				if (trim($row[$k]['mes2'])=='') 
					$row[$k]['mes2'] = 0;

				if (trim($row[$k]['mes1'])=='') 
					$row[$k]['mes1'] = 0;

				if (trim($row[$k]['stk_actual'])=='') 
					$row[$k]['stk_actual'] = 0;

				$sql = "
				INSERT INTO pedido_compra_detalle(
					id_pedido_cabecera,
					art_codigo,
					ped_cantidad,
					mes_1,
					mes_2,
					mes_3,
					stk_actual,
					stk_minimo,
					stk_maximo,		
					ch_usuario,
					ch_ip,
					fecha_actualizacion
				) VALUES (		
					" . trim($id_cab) . ",
					"  .trim($codigo[$k]) . ",
					" . trim($cantidad[$k]) . ",
					" . trim($row[$k]['mes3']) . ",
					" . trim($row[$k]['mes2']) . ",
					" . trim($row[$k]['mes1']) . ",
					" . trim($row[$k]['stk_actual']) . ",
					" . trim($stk_minimo[$k]) . ",
					" . trim($stk_maximo[$k]) . ",
					" . trim($usuario) . ",
					" . trim($ip) . ",
					now()
				);
				";

				//echo "\n-- Sentencia INSERT DETALLE: ".$sql.' --';
				$sqlca->query($sql);
				$act = PedidoComprasModel::actualizaMinMax($almacen, $codigo[$k], $stk_minimo[$k], $stk_maximo[$k]);
			}
		}
		return 1;
	}

	function eliminarPedido($id_pedido){
		global $sqlca;		

		//ELIMINAR pedido
		$sql = "DELETE FROM pedido_compra_detalle WHERE id_pedido_cabecera = '$id_pedido'";
		$sqlca->query($sql);
		$sql = "DELETE FROM pedido_compra_cabecera WHERE id_pedido_cabecera = '$id_pedido'";
		$sqlca->query($sql);
	}

	function modificarPedido($almacen, $codigo, $vec_check, $id_det, $usuario, $stk_minimo, $stk_maximo, $cantidad, $ip, $observacion) {
		global $sqlca;

		for ($k = 0; $k < count($codigo); $k++) {
			if($vec_check[$k] == 'S') {
				$sql = "	UPDATE 	
							pedido_compra_detalle
						SET 	
							stk_minimo=".trim($stk_minimo[$k]).", 
							stk_maximo=".trim($stk_maximo[$k]).",
							ped_cantidad=".trim($cantidad[$k]).",
							ch_usuario='".trim($usuario)."',  
							ch_ip='".trim($ip)."', 
							fecha_actualizacion = now()
						WHERE 	
							id_pedido_detalle=".trim($id_det[$k]).";";

				//echo "\n-- Sentencia MODIFICAR PEDIDO: ".$sql.' --';
				$sqlca->query($sql);	
				$act = PedidoComprasModel::actualizaMinMax($almacen, $codigo[$k], $stk_minimo[$k], $stk_maximo[$k]);
				$act1 = PedidoComprasModel::modificarObservacion($id_det, $observacion);
			}
		}

		return 1;
	} 

	function modificarObservacion($id_det, $observacion) {
		global $sqlca;
		$k=0;
		$sql = "	UPDATE 	
							pedido_compra_cabecera
						SET 	
							ped_observacion ='".trim($observacion)."'
						WHERE 	
							id_pedido_cabecera=(select id_pedido_cabecera from pedido_compra_detalle where id_pedido_detalle=".trim($id_det[$k]).");";

				//echo "\n-- Sentencia MODIFICAR PEDIDO: ".$sql.' --';".trim($observacion)."
				$sqlca->query($sql);	
			return 1;
	} 

	function actualizaMinMax($almacen, $codigo, $min, $max) {
		global $sqlca;

		$sql = "SELECT count(*) as num FROM inv_stkminmax WHERE ch_almacen='$almacen' AND art_codigo='$codigo';";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$a = $sqlca->fetchRow();

		if ($a[0] == 0) { // INSERTA A INV_STKMINMAX
			$sql = "INSERT INTO 	
					inv_stkminmax
					(
						ch_almacen,
  						art_codigo,
  						stk_minimo,
  						stk_maximo,
						fecha_actualizacion
					)
				VALUES 
					(
						'$almacen',	
						'$codigo',
						$min,
						$max,
						now()
					);";

		} else { // UPDATE A INV_STKMINMAX

			$sql = "UPDATE 	
					inv_stkminmax
				SET 	
					stk_minimo=".trim($min).", 
					stk_maximo=".trim($max).",
					fecha_actualizacion = now()
				WHERE 	
					ch_almacen='$almacen'
					AND art_codigo='$codigo';";
		}

		//echo "\n-- Sentencia MINMAX: ".$sql.' --';
		$sqlca->query($sql);		

		return 1;
	} 

	//function agregarItem($almacen, $sCodLinea, $sCodProveedor, $nropedido, $tipo, $observacion, $usuario, $vec_check, $codigo, $descripcion, $mes1, $mes2, $mes3, $stk_actual, $stk_minimo, $stk_maximo, $cantidad, $ip, $vec_checkx, $codigox, $descripcionx, $mes1x, $mes2x, $mes3x, $stk_actualx, $stk_minimox, $stk_maximox, $cantidadx) {
	function agregarItem($almacen, $sCodLinea, $sCodProveedor, $nropedido, $tipo, $observacion, $usuario, $vec_check, $codigo, $stk_minimo, $stk_maximo, $cantidad, $ip, $codigox, $descripcionx, $mes1x, $mes2x, $mes3x, $stk_actualx, $stk_minimox, $stk_maximox, $cantidadx) {
		global $sqlca;
		$res = Array();

		//if ($sCodLinea != '')
		//	$line = "AND art.art_linea = '" . $sCodLinea . "'";
//					" . $line . "

		if ($sCodProveedor == '')
			return false;
	    
		$sqlmes = "SELECT now() AS mes, now() - interval '1 month' AS mes1, now() - interval '2 month' AS mes2, now() - interval '3 month' AS mes3;";
		if ($sqlca->query($sqlmes) < 0)
			return false;
		$a = $sqlca->fetchRow();

		$mes   	= substr($a['mes'], 5, 2);
		$anio  	= substr($a['mes'], 0, 4);
		$mes01 	= substr($a['mes1'], 5, 2);
		$anio01	= substr($a['mes1'], 0, 4);
		$mes02 	= substr($a['mes2'], 5, 2);
		$anio02	= substr($a['mes2'], 0, 4);
		$mes03 	= substr($a['mes3'], 5, 2);
		$anio03	= substr($a['mes3'], 0, 4);

		for ($i = 0; $i < count($codigo); $i++) {
			if ($codigox != $codigo[$i]) {
				$res[$i]['art_codigo'] = $codigo[$i];

				$sql = "
				SELECT
					art.art_descripcion,
					(SELECT SUM(v3.mov_cantidad) FROM inv_movialma v3 WHERE v3.art_codigo=art.art_codigo and v3.mov_almacen='$almacen' and to_char(v3.mov_fecha,'YYYY-MM')='$anio03-$mes03' and v3.tran_codigo IN('25','35','45','10')) as mes3,
					(SELECT SUM(v2.mov_cantidad) FROM inv_movialma v2 WHERE v2.art_codigo=art.art_codigo and v2.mov_almacen='$almacen' and to_char(v2.mov_fecha,'YYYY-MM')='$anio02-$mes02' and v2.tran_codigo IN('25','35','45','10')) as mes2,
					(SELECT SUM(v1.mov_cantidad) FROM inv_movialma v1 WHERE v1.art_codigo=art.art_codigo and v1.mov_almacen='$almacen' and to_char(v1.mov_fecha,'YYYY-MM')='$anio01-$mes01' and v1.tran_codigo IN('25','35','45','10')) as mes1,
					(SELECT s.stk_stock$mes FROM inv_saldoalma s WHERE art.art_codigo=s.art_codigo AND s.stk_almacen='$almacen' AND s.stk_periodo='$anio') as stk_actual
				FROM
					int_articulos AS art
					LEFT JOIN inv_stkminmax AS mm ON (mm.art_codigo = art.art_codigo AND mm.ch_almacen = '" . $almacen . "')
					LEFT JOIN com_rec_pre_proveedor AS com ON (art.art_codigo = com.art_codigo)
					LEFT JOIN int_proveedores AS pro ON (pro.pro_codigo = com.pro_codigo)
					LEFT JOIN int_tabla_general AS tab ON (tab.tab_elemento = art.art_linea AND tab_tabla = '20' AND tab.tab_elemento != '000000')
				WHERE
					art.art_estado 		= '0'
					AND art.art_plutipo 	= '1'
					AND art.art_codigo 	= '" . $codigo[$i] . "'
					AND pro.pro_codigo 	= '" . $sCodProveedor . "'
				LIMIT 1;
				";

				$sqlca->query($sql);
				$row = $sqlca->fetchRow();

				if (trim($row['mes3'])=='') 
					$row['mes3'] = 0;

				if (trim($row['mes2'])=='') 
					$row['mes2'] = 0;

				if (trim($row['mes1'])=='') 
					$row['mes1'] = 0;

				if (trim($row['stk_actual'])=='') 
					$row['stk_actual'] = 0;

				$res[$i]['art_descripcion']	= $row['art_descripcion'];
				$res[$i]['mes_3'] 			= $row['mes3'];
				$res[$i]['mes_2'] 			= $row['mes2'];
				$res[$i]['mes_1'] 			= $row['mes1'];
				$res[$i]['stk_actual'] 		= $row['stk_actual'];

				/*
				$res[$i]['art_descripcion']	= $descripcion[$i];
				$res[$i]['mes_3'] 			= $mes3[$i];
				$res[$i]['mes_2'] 			= $mes2[$i];
				$res[$i]['mes_1'] 			= $mes1[$i];
				$res[$i]['stk_actual'] 		= $stk_actual[$i];
				*/
				$res[$i]['stk_minimo'] 		= $stk_minimo[$i];
				$res[$i]['stk_maximo'] 		= $stk_maximo[$i];
				$res[$i]['cantidad'] 		= $cantidad[$i];
			}
		}
		
		$i = count($codigo);
		$res[$i]['art_codigo']			= $codigox;
		$res[$i]['art_descripcion']	= $descripcionx;
		$res[$i]['mes_3'] 				= $mes3x;
		$res[$i]['mes_2'] 				= $mes2x;
		$res[$i]['mes_1'] 				= $mes1x;		
		$res[$i]['stk_actual'] 			= $stk_actualx;
		$res[$i]['stk_minimo'] 			= $stk_minimox;
		$res[$i]['stk_maximo'] 			= $stk_maximox;
		$res[$i]['cantidad'] 			= $cantidadx;
		$res[$i]['cantidad'] 			= $cantidadx;

		$resultado = Array();
		$resultado['cabecera']['almacen']		= $almacen;
		$resultado['cabecera']['linea']			= $sCodLinea;
		$resultado['cabecera']['proveedor']		= $sCodProveedor;
		$resultado['cabecera']['tipo']			= $tipo;
		$resultado['cabecera']['nropedido']		= $nropedido;
		$resultado['cabecera']['observacion']	= $observacion;
		$resultado['detalle']						= $res;	
		return $resultado;	
	} 

	function obtieneListaEstaciones() {
		global $sqlca;
	
		$sql = "SELECT 
				ch_almacen, 
				trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1'
			ORDER BY 
				ch_almacen; ";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$result = Array();	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[0]." - ".$a[1];
		}	
		return $result;
	}

	function obtieneNombreEstacion($almacen) {
		global $sqlca;
	
		$sql = "SELECT 
				ch_almacen, 
				trim(ch_nombre_almacen)
			FROM 
				inv_ta_almacenes
			WHERE 
				ch_clase_almacen='1'
				AND ch_almacen='$almacen';";

		if ($sqlca->query($sql) < 0) 
			return false;	

		$a = $sqlca->fetchRow();
		$nombre = $a[0]." - ".$a[1];

		return $nombre;
	}

	function obtieneNroPedido() {
		global $sqlca;

		$sql = "SELECT MAX(num_pedido)+1 FROM pedido_compra_cabecera;";

		if ($sqlca->query($sql)<=0)
			return false;

		$a = $sqlca->fetchRow();
		$nid = $a[0];
		if(trim($nid)=='') $nid = 1;

		return $nid;
	}
}
